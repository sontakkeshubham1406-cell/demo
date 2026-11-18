<?php
require 'config/config.php';
require 'database/Database.php';

use App\Database\Database;

$prods = Database::query('SELECT id, name, category_id, (SELECT count(id) from product_images where product_id=products.id) as img_count FROM products');
echo "Total Products: " . count($prods) . "\n";
foreach ($prods as $p) {
    echo $p['id'] . ': ' . $p['name'] . ' (Cat: ' . $p['category_id'] . ', Imgs: ' . $p['img_count'] . ")\n";
}

$cats = Database::query('SELECT id, name, slug, parent_id FROM categories');
echo "\nCategories:\n";
foreach ($cats as $c) {
    $pCount = Database::fetchOne('SELECT count(id) as cnt from products where category_id=?', [$c['id']])['cnt'];
    echo $c['id'] . ': ' . $c['name'] . ' (Slug: ' . $c['slug'] . ', Parent: ' . ($c['parent_id'] ?? 'none') . ', Prods: ' . $pCount . ")\n";
}
