<?php
declare(strict_types=1);

use App\Core\Csrf;
?>
<div class="space-y-6">
    <div class="text-center">
        <h2 class="text-xl font-bold tracking-tight text-white">Reset your password</h2>
        <p class="mt-1 text-sm text-slate-400">Enter your email and we'll generate a recovery link</p>
    </div>

    <form action="/forgot-password" method="POST" class="space-y-4">
        <?= Csrf::field() ?>

        <div>
            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Email Address</label>
            <div class="mt-1.5">
                <input type="email" id="email" name="email" required
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700/80 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
                       placeholder="name@company.com">
            </div>
        </div>

        <div class="pt-2">
            <button type="submit"
                    class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-xl shadow-lg shadow-indigo-600/25 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 active:scale-[0.99] transition duration-150">
                Send Reset Link
            </button>
        </div>
    </form>

    <div class="border-t border-slate-800 pt-4 text-center">
        <p class="text-xs text-slate-400">
            Remembered your credentials?
            <a href="/login" class="font-semibold text-indigo-400 hover:text-indigo-300 ml-1">Back to login</a>
        </p>
    </div>
</div>
