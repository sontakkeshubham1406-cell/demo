<?php
/**
 * Admin System Settings & Dynamic RBAC Matrix
 */
$roles = \App\Database\Database::query("SELECT * FROM roles ORDER BY id ASC");
$staffUsers = \App\Database\Database::query("
    SELECT u.*, r.name as role_name 
    FROM users u 
    JOIN roles r ON u.role_id = r.id 
    ORDER BY u.id ASC
");
?>
<div class="space-y-8">
    <div class="flex items-center justify-between pb-6 border-b border-slate-200">
        <div>
            <span class="text-[10px] font-bold tracking-[0.2em] text-champagne uppercase">Platform Configuration</span>
            <h1 class="font-serif text-3xl font-bold text-slate-900 mt-0.5">Settings & RBAC Governance</h1>
            <p class="text-xs text-slate-500">Configure financial rules, Razorpay gateway credentials, and staff permissions.</p>
        </div>
    </div>

    <!-- 2-Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 text-xs">
        
        <!-- Column 1: Store & Financial Settings -->
        <div class="bg-white border border-slate-200 p-6 sm:p-8 rounded-2xl shadow-xs space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 border-b border-slate-200 pb-3">1. Store Identity & Shipping Rules</h3>

            <div class="space-y-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Brand Name</label>
                    <input type="text" value="<?= STORE_NAME ?>" readonly class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-slate-600 font-semibold">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Free Shipping Threshold</label>
                        <input type="text" value="<?= STORE_CURRENCY_SYMBOL ?><?= number_format(FREE_SHIPPING_THRESHOLD) ?>" readonly class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-slate-600 font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Standard Flat Shipping</label>
                        <input type="text" value="<?= STORE_CURRENCY_SYMBOL ?><?= number_format(FLAT_SHIPPING_FEE) ?>" readonly class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-slate-600 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Apparel GST Rate</label>
                        <input type="text" value="12% (CGST 6% + SGST 6%)" readonly class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-slate-600">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Registered GSTIN</label>
                        <input type="text" value="<?= STORE_GSTIN ?>" readonly class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-slate-600 font-mono">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Temporary Stock Lock Duration</label>
                    <input type="text" value="<?= STOCK_RESERVATION_MINUTES ?> Minutes (Active Countdown Timer)" readonly class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-slate-600 font-semibold">
                </div>
            </div>
        </div>

        <!-- Column 2: Razorpay Gateway Credentials -->
        <div class="bg-white border border-slate-200 p-6 sm:p-8 rounded-2xl shadow-xs space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 border-b border-slate-200 pb-3">2. Razorpay Payment Gateway Keys</h3>

            <div class="space-y-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Razorpay Key ID</label>
                    <input type="text" value="<?= RAZORPAY_KEY_ID ?>" readonly class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl font-mono text-slate-700">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Razorpay Key Secret</label>
                    <input type="password" value="••••••••••••••••••••••••••••••" readonly class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl font-mono text-slate-700">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Webhook Endpoint URL</label>
                    <input type="text" value="https://yourbrandclothing.com/api/webhooks/razorpay" readonly class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl font-mono text-slate-700">
                </div>

                <div class="p-3 bg-emerald-50 text-emerald-800 rounded-xl border border-emerald-200 text-[11px] leading-relaxed">
                    <strong>Zero-Trust Security Verification:</strong> All incoming client responses are verified via cryptographic HMAC-SHA256 signatures server-side. Orders are never confirmed based on frontend status alone.
                </div>
            </div>
        </div>
    </div>

    <!-- Role-Based Access Control (RBAC) -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-200">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">3. Staff Accounts & Role-Based Access Control (RBAC)</h3>
            <p class="text-xs text-slate-500">Separation of concerns across Order Management, Product Catalog, and Inventory Audits.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-6">Staff Member</th>
                        <th class="py-3 px-4">Administrative Role</th>
                        <th class="py-3 px-4">Permissions Scope</th>
                        <th class="py-3 px-4">Account Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($staffUsers as $su): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-6">
                                <p class="font-bold text-slate-900"><?= htmlspecialchars($su['name']) ?></p>
                                <p class="text-slate-400 text-[11px]"><?= htmlspecialchars($su['email']) ?></p>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 bg-amber-50 text-amber-900 font-bold font-mono rounded-lg border border-amber-200 text-[11px]">
                                    <?= htmlspecialchars($su['role_name']) ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                <?= $su['role_id'] == 1 ? 'Full Super Admin (All Modules & Secrets)' : 'Catalog & Inventory Operations' ?>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-emerald-700 font-bold text-[11px] flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span>Active</span>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

