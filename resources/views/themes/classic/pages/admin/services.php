<?php
declare(strict_types=1);

use App\Core\Csrf;
use App\Helpers\Format;
use App\Helpers\Security;
?>
<div class="space-y-6"
     x-data="{
         createServiceModal: false,
         createCategoryModal: false,
         marginModal: false,
         search: '',
         filterCategory: 'all'
     }">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Services & Categories</h1>
            <p class="text-xs text-slate-400 mt-1">Manage catalog offerings, API mapping to providers, and wholesale price margins</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" @click="createCategoryModal = true"
                    class="px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-800 hover:bg-slate-800 text-slate-200 text-xs font-semibold transition">
                + Add Category
            </button>
            <button type="button" @click="marginModal = true"
                    class="px-3.5 py-2 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 border border-amber-500/20 text-xs font-semibold transition">
                % Bulk Margin
            </button>
            <button type="button" @click="createServiceModal = true"
                    class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-lg shadow-indigo-600/30 transition">
                + Add Service
            </button>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-2">
            <select x-model="filterCategory" class="px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="all">All Categories (<?= count($services) ?> services)</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= Security::e($c['name']) ?>"><?= Security::e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="relative max-w-xs w-full">
            <input type="text" x-model="search" placeholder="Search service name or ID..."
                   class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <svg class="w-4 h-4 text-slate-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
    </div>

    <!-- Services Table -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/70 uppercase text-[11px] text-slate-400 border-b border-slate-800 font-semibold tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">ID</th>
                        <th class="px-5 py-3.5">Category</th>
                        <th class="px-5 py-3.5">Service Name</th>
                        <th class="px-5 py-3.5">Provider</th>
                        <th class="px-5 py-3.5">Price / 1k</th>
                        <th class="px-5 py-3.5">Min / Max</th>
                        <th class="px-5 py-3.5">Features</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    <?php if (empty($services)): ?>
                        <tr><td colspan="9" class="px-6 py-8 text-center text-slate-500">No services found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($services as $s): ?>
                            <tr class="hover:bg-slate-800/30 transition"
                                x-show="(filterCategory === 'all' || filterCategory === '<?= Security::e($s['category_name']) ?>') && (!search || '<?= strtolower(addslashes($s['name'])) ?>'.includes(search.toLowerCase()) || '<?= (int)$s['id'] ?>'.includes(search))">
                                <td class="px-5 py-3.5 font-mono font-bold text-indigo-400">#<?= (int)$s['id'] ?></td>
                                <td class="px-5 py-3.5 text-slate-400"><?= Security::e($s['category_name']) ?></td>
                                <td class="px-5 py-3.5 font-semibold text-white max-w-sm truncate" title="<?= Security::e($s['name']) ?>">
                                    <?= Security::e($s['name']) ?>
                                </td>
                                <td class="px-5 py-3.5 text-slate-400">
                                    <?= Security::e($s['provider_name'] ?? 'Manual') ?>
                                    <?php if ($s['provider_service_id']): ?>
                                        <span class="font-mono text-[10px] text-slate-500">(#<?= Security::e($s['provider_service_id']) ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-3.5 font-bold text-white"><?= Format::currency((float)$s['price']) ?></td>
                                <td class="px-5 py-3.5 font-mono text-slate-400"><?= number_format((int)$s['min_quantity']) ?> / <?= number_format((int)$s['max_quantity']) ?></td>
                                <td class="px-5 py-3.5 whitespace-nowrap space-x-1">
                                    <?php if ($s['dripfeed_enabled'] == 1): ?>
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">Drip</span>
                                    <?php endif; ?>
                                    <?php if ($s['refill_enabled'] == 1): ?>
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Refill (<?= (int)$s['refill_days'] ?>d)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-3.5"><?= Format::badge($s['status']) ?></td>
                                <td class="px-5 py-3.5 text-right">
                                    <form action="/admin/services/delete/<?= (int)$s['id'] ?>" method="POST" class="inline" onsubmit="return confirm('Delete this service?');">
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

    <!-- Create Service Modal -->
    <div x-show="createServiceModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="createServiceModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="createServiceModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="createServiceModal" x-transition.scale class="inline-block align-bottom bg-slate-900 border border-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <h3 class="text-base font-bold text-white">Create New Service</h3>
                    <button @click="createServiceModal = false" class="text-slate-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="/admin/services/store" method="POST" class="space-y-4 text-xs">
                    <?= Csrf::field() ?>

                    <div>
                        <label class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">Service Name</label>
                        <input type="text" name="name" required placeholder="e.g. Instagram Followers [Super Fast]"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">Category</label>
                            <select name="category_id" required class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= (int)$cat['id'] ?>"><?= Security::e($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">Provider API</label>
                            <select name="provider_id" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs">
                                <option value="">None (Manual delivery)</option>
                                <?php foreach ($providers as $pr): ?>
                                    <option value="<?= (int)$pr['id'] ?>"><?= Security::e($pr['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">Provider Service ID</label>
                            <input type="text" name="provider_service_id" placeholder="e.g. 104"
                                   class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs">
                        </div>
                        <div>
                            <label class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">Rate / 1000 ($)</label>
                            <input type="number" step="0.0001" name="price" required placeholder="1.50"
                                   class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs">
                        </div>
                        <div>
                            <label class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">Service Type</label>
                            <select name="type" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs">
                                <option value="default">Default</option>
                                <option value="custom_comments">Custom Comments</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">Min Quantity</label>
                            <input type="number" name="min_quantity" value="10" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs">
                        </div>
                        <div>
                            <label class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">Max Quantity</label>
                            <input type="number" name="max_quantity" value="10000" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">Description & Rules</label>
                        <textarea name="description" rows="3" placeholder="High retention, instant start..."
                                  class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs"></textarea>
                    </div>

                    <div class="flex items-center space-x-6 pt-1">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="dripfeed_enabled" value="1" class="rounded bg-slate-950 border-slate-800 text-indigo-600">
                            <span class="text-slate-300">Allow Drip-feed</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="refill_enabled" value="1" class="rounded bg-slate-950 border-slate-800 text-indigo-600">
                            <span class="text-slate-300">Allow Refill</span>
                        </label>
                    </div>

                    <div class="pt-3 border-t border-slate-800 flex justify-end space-x-2">
                        <button type="button" @click="createServiceModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white">Save Service</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Create Category Modal -->
    <div x-show="createCategoryModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="createCategoryModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="createCategoryModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="createCategoryModal" x-transition.scale class="inline-block align-bottom bg-slate-900 border border-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <h3 class="text-base font-bold text-white">Add New Category</h3>
                    <button @click="createCategoryModal = false" class="text-slate-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="/admin/categories/store" method="POST" class="space-y-4">
                    <?= Csrf::field() ?>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Category Name</label>
                        <input type="text" name="name" required placeholder="e.g. TikTok Views & Shares"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Sort Order</label>
                        <input type="number" name="sort_order" value="10"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>

                    <div class="pt-2 flex justify-end space-x-2">
                        <button type="button" @click="createCategoryModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white">Create Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Price Margin Modal -->
    <div x-show="marginModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="marginModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="marginModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="marginModal" x-transition.scale class="inline-block align-bottom bg-slate-900 border border-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <h3 class="text-base font-bold text-white">Adjust Price Margin</h3>
                    <button @click="marginModal = false" class="text-slate-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="/admin/services/bulk-margin" method="POST" class="space-y-4">
                    <?= Csrf::field() ?>
                    <p class="text-xs text-slate-400">Increase or decrease all active service prices by a percentage. (e.g. enter <code class="text-emerald-400">20</code> to increase prices by 20%, or <code class="text-rose-400">-10</code> for a 10% discount).</p>
                    
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Percentage Change (%)</label>
                        <input type="number" step="0.5" name="margin_percentage" required placeholder="e.g. 15"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>

                    <div class="pt-2 flex justify-end space-x-2">
                        <button type="button" @click="marginModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-xs font-semibold text-white">Apply Adjustment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
