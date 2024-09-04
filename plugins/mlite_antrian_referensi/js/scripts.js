jQuery().ready(function () {
    var var_tbl_mlite_antrian_referensi = $('#tbl_mlite_antrian_referensi').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['mlite_antrian_referensi','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_mlite_antrian_referensi = $('#search_field_mlite_antrian_referensi').val();
                var search_text_mlite_antrian_referensi = $('#search_text_mlite_antrian_referensi').val();
                
                data.search_field_mlite_antrian_referensi = search_field_mlite_antrian_referensi;
                data.search_text_mlite_antrian_referensi = search_text_mlite_antrian_referensi;
                
            }
        },
        "columns": [
{ 'data': 'tanggal_periksa' },
{ 'data': 'no_rkm_medis' },
{ 'data': 'nomor_kartu' },
{ 'data': 'nomor_referensi' },
{ 'data': 'kodebooking' },
{ 'data': 'jenis_kunjungan' },
{ 'data': 'status_kirim' },
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
{ 'targets': 7}

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
        selector: '#tbl_mlite_antrian_referensi tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_mlite_antrian_referensi.rows({ selected: true }).data()[0];
          if (rowData != null) {
var tanggal_periksa = rowData['tanggal_periksa'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/mlite_antrian_referensi/detail/' + tanggal_periksa + '?t=' + mlite.token);
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

    $("form[name='form_mlite_antrian_referensi']").validate({
        rules: {
tanggal_periksa: 'required',
no_rkm_medis: 'required',
nomor_kartu: 'required',
nomor_referensi: 'required',
kodebooking: 'required',
jenis_kunjungan: 'required',
status_kirim: 'required',
keterangan: 'required'

        },
        messages: {
tanggal_periksa:'Tanggal Periksa tidak boleh kosong!',
no_rkm_medis:'No Rkm Medis tidak boleh kosong!',
nomor_kartu:'Nomor Kartu tidak boleh kosong!',
nomor_referensi:'Nomor Referensi tidak boleh kosong!',
kodebooking:'Kodebooking tidak boleh kosong!',
jenis_kunjungan:'Jenis Kunjungan tidak boleh kosong!',
status_kirim:'Status Kirim tidak boleh kosong!',
keterangan:'Keterangan tidak boleh kosong!'

        },
        submitHandler: function (form) {
var tanggal_periksa= $('#tanggal_periksa').val();
var no_rkm_medis= $('#no_rkm_medis').val();
var nomor_kartu= $('#nomor_kartu').val();
var nomor_referensi= $('#nomor_referensi').val();
var kodebooking= $('#kodebooking').val();
var jenis_kunjungan= $('#jenis_kunjungan').val();
var status_kirim= $('#status_kirim').val();
var keterangan= $('#keterangan').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['mlite_antrian_referensi','aksi'])?}",
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
                            $("#modal_mlite_antrian_referensi").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_mlite_antrian_referensi").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    var_tbl_mlite_antrian_referensi.draw();
                }
            })
        }
    });

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_mlite_antrian_referensi').click(function () {
        var_tbl_mlite_antrian_referensi.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_mlite_antrian_referensi").click(function () {
        var rowData = var_tbl_mlite_antrian_referensi.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var tanggal_periksa = rowData['tanggal_periksa'];
var no_rkm_medis = rowData['no_rkm_medis'];
var nomor_kartu = rowData['nomor_kartu'];
var nomor_referensi = rowData['nomor_referensi'];
var kodebooking = rowData['kodebooking'];
var jenis_kunjungan = rowData['jenis_kunjungan'];
var status_kirim = rowData['status_kirim'];
var keterangan = rowData['keterangan'];

            $("#typeact").val("edit");
  
            $('#tanggal_periksa').val(tanggal_periksa);
$('#no_rkm_medis').val(no_rkm_medis);
$('#nomor_kartu').val(nomor_kartu);
$('#nomor_referensi').val(nomor_referensi);
$('#kodebooking').val(kodebooking);
$('#jenis_kunjungan').val(jenis_kunjungan);
$('#status_kirim').val(status_kirim);
$('#keterangan').val(keterangan);

            $("#tanggal_periksa").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Mlite Antrian Referensi");
            $("#modal_mlite_antrian_referensi").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_mlite_antrian_referensi").click(function () {
        var rowData = var_tbl_mlite_antrian_referensi.rows({ selected: true }).data()[0];


        if (rowData) {
var tanggal_periksa = rowData['tanggal_periksa'];
            bootbox.confirm('Anda yakin akan menghapus data dengan tanggal_periksa="' + tanggal_periksa, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['mlite_antrian_referensi','aksi'])?}",
                        method: "POST",
                        data: {
                            tanggal_periksa: tanggal_periksa,
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
                            var_tbl_mlite_antrian_referensi.draw();
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
    jQuery("#tambah_data_mlite_antrian_referensi").click(function () {

        $('#tanggal_periksa').val('');
$('#no_rkm_medis').val('');
$('#nomor_kartu').val('');
$('#nomor_referensi').val('');
$('#kodebooking').val('');
$('#jenis_kunjungan').val('');
$('#status_kirim').val('');
$('#keterangan').val('');

        $("#typeact").val("add");
        $("#tanggal_periksa").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Mlite Antrian Referensi");
        $("#modal_mlite_antrian_referensi").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_mlite_antrian_referensi").click(function () {

        var search_field_mlite_antrian_referensi = $('#search_field_mlite_antrian_referensi').val();
        var search_text_mlite_antrian_referensi = $('#search_text_mlite_antrian_referensi').val();

        $.ajax({
            url: "{?=url(['mlite_antrian_referensi','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_mlite_antrian_referensi: search_field_mlite_antrian_referensi, 
                search_text_mlite_antrian_referensi: search_text_mlite_antrian_referensi
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_mlite_antrian_referensi' class='table display dataTable' style='width:100%'><thead><th>Tanggal Periksa</th><th>No Rkm Medis</th><th>Nomor Kartu</th><th>Nomor Referensi</th><th>Kodebooking</th><th>Jenis Kunjungan</th><th>Status Kirim</th><th>Keterangan</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['tanggal_periksa'] + '</td>';
eTable += '<td>' + res[i]['no_rkm_medis'] + '</td>';
eTable += '<td>' + res[i]['nomor_kartu'] + '</td>';
eTable += '<td>' + res[i]['nomor_referensi'] + '</td>';
eTable += '<td>' + res[i]['kodebooking'] + '</td>';
eTable += '<td>' + res[i]['jenis_kunjungan'] + '</td>';
eTable += '<td>' + res[i]['status_kirim'] + '</td>';
eTable += '<td>' + res[i]['keterangan'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_mlite_antrian_referensi').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_mlite_antrian_referensi").modal('show');
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
        doc.text("Tabel Data Mlite Antrian Referensi", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_mlite_antrian_referensi',
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
        // doc.save('table_data_mlite_antrian_referensi.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_mlite_antrian_referensi");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data mlite_antrian_referensi");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/mlite_antrian_referensi/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});