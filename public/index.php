<?php
declare(strict_types=1);

/**
 * NiveraCloud - Front Controller
 * Supports Pterodactyl, PufferPanel, and Pelican hosting panels.
 */

require __DIR__ . '/../config/config.php';

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\HomeController;
use App\Controllers\ProductController;
use App\Controllers\ServerController;
use App\Controllers\SupportController;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Router;
use App\Core\Session;
use App\Core\Settings;
use App\Middleware\Guest;
use App\Middleware\RequireAdmin;
use App\Middleware\RequireAuth;

Session::start();
Auth::attemptRememberCookie();
Settings::load();

// Installer redirect - if DB not set up, force install
if (!Settings::get('site_name') && Request::path() !== '/install' && !str_starts_with(Request::path(), '/install')) {
    header('Location: ' . Request::url('/install'));
    exit;
}

// Maintenance mode
if ((bool)Settings::get('maintenance_mode', '0') && (!Auth::check() || !Auth::isAdmin())) {
    if (Request::path() !== '/maintenance') {
        header('Location: ' . Request::url('/maintenance'));
        exit;
    }
}

// ------------------------------------------------------------------
// Middleware
// ------------------------------------------------------------------
Router::middleware('auth', [RequireAuth::class, 'handle']);
Router::middleware('guest', [Guest::class, 'handle']);
Router::middleware('admin', [RequireAdmin::class, 'handle']);

// ------------------------------------------------------------------
// Public Routes
// ------------------------------------------------------------------
Router::get('/', [HomeController::class, 'index']);
Router::get('/maintenance', [HomeController::class, 'maintenance']);
Router::get('/pricing', [HomeController::class, 'pricing']);
Router::get('/products', [ProductController::class, 'listing']);
Router::get('/products/{slug}', [ProductController::class, 'show']);

// ------------------------------------------------------------------
// Auth Routes
// ------------------------------------------------------------------
Router::get('/login', [AuthController::class, 'showLogin'], ['guest']);
Router::post('/login', [AuthController::class, 'login'], ['guest']);
Router::get('/register', [AuthController::class, 'showRegister'], ['guest']);
Router::post('/register', [AuthController::class, 'register'], ['guest']);
Router::post('/logout', [AuthController::class, 'logout'], ['auth']);
Router::get('/forgot-password', [AuthController::class, 'forgot'], ['guest']);
Router::post('/forgot-password', [AuthController::class, 'sendReset'], ['guest']);
Router::get('/reset-password', [AuthController::class, 'showReset'], ['guest']);
Router::post('/reset-password', [AuthController::class, 'resetPassword'], ['guest']);

// ------------------------------------------------------------------
// Checkout / Ordering
// ------------------------------------------------------------------
Router::get('/products/{id}/order', [ProductController::class, 'order'], ['auth']);
Router::post('/products/{id}/checkout', [ProductController::class, 'checkout'], ['auth']);

// ------------------------------------------------------------------
// Dashboard
// ------------------------------------------------------------------
Router::get('/dashboard', [DashboardController::class, 'index'], ['auth']);
Router::get('/dashboard/billing', [DashboardController::class, 'billing'], ['auth']);
Router::post('/dashboard/deposit', [DashboardController::class, 'deposit'], ['auth']);
Router::get('/dashboard/invoices', [DashboardController::class, 'invoices'], ['auth']);
Router::get('/dashboard/profile', [DashboardController::class, 'profile'], ['auth']);
Router::post('/dashboard/profile', [DashboardController::class, 'updateProfile'], ['auth']);

// ------------------------------------------------------------------
// Support / Tickets
// ------------------------------------------------------------------
Router::get('/support', [SupportController::class, 'index'], ['auth']);
Router::get('/support/create', [SupportController::class, 'create'], ['auth']);
Router::post('/support/create', [SupportController::class, 'store'], ['auth']);
Router::get('/support/{id}', [SupportController::class, 'show'], ['auth']);
Router::post('/support/{id}/reply', [SupportController::class, 'reply'], ['auth']);
Router::post('/support/{id}/close', [SupportController::class, 'close'], ['auth']);

// ------------------------------------------------------------------
// Server Control
// ------------------------------------------------------------------
Router::get('/servers', [ServerController::class, 'index'], ['auth']);
Router::get('/servers/{id}', [ServerController::class, 'show'], ['auth']);
Router::get('/servers/{id}/console', [ServerController::class, 'console'], ['auth']);
Router::get('/servers/{id}/settings', [ServerController::class, 'settings'], ['auth']);
Router::post('/servers/{id}/power', [ServerController::class, 'power'], ['auth']);
Router::post('/servers/{id}/command', [ServerController::class, 'command'], ['auth']);
Router::post('/servers/{id}/rename', [ServerController::class, 'rename'], ['auth']);
Router::post('/servers/{id}/delete', [ServerController::class, 'destroy'], ['auth']);
Router::get('/api/servers/{id}/resources', [ServerController::class, 'resources'], ['auth']);

// ------------------------------------------------------------------
// Admin Panel
// ------------------------------------------------------------------
Router::get('/admin', [AdminController::class, 'dashboard'], ['admin']);

// Admin - Products
Router::get('/admin/products', [AdminController::class, 'products'], ['admin']);
Router::get('/admin/products/create', [AdminController::class, 'productCreate'], ['admin']);
Router::post('/admin/products/create', [AdminController::class, 'productStore'], ['admin']);
Router::get('/admin/products/{id}/edit', [AdminController::class, 'productEdit'], ['admin']);
Router::post('/admin/products/{id}/edit', [AdminController::class, 'productUpdate'], ['admin']);
Router::post('/admin/products/{id}/delete', [AdminController::class, 'productDelete'], ['admin']);
Router::get('/api/admin/eggs', [AdminController::class, 'eggsForNest'], ['admin']);
Router::get('/api/admin/egg', [AdminController::class, 'eggDetails'], ['admin']);

// Admin - Users
Router::get('/admin/users', [AdminController::class, 'users'], ['admin']);
Router::get('/admin/users/{id}/edit', [AdminController::class, 'userEdit'], ['admin']);
Router::post('/admin/users/{id}/edit', [AdminController::class, 'userUpdate'], ['admin']);
Router::post('/admin/users/{id}/delete', [AdminController::class, 'userDelete'], ['admin']);

// Admin - Orders
Router::get('/admin/orders', [AdminController::class, 'orders'], ['admin']);
Router::post('/admin/orders/{id}/update', [AdminController::class, 'orderUpdate'], ['admin']);

// Admin - Servers
Router::get('/admin/servers', [AdminController::class, 'servers'], ['admin']);
Router::post('/admin/servers/{id}/suspend', [AdminController::class, 'serverSuspend'], ['admin']);
Router::post('/admin/servers/{id}/unsuspend', [AdminController::class, 'serverUnsuspend'], ['admin']);
Router::post('/admin/servers/{id}/delete', [AdminController::class, 'serverDelete'], ['admin']);

// Admin - Categories
Router::get('/admin/categories', [AdminController::class, 'categories'], ['admin']);
Router::post('/admin/categories/create', [AdminController::class, 'categoryStore'], ['admin']);
Router::post('/admin/categories/{id}/delete', [AdminController::class, 'categoryDelete'], ['admin']);

// Admin - Coupons
Router::get('/admin/coupons', [AdminController::class, 'coupons'], ['admin']);
Router::post('/admin/coupons/create', [AdminController::class, 'couponStore'], ['admin']);
Router::post('/admin/coupons/{id}/delete', [AdminController::class, 'couponDelete'], ['admin']);

// Admin - Announcements
Router::get('/admin/announcements', [AdminController::class, 'announcements'], ['admin']);
Router::post('/admin/announcements/create', [AdminController::class, 'announcementStore'], ['admin']);
Router::post('/admin/announcements/{id}/delete', [AdminController::class, 'announcementDelete'], ['admin']);

// Admin - Settings
Router::get('/admin/settings', [AdminController::class, 'settings'], ['admin']);
Router::post('/admin/settings', [AdminController::class, 'settingsSave'], ['admin']);
Router::get('/admin/settings/panel', [AdminController::class, 'panelSettings'], ['admin']);
Router::post('/admin/settings/panel', [AdminController::class, 'panelSettingsSave'], ['admin']);
Router::post('/api/admin/panel/test', [AdminController::class, 'panelTest'], ['admin']);
Router::get('/admin/settings/customization', [AdminController::class, 'customization'], ['admin']);
Router::post('/admin/settings/customization', [AdminController::class, 'customizationSave'], ['admin']);
Router::get('/admin/settings/email', [AdminController::class, 'emailSettings'], ['admin']);
Router::post('/admin/settings/email', [AdminController::class, 'emailSettingsSave'], ['admin']);
Router::get('/admin/settings/adsense', [AdminController::class, 'adsense'], ['admin']);
Router::post('/admin/settings/adsense', [AdminController::class, 'adsenseSave'], ['admin']);
Router::get('/admin/activity', [AdminController::class, 'activity'], ['admin']);

// ------------------------------------------------------------------
// Install
// ------------------------------------------------------------------
Router::get('/install', function() { require BASE_PATH . '/public/install.php'; });

// ------------------------------------------------------------------
// Dispatch
// ------------------------------------------------------------------
Router::dispatch();
