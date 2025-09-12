$("#notif").hide();
// tombol tambah diklik
$("#index").on('click', '#bukaform', function(){
  var baseURL = mlite.url + '/' + mlite.admin;
  $("#form").show().load(baseURL + '/master/penyakitform?t=' + mlite.token);
  $("#bukaform").val("Tutup Form");
  $("#bukaform").attr("id", "tutupform");
});

$("#index").on('click', '#tutupform', function(){
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
  var kd_penyakit = $('input:text[name=kd_penyakit]').val();
  var nm_penyakit = $('input:text[name=nm_penyakit]').val();
  var ciri_ciri = $('textarea[name=ciri_ciri]').val();
  var keterangan = $('input:text[name=keterangan]').val();
  var kd_ktg = $('select[name=kd_ktg]').val();
  var status = $('select[name=status]').val();

  var url = baseURL + '/master/penyakitsave?t=' + mlite.token;

  $.post(url,{
    kd_penyakit: kd_penyakit,
    nm_penyakit: nm_penyakit,
    ciri_ciri: ciri_ciri,
    keterangan: keterangan,
    kd_ktg: kd_ktg,
    status: status
  } ,function(data) {
      $("#display").show().load(baseURL + '/master/penyakitdisplay?t=' + mlite.token);
      $("#form").hide();
      $("#tutupform").val("Buka Form");
      $("#tutupform").attr("id", "bukaform");
      $('#notif').html("<div class=\"alert alert-success alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
      "Data telah disimpan!"+
      "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
      "</div>").show();
  });
});

// ketika baris data diklik
$("#display").on("click", ".edit", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/master/penyakitform?t=' + mlite.token;
  var kd_penyakit  = $(this).attr("data-kd_penyakit");

  $.post(url, {kd_penyakit: kd_penyakit} ,function(data) {
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
  var url = baseURL + '/master/penyakithapus?t=' + mlite.token;
  var kd_penyakit = $('input:text[name=kd_penyakit]').val();

  // tampilkan dialog konfirmasi
  bootbox.confirm("Apakah Anda yakin ingin menghapus data ini?", function(result){
    // ketika ditekan tombol ok
    if (result){
      // mengirimkan perintah penghapusan
      $.post(url, {
        kd_penyakit: kd_penyakit
      } ,function(data) {
        // sembunyikan form, tampilkan data yang sudah di perbaharui, tampilkan notif
        $("#form").hide();
        $("#tutupform").val("Buka Form");
        $("#tutupform").attr("id", "bukaform");
        $("#display").load(baseURL + '/master/penyakitdisplay?t=' + mlite.token);
        $('#notif').html("<div class=\"alert alert-danger alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
        "Data telah dihapus!"+
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
        "</div>").show();
      });
    }
  });
});

// ketika inputbox pencarian diisi
$('input:text[name=cari]').on('input',function(e){
  var baseURL = mlite.url + '/' + mlite.admin;
  var url    = baseURL + '/master/penyakitdisplay?t=' + mlite.token;
  var cari = $('input:text[name=cari]').val();

  if(cari!="") {
      $.post(url, {cari: cari} ,function(data) {
      // tampilkan data yang sudah di perbaharui
        $("#display").html(data).show();
      });
  } else {
      $("#display").load(baseURL + '/master/penyakitdisplay?t=' + mlite.token);
  }

});
// end pencarian

// ketika tombol halaman ditekan
$("#display").on("click", ".halaman",function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/master/penyakitdisplay?t=' + mlite.token;
  kd_hal  = $(this).attr("data-hal");

  $.post(url, {halaman: kd_hal} ,function(data) {
  // tampilkan data
    $("#display").html(data).show();
  });

});
// end halaman

function bersih(){
  $('input:text[name=kd_penyakit]').val("").removeAttr('disabled');
  $('input:text[name=nm_penyakit]').val("");
  $('textarea[name=ciri_ciri]').val("");
  $('input:text[name=keterangan]').val("");
  $('select[name=kd_ktg]').val("");
  $('select[name=status]').val("Tidak Menular");
}