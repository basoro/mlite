jQuery().ready(function () {
    var var_tbl_pendidikan = $('#tbl_pendidikan').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['pendidikan','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_pendidikan = $('#search_field_pendidikan').val();
                var search_text_pendidikan = $('#search_text_pendidikan').val();
                
                data.search_field_pendidikan = search_field_pendidikan;
                data.search_text_pendidikan = search_text_pendidikan;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_pendidikan').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_pendidikan tr').contextMenu({x: clientX, y: clientY});
            });          
        },        
        "columns": [
            { 'data': 'tingkat' },
            { 'data': 'indek' },
            { 'data': 'gapok1' },
            { 'data': 'kenaikan' },
            { 'data': 'maksimal' }

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
        selector: '#tbl_pendidikan tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_pendidikan.rows({ selected: true }).data()[0];
          if (rowData != null) {
            var tingkat = rowData['tingkat'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/pendidikan/detail/' + tingkat + '?t=' + mlite.token);
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

    $("form[name='form_pendidikan']").validate({
        rules: {
            tingkat: 'required',
            indek: 'required',
            gapok1: 'required',
            kenaikan: 'required',
            maksimal: 'required'

        },
        messages: {
            tingkat:'Tingkat tidak boleh kosong!',
            indek:'Indek tidak boleh kosong!',
            gapok1:'Gapok1 tidak boleh kosong!',
            kenaikan:'Kenaikan tidak boleh kosong!',
            maksimal:'Maksimal tidak boleh kosong!'

        },
        submitHandler: function (form) {
            var tingkat= $('#tingkat').val();
            var indek= $('#indek').val();
            var gapok1= $('#gapok1').val();
            var kenaikan= $('#kenaikan').val();
            var maksimal= $('#maksimal').val();

            var typeact = $('#typeact').val();

            var formData = new FormData(form); // tambahan
            formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['pendidikan','aksi'])?}",
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
                            $("#modal_pendidikan").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_pendidikan").modal('hide');
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
                    var_tbl_pendidikan.draw();
                }
            })
        }
    });


    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_pendidikan.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_pendidikan.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_pendidikan.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_pendidikan').click(function () {
        var_tbl_pendidikan.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_pendidikan").click(function () {
        var rowData = var_tbl_pendidikan.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var tingkat = rowData['tingkat'];
            var indek = rowData['indek'];
            var gapok1 = rowData['gapok1'];
            var kenaikan = rowData['kenaikan'];
            var maksimal = rowData['maksimal'];

            $("#typeact").val("edit");
  
            $('#tingkat').val(tingkat);
            $('#indek').val(indek);
            $('#gapok1').val(gapok1);
            $('#kenaikan').val(kenaikan);
            $('#maksimal').val(maksimal);

            $("#tingkat").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Pendidikan");
            $("#modal_pendidikan").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_pendidikan").click(function () {
        var rowData = var_tbl_pendidikan.rows({ selected: true }).data()[0];


        if (rowData) {
            var tingkat = rowData['tingkat'];
            bootbox.confirm('Anda yakin akan menghapus data dengan tingkat="' + tingkat, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['pendidikan','aksi'])?}",
                        method: "POST",
                        data: {
                            tingkat: tingkat,
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
                            var_tbl_pendidikan.draw();
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
    jQuery("#tambah_data_pendidikan").click(function () {

        $('#tingkat').val('');
        $('#indek').val('');
        $('#gapok1').val('');
        $('#kenaikan').val('');
        $('#maksimal').val('');

        $("#typeact").val("add");
        $("#tingkat").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Pendidikan");
        $("#modal_pendidikan").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_pendidikan").click(function () {

        var search_field_pendidikan = $('#search_field_pendidikan').val();
        var search_text_pendidikan = $('#search_text_pendidikan').val();

        $.ajax({
            url: "{?=url(['pendidikan','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_pendidikan: search_field_pendidikan, 
                search_text_pendidikan: search_text_pendidikan
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_pendidikan' class='table display dataTable' style='width:100%'><thead><th>Tingkat</th><th>Indek</th><th>Gapok1</th><th>Kenaikan</th><th>Maksimal</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['tingkat'] + '</td>';
                    eTable += '<td>' + res[i]['indek'] + '</td>';
                    eTable += '<td>' + res[i]['gapok1'] + '</td>';
                    eTable += '<td>' + res[i]['kenaikan'] + '</td>';
                    eTable += '<td>' + res[i]['maksimal'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_pendidikan').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_pendidikan").modal('show');
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
        doc.text("Tabel Data Pendidikan", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_pendidikan',
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
        // doc.save('table_data_pendidikan.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_pendidikan");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data pendidikan");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/pendidikan/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});