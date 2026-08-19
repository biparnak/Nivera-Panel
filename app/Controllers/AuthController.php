<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\PanelClient;
use App\Core\Request;
use App\Core\Session;
use App\Core\Validator;
use App\Models\ActivityLog;
use App\Models\User;

final class AuthController extends Controller
{
    public function showLogin(): void { $this->view('auth/login'); }

    public function showRegister(): void
    {
        if ((int)Settings::get('registration_enabled', '1') === 0) {
            Session::flash('error', 'Registration is currently disabled.');
            $this->redirect('/login');
        }
        $this->view('auth/register');
    }

    public function login(): void
    {
        $this->requireCsrf();
        if ($this->isLockedOut(Request::ip())) {
            Session::flash('error', 'Too many failed attempts. Please wait a few minutes.');
            $this->redirect('/login');
        }
        $identifier = trim((string)($_POST['identifier'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $user = str_contains($identifier, '@') ? User::findByEmail($identifier) : User::findByUsername($identifier);
        if (!$user || !$user->verifyPassword($password)) {
            $this->recordAttempt(Request::ip(), $identifier, false);
            Session::flash('error', 'Invalid credentials.');
            $this->redirect('/login');
        }
        if ($user->isSuspended() || $user->isBanned()) {
            Session::flash('error', 'Your account has been ' . $user->status . '.');
            $this->redirect('/login');
        }
        $this->recordAttempt(Request::ip(), $identifier, true);
        Auth::login($user, (bool)Request::post('remember'));
        ActivityLog::log('login', 'User logged in');
        Session::flash('success', 'Welcome back, ' . e($user->username) . '!');
        $this->redirect('/dashboard');
    }

    public function register(): void
    {
        $this->requireCsrf();
        if ((int)Settings::get('registration_enabled', '1') === 0) {
            Session::flash('error', 'Registration is currently disabled.');
            $this->redirect('/login');
        }
        $v = new Validator();
        if (!$v->make($_POST, [
            'username' => 'required|min:3|max:32|unique:users,username',
            'email' => 'required|email|max:190|unique:users,email',
            'password' => 'required|min:8|max:72|confirmed',
        ])) {
            Session::flash('error', $v->firstError());
            $this->redirect('/register');
        }

        $user = User::create([
            'username' => trim((string)$_POST['username']),
            'email' => trim(strtolower((string)$_POST['email'])),
            'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'role' => 'user',
        ]);

        // Auto-create user on hosting panel
        if ((int)Settings::get('auto_create_user', '1') === 1) {
            $panel = PanelClient::instance();
            if ($panel->isEnabled()) {
                $user->getOrCreatePanelUser();
            }
        }

        ActivityLog::log('register', 'New account created');
        Auth::login($user);
        Session::flash('success', 'Account created! Welcome to NiveraCloud.');
        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        $this->requireCsrf();
        ActivityLog::log('logout', 'User logged out');
        Auth::logout();
        \App\Core\Session::start();
        Session::flash('success', 'You have been logged out.');
        $this->redirect('/login');
    }

    public function forgot(): void { $this->view('auth/forgot'); }

    public function sendReset(): void
    {
        $this->requireCsrf();
        $email = trim(strtolower((string)($_POST['email'] ?? '')));
        $user = User::findByEmail($email);
        if ($user) {
            $token = bin2hex(random_bytes(32));
            Database::query('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)', [$user->id, hash('sha256', $token), date('Y-m-d H:i:s', time() + 3600)]);
            $resetUrl = Request::url('/reset-password?token=' . urlencode($token));
            Session::flash('info', "Password reset link: {$resetUrl}");
        } else {
            Session::flash('error', 'No account found with that email.');
        }
        $this->redirect('/forgot-password');
    }

    public function showReset(): void
    {
        $token = (string)Request::get('token', '');
        $row = Database::fetchOne('SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW()', [hash('sha256', $token)]);
        if (!$row) {
            Session::flash('error', 'Invalid or expired reset link.');
            $this->redirect('/forgot-password');
        }
        $this->view('auth/reset', ['token' => $token]);
    }

    public function resetPassword(): void
    {
        $this->requireCsrf();
        $token = (string)($_POST['token'] ?? '');
        $row = Database::fetchOne('SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW()', [hash('sha256', $token)]);
        if (!$row) {
            Session::flash('error', 'Invalid or expired reset link.');
            $this->redirect('/forgot-password');
        }
        $v = new Validator();
        if (!$v->make($_POST, ['password' => 'required|min:8|max:72|confirmed'])) {
            Session::flash('error', $v->firstError());
            $this->redirect('/reset-password?token=' . urlencode($token));
        }
        $user = User::find((int)$row['user_id']);
        if ($user) $user->update(['password' => password_hash($_POST['password'], PASSWORD_DEFAULT)]);
        Database::query('UPDATE password_resets SET used = 1 WHERE id = ?', [$row['id']]);
        Session::flash('success', 'Password updated. You can now log in.');
        $this->redirect('/login');
    }

    private function recordAttempt(string $ip, string $username, bool $success): void
    {
        Database::query('INSERT INTO login_attempts (ip, username, success) VALUES (?, ?, ?)', [$ip, mb_substr($username, 0, 64), (int)$success]);
    }

    private function isLockedOut(string $ip): bool
    {
        $count = (int)Database::query('SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND success = 0 AND attempted_at > ?', [$ip, date('Y-m-d H:i:s', time() - LOGIN_LOCKOUT_SECONDS)])->fetchColumn();
        return $count >= MAX_LOGIN_ATTEMPTS;
    }
}
