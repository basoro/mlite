jQuery().ready(function () {
    var var_tbl_jam_masuk = $('#tbl_jam_masuk').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url([ADMIN,'jam_masuk','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_jam_masuk = $('#search_field_jam_masuk').val();
                var search_text_jam_masuk = $('#search_text_jam_masuk').val();
                
                data.search_field_jam_masuk = search_field_jam_masuk;
                data.search_text_jam_masuk = search_text_jam_masuk;
                
            }
        },
        "columns": [
{ 'data': 'shift' },
{ 'data': 'jam_masuk' },
{ 'data': 'jam_pulang' }

        ],
        "columnDefs": [
{ 'targets': 0},
{ 'targets': 1},
{ 'targets': 2}

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

    $("form[name='form_jam_masuk']").validate({
        rules: {
shift: 'required',
jam_masuk: 'required',
jam_pulang: 'required'

        },
        messages: {
shift:'shift tidak boleh kosong!',
jam_masuk:'jam_masuk tidak boleh kosong!',
jam_pulang:'jam_pulang tidak boleh kosong!'

        },
        submitHandler: function (form) {
 var shift= $('#shift').val();
var jam_masuk= $('#jam_masuk').val();
var jam_pulang= $('#jam_pulang').val();

 var typeact = $('#typeact').val();

 var formData = new FormData(form); // tambahan
 formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url([ADMIN,'jam_masuk','aksi'])?}",
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
    $('#search_text_jam_masuk').keyup(function () {
        var_tbl_jam_masuk.draw();
    });
    // ==============================================================
    // CLICK TANDA X DI INPUT SEARCH
    // ==============================================================
    $("#searchclear_jam_masuk").click(function () {
        $("#search_text_jam_masuk").val("");
        var_tbl_jam_masuk.draw();
    });

    // ===========================================
    // Ketika tombol Edit di tekan
    // ===========================================

    $("#edit_data_jam_masuk").click(function () {
        var rowData = var_tbl_jam_masuk.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var shift = rowData['shift'];
var jam_masuk = rowData['jam_masuk'];
var jam_pulang = rowData['jam_pulang'];



            $("#typeact").val("edit");
  
            $('#shift').val(shift);
$('#jam_masuk').val(jam_masuk);
$('#jam_pulang').val(jam_pulang);

            //$("#shift").prop('disabled', true); // GA BISA DIEDIT KALI DISABLE
            $('#modal-title').text("Edit Data Jam Masuk");
            $("#modal_jam_masuk").modal();
        }
        else {
            alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_jam_masuk").click(function () {
        var rowData = var_tbl_jam_masuk.rows({ selected: true }).data()[0];


        if (rowData) {
var shift = rowData['shift'];
            var a = confirm("Anda yakin akan menghapus data dengan shift=" + shift);
            if (a) {

                $.ajax({
                    url: "{?=url([ADMIN,'jam_masuk','aksi'])?}",
                    method: "POST",
                    data: {
                        shift: shift,
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
    jQuery("#tambah_data_jam_masuk").click(function () {

        $('#shift').val('');
$('#jam_masuk').val('');
$('#jam_pulang').val('');


        $("#typeact").val("add");
        $("#shift").prop('disabled', false);
        
        $('#modal-title').text("Tambah Data Jam Masuk");
        $("#modal_jam_masuk").modal();
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_jam_masuk").click(function () {

        var search_field_jam_masuk = $('#search_field_jam_masuk').val();
        var search_text_jam_masuk = $('#search_text_jam_masuk').val();

        $.ajax({
            url: "{?=url([ADMIN,'jam_masuk','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_jam_masuk: search_field_jam_masuk, 
                search_text_jam_masuk: search_text_jam_masuk
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_jam_masuk' class='table display dataTable' style='width:100%'><thead><th>Shift</th><th>Jam Masuk</th><th>Jam Pulang</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['shift'] + '</td>';
eTable += '<td>' + res[i]['jam_masuk'] + '</td>';
eTable += '<td>' + res[i]['jam_pulang'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_jam_masuk').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_jam_masuk").modal();
    });

    // ==============================================================
    // TOMBOL DETAIL jam_masuk DI CLICK
    // ==============================================================
    jQuery("#lihat_detail_jam_masuk").click(function (event) {

        var rowData = var_tbl_jam_masuk.rows({ selected: true }).data()[0];

        if (rowData) {
var shift = rowData['shift'];
            var baseURL = mlite.url + '/' + mlite.admin;
            event.preventDefault();
            var loadURL =  baseURL + '/jam_masuk/detail/' + shift + '?t=' + mlite.token;
        
            var modal = $('#modal_detail_jam_masuk');
            var modalContent = $('#modal_detail_jam_masuk .modal-content');
        
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
        doc.text("Tabel Data Jam Masuk", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_jam_masuk',
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
        // doc.save('table_data_jam_masuk.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_jam_masuk");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data jam_masuk");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })
});