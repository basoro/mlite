jQuery().ready(function () {
    var var_tbl_maping_poliklinik_pcare = $('#tbl_maping_poliklinik_pcare').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['maping_poliklinik_pcare','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_maping_poliklinik_pcare = $('#search_field_maping_poliklinik_pcare').val();
                var search_text_maping_poliklinik_pcare = $('#search_text_maping_poliklinik_pcare').val();
                
                data.search_field_maping_poliklinik_pcare = search_field_maping_poliklinik_pcare;
                data.search_text_maping_poliklinik_pcare = search_text_maping_poliklinik_pcare;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_maping_poliklinik_pcare').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_maping_poliklinik_pcare tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'kd_poli_rs' },
{ 'data': 'kd_poli_pcare' },
{ 'data': 'nm_poli_pcare' }

        ],
        "columnDefs": [
{ 'targets': 0},
{ 'targets': 1},
{ 'targets': 2}

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
        selector: '#tbl_maping_poliklinik_pcare tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_maping_poliklinik_pcare.rows({ selected: true }).data()[0];
          if (rowData != null) {
var kd_poli_rs = rowData['kd_poli_rs'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/maping_poliklinik_pcare/detail/' + kd_poli_rs + '?t=' + mlite.token);
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

    $("form[name='form_maping_poliklinik_pcare']").validate({
        rules: {
kd_poli_rs: 'required',
kd_poli_pcare: 'required',
nm_poli_pcare: 'required'

        },
        messages: {
kd_poli_rs:'Kd Poli Rs tidak boleh kosong!',
kd_poli_pcare:'Kd Poli Pcare tidak boleh kosong!',
nm_poli_pcare:'Nm Poli Pcare tidak boleh kosong!'

        },
        submitHandler: function (form) {
var kd_poli_rs= $('#kd_poli_rs').val();
var kd_poli_pcare= $('#kd_poli_pcare').val();
var nm_poli_pcare= $('#nm_poli_pcare').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['maping_poliklinik_pcare','aksi'])?}",
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
                            $("#modal_maping_poliklinik_pcare").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_maping_poliklinik_pcare").modal('hide');
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
                    var_tbl_maping_poliklinik_pcare.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_maping_poliklinik_pcare.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_maping_poliklinik_pcare.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_maping_poliklinik_pcare.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_maping_poliklinik_pcare').click(function () {
        var_tbl_maping_poliklinik_pcare.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_maping_poliklinik_pcare").click(function () {
        var rowData = var_tbl_maping_poliklinik_pcare.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var kd_poli_rs = rowData['kd_poli_rs'];
var kd_poli_pcare = rowData['kd_poli_pcare'];
var nm_poli_pcare = rowData['nm_poli_pcare'];

            $("#typeact").val("edit");
  
            $('#kd_poli_rs').val(kd_poli_rs);
$('#kd_poli_pcare').val(kd_poli_pcare);
$('#nm_poli_pcare').val(nm_poli_pcare);

            $("#kd_poli_rs").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Maping Poliklinik Pcare");
            $("#modal_maping_poliklinik_pcare").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_maping_poliklinik_pcare").click(function () {
        var rowData = var_tbl_maping_poliklinik_pcare.rows({ selected: true }).data()[0];


        if (rowData) {
var kd_poli_rs = rowData['kd_poli_rs'];
            bootbox.confirm('Anda yakin akan menghapus data dengan kd_poli_rs="' + kd_poli_rs, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['maping_poliklinik_pcare','aksi'])?}",
                        method: "POST",
                        data: {
                            kd_poli_rs: kd_poli_rs,
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
                            var_tbl_maping_poliklinik_pcare.draw();
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
    jQuery("#tambah_data_maping_poliklinik_pcare").click(function () {

        $('#kd_poli_rs').val('');
$('#kd_poli_pcare').val('');
$('#nm_poli_pcare').val('');

        $("#typeact").val("add");
        $("#kd_poli_rs").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Maping Poliklinik Pcare");
        $("#modal_maping_poliklinik_pcare").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_maping_poliklinik_pcare").click(function () {

        var search_field_maping_poliklinik_pcare = $('#search_field_maping_poliklinik_pcare').val();
        var search_text_maping_poliklinik_pcare = $('#search_text_maping_poliklinik_pcare').val();

        $.ajax({
            url: "{?=url(['maping_poliklinik_pcare','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_maping_poliklinik_pcare: search_field_maping_poliklinik_pcare, 
                search_text_maping_poliklinik_pcare: search_text_maping_poliklinik_pcare
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_maping_poliklinik_pcare' class='table display dataTable' style='width:100%'><thead><th>Kd Poli Rs</th><th>Kd Poli Pcare</th><th>Nm Poli Pcare</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['kd_poli_rs'] + '</td>';
eTable += '<td>' + res[i]['kd_poli_pcare'] + '</td>';
eTable += '<td>' + res[i]['nm_poli_pcare'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_maping_poliklinik_pcare').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_maping_poliklinik_pcare").modal('show');
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
        doc.text("Tabel Data Maping Poliklinik Pcare", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_maping_poliklinik_pcare',
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
        // doc.save('table_data_maping_poliklinik_pcare.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_maping_poliklinik_pcare");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data maping_poliklinik_pcare");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/maping_poliklinik_pcare/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});