<?php
declare(strict_types=1);

use App\Core\Csrf;
use App\Helpers\Security;
?>
<div class="space-y-6">
    <div class="text-center">
        <h2 class="text-xl font-bold tracking-tight text-white">Choose a new password</h2>
        <p class="mt-1 text-sm text-slate-400">Must be at least 8 characters long</p>
    </div>

    <form action="/reset-password" method="POST" class="space-y-4">
        <?= Csrf::field() ?>
        <input type="hidden" name="token" value="<?= Security::e($token ?? '') ?>">

        <div>
            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">New Password</label>
            <div class="mt-1.5">
                <input type="password" id="password" name="password" required minlength="8"
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700/80 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
                       placeholder="••••••••">
            </div>
        </div>

        <div>
            <label for="password_confirm" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Confirm New Password</label>
            <div class="mt-1.5">
                <input type="password" id="password_confirm" name="password_confirm" required minlength="8"
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700/80 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
                       placeholder="••••••••">
            </div>
        </div>

        <div class="pt-2">
            <button type="submit"
                    class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-xl shadow-lg shadow-indigo-600/25 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 active:scale-[0.99] transition duration-150">
                Update Password
            </button>
        </div>
    </form>
</div>
