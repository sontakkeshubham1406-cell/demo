<?php
$cartTotals = \App\Services\CouponPricingEngine::calculateCartTotals();
?>
<!-- Slide-Over Mini-Cart Drawer -->
<div x-show="cartOpen" x-cloak class="relative z-50" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div x-show="cartOpen" 
         x-transition:enter="ease-in-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in-out duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" 
         @click="cartOpen = false"></div>

    <div class="fixed inset-0 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
            <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                <div x-show="cartOpen" 
                     x-transition:enter="transform transition ease-in-out duration-300 sm:duration-500"
                     x-transition:enter-start="translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transform transition ease-in-out duration-300 sm:duration-500"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="translate-x-full"
                     class="pointer-events-auto w-screen max-w-md bg-white shadow-2xl flex flex-col border-l border-subtleBorder">
                    
                    <!-- Drawer Header -->
                    <div class="p-6 border-b border-subtleBorder flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="shopping-bag" class="w-5 h-5 text-obsidian"></i>
                            <h2 class="text-sm font-bold uppercase tracking-[0.18em] text-obsidian">Your Shopping Bag</h2>
                            <span class="text-xs bg-champagneLight text-champagne px-2 py-0.5 rounded-full font-bold">
                                <?= $cartTotals['items_count'] ?>
                            </span>
                        </div>
                        <button type="button" @click="cartOpen = false" class="text-mutedText hover:text-obsidian p-1 rounded-md">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <!-- Free Shipping Progress Bar -->
                    <div class="bg-[#FAF9F6] px-6 py-3 border-b border-subtleBorder">
                        <?php if ($cartTotals['free_shipping_remaining'] > 0): ?>
                            <p class="text-[11px] text-mutedText text-center font-medium">
                                Add <strong class="text-obsidian"><?= STORE_CURRENCY_SYMBOL ?><?= number_format($cartTotals['free_shipping_remaining'], 2) ?></strong> more to unlock <span class="text-champagne font-bold">Complimentary Express Shipping</span>
                            </p>
                            <div class="w-full bg-subtleBorder h-1.5 rounded-full mt-2 overflow-hidden">
                                <?php $pct = min(100, round((($cartTotals['subtotal'] - $cartTotals['discount_amount']) / FREE_SHIPPING_THRESHOLD) * 100)); ?>
                                <div class="bg-obsidian h-full transition-all duration-300" style="width: <?= $pct ?>%;"></div>
                            </div>
                        <?php else: ?>
                            <div class="flex items-center justify-center gap-1.5 text-xs text-emerald-700 font-semibold">
                                <i data-lucide="check" class="w-4 h-4"></i>
                                <span>Complimentary Express Shipping Unlocked!</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Line Items List -->
                    <div class="flex-1 overflow-y-auto p-6 space-y-6">
                        <?php if (empty($cartTotals['items'])): ?>
                            <div class="h-full flex flex-col items-center justify-center text-center py-12">
                                <div class="w-16 h-16 rounded-full bg-[#FAF9F6] border border-subtleBorder flex items-center justify-center text-mutedText mb-4">
                                    <i data-lucide="shopping-bag" class="w-8 h-8"></i>
                                </div>
                                <h3 class="text-sm font-bold uppercase tracking-wider text-obsidian">Your Bag is Empty</h3>
                                <p class="text-xs text-mutedText max-w-xs mt-1">Discover handcrafted linens and timeless wardrobe essentials.</p>
                                <a href="/catalog" @click="cartOpen = false" class="mt-6 inline-block px-6 py-3 bg-obsidian text-white text-xs font-semibold uppercase tracking-widest rounded hover:bg-slateDark transition-colors">
                                    Explore Catalog
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($cartTotals['items'] as $item): ?>
                                <div class="flex gap-4 border-b border-subtleBorder/60 pb-5">
                                    <a href="/product/<?= htmlspecialchars($item['product_slug']) ?>" class="w-20 h-24 flex-shrink-0 bg-neutral-100 rounded overflow-hidden">
                                        <img src="<?= htmlspecialchars($item['item_image']) ?>" class="w-full h-full object-cover" alt="<?= htmlspecialchars($item['product_name']) ?>">
                                    </a>
                                    <div class="flex-grow min-w-0">
                                        <div class="flex justify-between items-start">
                                            <a href="/product/<?= htmlspecialchars($item['product_slug']) ?>" class="text-xs font-semibold text-obsidian hover:text-champagne truncate">
                                                <?= htmlspecialchars($item['product_name']) ?>
                                            </a>
                                            <form action="/cart/remove" method="POST" class="ml-2">
                                                <input type="hidden" name="cart_item_id" value="<?= $item['cart_item_id'] ?>">
                                                <button type="submit" class="text-mutedText hover:text-rose-600 transition-colors p-1" title="Remove">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </div>
                                        
                                        <!-- Variant Specs -->
                                        <p class="text-[11px] text-mutedText mt-0.5">
                                            Size: <strong class="text-obsidian"><?= htmlspecialchars($item['size_name']) ?></strong> | 
                                            Color: <strong class="text-obsidian"><?= htmlspecialchars($item['color_name']) ?></strong>
                                        </p>

                                        <div class="flex items-center justify-between mt-3">
                                            <!-- Quantity Stepper -->
                                            <div class="flex items-center border border-subtleBorder rounded bg-[#FAF9F6]">
                                                <form action="/cart/update" method="POST" class="inline">
                                                    <input type="hidden" name="cart_item_id" value="<?= $item['cart_item_id'] ?>">
                                                    <input type="hidden" name="quantity" value="<?= $item['quantity'] - 1 ?>">
                                                    <button type="submit" class="px-2 py-1 text-xs text-obsidian hover:bg-white transition-colors">−</button>
                                                </form>
                                                <span class="px-2.5 py-1 text-xs font-semibold text-obsidian"><?= $item['quantity'] ?></span>
                                                <form action="/cart/update" method="POST" class="inline">
                                                    <input type="hidden" name="cart_item_id" value="<?= $item['cart_item_id'] ?>">
                                                    <input type="hidden" name="quantity" value="<?= $item['quantity'] + 1 ?>">
                                                    <button type="submit" class="px-2 py-1 text-xs text-obsidian hover:bg-white transition-colors" <?= $item['quantity'] >= 5 ? 'disabled' : '' ?>>+</button>
                                                </form>
                                            </div>

                                            <!-- Price -->
                                            <div class="text-right">
                                                <span class="text-xs font-bold text-obsidian">
                                                    <?= STORE_CURRENCY_SYMBOL ?><?= number_format($item['unit_price'] * $item['quantity'], 2) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($cartTotals['items'])): ?>
                        <!-- Drawer Footer & Checkout Action -->
                        <div class="border-t border-subtleBorder p-6 bg-[#FAF9F6] space-y-4">
                            <!-- Coupon Code Quick Form -->
                            <form action="/cart/coupon" method="POST" class="flex gap-2">
                                <input type="text" name="coupon_code" placeholder="Promo code (e.g. WELCOME10)" 
                                       value="<?= htmlspecialchars($cartTotals['applied_coupon']['code'] ?? '') ?>"
                                       class="flex-1 px-3 py-2 text-xs bg-white border border-subtleBorder rounded uppercase focus:outline-none focus:border-obsidian font-mono">
                                <button type="submit" class="px-4 py-2 bg-obsidian text-white text-xs font-semibold uppercase tracking-wider rounded hover:bg-slateDark transition-colors">
                                    Apply
                                </button>
                            </form>

                            <?php if ($cartTotals['applied_coupon']): ?>
                                <div class="flex items-center justify-between text-xs bg-emerald-50 text-emerald-800 p-2.5 rounded border border-emerald-200">
                                    <span>Coupon <strong><?= htmlspecialchars($cartTotals['applied_coupon']['code']) ?></strong> applied!</span>
                                    <form action="/cart/coupon-remove" method="POST" class="inline">
                                        <button type="submit" class="text-rose-600 hover:underline font-medium text-[11px]">Remove</button>
                                    </form>
                                </div>
                            <?php endif; ?>

                            <!-- Totals Breakdown -->
                            <div class="space-y-1.5 text-xs">
                                <div class="flex justify-between text-mutedText">
                                    <span>Subtotal</span>
                                    <span class="font-medium text-obsidian"><?= STORE_CURRENCY_SYMBOL ?><?= number_format($cartTotals['subtotal'], 2) ?></span>
                                </div>
                                <?php if ($cartTotals['discount_amount'] > 0): ?>
                                    <div class="flex justify-between text-emerald-700 font-medium">
                                        <span>Discount</span>
                                        <span>- <?= STORE_CURRENCY_SYMBOL ?><?= number_format($cartTotals['discount_amount'], 2) ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="flex justify-between text-mutedText">
                                    <span>Shipping</span>
                                    <span><?= $cartTotals['shipping_fee'] == 0 ? '<strong class="text-emerald-700">FREE</strong>' : STORE_CURRENCY_SYMBOL . number_format($cartTotals['shipping_fee'], 2) ?></span>
                                </div>
                                <div class="flex justify-between text-mutedText text-[11px]">
                                    <span>Estimated GST (Included)</span>
                                    <span><?= STORE_CURRENCY_SYMBOL ?><?= number_format($cartTotals['tax_amount'], 2) ?></span>
                                </div>
                                <div class="flex justify-between text-sm font-bold text-obsidian pt-2 border-t border-subtleBorder">
                                    <span>Total Payable</span>
                                    <span><?= STORE_CURRENCY_SYMBOL ?><?= number_format($cartTotals['total_payable'], 2) ?></span>
                                </div>
                            </div>

                            <!-- Checkout CTA -->
                            <?php if (!\App\Services\AuthService::check()): ?>
                                <a href="/login" @click="cartOpen = false" class="block w-full py-3.5 bg-obsidian text-white text-center text-xs font-bold uppercase tracking-[0.2em] rounded hover:bg-slateDark transition-all shadow-md flex items-center justify-center gap-2">
                                    <i data-lucide="lock" class="w-4 h-4 text-champagne"></i>
                                    <span>Sign In to Checkout</span>
                                </a>
                            <?php else: ?>
                                <a href="/checkout" class="block w-full py-3.5 bg-obsidian text-white text-center text-xs font-bold uppercase tracking-[0.2em] rounded hover:bg-slateDark transition-all shadow-md">
                                    Proceed to Checkout
                                </a>
                            <?php endif; ?>

                            <div class="text-center">
                                <a href="/cart" @click="cartOpen = false" class="text-[11px] text-mutedText hover:text-obsidian underline uppercase tracking-wider">
                                    View Full Bag Page
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

