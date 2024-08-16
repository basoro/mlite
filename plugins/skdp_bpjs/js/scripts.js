jQuery().ready(function () {
    var var_tbl_skdp_bpjs = $('#tbl_skdp_bpjs').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['skdp_bpjs','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_skdp_bpjs = $('#search_field_skdp_bpjs').val();
                var search_text_skdp_bpjs = $('#search_text_skdp_bpjs').val();
                
                data.search_field_skdp_bpjs = search_field_skdp_bpjs;
                data.search_text_skdp_bpjs = search_text_skdp_bpjs;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_skdp_bpjs').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_skdp_bpjs tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'tahun' },
{ 'data': 'no_rkm_medis' },
{ 'data': 'diagnosa' },
{ 'data': 'terapi' },
{ 'data': 'alasan1' },
{ 'data': 'alasan2' },
{ 'data': 'rtl1' },
{ 'data': 'rtl2' },
{ 'data': 'tanggal_datang' },
{ 'data': 'tanggal_rujukan' },
{ 'data': 'no_antrian' },
{ 'data': 'kd_dokter' },
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
{ 'targets': 10},
{ 'targets': 11},
{ 'targets': 12}

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
        selector: '#tbl_skdp_bpjs tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_skdp_bpjs.rows({ selected: true }).data()[0];
          if (rowData != null) {
var tahun = rowData['tahun'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/skdp_bpjs/detail/' + tahun + '?t=' + mlite.token);
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

    $("form[name='form_skdp_bpjs']").validate({
        rules: {
tahun: 'required',
no_rkm_medis: 'required',
diagnosa: 'required',
terapi: 'required',
alasan1: 'required',
alasan2: 'required',
rtl1: 'required',
rtl2: 'required',
tanggal_datang: 'required',
tanggal_rujukan: 'required',
no_antrian: 'required',
kd_dokter: 'required',
status: 'required'

        },
        messages: {
tahun:'Tahun tidak boleh kosong!',
no_rkm_medis:'No Rkm Medis tidak boleh kosong!',
diagnosa:'Diagnosa tidak boleh kosong!',
terapi:'Terapi tidak boleh kosong!',
alasan1:'Alasan1 tidak boleh kosong!',
alasan2:'Alasan2 tidak boleh kosong!',
rtl1:'Rtl1 tidak boleh kosong!',
rtl2:'Rtl2 tidak boleh kosong!',
tanggal_datang:'Tanggal Datang tidak boleh kosong!',
tanggal_rujukan:'Tanggal Rujukan tidak boleh kosong!',
no_antrian:'No Antrian tidak boleh kosong!',
kd_dokter:'Kd Dokter tidak boleh kosong!',
status:'Status tidak boleh kosong!'

        },
        submitHandler: function (form) {
var tahun= $('#tahun').val();
var no_rkm_medis= $('#no_rkm_medis').val();
var diagnosa= $('#diagnosa').val();
var terapi= $('#terapi').val();
var alasan1= $('#alasan1').val();
var alasan2= $('#alasan2').val();
var rtl1= $('#rtl1').val();
var rtl2= $('#rtl2').val();
var tanggal_datang= $('#tanggal_datang').val();
var tanggal_rujukan= $('#tanggal_rujukan').val();
var no_antrian= $('#no_antrian').val();
var kd_dokter= $('#kd_dokter').val();
var status= $('#status').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['skdp_bpjs','aksi'])?}",
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
                            $("#modal_skdp_bpjs").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_skdp_bpjs").modal('hide');
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
                    var_tbl_skdp_bpjs.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_skdp_bpjs.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_skdp_bpjs.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_skdp_bpjs.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_skdp_bpjs').click(function () {
        var_tbl_skdp_bpjs.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_skdp_bpjs").click(function () {
        var rowData = var_tbl_skdp_bpjs.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var tahun = rowData['tahun'];
var no_rkm_medis = rowData['no_rkm_medis'];
var diagnosa = rowData['diagnosa'];
var terapi = rowData['terapi'];
var alasan1 = rowData['alasan1'];
var alasan2 = rowData['alasan2'];
var rtl1 = rowData['rtl1'];
var rtl2 = rowData['rtl2'];
var tanggal_datang = rowData['tanggal_datang'];
var tanggal_rujukan = rowData['tanggal_rujukan'];
var no_antrian = rowData['no_antrian'];
var kd_dokter = rowData['kd_dokter'];
var status = rowData['status'];

            $("#typeact").val("edit");
  
            $('#tahun').val(tahun);
$('#no_rkm_medis').val(no_rkm_medis);
$('#diagnosa').val(diagnosa);
$('#terapi').val(terapi);
$('#alasan1').val(alasan1);
$('#alasan2').val(alasan2);
$('#rtl1').val(rtl1);
$('#rtl2').val(rtl2);
$('#tanggal_datang').val(tanggal_datang);
$('#tanggal_rujukan').val(tanggal_rujukan);
$('#no_antrian').val(no_antrian);
$('#kd_dokter').val(kd_dokter);
$('#status').val(status);

            $("#tahun").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Skdp Bpjs");
            $("#modal_skdp_bpjs").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_skdp_bpjs").click(function () {
        var rowData = var_tbl_skdp_bpjs.rows({ selected: true }).data()[0];


        if (rowData) {
var tahun = rowData['tahun'];
            bootbox.confirm('Anda yakin akan menghapus data dengan tahun="' + tahun, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['skdp_bpjs','aksi'])?}",
                        method: "POST",
                        data: {
                            tahun: tahun,
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
                            var_tbl_skdp_bpjs.draw();
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
    jQuery("#tambah_data_skdp_bpjs").click(function () {

        $('#tahun').val('');
$('#no_rkm_medis').val('');
$('#diagnosa').val('');
$('#terapi').val('');
$('#alasan1').val('');
$('#alasan2').val('');
$('#rtl1').val('');
$('#rtl2').val('');
$('#tanggal_datang').val('');
$('#tanggal_rujukan').val('');
$('#no_antrian').val('');
$('#kd_dokter').val('');
$('#status').val('');

        $("#typeact").val("add");
        $("#tahun").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Skdp Bpjs");
        $("#modal_skdp_bpjs").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_skdp_bpjs").click(function () {

        var search_field_skdp_bpjs = $('#search_field_skdp_bpjs').val();
        var search_text_skdp_bpjs = $('#search_text_skdp_bpjs').val();

        $.ajax({
            url: "{?=url(['skdp_bpjs','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_skdp_bpjs: search_field_skdp_bpjs, 
                search_text_skdp_bpjs: search_text_skdp_bpjs
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_skdp_bpjs' class='table display dataTable' style='width:100%'><thead><th>Tahun</th><th>No Rkm Medis</th><th>Diagnosa</th><th>Terapi</th><th>Alasan1</th><th>Alasan2</th><th>Rtl1</th><th>Rtl2</th><th>Tanggal Datang</th><th>Tanggal Rujukan</th><th>No Antrian</th><th>Kd Dokter</th><th>Status</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['tahun'] + '</td>';
eTable += '<td>' + res[i]['no_rkm_medis'] + '</td>';
eTable += '<td>' + res[i]['diagnosa'] + '</td>';
eTable += '<td>' + res[i]['terapi'] + '</td>';
eTable += '<td>' + res[i]['alasan1'] + '</td>';
eTable += '<td>' + res[i]['alasan2'] + '</td>';
eTable += '<td>' + res[i]['rtl1'] + '</td>';
eTable += '<td>' + res[i]['rtl2'] + '</td>';
eTable += '<td>' + res[i]['tanggal_datang'] + '</td>';
eTable += '<td>' + res[i]['tanggal_rujukan'] + '</td>';
eTable += '<td>' + res[i]['no_antrian'] + '</td>';
eTable += '<td>' + res[i]['kd_dokter'] + '</td>';
eTable += '<td>' + res[i]['status'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_skdp_bpjs').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_skdp_bpjs").modal('show');
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
        doc.text("Tabel Data Skdp Bpjs", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_skdp_bpjs',
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
        // doc.save('table_data_skdp_bpjs.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_skdp_bpjs");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data skdp_bpjs");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/skdp_bpjs/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});