<div class="space-y-6">

  <!-- Header -->
  <div class="card-decesaro p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
      <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Administração</span>
      <h1 class="text-2xl font-bold font-serif text-slate-900 dark:text-white mt-0.5">Gestão de Níveis de Formação</h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Configuração dos níveis de formação e desenvolvimento</p>
    </div>
    <button onclick="openModalCreate()" class="px-4 py-2.5 rounded-xl bg-navy-900 hover:bg-navy-800 dark:bg-amber-500 dark:hover:bg-amber-600 text-white dark:text-slate-950 text-xs font-bold shadow-md transition-all flex items-center gap-2">
      <i class="fa-solid fa-plus text-xs"></i>
      <span>Novo Nível</span>
    </button>
  </div>

  <!-- Card Filtrar e Buscar -->
  <div class="card-decesaro p-4 flex flex-wrap items-end gap-3">
    <div class="flex-1 min-w-[200px]">
      <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Buscar Nível</label>
      <div class="relative">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
          <i class="fa-solid fa-magnifying-glass text-xs"></i>
        </span>
        <input type="text" id="filter-search" class="w-full pl-9 pr-3 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50" placeholder="Pesquisar por descrição...">
      </div>
    </div>
  </div>

  <!-- Tabela Dinâmica -->
  <div id="niveis-table-container"></div>

</div>

<!-- Modal com Grid de 12 Colunas (Section 22) -->
<div id="modal-nivel" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 animate-in fade-in duration-200">
    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700 mb-4">
      <h3 id="modal-title" class="text-base font-bold font-serif text-slate-900 dark:text-white">Novo Nível</h3>
      <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form id="form-nivel" onsubmit="saveNivel(event)">
      <input type="hidden" id="n-id" name="id">

      <!-- Grid de 12 Colunas -->
      <div class="grid grid-cols-12 gap-4 text-xs">
        
        <!-- Descrição: 12 cols -->
        <div class="col-span-12">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Descrição do Nível *</label>
          <input type="text" id="n-descricao" name="descricao" required placeholder="Ex: Juventude & Formação Inicial"
                 class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
        </div>

      </div>

      <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-2">
        <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300">
          Cancelar
        </button>
        <button type="submit" class="px-5 py-2 rounded-lg bg-navy-900 dark:bg-amber-500 text-white dark:text-slate-950 font-bold text-xs shadow-md">
          Salvar Nível
        </button>
      </div>
    </form>
  </div>
</div>

<script>
let tableInstance = null;

document.addEventListener('DOMContentLoaded', () => {
  tableInstance = new AjaxTable({
    container: '#niveis-table-container',
    searchInput: '#filter-search',
    endpoint: '/desenvolvimento/admin/niveis',
    columns: [
      { key: 'descricao', label: 'Descrição do Nível', sortable: false },
      {
        key: 'id',
        label: 'Ações',
        sortable: false,
        render: (val, row) => `
          <div class="flex items-center gap-1.5">
            <button onclick='openModalEdit(${JSON.stringify(row)})' class="p-1.5 text-slate-400 hover:text-amber-500 text-xs" title="Editar">
              <i class="fa-solid fa-pen-to-square"></i>
            </button>
            <button onclick="deleteNivel(${val})" class="p-1.5 text-slate-400 hover:text-red-500 text-xs" title="Excluir">
              <i class="fa-solid fa-trash-can"></i>
            </button>
          </div>
        `
      }
    ]
  });
});

function openModalCreate() {
  document.getElementById('form-nivel').reset();
  document.getElementById('n-id').value = '';
  document.getElementById('modal-title').textContent = 'Novo Nível';
  document.getElementById('modal-nivel').classList.remove('hidden');
}

function openModalEdit(row) {
  document.getElementById('form-nivel').reset();
  document.getElementById('n-id').value = row.id;
  document.getElementById('n-descricao').value = row.descricao;
  document.getElementById('modal-title').textContent = 'Editar Nível';
  document.getElementById('modal-nivel').classList.remove('hidden');
}

function closeModal() {
  document.getElementById('modal-nivel').classList.add('hidden');
}

async function saveNivel(e) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);
  const data = Object.fromEntries(formData.entries());

  try {
    const res = await apiRequest('/desenvolvimento/admin/niveis/salvar', {
      method: 'POST',
      data: data
    });
    closeModal();
    showToast('success', res.message);
    tableInstance.load();
  } catch {}
}

async function deleteNivel(id) {
  const result = await Swal.fire({
    title: 'Excluir Nível?',
    text: 'Esta ação afetará a regra de elegibilidade de conteúdos associados.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#1b3b5f',
    confirmButtonText: 'Sim, excluir',
    cancelButtonText: 'Cancelar'
  });

  if (result.isConfirmed) {
    try {
      const res = await apiRequest('/desenvolvimento/admin/niveis/excluir', {
        method: 'POST',
        data: { id }
      });
      showToast('success', res.message);
      tableInstance.load();
    } catch {}
  }
}
</script>
