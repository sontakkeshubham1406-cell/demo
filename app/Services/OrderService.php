<?php
namespace App\Services;

use App\Database\Database;
use Exception;

class OrderService
{
    /**
     * Initializes a new order in PENDING_PAYMENT state and reserves inventory.
     */
    public static function createPendingOrder(array $customerData, array $shippingAddress, array $billingAddress, string $notes = ''): array
    {
        $totals = CouponPricingEngine::calculateCartTotals();
        if (empty($totals['items'])) {
            return ['success' => false, 'message' => 'Shopping bag is empty.'];
        }

        // 1. Soft-Reserve Stock for 15 minutes
        $reservation = InventoryLockService::reserveCartStock($totals['items']);
        if (!$reservation['success']) {
            return $reservation;
        }

        Database::beginTransaction();

        try {
            $userId = $_SESSION['user_id'] ?? null;
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

            $orderId = Database::insert('orders', [
                'order_number' => $orderNumber,
                'user_id' => $userId,
                'customer_name' => trim($customerData['name']),
                'customer_email' => strtolower(trim($customerData['email'])),
                'customer_phone' => trim($customerData['phone']),
                'subtotal' => $totals['subtotal'],
                'discount_amount' => $totals['discount_amount'],
                'tax_amount' => $totals['tax_amount'],
                'shipping_fee' => $totals['shipping_fee'],
                'total_amount' => $totals['total_payable'],
                'status' => 'PENDING_PAYMENT',
                'payment_status' => 'UNPAID',
                'shipping_address' => json_encode($shippingAddress),
                'billing_address' => json_encode($billingAddress),
                'notes' => $notes
            ]);

            // Insert frozen snapshot of line items
            foreach ($totals['items'] as $item) {
                Database::insert('order_items', [
                    'order_id' => $orderId,
                    'variant_id' => $item['variant_id'],
                    'product_name' => $item['product_name'],
                    'sku' => $item['sku'],
                    'size_name' => $item['size_name'],
                    'color_name' => $item['color_name'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'total_price' => round($item['unit_price'] * $item['quantity'], 2)
                ]);
            }

            // Create Razorpay Gateway Order
            $razorpay = new RazorpayService();
            $rzpOrder = $razorpay->createOrder($totals['total_payable'], $orderNumber);

            if (!$rzpOrder['success']) {
                throw new Exception("Payment gateway order initialization failed.");
            }

            // Store payment record
            Database::insert('payments', [
                'order_id' => $orderId,
                'payment_method' => 'RAZORPAY',
                'gateway' => 'RAZORPAY',
                'gateway_order_id' => $rzpOrder['order_id'],
                'amount' => $totals['total_payable'],
                'currency' => 'INR',
                'status' => 'CREATED'
            ]);

            Database::commit();

            return [
                'success' => true,
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'total_amount' => $totals['total_payable'],
                'razorpay_order_id' => $rzpOrder['order_id'],
                'razorpay_key_id' => $rzpOrder['key_id'],
                'amount_in_paise' => $rzpOrder['amount_in_paise'],
                'customer_name' => $customerData['name'],
                'customer_email' => $customerData['email'],
                'customer_phone' => $customerData['phone'],
                'is_sandbox' => $rzpOrder['is_sandbox'] ?? false
            ];
        } catch (Exception $e) {
            Database::rollBack();
            InventoryLockService::releaseCartStock($totals['items']);
            return ['success' => false, 'message' => 'Order creation failed: ' . $e->getMessage()];
        }
    }

    /**
     * Confirms order after cryptographic verification of Razorpay payment signature.
     */
    public static function confirmPayment(int $orderId, string $razorpayOrderId, string $razorpayPaymentId, string $razorpaySignature): array
    {
        $order = Database::fetchOne("SELECT * FROM orders WHERE id = ?", [$orderId]);
        if (!$order) {
            return ['success' => false, 'message' => 'Order not found.'];
        }

        if ($order['status'] === 'CONFIRMED' || $order['payment_status'] === 'PAID') {
            return ['success' => true, 'message' => 'Order already verified.', 'order' => $order];
        }

        // Strict Server-Side Signature Verification
        $razorpay = new RazorpayService();
        $isValid = $razorpay->verifyPaymentSignature($razorpayOrderId, $razorpayPaymentId, $razorpaySignature);

        if (!$isValid) {
            Database::update('payments', [
                'status' => 'FAILED',
                'error_code' => 'SIGNATURE_MISMATCH',
                'error_description' => 'Cryptographic signature verification failed'
            ], 'order_id = :id', ['id' => $orderId]);

            return ['success' => false, 'message' => 'Payment signature verification failed. Contact support.'];
        }

        Database::beginTransaction();

        try {
            // Update order status
            Database::update('orders', [
                'status' => 'CONFIRMED',
                'payment_status' => 'PAID',
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = :id', ['id' => $orderId]);

            // Update payment record
            Database::update('payments', [
                'gateway_payment_id' => $razorpayPaymentId,
                'gateway_signature' => $razorpaySignature,
                'status' => 'CAPTURED',
                'updated_at' => date('Y-m-d H:i:s')
            ], 'order_id = :id', ['id' => $orderId]);

            // Deduct stock permanently from inventory
            InventoryLockService::commitOrderStock($orderId);

            // Record Coupon Usage if coupon was applied and user exists
            $cart = CartService::getOrCreateCart();
            if (!empty($cart['coupon_id']) && $order['discount_amount'] > 0 && !empty($order['user_id'])) {
                Database::insert('coupon_usages', [
                    'coupon_id' => $cart['coupon_id'],
                    'user_id' => $order['user_id'],
                    'order_id' => $orderId,
                    'discount_amount' => $order['discount_amount']
                ]);
            }

            // Clear shopping bag
            CartService::clearCart();

            Database::commit();

            return [
                'success' => true,
                'message' => 'Payment verified and order confirmed successfully!',
                'order' => Database::fetchOne("SELECT * FROM orders WHERE id = ?", [$orderId])
            ];
        } catch (Exception $e) {
            Database::rollBack();
            return ['success' => false, 'message' => 'Failed to commit order: ' . $e->getMessage()];
        }
    }

    /**
     * Transition order status (Admin Fulfillment)
     */
    public static function updateOrderStatus(int $orderId, string $newStatus, array $metadata = []): array
    {
        $order = Database::fetchOne("SELECT * FROM orders WHERE id = ?", [$orderId]);
        if (!$order) {
            return ['success' => false, 'message' => 'Order not found.'];
        }

        $validTransitions = [
            'PENDING_PAYMENT' => ['CONFIRMED', 'CANCELLED'],
            'CONFIRMED' => ['PROCESSING', 'CANCELLED'],
            'PROCESSING' => ['SHIPPED', 'CANCELLED'],
            'SHIPPED' => ['DELIVERED'],
            'DELIVERED' => ['RETURNED'],
            'CANCELLED' => [],
            'RETURNED' => []
        ];

        if (!in_array($newStatus, $validTransitions[$order['status']] ?? [])) {
            return ['success' => false, 'message' => "Illegal status transition from {$order['status']} to {$newStatus}."];
        }

        Database::beginTransaction();

        try {
            Database::update('orders', [
                'status' => $newStatus,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = :id', ['id' => $orderId]);

            // If marking shipped, attach tracking details
            if ($newStatus === 'SHIPPED' && !empty($metadata['tracking_number'])) {
                $carrier = $metadata['carrier_name'] ?? 'Blue Dart';
                $trackingNum = $metadata['tracking_number'];
                $trackingUrl = "https://track.courier.com/?awb=" . urlencode($trackingNum);

                Database::insert('shipments', [
                    'order_id' => $orderId,
                    'carrier_name' => $carrier,
                    'tracking_number' => $trackingNum,
                    'tracking_url' => $trackingUrl,
                    'shipped_at' => date('Y-m-d H:i:s')
                ]);
            }

            // If marking delivered, update shipment delivered timestamp
            if ($newStatus === 'DELIVERED') {
                Database::execute("UPDATE shipments SET delivered_at = CURRENT_TIMESTAMP WHERE order_id = ?", [$orderId]);
            }

            // If cancelling order, initiate refund and restock items
            if ($newStatus === 'CANCELLED') {
                self::handleOrderCancellation($order);
            }

            Database::commit();

            return ['success' => true, 'message' => "Order #{$order['order_number']} updated to {$newStatus}."];
        } catch (Exception $e) {
            Database::rollBack();
            return ['success' => false, 'message' => 'Failed to update order status: ' . $e->getMessage()];
        }
    }

    private static function handleOrderCancellation(array $order): void
    {
        // Restock inventory
        $items = Database::query("SELECT * FROM order_items WHERE order_id = ?", [$order['id']]);
        foreach ($items as $item) {
            if ($item['variant_id']) {
                InventoryLockService::restockVariant(
                    $item['variant_id'], 
                    $item['quantity'], 
                    "Order #{$order['order_number']} cancelled", 
                    $order['order_number']
                );
            }
        }

        // Trigger Razorpay Refund if payment was captured
        if ($order['payment_status'] === 'PAID') {
            $payment = Database::fetchOne("SELECT * FROM payments WHERE order_id = ? AND status = 'CAPTURED'", [$order['id']]);
            if ($payment && !empty($payment['gateway_payment_id'])) {
                $razorpay = new RazorpayService();
                $refundRes = $razorpay->processRefund($payment['gateway_payment_id'], (float)$order['total_amount'], [
                    'order_number' => $order['order_number'],
                    'reason' => 'Order cancelled prior to dispatch'
                ]);

                if ($refundRes['success']) {
                    Database::insert('refunds', [
                        'order_id' => $order['id'],
                        'payment_id' => $payment['id'],
                        'gateway_refund_id' => $refundRes['refund_id'],
                        'amount' => $order['total_amount'],
                        'reason' => 'Order cancelled',
                        'status' => 'PROCESSED'
                    ]);

                    Database::update('orders', ['payment_status' => 'REFUNDED'], 'id = :id', ['id' => $order['id']]);
                }
            }
        }
    }

    public static function getOrderDetails(int $orderId): ?array
    {
        $order = Database::fetchOne("SELECT * FROM orders WHERE id = ?", [$orderId]);
        if (!$order) {
            return null;
        }

        $order['items'] = Database::query("
            SELECT oi.*, pv.sku, p.slug as product_slug,
                (SELECT image_url FROM product_images WHERE product_id = pv.product_id AND is_primary = 1 LIMIT 1) as item_image
            FROM order_items oi
            LEFT JOIN product_variants pv ON oi.variant_id = pv.id
            LEFT JOIN products p ON pv.product_id = p.id
            WHERE oi.order_id = ?
        ", [$orderId]);

        $order['payment'] = Database::fetchOne("SELECT * FROM payments WHERE order_id = ?", [$orderId]);
        $order['shipment'] = Database::fetchOne("SELECT * FROM shipments WHERE order_id = ?", [$orderId]);
        $order['refund'] = Database::fetchOne("SELECT * FROM refunds WHERE order_id = ?", [$orderId]);
        $order['shipping_address'] = json_decode($order['shipping_address'], true);
        $order['billing_address'] = json_decode($order['billing_address'], true);

        return $order;
    }
}
