jQuery().ready(function () {
    var var_tbl_mlite_penilaian_medis_ralan = $('#tbl_mlite_penilaian_medis_ralan').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url([ADMIN,'penilaian_medis_ralan','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_mlite_penilaian_medis_ralan = $('#search_field_mlite_penilaian_medis_ralan').val();
                var search_text_mlite_penilaian_medis_ralan = $('#search_text_mlite_penilaian_medis_ralan').val();
                
                data.search_field_mlite_penilaian_medis_ralan = search_field_mlite_penilaian_medis_ralan;
                data.search_text_mlite_penilaian_medis_ralan = search_text_mlite_penilaian_medis_ralan;
                
            }
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
        buttons: [],
        "scrollCollapse": true,
        // "scrollY": '48vh', 
        "pageLength":'25', 
        "lengthChange": true,
        "scrollX": true,
        dom: "<'row'<'col-sm-12'tr>><<'pmd-datatable-pagination' l i p>>"
    });

    // ==============================================================
    // FORM VALIDASI
    // ==============================================================

    $("form[name='form_mlite_penilaian_medis_ralan']").validate({
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
no_rawat:'no_rawat tidak boleh kosong!',
tanggal:'tanggal tidak boleh kosong!',
kd_dokter:'kd_dokter tidak boleh kosong!',
anamnesis:'anamnesis tidak boleh kosong!',
hubungan:'hubungan tidak boleh kosong!',
keluhan_utama:'keluhan_utama tidak boleh kosong!',
rps:'rps tidak boleh kosong!',
rpd:'rpd tidak boleh kosong!',
rpk:'rpk tidak boleh kosong!',
rpo:'rpo tidak boleh kosong!',
alergi:'alergi tidak boleh kosong!',
keadaan:'keadaan tidak boleh kosong!',
gcs:'gcs tidak boleh kosong!',
kesadaran:'kesadaran tidak boleh kosong!',
td:'td tidak boleh kosong!',
nadi:'nadi tidak boleh kosong!',
rr:'rr tidak boleh kosong!',
suhu:'suhu tidak boleh kosong!',
spo:'spo tidak boleh kosong!',
bb:'bb tidak boleh kosong!',
tb:'tb tidak boleh kosong!',
kepala:'kepala tidak boleh kosong!',
gigi:'gigi tidak boleh kosong!',
tht:'tht tidak boleh kosong!',
thoraks:'thoraks tidak boleh kosong!',
abdomen:'abdomen tidak boleh kosong!',
genital:'genital tidak boleh kosong!',
ekstremitas:'ekstremitas tidak boleh kosong!',
kulit:'kulit tidak boleh kosong!',
ket_fisik:'ket_fisik tidak boleh kosong!',
ket_lokalis:'ket_lokalis tidak boleh kosong!',
penunjang:'penunjang tidak boleh kosong!',
diagnosis:'diagnosis tidak boleh kosong!',
tata:'tata tidak boleh kosong!',
konsulrujuk:'konsulrujuk tidak boleh kosong!'

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
                url: "{?=url([ADMIN,'penilaian_medis_ralan','aksi'])?}",
                method: "POST",
                contentType: false, // tambahan
                processData: false, // tambahan
                data: formData,
                success: function (data) {
                    if (typeact == "add") {
                        alert("Data Berhasil Ditambah");
                    }
                    else if (typeact == "edit") {
                        alert("Data Berhasil Diubah");
                    }
                    $("#modal_cs").hide();
                    location.reload(true);
                }
            })
        }
    });

    // ==============================================================
    // KETIKA MENGETIK DI INPUT SEARCH
    // ==============================================================
    $('#search_text_mlite_penilaian_medis_ralan').keyup(function () {
        var_tbl_mlite_penilaian_medis_ralan.draw();
    });
    // ==============================================================
    // CLICK TANDA X DI INPUT SEARCH
    // ==============================================================
    $("#searchclear_mlite_penilaian_medis_ralan").click(function () {
        $("#search_text_mlite_penilaian_medis_ralan").val("");
        var_tbl_mlite_penilaian_medis_ralan.draw();
    });

    // ===========================================
    // Ketika tombol Edit di tekan
    // ===========================================

    $("#edit_data_mlite_penilaian_medis_ralan").click(function () {
        var rowData = var_tbl_mlite_penilaian_medis_ralan.rows({ selected: true }).data()[0];
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
$('#anamnesis').val(anamnesis).change();
$('#hubungan').val(hubungan);
$('#keluhan_utama').val(keluhan_utama);
$('#rps').val(rps);
$('#rpd').val(rpd);
$('#rpk').val(rpk);
$('#rpo').val(rpo);
$('#alergi').val(alergi);
$('#keadaan').val(keadaan).change();
$('#gcs').val(gcs);
$('#kesadaran').val(kesadaran).change();
$('#td').val(td);
$('#nadi').val(nadi);
$('#rr').val(rr);
$('#suhu').val(suhu);
$('#spo').val(spo);
$('#bb').val(bb);
$('#tb').val(tb);
$('#kepala').val(kepala).change();
$('#gigi').val(gigi).change();
$('#tht').val(tht).change();
$('#thoraks').val(thoraks).change();
$('#abdomen').val(abdomen).change();
$('#genital').val(genital).change();
$('#ekstremitas').val(ekstremitas).change();
$('#kulit').val(kulit).change();
$('#ket_fisik').val(ket_fisik);
$('#ket_lokalis').val(ket_lokalis);
$('#penunjang').val(penunjang);
$('#diagnosis').val(diagnosis);
$('#tata').val(tata);
$('#konsulrujuk').val(konsulrujuk);

            //$("#no_rawat").prop('disabled', true); // GA BISA DIEDIT KALI DISABLE
            $('#modal-title').text("Edit Data Penilaian Medis Ralan");
            $("#modal_mlite_penilaian_medis_ralan").modal();
        }
        else {
            alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_mlite_penilaian_medis_ralan").click(function () {
        var rowData = var_tbl_mlite_penilaian_medis_ralan.rows({ selected: true }).data()[0];


        if (rowData) {
var no_rawat = rowData['no_rawat'];
            var a = confirm("Anda yakin akan menghapus data dengan no_rawat=" + no_rawat);
            if (a) {

                $.ajax({
                    url: "{?=url([ADMIN,'penilaian_medis_ralan','aksi'])?}",
                    method: "POST",
                    data: {
                        no_rawat: no_rawat,
                        typeact: 'del'
                    },
                    success: function (data) {
                        data = JSON.parse(data);
                        if(data.status === 'success') {
                            alert(data.msg);
                        } else {
                            alert(data.msg);
                        }
                        location.reload(true);
                    }
                })
            }
        }
        else {
            alert("Pilih satu baris untuk dihapus");
        }
    });

    // ==============================================================
    // TOMBOL TAMBAH DATA DI CLICK
    // ==============================================================

    let searchParams = new URLSearchParams(window.location.search)

    if(window.location.search.indexOf('no_rawat') !== -1) { 
        $('#search_text_mlite_penilaian_medis_ralan').val(searchParams.get('no_rawat'));
        var_tbl_mlite_penilaian_medis_ralan.draw();
        if(searchParams.get('modal') == 'true') {
            $("#modal_mlite_penilaian_medis_ralan").modal();
            $('#no_rawat').val(searchParams.get('no_rawat'));    
        }
    }

    jQuery("#tambah_data_mlite_penilaian_medis_ralan").click(function () {

        $('#no_rawat').val('');

        if(window.location.search.indexOf('no_rawat') !== -1) { 
            $('#no_rawat').val(searchParams.get('no_rawat'));
        }

        $('#tanggal').val('');
$('#kd_dokter').val('{?=$this->core->getUserInfo('username', null, true)?}');
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
        $("#no_rawat").prop('disabled', false);
        
        $('#modal-title').text("Tambah Data Penilaian Medis Ralan");
        $("#modal_mlite_penilaian_medis_ralan").modal();
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_mlite_penilaian_medis_ralan").click(function () {

        var search_field_mlite_penilaian_medis_ralan = $('#search_field_mlite_penilaian_medis_ralan').val();
        var search_text_mlite_penilaian_medis_ralan = $('#search_text_mlite_penilaian_medis_ralan').val();

        $.ajax({
            url: "{?=url([ADMIN,'penilaian_medis_ralan','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_mlite_penilaian_medis_ralan: search_field_mlite_penilaian_medis_ralan, 
                search_text_mlite_penilaian_medis_ralan: search_text_mlite_penilaian_medis_ralan
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_mlite_penilaian_medis_ralan' class='table display dataTable' style='width:100%'><thead><th>No Rawat</th><th>Tanggal</th><th>Kd Dokter</th><th>Anamnesis</th><th>Hubungan</th><th>Keluhan Utama</th><th>Rps</th><th>Rpd</th><th>Rpk</th><th>Rpo</th><th>Alergi</th><th>Keadaan</th><th>Gcs</th><th>Kesadaran</th><th>Td</th><th>Nadi</th><th>Rr</th><th>Suhu</th><th>Spo</th><th>Bb</th><th>Tb</th><th>Kepala</th><th>Gigi</th><th>Tht</th><th>Thoraks</th><th>Abdomen</th><th>Genital</th><th>Ekstremitas</th><th>Kulit</th><th>Ket Fisik</th><th>Ket Lokalis</th><th>Penunjang</th><th>Diagnosis</th><th>Tata</th><th>Konsulrujuk</th></thead>";
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
                $('#forTable_mlite_penilaian_medis_ralan').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_mlite_penilaian_medis_ralan").modal();
    });

    // ==============================================================
    // TOMBOL DETAIL mlite_penilaian_medis_ralan DI CLICK
    // ==============================================================
    jQuery("#lihat_detail_mlite_penilaian_medis_ralan").click(function (event) {

        var rowData = var_tbl_mlite_penilaian_medis_ralan.rows({ selected: true }).data()[0];

        if (rowData) {
            var no_rawat = rowData['no_rawat'];
            var baseURL = mlite.url + '/' + mlite.admin;
            event.preventDefault();
            var loadURL =  baseURL + '/penilaian_medis_ralan/detail/' + no_rawat.replace(/\//g,'') + '?t=' + mlite.token;
        
            var modal = $('#modal_detail_mlite_penilaian_medis_ralan');
            var modalContent = $('#modal_detail_mlite_penilaian_medis_ralan .modal-content');
        
            modal.off('show.bs.modal');
            modal.on('show.bs.modal', function () {
                modalContent.load(loadURL);
            }).modal();
            return false;
        
        }
        else {
            alert("Pilih satu baris untuk detail");
        }
    });

    jQuery("#lihat_detail_mlite_penilaian_medis_ralan2").click(function (event) {

        var rowData = var_tbl_mlite_penilaian_medis_ralan.rows({ selected: true }).data()[0];

        if (rowData) {
            var no_rawat = rowData['no_rawat'];
            var baseURL = mlite.url + '/' + mlite.admin;
            event.preventDefault();
            {if: $this->core->ActiveModule('jasper')}
                var loadURL =  baseURL + '/jasper/awalmedisralan/' + no_rawat.replace(/\//g,'') + '?t=' + mlite.token;
                $("#modal_detail_mlite_penilaian_medis_ralan").modal('show').html('<div style="text-align:center;margin:20px auto;width:90%;height:95%;"><iframe src="' + loadURL + '" frameborder="no" width="100%" height="100%"></iframe></div>');
            {else}
                bootbox.alert('Cetak PDF tidak bisa dilakukan. Silahkan aktifkan Modul Premium PDF Jasper!');
            {/if}

            return false;
        
        }
        else {
            alert("Pilih satu baris untuk detail");
        }
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
        doc.text("Tabel Data Mlite Penilaian Medis Ralan", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_mlite_penilaian_medis_ralan',
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
                doc.text(footerStr, data.settings.margin.left, doc.internal.pageSize.height - 10);
           }
        });
        if (typeof doc.putTotalPages === 'function') {
            doc.putTotalPages(totalPagesExp);
        }
        // doc.save('table_data_mlite_penilaian_medis_ralan.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_mlite_penilaian_medis_ralan");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data mlite_penilaian_medis_ralan");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })
});