jQuery().ready(function () {
    var var_tbl_resep_dokter_racikan_detail = $('#tbl_resep_dokter_racikan_detail').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['resep_dokter_racikan_detail','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_resep_dokter_racikan_detail = $('#search_field_resep_dokter_racikan_detail').val();
                var search_text_resep_dokter_racikan_detail = $('#search_text_resep_dokter_racikan_detail').val();
                
                data.search_field_resep_dokter_racikan_detail = search_field_resep_dokter_racikan_detail;
                data.search_text_resep_dokter_racikan_detail = search_text_resep_dokter_racikan_detail;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_resep_dokter_racikan_detail').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_resep_dokter_racikan_detail tr').contextMenu({x: clientX, y: clientY});
            });          
        },        
        "columns": [
{ 'data': 'no_resep' },
{ 'data': 'no_racik' },
{ 'data': 'kode_brng' },
{ 'data': 'p1' },
{ 'data': 'p2' },
{ 'data': 'kandungan' },
{ 'data': 'jml' }

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
        selector: '#tbl_resep_dokter_racikan_detail tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_resep_dokter_racikan_detail.rows({ selected: true }).data()[0];
          if (rowData != null) {
var no_resep = rowData['no_resep'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/resep_dokter_racikan_detail/detail/' + no_resep + '?t=' + mlite.token);
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

    $("form[name='form_resep_dokter_racikan_detail']").validate({
        rules: {
no_resep: 'required',
no_racik: 'required',
kode_brng: 'required',
p1: 'required',
p2: 'required',
kandungan: 'required',
jml: 'required'

        },
        messages: {
no_resep:'No Resep tidak boleh kosong!',
no_racik:'No Racik tidak boleh kosong!',
kode_brng:'Kode Brng tidak boleh kosong!',
p1:'P1 tidak boleh kosong!',
p2:'P2 tidak boleh kosong!',
kandungan:'Kandungan tidak boleh kosong!',
jml:'Jml tidak boleh kosong!'

        },
        submitHandler: function (form) {
var no_resep= $('#no_resep').val();
var no_racik= $('#no_racik').val();
var kode_brng= $('#kode_brng').val();
var p1= $('#p1').val();
var p2= $('#p2').val();
var kandungan= $('#kandungan').val();
var jml= $('#jml').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['resep_dokter_racikan_detail','aksi'])?}",
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
                            $("#modal_resep_dokter_racikan_detail").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_resep_dokter_racikan_detail").modal('hide');
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
                    var_tbl_resep_dokter_racikan_detail.draw();
                }
            })
        }
    });


    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_resep_dokter_racikan_detail.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_resep_dokter_racikan_detail.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_resep_dokter_racikan_detail.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_resep_dokter_racikan_detail').click(function () {
        var_tbl_resep_dokter_racikan_detail.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_resep_dokter_racikan_detail").click(function () {
        var rowData = var_tbl_resep_dokter_racikan_detail.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var no_resep = rowData['no_resep'];
var no_racik = rowData['no_racik'];
var kode_brng = rowData['kode_brng'];
var p1 = rowData['p1'];
var p2 = rowData['p2'];
var kandungan = rowData['kandungan'];
var jml = rowData['jml'];

            $("#typeact").val("edit");
  
            $('#no_resep').val(no_resep);
$('#no_racik').val(no_racik);
$('#kode_brng').val(kode_brng);
$('#p1').val(p1);
$('#p2').val(p2);
$('#kandungan').val(kandungan);
$('#jml').val(jml);

            $("#no_resep").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Resep Dokter Racikan Detail");
            $("#modal_resep_dokter_racikan_detail").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_resep_dokter_racikan_detail").click(function () {
        var rowData = var_tbl_resep_dokter_racikan_detail.rows({ selected: true }).data()[0];


        if (rowData) {
var no_resep = rowData['no_resep'];
            bootbox.confirm('Anda yakin akan menghapus data dengan no_resep="' + no_resep, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['resep_dokter_racikan_detail','aksi'])?}",
                        method: "POST",
                        data: {
                            no_resep: no_resep,
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
                            var_tbl_resep_dokter_racikan_detail.draw();
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
    jQuery("#tambah_data_resep_dokter_racikan_detail").click(function () {

        $('#no_resep').val('');
$('#no_racik').val('');
$('#kode_brng').val('');
$('#p1').val('');
$('#p2').val('');
$('#kandungan').val('');
$('#jml').val('');

        $("#typeact").val("add");
        $("#no_resep").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Resep Dokter Racikan Detail");
        $("#modal_resep_dokter_racikan_detail").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_resep_dokter_racikan_detail").click(function () {

        var search_field_resep_dokter_racikan_detail = $('#search_field_resep_dokter_racikan_detail').val();
        var search_text_resep_dokter_racikan_detail = $('#search_text_resep_dokter_racikan_detail').val();

        $.ajax({
            url: "{?=url(['resep_dokter_racikan_detail','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_resep_dokter_racikan_detail: search_field_resep_dokter_racikan_detail, 
                search_text_resep_dokter_racikan_detail: search_text_resep_dokter_racikan_detail
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_resep_dokter_racikan_detail' class='table display dataTable' style='width:100%'><thead><th>No Resep</th><th>No Racik</th><th>Kode Brng</th><th>P1</th><th>P2</th><th>Kandungan</th><th>Jml</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['no_resep'] + '</td>';
eTable += '<td>' + res[i]['no_racik'] + '</td>';
eTable += '<td>' + res[i]['kode_brng'] + '</td>';
eTable += '<td>' + res[i]['p1'] + '</td>';
eTable += '<td>' + res[i]['p2'] + '</td>';
eTable += '<td>' + res[i]['kandungan'] + '</td>';
eTable += '<td>' + res[i]['jml'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_resep_dokter_racikan_detail').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_resep_dokter_racikan_detail").modal('show');
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
        doc.text("Tabel Data Resep Dokter Racikan Detail", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_resep_dokter_racikan_detail',
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
        // doc.save('table_data_resep_dokter_racikan_detail.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_resep_dokter_racikan_detail");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data resep_dokter_racikan_detail");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/resep_dokter_racikan_detail/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});