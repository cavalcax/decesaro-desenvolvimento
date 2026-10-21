<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Audit;
use App\Helpers\Auth;
use App\Helpers\Format;
use App\Helpers\Response;
use App\Helpers\Security;
use App\Services\ExportService;
use Config\Database;
use PDO;

class AdminController
{
    public function __construct()
    {
        Auth::requireAdmin();
    }

    // 1. Dashboard Administrativo
    public function dashboard(): void
    {
        $pdo = Database::getConnection();
        $uTable = Database::table('usuario');
        $rTable = Database::table('raia');
        $cTable = Database::table('raia_conteudo');
        $vTable = Database::table('video');
        $pvTable = Database::table('progresso_video');
        $eTable = Database::table('evento');

        $totalUsuarios = (int)$pdo->query("SELECT COUNT(*) FROM `{$uTable}` WHERE status = 'Ativo'")->fetchColumn();
        $totalRaias = (int)$pdo->query("SELECT COUNT(*) FROM `{$rTable}`")->fetchColumn();
        $totalConteudos = (int)$pdo->query("SELECT COUNT(*) FROM `{$cTable}` WHERE ativo = 'S'")->fetchColumn();
        $totalVideos = (int)$pdo->query("SELECT COUNT(*) FROM `{$vTable}`")->fetchColumn();
        $totalConclusoes = (int)$pdo->query("SELECT COUNT(*) FROM `{$pvTable}` WHERE concluido = 'S'")->fetchColumn();
        $tempoTotalAssistido = (int)$pdo->query("SELECT SUM(tempo_assistido) FROM `{$pvTable}`")->fetchColumn();
        $proximosEventosCount = (int)$pdo->query("SELECT COUNT(*) FROM `{$eTable}` WHERE data_inicio >= NOW() AND situacao = 'Confirmado'")->fetchColumn();

        // Participantes por Família
        $stmtFam = $pdo->query("SELECT familia, COUNT(*) as total FROM `{$uTable}` WHERE status = 'Ativo' GROUP BY familia ORDER BY total DESC");
        $porFamilia = $stmtFam->fetchAll();

        // Participantes por Geração
        $stmtGer = $pdo->query("SELECT geracao, COUNT(*) as total FROM `{$uTable}` WHERE status = 'Ativo' GROUP BY geracao ORDER BY geracao ASC");
        $porGeracao = $stmtGer->fetchAll();

        // Atividades recentes
        $aTable = Database::table('auditoria_log');
        $stmtAct = $pdo->query("
            SELECT a.*, u.nome as usuario_nome
            FROM `{$aTable}` a
            LEFT JOIN `{$uTable}` u ON u.id = a.id_usuario
            ORDER BY a.data_hora DESC
            LIMIT 6
        ");
        $atividadesRecentes = $stmtAct->fetchAll();

        Response::view('admin/dashboard', [
            'pageTitle' => 'Painel Administrativo — Comitê',
            'kpis' => [
                'usuarios' => $totalUsuarios,
                'raias' => $totalRaias,
                'conteudos' => $totalConteudos,
                'videos' => $totalVideos,
                'conclusoes' => $totalConclusoes,
                'tempo_assistido' => $tempoTotalAssistido,
                'proximos_eventos' => $proximosEventosCount
            ],
            'porFamilia' => $porFamilia,
            'porGeracao' => $porGeracao,
            'atividadesRecentes' => $atividadesRecentes
        ]);
    }

    // 2. Participantes
    public function participantes(): void
    {
        $pdo = Database::getConnection();
        $uTable = Database::table('usuario');

        // Se for requisição AJAX da tabela
        if ($this->isAjax()) {
            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = max(5, min(100, (int)($_GET['per_page'] ?? 10)));
            $search = trim($_GET['search'] ?? '');
            $familia = trim($_GET['familia'] ?? '');
            $geracao = trim($_GET['geracao'] ?? '');
            $tipo = trim($_GET['tipo'] ?? '');

            $allowedSorts = ['id', 'nome', 'email', 'data_nascimento', 'familia', 'geracao', 'tipo', 'status'];
            $sortBy = in_array($_GET['sort'] ?? '', $allowedSorts) ? $_GET['sort'] : 'id';
            $sortDir = strtolower($_GET['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

            $where = ["1=1"];
            $params = [];

            if ($search !== '') {
                $where[] = "(nome LIKE ? OR email LIKE ?)";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }
            if ($familia !== '') {
                $where[] = "familia = ?";
                $params[] = $familia;
            }
            if ($geracao !== '') {
                $where[] = "geracao = ?";
                $params[] = $geracao;
            }
            if ($tipo !== '') {
                $where[] = "tipo = ?";
                $params[] = $tipo;
            }

            $whereClause = implode(' AND ', $where);

            $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM `{$uTable}` WHERE {$whereClause}");
            $stmtCount->execute($params);
            $total = (int)$stmtCount->fetchColumn();

            $offset = ($page - 1) * $perPage;
            $stmt = $pdo->prepare("SELECT id, nome, email, data_nascimento, tipo, familia, geracao, status FROM `{$uTable}` WHERE {$whereClause} ORDER BY {$sortBy} {$sortDir} LIMIT {$perPage} OFFSET {$offset}");
            $stmt->execute($params);
            $items = $stmt->fetchAll();

            foreach ($items as &$it) {
                $it['idade'] = Auth::calculateAge($it['data_nascimento']);
                $it['data_nasc_fmt'] = date('d/m/Y', strtotime($it['data_nascimento']));
            }

            Response::json(['success' => true, 'data' => ['items' => $items, 'total' => $total]]);
        }

        Response::view('admin/participantes', ['pageTitle' => 'Gestão de Participantes']);
    }

    public function participanteSalvar(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = !empty($input['id']) ? (int)$input['id'] : null;

        $nome = trim($input['nome'] ?? '');
        $email = trim($input['email'] ?? '');
        $dataNasc = trim($input['data_nascimento'] ?? '');
        $tipo = in_array($input['tipo'] ?? '', ['Normal', 'Admin']) ? $input['tipo'] : 'Normal';
        $familia = in_array($input['familia'] ?? '', ['Nestor', 'Zeca', 'Augusto', 'Wilson', 'Marisa', 'Nane', 'Tati']) ? $input['familia'] : 'Nestor';
        $geracao = trim($input['geracao'] ?? '3ª Geração');
        $status = in_array($input['status'] ?? '', ['Ativo', 'Inativo']) ? $input['status'] : 'Ativo';
        $senha = trim($input['senha'] ?? '');

        if (empty($nome) || empty($email) || empty($dataNasc)) {
            Response::json(['success' => false, 'message' => 'Preencha todos os campos obrigatórios.'], 400);
        }

        $pdo = Database::getConnection();
        $uTable = Database::table('usuario');

        // Checar e-mail duplicado
        $stmtCheck = $pdo->prepare("SELECT id FROM `{$uTable}` WHERE email = ? AND id != ?");
        $stmtCheck->execute([$email, $id ?? 0]);
        if ($stmtCheck->fetch()) {
            Response::json(['success' => false, 'message' => 'Este e-mail já está em uso por outro participante.'], 400);
        }

        if ($id) {
            // Edição
            if (!empty($senha)) {
                $val = Security::validatePasswordStrength($senha);
                if (!$val['valid']) {
                    Response::json(['success' => false, 'message' => $val['message']], 400);
                }
                $hash = Security::hashPassword($senha);
                $stmt = $pdo->prepare("UPDATE `{$uTable}` SET nome=?, email=?, data_nascimento=?, tipo=?, familia=?, geracao=?, status=?, senha=?, atualizado_em=NOW() WHERE id=?");
                $stmt->execute([$nome, $email, $dataNasc, $tipo, $familia, $geracao, $status, $hash, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE `{$uTable}` SET nome=?, email=?, data_nascimento=?, tipo=?, familia=?, geracao=?, status=?, atualizado_em=NOW() WHERE id=?");
                $stmt->execute([$nome, $email, $dataNasc, $tipo, $familia, $geracao, $status, $id]);
            }
            Audit::log('EDITOU_PARTICIPANTE', 'usuario', $id, null, ['nome' => $nome, 'email' => $email]);
        } else {
            // Novo
            if (empty($senha)) {
                $senha = 'DeCesaro@2026!';
            }
            $hash = Security::hashPassword($senha);
            $stmt = $pdo->prepare("INSERT INTO `{$uTable}` (nome, email, senha, data_nascimento, tipo, familia, geracao, status, criado_em) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$nome, $email, $hash, $dataNasc, $tipo, $familia, $geracao, $status]);
            $newId = (int)$pdo->lastInsertId();

            // Criar preferências padrão
            $prefTable = Database::table('preferencia_usuario');
            $pdo->prepare("INSERT INTO `{$prefTable}` (id_usuario, tema) VALUES (?, 'claro')")->execute([$newId]);

            Audit::log('CRIOU_PARTICIPANTE', 'usuario', $newId, null, ['nome' => $nome, 'email' => $email]);
        }

        Response::json(['success' => true, 'message' => 'Participante salvo com sucesso!']);
    }

    public function participanteExcluir(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = (int)($input['id'] ?? 0);

        if ($id === (int)Auth::id()) {
            Response::json(['success' => false, 'message' => 'Você não pode excluir sua própria conta.'], 400);
        }

        $pdo = Database::getConnection();
        $uTable = Database::table('usuario');
        $stmt = $pdo->prepare("DELETE FROM `{$uTable}` WHERE id = ?");
        $stmt->execute([$id]);

        Audit::log('EXCLUIU_PARTICIPANTE', 'usuario', $id);

        Response::json(['success' => true, 'message' => 'Participante removido com sucesso.']);
    }

    // 3. Níveis
    public function niveis(): void
    {
        $pdo = Database::getConnection();
        $table = Database::table('nivel');

        if ($this->isAjax()) {
            $search = trim($_GET['search'] ?? '');
            if ($search !== '') {
                $stmt = $pdo->prepare("SELECT id, descricao, idade_inicial, idade_final FROM `{$table}` WHERE descricao LIKE ? OR idade_inicial LIKE ? OR idade_final LIKE ? ORDER BY idade_inicial ASC");
                $stmt->execute(["%{$search}%", "%{$search}%", "%{$search}%"]);
            } else {
                $stmt = $pdo->query("SELECT id, descricao, idade_inicial, idade_final FROM `{$table}` ORDER BY idade_inicial ASC");
            }
            $items = $stmt->fetchAll();
            Response::json(['success' => true, 'data' => ['items' => $items, 'total' => count($items)]]);
        }

        Response::view('admin/niveis', ['pageTitle' => 'Gestão de Níveis de Formação']);
    }

    public function nivelSalvar(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = !empty($input['id']) ? (int)$input['id'] : null;
        $desc = trim($input['descricao'] ?? '');
        $idadeIni = (int)($input['idade_inicial'] ?? 0);
        $idadeFim = (int)($input['idade_final'] ?? 120);

        if (empty($desc)) {
            Response::json(['success' => false, 'message' => 'Informe a descrição do nível.'], 400);
        }

        $pdo = Database::getConnection();
        $table = Database::table('nivel');

        if ($id) {
            $stmt = $pdo->prepare("UPDATE `{$table}` SET descricao=?, idade_inicial=?, idade_final=?, atualizado_em=NOW() WHERE id=?");
            $stmt->execute([$desc, $idadeIni, $idadeFim, $id]);
            Audit::log('EDITOU_NIVEL', 'nivel', $id);
        } else {
            $stmt = $pdo->prepare("INSERT INTO `{$table}` (descricao, idade_inicial, idade_final) VALUES (?, ?, ?)");
            $stmt->execute([$desc, $idadeIni, $idadeFim]);
            $newId = (int)$pdo->lastInsertId();
            Audit::log('CRIOU_NIVEL', 'nivel', $newId);
        }

        Response::json(['success' => true, 'message' => 'Nível salvo com sucesso!']);
    }

    public function nivelExcluir(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = (int)($input['id'] ?? 0);

        $pdo = Database::getConnection();
        $table = Database::table('nivel');
        $stmt = $pdo->prepare("DELETE FROM `{$table}` WHERE id = ?");
        $stmt->execute([$id]);

        Audit::log('EXCLUIU_NIVEL', 'nivel', $id);
        Response::json(['success' => true, 'message' => 'Nível excluído com sucesso.']);
    }

    // 4. Raias
    public function raias(): void
    {
        $pdo = Database::getConnection();
        $table = Database::table('raia');

        if ($this->isAjax()) {
            $search = trim($_GET['search'] ?? '');
            if ($search !== '') {
                $stmt = $pdo->prepare("SELECT id, descricao, subtitulo, icone, slug, ordem FROM `{$table}` WHERE descricao LIKE ? OR subtitulo LIKE ? OR slug LIKE ? ORDER BY ordem ASC");
                $stmt->execute(["%{$search}%", "%{$search}%", "%{$search}%"]);
            } else {
                $stmt = $pdo->query("SELECT id, descricao, subtitulo, icone, slug, ordem FROM `{$table}` ORDER BY ordem ASC");
            }
            $items = $stmt->fetchAll();
            Response::json(['success' => true, 'data' => ['items' => $items, 'total' => count($items)]]);
        }

        Response::view('admin/raias', ['pageTitle' => 'Gestão de Raias']);
    }

    public function raiaSalvar(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = !empty($input['id']) ? (int)$input['id'] : null;
        $desc = trim($input['descricao'] ?? '');
        $sub = trim($input['subtitulo'] ?? '');
        $icone = trim($input['icone'] ?? 'fa-folder');
        $slug = trim($input['slug'] ?? '');
        $ordem = (int)($input['ordem'] ?? 0);

        if (empty($desc) || empty($slug)) {
            Response::json(['success' => false, 'message' => 'Descrição e slug são obrigatórios.'], 400);
        }

        $pdo = Database::getConnection();
        $table = Database::table('raia');

        if ($id) {
            $stmt = $pdo->prepare("UPDATE `{$table}` SET descricao=?, subtitulo=?, icone=?, slug=?, ordem=? WHERE id=?");
            $stmt->execute([$desc, $sub, $icone, $slug, $ordem, $id]);
            Audit::log('EDITOU_RAIA', 'raia', $id);
        } else {
            $stmt = $pdo->prepare("INSERT INTO `{$table}` (descricao, subtitulo, icone, slug, ordem) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$desc, $sub, $icone, $slug, $ordem]);
            $newId = (int)$pdo->lastInsertId();
            Audit::log('CRIOU_RAIA', 'raia', $newId);
        }

        Response::json(['success' => true, 'message' => 'Raia salva com sucesso!']);
    }

    public function raiaExcluir(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = (int)($input['id'] ?? 0);

        $pdo = Database::getConnection();
        $table = Database::table('raia');
        $stmt = $pdo->prepare("DELETE FROM `{$table}` WHERE id = ?");
        $stmt->execute([$id]);

        Audit::log('EXCLUIU_RAIA', 'raia', $id);
        Response::json(['success' => true, 'message' => 'Raia excluída com sucesso.']);
    }

    // 5. Conteúdos
    public function conteudos(): void
    {
        $pdo = Database::getConnection();
        $cTable = Database::table('raia_conteudo');
        $rTable = Database::table('raia');
        $rcnTable = Database::table('raia_conteudo_nivel');

        if ($this->isAjax()) {
            $raiaId = !empty($_GET['raia_id']) ? (int)$_GET['raia_id'] : null;
            $search = trim($_GET['search'] ?? '');

            $where = ["1=1"];
            $params = [];
            if ($raiaId) {
                $where[] = "c.id_raia = ?";
                $params[] = $raiaId;
            }
            if ($search !== '') {
                $where[] = "(c.descricao LIKE ? OR c.subtitulo LIKE ?)";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }

            $whereClause = implode(' AND ', $where);

            $stmt = $pdo->prepare("
                SELECT c.id, c.descricao, c.subtitulo, c.detalhes, c.id_raia, c.ordem, c.ativo,
                       r.descricao as raia_descricao,
                       (SELECT GROUP_CONCAT(id_nivel) FROM `{$rcnTable}` WHERE id_raia_conteudo = c.id) as niveis_ids
                FROM `{$cTable}` c
                JOIN `{$rTable}` r ON r.id = c.id_raia
                WHERE {$whereClause}
                ORDER BY c.id_raia ASC, c.ordem ASC
            ");
            $stmt->execute($params);
            $items = $stmt->fetchAll();

            Response::json(['success' => true, 'data' => ['items' => $items, 'total' => count($items)]]);
        }

        // Buscar raias e níveis para o formulário
        $nTable = Database::table('nivel');
        $raias = $pdo->query("SELECT id, descricao FROM `{$rTable}` ORDER BY ordem ASC")->fetchAll();
        $niveis = $pdo->query("SELECT id, descricao FROM `{$nTable}` ORDER BY idade_inicial ASC")->fetchAll();

        Response::view('admin/conteudos', [
            'pageTitle' => 'Gestão de Conteúdos das Raias',
            'raias' => $raias,
            'niveis' => $niveis
        ]);
    }

    public function conteudoSalvar(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = !empty($input['id']) ? (int)$input['id'] : null;
        $idRaia = (int)($input['id_raia'] ?? 0);
        $desc = trim($input['descricao'] ?? '');
        $sub = trim($input['subtitulo'] ?? '');
        $detalhes = trim($input['detalhes'] ?? '');
        $ordem = (int)($input['ordem'] ?? 0);
        $ativo = in_array($input['ativo'] ?? '', ['S', 'N']) ? $input['ativo'] : 'S';
        $niveis = is_array($input['niveis'] ?? null) ? $input['niveis'] : [];

        if (empty($desc) || !$idRaia) {
            Response::json(['success' => false, 'message' => 'Selecione a raia e informe o título do conteúdo.'], 400);
        }

        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            $cTable = Database::table('raia_conteudo');
            $rcnTable = Database::table('raia_conteudo_nivel');

            if ($id) {
                $stmt = $pdo->prepare("UPDATE `{$cTable}` SET id_raia=?, descricao=?, subtitulo=?, detalhes=?, ordem=?, ativo=?, atualizado_em=NOW() WHERE id=?");
                $stmt->execute([$idRaia, $desc, $sub, $detalhes, $ordem, $ativo, $id]);
                $contentId = $id;
            } else {
                $stmt = $pdo->prepare("INSERT INTO `{$cTable}` (id_raia, descricao, subtitulo, detalhes, ordem, ativo) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$idRaia, $desc, $sub, $detalhes, $ordem, $ativo]);
                $contentId = (int)$pdo->lastInsertId();
            }

            // Atualizar associação de níveis (RaiaConteudoNivel)
            $pdo->prepare("DELETE FROM `{$rcnTable}` WHERE id_raia_conteudo = ?")->execute([$contentId]);
            if (!empty($niveis)) {
                $stmtNivel = $pdo->prepare("INSERT INTO `{$rcnTable}` (id_raia_conteudo, id_nivel) VALUES (?, ?)");
                foreach ($niveis as $nid) {
                    $stmtNivel->execute([$contentId, (int)$nid]);
                }
            }

            $pdo->commit();
            Audit::log($id ? 'EDITOU_CONTEUDO' : 'CRIOU_CONTEUDO', 'raia_conteudo', $contentId);

            Response::json(['success' => true, 'message' => 'Conteúdo e níveis vinculados salvos com sucesso!']);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            Response::json(['success' => false, 'message' => 'Erro ao salvar conteúdo: ' . $e->getMessage()], 500);
        }
    }

    public function conteudoExcluir(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = (int)($input['id'] ?? 0);

        $pdo = Database::getConnection();
        $table = Database::table('raia_conteudo');
        $stmt = $pdo->prepare("DELETE FROM `{$table}` WHERE id = ?");
        $stmt->execute([$id]);

        Audit::log('EXCLUIU_CONTEUDO', 'raia_conteudo', $id);
        Response::json(['success' => true, 'message' => 'Conteúdo removido com sucesso.']);
    }

    // 6. Vídeos
    public function videos(): void
    {
        $pdo = Database::getConnection();
        $vTable = Database::table('video');
        $cTable = Database::table('raia_conteudo');
        $rTable = Database::table('raia');

        if ($this->isAjax()) {
            $conteudoId = !empty($_GET['conteudo_id']) ? (int)$_GET['conteudo_id'] : null;
            $search = trim($_GET['search'] ?? '');

            $where = ["1=1"];
            $params = [];
            if ($conteudoId) {
                $where[] = "v.id_raia_conteudo = ?";
                $params[] = $conteudoId;
            }
            if ($search !== '') {
                $where[] = "(v.titulo LIKE ? OR v.descricao LIKE ?)";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }

            $whereClause = implode(' AND ', $where);

            $stmt = $pdo->prepare("
                SELECT v.id, v.titulo, v.descricao, v.link_arquivo, v.id_raia_conteudo, v.exclusivo, v.duracao_segundos, v.ordem,
                       c.descricao as conteudo_descricao, r.descricao as raia_descricao
                FROM `{$vTable}` v
                JOIN `{$cTable}` c ON c.id = v.id_raia_conteudo
                JOIN `{$rTable}` r ON r.id = c.id_raia
                WHERE {$whereClause}
                ORDER BY v.id_raia_conteudo ASC, v.ordem ASC
            ");
            $stmt->execute($params);
            $items = $stmt->fetchAll();

            Response::json(['success' => true, 'data' => ['items' => $items, 'total' => count($items)]]);
        }

        $conteudos = $pdo->query("
            SELECT c.id, c.descricao, r.descricao as raia_descricao
            FROM `{$cTable}` c
            JOIN `{$rTable}` r ON r.id = c.id_raia
            ORDER BY r.ordem ASC, c.ordem ASC
        ")->fetchAll();

        Response::view('admin/videos', [
            'pageTitle' => 'Gestão de Vídeos e Aulas',
            'conteudos' => $conteudos
        ]);
    }

    public function videoSalvar(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = !empty($input['id']) ? (int)$input['id'] : null;
        $titulo = trim($input['titulo'] ?? '');
        $descricao = trim($input['descricao'] ?? '');
        $link = trim($input['link_arquivo'] ?? '');
        $idConteudo = (int)($input['id_raia_conteudo'] ?? 0);
        $exclusivo = in_array($input['exclusivo'] ?? '', ['Sim', 'Não']) ? $input['exclusivo'] : 'Sim';
        $duracao = Format::parseDuration($input['duracao_segundos'] ?? ($input['duracao'] ?? 0));
        $ordem = (int)($input['ordem'] ?? 0);

        if (empty($titulo) || empty($link) || !$idConteudo) {
            Response::json(['success' => false, 'message' => 'Informe o título, link do arquivo e conteúdo vinculado.'], 400);
        }

        $pdo = Database::getConnection();
        $vTable = Database::table('video');

        if ($id) {
            $stmt = $pdo->prepare("UPDATE `{$vTable}` SET titulo=?, descricao=?, link_arquivo=?, id_raia_conteudo=?, exclusivo=?, duracao_segundos=?, ordem=?, atualizado_em=NOW() WHERE id=?");
            $stmt->execute([$titulo, $descricao, $link, $idConteudo, $exclusivo, $duracao, $ordem, $id]);
            Audit::log('EDITOU_VIDEO', 'video', $id);
        } else {
            $stmt = $pdo->prepare("INSERT INTO `{$vTable}` (titulo, descricao, link_arquivo, id_raia_conteudo, exclusivo, duracao_segundos, ordem) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$titulo, $descricao, $link, $idConteudo, $exclusivo, $duracao, $ordem]);
            $newId = (int)$pdo->lastInsertId();
            Audit::log('CRIOU_VIDEO', 'video', $newId);
        }

        Response::json(['success' => true, 'message' => 'Vídeo cadastrado com sucesso!']);
    }

    public function videoExcluir(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = (int)($input['id'] ?? 0);

        $pdo = Database::getConnection();
        $table = Database::table('video');
        $stmt = $pdo->prepare("DELETE FROM `{$table}` WHERE id = ?");
        $stmt->execute([$id]);

        Audit::log('EXCLUIU_VIDEO', 'video', $id);
        Response::json(['success' => true, 'message' => 'Vídeo excluído com sucesso.']);
    }

    // 7. Agenda e Eventos
    public function agenda(): void
    {
        $pdo = Database::getConnection();
        $eTable = Database::table('evento');
        $cTable = Database::table('categoria_evento');

        if ($this->isAjax()) {
            $catId = !empty($_GET['categoria_id']) ? (int)$_GET['categoria_id'] : null;
            $search = trim($_GET['search'] ?? '');

            $where = ["1=1"];
            $params = [];
            if ($catId) {
                $where[] = "e.id_categoria = ?";
                $params[] = $catId;
            }
            if ($search !== '') {
                $where[] = "(e.titulo LIKE ? OR e.descricao LIKE ? OR e.local LIKE ? OR e.responsavel LIKE ?)";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }
            $whereClause = implode(' AND ', $where);

            $stmt = $pdo->prepare("
                SELECT e.*, c.nome as categoria_nome, c.cor as categoria_cor,
                       (SELECT COUNT(*) FROM " . Database::table('inscricao_evento') . " WHERE id_evento = e.id AND status = 'inscrito') as inscritos_count
                FROM `{$eTable}` e
                JOIN `{$cTable}` c ON c.id = e.id_categoria
                WHERE {$whereClause}
                ORDER BY e.data_inicio DESC
            ");
            $stmt->execute($params);
            $items = $stmt->fetchAll();
            Response::json(['success' => true, 'data' => ['items' => $items, 'total' => count($items)]]);
        }

        $categorias = $pdo->query("SELECT id, nome FROM `{$cTable}` ORDER BY nome ASC")->fetchAll();

        Response::view('admin/agenda', [
            'pageTitle' => 'Gestão da Agenda e Eventos',
            'categorias' => $categorias
        ]);
    }

    public function agendaSalvar(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = !empty($input['id']) ? (int)$input['id'] : null;
        $titulo = trim($input['titulo'] ?? '');
        $desc = trim($input['descricao'] ?? '');
        $idCat = (int)($input['id_categoria'] ?? 0);
        $dataIni = trim($input['data_inicio'] ?? '');
        $dataFim = !empty($input['data_fim']) ? trim($input['data_fim']) : null;
        $diaInteiro = ($input['dia_inteiro'] ?? '') === 'S' ? 'S' : 'N';
        $local = trim($input['local'] ?? '');
        $link = trim($input['link_online'] ?? '');
        $responsavel = trim($input['responsavel'] ?? '');
        $situacao = in_array($input['situacao'] ?? '', ['Confirmado', 'Pendente', 'Cancelado']) ? $input['situacao'] : 'Confirmado';
        $vagas = !empty($input['limite_vagas']) ? (int)$input['limite_vagas'] : null;

        if (empty($titulo) || !$idCat || empty($dataIni)) {
            Response::json(['success' => false, 'message' => 'Informe o título, categoria e data de início.'], 400);
        }

        $pdo = Database::getConnection();
        $eTable = Database::table('evento');

        if ($id) {
            $stmt = $pdo->prepare("
                UPDATE `{$eTable}`
                SET titulo=?, descricao=?, id_categoria=?, data_inicio=?, data_fim=?, dia_inteiro=?, local=?, link_online=?, responsavel=?, situacao=?, limite_vagas=?, atualizado_por=?, atualizado_em=NOW()
                WHERE id=?
            ");
            $stmt->execute([$titulo, $desc, $idCat, $dataIni, $dataFim, $diaInteiro, $local, $link, $responsavel, $situacao, $vagas, Auth::id(), $id]);
            Audit::log('EDITOU_EVENTO', 'evento', $id);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO `{$eTable}` (titulo, descricao, id_categoria, data_inicio, data_fim, dia_inteiro, local, link_online, responsavel, situacao, limite_vagas, criado_por)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$titulo, $desc, $idCat, $dataIni, $dataFim, $diaInteiro, $local, $link, $responsavel, $situacao, $vagas, Auth::id()]);
            $newId = (int)$pdo->lastInsertId();
            Audit::log('CRIOU_EVENTO', 'evento', $newId);
        }

        Response::json(['success' => true, 'message' => 'Evento salvo com êxito!']);
    }

    public function agendaInscritos(string $id): void
    {
        $eventoId = (int)$id;
        $pdo = Database::getConnection();
        $iTable = Database::table('inscricao_evento');
        $uTable = Database::table('usuario');

        $stmt = $pdo->prepare("
            SELECT i.id, i.id_usuario, i.status, i.presenca, i.data_inscricao,
                   u.nome as usuario_nome, u.email as usuario_email, u.familia, u.geracao
            FROM `{$iTable}` i
            JOIN `{$uTable}` u ON u.id = i.id_usuario
            WHERE i.id_evento = ?
            ORDER BY i.data_inscricao ASC
        ");
        $stmt->execute([$eventoId]);
        $inscritos = $stmt->fetchAll();

        Response::json(['success' => true, 'data' => $inscritos]);
    }

    public function agendaMarcarPresenca(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $inscricaoId = (int)($input['inscricao_id'] ?? 0);
        $presenca = ($input['presenca'] ?? '') === 'S' ? 'S' : 'N';

        $pdo = Database::getConnection();
        $table = Database::table('inscricao_evento');
        $stmt = $pdo->prepare("UPDATE `{$table}` SET presenca = ?, presenca_registrada_em = NOW(), presenca_registrada_por = ? WHERE id = ?");
        $stmt->execute([$presenca, Auth::id(), $inscricaoId]);

        Response::json(['success' => true, 'message' => 'Presença atualizada com sucesso!']);
    }

    // 8. Relatórios e Exportações CSV / PDF
    public function relatorios(): void
    {
        $tipo = $_GET['tipo'] ?? 'participantes';
        $export = $_GET['export'] ?? null;

        $pdo = Database::getConnection();
        $uTable = Database::table('usuario');
        $pvTable = Database::table('progresso_video');
        $vTable = Database::table('video');
        $rTable = Database::table('raia');
        $cTable = Database::table('raia_conteudo');

        $headers = [];
        $rows = [];
        $title = 'Relatório';
        $subtitle = 'Dados do Sistema';

        switch ($tipo) {
            case 'participantes':
                $title = 'Relatório de Progresso por Participante';
                $subtitle = 'Visão geral da jornada de desenvolvimento individual';
                $headers = ['Participante', 'Família', 'Geração', 'Faixa Etária', 'Aulas Concluídas', 'Tempo Assistido'];
                $stmt = $pdo->query("
                    SELECT u.nome, u.familia, u.geracao, u.data_nascimento,
                           COUNT(CASE WHEN pv.concluido = 'S' THEN 1 END) as concluidas,
                           IFNULL(SUM(pv.tempo_assistido), 0) as tempo_segundos
                    FROM `{$uTable}` u
                    LEFT JOIN `{$pvTable}` pv ON pv.id_usuario = u.id
                    WHERE u.status = 'Ativo'
                    GROUP BY u.id
                    ORDER BY u.nome ASC
                ");
                while ($r = $stmt->fetch()) {
                    $rows[] = [
                        $r['nome'],
                        'Família ' . $r['familia'],
                        $r['geracao'],
                        Auth::formatAgeBracket(Auth::calculateAge($r['data_nascimento'])),
                        (string)$r['concluidas'],
                        \App\Helpers\Format::duration((int)$r['tempo_segundos'])
                    ];
                }
                break;

            case 'familias':
                $title = 'Relatório de Desenvolvimento por Família';
                $subtitle = 'Métricas comparativas entre os ramos familiares';
                $headers = ['Ramo Familiar', 'Participantes Ativos', 'Aulas Concluídas', 'Tempo Total em Vídeo'];
                $stmt = $pdo->query("
                    SELECT u.familia,
                           COUNT(DISTINCT u.id) as participantes,
                           COUNT(CASE WHEN pv.concluido = 'S' THEN 1 END) as concluidas,
                           IFNULL(SUM(pv.tempo_assistido), 0) as tempo_segundos
                    FROM `{$uTable}` u
                    LEFT JOIN `{$pvTable}` pv ON pv.id_usuario = u.id
                    WHERE u.status = 'Ativo'
                    GROUP BY u.familia
                    ORDER BY participantes DESC
                ");
                while ($r = $stmt->fetch()) {
                    $rows[] = [
                        'Família ' . $r['familia'],
                        (string)$r['participantes'],
                        (string)$r['concluidas'],
                        \App\Helpers\Format::duration((int)$r['tempo_segundos'])
                    ];
                }
                break;

            case 'videos':
                $title = 'Relatório de Aulas e Vídeos Mais Assistidos';
                $subtitle = 'Adesão aos conteúdos formativos';
                $headers = ['Vídeo / Aula', 'Módulo', 'Raia', 'Visualizações', 'Conclusões'];
                $stmt = $pdo->query("
                    SELECT v.titulo, c.descricao as conteudo_desc, r.descricao as raia_desc,
                           COUNT(pv.id) as visualizacoes,
                           COUNT(CASE WHEN pv.concluido = 'S' THEN 1 END) as conclusoes
                    FROM `{$vTable}` v
                    JOIN `{$cTable}` c ON c.id = v.id_raia_conteudo
                    JOIN `{$rTable}` r ON r.id = c.id_raia
                    LEFT JOIN `{$pvTable}` pv ON pv.id_video = v.id
                    GROUP BY v.id
                    ORDER BY conclusoes DESC, visualizacoes DESC
                ");
                while ($r = $stmt->fetch()) {
                    $rows[] = [
                        $r['titulo'],
                        $r['conteudo_desc'],
                        $r['raia_desc'],
                        (string)$r['visualizacoes'],
                        (string)$r['conclusoes']
                    ];
                }
                break;

            case 'eventos':
                $eTable = Database::table('evento');
                $iTable = Database::table('inscricao_evento');
                $title = 'Relatório de Participação em Eventos';
                $subtitle = 'Inscrições e presenças registradas nos encontros';
                $headers = ['Evento', 'Data Início', 'Local', 'Vagas', 'Inscritos', 'Presenças Confirmadas'];
                $stmt = $pdo->query("
                    SELECT e.titulo, e.data_inicio, e.local, e.limite_vagas,
                           COUNT(CASE WHEN i.status = 'inscrito' THEN 1 END) as inscritos,
                           COUNT(CASE WHEN i.presenca = 'S' THEN 1 END) as presencas
                    FROM `{$eTable}` e
                    LEFT JOIN `{$iTable}` i ON i.id_evento = e.id
                    GROUP BY e.id
                    ORDER BY e.data_inicio DESC
                ");
                while ($r = $stmt->fetch()) {
                    $rows[] = [
                        $r['titulo'],
                        date('d/m/Y H:i', strtotime($r['data_inicio'])),
                        $r['local'] ?? 'Online / A definir',
                        $r['limite_vagas'] ? (string)$r['limite_vagas'] : 'Ilimitado',
                        (string)$r['inscritos'],
                        (string)$r['presencas']
                    ];
                }
                break;
        }

        // Exportação CSV
        if ($export === 'csv') {
            ExportService::csv($headers, $rows, "relatorio_{$tipo}_" . date('Ymd_His') . ".csv");
        }

        // Exportação PDF
        if ($export === 'pdf') {
            ExportService::pdf($title, $subtitle, $headers, $rows, "relatorio_{$tipo}_" . date('Ymd_His') . ".pdf");
        }

        Response::view('admin/relatorios', [
            'pageTitle' => 'Relatórios Institucionais',
            'tipo' => $tipo,
            'title' => $title,
            'subtitle' => $subtitle,
            'headers' => $headers,
            'rows' => $rows
        ]);
    }

    // 9. Auditoria
    public function auditoria(): void
    {
        $pdo = Database::getConnection();
        $aTable = Database::table('auditoria_log');
        $uTable = Database::table('usuario');

        if ($this->isAjax()) {
            $search = trim($_GET['search'] ?? '');
            $where = [];
            $params = [];
            if ($search !== '') {
                $where[] = "(a.acao LIKE ? OR a.entidade LIKE ? OR a.ip LIKE ? OR u.nome LIKE ? OR u.email LIKE ?)";
                $params = ["%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"];
            }
            $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            $stmt = $pdo->prepare("
                SELECT a.id, a.acao, a.entidade, a.entidade_id, a.ip, a.data_hora,
                       u.nome as usuario_nome, u.email as usuario_email
                FROM `{$aTable}` a
                LEFT JOIN `{$uTable}` u ON u.id = a.id_usuario
                {$whereSql}
                ORDER BY a.data_hora DESC
                LIMIT 100
            ");
            $stmt->execute($params);
            $items = $stmt->fetchAll();
            Response::json(['success' => true, 'data' => ['items' => $items, 'total' => count($items)]]);
        }

        Response::view('admin/auditoria', ['pageTitle' => 'Trilha de Auditoria e Logs']);
    }

    private function isAjax(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
               (!empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));
    }
}
