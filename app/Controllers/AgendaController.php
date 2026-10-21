<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Audit;
use App\Helpers\Auth;
use App\Helpers\Response;
use App\Helpers\Security;
use Config\Database;
use PDO;

class AgendaController
{
    public function index(): void
    {
        Auth::requireAuth();
        $user = Auth::user();
        $userId = (int)$user['id'];

        $pdo = Database::getConnection();
        $catTable = Database::table('categoria_evento');
        $eventoTable = Database::table('evento');
        $inscTable = Database::table('inscricao_evento');

        // Buscar categorias de eventos
        $stmtCats = $pdo->query("SELECT id, nome, cor FROM `{$catTable}` ORDER BY nome ASC");
        $categorias = $stmtCats->fetchAll();

        // Buscar próximos eventos com status de inscrição do usuário
        $stmtEvts = $pdo->prepare("
            SELECT e.*, c.nome as categoria_nome, c.cor as categoria_cor,
                   (SELECT COUNT(*) FROM `{$inscTable}` WHERE id_evento = e.id AND status = 'inscrito') as inscritos_count,
                   (SELECT status FROM `{$inscTable}` WHERE id_evento = e.id AND id_usuario = ?) as user_inscricao_status
            FROM `{$eventoTable}` e
            JOIN `{$catTable}` c ON c.id = e.id_categoria
            WHERE e.situacao = 'Confirmado'
            ORDER BY e.data_inicio ASC
        ");
        $stmtEvts->execute([$userId]);
        $eventos = $stmtEvts->fetchAll();

        Response::view('agenda/index', [
            'pageTitle' => 'Agenda — Ações de Desenvolvimento',
            'user' => $user,
            'categorias' => $categorias,
            'eventos' => $eventos
        ]);
    }

    public function apiEventos(): void
    {
        Auth::requireAuth();
        $user = Auth::user();
        $userId = (int)$user['id'];

        $start = $_GET['start'] ?? null;
        $end = $_GET['end'] ?? null;
        $categoriaId = !empty($_GET['categoria_id']) ? (int)$_GET['categoria_id'] : null;

        $pdo = Database::getConnection();
        $eventoTable = Database::table('evento');
        $catTable = Database::table('categoria_evento');
        $inscTable = Database::table('inscricao_evento');

        $query = "
            SELECT e.id, e.titulo as title, e.data_inicio as start, e.data_fim as end,
                   e.dia_inteiro, e.descricao, e.local, e.link_online, e.limite_vagas,
                   c.nome as categoria_nome, c.cor as backgroundColor, c.cor as borderColor,
                   (SELECT COUNT(*) FROM `{$inscTable}` WHERE id_evento = e.id AND status = 'inscrito') as inscritos_count,
                   (SELECT status FROM `{$inscTable}` WHERE id_evento = e.id AND id_usuario = ?) as user_inscricao_status
            FROM `{$eventoTable}` e
            JOIN `{$catTable}` c ON c.id = e.id_categoria
            WHERE e.situacao = 'Confirmado'
        ";
        $params = [$userId];

        if ($categoriaId) {
            $query .= " AND e.id_categoria = ?";
            $params[] = $categoriaId;
        }

        if ($start && $end) {
            $query .= " AND e.data_inicio >= ? AND e.data_inicio <= ?";
            $params[] = $start;
            $params[] = $end;
        }

        $query .= " ORDER BY e.data_inicio ASC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $events = $stmt->fetchAll();

        $formatted = array_map(function ($ev) {
            return [
                'id' => (string)$ev['id'],
                'title' => $ev['title'],
                'start' => $ev['start'],
                'end' => $ev['end'],
                'allDay' => $ev['dia_inteiro'] === 'S',
                'backgroundColor' => $ev['backgroundColor'],
                'borderColor' => $ev['borderColor'],
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'descricao' => $ev['descricao'],
                    'local' => $ev['local'],
                    'link_online' => $ev['link_online'],
                    'categoria' => $ev['categoria_nome'],
                    'limite_vagas' => $ev['limite_vagas'],
                    'inscritos_count' => (int)$ev['inscritos_count'],
                    'user_inscricao_status' => $ev['user_inscricao_status']
                ]
            ];
        }, $events);

        Response::json($formatted);
    }

    public function apiInscrever(): void
    {
        Auth::requireAuth();
        $user = Auth::user();
        $userId = (int)$user['id'];

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $eventoId = (int)($input['evento_id'] ?? 0);

        if (!$eventoId) {
            Response::json(['success' => false, 'message' => 'Identificador do evento não fornecido.'], 400);
        }

        $pdo = Database::getConnection();
        $eventoTable = Database::table('evento');
        $inscTable = Database::table('inscricao_evento');

        $stmtE = $pdo->prepare("SELECT id, titulo, limite_vagas FROM `{$eventoTable}` WHERE id = ? AND situacao = 'Confirmado' LIMIT 1");
        $stmtE->execute([$eventoId]);
        $evento = $stmtE->fetch();

        if (!$evento) {
            Response::json(['success' => false, 'message' => 'Evento não disponível para inscrições.'], 404);
        }

        // Verificar vagas
        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM `{$inscTable}` WHERE id_evento = ? AND status = 'inscrito'");
        $stmtCount->execute([$eventoId]);
        $inscritos = (int)$stmtCount->fetchColumn();

        $status = 'inscrito';
        if ($evento['limite_vagas'] !== null && $inscritos >= (int)$evento['limite_vagas']) {
            $status = 'fila_espera';
        }

        // Inserir ou atualizar inscrição
        $stmtInsc = $pdo->prepare("
            INSERT INTO `{$inscTable}` (id_evento, id_usuario, status, data_inscricao)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE status = VALUES(status), data_inscricao = NOW()
        ");
        $stmtInsc->execute([$eventoId, $userId, $status]);

        Audit::log('INSCRICAO_EVENTO', 'evento', $eventoId, null, ['status' => $status]);

        $msg = $status === 'inscrito' 
            ? 'Inscrição confirmada com sucesso!' 
            : 'As vagas regulares estão esgotadas. Você foi adicionado à fila de espera.';

        Response::json([
            'success' => true,
            'message' => $msg,
            'status' => $status
        ]);
    }

    public function apiCancelarInscricao(): void
    {
        Auth::requireAuth();
        $user = Auth::user();
        $userId = (int)$user['id'];

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $eventoId = (int)($input['evento_id'] ?? 0);

        if (!$eventoId) {
            Response::json(['success' => false, 'message' => 'Identificador do evento inválido.'], 400);
        }

        $pdo = Database::getConnection();
        $inscTable = Database::table('inscricao_evento');

        $stmt = $pdo->prepare("UPDATE `{$inscTable}` SET status = 'cancelado', atualizado_em = NOW() WHERE id_evento = ? AND id_usuario = ?");
        $stmt->execute([$eventoId, $userId]);

        Audit::log('CANCELAMENTO_INSCRICAO', 'evento', $eventoId);

        Response::json([
            'success' => true,
            'message' => 'Sua inscrição foi cancelada.'
        ]);
    }
}
