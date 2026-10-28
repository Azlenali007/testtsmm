<?php
declare(strict_types=1);

use App\Helpers\Format;
use App\Helpers\Security;
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8"
     x-data="{
         search: '',
         activeCategory: 'all',
         modalOpen: false,
         modalService: null,
         openDetails(service) {
             this.modalService = service;
             this.modalOpen = true;
         }
     }">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Services & Pricing</h1>
            <p class="text-xs text-slate-400 mt-1">Wholesale rate list with real-time API dispatch limits</p>
        </div>
        
        <!-- Search bar -->
        <div class="relative w-full sm:w-72">
            <input type="text" x-model="search" placeholder="Search services..."
                   class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <svg class="w-4 h-4 text-slate-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
    </div>

    <!-- Category Filter Tabs -->
    <div class="flex items-center space-x-2 overflow-x-auto pb-2 scrollbar-none">
        <button @click="activeCategory = 'all'"
                :class="activeCategory === 'all' ? 'bg-indigo-600 text-white' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white'"
                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition">
            All Categories
        </button>
        <?php foreach ($categories as $cat): ?>
            <button @click="activeCategory = '<?= Security::e($cat['name']) ?>'"
                    :class="activeCategory === '<?= Security::e($cat['name']) ?>' ? 'bg-indigo-600 text-white' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white'"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition">
                <?= Security::e($cat['name']) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Categorized Services Table Sections -->
    <div class="space-y-8">
        <?php foreach ($categorizedServices as $categoryName => $servicesList): ?>
            <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl"
                 x-show="activeCategory === 'all' || activeCategory === '<?= Security::e($categoryName) ?>'">
                <div class="px-6 py-4 bg-slate-950/70 border-b border-slate-800 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center">
                        <span class="w-2 h-2 rounded-full bg-indigo-500 mr-2.5"></span>
                        <?= Security::e($categoryName) ?>
                    </h2>
                    <span class="text-xs text-slate-500"><?= count($servicesList) ?> services</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="bg-slate-950/40 uppercase text-[11px] text-slate-400 border-b border-slate-800 font-semibold tracking-wider">
                            <tr>
                                <th class="px-6 py-3">ID</th>
                                <th class="px-6 py-3">Service Name</th>
                                <th class="px-6 py-3">Rate / 1k</th>
                                <th class="px-6 py-3">Min / Max</th>
                                <th class="px-6 py-3">Features</th>
                                <th class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80">
                            <?php foreach ($servicesList as $s): ?>
                                <tr class="hover:bg-slate-800/30 transition"
                                    x-show="!search || '<?= strtolower(addslashes($s['name'])) ?>'.includes(search.toLowerCase()) || '<?= (int)$s['id'] ?>'.includes(search)">
                                    <td class="px-6 py-3.5 font-mono text-indigo-400 font-bold">#<?= (int)$s['id'] ?></td>
                                    <td class="px-6 py-3.5 font-medium text-white max-w-md">
                                        <div class="font-semibold text-slate-100"><?= Security::e($s['name']) ?></div>
                                        <?php if (!empty($s['description'])): ?>
                                            <button @click="openDetails(<?= htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8') ?>)"
                                                    class="text-[11px] text-indigo-400 hover:text-indigo-300 underline mt-0.5 inline-block">
                                                View description & rules &rarr;
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-3.5 font-bold text-white whitespace-nowrap">
                                        <?= Format::currency((float)$s['price']) ?>
                                    </td>
                                    <td class="px-6 py-3.5 font-mono text-slate-400 whitespace-nowrap">
                                        <?= number_format((int)$s['min_quantity']) ?> / <?= number_format((int)$s['max_quantity']) ?>
                                    </td>
                                    <td class="px-6 py-3.5 whitespace-nowrap space-x-1.5">
                                        <?php if ($s['dripfeed_enabled'] == 1): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">Drip-feed</span>
                                        <?php endif; ?>
                                        <?php if ($s['refill_enabled'] == 1): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Refill</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-3.5 text-right whitespace-nowrap">
                                        <a href="/order/new?service_id=<?= (int)$s['id'] ?>"
                                           class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-md shadow-indigo-600/20 transition">
                                            Order
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Service Details Modal -->
    <div x-show="modalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="modalOpen" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity" @click="modalOpen = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="modalOpen" x-transition.scale class="inline-block align-bottom bg-slate-900 border border-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <h3 class="text-base font-bold text-white" x-text="modalService?.name"></h3>
                    <button @click="modalOpen = false" class="text-slate-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-3 text-xs text-slate-300">
                    <div class="grid grid-cols-2 gap-2 bg-slate-950 p-3 rounded-xl border border-slate-800">
                        <div>
                            <span class="text-slate-500 block">Rate / 1000:</span>
                            <span class="font-bold text-indigo-400 text-sm" x-text="'$' + parseFloat(modalService?.price || 0).toFixed(2)"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Limits:</span>
                            <span class="font-mono text-white" x-text="(modalService?.min_quantity || 0) + ' min / ' + Number(modalService?.max_quantity || 0).toLocaleString() + ' max'"></span>
                        </div>
                    </div>

                    <div>
                        <span class="text-slate-400 font-semibold uppercase tracking-wider block mb-1">Description & Guidelines</span>
                        <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 whitespace-pre-line text-slate-300 leading-relaxed max-h-60 overflow-y-auto"
                             x-text="modalService?.description || 'No specific description provided.'"></div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-800 flex justify-end space-x-2">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300">Close</button>
                    <a :href="'/order/new?service_id=' + modalService?.id" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white">Proceed to Order</a>
                </div>
            </div>
        </div>
    </div>
</div>
