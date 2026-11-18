<?php
/**
 * Dedicated Shopping Bag Page
 */
$cartTotals = \App\Services\CouponPricingEngine::calculateCartTotals();
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-16">
    <div class="mb-8">
        <span class="text-[10px] font-bold tracking-[0.25em] text-champagne uppercase">Review Your Selection</span>
        <h1 class="font-serif text-3xl sm:text-4xl font-bold text-obsidian mt-1">Shopping Bag</h1>
    </div>

    <?php if (empty($cartTotals['items'])): ?>
        <div class="bg-white border border-subtleBorder rounded-3xl p-12 sm:p-20 text-center max-w-2xl mx-auto shadow-xs">
            <div class="w-20 h-20 mx-auto rounded-full bg-[#FAF9F6] border border-subtleBorder flex items-center justify-center text-mutedText mb-6">
                <i data-lucide="shopping-bag" class="w-10 h-10"></i>
            </div>
            <h2 class="font-serif text-2xl font-bold text-obsidian">Your Bag is Empty</h2>
            <p class="text-xs text-mutedText max-w-md mx-auto mt-2 leading-relaxed">
                You have not added any garments to your bag yet. Explore our latest arrivals or seasonal linen drops.
            </p>
            <div class="mt-8">
                <a href="/catalog" class="px-8 py-3.5 bg-obsidian text-white text-xs font-bold uppercase tracking-[0.2em] rounded-xl hover:bg-slateDark transition-colors shadow-md inline-block">
                    Explore Haute Catalog
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
            
            <!-- Items Table (8 cols) -->
            <div class="lg:col-span-8 space-y-6">
                
                <!-- Free Shipping Indicator -->
                <div class="bg-white border border-subtleBorder p-4 rounded-2xl shadow-xs">
                    <?php if ($cartTotals['free_shipping_remaining'] > 0): ?>
                        <p class="text-xs text-mutedText font-medium text-center">
                            Add <strong class="text-obsidian"><?= STORE_CURRENCY_SYMBOL ?><?= number_format($cartTotals['free_shipping_remaining'], 2) ?></strong> more to unlock <span class="text-champagne font-bold">Complimentary Express Shipping</span>
                        </p>
                        <div class="w-full bg-[#FAF9F6] border border-subtleBorder h-2 rounded-full mt-2.5 overflow-hidden">
                            <?php $pct = min(100, round((($cartTotals['subtotal'] - $cartTotals['discount_amount']) / FREE_SHIPPING_THRESHOLD) * 100)); ?>
                            <div class="bg-obsidian h-full transition-all duration-300" style="width: <?= $pct ?>%;"></div>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center justify-center gap-2 text-xs text-emerald-800 font-semibold">
                            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                            <span>You have unlocked Complimentary Express Domestic Delivery!</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Bag Table -->
                <div class="bg-white border border-subtleBorder rounded-2xl overflow-hidden shadow-xs">
                    <div class="divide-y divide-subtleBorder">
                        <?php foreach ($cartTotals['items'] as $item): ?>
                            <div class="p-6 flex flex-col sm:flex-row gap-6 items-start sm:items-center">
                                <a href="/product/<?= htmlspecialchars($item['product_slug']) ?>" class="w-24 h-32 flex-shrink-0 bg-neutral-100 rounded-xl overflow-hidden border border-subtleBorder">
                                    <img src="<?= htmlspecialchars($item['item_image']) ?>" class="w-full h-full object-cover" alt="<?= htmlspecialchars($item['product_name']) ?>">
                                </a>

                                <div class="flex-grow space-y-1">
                                    <span class="text-[10px] uppercase tracking-wider text-mutedText font-mono"><?= htmlspecialchars($item['sku']) ?></span>
                                    <h3 class="text-sm font-semibold text-obsidian">
                                        <a href="/product/<?= htmlspecialchars($item['product_slug']) ?>" class="hover:text-champagne">
                                            <?= htmlspecialchars($item['product_name']) ?>
                                        </a>
                                    </h3>
                                    <p class="text-xs text-mutedText">
                                        Color: <strong class="text-obsidian"><?= htmlspecialchars($item['color_name']) ?></strong> | 
                                        Size: <strong class="text-obsidian"><?= htmlspecialchars($item['size_name']) ?></strong>
                                    </p>
                                    <p class="text-xs text-obsidian font-bold pt-1">
                                        <?= STORE_CURRENCY_SYMBOL ?><?= number_format($item['unit_price'], 2) ?> each
                                    </p>
                                </div>

                                <!-- Quantity Adjustment Stepper -->
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center border border-subtleBorder rounded-lg bg-[#FAF9F6]">
                                        <form action="/cart/update" method="POST" class="inline">
                                            <input type="hidden" name="cart_item_id" value="<?= $item['cart_item_id'] ?>">
                                            <input type="hidden" name="quantity" value="<?= $item['quantity'] - 1 ?>">
                                            <button type="submit" class="px-3 py-1.5 text-xs text-obsidian hover:bg-white transition-colors">−</button>
                                        </form>
                                        <span class="px-3 py-1.5 text-xs font-semibold text-obsidian"><?= $item['quantity'] ?></span>
                                        <form action="/cart/update" method="POST" class="inline">
                                            <input type="hidden" name="cart_item_id" value="<?= $item['cart_item_id'] ?>">
                                            <input type="hidden" name="quantity" value="<?= $item['quantity'] + 1 ?>">
                                            <button type="submit" class="px-3 py-1.5 text-xs text-obsidian hover:bg-white transition-colors" <?= $item['quantity'] >= 5 ? 'disabled' : '' ?>>+</button>
                                        </form>
                                    </div>

                                    <div class="text-right min-w-[90px]">
                                        <span class="text-sm font-bold text-obsidian">
                                            <?= STORE_CURRENCY_SYMBOL ?><?= number_format($item['unit_price'] * $item['quantity'], 2) ?>
                                        </span>
                                    </div>

                                    <form action="/cart/remove" method="POST">
                                        <input type="hidden" name="cart_item_id" value="<?= $item['cart_item_id'] ?>">
                                        <button type="submit" class="text-mutedText hover:text-rose-600 p-2 transition-colors" title="Remove Item">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex justify-between items-center text-xs">
                    <a href="/catalog" class="text-obsidian hover:text-champagne font-semibold uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        <span>Continue Browsing Atelier</span>
                    </a>
                </div>
            </div>

            <!-- Order Summary Sidebar (4 cols) -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- Promo Coupon Box -->
                <div class="bg-white border border-subtleBorder p-6 rounded-2xl shadow-xs space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-[0.2em] text-obsidian">Promotional Code</h3>
                    <form action="/cart/coupon" method="POST" class="flex gap-2">
                        <input type="text" name="coupon_code" placeholder="Enter coupon code" 
                               value="<?= htmlspecialchars($cartTotals['applied_coupon']['code'] ?? '') ?>"
                               class="flex-1 px-3 py-2.5 text-xs bg-[#FAF9F6] border border-subtleBorder rounded-lg uppercase focus:outline-none focus:border-obsidian font-mono">
                        <button type="submit" class="px-5 py-2.5 bg-obsidian text-white text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-slateDark transition-colors">
                            Apply
                        </button>
                    </form>

                    <?php if ($cartTotals['applied_coupon']): ?>
                        <div class="flex items-center justify-between text-xs bg-emerald-50 text-emerald-800 p-3 rounded-lg border border-emerald-200">
                            <div>
                                <p class="font-bold">Code <?= htmlspecialchars($cartTotals['applied_coupon']['code']) ?> Applied</p>
                                <p class="text-[11px] text-emerald-600">You saved <?= STORE_CURRENCY_SYMBOL ?><?= number_format($cartTotals['discount_amount'], 2) ?></p>
                            </div>
                            <form action="/cart/coupon-remove" method="POST">
                                <button type="submit" class="text-rose-600 hover:underline text-xs font-semibold">Remove</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Financial Summary Box -->
                <div class="bg-white border border-subtleBorder p-6 rounded-2xl shadow-xs space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-[0.2em] text-obsidian border-b border-subtleBorder pb-3">Order Financial Summary</h3>

                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between text-mutedText">
                            <span>Subtotal (<?= $cartTotals['items_count'] ?> garments)</span>
                            <span class="font-medium text-obsidian"><?= STORE_CURRENCY_SYMBOL ?><?= number_format($cartTotals['subtotal'], 2) ?></span>
                        </div>
                        <?php if ($cartTotals['discount_amount'] > 0): ?>
                            <div class="flex justify-between text-emerald-700 font-semibold">
                                <span>Promotional Discount</span>
                                <span>- <?= STORE_CURRENCY_SYMBOL ?><?= number_format($cartTotals['discount_amount'], 2) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="flex justify-between text-mutedText">
                            <span>Express Shipping</span>
                            <span><?= $cartTotals['shipping_fee'] == 0 ? '<strong class="text-emerald-700">COMPLIMENTARY</strong>' : STORE_CURRENCY_SYMBOL . number_format($cartTotals['shipping_fee'], 2) ?></span>
                        </div>
                        <div class="flex justify-between text-mutedText text-[11px]">
                            <span>Applicable GST (Included)</span>
                            <span><?= STORE_CURRENCY_SYMBOL ?><?= number_format($cartTotals['tax_amount'], 2) ?></span>
                        </div>
                        <div class="flex justify-between text-base font-bold text-obsidian pt-3 border-t border-subtleBorder">
                            <span>Final Total Payable</span>
                            <span><?= STORE_CURRENCY_SYMBOL ?><?= number_format($cartTotals['total_payable'], 2) ?></span>
                        </div>
                    </div>

                    <?php if (!\App\Services\AuthService::check()): ?>
                        <div class="space-y-2">
                            <a href="/login" class="block w-full py-4 bg-obsidian text-white text-center text-xs font-bold uppercase tracking-[0.2em] rounded-xl hover:bg-slateDark transition-all shadow-md flex items-center justify-center gap-2">
                                <i data-lucide="lock" class="w-4 h-4 text-champagne"></i>
                                <span>Sign In to Checkout</span>
                            </a>
                            <p class="text-center text-[11px] text-amber-700 bg-amber-50 p-2 rounded-lg border border-amber-200">
                                Customer account login is required before proceeding to payment.
                            </p>
                        </div>
                    <?php else: ?>
                        <a href="/checkout" class="block w-full py-4 bg-obsidian text-white text-center text-xs font-bold uppercase tracking-[0.2em] rounded-xl hover:bg-slateDark transition-all shadow-md">
                            Proceed to Checkout
                        </a>
                    <?php endif; ?>

                    <div class="pt-4 border-t border-subtleBorder text-[11px] text-mutedText space-y-2">
                        <div class="flex items-center gap-2">
                            <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i>
                            <span>Verified 256-Bit SSL Encryption</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="refresh-cw" class="w-4 h-4 text-emerald-600"></i>
                            <span>7-Day Doorstep Returns & Size Exchanges</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

