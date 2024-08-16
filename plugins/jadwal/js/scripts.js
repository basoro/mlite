jQuery().ready(function () {
    var var_tbl_jadwal = $('#tbl_jadwal').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['jadwal','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_jadwal = $('#search_field_jadwal').val();
                var search_text_jadwal = $('#search_text_jadwal').val();
                
                data.search_field_jadwal = search_field_jadwal;
                data.search_text_jadwal = search_text_jadwal;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_jadwal').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_jadwal tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'kd_dokter' },
{ 'data': 'hari_kerja' },
{ 'data': 'jam_mulai' },
{ 'data': 'jam_selesai' },
{ 'data': 'kd_poli' },
{ 'data': 'kuota' }

        ],
        "columnDefs": [
{ 'targets': 0},
{ 'targets': 1},
{ 'targets': 2},
{ 'targets': 3},
{ 'targets': 4},
{ 'targets': 5}

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
        selector: '#tbl_jadwal tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_jadwal.rows({ selected: true }).data()[0];
          if (rowData != null) {
var kd_dokter = rowData['kd_dokter'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/jadwal/detail/' + kd_dokter + '?t=' + mlite.token);
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

    $("form[name='form_jadwal']").validate({
        rules: {
kd_dokter: 'required',
hari_kerja: 'required',
jam_mulai: 'required',
jam_selesai: 'required',
kd_poli: 'required',
kuota: 'required'

        },
        messages: {
kd_dokter:'Kd Dokter tidak boleh kosong!',
hari_kerja:'Hari Kerja tidak boleh kosong!',
jam_mulai:'Jam Mulai tidak boleh kosong!',
jam_selesai:'Jam Selesai tidak boleh kosong!',
kd_poli:'Kd Poli tidak boleh kosong!',
kuota:'Kuota tidak boleh kosong!'

        },
        submitHandler: function (form) {
var kd_dokter= $('#kd_dokter').val();
var hari_kerja= $('#hari_kerja').val();
var jam_mulai= $('#jam_mulai').val();
var jam_selesai= $('#jam_selesai').val();
var kd_poli= $('#kd_poli').val();
var kuota= $('#kuota').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['jadwal','aksi'])?}",
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
                            $("#modal_jadwal").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_jadwal").modal('hide');
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
                    var_tbl_jadwal.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_jadwal.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_jadwal.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_jadwal.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_jadwal').click(function () {
        var_tbl_jadwal.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_jadwal").click(function () {
        var rowData = var_tbl_jadwal.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var kd_dokter = rowData['kd_dokter'];
var hari_kerja = rowData['hari_kerja'];
var jam_mulai = rowData['jam_mulai'];
var jam_selesai = rowData['jam_selesai'];
var kd_poli = rowData['kd_poli'];
var kuota = rowData['kuota'];

            $("#typeact").val("edit");
  
            $('#kd_dokter').val(kd_dokter);
$('#hari_kerja').val(hari_kerja);
$('#jam_mulai').val(jam_mulai);
$('#jam_selesai').val(jam_selesai);
$('#kd_poli').val(kd_poli);
$('#kuota').val(kuota);

            $("#kd_dokter").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Jadwal");
            $("#modal_jadwal").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_jadwal").click(function () {
        var rowData = var_tbl_jadwal.rows({ selected: true }).data()[0];


        if (rowData) {
var kd_dokter = rowData['kd_dokter'];
            bootbox.confirm('Anda yakin akan menghapus data dengan kd_dokter="' + kd_dokter, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['jadwal','aksi'])?}",
                        method: "POST",
                        data: {
                            kd_dokter: kd_dokter,
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
                            var_tbl_jadwal.draw();
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
    jQuery("#tambah_data_jadwal").click(function () {

        $('#kd_dokter').val('');
$('#hari_kerja').val('');
$('#jam_mulai').val('');
$('#jam_selesai').val('');
$('#kd_poli').val('');
$('#kuota').val('');

        $("#typeact").val("add");
        $("#kd_dokter").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Jadwal");
        $("#modal_jadwal").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_jadwal").click(function () {

        var search_field_jadwal = $('#search_field_jadwal').val();
        var search_text_jadwal = $('#search_text_jadwal').val();

        $.ajax({
            url: "{?=url(['jadwal','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_jadwal: search_field_jadwal, 
                search_text_jadwal: search_text_jadwal
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_jadwal' class='table display dataTable' style='width:100%'><thead><th>Kd Dokter</th><th>Hari Kerja</th><th>Jam Mulai</th><th>Jam Selesai</th><th>Kd Poli</th><th>Kuota</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['kd_dokter'] + '</td>';
eTable += '<td>' + res[i]['hari_kerja'] + '</td>';
eTable += '<td>' + res[i]['jam_mulai'] + '</td>';
eTable += '<td>' + res[i]['jam_selesai'] + '</td>';
eTable += '<td>' + res[i]['kd_poli'] + '</td>';
eTable += '<td>' + res[i]['kuota'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_jadwal').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_jadwal").modal('show');
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
        doc.text("Tabel Data Jadwal", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_jadwal',
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
        // doc.save('table_data_jadwal.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_jadwal");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data jadwal");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/jadwal/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});