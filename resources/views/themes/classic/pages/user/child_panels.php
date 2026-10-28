<?php
declare(strict_types=1);

use App\Core\Csrf;
use App\Helpers\Format;
use App\Helpers\Security;
?>
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 space-y-8">
    <div class="pb-4 border-b border-slate-800">
        <h1 class="text-2xl font-extrabold text-white tracking-tight">Rent a Child Panel</h1>
        <p class="text-xs text-slate-400 mt-1">Start your own independent SMM business with your own domain and custom branding</p>
    </div>

    <!-- Features & DNS Setup Guide -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
            </div>
            <h3 class="text-sm font-bold text-white">Your Own Brand</h3>
            <p class="text-xs text-slate-400 mt-1.5 leading-relaxed">Runs under your own custom domain name with zero mention of ApexSMM.</p>
        </div>

        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
            <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </div>
            <h3 class="text-sm font-bold text-white">Automated Sync</h3>
            <p class="text-xs text-slate-400 mt-1.5 leading-relaxed">All services, descriptions, and order statuses sync directly from your master balance.</p>
        </div>

        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-sm font-bold text-white">Monthly Subscription</h3>
            <p class="text-xs text-slate-400 mt-1.5 leading-relaxed">Only <strong class="text-white"><?= Format::currency((float)$monthlyPrice) ?>/mo</strong> with automatic recurring renewal from your wallet.</p>
        </div>
    </div>

    <!-- Order Panel Form & DNS Instructions Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Form (2 cols) -->
        <div class="lg:col-span-2 rounded-2xl bg-slate-900 border border-slate-800 p-6 sm:p-8 space-y-6">
            <h2 class="text-base font-bold text-white uppercase tracking-wider pb-3 border-b border-slate-800">Order New Child Panel</h2>

            <form action="/child-panels/order" method="POST" class="space-y-4">
                <?= Csrf::field() ?>

                <div>
                    <label for="domain" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Your Domain Name</label>
                    <input type="text" id="domain" name="domain" required placeholder="e.g. mysmmpanel.com"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm placeholder-slate-600 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="admin_user" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Admin Username</label>
                        <input type="text" id="admin_user" name="admin_user" required placeholder="admin"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="admin_pass" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Admin Password</label>
                        <input type="password" id="admin_pass" name="admin_pass" required minlength="8" placeholder="••••••••"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Panel Currency</label>
                    <select name="currency" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="USD" selected>USD — United States Dollar ($)</option>
                        <option value="EUR">EUR — Euro (€)</option>
                        <option value="GBP">GBP — British Pound (£)</option>
                    </select>
                </div>

                <div class="pt-4 border-t border-slate-800 flex items-center justify-between">
                    <div>
                        <span class="text-xs text-slate-500 block">First Month Charge:</span>
                        <div class="text-xl font-black text-white"><?= Format::currency((float)$monthlyPrice) ?></div>
                    </div>

                    <button type="submit" class="px-8 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-lg shadow-indigo-600/30 transition">
                        Order Child Panel
                    </button>
                </div>
            </form>
        </div>

        <!-- DNS Nameservers (1 col) -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 space-y-4">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center">
                <svg class="w-4 h-4 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                DNS Nameservers
            </h3>
            <p class="text-xs text-slate-400">Before ordering, please point your domain's nameservers at your domain registrar to:</p>

            <div class="space-y-2 text-xs font-mono">
                <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 text-indigo-300">
                    ns1.apexsmm.com
                </div>
                <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 text-indigo-300">
                    ns2.apexsmm.com
                </div>
            </div>

            <p class="text-[11px] text-slate-500 leading-relaxed">
                DNS propagation usually takes between 10 minutes to 24 hours depending on your registrar. SSL certificate is auto-installed upon domain verification.
            </p>
        </div>
    </div>

    <!-- Active Panels Table -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="p-6 border-b border-slate-800">
            <h3 class="text-base font-bold text-white">Your Child Panels</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/70 uppercase text-[11px] text-slate-400 border-b border-slate-800 font-semibold tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">ID</th>
                        <th class="px-6 py-3.5">Domain</th>
                        <th class="px-6 py-3.5">Price</th>
                        <th class="px-6 py-3.5">Admin User</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Renewal Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    <?php if (empty($panels)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                You do not have any active child panels. Order one above!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($panels as $pan): ?>
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="px-6 py-4 font-mono font-bold text-indigo-400">#<?= (int)$pan['id'] ?></td>
                                <td class="px-6 py-4 font-bold text-white font-mono"><?= Security::e($pan['domain']) ?></td>
                                <td class="px-6 py-4 font-semibold"><?= Format::currency((float)$pan['price']) ?>/mo</td>
                                <td class="px-6 py-4 text-slate-300"><?= Security::e($pan['admin_username']) ?></td>
                                <td class="px-6 py-4"><?= Format::badge($pan['status']) ?></td>
                                <td class="px-6 py-4 text-slate-400"><?= Format::date($pan['renewal_date']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
