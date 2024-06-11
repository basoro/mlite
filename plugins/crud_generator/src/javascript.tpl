jQuery().ready(function () {
    var var_tbl_NAMA_TABLE = $('#tbl_NAMA_TABLE').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url([ADMIN,'MODULE_NAME','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_NAMA_TABLE = $('#search_field_NAMA_TABLE').val();
                var search_text_NAMA_TABLE = $('#search_text_NAMA_TABLE').val();
                
                data.search_field_NAMA_TABLE = search_field_NAMA_TABLE;
                data.search_text_NAMA_TABLE = search_text_NAMA_TABLE;
                
            }
        },
        "columns": [
COLUMNS_ISI
        ],
        "columnDefs": [
COLUMNDEFS_ISI
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

    $("form[name='form_NAMA_TABLE']").validate({
        rules: {
RULES_ISI
        },
        messages: {
MESSAGES_ISI
        },
        submitHandler: function (form) {
 SUBMITHANDLER_ISI
 var typeact = $('#typeact').val();

 var formData = new FormData(form); // tambahan
 formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url([ADMIN,'MODULE_NAME','aksi'])?}",
                method: "POST",
                contentType: false, // tambahan
                processData: false, // tambahan
                data: formData,
                success: function (data) {
                    if (typeact == "add") {
                        alert("Data Berhasil Ditambah");
                    }
                    else if (typeact == "edit") {
                        alert("Data Berhasil Diubah");
                    }
                    $("#modal_cs").hide();
                    location.reload(true);
                }
            })
        }
    });

    // ==============================================================
    // KETIKA MENGETIK DI INPUT SEARCH
    // ==============================================================
    $('#search_text_NAMA_TABLE').keyup(function () {
        var_tbl_NAMA_TABLE.draw();
    });
    // ==============================================================
    // CLICK TANDA X DI INPUT SEARCH
    // ==============================================================
    $("#searchclear_NAMA_TABLE").click(function () {
        $("#search_text_NAMA_TABLE").val("");
        var_tbl_NAMA_TABLE.draw();
    });

    // ===========================================
    // Ketika tombol Edit di tekan
    // ===========================================

    $("#edit_data_NAMA_TABLE").click(function () {
        var rowData = var_tbl_NAMA_TABLE.rows({ selected: true }).data()[0];
        if (rowData != null) {

            EDIT_ISI


            $("#typeact").val("edit");
  
            FORM_ISI
            //$("#EDIT_FIELD").prop('disabled', true); // GA BISA DIEDIT KALI DISABLE
            $('#modal-title').text("Edit Data NAMA_MODULE");
            $("#modal_NAMA_TABLE").modal();
        }
        else {
            alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_NAMA_TABLE").click(function () {
        var rowData = var_tbl_NAMA_TABLE.rows({ selected: true }).data()[0];


        if (rowData) {
DELETE_ISI
            var a = confirm("Anda yakin akan menghapus data dengan DEL_FIELD=" + DEL_FIELD);
            if (a) {

                $.ajax({
                    url: "{?=url([ADMIN,'MODULE_NAME','aksi'])?}",
                    method: "POST",
                    data: {
                        DEL_FIELD: DEL_FIELD,
                        typeact: 'del'
                    },
                    success: function (data) {
                        data = JSON.parse(data);
                        if(data.status === 'success') {
                            alert(data.msg);
                        } else {
                            alert(data.msg);
                        }
                        location.reload(true);
                    }
                })
            }
        }
        else {
            alert("Pilih satu baris untuk dihapus");
        }
    });

    // ==============================================================
    // TOMBOL TAMBAH DATA DI CLICK
    // ==============================================================
    jQuery("#tambah_data_NAMA_TABLE").click(function () {

        TAMBAH_ISI

        $("#typeact").val("add");
        $("#ADD_FIELD").prop('disabled', false);
        
        $('#modal-title').text("Tambah Data NAMA_MODULE");
        $("#modal_NAMA_TABLE").modal();
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_NAMA_TABLE").click(function () {

        var search_field_NAMA_TABLE = $('#search_field_NAMA_TABLE').val();
        var search_text_NAMA_TABLE = $('#search_text_NAMA_TABLE').val();

        $.ajax({
            url: "{?=url([ADMIN,'MODULE_NAME','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_NAMA_TABLE: search_field_NAMA_TABLE, 
                search_text_NAMA_TABLE: search_text_NAMA_TABLE
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_NAMA_TABLE' class='table display dataTable' style='width:100%'><thead>HEADER_ISI</thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    ETABLE_ISI
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_NAMA_TABLE').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_NAMA_TABLE").modal();
    });

    // ==============================================================
    // TOMBOL DETAIL NAMA_TABLE DI CLICK
    // ==============================================================
    jQuery("#lihat_detail_NAMA_TABLE").click(function (event) {

        var rowData = var_tbl_NAMA_TABLE.rows({ selected: true }).data()[0];

        if (rowData) {
DATA_ISI
            var baseURL = mlite.url + '/' + mlite.admin;
            event.preventDefault();
            var loadURL =  baseURL + '/MODULE_NAME/detail/' + DEL_FIELD + '?t=' + mlite.token;
        
            var modal = $('#modal_detail_NAMA_TABLE');
            var modalContent = $('#modal_detail_NAMA_TABLE .modal-content');
        
            modal.off('show.bs.modal');
            modal.on('show.bs.modal', function () {
                modalContent.load(loadURL);
            }).modal();
            return false;
        
        }
        else {
            alert("Pilih satu baris untuk detail");
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
        doc.text("Tabel Data NAMA_TABLE_UPPER", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_NAMA_TABLE',
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
        // doc.save('table_data_NAMA_TABLE.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_NAMA_TABLE");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data NAMA_TABLE");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })
});