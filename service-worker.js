const cacheName = 'cache-v2';
const precacheResources = [
  '/',
  'assets/jscripts/bootstrap.min.js',
  'assets/css/flatly.min.css',
];

self.addEventListener('install', event => {
  //console.log('Service worker install event!');
  event.waitUntil(
    caches.open(cacheName)
      .then(cache => {
        return cache.addAll(precacheResources);
      })
  );
});

self.addEventListener('activate', event => {
  //console.log('Service worker activate event!');
});

self.addEventListener('fetch', event => {
  //console.log('Fetch intercepted for:', event.request.url);
  event.respondWith(caches.match(event.request)
    .then(cachedResponse => {
        if (cachedResponse) {
          return cachedResponse;
        }
        return fetch(event.request);
      })
    );
});
