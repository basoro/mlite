jQuery().ready(function () {
    var var_tbl_set_no_rkm_medis = $('#tbl_set_no_rkm_medis').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['set_no_rkm_medis','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_set_no_rkm_medis = $('#search_field_set_no_rkm_medis').val();
                var search_text_set_no_rkm_medis = $('#search_text_set_no_rkm_medis').val();
                
                data.search_field_set_no_rkm_medis = search_field_set_no_rkm_medis;
                data.search_text_set_no_rkm_medis = search_text_set_no_rkm_medis;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_set_no_rkm_medis').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_set_no_rkm_medis tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
            { 'data': 'no_rkm_medis' }
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
        selector: '#tbl_set_no_rkm_medis tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_set_no_rkm_medis.rows({ selected: true }).data()[0];
          if (rowData != null) {
            var no_rkm_medis = rowData['no_rkm_medis'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/set_no_rkm_medis/detail/' + no_rkm_medis + '?t=' + mlite.token);
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

    $("form[name='form_set_no_rkm_medis']").validate({
        rules: {
            no_rkm_medis: 'required'
        },
        messages: {
            no_rkm_medis:'No Rekam Medis tidak boleh kosong!'
        },
        submitHandler: function (form) {
            var no_rkm_medis= $('#no_rkm_medis').val();

            var typeact = $('#typeact').val();

            var formData = new FormData(form); // tambahan
            formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['set_no_rkm_medis','aksi'])?}",
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
                            $("#modal_set_no_rkm_medis").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_set_no_rkm_medis").modal('hide');
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
                    var_tbl_set_no_rkm_medis.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_set_no_rkm_medis.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_set_no_rkm_medis.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_set_no_rkm_medis.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_set_no_rkm_medis').click(function () {
        var_tbl_set_no_rkm_medis.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_set_no_rkm_medis").click(function () {
        var rowData = var_tbl_set_no_rkm_medis.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var no_rkm_medis = rowData['no_rkm_medis'];

            $("#typeact").val("edit");
  
            $('#no_rkm_medis').val(no_rkm_medis);

            $("#no_rkm_medis").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Set No Rkm Medis");
            $("#modal_set_no_rkm_medis").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_set_no_rkm_medis").click(function () {
        var rowData = var_tbl_set_no_rkm_medis.rows({ selected: true }).data()[0];


        if (rowData) {
            var no_rkm_medis = rowData['no_rkm_medis'];
            bootbox.confirm('Anda yakin akan menghapus data dengan no_rkm_medis="' + no_rkm_medis, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['set_no_rkm_medis','aksi'])?}",
                        method: "POST",
                        data: {
                            no_rkm_medis: no_rkm_medis,
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
                            if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
                                let payload = {
                                    'action' : 'del'
                                }
                                ws.send(JSON.stringify(payload));
                            }
                            var_tbl_set_no_rkm_medis.draw();
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
    jQuery("#tambah_data_set_no_rkm_medis").click(function () {

        $('#no_rkm_medis').val('');

        $("#typeact").val("add");
        $("#no_rkm_medis").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Set No Rkm Medis");
        $("#modal_set_no_rkm_medis").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_set_no_rkm_medis").click(function () {

        var search_field_set_no_rkm_medis = $('#search_field_set_no_rkm_medis').val();
        var search_text_set_no_rkm_medis = $('#search_text_set_no_rkm_medis').val();

        $.ajax({
            url: "{?=url(['set_no_rkm_medis','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_set_no_rkm_medis: search_field_set_no_rkm_medis, 
                search_text_set_no_rkm_medis: search_text_set_no_rkm_medis
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_set_no_rkm_medis' class='table display dataTable' style='width:100%'><thead><th>No Rekam Medis</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['no_rkm_medis'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_set_no_rkm_medis').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_set_no_rkm_medis").modal('show');
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
        doc.text("Tabel Data Set No Rekam Medis", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_set_no_rkm_medis',
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
        // doc.save('table_data_set_no_rkm_medis.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_set_no_rkm_medis");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data set_no_rkm_medis");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/set_no_rkm_medis/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});