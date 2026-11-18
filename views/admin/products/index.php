<?php
/**
 * Admin Product Catalog Listing
 */
$products = \App\Database\Database::query("
    SELECT 
        p.*,
        b.name as brand_name,
        c.name as category_name,
        (SELECT COUNT(id) FROM product_variants WHERE product_id = p.id) as variant_count,
        (SELECT COALESCE(SUM(inv.stock_quantity - inv.reserved_quantity), 0) 
         FROM product_variants pv 
         JOIN inventory inv ON pv.id = inv.variant_id 
         WHERE pv.product_id = p.id) as total_stock,
        (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
    FROM products p
    LEFT JOIN brands b ON p.brand_id = b.id
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.created_at DESC
");
?>
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-200 gap-4">
        <div>
            <span class="text-[10px] font-bold tracking-[0.2em] text-champagne uppercase">Master Catalog</span>
            <h1 class="font-serif text-3xl font-bold text-slate-900 mt-0.5">Apparel Products</h1>
            <p class="text-xs text-slate-500">Manage garments, 2D variant matrix permutations, pricing, and live inventory.</p>
        </div>

        <a href="/admin/products/create" class="px-5 py-2.5 bg-slate-900 text-white text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-slate-800 transition-colors flex items-center gap-2 shadow-sm self-start sm:self-auto">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Create Garment & Matrix</span>
        </a>
    </div>

    <!-- Products Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-6">Garment</th>
                        <th class="py-3.5 px-4">Department / Brand</th>
                        <th class="py-3.5 px-4">Base Price</th>
                        <th class="py-3.5 px-4">Sale Price</th>
                        <th class="py-3.5 px-4 text-center">Variants</th>
                        <th class="py-3.5 px-4 text-center">Available Stock</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($products as $p): ?>
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-6 flex items-center gap-3">
                                <img src="<?= htmlspecialchars($p['primary_image'] ?? '') ?>" class="w-12 h-16 object-cover rounded bg-neutral-100 border border-slate-200" alt="">
                                <div>
                                    <h4 class="font-bold text-slate-900"><?= htmlspecialchars($p['name']) ?></h4>
                                    <span class="text-[11px] font-mono text-slate-400">/product/<?= htmlspecialchars($p['slug']) ?></span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                <p class="font-semibold text-slate-800"><?= htmlspecialchars($p['category_name']) ?></p>
                                <p class="text-[11px] text-slate-400"><?= htmlspecialchars($p['brand_name'] ?? 'DEMO') ?></p>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-500">
                                <?= STORE_CURRENCY_SYMBOL ?><?= number_format($p['base_price'], 2) ?>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                <?= !empty($p['discount_price']) ? STORE_CURRENCY_SYMBOL . number_format($p['discount_price'], 2) : '—' ?>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-700 font-bold rounded-full font-mono text-[11px]">
                                    <?= $p['variant_count'] ?> SKUs
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 <?= $p['total_stock'] <= 5 ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-emerald-50 text-emerald-800 border-emerald-200' ?> font-bold font-mono rounded-lg border text-[11px]">
                                    <?= $p['total_stock'] ?> Units
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider <?= $p['is_active'] ? 'bg-emerald-50 text-emerald-800' : 'bg-rose-50 text-rose-800' ?>">
                                    <?= $p['is_active'] ? 'Active' : 'Archived' ?>
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                <a href="/product/<?= htmlspecialchars($p['slug']) ?>" target="_blank" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-md text-[11px] font-semibold transition-colors inline-block mr-2">
                                    Preview
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

