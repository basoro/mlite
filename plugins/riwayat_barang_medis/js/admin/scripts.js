jQuery().ready(function () {
    var var_tbl_riwayat_barang_medis = $('#tbl_riwayat_barang_medis').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url([ADMIN,'riwayat_barang_medis','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_riwayat_barang_medis = $('#search_field_riwayat_barang_medis').val();
                var search_text_riwayat_barang_medis = $('#search_text_riwayat_barang_medis').val();
                var tgl_awal = $('#tgl_awal').val();
                var tgl_akhir = $('#tgl_akhir').val();
                
                data.search_field_riwayat_barang_medis = search_field_riwayat_barang_medis;
                data.search_text_riwayat_barang_medis = search_text_riwayat_barang_medis;
                data.tgl_awal = tgl_awal;
                data.tgl_akhir = tgl_akhir;
                
            }
        },
        "columns": [
{ 'data': 'kode_brng' },
{ 'data': 'nama_brng' },
{ 'data': 'stok_awal' },
{ 'data': 'masuk' },
{ 'data': 'keluar' },
{ 'data': 'stok_akhir' },
{ 'data': 'posisi' },
{ 'data': 'tanggal' },
{ 'data': 'jam' },
{ 'data': 'petugas' },
{ 'data': 'kd_bangsal' },
{ 'data': 'nm_bangsal' },
{ 'data': 'status' },
{ 'data': 'no_batch' },
{ 'data': 'no_faktur' },
{ 'data': 'keterangan' }

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
{ 'targets': 10},
{ 'targets': 11},
{ 'targets': 12},
{ 'targets': 13},
{ 'targets': 14},
{ 'targets': 15}

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
    // KETIKA MENGETIK DI INPUT SEARCH
    // ==============================================================
    // $('#search_text_riwayat_barang_medis').keyup(function () {
    //     var_tbl_riwayat_barang_medis.draw();
    // });
    // ==============================================================
    // CLICK TANDA X DI INPUT SEARCH
    // ==============================================================
    $("#searchclear_riwayat_barang_medis").click(function () {
        // $("#search_text_riwayat_barang_medis").val("");
        var_tbl_riwayat_barang_medis.draw();
    });


    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_riwayat_barang_medis").click(function () {

        var search_field_riwayat_barang_medis = $('#search_field_riwayat_barang_medis').val();
        var search_text_riwayat_barang_medis = $('#search_text_riwayat_barang_medis').val();

        var tgl_awal = $('#tgl_awal').val();
        var tgl_akhir = $('#tgl_akhir').val();
        
        $.ajax({
            url: "{?=url([ADMIN,'riwayat_barang_medis','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_riwayat_barang_medis: search_field_riwayat_barang_medis, 
                search_text_riwayat_barang_medis: search_text_riwayat_barang_medis, 
                tgl_awal: tgl_awal, 
                tgl_akhir: tgl_akhir
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_riwayat_barang_medis' class='table display dataTable' style='width:100%'><thead><th>Kode Brng</th><th>Nama Brng</th><th>Stok Awal</th><th>Masuk</th><th>Keluar</th><th>Stok Akhir</th><th>Posisi</th><th>Tanggal</th><th>Jam</th><th>Petugas</th><th>Kd Bangsal</th><th>Nm Bangsal</th><th>Status</th><th>No Batch</th><th>No Faktur</th><th>Keterangan</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['kode_brng'] + '</td>';
                    eTable += '<td>' + res[i]['nama_brng'] + '</td>';
                    eTable += '<td>' + res[i]['stok_awal'] + '</td>';
                    eTable += '<td>' + res[i]['masuk'] + '</td>';
                    eTable += '<td>' + res[i]['keluar'] + '</td>';
                    eTable += '<td>' + res[i]['stok_akhir'] + '</td>';
                    eTable += '<td>' + res[i]['posisi'] + '</td>';
                    eTable += '<td>' + res[i]['tanggal'] + '</td>';
                    eTable += '<td>' + res[i]['jam'] + '</td>';
                    eTable += '<td>' + res[i]['petugas'] + '</td>';
                    eTable += '<td>' + res[i]['kd_bangsal'] + '</td>';
                    eTable += '<td>' + res[i]['nm_bangsal'] + '</td>';
                    eTable += '<td>' + res[i]['status'] + '</td>';
                    eTable += '<td>' + res[i]['no_batch'] + '</td>';
                    eTable += '<td>' + res[i]['no_faktur'] + '</td>';
                    eTable += '<td>' + res[i]['keterangan'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_riwayat_barang_medis').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_riwayat_barang_medis").modal();
    });

    // ==============================================================
    // TOMBOL DETAIL riwayat_barang_medis DI CLICK
    // ==============================================================
    jQuery("#lihat_detail_riwayat_barang_medis").click(function (event) {

        var rowData = var_tbl_riwayat_barang_medis.rows({ selected: true }).data()[0];

        if (rowData) {
var kode_brng = rowData['kode_brng'];
            var baseURL = mlite.url + '/' + mlite.admin;
            event.preventDefault();
            var loadURL =  baseURL + '/riwayat_barang_medis/detail/' + kode_brng + '?t=' + mlite.token;
        
            var modal = $('#modal_detail_riwayat_barang_medis');
            var modalContent = $('#modal_detail_riwayat_barang_medis .modal-content');
        
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
        doc.text("Tabel Data Riwayat Barang Medis", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_riwayat_barang_medis',
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
        // doc.save('table_data_riwayat_barang_medis.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_riwayat_barang_medis");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data riwayat_barang_medis");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })
});