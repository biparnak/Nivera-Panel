<?php $pageTitle = e($server->name) . ' - Settings'; $page = 'settings'; ?>
<?php require __DIR__ . '/show_header.php'; ?>

<div class="card" style="max-width:600px">
    <h3 style="font-weight:600;margin-bottom:1rem">Rename Server</h3>
    <form method="POST" action="/servers/<?= $server->id ?>/rename" style="display:flex;gap:.5rem">
        <input type="hidden" name="_token" value="<?= e($_csrf) ?>">
        <input type="text" name="name" value="<?= e($server->name) ?>" required style="flex:1">
        <button type="submit" class="btn btn-accent btn-sm">Rename</button>
    </form>
</div>

<div class="card" style="max-width:600px;margin-top:1rem">
    <h3 style="font-weight:600;margin-bottom:1rem">Server Details</h3>
    <table>
        <tr><td style="color:var(--text2)">Panel</td><td><?= e($server->panel_type) ?></td></tr>
        <tr><td style="color:var(--text2)">Memory</td><td><?= $server->memory_mb ?>MB</td></tr>
        <tr><td style="color:var(--text2)">Disk</td><td><?= $server->disk_mb ?>MB</td></tr>
        <tr><td style="color:var(--text2)">CPU</td><td><?= $server->cpu_percent ?>%</td></tr>
        <tr><td style="color:var(--text2)">Created</td><td><?= e($server->created_at) ?></td></tr>
        <?php if ($server->expires_at): ?><tr><td style="color:var(--text2)">Expires</td><td><?= e($server->expires_at) ?></td></tr><?php endif; ?>
    </table>
</div>

<div class="card" style="max-width:600px;margin-top:1rem;border-color:var(--err)">
    <h3 style="font-weight:600;margin-bottom:1rem;color:var(--err)">Danger Zone</h3>
    <p style="color:var(--text2);font-size:.9rem;margin-bottom:1rem">This will permanently delete your server and all its data.</p>
    <form method="POST" action="/servers/<?= $server->id ?>/delete" onsubmit="return confirm('Are you sure? This cannot be undone.')">
        <input type="hidden" name="_token" value="<?= e($_csrf) ?>">
        <button type="submit" class="btn btn-err btn-sm">Delete Server</button>
    </form>
</div>
