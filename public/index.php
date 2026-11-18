<?php
/**
 * Master Application Front Controller & Dispatcher
 * VELORIS LUXE Haute Couture Platform
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../database/Database.php';

// Autoload Services
require_once __DIR__ . '/../app/Services/AuthService.php';
require_once __DIR__ . '/../app/Services/CatalogService.php';
require_once __DIR__ . '/../app/Services/CartService.php';
require_once __DIR__ . '/../app/Services/CouponPricingEngine.php';
require_once __DIR__ . '/../app/Services/InventoryLockService.php';
require_once __DIR__ . '/../app/Services/RazorpayService.php';
require_once __DIR__ . '/../app/Services/OrderService.php';
require_once __DIR__ . '/../app/Services/AuditLogger.php';

use App\Services\AuthService;
use App\Services\CatalogService;
use App\Services\CartService;
use App\Services\CouponPricingEngine;
use App\Services\InventoryLockService;
use App\Services\OrderService;
use App\Services\RazorpayService;
use App\Services\AuditLogger;
use App\Database\Database;

// Normalize Request Path
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Flash Message Helper
function flash(string $key, string $message): void {
    $_SESSION['flash_' . $key] = $message;
}

// Render Helper
function render(string $viewPath, array $data = [], string $layout = 'main'): void {
    extract($data);
    ob_start();
    require VIEW_PATH . '/' . $viewPath . '.php';
    $content = ob_get_clean();

    require VIEW_PATH . '/layouts/' . $layout . '.php';
}

function jsonResponse(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

// -------------------------------------------------------------
// REST API & WEBHOOK ENDPOINTS
// -------------------------------------------------------------

// Live Autocomplete Search API
if ($uri === '/api/search' && $method === 'GET') {
    $q = $_GET['q'] ?? '';
    $results = CatalogService::getProducts(['search' => $q], 8);
    jsonResponse(['results' => $results]);
}

// Checkout Create Order API (Initializes 15-min stock lock & Razorpay Order)
if ($uri === '/api/checkout/create-order' && $method === 'POST') {
    if (!AuthService::check()) {
        jsonResponse(['success' => false, 'message' => 'Sign in is required to place an order.'], 401);
    }

    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $customerData = [
        'name' => $input['name'] ?? '',
        'email' => $input['email'] ?? '',
        'phone' => $input['phone'] ?? ''
    ];

    $shippingAddress = [
        'recipient_name' => $input['name'] ?? '',
        'phone' => $input['phone'] ?? '',
        'address_line1' => $input['address_line1'] ?? '',
        'address_line2' => $input['address_line2'] ?? '',
        'landmark' => $input['landmark'] ?? '',
        'city' => $input['city'] ?? '',
        'state' => $input['state'] ?? '',
        'postal_code' => $input['postal_code'] ?? '',
        'country' => 'India'
    ];

    $res = OrderService::createPendingOrder($customerData, $shippingAddress, $shippingAddress);
    jsonResponse($res, $res['success'] ? 200 : 422);
}

// Checkout Verify Payment API (Dual Server-Side Verification via HMAC-SHA256)
if ($uri === '/api/checkout/verify-payment' && $method === 'POST') {
    if (!AuthService::check()) {
        jsonResponse(['success' => false, 'message' => 'Sign in is required to complete payment.'], 401);
    }

    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $orderId = (int)($input['order_id'] ?? 0);
    $rzpOrderId = $input['razorpay_order_id'] ?? '';
    $rzpPaymentId = $input['razorpay_payment_id'] ?? '';
    $rzpSig = $input['razorpay_signature'] ?? '';

    $res = OrderService::confirmPayment($orderId, $rzpOrderId, $rzpPaymentId, $rzpSig);
    jsonResponse($res, $res['success'] ? 200 : 400);
}

// Razorpay Webhook Receiver with Idempotency
if ($uri === '/api/webhooks/razorpay' && $method === 'POST') {
    $payload = file_get_contents('php://input');
    $sig = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

    $rzp = new RazorpayService();
    if (!$rzp->verifyWebhookSignature($payload, $sig)) {
        jsonResponse(['error' => 'Invalid signature'], 401);
    }

    $event = json_decode($payload, true);
    $eventId = $event['event_id'] ?? uniqid('evt_');

    // Idempotency check
    $existing = Database::fetchOne("SELECT id FROM payment_webhooks WHERE event_id = ?", [$eventId]);
    if ($existing) {
        jsonResponse(['message' => 'Event already processed']);
    }

    Database::insert('payment_webhooks', [
        'gateway' => 'RAZORPAY',
        'event_id' => $eventId,
        'event_type' => $event['event'] ?? 'unknown',
        'payload' => $payload,
        'is_processed' => 1
    ]);

    jsonResponse(['status' => 'acknowledged']);
}

// -------------------------------------------------------------
// STOREFRONT CUSTOMER ROUTES
// -------------------------------------------------------------

// Homepage
if ($uri === '/' || $uri === '/home') {
    render('storefront/home', [
        'pageTitle' => STORE_NAME . ' | Haute Couture & European Linens'
    ]);
    exit;
}

// Product Catalog (PLP)
if ($uri === '/catalog') {
    render('storefront/catalog', [
        'pageTitle' => 'Apparel Catalog | ' . STORE_NAME
    ]);
    exit;
}

// Product Details Page (PDP)
if (preg_match('#^/product/([a-zA-Z0-9\-_]+)$#', $uri, $matches)) {
    $slug = $matches[1];
    $product = CatalogService::getProductBySlug($slug);
    if (!$product) {
        http_response_code(404);
        render('storefront/catalog', ['pageTitle' => 'Product Not Found']);
        exit;
    }

    render('storefront/product', [
        'product' => $product,
        'pageTitle' => $product['name'] . ' | ' . STORE_NAME
    ]);
    exit;
}

// Cart: Dedicated Page
if ($uri === '/cart' && $method === 'GET') {
    render('storefront/cart', [
        'pageTitle' => 'Your Shopping Bag | ' . STORE_NAME
    ]);
    exit;
}

// Cart: Add Item Action
if ($uri === '/cart/add' && $method === 'POST') {
    if (!AuthService::check()) {
        $_SESSION['redirect_after_login'] = $_SERVER['HTTP_REFERER'] ?? '/catalog';
        flash('error', 'Please sign in to your account to add items to your shopping bag.');
        header("Location: /login");
        exit;
    }
    $variantId = (int)($_POST['variant_id'] ?? 0);
    $qty = (int)($_POST['quantity'] ?? 1);
    $res = CartService::addItem($variantId, $qty);
    if ($res['success']) {
        flash('success', $res['message']);
    } else {
        flash('error', $res['message']);
    }
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/cart'));
    exit;
}

// Cart: Update Item Quantity Action
if ($uri === '/cart/update' && $method === 'POST') {
    $cartItemId = (int)($_POST['cart_item_id'] ?? 0);
    $qty = (int)($_POST['quantity'] ?? 1);
    $res = CartService::updateItem($cartItemId, $qty);
    if (!$res['success']) flash('error', $res['message']);
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/cart'));
    exit;
}

// Cart: Remove Item Action
if ($uri === '/cart/remove' && $method === 'POST') {
    $cartItemId = (int)($_POST['cart_item_id'] ?? 0);
    $res = CartService::removeItem($cartItemId);
    flash('success', $res['message']);
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/cart'));
    exit;
}

// Cart: Apply Coupon Code
if ($uri === '/cart/coupon' && $method === 'POST') {
    $code = trim($_POST['coupon_code'] ?? '');
    $res = CouponPricingEngine::applyCoupon($code);
    if ($res['success']) {
        flash('success', $res['message']);
    } else {
        flash('error', $res['message']);
    }
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/cart'));
    exit;
}

// Cart: Remove Coupon Code
if ($uri === '/cart/coupon-remove' && $method === 'POST') {
    CouponPricingEngine::removeCoupon();
    flash('success', 'Coupon removed from bag.');
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/cart'));
    exit;
}

// Checkout Page
if ($uri === '/checkout' && $method === 'GET') {
    if (!AuthService::check()) {
        $_SESSION['redirect_after_login'] = '/checkout';
        flash('error', 'Sign in is required to proceed with checkout and payment.');
        header("Location: /login");
        exit;
    }
    render('storefront/checkout', [
        'pageTitle' => 'Checkout & Payment | ' . STORE_NAME
    ]);
    exit;
}

// Direct 1-Click Buy Now
if ($uri === '/checkout/direct' && $method === 'POST') {
    if (!AuthService::check()) {
        $_SESSION['redirect_after_login'] = $_SERVER['HTTP_REFERER'] ?? '/checkout';
        flash('error', 'Please sign in to proceed with direct checkout.');
        header("Location: /login");
        exit;
    }
    $variantId = (int)($_POST['variant_id'] ?? 0);
    CartService::addItem($variantId, 1);
    header("Location: /checkout");
    exit;
}

// Order Confirmation Receipt
if ($uri === '/checkout/success' && $method === 'GET') {
    render('storefront/order_success', [
        'pageTitle' => 'Order Confirmed | ' . STORE_NAME
    ]);
    exit;
}

// Reviews: Create
if ($uri === '/reviews/create' && $method === 'POST') {
    $productId = (int)($_POST['product_id'] ?? 0);
    $rating = (int)($_POST['rating'] ?? 5);
    $title = trim($_POST['title'] ?? '');
    $comment = trim($_POST['comment'] ?? '');

    if ($productId && $comment) {
        Database::insert('reviews', [
            'product_id' => $productId,
            'user_id' => $_SESSION['user_id'] ?? 3,
            'rating' => max(1, min(5, $rating)),
            'title' => $title,
            'comment' => $comment,
            'is_verified_purchase' => 1,
            'is_approved' => 1
        ]);
        flash('success', 'Thank you! Your verified review has been published.');
    }
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/catalog'));
    exit;
}

// Customer Auth: Login
if ($uri === '/login') {
    if ($method === 'POST') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        if (AuthService::attempt($email, $password, false)) {
            flash('success', 'Welcome back, ' . htmlspecialchars($_SESSION['user_name']));
            $target = $_SESSION['redirect_after_login'] ?? '/account/orders';
            unset($_SESSION['redirect_after_login']);
            header("Location: " . $target);
            exit;
        } else {
            flash('error', 'Invalid email or password credentials.');
        }
    }
    render('storefront/account/login', ['pageTitle' => 'Sign In | ' . STORE_NAME]);
    exit;
}

// Customer Auth: Register
if ($uri === '/register') {
    if ($method === 'POST') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';

        $existing = Database::fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existing) {
            flash('error', 'An account with this email already exists.');
        } else {
            AuthService::register([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => $password
            ]);
            flash('success', 'Welcome to the VELORIS LUXE private atelier!');
            $target = $_SESSION['redirect_after_login'] ?? '/catalog';
            unset($_SESSION['redirect_after_login']);
            header("Location: " . $target);
            exit;
        }
    }
    render('storefront/account/register', ['pageTitle' => 'Join Atelier | ' . STORE_NAME]);
    exit;
}

// Customer Logout
if ($uri === '/logout') {
    AuthService::logout();
    flash('success', 'You have been signed out safely.');
    header("Location: /");
    exit;
}

// Customer Account: Orders History
if ($uri === '/account/orders' && $method === 'GET') {
    render('storefront/account/orders', [
        'pageTitle' => 'Order History | ' . STORE_NAME
    ]);
    exit;
}

// Customer Account: Single Order View
if (preg_match('#^/account/orders/([0-9]+)$#', $uri, $matches)) {
    $orderId = (int)$matches[1];
    render('storefront/account/order_view', [
        'pageTitle' => 'Order Tracking #' . $orderId,
        'params' => ['id' => $orderId]
    ]);
    exit;
}

// Customer Account: Cancel Order Action
if ($uri === '/account/orders/cancel' && $method === 'POST') {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $res = OrderService::updateOrderStatus($orderId, 'CANCELLED');
    if ($res['success']) {
        flash('success', 'Order cancelled and automated refund triggered.');
    } else {
        flash('error', $res['message']);
    }
    header("Location: /account/orders/" . $orderId);
    exit;
}

// Customer Account: Return / Exchange Request Action
if ($uri === '/account/orders/return' && $method === 'POST') {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $orderItemId = (int)($_POST['order_item_id'] ?? 0);
    $type = $_POST['type'] ?? 'RETURN';
    $reason = $_POST['reason'] ?? 'SIZE_DISCREPANCY';
    $notes = $_POST['customer_notes'] ?? '';

    Database::insert('returns', [
        'order_id' => $orderId,
        'order_item_id' => $orderItemId,
        'type' => $type,
        'reason' => $reason,
        'customer_notes' => $notes,
        'status' => 'REQUESTED'
    ]);

    flash('success', 'Your 7-day return request has been submitted for doorstep pickup inspection.');
    header("Location: /account/orders/" . $orderId);
    exit;
}

// Static Policy Pages
if ($uri === '/pages/about-us') {
    render('storefront/pages/about', ['pageTitle' => 'Atelier Craft & Heritage | ' . STORE_NAME]);
    exit;
}
if ($uri === '/pages/returns-exchanges') {
    render('storefront/pages/returns', ['pageTitle' => '7-Day Easy Returns | ' . STORE_NAME]);
    exit;
}
if ($uri === '/pages/shipping-policy') {
    render('storefront/pages/about', ['pageTitle' => 'Express Delivery Policy | ' . STORE_NAME]);
    exit;
}
if ($uri === '/pages/size-guide') {
    render('storefront/pages/about', ['pageTitle' => 'Comprehensive Sizing Guide | ' . STORE_NAME]);
    exit;
}
if ($uri === '/pages/terms-and-conditions' || $uri === '/pages/privacy-policy' || $uri === '/pages/contact') {
    render('storefront/pages/about', ['pageTitle' => 'Terms & Policies | ' . STORE_NAME]);
    exit;
}

// -------------------------------------------------------------
// BACK-OFFICE ADMIN ROUTES (Restricted)
// -------------------------------------------------------------

// Admin Login
if ($uri === '/admin/login') {
    if ($method === 'POST') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        if (AuthService::attempt($email, $password, true)) {
            AuditLogger::log('ADMIN_LOGIN', 'User', (string)$_SESSION['user_id']);
            header("Location: /admin");
            exit;
        } else {
            flash('error', 'Invalid admin staff credentials.');
        }
    }
    require VIEW_PATH . '/admin/login.php';
    exit;
}

// All /admin/* routes below require Admin privileges
if (strpos($uri, '/admin') === 0) {
    if (!AuthService::isAdmin()) {
        header("Location: /admin/login");
        exit;
    }
}

// Admin Dashboard
if ($uri === '/admin') {
    render('admin/dashboard', ['adminTitle' => 'Executive Dashboard'], 'admin');
    exit;
}

// Admin Products List
if ($uri === '/admin/products') {
    render('admin/products/index', ['adminTitle' => 'Apparel Catalog'], 'admin');
    exit;
}

// Admin Create Product & Variant Matrix
if ($uri === '/admin/products/create') {
    if ($method === 'POST') {
        $name = trim($_POST['name'] ?? '');
        $catId = (int)($_POST['category_id'] ?? 1);
        $brandId = (int)($_POST['brand_id'] ?? 1);
        $basePrice = (float)($_POST['base_price'] ?? 0);
        $discPrice = !empty($_POST['discount_price']) ? (float)$_POST['discount_price'] : null;
        $shortDesc = trim($_POST['short_description'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $fabric = trim($_POST['fabric_composition'] ?? '');
        $wash = trim($_POST['wash_care_instructions'] ?? '');
        $model = trim($_POST['model_specifications'] ?? '');
        $primaryImg = trim($_POST['primary_image_url'] ?? '');
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

        $productId = Database::insert('products', [
            'category_id' => $catId,
            'brand_id' => $brandId,
            'name' => $name,
            'slug' => $slug,
            'short_description' => $shortDesc,
            'description' => $desc,
            'fabric_composition' => $fabric,
            'wash_care_instructions' => $wash,
            'model_specifications' => $model,
            'base_price' => $basePrice,
            'discount_price' => $discPrice,
            'is_active' => 1
        ]);

        // Insert Image
        if ($primaryImg) {
            Database::insert('product_images', [
                'product_id' => $productId,
                'image_url' => $primaryImg,
                'alt_text' => $name,
                'is_primary' => 1
            ]);
        }

        // Insert Generated 2D Variant Matrix
        $variants = $_POST['variants'] ?? [];
        foreach ($variants as $v) {
            $variantId = Database::insert('product_variants', [
                'product_id' => $productId,
                'size_id' => (int)$v['size_id'],
                'color_id' => (int)$v['color_id'],
                'sku' => $v['sku'],
                'is_active' => 1
            ]);

            $stock = (int)($v['stock'] ?? 20);
            Database::insert('inventory', [
                'variant_id' => $variantId,
                'stock_quantity' => $stock,
                'reserved_quantity' => 0,
                'safety_stock_threshold' => 5
            ]);

            Database::insert('inventory_movements', [
                'variant_id' => $variantId,
                'user_id' => $_SESSION['user_id'] ?? null,
                'change_type' => 'PURCHASE_RECEIPT',
                'quantity' => $stock,
                'reference_id' => 'INIT-CREATION',
                'remarks' => 'Opening stock for ' . $v['sku']
            ]);
        }

        AuditLogger::log('PRODUCT_CREATED', 'Product', (string)$productId);
        flash('success', "Apparel item '{$name}' created with " . count($variants) . " variant SKUs!");
        header("Location: /admin/products");
        exit;
    }
    render('admin/products/create', ['adminTitle' => 'New Apparel Matrix'], 'admin');
    exit;
}

// Admin Inventory Ledger
if ($uri === '/admin/inventory') {
    render('admin/inventory/index', ['adminTitle' => 'SKU Stock Ledger'], 'admin');
    exit;
}

// Admin Stock Adjustment Action
if ($uri === '/admin/inventory/adjust' && $method === 'POST') {
    $variantId = (int)($_POST['variant_id'] ?? 0);
    $delta = (int)($_POST['quantity_delta'] ?? 0);
    $changeType = $_POST['change_type'] ?? 'MANUAL_CORRECTION';
    $refId = trim($_POST['reference_id'] ?? 'MANUAL');
    $remarks = trim($_POST['remarks'] ?? 'Cycle Count Correction');

    Database::execute("
        UPDATE inventory 
        SET stock_quantity = MAX(0, stock_quantity + ?), updated_at = CURRENT_TIMESTAMP 
        WHERE variant_id = ?
    ", [$delta, $variantId]);

    Database::insert('inventory_movements', [
        'variant_id' => $variantId,
        'user_id' => $_SESSION['user_id'] ?? null,
        'change_type' => $changeType,
        'quantity' => $delta,
        'reference_id' => $refId,
        'remarks' => $remarks
    ]);

    AuditLogger::log('STOCK_ADJUSTMENT', 'Inventory', (string)$variantId, null, ['delta' => $delta, 'type' => $changeType]);
    flash('success', "Stock mutated by {$delta} units on SKU #{$variantId}.");
    header("Location: /admin/inventory");
    exit;
}

// Admin Orders Queue
if ($uri === '/admin/orders') {
    render('admin/orders/index', ['adminTitle' => 'Orders & Fulfillment'], 'admin');
    exit;
}

// Admin Order Inspector
if ($uri === '/admin/orders/view') {
    render('admin/orders/view', ['adminTitle' => 'Inspect Order'], 'admin');
    exit;
}

// Admin Order Status Update Action
if ($uri === '/admin/orders/status' && $method === 'POST') {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';
    $carrier = $_POST['carrier_name'] ?? '';
    $trackingNum = $_POST['tracking_number'] ?? '';

    $res = OrderService::updateOrderStatus($orderId, $newStatus, [
        'carrier_name' => $carrier,
        'tracking_number' => $trackingNum
    ]);

    if ($res['success']) {
        AuditLogger::log('ORDER_STATUS_TRANSITION', 'Order', (string)$orderId, null, ['new_status' => $newStatus]);
        flash('success', $res['message']);
    } else {
        flash('error', $res['message']);
    }

    header("Location: /admin/orders/view?id=" . $orderId);
    exit;
}

// Admin Printable GST Tax Invoice
if ($uri === '/admin/orders/invoice') {
    require VIEW_PATH . '/admin/orders/invoice.php';
    exit;
}

// Admin Returns & Refunds
if ($uri === '/admin/returns') {
    render('admin/returns/index', ['adminTitle' => 'Returns & Refunds'], 'admin');
    exit;
}

// Admin Returns Action (Approve & Trigger Razorpay Refund)
if ($uri === '/admin/returns/action' && $method === 'POST') {
    $returnId = (int)($_POST['return_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    $ret = Database::fetchOne("SELECT * FROM returns WHERE id = ?", [$returnId]);
    if ($ret && $action === 'APPROVE_AND_REFUND') {
        $order = Database::fetchOne("SELECT * FROM orders WHERE id = ?", [$ret['order_id']]);
        $orderItem = Database::fetchOne("SELECT * FROM order_items WHERE id = ?", [$ret['order_item_id']]);
        $payment = Database::fetchOne("SELECT * FROM payments WHERE order_id = ?", [$order['id']]);

        // Restock item
        if ($orderItem['variant_id']) {
            InventoryLockService::restockVariant(
                $orderItem['variant_id'],
                $orderItem['quantity'],
                "Return approved for Ticket #{$returnId}",
                "RET-{$returnId}"
            );
        }

        // Trigger Razorpay Refund
        $rzp = new RazorpayService();
        $refundRes = $rzp->processRefund($payment['gateway_payment_id'] ?? 'pay_dummy', (float)$orderItem['total_price'], [
            'return_id' => $returnId,
            'order_number' => $order['order_number']
        ]);

        Database::insert('refunds', [
            'order_id' => $order['id'],
            'payment_id' => $payment['id'] ?? 1,
            'return_id' => $returnId,
            'gateway_refund_id' => $refundRes['refund_id'],
            'amount' => $orderItem['total_price'],
            'reason' => $ret['reason'],
            'status' => 'PROCESSED'
        ]);

        Database::update('returns', ['status' => 'REFUNDED'], 'id = :id', ['id' => $returnId]);
        AuditLogger::log('RETURN_APPROVED_REFUNDED', 'Return', (string)$returnId);
        flash('success', "Return approved and Razorpay refund of ₹" . number_format($orderItem['total_price'], 2) . " disbursed.");
    } elseif ($ret && $action === 'REJECT') {
        Database::update('returns', ['status' => 'REJECTED'], 'id = :id', ['id' => $returnId]);
        flash('error', "Return ticket #{$returnId} rejected.");
    }

    header("Location: /admin/returns");
    exit;
}

// Admin Coupons List
if ($uri === '/admin/coupons') {
    render('admin/coupons/index', ['adminTitle' => 'Discount Coupons'], 'admin');
    exit;
}

// Admin Create Coupon Action
if ($uri === '/admin/coupons/create' && $method === 'POST') {
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $type = $_POST['discount_type'] ?? 'PERCENTAGE';
    $val = (float)($_POST['discount_value'] ?? 0);
    $minSpend = (float)($_POST['min_order_value'] ?? 0);
    $maxCap = !empty($_POST['max_discount_amount']) ? (float)$_POST['max_discount_amount'] : null;
    $firstOrder = isset($_POST['is_first_order_only']) ? 1 : 0;

    Database::insert('coupons', [
        'code' => $code,
        'discount_type' => $type,
        'discount_value' => $val,
        'min_order_value' => $minSpend,
        'max_discount_amount' => $maxCap,
        'is_first_order_only' => $firstOrder,
        'start_date' => date('Y-m-d H:i:s'),
        'end_date' => date('Y-m-d H:i:s', strtotime('+6 months')),
        'is_active' => 1
    ]);

    AuditLogger::log('COUPON_CREATED', 'Coupon', $code);
    flash('success', "Coupon '{$code}' created successfully!");
    header("Location: /admin/coupons");
    exit;
}

// Admin Banners List
if ($uri === '/admin/banners') {
    render('admin/banners/index', ['adminTitle' => 'Hero Banners & CMS'], 'admin');
    exit;
}

// Admin Create Banner Action
if ($uri === '/admin/banners/create' && $method === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $imgDesktop = trim($_POST['image_url_desktop'] ?? '');
    $targetUrl = trim($_POST['target_url'] ?? '/catalog');

    Database::insert('banners', [
        'title' => $title,
        'subtitle' => $subtitle,
        'image_url_desktop' => $imgDesktop,
        'image_url_mobile' => $imgDesktop,
        'target_url' => $targetUrl,
        'sort_order' => 1,
        'is_active' => 1
    ]);

    AuditLogger::log('BANNER_CREATED', 'Banner', $title);
    flash('success', "Hero campaign banner added!");
    header("Location: /admin/banners");
    exit;
}

// Admin Settings & RBAC
if ($uri === '/admin/settings') {
    render('admin/settings/index', ['adminTitle' => 'Settings & Governance'], 'admin');
    exit;
}

// Admin Audit Logs
if ($uri === '/admin/audit-logs') {
    render('admin/audit_logs/index', ['adminTitle' => 'Security Audit Trail'], 'admin');
    exit;
}

// -------------------------------------------------------------
// 404 Fallback
// -------------------------------------------------------------
http_response_code(404);
render('storefront/catalog', ['pageTitle' => '404 - Page Not Found']);

