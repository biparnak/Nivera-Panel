<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\PanelClient;
use App\Core\Request;
use App\Core\Session;
use App\Core\Settings;
use App\Core\Validator;
use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Server;
use App\Models\Setting;
use App\Models\User;

final class AdminController extends Controller
{
    protected function view(string $template, array $data = [], string $layout = 'admin'): void
    {
        parent::view($template, $data, $layout);
    }

    public function dashboard(): void
    {
        $this->requireAdmin();
        $panel = PanelClient::instance();
        $this->view('admin/dashboard', [
            'userCount' => User::count(),
            'serverCount' => Server::count(),
            'orderCount' => Order::count(),
            'revenue' => Order::revenue(),
            'revenueMonth' => Order::revenueThisMonth(),
            'pendingOrders' => Order::countByStatus('pending'),
            'recentOrders' => Order::all(10),
            'recentUsers' => User::all('', 10),
            'serverStates' => $this->serverStates(),
            'panelType' => $panel->getTypeLabel(),
            'panelEnabled' => $panel->isEnabled(),
            'activities' => ActivityLog::recent(15),
        ]);
    }

    // --- Products ---
    public function products(): void
    {
        $this->requireAdmin();
        $this->view('admin/products/index', ['products' => Product::all('', 200), 'categories' => Category::allActive()]);
    }

    public function productCreate(): void
    {
        $this->requireAdmin();
        $panel = PanelClient::instance();
        $this->view('admin/products/form', [
            'product' => null,
            'nests' => $panel->listNests(),
            'eggs' => [],
            'nodes' => $panel->listNodes(),
            'nestId' => (int)Settings::get('pterodactyl_nest_id', 1),
            'categories' => Category::allActive(),
        ]);
    }

    public function productStore(): void
    {
        $this->requireAdmin();
        $this->requireCsrf();
        $v = new Validator();
        if (!$v->make($_POST, ['name' => 'required|max:100'])) {
            Session::flash('error', $v->firstError());
            $this->redirect('/admin/products/create');
        }
        $variables = Request::post('variable_key') ? array_combine(
            array_values((array)Request::post('variable_key')),
            array_values((array)Request::post('variable_value'))
        ) : [];
        Product::create([
            'category_id' => $_POST['category_id'] ? (int)$_POST['category_id'] : null,
            'name' => trim((string)$_POST['name']),
            'slug' => Product::slugify((string)$_POST['name']),
            'description' => $_POST['description'] ?? '',
            'icon' => $_POST['icon'] ?? '🖥',
            'price_monthly' => (float)($_POST['price_monthly'] ?? 0),
            'price_yearly' => (float)($_POST['price_yearly'] ?? 0),
            'setup_fee' => (float)($_POST['setup_fee'] ?? 0),
            'memory_mb' => (int)($_POST['memory_mb'] ?? 1024),
            'disk_mb' => (int)($_POST['disk_mb'] ?? 5120),
            'cpu_percent' => (int)($_POST['cpu_percent'] ?? 100),
            'swap_mb' => (int)($_POST['swap_mb'] ?? 512),
            'io_percent' => (int)($_POST['io_percent'] ?? 500),
            'databases' => (int)($_POST['databases'] ?? 1),
            'backups' => (int)($_POST['backups'] ?? 1),
            'allocations' => (int)($_POST['allocations'] ?? 1),
            'slots' => (int)($_POST['slots'] ?? 0),
            'node_id' => $_POST['node_id'] !== '' ? (int)$_POST['node_id'] : null,
            'egg_id' => $_POST['egg_id'] !== '' ? (int)$_POST['egg_id'] : null,
            'nest_id' => $_POST['nest_id'] !== '' ? (int)$_POST['nest_id'] : null,
            'egg_name' => $_POST['egg_name'] ?? null,
            'docker_image' => $_POST['docker_image'] ?: null,
            'startup' => $_POST['startup'] ?: null,
            'default_variables' => $variables ? json_encode($variables) : null,
            'active' => isset($_POST['active']),
            'featured' => isset($_POST['featured']),
        ]);
        ActivityLog::log('product_create', 'Created product: ' . $_POST['name']);
        Session::flash('success', 'Product created.');
        $this->redirect('/admin/products');
    }

    public function productEdit(string $id): void
    {
        $this->requireAdmin();
        $product = Product::find((int)$id);
        if (!$product) { \App\Core\View::renderError('Not found.', 404); return; }
        $panel = PanelClient::instance();
        $nestId = $product->nest_id ?: (int)Settings::get('pterodactyl_nest_id', 1);
        $this->view('admin/products/form', [
            'product' => $product,
            'nests' => $panel->listNests(),
            'eggs' => $product->egg_id ? $panel->listEggs($nestId) : [],
            'nodes' => $panel->listNodes(),
            'nestId' => $nestId,
            'categories' => Category::allActive(),
        ]);
    }

    public function productUpdate(string $id): void
    {
        $this->requireAdmin();
        $this->requireCsrf();
        $product = Product::find((int)$id);
        if (!$product) { \App\Core\View::renderError('Not found.', 404); return; }
        $variables = Request::post('variable_key') ? array_combine(
            array_values((array)Request::post('variable_key')),
            array_values((array)Request::post('variable_value'))
        ) : [];
        $product->update([
            'category_id' => $_POST['category_id'] ? (int)$_POST['category_id'] : null,
            'name' => trim((string)$_POST['name']),
            'description' => $_POST['description'] ?? '',
            'icon' => $_POST['icon'] ?? '🖥',
            'price_monthly' => (float)($_POST['price_monthly'] ?? 0),
            'price_yearly' => (float)($_POST['price_yearly'] ?? 0),
            'setup_fee' => (float)($_POST['setup_fee'] ?? 0),
            'memory_mb' => (int)($_POST['memory_mb'] ?? 1024),
            'disk_mb' => (int)($_POST['disk_mb'] ?? 5120),
            'cpu_percent' => (int)($_POST['cpu_percent'] ?? 100),
            'swap_mb' => (int)($_POST['swap_mb'] ?? 512),
            'io_percent' => (int)($_POST['io_percent'] ?? 500),
            'databases' => (int)($_POST['databases'] ?? 1),
            'backups' => (int)($_POST['backups'] ?? 1),
            'allocations' => (int)($_POST['allocations'] ?? 1),
            'slots' => (int)($_POST['slots'] ?? 0),
            'node_id' => $_POST['node_id'] !== '' ? (int)$_POST['node_id'] : null,
            'egg_id' => $_POST['egg_id'] !== '' ? (int)$_POST['egg_id'] : null,
            'nest_id' => $_POST['nest_id'] !== '' ? (int)$_POST['nest_id'] : null,
            'egg_name' => $_POST['egg_name'] ?? null,
            'docker_image' => $_POST['docker_image'] ?: null,
            'startup' => $_POST['startup'] ?: null,
            'default_variables' => $variables ? json_encode($variables) : null,
            'active' => isset($_POST['active']),
            'featured' => isset($_POST['featured']),
        ]);
        Session::flash('success', 'Product updated.');
        $this->redirect('/admin/products');
    }

    public function productDelete(string $id): void
    {
        $this->requireAdmin();
        $this->requireCsrf();
        $product = Product::find((int)$id);
        if ($product) { $product->delete(); Session::flash('success', 'Product deleted.'); }
        $this->redirect('/admin/products');
    }

    public function eggsForNest(): void
    {
        $this->requireAdmin();
        $nestId = (int)Request::get('nest_id', 1);
        $this->json(['ok' => true, 'eggs' => PanelClient::instance()->listEggs($nestId)]);
    }

    public function eggDetails(): void
    {
        $this->requireAdmin();
        $this->json(['ok' => true, 'egg' => PanelClient::instance()->getEgg((int)Request::get('egg_id', 0), (int)Request::get('nest_id', 1))]);
    }

    // --- Users ---
    public function users(): void
    {
        $this->requireAdmin();
        $search = trim((string)Request::get('q', ''));
        $this->view('admin/users/index', ['users' => User::all($search, 100), 'search' => $search]);
    }

    public function userEdit(string $id): void
    {
        $this->requireAdmin();
        $user = User::find((int)$id);
        if (!$user) { \App\Core\View::renderError('Not found.', 404); return; }
        $this->view('admin/users/edit', ['user' => $user, 'servers' => $user->servers(), 'orders' => $user->orders()]);
    }

    public function userUpdate(string $id): void
    {
        $this->requireAdmin();
        $this->requireCsrf();
        $user = User::find((int)$id);
        if (!$user) { \App\Core\View::renderError('Not found.', 404); return; }
        $user->update([
            'email' => trim(strtolower((string)$_POST['email'])),
            'role' => $_POST['role'] === 'admin' ? 'admin' : 'user',
            'status' => $_POST['status'] ?? 'active',
            'balance' => (float)($_POST['balance'] ?? 0),
        ]);
        if (!empty($_POST['password'])) $user->update(['password' => password_hash($_POST['password'], PASSWORD_DEFAULT)]);
        Session::flash('success', 'User updated.');
        $this->redirect('/admin/users/' . $user->id . '/edit');
    }

    public function userDelete(string $id): void
    {
        $this->requireAdmin();
        $this->requireCsrf();
        $user = User::find((int)$id);
        if ($user && $user->id !== Auth::id()) {
            $panel = PanelClient::instance();
            foreach ($user->servers() as $server) {
                if ($server->external_id && $panel->isEnabled()) $panel->deleteServer($server->external_id);
            }
            $user->delete();
            Session::flash('success', 'User deleted.');
        }
        $this->redirect('/admin/users');
    }

    // --- Orders ---
    public function orders(): void
    {
        $this->requireAdmin();
        $orders = [];
        foreach (Order::all(300) as $order) {
            $orders[] = ['order' => $order, 'user' => $order->user(), 'product' => $order->product()];
        }
        $this->view('admin/orders/index', ['orders' => $orders]);
    }

    public function orderUpdate(string $id): void
    {
        $this->requireAdmin();
        $this->requireCsrf();
        $order = Order::find((int)$id);
        if (!$order) { \App\Core\View::renderError('Not found.', 404); return; }
        $newStatus = (string)$_POST['status'];
        if (in_array($newStatus, ['pending', 'paid', 'cancelled', 'suspended', 'refunded'], true)) {
            $order->update(['status' => $newStatus, 'paid_at' => $newStatus === 'paid' ? date('Y-m-d H:i:s') : $order->paid_at]);
            if ($newStatus === 'paid' && $order->product_id && $order->user_id) {
                $product = $order->product();
                $user = $order->user();
                if ($product && $user && !Server::where('order_id = ?', [$order->id])[0] ?? null) {
                    (new ProductController())->provisionServer($order, $product, $user);
                }
            }
            Session::flash('success', 'Order updated.');
        }
        $this->redirect('/admin/orders');
    }

    // --- Servers ---
    public function servers(): void
    {
        $this->requireAdmin();
        $rows = [];
        foreach (Server::all(300) as $server) {
            $rows[] = ['server' => $server, 'user' => $server->user(), 'product' => $server->product()];
        }
        $this->view('admin/servers/index', ['servers' => $rows]);
    }

    public function serverSuspend(string $id): void
    {
        $this->requireAdmin();
        $this->requireCsrf();
        $server = Server::find((int)$id);
        if ($server) {
            $panel = PanelClient::instance();
            if ($server->external_id && $panel->isEnabled()) $panel->suspendServer($server->external_id);
            $server->update(['state' => 'suspended', 'suspended_at' => date('Y-m-d H:i:s')]);
            Session::flash('success', 'Server suspended.');
        }
        $this->redirect('/admin/servers');
    }

    public function serverUnsuspend(string $id): void
    {
        $this->requireAdmin();
        $this->requireCsrf();
        $server = Server::find((int)$id);
        if ($server) {
            $panel = PanelClient::instance();
            if ($server->external_id && $panel->isEnabled()) $panel->unsuspendServer($server->external_id);
            $server->update(['state' => 'stopped', 'suspended_at' => null]);
            Session::flash('success', 'Server unsuspended.');
        }
        $this->redirect('/admin/servers');
    }

    public function serverDelete(string $id): void
    {
        $this->requireAdmin();
        $this->requireCsrf();
        $server = Server::find((int)$id);
        if ($server) {
            $panel = PanelClient::instance();
            if ($server->external_id && $panel->isEnabled()) $panel->deleteServer($server->external_id);
            $server->delete();
            Session::flash('success', 'Server deleted.');
        }
        $this->redirect('/admin/servers');
    }

    // --- Categories ---
    public function categories(): void
    {
        $this->requireAdmin();
        $this->view('admin/categories/index', ['categories' => Category::all(50)]);
    }

    public function categoryStore(): void
    {
        $this->requireAdmin();
        $this->requireCsrf();
        Category::create([
            'name' => trim((string)($_POST['name'] ?? '')),
            'slug' => Product::slugify($_POST['name'] ?? ''),
            'description' => $_POST['description'] ?? '',
            'icon' => $_POST['icon'] ?? null,
            'active' => isset($_POST['active']),
        ]);
        Session::flash('success', 'Category created.');
        $this->redirect('/admin/categories');
    }

    public function categoryDelete(string $id): void
    {
        $this->requireAdmin();
        $this->requireCsrf();
        $cat = Category::find((int)$id);
        if ($cat) { $cat->delete(); Session::flash('success', 'Category deleted.'); }
        $this->redirect('/admin/categories');
    }

    // --- Coupons ---
    public function coupons(): void
    {
        $this->requireAdmin();
        $this->view('admin/coupons/index', ['coupons' => Coupon::all(100)]);
    }

    public function couponStore(): void
    {
        $this->requireAdmin();
        $this->requireCsrf();
        Coupon::create([
            'code' => strtoupper(trim((string)($_POST['code'] ?? ''))),
            'type' => $_POST['type'] ?? 'percentage',
            'value' => (float)($_POST['value'] ?? 0),
            'max_uses' => $_POST['max_uses'] ? (int)$_POST['max_uses'] : null,
            'min_amount' => (float)($_POST['min_amount'] ?? 0),
            'expires_at' => $_POST['expires_at'] ?: null,
            'active' => isset($_POST['active']),
        ]);
        Session::flash('success', 'Coupon created.');
        $this->redirect('/admin/coupons');
    }

    public function couponDelete(string $id): void
    {
        $this->requireAdmin();
        $this->requireCsrf();
        $coupon = Coupon::find((int)$id);
        if ($coupon) { $coupon->delete(); Session::flash('success', 'Coupon deleted.'); }
        $this->redirect('/admin/coupons');
    }

    // --- Announcements ---
    public function announcements(): void
    {
        $this->requireAdmin();
        $this->view('admin/announcements/index', ['announcements' => Announcement::all(50)]);
    }

    public function announcementStore(): void
    {
        $this->requireAdmin();
        $this->requireCsrf();
        Announcement::create([
            'title' => trim((string)($_POST['title'] ?? '')),
            'content' => $_POST['content'] ?? '',
            'type' => $_POST['type'] ?? 'info',
            'pinned' => isset($_POST['pinned']) ? 1 : 0,
            'author_id' => Auth::id(),
        ]);
        Session::flash('success', 'Announcement posted.');
        $this->redirect('/admin/announcements');
    }

    public function announcementDelete(string $id): void
    {
        $this->requireAdmin();
        $this->requireCsrf();
        $ann = Announcement::find((int)$id);
        if ($ann) { $ann->delete(); Session::flash('success', 'Announcement deleted.'); }
        $this->redirect('/admin/announcements');
    }

    // --- Settings ---
    public function settings(): void
    {
        $this->requireAdmin();
        $this->view('admin/settings/general', ['settings' => Settings::all()]);
    }

    public function settingsSave(): void
    {
        $this->requireAdmin();
        $this->requireCsrf();
        $allowed = ['site_name', 'site_tagline', 'site_description', 'currency_symbol', 'currency_code', 'footer_text', 'footer_links', 'hero_title', 'hero_subtitle', 'registration_enabled', 'maintenance_mode', 'maintenance_message', 'auto_deploy', 'auto_create_user', 'panel_type'];
        $values = [];
        foreach ($allowed as $key) $values[$key] = $_POST[$key] ?? '';
        Setting::massUpdate($values);
        Session::flash('success', 'Settings saved.');
        $this->redirect('/admin/settings');
    }

    // --- Panel Integration Settings ---
    public function panelSettings(): void
    {
        $this->requireAdmin();
        $panel = PanelClient::instance();
        $this->view('admin/settings/panel', [
            'settings' => Settings::all(),
            'panelType' => $panel->getTypeLabel(),
            'panelEnabled' => $panel->isEnabled(),
            'nests' => $panel->listNests(),
            'nodes' => $panel->listNodes(),
        ]);
    }

    public function panelSettingsSave(): void
    {
        $this->requireAdmin();
        $this->requireCsrf();
        $panelType = $_POST['panel_type'] ?? 'pterodactyl';
        Setting::massUpdate([
            'panel_type' => $panelType,
            'pterodactyl_enabled' => isset($_POST['pterodactyl_enabled']) ? '1' : '0',
            'pterodactyl_url' => rtrim((string)($_POST['pterodactyl_url'] ?? ''), '/'),
            'pterodactyl_api_key' => trim((string)($_POST['pterodactyl_api_key'] ?? '')),
            'pterodactyl_node_id' => (string)(int)($_POST['pterodactyl_node_id'] ?? 1),
            'pterodactyl_nest_id' => (string)(int)($_POST['pterodactyl_nest_id'] ?? 1),
            'pterodactyl_default_docker_image' => $_POST['pterodactyl_default_docker_image'] ?? '',
            'pterodactyl_startup' => $_POST['pterodactyl_startup'] ?? '',
            'pufferpanel_enabled' => isset($_POST['pufferpanel_enabled']) ? '1' : '0',
            'pufferpanel_url' => rtrim((string)($_POST['pufferpanel_url'] ?? ''), '/'),
            'pufferpanel_client_token' => trim((string)($_POST['pufferpanel_client_token'] ?? '')),
            'pufferpanel_client_secret' => trim((string)($_POST['pufferpanel_client_secret'] ?? '')),
            'pelican_enabled' => isset($_POST['pelican_enabled']) ? '1' : '0',
            'pelican_url' => rtrim((string)($_POST['pelican_url'] ?? ''), '/'),
            'pelican_api_key' => trim((string)($_POST['pelican_api_key'] ?? '')),
        ]);
        Session::flash('success', 'Panel settings saved.');
        $this->redirect('/admin/settings/panel');
    }

    public function panelTest(): void
    {
        $this->requireAdmin();
        $panel = PanelClient::instance();
        $this->json([
            'connected' => $panel->isEnabled(),
            'type' => $panel->getTypeLabel(),
            'nests' => count($panel->listNests()),
            'nodes' => count($panel->listNodes()),
        ]);
    }

    // --- Customization / CSS ---
    public function customization(): void
    {
        $this->requireAdmin();
        $this->view('admin/settings/customization', ['settings' => Settings::all()]);
    }

    public function customizationSave(): void
    {
        $this->requireAdmin();
        $this->requireCsrf();
        $accentColor = preg_match('/^#[0-9a-fA-F]{6}$/', (string)($_POST['accent_color'] ?? '')) ? $_POST['accent_color'] : '#7c3aed';

        // Handle logo upload
        $logoUrl = trim((string)($_POST['logo_url'] ?? ''));
        if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['logo_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'])) {
                $filename = 'logo_' . time() . '.' . $ext;
                $dest = UPLOAD_PATH . '/' . $filename;
                if (!is_dir(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0755, true);
                move_uploaded_file($_FILES['logo_file']['tmp_name'], $dest);
                $logoUrl = 'uploads/' . $filename;
            }
        }

        Setting::massUpdate([
            'logo_url' => $logoUrl,
            'favicon_url' => trim((string)($_POST['favicon_url'] ?? '')),
            'accent_color' => $accentColor,
            'theme_mode' => in_array($_POST['theme_mode'] ?? '', ['dark', 'light'], true) ? $_POST['theme_mode'] : 'dark',
            'custom_css' => (string)($_POST['custom_css'] ?? ''),
            'custom_js' => (string)($_POST['custom_js'] ?? ''),
            'hero_title' => (string)($_POST['hero_title'] ?? ''),
            'hero_subtitle' => (string)($_POST['hero_subtitle'] ?? ''),
            'footer_text' => (string)($_POST['footer_text'] ?? ''),
        ]);
        Session::flash('success', 'Customization saved.');
        $this->redirect('/admin/settings/customization');
    }

    // --- Email Settings ---
    public function emailSettings(): void
    {
        $this->requireAdmin();
        $this->view('admin/settings/email', ['settings' => Settings::all()]);
    }

    public function emailSettingsSave(): void
    {
        $this->requireAdmin();
        $this->requireCsrf();
        Setting::massUpdate([
            'mail_enabled' => isset($_POST['mail_enabled']) ? '1' : '0',
            'smtp_host' => $_POST['smtp_host'] ?? '',
            'smtp_port' => $_POST['smtp_port'] ?? '587',
            'smtp_user' => $_POST['smtp_user'] ?? '',
            'smtp_pass' => $_POST['smtp_pass'] ?? '',
            'smtp_from' => $_POST['smtp_from'] ?? '',
        ]);
        Session::flash('success', 'Email settings saved.');
        $this->redirect('/admin/settings/email');
    }

    // --- AdSense ---
    public function adsense(): void
    {
        $this->requireAdmin();
        $this->view('admin/settings/adsense', ['settings' => Settings::all()]);
    }

    public function adsenseSave(): void
    {
        $this->requireAdmin();
        $this->requireCsrf();
        Setting::massUpdate([
            'adsense_enabled' => isset($_POST['adsense_enabled']) ? '1' : '0',
            'adsense_client' => $_POST['adsense_client'] ?? '',
            'adsense_slot_header' => $_POST['adsense_slot_header'] ?? '',
            'adsense_slot_footer' => $_POST['adsense_slot_footer'] ?? '',
        ]);
        Session::flash('success', 'AdSense settings saved.');
        $this->redirect('/admin/settings/adsense');
    }

    // --- Activity Log ---
    public function activity(): void
    {
        $this->requireAdmin();
        $this->view('admin/activity', ['activities' => ActivityLog::recent(200)]);
    }

    private function serverStates(): array
    {
        $states = [];
        foreach (Database::fetchAll('SELECT state, COUNT(*) AS c FROM servers GROUP BY state') as $row) {
            $states[$row['state']] = (int)$row['c'];
        }
        return $states;
    }
}
