<?php
/** @var string|null $error */
/** @var string $token */
?>

<div class="mb-6 text-center">
  <h3 class="text-xl font-bold font-serif text-slate-900 dark:text-white">Criar Nova Senha</h3>
  <p class="text-xs text-slate-500 dark:text-slate-300 mt-1">Defina sua nova senha com segurança</p>
</div>

<?php if (!empty($error)): ?>
  <div class="mb-5 p-3.5 rounded-xl bg-red-50 dark:bg-red-950/60 border border-red-200 dark:border-red-800/80 text-red-700 dark:text-red-300 text-xs flex items-start gap-2.5">
    <i class="fa-solid fa-circle-exclamation text-sm mt-0.5"></i>
    <span><?= \App\Helpers\Security::e($error) ?></span>
  </div>
<?php endif; ?>

<form action="/desenvolvimento/redefinir-senha" method="POST" class="space-y-4">
  <?= $csrfField ?>
  <input type="hidden" name="token" value="<?= \App\Helpers\Security::e($token) ?>">

  <div>
    <label for="password" class="block text-xs font-semibold text-slate-700 dark:text-slate-200 mb-1">
      Nova Senha
    </label>
    <div class="relative">
      <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
        <i class="fa-solid fa-lock text-sm"></i>
      </span>
      <input type="password" id="password" name="password" required autocomplete="new-password"
             placeholder="Mínimo 8 caracteres, maiúscula, número e símbolo"
             class="auth-input w-full pl-9 pr-3 py-2.5 text-sm bg-slate-50 dark:bg-[#162e47] border border-slate-200 dark:border-slate-700/80 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 transition-all">
    </div>
    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Exigido: 8+ caracteres, 1 maiúscula, 1 número, 1 caractere especial.</p>
  </div>

  <div>
    <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 dark:text-slate-200 mb-1">
      Confirmar Nova Senha
    </label>
    <div class="relative">
      <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
        <i class="fa-solid fa-shield-halved text-sm"></i>
      </span>
      <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
             placeholder="Repita a senha"
             class="auth-input w-full pl-9 pr-3 py-2.5 text-sm bg-slate-50 dark:bg-[#162e47] border border-slate-200 dark:border-slate-700/80 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 transition-all">
    </div>
  </div>

  <div class="pt-2">
    <button type="submit" 
            class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500 via-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-amber-500/20 border border-amber-400/40 transition-all transform active:scale-[0.99] flex items-center justify-center gap-2">
      <i class="fa-solid fa-check text-xs"></i>
      <span>Salvar Nova Senha</span>
    </button>
  </div>
</form>
