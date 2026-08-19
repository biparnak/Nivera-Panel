<?php $pageTitle = 'Admin - Email Settings'; ?>
<h1 style="font-size:1.5rem;font-weight:700;margin-bottom:1.5rem">Email / SMTP Settings</h1>
<div class="card" style="max-width:600px">
<form method="POST" action="/admin/settings/email">
    <input type="hidden" name="_token" value="<?= e($_csrf) ?>">
    <label style="display:flex;align-items:center;gap:.5rem;font-size:.9rem;margin-bottom:1rem"><input type="checkbox" name="mail_enabled" value="1" style="width:auto" <?= ($settings['mail_enabled'] ?? '0') === '1' ? 'checked' : '' ?>> Enable Email Sending</label>
    <div class="form-group"><label>SMTP Host</label><input type="text" name="smtp_host" value="<?= e($settings['smtp_host'] ?? '') ?>"></div>
    <div class="grid grid-2">
        <div class="form-group"><label>SMTP Port</label><input type="number" name="smtp_port" value="<?= $settings['smtp_port'] ?? '587' ?>"></div>
        <div class="form-group"><label>From Email</label><input type="email" name="smtp_from" value="<?= e($settings['smtp_from'] ?? '') ?>"></div>
    </div>
    <div class="form-group"><label>SMTP Username</label><input type="text" name="smtp_user" value="<?= e($settings['smtp_user'] ?? '') ?>"></div>
    <div class="form-group"><label>SMTP Password</label><input type="password" name="smtp_pass" value="<?= e($settings['smtp_pass'] ?? '') ?>"></div>
    <button type="submit" class="btn btn-accent">Save Email Settings</button>
</form>
</div>
