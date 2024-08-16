jQuery().ready(function () {
    var var_tbl_paket_operasi = $('#tbl_paket_operasi').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['paket_operasi','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_paket_operasi = $('#search_field_paket_operasi').val();
                var search_text_paket_operasi = $('#search_text_paket_operasi').val();
                
                data.search_field_paket_operasi = search_field_paket_operasi;
                data.search_text_paket_operasi = search_text_paket_operasi;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_paket_operasi').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_paket_operasi tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'kode_paket' },
{ 'data': 'nm_perawatan' },
{ 'data': 'kategori' },
{ 'data': 'operator1' },
{ 'data': 'operator2' },
{ 'data': 'operator3' },
{ 'data': 'asisten_operator1' },
{ 'data': 'asisten_operator2' },
{ 'data': 'asisten_operator3' },
{ 'data': 'instrumen' },
{ 'data': 'dokter_anak' },
{ 'data': 'perawaat_resusitas' },
{ 'data': 'dokter_anestesi' },
{ 'data': 'asisten_anestesi' },
{ 'data': 'asisten_anestesi2' },
{ 'data': 'bidan' },
{ 'data': 'bidan2' },
{ 'data': 'bidan3' },
{ 'data': 'perawat_luar' },
{ 'data': 'sewa_ok' },
{ 'data': 'alat' },
{ 'data': 'akomodasi' },
{ 'data': 'bagian_rs' },
{ 'data': 'omloop' },
{ 'data': 'omloop2' },
{ 'data': 'omloop3' },
{ 'data': 'omloop4' },
{ 'data': 'omloop5' },
{ 'data': 'sarpras' },
{ 'data': 'dokter_pjanak' },
{ 'data': 'dokter_umum' },
{ 'data': 'kd_pj' },
{ 'data': 'status' },
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
{ 'targets': 15},
{ 'targets': 16},
{ 'targets': 17},
{ 'targets': 18},
{ 'targets': 19},
{ 'targets': 20},
{ 'targets': 21},
{ 'targets': 22},
{ 'targets': 23},
{ 'targets': 24},
{ 'targets': 25},
{ 'targets': 26},
{ 'targets': 27},
{ 'targets': 28},
{ 'targets': 29},
{ 'targets': 30},
{ 'targets': 31},
{ 'targets': 32},
{ 'targets': 33}

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
        selector: '#tbl_paket_operasi tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_paket_operasi.rows({ selected: true }).data()[0];
          if (rowData != null) {
var kode_paket = rowData['kode_paket'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/paket_operasi/detail/' + kode_paket + '?t=' + mlite.token);
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

    $("form[name='form_paket_operasi']").validate({
        rules: {
kode_paket: 'required',
nm_perawatan: 'required',
kategori: 'required',
operator1: 'required',
operator2: 'required',
operator3: 'required',
asisten_operator1: 'required',
asisten_operator2: 'required',
asisten_operator3: 'required',
instrumen: 'required',
dokter_anak: 'required',
perawaat_resusitas: 'required',
dokter_anestesi: 'required',
asisten_anestesi: 'required',
asisten_anestesi2: 'required',
bidan: 'required',
bidan2: 'required',
bidan3: 'required',
perawat_luar: 'required',
sewa_ok: 'required',
alat: 'required',
akomodasi: 'required',
bagian_rs: 'required',
omloop: 'required',
omloop2: 'required',
omloop3: 'required',
omloop4: 'required',
omloop5: 'required',
sarpras: 'required',
dokter_pjanak: 'required',
dokter_umum: 'required',
kd_pj: 'required',
status: 'required',
kelas: 'required'

        },
        messages: {
kode_paket:'Kode Paket tidak boleh kosong!',
nm_perawatan:'Nm Perawatan tidak boleh kosong!',
kategori:'Kategori tidak boleh kosong!',
operator1:'Operator1 tidak boleh kosong!',
operator2:'Operator2 tidak boleh kosong!',
operator3:'Operator3 tidak boleh kosong!',
asisten_operator1:'Asisten Operator1 tidak boleh kosong!',
asisten_operator2:'Asisten Operator2 tidak boleh kosong!',
asisten_operator3:'Asisten Operator3 tidak boleh kosong!',
instrumen:'Instrumen tidak boleh kosong!',
dokter_anak:'Dokter Anak tidak boleh kosong!',
perawaat_resusitas:'Perawaat Resusitas tidak boleh kosong!',
dokter_anestesi:'Dokter Anestesi tidak boleh kosong!',
asisten_anestesi:'Asisten Anestesi tidak boleh kosong!',
asisten_anestesi2:'Asisten Anestesi2 tidak boleh kosong!',
bidan:'Bidan tidak boleh kosong!',
bidan2:'Bidan2 tidak boleh kosong!',
bidan3:'Bidan3 tidak boleh kosong!',
perawat_luar:'Perawat Luar tidak boleh kosong!',
sewa_ok:'Sewa Ok tidak boleh kosong!',
alat:'Alat tidak boleh kosong!',
akomodasi:'Akomodasi tidak boleh kosong!',
bagian_rs:'Bagian Rs tidak boleh kosong!',
omloop:'Omloop tidak boleh kosong!',
omloop2:'Omloop2 tidak boleh kosong!',
omloop3:'Omloop3 tidak boleh kosong!',
omloop4:'Omloop4 tidak boleh kosong!',
omloop5:'Omloop5 tidak boleh kosong!',
sarpras:'Sarpras tidak boleh kosong!',
dokter_pjanak:'Dokter Pjanak tidak boleh kosong!',
dokter_umum:'Dokter Umum tidak boleh kosong!',
kd_pj:'Kd Pj tidak boleh kosong!',
status:'Status tidak boleh kosong!',
kelas:'Kelas tidak boleh kosong!'

        },
        submitHandler: function (form) {
var kode_paket= $('#kode_paket').val();
var nm_perawatan= $('#nm_perawatan').val();
var kategori= $('#kategori').val();
var operator1= $('#operator1').val();
var operator2= $('#operator2').val();
var operator3= $('#operator3').val();
var asisten_operator1= $('#asisten_operator1').val();
var asisten_operator2= $('#asisten_operator2').val();
var asisten_operator3= $('#asisten_operator3').val();
var instrumen= $('#instrumen').val();
var dokter_anak= $('#dokter_anak').val();
var perawaat_resusitas= $('#perawaat_resusitas').val();
var dokter_anestesi= $('#dokter_anestesi').val();
var asisten_anestesi= $('#asisten_anestesi').val();
var asisten_anestesi2= $('#asisten_anestesi2').val();
var bidan= $('#bidan').val();
var bidan2= $('#bidan2').val();
var bidan3= $('#bidan3').val();
var perawat_luar= $('#perawat_luar').val();
var sewa_ok= $('#sewa_ok').val();
var alat= $('#alat').val();
var akomodasi= $('#akomodasi').val();
var bagian_rs= $('#bagian_rs').val();
var omloop= $('#omloop').val();
var omloop2= $('#omloop2').val();
var omloop3= $('#omloop3').val();
var omloop4= $('#omloop4').val();
var omloop5= $('#omloop5').val();
var sarpras= $('#sarpras').val();
var dokter_pjanak= $('#dokter_pjanak').val();
var dokter_umum= $('#dokter_umum').val();
var kd_pj= $('#kd_pj').val();
var status= $('#status').val();
var kelas= $('#kelas').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['paket_operasi','aksi'])?}",
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
                            $("#modal_paket_operasi").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_paket_operasi").modal('hide');
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
                    var_tbl_paket_operasi.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_paket_operasi.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_paket_operasi.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_paket_operasi.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_paket_operasi').click(function () {
        var_tbl_paket_operasi.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_paket_operasi").click(function () {
        var rowData = var_tbl_paket_operasi.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var kode_paket = rowData['kode_paket'];
var nm_perawatan = rowData['nm_perawatan'];
var kategori = rowData['kategori'];
var operator1 = rowData['operator1'];
var operator2 = rowData['operator2'];
var operator3 = rowData['operator3'];
var asisten_operator1 = rowData['asisten_operator1'];
var asisten_operator2 = rowData['asisten_operator2'];
var asisten_operator3 = rowData['asisten_operator3'];
var instrumen = rowData['instrumen'];
var dokter_anak = rowData['dokter_anak'];
var perawaat_resusitas = rowData['perawaat_resusitas'];
var dokter_anestesi = rowData['dokter_anestesi'];
var asisten_anestesi = rowData['asisten_anestesi'];
var asisten_anestesi2 = rowData['asisten_anestesi2'];
var bidan = rowData['bidan'];
var bidan2 = rowData['bidan2'];
var bidan3 = rowData['bidan3'];
var perawat_luar = rowData['perawat_luar'];
var sewa_ok = rowData['sewa_ok'];
var alat = rowData['alat'];
var akomodasi = rowData['akomodasi'];
var bagian_rs = rowData['bagian_rs'];
var omloop = rowData['omloop'];
var omloop2 = rowData['omloop2'];
var omloop3 = rowData['omloop3'];
var omloop4 = rowData['omloop4'];
var omloop5 = rowData['omloop5'];
var sarpras = rowData['sarpras'];
var dokter_pjanak = rowData['dokter_pjanak'];
var dokter_umum = rowData['dokter_umum'];
var kd_pj = rowData['kd_pj'];
var status = rowData['status'];
var kelas = rowData['kelas'];

            $("#typeact").val("edit");
  
            $('#kode_paket').val(kode_paket);
$('#nm_perawatan').val(nm_perawatan);
$('#kategori').val(kategori);
$('#operator1').val(operator1);
$('#operator2').val(operator2);
$('#operator3').val(operator3);
$('#asisten_operator1').val(asisten_operator1);
$('#asisten_operator2').val(asisten_operator2);
$('#asisten_operator3').val(asisten_operator3);
$('#instrumen').val(instrumen);
$('#dokter_anak').val(dokter_anak);
$('#perawaat_resusitas').val(perawaat_resusitas);
$('#dokter_anestesi').val(dokter_anestesi);
$('#asisten_anestesi').val(asisten_anestesi);
$('#asisten_anestesi2').val(asisten_anestesi2);
$('#bidan').val(bidan);
$('#bidan2').val(bidan2);
$('#bidan3').val(bidan3);
$('#perawat_luar').val(perawat_luar);
$('#sewa_ok').val(sewa_ok);
$('#alat').val(alat);
$('#akomodasi').val(akomodasi);
$('#bagian_rs').val(bagian_rs);
$('#omloop').val(omloop);
$('#omloop2').val(omloop2);
$('#omloop3').val(omloop3);
$('#omloop4').val(omloop4);
$('#omloop5').val(omloop5);
$('#sarpras').val(sarpras);
$('#dokter_pjanak').val(dokter_pjanak);
$('#dokter_umum').val(dokter_umum);
$('#kd_pj').val(kd_pj);
$('#status').val(status);
$('#kelas').val(kelas);

            $("#kode_paket").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Paket Operasi");
            $("#modal_paket_operasi").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_paket_operasi").click(function () {
        var rowData = var_tbl_paket_operasi.rows({ selected: true }).data()[0];


        if (rowData) {
var kode_paket = rowData['kode_paket'];
            bootbox.confirm('Anda yakin akan menghapus data dengan kode_paket="' + kode_paket, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['paket_operasi','aksi'])?}",
                        method: "POST",
                        data: {
                            kode_paket: kode_paket,
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
                            var_tbl_paket_operasi.draw();
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
    jQuery("#tambah_data_paket_operasi").click(function () {

        $('#kode_paket').val('');
$('#nm_perawatan').val('');
$('#kategori').val('');
$('#operator1').val('');
$('#operator2').val('');
$('#operator3').val('');
$('#asisten_operator1').val('');
$('#asisten_operator2').val('');
$('#asisten_operator3').val('');
$('#instrumen').val('');
$('#dokter_anak').val('');
$('#perawaat_resusitas').val('');
$('#dokter_anestesi').val('');
$('#asisten_anestesi').val('');
$('#asisten_anestesi2').val('');
$('#bidan').val('');
$('#bidan2').val('');
$('#bidan3').val('');
$('#perawat_luar').val('');
$('#sewa_ok').val('');
$('#alat').val('');
$('#akomodasi').val('');
$('#bagian_rs').val('');
$('#omloop').val('');
$('#omloop2').val('');
$('#omloop3').val('');
$('#omloop4').val('');
$('#omloop5').val('');
$('#sarpras').val('');
$('#dokter_pjanak').val('');
$('#dokter_umum').val('');
$('#kd_pj').val('');
$('#status').val('');
$('#kelas').val('');

        $("#typeact").val("add");
        $("#kode_paket").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Paket Operasi");
        $("#modal_paket_operasi").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_paket_operasi").click(function () {

        var search_field_paket_operasi = $('#search_field_paket_operasi').val();
        var search_text_paket_operasi = $('#search_text_paket_operasi').val();

        $.ajax({
            url: "{?=url(['paket_operasi','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_paket_operasi: search_field_paket_operasi, 
                search_text_paket_operasi: search_text_paket_operasi
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_paket_operasi' class='table display dataTable' style='width:100%'><thead><th>Kode Paket</th><th>Nm Perawatan</th><th>Kategori</th><th>Operator1</th><th>Operator2</th><th>Operator3</th><th>Asisten Operator1</th><th>Asisten Operator2</th><th>Asisten Operator3</th><th>Instrumen</th><th>Dokter Anak</th><th>Perawaat Resusitas</th><th>Dokter Anestesi</th><th>Asisten Anestesi</th><th>Asisten Anestesi2</th><th>Bidan</th><th>Bidan2</th><th>Bidan3</th><th>Perawat Luar</th><th>Sewa Ok</th><th>Alat</th><th>Akomodasi</th><th>Bagian Rs</th><th>Omloop</th><th>Omloop2</th><th>Omloop3</th><th>Omloop4</th><th>Omloop5</th><th>Sarpras</th><th>Dokter Pjanak</th><th>Dokter Umum</th><th>Kd Pj</th><th>Status</th><th>Kelas</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['kode_paket'] + '</td>';
eTable += '<td>' + res[i]['nm_perawatan'] + '</td>';
eTable += '<td>' + res[i]['kategori'] + '</td>';
eTable += '<td>' + res[i]['operator1'] + '</td>';
eTable += '<td>' + res[i]['operator2'] + '</td>';
eTable += '<td>' + res[i]['operator3'] + '</td>';
eTable += '<td>' + res[i]['asisten_operator1'] + '</td>';
eTable += '<td>' + res[i]['asisten_operator2'] + '</td>';
eTable += '<td>' + res[i]['asisten_operator3'] + '</td>';
eTable += '<td>' + res[i]['instrumen'] + '</td>';
eTable += '<td>' + res[i]['dokter_anak'] + '</td>';
eTable += '<td>' + res[i]['perawaat_resusitas'] + '</td>';
eTable += '<td>' + res[i]['dokter_anestesi'] + '</td>';
eTable += '<td>' + res[i]['asisten_anestesi'] + '</td>';
eTable += '<td>' + res[i]['asisten_anestesi2'] + '</td>';
eTable += '<td>' + res[i]['bidan'] + '</td>';
eTable += '<td>' + res[i]['bidan2'] + '</td>';
eTable += '<td>' + res[i]['bidan3'] + '</td>';
eTable += '<td>' + res[i]['perawat_luar'] + '</td>';
eTable += '<td>' + res[i]['sewa_ok'] + '</td>';
eTable += '<td>' + res[i]['alat'] + '</td>';
eTable += '<td>' + res[i]['akomodasi'] + '</td>';
eTable += '<td>' + res[i]['bagian_rs'] + '</td>';
eTable += '<td>' + res[i]['omloop'] + '</td>';
eTable += '<td>' + res[i]['omloop2'] + '</td>';
eTable += '<td>' + res[i]['omloop3'] + '</td>';
eTable += '<td>' + res[i]['omloop4'] + '</td>';
eTable += '<td>' + res[i]['omloop5'] + '</td>';
eTable += '<td>' + res[i]['sarpras'] + '</td>';
eTable += '<td>' + res[i]['dokter_pjanak'] + '</td>';
eTable += '<td>' + res[i]['dokter_umum'] + '</td>';
eTable += '<td>' + res[i]['kd_pj'] + '</td>';
eTable += '<td>' + res[i]['status'] + '</td>';
eTable += '<td>' + res[i]['kelas'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_paket_operasi').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_paket_operasi").modal('show');
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
        doc.text("Tabel Data Paket Operasi", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_paket_operasi',
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
        // doc.save('table_data_paket_operasi.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_paket_operasi");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data paket_operasi");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/paket_operasi/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});