<?php
declare(strict_types=1);

use App\Helpers\Security;
?>
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-8 space-y-8">
    <div class="pb-4 border-b border-slate-800">
        <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs font-semibold mb-2">
            Standard SMM v2 Protocol
        </div>
        <h1 class="text-3xl font-extrabold text-white tracking-tight">API Documentation</h1>
        <p class="text-slate-400 text-sm mt-1">Connect your website, bot, or child panel with automated wholesale order dispatch.</p>
    </div>

    <!-- API Connection Credentials Card -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 space-y-4">
        <h2 class="text-sm font-bold text-white uppercase tracking-wider">General Information</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            <div class="p-4 bg-slate-950 rounded-xl border border-slate-800 space-y-1">
                <span class="text-slate-500 uppercase font-bold block">HTTP Method & Format</span>
                <span class="text-white font-mono text-sm block">POST (Content-Type: multipart/form-data or application/x-www-form-urlencoded)</span>
            </div>
            <div class="p-4 bg-slate-950 rounded-xl border border-slate-800 space-y-1">
                <span class="text-slate-500 uppercase font-bold block">API Endpoint URL</span>
                <span class="text-indigo-400 font-mono text-sm block"><?= Security::e($apiUrl) ?></span>
            </div>
        </div>

        <?php if (!empty($user['apikey'])): ?>
            <div class="p-4 bg-slate-950 rounded-xl border border-slate-800 flex items-center justify-between">
                <div>
                    <span class="text-slate-500 uppercase text-xs font-bold block">Your Personal API Key</span>
                    <span class="text-slate-200 font-mono text-xs mt-0.5 block"><?= Security::e($user['apikey']) ?></span>
                </div>
                <a href="/account" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold">Change Key</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Actions Specifications -->
    <div class="space-y-6">
        <!-- 1. Service List -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 space-y-3">
            <div class="flex items-center space-x-3">
                <span class="px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-400 font-mono text-xs font-bold border border-emerald-500/20">POST</span>
                <h3 class="text-base font-bold text-white">1. Service List</h3>
            </div>
            <p class="text-xs text-slate-400">Retrieve all available services, categories, and wholesale pricing.</p>

            <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 font-mono text-xs text-slate-300 space-y-1">
                <div>key: <span class="text-indigo-400">YOUR_API_KEY</span></div>
                <div>action: <span class="text-amber-400">"services"</span></div>
            </div>

            <div class="pt-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Example JSON Response:</span>
                <pre class="p-4 bg-slate-950 rounded-xl border border-slate-800 font-mono text-xs text-slate-300 overflow-x-auto">[
  {
    "service": 1,
    "name": "Instagram Followers [High Quality - 30D Refill]",
    "type": "default",
    "category": "Instagram Followers",
    "rate": "1.25",
    "min": "100",
    "max": "50000",
    "refill": true,
    "cancel": false
  }
]</pre>
            </div>
        </div>

        <!-- 2. Add Order -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 space-y-3">
            <div class="flex items-center space-x-3">
                <span class="px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-400 font-mono text-xs font-bold border border-emerald-500/20">POST</span>
                <h3 class="text-base font-bold text-white">2. Add Order</h3>
            </div>
            <p class="text-xs text-slate-400">Create a new order. Balance will be debited atomically.</p>

            <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 font-mono text-xs text-slate-300 space-y-1">
                <div>key: <span class="text-indigo-400">YOUR_API_KEY</span></div>
                <div>action: <span class="text-amber-400">"add"</span></div>
                <div>service: <span class="text-slate-400">1</span> (Service ID)</div>
                <div>link: <span class="text-slate-400">"https://instagram.com/user"</span></div>
                <div>quantity: <span class="text-slate-400">1000</span></div>
                <div>runs: <span class="text-slate-500">2</span> (optional for dripfeed)</div>
                <div>interval: <span class="text-slate-500">60</span> (optional for dripfeed)</div>
                <div>comments: <span class="text-slate-500">"Line1\nLine2"</span> (required for custom comments)</div>
            </div>

            <div class="pt-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Example Success Response:</span>
                <pre class="p-4 bg-slate-950 rounded-xl border border-slate-800 font-mono text-xs text-emerald-400 overflow-x-auto">{
  "order": 10582
}</pre>
            </div>
        </div>

        <!-- 3. Order Status -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 space-y-3">
            <div class="flex items-center space-x-3">
                <span class="px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-400 font-mono text-xs font-bold border border-emerald-500/20">POST</span>
                <h3 class="text-base font-bold text-white">3. Order Status (Single & Multi)</h3>
            </div>
            <p class="text-xs text-slate-400">Check current delivery progress, remains, and start count.</p>

            <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 font-mono text-xs text-slate-300 space-y-1">
                <div>key: <span class="text-indigo-400">YOUR_API_KEY</span></div>
                <div>action: <span class="text-amber-400">"status"</span></div>
                <div>order: <span class="text-slate-400">10582</span> (or <code class="text-slate-300">orders: "10582,10583,10584"</code>)</div>
            </div>

            <div class="pt-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Example Response:</span>
                <pre class="p-4 bg-slate-950 rounded-xl border border-slate-800 font-mono text-xs text-slate-300 overflow-x-auto">{
  "charge": "1.25",
  "start_count": "1420",
  "status": "In progress",
  "remains": "350",
  "currency": "USD"
}</pre>
            </div>
        </div>

        <!-- 4. User Balance -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 space-y-3">
            <div class="flex items-center space-x-3">
                <span class="px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-400 font-mono text-xs font-bold border border-emerald-500/20">POST</span>
                <h3 class="text-base font-bold text-white">4. User Balance</h3>
            </div>
            <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 font-mono text-xs text-slate-300 space-y-1">
                <div>key: <span class="text-indigo-400">YOUR_API_KEY</span></div>
                <div>action: <span class="text-amber-400">"balance"</span></div>
            </div>
            <div class="pt-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Example Response:</span>
                <pre class="p-4 bg-slate-950 rounded-xl border border-slate-800 font-mono text-xs text-slate-300 overflow-x-auto">{
  "balance": "249.50",
  "currency": "USD"
}</pre>
            </div>
        </div>
    </div>
</div>
