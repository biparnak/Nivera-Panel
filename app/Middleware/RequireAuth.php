<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Session;

final class RequireAuth
{
    public static function handle(): bool
    {
        if (!Auth::check()) {
            Session::flash('error', 'Please log in to continue.');
            header('Location: ' . \App\Core\Request::url('/login'));
            exit;
        }
        return true;
    }
}
