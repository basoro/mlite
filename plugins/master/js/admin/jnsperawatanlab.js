$("#notif").hide();
$("#display_template").hide();

// tombol tambah diklik
$("#index").on('click', '#bukaform', function(){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  $("#form").show().load(baseURL + '/master/jnsperawatanlabform?t=' + mlite.token);
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
  var kd_jenis_prw = $('input:text[name=kd_jenis_prw]').val();
  var nm_perawatan = $('input:text[name=nm_perawatan]').val();
  var bagian_rs = $('input:text[name=bagian_rs]').val();
  var bhp = $('input:text[name=bhp]').val();
  var tarif_perujuk = $('input:text[name=tarif_perujuk]').val();
  var tarif_tindakan_dokter = $('input:text[name=tarif_tindakan_dokter]').val();
  var tarif_tindakan_petugas = $('input:text[name=tarif_tindakan_petugas]').val();
  var kso = $('input:text[name=kso]').val();
  var menejemen = $('input:text[name=menejemen]').val();
  var total_byr = $('input:text[name=total_byr]').val();
  var kd_pj = $('select[name=kd_pj]').val();
  var kelas = $('select[name=kelas]').val();
  var kategori = $('select[name=kategori]').val();
  var status = $('select[name=status]').val();

  var url = baseURL + '/master/jnsperawatanlabsave?t=' + mlite.token;

  $.post(url,{
    kd_jenis_prw: kd_jenis_prw,
    nm_perawatan: nm_perawatan,
    bagian_rs: bagian_rs,
    bhp: bhp,
    tarif_perujuk: tarif_perujuk,
    tarif_tindakan_dokter: tarif_tindakan_dokter,
    tarif_tindakan_petugas: tarif_tindakan_petugas,
    kso: kso,
    menejemen: menejemen,
    total_byr: total_byr,
    kd_pj: kd_pj,
    kelas: kelas,
    kategori: kategori, 
    status: status
  } ,function(data) {
      $("#display").show().load(baseURL + '/master/jnsperawatanlabdisplay?t=' + mlite.token);
      $("#form").hide();
      $("#tutupform").val("Buka Form");
      $("#tutupform").attr("id", "bukaform");
      $('#notif').html("<div class=\"alert alert-success alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
      "Data bahasa telah disimpan!"+
      "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
      "</div>").show();
  });
});

// ketika baris data diklik
$("#display").on("click", ".edit", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/master/jnsperawatanlabform?t=' + mlite.token;
  var kd_jenis_prw  = $(this).attr("data-kd_jenis_prw");

  $.post(url, {kd_jenis_prw: kd_jenis_prw} ,function(data) {
    // tampilkan data
    $("#form").html(data).show();
    $("#bukaform").val("Tutup Form");
    $("#bukaform").attr("id", "tutupform");
  });
});

// ketika baris data diklik
$("#display").on("click", ".template_laboratorium", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/master/templatelaboratorium?t=' + mlite.token;
  var kd_jenis_prw  = $(this).attr("data-kd_jenis_prw");

  $.post(url, {kd_jenis_prw: kd_jenis_prw} ,function(data) {
    // tampilkan data
    $("#display_template").html(data).show();
  });
});

$("#display_template").on("click",".delete_template", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/master/jnsperawatanlabtemplatehapus?t=' + mlite.token;
  var id_template = $(this).attr("data-id_template");

      // mengirimkan perintah penghapusan
      $.post(url, {
        id_template: id_template
      } ,function(data) {
        // sembunyikan form, tampilkan data yang sudah di perbaharui, tampilkan notif
        $("#form").hide();
        $("#display_template").hide();
        $('#notif').html("<div class=\"alert alert-danger alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
        "Data template telah dihapus!"+
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
        "</div>").show();
      });
});

$("#display_template").on("click","#tutup_template", function(event){
        $("#display_template").hide();
});

// ketika tombol hapus ditekan
$("#form").on("click","#hapus", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/master/jnsperawatanlabhapus?t=' + mlite.token;
  var kd_jenis_prw = $('input:text[name=kd_jenis_prw]').val();

  // tampilkan dialog konfirmasi
  bootbox.confirm("Apakah Anda yakin ingin menghapus data ini?", function(result){
    // ketika ditekan tombol ok
    if (result){
      // mengirimkan perintah penghapusan
      $.post(url, {
        kd_jenis_prw: kd_jenis_prw
      } ,function(data) {
        // sembunyikan form, tampilkan data yang sudah di perbaharui, tampilkan notif
        $("#form").hide();
        $("#tutupform").val("Buka Form");
        $("#tutupform").attr("id", "bukaform");
        $("#display").load(baseURL + '/master/jnsperawatanlabdisplay?t=' + mlite.token);
        $('#notif').html("<div class=\"alert alert-danger alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
        "Data bahasa telah dihapus!"+
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
        "</div>").show();
      });
    }
  });
});


// ketika inputbox pencarian diisi
$('input:text[name=cari]').on('input',function(e){
  var baseURL = mlite.url + '/' + mlite.admin;
  var url    = baseURL + '/master/jnsperawatanlabdisplay?t=' + mlite.token;
  var cari = $('input:text[name=cari]').val();

  if(cari!="") {
      $.post(url, {cari: cari} ,function(data) {
      // tampilkan data yang sudah di perbaharui
        $("#display").html(data).show();
      });
  } else {
      $("#display").load(baseURL + '/master/jnsperawatanlabdisplay?t=' + mlite.token);
  }

});
// end pencarian

// ketika tombol halaman ditekan
$("#display").on("click", ".halaman",function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/master/jnsperawatanlabdisplay?t=' + mlite.token;
  kd_hal  = $(this).attr("data-hal");

  $.post(url, {halaman: kd_hal} ,function(data) {
  // tampilkan data
    $("#display").html(data).show();
  });

});
// end halaman

function bersih(){
  $('input:text[name=id]').val("").removeAttr('disabled');
  $('input:text[name=nama_bahasa]').val("");
}
