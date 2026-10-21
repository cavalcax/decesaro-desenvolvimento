<?php

declare(strict_types=1);

namespace App\Helpers;

class Env
{
    private static ?array $variables = null;

    public static function load(string $path): void
    {
        if (self::$variables !== null) {
            return;
        }

        self::$variables = [];

        if (!file_exists($path)) {
            $example = dirname($path) . DIRECTORY_SEPARATOR . '.env.example';
            if (file_exists($example)) {
                $path = $example;
            } else {
                return;
            }
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (str_contains($line, '=')) {
                [$k, $v] = explode('=', $line, 2);
                $key = trim($k);
                $val = trim(trim($v), '"\'');
                self::$variables[$key] = $val;
                if (!array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $val;
                }
            }
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (self::$variables === null) {
            self::load(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env');
        }

        if (isset(self::$variables[$key])) {
            $val = self::$variables[$key];
            if ($val === 'true' || $val === '(true)') return true;
            if ($val === 'false' || $val === '(false)') return false;
            if ($val === 'null' || $val === '(null)') return null;
            return $val;
        }

        $envVal = getenv($key);
        if ($envVal !== false) {
            return $envVal;
        }

        return $default;
    }
}
