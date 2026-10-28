<?php
declare(strict_types=1);

use App\Core\Csrf;
use App\Helpers\Format;
use App\Helpers\Security;
?>
<div class="space-y-6"
     x-data="{
         addModal: false,
         syncModal: false,
         syncProviderId: null,
         syncProviderName: '',
         openSync(id, name) {
             this.syncProviderId = id;
             this.syncProviderName = name;
             this.syncModal = true;
         }
     }">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">SMM API Providers</h1>
            <p class="text-xs text-slate-400 mt-1">Connect upstream wholesale providers with standard SMM v2 JSON API protocol</p>
        </div>
        <button type="button" @click="addModal = true"
                class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-lg shadow-indigo-600/30 transition">
            + Add New Provider
        </button>
    </div>

    <!-- Providers Table -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/70 uppercase text-[11px] text-slate-400 border-b border-slate-800 font-semibold tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">ID</th>
                        <th class="px-6 py-3.5">Provider Name</th>
                        <th class="px-6 py-3.5">API Endpoint</th>
                        <th class="px-6 py-3.5">Reported Balance</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Last Sync</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    <?php if (empty($providers)): ?>
                        <tr><td colspan="7" class="px-6 py-8 text-center text-slate-500">No API providers configured. Add your first provider above!</td></tr>
                    <?php else: ?>
                        <?php foreach ($providers as $pr): ?>
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="px-6 py-4 font-mono font-bold text-indigo-400">#<?= (int)$pr['id'] ?></td>
                                <td class="px-6 py-4 font-bold text-white"><?= Security::e($pr['name']) ?></td>
                                <td class="px-6 py-4 font-mono text-slate-400 max-w-xs truncate" title="<?= Security::e($pr['api_url']) ?>">
                                    <?= Security::e($pr['api_url']) ?>
                                </td>
                                <td class="px-6 py-4 font-mono font-bold text-emerald-400 text-sm">
                                    <?= Format::currency((float)$pr['balance']) ?> <span class="text-[10px] text-slate-500 font-normal"><?= Security::e($pr['currency']) ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider <?= $pr['is_active'] ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-800 text-slate-500' ?>">
                                        <?= $pr['is_active'] ? 'Active' : 'Disabled' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-400 whitespace-nowrap"><?= Format::date($pr['last_sync'] ?? $pr['created_at']) ?></td>
                                <td class="px-6 py-4 text-right whitespace-nowrap space-x-1.5">
                                    <form action="/admin/providers/test-balance/<?= (int)$pr['id'] ?>" method="POST" class="inline">
                                        <?= Csrf::field() ?>
                                        <button type="submit" title="Check live API balance" class="px-2.5 py-1 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 font-semibold transition">
                                            Test Balance
                                        </button>
                                    </form>

                                    <button type="button" @click="openSync(<?= (int)$pr['id'] ?>, '<?= Security::e($pr['name']) ?>')"
                                            class="px-2.5 py-1 rounded-lg bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 border border-indigo-500/20 font-semibold transition">
                                        Sync Catalog
                                    </button>

                                    <form action="/admin/providers/delete/<?= (int)$pr['id'] ?>" method="POST" class="inline" onsubmit="return confirm('Delete this provider?');">
                                        <?= Csrf::field() ?>
                                        <button type="submit" class="px-2 py-1 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 font-semibold transition">
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

    <!-- Add Provider Modal -->
    <div x-show="addModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="addModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="addModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="addModal" x-transition.scale class="inline-block align-bottom bg-slate-900 border border-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <h3 class="text-base font-bold text-white">Add SMM Provider</h3>
                    <button @click="addModal = false" class="text-slate-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="/admin/providers/store" method="POST" class="space-y-4">
                    <?= Csrf::field() ?>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Provider Name</label>
                        <input type="text" name="name" required placeholder="e.g. JustAnotherPanel / SMMProvider"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">API Endpoint URL</label>
                        <input type="url" name="api_url" required placeholder="https://provider.com/api/v2"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">API Key</label>
                        <input type="password" name="api_key" required placeholder="••••••••"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>

                    <div class="pt-2 flex justify-end space-x-2">
                        <button type="button" @click="addModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white">Save Provider</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sync Catalog Modal -->
    <div x-show="syncModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="syncModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="syncModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="syncModal" x-transition.scale class="inline-block align-bottom bg-slate-900 border border-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <h3 class="text-base font-bold text-white">Sync Catalog: <span class="text-indigo-400" x-text="syncProviderName"></span></h3>
                    <button @click="syncModal = false" class="text-slate-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="'/admin/providers/sync/' + syncProviderId" method="POST" class="space-y-4">
                    <?= Csrf::field() ?>
                    <p class="text-xs text-slate-400">Import or update all services from this provider API automatically. Set your profit markup below:</p>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Profit Margin Markup (%)</label>
                        <input type="number" step="1" name="margin_percentage" value="30" required
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>

                    <div class="pt-2 flex justify-end space-x-2">
                        <button type="button" @click="syncModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white">Execute Sync</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
