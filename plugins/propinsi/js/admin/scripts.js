jQuery().ready(function () {
    var var_tbl_propinsi = $('#tbl_propinsi').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url([ADMIN,'propinsi','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_propinsi = $('#search_field_propinsi').val();
                var search_text_propinsi = $('#search_text_propinsi').val();
                
                data.search_field_propinsi = search_field_propinsi;
                data.search_text_propinsi = search_text_propinsi;
                
            }
        },
        "columns": [
{ 'data': 'kd_prop' },
{ 'data': 'nm_prop' }

        ],
        "columnDefs": [
{ 'targets': 0},
{ 'targets': 1}

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

    $("form[name='form_propinsi']").validate({
        rules: {
kd_prop: 'required',
nm_prop: 'required'

        },
        messages: {
kd_prop:'kd_prop tidak boleh kosong!',
nm_prop:'nm_prop tidak boleh kosong!'

        },
        submitHandler: function (form) {
 var kd_prop= $('#kd_prop').val();
var nm_prop= $('#nm_prop').val();

 var typeact = $('#typeact').val();

 var formData = new FormData(form); // tambahan
 formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url([ADMIN,'propinsi','aksi'])?}",
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
    $('#search_text_propinsi').keyup(function () {
        var_tbl_propinsi.draw();
    });
    // ==============================================================
    // CLICK TANDA X DI INPUT SEARCH
    // ==============================================================
    $("#searchclear_propinsi").click(function () {
        $("#search_text_propinsi").val("");
        var_tbl_propinsi.draw();
    });

    // ===========================================
    // Ketika tombol Edit di tekan
    // ===========================================

    $("#edit_data_propinsi").click(function () {
        var rowData = var_tbl_propinsi.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var kd_prop = rowData['kd_prop'];
var nm_prop = rowData['nm_prop'];



            $("#typeact").val("edit");
  
            $('#kd_prop').val(kd_prop);
$('#nm_prop').val(nm_prop);

            //$("#kd_prop").prop('disabled', true); // GA BISA DIEDIT KALI DISABLE
            $('#modal-title').text("Edit Data Propinsi");
            $("#modal_propinsi").modal();
        }
        else {
            alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_propinsi").click(function () {
        var rowData = var_tbl_propinsi.rows({ selected: true }).data()[0];


        if (rowData) {
var kd_prop = rowData['kd_prop'];
            var a = confirm("Anda yakin akan menghapus data dengan kd_prop=" + kd_prop);
            if (a) {

                $.ajax({
                    url: "{?=url([ADMIN,'propinsi','aksi'])?}",
                    method: "POST",
                    data: {
                        kd_prop: kd_prop,
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
    jQuery("#tambah_data_propinsi").click(function () {

        $('#kd_prop').val('');
$('#nm_prop').val('');


        $("#typeact").val("add");
        $("#kd_prop").prop('disabled', false);
        
        $('#modal-title').text("Tambah Data Propinsi");
        $("#modal_propinsi").modal();
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_propinsi").click(function () {

        var search_field_propinsi = $('#search_field_propinsi').val();
        var search_text_propinsi = $('#search_text_propinsi').val();

        $.ajax({
            url: "{?=url([ADMIN,'propinsi','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_propinsi: search_field_propinsi, 
                search_text_propinsi: search_text_propinsi
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_propinsi' class='table display dataTable' style='width:100%'><thead><th>Kd Prop</th><th>Nm Prop</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['kd_prop'] + '</td>';
eTable += '<td>' + res[i]['nm_prop'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_propinsi').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_propinsi").modal();
    });

    // ==============================================================
    // TOMBOL DETAIL propinsi DI CLICK
    // ==============================================================
    jQuery("#lihat_detail_propinsi").click(function (event) {

        var rowData = var_tbl_propinsi.rows({ selected: true }).data()[0];

        if (rowData) {
var kd_prop = rowData['kd_prop'];
            var baseURL = mlite.url + '/' + mlite.admin;
            event.preventDefault();
            var loadURL =  baseURL + '/propinsi/detail/' + kd_prop + '?t=' + mlite.token;
        
            var modal = $('#modal_detail_propinsi');
            var modalContent = $('#modal_detail_propinsi .modal-content');
        
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
        doc.text("Tabel Data Propinsi", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_propinsi',
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
        // doc.save('table_data_propinsi.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_propinsi");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data propinsi");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })
});