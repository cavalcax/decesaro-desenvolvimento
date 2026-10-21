<!DOCTYPE html>
<html lang="pt-BR"
  class="h-full <?= ($isDarkTheme ?? (($_COOKIE['decesaro_theme'] ?? '') === 'escuro')) ? 'dark' : '' ?>">

<head>
  <meta charset="UTF-8">
  <!-- Prevenção imediata de FOUC / Piscar de Tema Escuro -->
  <script>
    (function () {
      try {
        var theme = localStorage.getItem('decesaro_theme');
        var cookieMatch = document.cookie.match(/(?:^|; )decesaro_theme=([^;]*)/);
        var cookieTheme = cookieMatch ? decodeURIComponent(cookieMatch[1]) : null;
        var activeTheme = theme || cookieTheme;
        if (activeTheme === 'escuro') {
          document.documentElement.classList.add('dark');
          if (cookieTheme !== 'escuro') {
            document.cookie = "decesaro_theme=escuro; path=/; max-age=31536000; SameSite=Lax";
          }
        } else if (activeTheme === 'claro') {
          document.documentElement.classList.remove('dark');
          if (cookieTheme !== 'claro') {
            document.cookie = "decesaro_theme=claro; path=/; max-age=31536000; SameSite=Lax";
          }
        } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
          document.documentElement.classList.add('dark');
        }
      } catch (e) { }
    })();
  </script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= \App\Helpers\Security::e($csrfToken) ?>">
  <meta name="theme-color" content="#03254A">

  <title><?= \App\Helpers\Security::e($pageTitle ?? 'Desenvolvimento') ?> — Família De Cesaro</title>

  <!-- Favicons e Manifest -->
  <link rel="icon" type="image/svg+xml" href="/desenvolvimento/assets/img/favicon.svg">
  <link rel="icon" type="image/png" sizes="96x96" href="/desenvolvimento/assets/img/favicon-96x96.png">
  <link rel="apple-touch-icon" href="/desenvolvimento/assets/img/apple-touch-icon.png">
  <link rel="manifest" href="/desenvolvimento/manifest.webmanifest">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="De Cesaro">

  <!-- Fontes Google (Inter & Playfair/Cinzel) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;1,600&display=swap"
    rel="stylesheet">

  <!-- TailwindCSS Play CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            navy: {
              950: '#0a1826',
              900: '#03254A',
              800: '#163650',
              700: '#1b3b5f',
              600: '#254d78',
              50: '#f0f5fa',
            },
            amber: {
              400: '#d4af37',
              500: '#c59b27',
              600: '#b8860b',
            }
          },
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
            serif: ['Cinzel', 'Playfair Display', 'serif']
          }
        }
      }
    }
  </script>

  <!-- FontAwesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- jQuery (necessário para Select2) -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <!-- Select2 CSS & JS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- FullCalendar 6 -->
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

  <!-- Chart.js 4 -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

  <!-- Estilos Customizados da Aplicação -->
  <link rel="stylesheet"
    href="/desenvolvimento/assets/css/app.css?v=<?= filemtime(BASE_PATH . '/public/assets/css/app.css') ?>">

  <script>
    window.IS_AUTHENTICATED = <?= $currentUser ? 'true' : 'false' ?>;
  </script>
</head>

<body class="flex flex-col min-h-screen bg-slate-50 dark:bg-[#03254A] text-slate-800 dark:text-slate-100">

  <!-- Global Loading Spinner Overlay -->
  <div id="global-spinner-overlay">
    <div
      class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-2xl flex flex-col items-center gap-3 border border-slate-100 dark:border-slate-700">
      <div class="w-10 h-10 border-4 border-amber-500/30 border-t-amber-500 rounded-full animate-spin"></div>
      <p class="spinner-text text-sm font-medium text-slate-700 dark:text-slate-200">Carregando...</p>
    </div>
  </div>

  <!-- Header Superior Institucional -->
  <header
    class="sticky top-0 z-40 bg-white/95 dark:bg-[#03254A]/95 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16 sm:h-20">

        <!-- Logo Institucional (Adaptação instantânea por CSS sem flash) -->
        <a href="/desenvolvimento/inicio" class="flex items-center group py-1"
          title="Plataforma de Desenvolvimento — Legado e Futuro">
          <!-- Logo para Tema Claro (oculto no escuro) -->
          <img id="brand-logo-img" src="/desenvolvimento/assets/img/logo-white.png"
            alt="Legado e Futuro — Desenvolvimento da família De Cesaro" data-base-path="/desenvolvimento/assets/img"
            class="dark:hidden h-14 sm:h-[74px] w-auto object-contain transition-transform">

          <!-- Logo para Tema Escuro (oculto no claro) -->
          <img id="brand-logo-img-dark" src="/desenvolvimento/assets/img/logo-dark.png"
            alt="Legado e Futuro — Desenvolvimento da família De Cesaro" data-base-path="/desenvolvimento/assets/img"
            class="hidden dark:block h-14 sm:h-[74px] w-auto object-contain transition-transform">
        </a>

        <!-- Ações do Cabeçalho: PWA, Tema, Notificações, Perfil -->
        <div class="flex items-center gap-2 sm:gap-3.5">
          <!-- Botão Instalar PWA -->
          <button id="pwa-install-btn" type="button"
            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 sm:px-3 sm:py-1.5 rounded-full text-xs font-semibold bg-amber-500/10 hover:bg-amber-500 text-amber-700 hover:text-slate-950 dark:bg-amber-400/10 dark:hover:bg-amber-400 dark:text-amber-300 dark:hover:text-slate-950 border border-amber-500/30 dark:border-amber-400/30 transition-all shadow-sm group"
            title="Instalar Aplicativo (PWA)" aria-label="Instalar Aplicativo">
            <i
              class="fa-solid fa-mobile-screen-button text-xs sm:text-sm group-hover:scale-110 transition-transform"></i>
            <span class="hidden sm:inline font-medium">Instalar App</span>
          </button>

          <!-- Botão Alternar Tema -->
          <button id="theme-toggle-btn"
            class="w-10 h-10 rounded-full flex items-center justify-center bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors"
            aria-label="Alternar tema claro/escuro" title="Alternar Tema">
            <i id="theme-toggle-icon" class="fa-solid fa-moon text-slate-600 dark:text-amber-400 text-sm"></i>
          </button>

          <?php if ($currentUser): ?>
            <!-- Sino de Notificações com Badge Dinâmico -->
            <button id="notif-bell-btn" type="button"
              class="w-10 h-10 rounded-full flex items-center justify-center bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors text-slate-600 dark:text-slate-300 relative group cursor-pointer"
              title="Minhas Notificações" aria-label="Abrir Notificações">
              <i class="fa-regular fa-bell text-sm group-hover:scale-110 transition-transform"></i>
              <span id="notif-badge"
                class="hidden absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-amber-500 text-slate-950 text-[10px] font-extrabold flex items-center justify-center shadow-md animate-pulse">0</span>
            </button>

            <!-- Avatar do Usuário e Dados -->
            <div class="flex items-center gap-2 pl-2 border-l border-slate-200 dark:border-slate-700">
              <a href="/desenvolvimento/perfil" class="flex items-center gap-2.5 group">
                <div
                  class="w-9 h-9 rounded-full bg-gradient-to-tr from-amber-600 to-amber-400 text-slate-900 font-bold flex items-center justify-center text-xs shadow-md group-hover:ring-2 group-hover:ring-amber-400 transition-all">
                  <?= \App\Helpers\Format::initials($currentUser['nome']) ?>
                </div>
                <div class="hidden lg:flex flex-col text-left">
                  <span
                    class="text-xs font-semibold text-slate-800 dark:text-slate-100 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors leading-tight">
                    <?= \App\Helpers\Security::e($currentUser['nome']) ?>
                  </span>
                  <span class="text-[10px] text-slate-500 dark:text-slate-400">
                    <?= \App\Helpers\Security::e($currentUser['geracao']) ?> • Núcleo
                    <?= \App\Helpers\Security::e($currentUser['familia']) ?>
                  </span>
                </div>
              </a>

              <!-- Botão Sair -->
              <form action="/desenvolvimento/logout" method="POST" class="inline m-0">
                <?= $csrfField ?>
                <button type="submit" class="p-2 text-slate-400 hover:text-red-500 transition-colors ml-1"
                  title="Encerrar Sessão">
                  <i class="fa-solid fa-arrow-right-from-bracket text-sm"></i>
                </button>
              </form>
            </div>
          <?php else: ?>
            <a href="/desenvolvimento/login"
              class="px-4 py-2 text-xs font-semibold rounded-lg bg-navy-900 dark:bg-amber-500 text-white dark:text-slate-950 hover:opacity-90 shadow-sm transition-opacity">
              Entrar
            </a>
          <?php endif; ?>

          <!-- Botão Menu Mobile -->
          <button id="mobile-menu-toggle"
            class="md:hidden p-2 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
            <i class="fa-solid fa-bars text-lg"></i>
          </button>
        </div>

      </div>
    </div>

    <!-- Navegação Principal para Participantes (8 Áreas) -->
    <?php if ($currentUser):
      $currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
      $navItems = [
        ['url' => '/desenvolvimento/inicio', 'label' => 'Início', 'icon' => 'fa-house'],
        ['url' => '/desenvolvimento/familia', 'label' => 'Família', 'icon' => 'fa-users'],
        ['url' => '/desenvolvimento/propriedade', 'label' => 'Propriedade', 'icon' => 'fa-landmark'],
        ['url' => '/desenvolvimento/negocios', 'label' => 'Negócios', 'icon' => 'fa-briefcase'],
        ['url' => '/desenvolvimento/competencias-individuais', 'label' => 'Competências Individuais', 'icon' => 'fa-lightbulb'],
        ['url' => '/desenvolvimento/jornada', 'label' => 'Minha Jornada', 'icon' => 'fa-chart-line'],
        ['url' => '/desenvolvimento/agenda', 'label' => 'Agenda', 'icon' => 'fa-calendar-days'],
        ['url' => '/desenvolvimento/perfil', 'label' => 'Meu Perfil', 'icon' => 'fa-user'],
      ];
      ?>
      <nav class="hidden md:block bg-slate-100/70 dark:bg-[#0c1f2e] border-t border-slate-200/80 dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <ul class="flex items-center justify-between gap-1 overflow-x-auto py-1">
            <?php foreach ($navItems as $item):
              $isActive = str_starts_with($currentUri, $item['url']);
              ?>
              <li class="flex-1 text-center">
                <a href="<?= $item['url'] ?>"
                  class="flex flex-col lg:flex-row items-center justify-center gap-1 lg:gap-1.5 py-2 px-1.5 lg:px-2.5 rounded-lg text-[11px] lg:text-xs font-medium transition-all <?= $isActive ? 'text-amber-600 dark:text-amber-400 bg-white dark:bg-slate-800 shadow-sm font-bold border-b-2 border-amber-500' : 'text-slate-600 dark:text-slate-300 hover:text-navy-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-slate-800/60' ?>">
                  <i class="fa-solid <?= $item['icon'] ?> text-sm"></i>
                  <span class="truncate max-w-[140px] xl:max-w-none"><?= $item['label'] ?></span>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </nav>

      <!-- Menu Mobile Dropdown -->
      <div id="mobile-nav-menu"
        class="hidden md:hidden bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 px-4 pt-2 pb-4 space-y-1">
        <?php foreach ($navItems as $item):
          $isActive = str_starts_with($currentUri, $item['url']);
          ?>
          <a href="<?= $item['url'] ?>"
            class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium <?= $isActive ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' ?>">
            <i class="fa-solid <?= $item['icon'] ?> w-5 text-center"></i>
            <span><?= $item['label'] ?></span>
          </a>
        <?php endforeach; ?>

        <?php if ($isAdmin): ?>
          <div class="pt-3 border-t border-slate-200 dark:border-slate-800">
            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-500 px-3 block mb-1">Administração</span>
            <a href="/desenvolvimento/admin"
              class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
              <i class="fa-solid fa-gauge w-5 text-center text-amber-500"></i>
              <span>Painel Administrativo</span>
            </a>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </header>

  <!-- Conteúdo Principal Dinâmico -->
  <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 pb-3 sm:pt-6 sm:pb-3">
    <?= $content ?>
  </main>

  <!-- Dock Administrativo Inferior (Se Admin) Conforme Mapa de Telas -->
  <?php if ($isAdmin && $currentUser): ?>
    <section
      class="bg-gradient-to-r from-navy-950 via-navy-900 to-navy-950 text-white border-t-2 border-amber-500/80 shadow-2xl py-3.5 sm:py-4 px-4 mt-auto">
      <div class="max-w-7xl mx-auto">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-4">
          <div class="flex items-center gap-3 text-center lg:text-left shrink-0">
            <div
              class="w-10 h-10 rounded-xl bg-amber-500/20 border border-amber-500/40 flex items-center justify-center text-amber-400 text-lg shadow-inner">
              <i class="fa-solid fa-users-gear"></i>
            </div>
            <div>
              <h3
                class="text-sm sm:text-base font-bold uppercase tracking-wider text-amber-300 font-serif whitespace-nowrap">
                Área Administrativa
              </h3>
              <p class="text-[11px] text-slate-400">Ambiente restrito.</p>
            </div>
          </div>

          <!-- Ações Rápidas do Dock (Divididas em 2 linhas de 4 botões) -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs w-full lg:w-auto">
            <a href="/desenvolvimento/admin"
              class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 transition-colors flex items-center justify-center gap-1.5 font-medium border border-white/10 whitespace-nowrap">
              <i class="fa-solid fa-chart-pie text-amber-400"></i><span>Dashboard</span>
            </a>
            <a href="/desenvolvimento/admin/participantes"
              class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 transition-colors flex items-center justify-center gap-1.5 font-medium border border-white/10 whitespace-nowrap">
              <i class="fa-solid fa-user-group text-amber-400"></i><span>Participantes</span>
            </a>
            <a href="/desenvolvimento/admin/niveis"
              class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 transition-colors flex items-center justify-center gap-1.5 font-medium border border-white/10 whitespace-nowrap">
              <i class="fa-solid fa-layer-group text-amber-400"></i><span>Níveis</span>
            </a>
            <a href="/desenvolvimento/admin/raias"
              class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 transition-colors flex items-center justify-center gap-1.5 font-medium border border-white/10 whitespace-nowrap">
              <i class="fa-solid fa-bars-staggered text-amber-400"></i><span>Raias</span>
            </a>
            <a href="/desenvolvimento/admin/conteudos"
              class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 transition-colors flex items-center justify-center gap-1.5 font-medium border border-white/10 whitespace-nowrap">
              <i class="fa-solid fa-book-open text-amber-400"></i><span>Conteúdos</span>
            </a>
            <a href="/desenvolvimento/admin/videos"
              class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 transition-colors flex items-center justify-center gap-1.5 font-medium border border-white/10 whitespace-nowrap">
              <i class="fa-solid fa-play text-amber-400"></i><span>Vídeos</span>
            </a>
            <a href="/desenvolvimento/admin/agenda"
              class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 transition-colors flex items-center justify-center gap-1.5 font-medium border border-white/10 whitespace-nowrap">
              <i class="fa-solid fa-calendar text-amber-400"></i><span>Agenda</span>
            </a>
            <a href="/desenvolvimento/admin/notificacoes"
              class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 transition-colors flex items-center justify-center gap-1.5 font-medium border border-white/10 whitespace-nowrap">
              <i class="fa-solid fa-paper-plane text-amber-400"></i><span>Notificações</span>
            </a>
            <a href="/desenvolvimento/admin/relatorios"
              class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-navy-950 font-bold transition-all shadow-md flex items-center justify-center gap-1.5 whitespace-nowrap">
              <i class="fa-solid fa-file-pdf"></i><span>Relatórios</span>
            </a>
          </div>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <!-- Rodapé Institucional -->
  <footer
    class="bg-white dark:bg-[#071018] border-t border-slate-200 dark:border-slate-800/80 py-6 text-xs text-slate-500 dark:text-slate-400 transition-colors">
    <div
      class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left">
      <div class="flex items-center gap-2">
        <span class="font-serif font-bold text-slate-700 dark:text-slate-300">LEGADO E FUTURO</span>
        <span class="text-slate-400 dark:text-slate-600">•</span>
        <span>Desenvolvimento da Família De Cesaro</span>
      </div>
      <div>
        <span>Versão 1.0</span>
      </div>
    </div>
  </footer>

  <!-- Scripts Principais -->
  <script src="/desenvolvimento/assets/js/api.js?v=<?= filemtime(BASE_PATH . '/public/assets/js/api.js') ?>"></script>
  <script
    src="/desenvolvimento/assets/js/tables.js?v=<?= filemtime(BASE_PATH . '/public/assets/js/tables.js') ?>"></script>
  <script
    src="/desenvolvimento/assets/js/video-player.js?v=<?= filemtime(BASE_PATH . '/public/assets/js/video-player.js') ?>"></script>
  <script src="/desenvolvimento/assets/js/app.js?v=<?= filemtime(BASE_PATH . '/public/assets/js/app.js') ?>"></script>

  <!-- Modal de Notificações do Usuário -->
  <div id="notifications-modal"
    class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div
      class="bg-white dark:bg-slate-800 rounded-3xl max-w-lg w-full shadow-2xl border border-slate-200 dark:border-slate-700 flex flex-col overflow-hidden animate-in fade-in duration-200 relative max-h-[90vh]">

      <!-- Cabeçalho do Modal -->
      <div
        class="p-5 pb-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between gap-3 bg-slate-50/70 dark:bg-slate-900/40">
        <div class="flex items-center gap-2.5">
          <div
            class="w-9 h-9 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center">
            <i class="fa-regular fa-bell text-base"></i>
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h3 class="text-base font-bold font-serif text-slate-900 dark:text-white leading-tight">Notificações</h3>
              <span id="notif-modal-unread-count"
                class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-700 dark:text-amber-300">0
                novas</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">Comunicados, novidades e alertas da plataforma</p>
          </div>
        </div>

        <div class="flex items-center gap-1.5">
          <button type="button" id="notif-mark-all-btn"
            class="text-xs font-semibold text-amber-600 dark:text-amber-400 hover:underline px-2 py-1 rounded-lg hover:bg-amber-500/10 transition-colors"
            title="Marcar todas como lidas">
            Marcar lidas
          </button>
          <button type="button" id="notif-modal-close"
            class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-200/50 dark:hover:bg-slate-700 transition-colors"
            aria-label="Fechar">
            <i class="fa-solid fa-xmark text-base"></i>
          </button>
        </div>
      </div>

      <!-- Filtros Rápidos (Todas / Não Lidas) -->
      <div
        class="px-5 py-2.5 bg-slate-100/60 dark:bg-slate-900/60 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
        <div
          class="flex items-center gap-1 bg-white dark:bg-slate-800 p-0.5 rounded-xl border border-slate-200/70 dark:border-slate-700">
          <button type="button" id="notif-filter-all"
            class="notif-tab px-3 py-1 rounded-lg font-bold text-xs bg-amber-500 text-slate-950 transition-all">Todas</button>
          <button type="button" id="notif-filter-unread"
            class="notif-tab px-3 py-1 rounded-lg font-medium text-xs text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition-all">Não
            lidas</button>
        </div>
        <span id="notif-filter-stats" class="text-[11px] text-slate-400">0 registros</span>
      </div>

      <!-- Lista de Notificações com rolagem suave -->
      <div id="notif-items-container" class="p-4 space-y-2.5 overflow-y-auto max-h-[440px]">
        <!-- Itens inseridos via JavaScript -->
        <div class="py-12 text-center text-slate-400 text-xs">
          <div class="w-8 h-8 border-2 border-amber-500 border-t-transparent rounded-full animate-spin mx-auto mb-2">
          </div>
          <p>Buscando notificações...</p>
        </div>
      </div>

      <!-- Rodapé do Modal -->
      <div
        class="p-3.5 bg-slate-50 dark:bg-slate-900/60 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between text-xs">
        <a href="/desenvolvimento/perfil#preferencias-push"
          class="inline-flex items-center gap-1.5 text-slate-500 hover:text-amber-600 dark:text-slate-400 dark:hover:text-amber-400 transition-colors text-[11px] font-medium">
          <i class="fa-solid fa-gear text-xs"></i>
          <span>Gerenciar alertas no perfil</span>
        </a>
        <button type="button" id="notif-modal-ok"
          class="px-4 py-1.5 rounded-xl bg-navy-900 hover:bg-navy-800 dark:bg-amber-500 dark:hover:bg-amber-600 text-white dark:text-slate-950 font-bold text-xs shadow-sm transition-all">
          Fechar
        </button>
      </div>

    </div>
  </div>

  <!-- Modal Passo a Passo de Instalação PWA no iOS (Safari / iPhone / iPad) -->
  <div id="pwa-ios-modal"
    class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div
      class="bg-white dark:bg-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 animate-in fade-in duration-200 relative">

      <!-- Cabeçalho do Modal -->
      <div class="flex items-start justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-700">
        <div class="flex items-center gap-3">
          <img src="/desenvolvimento/assets/img/apple-touch-icon.png" alt="Legado e Futuro"
            class="w-12 h-12 rounded-2xl shadow-md border border-slate-200 dark:border-slate-700 object-cover">
          <div>
            <h3 class="text-base font-bold font-serif text-slate-900 dark:text-white leading-snug">Instalar no iPhone /
              iPad</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Adicione o atalho à Tela de Início</p>
          </div>
        </div>
        <button type="button" id="pwa-ios-modal-close"
          class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg p-1 transition-colors"
          aria-label="Fechar">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <!-- Passo a Passo Ilustrado -->
      <div class="mt-5 space-y-3.5 text-xs">
        <!-- Passo 1 -->
        <div
          class="flex items-start gap-3.5 p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800">
          <div
            class="w-7 h-7 rounded-full bg-amber-500/20 text-amber-600 dark:text-amber-400 font-bold flex items-center justify-center text-xs shrink-0 mt-0.5">
            1
          </div>
          <div class="flex-1">
            <p class="text-slate-800 dark:text-slate-200 font-medium leading-relaxed">
              No navegador Safari do seu dispositivo iOS, toque no botão <strong>Compartilhar</strong> na barra inferior
              (ou superior no iPad):
            </p>
            <div
              class="inline-flex items-center gap-2 mt-2 px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-slate-700 font-semibold text-xs shadow-sm">
              <i class="fa-solid fa-arrow-up-from-bracket text-amber-500 text-sm"></i>
              <span>Compartilhar</span>
            </div>
          </div>
        </div>

        <!-- Passo 2 -->
        <div
          class="flex items-start gap-3.5 p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800">
          <div
            class="w-7 h-7 rounded-full bg-amber-500/20 text-amber-600 dark:text-amber-400 font-bold flex items-center justify-center text-xs shrink-0 mt-0.5">
            2
          </div>
          <div class="flex-1">
            <p class="text-slate-800 dark:text-slate-200 font-medium leading-relaxed">
              Deslize o menu de opções para baixo e toque em <strong>"Adicionar à Tela de Início"</strong>:
            </p>
            <div
              class="inline-flex items-center gap-2 mt-2 px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-slate-700 font-semibold text-xs shadow-sm">
              <i class="fa-regular fa-square-plus text-amber-500 text-sm"></i>
              <span>Adicionar à Tela de Início</span>
            </div>
          </div>
        </div>

        <!-- Passo 3 -->
        <div
          class="flex items-start gap-3.5 p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800">
          <div
            class="w-7 h-7 rounded-full bg-amber-500/20 text-amber-600 dark:text-amber-400 font-bold flex items-center justify-center text-xs shrink-0 mt-0.5">
            3
          </div>
          <div class="flex-1">
            <p class="text-slate-800 dark:text-slate-200 font-medium leading-relaxed">
              No canto superior direito da tela, toque em <strong>Adicionar</strong>.
            </p>
            <p class="text-[11px] text-amber-600 dark:text-amber-400 font-semibold mt-1">
              ✓ O aplicativo será instalado como app nativo em tela cheia!
            </p>
          </div>
        </div>
      </div>

      <!-- Rodapé do Modal -->
      <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-700 flex justify-end">
        <button type="button" id="pwa-ios-modal-ok"
          class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-navy-900 hover:bg-navy-800 dark:bg-amber-500 dark:hover:bg-amber-600 text-white dark:text-slate-950 font-bold text-xs shadow-md transition-all">
          Entendi
        </button>
      </div>

    </div>
  </div>

</body>

</html>