<?php
/**
 * Script de Migração e Seeds
 * Plataforma de Desenvolvimento da Família De Cesaro
 */

declare(strict_types=1);

$baseDir = dirname(__DIR__);

// Carregar variáveis de ambiente simples
$envFile = $baseDir . DIRECTORY_SEPARATOR . '.env';
if (!file_exists($envFile)) {
    $envFile = $baseDir . DIRECTORY_SEPARATOR . '.env.example';
}

$env = [];
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$k, $v] = explode('=', $line, 2);
            $env[trim($k)] = trim(trim($v), '"\'');
        }
    }
}

$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = (int)($env['DB_PORT'] ?? 3306);
$dbName = $env['DB_NAME'] ?? 'ctdecesaro';
$user = $env['DB_USER'] ?? 'root';
$pass = $env['DB_PASSWORD'] ?? '';
$prefix = $env['DB_PREFIX'] ?? 'ctdesenvolvimento_decesaro_';

echo "========================================================\n";
echo "Plataforma de Desenvolvimento da Família De Cesaro\n";
echo "Executando Migração de Banco de Dados...\n";
echo "Host: {$host}:{$port} | Banco: {$dbName} | Prefixo: {$prefix}\n";
echo "========================================================\n\n";

try {
    $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Garantir banco criado
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$dbName}`");
    echo "✓ Banco `{$dbName}` pronto.\n";

    // 1. Executar Schema
    $schemaFile = $baseDir . '/database/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new RuntimeException("Arquivo de schema não encontrado: {$schemaFile}");
    }

    $schemaSql = file_get_contents($schemaFile);
    if ($prefix !== 'ctdesenvolvimento_decesaro_') {
        $schemaSql = str_replace('ctdesenvolvimento_decesaro_', $prefix, $schemaSql);
    }

    // Executar schema completo
    $pdo->exec($schemaSql);
    echo "✓ Schema executado com sucesso.\n";

    // 2. Executar Seeds se solicitado ou por padrão
    $seedFile = $baseDir . '/database/seeds.sql';
    if (file_exists($seedFile)) {
        $seedSql = file_get_contents($seedFile);
        if ($prefix !== 'ctdesenvolvimento_decesaro_') {
            $seedSql = str_replace('ctdesenvolvimento_decesaro_', $prefix, $seedSql);
        }
        $pdo->exec($seedSql);
        echo "✓ Seeds aplicados com sucesso.\n";
    }

    // 3. Exibir resumo das tabelas criadas
    echo "\nTabelas com prefixo '{$prefix}':\n";
    $stmt = $pdo->query("SHOW TABLES LIKE '{$prefix}%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
        echo sprintf("  - %-48s [%d registros]\n", $table, $count);
    }

    echo "\n✓ Migração concluída com êxito!\n";

} catch (Throwable $e) {
    fwrite(STDERR, "ERRO durante a migração: " . $e->getMessage() . "\n");
    exit(1);
}
