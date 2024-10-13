jQuery().ready(function () {
    var var_tbl_mlite_antrian_referensi_taskid = $('#tbl_mlite_antrian_referensi_taskid').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url([ADMIN,'log_antrian_taskid','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_mlite_antrian_referensi_taskid = $('#search_field_mlite_antrian_referensi_taskid').val();
                var search_text_mlite_antrian_referensi_taskid = $('#search_text_mlite_antrian_referensi_taskid').val();
                
                data.search_field_mlite_antrian_referensi_taskid = search_field_mlite_antrian_referensi_taskid;
                data.search_text_mlite_antrian_referensi_taskid = search_text_mlite_antrian_referensi_taskid;
                
            }
        },
        "columns": [
{ 'data': 'tanggal_periksa' },
{ 'data': 'nomor_referensi' },
{ 'data': 'taskid' },
{ 'data': 'waktu' },
{ 'data': 'status' },
{ 'data': 'keterangan' }

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

    // ==============================================================
    // FORM VALIDASI
    // ==============================================================

    $("form[name='form_mlite_antrian_referensi_taskid']").validate({
        rules: {
tanggal_periksa: 'required',
nomor_referensi: 'required',
taskid: 'required',
waktu: 'required',
status: 'required',
keterangan: 'required'

        },
        messages: {
tanggal_periksa:'tanggal_periksa tidak boleh kosong!',
nomor_referensi:'nomor_referensi tidak boleh kosong!',
taskid:'taskid tidak boleh kosong!',
waktu:'waktu tidak boleh kosong!',
status:'status tidak boleh kosong!',
keterangan:'keterangan tidak boleh kosong!'

        },
        submitHandler: function (form) {
 var tanggal_periksa= $('#tanggal_periksa').val();
var nomor_referensi= $('#nomor_referensi').val();
var taskid= $('#taskid').val();
var waktu= $('#waktu').val();
var status= $('#status').val();
var keterangan= $('#keterangan').val();

 var typeact = $('#typeact').val();

 var formData = new FormData(form); // tambahan
 formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url([ADMIN,'log_antrian_taskid','aksi'])?}",
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
    $('#search_text_mlite_antrian_referensi_taskid').keyup(function () {
        var_tbl_mlite_antrian_referensi_taskid.draw();
    });
    // ==============================================================
    // CLICK TANDA X DI INPUT SEARCH
    // ==============================================================
    $("#searchclear_mlite_antrian_referensi_taskid").click(function () {
        $("#search_text_mlite_antrian_referensi_taskid").val("");
        var_tbl_mlite_antrian_referensi_taskid.draw();
    });

    // ===========================================
    // Ketika tombol Edit di tekan
    // ===========================================

    $("#edit_data_mlite_antrian_referensi_taskid").click(function () {
        var rowData = var_tbl_mlite_antrian_referensi_taskid.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var tanggal_periksa = rowData['tanggal_periksa'];
var nomor_referensi = rowData['nomor_referensi'];
var taskid = rowData['taskid'];
var waktu = rowData['waktu'];
var status = rowData['status'];
var keterangan = rowData['keterangan'];



            $("#typeact").val("edit");
  
            $('#tanggal_periksa').val(tanggal_periksa);
$('#nomor_referensi').val(nomor_referensi);
$('#taskid').val(taskid);
$('#waktu').val(waktu);
$('#status').val(status);
$('#keterangan').val(keterangan);

            //$("#tanggal_periksa").prop('disabled', true); // GA BISA DIEDIT KALI DISABLE
            $('#modal-title').text("Edit Data Log Antrian TaskID");
            $("#modal_mlite_antrian_referensi_taskid").modal();
        }
        else {
            alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_mlite_antrian_referensi_taskid").click(function () {
        var rowData = var_tbl_mlite_antrian_referensi_taskid.rows({ selected: true }).data()[0];


        if (rowData) {
var tanggal_periksa = rowData['tanggal_periksa'];
            var a = confirm("Anda yakin akan menghapus data dengan tanggal_periksa=" + tanggal_periksa);
            if (a) {

                $.ajax({
                    url: "{?=url([ADMIN,'log_antrian_taskid','aksi'])?}",
                    method: "POST",
                    data: {
                        tanggal_periksa: tanggal_periksa,
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
    jQuery("#tambah_data_mlite_antrian_referensi_taskid").click(function () {

        $('#tanggal_periksa').val('');
$('#nomor_referensi').val('');
$('#taskid').val('');
$('#waktu').val('');
$('#status').val('');
$('#keterangan').val('');


        $("#typeact").val("add");
        $("#tanggal_periksa").prop('disabled', false);
        
        $('#modal-title').text("Tambah Data Log Antrian TaskID");
        $("#modal_mlite_antrian_referensi_taskid").modal();
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_mlite_antrian_referensi_taskid").click(function () {

        var search_field_mlite_antrian_referensi_taskid = $('#search_field_mlite_antrian_referensi_taskid').val();
        var search_text_mlite_antrian_referensi_taskid = $('#search_text_mlite_antrian_referensi_taskid').val();

        $.ajax({
            url: "{?=url([ADMIN,'log_antrian_taskid','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_mlite_antrian_referensi_taskid: search_field_mlite_antrian_referensi_taskid, 
                search_text_mlite_antrian_referensi_taskid: search_text_mlite_antrian_referensi_taskid
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_mlite_antrian_referensi_taskid' class='table display dataTable' style='width:100%'><thead><th>Response</th><th>Jumlah</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['keterangan'] + '</td>';
                    eTable += '<td>' + res[i]['jumlah'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_mlite_antrian_referensi_taskid').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_mlite_antrian_referensi_taskid").modal();
    });

    // ==============================================================
    // TOMBOL DETAIL mlite_antrian_referensi_taskid DI CLICK
    // ==============================================================
    jQuery("#lihat_detail_mlite_antrian_referensi_taskid").click(function (event) {

        var rowData = var_tbl_mlite_antrian_referensi_taskid.rows({ selected: true }).data()[0];

        if (rowData) {
            var tanggal_periksa = rowData['tanggal_periksa'];
            var baseURL = mlite.url + '/' + mlite.admin;
            event.preventDefault();
            var loadURL =  baseURL + '/log_antrian_taskid/detail/' + tanggal_periksa + '?t=' + mlite.token;
        
            var modal = $('#modal_detail_mlite_antrian_referensi_taskid');
            var modalContent = $('#modal_detail_mlite_antrian_referensi_taskid .modal-content');
        
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
        doc.text("Tabel Data Mlite Antrian Referensi Taskid", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_mlite_antrian_referensi_taskid',
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
        // doc.save('table_data_mlite_antrian_referensi_taskid.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_mlite_antrian_referensi_taskid");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data mlite_antrian_referensi_taskid");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })
});