<?php
/**
 * High-Conversion One-Page Checkout with Razorpay Integration & 15-Minute Stock Reservation Lock
 */
$cartTotals = \App\Services\CouponPricingEngine::calculateCartTotals();
$currentUser = \App\Services\AuthService::getCurrentUser();
$savedAddresses = $currentUser ? \App\Database\Database::query("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC", [$currentUser['id']]) : [];
$defaultAddress = $savedAddresses[0] ?? null;

if (empty($cartTotals['items'])) {
    header("Location: /cart");
    exit;
}
?>
<!-- Razorpay Standard Checkout JS SDK -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12" 
     x-data="checkoutManager(<?= htmlspecialchars(json_encode($cartTotals)) ?>)">

    <!-- Stock Reservation Countdown Banner -->
    <div class="mb-8 bg-amber-50 border border-amber-200 text-amber-900 px-4 py-3 rounded-2xl flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-3">
            <i data-lucide="clock" class="w-5 h-5 text-amber-600 animate-pulse"></i>
            <div>
                <span class="text-xs font-bold uppercase tracking-wider">Garments Soft-Locked For You</span>
                <p class="text-[11px] text-amber-700">Stock is temporarily reserved for 15 minutes to prevent overselling.</p>
            </div>
        </div>
        <div class="text-right">
            <span class="font-mono text-base font-bold text-amber-900" x-text="timerDisplay">15:00</span>
            <span class="block text-[10px] uppercase text-amber-600 tracking-wider">Remaining</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
        
        <!-- Left: Form Steps (7 cols) -->
        <div class="lg:col-span-7 space-y-8">
            
            <!-- Step 1: Customer Contact -->
            <div class="bg-white border border-subtleBorder p-6 sm:p-8 rounded-2xl shadow-xs space-y-4">
                <div class="flex items-center gap-3 border-b border-subtleBorder pb-4">
                    <span class="w-6 h-6 rounded-full bg-obsidian text-white text-xs font-bold flex items-center justify-center">1</span>
                    <h2 class="text-sm font-bold uppercase tracking-[0.2em] text-obsidian">Contact Information</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-medium text-obsidian mb-1">Full Name *</label>
                        <input type="text" x-model="formData.name" required placeholder="Aarav Sharma" 
                               class="w-full px-3.5 py-2.5 bg-[#FAF9F6] border border-subtleBorder rounded-lg focus:outline-none focus:border-obsidian">
                    </div>
                    <div>
                        <label class="block font-medium text-obsidian mb-1">Mobile Phone (for delivery OTP) *</label>
                        <input type="tel" x-model="formData.phone" required placeholder="+91 98765 43210" 
                               class="w-full px-3.5 py-2.5 bg-[#FAF9F6] border border-subtleBorder rounded-lg focus:outline-none focus:border-obsidian">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block font-medium text-obsidian mb-1">Email Address (for order receipts & invoices) *</label>
                        <input type="email" x-model="formData.email" required placeholder="aarav@example.com" 
                               class="w-full px-3.5 py-2.5 bg-[#FAF9F6] border border-subtleBorder rounded-lg focus:outline-none focus:border-obsidian">
                    </div>
                </div>
            </div>

            <!-- Step 2: Delivery Address -->
            <div class="bg-white border border-subtleBorder p-6 sm:p-8 rounded-2xl shadow-xs space-y-4">
                <div class="flex items-center gap-3 border-b border-subtleBorder pb-4">
                    <span class="w-6 h-6 rounded-full bg-obsidian text-white text-xs font-bold flex items-center justify-center">2</span>
                    <h2 class="text-sm font-bold uppercase tracking-[0.2em] text-obsidian">Shipping Address</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="sm:col-span-2">
                        <label class="block font-medium text-obsidian mb-1">Flat / House No. / Building Name *</label>
                        <input type="text" x-model="formData.address_line1" required placeholder="Flat 402, Signature Heights" 
                               class="w-full px-3.5 py-2.5 bg-[#FAF9F6] border border-subtleBorder rounded-lg focus:outline-none focus:border-obsidian">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block font-medium text-obsidian mb-1">Street / Area / Colony</label>
                        <input type="text" x-model="formData.address_line2" placeholder="Bandra West, Hill Road" 
                               class="w-full px-3.5 py-2.5 bg-[#FAF9F6] border border-subtleBorder rounded-lg focus:outline-none focus:border-obsidian">
                    </div>
                    <div>
                        <label class="block font-medium text-obsidian mb-1">Landmark (Optional)</label>
                        <input type="text" x-model="formData.landmark" placeholder="Near Mehboob Studio" 
                               class="w-full px-3.5 py-2.5 bg-[#FAF9F6] border border-subtleBorder rounded-lg focus:outline-none focus:border-obsidian">
                    </div>
                    <div>
                        <label class="block font-medium text-obsidian mb-1">Pin Code (Postal Code) *</label>
                        <input type="text" x-model="formData.postal_code" required placeholder="400050" 
                               class="w-full px-3.5 py-2.5 bg-[#FAF9F6] border border-subtleBorder rounded-lg focus:outline-none focus:border-obsidian font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-obsidian mb-1">City *</label>
                        <input type="text" x-model="formData.city" required placeholder="Mumbai" 
                               class="w-full px-3.5 py-2.5 bg-[#FAF9F6] border border-subtleBorder rounded-lg focus:outline-none focus:border-obsidian">
                    </div>
                    <div>
                        <label class="block font-medium text-obsidian mb-1">State *</label>
                        <input type="text" x-model="formData.state" required placeholder="Maharashtra" 
                               class="w-full px-3.5 py-2.5 bg-[#FAF9F6] border border-subtleBorder rounded-lg focus:outline-none focus:border-obsidian">
                    </div>
                </div>
            </div>

            <!-- Step 3: Payment Gateway Selection (Razorpay) -->
            <div class="bg-white border border-subtleBorder p-6 sm:p-8 rounded-2xl shadow-xs space-y-4">
                <div class="flex items-center gap-3 border-b border-subtleBorder pb-4">
                    <span class="w-6 h-6 rounded-full bg-obsidian text-white text-xs font-bold flex items-center justify-center">3</span>
                    <h2 class="text-sm font-bold uppercase tracking-[0.2em] text-obsidian">Payment Method</h2>
                </div>

                <div class="space-y-3 text-xs">
                    <!-- Razorpay Option -->
                    <label class="flex items-start gap-4 p-4 border-2 border-obsidian rounded-xl bg-champagneLight/20 cursor-pointer">
                        <input type="radio" name="payment_method" value="RAZORPAY" checked class="mt-1 text-obsidian focus:ring-obsidian">
                        <div class="flex-grow">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-obsidian">Online Payment via Razorpay</span>
                                <span class="bg-obsidian text-white text-[10px] px-2 py-0.5 rounded font-bold uppercase">Fast & Secure</span>
                            </div>
                            <p class="text-mutedText mt-1">UPI (GPay, PhonePe, Paytm), All Debit & Credit Cards, NetBanking, and Wallets.</p>
                            <div class="flex items-center gap-2 mt-3 opacity-80">
                                <span class="bg-white border border-subtleBorder px-2 py-0.5 rounded font-bold text-[10px]">UPI</span>
                                <span class="bg-white border border-subtleBorder px-2 py-0.5 rounded font-bold text-[10px]">Cards</span>
                                <span class="bg-white border border-subtleBorder px-2 py-0.5 rounded font-bold text-[10px]">NetBanking</span>
                            </div>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Right: Order Summary Sticky (5 cols) -->
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-white border border-subtleBorder p-6 sm:p-8 rounded-2xl shadow-xs space-y-6 sticky top-28">
                <h3 class="text-xs font-bold uppercase tracking-[0.2em] text-obsidian border-b border-subtleBorder pb-3">Order Summary</h3>

                <!-- Bag Items Snapshot -->
                <div class="max-h-64 overflow-y-auto divide-y divide-subtleBorder/60 pr-1">
                    <?php foreach ($cartTotals['items'] as $item): ?>
                        <div class="py-3 flex items-center gap-3">
                            <img src="<?= htmlspecialchars($item['item_image']) ?>" class="w-12 h-16 object-cover rounded-md bg-neutral-100 flex-shrink-0" alt="<?= htmlspecialchars($item['product_name']) ?>">
                            <div class="flex-grow min-w-0 text-xs">
                                <h4 class="font-semibold text-obsidian truncate"><?= htmlspecialchars($item['product_name']) ?></h4>
                                <p class="text-mutedText text-[11px]"><?= htmlspecialchars($item['size_name']) ?> | <?= htmlspecialchars($item['color_name']) ?> × <?= $item['quantity'] ?></p>
                            </div>
                            <div class="text-xs font-bold text-obsidian">
                                <?= STORE_CURRENCY_SYMBOL ?><?= number_format($item['unit_price'] * $item['quantity'], 2) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Financial Totals -->
                <div class="space-y-2 text-xs border-t border-subtleBorder pt-4">
                    <div class="flex justify-between text-mutedText">
                        <span>Subtotal</span>
                        <span class="font-medium text-obsidian"><?= STORE_CURRENCY_SYMBOL ?><?= number_format($cartTotals['subtotal'], 2) ?></span>
                    </div>
                    <?php if ($cartTotals['discount_amount'] > 0): ?>
                        <div class="flex justify-between text-emerald-700 font-semibold">
                            <span>Coupon Savings (<?= htmlspecialchars($cartTotals['applied_coupon']['code']) ?>)</span>
                            <span>- <?= STORE_CURRENCY_SYMBOL ?><?= number_format($cartTotals['discount_amount'], 2) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="flex justify-between text-mutedText">
                        <span>Express Delivery</span>
                        <span><?= $cartTotals['shipping_fee'] == 0 ? '<strong class="text-emerald-700">COMPLIMENTARY</strong>' : STORE_CURRENCY_SYMBOL . number_format($cartTotals['shipping_fee'], 2) ?></span>
                    </div>
                    <div class="flex justify-between text-mutedText text-[11px]">
                        <span>Applicable GST (Included)</span>
                        <span><?= STORE_CURRENCY_SYMBOL ?><?= number_format($cartTotals['tax_amount'], 2) ?></span>
                    </div>
                    <div class="flex justify-between text-base font-bold text-obsidian pt-3 border-t border-subtleBorder">
                        <span>Final Amount Payable</span>
                        <span><?= STORE_CURRENCY_SYMBOL ?><?= number_format($cartTotals['total_payable'], 2) ?></span>
                    </div>
                </div>

                <!-- Trigger Payment CTA -->
                <button type="button" 
                        @click="initiateRazorpayPayment()" 
                        :disabled="loading"
                        class="w-full py-4 bg-obsidian text-white text-xs font-bold uppercase tracking-[0.2em] rounded-xl hover:bg-slateDark transition-all shadow-lg flex items-center justify-center gap-2 disabled:opacity-50">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                    <span x-text="loading ? 'Connecting Razorpay Gateway...' : 'Pay <?= STORE_CURRENCY_SYMBOL ?><?= number_format($cartTotals['total_payable'], 2) ?> Now'"></span>
                </button>

                <p class="text-[10px] text-center text-mutedText">
                    By confirming this order, you accept our <a href="/pages/terms-and-conditions" class="underline">Terms</a> and <a href="/pages/returns-exchanges" class="underline">7-Day Return Policy</a>.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function checkoutManager(cartTotals) {
    return {
        cartTotals: cartTotals,
        loading: false,
        timeLeft: 15 * 60,
        timerDisplay: '15:00',

        formData: {
            name: '<?= htmlspecialchars($currentUser['name'] ?? 'Aarav Sharma') ?>',
            email: '<?= htmlspecialchars($currentUser['email'] ?? 'customer@example.com') ?>',
            phone: '<?= htmlspecialchars($currentUser['phone'] ?? '+919876543210') ?>',
            address_line1: '<?= htmlspecialchars($defaultAddress['address_line1'] ?? 'Flat 402, Signature Heights') ?>',
            address_line2: '<?= htmlspecialchars($defaultAddress['address_line2'] ?? 'Bandra West, Hill Road') ?>',
            landmark: '<?= htmlspecialchars($defaultAddress['landmark'] ?? 'Near Mehboob Studio') ?>',
            city: '<?= htmlspecialchars($defaultAddress['city'] ?? 'Mumbai') ?>',
            state: '<?= htmlspecialchars($defaultAddress['state'] ?? 'Maharashtra') ?>',
            postal_code: '<?= htmlspecialchars($defaultAddress['postal_code'] ?? '400050') ?>',
        },

        init() {
            // Start 15-minute countdown timer
            setInterval(() => {
                if (this.timeLeft > 0) {
                    this.timeLeft--;
                    let m = Math.floor(this.timeLeft / 60);
                    let s = this.timeLeft % 60;
                    this.timerDisplay = `${m}:${s < 10 ? '0' : ''}${s}`;
                } else {
                    alert('Your 15-minute stock reservation has expired. Refreshing bag...');
                    window.location.href = '/cart';
                }
            }, 1000);
        },

        initiateRazorpayPayment() {
            if (!this.formData.name || !this.formData.phone || !this.formData.email || !this.formData.address_line1 || !this.formData.postal_code) {
                alert('Please complete all required contact and shipping address fields.');
                return;
            }

            this.loading = true;

            // 1. Initialize Order on Backend
            fetch('/api/checkout/create-order', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(this.formData)
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert('Checkout Error: ' + data.message);
                    this.loading = false;
                    return;
                }

                // If sandbox simulation mode or live Razorpay modal
                if (data.is_sandbox) {
                    // Seamless instant simulation popup for local sandbox testing
                    if (confirm(`DEMO Payment Gateway (Sandbox Mode)\n\nOrder: ${data.order_number}\nPayable: ₹${(data.amount_in_paise / 100).toFixed(2)}\n\nSimulate Successful Razorpay Payment?`)) {
                        this.verifyPaymentOnBackend(data.order_id, data.razorpay_order_id, 'pay_MockPay_' + Date.now(), 'sandbox_valid_sig');
                    } else {
                        this.loading = false;
                    }
                } else {
                    // Open live Razorpay standard checkout modal
                    let options = {
                        "key": data.razorpay_key_id,
                        "amount": data.amount_in_paise,
                        "currency": "INR",
                        "name": "<?= STORE_NAME ?>",
                        "description": "Payment for " + data.order_number,
                        "order_id": data.razorpay_order_id,
                        "handler": (response) => {
                            this.verifyPaymentOnBackend(data.order_id, response.razorpay_order_id, response.razorpay_payment_id, response.razorpay_signature);
                        },
                        "prefill": {
                            "name": data.customer_name,
                            "email": data.customer_email,
                            "contact": data.customer_phone
                        },
                        "theme": { "color": "#0F172A" }
                    };

                    let rzp = new Razorpay(options);
                    rzp.on('payment.failed', (response) => {
                        alert('Payment Failed: ' + response.error.description);
                        this.loading = false;
                    });
                    rzp.open();
                }
            })
            .catch(err => {
                alert('Network error connecting to payment gateway: ' + err.message);
                this.loading = false;
            });
        },

        verifyPaymentOnBackend(orderId, razorpayOrderId, razorpayPaymentId, razorpaySignature) {
            fetch('/api/checkout/verify-payment', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    order_id: orderId,
                    razorpay_order_id: razorpayOrderId,
                    razorpay_payment_id: razorpayPaymentId,
                    razorpay_signature: razorpaySignature
                })
            })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    window.location.href = '/checkout/success?order_id=' + orderId;
                } else {
                    alert('Verification error: ' + result.message);
                    this.loading = false;
                }
            })
            .catch(err => {
                alert('Verification callback failed: ' + err.message);
                this.loading = false;
            });
        }
    }
}
</script>

