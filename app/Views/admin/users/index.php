<?php $pageTitle = 'Admin - Users'; ?>
<h1 style="font-size:1.5rem;font-weight:700;margin-bottom:1.5rem">Users</h1>
<div class="card">
<form method="GET" style="margin-bottom:1rem;display:flex;gap:.5rem">
    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search users..." style="flex:1">
    <button class="btn btn-accent btn-sm">Search</button>
</form>
<table>
<thead><tr><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Balance</th><th>Joined</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($users as $u): ?>
<tr>
    <td style="font-weight:600"><?= e($u->username) ?></td>
    <td style="color:var(--text2)"><?= e($u->email) ?></td>
    <td><span class="badge <?= $u->role === 'admin' ? 'badge-info' : '' ?>"><?= e(ucfirst($u->role)) ?></span></td>
    <td><span class="badge <?= $u->status === 'active' ? 'badge-ok' : 'badge-err' ?>"><?= e(ucfirst($u->status)) ?></span></td>
    <td><?= '$' . number_format($u->getBalance(), 2) ?></td>
    <td style="color:var(--text2)"><?= timeAgo($u->created_at) ?></td>
    <td><a href="/admin/users/<?= $u->id ?>/edit" class="btn btn-sm" style="background:transparent;border:1px solid var(--border);color:var(--text)">Edit</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
