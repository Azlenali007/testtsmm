<?php
declare(strict_types=1);

use App\Core\Csrf;
use App\Helpers\Security;
?>
<div class="space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Payment Gateways Configuration</h1>
            <p class="text-xs text-slate-400 mt-1">Configure automated credentials, fee structures, and promotional bonuses</p>
        </div>
        <a href="/admin/payments" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs font-semibold text-slate-300 hover:text-white">
            &larr; Back to Payments
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php foreach ($paymentMethods as $pm): ?>
            <?php $cfg = json_decode($pm['config_json'] ?? '{}', true) ?: []; ?>
            <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <div>
                        <h2 class="text-base font-bold text-white"><?= Security::e($pm['name']) ?></h2>
                        <span class="font-mono text-[11px] text-indigo-400"><?= Security::e($pm['code']) ?></span>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider <?= $pm['is_active'] ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-800 text-slate-500' ?>">
                        <?= $pm['is_active'] ? 'Enabled' : 'Disabled' ?>
                    </span>
                </div>

                <form action="/admin/payments/settings/update/<?= (int)$pm['id'] ?>" method="POST" class="space-y-3 text-xs">
                    <?= Csrf::field() ?>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold uppercase tracking-wider text-slate-400 mb-1">Min Deposit ($)</label>
                            <input type="number" step="1" name="min_amount" value="<?= (float)($pm['min_amount'] ?? $pm['min_deposit'] ?? 5) ?>"
                                   class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs">
                        </div>
                        <div>
                            <label class="block font-semibold uppercase tracking-wider text-slate-400 mb-1">Max Deposit ($)</label>
                            <input type="number" step="1" name="max_amount" value="<?= (float)($pm['max_amount'] ?? $pm['max_deposit'] ?? 5000) ?>"
                                   class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold uppercase tracking-wider text-slate-400 mb-1">Fee Percentage (%)</label>
                            <input type="number" step="0.1" name="fee_percentage" value="<?= (float)$pm['fee_percentage'] ?>"
                                   class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs">
                        </div>
                        <div>
                            <label class="block font-semibold uppercase tracking-wider text-slate-400 mb-1">Bonus Credit (%)</label>
                            <input type="number" step="0.1" name="bonus_percentage" value="<?= (float)$pm['bonus_percentage'] ?>"
                                   class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs">
                        </div>
                    </div>

                    <!-- Gateway Specific Fields -->
                    <div class="space-y-2 pt-2 border-t border-slate-800">
                        <?php if ($pm['code'] === 'stripe'): ?>
                            <div>
                                <label class="block text-slate-400 mb-0.5">Publishable Key</label>
                                <input type="text" name="config[public_key]" value="<?= Security::e($cfg['public_key'] ?? '') ?>" placeholder="pk_test_..."
                                       class="w-full px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs font-mono">
                            </div>
                            <div>
                                <label class="block text-slate-400 mb-0.5">Secret Key</label>
                                <input type="password" name="config[secret_key]" value="<?= Security::e($cfg['secret_key'] ?? '') ?>" placeholder="sk_test_..."
                                       class="w-full px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs font-mono">
                            </div>
                        <?php elseif ($pm['code'] === 'paypal'): ?>
                            <div>
                                <label class="block text-slate-400 mb-0.5">PayPal Client ID</label>
                                <input type="text" name="config[client_id]" value="<?= Security::e($cfg['client_id'] ?? '') ?>"
                                       class="w-full px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs font-mono">
                            </div>
                            <div>
                                <label class="block text-slate-400 mb-0.5">PayPal Secret</label>
                                <input type="password" name="config[secret]" value="<?= Security::e($cfg['secret'] ?? '') ?>"
                                       class="w-full px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs font-mono">
                            </div>
                        <?php else: ?>
                            <div>
                                <label class="block text-slate-400 mb-0.5">API Merchant / Public Key</label>
                                <input type="text" name="config[merchant_id]" value="<?= Security::e($cfg['merchant_id'] ?? '') ?>"
                                       class="w-full px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs font-mono">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="pt-2 flex items-center justify-between">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" <?= $pm['is_active'] ? 'checked' : '' ?> class="rounded bg-slate-950 border-slate-800 text-indigo-600">
                            <span class="text-slate-300 font-semibold">Enable Method</span>
                        </label>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs transition">
                            Save Config
                        </button>
                    </div>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>
