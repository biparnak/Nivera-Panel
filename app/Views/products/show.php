<?php $pageTitle = e($product->name); ?>
<div style="max-width:800px;margin:0 auto">
    <a href="/products" style="font-size:.85rem;color:var(--text2)">&larr; Back to Products</a>
    <div class="card" style="margin-top:1rem">
        <div style="display:flex;gap:1.5rem;align-items:start">
            <div style="font-size:3rem"><?= e($product->icon) ?></div>
            <div style="flex:1">
                <h1 style="font-size:1.5rem;font-weight:700;margin-bottom:.5rem"><?= e($product->name) ?></h1>
                <p style="color:var(--text2);margin-bottom:1.5rem"><?= nl2br(e($product->description ?? '')) ?></p>
                <div class="grid grid-4" style="margin-bottom:1.5rem">
                    <div><span style="color:var(--text2);font-size:.8rem">Memory</span><div style="font-weight:700"><?= $product->memory_mb ?>MB</div></div>
                    <div><span style="color:var(--text2);font-size:.8rem">Disk</span><div style="font-weight:700"><?= $product->disk_mb ?>MB</div></div>
                    <div><span style="color:var(--text2);font-size:.8rem">CPU</span><div style="font-weight:700"><?= $product->cpu_percent ?>%</div></div>
                    <div><span style="color:var(--text2);font-size:.8rem">Backups</span><div style="font-weight:700"><?= $product->backups ?></div></div>
                </div>
                <div style="display:flex;gap:1.5rem;align-items:end">
                    <div>
                        <div style="color:var(--text2);font-size:.8rem">Monthly</div>
                        <div style="font-size:1.5rem;font-weight:800"><?= $product->price_monthly > 0 ? '$' . number_format($product->price_monthly, 2) : 'Free' ?></div>
                    </div>
                    <?php if ($product->price_yearly > 0): ?>
                    <div>
                        <div style="color:var(--text2);font-size:.8rem">Yearly</div>
                        <div style="font-size:1.5rem;font-weight:800;color:var(--ok)">$<?= number_format($product->price_yearly, 2) ?></div>
                    </div>
                    <?php endif; ?>
                    <a href="/products/<?= $product->id ?>/order" class="btn btn-accent" style="padding:.8rem 2rem">Order Now</a>
                </div>
            </div>
        </div>
    </div>
</div>
