// sembunyikan form dan notif
$("#form").hide();
$("#notif").hide();

// tombol buka form diklik
$("#index").on('click', '#bukaform', function(){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  $("#form").show().load(baseURL + '/cuti/form?t=' + mlite.token);
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
  bersih();
});

$("#form").on("click","#no_pengajuan", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/cuti/maxid?t=' + mlite.token;
  $.post(url, {
  } ,function(data) {
    $("#no_pengajuan").val(data);
  });
});

$("#form").on("click", "#simpan", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  var no_pengajuan = $('input:text[name=no_pengajuan]').val();
  var tgl_pengajuan = $('input:text[name=tgl_pengajuan]').val();
  var tgl_akhir = $('input:text[name=tgl_akhir]').val();
  var description = $('textarea[name=description]').val();
  var urgensi = $('select[name=urgensi]').val();
  var kd_pj = $('select[name=kd_pj]').val();
  var kepentingan = $('input:text[name=kepentingan]').val();

  var url = baseURL + '/cuti/save?t=' + mlite.token;

  if(no_pengajuan == '') {
    alert('Nomor rawat masih kosong!')
  }
  if(description == '') {
    alert('Alamat tidak boleh kosong!')
  }
  else if(tgl_pengajuan > tgl_akhir) {
    alert('Tanggal tidak boleh lebih besar!')
  }
  else if(kepentingan == '') {
    alert('Kepentingan masih kosong! Silahkan tulis.')
  } else {
    $.post(url,{
      no_pengajuan: no_pengajuan,
      tgl_pengajuan: tgl_pengajuan,
      tgl_akhir: tgl_akhir,
      description: description,
      urgensi: urgensi,
      kd_pj: kd_pj,
      kepentingan: kepentingan
    },function(dada) {
      $("#display").show().load(baseURL + '/cuti/display?t=' + mlite.token);
      bersih();
      if (dada != 'gagal'){
        $('#notif').html("<div class=\"alert alert-success alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
        "Data pengajuan telah disimpan!"+
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
        "</div>").show();
      } else {
        $('#notif').html("<div class=\"alert alert-danger alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
        "Gagal menyimpan data pengajuan!"+
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
        "</div>").show();
      }
    });
  }
  event.preventDefault();
});

$("#display").on("click", ".edit", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/cuti/form?t=' + mlite.token;
  var no_pengajuan = $(this).attr("data-no_pengajuan");
  $.post(url, {no_pengajuan: no_pengajuan} ,function(data) {
    // tampilkan data
    $("#form").html(data).show();
  });
});

// ketika tombol hapus ditekan
$("#form").on("click","#hapus", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/cuti/hapus?t=' + mlite.token;
  var no_pengajuan = $('input:text[name=no_pengajuan]').val();

  // tampilkan dialog konfirmasi
  bootbox.confirm("Apakah Anda yakin ingin menghapus data ini?", function(result){
    // ketika ditekan tombol ok
    if (result){
      // mengirimkan perintah penghapusan
      $.post(url, {
        no_pengajuan: no_pengajuan
      } ,function(dapa) {
        // sembunyikan form, tampilkan data yang sudah di perbaharui, tampilkan notif
        $("#display").load(baseURL + '/cuti/display?t=' + mlite.token);
        bersih();
        if (dapa != 'error'){
          $('#notif').html("<div class=\"alert alert-success alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
          "Data cuti telah dihapus!"+
          "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
          "</div>").show();
        } else {
          $('#notif').html("<div class=\"alert alert-danger alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
          "Gagal menghapus data pengajuan karena data telah di setujui!"+
          "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
          "</div>").show();
        }   
      });
    }
  });
});

function bersih(){
  $('input:text[name=no_pengajuan]').val("");
  $('input:text[name=description]').val("");
  $('input:text[name=kepentingan]').val("");
  $('textarea[name=description]').val("");
  $('input:text[name=tgl_pengajuan]').val("{?=date('Y-m-d')?}");
  $('input:text[name=tgl_akhir]').val("{?=date('Y-m-d')?}");
}

$('#manage').on('click', '#submit_periode_cuti', function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/cuti/display?t=' + mlite.token;
  var periode_cuti  = $('input:text[name=periode_cuti]').val();
  var periode_cuti_akhir  = $('input:text[name=periode_cuti_akhir]').val();

  if(periode_cuti == '') {
    alert('Tanggal awal masih kosong!')
  }
  if(periode_cuti_akhir == '') {
    alert('Tanggal akhir masih kosong!')
  }

  $.post(url, {periode_cuti: periode_cuti, periode_cuti_akhir: periode_cuti_akhir} ,function(data) {
  // tampilkan data
    $("#display").html(data).show();
    $("#notif").hide();
    $('.periode_cuti').datetimepicker('remove');
  });

  event.stopPropagation();

});

$('#manage').on('click', '#proses_periode_cuti', function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/cuti/display?t=' + mlite.token;
  var periode_cuti  = $('input:text[name=periode_cuti]').val();
  var periode_cuti_akhir  = $('input:text[name=periode_cuti_akhir]').val();
  var status_cuti = 'Proses Pengajuan';

  if(periode_cuti == '') {
    alert('Tanggal awal masih kosong!')
  }
  if(periode_cuti_akhir == '') {
    alert('Tanggal akhir masih kosong!')
  }

  $.post(url, {periode_cuti: periode_cuti, periode_cuti_akhir: periode_cuti_akhir, status_cuti: status_cuti} ,function(data) {
  // tampilkan data
  $("#display").html(data).show();
  $("#notif").hide();
  $('.periode_cuti').datetimepicker('remove');
  });

  event.stopPropagation();

});

$('#manage').on('click', '#disetujui_periode_cuti', function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/cuti/display?t=' + mlite.token;
  var periode_cuti  = $('input:text[name=periode_cuti]').val();
  var periode_cuti_akhir  = $('input:text[name=periode_cuti_akhir]').val();
  var status_cuti = 'Disetujui';

  if(periode_cuti == '') {
    alert('Tanggal awal masih kosong!')
  }
  if(periode_cuti_akhir == '') {
    alert('Tanggal akhir masih kosong!')
  }

  $.post(url, {periode_cuti: periode_cuti, periode_cuti_akhir: periode_cuti_akhir, status_cuti: status_cuti} ,function(data) {
  // tampilkan data
  $("#display").html(data).show();
  $("#notif").hide();
  $('.periode_cuti').datetimepicker('remove');
  });

  event.stopPropagation();

});

$('#manage').on('click', '#ditolak_periode_cuti', function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/cuti/display?t=' + mlite.token;
  var periode_cuti  = $('input:text[name=periode_cuti]').val();
  var periode_cuti_akhir  = $('input:text[name=periode_cuti_akhir]').val();
  var status_cuti = 'Ditolak';

  if(periode_cuti == '') {
    alert('Tanggal awal masih kosong!')
  }
  if(periode_cuti_akhir == '') {
    alert('Tanggal akhir masih kosong!')
  }

  $.post(url, {periode_cuti: periode_cuti, periode_cuti_akhir: periode_cuti_akhir, status_cuti: status_cuti} ,function(data) {
  // tampilkan data
  $("#display").html(data).show();
  $("#notif").hide();
  $('.periode_cuti').datetimepicker('remove');
  });

  event.stopPropagation();

});

$('#rekap_cuti').on('click', '#submit_periode', function(event){
    var baseURL = mlite.url + '/' + mlite.admin;
    event.preventDefault();
    var url    = baseURL + '/cuti/rekap_cuti?t=' + mlite.token;
    var periode  = $('input:text[name=periode]').val();
    var s  = $('input:text[name=s]').val();
  
    if(periode == '') {
      alert('Tahun masih kosong!')
    }
    var ss = decodeURI(s);
    window.location.href = baseURL+'/cuti/rekap_cuti?awal='+periode+'&s='+ss+'&t=' + mlite.token;
    
    event.stopPropagation();
  
  });
$('#rekap_cuti').on('click', '#cari', function(event){
    var baseURL = mlite.url + '/' + mlite.admin;
    event.preventDefault();
    var url    = baseURL + '/cuti/rekap_cuti?t=' + mlite.token;
    var s  = $('input:text[name=s]').val();
    window.location.href = baseURL+'/cuti/rekap_cuti?s='+s+'&t=' + mlite.token;
  
    event.stopPropagation();
  
  });

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
