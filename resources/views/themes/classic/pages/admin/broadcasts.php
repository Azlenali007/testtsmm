<?php
declare(strict_types=1);

use App\Core\Csrf;
use App\Helpers\Format;
use App\Helpers\Security;
?>
<div class="space-y-6" x-data="{ createModal: false }">
    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">System Announcements</h1>
            <p class="text-xs text-slate-400 mt-1">Publish alerts, service maintenance notices, and news banners on client dashboards</p>
        </div>
        <button type="button" @click="createModal = true"
                class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-lg shadow-indigo-600/30 transition">
            + New Announcement
        </button>
    </div>

    <!-- Broadcasts Table -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/70 uppercase text-[11px] text-slate-400 border-b border-slate-800 font-semibold tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">ID</th>
                        <th class="px-5 py-3.5">Type</th>
                        <th class="px-5 py-3.5">Title</th>
                        <th class="px-5 py-3.5">Message Snippet</th>
                        <th class="px-5 py-3.5">Expires</th>
                        <th class="px-5 py-3.5">Date Created</th>
                        <th class="px-5 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    <?php if (empty($broadcasts)): ?>
                        <tr><td colspan="7" class="px-6 py-8 text-center text-slate-500">No active announcements.</td></tr>
                    <?php else: ?>
                        <?php foreach ($broadcasts as $b): ?>
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="px-5 py-4 font-mono font-bold text-indigo-400">#<?= (int)$b['id'] ?></td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider <?= $b['type'] === 'danger' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : ($b['type'] === 'warning' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20') ?>">
                                        <?= Security::e($b['type']) ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4 font-bold text-white"><?= Security::e($b['title']) ?></td>
                                <td class="px-5 py-4 text-slate-300 max-w-sm truncate"><?= Security::e($b['content'] ?? ($b['message'] ?? '')) ?></td>
                                <td class="px-5 py-4 text-slate-400"><?= $b['expiry_date'] ? Format::date($b['expiry_date']) : 'Never' ?></td>
                                <td class="px-5 py-4 text-slate-400 whitespace-nowrap"><?= Format::date($b['created_at']) ?></td>
                                <td class="px-5 py-4 text-right">
                                    <form action="/admin/broadcasts/delete/<?= (int)$b['id'] ?>" method="POST" class="inline" onsubmit="return confirm('Delete announcement?');">
                                        <?= Csrf::field() ?>
                                        <button type="submit" class="px-2.5 py-1 rounded bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 font-semibold transition">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Announcement Modal -->
    <div x-show="createModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="createModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="createModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="createModal" x-transition.scale class="inline-block align-bottom bg-slate-900 border border-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <h3 class="text-base font-bold text-white">Create Announcement</h3>
                    <button @click="createModal = false" class="text-slate-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="/admin/broadcasts/store" method="POST" class="space-y-4 text-xs">
                    <?= Csrf::field() ?>

                    <div>
                        <label class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">Title</label>
                        <input type="text" name="title" required placeholder="e.g. Scheduled API Maintenance"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">Alert Style</label>
                            <select name="type" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs">
                                <option value="info">Info (Blue)</option>
                                <option value="warning">Warning (Amber)</option>
                                <option value="danger">Urgent / Alert (Red)</option>
                                <option value="announcement">Announcement (Purple)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">Auto-Expire Date</label>
                            <input type="date" name="expiry_date" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">Message Content</label>
                        <textarea name="content" rows="4" required placeholder="Announcement message displayed to users..."
                                  class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs"></textarea>
                    </div>

                    <div class="pt-2 flex justify-end space-x-2">
                        <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white">Publish</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
