<!DOCTYPE html>
<html lang="en" class="h-full bg-[#FAF9F6] text-[#111111] antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? STORE_NAME . ' | ' . STORE_TAGLINE) ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDescription ?? 'Explore handcrafted luxury apparel, pure European linens, and architectural streetwear at VELORIS LUXE.') ?>">
    
    <!-- Google Fonts: Playfair Display (Serif Editorial) + Plus Jakarta Sans (Clean Modern Sans) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..800;1,400..800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN with Custom Theme Config -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        canvas: '#FAF9F6',
                        obsidian: '#111111',
                        slateDark: '#0F172A',
                        champagne: '#C5A880',
                        champagneLight: '#F4EFEA',
                        subtleBorder: '#E5E5E5',
                        mutedText: '#666666'
                    },
                    fontFamily: {
                        serif: ['"Playfair Display"', 'Georgia', 'serif'],
                        sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif']
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js for lightweight, instant reactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        [x-cloak] { display: none !important; }
        /* Custom scrollbars */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #FAF9F6; }
        ::-webkit-scrollbar-thumb { background: #D1D5DB; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #9CA3AF; }
    </style>
</head>
<body class="flex min-h-full flex-col font-sans selection:bg-champagne selection:text-white" x-data="globalStore()">

    <!-- Announcement Bar -->
    <div class="bg-obsidian text-white text-[11px] tracking-[0.18em] uppercase py-2.5 px-4 text-center font-medium flex items-center justify-center gap-3">
        <span>Complimentary Express Shipping on orders above <?= STORE_CURRENCY_SYMBOL ?><?= number_format(FREE_SHIPPING_THRESHOLD) ?></span>
        <span class="opacity-40">|</span>
        <span class="text-champagne font-semibold">Code: WELCOME10 for 10% OFF</span>
    </div>

    <!-- Header Navigation -->
    <?php require VIEW_PATH . '/components/header.php'; ?>

    <!-- Main Content Container -->
    <main class="flex-grow">
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg text-sm flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                        <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">&times;</button>
                </div>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-lg text-sm flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                        <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900">&times;</button>
                </div>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <?= $content ?? '' ?>
    </main>

    <!-- Slide-over Mini-Cart Drawer -->
    <?php require VIEW_PATH . '/components/cart_drawer.php'; ?>

    <!-- Interactive Size Guide Modal -->
    <?php require VIEW_PATH . '/components/size_guide_modal.php'; ?>

    <!-- Footer -->
    <?php require VIEW_PATH . '/components/footer.php'; ?>

    <!-- Alpine Global Store Data & Scripts -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('globalStore', () => ({
                cartOpen: false,
                sizeGuideOpen: false,
                cartCount: <?= \App\Services\CouponPricingEngine::calculateCartTotals()['items_count'] ?>,
                searchOpen: false,
                searchQuery: '',
                searchResults: [],
                searchLoading: false,

                triggerSearch() {
                    if (this.searchQuery.trim().length < 2) {
                        this.searchResults = [];
                        return;
                    }
                    this.searchLoading = true;
                    fetch('/api/search?q=' + encodeURIComponent(this.searchQuery))
                        .then(res => res.json())
                        .then(data => {
                            this.searchResults = data.results || [];
                            this.searchLoading = false;
                        })
                        .catch(() => { this.searchLoading = false; });
                }
            }));
        });

        // Initialize Lucide icons
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
</body>
</html>

