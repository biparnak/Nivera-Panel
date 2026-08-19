<?php
declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    public static function token(): string
    {
        if (!Session::has('_csrf_token')) {
            Session::set('_csrf_token', bin2hex(random_bytes(32)));
        }
        return Session::get('_csrf_token');
    }

    public static function validate(): void
    {
        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!hash_equals(self::token(), (string)$token)) {
            Session::flash('error', 'Invalid security token. Please try again.');
            header('Location: ' . Request::url($_SERVER['HTTP_REFERER'] ?? '/'));
            exit;
        }
    }
}
