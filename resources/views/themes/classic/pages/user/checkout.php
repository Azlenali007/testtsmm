<?php
declare(strict_types=1);

use App\Core\Csrf;
use App\Helpers\Format;
use App\Helpers\Security;
?>
<div class="max-w-2xl mx-auto px-4 sm:px-6 py-12 space-y-6">
    <div class="text-center">
        <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
        <h1 class="text-2xl font-extrabold text-white tracking-tight">Complete Deposit</h1>
        <p class="text-xs text-slate-400 mt-1 font-mono">Transaction ID: <?= Security::e($payment['transaction_code']) ?></p>
    </div>

    <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 sm:p-8 space-y-6 shadow-2xl">
        <!-- Summary Box -->
        <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2.5 text-xs">
            <div class="flex justify-between items-center">
                <span class="text-slate-400">Payment Gateway:</span>
                <span class="font-bold text-white"><?= Security::e($payment['method_name']) ?></span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-400">Amount to Deposit:</span>
                <span class="font-bold text-white"><?= Format::currency((float)$payment['amount']) ?></span>
            </div>
            <?php if ((float)$payment['fee'] > 0): ?>
                <div class="flex justify-between items-center">
                    <span class="text-slate-400">Processing Fee:</span>
                    <span class="font-mono text-slate-400">+<?= Format::currency((float)$payment['fee']) ?></span>
                </div>
            <?php endif; ?>
            <div class="pt-2 border-t border-slate-800 flex justify-between items-center text-sm">
                <span class="font-bold text-white">Total Amount Due:</span>
                <span class="font-black text-indigo-400"><?= Format::currency((float)$payment['amount'] + (float)$payment['fee']) ?></span>
            </div>
            <div class="flex justify-between items-center pt-1">
                <span class="text-slate-400">Current Status:</span>
                <div><?= Format::badge($payment['status']) ?></div>
            </div>
        </div>

        <?php if ($payment['method_code'] === 'bank_transfer'): ?>
            <!-- Manual Bank Transfer Instructions -->
            <div class="space-y-4">
                <div class="p-4 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-xs text-indigo-300 space-y-2">
                    <h3 class="font-bold text-white uppercase tracking-wider">Bank Wire Details:</h3>
                    <p class="leading-relaxed">
                        Bank Name: <strong>Apex Global FinCorp</strong><br>
                        Account Name: <strong>Apex SMM Technologies Ltd</strong><br>
                        IBAN / Account #: <strong>US89 APEX 0001 2345 6789 01</strong><br>
                        SWIFT / BIC: <strong>APEXUS33XXX</strong><br>
                        Payment Reference: <strong class="text-white font-mono"><?= Security::e($payment['transaction_code']) ?></strong>
                    </p>
                </div>

                <form action="/payment/submit-proof" method="POST" class="space-y-4">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="transaction_code" value="<?= Security::e($payment['transaction_code']) ?>">

                    <div>
                        <label for="proof_details" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Transfer Reference / Receipt Info</label>
                        <textarea id="proof_details" name="proof_details" rows="3" required
                                  placeholder="Enter bank transaction reference number, sender name, or payment notes..."
                                  class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                    </div>

                    <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs transition">
                        Submit Transfer Confirmation
                    </button>
                </form>
            </div>
        <?php else: ?>
            <!-- Automated Gateway Simulated Checkout -->
            <div class="space-y-4 text-center">
                <p class="text-xs text-slate-400">
                    You are paying securely via <strong class="text-white"><?= Security::e($payment['method_name']) ?></strong>.
                </p>

                <!-- Simulated instant test payment button for demo purposes -->
                <form action="/payment/simulate-success" method="POST">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="transaction_code" value="<?= Security::e($payment['transaction_code']) ?>">
                    <button type="submit" class="w-full py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm shadow-lg shadow-emerald-600/30 transition">
                        Authorize & Pay <?= Format::currency((float)$payment['amount'] + (float)$payment['fee']) ?>
                    </button>
                </form>

                <p class="text-[11px] text-slate-500">
                    Simulates instant webhook confirmation and credits your wallet atomically.
                </p>
            </div>
        <?php endif; ?>

        <div class="pt-3 border-t border-slate-800 text-center">
            <a href="/wallet" class="text-xs text-slate-400 hover:text-white font-semibold">&larr; Return to Wallet</a>
        </div>
    </div>
</div>
