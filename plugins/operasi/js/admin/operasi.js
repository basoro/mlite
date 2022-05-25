// sembunyikan form dan notif
$("#form_rincian").hide();
$("#form_soap").hide();
$("#form_sep").hide();
$("#form_berkasdigital").hide();
$("#histori_pelayanan").hide();
$("#notif").hide();
$('#provider').hide();
$('#aturan_pakai').hide();
$("#tanggal_keluar").hide();
$("#diagnosa_keluar").hide();

// tombol buka form diklik
$("#index").on('click', '#bukaform', function(){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  $("#form").show().load(baseURL + '/operasi/form?t=' + mlite.token);
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

// tombol batal diklik
$("#form").on("click", "#batal", function(event){
  $("#pasien").hide();
  $('input:text[name=pasien]').val("");
  $('input:text[name=jk]').val("");
  $('input:text[name=stts_daftar]').val("");
  $('input:text[name=no_tlp]').val("");
  $('input:text[name=no_rawat]').removeAttr("disabled", true);
  $('input:text[name=no_reg]').removeAttr("disabled", true);
  bersih();
});

// tombol  diklik
$("#form").on("click", "#simpan", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var no_rawat = $('input:text[name=no_rawat]').val();
  var tgl_operasi = $('#tgl_operasi').val();
  var jenis_anasthesi = $('input:text[name=jenis_anasthesi]').val();
  var kategori = $('select[name=kategori]').val();
  var operator1 = $('select[name=operator1]').val();
  var operator2 = $('select[name=operator2]').val();
  var operator3 = $('select[name=operator3]').val();
  var asisten_operator1 = $('select[name=asisten_operator1]').val();
  var asisten_operator2 = $('select[name=asisten_operator2]').val();
  var dokter_anak = $('select[name=dokter_anak]').val();
  var perawaat_resusitas = $('select[name=perawaat_resusitas]').val();
  var dokter_anestesi = $('select[name=dokter_anestesi]').val();
  var asisten_anestesi = $('select[name=asisten_anestesi]').val();
  var bidan = $('select[name=bidan]').val();
  var perawat_luar = $('select[name=perawat_luar]').val();
  var kode_paket = $('select[name=kode_paket]').val();
  var status = $('select[name=status]').val();

  var url = baseURL + '/operasi/save?t=' + mlite.token;

  if(no_rawat == '') {
    bootbox.alert("Pasien belum dipilih!");
  } else {
    $.post(url,{
      no_rawat: no_rawat,
      tgl_operasi: tgl_operasi,
      jenis_anasthesi: jenis_anasthesi,
      kategori: kategori,
      operator1: operator1,
      operator2: operator2,
      operator3: operator3,
      asisten_operator1: asisten_operator1,
      asisten_operator2: asisten_operator2,
      dokter_anak: dokter_anak,
      perawaat_resusitas: perawaat_resusitas,
      dokter_anestesi: dokter_anestesi,
      asisten_anestesi: asisten_anestesi,
      bidan: bidan,
      perawat_luar: perawat_luar,
      kode_paket: kode_paket,
      status: status
    } ,function(data) {
      console.log(data);
      $("#display").show().load(baseURL + '/operasi/display?t=' + mlite.token);
      bersih();
      $("#status_pendaftaran").hide();
      $('#notif').html("<div class=\"alert alert-success alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
      "Data pasien telah disimpan!"+
      "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
      "</div>").show();
    });
  }
});

$("#display").on("click",".riwayat_perawatan", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var no_rkm_medis = $(this).attr("data-no_rkm_medis");
  window.open(baseURL + '/pasien/riwayatperawatan/' + no_rkm_medis + '?t=' + mlite.token);
});

// ketika baris data diklik
$("#display").on("click", ".edit", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/operasi/form?t=' + mlite.token;
  var no_rawat = $(this).attr("data-no_rawat");
  $.post(url, {no_rawat: no_rawat} ,function(data) {
    console.log(data);
    // tampilkan data
    //$('#kd_rek').val(kd_rek).change();
    $("#form").html(data).show();
    $("#cari_pasien").hide();
  });
});

// ketika tombol hapus ditekan
$("#form").on("click","#hapus", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/operasi/hapus?t=' + mlite.token;
  //var no_rawat = $(this).attr("data-no_rawat");
  var no_rawat = $('input:text[name=no_rawat]').val();

  // tampilkan dialog konfirmasi
  bootbox.confirm("Apakah Anda yakin ingin menghapus data ini?", function(result){
    // ketika ditekan tombol ok
    if (result){
      // mengirimkan perintah penghapusan
      $.post(url, {
        no_rawat: no_rawat
      } ,function(data) {
        // sembunyikan form, tampilkan data yang sudah di perbaharui, tampilkan notif
        $("#display").load(baseURL + '/operasi/display?t=' + mlite.token);
        bersih();
        $('#notif').html("<div class=\"alert alert-danger alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
        "Data pasien telah dihapus!"+
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
        "</div>").show();
      });
    }
  });
});

$('#manage').on('click', '#submit_periode_rawat', function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/operasi/display?t=' + mlite.token;
  var periode_rawat  = $('input:text[name=periode_rawat]').val();
  var periode_rawat_akhir  = $('input:text[name=periode_rawat_akhir]').val();

  if(periode_rawat == '') {
    alert('Tanggal awal masih kosong!')
  }
  if(periode_rawat_akhir == '') {
    alert('Tanggal akhir masih kosong!')
  }

  $.post(url, {periode_rawat: periode_rawat, periode_rawat_akhir: periode_rawat_akhir} ,function(data) {
  // tampilkan data
    $("#form").show();
    $("#display").html(data).show();
    $("#form_rincian").hide();
    $("#notif").hide();
    $("#rincian").hide();
    $('.periode_rawat').datetimepicker('remove');
  });

  event.stopPropagation();

});

// tombol batal diklik
$("#form_rincian").on("click", "#selesai", function(event){
  bersih();
  $("#form_berkasdigital").hide();
  $("#form_rincian").hide();
  $("#form_soap").hide();
  $("#form").show();
  $("#display").show();
  $("#rincian").hide();
  $("#soap").hide();
  $("#berkasdigital").hide();
});

// ketika inputbox pencarian diisi
$('input:text[name=obat]').on('input',function(e){
  var baseURL = mlite.url + '/' + mlite.admin;
  var url    = baseURL + '/operasi/obat?t=' + mlite.token;
  var obat = $('input:text[name=obat]').val();

  if(obat!="") {
      $.post(url, {obat: obat} ,function(data) {
      // tampilkan data yang sudah di perbaharui
        $("#obat").html(data).show();
        $("#layanan").hide();
      });
  }

});
// end pencarian

// ketika baris data diklik
$("#obat").on("click", ".pilih_obat", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();

  var kd_obat = $(this).attr("data-kd_obat");
  var nm_obat = $(this).attr("data-nm_obat");
  var hargasatuan = $(this).attr("data-hargasatuan");

  $('input:text[name=nm_obat]').val(nm_obat);
  $('input:hidden[name=kd_obat]').val(kd_obat);
  $('input:hidden[name=hargasatuan]').val(hargasatuan);

  $('#obat').hide();
  $('#rawat_jl_dr').show();
});

// ketika tombol simpan diklik
$("#form_rincian").on("click", "#simpan_rincian", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();

  var no_rawat        = $('input:text[name=no_rawat]').val();
  var kd_obat         = $('input:hidden[name=kd_obat]').val();
  var hargasatuan 	  = $('input:hidden[name=hargasatuan]').val();
  var jumlah          = $('input:text[name=jumlah]').val();

  var url = baseURL + '/operasi/savedetail?t=' + mlite.token;
  $.post(url, {no_rawat : no_rawat,
  kd_obat       : kd_obat,
  hargasatuan   : hargasatuan,
  jumlah        : jumlah
  }, function(data) {
    // tampilkan data
    $("#display").hide();
    var url = baseURL + '/operasi/rincian?t=' + mlite.token;
    $.post(url, {no_rawat : no_rawat,
    }, function(data) {
      // tampilkan data
      $("#rincian").html(data).show();
    });
    $('input:hidden[name=kd_obat]').val("");
    $('input:text[name=nm_obat]').val("");
    $('input:hidden[name=hargasatuan]').val("");
    $('input:text[name=jumlah]').val("");
    $('#notif').html("<div class=\"alert alert-success alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
    "Data obat telah disimpan!"+
    "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
    "</div>").show();
  });
});

// ketika tombol hapus ditekan
$("#rincian").on("click",".hapus_detail", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/operasi/hapusdetail?t=' + mlite.token;
  var no_rawat = $(this).attr("data-no_rawat");
  var kd_obat = $(this).attr("data-kd_obat");
  var tanggal = $(this).attr("data-tanggal");

  // tampilkan dialog konfirmasi
  bootbox.confirm("Apakah Anda yakin ingin menghapus data ini?", function(result){
    // ketika ditekan tombol ok
    if (result){
      // mengirimkan perintah penghapusan
      $.post(url, {
        no_rawat: no_rawat,
        kd_obat: kd_obat,
        tanggal: tanggal
      } ,function(data) {
        var url = baseURL + '/operasi/rincian?t=' + mlite.token;
        $.post(url, {no_rawat : no_rawat,
        }, function(data) {
          // tampilkan data
          $("#rincian").html(data).show();
        });
        $('#notif').html("<div class=\"alert alert-danger alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
        "Data rincian rawat jalan telah dihapus!"+
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
        "</div>").show();
      });
    }
  });
});

// ketika tombol hapus ditekan
$("#rincian").on("click",".hapus_resep_obat", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/operasi/hapusresep?t=' + mlite.token;
  var no_resep = $(this).attr("data-no_resep");
  var no_rawat = $(this).attr("data-no_rawat");
  var tgl_peresepan = $(this).attr("data-tgl_peresepan");
  var jam_peresepan = $(this).attr("data-jam_peresepan");

  // tampilkan dialog konfirmasi
  bootbox.confirm("Apakah Anda yakin ingin menghapus data ini?", function(result){
    // ketika ditekan tombol ok
    if (result){
      // mengirimkan perintah penghapusan
      $.post(url, {
        no_resep: no_resep,
        no_rawat: no_rawat,
        tgl_peresepan: tgl_peresepan,
        jam_peresepan: jam_peresepan
      } ,function(data) {
        var url = baseURL + '/operasi/rincian?t=' + mlite.token;
        $.post(url, {no_rawat : no_rawat,
        }, function(data) {
          // tampilkan data
          $("#rincian").html(data).show();
        });
        $('#notif').html("<div class=\"alert alert-danger alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
        "Data rincian rawat jalan telah dihapus!"+
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
        "</div>").show();
      });
    }
  });
});

function bersih(){
  $('input:text[name=no_rawat]').val("");
  $('input:text[name=no_rkm_medis]').val("");
  $('input:text[name=nm_pasien]').val("");
  $('input:text[name=tgl_perawatan]').val("{?=date('Y-m-d')?}");
  $('input:text[name=tgl_registrasi]').val("{?=date('Y-m-d')?}");
  $('input:text[name=tgl_lahir]').val("");
  $('input:text[name=jenis_kelamin]').val("");
  $('input:text[name=alamat]').val("");
  $('input:text[name=telepon]').val("");
  $('input:text[name=pekerjaan]').val("");
  $('input:text[name=layanan]').val("");
  $('input:text[name=obat]').val("");
  $('input:text[name=nama_jenis]').val("");
  $('input:text[name=jumlah_jual]').attr("disabled", true);
  $('input:text[name=potongan]').attr("disabled", true);
  $('input:text[name=harga_jual]').val("");
  $('input:text[name=total]').val("");
  $('input:text[name=no_reg]').val("");
}

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
