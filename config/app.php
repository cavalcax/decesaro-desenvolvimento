<?php

declare(strict_types=1);

namespace Config;

use App\Helpers\Env;

class App
{
    public static function name(): string
    {
        return (string)Env::get('APP_NAME', 'Desenvolvimento');
    }

    public static function url(string $path = ''): string
    {
        $base = rtrim((string)Env::get('APP_URL', 'http://localhost/desenvolvimento'), '/');
        // Se a requisição veio diretamente para public/, ajustar
        if ($path === '') {
            return $base;
        }
        return $base . '/' . ltrim($path, '/');
    }

    public static function isDebug(): bool
    {
        return (bool)Env::get('APP_DEBUG', false);
    }

    public static function timezone(): string
    {
        return (string)Env::get('APP_TIMEZONE', 'America/Sao_Paulo');
    }
}
