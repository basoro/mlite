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
    $("#bukaform").val("Tutup Form");
    $("#bukaform").attr("id", "tutupform");
  });
});
