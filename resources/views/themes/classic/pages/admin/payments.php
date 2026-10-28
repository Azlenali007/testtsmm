<?php
declare(strict_types=1);

use App\Core\Csrf;
use App\Helpers\Format;
use App\Helpers\Security;
?>
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Deposit Transactions</h1>
            <p class="text-xs text-slate-400 mt-1">Audit customer payment attempts and manually verify bank wire transfers</p>
        </div>
        <div class="flex space-x-2">
            <a href="/admin/payments/settings" class="px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-800 hover:bg-slate-800 text-slate-300 hover:text-white text-xs font-semibold transition">
                Gateway Configuration &rarr;
            </a>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="flex items-center space-x-1">
        <?php foreach (['all' => 'All', 'pending' => 'Pending Verification', 'completed' => 'Completed', 'failed' => 'Failed'] as $k => $l): ?>
            <a href="/admin/payments?status=<?= $k ?>"
               class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition <?= $currentStatus === $k ? 'bg-indigo-600 text-white' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white' ?>">
                <?= $l ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Payments Table -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/70 uppercase text-[11px] text-slate-400 border-b border-slate-800 font-semibold tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">ID</th>
                        <th class="px-5 py-3.5">Code</th>
                        <th class="px-5 py-3.5">User</th>
                        <th class="px-5 py-3.5">Method</th>
                        <th class="px-5 py-3.5">Amount</th>
                        <th class="px-5 py-3.5">Fee</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Details / Proof</th>
                        <th class="px-5 py-3.5">Date</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    <?php if (empty($payments)): ?>
                        <tr><td colspan="10" class="px-6 py-8 text-center text-slate-500">No payment records found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($payments as $p): ?>
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="px-5 py-3.5 font-mono font-bold text-indigo-400">#<?= (int)$p['id'] ?></td>
                                <td class="px-5 py-3.5 font-mono text-[11px] text-slate-300 font-bold"><?= Security::e($p['transaction_code']) ?></td>
                                <td class="px-5 py-3.5 font-semibold text-white"><?= Security::e($p['username']) ?></td>
                                <td class="px-5 py-3.5 text-slate-300"><?= Security::e($p['method_name']) ?></td>
                                <td class="px-5 py-3.5 font-bold text-emerald-400"><?= Format::currency((float)$p['amount']) ?></td>
                                <td class="px-5 py-3.5 font-mono text-slate-400"><?= Format::currency((float)$p['fee']) ?></td>
                                <td class="px-5 py-3.5"><?= Format::badge($p['status']) ?></td>
                                <td class="px-5 py-3.5 text-slate-400 max-w-xs truncate" title="<?= Security::e($p['gateway_response'] ?? '') ?>">
                                    <?= Security::e($p['gateway_response'] ?: 'None') ?>
                                </td>
                                <td class="px-5 py-3.5 text-slate-400 whitespace-nowrap"><?= Format::date($p['created_at']) ?></td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-1">
                                    <?php if ($p['status'] === 'pending'): ?>
                                        <form action="/admin/payments/approve/<?= (int)$p['id'] ?>" method="POST" class="inline" onsubmit="return confirm('Approve this deposit and credit user balance?');">
                                            <?= Csrf::field() ?>
                                            <button type="submit" class="px-2.5 py-1 rounded bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 font-semibold transition">
                                                Approve
                                            </button>
                                        </form>
                                        <form action="/admin/payments/reject/<?= (int)$p['id'] ?>" method="POST" class="inline" onsubmit="return confirm('Reject this deposit?');">
                                            <?= Csrf::field() ?>
                                            <button type="submit" class="px-2.5 py-1 rounded bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 font-semibold transition">
                                                Reject
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-slate-600">—</span>
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
                        <a href="/admin/payments?page=<?= $currentPage - 1 ?>&status=<?= urlencode($currentStatus) ?>"
                           class="px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-xs font-medium text-slate-300 hover:text-white">
                            Previous
                        </a>
                    <?php endif; ?>
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="/admin/payments?page=<?= $currentPage + 1 ?>&status=<?= urlencode($currentStatus) ?>"
                           class="px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-xs font-medium text-slate-300 hover:text-white">
                            Next
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
