function cekNokaBPJS(){
    var no_peserta = $("#no_peserta").val();
    $.ajax({
        url: '{?=url()?}/admin/pasien/noka_bpjs?noka='+no_peserta+'&t={?=$_SESSION['token']?}',
    }).success(function (data) {
        var json = data,
        obj = JSON.parse(json);
        console.log(obj);
        $('#nm_pasien').val(obj.response.peserta.nama);
        $('#no_ktp').val(obj.response.peserta.nik);
        $('#tgl_lahir').val(obj.response.peserta.tglLahir);
        $('#no_tlp').val(obj.response.peserta.mr.noTelepon);
    });
}

function cekNikBPJS(){
    var no_ktp = $("#no_ktp").val();
    $.ajax({
        url: '{?=url()?}/admin/pasien/nik_bpjs?nik='+no_ktp+'&t={?=$_SESSION['token']?}',
    }).success(function (data) {
        var json = data,
        obj = JSON.parse(json);
        console.log(obj);
        $('#nm_pasien').val(obj.response.peserta.nama);
        $('#no_peserta').val(obj.response.peserta.noKartu);
        $('#tgl_lahir').val(obj.response.peserta.tglLahir);
        $('#no_tlp').val(obj.response.peserta.mr.noTelepon);
    });
}

// Avatar
var reader  = new FileReader();
reader.addEventListener("load", function() {
  $("#photoPreview").attr('src', reader.result);
}, false);
$("input[name=photo]").change(function() {
  reader.readAsDataURL(this.files[0]);
});

// Datepicker
$( function() {
  $( ".datepicker" ).datepicker({
    dateFormat: "yy-mm-dd",
    changeMonth: true,
    changeYear: true,
    yearRange: "-100:+0",
  });
} );

$(document).ready(function(){
    $('.display').DataTable({
      "lengthChange": false,
      "scrollX": true
    });
});

$(document).ready(function(){
    $.ajax({
      type: 'GET',
      url: '{?=url()?}/admin/pasien/ajax?show=propinsi&t={?=$_SESSION['token']?}',
      success: function(response) {
        $('#propinsi').html(response);
        $('.propinsi').DataTable({
          "lengthChange": false,
          "scrollX": true
        });
        console.log(response);
      }
    })
});

$(document).on('click', '.pilihpropinsi', function (e) {
  $("#kd_prop")[0].value = $(this).attr('data-kdprop');
  $("#namaprop")[0].value = $(this).attr('data-namaprop');
  $('#propinsiModal').modal('hide');
  var kd_prop = $(this).attr('data-kdprop');
  $.ajax({
    type: 'GET',
    url: '{?=url()?}/admin/pasien/ajax?show=kabupaten&kd_prop='+kd_prop+'&t={?=$_SESSION['token']?}',
    success: function(response) {
      $('#kabupaten').html(response);
      $('.kabupaten').DataTable({
        "lengthChange": false,
        "scrollX": true
      });
      console.log(kd_prop);
    }
  })
});

$(document).on('click', '.pilihkabupaten', function (e) {
  $("#kd_kab")[0].value = $(this).attr('data-kdkab');
  $("#namakab")[0].value = $(this).attr('data-namakab');
  $('#kabupatenModal').modal('hide');
  var kd_kab = $(this).attr('data-kdkab');
  $.ajax({
    type: 'GET',
    url: '{?=url()?}/admin/pasien/ajax?show=kecamatan&kd_kab='+kd_kab+'&t={?=$_SESSION['token']?}',
    success: function(response) {
      $('#kecamatan').html(response);
      $('.kecamatan').DataTable({
        "lengthChange": false,
        "scrollX": true
      });
      console.log(response);
    }
  })
});

$(document).on('click', '.pilihkecamatan', function (e) {
  $("#kd_kec")[0].value = $(this).attr('data-kdkec');
  $("#namakec")[0].value = $(this).attr('data-namakec');
  $('#kecamatanModal').modal('hide');
  var kd_kec = $(this).attr('data-kdkec');
  $.ajax({
    type: 'GET',
    url: '{?=url()?}/admin/pasien/ajax?show=kelurahan&kd_kec='+kd_kec+'&t={?=$_SESSION['token']?}',
    success: function(response) {
      $('#kelurahan').html(response);
      $('.kelurahan').DataTable({
        "lengthChange": false,
        "scrollX": true
      });
      console.log(response);
    }
  })
});

$(document).on('click', '.pilihkelurahan', function (e) {
    $("#kd_kel")[0].value = $(this).attr('data-kdkel');
    $("#namakel")[0].value = $(this).attr('data-namakel');
    $('#kelurahanModal').modal('hide');
});

$("#copy_alamat").click(function(){
    $("#alamatpj")[0].value = $("#alamat").val();
    $("#propinsipj")[0].value = $("#namaprop").val();
    $("#kabupatenpj")[0].value = $("#namakab").val();
    $("#kecamatanpj")[0].value = $("#namakec").val();
    $("#kelurahanpj")[0].value = $("#namakel").val();
});
