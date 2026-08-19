<?php $pageTitle = 'Admin - AdSense'; ?>
<h1 style="font-size:1.5rem;font-weight:700;margin-bottom:1.5rem">Google AdSense</h1>
<div class="card" style="max-width:600px">
<form method="POST" action="/admin/settings/adsense">
    <input type="hidden" name="_token" value="<?= e($_csrf) ?>">
    <label style="display:flex;align-items:center;gap:.5rem;font-size:.9rem;margin-bottom:1rem"><input type="checkbox" name="adsense_enabled" value="1" style="width:auto" <?= ($settings['adsense_enabled'] ?? '0') === '1' ? 'checked' : '' ?>> Enable AdSense</label>
    <div class="form-group"><label>Client ID</label><input type="text" name="adsense_client" value="<?= e($settings['adsense_client'] ?? '') ?>"></div>
    <div class="grid grid-2">
        <div class="form-group"><label>Header Slot</label><input type="text" name="adsense_slot_header" value="<?= e($settings['adsense_slot_header'] ?? '') ?>"></div>
        <div class="form-group"><label>Footer Slot</label><input type="text" name="adsense_slot_footer" value="<?= e($settings['adsense_slot_footer'] ?? '') ?>"></div>
    </div>
    <button type="submit" class="btn btn-accent">Save AdSense Settings</button>
</form>
</div>
