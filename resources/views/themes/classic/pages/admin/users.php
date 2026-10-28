<?php
declare(strict_types=1);

use App\Core\Csrf;
use App\Helpers\Format;
use App\Helpers\Security;
?>
<div class="space-y-6"
     x-data="{
         adjustModal: false,
         targetUserId: null,
         targetUsername: '',
         openAdjust(id, username) {
             this.targetUserId = id;
             this.targetUsername = username;
             this.adjustModal = true;
         }
     }">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Users Management</h1>
            <p class="text-xs text-slate-400 mt-1">Manage client profiles, custom discount rates, and manual balance credits</p>
        </div>
        <div class="text-xs text-slate-400">
            Total Users: <span class="font-bold text-white"><?= number_format($totalUsers) ?></span>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <!-- Status Tabs -->
        <div class="flex items-center space-x-1">
            <?php foreach (['all' => 'All', 'active' => 'Active', 'suspended' => 'Suspended', 'pending' => 'Pending'] as $k => $l): ?>
                <a href="/admin/users?status=<?= $k ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition <?= $status === $k ? 'bg-indigo-600 text-white' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white' ?>">
                    <?= $l ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Search Box -->
        <form method="GET" action="/admin/users" class="relative max-w-xs w-full">
            <input type="hidden" name="status" value="<?= Security::e($status) ?>">
            <input type="text" name="search" value="<?= Security::e($search) ?>" placeholder="Search username, email, phone..."
                   class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <svg class="w-4 h-4 text-slate-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </form>
    </div>

    <!-- Users Table -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/70 uppercase text-[11px] text-slate-400 border-b border-slate-800 font-semibold tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">ID</th>
                        <th class="px-5 py-3.5">Username</th>
                        <th class="px-5 py-3.5">Email</th>
                        <th class="px-5 py-3.5">Balance</th>
                        <th class="px-5 py-3.5">Discount %</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Registered</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    <?php if (empty($users)): ?>
                        <tr><td colspan="8" class="px-6 py-8 text-center text-slate-500">No users found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="px-5 py-3.5 font-mono font-bold text-indigo-400">#<?= (int)$u['id'] ?></td>
                                <td class="px-5 py-3.5 font-semibold text-white">
                                    <a href="/admin/users/edit/<?= (int)$u['id'] ?>" class="hover:text-indigo-300">
                                        <?= Security::e($u['username']) ?>
                                    </a>
                                </td>
                                <td class="px-5 py-3.5 text-slate-400"><?= Security::e($u['email']) ?></td>
                                <td class="px-5 py-3.5 font-bold text-white"><?= Format::currency((float)$u['balance']) ?></td>
                                <td class="px-5 py-3.5 font-mono text-indigo-300"><?= (float)($u['custom_rate'] ?? 0) ?>%</td>
                                <td class="px-5 py-3.5"><?= Format::badge($u['status']) ?></td>
                                <td class="px-5 py-3.5 text-slate-400 whitespace-nowrap"><?= Format::date($u['created_at']) ?></td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-1.5">
                                    <button type="button" @click="openAdjust(<?= (int)$u['id'] ?>, '<?= Security::e($u['username']) ?>')"
                                            class="px-2.5 py-1 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 font-semibold transition">
                                        &plusmn; Balance
                                    </button>
                                    <a href="/admin/users/edit/<?= (int)$u['id'] ?>"
                                       class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 font-semibold transition">
                                        Edit
                                    </a>
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
                        <a href="/admin/users?page=<?= $currentPage - 1 ?>&status=<?= urlencode($status) ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                           class="px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-xs font-medium text-slate-300 hover:text-white">
                            Previous
                        </a>
                    <?php endif; ?>
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="/admin/users?page=<?= $currentPage + 1 ?>&status=<?= urlencode($status) ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                           class="px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-xs font-medium text-slate-300 hover:text-white">
                            Next
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Balance Adjustment Modal -->
    <div x-show="adjustModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="adjustModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="adjustModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="adjustModal" x-transition.scale class="inline-block align-bottom bg-slate-900 border border-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <h3 class="text-base font-bold text-white">Adjust Balance for <span class="text-indigo-400" x-text="targetUsername"></span></h3>
                    <button @click="adjustModal = false" class="text-slate-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="/admin/users/adjust-balance" method="POST" class="space-y-4">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="user_id" :value="targetUserId">

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Adjustment Action</label>
                        <select name="action" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <option value="add">Add Balance (+) — Credit User</option>
                            <option value="deduct">Deduct Balance (-) — Debit User</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Amount ($ USD)</label>
                        <input type="number" name="amount" step="0.01" min="0.01" required placeholder="50.00"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Audit Reason / Description</label>
                        <input type="text" name="reason" required placeholder="e.g. Manual bank wire approval / Compensation"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>

                    <div class="pt-2 flex justify-end space-x-2">
                        <button type="button" @click="adjustModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-xs font-semibold text-white">Execute Adjustment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
