<?php
declare(strict_types=1);

use App\Core\Csrf;
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8"
     x-data="{
         categories: <?= htmlspecialchars(json_encode($categories), ENT_QUOTES, 'UTF-8') ?>,
         services: <?= htmlspecialchars(json_encode($services), ENT_QUOTES, 'UTF-8') ?>,
         selectedCategory: '<?= !empty($categories) ? (int)$categories[0]['id'] : '' ?>',
         selectedServiceId: <?= (int)$preselectedService ?: (!empty($services) ? (int)$services[0]['id'] : 0) ?>,
         quantity: 1000,
         isDripfeed: false,
         runs: 2,
         interval: 60,
         init() {
             if (this.selectedServiceId > 0) {
                 let s = this.services.find(item => item.id == this.selectedServiceId);
                 if (s) {
                     this.selectedCategory = s.category_id;
                     this.quantity = s.min_quantity;
                 }
             }
         },
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

    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">New Order</h1>
            <p class="text-xs text-slate-400 mt-1">Select your desired package and enter your target URL</p>
        </div>
        <div class="flex space-x-3">
            <a href="/orders/mass" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-slate-300 hover:text-white text-xs font-semibold transition">
                Mass Order
            </a>
            <a href="/services" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-slate-300 hover:text-white text-xs font-semibold transition">
                Services Price List
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Form Panel -->
        <div class="lg:col-span-2 rounded-2xl bg-slate-900 border border-slate-800 p-6 sm:p-8">
            <form action="/order/new" method="POST" class="space-y-5">
                <?= Csrf::field() ?>

                <!-- Category -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Category</label>
                    <select x-model="selectedCategory" @change="onCategoryChange()" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <template x-for="c in categories" :key="c.id">
                            <option :value="c.id" x-text="c.name"></option>
                        </template>
                    </select>
                </div>

                <!-- Service -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Service</label>
                    <select name="service_id" x-model="selectedServiceId" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <template x-for="s in currentServices" :key="s.id">
                            <option :value="s.id" x-text="s.name + ' — $' + parseFloat(s.price).toFixed(2) + ' / 1k'"></option>
                        </template>
                    </select>
                </div>

                <!-- Target Link -->
                <div>
                    <label for="link" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Target Link</label>
                    <input type="text" id="link" name="link" required placeholder="https://instagram.com/p/xxx"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm placeholder-slate-600 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <!-- Custom Comments -->
                <div x-show="currentService && currentService.type === 'custom_comments'" class="space-y-1.5">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Custom Comments (one per line)</label>
                    <textarea name="comments" rows="4" placeholder="Great post!&#10;Incredible work!&#10;Highly recommended"
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

                <!-- Drip-feed Options -->
                <div x-show="currentService && currentService.dripfeed_enabled == 1" class="pt-3 border-t border-slate-800 space-y-4">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" name="dripfeed" x-model="isDripfeed" class="w-4 h-4 rounded text-indigo-600 bg-slate-950 border-slate-700 focus:ring-indigo-500">
                        <span class="text-sm font-medium text-slate-300">Enable Drip-feed (Spread out delivery)</span>
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

                <!-- Submit & Charge Box -->
                <div class="pt-4 border-t border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <span class="text-xs text-slate-400 block">Total Estimated Charge:</span>
                        <div class="text-2xl font-black text-indigo-400">$<span x-text="totalPrice">0.00</span></div>
                    </div>

                    <button type="submit" class="px-8 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-lg shadow-indigo-600/30 transition duration-150">
                        Submit Order
                    </button>
                </div>
            </form>
        </div>

        <!-- Sidebar Description Panel -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 flex flex-col justify-between">
            <div class="space-y-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Service Overview</h3>

                <div x-show="currentService" class="space-y-4 text-xs">
                    <div>
                        <span class="text-slate-500 uppercase tracking-wider block">Service</span>
                        <span class="text-white font-semibold text-sm mt-0.5 block" x-text="currentService?.name"></span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-3 border-t border-slate-800">
                        <div>
                            <span class="text-slate-500 uppercase block">Rate / 1k</span>
                            <span class="text-indigo-400 font-bold text-sm block mt-0.5" x-text="'$' + parseFloat(currentService?.price || 0).toFixed(2)"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 uppercase block">Refill Button</span>
                            <span class="font-bold text-sm block mt-0.5" :class="currentService?.refill_enabled == 1 ? 'text-emerald-400' : 'text-slate-500'" x-text="currentService?.refill_enabled == 1 ? 'Available' : 'No Refill'"></span>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-800">
                        <span class="text-slate-500 uppercase block mb-1">Service Rules</span>
                        <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 text-slate-300 whitespace-pre-line leading-relaxed"
                             x-text="currentService?.description || 'Ensure account is public. Do not change username or place double orders simultaneously.'"></div>
                    </div>
                </div>

                <div x-show="!currentService" class="text-slate-500 text-center py-6 text-xs">
                    Please select a service to view specs.
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-800 text-xs text-slate-400">
                <span>Need custom API integration? Check out our <a href="/api-docs" class="text-indigo-400 underline font-semibold">API Docs</a>.</span>
            </div>
        </div>
    </div>
</div>
