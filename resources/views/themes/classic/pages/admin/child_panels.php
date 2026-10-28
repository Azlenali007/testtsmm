<?php
declare(strict_types=1);

use App\Core\Csrf;
use App\Helpers\Format;
use App\Helpers\Security;
?>
<div class="space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Child Panels</h1>
            <p class="text-xs text-slate-400 mt-1">Manage white-label reseller domains, subscriptions, and provisioning status</p>
        </div>
        <span class="text-xs text-slate-400">Total: <strong class="text-white"><?= count($panels) ?></strong> panels</span>
    </div>

    <!-- Panels Table -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/70 uppercase text-[11px] text-slate-400 border-b border-slate-800 font-semibold tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">ID</th>
                        <th class="px-5 py-3.5">User</th>
                        <th class="px-5 py-3.5">Domain</th>
                        <th class="px-5 py-3.5">Admin Username</th>
                        <th class="px-5 py-3.5">Monthly Fee</th>
                        <th class="px-5 py-3.5">Renewal Date</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    <?php if (empty($panels)): ?>
                        <tr><td colspan="8" class="px-6 py-8 text-center text-slate-500">No child panels registered.</td></tr>
                    <?php else: ?>
                        <?php foreach ($panels as $pan): ?>
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="px-5 py-4 font-mono font-bold text-indigo-400">#<?= (int)$pan['id'] ?></td>
                                <td class="px-5 py-4 font-semibold text-white"><?= Security::e($pan['username']) ?></td>
                                <td class="px-5 py-4 font-mono font-bold text-indigo-300"><?= Security::e($pan['domain']) ?></td>
                                <td class="px-5 py-4 text-slate-300"><?= Security::e($pan['admin_username']) ?></td>
                                <td class="px-5 py-4 font-bold text-white"><?= Format::currency((float)$pan['price']) ?></td>
                                <td class="px-5 py-4 text-slate-400"><?= Format::date($pan['renewal_date']) ?></td>
                                <td class="px-5 py-4"><?= Format::badge($pan['status']) ?></td>
                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    <form action="/admin/child-panels/status/<?= (int)$pan['id'] ?>" method="POST" class="inline">
                                        <?= Csrf::field() ?>
                                        <select name="status" onchange="this.form.submit()" class="px-2 py-1 rounded bg-slate-950 border border-slate-800 text-[11px] text-slate-300">
                                            <option value="pending" <?= $pan['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="active" <?= $pan['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                            <option value="suspended" <?= $pan['status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                                            <option value="terminated" <?= $pan['status'] === 'terminated' ? 'selected' : '' ?>>Terminated</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
