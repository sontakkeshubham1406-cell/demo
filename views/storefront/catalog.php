<?php
/**
 * Product Listing Page (PLP) with Faceted Filtering
 */
$filterOptions = \App\Services\CatalogService::getFilterOptions();
$currentFilters = [
    'category' => $_GET['category'] ?? null,
    'size' => $_GET['size'] ?? [],
    'color' => $_GET['color'] ?? [],
    'min_price' => $_GET['min_price'] ?? null,
    'max_price' => $_GET['max_price'] ?? null,
    'brand' => $_GET['brand'] ?? null,
    'in_stock' => isset($_GET['in_stock']) ? 1 : null,
    'sort' => $_GET['sort'] ?? 'newest',
    'search' => $_GET['search'] ?? null,
];

$products = \App\Services\CatalogService::getProducts($currentFilters, 48);
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12" x-data="{ mobileFiltersOpen: false }">
    
    <!-- Breadcrumbs -->
    <nav class="flex text-xs text-mutedText mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2">
            <li><a href="/" class="hover:text-obsidian">Home</a></li>
            <li><span>/</span></li>
            <li class="font-medium text-obsidian">Catalog</li>
            <?php if (!empty($currentFilters['category'])): ?>
                <li><span>/</span></li>
                <li class="text-champagne font-semibold uppercase tracking-wider"><?= htmlspecialchars($currentFilters['category']) ?></li>
            <?php endif; ?>
        </ol>
    </nav>

    <!-- Page Header & Active Filters Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 border-b border-subtleBorder gap-4">
        <div>
            <h1 class="font-serif text-3xl font-bold text-obsidian">
                <?= !empty($currentFilters['search']) ? 'Search: "' . htmlspecialchars($currentFilters['search']) . '"' : 'The Apparel Catalog' ?>
            </h1>
            <p class="text-xs text-mutedText mt-1">
                Showing <strong class="text-obsidian"><?= count($products) ?></strong> exquisite garments
            </p>
        </div>

        <div class="flex items-center gap-4">
            <!-- Mobile Filter Trigger -->
            <button type="button" @click="mobileFiltersOpen = true" class="lg:hidden flex items-center gap-2 px-4 py-2 bg-white border border-subtleBorder rounded-lg text-xs font-semibold text-obsidian">
                <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                <span>Filters</span>
            </button>

            <!-- Sorting Dropdown -->
            <form action="/catalog" method="GET" class="flex items-center gap-2">
                <!-- Keep other filter parameters hidden -->
                <?php if (!empty($currentFilters['category'])): ?>
                    <input type="hidden" name="category" value="<?= htmlspecialchars($currentFilters['category']) ?>">
                <?php endif; ?>
                <?php if (!empty($currentFilters['search'])): ?>
                    <input type="hidden" name="search" value="<?= htmlspecialchars($currentFilters['search']) ?>">
                <?php endif; ?>

                <label for="sort" class="text-xs text-mutedText whitespace-nowrap hidden sm:inline">Sort by:</label>
                <select name="sort" id="sort" onchange="this.form.submit()" 
                        class="px-3 py-2 bg-white border border-subtleBorder rounded-lg text-xs font-medium text-obsidian focus:outline-none focus:border-obsidian">
                    <option value="newest" <?= $currentFilters['sort'] === 'newest' ? 'selected' : '' ?>>Newest Arrivals</option>
                    <option value="popular" <?= $currentFilters['sort'] === 'popular' ? 'selected' : '' ?>>Popular / Trending</option>
                    <option value="price_asc" <?= $currentFilters['sort'] === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="price_desc" <?= $currentFilters['sort'] === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                    <option value="rating" <?= $currentFilters['sort'] === 'rating' ? 'selected' : '' ?>>Highest Rated</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Main Content Layout (Sidebar + Product Grid) -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 pt-8">
        
        <!-- Desktop Filter Sidebar -->
        <aside class="hidden lg:block space-y-8 pr-4">
            <form action="/catalog" method="GET" id="desktopFilterForm" class="space-y-6">
                <div class="flex items-center justify-between pb-3 border-b border-subtleBorder">
                    <h3 class="text-xs font-bold uppercase tracking-[0.2em] text-obsidian">Refine Selection</h3>
                    <a href="/catalog" class="text-[11px] text-mutedText hover:text-rose-600 underline">Reset All</a>
                </div>

                <!-- Categories -->
                <div class="space-y-3">
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-obsidian">Categories</h4>
                    <div class="space-y-1.5 max-h-48 overflow-y-auto text-xs">
                        <?php foreach ($filterOptions['categories'] as $cat): ?>
                            <label class="flex items-center gap-2 cursor-pointer text-mutedText hover:text-obsidian">
                                <input type="radio" name="category" value="<?= htmlspecialchars($cat['slug']) ?>" 
                                       <?= $currentFilters['category'] === $cat['slug'] ? 'checked' : '' ?>
                                       onchange="this.form.submit()"
                                       class="text-obsidian focus:ring-obsidian">
                                <span><?= htmlspecialchars($cat['name']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Size Chips -->
                <div class="space-y-3 pt-4 border-t border-subtleBorder">
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-obsidian">Available Sizes</h4>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($filterOptions['sizes'] as $size): ?>
                            <?php $isSelected = in_array($size['id'], (array)$currentFilters['size']); ?>
                            <label class="cursor-pointer">
                                <input type="checkbox" name="size[]" value="<?= $size['id'] ?>" 
                                       <?= $isSelected ? 'checked' : '' ?>
                                       onchange="this.form.submit()"
                                       class="sr-only">
                                <span class="px-3 py-1.5 border text-xs font-semibold rounded-md transition-colors <?= $isSelected ? 'bg-obsidian text-white border-obsidian' : 'bg-white text-obsidian border-subtleBorder hover:border-obsidian' ?>">
                                    <?= htmlspecialchars($size['name']) ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Color Swatches -->
                <div class="space-y-3 pt-4 border-t border-subtleBorder">
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-obsidian">Colorway</h4>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($filterOptions['colors'] as $color): ?>
                            <?php $isSelected = in_array($color['id'], (array)$currentFilters['color']); ?>
                            <label class="cursor-pointer relative group" title="<?= htmlspecialchars($color['name']) ?>">
                                <input type="checkbox" name="color[]" value="<?= $color['id'] ?>" 
                                       <?= $isSelected ? 'checked' : '' ?>
                                       onchange="this.form.submit()"
                                       class="sr-only">
                                <span class="w-6 h-6 rounded-full border flex items-center justify-center transition-all <?= $isSelected ? 'ring-2 ring-obsidian ring-offset-2' : 'border-neutral-300 hover:scale-110' ?>"
                                      style="background-color: <?= htmlspecialchars($color['hex_code']) ?>">
                                    <?php if ($isSelected): ?>
                                        <i data-lucide="check" class="w-3.5 h-3.5 <?= $color['hex_code'] === '#F8F9FA' ? 'text-obsidian' : 'text-white' ?>"></i>
                                    <?php endif; ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Price Range -->
                <div class="space-y-3 pt-4 border-t border-subtleBorder">
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-obsidian">Price Range (<?= STORE_CURRENCY_SYMBOL ?>)</h4>
                    <div class="flex items-center gap-2">
                        <input type="number" name="min_price" placeholder="Min" value="<?= htmlspecialchars($currentFilters['min_price'] ?? '') ?>" 
                               class="w-full px-2.5 py-1.5 bg-white border border-subtleBorder rounded text-xs">
                        <span class="text-mutedText">-</span>
                        <input type="number" name="max_price" placeholder="Max" value="<?= htmlspecialchars($currentFilters['max_price'] ?? '') ?>" 
                               class="w-full px-2.5 py-1.5 bg-white border border-subtleBorder rounded text-xs">
                    </div>
                    <button type="submit" class="w-full py-1.5 bg-neutral-100 hover:bg-neutral-200 text-obsidian rounded text-xs font-semibold uppercase tracking-wider transition-colors">
                        Apply Range
                    </button>
                </div>

                <!-- In Stock Checkbox -->
                <div class="pt-4 border-t border-subtleBorder">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-medium text-obsidian">
                        <input type="checkbox" name="in_stock" value="1" 
                               <?= !empty($currentFilters['in_stock']) ? 'checked' : '' ?>
                               onchange="this.form.submit()"
                               class="rounded border-subtleBorder text-obsidian focus:ring-obsidian">
                        <span>In Stock Only</span>
                    </label>
                </div>
            </form>
        </aside>

        <!-- Product Grid Area (3 Columns on Desktop) -->
        <div class="lg:col-span-3">
            <?php if (empty($products)): ?>
                <div class="bg-white border border-subtleBorder rounded-2xl p-12 text-center space-y-4">
                    <div class="w-16 h-16 mx-auto rounded-full bg-[#FAF9F6] border border-subtleBorder flex items-center justify-center text-mutedText">
                        <i data-lucide="filter-x" class="w-8 h-8"></i>
                    </div>
                    <h3 class="font-serif text-2xl font-bold text-obsidian">No garments match this criteria</h3>
                    <p class="text-xs text-mutedText max-w-md mx-auto">
                        Try resetting your size or price filters to browse our complete collection.
                    </p>
                    <a href="/catalog" class="inline-block px-6 py-2.5 bg-obsidian text-white text-xs font-bold uppercase tracking-wider rounded hover:bg-slateDark transition-colors">
                        Reset Filters
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                    <?php foreach ($products as $product): ?>
                        <?php require VIEW_PATH . '/components/product_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

