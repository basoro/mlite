jQuery().ready(function () {
    var var_tbl_riwayat_barang_medis = $('#tbl_riwayat_barang_medis').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['riwayat_barang_medis','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_riwayat_barang_medis = $('#search_field_riwayat_barang_medis').val();
                var search_text_riwayat_barang_medis = $('#search_text_riwayat_barang_medis').val();
                
                data.search_field_riwayat_barang_medis = search_field_riwayat_barang_medis;
                data.search_text_riwayat_barang_medis = search_text_riwayat_barang_medis;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_riwayat_barang_medis').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_riwayat_barang_medis tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'kode_brng' },
{ 'data': 'stok_awal' },
{ 'data': 'masuk' },
{ 'data': 'keluar' },
{ 'data': 'stok_akhir' },
{ 'data': 'posisi' },
{ 'data': 'tanggal' },
{ 'data': 'jam' },
{ 'data': 'petugas' },
{ 'data': 'kd_bangsal' },
{ 'data': 'status' },
{ 'data': 'no_batch' },
{ 'data': 'no_faktur' },
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
        selector: '#tbl_riwayat_barang_medis tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_riwayat_barang_medis.rows({ selected: true }).data()[0];
          if (rowData != null) {
var kode_brng = rowData['kode_brng'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/riwayat_barang_medis/detail/' + kode_brng + '?t=' + mlite.token);
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

    $("form[name='form_riwayat_barang_medis']").validate({
        rules: {
kode_brng: 'required',
stok_awal: 'required',
masuk: 'required',
keluar: 'required',
stok_akhir: 'required',
posisi: 'required',
tanggal: 'required',
jam: 'required',
petugas: 'required',
kd_bangsal: 'required',
status: 'required',
no_batch: 'required',
no_faktur: 'required',
keterangan: 'required'

        },
        messages: {
kode_brng:'Kode Brng tidak boleh kosong!',
stok_awal:'Stok Awal tidak boleh kosong!',
masuk:'Masuk tidak boleh kosong!',
keluar:'Keluar tidak boleh kosong!',
stok_akhir:'Stok Akhir tidak boleh kosong!',
posisi:'Posisi tidak boleh kosong!',
tanggal:'Tanggal tidak boleh kosong!',
jam:'Jam tidak boleh kosong!',
petugas:'Petugas tidak boleh kosong!',
kd_bangsal:'Kd Bangsal tidak boleh kosong!',
status:'Status tidak boleh kosong!',
no_batch:'No Batch tidak boleh kosong!',
no_faktur:'No Faktur tidak boleh kosong!',
keterangan:'Keterangan tidak boleh kosong!'

        },
        submitHandler: function (form) {
var kode_brng= $('#kode_brng').val();
var stok_awal= $('#stok_awal').val();
var masuk= $('#masuk').val();
var keluar= $('#keluar').val();
var stok_akhir= $('#stok_akhir').val();
var posisi= $('#posisi').val();
var tanggal= $('#tanggal').val();
var jam= $('#jam').val();
var petugas= $('#petugas').val();
var kd_bangsal= $('#kd_bangsal').val();
var status= $('#status').val();
var no_batch= $('#no_batch').val();
var no_faktur= $('#no_faktur').val();
var keterangan= $('#keterangan').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['riwayat_barang_medis','aksi'])?}",
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
                            $("#modal_riwayat_barang_medis").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_riwayat_barang_medis").modal('hide');
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
                    var_tbl_riwayat_barang_medis.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_riwayat_barang_medis.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_riwayat_barang_medis.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_riwayat_barang_medis.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_riwayat_barang_medis').click(function () {
        var_tbl_riwayat_barang_medis.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_riwayat_barang_medis").click(function () {
        var rowData = var_tbl_riwayat_barang_medis.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var kode_brng = rowData['kode_brng'];
var stok_awal = rowData['stok_awal'];
var masuk = rowData['masuk'];
var keluar = rowData['keluar'];
var stok_akhir = rowData['stok_akhir'];
var posisi = rowData['posisi'];
var tanggal = rowData['tanggal'];
var jam = rowData['jam'];
var petugas = rowData['petugas'];
var kd_bangsal = rowData['kd_bangsal'];
var status = rowData['status'];
var no_batch = rowData['no_batch'];
var no_faktur = rowData['no_faktur'];
var keterangan = rowData['keterangan'];

            $("#typeact").val("edit");
  
            $('#kode_brng').val(kode_brng);
$('#stok_awal').val(stok_awal);
$('#masuk').val(masuk);
$('#keluar').val(keluar);
$('#stok_akhir').val(stok_akhir);
$('#posisi').val(posisi);
$('#tanggal').val(tanggal);
$('#jam').val(jam);
$('#petugas').val(petugas);
$('#kd_bangsal').val(kd_bangsal);
$('#status').val(status);
$('#no_batch').val(no_batch);
$('#no_faktur').val(no_faktur);
$('#keterangan').val(keterangan);

            $("#kode_brng").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Riwayat Barang Medis");
            $("#modal_riwayat_barang_medis").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_riwayat_barang_medis").click(function () {
        var rowData = var_tbl_riwayat_barang_medis.rows({ selected: true }).data()[0];


        if (rowData) {
var kode_brng = rowData['kode_brng'];
            bootbox.confirm('Anda yakin akan menghapus data dengan kode_brng="' + kode_brng, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['riwayat_barang_medis','aksi'])?}",
                        method: "POST",
                        data: {
                            kode_brng: kode_brng,
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
                            var_tbl_riwayat_barang_medis.draw();
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
    jQuery("#tambah_data_riwayat_barang_medis").click(function () {

        $('#kode_brng').val('');
$('#stok_awal').val('');
$('#masuk').val('');
$('#keluar').val('');
$('#stok_akhir').val('');
$('#posisi').val('');
$('#tanggal').val('');
$('#jam').val('');
$('#petugas').val('');
$('#kd_bangsal').val('');
$('#status').val('');
$('#no_batch').val('');
$('#no_faktur').val('');
$('#keterangan').val('');

        $("#typeact").val("add");
        $("#kode_brng").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Riwayat Barang Medis");
        $("#modal_riwayat_barang_medis").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_riwayat_barang_medis").click(function () {

        var search_field_riwayat_barang_medis = $('#search_field_riwayat_barang_medis').val();
        var search_text_riwayat_barang_medis = $('#search_text_riwayat_barang_medis').val();

        $.ajax({
            url: "{?=url(['riwayat_barang_medis','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_riwayat_barang_medis: search_field_riwayat_barang_medis, 
                search_text_riwayat_barang_medis: search_text_riwayat_barang_medis
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_riwayat_barang_medis' class='table display dataTable' style='width:100%'><thead><th>Kode Brng</th><th>Stok Awal</th><th>Masuk</th><th>Keluar</th><th>Stok Akhir</th><th>Posisi</th><th>Tanggal</th><th>Jam</th><th>Petugas</th><th>Kd Bangsal</th><th>Status</th><th>No Batch</th><th>No Faktur</th><th>Keterangan</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['kode_brng'] + '</td>';
eTable += '<td>' + res[i]['stok_awal'] + '</td>';
eTable += '<td>' + res[i]['masuk'] + '</td>';
eTable += '<td>' + res[i]['keluar'] + '</td>';
eTable += '<td>' + res[i]['stok_akhir'] + '</td>';
eTable += '<td>' + res[i]['posisi'] + '</td>';
eTable += '<td>' + res[i]['tanggal'] + '</td>';
eTable += '<td>' + res[i]['jam'] + '</td>';
eTable += '<td>' + res[i]['petugas'] + '</td>';
eTable += '<td>' + res[i]['kd_bangsal'] + '</td>';
eTable += '<td>' + res[i]['status'] + '</td>';
eTable += '<td>' + res[i]['no_batch'] + '</td>';
eTable += '<td>' + res[i]['no_faktur'] + '</td>';
eTable += '<td>' + res[i]['keterangan'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_riwayat_barang_medis').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_riwayat_barang_medis").modal('show');
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
        doc.text("Tabel Data Riwayat Barang Medis", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_riwayat_barang_medis',
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
        // doc.save('table_data_riwayat_barang_medis.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_riwayat_barang_medis");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data riwayat_barang_medis");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/riwayat_barang_medis/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});