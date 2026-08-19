<?php $pageTitle = e($server->name); $page = 'overview'; ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
    <h1 style="font-size:1.5rem;font-weight:700"><?= e($server->name) ?></h1>
    <div style="display:flex;gap:.5rem">
        <a href="/servers/<?= $server->id ?>" class="btn btn-sm" style="background:var(--accent);color:#fff">Overview</a>
        <a href="/servers/<?= $server->id ?>/console" class="btn btn-sm" style="background:transparent;color:var(--text);border:1px solid var(--border)">Console</a>
        <a href="/servers/<?= $server->id ?>/settings" class="btn btn-sm" style="background:transparent;color:var(--text);border:1px solid var(--border)">Settings</a>
    </div>
</div>
