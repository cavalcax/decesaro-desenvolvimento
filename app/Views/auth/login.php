<?php
/** @var string|null $error */
/** @var string|null $success */
/** @var string|null $oldEmail */
?>

<div class="mb-6 text-center">
  <h3 class="text-xl font-bold font-serif text-slate-900 dark:text-white">Acesse sua Conta</h3>
  <p class="text-xs text-slate-500 dark:text-slate-300 mt-1">Insira suas credenciais para acessar sua jornada</p>
</div>

<?php if (!empty($error)): ?>
  <div
    class="mb-5 p-3.5 rounded-xl bg-red-50 dark:bg-red-950/60 border border-red-200 dark:border-red-800/80 text-red-700 dark:text-red-300 text-xs flex items-start gap-2.5">
    <i class="fa-solid fa-circle-exclamation text-sm mt-0.5"></i>
    <span><?= \App\Helpers\Security::e($error) ?></span>
  </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
  <div
    class="mb-5 p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/80 text-emerald-700 dark:text-emerald-300 text-xs flex items-start gap-2.5">
    <i class="fa-solid fa-circle-check text-sm mt-0.5"></i>
    <span><?= \App\Helpers\Security::e($success) ?></span>
  </div>
<?php endif; ?>

<form action="/desenvolvimento/login" method="POST" class="space-y-4">
  <?= $csrfField ?>

  <div>
    <label for="email" class="block text-xs font-semibold text-slate-700 dark:text-slate-200 mb-1">
      E-mail
    </label>
    <div class="relative">
      <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
        <i class="fa-regular fa-envelope text-sm"></i>
      </span>
      <input type="email" id="email" name="email" required autocomplete="email"
        value="<?= \App\Helpers\Security::e($oldEmail ?? '') ?>" placeholder="seu.email@decesaro.com"
        class="auth-input w-full pl-9 pr-3 py-2.5 text-sm bg-slate-50 dark:bg-[#162e47] border border-slate-200 dark:border-slate-700/80 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 transition-all">
    </div>
  </div>

  <div>
    <label for="password" class="block text-xs font-semibold text-slate-700 dark:text-slate-200 mb-1">
      Senha
    </label>
    <div class="relative">
      <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
        <i class="fa-solid fa-lock text-sm"></i>
      </span>
      <input type="password" id="password" name="password" required autocomplete="current-password"
        placeholder="••••••••"
        class="auth-input w-full pl-9 pr-3 py-2.5 text-sm bg-slate-50 dark:bg-[#162e47] border border-slate-200 dark:border-slate-700/80 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 transition-all">
    </div>
  </div>

  <div class="flex items-center justify-between pt-0.5">
    <label for="remember" class="inline-flex items-center gap-2 cursor-pointer select-none group">
      <input type="checkbox" id="remember" name="remember" value="1"
        <?= !empty($remember) ? 'checked' : '' ?>
        class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 text-amber-500 focus:ring-amber-500/40 bg-slate-50 dark:bg-[#162e47] cursor-pointer transition-colors">
      <span class="text-xs text-slate-600 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white transition-colors">
        Lembrar de mim
      </span>
    </label>
    <a href="/desenvolvimento/recuperar-senha" class="text-xs text-amber-600 dark:text-amber-400 hover:underline">
      Esqueceu a senha?
    </a>
  </div>

  <div class="pt-2">
    <button type="submit"
      class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500 via-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-amber-500/20 border border-amber-400/40 transition-all transform active:scale-[0.99] flex items-center justify-center gap-2">
      <span>Entrar na Plataforma</span>
      <i class="fa-solid fa-arrow-right text-xs"></i>
    </button>
  </div>
</form>