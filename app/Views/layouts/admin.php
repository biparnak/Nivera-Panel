<?php
$accentColor = \App\Core\Settings::get('accent_color', '#7c3aed');
$siteName = e(\App\Core\Settings::get('site_name', 'NiveraCloud'));
$logoUrl = \App\Core\Settings::get('logo_url', '');
$customCss = \App\Core\Settings::get('custom_css', '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Admin') ?> - <?= $siteName ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--accent:<?= $accentColor ?>;--bg:#0f0f23;--card:#1a1a2e;--border:#2d2d44;--text:#e2e8f0;--text2:#94a3b8;--ok:#22c55e;--warn:#f59e0b;--err:#ef4444;--info:#3b82f6}
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;flex-direction:column}
        a{color:var(--accent);text-decoration:none}a:hover{color:#a78bfa}
        .nb{background:var(--card);border-bottom:1px solid var(--border);padding:0 2rem;height:56px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100}
        .nb-brand{display:flex;align-items:center;gap:.75rem;font-weight:700;font-size:1rem;color:var(--text)}.nb-brand img{height:28px;width:auto}
        .nb-right{display:flex;align-items:center;gap:1rem}
        .nb-right a{color:var(--text2);font-size:.85rem}.nb-right a:hover{color:var(--text)}
        .layout{display:flex;min-height:calc(100vh - 56px)}
        .sb{width:220px;background:var(--card);border-right:1px solid var(--border);padding:1rem 0;position:sticky;top:56px;height:calc(100vh - 56px);overflow-y:auto;flex-shrink:0}
        .sb-title{padding:.5rem 1.25rem;font-size:.7rem;font-weight:600;text-transform:uppercase;color:var(--text2);letter-spacing:.05em;margin-top:.5rem}
        .sb a{display:flex;align-items:center;gap:.6rem;padding:.55rem 1.25rem;color:var(--text2);font-size:.85rem;font-weight:500;transition:background .15s}
        .sb a:hover,.sb a.active{color:var(--text);background:rgba(255,255,255,.05)}
        .sb a.active{border-right:2px solid var(--accent)}
        .ct{flex:1;padding:1.5rem 2rem;overflow-x:auto}
        .alert{padding:.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.9rem}.alert-success{background:#052e16;border:1px solid var(--ok);color:#86efac}.alert-error{background:#451a2a;border:1px solid var(--err);color:#fca5a5}.alert-info{background:#172554;border:1px solid var(--info);color:#93c5fd}
        .card{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:1.5rem;margin-bottom:1rem}
        .btn{display:inline-block;padding:.6rem 1.2rem;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;transition:transform .1s;text-align:center}.btn:hover{transform:translateY(-1px)}.btn-accent{background:var(--accent);color:#fff}.btn-ok{background:var(--ok);color:#fff}.btn-err{background:var(--err);color:#fff}.btn-sm{padding:.35rem .7rem;font-size:.8rem}
        .form-group{margin-bottom:1rem}.form-group label{display:block;font-size:.85rem;font-weight:500;color:var(--text2);margin-bottom:.4rem}
        input[type=text],input[type=password],input[type=email],input[type=number],input[type=color],input[type=file],input[type=datetime-local],select,textarea{width:100%;padding:.6rem .9rem;background:var(--bg);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:.9rem;outline:none;font-family:inherit}
        input:focus,select:focus,textarea:focus{border-color:var(--accent)}textarea{min-height:80px;resize:vertical}
        .grid{display:grid;gap:1rem}.grid-2{grid-template-columns:repeat(2,1fr)}.grid-3{grid-template-columns:repeat(3,1fr)}.grid-4{grid-template-columns:repeat(4,1fr)}
        @media(max-width:768px){.sb{display:none}.ct{padding:1rem}.grid-2,.grid-3,.grid-4{grid-template-columns:1fr}}
        table{width:100%;border-collapse:collapse}th,td{text-align:left;padding:.6rem .75rem;border-bottom:1px solid var(--border);font-size:.85rem}th{color:var(--text2);font-weight:500}
        .badge{display:inline-block;padding:.2rem .55rem;border-radius:20px;font-size:.7rem;font-weight:600}.badge-ok{background:#052e16;color:#86efac}.badge-err{background:#451a2a;color:#fca5a5}.badge-warn{background:#451a2e;color:#fcd34d}.badge-info{background:#172554;color:#93c5fd}
        .stat{text-align:center;padding:1.25rem}.stat-num{font-size:1.7rem;font-weight:800}.stat-label{color:var(--text2);font-size:.8rem;margin-top:.25rem}
        .empty{text-align:center;padding:2rem;color:var(--text2)}
        <?= $customCss ?>
    </style>
</head>
<body>
<nav class="nb">
    <a href="/admin" class="nb-brand">
        <?php if ($logoUrl): ?><img src="<?= e(asset($logoUrl)) ?>" alt="Logo"><?php endif; ?>
        <?= $siteName ?> <span style="font-size:.7rem;background:var(--accent);color:#fff;padding:.15rem .5rem;border-radius:4px;font-weight:600">Admin</span>
    </a>
    <div class="nb-right">
        <a href="/dashboard">Dashboard</a>
        <a href="/" target="_blank">View Site</a>
        <form method="POST" action="/logout" style="display:inline"><input type="hidden" name="_token" value="<?= e($_csrf) ?>"><button type="submit" class="btn btn-sm" style="background:transparent;color:var(--text2);border:1px solid var(--border)">Logout</button></form>
    </div>
</nav>
<div class="layout">
<aside class="sb">
    <div class="sb-title">Main</div>
    <a href="/admin" class="<?= ($pageTitle ?? '') === 'Admin Dashboard' ? 'active' : '' ?>">&#128200; Dashboard</a>
    <div class="sb-title">Management</div>
    <a href="/admin/products">&#128230; Products</a>
    <a href="/admin/categories">&#128193; Categories</a>
    <a href="/admin/orders">&#128179; Orders</a>
    <a href="/admin/servers">&#128421; Servers</a>
    <a href="/admin/users">&#128100; Users</a>
    <div class="sb-title">Engagement</div>
    <a href="/admin/coupons">&#127915; Coupons</a>
    <a href="/admin/announcements">&#128227; Announcements</a>
    <a href="/admin/activity">&#128337; Activity Log</a>
    <div class="sb-title">Configuration</div>
    <a href="/admin/settings">&#9881; General</a>
    <a href="/admin/settings/panel">&#127760; Panel API</a>
    <a href="/admin/settings/customization">&#127912; Customization</a>
    <a href="/admin/settings/email">&#9993; Email</a>
    <a href="/admin/settings/adsense">&#128640; AdSense</a>
</aside>
<div class="ct">
    <?php if (!empty($_flash)): foreach ($_flash as $f): ?>
        <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
    <?php endforeach; endif; ?>
    <?= $content ?>
</div>
</div>
</body>
</html>
