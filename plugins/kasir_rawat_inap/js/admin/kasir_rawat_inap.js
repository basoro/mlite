// sembunyikan form dan notif
$("#form_rincian").hide();
$("#notif").hide();
$('#provider').hide();
$('#aturan_pakai').hide();

$('#manage').on('click', '#submit_periode_rawat_inap', function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/kasir_rawat_inap/display?t=' + mlite.token;
  var periode_rawat_inap  = $('input:text[name=periode_rawat_inap]').val();
  var periode_rawat_inap_akhir  = $('input:text[name=periode_rawat_inap_akhir]').val();
  var status_periksa = 'all';

  if(periode_rawat_inap == '') {
    alert('Tanggal awal masih kosong!')
  }
  if(periode_rawat_inap_akhir == '') {
    alert('Tanggal akhir masih kosong!')
  }

  $.post(url, {periode_rawat_inap: periode_rawat_inap, periode_rawat_inap_akhir: periode_rawat_inap_akhir, status_pulang: status_periksa} ,function(data) {
  // tampilkan data
    $("#form").show();
    $("#display").html(data).show();
    $("#form_rincian").hide();
    $("#form_soap").hide();
    $("#form_sep").hide();
    $("#notif").hide();
    $("#rincian").hide();
    $("#sep").hide();
    $("#soap").hide();
    $('.periode_rawat_inap').datetimepicker('remove');
  });

});

$('#manage').on('click', '#belum_periode_rawat_inap', function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/kasir_rawat_inap/display?t=' + mlite.token;
  var periode_rawat_inap  = $('input:text[name=periode_rawat_inap]').val();
  var periode_rawat_inap_akhir  = $('input:text[name=periode_rawat_inap_akhir]').val();
  var status_periksa = 'masuk';

  if(periode_rawat_inap == '') {
    alert('Tanggal awal masih kosong!')
  }
  if(periode_rawat_inap_akhir == '') {
    alert('Tanggal akhir masih kosong!')
  }

  $.post(url, {periode_rawat_inap: periode_rawat_inap, periode_rawat_inap_akhir: periode_rawat_inap_akhir, status_pulang: status_periksa} ,function(data) {
  // tampilkan data
    $("#form").show();
    $("#display").html(data).show();
    $("#form_rincian").hide();
    $("#form_soap").hide();
    $("#form_sep").hide();
    $("#notif").hide();
    $("#rincian").hide();
    $("#sep").hide();
    $("#soap").hide();
    $('.periode_rawat_inap').datetimepicker('remove');
  });

});

$('#manage').on('click', '#selesai_periode_rawat_inap', function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/kasir_rawat_inap/display?t=' + mlite.token;
  var periode_rawat_inap  = $('input:text[name=periode_rawat_inap]').val();
  var periode_rawat_inap_akhir  = $('input:text[name=periode_rawat_inap_akhir]').val();
  var status_periksa = 'pulang';

  if(periode_rawat_inap == '') {
    alert('Tanggal awal masih kosong!')
  }
  if(periode_rawat_inap_akhir == '') {
    alert('Tanggal akhir masih kosong!')
  }

  $.post(url, {periode_rawat_inap: periode_rawat_inap, periode_rawat_inap_akhir: periode_rawat_inap_akhir, status_pulang: status_periksa} ,function(data) {
  // tampilkan data
    $("#form").show();
    $("#display").html(data).show();
    $("#form_rincian").hide();
    $("#form_soap").hide();
    $("#form_sep").hide();
    $("#notif").hide();
    $("#rincian").hide();
    $("#sep").hide();
    $("#soap").hide();
    $('.periode_rawat_inap').datetimepicker('remove');
  });

});

$('#manage').on('click', '#lunas_periode_rawat_inap', function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/kasir_rawat_inap/display?t=' + mlite.token;
  var periode_rawat_inap  = $('input:text[name=periode_rawat_inap]').val();
  var periode_rawat_inap_akhir  = $('input:text[name=periode_rawat_inap_akhir]').val();
  var status_periksa = 'lunas';

  if(periode_rawat_inap == '') {
    alert('Tanggal awal masih kosong!')
  }
  if(periode_rawat_inap_akhir == '') {
    alert('Tanggal akhir masih kosong!')
  }

  $.post(url, {periode_rawat_inap: periode_rawat_inap, periode_rawat_inap_akhir: periode_rawat_inap_akhir, status_pulang: status_periksa} ,function(data) {
  // tampilkan data
    $("#form").show();
    $("#display").html(data).show();
    $("#form_rincian").hide();
    $("#form_soap").hide();
    $("#form_sep").hide();
    $("#notif").hide();
    $("#rincian").hide();
    $("#sep").hide();
    $("#soap").hide();
    $('.periode_rawat_inap').datetimepicker('remove');
  });

});

// tombol batal diklik
$("#rincian").on("click", "#selesai", function(event){
  bersih();
  $("#form_rincian").hide();
  $("#form_soap").hide();
  $("#form").show();
  $("#display").show();
  $("#rincian").hide();
  $("#soap").hide();
});

// ketika baris data diklik
$("#display").on("click", ".layanan_obat", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();

  var no_rawat = $(this).attr("data-no_rawat");
  var no_rkm_medis = $(this).attr("data-no_rkm_medis");
  var nm_pasien = $(this).attr("data-nm_pasien");

  $('input:text[name=no_rawat]').val(no_rawat);
  $('input:text[name=no_rkm_medis]').val(no_rkm_medis);
  $('input:text[name=nm_pasien]').val(nm_pasien);
  $("#display").hide();

  var url = baseURL + '/kasir_rawat_inap/rincian?t=' + mlite.token;
  $.post(url, {no_rawat : no_rawat,
  }, function(data) {
    // tampilkan data
    $("#form_rincian").show();
    $("#form").hide();
    $("#notif").hide();
    $("#rincian").html(data).show();
  });
});

// ketika inputbox pencarian diisi
$('input:text[name=tambahan_biaya]').on('input',function(e){
  var baseURL = mlite.url + '/' + mlite.admin;
  var url    = baseURL + '/kasir_rawat_inap/tambahanbiaya?t=' + mlite.token;
  var tambahan_biaya = $('input:text[name=tambahan_biaya]').val();

  if(tambahan_biaya!="") {
      $.post(url, {tambahan_biaya: tambahan_biaya} ,function(data) {
      // tampilkan data yang sudah di perbaharui
        $("#tambahan_biaya").html(data).show();
        $('#biaya').removeAttr("readonly");
        $('#nm_perawatan').removeAttr("readonly");
        $("#layanan").hide();
        $("#obat").hide();
      });
  }

});
// end pencarian

// ketika inputbox pencarian diisi
$('input:text[name=layanan]').on('input',function(e){
  var baseURL = mlite.url + '/' + mlite.admin;
  var url    = baseURL + '/kasir_rawat_inap/layanan?t=' + mlite.token;
  var layanan = $('input:text[name=layanan]').val();

  if(layanan!="") {
      $.post(url, {layanan: layanan} ,function(data) {
      // tampilkan data yang sudah di perbaharui
        $("#layanan").html(data).show();
        $("#obat").hide();
        $("#radiologi").hide();
        $("#laboratorium").hide();
        $('#biaya').attr("readonly", true);
        $('#nm_perawatan').attr("readonly", true);
      });
  }

});
// end pencarian

// ketika inputbox pencarian diisi
$('input:text[name=obat]').on('input',function(e){
  var baseURL = mlite.url + '/' + mlite.admin;
  var url    = baseURL + '/kasir_rawat_inap/obat?t=' + mlite.token;
  var obat = $('input:text[name=obat]').val();

  if(obat!="") {
      $.post(url, {obat: obat} ,function(data) {
      // tampilkan data yang sudah di perbaharui
        $("#obat").html(data).show();
        $("#layanan").hide();
        $("#radiologi").hide();
        $("#laboratorium").hide();
        $('#biaya').attr("readonly", true);
        $('#nm_perawatan').attr("readonly", true);
      });
  }

});
// end pencarian

// ketika inputbox pencarian diisi
$('input:text[name=laboratorium]').on('input',function(e){
  var baseURL = mlite.url + '/' + mlite.admin;
  var url    = baseURL + '/kasir_rawat_inap/laboratorium?t=' + mlite.token;
  var laboratorium = $('input:text[name=laboratorium]').val();

  if(laboratorium!="") {
      $.post(url, {laboratorium: laboratorium} ,function(data) {
      // tampilkan data yang sudah di perbaharui
        $("#laboratorium").html(data).show();
        $("#layanan").hide();
        $("#obat").hide();
        $("#radiologi").hide();
        $('#biaya').attr("readonly", true);
        $('#nm_perawatan').attr("readonly", true);
      });
  }

});
// end pencarian

// ketika inputbox pencarian diisi
$('input:text[name=radiologi]').on('input',function(e){
  var baseURL = mlite.url + '/' + mlite.admin;
  var url    = baseURL + '/kasir_rawat_inap/radiologi?t=' + mlite.token;
  var radiologi = $('input:text[name=radiologi]').val();

  if(radiologi!="") {
      $.post(url, {radiologi: radiologi} ,function(data) {
      // tampilkan data yang sudah di perbaharui
        $("#radiologi").html(data).show();
        $("#layanan").hide();
        $("#obat").hide();
        $("#laboratorium").hide();
        $('#biaya').attr("readonly", true);
        $('#nm_perawatan').attr("readonly", true);
      });
  }

});
// end pencarian

// ketika baris data diklik
$("#tambahan_biaya").on("click", ".pilih_tambahan_biaya", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();

  var kd_jenis_prw = $(this).attr("data-nama_biaya");
  var nm_perawatan = $(this).attr("data-nama_biaya");
  var biaya = $(this).attr("data-besar_biaya");
  var kat = $(this).attr("data-kat");

  $('input:hidden[name=kd_jenis_prw]').val(kd_jenis_prw);
  $('input:text[name=nm_perawatan]').val(nm_perawatan);
  $('input:text[name=biaya]').val(biaya);
  $('input:hidden[name=kat]').val(kat);

  $("#tambahan_biaya").hide();
  $('#provider').hide();
  $('#aturan_pakai').hide();
});

// ketika baris data diklik
$("#layanan").on("click", ".pilih_layanan", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();

  var kd_jenis_prw = $(this).attr("data-kd_jenis_prw");
  var nm_perawatan = $(this).attr("data-nm_perawatan");
  var biaya = $(this).attr("data-biaya");
  var kat = $(this).attr("data-kat");

  $('input:hidden[name=kd_jenis_prw]').val(kd_jenis_prw);
  $('input:text[name=nm_perawatan]').val(nm_perawatan);
  $('input:text[name=biaya]').val(biaya);
  $('input:hidden[name=kat]').val(kat);

  $("#layanan").hide();
  $('#provider').show();
  $('#aturan_pakai').hide();
});

// ketika baris data diklik
$("#obat").on("click", ".pilih_obat", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();

  var kode_brng = $(this).attr("data-kode_brng");
  var nama_brng = $(this).attr("data-nama_brng");
  var biaya = $(this).attr("data-ralan");
  var kat = $(this).attr("data-kat");

  $('input:hidden[name=kd_jenis_prw]').val(kode_brng);
  $('input:text[name=nm_perawatan]').val(nama_brng);
  $('input:text[name=biaya]').val(biaya);
  $('input:hidden[name=kat]').val(kat);

  /*$('#jumlah_jual').val(1);
  var jumlah_jual  = $('input:text[name=jumlah_jual]').val();

  $('#jumlah_jual').removeAttr("disabled");
  $('#potongan').removeAttr("disabled");
  $('#jumlah_jual').focus();

  var total = (Number(harga)) * (Number(jumlah_jual));
  $('input:text[name=total]').val(total);*/

  $('#obat').hide();
  $('#aturan_pakai').show();
  $('#rawat_inap_dr').show();
});

// ketika baris data diklik
$("#laboratorium").on("click", ".pilih_laboratorium", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();

  var kd_jenis_prw = $(this).attr("data-kd_jenis_prw");
  var nm_perawatan = $(this).attr("data-nm_perawatan");
  var biaya = $(this).attr("data-biaya");
  var kat = $(this).attr("data-kat");

  $('input:hidden[name=kd_jenis_prw]').val(kd_jenis_prw);
  $('input:text[name=nm_perawatan]').val(nm_perawatan);
  $('input:text[name=biaya]').val(biaya);
  $('input:hidden[name=kat]').val(kat);

  $("#laboratorium").hide();
  $('#provider').show();
  $('#aturan_pakai').hide();
});

// ketika baris data diklik
$("#radiologi").on("click", ".pilih_radiologi", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();

  var kd_jenis_prw = $(this).attr("data-kd_jenis_prw");
  var nm_perawatan = $(this).attr("data-nm_perawatan");
  var biaya = $(this).attr("data-biaya");
  var kat = $(this).attr("data-kat");

  $('input:hidden[name=kd_jenis_prw]').val(kd_jenis_prw);
  $('input:text[name=nm_perawatan]').val(nm_perawatan);
  $('input:text[name=biaya]').val(biaya);
  $('input:hidden[name=kat]').val(kat);

  $("#radiologi").hide();
  $('#provider').show();
  $('#aturan_pakai').hide();
});

// ketika tombol simpan diklik
$("#form_rincian").on("click", "#simpan_rincian", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();

  var no_rawat        = $('input:text[name=no_rawat]').val();
  var kd_jenis_prw 	  = $('input:hidden[name=kd_jenis_prw]').val();
  var nm_perawatan    = $('input:text[name=nm_perawatan]').val();
  var provider        = $('select[name=provider]').val();
  var kode_provider   = $('input:text[name=kode_provider]').val();
  var kode_provider2   = $('input:text[name=kode_provider2]').val();
  var tgl_perawatan   = $('input:text[name=tgl_billing]').val();
  var jam_rawat       = $('input:text[name=jam_billing]').val();
  var biaya           = $('input:text[name=biaya]').val();
  var aturan_pakai    = $('input:text[name=aturan_pakai]').val();
  var kat             = $('input:hidden[name=kat]').val();
  var jml             = $('input:text[name=jml]').val();

  var url = baseURL + '/kasir_rawat_inap/savedetail?t=' + mlite.token;
  $.post(url, {no_rawat : no_rawat,
  kd_jenis_prw   : kd_jenis_prw,
  nm_perawatan   : nm_perawatan,
  provider       : provider,
  kode_provider  : kode_provider,
  kode_provider2 : kode_provider2,
  tgl_perawatan  : tgl_perawatan,
  jam_rawat      : jam_rawat,
  biaya          : biaya,
  aturan_pakai   : aturan_pakai,
  kat            : kat,
  jml            : jml
  }, function(data) {
    // tampilkan data
    $("#display").hide();
    var url = baseURL + '/kasir_rawat_inap/rincian?t=' + mlite.token;
    $.post(url, {no_rawat : no_rawat,
    }, function(data) {
      // tampilkan data
      $("#rincian").html(data).show();
    });
    $("#rawat_inap_dr").hide();
    $("#rawat_inap_pr").hide();
    $('#biaya').attr("readonly", true);
    $('#nm_perawatan').attr("readonly", true);
    $('input:hidden[name=kd_jenis_prw]').val("");
    $('input:text[name=nm_perawatan]').val("");
    $('input:hidden[name=kat]').val("");
    $('input:text[name=biaya]').val("");
    $('input:text[name=nama_provider]').val("");
    $('input:text[name=nama_provider2]').val("");
    $('input:text[name=kode_provider]').val("");
    $('input:text[name=kode_provider2]').val("");
    $('#notif').html("<div class=\"alert alert-success alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
    "Data pasien telah disimpan!"+
    "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
    "</div>").show();
  });
});

// ketika tombol hapus ditekan
$("#rincian").on("click",".hapus_detail", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/kasir_rawat_inap/hapusdetail?t=' + mlite.token;
  var no_rawat = $(this).attr("data-no_rawat");
  var kd_jenis_prw = $(this).attr("data-kd_jenis_prw");
  var tgl_perawatan = $(this).attr("data-tgl_perawatan");
  var jam_rawat = $(this).attr("data-jam_rawat");
  var provider = $(this).attr("data-provider");

  // tampilkan dialog konfirmasi
  bootbox.confirm("Apakah Anda yakin ingin menghapus data ini?", function(result){
    // ketika ditekan tombol ok
    if (result){
      // mengirimkan perintah penghapusan
      $.post(url, {
        no_rawat: no_rawat,
        kd_jenis_prw: kd_jenis_prw,
        tgl_perawatan: tgl_perawatan,
        jam_rawat: jam_rawat,
        provider: provider
      } ,function(data) {
        var url = baseURL + '/kasir_rawat_inap/rincian?t=' + mlite.token;
        $.post(url, {no_rawat : no_rawat,
        }, function(data) {
          // tampilkan data
          $("#rincian").html(data).show();
        });
        $('#notif').html("<div class=\"alert alert-danger alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
        "Data rincian rawat inap telah dihapus!"+
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
        "</div>").show();
      });
    }
  });
});

// ketika tombol hapus ditekan
$("#rincian").on("click",".hapus_obat", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/kasir_rawat_inap/hapusobat?t=' + mlite.token;
  var kode_brng = $(this).attr("data-kode_brng");
  var no_resep = $(this).attr("data-no_resep");
  var no_rawat = $(this).attr("data-no_rawat");
  var tgl_peresepan = $(this).attr("data-tgl_peresepan");
  var jam_peresepan = $(this).attr("data-jam_peresepan");
  var jml = $(this).attr("data-jml");

  // tampilkan dialog konfirmasi
  bootbox.confirm("Apakah Anda yakin ingin menghapus data ini?", function(result){
    // ketika ditekan tombol ok
    if (result){
      // mengirimkan perintah penghapusan
      $.post(url, {
        kode_brng: kode_brng,
        no_resep: no_resep,
        no_rawat: no_rawat,
        tgl_peresepan: tgl_peresepan,
        jam_peresepan: jam_peresepan,
        jml: jml
      } ,function(data) {
        var url = baseURL + '/kasir_rawat_inap/rincian?t=' + mlite.token;
        $.post(url, {no_rawat : no_rawat,
        }, function(data) {
          // tampilkan data
          $("#rincian").html(data).show();
        });
        $('#notif').html("<div class=\"alert alert-danger alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
        "Data rincian obat rawat inap telah dihapus!"+
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
        "</div>").show();
      });
    }
  });
});

// ketika tombol hapus ditekan
$("#rincian").on("click",".hapus_laboratorium", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/kasir_rawat_inap/hapuslaboratorium?t=' + mlite.token;
  var no_rawat = $(this).attr("data-no_rawat");
  var kd_jenis_prw = $(this).attr("data-kd_jenis_prw");
  var tgl_perawatan = $(this).attr("data-tgl_periksa");
  var jam_rawat = $(this).attr("data-jam_periksa");
  var provider = $(this).attr("data-provider");

  // tampilkan dialog konfirmasi
  bootbox.confirm("Apakah Anda yakin ingin menghapus data ini?", function(result){
    // ketika ditekan tombol ok
    if (result){
      // mengirimkan perintah penghapusan
      $.post(url, {
        no_rawat: no_rawat,
        kd_jenis_prw: kd_jenis_prw,
        tgl_perawatan: tgl_perawatan,
        jam_rawat: jam_rawat,
        provider: provider
      } ,function(data) {
        var url = baseURL + '/kasir_rawat_inap/rincian?t=' + mlite.token;
        $.post(url, {no_rawat : no_rawat,
        }, function(data) {
          // tampilkan data
          $("#rincian").html(data).show();
        });
        $('#notif').html("<div class=\"alert alert-danger alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
        "Data rincian rawat inap telah dihapus!"+
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
        "</div>").show();
      });
    }
  });
});

// ketika tombol hapus ditekan
$("#rincian").on("click",".hapus_radiologi", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/kasir_rawat_inap/hapusradiologi?t=' + mlite.token;
  var no_rawat = $(this).attr("data-no_rawat");
  var kd_jenis_prw = $(this).attr("data-kd_jenis_prw");
  var tgl_perawatan = $(this).attr("data-tgl_periksa");
  var jam_rawat = $(this).attr("data-jam_periksa");
  var provider = $(this).attr("data-provider");

  // tampilkan dialog konfirmasi
  bootbox.confirm("Apakah Anda yakin ingin menghapus data ini?", function(result){
    // ketika ditekan tombol ok
    if (result){
      // mengirimkan perintah penghapusan
      $.post(url, {
        no_rawat: no_rawat,
        kd_jenis_prw: kd_jenis_prw,
        tgl_perawatan: tgl_perawatan,
        jam_rawat: jam_rawat,
        provider: provider
      } ,function(data) {
        var url = baseURL + '/kasir_rawat_inap/rincian?t=' + mlite.token;
        $.post(url, {no_rawat : no_rawat,
        }, function(data) {
          // tampilkan data
          $("#rincian").html(data).show();
        });
        $('#notif').html("<div class=\"alert alert-danger alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
        "Data rincian rawat inap telah dihapus!"+
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
        "</div>").show();
      });
    }
  });
});

// ketika tombol hapus ditekan
$("#rincian").on("click",".hapus_tambahan_biaya", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/kasir_rawat_inap/hapustambahanbiaya?t=' + mlite.token;
  var nama_biaya = $(this).attr("data-nama_biaya");
  var no_rawat = $(this).attr("data-no_rawat");

  // tampilkan dialog konfirmasi
  bootbox.confirm("Apakah Anda yakin ingin menghapus data ini?", function(result){
    // ketika ditekan tombol ok
    if (result){
      // mengirimkan perintah penghapusan
      $.post(url, {
        nama_biaya: nama_biaya,
        no_rawat: no_rawat
      } ,function(data) {
        var url = baseURL + '/kasir_rawat_inap/rincian?t=' + mlite.token;
        $.post(url, {no_rawat : no_rawat,
        }, function(data) {
          // tampilkan data
          $("#rincian").html(data).show();
        });
        $('#notif').html("<div class=\"alert alert-danger alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
        "Data tambahan biaya rawat inap telah dihapus!"+
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
        "</div>").show();
      });
    }
  });
});

// ketika inputbox potongan faktur diisi
$("#rincian").on("input","#potongan_faktur2", function(event){
  event.preventDefault();

  var jumlah_total_belanja = $('input:text[name=jumlah_total]').val()
  var potongan_faktur      = $('input:text[name=potongan_faktur]').val();
  potongan_faktur = potongan_faktur.replace(/\.|R|p/g,'');
  var jumlah_diskon               = (Number(jumlah_total_belanja)) - (Number(potongan_faktur));

  $('input:text[name=jumlah_harus_bayar]').val(jumlah_diskon);
  $('input:text[name=terbilang_jumlah_harus_bayar]').val(terbilang(Number(jumlah_diskon)));


  var jumlah_bayar        = $('input:text[name=jumlah_bayar]').val();
  jumlah_bayar = jumlah_bayar.replace(/\.|R|p/g,'')
  var kembalian           = (Number(jumlah_bayar)) - (Number(jumlah_diskon));

  if (kembalian  < 0 ){
    $('input:text[name=kembalian]').val("");
    $('input:text[name=terbilang_kembalian]').val("Kurang Bayar");
  }
  else if (jumlah_bayar == "" || jumlah_bayar == 0)
  {
    $('input:text[name=kembalian]').val(0);
    $('input:text[name=jumlah_bayar]').val(0);
    $('input:text[name=terbilang_kembalian]').val("");
  } else
  {
    $('input:text[name=kembalian]').val(kembalian);
    $('input:text[name=terbilang_kembalian]').val(terbilang(Number(kembalian)));
  }
});
// end potongan faktur

// ketika inputbox jumlah bayar diisi
$("#rincian").on("input","#jumlah_bayar2", function(event){
  event.preventDefault();

  var jumlah_harus_bayar  = $('input:text[name=jumlah_harus_bayar]').val();
  var jumlah_bayar        = $('input:text[name=jumlah_bayar]').val();
    jumlah_bayar = jumlah_bayar.replace(/\.|R|p/g,'')
  var kembalian           = (Number(jumlah_bayar)) - (Number(jumlah_harus_bayar));

  if (kembalian  < 0 ){
    $('input:text[name=kembalian]').val("");
    $('input:text[name=terbilang_kembalian]').val("Kurang Bayar");
  }
  else if (jumlah_bayar == "" || jumlah_bayar == 0)
  {
    $('input:text[name=kembalian]').val(0);
    $('input:text[name=jumlah_bayar]').val(0);
    $('input:text[name=terbilang_kembalian]').val("");
  } else
  {
    $('input:text[name=kembalian]').val(kembalian);
    $('input:text[name=terbilang_kembalian]').val(terbilang(Number(kembalian)));
  }

});
// end jumlah bayar

// tombol simpan semua yang dibawah di klick
$("#rincian").on("click","#simpan_billing", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var kd_billing        = $('input:text[name=kd_billing]').val();
  var no_rawat          = $('input:text[name=no_rawat]').val();
  var jumlah_total       = $('input:text[name=jumlah_total]').val();
  var potongan_faktur    = $('input:text[name=potongan_faktur]').val();
    potongan_faktur = potongan_faktur.replace(/\.|R|p/g,'')
  var jumlah_harus_bayar = $('input:text[name=jumlah_harus_bayar]').val();
  var jumlah_bayar       = $('input:text[name=jumlah_bayar]').val();
    jumlah_bayar = jumlah_bayar.replace(/\.|R|p/g,'')
  var tgl_billing            = $('#tgl_billing').val();
  var jam_billing            = $('#jam_billing').val();
  var keterangan         = $('select[name=keterangan]').val();
  if 	(potongan_faktur == "" )
  {
    alert ("potongan belum diisi ");
    $('input:text[name=potongan_faktur]').focus();

  }
  else if ( Number(jumlah_bayar) < Number(jumlah_harus_bayar && keterangan != 'Tunai'))
  {
    alert ("Jumlah bayar masih kurang ! ");
    $('input:text[name=jumlah_bayar]').focus();
  }
  else
  {
    // tampilkan dialog konfirmasi
    bootbox.confirm("Apakah data faktur sudah sesuai?", function(result){
      // ketika ditekan tombol ok
      if (result){
        // mengirimkan perintah penghapusan
        var url = baseURL + '/kasir_rawat_inap/save?t=' + mlite.token;
        $.post(url, {kd_billing : kd_billing,
        no_rawat              : no_rawat,
        jumlah_total       : jumlah_total,
        potongan    : potongan_faktur,
        tgl_billing   		   : tgl_billing,
        jam_billing: jam_billing,
        jumlah_harus_bayar : jumlah_harus_bayar,
        jumlah_bayar       : jumlah_bayar,
        keterangan         : keterangan,
        } ,function(data) {
          var url = baseURL + '/kasir_rawat_inap/rincian?t=' + mlite.token;
          $.post(url, {no_rawat : no_rawat,
          }, function(data) {
            // tampilkan data
            $("#rincian").html(data).show();
          });
          $('#notif').html("<div class=\"alert alert-success alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
          "Data rincian faktur rawat inap telah disimpan!"+
          "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
          "</div>").show();

        });
      }
    });

  }

});

// ketika tombol antrian ditekan
$("#rincian").on("click","#cetak_billing", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/kasir_rawat_inap/faktur?t=' + mlite.token;
  var no_rawat = $(this).attr("data-no_rawat");
  $.post(url, {no_rawat : no_rawat,
  }, function(data) {
    // tampilkan data
    if(data == 'OK') {
      bootbox.confirm("Apakah data faktur sudah sesuai? Jika berbeda, silahkan simpan dulu sebelum mencetak!", function(result){
        // ketika ditekan tombol ok
        if (result){
          window.open(baseURL + '/kasir_rawat_inap/faktur?show=besar&no_rawat=' + no_rawat + '&t=' + mlite.token);
        }
      });
    } else {
      bootbox.alert("Data faktur belum disimpan. Silahkan simpan dulu sebelum mencetak?");
    }
  });
});

// ketika tombol antrian ditekan
$("#rincian").on("click","#cetak_billing_kecil", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/kasir_rawat_inap/faktur?t=' + mlite.token;
  var no_rawat = $(this).attr("data-no_rawat");
  $.post(url, {no_rawat : no_rawat,
  }, function(data) {
    // tampilkan data
    if(data == 'OK') {
      bootbox.confirm("Apakah data faktur sudah sesuai? Jika berbeda, silahkan simpan dulu sebelum mencetak!", function(result){
        // ketika ditekan tombol ok
        if (result){
          window.open(baseURL + '/kasir_rawat_inap/faktur?show=kecil&no_rawat=' + no_rawat + '&t=' + mlite.token);
        }
      });
    } else {
      bootbox.alert("Data faktur belum disimpan. Silahkan simpan dulu sebelum mencetak!");
    }
  });
});

$("#rincian").on("click", "#selesai_billing", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  $("#layanan").hide();
  $("#obat").hide();
  bersih();
  //$("#display").show();
  $("#rincian").hide();
  $("#form_rincian").hide();
  var url    = baseURL + '/kasir_rawat_inap/display?t=' + mlite.token;
  $.post(url, {} ,function(data) {
  // tampilkan data
    $("#display").html(data).show();
    $("#notif").hide();
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

/* Fungsi formatRupiah */
function formatRupiah(angka, prefix){
	var number_string = angka.replace(/[^,\d]/g, '').toString(),
	split   		= number_string.split(','),
	sisa     		= split[0].length % 3,
	rupiah     		= split[0].substr(0, sisa),
	ribuan     		= split[0].substr(sisa).match(/\d{3}/gi);

	// tambahkan titik jika yang di input sudah menjadi angka ribuan
	if(ribuan){
		separator = sisa ? '.' : '';
		rupiah += separator + ribuan.join('.');
	}

	rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
	return prefix == undefined ? rupiah : (rupiah ? 'Rp.' + rupiah : '');
}

function terbilang(a){
	var bilangan = ['','Satu','Dua','Tiga','Empat','Lima','Enam','Tujuh','Delapan','Sembilan','Sepuluh','Sebelas'];

	// 1 - 11
	if(a < 12){
		var kalimat = bilangan[a];
	}
	// 12 - 19
	else if(a < 20){
		var kalimat = bilangan[a-10]+' Belas';
	}
	// 20 - 99
	else if(a < 100){
		var utama = a/10;
		var depan = parseInt(String(utama).substr(0,1));
		var belakang = a%10;
		var kalimat = bilangan[depan]+' Puluh '+bilangan[belakang];
	}
	// 100 - 199
	else if(a < 200){
		var kalimat = 'Seratus '+ terbilang(a - 100);
	}
	// 200 - 999
	else if(a < 1000){
		var utama = a/100;
		var depan = parseInt(String(utama).substr(0,1));
		var belakang = a%100;
		var kalimat = bilangan[depan] + ' Ratus '+ terbilang(belakang);
	}
	// 1,000 - 1,999
	else if(a < 2000){
		var kalimat = 'Seribu '+ terbilang(a - 1000);
	}
	// 2,000 - 9,999
	else if(a < 10000){
		var utama = a/1000;
		var depan = parseInt(String(utama).substr(0,1));
		var belakang = a%1000;
		var kalimat = bilangan[depan] + ' Ribu '+ terbilang(belakang);
	}
	// 10,000 - 99,999
	else if(a < 100000){
		var utama = a/100;
		var depan = parseInt(String(utama).substr(0,2));
		var belakang = a%1000;
		var kalimat = terbilang(depan) + ' Ribu '+ terbilang(belakang);
	}
	// 100,000 - 999,999
	else if(a < 1000000){
		var utama = a/1000;
		var depan = parseInt(String(utama).substr(0,3));
		var belakang = a%1000;
		var kalimat = terbilang(depan) + ' Ribu '+ terbilang(belakang);
	}
	// 1,000,000 - 	99,999,999
	else if(a < 100000000){
		var utama = a/1000000;
		var depan = parseInt(String(utama).substr(0,4));
		var belakang = a%1000000;
		var kalimat = terbilang(depan) + ' Juta '+ terbilang(belakang);
	}
	else if(a < 1000000000){
		var utama = a/1000000;
		var depan = parseInt(String(utama).substr(0,4));
		var belakang = a%1000000;
		var kalimat = terbilang(depan) + ' Juta '+ terbilang(belakang);
	}
	else if(a < 10000000000){
		var utama = a/1000000000;
		var depan = parseInt(String(utama).substr(0,1));
		var belakang = a%1000000000;
		var kalimat = terbilang(depan) + ' Milyar '+ terbilang(belakang);
	}
	else if(a < 100000000000){
		var utama = a/1000000000;
		var depan = parseInt(String(utama).substr(0,2));
		var belakang = a%1000000000;
		var kalimat = terbilang(depan) + ' Milyar '+ terbilang(belakang);
	}
	else if(a < 1000000000000){
		var utama = a/1000000000;
		var depan = parseInt(String(utama).substr(0,3));
		var belakang = a%1000000000;
		var kalimat = terbilang(depan) + ' Milyar '+ terbilang(belakang);
	}
	else if(a < 10000000000000){
		var utama = a/10000000000;
		var depan = parseInt(String(utama).substr(0,1));
		var belakang = a%10000000000;
		var kalimat = terbilang(depan) + ' Triliun '+ terbilang(belakang);
	}
	else if(a < 100000000000000){
		var utama = a/1000000000000;
		var depan = parseInt(String(utama).substr(0,2));
		var belakang = a%1000000000000;
		var kalimat = terbilang(depan) + ' Triliun '+ terbilang(belakang);
	}

	else if(a < 1000000000000000){
		var utama = a/1000000000000;
		var depan = parseInt(String(utama).substr(0,3));
		var belakang = a%1000000000000;
		var kalimat = terbilang(depan) + ' Triliun '+ terbilang(belakang);
	}

  else if(a < 10000000000000000){
		var utama = a/1000000000000000;
		var depan = parseInt(String(utama).substr(0,1));
		var belakang = a%1000000000000000;
		var kalimat = terbilang(depan) + ' Kuadriliun '+ terbilang(belakang);
	}

	var pisah = kalimat.split(' ');
	var full = [];
	for(var i=0;i<pisah.length;i++){
	 if(pisah[i] != ""){full.push(pisah[i]);}
	}
	return full.join(' ');
}
