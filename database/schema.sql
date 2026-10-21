-- ========================================================
-- PLATAFORMA DE DESENVOLVIMENTO DA FAMÍLIA DE CESARO
-- LEGADO E FUTURO
-- Schema de Banco de Dados (MySQL 8 / InnoDB / utf8mb4)
-- Prefixo padrão: ctdesenvolvimento_decesaro_
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Tabela: Usuario
CREATE TABLE IF NOT EXISTS `ctdesenvolvimento_decesaro_usuario` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(150) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `senha` VARCHAR(255) NOT NULL,
  `data_nascimento` DATE NOT NULL,
  `tipo` ENUM('Normal', 'Admin') NOT NULL DEFAULT 'Normal',
  `familia` ENUM('Nestor', 'Zeca', 'Augusto', 'Wilson', 'Marisa', 'Nane', 'Tati') NOT NULL,
  `geracao` VARCHAR(50) NOT NULL,
  `foto` VARCHAR(255) NULL,
  `status` ENUM('Ativo', 'Inativo') NOT NULL DEFAULT 'Ativo',
  `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usuario_email` (`email`),
  KEY `ix_usuario_tipo_status` (`tipo`, `status`),
  KEY `ix_usuario_familia` (`familia`),
  KEY `ix_usuario_geracao` (`geracao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabela: Nivel
CREATE TABLE IF NOT EXISTS `ctdesenvolvimento_decesaro_nivel` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descricao` VARCHAR(120) NOT NULL,
  `idade_inicial` INT NOT NULL,
  `idade_final` INT NOT NULL,
  `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_nivel_idades` (`idade_inicial`, `idade_final`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabela: Raia
CREATE TABLE IF NOT EXISTS `ctdesenvolvimento_decesaro_raia` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descricao` VARCHAR(100) NOT NULL,
  `subtitulo` VARCHAR(255) NULL,
  `icone` VARCHAR(50) NOT NULL DEFAULT 'fa-folder',
  `slug` VARCHAR(50) NOT NULL,
  `ordem` INT NOT NULL DEFAULT 0,
  `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_raia_slug` (`slug`),
  KEY `ix_raia_ordem` (`ordem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabela: RaiaConteudo
CREATE TABLE IF NOT EXISTS `ctdesenvolvimento_decesaro_raia_conteudo` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_raia` INT UNSIGNED NOT NULL,
  `descricao` VARCHAR(150) NOT NULL,
  `subtitulo` VARCHAR(255) NULL,
  `detalhes` TEXT NULL,
  `imagem` VARCHAR(255) NULL,
  `ordem` INT NOT NULL DEFAULT 0,
  `ativo` CHAR(1) NOT NULL DEFAULT 'S',
  `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_conteudo_raia_ordem` (`id_raia`, `ordem`),
  CONSTRAINT `fk_conteudo_raia` FOREIGN KEY (`id_raia`) REFERENCES `ctdesenvolvimento_decesaro_raia` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabela: RaiaConteudoNivel
CREATE TABLE IF NOT EXISTS `ctdesenvolvimento_decesaro_raia_conteudo_nivel` (
  `id_raia_conteudo` INT UNSIGNED NOT NULL,
  `id_nivel` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id_raia_conteudo`, `id_nivel`),
  KEY `ix_rcn_nivel` (`id_nivel`),
  CONSTRAINT `fk_rcn_conteudo` FOREIGN KEY (`id_raia_conteudo`) REFERENCES `ctdesenvolvimento_decesaro_raia_conteudo` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rcn_nivel` FOREIGN KEY (`id_nivel`) REFERENCES `ctdesenvolvimento_decesaro_nivel` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabela: Video
CREATE TABLE IF NOT EXISTS `ctdesenvolvimento_decesaro_video` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(200) NOT NULL,
  `descricao` TEXT NULL,
  `link_arquivo` VARCHAR(500) NOT NULL,
  `id_raia_conteudo` INT UNSIGNED NOT NULL,
  `exclusivo` ENUM('Sim', 'Não') NOT NULL DEFAULT 'Sim',
  `duracao_segundos` INT UNSIGNED NOT NULL DEFAULT 0,
  `ordem` INT NOT NULL DEFAULT 0,
  `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_video_conteudo_ordem` (`id_raia_conteudo`, `ordem`),
  KEY `ix_video_exclusivo` (`exclusivo`),
  CONSTRAINT `fk_video_conteudo` FOREIGN KEY (`id_raia_conteudo`) REFERENCES `ctdesenvolvimento_decesaro_raia_conteudo` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tabela: ProgressoVideo
CREATE TABLE IF NOT EXISTS `ctdesenvolvimento_decesaro_progresso_video` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_usuario` INT UNSIGNED NOT NULL,
  `id_video` INT UNSIGNED NOT NULL,
  `posicao_atual` INT UNSIGNED NOT NULL DEFAULT 0,
  `tempo_assistido` INT UNSIGNED NOT NULL DEFAULT 0,
  `percentual` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `concluido` CHAR(1) NOT NULL DEFAULT 'N',
  `data_inicio` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_conclusao` DATETIME NULL,
  `data_ultima_atividade` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `metodo_conclusao` ENUM('automatico', 'manual') NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_progresso_usuario_video` (`id_usuario`, `id_video`),
  KEY `ix_progresso_video` (`id_video`),
  KEY `ix_progresso_usuario_concluido` (`id_usuario`, `concluido`),
  CONSTRAINT `fk_progresso_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `ctdesenvolvimento_decesaro_usuario` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_progresso_video` FOREIGN KEY (`id_video`) REFERENCES `ctdesenvolvimento_decesaro_video` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Tabela: SessaoReproducao
CREATE TABLE IF NOT EXISTS `ctdesenvolvimento_decesaro_sessao_reproducao` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_usuario` INT UNSIGNED NOT NULL,
  `id_video` INT UNSIGNED NOT NULL,
  `inicio` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fim` DATETIME NULL,
  `segundos_assistidos` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `ix_sessao_usuario_video` (`id_usuario`, `id_video`),
  KEY `ix_sessao_inicio` (`inicio`),
  CONSTRAINT `fk_sessao_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `ctdesenvolvimento_decesaro_usuario` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sessao_video` FOREIGN KEY (`id_video`) REFERENCES `ctdesenvolvimento_decesaro_video` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Tabela: RecuperacaoSenha
CREATE TABLE IF NOT EXISTS `ctdesenvolvimento_decesaro_recuperacao_senha` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_usuario` INT UNSIGNED NOT NULL,
  `token_hash` VARCHAR(64) NOT NULL,
  `expira_em` DATETIME NOT NULL,
  `utilizado` CHAR(1) NOT NULL DEFAULT 'N',
  `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_recup_token` (`token_hash`),
  KEY `ix_recup_usuario` (`id_usuario`),
  CONSTRAINT `fk_recup_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `ctdesenvolvimento_decesaro_usuario` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Tabela: TentativaLogin
CREATE TABLE IF NOT EXISTS `ctdesenvolvimento_decesaro_tentativa_login` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip` VARCHAR(45) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `data_hora` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sucesso` CHAR(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`id`),
  KEY `ix_tentativa_ip_email` (`ip`, `email`, `data_hora`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Tabela: BloqueioTemporario
CREATE TABLE IF NOT EXISTS `ctdesenvolvimento_decesaro_bloqueio_temporario` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip` VARCHAR(45) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `bloqueado_ate` DATETIME NOT NULL,
  `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_bloqueio_ativo` (`ip`, `email`, `bloqueado_ate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Tabela: AuditoriaLog
CREATE TABLE IF NOT EXISTS `ctdesenvolvimento_decesaro_auditoria_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_usuario` INT UNSIGNED NULL,
  `acao` VARCHAR(80) NOT NULL,
  `entidade` VARCHAR(60) NOT NULL,
  `entidade_id` INT UNSIGNED NULL,
  `dados_anteriores` JSON NULL,
  `dados_novos` JSON NULL,
  `ip` VARCHAR(45) NULL,
  `data_hora` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_auditoria_entidade` (`entidade`, `entidade_id`),
  KEY `ix_auditoria_usuario` (`id_usuario`, `data_hora`),
  CONSTRAINT `fk_auditoria_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `ctdesenvolvimento_decesaro_usuario` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Tabela: LogAcesso
CREATE TABLE IF NOT EXISTS `ctdesenvolvimento_decesaro_log_acesso` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_usuario` INT UNSIGNED NOT NULL,
  `ip` VARCHAR(45) NOT NULL,
  `user_agent` VARCHAR(255) NULL,
  `data_hora` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_log_acesso_usuario` (`id_usuario`, `data_hora`),
  CONSTRAINT `fk_log_acesso_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `ctdesenvolvimento_decesaro_usuario` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Tabela: PreferenciaUsuario
CREATE TABLE IF NOT EXISTS `ctdesenvolvimento_decesaro_preferencia_usuario` (
  `id_usuario` INT UNSIGNED NOT NULL,
  `tema` ENUM('claro', 'escuro') NOT NULL DEFAULT 'claro',
  `notificacoes_email` CHAR(1) NOT NULL DEFAULT 'S',
  `atualizado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  CONSTRAINT `fk_pref_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `ctdesenvolvimento_decesaro_usuario` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Tabela: CategoriaEvento
CREATE TABLE IF NOT EXISTS `ctdesenvolvimento_decesaro_categoria_evento` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(80) NOT NULL,
  `cor` VARCHAR(30) NOT NULL DEFAULT '#1B3B5F',
  `descricao` VARCHAR(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Tabela: Evento
CREATE TABLE IF NOT EXISTS `ctdesenvolvimento_decesaro_evento` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(200) NOT NULL,
  `descricao` TEXT NULL,
  `id_categoria` INT UNSIGNED NOT NULL,
  `data_inicio` DATETIME NOT NULL,
  `data_fim` DATETIME NULL,
  `dia_inteiro` CHAR(1) NOT NULL DEFAULT 'N',
  `local` VARCHAR(255) NULL,
  `link_online` VARCHAR(500) NULL,
  `responsavel` VARCHAR(150) NULL,
  `situacao` ENUM('Confirmado', 'Pendente', 'Cancelado') NOT NULL DEFAULT 'Confirmado',
  `visibilidade` ENUM('Todos', 'Admin', 'Familia', 'Geracao', 'Especifico') NOT NULL DEFAULT 'Todos',
  `limite_vagas` INT UNSIGNED NULL,
  `id_raia_conteudo` INT UNSIGNED NULL,
  `id_video` INT UNSIGNED NULL,
  `criado_por` INT UNSIGNED NULL,
  `atualizado_por` INT UNSIGNED NULL,
  `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_evento_categoria` (`id_categoria`),
  KEY `ix_evento_inicio_fim` (`data_inicio`, `data_fim`),
  KEY `ix_evento_situacao` (`situacao`),
  KEY `ix_evento_visibilidade` (`visibilidade`),
  CONSTRAINT `fk_evento_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `ctdesenvolvimento_decesaro_categoria_evento` (`id`),
  CONSTRAINT `fk_evento_conteudo` FOREIGN KEY (`id_raia_conteudo`) REFERENCES `ctdesenvolvimento_decesaro_raia_conteudo` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_evento_video` FOREIGN KEY (`id_video`) REFERENCES `ctdesenvolvimento_decesaro_video` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_evento_criador` FOREIGN KEY (`criado_por`) REFERENCES `ctdesenvolvimento_decesaro_usuario` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Tabela: EventoPublicoAlvo
CREATE TABLE IF NOT EXISTS `ctdesenvolvimento_decesaro_evento_publico_alvo` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_evento` INT UNSIGNED NOT NULL,
  `tipo_criterio` ENUM('familia', 'geracao', 'usuario', 'idade_min') NOT NULL,
  `valor_criterio` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_publico_evento` (`id_evento`),
  CONSTRAINT `fk_publico_evento` FOREIGN KEY (`id_evento`) REFERENCES `ctdesenvolvimento_decesaro_evento` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Tabela: InscricaoEvento
CREATE TABLE IF NOT EXISTS `ctdesenvolvimento_decesaro_inscricao_evento` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_evento` INT UNSIGNED NOT NULL,
  `id_usuario` INT UNSIGNED NOT NULL,
  `status` ENUM('inscrito', 'cancelado', 'fila_espera') NOT NULL DEFAULT 'inscrito',
  `presenca` CHAR(1) NOT NULL DEFAULT 'N',
  `presenca_registrada_em` DATETIME NULL,
  `presenca_registrada_por` INT UNSIGNED NULL,
  `data_inscricao` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_inscricao_evento_usuario` (`id_evento`, `id_usuario`),
  KEY `ix_inscricao_usuario` (`id_usuario`),
  CONSTRAINT `fk_inscricao_evento` FOREIGN KEY (`id_evento`) REFERENCES `ctdesenvolvimento_decesaro_evento` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_inscricao_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `ctdesenvolvimento_decesaro_usuario` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. Tabela: NotificacaoLembrete
CREATE TABLE IF NOT EXISTS `ctdesenvolvimento_decesaro_notificacao_lembrete` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_evento` INT UNSIGNED NOT NULL,
  `id_usuario` INT UNSIGNED NOT NULL,
  `tipo` ENUM('email', 'sistema') NOT NULL DEFAULT 'email',
  `status` ENUM('pendente', 'enviado', 'falha') NOT NULL DEFAULT 'pendente',
  `agendado_para` DATETIME NOT NULL,
  `enviado_em` DATETIME NULL,
  `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_lembrete_status_agendado` (`status`, `agendado_para`),
  CONSTRAINT `fk_lembrete_evento` FOREIGN KEY (`id_evento`) REFERENCES `ctdesenvolvimento_decesaro_evento` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_lembrete_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `ctdesenvolvimento_decesaro_usuario` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
