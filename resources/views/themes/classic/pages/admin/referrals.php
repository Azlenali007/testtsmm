<?php
declare(strict_types=1);

use App\Core\Csrf;
use App\Helpers\Format;
use App\Helpers\Security;
?>
<div class="space-y-8">
    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Affiliates & Payouts</h1>
            <p class="text-xs text-slate-400 mt-1">Review referral earnings and approve commission payouts</p>
        </div>
    </div>

    <!-- Payout Requests Table -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="p-6 border-b border-slate-800">
            <h3 class="text-base font-bold text-white">Pending & Processed Payout Requests</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/70 uppercase text-[11px] text-slate-400 border-b border-slate-800 font-semibold tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">ID</th>
                        <th class="px-5 py-3.5">User</th>
                        <th class="px-5 py-3.5">Amount</th>
                        <th class="px-5 py-3.5">Payment Details</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Date</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    <?php if (empty($payouts)): ?>
                        <tr><td colspan="7" class="px-6 py-8 text-center text-slate-500">No payout requests found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($payouts as $p): ?>
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="px-5 py-4 font-mono font-bold text-indigo-400">#<?= (int)$p['id'] ?></td>
                                <td class="px-5 py-4 font-semibold text-white"><?= Security::e($p['username']) ?></td>
                                <td class="px-5 py-4 font-bold text-emerald-400"><?= Format::currency((float)$p['amount']) ?></td>
                                <td class="px-5 py-4 text-slate-300 max-w-xs truncate"><?= Security::e($p['payment_details']) ?></td>
                                <td class="px-5 py-4"><?= Format::badge($p['status']) ?></td>
                                <td class="px-5 py-4 text-slate-400 whitespace-nowrap"><?= Format::date($p['created_at']) ?></td>
                                <td class="px-5 py-4 text-right whitespace-nowrap space-x-1.5">
                                    <?php if ($p['status'] === 'pending'): ?>
                                        <form action="/admin/referrals/approve/<?= (int)$p['id'] ?>" method="POST" class="inline" onsubmit="return confirm('Mark this payout as approved/paid?');">
                                            <?= Csrf::field() ?>
                                            <button type="submit" class="px-2.5 py-1 rounded bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 font-semibold transition">
                                                Approve
                                            </button>
                                        </form>
                                        <form action="/admin/referrals/reject/<?= (int)$p['id'] ?>" method="POST" class="inline" onsubmit="return confirm('Reject this payout request?');">
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
    </div>

    <!-- Referral Connections Table -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="p-6 border-b border-slate-800">
            <h3 class="text-base font-bold text-white">Active Referral Links & Commission Generated</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/70 uppercase text-[11px] text-slate-400 border-b border-slate-800 font-semibold tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">ID</th>
                        <th class="px-5 py-3.5">Referrer</th>
                        <th class="px-5 py-3.5">Referred Client</th>
                        <th class="px-5 py-3.5">Total Commission Earned</th>
                        <th class="px-5 py-3.5 text-right">Referral Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    <?php if (empty($referrals)): ?>
                        <tr><td colspan="5" class="px-6 py-6 text-center text-slate-500">No referral relationships active yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($referrals as $r): ?>
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="px-5 py-3 font-mono text-slate-400">#<?= (int)$r['id'] ?></td>
                                <td class="px-5 py-3 font-semibold text-white"><?= Security::e($r['referrer_name']) ?></td>
                                <td class="px-5 py-3 text-slate-300"><?= Security::e($r['referred_name']) ?></td>
                                <td class="px-5 py-3 font-bold text-emerald-400"><?= Format::currency((float)$r['total_earned']) ?></td>
                                <td class="px-5 py-3 text-right text-slate-400"><?= Format::date($r['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
