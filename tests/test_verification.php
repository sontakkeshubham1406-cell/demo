<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../app/Services/CatalogService.php';

use App\Database\Database;
use App\Services\CatalogService;

echo "=== DEMO CATALOG & IMAGE AUDIT ===\n";
$categories = Database::query("SELECT id, name, slug FROM categories ORDER BY id ASC");
$totalProducts = 0;
$totalImages = 0;

foreach ($categories as $cat) {
    $products = Database::query("SELECT id, name, slug FROM products WHERE category_id = ?", [$cat['id']]);
    $catProductCount = count($products);
    $totalProducts += $catProductCount;
    echo "\nCategory [{$cat['id']}] {$cat['name']} ({$catProductCount} products):\n";
    
    foreach ($products as $p) {
        $images = Database::query("SELECT image_url, is_primary FROM product_images WHERE product_id = ? ORDER BY sort_order ASC", [$p['id']]);
        $imgCount = count($images);
        $totalImages += $imgCount;
        echo "  - Product #{$p['id']}: '{$p['name']}' -> {$imgCount} images\n";
    }
}

echo "\n========================================\n";
echo "SUMMARY:\n";
echo "Total Categories: " . count($categories) . "\n";
echo "Total Products: {$totalProducts}\n";
echo "Total Product Images: {$totalImages}\n";

// Verify Admin account
$admin = Database::fetchOne("SELECT id, name, email, role_id, is_active FROM users WHERE email = 'admin@demo.com'");
echo "Super Admin Account: " . ($admin ? "Found (ID {$admin['id']}, Name: {$admin['name']})" : "NOT FOUND") . "\n";

// Verify Customer account
$cust = Database::fetchOne("SELECT id, name, email, role_id, is_active FROM users WHERE email = 'customer@example.com'");
echo "Customer Demo Account: " . ($cust ? "Found (ID {$cust['id']}, Name: {$cust['name']})" : "NOT FOUND") . "\n";

