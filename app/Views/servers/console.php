<?php $pageTitle = e($server->name) . ' - Console'; $page = 'console'; ?>
<?php require __DIR__ . '/show_header.php'; ?>

<div class="card" style="margin-bottom:1rem">
    <h3 style="font-weight:600;margin-bottom:1rem">Live Console</h3>
    <div id="console" style="background:#000;border-radius:8px;padding:1rem;font-family:monospace;font-size:.85rem;color:#22c55e;min-height:300px;max-height:500px;overflow-y:auto;white-space:pre-wrap;word-break:break-all">
        [NiveraCloud] Console loaded. State: <?= e($state ?? 'unknown') ?>.
        <?php if ($simulator): ?>[Simulator] No live panel connected.<?php endif; ?>
    </div>
</div>

<div class="card">
    <form id="consoleForm" style="display:flex;gap:.5rem">
        <input type="text" id="consoleInput" placeholder="Send command..." style="flex:1" maxlength="200">
        <button type="submit" class="btn btn-accent btn-sm">Send</button>
    </form>
</div>

<script>
var console = document.getElementById('console');
console.scrollTop = console.scrollHeight;
document.getElementById('consoleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var input = document.getElementById('consoleInput');
    var cmd = input.value.trim();
    if (!cmd) return;
    console.innerHTML += '\n> ' + cmd;
    console.scrollTop = console.scrollHeight;
    fetch('/servers/<?= $server->id ?>/command', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: '_token=<?= e($_csrf) ?>&command=' + encodeURIComponent(cmd)
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.ok) { console.innerHTML += '\n[OK] Command sent.'; } else { console.innerHTML += '\n[ERR] ' + (d.message || 'Failed'); }
        console.scrollTop = console.scrollHeight;
    });
    input.value = '';
});
<?php if (!$simulator && $ws): ?>
var wsProtocol = location.protocol === 'https:' ? 'wss:' : 'ws:';
var wsUrl = '<?= e($ws['socket']) ?>';
try {
    var socket = new WebSocket(wsUrl + '?token=<?= e($ws['token']) ?>');
    socket.onmessage = function(e) { console.innerHTML += '\n' + e.data; console.scrollTop = console.scrollHeight; };
    socket.onopen = function() { console.innerHTML += '\n[WS] Connected to console.'; console.scrollTop = console.scrollHeight; };
    socket.onerror = function() { console.innerHTML += '\n[WS] Connection error.'; };
} catch(ex) { console.innerHTML += '\n[WS] Could not connect: ' + ex.message; }
<?php endif; ?>
</script>
