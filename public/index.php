<?php

declare(strict_types=1);

/**
 * Ponto de Entrada Público
 * Plataforma de Desenvolvimento da Família De Cesaro — Legado e Futuro
 */

require_once dirname(__DIR__) . '/config/bootstrap.php';
require_once dirname(__DIR__) . '/routes/web.php';

\App\Helpers\Router::dispatch();
