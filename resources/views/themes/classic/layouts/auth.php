<?php
declare(strict_types=1);

use App\Core\Session;
use App\Helpers\Security;

$title = $title ?? 'Sign In';
$flashSuccess = Session::getFlash('success');
$flashError = Session::getFlash('error');
$flashInfo = Session::getFlash('info');
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= Security::e(\App\Core\Csrf::token()) ?>">
    <title><?= Security::e($title) ?> — ApexSMM</title>
    
    <link rel="stylesheet" href="/assets/css/app.css">
    <script defer src="/assets/js/alpine.min.js"></script>
    <script defer src="/assets/js/app.js"></script>
</head>
<body class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-slate-950 text-slate-100 antialiased selection:bg-indigo-500 selection:text-white">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <a href="/" class="flex items-center justify-center space-x-2.5">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-indigo-400 flex items-center justify-center text-white shadow-lg shadow-indigo-500/25">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <span class="text-2xl font-bold tracking-tight text-white">Apex<span class="text-indigo-400">SMM</span></span>
        </a>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <!-- Flash notifications -->
        <?php if ($flashSuccess): ?>
            <div class="mb-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 p-4 text-emerald-400 text-sm flex items-start space-x-3">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span><?= Security::e($flashSuccess) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($flashError): ?>
            <div class="mb-4 rounded-xl bg-rose-500/10 border border-rose-500/20 p-4 text-rose-400 text-sm flex items-start space-x-3">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span><?= Security::e($flashError) ?></span>
            </div>
        <?php endif; ?>

        <div class="bg-slate-900/80 backdrop-blur border border-slate-800 py-8 px-6 shadow-2xl rounded-2xl sm:px-10">
            <?= $content ?>
        </div>

        <div class="mt-8 text-center text-xs text-slate-500">
            <p>&copy; <?= date('Y') ?> ApexSMM. All rights reserved. Secure 256-bit SSL encrypted.</p>
        </div>
    </div>
</body>
</html>
