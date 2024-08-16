jQuery().ready(function () {
    var var_tbl_jns_perawatan_inap = $('#tbl_jns_perawatan_inap').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['jns_perawatan_inap','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_jns_perawatan_inap = $('#search_field_jns_perawatan_inap').val();
                var search_text_jns_perawatan_inap = $('#search_text_jns_perawatan_inap').val();
                
                data.search_field_jns_perawatan_inap = search_field_jns_perawatan_inap;
                data.search_text_jns_perawatan_inap = search_text_jns_perawatan_inap;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_jns_perawatan_inap').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_jns_perawatan_inap tr').contextMenu({x: clientX, y: clientY});
            });          
        },        
        "columns": [
            { 'data': 'kd_jenis_prw' },
            { 'data': 'nm_perawatan' },
            { 'data': 'kd_kategori' },
            { 'data': 'material' },
            { 'data': 'bhp' },
            { 'data': 'tarif_tindakandr' },
            { 'data': 'tarif_tindakanpr' },
            { 'data': 'kso' },
            { 'data': 'menejemen' },
            { 'data': 'total_byrdr' },
            { 'data': 'total_byrpr' },
            { 'data': 'total_byrdrpr' },
            { 'data': 'kd_pj' },
            { 'data': 'kd_bangsal' },
            { 'data': 'status', 
                "render": function (data) {
                    if(data == '1') {
                        var status = 'Aktif';
                    } else {
                        var status = 'Tidak Aktif';
                    }
                    return status;
                }      
            },
            { 'data': 'kelas' }
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
            { 'targets': 13},
            { 'targets': 14},
            { 'targets': 15}
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
        selector: '#tbl_jns_perawatan_inap tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_jns_perawatan_inap.rows({ selected: true }).data()[0];
          if (rowData != null) {
            var kd_jenis_prw = rowData['kd_jenis_prw'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/jns_perawatan_inap/detail/' + kd_jenis_prw + '?t=' + mlite.token);
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

    $("form[name='form_jns_perawatan_inap']").validate({
        rules: {
            kd_jenis_prw: 'required',
            nm_perawatan: 'required',
            kd_kategori: 'required',
            material: 'required',
            bhp: 'required',
            tarif_tindakandr: 'required',
            tarif_tindakanpr: 'required',
            kso: 'required',
            menejemen: 'required',
            total_byrdr: 'required',
            total_byrpr: 'required',
            total_byrdrpr: 'required',
            kd_pj: 'required',
            kd_bangsal: 'required',
            status: 'required',
            kelas: 'required'
        },
        messages: {
            kd_jenis_prw:'Kd Jenis Prw tidak boleh kosong!',
            nm_perawatan:'Nm Perawatan tidak boleh kosong!',
            kd_kategori:'Kd Kategori tidak boleh kosong!',
            material:'Material tidak boleh kosong!',
            bhp:'Bhp tidak boleh kosong!',
            tarif_tindakandr:'Tarif Tindakandr tidak boleh kosong!',
            tarif_tindakanpr:'Tarif Tindakanpr tidak boleh kosong!',
            kso:'Kso tidak boleh kosong!',
            menejemen:'Menejemen tidak boleh kosong!',
            total_byrdr:'Total Byrdr tidak boleh kosong!',
            total_byrpr:'Total Byrpr tidak boleh kosong!',
            total_byrdrpr:'Total Byrdrpr tidak boleh kosong!',
            kd_pj:'Kd Pj tidak boleh kosong!',
            kd_bangsal:'Kd Bangsal tidak boleh kosong!',
            status:'Status tidak boleh kosong!',
            kelas:'Kelas tidak boleh kosong!'
        },
        submitHandler: function (form) {
            var kd_jenis_prw= $('#kd_jenis_prw').val();
            var nm_perawatan= $('#nm_perawatan').val();
            var kd_kategori= $('#kd_kategori').val();
            var material= $('#material').val();
            var bhp= $('#bhp').val();
            var tarif_tindakandr= $('#tarif_tindakandr').val();
            var tarif_tindakanpr= $('#tarif_tindakanpr').val();
            var kso= $('#kso').val();
            var menejemen= $('#menejemen').val();
            var total_byrdr= $('#total_byrdr').val();
            var total_byrpr= $('#total_byrpr').val();
            var total_byrdrpr= $('#total_byrdrpr').val();
            var kd_pj= $('#kd_pj').val();
            var kd_bangsal= $('#kd_bangsal').val();
            var status= $('#status').val();
            var kelas= $('#kelas').val();

            var typeact = $('#typeact').val();

            var formData = new FormData(form); // tambahan
            formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['jns_perawatan_inap','aksi'])?}",
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
                            $("#modal_jns_perawatan_inap").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_jns_perawatan_inap").modal('hide');
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
                    var_tbl_jns_perawatan_inap.draw();
                }
            })
        }
    });


    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_jns_perawatan_inap.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_jns_perawatan_inap.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_jns_perawatan_inap.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_jns_perawatan_inap').click(function () {
        var_tbl_jns_perawatan_inap.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_jns_perawatan_inap").click(function () {
        var rowData = var_tbl_jns_perawatan_inap.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var kd_jenis_prw = rowData['kd_jenis_prw'];
            var nm_perawatan = rowData['nm_perawatan'];
            var kd_kategori = rowData['kd_kategori'];
            var material = rowData['material'];
            var bhp = rowData['bhp'];
            var tarif_tindakandr = rowData['tarif_tindakandr'];
            var tarif_tindakanpr = rowData['tarif_tindakanpr'];
            var kso = rowData['kso'];
            var menejemen = rowData['menejemen'];
            var total_byrdr = rowData['total_byrdr'];
            var total_byrpr = rowData['total_byrpr'];
            var total_byrdrpr = rowData['total_byrdrpr'];
            var kd_pj = rowData['kd_pj'];
            var kd_bangsal = rowData['kd_bangsal'];
            var status = rowData['status'];
            var kelas = rowData['kelas'];

            $("#typeact").val("edit");
  
            $('#kd_jenis_prw').val(kd_jenis_prw);
            $('#nm_perawatan').val(nm_perawatan);
            $('#kd_kategori').val(kd_kategori).change();
            $('#material').val(material);
            $('#bhp').val(bhp);
            $('#tarif_tindakandr').val(tarif_tindakandr);
            $('#tarif_tindakanpr').val(tarif_tindakanpr);
            $('#kso').val(kso);
            $('#menejemen').val(menejemen);
            $('#total_byrdr').val(total_byrdr);
            $('#total_byrpr').val(total_byrpr);
            $('#total_byrdrpr').val(total_byrdrpr);
            $('#kd_pj').val(kd_pj).change();
            $('#kd_bangsal').val(kd_bangsal).change();
            $('#status').val(status).change();
            $('#kelas').val(kelas).change();

            //$("#kd_jenis_prw").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Jns Perawatan Inap");
            $("#modal_jns_perawatan_inap").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_jns_perawatan_inap").click(function () {
        var rowData = var_tbl_jns_perawatan_inap.rows({ selected: true }).data()[0];


        if (rowData) {
            var kd_jenis_prw = rowData['kd_jenis_prw'];
            bootbox.confirm('Anda yakin akan menghapus data dengan kd_jenis_prw="' + kd_jenis_prw, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['jns_perawatan_inap','aksi'])?}",
                        method: "POST",
                        data: {
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
                            var_tbl_jns_perawatan_inap.draw();
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
    jQuery("#tambah_data_jns_perawatan_inap").click(function () {

        $('#kd_jenis_prw').val('{?=$this->core->setKodeJnsPerawatanInap()?}');
        $('#nm_perawatan').val('');
        $('#kd_kategori').val('').change();
        $('#material').val('0');
        $('#bhp').val('0');
        $('#tarif_tindakandr').val('0');
        $('#tarif_tindakanpr').val('0');
        $('#kso').val('0');
        $('#menejemen').val('0');
        $('#total_byrdr').val('0');
        $('#total_byrpr').val('0');
        $('#total_byrdrpr').val('0');
        $('#kd_pj').val('').change();
        $('#kd_bangsal').val('').change();
        $('#status').val('').change();
        $('#kelas').val('').change();

        $("#typeact").val("add");
        $("#kd_jenis_prw").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Jns Perawatan Inap");
        $("#modal_jns_perawatan_inap").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_jns_perawatan_inap").click(function () {

        var search_field_jns_perawatan_inap = $('#search_field_jns_perawatan_inap').val();
        var search_text_jns_perawatan_inap = $('#search_text_jns_perawatan_inap').val();

        $.ajax({
            url: "{?=url(['jns_perawatan_inap','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_jns_perawatan_inap: search_field_jns_perawatan_inap, 
                search_text_jns_perawatan_inap: search_text_jns_perawatan_inap
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_jns_perawatan_inap' class='table display dataTable' style='width:100%'><thead><th>Kd Jenis Prw</th><th>Nm Perawatan</th><th>Kd Kategori</th><th>Material</th><th>Bhp</th><th>Tarif Tindakandr</th><th>Tarif Tindakanpr</th><th>Kso</th><th>Menejemen</th><th>Total Byrdr</th><th>Total Byrpr</th><th>Total Byrdrpr</th><th>Kd Pj</th><th>Kd Bangsal</th><th>Status</th><th>Kelas</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['kd_jenis_prw'] + '</td>';
                    eTable += '<td>' + res[i]['nm_perawatan'] + '</td>';
                    eTable += '<td>' + res[i]['kd_kategori'] + '</td>';
                    eTable += '<td>' + res[i]['material'] + '</td>';
                    eTable += '<td>' + res[i]['bhp'] + '</td>';
                    eTable += '<td>' + res[i]['tarif_tindakandr'] + '</td>';
                    eTable += '<td>' + res[i]['tarif_tindakanpr'] + '</td>';
                    eTable += '<td>' + res[i]['kso'] + '</td>';
                    eTable += '<td>' + res[i]['menejemen'] + '</td>';
                    eTable += '<td>' + res[i]['total_byrdr'] + '</td>';
                    eTable += '<td>' + res[i]['total_byrpr'] + '</td>';
                    eTable += '<td>' + res[i]['total_byrdrpr'] + '</td>';
                    eTable += '<td>' + res[i]['kd_pj'] + '</td>';
                    eTable += '<td>' + res[i]['kd_bangsal'] + '</td>';
                    eTable += '<td>' + res[i]['status'] + '</td>';
                    eTable += '<td>' + res[i]['kelas'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_jns_perawatan_inap').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_jns_perawatan_inap").modal('show');
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
        doc.text("Tabel Data Jns Perawatan Inap", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_jns_perawatan_inap',
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
        // doc.save('table_data_jns_perawatan_inap.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_jns_perawatan_inap");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data jns_perawatan_inap");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/jns_perawatan_inap/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

    $('#total_byrdr').on('keyup', function() {
        var material = parseInt($('#material').val());
        var bhp = parseInt($('#bhp').val());
        var kso = parseInt($('#kso').val());
        var menejemen = parseInt($('#menejemen').val());
        var tarif_tindakandr = parseInt($('#tarif_tindakandr').val());
        var total_byrdr = '0';
        if(tarif_tindakandr !='') {
            var total_byrdr = (((material + bhp) + kso) + menejemen) + tarif_tindakandr;
        }
        $('#total_byrdr').val(total_byrdr);
    })

    $('#total_byrpr').on('keyup', function() {
        var material = parseInt($('#material').val());
        var bhp = parseInt($('#bhp').val());
        var kso = parseInt($('#kso').val());
        var menejemen = parseInt($('#menejemen').val());
        var tarif_tindakanpr = parseInt($('#tarif_tindakanpr').val());
        var total_byrpr = '0';
        if(tarif_tindakanpr != '') {
            var total_byrpr = (((material + bhp) + kso) + menejemen) + tarif_tindakanpr;
        }
        $('#total_byrpr').val(total_byrpr);
    })

    $('#total_byrdrpr').on('keyup', function() {
        var material = parseInt($('#material').val());
        var bhp = parseInt($('#bhp').val());
        var kso = parseInt($('#kso').val());
        var menejemen = parseInt($('#menejemen').val());
        var tarif_tindakandr = '0';
        var tarif_tindakanpr = '0';
        if(tarif_tindakandr !='' && tarif_tindakanpr !='') {
            var tarif_tindakandr = parseInt($('#tarif_tindakandr').val());
            var tarif_tindakanpr = parseInt($('#tarif_tindakanpr').val());    
        }
        var total_byrdrpr = ((((material + bhp) + kso) + menejemen) + tarif_tindakandr) + tarif_tindakanpr;
        $('#total_byrdrpr').val(total_byrdrpr);
    })  

});