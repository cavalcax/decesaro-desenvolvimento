/**
 * PLATAFORMA DE DESENVOLVIMENTO DA FAMÍLIA DE CESARO
 * LEGADO E FUTURO — SCRIPT PRINCIPAL
 */

// Helper global para Toasts SweetAlert2
window.showToast = function (icon, title) {
  if (typeof Swal !== 'undefined') {
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: icon,
      title: title,
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    });
  }
};

// Gerenciamento de Tema (Claro / Escuro)
const ThemeManager = {
  _initialized: false,

  init() {
    if (this._initialized) return;
    this._initialized = true;

    const cookieMatch = document.cookie.match(/(?:^|; )decesaro_theme=([^;]*)/);
    const cookieTheme = cookieMatch ? decodeURIComponent(cookieMatch[1]) : null;
    const rawSaved = localStorage.getItem('decesaro_theme') || cookieTheme;
    let savedTheme = rawSaved;
    if (!savedTheme) {
      savedTheme = document.documentElement.classList.contains('dark') ? 'escuro' : 'claro';
    }
    this.apply(savedTheme, false);

    const toggleBtn = document.getElementById('theme-toggle-btn');
    if (toggleBtn) {
      toggleBtn.addEventListener('click', () => {
        const isDark = document.documentElement.classList.contains('dark');
        const nextTheme = isDark ? 'claro' : 'escuro';
        this.apply(nextTheme, true);
      });
    }
  },

  apply(theme, saveServer = false) {
    const isDark = (theme === 'escuro' || theme === 'dark');
    const root = document.documentElement;

    if (isDark) {
      root.classList.add('dark');
    } else {
      root.classList.remove('dark');
    }

    try {
      localStorage.setItem('decesaro_theme', isDark ? 'dark' : 'light');
      document.cookie = `decesaro_theme=${isDark ? 'dark' : 'light'}; path=/; max-age=31536000; SameSite=Lax`;
    } catch (e) { }

    // Atualizar logos dinâmicos se existirem
    const logoImg = document.getElementById('brand-logo-img');
    if (logoImg) {
      const basePath = logoImg.getAttribute('data-base-path') || '/desenvolvimento/assets/img';
      logoImg.src = `${basePath}/${isDark ? 'logo-dark.png' : 'logo-white.png'}`;
    }

    // Atualizar ícone do botão
    const toggleIcon = document.getElementById('theme-toggle-icon');
    if (toggleIcon) {
      toggleIcon.className = isDark ? 'fa-solid fa-sun text-amber-400' : 'fa-solid fa-moon text-slate-600';
    }

    if (saveServer && window.IS_AUTHENTICATED) {
      apiRequest('/desenvolvimento/api/preferencias', {
        method: 'POST',
        data: { tema: isDark ? 'escuro' : 'claro' },
        loading: false,
        silentError: true
      }).catch(() => { });
    }
  }
};

// Inicialização do Select2 com idioma pt-BR
function initSelect2() {
  if (typeof $ !== 'undefined' && $.fn.select2) {
    $('.select2').each(function () {
      const $this = $(this);
      const placeholder = $this.attr('placeholder') || 'Selecione...';
      const dropdownParent = $this.closest('.modal');

      $this.select2({
        language: 'pt-BR',
        width: '100%',
        placeholder: placeholder,
        allowClear: true,
        dropdownParent: dropdownParent.length ? dropdownParent : $(document.body)
      });
    });
  }
}

// Gerenciamento de Instalação do PWA e Modal iOS
const PwaManager = {
  deferredPrompt: null,
  btn: null,
  modal: null,

  isIOS() {
    return /iPad|iPhone|iPod/.test(navigator.userAgent) || 
           (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
  },

  isStandalone() {
    return window.matchMedia('(display-mode: standalone)').matches || 
           window.navigator.standalone === true;
  },

  init() {
    this.btn = document.getElementById('pwa-install-btn');
    this.modal = document.getElementById('pwa-ios-modal');
    if (!this.btn) return;

    // Se já estiver em modo standalone (app instalado aberto em tela cheia), oculta o botão
    if (this.isStandalone()) {
      this.btn.classList.add('hidden');
      return;
    }

    // Capturar o evento nativo de instalação do navegador (Chrome, Edge, Android)
    window.addEventListener('beforeinstallprompt', (e) => {
      e.preventDefault();
      this.deferredPrompt = e;
      this.btn.classList.remove('hidden');
    });

    // Evento disparado após a conclusão da instalação
    window.addEventListener('appinstalled', () => {
      this.deferredPrompt = null;
      if (this.btn) this.btn.classList.add('hidden');
      if (typeof showToast === 'function') {
        showToast('success', 'Aplicativo instalado com sucesso!');
      }
    });

    // Clique no botão de instalar no header
    this.btn.addEventListener('click', () => {
      this.handleInstallClick();
    });

    // Listeners para fechar o modal iOS
    const closeBtn = document.getElementById('pwa-ios-modal-close');
    const okBtn = document.getElementById('pwa-ios-modal-ok');
    if (closeBtn) closeBtn.addEventListener('click', () => this.closeModal());
    if (okBtn) okBtn.addEventListener('click', () => this.closeModal());
    if (this.modal) {
      this.modal.addEventListener('click', (e) => {
        if (e.target === this.modal) this.closeModal();
      });
    }

    // Tecla ESC para fechar o modal
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && this.modal && !this.modal.classList.contains('hidden')) {
        this.closeModal();
      }
    });
  },

  handleInstallClick() {
    // 1. No iOS (iPhone/iPad/Safari/Chrome no iOS), abre o modal de passo a passo
    if (this.isIOS()) {
      this.openModal();
      return;
    }

    // 2. Se houver prompt nativo capturado pelo navegador (Android, Chrome, Edge)
    if (this.deferredPrompt) {
      this.deferredPrompt.prompt();
      this.deferredPrompt.userChoice.then((choiceResult) => {
        if (choiceResult && choiceResult.outcome === 'accepted') {
          if (typeof showToast === 'function') {
            showToast('success', 'Instalando aplicativo...');
          }
        }
        this.deferredPrompt = null;
      });
      return;
    }

    // 3. Fallback informativo para desktop ou navegadores sem prompt automático
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        title: 'Instalar Aplicativo',
        html: `
          <div class="text-left text-xs text-slate-600 dark:text-slate-300 space-y-2.5 mt-2">
            <p>Para instalar a plataforma no seu dispositivo:</p>
            <ul class="list-disc pl-5 space-y-1.5">
              <li><strong>No Computador:</strong> Clique no ícone de instalação <i class="fa-solid fa-download text-amber-500"></i> na barra de endereços do navegador.</li>
              <li><strong>No Android:</strong> Toque no menu de opções (<strong>⋮</strong>) e selecione <strong>"Instalar aplicativo"</strong> ou <strong>"Adicionar à tela inicial"</strong>.</li>
            </ul>
            <div class="pt-3 border-t border-slate-200 dark:border-slate-700 text-center">
              <button type="button" id="swal-see-ios-guide" class="text-amber-600 dark:text-amber-400 font-semibold hover:underline text-xs inline-flex items-center gap-1.5">
                <i class="fa-brands fa-apple text-sm"></i>
                <span>Ver instruções para iPhone / iPad</span>
              </button>
            </div>
          </div>
        `,
        icon: 'info',
        confirmButtonColor: '#03254A',
        confirmButtonText: 'Entendi',
        didOpen: () => {
          const btnIos = document.getElementById('swal-see-ios-guide');
          if (btnIos) {
            btnIos.addEventListener('click', () => {
              Swal.close();
              this.openModal();
            });
          }
        }
      });
    } else {
      this.openModal();
    }
  },

  openModal() {
    if (this.modal) {
      this.modal.classList.remove('hidden');
      document.body.classList.add('overflow-hidden');
    }
  },

  closeModal() {
    if (this.modal) {
      this.modal.classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
    }
  }
};

// Conversão de VAPID Key Base64URL para Uint8Array para Web Push API
function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
  const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);
  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}

// Gerenciador do Modal e Sino de Notificações
const NotificationManager = {
  bellBtn: null,
  badge: null,
  modal: null,
  itemsContainer: null,
  unreadCountBadge: null,
  filterStats: null,
  markAllBtn: null,
  cachedItems: [],
  currentFilter: 'all',

  init() {
    this.bellBtn = document.getElementById('notif-bell-btn');
    this.badge = document.getElementById('notif-badge');
    this.modal = document.getElementById('notifications-modal');
    this.itemsContainer = document.getElementById('notif-items-container');
    this.unreadCountBadge = document.getElementById('notif-modal-unread-count');
    this.filterStats = document.getElementById('notif-filter-stats');
    this.markAllBtn = document.getElementById('notif-mark-all-btn');

    if (!this.bellBtn || !this.modal) return;

    // Buscar contagem inicial de notificações se autenticado
    if (window.IS_AUTHENTICATED) {
      this.refreshCount();
    }

    // Clique no sino: abre o modal e carrega os itens
    this.bellBtn.addEventListener('click', () => {
      this.openModal();
    });

    // Fechar modal
    const closeBtn = document.getElementById('notif-modal-close');
    const okBtn = document.getElementById('notif-modal-ok');
    if (closeBtn) closeBtn.addEventListener('click', () => this.closeModal());
    if (okBtn) okBtn.addEventListener('click', () => this.closeModal());
    this.modal.addEventListener('click', (e) => {
      if (e.target === this.modal) this.closeModal();
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && !this.modal.classList.contains('hidden')) {
        this.closeModal();
      }
    });

    // Marcar todas como lidas
    if (this.markAllBtn) {
      this.markAllBtn.addEventListener('click', async () => {
        await this.markAllAsRead();
      });
    }

    // Filtros de abas (Todas / Não Lidas)
    const filterAll = document.getElementById('notif-filter-all');
    const filterUnread = document.getElementById('notif-filter-unread');
    if (filterAll && filterUnread) {
      filterAll.addEventListener('click', () => {
        this.currentFilter = 'all';
        filterAll.className = 'notif-tab px-3 py-1 rounded-lg font-bold text-xs bg-amber-500 text-slate-950 transition-all';
        filterUnread.className = 'notif-tab px-3 py-1 rounded-lg font-medium text-xs text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition-all';
        this.renderItems();
      });

      filterUnread.addEventListener('click', () => {
        this.currentFilter = 'unread';
        filterUnread.className = 'notif-tab px-3 py-1 rounded-lg font-bold text-xs bg-amber-500 text-slate-950 transition-all';
        filterAll.className = 'notif-tab px-3 py-1 rounded-lg font-medium text-xs text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition-all';
        this.renderItems();
      });
    }
  },

  async refreshCount() {
    try {
      const res = await apiRequest('/desenvolvimento/api/notificacoes', { loading: false });
      if (res.success) {
        this.updateBadge(res.unread_count || 0);
        this.cachedItems = res.items || [];
      }
    } catch (e) {}
  },

  updateBadge(count) {
    if (!this.badge) return;
    if (count > 0) {
      this.badge.textContent = count > 99 ? '99+' : count;
      this.badge.classList.remove('hidden');
    } else {
      this.badge.classList.add('hidden');
    }
    if (this.unreadCountBadge) {
      this.unreadCountBadge.textContent = `${count} nova${count === 1 ? '' : 's'}`;
    }
  },

  async openModal() {
    this.modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    await this.loadNotifications();
  },

  closeModal() {
    this.modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
  },

  async loadNotifications() {
    if (!this.itemsContainer) return;
    this.itemsContainer.innerHTML = `
      <div class="py-12 text-center text-slate-400 text-xs">
        <div class="w-8 h-8 border-2 border-amber-500 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
        <p>Buscando notificações...</p>
      </div>
    `;

    try {
      const res = await apiRequest('/desenvolvimento/api/notificacoes', { loading: false });
      if (res.success) {
        this.cachedItems = res.items || [];
        this.updateBadge(res.unread_count || 0);
        this.renderItems();
      }
    } catch (err) {
      this.itemsContainer.innerHTML = `
        <div class="py-10 text-center text-slate-400 text-xs">
          <i class="fa-solid fa-triangle-exclamation text-amber-500 text-2xl mb-2"></i>
          <p>Erro ao carregar notificações. Tente novamente.</p>
        </div>
      `;
    }
  },

  renderItems() {
    if (!this.itemsContainer) return;
    const items = this.currentFilter === 'unread' 
      ? this.cachedItems.filter(i => i.lida === 'N') 
      : this.cachedItems;

    if (this.filterStats) {
      this.filterStats.textContent = `${items.length} registro${items.length === 1 ? '' : 's'}`;
    }

    if (items.length === 0) {
      this.itemsContainer.innerHTML = `
        <div class="py-12 text-center text-slate-400 text-xs">
          <i class="fa-regular fa-bell-slash text-slate-300 dark:text-slate-600 text-4xl mb-3"></i>
          <p class="font-bold text-slate-600 dark:text-slate-300">Nenhuma notificação encontrada</p>
          <p class="text-[11px] text-slate-400 mt-1">${this.currentFilter === 'unread' ? 'Você já visualizou todas as mensagens!' : 'Quando o comitê enviar avisos ou houver novos eventos, eles aparecerão aqui.'}</p>
        </div>
      `;
      return;
    }

    this.itemsContainer.innerHTML = items.map(item => {
      const isUnread = item.lida === 'N';
      let iconClass = 'fa-solid fa-bell text-amber-500';
      if (item.tipo === 'push') iconClass = 'fa-solid fa-tower-broadcast text-amber-500';
      else if (item.tipo === 'agenda') iconClass = 'fa-solid fa-calendar-check text-blue-500';
      else if (item.tipo === 'video' || item.tipo === 'conteudo') iconClass = 'fa-solid fa-circle-play text-emerald-500';

      return `
        <div class="notif-item p-3 rounded-2xl transition-all cursor-pointer border ${isUnread ? 'bg-amber-500/5 dark:bg-amber-500/10 border-amber-500/30 dark:border-amber-500/20 shadow-sm' : 'bg-slate-50/70 dark:bg-slate-900/30 border-transparent hover:border-slate-200 dark:hover:border-slate-700'}"
             data-id="${item.id}" data-url="${item.url || ''}">
          <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 flex items-center justify-center shrink-0 shadow-sm mt-0.5">
              <i class="${iconClass} text-xs"></i>
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-start justify-between gap-2">
                <h4 class="text-xs ${isUnread ? 'font-bold text-slate-900 dark:text-white' : 'font-semibold text-slate-700 dark:text-slate-300'} truncate">
                  ${item.titulo}
                </h4>
                <div class="flex items-center gap-1.5 shrink-0">
                  ${isUnread ? '<span class="w-2 h-2 rounded-full bg-amber-500" title="Não lida"></span>' : ''}
                  <span class="text-[10px] text-slate-400 font-mono">${item.criado_em.substring(0, 16)}</span>
                </div>
              </div>
              <p class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                ${item.mensagem}
              </p>
              ${item.url ? `
                <div class="mt-2 flex items-center gap-2">
                  <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 inline-flex items-center gap-1 hover:underline">
                    <span>Acessar conteúdo</span>
                    <i class="fa-solid fa-arrow-right text-[9px]"></i>
                  </span>
                </div>
              ` : ''}
            </div>
          </div>
        </div>
      `;
    }).join('');

    // Listener para clique nos itens
    this.itemsContainer.querySelectorAll('.notif-item').forEach(el => {
      el.addEventListener('click', async () => {
        const id = el.dataset.id;
        const url = el.dataset.url;
        await this.markAsRead(id);
        if (url) {
          window.location.href = url;
        }
      });
    });
  },

  async markAsRead(id) {
    try {
      await apiRequest('/desenvolvimento/api/notificacoes/ler', {
        method: 'POST',
        data: { id },
        loading: false
      });
      const item = this.cachedItems.find(i => String(i.id) === String(id));
      if (item && item.lida === 'N') {
        item.lida = 'S';
        const unreadCount = this.cachedItems.filter(i => i.lida === 'N').length;
        this.updateBadge(unreadCount);
        this.renderItems();
      }
    } catch (e) {}
  },

  async markAllAsRead() {
    try {
      const res = await apiRequest('/desenvolvimento/api/notificacoes/ler-todas', {
        method: 'POST',
        loadingText: 'Atualizando...'
      });
      if (res.success) {
        this.cachedItems.forEach(i => i.lida = 'S');
        this.updateBadge(0);
        this.renderItems();
        if (typeof showToast === 'function') {
          showToast('success', res.message);
        }
      }
    } catch (e) {}
  }
};

// Gerenciador de Inscrição Push do Usuário (Web Push API)
const PushSubscriptionManager = {
  toggleBtn: null,
  testBtn: null,
  statusBadge: null,
  warningEl: null,

  init() {
    this.toggleBtn = document.getElementById('push-toggle-btn');
    this.testBtn = document.getElementById('push-test-btn');
    this.statusBadge = document.getElementById('push-status-badge');
    this.warningEl = document.getElementById('push-permission-warning');

    if (!this.toggleBtn) return;

    this.checkPermissionStatus();

    this.toggleBtn.addEventListener('click', async () => {
      const currentStatus = this.toggleBtn.dataset.status;
      if (currentStatus === 'enabled') {
        await this.disablePush();
      } else {
        await this.enablePush();
      }
    });

    if (this.testBtn) {
      this.testBtn.addEventListener('click', async () => {
        await this.sendTestNotification();
      });
    }
  },

  checkPermissionStatus() {
    if (!('Notification' in window)) {
      if (this.warningEl) {
        this.warningEl.classList.remove('hidden');
        this.warningEl.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Este navegador não possui suporte a Notificações Push nativas.';
      }
      return;
    }

    if (Notification.permission === 'denied') {
      if (this.warningEl) this.warningEl.classList.remove('hidden');
      this.renderStatus('blocked');
    }
  },

  renderStatus(state) {
    if (!this.statusBadge || !this.toggleBtn) return;
    const btnText = document.getElementById('push-toggle-btn-text');
    const icon = this.toggleBtn.querySelector('i');

    if (state === 'enabled') {
      this.toggleBtn.dataset.status = 'enabled';
      this.toggleBtn.className = 'w-full px-3.5 py-2.5 rounded-xl font-bold text-xs transition-all shadow-sm flex items-center justify-center gap-2 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200';
      if (btnText) btnText.textContent = 'Desativar Notificações';
      if (icon) icon.className = 'fa-solid fa-bell-slash';
      this.statusBadge.innerHTML = `
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          <span>Notificações Ativas</span>
        </span>
      `;
      if (this.warningEl) this.warningEl.classList.add('hidden');
    } else if (state === 'blocked') {
      this.toggleBtn.dataset.status = 'disabled';
      this.toggleBtn.className = 'w-full px-3.5 py-2.5 rounded-xl font-bold text-xs transition-all shadow-sm flex items-center justify-center gap-2 bg-slate-200 dark:bg-slate-700 text-slate-500 opacity-70 cursor-not-allowed';
      if (btnText) btnText.textContent = 'Bloqueado no Navegador';
      if (icon) icon.className = 'fa-solid fa-ban';
      this.statusBadge.innerHTML = `
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-300">
          <span class="w-2 h-2 rounded-full bg-red-500"></span>
          <span>Permissão Bloqueada</span>
        </span>
      `;
      if (this.warningEl) this.warningEl.classList.remove('hidden');
    } else {
      this.toggleBtn.dataset.status = 'disabled';
      this.toggleBtn.className = 'w-full px-3.5 py-2.5 rounded-xl font-bold text-xs transition-all shadow-md flex items-center justify-center gap-2 bg-amber-500 hover:bg-amber-600 text-slate-950';
      if (btnText) btnText.textContent = 'Ativar Notificações';
      if (icon) icon.className = 'fa-solid fa-bell';
      this.statusBadge.innerHTML = `
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
          <span class="w-2 h-2 rounded-full bg-slate-400"></span>
          <span>Notificações Desativadas</span>
        </span>
      `;
      if (this.warningEl) this.warningEl.classList.add('hidden');
    }
  },

  async enablePush() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: 'info',
          title: 'Notificações no Dispositivo',
          text: 'Seu navegador não oferece suporte nativo à API Web Push. As notificações continuarão acessíveis pelo sino do cabeçalho.',
          confirmButtonColor: '#03254A'
        });
      }
      return;
    }

    try {
      const permission = await Notification.requestPermission();
      if (permission === 'denied') {
        this.renderStatus('blocked');
        if (typeof showToast === 'function') {
          showToast('error', 'Permissão de notificação negada no navegador.');
        }
        return;
      }
      if (permission !== 'granted') return;

      const reg = await navigator.serviceWorker.ready;
      const keyData = await apiRequest('/desenvolvimento/api/push/public-key', { loadingText: 'Configurando Push...' });
      if (!keyData.success || !keyData.publicKey) {
        throw new Error('Chave VAPID não encontrada.');
      }

      const applicationServerKey = urlBase64ToUint8Array(keyData.publicKey);
      const subscription = await reg.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey
      });

      const subJson = subscription.toJSON();
      const res = await apiRequest('/desenvolvimento/api/push/subscribe', {
        method: 'POST',
        data: subJson,
        loadingText: 'Ativando notificações...'
      });

      if (res.success) {
        this.renderStatus('enabled');
        if (typeof showToast === 'function') {
          showToast('success', 'Notificações ativadas com sucesso!');
        }
      }
    } catch (err) {
      console.error('[Push] Erro ao ativar push:', err);
      if (typeof showToast === 'function') {
        showToast('error', 'Falha ao ativar notificações push.');
      }
    }
  },

  async disablePush() {
    try {
      if ('serviceWorker' in navigator) {
        const reg = await navigator.serviceWorker.ready;
        const sub = await reg.pushManager.getSubscription();
        if (sub) {
          await sub.unsubscribe();
        }
      }

      await apiRequest('/desenvolvimento/api/push/unsubscribe', {
        method: 'POST',
        loadingText: 'Desativando...'
      });

      // Salvar preferência
      await apiRequest('/desenvolvimento/perfil/preferencias', {
        method: 'POST',
        data: { notificacoes_push: 'N' },
        loading: false
      });

      this.renderStatus('disabled');
      if (typeof showToast === 'function') {
        showToast('success', 'Notificações push desativadas.');
      }
    } catch (err) {
      console.error('[Push] Erro ao desativar push:', err);
    }
  },

  async sendTestNotification() {
    try {
      const res = await apiRequest('/desenvolvimento/api/push/test', {
        method: 'POST',
        loadingText: 'Disparando teste...'
      });
      if (res.success) {
        if (typeof showToast === 'function') {
          showToast('success', res.message);
        }
      }
    } catch (e) {}
  }
};

// Inicialização do Service Worker para PWA
function registerServiceWorker() {
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/desenvolvimento/sw.js', { scope: '/desenvolvimento/' })
        .then(reg => {
          console.log('[PWA] Service Worker registrado com escopo:', reg.scope);
        })
        .catch(err => {
          console.warn('[PWA] Falha no registro do Service Worker:', err);
        });
    });
  }
}

// Inicialização de componentes após carregamento do DOM
document.addEventListener('DOMContentLoaded', () => {
  ThemeManager.init();
  PwaManager.init();
  NotificationManager.init();
  PushSubscriptionManager.init();
  initSelect2();
  registerServiceWorker();

  // Menu mobile dropdown toggle
  const mobileToggle = document.getElementById('mobile-menu-toggle');
  const mobileMenu = document.getElementById('mobile-nav-menu');
  if (mobileToggle && mobileMenu) {
    mobileToggle.addEventListener('click', () => {
      mobileMenu.classList.toggle('hidden');
    });
  }
});
