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
            "url": "{?=url([ADMIN,'NAMA_TABLE','data'])?}",
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
        "scrollY": '48vh', 
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
                url: "{?=url([ADMIN,'NAMA_TABLE','aksi'])?}",
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
            $('#modal-title').text("Edit Data NAMA_TABLE");
            $("#modal_NAMA_TABLE").modal();
        }
        else {
            alert("Silakan pilih data yang akan di edit.");
        }
        //var no_pengajuan = rowData["no_pengajuan"];

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
                    url: "{?=url([ADMIN,'NAMA_TABLE','aksi'])?}",
                    method: "POST",
                    data: {
                        DEL_FIELD: DEL_FIELD,
                        typeact: 'del'
                    },
                    success: function (data) {
                        alert("Data Berhasil Dihapus");
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
        
        $('#modal-title').text("Tambah Data NAMA_TABLE");
        $("#modal_NAMA_TABLE").modal();
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_NAMA_TABLE").click(function () {

        var search_field_NAMA_TABLE = $('#search_field_NAMA_TABLE').val();
        var search_text_NAMA_TABLE = $('#search_text_NAMA_TABLE').val();

        $.ajax({
            url: "{?=url([ADMIN,'NAMA_TABLE','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field: search_field_NAMA_TABLE, 
                search_value: search_text_NAMA_TABLE
            },
            dataType: 'json',
            success: function (res) {
                console.log(res);
                var eTable = "<div class='table-responsive'><table id='tbl_NAMA_TABLE' class='display dataTable' style='width:100%'><thead>HEADER_ISI</thead>";
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
    // ===========================================
    // Ketika tombol print data di tekan
    // ===========================================
    $("#print_data").click(function () {
        printHtml("forTable_NAMA_TABLE");
    });

    // ===========================================
    // Ketika tombol export pdf di tekan
    // ===========================================
    $("#export_pdf").click(function () {

        var doc = new jsPDF('p', 'pt', 'A4');
        doc.setFontSize(16);
        doc.text("Tabel Data NAMA_TABLE", (doc.internal.pageSize.width / 2), 50, null, null, 'center');
        doc.autoTable({
            html: '#tbl_NAMA_TABLE',
            startY: 60,
            styles: {
                fontSize: 10,
                cellPadding: 1
            }
        });
        doc.save('table_data_NAMA_TABLE.pdf')
    })


    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_NAMA_TABLE");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data NAMA_TABLE");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })
});