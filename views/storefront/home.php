<?php
/**
 * Luxury Clothing Storefront Homepage
 */
$banners = \App\Database\Database::query("SELECT * FROM banners WHERE is_active = 1 ORDER BY sort_order ASC");
$featuredProducts = \App\Services\CatalogService::getProducts(['category' => null], 4);
$trendingProducts = \App\Services\CatalogService::getProducts(['sort' => 'popular'], 4);
$departments = \App\Database\Database::query("SELECT * FROM categories WHERE parent_id IS NULL AND is_active = 1 ORDER BY display_order ASC");
?>

<!-- Hero Banner Slider -->
<section class="relative w-full bg-neutral-900 overflow-hidden" x-data="{ currentSlide: 0, slidesCount: <?= count($banners) ?> }" x-init="setInterval(() => { currentSlide = (currentSlide + 1) % slidesCount }, 6000)">
    <div class="relative h-[550px] sm:h-[650px] w-full">
        <?php foreach ($banners as $idx => $b): ?>
            <div x-show="currentSlide === <?= $idx ?>"
                 x-transition:enter="transition ease-out duration-700"
                 x-transition:enter-start="opacity-0 scale-105"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-500"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute inset-0 w-full h-full">
                <!-- Background Image -->
                <img src="<?= htmlspecialchars($b['image_url_desktop']) ?>" 
                     alt="<?= htmlspecialchars($b['title']) ?>" 
                     class="w-full h-full object-cover object-top opacity-75">
                
                <!-- Dark Gradient Overlay -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-black/30"></div>

                <!-- Text Content -->
                <div class="absolute inset-0 flex items-center justify-center text-center px-4">
                    <div class="max-w-3xl space-y-4">
                        <span class="inline-block text-[11px] font-bold tracking-[0.3em] uppercase text-champagne bg-black/40 backdrop-blur-md px-3.5 py-1 rounded-full border border-champagne/30">
                            Seasonal Capsule Drop
                        </span>
                        <h1 class="font-serif text-3xl sm:text-5xl lg:text-6xl font-bold text-white tracking-wide leading-tight">
                            <?= htmlspecialchars($b['title']) ?>
                        </h1>
                        <p class="text-sm sm:text-base text-neutral-200 max-w-xl mx-auto font-light leading-relaxed">
                            <?= htmlspecialchars($b['subtitle']) ?>
                        </p>
                        <div class="pt-4 flex items-center justify-center gap-4">
                            <a href="<?= htmlspecialchars($b['target_url']) ?>" class="px-8 py-3.5 bg-white text-obsidian text-xs font-bold uppercase tracking-[0.2em] rounded hover:bg-champagne hover:text-white transition-all shadow-lg">
                                Explore The Edit
                            </a>
                            <a href="/catalog" class="px-8 py-3.5 bg-transparent border border-white text-white text-xs font-bold uppercase tracking-[0.2em] rounded hover:bg-white hover:text-obsidian transition-all">
                                View Lookbook
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Slider Controls -->
    <?php if (count($banners) > 1): ?>
        <div class="absolute bottom-6 left-0 right-0 flex justify-center gap-2 z-20">
            <?php foreach ($banners as $idx => $b): ?>
                <button type="button" 
                        @click="currentSlide = <?= $idx ?>"
                        :class="currentSlide === <?= $idx ?> ? 'w-8 bg-white' : 'w-2 bg-white/40'"
                        class="h-1.5 rounded-full transition-all duration-300"></button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- Brand Value Propositions Strip -->
<section class="border-y border-subtleBorder bg-white py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
            <div class="flex flex-col items-center space-y-1.5 p-2">
                <i data-lucide="feather" class="w-5 h-5 text-champagne mb-1"></i>
                <h4 class="text-xs font-bold uppercase tracking-wider text-obsidian">Noble Natural Fibers</h4>
                <p class="text-[11px] text-mutedText">100% French linen, mulberry silk, and combed cotton.</p>
            </div>
            <div class="flex flex-col items-center space-y-1.5 p-2">
                <i data-lucide="truck" class="w-5 h-5 text-champagne mb-1"></i>
                <h4 class="text-xs font-bold uppercase tracking-wider text-obsidian">Complimentary Shipping</h4>
                <p class="text-[11px] text-mutedText">Express 48h dispatch on all orders above <?= STORE_CURRENCY_SYMBOL ?>999.</p>
            </div>
            <div class="flex flex-col items-center space-y-1.5 p-2">
                <i data-lucide="refresh-cw" class="w-5 h-5 text-champagne mb-1"></i>
                <h4 class="text-xs font-bold uppercase tracking-wider text-obsidian">7-Day Effortless Returns</h4>
                <p class="text-[11px] text-mutedText">Doorstep reverse pickup & size exchanges.</p>
            </div>
            <div class="flex flex-col items-center space-y-1.5 p-2">
                <i data-lucide="shield-check" class="w-5 h-5 text-champagne mb-1"></i>
                <h4 class="text-xs font-bold uppercase tracking-wider text-obsidian">Razorpay 256-Bit SSL</h4>
                <p class="text-[11px] text-mutedText">Zero-trust verified UPI, Cards, and NetBanking.</p>
            </div>
        </div>
    </div>
</section>

<!-- Departments Showcase (Categories Grid) -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
    <div class="text-center max-w-xl mx-auto mb-12">
        <span class="text-[10px] font-bold tracking-[0.25em] text-champagne uppercase">Dynamic Taxonomy</span>
        <h2 class="font-serif text-3xl sm:text-4xl font-bold text-obsidian mt-1">Curated Departments</h2>
        <p class="text-xs text-mutedText mt-2">Explore distinct apparel collections tailored for uncompromising elegance.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach ($departments as $dept): ?>
            <a href="/catalog?category=<?= urlencode($dept['slug']) ?>" 
               class="group relative h-96 rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300">
                <img src="<?= htmlspecialchars($dept['image_url']) ?>" 
                     alt="<?= htmlspecialchars($dept['name']) ?>" 
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                <div class="absolute bottom-6 left-6 right-6 text-white">
                    <span class="text-[10px] uppercase tracking-[0.2em] text-champagne">Department</span>
                    <h3 class="font-serif text-2xl font-bold mt-0.5 group-hover:translate-x-1 transition-transform">
                        <?= htmlspecialchars($dept['name']) ?>
                    </h3>
                    <span class="mt-2 inline-flex items-center gap-1 text-[11px] font-semibold uppercase tracking-wider text-neutral-300 group-hover:text-white">
                        <span>Shop Collection</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- Featured Haute Edit Grid -->
<section class="bg-white border-y border-subtleBorder py-16 sm:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12">
            <div>
                <span class="text-[10px] font-bold tracking-[0.25em] text-champagne uppercase">Exclusive Capsule</span>
                <h2 class="font-serif text-3xl sm:text-4xl font-bold text-obsidian mt-1">Featured Garments</h2>
                <p class="text-xs text-mutedText mt-1">Bespoke construction with 2D variant sizing matrix & live stock tracking.</p>
            </div>
            <a href="/catalog" class="mt-4 md:mt-0 text-xs font-bold uppercase tracking-[0.2em] text-obsidian hover:text-champagne transition-colors flex items-center gap-1.5">
                <span>View Full Catalog</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($featuredProducts as $product): ?>
                <?php require VIEW_PATH . '/components/product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Brand Editorial Feature -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="relative bg-obsidian text-white rounded-3xl overflow-hidden p-8 sm:p-16 lg:p-20">
        <div class="relative z-10 max-w-2xl space-y-6">
            <span class="text-[10px] font-bold tracking-[0.3em] uppercase text-champagne">The Atelier Philosophy</span>
            <h2 class="font-serif text-3xl sm:text-5xl font-bold leading-tight">
                "We do not design for the season. We tailor for permanence."
            </h2>
            <p class="text-xs sm:text-sm text-neutral-300 font-light leading-relaxed">
                Every VELORIS LUXE garment begins in Normandy, Portugal, and Okayama with raw organic fibres. No artificial synthetics, no fast-fashion shortcuts—only noble textiles cut to architectural perfection.
            </p>
            <div class="pt-2">
                <a href="/pages/about-us" class="inline-block px-7 py-3 bg-champagne text-obsidian text-xs font-bold uppercase tracking-[0.2em] rounded hover:bg-white transition-colors">
                    Read Our Craft Story
                </a>
            </div>
        </div>
        <div class="absolute right-0 top-0 bottom-0 w-1/2 opacity-20 pointer-events-none hidden md:block">
            <img src="https://images.unsplash.com/photo-1558769132-cb1aea458c5e?auto=format&fit=crop&w=1200&q=80" class="w-full h-full object-cover" alt="Atelier Craft">
        </div>
    </div>
</section>

<!-- Trending Drops Section -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
    <div class="flex items-center justify-between mb-10">
        <div>
            <span class="text-[10px] font-bold tracking-[0.25em] text-champagne uppercase">Trending Right Now</span>
            <h2 class="font-serif text-3xl font-bold text-obsidian mt-1">Most Desired Silhouettes</h2>
        </div>
        <a href="/catalog?sort=popular" class="text-xs font-bold uppercase tracking-[0.15em] text-obsidian hover:text-champagne transition-colors">
            See All &rarr;
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach ($trendingProducts as $product): ?>
            <?php require VIEW_PATH . '/components/product_card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>

