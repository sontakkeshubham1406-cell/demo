<?php
/**
 * Master Database Seeder - Expanded High-Resolution Luxury Apparel Catalog
 * 18 Unique Products across Men, Women, Kids & Accessories with 3-4 High-Res Images per Product.
 */

namespace App\Database;

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';

$pdo = Database::getConnection();

echo "Starting full catalog expansion...\n";

// 1. Roles & Permissions
$roles = [
    ['name' => 'Super Admin', 'slug' => 'super-admin', 'description' => 'Full control over all platform modules and financial credentials'],
    ['name' => 'Store Admin', 'slug' => 'store-admin', 'description' => 'Daily operational management of catalog, orders, and promotions'],
    ['name' => 'Product Manager', 'slug' => 'product-manager', 'description' => 'Catalog curation, variants, categories, and brands'],
    ['name' => 'Order Manager', 'slug' => 'order-manager', 'description' => 'Order fulfillment, packing, shipping, and AWB dispatch'],
    ['name' => 'Inventory Manager', 'slug' => 'inventory-manager', 'description' => 'Stock adjustments, warehouse counts, and inventory audit'],
    ['name' => 'Customer Support', 'slug' => 'customer-support', 'description' => 'Customer profiles, order tracking, and return triage'],
];

foreach ($roles as $role) {
    $existing = Database::fetchOne("SELECT id FROM roles WHERE slug = ?", [$role['slug']]);
    if (!$existing) {
        Database::insert('roles', $role);
    }
}

// 2. Users (Admin Staff & Customer)
$adminPassword = password_hash('Admin@123', PASSWORD_BCRYPT);
$customerPassword = password_hash('Customer@123', PASSWORD_BCRYPT);

$users = [
    [
        'role_id' => 1, // Super Admin
        'name' => 'Alexander Vance',
        'email' => 'admin@velorisluxe.com',
        'phone' => '+919876543200',
        'password_hash' => $adminPassword,
        'is_active' => 1,
    ],
    [
        'role_id' => 3, // Product Manager
        'name' => 'Elena Rostova',
        'email' => 'manager@velorisluxe.com',
        'phone' => '+919876543201',
        'password_hash' => $adminPassword,
        'is_active' => 1,
    ],
    [
        'role_id' => null, // Customer
        'name' => 'Aarav Sharma',
        'email' => 'customer@example.com',
        'phone' => '+919876543210',
        'password_hash' => $customerPassword,
        'is_active' => 1,
    ]
];

foreach ($users as $user) {
    $existing = Database::fetchOne("SELECT id FROM users WHERE email = ?", [$user['email']]);
    if (!$existing) {
        $userId = Database::insert('users', $user);
        if ($user['role_id'] === null) {
            Database::insert('user_addresses', [
                'user_id' => $userId,
                'address_type' => 'BOTH',
                'recipient_name' => 'Aarav Sharma',
                'phone' => '+919876543210',
                'address_line1' => 'Flat 402, Signature Heights',
                'address_line2' => 'Bandra West, Hill Road',
                'landmark' => 'Near Mehboob Studio',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'country' => 'India',
                'postal_code' => '400050',
                'is_default' => 1
            ]);
        }
    } else {
        // Ensure password matches
        Database::update('users', ['password_hash' => $user['password_hash']], 'id = :id', ['id' => $existing['id']]);
    }
}

// 3. Brands
$brands = [
    ['name' => 'VELORIS Atelier', 'slug' => 'veloris-atelier', 'description' => 'Flagship minimalist luxury fashion handcrafted from noble textiles.'],
    ['name' => 'Linen & Co', 'slug' => 'linen-and-co', 'description' => 'Pure breathable European linen tailored for effortless living.'],
    ['name' => 'Noir Studio', 'slug' => 'noir-studio', 'description' => 'Contemporary urban tailoring with sharp Japanese silhouettes.'],
    ['name' => 'Celestial Silk', 'slug' => 'celestial-silk', 'description' => 'Heritage handwoven Mulberry silks and festive ethnic wear.'],
    ['name' => 'Petit Luxe', 'slug' => 'petit-luxe', 'description' => 'Artisanal organic cotton childrenswear tailored for sensitive skin.'],
    ['name' => 'Cuoio Atelier', 'slug' => 'cuoio-atelier', 'description' => 'Tuscan vegetable-tanned leather goods and hand-rolled silks.']
];

foreach ($brands as $brand) {
    if (!Database::fetchOne("SELECT id FROM brands WHERE slug = ?", [$brand['slug']])) {
        Database::insert('brands', $brand);
    }
}

// 4. Categories & Taxonomy (All Departments & Subcategories)
$categories = [
    // Top-level Departments
    ['name' => "Men's Wear", 'slug' => 'men', 'parent_id' => null, 'display_order' => 1, 'image_url' => 'https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?auto=format&fit=crop&w=800&q=80'],
    ['name' => "Women's Wear", 'slug' => 'women', 'parent_id' => null, 'display_order' => 2, 'image_url' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=800&q=80'],
    ['name' => "Kids' Wear", 'slug' => 'kids', 'parent_id' => null, 'display_order' => 3, 'image_url' => 'https://images.unsplash.com/photo-1622290291468-a28f7a7dc6a8?auto=format&fit=crop&w=800&q=80'],
    ['name' => "Accessories", 'slug' => 'accessories', 'parent_id' => null, 'display_order' => 4, 'image_url' => 'https://images.unsplash.com/photo-1523293182086-7651a899d37f?auto=format&fit=crop&w=800&q=80'],

    // Men Subcategories (Parent ID 1)
    ['name' => 'Casual Shirts', 'slug' => 'men-shirts', 'parent_id' => 1, 'display_order' => 1, 'image_url' => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?auto=format&fit=crop&w=800&q=80'],
    ['name' => 'T-Shirts & Polos', 'slug' => 'men-tshirts', 'parent_id' => 1, 'display_order' => 2, 'image_url' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=800&q=80'],
    ['name' => 'Trousers & Chinos', 'slug' => 'men-trousers', 'parent_id' => 1, 'display_order' => 3, 'image_url' => 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?auto=format&fit=crop&w=800&q=80'],
    ['name' => 'Jeans & Denim', 'slug' => 'men-jeans', 'parent_id' => 1, 'display_order' => 4, 'image_url' => 'https://images.unsplash.com/photo-1542272604-780c96856592?auto=format&fit=crop&w=800&q=80'],

    // Women Subcategories (Parent ID 2)
    ['name' => 'Dresses & Jumpsuits', 'slug' => 'women-dresses', 'parent_id' => 2, 'display_order' => 1, 'image_url' => 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?auto=format&fit=crop&w=800&q=80'],
    ['name' => 'Ethnic & Festive', 'slug' => 'women-ethnic', 'parent_id' => 2, 'display_order' => 2, 'image_url' => 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=800&q=80'],
    ['name' => 'Tops & Blouses', 'slug' => 'women-tops', 'parent_id' => 2, 'display_order' => 3, 'image_url' => 'https://images.unsplash.com/photo-1564257631407-4deb1f99d992?auto=format&fit=crop&w=800&q=80'],

    // Kids Subcategories (Parent ID 3)
    ['name' => "Boys' Collection", 'slug' => 'kids-boys', 'parent_id' => 3, 'display_order' => 1, 'image_url' => 'https://images.unsplash.com/photo-1519457431-44ccd64a579b?auto=format&fit=crop&w=800&q=80'],
    ['name' => "Girls' Collection", 'slug' => 'kids-girls', 'parent_id' => 3, 'display_order' => 2, 'image_url' => 'https://images.unsplash.com/photo-1518831959646-742c3a14ebf7?auto=format&fit=crop&w=800&q=80'],

    // Accessories Subcategories (Parent ID 4)
    ['name' => 'Leather Belts & Wallets', 'slug' => 'acc-leather', 'parent_id' => 4, 'display_order' => 1, 'image_url' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&q=80'],
    ['name' => 'Silk Scarves & Pocket Squares', 'slug' => 'acc-scarves', 'parent_id' => 4, 'display_order' => 2, 'image_url' => 'https://images.unsplash.com/photo-1601924994987-69e26d50dc26?auto=format&fit=crop&w=800&q=80'],
];

foreach ($categories as $cat) {
    $existing = Database::fetchOne("SELECT id FROM categories WHERE slug = ?", [$cat['slug']]);
    if (!$existing) {
        Database::insert('categories', $cat);
    } else {
        Database::update('categories', [
            'name' => $cat['name'],
            'image_url' => $cat['image_url'],
            'parent_id' => $cat['parent_id']
        ], 'id = :id', ['id' => $existing['id']]);
    }
}

// 5. Sizes & Colors Master
$sizes = [
    ['name' => 'XS', 'code' => 'SIZE_XS', 'sort_order' => 1, 'category_group' => 'TOPWEAR'],
    ['name' => 'S', 'code' => 'SIZE_S', 'sort_order' => 2, 'category_group' => 'TOPWEAR'],
    ['name' => 'M', 'code' => 'SIZE_M', 'sort_order' => 3, 'category_group' => 'TOPWEAR'],
    ['name' => 'L', 'code' => 'SIZE_L', 'sort_order' => 4, 'category_group' => 'TOPWEAR'],
    ['name' => 'XL', 'code' => 'SIZE_XL', 'sort_order' => 5, 'category_group' => 'TOPWEAR'],
    ['name' => 'XXL', 'code' => 'SIZE_XXL', 'sort_order' => 6, 'category_group' => 'TOPWEAR'],
];

foreach ($sizes as $s) {
    if (!Database::fetchOne("SELECT id FROM sizes WHERE code = ?", [$s['code']])) {
        Database::insert('sizes', $s);
    }
}

$colors = [
    ['name' => 'Obsidian Black', 'hex_code' => '#111111'],
    ['name' => 'Alabaster White', 'hex_code' => '#F8F9FA'],
    ['name' => 'Olive Moss', 'hex_code' => '#556B2F'],
    ['name' => 'Midnight Navy', 'hex_code' => '#0A192F'],
    ['name' => 'Sand Beige', 'hex_code' => '#D2B48C'],
    ['name' => 'Terracotta Rust', 'hex_code' => '#CC4E46'],
    ['name' => 'Cognac Brown', 'hex_code' => '#9A461E'],
    ['name' => 'Powder Blue', 'hex_code' => '#B0E0E6'],
];

foreach ($colors as $c) {
    if (!Database::fetchOne("SELECT id FROM colors WHERE name = ?", [$c['name']])) {
        Database::insert('colors', $c);
    }
}

// Map Category Slugs to IDs
$catMap = [];
foreach (Database::query("SELECT id, slug FROM categories") as $row) {
    $catMap[$row['slug']] = $row['id'];
}
$brandMap = [];
foreach (Database::query("SELECT id, slug FROM brands") as $row) {
    $brandMap[$row['slug']] = $row['id'];
}

// 6. Complete Apparel Catalog: 18 Products with 3-4 High-Res Images Each
$catalog = [
    // -------------------------------------------------------------
    // MEN'S WEAR: SHIRTS
    // -------------------------------------------------------------
    [
        'category_id' => $catMap['men-shirts'] ?? 5,
        'brand_id' => $brandMap['linen-and-co'] ?? 2,
        'name' => 'Tailored Italian Linen Relaxed Shirt',
        'slug' => 'tailored-italian-linen-shirt',
        'short_description' => 'Bespoke garment-dyed European linen crafted for breathable comfort in warm climates.',
        'description' => 'Engineered with 100% fine French flax linen, this relaxed shirt features a softly rolled resort collar, genuine mother-of-pearl buttons, and a clean French placket. Pre-washed for an irresistibly soft hand feel that drapes naturally.',
        'fabric_composition' => '100% Normandy Flax Linen',
        'wash_care_instructions' => 'Machine wash cold on gentle cycle. Line dry in shade. Warm iron while slightly damp.',
        'model_specifications' => 'Model is 6\'1" (185cm), chest 39", wearing Size M.',
        'base_price' => 2999.00,
        'discount_price' => 2499.00,
        'is_featured' => 1,
        'is_trending' => 1,
        'colors' => [2, 3, 4], // White, Olive, Navy
        'sizes' => [2, 3, 4, 5], // S, M, L, XL
        'images' => [
            'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1589310243389-96a5483213a8?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1607345366928-199ea26cfe3e?auto=format&fit=crop&w=1000&q=80'
        ]
    ],
    [
        'category_id' => $catMap['men-shirts'] ?? 5,
        'brand_id' => $brandMap['veloris-atelier'] ?? 1,
        'name' => 'Classic Oxford Cotton Button-Down',
        'slug' => 'classic-oxford-cotton-button-down',
        'short_description' => 'Two-ply combed Supima cotton woven in a durable royal oxford basketweave.',
        'description' => 'An essential sartorial foundation. Finished with an unlined button-down collar that creates a natural S-curve roll, box pleat at the back for shoulder ease, and reinforced gussets.',
        'fabric_composition' => '100% Two-Ply Combed Supima Cotton (80s)',
        'wash_care_instructions' => 'Machine wash warm with like colors. Hang dry or light tumble dry.',
        'model_specifications' => 'Model is 6\'0", chest 40", wearing Size M.',
        'base_price' => 2499.00,
        'discount_price' => 1999.00,
        'is_featured' => 0,
        'is_trending' => 1,
        'colors' => [2, 4], // White, Navy
        'sizes' => [2, 3, 4, 5],
        'images' => [
            'https://images.unsplash.com/photo-1598033129183-c4f50c736f10?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1620012253295-c15c429fccf8?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1603252109303-2751441dd157?auto=format&fit=crop&w=1000&q=80'
        ]
    ],

    // -------------------------------------------------------------
    // MEN'S WEAR: T-SHIRTS & POLOS
    // -------------------------------------------------------------
    [
        'category_id' => $catMap['men-tshirts'] ?? 6,
        'brand_id' => $brandMap['veloris-atelier'] ?? 1,
        'name' => 'Heavyweight 280 GSM Structured Boxy Tee',
        'slug' => 'heavyweight-structured-boxy-tee',
        'short_description' => 'Ultra-dense combed organic cotton with dropped shoulders and a durable ribbed neckline.',
        'description' => 'An architectural streetwear staple cut from heavyweight 280 GSM zero-twist cotton. Designed with a structured boxy silhouette that holds its form, pre-shrunk to guarantee perpetual fit wear after wear.',
        'fabric_composition' => '100% GOTS Certified Organic Cotton (280 GSM)',
        'wash_care_instructions' => 'Machine wash cold inside out. Tumble dry low or hang dry. Do not bleach.',
        'model_specifications' => 'Model is 6\'0", wearing Size L for an oversized streetwear drape.',
        'base_price' => 1699.00,
        'discount_price' => 1299.00,
        'is_featured' => 1,
        'is_trending' => 1,
        'colors' => [1, 2, 5], // Black, White, Sand
        'sizes' => [1, 2, 3, 4, 5],
        'images' => [
            'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1576566588028-4147f3842f27?auto=format&fit=crop&w=1000&q=80'
        ]
    ],
    [
        'category_id' => $catMap['men-tshirts'] ?? 6,
        'brand_id' => $brandMap['noir-studio'] ?? 3,
        'name' => 'Mercerized Supima Cotton Knit Polo',
        'slug' => 'mercerized-supima-cotton-knit-polo',
        'short_description' => 'Silky mercerized finish with an open Johnny collar and ribbed hems.',
        'description' => 'Elevated resort leisure. Spun from ultra-long-staple American Supima yarns with a double-mercerized liquid luster. Fluid drape without clinging.',
        'fabric_composition' => '100% Double-Mercerized Supima Cotton',
        'wash_care_instructions' => 'Hand wash or dry clean. Dry flat in shade. Do not wring.',
        'model_specifications' => 'Model is 6\'2", chest 38", wearing Size M.',
        'base_price' => 2299.00,
        'discount_price' => 1899.00,
        'is_featured' => 0,
        'is_trending' => 1,
        'colors' => [1, 4], // Black, Navy
        'sizes' => [2, 3, 4, 5],
        'images' => [
            'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1625910513413-5825a07c4ba2?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1586363104862-3a5e2ab60d99?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1581655353564-df123a1eb820?auto=format&fit=crop&w=1000&q=80'
        ]
    ],

    // -------------------------------------------------------------
    // MEN'S WEAR: TROUSERS & CHINOS
    // -------------------------------------------------------------
    [
        'category_id' => $catMap['men-trousers'] ?? 7,
        'brand_id' => $brandMap['noir-studio'] ?? 3,
        'name' => 'Double-Pleated Wide Leg Wool-Blend Trousers',
        'slug' => 'double-pleated-wide-leg-trousers',
        'short_description' => 'Architectural double pleats and a relaxed tapered leg offer relaxed sartorial elegance.',
        'description' => 'Crafted in Portugal from high-twist tropical wool blended with recycled viscose. Features internal waist adjusters, horn button closure, deep slash pockets, and a fluid drape.',
        'fabric_composition' => '55% Tropical Wool, 45% Eco-Vero Viscose',
        'wash_care_instructions' => 'Dry clean only. Steam gently.',
        'model_specifications' => 'Model is 6\'2", waist 31", wearing Size M.',
        'base_price' => 4499.00,
        'discount_price' => 3699.00,
        'is_featured' => 1,
        'is_trending' => 1,
        'colors' => [1, 5], // Black, Sand Beige
        'sizes' => [2, 3, 4, 5],
        'images' => [
            'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1509551388413-e18d0ac5d495?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1479064555552-3ef4979f8908?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=1000&q=80'
        ]
    ],
    [
        'category_id' => $catMap['men-trousers'] ?? 7,
        'brand_id' => $brandMap['veloris-atelier'] ?? 1,
        'name' => 'Tailored Slim-Fit Cotton Chinos',
        'slug' => 'tailored-slim-fit-cotton-chinos',
        'short_description' => 'Micro-sanded stretch twill with clean welt rear pockets.',
        'description' => 'A sharp modern silhouette cut with 2% elastane for seamless daily mobility. Garment-washed to achieve a velvety peach-skin finish.',
        'fabric_composition' => '98% Cotton, 2% Spandex',
        'wash_care_instructions' => 'Machine wash cold inside out. Tumble dry gentle.',
        'model_specifications' => 'Model is 6\'1", waist 32", wearing Size M.',
        'base_price' => 2799.00,
        'discount_price' => 2299.00,
        'is_featured' => 0,
        'is_trending' => 0,
        'colors' => [5, 4], // Sand, Navy
        'sizes' => [2, 3, 4, 5],
        'images' => [
            'https://images.unsplash.com/photo-1624378440847-4a64ee1a889d?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1517445312882-bc9910d016b7?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1473966968600-fa801b869a1a?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1548883354-7622d03aca27?auto=format&fit=crop&w=1000&q=80'
        ]
    ],

    // -------------------------------------------------------------
    // MEN'S WEAR: JEANS & DENIM
    // -------------------------------------------------------------
    [
        'category_id' => $catMap['men-jeans'] ?? 8,
        'brand_id' => $brandMap['noir-studio'] ?? 3,
        'name' => '14oz Kurabo Japanese Selvedge Raw Denim',
        'slug' => 'kurabo-japanese-selvedge-denim',
        'short_description' => 'Authentic shuttle-loom Japanese selvedge denim that develops personalized fading over time.',
        'description' => 'Milled in Okayama, Japan on vintage shuttle looms. Rigid 14oz indigo denim with classic red-line selvedge ID, custom matte black hardware, and hidden back pocket rivets.',
        'fabric_composition' => '100% Ring-Spun Okayama Cotton (14oz)',
        'wash_care_instructions' => 'Wear raw for 6 months before first cold soak. Always hang dry.',
        'model_specifications' => 'Model is 6\'1", wearing Size M (32 Waist).',
        'base_price' => 4999.00,
        'discount_price' => 4199.00,
        'is_featured' => 1,
        'is_trending' => 1,
        'colors' => [4, 1], // Navy Indigo, Black
        'sizes' => [2, 3, 4],
        'images' => [
            'https://images.unsplash.com/photo-1542272604-780c96856592?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1560243563-062bfc001d68?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1582552938357-32b906df40cb?auto=format&fit=crop&w=1000&q=80'
        ]
    ],
    [
        'category_id' => $catMap['men-jeans'] ?? 8,
        'brand_id' => $brandMap['noir-studio'] ?? 3,
        'name' => 'Vintage Washed Relaxed Straight Jeans',
        'slug' => 'vintage-washed-relaxed-straight-jeans',
        'short_description' => 'Subtle whiskering and authentic fading on 12.5oz medium-blue denim.',
        'description' => 'A tribute to 90s vintage cuts. Roomy through the thigh with a straight leg opening designed to sit gracefully over boots or sneakers.',
        'fabric_composition' => '100% Cotton Denim (12.5oz)',
        'wash_care_instructions' => 'Machine wash cold inside out with mild detergent.',
        'model_specifications' => 'Model is 6\'0", wearing Size M.',
        'base_price' => 3799.00,
        'discount_price' => 2999.00,
        'is_featured' => 0,
        'is_trending' => 1,
        'colors' => [4],
        'sizes' => [2, 3, 4, 5],
        'images' => [
            'https://images.unsplash.com/photo-1511105612320-2e62a04dd044?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1516257984-b1b4d707412e?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1576995853123-5a10305d93c0?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1584370848010-d7fe6bc767ec?auto=format&fit=crop&w=1000&q=80'
        ]
    ],

    // -------------------------------------------------------------
    // WOMEN'S WEAR: DRESSES & JUMPSUITS
    // -------------------------------------------------------------
    [
        'category_id' => $catMap['women-dresses'] ?? 9,
        'brand_id' => $brandMap['veloris-atelier'] ?? 1,
        'name' => 'Pure Mulberry Silk Bias-Cut Midi Slip Dress',
        'slug' => 'silk-bias-cut-midi-dress',
        'short_description' => 'Effortlessly sensual bias-cut silk dress that glides gracefully along the natural contours of the body.',
        'description' => 'Woven from 22-Momme Grade 6A Mulberry silk, this slip dress features a cowl neckline, delicate adjustable spaghetti straps, and a low scooped back. Unlined for ethereal lightness.',
        'fabric_composition' => '100% Grade 6A Pure Mulberry Silk (22 Momme)',
        'wash_care_instructions' => 'Dry clean recommended or hand wash cold with silk detergent. Iron inside out on cool.',
        'model_specifications' => 'Model is 5\'9" (175cm), bust 33", waist 25", wearing Size S.',
        'base_price' => 5999.00,
        'discount_price' => 4799.00,
        'is_featured' => 1,
        'is_trending' => 1,
        'colors' => [1, 6], // Black, Terracotta
        'sizes' => [1, 2, 3, 4],
        'images' => [
            'https://images.unsplash.com/photo-1595777457583-95e059d581b8?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?auto=format&fit=crop&w=1000&q=80'
        ]
    ],
    [
        'category_id' => $catMap['women-dresses'] ?? 9,
        'brand_id' => $brandMap['linen-and-co'] ?? 2,
        'name' => 'Belted French Linen Wrap Maxi Dress',
        'slug' => 'belted-french-linen-wrap-maxi-dress',
        'short_description' => 'Full-skirted wrap silhouette with an exaggerated sash and hidden side seam pockets.',
        'description' => 'Sun-drenched coastal elegance. Cut from medium-weight French linen that softens with laundering. Fully adjustable wrap closure creates an empowering hourglass fit.',
        'fabric_composition' => '100% European Flax Linen',
        'wash_care_instructions' => 'Machine wash cold on delicate cycle. Line dry in shade.',
        'model_specifications' => 'Model is 5\'10", bust 34", wearing Size S.',
        'base_price' => 4799.00,
        'discount_price' => 3999.00,
        'is_featured' => 1,
        'is_trending' => 0,
        'colors' => [5, 2], // Sand, White
        'sizes' => [1, 2, 3, 4],
        'images' => [
            'https://images.unsplash.com/photo-1502716119720-b23a93e5fe1b?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1525507119028-ed4c629a60a3?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=1000&q=80'
        ]
    ],

    // -------------------------------------------------------------
    // WOMEN'S WEAR: ETHNIC & FESTIVE
    // -------------------------------------------------------------
    [
        'category_id' => $catMap['women-ethnic'] ?? 10,
        'brand_id' => $brandMap['celestial-silk'] ?? 4,
        'name' => 'Handcrafted Chanderi Silk Zari Embroidered Kurta Set',
        'slug' => 'chanderi-silk-zari-kurta-set',
        'short_description' => 'Artisanal Chanderi silk with delicate golden Zari threadwork, paired with matching straight-fit pants.',
        'description' => 'A tribute to royal Indian craftsmanship. Woven by master weavers in Madhya Pradesh, adorned with hand-embroidered floral buttis along the yoke and sleeve cuffs. Includes pure cotton mulmul lining.',
        'fabric_composition' => 'Pure Chanderi Silk with 100% Mulmul Cotton Lining',
        'wash_care_instructions' => 'Dry clean only. Store wrapped in muslin cloth.',
        'model_specifications' => 'Model is 5\'8", wearing Size S.',
        'base_price' => 6499.00,
        'discount_price' => 4999.00,
        'is_featured' => 1,
        'is_trending' => 1,
        'colors' => [6, 2], // Terracotta, White
        'sizes' => [2, 3, 4, 5],
        'images' => [
            'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1583391733956-3750e0ff4e8b?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1617627143750-d86bc21e42bb?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1583391733975-0104e76a0841?auto=format&fit=crop&w=1000&q=80'
        ]
    ],
    [
        'category_id' => $catMap['women-ethnic'] ?? 10,
        'brand_id' => $brandMap['celestial-silk'] ?? 4,
        'name' => 'Banarasi Georgette Handwoven Festive Anarkali',
        'slug' => 'banarasi-georgette-handwoven-anarkali',
        'short_description' => 'Flowing 32-kali anarkali gown in rich emerald georgette with antique gold borders.',
        'description' => 'Masterfully hand-loomed in Varanasi using pure silk georgette yarns. Finished with an organza scalloped dupatta and churidar trousers.',
        'fabric_composition' => 'Pure Silk Georgette with Zari Motifs',
        'wash_care_instructions' => 'Dry clean only. Low heat iron.',
        'model_specifications' => 'Model is 5\'9", wearing Size M.',
        'base_price' => 8999.00,
        'discount_price' => 7499.00,
        'is_featured' => 0,
        'is_trending' => 1,
        'colors' => [3, 6], // Olive, Terracotta
        'sizes' => [2, 3, 4],
        'images' => [
            'https://images.unsplash.com/photo-1617627143750-d86bc21e42bb?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1583391733956-3750e0ff4e8b?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1583391733975-0104e76a0841?auto=format&fit=crop&w=1000&q=80'
        ]
    ],

    // -------------------------------------------------------------
    // WOMEN'S WEAR: TOPS & BLOUSES
    // -------------------------------------------------------------
    [
        'category_id' => $catMap['women-tops'] ?? 11,
        'brand_id' => $brandMap['veloris-atelier'] ?? 1,
        'name' => 'Silk Georgette Tie-Neck Lavallière Blouse',
        'slug' => 'silk-georgette-tie-neck-blouse',
        'short_description' => 'Ethereal sheer georgette with French cuffs and an elegant neck scarf tie.',
        'description' => 'Bespoke romantic tailoring. Features concealed mother-of-pearl buttons, gentle shirring across the back yoke, and billowing bishop sleeves.',
        'fabric_composition' => '100% Silk Georgette',
        'wash_care_instructions' => 'Dry clean or gentle hand wash cold with silk detergent.',
        'model_specifications' => 'Model is 5\'8", wearing Size S.',
        'base_price' => 3899.00,
        'discount_price' => 3199.00,
        'is_featured' => 1,
        'is_trending' => 1,
        'colors' => [2, 1], // White, Black
        'sizes' => [1, 2, 3, 4],
        'images' => [
            'https://images.unsplash.com/photo-1564257631407-4deb1f99d992?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1551803091-e20673f15770?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1534126511673-b6899657816a?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1508427953056-b00b8d78ebf5?auto=format&fit=crop&w=1000&q=80'
        ]
    ],
    [
        'category_id' => $catMap['women-tops'] ?? 11,
        'brand_id' => $brandMap['noir-studio'] ?? 3,
        'name' => 'Ribbed Cashmere Knit Mock-Neck Top',
        'slug' => 'ribbed-cashmere-knit-mock-neck-top',
        'short_description' => 'Featherweight 16-gauge Mongolian cashmere with sculptured ribbed contouring.',
        'description' => 'Second-skin luxury. Seamlessly circular-knitted for a smooth hug against the skin. Perfect for layering under tailored blazers or silk skirts.',
        'fabric_composition' => '70% Fine Merino, 30% Grade-A Mongolian Cashmere',
        'wash_care_instructions' => 'Hand wash cold with wool wash. Dry flat on towel.',
        'model_specifications' => 'Model is 5\'9", wearing Size S.',
        'base_price' => 4199.00,
        'discount_price' => 3499.00,
        'is_featured' => 0,
        'is_trending' => 1,
        'colors' => [5, 1], // Sand, Black
        'sizes' => [1, 2, 3, 4],
        'images' => [
            'https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1503342394128-c104d54dba01?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1516762689617-e1cffcef479d?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1485968579580-b6d095142e6e?auto=format&fit=crop&w=1000&q=80'
        ]
    ],

    // -------------------------------------------------------------
    // KIDS' WEAR: BOYS & GIRLS
    // -------------------------------------------------------------
    [
        'category_id' => $catMap['kids-boys'] ?? $catMap['kids'] ?? 3,
        'brand_id' => $brandMap['petit-luxe'] ?? 5,
        'name' => 'Junior Organic Linen Resort Shirt & Shorts Set',
        'slug' => 'junior-organic-linen-shirt-shorts-set',
        'short_description' => 'Breathable pre-washed pure European flax tailored for active little explorers.',
        'description' => 'Crafted without harsh chemicals or synthetic dyes. Elasticated waistband on shorts, real coconut shell buttons, and soft interior seams.',
        'fabric_composition' => '100% Organic European Flax Linen',
        'wash_care_instructions' => 'Machine wash cold. Tumble dry low.',
        'model_specifications' => 'Fits true to age. Available in sizes 2Y to 10Y.',
        'base_price' => 2499.00,
        'discount_price' => 1999.00,
        'is_featured' => 1,
        'is_trending' => 1,
        'colors' => [2, 3], // White, Olive
        'sizes' => [1, 2, 3],
        'images' => [
            'https://images.unsplash.com/photo-1622290291468-a28f7a7dc6a8?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1519457431-44ccd64a579b?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1503944570647-94ddb02e4a3a?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1519689680058-324335c77eba?auto=format&fit=crop&w=1000&q=80'
        ]
    ],
    [
        'category_id' => $catMap['kids-girls'] ?? $catMap['kids'] ?? 3,
        'brand_id' => $brandMap['petit-luxe'] ?? 5,
        'name' => 'Girls\' Broderie Anglaise Cotton Tiered Sundress',
        'slug' => 'girls-broderie-anglaise-cotton-sundress',
        'short_description' => 'Delicate eyelet floral embroidery on soft breathable organic cambric cotton.',
        'description' => 'Flutter sleeves, a smocked bodice for expandable comfort, and a three-tier twirl skirt lined with pure mulmul cotton.',
        'fabric_composition' => '100% GOTS Certified Cambric Cotton',
        'wash_care_instructions' => 'Machine wash gentle cold in mesh laundry bag.',
        'model_specifications' => 'Available for ages 3Y to 12Y.',
        'base_price' => 2799.00,
        'discount_price' => 2199.00,
        'is_featured' => 0,
        'is_trending' => 1,
        'colors' => [2, 6], // White, Rust
        'sizes' => [1, 2, 3],
        'images' => [
            'https://images.unsplash.com/photo-1518831959646-742c3a14ebf7?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1543332164-6e82f355badc?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1508214751196-bcfd4ca60f91?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1471286174890-9c112ffca56a?auto=format&fit=crop&w=1000&q=80'
        ]
    ],

    // -------------------------------------------------------------
    // ACCESSORIES: LEATHER & SILK
    // -------------------------------------------------------------
    [
        'category_id' => $catMap['acc-leather'] ?? $catMap['accessories'] ?? 4,
        'brand_id' => $brandMap['cuoio-atelier'] ?? 6,
        'name' => 'Italian Full-Grain Vachetta Leather Belt',
        'slug' => 'italian-full-grain-vachetta-leather-belt',
        'short_description' => 'Vegetable-tanned in Tuscany with hand-burnished edges and solid forged brass hardware.',
        'description' => 'An heirloom accessory crafted from 3.5mm thick vegetable-tanned shoulder hide. Patinas to a deep golden luster with years of wear. 32mm width suited for both tailored trousers and selvedge denim.',
        'fabric_composition' => '100% Tuscan Full-Grain Vachetta Cowhide',
        'wash_care_instructions' => 'Treat with natural beeswax balm once annually. Keep away from water.',
        'model_specifications' => 'Available in sizes 30, 32, 34, 36, 38 waist.',
        'base_price' => 2999.00,
        'discount_price' => 2499.00,
        'is_featured' => 1,
        'is_trending' => 1,
        'colors' => [1, 7], // Black, Cognac Brown
        'sizes' => [2, 3, 4],
        'images' => [
            'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1624222247344-550fb60583dc?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1523293182086-7651a899d37f?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1512496015851-a90fb38ba796?auto=format&fit=crop&w=1000&q=80'
        ]
    ],
    [
        'category_id' => $catMap['acc-scarves'] ?? $catMap['accessories'] ?? 4,
        'brand_id' => $brandMap['cuoio-atelier'] ?? 6,
        'name' => 'Hand-Rolled Mulberry Silk Twill Atelier Scarf',
        'slug' => 'hand-rolled-mulberry-silk-twill-scarf',
        'short_description' => 'Screen-printed 16mm silk twill with artisan hand-rolled French edges.',
        'description' => 'Geometric architectural motifs inspired by modern art galleries. 90cm x 90cm square scarf that can be worn knotted at the throat, draped over shoulders, or tied to a handbag.',
        'fabric_composition' => '100% Grade 6A Mulberry Silk Twill (16 Momme)',
        'wash_care_instructions' => 'Dry clean only. Store flat.',
        'model_specifications' => 'Dimensions: 90cm x 90cm (35.4" x 35.4")',
        'base_price' => 3499.00,
        'discount_price' => 2899.00,
        'is_featured' => 0,
        'is_trending' => 1,
        'colors' => [4, 6], // Navy, Terracotta
        'sizes' => [3], // One Size (M)
        'images' => [
            'https://images.unsplash.com/photo-1601924994987-69e26d50dc26?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1509319117193-57bab727e09d?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1584030373081-f37b7bb4fa8e?auto=format&fit=crop&w=1000&q=80'
        ]
    ],
];

// Insert or update all products with their 3-4 images and 2D variant inventory
foreach ($catalog as $item) {
    $existing = Database::fetchOne("SELECT id FROM products WHERE slug = ?", [$item['slug']]);
    
    if (!$existing) {
        $productId = Database::insert('products', [
            'category_id' => $item['category_id'],
            'brand_id' => $item['brand_id'],
            'name' => $item['name'],
            'slug' => $item['slug'],
            'short_description' => $item['short_description'],
            'description' => $item['description'],
            'fabric_composition' => $item['fabric_composition'],
            'wash_care_instructions' => $item['wash_care_instructions'],
            'model_specifications' => $item['model_specifications'],
            'base_price' => $item['base_price'],
            'discount_price' => $item['discount_price'],
            'is_featured' => $item['is_featured'],
            'is_trending' => $item['is_trending'],
            'is_active' => 1
        ]);
    } else {
        $productId = $existing['id'];
        Database::update('products', [
            'category_id' => $item['category_id'],
            'brand_id' => $item['brand_id'],
            'name' => $item['name'],
            'base_price' => $item['base_price'],
            'discount_price' => $item['discount_price'],
            'is_featured' => $item['is_featured'],
            'is_trending' => $item['is_trending']
        ], 'id = :id', ['id' => $productId]);
    }

    // Clear old images and insert fresh 3-4 images
    Database::execute("DELETE FROM product_images WHERE product_id = ?", [$productId]);
    
    foreach ($item['images'] as $imgIdx => $imgUrl) {
        $colorId = $item['colors'][$imgIdx % count($item['colors'])] ?? null;
        Database::insert('product_images', [
            'product_id' => $productId,
            'color_id' => $colorId,
            'image_url' => $imgUrl,
            'alt_text' => $item['name'] . ' view ' . ($imgIdx + 1),
            'sort_order' => $imgIdx,
            'is_primary' => ($imgIdx === 0 ? 1 : 0)
        ]);
    }

    // Generate 2D Variants & Inventory (Colors x Sizes)
    foreach ($item['colors'] as $cId) {
        $colorRow = Database::fetchOne("SELECT name FROM colors WHERE id = ?", [$cId]);
        $cPrefix = strtoupper(substr($colorRow['name'] ?? 'CLR', 0, 3));

        foreach ($item['sizes'] as $sId) {
            $sizeRow = Database::fetchOne("SELECT name FROM sizes WHERE id = ?", [$sId]);
            $sCode = $sizeRow['name'] ?? 'SZ';

            $sku = sprintf("VEL-%03d-%s-%s", $productId, $cPrefix, $sCode);

            $existingVar = Database::fetchOne("SELECT id FROM product_variants WHERE product_id = ? AND size_id = ? AND color_id = ?", [$productId, $sId, $cId]);
            if (!$existingVar) {
                $variantId = Database::insert('product_variants', [
                    'product_id' => $productId,
                    'size_id' => $sId,
                    'color_id' => $cId,
                    'sku' => $sku,
                    'barcode' => '890' . rand(100000000, 999999999),
                    'is_active' => 1
                ]);

                $stock = rand(20, 50);
                Database::insert('inventory', [
                    'variant_id' => $variantId,
                    'stock_quantity' => $stock,
                    'reserved_quantity' => 0,
                    'safety_stock_threshold' => 5
                ]);

                Database::insert('inventory_movements', [
                    'variant_id' => $variantId,
                    'user_id' => 1,
                    'change_type' => 'PURCHASE_RECEIPT',
                    'quantity' => $stock,
                    'reference_id' => 'INIT-BATCH-2026',
                    'remarks' => 'Opening luxury stock'
                ]);
            }
        }
    }
}

echo "Successfully populated 18 garments across all categories with 3-4 high-resolution images each!\n";
