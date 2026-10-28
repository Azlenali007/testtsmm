<?php
declare(strict_types=1);

use App\Core\Auth;
use App\Core\Csrf;
use App\Helpers\Format;
use App\Helpers\Security;

$currentUser = Auth::user();
?>
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 space-y-6">
    <!-- Header Card -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="font-mono text-xs font-bold text-indigo-400">Ticket #<?= (int)$ticket['id'] ?></span>
                <span class="text-slate-600">&bull;</span>
                <span class="text-xs text-slate-400"><?= Format::date($ticket['created_at']) ?></span>
            </div>
            <h1 class="text-xl font-bold text-white mt-1"><?= Security::e($ticket['subject']) ?></h1>
        </div>

        <div class="flex items-center space-x-3">
            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wider <?= $ticket['priority'] === 'high' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : ($ticket['priority'] === 'medium' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-slate-800 text-slate-400') ?>">
                Priority: <?= Security::e($ticket['priority']) ?>
            </span>
            <div><?= Format::badge($ticket['status']) ?></div>
        </div>
    </div>

    <!-- Message Thread -->
    <div class="space-y-4">
        <?php foreach ($messages as $msg): ?>
            <?php $isStaff = ($msg['user_id'] != $currentUser['id']); ?>
            <div class="rounded-2xl border p-5 sm:p-6 space-y-3 <?= $isStaff ? 'bg-indigo-950/20 border-indigo-500/30' : 'bg-slate-900 border-slate-800' ?>">
                <div class="flex items-center justify-between pb-3 border-b <?= $isStaff ? 'border-indigo-500/20' : 'border-slate-800' ?>">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs <?= $isStaff ? 'bg-indigo-600 text-white' : 'bg-slate-700 text-slate-300' ?>">
                            <?= strtoupper(substr($msg['username'] ?? ($isStaff ? 'Support' : 'You'), 0, 1)) ?>
                        </div>
                        <div>
                            <span class="font-semibold text-xs text-white"><?= Security::e($msg['username'] ?? 'User') ?></span>
                            <?php if ($isStaff): ?>
                                <span class="ml-1.5 px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">Staff Agent</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <span class="text-[11px] text-slate-500"><?= Format::date($msg['created_at']) ?></span>
                </div>

                <div class="text-sm text-slate-300 whitespace-pre-line leading-relaxed">
                    <?= Security::e($msg['message']) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Reply Form Box -->
    <?php if ($ticket['status'] !== 'closed'): ?>
        <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 sm:p-8 space-y-4">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Post a Reply</h3>

            <form action="/tickets/<?= (int)$ticket['id'] ?>/reply" method="POST" class="space-y-4">
                <?= Csrf::field() ?>

                <div>
                    <textarea name="message" rows="4" required placeholder="Type your response here..."
                              class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none placeholder-slate-500"></textarea>
                </div>

                <div class="flex items-center justify-between">
                    <a href="/tickets" class="text-xs font-semibold text-slate-400 hover:text-white">&larr; Back to Ticket List</a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-lg shadow-indigo-600/30 transition">
                        Send Reply
                    </button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 text-center text-xs text-slate-400">
            This support ticket has been closed. If you require further assistance, please <a href="/tickets/create" class="text-indigo-400 underline font-semibold">open a new ticket</a>.
        </div>
    <?php endif; ?>
</div>
