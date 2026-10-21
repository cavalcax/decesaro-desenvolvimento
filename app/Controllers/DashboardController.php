<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Response;
use Config\Database;
use PDO;

class DashboardController
{
    public function index(): void
    {
        Auth::requireAuth();
        $user = Auth::user();
        $userId = (int)$user['id'];
        $age = (int)$user['idade'];

        $pdo = Database::getConnection();

        // 1. Obter as 4 Raias com progresso calculado
        $raiaTable = Database::table('raia');
        $conteudoTable = Database::table('raia_conteudo');
        $videoTable = Database::table('video');
        $progressoTable = Database::table('progresso_video');

        $stmtRaias = $pdo->query("SELECT id, descricao, subtitulo, icone, slug, ordem, idade_minima, idade_maxima FROM `{$raiaTable}` ORDER BY ordem ASC");
        $raias = $stmtRaias->fetchAll();

        $raiasData = [];
        foreach ($raias as $r) {
            $raiaId = (int)$r['id'];
            $isAcessivel = Auth::canAccessRaia($userId, $raiaId);

            // Conteúdos da raia
            $stmtC = $pdo->prepare("
                SELECT c.id, c.descricao, c.subtitulo
                FROM `{$conteudoTable}` c
                WHERE c.id_raia = ? AND c.ativo = 'S'
                ORDER BY c.ordem ASC
            ");
            $stmtC->execute([$raiaId]);
            $conteudos = $stmtC->fetchAll();

            $totalConteudos = count($conteudos);
            $elegiveisCount = 0;
            $concluidosCount = 0;

            if ($isAcessivel) {
                $elegiveisCount = $totalConteudos;

                foreach ($conteudos as $c) {
                    // Verificar se os vídeos deste conteúdo foram concluídos
                    $stmtV = $pdo->prepare("
                        SELECT COUNT(v.id) as total_videos,
                               COUNT(pv.id) as concluidos_videos
                        FROM `{$videoTable}` v
                        LEFT JOIN `{$progressoTable}` pv ON pv.id_video = v.id AND pv.id_usuario = ? AND pv.concluido = 'S'
                        WHERE v.id_raia_conteudo = ?
                    ");
                    $stmtV->execute([$userId, $c['id']]);
                    $vStats = $stmtV->fetch();

                    if (!empty($vStats['total_videos']) && $vStats['total_videos'] == $vStats['concluidos_videos']) {
                        $concluidosCount++;
                    }
                }
            }

            $progressoPercentual = $elegiveisCount > 0 ? round(($concluidosCount / $elegiveisCount) * 100) : 0;

            $raiasData[] = [
                'id' => $raiaId,
                'descricao' => $r['descricao'],
                'subtitulo' => $r['subtitulo'],
                'icone' => $r['icone'],
                'slug' => $r['slug'],
                'total_conteudos' => $totalConteudos,
                'elegiveis' => $elegiveisCount,
                'concluidos' => $concluidosCount,
                'percentual' => $progressoPercentual,
                'bloqueada' => !$isAcessivel
            ];
        }

        // 2. Próximas ações (Eventos futuros)
        $eventoTable = Database::table('evento');
        $catTable = Database::table('categoria_evento');
        $stmtEventos = $pdo->prepare("
            SELECT e.id, e.titulo, e.descricao, e.data_inicio, e.local, e.dia_inteiro,
                   c.nome as categoria_nome, c.cor as categoria_cor
            FROM `{$eventoTable}` e
            JOIN `{$catTable}` c ON c.id = e.id_categoria
            WHERE e.situacao = 'Confirmado'
            ORDER BY e.data_inicio ASC
            LIMIT 3
        ");
        $stmtEventos->execute();
        $proximosEventos = $stmtEventos->fetchAll();

        // 3. Última atividade
        $stmtUltima = $pdo->prepare("
            SELECT pv.id_video, pv.posicao_atual, pv.percentual, pv.concluido, pv.data_ultima_atividade,
                   v.titulo as video_titulo, v.duracao_segundos,
                   c.descricao as conteudo_descricao,
                   r.descricao as raia_descricao, r.slug as raia_slug
            FROM `{$progressoTable}` pv
            JOIN `{$videoTable}` v ON v.id = pv.id_video
            JOIN `{$conteudoTable}` c ON c.id = v.id_raia_conteudo
            JOIN `{$raiaTable}` r ON r.id = c.id_raia
            WHERE pv.id_usuario = ?
            ORDER BY pv.data_ultima_atividade DESC
            LIMIT 1
        ");
        $stmtUltima->execute([$userId]);
        $ultimaAtividade = $stmtUltima->fetch();

        Response::view('dashboard/index', [
            'pageTitle' => 'Início — Painel Personalizado',
            'user' => $user,
            'raias' => $raiasData,
            'proximosEventos' => $proximosEventos,
            'ultimaAtividade' => $ultimaAtividade
        ]);
    }
}
