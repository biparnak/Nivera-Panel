<?php $pageTitle = 'Admin - Customization'; ?>
<h1 style="font-size:1.5rem;font-weight:700;margin-bottom:1.5rem">Customization &amp; Branding</h1>
<div class="card" style="max-width:800px">
<form method="POST" action="/admin/settings/customization" enctype="multipart/form-data">
    <input type="hidden" name="_token" value="<?= e($_csrf) ?>">
    <h3 style="font-weight:600;margin-bottom:1rem">Logo</h3>
    <div class="form-group"><label>Logo URL (leave blank to use uploaded file)</label><input type="text" name="logo_url" value="<?= e($settings['logo_url'] ?? '') ?>"></div>
    <div class="form-group"><label>Upload Logo</label><input type="file" name="logo_file" accept="image/*" style="padding:.5rem"></div>
    <?php if (!empty($settings['logo_url'])): ?><p style="font-size:.85rem;color:var(--text2);margin-bottom:1rem">Current: <img src="<?= e(asset($settings['logo_url'])) ?>" style="height:32px;vertical-align:middle"></p><?php endif; ?>
    <div class="form-group"><label>Favicon URL</label><input type="text" name="favicon_url" value="<?= e($settings['favicon_url'] ?? '') ?>"></div>
    <hr style="border:none;border-top:1px solid var(--border);margin:1.5rem 0">
    <h3 style="font-weight:600;margin-bottom:1rem">Colors &amp; Theme</h3>
    <div class="grid grid-2">
        <div class="form-group"><label>Accent Color</label><input type="color" name="accent_color" value="<?= e($settings['accent_color'] ?? '#7c3aed') ?>" style="height:40px"></div>
        <div class="form-group"><label>Theme</label><select name="theme_mode"><option value="dark" <?= ($settings['theme_mode'] ?? 'dark') === 'dark' ? 'selected' : '' ?>>Dark</option><option value="light" <?= ($settings['theme_mode'] ?? 'dark') === 'light' ? 'selected' : '' ?>>Light</option></select></div>
    </div>
    <hr style="border:none;border-top:1px solid var(--border);margin:1.5rem 0">
    <h3 style="font-weight:600;margin-bottom:1rem">Hero Section</h3>
    <div class="form-group"><label>Hero Title</label><input type="text" name="hero_title" value="<?= e($settings['hero_title'] ?? '') ?>"></div>
    <div class="form-group"><label>Hero Subtitle</label><input type="text" name="hero_subtitle" value="<?= e($settings['hero_subtitle'] ?? '') ?>"></div>
    <div class="form-group"><label>Footer Text</label><input type="text" name="footer_text" value="<?= e($settings['footer_text'] ?? '') ?>"></div>
    <hr style="border:none;border-top:1px solid var(--border);margin:1.5rem 0">
    <h3 style="font-weight:600;margin-bottom:1rem">Custom CSS</h3>
    <div class="form-group"><textarea name="custom_css" style="font-family:monospace;min-height:150px" placeholder="/* Add your custom CSS here */"><?= e($settings['custom_css'] ?? '') ?></textarea></div>
    <h3 style="font-weight:600;margin-bottom:1rem">Custom JavaScript</h3>
    <div class="form-group"><textarea name="custom_js" style="font-family:monospace;min-height:100px" placeholder="// Add your custom JS here"><?= e($settings['custom_js'] ?? '') ?></textarea></div>
    <button type="submit" class="btn btn-accent" style="margin-top:1rem">Save Customization</button>
</form>
</div>
