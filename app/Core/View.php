<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    private static string $layout = 'main';
    private static array $data = [];
    private static bool $rendered = false;

    public static function layout(string $layout): void { self::$layout = $layout; }
    public static function share(string $key, mixed $value): void { self::$data[$key] = $value; }

    public static function render(string $template, array $data = []): void
    {
        $data = array_merge(self::$data, $data);
        $data['_flash'] = Session::pullFlash();
        $data['_user'] = Auth::user();
        $data['_csrf'] = Csrf::token();
        extract($data, EXTR_SKIP);
        $viewFile = BASE_PATH . '/app/Views/' . $template . '.php';
        if (!is_file($viewFile)) throw new \RuntimeException("View not found: {$template}");
        ob_start();
        require $viewFile;
        $content = ob_get_clean();
        $layoutFile = BASE_PATH . '/app/Views/layouts/' . self::$layout . '.php';
        $layoutData = ['content' => $content, '_flash' => $data['_flash'], '_user' => $data['_user'], '_csrf' => $data['_csrf']];
        extract($layoutData, EXTR_SKIP);
        if (is_file($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
        self::$rendered = true;
    }

    public static function renderError(string $message, int $code = 404): void
    {
        http_response_code($code);
        $title = match($code) { 403 => 'Forbidden', 404 => 'Page Not Found', 419 => 'Page Expired', default => 'Error' };
        if (self::$rendered) { echo '<h1>' . e($code) . '</h1><p>' . e($message) . '</p>'; return; }
        self::layout('guest');
        self::render('errors/error', compact('code', 'message', 'title'));
    }

    public static function json(mixed $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
