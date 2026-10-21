<?php
/** @var array $stats */
/** @var array $usuarios */
?>

<div class="space-y-6">

  <!-- Cabeçalho e Título -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-1">
        <a href="/desenvolvimento/admin" class="hover:underline">Administração</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-slate-700 dark:text-slate-200 font-semibold">Central de Notificações</span>
      </div>
      <h1 class="text-2xl font-bold font-serif text-slate-900 dark:text-white">
        Envio de Notificações e Push
      </h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
        Dispare avisos no sistema e mensagens Push PWA para os celulares e computadores dos familiares.
      </p>
    </div>

    <div class="flex items-center gap-2">
      <button type="button" onclick="document.getElementById('form-envio-card').scrollIntoView({ behavior: 'smooth' })"
        class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs shadow-md transition-all inline-flex items-center gap-2">
        <i class="fa-solid fa-paper-plane"></i>
        <span>Nova Notificação</span>
      </button>
    </div>
  </div>

  <!-- Cards de Indicadores (KPIs) -->
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

    <div class="card-decesaro p-4 flex items-center gap-4">
      <div
        class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl shrink-0">
        <i class="fa-solid fa-tower-broadcast"></i>
      </div>
      <div>
        <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium leading-none">Inscrições Push Ativas</p>
        <p class="text-2xl font-bold font-serif text-slate-900 dark:text-white mt-1.5">
          <?= number_format($stats['total_inscricoes'] ?? 0, 0, ',', '.') ?></p>
        <p class="text-[10px] text-slate-400 mt-0.5">Dispositivos conectados</p>
      </div>
    </div>

    <div class="card-decesaro p-4 flex items-center gap-4">
      <div
        class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl shrink-0">
        <i class="fa-solid fa-mobile-screen-button"></i>
      </div>
      <div>
        <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium leading-none">Usuários com Push</p>
        <p class="text-2xl font-bold font-serif text-slate-900 dark:text-white mt-1.5">
          <?= number_format($stats['usuarios_com_push'] ?? 0, 0, ',', '.') ?></p>
        <p class="text-[10px] text-slate-400 mt-0.5">Familiares habilitados</p>
      </div>
    </div>

    <div class="card-decesaro p-4 flex items-center gap-4">
      <div
        class="w-12 h-12 rounded-2xl bg-blue-500/10 border border-blue-500/20 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xl shrink-0">
        <i class="fa-solid fa-bullhorn"></i>
      </div>
      <div>
        <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium leading-none">Campanhas Disparadas</p>
        <p class="text-2xl font-bold font-serif text-slate-900 dark:text-white mt-1.5">
          <?= number_format($stats['total_campanhas'] ?? 0, 0, ',', '.') ?></p>
        <p class="text-[10px] text-slate-400 mt-0.5">Histórico administrativo</p>
      </div>
    </div>

    <div class="card-decesaro p-4 flex items-center gap-4">
      <div
        class="w-12 h-12 rounded-2xl bg-purple-500/10 border border-purple-500/20 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl shrink-0">
        <i class="fa-solid fa-envelope-open-text"></i>
      </div>
      <div>
        <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium leading-none">Notificações Entregues</p>
        <p class="text-2xl font-bold font-serif text-slate-900 dark:text-white mt-1.5">
          <?= number_format($stats['total_notificacoes'] ?? 0, 0, ',', '.') ?></p>
        <p class="text-[10px] text-slate-400 mt-0.5">Total no sistema</p>
      </div>
    </div>

  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Formulário de Criação e Envio de Notificação -->
    <div id="form-envio-card" class="card-decesaro p-6 lg:col-span-2 space-y-5">
      <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
        <div class="flex items-center gap-2.5">
          <div
            class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm font-bold">
            <i class="fa-solid fa-paper-plane"></i>
          </div>
          <div>
            <h2 class="text-base font-bold font-serif text-slate-900 dark:text-white leading-tight">Compor Notificação
            </h2>
            <p class="text-[11px] text-slate-400">Preencha os dados da mensagem para envio em tempo real</p>
          </div>
        </div>
      </div>

      <form id="notif-send-form" class="space-y-4">
        <?= $csrfField ?>

        <!-- Título -->
        <div>
          <label for="titulo" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
            Título da Notificação <span class="text-red-500">*</span>
          </label>
          <input type="text" id="titulo" name="titulo" required maxlength="150"
            placeholder="Ex: Novo Encontro Familiar agendado!"
            class="w-full px-3.5 py-2.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
        </div>

        <!-- Mensagem -->
        <div>
          <div class="flex items-center justify-between mb-1">
            <label for="mensagem" class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
              Mensagem / Corpo <span class="text-red-500">*</span>
            </label>
            <span id="char-count" class="text-[10px] text-slate-400">0 / 250</span>
          </div>
          <textarea id="mensagem" name="mensagem" required rows="3" maxlength="250"
            placeholder="Ex: Não se esqueça de confirmar sua presença no Encontro da Família no próximo sábado..."
            class="w-full px-3.5 py-2.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50 resize-none"></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- Link de Destino -->
          <div>
            <label for="url" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
              Link de Destino / Ação (Opcional)
            </label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                <i class="fa-solid fa-link"></i>
              </span>
              <input type="text" id="url" name="url" placeholder="/desenvolvimento/agenda"
                class="w-full pl-8 pr-3.5 py-2.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
            </div>
            <p class="text-[10px] text-slate-400 mt-1">Ao tocar na notificação, o usuário será direcionado para cá.</p>
          </div>

          <!-- Tipo de Público-Alvo -->
          <div>
            <label for="target_type" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
              Público-Alvo <span class="text-red-500">*</span>
            </label>
            <select id="target_type"
              class="w-full px-3.5 py-2.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
              <option value="todos">Todos os Participantes Ativos</option>
              <option value="admin">Apenas Administradores (Comitê)</option>
              <option value="familia">Filtrar por Ramo Familiar...</option>
              <option value="geracao">Filtrar por Geração...</option>
              <option value="usuario">Participante Específico...</option>
            </select>
          </div>
        </div>

        <!-- Seletor Dinâmico do Público (escondido ou mostrado conforme target_type) -->
        <div id="target-detail-container"
          class="hidden p-3 rounded-xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-700">

          <!-- Seleção de Família -->
          <div id="target-familia-box" class="hidden">
            <label for="sub_familia"
              class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Selecione o Núcleo
              Familiar</label>
            <select id="sub_familia"
              class="w-full px-3 py-2 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg">
              <option value="Nestor">Nestor</option>
              <option value="Zeca">Zeca</option>
              <option value="Augusto">Augusto</option>
              <option value="Wilson">Wilson</option>
              <option value="Marisa">Marisa</option>
              <option value="Nane">Nane</option>
              <option value="Tati">Tati</option>
            </select>
          </div>

          <!-- Seleção de Geração -->
          <div id="target-geracao-box" class="hidden">
            <label for="sub_geracao"
              class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Selecione a Geração</label>
            <select id="sub_geracao"
              class="w-full px-3 py-2 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg">
              <option value="G1">Geração 1 (G1)</option>
              <option value="G2">Geração 2 (G2)</option>
              <option value="G3">Geração 3 (G3)</option>
            </select>
          </div>

          <!-- Seleção de Usuário Específico -->
          <div id="target-usuario-box" class="hidden">
            <label for="sub_usuario"
              class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Selecione o
              Participante</label>
            <select id="sub_usuario" class="w-full select2-basic">
              <option value="">Selecione um participante...</option>
              <?php foreach ($usuarios as $u): ?>
                <option value="<?= $u['id'] ?>">
                  <?= \App\Helpers\Security::e($u['nome']) ?> (Família <?= \App\Helpers\Security::e($u['familia']) ?> -
                  <?= \App\Helpers\Security::e($u['geracao']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

        </div>

        <input type="hidden" id="publico_alvo" name="publico_alvo" value="todos">

        <!-- Canais de Disparo -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">
            Canais de Entrega <span class="text-red-500">*</span>
          </label>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <label
              class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer transition-colors">
              <input type="checkbox" name="canais[]" value="sistema" checked
                class="w-4 h-4 text-amber-500 rounded focus:ring-amber-500/50">
              <div>
                <span class="block text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                  <i class="fa-solid fa-bell text-amber-500 text-xs"></i>
                  <span>Notificação no Sistema</span>
                </span>
                <span class="block text-[10px] text-slate-400">Modal do sino no cabeçalho</span>
              </div>
            </label>

            <label
              class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer transition-colors">
              <input type="checkbox" name="canais[]" value="push" checked
                class="w-4 h-4 text-amber-500 rounded focus:ring-amber-500/50">
              <div>
                <span class="block text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                  <i class="fa-solid fa-tower-broadcast text-amber-500 text-xs"></i>
                  <span>Push Notification (Web Push)</span>
                </span>
                <span class="block text-[10px] text-slate-400">Alerta direto no celular e desktop</span>
              </div>
            </label>
          </div>
        </div>

        <!-- Botão de Envio -->
        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end">
          <button type="submit" id="btn-submit-notif"
            class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-navy-900 hover:bg-navy-800 dark:bg-amber-500 dark:hover:bg-amber-600 text-white dark:text-slate-950 font-bold text-xs shadow-md transition-all inline-flex items-center justify-center gap-2">
            <i class="fa-solid fa-paper-plane"></i>
            <span>Disparar Notificação Agora</span>
          </button>
        </div>

      </form>
    </div>

    <!-- Pré-Visualização Dinâmica (Live Preview) -->
    <div class="card-decesaro p-6 flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
          <div class="flex items-center gap-2">
            <i class="fa-solid fa-eye text-amber-500 text-xs"></i>
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
              Pré-Visualização
            </h3>
          </div>
          <span class="text-[10px] text-slate-400">Tempo Real</span>
        </div>

        <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-3">
          Veja como a notificação será exibida na tela do celular ou computador do usuário:
        </p>

        <!-- Mockup Card de Notificação Push -->
        <div
          class="rounded-2xl bg-white/95 dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 p-3.5 shadow-xl space-y-2 backdrop-blur-md">
          <div class="flex items-center justify-between text-[10px] text-slate-400">
            <div class="flex items-center gap-1.5 font-semibold text-slate-600 dark:text-slate-300">
              <img src="/desenvolvimento/assets/img/apple-touch-icon.png" class="w-4 h-4 rounded-md object-cover"
                alt="App">
              <span>LEGADO E FUTURO</span>
            </div>
            <span>Agora</span>
          </div>

          <div class="flex items-start gap-3">
            <div
              class="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-600 to-amber-400 text-slate-950 flex items-center justify-center text-sm font-bold shrink-0 shadow-md">
              <i class="fa-solid fa-bell"></i>
            </div>
            <div class="flex-1 min-w-0">
              <h4 id="preview-title" class="text-xs font-bold text-slate-900 dark:text-white truncate">
                Título da Notificação
              </h4>
              <p id="preview-body"
                class="text-[11px] text-slate-600 dark:text-slate-300 mt-0.5 line-clamp-3 leading-relaxed">
                A mensagem da sua notificação aparecerá aqui conforme você digita.
              </p>
            </div>
          </div>

          <div
            class="pt-2 border-t border-slate-100 dark:border-slate-800/60 flex items-center justify-between text-[10px]">
            <span id="preview-target" class="text-amber-600 dark:text-amber-400 font-semibold">
              Destinatários: Todos os participantes
            </span>
            <span class="text-slate-400">Toque para abrir</span>
          </div>
        </div>
      </div>

      <!-- Dica de Teste Rápido -->
      <div class="mt-6 p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 text-xs">
        <p class="font-bold text-amber-800 dark:text-amber-300 flex items-center gap-1.5">
          <i class="fa-solid fa-lightbulb"></i>
          <span>Dica do Administrador</span>
        </p>
        <p class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
          Você pode verificar suas próprias notificações no sino do cabeçalho ou testar o recebimento no seu <a
            href="/desenvolvimento/perfil" class="underline font-bold hover:text-amber-500">Perfil</a>.
        </p>
      </div>

    </div>

  </div>

  <!-- Histórico das Últimas Campanhas Enviadas -->
  <div class="card-decesaro p-6 space-y-4">
    <div
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
      <div>
        <h3 class="text-base font-bold font-serif text-slate-900 dark:text-white">
          Histórico de Envios
        </h3>
        <p class="text-xs text-slate-400">Registro de todas as notificações e campanhas disparadas pelo comitê</p>
      </div>
      <div class="w-full sm:w-64">
        <input type="text" id="campanha-search" placeholder="Filtrar histórico..."
          class="w-full px-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
      </div>
    </div>

    <!-- Tabela Gerenciada por tables.js -->
    <div class="overflow-x-auto">
      <table id="tabela-campanhas" class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
        <thead
          class="text-[11px] uppercase bg-slate-50 dark:bg-slate-900/60 text-slate-700 dark:text-slate-300 border-b border-slate-200 dark:border-slate-700">
          <tr>
            <th class="py-3 px-3">Data / Hora</th>
            <th class="py-3 px-3">Título & Mensagem</th>
            <th class="py-3 px-3">Público-Alvo</th>
            <th class="py-3 px-3">Canais</th>
            <th class="py-3 px-3 text-center">Destinatários</th>
            <th class="py-3 px-3 text-center">Push Enviados</th>
            <th class="py-3 px-3">Autor</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
          <!-- Linhas carregadas via tables.js -->
        </tbody>
      </table>
    </div>

  </div>

</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('notif-send-form');
    const inputTitulo = document.getElementById('titulo');
    const inputMensagem = document.getElementById('mensagem');
    const charCount = document.getElementById('char-count');
    const targetType = document.getElementById('target_type');
    const targetDetail = document.getElementById('target-detail-container');
    const targetFamiliaBox = document.getElementById('target-familia-box');
    const targetGeracaoBox = document.getElementById('target-geracao-box');
    const targetUsuarioBox = document.getElementById('target-usuario-box');
    const publicoAlvoInput = document.getElementById('publico_alvo');
    const subFamilia = document.getElementById('sub_familia');
    const subGeracao = document.getElementById('sub_geracao');
    const subUsuario = document.getElementById('sub_usuario');

    const previewTitle = document.getElementById('preview-title');
    const previewBody = document.getElementById('preview-body');
    const previewTarget = document.getElementById('preview-target');

    // Atualização da Pré-visualização em tempo real
    function updatePreview() {
      previewTitle.textContent = inputTitulo.value.trim() || 'Título da Notificação';
      previewBody.textContent = inputMensagem.value.trim() || 'A mensagem da sua notificação aparecerá aqui conforme você digita.';
      charCount.textContent = `${inputMensagem.value.length} / 250`;

      let targetLabel = 'Todos os participantes';
      const val = targetType.value;
      if (val === 'todos') {
        publicoAlvoInput.value = 'todos';
        targetLabel = 'Todos os participantes';
      } else if (val === 'admin') {
        publicoAlvoInput.value = 'admin';
        targetLabel = 'Apenas Administradores (Comitê)';
      } else if (val === 'familia') {
        publicoAlvoInput.value = 'familia:' + subFamilia.value;
        targetLabel = 'Família ' + subFamilia.value;
      } else if (val === 'geracao') {
        publicoAlvoInput.value = 'geracao:' + subGeracao.value;
        targetLabel = 'Geração ' + subGeracao.value;
      } else if (val === 'usuario') {
        const uId = subUsuario.value;
        publicoAlvoInput.value = uId ? 'usuario:' + uId : 'todos';
        const uText = subUsuario.options[subUsuario.selectedIndex]?.text || 'Participante';
        targetLabel = uId ? uText : 'Selecione um participante';
      }
      previewTarget.textContent = 'Destinatários: ' + targetLabel;
    }

    inputTitulo.addEventListener('input', updatePreview);
    inputMensagem.addEventListener('input', updatePreview);

    targetType.addEventListener('change', () => {
      const val = targetType.value;
      targetDetail.classList.toggle('hidden', val === 'todos' || val === 'admin');
      targetFamiliaBox.classList.toggle('hidden', val !== 'familia');
      targetGeracaoBox.classList.toggle('hidden', val !== 'geracao');
      targetUsuarioBox.classList.toggle('hidden', val !== 'usuario');
      updatePreview();
    });

    subFamilia.addEventListener('change', updatePreview);
    subGeracao.addEventListener('change', updatePreview);
    $(subUsuario).on('change', updatePreview);

    // Inicializar Tabela com tables.js
    const table = new DataTableManager({
      tableSelector: '#tabela-campanhas',
      endpoint: '/desenvolvimento/admin/notificacoes',
      filterInputSelector: '#campanha-search',
      renderRow: (c) => {
        let canaisBadge = '';
        if (c.canais.includes('sistema')) {
          canaisBadge += '<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] bg-amber-500/10 text-amber-700 dark:text-amber-400 font-bold mr-1"><i class="fa-solid fa-bell text-[9px]"></i> Sistema</span>';
        }
        if (c.canais.includes('push')) {
          canaisBadge += '<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] bg-blue-500/10 text-blue-700 dark:text-blue-400 font-bold"><i class="fa-solid fa-tower-broadcast text-[9px]"></i> Push</span>';
        }

        return `
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
          <td class="py-3 px-3 whitespace-nowrap text-slate-500 dark:text-slate-400 font-mono text-[11px]">
            ${c.criado_em}
          </td>
          <td class="py-3 px-3 max-w-xs">
            <p class="font-bold text-slate-900 dark:text-white text-xs truncate">${c.titulo}</p>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">${c.mensagem}</p>
            ${c.url ? `<a href="${c.url}" class="text-[10px] text-amber-600 dark:text-amber-400 hover:underline mt-0.5 inline-block truncate max-w-xs"><i class="fa-solid fa-link text-[9px]"></i> ${c.url}</a>` : ''}
          </td>
          <td class="py-3 px-3 whitespace-nowrap font-medium text-slate-700 dark:text-slate-300">
            ${c.publico_alvo}
          </td>
          <td class="py-3 px-3 whitespace-nowrap">
            ${canaisBadge}
          </td>
          <td class="py-3 px-3 text-center font-bold text-slate-800 dark:text-slate-200">
            ${c.total_destinatarios}
          </td>
          <td class="py-3 px-3 text-center whitespace-nowrap">
            <span class="font-bold text-emerald-600 dark:text-emerald-400">${c.total_push_sucesso}</span>
            <span class="text-slate-400 text-[10px]">/ ${c.total_push_enviados}</span>
          </td>
          <td class="py-3 px-3 whitespace-nowrap text-slate-600 dark:text-slate-400 text-[11px]">
            ${c.autor_nome || 'Sistema'}
          </td>
        </tr>
      `;
      }
    });

    table.load();

    // Envio do Formulário
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      updatePreview();

      const titulo = inputTitulo.value.trim();
      const mensagem = inputMensagem.value.trim();

      if (!titulo || !mensagem) {
        showToast('error', 'Preencha o título e a mensagem da notificação.');
        return;
      }

      const confirm = await Swal.fire({
        title: 'Disparar Notificação?',
        html: `
        <div class="text-left text-xs space-y-2 mt-2">
          <p><strong>Título:</strong> ${titulo}</p>
          <p><strong>Destinatários:</strong> ${previewTarget.textContent.replace('Destinatários: ', '')}</p>
          <p class="text-amber-600 dark:text-amber-400">Esta ação disparará alertas para os dispositivos dos participantes selecionados.</p>
        </div>
      `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#03254A',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sim, disparar agora!',
        cancelButtonText: 'Cancelar'
      });

      if (!confirm.isConfirmed) return;

      try {
        const formData = new FormData(form);
        const res = await apiRequest('/desenvolvimento/admin/notificacoes/enviar', {
          method: 'POST',
          data: formData
        });

        if (res.success) {
          showToast('success', res.message);
          form.reset();
          targetType.value = 'todos';
          targetDetail.classList.add('hidden');
          updatePreview();
          table.load();
        }
      } catch (err) {
        // apiRequest trata erros automaticamente
      }
    });
  });
</script>