jQuery().ready(function () {
    var var_tbl_utd_stok_darah = $('#tbl_utd_stok_darah').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['utd_stok_darah','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_utd_stok_darah = $('#search_field_utd_stok_darah').val();
                var search_text_utd_stok_darah = $('#search_text_utd_stok_darah').val();
                
                data.search_field_utd_stok_darah = search_field_utd_stok_darah;
                data.search_text_utd_stok_darah = search_text_utd_stok_darah;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_utd_stok_darah').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_utd_stok_darah tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'no_kantong' },
{ 'data': 'kode_komponen' },
{ 'data': 'golongan_darah' },
{ 'data': 'resus' },
{ 'data': 'tanggal_aftap' },
{ 'data': 'tanggal_kadaluarsa' },
{ 'data': 'asal_darah' },
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
        selector: '#tbl_utd_stok_darah tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_utd_stok_darah.rows({ selected: true }).data()[0];
          if (rowData != null) {
var no_kantong = rowData['no_kantong'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/utd_stok_darah/detail/' + no_kantong + '?t=' + mlite.token);
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

    $("form[name='form_utd_stok_darah']").validate({
        rules: {
no_kantong: 'required',
kode_komponen: 'required',
golongan_darah: 'required',
resus: 'required',
tanggal_aftap: 'required',
tanggal_kadaluarsa: 'required',
asal_darah: 'required',
status: 'required'

        },
        messages: {
no_kantong:'No Kantong tidak boleh kosong!',
kode_komponen:'Kode Komponen tidak boleh kosong!',
golongan_darah:'Golongan Darah tidak boleh kosong!',
resus:'Resus tidak boleh kosong!',
tanggal_aftap:'Tanggal Aftap tidak boleh kosong!',
tanggal_kadaluarsa:'Tanggal Kadaluarsa tidak boleh kosong!',
asal_darah:'Asal Darah tidak boleh kosong!',
status:'Status tidak boleh kosong!'

        },
        submitHandler: function (form) {
var no_kantong= $('#no_kantong').val();
var kode_komponen= $('#kode_komponen').val();
var golongan_darah= $('#golongan_darah').val();
var resus= $('#resus').val();
var tanggal_aftap= $('#tanggal_aftap').val();
var tanggal_kadaluarsa= $('#tanggal_kadaluarsa').val();
var asal_darah= $('#asal_darah').val();
var status= $('#status').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['utd_stok_darah','aksi'])?}",
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
                            $("#modal_utd_stok_darah").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_utd_stok_darah").modal('hide');
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
                    var_tbl_utd_stok_darah.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_utd_stok_darah.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_utd_stok_darah.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_utd_stok_darah.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_utd_stok_darah').click(function () {
        var_tbl_utd_stok_darah.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_utd_stok_darah").click(function () {
        var rowData = var_tbl_utd_stok_darah.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var no_kantong = rowData['no_kantong'];
var kode_komponen = rowData['kode_komponen'];
var golongan_darah = rowData['golongan_darah'];
var resus = rowData['resus'];
var tanggal_aftap = rowData['tanggal_aftap'];
var tanggal_kadaluarsa = rowData['tanggal_kadaluarsa'];
var asal_darah = rowData['asal_darah'];
var status = rowData['status'];

            $("#typeact").val("edit");
  
            $('#no_kantong').val(no_kantong);
$('#kode_komponen').val(kode_komponen);
$('#golongan_darah').val(golongan_darah);
$('#resus').val(resus);
$('#tanggal_aftap').val(tanggal_aftap);
$('#tanggal_kadaluarsa').val(tanggal_kadaluarsa);
$('#asal_darah').val(asal_darah);
$('#status').val(status);

            $("#no_kantong").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Utd Stok Darah");
            $("#modal_utd_stok_darah").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_utd_stok_darah").click(function () {
        var rowData = var_tbl_utd_stok_darah.rows({ selected: true }).data()[0];


        if (rowData) {
var no_kantong = rowData['no_kantong'];
            bootbox.confirm('Anda yakin akan menghapus data dengan no_kantong="' + no_kantong, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['utd_stok_darah','aksi'])?}",
                        method: "POST",
                        data: {
                            no_kantong: no_kantong,
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
                            var_tbl_utd_stok_darah.draw();
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
    jQuery("#tambah_data_utd_stok_darah").click(function () {

        $('#no_kantong').val('');
$('#kode_komponen').val('');
$('#golongan_darah').val('');
$('#resus').val('');
$('#tanggal_aftap').val('');
$('#tanggal_kadaluarsa').val('');
$('#asal_darah').val('');
$('#status').val('');

        $("#typeact").val("add");
        $("#no_kantong").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Utd Stok Darah");
        $("#modal_utd_stok_darah").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_utd_stok_darah").click(function () {

        var search_field_utd_stok_darah = $('#search_field_utd_stok_darah').val();
        var search_text_utd_stok_darah = $('#search_text_utd_stok_darah').val();

        $.ajax({
            url: "{?=url(['utd_stok_darah','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_utd_stok_darah: search_field_utd_stok_darah, 
                search_text_utd_stok_darah: search_text_utd_stok_darah
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_utd_stok_darah' class='table display dataTable' style='width:100%'><thead><th>No Kantong</th><th>Kode Komponen</th><th>Golongan Darah</th><th>Resus</th><th>Tanggal Aftap</th><th>Tanggal Kadaluarsa</th><th>Asal Darah</th><th>Status</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['no_kantong'] + '</td>';
eTable += '<td>' + res[i]['kode_komponen'] + '</td>';
eTable += '<td>' + res[i]['golongan_darah'] + '</td>';
eTable += '<td>' + res[i]['resus'] + '</td>';
eTable += '<td>' + res[i]['tanggal_aftap'] + '</td>';
eTable += '<td>' + res[i]['tanggal_kadaluarsa'] + '</td>';
eTable += '<td>' + res[i]['asal_darah'] + '</td>';
eTable += '<td>' + res[i]['status'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_utd_stok_darah').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_utd_stok_darah").modal('show');
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
        doc.text("Tabel Data Utd Stok Darah", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_utd_stok_darah',
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
        // doc.save('table_data_utd_stok_darah.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_utd_stok_darah");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data utd_stok_darah");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/utd_stok_darah/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});