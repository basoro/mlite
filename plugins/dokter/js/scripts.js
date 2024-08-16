jQuery().ready(function () {
    var var_tbl_dokter = $('#tbl_dokter').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['dokter','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_dokter = $('#search_field_dokter').val();
                var search_text_dokter = $('#search_text_dokter').val();
                
                data.search_field_dokter = search_field_dokter;
                data.search_text_dokter = search_text_dokter;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_dokter').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_dokter tr').contextMenu({x: clientX, y: clientY});
            });          
        },        
        "columns": [
            { 'data': 'kd_dokter' },
            { 'data': 'nm_dokter' },
            { 'data': 'jk' },
            { 'data': 'tmp_lahir' },
            { 'data': 'tgl_lahir' },
            { 'data': 'gol_drh' },
            { 'data': 'agama' },
            { 'data': 'almt_tgl' },
            { 'data': 'no_telp' },
            { 'data': 'stts_nikah' },
            { 'data': 'kd_sps' },
            { 'data': 'alumni' },
            { 'data': 'no_ijn_praktek' },
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
        selector: '#tbl_dokter tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_dokter.rows({ selected: true }).data()[0];
          if (rowData != null) {
            var kd_dokter = rowData['kd_dokter'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/dokter/detail/' + kd_dokter + '?t=' + mlite.token);
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

    $("form[name='form_dokter']").validate({
        rules: {
            kd_dokter: 'required',
            nm_dokter: 'required',
            jk: 'required',
            tmp_lahir: 'required',
            tgl_lahir: 'required',
            gol_drh: 'required',
            agama: 'required',
            almt_tgl: 'required',
            no_telp: 'required',
            stts_nikah: 'required',
            kd_sps: 'required',
            alumni: 'required',
            no_ijn_praktek: 'required',
            status: 'required'
        },
        messages: {
            kd_dokter:'Kd Dokter tidak boleh kosong!',
            nm_dokter:'Nm Dokter tidak boleh kosong!',
            jk:'Jk tidak boleh kosong!',
            tmp_lahir:'Tmp Lahir tidak boleh kosong!',
            tgl_lahir:'Tgl Lahir tidak boleh kosong!',
            gol_drh:'Gol Drh tidak boleh kosong!',
            agama:'Agama tidak boleh kosong!',
            almt_tgl:'Almt Tgl tidak boleh kosong!',
            no_telp:'No Telp tidak boleh kosong!',
            stts_nikah:'Stts Nikah tidak boleh kosong!',
            kd_sps:'Kd Sps tidak boleh kosong!',
            alumni:'Alumni tidak boleh kosong!',
            no_ijn_praktek:'No Ijn Praktek tidak boleh kosong!',
            status:'Status tidak boleh kosong!'
        },
        submitHandler: function (form) {
            var kd_dokter= $('#kd_dokter').val();
            var nm_dokter= $('#kd_dokter').find(':selected').text();
            var jk= $('#jk').val();
            var tmp_lahir= $('#tmp_lahir').val();
            var tgl_lahir= $('#tgl_lahir').val();
            var gol_drh= $('#gol_drh').val();
            var agama= $('#agama').val();
            var almt_tgl= $('#almt_tgl').val();
            var no_telp= $('#no_telp').val();
            var stts_nikah= $('#stts_nikah').val();
            var kd_sps= $('#kd_sps').val();
            var alumni= $('#alumni').val();
            var no_ijn_praktek= $('#no_ijn_praktek').val();
            var status= $('#status').val();

            var typeact = $('#typeact').val();

            var formData = new FormData(form); // tambahan
            formData.append('typeact', typeact); // tambahan
            formData.append('nm_dokter', nm_dokter); // tambahan

            $.ajax({
                url: "{?=url(['dokter','aksi'])?}",
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
                            $("#modal_dokter").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_dokter").modal('hide');
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
                    var_tbl_dokter.draw();
                }
            })
        }
    });


    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_dokter.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_dokter.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_dokter.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_dokter').click(function () {
        var_tbl_dokter.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_dokter").click(function () {
        var rowData = var_tbl_dokter.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var kd_dokter = rowData['kd_dokter'];
            var nm_dokter = rowData['nm_dokter'];
            var jk = rowData['jk'];
            var tmp_lahir = rowData['tmp_lahir'];
            var tgl_lahir = rowData['tgl_lahir'];
            var gol_drh = rowData['gol_drh'];
            var agama = rowData['agama'];
            var almt_tgl = rowData['almt_tgl'];
            var no_telp = rowData['no_telp'];
            var stts_nikah = rowData['stts_nikah'];
            var kd_sps = rowData['kd_sps'];
            var alumni = rowData['alumni'];
            var no_ijn_praktek = rowData['no_ijn_praktek'];
            var status = rowData['status'];

            $("#typeact").val("edit");
  
            $('#kd_dokter').val(kd_dokter).change();
            $('#jk').val(jk).change();
            $('#tmp_lahir').val(tmp_lahir);
            $('#tgl_lahir').val(tgl_lahir);
            $('#gol_drh').val(gol_drh).change();
            $('#agama').val(agama).change();
            $('#almt_tgl').val(almt_tgl);
            $('#no_telp').val(no_telp);
            $('#stts_nikah').val(stts_nikah).change();
            $('#kd_sps').val(kd_sps).change();
            $('#alumni').val(alumni);
            $('#no_ijn_praktek').val(no_ijn_praktek);
            $('#status').val(status).change();

            $("#kd_dokter").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Dokter");
            $("#modal_dokter").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_dokter").click(function () {
        var rowData = var_tbl_dokter.rows({ selected: true }).data()[0];


        if (rowData) {
            var kd_dokter = rowData['kd_dokter'];
            bootbox.confirm('Anda yakin akan menghapus data dengan kd_dokter="' + kd_dokter, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['dokter','aksi'])?}",
                        method: "POST",
                        data: {
                            kd_dokter: kd_dokter,
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
                            var_tbl_dokter.draw();
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
    jQuery("#tambah_data_dokter").click(function () {

        $('#kd_dokter').val('').change();
        $('#nm_dokter').val('');
        $('#jk').val('').change();
        $('#tmp_lahir').val('');
        // $('#tgl_lahir').val('');
        $('#gol_drh').val('').change();
        $('#agama').val('').change();
        $('#almt_tgl').val('');
        $('#no_telp').val('');
        $('#stts_nikah').val('').change();
        $('#kd_sps').val('').change();
        $('#alumni').val('');
        $('#no_ijn_praktek').val('');
        $('#status').val('').change();

        $("#typeact").val("add");
        $("#kd_dokter").prop('disabled', false);
        
        $('#modal-title').text("Tambah Data Dokter");
        $("#modal_dokter").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_dokter").click(function () {

        var search_field_dokter = $('#search_field_dokter').val();
        var search_text_dokter = $('#search_text_dokter').val();

        $.ajax({
            url: "{?=url(['dokter','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_dokter: search_field_dokter, 
                search_text_dokter: search_text_dokter
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_dokter' class='table display dataTable' style='width:100%'><thead><th>Kd Dokter</th><th>Nm Dokter</th><th>Jk</th><th>Tmp Lahir</th><th>Tgl Lahir</th><th>Gol Drh</th><th>Agama</th><th>Almt Tgl</th><th>No Telp</th><th>Stts Nikah</th><th>Kd Sps</th><th>Alumni</th><th>No Ijn Praktek</th><th>Status</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['kd_dokter'] + '</td>';
                    eTable += '<td>' + res[i]['nm_dokter'] + '</td>';
                    eTable += '<td>' + res[i]['jk'] + '</td>';
                    eTable += '<td>' + res[i]['tmp_lahir'] + '</td>';
                    eTable += '<td>' + res[i]['tgl_lahir'] + '</td>';
                    eTable += '<td>' + res[i]['gol_drh'] + '</td>';
                    eTable += '<td>' + res[i]['agama'] + '</td>';
                    eTable += '<td>' + res[i]['almt_tgl'] + '</td>';
                    eTable += '<td>' + res[i]['no_telp'] + '</td>';
                    eTable += '<td>' + res[i]['stts_nikah'] + '</td>';
                    eTable += '<td>' + res[i]['kd_sps'] + '</td>';
                    eTable += '<td>' + res[i]['alumni'] + '</td>';
                    eTable += '<td>' + res[i]['no_ijn_praktek'] + '</td>';
                    eTable += '<td>' + res[i]['status'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_dokter').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_dokter").modal('show');
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
        doc.text("Tabel Data Dokter", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_dokter',
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
        // doc.save('table_data_dokter.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_dokter");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data dokter");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/dokter/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

    $('#kd_dokter').on('change', function(e) {
        var kd_dokter = $('#kd_dokter').find(':selected').val();
        if(kd_dokter !='') {
            $.ajax({
                url: mlite.url + '/pegawai/read/' + kd_dokter + '?t=' + mlite.token,
                method: "GET",
                data: {
                },
                success: function (data) {
                    data = JSON.parse(data);
                    $('#kd_dokter').val(data.msg.nik);
                    if(data.msg.jk == 'Pria') {
                        $('#jk').val('L').change();
                    } else {
                        $('#jk').val('P').change();
                    }
                    $('#tmp_lahir').val(data.msg.tmp_lahir);
                    $('#tgl_lahir').val(data.msg.tgl_lahir);
                    $('#almt_tgl').val(data.msg.alamat);                    
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