const CACHE_NAME = 'hec-cache-v1';
const urlsToCache = [
  '/superadmin/login', // halaman utama
  '/css/app.css',      // CSS
  '/js/app.js',        // JS
  '/icons/hec.png'     // icon
];

// Install: cache semua file
self.addEventListener('install', event => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(urlsToCache))
  );
});

// Activate: take control immediately
self.addEventListener('activate', event => {
  self.clients.claim();
});

// Fetch: ambil dari cache dulu, fallback ke network
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request).then(cachedResponse => {
      return cachedResponse || fetch(event.request);
    })
  );
});
