// Service Worker pour Gplanning PWA
const CACHE_NAME = 'gplanning-v1.0.0';
const RUNTIME_CACHE = 'gplanning-runtime-v1.0.0';

// Fichiers statiques à mettre en cache lors de l'installation
const STATIC_CACHE_URLS = [
  '/',
  '/login',
  '/logo.png',
  '/icon-192x192.png',
  '/icon-512x512.png',
  '/manifest.json'
];

// Installer le service worker et mettre en cache les fichiers statiques
self.addEventListener('install', (event) => {
  console.log('[Service Worker] Installation...');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('[Service Worker] Mise en cache des fichiers statiques');
        return cache.addAll(STATIC_CACHE_URLS.map(url => new Request(url, { credentials: 'same-origin' })));
      })
      .then(() => self.skipWaiting())
      .catch((error) => {
        console.error('[Service Worker] Erreur lors de la mise en cache:', error);
      })
  );
});

// Activer le service worker et nettoyer les anciens caches
self.addEventListener('activate', (event) => {
  console.log('[Service Worker] Activation...');
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter((cacheName) => {
            return cacheName !== CACHE_NAME && cacheName !== RUNTIME_CACHE;
          })
          .map((cacheName) => {
            console.log('[Service Worker] Suppression de l\'ancien cache:', cacheName);
            return caches.delete(cacheName);
          })
      );
    })
    .then(() => self.clients.claim())
  );
});

// Stratégie de cache: Network First, puis Cache
self.addEventListener('fetch', (event) => {
  // Ignorer les requêtes non-GET
  if (event.request.method !== 'GET') {
    return;
  }

  // Ignorer les requêtes vers l'API (toujours en ligne)
  if (event.request.url.includes('/api/')) {
    return;
  }

  // Ignorer les requêtes de fichiers de cache (manifest, service worker)
  if (event.request.url.includes('/manifest.json') || 
      event.request.url.includes('/sw.js')) {
    return;
  }

  event.respondWith(
    fetch(event.request)
      .then((response) => {
        // Vérifier si la réponse est valide
        if (!response || response.status !== 200 || response.type !== 'basic') {
          return response;
        }

        // Cloner la réponse pour la mettre en cache
        const responseToCache = response.clone();

        caches.open(RUNTIME_CACHE).then((cache) => {
          cache.put(event.request, responseToCache);
        });

        return response;
      })
      .catch(() => {
        // Si le réseau échoue, essayer le cache
        return caches.match(event.request).then((cachedResponse) => {
          if (cachedResponse) {
            return cachedResponse;
          }

          // Si c'est une navigation (page HTML), retourner la page d'accueil en cache
          if (event.request.mode === 'navigate') {
            return caches.match('/');
          }

          // Sinon, retourner une réponse d'erreur
          return new Response('Hors ligne - Contenu non disponible', {
            status: 503,
            statusText: 'Service Unavailable',
            headers: new Headers({
              'Content-Type': 'text/plain'
            })
          });
        })
      )
  );
});

// Gérer les messages depuis l'application
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
  
  if (event.data && event.data.type === 'CACHE_URLS') {
    event.waitUntil(
      caches.open(RUNTIME_CACHE).then((cache) => {
        return cache.addAll(event.data.urls);
      })
    );
  }
});
