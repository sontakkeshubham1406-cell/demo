<?php
/**
 * Admin Multi-SKU Inventory Ledger & Stock Adjustment
 */
$inventory = \App\Database\Database::query("
    SELECT 
        inv.*,
        pv.sku,
        pv.barcode,
        p.name as product_name,
        p.slug as product_slug,
        s.name as size_name,
        c.name as color_name,
        c.hex_code as color_hex,
        (inv.stock_quantity - inv.reserved_quantity) as available_to_sell
    FROM inventory inv
    JOIN product_variants pv ON inv.variant_id = pv.id
    JOIN products p ON pv.product_id = p.id
    JOIN sizes s ON pv.size_id = s.id
    JOIN colors c ON pv.color_id = c.id
    ORDER BY available_to_sell ASC
");

$movements = \App\Database\Database::query("
    SELECT im.*, pv.sku, p.name as product_name, u.name as user_name
    FROM inventory_movements im
    JOIN product_variants pv ON im.variant_id = pv.id
    JOIN products p ON pv.product_id = p.id
    LEFT JOIN users u ON im.user_id = u.id
    ORDER BY im.created_at DESC
    LIMIT 15
");
?>
<div class="space-y-8" x-data="{ adjustModalOpen: false, selectedVariantId: null, selectedSku: '', currentStock: 0 }">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-200 gap-4">
        <div>
            <span class="text-[10px] font-bold tracking-[0.2em] text-champagne uppercase">Physical & Reserved Inventory</span>
            <h1 class="font-serif text-3xl font-bold text-slate-900 mt-0.5">SKU Stock Ledger & Auditing</h1>
            <p class="text-xs text-slate-500">Track on-hand stock, soft reservation locks, and immutable ledger movements.</p>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-700">All Apparel SKUs (<?= count($inventory) ?> Items)</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-6">SKU Code</th>
                        <th class="py-3.5 px-4">Garment</th>
                        <th class="py-3.5 px-4">Variant Attributes</th>
                        <th class="py-3.5 px-4 text-center">On-Hand Physical</th>
                        <th class="py-3.5 px-4 text-center">Checkout Reserved</th>
                        <th class="py-3.5 px-4 text-center">Available to Promise</th>
                        <th class="py-3.5 px-6 text-right">Adjustment</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($inventory as $inv): ?>
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-6 font-mono font-bold text-slate-900">
                                <?= htmlspecialchars($inv['sku']) ?>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-900">
                                <?= htmlspecialchars($inv['product_name']) ?>
                            </td>
                            <td class="py-3.5 px-4 flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full border border-slate-300" style="background-color: <?= $inv['color_hex'] ?>"></span>
                                <span><?= htmlspecialchars($inv['color_name']) ?></span>
                                <span class="font-bold text-slate-700 font-mono">(<?= htmlspecialchars($inv['size_name']) ?>)</span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-800">
                                <?= $inv['stock_quantity'] ?>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono text-amber-600 font-bold">
                                <?= $inv['reserved_quantity'] ?>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-lg font-mono font-bold text-[11px] border <?= $inv['available_to_sell'] <= $inv['safety_stock_threshold'] ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-emerald-50 text-emerald-800 border-emerald-200' ?>">
                                    <?= $inv['available_to_sell'] ?> Units
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                <button type="button" 
                                        @click="adjustModalOpen = true; selectedVariantId = <?= $inv['variant_id'] ?>; selectedSku = '<?= htmlspecialchars($inv['sku']) ?>'; currentStock = <?= $inv['stock_quantity'] ?>;"
                                        class="px-3 py-1 bg-slate-900 text-white rounded-md text-[11px] font-bold uppercase tracking-wider hover:bg-slate-800 transition-colors">
                                    Adjust Stock
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Immutable Audit Log of Inventory Movements -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-200">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">Immutable Inventory Movement Audit Trail</h3>
            <p class="text-xs text-slate-500">Every single stock decrement, reservation, and batch purchase receipt logged permanently.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-6">Timestamp</th>
                        <th class="py-3 px-4">SKU / Garment</th>
                        <th class="py-3 px-4">Event Type</th>
                        <th class="py-3 px-4 text-center">Delta Quantity</th>
                        <th class="py-3 px-4">Reference Document</th>
                        <th class="py-3 px-6">Notes / Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono text-[11px]">
                    <?php foreach ($movements as $m): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-6 text-slate-500"><?= date('d M Y, H:i', strtotime($m['created_at'])) ?></td>
                            <td class="py-3 px-4 font-bold text-slate-900"><?= htmlspecialchars($m['sku']) ?></td>
                            <td class="py-3 px-4 font-sans font-semibold">
                                <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold <?= $m['quantity'] > 0 ? 'bg-emerald-50 text-emerald-800' : 'bg-rose-50 text-rose-800' ?>">
                                    <?= htmlspecialchars($m['change_type']) ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center font-bold <?= $m['quantity'] > 0 ? 'text-emerald-700' : 'text-rose-700' ?>">
                                <?= $m['quantity'] > 0 ? '+' . $m['quantity'] : $m['quantity'] ?>
                            </td>
                            <td class="py-3 px-4 text-slate-700"><?= htmlspecialchars($m['reference_id'] ?? '—') ?></td>
                            <td class="py-3 px-6 text-slate-500 font-sans"><?= htmlspecialchars($m['remarks'] ?? 'System automatic mutation') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Stock Adjustment Modal -->
    <div x-show="adjustModalOpen" x-cloak class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 sm:p-8 space-y-4 border border-slate-200 shadow-2xl text-xs" @click.away="adjustModalOpen = false">
            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <div>
                    <span class="text-[10px] font-bold text-champagne uppercase tracking-wider">Inventory Mutation</span>
                    <h3 class="font-serif text-lg font-bold text-slate-900">Adjust SKU Stock</h3>
                </div>
                <button type="button" @click="adjustModalOpen = false" class="text-slate-400 hover:text-slate-900">&times;</button>
            </div>

            <form action="/admin/inventory/adjust" method="POST" class="space-y-4">
                <input type="hidden" name="variant_id" :value="selectedVariantId">

                <div class="bg-slate-50 p-3 rounded-lg border border-slate-200">
                    <p class="text-slate-500">Target SKU: <strong class="font-mono text-slate-900" x-text="selectedSku"></strong></p>
                    <p class="text-slate-500">Current Physical On-Hand: <strong class="font-mono text-slate-900" x-text="currentStock + ' units'"></strong></p>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Delta Quantity (+/- Units) *</label>
                    <input type="number" name="quantity_delta" required placeholder="e.g. 50 (to add) or -5 (to deduct)" 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-slate-900 font-mono text-xs">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Mandatory Reason Code *</label>
                    <select name="change_type" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-slate-900 text-xs">
                        <option value="PURCHASE_RECEIPT">PO Batch Receipt (+)</option>
                        <option value="RETURN_RESTOCK">Customer Return Restock (+)</option>
                        <option value="MANUAL_CORRECTION">Cycle Count Correction (+/-)</option>
                        <option value="DAMAGE_WRITE_OFF">Damage / Defect Write-Off (-)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Reference Doc (PO / Audit Ticket #)</label>
                    <input type="text" name="reference_id" placeholder="e.g. PO-2026-NOV-09" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Auditor Remarks</label>
                    <input type="text" name="remarks" placeholder="Notes for compliance ledger..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-200">
                    <button type="button" @click="adjustModalOpen = false" class="px-4 py-2 border rounded-xl font-bold uppercase tracking-wider text-slate-600 hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-6 py-2 bg-slate-900 text-white rounded-xl font-bold uppercase tracking-wider hover:bg-slate-800 transition-colors shadow-sm">Commit Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

