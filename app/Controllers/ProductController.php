<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\PanelClient;
use App\Core\Request;
use App\Core\Session;
use App\Core\Settings;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Server;

final class ProductController extends Controller
{
    public function listing(): void
    {
        $categories = Category::allActive();
        $products = Product::allActive();
        $this->view('products/listing', ['categories' => $categories, 'products' => $products]);
    }

    public function show(string $slug): void
    {
        $product = Product::findBySlug($slug) ?: Product::find((int)$slug);
        if (!$product || !$product->isActive()) {
            \App\Core\View::renderError('Product not found.', 404);
            return;
        }
        $this->view('products/show', ['product' => $product]);
    }

    public function order(string $id): void
    {
        $this->requireAuth();
        $product = Product::find((int)$id);
        if (!$product || !$product->isActive()) {
            \App\Core\View::renderError('Product not found.', 404);
            return;
        }
        $this->view('products/order', ['product' => $product]);
    }

    public function checkout(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $product = Product::find((int)$id);
        if (!$product || !$product->isActive()) {
            Session::flash('error', 'Product not found.');
            $this->redirect('/products');
        }

        $cycle = Request::post('billing_cycle') === 'yearly' ? 'yearly' : 'monthly';
        $amount = $product->price($cycle) + $product->setup_fee;

        // Apply coupon
        $couponCode = trim((string)($_POST['coupon'] ?? ''));
        if ($couponCode !== '' && (int)Settings::get('enable_coupons', '1') === 1) {
            $coupon = Coupon::findByCode($couponCode);
            if ($coupon && $coupon->isValid($amount)) {
                $discount = $coupon->calculateDiscount($amount);
                $amount = max(0, $amount - $discount);
                $coupon->update(['used_count' => $coupon->used_count + 1]);
            } else {
                Session::flash('error', 'Invalid coupon code.');
                $this->redirect('/products/' . $product->id . '/order');
            }
        }

        $user = Auth::user();
        $paymentMethod = Request::post('payment_method', 'balance');

        $order = Order::create([
            'order_number' => Order::generateNumber(),
            'user_id' => $user->id,
            'product_id' => $product->id,
            'billing_cycle' => $cycle,
            'amount' => $amount,
            'status' => 'pending',
            'payment_method' => $paymentMethod,
        ]);

        if ($paymentMethod === 'balance') {
            if ($user->deductFunds($amount)) {
                $order->update(['status' => 'paid', 'payment_method' => 'balance', 'paid_at' => date('Y-m-d H:i:s')]);
                Payment::create(['order_id' => $order->id, 'user_id' => $user->id, 'gateway' => 'balance', 'amount' => $amount, 'status' => 'completed']);
                $server = $this->provisionServer($order, $product, $user);
                if ($server) {
                    Session::flash('success', "Payment received. Server '{$server->name}' is deploying.");
                    $this->redirect('/servers/' . $server->id);
                }
                Session::flash('success', 'Payment received. Order #' . $order->order_number . ' complete.');
                $this->redirect('/dashboard');
            }
            Session::flash('error', 'Insufficient balance.');
            $order->update(['status' => 'cancelled']);
            $this->redirect('/dashboard/billing');
        }

        Session::flash('info', 'Order #' . $order->order_number . ' created. Payment is pending.');
        $this->redirect('/dashboard/billing');
    }

    public function provisionServer(Order $order, Product $product, \App\Models\User $user): ?Server
    {
        $panel = PanelClient::instance();

        if (!$product->egg_id) {
            return Server::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'order_id' => $order->id,
                'name' => $product->name . ' #' . $order->id,
                'panel_type' => $panel->getType(),
                'state' => 'pending',
            ]);
        }

        $nestId = $product->nest_id ?: (int)Settings::get('pterodactyl_nest_id', 1);
        $nodeId = $product->node_id ?: (int)Settings::get('pterodactyl_node_id', 1);

        $panelUserId = $user->getOrCreatePanelUser();
        if (!$panelUserId && $panel->isEnabled()) {
            Session::flash('error', 'Failed to create panel user.');
            return null;
        }

        $allocations = $panel->listAllocations($nodeId);
        $allocationId = $allocations[0]['id'] ?? 1;

        $variables = $product->getVariables();
        if ($panel->isEnabled()) {
            $egg = $panel->getEgg((int)$product->egg_id, $nestId);
            foreach ($egg['variables'] ?? [] as $var) {
                if (!isset($variables[$var['env_variable']])) $variables[$var['env_variable']] = $var['default_value'] ?? '';
            }
        }

        $result = $panel->createServer([
            'name' => $product->name . '-' . $order->id,
            'pterodactyl_user_id' => $panelUserId,
            'egg_id' => (int)$product->egg_id,
            'nest_id' => $nestId,
            'node_id' => $nodeId,
            'allocation_id' => $allocationId,
            'startup' => $product->startup ?? '',
            'docker_image' => $product->docker_image ?? '',
            'variables' => $variables,
            'memory_mb' => $product->memory_mb,
            'disk_mb' => $product->disk_mb,
            'swap_mb' => $product->swap_mb,
            'cpu_percent' => $product->cpu_percent,
            'io_percent' => $product->io_percent,
            'databases' => $product->databases,
            'backups' => $product->backups,
            'allocations' => $product->allocations,
        ]);

        if (!$result['ok']) {
            Session::flash('error', 'Deployment failed: ' . ($result['error'] ?? 'unknown'));
            return null;
        }

        $expires = match($order->billing_cycle) { 'yearly' => '+1 year', default => '+1 month' };
        $server = Server::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'name' => $product->name . '-' . $order->id,
            'external_id' => $result['server_id'] ?? null,
            'external_identifier' => $result['identifier'] ?? null,
            'external_uuid' => $result['uuid'] ?? null,
            'node_id' => $nodeId,
            'egg_id' => (int)$product->egg_id,
            'memory_mb' => $product->memory_mb,
            'disk_mb' => $product->disk_mb,
            'cpu_percent' => $product->cpu_percent,
            'swap_mb' => $product->swap_mb,
            'io_percent' => $product->io_percent,
            'databases' => $product->databases,
            'backups' => $product->backups,
            'allocations' => $product->allocations,
            'state' => 'deploying',
            'panel_type' => $panel->getType(),
            'expires_at' => date('Y-m-d H:i:s', strtotime($expires)),
        ]);

        $order->update(['status' => 'paid', 'paid_at' => date('Y-m-d H:i:s')]);
        return $server;
    }
}
