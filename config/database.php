<?php

declare(strict_types=1);

namespace Config;

use App\Helpers\Env;
use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $instance = null;
    private static ?string $prefix = null;

    public static function getConnection(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $host = (string)Env::get('DB_HOST', '127.0.0.1');
        $port = (int)Env::get('DB_PORT', 3306);
        $dbname = (string)Env::get('DB_NAME', 'ctdecesaro');
        $username = (string)Env::get('DB_USER', 'root');
        $password = (string)Env::get('DB_PASSWORD', '');

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        try {
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]);
            self::$instance = $pdo;
            return self::$instance;
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new RuntimeException("Falha na conexão com o banco de dados. Verifique a configuração.");
        }
    }

    public static function getPrefix(): string
    {
        if (self::$prefix === null) {
            self::$prefix = (string)Env::get('DB_PREFIX', 'ctdesenvolvimento_decesaro_');
            // Validar prefixo para segurança (somente letras, números e underscore)
            if (!preg_match('/^[a-zA-Z0-9_]+$/', self::$prefix)) {
                throw new RuntimeException("Prefixo de tabela inválido.");
            }
        }
        return self::$prefix;
    }

    public static function table(string $name): string
    {
        // Validar nome da tabela para impedir injeção SQL
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
            throw new RuntimeException("Nome de tabela inválido: {$name}");
        }
        return self::getPrefix() . $name;
    }
}
