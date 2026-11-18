<?php
/**
 * Admin Executive Dashboard
 */
$totalRevenue = \App\Database\Database::fetchOne("SELECT COALESCE(SUM(total_amount), 0) as rev FROM orders WHERE payment_status = 'PAID'")['rev'];
$totalOrders = \App\Database\Database::fetchOne("SELECT COUNT(id) as c FROM orders")['c'];
$pendingFulfillment = \App\Database\Database::fetchOne("SELECT COUNT(id) as c FROM orders WHERE status IN ('CONFIRMED', 'PROCESSING')")['c'];
$lowStockCount = \App\Database\Database::fetchOne("SELECT COUNT(id) as c FROM inventory WHERE (stock_quantity - reserved_quantity) <= safety_stock_threshold")['c'];
$totalCustomers = \App\Database\Database::fetchOne("SELECT COUNT(id) as c FROM users WHERE role_id IS NULL")['c'];

$recentOrders = \App\Database\Database::query("
    SELECT o.*,
        (SELECT COUNT(id) FROM order_items WHERE order_id = o.id) as item_count
    FROM orders o 
    ORDER BY o.created_at DESC 
    LIMIT 6
");

$lowStockItems = \App\Database\Database::query("
    SELECT inv.*, pv.sku, p.name as product_name, s.name as size_name, c.name as color_name
    FROM inventory inv
    JOIN product_variants pv ON inv.variant_id = pv.id
    JOIN products p ON pv.product_id = p.id
    JOIN sizes s ON pv.size_id = s.id
    JOIN colors c ON pv.color_id = c.id
    WHERE (inv.stock_quantity - inv.reserved_quantity) <= inv.safety_stock_threshold
    LIMIT 5
");
?>
<div class="space-y-8">
    
    <!-- Page Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-200 gap-4">
        <div>
            <span class="text-[10px] font-bold tracking-[0.2em] text-champagne uppercase">Business Intelligence</span>
            <h1 class="font-serif text-3xl font-bold text-slate-900 mt-1">Executive Atelier Dashboard</h1>
            <p class="text-xs text-slate-500 mt-0.5">Real-time revenue, order fulfillment velocity, and inventory alerts.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="/admin/products/create" class="px-4 py-2.5 bg-slate-900 text-white text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-slate-800 transition-colors flex items-center gap-2 shadow-sm">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Add Apparel Variant</span>
            </a>
            <a href="/admin/inventory" class="px-4 py-2.5 bg-white border border-slate-200 text-slate-700 text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-slate-50 transition-colors flex items-center gap-2">
                <i data-lucide="boxes" class="w-4 h-4"></i>
                <span>Adjust Stock</span>
            </a>
        </div>
    </div>

    <!-- 5 KPI Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- KPI 1: Gross Revenue -->
        <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-xs space-y-2">
            <div class="flex items-center justify-between text-slate-500">
                <span class="text-[11px] font-bold uppercase tracking-wider">Net Revenue</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="indian-rupee" class="w-4 h-4"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-900 font-mono">
                <?= STORE_CURRENCY_SYMBOL ?><?= number_format($totalRevenue, 2) ?>
            </p>
            <span class="text-[10px] text-emerald-600 font-semibold flex items-center gap-1">
                <i data-lucide="trending-up" class="w-3 h-3"></i>
                <span>Razorpay Settled</span>
            </span>
        </div>

        <!-- KPI 2: Total Orders -->
        <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-xs space-y-2">
            <div class="flex items-center justify-between text-slate-500">
                <span class="text-[11px] font-bold uppercase tracking-wider">Total Orders</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-900 font-mono"><?= $totalOrders ?></p>
            <span class="text-[10px] text-slate-500">Lifetime Purchases</span>
        </div>

        <!-- KPI 3: Fulfillment Queue -->
        <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-xs space-y-2">
            <div class="flex items-center justify-between text-slate-500">
                <span class="text-[11px] font-bold uppercase tracking-wider">To Fulfill</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-amber-600 font-mono"><?= $pendingFulfillment ?></p>
            <span class="text-[10px] text-slate-500">Packing & Dispatch Pending</span>
        </div>

        <!-- KPI 4: Low Stock Alerts -->
        <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-xs space-y-2">
            <div class="flex items-center justify-between text-slate-500">
                <span class="text-[11px] font-bold uppercase tracking-wider">Low Stock SKUs</span>
                <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-rose-600 font-mono"><?= $lowStockCount ?></p>
            <span class="text-[10px] text-rose-500 font-semibold">Below Safety Threshold</span>
        </div>

        <!-- KPI 5: Customer Base -->
        <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-xs space-y-2">
            <div class="flex items-center justify-between text-slate-500">
                <span class="text-[11px] font-bold uppercase tracking-wider">Clients</span>
                <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-900 font-mono"><?= $totalCustomers ?></p>
            <span class="text-[10px] text-slate-500">Registered Accounts</span>
        </div>
    </div>

    <!-- Layout Grid: Recent Orders & Stock Urgency -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Recent Orders Table (2 cols) -->
        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
            <div class="p-6 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">Recent Customer Purchases</h3>
                    <p class="text-xs text-slate-500">Latest orders placed via storefront & Razorpay gateway</p>
                </div>
                <a href="/admin/orders" class="text-xs font-bold text-slate-900 hover:text-champagne flex items-center gap-1">
                    <span>View All Orders</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-6">Order ID</th>
                            <th class="py-3.5 px-4">Client</th>
                            <th class="py-3.5 px-4">Amount</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-6 text-right">Fulfillment</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($recentOrders as $ro): ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 px-6 font-mono font-bold text-slate-900">
                                    <a href="/admin/orders/view?id=<?= $ro['id'] ?>" class="hover:underline text-slate-900">
                                        <?= htmlspecialchars($ro['order_number']) ?>
                                    </a>
                                </td>
                                <td class="py-3.5 px-4">
                                    <p class="font-semibold text-slate-900"><?= htmlspecialchars($ro['customer_name']) ?></p>
                                    <p class="text-[11px] text-slate-400"><?= htmlspecialchars($ro['customer_email']) ?></p>
                                </td>
                                <td class="py-3.5 px-4 font-bold font-mono text-slate-900">
                                    <?= STORE_CURRENCY_SYMBOL ?><?= number_format($ro['total_amount'], 2) ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border <?= $ro['status'] === 'CONFIRMED' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-slate-100 text-slate-700 border-slate-200' ?>">
                                        <?= htmlspecialchars($ro['status']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-6 text-right">
                                    <a href="/admin/orders/view?id=<?= $ro['id'] ?>" class="px-3 py-1 bg-slate-900 text-white rounded-md text-[11px] font-bold uppercase tracking-wider hover:bg-slate-800 transition-colors inline-block">
                                        Inspect
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right: Low-Stock Inventory Alerts (1 col) -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden flex flex-col">
            <div class="p-6 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-rose-700 flex items-center gap-1.5">
                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                        <span>Stock Urgency</span>
                    </h3>
                    <p class="text-xs text-slate-500">Variants below threshold (<= 5)</p>
                </div>
                <a href="/admin/inventory" class="text-xs font-bold text-slate-900 hover:text-champagne">Ledger &rarr;</a>
            </div>

            <div class="p-6 flex-1 divide-y divide-slate-100 space-y-4">
                <?php if (empty($lowStockItems)): ?>
                    <p class="text-center text-xs text-slate-400 py-8">All apparel SKUs have healthy safety stock levels.</p>
                <?php else: ?>
                    <?php foreach ($lowStockItems as $lsi): ?>
                        <div class="pt-3 first:pt-0 flex items-center justify-between text-xs">
                            <div class="space-y-0.5">
                                <h4 class="font-bold text-slate-900"><?= htmlspecialchars($lsi['product_name']) ?></h4>
                                <p class="text-slate-500 text-[11px]">
                                    Size: <strong><?= htmlspecialchars($lsi['size_name']) ?></strong> | 
                                    Color: <strong><?= htmlspecialchars($lsi['color_name']) ?></strong>
                                </p>
                                <span class="font-mono text-[10px] text-slate-400"><?= htmlspecialchars($lsi['sku']) ?></span>
                            </div>
                            <div class="text-right">
                                <span class="px-2.5 py-1 bg-rose-50 text-rose-700 font-bold font-mono rounded-lg border border-rose-200">
                                    <?= $lsi['stock_quantity'] - $lsi['reserved_quantity'] ?> Left
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-200 text-center">
                <a href="/admin/inventory" class="text-xs font-bold text-slate-700 hover:text-slate-900 uppercase tracking-wider">
                    Restock Batch Purchase Order &rarr;
                </a>
            </div>
        </div>
    </div>
</div>

