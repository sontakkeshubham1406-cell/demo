<?php
$categoriesTree = \App\Services\CatalogService::getCategories();
$currentUser = \App\Services\AuthService::getCurrentUser();
?>
<header class="sticky top-0 z-40 bg-[#FAF9F6]/95 backdrop-blur-md border-b border-subtleBorder transition-all duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            
            <!-- Mobile Menu Toggle -->
            <div class="flex items-center lg:hidden">
                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 text-obsidian hover:text-champagne transition-colors">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
            </div>

            <!-- Brand Logo -->
            <div class="flex-shrink-0 flex items-center">
                <a href="/" class="flex flex-col items-center group">
                    <span class="font-serif text-2xl sm:text-3xl tracking-[0.25em] font-bold text-obsidian uppercase group-hover:text-champagne transition-colors">
                        <?= STORE_NAME ?>
                    </span>
                    <span class="text-[9px] tracking-[0.35em] text-mutedText uppercase -mt-0.5">
                        Haute Couture
                    </span>
                </a>
            </div>

            <!-- Desktop Megamenu Navigation -->
            <nav class="hidden lg:flex items-center space-x-8">
                <a href="/catalog?sort=newest" class="text-xs uppercase tracking-[0.15em] font-medium text-obsidian hover:text-champagne transition-colors py-2">
                    New Arrivals
                </a>
                
                <?php foreach ($categoriesTree as $cat): ?>
                    <div class="relative group" x-data="{ open: false }">
                        <a href="/catalog?category=<?= urlencode($cat['slug']) ?>" 
                           class="flex items-center gap-1 text-xs uppercase tracking-[0.15em] font-medium text-obsidian group-hover:text-champagne transition-colors py-2">
                            <span><?= htmlspecialchars($cat['name']) ?></span>
                            <?php if (!empty($cat['children'])): ?>
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5 opacity-60 group-hover:rotate-180 transition-transform"></i>
                            <?php endif; ?>
                        </a>

                        <?php if (!empty($cat['children'])): ?>
                            <!-- Dropdown Menu -->
                            <div class="absolute left-0 mt-2 w-64 bg-white shadow-xl border border-subtleBorder rounded-lg py-3 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 z-50">
                                <div class="px-4 py-2 border-b border-subtleBorder/50">
                                    <span class="text-[10px] tracking-[0.2em] font-semibold uppercase text-mutedText">
                                        Explore <?= htmlspecialchars($cat['name']) ?>
                                    </span>
                                </div>
                                <?php foreach ($cat['children'] as $child): ?>
                                    <a href="/catalog?category=<?= urlencode($child['slug']) ?>" 
                                       class="block px-4 py-2.5 text-xs text-obsidian hover:bg-[#FAF9F6] hover:text-champagne font-medium transition-colors">
                                        <?= htmlspecialchars($child['name']) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <a href="/catalog?sort=popular" class="text-xs uppercase tracking-[0.15em] font-medium text-rose-700 hover:text-rose-900 transition-colors py-2">
                    Trending
                </a>
            </nav>

            <!-- Action Controls (Search, Account, Bag) -->
            <div class="flex items-center space-x-4 sm:space-x-6">
                <!-- Predictive Search Trigger -->
                <button type="button" @click="searchOpen = true" class="p-2 text-obsidian hover:text-champagne transition-colors" title="Search catalog">
                    <i data-lucide="search" class="w-5 h-5"></i>
                </button>

                <!-- Customer Account Dropdown -->
                <div class="relative" x-data="{ userMenu: false }">
                    <button type="button" @click="userMenu = !userMenu" class="p-2 text-obsidian hover:text-champagne transition-colors flex items-center gap-1">
                        <i data-lucide="user" class="w-5 h-5"></i>
                        <?php if ($currentUser): ?>
                            <span class="hidden md:inline text-xs font-medium max-w-[80px] truncate">
                                <?= htmlspecialchars(explode(' ', $currentUser['name'])[0]) ?>
                            </span>
                        <?php endif; ?>
                    </button>

                    <div x-show="userMenu" @click.away="userMenu = false" x-cloak
                         class="absolute right-0 mt-2 w-52 bg-white shadow-xl border border-subtleBorder rounded-lg py-2 z-50 text-xs">
                        <?php if ($currentUser): ?>
                            <div class="px-4 py-2 border-b border-subtleBorder">
                                <p class="font-semibold text-obsidian"><?= htmlspecialchars($currentUser['name']) ?></p>
                                <p class="text-mutedText truncate"><?= htmlspecialchars($currentUser['email']) ?></p>
                            </div>
                            <?php if (!empty($currentUser['role_id'])): ?>
                                <a href="/admin" class="flex items-center gap-2 px-4 py-2 text-champagne font-semibold hover:bg-[#FAF9F6]">
                                    <i data-lucide="shield" class="w-4 h-4"></i>
                                    <span>Admin Panel</span>
                                </a>
                            <?php endif; ?>
                            <a href="/account/orders" class="flex items-center gap-2 px-4 py-2 text-obsidian hover:bg-[#FAF9F6]">
                                <i data-lucide="package" class="w-4 h-4"></i>
                                <span>My Orders & Tracking</span>
                            </a>
                            <a href="/account/addresses" class="flex items-center gap-2 px-4 py-2 text-obsidian hover:bg-[#FAF9F6]">
                                <i data-lucide="map-pin" class="w-4 h-4"></i>
                                <span>Saved Addresses</span>
                            </a>
                            <a href="/logout" class="flex items-center gap-2 px-4 py-2 text-rose-600 hover:bg-rose-50 border-t border-subtleBorder mt-1">
                                <i data-lucide="log-out" class="w-4 h-4"></i>
                                <span>Sign Out</span>
                            </a>
                        <?php else: ?>
                            <div class="px-4 py-2 text-center">
                                <a href="/login" class="block w-full py-2 bg-obsidian text-white rounded text-center font-medium hover:bg-slateDark">
                                    Sign In
                                </a>
                                <p class="mt-2 text-mutedText">New to DEMO? <a href="/register" class="text-champagne underline">Register</a></p>
                            </div>
                            <div class="border-t border-subtleBorder mt-2 pt-2">
                                <a href="/admin/login" class="flex items-center gap-2 px-4 py-2 text-mutedText hover:text-obsidian hover:bg-[#FAF9F6]">
                                    <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                    <span>Staff / Admin Portal</span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Shopping Bag / Cart Drawer Button -->
                <button type="button" @click="cartOpen = true" class="relative p-2 text-obsidian hover:text-champagne transition-colors flex items-center" title="Shopping Bag">
                    <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                    <span x-show="cartCount > 0" x-text="cartCount" 
                          class="absolute -top-1 -right-1 bg-obsidian text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center">
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- Live Search Overlay Modal -->
    <div x-show="searchOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm" @keydown.escape.window="searchOpen = false">
        <div class="min-h-screen px-4 text-center">
            <div class="inline-block w-full max-w-2xl my-16 text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl p-6 border border-subtleBorder"
                 @click.away="searchOpen = false">
                <div class="relative flex items-center">
                    <i data-lucide="search" class="w-5 h-5 text-mutedText absolute left-4"></i>
                    <input type="text" 
                           x-model="searchQuery" 
                           @input.debounce.300ms="triggerSearch()"
                           placeholder="Search linen shirts, heavyweight tees, dresses, denim..." 
                           class="w-full pl-12 pr-10 py-3.5 bg-[#FAF9F6] border border-subtleBorder rounded-xl text-sm focus:outline-none focus:border-obsidian focus:ring-1 focus:ring-obsidian"
                           autofocus>
                    <button type="button" @click="searchOpen = false" class="absolute right-4 text-mutedText hover:text-obsidian">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Suggestions / Search Results -->
                <div class="mt-4 max-h-96 overflow-y-auto">
                    <template x-if="searchLoading">
                        <div class="py-8 text-center text-mutedText text-sm">Searching our atelier...</div>
                    </template>
                    <template x-if="!searchLoading && searchResults.length > 0">
                        <div class="divide-y divide-subtleBorder">
                            <template x-for="item in searchResults" :key="item.id">
                                <a :href="'/product/' + item.slug" class="flex items-center gap-4 py-3 hover:bg-[#FAF9F6] px-2 rounded-lg transition-colors">
                                    <img :src="item.primary_image" class="w-12 h-16 object-cover rounded bg-neutral-100 flex-shrink-0" :alt="item.name">
                                    <div class="flex-grow min-w-0">
                                        <p class="text-xs uppercase tracking-wider text-mutedText" x-text="item.category_name"></p>
                                        <h4 class="text-sm font-medium text-obsidian truncate" x-text="item.name"></h4>
                                        <p class="text-xs font-bold text-obsidian mt-0.5">
                                            <?= STORE_CURRENCY_SYMBOL ?><span x-text="item.discount_price ? item.discount_price : item.base_price"></span>
                                        </p>
                                    </div>
                                    <i data-lucide="arrow-right" class="w-4 h-4 text-mutedText"></i>
                                </a>
                            </template>
                        </div>
                    </template>
                    <template x-if="!searchLoading && searchQuery.length >= 2 && searchResults.length === 0">
                        <div class="py-8 text-center text-mutedText text-sm">
                            No garments match "<span x-text="searchQuery"></span>". Try searching for "Linen", "Shirt", or "Silk".
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</header>

