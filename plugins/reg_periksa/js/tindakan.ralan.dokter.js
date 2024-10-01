jQuery().ready(function () {
    var var_tbl_rawat_jl_dr = $('#tindakan_dokter_reg_periksa #tbl_rawat_jl_dr').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'responsive': true, 
        'searching': false,
        'select': true, 
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['reg_periksa','datatindakanralandokter'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                data.no_rawat = $("a.active").attr('data-no_rawat');
                
            }
        },
        "columns": [
            { 'data': 'no_rawat' },
            { 'data': 'kd_jenis_prw' },
            { 'data': 'nm_perawatan' },
            { 'data': 'kd_dokter' },
            { 'data': 'nm_dokter' },
            { 'data': 'tgl_perawatan' },
            { 'data': 'jam_rawat' },
            { 'data': 'material' },
            { 'data': 'bhp' },
            { 'data': 'tarif_tindakandr' },
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
        selector: '#tbl_rawat_jl_dr tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_rawat_jl_dr.rows({ selected: true }).data()[0];
          if (rowData != null) {
var no_rawat = rowData['no_rawat'];
            switch (key) {
                case 'detail' :
                OpenModal(mlite.url + '/rawat_jl_dr/detail/' + no_rawat + '?t=' + mlite.token);
                break;
                default :
                break
            } 
          } else {
            bootbox.alert("Silakan pilih data atau klik baris data.");            
          }          
        },
        items: {
            "detail": {name: "View Detail", "icon": "edit", disabled:  {$disabled_menu.read}},
            // "sep1": "---------",
            // "fold1": {
            //     "name": "Sub group", 
            //     "items": {
            //         "fold1-key1": {"name": "Foo bar"},
            //         "fold2": {
            //             "name": "Sub group 2", 
            //             "items": {
            //                 "fold2-key1": {"name": "alpha"},
            //                 "fold2-key2": {"name": "bravo"}
            //             }
            //         }
            //     }
            // }
        }
    });

    // ==============================================================
    // FORM VALIDASI
    // ==============================================================

    $("form[name='form_rawat_jl_dr']").validate({
        rules: {
no_rawat: 'required',
kd_jenis_prw: 'required',
kd_dokter: 'required',
tgl_perawatan: 'required',
jam_rawat: 'required',
material: 'required',
bhp: 'required',
tarif_tindakandr: 'required',
kso: 'required',
menejemen: 'required',
biaya_rawat: 'required',
stts_bayar: 'required'

        },
        messages: {
no_rawat:'No Rawat tidak boleh kosong!',
kd_jenis_prw:'Kd Jenis Prw tidak boleh kosong!',
kd_dokter:'Kd Dokter tidak boleh kosong!',
tgl_perawatan:'Tgl Perawatan tidak boleh kosong!',
jam_rawat:'Jam Rawat tidak boleh kosong!',
material:'Material tidak boleh kosong!',
bhp:'Bhp tidak boleh kosong!',
tarif_tindakandr:'Tarif Tindakandr tidak boleh kosong!',
kso:'Kso tidak boleh kosong!',
menejemen:'Menejemen tidak boleh kosong!',
biaya_rawat:'Biaya Rawat tidak boleh kosong!',
stts_bayar:'Stts Bayar tidak boleh kosong!'

        },
        submitHandler: function (form) {
 var no_rawat= $('#no_rawat').val();
var kd_jenis_prw= $('#kd_jenis_prw').val();
var kd_dokter= $('#kd_dokter').val();
var tgl_perawatan= $('#tgl_perawatan').val();
var jam_rawat= $('#jam_rawat').val();
var material= $('#material').val();
var bhp= $('#bhp').val();
var tarif_tindakandr= $('#tarif_tindakandr').val();
var kso= $('#kso').val();
var menejemen= $('#menejemen').val();
var biaya_rawat= $('#biaya_rawat').val();
var stts_bayar= $('#stts_bayar').val();

 var typeact = $('#typeact').val();

 var formData = new FormData(form); // tambahan
 formData.append('typeact', typeact); // tambahan
 console.log(JSON.stringify(Object.fromEntries(formData)));

            $.ajax({
                url: "{?=url(['rawat_jl_dr','aksi'])?}",
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
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    $("#modal_rawat_jl_dr").modal('hide');
                    var_tbl_rawat_jl_dr.draw();
                }
            })
        }
    });

    $("form[name='form_rawat_jl_dr_edit']").validate({
        rules: {
no_rawat: 'required',
kd_jenis_prw: 'required',
kd_dokter: 'required',
tgl_perawatan: 'required',
jam_rawat: 'required',
material: 'required',
bhp: 'required',
tarif_tindakandr: 'required',
kso: 'required',
menejemen: 'required',
biaya_rawat: 'required',
stts_bayar: 'required'

        },
        messages: {
no_rawat:'No Rawat tidak boleh kosong!',
kd_jenis_prw:'Kd Jenis Prw tidak boleh kosong!',
kd_dokter:'Kd Dokter tidak boleh kosong!',
tgl_perawatan:'Tgl Perawatan tidak boleh kosong!',
jam_rawat:'Jam Rawat tidak boleh kosong!',
material:'Material tidak boleh kosong!',
bhp:'Bhp tidak boleh kosong!',
tarif_tindakandr:'Tarif Tindakandr tidak boleh kosong!',
kso:'Kso tidak boleh kosong!',
menejemen:'Menejemen tidak boleh kosong!',
biaya_rawat:'Biaya Rawat tidak boleh kosong!',
stts_bayar:'Stts Bayar tidak boleh kosong!'

        },
        submitHandler: function (form) {
 var no_rawat= $('#no_rawat').val();
var kd_jenis_prw= $('#kd_jenis_prw').val();
var kd_dokter= $('#kd_dokter').val();
var tgl_perawatan= $('#tgl_perawatan').val();
var jam_rawat= $('#jam_rawat').val();
var material= $('#material').val();
var bhp= $('#bhp').val();
var tarif_tindakandr= $('#tarif_tindakandr').val();
var kso= $('#kso').val();
var menejemen= $('#menejemen').val();
var biaya_rawat= $('#biaya_rawat').val();
var stts_bayar= $('#stts_bayar').val();

 var typeact = $('#typeact').val();

 var formData = new FormData(form); // tambahan
 formData.append('typeact', typeact); // tambahan
 console.log(JSON.stringify(Object.fromEntries(formData)));

            $.ajax({
                url: "{?=url(['rawat_jl_dr','aksi'])?}",
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
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    $("#modal_rawat_jl_dr").modal('hide');
                    var_tbl_rawat_jl_dr.draw();
                }
            })
        }
    });


    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_rawat_jl_dr').click(function () {
        var_tbl_rawat_jl_dr.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_rawat_jl_dr").click(function () {
        var rowData = var_tbl_rawat_jl_dr.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var no_rawat = rowData['no_rawat'];
var kd_jenis_prw = rowData['kd_jenis_prw'];
var kd_dokter = rowData['kd_dokter'];
var tgl_perawatan = rowData['tgl_perawatan'];
var jam_rawat = rowData['jam_rawat'];
var material = rowData['material'];
var bhp = rowData['bhp'];
var tarif_tindakandr = rowData['tarif_tindakandr'];
var kso = rowData['kso'];
var menejemen = rowData['menejemen'];
var biaya_rawat = rowData['biaya_rawat'];
var stts_bayar = rowData['stts_bayar'];



            $("#typeact").val("edit");
  
            $('#no_rawat').val(no_rawat);
$('#modal_rawat_jl_dr_edit #kd_jenis_prw').val(kd_jenis_prw);
$('#modal_rawat_jl_dr_edit #kd_dokter').val(kd_dokter).change();
$('#modal_rawat_jl_dr_edit #tgl_perawatan').val(tgl_perawatan);
$('#modal_rawat_jl_dr_edit #jam_rawat').val(jam_rawat);
$('#modal_rawat_jl_dr_edit #material').val(material);
$('#modal_rawat_jl_dr_edit #bhp').val(bhp);
$('#modal_rawat_jl_dr_edit #tarif_tindakandr').val(tarif_tindakandr);
$('#modal_rawat_jl_dr_edit #kso').val(kso);
$('#modal_rawat_jl_dr_edit #menejemen').val(menejemen);
$('#modal_rawat_jl_dr_edit #biaya_rawat').val(biaya_rawat);
$('#modal_rawat_jl_dr_edit #stts_bayar').val(stts_bayar).change();

            $("#no_rawat_rawat_jl_dr_edit").prop('readonly', true); // GA BISA DIEDIT KALI DISABLE
            $("#modal_rawat_jl_dr_edit #kd_jenis_prw").prop('readonly', true); // GA BISA DIEDIT KALI DISABLE
            $("#modal_rawat_jl_dr_edit #tgl_perawatan").prop('readonly', true); // GA BISA DIEDIT KALI DISABLE
            $("#modal_rawat_jl_dr_edit #jam_rawat").prop('readonly', true); // GA BISA DIEDIT KALI DISABLE
            $("#modal_rawat_jl_dr_edit #material").prop('readonly', true); // GA BISA DIEDIT KALI DISABLE
            $("#modal_rawat_jl_dr_edit #bhp").prop('readonly', true); // GA BISA DIEDIT KALI DISABLE
            $("#modal_rawat_jl_dr_edit #tarif_tindakandr").prop('readonly', true); // GA BISA DIEDIT KALI DISABLE
            $("#modal_rawat_jl_dr_edit #kso").prop('readonly', true); // GA BISA DIEDIT KALI DISABLE
            $("#modal_rawat_jl_dr_edit #menejemen").prop('readonly', true); // GA BISA DIEDIT KALI DISABLE
            $("#modal_rawat_jl_dr_edit #biaya_rawat").prop('readonly', true); // GA BISA DIEDIT KALI DISABLE
            $('#modal-title').text("Edit Data Tindakan Ralan Dokter");
            $("#modal_rawat_jl_dr_edit").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_rawat_jl_dr").click(function () {
        var rowData = var_tbl_rawat_jl_dr.rows({ selected: true }).data()[0];


        if (rowData) {
            var no_rawat = rowData['no_rawat'];
            var tgl_perawatan = rowData['tgl_perawatan'];
            var jam_rawat = rowData['jam_rawat'];
            var kd_jenis_prw = rowData['kd_jenis_prw'];
            bootbox.confirm('Anda yakin akan menghapus data dengan no_rawat="' + no_rawat, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['rawat_jl_dr','aksi'])?}",
                        method: "POST",
                        data: {
                            no_rawat: no_rawat,
                            tgl_perawatan: tgl_perawatan, 
                            jam_rawat: jam_rawat, 
                            kd_jenis_prw: kd_jenis_prw, 
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
                            var_tbl_rawat_jl_dr.draw();
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
    jQuery("#tambah_data_rawat_jl_dr").click(function () {

        $('#no_rawat').val('');
$('#kd_jenis_prw').val('');
$('#kd_dokter').find(':selected');
$('#tgl_perawatan').val('');
$('#jam_rawat').val('');
$('#material').val('');
$('#bhp').val('');
$('#tarif_tindakandr').val('');
$('#kso').val('');
$('#menejemen').val('');
$('#biaya_rawat').val('');
$('#stts_bayar').val('Belum').change();

// $('#kd_jenis_prw').on('change', function() {
//     $.ajax({
//         url: "{?=url(['reg_periksa','getjnsperawatan'])?}",
//         method: "POST",
//         data: {
//             kd_jenis_prw: this.value
//         },
//         success: function (data) {
//             data = JSON.parse(data);
//             $('#material').val(data.material);
//             $('#bhp').val(data.bhp);
//             $('#tarif_tindakandr').val(data.tarif_tindakandr);
//             $('#kso').val(data.kso);
//             $('#menejemen').val(data.menejemen);
//             $('#biaya_rawat').val(data.total_byrdrpr);
//         }
//     })
// });        

        $("#typeact").val("add");
        $("#no_rawat").prop('disabled', false);
        
        $('#modal-title').text("Tambah Data Tindakan Ralan Dokter");
        $("#modal_rawat_jl_dr").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_rawat_jl_dr").click(function () {

        var search_field_rawat_jl_dr = $('#search_field_rawat_jl_dr').val();
        var search_text_rawat_jl_dr = $('#search_text_rawat_jl_dr').val();

        $.ajax({
            url: "{?=url(['rawat_jl_dr','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_rawat_jl_dr: search_field_rawat_jl_dr, 
                search_text_rawat_jl_dr: search_text_rawat_jl_dr
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_rawat_jl_dr' class='table display dataTable' style='width:100%'><thead><th>No Rawat</th><th>Kd Jenis Prw</th><th>Kd Dokter</th><th>Tgl Perawatan</th><th>Jam Rawat</th><th>Material</th><th>Bhp</th><th>Tarif Tindakandr</th><th>Kso</th><th>Menejemen</th><th>Biaya Rawat</th><th>Stts Bayar</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['no_rawat'] + '</td>';
eTable += '<td>' + res[i]['kd_jenis_prw'] + '</td>';
eTable += '<td>' + res[i]['kd_dokter'] + '</td>';
eTable += '<td>' + res[i]['tgl_perawatan'] + '</td>';
eTable += '<td>' + res[i]['jam_rawat'] + '</td>';
eTable += '<td>' + res[i]['material'] + '</td>';
eTable += '<td>' + res[i]['bhp'] + '</td>';
eTable += '<td>' + res[i]['tarif_tindakandr'] + '</td>';
eTable += '<td>' + res[i]['kso'] + '</td>';
eTable += '<td>' + res[i]['menejemen'] + '</td>';
eTable += '<td>' + res[i]['biaya_rawat'] + '</td>';
eTable += '<td>' + res[i]['stts_bayar'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_rawat_jl_dr').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_rawat_jl_dr").modal('show');
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
        doc.text("Tabel Data Rawat Jl Dr", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_rawat_jl_dr',
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
        // doc.save('table_data_rawat_jl_dr.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_rawat_jl_dr");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data rawat_jl_dr");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    var tbl_reg_periksa_jns_perawatan = $('#tbl_reg_periksa_jns_perawatan').DataTable({
        columnDefs: [
          { targets: 0, visible: false }
        ],
        orderFixed: [0, 'desc']
    })  
     
    $('#tbl_reg_periksa_jns_perawatan').on('click', 'input[type="checkbox"]', function() {
        var row =  tbl_reg_periksa_jns_perawatan.row($(this).closest('tr'));
        tbl_reg_periksa_jns_perawatan.cell({ row: row.index(), column: 0 } ).data( this.checked ? 1 : 0 )
        row.invalidate().draw()
    })    

});