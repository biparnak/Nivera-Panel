<?php $pageTitle = 'Dashboard'; ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
    <h1 style="font-size:1.5rem;font-weight:700">Dashboard</h1>
    <span style="color:var(--text2)">Welcome back, <?= e($_user->username) ?></span>
</div>

<div class="grid grid-4" style="margin-bottom:1.5rem">
    <div class="card stat"><div class="stat-num" style="color:var(--accent)"><?= $serverCount ?></div><div class="stat-label">Servers</div></div>
    <div class="card stat"><div class="stat-num" style="color:var(--ok)"><?= '$' . number_format($_user->getBalance(), 2) ?></div><div class="stat-label">Balance</div></div>
    <div class="card stat"><div class="stat-num" style="color:var(--info)"><?= count($orders) ?></div><div class="stat-label">Orders</div></div>
    <div class="card stat"><div class="stat-num" style="color:var(--warn)"><?= $_user->created_at ? timeAgo($_user->created_at) : 'New' ?></div><div class="stat-label">Member Since</div></div>
</div>

<?php if (!empty($announcements)): foreach ($announcements as $ann): ?>
<div class="alert alert-info"><strong><?= e($ann->title) ?>:</strong> <?= nl2br(e($ann->content)) ?></div>
<?php endforeach; endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
    <h2 style="font-size:1.1rem;font-weight:600">Your Servers</h2>
    <a href="/products" class="btn btn-accent btn-sm">Order New</a>
</div>

<?php if (empty($servers)): ?>
<div class="card empty">No servers yet. <a href="/products">Browse products</a> to get started.</div>
<?php else: ?>
<div class="grid grid-2">
    <?php foreach ($servers as $server): ?>
    <a href="/servers/<?= $server->id ?>" class="card" style="text-decoration:none;display:flex;justify-content:space-between;align-items:center">
        <div>
            <div style="font-weight:700;margin-bottom:.25rem"><?= e($server->name) ?></div>
            <div style="color:var(--text2);font-size:.85rem"><?= $server->memory_mb ?>MB RAM / <?= $server->disk_mb ?>MB Disk</div>
        </div>
        <?php
            $stateLabel = $serverStates[$server->id] ?? $server->state;
            $badge = match($stateLabel) { 'running' => 'badge-ok', 'stopped', 'offline' => 'badge-err', 'starting', 'stopping', 'deploying' => 'badge-warn', default => 'badge-info' };
        ?>
        <span class="badge <?= $badge ?>"><?= e($stateLabel) ?></span>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>
