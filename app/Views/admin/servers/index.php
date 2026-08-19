<?php $pageTitle = 'Admin - Servers'; ?>
<h1 style="font-size:1.5rem;font-weight:700;margin-bottom:1.5rem">Servers</h1>
<div class="card">
<table>
<thead><tr><th>Name</th><th>User</th><th>Panel</th><th>RAM/Disk</th><th>State</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($servers as $s): ?>
<tr>
    <td style="font-weight:600"><?= e($s['server']->name) ?></td>
    <td><?= e($s['user']?->username ?? '-') ?></td>
    <td><span class="badge badge-info"><?= e($s['server']->panel_type) ?></span></td>
    <td><?= $s['server']->memory_mb ?>MB / <?= $s['server']->disk_mb ?>MB</td>
    <td><span class="badge <?= $s['server']->state === 'running' ? 'badge-ok' : ($s['server']->state === 'suspended' ? 'badge-err' : 'badge-warn') ?>"><?= e(ucfirst($s['server']->state)) ?></span></td>
    <td style="display:flex;gap:.25rem">
        <?php if ($s['server']->state !== 'suspended'): ?>
        <form method="POST" action="/admin/servers/<?= $s['server']->id ?>/suspend" style="display:inline"><input type="hidden" name="_token" value="<?= e($_csrf) ?>"><button class="btn btn-sm" style="background:var(--warn);color:#000">Suspend</button></form>
        <?php else: ?>
        <form method="POST" action="/admin/servers/<?= $s['server']->id ?>/unsuspend" style="display:inline"><input type="hidden" name="_token" value="<?= e($_csrf) ?>"><button class="btn btn-ok btn-sm">Unsuspend</button></form>
        <?php endif; ?>
        <form method="POST" action="/admin/servers/<?= $s['server']->id ?>/delete" style="display:inline" onsubmit="return confirm('Delete?')"><input type="hidden" name="_token" value="<?= e($_csrf) ?>"><button class="btn btn-err btn-sm">Delete</button></form>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
