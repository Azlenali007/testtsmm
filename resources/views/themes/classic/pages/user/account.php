<?php
declare(strict_types=1);

use App\Core\Csrf;
use App\Helpers\Format;
use App\Helpers\Security;
?>
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 space-y-8"
     x-data="{
         apiKey: '<?= Security::e($user['apikey'] ?? '') ?>',
         copied: false,
         copyKey() {
             navigator.clipboard.writeText(this.apiKey);
             this.copied = true;
             setTimeout(() => { this.copied = false; }, 2000);
         }
     }">

    <div class="pb-4 border-b border-slate-800">
        <h1 class="text-2xl font-extrabold text-white tracking-tight">Account Settings</h1>
        <p class="text-xs text-slate-400 mt-1">Manage your credentials, timezone, and developer API access</p>
    </div>

    <!-- User Profile Overview -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-indigo-400 flex items-center justify-center text-white text-xl font-bold shadow-lg shadow-indigo-600/30">
                <?= strtoupper(substr($user['username'] ?? 'U', 0, 1)) ?>
            </div>
            <div>
                <h2 class="text-lg font-bold text-white"><?= Security::e($user['username']) ?></h2>
                <p class="text-xs text-slate-400"><?= Security::e($user['email']) ?></p>
                <span class="inline-flex items-center px-2 py-0.5 mt-1 rounded text-[10px] font-bold uppercase tracking-wider bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                    Role: <?= Security::e($user['role'] ?? 'Client') ?>
                </span>
            </div>
        </div>

        <div class="text-right">
            <span class="text-xs text-slate-400 uppercase tracking-wider block">Balance</span>
            <div class="text-2xl font-black text-indigo-400"><?= Format::currency((float)($user['balance'] ?? 0.00)) ?></div>
            <span class="text-[11px] text-slate-500 block mt-0.5">Member since <?= Format::date($user['created_at']) ?></span>
        </div>
    </div>

    <!-- API Key Card -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 sm:p-8 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center">
                    <svg class="w-4 h-4 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    SMM v2 API Access Key
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Use this key to automate orders from your own software or website</p>
            </div>
            <a href="/api-docs" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">View API Docs &rarr;</a>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3">
            <div class="w-full flex-1 relative">
                <input type="text" readonly :value="apiKey"
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-indigo-300 font-mono text-xs focus:outline-none">
            </div>

            <button type="button" @click="copyKey()" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold border border-slate-700 transition flex items-center justify-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                <span x-text="copied ? 'Copied!' : 'Copy Key'">Copy Key</span>
            </button>

            <form action="/account/regenerate-api" method="POST" onsubmit="return confirm('Regenerate API key? Any existing integration will stop working until updated.');">
                <?= Csrf::field() ?>
                <button type="submit" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 text-xs font-semibold border border-rose-500/30 transition">
                    Regenerate Key
                </button>
            </form>
        </div>
    </div>

    <!-- Security & Password Change -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Change Password -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 sm:p-8 space-y-4">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Change Password</h3>
            
            <form action="/account/password" method="POST" class="space-y-4">
                <?= Csrf::field() ?>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Current Password</label>
                    <input type="password" name="current_password" required placeholder="••••••••"
                           class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">New Password</label>
                    <input type="password" name="new_password" required minlength="8" placeholder="Minimum 8 characters"
                           class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Confirm New Password</label>
                    <input type="password" name="confirm_password" required minlength="8" placeholder="Re-enter new password"
                           class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs transition">
                    Update Password
                </button>
            </form>
        </div>

        <!-- Timezone Preference -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 sm:p-8 space-y-4 flex flex-col justify-between">
            <div class="space-y-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Timezone Preference</h3>
                <p class="text-xs text-slate-400">Order dates and transaction timestamps will be adjusted to your selected timezone.</p>

                <form action="/account/timezone" method="POST" class="space-y-4">
                    <?= Csrf::field() ?>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Timezone</label>
                        <select name="timezone" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <?php 
                            $tzList = ['UTC', 'America/New_York', 'America/Chicago', 'America/Los_Angeles', 'Europe/London', 'Europe/Paris', 'Europe/Berlin', 'Asia/Dubai', 'Asia/Singapore', 'Asia/Tokyo', 'Australia/Sydney'];
                            $currentTz = $user['timezone'] ?? 'UTC';
                            foreach ($tzList as $tz): ?>
                                <option value="<?= $tz ?>" <?= $currentTz === $tz ? 'selected' : '' ?>><?= $tz ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-indigo-300 font-semibold text-xs border border-slate-700 transition">
                        Save Preference
                    </button>
                </form>
            </div>

            <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 text-[11px] text-slate-500">
                All activity is logged and monitored for unauthorized account access.
            </div>
        </div>
    </div>
</div>
