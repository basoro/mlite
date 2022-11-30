// tombol tambah diklik
$("#index").on('click', '#bukaform', function(){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  $("#form").show().load(baseURL + '/jkn_mobile_fktp/mappingpoliform?t=' + mlite.token);
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
  var kd_poli_rs = $('select[name=kd_poli_rs]').val();
  var getPoli = $('#getPoli').val()

  var url = baseURL + '/jkn_mobile_fktp/mappingpolisave?t=' + mlite.token;

  $.post(url,{
    kd_poli_rs: kd_poli_rs,
    getPoli: getPoli
  } ,function(data) {
      $("#display").show().load(baseURL + '/jkn_mobile_fktp/mappingpolidisplay?t=' + mlite.token);
      $("#form").hide();
      $("#tutupform").val("Buka Form");
      $("#tutupform").attr("id", "bukaform");
      $('#notif').html("<div class=\"alert alert-success alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
      "Data pasien telah disimpan!"+
      "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
      "</div>").show();
  });
});

// ketika baris data diklik
$("#display").on("click", ".edit", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/jkn_mobile_fktp/mappingpoliform?t=' + mlite.token;
  var kd_poli_rs  = $(this).attr("data-kd_poli_rs");

  $.post(url, {kd_poli_rs: kd_poli_rs} ,function(data) {
    //console.log(data);
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
  var url = baseURL + '/jkn_mobile_fktp/mappingpolihapus?t=' + mlite.token;
  var kd_poli_rs = $('select[name=kd_poli_rs]').val();

  // tampilkan dialog konfirmasi
  bootbox.confirm("Apakah Anda yakin ingin menghapus data ini?", function(result){
    // ketika ditekan tombol ok
    if (result){
      // mengirimkan perintah penghapusan
      $.post(url, {
        kd_poli_rs: kd_poli_rs
      } ,function(data) {
        // sembunyikan form, tampilkan data yang sudah di perbaharui, tampilkan notif
        $("#form").hide();
        $("#tutupform").val("Buka Form");
        $("#tutupform").attr("id", "bukaform");
        $("#display").load(baseURL + '/jkn_mobile_fktp/mappingpolidisplay?t=' + mlite.token);
        $('#notif').html("<div class=\"alert alert-danger alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
        "Data pasien telah dihapus!"+
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
        "</div>").show();
      });
    }
  });
});

// ketika inputbox pencarian diisi
$('input:text[name=cari]').on('input',function(e){
  var baseURL = mlite.url + '/' + mlite.admin;
  var url    = baseURL + '/jkn_mobile_fktp/mappingpolidisplay?t=' + mlite.token;
  var cari = $('input:text[name=cari]').val();

  if(cari!="") {
      $.post(url, {cari: cari} ,function(data) {
      // tampilkan data yang sudah di perbaharui
        $("#display").html(data).show();
      });
  } else {
      $("#display").load(baseURL + '/jkn_mobile_fktp/mappingpolidisplay?t=' + batflat.token);
  }

});
// end pencarian

// ketika tombol halaman ditekan
$("#display").on("click", ".halaman",function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/jkn_mobile_fktp/mappingpolidisplay?t=' + mlite.token;
  kd_hal  = $(this).attr("data-hal");

  $.post(url, {halaman: kd_hal} ,function(data) {
  // tampilkan data
    $("#display").html(data).show();
  });

});
// end halaman

function bersih(){
  $('select[name=kd_poli_rs]').val("").removeAttr('disabled');
  $('input:text[name=getPoli]').val("");
}
