<?php
$accentColor = \App\Core\Settings::get('accent_color', '#7c3aed');
$siteName = e(\App\Core\Settings::get('site_name', 'NiveraCloud'));
$logoUrl = \App\Core\Settings::get('logo_url', '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? $siteName) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--accent:<?= $accentColor ?>;--bg:#0f0f23;--card:#1a1a2e;--border:#2d2d44;--text:#e2e8f0;--text2:#94a3b8;--ok:#22c55e;--err:#ef4444;--info:#3b82f6}
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center}
        .installer{max-width:440px;width:100%;padding:2rem}
        .card{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:2rem;text-align:center}
        .brand{font-size:1.8rem;font-weight:800;margin-bottom:1.5rem}
        .brand span{background:linear-gradient(135deg,var(--accent),#06b6d4);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
        a{color:var(--accent);text-decoration:none}
        .form-group{margin-bottom:1rem;text-align:left}.form-group label{display:block;font-size:.85rem;font-weight:500;color:var(--text2);margin-bottom:.4rem}
        input[type=text],input[type=password],input[type=email]{width:100%;padding:.7rem 1rem;background:var(--bg);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:.95rem;outline:none;font-family:inherit}
        input:focus{border-color:var(--accent)}
        .btn{display:block;width:100%;padding:.75rem;background:var(--accent);color:#fff;border:none;border-radius:8px;font-size:1rem;font-weight:600;cursor:pointer;transition:transform .15s;margin-top:1rem}
        .btn:hover{transform:translateY(-1px)}.btn-ok{background:var(--ok)}.btn-err{background:var(--err)}
        .alert{padding:.75rem;border-radius:8px;margin-bottom:1rem;font-size:.9rem}
        .alert-success{background:#052e16;border:1px solid var(--ok);color:#86efac}
        .alert-error{background:#451a2a;border:1px solid var(--err);color:#fca5a5}
        .alert-info{background:#172554;border:1px solid var(--info);color:#93c5fd}
        .links{margin-top:1.5rem;font-size:.9rem;color:var(--text2)}
    </style>
</head>
<body>
<div class="installer">
    <div class="card">
        <div class="brand"><a href="/"><?= $siteName ?></a></div>
        <?php if (!empty($_flash)): foreach ($_flash as $f): ?>
            <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
        <?php endforeach; endif; ?>
        <?= $content ?>
    </div>
</div>
</body>
</html>
