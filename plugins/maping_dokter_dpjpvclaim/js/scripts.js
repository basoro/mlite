jQuery().ready(function () {
    var var_tbl_maping_dokter_dpjpvclaim = $('#tbl_maping_dokter_dpjpvclaim').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['maping_dokter_dpjpvclaim','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_maping_dokter_dpjpvclaim = $('#search_field_maping_dokter_dpjpvclaim').val();
                var search_text_maping_dokter_dpjpvclaim = $('#search_text_maping_dokter_dpjpvclaim').val();
                
                data.search_field_maping_dokter_dpjpvclaim = search_field_maping_dokter_dpjpvclaim;
                data.search_text_maping_dokter_dpjpvclaim = search_text_maping_dokter_dpjpvclaim;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_maping_dokter_dpjpvclaim').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_maping_dokter_dpjpvclaim tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'kd_dokter' },
{ 'data': 'kd_dokter_bpjs' },
{ 'data': 'nm_dokter_bpjs' }

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
        selector: '#tbl_maping_dokter_dpjpvclaim tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_maping_dokter_dpjpvclaim.rows({ selected: true }).data()[0];
          if (rowData != null) {
var kd_dokter = rowData['kd_dokter'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/maping_dokter_dpjpvclaim/detail/' + kd_dokter + '?t=' + mlite.token);
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

    $("form[name='form_maping_dokter_dpjpvclaim']").validate({
        rules: {
kd_dokter: 'required',
kd_dokter_bpjs: 'required',
nm_dokter_bpjs: 'required'

        },
        messages: {
kd_dokter:'Kd Dokter tidak boleh kosong!',
kd_dokter_bpjs:'Kd Dokter Bpjs tidak boleh kosong!',
nm_dokter_bpjs:'Nm Dokter Bpjs tidak boleh kosong!'

        },
        submitHandler: function (form) {
var kd_dokter= $('#kd_dokter').val();
var kd_dokter_bpjs= $('#kd_dokter_bpjs').val();
var nm_dokter_bpjs= $('#nm_dokter_bpjs').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['maping_dokter_dpjpvclaim','aksi'])?}",
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
                            $("#modal_maping_dokter_dpjpvclaim").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_maping_dokter_dpjpvclaim").modal('hide');
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
                    var_tbl_maping_dokter_dpjpvclaim.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_maping_dokter_dpjpvclaim.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_maping_dokter_dpjpvclaim.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_maping_dokter_dpjpvclaim.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_maping_dokter_dpjpvclaim').click(function () {
        var_tbl_maping_dokter_dpjpvclaim.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_maping_dokter_dpjpvclaim").click(function () {
        var rowData = var_tbl_maping_dokter_dpjpvclaim.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var kd_dokter = rowData['kd_dokter'];
var kd_dokter_bpjs = rowData['kd_dokter_bpjs'];
var nm_dokter_bpjs = rowData['nm_dokter_bpjs'];

            $("#typeact").val("edit");
  
            $('#kd_dokter').val(kd_dokter);
$('#kd_dokter_bpjs').val(kd_dokter_bpjs);
$('#nm_dokter_bpjs').val(nm_dokter_bpjs);

            $("#kd_dokter").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Maping Dokter Dpjpvclaim");
            $("#modal_maping_dokter_dpjpvclaim").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_maping_dokter_dpjpvclaim").click(function () {
        var rowData = var_tbl_maping_dokter_dpjpvclaim.rows({ selected: true }).data()[0];


        if (rowData) {
var kd_dokter = rowData['kd_dokter'];
            bootbox.confirm('Anda yakin akan menghapus data dengan kd_dokter="' + kd_dokter, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['maping_dokter_dpjpvclaim','aksi'])?}",
                        method: "POST",
                        data: {
                            kd_dokter: kd_dokter,
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
                            var_tbl_maping_dokter_dpjpvclaim.draw();
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
    jQuery("#tambah_data_maping_dokter_dpjpvclaim").click(function () {

        $('#kd_dokter').val('');
$('#kd_dokter_bpjs').val('');
$('#nm_dokter_bpjs').val('');

        $("#typeact").val("add");
        $("#kd_dokter").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Maping Dokter Dpjpvclaim");
        $("#modal_maping_dokter_dpjpvclaim").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_maping_dokter_dpjpvclaim").click(function () {

        var search_field_maping_dokter_dpjpvclaim = $('#search_field_maping_dokter_dpjpvclaim').val();
        var search_text_maping_dokter_dpjpvclaim = $('#search_text_maping_dokter_dpjpvclaim').val();

        $.ajax({
            url: "{?=url(['maping_dokter_dpjpvclaim','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_maping_dokter_dpjpvclaim: search_field_maping_dokter_dpjpvclaim, 
                search_text_maping_dokter_dpjpvclaim: search_text_maping_dokter_dpjpvclaim
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_maping_dokter_dpjpvclaim' class='table display dataTable' style='width:100%'><thead><th>Kd Dokter</th><th>Kd Dokter Bpjs</th><th>Nm Dokter Bpjs</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['kd_dokter'] + '</td>';
eTable += '<td>' + res[i]['kd_dokter_bpjs'] + '</td>';
eTable += '<td>' + res[i]['nm_dokter_bpjs'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_maping_dokter_dpjpvclaim').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_maping_dokter_dpjpvclaim").modal('show');
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
        doc.text("Tabel Data Maping Dokter Dpjpvclaim", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_maping_dokter_dpjpvclaim',
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
        // doc.save('table_data_maping_dokter_dpjpvclaim.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_maping_dokter_dpjpvclaim");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data maping_dokter_dpjpvclaim");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/maping_dokter_dpjpvclaim/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

    $('#cari_referensi_dpjp').click(function() {
        
        var poli_bpjs = $('#poli_bpjs').find(':selected').val();

        $.ajax({
            url: "{?=url(['bridging_sep','referensidpjp'])?}",
            method: "POST",
            data: {
                poli_bpjs: poli_bpjs
            },
            success: function (data) {
                var data = JSON.parse(data);
                console.log(data);
                if(data.metaData.code == '200') {
                    var data = data.response.list;                    
                } else {
                    var data = [];
                }
                let table = '<table id="tbl_cari_referensi_dpjp" class="table table-stripped" width="100%"><thead>';
                    table += '<tr>';
                    table += '<th>Kode Dokter BPJS</th>';
                    table += '<th>Nama Dokter BPJS</th>';
                    table += '</tr>';
                    table += '</thead><tbody>';
                data.forEach(function(d){
                    table += '<tr>';
                    table += '<td>'+d.kode+'</td>';
                    table += '<td>'+d.nama+'</td>';

                    table += '</tr>';
                })
                table += '</tbody></table>';
                $('#forTable_referensi_dpjp').empty().html(table);

                var var_tbl_cari_referensi_dpjp = $('#tbl_cari_referensi_dpjp').DataTable({
                    'select': true
                });
                $('#tbl_cari_referensi_dpjp').on('select.dt', function ( e, dt, type, indexes ) {
                    var rowData = var_tbl_cari_referensi_dpjp.rows({ selected: true }).data()[0];
                    console.log(rowData);
                    $('#kd_dokter_bpjs').val(rowData['0']);
                    $('#nm_dokter_bpjs').val(rowData['1']);
                    $("#modal_cari_referensi_dpjp").modal('hide');
                });
        
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_cari_referensi_dpjp").modal('show');        
    })

});