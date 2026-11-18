<?php
/**
 * Admin Audit Logs Ledger
 */
$logs = \App\Database\Database::query("
    SELECT al.*, u.name as user_name, u.email as user_email
    FROM audit_logs al
    LEFT JOIN users u ON al.user_id = u.id
    ORDER BY al.created_at DESC
    LIMIT 30
");
?>
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-200 gap-4">
        <div>
            <span class="text-[10px] font-bold tracking-[0.2em] text-champagne uppercase">Compliance & Security</span>
            <h1 class="font-serif text-3xl font-bold text-slate-900 mt-0.5">Administrative Audit Trail</h1>
            <p class="text-xs text-slate-500">Immutable ledger capturing critical administrative events, role changes, price overrides, and stock updates.</p>
        </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200 font-sans">
                    <tr>
                        <th class="py-3.5 px-6">Timestamp</th>
                        <th class="py-3.5 px-4">Operator</th>
                        <th class="py-3.5 px-4">Action Event</th>
                        <th class="py-3.5 px-4">Entity Type</th>
                        <th class="py-3.5 px-4">Entity ID</th>
                        <th class="py-3.5 px-6">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-[11px]">
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 font-sans">
                                Zero security incidents or audited actions recorded yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $l): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="py-3.5 px-6 text-slate-500">
                                    <?= date('d M Y, H:i:s', strtotime($l['created_at'])) ?>
                                </td>
                                <td class="py-3.5 px-4 font-sans font-bold text-slate-900">
                                    <?= htmlspecialchars($l['user_name'] ?? 'System Core') ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded font-bold uppercase text-[10px] bg-slate-100 text-slate-800 border border-slate-200">
                                        <?= htmlspecialchars($l['action']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-700">
                                    <?= htmlspecialchars($l['entity_type']) ?>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-900">
                                    #<?= htmlspecialchars($l['entity_id']) ?>
                                </td>
                                <td class="py-3.5 px-6 text-slate-400">
                                    <?= htmlspecialchars($l['ip_address']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

