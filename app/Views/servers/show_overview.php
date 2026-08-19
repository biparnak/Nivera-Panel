<?php $pageTitle = e($server->name); $page = 'overview'; ?>
<?php require __DIR__ . '/show.php'; ?>

<?php
    $stateLabel = $state ?? $server->state;
    $badge = match($stateLabel) { 'running' => 'badge-ok', 'stopped', 'offline' => 'badge-err', 'starting', 'stopping', 'deploying' => 'badge-warn', default => 'badge-info' };
    $memPct = $resources ? ($resources['memory_limit'] > 0 ? round($resources['memory'] / 1048576 / $resources['memory_limit'] * 100, 1) : 0) : 0;
    $diskPct = $resources ? ($resources['disk_limit'] > 0 ? round($resources['disk'] / 1048576 / $resources['disk_limit'] * 100, 1) : 0) : 0;
    $cpuPct = $resources ? ($resources['cpu_limit'] > 0 ? round($resources['cpu'] / $resources['cpu_limit'] * 100, 1) : 0) : 0;
?>

<div class="grid grid-3" style="margin-bottom:1.5rem">
    <div class="card stat"><div class="stat-num"><span class="badge <?= $badge ?>" style="font-size:1rem;padding:.4rem 1rem"><?= e(ucfirst($stateLabel)) ?></span></div><div class="stat-label">Status</div></div>
    <div class="card stat"><div class="stat-num"><?= $server->memory_mb ?>MB</div><div class="stat-label">Memory</div></div>
    <div class="card stat"><div class="stat-num"><?= $server->cpu_percent ?>%</div><div class="stat-label">CPU Limit</div></div>
</div>

<div class="card" style="margin-bottom:1rem">
    <h3 style="font-weight:600;margin-bottom:1rem">Power Control</h3>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap">
        <form method="POST" action="/servers/<?= $server->id ?>/power" style="display:inline"><input type="hidden" name="_token" value="<?= e($_csrf) ?>"><input type="hidden" name="action" value="start"><button class="btn btn-ok btn-sm">Start</button></form>
        <form method="POST" action="/servers/<?= $server->id ?>/power" style="display:inline"><input type="hidden" name="_token" value="<?= e($_csrf) ?>"><input type="hidden" name="action" value="stop"><button class="btn btn-sm" style="background:var(--warn);color:#000">Stop</button></form>
        <form method="POST" action="/servers/<?= $server->id ?>/power" style="display:inline"><input type="hidden" name="_token" value="<?= e($_csrf) ?>"><input type="hidden" name="action" value="restart"><button class="btn btn-sm" style="background:var(--info);color:#fff">Restart</button></form>
        <form method="POST" action="/servers/<?= $server->id ?>/power" style="display:inline"><input type="hidden" name="_token" value="<?= e($_csrf) ?>"><input type="hidden" name="action" value="kill"><button class="btn btn-err btn-sm">Kill</button></form>
    </div>
</div>

<?php if ($resources): ?>
<div class="card" style="margin-bottom:1rem">
    <h3 style="font-weight:600;margin-bottom:1rem">Resources</h3>
    <div class="grid grid-3">
        <div><span style="color:var(--text2);font-size:.85rem">Memory</span><div style="background:var(--border);border-radius:4px;height:8px;margin-top:.4rem"><div style="background:var(--accent);height:100%;border-radius:4px;width:<?= min($memPct, 100) ?>%"></div></div><span style="font-size:.8rem;color:var(--text2)"><?= round($resources['memory']/1048576, 0) ?>MB / <?= $resources['memory_limit'] ?>MB</span></div>
        <div><span style="color:var(--text2);font-size:.85rem">Disk</span><div style="background:var(--border);border-radius:4px;height:8px;margin-top:.4rem"><div style="background:var(--ok);height:100%;border-radius:4px;width:<?= min($diskPct, 100) ?>%"></div></div><span style="font-size:.8rem;color:var(--text2)"><?= round($resources['disk']/1048576, 0) ?>MB / <?= $resources['disk_limit'] ?>MB</span></div>
        <div><span style="color:var(--text2);font-size:.85rem">CPU</span><div style="background:var(--border);border-radius:4px;height:8px;margin-top:.4rem"><div style="background:var(--warn);height:100%;border-radius:4px;width:<?= min($cpuPct, 100) ?>%"></div></div><span style="font-size:.8rem;color:var(--text2)"><?= round($resources['cpu'], 1) ?>% / <?= $resources['cpu_limit'] ?>%</span></div>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <h3 style="font-weight:600;margin-bottom:1rem">Quick Command</h3>
    <form id="cmdForm" style="display:flex;gap:.5rem">
        <input type="text" id="cmdInput" placeholder="Type a command..." style="flex:1" maxlength="200">
        <button type="submit" class="btn btn-accent btn-sm">Send</button>
    </form>
</div>

<script>
document.getElementById('cmdForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var cmd = document.getElementById('cmdInput').value;
    if (!cmd) return;
    fetch('/servers/<?= $server->id ?>/command', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: '_token=<?= e($_csrf) ?>&command=' + encodeURIComponent(cmd)
    }).then(function(r) { return r.json(); }).then(function(d) { document.getElementById('cmdInput').value = ''; });
});
</script>
