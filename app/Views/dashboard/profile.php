<?php $pageTitle = 'Profile'; ?>
<h1 style="font-size:1.5rem;font-weight:700;margin-bottom:1.5rem">Profile Settings</h1>
<div class="card" style="max-width:600px">
    <form method="POST" action="/dashboard/profile">
        <input type="hidden" name="_token" value="<?= e($_csrf) ?>">
        <div class="form-group"><label>Email</label><input type="email" name="email" value="<?= e($_user->email) ?>" required></div>
        <hr style="border:none;border-top:1px solid var(--border);margin:1rem 0">
        <p style="color:var(--text2);font-size:.85rem;margin-bottom:1rem">Leave blank to keep current password.</p>
        <div class="form-group"><label>Current Password</label><input type="password" name="current_password"></div>
        <div class="form-group"><label>New Password</label><input type="password" name="new_password" minlength="8"></div>
        <button type="submit" class="btn btn-accent">Save Changes</button>
    </form>
</div>
