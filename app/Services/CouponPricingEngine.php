<?php
namespace App\Services;

use App\Database\Database;

class CouponPricingEngine
{
    public static function calculateCartTotals(?int $couponId = null): array
    {
        $items = CartService::getCartItems();
        $subtotal = 0.00;
        $totalItemsCount = 0;

        foreach ($items as $item) {
            $subtotal += ($item['unit_price'] * $item['quantity']);
            $totalItemsCount += $item['quantity'];
        }

        $cart = CartService::getOrCreateCart();
        $activeCouponId = $couponId ?? $cart['coupon_id'];
        $discountAmount = 0.00;
        $appliedCoupon = null;
        $couponError = null;

        if ($activeCouponId && $subtotal > 0) {
            $coupon = Database::fetchOne("SELECT * FROM coupons WHERE id = ? AND is_active = 1", [$activeCouponId]);
            if ($coupon) {
                $validation = self::validateCoupon($coupon['code'], $subtotal);
                if ($validation['valid']) {
                    $appliedCoupon = $coupon;
                    $discountAmount = $validation['discount'];
                } else {
                    $couponError = $validation['message'];
                    // Remove invalid coupon from cart
                    Database::update('carts', ['coupon_id' => null], 'id = :id', ['id' => $cart['id']]);
                }
            }
        }

        // Shipping Calculation
        $afterDiscount = max(0, $subtotal - $discountAmount);
        $shippingFee = ($afterDiscount >= FREE_SHIPPING_THRESHOLD || $subtotal == 0) ? 0.00 : FLAT_SHIPPING_FEE;
        $freeShippingRemaining = max(0, FREE_SHIPPING_THRESHOLD - $afterDiscount);

        // Tax Calculation (GST is built into retail pricing, calculating 12% component for tax breakdown)
        $taxableAmount = $afterDiscount / (1 + GST_RATE_STANDARD);
        $taxAmount = round($afterDiscount - $taxableAmount, 2);

        $totalPayable = round($afterDiscount + $shippingFee, 2);

        return [
            'items' => $items,
            'items_count' => $totalItemsCount,
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($discountAmount, 2),
            'applied_coupon' => $appliedCoupon,
            'coupon_error' => $couponError,
            'shipping_fee' => round($shippingFee, 2),
            'free_shipping_remaining' => round($freeShippingRemaining, 2),
            'tax_amount' => $taxAmount,
            'total_payable' => $totalPayable
        ];
    }

    public static function validateCoupon(string $code, float $cartSubtotal): array
    {
        $code = strtoupper(trim($code));
        $coupon = Database::fetchOne("SELECT * FROM coupons WHERE code = ? AND is_active = 1", [$code]);

        if (!$coupon) {
            return ['valid' => false, 'message' => 'Invalid or inactive promo code.'];
        }

        $now = date('Y-m-d H:i:s');
        if ($now < $coupon['start_date'] || $now > $coupon['end_date']) {
            return ['valid' => false, 'message' => 'This promo code has expired.'];
        }

        if ($cartSubtotal < (float)$coupon['min_order_value']) {
            return [
                'valid' => false, 
                'message' => 'Minimum bag value of ₹' . number_format($coupon['min_order_value']) . ' required for this coupon.'
            ];
        }

        // Usage limit total
        if ($coupon['usage_limit_total'] !== null) {
            $usedCount = Database::fetchOne("SELECT COUNT(id) as c FROM coupon_usages WHERE coupon_id = ?", [$coupon['id']])['c'];
            if ($usedCount >= $coupon['usage_limit_total']) {
                return ['valid' => false, 'message' => 'Coupon redemption limit has been reached.'];
            }
        }

        // User specific checks
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            // First order only check
            if ($coupon['is_first_order_only']) {
                $pastOrders = Database::fetchOne("SELECT COUNT(id) as c FROM orders WHERE user_id = ? AND payment_status = 'PAID'", [$userId])['c'];
                if ($pastOrders > 0) {
                    return ['valid' => false, 'message' => 'This promo code is strictly valid for your first order only.'];
                }
            }

            // Usage limit per user
            $userUsage = Database::fetchOne("SELECT COUNT(id) as c FROM coupon_usages WHERE coupon_id = ? AND user_id = ?", [$coupon['id'], $userId])['c'];
            if ($userUsage >= $coupon['usage_limit_per_user']) {
                return ['valid' => false, 'message' => 'You have already used this coupon maximum allowed times.'];
            }
        }

        // Calculate discount
        $discount = 0.00;
        if ($coupon['discount_type'] === 'PERCENTAGE') {
            $discount = ($cartSubtotal * (float)$coupon['discount_value']) / 100.0;
            if ($coupon['max_discount_amount'] !== null && $discount > (float)$coupon['max_discount_amount']) {
                $discount = (float)$coupon['max_discount_amount'];
            }
        } else {
            $discount = (float)$coupon['discount_value'];
        }

        $discount = min($discount, $cartSubtotal);

        return [
            'valid' => true,
            'coupon' => $coupon,
            'discount' => round($discount, 2),
            'message' => 'Coupon ' . $code . ' applied successfully!'
        ];
    }

    public static function applyCoupon(string $code): array
    {
        $totals = self::calculateCartTotals();
        $validation = self::validateCoupon($code, $totals['subtotal']);

        if (!$validation['valid']) {
            return ['success' => false, 'message' => $validation['message']];
        }

        $cart = CartService::getOrCreateCart();
        Database::update('carts', ['coupon_id' => $validation['coupon']['id']], 'id = :id', ['id' => $cart['id']]);

        return [
            'success' => true, 
            'message' => 'Coupon applied! You saved ₹' . number_format($validation['discount'], 2)
        ];
    }

    public static function removeCoupon(): array
    {
        $cart = CartService::getOrCreateCart();
        Database::update('carts', ['coupon_id' => null], 'id = :id', ['id' => $cart['id']]);
        return ['success' => true, 'message' => 'Coupon removed.'];
    }
}

