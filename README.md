# Desenvolvimento — Legado e Futuro

## Plataforma de Desenvolvimento da Família De Cesaro

Aplicação web completa para acompanhamento e gestão do desenvolvimento pessoal, familiar, patrimonial e profissional dos membros e futuras gerações da **Família De Cesaro**.

A plataforma reproduz a identidade visual institucional e a organização de navegação apresentadas no conceito **"Legado e Futuro — Desenvolvimento da família De Cesaro"** (`layout.png`), implementando regras de negócio reais, controle de elegibilidade por idade e nível, player de vídeo HTML5 customizado com rastreamento periódico, agenda com FullCalendar e inscrições, relatórios com exportação para PDF e CSV, e ferramentas administrativas com formulários em grid de 12 colunas.

---

## 1. Tecnologias Utilizadas

- **Backend:** PHP 8.1+ com PDO e Prepared Statements
- **Banco de Dados:** MySQL 8 com codificação `utf8mb4` e prefixo centralizado configurável (`ctdesenvolvimento_decesaro_`)
- **Frontend:** HTML5, TailwindCSS via CDN, JavaScript moderno (Fetch API assíncrono com função central `apiRequest()`)
- **Componentes de Interface:** Select2 (busca com suporte pt-BR), SweetAlert2 (alertas e confirmações), FullCalendar 6 (agenda mensal, semanal, diária e lista), Chart.js 4 (gráficos analíticos)
- **Vídeo Player:** HTML5 nativo personalizado com velocidade selecionável até 1,5x, controle de volume, tela cheia, atalhos de teclado, sincronização periódica de progresso e cálculo de cobertura para conclusão automática a 90%
- **Exportação:** FPDF institucional nativo (cabeçalho azul-marinho `#03254A`, detalhes dourados `#D4AF37`, logo institucional) e exportação em CSV UTF-8
- **E-mails:** PHPMailer integrado com suporte SMTP e simulação local segura em log
- **PWA:** Manifest (`manifest.webmanifest`), Service Worker (`sw.js`) com cache estratégico e página offline (`offline.html`)

---

## 2. Estrutura do Projeto

```text
desenvolvimento/
├── app/
│   ├── Controllers/
│   │   ├── AdminController.php         # Painel administrativo, CRUDs e relatórios
│   │   ├── AgendaController.php        # Calendário de ações e inscrições
│   │   ├── ApiController.php           # Sincronização de progresso e preferências
│   │   ├── AuthController.php          # Login, logout e recuperação de senha
│   │   ├── DashboardController.php     # Início — painel personalizado do participante
│   │   ├── JornadaController.php       # Minha Jornada — progresso e PDI
│   │   ├── PerfilController.php        # Meu Perfil — dados e elegibilidade das raias
│   │   ├── RaiaController.php          # As 4 Raias e conteúdos temáticos
│   │   └── VideoController.php         # Player de vídeo e playlist
│   ├── Helpers/
│   │   ├── Audit.php                   # Trilha de auditoria
│   │   ├── Auth.php                    # Regras de idade, nível e autorização
│   │   ├── Env.php                     # Leitor de variáveis .env
│   │   ├── Format.php                  # Formatação de datas e durações
│   │   ├── Response.php                # Renderizador de views, JSON e redirects
│   │   ├── Router.php                  # Roteador com suporte a parâmetros e middlewares
│   │   └── Security.php                # CSRF, senhas seguras e sanitização
│   ├── Services/
│   │   ├── ExportService.php           # Geração de PDF e CSV
│   │   ├── MailService.php             # Envio de e-mails via PHPMailer
│   │   ├── FPDF/                       # Biblioteca FPDF institucional
│   │   └── PHPMailer/                  # Biblioteca PHPMailer e SMTP
│   └── Views/
│       ├── layouts/                    # Layout principal, auth e erro
│       ├── auth/                       # Telas de login e recuperação
│       ├── dashboard/                  # Tela 1: Início
│       ├── raias/                      # Telas 2 a 5: Família, Propriedade, Negócios, Competências Individuais
│       ├── jornada/                    # Tela 6: Minha Jornada
│       ├── agenda/                     # Tela 7: Agenda
│       ├── perfil/                     # Tela 8: Meu Perfil
│       └── admin/                      # Painel administrativo, CRUDs e relatórios
├── config/
│   ├── app.php                         # Configuração geral da aplicação
│   ├── bootstrap.php                   # Autoloader PSR-4 e inicialização segura
│   ├── database.php                    # Conexão PDO e prefixo de tabelas
│   ├── mail.php                        # Configurações de e-mail
│   └── security.php                    # Políticas de segurança e parâmetros de vídeo
├── database/
│   ├── schema.sql                      # Schema executável para instalação nova
│   └── seeds.sql                       # Dados iniciais compatíveis com layout.png
├── public/
│   ├── assets/
│   │   ├── css/app.css                 # Estilos institucionais e tema claro/escuro
│   │   ├── js/                         # api.js, app.js, tables.js, video-player.js
│   │   └── img/                        # Logos, favicons e ícones PWA
│   ├── index.php                       # Ponto de entrada web público
│   ├── manifest.webmanifest            # Manifesto PWA
│   ├── sw.js                           # Service Worker
│   └── offline.html                    # Página offline
├── routes/
│   └── web.php                         # Definição central de rotas
├── scripts/
│   └── migrate.php                     # Script CLI para aplicar schema e seeds
├── storage/
│   ├── logs/                           # Logs de e-mail e erros
│   └── exports/                        # Relatórios gerados
├── tests/
│   └── test_suite.php                  # Bateria de testes automatizados
├── .env.example                        # Exemplo de configuração de ambiente
├── .gitignore                          # Arquivos ignorados no versionamento
└── README.md                           # Documentação técnica
```

---

## 3. Instruções de Instalação e Execução

### Pré-requisitos
- PHP 8.1+ instalado (CLI e Apache/Nginx)
- MySQL 8 instalado e em execução (porta 3306)

### Passo 1: Configuração do Ambiente (.env)
Copie o arquivo `.env.example` para `.env`:
```bash
cp .env.example .env
```
Verifique as credenciais de banco de dados no `.env`:
```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=ctdecesaro
DB_USER=root
DB_PASSWORD=
DB_PREFIX=ctdesenvolvimento_decesaro_
```

### Passo 2: Executar as Migrações e Seeds
Execute o script de migração no terminal:
```bash
php scripts/migrate.php
```
O script criará o banco de dados `ctdecesaro` (se não existir), aplicará o schema das 19 tabelas com o prefixo `ctdesenvolvimento_decesaro_` e inserirá todos os dados iniciais compatíveis com o mapa de referência.

### Passo 3: Acesso Web
Acesse a aplicação no navegador através do servidor Apache:
`http://localhost/desenvolvimento/login` ou `http://localhost/desenvolvimento/public/login`.

---

## 4. Contas de Acesso e Credenciais de Demonstração

| Perfil | Nome | E-mail | Senha | Ramo Familiar | Geração |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Administrador / Comitê** | Augusto Cavalca | `admin@decesaro.com` | `Admin@2026!` | Augusto | 2ª Geração |
| **Participante (Referência)** | Nicolle De Cesaro | `nicolle@decesaro.com` | `Nicolle@2026!` | Nestor | 3ª Geração |
| **Participante** | Zeca De Cesaro | `zeca@decesaro.com` | `DeCesaro@2026!` | Zeca | 1ª Geração |
| **Participante** | Wilson De Cesaro | `wilson@decesaro.com` | `DeCesaro@2026!` | Wilson | 2ª Geração |
| **Participante** | Marisa De Cesaro | `marisa@decesaro.com` | `DeCesaro@2026!` | Marisa | 2ª Geração |
| **Participante** | Nane De Cesaro | `nane@decesaro.com` | `DeCesaro@2026!` | Nane | 3ª Geração |
| **Participante** | Tati De Cesaro | `tati@decesaro.com` | `DeCesaro@2026!` | Tati | 3ª Geração |

---

## 5. Regras de Negócio e Elegibilidade

1. **Cálculo da Idade:**
   - A idade é calculada dinamicamente a partir de `Usuario.data_nascimento` em relação à data atual.
2. **Níveis de Elegibilidade:**
   - Nível 1: Introdução & Primeiros Passos (0 a 15 anos)
   - Nível 2: Juventude & Formação Inicial (16 a 20 anos)
   - Nível 3: Governança & Formação Acionária (21 a 35 anos)
   - Nível 4: Liderança, Patrimônio & Estratégia (36+ anos)
3. **Acesso aos Conteúdos:**
   - Um conteúdo (`RaiaConteudo`) é elegível para o participante se houver vínculo na tabela `RaiaConteudoNivel` para a idade do participante.
   - Quando o conteúdo não é elegível, a interface exibe o ícone de cadeado e o rótulo **"Disponível conforme elegibilidade"**, bloqueando o acesso e reprodução no backend.
4. **Regra de Exclusividade do Vídeo:**
   - Se `Video.exclusivo == 'Não'`, qualquer usuário autenticado pode assistir à aula, desconsiderando restrições de nível.
   - Se `Video.exclusivo == 'Sim'`, o acesso exige que o participante pertença ao nível correspondente do conteúdo.
5. **Player de Vídeo e Progresso:**
   - O player HTML5 rastreia o tempo assistido real a cada 5 segundos via chamada AJAX segura (`/api/video-progress`).
   - O tempo não é multiplicado pela velocidade de reprodução (0,5x a 1,5x) e trechos pulados na barra de progresso não são contabilizados como assistidos.
   - Ao atingir 90% de cobertura, o backend marca automaticamente a aula como concluída (`concluido = 'S'`).
6. **Agenda e Presenças:**
   - Eventos suportam limite de vagas com fila de espera automática.
   - Participantes podem se inscrever ou cancelar inscrição com atualização em tempo real.
   - Administradores têm acesso ao modal de chamada para marcar presença individualmente.
7. **Formulários de 12 Colunas:**
   - Todos os formulários administrativos utilizam grid de 12 colunas responsivo, com Select2 para buscas dinâmicas e SweetAlert2 para confirmações.

---

## 6. Testes Automatizados

A aplicação inclui uma suíte de testes completa cobrindo todos os requisitos obrigatórios da Seção 24. Para executá-la:
```bash
php tests/test_suite.php
```
Todos os 34 testes são executados e verificam banco de dados, segurança, hash bcrypt, CSRF, regras de idade, exclusividade de vídeos, progresso, agenda, inscrições, relatórios e PWA.
