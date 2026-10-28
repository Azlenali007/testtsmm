<?php
declare(strict_types=1);

use App\Core\Csrf;
use App\Helpers\Format;
use App\Helpers\Security;

$statuses = [
    'all' => 'All Orders',
    'pending' => 'Pending',
    'inprogress' => 'In Progress',
    'completed' => 'Completed',
    'partial' => 'Partial',
    'canceled' => 'Canceled',
];
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Order History</h1>
            <p class="text-xs text-slate-400 mt-1">Track the status, delivery, and refills of your active orders</p>
        </div>
        <a href="/order/new" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-lg shadow-indigo-600/30 transition">
            + Place New Order
        </a>
    </div>

    <!-- Filter Tabs & Search Bar -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <!-- Status Tabs -->
        <div class="flex items-center space-x-1 overflow-x-auto pb-2 lg:pb-0 scrollbar-none">
            <?php foreach ($statuses as $key => $label): ?>
                <a href="/orders?status=<?= $key ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition <?= $currentStatus === $key ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white hover:bg-slate-800' ?>">
                    <?= $label ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Search Form -->
        <form method="GET" action="/orders" class="relative max-w-xs w-full">
            <input type="hidden" name="status" value="<?= Security::e($currentStatus) ?>">
            <input type="text" name="search" value="<?= Security::e($search ?? '') ?>" placeholder="Search Order ID or Link..."
                   class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <svg class="w-4 h-4 text-slate-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </form>
    </div>

    <!-- Orders Table Card -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/80 uppercase text-[11px] text-slate-400 border-b border-slate-800 font-semibold tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">ID</th>
                        <th class="px-5 py-3.5">Service</th>
                        <th class="px-5 py-3.5">Target Link</th>
                        <th class="px-5 py-3.5">Charge</th>
                        <th class="px-5 py-3.5">Start</th>
                        <th class="px-5 py-3.5">Qty</th>
                        <th class="px-5 py-3.5">Remains</th>
                        <th class="px-5 py-3.5">Date</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center text-slate-500">
                                No orders matching the current filter criteria.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $o): ?>
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="px-5 py-4 font-mono font-bold text-indigo-400">#<?= (int)$o['id'] ?></td>
                                <td class="px-5 py-4 font-medium text-white max-w-xs truncate" title="<?= Security::e($o['service_name'] ?? '') ?>">
                                    <?= Security::e($o['service_name'] ?? 'Custom Service') ?>
                                </td>
                                <td class="px-5 py-4 max-w-[180px] truncate">
                                    <a href="<?= Security::e($o['link']) ?>" target="_blank" rel="noopener noreferrer" class="text-indigo-400 hover:text-indigo-300 underline font-mono">
                                        <?= Security::e($o['link']) ?>
                                    </a>
                                </td>
                                <td class="px-5 py-4 font-bold text-white"><?= Format::currency((float)$o['charge']) ?></td>
                                <td class="px-5 py-4 font-mono text-slate-400"><?= (int)($o['start_count'] ?? $o['start_counter'] ?? 0) ?></td>
                                <td class="px-5 py-4 font-mono font-semibold"><?= number_format((int)$o['quantity']) ?></td>
                                <td class="px-5 py-4 font-mono text-slate-400"><?= (int)$o['remains'] ?></td>
                                <td class="px-5 py-4 text-slate-400 whitespace-nowrap"><?= Format::date($o['created_at']) ?></td>
                                <td class="px-5 py-4"><?= Format::badge($o['status']) ?></td>
                                <td class="px-5 py-4 text-right whitespace-nowrap space-x-1">
                                    <?php if (!empty($o['service_refill_enabled']) || ($o['refill_status'] ?? '') === 'eligible' || $o['status'] === 'completed'): ?>
                                        <form action="/order/<?= (int)$o['id'] ?>/refill" method="POST" class="inline">
                                            <?= Csrf::field() ?>
                                            <button type="submit" title="Request automated refill"
                                                    class="px-2.5 py-1 rounded bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-300 text-xs font-semibold border border-indigo-500/20 transition">
                                                Refill
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($o['status'] === 'pending'): ?>
                                        <form action="/order/<?= (int)$o['id'] ?>/cancel" method="POST" class="inline" onsubmit="return confirm('Cancel this pending order and refund to balance?');">
                                            <?= Csrf::field() ?>
                                            <button type="submit" title="Cancel order and refund balance"
                                                    class="px-2.5 py-1 rounded bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 text-xs font-semibold border border-rose-500/20 transition">
                                                Cancel
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <?php if ($totalPages > 1): ?>
            <div class="p-4 bg-slate-950/70 border-t border-slate-800 flex items-center justify-between">
                <span class="text-xs text-slate-400">
                    Showing page <?= (int)$currentPage ?> of <?= (int)$totalPages ?> (<?= number_format((int)$totalOrders) ?> total orders)
                </span>
                <div class="flex space-x-2">
                    <?php if ($currentPage > 1): ?>
                        <a href="/orders?page=<?= $currentPage - 1 ?>&status=<?= urlencode($currentStatus) ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                           class="px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-xs font-medium text-slate-300 hover:text-white hover:bg-slate-800">
                            Previous
                        </a>
                    <?php endif; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="/orders?page=<?= $currentPage + 1 ?>&status=<?= urlencode($currentStatus) ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                           class="px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-xs font-medium text-slate-300 hover:text-white hover:bg-slate-800">
                            Next
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
