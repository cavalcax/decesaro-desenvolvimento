<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= \App\Helpers\Security::e($pageTitle ?? 'Erro') ?> — Legado e Futuro</title>
  <link rel="stylesheet" href="/desenvolvimento/assets/css/app.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { darkMode: 'class' };
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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
<body class="bg-[#f8f9fc] dark:bg-[#03254A] text-slate-800 dark:text-slate-100 flex items-center justify-center min-h-screen p-4 transition-colors">
  <!-- Botão Flutuante de Alternar Tema (Claro / Escuro) -->
  <div class="fixed top-4 right-4 z-50">
    <button id="theme-toggle-btn"
      class="w-10 h-10 rounded-full flex items-center justify-center bg-white/90 dark:bg-slate-800/90 hover:bg-white dark:hover:bg-slate-700 text-slate-600 dark:text-amber-400 border border-slate-200 dark:border-slate-700 shadow-md backdrop-blur transition-all transform active:scale-95"
      aria-label="Alternar tema claro/escuro" title="Alternar Tema">
      <i id="theme-toggle-icon" class="fa-solid fa-moon text-sm"></i>
    </button>
  </div>

  <div class="max-w-md w-full bg-white dark:bg-[#0f2233] p-8 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl text-center">
    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-500 text-2xl font-bold">
      <?= (int)($statusCode ?? 500) ?>
    </div>
    <h1 class="text-2xl font-bold font-serif mb-2 text-slate-900 dark:text-white"><?= \App\Helpers\Security::e($pageTitle ?? 'Ocorreu um erro') ?></h1>
    <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">
      <?= \App\Helpers\Security::e($message ?? 'Não foi possível completar sua solicitação.') ?>
    </p>
    <a href="/desenvolvimento/inicio" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-navy-900 to-navy-800 dark:from-amber-500 dark:to-amber-600 text-white dark:text-slate-950 font-bold rounded-lg shadow-md hover:opacity-95 transition-all text-sm">
      <i class="fa-solid fa-arrow-left"></i>
      <span>Voltar ao Início</span>
    </a>
  </div>

  <script src="/desenvolvimento/assets/js/app.js"></script>
</body>
</html>
