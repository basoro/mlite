jQuery().ready(function () {
    var var_tbl_mlite_antrian_referensi = $('#tbl_mlite_antrian_referensi').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo": false,
        "ajax": {
            "url": "{?=url([ADMIN,'laporan_bpjs','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_mlite_antrian_referensi = $('#search_field_mlite_antrian_referensi').val();
                var search_text_mlite_antrian_referensi = $('#search_text_mlite_antrian_referensi').val();

                data.search_field_mlite_antrian_referensi = search_field_mlite_antrian_referensi;
                data.search_text_mlite_antrian_referensi = search_text_mlite_antrian_referensi;

            }
        },
        "columns": [
            { 'data': 'tanggal_periksa' },
            { 'data': 'no_rkm_medis' },
            { 'data': 'nm_pasien' },
            { 'data': 'nm_poli' },
            { 'data': 'nomor_kartu' },
            { 'data': 'nomor_referensi' },
            { 'data': 'kodebooking' },
            { 'data': 'jenis_kunjungan' },
            { 'data': 'status_kirim' },
            { 'data': 'keterangan' }

        ],
        "columnDefs": [
            { 'targets': 0 },
            { 'targets': 1 },
            { 'targets': 2 },
            { 'targets': 3 },
            { 'targets': 4 },
            { 'targets': 5 },
            { 'targets': 6 },
            { 'targets': 7 },
            { 'targets': 8 },
            { 'targets': 9 },

        ],
        buttons: [],
        "scrollCollapse": true,
        // "scrollY": '48vh', 
        "pageLength": '25',
        "lengthChange": true,
        "scrollX": true,
        dom: "<'row'<'col-sm-12'tr>><<'pmd-datatable-pagination' l i p>>"
    });

    // ==============================================================
    // FORM VALIDASI
    // ==============================================================

    $("form[name='form_mlite_antrian_referensi']").validate({
        rules: {
            tanggal_periksa: 'required',
            no_rkm_medis: 'required',
            nomor_kartu: 'required',
            nomor_referensi: 'required',
            kodebooking: 'required',
            jenis_kunjungan: 'required',
            status_kirim: 'required',
            keterangan: 'required'

        },
        messages: {
            tanggal_periksa: 'tanggal_periksa tidak boleh kosong!',
            no_rkm_medis: 'no_rkm_medis tidak boleh kosong!',
            nomor_kartu: 'nomor_kartu tidak boleh kosong!',
            nomor_referensi: 'nomor_referensi tidak boleh kosong!',
            kodebooking: 'kodebooking tidak boleh kosong!',
            jenis_kunjungan: 'jenis_kunjungan tidak boleh kosong!',
            status_kirim: 'status_kirim tidak boleh kosong!',
            keterangan: 'keterangan tidak boleh kosong!'

        },
        submitHandler: function (form) {
            var tanggal_periksa = $('#tanggal_periksa').val();
            var no_rkm_medis = $('#no_rkm_medis').val();
            var nomor_kartu = $('#nomor_kartu').val();
            var nomor_referensi = $('#nomor_referensi').val();
            var kodebooking = $('#kodebooking').val();
            var jenis_kunjungan = $('#jenis_kunjungan').val();
            var status_kirim = $('#status_kirim').val();
            var keterangan = $('#keterangan').val();

            var typeact = $('#typeact').val();

            var formData = new FormData(form); // tambahan
            formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url([ADMIN,'laporan_bpjs','aksi'])?}",
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
    $('#search_text_mlite_antrian_referensi').keyup(function () {
        var_tbl_mlite_antrian_referensi.draw();
    });
    // ==============================================================
    // CLICK TANDA X DI INPUT SEARCH
    // ==============================================================
    $("#searchclear_mlite_antrian_referensi").click(function () {
        $("#search_text_mlite_antrian_referensi").val("");
        var_tbl_mlite_antrian_referensi.draw();
    });

    // ===========================================
    // Ketika tombol Edit di tekan
    // ===========================================

    $("#edit_data_mlite_antrian_referensi").click(function () {
        var rowData = var_tbl_mlite_antrian_referensi.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var tanggal_periksa = rowData['tanggal_periksa'];
            var no_rkm_medis = rowData['no_rkm_medis'];
            var nomor_kartu = rowData['nomor_kartu'];
            var nomor_referensi = rowData['nomor_referensi'];
            var kodebooking = rowData['kodebooking'];
            var jenis_kunjungan = rowData['jenis_kunjungan'];
            var status_kirim = rowData['status_kirim'];
            var keterangan = rowData['keterangan'];



            $("#typeact").val("edit");

            $('#tanggal_periksa').val(tanggal_periksa);
            $('#no_rkm_medis').val(no_rkm_medis);
            $('#nomor_kartu').val(nomor_kartu);
            $('#nomor_referensi').val(nomor_referensi);
            $('#kodebooking').val(kodebooking);
            $('#jenis_kunjungan').val(jenis_kunjungan);
            $('#status_kirim').val(status_kirim);
            $('#keterangan').val(keterangan);

            //$("#tanggal_periksa").prop('disabled', true); // GA BISA DIEDIT KALI DISABLE
            $('#modal-title').text("Edit Data laporan_bpjs");
            $("#modal_mlite_antrian_referensi").modal();
        }
        else {
            alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_mlite_antrian_referensi").click(function () {
        var rowData = var_tbl_mlite_antrian_referensi.rows({ selected: true }).data()[0];


        if (rowData) {
            var tanggal_periksa = rowData['tanggal_periksa'];
            var a = confirm("Anda yakin akan menghapus data dengan tanggal_periksa=" + tanggal_periksa);
            if (a) {

                $.ajax({
                    url: "{?=url([ADMIN,'laporan_bpjs','aksi'])?}",
                    method: "POST",
                    data: {
                        tanggal_periksa: tanggal_periksa,
                        typeact: 'del'
                    },
                    success: function (data) {
                        data = JSON.parse(data);
                        if (data.status === 'success') {
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
    jQuery("#tambah_data_mlite_antrian_referensi").click(function () {

        $('#tanggal_periksa').val('');
        $('#no_rkm_medis').val('');
        $('#nomor_kartu').val('');
        $('#nomor_referensi').val('');
        $('#kodebooking').val('');
        $('#jenis_kunjungan').val('');
        $('#status_kirim').val('');
        $('#keterangan').val('');


        $("#typeact").val("add");
        $("#tanggal_periksa").prop('disabled', false);

        $('#modal-title').text("Tambah Data laporan_bpjs");
        $("#modal_mlite_antrian_referensi").modal();
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_mlite_antrian_referensi").click(function () {

        var search_field_mlite_antrian_referensi = $('#search_field_mlite_antrian_referensi').val();
        var search_text_mlite_antrian_referensi = $('#search_text_mlite_antrian_referensi').val();

        $.ajax({
            url: "{?=url([ADMIN,'laporan_bpjs','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat',
                search_field_mlite_antrian_referensi: search_field_mlite_antrian_referensi,
                search_text_mlite_antrian_referensi: search_text_mlite_antrian_referensi
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_mlite_antrian_referensi' class='table display dataTable' style='width:100%'><thead><th>Tanggal Periksa</th><th>No Rkm Medis</th><th>Nomor Kartu</th><th>Nomor Referensi</th><th>Kodebooking</th><th>Jenis Kunjungan</th><th>Status Kirim</th><th>Keterangan</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['tanggal_periksa'] + '</td>';
                    eTable += '<td>' + res[i]['no_rkm_medis'] + '</td>';
                    eTable += '<td>' + res[i]['nomor_kartu'] + '</td>';
                    eTable += '<td>' + res[i]['nomor_referensi'] + '</td>';
                    eTable += '<td>' + res[i]['kodebooking'] + '</td>';
                    eTable += '<td>' + res[i]['jenis_kunjungan'] + '</td>';
                    eTable += '<td>' + res[i]['status_kirim'] + '</td>';
                    eTable += '<td>' + res[i]['keterangan'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_mlite_antrian_referensi').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_mlite_antrian_referensi").modal();
    });

    // ==============================================================
    // TOMBOL DETAIL mlite_antrian_referensi DI CLICK
    // ==============================================================
    jQuery("#lihat_detail_mlite_antrian_referensi").click(function (event) {

        var rowData = var_tbl_mlite_antrian_referensi.rows({ selected: true }).data()[0];

        if (rowData) {
            var tanggal_periksa = rowData['tanggal_periksa'];
            var baseURL = mlite.url + '/' + mlite.admin;
            event.preventDefault();
            var loadURL = baseURL + '/laporan_bpjs/detail/' + tanggal_periksa + '?t=' + mlite.token;

            var modal = $('#modal_detail_mlite_antrian_referensi');
            var modalContent = $('#modal_detail_mlite_antrian_referensi .modal-content');

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
        doc.line(20, 70, 572, 70, null); /* doc.line(20,70,820,70,null); --> Jika landscape */
        doc.line(20, 72, 572, 72, null); /* doc.line(20,72,820,72,null); --> Jika landscape */
        doc.setFontSize(14);
        doc.text("Tabel Data Mlite Antrian Referensi", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";
        doc.autoTable({
            html: '#tbl_lihat_mlite_antrian_referensi',
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
        // doc.save('table_data_mlite_antrian_referensi.pdf')
        window.open(doc.output('bloburl'), '_blank', "toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");

    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_mlite_antrian_referensi");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data mlite_antrian_referensi");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })
});