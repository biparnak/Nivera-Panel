<?php $pageTitle = 'Admin - Products'; ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
    <h1 style="font-size:1.5rem;font-weight:700">Products</h1>
    <a href="/admin/products/create" class="btn btn-accent btn-sm">Create Product</a>
</div>
<div class="card">
<table>
<thead><tr><th>Name</th><th>Price/Mo</th><th>RAM</th><th>Disk</th><th>Category</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($products as $p): ?>
<tr>
    <td style="font-weight:600"><?= e($p->icon) ?> <?= e($p->name) ?></td>
    <td><?= '$' . number_format($p->price_monthly, 2) ?></td>
    <td><?= $p->memory_mb ?>MB</td>
    <td><?= $p->disk_mb ?>MB</td>
    <td><?= e($p->category()?->name ?? '-') ?></td>
    <td><span class="badge <?= $p->active ? 'badge-ok' : 'badge-err' ?>"><?= $p->active ? 'Active' : 'Inactive' ?></span></td>
    <td>
        <a href="/admin/products/<?= $p->id ?>/edit" class="btn btn-sm" style="background:transparent;border:1px solid var(--border);color:var(--text)">Edit</a>
        <form method="POST" action="/admin/products/<?= $p->id ?>/delete" style="display:inline" onsubmit="return confirm('Delete?')"><input type="hidden" name="_token" value="<?= e($_csrf) ?>"><button class="btn btn-err btn-sm">Delete</button></form>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
