jQuery().ready(function () {
    var var_tbl_booking_registrasi = $('#tbl_booking_registrasi').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['booking_registrasi','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_booking_registrasi = $('#search_field_booking_registrasi').val();
                var search_text_booking_registrasi = $('#search_text_booking_registrasi').val();
                
                data.search_field_booking_registrasi = search_field_booking_registrasi;
                data.search_text_booking_registrasi = search_text_booking_registrasi;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_booking_registrasi').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_booking_registrasi tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'tanggal_booking' },
{ 'data': 'jam_booking' },
{ 'data': 'no_rkm_medis' },
{ 'data': 'tanggal_periksa' },
{ 'data': 'kd_dokter' },
{ 'data': 'kd_poli' },
{ 'data': 'no_reg' },
{ 'data': 'kd_pj' },
{ 'data': 'limit_reg' },
{ 'data': 'waktu_kunjungan' },
{ 'data': 'status' }

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
        order: [[1, 'DESC']], 
        buttons: [],
        "scrollCollapse": true,
        // "scrollY": '48vh', 
        // "pageLength":'25', 
        "lengthChange": true,
        "scrollX": true,
        dom: "<'row'<'col-sm-12'tr>><<'pmd-datatable-pagination' l i p>>"
    });


    $.contextMenu({
        selector: '#tbl_booking_registrasi tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_booking_registrasi.rows({ selected: true }).data()[0];
          if (rowData != null) {
var tanggal_booking = rowData['tanggal_booking'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/booking_registrasi/detail/' + tanggal_booking + '?t=' + mlite.token);
                break;
                default :
                break
            } 
          } else {
            bootbox.alert("Silakan pilih data atau klik baris data.");            
          }          
        },
        items: {
            "detail": {name: "View Detail", "icon": "edit", disabled:  {$disabled_menu.read}}
        }
    });

    // ==============================================================
    // FORM VALIDASI
    // ==============================================================

    $("form[name='form_booking_registrasi']").validate({
        rules: {
tanggal_booking: 'required',
jam_booking: 'required',
no_rkm_medis: 'required',
tanggal_periksa: 'required',
kd_dokter: 'required',
kd_poli: 'required',
no_reg: 'required',
kd_pj: 'required',
limit_reg: 'required',
waktu_kunjungan: 'required',
status: 'required'

        },
        messages: {
tanggal_booking:'Tanggal Booking tidak boleh kosong!',
jam_booking:'Jam Booking tidak boleh kosong!',
no_rkm_medis:'No Rkm Medis tidak boleh kosong!',
tanggal_periksa:'Tanggal Periksa tidak boleh kosong!',
kd_dokter:'Kd Dokter tidak boleh kosong!',
kd_poli:'Kd Poli tidak boleh kosong!',
no_reg:'No Reg tidak boleh kosong!',
kd_pj:'Kd Pj tidak boleh kosong!',
limit_reg:'Limit Reg tidak boleh kosong!',
waktu_kunjungan:'Waktu Kunjungan tidak boleh kosong!',
status:'Status tidak boleh kosong!'

        },
        submitHandler: function (form) {
var tanggal_booking= $('#tanggal_booking').val();
var jam_booking= $('#jam_booking').val();
var no_rkm_medis= $('#no_rkm_medis').val();
var tanggal_periksa= $('#tanggal_periksa').val();
var kd_dokter= $('#kd_dokter').val();
var kd_poli= $('#kd_poli').val();
var no_reg= $('#no_reg').val();
var kd_pj= $('#kd_pj').val();
var limit_reg= $('#limit_reg').val();
var waktu_kunjungan= $('#waktu_kunjungan').val();
var status= $('#status').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['booking_registrasi','aksi'])?}",
                method: "POST",
                contentType: false, // tambahan
                processData: false, // tambahan
                data: formData,
                success: function (data) {
                    data = JSON.parse(data);
                    var audio = new Audio('{?=url()?}/assets/sound/' + data.status + '.mp3');
                    audio.play();
                    if (typeact == "add") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_booking_registrasi").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_booking_registrasi").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
                        let payload = {
                            'action' : typeact
                        }
                        ws.send(JSON.stringify(payload));
                    } 
                    var_tbl_booking_registrasi.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_booking_registrasi.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_booking_registrasi.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_booking_registrasi.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_booking_registrasi').click(function () {
        var_tbl_booking_registrasi.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_booking_registrasi").click(function () {
        var rowData = var_tbl_booking_registrasi.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var tanggal_booking = rowData['tanggal_booking'];
var jam_booking = rowData['jam_booking'];
var no_rkm_medis = rowData['no_rkm_medis'];
var tanggal_periksa = rowData['tanggal_periksa'];
var kd_dokter = rowData['kd_dokter'];
var kd_poli = rowData['kd_poli'];
var no_reg = rowData['no_reg'];
var kd_pj = rowData['kd_pj'];
var limit_reg = rowData['limit_reg'];
var waktu_kunjungan = rowData['waktu_kunjungan'];
var status = rowData['status'];

            $("#typeact").val("edit");
  
            $('#tanggal_booking').val(tanggal_booking);
$('#jam_booking').val(jam_booking);
$('#no_rkm_medis').val(no_rkm_medis);
$('#tanggal_periksa').val(tanggal_periksa);
$('#kd_dokter').val(kd_dokter);
$('#kd_poli').val(kd_poli);
$('#no_reg').val(no_reg);
$('#kd_pj').val(kd_pj);
$('#limit_reg').val(limit_reg);
$('#waktu_kunjungan').val(waktu_kunjungan);
$('#status').val(status);

            $("#tanggal_booking").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Booking Registrasi");
            $("#modal_booking_registrasi").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_booking_registrasi").click(function () {
        var rowData = var_tbl_booking_registrasi.rows({ selected: true }).data()[0];


        if (rowData) {
var tanggal_booking = rowData['tanggal_booking'];
            bootbox.confirm('Anda yakin akan menghapus data dengan tanggal_booking="' + tanggal_booking, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['booking_registrasi','aksi'])?}",
                        method: "POST",
                        data: {
                            tanggal_booking: tanggal_booking,
                            typeact: 'del'
                        },
                        success: function (data) {
                            data = JSON.parse(data);
                            var audio = new Audio('{?=url()?}/assets/sound/' + data.status + '.mp3');
                            audio.play();
                            if(data.status === 'success') {
                                bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            } else {
                                bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                            } 
                            if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
                                let payload = {
                                    'action' : 'del'
                                }
                                ws.send(JSON.stringify(payload));
                            }
                            var_tbl_booking_registrasi.draw();
                        }
                    })    
                }
            });

        }
        else {
            bootbox.alert("Pilih satu baris untuk dihapus");
        }
    });

    // ==============================================================
    // TOMBOL TAMBAH DATA DI CLICK
    // ==============================================================
    jQuery("#tambah_data_booking_registrasi").click(function () {

        $('#tanggal_booking').val('');
$('#jam_booking').val('');
$('#no_rkm_medis').val('');
$('#tanggal_periksa').val('');
$('#kd_dokter').val('');
$('#kd_poli').val('');
$('#no_reg').val('');
$('#kd_pj').val('');
$('#limit_reg').val('');
$('#waktu_kunjungan').val('');
$('#status').val('');

        $("#typeact").val("add");
        $("#tanggal_booking").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Booking Registrasi");
        $("#modal_booking_registrasi").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_booking_registrasi").click(function () {

        var search_field_booking_registrasi = $('#search_field_booking_registrasi').val();
        var search_text_booking_registrasi = $('#search_text_booking_registrasi').val();

        $.ajax({
            url: "{?=url(['booking_registrasi','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_booking_registrasi: search_field_booking_registrasi, 
                search_text_booking_registrasi: search_text_booking_registrasi
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_booking_registrasi' class='table display dataTable' style='width:100%'><thead><th>Tanggal Booking</th><th>Jam Booking</th><th>No Rkm Medis</th><th>Tanggal Periksa</th><th>Kd Dokter</th><th>Kd Poli</th><th>No Reg</th><th>Kd Pj</th><th>Limit Reg</th><th>Waktu Kunjungan</th><th>Status</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['tanggal_booking'] + '</td>';
eTable += '<td>' + res[i]['jam_booking'] + '</td>';
eTable += '<td>' + res[i]['no_rkm_medis'] + '</td>';
eTable += '<td>' + res[i]['tanggal_periksa'] + '</td>';
eTable += '<td>' + res[i]['kd_dokter'] + '</td>';
eTable += '<td>' + res[i]['kd_poli'] + '</td>';
eTable += '<td>' + res[i]['no_reg'] + '</td>';
eTable += '<td>' + res[i]['kd_pj'] + '</td>';
eTable += '<td>' + res[i]['limit_reg'] + '</td>';
eTable += '<td>' + res[i]['waktu_kunjungan'] + '</td>';
eTable += '<td>' + res[i]['status'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_booking_registrasi').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_booking_registrasi").modal('show');
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
        doc.text("Tabel Data Booking Registrasi", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_booking_registrasi',
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
                doc.text(`© ${new Date().getFullYear()} {$settings.nama_instansi}.`, data.settings.margin.left, doc.internal.pageSize.height - 10);                
                doc.text(footerStr, data.settings.margin.left + 480, doc.internal.pageSize.height - 10);
           }
        });
        if (typeof doc.putTotalPages === 'function') {
            doc.putTotalPages(totalPagesExp);
        }
        // doc.save('table_data_booking_registrasi.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_booking_registrasi");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data booking_registrasi");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/booking_registrasi/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});