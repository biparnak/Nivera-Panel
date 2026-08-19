<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Session;
use App\Core\Settings;
use App\Core\Validator;

abstract class Controller
{
    protected function view(string $template, array $data = [], string $layout = 'main'): void
    {
        \App\Core\View::layout($layout);
        \App\Core\View::render($template, $data);
    }

    protected function validate(array $rules): array
    {
        $validator = new Validator();
        if (!$validator->make($_POST, $rules)) {
            Session::flash('error', $validator->firstError());
            $this->back();
        }
        return $_POST;
    }

    protected function back(): void
    {
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? Request::url('/')));
        exit;
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . Request::url($path));
        exit;
    }

    protected function requireCsrf(): void { Csrf::validate(); }

    protected function requireAuth(): void
    {
        if (!\App\Core\Auth::check()) {
            Session::flash('error', 'Please log in to continue.');
            $this->redirect('/login');
        }
    }

    protected function requireAdmin(): void
    {
        $this->requireAuth();
        if (!\App\Core\Auth::isAdmin()) {
            \App\Core\View::renderError('You do not have permission.', 403);
            exit;
        }
    }

    protected function currency(): string { return (string)Settings::get('currency_symbol', CURRENCY_SYMBOL); }
    protected function formatMoney(float $amount): string { return $this->currency() . number_format($amount, 2); }
    protected function json(mixed $data, int $code = 200): void { \App\Core\View::json($data, $code); exit; }
}
