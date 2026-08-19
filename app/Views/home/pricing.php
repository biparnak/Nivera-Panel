<?php $pageTitle = 'Pricing'; ?>
<h1 style="font-size:2rem;font-weight:800;text-align:center;margin-bottom:2rem">Pricing Plans</h1>
<?php if (!empty($categories)): foreach ($categories as $cat): ?>
<?php $catProducts = array_filter($products, fn($p) => $p->category_id == $cat->id); ?>
<?php if (!empty($catProducts)): ?>
<h2 style="font-size:1.3rem;font-weight:700;margin:2rem 0 1rem"><?= $cat->icon ?? '' ?> <?= e($cat->name) ?></h2>
<div class="grid grid-3">
    <?php foreach ($catProducts as $p): ?>
    <div class="card" style="text-align:center">
        <div style="font-size:2rem;margin-bottom:.5rem"><?= e($p->icon) ?></div>
        <h3 style="font-weight:700;margin-bottom:.5rem"><?= e($p->name) ?></h3>
        <div style="font-size:2rem;font-weight:800;margin:1rem 0"><?= $p->price_monthly > 0 ? '$' . number_format($p->price_monthly, 2) : 'Free' ?><?= $p->price_monthly > 0 ? '<span style="font-size:.9rem;font-weight:400;color:var(--text2)">/mo</span>' : '' ?></div>
        <div style="text-align:left;margin:1rem 0;font-size:.85rem;color:var(--text2)">
            <div><?= $p->memory_mb ?>MB RAM</div>
            <div><?= $p->disk_mb ?>MB Disk</div>
            <div><?= $p->cpu_percent ?>% CPU</div>
            <div><?= $p->backups ?> Backup<?= $p->backups != 1 ? 's' : '' ?></div>
        </div>
        <a href="/products/<?= e($p->slug ?: $p->id) ?>" class="btn btn-accent" style="width:100%;text-decoration:none">Order Now</a>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; endforeach; endif; ?>
