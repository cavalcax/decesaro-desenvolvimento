<div class="space-y-6">

  <!-- Header -->
  <div class="card-decesaro p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
      <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Administração</span>
      <h1 class="text-2xl font-bold font-serif text-slate-900 dark:text-white mt-0.5">Gestão de Participantes</h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Cadastro, ramos familiares, gerações e permissões de
        acesso</p>
    </div>
    <button onclick="openModalCreate()"
      class="px-4 py-2.5 rounded-xl bg-navy-900 hover:bg-navy-800 dark:bg-amber-500 dark:hover:bg-amber-600 text-white dark:text-slate-950 text-xs font-bold shadow-md transition-all flex items-center gap-2">
      <i class="fa-solid fa-user-plus text-xs"></i>
      <span>Novo Participante</span>
    </button>
  </div>

  <!-- Card Filtrar e Buscar -->
  <div class="card-decesaro p-4 flex flex-wrap items-end gap-3">
    <div class="w-full sm:w-44">
      <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Núcleo</label>
      <select id="filter-familia" class="select2" onchange="applyFilters()">
        <option value="">Todas os Núcleos</option>
        <option value="Nestor">Nestor</option>
        <option value="Zeca">Zeca</option>
        <option value="Augusto">Augusto</option>
        <option value="Wilson">Wilson</option>
        <option value="Marisa">Marisa</option>
        <option value="Nane">Nane</option>
        <option value="Tati">Tati</option>
      </select>
    </div>

    <div class="w-full sm:w-44">
      <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Geração</label>
      <select id="filter-geracao" class="select2" onchange="applyFilters()">
        <option value="">Todas as Gerações</option>
        <option value="1ª Geração">1ª Geração</option>
        <option value="2ª Geração">2ª Geração</option>
        <option value="3ª Geração">3ª Geração</option>
        <option value="4ª Geração">4ª Geração</option>
      </select>
    </div>

    <div class="w-full sm:w-36">
      <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Perfil</label>
      <select id="filter-tipo" class="select2" onchange="applyFilters()">
        <option value="">Todos</option>
        <option value="Normal">Normal</option>
        <option value="Admin">Admin</option>
      </select>
    </div>

    <div class="flex-1 min-w-[200px]">
      <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Buscar Participante</label>
      <div class="relative">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
          <i class="fa-solid fa-magnifying-glass text-xs"></i>
        </span>
        <input type="text" id="filter-search"
          class="w-full pl-9 pr-3 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50"
          placeholder="Pesquisar por nome, email ou telefone...">
      </div>
    </div>
  </div>

  <!-- Tabela AJAX Dinâmica -->
  <div id="participantes-table-container"></div>

</div>

<!-- Modal com Grid de 12 Colunas (Section 22) -->
<div id="modal-participante"
  class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
  <div
    class="bg-white dark:bg-slate-800 rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 animate-in fade-in duration-200 max-h-[90vh] overflow-y-auto">
    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700 mb-4">
      <h3 id="modal-title" class="text-base font-bold font-serif text-slate-900 dark:text-white">Novo Participante</h3>
      <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form id="form-participante" onsubmit="saveParticipante(event)">
      <input type="hidden" id="p-id" name="id">

      <!-- Grid de 12 Colunas -->
      <div class="grid grid-cols-12 gap-4 text-xs">

        <!-- Nome: 6 cols -->
        <div class="col-span-12 sm:col-span-6">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nome Completo *</label>
          <input type="text" id="p-nome" name="nome" required placeholder="Ex: Nicolle De Cesaro"
            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
        </div>

        <!-- E-mail: 6 cols -->
        <div class="col-span-12 sm:col-span-6">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">E-mail *</label>
          <input type="email" id="p-email" name="email" required placeholder="nicolle@decesaro.com"
            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
        </div>

        <!-- Data de Nascimento: 4 cols -->
        <div class="col-span-12 sm:col-span-4">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nascimento *</label>
          <input type="date" id="p-data-nasc" name="data_nascimento" required
            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
        </div>

        <!-- Família: 4 cols -->
        <div class="col-span-12 sm:col-span-4">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Núcleo Familiar *</label>
          <select id="p-familia" name="familia"
            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
            <option value="Nestor">Nestor</option>
            <option value="Zeca">Zeca</option>
            <option value="Augusto">Augusto</option>
            <option value="Wilson">Wilson</option>
            <option value="Marisa">Marisa</option>
            <option value="Nane">Nane</option>
            <option value="Tati">Tati</option>
          </select>
        </div>

        <!-- Geração: 4 cols -->
        <div class="col-span-12 sm:col-span-4">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Geração *</label>
          <select id="p-geracao" name="geracao"
            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
            <option value="1ª Geração">1ª Geração</option>
            <option value="2ª Geração">2ª Geração</option>
            <option value="3ª Geração">3ª Geração</option>
            <option value="4ª Geração">4ª Geração</option>
          </select>
        </div>

        <!-- Tipo: 6 cols -->
        <div class="col-span-12 sm:col-span-6">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Perfil de Acesso *</label>
          <select id="p-tipo" name="tipo"
            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
            <option value="Normal">Normal (Participante)</option>
            <option value="Admin">Admin (Comitê)</option>
          </select>
        </div>

        <!-- Status: 6 cols -->
        <div class="col-span-12 sm:col-span-6">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Situação *</label>
          <select id="p-status" name="status"
            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
            <option value="Ativo">Ativo</option>
            <option value="Inativo">Inativo</option>
          </select>
        </div>

        <!-- Senha: 12 cols -->
        <div class="col-span-12">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
            Senha <span id="senha-help" class="text-slate-400 font-normal">(Deixe em branco para manter a atual se
              estiver editando)</span>
          </label>
          <input type="password" id="p-senha" name="senha"
            placeholder="Mínimo 8 caracteres, maiúscula, número e símbolo"
            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
        </div>

      </div>

      <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-2">
        <button type="button" onclick="closeModal()"
          class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300">
          Cancelar
        </button>
        <button type="submit"
          class="px-5 py-2 rounded-lg bg-navy-900 dark:bg-amber-500 text-white dark:text-slate-950 font-bold text-xs shadow-md">
          Salvar Participante
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  let tableInstance = null;

  document.addEventListener('DOMContentLoaded', () => {
    tableInstance = new AjaxTable({
      container: '#participantes-table-container',
      searchInput: '#filter-search',
      endpoint: '/desenvolvimento/admin/participantes',
      columns: [
        { key: 'nome', label: 'Nome', sortable: true },
        { key: 'email', label: 'E-mail', sortable: true },
        { key: 'familia', label: 'Núcleo', sortable: true, render: (val) => `<span class="font-medium text-slate-700 dark:text-slate-200">Núcleo ${escapeHtml(val)}</span>` },
        { key: 'geracao', label: 'Geração', sortable: true },
        { key: 'idade', label: 'Idade', sortable: false, render: (val) => `<span class="font-mono">${val} anos</span>` },
        { key: 'tipo', label: 'Perfil', sortable: true, render: (val) => `<span class="px-2 py-0.5 rounded text-[10px] font-bold ${val === 'Admin' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'}">${val}</span>` },
        { key: 'status', label: 'Status', sortable: true, render: (val) => `<span class="px-2 py-0.5 rounded text-[10px] font-bold ${val === 'Ativo' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300'}">${val}</span>` },
        {
          key: 'id',
          label: 'Ações',
          sortable: false,
          render: (val, row) => `
          <div class="flex items-center gap-1.5">
            <button onclick='openModalEdit(${JSON.stringify(row)})' class="p-1.5 text-slate-400 hover:text-amber-500 text-xs" title="Editar">
              <i class="fa-solid fa-pen-to-square"></i>
            </button>
            <button onclick="deleteParticipante(${val})" class="p-1.5 text-slate-400 hover:text-red-500 text-xs" title="Excluir">
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
        familia: document.getElementById('filter-familia').value,
        geracao: document.getElementById('filter-geracao').value,
        tipo: document.getElementById('filter-tipo').value
      });
    }
  }

  function openModalCreate() {
    document.getElementById('form-participante').reset();
    document.getElementById('p-id').value = '';
    document.getElementById('modal-title').textContent = 'Novo Participante';
    document.getElementById('senha-help').textContent = '(Padrão: DeCesaro@2026!)';
    document.getElementById('modal-participante').classList.remove('hidden');
  }

  function openModalEdit(row) {
    document.getElementById('form-participante').reset();
    document.getElementById('p-id').value = row.id;
    document.getElementById('p-nome').value = row.nome;
    document.getElementById('p-email').value = row.email;
    document.getElementById('p-data-nasc').value = row.data_nascimento;
    document.getElementById('p-familia').value = row.familia;
    document.getElementById('p-geracao').value = row.geracao;
    document.getElementById('p-tipo').value = row.tipo;
    document.getElementById('p-status').value = row.status;
    document.getElementById('modal-title').textContent = 'Editar Participante';
    document.getElementById('senha-help').textContent = '(Deixe em branco para não alterar)';
    document.getElementById('modal-participante').classList.remove('hidden');
  }

  function closeModal() {
    document.getElementById('modal-participante').classList.add('hidden');
  }

  async function saveParticipante(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    try {
      const res = await apiRequest('/desenvolvimento/admin/participantes/salvar', {
        method: 'POST',
        data: data
      });
      closeModal();
      showToast('success', res.message);
      tableInstance.load();
    } catch { }
  }

  async function deleteParticipante(id) {
    const result = await Swal.fire({
      title: 'Excluir Participante?',
      text: 'Esta ação removerá o participante e seus registros de progresso.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#1b3b5f',
      confirmButtonText: 'Sim, excluir',
      cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
      try {
        const res = await apiRequest('/desenvolvimento/admin/participantes/excluir', {
          method: 'POST',
          data: { id }
        });
        showToast('success', res.message);
        tableInstance.load();
      } catch { }
    }
  }
</script>