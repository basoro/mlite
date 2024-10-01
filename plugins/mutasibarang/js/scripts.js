jQuery().ready(function () {
    var var_tbl_mutasibarang = $('#tbl_mutasibarang').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['mutasibarang','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_mutasibarang = $('#search_field_mutasibarang').val();
                var search_text_mutasibarang = $('#search_text_mutasibarang').val();
                
                data.search_field_mutasibarang = search_field_mutasibarang;
                data.search_text_mutasibarang = search_text_mutasibarang;
                
            }
        },
        "columns": [
{ 'data': 'kode_brng' },
{ 'data': 'jml' },
{ 'data': 'harga' },
{ 'data': 'kd_bangsaldari' },
{ 'data': 'kd_bangsalke' },
{ 'data': 'tanggal' },
{ 'data': 'keterangan' },
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
        selector: '#tbl_mutasibarang tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_mutasibarang.rows({ selected: true }).data()[0];
          if (rowData != null) {
var kode_brng = rowData['kode_brng'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/mutasibarang/detail/' + kode_brng + '?t=' + mlite.token);
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

    $("form[name='form_mutasibarang']").validate({
        rules: {
kode_brng: 'required',
jml: 'required',
harga: 'required',
kd_bangsaldari: 'required',
kd_bangsalke: 'required',
tanggal: 'required',
keterangan: 'required',
no_batch: 'required',
no_faktur: 'required'

        },
        messages: {
kode_brng:'Kode Brng tidak boleh kosong!',
jml:'Jml tidak boleh kosong!',
harga:'Harga tidak boleh kosong!',
kd_bangsaldari:'Kd Bangsaldari tidak boleh kosong!',
kd_bangsalke:'Kd Bangsalke tidak boleh kosong!',
tanggal:'Tanggal tidak boleh kosong!',
keterangan:'Keterangan tidak boleh kosong!',
no_batch:'No Batch tidak boleh kosong!',
no_faktur:'No Faktur tidak boleh kosong!'

        },
        submitHandler: function (form) {
var kode_brng= $('#kode_brng').val();
var jml= $('#jml').val();
var harga= $('#harga').val();
var kd_bangsaldari= $('#kd_bangsaldari').val();
var kd_bangsalke= $('#kd_bangsalke').val();
var tanggal= $('#tanggal').val();
var keterangan= $('#keterangan').val();
var no_batch= $('#no_batch').val();
var no_faktur= $('#no_faktur').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['mutasibarang','aksi'])?}",
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
                            $("#modal_mutasibarang").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_mutasibarang").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    var_tbl_mutasibarang.draw();
                }
            })
        }
    });

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_mutasibarang').click(function () {
        var_tbl_mutasibarang.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_mutasibarang").click(function () {
        var rowData = var_tbl_mutasibarang.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var kode_brng = rowData['kode_brng'];
var jml = rowData['jml'];
var harga = rowData['harga'];
var kd_bangsaldari = rowData['kd_bangsaldari'];
var kd_bangsalke = rowData['kd_bangsalke'];
var tanggal = rowData['tanggal'];
var keterangan = rowData['keterangan'];
var no_batch = rowData['no_batch'];
var no_faktur = rowData['no_faktur'];

            $("#typeact").val("edit");
  
            $('#kode_brng').val(kode_brng);
$('#jml').val(jml);
$('#harga').val(harga);
$('#kd_bangsaldari').val(kd_bangsaldari);
$('#kd_bangsalke').val(kd_bangsalke);
$('#tanggal').val(tanggal);
$('#keterangan').val(keterangan);
$('#no_batch').val(no_batch);
$('#no_faktur').val(no_faktur);

            $("#kode_brng").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Mutasibarang");
            $("#modal_mutasibarang").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_mutasibarang").click(function () {
        var rowData = var_tbl_mutasibarang.rows({ selected: true }).data()[0];


        if (rowData) {
var kode_brng = rowData['kode_brng'];
            bootbox.confirm('Anda yakin akan menghapus data dengan kode_brng="' + kode_brng, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['mutasibarang','aksi'])?}",
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
                            var_tbl_mutasibarang.draw();
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
    jQuery("#tambah_data_mutasibarang").click(function () {

        $('#kode_brng').val('');
$('#jml').val('');
$('#harga').val('');
$('#kd_bangsaldari').val('');
$('#kd_bangsalke').val('');
$('#tanggal').val('');
$('#keterangan').val('');
$('#no_batch').val('');
$('#no_faktur').val('');

        $("#typeact").val("add");
        $("#kode_brng").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Mutasibarang");
        $("#modal_mutasibarang").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_mutasibarang").click(function () {

        var search_field_mutasibarang = $('#search_field_mutasibarang').val();
        var search_text_mutasibarang = $('#search_text_mutasibarang').val();

        $.ajax({
            url: "{?=url(['mutasibarang','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_mutasibarang: search_field_mutasibarang, 
                search_text_mutasibarang: search_text_mutasibarang
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_mutasibarang' class='table display dataTable' style='width:100%'><thead><th>Kode Brng</th><th>Jml</th><th>Harga</th><th>Kd Bangsaldari</th><th>Kd Bangsalke</th><th>Tanggal</th><th>Keterangan</th><th>No Batch</th><th>No Faktur</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['kode_brng'] + '</td>';
eTable += '<td>' + res[i]['jml'] + '</td>';
eTable += '<td>' + res[i]['harga'] + '</td>';
eTable += '<td>' + res[i]['kd_bangsaldari'] + '</td>';
eTable += '<td>' + res[i]['kd_bangsalke'] + '</td>';
eTable += '<td>' + res[i]['tanggal'] + '</td>';
eTable += '<td>' + res[i]['keterangan'] + '</td>';
eTable += '<td>' + res[i]['no_batch'] + '</td>';
eTable += '<td>' + res[i]['no_faktur'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_mutasibarang').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_mutasibarang").modal('show');
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
        doc.text("Tabel Data Mutasibarang", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_mutasibarang',
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
        // doc.save('table_data_mutasibarang.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_mutasibarang");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data mutasibarang");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/mutasibarang/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});