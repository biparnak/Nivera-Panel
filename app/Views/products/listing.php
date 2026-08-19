<?php $pageTitle = 'Products'; ?>
<h1 style="font-size:1.5rem;font-weight:700;margin-bottom:1.5rem">Products</h1>
<?php if (!empty($categories)): ?>
<div style="display:flex;gap:.5rem;margin-bottom:1.5rem;flex-wrap:wrap">
    <a href="/products" class="btn btn-sm" style="background:var(--accent);color:#fff">All</a>
    <?php foreach ($categories as $cat): ?>
    <a href="/products?category=<?= e($cat->slug) ?>" class="btn btn-sm" style="background:transparent;color:var(--text);border:1px solid var(--border)"><?= $cat->icon ?? '' ?> <?= e($cat->name) ?></a>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<div class="grid grid-3">
    <?php foreach ($products as $p): ?>
    <a href="/products/<?= e($p->slug ?: $p->id) ?>" class="card" style="text-decoration:none">
        <div style="font-size:2rem;margin-bottom:.5rem"><?= e($p->icon) ?></div>
        <div style="font-weight:700;margin-bottom:.25rem"><?= e($p->name) ?></div>
        <p style="color:var(--text2);font-size:.85rem;margin-bottom:1rem"><?= e(mb_substr($p->description ?? '', 0, 100)) ?></p>
        <div style="display:flex;justify-content:space-between;align-items:center">
            <span style="font-weight:800"><?= $p->price_monthly > 0 ? '$' . number_format($p->price_monthly, 2) . '/mo' : 'Free' ?></span>
            <span style="color:var(--text2);font-size:.8rem"><?= $p->memory_mb ?>MB RAM</span>
        </div>
    </a>
    <?php endforeach; ?>
</div>
