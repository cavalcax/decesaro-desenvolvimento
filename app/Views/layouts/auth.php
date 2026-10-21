<!DOCTYPE html>
<html lang="pt-BR" class="h-full">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= \App\Helpers\Security::e($csrfToken) ?>">
  <title><?= \App\Helpers\Security::e($pageTitle ?? 'Acesso') ?> — Família De Cesaro</title>

  <link rel="icon" type="image/svg+xml" href="/desenvolvimento/assets/img/favicon.svg">
  <link rel="apple-touch-icon" href="/desenvolvimento/assets/img/apple-touch-icon.png">
  <link rel="manifest" href="/desenvolvimento/manifest.webmanifest">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Inter:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">

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
            },
            amber: {
              400: '#d4af37',
              500: '#c59b27',
              600: '#b8860b',
            }
          }
        }
      }
    }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/desenvolvimento/assets/css/app.css?v=<?= filemtime(BASE_PATH . '/public/assets/css/app.css') ?>">

  <!-- Script anti-flicker de tema síncrono no head -->
  <script>
      (function () {
        var stored = localStorage.getItem('decesaro_theme');
        var cookieMatch = document.cookie.match(/(?:^|;\s*)decesaro_theme=([^;]*)/);
        var cookieVal = cookieMatch ? decodeURIComponent(cookieMatch[1]) : null;
        var theme = stored || cookieVal;
        var isDark = (theme === 'dark' || theme === 'escuro') || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches);
        if (isDark) {
          document.documentElement.classList.add('dark');
        } else {
          document.documentElement.classList.remove('dark');
        }
      })();
  </script>
</head>

<body
  class="min-h-screen bg-[#f8f9fc] dark:bg-[#03254A] flex flex-col justify-center py-10 sm:px-6 lg:px-8 text-slate-800 dark:text-slate-100 selection:bg-amber-500 selection:text-slate-900 transition-colors duration-200">

  <!-- Botão Flutuante de Alternar Tema (Claro / Escuro) -->
  <div class="fixed top-4 right-4 z-50">
    <button id="theme-toggle-btn"
      class="w-10 h-10 rounded-full flex items-center justify-center bg-white/90 dark:bg-slate-800/90 hover:bg-white dark:hover:bg-slate-700 text-slate-600 dark:text-amber-400 border border-slate-200 dark:border-slate-700 shadow-md backdrop-blur transition-all transform active:scale-95"
      aria-label="Alternar tema claro/escuro" title="Alternar Tema">
      <i id="theme-toggle-icon" class="fa-solid fa-moon text-sm"></i>
    </button>
  </div>

  <div class="sm:mx-auto sm:w-full sm:max-w-md text-center px-4">
    <!-- Logo para Tema Claro (oculto no escuro) -->
    <img id="brand-logo-img" src="/desenvolvimento/assets/img/logo-white.png" alt="Legado e Futuro"
      data-base-path="/desenvolvimento/assets/img"
      class="dark:hidden w-full max-w-[340px] sm:max-w-[420px] mx-auto object-contain mb-2 transition-transform">

    <!-- Logo para Tema Escuro (oculto no claro) -->
    <img id="brand-logo-img-dark" src="/desenvolvimento/assets/img/logo-dark.png" alt="Legado e Futuro"
      data-base-path="/desenvolvimento/assets/img"
      class="hidden dark:block w-full max-w-[340px] sm:max-w-[420px] mx-auto object-contain mb-2 transition-transform">
  </div>

  <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md px-4">
    <div
      class="auth-card bg-white dark:bg-[#072342] backdrop-blur-md py-8 px-6 sm:px-10 shadow-xl dark:shadow-2xl rounded-2xl border border-slate-200 dark:border-navy-700/80 text-slate-800 dark:text-white transition-colors">
      <?= $content ?>
    </div>

    <div class="mt-8 text-center text-xs text-slate-500 dark:text-slate-400">
      <p>&copy; <?= date('Y') ?> Família De Cesaro. Todos os direitos reservados.</p>
    </div>
  </div>

  <script src="/desenvolvimento/assets/js/app.js"></script>
</body>

</html>