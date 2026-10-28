<?php
declare(strict_types=1);

use App\Core\Csrf;
use App\Helpers\Security;
?>
<div class="space-y-8">
    <div class="pb-4 border-b border-slate-800">
        <h1 class="text-2xl font-black text-white tracking-tight">System Settings & Theme Manager</h1>
        <p class="text-xs text-slate-400 mt-1">Configure global platform branding, active frontend theme, reseller pricing, and legal documents</p>
    </div>

    <form action="/admin/settings/update" method="POST" class="space-y-8">
        <?= Csrf::field() ?>

        <!-- Theme Selection Section -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 sm:p-8 space-y-4">
            <div class="pb-3 border-b border-slate-800">
                <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center">
                    <svg class="w-4 h-4 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                    Frontend Theme Engine
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Switch themes seamlessly. The underlying architecture and database remain completely decoupled.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <?php foreach ($installedThemes as $tKey => $tInfo): ?>
                    <label class="rounded-xl border p-4 flex flex-col justify-between cursor-pointer transition <?= $activeTheme === $tKey ? 'bg-indigo-950/30 border-indigo-500 shadow-md shadow-indigo-500/10' : 'bg-slate-950/60 border-slate-800 hover:border-slate-700' ?>">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-white text-sm"><?= Security::e($tInfo['name']) ?></span>
                                <input type="radio" name="active_theme" value="<?= Security::e($tKey) ?>" <?= $activeTheme === $tKey ? 'checked' : '' ?> class="text-indigo-600 focus:ring-indigo-500">
                            </div>
                            <span class="text-xs text-slate-400 block font-mono text-[11px]"><?= Security::e($tKey) ?></span>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-center justify-between">
                            <span class="text-[10px] text-slate-500 font-mono">Tailwind + Alpine</span>
                            <?php if ($activeTheme === $tKey): ?>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">Active Theme</span>
                            <?php endif; ?>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- General Branding & Configuration -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 sm:p-8 space-y-6">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider pb-3 border-b border-slate-800">General Branding & SEO</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">Site Name</label>
                    <input type="text" name="site_name" value="<?= Security::e($settings['site_name'] ?? 'ApexSMM') ?>" required
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div>
                    <label class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">Site Header Title</label>
                    <input type="text" name="site_title" value="<?= Security::e($settings['site_title'] ?? 'The #1 Social Media Marketing Provider') ?>" required
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">Currency Code</label>
                    <input type="text" name="site_currency" value="<?= Security::e($settings['site_currency'] ?? 'USD') ?>" required
                           class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs">
                </div>
                <div>
                    <label class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">Currency Symbol</label>
                    <input type="text" name="currency_symbol" value="<?= Security::e($settings['currency_symbol'] ?? '$') ?>" required
                           class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs">
                </div>
                <div>
                    <label class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">Support / Contact Email</label>
                    <input type="email" name="contact_email" value="<?= Security::e($settings['contact_email'] ?? 'support@apexsmm.com') ?>" required
                           class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs">
                </div>
            </div>

            <div class="text-xs">
                <label class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">Meta Description</label>
                <textarea name="site_description" rows="2"
                          class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs"><?= Security::e($settings['site_description'] ?? '') ?></textarea>
            </div>

            <!-- Toggles -->
            <div class="pt-2 border-t border-slate-800 flex items-center space-x-8 text-xs">
                <label class="flex items-center space-x-2.5 cursor-pointer">
                    <input type="checkbox" name="register_enabled" value="1" <?= (!isset($settings['register_enabled']) || $settings['register_enabled'] == '1') ? 'checked' : '' ?>
                           class="rounded bg-slate-950 border-slate-800 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-slate-300 font-semibold">Enable New User Registrations</span>
                </label>

                <label class="flex items-center space-x-2.5 cursor-pointer">
                    <input type="checkbox" name="maintenance_mode" value="1" <?= (isset($settings['maintenance_mode']) && $settings['maintenance_mode'] == '1') ? 'checked' : '' ?>
                           class="rounded bg-slate-950 border-slate-800 text-amber-500 focus:ring-amber-500">
                    <span class="text-amber-400 font-semibold">Maintenance Mode (Staff Only)</span>
                </label>
            </div>
        </div>

        <!-- Reseller & Affiliate Parameters -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 sm:p-8 space-y-4">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider pb-3 border-b border-slate-800">Affiliate & Child Panel Parameters</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">Referral Commission Rate (%)</label>
                    <input type="number" step="0.5" name="referral_commission_rate" value="<?= (float)($settings['referral_commission_rate'] ?? 5.0) ?>"
                           class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs">
                </div>
                <div>
                    <label class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">Min Referral Payout ($)</label>
                    <input type="number" step="1" name="referral_min_payout" value="<?= (float)($settings['referral_min_payout'] ?? 20.0) ?>"
                           class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs">
                </div>
                <div>
                    <label class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">Child Panel Monthly Fee ($)</label>
                    <input type="number" step="1" name="child_panel_price" value="<?= (float)($settings['child_panel_price'] ?? 25.0) ?>"
                           class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs">
                </div>
            </div>
        </div>

        <!-- Legal Documents -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 sm:p-8 space-y-4">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider pb-3 border-b border-slate-800">Legal Pages (HTML)</h2>

            <div class="space-y-4 text-xs">
                <div>
                    <label class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">Terms of Service Content</label>
                    <textarea name="terms_content" rows="4" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono text-xs"><?= Security::e($settings['terms_content'] ?? '') ?></textarea>
                </div>

                <div>
                    <label class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">Privacy Policy Content</label>
                    <textarea name="privacy_content" rows="4" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono text-xs"><?= Security::e($settings['privacy_content'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-8 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-lg shadow-indigo-600/30 transition">
                Save System Settings
            </button>
        </div>
    </form>
</div>
