jQuery().ready(function () {
    var var_tbl_poliklinik = $('#tbl_poliklinik').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['poliklinik','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_poliklinik = $('#search_field_poliklinik').val();
                var search_text_poliklinik = $('#search_text_poliklinik').val();
                
                data.search_field_poliklinik = search_field_poliklinik;
                data.search_text_poliklinik = search_text_poliklinik;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_poliklinik').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_poliklinik tr').contextMenu({x: clientX, y: clientY});
            });          
        },        
        "columns": [
            { 'data': 'kd_poli' },
            { 'data': 'nm_poli' },
            { 'data': 'registrasi' },
            { 'data': 'registrasilama' },
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
            { 'targets': 4}
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
        selector: '#tbl_poliklinik tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_poliklinik.rows({ selected: true }).data()[0];
          if (rowData != null) {
            var kd_poli = rowData['kd_poli'];
            switch (key) {
                case 'detail' :
                OpenModal(mlite.url + '/poliklinik/detail/' + kd_poli + '?t=' + mlite.token);
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

    $("form[name='form_poliklinik']").validate({
        rules: {
            kd_poli: 'required',
            nm_poli: 'required',
            registrasi: 'required',
            registrasilama: 'required',
            status: 'required'
        },
        messages: {
            kd_poli:'Kd Poli tidak boleh kosong!',
            nm_poli:'Nm Poli tidak boleh kosong!',
            registrasi:'Registrasi tidak boleh kosong!',
            registrasilama:'Registrasilama tidak boleh kosong!',
            status:'Status tidak boleh kosong!'
        },
        submitHandler: function (form) {
            var kd_poli= $('#kd_poli').val();
            var nm_poli= $('#nm_poli').val();
            var registrasi= $('#registrasi').val();
            var registrasilama= $('#registrasilama').val();
            var status= $('#status').val();

            var typeact = $('#typeact').val();

            var formData = new FormData(form); // tambahan
            formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['poliklinik','aksi'])?}",
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
                            $("#modal_poliklinik").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_poliklinik").modal('hide');
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
                    var_tbl_poliklinik.draw();
                }
            })
        }
    });


    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_poliklinik.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_poliklinik.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_poliklinik.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_poliklinik').click(function () {
        var_tbl_poliklinik.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_poliklinik").click(function () {
        var rowData = var_tbl_poliklinik.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var kd_poli = rowData['kd_poli'];
            var nm_poli = rowData['nm_poli'];
            var registrasi = rowData['registrasi'];
            var registrasilama = rowData['registrasilama'];
            var status = rowData['status'];

            $("#typeact").val("edit");
  
            $('#kd_poli').val(kd_poli);
            $('#nm_poli').val(nm_poli);
            $('#registrasi').val(registrasi);
            $('#registrasilama').val(registrasilama);
            $('#status').val(status).change();

            $("#kd_poli").prop('readonly', true); // GA BISA DIEDIT KALI DISABLE
            $('#modal-title').text("Edit Data Poliklinik");
            $("#modal_poliklinik").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_poliklinik").click(function () {
        var rowData = var_tbl_poliklinik.rows({ selected: true }).data()[0];


        if (rowData) {
            var kd_poli = rowData['kd_poli'];
            bootbox.confirm('Anda yakin akan menghapus data dengan kd_poli="' + kd_poli, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['poliklinik','aksi'])?}",
                        method: "POST",
                        data: {
                            kd_poli: kd_poli,
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
                            var_tbl_poliklinik.draw();
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
    jQuery("#tambah_data_poliklinik").click(function () {

        $('#kd_poli').val('');
        $('#nm_poli').val('');
        $('#registrasi').val('');
        $('#registrasilama').val('');
        $('#status').val('').change();

        $("#typeact").val("add");
        $("#kd_poli").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Poliklinik");
        $("#modal_poliklinik").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_poliklinik").click(function () {

        var search_field_poliklinik = $('#search_field_poliklinik').val();
        var search_text_poliklinik = $('#search_text_poliklinik').val();

        $.ajax({
            url: "{?=url(['poliklinik','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_poliklinik: search_field_poliklinik, 
                search_text_poliklinik: search_text_poliklinik
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_poliklinik' class='table display dataTable' style='width:100%'><thead><th>Kd Poli</th><th>Nm Poli</th><th>Registrasi</th><th>Registrasilama</th><th>Status</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['kd_poli'] + '</td>';
                    eTable += '<td>' + res[i]['nm_poli'] + '</td>';
                    eTable += '<td>' + res[i]['registrasi'] + '</td>';
                    eTable += '<td>' + res[i]['registrasilama'] + '</td>';
                    eTable += '<td>' + res[i]['status'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_poliklinik').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_poliklinik").modal('show');
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
        doc.text("Tabel Data Poliklinik", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_poliklinik',
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
        // doc.save('table_data_poliklinik.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_poliklinik");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data poliklinik");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/poliklinik/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});