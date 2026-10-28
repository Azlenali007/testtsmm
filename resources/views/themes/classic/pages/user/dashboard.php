<?php
declare(strict_types=1);

use App\Core\Csrf;
use App\Helpers\Format;
use App\Helpers\Security;
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    
    <!-- System Announcements / Broadcasts -->
    <?php if (!empty($broadcasts)): ?>
        <div class="space-y-3">
            <?php foreach ($broadcasts as $b): ?>
                <div class="rounded-xl border p-4 flex items-start space-x-3.5 <?= $b['type'] === 'danger' ? 'bg-rose-500/10 border-rose-500/20 text-rose-300' : ($b['type'] === 'warning' ? 'bg-amber-500/10 border-amber-500/20 text-amber-300' : 'bg-indigo-500/10 border-indigo-500/20 text-indigo-300') ?>">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div class="flex-1 text-sm">
                        <span class="font-bold text-white"><?= Security::e($b['title']) ?>:</span>
                        <span class="ml-1 opacity-90"><?= Security::e($b['content'] ?? $b['message'] ?? '') ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- User KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Balance Card -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 p-5 flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Account Balance</span>
                <div class="text-2xl font-black text-white mt-1"><?= Format::currency((float)($user['balance'] ?? 0.00)) ?></div>
                <a href="/wallet" class="inline-block mt-2 text-xs font-semibold text-indigo-400 hover:text-indigo-300">+ Deposit Funds</a>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
        </div>

        <!-- Total Orders Card -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 p-5 flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Orders</span>
                <div class="text-2xl font-black text-white mt-1"><?= number_format((int)($stats['total_orders'] ?? 0)) ?></div>
                <a href="/orders" class="inline-block mt-2 text-xs font-semibold text-slate-400 hover:text-white">View all history &rarr;</a>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
        </div>

        <!-- Active Orders Card -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 p-5 flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Processing</span>
                <div class="text-2xl font-black text-amber-400 mt-1"><?= number_format((int)($stats['active_orders'] ?? 0)) ?></div>
                <span class="inline-block mt-2 text-xs text-slate-500">Queued & In-progress</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Completed Orders Card -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 p-5 flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Completed</span>
                <div class="text-2xl font-black text-emerald-400 mt-1"><?= number_format((int)($stats['completed_orders'] ?? 0)) ?></div>
                <span class="inline-block mt-2 text-xs text-slate-500">Delivered successfully</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    <!-- Quick New Order & Service Description Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8"
         x-data="{
             categories: <?= htmlspecialchars(json_encode($categories), ENT_QUOTES, 'UTF-8') ?>,
             services: <?= htmlspecialchars(json_encode($services), ENT_QUOTES, 'UTF-8') ?>,
             selectedCategory: '<?= !empty($categories) ? (int)$categories[0]['id'] : '' ?>',
             selectedServiceId: '<?= !empty($services) ? (int)$services[0]['id'] : '' ?>',
             quantity: 1000,
             isDripfeed: false,
             runs: 2,
             interval: 60,
             get currentServices() {
                 return this.services.filter(s => s.category_id == this.selectedCategory);
             },
             get currentService() {
                 return this.services.find(s => s.id == this.selectedServiceId) || this.currentServices[0] || null;
             },
             get totalPrice() {
                 if (!this.currentService) return '0.00';
                 let rate = parseFloat(this.currentService.price) || 0;
                 let qty = parseInt(this.quantity) || 0;
                 let totalQty = this.isDripfeed ? qty * (parseInt(this.runs) || 1) : qty;
                 return ((rate * totalQty) / 1000).toFixed(4);
             },
             onCategoryChange() {
                 if (this.currentServices.length > 0) {
                     this.selectedServiceId = this.currentServices[0].id;
                     this.quantity = this.currentServices[0].min_quantity || 100;
                 }
             }
         }">
         
        <!-- Order Placement Form (2 Cols) -->
        <div class="lg:col-span-2 rounded-2xl bg-slate-900 border border-slate-800 p-6 sm:p-8">
            <div class="flex items-center justify-between pb-5 border-b border-slate-800">
                <div>
                    <h2 class="text-lg font-bold text-white">Place New Order</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Direct API order dispatch with automated delivery</p>
                </div>
                <a href="/orders/mass" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">
                    Switch to Mass Order &rarr;
                </a>
            </div>

            <form action="/order/new" method="POST" class="mt-6 space-y-5">
                <?= Csrf::field() ?>

                <!-- Category Select -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Category</label>
                    <select x-model="selectedCategory" @change="onCategoryChange()" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <template x-for="c in categories" :key="c.id">
                            <option :value="c.id" x-text="c.name"></option>
                        </template>
                    </select>
                </div>

                <!-- Service Select -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Service</label>
                    <select name="service_id" x-model="selectedServiceId" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <template x-for="s in currentServices" :key="s.id">
                            <option :value="s.id" x-text="s.name + ' — $' + parseFloat(s.price).toFixed(2) + ' per 1k'"></option>
                        </template>
                    </select>
                </div>

                <!-- Link / Target -->
                <div>
                    <label for="link" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Target Link</label>
                    <input type="text" id="link" name="link" required placeholder="https://instagram.com/p/xxx or username"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm placeholder-slate-600 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <!-- Custom Comments (if custom_comments) -->
                <div x-show="currentService && currentService.type === 'custom_comments'" class="space-y-1.5">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Custom Comments (one per line)</label>
                    <textarea name="comments" rows="4" placeholder="Nice post!&#10;Great photo!&#10;Love this"
                              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                </div>

                <!-- Quantity -->
                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label for="quantity" class="text-xs font-semibold uppercase tracking-wider text-slate-300">Quantity</label>
                        <span class="text-xs text-slate-500" x-show="currentService">
                            Min: <span class="text-slate-300" x-text="currentService?.min_quantity"></span> | 
                            Max: <span class="text-slate-300" x-text="Number(currentService?.max_quantity).toLocaleString()"></span>
                        </span>
                    </div>
                    <input type="number" id="quantity" name="quantity" x-model="quantity"
                           :min="currentService?.min_quantity || 10"
                           :max="currentService?.max_quantity || 1000000"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <!-- Drip-feed Toggle -->
                <div x-show="currentService && currentService.dripfeed_enabled == 1" class="pt-2 border-t border-slate-800/80 space-y-4">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" name="dripfeed" x-model="isDripfeed" class="w-4 h-4 rounded text-indigo-600 bg-slate-950 border-slate-700 focus:ring-indigo-500">
                        <span class="text-sm font-medium text-slate-300">Enable Drip-feed Delivery</span>
                    </label>

                    <div x-show="isDripfeed" class="grid grid-cols-2 gap-4 pl-7">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1">Runs</label>
                            <input type="number" name="runs" x-model="runs" min="2" max="100" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1">Interval (minutes)</label>
                            <input type="number" name="interval" x-model="interval" min="10" max="1440" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm">
                        </div>
                    </div>
                </div>

                <!-- Price Calculator & Submit Button -->
                <div class="pt-4 border-t border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <span class="text-xs text-slate-400 block">Total Calculated Charge:</span>
                        <div class="text-2xl font-black text-indigo-400">$<span x-text="totalPrice">0.00</span></div>
                    </div>

                    <button type="submit" class="px-8 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-lg shadow-indigo-600/30 transition duration-150">
                        Submit Order
                    </button>
                </div>
            </form>
        </div>

        <!-- Service Description & Details Panel (1 Col) -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 flex flex-col justify-between">
            <div>
                <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Service Details</h3>
                
                <div x-show="currentService" class="space-y-4 text-xs">
                    <div>
                        <span class="text-slate-500 block uppercase tracking-wider">Service Name</span>
                        <span class="text-slate-200 font-semibold text-sm mt-0.5 block" x-text="currentService?.name"></span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-3 border-t border-slate-800">
                        <div>
                            <span class="text-slate-500 uppercase">Rate per 1,000</span>
                            <span class="text-indigo-400 font-bold block text-sm mt-0.5" x-text="'$' + parseFloat(currentService?.price || 0).toFixed(2)"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 uppercase">Speed / Type</span>
                            <span class="text-slate-300 font-semibold block text-sm mt-0.5" x-text="currentService?.type || 'Default'"></span>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-800">
                        <span class="text-slate-500 uppercase block mb-1">Description & Guidelines</span>
                        <p class="text-slate-300 leading-relaxed whitespace-pre-line bg-slate-950/70 p-3 rounded-xl border border-slate-800/80"
                           x-text="currentService?.description || 'Instant automated start. Ensure account is set to public.'"></p>
                    </div>
                </div>

                <div x-show="!currentService" class="text-xs text-slate-500 py-6 text-center">
                    Select a service to view full specifications.
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-800 text-xs text-slate-400 flex items-center space-x-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span>Automated refill protection enabled.</span>
            </div>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden">
        <div class="p-6 border-b border-slate-800 flex items-center justify-between">
            <h3 class="text-base font-bold text-white">Recent Orders</h3>
            <a href="/orders" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">View All Orders &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950/60 text-xs uppercase text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-3 font-semibold">ID</th>
                        <th class="px-6 py-3 font-semibold">Service</th>
                        <th class="px-6 py-3 font-semibold">Quantity</th>
                        <th class="px-6 py-3 font-semibold">Charge</th>
                        <th class="px-6 py-3 font-semibold">Date</th>
                        <th class="px-6 py-3 font-semibold">Status</th>
                        <th class="px-6 py-3 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    <?php if (empty($recentOrders)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-500 text-xs">
                                No recent orders found. Place your first order above!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentOrders as $ro): ?>
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="px-6 py-4 font-mono text-xs text-indigo-400 font-bold">#<?= (int)$ro['id'] ?></td>
                                <td class="px-6 py-4 text-xs font-medium text-white max-w-xs truncate"><?= Security::e($ro['service_name'] ?? 'Custom Service') ?></td>
                                <td class="px-6 py-4 text-xs font-mono"><?= number_format((int)$ro['quantity']) ?></td>
                                <td class="px-6 py-4 text-xs font-semibold text-white"><?= Format::currency((float)$ro['charge']) ?></td>
                                <td class="px-6 py-4 text-xs text-slate-400"><?= Format::date($ro['created_at']) ?></td>
                                <td class="px-6 py-4"><?= Format::badge($ro['status']) ?></td>
                                <td class="px-6 py-4 text-right">
                                    <?php if (!empty($ro['service_refill_enabled']) || ($ro['refill_status'] ?? '') === 'eligible' || $ro['status'] === 'completed'): ?>
                                        <form action="/order/<?= (int)$ro['id'] ?>/refill" method="POST" class="inline">
                                            <?= Csrf::field() ?>
                                            <button type="submit" class="px-2.5 py-1 rounded bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-300 text-xs font-semibold transition">
                                                Refill
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-slate-600 text-xs">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
