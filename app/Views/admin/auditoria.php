<div class="space-y-6">

  <!-- Header -->
  <div class="card-decesaro p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
      <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Segurança & Conformidade</span>
      <h1 class="text-2xl font-bold font-serif text-slate-900 dark:text-white mt-0.5">Trilha de Auditoria e Logs</h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Histórico completo de ações administrativas e alterações de dados na plataforma</p>
    </div>
  </div>

  <!-- Card Filtrar e Buscar -->
  <div class="card-decesaro p-4 flex flex-wrap items-end gap-3">
    <div class="flex-1 min-w-[200px]">
      <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Buscar na Auditoria</label>
      <div class="relative">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
          <i class="fa-solid fa-magnifying-glass text-xs"></i>
        </span>
        <input type="text" id="filter-search" class="w-full pl-9 pr-3 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50" placeholder="Pesquisar por ação, usuário, entidade ou IP...">
      </div>
    </div>
  </div>

  <!-- Tabela Dinâmica -->
  <div id="auditoria-table-container"></div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  new AjaxTable({
    container: '#auditoria-table-container',
    searchInput: '#filter-search',
    endpoint: '/desenvolvimento/admin/auditoria',
    columns: [
      {
        key: 'data_hora',
        label: 'Data / Horário',
        sortable: false,
        render: (val) => `<span class="font-mono text-xs text-slate-400">${new Date(val).toLocaleString('pt-BR')}</span>`
      },
      {
        key: 'usuario_nome',
        label: 'Usuário',
        sortable: false,
        render: (val, row) => `
          <div>
            <span class="font-bold text-slate-800 dark:text-white block">${escapeHtml(val || 'Sistema')}</span>
            <span class="text-[11px] text-slate-400 font-mono">${escapeHtml(row.usuario_email || '')}</span>
          </div>
        `
      },
      {
        key: 'acao',
        label: 'Ação Realizada',
        sortable: false,
        render: (val) => `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 font-mono">${escapeHtml(val)}</span>`
      },
      {
        key: 'entidade',
        label: 'Entidade Afetada',
        sortable: false,
        render: (val, row) => `<span class="font-mono text-xs text-slate-600 dark:text-slate-300">${escapeHtml(val)} ${row.entidade_id ? '#' + row.entidade_id : ''}</span>`
      },
      {
        key: 'ip',
        label: 'Endereço IP',
        sortable: false,
        render: (val) => `<span class="font-mono text-xs text-slate-400">${escapeHtml(val || '-')}</span>`
      }
    ]
  });
});
</script>
