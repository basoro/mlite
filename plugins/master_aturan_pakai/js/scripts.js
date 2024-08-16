jQuery().ready(function () {
    var var_tbl_master_aturan_pakai = $('#tbl_master_aturan_pakai').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['master_aturan_pakai','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_master_aturan_pakai = $('#search_field_master_aturan_pakai').val();
                var search_text_master_aturan_pakai = $('#search_text_master_aturan_pakai').val();
                
                data.search_field_master_aturan_pakai = search_field_master_aturan_pakai;
                data.search_text_master_aturan_pakai = search_text_master_aturan_pakai;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_master_aturan_pakai').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_master_aturan_pakai tr').contextMenu({x: clientX, y: clientY});
            });          
        },        
        "columns": [
            { 'data': 'aturan' }

        ],
        "columnDefs": [
            { 'targets': 0}

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
        selector: '#tbl_master_aturan_pakai tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_master_aturan_pakai.rows({ selected: true }).data()[0];
          if (rowData != null) {
            var aturan = rowData['aturan'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/master_aturan_pakai/detail/' + aturan + '?t=' + mlite.token);
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

    $("form[name='form_master_aturan_pakai']").validate({
        rules: {
            aturan: 'required'

        },
        messages: {
            aturan:'Aturan tidak boleh kosong!'

        },
        submitHandler: function (form) {
            var aturan= $('#aturan').val();

            var typeact = $('#typeact').val();

            var formData = new FormData(form); // tambahan
            formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['master_aturan_pakai','aksi'])?}",
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
                            $("#modal_master_aturan_pakai").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_master_aturan_pakai").modal('hide');
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
                    var_tbl_master_aturan_pakai.draw();
                }
            })
        }
    });


    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_master_aturan_pakai.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_master_aturan_pakai.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_master_aturan_pakai.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_master_aturan_pakai').click(function () {
        var_tbl_master_aturan_pakai.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_master_aturan_pakai").click(function () {
        var rowData = var_tbl_master_aturan_pakai.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var aturan = rowData['aturan'];

            $("#typeact").val("edit");
  
            $('#aturan').val(aturan);

            $("#aturan").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Master Aturan Pakai");
            $("#modal_master_aturan_pakai").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_master_aturan_pakai").click(function () {
        var rowData = var_tbl_master_aturan_pakai.rows({ selected: true }).data()[0];


        if (rowData) {
            var aturan = rowData['aturan'];
            bootbox.confirm('Anda yakin akan menghapus data dengan aturan="' + aturan, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['master_aturan_pakai','aksi'])?}",
                        method: "POST",
                        data: {
                            aturan: aturan,
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
                            var_tbl_master_aturan_pakai.draw();
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
    jQuery("#tambah_data_master_aturan_pakai").click(function () {

        $('#aturan').val('');

        $("#typeact").val("add");
        $("#aturan").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Master Aturan Pakai");
        $("#modal_master_aturan_pakai").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_master_aturan_pakai").click(function () {

        var search_field_master_aturan_pakai = $('#search_field_master_aturan_pakai').val();
        var search_text_master_aturan_pakai = $('#search_text_master_aturan_pakai').val();

        $.ajax({
            url: "{?=url(['master_aturan_pakai','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_master_aturan_pakai: search_field_master_aturan_pakai, 
                search_text_master_aturan_pakai: search_text_master_aturan_pakai
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_master_aturan_pakai' class='table display dataTable' style='width:100%'><thead><th>Aturan</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['aturan'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_master_aturan_pakai').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_master_aturan_pakai").modal('show');
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
        doc.text("Tabel Data Master Aturan Pakai", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_master_aturan_pakai',
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
        // doc.save('table_data_master_aturan_pakai.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_master_aturan_pakai");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data master_aturan_pakai");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/master_aturan_pakai/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});