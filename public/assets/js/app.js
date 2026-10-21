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
