<?php
/**
 * Admin Coupon & Promotional Rule Manager
 */
$coupons = \App\Database\Database::query("
    SELECT c.*,
        (SELECT COUNT(id) FROM coupon_usages WHERE coupon_id = c.id) as used_count
    FROM coupons c
    ORDER BY c.created_at DESC
");
?>
<div class="space-y-6" x-data="{ newCouponModal: false }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-200 gap-4">
        <div>
            <span class="text-[10px] font-bold tracking-[0.2em] text-champagne uppercase">Marketing & Promotions</span>
            <h1 class="font-serif text-3xl font-bold text-slate-900 mt-0.5">Discount Coupons</h1>
            <p class="text-xs text-slate-500">Create percentage or flat discount rules with minimum order thresholds and usage caps.</p>
        </div>

        <button type="button" @click="newCouponModal = true" class="px-5 py-2.5 bg-slate-900 text-white text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-slate-800 transition-colors flex items-center gap-2 shadow-sm self-start sm:self-auto">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Create New Promo Code</span>
        </button>
    </div>

    <!-- Coupons Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-6">Promo Code</th>
                        <th class="py-3.5 px-4">Discount Value</th>
                        <th class="py-3.5 px-4">Min Bag Spend</th>
                        <th class="py-3.5 px-4">Max Cap</th>
                        <th class="py-3.5 px-4">Conditions</th>
                        <th class="py-3.5 px-4 text-center">Redemptions</th>
                        <th class="py-3.5 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono text-[11px]">
                    <?php foreach ($coupons as $c): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="py-3.5 px-6 font-bold text-slate-900 text-sm">
                                <span class="bg-slate-100 border border-slate-300 px-2.5 py-1 rounded-md tracking-wider">
                                    <?= htmlspecialchars($c['code']) ?>
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-bold font-sans text-slate-800">
                                <?= $c['discount_type'] === 'PERCENTAGE' ? $c['discount_value'] . '% OFF' : STORE_CURRENCY_SYMBOL . number_format($c['discount_value']) . ' FLAT' ?>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700">
                                <?= STORE_CURRENCY_SYMBOL ?><?= number_format($c['min_order_value']) ?>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700">
                                <?= $c['max_discount_amount'] ? STORE_CURRENCY_SYMBOL . number_format($c['max_discount_amount']) : 'No Limit' ?>
                            </td>
                            <td class="py-3.5 px-4 font-sans text-slate-500">
                                <?= $c['is_first_order_only'] ? '<span class="text-amber-700 font-bold">1st Order Only</span>' : 'All Orders' ?>
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-slate-900">
                                <?= $c['used_count'] ?> / <?= $c['usage_limit_total'] ?? '∞' ?>
                            </td>
                            <td class="py-3.5 px-4 font-sans">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider <?= $c['is_active'] ? 'bg-emerald-50 text-emerald-800' : 'bg-slate-100 text-slate-600' ?>">
                                    <?= $c['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Coupon Modal -->
    <div x-show="newCouponModal" x-cloak class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 sm:p-8 space-y-4 border border-slate-200 shadow-2xl text-xs" @click.away="newCouponModal = false">
            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <h3 class="font-serif text-lg font-bold text-slate-900">Create Promotional Coupon</h3>
                <button type="button" @click="newCouponModal = false" class="text-slate-400 hover:text-slate-900">&times;</button>
            </div>

            <form action="/admin/coupons/create" method="POST" class="space-y-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Coupon Code (Uppercase) *</label>
                    <input type="text" name="code" required placeholder="e.g. FESTIVE25" 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-slate-900 font-mono text-xs uppercase">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Type *</label>
                        <select name="discount_type" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                            <option value="PERCENTAGE">Percentage (%)</option>
                            <option value="FIXED">Flat Fixed (₹)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Discount Value *</label>
                        <input type="number" step="0.01" name="discount_value" required placeholder="20" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Min Order Value (₹)</label>
                        <input type="number" name="min_order_value" value="999" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Max Discount Cap (₹)</label>
                        <input type="number" name="max_discount_amount" placeholder="1000" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono">
                    </div>
                </div>

                <div>
                    <label class="flex items-center gap-2 cursor-pointer font-semibold text-slate-800">
                        <input type="checkbox" name="is_first_order_only" value="1" class="rounded text-slate-900">
                        <span>Valid strictly on First Order Only</span>
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-200">
                    <button type="button" @click="newCouponModal = false" class="px-4 py-2 border rounded-xl font-bold uppercase tracking-wider text-slate-600 hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-6 py-2 bg-slate-900 text-white rounded-xl font-bold uppercase tracking-wider hover:bg-slate-800 transition-colors shadow-sm">Save Coupon</button>
                </div>
            </form>
        </div>
    </div>
</div>

