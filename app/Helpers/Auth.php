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

    public static function resetUserState(): void
    {
        self::$currentUser = null;
    }

    public static function check(): bool
    {
        Security::initSession();
        if (!empty($_SESSION['user_id'])) {
            return true;
        }

        return self::tryAutoLoginFromToken();
    }

    public static function id(): ?int
    {
        if (self::check()) {
            return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        }
        return null;
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

    public static function login(array $user, bool $remember = false): void
    {
        Security::initSession();
        if (!headers_sent() && session_status() === PHP_SESSION_ACTIVE) {
            @session_regenerate_id(true);
        }

        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_nome'] = $user['nome'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_tipo'] = $user['tipo'];
        $_SESSION['logged_at'] = time();

        self::$currentUser = null;

        // Tratar persistência "Lembrar de mim"
        if ($remember) {
            self::issueRememberToken((int)$user['id']);
        } else {
            self::revokeCurrentRememberToken();
        }

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

        // Revogar token de persistência se existir
        self::revokeCurrentRememberToken();

        $_SESSION = [];
        if (!headers_sent() && ini_get("session.use_cookies")) {
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
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_destroy();
        }
        self::$currentUser = null;
    }

    public static function issueRememberToken(int $userId): void
    {
        try {
            $selector = bin2hex(random_bytes(16)); // 32 caracteres hex
            $validator = bin2hex(random_bytes(32)); // 64 caracteres hex
            $tokenHash = hash('sha256', $validator);
            $expiraEm = date('Y-m-d H:i:s', time() + (86400 * 30)); // 30 dias

            $pdo = Database::getConnection();
            $table = Database::table('lembrar_token');
            $stmt = $pdo->prepare("INSERT INTO `{$table}` (id_usuario, selector, token_hash, expira_em, criado_em, ultimo_uso) VALUES (?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$userId, $selector, $tokenHash, $expiraEm]);

            $cookieValue = "{$selector}:{$validator}";
            if (!headers_sent()) {
                setcookie('decesaro_remember', $cookieValue, [
                    'expires' => time() + (86400 * 30),
                    'path' => '/',
                    'domain' => '',
                    'secure' => Security::isHttps(),
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            }
            $_COOKIE['decesaro_remember'] = $cookieValue;
        } catch (\Throwable $e) {
            error_log("Erro ao emitir token de persistência: " . $e->getMessage());
        }
    }

    public static function tryAutoLoginFromToken(): bool
    {
        $cookie = $_COOKIE['decesaro_remember'] ?? '';
        if (empty($cookie) || !str_contains($cookie, ':')) {
            return false;
        }

        [$selector, $validator] = explode(':', $cookie, 2);
        if (empty($selector) || empty($validator) || strlen($selector) !== 32 || strlen($validator) !== 64) {
            self::clearRememberCookie();
            return false;
        }

        try {
            $pdo = Database::getConnection();
            $tokenTable = Database::table('lembrar_token');
            $stmt = $pdo->prepare("SELECT id, id_usuario, token_hash, expira_em FROM `{$tokenTable}` WHERE selector = ? AND expira_em > NOW() LIMIT 1");
            $stmt->execute([$selector]);
            $record = $stmt->fetch();

            if (!$record) {
                self::clearRememberCookie();
                return false;
            }

            $computedHash = hash('sha256', $validator);
            if (!hash_equals($record['token_hash'], $computedHash)) {
                $stmtDel = $pdo->prepare("DELETE FROM `{$tokenTable}` WHERE id = ?");
                $stmtDel->execute([(int)$record['id']]);
                self::clearRememberCookie();
                return false;
            }

            $userTable = Database::table('usuario');
            $stmtUser = $pdo->prepare("SELECT id, nome, email, data_nascimento, tipo, familia, geracao, foto, status, criado_em FROM `{$userTable}` WHERE id = ? AND status = 'Ativo' LIMIT 1");
            $stmtUser->execute([(int)$record['id_usuario']]);
            $user = $stmtUser->fetch();

            if (!$user) {
                $stmtDel = $pdo->prepare("DELETE FROM `{$tokenTable}` WHERE id = ?");
                $stmtDel->execute([(int)$record['id']]);
                self::clearRememberCookie();
                return false;
            }

            Security::initSession();
            if (!headers_sent() && session_status() === PHP_SESSION_ACTIVE) {
                @session_regenerate_id(true);
            }

            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['user_nome'] = $user['nome'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_tipo'] = $user['tipo'];
            $_SESSION['logged_at'] = time();

            $user['idade'] = self::calculateAge($user['data_nascimento']);
            $user['faixa_etaria'] = self::formatAgeBracket($user['idade']);
            self::$currentUser = $user;

            $newExpires = date('Y-m-d H:i:s', time() + (86400 * 30));
            $stmtUp = $pdo->prepare("UPDATE `{$tokenTable}` SET ultimo_uso = NOW(), expira_em = ? WHERE id = ?");
            $stmtUp->execute([$newExpires, (int)$record['id']]);

            if (!headers_sent()) {
                setcookie('decesaro_remember', $cookie, [
                    'expires' => time() + (86400 * 30),
                    'path' => '/',
                    'domain' => '',
                    'secure' => Security::isHttps(),
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            }
            $_COOKIE['decesaro_remember'] = $cookie;

            return true;

        } catch (\Throwable $e) {
            error_log("Erro no auto-login via remember token: " . $e->getMessage());
            return false;
        }
    }

    public static function revokeCurrentRememberToken(): void
    {
        $cookie = $_COOKIE['decesaro_remember'] ?? '';
        if (!empty($cookie) && str_contains($cookie, ':')) {
            [$selector] = explode(':', $cookie, 2);
            if (!empty($selector)) {
                try {
                    $pdo = Database::getConnection();
                    $tokenTable = Database::table('lembrar_token');
                    $stmt = $pdo->prepare("DELETE FROM `{$tokenTable}` WHERE selector = ?");
                    $stmt->execute([$selector]);
                } catch (\Throwable) {}
            }
        }
        self::clearRememberCookie();
    }

    public static function clearRememberCookie(): void
    {
        if (isset($_COOKIE['decesaro_remember'])) {
            if (!headers_sent()) {
                setcookie('decesaro_remember', '', [
                    'expires' => time() - 42000,
                    'path' => '/',
                    'domain' => '',
                    'secure' => Security::isHttps(),
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            }
            unset($_COOKIE['decesaro_remember']);
        }
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
        if ($age <= 10) return "Até 10 anos";
        if ($age <= 15) return "11 a 15 anos";
        if ($age <= 20) return "16 a 20 anos";
        return "Acima de 21 anos";
    }

    public static function getUserLevels(?int $age = null): array
    {
        $pdo = Database::getConnection();
        $table = Database::table('nivel');
        $stmt = $pdo->query("SELECT id, descricao FROM `{$table}` ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    public static function canAccessRaia(int $userId, int $raiaId): bool
    {
        $pdo = Database::getConnection();
        $uTable = Database::table('usuario');
        $stmtU = $pdo->prepare("SELECT id, data_nascimento, tipo FROM `{$uTable}` WHERE id = ? LIMIT 1");
        $stmtU->execute([$userId]);
        $targetUser = $stmtU->fetch();

        if (!$targetUser) {
            return false;
        }

        // Administrador tem acesso irrestrito a todas as raias
        if ($targetUser['tipo'] === 'Admin') {
            return true;
        }

        $rTable = Database::table('raia');
        $stmtR = $pdo->prepare("SELECT id, idade_minima, idade_maxima FROM `{$rTable}` WHERE id = ? LIMIT 1");
        $stmtR->execute([$raiaId]);
        $raia = $stmtR->fetch();

        if (!$raia) {
            return false;
        }

        $age = self::calculateAge($targetUser['data_nascimento']);
        $idadeMin = (int)($raia['idade_minima'] ?? 0);
        $idadeMax = $raia['idade_maxima'] !== null ? (int)$raia['idade_maxima'] : null;

        if ($age < $idadeMin) {
            return false;
        }
        if ($idadeMax !== null && $age > $idadeMax) {
            return false;
        }

        return true;
    }

    public static function canAccessContent(int $userId, int $contentId): bool
    {
        $pdo = Database::getConnection();
        $uTable = Database::table('usuario');
        $stmtU = $pdo->prepare("SELECT id, tipo FROM `{$uTable}` WHERE id = ? LIMIT 1");
        $stmtU->execute([$userId]);
        $targetUser = $stmtU->fetch();

        if (!$targetUser) {
            return false;
        }

        // Administrador tem acesso irrestrito
        if ($targetUser['tipo'] === 'Admin') {
            return true;
        }

        $cTable = Database::table('raia_conteudo');
        $stmtC = $pdo->prepare("SELECT id, id_raia FROM `{$cTable}` WHERE id = ? LIMIT 1");
        $stmtC->execute([$contentId]);
        $content = $stmtC->fetch();

        if (!$content) {
            return false;
        }

        // Elegibilidade de conteúdo vinculada diretamente à elegibilidade da Raia
        return self::canAccessRaia($userId, (int)$content['id_raia']);
    }

    public static function canAccessVideo(int $userId, int $videoId): bool
    {
        $pdo = Database::getConnection();
        $uTable = Database::table('usuario');
        $stmtU = $pdo->prepare("SELECT id, data_nascimento, tipo FROM `{$uTable}` WHERE id = ? LIMIT 1");
        $stmtU->execute([$userId]);
        $targetUser = $stmtU->fetch();

        if (!$targetUser) {
            return false;
        }

        if ($targetUser['tipo'] === 'Admin') {
            return true;
        }

        $videoTable = Database::table('video');
        $stmt = $pdo->prepare("SELECT id, id_raia_conteudo, exclusivo, idade_minima, idade_maxima FROM `{$videoTable}` WHERE id = ? LIMIT 1");
        $stmt->execute([$videoId]);
        $video = $stmt->fetch();

        if (!$video) {
            return false;
        }

        // Valida se o usuário tem permissão para a raia deste conteúdo
        if (!self::canAccessContent($userId, (int)$video['id_raia_conteudo'])) {
            return false;
        }

        // Se o vídeo não for exclusivo, qualquer participante elegível na raia pode assistir
        if ($video['exclusivo'] === 'Não') {
            return true;
        }

        // Se for exclusivo, valida o intervalo de idade configurado diretamente no vídeo
        $age = self::calculateAge($targetUser['data_nascimento']);
        $idadeMin = (int)($video['idade_minima'] ?? 0);
        $idadeMax = $video['idade_maxima'] !== null ? (int)$video['idade_maxima'] : null;

        if ($age < $idadeMin) {
            return false;
        }
        if ($idadeMax !== null && $age > $idadeMax) {
            return false;
        }

        return true;
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
