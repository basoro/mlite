jQuery().ready(function () {
    var var_tbl_penjab = $('#tbl_penjab').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['penjab','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_penjab = $('#search_field_penjab').val();
                var search_text_penjab = $('#search_text_penjab').val();
                
                data.search_field_penjab = search_field_penjab;
                data.search_text_penjab = search_text_penjab;
                
            }
        },
        "columns": [
            { 'data': 'kd_pj' },
            { 'data': 'png_jawab' },
            { 'data': 'nama_perusahaan' },
            { 'data': 'alamat_asuransi' },
            { 'data': 'no_telp' },
            { 'data': 'attn' },
            { 'data': 'status' }
        ],
        "columnDefs": [
            { 'targets': 0},
            { 'targets': 1},
            { 'targets': 2},
            { 'targets': 3},
            { 'targets': 4},
            { 'targets': 5},
            { 'targets': 6}
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
        selector: '#tbl_penjab tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_penjab.rows({ selected: true }).data()[0];
          if (rowData != null) {
            var kd_pj = rowData['kd_pj'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/penjab/detail/' + kd_pj + '?t=' + mlite.token);
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

    $("form[name='form_penjab']").validate({
        rules: {
            kd_pj: 'required',
            png_jawab: 'required',
            nama_perusahaan: 'required',
            alamat_asuransi: 'required',
            no_telp: 'required',
            attn: 'required',
            status: 'required'
        },
        messages: {
            kd_pj:'Kd Pj tidak boleh kosong!',
            png_jawab:'Png Jawab tidak boleh kosong!',
            nama_perusahaan:'Nama Perusahaan tidak boleh kosong!',
            alamat_asuransi:'Alamat Asuransi tidak boleh kosong!',
            no_telp:'No Telp tidak boleh kosong!',
            attn:'Attn tidak boleh kosong!',
            status:'Status tidak boleh kosong!'
        },
        submitHandler: function (form) {
            var kd_pj= $('#kd_pj').val();
            var png_jawab= $('#png_jawab').val();
            var nama_perusahaan= $('#nama_perusahaan').val();
            var alamat_asuransi= $('#alamat_asuransi').val();
            var no_telp= $('#no_telp').val();
            var attn= $('#attn').val();
            var status= $('#status').val();

            var typeact = $('#typeact').val();

            var formData = new FormData(form); // tambahan
            formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['penjab','aksi'])?}",
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
                            $("#modal_penjab").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_penjab").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    var_tbl_penjab.draw();
                }
            })
        }
    });

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_penjab').click(function () {
        var_tbl_penjab.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_penjab").click(function () {
        var rowData = var_tbl_penjab.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var kd_pj = rowData['kd_pj'];
            var png_jawab = rowData['png_jawab'];
            var nama_perusahaan = rowData['nama_perusahaan'];
            var alamat_asuransi = rowData['alamat_asuransi'];
            var no_telp = rowData['no_telp'];
            var attn = rowData['attn'];
            var status = rowData['status'];

            $("#typeact").val("edit");
  
            $('#kd_pj').val(kd_pj);
            $('#png_jawab').val(png_jawab);
            $('#nama_perusahaan').val(nama_perusahaan);
            $('#alamat_asuransi').val(alamat_asuransi);
            $('#no_telp').val(no_telp);
            $('#attn').val(attn);
            $('#status').val(status).change();

            $("#kd_pj").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Penjab");
            $("#modal_penjab").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_penjab").click(function () {
        var rowData = var_tbl_penjab.rows({ selected: true }).data()[0];


        if (rowData) {
            var kd_pj = rowData['kd_pj'];
            bootbox.confirm('Anda yakin akan menghapus data dengan kd_pj="' + kd_pj, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['penjab','aksi'])?}",
                        method: "POST",
                        data: {
                            kd_pj: kd_pj,
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
                            var_tbl_penjab.draw();
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
    jQuery("#tambah_data_penjab").click(function () {

        $('#kd_pj').val('');
        $('#png_jawab').val('');
        $('#nama_perusahaan').val('');
        $('#alamat_asuransi').val('');
        $('#no_telp').val('');
        $('#attn').val('');
        $('#status').val('').change();

        $("#typeact").val("add");
        $("#kd_pj").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Penjab");
        $("#modal_penjab").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_penjab").click(function () {

        var search_field_penjab = $('#search_field_penjab').val();
        var search_text_penjab = $('#search_text_penjab').val();

        $.ajax({
            url: "{?=url(['penjab','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_penjab: search_field_penjab, 
                search_text_penjab: search_text_penjab
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_penjab' class='table display dataTable' style='width:100%'><thead><th>Kd Pj</th><th>Png Jawab</th><th>Nama Perusahaan</th><th>Alamat Asuransi</th><th>No Telp</th><th>Attn</th><th>Status</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['kd_pj'] + '</td>';
                    eTable += '<td>' + res[i]['png_jawab'] + '</td>';
                    eTable += '<td>' + res[i]['nama_perusahaan'] + '</td>';
                    eTable += '<td>' + res[i]['alamat_asuransi'] + '</td>';
                    eTable += '<td>' + res[i]['no_telp'] + '</td>';
                    eTable += '<td>' + res[i]['attn'] + '</td>';
                    eTable += '<td>' + res[i]['status'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_penjab').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_penjab").modal('show');
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
        doc.text("Tabel Data Penjab", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_penjab',
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
        // doc.save('table_data_penjab.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_penjab");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data penjab");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/penjab/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});