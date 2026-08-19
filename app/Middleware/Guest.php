<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

final class Guest
{
    public static function handle(): bool
    {
        if (Auth::check()) {
            header('Location: ' . \App\Core\Request::url('/dashboard'));
            exit;
        }
        return true;
    }
}
