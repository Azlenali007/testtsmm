<?php
declare(strict_types=1);

use App\Core\Auth;
use App\Core\Csrf;
use App\Helpers\Format;
use App\Helpers\Security;

$admin = Auth::user();
?>
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Ticket Header -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="font-mono text-xs font-bold text-indigo-400">Ticket #<?= (int)$ticket['id'] ?></span>
                <span class="text-slate-600">&bull;</span>
                <span class="text-xs text-slate-400">User: <strong class="text-white"><?= Security::e($ticket['username']) ?></strong> (<?= Security::e($ticket['user_email']) ?>)</span>
            </div>
            <h1 class="text-xl font-bold text-white mt-1"><?= Security::e($ticket['subject']) ?></h1>
        </div>

        <div class="flex items-center space-x-3">
            <div><?= Format::badge($ticket['status']) ?></div>
            <?php if ($ticket['status'] !== 'closed'): ?>
                <form action="/admin/tickets/close/<?= (int)$ticket['id'] ?>" method="POST" class="inline">
                    <?= Csrf::field() ?>
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 text-xs font-semibold transition">
                        Close Ticket
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Message Thread -->
    <div class="space-y-4">
        <?php foreach ($messages as $msg): ?>
            <?php $isStaff = ($msg['user_id'] == $admin['id'] || ($msg['username'] === 'admin')); ?>
            <div class="rounded-2xl border p-5 sm:p-6 space-y-3 <?= $isStaff ? 'bg-indigo-950/20 border-indigo-500/30' : 'bg-slate-900 border-slate-800' ?>">
                <div class="flex items-center justify-between pb-3 border-b <?= $isStaff ? 'border-indigo-500/20' : 'border-slate-800' ?>">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs <?= $isStaff ? 'bg-indigo-600 text-white' : 'bg-slate-700 text-slate-300' ?>">
                            <?= strtoupper(substr($msg['username'] ?? 'U', 0, 1)) ?>
                        </div>
                        <div>
                            <span class="font-semibold text-xs text-white"><?= Security::e($msg['username']) ?></span>
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

    <!-- Staff Reply Box -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 sm:p-8 space-y-4">
        <h3 class="text-sm font-bold text-white uppercase tracking-wider">Staff Reply</h3>

        <form action="/admin/tickets/reply/<?= (int)$ticket['id'] ?>" method="POST" class="space-y-4">
            <?= Csrf::field() ?>

            <div>
                <textarea name="message" rows="5" required placeholder="Type official staff response..."
                          class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
            </div>

            <div class="flex items-center justify-between">
                <a href="/admin/tickets" class="text-xs font-semibold text-slate-400 hover:text-white">&larr; Back to Ticket Queue</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-lg shadow-indigo-600/30 transition">
                    Post Staff Reply
                </button>
            </div>
        </form>
    </div>
</div>
