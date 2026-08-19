<?php $pageTitle = 'Admin - Categories'; ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
    <h1 style="font-size:1.5rem;font-weight:700">Categories</h1>
</div>
<div class="grid grid-2">
<div class="card">
    <h3 style="font-weight:600;margin-bottom:1rem">Create Category</h3>
    <form method="POST" action="/admin/categories/create">
        <input type="hidden" name="_token" value="<?= e($_csrf) ?>">
        <div class="form-group"><label>Name</label><input type="text" name="name" required></div>
        <div class="form-group"><label>Description</label><input type="text" name="description"></div>
        <div class="grid grid-2">
            <div class="form-group"><label>Icon (emoji)</label><input type="text" name="icon" value="📦"></div>
            <div class="form-group"><label style="display:flex;align-items:center;gap:.5rem;margin-top:1.5rem"><input type="checkbox" name="active" value="1" checked style="width:auto"> Active</label></div>
        </div>
        <button type="submit" class="btn btn-accent">Create</button>
    </form>
</div>
<div class="card">
    <h3 style="font-weight:600;margin-bottom:1rem">Existing Categories</h3>
    <?php foreach ($categories as $c): ?>
    <div style="display:flex;justify-content:space-between;align-items:center;padding:.5rem 0;border-bottom:1px solid var(--border)">
        <span><?= $c->icon ?? '📦' ?> <?= e($c->name) ?> <span class="badge <?= $c->active ? 'badge-ok' : 'badge-err' ?>"><?= $c->active ? 'Active' : 'Off' ?></span></span>
        <form method="POST" action="/admin/categories/<?= $c->id ?>/delete" onsubmit="return confirm('Delete?')"><input type="hidden" name="_token" value="<?= e($_csrf) ?>"><button class="btn btn-err btn-sm">Delete</button></form>
    </div>
    <?php endforeach; ?>
</div>
</div>
