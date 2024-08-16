jQuery().ready(function () {
    var var_tbl_perbaikan_inventaris = $('#tbl_perbaikan_inventaris').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['perbaikan_inventaris','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_perbaikan_inventaris = $('#search_field_perbaikan_inventaris').val();
                var search_text_perbaikan_inventaris = $('#search_text_perbaikan_inventaris').val();
                
                data.search_field_perbaikan_inventaris = search_field_perbaikan_inventaris;
                data.search_text_perbaikan_inventaris = search_text_perbaikan_inventaris;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_perbaikan_inventaris').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_perbaikan_inventaris tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'no_permintaan' },
{ 'data': 'tanggal' },
{ 'data': 'uraian_kegiatan' },
{ 'data': 'nip' },
{ 'data': 'pelaksana' },
{ 'data': 'biaya' },
{ 'data': 'keterangan' },
{ 'data': 'status' }

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
        selector: '#tbl_perbaikan_inventaris tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_perbaikan_inventaris.rows({ selected: true }).data()[0];
          if (rowData != null) {
var no_permintaan = rowData['no_permintaan'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/perbaikan_inventaris/detail/' + no_permintaan + '?t=' + mlite.token);
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

    $("form[name='form_perbaikan_inventaris']").validate({
        rules: {
no_permintaan: 'required',
tanggal: 'required',
uraian_kegiatan: 'required',
nip: 'required',
pelaksana: 'required',
biaya: 'required',
keterangan: 'required',
status: 'required'

        },
        messages: {
no_permintaan:'No Permintaan tidak boleh kosong!',
tanggal:'Tanggal tidak boleh kosong!',
uraian_kegiatan:'Uraian Kegiatan tidak boleh kosong!',
nip:'Nip tidak boleh kosong!',
pelaksana:'Pelaksana tidak boleh kosong!',
biaya:'Biaya tidak boleh kosong!',
keterangan:'Keterangan tidak boleh kosong!',
status:'Status tidak boleh kosong!'

        },
        submitHandler: function (form) {
var no_permintaan= $('#no_permintaan').val();
var tanggal= $('#tanggal').val();
var uraian_kegiatan= $('#uraian_kegiatan').val();
var nip= $('#nip').val();
var pelaksana= $('#pelaksana').val();
var biaya= $('#biaya').val();
var keterangan= $('#keterangan').val();
var status= $('#status').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['perbaikan_inventaris','aksi'])?}",
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
                            $("#modal_perbaikan_inventaris").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_perbaikan_inventaris").modal('hide');
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
                    var_tbl_perbaikan_inventaris.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_perbaikan_inventaris.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_perbaikan_inventaris.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_perbaikan_inventaris.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_perbaikan_inventaris').click(function () {
        var_tbl_perbaikan_inventaris.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_perbaikan_inventaris").click(function () {
        var rowData = var_tbl_perbaikan_inventaris.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var no_permintaan = rowData['no_permintaan'];
var tanggal = rowData['tanggal'];
var uraian_kegiatan = rowData['uraian_kegiatan'];
var nip = rowData['nip'];
var pelaksana = rowData['pelaksana'];
var biaya = rowData['biaya'];
var keterangan = rowData['keterangan'];
var status = rowData['status'];

            $("#typeact").val("edit");
  
            $('#no_permintaan').val(no_permintaan);
$('#tanggal').val(tanggal);
$('#uraian_kegiatan').val(uraian_kegiatan);
$('#nip').val(nip);
$('#pelaksana').val(pelaksana);
$('#biaya').val(biaya);
$('#keterangan').val(keterangan);
$('#status').val(status);

            $("#no_permintaan").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Perbaikan Inventaris");
            $("#modal_perbaikan_inventaris").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_perbaikan_inventaris").click(function () {
        var rowData = var_tbl_perbaikan_inventaris.rows({ selected: true }).data()[0];


        if (rowData) {
var no_permintaan = rowData['no_permintaan'];
            bootbox.confirm('Anda yakin akan menghapus data dengan no_permintaan="' + no_permintaan, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['perbaikan_inventaris','aksi'])?}",
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
                            var_tbl_perbaikan_inventaris.draw();
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
    jQuery("#tambah_data_perbaikan_inventaris").click(function () {

        $('#no_permintaan').val('');
$('#tanggal').val('');
$('#uraian_kegiatan').val('');
$('#nip').val('');
$('#pelaksana').val('');
$('#biaya').val('');
$('#keterangan').val('');
$('#status').val('');

        $("#typeact").val("add");
        $("#no_permintaan").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Perbaikan Inventaris");
        $("#modal_perbaikan_inventaris").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_perbaikan_inventaris").click(function () {

        var search_field_perbaikan_inventaris = $('#search_field_perbaikan_inventaris').val();
        var search_text_perbaikan_inventaris = $('#search_text_perbaikan_inventaris').val();

        $.ajax({
            url: "{?=url(['perbaikan_inventaris','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_perbaikan_inventaris: search_field_perbaikan_inventaris, 
                search_text_perbaikan_inventaris: search_text_perbaikan_inventaris
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_perbaikan_inventaris' class='table display dataTable' style='width:100%'><thead><th>No Permintaan</th><th>Tanggal</th><th>Uraian Kegiatan</th><th>Nip</th><th>Pelaksana</th><th>Biaya</th><th>Keterangan</th><th>Status</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['no_permintaan'] + '</td>';
eTable += '<td>' + res[i]['tanggal'] + '</td>';
eTable += '<td>' + res[i]['uraian_kegiatan'] + '</td>';
eTable += '<td>' + res[i]['nip'] + '</td>';
eTable += '<td>' + res[i]['pelaksana'] + '</td>';
eTable += '<td>' + res[i]['biaya'] + '</td>';
eTable += '<td>' + res[i]['keterangan'] + '</td>';
eTable += '<td>' + res[i]['status'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_perbaikan_inventaris').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_perbaikan_inventaris").modal('show');
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
        doc.text("Tabel Data Perbaikan Inventaris", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_perbaikan_inventaris',
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
        // doc.save('table_data_perbaikan_inventaris.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_perbaikan_inventaris");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data perbaikan_inventaris");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/perbaikan_inventaris/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});