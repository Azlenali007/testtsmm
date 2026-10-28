<?php
declare(strict_types=1);

use App\Helpers\Format;
use App\Helpers\Security;
?>
<div class="space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Support Tickets Queue</h1>
            <p class="text-xs text-slate-400 mt-1">Review user inquiries, respond to issues, and resolve tickets</p>
        </div>
        <span class="text-xs text-slate-400">Total: <strong class="text-white"><?= count($tickets) ?></strong> tickets</span>
    </div>

    <!-- Tickets Table -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/70 uppercase text-[11px] text-slate-400 border-b border-slate-800 font-semibold tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">ID</th>
                        <th class="px-5 py-3.5">User</th>
                        <th class="px-5 py-3.5">Subject</th>
                        <th class="px-5 py-3.5">Priority</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Updated</th>
                        <th class="px-5 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    <?php if (empty($tickets)): ?>
                        <tr><td colspan="7" class="px-6 py-8 text-center text-slate-500">No support tickets found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($tickets as $t): ?>
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="px-5 py-4 font-mono font-bold text-indigo-400">#<?= (int)$t['id'] ?></td>
                                <td class="px-5 py-4 font-semibold text-white"><?= Security::e($t['username']) ?></td>
                                <td class="px-5 py-4 font-medium text-slate-200">
                                    <a href="/admin/tickets/<?= (int)$t['id'] ?>" class="hover:text-indigo-300">
                                        <?= Security::e($t['subject']) ?>
                                    </a>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider <?= $t['priority'] === 'high' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : ($t['priority'] === 'medium' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-slate-800 text-slate-400') ?>">
                                        <?= Security::e($t['priority']) ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4"><?= Format::badge($t['status']) ?></td>
                                <td class="px-5 py-4 text-slate-400 whitespace-nowrap"><?= Format::date($t['updated_at'] ?? $t['created_at']) ?></td>
                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    <a href="/admin/tickets/<?= (int)$t['id'] ?>" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-md shadow-indigo-600/20 transition">
                                        Open Ticket &rarr;
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
