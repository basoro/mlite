var staticCacheName = 'khanza-lite';
var filesToCache = [
  '/',
  'assets/css/roboto.css',
  'assets/css/material-icon.css',
  'assets/plugins/bootstrap/css/bootstrap.css',
  'assets/plugins/node-waves/waves.css',
  'assets/plugins/animate-css/animate.css',
  'assets/plugins/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css',
  'assets/plugins/jquery-datatable/extensions/responsive/css/responsive.dataTables.min.css',
  'assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css',
  'assets/plugins/bootstrap-select/css/bootstrap-select.css',
  'assets/css/select2.min.css',
  'assets/plugins/light-gallery/css/lightgallery.css',
  'assets/css/style.css',
  'assets/css/themes/all-themes.min.css',
  'assets/plugins/jquery-validation/jquery.validate.js',
  'assets/plugins/jquery/jquery.min.js',
  'assets/plugins/bootstrap/js/bootstrap.js',
  'assets/plugins/bootstrap-select/js/bootstrap-select.js',
  'assets/plugins/jquery-slimscroll/jquery.slimscroll.js',
  'assets/plugins/node-waves/waves.js',
  'assets/plugins/jquery-countto/jquery.countTo.js',
  'assets/plugins/jquery-datatable/jquery.dataTables.js',
  'assets/plugins/jquery-datatable/skin/bootstrap/js/dataTables.bootstrap.js',
  'assets/plugins/jquery-datatable/extensions/responsive/js/dataTables.responsive.min.js',
  'assets/plugins/jquery-datatable/extensions/export/dataTables.buttons.min.js',
  'assets/plugins/jquery-datatable/extensions/export/buttons.flash.min.js',
  'assets/plugins/jquery-datatable/extensions/export/jszip.min.js',
  'assets/plugins/jquery-datatable/extensions/export/pdfmake.min.js',
  'assets/plugins/jquery-datatable/extensions/export/vfs_fonts.js',
  'assets/plugins/jquery-datatable/extensions/export/buttons.html5.min.js',
  'assets/plugins/jquery-datatable/extensions/export/buttons.print.min.js',
  'assets/plugins/chartjs/Chart.bundle.js',
  'assets/plugins/jquery-sparkline/jquery.sparkline.js',
  'assets/plugins/autosize/autosize.js',
  'assets/plugins/momentjs/moment.js',
  'assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js',
  'assets/js/jquery-ui.min.js',
  'assets/plugins/sweetalert/sweetalert.min.js',
  'assets/js/select2.min.js',
  'assets/plugins/light-gallery/js/lightgallery-all.js',
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
