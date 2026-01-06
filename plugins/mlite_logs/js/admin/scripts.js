jQuery().ready(function () {
    var var_tbl_mlite_query_logs = $('#tbl_mlite_query_logs').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url([ADMIN,'mlite_logs','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_mlite_query_logs = $('#search_field_mlite_query_logs').val();
                var search_text_mlite_query_logs = $('#search_text_mlite_query_logs').val();
                
                data.search_field_mlite_query_logs = search_field_mlite_query_logs;
                data.search_text_mlite_query_logs = search_text_mlite_query_logs;
                
            }
        },
        "columns": [
{ 'data': 'id' },
{ 'data': 'username' },
{ 'data': 'created_at' },
{ 'data': 'sql_text' },
{ 'data': 'bindings' },
{ 'data': 'error_message' }

        ],
        "columnDefs": [
{ 'targets': 0},
{ 'targets': 1},
{ 'targets': 2},
{ 'targets': 3},
{ 'targets': 4},
{ 'targets': 5}

        ],
        buttons: [],
        "scrollCollapse": true,
        // "scrollY": '48vh', 
        "pageLength":'25', 
        "lengthChange": true,
        "scrollX": true,
        dom: "<'row'<'col-sm-12'tr>><<'pmd-datatable-pagination' l i p>>"
    });

    // Configure long press
    let longPressTimer;
    let longPressDelay = 500; // milliseconds

    // Context menu configuration
    $.contextMenu({
        selector: '#tbl_mlite_query_logs tbody tr', 
        trigger: 'right', // for right-click
        events: {
            show: function(options) {
                // Highlight the selected row
                $(this).addClass('selected');
            },
            hide: function(options) {
                // Remove highlight
                $(this).removeClass('selected');
            }
        },
        callback: function(key, options) {
            // Get the data from the selected row
            let table = $('#tbl_mlite_query_logs').DataTable();
            let data = table.row(this).data();
            
            // Handle menu actions
            switch(key) {
                case "edit":
                    $('#edit_data_mlite_query_logs').trigger('click');
                    break;
                case "delete":
                    $('#hapus_data_mlite_query_logs').trigger('click');
                    break;
                case "detail":
                    $('#lihat_detail_mlite_query_logs').trigger('click');
                    break;
            }
        },
        items: {
            "edit": {name: "Edit", icon: "fa-edit"},
            "delete": {name: "Hapus", icon: "fa-trash"},
            "detail": {name: "Detail", icon: "fa-eye"},
            "sep1": "---------",
            "quit": {name: "Tutup", icon: "fa-close"}
        }
    });

    // Add touch support for mobile devices
    $('#tbl_mlite_query_logs tbody').on('touchstart', 'tr', function(e) {
        let row = $(this);
        longPressTimer = setTimeout(function() {
            // Trigger context menu
            row.contextMenu({x: e.originalEvent.touches[0].pageX, y: e.originalEvent.touches[0].pageY});
        }, longPressDelay);
    }).on('touchend touchcancel', 'tr', function() {
        // Clear timer if touch ends before longpress delay
        clearTimeout(longPressTimer);
    });

    // ==============================================================
    // CLICK ICON SEARCH DI INPUT SEARCH
    // ==============================================================
    $("#search_mlite_query_logs").click(function () {
        var_tbl_mlite_query_logs.draw();
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_mlite_query_logs").click(function () {

        var search_field_mlite_query_logs = $('#search_field_mlite_query_logs').val();
        var search_text_mlite_query_logs = $('#search_text_mlite_query_logs').val();

        $.ajax({
            url: "{?=url([ADMIN,'mlite_logs','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_mlite_query_logs: search_field_mlite_query_logs, 
                search_text_mlite_query_logs: search_text_mlite_query_logs
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_mlite_query_logs' class='table display dataTable' style='width:100%'><thead><th>Id</th><th>Sql Text</th><th>Bindings</th><th>Created At</th><th>Error Message</th><th>Username</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['id'] + '</td>';
eTable += '<td>' + res[i]['sql_text'] + '</td>';
eTable += '<td>' + res[i]['bindings'] + '</td>';
eTable += '<td>' + res[i]['created_at'] + '</td>';
eTable += '<td>' + res[i]['error_message'] + '</td>';
eTable += '<td>' + res[i]['username'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_mlite_query_logs').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_mlite_query_logs").modal();
    });

    // ==============================================================
    // TOMBOL DETAIL mlite_query_logs DI CLICK
    // ==============================================================
    jQuery("#lihat_detail_mlite_query_logs").click(function (event) {

        var rowData = var_tbl_mlite_query_logs.rows({ selected: true }).data()[0];

        if (rowData) {
var id = rowData['id'];
            var baseURL = mlite.url + '/' + mlite.admin;
            event.preventDefault();
            var loadURL =  baseURL + '/mlite_logs/detail/' + id + '?t=' + mlite.token;
        
            var modal = $('#modal_detail_mlite_query_logs');
            var modalContent = $('#modal_detail_mlite_query_logs .modal-content');
        
            modal.off('show.bs.modal');
            modal.on('show.bs.modal', function () {
                modalContent.load(loadURL);
            }).modal();
            return false;
        
        }
        else {
            bootbox.alert("Pilih satu baris untuk detail");
        }
    });
        
    // ===========================================
    // Ketika tombol export pdf di tekan
    // ===========================================
    $("#export_pdf").click(function () {

        var doc = new jsPDF('p', 'pt', 'A4'); /* pilih 'l' atau 'p' */
        var img = "{?=base64_encode(file_get_contents(BASE_DIR . '/' . $settings['logo']))?}";
        doc.addImage(img, 'JPEG', 20, 10, 50, 50);
        doc.setFontSize(20);
        doc.text("{$settings.nama_instansi}", 80, 35, null, null, null);
        doc.setFontSize(10);
        doc.text("{$settings.alamat} - {$settings.kota} - {$settings.propinsi}", 80, 46, null, null, null);
        doc.text("Telepon: {$settings.nomor_telepon} - Email: {$settings.email}", 80, 56, null, null, null);
        doc.line(20,70,572,70,null); /* doc.line(20,70,820,70,null); --> Jika landscape */
        doc.line(20,72,572,72,null); /* doc.line(20,72,820,72,null); --> Jika landscape */
        doc.setFontSize(14);
        doc.text("Tabel Data Mlite Query Logs", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_mlite_query_logs',
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
        // doc.save('table_data_mlite_query_logs.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_mlite_query_logs");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data mlite_query_logs");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    // ===========================================
    // Ketika tombol chart di tekan
    // ===========================================

    $("#view_chart").click(function () {
        var baseURL = mlite.url + '/' + mlite.admin;
        window.open(baseURL + '/mlite_logs/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});