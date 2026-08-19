<?php
$accentColor = \App\Core\Settings::get('accent_color', '#7c3aed');
$siteName = e(\App\Core\Settings::get('site_name', 'NiveraCloud'));
$logoUrl = \App\Core\Settings::get('logo_url', '');
$customCss = \App\Core\Settings::get('custom_css', '');
$customJs = \App\Core\Settings::get('custom_js', '');
$footerText = e(\App\Core\Settings::get('footer_text', 'Powered by NiveraCloud'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? $siteName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--accent:<?= $accentColor ?>;--bg:#0f0f23;--card:#1a1a2e;--border:#2d2d44;--text:#e2e8f0;--text2:#94a3b8;--ok:#22c55e;--warn:#f59e0b;--err:#ef4444;--info:#3b82f6}
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;flex-direction:column}
        a{color:var(--accent);text-decoration:none}a:hover{color:#a78bfa}
        .nb{background:var(--card);border-bottom:1px solid var(--border);padding:0 2rem;height:64px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100}
        .nb-brand{display:flex;align-items:center;gap:.75rem;font-weight:700;font-size:1.1rem;color:var(--text)}
        .nb-brand img{height:32px;width:auto}
        .nb-links{display:flex;align-items:center;gap:1.5rem}
        .nb-links a{color:var(--text2);font-size:.9rem;font-weight:500}.nb-links a:hover{color:var(--text)}
        .nb-right{display:flex;align-items:center;gap:1rem}
        .avatar{width:36px;height:36px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;color:#fff}
        .main{flex:1;width:100%;max-width:1200px;margin:0 auto;padding:2rem}
        .footer{text-align:center;padding:2rem;color:var(--text2);font-size:.85rem;border-top:1px solid var(--border)}
        .card{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:1.5rem;margin-bottom:1rem}
        .btn{display:inline-block;padding:.65rem 1.25rem;border-radius:8px;font-size:.9rem;font-weight:600;border:none;cursor:pointer;transition:transform .15s;text-align:center}
        .btn:hover{transform:translateY(-1px)}.btn-accent{background:var(--accent);color:#fff}.btn-ok{background:var(--ok);color:#fff}.btn-err{background:var(--err);color:#fff}
        .btn-sm{padding:.4rem .8rem;font-size:.8rem}
        .form-group{margin-bottom:1rem}.form-group label{display:block;font-size:.85rem;font-weight:500;color:var(--text2);margin-bottom:.4rem}
        input[type=text],input[type=password],input[type=email],input[type=number],select,textarea{width:100%;padding:.65rem 1rem;background:var(--bg);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:.9rem;outline:none;font-family:inherit}
        input:focus,select:focus,textarea:focus{border-color:var(--accent)}
        textarea{min-height:100px;resize:vertical}
        .alert{padding:.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.9rem}
        .alert-success{background:#052e16;border:1px solid var(--ok);color:#86efac}
        .alert-error{background:#451a2a;border:1px solid var(--err);color:#fca5a5}
        .alert-info{background:#172554;border:1px solid var(--info);color:#93c5fd}
        .grid{display:grid;gap:1rem}.grid-2{grid-template-columns:repeat(2,1fr)}.grid-3{grid-template-columns:repeat(3,1fr)}.grid-4{grid-template-columns:repeat(4,1fr)}
        @media(max-width:768px){.grid-2,.grid-3,.grid-4{grid-template-columns:1fr}.nb-links{display:none}.main{padding:1rem}}
        .stat{text-align:center;padding:1.5rem}.stat-num{font-size:2rem;font-weight:800}.stat-label{color:var(--text2);font-size:.85rem;margin-top:.25rem}
        table{width:100%;border-collapse:collapse}th,td{text-align:left;padding:.75rem;border-bottom:1px solid var(--border);font-size:.9rem}th{color:var(--text2);font-weight:500}
        .badge{display:inline-block;padding:.2rem .6rem;border-radius:20px;font-size:.75rem;font-weight:600}.badge-ok{background:#052e16;color:#86efac}.badge-err{background:#451a2a;color:#fca5a5}.badge-warn{background:#451a2e;color:#fcd34d}.badge-info{background:#172554;color:#93c5fd}
        .sidebar{width:240px;min-height:calc(100vh - 64px);background:var(--card);border-right:1px solid var(--border);padding:1.5rem 0;position:fixed;top:64px;left:0}
        .sidebar a{display:flex;align-items:center;gap:.75rem;padding:.6rem 1.5rem;color:var(--text2);font-size:.9rem;font-weight:500}.sidebar a:hover,.sidebar a.active{color:var(--text);background:rgba(255,255,255,.05)}
        .content{margin-left:240px;padding:2rem;min-height:calc(100vh - 64px)}
        @media(max-width:768px){.sidebar{display:none}.content{margin-left:0}}
        .empty{text-align:center;padding:3rem;color:var(--text2)}
        <?= $customCss ?>
    </style>
</head>
<body>
<nav class="nb">
    <a href="/" class="nb-brand">
        <?php if ($logoUrl): ?><img src="<?= e(asset($logoUrl)) ?>" alt="Logo"><?php endif; ?>
        <?= $siteName ?>
    </a>
    <div class="nb-links">
        <a href="/products">Products</a>
        <?php if ($logoUrl): ?><a href="/pricing">Pricing</a><?php endif; ?>
        <?php if ($_user && $_user->isAdmin()): ?><a href="/admin">Admin</a><?php endif; ?>
    </div>
    <div class="nb-right">
        <?php if ($_user): ?>
            <a href="/dashboard" style="color:var(--text2);font-size:.9rem">Dashboard</a>
            <div class="avatar" title="<?= e($_user->username) ?>"><?= strtoupper(substr($_user->username,0,1)) ?></div>
            <form method="POST" action="/logout" style="display:inline"><input type="hidden" name="_token" value="<?= e($_csrf) ?>"><button type="submit" class="btn btn-sm" style="background:transparent;color:var(--text2);border:1px solid var(--border)">Logout</button></form>
        <?php else: ?>
            <a href="/login" class="btn btn-sm" style="background:transparent;color:var(--text);border:1px solid var(--border)">Login</a>
            <a href="/register" class="btn btn-sm btn-accent">Sign Up</a>
        <?php endif; ?>
    </div>
</nav>

<?php if (!empty($_flash)): foreach ($_flash as $f): ?>
    <div class="main" style="padding-bottom:0"><div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div></div>
<?php endforeach; endif; ?>

<div class="main">
    <?= $content ?>
</div>

<footer class="footer"><?= $footerText ?></footer>
<?php if ($customJs): ?><script><?= $customJs ?></script><?php endif; ?>
</body>
</html>
