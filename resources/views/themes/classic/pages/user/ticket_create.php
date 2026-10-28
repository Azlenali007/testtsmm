<?php
declare(strict_types=1);

use App\Core\Csrf;
?>
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 space-y-6">
    <div class="pb-4 border-b border-slate-800">
        <h1 class="text-2xl font-extrabold text-white tracking-tight">Open Support Ticket</h1>
        <p class="text-xs text-slate-400 mt-1">Our customer satisfaction team typically responds within 30 minutes</p>
    </div>

    <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 sm:p-8">
        <form action="/tickets/store" method="POST" class="space-y-5">
            <?= Csrf::field() ?>

            <div>
                <label for="priority" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Priority</label>
                <select id="priority" name="priority" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="low">Low — General Inquiry</option>
                    <option value="medium" selected>Medium — Order Delay / Tracking</option>
                    <option value="high">High — Urgent / Payment Issue</option>
                </select>
            </div>

            <div>
                <label for="subject" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Subject</label>
                <input type="text" id="subject" name="subject" required placeholder="Brief description of the problem..."
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div>
                <label for="message" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Message / Details</label>
                <textarea id="message" name="message" rows="6" required placeholder="Include relevant Order IDs or Transaction codes for faster resolution..."
                          class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
            </div>

            <div class="pt-4 border-t border-slate-800 flex items-center justify-between">
                <a href="/tickets" class="text-xs font-semibold text-slate-400 hover:text-white">&larr; Back to Tickets</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-lg shadow-indigo-600/30 transition">
                    Submit Ticket
                </button>
            </div>
        </form>
    </div>
</div>
