<?php

declare(strict_types=1);

// Definir diretório raiz do projeto
defined('BASE_PATH') or define('BASE_PATH', dirname(__DIR__));

// Autoloader PSR-4 para App e Config
spl_autoload_register(function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        $relative = substr($class, 4);
        $file = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    } elseif (str_starts_with($class, 'Config\\')) {
        $relative = substr($class, 7);
        $map = [
            'App' => 'app',
            'Database' => 'database',
            'MailConfig' => 'mail',
            'SecurityConfig' => 'security',
        ];
        $filename = $map[$relative] ?? strtolower($relative);
        $file = BASE_PATH . '/config/' . $filename . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
        $fileDirect = BASE_PATH . '/config/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($fileDirect)) {
            require_once $fileDirect;
            return;
        }
    }
});

// Carregar variáveis de ambiente
\App\Helpers\Env::load(BASE_PATH . '/.env');

// Configurar fuso horário
date_default_timezone_set(\Config\App::timezone());

// Inicializar sessão segura
\App\Helpers\Security::initSession();
