<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('APP_NAME', 'NiveraCloud');
define('APP_VERSION', '2.0.0');

define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3306);
define('DB_NAME', 'niveracloud');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('APP_URL', '');
date_default_timezone_set('UTC');
define('APP_KEY', 'CHANGE_ME_TO_A_LONG_RANDOM_STRING');
define('APP_DEBUG', false);
define('SESSION_LIFETIME', 120);
define('REGISTRATION_ENABLED', true);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_SECONDS', 300);
define('CURRENCY_SYMBOL', '$');
define('UPLOAD_PATH', BASE_PATH . '/public/uploads');

if (APP_DEBUG) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
}

require_once __DIR__ . '/../app/helpers.php';

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});
