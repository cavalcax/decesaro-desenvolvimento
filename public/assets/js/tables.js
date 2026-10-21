/**
 * Componente Reutilizável de Tabela Dinâmica com AJAX
 * Plataforma de Desenvolvimento da Família De Cesaro
 */

function escapeHtml(str) {
  if (str === null || str === undefined) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

class AjaxTable {
  constructor(config) {
    this.container = typeof config.container === 'string' ? document.querySelector(config.container) : config.container;
    this.endpoint = config.endpoint;
    this.columns = config.columns; // [{ key, label, sortable, render }]
    this.filters = config.filters || {};
    this.page = 1;
    this.perPage = config.perPage || 10;
    this.sortBy = config.defaultSortBy || 'id';
    this.sortDir = config.defaultSortDir || 'desc';
    this.searchQuery = '';
    this.debounceTimer = null;
    this.onRowAction = config.onRowAction || null;

    // Seletor ou elemento de busca externo (dentro do card filtrar)
    this.searchInput = config.searchInput 
      ? (typeof config.searchInput === 'string' ? document.querySelector(config.searchInput) : config.searchInput)
      : document.querySelector('#filter-search');

    if (this.container) {
      this.init();
    }
  }

  init() {
    const hasExternalSearch = !!this.searchInput;

    this.container.innerHTML = `
      <div class="flex flex-col gap-4">
        ${!hasExternalSearch ? `
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div class="relative w-full sm:w-72">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
              <i class="fa-solid fa-magnifying-glass"></i>
            </span>
            <input type="text" class="table-search-input w-full pl-9 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50" placeholder="Pesquisar...">
          </div>
          <div class="table-extra-actions flex items-center gap-2"></div>
        </div>
        ` : ''}

        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm">
          <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
            <thead class="bg-slate-50 dark:bg-slate-900/60 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
              <tr class="table-header-row"></tr>
            </thead>
            <tbody class="table-body divide-y divide-slate-100 dark:divide-slate-700/50">
              <tr><td colspan="${this.columns.length}" class="text-center py-8 text-slate-400">Carregando dados...</td></tr>
            </tbody>
          </table>
        </div>

        <div class="table-pagination flex flex-wrap items-center justify-between gap-3 text-xs text-slate-500 dark:text-slate-400 px-1">
          <div class="table-info">Mostrando 0 de 0 registros</div>
          <div class="table-pagination-buttons flex items-center gap-1"></div>
        </div>
      </div>
    `;

    // Renderizar cabeçalhos
    const headerRow = this.container.querySelector('.table-header-row');
    this.columns.forEach(col => {
      const th = document.createElement('th');
      th.className = 'px-4 py-3 select-none';
      if (col.sortable !== false) {
        th.className += ' cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors';
        th.innerHTML = `
          <div class="flex items-center gap-1.5">
            <span>${escapeHtml(col.label)}</span>
            <span class="sort-icon text-slate-400 text-[10px]"><i class="fa-solid fa-sort"></i></span>
          </div>
        `;
        th.addEventListener('click', () => this.handleSort(col.key, th));
      } else {
        th.textContent = col.label;
      }
      headerRow.appendChild(th);
    });

    // Event listener busca com debounce (seja no card filtrar ou interno)
    const targetSearch = this.searchInput || this.container.querySelector('.table-search-input');
    if (targetSearch) {
      targetSearch.addEventListener('input', (e) => {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => {
          this.searchQuery = e.target.value.trim();
          this.page = 1;
          this.load();
        }, 300);
      });
    }

    this.load();
  }

  handleSort(key, thElement) {
    if (this.sortBy === key) {
      this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
    } else {
      this.sortBy = key;
      this.sortDir = 'asc';
    }

    // Atualizar ícones visuais
    const allIcons = this.container.querySelectorAll('.sort-icon i');
    allIcons.forEach(i => i.className = 'fa-solid fa-sort');

    const currentIcon = thElement.querySelector('.sort-icon i');
    if (currentIcon) {
      currentIcon.className = this.sortDir === 'asc' ? 'fa-solid fa-sort-up text-amber-500' : 'fa-solid fa-sort-down text-amber-500';
    }

    this.page = 1;
    this.load();
  }

  setFilters(filters) {
    this.filters = { ...this.filters, ...filters };
    this.page = 1;
    this.load();
  }

  async load() {
    const params = new URLSearchParams({
      page: this.page,
      per_page: this.perPage,
      sort: this.sortBy,
      dir: this.sortDir,
      search: this.searchQuery,
      ...this.filters
    });

    const tbody = this.container.querySelector('.table-body');

    try {
      const res = await apiRequest(`${this.endpoint}?${params.toString()}`, { loading: false });
      const items = res.data?.items || res.data || [];
      const total = res.data?.total || items.length;
      const totalPages = Math.ceil(total / this.perPage) || 1;

      if (items.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="${this.columns.length}" class="text-center py-12 text-slate-400">
              <i class="fa-regular fa-folder-open text-3xl mb-2 text-slate-300 dark:text-slate-600 block"></i>
              Nenhum registro encontrado.
            </td>
          </tr>
        `;
      } else {
        tbody.innerHTML = '';
        items.forEach(item => {
          const tr = document.createElement('tr');
          tr.className = 'hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors';

          this.columns.forEach(col => {
            const td = document.createElement('td');
            td.className = 'px-4 py-3 text-sm';
            if (typeof col.render === 'function') {
              td.innerHTML = col.render(item[col.key], item);
            } else {
              td.textContent = item[col.key] ?? '';
            }
            tr.appendChild(td);
          });

          tbody.appendChild(tr);
        });
      }

      // Atualizar paginação e info
      const start = total === 0 ? 0 : (this.page - 1) * this.perPage + 1;
      const end = Math.min(this.page * this.perPage, total);
      this.container.querySelector('.table-info').textContent = `Mostrando ${start} a ${end} de ${total} registros`;

      this.renderPagination(totalPages);

    } catch (err) {
      tbody.innerHTML = `
        <tr>
          <td colspan="${this.columns.length}" class="text-center py-8 text-red-500">
            Falha ao carregar registros. Tente novamente.
          </td>
        </tr>
      `;
    }
  }

  renderPagination(totalPages) {
    const paginContainer = this.container.querySelector('.table-pagination-buttons');
    paginContainer.innerHTML = '';

    if (totalPages <= 1) return;

    const prevBtn = document.createElement('button');
    prevBtn.className = `px-2.5 py-1 rounded border border-slate-200 dark:border-slate-700 ${this.page === 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-slate-100 dark:hover:bg-slate-700'}`;
    prevBtn.innerHTML = '<i class="fa-solid fa-chevron-left"></i>';
    prevBtn.disabled = this.page === 1;
    prevBtn.onclick = () => { if (this.page > 1) { this.page--; this.load(); } };
    paginContainer.appendChild(prevBtn);

    for (let p = 1; p <= totalPages; p++) {
      if (p === 1 || p === totalPages || (p >= this.page - 1 && p <= this.page + 1)) {
        const pBtn = document.createElement('button');
        pBtn.className = `px-3 py-1 rounded border ${p === this.page ? 'bg-navy-900 dark:bg-amber-500 text-white border-transparent font-bold' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700'}`;
        pBtn.textContent = p;
        pBtn.onclick = () => { this.page = p; this.load(); };
        paginContainer.appendChild(pBtn);
      } else if (p === this.page - 2 || p === this.page + 2) {
        const dots = document.createElement('span');
        dots.className = 'px-1 text-slate-400';
        dots.textContent = '...';
        paginContainer.appendChild(dots);
      }
    }

    const nextBtn = document.createElement('button');
    nextBtn.className = `px-2.5 py-1 rounded border border-slate-200 dark:border-slate-700 ${this.page === totalPages ? 'opacity-40 cursor-not-allowed' : 'hover:bg-slate-100 dark:hover:bg-slate-700'}`;
    nextBtn.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
    nextBtn.disabled = this.page === totalPages;
    nextBtn.onclick = () => { if (this.page < totalPages) { this.page++; this.load(); } };
    paginContainer.appendChild(nextBtn);
  }
}
