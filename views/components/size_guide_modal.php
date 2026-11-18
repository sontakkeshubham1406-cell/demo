<!-- Size Guide Interactive Modal -->
<div x-show="sizeGuideOpen" x-cloak class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div x-show="sizeGuideOpen" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" 
         @click="sizeGuideOpen = false"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="sizeGuideOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-subtleBorder"
                 @click.away="sizeGuideOpen = false"
                 x-data="{ unit: 'in' }">
                
                <div class="bg-white px-6 pt-6 pb-4 sm:p-8">
                    <div class="flex items-center justify-between border-b border-subtleBorder pb-4">
                        <div>
                            <span class="text-[10px] font-bold tracking-[0.2em] text-champagne uppercase">Garment Specifications</span>
                            <h3 class="text-xl font-serif font-bold text-obsidian">Comprehensive Size & Fit Guide</h3>
                        </div>
                        <button type="button" @click="sizeGuideOpen = false" class="text-mutedText hover:text-obsidian p-1 rounded-md">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <!-- Unit Switcher -->
                    <div class="flex items-center justify-between mt-6 mb-4">
                        <p class="text-xs text-mutedText">Select standard body measurement unit:</p>
                        <div class="inline-flex rounded-lg border border-subtleBorder p-0.5 bg-[#FAF9F6]">
                            <button type="button" 
                                    @click="unit = 'in'" 
                                    :class="unit === 'in' ? 'bg-obsidian text-white shadow-sm' : 'text-mutedText hover:text-obsidian'"
                                    class="px-3 py-1 text-xs font-semibold rounded-md transition-colors">
                                Inches (IN)
                            </button>
                            <button type="button" 
                                    @click="unit = 'cm'" 
                                    :class="unit === 'cm' ? 'bg-obsidian text-white shadow-sm' : 'text-mutedText hover:text-obsidian'"
                                    class="px-3 py-1 text-xs font-semibold rounded-md transition-colors">
                                Centimeters (CM)
                            </button>
                        </div>
                    </div>

                    <!-- Measurement Matrix Table -->
                    <div class="overflow-x-auto border border-subtleBorder rounded-xl">
                        <table class="min-w-full divide-y divide-subtleBorder text-center text-xs">
                            <thead class="bg-[#FAF9F6] text-obsidian font-bold uppercase tracking-wider text-[11px]">
                                <tr>
                                    <th class="py-3 px-4">Size Tag</th>
                                    <th class="py-3 px-4">Chest / Bust</th>
                                    <th class="py-3 px-4">Waist</th>
                                    <th class="py-3 px-4">Shoulder</th>
                                    <th class="py-3 px-4">Length</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-subtleBorder/60 bg-white">
                                <tr>
                                    <td class="py-3 px-4 font-bold bg-[#FAF9F6]/50">XS</td>
                                    <td class="py-3 px-4" x-text="unit === 'in' ? '36 in' : '91 cm'"></td>
                                    <td class="py-3 px-4" x-text="unit === 'in' ? '30 in' : '76 cm'"></td>
                                    <td class="py-3 px-4" x-text="unit === 'in' ? '16.5 in' : '42 cm'"></td>
                                    <td class="py-3 px-4" x-text="unit === 'in' ? '28 in' : '71 cm'"></td>
                                </tr>
                                <tr>
                                    <td class="py-3 px-4 font-bold bg-[#FAF9F6]/50">S</td>
                                    <td class="py-3 px-4" x-text="unit === 'in' ? '38 in' : '96 cm'"></td>
                                    <td class="py-3 px-4" x-text="unit === 'in' ? '32 in' : '81 cm'"></td>
                                    <td class="py-3 px-4" x-text="unit === 'in' ? '17.2 in' : '44 cm'"></td>
                                    <td class="py-3 px-4" x-text="unit === 'in' ? '28.5 in' : '72 cm'"></td>
                                </tr>
                                <tr>
                                    <td class="py-3 px-4 font-bold bg-[#FAF9F6]/50">M</td>
                                    <td class="py-3 px-4" x-text="unit === 'in' ? '40 in' : '101 cm'"></td>
                                    <td class="py-3 px-4" x-text="unit === 'in' ? '34 in' : '86 cm'"></td>
                                    <td class="py-3 px-4" x-text="unit === 'in' ? '18.0 in' : '46 cm'"></td>
                                    <td class="py-3 px-4" x-text="unit === 'in' ? '29.2 in' : '74 cm'"></td>
                                </tr>
                                <tr>
                                    <td class="py-3 px-4 font-bold bg-[#FAF9F6]/50">L</td>
                                    <td class="py-3 px-4" x-text="unit === 'in' ? '42 in' : '107 cm'"></td>
                                    <td class="py-3 px-4" x-text="unit === 'in' ? '36 in' : '91 cm'"></td>
                                    <td class="py-3 px-4" x-text="unit === 'in' ? '18.8 in' : '48 cm'"></td>
                                    <td class="py-3 px-4" x-text="unit === 'in' ? '30.0 in' : '76 cm'"></td>
                                </tr>
                                <tr>
                                    <td class="py-3 px-4 font-bold bg-[#FAF9F6]/50">XL</td>
                                    <td class="py-3 px-4" x-text="unit === 'in' ? '44 in' : '112 cm'"></td>
                                    <td class="py-3 px-4" x-text="unit === 'in' ? '38 in' : '96 cm'"></td>
                                    <td class="py-3 px-4" x-text="unit === 'in' ? '19.5 in' : '50 cm'"></td>
                                    <td class="py-3 px-4" x-text="unit === 'in' ? '30.8 in' : '78 cm'"></td>
                                </tr>
                                <tr>
                                    <td class="py-3 px-4 font-bold bg-[#FAF9F6]/50">XXL</td>
                                    <td class="py-3 px-4" x-text="unit === 'in' ? '46 in' : '117 cm'"></td>
                                    <td class="py-3 px-4" x-text="unit === 'in' ? '40 in' : '101 cm'"></td>
                                    <td class="py-3 px-4" x-text="unit === 'in' ? '20.2 in' : '52 cm'"></td>
                                    <td class="py-3 px-4" x-text="unit === 'in' ? '31.5 in' : '80 cm'"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Measuring Advice -->
                    <div class="mt-6 bg-[#FAF9F6] p-4 rounded-xl border border-subtleBorder text-xs text-mutedText space-y-2">
                        <h4 class="font-bold text-obsidian uppercase tracking-wider text-[11px]">How To Measure Accurately</h4>
                        <p><strong>Chest:</strong> Measure around the fullest part of your chest, keeping the tape horizontal under your arms.</p>
                        <p><strong>Waist:</strong> Measure around your natural waistline, where your trousers naturally sit.</p>
                        <p><strong>Fit Advice:</strong> If you are between two sizes, choose the smaller size for a tailored slim fit, or the larger size for a relaxed drape.</p>
                    </div>
                </div>

                <div class="bg-[#FAF9F6] px-6 py-4 flex justify-end border-t border-subtleBorder">
                    <button type="button" @click="sizeGuideOpen = false" class="px-5 py-2 bg-obsidian text-white text-xs font-semibold uppercase tracking-wider rounded hover:bg-slateDark transition-colors">
                        Close Guide
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

