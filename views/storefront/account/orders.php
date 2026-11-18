<?php
/**
 * Customer Order History
 */
$currentUser = \App\Services\AuthService::getCurrentUser();
if (!$currentUser) {
    header("Location: /login");
    exit;
}

$orders = \App\Database\Database::query("
    SELECT o.*,
        (SELECT COUNT(id) FROM order_items WHERE order_id = o.id) as items_count,
        (SELECT tracking_number FROM shipments WHERE order_id = o.id) as tracking_number,
        (SELECT carrier_name FROM shipments WHERE order_id = o.id) as carrier_name
    FROM orders o
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
", [$currentUser['id']]);
?>
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-16">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-subtleBorder gap-4">
        <div>
            <span class="text-[10px] font-bold tracking-[0.25em] text-champagne uppercase">Wardrobe Ledger</span>
            <h1 class="font-serif text-3xl font-bold text-obsidian mt-1">My Orders & Purchases</h1>
        </div>
        <a href="/catalog" class="px-5 py-2.5 bg-obsidian text-white text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-slateDark transition-colors inline-flex items-center gap-2 self-start sm:self-auto">
            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
            <span>Shop New Drops</span>
        </a>
    </div>

    <?php if (empty($orders)): ?>
        <div class="bg-white border border-subtleBorder rounded-3xl p-12 text-center my-8 shadow-xs">
            <div class="w-16 h-16 mx-auto rounded-full bg-[#FAF9F6] border border-subtleBorder flex items-center justify-center text-mutedText mb-4">
                <i data-lucide="package" class="w-8 h-8"></i>
            </div>
            <h3 class="font-serif text-xl font-bold text-obsidian">No Orders Yet</h3>
            <p class="text-xs text-mutedText mt-1">When you place an order, its fulfillment timeline and invoice will appear here.</p>
        </div>
    <?php else: ?>
        <div class="mt-8 space-y-6">
            <?php foreach ($orders as $order): ?>
                <?php
                $statusColors = [
                    'PENDING_PAYMENT' => 'bg-amber-50 text-amber-800 border-amber-200',
                    'CONFIRMED' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                    'PROCESSING' => 'bg-blue-50 text-blue-800 border-blue-200',
                    'SHIPPED' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
                    'DELIVERED' => 'bg-purple-50 text-purple-800 border-purple-200',
                    'CANCELLED' => 'bg-rose-50 text-rose-800 border-rose-200',
                    'RETURNED' => 'bg-neutral-100 text-neutral-800 border-neutral-300'
                ];
                $badgeClass = $statusColors[$order['status']] ?? 'bg-neutral-100 text-neutral-800 border-neutral-300';
                ?>
                <div class="bg-white border border-subtleBorder rounded-2xl p-6 shadow-xs hover:shadow-md transition-shadow">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-subtleBorder/60 gap-3">
                        <div class="space-y-1">
                            <span class="font-mono text-xs font-bold text-obsidian"><?= htmlspecialchars($order['order_number']) ?></span>
                            <p class="text-[11px] text-mutedText">Placed on <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs px-3 py-1 rounded-full border font-bold uppercase tracking-wider <?= $badgeClass ?>">
                                <?= htmlspecialchars($order['status']) ?>
                            </span>
                            <span class="text-sm font-bold text-obsidian">
                                <?= STORE_CURRENCY_SYMBOL ?><?= number_format($order['total_amount'], 2) ?>
                            </span>
                        </div>
                    </div>

                    <div class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs">
                        <div class="text-mutedText">
                            <span><?= $order['items_count'] ?> item(s)</span>
                            <?php if (!empty($order['tracking_number'])): ?>
                                <span class="mx-2">•</span>
                                <span>Dispatched via <strong class="text-obsidian"><?= htmlspecialchars($order['carrier_name']) ?></strong> (AWB: <span class="font-mono"><?= htmlspecialchars($order['tracking_number']) ?></span>)</span>
                            <?php endif; ?>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="/admin/orders/invoice?id=<?= $order['id'] ?>" target="_blank" class="px-3.5 py-1.5 bg-[#FAF9F6] border border-subtleBorder text-obsidian rounded-lg hover:bg-neutral-100 font-semibold text-[11px]">
                                Invoice PDF
                            </a>
                            <a href="/account/orders/<?= $order['id'] ?>" class="px-4 py-1.5 bg-obsidian text-white rounded-lg hover:bg-slateDark font-semibold text-[11px] uppercase tracking-wider flex items-center gap-1">
                                <span>Order Details & Tracking</span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

