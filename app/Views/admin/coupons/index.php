<?php $pageTitle = 'Admin - Coupons'; ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
    <h1 style="font-size:1.5rem;font-weight:700">Coupons</h1>
</div>
<div class="grid grid-2">
<div class="card">
    <h3 style="font-weight:600;margin-bottom:1rem">Create Coupon</h3>
    <form method="POST" action="/admin/coupons/create">
        <input type="hidden" name="_token" value="<?= e($_csrf) ?>">
        <div class="form-group"><label>Code</label><input type="text" name="code" required style="text-transform:uppercase"></div>
        <div class="grid grid-2">
            <div class="form-group"><label>Type</label><select name="type"><option value="percentage">Percentage</option><option value="fixed">Fixed Amount</option></select></div>
            <div class="form-group"><label>Value</label><input type="number" name="value" step="0.01" required></div>
        </div>
        <div class="grid grid-2">
            <div class="form-group"><label>Max Uses</label><input type="number" name="max_uses" placeholder="Unlimited"></div>
            <div class="form-group"><label>Min Amount ($)</label><input type="number" name="min_amount" step="0.01" value="0"></div>
        </div>
        <div class="form-group"><label>Expires At</label><input type="datetime-local" name="expires_at"></div>
        <label style="display:flex;align-items:center;gap:.5rem;font-size:.9rem;margin-bottom:1rem"><input type="checkbox" name="active" value="1" checked style="width:auto"> Active</label>
        <button type="submit" class="btn btn-accent">Create Coupon</button>
    </form>
</div>
<div class="card">
    <h3 style="font-weight:600;margin-bottom:1rem">Existing Coupons</h3>
    <?php foreach ($coupons as $c): ?>
    <div style="display:flex;justify-content:space-between;align-items:center;padding:.5rem 0;border-bottom:1px solid var(--border)">
        <div><span style="font-weight:600"><?= e($c->code) ?></span> - <?= $c->type === 'percentage' ? $c->value . '%' : '$' . number_format($c->value, 2) ?> (Used: <?= $c->used_count ?>/<?= $c->max_uses ?? '∞' ?>)</div>
        <form method="POST" action="/admin/coupons/<?= $c->id ?>/delete" onsubmit="return confirm('Delete?')"><input type="hidden" name="_token" value="<?= e($_csrf) ?>"><button class="btn btn-err btn-sm">Delete</button></form>
    </div>
    <?php endforeach; ?>
</div>
</div>
