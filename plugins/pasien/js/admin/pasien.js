// sembunyikan notif
$("#notif").hide();

// tombol buka form diklik
$("#index").on('click', '#bukaform', function(){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  $("#form").show().load(baseURL + '/pasien/form?t=' + mlite.token);
  $("#bukaform").val("Tutup Form");
  $("#bukaform").attr("id", "tutupform");
});

// tombol tutup form diklik
$("#index").on('click', '#tutupform', function(){
  event.preventDefault();
  $("#form").hide();
  $("#tutupform").val("Buka Form");
  $("#tutupform").attr("id", "bukaform");
});

// ketika inputbox no_rm_medis diklik
$("#form").on("click","#no_rkm_medis", function(event){
  var no_rkm_medis_baru = $("#no_rkm_medis_baru").val();
  $("#no_rkm_medis").val(no_rkm_medis_baru);
});

// tombol batal diklik
$("#form").on("click", "#batal", function(event){
  bersih();
});

// tombol simpan diklik
$("#form").on("click", "#simpan", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var no_rkm_medis = $('input:text[name=no_rkm_medis]').val();
  var nm_pasien = $('input:text[name=nm_pasien]').val();
  var nm_ibu = $('input:text[name=nm_ibu]').val();
  var tgl_lahir = $('#tgl_lahir').val();
  var jk = $('select[name=jk]').val();
  var gol_darah = $('select[name=gol_darah]').val();
  var stts_nikah = $('select[name=stts_nikah]').val();
  var agama = $('select[name=agama]').val();
  var pekerjaan = $('input:text[name=pekerjaan]').val();
  var no_ktp = $('input[name=no_ktp]').val();
  var alamat = $('textarea[name=alamat]').val();
  var no_tlp = $('input:text[name=no_tlp]').val();
  var tgl_daftar = $('input:text[name=tgl_daftar]').val();
  var email = $('input:text[name=email]').val();
  var pnd = $('select[name=pnd]').val();
  var keluarga = $('select[name=keluarga]').val();
  var namakeluarga = $('input:text[name=namakeluarga]').val();
  var kd_prop = $('#kd_prop').val();
  var kd_kab = $('#kd_kab').val();
  var kd_kec = $('#kd_kec').val();
  var kd_kel = $('#kd_kel').val();
  var nm_prop = $('#nm_prop').val();
  var nm_kab = $('#nm_kab').val();
  var nm_kec = $('#nm_kec').val();
  var nm_kel = $('#nm_kel').val();
  var kd_pj = $('select[name=kd_pj]').val();
  var no_peserta = $('input:text[name=no_peserta]').val();

  var url = baseURL + '/pasien/save?t=' + mlite.token;

  if(no_rkm_medis == '') {
    alert('Nomor rekam medis masih kosong!')
  }

  else if(nm_pasien == '') {
    alert('Nama pasien masih kosong!')
  }

  else if(nm_ibu == '') {
    alert('Nama ibu masih kosong!')
  }

  else if(tgl_lahir == '') {
    alert('Tanggal lahir masih kosong!')
  }

  else if(jk == '') {
    alert('Jenis kelamin belum dipilih!')
  }

  else {
    $.post(url,{
      no_rkm_medis: no_rkm_medis,
      nm_pasien: nm_pasien,
      nm_ibu: nm_ibu,
      tgl_lahir: tgl_lahir,
      jk: jk,
      gol_darah: gol_darah,
      stts_nikah: stts_nikah,
      agama: agama,
      pekerjaan: pekerjaan,
      no_ktp: no_ktp,
      alamat: alamat,
      no_tlp: no_tlp,
      tgl_daftar: tgl_daftar,
      email:email,
      pnd: pnd,
      keluarga: keluarga,
      namakeluarga: namakeluarga,
      kd_prop:kd_prop,
      kd_kab:kd_kab,
      kd_kec:kd_kec,
      kd_kel:kd_kel,
      nm_prop:nm_prop,
      nm_kab:nm_kab,
      nm_kec:nm_kec,
      nm_kel:nm_kel,
      kd_pj: kd_pj,
      no_peserta: no_peserta
    } ,function(data) {
      //alert(data);
      var data = JSON.parse(data);
      if(data.status == 'success')
      {
        $("#form").hide();
        $("#tutupform").val("Buka Form");
        $("#tutupform").attr("id", "bukaform");
        $('#notif').html("<div class=\"alert alert-success alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
        "Data pasien telah disimpan!"+
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
        "</div>").show();
      } else {
        $('#notif').html("<div class=\"alert alert-danger alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
        "Gagal menyimpan data pasien!"+
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
        "</div>").show();
      }
      $("#display").show().load(baseURL + '/pasien/display?t=' + mlite.token);
    });
  }

});

// ketika baris data diklik
$("#display").on("click", ".edit", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/pasien/form?t=' + mlite.token;
  var no_rkm_medis = $(this).attr("data-no_rkm_medis");

  $.post(url, {no_rkm_medis: no_rkm_medis} ,function(data) {
    // tampilkan data
    $("#form").html(data).show();
    $("#bukaform").val("Tutup Form");
    $("#bukaform").attr("id", "tutupform");
    $("#kartu").removeClass('hidden');
    $("#kirimwa").removeClass('hidden');
    $("#foto").removeClass('hidden');
    $("#hapus").removeClass('hidden');
  });
});

// ketika tombol hapus diklik
$("#form").on("click","#hapus", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/pasien/hapus?t=' + mlite.token;
  var no_rkm_medis = $('input:text[name=no_rkm_medis]').val();

  // tampilkan dialog konfirmasi
  bootbox.confirm("Apakah Anda yakin ingin menghapus data ini?", function(result){
    // ketika ditekan tombol ok
    if (result){
      // mengirimkan perintah penghapusan
      $.post(url, {
        no_rkm_medis: no_rkm_medis
      } ,function(data) {
        // sembunyikan form, tampilkan data yang sudah di perbaharui, tampilkan notif
        $("#form").hide();
        $("#tutupform").val("Buka Form");
        $("#tutupform").attr("id", "bukaform");
        $("#display").load(baseURL + '/pasien/display?t=' + mlite.token);
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
  var url    = baseURL + '/pasien/display?t=' + mlite.token;
  var cari = $('input:text[name=cari]').val();

  if(cari!="") {
      $.post(url, {cari: cari} ,function(data) {
        // tampilkan data yang sudah di perbaharui dan sembunyikan notif
        $("#notif").hide();
        $("#display").html(data).show();
      });
  } else {
      $("#notif").hide();
      $("#display").load(baseURL + '/pasien/display?t=' + mlite.token);
  }

});

// ketika tombol halaman ditekan
$("#display").on("click", ".halaman",function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/pasien/display?t=' + mlite.token;
  kd_hal  = $(this).attr("data-hal");
  var cari = $('input:text[name=cari]').val();
  if(cari !='') {
    $.post(url, {halaman: kd_hal, cari: cari} ,function(data) {
      // tampilkan data
      $("#notif").hide();
      $("#display").html(data).show();
    });
  } else {
    $.post(url, {halaman: kd_hal} ,function(data) {
      // tampilkan data
      $("#notif").hide();
      $("#display").html(data).show();
    });
  }

});

$("#display").on("click",".riwayat_perawatan", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var no_rkm_medis = $(this).attr("data-no_rkm_medis");
  window.open(baseURL + '/pasien/riwayatperawatan/' + no_rkm_medis + '?t=' + mlite.token);
});

// ketika tombol cetak kartu ditekan
$("#form").on("click","#kartu", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var no_rkm_medis = $(this).attr("data-no_rkm_medis");
  window.open(baseURL + '/pasien/kartu?no_rkm_medis=' + no_rkm_medis + '&t=' + mlite.token);
});

// ketika tombol cetak ditekan
$("#btn_cetak_jasper").click(function(event) {
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var cari = $('input:text[name=cari]').val();
  window.open(baseURL + '/jasper/pasien?cari=' + cari + '&t=' + mlite.token);
});

// reset form
function bersih(){
  $('input:text[name=no_rkm_medis]').val("").removeAttr('disabled');
  $('input:text[name=nm_pasien]').val("");
  $('#tgl_lahir').val("");
  $('input:radio[name=jk]').val("").removeAttr('checked');
  $('input:text[name=no_rkm_medis]').val("");
  $('input:text[name=pekerjaan]').val("");
  $('input:text[name=no_ktp]').val("");
  $('textarea[name=alamat]').val("");
  $('input:text[name=telepon]').val("");
  $('#tgl_daftar').val("");
  $('#email').val("");
  $('select').selectator('destroy');
  $('select[name=gol_darah]').val("");
  $('select').selectator();
}

// dropdown select pada baris data
$(document).click(function (event) {
    $('.dropdown-menu[data-parent]').hide();
});

$(document).on('click', '.table-responsive [data-toggle="dropdown"]', function () {
    if ($('body').hasClass('modal-open')) {
        throw new Error("This solution is not working inside a responsive table inside a modal, you need to find out a way to calculate the modal Z-index and add it to the element")
        return true;
    }

    $buttonGroup = $(this).parent();
    if (!$buttonGroup.attr('data-attachedUl')) {
        var ts = +new Date;
        $ul = $(this).siblings('ul');
        $ul.attr('data-parent', ts);
        $buttonGroup.attr('data-attachedUl', ts);
        $(window).resize(function () {
            $ul.css('display', 'none').data('top');
        });
    } else {
        $ul = $('[data-parent=' + $buttonGroup.attr('data-attachedUl') + ']');
    }
    if (!$buttonGroup.hasClass('open')) {
        $ul.css('display', 'none');
        return;
    }
    dropDownFixPosition($(this).parent(), $ul);
    function dropDownFixPosition(button, dropdown) {
        var dropDownTop = button.offset().top + button.outerHeight();
        dropdown.css('top', dropDownTop-60 + "px");
        dropdown.css('left', button.offset().left+7 + "px");
        dropdown.css('position', "absolute");

        dropdown.css('width', dropdown.width());
        dropdown.css('heigt', dropdown.height());
        dropdown.css('display', 'block');
        dropdown.appendTo('body');
    }
});

$('body').on('hidden.bs.modal', '.modal', function () {
    $(this).removeData('bs.modal');
});

$(function () {
    if (window.location.href.indexOf("nama") > -1) {
        var baseURL = mlite.url + '/' + mlite.admin;
        event.preventDefault();
        $("#form").show().load(baseURL + '/pasien/form?t=' + mlite.token);
        $("#bukaform").val("Tutup Form");
        $("#bukaform").attr("id", "tutupform");
    }
});
