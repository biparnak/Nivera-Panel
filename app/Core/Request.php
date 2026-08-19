<?php
declare(strict_types=1);

namespace App\Core;

final class Request
{
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function path(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $base = parse_url(self::url('/'), PHP_URL_PATH);
        $path = substr($uri, strlen($base));
        return '/' . trim($path ?? '', '/');
    }

    public static function url(string $path = ''): string
    {
        $defined = defined('APP_URL') ? APP_URL : '';
        if ($defined !== '') {
            return rtrim($defined, '/') . '/' . ltrim($path, '/');
        }
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . '/' . ltrim($path, '/');
    }

    public static function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public static function post(string $key = '', mixed $default = null): mixed
    {
        return $key === '' ? $_POST : ($_POST[$key] ?? $default);
    }

    public static function get(string $key = '', mixed $default = null): mixed
    {
        return $key === '' ? $_GET : ($_GET[$key] ?? $default);
    }

    public static function wantsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return str_contains($accept, 'application/json') || isset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    public static function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }
}
