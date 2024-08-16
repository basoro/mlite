jQuery().ready(function () {
    var var_tbl_opname = $('#tbl_opname').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['opname','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_opname = $('#search_field_opname').val();
                var search_text_opname = $('#search_text_opname').val();
                
                data.search_field_opname = search_field_opname;
                data.search_text_opname = search_text_opname;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_opname').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_opname tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'kode_brng' },
{ 'data': 'h_beli' },
{ 'data': 'tanggal' },
{ 'data': 'stok' },
{ 'data': 'real' },
{ 'data': 'selisih' },
{ 'data': 'nomihilang' },
{ 'data': 'lebih' },
{ 'data': 'nomilebih' },
{ 'data': 'keterangan' },
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
        selector: '#tbl_opname tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_opname.rows({ selected: true }).data()[0];
          if (rowData != null) {
var kode_brng = rowData['kode_brng'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/opname/detail/' + kode_brng + '?t=' + mlite.token);
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

    $("form[name='form_opname']").validate({
        rules: {
kode_brng: 'required',
h_beli: 'required',
tanggal: 'required',
stok: 'required',
real: 'required',
selisih: 'required',
nomihilang: 'required',
lebih: 'required',
nomilebih: 'required',
keterangan: 'required',
kd_bangsal: 'required',
no_batch: 'required',
no_faktur: 'required'

        },
        messages: {
kode_brng:'Kode Brng tidak boleh kosong!',
h_beli:'H Beli tidak boleh kosong!',
tanggal:'Tanggal tidak boleh kosong!',
stok:'Stok tidak boleh kosong!',
real:'Real tidak boleh kosong!',
selisih:'Selisih tidak boleh kosong!',
nomihilang:'Nomihilang tidak boleh kosong!',
lebih:'Lebih tidak boleh kosong!',
nomilebih:'Nomilebih tidak boleh kosong!',
keterangan:'Keterangan tidak boleh kosong!',
kd_bangsal:'Kd Bangsal tidak boleh kosong!',
no_batch:'No Batch tidak boleh kosong!',
no_faktur:'No Faktur tidak boleh kosong!'

        },
        submitHandler: function (form) {
var kode_brng= $('#kode_brng').val();
var h_beli= $('#h_beli').val();
var tanggal= $('#tanggal').val();
var stok= $('#stok').val();
var real= $('#real').val();
var selisih= $('#selisih').val();
var nomihilang= $('#nomihilang').val();
var lebih= $('#lebih').val();
var nomilebih= $('#nomilebih').val();
var keterangan= $('#keterangan').val();
var kd_bangsal= $('#kd_bangsal').val();
var no_batch= $('#no_batch').val();
var no_faktur= $('#no_faktur').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['opname','aksi'])?}",
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
                            $("#modal_opname").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_opname").modal('hide');
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
                    var_tbl_opname.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_opname.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_opname.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_opname.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_opname').click(function () {
        var_tbl_opname.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_opname").click(function () {
        var rowData = var_tbl_opname.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var kode_brng = rowData['kode_brng'];
var h_beli = rowData['h_beli'];
var tanggal = rowData['tanggal'];
var stok = rowData['stok'];
var real = rowData['real'];
var selisih = rowData['selisih'];
var nomihilang = rowData['nomihilang'];
var lebih = rowData['lebih'];
var nomilebih = rowData['nomilebih'];
var keterangan = rowData['keterangan'];
var kd_bangsal = rowData['kd_bangsal'];
var no_batch = rowData['no_batch'];
var no_faktur = rowData['no_faktur'];

            $("#typeact").val("edit");
  
            $('#kode_brng').val(kode_brng);
$('#h_beli').val(h_beli);
$('#tanggal').val(tanggal);
$('#stok').val(stok);
$('#real').val(real);
$('#selisih').val(selisih);
$('#nomihilang').val(nomihilang);
$('#lebih').val(lebih);
$('#nomilebih').val(nomilebih);
$('#keterangan').val(keterangan);
$('#kd_bangsal').val(kd_bangsal);
$('#no_batch').val(no_batch);
$('#no_faktur').val(no_faktur);

            $("#kode_brng").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Opname");
            $("#modal_opname").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_opname").click(function () {
        var rowData = var_tbl_opname.rows({ selected: true }).data()[0];


        if (rowData) {
var kode_brng = rowData['kode_brng'];
            bootbox.confirm('Anda yakin akan menghapus data dengan kode_brng="' + kode_brng, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['opname','aksi'])?}",
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
                            var_tbl_opname.draw();
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
    jQuery("#tambah_data_opname").click(function () {

        $('#kode_brng').val('');
$('#h_beli').val('');
$('#tanggal').val('');
$('#stok').val('');
$('#real').val('');
$('#selisih').val('');
$('#nomihilang').val('');
$('#lebih').val('');
$('#nomilebih').val('');
$('#keterangan').val('');
$('#kd_bangsal').val('');
$('#no_batch').val('');
$('#no_faktur').val('');

        $("#typeact").val("add");
        $("#kode_brng").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Opname");
        $("#modal_opname").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_opname").click(function () {

        var search_field_opname = $('#search_field_opname').val();
        var search_text_opname = $('#search_text_opname').val();

        $.ajax({
            url: "{?=url(['opname','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_opname: search_field_opname, 
                search_text_opname: search_text_opname
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_opname' class='table display dataTable' style='width:100%'><thead><th>Kode Brng</th><th>H Beli</th><th>Tanggal</th><th>Stok</th><th>Real</th><th>Selisih</th><th>Nomihilang</th><th>Lebih</th><th>Nomilebih</th><th>Keterangan</th><th>Kd Bangsal</th><th>No Batch</th><th>No Faktur</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['kode_brng'] + '</td>';
eTable += '<td>' + res[i]['h_beli'] + '</td>';
eTable += '<td>' + res[i]['tanggal'] + '</td>';
eTable += '<td>' + res[i]['stok'] + '</td>';
eTable += '<td>' + res[i]['real'] + '</td>';
eTable += '<td>' + res[i]['selisih'] + '</td>';
eTable += '<td>' + res[i]['nomihilang'] + '</td>';
eTable += '<td>' + res[i]['lebih'] + '</td>';
eTable += '<td>' + res[i]['nomilebih'] + '</td>';
eTable += '<td>' + res[i]['keterangan'] + '</td>';
eTable += '<td>' + res[i]['kd_bangsal'] + '</td>';
eTable += '<td>' + res[i]['no_batch'] + '</td>';
eTable += '<td>' + res[i]['no_faktur'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_opname').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_opname").modal('show');
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
        doc.text("Tabel Data Opname", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_opname',
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
        // doc.save('table_data_opname.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_opname");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data opname");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/opname/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});