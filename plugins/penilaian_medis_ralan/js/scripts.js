jQuery().ready(function () {
    var var_tbl_penilaian_medis_ralan = $('#tbl_penilaian_medis_ralan').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url(['penilaian_medis_ralan','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_penilaian_medis_ralan = $('#search_field_penilaian_medis_ralan').val();
                var search_text_penilaian_medis_ralan = $('#search_text_penilaian_medis_ralan').val();
                
                data.search_field_penilaian_medis_ralan = search_field_penilaian_medis_ralan;
                data.search_text_penilaian_medis_ralan = search_text_penilaian_medis_ralan;
                
            }
        },
        "fnDrawCallback": function () {
            $('#more_data_penilaian_medis_ralan').on('click', function(e) {
                e.preventDefault();
                var clientX = e.originalEvent.clientX;
                var clientY = e.originalEvent.clientY;
                $('#tbl_penilaian_medis_ralan tr').contextMenu({x: clientX, y: clientY});
            });          
        }, 
        "columns": [
{ 'data': 'no_rawat' },
{ 'data': 'tanggal' },
{ 'data': 'kd_dokter' },
{ 'data': 'anamnesis' },
{ 'data': 'hubungan' },
{ 'data': 'keluhan_utama' },
{ 'data': 'rps' },
{ 'data': 'rpd' },
{ 'data': 'rpk' },
{ 'data': 'rpo' },
{ 'data': 'alergi' },
{ 'data': 'keadaan' },
{ 'data': 'gcs' },
{ 'data': 'kesadaran' },
{ 'data': 'td' },
{ 'data': 'nadi' },
{ 'data': 'rr' },
{ 'data': 'suhu' },
{ 'data': 'spo' },
{ 'data': 'bb' },
{ 'data': 'tb' },
{ 'data': 'kepala' },
{ 'data': 'gigi' },
{ 'data': 'tht' },
{ 'data': 'thoraks' },
{ 'data': 'abdomen' },
{ 'data': 'genital' },
{ 'data': 'ekstremitas' },
{ 'data': 'kulit' },
{ 'data': 'ket_fisik' },
{ 'data': 'ket_lokalis' },
{ 'data': 'penunjang' },
{ 'data': 'diagnosis' },
{ 'data': 'tata' },
{ 'data': 'konsulrujuk' }

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
{ 'targets': 33},
{ 'targets': 34}

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
        selector: '#tbl_penilaian_medis_ralan tr', 
        trigger: 'right',
        callback: function(key, options) {
          var rowData = var_tbl_penilaian_medis_ralan.rows({ selected: true }).data()[0];
          if (rowData != null) {
var no_rawat = rowData['no_rawat'];
            switch (key) {
                case 'detail' :
                    OpenModal(mlite.url + '/penilaian_medis_ralan/detail/' + no_rawat + '?t=' + mlite.token);
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

    $("form[name='form_penilaian_medis_ralan']").validate({
        rules: {
no_rawat: 'required',
tanggal: 'required',
kd_dokter: 'required',
anamnesis: 'required',
hubungan: 'required',
keluhan_utama: 'required',
rps: 'required',
rpd: 'required',
rpk: 'required',
rpo: 'required',
alergi: 'required',
keadaan: 'required',
gcs: 'required',
kesadaran: 'required',
td: 'required',
nadi: 'required',
rr: 'required',
suhu: 'required',
spo: 'required',
bb: 'required',
tb: 'required',
kepala: 'required',
gigi: 'required',
tht: 'required',
thoraks: 'required',
abdomen: 'required',
genital: 'required',
ekstremitas: 'required',
kulit: 'required',
ket_fisik: 'required',
ket_lokalis: 'required',
penunjang: 'required',
diagnosis: 'required',
tata: 'required',
konsulrujuk: 'required'

        },
        messages: {
no_rawat:'No Rawat tidak boleh kosong!',
tanggal:'Tanggal tidak boleh kosong!',
kd_dokter:'Kd Dokter tidak boleh kosong!',
anamnesis:'Anamnesis tidak boleh kosong!',
hubungan:'Hubungan tidak boleh kosong!',
keluhan_utama:'Keluhan Utama tidak boleh kosong!',
rps:'Rps tidak boleh kosong!',
rpd:'Rpd tidak boleh kosong!',
rpk:'Rpk tidak boleh kosong!',
rpo:'Rpo tidak boleh kosong!',
alergi:'Alergi tidak boleh kosong!',
keadaan:'Keadaan tidak boleh kosong!',
gcs:'Gcs tidak boleh kosong!',
kesadaran:'Kesadaran tidak boleh kosong!',
td:'Td tidak boleh kosong!',
nadi:'Nadi tidak boleh kosong!',
rr:'Rr tidak boleh kosong!',
suhu:'Suhu tidak boleh kosong!',
spo:'Spo tidak boleh kosong!',
bb:'Bb tidak boleh kosong!',
tb:'Tb tidak boleh kosong!',
kepala:'Kepala tidak boleh kosong!',
gigi:'Gigi tidak boleh kosong!',
tht:'Tht tidak boleh kosong!',
thoraks:'Thoraks tidak boleh kosong!',
abdomen:'Abdomen tidak boleh kosong!',
genital:'Genital tidak boleh kosong!',
ekstremitas:'Ekstremitas tidak boleh kosong!',
kulit:'Kulit tidak boleh kosong!',
ket_fisik:'Ket Fisik tidak boleh kosong!',
ket_lokalis:'Ket Lokalis tidak boleh kosong!',
penunjang:'Penunjang tidak boleh kosong!',
diagnosis:'Diagnosis tidak boleh kosong!',
tata:'Tata tidak boleh kosong!',
konsulrujuk:'Konsulrujuk tidak boleh kosong!'

        },
        submitHandler: function (form) {
var no_rawat= $('#no_rawat').val();
var tanggal= $('#tanggal').val();
var kd_dokter= $('#kd_dokter').val();
var anamnesis= $('#anamnesis').val();
var hubungan= $('#hubungan').val();
var keluhan_utama= $('#keluhan_utama').val();
var rps= $('#rps').val();
var rpd= $('#rpd').val();
var rpk= $('#rpk').val();
var rpo= $('#rpo').val();
var alergi= $('#alergi').val();
var keadaan= $('#keadaan').val();
var gcs= $('#gcs').val();
var kesadaran= $('#kesadaran').val();
var td= $('#td').val();
var nadi= $('#nadi').val();
var rr= $('#rr').val();
var suhu= $('#suhu').val();
var spo= $('#spo').val();
var bb= $('#bb').val();
var tb= $('#tb').val();
var kepala= $('#kepala').val();
var gigi= $('#gigi').val();
var tht= $('#tht').val();
var thoraks= $('#thoraks').val();
var abdomen= $('#abdomen').val();
var genital= $('#genital').val();
var ekstremitas= $('#ekstremitas').val();
var kulit= $('#kulit').val();
var ket_fisik= $('#ket_fisik').val();
var ket_lokalis= $('#ket_lokalis').val();
var penunjang= $('#penunjang').val();
var diagnosis= $('#diagnosis').val();
var tata= $('#tata').val();
var konsulrujuk= $('#konsulrujuk').val();

var typeact = $('#typeact').val();

var formData = new FormData(form); // tambahan
formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url(['penilaian_medis_ralan','aksi'])?}",
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
                            $("#modal_penilaian_medis_ralan").modal('hide');
                        } else {
                            bootbox.alert('<span class="text-danger">' + data.msg + '</span>');
                        }    
                    }
                    else if (typeact == "edit") {
                        if(data.status === 'success') {
                            bootbox.alert('<span class="text-success">' + data.msg + '</span>');
                            $("#modal_penilaian_medis_ralan").modal('hide');
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
                    var_tbl_penilaian_medis_ralan.draw();
                }
            })
        }
    });

    if(typeof ws != 'undefined' && typeof ws.readyState != 'undefined' && ws.readyState == 1){
        ws.onmessage = function(response){
            try{
                output = JSON.parse(response.data);
                if(output['action'] == 'add'){
                    var_tbl_penilaian_medis_ralan.draw();
                }
                if(output['action'] == 'edit'){
                    var_tbl_penilaian_medis_ralan.draw();
                }
                if(output['action'] == 'del'){
                    var_tbl_penilaian_medis_ralan.draw();
                }
            }catch(e){
                console.log(e);
            }
        }
    }

    // ==============================================================
    // KETIKA TOMBOL SEARCH DITEKAN
    // ==============================================================
    $('#filter_search_penilaian_medis_ralan').click(function () {
        var_tbl_penilaian_medis_ralan.draw();
    });

    // ===========================================
    // KETIKA TOMBOL EDIT DITEKAN
    // ===========================================

    $("#edit_data_penilaian_medis_ralan").click(function () {
        var rowData = var_tbl_penilaian_medis_ralan.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var no_rawat = rowData['no_rawat'];
var tanggal = rowData['tanggal'];
var kd_dokter = rowData['kd_dokter'];
var anamnesis = rowData['anamnesis'];
var hubungan = rowData['hubungan'];
var keluhan_utama = rowData['keluhan_utama'];
var rps = rowData['rps'];
var rpd = rowData['rpd'];
var rpk = rowData['rpk'];
var rpo = rowData['rpo'];
var alergi = rowData['alergi'];
var keadaan = rowData['keadaan'];
var gcs = rowData['gcs'];
var kesadaran = rowData['kesadaran'];
var td = rowData['td'];
var nadi = rowData['nadi'];
var rr = rowData['rr'];
var suhu = rowData['suhu'];
var spo = rowData['spo'];
var bb = rowData['bb'];
var tb = rowData['tb'];
var kepala = rowData['kepala'];
var gigi = rowData['gigi'];
var tht = rowData['tht'];
var thoraks = rowData['thoraks'];
var abdomen = rowData['abdomen'];
var genital = rowData['genital'];
var ekstremitas = rowData['ekstremitas'];
var kulit = rowData['kulit'];
var ket_fisik = rowData['ket_fisik'];
var ket_lokalis = rowData['ket_lokalis'];
var penunjang = rowData['penunjang'];
var diagnosis = rowData['diagnosis'];
var tata = rowData['tata'];
var konsulrujuk = rowData['konsulrujuk'];

            $("#typeact").val("edit");
  
            $('#no_rawat').val(no_rawat);
$('#tanggal').val(tanggal);
$('#kd_dokter').val(kd_dokter);
$('#anamnesis').val(anamnesis);
$('#hubungan').val(hubungan);
$('#keluhan_utama').val(keluhan_utama);
$('#rps').val(rps);
$('#rpd').val(rpd);
$('#rpk').val(rpk);
$('#rpo').val(rpo);
$('#alergi').val(alergi);
$('#keadaan').val(keadaan);
$('#gcs').val(gcs);
$('#kesadaran').val(kesadaran);
$('#td').val(td);
$('#nadi').val(nadi);
$('#rr').val(rr);
$('#suhu').val(suhu);
$('#spo').val(spo);
$('#bb').val(bb);
$('#tb').val(tb);
$('#kepala').val(kepala);
$('#gigi').val(gigi);
$('#tht').val(tht);
$('#thoraks').val(thoraks);
$('#abdomen').val(abdomen);
$('#genital').val(genital);
$('#ekstremitas').val(ekstremitas);
$('#kulit').val(kulit);
$('#ket_fisik').val(ket_fisik);
$('#ket_lokalis').val(ket_lokalis);
$('#penunjang').val(penunjang);
$('#diagnosis').val(diagnosis);
$('#tata').val(tata);
$('#konsulrujuk').val(konsulrujuk);

            $("#no_rawat").prop('readonly', true); // GA BISA DIEDIT KALI READONLY
            $('#modal-title').text("Edit Data Penilaian Medis Ralan");
            $("#modal_penilaian_medis_ralan").modal('show');
        }
        else {
            bootbox.alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_penilaian_medis_ralan").click(function () {
        var rowData = var_tbl_penilaian_medis_ralan.rows({ selected: true }).data()[0];


        if (rowData) {
var no_rawat = rowData['no_rawat'];
            bootbox.confirm('Anda yakin akan menghapus data dengan no_rawat="' + no_rawat, function(result) {
                if(result) {
                    $.ajax({
                        url: "{?=url(['penilaian_medis_ralan','aksi'])?}",
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
                            var_tbl_penilaian_medis_ralan.draw();
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
    jQuery("#tambah_data_penilaian_medis_ralan").click(function () {

        $('#no_rawat').val('');
$('#tanggal').val('');
$('#kd_dokter').val('');
$('#anamnesis').val('');
$('#hubungan').val('');
$('#keluhan_utama').val('');
$('#rps').val('');
$('#rpd').val('');
$('#rpk').val('');
$('#rpo').val('');
$('#alergi').val('');
$('#keadaan').val('');
$('#gcs').val('');
$('#kesadaran').val('');
$('#td').val('');
$('#nadi').val('');
$('#rr').val('');
$('#suhu').val('');
$('#spo').val('');
$('#bb').val('');
$('#tb').val('');
$('#kepala').val('');
$('#gigi').val('');
$('#tht').val('');
$('#thoraks').val('');
$('#abdomen').val('');
$('#genital').val('');
$('#ekstremitas').val('');
$('#kulit').val('');
$('#ket_fisik').val('');
$('#ket_lokalis').val('');
$('#penunjang').val('');
$('#diagnosis').val('');
$('#tata').val('');
$('#konsulrujuk').val('');

        $("#typeact").val("add");
        $("#no_rawat").prop('readonly', false);
        
        $('#modal-title').text("Tambah Data Penilaian Medis Ralan");
        $("#modal_penilaian_medis_ralan").modal('show');
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_penilaian_medis_ralan").click(function () {

        var search_field_penilaian_medis_ralan = $('#search_field_penilaian_medis_ralan').val();
        var search_text_penilaian_medis_ralan = $('#search_text_penilaian_medis_ralan').val();

        $.ajax({
            url: "{?=url(['penilaian_medis_ralan','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_penilaian_medis_ralan: search_field_penilaian_medis_ralan, 
                search_text_penilaian_medis_ralan: search_text_penilaian_medis_ralan
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_penilaian_medis_ralan' class='table display dataTable' style='width:100%'><thead><th>No Rawat</th><th>Tanggal</th><th>Kd Dokter</th><th>Anamnesis</th><th>Hubungan</th><th>Keluhan Utama</th><th>Rps</th><th>Rpd</th><th>Rpk</th><th>Rpo</th><th>Alergi</th><th>Keadaan</th><th>Gcs</th><th>Kesadaran</th><th>Td</th><th>Nadi</th><th>Rr</th><th>Suhu</th><th>Spo</th><th>Bb</th><th>Tb</th><th>Kepala</th><th>Gigi</th><th>Tht</th><th>Thoraks</th><th>Abdomen</th><th>Genital</th><th>Ekstremitas</th><th>Kulit</th><th>Ket Fisik</th><th>Ket Lokalis</th><th>Penunjang</th><th>Diagnosis</th><th>Tata</th><th>Konsulrujuk</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['no_rawat'] + '</td>';
eTable += '<td>' + res[i]['tanggal'] + '</td>';
eTable += '<td>' + res[i]['kd_dokter'] + '</td>';
eTable += '<td>' + res[i]['anamnesis'] + '</td>';
eTable += '<td>' + res[i]['hubungan'] + '</td>';
eTable += '<td>' + res[i]['keluhan_utama'] + '</td>';
eTable += '<td>' + res[i]['rps'] + '</td>';
eTable += '<td>' + res[i]['rpd'] + '</td>';
eTable += '<td>' + res[i]['rpk'] + '</td>';
eTable += '<td>' + res[i]['rpo'] + '</td>';
eTable += '<td>' + res[i]['alergi'] + '</td>';
eTable += '<td>' + res[i]['keadaan'] + '</td>';
eTable += '<td>' + res[i]['gcs'] + '</td>';
eTable += '<td>' + res[i]['kesadaran'] + '</td>';
eTable += '<td>' + res[i]['td'] + '</td>';
eTable += '<td>' + res[i]['nadi'] + '</td>';
eTable += '<td>' + res[i]['rr'] + '</td>';
eTable += '<td>' + res[i]['suhu'] + '</td>';
eTable += '<td>' + res[i]['spo'] + '</td>';
eTable += '<td>' + res[i]['bb'] + '</td>';
eTable += '<td>' + res[i]['tb'] + '</td>';
eTable += '<td>' + res[i]['kepala'] + '</td>';
eTable += '<td>' + res[i]['gigi'] + '</td>';
eTable += '<td>' + res[i]['tht'] + '</td>';
eTable += '<td>' + res[i]['thoraks'] + '</td>';
eTable += '<td>' + res[i]['abdomen'] + '</td>';
eTable += '<td>' + res[i]['genital'] + '</td>';
eTable += '<td>' + res[i]['ekstremitas'] + '</td>';
eTable += '<td>' + res[i]['kulit'] + '</td>';
eTable += '<td>' + res[i]['ket_fisik'] + '</td>';
eTable += '<td>' + res[i]['ket_lokalis'] + '</td>';
eTable += '<td>' + res[i]['penunjang'] + '</td>';
eTable += '<td>' + res[i]['diagnosis'] + '</td>';
eTable += '<td>' + res[i]['tata'] + '</td>';
eTable += '<td>' + res[i]['konsulrujuk'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_penilaian_medis_ralan').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_penilaian_medis_ralan").modal('show');
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
        doc.text("Tabel Data Penilaian Medis Ralan", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_penilaian_medis_ralan',
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
        // doc.save('table_data_penilaian_medis_ralan.pdf');
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_penilaian_medis_ralan");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data penilaian_medis_ralan");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })

    $("#view_chart").click(function () {
        window.open(mlite.url + '/penilaian_medis_ralan/chart?t=' + mlite.token, '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
    })   

});