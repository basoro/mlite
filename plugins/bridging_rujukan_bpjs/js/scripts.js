jQuery().ready(function () {
    var var_tbl_bridging_rujukan_bpjs = $('#tbl_bridging_rujukan_bpjs').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['bridging_rujukan_bpjs','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_bridging_rujukan_bpjs = $('#search_field_bridging_rujukan_bpjs').val();
                var search_text_bridging_rujukan_bpjs = $('#search_text_bridging_rujukan_bpjs').val();
                
                data.search_field_bridging_rujukan_bpjs = search_field_bridging_rujukan_bpjs;
                data.search_text_bridging_rujukan_bpjs = search_text_bridging_rujukan_bpjs;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_bridging_rujukan_bpjs').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_bridging_rujukan_bpjs tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'no_sep' },
{ 'data': 'tglRujukan' },
{ 'data': 'tglRencanaKunjungan' },
{ 'data': 'ppkDirujuk' },
{ 'data': 'nm_ppkDirujuk' },
{ 'data': 'jnsPelayanan' },
{ 'data': 'catatan' },
{ 'data': 'diagRujukan' },
{ 'data': 'nama_diagRujukan' },
{ 'data': 'tipeRujukan' },
{ 'data': 'poliRujukan' },
{ 'data': 'nama_poliRujukan' },
{ 'data': 'no_rujukan' },
{ 'data': 'user' }

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
{ 'targets': 13}

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
        selector: '#tbl_bridging_rujukan_bpjs tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_bridging_rujukan_bpjs.rows({ selected: true }).data()[0];
          if (rowData != null) {
var no_sep = rowData['no_sep'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/bridging_rujukan_bpjs/detail/' + no_sep + '?t=' + mlite.token);
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

    $("form[name='form_bridging_rujukan_bpjs']").validate({
        rules: {
no_sep: 'required',
tglRujukan: 'required',
tglRencanaKunjungan: 'required',
ppkDirujuk: 'required',
nm_ppkDirujuk: 'required',
jnsPelayanan: 'required',
catatan: 'required',
diagRujukan: 'required',
nama_diagRujukan: 'required',
tipeRujukan: 'required',
poliRujukan: 'required',
nama_poliRujukan: 'required',
no_rujukan: 'required',
user: 'required'

        },
        messages: {
no_sep:'No Sep tidak boleh kosong!',
tglRujukan:'Tglrujukan tidak boleh kosong!',
tglRencanaKunjungan:'Tglrencanakunjungan tidak boleh kosong!',
ppkDirujuk:'Ppkdirujuk tidak boleh kosong!',
nm_ppkDirujuk:'Nm Ppkdirujuk tidak boleh kosong!',
jnsPelayanan:'Jnspelayanan tidak boleh kosong!',
catatan:'Catatan tidak boleh kosong!',
diagRujukan:'Diagrujukan tidak boleh kosong!',
nama_diagRujukan:'Nama Diagrujukan tidak boleh kosong!',
tipeRujukan:'Tiperujukan tidak boleh kosong!',
poliRujukan:'Polirujukan tidak boleh kosong!',
nama_poliRujukan:'Nama Polirujukan tidak boleh kosong!',
no_rujukan:'No Rujukan tidak boleh kosong!',
user:'User tidak boleh kosong!'

        },
        submitHandler: function (form) {
var no_sep= $('#no_sep').val();
var tglRujukan= $('#tglRujukan').val();
var tglRencanaKunjungan= $('#tglRencanaKunjungan').val();
var ppkDirujuk= $('#ppkDirujuk').val();
var nm_ppkDirujuk= $('#nm_ppkDirujuk').val();
var jnsPelayanan= $('#jnsPelayanan').val();
var catatan= $('#catatan').val();
var diagRujukan= $('#diagRujukan').val();
var nama_diagRujukan= $('#nama_diagRujukan').val();
var tipeRujukan= $('#tipeRujukan').val();
var poliRujukan= $('#poliRujukan').val();
var nama_poliRujukan= $('#nama_poliRujukan').val();
var no_rujukan= $('#no_rujukan').val();
var user= $('#user').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['bridging_rujukan_bpjs','aksi'])?}",
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
                            $("#modal_bridging_rujukan_bpjs").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_bridging_rujukan_bpjs").modal('hide');
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
                    var_tbl_bridging_rujukan_bpjs.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_bridging_rujukan_bpjs.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_bridging_rujukan_bpjs.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_bridging_rujukan_bpjs.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_bridging_rujukan_bpjs').click(function () {
        var_tbl_bridging_rujukan_bpjs.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_bridging_rujukan_bpjs").click(function () {
        var rowData = var_tbl_bridging_rujukan_bpjs.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var no_sep = rowData['no_sep'];
var tglRujukan = rowData['tglRujukan'];
var tglRencanaKunjungan = rowData['tglRencanaKunjungan'];
var ppkDirujuk = rowData['ppkDirujuk'];
var nm_ppkDirujuk = rowData['nm_ppkDirujuk'];
var jnsPelayanan = rowData['jnsPelayanan'];
var catatan = rowData['catatan'];
var diagRujukan = rowData['diagRujukan'];
var nama_diagRujukan = rowData['nama_diagRujukan'];
var tipeRujukan = rowData['tipeRujukan'];
var poliRujukan = rowData['poliRujukan'];
var nama_poliRujukan = rowData['nama_poliRujukan'];
var no_rujukan = rowData['no_rujukan'];
var user = rowData['user'];

            $("#typeact").val("edit");
  
            $('#no_sep').val(no_sep);
$('#tglRujukan').val(tglRujukan);
$('#tglRencanaKunjungan').val(tglRencanaKunjungan);
$('#ppkDirujuk').val(ppkDirujuk);
$('#nm_ppkDirujuk').val(nm_ppkDirujuk);
$('#jnsPelayanan').val(jnsPelayanan);
$('#catatan').val(catatan);
$('#diagRujukan').val(diagRujukan);
$('#nama_diagRujukan').val(nama_diagRujukan);
$('#tipeRujukan').val(tipeRujukan);
$('#poliRujukan').val(poliRujukan);
$('#nama_poliRujukan').val(nama_poliRujukan);
$('#no_rujukan').val(no_rujukan);
$('#user').val(user);

            $("#no_sep").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Bridging Rujukan Bpjs");
            $("#modal_bridging_rujukan_bpjs").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_bridging_rujukan_bpjs").click(function () {
        var rowData = var_tbl_bridging_rujukan_bpjs.rows({ selected: true }).data()[0];


        if (rowData) {
var no_sep = rowData['no_sep'];
            bootbox.confirm('Anda yakin akan menghapus data dengan no_sep="' + no_sep, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['bridging_rujukan_bpjs','aksi'])?}",
                        method: "POST",
                        data: {
                            no_sep: no_sep,
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
                            var_tbl_bridging_rujukan_bpjs.draw();
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
    jQuery("#tambah_data_bridging_rujukan_bpjs").click(function () {

        $('#no_sep').val('');
$('#tglRujukan').val('');
$('#tglRencanaKunjungan').val('');
$('#ppkDirujuk').val('');
$('#nm_ppkDirujuk').val('');
$('#jnsPelayanan').val('');
$('#catatan').val('');
$('#diagRujukan').val('');
$('#nama_diagRujukan').val('');
$('#tipeRujukan').val('');
$('#poliRujukan').val('');
$('#nama_poliRujukan').val('');
$('#no_rujukan').val('');
$('#user').val('');

        $("#typeact").val("add");
        $("#no_sep").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Bridging Rujukan Bpjs");
        $("#modal_bridging_rujukan_bpjs").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_bridging_rujukan_bpjs").click(function () {

        var search_field_bridging_rujukan_bpjs = $('#search_field_bridging_rujukan_bpjs').val();
        var search_text_bridging_rujukan_bpjs = $('#search_text_bridging_rujukan_bpjs').val();

        $.ajax({
            url: "{?=url(['bridging_rujukan_bpjs','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_bridging_rujukan_bpjs: search_field_bridging_rujukan_bpjs, 
                search_text_bridging_rujukan_bpjs: search_text_bridging_rujukan_bpjs
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_bridging_rujukan_bpjs' class='table display dataTable' style='width:100%'><thead><th>No Sep</th><th>Tglrujukan</th><th>Tglrencanakunjungan</th><th>Ppkdirujuk</th><th>Nm Ppkdirujuk</th><th>Jnspelayanan</th><th>Catatan</th><th>Diagrujukan</th><th>Nama Diagrujukan</th><th>Tiperujukan</th><th>Polirujukan</th><th>Nama Polirujukan</th><th>No Rujukan</th><th>User</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['no_sep'] + '</td>';
eTable += '<td>' + res[i]['tglRujukan'] + '</td>';
eTable += '<td>' + res[i]['tglRencanaKunjungan'] + '</td>';
eTable += '<td>' + res[i]['ppkDirujuk'] + '</td>';
eTable += '<td>' + res[i]['nm_ppkDirujuk'] + '</td>';
eTable += '<td>' + res[i]['jnsPelayanan'] + '</td>';
eTable += '<td>' + res[i]['catatan'] + '</td>';
eTable += '<td>' + res[i]['diagRujukan'] + '</td>';
eTable += '<td>' + res[i]['nama_diagRujukan'] + '</td>';
eTable += '<td>' + res[i]['tipeRujukan'] + '</td>';
eTable += '<td>' + res[i]['poliRujukan'] + '</td>';
eTable += '<td>' + res[i]['nama_poliRujukan'] + '</td>';
eTable += '<td>' + res[i]['no_rujukan'] + '</td>';
eTable += '<td>' + res[i]['user'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_bridging_rujukan_bpjs').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_bridging_rujukan_bpjs").modal('show');
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
        doc.text("Tabel Data Bridging Rujukan Bpjs", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_bridging_rujukan_bpjs',
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
        // doc.save('table_data_bridging_rujukan_bpjs.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_bridging_rujukan_bpjs");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data bridging_rujukan_bpjs");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/bridging_rujukan_bpjs/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});