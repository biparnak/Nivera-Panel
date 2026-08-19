<?php
declare(strict_types=1);

require __DIR__ . '/../config/config.php';

use App\Core\Database;
use App\Core\Session;
use App\Core\Validator;

Session::start();

$step = (int)($_GET['step'] ?? 1);
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 2) {
        // Database setup
        $host = trim((string)($_POST['db_host'] ?? '127.0.0.1'));
        $port = (int)($_POST['db_port'] ?? 3306);
        $name = trim((string)($_POST['db_name'] ?? 'niveracloud'));
        $user = trim((string)($_POST['db_user'] ?? 'root'));
        $pass = (string)($_POST['db_pass'] ?? '');

        // Test connection
        try {
            $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$name}`");

            // Run schema
            $schema = file_get_contents(BASE_PATH . '/database/schema.sql');
            $schema = str_replace('CREATE DATABASE IF NOT EXISTS `niveracloud` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;', '', $schema);
            $schema = str_replace('USE `niveracloud`;', '', $schema);
            $statements = array_filter(array_map('trim', explode(';', $schema)));
            foreach ($statements as $stmt) {
                if (!empty($stmt)) $pdo->exec($stmt);
            }

            // Update config.php
            $configPath = BASE_PATH . '/config/config.php';
            $config = file_get_contents($configPath);
            $config = str_replace("define('DB_HOST', '127.0.0.1');", "define('DB_HOST', '{$host}');", $config);
            $config = str_replace("define('DB_PORT', 3306);", "define('DB_PORT', {$port});", $config);
            $config = str_replace("define('DB_NAME', 'niveracloud');", "define('DB_NAME', '{$name}');", $config);
            $config = str_replace("define('DB_USER', 'root');", "define('DB_USER', '{$user}');", $config);
            $config = str_replace("define('DB_PASS', '');", "define('DB_PASS', '{$pass}');", $config);
            file_put_contents($configPath, $config);

            header('Location: ' . $_SERVER['PHP_SELF'] . '?step=3');
            exit;
        } catch (\PDOException $e) {
            $error = 'Database connection failed: ' . $e->getMessage();
        }
    }

    if ($step === 3) {
        // Admin account
        $username = trim((string)($_POST['admin_user'] ?? 'admin'));
        $email = trim(strtolower((string)($_POST['admin_email'] ?? '')));
        $password = (string)($_POST['admin_pass'] ?? '');
        $siteName = trim((string)($_POST['site_name'] ?? 'NiveraCloud'));

        $v = new Validator();
        if (!$v->make($_POST, [
            'admin_user' => 'required|min:3|max:32',
            'admin_email' => 'required|email',
            'admin_pass' => 'required|min:8|max:72',
            'site_name' => 'required|max:100',
        ])) {
            $error = $v->firstError();
        } else {
            try {
                $db = Database::connection();
                // Create admin
                $stmt = $db->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
                $stmt->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT), 'admin']);

                // Update site settings
                $stmt = $db->prepare('UPDATE settings SET `value` = ? WHERE `key` = ?');
                $stmt->execute([$siteName, 'site_name']);

                // Generate APP_KEY
                $newKey = bin2hex(random_bytes(32));
                $configPath = BASE_PATH . '/config/config.php';
                $config = file_get_contents($configPath);
                $config = str_replace("define('APP_KEY', 'CHANGE_ME_TO_A_LONG_RANDOM_STRING');", "define('APP_KEY', '{$newKey}');", $config);
                file_put_contents($configPath, $config);

                header('Location: ' . $_SERVER['PHP_SELF'] . '?step=4');
                exit;
            } catch (\Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NiveraCloud Installer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #0f0f23; color: #e2e8f0; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .installer { max-width: 560px; width: 100%; padding: 2rem; }
        .card { background: #1a1a2e; border: 1px solid #2d2d44; border-radius: 12px; padding: 2rem; margin-bottom: 1.5rem; }
        h1 { font-size: 1.8rem; font-weight: 700; background: linear-gradient(135deg, #7c3aed, #06b6d4); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 0.5rem; }
        h2 { font-size: 1.2rem; font-weight: 600; color: #c4b5fd; margin-bottom: 1.5rem; }
        .steps { display: flex; gap: 0.5rem; margin-bottom: 2rem; }
        .step { flex: 1; height: 4px; background: #2d2d44; border-radius: 2px; }
        .step.active { background: linear-gradient(90deg, #7c3aed, #06b6d4); }
        .step.done { background: #22c55e; }
        label { display: block; font-size: 0.85rem; font-weight: 500; color: #94a3b8; margin-bottom: 0.4rem; }
        input[type="text"], input[type="password"], input[type="email"], input[type="number"] {
            width: 100%; padding: 0.7rem 1rem; background: #0f0f23; border: 1px solid #2d2d44; border-radius: 8px;
            color: #e2e8f0; font-size: 0.95rem; outline: none; transition: border-color 0.2s; margin-bottom: 1rem;
        }
        input:focus { border-color: #7c3aed; }
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .btn { display: inline-block; width: 100%; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #7c3aed, #6d28d9); color: #fff; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: transform 0.15s, box-shadow 0.15s; }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 4px 20px rgba(124,58,237,0.4); }
        .btn-success { background: linear-gradient(135deg, #22c55e, #16a34a); }
        .error { background: #451a2a; border: 1px solid #dc2626; color: #fca5a5; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.9rem; }
        .success-msg { background: #052e16; border: 1px solid #22c55e; color: #86efac; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.9rem; }
        .check-list { list-style: none; margin: 1rem 0; }
        .check-list li { padding: 0.5rem 0; display: flex; align-items: center; gap: 0.5rem; }
        .check-list li.pass { color: #22c55e; }
        .check-list li.fail { color: #ef4444; }
        a { color: #7c3aed; text-decoration: none; }
        .panel { color: #94a3b8; font-size: 0.85rem; margin-top: 1rem; }
    </style>
</head>
<body>
<div class="installer">
    <div class="steps">
        <div class="step <?= $step >= 1 ? ($step > 1 ? 'done' : 'active') : '' ?>"></div>
        <div class="step <?= $step >= 2 ? ($step > 2 ? 'done' : 'active') : '' ?>"></div>
        <div class="step <?= $step >= 3 ? ($step > 3 ? 'done' : 'active') : '' ?>"></div>
        <div class="step <?= $step >= 4 ? 'active' : '' ?>"></div>
    </div>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($step === 1): ?>
    <div class="card">
        <h1>NiveraCloud Installer</h1>
        <h2>Welcome! Let's set up your hosting panel.</h2>
        <p style="color: #94a3b8; margin-bottom: 1.5rem;">Requirements Check</p>
        <ul class="check-list">
            <li class="<?= PHP_VERSION_ID >= 80100 ? 'pass' : 'fail' ?>">
                <?= PHP_VERSION_ID >= 80100 ? '&#10003;' : '&#10007;' ?> PHP <?= PHP_VERSION ?>
                <?= PHP_VERSION_ID < 80100 ? ' (requires 8.1+)' : '' ?>
            </li>
            <li class="<?= extension_loaded('pdo_mysql') ? 'pass' : 'fail' ?>">
                <?= extension_loaded('pdo_mysql') ? '&#10003;' : '&#10007;' ?> PDO MySQL Extension
            </li>
            <li class="<?= extension_loaded('curl') ? 'pass' : 'fail' ?>">
                <?= extension_loaded('curl') ? '&#10003;' : '&#10007;' ?> cURL Extension
            </li>
            <li class="<?= extension_loaded('json') ? 'pass' : 'fail' ?>">
                <?= extension_loaded('json') ? '&#10003;' : '&#10007;' ?> JSON Extension
            </li>
            <li class="<?= extension_loaded('mbstring') ? 'pass' : 'fail' ?>">
                <?= extension_loaded('mbstring') ? '&#10003;' : '&#10007;' ?> MBString Extension
            </li>
            <li class="<?= is_writable(BASE_PATH . '/config') ? 'pass' : 'fail' ?>">
                <?= is_writable(BASE_PATH . '/config') ? '&#10003;' : '&#10007;' ?> Config directory writable
            </li>
        </ul>
        <a href="?step=2" class="btn" style="text-align:center; text-decoration:none;">Continue &rarr;</a>
    </div>

    <?php elseif ($step === 2): ?>
    <div class="card">
        <h1>Database Setup</h1>
        <h2>Configure your MySQL/MariaDB connection</h2>
        <form method="POST">
            <div class="row">
                <div>
                    <label>Database Host</label>
                    <input type="text" name="db_host" value="127.0.0.1" required>
                </div>
                <div>
                    <label>Port</label>
                    <input type="number" name="db_port" value="3306">
                </div>
            </div>
            <label>Database Name</label>
            <input type="text" name="db_name" value="niveracloud" required>
            <label>Database User</label>
            <input type="text" name="db_user" value="root" required>
            <label>Database Password</label>
            <input type="password" name="db_pass" value="">
            <button type="submit" class="btn">Test &amp; Install Database &rarr;</button>
        </form>
    </div>

    <?php elseif ($step === 3): ?>
    <div class="card">
        <h1>Admin Account</h1>
        <h2>Create your administrator account</h2>
        <form method="POST">
            <label>Site Name</label>
            <input type="text" name="site_name" value="NiveraCloud" required>
            <label>Admin Username</label>
            <input type="text" name="admin_user" value="admin" required>
            <label>Admin Email</label>
            <input type="email" name="admin_email" required>
            <label>Admin Password (min 8 chars)</label>
            <input type="password" name="admin_pass" required minlength="8">
            <button type="submit" class="btn">Create Admin &amp; Finish &rarr;</button>
        </form>
    </div>

    <?php elseif ($step === 4): ?>
    <div class="card" style="text-align: center;">
        <h1>&#10003; Installation Complete!</h1>
        <h2>NiveraCloud is ready to use.</h2>
        <div class="success-msg">Admin account created. You can now log in and configure your hosting panels.</div>
        <p style="color: #94a3b8; margin: 1rem 0;">For security, delete or rename the install.php file.</p>
        <a href="/login" class="btn btn-success" style="text-align:center; text-decoration:none;">Go to Login &rarr;</a>
        <div class="panel">
            <p>Configure your panel at: <a href="/admin/settings/panel">Admin &rarr; Panel Settings</a></p>
        </div>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
<?php exit; ?>
