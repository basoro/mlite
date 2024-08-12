jQuery().ready(function () {
    var var_tbl_mlite_settings = $('#tbl_mlite_settings').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['mlite_settings','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_mlite_settings = $('#search_field_mlite_settings').val();
                var search_text_mlite_settings = $('#search_text_mlite_settings').val();
                
                data.search_field_mlite_settings = search_field_mlite_settings;
                data.search_text_mlite_settings = search_text_mlite_settings;
                
            }
        },
        "columns": [
{ 'data': 'id' },
{ 'data': 'module' },
{ 'data': 'field' },
{ 'data': 'value' }

        ],
        "columnDefs": [
{ 'targets': 0},
{ 'targets': 1},
{ 'targets': 2},
{ 'targets': 3}

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
        selector: '#tbl_mlite_settings tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_mlite_settings.rows({ selected: true }).data()[0];
          if (rowData != null) {
var id = rowData['id'];
            switch (key) {
                case 'detail' :
                OpenModal(mlite.url + '/mlite_settings/detail/' + id + '?t=' + mlite.token);
                break;
                default :
                break
            } 
          } else {
            bootbox.alert("Silakan pilih data atau klik baris data.");            
          }          
        },
        items: {
            "detail": {name: "View Detail", "icon": "edit", disabled:  {$disabled_menu.read}},
            // "sep1": "---------",
            // "fold1": {
            //     "name": "Sub group", 
            //     "items": {
            //         "fold1-key1": {"name": "Foo bar"},
            //         "fold2": {
            //             "name": "Sub group 2", 
            //             "items": {
            //                 "fold2-key1": {"name": "alpha"},
            //                 "fold2-key2": {"name": "bravo"}
            //             }
            //         }
            //     }
            // }
        }
    });

    // ==============================================================
    // FORM VALIDASI
    // ==============================================================

    $("form[name='form_mlite_settings']").validate({
        rules: {
id: 'required',
module: 'required',
field: 'required',
value: 'required'

        },
        messages: {
id:'Id tidak boleh kosong!',
module:'Module tidak boleh kosong!',
field:'Field tidak boleh kosong!',
value:'Value tidak boleh kosong!'

        },
        submitHandler: function (form) {
 var id= $('#id').val();
var module= $('#module').val();
var field= $('#field').val();
var value= $('#value').val();

 var typeact = $('#typeact').val();

 var formData = new FormData(form); // tambahan
 formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['mlite_settings','aksi'])?}",
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
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    $("#modal_mlite_settings").modal('hide');
                    var_tbl_mlite_settings.draw();
                }
            })
        }
    });

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_mlite_settings').click(function () {
        var_tbl_mlite_settings.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_mlite_settings").click(function () {
        var rowData = var_tbl_mlite_settings.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var id = rowData['id'];
var module = rowData['module'];
var field = rowData['field'];
var value = rowData['value'];



            $("#typeact").val("edit");
  
            $('#id').val(id);
$('#module').val(module);
$('#field').val(field);
$('#value').val(value);

            //$("#id").prop('disabled', true); // GA BISA DIEDIT KALI DISABLE
            $('#modal-title').text("Edit Data Mlite Settings");
            $("#modal_mlite_settings").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_mlite_settings").click(function () {
        var rowData = var_tbl_mlite_settings.rows({ selected: true }).data()[0];


        if (rowData) {
var id = rowData['id'];
            bootbox.confirm('Anda yakin akan menghapus data dengan id="' + id, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['mlite_settings','aksi'])?}",
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
                            var_tbl_mlite_settings.draw();
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
    jQuery("#tambah_data_mlite_settings").click(function () {

        $('#id').val('');
$('#module').val('');
$('#field').val('');
$('#value').val('');


        $("#typeact").val("add");
        $("#id").prop('disabled', false);
        
        $('#modal-title').text("Tambah Data Mlite Settings");
        $("#modal_mlite_settings").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_mlite_settings").click(function () {

        var search_field_mlite_settings = $('#search_field_mlite_settings').val();
        var search_text_mlite_settings = $('#search_text_mlite_settings').val();

        $.ajax({
            url: "{?=url(['mlite_settings','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_mlite_settings: search_field_mlite_settings, 
                search_text_mlite_settings: search_text_mlite_settings
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_mlite_settings' class='table display dataTable' style='width:100%'><thead><th>Id</th><th>Module</th><th>Field</th><th>Value</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['id'] + '</td>';
eTable += '<td>' + res[i]['module'] + '</td>';
eTable += '<td>' + res[i]['field'] + '</td>';
eTable += '<td>' + res[i]['value'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_mlite_settings').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_mlite_settings").modal('show');
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
        doc.text("Tabel Data Mlite Settings", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_mlite_settings',
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
        // doc.save('table_data_mlite_settings.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    });

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_mlite_settings");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data mlite_settings");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    });

    // Avatar
    var reader  = new FileReader();
    reader.addEventListener("load", function() {
        $("#logoPreview").attr('src', reader.result);
    }, false);
    $("input[name=logo]").change(function() {
        reader.readAsDataURL(this.files[0]);
    });

});