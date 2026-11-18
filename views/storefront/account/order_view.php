<?php
/**
 * Customer Order Details & Tracking Stepper & Return Action
 */
$currentUser = \App\Services\AuthService::getCurrentUser();
if (!$currentUser) {
    header("Location: /login");
    exit;
}

$orderId = (int)($params['id'] ?? $_GET['id'] ?? 0);
$order = \App\Services\OrderService::getOrderDetails($orderId);

if (!$order || ($order['user_id'] != $currentUser['id'] && empty($currentUser['role_id']))) {
    echo "<div class='max-w-2xl mx-auto py-20 text-center text-rose-600'>Order not found or unauthorized.</div>";
    return;
}

$canCancel = in_array($order['status'], ['CONFIRMED', 'PROCESSING']);
$isDelivered = ($order['status'] === 'DELIVERED');
?>
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-16">
    <!-- Back Link -->
    <a href="/account/orders" class="inline-flex items-center gap-1 text-xs text-mutedText hover:text-obsidian mb-6 uppercase tracking-wider font-semibold">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        <span>Back to Order History</span>
    </a>

    <!-- Header & Status Card -->
    <div class="bg-white border border-subtleBorder rounded-3xl p-6 sm:p-8 shadow-xs space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-subtleBorder gap-4">
            <div>
                <span class="text-[10px] font-bold tracking-[0.25em] text-champagne uppercase">Order Overview</span>
                <h1 class="font-serif text-2xl sm:text-3xl font-bold text-obsidian mt-0.5">
                    Order #<?= htmlspecialchars($order['order_number']) ?>
                </h1>
                <p class="text-xs text-mutedText mt-1">Placed on <?= date('d F Y, h:i A', strtotime($order['created_at'])) ?></p>
            </div>
            
            <div class="flex items-center gap-3">
                <span class="px-3.5 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-50 text-emerald-800 border border-emerald-200">
                    <?= htmlspecialchars($order['status']) ?>
                </span>
                <a href="/admin/orders/invoice?id=<?= $order['id'] ?>" target="_blank" class="px-4 py-2 bg-[#FAF9F6] border border-subtleBorder rounded-lg text-xs font-semibold text-obsidian hover:bg-neutral-100 flex items-center gap-1.5">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Tax Invoice PDF</span>
                </a>
            </div>
        </div>

        <!-- Tracking Timeline Stepper -->
        <div class="bg-[#FAF9F6] p-6 sm:p-8 rounded-2xl border border-subtleBorder">
            <h3 class="text-xs font-bold uppercase tracking-wider text-obsidian text-center mb-6">Fulfillment Stepper</h3>
            <div class="flex items-center justify-between text-center relative max-w-2xl mx-auto">
                <div class="flex flex-col items-center z-10">
                    <span class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold mb-2">
                        <i data-lucide="check" class="w-4 h-4"></i>
                    </span>
                    <span class="text-xs font-bold text-obsidian">Order Placed</span>
                    <span class="text-[10px] text-mutedText"><?= date('d M', strtotime($order['created_at'])) ?></span>
                </div>
                <div class="flex flex-col items-center z-10">
                    <span class="w-8 h-8 rounded-full <?= in_array($order['status'], ['PROCESSING', 'SHIPPED', 'DELIVERED']) ? 'bg-emerald-600 text-white' : 'bg-neutral-200 text-mutedText' ?> flex items-center justify-center text-xs font-bold mb-2">
                        <?= in_array($order['status'], ['PROCESSING', 'SHIPPED', 'DELIVERED']) ? '<i data-lucide="check" class="w-4 h-4"></i>' : '2' ?>
                    </span>
                    <span class="text-xs font-bold text-obsidian">In Packing</span>
                    <span class="text-[10px] text-mutedText">Atelier QA</span>
                </div>
                <div class="flex flex-col items-center z-10">
                    <span class="w-8 h-8 rounded-full <?= in_array($order['status'], ['SHIPPED', 'DELIVERED']) ? 'bg-emerald-600 text-white' : 'bg-neutral-200 text-mutedText' ?> flex items-center justify-center text-xs font-bold mb-2">
                        <?= in_array($order['status'], ['SHIPPED', 'DELIVERED']) ? '<i data-lucide="check" class="w-4 h-4"></i>' : '3' ?>
                    </span>
                    <span class="text-xs font-bold text-obsidian">Dispatched</span>
                    <span class="text-[10px] text-mutedText"><?= !empty($order['shipment']['shipped_at']) ? date('d M', strtotime($order['shipment']['shipped_at'])) : 'Pending' ?></span>
                </div>
                <div class="flex flex-col items-center z-10">
                    <span class="w-8 h-8 rounded-full <?= $order['status'] === 'DELIVERED' ? 'bg-emerald-600 text-white' : 'bg-neutral-200 text-mutedText' ?> flex items-center justify-center text-xs font-bold mb-2">
                        <?= $order['status'] === 'DELIVERED' ? '<i data-lucide="check" class="w-4 h-4"></i>' : '4' ?>
                    </span>
                    <span class="text-xs font-bold text-obsidian">Delivered</span>
                    <span class="text-[10px] text-mutedText"><?= !empty($order['shipment']['delivered_at']) ? date('d M', strtotime($order['shipment']['delivered_at'])) : 'Estimated' ?></span>
                </div>
            </div>

            <?php if (!empty($order['shipment']['tracking_number'])): ?>
                <div class="mt-8 text-center pt-6 border-t border-subtleBorder text-xs">
                    <p class="text-mutedText">
                        Courier Partner: <strong class="text-obsidian"><?= htmlspecialchars($order['shipment']['carrier_name']) ?></strong> | 
                        AWB Tracking No: <strong class="font-mono text-obsidian"><?= htmlspecialchars($order['shipment']['tracking_number']) ?></strong>
                    </p>
                    <?php if (!empty($order['shipment']['tracking_url'])): ?>
                        <a href="<?= htmlspecialchars($order['shipment']['tracking_url']) ?>" target="_blank" class="mt-2 inline-flex items-center gap-1.5 text-champagne font-bold underline">
                            <span>Open Live Courier Tracking Portal</span>
                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Garments Table -->
        <div class="space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-[0.2em] text-obsidian">Garments in this Order</h3>
            <div class="divide-y divide-subtleBorder/60 border border-subtleBorder rounded-2xl overflow-hidden">
                <?php foreach ($order['items'] as $item): ?>
                    <div class="p-4 flex items-center justify-between text-xs bg-white">
                        <div class="flex items-center gap-4">
                            <img src="<?= htmlspecialchars($item['item_image']) ?>" class="w-16 h-20 object-cover rounded-lg bg-neutral-100 border border-subtleBorder" alt="">
                            <div class="space-y-0.5">
                                <h4 class="font-semibold text-obsidian"><?= htmlspecialchars($item['product_name']) ?></h4>
                                <p class="text-mutedText">
                                    Size: <strong class="text-obsidian"><?= htmlspecialchars($item['size_name']) ?></strong> | 
                                    Color: <strong class="text-obsidian"><?= htmlspecialchars($item['color_name']) ?></strong> | 
                                    SKU: <span class="font-mono"><?= htmlspecialchars($item['sku']) ?></span>
                                </p>
                                <p class="text-[11px] text-mutedText">Quantity: <?= $item['quantity'] ?> × <?= STORE_CURRENCY_SYMBOL ?><?= number_format($item['unit_price'], 2) ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-bold text-obsidian">
                                <?= STORE_CURRENCY_SYMBOL ?><?= number_format($item['total_price'], 2) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Delivery & Payment Split -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4 text-xs">
            <div class="bg-[#FAF9F6] p-5 rounded-2xl border border-subtleBorder space-y-1">
                <h4 class="font-bold uppercase tracking-wider text-obsidian text-[11px] mb-2">Delivery Destination</h4>
                <p class="font-semibold text-obsidian"><?= htmlspecialchars($order['shipping_address']['recipient_name'] ?? '') ?></p>
                <p class="text-mutedText"><?= htmlspecialchars($order['shipping_address']['address_line1'] ?? '') ?></p>
                <?php if (!empty($order['shipping_address']['address_line2'])): ?>
                    <p class="text-mutedText"><?= htmlspecialchars($order['shipping_address']['address_line2']) ?></p>
                <?php endif; ?>
                <p class="text-mutedText"><?= htmlspecialchars($order['shipping_address']['city'] ?? '') ?>, <?= htmlspecialchars($order['shipping_address']['state'] ?? '') ?> - <?= htmlspecialchars($order['shipping_address']['postal_code'] ?? '') ?></p>
                <p class="text-mutedText">Phone: <?= htmlspecialchars($order['shipping_address']['phone'] ?? '') ?></p>
            </div>

            <div class="bg-[#FAF9F6] p-5 rounded-2xl border border-subtleBorder space-y-2">
                <h4 class="font-bold uppercase tracking-wider text-obsidian text-[11px] mb-2">Payment Details</h4>
                <div class="flex justify-between text-mutedText">
                    <span>Subtotal:</span>
                    <span><?= STORE_CURRENCY_SYMBOL ?><?= number_format($order['subtotal'], 2) ?></span>
                </div>
                <?php if ($order['discount_amount'] > 0): ?>
                    <div class="flex justify-between text-emerald-700 font-semibold">
                        <span>Discount:</span>
                        <span>- <?= STORE_CURRENCY_SYMBOL ?><?= number_format($order['discount_amount'], 2) ?></span>
                    </div>
                <?php endif; ?>
                <div class="flex justify-between text-mutedText">
                    <span>Shipping:</span>
                    <span><?= $order['shipping_fee'] == 0 ? 'FREE' : STORE_CURRENCY_SYMBOL . number_format($order['shipping_fee'], 2) ?></span>
                </div>
                <div class="flex justify-between font-bold text-obsidian pt-2 border-t border-subtleBorder">
                    <span>Total Paid:</span>
                    <span class="text-sm"><?= STORE_CURRENCY_SYMBOL ?><?= number_format($order['total_amount'], 2) ?></span>
                </div>
            </div>
        </div>

        <!-- Cancellation & Return Self-Service Trigger -->
        <div class="pt-6 border-t border-subtleBorder flex flex-wrap items-center justify-between gap-4">
            <?php if ($canCancel): ?>
                <form action="/account/orders/cancel" method="POST" onsubmit="return confirm('Are you sure you want to cancel this order? An automatic refund will be triggered back to your source payment method.')">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <button type="submit" class="px-5 py-2.5 bg-rose-50 text-rose-700 border border-rose-200 rounded-xl text-xs font-bold uppercase tracking-wider hover:bg-rose-100 transition-colors">
                        Cancel Order & Request Instant Refund
                    </button>
                </form>
            <?php endif; ?>

            <?php if ($isDelivered): ?>
                <!-- Return / Replacement Request Button -->
                <button type="button" onclick="document.getElementById('returnModal').classList.remove('hidden')" 
                        class="px-5 py-2.5 bg-obsidian text-white rounded-xl text-xs font-bold uppercase tracking-wider hover:bg-slateDark transition-colors">
                    Request 7-Day Return / Size Exchange
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Return Request Modal -->
<div id="returnModal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 sm:p-8 space-y-4 border border-subtleBorder text-xs shadow-2xl">
        <div class="flex items-center justify-between border-b border-subtleBorder pb-3">
            <h3 class="font-serif text-lg font-bold text-obsidian">Initiate 7-Day Return or Exchange</h3>
            <button type="button" onclick="document.getElementById('returnModal').classList.add('hidden')" class="text-mutedText hover:text-obsidian">&times;</button>
        </div>
        <form action="/account/orders/return" method="POST" class="space-y-4">
            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
            <div>
                <label class="block font-semibold text-obsidian mb-1">Select Garment to Return</label>
                <select name="order_item_id" class="w-full px-3 py-2 bg-[#FAF9F6] border border-subtleBorder rounded-lg">
                    <?php foreach ($order['items'] as $item): ?>
                        <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['product_name']) ?> (<?= htmlspecialchars($item['size_name']) ?> / <?= htmlspecialchars($item['color_name']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block font-semibold text-obsidian mb-1">Resolution Type</label>
                <select name="type" class="w-full px-3 py-2 bg-[#FAF9F6] border border-subtleBorder rounded-lg">
                    <option value="RETURN">Return for Full Razorpay Refund</option>
                    <option value="REPLACEMENT">Exchange for Different Size</option>
                </select>
            </div>
            <div>
                <label class="block font-semibold text-obsidian mb-1">Primary Reason</label>
                <select name="reason" class="w-full px-3 py-2 bg-[#FAF9F6] border border-subtleBorder rounded-lg">
                    <option value="SIZE_TOO_SMALL">Size too small</option>
                    <option value="SIZE_TOO_LARGE">Size too large</option>
                    <option value="DISLIKED_FABRIC">Fabric texture/color differed from photos</option>
                    <option value="DEFECTIVE_STITCHING">Defective stitching or manufacturing fault</option>
                </select>
            </div>
            <div>
                <label class="block font-semibold text-obsidian mb-1">Customer Remarks</label>
                <textarea name="customer_notes" rows="3" placeholder="Please describe any specifics for warehouse inspection..." class="w-full px-3 py-2 bg-[#FAF9F6] border border-subtleBorder rounded-lg"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('returnModal').classList.add('hidden')" class="px-4 py-2 border rounded-lg text-mutedText">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-obsidian text-white rounded-lg font-bold uppercase tracking-wider">Submit Return Request</button>
            </div>
        </form>
    </div>
</div>

