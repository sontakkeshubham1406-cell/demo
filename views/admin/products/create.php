<?php
/**
 * Product & 2D Variant Matrix Builder
 */
$categories = \App\Database\Database::query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC");
$brands = \App\Database\Database::query("SELECT * FROM brands WHERE is_active = 1 ORDER BY name ASC");
$sizes = \App\Database\Database::query("SELECT * FROM sizes ORDER BY sort_order ASC");
$colors = \App\Database\Database::query("SELECT * FROM colors ORDER BY name ASC");
?>
<div class="max-w-5xl mx-auto space-y-8" x-data="variantMatrixBuilder()">
    
    <div class="flex items-center justify-between pb-6 border-b border-slate-200">
        <div>
            <span class="text-[10px] font-bold tracking-[0.2em] text-champagne uppercase">Product Builder</span>
            <h1 class="font-serif text-3xl font-bold text-slate-900 mt-0.5">Create Garment & Generate Variant Matrix</h1>
            <p class="text-xs text-slate-500">Configure parent textile metadata and auto-generate purchasable SKUs per Colorway × Size.</p>
        </div>
        <a href="/admin/products" class="text-xs text-slate-500 hover:text-slate-900 font-semibold flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Cancel</span>
        </a>
    </div>

    <form action="/admin/products/create" method="POST" class="space-y-8 text-xs">
        
        <!-- Section 1: General Product Information -->
        <div class="bg-white border border-slate-200 p-6 sm:p-8 rounded-2xl shadow-xs space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 border-b border-slate-200 pb-3">1. Master Apparel Details</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-700 mb-1">Garment Name *</label>
                    <input type="text" name="name" x-model="productName" required placeholder="e.g. Pure Normandy Linen Camp Collar Shirt" 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-slate-900 text-xs">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Category / Department *</label>
                    <select name="category_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-slate-900 text-xs">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Brand / Label *</label>
                    <select name="brand_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-slate-900 text-xs">
                        <?php foreach ($brands as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Base Price (MRP in <?= STORE_CURRENCY_SYMBOL ?>) *</label>
                    <input type="number" step="0.01" name="base_price" x-model="basePrice" required placeholder="2999.00" 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-slate-900 text-xs font-mono">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Discounted Sale Price (<?= STORE_CURRENCY_SYMBOL ?>)</label>
                    <input type="number" step="0.01" name="discount_price" placeholder="2499.00" 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-slate-900 text-xs font-mono">
                </div>

                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-700 mb-1">Short Editorial Summary</label>
                    <input type="text" name="short_description" placeholder="A brief one-sentence luxury tagline for product cards..." 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-slate-900 text-xs">
                </div>

                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-700 mb-1">Comprehensive Description & Tailoring Story</label>
                    <textarea name="description" rows="3" placeholder="Describe the origin of the textile, silhouette, collar cut, buttons, and craftsmanship..." 
                              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-slate-900 text-xs"></textarea>
                </div>
            </div>
        </div>

        <!-- Section 2: Textile Specifications -->
        <div class="bg-white border border-slate-200 p-6 sm:p-8 rounded-2xl shadow-xs space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 border-b border-slate-200 pb-3">2. Textile & Care Specifications</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Fabric Composition</label>
                    <input type="text" name="fabric_composition" placeholder="e.g. 100% French Normandy Linen (180 GSM)" 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Model Height & Size Worn</label>
                    <input type="text" name="model_specifications" placeholder="e.g. Model is 6'1&quot;, chest 39&quot;, wearing Size M" 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-700 mb-1">Wash & Care Instructions</label>
                    <input type="text" name="wash_care_instructions" placeholder="e.g. Cold hand wash or dry clean. Hang dry in shade. Warm iron." 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-700 mb-1">Primary High-Resolution Image URL *</label>
                    <input type="url" name="primary_image_url" required placeholder="https://images.unsplash.com/photo-..." 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>
            </div>
        </div>

        <!-- Section 3: 2D Variant Matrix Generator (Colors x Sizes) -->
        <div class="bg-white border border-slate-200 p-6 sm:p-8 rounded-2xl shadow-xs space-y-6">
            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">3. 2D Apparel Variant Matrix Generator</h3>
                    <p class="text-xs text-slate-500">Pick active sizes and colorways to instantly generate distinct SKUs and initial inventory allocations.</p>
                </div>
                <button type="button" @click="generateMatrix()" class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold uppercase tracking-wider rounded-xl transition-colors shadow-xs">
                    Generate SKUs Matrix
                </button>
            </div>

            <!-- Choose Colorways -->
            <div class="space-y-2">
                <label class="block font-semibold text-slate-700">Select Available Colorways:</label>
                <div class="flex flex-wrap gap-3">
                    <?php foreach ($colors as $c): ?>
                        <label class="flex items-center gap-2 px-3 py-1.5 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                            <input type="checkbox" value="<?= $c['id'] ?>" data-name="<?= htmlspecialchars($c['name']) ?>" x-model="selectedColors" class="rounded text-slate-900">
                            <span class="w-3.5 h-3.5 rounded-full border border-slate-300" style="background-color: <?= $c['hex_code'] ?>"></span>
                            <span><?= htmlspecialchars($c['name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Choose Sizes -->
            <div class="space-y-2">
                <label class="block font-semibold text-slate-700">Select Available Sizes:</label>
                <div class="flex flex-wrap gap-3">
                    <?php foreach ($sizes as $s): ?>
                        <label class="flex items-center gap-2 px-3.5 py-1.5 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50 font-bold">
                            <input type="checkbox" value="<?= $s['id'] ?>" data-name="<?= htmlspecialchars($s['name']) ?>" x-model="selectedSizes" class="rounded text-slate-900">
                            <span><?= htmlspecialchars($s['name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Generated 2D Matrix Rows -->
            <div x-show="matrixRows.length > 0" class="pt-4 border-t border-slate-200">
                <h4 class="font-bold text-slate-900 uppercase tracking-wider mb-3">Generated Variant Matrix (<span x-text="matrixRows.length"></span> Unique SKUs)</h4>
                
                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-700 font-bold uppercase text-[11px] border-b border-slate-200">
                            <tr>
                                <th class="py-2.5 px-4">Color</th>
                                <th class="py-2.5 px-4">Size</th>
                                <th class="py-2.5 px-4">Auto-Generated SKU</th>
                                <th class="py-2.5 px-4">Initial Stock Quantity</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(row, idx) in matrixRows" :key="idx">
                                <tr class="hover:bg-slate-50">
                                    <td class="py-2.5 px-4 font-semibold text-slate-900" x-text="row.color_name"></td>
                                    <td class="py-2.5 px-4 font-bold text-slate-900" x-text="row.size_name"></td>
                                    <td class="py-2.5 px-4">
                                        <input type="text" :name="'variants[' + idx + '][sku]'" :value="row.sku" required class="px-2.5 py-1 bg-slate-50 border border-slate-200 rounded font-mono text-[11px] w-full">
                                        <input type="hidden" :name="'variants[' + idx + '][color_id]'" :value="row.color_id">
                                        <input type="hidden" :name="'variants[' + idx + '][size_id]'" :value="row.size_id">
                                    </td>
                                    <td class="py-2.5 px-4">
                                        <input type="number" :name="'variants[' + idx + '][stock]'" value="20" min="0" required class="w-24 px-2.5 py-1 bg-slate-50 border border-slate-200 rounded font-mono text-[11px]">
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <a href="/admin/products" class="px-6 py-3 border border-slate-200 text-slate-700 rounded-xl font-bold uppercase tracking-wider hover:bg-slate-50 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-8 py-3 bg-slate-900 text-white rounded-xl font-bold uppercase tracking-[0.2em] hover:bg-slate-800 transition-colors shadow-lg">
                Save Product & Commit Matrix
            </button>
        </div>
    </form>
</div>

<script>
function variantMatrixBuilder() {
    return {
        productName: '',
        basePrice: '2499.00',
        selectedColors: [1, 2], // Black, White by default
        selectedSizes: [2, 3, 4], // S, M, L by default
        matrixRows: [],

        init() {
            this.generateMatrix();
        },

        generateMatrix() {
            let colors = <?= json_encode($colors) ?>;
            let sizes = <?= json_encode($sizes) ?>;
            let rows = [];

            this.selectedColors.forEach(cId => {
                let colorObj = colors.find(c => c.id == cId);
                let colorName = colorObj ? colorObj.name : 'CLR';
                let colorPrefix = colorName.substring(0, 3).toUpperCase();

                this.selectedSizes.forEach(sId => {
                    let sizeObj = sizes.find(s => s.id == sId);
                    let sizeName = sizeObj ? sizeObj.name : 'SZ';
                    
                    let shortProd = this.productName ? this.productName.substring(0, 4).toUpperCase().replace(/[^A-Z]/g, '') : 'GAR';
                    let sku = `VEL-${shortProd}-${colorPrefix}-${sizeName}`;

                    rows.push({
                        color_id: cId,
                        color_name: colorName,
                        size_id: sId,
                        size_name: sizeName,
                        sku: sku,
                        stock: 25
                    });
                });
            });

            this.matrixRows = rows;
        }
    }
}
</script>

