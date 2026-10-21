<?php

declare(strict_types=1);

namespace App\Helpers;

use Config\App;

class Response
{
    public static function json(array $data, int $statusCode = 200): never
    {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, no-store, must-revalidate');
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function redirect(string $url): never
    {
        if (!headers_sent()) {
            if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
                $url = App::url($url);
            }
            header("Location: {$url}");
        }
        exit;
    }

    public static function view(string $viewName, array $data = [], ?string $layout = 'layouts/main'): void
    {
        // Extrair variáveis para a view
        extract($data);

        $appName = App::name();
        $currentUser = Auth::user();
        $isAdmin = Auth::isAdmin();
        $csrfToken = Security::csrfToken();
        $csrfField = Security::csrfField();
        $currentTheme = $_COOKIE['decesaro_theme'] ?? ($_SESSION['user_tema'] ?? 'claro');
        $isDarkTheme = ($currentTheme === 'escuro');

        $viewFile = dirname(__DIR__) . '/Views/' . str_replace('.', '/', $viewName) . '.php';

        if (!file_exists($viewFile)) {
            self::abort(500, "View '{$viewName}' não encontrada.");
        }

        // Renderizar conteúdo da view para buffer
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout === null) {
            echo $content;
            return;
        }

        $layoutFile = dirname(__DIR__) . '/Views/' . str_replace('.', '/', $layout) . '.php';
        if (!file_exists($layoutFile)) {
            echo $content;
            return;
        }

        require $layoutFile;
    }

    public static function abort(int $statusCode, string $message = ''): never
    {
        if (!headers_sent()) {
            http_response_code($statusCode);
        }

        $defaultMessages = [
            400 => 'Requisição inválida.',
            401 => 'Não autorizado.',
            403 => 'Acesso proibido.',
            404 => 'Página não encontrada.',
            500 => 'Erro interno do servidor.'
        ];

        $displayMessage = $message !== '' ? $message : ($defaultMessages[$statusCode] ?? 'Ocorreu um erro.');

        // Se for AJAX, retornar JSON
        if ((!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
            (!empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
            self::json(['success' => false, 'message' => $displayMessage], $statusCode);
        }

        self::view('layouts/error', [
            'statusCode' => $statusCode,
            'message' => $displayMessage,
            'pageTitle' => "Erro {$statusCode}"
        ], null);
        exit;
    }
}
