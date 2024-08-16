jQuery().ready(function () {
    var var_tbl_catatan_perawatan = $('#tbl_catatan_perawatan').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['catatan_perawatan','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_catatan_perawatan = $('#search_field_catatan_perawatan').val();
                var search_text_catatan_perawatan = $('#search_text_catatan_perawatan').val();
                
                data.search_field_catatan_perawatan = search_field_catatan_perawatan;
                data.search_text_catatan_perawatan = search_text_catatan_perawatan;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_catatan_perawatan').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_catatan_perawatan tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'tanggal' },
{ 'data': 'jam' },
{ 'data': 'no_rawat' },
{ 'data': 'kd_dokter' },
{ 'data': 'catatan' }

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
        selector: '#tbl_catatan_perawatan tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_catatan_perawatan.rows({ selected: true }).data()[0];
          if (rowData != null) {
var tanggal = rowData['tanggal'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/catatan_perawatan/detail/' + tanggal + '?t=' + mlite.token);
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

    $("form[name='form_catatan_perawatan']").validate({
        rules: {
tanggal: 'required',
jam: 'required',
no_rawat: 'required',
kd_dokter: 'required',
catatan: 'required'

        },
        messages: {
tanggal:'Tanggal tidak boleh kosong!',
jam:'Jam tidak boleh kosong!',
no_rawat:'No Rawat tidak boleh kosong!',
kd_dokter:'Kd Dokter tidak boleh kosong!',
catatan:'Catatan tidak boleh kosong!'

        },
        submitHandler: function (form) {
var tanggal= $('#tanggal').val();
var jam= $('#jam').val();
var no_rawat= $('#no_rawat').val();
var kd_dokter= $('#kd_dokter').val();
var catatan= $('#catatan').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['catatan_perawatan','aksi'])?}",
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
                            $("#modal_catatan_perawatan").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_catatan_perawatan").modal('hide');
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
                    var_tbl_catatan_perawatan.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_catatan_perawatan.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_catatan_perawatan.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_catatan_perawatan.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_catatan_perawatan').click(function () {
        var_tbl_catatan_perawatan.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_catatan_perawatan").click(function () {
        var rowData = var_tbl_catatan_perawatan.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var tanggal = rowData['tanggal'];
var jam = rowData['jam'];
var no_rawat = rowData['no_rawat'];
var kd_dokter = rowData['kd_dokter'];
var catatan = rowData['catatan'];

            $("#typeact").val("edit");
  
            $('#tanggal').val(tanggal);
$('#jam').val(jam);
$('#no_rawat').val(no_rawat);
$('#kd_dokter').val(kd_dokter);
$('#catatan').val(catatan);

            $("#tanggal").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Catatan Perawatan");
            $("#modal_catatan_perawatan").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_catatan_perawatan").click(function () {
        var rowData = var_tbl_catatan_perawatan.rows({ selected: true }).data()[0];


        if (rowData) {
var tanggal = rowData['tanggal'];
            bootbox.confirm('Anda yakin akan menghapus data dengan tanggal="' + tanggal, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['catatan_perawatan','aksi'])?}",
                        method: "POST",
                        data: {
                            tanggal: tanggal,
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
                            var_tbl_catatan_perawatan.draw();
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
    jQuery("#tambah_data_catatan_perawatan").click(function () {

        $('#tanggal').val('');
$('#jam').val('');
$('#no_rawat').val('');
$('#kd_dokter').val('');
$('#catatan').val('');

        $("#typeact").val("add");
        $("#tanggal").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Catatan Perawatan");
        $("#modal_catatan_perawatan").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_catatan_perawatan").click(function () {

        var search_field_catatan_perawatan = $('#search_field_catatan_perawatan').val();
        var search_text_catatan_perawatan = $('#search_text_catatan_perawatan').val();

        $.ajax({
            url: "{?=url(['catatan_perawatan','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_catatan_perawatan: search_field_catatan_perawatan, 
                search_text_catatan_perawatan: search_text_catatan_perawatan
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_catatan_perawatan' class='table display dataTable' style='width:100%'><thead><th>Tanggal</th><th>Jam</th><th>No Rawat</th><th>Kd Dokter</th><th>Catatan</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['tanggal'] + '</td>';
eTable += '<td>' + res[i]['jam'] + '</td>';
eTable += '<td>' + res[i]['no_rawat'] + '</td>';
eTable += '<td>' + res[i]['kd_dokter'] + '</td>';
eTable += '<td>' + res[i]['catatan'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_catatan_perawatan').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_catatan_perawatan").modal('show');
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
        doc.text("Tabel Data Catatan Perawatan", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_catatan_perawatan',
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
        // doc.save('table_data_catatan_perawatan.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_catatan_perawatan");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data catatan_perawatan");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/catatan_perawatan/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});