<div class="space-y-6">

  <!-- Header -->
  <div class="card-decesaro p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
      <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Administração</span>
      <h1 class="text-2xl font-bold font-serif text-slate-900 dark:text-white mt-0.5">Gestão de Raias</h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Eixos temáticos de desenvolvimento (Família, Propriedade, Negócios, Competências)</p>
    </div>
    <button onclick="openModalCreate()" class="px-4 py-2.5 rounded-xl bg-navy-900 hover:bg-navy-800 dark:bg-amber-500 dark:hover:bg-amber-600 text-white dark:text-slate-950 text-xs font-bold shadow-md transition-all flex items-center gap-2">
      <i class="fa-solid fa-plus text-xs"></i>
      <span>Nova Raia</span>
    </button>
  </div>

  <!-- Card Filtrar e Buscar -->
  <div class="card-decesaro p-4 flex flex-wrap items-end gap-3">
    <div class="flex-1 min-w-[200px]">
      <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Buscar Raia</label>
      <div class="relative">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
          <i class="fa-solid fa-magnifying-glass text-xs"></i>
        </span>
        <input type="text" id="filter-search" class="w-full pl-9 pr-3 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50" placeholder="Pesquisar por título, subtítulo ou slug...">
      </div>
    </div>
  </div>

  <!-- Tabela Dinâmica -->
  <div id="raias-table-container"></div>

</div>

<!-- Modal com Grid de 12 Colunas (Section 22) -->
<div id="modal-raia" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 animate-in fade-in duration-200">
    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700 mb-4">
      <h3 id="modal-title" class="text-base font-bold font-serif text-slate-900 dark:text-white">Nova Raia</h3>
      <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form id="form-raia" onsubmit="saveRaia(event)">
      <input type="hidden" id="r-id" name="id">

      <!-- Grid de 12 Colunas -->
      <div class="grid grid-cols-12 gap-4 text-xs">
        
        <!-- Descrição: 6 cols -->
        <div class="col-span-12 sm:col-span-6">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Título da Raia *</label>
          <input type="text" id="r-descricao" name="descricao" required placeholder="Ex: Família"
                 class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
        </div>

        <!-- Subtítulo: 6 cols -->
        <div class="col-span-12 sm:col-span-6">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Subtítulo Conceitual</label>
          <input type="text" id="r-subtitulo" name="subtitulo" placeholder="Ex: De onde viemos"
                 class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
        </div>

        <!-- Slug: 6 cols -->
        <div class="col-span-12 sm:col-span-6">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Slug (URL) *</label>
          <input type="text" id="r-slug" name="slug" required placeholder="Ex: familia"
                 class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
        </div>

        <!-- Idade Mínima: 6 cols -->
        <div class="col-span-12 sm:col-span-6">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Idade Mínima (anos) *</label>
          <input type="number" id="r-idade-min" name="idade_minima" required min="0" max="120" value="0"
                 class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
        </div>

        <!-- Idade Máxima: 6 cols -->
        <div class="col-span-12 sm:col-span-6">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Idade Máxima (opcional)</label>
          <input type="number" id="r-idade-max" name="idade_maxima" min="0" max="120" placeholder="Sem limite"
                 class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
        </div>

        <!-- Ícone: 3 cols -->
        <div class="col-span-12 sm:col-span-3">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Ícone FontAwesome</label>
          <input type="text" id="r-icone" name="icone" placeholder="fa-users"
                 class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
        </div>

        <!-- Ordem: 3 cols -->
        <div class="col-span-12 sm:col-span-3">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Ordem Exibição</label>
          <input type="number" id="r-ordem" name="ordem" min="0" value="0"
                 class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
        </div>

      </div>

      <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-2">
        <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300">
          Cancelar
        </button>
        <button type="submit" class="px-5 py-2 rounded-lg bg-navy-900 dark:bg-amber-500 text-white dark:text-slate-950 font-bold text-xs shadow-md">
          Salvar Raia
        </button>
      </div>
    </form>
  </div>
</div>

<script>
let tableInstance = null;

document.addEventListener('DOMContentLoaded', () => {
  tableInstance = new AjaxTable({
    container: '#raias-table-container',
    searchInput: '#filter-search',
    endpoint: '/desenvolvimento/admin/raias',
    columns: [
      { key: 'ordem', label: 'Ordem', sortable: false, render: (val) => `<span class="font-mono font-bold text-amber-500">#${val}</span>` },
      {
        key: 'descricao',
        label: 'Título',
        sortable: false,
        render: (val, row) => `
          <div class="flex items-center gap-2">
            <i class="fa-solid ${escapeHtml(row.icone || 'fa-folder')} text-slate-400"></i>
            <span class="font-bold text-slate-800 dark:text-white">${escapeHtml(val)}</span>
          </div>
        `
      },
      { key: 'subtitulo', label: 'Subtítulo', sortable: false },
      { key: 'slug', label: 'Slug / URL', sortable: false, render: (val) => `<code class="text-[11px] bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded">/${val}</code>` },
      {
        key: 'idade_minima',
        label: 'Acesso por Idade',
        sortable: false,
        render: (val, row) => {
          const min = val || 0;
          const max = row.idade_maxima;
          if (min == 0 && !max) {
            return `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">Todas as idades</span>`;
          }
          if (max) {
            return `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">${min} a ${max} anos</span>`;
          }
          return `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">${min}+ anos</span>`;
        }
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
            <button onclick="deleteRaia(${val})" class="p-1.5 text-slate-400 hover:text-red-500 text-xs" title="Excluir">
              <i class="fa-solid fa-trash-can"></i>
            </button>
          </div>
        `
      }
    ]
  });
});

function openModalCreate() {
  document.getElementById('form-raia').reset();
  document.getElementById('r-id').value = '';
  document.getElementById('r-idade-min').value = '0';
  document.getElementById('r-idade-max').value = '';
  document.getElementById('modal-title').textContent = 'Nova Raia';
  document.getElementById('modal-raia').classList.remove('hidden');
}

function openModalEdit(row) {
  document.getElementById('form-raia').reset();
  document.getElementById('r-id').value = row.id;
  document.getElementById('r-descricao').value = row.descricao;
  document.getElementById('r-subtitulo').value = row.subtitulo || '';
  document.getElementById('r-slug').value = row.slug;
  document.getElementById('r-idade-min').value = row.idade_minima ?? 0;
  document.getElementById('r-idade-max').value = row.idade_maxima ?? '';
  document.getElementById('r-icone').value = row.icone || '';
  document.getElementById('r-ordem').value = row.ordem || 0;
  document.getElementById('modal-title').textContent = 'Editar Raia';
  document.getElementById('modal-raia').classList.remove('hidden');
}

function closeModal() {
  document.getElementById('modal-raia').classList.add('hidden');
}

async function saveRaia(e) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);
  const data = Object.fromEntries(formData.entries());

  try {
    const res = await apiRequest('/desenvolvimento/admin/raias/salvar', {
      method: 'POST',
      data: data
    });
    closeModal();
    showToast('success', res.message);
    tableInstance.load();
  } catch {}
}

async function deleteRaia(id) {
  const result = await Swal.fire({
    title: 'Excluir Raia?',
    text: 'Esta ação excluirá a raia e todos os conteúdos associados.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#1b3b5f',
    confirmButtonText: 'Sim, excluir',
    cancelButtonText: 'Cancelar'
  });

  if (result.isConfirmed) {
    try {
      const res = await apiRequest('/desenvolvimento/admin/raias/excluir', {
        method: 'POST',
        data: { id }
      });
      showToast('success', res.message);
      tableInstance.load();
    } catch {}
  }
}
</script>
