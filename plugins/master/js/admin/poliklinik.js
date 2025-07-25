$("#notif").hide();
// tombol tambah diklik
$("#index").on('click', '#bukaform', function(){
  var baseURL = mlite.url + '/' + mlite.admin;
  $("#form").show().load(baseURL + '/master/poliklinikform?t=' + mlite.token);
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
  var kd_poli = $('input:text[name=kd_poli]').val();
  var nm_poli = $('input:text[name=nm_poli]').val();
  var registrasi = $('input:text[name=registrasi]').val();
  var registrasilama = $('input:text[name=registrasilama]').val();
  var status = $('select[name=status]').val();

  var url = baseURL + '/master/polikliniksave?t=' + mlite.token;

  $.post(url,{
    kd_poli: kd_poli,
    nm_poli: nm_poli,
    registrasi: registrasi,
    registrasilama: registrasilama,
    status: status
  } ,function(data) {
      $("#display").show().load(baseURL + '/master/poliklinikdisplay?t=' + mlite.token);
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
  var url    = baseURL + '/master/poliklinikform?t=' + mlite.token;
  var kd_poli  = $(this).attr("data-kd_poli");

  $.post(url, {kd_poli: kd_poli} ,function(data) {
    console.log(data);
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
  var url = baseURL + '/master/poliklinikhapus?t=' + mlite.token;
  var kd_poli = $('input:text[name=kd_poli]').val();

  // tampilkan dialog konfirmasi
  bootbox.confirm("Apakah Anda yakin ingin menghapus data ini?", function(result){
    // ketika ditekan tombol ok
    if (result){
      // mengirimkan perintah penghapusan
      $.post(url, {
        kd_poli: kd_poli
      } ,function(data) {
        // sembunyikan form, tampilkan data yang sudah di perbaharui, tampilkan notif
        $("#form").hide();
        $("#tutupform").val("Buka Form");
        $("#tutupform").attr("id", "bukaform");
        $("#display").load(baseURL + '/master/poliklinikdisplay?t=' + mlite.token);
        $('#notif').html("<div class=\"alert alert-danger alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
        "Data poliklinik telah dihapus!"+
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
        "</div>").show();
      });
    }
  });
});

// ketika inputbox pencarian diisi
$('input:text[name=cari]').on('input',function(e){
  var baseURL = mlite.url + '/' + mlite.admin;
  var url    = baseURL + '/master/poliklinikdisplay?t=' + mlite.token;
  var cari = $('input:text[name=cari]').val();

  if(cari!="") {
      $.post(url, {cari: cari} ,function(data) {
      // tampilkan data yang sudah di perbaharui
        $("#display").html(data).show();
      });
  } else {
      $("#display").load(baseURL + '/master/poliklinikdisplay?t=' + mlite.token);
  }

});
// end pencarian

// ketika tombol halaman ditekan
$("#display").on("click", ".halaman",function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/master/poliklinikdisplay?t=' + mlite.token;
  kd_hal  = $(this).attr("data-hal");

  $.post(url, {halaman: kd_hal} ,function(data) {
  // tampilkan data
    $("#display").html(data).show();
  });

});
// end halaman

function bersih(){
  $('input:text[name=kd_poli]').val("").removeAttr('disabled');
  $('input:text[name=nm_poli]').val("");
  $('input:text[name=registrasi]').val("");
  $('input:text[name=registrasilama]').val("");
  $('select[name=status]').val("");
}

jQuery().ready(function () {
  var var_tbl_poliklinik = $('#tbl_poliklinik').DataTable({
      'processing': true,
      'serverSide': true,
      'serverMethod': 'post',
      'dom': 'Bfrtip',
      'searching': false,
      'select': true,
      'colReorder': true,
      "bInfo" : false,
      "ajax": {
          "url": "{?=url([ADMIN,'master','poliklinikdata'])?}",
          "dataType": "json",
          "type": "POST",
          "data": function (data) {

              // Read values
              var search_field_poliklinik = $('#search_field_poliklinik').val();
              var search_text_poliklinik = $('#search_text_poliklinik').val();
              
              // Normalisasi untuk field status
              if (search_field_poliklinik === 'status') {
                  if (search_text_poliklinik.toLowerCase() === 'aktif') {
                      search_text_poliklinik = '1';
                  } else if (search_text_poliklinik.toLowerCase() === 'tidak aktif') {
                      search_text_poliklinik = '0';
                  }
              }

              data.search_field_poliklinik = search_field_poliklinik;
              data.search_text_poliklinik = search_text_poliklinik;
              
          }
      },
      "columns": [
          { 'data': 'kd_poli' },
          { 'data': 'nm_poli' },
          { 'data': 'registrasi' },
          { 'data': 'registrasilama' },
          { 'data': 'status' }
      ],
      "columnDefs": [
          { 'targets': 0},
          { 'targets': 1},
          { 'targets': 2},
          { 'targets': 3},
          {
              'targets': 4,
              'render': function (data, type, row, meta) {
                  return data == 1 ? 'Aktif' : 'Tidak Aktif';
              }
          }
      ],
      buttons: [],
      "scrollCollapse": true,
      // "scrollY": '48vh', 
      "pageLength":'25', 
      "lengthChange": true,
      "scrollX": true,
      dom: "<'row'<'col-sm-12'tr>><<'pmd-datatable-pagination' l i p>>"
  });

  // ==============================================================
  // FORM VALIDASI
  // ==============================================================

  $("form[name='form_poliklinik']").validate({
      rules: {
          kd_poli: 'required',
          nm_poli: 'required',
          registrasi: 'required',
          registrasilama: 'required',
          status: 'required'
      },
      messages: {
          kd_poli:'kd_poli tidak boleh kosong!',
          nm_poli:'nm_poli tidak boleh kosong!',
          registrasi:'registrasi tidak boleh kosong!',
          registrasilama:'registrasilama tidak boleh kosong!',
          status:'status tidak boleh kosong!'
      },
      submitHandler: function (form) {
          var kd_poli= $('#kd_poli').val();
          var nm_poli= $('#nm_poli').val();
          var registrasi= $('#registrasi').val();
          var registrasilama= $('#registrasilama').val();
          var status= $('#status').val();

          var typeact = $('#typeact').val();

          var formData = new FormData(form); // tambahan
          formData.append('typeact', typeact); // tambahan

          $.ajax({
              url: "{?=url([ADMIN,'master','poliklinikaksi'])?}",
              method: "POST",
              contentType: false, // tambahan
              processData: false, // tambahan
              data: formData,
              success: function (data) {
                  try {
                      data = JSON.parse(data);
                      var audio = new Audio('{?=url()?}/assets/sound/' + data.status + '.mp3');
                      audio.play();    
                      if (data.status === "success") {
                          bootbox.alert(data.message);
                          $("#modal_poliklinik").modal('hide');
                          var_tbl_poliklinik.draw();
                      } else {
                          bootbox.alert("Gagal: " + data.message);
                      }
                  } catch (e) {
                      bootbox.alert("Terjadi kesalahan saat memproses respons server.");
                  }
              }
          })
      }
  });

  // ==============================================================
  // CLICK ICON SEARCH DI INPUT SEARCH
  // ==============================================================
  $("#search_poliklinik").click(function () {
      var_tbl_poliklinik.draw();
  });

  // ===========================================
  // Ketika tombol Edit di tekan
  // ===========================================

  $("#edit_data_poliklinik").click(function () {
      var rowData = var_tbl_poliklinik.rows({ selected: true }).data()[0];
      if (rowData != null) {

          var kd_poli = rowData['kd_poli'];
          var nm_poli = rowData['nm_poli'];
          var registrasi = rowData['registrasi'];
          var registrasilama = rowData['registrasilama'];
          var status = rowData['status'];

          $("#typeact").val("edit");

          $('#kd_poli').val(kd_poli);
          $('#nm_poli').val(nm_poli);
          $('#registrasi').val(registrasi);
          $('#registrasilama').val(registrasilama);
          $('#status').val(status).change();

          $("#kd_poli").prop('readonly', true); // GA BISA DIEDIT KALI DISABLE
          $('#modal-title').text("Edit Data Poliklinik");
          $("#modal_poliklinik").modal();
      }
      else {
          alert("Silakan pilih data yang akan di edit.");
      }

  });

  // ==============================================================
  // TOMBOL  DELETE DI CLICK
  // ==============================================================
  jQuery("#hapus_data_poliklinik").click(function () {
      var rowData = var_tbl_poliklinik.rows({ selected: true }).data()[0];

      if (rowData) {
          var kd_poli = rowData['kd_poli'];
          bootbox.confirm("Anda yakin akan menghapus data dengan kd_poli = " + kd_poli + "?", function(result) {
              if (result) {
                  $.ajax({
                      url: "{?=url([ADMIN,'master','poliklinikaksi'])?}",
                      method: "POST",
                      data: {
                          kd_poli: kd_poli,
                          typeact: 'del'
                      },
                      success: function (data) {
                          try {
                              data = JSON.parse(data);
                              var audio = new Audio('{?=url()?}/assets/sound/' + data.status + '.mp3');
                              audio.play();    
                              bootbox.alert(data.message);
                              if(data.status === 'success') {
                                  var_tbl_poliklinik.draw();
                              }
                          } catch (e) {
                              bootbox.alert("Terjadi kesalahan saat menghapus.");
                          }
                      },
                      error: function () {
                          bootbox.alert("Gagal terhubung ke server.");
                      }
                  });
              }
          });
      }
      else {
          bootbox.alert("Pilih satu baris untuk dihapus");
      }
  });

  // ==============================================================
  // TOMBOL TAMBAH DATA DI CLICK
  // ==============================================================

  jQuery("#tambah_data_poliklinik").click(function () {

      $('#kd_poli').val('');
      $('#nm_poli').val('');
      $('#registrasi').val('');
      $('#registrasilama').val('');
      $('#status').val('');

      $("#typeact").val("add");
      $("#kd_poli").prop('disabled', false);
      
      $('#modal-title').text("Tambah Data Poliklinik");
      $("#modal_poliklinik").modal();
  });

  // ===========================================
  // Ketika tombol lihat data di tekan
  // ===========================================
  $("#lihat_data_poliklinik").click(function () {

      var search_field_poliklinik = $('#search_field_poliklinik').val();
      var search_text_poliklinik = $('#search_text_poliklinik').val();

      // Normalisasi untuk field status
      if (search_field_poliklinik === 'status') {
          if (search_text_poliklinik.toLowerCase() === 'aktif') {
              search_text_poliklinik = '1';
          } else if (search_text_poliklinik.toLowerCase() === 'tidak aktif') {
              search_text_poliklinik = '0';
          }
      }

      $.ajax({
          url: "{?=url([ADMIN,'master','poliklinikaksi'])?}",
          method: "POST",
          data: {
              typeact: 'lihat', 
              search_field_poliklinik: search_field_poliklinik, 
              search_text_poliklinik: search_text_poliklinik
              
          },
          dataType: 'json',
          success: function (res) {
              var eTable = "<div class='table-responsive'><table id='tbl_lihat_poliklinik' class='table display dataTable' style='width:100%'><thead><th>Kd Poli</th><th>Nm Poli</th><th>Registrasi</th><th>Registrasilama</th><th>Status</th></thead>";
              for (var i = 0; i < res.length; i++) {
                  eTable += "<tr>";
                  eTable += '<td>' + res[i]['kd_poli'] + '</td>';
                  eTable += '<td>' + res[i]['nm_poli'] + '</td>';
                  eTable += '<td>' + res[i]['registrasi'] + '</td>';
                  eTable += '<td>' + res[i]['registrasilama'] + '</td>';
                  eTable += '<td>' + res[i]['status'] + '</td>';
                  eTable += "</tr>";
              }
              eTable += "</tbody></table></div>";
              $('#forTable_poliklinik').html(eTable);
          }
      });

      $('#modal-title').text("Lihat Data");
      $("#modal_lihat_poliklinik").modal();
  });

  // ==============================================================
  // TOMBOL DETAIL poliklinik DI CLICK
  // ==============================================================
  jQuery("#lihat_detail_poliklinik").click(function (event) {

      var rowData = var_tbl_poliklinik.rows({ selected: true }).data()[0];

      if (rowData) {
          var kd_poli = rowData['kd_poli'];
          var baseURL = mlite.url + '/' + mlite.admin;
          event.preventDefault();
          var loadURL =  baseURL + '/master/poliklinikdetail/' + kd_poli + '?t=' + mlite.token;
      
          var modal = $('#modal_detail_poliklinik');
          var modalContent = $('#modal_detail_poliklinik .modal-content');
      
          modal.off('show.bs.modal');
          modal.on('show.bs.modal', function () {
              modalContent.load(loadURL);
          }).modal();
          return false;
      
      }
      else {
          bootbox.alert("Pilih satu baris untuk detail");
      }
  });
      
  // ===========================================
  // Ketika tombol export pdf di tekan
  // ===========================================
  $("#export_pdf").click(function () {

      var doc = new jsPDF('p', 'pt', 'A4'); /* pilih 'l' atau 'p' */
      var img = "{?=base64_encode(file_get_contents(url($settings['logo'])))?}";
      doc.addImage(img, 'JPEG', 20, 10, 50, 50);
      doc.setFontSize(20);
      doc.text("{$settings.nama_instansi}", 80, 35, null, null, null);
      doc.setFontSize(10);
      doc.text("{$settings.alamat} - {$settings.kota} - {$settings.propinsi}", 80, 46, null, null, null);
      doc.text("Telepon: {$settings.nomor_telepon} - Email: {$settings.email}", 80, 56, null, null, null);
      doc.line(20,70,572,70,null); /* doc.line(20,70,820,70,null); --> Jika landscape */
      doc.line(20,72,572,72,null); /* doc.line(20,72,820,72,null); --> Jika landscape */
      doc.setFontSize(14);
      doc.text("Tabel Data Poliklinik", 20, 95, null, null, null);
      const totalPagesExp = "{total_pages_count_string}";        
      doc.autoTable({
          html: '#tbl_lihat_poliklinik',
          startY: 105,
          margin: {
              left: 20, 
              right: 20
          }, 
          styles: {
              fontSize: 10,
              cellPadding: 5
          }, 
          didDrawPage: data => {
              let footerStr = "Page " + doc.internal.getNumberOfPages();
              if (typeof doc.putTotalPages === 'function') {
              footerStr = footerStr + " of " + totalPagesExp;
              }
              doc.setFontSize(10);
              doc.text(footerStr, data.settings.margin.left, doc.internal.pageSize.height - 10);
         }
      });
      if (typeof doc.putTotalPages === 'function') {
          doc.putTotalPages(totalPagesExp);
      }
      // doc.save('table_data_poliklinik.pdf')
      window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
            
  })

  // ===========================================
  // Ketika tombol export xlsx di tekan
  // ===========================================
  $("#export_xlsx").click(function () {
      let tbl1 = document.getElementById("tbl_lihat_poliklinik");
      let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
      let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
      let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
      const new_workbook = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data poliklinik");
      XLSX.writeFile(new_workbook, 'tmp_file.xls');
  })

  // ===========================================
  // Ketika tombol chart di tekan
  // ===========================================
  $("#view_chart").click(function () {
      var baseURL = mlite.url + '/' + mlite.admin;
      window.open(baseURL + '/master/poliklinikchart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
  })   

});