<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\User;

final class Auth
{
    public static function user(): ?User
    {
        $id = Session::get('user_id');
        return $id ? User::find((int)$id) : null;
    }

    public static function id(): ?int
    {
        $id = Session::get('user_id');
        return $id ? (int)$id : null;
    }

    public static function check(): bool
    {
        return Session::has('user_id');
    }

    public static function isAdmin(): bool
    {
        $user = self::user();
        return $user !== null && $user->role === 'admin';
    }

    public static function login(User $user, bool $remember = false): void
    {
        Session::set('user_id', $user->id);
        Session::set('_started', time());
        session_regenerate_id(true);
        $user->update(['last_login_at' => date('Y-m-d H:i:s'), 'last_login_ip' => Request::ip()]);
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $user->update(['remember_token' => $token]);
            setcookie('nc_remember', $user->id . '.' . $token, time() + 60 * 60 * 24 * 30, '/', '', false, true);
        }
    }

    public static function attemptRememberCookie(): void
    {
        if (self::check()) return;
        $cookie = $_COOKIE['nc_remember'] ?? '';
        if ($cookie === '' || !str_contains($cookie, '.')) return;
        [$id, $token] = explode('.', $cookie, 2);
        $user = User::find((int)$id);
        if ($user && hash_equals((string)$user->remember_token, (string)$token)) {
            self::login($user, true);
        }
    }

    public static function logout(): void
    {
        if (self::id()) {
            $user = User::find(self::id());
            if ($user) $user->update(['remember_token' => null]);
        }
        setcookie('nc_remember', '', time() - 42000, '/');
        Session::destroy();
    }
}
