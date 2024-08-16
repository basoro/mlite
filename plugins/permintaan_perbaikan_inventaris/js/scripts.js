jQuery().ready(function () {
    var var_tbl_permintaan_perbaikan_inventaris = $('#tbl_permintaan_perbaikan_inventaris').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['permintaan_perbaikan_inventaris','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_permintaan_perbaikan_inventaris = $('#search_field_permintaan_perbaikan_inventaris').val();
                var search_text_permintaan_perbaikan_inventaris = $('#search_text_permintaan_perbaikan_inventaris').val();
                
                data.search_field_permintaan_perbaikan_inventaris = search_field_permintaan_perbaikan_inventaris;
                data.search_text_permintaan_perbaikan_inventaris = search_text_permintaan_perbaikan_inventaris;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_permintaan_perbaikan_inventaris').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_permintaan_perbaikan_inventaris tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'no_permintaan' },
{ 'data': 'no_inventaris' },
{ 'data': 'nik' },
{ 'data': 'tanggal' },
{ 'data': 'deskripsi_kerusakan' }

        ],
        "columnDefs": [
{ 'targets': 0},
{ 'targets': 1},
{ 'targets': 2},
{ 'targets': 3},
{ 'targets': 4}

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
        selector: '#tbl_permintaan_perbaikan_inventaris tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_permintaan_perbaikan_inventaris.rows({ selected: true }).data()[0];
          if (rowData != null) {
var no_permintaan = rowData['no_permintaan'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/permintaan_perbaikan_inventaris/detail/' + no_permintaan + '?t=' + mlite.token);
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

    $("form[name='form_permintaan_perbaikan_inventaris']").validate({
        rules: {
no_permintaan: 'required',
no_inventaris: 'required',
nik: 'required',
tanggal: 'required',
deskripsi_kerusakan: 'required'

        },
        messages: {
no_permintaan:'No Permintaan tidak boleh kosong!',
no_inventaris:'No Inventaris tidak boleh kosong!',
nik:'Nik tidak boleh kosong!',
tanggal:'Tanggal tidak boleh kosong!',
deskripsi_kerusakan:'Deskripsi Kerusakan tidak boleh kosong!'

        },
        submitHandler: function (form) {
var no_permintaan= $('#no_permintaan').val();
var no_inventaris= $('#no_inventaris').val();
var nik= $('#nik').val();
var tanggal= $('#tanggal').val();
var deskripsi_kerusakan= $('#deskripsi_kerusakan').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['permintaan_perbaikan_inventaris','aksi'])?}",
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
                            $("#modal_permintaan_perbaikan_inventaris").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_permintaan_perbaikan_inventaris").modal('hide');
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
                    var_tbl_permintaan_perbaikan_inventaris.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_permintaan_perbaikan_inventaris.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_permintaan_perbaikan_inventaris.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_permintaan_perbaikan_inventaris.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_permintaan_perbaikan_inventaris').click(function () {
        var_tbl_permintaan_perbaikan_inventaris.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_permintaan_perbaikan_inventaris").click(function () {
        var rowData = var_tbl_permintaan_perbaikan_inventaris.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var no_permintaan = rowData['no_permintaan'];
var no_inventaris = rowData['no_inventaris'];
var nik = rowData['nik'];
var tanggal = rowData['tanggal'];
var deskripsi_kerusakan = rowData['deskripsi_kerusakan'];

            $("#typeact").val("edit");
  
            $('#no_permintaan').val(no_permintaan);
$('#no_inventaris').val(no_inventaris);
$('#nik').val(nik);
$('#tanggal').val(tanggal);
$('#deskripsi_kerusakan').val(deskripsi_kerusakan);

            $("#no_permintaan").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Permintaan Perbaikan Inventaris");
            $("#modal_permintaan_perbaikan_inventaris").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_permintaan_perbaikan_inventaris").click(function () {
        var rowData = var_tbl_permintaan_perbaikan_inventaris.rows({ selected: true }).data()[0];


        if (rowData) {
var no_permintaan = rowData['no_permintaan'];
            bootbox.confirm('Anda yakin akan menghapus data dengan no_permintaan="' + no_permintaan, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['permintaan_perbaikan_inventaris','aksi'])?}",
                        method: "POST",
                        data: {
                            no_permintaan: no_permintaan,
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
                            var_tbl_permintaan_perbaikan_inventaris.draw();
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
    jQuery("#tambah_data_permintaan_perbaikan_inventaris").click(function () {

        $('#no_permintaan').val('');
$('#no_inventaris').val('');
$('#nik').val('');
$('#tanggal').val('');
$('#deskripsi_kerusakan').val('');

        $("#typeact").val("add");
        $("#no_permintaan").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Permintaan Perbaikan Inventaris");
        $("#modal_permintaan_perbaikan_inventaris").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_permintaan_perbaikan_inventaris").click(function () {

        var search_field_permintaan_perbaikan_inventaris = $('#search_field_permintaan_perbaikan_inventaris').val();
        var search_text_permintaan_perbaikan_inventaris = $('#search_text_permintaan_perbaikan_inventaris').val();

        $.ajax({
            url: "{?=url(['permintaan_perbaikan_inventaris','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_permintaan_perbaikan_inventaris: search_field_permintaan_perbaikan_inventaris, 
                search_text_permintaan_perbaikan_inventaris: search_text_permintaan_perbaikan_inventaris
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_permintaan_perbaikan_inventaris' class='table display dataTable' style='width:100%'><thead><th>No Permintaan</th><th>No Inventaris</th><th>Nik</th><th>Tanggal</th><th>Deskripsi Kerusakan</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['no_permintaan'] + '</td>';
eTable += '<td>' + res[i]['no_inventaris'] + '</td>';
eTable += '<td>' + res[i]['nik'] + '</td>';
eTable += '<td>' + res[i]['tanggal'] + '</td>';
eTable += '<td>' + res[i]['deskripsi_kerusakan'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_permintaan_perbaikan_inventaris').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_permintaan_perbaikan_inventaris").modal('show');
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
        doc.text("Tabel Data Permintaan Perbaikan Inventaris", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_permintaan_perbaikan_inventaris',
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
        // doc.save('table_data_permintaan_perbaikan_inventaris.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_permintaan_perbaikan_inventaris");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data permintaan_perbaikan_inventaris");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/permintaan_perbaikan_inventaris/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});