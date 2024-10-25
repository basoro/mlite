jQuery().ready(function () {
    var var_tbl_databarang = $('#tbl_databarang').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url([ADMIN,'darurat_stok','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_databarang = $('#search_field_databarang').val();
                var search_text_databarang = $('#search_text_databarang').val();
                
                data.search_field_databarang = search_field_databarang;
                data.search_text_databarang = search_text_databarang;
                
            }
        },
        "columns": [
{ 'data': 'kode_brng' },
{ 'data': 'nama_brng' },
{ 'data': 'stok' },
{ 'data': 'stokminimal' },
{ 'data': 'kode_satbesar' },
{ 'data': 'kode_sat' },
{ 'data': 'dasar' },
{ 'data': 'h_beli' },
{ 'data': 'isi' },
{ 'data': 'kapasitas' },
{ 'data': 'expire' }

        ],
        "columnDefs": [
{ 'targets': 0},
{ 'targets': 1},
{ 'targets': 2},
{ 'targets': 3},
{ 'targets': 4},
{ 'targets': 5},
{ 'targets': 6},
{ 'targets': 7},
{ 'targets': 8},
{ 'targets': 9},
{ 'targets': 10}

        ],
        "createdRow": function( row, data, dataIndex){
            if(data['stok'] <= data['stokminimal']){
                $('td', row).css('background-color', 'Red');
                $('td', row).css('color', 'white');
            }
        },         
        buttons: [],
        "scrollCollapse": true,
        // "scrollY": '48vh', 
        "pageLength":'25', 
        "lengthChange": true,
        "scrollX": true,
        dom: "<'row'<'col-sm-12'tr>><<'pmd-datatable-pagination' l i p>>"
    });


    // ==============================================================
    // KETIKA MENGETIK DI INPUT SEARCH
    // ==============================================================
    $('#search_text_databarang').keyup(function () {
        var_tbl_databarang.draw();
    });
    // ==============================================================
    // CLICK TANDA X DI INPUT SEARCH
    // ==============================================================
    $("#searchclear_databarang").click(function () {
        $("#search_text_databarang").val("");
        var_tbl_databarang.draw();
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_databarang").click(function () {

        var search_field_databarang = $('#search_field_databarang').val();
        var search_text_databarang = $('#search_text_databarang').val();

        $.ajax({
            url: "{?=url([ADMIN,'darurat_stok','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_databarang: search_field_databarang, 
                search_text_databarang: search_text_databarang
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_databarang' class='table display dataTable' style='width:100%'><thead><th>Kode Brng</th><th>Nama Brng</th><th>Stok Saat Ini</th><th>Stokminimal</th><th>Kode Satbesar</th><th>Kode Sat</th><th>Harga Dasar</th><th>Harga Beli</th><th>Isi</th><th>Kapasitas</th><th>Expire</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['kode_brng'] + '</td>';
                    eTable += '<td>' + res[i]['nama_brng'] + '</td>';
                    eTable += '<td>' + res[i]['stok'] + '</td>';
                    eTable += '<td>' + res[i]['stokminimal'] + '</td>';
                    eTable += '<td>' + res[i]['kode_satbesar'] + '</td>';
                    eTable += '<td>' + res[i]['kode_sat'] + '</td>';
                    eTable += '<td>' + res[i]['dasar'] + '</td>';
                    eTable += '<td>' + res[i]['h_beli'] + '</td>';
                    eTable += '<td>' + res[i]['isi'] + '</td>';
                    eTable += '<td>' + res[i]['kapasitas'] + '</td>';
                    eTable += '<td>' + res[i]['expire'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_databarang').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_databarang").modal();
    });

    // ==============================================================
    // TOMBOL DETAIL databarang DI CLICK
    // ==============================================================
    jQuery("#lihat_detail_databarang").click(function (event) {

        var rowData = var_tbl_databarang.rows({ selected: true }).data()[0];

        if (rowData) {
            var kode_brng = rowData['kode_brng'];
            var baseURL = mlite.url + '/' + mlite.admin;
            event.preventDefault();
            var loadURL =  baseURL + '/darurat_stok/detail/' + kode_brng + '?t=' + mlite.token;
        
            var modal = $('#modal_detail_databarang');
            var modalContent = $('#modal_detail_databarang .modal-content');
        
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

        var doc = new jsPDF('l', 'pt', 'A4'); /* pilih 'l' atau 'p' */
        var img = "{?=base64_encode(file_get_contents(url($settings['logo'])))?}";
        doc.addImage(img, 'JPEG', 20, 10, 50, 50);
        doc.setFontSize(20);
        doc.text("{$settings.nama_instansi}", 80, 35, null, null, null);
        doc.setFontSize(10);
        doc.text("{$settings.alamat} - {$settings.kota} - {$settings.propinsi}", 80, 46, null, null, null);
        doc.text("Telepon: {$settings.nomor_telepon} - Email: {$settings.email}", 80, 56, null, null, null);
        doc.line(20,70,820,70,null); /* doc.line(20,70,820,70,null); --> Jika landscape */
        doc.line(20,72,820,72,null); /* doc.line(20,72,820,72,null); --> Jika landscape */
        doc.setFontSize(14);
        doc.text("Tabel Data Databarang", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_databarang',
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
        // doc.save('table_data_databarang.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_databarang");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data databarang");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })
});