/**
 * Central de Requisições AJAX (Fetch API)
 * Plataforma de Desenvolvimento da Família De Cesaro
 */

function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  if (meta) {
    return meta.getAttribute('content');
  }
  const input = document.querySelector('input[name="csrf_token"]');
  return input ? input.value : '';
}

function showLoading(message = 'Carregando...') {
  const overlay = document.getElementById('global-spinner-overlay');
  if (overlay) {
    const text = overlay.querySelector('.spinner-text');
    if (text) text.textContent = message;
    overlay.classList.add('active');
  }
}

function hideLoading() {
  const overlay = document.getElementById('global-spinner-overlay');
  if (overlay) {
    overlay.classList.remove('active');
  }
}

/**
 * Função central para chamadas AJAX
 * @param {string} url - URL do endpoint
 * @param {object} options - Opções (method, data, loading, timeout, headers, signal)
 * @returns {Promise<any>}
 */
async function apiRequest(url, options = {}) {
  const method = (options.method || 'GET').toUpperCase();
  const loading = options.loading !== false;
  const loadingText = options.loadingText || (method === 'GET' ? 'Carregando...' : 'Processando...');
  const timeoutMs = options.timeout || 30000;

  if (loading) {
    showLoading(loadingText);
  }

  const controller = new AbortController();
  const signal = options.signal || controller.signal;
  const timeoutId = setTimeout(() => controller.abort(), timeoutMs);

  const headers = new Headers(options.headers || {});
  headers.set('X-Requested-With', 'XMLHttpRequest');
  headers.set('Accept', 'application/json');

  const csrf = getCsrfToken();
  if (csrf && !headers.has('X-CSRF-TOKEN') && ['POST', 'PUT', 'DELETE', 'PATCH'].includes(method)) {
    headers.set('X-CSRF-TOKEN', csrf);
  }

  let body = undefined;
  if (options.data !== undefined && options.data !== null) {
    if (options.data instanceof FormData) {
      body = options.data;
      if (csrf && !body.has('csrf_token')) {
        body.append('csrf_token', csrf);
      }
      // NOTA: Nunca definir Content-Type para FormData; o navegador inclui o boundary
    } else if (typeof options.data === 'object') {
      headers.set('Content-Type', 'application/json');
      const payload = { ...options.data };
      if (csrf && !payload.csrf_token && ['POST', 'PUT', 'DELETE', 'PATCH'].includes(method)) {
        payload.csrf_token = csrf;
      }
      body = JSON.stringify(payload);
    } else {
      body = options.data;
    }
  }

  try {
    const response = await fetch(url, {
      method,
      headers,
      body,
      signal,
      credentials: 'same-origin'
    });

    clearTimeout(timeoutId);

    // Tratar sessão expirada
    if (response.status === 401) {
      if (typeof Swal !== 'undefined') {
        await Swal.fire({
          icon: 'warning',
          title: 'Sessão Expirada',
          text: 'Sua sessão expirou. Redirecionando para a tela de login...',
          confirmButtonColor: '#1b3b5f',
          timer: 2500
        });
      }
      window.location.href = '/desenvolvimento/login';
      throw new Error('Sessão expirada.');
    }

    // Tentar ler resposta JSON
    const contentType = response.headers.get('content-type') || '';
    let jsonResult;
    if (contentType.includes('application/json')) {
      jsonResult = await response.json();
    } else {
      const text = await response.text();
      try {
        jsonResult = JSON.parse(text);
      } catch {
        jsonResult = { success: response.ok, message: text };
      }
    }

    if (!response.ok || (jsonResult && jsonResult.success === false)) {
      const errorMsg = jsonResult?.message || `Erro ${response.status}: ${response.statusText}`;
      const err = new Error(errorMsg);
      err.response = response;
      err.data = jsonResult;
      throw err;
    }

    return jsonResult;

  } catch (error) {
    if (error.name === 'AbortError') {
      const timeoutError = new Error('A requisição excedeu o tempo limite. Verifique sua conexão.');
      if (typeof Swal !== 'undefined' && options.silentError !== true) {
        Swal.fire({
          icon: 'error',
          title: 'Tempo Excedido',
          text: timeoutError.message,
          confirmButtonColor: '#1b3b5f'
        });
      }
      throw timeoutError;
    }

    if (typeof Swal !== 'undefined' && options.silentError !== true) {
      Swal.fire({
        icon: 'error',
        title: 'Atenção',
        text: error.message || 'Falha ao processar a operação.',
        confirmButtonColor: '#1b3b5f'
      });
    }

    throw error;
  } finally {
    if (loading) {
      hideLoading();
    }
  }
}
