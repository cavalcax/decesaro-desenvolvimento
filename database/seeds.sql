-- ========================================================
-- PLATAFORMA DE DESENVOLVIMENTO DA FAMÍLIA DE CESARO
-- LEGADO E FUTURO
-- Seeds Iniciais (Compatíveis com o layout de referência)
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Raias
INSERT INTO `ctdesenvolvimento_decesaro_raia` (`id`, `descricao`, `subtitulo`, `icone`, `slug`, `ordem`) VALUES
(1, 'Família', 'De onde viemos', 'fa-users', 'familia', 1),
(2, 'Propriedade', 'Nosso papel como acionistas', 'fa-landmark', 'propriedade', 2),
(3, 'Negócios', 'Onde estamos e para onde vamos', 'fa-briefcase', 'negocios', 3),
(4, 'Competências Individuais', 'Competências complementares', 'fa-lightbulb', 'competencias-individuais', 4)
ON DUPLICATE KEY UPDATE `descricao` = VALUES(`descricao`), `subtitulo` = VALUES(`subtitulo`), `icone` = VALUES(`icone`);

-- 2. Níveis
INSERT INTO `ctdesenvolvimento_decesaro_nivel` (`id`, `descricao`, `idade_inicial`, `idade_final`) VALUES
(1, 'Introdução & Primeiros Passos (0 a 15 anos)', 0, 15),
(2, 'Juventude & Formação Inicial (16 a 20 anos)', 16, 20),
(3, 'Governança & Formação Acionária (21 a 35 anos)', 21, 35),
(4, 'Liderança, Patrimônio & Estratégia (36+ anos)', 36, 120)
ON DUPLICATE KEY UPDATE `descricao` = VALUES(`descricao`), `idade_inicial` = VALUES(`idade_inicial`), `idade_final` = VALUES(`idade_final`);

-- 3. Conteúdos das Raias (Conforme Mapa de Telas)
INSERT INTO `ctdesenvolvimento_decesaro_raia_conteudo` (`id`, `id_raia`, `descricao`, `subtitulo`, `detalhes`, `ordem`, `ativo`) VALUES
-- Raia 1: Família
(1, 1, 'Nossa história', 'A trajetória da família De Cesaro', 'Origens, memórias e momentos marcantes que construíram nossa união e valores.', 1, 'S'),
(2, 1, 'Valores e cultura', 'O que nos guia', 'Princípios éticos, respeito mútuo, trabalho e perpetuação do legado familiar.', 2, 'S'),
(3, 1, 'Convivência familiar', 'Fortalecendo vínculos', 'Encontros, comunicação intergeracional e dinâmicas de acolhimento e escuta.', 3, 'S'),
(4, 1, 'Ancestralidade e legado', 'Nossas raízes, nosso futuro', 'A história dos pioneiros e a responsabilidade de passar o bastão para as próximas gerações.', 4, 'S'),
(5, 1, 'Governança familiar', 'Disponível conforme elegibilidade', 'Protocolo familiar, conselho de família e mecanismos de tomada de decisão colegiada.', 5, 'S'),

-- Raia 2: Propriedade
(6, 2, 'O que significa ser acionista', 'Direitos e responsabilidades', 'Conceito de cotista e acionista, direitos societários e deveres para com o patrimônio.', 1, 'S'),
(7, 2, 'Educação patrimonial', 'Planejamento de longo prazo', 'Preservação de capital, diversificação e entendimento de valor de longo prazo.', 2, 'S'),
(8, 2, 'Governança corporativa', 'Estruturas e comitês', 'O papel do conselho de administração, auditoria e comitês de apoio.', 3, 'S'),
(9, 2, 'Direito societário', 'Conceitos fundamentais', 'Contrato social, acordos de sócios e proteção patrimonial.', 4, 'S'),
(10, 2, 'Participação em comitês', 'Disponível conforme elegibilidade', 'Como integrar comitês temáticos de desenvolvimento e governança.', 5, 'S'),

-- Raia 3: Negócios
(11, 3, 'Nossos negócios', 'Varejo, atacarejo, atacado e logística', 'Visão geral das unidades de negócio, operações e cadeia de suprimentos.', 1, 'S'),
(12, 3, 'Estratégia e mercado', 'Cenário e oportunidades', 'Posicionamento competitivo, tendências de mercado e crescimento sustentável.', 2, 'S'),
(13, 3, 'Gestão de pessoas', 'Liderança e cultura', 'Gestão de equipes, valorização de talentos e clima organizacional.', 3, 'S'),
(14, 3, 'Indicadores e resultados', 'Entendendo o desempenho', 'DRE, margens, EBITDA, fluxo de caixa e metas orçamentárias.', 4, 'S'),
(15, 3, 'Participamento estratégico', 'Disponível conforme elegibilidade', 'Como colaborar em discussões estratégicas e planejamento plurianual.', 5, 'S'),

-- Raia 4: Competências Individuais
(16, 4, 'Comunicação', 'Oratória e comunicação ativa', 'Comunicação clara, assertividade e apresentação de ideias.', 1, 'S'),
(17, 4, 'Inovação e tecnologia', 'Inovação e IA', 'Ferramentas digitais, produtividade, inteligência artificial e novas tendências.', 2, 'S'),
(18, 4, 'Negociação', 'Negociações eficazes', 'Métodos de resolução colaborativa, negociação ganha-ganha e persuasão ética.', 3, 'S'),
(19, 4, 'ESG e sustentabilidade', 'Construindo um futuro responsável', 'Práticas ambientais, sociais e de governança integradas aos negócios.', 4, 'S'),
(20, 4, 'Inteligência emocional', 'Disponível conforme elegibilidade', 'Autoconhecimento, autorregulação e empatia no ambiente familiar e corporativo.', 5, 'S')
ON DUPLICATE KEY UPDATE `descricao` = VALUES(`descricao`), `subtitulo` = VALUES(`subtitulo`), `detalhes` = VALUES(`detalhes`);

-- 4. Associação RaiaConteudoNivel (Elegibilidade)
-- Conteúdos 1 a 4 (Família) para Níveis 1, 2, 3, 4
INSERT INTO `ctdesenvolvimento_decesaro_raia_conteudo_nivel` (`id_raia_conteudo`, `id_nivel`) VALUES
(1, 1), (1, 2), (1, 3), (1, 4),
(2, 1), (2, 2), (2, 3), (2, 4),
(3, 1), (3, 2), (3, 3), (3, 4),
(4, 1), (4, 2), (4, 3), (4, 4),
-- Conteúdo 5 (Governança familiar) restrito a Níveis 3 e 4 (21+ anos)
(5, 3), (5, 4),
-- Propriedade (Conteúdos 6 a 9 liberados para 16+ anos: Níveis 2, 3, 4)
(6, 2), (6, 3), (6, 4),
(7, 2), (7, 3), (7, 4),
(8, 2), (8, 3), (8, 4),
(9, 2), (9, 3), (9, 4),
-- Conteúdo 10 (Participação em comitês) restrito a 21+ anos: Níveis 3, 4
(10, 3), (10, 4),
-- Negócios (Conteúdos 11 a 14 liberados para 16+ anos: Níveis 2, 3, 4)
(11, 2), (11, 3), (11, 4),
(12, 2), (12, 3), (12, 4),
(13, 2), (13, 3), (13, 4),
(14, 2), (14, 3), (14, 4),
-- Conteúdo 15 restrito a Níveis 3 e 4
(15, 3), (15, 4),
-- Competências Individuais (16 a 19 liberados para Níveis 2, 3, 4)
(16, 2), (16, 3), (16, 4),
(17, 2), (17, 3), (17, 4),
(18, 2), (18, 3), (18, 4),
(19, 2), (19, 3), (19, 4),
-- Conteúdo 20 restrito a Níveis 3 e 4
(20, 3), (20, 4)
ON DUPLICATE KEY UPDATE `id_raia_conteudo` = VALUES(`id_raia_conteudo`);

-- 5. Vídeos Educativos com Links HTML5
INSERT INTO `ctdesenvolvimento_decesaro_video` (`id`, `titulo`, `descricao`, `link_arquivo`, `id_raia_conteudo`, `exclusivo`, `duracao_segundos`, `ordem`) VALUES
(1, 'Origens e Primeiras Conquistas', 'Documentário sobre a imigração e as primeiras realizações dos fundadores da família De Cesaro.', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4', 1, 'Sim', 360, 1),
(2, 'O Pilar dos Nossos Valores', 'Depoimentos sobre honestidade, trabalho duro e união familiar ao longo das décadas.', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4', 2, 'Sim', 420, 1),
(3, 'Vínculos que Perduram', 'Guia prático para a convivência saudável e colaboração mútua entre diferentes gerações.', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerFun.mp4', 3, 'Não', 300, 1),
(4, 'Do Passado ao Futuro', 'Visão dos patriarcas e a ponte para as novas gerações da família.', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyBlazes.mp4', 4, 'Sim', 480, 1),
(5, 'Introdução ao Papel de Acionista', 'Fundamentos de responsabilidade societária e compromisso com o patrimônio.', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerMeltdowns.mp4', 6, 'Sim', 540, 1),
(6, 'Planejamento e Gestão Patrimonial', 'Como proteger ativos, gerir riscos e assegurar a perpetuidade do capital familiar.', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/Sintel.mp4', 7, 'Sim', 600, 1),
(7, 'Panorama dos Nossos Negócios', 'Apresentação detalhada das operações de atacado, varejo e logística do grupo.', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/SubaruOutbackSeeTheWorld.mp4', 11, 'Sim', 720, 1),
(8, 'Estratégia e Visão de Futuro', 'Análise de mercado, diferenciais competitivos e projetos de expansão.', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4', 12, 'Sim', 660, 1),
(9, 'Comunicação Interpessoal e Oratória', 'Técnicas de expressão oral, escuta ativa e estruturação de mensagens de impacto.', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/WeAreGoingOnBullrun.mp4', 16, 'Não', 450, 1),
(10, 'Inovação e Ferramentas Digitais', 'Adoção de inteligência artificial e modernização dos processos corporativos.', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/WhatCarCanYouGetForAGrand.mp4', 17, 'Sim', 510, 1),
(11, 'Vídeo Institucional da Família De Cesaro', 'Vídeo institucional da Família De Cesaro hospedado no Google Drive, apresentando a trajetória, memórias e legado da família.', 'https://drive.google.com/file/d/1BYwZ3kiA9KEhRUpBkLbEsHi5eK6qMozv/view?usp=drive_link', 1, 'Não', 480, 2)
ON DUPLICATE KEY UPDATE `titulo` = VALUES(`titulo`), `link_arquivo` = VALUES(`link_arquivo`);

-- 6. Categorias de Eventos
INSERT INTO `ctdesenvolvimento_decesaro_categoria_evento` (`id`, `nome`, `cor`, `descricao`) VALUES
(1, 'Encontro da Família', '#2563EB', 'Reuniões e celebrações de integração de toda a família De Cesaro.'),
(2, 'Workshop', '#C59B27', 'Workshops e oficinas práticas de competências e inovação.'),
(3, 'Governança', '#7C3AED', 'Reuniões de conselho, governança e temas societários.'),
(4, 'Planejamento Estratégico', '#0284C7', 'Sessões de alinhamento e plano de negócios plurianual.'),
(5, 'Curso & Formação', '#16A34A', 'Cursos de capacitação e formação continuada.')
ON DUPLICATE KEY UPDATE `nome` = VALUES(`nome`), `cor` = VALUES(`cor`);

-- 7. Usuários Iniciais
-- Admin ($2y$10$Es/QiZgNIJkCg/SUOBOuyOiGtysLP3bpVkVKC5rMFD0fTZ3UI4skW = Admin@2026!)
-- Nicolle ($2y$10$fVqcJA3LdTHy/bMeG3E4sewfMDjZHLDu5uVPCTB8hKMPqIRWEsGt2 = Nicolle@2026!)
-- Demais participantes ($2y$10$yzpNqoI9f.c5SZXraJI4muGqrWySpedDz3ZVC3fQrJXW1wdF8ZEFG = DeCesaro@2026!)
INSERT INTO `ctdesenvolvimento_decesaro_usuario` (`id`, `nome`, `email`, `senha`, `data_nascimento`, `tipo`, `familia`, `geracao`, `status`) VALUES
(1, 'Augusto Cavalca (Comitê)', 'admin@decesaro.com', '$2y$10$Es/QiZgNIJkCg/SUOBOuyOiGtysLP3bpVkVKC5rMFD0fTZ3UI4skW', '1985-06-20', 'Admin', 'Augusto', '2ª Geração', 'Ativo'),
(2, 'Nicolle De Cesaro', 'nicolle@decesaro.com', '$2y$10$fVqcJA3LdTHy/bMeG3E4sewfMDjZHLDu5uVPCTB8hKMPqIRWEsGt2', '2008-03-15', 'Normal', 'Nestor', '3ª Geração', 'Ativo'),
(3, 'Zeca De Cesaro', 'zeca@decesaro.com', '$2y$10$yzpNqoI9f.c5SZXraJI4muGqrWySpedDz3ZVC3fQrJXW1wdF8ZEFG', '1962-11-10', 'Normal', 'Zeca', '1ª Geração', 'Ativo'),
(4, 'Wilson De Cesaro', 'wilson@decesaro.com', '$2y$10$yzpNqoI9f.c5SZXraJI4muGqrWySpedDz3ZVC3fQrJXW1wdF8ZEFG', '1970-04-25', 'Normal', 'Wilson', '2ª Geração', 'Ativo'),
(5, 'Marisa De Cesaro', 'marisa@decesaro.com', '$2y$10$yzpNqoI9f.c5SZXraJI4muGqrWySpedDz3ZVC3fQrJXW1wdF8ZEFG', '1975-08-14', 'Normal', 'Marisa', '2ª Geração', 'Ativo'),
(6, 'Nane De Cesaro', 'nane@decesaro.com', '$2y$10$yzpNqoI9f.c5SZXraJI4muGqrWySpedDz3ZVC3fQrJXW1wdF8ZEFG', '1998-12-05', 'Normal', 'Nane', '3ª Geração', 'Ativo'),
(7, 'Tati De Cesaro', 'tati@decesaro.com', '$2y$10$yzpNqoI9f.c5SZXraJI4muGqrWySpedDz3ZVC3fQrJXW1wdF8ZEFG', '2004-09-30', 'Normal', 'Tati', '3ª Geração', 'Ativo')
ON DUPLICATE KEY UPDATE `nome` = VALUES(`nome`), `tipo` = VALUES(`tipo`), `familia` = VALUES(`familia`), `geracao` = VALUES(`geracao`);

-- 8. Preferências dos Usuários
INSERT INTO `ctdesenvolvimento_decesaro_preferencia_usuario` (`id_usuario`, `tema`, `notificacoes_email`) VALUES
(1, 'claro', 'S'),
(2, 'claro', 'S'),
(3, 'claro', 'S'),
(4, 'claro', 'S'),
(5, 'claro', 'S'),
(6, 'claro', 'S'),
(7, 'claro', 'S')
ON DUPLICATE KEY UPDATE `tema` = VALUES(`tema`);

-- 9. Progresso Inicial da Nicolle (Refletindo layout.png: "Família - Nossa história - Concluído em 20/02")
INSERT INTO `ctdesenvolvimento_decesaro_progresso_video` (`id_usuario`, `id_video`, `posicao_atual`, `tempo_assistido`, `percentual`, `concluido`, `data_inicio`, `data_conclusao`, `data_ultima_atividade`, `metodo_conclusao`) VALUES
(2, 1, 360, 360, 100.00, 'S', '2026-02-20 14:00:00', '2026-02-20 14:30:00', '2026-02-20 14:30:00', 'automatico'),
(2, 2, 210, 210, 50.00, 'N', '2026-03-01 10:15:00', NULL, '2026-03-01 10:25:00', NULL),
(2, 9, 315, 315, 70.00, 'N', '2026-03-05 16:00:00', NULL, '2026-03-05 16:15:00', NULL)
ON DUPLICATE KEY UPDATE `percentual` = VALUES(`percentual`), `concluido` = VALUES(`concluido`);

-- 10. Eventos da Agenda (Conforme layout.png)
INSERT INTO `ctdesenvolvimento_decesaro_evento` (`id`, `titulo`, `descricao`, `id_categoria`, `data_inicio`, `data_fim`, `dia_inteiro`, `local`, `link_online`, `responsavel`, `situacao`, `visibilidade`, `limite_vagas`, `id_raia_conteudo`, `criado_por`) VALUES
(1, 'Encontro da Família', 'Tema: Valores e Legado. Encontro geral para fortalecimento dos laços familiares e reflexão sobre nossos princípios fundamentais.', 1, '2026-03-15 09:00:00', '2026-03-15 18:00:00', 'S', 'Xangri-lá, RS', NULL, 'Comitê de Desenvolvimento', 'Confirmado', 'Todos', 60, 1, 1),
(2, 'Workshop - Inovação e Tecnologia', 'Oficina prática sobre Inteligência Artificial, ferramentas digitais e o impacto no futuro dos nossos negócios.', 2, '2026-06-20 14:00:00', '2026-06-20 17:30:00', 'N', 'Auditório Corporativo / Online', 'https://meet.google.com/xyz-decesaro', 'Augusto Cavalca', 'Confirmado', 'Todos', 40, 17, 1),
(3, 'Encontro de Governança', 'Apresentação das diretrizes societárias, acordos de família e prestação de contas dos comitês.', 3, '2026-08-10 19:00:00', '2026-08-10 21:30:00', 'N', 'Sede Administrativa De Cesaro', NULL, 'Conselho de Administração', 'Confirmado', 'Todos', 35, 8, 1),
(4, 'Planejamento Estratégico', 'Reunião para debater metas plurianuais, investimentos e diretrizes de mercado.', 4, '2026-11-14 09:00:00', '2026-11-14 17:00:00', 'S', 'Centro de Eventos De Cesaro', NULL, 'Diretoria Executiva', 'Confirmado', 'Todos', 50, 12, 1)
ON DUPLICATE KEY UPDATE `titulo` = VALUES(`titulo`), `data_inicio` = VALUES(`data_inicio`), `local` = VALUES(`local`);

-- 11. Inscrições em Eventos (Nicolle inscrita no Encontro da Família)
INSERT INTO `ctdesenvolvimento_decesaro_inscricao_evento` (`id_evento`, `id_usuario`, `status`, `presenca`, `data_inscricao`) VALUES
(1, 2, 'inscrito', 'N', '2026-02-15 10:00:00'),
(2, 2, 'inscrito', 'N', '2026-02-18 11:30:00')
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`);

SET FOREIGN_KEY_CHECKS = 1;
