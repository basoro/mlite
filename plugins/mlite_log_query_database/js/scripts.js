jQuery().ready(function () {
    var var_tbl_mlite_log_query_database = $('#tbl_mlite_log_query_database').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['mlite_log_query_database','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_mlite_log_query_database = $('#search_field_mlite_log_query_database').val();
                var search_text_mlite_log_query_database = $('#search_text_mlite_log_query_database').val();
                
                data.search_field_mlite_log_query_database = search_field_mlite_log_query_database;
                data.search_text_mlite_log_query_database = search_text_mlite_log_query_database;
                
            }
        },
        "columns": [
{ 'data': 'id' },
{ 'data': 'user' },
{ 'data': 'tanggal' },
{ 'data': 'endpoint' },
{ 'data': 'query' }

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
        selector: '#tbl_mlite_log_query_database tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_mlite_log_query_database.rows({ selected: true }).data()[0];
          if (rowData != null) {
var id = rowData['id'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/mlite_log_query_database/detail/' + id + '?t=' + mlite.token);
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

    $("form[name='form_mlite_log_query_database']").validate({
        rules: {
id: 'required',
user: 'required',
tanggal: 'required',
endpoint: 'required',
query: 'required'

        },
        messages: {
id:'Id tidak boleh kosong!',
user:'User tidak boleh kosong!',
tanggal:'Tanggal tidak boleh kosong!',
endpoint:'Endpoint tidak boleh kosong!',
query:'Query tidak boleh kosong!'

        },
        submitHandler: function (form) {
var id= $('#id').val();
var user= $('#user').val();
var tanggal= $('#tanggal').val();
var endpoint= $('#endpoint').val();
var query= $('#query').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['mlite_log_query_database','aksi'])?}",
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
                            $("#modal_mlite_log_query_database").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_mlite_log_query_database").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    var_tbl_mlite_log_query_database.draw();
                }
            })
        }
    });

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_mlite_log_query_database').click(function () {
        var_tbl_mlite_log_query_database.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_mlite_log_query_database").click(function () {
        var rowData = var_tbl_mlite_log_query_database.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var id = rowData['id'];
var user = rowData['user'];
var tanggal = rowData['tanggal'];
var endpoint = rowData['endpoint'];
var query = rowData['query'];

            $("#typeact").val("edit");
  
            $('#id').val(id);
$('#user').val(user);
$('#tanggal').val(tanggal);
$('#endpoint').val(endpoint);
$('#query').val(query);

            $("#id").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Mlite Log Query Database");
            $("#modal_mlite_log_query_database").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_mlite_log_query_database").click(function () {
        var rowData = var_tbl_mlite_log_query_database.rows({ selected: true }).data()[0];


        if (rowData) {
var id = rowData['id'];
            bootbox.confirm('Anda yakin akan menghapus data dengan id="' + id, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['mlite_log_query_database','aksi'])?}",
                        method: "POST",
                        data: {
                            id: id,
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
                            var_tbl_mlite_log_query_database.draw();
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
    jQuery("#tambah_data_mlite_log_query_database").click(function () {

        $('#id').val('');
$('#user').val('');
$('#tanggal').val('');
$('#endpoint').val('');
$('#query').val('');

        $("#typeact").val("add");
        $("#id").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Mlite Log Query Database");
        $("#modal_mlite_log_query_database").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_mlite_log_query_database").click(function () {

        var search_field_mlite_log_query_database = $('#search_field_mlite_log_query_database').val();
        var search_text_mlite_log_query_database = $('#search_text_mlite_log_query_database').val();

        $.ajax({
            url: "{?=url(['mlite_log_query_database','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_mlite_log_query_database: search_field_mlite_log_query_database, 
                search_text_mlite_log_query_database: search_text_mlite_log_query_database
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_mlite_log_query_database' class='table display dataTable' style='width:100%'><thead><th>Id</th><th>User</th><th>Tanggal</th><th>Endpoint</th><th>Query</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['id'] + '</td>';
eTable += '<td>' + res[i]['user'] + '</td>';
eTable += '<td>' + res[i]['tanggal'] + '</td>';
eTable += '<td>' + res[i]['endpoint'] + '</td>';
eTable += '<td>' + res[i]['query'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_mlite_log_query_database').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_mlite_log_query_database").modal('show');
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
        doc.text("Tabel Data Mlite Log Query Database", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_mlite_log_query_database',
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
        // doc.save('table_data_mlite_log_query_database.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_mlite_log_query_database");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data mlite_log_query_database");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/mlite_log_query_database/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});