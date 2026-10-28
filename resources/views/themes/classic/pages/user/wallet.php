<?php
declare(strict_types=1);

use App\Core\Csrf;
use App\Helpers\Format;
use App\Helpers\Security;
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8"
     x-data="{
         methods: <?= htmlspecialchars(json_encode($paymentMethods), ENT_QUOTES, 'UTF-8') ?>,
         selectedMethodId: '<?= !empty($paymentMethods) ? (int)$paymentMethods[0]['id'] : '' ?>',
         amount: 25.00,
         get currentMethod() {
             return this.methods.find(m => m.id == this.selectedMethodId) || null;
         },
         get feeAmount() {
             if (!this.currentMethod) return 0;
             let fee = parseFloat(this.currentMethod.fee_percentage) || 0;
             return ((this.amount * fee) / 100);
         },
         get bonusAmount() {
             if (!this.currentMethod) return 0;
             let bonus = parseFloat(this.currentMethod.bonus_percentage) || 0;
             return ((this.amount * bonus) / 100);
         },
         get totalPayable() {
             return (parseFloat(this.amount || 0) + this.feeAmount).toFixed(2);
         },
         get creditedAmount() {
             return (parseFloat(this.amount || 0) + this.bonusAmount).toFixed(2);
         }
     }">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Deposit Funds & Wallet</h1>
            <p class="text-xs text-slate-400 mt-1">Add funds securely via automated gateways or bank transfers</p>
        </div>
        <div class="flex items-center space-x-2 px-4 py-2 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-300">
            <span class="text-xs uppercase font-semibold text-indigo-400">Current Balance:</span>
            <span class="text-lg font-black text-white"><?= Format::currency((float)($user['balance'] ?? 0.00)) ?></span>
        </div>
    </div>

    <!-- Deposit Form & Coupon Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Deposit Form (2 Cols) -->
        <div class="lg:col-span-2 rounded-2xl bg-slate-900 border border-slate-800 p-6 sm:p-8 space-y-6">
            <h2 class="text-base font-bold text-white uppercase tracking-wider pb-3 border-b border-slate-800">Add Balance</h2>

            <form action="/payment/initiate" method="POST" class="space-y-5">
                <?= Csrf::field() ?>

                <!-- Payment Method Select -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Select Payment Method</label>
                    <select name="payment_method_id" x-model="selectedMethodId" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <template x-for="m in methods" :key="m.id">
                            <option :value="m.id" x-text="m.name + (parseFloat(m.bonus_percentage) > 0 ? ' (+' + m.bonus_percentage + '% Bonus)' : '')"></option>
                        </template>
                    </select>
                </div>

                <!-- Amount Input -->
                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label for="amount" class="text-xs font-semibold uppercase tracking-wider text-slate-300">Deposit Amount (USD)</label>
                        <span class="text-xs text-slate-500" x-show="currentMethod">
                            Min: $<span x-text="currentMethod?.min_deposit"></span> | 
                            Max: $<span x-text="Number(currentMethod?.max_deposit).toLocaleString()"></span>
                        </span>
                    </div>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-slate-500 text-sm font-semibold">$</span>
                        <input type="number" id="amount" name="amount" x-model="amount" step="1"
                               :min="currentMethod?.min_deposit || 5"
                               :max="currentMethod?.max_deposit || 10000"
                               class="w-full pl-8 pr-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm font-semibold focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

                <!-- Calculation breakdown -->
                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2 text-xs">
                    <div class="flex justify-between text-slate-400">
                        <span>Gateway Fee:</span>
                        <span class="font-mono text-slate-300" x-text="'$' + feeAmount.toFixed(2) + ' (' + (currentMethod?.fee_percentage || 0) + '%)'"></span>
                    </div>
                    <div class="flex justify-between text-slate-400" x-show="bonusAmount > 0">
                        <span class="text-emerald-400">Promotional Bonus:</span>
                        <span class="font-mono text-emerald-400 font-bold" x-text="'+$' + bonusAmount.toFixed(2) + ' (' + (currentMethod?.bonus_percentage || 0) + '%)'"></span>
                    </div>
                    <div class="pt-2 border-t border-slate-800/80 flex justify-between font-bold text-sm">
                        <span class="text-white">Credited to Balance:</span>
                        <span class="text-emerald-400" x-text="'$' + creditedAmount"></span>
                    </div>
                    <div class="flex justify-between font-bold text-sm">
                        <span class="text-white">Total Amount to Pay:</span>
                        <span class="text-indigo-400" x-text="'$' + totalPayable"></span>
                    </div>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-lg shadow-indigo-600/30 transition">
                    Continue to Payment
                </button>
            </form>
        </div>

        <!-- Coupon Code Box & Policy (1 Col) -->
        <div class="space-y-6">
            <!-- Coupon Card -->
            <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 space-y-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center">
                    <svg class="w-4 h-4 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    Redeem Promo Coupon
                </h3>
                <p class="text-xs text-slate-400">Have a promotional coupon code? Enter it below for instant wallet balance credit.</p>

                <form action="/coupon/redeem" method="POST" class="space-y-3">
                    <?= Csrf::field() ?>
                    <div>
                        <input type="text" name="code" required placeholder="e.g. WELCOME10"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white uppercase font-mono text-sm placeholder-slate-600 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-indigo-300 font-semibold text-xs border border-slate-700 transition">
                        Redeem Coupon
                    </button>
                </form>
            </div>

            <!-- Deposit Security Notice -->
            <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 space-y-3 text-xs text-slate-400">
                <h4 class="font-bold text-slate-300 uppercase tracking-wider">Payment Information</h4>
                <ul class="list-disc list-inside space-y-1.5 leading-relaxed">
                    <li>Automated deposits are credited immediately after confirmation.</li>
                    <li>Cryptocurrency payments require 1-3 network confirmations.</li>
                    <li>All transactions are protected by atomic ledger accounting.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Wallet Transaction Ledger Table -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="p-6 border-b border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-white">Wallet Transaction Ledger</h3>
                <p class="text-xs text-slate-400 mt-0.5">Immutable audit trail of all debits, credits, and refunds</p>
            </div>
            <span class="text-xs text-slate-500"><?= count($transactions) ?> entries</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/70 uppercase text-[11px] text-slate-400 border-b border-slate-800 font-semibold tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">ID</th>
                        <th class="px-5 py-3.5">Type</th>
                        <th class="px-5 py-3.5">Amount</th>
                        <th class="px-5 py-3.5">Balance Before</th>
                        <th class="px-5 py-3.5">Balance After</th>
                        <th class="px-5 py-3.5">Description</th>
                        <th class="px-5 py-3.5">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-slate-500">
                                No wallet transactions recorded yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $t): ?>
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="px-5 py-3.5 font-mono text-slate-400">#<?= (int)$t['id'] ?></td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider <?= $t['type'] === 'credit' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' ?>">
                                        <?= Security::e($t['type']) ?>
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 font-bold <?= $t['type'] === 'credit' ? 'text-emerald-400' : 'text-rose-400' ?>">
                                    <?= $t['type'] === 'credit' ? '+' : '-' ?><?= Format::currency((float)$t['amount']) ?>
                                </td>
                                <td class="px-5 py-3.5 font-mono text-slate-400"><?= Format::currency((float)$t['balance_before']) ?></td>
                                <td class="px-5 py-3.5 font-mono font-bold text-white"><?= Format::currency((float)$t['balance_after']) ?></td>
                                <td class="px-5 py-3.5 text-slate-300"><?= Security::e($t['description'] ?? 'Transaction') ?></td>
                                <td class="px-5 py-3.5 text-slate-400 whitespace-nowrap"><?= Format::date($t['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
