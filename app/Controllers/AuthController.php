<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Audit;
use App\Helpers\Auth;
use App\Helpers\Format;
use App\Helpers\Response;
use App\Helpers\Security;
use App\Services\MailService;
use Config\Database;
use PDO;

class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            Response::redirect('/inicio');
        }

        $savedEmail = $_COOKIE['decesaro_saved_email'] ?? '';

        Response::view('auth/login', [
            'pageTitle' => 'Acesso à Plataforma',
            'error' => null,
            'success' => null,
            'oldEmail' => $savedEmail,
            'remember' => !empty($savedEmail)
        ], 'layouts/auth');
    }

    public function login(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!Security::validateCsrf($token)) {
            Response::abort(403, 'Sessão expirada ou token CSRF inválido.');
        }

        $email = trim($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $remember = !empty($_POST['remember']);
        $ip = Security::getClientIp();

        if (empty($email) || empty($password)) {
            Response::view('auth/login', [
                'pageTitle' => 'Acesso à Plataforma',
                'error' => 'Por favor, informe seu e-mail e senha.',
                'oldEmail' => $email,
                'remember' => $remember
            ], 'layouts/auth');
            return;
        }

        // Verificar proteção de força bruta / bloqueio
        if (Auth::isLockedOut($email, $ip)) {
            Response::view('auth/login', [
                'pageTitle' => 'Acesso Bloqueado',
                'error' => 'Muitas tentativas incorretas. Seu acesso está temporariamente bloqueado por 15 minutos.',
                'oldEmail' => $email,
                'remember' => $remember
            ], 'layouts/auth');
            return;
        }

        $pdo = Database::getConnection();
        $table = Database::table('usuario');
        $stmt = $pdo->prepare("SELECT id, nome, email, senha, data_nascimento, tipo, familia, geracao, foto, status FROM `{$table}` WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !Security::verifyPassword($password, $user['senha'])) {
            Auth::recordLoginAttempt($email, $ip, false);
            Response::view('auth/login', [
                'pageTitle' => 'Acesso à Plataforma',
                'error' => 'E-mail ou senha incorretos.',
                'oldEmail' => $email,
                'remember' => $remember
            ], 'layouts/auth');
            return;
        }

        if ($user['status'] !== 'Ativo') {
            Response::view('auth/login', [
                'pageTitle' => 'Acesso Desativado',
                'error' => 'Este usuário está inativo no sistema. Contate o comitê.',
                'oldEmail' => $email,
                'remember' => $remember
            ], 'layouts/auth');
            return;
        }

        // Sucesso: registrar e autenticar com suporte a "Lembrar de mim"
        Auth::recordLoginAttempt($email, $ip, true);
        Auth::clearLoginAttempts($email, $ip);
        Auth::login($user, $remember);

        // Salvar e-mail lembrado para conveniência
        if ($remember) {
            setcookie('decesaro_saved_email', $email, [
                'expires' => time() + (86400 * 30),
                'path' => '/',
                'domain' => '',
                'secure' => Security::isHttps(),
                'httponly' => false,
                'samesite' => 'Lax'
            ]);
        } else {
            setcookie('decesaro_saved_email', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'domain' => '',
                'secure' => Security::isHttps(),
                'httponly' => false,
                'samesite' => 'Lax'
            ]);
        }

        // Carregar preferência de tema do usuário
        try {
            $prefTable = Database::table('preferencia_usuario');
            $stmtPref = $pdo->prepare("SELECT tema FROM `{$prefTable}` WHERE id_usuario = ? LIMIT 1");
            $stmtPref->execute([(int)$user['id']]);
            $savedTheme = $stmtPref->fetchColumn();
            if ($savedTheme && in_array($savedTheme, ['claro', 'escuro'])) {
                setcookie('decesaro_theme', $savedTheme, time() + 31536000, '/', '', false, false);
                $_SESSION['user_tema'] = $savedTheme;
            }
        } catch (\Throwable $e) {}

        Audit::log('LOGIN', 'usuario', (int) $user['id'], null, ['email' => $email]);

        Response::redirect('/inicio');
    }

    public function logout(): void
    {
        $userId = Auth::id();
        if ($userId) {
            Audit::log('LOGOUT', 'usuario', $userId);
        }

        Auth::logout();
        Response::redirect('/login');
    }

    public function showForgot(): void
    {
        Response::view('auth/forgot', [
            'pageTitle' => 'Recuperar Senha',
            'error' => null,
            'success' => null
        ], 'layouts/auth');
    }

    public function sendResetLink(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!Security::validateCsrf($token)) {
            Response::abort(403, 'Token CSRF inválido.');
        }

        $email = trim($_POST['email'] ?? '');
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::view('auth/forgot', [
                'pageTitle' => 'Recuperar Senha',
                'error' => 'Informe um e-mail válido.',
                'success' => null
            ], 'layouts/auth');
            return;
        }

        $pdo = Database::getConnection();
        $userTable = Database::table('usuario');
        $stmt = $pdo->prepare("SELECT id, nome FROM `{$userTable}` WHERE email = ? AND status = 'Ativo' LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Resposta genérica para evitar enumeração de usuários
        $genericMessage = 'Se o e-mail informado estiver cadastrado em nossa base, você receberá instruções para redefinir sua senha em instantes.';

        if ($user) {
            $resetToken = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $resetToken);
            $expiraEm = date('Y-m-d H:i:s', time() + 3600); // 1 hora de validade

            $resetTable = Database::table('recuperacao_senha');
            $stmtInsert = $pdo->prepare("INSERT INTO `{$resetTable}` (email, token_hash, expira_em) VALUES (?, ?, ?)");
            $stmtInsert->execute([$email, $tokenHash, $expiraEm]);

            Audit::log('SOLICITOU_RECUPERACAO', 'usuario', (int)$user['id'], null, ['email' => $email]);

            // Enviar e-mail com o link seguro
            $resetUrl = \Config\App::url("/redefinir-senha?token={$resetToken}&email=" . urlencode($email));
            MailService::sendPasswordReset($email, $user['nome'], $resetUrl);
        }

        Response::view('auth/forgot', [
            'pageTitle' => 'Recuperar Senha',
            'error' => null,
            'success' => $genericMessage
        ], 'layouts/auth');
    }

    public function showReset(): void
    {
        $token = $_GET['token'] ?? '';
        $email = $_GET['email'] ?? '';

        if (empty($token) || empty($email)) {
            Response::redirect('/login');
        }

        Response::view('auth/reset', [
            'pageTitle' => 'Nova Senha',
            'token' => $token,
            'email' => $email,
            'error' => null,
            'success' => null
        ], 'layouts/auth');
    }

    public function resetPassword(): void
    {
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Security::validateCsrf($csrfToken)) {
            Response::abort(403, 'Token CSRF inválido.');
        }

        $token = $_POST['token'] ?? '';
        $email = trim($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

        if (empty($token) || empty($email) || empty($password)) {
            Response::view('auth/reset', [
                'pageTitle' => 'Nova Senha',
                'token' => $token,
                'email' => $email,
                'error' => 'Preencha todos os campos obrigatórios.'
            ], 'layouts/auth');
            return;
        }

        if ($password !== $passwordConfirm) {
            Response::view('auth/reset', [
                'pageTitle' => 'Nova Senha',
                'token' => $token,
                'email' => $email,
                'error' => 'A confirmação de senha não confere.'
            ], 'layouts/auth');
            return;
        }

        if (!Security::isStrongPassword($password)) {
            Response::view('auth/reset', [
                'pageTitle' => 'Nova Senha',
                'token' => $token,
                'email' => $email,
                'error' => 'A senha deve conter ao menos 8 caracteres, incluindo uma letra maiúscula, um número e um caractere especial.'
            ], 'layouts/auth');
            return;
        }

        $pdo = Database::getConnection();
        $tokenHash = hash('sha256', $token);
        $resetTable = Database::table('recuperacao_senha');

        $stmt = $pdo->prepare("SELECT id FROM `{$resetTable}` WHERE email = ? AND token_hash = ? AND utilizado = 'N' AND expira_em > NOW() ORDER BY id DESC LIMIT 1");
        $stmt->execute([$email, $tokenHash]);
        $resetRecord = $stmt->fetch();

        if (!$resetRecord) {
            Response::view('auth/reset', [
                'pageTitle' => 'Nova Senha',
                'token' => $token,
                'email' => $email,
                'error' => 'Este link de recuperação é inválido, expirou ou já foi utilizado.'
            ], 'layouts/auth');
            return;
        }

        // Atualizar senha do usuário
        $userTable = Database::table('usuario');
        $newHash = Security::hashPassword($password);
        $stmtUpdate = $pdo->prepare("UPDATE `{$userTable}` SET senha = ?, atualizado_em = NOW() WHERE email = ?");
        $stmtUpdate->execute([$newHash, $email]);

        // Marcar token como utilizado
        $stmtUsed = $pdo->prepare("UPDATE `{$resetTable}` SET utilizado = 'S' WHERE id = ?");
        $stmtUsed->execute([(int)$resetRecord['id']]);

        Audit::log('REDEFINIU_SENHA', 'usuario', null, null, ['email' => $email]);

        Response::view('auth/login', [
            'pageTitle' => 'Senha Redefinida',
            'success' => 'Sua senha foi redefinida com sucesso! Você já pode efetuar login com sua nova credencial.',
            'error' => null,
            'oldEmail' => $email
        ], 'layouts/auth');
    }
}
