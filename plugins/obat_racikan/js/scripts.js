jQuery().ready(function () {
    var var_tbl_obat_racikan = $('#tbl_obat_racikan').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['obat_racikan','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_obat_racikan = $('#search_field_obat_racikan').val();
                var search_text_obat_racikan = $('#search_text_obat_racikan').val();
                
                data.search_field_obat_racikan = search_field_obat_racikan;
                data.search_text_obat_racikan = search_text_obat_racikan;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_obat_racikan').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_obat_racikan tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'tgl_perawatan' },
{ 'data': 'jam' },
{ 'data': 'no_rawat' },
{ 'data': 'no_racik' },
{ 'data': 'nama_racik' },
{ 'data': 'kd_racik' },
{ 'data': 'jml_dr' },
{ 'data': 'aturan_pakai' },
{ 'data': 'keterangan' }

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
{ 'targets': 8}

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
        selector: '#tbl_obat_racikan tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_obat_racikan.rows({ selected: true }).data()[0];
          if (rowData != null) {
var tgl_perawatan = rowData['tgl_perawatan'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/obat_racikan/detail/' + tgl_perawatan + '?t=' + mlite.token);
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

    $("form[name='form_obat_racikan']").validate({
        rules: {
tgl_perawatan: 'required',
jam: 'required',
no_rawat: 'required',
no_racik: 'required',
nama_racik: 'required',
kd_racik: 'required',
jml_dr: 'required',
aturan_pakai: 'required',
keterangan: 'required'

        },
        messages: {
tgl_perawatan:'Tgl Perawatan tidak boleh kosong!',
jam:'Jam tidak boleh kosong!',
no_rawat:'No Rawat tidak boleh kosong!',
no_racik:'No Racik tidak boleh kosong!',
nama_racik:'Nama Racik tidak boleh kosong!',
kd_racik:'Kd Racik tidak boleh kosong!',
jml_dr:'Jml Dr tidak boleh kosong!',
aturan_pakai:'Aturan Pakai tidak boleh kosong!',
keterangan:'Keterangan tidak boleh kosong!'

        },
        submitHandler: function (form) {
var tgl_perawatan= $('#tgl_perawatan').val();
var jam= $('#jam').val();
var no_rawat= $('#no_rawat').val();
var no_racik= $('#no_racik').val();
var nama_racik= $('#nama_racik').val();
var kd_racik= $('#kd_racik').val();
var jml_dr= $('#jml_dr').val();
var aturan_pakai= $('#aturan_pakai').val();
var keterangan= $('#keterangan').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['obat_racikan','aksi'])?}",
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
                            $("#modal_obat_racikan").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_obat_racikan").modal('hide');
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
                    var_tbl_obat_racikan.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_obat_racikan.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_obat_racikan.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_obat_racikan.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_obat_racikan').click(function () {
        var_tbl_obat_racikan.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_obat_racikan").click(function () {
        var rowData = var_tbl_obat_racikan.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var tgl_perawatan = rowData['tgl_perawatan'];
var jam = rowData['jam'];
var no_rawat = rowData['no_rawat'];
var no_racik = rowData['no_racik'];
var nama_racik = rowData['nama_racik'];
var kd_racik = rowData['kd_racik'];
var jml_dr = rowData['jml_dr'];
var aturan_pakai = rowData['aturan_pakai'];
var keterangan = rowData['keterangan'];

            $("#typeact").val("edit");
  
            $('#tgl_perawatan').val(tgl_perawatan);
$('#jam').val(jam);
$('#no_rawat').val(no_rawat);
$('#no_racik').val(no_racik);
$('#nama_racik').val(nama_racik);
$('#kd_racik').val(kd_racik);
$('#jml_dr').val(jml_dr);
$('#aturan_pakai').val(aturan_pakai);
$('#keterangan').val(keterangan);

            $("#tgl_perawatan").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Obat Racikan");
            $("#modal_obat_racikan").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_obat_racikan").click(function () {
        var rowData = var_tbl_obat_racikan.rows({ selected: true }).data()[0];


        if (rowData) {
var tgl_perawatan = rowData['tgl_perawatan'];
            bootbox.confirm('Anda yakin akan menghapus data dengan tgl_perawatan="' + tgl_perawatan, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['obat_racikan','aksi'])?}",
                        method: "POST",
                        data: {
                            tgl_perawatan: tgl_perawatan,
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
                            var_tbl_obat_racikan.draw();
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
    jQuery("#tambah_data_obat_racikan").click(function () {

        $('#tgl_perawatan').val('');
$('#jam').val('');
$('#no_rawat').val('');
$('#no_racik').val('');
$('#nama_racik').val('');
$('#kd_racik').val('');
$('#jml_dr').val('');
$('#aturan_pakai').val('');
$('#keterangan').val('');

        $("#typeact").val("add");
        $("#tgl_perawatan").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Obat Racikan");
        $("#modal_obat_racikan").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_obat_racikan").click(function () {

        var search_field_obat_racikan = $('#search_field_obat_racikan').val();
        var search_text_obat_racikan = $('#search_text_obat_racikan').val();

        $.ajax({
            url: "{?=url(['obat_racikan','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_obat_racikan: search_field_obat_racikan, 
                search_text_obat_racikan: search_text_obat_racikan
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_obat_racikan' class='table display dataTable' style='width:100%'><thead><th>Tgl Perawatan</th><th>Jam</th><th>No Rawat</th><th>No Racik</th><th>Nama Racik</th><th>Kd Racik</th><th>Jml Dr</th><th>Aturan Pakai</th><th>Keterangan</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['tgl_perawatan'] + '</td>';
eTable += '<td>' + res[i]['jam'] + '</td>';
eTable += '<td>' + res[i]['no_rawat'] + '</td>';
eTable += '<td>' + res[i]['no_racik'] + '</td>';
eTable += '<td>' + res[i]['nama_racik'] + '</td>';
eTable += '<td>' + res[i]['kd_racik'] + '</td>';
eTable += '<td>' + res[i]['jml_dr'] + '</td>';
eTable += '<td>' + res[i]['aturan_pakai'] + '</td>';
eTable += '<td>' + res[i]['keterangan'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_obat_racikan').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_obat_racikan").modal('show');
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
        doc.text("Tabel Data Obat Racikan", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_obat_racikan',
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
        // doc.save('table_data_obat_racikan.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_obat_racikan");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data obat_racikan");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/obat_racikan/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});