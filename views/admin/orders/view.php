<?php
/**
 * Admin Detailed Order Fulfillment Inspector
 */
$orderId = (int)($_GET['id'] ?? 0);
$order = \App\Services\OrderService::getOrderDetails($orderId);

if (!$order) {
    echo "<div class='p-12 text-center text-rose-600'>Order not found.</div>";
    return;
}
?>
<div class="max-w-5xl mx-auto space-y-8">
    
    <!-- Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-200 gap-4">
        <div>
            <a href="/admin/orders" class="inline-flex items-center gap-1 text-xs text-slate-500 hover:text-slate-900 mb-2 font-semibold">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to Orders Queue</span>
            </a>
            <h1 class="font-serif text-3xl font-bold text-slate-900">
                Order #<?= htmlspecialchars($order['order_number']) ?>
            </h1>
            <p class="text-xs text-slate-500">Placed on <?= date('d F Y, H:i:s', strtotime($order['created_at'])) ?></p>
        </div>

        <div class="flex items-center gap-3">
            <a href="/admin/orders/invoice?id=<?= $order['id'] ?>" target="_blank" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-700 hover:bg-slate-50 flex items-center gap-2 shadow-xs">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Tax Invoice PDF</span>
            </a>
            <span class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider bg-slate-900 text-white shadow-xs">
                <?= htmlspecialchars($order['status']) ?>
            </span>
        </div>
    </div>

    <!-- Fulfillment Action Control Card -->
    <div class="bg-white border-2 border-slate-900 p-6 sm:p-8 rounded-2xl shadow-xs space-y-4">
        <h3 class="text-xs font-bold uppercase tracking-[0.2em] text-slate-900">Fulfillment Pipeline Controls</h3>
        
        <div class="flex flex-wrap items-center gap-4 pt-2">
            <?php if ($order['status'] === 'CONFIRMED'): ?>
                <form action="/admin/orders/status" method="POST">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <input type="hidden" name="status" value="PROCESSING">
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-colors flex items-center gap-2">
                        <i data-lucide="package" class="w-4 h-4"></i>
                        <span>Start Packing in Warehouse (Mark PROCESSING)</span>
                    </button>
                </form>
            <?php endif; ?>

            <?php if ($order['status'] === 'PROCESSING'): ?>
                <!-- Dispatch Form -->
                <form action="/admin/orders/status" method="POST" class="flex flex-wrap items-center gap-3">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <input type="hidden" name="status" value="SHIPPED">
                    
                    <select name="carrier_name" required class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-semibold">
                        <option value="Blue Dart">Blue Dart Express</option>
                        <option value="Delhivery">Delhivery Air</option>
                        <option value="Shiprocket">Shiprocket Prime</option>
                        <option value="DTDC">DTDC Premium</option>
                    </select>

                    <input type="text" name="tracking_number" required placeholder="Enter AWB / Tracking #" 
                           class="px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-mono">

                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-colors flex items-center gap-2">
                        <i data-lucide="truck" class="w-4 h-4"></i>
                        <span>Dispatch & Mark SHIPPED</span>
                    </button>
                </form>
            <?php endif; ?>

            <?php if ($order['status'] === 'SHIPPED'): ?>
                <form action="/admin/orders/status" method="POST">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <input type="hidden" name="status" value="DELIVERED">
                    <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-colors flex items-center gap-2">
                        <i data-lucide="check-check" class="w-4 h-4"></i>
                        <span>Confirm Doorstep Delivery (Mark DELIVERED)</span>
                    </button>
                </form>
            <?php endif; ?>

            <?php if (in_array($order['status'], ['CONFIRMED', 'PROCESSING'])): ?>
                <form action="/admin/orders/status" method="POST" onsubmit="return confirm('Cancel this order? This will restock inventory and execute an automated Razorpay refund.')">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <input type="hidden" name="status" value="CANCELLED">
                    <button type="submit" class="px-5 py-2 bg-rose-50 text-rose-700 border border-rose-200 text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-rose-100 transition-colors">
                        Cancel & Issue Razorpay Refund
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Garments Ordered -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xs space-y-4">
        <h3 class="text-xs font-bold uppercase tracking-[0.2em] text-slate-900 border-b border-slate-200 pb-3">Line Items Snapshot</h3>

        <div class="divide-y divide-slate-100">
            <?php foreach ($order['items'] as $item): ?>
                <div class="py-4 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-4">
                        <img src="<?= htmlspecialchars($item['item_image']) ?>" class="w-14 h-18 object-cover rounded-lg bg-slate-100 border border-slate-200" alt="">
                        <div>
                            <h4 class="font-bold text-slate-900"><?= htmlspecialchars($item['product_name']) ?></h4>
                            <p class="text-slate-500 text-[11px]">
                                Size: <strong><?= htmlspecialchars($item['size_name']) ?></strong> | 
                                Color: <strong><?= htmlspecialchars($item['color_name']) ?></strong> | 
                                SKU: <span class="font-mono"><?= htmlspecialchars($item['sku']) ?></span>
                            </p>
                            <p class="text-[11px] text-slate-400">Qty: <?= $item['quantity'] ?> × <?= STORE_CURRENCY_SYMBOL ?><?= number_format($item['unit_price'], 2) ?></p>
                        </div>
                    </div>
                    <span class="font-mono font-bold text-slate-900 text-sm">
                        <?= STORE_CURRENCY_SYMBOL ?><?= number_format($item['total_price'], 2) ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 2-Column Details: Shipping Destination & Gateway Verification -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">
        <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-xs space-y-2">
            <h4 class="font-bold uppercase tracking-wider text-slate-900 text-[11px] border-b border-slate-200 pb-2">Customer & Shipping Information</h4>
            <p class="font-bold text-slate-900 text-sm"><?= htmlspecialchars($order['customer_name']) ?></p>
            <p class="text-slate-500">Email: <?= htmlspecialchars($order['customer_email']) ?></p>
            <p class="text-slate-500">Phone: <?= htmlspecialchars($order['customer_phone']) ?></p>
            <div class="pt-2 text-slate-600 leading-relaxed border-t border-slate-100">
                <p><?= htmlspecialchars($order['shipping_address']['address_line1'] ?? '') ?></p>
                <?php if (!empty($order['shipping_address']['address_line2'])): ?>
                    <p><?= htmlspecialchars($order['shipping_address']['address_line2']) ?></p>
                <?php endif; ?>
                <p><?= htmlspecialchars($order['shipping_address']['city'] ?? '') ?>, <?= htmlspecialchars($order['shipping_address']['state'] ?? '') ?> - <?= htmlspecialchars($order['shipping_address']['postal_code'] ?? '') ?></p>
            </div>
        </div>

        <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-xs space-y-2">
            <h4 class="font-bold uppercase tracking-wider text-slate-900 text-[11px] border-b border-slate-200 pb-2">Razorpay Payment Verification</h4>
            <div class="flex justify-between text-slate-500">
                <span>Gateway Status:</span>
                <span class="font-bold text-emerald-600 font-mono"><?= htmlspecialchars($order['payment']['status'] ?? 'CAPTURED') ?></span>
            </div>
            <div class="flex justify-between text-slate-500">
                <span>Razorpay Order ID:</span>
                <span class="font-mono text-slate-900"><?= htmlspecialchars($order['payment']['gateway_order_id'] ?? 'N/A') ?></span>
            </div>
            <div class="flex justify-between text-slate-500">
                <span>Payment Reference:</span>
                <span class="font-mono text-slate-900"><?= htmlspecialchars($order['payment']['gateway_payment_id'] ?? 'N/A') ?></span>
            </div>
            <div class="flex justify-between font-bold text-slate-900 pt-3 border-t border-slate-200">
                <span>Total Settled:</span>
                <span class="text-base font-mono"><?= STORE_CURRENCY_SYMBOL ?><?= number_format($order['total_amount'], 2) ?></span>
            </div>
        </div>
    </div>
</div>

