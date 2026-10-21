<?php

declare(strict_types=1);

namespace App\Helpers;

use Config\App;

class Router
{
    private static array $routes = [];

    public static function get(string $path, array|callable $handler, array $middlewares = []): void
    {
        self::add('GET', $path, $handler, $middlewares);
    }

    public static function post(string $path, array|callable $handler, array $middlewares = []): void
    {
        self::add('POST', $path, $handler, $middlewares);
    }

    public static function add(string $method, string $path, array|callable $handler, array $middlewares = []): void
    {
        self::$routes[] = [
            'method' => strtoupper($method),
            'path' => rtrim($path, '/') === '' ? '/' : rtrim($path, '/'),
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
    }

    public static function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        // Suporte para _method em formulários se houver
        if ($method === 'POST' && !empty($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // Detectar e remover prefixos base da URL
        $appPath = rtrim(parse_url(\Config\App::url(), PHP_URL_PATH) ?? '', '/');
        $candidates = array_unique(array_filter([
            $appPath . '/public',
            $appPath,
            '/desenvolvimento/public',
            '/desenvolvimento',
            '/public'
        ]));

        foreach ($candidates as $cand) {
            if ($cand !== '' && str_starts_with($uri, $cand)) {
                $uri = substr($uri, strlen($cand));
                break;
            }
        }

        // Se ainda restar /public no início, remover
        if (str_starts_with($uri, '/public')) {
            $uri = substr($uri, 7);
        }

        $uri = rtrim($uri, '/') === '' ? '/' : rtrim($uri, '/');

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = "#^{$pattern}$#";

            if (preg_match($pattern, $uri, $matches)) {
                // Executar middlewares
                foreach ($route['middlewares'] as $middleware) {
                    if (is_callable($middleware)) {
                        call_user_func($middleware);
                    }
                }

                // Filtrar apenas parâmetros nomeados
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                $handler = $route['handler'];
                if (is_callable($handler)) {
                    call_user_func_array($handler, $params);
                    return;
                }

                if (is_array($handler) && count($handler) === 2) {
                    [$class, $action] = $handler;
                    if (class_exists($class)) {
                        $controller = new $class();
                        if (method_exists($controller, $action)) {
                            call_user_func_array([$controller, $action], $params);
                            return;
                        }
                    }
                }

                Response::abort(500, "Manipulador da rota não encontrado.");
            }
        }

        Response::abort(404, "Página não encontrada: {$uri}");
    }
}
