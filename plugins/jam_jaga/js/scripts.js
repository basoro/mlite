jQuery().ready(function () {
    var var_tbl_jam_jaga = $('#tbl_jam_jaga').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['jam_jaga','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_jam_jaga = $('#search_field_jam_jaga').val();
                var search_text_jam_jaga = $('#search_text_jam_jaga').val();
                
                data.search_field_jam_jaga = search_field_jam_jaga;
                data.search_text_jam_jaga = search_text_jam_jaga;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_jam_jaga').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_jam_jaga tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'no_id' },
{ 'data': 'dep_id' },
{ 'data': 'shift' },
{ 'data': 'jam_masuk' },
{ 'data': 'jam_pulang' }

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
        selector: '#tbl_jam_jaga tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_jam_jaga.rows({ selected: true }).data()[0];
          if (rowData != null) {
var no_id = rowData['no_id'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/jam_jaga/detail/' + no_id + '?t=' + mlite.token);
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

    $("form[name='form_jam_jaga']").validate({
        rules: {
no_id: 'required',
dep_id: 'required',
shift: 'required',
jam_masuk: 'required',
jam_pulang: 'required'

        },
        messages: {
no_id:'No Id tidak boleh kosong!',
dep_id:'Dep Id tidak boleh kosong!',
shift:'Shift tidak boleh kosong!',
jam_masuk:'Jam Masuk tidak boleh kosong!',
jam_pulang:'Jam Pulang tidak boleh kosong!'

        },
        submitHandler: function (form) {
var no_id= $('#no_id').val();
var dep_id= $('#dep_id').val();
var shift= $('#shift').val();
var jam_masuk= $('#jam_masuk').val();
var jam_pulang= $('#jam_pulang').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['jam_jaga','aksi'])?}",
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
                            $("#modal_jam_jaga").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_jam_jaga").modal('hide');
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
                    var_tbl_jam_jaga.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_jam_jaga.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_jam_jaga.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_jam_jaga.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_jam_jaga').click(function () {
        var_tbl_jam_jaga.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_jam_jaga").click(function () {
        var rowData = var_tbl_jam_jaga.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var no_id = rowData['no_id'];
var dep_id = rowData['dep_id'];
var shift = rowData['shift'];
var jam_masuk = rowData['jam_masuk'];
var jam_pulang = rowData['jam_pulang'];

            $("#typeact").val("edit");
  
            $('#no_id').val(no_id);
$('#dep_id').val(dep_id);
$('#shift').val(shift);
$('#jam_masuk').val(jam_masuk);
$('#jam_pulang').val(jam_pulang);

            $("#no_id").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Jam Jaga");
            $("#modal_jam_jaga").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_jam_jaga").click(function () {
        var rowData = var_tbl_jam_jaga.rows({ selected: true }).data()[0];


        if (rowData) {
var no_id = rowData['no_id'];
            bootbox.confirm('Anda yakin akan menghapus data dengan no_id="' + no_id, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['jam_jaga','aksi'])?}",
                        method: "POST",
                        data: {
                            no_id: no_id,
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
                            var_tbl_jam_jaga.draw();
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
    jQuery("#tambah_data_jam_jaga").click(function () {

        $('#no_id').val('');
$('#dep_id').val('');
$('#shift').val('');
$('#jam_masuk').val('');
$('#jam_pulang').val('');

        $("#typeact").val("add");
        $("#no_id").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Jam Jaga");
        $("#modal_jam_jaga").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_jam_jaga").click(function () {

        var search_field_jam_jaga = $('#search_field_jam_jaga').val();
        var search_text_jam_jaga = $('#search_text_jam_jaga').val();

        $.ajax({
            url: "{?=url(['jam_jaga','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_jam_jaga: search_field_jam_jaga, 
                search_text_jam_jaga: search_text_jam_jaga
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_jam_jaga' class='table display dataTable' style='width:100%'><thead><th>No Id</th><th>Dep Id</th><th>Shift</th><th>Jam Masuk</th><th>Jam Pulang</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['no_id'] + '</td>';
eTable += '<td>' + res[i]['dep_id'] + '</td>';
eTable += '<td>' + res[i]['shift'] + '</td>';
eTable += '<td>' + res[i]['jam_masuk'] + '</td>';
eTable += '<td>' + res[i]['jam_pulang'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_jam_jaga').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_jam_jaga").modal('show');
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
        doc.text("Tabel Data Jam Jaga", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_jam_jaga',
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
        // doc.save('table_data_jam_jaga.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_jam_jaga");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data jam_jaga");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/jam_jaga/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});