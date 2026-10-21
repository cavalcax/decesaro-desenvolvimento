<?php

declare(strict_types=1);

namespace App\Helpers;

use Config\Database;
use Config\SecurityConfig;
use DateTime;
use PDO;

class Auth
{
    private static ?array $currentUser = null;

    public static function check(): bool
    {
        Security::initSession();
        return !empty($_SESSION['user_id']);
    }

    public static function id(): ?int
    {
        Security::initSession();
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        if (self::$currentUser !== null) {
            return self::$currentUser;
        }

        $userId = self::id();
        $pdo = Database::getConnection();
        $table = Database::table('usuario');
        $stmt = $pdo->prepare("SELECT id, nome, email, data_nascimento, tipo, familia, geracao, foto, status, criado_em FROM `{$table}` WHERE id = ? AND status = 'Ativo' LIMIT 1");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            self::logout();
            return null;
        }

        // Adicionar idade calculada e faixa etária
        $user['idade'] = self::calculateAge($user['data_nascimento']);
        $user['faixa_etaria'] = self::formatAgeBracket($user['idade']);

        self::$currentUser = $user;
        return self::$currentUser;
    }

    public static function isAdmin(): bool
    {
        $user = self::user();
        return $user !== null && $user['tipo'] === 'Admin';
    }

    public static function requireAuth(): void
    {
        if (!self::check()) {
            if (self::isAjax()) {
                Response::json(['success' => false, 'message' => 'Sessão expirada. Por favor, faça login novamente.'], 401);
            }
            Response::redirect('/login');
        }
    }

    public static function requireAdmin(): void
    {
        self::requireAuth();
        if (!self::isAdmin()) {
            if (self::isAjax()) {
                Response::json(['success' => false, 'message' => 'Acesso negado. Requer perfil administrativo.'], 403);
            }
            Response::abort(403, 'Acesso restrito ao Comitê de Desenvolvimento.');
        }
    }

    public static function login(array $user): void
    {
        Security::initSession();
        session_regenerate_id(true);

        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_nome'] = $user['nome'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_tipo'] = $user['tipo'];
        $_SESSION['logged_at'] = time();

        self::$currentUser = null;

        // Registrar log de acesso
        try {
            $pdo = Database::getConnection();
            $logTable = Database::table('log_acesso');
            $stmt = $pdo->prepare("INSERT INTO `{$logTable}` (id_usuario, ip, user_agent, data_hora) VALUES (?, ?, ?, NOW())");
            $stmt->execute([
                $user['id'],
                Security::getClientIp(),
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)
            ]);
        } catch (\Throwable $e) {
            error_log("Erro ao registrar log de acesso: " . $e->getMessage());
        }
    }

    public static function logout(): void
    {
        Security::initSession();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
        self::$currentUser = null;
    }

    public static function calculateAge(string $birthDate): int
    {
        try {
            $b = new DateTime($birthDate);
            $now = new DateTime();
            return $b->diff($now)->y;
        } catch (\Throwable) {
            return 0;
        }
    }

    public static function formatAgeBracket(int $age): string
    {
        if ($age <= 15) return "0 a 15 anos";
        if ($age <= 20) return "16 a 20 anos";
        if ($age <= 35) return "21 a 35 anos";
        return "36+ anos";
    }

    public static function getUserLevels(int $age): array
    {
        $pdo = Database::getConnection();
        $table = Database::table('nivel');
        $stmt = $pdo->prepare("SELECT id, descricao, idade_inicial, idade_final FROM `{$table}` WHERE ? >= idade_inicial AND ? <= idade_final ORDER BY idade_inicial ASC");
        $stmt->execute([$age, $age]);
        return $stmt->fetchAll();
    }

    public static function canAccessContent(int $userId, int $contentId): bool
    {
        $pdo = Database::getConnection();
        $uTable = Database::table('usuario');
        $stmtU = $pdo->prepare("SELECT id, data_nascimento, tipo FROM `{$uTable}` WHERE id = ? LIMIT 1");
        $stmtU->execute([$userId]);
        $targetUser = $stmtU->fetch();

        if (!$targetUser) {
            return false;
        }

        // Administrador tem acesso irrestrito
        if ($targetUser['tipo'] === 'Admin') {
            return true;
        }

        $age = self::calculateAge($targetUser['data_nascimento']);
        $rcnTable = Database::table('raia_conteudo_nivel');
        $nivelTable = Database::table('nivel');

        // Verificar se existe ligação entre o conteúdo e algum nível compatível com a idade do participante
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM `{$rcnTable}` rcn
            JOIN `{$nivelTable}` n ON n.id = rcn.id_nivel
            WHERE rcn.id_raia_conteudo = ?
              AND ? >= n.idade_inicial 
              AND ? <= n.idade_final
        ");
        $stmt->execute([$contentId, $age, $age]);
        return ((int)$stmt->fetchColumn()) > 0;
    }

    public static function canAccessVideo(int $userId, int $videoId): bool
    {
        $pdo = Database::getConnection();
        $uTable = Database::table('usuario');
        $stmtU = $pdo->prepare("SELECT id, tipo FROM `{$uTable}` WHERE id = ? LIMIT 1");
        $stmtU->execute([$userId]);
        $targetUser = $stmtU->fetch();

        if ($targetUser && $targetUser['tipo'] === 'Admin') {
            return true;
        }

        $videoTable = Database::table('video');
        $stmt = $pdo->prepare("SELECT id, id_raia_conteudo, exclusivo FROM `{$videoTable}` WHERE id = ? LIMIT 1");
        $stmt->execute([$videoId]);
        $video = $stmt->fetch();

        if (!$video) {
            return false;
        }

        // Se o vídeo não for exclusivo, qualquer usuário autenticado pode assistir
        if ($video['exclusivo'] === 'Não') {
            return true;
        }

        // Se for exclusivo, verificar elegibilidade pelo conteúdo
        return self::canAccessContent($userId, (int)$video['id_raia_conteudo']);
    }

    public static function isLockedOut(string $email, string $ip): bool
    {
        $pdo = Database::getConnection();
        $table = Database::table('bloqueio_temporario');

        // Remover bloqueios expirados
        $pdo->prepare("DELETE FROM `{$table}` WHERE bloqueado_ate < NOW()")->execute();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `{$table}` WHERE (email = ? OR ip = ?) AND bloqueado_ate >= NOW()");
        $stmt->execute([$email, $ip]);
        return ((int)$stmt->fetchColumn()) > 0;
    }

    public static function recordLoginAttempt(string $email, string $ip, bool $success): void
    {
        $pdo = Database::getConnection();
        $tTable = Database::table('tentativa_login');
        $stmt = $pdo->prepare("INSERT INTO `{$tTable}` (ip, email, data_hora, sucesso) VALUES (?, ?, NOW(), ?)");
        $stmt->execute([$ip, $email, $success ? 'S' : 'N']);

        if (!$success) {
            $max = SecurityConfig::maxLoginAttempts();
            $lockoutMinutes = SecurityConfig::lockoutMinutes();

            // Contar falhas recentes nos últimos lockoutMinutes
            $stmtCount = $pdo->prepare("
                SELECT COUNT(*) FROM `{$tTable}` 
                WHERE (email = ? OR ip = ?) 
                  AND sucesso = 'N' 
                  AND data_hora >= DATE_SUB(NOW(), INTERVAL ? MINUTE)
            ");
            $stmtCount->execute([$email, $ip, $lockoutMinutes]);
            $failures = (int)$stmtCount->fetchColumn();

            if ($failures >= $max) {
                $bTable = Database::table('bloqueio_temporario');
                $stmtLock = $pdo->prepare("
                    INSERT INTO `{$bTable}` (ip, email, bloqueado_ate) 
                    VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? MINUTE))
                ");
                $stmtLock->execute([$ip, $email, $lockoutMinutes]);
            }
        }
    }

    public static function clearLoginAttempts(string $email, string $ip): void
    {
        try {
            $pdo = Database::getConnection();
            $tTable = Database::table('tentativa_login');
            $stmt = $pdo->prepare("DELETE FROM `{$tTable}` WHERE email = ? OR ip = ?");
            $stmt->execute([$email, $ip]);
        } catch (\Throwable) {}
    }

    private static function isAjax(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
               (!empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));
    }
}
