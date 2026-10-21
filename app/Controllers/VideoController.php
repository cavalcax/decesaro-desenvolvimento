<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Response;
use Config\Database;
use Config\SecurityConfig;
use PDO;

class VideoController
{
    public function show(string $id): void
    {
        Auth::requireAuth();
        $videoId = (int)$id;
        $user = Auth::user();
        $userId = (int)$user['id'];

        if (!Auth::canAccessVideo($userId, $videoId)) {
            Response::abort(403, 'Acesso restrito. Este vídeo exige nível de elegibilidade superior ao da sua faixa etária.');
        }

        $pdo = Database::getConnection();
        $videoTable = Database::table('video');
        $conteudoTable = Database::table('raia_conteudo');
        $raiaTable = Database::table('raia');
        $progressoTable = Database::table('progresso_video');

        // Buscar vídeo
        $stmtV = $pdo->prepare("
            SELECT v.*, c.descricao as conteudo_descricao, c.id_raia,
                   r.descricao as raia_descricao, r.slug as raia_slug
            FROM `{$videoTable}` v
            JOIN `{$conteudoTable}` c ON c.id = v.id_raia_conteudo
            JOIN `{$raiaTable}` r ON r.id = c.id_raia
            WHERE v.id = ?
            LIMIT 1
        ");
        $stmtV->execute([$videoId]);
        $video = $stmtV->fetch();

        if (!$video) {
            Response::abort(404, 'Vídeo não encontrado.');
        }

        // Buscar progresso atual do usuário
        $stmtP = $pdo->prepare("SELECT posicao_atual, tempo_assistido, percentual, concluido FROM `{$progressoTable}` WHERE id_usuario = ? AND id_video = ? LIMIT 1");
        $stmtP->execute([$userId, $videoId]);
        $progresso = $stmtP->fetch() ?: [
            'posicao_atual' => 0,
            'tempo_assistido' => 0,
            'percentual' => 0.0,
            'concluido' => 'N'
        ];

        // Buscar outros vídeos do mesmo conteúdo para lista lateral
        $stmtOutros = $pdo->prepare("
            SELECT v.id, v.titulo, v.duracao_segundos, v.exclusivo,
                   pv.concluido
            FROM `{$videoTable}` v
            LEFT JOIN `{$progressoTable}` pv ON pv.id_video = v.id AND pv.id_usuario = ?
            WHERE v.id_raia_conteudo = ?
            ORDER BY v.ordem ASC
        ");
        $stmtOutros->execute([$userId, $video['id_raia_conteudo']]);
        $playlist = $stmtOutros->fetchAll();

        Response::view('raias/video', [
            'pageTitle' => $video['titulo'] . ' — ' . $video['raia_descricao'],
            'video' => $video,
            'progresso' => $progresso,
            'playlist' => $playlist,
            'completionThreshold' => SecurityConfig::videoCompletionThreshold(),
            'syncInterval' => SecurityConfig::videoTrackingInterval()
        ]);
    }
}
