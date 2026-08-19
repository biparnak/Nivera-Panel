<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\PanelClient;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Announcement;
use App\Models\Order;
use App\Models\Product;
use App\Models\Server;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $user = Auth::user();
        $servers = $user->servers();
        $panel = PanelClient::instance();
        $serverStates = [];
        foreach ($servers as $server) {
            $serverStates[$server->id] = $server->state;
            if ($server->external_identifier && $panel->isEnabled()) {
                $resources = $panel->getResources($server->getIdentifier());
                if ($resources) $serverStates[$server->id] = $resources['current_state'];
            }
        }
        $this->view('dashboard/index', [
            'user' => $user,
            'servers' => $servers,
            'serverCount' => count($servers),
            'orders' => $user->orders(),
            'serverStates' => $serverStates,
            'announcements' => Announcement::allActive(5),
        ]);
    }

    public function billing(): void
    {
        $user = Auth::user();
        $this->view('dashboard/billing', ['user' => $user, 'orders' => $user->orders()]);
    }

    public function deposit(): void
    {
        $this->requireCsrf();
        $user = Auth::user();
        $amount = round((float)($_POST['amount'] ?? 0), 2);
        if ($amount < 1) {
            Session::flash('error', 'Minimum deposit is ' . $this->formatMoney(1) . '.');
            $this->redirect('/dashboard/billing');
        }
        $order = Order::create([
            'order_number' => Order::generateNumber(),
            'user_id' => $user->id,
            'amount' => $amount,
            'status' => 'pending',
            'payment_method' => 'manual',
        ]);
        Session::flash('info', "Deposit order #{$order->order_number} for {$this->formatMoney($amount)} created.");
        $this->redirect('/dashboard/billing');
    }

    public function profile(): void
    {
        $this->view('dashboard/profile', ['user' => Auth::user()]);
    }

    public function updateProfile(): void
    {
        $this->requireCsrf();
        $user = Auth::user();
        $v = new Validator();
        if (!$v->make(['email' => $_POST['email']], ['email' => 'required|email|max:190'])) {
            Session::flash('error', $v->firstError());
            $this->redirect('/dashboard/profile');
        }
        $user->update(['email' => trim(strtolower((string)$_POST['email']))]);
        if (!empty($_POST['current_password']) || !empty($_POST['new_password'])) {
            if (!$user->verifyPassword((string)$_POST['current_password'])) {
                Session::flash('error', 'Current password is incorrect.');
                $this->redirect('/dashboard/profile');
            }
            if (!$v->make($_POST, ['new_password' => 'required|min:8|max:72'])) {
                Session::flash('error', $v->firstError());
                $this->redirect('/dashboard/profile');
            }
            $user->update(['password' => password_hash($_POST['new_password'], PASSWORD_DEFAULT)]);
        }
        Session::flash('success', 'Profile updated.');
        $this->redirect('/dashboard/profile');
    }

    public function invoices(): void
    {
        $this->view('dashboard/invoices', ['user' => Auth::user(), 'orders' => Auth::user()->orders()]);
    }
}
