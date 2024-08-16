jQuery().ready(function () {
    var var_tbl_laporan_operasi = $('#tbl_laporan_operasi').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['laporan_operasi','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_laporan_operasi = $('#search_field_laporan_operasi').val();
                var search_text_laporan_operasi = $('#search_text_laporan_operasi').val();
                
                data.search_field_laporan_operasi = search_field_laporan_operasi;
                data.search_text_laporan_operasi = search_text_laporan_operasi;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_laporan_operasi').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_laporan_operasi tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'no_rawat' },
{ 'data': 'tanggal' },
{ 'data': 'diagnosa_preop' },
{ 'data': 'diagnosa_postop' },
{ 'data': 'jaringan_dieksekusi' },
{ 'data': 'selesaioperasi' },
{ 'data': 'permintaan_pa' },
{ 'data': 'laporan_operasi' }

        ],
        "columnDefs": [
{ 'targets': 0},
{ 'targets': 1},
{ 'targets': 2},
{ 'targets': 3},
{ 'targets': 4},
{ 'targets': 5},
{ 'targets': 6},
{ 'targets': 7}

        ],
        order: [[1, 'DESC']], 
        buttons: [],
        "scrollCollapse": true,
        // "scrollY": '48vh', 
        // "pageLength":'25', 
        "lengthChange": true,
        "scrollX": true,
        dom: "<'row'<'col-sm-12'tr>><<'pmd-datatable-pagination' l i p>>"
    });


    $.contextMenu({
        selector: '#tbl_laporan_operasi tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_laporan_operasi.rows({ selected: true }).data()[0];
          if (rowData != null) {
var no_rawat = rowData['no_rawat'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/laporan_operasi/detail/' + no_rawat + '?t=' + mlite.token);
                break;
                default :
                break
            } 
          } else {
            bootbox.alert("Silakan pilih data atau klik baris data.");            
          }          
        },
        items: {
            "detail": {name: "View Detail", "icon": "edit", disabled:  {$disabled_menu.read}}
        }
    });

    // ==============================================================
    // FORM VALIDASI
    // ==============================================================

    $("form[name='form_laporan_operasi']").validate({
        rules: {
no_rawat: 'required',
tanggal: 'required',
diagnosa_preop: 'required',
diagnosa_postop: 'required',
jaringan_dieksekusi: 'required',
selesaioperasi: 'required',
permintaan_pa: 'required',
laporan_operasi: 'required'

        },
        messages: {
no_rawat:'No Rawat tidak boleh kosong!',
tanggal:'Tanggal tidak boleh kosong!',
diagnosa_preop:'Diagnosa Preop tidak boleh kosong!',
diagnosa_postop:'Diagnosa Postop tidak boleh kosong!',
jaringan_dieksekusi:'Jaringan Dieksekusi tidak boleh kosong!',
selesaioperasi:'Selesaioperasi tidak boleh kosong!',
permintaan_pa:'Permintaan Pa tidak boleh kosong!',
laporan_operasi:'Laporan Operasi tidak boleh kosong!'

        },
        submitHandler: function (form) {
var no_rawat= $('#no_rawat').val();
var tanggal= $('#tanggal').val();
var diagnosa_preop= $('#diagnosa_preop').val();
var diagnosa_postop= $('#diagnosa_postop').val();
var jaringan_dieksekusi= $('#jaringan_dieksekusi').val();
var selesaioperasi= $('#selesaioperasi').val();
var permintaan_pa= $('#permintaan_pa').val();
var laporan_operasi= $('#laporan_operasi').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['laporan_operasi','aksi'])?}",
                method: "POST",
                contentType: false, // tambahan
                processData: false, // tambahan
                data: formData,
                success: function (data) {
                    data = JSON.parse(data);
                    var audio = new Audio('{?=url()?}/assets/sound/' + data.status + '.mp3');
                    audio.play();
                    if (typeact == "add") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_laporan_operasi").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_laporan_operasi").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
                        let payload = {
                            'action' : typeact
                        }
                        ws.send(JSON.stringify(payload));
                    } 
                    var_tbl_laporan_operasi.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_laporan_operasi.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_laporan_operasi.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_laporan_operasi.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_laporan_operasi').click(function () {
        var_tbl_laporan_operasi.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_laporan_operasi").click(function () {
        var rowData = var_tbl_laporan_operasi.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var no_rawat = rowData['no_rawat'];
var tanggal = rowData['tanggal'];
var diagnosa_preop = rowData['diagnosa_preop'];
var diagnosa_postop = rowData['diagnosa_postop'];
var jaringan_dieksekusi = rowData['jaringan_dieksekusi'];
var selesaioperasi = rowData['selesaioperasi'];
var permintaan_pa = rowData['permintaan_pa'];
var laporan_operasi = rowData['laporan_operasi'];

            $("#typeact").val("edit");
  
            $('#no_rawat').val(no_rawat);
$('#tanggal').val(tanggal);
$('#diagnosa_preop').val(diagnosa_preop);
$('#diagnosa_postop').val(diagnosa_postop);
$('#jaringan_dieksekusi').val(jaringan_dieksekusi);
$('#selesaioperasi').val(selesaioperasi);
$('#permintaan_pa').val(permintaan_pa);
$('#laporan_operasi').val(laporan_operasi);

            $("#no_rawat").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Laporan Operasi");
            $("#modal_laporan_operasi").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_laporan_operasi").click(function () {
        var rowData = var_tbl_laporan_operasi.rows({ selected: true }).data()[0];


        if (rowData) {
var no_rawat = rowData['no_rawat'];
            bootbox.confirm('Anda yakin akan menghapus data dengan no_rawat="' + no_rawat, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['laporan_operasi','aksi'])?}",
                        method: "POST",
                        data: {
                            no_rawat: no_rawat,
                            typeact: 'del'
                        },
                        success: function (data) {
                            data = JSON.parse(data);
                            var audio = new Audio('{?=url()?}/assets/sound/' + data.status + '.mp3');
                            audio.play();
                            if(data.status === 'success') {
                                bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            } else {
                                bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                            } 
                            if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
                                let payload = {
                                    'action' : 'del'
                                }
                                ws.send(JSON.stringify(payload));
                            }
                            var_tbl_laporan_operasi.draw();
                        }
                    })    
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
    jQuery("#tambah_data_laporan_operasi").click(function () {

        $('#no_rawat').val('');
$('#tanggal').val('');
$('#diagnosa_preop').val('');
$('#diagnosa_postop').val('');
$('#jaringan_dieksekusi').val('');
$('#selesaioperasi').val('');
$('#permintaan_pa').val('');
$('#laporan_operasi').val('');

        $("#typeact").val("add");
        $("#no_rawat").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Laporan Operasi");
        $("#modal_laporan_operasi").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_laporan_operasi").click(function () {

        var search_field_laporan_operasi = $('#search_field_laporan_operasi').val();
        var search_text_laporan_operasi = $('#search_text_laporan_operasi').val();

        $.ajax({
            url: "{?=url(['laporan_operasi','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_laporan_operasi: search_field_laporan_operasi, 
                search_text_laporan_operasi: search_text_laporan_operasi
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_laporan_operasi' class='table display dataTable' style='width:100%'><thead><th>No Rawat</th><th>Tanggal</th><th>Diagnosa Preop</th><th>Diagnosa Postop</th><th>Jaringan Dieksekusi</th><th>Selesaioperasi</th><th>Permintaan Pa</th><th>Laporan Operasi</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['no_rawat'] + '</td>';
eTable += '<td>' + res[i]['tanggal'] + '</td>';
eTable += '<td>' + res[i]['diagnosa_preop'] + '</td>';
eTable += '<td>' + res[i]['diagnosa_postop'] + '</td>';
eTable += '<td>' + res[i]['jaringan_dieksekusi'] + '</td>';
eTable += '<td>' + res[i]['selesaioperasi'] + '</td>';
eTable += '<td>' + res[i]['permintaan_pa'] + '</td>';
eTable += '<td>' + res[i]['laporan_operasi'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_laporan_operasi').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_laporan_operasi").modal('show');
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
        doc.text("Tabel Data Laporan Operasi", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_laporan_operasi',
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
                doc.text(`© ${new Date().getFullYear()} {$settings.nama_instansi}.`, data.settings.margin.left, doc.internal.pageSize.height - 10);                
                doc.text(footerStr, data.settings.margin.left + 480, doc.internal.pageSize.height - 10);
           }
        });
        if (typeof doc.putTotalPages === 'function') {
            doc.putTotalPages(totalPagesExp);
        }
        // doc.save('table_data_laporan_operasi.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_laporan_operasi");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data laporan_operasi");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/laporan_operasi/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});