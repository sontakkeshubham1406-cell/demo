<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../app/Services/CatalogService.php';

use App\Database\Database;
use App\Services\CatalogService;

echo "--- CATEGORY TABLE ---\n";
$categories = Database::query("SELECT id, name, slug, parent_id FROM categories");
foreach ($categories as $c) {
    echo "ID: {$c['id']}, Name: {$c['name']}, Slug: {$c['slug']}, Parent: " . ($c['parent_id'] ?? 'null') . "\n";
}

echo "\n--- TESTING CATALOG QUERIES ---\n";
foreach ($categories as $c) {
    $products = CatalogService::getProducts(['category' => $c['slug']]);
    echo "Slug: '{$c['slug']}' -> " . count($products) . " products returned.\n";
}
