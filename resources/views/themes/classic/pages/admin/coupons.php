<?php
declare(strict_types=1);

use App\Core\Csrf;
use App\Helpers\Format;
use App\Helpers\Security;
?>
<div class="space-y-8" x-data="{ createModal: false }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Coupons & Promo Codes</h1>
            <p class="text-xs text-slate-400 mt-1">Generate promotional balance credits and track redemption usage</p>
        </div>
        <button type="button" @click="createModal = true"
                class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-lg shadow-indigo-600/30 transition">
            + Create Coupon
        </button>
    </div>

    <!-- Active Coupons Table -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="p-6 border-b border-slate-800">
            <h3 class="text-base font-bold text-white">Coupons Catalog</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/70 uppercase text-[11px] text-slate-400 border-b border-slate-800 font-semibold tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">ID</th>
                        <th class="px-5 py-3.5">Code</th>
                        <th class="px-5 py-3.5">Type</th>
                        <th class="px-5 py-3.5">Credit Value</th>
                        <th class="px-5 py-3.5">Usage / Max</th>
                        <th class="px-5 py-3.5">Expiry Date</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    <?php if (empty($coupons)): ?>
                        <tr><td colspan="8" class="px-6 py-8 text-center text-slate-500">No promo coupons configured yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($coupons as $c): ?>
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="px-5 py-4 font-mono font-bold text-indigo-400">#<?= (int)$c['id'] ?></td>
                                <td class="px-5 py-4 font-mono font-bold text-white text-sm tracking-wider"><?= Security::e($c['code']) ?></td>
                                <td class="px-5 py-4 capitalize text-slate-400"><?= Security::e($c['type']) ?></td>
                                <td class="px-5 py-4 font-bold text-emerald-400">
                                    <?= $c['type'] === 'percentage' ? (float)$c['value'] . '%' : Format::currency((float)$c['value']) ?>
                                </td>
                                <td class="px-5 py-4 font-mono text-slate-300">
                                    <?= (int)$c['used_count'] ?> / <?= (int)$c['max_uses'] ?>
                                </td>
                                <td class="px-5 py-4 text-slate-400"><?= $c['expiry_date'] ? Format::date($c['expiry_date']) : 'Never' ?></td>
                                <td class="px-5 py-4"><?= Format::badge($c['status']) ?></td>
                                <td class="px-5 py-4 text-right">
                                    <form action="/admin/coupons/delete/<?= (int)$c['id'] ?>" method="POST" class="inline" onsubmit="return confirm('Delete this coupon?');">
                                        <?= Csrf::field() ?>
                                        <button type="submit" class="px-2.5 py-1 rounded bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 font-semibold transition">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Redemptions Table -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="p-6 border-b border-slate-800">
            <h3 class="text-base font-bold text-white">Recent Redemptions</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/70 uppercase text-[11px] text-slate-400 border-b border-slate-800 font-semibold tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">ID</th>
                        <th class="px-5 py-3.5">User</th>
                        <th class="px-5 py-3.5">Coupon Code</th>
                        <th class="px-5 py-3.5">Credited Amount</th>
                        <th class="px-5 py-3.5 text-right">Date Redeemed</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    <?php if (empty($usages)): ?>
                        <tr><td colspan="5" class="px-6 py-6 text-center text-slate-500">No redemptions recorded yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($usages as $u): ?>
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="px-5 py-3 font-mono text-slate-400">#<?= (int)$u['id'] ?></td>
                                <td class="px-5 py-3 font-semibold text-white"><?= Security::e($u['username']) ?></td>
                                <td class="px-5 py-3 font-mono text-indigo-400 font-bold"><?= Security::e($u['code']) ?></td>
                                <td class="px-5 py-3 font-bold text-emerald-400">+<?= Format::currency((float)$u['amount_credited']) ?></td>
                                <td class="px-5 py-3 text-right text-slate-400"><?= Format::date($u['redeemed_at'] ?? $u['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Coupon Modal -->
    <div x-show="createModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="createModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="createModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="createModal" x-transition.scale class="inline-block align-bottom bg-slate-900 border border-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <h3 class="text-base font-bold text-white">Create New Coupon</h3>
                    <button @click="createModal = false" class="text-slate-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="/admin/coupons/store" method="POST" class="space-y-4">
                    <?= Csrf::field() ?>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Coupon Code</label>
                        <input type="text" name="code" required placeholder="e.g. SUMMER25"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono uppercase text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Type</label>
                            <select name="type" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs">
                                <option value="fixed">Fixed Balance ($)</option>
                                <option value="percentage">Percentage (%)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Value</label>
                            <input type="number" step="0.01" name="value" required placeholder="10.00"
                                   class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Max Usages</label>
                            <input type="number" name="max_uses" value="100" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Expiry Date</label>
                            <input type="date" name="expiry_date" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs">
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end space-x-2">
                        <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white">Save Coupon</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
