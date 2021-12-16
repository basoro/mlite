
// ketika inputbox pencarian diisi
$('input:text[name=cari]').on('input',function(e){
  var baseURL = mlite.url + '/' + mlite.admin;
  var url    = baseURL + '/pasien_galleries/display?t=' + mlite.token;
  var cari = $('input:text[name=cari]').val();

  if(cari!="") {
      $.post(url, {cari: cari} ,function(data) {
        // tampilkan data yang sudah di perbaharui dan sembunyikan notif
        $("#notif").hide();
        $("#display").html(data).show();
      });
  } else {
      $("#notif").hide();
      $("#display").load(baseURL + '/pasien_galleries/display?t=' + mlite.token);
  }

});

// ketika tombol halaman ditekan
$("#display").on("click", ".halaman",function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/pasien_galleries/display?t=' + mlite.token;
  kd_hal  = $(this).attr("data-hal");

  $.post(url, {halaman: kd_hal} ,function(data) {
    // tampilkan data
    $("#notif").hide();
    $("#display").html(data).show();
  });

});

$(document).ready(function () {
  var strip_tags = function(str) {
    return (str + '').replace(/<\/?[^>]+(>|$)/g, '')
  };
  var truncate_string = function(str, chars) {
    if ($.trim(str).length <= chars) {
      return str;
    } else {
      return $.trim(str.substr(0, chars)) + '...';
    }
  };
  $('select').selectator('destroy');
  $('.pasien_ajax').selectator({
    labels: {
      search: 'Cari pasien...'
    },
    load: function (search, callback) {
      if (search.length < this.minSearchLength) return callback();
      $.ajax({
        url: '{?=url()?}/admin/pasien_galleries/ajax?show=pasien&s=' + encodeURIComponent(search) + '&t={?=$_SESSION['token']?}',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
          callback(data.slice(0, 100));
          console.log(data);
        },
        error: function() {
          callback();
        }
      });
    },
    delay: 500,
    minSearchLength: 4,
    valueField: 'no_rkm_medis',
    textField: 'nm_pasien'
  });
  $('select').selectator();
});
