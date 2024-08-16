jQuery().ready(function () {
    var var_tbl_pemeliharaan_inventaris = $('#tbl_pemeliharaan_inventaris').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['pemeliharaan_inventaris','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_pemeliharaan_inventaris = $('#search_field_pemeliharaan_inventaris').val();
                var search_text_pemeliharaan_inventaris = $('#search_text_pemeliharaan_inventaris').val();
                
                data.search_field_pemeliharaan_inventaris = search_field_pemeliharaan_inventaris;
                data.search_text_pemeliharaan_inventaris = search_text_pemeliharaan_inventaris;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_pemeliharaan_inventaris').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_pemeliharaan_inventaris tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'no_inventaris' },
{ 'data': 'tanggal' },
{ 'data': 'uraian_kegiatan' },
{ 'data': 'nip' },
{ 'data': 'pelaksana' },
{ 'data': 'biaya' },
{ 'data': 'jenis_pemeliharaan' }

        ],
        "columnDefs": [
{ 'targets': 0},
{ 'targets': 1},
{ 'targets': 2},
{ 'targets': 3},
{ 'targets': 4},
{ 'targets': 5},
{ 'targets': 6}

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
        selector: '#tbl_pemeliharaan_inventaris tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_pemeliharaan_inventaris.rows({ selected: true }).data()[0];
          if (rowData != null) {
var no_inventaris = rowData['no_inventaris'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/pemeliharaan_inventaris/detail/' + no_inventaris + '?t=' + mlite.token);
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

    $("form[name='form_pemeliharaan_inventaris']").validate({
        rules: {
no_inventaris: 'required',
tanggal: 'required',
uraian_kegiatan: 'required',
nip: 'required',
pelaksana: 'required',
biaya: 'required',
jenis_pemeliharaan: 'required'

        },
        messages: {
no_inventaris:'No Inventaris tidak boleh kosong!',
tanggal:'Tanggal tidak boleh kosong!',
uraian_kegiatan:'Uraian Kegiatan tidak boleh kosong!',
nip:'Nip tidak boleh kosong!',
pelaksana:'Pelaksana tidak boleh kosong!',
biaya:'Biaya tidak boleh kosong!',
jenis_pemeliharaan:'Jenis Pemeliharaan tidak boleh kosong!'

        },
        submitHandler: function (form) {
var no_inventaris= $('#no_inventaris').val();
var tanggal= $('#tanggal').val();
var uraian_kegiatan= $('#uraian_kegiatan').val();
var nip= $('#nip').val();
var pelaksana= $('#pelaksana').val();
var biaya= $('#biaya').val();
var jenis_pemeliharaan= $('#jenis_pemeliharaan').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['pemeliharaan_inventaris','aksi'])?}",
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
                            $("#modal_pemeliharaan_inventaris").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_pemeliharaan_inventaris").modal('hide');
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
                    var_tbl_pemeliharaan_inventaris.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_pemeliharaan_inventaris.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_pemeliharaan_inventaris.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_pemeliharaan_inventaris.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_pemeliharaan_inventaris').click(function () {
        var_tbl_pemeliharaan_inventaris.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_pemeliharaan_inventaris").click(function () {
        var rowData = var_tbl_pemeliharaan_inventaris.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var no_inventaris = rowData['no_inventaris'];
var tanggal = rowData['tanggal'];
var uraian_kegiatan = rowData['uraian_kegiatan'];
var nip = rowData['nip'];
var pelaksana = rowData['pelaksana'];
var biaya = rowData['biaya'];
var jenis_pemeliharaan = rowData['jenis_pemeliharaan'];

            $("#typeact").val("edit");
  
            $('#no_inventaris').val(no_inventaris);
$('#tanggal').val(tanggal);
$('#uraian_kegiatan').val(uraian_kegiatan);
$('#nip').val(nip);
$('#pelaksana').val(pelaksana);
$('#biaya').val(biaya);
$('#jenis_pemeliharaan').val(jenis_pemeliharaan);

            $("#no_inventaris").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Pemeliharaan Inventaris");
            $("#modal_pemeliharaan_inventaris").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_pemeliharaan_inventaris").click(function () {
        var rowData = var_tbl_pemeliharaan_inventaris.rows({ selected: true }).data()[0];


        if (rowData) {
var no_inventaris = rowData['no_inventaris'];
            bootbox.confirm('Anda yakin akan menghapus data dengan no_inventaris="' + no_inventaris, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['pemeliharaan_inventaris','aksi'])?}",
                        method: "POST",
                        data: {
                            no_inventaris: no_inventaris,
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
                            var_tbl_pemeliharaan_inventaris.draw();
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
    jQuery("#tambah_data_pemeliharaan_inventaris").click(function () {

        $('#no_inventaris').val('');
$('#tanggal').val('');
$('#uraian_kegiatan').val('');
$('#nip').val('');
$('#pelaksana').val('');
$('#biaya').val('');
$('#jenis_pemeliharaan').val('');

        $("#typeact").val("add");
        $("#no_inventaris").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Pemeliharaan Inventaris");
        $("#modal_pemeliharaan_inventaris").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_pemeliharaan_inventaris").click(function () {

        var search_field_pemeliharaan_inventaris = $('#search_field_pemeliharaan_inventaris').val();
        var search_text_pemeliharaan_inventaris = $('#search_text_pemeliharaan_inventaris').val();

        $.ajax({
            url: "{?=url(['pemeliharaan_inventaris','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_pemeliharaan_inventaris: search_field_pemeliharaan_inventaris, 
                search_text_pemeliharaan_inventaris: search_text_pemeliharaan_inventaris
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_pemeliharaan_inventaris' class='table display dataTable' style='width:100%'><thead><th>No Inventaris</th><th>Tanggal</th><th>Uraian Kegiatan</th><th>Nip</th><th>Pelaksana</th><th>Biaya</th><th>Jenis Pemeliharaan</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['no_inventaris'] + '</td>';
eTable += '<td>' + res[i]['tanggal'] + '</td>';
eTable += '<td>' + res[i]['uraian_kegiatan'] + '</td>';
eTable += '<td>' + res[i]['nip'] + '</td>';
eTable += '<td>' + res[i]['pelaksana'] + '</td>';
eTable += '<td>' + res[i]['biaya'] + '</td>';
eTable += '<td>' + res[i]['jenis_pemeliharaan'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_pemeliharaan_inventaris').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_pemeliharaan_inventaris").modal('show');
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
        doc.text("Tabel Data Pemeliharaan Inventaris", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_pemeliharaan_inventaris',
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
        // doc.save('table_data_pemeliharaan_inventaris.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_pemeliharaan_inventaris");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data pemeliharaan_inventaris");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/pemeliharaan_inventaris/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});