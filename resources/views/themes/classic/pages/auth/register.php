<?php
declare(strict_types=1);

use App\Core\Csrf;
?>
<div class="space-y-6">
    <div class="text-center">
        <h2 class="text-xl font-bold tracking-tight text-white">Create your account</h2>
        <p class="mt-1 text-sm text-slate-400">Join thousands of businesses boosting their social growth</p>
    </div>

    <form action="/register" method="POST" class="space-y-4">
        <?= Csrf::field() ?>

        <div>
            <label for="username" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Username</label>
            <div class="mt-1.5">
                <input type="text" id="username" name="username" required minlength="3" maxlength="32"
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700/80 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
                       placeholder="choose_username">
            </div>
        </div>

        <div>
            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Email Address</label>
            <div class="mt-1.5">
                <input type="email" id="email" name="email" required
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700/80 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
                       placeholder="name@company.com">
            </div>
        </div>

        <div>
            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Password</label>
            <div class="mt-1.5">
                <input type="password" id="password" name="password" required minlength="8"
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700/80 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
                       placeholder="Minimum 8 characters">
            </div>
        </div>

        <div>
            <label for="password_confirm" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Confirm Password</label>
            <div class="mt-1.5">
                <input type="password" id="password_confirm" name="password_confirm" required minlength="8"
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700/80 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
                       placeholder="Re-enter password">
            </div>
        </div>

        <div>
            <label for="ref" class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Referral Code (Optional)</label>
            <div class="mt-1.5">
                <input type="text" id="ref" name="ref" value="<?= htmlspecialchars($_GET['ref'] ?? '') ?>"
                       class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 placeholder-slate-600 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="Optional referral code">
            </div>
        </div>

        <div class="pt-2">
            <button type="submit"
                    class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-xl shadow-lg shadow-indigo-600/25 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 active:scale-[0.99] transition duration-150">
                Create Account
            </button>
        </div>
    </form>

    <div class="border-t border-slate-800 pt-4 text-center">
        <p class="text-xs text-slate-400">
            Already have an account?
            <a href="/login" class="font-semibold text-indigo-400 hover:text-indigo-300 ml-1">Sign in</a>
        </p>
    </div>
</div>
