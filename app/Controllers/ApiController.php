<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Audit;
use App\Helpers\Auth;
use App\Helpers\Response;
use App\Helpers\Security;
use Config\Database;
use PDO;

class ApiController
{
    public function saveVideoProgress(): void
    {
        Auth::requireAuth();
        $user = Auth::user();
        $userId = (int)$user['id'];

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $videoId = isset($input['video_id']) ? (int)$input['video_id'] : 0;
        $posicao = isset($input['posicao']) ? (int)round((float)$input['posicao']) : 0;
        $tempoAssistido = isset($input['tempo_assistido']) ? (int)round((float)$input['tempo_assistido']) : 0;
        $percentual = isset($input['percentual']) ? (float)$input['percentual'] : 0.0;
        $duracao = isset($input['duracao']) ? (int)$input['duracao'] : 0;

        if ($videoId <= 0) {
            Response::json(['success' => false, 'message' => 'Vídeo inválido.'], 400);
        }

        if (!Auth::canAccessVideo($videoId, $userId)) {
            Response::json(['success' => false, 'message' => 'Acesso não autorizado a este vídeo.'], 403);
        }

        $pdo = Database::getConnection();
        $progTable = Database::table('progresso_video');
        $videoTable = Database::table('video');

        // Se a duração não veio no payload, busca do banco
        if ($duracao <= 0) {
            $stmtDur = $pdo->prepare("SELECT duracao_segundos FROM `{$videoTable}` WHERE id = ?");
            $stmtDur->execute([$videoId]);
            $duracao = (int)$stmtDur->fetchColumn();
        }

        // Calcula percentual se for zero mas temos posição e duração
        if ($percentual <= 0.0 && $duracao > 0 && $posicao > 0) {
            $percentual = min(100.0, round(($posicao / $duracao) * 100, 2));
        }

        $concluidoLimiar = (float)\Config\App::env('VIDEO_COMPLETION_PERCENTAGE', 90.0);

        // Buscar progresso existente
        $stmt = $pdo->prepare("SELECT id, concluido, percentual, tempo_assistido FROM `{$progTable}` WHERE id_usuario = ? AND id_video = ? LIMIT 1");
        $stmt->execute([$userId, $videoId]);
        $existente = $stmt->fetch();

        $jaConcluido = $existente && $existente['concluido'] === 'S';
        $atingiuLimiar = $percentual >= $concluidoLimiar;
        $novoConcluido = ($jaConcluido || $atingiuLimiar) ? 'S' : 'N';
        $tempoTotalAssistido = ($existente ? (int)$existente['tempo_assistido'] : 0) + $tempoAssistido;
        $maiorPercentual = $existente ? max((float)$existente['percentual'], $percentual) : $percentual;

        if ($existente) {
            $sql = "UPDATE `{$progTable}` 
                    SET posicao_atual = ?, 
                        percentual = ?, 
                        tempo_assistido = ?, 
                        data_ultima_atividade = NOW()" .
                    ($novoConcluido === 'S' && !$jaConcluido ? ", concluido = 'S', data_conclusao = NOW(), metodo_conclusao = 'automatico'" : "") .
                    " WHERE id = ?";
            $upStmt = $pdo->prepare($sql);
            $upStmt->execute([$posicao, $maiorPercentual, $tempoTotalAssistido, $existente['id']]);
        } else {
            $sql = "INSERT INTO `{$progTable}` 
                    (id_usuario, id_video, posicao_atual, tempo_assistido, percentual, concluido, data_inicio, data_conclusao, data_ultima_atividade, metodo_conclusao) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), " . 
                    ($novoConcluido === 'S' ? "NOW(), NOW(), 'automatico'" : "NULL, NOW(), NULL") . ")";
            $inStmt = $pdo->prepare($sql);
            $inStmt->execute([$userId, $videoId, $posicao, $tempoTotalAssistido, $maiorPercentual, $novoConcluido]);
        }

        // Registrar na sessão de reprodução
        try {
            $sessTable = Database::table('sessao_reproducao');
            $stmtSess = $pdo->prepare("
                INSERT INTO `{$sessTable}` (id_usuario, id_video, inicio, fim, segundos_assistidos)
                VALUES (?, ?, NOW(), NOW(), ?)
            ");
            $stmtSess->execute([
                $userId,
                $videoId,
                $tempoAssistido > 0 ? $tempoAssistido : 5
            ]);
        } catch (\Throwable $e) {}

        Response::json([
            'success' => true,
            'percentual' => $maiorPercentual,
            'concluido' => $novoConcluido === 'S'
        ]);
    }

    public function progresso(): void
    {
        $this->saveVideoProgress();
    }

    public function markVideoComplete(): void
    {
        Auth::requireAuth();
        $user = Auth::user();
        $userId = (int)$user['id'];

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $videoId = isset($input['video_id']) ? (int)$input['video_id'] : 0;
        $metodo = in_array($input['metodo'] ?? '', ['automatico', 'manual']) ? $input['metodo'] : 'manual';
        $posicao = isset($input['posicao']) ? (int)round((float)$input['posicao']) : 0;
        $tempoAssistido = isset($input['tempo_assistido']) ? (int)round((float)$input['tempo_assistido']) : 0;

        if ($videoId <= 0) {
            Response::json(['success' => false, 'message' => 'Vídeo inválido.'], 400);
        }

        if (!Auth::canAccessVideo($videoId, $userId)) {
            Response::json(['success' => false, 'message' => 'Acesso não autorizado a este vídeo.'], 403);
        }

        $pdo = Database::getConnection();
        $progTable = Database::table('progresso_video');

        // Buscar progresso atual se existir
        $stmtCheck = $pdo->prepare("SELECT id, tempo_assistido FROM `{$progTable}` WHERE id_usuario = ? AND id_video = ? LIMIT 1");
        $stmtCheck->execute([$userId, $videoId]);
        $existente = $stmtCheck->fetch();

        $tempoFinal = ($existente ? (int)$existente['tempo_assistido'] : 0) + $tempoAssistido;

        $stmt = $pdo->prepare("
            INSERT INTO `{$progTable}` 
                (id_usuario, id_video, posicao_atual, tempo_assistido, percentual, concluido, data_inicio, data_conclusao, data_ultima_atividade, metodo_conclusao)
            VALUES (?, ?, ?, ?, 100.0, 'S', NOW(), NOW(), NOW(), ?)
            ON DUPLICATE KEY UPDATE 
                posicao_atual = IF(VALUES(posicao_atual) > 0, VALUES(posicao_atual), posicao_atual),
                tempo_assistido = GREATEST(tempo_assistido, VALUES(tempo_assistido)),
                percentual = 100.0,
                concluido = 'S',
                data_conclusao = IFNULL(data_conclusao, NOW()),
                metodo_conclusao = IFNULL(metodo_conclusao, VALUES(metodo_conclusao)),
                data_ultima_atividade = NOW()
        ");
        $stmt->execute([$userId, $videoId, $posicao, $tempoFinal, $metodo]);

        // Registrar sessão de encerramento
        try {
            $sessTable = Database::table('sessao_reproducao');
            $stmtSess = $pdo->prepare("
                INSERT INTO `{$sessTable}` (id_usuario, id_video, inicio, fim, segundos_assistidos)
                VALUES (?, ?, NOW(), NOW(), ?)
            ");
            $stmtSess->execute([
                $userId,
                $videoId,
                $tempoAssistido > 0 ? $tempoAssistido : 10
            ]);
        } catch (\Throwable $e) {}

        Audit::log('CONCLUIU_VIDEO', 'video', $videoId);

        Response::json([
            'success' => true,
            'message' => 'Aula concluída com sucesso!'
        ]);
    }

    public function concluir(): void
    {
        $this->markVideoComplete();
    }

    public function preferencias(): void
    {
        Auth::requireAuth();
        $user = Auth::user();
        $userId = (int)$user['id'];

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $tema = in_array($input['tema'] ?? '', ['claro', 'escuro']) ? $input['tema'] : 'claro';

        $pdo = Database::getConnection();
        $table = Database::table('preferencia_usuario');
        $stmt = $pdo->prepare("
            INSERT INTO `{$table}` (id_usuario, tema, atualizado_em)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE tema = VALUES(tema), atualizado_em = NOW()
        ");
        $stmt->execute([$userId, $tema]);
        setcookie('decesaro_theme', $tema, time() + 31536000, '/', '', false, false);
        $_SESSION['user_tema'] = $tema;

        Response::json(['success' => true]);
    }

    public function notificacoes(): void
    {
        Auth::requireAuth();
        $user = Auth::user();
        $userId = (int)$user['id'];

        $pdo = Database::getConnection();
        $notifTable = Database::table('notificacao');

        // Total não lidas
        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM `{$notifTable}` WHERE id_usuario = ? AND lida = 'N'");
        $stmtCount->execute([$userId]);
        $naoLidas = (int)$stmtCount->fetchColumn();

        // Lista das últimas 50 notificações
        $stmtList = $pdo->prepare("
            SELECT id, titulo, mensagem, url, tipo, lida, lida_em, criado_em
            FROM `{$notifTable}`
            WHERE id_usuario = ?
            ORDER BY criado_em DESC
            LIMIT 50
        ");
        $stmtList->execute([$userId]);
        $items = $stmtList->fetchAll(PDO::FETCH_ASSOC);

        Response::json([
            'success' => true,
            'unread_count' => $naoLidas,
            'items' => $items
        ]);
    }

    public function notificacaoMarcarLida(): void
    {
        Auth::requireAuth();
        $user = Auth::user();
        $userId = (int)$user['id'];

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = isset($input['id']) ? (int)$input['id'] : 0;

        if ($id <= 0) {
            Response::json(['success' => false, 'message' => 'Notificação inválida.'], 400);
        }

        $pdo = Database::getConnection();
        $notifTable = Database::table('notificacao');
        $stmt = $pdo->prepare("UPDATE `{$notifTable}` SET lida = 'S', lida_em = NOW() WHERE id = ? AND id_usuario = ?");
        $stmt->execute([$id, $userId]);

        Response::json(['success' => true, 'message' => 'Notificação marcada como lida.']);
    }

    public function notificacaoMarcarTodasLidas(): void
    {
        Auth::requireAuth();
        $user = Auth::user();
        $userId = (int)$user['id'];

        $pdo = Database::getConnection();
        $notifTable = Database::table('notificacao');
        $stmt = $pdo->prepare("UPDATE `{$notifTable}` SET lida = 'S', lida_em = NOW() WHERE id_usuario = ? AND lida = 'N'");
        $stmt->execute([$userId]);

        Response::json(['success' => true, 'message' => 'Todas as notificações foram marcadas como lidas.']);
    }

    public function pushPublicKey(): void
    {
        Auth::requireAuth();
        Response::json([
            'success' => true,
            'publicKey' => \App\Services\WebPushService::getPublicKey()
        ]);
    }

    public function pushSubscribe(): void
    {
        Auth::requireAuth();
        $user = Auth::user();
        $userId = (int)$user['id'];

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $endpoint = trim((string)($input['endpoint'] ?? ''));
        $keys = $input['keys'] ?? [];
        $p256dh = trim((string)($keys['p256dh'] ?? ($input['p256dh'] ?? '')));
        $auth = trim((string)($keys['auth'] ?? ($input['auth'] ?? '')));

        if (empty($endpoint) || empty($p256dh) || empty($auth)) {
            Response::json(['success' => false, 'message' => 'Dados de inscrição push inválidos.'], 400);
        }

        $endpointHash = hash('sha256', $endpoint);
        $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

        $pdo = Database::getConnection();
        $table = Database::table('push_subscription');
        $stmt = $pdo->prepare("
            INSERT INTO `{$table}` (id_usuario, endpoint, endpoint_hash, p256dh, auth, user_agent, ativo, atualizado_em)
            VALUES (?, ?, ?, ?, ?, ?, 'S', NOW())
            ON DUPLICATE KEY UPDATE 
                id_usuario = VALUES(id_usuario),
                endpoint = VALUES(endpoint),
                p256dh = VALUES(p256dh),
                auth = VALUES(auth),
                user_agent = VALUES(user_agent),
                ativo = 'S',
                atualizado_em = NOW()
        ");
        $stmt->execute([$userId, $endpoint, $endpointHash, $p256dh, $auth, $userAgent]);

        // Garantir que a preferência do usuário esteja ativa
        $prefTable = Database::table('preferencia_usuario');
        $pdo->prepare("
            INSERT INTO `{$prefTable}` (id_usuario, notificacoes_push, atualizado_em)
            VALUES (?, 'S', NOW())
            ON DUPLICATE KEY UPDATE notificacoes_push = 'S', atualizado_em = NOW()
        ")->execute([$userId]);

        Response::json(['success' => true, 'message' => 'Dispositivo inscrito com sucesso para notificações push!']);
    }

    public function pushUnsubscribe(): void
    {
        Auth::requireAuth();
        $user = Auth::user();
        $userId = (int)$user['id'];

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $endpoint = trim((string)($input['endpoint'] ?? ''));

        $pdo = Database::getConnection();
        $table = Database::table('push_subscription');

        if (!empty($endpoint)) {
            $endpointHash = hash('sha256', $endpoint);
            $stmt = $pdo->prepare("UPDATE `{$table}` SET ativo = 'N', atualizado_em = NOW() WHERE endpoint_hash = ? AND id_usuario = ?");
            $stmt->execute([$endpointHash, $userId]);
        } else {
            $stmt = $pdo->prepare("UPDATE `{$table}` SET ativo = 'N', atualizado_em = NOW() WHERE id_usuario = ?");
            $stmt->execute([$userId]);
        }

        Response::json(['success' => true, 'message' => 'Inscrição push desativada com sucesso.']);
    }

    public function pushTest(): void
    {
        Auth::requireAuth();
        $user = Auth::user();
        $userId = (int)$user['id'];

        $res = \App\Services\WebPushService::sendToUser($userId, [
            'title' => 'Teste de Notificação Push',
            'body' => 'Olá ' . $user['nome'] . '! As notificações push estão funcionando perfeitamente.',
            'url' => '/desenvolvimento/perfil'
        ]);

        Response::json([
            'success' => true,
            'message' => 'Disparo de teste realizado!',
            'details' => $res
        ]);
    }
}
