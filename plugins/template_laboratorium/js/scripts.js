jQuery().ready(function () {
    var var_tbl_template_laboratorium = $('#tbl_template_laboratorium').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['template_laboratorium','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_template_laboratorium = $('#search_field_template_laboratorium').val();
                var search_text_template_laboratorium = $('#search_text_template_laboratorium').val();
                
                data.search_field_template_laboratorium = search_field_template_laboratorium;
                data.search_text_template_laboratorium = search_text_template_laboratorium;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_template_laboratorium').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_template_laboratorium tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'kd_jenis_prw' },
{ 'data': 'id_template' },
{ 'data': 'Pemeriksaan' },
{ 'data': 'satuan' },
{ 'data': 'nilai_rujukan_ld' },
{ 'data': 'nilai_rujukan_la' },
{ 'data': 'nilai_rujukan_pd' },
{ 'data': 'nilai_rujukan_pa' },
{ 'data': 'bagian_rs' },
{ 'data': 'bhp' },
{ 'data': 'bagian_perujuk' },
{ 'data': 'bagian_dokter' },
{ 'data': 'bagian_laborat' },
{ 'data': 'kso' },
{ 'data': 'menejemen' },
{ 'data': 'biaya_item' },
{ 'data': 'urut' }

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
{ 'targets': 16}

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
        selector: '#tbl_template_laboratorium tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_template_laboratorium.rows({ selected: true }).data()[0];
          if (rowData != null) {
var kd_jenis_prw = rowData['kd_jenis_prw'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/template_laboratorium/detail/' + kd_jenis_prw + '?t=' + mlite.token);
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

    $("form[name='form_template_laboratorium']").validate({
        rules: {
kd_jenis_prw: 'required',
id_template: 'required',
Pemeriksaan: 'required',
satuan: 'required',
nilai_rujukan_ld: 'required',
nilai_rujukan_la: 'required',
nilai_rujukan_pd: 'required',
nilai_rujukan_pa: 'required',
bagian_rs: 'required',
bhp: 'required',
bagian_perujuk: 'required',
bagian_dokter: 'required',
bagian_laborat: 'required',
kso: 'required',
menejemen: 'required',
biaya_item: 'required',
urut: 'required'

        },
        messages: {
kd_jenis_prw:'Kd Jenis Prw tidak boleh kosong!',
id_template:'Id Template tidak boleh kosong!',
Pemeriksaan:'Pemeriksaan tidak boleh kosong!',
satuan:'Satuan tidak boleh kosong!',
nilai_rujukan_ld:'Nilai Rujukan Ld tidak boleh kosong!',
nilai_rujukan_la:'Nilai Rujukan La tidak boleh kosong!',
nilai_rujukan_pd:'Nilai Rujukan Pd tidak boleh kosong!',
nilai_rujukan_pa:'Nilai Rujukan Pa tidak boleh kosong!',
bagian_rs:'Bagian Rs tidak boleh kosong!',
bhp:'Bhp tidak boleh kosong!',
bagian_perujuk:'Bagian Perujuk tidak boleh kosong!',
bagian_dokter:'Bagian Dokter tidak boleh kosong!',
bagian_laborat:'Bagian Laborat tidak boleh kosong!',
kso:'Kso tidak boleh kosong!',
menejemen:'Menejemen tidak boleh kosong!',
biaya_item:'Biaya Item tidak boleh kosong!',
urut:'Urut tidak boleh kosong!'

        },
        submitHandler: function (form) {
var kd_jenis_prw= $('#kd_jenis_prw').val();
var id_template= $('#id_template').val();
var Pemeriksaan= $('#Pemeriksaan').val();
var satuan= $('#satuan').val();
var nilai_rujukan_ld= $('#nilai_rujukan_ld').val();
var nilai_rujukan_la= $('#nilai_rujukan_la').val();
var nilai_rujukan_pd= $('#nilai_rujukan_pd').val();
var nilai_rujukan_pa= $('#nilai_rujukan_pa').val();
var bagian_rs= $('#bagian_rs').val();
var bhp= $('#bhp').val();
var bagian_perujuk= $('#bagian_perujuk').val();
var bagian_dokter= $('#bagian_dokter').val();
var bagian_laborat= $('#bagian_laborat').val();
var kso= $('#kso').val();
var menejemen= $('#menejemen').val();
var biaya_item= $('#biaya_item').val();
var urut= $('#urut').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['template_laboratorium','aksi'])?}",
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
                            $("#modal_template_laboratorium").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_template_laboratorium").modal('hide');
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
                    var_tbl_template_laboratorium.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_template_laboratorium.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_template_laboratorium.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_template_laboratorium.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_template_laboratorium').click(function () {
        var_tbl_template_laboratorium.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_template_laboratorium").click(function () {
        var rowData = var_tbl_template_laboratorium.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var kd_jenis_prw = rowData['kd_jenis_prw'];
var id_template = rowData['id_template'];
var Pemeriksaan = rowData['Pemeriksaan'];
var satuan = rowData['satuan'];
var nilai_rujukan_ld = rowData['nilai_rujukan_ld'];
var nilai_rujukan_la = rowData['nilai_rujukan_la'];
var nilai_rujukan_pd = rowData['nilai_rujukan_pd'];
var nilai_rujukan_pa = rowData['nilai_rujukan_pa'];
var bagian_rs = rowData['bagian_rs'];
var bhp = rowData['bhp'];
var bagian_perujuk = rowData['bagian_perujuk'];
var bagian_dokter = rowData['bagian_dokter'];
var bagian_laborat = rowData['bagian_laborat'];
var kso = rowData['kso'];
var menejemen = rowData['menejemen'];
var biaya_item = rowData['biaya_item'];
var urut = rowData['urut'];

            $("#typeact").val("edit");
  
            $('#kd_jenis_prw').val(kd_jenis_prw);
$('#id_template').val(id_template);
$('#Pemeriksaan').val(Pemeriksaan);
$('#satuan').val(satuan);
$('#nilai_rujukan_ld').val(nilai_rujukan_ld);
$('#nilai_rujukan_la').val(nilai_rujukan_la);
$('#nilai_rujukan_pd').val(nilai_rujukan_pd);
$('#nilai_rujukan_pa').val(nilai_rujukan_pa);
$('#bagian_rs').val(bagian_rs);
$('#bhp').val(bhp);
$('#bagian_perujuk').val(bagian_perujuk);
$('#bagian_dokter').val(bagian_dokter);
$('#bagian_laborat').val(bagian_laborat);
$('#kso').val(kso);
$('#menejemen').val(menejemen);
$('#biaya_item').val(biaya_item);
$('#urut').val(urut);

            $("#kd_jenis_prw").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Template Laboratorium");
            $("#modal_template_laboratorium").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_template_laboratorium").click(function () {
        var rowData = var_tbl_template_laboratorium.rows({ selected: true }).data()[0];


        if (rowData) {
var kd_jenis_prw = rowData['kd_jenis_prw'];
            bootbox.confirm('Anda yakin akan menghapus data dengan kd_jenis_prw="' + kd_jenis_prw, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['template_laboratorium','aksi'])?}",
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
                            if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
                                let payload = {
                                    'action' : 'del'
                                }
                                ws.send(JSON.stringify(payload));
                            }
                            var_tbl_template_laboratorium.draw();
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
    jQuery("#tambah_data_template_laboratorium").click(function () {

        $('#kd_jenis_prw').val('');
$('#id_template').val('');
$('#Pemeriksaan').val('');
$('#satuan').val('');
$('#nilai_rujukan_ld').val('');
$('#nilai_rujukan_la').val('');
$('#nilai_rujukan_pd').val('');
$('#nilai_rujukan_pa').val('');
$('#bagian_rs').val('');
$('#bhp').val('');
$('#bagian_perujuk').val('');
$('#bagian_dokter').val('');
$('#bagian_laborat').val('');
$('#kso').val('');
$('#menejemen').val('');
$('#biaya_item').val('');
$('#urut').val('');

        $("#typeact").val("add");
        $("#kd_jenis_prw").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Template Laboratorium");
        $("#modal_template_laboratorium").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_template_laboratorium").click(function () {

        var search_field_template_laboratorium = $('#search_field_template_laboratorium').val();
        var search_text_template_laboratorium = $('#search_text_template_laboratorium').val();

        $.ajax({
            url: "{?=url(['template_laboratorium','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_template_laboratorium: search_field_template_laboratorium, 
                search_text_template_laboratorium: search_text_template_laboratorium
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_template_laboratorium' class='table display dataTable' style='width:100%'><thead><th>Kd Jenis Prw</th><th>Id Template</th><th>Pemeriksaan</th><th>Satuan</th><th>Nilai Rujukan Ld</th><th>Nilai Rujukan La</th><th>Nilai Rujukan Pd</th><th>Nilai Rujukan Pa</th><th>Bagian Rs</th><th>Bhp</th><th>Bagian Perujuk</th><th>Bagian Dokter</th><th>Bagian Laborat</th><th>Kso</th><th>Menejemen</th><th>Biaya Item</th><th>Urut</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['kd_jenis_prw'] + '</td>';
eTable += '<td>' + res[i]['id_template'] + '</td>';
eTable += '<td>' + res[i]['Pemeriksaan'] + '</td>';
eTable += '<td>' + res[i]['satuan'] + '</td>';
eTable += '<td>' + res[i]['nilai_rujukan_ld'] + '</td>';
eTable += '<td>' + res[i]['nilai_rujukan_la'] + '</td>';
eTable += '<td>' + res[i]['nilai_rujukan_pd'] + '</td>';
eTable += '<td>' + res[i]['nilai_rujukan_pa'] + '</td>';
eTable += '<td>' + res[i]['bagian_rs'] + '</td>';
eTable += '<td>' + res[i]['bhp'] + '</td>';
eTable += '<td>' + res[i]['bagian_perujuk'] + '</td>';
eTable += '<td>' + res[i]['bagian_dokter'] + '</td>';
eTable += '<td>' + res[i]['bagian_laborat'] + '</td>';
eTable += '<td>' + res[i]['kso'] + '</td>';
eTable += '<td>' + res[i]['menejemen'] + '</td>';
eTable += '<td>' + res[i]['biaya_item'] + '</td>';
eTable += '<td>' + res[i]['urut'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_template_laboratorium').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_template_laboratorium").modal('show');
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
        doc.text("Tabel Data Template Laboratorium", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_template_laboratorium',
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
        // doc.save('table_data_template_laboratorium.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_template_laboratorium");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data template_laboratorium");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/template_laboratorium/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});