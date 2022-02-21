$("#notif").hide();
// tombol tambah diklik
$("#index").on('click', '#bukaform', function(){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  $("#form").show().load(baseURL + '/master/dokterform?t=' + mlite.token);
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
  var kd_dokter = $('select[name=kd_dokter]').val();
  var nm_dokter = $('input:text[name=nm_dokter]').val();
  var jk = $('select[name=jk]').val();
  var tmp_lahir = $('input:text[name=tmp_lahir]').val();
  var tgl_lahir = $('input:text[name=tgl_lahir]').val();
  var gol_drh = $('select[name=gol_drh]').val();
  var agama = $('select[name=agama]').val();
  var almt_tgl = $('textarea[name=almt_tgl]').val();
  var no_telp = $('input:text[name=no_telp]').val();
  var stts_nikah = $('select[name=stts_nikah]').val();
  var kd_sps = $('select[name=kd_sps]').val();
  var alumni = $('input:text[name=alumni]').val();
  var no_ijn_praktek = $('input:text[name=no_ijn_praktek]').val();
  var status = $('select[name=status]').val();

  var url = baseURL + '/master/doktersave?t=' + mlite.token;

  $.post(url,{
    kd_dokter: kd_dokter,
    nm_dokter: nm_dokter,
    jk: jk,
    tmp_lahir: tmp_lahir,
    tgl_lahir: tgl_lahir,
    gol_drh: gol_drh,
    agama: agama,
    almt_tgl: almt_tgl,
    no_telp: no_telp,
    stts_nikah: stts_nikah,
    kd_sps: kd_sps,
    alumni: alumni,
    no_ijn_praktek: no_ijn_praktek,
    status: status
  } ,function(data) {
      $("#display").show().load(baseURL + '/master/dokterdisplay?t=' + mlite.token);
      $("#form").hide();
      $("#tutupform").val("Buka Form");
      $("#tutupform").attr("id", "bukaform");
      $('#notif').html("<div class=\"alert alert-success alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
      "Data dokter telah disimpan!"+
      "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
      "</div>").show();
  });
});

// ketika baris data diklik
$("#display").on("click", ".edit", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/master/dokterform?t=' + mlite.token;
  var kd_dokter  = $(this).attr("data-kd_dokter");

  $.post(url, {kd_dokter: kd_dokter} ,function(data) {
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
  var url = baseURL + '/master/dokterhapus?t=' + mlite.token;
  var kd_dokter = $('select[name=kd_dokter]').val();

  // tampilkan dialog konfirmasi
  bootbox.confirm("Apakah Anda yakin ingin menghapus data ini?", function(result){
    // ketika ditekan tombol ok
    if (result){
      // mengirimkan perintah penghapusan
      $.post(url, {
        kd_dokter: kd_dokter
      } ,function(data) {
        // sembunyikan form, tampilkan data yang sudah di perbaharui, tampilkan notif
        $("#form").hide();
        $("#tutupform").val("Buka Form");
        $("#tutupform").attr("id", "bukaform");
        $("#display").load(baseURL + '/master/dokterdisplay?t=' + mlite.token);
        $('#notif').html("<div class=\"alert alert-danger alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
        "Data dokter telah dihapus!"+
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
        "</div>").show();
      });
    }
  });
});

// ketika inputbox pencarian diisi
$('input:text[name=cari]').on('input',function(e){
  var baseURL = mlite.url + '/' + mlite.admin;
  var url    = baseURL + '/master/dokterdisplay?t=' + mlite.token;
  var cari = $('input:text[name=cari]').val();

  if(cari!="") {
      $.post(url, {cari: cari} ,function(data) {
      // tampilkan data yang sudah di perbaharui
        $("#display").html(data).show();
      });
  } else {
      $("#display").load(baseURL + '/master/dokterdisplay?t=' + mlite.token);
  }

});
// end pencarian

// ketika tombol halaman ditekan
$("#display").on("click", ".halaman",function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/master/dokterdisplay?t=' + mlite.token;
  kd_hal  = $(this).attr("data-hal");

  $.post(url, {halaman: kd_hal} ,function(data) {
  // tampilkan data
    $("#display").html(data).show();
  });

});
// end halaman

function bersih(){
  $('input:text[name=kd_dokter]').val("").removeAttr('disabled');
  $('input:text[name=nm_dokter]').val("");
  $('select[name=jk]').val("");
  $('input:text[name=tmp_lahir]').val("");
  $('input:text[name=tgl_lahir]').val("");
  $('select[name=gol_drh]').val("");
  $('select[name=agama]').val("");
  $('textarea[name=almt_tgl]').val("");
  $('input:text[name=no_telp]').val("");
  $('select[name=stts_nikah]').val("");
  $('select[name=kd_sps]').val("");
  $('input:text[name=alumni]').val("");
  $('input:text[name=no_ijn_praktek]').val("");
  $('select[name=status]').val("");
}
