<?php
declare(strict_types=1);

use App\Helpers\Format;
use App\Helpers\Security;
?>
<div class="space-y-24 py-12">
    <!-- Hero Section -->
    <section class="text-center max-w-4xl mx-auto px-4 sm:px-6">
        <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs font-semibold mb-6">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>Automated SMM Infrastructure v2.5</span>
        </div>
        <h1 class="text-4xl sm:text-6xl font-extrabold text-white tracking-tight leading-[1.1]">
            Scale Your Social Presence <br class="hidden sm:inline">
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-purple-300 to-indigo-200">
                Instantly & Reliably
            </span>
        </h1>
        <p class="mt-6 text-lg sm:text-xl text-slate-400 max-w-2xl mx-auto font-normal leading-relaxed">
            Direct wholesale prices on TikTok, Instagram, YouTube, Telegram, and Facebook growth services. Instant delivery with automated refill protection.
        </p>

        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="/register" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-base shadow-xl shadow-indigo-600/30 transition transform hover:-translate-y-0.5 text-center">
                Get Started Free
            </a>
            <a href="/services" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-200 font-semibold text-base border border-slate-700 transition text-center">
                Explore Services & Pricing
            </a>
        </div>

        <!-- Metrics Row -->
        <div class="mt-16 grid grid-cols-2 md:grid-cols-3 gap-6 pt-12 border-t border-slate-800/80">
            <div class="p-4 rounded-2xl bg-slate-900/40 border border-slate-800">
                <div class="text-3xl font-extrabold text-white"><?= number_format($stats['total_orders'] ?? 15000) ?>+</div>
                <div class="text-xs font-medium text-slate-400 mt-1 uppercase tracking-wider">Orders Processed</div>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900/40 border border-slate-800">
                <div class="text-3xl font-extrabold text-indigo-400"><?= number_format($stats['active_services'] ?? 50) ?>+</div>
                <div class="text-xs font-medium text-slate-400 mt-1 uppercase tracking-wider">Active Services</div>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900/40 border border-slate-800 col-span-2 md:col-span-1">
                <div class="text-3xl font-extrabold text-emerald-400">99.98%</div>
                <div class="text-xs font-medium text-slate-400 mt-1 uppercase tracking-wider">API Uptime</div>
            </div>
        </div>
    </section>

    <!-- Popular Services Highlights -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
            <div>
                <h2 class="text-2xl font-bold text-white tracking-tight">Popular Wholesale Services</h2>
                <p class="text-slate-400 text-sm mt-1">High-retention, high-speed packages chosen by top agencies</p>
            </div>
            <a href="/services" class="mt-4 sm:mt-0 text-sm font-semibold text-indigo-400 hover:text-indigo-300 inline-flex items-center">
                View complete service list
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($popularServices as $svc): ?>
                <div class="rounded-2xl bg-slate-900/70 border border-slate-800 p-5 flex flex-col justify-between hover:border-slate-700 hover:bg-slate-900 transition group">
                    <div>
                        <div class="flex items-center justify-between text-xs text-indigo-400 font-semibold mb-2">
                            <span><?= Security::e($svc['category_name']) ?></span>
                            <span class="text-slate-500 font-mono text-[11px]">ID #<?= (int)$svc['id'] ?></span>
                        </div>
                        <h3 class="text-white font-semibold text-sm line-clamp-2 leading-snug group-hover:text-indigo-300 transition">
                            <?= Security::e($svc['name']) ?>
                        </h3>
                        <p class="mt-2 text-xs text-slate-400 line-clamp-2">
                            <?= Security::e($svc['description'] ?: 'High quality delivery with automated tracking.') ?>
                        </p>
                    </div>

                    <div class="mt-5 pt-4 border-t border-slate-800/80 flex items-center justify-between">
                        <div>
                            <span class="text-xs text-slate-500 block">Rate / 1k</span>
                            <span class="text-base font-bold text-white"><?= Format::currency((float)($svc['price'] ?? 0.00)) ?></span>
                        </div>
                        <a href="/login" class="px-3 py-1.5 rounded-lg bg-indigo-600/20 text-indigo-300 border border-indigo-500/30 text-xs font-semibold hover:bg-indigo-600 hover:text-white transition">
                            Order Now
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Why Choose Us Features -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">Built for Growth Agencies & Resellers</h2>
            <p class="text-slate-400 text-sm mt-2">Enterprise stability, developer-friendly API v2, and automated refunds.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="p-6 rounded-2xl bg-slate-900/50 border border-slate-800">
                <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center mb-5">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-white">Instant Automated Dispatch</h3>
                <p class="mt-2 text-sm text-slate-400 leading-relaxed">Orders are queued and transmitted to provider servers in real-time. Track real-time progress right from your panel.</p>
            </div>

            <div class="p-6 rounded-2xl bg-slate-900/50 border border-slate-800">
                <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center mb-5">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                </div>
                <h3 class="text-lg font-bold text-white">Standard SMM v2 API</h3>
                <p class="mt-2 text-sm text-slate-400 leading-relaxed">Full JSON API compatibility. Connect your own custom scripts, WordPress plugins, or child panels effortlessly.</p>
            </div>

            <div class="p-6 rounded-2xl bg-slate-900/50 border border-slate-800">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-5">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-white">Guaranteed Wallet Protection</h3>
                <p class="mt-2 text-sm text-slate-400 leading-relaxed">Atomic database balance debits and automatic pro-rata refunds for partial or canceled orders. Zero balance leakage.</p>
            </div>
        </div>
    </section>

    <!-- Ready to Start CTA Banner -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl bg-gradient-to-r from-indigo-900/60 via-slate-900 to-indigo-950 border border-indigo-500/20 p-8 sm:p-12 text-center relative overflow-hidden">
            <h2 class="text-3xl font-extrabold text-white">Ready to elevate your social impact?</h2>
            <p class="mt-3 text-slate-400 text-sm max-w-xl mx-auto">Create an account in less than 30 seconds and fund with your preferred payment method.</p>
            <div class="mt-8 flex justify-center space-x-4">
                <a href="/register" class="px-8 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-lg shadow-indigo-600/30 transition">
                    Register Now
                </a>
            </div>
        </div>
    </section>
</div>
