jQuery().ready(function () {
    var var_tbl_inventaris_barang = $('#tbl_inventaris_barang').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['inventaris_barang','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_inventaris_barang = $('#search_field_inventaris_barang').val();
                var search_text_inventaris_barang = $('#search_text_inventaris_barang').val();
                
                data.search_field_inventaris_barang = search_field_inventaris_barang;
                data.search_text_inventaris_barang = search_text_inventaris_barang;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_inventaris_barang').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_inventaris_barang tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'kode_barang' },
{ 'data': 'nama_barang' },
{ 'data': 'jml_barang' },
{ 'data': 'kode_produsen' },
{ 'data': 'id_merk' },
{ 'data': 'thn_produksi' },
{ 'data': 'isbn' },
{ 'data': 'id_kategori' },
{ 'data': 'id_jenis' }

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
        selector: '#tbl_inventaris_barang tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_inventaris_barang.rows({ selected: true }).data()[0];
          if (rowData != null) {
var kode_barang = rowData['kode_barang'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/inventaris_barang/detail/' + kode_barang + '?t=' + mlite.token);
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

    $("form[name='form_inventaris_barang']").validate({
        rules: {
kode_barang: 'required',
nama_barang: 'required',
jml_barang: 'required',
kode_produsen: 'required',
id_merk: 'required',
thn_produksi: 'required',
isbn: 'required',
id_kategori: 'required',
id_jenis: 'required'

        },
        messages: {
kode_barang:'Kode Barang tidak boleh kosong!',
nama_barang:'Nama Barang tidak boleh kosong!',
jml_barang:'Jml Barang tidak boleh kosong!',
kode_produsen:'Kode Produsen tidak boleh kosong!',
id_merk:'Id Merk tidak boleh kosong!',
thn_produksi:'Thn Produksi tidak boleh kosong!',
isbn:'Isbn tidak boleh kosong!',
id_kategori:'Id Kategori tidak boleh kosong!',
id_jenis:'Id Jenis tidak boleh kosong!'

        },
        submitHandler: function (form) {
var kode_barang= $('#kode_barang').val();
var nama_barang= $('#nama_barang').val();
var jml_barang= $('#jml_barang').val();
var kode_produsen= $('#kode_produsen').val();
var id_merk= $('#id_merk').val();
var thn_produksi= $('#thn_produksi').val();
var isbn= $('#isbn').val();
var id_kategori= $('#id_kategori').val();
var id_jenis= $('#id_jenis').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['inventaris_barang','aksi'])?}",
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
                            $("#modal_inventaris_barang").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_inventaris_barang").modal('hide');
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
                    var_tbl_inventaris_barang.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_inventaris_barang.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_inventaris_barang.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_inventaris_barang.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_inventaris_barang').click(function () {
        var_tbl_inventaris_barang.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_inventaris_barang").click(function () {
        var rowData = var_tbl_inventaris_barang.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var kode_barang = rowData['kode_barang'];
var nama_barang = rowData['nama_barang'];
var jml_barang = rowData['jml_barang'];
var kode_produsen = rowData['kode_produsen'];
var id_merk = rowData['id_merk'];
var thn_produksi = rowData['thn_produksi'];
var isbn = rowData['isbn'];
var id_kategori = rowData['id_kategori'];
var id_jenis = rowData['id_jenis'];

            $("#typeact").val("edit");
  
            $('#kode_barang').val(kode_barang);
$('#nama_barang').val(nama_barang);
$('#jml_barang').val(jml_barang);
$('#kode_produsen').val(kode_produsen);
$('#id_merk').val(id_merk);
$('#thn_produksi').val(thn_produksi);
$('#isbn').val(isbn);
$('#id_kategori').val(id_kategori);
$('#id_jenis').val(id_jenis);

            $("#kode_barang").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Inventaris Barang");
            $("#modal_inventaris_barang").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_inventaris_barang").click(function () {
        var rowData = var_tbl_inventaris_barang.rows({ selected: true }).data()[0];


        if (rowData) {
var kode_barang = rowData['kode_barang'];
            bootbox.confirm('Anda yakin akan menghapus data dengan kode_barang="' + kode_barang, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['inventaris_barang','aksi'])?}",
                        method: "POST",
                        data: {
                            kode_barang: kode_barang,
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
                            var_tbl_inventaris_barang.draw();
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
    jQuery("#tambah_data_inventaris_barang").click(function () {

        $('#kode_barang').val('');
$('#nama_barang').val('');
$('#jml_barang').val('');
$('#kode_produsen').val('');
$('#id_merk').val('');
$('#thn_produksi').val('');
$('#isbn').val('');
$('#id_kategori').val('');
$('#id_jenis').val('');

        $("#typeact").val("add");
        $("#kode_barang").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Inventaris Barang");
        $("#modal_inventaris_barang").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_inventaris_barang").click(function () {

        var search_field_inventaris_barang = $('#search_field_inventaris_barang').val();
        var search_text_inventaris_barang = $('#search_text_inventaris_barang').val();

        $.ajax({
            url: "{?=url(['inventaris_barang','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_inventaris_barang: search_field_inventaris_barang, 
                search_text_inventaris_barang: search_text_inventaris_barang
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_inventaris_barang' class='table display dataTable' style='width:100%'><thead><th>Kode Barang</th><th>Nama Barang</th><th>Jml Barang</th><th>Kode Produsen</th><th>Id Merk</th><th>Thn Produksi</th><th>Isbn</th><th>Id Kategori</th><th>Id Jenis</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['kode_barang'] + '</td>';
eTable += '<td>' + res[i]['nama_barang'] + '</td>';
eTable += '<td>' + res[i]['jml_barang'] + '</td>';
eTable += '<td>' + res[i]['kode_produsen'] + '</td>';
eTable += '<td>' + res[i]['id_merk'] + '</td>';
eTable += '<td>' + res[i]['thn_produksi'] + '</td>';
eTable += '<td>' + res[i]['isbn'] + '</td>';
eTable += '<td>' + res[i]['id_kategori'] + '</td>';
eTable += '<td>' + res[i]['id_jenis'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_inventaris_barang').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_inventaris_barang").modal('show');
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
        doc.text("Tabel Data Inventaris Barang", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_inventaris_barang',
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
        // doc.save('table_data_inventaris_barang.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_inventaris_barang");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data inventaris_barang");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/inventaris_barang/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});