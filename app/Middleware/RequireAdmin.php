<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\View;

final class RequireAdmin
{
    public static function handle(): bool
    {
        if (!Auth::check()) {
            Session::flash('error', 'Please log in to continue.');
            header('Location: ' . \App\Core\Request::url('/login'));
            exit;
        }
        if (!Auth::isAdmin()) {
            View::renderError('You do not have permission to access this page.', 403);
            exit;
        }
        return true;
    }
}
