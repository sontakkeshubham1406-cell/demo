<?php
/**
 * GST Tax Invoice (Printable & Compliant)
 */
$orderId = (int)($_GET['id'] ?? 0);
$order = \App\Services\OrderService::getOrderDetails($orderId);

if (!$order) {
    die("Invoice not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tax Invoice - <?= htmlspecialchars($order['order_number']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
    </style>
</head>
<body class="bg-neutral-100 p-4 sm:p-8 font-sans text-neutral-800 text-xs">
    
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 border border-neutral-300 rounded-xl shadow-lg print:shadow-none print:border-none">
        
        <!-- Action Print Bar -->
        <div class="no-print mb-8 pb-4 border-b border-neutral-200 flex items-center justify-between">
            <span class="text-xs text-neutral-500">Official GST Compliant Electronic Tax Invoice</span>
            <button onclick="window.print()" class="px-5 py-2 bg-neutral-900 text-white font-bold rounded-lg uppercase tracking-wider text-xs hover:bg-neutral-800 transition-colors">
                Print / Save as PDF
            </button>
        </div>

        <!-- Invoice Header -->
        <div class="flex justify-between items-start pb-8 border-b-2 border-neutral-900">
            <div>
                <h1 class="text-2xl font-serif font-bold text-neutral-900 uppercase tracking-widest"><?= STORE_NAME ?></h1>
                <p class="text-[11px] text-neutral-500 mt-1">DEMO Private Limited</p>
                <p class="text-[11px] text-neutral-500">Level 8, Signature Towers, BKC, Mumbai - 400051</p>
                <p class="text-[11px] text-neutral-500"><strong>GSTIN:</strong> <?= STORE_GSTIN ?> | State: 27-Maharashtra</p>
            </div>
            <div class="text-right">
                <span class="text-lg font-bold text-neutral-900 uppercase">Tax Invoice</span>
                <p class="text-[11px] font-mono mt-1"><strong>Invoice No:</strong> INV-<?= htmlspecialchars($order['order_number']) ?></p>
                <p class="text-[11px]"><strong>Date:</strong> <?= date('d M Y', strtotime($order['created_at'])) ?></p>
                <p class="text-[11px]"><strong>Place of Supply:</strong> <?= htmlspecialchars($order['shipping_address']['state'] ?? 'Maharashtra') ?></p>
            </div>
        </div>

        <!-- Bill To & Ship To -->
        <div class="grid grid-cols-2 gap-8 py-6 border-b border-neutral-200 text-xs">
            <div>
                <span class="font-bold uppercase tracking-wider text-[10px] text-neutral-400">Billed & Shipped To:</span>
                <p class="font-bold text-neutral-900 mt-1"><?= htmlspecialchars($order['customer_name']) ?></p>
                <p class="text-neutral-600"><?= htmlspecialchars($order['shipping_address']['address_line1'] ?? '') ?></p>
                <?php if (!empty($order['shipping_address']['address_line2'])): ?>
                    <p class="text-neutral-600"><?= htmlspecialchars($order['shipping_address']['address_line2']) ?></p>
                <?php endif; ?>
                <p class="text-neutral-600"><?= htmlspecialchars($order['shipping_address']['city'] ?? '') ?>, <?= htmlspecialchars($order['shipping_address']['state'] ?? '') ?> - <?= htmlspecialchars($order['shipping_address']['postal_code'] ?? '') ?></p>
                <p class="text-neutral-600">Phone: <?= htmlspecialchars($order['customer_phone']) ?> | Email: <?= htmlspecialchars($order['customer_email']) ?></p>
            </div>
            <div class="text-right space-y-1">
                <span class="font-bold uppercase tracking-wider text-[10px] text-neutral-400">Payment & Shipment:</span>
                <p class="text-neutral-700">Payment Mode: <strong><?= htmlspecialchars($order['payment']['payment_method'] ?? 'RAZORPAY') ?></strong></p>
                <p class="text-neutral-700 font-mono text-[11px]">Gateway Ref: <?= htmlspecialchars($order['payment']['gateway_payment_id'] ?? 'VERIFIED') ?></p>
                <?php if (!empty($order['shipment']['tracking_number'])): ?>
                    <p class="text-neutral-700">Courier: <?= htmlspecialchars($order['shipment']['carrier_name']) ?> (AWB: <?= htmlspecialchars($order['shipment']['tracking_number']) ?>)</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Items Table -->
        <div class="py-6">
            <table class="w-full text-left text-xs border border-neutral-200">
                <thead class="bg-neutral-100 font-bold uppercase text-[10px] text-neutral-700 border-b border-neutral-200">
                    <tr>
                        <th class="py-2.5 px-3">#</th>
                        <th class="py-2.5 px-3">Description of Goods</th>
                        <th class="py-2.5 px-3">HSN Code</th>
                        <th class="py-2.5 px-3">Variant (Size/Color)</th>
                        <th class="py-2.5 px-3 text-center">Qty</th>
                        <th class="py-2.5 px-3 text-right">Unit Rate (₹)</th>
                        <th class="py-2.5 px-3 text-right">Taxable Value (₹)</th>
                        <th class="py-2.5 px-3 text-right">GST Rate</th>
                        <th class="py-2.5 px-3 text-right">Total (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200">
                    <?php $idx = 1; foreach ($order['items'] as $item): ?>
                        <?php 
                        $taxable = round($item['total_price'] / 1.12, 2);
                        ?>
                        <tr>
                            <td class="py-2.5 px-3 text-neutral-500"><?= $idx++ ?></td>
                            <td class="py-2.5 px-3 font-semibold text-neutral-900"><?= htmlspecialchars($item['product_name']) ?></td>
                            <td class="py-2.5 px-3 font-mono text-neutral-500">62052000</td>
                            <td class="py-2.5 px-3 text-neutral-600"><?= htmlspecialchars($item['size_name']) ?> / <?= htmlspecialchars($item['color_name']) ?></td>
                            <td class="py-2.5 px-3 text-center font-mono font-bold"><?= $item['quantity'] ?></td>
                            <td class="py-2.5 px-3 text-right font-mono"><?= number_format($item['unit_price'], 2) ?></td>
                            <td class="py-2.5 px-3 text-right font-mono"><?= number_format($taxable, 2) ?></td>
                            <td class="py-2.5 px-3 text-right">12%</td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold"><?= number_format($item['total_price'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Totals Summary & Tax Breakdown -->
        <div class="flex justify-end pt-4">
            <div class="w-72 space-y-2 text-xs">
                <div class="flex justify-between text-neutral-600">
                    <span>Subtotal:</span>
                    <span class="font-mono"><?= STORE_CURRENCY_SYMBOL ?><?= number_format($order['subtotal'], 2) ?></span>
                </div>
                <?php if ($order['discount_amount'] > 0): ?>
                    <div class="flex justify-between text-emerald-700 font-semibold">
                        <span>Coupon Discount:</span>
                        <span class="font-mono">- <?= STORE_CURRENCY_SYMBOL ?><?= number_format($order['discount_amount'], 2) ?></span>
                    </div>
                <?php endif; ?>
                <div class="flex justify-between text-neutral-600">
                    <span>Shipping Charges:</span>
                    <span class="font-mono"><?= $order['shipping_fee'] == 0 ? 'FREE' : STORE_CURRENCY_SYMBOL . number_format($order['shipping_fee'], 2) ?></span>
                </div>
                <div class="flex justify-between text-neutral-500 text-[11px] pt-1 border-t border-neutral-200">
                    <span>CGST (6%):</span>
                    <span class="font-mono"><?= STORE_CURRENCY_SYMBOL ?><?= number_format($order['tax_amount'] / 2, 2) ?></span>
                </div>
                <div class="flex justify-between text-neutral-500 text-[11px]">
                    <span>SGST (6%):</span>
                    <span class="font-mono"><?= STORE_CURRENCY_SYMBOL ?><?= number_format($order['tax_amount'] / 2, 2) ?></span>
                </div>
                <div class="flex justify-between font-bold text-neutral-900 pt-2 border-t-2 border-neutral-900 text-sm">
                    <span>Invoice Total:</span>
                    <span class="font-mono"><?= STORE_CURRENCY_SYMBOL ?><?= number_format($order['total_amount'], 2) ?></span>
                </div>
            </div>
        </div>

        <!-- Signatory Declaration -->
        <div class="pt-12 mt-8 border-t border-neutral-200 flex justify-between items-end text-[11px] text-neutral-500">
            <div>
                <p>This is a computer-generated tax invoice. No signature required.</p>
                <p class="text-[10px] text-neutral-400 mt-0.5">Goods once sold are eligible for 7-day return/replacement as per terms.</p>
            </div>
            <div class="text-right">
                <p class="font-bold text-neutral-900 uppercase">For <?= STORE_NAME ?> Private Limited</p>
                <div class="h-12 flex items-center justify-end font-serif italic text-neutral-400">Authorized Signatory</div>
            </div>
        </div>
    </div>
</body>
</html>

