<?php
/** @var array $raias */
/** @var array $niveis */
?>

<div class="space-y-6">

  <!-- Header -->
  <div class="card-decesaro p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
      <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Administração</span>
      <h1 class="text-2xl font-bold font-serif text-slate-900 dark:text-white mt-0.5">Gestão de Conteúdos das Raias</h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Organização dos módulos temáticos e associação com níveis de elegibilidade</p>
    </div>
    <button onclick="openModalCreate()" class="px-4 py-2.5 rounded-xl bg-navy-900 hover:bg-navy-800 dark:bg-amber-500 dark:hover:bg-amber-600 text-white dark:text-slate-950 text-xs font-bold shadow-md transition-all flex items-center gap-2">
      <i class="fa-solid fa-plus text-xs"></i>
      <span>Novo Conteúdo</span>
    </button>
  </div>

  <!-- Card Filtrar e Buscar -->
  <div class="card-decesaro p-4 flex flex-wrap items-end gap-3">
    <div class="w-full sm:w-60">
      <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Filtrar por Raia</label>
      <select id="filter-raia" class="select2" onchange="applyFilters()">
        <option value="">Todas as Raias</option>
        <?php foreach ($raias as $r): ?>
          <option value="<?= $r['id'] ?>"><?= \App\Helpers\Security::e($r['descricao']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="flex-1 min-w-[200px]">
      <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Buscar Conteúdo</label>
      <div class="relative">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
          <i class="fa-solid fa-magnifying-glass text-xs"></i>
        </span>
        <input type="text" id="filter-search" class="w-full pl-9 pr-3 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50" placeholder="Pesquisar por descrição ou subtítulo...">
      </div>
    </div>
  </div>

  <!-- Tabela Dinâmica -->
  <div id="conteudos-table-container"></div>

</div>

<!-- Modal com Grid de 12 Colunas (Section 22) -->
<div id="modal-conteudo" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 animate-in fade-in duration-200 max-h-[90vh] overflow-y-auto">
    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700 mb-4">
      <h3 id="modal-title" class="text-base font-bold font-serif text-slate-900 dark:text-white">Novo Conteúdo</h3>
      <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form id="form-conteudo" onsubmit="saveConteudo(event)">
      <input type="hidden" id="c-id" name="id">

      <!-- Grid de 12 Colunas -->
      <div class="grid grid-cols-12 gap-4 text-xs">
        
        <!-- Raia: 6 cols -->
        <div class="col-span-12 sm:col-span-6">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Raia de Desenvolvimento *</label>
          <select id="c-id-raia" name="id_raia" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
            <option value="">Selecione a raia...</option>
            <?php foreach ($raias as $r): ?>
              <option value="<?= $r['id'] ?>"><?= \App\Helpers\Security::e($r['descricao']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Descrição / Título: 6 cols -->
        <div class="col-span-12 sm:col-span-6">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Título do Conteúdo *</label>
          <input type="text" id="c-descricao" name="descricao" required placeholder="Ex: Nossa história"
                 class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
        </div>

        <!-- Subtítulo: 6 cols -->
        <div class="col-span-12 sm:col-span-6">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Subtítulo Resumido</label>
          <input type="text" id="c-subtitulo" name="subtitulo" placeholder="Ex: A trajetória da família De Cesaro"
                 class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
        </div>

        <!-- Ordem: 3 cols -->
        <div class="col-span-12 sm:col-span-3">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Ordem na Raia</label>
          <input type="number" id="c-ordem" name="ordem" min="0" value="0"
                 class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
        </div>

        <!-- Ativo: 3 cols -->
        <div class="col-span-12 sm:col-span-3">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Status</label>
          <select id="c-ativo" name="ativo" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
            <option value="S">Ativo (Visível)</option>
            <option value="N">Inativo</option>
          </select>
        </div>

        <!-- Níveis Elegíveis (Associação RaiaConteudoNivel): 12 cols -->
        <div class="col-span-12">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
            Níveis de Formação Elegíveis (RaiaConteudoNivel) *
          </label>
          <p class="text-[11px] text-slate-400 mb-1.5">Selecione quais faixas etárias têm acesso autorizado a este conteúdo:</p>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
            <?php foreach ($niveis as $n): ?>
              <label class="flex items-center gap-2 cursor-pointer p-1.5 rounded hover:bg-white dark:hover:bg-slate-800 transition-colors">
                <input type="checkbox" name="niveis[]" value="<?= $n['id'] ?>" class="c-nivel-check rounded border-slate-300 text-amber-500 focus:ring-amber-400">
                <span class="text-slate-700 dark:text-slate-300 font-medium"><?= \App\Helpers\Security::e($n['descricao']) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Detalhes: 12 cols -->
        <div class="col-span-12">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Descrição Detalhada / Objetivos</label>
          <textarea id="c-detalhes" name="detalhes" rows="3" placeholder="Apresentação do propósito, objetivos de aprendizagem e direcionamento..."
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50"></textarea>
        </div>

      </div>

      <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-2">
        <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300">
          Cancelar
        </button>
        <button type="submit" class="px-5 py-2 rounded-lg bg-navy-900 dark:bg-amber-500 text-white dark:text-slate-950 font-bold text-xs shadow-md">
          Salvar Conteúdo
        </button>
      </div>
    </form>
  </div>
</div>

<script>
let tableInstance = null;

document.addEventListener('DOMContentLoaded', () => {
  tableInstance = new AjaxTable({
    container: '#conteudos-table-container',
    searchInput: '#filter-search',
    endpoint: '/desenvolvimento/admin/conteudos',
    columns: [
      { key: 'ordem', label: 'Ordem', sortable: false, render: (val) => `<span class="font-mono text-amber-500 font-bold">#${val}</span>` },
      { key: 'raia_descricao', label: 'Raia', sortable: false, render: (val) => `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-navy-900/10 text-navy-800 dark:bg-amber-500/10 dark:text-amber-400">${escapeHtml(val)}</span>` },
      {
        key: 'descricao',
        label: 'Título',
        sortable: false,
        render: (val, row) => `
          <div>
            <span class="font-bold text-slate-800 dark:text-white block">${escapeHtml(val)}</span>
            <span class="text-[11px] text-slate-400">${escapeHtml(row.subtitulo || '')}</span>
          </div>
        `
      },
      {
        key: 'niveis_ids',
        label: 'Níveis Elegíveis',
        sortable: false,
        render: (val) => {
          if (!val) return '<span class="text-slate-400 italic text-[11px]">Nenhum nível vinculado</span>';
          const count = val.split(',').length;
          return `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300"><i class="fa-solid fa-check text-[9px]"></i> ${count} nível(is)</span>`;
        }
      },
      {
        key: 'ativo',
        label: 'Status',
        sortable: false,
        render: (val) => `<span class="px-2 py-0.5 rounded text-[10px] font-bold ${val === 'S' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'}">${val === 'S' ? 'Ativo' : 'Inativo'}</span>`
      },
      {
        key: 'id',
        label: 'Ações',
        sortable: false,
        render: (val, row) => `
          <div class="flex items-center gap-1.5">
            <button onclick='openModalEdit(${JSON.stringify(row)})' class="p-1.5 text-slate-400 hover:text-amber-500 text-xs" title="Editar">
              <i class="fa-solid fa-pen-to-square"></i>
            </button>
            <button onclick="deleteConteudo(${val})" class="p-1.5 text-slate-400 hover:text-red-500 text-xs" title="Excluir">
              <i class="fa-solid fa-trash-can"></i>
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
      raia_id: document.getElementById('filter-raia').value
    });
  }
}

function openModalCreate() {
  document.getElementById('form-conteudo').reset();
  document.getElementById('c-id').value = '';
  document.querySelectorAll('.c-nivel-check').forEach(c => c.checked = false);
  document.getElementById('modal-title').textContent = 'Novo Conteúdo';
  document.getElementById('modal-conteudo').classList.remove('hidden');
}

function openModalEdit(row) {
  document.getElementById('form-conteudo').reset();
  document.getElementById('c-id').value = row.id;
  document.getElementById('c-id-raia').value = row.id_raia;
  document.getElementById('c-descricao').value = row.descricao;
  document.getElementById('c-subtitulo').value = row.subtitulo || '';
  document.getElementById('c-detalhes').value = row.detalhes || '';
  document.getElementById('c-ordem').value = row.ordem || 0;
  document.getElementById('c-ativo').value = row.ativo || 'S';

  const linkedNiveis = (row.niveis_ids || '').split(',').map(Number);
  document.querySelectorAll('.c-nivel-check').forEach(cb => {
    cb.checked = linkedNiveis.includes(parseInt(cb.value));
  });

  document.getElementById('modal-title').textContent = 'Editar Conteúdo';
  document.getElementById('modal-conteudo').classList.remove('hidden');
}

function closeModal() {
  document.getElementById('modal-conteudo').classList.add('hidden');
}

async function saveConteudo(e) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);

  const selectedNiveis = [];
  document.querySelectorAll('.c-nivel-check:checked').forEach(c => selectedNiveis.push(parseInt(c.value)));

  const payload = {
    id: formData.get('id') || null,
    id_raia: formData.get('id_raia'),
    descricao: formData.get('descricao'),
    subtitulo: formData.get('subtitulo'),
    detalhes: formData.get('detalhes'),
    ordem: formData.get('ordem'),
    ativo: formData.get('ativo'),
    niveis: selectedNiveis
  };

  try {
    const res = await apiRequest('/desenvolvimento/admin/conteudos/salvar', {
      method: 'POST',
      data: payload
    });
    closeModal();
    showToast('success', res.message);
    tableInstance.load();
  } catch {}
}

async function deleteConteudo(id) {
  const result = await Swal.fire({
    title: 'Excluir Conteúdo?',
    text: 'Esta ação excluirá o conteúdo, os vídeos relacionados e as associações com níveis.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#1b3b5f',
    confirmButtonText: 'Sim, excluir',
    cancelButtonText: 'Cancelar'
  });

  if (result.isConfirmed) {
    try {
      const res = await apiRequest('/desenvolvimento/admin/conteudos/excluir', {
        method: 'POST',
        data: { id }
      });
      showToast('success', res.message);
      tableInstance.load();
    } catch {}
  }
}
</script>
