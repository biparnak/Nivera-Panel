<?php $pageTitle = e($server->name) . ' - Server'; ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
    <h1 style="font-size:1.5rem;font-weight:700"><?= e($server->name) ?></h1>
    <div style="display:flex;gap:.5rem">
        <a href="/servers/<?= $server->id ?>" class="btn btn-sm <?= $page === 'overview' ? 'btn-accent' : '' ?>" style="background:<?= $page === 'overview' ? '' : 'transparent' ?>;color:var(--text);border:1px solid var(--border)">Overview</a>
        <a href="/servers/<?= $server->id ?>/console" class="btn btn-sm <?= $page === 'console' ? 'btn-accent' : '' ?>" style="background:<?= $page === 'console' ? '' : 'transparent' ?>;color:var(--text);border:1px solid var(--border)">Console</a>
        <a href="/servers/<?= $server->id ?>/settings" class="btn btn-sm <?= $page === 'settings' ? 'btn-accent' : '' ?>" style="background:<?= $page === 'settings' ? '' : 'transparent' ?>;color:var(--text);border:1px solid var(--border)">Settings</a>
    </div>
</div>
