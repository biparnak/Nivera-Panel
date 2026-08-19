<?php $pageTitle = 'Admin - General Settings'; ?>
<h1 style="font-size:1.5rem;font-weight:700;margin-bottom:1.5rem">General Settings</h1>
<div class="card" style="max-width:700px">
<form method="POST" action="/admin/settings">
    <input type="hidden" name="_token" value="<?= e($_csrf) ?>">
    <div class="grid grid-2">
        <div class="form-group"><label>Site Name</label><input type="text" name="site_name" value="<?= e($settings['site_name'] ?? '') ?>"></div>
        <div class="form-group"><label>Site Tagline</label><input type="text" name="site_tagline" value="<?= e($settings['site_tagline'] ?? '') ?>"></div>
    </div>
    <div class="form-group"><label>Site Description</label><textarea name="site_description"><?= e($settings['site_description'] ?? '') ?></textarea></div>
    <div class="grid grid-2">
        <div class="form-group"><label>Currency Symbol</label><input type="text" name="currency_symbol" value="<?= e($settings['currency_symbol'] ?? '$') ?>"></div>
        <div class="form-group"><label>Currency Code</label><input type="text" name="currency_code" value="<?= e($settings['currency_code'] ?? 'USD') ?>"></div>
    </div>
    <div class="form-group"><label>Footer Text</label><input type="text" name="footer_text" value="<?= e($settings['footer_text'] ?? '') ?>"></div>
    <div class="grid grid-2">
        <label style="display:flex;align-items:center;gap:.5rem;font-size:.9rem"><input type="checkbox" name="registration_enabled" value="1" style="width:auto" <?= ($settings['registration_enabled'] ?? '1') === '1' ? 'checked' : '' ?>> Enable Registration</label>
        <label style="display:flex;align-items:center;gap:.5rem;font-size:.9rem"><input type="checkbox" name="maintenance_mode" value="1" style="width:auto" <?= ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : '' ?>> Maintenance Mode</label>
    </div>
    <div class="form-group"><label>Maintenance Message</label><input type="text" name="maintenance_message" value="<?= e($settings['maintenance_message'] ?? '') ?>"></div>
    <div class="grid grid-2">
        <label style="display:flex;align-items:center;gap:.5rem;font-size:.9rem"><input type="checkbox" name="auto_deploy" value="1" style="width:auto" <?= ($settings['auto_deploy'] ?? '1') === '1' ? 'checked' : '' ?>> Auto Deploy Servers</label>
        <label style="display:flex;align-items:center;gap:.5rem;font-size:.9rem"><input type="checkbox" name="auto_create_user" value="1" style="width:auto" <?= ($settings['auto_create_user'] ?? '1') === '1' ? 'checked' : '' ?>> Auto-Create Panel Users</label>
    </div>
    <button type="submit" class="btn btn-accent" style="margin-top:1rem">Save Settings</button>
</form>
</div>
