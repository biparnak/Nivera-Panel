<?php $pageTitle = $product ? 'Edit Product' : 'Create Product'; ?>
<h1 style="font-size:1.5rem;font-weight:700;margin-bottom:1.5rem"><?= $pageTitle ?></h1>
<div class="card" style="max-width:800px">
<form method="POST" action="<?= $product ? '/admin/products/' . $product->id . '/edit' : '/admin/products/create' ?>">
    <input type="hidden" name="_token" value="<?= e($_csrf) ?>">
    <div class="grid grid-2">
        <div class="form-group"><label>Product Name</label><input type="text" name="name" value="<?= e($product->name ?? '') ?>" required></div>
        <div class="form-group"><label>Icon (emoji)</label><input type="text" name="icon" value="<?= e($product->icon ?? '🖥') ?>"></div>
    </div>
    <div class="form-group"><label>Description</label><textarea name="description"><?= e($product->description ?? '') ?></textarea></div>
    <div class="grid grid-3">
        <div class="form-group"><label>Category</label><select name="category_id"><option value="">None</option><?php foreach ($categories as $c): ?><option value="<?= $c->id ?>" <?= ($product->category_id ?? '') == $c->id ? 'selected' : '' ?>><?= e($c->name) ?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label>Price Monthly ($)</label><input type="number" name="price_monthly" value="<?= $product->price_monthly ?? 0 ?>" step="0.01"></div>
        <div class="form-group"><label>Price Yearly ($)</label><input type="number" name="price_yearly" value="<?= $product->price_yearly ?? 0 ?>" step="0.01"></div>
    </div>
    <div class="grid grid-4">
        <div class="form-group"><label>Memory (MB)</label><input type="number" name="memory_mb" value="<?= $product->memory_mb ?? 1024 ?>"></div>
        <div class="form-group"><label>Disk (MB)</label><input type="number" name="disk_mb" value="<?= $product->disk_mb ?? 5120 ?>"></div>
        <div class="form-group"><label>CPU (%)</label><input type="number" name="cpu_percent" value="<?= $product->cpu_percent ?? 100 ?>"></div>
        <div class="form-group"><label>Slots</label><input type="number" name="slots" value="<?= $product->slots ?? 0 ?>"></div>
    </div>
    <div class="grid grid-4">
        <div class="form-group"><label>Swap (MB)</label><input type="number" name="swap_mb" value="<?= $product->swap_mb ?? 512 ?>"></div>
        <div class="form-group"><label>IO</label><input type="number" name="io_percent" value="<?= $product->io_percent ?? 500 ?>"></div>
        <div class="form-group"><label>Databases</label><input type="number" name="databases" value="<?= $product->databases ?? 1 ?>"></div>
        <div class="form-group"><label>Backups</label><input type="number" name="backups" value="<?= $product->backups ?? 1 ?>"></div>
    </div>
    <hr style="border:none;border-top:1px solid var(--border);margin:1rem 0">
    <p style="color:var(--text2);font-size:.85rem;margin-bottom:1rem">Panel Integration</p>
    <div class="grid grid-3">
        <div class="form-group"><label>Nest ID</label><input type="number" name="nest_id" value="<?= $product->nest_id ?? $nestId ?>"></div>
        <div class="form-group"><label>Egg ID</label><input type="number" name="egg_id" value="<?= $product->egg_id ?? '' ?>"></div>
        <div class="form-group"><label>Node ID</label><input type="number" name="node_id" value="<?= $product->node_id ?? '' ?>"></div>
    </div>
    <div class="form-group"><label>Docker Image</label><input type="text" name="docker_image" value="<?= e($product->docker_image ?? '') ?>"></div>
    <div class="form-group"><label>Startup Command</label><textarea name="startup"><?= e($product->startup ?? '') ?></textarea></div>
    <div class="grid grid-2">
        <label style="display:flex;align-items:center;gap:.5rem;font-size:.9rem"><input type="checkbox" name="active" value="1" style="width:auto" <?= ($product->active ?? 1) ? 'checked' : '' ?>> Active</label>
        <label style="display:flex;align-items:center;gap:.5rem;font-size:.9rem"><input type="checkbox" name="featured" value="1" style="width:auto" <?= ($product->featured ?? 0) ? 'checked' : '' ?>> Featured</label>
    </div>
    <button type="submit" class="btn btn-accent" style="margin-top:1rem"><?= $product ? 'Update Product' : 'Create Product' ?></button>
</form>
</div>
