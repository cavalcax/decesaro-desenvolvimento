<?php
/** @var array $user */
/** @var array $preferencias */
/** @var array $elegibilidadeRaias */

$success = $_GET['success'] ?? null;
$error = $_GET['error'] ?? null;
?>

<div class="max-w-4xl mx-auto space-y-6">

  <!-- Mensagens de Alerta -->
  <?php if (!empty($success)): ?>
    <div
      class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs flex items-center gap-2 shadow-sm">
      <i class="fa-solid fa-circle-check text-sm"></i>
      <span><?= \App\Helpers\Security::e($success) ?></span>
    </div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div
      class="p-4 rounded-xl bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 text-xs flex items-center gap-2 shadow-sm">
      <i class="fa-solid fa-circle-exclamation text-sm"></i>
      <span><?= \App\Helpers\Security::e($error) ?></span>
    </div>
  <?php endif; ?>

  <!-- Card do Perfil do Participante (layout.png) -->
  <div class="card-decesaro p-6 sm:p-8">
    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">

      <!-- Avatar Grande -->
      <div
        class="w-24 h-24 rounded-full bg-gradient-to-tr from-amber-600 via-amber-500 to-amber-300 text-slate-950 font-bold text-2xl flex items-center justify-center shadow-xl border-4 border-white dark:border-slate-800 flex-shrink-0">
        <?= \App\Helpers\Format::initials($user['nome']) ?>
      </div>

      <div class="flex-1 text-center sm:text-left min-w-0">
        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-1">
          <h1 class="text-2xl font-bold font-serif text-slate-900 dark:text-white">
            <?= \App\Helpers\Security::e($user['nome']) ?>
          </h1>
          <span
            class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-500/20 text-amber-700 dark:text-amber-400 border border-amber-500/30">
            <?= \App\Helpers\Security::e($user['geracao']) ?>
          </span>
          <?php if ($user['tipo'] === 'Admin'): ?>
            <span
              class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-navy-900 text-white dark:bg-slate-700 border border-slate-600">
              Comitê de Desenvolvimento
            </span>
          <?php endif; ?>
        </div>

        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
          Núcleo Familiar: <strong
            class="text-slate-700 dark:text-slate-300"><?= \App\Helpers\Security::e($user['familia']) ?></strong> •
          Cadastrado em <?= \App\Helpers\Format::date($user['criado_em']) ?>
        </p>

        <!-- Botão Abrir Edição -->
        <div class="mt-4">
          <button onclick="document.getElementById('edit-profile-modal').classList.remove('hidden')"
            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all inline-flex items-center gap-1.5 shadow-sm border border-slate-200 dark:border-slate-700">
            <i class="fa-solid fa-pen-to-square text-xs text-amber-500"></i>
            <span>Editar Meus Dados</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    <!-- Dados Pessoais (layout.png) -->
    <div class="card-decesaro p-6">
      <h3
        class="text-base font-bold font-serif text-slate-900 dark:text-white mb-4 pb-2 border-b border-slate-100 dark:border-slate-800">
        Dados Pessoais
      </h3>

      <dl class="space-y-3 text-xs">
        <div class="flex items-center justify-between py-1.5 border-b border-slate-50 dark:border-slate-800/50">
          <dt class="text-slate-500 dark:text-slate-400 font-medium">Data de nascimento</dt>
          <dd class="font-bold text-slate-800 dark:text-slate-100 font-mono">
            <?= \App\Helpers\Format::date($user['data_nascimento']) ?>
          </dd>
        </div>
        <div class="flex items-center justify-between py-1.5 border-b border-slate-50 dark:border-slate-800/50">
          <dt class="text-slate-500 dark:text-slate-400 font-medium">Faixa etária calculada</dt>
          <dd class="font-bold text-amber-600 dark:text-amber-400"><?= $user['idade'] ?> anos
            (<?= $user['faixa_etaria'] ?>)</dd>
        </div>
        <div class="flex items-center justify-between py-1.5 border-b border-slate-50 dark:border-slate-800/50">
          <dt class="text-slate-500 dark:text-slate-400 font-medium">Geração</dt>
          <dd class="font-bold text-slate-800 dark:text-slate-100"><?= \App\Helpers\Security::e($user['geracao']) ?>
          </dd>
        </div>
        <div class="flex items-center justify-between py-1.5 border-b border-slate-50 dark:border-slate-800/50">
          <dt class="text-slate-500 dark:text-slate-400 font-medium">Núcleo Familiar</dt>
          <dd class="font-bold text-slate-800 dark:text-slate-100">
            <?= \App\Helpers\Security::e($user['familia']) ?>
          </dd>
        </div>
        <div class="flex items-center justify-between py-1.5 border-b border-slate-50 dark:border-slate-800/50">
          <dt class="text-slate-500 dark:text-slate-400 font-medium">Perfil na plataforma</dt>
          <dd class="font-bold text-slate-800 dark:text-slate-100">
            <?= $user['tipo'] === 'Admin' ? 'Administrador / Comitê' : 'Participante Familiar' ?>
          </dd>
        </div>
        <div class="flex items-center justify-between py-1.5">
          <dt class="text-slate-500 dark:text-slate-400 font-medium">E-mail</dt>
          <dd class="font-bold text-slate-800 dark:text-slate-100 font-mono">
            <?= \App\Helpers\Security::e($user['email']) ?>
          </dd>
        </div>
      </dl>
    </div>

    <!-- Elegibilidade das Raias (layout.png) -->
    <div class="card-decesaro p-6">
      <h3
        class="text-base font-bold font-serif text-slate-900 dark:text-white mb-4 pb-2 border-b border-slate-100 dark:border-slate-800">
        Elegibilidade das Raias
      </h3>

      <div class="space-y-3">
        <?php foreach ($elegibilidadeRaias as $er): ?>
          <div
            class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">
              <?= \App\Helpers\Security::e($er['descricao']) ?>
            </span>

            <?php if ($er['disponivel']): ?>
              <span
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                <i class="fa-solid fa-check text-[10px]"></i> Disponível
              </span>
            <?php else: ?>
              <span
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                <i class="fa-solid fa-lock text-[10px]"></i> Bloqueada
              </span>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <p class="text-[11px] text-slate-400 mt-4 leading-relaxed">
        * A elegibilidade é definida pela data de nascimento e pelos níveis de idade cadastrados pelo Comitê de
        Desenvolvimento.
      </p>
    </div>

  </div>

  <!-- Preferências de Notificações e Push (Web Push PWA) -->
  <div id="preferencias-push" class="card-decesaro p-6 space-y-4">
    <div
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
      <div class="flex items-center gap-3">
        <div
          class="w-10 h-10 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg shrink-0">
          <i class="fa-solid fa-tower-broadcast"></i>
        </div>
        <div>
          <h3 class="text-base font-bold font-serif text-slate-900 dark:text-white leading-snug">
            Notificações Push no Celular e Computador
          </h3>
          <p class="text-xs text-slate-500 dark:text-slate-400">
            Receba alertas instantâneos de novos eventos da agenda, vídeos liberados e comunicados
          </p>
        </div>
      </div>

      <!-- Badge de Status Atual -->
      <div id="push-status-badge">
        <?php if (($preferencias['notificacoes_push'] ?? 'S') === 'S'): ?>
          <span
            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Notificações Ativas</span>
          </span>
        <?php else: ?>
          <span
            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
            <span>Notificações Desativadas</span>
          </span>
        <?php endif; ?>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-1">

      <div class="md:col-span-2 space-y-2 text-xs text-slate-600 dark:text-slate-300">
        <p class="leading-relaxed">
          As <strong>Push Notifications</strong> funcionam no aplicativo instalado (PWA) e no navegador web. Ao manter
          ativado, você será informado sempre que:
        </p>
        <ul class="space-y-1.5 text-slate-500 dark:text-slate-400 pl-4 list-disc">
          <li>Um novo <strong>Encontro da Família</strong> ou evento for confirmado na agenda</li>
          <li>Novos <strong>vídeos ou módulos</strong> forem liberados na sua trilha de desenvolvimento</li>
          <li>O <strong>Comitê de Desenvolvimento</strong> enviar avisos e informativos exclusivos</li>
        </ul>
      </div>

      <!-- Ações de Ativar / Desativar e Testar -->
      <div
        class="flex flex-col justify-center gap-2.5 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800">
        <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300">Ações Rápidas</span>

        <!-- Botão Toggle de Notificação Push -->
        <button type="button" id="push-toggle-btn"
          data-status="<?= ($preferencias['notificacoes_push'] ?? 'S') === 'S' ? 'enabled' : 'disabled' ?>"
          class="w-full px-3.5 py-2.5 rounded-xl font-bold text-xs transition-all shadow-sm flex items-center justify-center gap-2 <?= ($preferencias['notificacoes_push'] ?? 'S') === 'S' ? 'bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200' : 'bg-amber-500 hover:bg-amber-600 text-slate-950 shadow-md' ?>">
          <i
            class="fa-solid <?= ($preferencias['notificacoes_push'] ?? 'S') === 'S' ? 'fa-bell-slash' : 'fa-bell' ?>"></i>
          <span id="push-toggle-btn-text">
            <?= ($preferencias['notificacoes_push'] ?? 'S') === 'S' ? 'Desativar Notificações' : 'Ativar Notificações' ?>
          </span>
        </button>

        <!-- Botão de Teste de Push Notification -->
        <button type="button" id="push-test-btn"
          class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-[11px] transition-colors flex items-center justify-center gap-1.5">
          <i class="fa-solid fa-paper-plane text-xs text-amber-500"></i>
          <span>Enviar Teste para Este Aparelho</span>
        </button>
      </div>

    </div>

    <!-- Mensagem de Ajuda do Navegador (se bloqueado) -->
    <div id="push-permission-warning"
      class="hidden p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 text-xs flex items-center gap-2.5">
      <i class="fa-solid fa-triangle-exclamation text-base shrink-0"></i>
      <span class="leading-relaxed">
        As notificações estão <strong>bloqueadas</strong> nas permissões do seu navegador. Para ativá-las, toque no
        ícone de cadeado / configurações na barra de endereços e autorize as Notificações.
      </span>
    </div>
  </div>

  <!-- Alterar Senha de Acesso -->
  <div class="card-decesaro p-6">
    <h3 class="text-base font-bold font-serif text-slate-900 dark:text-white mb-1">
      Segurança e Senha de Acesso
    </h3>
    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
      Mantenha sua conta protegida com uma senha forte.
    </p>

    <form action="/desenvolvimento/perfil/senha" method="POST" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <?= $csrfField ?>

      <div>
        <label for="current_password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Senha
          Atual</label>
        <input type="password" id="current_password" name="current_password" required placeholder="••••••••"
          class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
      </div>

      <div>
        <label for="new_password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nova
          Senha</label>
        <input type="password" id="new_password" name="new_password" required placeholder="Mínimo 8 caracteres"
          class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
      </div>

      <div>
        <label for="confirm_password"
          class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Confirmar Nova Senha</label>
        <input type="password" id="confirm_password" name="confirm_password" required placeholder="Repita a nova senha"
          class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
      </div>

      <div class="sm:col-span-3 flex justify-end pt-2">
        <button type="submit"
          class="px-5 py-2 rounded-xl bg-navy-900 hover:bg-navy-800 dark:bg-amber-500 dark:hover:bg-amber-600 text-white dark:text-slate-950 font-bold text-xs shadow-md transition-all">
          Atualizar Senha
        </button>
      </div>
    </form>
  </div>

</div>

<!-- Modal Edição de Nome do Participante -->
<div id="edit-profile-modal"
  class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
  <div
    class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-base font-bold font-serif text-slate-900 dark:text-white">Editar Meus Dados</h3>
      <button onclick="document.getElementById('edit-profile-modal').classList.add('hidden')"
        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="/desenvolvimento/perfil/atualizar" method="POST" class="space-y-4">
      <?= $csrfField ?>

      <div>
        <label for="edit_nome" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nome
          Completo</label>
        <input type="text" id="edit_nome" name="nome" value="<?= \App\Helpers\Security::e($user['nome']) ?>" required
          class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
      </div>

      <p class="text-[11px] text-slate-400">
        Nota: Geração, núcleo familiar e tipo de usuário são administrados exclusivamente pelo Comitê.
      </p>

      <div class="flex items-center justify-end gap-2 pt-2">
        <button type="button" onclick="document.getElementById('edit-profile-modal').classList.add('hidden')"
          class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300">
          Cancelar
        </button>
        <button type="submit"
          class="px-5 py-2 rounded-lg bg-navy-900 dark:bg-amber-500 text-white dark:text-slate-950 text-xs font-bold shadow-md">
          Salvar Alterações
        </button>
      </div>
    </form>
  </div>
</div>