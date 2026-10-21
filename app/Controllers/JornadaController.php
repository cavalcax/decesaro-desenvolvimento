<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Response;
use Config\Database;
use PDO;

class JornadaController
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
        $videoTable = Database::table('video');
        $progressoTable = Database::table('progresso_video');

        // Buscar raias com progresso detalhado
        $stmtR = $pdo->query("SELECT id, descricao, subtitulo, icone, slug, idade_minima, idade_maxima FROM `{$raiaTable}` ORDER BY ordem ASC");
        $raias = $stmtR->fetchAll();

        $raiasProgress = [];
        $totalVideosGerais = 0;
        $totalVideosConcluidos = 0;
        $tempoTotalAssistido = 0;

        foreach ($raias as $r) {
            $raiaId = (int)$r['id'];
            $isAcessivel = Auth::canAccessRaia($userId, $raiaId);

            // Conteúdos da raia
            $stmtC = $pdo->prepare("
                SELECT c.id, c.descricao
                FROM `{$conteudoTable}` c
                WHERE c.id_raia = ? AND c.ativo = 'S'
                ORDER BY c.ordem ASC
            ");
            $stmtC->execute([$raiaId]);
            $conteudos = $stmtC->fetchAll();

            $totalC = count($conteudos);
            $concluidosC = 0;
            $elegiveisC = 0;

            if ($isAcessivel) {
                $elegiveisC = $totalC;

                foreach ($conteudos as $c) {
                    // Vídeos do conteúdo
                    $stmtV = $pdo->prepare("
                        SELECT COUNT(v.id) as total_v,
                               COUNT(pv.id) as done_v,
                               SUM(pv.tempo_assistido) as tempo_v
                        FROM `{$videoTable}` v
                        LEFT JOIN `{$progressoTable}` pv ON pv.id_video = v.id AND pv.id_usuario = ? AND pv.concluido = 'S'
                        WHERE v.id_raia_conteudo = ?
                    ");
                    $stmtV->execute([$userId, $c['id']]);
                    $vStats = $stmtV->fetch();

                    if (!empty($vStats['total_v']) && $vStats['total_v'] == $vStats['done_v']) {
                        $concluidosC++;
                    }

                    $totalVideosGerais += (int)($vStats['total_v'] ?? 0);
                    $totalVideosConcluidos += (int)($vStats['done_v'] ?? 0);
                    $tempoTotalAssistido += (int)($vStats['tempo_v'] ?? 0);
                }
            }

            $percent = $elegiveisC > 0 ? round(($concluidosC / $elegiveisC) * 100) : 0;
            $statusText = !$isAcessivel ? 'Bloqueada' : ($percent == 100 ? 'Concluída' : ($percent > 0 ? 'Em andamento' : 'Não iniciado'));

            $raiasProgress[] = [
                'id' => $raiaId,
                'descricao' => $r['descricao'],
                'subtitulo' => $r['subtitulo'],
                'icone' => $r['icone'],
                'slug' => $r['slug'],
                'total_conteudos' => $totalC,
                'elegiveis' => $elegiveisC,
                'concluidos' => $concluidosC,
                'percentual' => $percent,
                'status' => $statusText
            ];
        }

        // Histórico de vídeos assistidos/concluídos
        $stmtHist = $pdo->prepare("
            SELECT pv.*, v.titulo as video_titulo, v.duracao_segundos,
                   c.descricao as conteudo_descricao,
                   r.descricao as raia_descricao, r.slug as raia_slug
            FROM `{$progressoTable}` pv
            JOIN `{$videoTable}` v ON v.id = pv.id_video
            JOIN `{$conteudoTable}` c ON c.id = v.id_raia_conteudo
            JOIN `{$raiaTable}` r ON r.id = c.id_raia
            WHERE pv.id_usuario = ?
            ORDER BY pv.data_ultima_atividade DESC
            LIMIT 10
        ");
        $stmtHist->execute([$userId]);
        $historico = $stmtHist->fetchAll();

        Response::view('jornada/index', [
            'pageTitle' => 'Minha Jornada — Meu Desenvolvimento',
            'user' => $user,
            'raias' => $raiasProgress,
            'historico' => $historico,
            'totalVideosGerais' => $totalVideosGerais,
            'totalVideosConcluidos' => $totalVideosConcluidos,
            'tempoTotalAssistido' => $tempoTotalAssistido
        ]);
    }
}
