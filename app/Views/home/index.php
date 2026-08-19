<?php $pageTitle = \App\Core\Settings::get('site_name', 'NiveraCloud') . ' - Home'; ?>
<?php $heroTitle = e(\App\Core\Settings::get('hero_title', 'Premium Game & Application Hosting')); ?>
<?php $heroSubtitle = e(\App\Core\Settings::get('hero_subtitle', 'Deploy your servers instantly.')); ?>
<div style="text-align:center;padding:4rem 0 3rem">
    <h1 style="font-size:3rem;font-weight:800;margin-bottom:1rem;line-height:1.2"><?= $heroTitle ?></h1>
    <p style="color:var(--text2);font-size:1.15rem;max-width:600px;margin:0 auto 2rem"><?= $heroSubtitle ?></p>
    <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
        <a href="/products" class="btn btn-accent" style="padding:.8rem 2rem;font-size:1rem">Browse Products</a>
        <?php if (!$_user): ?><a href="/register" style="padding:.8rem 2rem;font-size:1rem;border:1px solid var(--border);border-radius:8px;color:var(--text);font-weight:600">Sign Up Free</a><?php endif; ?>
    </div>
</div>

<?php if (!empty($announcements)): foreach ($announcements as $ann): ?>
<div class="alert alert-info" style="max-width:800px;margin:0 auto 1rem"><strong><?= e($ann->title) ?>:</strong> <?= nl2br(e($ann->content)) ?></div>
<?php endforeach; endif; ?>

<?php if (!empty($categories)): ?>
<h2 style="text-align:center;margin:2rem 0 1.5rem">Categories</h2>
<div class="grid grid-4" style="max-width:800px;margin:0 auto">
    <?php foreach ($categories as $cat): ?>
    <a href="/products?category=<?= e($cat->slug) ?>" class="card" style="text-align:center;text-decoration:none">
        <div style="font-size:2rem;margin-bottom:.5rem"><?= $cat->icon ?? '📦' ?></div>
        <div style="font-weight:600"><?= e($cat->name) ?></div>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!empty($products)): ?>
<h2 style="text-align:center;margin:2rem 0 1.5rem">Featured Products</h2>
<div class="grid grid-3" style="max-width:900px;margin:0 auto">
    <?php foreach (array_slice($products, 0, 6) as $p): ?>
    <a href="/products/<?= e($p->slug ?: $p->id) ?>" class="card" style="text-decoration:none">
        <div style="font-size:2rem;margin-bottom:.5rem"><?= e($p->icon) ?></div>
        <div style="font-weight:700;font-size:1.05rem;margin-bottom:.5rem"><?= e($p->name) ?></div>
        <p style="color:var(--text2);font-size:.85rem;margin-bottom:1rem"><?= e(mb_substr($p->description ?? '', 0, 80)) ?></p>
        <div style="display:flex;justify-content:space-between;align-items:center">
            <span style="font-weight:800;font-size:1.1rem"><?= $p->price_monthly > 0 ? '$' . number_format($p->price_monthly, 2) . '/mo' : 'Free' ?></span>
            <span style="color:var(--text2);font-size:.8rem"><?= $p->memory_mb ?>MB / <?= $p->disk_mb ?>MB</span>
        </div>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>
