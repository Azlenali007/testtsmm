<?php
declare(strict_types=1);

use App\Core\Csrf;
?>
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 space-y-6">
    <div class="pb-4 border-b border-slate-800">
        <h1 class="text-2xl font-extrabold text-white tracking-tight">Mass Order</h1>
        <p class="text-xs text-slate-400 mt-1">Submit multiple orders across different services at once</p>
    </div>

    <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 sm:p-8 space-y-6">
        <div class="p-4 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-xs text-indigo-300 space-y-1">
            <span class="font-bold text-white block">Instructions:</span>
            <p>One order per line formatted strictly as: <code class="text-indigo-200 bg-slate-950 px-1.5 py-0.5 rounded font-mono">service_id | quantity | link</code></p>
            <p class="text-slate-400">Example:<br>
            <code class="text-slate-300 font-mono">1|1000|https://instagram.com/p/abc123456</code><br>
            <code class="text-slate-300 font-mono">3|500|https://tiktok.com/@creator/video/123456</code>
            </p>
        </div>

        <form action="/orders/mass" method="POST" class="space-y-5">
            <?= Csrf::field() ?>

            <div>
                <label for="mass_orders" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Orders Input</label>
                <textarea id="mass_orders" name="mass_orders" rows="10" required
                          placeholder="1|1000|https://instagram.com/p/xxx&#10;2|500|https://tiktok.com/@user/video/yyy"
                          class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
            </div>

            <div class="pt-2 flex items-center justify-between">
                <span class="text-xs text-slate-500">Total charge will be debited per validated row.</span>
                <button type="submit" class="px-8 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-lg shadow-indigo-600/30 transition">
                    Process Mass Orders
                </button>
            </div>
        </form>
    </div>
</div>
