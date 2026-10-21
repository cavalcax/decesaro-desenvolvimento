<?php
/** @var array $categorias */
?>

<div class="space-y-6">

  <!-- Header -->
  <div class="card-decesaro p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
      <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Administração</span>
      <h1 class="text-2xl font-bold font-serif text-slate-900 dark:text-white mt-0.5">Gestão de Agenda e Eventos</h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Publicação de ações de desenvolvimento, encontros da família e controle de presenças</p>
    </div>
    <button onclick="openModalCreate()" class="px-4 py-2.5 rounded-xl bg-navy-900 hover:bg-navy-800 dark:bg-amber-500 dark:hover:bg-amber-600 text-white dark:text-slate-950 text-xs font-bold shadow-md transition-all flex items-center gap-2">
      <i class="fa-solid fa-calendar-plus text-xs"></i>
      <span>Novo Evento</span>
    </button>
  </div>

  <!-- Card Filtrar e Buscar -->
  <div class="card-decesaro p-4 flex flex-wrap items-end gap-3">
    <div class="w-full sm:w-60">
      <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Filtrar por Categoria</label>
      <select id="filter-categoria" class="select2" onchange="applyFilters()">
        <option value="">Todas as Categorias</option>
        <?php foreach ($categorias as $cat): ?>
          <option value="<?= $cat['id'] ?>"><?= \App\Helpers\Security::e($cat['nome']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="flex-1 min-w-[200px]">
      <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Buscar Evento</label>
      <div class="relative">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
          <i class="fa-solid fa-magnifying-glass text-xs"></i>
        </span>
        <input type="text" id="filter-search" class="w-full pl-9 pr-3 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50" placeholder="Pesquisar por título, local ou responsável...">
      </div>
    </div>
  </div>

  <!-- Tabela Dinâmica -->
  <div id="agenda-table-container"></div>

</div>

<!-- Modal com Grid de 12 Colunas (Section 22) -->
<div id="modal-evento" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 animate-in fade-in duration-200 max-h-[90vh] overflow-y-auto">
    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700 mb-4">
      <h3 id="modal-title" class="text-base font-bold font-serif text-slate-900 dark:text-white">Novo Evento</h3>
      <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form id="form-evento" onsubmit="saveEvento(event)">
      <input type="hidden" id="e-id" name="id">

      <!-- Grid de 12 Colunas -->
      <div class="grid grid-cols-12 gap-4 text-xs">
        
        <!-- Título: 6 cols -->
        <div class="col-span-12 sm:col-span-6">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Título do Evento *</label>
          <input type="text" id="e-titulo" name="titulo" required placeholder="Ex: Encontro da Família"
                 class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
        </div>

        <!-- Categoria: 6 cols -->
        <div class="col-span-12 sm:col-span-6">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Categoria *</label>
          <select id="e-id-categoria" name="id_categoria" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
            <option value="">Selecione...</option>
            <?php foreach ($categorias as $cat): ?>
              <option value="<?= $cat['id'] ?>"><?= \App\Helpers\Security::e($cat['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Data Início: 6 cols -->
        <div class="col-span-12 sm:col-span-6">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Data & Hora de Início *</label>
          <input type="datetime-local" id="e-data-inicio" name="data_inicio" required
                 class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
        </div>

        <!-- Data Fim: 6 cols -->
        <div class="col-span-12 sm:col-span-6">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Data & Hora de Término</label>
          <input type="datetime-local" id="e-data-fim" name="data_fim"
                 class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
        </div>

        <!-- Local: 6 cols -->
        <div class="col-span-12 sm:col-span-6">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Local Físico</label>
          <input type="text" id="e-local" name="local" placeholder="Ex: Xangri-lá, RS ou Auditório Corporativo"
                 class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
        </div>

        <!-- Link Online: 6 cols -->
        <div class="col-span-12 sm:col-span-6">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Link de Acesso Online (se houver)</label>
          <input type="url" id="e-link-online" name="link_online" placeholder="https://meet.google.com/..."
                 class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
        </div>

        <!-- Responsável: 4 cols -->
        <div class="col-span-12 sm:col-span-4">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Responsável / Comitê</label>
          <input type="text" id="e-responsavel" name="responsavel" placeholder="Ex: Comitê de Desenvolvimento"
                 class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
        </div>

        <!-- Situação: 4 cols -->
        <div class="col-span-12 sm:col-span-4">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Situação *</label>
          <select id="e-situacao" name="situacao" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
            <option value="Confirmado">Confirmado</option>
            <option value="Pendente">Pendente</option>
            <option value="Cancelado">Cancelado</option>
          </select>
        </div>

        <!-- Limite de Vagas: 4 cols -->
        <div class="col-span-12 sm:col-span-4">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Limite de Vagas (opcional)</label>
          <input type="number" id="e-limite-vagas" name="limite_vagas" min="1" placeholder="Em branco = ilimitado"
                 class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
        </div>

        <!-- Descrição: 12 cols -->
        <div class="col-span-12">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Descrição / Pauta do Evento</label>
          <textarea id="e-descricao" name="descricao" rows="3" placeholder="Informações aos participantes, tema, horários e objetivos..."
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50"></textarea>
        </div>

      </div>

      <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-2">
        <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300">
          Cancelar
        </button>
        <button type="submit" class="px-5 py-2 rounded-lg bg-navy-900 dark:bg-amber-500 text-white dark:text-slate-950 font-bold text-xs shadow-md">
          Salvar Evento
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Modal de Gerenciamento de Inscritos e Presença -->
<div id="modal-inscritos" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 max-h-[85vh] flex flex-col">
    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700 mb-3">
      <div>
        <h3 id="inscritos-event-title" class="text-base font-bold font-serif text-slate-900 dark:text-white"></h3>
        <p class="text-xs text-slate-400">Lista de participantes inscritos e chamada de presença</p>
      </div>
      <button onclick="document.getElementById('modal-inscritos').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div class="flex-1 overflow-y-auto">
      <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
        <thead class="bg-slate-50 dark:bg-slate-900/60 uppercase text-[10px] tracking-wider text-slate-400 border-b border-slate-100 dark:border-slate-800">
          <tr>
            <th class="py-2.5 px-3">Participante</th>
            <th class="py-2.5 px-3">Família</th>
            <th class="py-2.5 px-3">Geração</th>
            <th class="py-2.5 px-3">Status</th>
            <th class="py-2.5 px-3 text-center">Presença</th>
          </tr>
        </thead>
        <tbody id="inscritos-table-body" class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr><td colspan="5" class="text-center py-6 text-slate-400">Carregando inscritos...</td></tr>
        </tbody>
      </table>
    </div>

    <div class="pt-3 border-t border-slate-100 dark:border-slate-700 flex justify-end">
      <button onclick="document.getElementById('modal-inscritos').classList.add('hidden')" class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300">
        Fechar
      </button>
    </div>
  </div>
</div>

<script>
let tableInstance = null;

document.addEventListener('DOMContentLoaded', () => {
  tableInstance = new AjaxTable({
    container: '#agenda-table-container',
    searchInput: '#filter-search',
    endpoint: '/desenvolvimento/admin/agenda',
    columns: [
      {
        key: 'data_inicio',
        label: 'Data',
        sortable: false,
        render: (val) => {
          const d = new Date(val);
          return `<span class="font-mono text-xs font-bold text-slate-800 dark:text-slate-200">${d.toLocaleDateString('pt-BR')} ${d.toLocaleTimeString('pt-BR', {hour:'2-digit', minute:'2-digit'})}</span>`;
        }
      },
      {
        key: 'titulo',
        label: 'Evento',
        sortable: false,
        render: (val, row) => `
          <div>
            <span class="font-bold text-slate-800 dark:text-white block">${escapeHtml(val)}</span>
            <span class="text-[11px] text-slate-400">${escapeHtml(row.local || 'Online / A definir')}</span>
          </div>
        `
      },
      { key: 'categoria_nome', label: 'Categoria', sortable: false, render: (val, row) => `<span class="px-2 py-0.5 rounded text-[10px] font-bold text-white" style="background-color: ${row.categoria_cor || '#1b3b5f'}">${escapeHtml(val)}</span>` },
      {
        key: 'inscritos_count',
        label: 'Inscritos / Vagas',
        sortable: false,
        render: (val, row) => `<span class="font-mono text-xs font-bold text-amber-500">${val}</span> <span class="text-slate-400 text-[11px]">/ ${row.limite_vagas || 'Ilimitado'}</span>`
      },
      {
        key: 'situacao',
        label: 'Situação',
        sortable: false,
        render: (val) => `<span class="px-2 py-0.5 rounded text-[10px] font-bold ${val === 'Confirmado' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300'}">${val}</span>`
      },
      {
        key: 'id',
        label: 'Ações',
        sortable: false,
        render: (val, row) => `
          <div class="flex items-center gap-1.5">
            <button onclick='openInscritosModal(${val}, "${escapeHtml(row.titulo)}")' class="px-2 py-1 rounded bg-amber-500/10 hover:bg-amber-500 text-amber-600 hover:text-slate-900 text-xs font-bold transition-colors" title="Gerenciar Inscritos e Presença">
              <i class="fa-solid fa-users-check mr-1"></i>Presenças
            </button>
            <button onclick='openModalEdit(${JSON.stringify(row)})' class="p-1.5 text-slate-400 hover:text-amber-500 text-xs" title="Editar">
              <i class="fa-solid fa-pen-to-square"></i>
            </button>
          </div>
        `
      }
    ]
  });
});

function applyFilters() {
  if (tableInstance) {
    tableInstance.setFilters({
      categoria_id: document.getElementById('filter-categoria').value
    });
  }
}

function openModalCreate() {
  document.getElementById('form-evento').reset();
  document.getElementById('e-id').value = '';
  document.getElementById('modal-title').textContent = 'Novo Evento';
  document.getElementById('modal-evento').classList.remove('hidden');
}

function openModalEdit(row) {
  document.getElementById('form-evento').reset();
  document.getElementById('e-id').value = row.id;
  document.getElementById('e-titulo').value = row.titulo;
  document.getElementById('e-id-categoria').value = row.id_categoria;
  document.getElementById('e-data-inicio').value = row.data_inicio ? row.data_inicio.replace(' ', 'T').substring(0, 16) : '';
  document.getElementById('e-data-fim').value = row.data_fim ? row.data_fim.replace(' ', 'T').substring(0, 16) : '';
  document.getElementById('e-local').value = row.local || '';
  document.getElementById('e-link-online').value = row.link_online || '';
  document.getElementById('e-responsavel').value = row.responsavel || '';
  document.getElementById('e-situacao').value = row.situacao || 'Confirmado';
  document.getElementById('e-limite-vagas').value = row.limite_vagas || '';
  document.getElementById('e-descricao').value = row.descricao || '';
  document.getElementById('modal-title').textContent = 'Editar Evento';
  document.getElementById('modal-evento').classList.remove('hidden');
}

function closeModal() {
  document.getElementById('modal-evento').classList.add('hidden');
}

async function saveEvento(e) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);
  const data = Object.fromEntries(formData.entries());

  try {
    const res = await apiRequest('/desenvolvimento/admin/agenda/salvar', {
      method: 'POST',
      data: data
    });
    closeModal();
    showToast('success', res.message);
    tableInstance.load();
  } catch {}
}

async function openInscritosModal(eventoId, title) {
  document.getElementById('inscritos-event-title').textContent = title;
  const tbody = document.getElementById('inscritos-table-body');
  tbody.innerHTML = '<tr><td colspan="5" class="text-center py-6 text-slate-400">Carregando inscritos...</td></tr>';
  document.getElementById('modal-inscritos').classList.remove('hidden');

  try {
    const res = await apiRequest(`/desenvolvimento/admin/agenda/inscritos/${eventoId}`, { loading: false });
    const inscritos = res.data || [];

    if (inscritos.length === 0) {
      tbody.innerHTML = '<tr><td colspan="5" class="text-center py-6 text-slate-400">Nenhum participante inscrito neste evento.</td></tr>';
      return;
    }

    tbody.innerHTML = '';
    inscritos.forEach(ins => {
      const tr = document.createElement('tr');
      tr.className = 'hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors';
      tr.innerHTML = `
        <td class="py-2.5 px-3 font-bold text-slate-800 dark:text-slate-200">${escapeHtml(ins.usuario_nome)}</td>
        <td class="py-2.5 px-3">Família ${escapeHtml(ins.familia)}</td>
        <td class="py-2.5 px-3">${escapeHtml(ins.geracao)}</td>
        <td class="py-2.5 px-3">
          <span class="px-2 py-0.5 rounded text-[10px] font-bold ${ins.status === 'inscrito' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-600'}">${ins.status}</span>
        </td>
        <td class="py-2.5 px-3 text-center">
          <button onclick="togglePresenca(${ins.id}, '${ins.presenca === 'S' ? 'N' : 'S'}')" class="px-2.5 py-1 rounded text-xs font-bold transition-all ${ins.presenca === 'S' ? 'bg-emerald-500 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-700 text-slate-500 hover:bg-slate-200'}">
            ${ins.presenca === 'S' ? '<i class="fa-solid fa-check mr-1"></i>Presente' : 'Marcar'}
          </button>
        </td>
      `;
      tbody.appendChild(tr);
    });

  } catch (e) {
    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-6 text-red-500">Falha ao carregar inscritos.</td></tr>';
  }
}

async function togglePresenca(inscricaoId, presenca) {
  try {
    await apiRequest('/desenvolvimento/admin/agenda/presenca', {
      method: 'POST',
      data: { inscricao_id: inscricaoId, presenca: presenca },
      loading: false
    });
    showToast('success', 'Presença atualizada!');
    // Re-abrir lista
    const activeTitle = document.getElementById('inscritos-event-title').textContent;
  } catch {}
}
</script>
