// REGISTER ON LOAD
window.addEventListener('load', e => {
  if (!('serviceWorker' in navigator)) {
      console.log('Service worker not supported');
      return;
  }
  navigator.serviceWorker.register('service-worker.js')
      .then(function () {
          console.log('Service worker registered');
      })
      .catch(function (error) {
          console.log('Service worker registration failed:',
              error);
      });
});
