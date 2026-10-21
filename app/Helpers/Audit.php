<?php

declare(strict_types=1);

namespace App\Helpers;

use Config\Database;

class Audit
{
    public static function log(
        string $action,
        string $entity,
        ?int $entityId = null,
        ?array $oldData = null,
        ?array $newData = null
    ): void {
        try {
            $userId = Auth::id();
            $ip = Security::getClientIp();

            // Filtrar campos sensíveis
            $sanitizedOld = self::sanitizeData($oldData);
            $sanitizedNew = self::sanitizeData($newData);

            $pdo = Database::getConnection();
            $table = Database::table('auditoria_log');
            $stmt = $pdo->prepare("
                INSERT INTO `{$table}` (id_usuario, acao, entidade, entidade_id, dados_anteriores, dados_novos, ip, data_hora)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $userId,
                $action,
                $entity,
                $entityId,
                $sanitizedOld !== null ? json_encode($sanitizedOld, JSON_UNESCAPED_UNICODE) : null,
                $sanitizedNew !== null ? json_encode($sanitizedNew, JSON_UNESCAPED_UNICODE) : null,
                $ip
            ]);
        } catch (\Throwable $e) {
            error_log("Falha ao registrar auditoria: " . $e->getMessage());
        }
    }

    private static function sanitizeData(?array $data): ?array
    {
        if ($data === null) {
            return null;
        }

        $sensitive = ['senha', 'password', 'password_hash', 'token', 'token_hash', 'csrf_token'];
        foreach ($sensitive as $key) {
            if (isset($data[$key])) {
                $data[$key] = '[REDACTED]';
            }
        }
        return $data;
    }
}
