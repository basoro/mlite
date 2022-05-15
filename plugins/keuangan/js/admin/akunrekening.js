$("#notif").hide();
// tombol simpan diklik
$("#form").on("click", "#simpan", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var kd_rek = $('input:text[name=kd_rek]').val();
  var nm_rek = $('input:text[name=nm_rek]').val();
  var tipe = $('select[name=tipe]').val();
  var balance = $('select[name=balance]').val();

  var url = baseURL + '/keuangan/akunrekeningsave?t=' + mlite.token;

  $.post(url,{
    kd_rek: kd_rek,
    nm_rek: nm_rek,
    tipe: tipe,
    balance: balance
  } ,function(data) {
      $("#display").show().load(baseURL + '/keuangan/akunrekeningdisplay?t=' + mlite.token);
      $("#form").hide();
      $("#tutupform").val("Buka Form");
      $("#tutupform").attr("id", "bukaform");
      $('#notif').html("<div class=\"alert alert-success alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
      "Data akun rekening telah disimpan!"+
      "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
      "</div>").show();
  });
});

// ketika baris data diklik
$("#display").on("click", ".edit", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/keuangan/akunrekeningform?t=' + mlite.token;
  var kd_rek  = $(this).attr("data-kd_rek");

  $.post(url, {kd_rek: kd_rek} ,function(data) {
    // tampilkan data
    $("#form").html(data).show();
    $("#buka_form").val("Tutup Form");
    $("#buka_form").attr("id", "tutupform");
  });
});

// ketika baris data diklik
$("#akunrekening").on("click", "#buka_form", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/keuangan/akunrekeningform?t=' + mlite.token;
  var kd_rek  = $(this).attr("data-kd_rek");

  $.post(url, {kd_rek: kd_rek} ,function(data) {
    // tampilkan data
    $("#form").html(data).show();
    $("#buka_form").val("Tutup Form");
    $("#buka_form").attr("id", "tutupform");
  });
});

// tombol tutup form diklik
$("#akunrekening").on('click', '#tutupform', function(){
  event.preventDefault();
  $("#form").hide();
  $("#tutupform").val("Buka Form");
  $("#tutupform").attr("id", "buka_form");
});

// ketika baris data diklik
$("#jenis_kegiatan").on("click", ".kegiatan", function(event){
  var id  = $(this).attr("data-id");
  var kegiatan  = $(this).attr("data-kegiatan");
  //alert(kegiatan);
  $('input:hidden[name=id]').val(id);
  $('input:text[name=nama_kegiatan]').val(kegiatan);
});

// ketika baris data diklik
$("#rekeningtahun").on("click", ".rekeningtahun", function(event){
  var tahun  = $(this).attr("data-tahun");
  var kd_rek  = $(this).attr("data-kd_rek");
  var saldo_awal  = $(this).attr("data-saldo_awal");
  $('#kd_rek').val(kd_rek).change();
  $('input:text[name=tahun]').val(tahun);
  $('input:text[name=saldo_awal]').val(saldo_awal);
});
