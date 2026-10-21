<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Audit;
use App\Helpers\Auth;
use App\Helpers\Response;
use App\Helpers\Security;
use Config\Database;
use PDO;

class PerfilController
{
    public function index(): void
    {
        Auth::requireAuth();
        $user = Auth::user();
        $userId = (int)$user['id'];
        $age = (int)$user['idade'];

        $pdo = Database::getConnection();
        $raiaTable = Database::table('raia');
        $conteudoTable = Database::table('raia_conteudo');
        $rcnTable = Database::table('raia_conteudo_nivel');
        $nivelTable = Database::table('nivel');
        $prefTable = Database::table('preferencia_usuario');

        // Buscar preferência do usuário
        $stmtPref = $pdo->prepare("SELECT tema, notificacoes_email FROM `{$prefTable}` WHERE id_usuario = ? LIMIT 1");
        $stmtPref->execute([$userId]);
        $pref = $stmtPref->fetch() ?: ['tema' => 'claro', 'notificacoes_email' => 'S'];

        // Checar elegibilidade de cada uma das 4 raias
        $stmtR = $pdo->query("SELECT id, descricao, slug FROM `{$raiaTable}` ORDER BY ordem ASC");
        $raias = $stmtR->fetchAll();

        $elegibilidadeRaias = [];
        foreach ($raias as $r) {
            $raiaId = (int)$r['id'];

            $stmtCheck = $pdo->prepare("
                SELECT COUNT(*) 
                FROM `{$conteudoTable}` c
                JOIN `{$rcnTable}` rcn ON rcn.id_raia_conteudo = c.id
                JOIN `{$nivelTable}` n ON n.id = rcn.id_nivel
                WHERE c.id_raia = ? AND c.ativo = 'S'
                  AND ? >= n.idade_inicial AND ? <= n.idade_final
            ");
            $stmtCheck->execute([$raiaId, $age, $age]);
            $isDisponivel = ((int)$stmtCheck->fetchColumn() > 0) || Auth::isAdmin();

            $elegibilidadeRaias[] = [
                'id' => $raiaId,
                'descricao' => $r['descricao'],
                'slug' => $r['slug'],
                'disponivel' => $isDisponivel
            ];
        }

        Response::view('perfil/index', [
            'pageTitle' => 'Meu Perfil — Informações Pessoais',
            'user' => $user,
            'preferencias' => $pref,
            'elegibilidadeRaias' => $elegibilidadeRaias
        ]);
    }

    public function update(): void
    {
        Auth::requireAuth();
        $user = Auth::user();
        $userId = (int)$user['id'];

        $token = $_POST['csrf_token'] ?? '';
        if (!Security::validateCsrf($token)) {
            Response::abort(403, 'Token CSRF inválido.');
        }

        $nome = trim($_POST['nome'] ?? '');
        if (empty($nome)) {
            Response::redirect('/perfil?error=O nome não pode ficar em branco.');
        }

        // Atualizar apenas dados permitidos (NÃO permite alterar tipo, família ou geração)
        $pdo = Database::getConnection();
        $table = Database::table('usuario');
        $stmt = $pdo->prepare("UPDATE `{$table}` SET nome = ?, atualizado_em = NOW() WHERE id = ?");
        $stmt->execute([$nome, $userId]);

        Audit::log('ATUALIZOU_PERFIL', 'usuario', $userId, ['nome' => $user['nome']], ['nome' => $nome]);

        Response::redirect('/perfil?success=Dados atualizados com sucesso!');
    }

    public function changePassword(): void
    {
        Auth::requireAuth();
        $user = Auth::user();
        $userId = (int)$user['id'];

        $token = $_POST['csrf_token'] ?? '';
        if (!Security::validateCsrf($token)) {
            Response::abort(403, 'Token CSRF inválido.');
        }

        $currentPassword = (string)($_POST['current_password'] ?? '');
        $newPassword = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        $pdo = Database::getConnection();
        $table = Database::table('usuario');
        $stmt = $pdo->prepare("SELECT senha FROM `{$table}` WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        if (!Security::verifyPassword($currentPassword, $row['senha'])) {
            Response::redirect('/perfil?error=A senha atual informada está incorreta.');
        }

        if ($newPassword !== $confirmPassword) {
            Response::redirect('/perfil?error=A confirmação da nova senha não confere.');
        }

        $valid = Security::validatePasswordStrength($newPassword);
        if (!$valid['valid']) {
            Response::redirect('/perfil?error=' . urlencode($valid['message']));
        }

        $hash = Security::hashPassword($newPassword);
        $stmtUp = $pdo->prepare("UPDATE `{$table}` SET senha = ?, atualizado_em = NOW() WHERE id = ?");
        $stmtUp->execute([$hash, $userId]);

        Audit::log('ALTEROU_SENHA', 'usuario', $userId);

        Response::redirect('/perfil?success=Senha alterada com sucesso!');
    }

    public function savePreferences(): void
    {
        Auth::requireAuth();
        $user = Auth::user();
        $userId = (int)$user['id'];

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $tema = in_array($input['tema'] ?? '', ['claro', 'escuro']) ? $input['tema'] : 'claro';
        $notif = ($input['notificacoes_email'] ?? '') === 'N' ? 'N' : 'S';

        $pdo = Database::getConnection();
        $table = Database::table('preferencia_usuario');
        $stmt = $pdo->prepare("
            INSERT INTO `{$table}` (id_usuario, tema, notificacoes_email, atualizado_em)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE tema = VALUES(tema), notificacoes_email = VALUES(notificacoes_email), atualizado_em = NOW()
        ");
        $stmt->execute([$userId, $tema, $notif]);

        Response::json(['success' => true, 'message' => 'Preferências salvas com sucesso.']);
    }
}
