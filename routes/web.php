<?php

declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AgendaController;
use App\Controllers\ApiController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\JornadaController;
use App\Controllers\PerfilController;
use App\Controllers\RaiaController;
use App\Controllers\VideoController;
use App\Helpers\Router;

// ==========================================
// Rotas de Autenticação
// ==========================================
Router::get('/login', [AuthController::class, 'showLogin']);
Router::post('/login', [AuthController::class, 'login']);
Router::get('/logout', [AuthController::class, 'logout']);
Router::post('/logout', [AuthController::class, 'logout']);
Router::get('/recuperar-senha', [AuthController::class, 'showForgot']);
Router::post('/recuperar-senha', [AuthController::class, 'sendForgot']);
Router::get('/redefinir-senha', [AuthController::class, 'showReset']);
Router::post('/redefinir-senha', [AuthController::class, 'resetPassword']);

// ==========================================
// Rotas Principais dos Participantes (8 Áreas)
// ==========================================
Router::get('/', [DashboardController::class, 'index']);
Router::get('/inicio', [DashboardController::class, 'index']);

Router::get('/familia', [RaiaController::class, 'familia']);
Router::get('/propriedade', [RaiaController::class, 'propriedade']);
Router::get('/negocios', [RaiaController::class, 'negocios']);
Router::get('/competencias-individuais', [RaiaController::class, 'competencias']);
Router::get('/outros-skills', [RaiaController::class, 'competencias']);

Router::get('/jornada', [JornadaController::class, 'index']);
Router::get('/minha-jornada', [JornadaController::class, 'index']);

Router::get('/agenda', [AgendaController::class, 'index']);

Router::get('/perfil', [PerfilController::class, 'index']);
Router::get('/meu-perfil', [PerfilController::class, 'index']);
Router::post('/perfil/atualizar', [PerfilController::class, 'update']);
Router::post('/perfil/senha', [PerfilController::class, 'changePassword']);
Router::post('/perfil/preferencias', [PerfilController::class, 'savePreferences']);

// ==========================================
// Conteúdos e Player de Vídeo
// ==========================================
Router::get('/conteudo/{id}', [RaiaController::class, 'showContent']);
Router::get('/video/{id}', [VideoController::class, 'show']);

// ==========================================
// APIs AJAX para o Frontend
// ==========================================
Router::get('/api/me', [ApiController::class, 'me']);
Router::post('/api/video-progress', [ApiController::class, 'saveVideoProgress']);
Router::post('/api/video-complete', [ApiController::class, 'markVideoComplete']);
Router::post('/api/preferencias', [ApiController::class, 'preferencias']);
Router::get('/api/agenda/eventos', [AgendaController::class, 'apiEventos']);
Router::post('/api/agenda/inscrever', [AgendaController::class, 'apiInscrever']);
Router::post('/api/agenda/cancelar-inscricao', [AgendaController::class, 'apiCancelarInscricao']);

// Notificações e Web Push
Router::get('/api/notificacoes', [ApiController::class, 'notificacoes']);
Router::post('/api/notificacoes/ler', [ApiController::class, 'notificacaoMarcarLida']);
Router::post('/api/notificacoes/ler-todas', [ApiController::class, 'notificacaoMarcarTodasLidas']);
Router::get('/api/push/public-key', [ApiController::class, 'pushPublicKey']);
Router::post('/api/push/subscribe', [ApiController::class, 'pushSubscribe']);
Router::post('/api/push/unsubscribe', [ApiController::class, 'pushUnsubscribe']);
Router::post('/api/push/test', [ApiController::class, 'pushTest']);

// ==========================================
// Área Administrativa (Comitê de Desenvolvimento)
// ==========================================
Router::get('/admin', [AdminController::class, 'dashboard']);

// CRUD Participantes
Router::get('/admin/participantes', [AdminController::class, 'participantes']);
Router::post('/admin/participantes/salvar', [AdminController::class, 'participanteSalvar']);
Router::post('/admin/participantes/excluir', [AdminController::class, 'participanteExcluir']);

// CRUD Níveis
Router::get('/admin/niveis', [AdminController::class, 'niveis']);
Router::post('/admin/niveis/salvar', [AdminController::class, 'nivelSalvar']);
Router::post('/admin/niveis/excluir', [AdminController::class, 'nivelExcluir']);

// CRUD Raias
Router::get('/admin/raias', [AdminController::class, 'raias']);
Router::post('/admin/raias/salvar', [AdminController::class, 'raiaSalvar']);
Router::post('/admin/raias/excluir', [AdminController::class, 'raiaExcluir']);

// CRUD Conteúdos
Router::get('/admin/conteudos', [AdminController::class, 'conteudos']);
Router::post('/admin/conteudos/salvar', [AdminController::class, 'conteudoSalvar']);
Router::post('/admin/conteudos/excluir', [AdminController::class, 'conteudoExcluir']);

// CRUD Vídeos
Router::get('/admin/videos', [AdminController::class, 'videos']);
Router::post('/admin/videos/salvar', [AdminController::class, 'videoSalvar']);
Router::post('/admin/videos/excluir', [AdminController::class, 'videoExcluir']);

// CRUD Agenda e Eventos
Router::get('/admin/agenda', [AdminController::class, 'agenda']);
Router::post('/admin/agenda/salvar', [AdminController::class, 'agendaSalvar']);
Router::get('/admin/agenda/inscritos/{id}', [AdminController::class, 'agendaInscritos']);
Router::post('/admin/agenda/presenca', [AdminController::class, 'agendaMarcarPresenca']);

// Central de Notificações e Push
Router::get('/admin/notificacoes', [AdminController::class, 'notificacoes']);
Router::post('/admin/notificacoes/enviar', [AdminController::class, 'notificacaoEnviar']);

// Relatórios e Auditoria
Router::get('/admin/relatorios', [AdminController::class, 'relatorios']);
Router::get('/admin/auditoria', [AdminController::class, 'auditoria']);
