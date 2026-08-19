<?php $pageTitle = 'Admin - Announcements'; ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
    <h1 style="font-size:1.5rem;font-weight:700">Announcements</h1>
</div>
<div class="grid grid-2">
<div class="card">
    <h3 style="font-weight:600;margin-bottom:1rem">Post Announcement</h3>
    <form method="POST" action="/admin/announcements/create">
        <input type="hidden" name="_token" value="<?= e($_csrf) ?>">
        <div class="form-group"><label>Title</label><input type="text" name="title" required></div>
        <div class="form-group"><label>Content</label><textarea name="content" required></textarea></div>
        <div class="grid grid-2">
            <div class="form-group"><label>Type</label><select name="type"><option value="info">Info</option><option value="warning">Warning</option><option value="danger">Danger</option><option value="success">Success</option></select></div>
            <div class="form-group"><label style="display:flex;align-items:center;gap:.5rem;margin-top:1.5rem"><input type="checkbox" name="pinned" value="1" style="width:auto"> Pinned</label></div>
        </div>
        <button type="submit" class="btn btn-accent">Post</button>
    </form>
</div>
<div class="card">
    <h3 style="font-weight:600;margin-bottom:1rem">Existing</h3>
    <?php foreach ($announcements as $a): ?>
    <div style="padding:.5rem 0;border-bottom:1px solid var(--border)">
        <div style="display:flex;justify-content:space-between"><strong><?= e($a->title) ?></strong><span class="badge badge-info"><?= e($a->type) ?></span></div>
        <p style="color:var(--text2);font-size:.85rem;margin:.25rem 0"><?= e(mb_substr($a->content, 0, 100)) ?></p>
        <form method="POST" action="/admin/announcements/<?= $a->id ?>/delete" style="display:inline" onsubmit="return confirm('Delete?')"><input type="hidden" name="_token" value="<?= e($_csrf) ?>"><button class="btn btn-err btn-sm" style="font-size:.75rem">Delete</button></form>
    </div>
    <?php endforeach; ?>
</div>
</div>
