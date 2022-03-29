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
  $("#form").show().load(baseURL + '/rawat_inap/form?t=' + mlite.token);
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
  var kd_kamar = $('select[name=kd_kamar]').val();
  var kd_dokter = $('select[name=kd_dokter]').val();
  var tgl_masuk = $('#tgl_masuk').val();
  var jam_masuk = $('#jam_masuk').val();
  var lama = $('input:text[name=lama]').val();
  var diagnosa_awal = $('input:text[name=diagnosa_awal]').val();

  var url = baseURL + '/rawat_inap/save?t=' + mlite.token;

  if(kd_kamar == '' || diagnosa_awal == '' || tgl_masuk == ''|| jam_masuk == ''|| lama == '') {
    bootbox.alert("Isian ada yang kosong!");
  } else {
    $.post(url,{
      no_rawat: no_rawat,
      kd_kamar: kd_kamar,
      kd_dokter: kd_dokter,
      tgl_masuk: tgl_masuk,
      jam_masuk: jam_masuk,
      lama: lama,
      diagnosa_awal: diagnosa_awal
    } ,function(data) {
      $("#display").show().load(baseURL + '/rawat_inap/display?t=' + mlite.token);
      bersih();
      $("#status_pendaftaran").hide();
      $('#notif').html("<div class=\"alert alert-success alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
      "Data pasien telah disimpan!"+
      "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
      "</div>").show();
    });
  }
});

// tombol  diklik
$("#form").on("click", "#simpan_keluar", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var no_rawat = $('input:text[name=no_rawat]').val();
  var kd_kamar = $('select[name=kd_kamar]').val();
  var tgl_keluar = $('#tgl_keluar').val();
  var jam_keluar = $('#jam_keluar').val();
  var lama = $('input:text[name=lama]').val();
  var diagnosa_akhir = $('input:text[name=diagnosa_akhir]').val();
  var stts_pulang = $('select[name=stts_pulang]').val();

  var url = baseURL + '/rawat_inap/savekeluar?t=' + mlite.token;

  if(stts_pulang == '' || diagnosa_akhir == '' || tgl_keluar == ''|| jam_keluar == ''|| lama == '') {
    bootbox.alert("Isian ada yang kosong!");
  } else {
    $.post(url,{
      no_rawat: no_rawat,
      kd_kamar: kd_kamar,
      tgl_keluar: tgl_keluar,
      jam_keluar: jam_keluar,
      lama: lama,
      diagnosa_akhir: diagnosa_akhir,
      stts_pulang: stts_pulang
    } ,function(data) {
      $("#display").show().load(baseURL + '/rawat_inap/display?t=' + mlite.token);
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
  var url = baseURL + '/rawat_inap/form?t=' + mlite.token;
  var no_rawat = $(this).attr("data-no_rawat");
  $.post(url, {no_rawat: no_rawat} ,function(data) {
    // tampilkan data
    $("#form").html(data).show();
    $("#stts_daftar").hide();
    $("#cari_pasien").hide();
    $("#tanggal_keluar").show();
    $("#diagnosa_masuk").hide();
    $("#diagnosa_keluar").show();
    //var url    				= baseURL + '/rawat_inap/statusdaftar?t=' + mlite.token;

    //$.post(url, {no_rawat: no_rawat} ,function(data) {
    //  $("#stts_daftar").html(data).show();
    //});
  });
});

// ketika baris data diklik
$("#display").on("click", ".set_dpjp___", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/rawat_inap/setdpjp?t=' + mlite.token;
  var no_rawat = $(this).attr("data-no_rawat");
  var kd_dokter = 'D0000063';

  $.post(url, {no_rawat: no_rawat, kd_dokter: kd_dokter} ,function(data) {
    // tampilkan data
    $("#display").show().load(baseURL + '/rawat_inap/display?t=' + mlite.token);
    $('#notif').html("<div class=\"alert alert-success alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
    "Data pasien telah disimpan!"+
    "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
    "</div>").show();
  });
});

// ketika tombol hapus ditekan
$("#form").on("click","#hapus", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/rawat_inap/hapus?t=' + mlite.token;
  //var no_rawat = $(this).attr("data-no_rawat");
  var no_rawat = $('input:text[name=no_rawat]').val();
  var tgl_masuk = $('input:text[name=tgl_masuk]').val();
  var jam_masuk = $('input:text[name=jam_masuk]').val();

  // tampilkan dialog konfirmasi
  bootbox.confirm("Apakah Anda yakin ingin menghapus data ini?", function(result){
    // ketika ditekan tombol ok
    if (result){
      // mengirimkan perintah penghapusan
      $.post(url, {
        no_rawat: no_rawat,
        tgl_masuk: tgl_masuk,
        jam_masuk: jam_masuk
      } ,function(data) {
        // sembunyikan form, tampilkan data yang sudah di perbaharui, tampilkan notif
        $("#display").load(baseURL + '/rawat_inap/display?t=' + mlite.token);
        bersih();
        $('#notif').html("<div class=\"alert alert-danger alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
        "Data pasien telah dihapus!"+
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
        "</div>").show();
      });
    }
  });
});

$('#manage').on('click', '#submit_periode_rawat_inap', function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/rawat_inap/display?t=' + mlite.token;
  var periode_rawat_inap  = $('input:text[name=periode_rawat_inap]').val();
  var periode_rawat_inap_akhir  = $('input:text[name=periode_rawat_inap_akhir]').val();
  var status_pulang = 'all';

  if(periode_rawat_inap == '') {
    alert('Tanggal awal masih kosong!')
  }
  if(periode_rawat_inap_akhir == '') {
    alert('Tanggal akhir masih kosong!')
  }

  $.post(url, {periode_rawat_inap: periode_rawat_inap, periode_rawat_inap_akhir: periode_rawat_inap_akhir, status_pulang: status_pulang} ,function(data) {
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

  event.stopPropagation();

});

$('#manage').on('click', '#masuk_periode_rawat_inap', function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/rawat_inap/display?t=' + mlite.token;
  var periode_rawat_inap  = $('input:text[name=periode_rawat_inap]').val();
  var periode_rawat_inap_akhir  = $('input:text[name=periode_rawat_inap_akhir]').val();
  var status_pulang = 'masuk';

  if(periode_rawat_inap == '') {
    alert('Tanggal awal masih kosong!')
  }
  if(periode_rawat_inap_akhir == '') {
    alert('Tanggal akhir masih kosong!')
  }

  $.post(url, {periode_rawat_inap: periode_rawat_inap, periode_rawat_inap_akhir: periode_rawat_inap_akhir, status_pulang: status_pulang} ,function(data) {
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

  event.stopPropagation();

});

$('#manage').on('click', '#pulang_periode_rawat_inap', function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/rawat_inap/display?t=' + mlite.token;
  var periode_rawat_inap  = $('input:text[name=periode_rawat_inap]').val();
  var periode_rawat_inap_akhir  = $('input:text[name=periode_rawat_inap_akhir]').val();
  var status_pulang = 'pulang';

  if(periode_rawat_inap == '') {
    alert('Tanggal awal masih kosong!')
  }
  if(periode_rawat_inap_akhir == '') {
    alert('Tanggal akhir masih kosong!')
  }

  $.post(url, {periode_rawat_inap: periode_rawat_inap, periode_rawat_inap_akhir: periode_rawat_inap_akhir, status_pulang: status_pulang} ,function(data) {
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

  event.stopPropagation();

});

$('#manage').on('click', '#lunas_periode_rawat_inap', function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/rawat_inap/display?t=' + mlite.token;
  var periode_rawat_inap  = $('input:text[name=periode_rawat_inap]').val();
  var periode_rawat_inap_akhir  = $('input:text[name=periode_rawat_inap_akhir]').val();
  var status_periksa = 'lunas';

  if(periode_rawat_inap == '') {
    alert('Tanggal awal masih kosong!')
  }
  if(periode_rawat_inap_akhir == '') {
    alert('Tanggal akhir masih kosong!')
  }

  $.post(url, {periode_rawat_inap: periode_rawat_inap, periode_rawat_inap_akhir: periode_rawat_inap_akhir, status_periksa: status_periksa} ,function(data) {
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

  event.stopPropagation();

});

// tombol batal diklik
$("#form_soap").on("click", "#selesai_soap", function(event){
  event.preventDefault();
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

// ketika baris data diklik
//$("#display").on("click", ".layanan_obat", function(event){

// ketika inputbox pencarian diisi
$('input:text[name=layanan]').on('input',function(e){
  var baseURL = mlite.url + '/' + mlite.admin;
  var url    = baseURL + '/rawat_inap/layanan?t=' + mlite.token;
  var layanan = $('input:text[name=layanan]').val();

  if(layanan!="") {
      $.post(url, {layanan: layanan} ,function(data) {
      // tampilkan data yang sudah di perbaharui
        $("#layanan").html(data).show();
        $("#obat").hide();
      });
  }

});
// end pencarian

// ketika inputbox pencarian diisi
$('input:text[name=obat]').on('input',function(e){
  var baseURL = mlite.url + '/' + mlite.admin;
  var url    = baseURL + '/rawat_inap/obat?t=' + mlite.token;
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
  $('#rawat_jl_dr').show();
});

// ketika tombol simpan diklik
$("#form_rincian").on("click", "#simpan_rincian", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();

  var no_rawat        = $('input:text[name=no_rawat]').val();
  var kd_jenis_prw 	  = $('input:hidden[name=kd_jenis_prw]').val();
  var provider        = $('select[name=provider]').val();
  var kode_provider   = $('input:text[name=kode_provider]').val();
  var kode_provider2   = $('input:text[name=kode_provider2]').val();
  var tgl_perawatan   = $('input:text[name=tgl_perawatan]').val();
  var jam_rawat       = $('input:text[name=jam_rawat]').val();
  var biaya           = $('input:text[name=biaya]').val();
  var aturan_pakai    = $('input:text[name=aturan_pakai]').val();
  var kat             = $('input:hidden[name=kat]').val();
  var jml             = $('input:text[name=jml]').val();

  var url = baseURL + '/rawat_inap/savedetail?t=' + mlite.token;
  $.post(url, {no_rawat : no_rawat,
  kd_jenis_prw   : kd_jenis_prw,
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
    var url = baseURL + '/rawat_inap/rincian?t=' + mlite.token;
    $.post(url, {no_rawat : no_rawat,
    }, function(data) {
      // tampilkan data
      $("#rincian").html(data).show();
    });
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
  var url = baseURL + '/rawat_inap/hapusdetail?t=' + mlite.token;
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
        var url = baseURL + '/rawat_inap/rincian?t=' + mlite.token;
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
  var url = baseURL + '/rawat_inap/hapusresep?t=' + mlite.token;
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
        var url = baseURL + '/rawat_inap/rincian?t=' + mlite.token;
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
$("#rincian").on("click",".hapus_resep_dokter", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/rawat_inap/hapusresep?t=' + mlite.token;
  var no_resep = $(this).attr("data-no_resep");
  var no_rawat = $(this).attr("data-no_rawat");
  var kd_jenis_prw = $(this).attr("data-kd_jenis_prw");

  // tampilkan dialog konfirmasi
  bootbox.confirm("Apakah Anda yakin ingin menghapus data ini?", function(result){
    // ketika ditekan tombol ok
    if (result){
      // mengirimkan perintah penghapusan
      $.post(url, {
        no_resep: no_resep,
        no_rawat: no_rawat,
        kd_jenis_prw: kd_jenis_prw
      } ,function(data) {
        var url = baseURL + '/rawat_inap/rincian?t=' + mlite.token;
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
