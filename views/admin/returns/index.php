<?php
/**
 * Admin Returns, Exchanges & Refund Processing
 */
$returns = \App\Database\Database::query("
    SELECT r.*,
        o.order_number,
        o.customer_name,
        o.customer_email,
        oi.product_name,
        oi.sku,
        oi.size_name,
        oi.color_name,
        oi.unit_price,
        oi.quantity,
        oi.variant_id
    FROM returns r
    JOIN orders o ON r.order_id = o.id
    JOIN order_items oi ON r.order_item_id = oi.id
    ORDER BY r.created_at DESC
");
?>
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-200 gap-4">
        <div>
            <span class="text-[10px] font-bold tracking-[0.2em] text-champagne uppercase">Reverse Logistics</span>
            <h1 class="font-serif text-3xl font-bold text-slate-900 mt-0.5">Returns, Replacements & Refunds</h1>
            <p class="text-xs text-slate-500">Triage 7-day customer return requests, approve doorstep pickups, and disburse Razorpay refunds.</p>
        </div>
    </div>

    <!-- Returns Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-6">Ticket / Order</th>
                        <th class="py-3.5 px-4">Client</th>
                        <th class="py-3.5 px-4">Garment</th>
                        <th class="py-3.5 px-4">Resolution</th>
                        <th class="py-3.5 px-4">Reason / Remarks</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($returns)): ?>
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                No pending returns or replacement tickets in queue.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($returns as $r): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="py-3.5 px-6 font-mono font-bold text-slate-900">
                                    <p>RET-#<?= $r['id'] ?></p>
                                    <span class="text-[10px] text-slate-400 font-normal">Order: <?= htmlspecialchars($r['order_number']) ?></span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <p class="font-bold text-slate-900"><?= htmlspecialchars($r['customer_name']) ?></p>
                                    <p class="text-[11px] text-slate-400"><?= htmlspecialchars($r['customer_email']) ?></p>
                                </td>
                                <td class="py-3.5 px-4">
                                    <p class="font-semibold text-slate-900"><?= htmlspecialchars($r['product_name']) ?></p>
                                    <p class="text-[11px] text-slate-500"><?= htmlspecialchars($r['size_name']) ?> / <?= htmlspecialchars($r['color_name']) ?> (<?= STORE_CURRENCY_SYMBOL ?><?= number_format($r['unit_price'], 2) ?>)</p>
                                </td>
                                <td class="py-3.5 px-4 font-bold">
                                    <span class="px-2 py-0.5 rounded text-[10px] uppercase <?= $r['type'] === 'RETURN' ? 'bg-amber-50 text-amber-800' : 'bg-blue-50 text-blue-800' ?>">
                                        <?= htmlspecialchars($r['type']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 max-w-xs">
                                    <p class="font-bold text-slate-800"><?= htmlspecialchars($r['reason']) ?></p>
                                    <?php if (!empty($r['customer_notes'])): ?>
                                        <p class="text-[11px] text-slate-400 truncate"><?= htmlspecialchars($r['customer_notes']) ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider <?= $r['status'] === 'REFUNDED' ? 'bg-emerald-50 text-emerald-800' : 'bg-amber-50 text-amber-800' ?>">
                                        <?= htmlspecialchars($r['status']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-6 text-right space-x-2">
                                    <?php if ($r['status'] === 'REQUESTED'): ?>
                                        <form action="/admin/returns/action" method="POST" class="inline" onsubmit="return confirm('Approve this return and trigger server-to-server Razorpay refund?')">
                                            <input type="hidden" name="return_id" value="<?= $r['id'] ?>">
                                            <input type="hidden" name="action" value="APPROVE_AND_REFUND">
                                            <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[11px] font-bold uppercase tracking-wider transition-colors">
                                                Approve & Refund
                                            </button>
                                        </form>
                                        <form action="/admin/returns/action" method="POST" class="inline" onsubmit="return confirm('Reject this return ticket?')">
                                            <input type="hidden" name="return_id" value="<?= $r['id'] ?>">
                                            <input type="hidden" name="action" value="REJECT">
                                            <button type="submit" class="px-3 py-1 bg-rose-50 text-rose-700 hover:bg-rose-100 rounded text-[11px] font-bold uppercase tracking-wider transition-colors">
                                                Reject
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400 font-semibold">Processed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

