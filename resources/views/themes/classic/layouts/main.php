<?php
declare(strict_types=1);

use App\Core\Auth;
use App\Core\Session;
use App\Helpers\Format;
use App\Helpers\Security;

$user = Auth::user();
$isAdmin = Auth::isAdmin();
$title = $title ?? 'Social Media Marketing Panel';
$flashSuccess = Session::getFlash('success');
$flashError = Session::getFlash('error');
$flashInfo = Session::getFlash('info');
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= Security::e(\App\Core\Csrf::token()) ?>">
    <title><?= Security::e($title) ?> — ApexSMM</title>
    
    <!-- Compiled Tailwind CSS -->
    <link rel="stylesheet" href="/assets/css/app.css">
    
    <!-- Standalone Alpine.js -->
    <script defer src="/assets/js/alpine.min.js"></script>
    <script defer src="/assets/js/app.js"></script>
</head>
<body class="min-h-full flex flex-col bg-slate-950 text-slate-100 font-sans antialiased" x-data="{ mobileMenu: false, userDropdown: false }">

    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col space-y-2 pointer-events-none"></div>

    <!-- Navigation Header -->
    <header class="sticky top-0 z-40 bg-slate-900/90 backdrop-blur-md border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo & Brand -->
                <div class="flex items-center space-x-3">
                    <a href="<?= Auth::check() ? '/dashboard' : '/' ?>" class="flex items-center space-x-2.5">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-indigo-400 flex items-center justify-center text-white shadow-md shadow-indigo-500/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <span class="text-xl font-bold tracking-tight text-white">Apex<span class="text-indigo-400">SMM</span></span>
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <nav class="hidden md:flex items-center space-x-1">
                    <?php if (Auth::check()): ?>
                        <a href="/dashboard" class="px-3 py-2 rounded-lg text-sm font-medium hover:text-white hover:bg-slate-800 text-slate-300">Dashboard</a>
                        <a href="/order/new" class="px-3 py-2 rounded-lg text-sm font-medium hover:text-white hover:bg-slate-800 text-slate-300">New Order</a>
                        <a href="/orders" class="px-3 py-2 rounded-lg text-sm font-medium hover:text-white hover:bg-slate-800 text-slate-300">Orders</a>
                        <a href="/services" class="px-3 py-2 rounded-lg text-sm font-medium hover:text-white hover:bg-slate-800 text-slate-300">Services</a>
                        <a href="/wallet" class="px-3 py-2 rounded-lg text-sm font-medium hover:text-white hover:bg-slate-800 text-slate-300">Add Funds</a>
                        <a href="/tickets" class="px-3 py-2 rounded-lg text-sm font-medium hover:text-white hover:bg-slate-800 text-slate-300">Tickets</a>
                        <a href="/affiliates" class="px-3 py-2 rounded-lg text-sm font-medium hover:text-white hover:bg-slate-800 text-slate-300">Affiliates</a>
                        <a href="/child-panels" class="px-3 py-2 rounded-lg text-sm font-medium hover:text-white hover:bg-slate-800 text-slate-300">Child Panels</a>
                        <a href="/api-docs" class="px-3 py-2 rounded-lg text-sm font-medium hover:text-white hover:bg-slate-800 text-slate-300">API</a>
                    <?php else: ?>
                        <a href="/" class="px-3 py-2 rounded-lg text-sm font-medium hover:text-white hover:bg-slate-800 text-slate-300">Home</a>
                        <a href="/services" class="px-3 py-2 rounded-lg text-sm font-medium hover:text-white hover:bg-slate-800 text-slate-300">Services</a>
                        <a href="/terms" class="px-3 py-2 rounded-lg text-sm font-medium hover:text-white hover:bg-slate-800 text-slate-300">Terms</a>
                    <?php endif; ?>
                </nav>

                <!-- User Controls or Guest Auth Buttons -->
                <div class="flex items-center space-x-3">
                    <?php if (Auth::check()): ?>
                        <!-- User Balance Badge -->
                        <a href="/wallet" class="hidden sm:inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-sm font-semibold hover:bg-indigo-500/20 transition-colors">
                            <span class="text-xs text-indigo-400">Balance:</span>
                            <span><?= Format::currency($user['balance'] ?? 0.00) ?></span>
                        </a>

                        <!-- User Profile Dropdown -->
                        <div class="relative" @click.outside="userDropdown = false">
                            <button @click="userDropdown = !userDropdown" type="button" class="flex items-center space-x-2 p-1.5 rounded-lg hover:bg-slate-800 text-slate-300 hover:text-white focus:outline-none">
                                <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center font-bold text-sm text-indigo-300">
                                    <?= strtoupper(substr($user['username'] ?? 'U', 0, 1)) ?>
                                </div>
                                <span class="hidden lg:inline text-sm font-medium"><?= Security::e($user['username'] ?? 'User') ?></span>
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div x-show="userDropdown" x-transition.origin.top.right class="absolute right-0 mt-2 w-52 rounded-xl bg-slate-800 border border-slate-700 shadow-xl py-1 text-sm z-50">
                                <div class="px-4 py-2 border-b border-slate-700 text-xs text-slate-400">
                                    Signed in as <span class="font-semibold text-white"><?= Security::e($user['username']) ?></span>
                                </div>
                                <?php if ($isAdmin): ?>
                                    <a href="/admin" class="flex items-center px-4 py-2.5 text-amber-400 hover:bg-slate-700 font-medium">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                                        Super Admin Panel
                                    </a>
                                <?php endif; ?>
                                <a href="/account" class="block px-4 py-2 hover:bg-slate-700 text-slate-200">Account Settings</a>
                                <a href="/wallet" class="block px-4 py-2 hover:bg-slate-700 text-slate-200">Transactions & Funds</a>
                                <a href="/order/mass" class="block px-4 py-2 hover:bg-slate-700 text-slate-200">Mass Orders</a>
                                <a href="/api-docs" class="block px-4 py-2 hover:bg-slate-700 text-slate-200">API Credentials</a>
                                <div class="border-t border-slate-700 my-1"></div>
                                <a href="/logout" class="block px-4 py-2 text-rose-400 hover:bg-slate-700">Sign Out</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="/login" class="text-sm font-semibold text-slate-300 hover:text-white px-3 py-2">Sign in</a>
                        <a href="/register" class="btn-primary text-sm">Get Started</a>
                    <?php endif; ?>

                    <!-- Mobile menu button -->
                    <button @click="mobileMenu = !mobileMenu" class="md:hidden p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            <path x-show="mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Drawer -->
        <div x-show="mobileMenu" x-transition class="md:hidden border-b border-slate-800 bg-slate-900 px-4 pt-2 pb-4 space-y-1">
            <?php if (Auth::check()): ?>
                <a href="/dashboard" class="block px-3 py-2 rounded-md text-base font-medium text-slate-300 hover:text-white hover:bg-slate-800">Dashboard</a>
                <a href="/order/new" class="block px-3 py-2 rounded-md text-base font-medium text-slate-300 hover:text-white hover:bg-slate-800">New Order</a>
                <a href="/orders" class="block px-3 py-2 rounded-md text-base font-medium text-slate-300 hover:text-white hover:bg-slate-800">My Orders</a>
                <a href="/services" class="block px-3 py-2 rounded-md text-base font-medium text-slate-300 hover:text-white hover:bg-slate-800">Services</a>
                <a href="/wallet" class="block px-3 py-2 rounded-md text-base font-medium text-slate-300 hover:text-white hover:bg-slate-800">Wallet & Deposits</a>
                <a href="/tickets" class="block px-3 py-2 rounded-md text-base font-medium text-slate-300 hover:text-white hover:bg-slate-800">Support</a>
                <a href="/affiliates" class="block px-3 py-2 rounded-md text-base font-medium text-slate-300 hover:text-white hover:bg-slate-800">Affiliates</a>
                <a href="/child-panels" class="block px-3 py-2 rounded-md text-base font-medium text-slate-300 hover:text-white hover:bg-slate-800">Child Panels</a>
                <a href="/account" class="block px-3 py-2 rounded-md text-base font-medium text-slate-300 hover:text-white hover:bg-slate-800">Account</a>
                <?php if ($isAdmin): ?>
                    <a href="/admin" class="block px-3 py-2 rounded-md text-base font-medium text-amber-400 hover:bg-slate-800">Admin Control Center</a>
                <?php endif; ?>
                <a href="/logout" class="block px-3 py-2 rounded-md text-base font-medium text-rose-400 hover:bg-slate-800">Sign Out</a>
            <?php else: ?>
                <a href="/" class="block px-3 py-2 rounded-md text-base font-medium text-slate-300 hover:text-white hover:bg-slate-800">Home</a>
                <a href="/services" class="block px-3 py-2 rounded-md text-base font-medium text-slate-300 hover:text-white hover:bg-slate-800">Services</a>
                <a href="/login" class="block px-3 py-2 rounded-md text-base font-medium text-slate-300 hover:text-white hover:bg-slate-800">Sign In</a>
                <a href="/register" class="block px-3 py-2 rounded-md text-base font-medium text-indigo-400 hover:bg-slate-800">Register</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Global Flash Alerts -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        <?php if ($flashSuccess): ?>
            <div class="p-4 mb-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <svg class="w-5 h-5 flex-shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span class="text-sm font-medium"><?= $flashSuccess ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($flashError): ?>
            <div class="p-4 mb-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <svg class="w-5 h-5 flex-shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span class="text-sm font-medium"><?= $flashError ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($flashInfo): ?>
            <div class="p-4 mb-4 rounded-xl bg-indigo-500/10 border border-indigo-500/30 text-indigo-300 flex items-center space-x-3">
                <svg class="w-5 h-5 flex-shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-sm font-medium"><?= $flashInfo ?></span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Main Content Body Slot -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="mt-auto border-t border-slate-800 bg-slate-900/60 py-8 text-sm text-slate-400">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center space-x-2">
                <span class="font-bold text-white tracking-tight">Apex<span class="text-indigo-400">SMM</span></span>
                <span>&copy; <?= date('Y') ?>. All rights reserved. Production Core PHP Platform.</span>
            </div>
            <div class="flex items-center space-x-6 text-xs text-slate-400">
                <a href="/services" class="hover:text-white">Services</a>
                <a href="/terms" class="hover:text-white">Terms of Service</a>
                <a href="/privacy" class="hover:text-white">Privacy Policy</a>
                <a href="/api-docs" class="hover:text-white">API Documentation</a>
                <?php if ($isAdmin): ?>
                    <a href="/admin" class="text-amber-400 font-semibold hover:underline">Admin Panel</a>
                <?php endif; ?>
            </div>
        </div>
    </footer>
</body>
</html>
