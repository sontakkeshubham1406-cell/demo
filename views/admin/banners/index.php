<?php
/**
 * Admin Hero Banners & Promotional CMS
 */
$banners = \App\Database\Database::query("SELECT * FROM banners ORDER BY sort_order ASC");
?>
<div class="space-y-6" x-data="{ newBannerModal: false }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-200 gap-4">
        <div>
            <span class="text-[10px] font-bold tracking-[0.2em] text-champagne uppercase">Storefront Merchandising</span>
            <h1 class="font-serif text-3xl font-bold text-slate-900 mt-0.5">Hero Banners & CMS</h1>
            <p class="text-xs text-slate-500">Configure editorial homepage sliders, promotional campaign graphics, and click targets.</p>
        </div>

        <button type="button" @click="newBannerModal = true" class="px-5 py-2.5 bg-slate-900 text-white text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-slate-800 transition-colors flex items-center gap-2 shadow-sm self-start sm:self-auto">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Add Hero Slider Banner</span>
        </button>
    </div>

    <!-- Banners Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php foreach ($banners as $b): ?>
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs space-y-4">
                <div class="h-48 relative overflow-hidden bg-slate-900">
                    <img src="<?= htmlspecialchars($b['image_url_desktop']) ?>" class="w-full h-full object-cover opacity-80" alt="">
                    <span class="absolute top-3 right-3 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider <?= $b['is_active'] ? 'bg-emerald-500 text-white' : 'bg-slate-700 text-slate-300' ?>">
                        <?= $b['is_active'] ? 'Active Slide' : 'Hidden' ?>
                    </span>
                    <div class="absolute bottom-3 left-3 right-3 text-white">
                        <h4 class="font-serif text-lg font-bold leading-tight"><?= htmlspecialchars($b['title']) ?></h4>
                        <p class="text-[11px] text-slate-200 line-clamp-1"><?= htmlspecialchars($b['subtitle']) ?></p>
                    </div>
                </div>

                <div class="p-4 flex items-center justify-between text-xs border-t border-slate-100">
                    <span class="text-slate-500 font-mono">Target: <?= htmlspecialchars($b['target_url']) ?></span>
                    <span class="text-slate-400 font-bold">Sort Order: <?= $b['sort_order'] ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Add Banner Modal -->
    <div x-show="newBannerModal" x-cloak class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 sm:p-8 space-y-4 border border-slate-200 shadow-2xl text-xs" @click.away="newBannerModal = false">
            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <h3 class="font-serif text-lg font-bold text-slate-900">Add Hero Campaign Slider</h3>
                <button type="button" @click="newBannerModal = false" class="text-slate-400 hover:text-slate-900">&times;</button>
            </div>

            <form action="/admin/banners/create" method="POST" class="space-y-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Headline Title *</label>
                    <input type="text" name="title" required placeholder="e.g. The Festive Chanderi Edit" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Subtitle / Tagline</label>
                    <input type="text" name="subtitle" placeholder="e.g. Royal zari embroideries and handwoven chanderi silks." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Desktop Photography URL *</label>
                    <input type="url" name="image_url_desktop" required placeholder="https://images.unsplash.com/..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Target Click URL</label>
                    <input type="text" name="target_url" value="/catalog" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono">
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-200">
                    <button type="button" @click="newBannerModal = false" class="px-4 py-2 border rounded-xl font-bold uppercase tracking-wider text-slate-600 hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-6 py-2 bg-slate-900 text-white rounded-xl font-bold uppercase tracking-wider hover:bg-slate-800 transition-colors shadow-sm">Save Banner</button>
                </div>
            </form>
        </div>
    </div>
</div>

