jQuery().ready(function () {
    var var_tbl_detail_periksa_lab = $('#tbl_detail_periksa_lab').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['detail_periksa_lab','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_detail_periksa_lab = $('#search_field_detail_periksa_lab').val();
                var search_text_detail_periksa_lab = $('#search_text_detail_periksa_lab').val();
                
                data.search_field_detail_periksa_lab = search_field_detail_periksa_lab;
                data.search_text_detail_periksa_lab = search_text_detail_periksa_lab;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_detail_periksa_lab').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_detail_periksa_lab tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'no_rawat' },
{ 'data': 'kd_jenis_prw' },
{ 'data': 'tgl_periksa' },
{ 'data': 'jam' },
{ 'data': 'id_template' },
{ 'data': 'nilai' },
{ 'data': 'nilai_rujukan' },
{ 'data': 'keterangan' },
{ 'data': 'bagian_rs' },
{ 'data': 'bhp' },
{ 'data': 'bagian_perujuk' },
{ 'data': 'bagian_dokter' },
{ 'data': 'bagian_laborat' },
{ 'data': 'kso' },
{ 'data': 'menejemen' },
{ 'data': 'biaya_item' }

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
{ 'targets': 15}

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
        selector: '#tbl_detail_periksa_lab tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_detail_periksa_lab.rows({ selected: true }).data()[0];
          if (rowData != null) {
var no_rawat = rowData['no_rawat'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/detail_periksa_lab/detail/' + no_rawat + '?t=' + mlite.token);
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

    $("form[name='form_detail_periksa_lab']").validate({
        rules: {
no_rawat: 'required',
kd_jenis_prw: 'required',
tgl_periksa: 'required',
jam: 'required',
id_template: 'required',
nilai: 'required',
nilai_rujukan: 'required',
keterangan: 'required',
bagian_rs: 'required',
bhp: 'required',
bagian_perujuk: 'required',
bagian_dokter: 'required',
bagian_laborat: 'required',
kso: 'required',
menejemen: 'required',
biaya_item: 'required'

        },
        messages: {
no_rawat:'No Rawat tidak boleh kosong!',
kd_jenis_prw:'Kd Jenis Prw tidak boleh kosong!',
tgl_periksa:'Tgl Periksa tidak boleh kosong!',
jam:'Jam tidak boleh kosong!',
id_template:'Id Template tidak boleh kosong!',
nilai:'Nilai tidak boleh kosong!',
nilai_rujukan:'Nilai Rujukan tidak boleh kosong!',
keterangan:'Keterangan tidak boleh kosong!',
bagian_rs:'Bagian Rs tidak boleh kosong!',
bhp:'Bhp tidak boleh kosong!',
bagian_perujuk:'Bagian Perujuk tidak boleh kosong!',
bagian_dokter:'Bagian Dokter tidak boleh kosong!',
bagian_laborat:'Bagian Laborat tidak boleh kosong!',
kso:'Kso tidak boleh kosong!',
menejemen:'Menejemen tidak boleh kosong!',
biaya_item:'Biaya Item tidak boleh kosong!'

        },
        submitHandler: function (form) {
var no_rawat= $('#no_rawat').val();
var kd_jenis_prw= $('#kd_jenis_prw').val();
var tgl_periksa= $('#tgl_periksa').val();
var jam= $('#jam').val();
var id_template= $('#id_template').val();
var nilai= $('#nilai').val();
var nilai_rujukan= $('#nilai_rujukan').val();
var keterangan= $('#keterangan').val();
var bagian_rs= $('#bagian_rs').val();
var bhp= $('#bhp').val();
var bagian_perujuk= $('#bagian_perujuk').val();
var bagian_dokter= $('#bagian_dokter').val();
var bagian_laborat= $('#bagian_laborat').val();
var kso= $('#kso').val();
var menejemen= $('#menejemen').val();
var biaya_item= $('#biaya_item').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['detail_periksa_lab','aksi'])?}",
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
                            $("#modal_detail_periksa_lab").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_detail_periksa_lab").modal('hide');
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
                    var_tbl_detail_periksa_lab.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_detail_periksa_lab.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_detail_periksa_lab.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_detail_periksa_lab.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_detail_periksa_lab').click(function () {
        var_tbl_detail_periksa_lab.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_detail_periksa_lab").click(function () {
        var rowData = var_tbl_detail_periksa_lab.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var no_rawat = rowData['no_rawat'];
var kd_jenis_prw = rowData['kd_jenis_prw'];
var tgl_periksa = rowData['tgl_periksa'];
var jam = rowData['jam'];
var id_template = rowData['id_template'];
var nilai = rowData['nilai'];
var nilai_rujukan = rowData['nilai_rujukan'];
var keterangan = rowData['keterangan'];
var bagian_rs = rowData['bagian_rs'];
var bhp = rowData['bhp'];
var bagian_perujuk = rowData['bagian_perujuk'];
var bagian_dokter = rowData['bagian_dokter'];
var bagian_laborat = rowData['bagian_laborat'];
var kso = rowData['kso'];
var menejemen = rowData['menejemen'];
var biaya_item = rowData['biaya_item'];

            $("#typeact").val("edit");
  
            $('#no_rawat').val(no_rawat);
$('#kd_jenis_prw').val(kd_jenis_prw);
$('#tgl_periksa').val(tgl_periksa);
$('#jam').val(jam);
$('#id_template').val(id_template);
$('#nilai').val(nilai);
$('#nilai_rujukan').val(nilai_rujukan);
$('#keterangan').val(keterangan);
$('#bagian_rs').val(bagian_rs);
$('#bhp').val(bhp);
$('#bagian_perujuk').val(bagian_perujuk);
$('#bagian_dokter').val(bagian_dokter);
$('#bagian_laborat').val(bagian_laborat);
$('#kso').val(kso);
$('#menejemen').val(menejemen);
$('#biaya_item').val(biaya_item);

            $("#no_rawat").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Detail Periksa Lab");
            $("#modal_detail_periksa_lab").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_detail_periksa_lab").click(function () {
        var rowData = var_tbl_detail_periksa_lab.rows({ selected: true }).data()[0];


        if (rowData) {
var no_rawat = rowData['no_rawat'];
            bootbox.confirm('Anda yakin akan menghapus data dengan no_rawat="' + no_rawat, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['detail_periksa_lab','aksi'])?}",
                        method: "POST",
                        data: {
                            no_rawat: no_rawat,
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
                            var_tbl_detail_periksa_lab.draw();
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
    jQuery("#tambah_data_detail_periksa_lab").click(function () {

        $('#no_rawat').val('');
$('#kd_jenis_prw').val('');
$('#tgl_periksa').val('');
$('#jam').val('');
$('#id_template').val('');
$('#nilai').val('');
$('#nilai_rujukan').val('');
$('#keterangan').val('');
$('#bagian_rs').val('');
$('#bhp').val('');
$('#bagian_perujuk').val('');
$('#bagian_dokter').val('');
$('#bagian_laborat').val('');
$('#kso').val('');
$('#menejemen').val('');
$('#biaya_item').val('');

        $("#typeact").val("add");
        $("#no_rawat").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Detail Periksa Lab");
        $("#modal_detail_periksa_lab").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_detail_periksa_lab").click(function () {

        var search_field_detail_periksa_lab = $('#search_field_detail_periksa_lab').val();
        var search_text_detail_periksa_lab = $('#search_text_detail_periksa_lab').val();

        $.ajax({
            url: "{?=url(['detail_periksa_lab','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_detail_periksa_lab: search_field_detail_periksa_lab, 
                search_text_detail_periksa_lab: search_text_detail_periksa_lab
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_detail_periksa_lab' class='table display dataTable' style='width:100%'><thead><th>No Rawat</th><th>Kd Jenis Prw</th><th>Tgl Periksa</th><th>Jam</th><th>Id Template</th><th>Nilai</th><th>Nilai Rujukan</th><th>Keterangan</th><th>Bagian Rs</th><th>Bhp</th><th>Bagian Perujuk</th><th>Bagian Dokter</th><th>Bagian Laborat</th><th>Kso</th><th>Menejemen</th><th>Biaya Item</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['no_rawat'] + '</td>';
eTable += '<td>' + res[i]['kd_jenis_prw'] + '</td>';
eTable += '<td>' + res[i]['tgl_periksa'] + '</td>';
eTable += '<td>' + res[i]['jam'] + '</td>';
eTable += '<td>' + res[i]['id_template'] + '</td>';
eTable += '<td>' + res[i]['nilai'] + '</td>';
eTable += '<td>' + res[i]['nilai_rujukan'] + '</td>';
eTable += '<td>' + res[i]['keterangan'] + '</td>';
eTable += '<td>' + res[i]['bagian_rs'] + '</td>';
eTable += '<td>' + res[i]['bhp'] + '</td>';
eTable += '<td>' + res[i]['bagian_perujuk'] + '</td>';
eTable += '<td>' + res[i]['bagian_dokter'] + '</td>';
eTable += '<td>' + res[i]['bagian_laborat'] + '</td>';
eTable += '<td>' + res[i]['kso'] + '</td>';
eTable += '<td>' + res[i]['menejemen'] + '</td>';
eTable += '<td>' + res[i]['biaya_item'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_detail_periksa_lab').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_detail_periksa_lab").modal('show');
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
        doc.text("Tabel Data Detail Periksa Lab", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_detail_periksa_lab',
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
        // doc.save('table_data_detail_periksa_lab.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_detail_periksa_lab");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data detail_periksa_lab");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/detail_periksa_lab/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});