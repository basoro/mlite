jQuery().ready(function () {
    var var_tbl_icd9 = $('#tbl_icd9').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url([ADMIN,'icd_9','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_icd9 = $('#search_field_icd9').val();
                var search_text_icd9 = $('#search_text_icd9').val();
                
                data.search_field_icd9 = search_field_icd9;
                data.search_text_icd9 = search_text_icd9;
                
            }
        },
        "columns": [
{ 'data': 'kode' },
{ 'data': 'deskripsi_panjang' },
{ 'data': 'deskripsi_pendek' }

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

    $("form[name='form_icd9']").validate({
        rules: {
kode: 'required',
deskripsi_panjang: 'required',
deskripsi_pendek: 'required'

        },
        messages: {
kode:'kode tidak boleh kosong!',
deskripsi_panjang:'deskripsi_panjang tidak boleh kosong!',
deskripsi_pendek:'deskripsi_pendek tidak boleh kosong!'

        },
        submitHandler: function (form) {
 var kode= $('#kode').val();
var deskripsi_panjang= $('#deskripsi_panjang').val();
var deskripsi_pendek= $('#deskripsi_pendek').val();

 var typeact = $('#typeact').val();

 var formData = new FormData(form); // tambahan
 formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url([ADMIN,'icd_9','aksi'])?}",
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
    $('#search_text_icd9').keyup(function () {
        var_tbl_icd9.draw();
    });
    // ==============================================================
    // CLICK TANDA X DI INPUT SEARCH
    // ==============================================================
    $("#searchclear_icd9").click(function () {
        $("#search_text_icd9").val("");
        var_tbl_icd9.draw();
    });

    // ===========================================
    // Ketika tombol Edit di tekan
    // ===========================================

    $("#edit_data_icd9").click(function () {
        var rowData = var_tbl_icd9.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var kode = rowData['kode'];
var deskripsi_panjang = rowData['deskripsi_panjang'];
var deskripsi_pendek = rowData['deskripsi_pendek'];



            $("#typeact").val("edit");
  
            $('#kode').val(kode);
$('#deskripsi_panjang').val(deskripsi_panjang);
$('#deskripsi_pendek').val(deskripsi_pendek);

            //$("#kode").prop('disabled', true); // GA BISA DIEDIT KALI DISABLE
            $('#modal-title').text("Edit Data ICD 9");
            $("#modal_icd9").modal();
        }
        else {
            alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_icd9").click(function () {
        var rowData = var_tbl_icd9.rows({ selected: true }).data()[0];


        if (rowData) {
var kode = rowData['kode'];
            var a = confirm("Anda yakin akan menghapus data dengan kode=" + kode);
            if (a) {

                $.ajax({
                    url: "{?=url([ADMIN,'icd_9','aksi'])?}",
                    method: "POST",
                    data: {
                        kode: kode,
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
    jQuery("#tambah_data_icd9").click(function () {

        $('#kode').val('');
$('#deskripsi_panjang').val('');
$('#deskripsi_pendek').val('');


        $("#typeact").val("add");
        $("#kode").prop('disabled', false);
        
        $('#modal-title').text("Tambah Data ICD 9");
        $("#modal_icd9").modal();
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_icd9").click(function () {

        var search_field_icd9 = $('#search_field_icd9').val();
        var search_text_icd9 = $('#search_text_icd9').val();

        $.ajax({
            url: "{?=url([ADMIN,'icd_9','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_icd9: search_field_icd9, 
                search_text_icd9: search_text_icd9
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_icd9' class='table display dataTable' style='width:100%'><thead><th>Kode</th><th>Deskripsi Panjang</th><th>Deskripsi Pendek</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['kode'] + '</td>';
eTable += '<td>' + res[i]['deskripsi_panjang'] + '</td>';
eTable += '<td>' + res[i]['deskripsi_pendek'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_icd9').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_icd9").modal();
    });

    // ==============================================================
    // TOMBOL DETAIL icd9 DI CLICK
    // ==============================================================
    jQuery("#lihat_detail_icd9").click(function (event) {

        var rowData = var_tbl_icd9.rows({ selected: true }).data()[0];

        if (rowData) {
            var kode = rowData['kode'];
            var baseURL = mlite.url + '/' + mlite.admin;
            event.preventDefault();
            var loadURL =  baseURL + '/icd_9/detail/' + kode + '?t=' + mlite.token;
        
            var modal = $('#modal_detail_icd9');
            var modalContent = $('#modal_detail_icd9 .modal-content');
        
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
        doc.text("Tabel Data Icd9", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_icd9',
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
        // doc.save('table_data_icd9.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_icd9");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data icd9");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })
});