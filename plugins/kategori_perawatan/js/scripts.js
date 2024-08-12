jQuery().ready(function () {
    var var_tbl_kategori_perawatan = $('#tbl_kategori_perawatan').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['kategori_perawatan','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_kategori_perawatan = $('#search_field_kategori_perawatan').val();
                var search_text_kategori_perawatan = $('#search_text_kategori_perawatan').val();
                
                data.search_field_kategori_perawatan = search_field_kategori_perawatan;
                data.search_text_kategori_perawatan = search_text_kategori_perawatan;
                
            }
        },
        "columns": [
            { 'data': 'kd_kategori' },
            { 'data': 'nm_kategori' }

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
        selector: '#tbl_kategori_perawatan tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_kategori_perawatan.rows({ selected: true }).data()[0];
          if (rowData != null) {
                var kd_kategori = rowData['kd_kategori'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/kategori_perawatan/detail/' + kd_kategori + '?t=' + mlite.token);
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

    $("form[name='form_kategori_perawatan']").validate({
        rules: {
            kd_kategori: 'required',
            nm_kategori: 'required'

        },
        messages: {
            kd_kategori:'Kd Kategori tidak boleh kosong!',
            nm_kategori:'Nm Kategori tidak boleh kosong!'

        },
        submitHandler: function (form) {
            var kd_kategori= $('#kd_kategori').val();
            var nm_kategori= $('#nm_kategori').val();

            var typeact = $('#typeact').val();

            var formData = new FormData(form); // tambahan
            formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['kategori_perawatan','aksi'])?}",
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
                            $("#modal_kategori_perawatan").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_kategori_perawatan").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    var_tbl_kategori_perawatan.draw();
                }
            })
        }
    });

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_kategori_perawatan').click(function () {
        var_tbl_kategori_perawatan.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_kategori_perawatan").click(function () {
        var rowData = var_tbl_kategori_perawatan.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var kd_kategori = rowData['kd_kategori'];
            var nm_kategori = rowData['nm_kategori'];

            $("#typeact").val("edit");
  
            $('#kd_kategori').val(kd_kategori);
            $('#nm_kategori').val(nm_kategori);

            $("#kd_kategori").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Kategori Perawatan");
            $("#modal_kategori_perawatan").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_kategori_perawatan").click(function () {
        var rowData = var_tbl_kategori_perawatan.rows({ selected: true }).data()[0];


        if (rowData) {
            var kd_kategori = rowData['kd_kategori'];
            bootbox.confirm('Anda yakin akan menghapus data dengan kd_kategori="' + kd_kategori, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['kategori_perawatan','aksi'])?}",
                        method: "POST",
                        data: {
                            kd_kategori: kd_kategori,
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
                            var_tbl_kategori_perawatan.draw();
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
    jQuery("#tambah_data_kategori_perawatan").click(function () {

        $('#kd_kategori').val('');
        $('#nm_kategori').val('');

        $("#typeact").val("add");
        $("#kd_kategori").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Kategori Perawatan");
        $("#modal_kategori_perawatan").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_kategori_perawatan").click(function () {

        var search_field_kategori_perawatan = $('#search_field_kategori_perawatan').val();
        var search_text_kategori_perawatan = $('#search_text_kategori_perawatan').val();

        $.ajax({
            url: "{?=url(['kategori_perawatan','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_kategori_perawatan: search_field_kategori_perawatan, 
                search_text_kategori_perawatan: search_text_kategori_perawatan
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_kategori_perawatan' class='table display dataTable' style='width:100%'><thead><th>Kd Kategori</th><th>Nm Kategori</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['kd_kategori'] + '</td>';
                    eTable += '<td>' + res[i]['nm_kategori'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_kategori_perawatan').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_kategori_perawatan").modal('show');
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
        doc.text("Tabel Data Kategori Perawatan", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_kategori_perawatan',
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
        // doc.save('table_data_kategori_perawatan.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_kategori_perawatan");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data kategori_perawatan");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/kategori_perawatan/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});