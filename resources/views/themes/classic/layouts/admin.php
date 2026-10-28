<?php
declare(strict_types=1);

use App\Core\Auth;
use App\Core\Session;
use App\Helpers\Security;

$admin = Auth::user();
$title = $title ?? 'Super Admin Control Center';
$currentUri = $_SERVER['REQUEST_URI'] ?? '/admin';
$flashSuccess = Session::getFlash('success');
$flashError = Session::getFlash('error');
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= Security::e(\App\Core\Csrf::token()) ?>">
    <title><?= Security::e($title) ?> — ApexSMM Admin</title>

    <!-- Compiled Tailwind CSS -->
    <link rel="stylesheet" href="/assets/css/app.css">
    
    <!-- Alpine.js & Chart.js -->
    <script defer src="/assets/js/alpine.min.js"></script>
    <script src="/assets/js/chart.min.js"></script>
    <script defer src="/assets/js/app.js"></script>
</head>
<body class="min-h-full flex flex-col md:flex-row bg-slate-950 text-slate-100 font-sans antialiased" x-data="{ sidebarOpen: false }">

    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col space-y-2 pointer-events-none"></div>

    <!-- Sidebar Navigation -->
    <aside class="w-full md:w-64 bg-slate-900 border-r border-slate-800 flex flex-col flex-shrink-0">
        <!-- Brand Header -->
        <div class="h-16 flex items-center justify-between px-6 border-b border-slate-800">
            <a href="/admin" class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold shadow-md shadow-indigo-600/30">
                    A
                </div>
                <span class="text-lg font-bold tracking-tight text-white">Apex<span class="text-indigo-400">Admin</span></span>
            </a>
            <button @click="sidebarOpen = !sidebarOpen" class="md:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav :class="sidebarOpen ? 'block' : 'hidden md:block'" class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
            <div class="px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Core Operations</div>
            <a href="/admin" class="sidebar-link <?= $currentUri === '/admin' ? 'active' : '' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="/admin/orders" class="sidebar-link <?= str_starts_with($currentUri, '/admin/orders') ? 'active' : '' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Orders
            </a>
            <a href="/admin/services" class="sidebar-link <?= str_starts_with($currentUri, '/admin/services') ? 'active' : '' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Services & Catalog
            </a>
            <a href="/admin/providers" class="sidebar-link <?= str_starts_with($currentUri, '/admin/providers') ? 'active' : '' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                API Providers
            </a>

            <div class="pt-4 px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Users & Billing</div>
            <a href="/admin/users" class="sidebar-link <?= str_starts_with($currentUri, '/admin/users') ? 'active' : '' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Users & Accounts
            </a>
            <a href="/admin/payments" class="sidebar-link <?= $currentUri === '/admin/payments' ? 'active' : '' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Deposits & Payments
            </a>
            <a href="/admin/payments/settings" class="sidebar-link <?= str_starts_with($currentUri, '/admin/payments/settings') ? 'active' : '' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                Gateway Config
            </a>

            <div class="pt-4 px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Customer Support</div>
            <a href="/admin/tickets" class="sidebar-link <?= str_starts_with($currentUri, '/admin/tickets') ? 'active' : '' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                Tickets Helpdesk
            </a>
            <a href="/admin/coupons" class="sidebar-link <?= str_starts_with($currentUri, '/admin/coupons') ? 'active' : '' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                Coupons & Promos
            </a>
            <a href="/admin/referrals" class="sidebar-link <?= str_starts_with($currentUri, '/admin/referrals') ? 'active' : '' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                Affiliate Payouts
            </a>
            <a href="/admin/child-panels" class="sidebar-link <?= str_starts_with($currentUri, '/admin/child-panels') ? 'active' : '' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                Child Panels
            </a>
            <a href="/admin/broadcasts" class="sidebar-link <?= str_starts_with($currentUri, '/admin/broadcasts') ? 'active' : '' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                Broadcasts
            </a>

            <div class="pt-4 px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Settings & Logs</div>
            <a href="/admin/settings" class="sidebar-link <?= str_starts_with($currentUri, '/admin/settings') ? 'active' : '' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                System & Themes
            </a>
            <a href="/admin/logs" class="sidebar-link <?= str_starts_with($currentUri, '/admin/logs') ? 'active' : '' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Audit & Cron Logs
            </a>
        </nav>

        <!-- Bottom User Footer -->
        <div class="p-4 border-t border-slate-800 bg-slate-900/60 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <div class="w-7 h-7 rounded-full bg-indigo-600 flex items-center justify-center text-xs font-bold text-white">
                    <?= strtoupper(substr($admin['username'] ?? 'A', 0, 1)) ?>
                </div>
                <div class="text-xs">
                    <p class="font-medium text-white"><?= Security::e($admin['username']) ?></p>
                    <p class="text-slate-400">Super Admin</p>
                </div>
            </div>
            <a href="/dashboard" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium">User Panel &rarr;</a>
        </div>
    </aside>

    <!-- Main Content Canvas -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Top App Bar -->
        <header class="h-16 bg-slate-900 border-b border-slate-800 flex items-center justify-between px-6">
            <div class="flex items-center space-x-3 text-sm">
                <span class="text-slate-400">Administration</span>
                <span class="text-slate-600">/</span>
                <span class="font-semibold text-white"><?= Security::e($title) ?></span>
            </div>

            <div class="flex items-center space-x-4">
                <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span>
                    Database Connected
                </span>
                <a href="/logout" class="text-xs text-rose-400 hover:text-rose-300 font-medium">Sign Out</a>
            </div>
        </header>

        <!-- Flash Message Banners -->
        <div class="p-6 pb-0">
            <?php if ($flashSuccess): ?>
                <div class="p-4 mb-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 flex items-center space-x-2">
                    <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span class="text-sm font-medium"><?= $flashSuccess ?></span>
                </div>
            <?php endif; ?>

            <?php if ($flashError): ?>
                <div class="p-4 mb-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 flex items-center space-x-2">
                    <svg class="w-5 h-5 text-rose-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span class="text-sm font-medium"><?= $flashError ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Admin View Slot -->
        <main class="flex-1 p-6 overflow-y-auto">
            <?= $content ?>
        </main>
    </div>
</body>
</html>
