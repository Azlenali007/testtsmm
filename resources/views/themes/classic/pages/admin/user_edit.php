<?php
declare(strict_types=1);

use App\Core\Csrf;
use App\Helpers\Format;
use App\Helpers\Security;
?>
<div class="max-w-5xl mx-auto space-y-8">
    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-mono text-indigo-400 font-bold">Client #<?= (int)$targetUser['id'] ?></span>
                <span class="text-slate-600">&bull;</span>
                <span class="text-xs text-slate-400">Joined <?= Format::date($targetUser['created_at']) ?></span>
            </div>
            <h1 class="text-2xl font-black text-white mt-1">Edit User: <?= Security::e($targetUser['username']) ?></h1>
        </div>

        <a href="/admin/users" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs font-semibold text-slate-300 hover:text-white">
            &larr; Back to Users
        </a>
    </div>

    <!-- Edit Form Card -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 sm:p-8 space-y-6">
        <form action="/admin/users/update/<?= (int)$targetUser['id'] ?>" method="POST" class="space-y-5">
            <?= Csrf::field() ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Full Name</label>
                    <input type="text" name="name" value="<?= Security::e($targetUser['name'] ?? '') ?>"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Email Address</label>
                    <input type="email" name="email" required value="<?= Security::e($targetUser['email']) ?>"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Phone</label>
                    <input type="text" name="phone" value="<?= Security::e($targetUser['phone'] ?? '') ?>"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Account Status</label>
                    <select name="status" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="active" <?= $targetUser['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="suspended" <?= $targetUser['status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                        <option value="pending" <?= $targetUser['status'] === 'pending' ? 'selected' : '' ?>>Pending Verification</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Custom Discount (%)</label>
                    <input type="number" step="0.5" min="0" max="90" name="custom_rate" value="<?= (float)($targetUser['custom_rate'] ?? 0) ?>"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-800 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-lg shadow-indigo-600/30 transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Recent Transactions Table -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="p-6 border-b border-slate-800">
            <h3 class="text-base font-bold text-white">Recent Transactions for <?= Security::e($targetUser['username']) ?></h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/70 uppercase text-[11px] text-slate-400 border-b border-slate-800 font-semibold tracking-wider">
                    <tr>
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">Type</th>
                        <th class="px-5 py-3">Amount</th>
                        <th class="px-5 py-3">Before</th>
                        <th class="px-5 py-3">After</th>
                        <th class="px-5 py-3">Description</th>
                        <th class="px-5 py-3 text-right">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    <?php if (empty($transactions)): ?>
                        <tr><td colspan="7" class="px-6 py-8 text-center text-slate-500">No transactions recorded for this user.</td></tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $t): ?>
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="px-5 py-3 font-mono text-slate-400">#<?= (int)$t['id'] ?></td>
                                <td class="px-5 py-3 font-bold <?= $t['type'] === 'credit' ? 'text-emerald-400' : 'text-rose-400' ?>">
                                    <?= strtoupper($t['type']) ?>
                                </td>
                                <td class="px-5 py-3 font-bold text-white"><?= Format::currency((float)$t['amount']) ?></td>
                                <td class="px-5 py-3 font-mono text-slate-400"><?= Format::currency((float)$t['balance_before']) ?></td>
                                <td class="px-5 py-3 font-mono font-bold text-white"><?= Format::currency((float)$t['balance_after']) ?></td>
                                <td class="px-5 py-3 text-slate-300"><?= Security::e($t['description']) ?></td>
                                <td class="px-5 py-3 text-right text-slate-400"><?= Format::date($t['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
