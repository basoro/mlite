jQuery().ready(function () {
    var var_tbl_mlite_billing = $('#tbl_mlite_billing').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['mlite_billing','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_mlite_billing = $('#search_field_mlite_billing').val();
                var search_text_mlite_billing = $('#search_text_mlite_billing').val();
                
                data.search_field_mlite_billing = search_field_mlite_billing;
                data.search_text_mlite_billing = search_text_mlite_billing;
                
            }
        },
        "columns": [
{ 'data': 'id_billing' },
{ 'data': 'kd_billing' },
{ 'data': 'no_rawat' },
{ 'data': 'jumlah_total' },
{ 'data': 'potongan' },
{ 'data': 'jumlah_harus_bayar' },
{ 'data': 'jumlah_bayar' },
{ 'data': 'tgl_billing' },
{ 'data': 'jam_billing' },
{ 'data': 'id_user' },
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
        selector: '#tbl_mlite_billing tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_mlite_billing.rows({ selected: true }).data()[0];
          if (rowData != null) {
var id_billing = rowData['id_billing'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/mlite_billing/detail/' + id_billing + '?t=' + mlite.token);
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

    $("form[name='form_mlite_billing']").validate({
        rules: {
id_billing: 'required',
kd_billing: 'required',
no_rawat: 'required',
jumlah_total: 'required',
potongan: 'required',
jumlah_harus_bayar: 'required',
jumlah_bayar: 'required',
tgl_billing: 'required',
jam_billing: 'required',
id_user: 'required',
keterangan: 'required'

        },
        messages: {
id_billing:'Id Billing tidak boleh kosong!',
kd_billing:'Kd Billing tidak boleh kosong!',
no_rawat:'No Rawat tidak boleh kosong!',
jumlah_total:'Jumlah Total tidak boleh kosong!',
potongan:'Potongan tidak boleh kosong!',
jumlah_harus_bayar:'Jumlah Harus Bayar tidak boleh kosong!',
jumlah_bayar:'Jumlah Bayar tidak boleh kosong!',
tgl_billing:'Tgl Billing tidak boleh kosong!',
jam_billing:'Jam Billing tidak boleh kosong!',
id_user:'Id User tidak boleh kosong!',
keterangan:'Keterangan tidak boleh kosong!'

        },
        submitHandler: function (form) {
var id_billing= $('#id_billing').val();
var kd_billing= $('#kd_billing').val();
var no_rawat= $('#no_rawat').val();
var jumlah_total= $('#jumlah_total').val();
var potongan= $('#potongan').val();
var jumlah_harus_bayar= $('#jumlah_harus_bayar').val();
var jumlah_bayar= $('#jumlah_bayar').val();
var tgl_billing= $('#tgl_billing').val();
var jam_billing= $('#jam_billing').val();
var id_user= $('#id_user').val();
var keterangan= $('#keterangan').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['mlite_billing','aksi'])?}",
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
                            $("#modal_mlite_billing").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_mlite_billing").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    var_tbl_mlite_billing.draw();
                }
            })
        }
    });

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_mlite_billing').click(function () {
        var_tbl_mlite_billing.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_mlite_billing").click(function () {
        var rowData = var_tbl_mlite_billing.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var id_billing = rowData['id_billing'];
var kd_billing = rowData['kd_billing'];
var no_rawat = rowData['no_rawat'];
var jumlah_total = rowData['jumlah_total'];
var potongan = rowData['potongan'];
var jumlah_harus_bayar = rowData['jumlah_harus_bayar'];
var jumlah_bayar = rowData['jumlah_bayar'];
var tgl_billing = rowData['tgl_billing'];
var jam_billing = rowData['jam_billing'];
var id_user = rowData['id_user'];
var keterangan = rowData['keterangan'];

            $("#typeact").val("edit");
  
            $('#id_billing').val(id_billing);
$('#kd_billing').val(kd_billing);
$('#no_rawat').val(no_rawat);
$('#jumlah_total').val(jumlah_total);
$('#potongan').val(potongan);
$('#jumlah_harus_bayar').val(jumlah_harus_bayar);
$('#jumlah_bayar').val(jumlah_bayar);
$('#tgl_billing').val(tgl_billing);
$('#jam_billing').val(jam_billing);
$('#id_user').val(id_user);
$('#keterangan').val(keterangan);

            $("#id_billing").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Mlite Billing");
            $("#modal_mlite_billing").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_mlite_billing").click(function () {
        var rowData = var_tbl_mlite_billing.rows({ selected: true }).data()[0];


        if (rowData) {
var id_billing = rowData['id_billing'];
            bootbox.confirm('Anda yakin akan menghapus data dengan id_billing="' + id_billing, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['mlite_billing','aksi'])?}",
                        method: "POST",
                        data: {
                            id_billing: id_billing,
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
                            var_tbl_mlite_billing.draw();
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
    jQuery("#tambah_data_mlite_billing").click(function () {

        $('#id_billing').val('');
$('#kd_billing').val('');
$('#no_rawat').val('');
$('#jumlah_total').val('');
$('#potongan').val('');
$('#jumlah_harus_bayar').val('');
$('#jumlah_bayar').val('');
$('#tgl_billing').val('');
$('#jam_billing').val('');
$('#id_user').val('');
$('#keterangan').val('');

        $("#typeact").val("add");
        $("#id_billing").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Mlite Billing");
        $("#modal_mlite_billing").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_mlite_billing").click(function () {

        var search_field_mlite_billing = $('#search_field_mlite_billing').val();
        var search_text_mlite_billing = $('#search_text_mlite_billing').val();

        $.ajax({
            url: "{?=url(['mlite_billing','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_mlite_billing: search_field_mlite_billing, 
                search_text_mlite_billing: search_text_mlite_billing
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_mlite_billing' class='table display dataTable' style='width:100%'><thead><th>Id Billing</th><th>Kd Billing</th><th>No Rawat</th><th>Jumlah Total</th><th>Potongan</th><th>Jumlah Harus Bayar</th><th>Jumlah Bayar</th><th>Tgl Billing</th><th>Jam Billing</th><th>Id User</th><th>Keterangan</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['id_billing'] + '</td>';
eTable += '<td>' + res[i]['kd_billing'] + '</td>';
eTable += '<td>' + res[i]['no_rawat'] + '</td>';
eTable += '<td>' + res[i]['jumlah_total'] + '</td>';
eTable += '<td>' + res[i]['potongan'] + '</td>';
eTable += '<td>' + res[i]['jumlah_harus_bayar'] + '</td>';
eTable += '<td>' + res[i]['jumlah_bayar'] + '</td>';
eTable += '<td>' + res[i]['tgl_billing'] + '</td>';
eTable += '<td>' + res[i]['jam_billing'] + '</td>';
eTable += '<td>' + res[i]['id_user'] + '</td>';
eTable += '<td>' + res[i]['keterangan'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_mlite_billing').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_mlite_billing").modal('show');
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
        doc.text("Tabel Data Mlite Billing", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_mlite_billing',
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
        // doc.save('table_data_mlite_billing.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_mlite_billing");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data mlite_billing");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/mlite_billing/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});