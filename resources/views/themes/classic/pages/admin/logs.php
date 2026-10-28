<?php
declare(strict_types=1);

use App\Helpers\Format;
use App\Helpers\Security;
?>
<div class="space-y-8" x-data="{ activeTab: 'audit' }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">System & Audit Logs</h1>
            <p class="text-xs text-slate-400 mt-1">Immutable security trail of administrative actions and automated background cron jobs</p>
        </div>

        <div class="flex items-center space-x-1 p-1 bg-slate-900 border border-slate-800 rounded-xl">
            <button @click="activeTab = 'audit'"
                    :class="activeTab === 'audit' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white'"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition">
                Security Audit Logs
            </button>
            <button @click="activeTab = 'cron'"
                    :class="activeTab === 'cron' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white'"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition">
                Background Cron Logs
            </button>
        </div>
    </div>

    <!-- Security Audit Logs Table -->
    <div x-show="activeTab === 'audit'" class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="p-6 border-b border-slate-800 flex items-center justify-between">
            <h3 class="text-base font-bold text-white">Administrative Audit Trail</h3>
            <span class="text-xs text-slate-500">Last 50 entries</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/70 uppercase text-[11px] text-slate-400 border-b border-slate-800 font-semibold tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">ID</th>
                        <th class="px-5 py-3.5">Admin User</th>
                        <th class="px-5 py-3.5">Action Executed</th>
                        <th class="px-5 py-3.5">Details</th>
                        <th class="px-5 py-3.5">IP Address</th>
                        <th class="px-5 py-3.5 text-right">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    <?php if (empty($auditLogs)): ?>
                        <tr><td colspan="6" class="px-6 py-8 text-center text-slate-500">No audit logs recorded yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($auditLogs as $al): ?>
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="px-5 py-3.5 font-mono text-slate-400">#<?= (int)$al['id'] ?></td>
                                <td class="px-5 py-3.5 font-semibold text-white"><?= Security::e($al['username'] ?? 'System/Guest') ?></td>
                                <td class="px-5 py-3.5 font-medium text-indigo-300"><?= Security::e($al['action']) ?></td>
                                <td class="px-5 py-3.5 text-slate-400 max-w-xs truncate"><?= Security::e($al['details'] ?? '') ?></td>
                                <td class="px-5 py-3.5 font-mono text-slate-400"><?= Security::e($al['ip_address'] ?? '127.0.0.1') ?></td>
                                <td class="px-5 py-3.5 text-right text-slate-400 whitespace-nowrap"><?= Format::date($al['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Cron Background Execution Logs Table -->
    <div x-show="activeTab === 'cron'" style="display: none;" class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="p-6 border-b border-slate-800 flex items-center justify-between">
            <h3 class="text-base font-bold text-white">Cron Background Execution Logs</h3>
            <span class="text-xs text-slate-500">Last 50 automated runs</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/70 uppercase text-[11px] text-slate-400 border-b border-slate-800 font-semibold tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">ID</th>
                        <th class="px-5 py-3.5">Job Name</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Records Processed</th>
                        <th class="px-5 py-3.5">Details</th>
                        <th class="px-5 py-3.5 text-right">Execution Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    <?php if (empty($cronLogs)): ?>
                        <tr><td colspan="6" class="px-6 py-8 text-center text-slate-500">No cron runs recorded yet. (Run <code class="text-indigo-400">php cron/check_orders.php</code> to test).</td></tr>
                    <?php else: ?>
                        <?php foreach ($cronLogs as $cl): ?>
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="px-5 py-3.5 font-mono text-slate-400">#<?= (int)$cl['id'] ?></td>
                                <td class="px-5 py-3.5 font-mono font-bold text-white"><?= Security::e($cl['job_name']) ?></td>
                                <td class="px-5 py-3.5"><?= Format::badge($cl['status']) ?></td>
                                <td class="px-5 py-3.5 font-mono text-slate-300"><?= (int)($cl['records_processed'] ?? 0) ?></td>
                                <td class="px-5 py-3.5 text-slate-400 max-w-sm truncate"><?= Security::e($cl['details'] ?? '') ?></td>
                                <td class="px-5 py-3.5 text-right text-slate-400 whitespace-nowrap"><?= Format::date($cl['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
