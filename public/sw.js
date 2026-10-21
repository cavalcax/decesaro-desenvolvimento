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
        // Fallback: se offline e for navegação de página, retornar offline.html
        if (event.request.mode === 'navigate') {
          return caches.match('/desenvolvimento/offline.html');
        }
        return caches.match(event.request);
      })
  );
});
