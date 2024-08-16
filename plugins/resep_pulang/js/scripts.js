jQuery().ready(function () {
    var var_tbl_resep_pulang = $('#tbl_resep_pulang').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['resep_pulang','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_resep_pulang = $('#search_field_resep_pulang').val();
                var search_text_resep_pulang = $('#search_text_resep_pulang').val();
                
                data.search_field_resep_pulang = search_field_resep_pulang;
                data.search_text_resep_pulang = search_text_resep_pulang;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_resep_pulang').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_resep_pulang tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'no_rawat' },
{ 'data': 'kode_brng' },
{ 'data': 'jml_barang' },
{ 'data': 'harga' },
{ 'data': 'total' },
{ 'data': 'dosis' },
{ 'data': 'tanggal' },
{ 'data': 'jam' },
{ 'data': 'kd_bangsal' },
{ 'data': 'no_batch' },
{ 'data': 'no_faktur' }

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
        selector: '#tbl_resep_pulang tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_resep_pulang.rows({ selected: true }).data()[0];
          if (rowData != null) {
var no_rawat = rowData['no_rawat'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/resep_pulang/detail/' + no_rawat + '?t=' + mlite.token);
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

    $("form[name='form_resep_pulang']").validate({
        rules: {
no_rawat: 'required',
kode_brng: 'required',
jml_barang: 'required',
harga: 'required',
total: 'required',
dosis: 'required',
tanggal: 'required',
jam: 'required',
kd_bangsal: 'required',
no_batch: 'required',
no_faktur: 'required'

        },
        messages: {
no_rawat:'No Rawat tidak boleh kosong!',
kode_brng:'Kode Brng tidak boleh kosong!',
jml_barang:'Jml Barang tidak boleh kosong!',
harga:'Harga tidak boleh kosong!',
total:'Total tidak boleh kosong!',
dosis:'Dosis tidak boleh kosong!',
tanggal:'Tanggal tidak boleh kosong!',
jam:'Jam tidak boleh kosong!',
kd_bangsal:'Kd Bangsal tidak boleh kosong!',
no_batch:'No Batch tidak boleh kosong!',
no_faktur:'No Faktur tidak boleh kosong!'

        },
        submitHandler: function (form) {
var no_rawat= $('#no_rawat').val();
var kode_brng= $('#kode_brng').val();
var jml_barang= $('#jml_barang').val();
var harga= $('#harga').val();
var total= $('#total').val();
var dosis= $('#dosis').val();
var tanggal= $('#tanggal').val();
var jam= $('#jam').val();
var kd_bangsal= $('#kd_bangsal').val();
var no_batch= $('#no_batch').val();
var no_faktur= $('#no_faktur').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['resep_pulang','aksi'])?}",
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
                            $("#modal_resep_pulang").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_resep_pulang").modal('hide');
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
                    var_tbl_resep_pulang.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_resep_pulang.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_resep_pulang.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_resep_pulang.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_resep_pulang').click(function () {
        var_tbl_resep_pulang.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_resep_pulang").click(function () {
        var rowData = var_tbl_resep_pulang.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var no_rawat = rowData['no_rawat'];
var kode_brng = rowData['kode_brng'];
var jml_barang = rowData['jml_barang'];
var harga = rowData['harga'];
var total = rowData['total'];
var dosis = rowData['dosis'];
var tanggal = rowData['tanggal'];
var jam = rowData['jam'];
var kd_bangsal = rowData['kd_bangsal'];
var no_batch = rowData['no_batch'];
var no_faktur = rowData['no_faktur'];

            $("#typeact").val("edit");
  
            $('#no_rawat').val(no_rawat);
$('#kode_brng').val(kode_brng);
$('#jml_barang').val(jml_barang);
$('#harga').val(harga);
$('#total').val(total);
$('#dosis').val(dosis);
$('#tanggal').val(tanggal);
$('#jam').val(jam);
$('#kd_bangsal').val(kd_bangsal);
$('#no_batch').val(no_batch);
$('#no_faktur').val(no_faktur);

            $("#no_rawat").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Resep Pulang");
            $("#modal_resep_pulang").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_resep_pulang").click(function () {
        var rowData = var_tbl_resep_pulang.rows({ selected: true }).data()[0];


        if (rowData) {
var no_rawat = rowData['no_rawat'];
            bootbox.confirm('Anda yakin akan menghapus data dengan no_rawat="' + no_rawat, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['resep_pulang','aksi'])?}",
                        method: "POST",
                        data: {
                            no_rawat: no_rawat,
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
                            var_tbl_resep_pulang.draw();
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
    jQuery("#tambah_data_resep_pulang").click(function () {

        $('#no_rawat').val('');
$('#kode_brng').val('');
$('#jml_barang').val('');
$('#harga').val('');
$('#total').val('');
$('#dosis').val('');
$('#tanggal').val('');
$('#jam').val('');
$('#kd_bangsal').val('');
$('#no_batch').val('');
$('#no_faktur').val('');

        $("#typeact").val("add");
        $("#no_rawat").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Resep Pulang");
        $("#modal_resep_pulang").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_resep_pulang").click(function () {

        var search_field_resep_pulang = $('#search_field_resep_pulang').val();
        var search_text_resep_pulang = $('#search_text_resep_pulang').val();

        $.ajax({
            url: "{?=url(['resep_pulang','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_resep_pulang: search_field_resep_pulang, 
                search_text_resep_pulang: search_text_resep_pulang
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_resep_pulang' class='table display dataTable' style='width:100%'><thead><th>No Rawat</th><th>Kode Brng</th><th>Jml Barang</th><th>Harga</th><th>Total</th><th>Dosis</th><th>Tanggal</th><th>Jam</th><th>Kd Bangsal</th><th>No Batch</th><th>No Faktur</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['no_rawat'] + '</td>';
eTable += '<td>' + res[i]['kode_brng'] + '</td>';
eTable += '<td>' + res[i]['jml_barang'] + '</td>';
eTable += '<td>' + res[i]['harga'] + '</td>';
eTable += '<td>' + res[i]['total'] + '</td>';
eTable += '<td>' + res[i]['dosis'] + '</td>';
eTable += '<td>' + res[i]['tanggal'] + '</td>';
eTable += '<td>' + res[i]['jam'] + '</td>';
eTable += '<td>' + res[i]['kd_bangsal'] + '</td>';
eTable += '<td>' + res[i]['no_batch'] + '</td>';
eTable += '<td>' + res[i]['no_faktur'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_resep_pulang').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_resep_pulang").modal('show');
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
        doc.text("Tabel Data Resep Pulang", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_resep_pulang',
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
        // doc.save('table_data_resep_pulang.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_resep_pulang");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data resep_pulang");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/resep_pulang/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});