<?php
/**
 * Master Platform Configuration
 */

// Determine Environment
$isDev = true;

// Database Configuration
define('DB_DRIVER', getenv('DB_DRIVER') ?: 'sqlite'); // 'sqlite' or 'mysql'
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_DATABASE', getenv('DB_DATABASE') ?: __DIR__ . '/../database/ecommerce.sqlite');
define('DB_USERNAME', getenv('DB_USERNAME') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');

// Store Identity & Branding
define('STORE_NAME', 'VELORIS LUXE');
define('STORE_TAGLINE', 'Modern Minimalist Luxury Fashion');
define('STORE_CURRENCY_SYMBOL', '₹');
define('STORE_CURRENCY_CODE', 'INR');
define('STORE_SUPPORT_EMAIL', 'concierge@velorisluxe.com');
define('STORE_PHONE', '+91 98765 43210');
define('STORE_GSTIN', '27AABCU9603R1ZM');

// Financial & Commercial Rules
define('FREE_SHIPPING_THRESHOLD', 999.00);
define('FLAT_SHIPPING_FEE', 79.00);
define('GST_RATE_STANDARD', 0.12); // 12% GST
define('GST_RATE_LOW', 0.05);      // 5% GST for items <= 1000
define('STOCK_RESERVATION_MINUTES', 15); // Temporary stock lock duration

// Razorpay Payment Gateway Credentials (Test Keys by default)
define('RAZORPAY_KEY_ID', getenv('RAZORPAY_KEY_ID') ?: 'rzp_test_eCommerceKey123');
define('RAZORPAY_KEY_SECRET', getenv('RAZORPAY_KEY_SECRET') ?: 'rzp_secret_MockKey456789Secret');
define('RAZORPAY_WEBHOOK_SECRET', getenv('RAZORPAY_WEBHOOK_SECRET') ?: 'whsec_MockWebhookSecret789');

// Base Paths
define('BASE_PATH', dirname(__DIR__));
define('VIEW_PATH', BASE_PATH . '/views');
define('UPLOAD_PATH', BASE_PATH . '/public/uploads');

// Start secure session if not started
if (session_status() === PHP_SESSION_NONE) {
    if (getenv('VERCEL') || isset($_SERVER['VERCEL']) || isset($_ENV['VERCEL'])) {
        $sessDir = sys_get_temp_dir() . '/php_sessions';
        if (!is_dir($sessDir)) {
            @mkdir($sessDir, 0777, true);
        }
        @session_save_path($sessDir);
    }

    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true
    ]);
}

