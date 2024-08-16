jQuery().ready(function () {
    var var_tbl_inventaris_peminjaman = $('#tbl_inventaris_peminjaman').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['inventaris_peminjaman','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_inventaris_peminjaman = $('#search_field_inventaris_peminjaman').val();
                var search_text_inventaris_peminjaman = $('#search_text_inventaris_peminjaman').val();
                
                data.search_field_inventaris_peminjaman = search_field_inventaris_peminjaman;
                data.search_text_inventaris_peminjaman = search_text_inventaris_peminjaman;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_inventaris_peminjaman').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_inventaris_peminjaman tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'peminjam' },
{ 'data': 'tlp' },
{ 'data': 'no_inventaris' },
{ 'data': 'tgl_pinjam' },
{ 'data': 'tgl_kembali' },
{ 'data': 'nip' },
{ 'data': 'status_pinjam' }

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
        selector: '#tbl_inventaris_peminjaman tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_inventaris_peminjaman.rows({ selected: true }).data()[0];
          if (rowData != null) {
var peminjam = rowData['peminjam'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/inventaris_peminjaman/detail/' + peminjam + '?t=' + mlite.token);
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

    $("form[name='form_inventaris_peminjaman']").validate({
        rules: {
peminjam: 'required',
tlp: 'required',
no_inventaris: 'required',
tgl_pinjam: 'required',
tgl_kembali: 'required',
nip: 'required',
status_pinjam: 'required'

        },
        messages: {
peminjam:'Peminjam tidak boleh kosong!',
tlp:'Tlp tidak boleh kosong!',
no_inventaris:'No Inventaris tidak boleh kosong!',
tgl_pinjam:'Tgl Pinjam tidak boleh kosong!',
tgl_kembali:'Tgl Kembali tidak boleh kosong!',
nip:'Nip tidak boleh kosong!',
status_pinjam:'Status Pinjam tidak boleh kosong!'

        },
        submitHandler: function (form) {
var peminjam= $('#peminjam').val();
var tlp= $('#tlp').val();
var no_inventaris= $('#no_inventaris').val();
var tgl_pinjam= $('#tgl_pinjam').val();
var tgl_kembali= $('#tgl_kembali').val();
var nip= $('#nip').val();
var status_pinjam= $('#status_pinjam').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['inventaris_peminjaman','aksi'])?}",
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
                            $("#modal_inventaris_peminjaman").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_inventaris_peminjaman").modal('hide');
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
                    var_tbl_inventaris_peminjaman.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_inventaris_peminjaman.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_inventaris_peminjaman.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_inventaris_peminjaman.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_inventaris_peminjaman').click(function () {
        var_tbl_inventaris_peminjaman.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_inventaris_peminjaman").click(function () {
        var rowData = var_tbl_inventaris_peminjaman.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var peminjam = rowData['peminjam'];
var tlp = rowData['tlp'];
var no_inventaris = rowData['no_inventaris'];
var tgl_pinjam = rowData['tgl_pinjam'];
var tgl_kembali = rowData['tgl_kembali'];
var nip = rowData['nip'];
var status_pinjam = rowData['status_pinjam'];

            $("#typeact").val("edit");
  
            $('#peminjam').val(peminjam);
$('#tlp').val(tlp);
$('#no_inventaris').val(no_inventaris);
$('#tgl_pinjam').val(tgl_pinjam);
$('#tgl_kembali').val(tgl_kembali);
$('#nip').val(nip);
$('#status_pinjam').val(status_pinjam);

            $("#peminjam").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Inventaris Peminjaman");
            $("#modal_inventaris_peminjaman").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_inventaris_peminjaman").click(function () {
        var rowData = var_tbl_inventaris_peminjaman.rows({ selected: true }).data()[0];


        if (rowData) {
var peminjam = rowData['peminjam'];
            bootbox.confirm('Anda yakin akan menghapus data dengan peminjam="' + peminjam, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['inventaris_peminjaman','aksi'])?}",
                        method: "POST",
                        data: {
                            peminjam: peminjam,
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
                            var_tbl_inventaris_peminjaman.draw();
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
    jQuery("#tambah_data_inventaris_peminjaman").click(function () {

        $('#peminjam').val('');
$('#tlp').val('');
$('#no_inventaris').val('');
$('#tgl_pinjam').val('');
$('#tgl_kembali').val('');
$('#nip').val('');
$('#status_pinjam').val('');

        $("#typeact").val("add");
        $("#peminjam").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Inventaris Peminjaman");
        $("#modal_inventaris_peminjaman").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_inventaris_peminjaman").click(function () {

        var search_field_inventaris_peminjaman = $('#search_field_inventaris_peminjaman').val();
        var search_text_inventaris_peminjaman = $('#search_text_inventaris_peminjaman').val();

        $.ajax({
            url: "{?=url(['inventaris_peminjaman','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_inventaris_peminjaman: search_field_inventaris_peminjaman, 
                search_text_inventaris_peminjaman: search_text_inventaris_peminjaman
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_inventaris_peminjaman' class='table display dataTable' style='width:100%'><thead><th>Peminjam</th><th>Tlp</th><th>No Inventaris</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Nip</th><th>Status Pinjam</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['peminjam'] + '</td>';
eTable += '<td>' + res[i]['tlp'] + '</td>';
eTable += '<td>' + res[i]['no_inventaris'] + '</td>';
eTable += '<td>' + res[i]['tgl_pinjam'] + '</td>';
eTable += '<td>' + res[i]['tgl_kembali'] + '</td>';
eTable += '<td>' + res[i]['nip'] + '</td>';
eTable += '<td>' + res[i]['status_pinjam'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_inventaris_peminjaman').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_inventaris_peminjaman").modal('show');
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
        doc.text("Tabel Data Inventaris Peminjaman", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_inventaris_peminjaman',
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
        // doc.save('table_data_inventaris_peminjaman.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_inventaris_peminjaman");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data inventaris_peminjaman");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/inventaris_peminjaman/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});