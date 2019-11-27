var staticCacheName = 'khanza-lite';
var filesToCache = [
  '/',
  'index.html',
  'assets/css/roboto.css',
  'assets/css/material-icon.css',
  'assets/plugins/bootstrap/css/bootstrap.css',
  'assets/plugins/node-waves/waves.css',
  'assets/plugins/animate-css/animate.css',
  'assets/css/style.css',
  'assets/css/themes/all-themes.min.css',
  'assets/plugins/jquery/jquery.min.js',
  'assets/plugins/bootstrap/js/bootstrap.js',
  'assets/plugins/node-waves/waves.js',
  'assets/plugins/jquery-validation/jquery.validate.js',
  'assets/js/admin.js',
  'main.js'
];

// Start the service worker and cache all of the app's shell content
self.addEventListener('install', function (e) {
  e.waitUntil(
    caches.open(staticCacheName).then(function (cache) {
      return cache.addAll(filesToCache);
    })
  );
});

// Check if server worker is activated
self.addEventListener('activate', function (e) {
  console.log('Service worker has been activate.');
  // Delete old static cache
  e.waitUntil(
    caches.keys().then(cacheNames => {
      console.log(cacheNames);
      return Promise.all(cacheNames
        .filter(cacheName => cacheName !== staticCacheName)
        .map(cacheName => caches.delete(cacheName))
      );
    })
  );
});

// Serve cached content when offline
self.addEventListener('fetch', function (e) {
  e.respondWith(
    caches.match(e.request).then(function (response) {
      return response || fetch(e.request);
    })
  );
});
