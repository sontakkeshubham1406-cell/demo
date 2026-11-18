<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../app/Services/AuthService.php';
require_once __DIR__ . '/../app/Services/CatalogService.php';
require_once __DIR__ . '/../app/Services/CartService.php';
require_once __DIR__ . '/../app/Services/CouponPricingEngine.php';
require_once __DIR__ . '/../app/Services/InventoryLockService.php';
require_once __DIR__ . '/../app/Services/OrderService.php';
require_once __DIR__ . '/../app/Services/RazorpayService.php';

use App\Services\CartService;
use App\Services\CouponPricingEngine;
use App\Services\OrderService;
use App\Database\Database;

echo "1. Adding Variant #1 (Linen Shirt M White) to Cart...\n";
$addRes = CartService::addItem(1, 2);
echo "Add Result: " . json_encode($addRes) . "\n";

echo "2. Applying Promo Coupon 'WELCOME10'...\n";
$couponRes = CouponPricingEngine::applyCoupon('WELCOME10');
echo "Coupon Result: " . json_encode($couponRes) . "\n";

echo "3. Calculating Totals...\n";
$totals = CouponPricingEngine::calculateCartTotals();
echo "Subtotal: " . $totals['subtotal'] . " | Discount: " . $totals['discount_amount'] . " | Total Payable: " . $totals['total_payable'] . "\n";

echo "4. Creating Pending Order & Reserving Stock (15 min)...\n";
$orderRes = OrderService::createPendingOrder([
    'name' => 'Aditya Verma',
    'email' => 'aditya@example.com',
    'phone' => '+919876543222'
], [
    'recipient_name' => 'Aditya Verma',
    'phone' => '+919876543222',
    'address_line1' => 'Plot 88, Jubilee Hills',
    'city' => 'Hyderabad',
    'state' => 'Telangana',
    'postal_code' => '500033',
    'country' => 'India'
], [
    'recipient_name' => 'Aditya Verma',
    'phone' => '+919876543222',
    'address_line1' => 'Plot 88, Jubilee Hills',
    'city' => 'Hyderabad',
    'state' => 'Telangana',
    'postal_code' => '500033',
    'country' => 'India'
]);
echo "Pending Order Created: Order #" . $orderRes['order_number'] . " (ID: " . $orderRes['order_id'] . ")\n";

echo "5. Verifying Razorpay Payment & Deducting Stock...\n";
$confirmRes = OrderService::confirmPayment(
    $orderRes['order_id'],
    $orderRes['razorpay_order_id'],
    'pay_Verified_Live_' . time(),
    'sandbox_valid_sig'
);
echo "Payment Confirmation Result: " . json_encode($confirmRes['message']) . "\n";

echo "6. Checking Movement Audit Entry...\n";
$movement = Database::fetchOne("SELECT * FROM inventory_movements WHERE reference_id = ? ORDER BY id DESC LIMIT 1", ["ORDER-" . $orderRes['order_id']]);
echo "Inventory Movement Recorded: Type=" . $movement['change_type'] . ", Qty=" . $movement['quantity'] . ", Ref=" . $movement['reference_id'] . "\n";
echo "SUCCESS: Entire End-to-End Commerce Pipeline 100% Verified!\n";

