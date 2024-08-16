jQuery().ready(function () {
    var var_tbl_industrifarmasi = $('#tbl_industrifarmasi').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['industrifarmasi','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_industrifarmasi = $('#search_field_industrifarmasi').val();
                var search_text_industrifarmasi = $('#search_text_industrifarmasi').val();
                
                data.search_field_industrifarmasi = search_field_industrifarmasi;
                data.search_text_industrifarmasi = search_text_industrifarmasi;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_industrifarmasi').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_industrifarmasi tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'kode_industri' },
{ 'data': 'nama_industri' },
{ 'data': 'alamat' },
{ 'data': 'kota' },
{ 'data': 'no_telp' }

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
        selector: '#tbl_industrifarmasi tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_industrifarmasi.rows({ selected: true }).data()[0];
          if (rowData != null) {
var kode_industri = rowData['kode_industri'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/industrifarmasi/detail/' + kode_industri + '?t=' + mlite.token);
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

    $("form[name='form_industrifarmasi']").validate({
        rules: {
kode_industri: 'required',
nama_industri: 'required',
alamat: 'required',
kota: 'required',
no_telp: 'required'

        },
        messages: {
kode_industri:'Kode Industri tidak boleh kosong!',
nama_industri:'Nama Industri tidak boleh kosong!',
alamat:'Alamat tidak boleh kosong!',
kota:'Kota tidak boleh kosong!',
no_telp:'No Telp tidak boleh kosong!'

        },
        submitHandler: function (form) {
var kode_industri= $('#kode_industri').val();
var nama_industri= $('#nama_industri').val();
var alamat= $('#alamat').val();
var kota= $('#kota').val();
var no_telp= $('#no_telp').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['industrifarmasi','aksi'])?}",
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
                            $("#modal_industrifarmasi").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_industrifarmasi").modal('hide');
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
                    var_tbl_industrifarmasi.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_industrifarmasi.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_industrifarmasi.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_industrifarmasi.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_industrifarmasi').click(function () {
        var_tbl_industrifarmasi.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_industrifarmasi").click(function () {
        var rowData = var_tbl_industrifarmasi.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var kode_industri = rowData['kode_industri'];
var nama_industri = rowData['nama_industri'];
var alamat = rowData['alamat'];
var kota = rowData['kota'];
var no_telp = rowData['no_telp'];

            $("#typeact").val("edit");
  
            $('#kode_industri').val(kode_industri);
$('#nama_industri').val(nama_industri);
$('#alamat').val(alamat);
$('#kota').val(kota);
$('#no_telp').val(no_telp);

            $("#kode_industri").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Industrifarmasi");
            $("#modal_industrifarmasi").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_industrifarmasi").click(function () {
        var rowData = var_tbl_industrifarmasi.rows({ selected: true }).data()[0];


        if (rowData) {
var kode_industri = rowData['kode_industri'];
            bootbox.confirm('Anda yakin akan menghapus data dengan kode_industri="' + kode_industri, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['industrifarmasi','aksi'])?}",
                        method: "POST",
                        data: {
                            kode_industri: kode_industri,
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
                            var_tbl_industrifarmasi.draw();
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
    jQuery("#tambah_data_industrifarmasi").click(function () {

        $('#kode_industri').val('');
$('#nama_industri').val('');
$('#alamat').val('');
$('#kota').val('');
$('#no_telp').val('');

        $("#typeact").val("add");
        $("#kode_industri").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Industrifarmasi");
        $("#modal_industrifarmasi").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_industrifarmasi").click(function () {

        var search_field_industrifarmasi = $('#search_field_industrifarmasi').val();
        var search_text_industrifarmasi = $('#search_text_industrifarmasi').val();

        $.ajax({
            url: "{?=url(['industrifarmasi','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_industrifarmasi: search_field_industrifarmasi, 
                search_text_industrifarmasi: search_text_industrifarmasi
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_industrifarmasi' class='table display dataTable' style='width:100%'><thead><th>Kode Industri</th><th>Nama Industri</th><th>Alamat</th><th>Kota</th><th>No Telp</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['kode_industri'] + '</td>';
eTable += '<td>' + res[i]['nama_industri'] + '</td>';
eTable += '<td>' + res[i]['alamat'] + '</td>';
eTable += '<td>' + res[i]['kota'] + '</td>';
eTable += '<td>' + res[i]['no_telp'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_industrifarmasi').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_industrifarmasi").modal('show');
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
        doc.text("Tabel Data Industrifarmasi", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_industrifarmasi',
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
        // doc.save('table_data_industrifarmasi.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_industrifarmasi");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data industrifarmasi");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/industrifarmasi/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});