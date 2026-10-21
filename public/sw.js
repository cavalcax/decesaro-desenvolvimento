/**
 * Service Worker para PWA
 * Plataforma de Desenvolvimento da Família De Cesaro
 */

const CACHE_NAME = 'decesaro-desenvolvimento-v1';
const STATIC_ASSETS = [
  '/desenvolvimento/offline.html',
  '/desenvolvimento/assets/css/app.css',
  '/desenvolvimento/assets/js/api.js',
  '/desenvolvimento/assets/js/app.js',
  '/desenvolvimento/assets/js/tables.js',
  '/desenvolvimento/assets/js/video-player.js',
  '/desenvolvimento/assets/img/logo-dark.png',
  '/desenvolvimento/assets/img/logo-white.png',
  '/desenvolvimento/assets/img/favicon.svg',
  '/desenvolvimento/assets/img/web-app-manifest-192x192.png'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(STATIC_ASSETS);
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.map((key) => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      );
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  // Ignorar requisições não GET ou requisições de API / autenticadas
  if (event.request.method !== 'GET') return;

  const url = new URL(event.request.url);

  // Não cachear chamadas de API ou arquivos privados
  if (url.pathname.includes('/api/') || url.pathname.includes('/admin/')) {
    return;
  }

  // Para requisições de navegação de páginas (HTML dinâmico), priorizar rede para garantir integridade da sessão
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request).catch(() => caches.match('/desenvolvimento/offline.html'))
    );
    return;
  }

  event.respondWith(
    fetch(event.request)
      .then((response) => {
        // Se a resposta for válida para assets estáticos, podemos cachear
        if (response && response.status === 200 && response.type === 'basic') {
          if (url.pathname.match(/\.(css|js|png|jpg|svg|woff2)$/)) {
            const responseToCache = response.clone();
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(event.request, responseToCache);
            });
          }
        }
        return response;
      })
      .catch(() => {
        return caches.match(event.request);
      })
  );
});

// ==========================================
// Evento: Recebimento de Push Notification
// ==========================================
self.addEventListener('push', (event) => {
  let data = {
    title: 'Legado e Futuro — De Cesaro',
    body: 'Você recebeu uma nova notificação.',
    icon: '/desenvolvimento/assets/img/apple-touch-icon.png',
    badge: '/desenvolvimento/assets/img/favicon-96x96.png',
    url: '/desenvolvimento/inicio',
    data: {
      url: '/desenvolvimento/inicio'
    }
  };

  if (event.data) {
    try {
      const json = event.data.json();
      data = Object.assign(data, json);
      if (json.url && (!data.data || !data.data.url)) {
        data.data = Object.assign({}, data.data, { url: json.url });
      }
    } catch (e) {
      data.body = event.data.text();
    }
  }

  const options = {
    body: data.body,
    icon: data.icon || '/desenvolvimento/assets/img/apple-touch-icon.png',
    badge: data.badge || '/desenvolvimento/assets/img/favicon-96x96.png',
    vibrate: [100, 50, 100],
    data: data.data || { url: data.url || '/desenvolvimento/inicio' },
    tag: 'decesaro-push-' + (data.id || Date.now()),
    renotify: true
  };

  event.waitUntil(
    self.registration.showNotification(data.title, options)
  );
});

// ==========================================
// Evento: Clique na Notificação Push
// ==========================================
self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  const targetUrl = (event.notification.data && event.notification.data.url)
    ? event.notification.data.url
    : '/desenvolvimento/inicio';

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
      // Se houver uma janela aberta do mesmo domínio, foca nela e navega para o link
      for (const client of windowClients) {
        if (client.url && 'focus' in client) {
          client.focus();
          if ('navigate' in client) {
            return client.navigate(targetUrl);
          }
          return client;
        }
      }
      // Se nenhuma janela estiver aberta, abre uma nova
      if (clients.openWindow) {
        return clients.openWindow(targetUrl);
      }
    })
  );
});
