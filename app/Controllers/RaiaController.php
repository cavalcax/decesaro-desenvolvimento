<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Response;
use Config\Database;
use PDO;

class RaiaController
{
    public function familia(): void
    {
        $this->renderRaia('familia', 'Família', 'De onde viemos', 'Nossa história, nossos valores e os laços que nos unem para as próximas gerações.');
    }

    public function propriedade(): void
    {
        $this->renderRaia('propriedade', 'Propriedade', 'Nosso papel como acionistas', 'Nossa história, preservar e desenvolver o patrimônio da família.');
    }

    public function negocios(): void
    {
        $this->renderRaia('negocios', 'Negócios', 'Onde estamos e para onde vamos', 'Compreender nossos negócios, sua estratégia e os desafios para o futuro.');
    }

    public function competencias(): void
    {
        $this->renderRaia('competencias-individuais', 'Competências Individuais', 'Competências complementares', 'Desenvolver habilidades que reforçam o seu papel junto à Família, à Propriedade e ao Negócio.');
    }

    private function renderRaia(string $slug, string $title, string $subtitle, string $description): void
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
        $videoTable = Database::table('video');
        $progressoTable = Database::table('progresso_video');

        // Buscar dados da raia
        $stmtR = $pdo->prepare("SELECT id, descricao, subtitulo, icone, slug FROM `{$raiaTable}` WHERE slug = ? LIMIT 1");
        $stmtR->execute([$slug]);
        $raia = $stmtR->fetch();

        if (!$raia) {
            Response::abort(404, "Raia '{$title}' não encontrada.");
        }

        $raiaId = (int)$raia['id'];

        // Buscar conteúdos da raia com verificação de elegibilidade
        $stmtC = $pdo->prepare("
            SELECT c.id, c.descricao, c.subtitulo, c.detalhes, c.imagem, c.ordem,
                   (SELECT COUNT(*) FROM `{$rcnTable}` rcn 
                    JOIN `{$nivelTable}` n ON n.id = rcn.id_nivel 
                    WHERE rcn.id_raia_conteudo = c.id 
                      AND ? >= n.idade_inicial AND ? <= n.idade_final) as is_elegivel
            FROM `{$conteudoTable}` c
            WHERE c.id_raia = ? AND c.ativo = 'S'
            ORDER BY c.ordem ASC
        ");
        $stmtC->execute([$age, $age, $raiaId]);
        $conteudos = $stmtC->fetchAll();

        // Para cada conteúdo, buscar vídeos e progresso do usuário
        $conteudosData = [];
        $totalConteudos = count($conteudos);
        $concluidosCount = 0;
        $elegiveisCount = 0;

        foreach ($conteudos as $c) {
            $isElegivel = ((int)$c['is_elegivel'] > 0) || Auth::isAdmin();
            if ($isElegivel) $elegiveisCount++;

            // Buscar vídeos
            $stmtV = $pdo->prepare("
                SELECT v.id, v.titulo, v.descricao, v.duracao_segundos, v.exclusivo,
                       pv.posicao_atual, pv.percentual, pv.concluido, pv.data_conclusao
                FROM `{$videoTable}` v
                LEFT JOIN `{$progressoTable}` pv ON pv.id_video = v.id AND pv.id_usuario = ?
                WHERE v.id_raia_conteudo = ?
                ORDER BY v.ordem ASC
            ");
            $stmtV->execute([$userId, $c['id']]);
            $videos = $stmtV->fetchAll();

            $totalVideos = count($videos);
            $videosConcluidos = 0;
            foreach ($videos as $v) {
                if ($v['concluido'] === 'S') {
                    $videosConcluidos++;
                }
            }

            $isConcluido = ($totalVideos > 0 && $videosConcluidos === $totalVideos);
            if ($isConcluido && $isElegivel) {
                $concluidosCount++;
            }

            $conteudosData[] = [
                'id' => (int)$c['id'],
                'descricao' => $c['descricao'],
                'subtitulo' => $c['subtitulo'],
                'detalhes' => $c['detalhes'],
                'imagem' => $c['imagem'],
                'is_elegivel' => $isElegivel,
                'total_videos' => $totalVideos,
                'videos_concluidos' => $videosConcluidos,
                'is_concluido' => $isConcluido,
                'videos' => $videos
            ];
        }

        $progressoGeral = $elegiveisCount > 0 ? round(($concluidosCount / $elegiveisCount) * 100) : 0;

        Response::view('raias/show', [
            'pageTitle' => "{$title} — {$subtitle}",
            'raia' => $raia,
            'title' => $title,
            'subtitle' => $subtitle,
            'description' => $description,
            'conteudos' => $conteudosData,
            'progressoGeral' => $progressoGeral,
            'elegiveisCount' => $elegiveisCount,
            'concluidosCount' => $concluidosCount
        ]);
    }

    public function showContent(string $id): void
    {
        Auth::requireAuth();
        $contentId = (int)$id;
        $user = Auth::user();
        $userId = (int)$user['id'];

        if (!Auth::canAccessContent($userId, $contentId)) {
            Response::abort(403, 'Este conteúdo não está disponível para sua faixa etária atual conforme as regras de elegibilidade.');
        }

        $pdo = Database::getConnection();
        $conteudoTable = Database::table('raia_conteudo');
        $raiaTable = Database::table('raia');
        $videoTable = Database::table('video');
        $progressoTable = Database::table('progresso_video');

        $stmtC = $pdo->prepare("
            SELECT c.*, r.descricao as raia_descricao, r.slug as raia_slug
            FROM `{$conteudoTable}` c
            JOIN `{$raiaTable}` r ON r.id = c.id_raia
            WHERE c.id = ? AND c.ativo = 'S'
            LIMIT 1
        ");
        $stmtC->execute([$contentId]);
        $conteudo = $stmtC->fetch();

        if (!$conteudo) {
            Response::abort(404, 'Conteúdo não encontrado.');
        }

        // Buscar vídeos do conteúdo
        $stmtV = $pdo->prepare("
            SELECT v.*, pv.posicao_atual, pv.percentual, pv.concluido
            FROM `{$videoTable}` v
            LEFT JOIN `{$progressoTable}` pv ON pv.id_video = v.id AND pv.id_usuario = ?
            WHERE v.id_raia_conteudo = ?
            ORDER BY v.ordem ASC
        ");
        $stmtV->execute([$userId, $contentId]);
        $videos = $stmtV->fetchAll();

        Response::view('raias/content', [
            'pageTitle' => $conteudo['descricao'] . ' — ' . $conteudo['raia_descricao'],
            'conteudo' => $conteudo,
            'videos' => $videos
        ]);
    }
}
