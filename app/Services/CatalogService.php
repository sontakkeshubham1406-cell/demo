<?php
namespace App\Services;

use App\Database\Database;

class CatalogService
{
    public static function getCategories(bool $activeOnly = true): array
    {
        $where = $activeOnly ? "WHERE is_active = 1" : "";
        $categories = Database::query("
            SELECT * FROM categories 
            {$where} 
            ORDER BY display_order ASC, name ASC
        ");

        // Build tree: parent categories with nested children
        $tree = [];
        $lookup = [];

        foreach ($categories as $cat) {
            $cat['children'] = [];
            $lookup[$cat['id']] = $cat;
        }

        foreach ($lookup as $id => &$cat) {
            if ($cat['parent_id'] !== null && isset($lookup[$cat['parent_id']])) {
                $lookup[$cat['parent_id']]['children'][] = &$cat;
            } else {
                $tree[] = &$cat;
            }
        }

        return $tree;
    }

    public static function getFilterOptions(): array
    {
        return [
            'sizes' => Database::query("SELECT * FROM sizes ORDER BY sort_order ASC"),
            'colors' => Database::query("SELECT * FROM colors ORDER BY name ASC"),
            'brands' => Database::query("SELECT * FROM brands WHERE is_active = 1 ORDER BY name ASC"),
            'categories' => Database::query("SELECT id, name, slug FROM categories WHERE is_active = 1 ORDER BY name ASC")
        ];
    }

    public static function getProducts(array $filters = [], int $limit = 24, int $offset = 0): array
    {
        $where = ["p.is_active = 1"];
        $params = [];

        // Category filter (handles slug or ID, including child categories)
        if (!empty($filters['category'])) {
            $cat = Database::fetchOne("SELECT id FROM categories WHERE slug = ? OR id = ?", [$filters['category'], $filters['category']]);
            if ($cat) {
                // Find all child category IDs
                $children = Database::query("SELECT id FROM categories WHERE parent_id = ?", [$cat['id']]);
                $catIds = array_merge([$cat['id']], array_column($children, 'id'));
                $placeholders = implode(',', array_fill(0, count($catIds), '?'));
                $where[] = "p.category_id IN ({$placeholders})";
                $params = array_merge($params, $catIds);
            }
        }

        // Search keyword
        if (!empty($filters['search'])) {
            $kw = '%' . trim($filters['search']) . '%';
            $where[] = "(p.name LIKE ? OR p.short_description LIKE ? OR p.fabric_composition LIKE ?)";
            $params[] = $kw;
            $params[] = $kw;
            $params[] = $kw;
        }

        // Price range
        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $where[] = "COALESCE(p.discount_price, p.base_price) >= ?";
            $params[] = (float) $filters['min_price'];
        }
        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $where[] = "COALESCE(p.discount_price, p.base_price) <= ?";
            $params[] = (float) $filters['max_price'];
        }

        // Brand filter
        if (!empty($filters['brand'])) {
            $where[] = "p.brand_id = ?";
            $params[] = (int) $filters['brand'];
        }

        // Size filter
        if (!empty($filters['size'])) {
            $sizeIds = is_array($filters['size']) ? $filters['size'] : [$filters['size']];
            $placeholders = implode(',', array_fill(0, count($sizeIds), '?'));
            $where[] = "EXISTS (SELECT 1 FROM product_variants pv WHERE pv.product_id = p.id AND pv.size_id IN ({$placeholders}))";
            $params = array_merge($params, $sizeIds);
        }

        // Color filter
        if (!empty($filters['color'])) {
            $colorIds = is_array($filters['color']) ? $filters['color'] : [$filters['color']];
            $placeholders = implode(',', array_fill(0, count($colorIds), '?'));
            $where[] = "EXISTS (SELECT 1 FROM product_variants pv WHERE pv.product_id = p.id AND pv.color_id IN ({$placeholders}))";
            $params = array_merge($params, $colorIds);
        }

        // In Stock filter
        if (!empty($filters['in_stock'])) {
            $where[] = "EXISTS (
                SELECT 1 FROM product_variants pv 
                JOIN inventory inv ON pv.id = inv.variant_id 
                WHERE pv.product_id = p.id AND (inv.stock_quantity - inv.reserved_quantity) > 0
            )";
        }

        // Sorting
        $orderBy = "p.created_at DESC";
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price_asc':
                    $orderBy = "COALESCE(p.discount_price, p.base_price) ASC";
                    break;
                case 'price_desc':
                    $orderBy = "COALESCE(p.discount_price, p.base_price) DESC";
                    break;
                case 'rating':
                    $orderBy = "rating_avg DESC";
                    break;
                case 'popular':
                    $orderBy = "p.is_trending DESC, p.created_at DESC";
                    break;
                case 'newest':
                default:
                    $orderBy = "p.created_at DESC";
                    break;
            }
        }

        $whereSql = implode(' AND ', $where);

        $sql = "
            SELECT 
                p.*,
                b.name as brand_name,
                c.name as category_name,
                c.slug as category_slug,
                (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
                (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 0 ORDER BY sort_order ASC LIMIT 1) as secondary_image,
                (SELECT AVG(rating) FROM reviews WHERE product_id = p.id AND is_approved = 1) as rating_avg,
                (SELECT COUNT(id) FROM reviews WHERE product_id = p.id AND is_approved = 1) as rating_count
            FROM products p
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE {$whereSql}
            ORDER BY {$orderBy}
            LIMIT {$limit} OFFSET {$offset}
        ";

        $products = Database::query($sql, $params);

        // Fetch color swatches for each product card
        foreach ($products as &$p) {
            $p['available_colors'] = Database::query("
                SELECT DISTINCT c.id, c.name, c.hex_code 
                FROM product_variants pv
                JOIN colors c ON pv.color_id = c.id
                WHERE pv.product_id = ?
            ", [$p['id']]);

            $p['available_sizes'] = Database::query("
                SELECT DISTINCT s.id, s.name, s.code 
                FROM product_variants pv
                JOIN sizes s ON pv.size_id = s.id
                WHERE pv.product_id = ?
                ORDER BY s.sort_order ASC
            ", [$p['id']]);
        }

        return $products;
    }

    public static function getProductBySlug(string $slug): ?array
    {
        $product = Database::fetchOne("
            SELECT 
                p.*,
                b.name as brand_name,
                b.slug as brand_slug,
                c.name as category_name,
                c.slug as category_slug,
                (SELECT AVG(rating) FROM reviews WHERE product_id = p.id AND is_approved = 1) as rating_avg,
                (SELECT COUNT(id) FROM reviews WHERE product_id = p.id AND is_approved = 1) as rating_count
            FROM products p
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.slug = ? AND p.is_active = 1
        ", [$slug]);

        if (!$product) {
            return null;
        }

        // Product Images
        $product['images'] = Database::query("
            SELECT * FROM product_images 
            WHERE product_id = ? 
            ORDER BY is_primary DESC, sort_order ASC
        ", [$product['id']]);

        // Variants Matrix with live stock
        $product['variants'] = Database::query("
            SELECT 
                pv.*,
                s.name as size_name,
                s.code as size_code,
                s.sort_order as size_sort,
                c.name as color_name,
                c.hex_code as color_hex,
                COALESCE(inv.stock_quantity, 0) as stock_quantity,
                COALESCE(inv.reserved_quantity, 0) as reserved_quantity,
                (COALESCE(inv.stock_quantity, 0) - COALESCE(inv.reserved_quantity, 0)) as available_to_sell
            FROM product_variants pv
            JOIN sizes s ON pv.size_id = s.id
            JOIN colors c ON pv.color_id = c.id
            LEFT JOIN inventory inv ON pv.id = inv.variant_id
            WHERE pv.product_id = ? AND pv.is_active = 1
            ORDER BY c.name ASC, s.sort_order ASC
        ", [$product['id']]);

        // Distinct colors & sizes for quick selection UI
        $colors = [];
        $sizes = [];
        foreach ($product['variants'] as $v) {
            $colors[$v['color_id']] = [
                'id' => $v['color_id'],
                'name' => $v['color_name'],
                'hex_code' => $v['color_hex']
            ];
            $sizes[$v['size_id']] = [
                'id' => $v['size_id'],
                'name' => $v['size_name'],
                'code' => $v['size_code'],
                'sort_order' => $v['size_sort']
            ];
        }
        $product['colors'] = array_values($colors);
        usort($sizes, fn($a, $b) => $a['sort_order'] <=> $b['sort_order']);
        $product['sizes'] = array_values($sizes);

        // Reviews
        $product['reviews'] = Database::query("
            SELECT r.*, u.name as reviewer_name 
            FROM reviews r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.product_id = ? AND r.is_approved = 1
            ORDER BY r.created_at DESC
        ", [$product['id']]);

        // Related Products
        $product['related'] = self::getProducts([
            'category' => $product['category_id']
        ], 4);

        return $product;
    }
}

