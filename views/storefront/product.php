<?php
/**
 * Luxury Product Details Page (PDP)
 * Variables: $product (array with variants, images, reviews, colors, sizes, related)
 */
$primaryImg = $product['images'][0]['image_url'] ?? '';
$initialColor = $product['colors'][0] ?? null;
$initialSize = $product['sizes'][0] ?? null;

// Initial resolved variant
$initialVariant = null;
foreach ($product['variants'] as $v) {
    if ($v['color_id'] == ($initialColor['id'] ?? 0) && $v['size_id'] == ($initialSize['id'] ?? 0)) {
        $initialVariant = $v;
        break;
    }
}
if (!$initialVariant && !empty($product['variants'])) {
    $initialVariant = $product['variants'][0];
}

$variantsJson = json_encode($product['variants']);
$imagesJson = json_encode($product['images']);
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12" 
     x-data="pdpManager(<?= htmlspecialchars($variantsJson) ?>, <?= htmlspecialchars($imagesJson) ?>, <?= $initialColor['id'] ?? 0 ?>, <?= $initialSize['id'] ?? 0 ?>)">

    <!-- Breadcrumb -->
    <nav class="flex text-xs text-mutedText mb-8">
        <ol class="inline-flex items-center space-x-1.5">
            <li><a href="/" class="hover:text-obsidian">Home</a></li>
            <li><span>/</span></li>
            <li><a href="/catalog?category=<?= urlencode($product['category_slug']) ?>" class="hover:text-obsidian"><?= htmlspecialchars($product['category_name']) ?></a></li>
            <li><span>/</span></li>
            <li class="font-medium text-obsidian truncate max-w-xs"><?= htmlspecialchars($product['name']) ?></li>
        </ol>
    </nav>

    <!-- Split View: Gallery Left (60%), Purchase Panel Right (40%) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
        
        <!-- Left: Image Gallery (7 cols) -->
        <div class="lg:col-span-7 flex flex-col-reverse sm:flex-row gap-4">
            
            <!-- Thumbnails Column -->
            <div class="flex sm:flex-col gap-3 overflow-x-auto sm:overflow-y-auto sm:max-h-[650px] flex-shrink-0">
                <template x-for="(img, idx) in activeImages" :key="idx">
                    <button type="button" 
                            @click="selectedImage = img.image_url"
                            :class="selectedImage === img.image_url ? 'ring-2 ring-obsidian' : 'opacity-70 hover:opacity-100'"
                            class="w-16 h-20 sm:w-20 sm:h-24 rounded-lg overflow-hidden border border-subtleBorder flex-shrink-0 transition-all bg-neutral-100">
                        <img :src="img.image_url" class="w-full h-full object-cover" :alt="product.name">
                    </button>
                </template>
            </div>

            <!-- Primary High-Res Display -->
            <div class="flex-grow aspect-[3/4] bg-neutral-100 rounded-2xl overflow-hidden border border-subtleBorder relative group">
                <img :src="selectedImage" 
                     alt="<?= htmlspecialchars($product['name']) ?>" 
                     class="w-full h-full object-cover object-top transition-transform duration-500 group-hover:scale-105">
                
                <?php if (!empty($product['discount_price'])): ?>
                    <span class="absolute top-4 left-4 bg-obsidian text-white text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded shadow-md">
                        SALE
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: Sticky Purchase Panel (5 cols) -->
        <div class="lg:col-span-5 space-y-6">
            
            <!-- Brand & Title -->
            <div class="border-b border-subtleBorder pb-6">
                <span class="text-xs uppercase tracking-[0.2em] text-champagne font-bold">
                    <?= htmlspecialchars($product['brand_name'] ?? 'VELORIS Atelier') ?>
                </span>
                <h1 class="font-serif text-2xl sm:text-3xl font-bold text-obsidian mt-1 leading-snug">
                    <?= htmlspecialchars($product['name']) ?>
                </h1>

                <!-- Rating & Reviews Counter -->
                <div class="flex items-center gap-3 mt-3">
                    <div class="flex items-center text-amber-500">
                        <?php $rating = round($product['rating_avg'] ?? 5); ?>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i data-lucide="star" class="w-4 h-4 <?= $i <= $rating ? 'fill-amber-500' : 'text-neutral-300' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <span class="text-xs text-mutedText">
                        (<?= $product['rating_count'] ?> customer reviews)
                    </span>
                    <span class="text-neutral-300">|</span>
                    <span class="text-xs font-mono text-mutedText" x-text="'SKU: ' + activeVariant.sku"></span>
                </div>

                <!-- Price Block -->
                <div class="flex items-baseline gap-3 mt-4">
                    <span class="text-2xl font-bold text-obsidian">
                        <?= STORE_CURRENCY_SYMBOL ?><span x-text="formatPrice(activeVariant.price_override || <?= $product['discount_price'] ?? $product['base_price'] ?>)"></span>
                    </span>
                    <?php if (!empty($product['discount_price'])): ?>
                        <span class="text-base text-mutedText line-through">
                            <?= STORE_CURRENCY_SYMBOL ?><?= number_format($product['base_price'], 2) ?>
                        </span>
                        <span class="text-xs text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                            Save <?= STORE_CURRENCY_SYMBOL ?><?= number_format($product['base_price'] - $product['discount_price'], 2) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <p class="text-[11px] text-mutedText mt-1">Inclusive of all taxes. Free express domestic delivery over ₹999.</p>
            </div>

            <!-- Color Swatch Selector -->
            <div class="space-y-3">
                <div class="flex justify-between items-center text-xs">
                    <span class="font-bold uppercase tracking-wider text-obsidian">
                        Color: <span class="font-normal text-mutedText" x-text="activeColorName"></span>
                    </span>
                </div>
                <div class="flex items-center gap-3">
                    <?php foreach ($product['colors'] as $color): ?>
                        <button type="button" 
                                @click="selectColor(<?= $color['id'] ?>, '<?= htmlspecialchars($color['name']) ?>')"
                                :class="selectedColorId === <?= $color['id'] ?> ? 'ring-2 ring-obsidian ring-offset-2' : 'border-neutral-300 hover:scale-105'"
                                class="w-7 h-7 rounded-full border shadow-sm transition-all flex items-center justify-center"
                                style="background-color: <?= htmlspecialchars($color['hex_code']) ?>"
                                title="<?= htmlspecialchars($color['name']) ?>">
                            <template x-if="selectedColorId === <?= $color['id'] ?>">
                                <i data-lucide="check" class="w-3.5 h-3.5 <?= $color['hex_code'] === '#F8F9FA' ? 'text-obsidian' : 'text-white' ?>"></i>
                            </template>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Size Selection & Size Guide Trigger -->
            <div class="space-y-3">
                <div class="flex justify-between items-center text-xs">
                    <span class="font-bold uppercase tracking-wider text-obsidian">Select Size</span>
                    <button type="button" @click="sizeGuideOpen = true" class="text-champagne hover:underline font-semibold flex items-center gap-1">
                        <i data-lucide="ruler" class="w-3.5 h-3.5"></i>
                        <span>Size Guide</span>
                    </button>
                </div>

                <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                    <?php foreach ($product['sizes'] as $size): ?>
                        <button type="button" 
                                @click="selectSize(<?= $size['id'] ?>, '<?= htmlspecialchars($size['name']) ?>')"
                                :class="{
                                    'bg-obsidian text-white border-obsidian': selectedSizeId === <?= $size['id'] ?>,
                                    'bg-white text-obsidian border-subtleBorder hover:border-obsidian': selectedSizeId !== <?= $size['id'] ?> && isSizeAvailable(<?= $size['id'] ?>),
                                    'opacity-30 bg-neutral-100 cursor-not-allowed line-through': !isSizeAvailable(<?= $size['id'] ?>)
                                }"
                                class="py-2.5 px-3 border rounded-lg text-xs font-bold uppercase tracking-wider transition-all">
                            <?= htmlspecialchars($size['name']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Stock Availability & Urgency Indicator -->
            <div>
                <template x-if="activeVariant.available_to_sell > 0 && activeVariant.available_to_sell <= 5">
                    <div class="flex items-center gap-2 text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-3 py-2 rounded-lg">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600"></i>
                        <span>Hurry, only <strong x-text="activeVariant.available_to_sell"></strong> left in our Mumbai atelier!</span>
                    </div>
                </template>
                <template x-if="activeVariant.available_to_sell > 5">
                    <div class="flex items-center gap-1.5 text-xs text-emerald-700">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                        <span>In Stock & Ready for 48h Dispatch</span>
                    </div>
                </template>
                <template x-if="activeVariant.available_to_sell <= 0">
                    <div class="flex items-center gap-1.5 text-xs text-rose-700 font-semibold bg-rose-50 border border-rose-200 p-2.5 rounded-lg">
                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                        <span>Currently Out of Stock for this variant.</span>
                    </div>
                </template>
            </div>

            <!-- Action Buttons: Add to Bag & Buy Now -->
            <div class="space-y-3 pt-2">
                <?php if (!\App\Services\AuthService::check()): ?>
                    <div class="flex items-center justify-between p-3 bg-amber-50/90 border border-amber-200/80 rounded-xl text-amber-900 text-xs">
                        <div class="flex items-center gap-2">
                            <i data-lucide="lock" class="w-4 h-4 text-amber-600 flex-shrink-0"></i>
                            <span>Sign-in is mandatory before adding items to cart or initiating payment.</span>
                        </div>
                        <a href="/login" class="font-bold underline hover:text-amber-950 uppercase tracking-wider text-[11px] whitespace-nowrap ml-2">Sign In</a>
                    </div>
                <?php endif; ?>

                <form action="/cart/add" method="POST" class="flex gap-3">
                    <input type="hidden" name="variant_id" :value="activeVariant.id">
                    <input type="hidden" name="quantity" value="1">
                    
                    <button type="submit" 
                            :disabled="activeVariant.available_to_sell <= 0"
                            class="flex-1 py-4 bg-obsidian text-white text-xs font-bold uppercase tracking-[0.2em] rounded-xl hover:bg-slateDark transition-all disabled:opacity-40 disabled:cursor-not-allowed shadow-md flex items-center justify-center gap-2">
                        <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                        <span x-text="activeVariant.available_to_sell > 0 ? 'Add to Bag' : 'Sold Out'"></span>
                    </button>
                </form>

                <form action="/checkout/direct" method="POST">
                    <input type="hidden" name="variant_id" :value="activeVariant.id">
                    <button type="submit" 
                            :disabled="activeVariant.available_to_sell <= 0"
                            class="w-full py-3.5 bg-champagneLight text-obsidian border border-champagne/40 text-xs font-bold uppercase tracking-[0.2em] rounded-xl hover:bg-champagne hover:text-white transition-all disabled:opacity-40">
                        Buy Now with 1-Click
                    </button>
                </form>
            </div>

            <!-- Accordions: Craft, Specs, Returns -->
            <div class="border-t border-subtleBorder pt-6 space-y-3 text-xs" x-data="{ activeTab: 1 }">
                <!-- Tab 1: Fabric & Craft -->
                <div class="border border-subtleBorder rounded-xl overflow-hidden bg-white">
                    <button type="button" @click="activeTab = activeTab === 1 ? 0 : 1" class="w-full p-4 flex justify-between items-center font-bold text-obsidian uppercase tracking-wider text-left">
                        <span>Fabric Composition & Tailoring</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" :class="activeTab === 1 ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="activeTab === 1" class="px-4 pb-4 text-mutedText space-y-2 border-t border-subtleBorder/50 pt-3">
                        <p><strong>Composition:</strong> <?= htmlspecialchars($product['fabric_composition']) ?></p>
                        <p><strong>Care:</strong> <?= htmlspecialchars($product['wash_care_instructions']) ?></p>
                        <p class="leading-relaxed"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                    </div>
                </div>

                <!-- Tab 2: Model & Fit -->
                <div class="border border-subtleBorder rounded-xl overflow-hidden bg-white">
                    <button type="button" @click="activeTab = activeTab === 2 ? 0 : 2" class="w-full p-4 flex justify-between items-center font-bold text-obsidian uppercase tracking-wider text-left">
                        <span>Model Specifications & Fit</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" :class="activeTab === 2 ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="activeTab === 2" class="px-4 pb-4 text-mutedText space-y-2 border-t border-subtleBorder/50 pt-3">
                        <p><?= htmlspecialchars($product['model_specifications']) ?></p>
                        <p>Relaxed luxury fit. Tailored to allow graceful movement without feeling oversized.</p>
                    </div>
                </div>

                <!-- Tab 3: Complimentary Shipping & Returns -->
                <div class="border border-subtleBorder rounded-xl overflow-hidden bg-white">
                    <button type="button" @click="activeTab = activeTab === 3 ? 0 : 3" class="w-full p-4 flex justify-between items-center font-bold text-obsidian uppercase tracking-wider text-left">
                        <span>Shipping & 7-Day Doorstep Returns</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" :class="activeTab === 3 ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="activeTab === 3" class="px-4 pb-4 text-mutedText space-y-2 border-t border-subtleBorder/50 pt-3 leading-relaxed">
                        <p>All items ship in eco-friendly luxury garment bags via Blue Dart or Delhivery Air Express.</p>
                        <p>If the fit does not satisfy your expectations, request a doorstep reverse pickup or size exchange within 7 days from your account dashboard.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Reviews Section -->
    <div class="mt-20 pt-16 border-t border-subtleBorder">
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-12">
                <span class="text-[10px] font-bold tracking-[0.25em] text-champagne uppercase">Verified Atelier Feedback</span>
                <h2 class="font-serif text-3xl font-bold text-obsidian mt-1">Customer Reviews</h2>
            </div>

            <!-- Existing Reviews List -->
            <div class="space-y-6">
                <?php if (empty($product['reviews'])): ?>
                    <p class="text-center text-xs text-mutedText py-6">Be the first to review this garment.</p>
                <?php else: ?>
                    <?php foreach ($product['reviews'] as $rev): ?>
                        <div class="bg-white border border-subtleBorder p-6 rounded-2xl shadow-xs space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-xs text-obsidian"><?= htmlspecialchars($rev['reviewer_name'] ?? 'Verified Client') ?></span>
                                    <span class="text-[10px] bg-emerald-50 text-emerald-800 border border-emerald-200 px-2 py-0.5 rounded-full font-medium">Verified Purchase</span>
                                </div>
                                <span class="text-[11px] text-mutedText"><?= date('d M Y', strtotime($rev['created_at'])) ?></span>
                            </div>
                            <div class="flex text-amber-500">
                                <?php for ($s = 1; $s <= 5; $s++): ?>
                                    <i data-lucide="star" class="w-3.5 h-3.5 <?= $s <= $rev['rating'] ? 'fill-amber-500' : 'text-neutral-200' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <?php if (!empty($rev['title'])): ?>
                                <h4 class="text-xs font-bold text-obsidian"><?= htmlspecialchars($rev['title']) ?></h4>
                            <?php endif; ?>
                            <p class="text-xs text-mutedText leading-relaxed"><?= nl2br(htmlspecialchars($rev['comment'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Add Review Form -->
            <div class="mt-12 bg-[#FAF9F6] border border-subtleBorder rounded-2xl p-6 sm:p-8">
                <h3 class="font-serif text-xl font-bold text-obsidian mb-4">Write a Review</h3>
                <form action="/reviews/create" method="POST" class="space-y-4 text-xs">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    
                    <div>
                        <label class="block font-medium text-obsidian mb-1">Your Rating</label>
                        <select name="rating" class="px-3 py-2 bg-white border border-subtleBorder rounded-lg focus:outline-none focus:border-obsidian">
                            <option value="5">★★★★★ (5 Stars - Exceptional)</option>
                            <option value="4">★★★★☆ (4 Stars - Great)</option>
                            <option value="3">★★★☆☆ (3 Stars - Average)</option>
                            <option value="2">★★☆☆☆ (2 Stars - Disappointed)</option>
                            <option value="1">★☆☆☆☆ (1 Star - Poor)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-medium text-obsidian mb-1">Review Headline</label>
                        <input type="text" name="title" placeholder="Summarize your experience (e.g. Magnificent linen hand-feel)" 
                               class="w-full px-3 py-2 bg-white border border-subtleBorder rounded-lg focus:outline-none focus:border-obsidian">
                    </div>

                    <div>
                        <label class="block font-medium text-obsidian mb-1">Detailed Comment</label>
                        <textarea name="comment" rows="3" required placeholder="Describe the fit, fabric texture, drape, and sizing accuracy..." 
                                  class="w-full px-3 py-2 bg-white border border-subtleBorder rounded-lg focus:outline-none focus:border-obsidian"></textarea>
                    </div>

                    <button type="submit" class="px-6 py-2.5 bg-obsidian text-white text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-slateDark transition-colors">
                        Submit Review
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function pdpManager(variants, images, initialColorId, initialSizeId) {
    return {
        variants: variants,
        images: images,
        selectedColorId: initialColorId,
        selectedSizeId: initialSizeId,
        activeColorName: '<?= htmlspecialchars($initialColor['name'] ?? '') ?>',
        selectedImage: '<?= htmlspecialchars($primaryImg) ?>',

        get activeImages() {
            // Filter images by selected colorway if tagged, else show all
            let colorImgs = this.images.filter(img => img.color_id == this.selectedColorId);
            return colorImgs.length > 0 ? colorImgs : this.images;
        },

        get activeVariant() {
            let v = this.variants.find(v => v.color_id == this.selectedColorId && v.size_id == this.selectedSizeId);
            return v || (this.variants[0] || {});
        },

        selectColor(colorId, colorName) {
            this.selectedColorId = colorId;
            this.activeColorName = colorName;
            
            // Switch primary image to first photo of this color
            let colorImgs = this.images.filter(img => img.color_id == colorId);
            if (colorImgs.length > 0) {
                this.selectedImage = colorImgs[0].image_url;
            }
        },

        selectSize(sizeId, sizeName) {
            this.selectedSizeId = sizeId;
        },

        isSizeAvailable(sizeId) {
            let v = this.variants.find(v => v.color_id == this.selectedColorId && v.size_id == sizeId);
            return v && v.available_to_sell > 0;
        },

        formatPrice(val) {
            return Number(val).toLocaleString('en-IN', { minimumFractionDigits: 2 });
        }
    }
}
</script>

