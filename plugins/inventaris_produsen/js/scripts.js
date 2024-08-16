jQuery().ready(function () {
    var var_tbl_inventaris_produsen = $('#tbl_inventaris_produsen').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['inventaris_produsen','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_inventaris_produsen = $('#search_field_inventaris_produsen').val();
                var search_text_inventaris_produsen = $('#search_text_inventaris_produsen').val();
                
                data.search_field_inventaris_produsen = search_field_inventaris_produsen;
                data.search_text_inventaris_produsen = search_text_inventaris_produsen;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_inventaris_produsen').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_inventaris_produsen tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'kode_produsen' },
{ 'data': 'nama_produsen' },
{ 'data': 'alamat_produsen' },
{ 'data': 'no_telp' },
{ 'data': 'email' },
{ 'data': 'website_produsen' }

        ],
        "columnDefs": [
{ 'targets': 0},
{ 'targets': 1},
{ 'targets': 2},
{ 'targets': 3},
{ 'targets': 4},
{ 'targets': 5}

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
        selector: '#tbl_inventaris_produsen tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_inventaris_produsen.rows({ selected: true }).data()[0];
          if (rowData != null) {
var kode_produsen = rowData['kode_produsen'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/inventaris_produsen/detail/' + kode_produsen + '?t=' + mlite.token);
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

    $("form[name='form_inventaris_produsen']").validate({
        rules: {
kode_produsen: 'required',
nama_produsen: 'required',
alamat_produsen: 'required',
no_telp: 'required',
email: 'required',
website_produsen: 'required'

        },
        messages: {
kode_produsen:'Kode Produsen tidak boleh kosong!',
nama_produsen:'Nama Produsen tidak boleh kosong!',
alamat_produsen:'Alamat Produsen tidak boleh kosong!',
no_telp:'No Telp tidak boleh kosong!',
email:'Email tidak boleh kosong!',
website_produsen:'Website Produsen tidak boleh kosong!'

        },
        submitHandler: function (form) {
var kode_produsen= $('#kode_produsen').val();
var nama_produsen= $('#nama_produsen').val();
var alamat_produsen= $('#alamat_produsen').val();
var no_telp= $('#no_telp').val();
var email= $('#email').val();
var website_produsen= $('#website_produsen').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['inventaris_produsen','aksi'])?}",
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
                            $("#modal_inventaris_produsen").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_inventaris_produsen").modal('hide');
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
                    var_tbl_inventaris_produsen.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_inventaris_produsen.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_inventaris_produsen.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_inventaris_produsen.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_inventaris_produsen').click(function () {
        var_tbl_inventaris_produsen.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_inventaris_produsen").click(function () {
        var rowData = var_tbl_inventaris_produsen.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var kode_produsen = rowData['kode_produsen'];
var nama_produsen = rowData['nama_produsen'];
var alamat_produsen = rowData['alamat_produsen'];
var no_telp = rowData['no_telp'];
var email = rowData['email'];
var website_produsen = rowData['website_produsen'];

            $("#typeact").val("edit");
  
            $('#kode_produsen').val(kode_produsen);
$('#nama_produsen').val(nama_produsen);
$('#alamat_produsen').val(alamat_produsen);
$('#no_telp').val(no_telp);
$('#email').val(email);
$('#website_produsen').val(website_produsen);

            $("#kode_produsen").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Inventaris Produsen");
            $("#modal_inventaris_produsen").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_inventaris_produsen").click(function () {
        var rowData = var_tbl_inventaris_produsen.rows({ selected: true }).data()[0];


        if (rowData) {
var kode_produsen = rowData['kode_produsen'];
            bootbox.confirm('Anda yakin akan menghapus data dengan kode_produsen="' + kode_produsen, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['inventaris_produsen','aksi'])?}",
                        method: "POST",
                        data: {
                            kode_produsen: kode_produsen,
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
                            var_tbl_inventaris_produsen.draw();
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
    jQuery("#tambah_data_inventaris_produsen").click(function () {

        $('#kode_produsen').val('');
$('#nama_produsen').val('');
$('#alamat_produsen').val('');
$('#no_telp').val('');
$('#email').val('');
$('#website_produsen').val('');

        $("#typeact").val("add");
        $("#kode_produsen").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Inventaris Produsen");
        $("#modal_inventaris_produsen").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_inventaris_produsen").click(function () {

        var search_field_inventaris_produsen = $('#search_field_inventaris_produsen').val();
        var search_text_inventaris_produsen = $('#search_text_inventaris_produsen').val();

        $.ajax({
            url: "{?=url(['inventaris_produsen','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_inventaris_produsen: search_field_inventaris_produsen, 
                search_text_inventaris_produsen: search_text_inventaris_produsen
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_inventaris_produsen' class='table display dataTable' style='width:100%'><thead><th>Kode Produsen</th><th>Nama Produsen</th><th>Alamat Produsen</th><th>No Telp</th><th>Email</th><th>Website Produsen</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['kode_produsen'] + '</td>';
eTable += '<td>' + res[i]['nama_produsen'] + '</td>';
eTable += '<td>' + res[i]['alamat_produsen'] + '</td>';
eTable += '<td>' + res[i]['no_telp'] + '</td>';
eTable += '<td>' + res[i]['email'] + '</td>';
eTable += '<td>' + res[i]['website_produsen'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_inventaris_produsen').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_inventaris_produsen").modal('show');
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
        doc.text("Tabel Data Inventaris Produsen", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_inventaris_produsen',
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
        // doc.save('table_data_inventaris_produsen.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_inventaris_produsen");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data inventaris_produsen");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/inventaris_produsen/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});