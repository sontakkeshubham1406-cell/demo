<?php
/**
 * Reusable Luxury Product Card Component
 * Expected: $product (array)
 */
$discountPercent = 0;
if (!empty($product['discount_price']) && $product['discount_price'] < $product['base_price']) {
    $discountPercent = round((($product['base_price'] - $product['discount_price']) / $product['base_price']) * 100);
}
?>
<div class="group relative flex flex-col bg-white border border-subtleBorder/70 rounded-xl overflow-hidden hover:shadow-xl transition-all duration-300"
     x-data="{ activeImage: '<?= htmlspecialchars($product['primary_image'] ?? '') ?>' }">
    
    <!-- Image Gallery Container -->
    <div class="relative w-full aspect-[3/4] bg-neutral-100 overflow-hidden">
        <a href="/product/<?= htmlspecialchars($product['slug']) ?>" class="block w-full h-full">
            <img :src="activeImage" 
                 src="<?= htmlspecialchars($product['primary_image'] ?? '') ?>" 
                 alt="<?= htmlspecialchars($product['name']) ?>" 
                 class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500"
                 loading="lazy">
        </a>

        <!-- Discount Badge -->
        <?php if ($discountPercent > 0): ?>
            <span class="absolute top-3 left-3 bg-obsidian text-white text-[10px] font-bold uppercase tracking-widest px-2.5 py-1 rounded shadow-sm">
                -<?= $discountPercent ?>% OFF
            </span>
        <?php endif; ?>

        <?php if (!empty($product['is_trending'])): ?>
            <span class="absolute top-3 right-3 bg-champagne text-white text-[9px] font-bold uppercase tracking-widest px-2 py-0.5 rounded shadow-sm">
                Trending
            </span>
        <?php endif; ?>
    </div>

    <!-- Product Metadata -->
    <div class="p-4 flex flex-col flex-grow">
        <!-- Brand & Category -->
        <div class="flex items-center justify-between text-[11px] uppercase tracking-[0.15em] text-mutedText font-medium mb-1">
            <span><?= htmlspecialchars($product['brand_name'] ?? 'DEMO') ?></span>
            <span><?= htmlspecialchars($product['category_name'] ?? '') ?></span>
        </div>

        <!-- Product Name -->
        <h3 class="text-sm font-medium text-obsidian line-clamp-1 group-hover:text-champagne transition-colors">
            <a href="/product/<?= htmlspecialchars($product['slug']) ?>">
                <?= htmlspecialchars($product['name']) ?>
            </a>
        </h3>

        <!-- Color Swatches -->
        <?php if (!empty($product['available_colors'])): ?>
            <div class="flex items-center gap-1.5 my-2.5">
                <?php foreach ($product['available_colors'] as $c): ?>
                    <span class="w-3.5 h-3.5 rounded-full border border-neutral-300 cursor-pointer shadow-xs hover:scale-110 transition-transform" 
                          style="background-color: <?= htmlspecialchars($c['hex_code']) ?>"
                          title="<?= htmlspecialchars($c['name']) ?>"></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Pricing & Action -->
        <div class="mt-auto pt-2 flex items-center justify-between border-t border-subtleBorder/50">
            <div class="flex items-baseline gap-2">
                <span class="text-sm font-bold text-obsidian">
                    <?= STORE_CURRENCY_SYMBOL ?><?= number_format($product['discount_price'] ?? $product['base_price']) ?>
                </span>
                <?php if (!empty($product['discount_price'])): ?>
                    <span class="text-xs text-mutedText line-through">
                        <?= STORE_CURRENCY_SYMBOL ?><?= number_format($product['base_price']) ?>
                    </span>
                <?php endif; ?>
            </div>

            <a href="/product/<?= htmlspecialchars($product['slug']) ?>" 
               class="text-xs font-semibold uppercase tracking-wider text-obsidian hover:text-champagne flex items-center gap-1">
                <span>View</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
    </div>
</div>

