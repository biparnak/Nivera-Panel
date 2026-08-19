<?php $pageTitle = 'Admin Dashboard'; ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
    <h1 style="font-size:1.5rem;font-weight:700">Admin Dashboard</h1>
    <span style="color:var(--text2);font-size:.85rem">Panel: <?= e($panelType) ?> <?= $panelEnabled ? '<span class="badge badge-ok">Connected</span>' : '<span class="badge badge-warn">Simulator</span>' ?></span>
</div>

<div class="grid grid-4" style="margin-bottom:1.5rem">
    <div class="card stat"><div class="stat-num" style="color:var(--accent)"><?= $userCount ?></div><div class="stat-label">Users</div></div>
    <div class="card stat"><div class="stat-num" style="color:var(--ok)"><?= $serverCount ?></div><div class="stat-label">Servers</div></div>
    <div class="card stat"><div class="stat-num" style="color:var(--info)"><?= $orderCount ?></div><div class="stat-label">Orders</div></div>
    <div class="card stat"><div class="stat-num" style="color:var(--warn)"><?= '$' . number_format($revenue, 2) ?></div><div class="stat-label">Revenue</div></div>
</div>

<div class="grid grid-2">
    <div class="card">
        <h3 style="font-weight:600;margin-bottom:1rem">Recent Orders</h3>
        <?php if (empty($recentOrders)): ?><p style="color:var(--text2)">No orders.</p><?php else: ?>
        <table><thead><tr><th>#</th><th>User</th><th>Amount</th><th>Status</th></tr></thead><tbody>
        <?php foreach ($recentOrders as $o): ?>
        <tr><td><?= e($o->order_number) ?></td><td><?= e(($o->user()?->username ?? '-')) ?></td><td><?= '$' . number_format($o->amount, 2) ?></td><td><span class="badge <?= $o->status === 'paid' ? 'badge-ok' : 'badge-warn' ?>"><?= e(ucfirst($o->status)) ?></span></td></tr>
        <?php endforeach; ?>
        </tbody></table><?php endif; ?>
    </div>
    <div class="card">
        <h3 style="font-weight:600;margin-bottom:1rem">Recent Users</h3>
        <?php if (empty($recentUsers)): ?><p style="color:var(--text2)">No users.</p><?php else: ?>
        <table><thead><tr><th>User</th><th>Email</th><th>Joined</th></tr></thead><tbody>
        <?php foreach ($recentUsers as $u): ?>
        <tr><td style="font-weight:600"><?= e($u->username) ?></td><td style="color:var(--text2)"><?= e($u->email) ?></td><td style="color:var(--text2)"><?= timeAgo($u->created_at) ?></td></tr>
        <?php endforeach; ?>
        </tbody></table><?php endif; ?>
    </div>
</div>

<div class="card" style="margin-top:1rem">
    <h3 style="font-weight:600;margin-bottom:1rem">Server States</h3>
    <div style="display:flex;gap:1rem;flex-wrap:wrap">
        <?php foreach ($serverStates as $state => $count): ?>
        <div class="badge <?= $state === 'running' ? 'badge-ok' : ($state === 'stopped' ? 'badge-err' : 'badge-warn') ?>" style="padding:.5rem 1rem;font-size:.9rem"><?= e(ucfirst($state)) ?>: <?= $count ?></div>
        <?php endforeach; ?>
        <?php if (empty($serverStates)): ?><span style="color:var(--text2)">No servers yet.</span><?php endif; ?>
    </div>
</div>

<?php if (!empty($activities)): ?>
<div class="card" style="margin-top:1rem">
    <h3 style="font-weight:600;margin-bottom:1rem">Activity Log</h3>
    <?php foreach (array_slice($activities, 0, 10) as $a): ?>
    <div style="padding:.5rem 0;border-bottom:1px solid var(--border);font-size:.85rem">
        <span style="color:var(--accent);font-weight:500"><?= e($a['action'] ?? '') ?></span>
        <span style="color:var(--text2)"><?= e($a['description'] ?? '') ?></span>
        <span style="float:right;color:var(--text-muted)"><?= e($a['username'] ?? 'system') ?> &middot; <?= timeAgo($a['created_at']) ?></span>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
