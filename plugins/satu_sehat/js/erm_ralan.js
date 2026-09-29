/**
 * ERM Rawat Jalan - SATUSEHAT
 * Perilaku halaman: sinkronisasi ajax, accordion BAB, salin JSON, toggle log.
 */
(function ($) {
  'use strict';

  function esc(text) {
    return $('<div></div>').text(text === null || text === undefined ? '' : String(text)).html();
  }

  function renderResult($target, result) {
    if (!$target || !$target.length) {
      return;
    }
    var type = result.status === 'success' ? 'success' : (result.status === 'ready' ? 'info' : 'danger');
    var html = '<div class="alert alert-' + type + ' erm-sync-message">';
    html += '<strong>' + esc(result.message || '') + '</strong>';
    if (result.http_code) {
      html += '<br><small>HTTP ' + esc(result.http_code) + '</small>';
    }
    if (result.errors && result.errors.length) {
      html += '<ul>';
      $.each(result.errors, function (i, error) {
        html += '<li>' + esc(error) + '</li>';
      });
      html += '</ul>';
    }
    if (result.warnings && result.warnings.length) {
      html += '<p class="erm-sync-warnings"><small><i class="fa fa-exclamation-triangle"></i> ';
      html += esc(result.warnings.join(' | '));
      html += '</small></p>';
    }
    html += '</div>';
    $target.html(html);
  }

  $(function () {
    // Sinkronisasi ERM (kirim Bundle transaction ke SATUSEHAT).
    $(document).on('click', '.erm-sync-btn', function () {
      var $btn = $(this);
      var url = $btn.data('url');
      var noRawat = $btn.data('no-rawat');
      var $result = $('#erm-sync-result');

      var ermLabel = $btn.data('erm-label') || 'ERM';
      if (!window.confirm('Kirim Bundle ' + ermLabel + ' untuk ' + noRawat + ' ke SATUSEHAT?')) {
        return;
      }

      $btn.prop('disabled', true).addClass('disabled');
      $result.html('<div class="alert alert-info">Mengirim Bundle ERM ke SATUSEHAT&hellip;</div>');

      $.ajax({
        url: url,
        method: 'POST',
        dataType: 'json',
        data: { no_rawat: noRawat }
      })
        .done(function (response) {
          renderResult($result, response || { status: 'error', message: 'Response kosong.' });
        })
        .fail(function (xhr) {
          var response = xhr && xhr.responseJSON ? xhr.responseJSON : null;
          renderResult($result, response || {
            status: 'error',
            message: 'Gagal menghubungi server (HTTP ' + xhr.status + ').'
          });
        })
        .always(function () {
          $btn.prop('disabled', false).removeClass('disabled');
        });
    });

    // Salin JSON bundle ke clipboard.
    $(document).on('click', '.erm-copy-btn', function () {
      var target = $(this).data('target');
      var text = $(target).text();
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text);
      } else {
        var $tmp = $('<textarea></textarea>').css({ position: 'fixed', left: '-1000px' }).text(text);
        $('body').append($tmp);
        $tmp[0].select();
        document.execCommand('copy');
        $tmp.remove();
      }
      $(this).tooltip({ title: 'Tersalin!', trigger: 'manual' }).tooltip('show');
    });

    // Toggle detail log request/response.
    $(document).on('click', '.erm-log-toggle', function () {
      $($(this).data('target')).collapse('toggle');
    });

    // Chevron indikator accordion BAB.
    $('.erm-section-head').on('click', function () {
      $(this).find('.erm-chevron').toggleClass('fa-chevron-down fa-chevron-up');
    });
  });
})(jQuery);
