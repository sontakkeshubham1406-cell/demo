<?php
/**
 * Admin Orders & Fulfillment List
 */
$selectedStatus = $_GET['status'] ?? 'ALL';
$where = [];
$params = [];

if ($selectedStatus !== 'ALL') {
    $where[] = "o.status = ?";
    $params[] = $selectedStatus;
}

$whereSql = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";

$orders = \App\Database\Database::query("
    SELECT o.*,
        (SELECT COUNT(id) FROM order_items WHERE order_id = o.id) as items_count,
        (SELECT carrier_name FROM shipments WHERE order_id = o.id) as carrier_name,
        (SELECT tracking_number FROM shipments WHERE order_id = o.id) as tracking_number
    FROM orders o
    {$whereSql}
    ORDER BY o.created_at DESC
", $params);
?>
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-200 gap-4">
        <div>
            <span class="text-[10px] font-bold tracking-[0.2em] text-champagne uppercase">Fulfillment Logistics</span>
            <h1 class="font-serif text-3xl font-bold text-slate-900 mt-0.5">Customer Orders</h1>
            <p class="text-xs text-slate-500">Inspect payments, process warehouse packing, and assign AWB shipping tracking.</p>
        </div>
    </div>

    <!-- Filter Tabs by Status -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 border-b border-slate-200 text-xs font-semibold">
        <?php 
        $statuses = [
            'ALL' => 'All Orders',
            'CONFIRMED' => 'Confirmed (Ready to Pack)',
            'PROCESSING' => 'In Packing',
            'SHIPPED' => 'Dispatched',
            'DELIVERED' => 'Delivered',
            'CANCELLED' => 'Cancelled'
        ];
        foreach ($statuses as $k => $label): ?>
            <a href="/admin/orders?status=<?= $k ?>" 
               class="px-4 py-2 rounded-xl whitespace-nowrap transition-colors <?= $selectedStatus === $k ? 'bg-slate-900 text-white font-bold' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' ?>">
                <?= $label ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Orders Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-6">Order ID</th>
                        <th class="py-3.5 px-4">Date</th>
                        <th class="py-3.5 px-4">Customer</th>
                        <th class="py-3.5 px-4">Amount</th>
                        <th class="py-3.5 px-4">Payment</th>
                        <th class="py-3.5 px-4">Fulfillment Status</th>
                        <th class="py-3.5 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                No orders matching status "<?= htmlspecialchars($selectedStatus) ?>".
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $o): ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 px-6 font-mono font-bold text-slate-900">
                                    <a href="/admin/orders/view?id=<?= $o['id'] ?>" class="hover:underline">
                                        <?= htmlspecialchars($o['order_number']) ?>
                                    </a>
                                </td>
                                <td class="py-3.5 px-4 text-slate-500">
                                    <?= date('d M Y, H:i', strtotime($o['created_at'])) ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <p class="font-bold text-slate-900"><?= htmlspecialchars($o['customer_name']) ?></p>
                                    <p class="text-[11px] text-slate-400"><?= htmlspecialchars($o['customer_phone']) ?></p>
                                </td>
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                    <?= STORE_CURRENCY_SYMBOL ?><?= number_format($o['total_amount'], 2) ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border <?= $o['payment_status'] === 'PAID' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-rose-50 text-rose-800 border-rose-200' ?>">
                                        <?= htmlspecialchars($o['payment_status']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-800 border border-slate-200">
                                        <?= htmlspecialchars($o['status']) ?>
                                    </span>
                                    <?php if (!empty($o['tracking_number'])): ?>
                                        <p class="text-[10px] text-slate-400 font-mono mt-0.5"><?= htmlspecialchars($o['carrier_name']) ?>: <?= htmlspecialchars($o['tracking_number']) ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-6 text-right space-x-2">
                                    <a href="/admin/orders/invoice?id=<?= $o['id'] ?>" target="_blank" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[11px] font-semibold" title="Invoice">
                                        Invoice
                                    </a>
                                    <a href="/admin/orders/view?id=<?= $o['id'] ?>" class="px-3 py-1 bg-slate-900 text-white rounded text-[11px] font-bold uppercase tracking-wider hover:bg-slate-800 transition-colors inline-block">
                                        Inspect & Fulfill
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

