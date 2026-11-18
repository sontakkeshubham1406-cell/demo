<?php
/**
 * Order Confirmation & Success Receipt
 */
$orderId = (int)($_GET['order_id'] ?? 0);
$order = \App\Services\OrderService::getOrderDetails($orderId);

if (!$order) {
    header("Location: /");
    exit;
}
?>
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-20">
    <div class="bg-white border border-subtleBorder rounded-3xl p-8 sm:p-12 shadow-sm space-y-8">
        
        <!-- Header Success Icon -->
        <div class="text-center space-y-3">
            <div class="w-16 h-16 mx-auto rounded-full bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600">
                <i data-lucide="check" class="w-8 h-8"></i>
            </div>
            <span class="text-[10px] font-bold tracking-[0.25em] text-champagne uppercase">Payment Verified via Razorpay</span>
            <h1 class="font-serif text-3xl sm:text-4xl font-bold text-obsidian">Thank You For Your Order</h1>
            <p class="text-xs text-mutedText max-w-md mx-auto">
                Your order <strong class="text-obsidian font-mono"><?= htmlspecialchars($order['order_number']) ?></strong> has been confirmed. A receipt and GST tax invoice have been dispatched to <strong class="text-obsidian"><?= htmlspecialchars($order['customer_email']) ?></strong>.
            </p>
        </div>

        <!-- Order Stepper -->
        <div class="bg-[#FAF9F6] p-6 rounded-2xl border border-subtleBorder">
            <h3 class="text-xs font-bold uppercase tracking-wider text-obsidian text-center mb-6">Fulfillment Lifecycle</h3>
            <div class="flex items-center justify-between text-center relative max-w-2xl mx-auto">
                <div class="flex flex-col items-center z-10">
                    <span class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold mb-2">1</span>
                    <span class="text-xs font-bold text-obsidian">Placed</span>
                    <span class="text-[10px] text-mutedText">Confirmed</span>
                </div>
                <div class="flex flex-col items-center z-10">
                    <span class="w-8 h-8 rounded-full <?= in_array($order['status'], ['PROCESSING', 'SHIPPED', 'DELIVERED']) ? 'bg-emerald-600 text-white' : 'bg-neutral-200 text-mutedText' ?> flex items-center justify-center text-xs font-bold mb-2">2</span>
                    <span class="text-xs font-bold text-obsidian">Packing</span>
                    <span class="text-[10px] text-mutedText">Atelier QA</span>
                </div>
                <div class="flex flex-col items-center z-10">
                    <span class="w-8 h-8 rounded-full <?= in_array($order['status'], ['SHIPPED', 'DELIVERED']) ? 'bg-emerald-600 text-white' : 'bg-neutral-200 text-mutedText' ?> flex items-center justify-center text-xs font-bold mb-2">3</span>
                    <span class="text-xs font-bold text-obsidian">Dispatched</span>
                    <span class="text-[10px] text-mutedText">Air Express</span>
                </div>
                <div class="flex flex-col items-center z-10">
                    <span class="w-8 h-8 rounded-full <?= $order['status'] === 'DELIVERED' ? 'bg-emerald-600 text-white' : 'bg-neutral-200 text-mutedText' ?> flex items-center justify-center text-xs font-bold mb-2">4</span>
                    <span class="text-xs font-bold text-obsidian">Delivered</span>
                    <span class="text-[10px] text-mutedText">Doorstep</span>
                </div>
            </div>
        </div>

        <!-- Items Summary Grid -->
        <div class="border-t border-subtleBorder pt-6">
            <h3 class="text-xs font-bold uppercase tracking-[0.2em] text-obsidian mb-4">Garments Ordered</h3>
            <div class="divide-y divide-subtleBorder/60">
                <?php foreach ($order['items'] as $item): ?>
                    <div class="py-3 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-3">
                            <img src="<?= htmlspecialchars($item['item_image']) ?>" class="w-12 h-16 object-cover rounded bg-neutral-100" alt="">
                            <div>
                                <h4 class="font-semibold text-obsidian"><?= htmlspecialchars($item['product_name']) ?></h4>
                                <p class="text-mutedText text-[11px]">Size: <?= htmlspecialchars($item['size_name']) ?> | Color: <?= htmlspecialchars($item['color_name']) ?> × <?= $item['quantity'] ?></p>
                                <span class="font-mono text-[10px] text-mutedText"><?= htmlspecialchars($item['sku']) ?></span>
                            </div>
                        </div>
                        <span class="font-bold text-obsidian">
                            <?= STORE_CURRENCY_SYMBOL ?><?= number_format($item['total_price'], 2) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Details Grid (Shipping & Financials) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-6 border-t border-subtleBorder text-xs">
            <div class="bg-[#FAF9F6] p-5 rounded-2xl border border-subtleBorder space-y-1.5">
                <h4 class="font-bold uppercase tracking-wider text-obsidian text-[11px] mb-2">Shipping Destination</h4>
                <p class="font-semibold text-obsidian"><?= htmlspecialchars($order['shipping_address']['recipient_name'] ?? '') ?></p>
                <p class="text-mutedText"><?= htmlspecialchars($order['shipping_address']['address_line1'] ?? '') ?></p>
                <?php if (!empty($order['shipping_address']['address_line2'])): ?>
                    <p class="text-mutedText"><?= htmlspecialchars($order['shipping_address']['address_line2']) ?></p>
                <?php endif; ?>
                <p class="text-mutedText"><?= htmlspecialchars($order['shipping_address']['city'] ?? '') ?>, <?= htmlspecialchars($order['shipping_address']['state'] ?? '') ?> - <?= htmlspecialchars($order['shipping_address']['postal_code'] ?? '') ?></p>
                <p class="text-mutedText">Phone: <?= htmlspecialchars($order['shipping_address']['phone'] ?? '') ?></p>
            </div>

            <div class="bg-[#FAF9F6] p-5 rounded-2xl border border-subtleBorder space-y-2">
                <h4 class="font-bold uppercase tracking-wider text-obsidian text-[11px] mb-2">Payment Verification</h4>
                <div class="flex justify-between text-mutedText">
                    <span>Payment Gateway:</span>
                    <span class="font-semibold text-obsidian"><?= htmlspecialchars($order['payment']['gateway'] ?? 'RAZORPAY') ?></span>
                </div>
                <div class="flex justify-between text-mutedText">
                    <span>Gateway Order ID:</span>
                    <span class="font-mono text-[11px]"><?= htmlspecialchars($order['payment']['gateway_order_id'] ?? 'N/A') ?></span>
                </div>
                <div class="flex justify-between text-mutedText">
                    <span>Payment Reference:</span>
                    <span class="font-mono text-[11px]"><?= htmlspecialchars($order['payment']['gateway_payment_id'] ?? 'VERIFIED') ?></span>
                </div>
                <div class="flex justify-between font-bold text-obsidian pt-2 border-t border-subtleBorder">
                    <span>Amount Paid:</span>
                    <span class="text-sm"><?= STORE_CURRENCY_SYMBOL ?><?= number_format($order['total_amount'], 2) ?></span>
                </div>
            </div>
        </div>

        <!-- Action Links -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
            <a href="/admin/orders/invoice?id=<?= $order['id'] ?>" target="_blank" class="w-full sm:w-auto px-6 py-3 bg-white border border-subtleBorder text-obsidian text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-neutral-50 transition-colors flex items-center justify-center gap-2">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Print GST Tax Invoice</span>
            </a>
            <a href="/account/orders/<?= $order['id'] ?>" class="w-full sm:w-auto px-6 py-3 bg-neutral-100 text-obsidian text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-neutral-200 transition-colors flex items-center justify-center gap-2">
                <i data-lucide="truck" class="w-4 h-4"></i>
                <span>Track Live Order</span>
            </a>
            <a href="/catalog" class="w-full sm:w-auto px-6 py-3 bg-obsidian text-white text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-slateDark transition-colors flex items-center justify-center gap-2 shadow-md">
                <span>Continue Shopping</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
    </div>
</div>

