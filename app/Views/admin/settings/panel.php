<?php $pageTitle = 'Admin - Panel Settings'; ?>
<h1 style="font-size:1.5rem;font-weight:700;margin-bottom:1.5rem">Panel Integration Settings</h1>
<div class="card" style="max-width:800px">
    <p style="color:var(--text2);font-size:.9rem;margin-bottom:1rem">Current: <strong><?= e($panelType) ?></strong> <?= $panelEnabled ? '<span class="badge badge-ok">Connected</span>' : '<span class="badge badge-warn">Simulator</span>' ?></p>
    <button id="testBtn" class="btn btn-sm" style="background:var(--info);color:#fff;margin-bottom:1rem" onclick="testPanel()">Test Connection</button>
    <div id="testResult" style="margin-bottom:1rem"></div>
</div>
<div class="card" style="max-width:800px">
<form method="POST" action="/admin/settings/panel">
    <input type="hidden" name="_token" value="<?= e($_csrf) ?>">
    <div class="form-group"><label>Active Panel Type</label><select name="panel_type"><option value="pterodactyl" <?= ($settings['panel_type'] ?? '') === 'pterodactyl' ? 'selected' : '' ?>>Pterodactyl</option><option value="pufferpanel" <?= ($settings['panel_type'] ?? '') === 'pufferpanel' ? 'selected' : '' ?>>PufferPanel</option><option value="pelican" <?= ($settings['panel_type'] ?? '') === 'pelican' ? 'selected' : '' ?>>Pelican</option></select></div>
    <hr style="border:none;border-top:1px solid var(--border);margin:1.5rem 0">
    <h3 style="font-weight:600;margin-bottom:1rem">Pterodactyl / Pelican</h3>
    <div class="grid grid-2">
        <label style="display:flex;align-items:center;gap:.5rem;font-size:.9rem"><input type="checkbox" name="pterodactyl_enabled" value="1" style="width:auto" <?= ($settings['pterodactyl_enabled'] ?? '0') === '1' ? 'checked' : '' ?>> Enable Pterodactyl</label>
        <label style="display:flex;align-items:center;gap:.5rem;font-size:.9rem"><input type="checkbox" name="pelican_enabled" value="1" style="width:auto" <?= ($settings['pelican_enabled'] ?? '0') === '1' ? 'checked' : '' ?>> Enable Pelican</label>
    </div>
    <div class="form-group"><label>Panel URL</label><input type="text" name="pterodactyl_url" value="<?= e($settings['pterodactyl_url'] ?? '') ?>" placeholder="https://panel.example.com"></div>
    <div class="form-group"><label>API Key (Application Key)</label><input type="password" name="pterodactyl_api_key" value="<?= e($settings['pterodactyl_api_key'] ?? '') ?>"></div>
    <div class="grid grid-2">
        <div class="form-group"><label>Node ID</label><input type="number" name="pterodactyl_node_id" value="<?= $settings['pterodactyl_node_id'] ?? '1' ?>"></div>
        <div class="form-group"><label>Nest ID</label><input type="number" name="pterodactyl_nest_id" value="<?= $settings['pterodactyl_nest_id'] ?? '1' ?>"></div>
    </div>
    <div class="form-group"><label>Default Docker Image</label><input type="text" name="pterodactyl_default_docker_image" value="<?= e($settings['pterodactyl_default_docker_image'] ?? '') ?>"></div>
    <div class="form-group"><label>Default Startup Command</label><input type="text" name="pterodactyl_startup" value="<?= e($settings['pterodactyl_startup'] ?? '') ?>"></div>
    <hr style="border:none;border-top:1px solid var(--border);margin:1.5rem 0">
    <h3 style="font-weight:600;margin-bottom:1rem">PufferPanel</h3>
    <label style="display:flex;align-items:center;gap:.5rem;font-size:.9rem;margin-bottom:1rem"><input type="checkbox" name="pufferpanel_enabled" value="1" style="width:auto" <?= ($settings['pufferpanel_enabled'] ?? '0') === '1' ? 'checked' : '' ?>> Enable PufferPanel</label>
    <div class="form-group"><label>PufferPanel URL</label><input type="text" name="pufferpanel_url" value="<?= e($settings['pufferpanel_url'] ?? '') ?>" placeholder="https://panel.example.com"></div>
    <div class="grid grid-2">
        <div class="form-group"><label>Client Token</label><input type="password" name="pufferpanel_client_token" value="<?= e($settings['pufferpanel_client_token'] ?? '') ?>"></div>
        <div class="form-group"><label>Client Secret</label><input type="password" name="pufferpanel_client_secret" value="<?= e($settings['pufferpanel_client_secret'] ?? '') ?>"></div>
    </div>
    <hr style="border:none;border-top:1px solid var(--border);margin:1.5rem 0">
    <h3 style="font-weight:600;margin-bottom:1rem">Pelican (Standalone)</h3>
    <div class="form-group"><label>Pelican URL</label><input type="text" name="pelican_url" value="<?= e($settings['pelican_url'] ?? $settings['pterodactyl_url'] ?? '') ?>"></div>
    <div class="form-group"><label>Pelican API Key</label><input type="password" name="pelican_api_key" value="<?= e($settings['pelican_api_key'] ?? $settings['pterodactyl_api_key'] ?? '') ?>"></div>
    <button type="submit" class="btn btn-accent" style="margin-top:1rem">Save Panel Settings</button>
</form>
</div>
<script>
function testPanel(){fetch('/api/admin/panel/test',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},body:'_token=<?= e($_csrf) ?>'}).then(function(r){return r.json()}).then(function(d){document.getElementById('testResult').innerHTML='<div class="alert alert-'+(d.connected?'success':'error')+'">'+(d.connected?'Connected to '+d.type+'! Nests: '+d.nests+', Nodes: '+d.nodes:'Not connected. Check your settings.')+'</div>'})}
</script>
