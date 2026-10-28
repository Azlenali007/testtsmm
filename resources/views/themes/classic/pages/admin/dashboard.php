<?php
declare(strict_types=1);

use App\Helpers\Format;
use App\Helpers\Security;
?>
<div class="space-y-8"
     x-data="{
         init() {
             const ctx = document.getElementById('revenueChart');
             if (ctx && typeof Chart !== 'undefined') {
                 new Chart(ctx, {
                     type: 'line',
                     data: {
                         labels: <?= json_encode($revenueData['labels']) ?>,
                         datasets: [
                             {
                                 label: 'Revenue ($)',
                                 data: <?= json_encode($revenueData['revenue']) ?>,
                                 borderColor: '#6366f1',
                                 backgroundColor: 'rgba(99, 102, 241, 0.1)',
                                 fill: true,
                                 tension: 0.3
                             },
                             {
                                 label: 'Orders Count',
                                 data: <?= json_encode($revenueData['orders']) ?>,
                                 borderColor: '#10b981',
                                 backgroundColor: 'transparent',
                                 borderDash: [5, 5],
                                 tension: 0.3
                             }
                         ]
                     },
                     options: {
                         responsive: true,
                         maintainAspectRatio: false,
                         plugins: {
                             legend: { labels: { color: '#94a3b8', font: { size: 12 } } }
                         },
                         scales: {
                             x: { grid: { color: '#1e293b' }, ticks: { color: '#64748b' } },
                             y: { grid: { color: '#1e293b' }, ticks: { color: '#64748b' } }
                         }
                     }
                 });
             }
         }
     }">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Super Admin Dashboard</h1>
            <p class="text-xs text-slate-400 mt-1">Real-time system telemetry, profit metrics, and order dispatch status</p>
        </div>
        <div class="flex space-x-2">
            <a href="/admin/orders" class="px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs font-semibold text-slate-300 hover:text-white transition">
                Manage Orders
            </a>
            <a href="/admin/users" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-lg shadow-indigo-600/30 transition">
                Manage Users
            </a>
        </div>
    </div>

    <!-- 6 KPI Stat Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Total Users</span>
            <div class="text-xl font-black text-white mt-1"><?= number_format($totalUsers) ?></div>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Total Orders</span>
            <div class="text-xl font-black text-indigo-400 mt-1"><?= number_format($totalOrders) ?></div>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Completed Sales</span>
            <div class="text-xl font-black text-emerald-400 mt-1"><?= Format::currency((float)$totalRevenue) ?></div>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Net Profit</span>
            <div class="text-xl font-black text-emerald-300 mt-1"><?= Format::currency((float)$totalProfits) ?></div>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Open Tickets</span>
            <div class="text-xl font-black text-amber-400 mt-1"><?= number_format($openTickets) ?></div>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Pending Deposits</span>
            <div class="text-xl font-black text-purple-400 mt-1"><?= number_format($pendingPayments) ?></div>
        </div>
    </div>

    <!-- Chart & Provider Balances Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Revenue Trends Chart (2 Cols) -->
        <div class="lg:col-span-2 rounded-2xl bg-slate-900 border border-slate-800 p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider">30-Day Growth & Revenue</h3>
                <span class="text-xs text-indigo-400 font-semibold">Live System Metric</span>
            </div>
            <div class="h-64 relative">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- SMM Provider API Balances (1 Col) -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 space-y-4 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">Provider Balances</h3>
                    <a href="/admin/providers" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold">Manage &rarr;</a>
                </div>

                <div class="mt-4 space-y-3">
                    <?php if (empty($providers)): ?>
                        <div class="text-xs text-slate-500 py-4 text-center">No SMM providers configured.</div>
                    <?php else: ?>
                        <?php foreach ($providers as $prov): ?>
                            <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 flex items-center justify-between text-xs">
                                <div>
                                    <div class="font-bold text-white"><?= Security::e($prov['name']) ?></div>
                                    <span class="text-[10px] text-slate-500">API Status: <?= $prov['is_active'] ? 'Active' : 'Disabled' ?></span>
                                </div>
                                <div class="text-right">
                                    <span class="font-mono font-bold text-emerald-400 text-sm"><?= Format::currency((float)$prov['balance']) ?></span>
                                    <span class="text-[10px] text-slate-500 block"><?= Security::e($prov['currency']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-800 text-xs text-slate-400 flex items-center justify-between">
                <span>Cron sync frequency:</span>
                <span class="font-mono text-indigo-300">Every 5 mins</span>
            </div>
        </div>
    </div>

    <!-- Recent System Orders Table -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="p-6 border-b border-slate-800 flex items-center justify-between">
            <h3 class="text-base font-bold text-white">Latest Orders</h3>
            <a href="/admin/orders" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">View All Orders &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/70 uppercase text-[11px] text-slate-400 border-b border-slate-800 font-semibold tracking-wider">
                    <tr>
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3">Service</th>
                        <th class="px-5 py-3">Charge</th>
                        <th class="px-5 py-3">Cost / Profit</th>
                        <th class="px-5 py-3">Qty</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    <?php if (empty($recentOrders)): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-slate-500">No orders recorded yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentOrders as $ro): ?>
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="px-5 py-3.5 font-mono font-bold text-indigo-400">#<?= (int)$ro['id'] ?></td>
                                <td class="px-5 py-3.5 font-semibold text-white"><?= Security::e($ro['username'] ?? 'User') ?></td>
                                <td class="px-5 py-3.5 max-w-xs truncate text-slate-300"><?= Security::e($ro['service_name'] ?? 'Custom') ?></td>
                                <td class="px-5 py-3.5 font-bold text-white"><?= Format::currency((float)$ro['charge']) ?></td>
                                <td class="px-5 py-3.5 font-mono text-emerald-400">+<?= Format::currency((float)$ro['profit']) ?></td>
                                <td class="px-5 py-3.5 font-mono"><?= number_format((int)$ro['quantity']) ?></td>
                                <td class="px-5 py-3.5"><?= Format::badge($ro['status']) ?></td>
                                <td class="px-5 py-3.5 text-right text-slate-400 whitespace-nowrap"><?= Format::date($ro['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
