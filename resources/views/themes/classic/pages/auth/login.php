<?php
declare(strict_types=1);

use App\Core\Csrf;
?>
<div class="space-y-6">
    <div class="text-center">
        <h2 class="text-xl font-bold tracking-tight text-white">Welcome back</h2>
        <p class="mt-1 text-sm text-slate-400">Enter your credentials to access your account</p>
    </div>

    <form action="/login" method="POST" class="space-y-4">
        <?= Csrf::field() ?>

        <div>
            <label for="username" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Username or Email</label>
            <div class="mt-1.5 relative">
                <input type="text" id="username" name="username" required autocomplete="username"
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700/80 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
                       placeholder="your_username or you@example.com">
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between">
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Password</label>
                <a href="/forgot-password" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium">Forgot password?</a>
            </div>
            <div class="mt-1.5 relative">
                <input type="password" id="password" name="password" required autocomplete="current-password"
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700/80 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
                       placeholder="••••••••">
            </div>
        </div>

        <div class="pt-2">
            <button type="submit"
                    class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-xl shadow-lg shadow-indigo-600/25 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 active:scale-[0.99] transition duration-150">
                Sign In
            </button>
        </div>
    </form>

    <div class="border-t border-slate-800 pt-4 text-center">
        <p class="text-xs text-slate-400">
            Don't have an account?
            <a href="/register" class="font-semibold text-indigo-400 hover:text-indigo-300 ml-1">Create one for free</a>
        </p>
    </div>

    <!-- Quick Demo Logins Helper -->
    <div class="mt-4 p-3 bg-slate-950/60 rounded-xl border border-slate-800 text-xs text-slate-400 space-y-1">
        <div class="font-medium text-slate-300">Demo Credentials:</div>
        <div class="flex justify-between items-center text-[11px]">
            <span>Super Admin: <code class="text-amber-400">admin</code> / <code class="text-amber-400">Password123!</code></span>
        </div>
        <div class="flex justify-between items-center text-[11px]">
            <span>Regular User: <code class="text-indigo-400">demo_user</code> / <code class="text-indigo-400">Password123!</code></span>
        </div>
    </div>
</div>
