jQuery().ready(function () {
    var var_tbl_inventaris = $('#tbl_inventaris').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['inventaris','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_inventaris = $('#search_field_inventaris').val();
                var search_text_inventaris = $('#search_text_inventaris').val();
                
                data.search_field_inventaris = search_field_inventaris;
                data.search_text_inventaris = search_text_inventaris;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_inventaris').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_inventaris tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'no_inventaris' },
{ 'data': 'kode_barang' },
{ 'data': 'asal_barang' },
{ 'data': 'tgl_pengadaan' },
{ 'data': 'harga' },
{ 'data': 'status_barang' },
{ 'data': 'id_ruang' },
{ 'data': 'no_rak' },
{ 'data': 'no_box' }

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
        selector: '#tbl_inventaris tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_inventaris.rows({ selected: true }).data()[0];
          if (rowData != null) {
var no_inventaris = rowData['no_inventaris'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/inventaris/detail/' + no_inventaris + '?t=' + mlite.token);
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

    $("form[name='form_inventaris']").validate({
        rules: {
no_inventaris: 'required',
kode_barang: 'required',
asal_barang: 'required',
tgl_pengadaan: 'required',
harga: 'required',
status_barang: 'required',
id_ruang: 'required',
no_rak: 'required',
no_box: 'required'

        },
        messages: {
no_inventaris:'No Inventaris tidak boleh kosong!',
kode_barang:'Kode Barang tidak boleh kosong!',
asal_barang:'Asal Barang tidak boleh kosong!',
tgl_pengadaan:'Tgl Pengadaan tidak boleh kosong!',
harga:'Harga tidak boleh kosong!',
status_barang:'Status Barang tidak boleh kosong!',
id_ruang:'Id Ruang tidak boleh kosong!',
no_rak:'No Rak tidak boleh kosong!',
no_box:'No Box tidak boleh kosong!'

        },
        submitHandler: function (form) {
var no_inventaris= $('#no_inventaris').val();
var kode_barang= $('#kode_barang').val();
var asal_barang= $('#asal_barang').val();
var tgl_pengadaan= $('#tgl_pengadaan').val();
var harga= $('#harga').val();
var status_barang= $('#status_barang').val();
var id_ruang= $('#id_ruang').val();
var no_rak= $('#no_rak').val();
var no_box= $('#no_box').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['inventaris','aksi'])?}",
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
                            $("#modal_inventaris").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_inventaris").modal('hide');
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
                    var_tbl_inventaris.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_inventaris.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_inventaris.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_inventaris.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_inventaris').click(function () {
        var_tbl_inventaris.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_inventaris").click(function () {
        var rowData = var_tbl_inventaris.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var no_inventaris = rowData['no_inventaris'];
var kode_barang = rowData['kode_barang'];
var asal_barang = rowData['asal_barang'];
var tgl_pengadaan = rowData['tgl_pengadaan'];
var harga = rowData['harga'];
var status_barang = rowData['status_barang'];
var id_ruang = rowData['id_ruang'];
var no_rak = rowData['no_rak'];
var no_box = rowData['no_box'];

            $("#typeact").val("edit");
  
            $('#no_inventaris').val(no_inventaris);
$('#kode_barang').val(kode_barang);
$('#asal_barang').val(asal_barang);
$('#tgl_pengadaan').val(tgl_pengadaan);
$('#harga').val(harga);
$('#status_barang').val(status_barang);
$('#id_ruang').val(id_ruang);
$('#no_rak').val(no_rak);
$('#no_box').val(no_box);

            $("#no_inventaris").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Inventaris");
            $("#modal_inventaris").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_inventaris").click(function () {
        var rowData = var_tbl_inventaris.rows({ selected: true }).data()[0];


        if (rowData) {
var no_inventaris = rowData['no_inventaris'];
            bootbox.confirm('Anda yakin akan menghapus data dengan no_inventaris="' + no_inventaris, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['inventaris','aksi'])?}",
                        method: "POST",
                        data: {
                            no_inventaris: no_inventaris,
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
                            var_tbl_inventaris.draw();
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
    jQuery("#tambah_data_inventaris").click(function () {

        $('#no_inventaris').val('');
$('#kode_barang').val('');
$('#asal_barang').val('');
$('#tgl_pengadaan').val('');
$('#harga').val('');
$('#status_barang').val('');
$('#id_ruang').val('');
$('#no_rak').val('');
$('#no_box').val('');

        $("#typeact").val("add");
        $("#no_inventaris").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Inventaris");
        $("#modal_inventaris").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_inventaris").click(function () {

        var search_field_inventaris = $('#search_field_inventaris').val();
        var search_text_inventaris = $('#search_text_inventaris').val();

        $.ajax({
            url: "{?=url(['inventaris','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_inventaris: search_field_inventaris, 
                search_text_inventaris: search_text_inventaris
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_inventaris' class='table display dataTable' style='width:100%'><thead><th>No Inventaris</th><th>Kode Barang</th><th>Asal Barang</th><th>Tgl Pengadaan</th><th>Harga</th><th>Status Barang</th><th>Id Ruang</th><th>No Rak</th><th>No Box</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['no_inventaris'] + '</td>';
eTable += '<td>' + res[i]['kode_barang'] + '</td>';
eTable += '<td>' + res[i]['asal_barang'] + '</td>';
eTable += '<td>' + res[i]['tgl_pengadaan'] + '</td>';
eTable += '<td>' + res[i]['harga'] + '</td>';
eTable += '<td>' + res[i]['status_barang'] + '</td>';
eTable += '<td>' + res[i]['id_ruang'] + '</td>';
eTable += '<td>' + res[i]['no_rak'] + '</td>';
eTable += '<td>' + res[i]['no_box'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_inventaris').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_inventaris").modal('show');
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
        doc.text("Tabel Data Inventaris", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_inventaris',
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
        // doc.save('table_data_inventaris.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_inventaris");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data inventaris");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/inventaris/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});