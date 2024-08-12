jQuery().ready(function () {
    var var_tbl_petugas = $('#tbl_petugas').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['petugas','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_petugas = $('#search_field_petugas').val();
                var search_text_petugas = $('#search_text_petugas').val();
                
                data.search_field_petugas = search_field_petugas;
                data.search_text_petugas = search_text_petugas;
                
            }
        },
        "columns": [
            { 'data': 'nip' },
            { 'data': 'nama' },
            { 'data': 'jk' },
            { 'data': 'tmp_lahir' },
            { 'data': 'tgl_lahir' },
            { 'data': 'gol_darah' },
            { 'data': 'agama' },
            { 'data': 'stts_nikah' },
            { 'data': 'alamat' },
            { 'data': 'kd_jbtn' },
            { 'data': 'no_telp' },
            { 'data': 'status', 
                "render": function (data) {
                    if(data == '1') {
                        var status = 'Aktif';
                    } else {
                        var status = 'Tidak Aktif';
                    }
                    return status;
                }      
            }
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
        selector: '#tbl_petugas tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_petugas.rows({ selected: true }).data()[0];
          if (rowData != null) {
            var nip = rowData['nip'];
            switch (key) {
                case 'detail' :
                OpenModal(mlite.url + '/petugas/detail/' + nip + '?t=' + mlite.token);
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

    $("form[name='form_petugas']").validate({
        rules: {
            nip: 'required',
            nama: 'required',
            jk: 'required',
            tmp_lahir: 'required',
            tgl_lahir: 'required',
            gol_darah: 'required',
            agama: 'required',
            stts_nikah: 'required',
            alamat: 'required',
            kd_jbtn: 'required',
            no_telp: 'required',
            status: 'required'
        },
        messages: {
            nip:'Nip tidak boleh kosong!',
            nama:'Nama tidak boleh kosong!',
            jk:'Jk tidak boleh kosong!',
            tmp_lahir:'Tmp Lahir tidak boleh kosong!',
            tgl_lahir:'Tgl Lahir tidak boleh kosong!',
            gol_darah:'Gol Darah tidak boleh kosong!',
            agama:'Agama tidak boleh kosong!',
            stts_nikah:'Stts Nikah tidak boleh kosong!',
            alamat:'Alamat tidak boleh kosong!',
            kd_jbtn:'Kd Jbtn tidak boleh kosong!',
            no_telp:'No Telp tidak boleh kosong!',
            status:'Status tidak boleh kosong!'
        },
        submitHandler: function (form) {
            var nip= $('#nip').val();
            var nama= $('#nip').find(':selected').text();
            var jk= $('#jk').val();
            var tmp_lahir= $('#tmp_lahir').val();
            var tgl_lahir= $('#tgl_lahir').val();
            var gol_darah= $('#gol_darah').val();
            var agama= $('#agama').val();
            var stts_nikah= $('#stts_nikah').val();
            var alamat= $('#alamat').val();
            var kd_jbtn= $('#kd_jbtn').val();
            var no_telp= $('#no_telp').val();
            var status= $('#status').val();

            var typeact = $('#typeact').val();

            var formData = new FormData(form); // tambahan
            formData.append('typeact', typeact); // tambahan
            formData.append('nama', nama); // tambahan

            $.ajax({
                url: "{?=url(['petugas','aksi'])?}",
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
                            $("#modal_petugas").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_petugas").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    var_tbl_petugas.draw();
                }
            })
        }
    });

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_petugas').click(function () {
        var_tbl_petugas.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_petugas").click(function () {
        var rowData = var_tbl_petugas.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var nip = rowData['nip'];
            var nama = rowData['nama'];
            var jk = rowData['jk'];
            var tmp_lahir = rowData['tmp_lahir'];
            var tgl_lahir = rowData['tgl_lahir'];
            var gol_darah = rowData['gol_darah'];
            var agama = rowData['agama'];
            var stts_nikah = rowData['stts_nikah'];
            var alamat = rowData['alamat'];
            var kd_jbtn = rowData['kd_jbtn'];
            var no_telp = rowData['no_telp'];
            var status = rowData['status'];

            $("#typeact").val("edit");
  
            $('#nip').val(nip).change();
            // $('#nama').val(nama);
            $('#jk').val(jk).change();
            $('#tmp_lahir').val(tmp_lahir);
            $('#tgl_lahir').val(tgl_lahir);
            $('#gol_darah').val(gol_darah).change();
            $('#agama').val(agama).change();
            $('#stts_nikah').val(stts_nikah).change();
            $('#alamat').val(alamat);
            $('#kd_jbtn').val(kd_jbtn).change();
            $('#no_telp').val(no_telp);
            $('#status').val(status).change();

            $("#nip").prop('readonly', true); // GA BISA DIEDIT KALI DISABLE
            $('#modal-title').text("Edit Data Petugas");
            $("#modal_petugas").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_petugas").click(function () {
        var rowData = var_tbl_petugas.rows({ selected: true }).data()[0];


        if (rowData) {
            var nip = rowData['nip'];
            bootbox.confirm('Anda yakin akan menghapus data dengan nip="' + nip, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['petugas','aksi'])?}",
                        method: "POST",
                        data: {
                            nip: nip,
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
                            var_tbl_petugas.draw();
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
    jQuery("#tambah_data_petugas").click(function () {

        $('#nip').val('');
        $('#nama').val('');
        $('#jk').val('');
        $('#tmp_lahir').val('');
        $('#tgl_lahir').val('');
        $('#gol_darah').val('');
        $('#agama').val('');
        $('#stts_nikah').val('');
        $('#alamat').val('');
        $('#kd_jbtn').val('');
        $('#no_telp').val('');
        $('#status').val('');

        $("#typeact").val("add");
        $("#nip").prop('disabled', false);
        
        $('#modal-title').text("Tambah Data Petugas");
        $("#modal_petugas").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_petugas").click(function () {

        var search_field_petugas = $('#search_field_petugas').val();
        var search_text_petugas = $('#search_text_petugas').val();

        $.ajax({
            url: "{?=url(['petugas','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_petugas: search_field_petugas, 
                search_text_petugas: search_text_petugas
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_petugas' class='table display dataTable' style='width:100%'><thead><th>Nip</th><th>Nama</th><th>Jk</th><th>Tmp Lahir</th><th>Tgl Lahir</th><th>Gol Darah</th><th>Agama</th><th>Stts Nikah</th><th>Alamat</th><th>Kd Jbtn</th><th>No Telp</th><th>Status</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['nip'] + '</td>';
                    eTable += '<td>' + res[i]['nama'] + '</td>';
                    eTable += '<td>' + res[i]['jk'] + '</td>';
                    eTable += '<td>' + res[i]['tmp_lahir'] + '</td>';
                    eTable += '<td>' + res[i]['tgl_lahir'] + '</td>';
                    eTable += '<td>' + res[i]['gol_darah'] + '</td>';
                    eTable += '<td>' + res[i]['agama'] + '</td>';
                    eTable += '<td>' + res[i]['stts_nikah'] + '</td>';
                    eTable += '<td>' + res[i]['alamat'] + '</td>';
                    eTable += '<td>' + res[i]['kd_jbtn'] + '</td>';
                    eTable += '<td>' + res[i]['no_telp'] + '</td>';
                    eTable += '<td>' + res[i]['status'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_petugas').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_petugas").modal('show');
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
        doc.text("Tabel Data Petugas", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_petugas',
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
        // doc.save('table_data_petugas.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_petugas");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data petugas");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/petugas/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

    $('#nip').on('change', function(e) {
        var nip = $('#nip').find(':selected').val();
        if(nip !='') {
            $.ajax({
                url: mlite.url + '/pegawai/read/' + nip + '?t=' + mlite.token,
                method: "GET",
                data: {
                },
                success: function (data) {
                    data = JSON.parse(data);
                    if(data.msg.jk == 'Pria') {
                        $('#jk').val('L').change();
                    } else {
                        $('#jk').val('P').change();
                    }
                    $('#tmp_lahir').val(data.msg.tmp_lahir);
                    $('#tgl_lahir').val(data.msg.tgl_lahir);
                    $('#alamat').val(data.msg.alamat);                    
                }
            })    
        }
    })

    $(".datepicker").daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        locale: {
            format: "YYYY-MM-DD",
        },
    });

});