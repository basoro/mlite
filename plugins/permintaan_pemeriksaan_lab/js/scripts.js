jQuery().ready(function () {
    var var_tbl_permintaan_pemeriksaan_lab = $('#tbl_permintaan_pemeriksaan_lab').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['permintaan_pemeriksaan_lab','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_permintaan_pemeriksaan_lab = $('#search_field_permintaan_pemeriksaan_lab').val();
                var search_text_permintaan_pemeriksaan_lab = $('#search_text_permintaan_pemeriksaan_lab').val();
                
                data.search_field_permintaan_pemeriksaan_lab = search_field_permintaan_pemeriksaan_lab;
                data.search_text_permintaan_pemeriksaan_lab = search_text_permintaan_pemeriksaan_lab;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_permintaan_pemeriksaan_lab').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_permintaan_pemeriksaan_lab tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'noorder' },
{ 'data': 'kd_jenis_prw' },
{ 'data': 'stts_bayar' }

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
        selector: '#tbl_permintaan_pemeriksaan_lab tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_permintaan_pemeriksaan_lab.rows({ selected: true }).data()[0];
          if (rowData != null) {
var noorder = rowData['noorder'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/permintaan_pemeriksaan_lab/detail/' + noorder + '?t=' + mlite.token);
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

    $("form[name='form_permintaan_pemeriksaan_lab']").validate({
        rules: {
noorder: 'required',
kd_jenis_prw: 'required',
stts_bayar: 'required'

        },
        messages: {
noorder:'Noorder tidak boleh kosong!',
kd_jenis_prw:'Kd Jenis Prw tidak boleh kosong!',
stts_bayar:'Stts Bayar tidak boleh kosong!'

        },
        submitHandler: function (form) {
var noorder= $('#noorder').val();
var kd_jenis_prw= $('#kd_jenis_prw').val();
var stts_bayar= $('#stts_bayar').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['permintaan_pemeriksaan_lab','aksi'])?}",
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
                            $("#modal_permintaan_pemeriksaan_lab").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_permintaan_pemeriksaan_lab").modal('hide');
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
                    var_tbl_permintaan_pemeriksaan_lab.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_permintaan_pemeriksaan_lab.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_permintaan_pemeriksaan_lab.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_permintaan_pemeriksaan_lab.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_permintaan_pemeriksaan_lab').click(function () {
        var_tbl_permintaan_pemeriksaan_lab.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_permintaan_pemeriksaan_lab").click(function () {
        var rowData = var_tbl_permintaan_pemeriksaan_lab.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var noorder = rowData['noorder'];
var kd_jenis_prw = rowData['kd_jenis_prw'];
var stts_bayar = rowData['stts_bayar'];

            $("#typeact").val("edit");
  
            $('#noorder').val(noorder);
$('#kd_jenis_prw').val(kd_jenis_prw);
$('#stts_bayar').val(stts_bayar);

            $("#noorder").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Permintaan Pemeriksaan Lab");
            $("#modal_permintaan_pemeriksaan_lab").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_permintaan_pemeriksaan_lab").click(function () {
        var rowData = var_tbl_permintaan_pemeriksaan_lab.rows({ selected: true }).data()[0];


        if (rowData) {
var noorder = rowData['noorder'];
            bootbox.confirm('Anda yakin akan menghapus data dengan noorder="' + noorder, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['permintaan_pemeriksaan_lab','aksi'])?}",
                        method: "POST",
                        data: {
                            noorder: noorder,
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
                            var_tbl_permintaan_pemeriksaan_lab.draw();
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
    jQuery("#tambah_data_permintaan_pemeriksaan_lab").click(function () {

        $('#noorder').val('');
$('#kd_jenis_prw').val('');
$('#stts_bayar').val('');

        $("#typeact").val("add");
        $("#noorder").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Permintaan Pemeriksaan Lab");
        $("#modal_permintaan_pemeriksaan_lab").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_permintaan_pemeriksaan_lab").click(function () {

        var search_field_permintaan_pemeriksaan_lab = $('#search_field_permintaan_pemeriksaan_lab').val();
        var search_text_permintaan_pemeriksaan_lab = $('#search_text_permintaan_pemeriksaan_lab').val();

        $.ajax({
            url: "{?=url(['permintaan_pemeriksaan_lab','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_permintaan_pemeriksaan_lab: search_field_permintaan_pemeriksaan_lab, 
                search_text_permintaan_pemeriksaan_lab: search_text_permintaan_pemeriksaan_lab
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_permintaan_pemeriksaan_lab' class='table display dataTable' style='width:100%'><thead><th>Noorder</th><th>Kd Jenis Prw</th><th>Stts Bayar</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['noorder'] + '</td>';
eTable += '<td>' + res[i]['kd_jenis_prw'] + '</td>';
eTable += '<td>' + res[i]['stts_bayar'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_permintaan_pemeriksaan_lab').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_permintaan_pemeriksaan_lab").modal('show');
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
        doc.text("Tabel Data Permintaan Pemeriksaan Lab", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_permintaan_pemeriksaan_lab',
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
        // doc.save('table_data_permintaan_pemeriksaan_lab.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_permintaan_pemeriksaan_lab");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data permintaan_pemeriksaan_lab");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/permintaan_pemeriksaan_lab/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});