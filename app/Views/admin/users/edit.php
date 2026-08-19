<?php $pageTitle = 'Edit User - ' . e($user->username); ?>
<h1 style="font-size:1.5rem;font-weight:700;margin-bottom:1.5rem">Edit User: <?= e($user->username) ?></h1>
<div class="grid grid-2">
<div class="card">
    <form method="POST" action="/admin/users/<?= $user->id ?>/edit">
        <input type="hidden" name="_token" value="<?= e($_csrf) ?>">
        <div class="form-group"><label>Email</label><input type="email" name="email" value="<?= e($user->email) ?>" required></div>
        <div class="form-group"><label>Role</label><select name="role"><option value="user" <?= $user->role === 'user' ? 'selected' : '' ?>>User</option><option value="admin" <?= $user->role === 'admin' ? 'selected' : '' ?>>Admin</option></select></div>
        <div class="form-group"><label>Status</label><select name="status"><option value="active" <?= $user->status === 'active' ? 'selected' : '' ?>>Active</option><option value="suspended" <?= $user->status === 'suspended' ? 'selected' : '' ?>>Suspended</option><option value="banned" <?= $user->status === 'banned' ? 'selected' : '' ?>>Banned</option></select></div>
        <div class="form-group"><label>Balance ($)</label><input type="number" name="balance" value="<?= $user->getBalance() ?>" step="0.01"></div>
        <div class="form-group"><label>New Password (blank to keep)</label><input type="password" name="password"></div>
        <button type="submit" class="btn btn-accent">Save Changes</button>
    </form>
</div>
<div>
    <div class="card">
        <h3 style="font-weight:600;margin-bottom:1rem">User Info</h3>
        <p style="font-size:.85rem;color:var(--text2)">ID: <?= $user->id ?></p>
        <p style="font-size:.85rem;color:var(--text2)">Joined: <?= e($user->created_at) ?></p>
        <p style="font-size:.85rem;color:var(--text2)">Last Login: <?= $user->last_login_at ? e($user->last_login_at) : 'Never' ?></p>
        <p style="font-size:.85rem;color:var(--text2)">Servers: <?= count($servers) ?></p>
        <p style="font-size:.85rem;color:var(--text2)">Orders: <?= count($orders) ?></p>
    </div>
    <div class="card" style="margin-top:1rem;border-color:var(--err)">
        <h3 style="font-weight:600;margin-bottom:1rem;color:var(--err)">Delete User</h3>
        <form method="POST" action="/admin/users/<?= $user->id ?>/delete" onsubmit="return confirm('Delete this user and all their data?')">
            <input type="hidden" name="_token" value="<?= e($_csrf) ?>">
            <button class="btn btn-err btn-sm">Delete User</button>
        </form>
    </div>
</div>
</div>
