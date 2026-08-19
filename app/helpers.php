<?php
declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(\App\Core\Csrf::token()) . '">';
}

function asset(string $path): string
{
    $base = \App\Core\Request::url('/');
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

function formatMoney(float $amount, string $symbol = '$'): string
{
    return $symbol . number_format($amount, 2);
}

function timeAgo(string $datetime): string
{
    $now = new \DateTime();
    $ago = new \DateTime($datetime);
    $diff = $now->diff($ago);
    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'just now';
}

function randomColor(): string
{
    $colors = ['#5865f2', '#57f287', '#fee75c', '#eb459e', '#ed4245', '#3ba55d', '#faa61a'];
    return $colors[array_rand($colors)];
}

function generateOrderNumber(): string
{
    return 'NC-' . strtoupper(bin2hex(random_bytes(4)));
}
