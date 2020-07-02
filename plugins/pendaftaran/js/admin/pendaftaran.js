// Datepicker
$( function() {
  $( ".datepicker" ).datepicker({
    dateFormat: "yy-mm-dd",
    changeMonth: true,
    changeYear: true,
    yearRange: "-100:+0",
  });
} );

$( function() {
  $('.timepicker').timepicker();
} );
$(document).ready(function(){
    var keyword = '';
    load_data(keyword);
    function load_data(keyword) {
      $.ajax({
        type: 'GET',
        url: '{?=url(ADMIN)?}/pendaftaran/ajax?keyword='+keyword+'&t={?=$_SESSION['token']?}',
        success: function(response) {
          $('#pasien').html(response);
        }
      })
    }
    $('#s_keyword').keyup(function(){
  		var keyword = $("#s_keyword").val();
			load_data(keyword);
		});
});

$(document).on('click', '.pilihpasien', function (e) {
    $("#no_rkm_medis")[0].value = $(this).attr('data-norkmmedis');
    $("#nm_pasien")[0].value = $(this).attr('data-nmpasien');
    $("#namakeluarga")[0].value = $(this).attr('data-namakeluarga');
    $("#alamatkeluarga")[0].value = $(this).attr('data-alamatkeluarga');
    $('#pasienModal').modal('hide');
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
  $('.poliklinik').selectator({
    labels: {
      search: 'Cari Poliklinik...'
    },
    load: function (search, callback) {
      if (search.length < this.minSearchLength) return callback();
      $.ajax({
        url: '{?=url()?}/admin/pendaftaran/ajax?show=poliklinik&s=' + encodeURIComponent(search) + '&t={?=$_SESSION['token']?}',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
          callback(data['response']['poli'].slice(0, 100));
          console.log(data['response']['poli']);
        },
        error: function() {
          callback();
        }
      });
    },
    delay: 300,
    minSearchLength: 3,
    valueField: 'kode',
    textField: 'nama'
  });
  $('.dokter').selectator({
    labels: {
      search: 'Cari DPJP...'
    },
    load: function (search, callback) {
      if (search.length < this.minSearchLength) return callback();
      $.ajax({
        url: '{?=url()?}/admin/pendaftaran/ajax?show=dokter&s=' + encodeURIComponent(search) + '&t={?=$_SESSION['token']?}',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
          callback(data['response']['list'].slice(0, 100));
          console.log(data['response']['list']);
        },
        error: function() {
          callback();
        }
      });
    },
    delay: 300,
    minSearchLength: 3,
    valueField: 'kode',
    textField: 'nama'
  });
  $('.icd10').selectator({
    labels: {
      search: 'Cari ICD-10...'
    },
    load: function (search, callback) {
      if (search.length < this.minSearchLength) return callback();
      $.ajax({
        url: '{?=url()?}/admin/pendaftaran/ajax?show=diagnosa&s=' + encodeURIComponent(search) + '&t={?=$_SESSION['token']?}',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
          callback(data['response']['diagnosa'].slice(0, 100));
          console.log(data['response']['diagnosa']);
        },
        error: function() {
          callback();
        }
      });
    },
    delay: 300,
    minSearchLength: 3,
    valueField: 'kode',
    textField: 'nama'
  });
  $('.propinsi').selectator({
    labels: {
      search: 'Cari Propinsi...'
    },
    load: function (search, callback) {
      if (search.length < this.minSearchLength) return callback();
      $.ajax({
        url: '{?=url()?}/admin/pendaftaran/ajax?show=propinsi&t={?=$_SESSION['token']?}',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
          callback(data['response']['list'].slice(0, 100));
          console.log(data['response']['list']);
        },
        error: function() {
          callback();
        }
      });
    },
    delay: 300,
    minSearchLength: 0,
    valueField: 'kode',
    textField: 'nama'
  });
  $('.kabupaten').selectator({
    labels: {
      search: 'Cari Kabupaten...'
    },
    load: function (search, callback) {
      if (search.length < this.minSearchLength) return callback();
      $.ajax({
        url: '{?=url()?}/admin/pendaftaran/ajax?show=kabupaten&t={?=$_SESSION['token']?}',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
          callback(data['response']['list'].slice(0, 100));
          console.log(data['response']['list']);
        },
        error: function() {
          callback();
        }
      });
    },
    delay: 300,
    minSearchLength: 0,
    valueField: 'kode',
    textField: 'nama'
  });

  $('select').selectator();
});
