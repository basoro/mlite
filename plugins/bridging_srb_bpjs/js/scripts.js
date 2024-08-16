jQuery().ready(function () {
    var var_tbl_bridging_srb_bpjs = $('#tbl_bridging_srb_bpjs').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['bridging_srb_bpjs','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_bridging_srb_bpjs = $('#search_field_bridging_srb_bpjs').val();
                var search_text_bridging_srb_bpjs = $('#search_text_bridging_srb_bpjs').val();
                
                data.search_field_bridging_srb_bpjs = search_field_bridging_srb_bpjs;
                data.search_text_bridging_srb_bpjs = search_text_bridging_srb_bpjs;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_bridging_srb_bpjs').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_bridging_srb_bpjs tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'no_sep' },
{ 'data': 'no_srb' },
{ 'data': 'tgl_srb' },
{ 'data': 'alamat' },
{ 'data': 'email' },
{ 'data': 'kodeprogram' },
{ 'data': 'namaprogram' },
{ 'data': 'kodedpjp' },
{ 'data': 'nmdpjp' },
{ 'data': 'user' },
{ 'data': 'keterangan' },
{ 'data': 'saran' }

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
{ 'targets': 11}

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
        selector: '#tbl_bridging_srb_bpjs tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_bridging_srb_bpjs.rows({ selected: true }).data()[0];
          if (rowData != null) {
var no_sep = rowData['no_sep'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/bridging_srb_bpjs/detail/' + no_sep + '?t=' + mlite.token);
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

    $("form[name='form_bridging_srb_bpjs']").validate({
        rules: {
no_sep: 'required',
no_srb: 'required',
tgl_srb: 'required',
alamat: 'required',
email: 'required',
kodeprogram: 'required',
namaprogram: 'required',
kodedpjp: 'required',
nmdpjp: 'required',
user: 'required',
keterangan: 'required',
saran: 'required'

        },
        messages: {
no_sep:'No Sep tidak boleh kosong!',
no_srb:'No Srb tidak boleh kosong!',
tgl_srb:'Tgl Srb tidak boleh kosong!',
alamat:'Alamat tidak boleh kosong!',
email:'Email tidak boleh kosong!',
kodeprogram:'Kodeprogram tidak boleh kosong!',
namaprogram:'Namaprogram tidak boleh kosong!',
kodedpjp:'Kodedpjp tidak boleh kosong!',
nmdpjp:'Nmdpjp tidak boleh kosong!',
user:'User tidak boleh kosong!',
keterangan:'Keterangan tidak boleh kosong!',
saran:'Saran tidak boleh kosong!'

        },
        submitHandler: function (form) {
var no_sep= $('#no_sep').val();
var no_srb= $('#no_srb').val();
var tgl_srb= $('#tgl_srb').val();
var alamat= $('#alamat').val();
var email= $('#email').val();
var kodeprogram= $('#kodeprogram').val();
var namaprogram= $('#namaprogram').val();
var kodedpjp= $('#kodedpjp').val();
var nmdpjp= $('#nmdpjp').val();
var user= $('#user').val();
var keterangan= $('#keterangan').val();
var saran= $('#saran').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['bridging_srb_bpjs','aksi'])?}",
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
                            $("#modal_bridging_srb_bpjs").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_bridging_srb_bpjs").modal('hide');
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
                    var_tbl_bridging_srb_bpjs.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_bridging_srb_bpjs.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_bridging_srb_bpjs.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_bridging_srb_bpjs.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_bridging_srb_bpjs').click(function () {
        var_tbl_bridging_srb_bpjs.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_bridging_srb_bpjs").click(function () {
        var rowData = var_tbl_bridging_srb_bpjs.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var no_sep = rowData['no_sep'];
var no_srb = rowData['no_srb'];
var tgl_srb = rowData['tgl_srb'];
var alamat = rowData['alamat'];
var email = rowData['email'];
var kodeprogram = rowData['kodeprogram'];
var namaprogram = rowData['namaprogram'];
var kodedpjp = rowData['kodedpjp'];
var nmdpjp = rowData['nmdpjp'];
var user = rowData['user'];
var keterangan = rowData['keterangan'];
var saran = rowData['saran'];

            $("#typeact").val("edit");
  
            $('#no_sep').val(no_sep);
$('#no_srb').val(no_srb);
$('#tgl_srb').val(tgl_srb);
$('#alamat').val(alamat);
$('#email').val(email);
$('#kodeprogram').val(kodeprogram);
$('#namaprogram').val(namaprogram);
$('#kodedpjp').val(kodedpjp);
$('#nmdpjp').val(nmdpjp);
$('#user').val(user);
$('#keterangan').val(keterangan);
$('#saran').val(saran);

            $("#no_sep").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Bridging Srb Bpjs");
            $("#modal_bridging_srb_bpjs").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_bridging_srb_bpjs").click(function () {
        var rowData = var_tbl_bridging_srb_bpjs.rows({ selected: true }).data()[0];


        if (rowData) {
var no_sep = rowData['no_sep'];
            bootbox.confirm('Anda yakin akan menghapus data dengan no_sep="' + no_sep, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['bridging_srb_bpjs','aksi'])?}",
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
                            var_tbl_bridging_srb_bpjs.draw();
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
    jQuery("#tambah_data_bridging_srb_bpjs").click(function () {

        $('#no_sep').val('');
$('#no_srb').val('');
$('#tgl_srb').val('');
$('#alamat').val('');
$('#email').val('');
$('#kodeprogram').val('');
$('#namaprogram').val('');
$('#kodedpjp').val('');
$('#nmdpjp').val('');
$('#user').val('');
$('#keterangan').val('');
$('#saran').val('');

        $("#typeact").val("add");
        $("#no_sep").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Bridging Srb Bpjs");
        $("#modal_bridging_srb_bpjs").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_bridging_srb_bpjs").click(function () {

        var search_field_bridging_srb_bpjs = $('#search_field_bridging_srb_bpjs').val();
        var search_text_bridging_srb_bpjs = $('#search_text_bridging_srb_bpjs').val();

        $.ajax({
            url: "{?=url(['bridging_srb_bpjs','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_bridging_srb_bpjs: search_field_bridging_srb_bpjs, 
                search_text_bridging_srb_bpjs: search_text_bridging_srb_bpjs
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_bridging_srb_bpjs' class='table display dataTable' style='width:100%'><thead><th>No Sep</th><th>No Srb</th><th>Tgl Srb</th><th>Alamat</th><th>Email</th><th>Kodeprogram</th><th>Namaprogram</th><th>Kodedpjp</th><th>Nmdpjp</th><th>User</th><th>Keterangan</th><th>Saran</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['no_sep'] + '</td>';
eTable += '<td>' + res[i]['no_srb'] + '</td>';
eTable += '<td>' + res[i]['tgl_srb'] + '</td>';
eTable += '<td>' + res[i]['alamat'] + '</td>';
eTable += '<td>' + res[i]['email'] + '</td>';
eTable += '<td>' + res[i]['kodeprogram'] + '</td>';
eTable += '<td>' + res[i]['namaprogram'] + '</td>';
eTable += '<td>' + res[i]['kodedpjp'] + '</td>';
eTable += '<td>' + res[i]['nmdpjp'] + '</td>';
eTable += '<td>' + res[i]['user'] + '</td>';
eTable += '<td>' + res[i]['keterangan'] + '</td>';
eTable += '<td>' + res[i]['saran'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_bridging_srb_bpjs').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_bridging_srb_bpjs").modal('show');
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
        doc.text("Tabel Data Bridging Srb Bpjs", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_bridging_srb_bpjs',
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
        // doc.save('table_data_bridging_srb_bpjs.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_bridging_srb_bpjs");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data bridging_srb_bpjs");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/bridging_srb_bpjs/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});