<?php
declare(strict_types=1);

use App\Core\Csrf;
use App\Helpers\Format;
use App\Helpers\Security;
?>
<div class="space-y-6"
     x-data="{
         editModal: false,
         selectedOrder: null,
         openEdit(order) {
             this.selectedOrder = order;
             this.editModal = true;
         }
     }">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Order Management</h1>
            <p class="text-xs text-slate-400 mt-1">Review orders, force status updates, process manual refunds, or resend to API providers</p>
        </div>
        <div class="text-xs text-slate-400">
            Total Orders: <span class="font-bold text-white"><?= number_format($totalOrders) ?></span>
        </div>
    </div>

    <!-- Status Tabs with live count badges -->
    <div class="flex items-center space-x-1.5 overflow-x-auto pb-2 scrollbar-none">
        <?php foreach ($statusCounts as $sKey => $sCount): ?>
            <a href="/admin/orders?status=<?= $sKey ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
               class="px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition flex items-center space-x-1.5 <?= $currentStatus === $sKey ? 'bg-indigo-600 text-white' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white' ?>">
                <span><?= ucfirst($sKey) ?></span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-950/60 font-mono"><?= $sCount ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Search input -->
    <div class="flex justify-end">
        <form method="GET" action="/admin/orders" class="relative max-w-xs w-full">
            <input type="hidden" name="status" value="<?= Security::e($currentStatus) ?>">
            <input type="text" name="search" value="<?= Security::e($search) ?>" placeholder="Search Order ID, user, or link..."
                   class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <svg class="w-4 h-4 text-slate-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/70 uppercase text-[11px] text-slate-400 border-b border-slate-800 font-semibold tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">ID</th>
                        <th class="px-5 py-3.5">User</th>
                        <th class="px-5 py-3.5">Charge</th>
                        <th class="px-5 py-3.5">Target Link</th>
                        <th class="px-5 py-3.5">Service</th>
                        <th class="px-5 py-3.5">Provider / Ext ID</th>
                        <th class="px-5 py-3.5">Qty</th>
                        <th class="px-5 py-3.5">Start / Rem</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    <?php if (empty($orders)): ?>
                        <tr><td colspan="10" class="px-6 py-8 text-center text-slate-500">No orders match filter.</td></tr>
                    <?php else: ?>
                        <?php foreach ($orders as $o): ?>
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="px-5 py-3.5 font-mono font-bold text-indigo-400">#<?= (int)$o['id'] ?></td>
                                <td class="px-5 py-3.5 font-semibold text-white"><?= Security::e($o['username'] ?? 'User') ?></td>
                                <td class="px-5 py-3.5 font-bold text-white"><?= Format::currency((float)$o['charge']) ?></td>
                                <td class="px-5 py-3.5 max-w-[140px] truncate">
                                    <a href="<?= Security::e($o['link']) ?>" target="_blank" rel="noopener noreferrer" class="text-indigo-400 hover:text-indigo-300 underline font-mono">
                                        <?= Security::e($o['link']) ?>
                                    </a>
                                </td>
                                <td class="px-5 py-3.5 max-w-[180px] truncate text-slate-200" title="<?= Security::e($o['service_name'] ?? '') ?>">
                                    <?= Security::e($o['service_name'] ?? 'Custom') ?>
                                </td>
                                <td class="px-5 py-3.5 text-slate-400 font-mono text-[11px]">
                                    <?= Security::e($o['provider_name'] ?? 'Manual') ?>
                                    <?php if ($o['provider_order_id']): ?>
                                        <span class="text-indigo-300">(#<?= Security::e($o['provider_order_id']) ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-3.5 font-mono font-bold text-white"><?= number_format((int)$o['quantity']) ?></td>
                                <td class="px-5 py-3.5 font-mono text-slate-400"><?= (int)($o['start_count'] ?? $o['start_counter'] ?? 0) ?> / <?= (int)$o['remains'] ?></td>
                                <td class="px-5 py-3.5"><?= Format::badge($o['status']) ?></td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-1.5">
                                    <button type="button" @click="openEdit(<?= htmlspecialchars(json_encode($o), ENT_QUOTES, 'UTF-8') ?>)"
                                            class="px-2.5 py-1 rounded-lg bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 border border-indigo-500/20 font-semibold transition">
                                        Status
                                    </button>
                                    <?php if (!empty($o['provider_id'])): ?>
                                        <form action="/admin/orders/resend/<?= (int)$o['id'] ?>" method="POST" class="inline">
                                            <?= Csrf::field() ?>
                                            <button type="submit" title="Resend to provider API" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 font-semibold transition">
                                                Resend
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

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="p-4 bg-slate-950/70 border-t border-slate-800 flex items-center justify-between">
                <span class="text-xs text-slate-400">Page <?= (int)$currentPage ?> of <?= (int)$totalPages ?></span>
                <div class="flex space-x-2">
                    <?php if ($currentPage > 1): ?>
                        <a href="/admin/orders?page=<?= $currentPage - 1 ?>&status=<?= urlencode($currentStatus) ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                           class="px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-xs font-medium text-slate-300 hover:text-white">
                            Previous
                        </a>
                    <?php endif; ?>
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="/admin/orders?page=<?= $currentPage + 1 ?>&status=<?= urlencode($currentStatus) ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                           class="px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-xs font-medium text-slate-300 hover:text-white">
                            Next
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Edit Order Status Modal -->
    <div x-show="editModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="editModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="editModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="editModal" x-transition.scale class="inline-block align-bottom bg-slate-900 border border-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <h3 class="text-base font-bold text-white">Update Order #<span x-text="selectedOrder?.id"></span></h3>
                    <button @click="editModal = false" class="text-slate-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="'/admin/orders/update/' + selectedOrder?.id" method="POST" class="space-y-4 text-xs">
                    <?= Csrf::field() ?>

                    <div>
                        <label class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">New Status</label>
                        <select name="status" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="inprogress">In progress</option>
                            <option value="completed">Completed</option>
                            <option value="partial">Partial (Auto-refund remaining)</option>
                            <option value="canceled">Canceled (Full refund to balance)</option>
                            <option value="fail">Fail</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">Start Count</label>
                            <input type="number" name="start_count" :value="selectedOrder?.start_counter || 0"
                                   class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs">
                        </div>
                        <div>
                            <label class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">Remains Count</label>
                            <input type="number" name="remains" :value="selectedOrder?.remains || 0"
                                   class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs">
                        </div>
                    </div>

                    <p class="text-[11px] text-slate-400">Selecting 'Partial' or 'Canceled' triggers atomic wallet refund transactions automatically.</p>

                    <div class="pt-2 flex justify-end space-x-2">
                        <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
