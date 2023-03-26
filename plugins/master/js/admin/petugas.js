$("#notif").hide();
// tombol tambah diklik
$("#index").on('click', '#bukaform', function(){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  $("#form").show().load(baseURL + '/master/petugasform?t=' + mlite.token);
  $("#bukaform").val("Tutup Form");
  $("#bukaform").attr("id", "tutupform");
});

$("#index").on('click', '#tutupform', function(){
  event.preventDefault();
  $("#form").hide();
  $("#tutupform").val("Buka Form");
  $("#tutupform").attr("id", "bukaform");
});

// tombol batal diklik
$("#form").on("click", "#batal", function(event){
  bersih();
});

// tombol simpan diklik
$("#form").on("click", "#simpan", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var nip = $('select[name=nip]').val();
  var nama = $('input:text[name=nama]').val();
  var jk = $('select[name=jk]').val();
  var tmp_lahir = $('input:text[name=tmp_lahir]').val();
  var tgl_lahir = $('input:text[name=tgl_lahir]').val();
  var gol_darah = $('select[name=gol_darah]').val();
  var agama = $('select[name=agama]').val();
  var stts_nikah = $('select[name=stts_nikah]').val();
  var alamat = $('textarea[name=alamat]').val();
  var kd_jbtn = $('select[name=kd_jbtn]').val();
  var no_telp = $('input:text[name=no_telp]').val();
  var status = $('select[name=status]').val();

  var url = baseURL + '/master/petugassave?t=' + mlite.token;

  $.post(url,{
    nip: nip,
    nama: nama,
    jk: jk,
    tmp_lahir: tmp_lahir,
    tgl_lahir: tgl_lahir,
    gol_darah: gol_darah,
    agama: agama,
    stts_nikah: stts_nikah,
    alamat: alamat,
    kd_jbtn: kd_jbtn,
    no_telp: no_telp,
    status: status
  } ,function(data) {
      $("#display").show().load(baseURL + '/master/petugasdisplay?t=' + mlite.token);
      $("#form").hide();
      $("#tutupform").val("Buka Form");
      $("#tutupform").attr("id", "bukaform");
      $('#notif').html("<div class=\"alert alert-success alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
      "Data petugas telah disimpan!"+
      "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
      "</div>").show();
  });
});

// ketika baris data diklik
$("#display").on("click", ".edit", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/master/petugasform?t=' + mlite.token;
  var nip  = $(this).attr("data-nip");

  $.post(url, {nip: nip} ,function(data) {
    // tampilkan data
    $("#form").html(data).show();
    $("#bukaform").val("Tutup Form");
    $("#bukaform").attr("id", "tutupform");
  });
});

// ketika tombol hapus ditekan
$("#form").on("click","#hapus", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/master/petugashapus?t=' + mlite.token;
  var nip = $('select[name=nip]').val();

  // tampilkan dialog konfirmasi
  bootbox.confirm("Apakah Anda yakin ingin menghapus data ini?", function(result){
    // ketika ditekan tombol ok
    if (result){
      // mengirimkan perintah penghapusan
      $.post(url, {
        nip: nip
      } ,function(data) {
        // sembunyikan form, tampilkan data yang sudah di perbaharui, tampilkan notif
        $("#form").hide();
        $("#tutupform").val("Buka Form");
        $("#tutupform").attr("id", "bukaform");
        $("#display").load(baseURL + '/master/petugasdisplay?t=' + mlite.token);
        $('#notif').html("<div class=\"alert alert-danger alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
        "Data petugas telah dihapus!"+
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
        "</div>").show();
      });
    }
  });
});

// ketika inputbox pencarian diisi
$('input:text[name=cari]').on('input',function(e){
  var baseURL = mlite.url + '/' + mlite.admin;
  var url    = baseURL + '/master/petugasdisplay?t=' + mlite.token;
  var cari = $('input:text[name=cari]').val();

  if(cari!="") {
      $.post(url, {cari: cari} ,function(data) {
      // tampilkan data yang sudah di perbaharui
        $("#display").html(data).show();
      });
  } else {
      $("#display").load(baseURL + '/master/petugasdisplay?t=' + mlite.token);
  }

});
// end pencarian

// ketika tombol halaman ditekan
$("#display").on("click", ".halaman",function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/master/petugasdisplay?t=' + mlite.token;
  kd_hal  = $(this).attr("data-hal");

  $.post(url, {halaman: kd_hal} ,function(data) {
  // tampilkan data
    $("#display").html(data).show();
  });

});
// end halaman

function bersih(){
  $('input:text[name=nip]').val("").removeAttr('disabled');
  $('input:text[name=nama]').val("");
  $('select[name=jk]').val("");
  $('input:text[name=tmp_lahir]').val("");
  $('input:text[name=tgl_lahir]').val("");
  $('select[name=gol_darah]').val("");
  $('select[name=agama]').val("");
  $('select[name=stts_nikah]').val("");
  $('textarea[name=alamat]').val("");
  $('select[name=kd_jbtn]').val("");
  $('input:text[name=no_telp]').val("");
  $('select[name=status]').val("");
}
