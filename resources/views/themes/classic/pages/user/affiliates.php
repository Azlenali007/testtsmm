<?php
declare(strict_types=1);

use App\Core\Csrf;
use App\Helpers\Format;
use App\Helpers\Security;
?>
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 space-y-8"
     x-data="{
         link: '<?= Security::e($referralLink) ?>',
         copied: false,
         payoutModal: false,
         copyLink() {
             navigator.clipboard.writeText(this.link);
             this.copied = true;
             setTimeout(() => { this.copied = false; }, 2000);
         }
     }">

    <div class="pb-4 border-b border-slate-800">
        <h1 class="text-2xl font-extrabold text-white tracking-tight">Affiliate Program</h1>
        <p class="text-xs text-slate-400 mt-1">Earn <?= (float)$commissionRate ?>% lifetime recurring commission on all deposits made by users you refer</p>
    </div>

    <!-- Referral Link Box -->
    <div class="rounded-2xl bg-gradient-to-r from-indigo-900/40 via-slate-900 to-indigo-950/40 border border-indigo-500/20 p-6 sm:p-8 space-y-4">
        <span class="text-xs font-semibold text-indigo-400 uppercase tracking-wider block">Your Unique Referral Link</span>
        
        <div class="flex flex-col sm:flex-row items-center gap-3">
            <input type="text" readonly :value="link"
                   class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono text-sm focus:outline-none">
            
            <button type="button" @click="copyLink()"
                    class="w-full sm:w-auto px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-lg shadow-indigo-600/30 transition flex items-center justify-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                <span x-text="copied ? 'Copied Link!' : 'Copy Link'">Copy Link</span>
            </button>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="rounded-2xl bg-slate-900 border border-slate-800 p-5">
            <span class="text-xs uppercase font-semibold text-slate-400">Total Referred Users</span>
            <div class="text-2xl font-black text-white mt-1"><?= number_format((int)($stats['total_referred'] ?? 0)) ?></div>
            <span class="text-[11px] text-slate-500 mt-1 block">Active registrations</span>
        </div>

        <div class="rounded-2xl bg-slate-900 border border-slate-800 p-5">
            <span class="text-xs uppercase font-semibold text-slate-400">Total Commission Earned</span>
            <div class="text-2xl font-black text-emerald-400 mt-1"><?= Format::currency((float)($stats['total_earnings'] ?? 0.00)) ?></div>
            <span class="text-[11px] text-slate-500 mt-1 block">From successful user deposits</span>
        </div>

        <div class="rounded-2xl bg-slate-900 border border-slate-800 p-5 flex flex-col justify-between">
            <div>
                <span class="text-xs uppercase font-semibold text-slate-400">Minimum Payout</span>
                <div class="text-2xl font-black text-indigo-400 mt-1"><?= Format::currency((float)$minPayout) ?></div>
            </div>
            <button type="button" @click="payoutModal = true" class="mt-3 px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-indigo-300 font-semibold text-xs border border-slate-700 transition">
                Request Payout &rarr;
            </button>
        </div>
    </div>

    <!-- Payout Request Modal -->
    <div x-show="payoutModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="payoutModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity" @click="payoutModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="payoutModal" x-transition.scale class="inline-block align-bottom bg-slate-900 border border-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <h3 class="text-base font-bold text-white">Request Affiliate Payout</h3>
                    <button @click="payoutModal = false" class="text-slate-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="/affiliates/payout" method="POST" class="space-y-4">
                    <?= Csrf::field() ?>

                    <div>
                        <label for="payout_amount" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Amount (USD)</label>
                        <input type="number" id="payout_amount" name="amount" min="<?= (float)$minPayout ?>" step="1" required
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="payment_details" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Payment Method & Account Details</label>
                        <textarea id="payment_details" name="payment_details" rows="3" required placeholder="PayPal email, USDT TRC20 address, or Bank account..."
                                  class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                    </div>

                    <div class="pt-2 flex justify-end space-x-2">
                        <button type="button" @click="payoutModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Payout History -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="p-6 border-b border-slate-800">
            <h3 class="text-base font-bold text-white">Payout Requests</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/70 uppercase text-[11px] text-slate-400 border-b border-slate-800 font-semibold tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">ID</th>
                        <th class="px-6 py-3.5">Amount</th>
                        <th class="px-6 py-3.5">Payment Details</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Requested On</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    <?php if (empty($payouts)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                No payout requests yet. Share your referral link above to start earning!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payouts as $p): ?>
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="px-6 py-4 font-mono font-bold text-indigo-400">#<?= (int)$p['id'] ?></td>
                                <td class="px-6 py-4 font-bold text-white"><?= Format::currency((float)$p['amount']) ?></td>
                                <td class="px-6 py-4 text-slate-300 max-w-xs truncate"><?= Security::e($p['payment_details']) ?></td>
                                <td class="px-6 py-4"><?= Format::badge($p['status']) ?></td>
                                <td class="px-6 py-4 text-slate-400"><?= Format::date($p['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
