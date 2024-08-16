jQuery().ready(function () {
    var var_tbl_utd_donor = $('#tbl_utd_donor').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['utd_donor','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_utd_donor = $('#search_field_utd_donor').val();
                var search_text_utd_donor = $('#search_text_utd_donor').val();
                
                data.search_field_utd_donor = search_field_utd_donor;
                data.search_text_utd_donor = search_text_utd_donor;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_utd_donor').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_utd_donor tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'no_donor' },
{ 'data': 'no_pendonor' },
{ 'data': 'tanggal' },
{ 'data': 'dinas' },
{ 'data': 'tensi' },
{ 'data': 'no_bag' },
{ 'data': 'jenis_bag' },
{ 'data': 'jenis_donor' },
{ 'data': 'tempat_aftap' },
{ 'data': 'petugas_aftap' },
{ 'data': 'hbsag' },
{ 'data': 'hcv' },
{ 'data': 'hiv' },
{ 'data': 'spilis' },
{ 'data': 'malaria' },
{ 'data': 'petugas_u_saring' },
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
{ 'targets': 12},
{ 'targets': 13},
{ 'targets': 14},
{ 'targets': 15},
{ 'targets': 16}

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
        selector: '#tbl_utd_donor tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_utd_donor.rows({ selected: true }).data()[0];
          if (rowData != null) {
var no_donor = rowData['no_donor'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/utd_donor/detail/' + no_donor + '?t=' + mlite.token);
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

    $("form[name='form_utd_donor']").validate({
        rules: {
no_donor: 'required',
no_pendonor: 'required',
tanggal: 'required',
dinas: 'required',
tensi: 'required',
no_bag: 'required',
jenis_bag: 'required',
jenis_donor: 'required',
tempat_aftap: 'required',
petugas_aftap: 'required',
hbsag: 'required',
hcv: 'required',
hiv: 'required',
spilis: 'required',
malaria: 'required',
petugas_u_saring: 'required',
status: 'required'

        },
        messages: {
no_donor:'No Donor tidak boleh kosong!',
no_pendonor:'No Pendonor tidak boleh kosong!',
tanggal:'Tanggal tidak boleh kosong!',
dinas:'Dinas tidak boleh kosong!',
tensi:'Tensi tidak boleh kosong!',
no_bag:'No Bag tidak boleh kosong!',
jenis_bag:'Jenis Bag tidak boleh kosong!',
jenis_donor:'Jenis Donor tidak boleh kosong!',
tempat_aftap:'Tempat Aftap tidak boleh kosong!',
petugas_aftap:'Petugas Aftap tidak boleh kosong!',
hbsag:'Hbsag tidak boleh kosong!',
hcv:'Hcv tidak boleh kosong!',
hiv:'Hiv tidak boleh kosong!',
spilis:'Spilis tidak boleh kosong!',
malaria:'Malaria tidak boleh kosong!',
petugas_u_saring:'Petugas U Saring tidak boleh kosong!',
status:'Status tidak boleh kosong!'

        },
        submitHandler: function (form) {
var no_donor= $('#no_donor').val();
var no_pendonor= $('#no_pendonor').val();
var tanggal= $('#tanggal').val();
var dinas= $('#dinas').val();
var tensi= $('#tensi').val();
var no_bag= $('#no_bag').val();
var jenis_bag= $('#jenis_bag').val();
var jenis_donor= $('#jenis_donor').val();
var tempat_aftap= $('#tempat_aftap').val();
var petugas_aftap= $('#petugas_aftap').val();
var hbsag= $('#hbsag').val();
var hcv= $('#hcv').val();
var hiv= $('#hiv').val();
var spilis= $('#spilis').val();
var malaria= $('#malaria').val();
var petugas_u_saring= $('#petugas_u_saring').val();
var status= $('#status').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['utd_donor','aksi'])?}",
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
                            $("#modal_utd_donor").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_utd_donor").modal('hide');
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
                    var_tbl_utd_donor.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_utd_donor.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_utd_donor.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_utd_donor.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_utd_donor').click(function () {
        var_tbl_utd_donor.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_utd_donor").click(function () {
        var rowData = var_tbl_utd_donor.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var no_donor = rowData['no_donor'];
var no_pendonor = rowData['no_pendonor'];
var tanggal = rowData['tanggal'];
var dinas = rowData['dinas'];
var tensi = rowData['tensi'];
var no_bag = rowData['no_bag'];
var jenis_bag = rowData['jenis_bag'];
var jenis_donor = rowData['jenis_donor'];
var tempat_aftap = rowData['tempat_aftap'];
var petugas_aftap = rowData['petugas_aftap'];
var hbsag = rowData['hbsag'];
var hcv = rowData['hcv'];
var hiv = rowData['hiv'];
var spilis = rowData['spilis'];
var malaria = rowData['malaria'];
var petugas_u_saring = rowData['petugas_u_saring'];
var status = rowData['status'];

            $("#typeact").val("edit");
  
            $('#no_donor').val(no_donor);
$('#no_pendonor').val(no_pendonor);
$('#tanggal').val(tanggal);
$('#dinas').val(dinas);
$('#tensi').val(tensi);
$('#no_bag').val(no_bag);
$('#jenis_bag').val(jenis_bag);
$('#jenis_donor').val(jenis_donor);
$('#tempat_aftap').val(tempat_aftap);
$('#petugas_aftap').val(petugas_aftap);
$('#hbsag').val(hbsag);
$('#hcv').val(hcv);
$('#hiv').val(hiv);
$('#spilis').val(spilis);
$('#malaria').val(malaria);
$('#petugas_u_saring').val(petugas_u_saring);
$('#status').val(status);

            $("#no_donor").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Utd Donor");
            $("#modal_utd_donor").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_utd_donor").click(function () {
        var rowData = var_tbl_utd_donor.rows({ selected: true }).data()[0];


        if (rowData) {
var no_donor = rowData['no_donor'];
            bootbox.confirm('Anda yakin akan menghapus data dengan no_donor="' + no_donor, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['utd_donor','aksi'])?}",
                        method: "POST",
                        data: {
                            no_donor: no_donor,
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
                            var_tbl_utd_donor.draw();
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
    jQuery("#tambah_data_utd_donor").click(function () {

        $('#no_donor').val('');
$('#no_pendonor').val('');
$('#tanggal').val('');
$('#dinas').val('');
$('#tensi').val('');
$('#no_bag').val('');
$('#jenis_bag').val('');
$('#jenis_donor').val('');
$('#tempat_aftap').val('');
$('#petugas_aftap').val('');
$('#hbsag').val('');
$('#hcv').val('');
$('#hiv').val('');
$('#spilis').val('');
$('#malaria').val('');
$('#petugas_u_saring').val('');
$('#status').val('');

        $("#typeact").val("add");
        $("#no_donor").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Utd Donor");
        $("#modal_utd_donor").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_utd_donor").click(function () {

        var search_field_utd_donor = $('#search_field_utd_donor').val();
        var search_text_utd_donor = $('#search_text_utd_donor').val();

        $.ajax({
            url: "{?=url(['utd_donor','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_utd_donor: search_field_utd_donor, 
                search_text_utd_donor: search_text_utd_donor
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_utd_donor' class='table display dataTable' style='width:100%'><thead><th>No Donor</th><th>No Pendonor</th><th>Tanggal</th><th>Dinas</th><th>Tensi</th><th>No Bag</th><th>Jenis Bag</th><th>Jenis Donor</th><th>Tempat Aftap</th><th>Petugas Aftap</th><th>Hbsag</th><th>Hcv</th><th>Hiv</th><th>Spilis</th><th>Malaria</th><th>Petugas U Saring</th><th>Status</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['no_donor'] + '</td>';
eTable += '<td>' + res[i]['no_pendonor'] + '</td>';
eTable += '<td>' + res[i]['tanggal'] + '</td>';
eTable += '<td>' + res[i]['dinas'] + '</td>';
eTable += '<td>' + res[i]['tensi'] + '</td>';
eTable += '<td>' + res[i]['no_bag'] + '</td>';
eTable += '<td>' + res[i]['jenis_bag'] + '</td>';
eTable += '<td>' + res[i]['jenis_donor'] + '</td>';
eTable += '<td>' + res[i]['tempat_aftap'] + '</td>';
eTable += '<td>' + res[i]['petugas_aftap'] + '</td>';
eTable += '<td>' + res[i]['hbsag'] + '</td>';
eTable += '<td>' + res[i]['hcv'] + '</td>';
eTable += '<td>' + res[i]['hiv'] + '</td>';
eTable += '<td>' + res[i]['spilis'] + '</td>';
eTable += '<td>' + res[i]['malaria'] + '</td>';
eTable += '<td>' + res[i]['petugas_u_saring'] + '</td>';
eTable += '<td>' + res[i]['status'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_utd_donor').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_utd_donor").modal('show');
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
        doc.text("Tabel Data Utd Donor", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_utd_donor',
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
        // doc.save('table_data_utd_donor.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_utd_donor");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data utd_donor");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/utd_donor/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});