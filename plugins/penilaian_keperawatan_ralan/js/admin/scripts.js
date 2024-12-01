jQuery().ready(function () {
    var var_tbl_mlite_penilaian_awal_keperawatan_ralan = $('#tbl_mlite_penilaian_awal_keperawatan_ralan').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url([ADMIN,'penilaian_keperawatan_ralan','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_mlite_penilaian_awal_keperawatan_ralan = $('#search_field_mlite_penilaian_awal_keperawatan_ralan').val();
                var search_text_mlite_penilaian_awal_keperawatan_ralan = $('#search_text_mlite_penilaian_awal_keperawatan_ralan').val();
                
                data.search_field_mlite_penilaian_awal_keperawatan_ralan = search_field_mlite_penilaian_awal_keperawatan_ralan;
                data.search_text_mlite_penilaian_awal_keperawatan_ralan = search_text_mlite_penilaian_awal_keperawatan_ralan;
                
            }
        },
        "columns": [
{ 'data': 'no_rawat' },
{ 'data': 'tanggal' },
{ 'data': 'informasi' },
{ 'data': 'td' },
{ 'data': 'nadi' },
{ 'data': 'rr' },
{ 'data': 'suhu' },
{ 'data': 'gcs' },
{ 'data': 'bb' },
{ 'data': 'tb' },
{ 'data': 'bmi' },
{ 'data': 'keluhan_utama' },
{ 'data': 'rpd' },
{ 'data': 'rpk' },
{ 'data': 'rpo' },
{ 'data': 'alergi' },
{ 'data': 'alat_bantu' },
{ 'data': 'ket_bantu' },
{ 'data': 'prothesa' },
{ 'data': 'ket_pro' },
{ 'data': 'adl' },
{ 'data': 'status_psiko' },
{ 'data': 'ket_psiko' },
{ 'data': 'hub_keluarga' },
{ 'data': 'tinggal_dengan' },
{ 'data': 'ket_tinggal' },
{ 'data': 'ekonomi' },
{ 'data': 'budaya' },
{ 'data': 'ket_budaya' },
{ 'data': 'edukasi' },
{ 'data': 'ket_edukasi' },
{ 'data': 'berjalan_a' },
{ 'data': 'berjalan_b' },
{ 'data': 'berjalan_c' },
{ 'data': 'hasil' },
{ 'data': 'lapor' },
{ 'data': 'ket_lapor' },
{ 'data': 'sg1' },
{ 'data': 'nilai1' },
{ 'data': 'sg2' },
{ 'data': 'nilai2' },
{ 'data': 'total_hasil' },
{ 'data': 'nyeri' },
{ 'data': 'provokes' },
{ 'data': 'ket_provokes' },
{ 'data': 'quality' },
{ 'data': 'ket_quality' },
{ 'data': 'lokasi' },
{ 'data': 'menyebar' },
{ 'data': 'skala_nyeri' },
{ 'data': 'durasi' },
{ 'data': 'nyeri_hilang' },
{ 'data': 'ket_nyeri' },
{ 'data': 'pada_dokter' },
{ 'data': 'ket_dokter' },
{ 'data': 'rencana' },
{ 'data': 'nip' }

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
{ 'targets': 34},
{ 'targets': 35},
{ 'targets': 36},
{ 'targets': 37},
{ 'targets': 38},
{ 'targets': 39},
{ 'targets': 40},
{ 'targets': 41},
{ 'targets': 42},
{ 'targets': 43},
{ 'targets': 44},
{ 'targets': 45},
{ 'targets': 46},
{ 'targets': 47},
{ 'targets': 48},
{ 'targets': 49},
{ 'targets': 50},
{ 'targets': 51},
{ 'targets': 52},
{ 'targets': 53},
{ 'targets': 54},
{ 'targets': 55},
{ 'targets': 56}

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

    $("form[name='form_mlite_penilaian_awal_keperawatan_ralan']").validate({
        rules: {
no_rawat: 'required',
tanggal: 'required',
informasi: 'required',
td: 'required',
nadi: 'required',
rr: 'required',
suhu: 'required',
gcs: 'required',
bb: 'required',
tb: 'required',
bmi: 'required',
keluhan_utama: 'required',
rpd: 'required',
rpk: 'required',
rpo: 'required',
alergi: 'required',
alat_bantu: 'required',
ket_bantu: 'required',
prothesa: 'required',
ket_pro: 'required',
adl: 'required',
status_psiko: 'required',
ket_psiko: 'required',
hub_keluarga: 'required',
tinggal_dengan: 'required',
ket_tinggal: 'required',
ekonomi: 'required',
budaya: 'required',
ket_budaya: 'required',
edukasi: 'required',
ket_edukasi: 'required',
berjalan_a: 'required',
berjalan_b: 'required',
berjalan_c: 'required',
hasil: 'required',
lapor: 'required',
ket_lapor: 'required',
sg1: 'required',
nilai1: 'required',
sg2: 'required',
nilai2: 'required',
total_hasil: 'required',
nyeri: 'required',
provokes: 'required',
ket_provokes: 'required',
quality: 'required',
ket_quality: 'required',
lokasi: 'required',
menyebar: 'required',
skala_nyeri: 'required',
durasi: 'required',
nyeri_hilang: 'required',
ket_nyeri: 'required',
pada_dokter: 'required',
ket_dokter: 'required',
rencana: 'required',
nip: 'required'

        },
        messages: {
no_rawat:'no_rawat tidak boleh kosong!',
tanggal:'tanggal tidak boleh kosong!',
informasi:'informasi tidak boleh kosong!',
td:'td tidak boleh kosong!',
nadi:'nadi tidak boleh kosong!',
rr:'rr tidak boleh kosong!',
suhu:'suhu tidak boleh kosong!',
gcs:'gcs tidak boleh kosong!',
bb:'bb tidak boleh kosong!',
tb:'tb tidak boleh kosong!',
bmi:'bmi tidak boleh kosong!',
keluhan_utama:'keluhan_utama tidak boleh kosong!',
rpd:'rpd tidak boleh kosong!',
rpk:'rpk tidak boleh kosong!',
rpo:'rpo tidak boleh kosong!',
alergi:'alergi tidak boleh kosong!',
alat_bantu:'alat_bantu tidak boleh kosong!',
ket_bantu:'ket_bantu tidak boleh kosong!',
prothesa:'prothesa tidak boleh kosong!',
ket_pro:'ket_pro tidak boleh kosong!',
adl:'adl tidak boleh kosong!',
status_psiko:'status_psiko tidak boleh kosong!',
ket_psiko:'ket_psiko tidak boleh kosong!',
hub_keluarga:'hub_keluarga tidak boleh kosong!',
tinggal_dengan:'tinggal_dengan tidak boleh kosong!',
ket_tinggal:'ket_tinggal tidak boleh kosong!',
ekonomi:'ekonomi tidak boleh kosong!',
budaya:'budaya tidak boleh kosong!',
ket_budaya:'ket_budaya tidak boleh kosong!',
edukasi:'edukasi tidak boleh kosong!',
ket_edukasi:'ket_edukasi tidak boleh kosong!',
berjalan_a:'berjalan_a tidak boleh kosong!',
berjalan_b:'berjalan_b tidak boleh kosong!',
berjalan_c:'berjalan_c tidak boleh kosong!',
hasil:'hasil tidak boleh kosong!',
lapor:'lapor tidak boleh kosong!',
ket_lapor:'ket_lapor tidak boleh kosong!',
sg1:'sg1 tidak boleh kosong!',
nilai1:'nilai1 tidak boleh kosong!',
sg2:'sg2 tidak boleh kosong!',
nilai2:'nilai2 tidak boleh kosong!',
total_hasil:'total_hasil tidak boleh kosong!',
nyeri:'nyeri tidak boleh kosong!',
provokes:'provokes tidak boleh kosong!',
ket_provokes:'ket_provokes tidak boleh kosong!',
quality:'quality tidak boleh kosong!',
ket_quality:'ket_quality tidak boleh kosong!',
lokasi:'lokasi tidak boleh kosong!',
menyebar:'menyebar tidak boleh kosong!',
skala_nyeri:'skala_nyeri tidak boleh kosong!',
durasi:'durasi tidak boleh kosong!',
nyeri_hilang:'nyeri_hilang tidak boleh kosong!',
ket_nyeri:'ket_nyeri tidak boleh kosong!',
pada_dokter:'pada_dokter tidak boleh kosong!',
ket_dokter:'ket_dokter tidak boleh kosong!',
rencana:'rencana tidak boleh kosong!',
nip:'nip tidak boleh kosong!'

        },
        submitHandler: function (form) {
 var no_rawat= $('#no_rawat').val();
var tanggal= $('#tanggal').val();
var informasi= $('#informasi').val();
var td= $('#td').val();
var nadi= $('#nadi').val();
var rr= $('#rr').val();
var suhu= $('#suhu').val();
var gcs= $('#gcs').val();
var bb= $('#bb').val();
var tb= $('#tb').val();
var bmi= $('#bmi').val();
var keluhan_utama= $('#keluhan_utama').val();
var rpd= $('#rpd').val();
var rpk= $('#rpk').val();
var rpo= $('#rpo').val();
var alergi= $('#alergi').val();
var alat_bantu= $('#alat_bantu').val();
var ket_bantu= $('#ket_bantu').val();
var prothesa= $('#prothesa').val();
var ket_pro= $('#ket_pro').val();
var adl= $('#adl').val();
var status_psiko= $('#status_psiko').val();
var ket_psiko= $('#ket_psiko').val();
var hub_keluarga= $('#hub_keluarga').val();
var tinggal_dengan= $('#tinggal_dengan').val();
var ket_tinggal= $('#ket_tinggal').val();
var ekonomi= $('#ekonomi').val();
var budaya= $('#budaya').val();
var ket_budaya= $('#ket_budaya').val();
var edukasi= $('#edukasi').val();
var ket_edukasi= $('#ket_edukasi').val();
var berjalan_a= $('#berjalan_a').val();
var berjalan_b= $('#berjalan_b').val();
var berjalan_c= $('#berjalan_c').val();
var hasil= $('#hasil').val();
var lapor= $('#lapor').val();
var ket_lapor= $('#ket_lapor').val();
var sg1= $('#sg1').val();
var nilai1= $('#nilai1').val();
var sg2= $('#sg2').val();
var nilai2= $('#nilai2').val();
var total_hasil= $('#total_hasil').val();
var nyeri= $('#nyeri').val();
var provokes= $('#provokes').val();
var ket_provokes= $('#ket_provokes').val();
var quality= $('#quality').val();
var ket_quality= $('#ket_quality').val();
var lokasi= $('#lokasi').val();
var menyebar= $('#menyebar').val();
var skala_nyeri= $('#skala_nyeri').val();
var durasi= $('#durasi').val();
var nyeri_hilang= $('#nyeri_hilang').val();
var ket_nyeri= $('#ket_nyeri').val();
var pada_dokter= $('#pada_dokter').val();
var ket_dokter= $('#ket_dokter').val();
var rencana= $('#rencana').val();
var nip= $('#nip').val();

 var typeact = $('#typeact').val();

 var formData = new FormData(form); // tambahan
 formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url([ADMIN,'penilaian_keperawatan_ralan','aksi'])?}",
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
    $('#search_text_mlite_penilaian_awal_keperawatan_ralan').keyup(function () {
        var_tbl_mlite_penilaian_awal_keperawatan_ralan.draw();
    });
    // ==============================================================
    // CLICK TANDA X DI INPUT SEARCH
    // ==============================================================
    $("#searchclear_mlite_penilaian_awal_keperawatan_ralan").click(function () {
        $("#search_text_mlite_penilaian_awal_keperawatan_ralan").val("");
        var_tbl_mlite_penilaian_awal_keperawatan_ralan.draw();
    });

    // ===========================================
    // Ketika tombol Edit di tekan
    // ===========================================

    $("#edit_data_mlite_penilaian_awal_keperawatan_ralan").click(function () {
        var rowData = var_tbl_mlite_penilaian_awal_keperawatan_ralan.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var no_rawat = rowData['no_rawat'];
var tanggal = rowData['tanggal'];
var informasi = rowData['informasi'];
var td = rowData['td'];
var nadi = rowData['nadi'];
var rr = rowData['rr'];
var suhu = rowData['suhu'];
var gcs = rowData['gcs'];
var bb = rowData['bb'];
var tb = rowData['tb'];
var bmi = rowData['bmi'];
var keluhan_utama = rowData['keluhan_utama'];
var rpd = rowData['rpd'];
var rpk = rowData['rpk'];
var rpo = rowData['rpo'];
var alergi = rowData['alergi'];
var alat_bantu = rowData['alat_bantu'];
var ket_bantu = rowData['ket_bantu'];
var prothesa = rowData['prothesa'];
var ket_pro = rowData['ket_pro'];
var adl = rowData['adl'];
var status_psiko = rowData['status_psiko'];
var ket_psiko = rowData['ket_psiko'];
var hub_keluarga = rowData['hub_keluarga'];
var tinggal_dengan = rowData['tinggal_dengan'];
var ket_tinggal = rowData['ket_tinggal'];
var ekonomi = rowData['ekonomi'];
var budaya = rowData['budaya'];
var ket_budaya = rowData['ket_budaya'];
var edukasi = rowData['edukasi'];
var ket_edukasi = rowData['ket_edukasi'];
var berjalan_a = rowData['berjalan_a'];
var berjalan_b = rowData['berjalan_b'];
var berjalan_c = rowData['berjalan_c'];
var hasil = rowData['hasil'];
var lapor = rowData['lapor'];
var ket_lapor = rowData['ket_lapor'];
var sg1 = rowData['sg1'];
var nilai1 = rowData['nilai1'];
var sg2 = rowData['sg2'];
var nilai2 = rowData['nilai2'];
var total_hasil = rowData['total_hasil'];
var nyeri = rowData['nyeri'];
var provokes = rowData['provokes'];
var ket_provokes = rowData['ket_provokes'];
var quality = rowData['quality'];
var ket_quality = rowData['ket_quality'];
var lokasi = rowData['lokasi'];
var menyebar = rowData['menyebar'];
var skala_nyeri = rowData['skala_nyeri'];
var durasi = rowData['durasi'];
var nyeri_hilang = rowData['nyeri_hilang'];
var ket_nyeri = rowData['ket_nyeri'];
var pada_dokter = rowData['pada_dokter'];
var ket_dokter = rowData['ket_dokter'];
var rencana = rowData['rencana'];
var nip = rowData['nip'];



            $("#typeact").val("edit");
  
            $('#no_rawat').val(no_rawat);
$('#tanggal').val(tanggal);
$('#informasi').val(informasi).change();
$('#td').val(td);
$('#nadi').val(nadi);
$('#rr').val(rr);
$('#suhu').val(suhu);
$('#gcs').val(gcs);
$('#bb').val(bb);
$('#tb').val(tb);
$('#bmi').val(bmi);
$('#keluhan_utama').val(keluhan_utama);
$('#rpd').val(rpd);
$('#rpk').val(rpk);
$('#rpo').val(rpo);
$('#alergi').val(alergi);
$('#alat_bantu').val(alat_bantu).change();
$('#ket_bantu').val(ket_bantu);
$('#prothesa').val(prothesa).change();
$('#ket_pro').val(ket_pro);
$('#adl').val(adl).change();
$('#status_psiko').val(status_psiko).change();
$('#ket_psiko').val(ket_psiko);
$('#hub_keluarga').val(hub_keluarga).change();
$('#tinggal_dengan').val(tinggal_dengan).change();
$('#ket_tinggal').val(ket_tinggal);
$('#ekonomi').val(ekonomi).change();
$('#budaya').val(budaya).change();
$('#ket_budaya').val(ket_budaya);
$('#edukasi').val(edukasi).change();
$('#ket_edukasi').val(ket_edukasi);
$('#berjalan_a').val(berjalan_a).change();
$('#berjalan_b').val(berjalan_b).change();
$('#berjalan_c').val(berjalan_c).change();
$('#hasil').val(hasil).change();
$('#lapor').val(lapor).change();
$('#ket_lapor').val(ket_lapor);
$('#sg1').val(sg1).change();
$('#nilai1').val(nilai1).change();
$('#sg2').val(sg2).change();
$('#nilai2').val(nilai2).change();
$('#total_hasil').val(total_hasil);
$('#nyeri').val(nyeri).change();
$('#provokes').val(provokes).change();
$('#ket_provokes').val(ket_provokes);
$('#quality').val(quality).change();
$('#ket_quality').val(ket_quality);
$('#lokasi').val(lokasi);
$('#menyebar').val(menyebar).change();
$('#skala_nyeri').val(skala_nyeri).change();
$('#durasi').val(durasi);
$('#nyeri_hilang').val(nyeri_hilang).change();
$('#ket_nyeri').val(ket_nyeri);
$('#pada_dokter').val(pada_dokter).change();
$('#ket_dokter').val(ket_dokter);
$('#rencana').val(rencana);
$('#nip').val(nip);

            //$("#no_rawat").prop('disabled', true); // GA BISA DIEDIT KALI DISABLE
            $('#modal-title').text("Edit Data Penilaian Keperawatan Ralan");
            $("#modal_mlite_penilaian_awal_keperawatan_ralan").modal();
        }
        else {
            alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_mlite_penilaian_awal_keperawatan_ralan").click(function () {
        var rowData = var_tbl_mlite_penilaian_awal_keperawatan_ralan.rows({ selected: true }).data()[0];


        if (rowData) {
var no_rawat = rowData['no_rawat'];
            var a = confirm("Anda yakin akan menghapus data dengan no_rawat=" + no_rawat);
            if (a) {

                $.ajax({
                    url: "{?=url([ADMIN,'penilaian_keperawatan_ralan','aksi'])?}",
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
        $('#search_text_mlite_penilaian_awal_keperawatan_ralan').val(searchParams.get('no_rawat'));
        var_tbl_mlite_penilaian_awal_keperawatan_ralan.draw();
        if(searchParams.get('modal') == 'true') {
            $("#modal_mlite_penilaian_awal_keperawatan_ralan").modal();
            $('#no_rawat').val(searchParams.get('no_rawat'));    
        }
    }

    jQuery("#tambah_data_mlite_penilaian_awal_keperawatan_ralan").click(function () {

        $('#no_rawat').val('');

        if(window.location.search.indexOf('no_rawat') !== -1) { 
            $('#no_rawat').val(searchParams.get('no_rawat'));
        }

$('#tanggal').val('');
$('#informasi').val('');
$('#td').val('');
$('#nadi').val('');
$('#rr').val('');
$('#suhu').val('');
$('#gcs').val('');
$('#bb').val('');
$('#tb').val('');
$('#bmi').val('');
$('#keluhan_utama').val('');
$('#rpd').val('');
$('#rpk').val('');
$('#rpo').val('');
$('#alergi').val('');
$('#alat_bantu').val('');
$('#ket_bantu').val('');
$('#prothesa').val('');
$('#ket_pro').val('');
$('#adl').val('');
$('#status_psiko').val('');
$('#ket_psiko').val('');
$('#hub_keluarga').val('');
$('#tinggal_dengan').val('');
$('#ket_tinggal').val('');
$('#ekonomi').val('');
$('#budaya').val('');
$('#ket_budaya').val('');
$('#edukasi').val('');
$('#ket_edukasi').val('');
$('#berjalan_a').val('');
$('#berjalan_b').val('');
$('#berjalan_c').val('');
$('#hasil').val('');
$('#lapor').val('');
$('#ket_lapor').val('');
$('#sg1').val('');
$('#nilai1').val('');
$('#sg2').val('');
$('#nilai2').val('');
$('#total_hasil').val('');
$('#nyeri').val('');
$('#provokes').val('');
$('#ket_provokes').val('');
$('#quality').val('');
$('#ket_quality').val('');
$('#lokasi').val('');
$('#menyebar').val('');
$('#skala_nyeri').val('');
$('#durasi').val('');
$('#nyeri_hilang').val('');
$('#ket_nyeri').val('');
$('#pada_dokter').val('');
$('#ket_dokter').val('');
$('#rencana').val('');
$('#nip').val('{?=$this->core->getUserInfo('username', null, true)?}');

        $("#typeact").val("add");
        $("#no_rawat").prop('disabled', false);
        
        $('#modal-title').text("Tambah Data Penilaian Keperawatan Ralan");
        $("#modal_mlite_penilaian_awal_keperawatan_ralan").modal();
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_mlite_penilaian_awal_keperawatan_ralan").click(function () {

        var search_field_mlite_penilaian_awal_keperawatan_ralan = $('#search_field_mlite_penilaian_awal_keperawatan_ralan').val();
        var search_text_mlite_penilaian_awal_keperawatan_ralan = $('#search_text_mlite_penilaian_awal_keperawatan_ralan').val();

        $.ajax({
            url: "{?=url([ADMIN,'penilaian_keperawatan_ralan','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_mlite_penilaian_awal_keperawatan_ralan: search_field_mlite_penilaian_awal_keperawatan_ralan, 
                search_text_mlite_penilaian_awal_keperawatan_ralan: search_text_mlite_penilaian_awal_keperawatan_ralan
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_mlite_penilaian_awal_keperawatan_ralan' class='table display dataTable' style='width:100%'><thead><th>No Rawat</th><th>Tanggal</th><th>Informasi</th><th>Td</th><th>Nadi</th><th>Rr</th><th>Suhu</th><th>Gcs</th><th>Bb</th><th>Tb</th><th>Bmi</th><th>Keluhan Utama</th><th>Rpd</th><th>Rpk</th><th>Rpo</th><th>Alergi</th><th>Alat Bantu</th><th>Ket Bantu</th><th>Prothesa</th><th>Ket Pro</th><th>Adl</th><th>Status Psiko</th><th>Ket Psiko</th><th>Hub Keluarga</th><th>Tinggal Dengan</th><th>Ket Tinggal</th><th>Ekonomi</th><th>Budaya</th><th>Ket Budaya</th><th>Edukasi</th><th>Ket Edukasi</th><th>Berjalan A</th><th>Berjalan B</th><th>Berjalan C</th><th>Hasil</th><th>Lapor</th><th>Ket Lapor</th><th>Sg1</th><th>Nilai1</th><th>Sg2</th><th>Nilai2</th><th>Total Hasil</th><th>Nyeri</th><th>Provokes</th><th>Ket Provokes</th><th>Quality</th><th>Ket Quality</th><th>Lokasi</th><th>Menyebar</th><th>Skala Nyeri</th><th>Durasi</th><th>Nyeri Hilang</th><th>Ket Nyeri</th><th>Pada Dokter</th><th>Ket Dokter</th><th>Rencana</th><th>Nip</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['no_rawat'] + '</td>';
eTable += '<td>' + res[i]['tanggal'] + '</td>';
eTable += '<td>' + res[i]['informasi'] + '</td>';
eTable += '<td>' + res[i]['td'] + '</td>';
eTable += '<td>' + res[i]['nadi'] + '</td>';
eTable += '<td>' + res[i]['rr'] + '</td>';
eTable += '<td>' + res[i]['suhu'] + '</td>';
eTable += '<td>' + res[i]['gcs'] + '</td>';
eTable += '<td>' + res[i]['bb'] + '</td>';
eTable += '<td>' + res[i]['tb'] + '</td>';
eTable += '<td>' + res[i]['bmi'] + '</td>';
eTable += '<td>' + res[i]['keluhan_utama'] + '</td>';
eTable += '<td>' + res[i]['rpd'] + '</td>';
eTable += '<td>' + res[i]['rpk'] + '</td>';
eTable += '<td>' + res[i]['rpo'] + '</td>';
eTable += '<td>' + res[i]['alergi'] + '</td>';
eTable += '<td>' + res[i]['alat_bantu'] + '</td>';
eTable += '<td>' + res[i]['ket_bantu'] + '</td>';
eTable += '<td>' + res[i]['prothesa'] + '</td>';
eTable += '<td>' + res[i]['ket_pro'] + '</td>';
eTable += '<td>' + res[i]['adl'] + '</td>';
eTable += '<td>' + res[i]['status_psiko'] + '</td>';
eTable += '<td>' + res[i]['ket_psiko'] + '</td>';
eTable += '<td>' + res[i]['hub_keluarga'] + '</td>';
eTable += '<td>' + res[i]['tinggal_dengan'] + '</td>';
eTable += '<td>' + res[i]['ket_tinggal'] + '</td>';
eTable += '<td>' + res[i]['ekonomi'] + '</td>';
eTable += '<td>' + res[i]['budaya'] + '</td>';
eTable += '<td>' + res[i]['ket_budaya'] + '</td>';
eTable += '<td>' + res[i]['edukasi'] + '</td>';
eTable += '<td>' + res[i]['ket_edukasi'] + '</td>';
eTable += '<td>' + res[i]['berjalan_a'] + '</td>';
eTable += '<td>' + res[i]['berjalan_b'] + '</td>';
eTable += '<td>' + res[i]['berjalan_c'] + '</td>';
eTable += '<td>' + res[i]['hasil'] + '</td>';
eTable += '<td>' + res[i]['lapor'] + '</td>';
eTable += '<td>' + res[i]['ket_lapor'] + '</td>';
eTable += '<td>' + res[i]['sg1'] + '</td>';
eTable += '<td>' + res[i]['nilai1'] + '</td>';
eTable += '<td>' + res[i]['sg2'] + '</td>';
eTable += '<td>' + res[i]['nilai2'] + '</td>';
eTable += '<td>' + res[i]['total_hasil'] + '</td>';
eTable += '<td>' + res[i]['nyeri'] + '</td>';
eTable += '<td>' + res[i]['provokes'] + '</td>';
eTable += '<td>' + res[i]['ket_provokes'] + '</td>';
eTable += '<td>' + res[i]['quality'] + '</td>';
eTable += '<td>' + res[i]['ket_quality'] + '</td>';
eTable += '<td>' + res[i]['lokasi'] + '</td>';
eTable += '<td>' + res[i]['menyebar'] + '</td>';
eTable += '<td>' + res[i]['skala_nyeri'] + '</td>';
eTable += '<td>' + res[i]['durasi'] + '</td>';
eTable += '<td>' + res[i]['nyeri_hilang'] + '</td>';
eTable += '<td>' + res[i]['ket_nyeri'] + '</td>';
eTable += '<td>' + res[i]['pada_dokter'] + '</td>';
eTable += '<td>' + res[i]['ket_dokter'] + '</td>';
eTable += '<td>' + res[i]['rencana'] + '</td>';
eTable += '<td>' + res[i]['nip'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_mlite_penilaian_awal_keperawatan_ralan').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_mlite_penilaian_awal_keperawatan_ralan").modal();
    });

    // ==============================================================
    // TOMBOL DETAIL mlite_penilaian_awal_keperawatan_ralan DI CLICK
    // ==============================================================
    jQuery("#lihat_detail_mlite_penilaian_awal_keperawatan_ralan").click(function (event) {

        var rowData = var_tbl_mlite_penilaian_awal_keperawatan_ralan.rows({ selected: true }).data()[0];

        if (rowData) {
            var no_rawat = rowData['no_rawat'];
            var baseURL = mlite.url + '/' + mlite.admin;
            event.preventDefault();
            var loadURL =  baseURL + '/penilaian_keperawatan_ralan/detail/' + no_rawat.replace(/\//g,'') + '?t=' + mlite.token;
        
            var modal = $('#modal_detail_mlite_penilaian_awal_keperawatan_ralan');
            var modalContent = $('#modal_detail_mlite_penilaian_awal_keperawatan_ralan .modal-content');
        
            modal.off('show.bs.modal');
            modal.on('show.bs.modal', function () {
                modalContent.load(loadURL);
            }).modal();
            console.log(loadURL);
            return false;
        
        }
        else {
            alert("Pilih satu baris untuk detail");
        }
    });
        
    jQuery("#lihat_detail_mlite_penilaian_awal_keperawatan_ralan2").click(function (event) {

        var rowData = var_tbl_mlite_penilaian_awal_keperawatan_ralan.rows({ selected: true }).data()[0];

        if (rowData) {
            var no_rawat = rowData['no_rawat'];
            var baseURL = mlite.url + '/' + mlite.admin;
            event.preventDefault();        
            {if: $this->core->ActiveModule('jasper')}
                var loadURL =  baseURL + '/jasper/awalkeperawatanralan/' + no_rawat.replace(/\//g,'') + '?t=' + mlite.token;
                $("#modal_detail_mlite_penilaian_awal_keperawatan_ralan").modal('show').html('<div style="text-align:center;margin:20px auto;width:90%;height:95%;"><iframe src="' + loadURL + '" frameborder="no" width="100%" height="100%"></iframe></div>');
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
        doc.text("Tabel Data Penilaian Awal Keperawatan Ralan", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_mlite_penilaian_awal_keperawatan_ralan',
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
        // doc.save('table_data_mlite_penilaian_awal_keperawatan_ralan.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_mlite_penilaian_awal_keperawatan_ralan");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data mlite_penilaian_awal_keperawatan_ralan");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })
});