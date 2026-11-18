<footer class="bg-obsidian text-white pt-16 pb-12 border-t border-neutral-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Top Newsletter & Value Proposition Strip -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 pb-16 border-b border-neutral-800">
            <div>
                <span class="text-[10px] font-bold tracking-[0.25em] text-champagne uppercase">Private Atelier Membership</span>
                <h3 class="font-serif text-2xl sm:text-3xl font-bold mt-2 text-white">Join the VELORIS Circle</h3>
                <p class="text-xs text-neutral-400 mt-2 max-w-md leading-relaxed">
                    Receive confidential previews of upcoming seasonal drops, private capsule collections, and bespoke styling edits.
                </p>
            </div>
            <div class="flex items-center">
                <form action="#" method="POST" class="w-full flex flex-col sm:flex-row gap-3">
                    <input type="email" placeholder="Enter your email address" required 
                           class="flex-grow px-4 py-3 bg-neutral-900 border border-neutral-700 text-white rounded text-xs focus:outline-none focus:border-champagne placeholder-neutral-500">
                    <button type="submit" class="px-6 py-3 bg-champagne text-obsidian text-xs font-bold uppercase tracking-[0.15em] rounded hover:bg-white transition-colors flex-shrink-0">
                        Subscribe
                    </button>
                </form>
            </div>
        </div>

        <!-- 4-Column Navigation Links -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 py-16 text-xs text-neutral-400">
            <!-- Col 1: Collections -->
            <div class="space-y-3">
                <h4 class="font-bold text-white uppercase tracking-[0.2em] text-[11px]">Collections</h4>
                <ul class="space-y-2">
                    <li><a href="/catalog?category=men-shirts" class="hover:text-champagne transition-colors">Normandy Linen Shirts</a></li>
                    <li><a href="/catalog?category=men-tshirts" class="hover:text-champagne transition-colors">280 GSM Boxy Tees</a></li>
                    <li><a href="/catalog?category=women-dresses" class="hover:text-champagne transition-colors">Mulberry Silk Slip Dresses</a></li>
                    <li><a href="/catalog?category=men-jeans" class="hover:text-champagne transition-colors">Japanese Selvedge Denim</a></li>
                    <li><a href="/catalog?category=women-ethnic" class="hover:text-champagne transition-colors">Handcrafted Chanderi Kurtas</a></li>
                </ul>
            </div>

            <!-- Col 2: Customer Concierge -->
            <div class="space-y-3">
                <h4 class="font-bold text-white uppercase tracking-[0.2em] text-[11px]">Concierge</h4>
                <ul class="space-y-2">
                    <li><a href="/account/orders" class="hover:text-champagne transition-colors">Track Your Order</a></li>
                    <li><a href="/pages/shipping-policy" class="hover:text-champagne transition-colors">Shipping & Delivery Rates</a></li>
                    <li><a href="/pages/returns-exchanges" class="hover:text-champagne transition-colors">7-Day Easy Returns & Exchanges</a></li>
                    <li><a href="/pages/size-guide" class="hover:text-champagne transition-colors">Interactive Size Guide</a></li>
                    <li><a href="/pages/contact" class="hover:text-champagne transition-colors">Contact Concierge</a></li>
                </ul>
            </div>

            <!-- Col 3: Brand & Policies -->
            <div class="space-y-3">
                <h4 class="font-bold text-white uppercase tracking-[0.2em] text-[11px]">The House</h4>
                <ul class="space-y-2">
                    <li><a href="/pages/about-us" class="hover:text-champagne transition-colors">Our Atelier & Craft</a></li>
                    <li><a href="/pages/terms-and-conditions" class="hover:text-champagne transition-colors">Terms of Service</a></li>
                    <li><a href="/pages/privacy-policy" class="hover:text-champagne transition-colors">Privacy & Cookie Policy</a></li>
                    <li><a href="/admin/login" class="hover:text-champagne transition-colors">Staff Portal Access</a></li>
                </ul>
            </div>

            <!-- Col 4: Contact & Verification -->
            <div class="space-y-3">
                <h4 class="font-bold text-white uppercase tracking-[0.2em] text-[11px]">Headquarters</h4>
                <p class="leading-relaxed">
                    VELORIS LUXE Atelier<br>
                    Level 8, Signature Towers, Bandra Kurla Complex (BKC)<br>
                    Mumbai, Maharashtra 400051<br>
                    GSTIN: <?= STORE_GSTIN ?>
                </p>
                <p class="text-[11px]">
                    Concierge Desk: <span class="text-white"><?= STORE_PHONE ?></span><br>
                    Email: <span class="text-white"><?= STORE_SUPPORT_EMAIL ?></span>
                </p>
            </div>
        </div>

        <!-- Bottom Payment Badges & Copyright -->
        <div class="border-t border-neutral-800 pt-8 flex flex-col md:flex-row items-center justify-between text-[11px] text-neutral-500 gap-4">
            <p>&copy; <?= date('Y') ?> <?= STORE_NAME ?> Private Limited. All Rights Reserved. Master Architecture v2.0.</p>
            
            <!-- Payment Trust Seals -->
            <div class="flex items-center gap-3 text-neutral-400">
                <span class="text-[10px] uppercase tracking-wider text-neutral-500">Secure 256-Bit SSL Checkout:</span>
                <span class="bg-neutral-900 border border-neutral-700 px-2 py-0.5 rounded text-[10px] text-white font-semibold">Razorpay</span>
                <span class="bg-neutral-900 border border-neutral-700 px-2 py-0.5 rounded text-[10px] text-white font-semibold">UPI</span>
                <span class="bg-neutral-900 border border-neutral-700 px-2 py-0.5 rounded text-[10px] text-white font-semibold">Visa</span>
                <span class="bg-neutral-900 border border-neutral-700 px-2 py-0.5 rounded text-[10px] text-white font-semibold">MasterCard</span>
                <span class="bg-neutral-900 border border-neutral-700 px-2 py-0.5 rounded text-[10px] text-white font-semibold">RuPay</span>
            </div>
        </div>
    </div>
</footer>

