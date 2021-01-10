$("#notif").hide();
// tombol tambah diklik
$("#index").on('click', '#bukaform', function(){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  $("#form").show().load(baseURL + '/master/jnsperawataninapform?t=' + mlite.token);
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
  var kd_kategori = $('select[name=kd_kategori]').val();
  var material = $('input:text[name=material]').val();
  var bhp = $('input:text[name=bhp]').val();
  var tarif_tindakandr = $('input:text[name=tarif_tindakandr]').val();
  var tarif_tindakanpr = $('input:text[name=tarif_tindakanpr]').val();
  var kso = $('input:text[name=kso]').val();
  var menejemen = $('input:text[name=menejemen]').val();
  var total_byrdr = $('input:text[name=total_byrdr]').val();
  var total_byrpr = $('input:text[name=total_byrpr]').val();
  var total_byrdrpr = $('input:text[name=total_byrdrpr]').val();
  var kd_pj = $('select[name=kd_pj]').val();
  var kd_bangsal = $('select[name=kd_bangsal]').val();
  var kelas = $('select[name=kelas]').val();
  var status = $('select[name=status]').val();

  var url = baseURL + '/master/jnsperawataninapsave?t=' + mlite.token;

  $.post(url,{
    kd_jenis_prw: kd_jenis_prw,
    nm_perawatan: nm_perawatan,
    kd_kategori: kd_kategori,
    material: material,
    bhp: bhp,
    tarif_tindakandr: tarif_tindakandr,
    tarif_tindakanpr: tarif_tindakanpr,
    kso: kso,
    menejemen: menejemen,
    total_byrdr: total_byrdr,
    total_byrpr: total_byrpr,
    total_byrdrpr: total_byrdrpr,
    kd_pj: kd_pj,
    kd_bangsal: kd_bangsal,
    kelas: kelas, 
    status: status
  } ,function(data) {
      $("#display").show().load(baseURL + '/master/jnsperawataninapdisplay?t=' + mlite.token);
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
  var url    = baseURL + '/master/jnsperawataninapform?t=' + mlite.token;
  var kd_jenis_prw  = $(this).attr("data-kd_jenis_prw");

  $.post(url, {kd_jenis_prw: kd_jenis_prw} ,function(data) {
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
  var url = baseURL + '/master/jnsperawataninaphapus?t=' + mlite.token;
  var id = $('input:text[name=id]').val();

  // tampilkan dialog konfirmasi
  bootbox.confirm("Apakah Anda yakin ingin menghapus data ini?", function(result){
    // ketika ditekan tombol ok
    if (result){
      // mengirimkan perintah penghapusan
      $.post(url, {
        id: id
      } ,function(data) {
        // sembunyikan form, tampilkan data yang sudah di perbaharui, tampilkan notif
        $("#form").hide();
        $("#tutupform").val("Buka Form");
        $("#tutupform").attr("id", "bukaform");
        $("#display").load(baseURL + '/master/jnsperawataninapdisplay?t=' + mlite.token);
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
  var url    = baseURL + '/master/jnsperawataninapdisplay?t=' + mlite.token;
  var cari = $('input:text[name=cari]').val();

  if(cari!="") {
      $.post(url, {cari: cari} ,function(data) {
      // tampilkan data yang sudah di perbaharui
        $("#display").html(data).show();
      });
  } else {
      $("#display").load(baseURL + '/master/jnsperawataninapdisplay?t=' + mlite.token);
  }

});
// end pencarian

// ketika tombol halaman ditekan
$("#display").on("click", ".halaman",function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/master/jnsperawataninapdisplay?t=' + mlite.token;
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
