<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50 text-slate-900 antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin Console | ' . STORE_NAME) ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        slateDark: '#0F172A',
                        slateNavy: '#1E293B',
                        champagne: '#C5A880',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
                        serif: ['"Playfair Display"', 'Georgia', 'serif']
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="flex h-full font-sans text-slate-800" x-data="{ sidebarOpen: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" x-cloak 
         class="fixed inset-0 z-40 bg-black/50 lg:hidden" 
         @click="sidebarOpen = false"></div>

    <!-- Sidebar Navigation -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
           class="fixed inset-y-0 left-0 z-50 w-64 bg-slateDark text-white flex flex-col transition-transform duration-300 lg:static lg:translate-x-0">
        
        <!-- Brand Header -->
        <div class="h-20 flex items-center px-6 border-b border-slate-800">
            <a href="/admin" class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-champagne text-slateDark flex items-center justify-center font-bold text-lg font-serif">
                    A
                </div>
                <div>
                    <span class="font-serif text-lg font-bold tracking-wider uppercase text-white"><?= STORE_NAME ?></span>
                    <span class="block text-[9px] uppercase tracking-[0.25em] text-champagne -mt-1">Control Console</span>
                </div>
            </a>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-1 text-xs font-semibold">
            <?php
            $currentRoute = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
            function isActive(string $route, string $currentRoute): bool {
                return $currentRoute === $route || (strlen($route) > 6 && strpos($currentRoute, $route) === 0);
            }
            ?>
            
            <div class="px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Operations</div>
            
            <a href="/admin" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors <?= $currentRoute === '/admin' ? 'bg-slate-800 text-champagne' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' ?>">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                <span>Executive Dashboard</span>
            </a>

            <a href="/admin/orders" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors <?= isActive('/admin/orders', $currentRoute) ? 'bg-slate-800 text-champagne' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' ?>">
                <i data-lucide="package-check" class="w-4 h-4"></i>
                <span>Orders & Fulfillment</span>
            </a>

            <a href="/admin/returns" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors <?= isActive('/admin/returns', $currentRoute) ? 'bg-slate-800 text-champagne' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' ?>">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                <span>Returns & Refunds</span>
            </a>

            <div class="px-3 pt-6 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Catalog & Stock</div>

            <a href="/admin/products" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors <?= isActive('/admin/products', $currentRoute) ? 'bg-slate-800 text-champagne' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' ?>">
                <i data-lucide="shirt" class="w-4 h-4"></i>
                <span>Apparel Products</span>
            </a>

            <a href="/admin/products/create" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors <?= isActive('/admin/products/create', $currentRoute) ? 'bg-slate-800 text-champagne' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' ?>">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>New Variant Matrix</span>
            </a>

            <a href="/admin/inventory" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors <?= isActive('/admin/inventory', $currentRoute) ? 'bg-slate-800 text-champagne' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' ?>">
                <i data-lucide="boxes" class="w-4 h-4"></i>
                <span>SKU Stock Ledger</span>
            </a>

            <div class="px-3 pt-6 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Marketing & CMS</div>

            <a href="/admin/coupons" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors <?= isActive('/admin/coupons', $currentRoute) ? 'bg-slate-800 text-champagne' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' ?>">
                <i data-lucide="ticket" class="w-4 h-4"></i>
                <span>Discount Coupons</span>
            </a>

            <a href="/admin/banners" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors <?= isActive('/admin/banners', $currentRoute) ? 'bg-slate-800 text-champagne' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' ?>">
                <i data-lucide="image" class="w-4 h-4"></i>
                <span>Hero Banners</span>
            </a>

            <div class="px-3 pt-6 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">System & Governance</div>

            <a href="/admin/settings" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors <?= isActive('/admin/settings', $currentRoute) ? 'bg-slate-800 text-champagne' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' ?>">
                <i data-lucide="settings" class="w-4 h-4"></i>
                <span>Settings & RBAC</span>
            </a>

            <a href="/admin/audit-logs" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors <?= isActive('/admin/audit-logs', $currentRoute) ? 'bg-slate-800 text-champagne' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' ?>">
                <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                <span>Security Audit Trail</span>
            </a>
        </nav>

        <!-- User Session & Switcher Footer -->
        <div class="p-4 border-t border-slate-800 text-xs bg-slate-900/60">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-bold text-white"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></p>
                    <span class="text-[10px] text-champagne font-semibold uppercase tracking-wider">Super Administrator</span>
                </div>
                <a href="/logout" class="p-1.5 text-slate-400 hover:text-rose-400 transition-colors" title="Sign Out">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </a>
            </div>
            <a href="/" target="_blank" class="mt-3 flex items-center justify-center gap-2 py-2 px-3 bg-slate-800 hover:bg-slate-700 text-white rounded-lg font-medium text-[11px] transition-colors">
                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                <span>View Storefront</span>
            </a>
        </div>
    </aside>

    <!-- Main Content Canvas -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <!-- Admin Top Navigation Bar -->
        <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-6 sticky top-0 z-30">
            <div class="flex items-center gap-4">
                <button type="button" @click="sidebarOpen = true" class="lg:hidden p-2 text-slate-600 hover:text-slate-900">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                    <span>Atelier Core</span>
                    <span>/</span>
                    <span class="text-slate-900"><?= htmlspecialchars($adminTitle ?? 'Management Console') ?></span>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-800 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Razorpay Live Ready</span>
                </span>
            </div>
        </header>

        <!-- Dynamic Body Content -->
        <main class="flex-1 p-6 sm:p-8 lg:p-10">
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-xs font-medium flex items-center justify-between">
                    <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
                    <button onclick="this.parentElement.remove()" class="text-emerald-700">&times;</button>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-xs font-medium flex items-center justify-between">
                    <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
                    <button onclick="this.parentElement.remove()" class="text-rose-700">&times;</button>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>

            <?= $content ?? '' ?>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
</body>
</html>

