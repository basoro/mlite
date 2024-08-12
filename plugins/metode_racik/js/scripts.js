jQuery().ready(function () {
    var var_tbl_metode_racik = $('#tbl_metode_racik').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['metode_racik','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_metode_racik = $('#search_field_metode_racik').val();
                var search_text_metode_racik = $('#search_text_metode_racik').val();
                
                data.search_field_metode_racik = search_field_metode_racik;
                data.search_text_metode_racik = search_text_metode_racik;
                
            }
        },
        "columns": [
            { 'data': 'kd_racik' },
            { 'data': 'nm_racik' }
        ],
        "columnDefs": [
            { 'targets': 0},
            { 'targets': 1}
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
        selector: '#tbl_metode_racik tr', 
        trigger: 'right',
        callback: function(key, options) {
            var rowData = var_tbl_metode_racik.rows({ selected: true }).data()[0];
            if (rowData != null) {
                var kd_racik = rowData['kd_racik'];
                switch (key) {
                    case 'detail' :
                        OpenModal(mlite.url + '/metode_racik/detail/' + kd_racik + '?t=' + mlite.token);
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

    $("form[name='form_metode_racik']").validate({
        rules: {
            kd_racik: 'required',
            nm_racik: 'required'
        },
        messages: {
            kd_racik:'Kd Racik tidak boleh kosong!',
            nm_racik:'Nm Racik tidak boleh kosong!'
        },
        submitHandler: function (form) {
            var kd_racik= $('#kd_racik').val();
            var nm_racik= $('#nm_racik').val();

            var typeact = $('#typeact').val();

            var formData = new FormData(form); // tambahan
            formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['metode_racik','aksi'])?}",
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
                            $("#modal_metode_racik").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_metode_racik").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    var_tbl_metode_racik.draw();
                }
            })
        }
    });

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_metode_racik').click(function () {
        var_tbl_metode_racik.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_metode_racik").click(function () {
        var rowData = var_tbl_metode_racik.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var kd_racik = rowData['kd_racik'];
            var nm_racik = rowData['nm_racik'];

            $("#typeact").val("edit");

            $('#kd_racik').val(kd_racik);
            $('#nm_racik').val(nm_racik);

            $("#kd_racik").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Metode Racik");
            $("#modal_metode_racik").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_metode_racik").click(function () {
        var rowData = var_tbl_metode_racik.rows({ selected: true }).data()[0];


        if (rowData) {
            var kd_racik = rowData['kd_racik'];
                bootbox.confirm('Anda yakin akan menghapus data dengan kd_racik="' + kd_racik, function(result) {
                    if(result) {
                        $.ajax({
                            url: "{?=url(['metode_racik','aksi'])?}",
                            method: "POST",
                            data: {
                                kd_racik: kd_racik,
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
                                var_tbl_metode_racik.draw();
                            }
                        })    
                    }
                });
        } else {
            bootbox.alert("Pilih satu baris untuk dihapus");
        }
    });

    // ==============================================================
    // TOMBOL TAMBAH DATA DI CLICK
    // ==============================================================
    jQuery("#tambah_data_metode_racik").click(function () {

        $('#kd_racik').val('');
        $('#nm_racik').val('');

        $("#typeact").val("add");
        $("#kd_racik").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Metode Racik");
        $("#modal_metode_racik").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_metode_racik").click(function () {

        var search_field_metode_racik = $('#search_field_metode_racik').val();
        var search_text_metode_racik = $('#search_text_metode_racik').val();

        $.ajax({
            url: "{?=url(['metode_racik','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_metode_racik: search_field_metode_racik, 
                search_text_metode_racik: search_text_metode_racik
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_metode_racik' class='table display dataTable' style='width:100%'><thead><th>Kd Racik</th><th>Nm Racik</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['kd_racik'] + '</td>';
eTable += '<td>' + res[i]['nm_racik'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_metode_racik').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_metode_racik").modal('show');
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
        doc.text("Tabel Data Metode Racik", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_metode_racik',
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
        // doc.save('table_data_metode_racik.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_metode_racik");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data metode_racik");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/metode_racik/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});