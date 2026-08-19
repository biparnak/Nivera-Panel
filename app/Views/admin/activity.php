<?php $pageTitle = 'Activity Log'; ?>
<h1 style="font-size:1.5rem;font-weight:700;margin-bottom:1.5rem">Activity Log</h1>
<div class="card">
<?php foreach ($activities as $a): ?>
<div style="padding:.5rem 0;border-bottom:1px solid var(--border);font-size:.9rem;display:flex;justify-content:space-between">
    <div><span style="color:var(--accent);font-weight:500"><?= e($a['action'] ?? '') ?></span> <?= e($a['description'] ?? '') ?></div>
    <div style="color:var(--text2);font-size:.8rem;white-space:nowrap;margin-left:1rem"><?= e($a['username'] ?? 'system') ?> &middot; <?= timeAgo($a['created_at']) ?></div>
</div>
<?php endforeach; ?>
</div>
