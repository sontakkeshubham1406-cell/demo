<?php
require 'config/config.php';
require 'database/Database.php';

use App\Database\Database;

$imgs = Database::query('SELECT p.id, p.name, (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_img, (SELECT count(id) from product_images where product_id = p.id) as total_imgs FROM products p');
foreach ($imgs as $i) {
    echo 'Product #' . $i['id'] . ': ' . $i['name'] . ' -> Primary: ' . ($i['primary_img'] ? $i['primary_img'] : 'NONE') . ' (Total: ' . $i['total_imgs'] . ")\n";
}

