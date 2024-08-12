jQuery().ready(function () {
    var var_tbl_rawat_jl_pr = $('#tbl_rawat_jl_pr').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['rawat_jl_pr','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_rawat_jl_pr = $('#search_field_rawat_jl_pr').val();
                var search_text_rawat_jl_pr = $('#search_text_rawat_jl_pr').val();
                
                data.search_field_rawat_jl_pr = search_field_rawat_jl_pr;
                data.search_text_rawat_jl_pr = search_text_rawat_jl_pr;
                
            }
        },
        "columns": [
{ 'data': 'no_rawat' },
{ 'data': 'kd_jenis_prw' },
{ 'data': 'nip' },
{ 'data': 'tgl_perawatan' },
{ 'data': 'jam_rawat' },
{ 'data': 'material' },
{ 'data': 'bhp' },
{ 'data': 'tarif_tindakanpr' },
{ 'data': 'kso' },
{ 'data': 'menejemen' },
{ 'data': 'biaya_rawat' },
{ 'data': 'stts_bayar' }

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
{ 'targets': 11}

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
        selector: '#tbl_rawat_jl_pr tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_rawat_jl_pr.rows({ selected: true }).data()[0];
          if (rowData != null) {
var no_rawat = rowData['no_rawat'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/rawat_jl_pr/detail/' + no_rawat + '?t=' + mlite.token);
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

    $("form[name='form_rawat_jl_pr']").validate({
        rules: {
no_rawat: 'required',
kd_jenis_prw: 'required',
nip: 'required',
tgl_perawatan: 'required',
jam_rawat: 'required',
material: 'required',
bhp: 'required',
tarif_tindakanpr: 'required',
kso: 'required',
menejemen: 'required',
biaya_rawat: 'required',
stts_bayar: 'required'

        },
        messages: {
no_rawat:'No Rawat tidak boleh kosong!',
kd_jenis_prw:'Kd Jenis Prw tidak boleh kosong!',
nip:'Nip tidak boleh kosong!',
tgl_perawatan:'Tgl Perawatan tidak boleh kosong!',
jam_rawat:'Jam Rawat tidak boleh kosong!',
material:'Material tidak boleh kosong!',
bhp:'Bhp tidak boleh kosong!',
tarif_tindakanpr:'Tarif Tindakanpr tidak boleh kosong!',
kso:'Kso tidak boleh kosong!',
menejemen:'Menejemen tidak boleh kosong!',
biaya_rawat:'Biaya Rawat tidak boleh kosong!',
stts_bayar:'Stts Bayar tidak boleh kosong!'

        },
        submitHandler: function (form) {
var no_rawat= $('#no_rawat').val();
var kd_jenis_prw= $('#kd_jenis_prw').val();
var nip= $('#nip').val();
var tgl_perawatan= $('#tgl_perawatan').val();
var jam_rawat= $('#jam_rawat').val();
var material= $('#material').val();
var bhp= $('#bhp').val();
var tarif_tindakanpr= $('#tarif_tindakanpr').val();
var kso= $('#kso').val();
var menejemen= $('#menejemen').val();
var biaya_rawat= $('#biaya_rawat').val();
var stts_bayar= $('#stts_bayar').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['rawat_jl_pr','aksi'])?}",
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
                            $("#modal_rawat_jl_pr").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_rawat_jl_pr").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    var_tbl_rawat_jl_pr.draw();
                }
            })
        }
    });

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_rawat_jl_pr').click(function () {
        var_tbl_rawat_jl_pr.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_rawat_jl_pr").click(function () {
        var rowData = var_tbl_rawat_jl_pr.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var no_rawat = rowData['no_rawat'];
var kd_jenis_prw = rowData['kd_jenis_prw'];
var nip = rowData['nip'];
var tgl_perawatan = rowData['tgl_perawatan'];
var jam_rawat = rowData['jam_rawat'];
var material = rowData['material'];
var bhp = rowData['bhp'];
var tarif_tindakanpr = rowData['tarif_tindakanpr'];
var kso = rowData['kso'];
var menejemen = rowData['menejemen'];
var biaya_rawat = rowData['biaya_rawat'];
var stts_bayar = rowData['stts_bayar'];

            $("#typeact").val("edit");
  
            $('#no_rawat').val(no_rawat);
$('#kd_jenis_prw').val(kd_jenis_prw);
$('#nip').val(nip);
$('#tgl_perawatan').val(tgl_perawatan);
$('#jam_rawat').val(jam_rawat);
$('#material').val(material);
$('#bhp').val(bhp);
$('#tarif_tindakanpr').val(tarif_tindakanpr);
$('#kso').val(kso);
$('#menejemen').val(menejemen);
$('#biaya_rawat').val(biaya_rawat);
$('#stts_bayar').val(stts_bayar);

            $("#no_rawat").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Rawat Jl Pr");
            $("#modal_rawat_jl_pr").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_rawat_jl_pr").click(function () {
        var rowData = var_tbl_rawat_jl_pr.rows({ selected: true }).data()[0];


        if (rowData) {
var no_rawat = rowData['no_rawat'];
            bootbox.confirm('Anda yakin akan menghapus data dengan no_rawat="' + no_rawat, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['rawat_jl_pr','aksi'])?}",
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
                            var_tbl_rawat_jl_pr.draw();
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
    jQuery("#tambah_data_rawat_jl_pr").click(function () {

        $('#no_rawat').val('');
$('#kd_jenis_prw').val('');
$('#nip').val('');
$('#tgl_perawatan').val('');
$('#jam_rawat').val('');
$('#material').val('');
$('#bhp').val('');
$('#tarif_tindakanpr').val('');
$('#kso').val('');
$('#menejemen').val('');
$('#biaya_rawat').val('');
$('#stts_bayar').val('');

        $("#typeact").val("add");
        $("#no_rawat").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Rawat Jl Pr");
        $("#modal_rawat_jl_pr").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_rawat_jl_pr").click(function () {

        var search_field_rawat_jl_pr = $('#search_field_rawat_jl_pr').val();
        var search_text_rawat_jl_pr = $('#search_text_rawat_jl_pr').val();

        $.ajax({
            url: "{?=url(['rawat_jl_pr','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_rawat_jl_pr: search_field_rawat_jl_pr, 
                search_text_rawat_jl_pr: search_text_rawat_jl_pr
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_rawat_jl_pr' class='table display dataTable' style='width:100%'><thead><th>No Rawat</th><th>Kd Jenis Prw</th><th>Nip</th><th>Tgl Perawatan</th><th>Jam Rawat</th><th>Material</th><th>Bhp</th><th>Tarif Tindakanpr</th><th>Kso</th><th>Menejemen</th><th>Biaya Rawat</th><th>Stts Bayar</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['no_rawat'] + '</td>';
eTable += '<td>' + res[i]['kd_jenis_prw'] + '</td>';
eTable += '<td>' + res[i]['nip'] + '</td>';
eTable += '<td>' + res[i]['tgl_perawatan'] + '</td>';
eTable += '<td>' + res[i]['jam_rawat'] + '</td>';
eTable += '<td>' + res[i]['material'] + '</td>';
eTable += '<td>' + res[i]['bhp'] + '</td>';
eTable += '<td>' + res[i]['tarif_tindakanpr'] + '</td>';
eTable += '<td>' + res[i]['kso'] + '</td>';
eTable += '<td>' + res[i]['menejemen'] + '</td>';
eTable += '<td>' + res[i]['biaya_rawat'] + '</td>';
eTable += '<td>' + res[i]['stts_bayar'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_rawat_jl_pr').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_rawat_jl_pr").modal('show');
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
        doc.text("Tabel Data Rawat Jl Pr", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_rawat_jl_pr',
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
        // doc.save('table_data_rawat_jl_pr.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_rawat_jl_pr");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data rawat_jl_pr");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/rawat_jl_pr/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});