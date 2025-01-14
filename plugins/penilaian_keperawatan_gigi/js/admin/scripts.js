jQuery().ready(function () {
    var var_tbl_mlite_penilaian_awal_keperawatan_gigi = $('#tbl_mlite_penilaian_awal_keperawatan_gigi').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url([ADMIN,'penilaian_keperawatan_gigi','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_mlite_penilaian_awal_keperawatan_gigi = $('#search_field_mlite_penilaian_awal_keperawatan_gigi').val();
                var search_text_mlite_penilaian_awal_keperawatan_gigi = $('#search_text_mlite_penilaian_awal_keperawatan_gigi').val();
                
                data.search_field_mlite_penilaian_awal_keperawatan_gigi = search_field_mlite_penilaian_awal_keperawatan_gigi;
                data.search_text_mlite_penilaian_awal_keperawatan_gigi = search_text_mlite_penilaian_awal_keperawatan_gigi;
                
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
{ 'data': 'bb' },
{ 'data': 'tb' },
{ 'data': 'bmi' },
{ 'data': 'keluhan_utama' },
{ 'data': 'riwayat_penyakit' },
{ 'data': 'ket_riwayat_penyakit' },
{ 'data': 'alergi' },
{ 'data': 'riwayat_perawatan_gigi' },
{ 'data': 'ket_riwayat_perawatan_gigi' },
{ 'data': 'kebiasaan_sikat_gigi' },
{ 'data': 'kebiasaan_lain' },
{ 'data': 'ket_kebiasaan_lain' },
{ 'data': 'obat_yang_diminum_saatini' },
{ 'data': 'alat_bantu' },
{ 'data': 'ket_alat_bantu' },
{ 'data': 'prothesa' },
{ 'data': 'ket_pro' },
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
{ 'data': 'nyeri' },
{ 'data': 'lokasi' },
{ 'data': 'skala_nyeri' },
{ 'data': 'durasi' },
{ 'data': 'frekuensi' },
{ 'data': 'nyeri_hilang' },
{ 'data': 'ket_nyeri' },
{ 'data': 'pada_dokter' },
{ 'data': 'ket_dokter' },
{ 'data': 'kebersihan_mulut' },
{ 'data': 'mukosa_mulut' },
{ 'data': 'karies' },
{ 'data': 'karang_gigi' },
{ 'data': 'gingiva' },
{ 'data': 'palatum' },
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

    $("form[name='form_mlite_penilaian_awal_keperawatan_gigi']").validate({
        rules: {
no_rawat: 'required',
tanggal: 'required',
informasi: 'required',
td: 'required',
nadi: 'required',
rr: 'required',
suhu: 'required',
bb: 'required',
tb: 'required',
bmi: 'required',
keluhan_utama: 'required',
riwayat_penyakit: 'required',
ket_riwayat_penyakit: 'required',
alergi: 'required',
riwayat_perawatan_gigi: 'required',
ket_riwayat_perawatan_gigi: 'required',
kebiasaan_sikat_gigi: 'required',
kebiasaan_lain: 'required',
ket_kebiasaan_lain: 'required',
obat_yang_diminum_saatini: 'required',
alat_bantu: 'required',
ket_alat_bantu: 'required',
prothesa: 'required',
ket_pro: 'required',
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
nyeri: 'required',
lokasi: 'required',
skala_nyeri: 'required',
durasi: 'required',
frekuensi: 'required',
nyeri_hilang: 'required',
ket_nyeri: 'required',
pada_dokter: 'required',
ket_dokter: 'required',
kebersihan_mulut: 'required',
mukosa_mulut: 'required',
karies: 'required',
karang_gigi: 'required',
gingiva: 'required',
palatum: 'required',
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
bb:'bb tidak boleh kosong!',
tb:'tb tidak boleh kosong!',
bmi:'bmi tidak boleh kosong!',
keluhan_utama:'keluhan_utama tidak boleh kosong!',
riwayat_penyakit:'riwayat_penyakit tidak boleh kosong!',
ket_riwayat_penyakit:'ket_riwayat_penyakit tidak boleh kosong!',
alergi:'alergi tidak boleh kosong!',
riwayat_perawatan_gigi:'riwayat_perawatan_gigi tidak boleh kosong!',
ket_riwayat_perawatan_gigi:'ket_riwayat_perawatan_gigi tidak boleh kosong!',
kebiasaan_sikat_gigi:'kebiasaan_sikat_gigi tidak boleh kosong!',
kebiasaan_lain:'kebiasaan_lain tidak boleh kosong!',
ket_kebiasaan_lain:'ket_kebiasaan_lain tidak boleh kosong!',
obat_yang_diminum_saatini:'obat_yang_diminum_saatini tidak boleh kosong!',
alat_bantu:'alat_bantu tidak boleh kosong!',
ket_alat_bantu:'ket_alat_bantu tidak boleh kosong!',
prothesa:'prothesa tidak boleh kosong!',
ket_pro:'ket_pro tidak boleh kosong!',
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
nyeri:'nyeri tidak boleh kosong!',
lokasi:'lokasi tidak boleh kosong!',
skala_nyeri:'skala_nyeri tidak boleh kosong!',
durasi:'durasi tidak boleh kosong!',
frekuensi:'frekuensi tidak boleh kosong!',
nyeri_hilang:'nyeri_hilang tidak boleh kosong!',
ket_nyeri:'ket_nyeri tidak boleh kosong!',
pada_dokter:'pada_dokter tidak boleh kosong!',
ket_dokter:'ket_dokter tidak boleh kosong!',
kebersihan_mulut:'kebersihan_mulut tidak boleh kosong!',
mukosa_mulut:'mukosa_mulut tidak boleh kosong!',
karies:'karies tidak boleh kosong!',
karang_gigi:'karang_gigi tidak boleh kosong!',
gingiva:'gingiva tidak boleh kosong!',
palatum:'palatum tidak boleh kosong!',
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
var bb= $('#bb').val();
var tb= $('#tb').val();
var bmi= $('#bmi').val();
var keluhan_utama= $('#keluhan_utama').val();
var riwayat_penyakit= $('#riwayat_penyakit').val();
var ket_riwayat_penyakit= $('#ket_riwayat_penyakit').val();
var alergi= $('#alergi').val();
var riwayat_perawatan_gigi= $('#riwayat_perawatan_gigi').val();
var ket_riwayat_perawatan_gigi= $('#ket_riwayat_perawatan_gigi').val();
var kebiasaan_sikat_gigi= $('#kebiasaan_sikat_gigi').val();
var kebiasaan_lain= $('#kebiasaan_lain').val();
var ket_kebiasaan_lain= $('#ket_kebiasaan_lain').val();
var obat_yang_diminum_saatini= $('#obat_yang_diminum_saatini').val();
var alat_bantu= $('#alat_bantu').val();
var ket_alat_bantu= $('#ket_alat_bantu').val();
var prothesa= $('#prothesa').val();
var ket_pro= $('#ket_pro').val();
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
var nyeri= $('#nyeri').val();
var lokasi= $('#lokasi').val();
var skala_nyeri= $('#skala_nyeri').val();
var durasi= $('#durasi').val();
var frekuensi= $('#frekuensi').val();
var nyeri_hilang= $('#nyeri_hilang').val();
var ket_nyeri= $('#ket_nyeri').val();
var pada_dokter= $('#pada_dokter').val();
var ket_dokter= $('#ket_dokter').val();
var kebersihan_mulut= $('#kebersihan_mulut').val();
var mukosa_mulut= $('#mukosa_mulut').val();
var karies= $('#karies').val();
var karang_gigi= $('#karang_gigi').val();
var gingiva= $('#gingiva').val();
var palatum= $('#palatum').val();
var rencana= $('#rencana').val();
var nip= $('#nip').val();

 var typeact = $('#typeact').val();

 var formData = new FormData(form); // tambahan
 formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url([ADMIN,'penilaian_keperawatan_gigi','aksi'])?}",
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
    $('#search_text_mlite_penilaian_awal_keperawatan_gigi').keyup(function () {
        var_tbl_mlite_penilaian_awal_keperawatan_gigi.draw();
    });
    // ==============================================================
    // CLICK TANDA X DI INPUT SEARCH
    // ==============================================================
    $("#searchclear_mlite_penilaian_awal_keperawatan_gigi").click(function () {
        $("#search_text_mlite_penilaian_awal_keperawatan_gigi").val("");
        var_tbl_mlite_penilaian_awal_keperawatan_gigi.draw();
    });

    // ===========================================
    // Ketika tombol Edit di tekan
    // ===========================================

    $("#edit_data_mlite_penilaian_awal_keperawatan_gigi").click(function () {
        var rowData = var_tbl_mlite_penilaian_awal_keperawatan_gigi.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var no_rawat = rowData['no_rawat'];
var tanggal = rowData['tanggal'];
var informasi = rowData['informasi'];
var td = rowData['td'];
var nadi = rowData['nadi'];
var rr = rowData['rr'];
var suhu = rowData['suhu'];
var bb = rowData['bb'];
var tb = rowData['tb'];
var bmi = rowData['bmi'];
var keluhan_utama = rowData['keluhan_utama'];
var riwayat_penyakit = rowData['riwayat_penyakit'];
var ket_riwayat_penyakit = rowData['ket_riwayat_penyakit'];
var alergi = rowData['alergi'];
var riwayat_perawatan_gigi = rowData['riwayat_perawatan_gigi'];
var ket_riwayat_perawatan_gigi = rowData['ket_riwayat_perawatan_gigi'];
var kebiasaan_sikat_gigi = rowData['kebiasaan_sikat_gigi'];
var kebiasaan_lain = rowData['kebiasaan_lain'];
var ket_kebiasaan_lain = rowData['ket_kebiasaan_lain'];
var obat_yang_diminum_saatini = rowData['obat_yang_diminum_saatini'];
var alat_bantu = rowData['alat_bantu'];
var ket_alat_bantu = rowData['ket_alat_bantu'];
var prothesa = rowData['prothesa'];
var ket_pro = rowData['ket_pro'];
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
var nyeri = rowData['nyeri'];
var lokasi = rowData['lokasi'];
var skala_nyeri = rowData['skala_nyeri'];
var durasi = rowData['durasi'];
var frekuensi = rowData['frekuensi'];
var nyeri_hilang = rowData['nyeri_hilang'];
var ket_nyeri = rowData['ket_nyeri'];
var pada_dokter = rowData['pada_dokter'];
var ket_dokter = rowData['ket_dokter'];
var kebersihan_mulut = rowData['kebersihan_mulut'];
var mukosa_mulut = rowData['mukosa_mulut'];
var karies = rowData['karies'];
var karang_gigi = rowData['karang_gigi'];
var gingiva = rowData['gingiva'];
var palatum = rowData['palatum'];
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
$('#bb').val(bb);
$('#tb').val(tb);
$('#bmi').val(bmi);
$('#keluhan_utama').val(keluhan_utama);
$('#riwayat_penyakit').val(riwayat_penyakit).change();
$('#ket_riwayat_penyakit').val(ket_riwayat_penyakit);
$('#alergi').val(alergi);
$('#riwayat_perawatan_gigi').val(riwayat_perawatan_gigi).change();
$('#ket_riwayat_perawatan_gigi').val(ket_riwayat_perawatan_gigi);
$('#kebiasaan_sikat_gigi').val(kebiasaan_sikat_gigi).change();
$('#kebiasaan_lain').val(kebiasaan_lain).change();
$('#ket_kebiasaan_lain').val(ket_kebiasaan_lain);
$('#obat_yang_diminum_saatini').val(obat_yang_diminum_saatini);
$('#alat_bantu').val(alat_bantu).change();
$('#ket_alat_bantu').val(ket_alat_bantu);
$('#prothesa').val(prothesa).change();
$('#ket_pro').val(ket_pro);
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
$('#nyeri').val(nyeri).change();
$('#lokasi').val(lokasi);
$('#skala_nyeri').val(skala_nyeri).change();
$('#durasi').val(durasi);
$('#frekuensi').val(frekuensi);
$('#nyeri_hilang').val(nyeri_hilang).change();
$('#ket_nyeri').val(ket_nyeri);
$('#pada_dokter').val(pada_dokter).change();
$('#ket_dokter').val(ket_dokter);
$('#kebersihan_mulut').val(kebersihan_mulut).change();
$('#mukosa_mulut').val(mukosa_mulut).change();
$('#karies').val(karies).change();
$('#karang_gigi').val(karang_gigi).change();
$('#gingiva').val(gingiva).change();
$('#palatum').val(palatum).change();
$('#rencana').val(rencana);
$('#nip').val(nip);

            //$("#no_rawat").prop('disabled', true); // GA BISA DIEDIT KALI DISABLE
            $('#modal-title').text("Edit Data Penilaian Keperawatan Gigi");
            $("#modal_mlite_penilaian_awal_keperawatan_gigi").modal();
        }
        else {
            alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_mlite_penilaian_awal_keperawatan_gigi").click(function () {
        var rowData = var_tbl_mlite_penilaian_awal_keperawatan_gigi.rows({ selected: true }).data()[0];


        if (rowData) {
var no_rawat = rowData['no_rawat'];
            var a = confirm("Anda yakin akan menghapus data dengan no_rawat=" + no_rawat);
            if (a) {

                $.ajax({
                    url: "{?=url([ADMIN,'penilaian_keperawatan_gigi','aksi'])?}",
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
        $('#search_text_mlite_penilaian_awal_keperawatan_gigi').val(searchParams.get('no_rawat'));
        var_tbl_mlite_penilaian_awal_keperawatan_gigi.draw();
        if(searchParams.get('modal') == 'true') {
            $("#modal_mlite_penilaian_awal_keperawatan_gigi").modal();
            $('#no_rawat').val(searchParams.get('no_rawat'));    
        }
    }

    jQuery("#tambah_data_mlite_penilaian_awal_keperawatan_gigi").click(function () {

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
$('#bb').val('');
$('#tb').val('');
$('#bmi').val('');
$('#keluhan_utama').val('');
$('#riwayat_penyakit').val('');
$('#ket_riwayat_penyakit').val('');
$('#alergi').val('');
$('#riwayat_perawatan_gigi').val('');
$('#ket_riwayat_perawatan_gigi').val('');
$('#kebiasaan_sikat_gigi').val('');
$('#kebiasaan_lain').val('');
$('#ket_kebiasaan_lain').val('');
$('#obat_yang_diminum_saatini').val('');
$('#alat_bantu').val('');
$('#ket_alat_bantu').val('');
$('#prothesa').val('');
$('#ket_pro').val('');
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
$('#nyeri').val('');
$('#lokasi').val('');
$('#skala_nyeri').val('');
$('#durasi').val('');
$('#frekuensi').val('');
$('#nyeri_hilang').val('');
$('#ket_nyeri').val('');
$('#pada_dokter').val('');
$('#ket_dokter').val('');
$('#kebersihan_mulut').val('');
$('#mukosa_mulut').val('');
$('#karies').val('');
$('#karang_gigi').val('');
$('#gingiva').val('');
$('#palatum').val('');
$('#rencana').val('');
$('#nip').val('{?=$this->core->getUserInfo('username', null, true)?}');


        $("#typeact").val("add");
        $("#no_rawat").prop('disabled', false);
        
        $('#modal-title').text("Tambah Data Penilaian Keperawatan Gigi");
        $("#modal_mlite_penilaian_awal_keperawatan_gigi").modal();
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_mlite_penilaian_awal_keperawatan_gigi").click(function () {

        var search_field_mlite_penilaian_awal_keperawatan_gigi = $('#search_field_mlite_penilaian_awal_keperawatan_gigi').val();
        var search_text_mlite_penilaian_awal_keperawatan_gigi = $('#search_text_mlite_penilaian_awal_keperawatan_gigi').val();

        $.ajax({
            url: "{?=url([ADMIN,'penilaian_keperawatan_gigi','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_mlite_penilaian_awal_keperawatan_gigi: search_field_mlite_penilaian_awal_keperawatan_gigi, 
                search_text_mlite_penilaian_awal_keperawatan_gigi: search_text_mlite_penilaian_awal_keperawatan_gigi
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_mlite_penilaian_awal_keperawatan_gigi' class='table display dataTable' style='width:100%'><thead><th>No Rawat</th><th>Tanggal</th><th>Informasi</th><th>Td</th><th>Nadi</th><th>Rr</th><th>Suhu</th><th>Bb</th><th>Tb</th><th>Bmi</th><th>Keluhan Utama</th><th>Riwayat Penyakit</th><th>Ket Riwayat Penyakit</th><th>Alergi</th><th>Riwayat Perawatan Gigi</th><th>Ket Riwayat Perawatan Gigi</th><th>Kebiasaan Sikat Gigi</th><th>Kebiasaan Lain</th><th>Ket Kebiasaan Lain</th><th>Obat Yang Diminum Saatini</th><th>Alat Bantu</th><th>Ket Alat Bantu</th><th>Prothesa</th><th>Ket Pro</th><th>Status Psiko</th><th>Ket Psiko</th><th>Hub Keluarga</th><th>Tinggal Dengan</th><th>Ket Tinggal</th><th>Ekonomi</th><th>Budaya</th><th>Ket Budaya</th><th>Edukasi</th><th>Ket Edukasi</th><th>Berjalan A</th><th>Berjalan B</th><th>Berjalan C</th><th>Hasil</th><th>Lapor</th><th>Ket Lapor</th><th>Nyeri</th><th>Lokasi</th><th>Skala Nyeri</th><th>Durasi</th><th>Frekuensi</th><th>Nyeri Hilang</th><th>Ket Nyeri</th><th>Pada Dokter</th><th>Ket Dokter</th><th>Kebersihan Mulut</th><th>Mukosa Mulut</th><th>Karies</th><th>Karang Gigi</th><th>Gingiva</th><th>Palatum</th><th>Rencana</th><th>Nip</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['no_rawat'] + '</td>';
eTable += '<td>' + res[i]['tanggal'] + '</td>';
eTable += '<td>' + res[i]['informasi'] + '</td>';
eTable += '<td>' + res[i]['td'] + '</td>';
eTable += '<td>' + res[i]['nadi'] + '</td>';
eTable += '<td>' + res[i]['rr'] + '</td>';
eTable += '<td>' + res[i]['suhu'] + '</td>';
eTable += '<td>' + res[i]['bb'] + '</td>';
eTable += '<td>' + res[i]['tb'] + '</td>';
eTable += '<td>' + res[i]['bmi'] + '</td>';
eTable += '<td>' + res[i]['keluhan_utama'] + '</td>';
eTable += '<td>' + res[i]['riwayat_penyakit'] + '</td>';
eTable += '<td>' + res[i]['ket_riwayat_penyakit'] + '</td>';
eTable += '<td>' + res[i]['alergi'] + '</td>';
eTable += '<td>' + res[i]['riwayat_perawatan_gigi'] + '</td>';
eTable += '<td>' + res[i]['ket_riwayat_perawatan_gigi'] + '</td>';
eTable += '<td>' + res[i]['kebiasaan_sikat_gigi'] + '</td>';
eTable += '<td>' + res[i]['kebiasaan_lain'] + '</td>';
eTable += '<td>' + res[i]['ket_kebiasaan_lain'] + '</td>';
eTable += '<td>' + res[i]['obat_yang_diminum_saatini'] + '</td>';
eTable += '<td>' + res[i]['alat_bantu'] + '</td>';
eTable += '<td>' + res[i]['ket_alat_bantu'] + '</td>';
eTable += '<td>' + res[i]['prothesa'] + '</td>';
eTable += '<td>' + res[i]['ket_pro'] + '</td>';
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
eTable += '<td>' + res[i]['nyeri'] + '</td>';
eTable += '<td>' + res[i]['lokasi'] + '</td>';
eTable += '<td>' + res[i]['skala_nyeri'] + '</td>';
eTable += '<td>' + res[i]['durasi'] + '</td>';
eTable += '<td>' + res[i]['frekuensi'] + '</td>';
eTable += '<td>' + res[i]['nyeri_hilang'] + '</td>';
eTable += '<td>' + res[i]['ket_nyeri'] + '</td>';
eTable += '<td>' + res[i]['pada_dokter'] + '</td>';
eTable += '<td>' + res[i]['ket_dokter'] + '</td>';
eTable += '<td>' + res[i]['kebersihan_mulut'] + '</td>';
eTable += '<td>' + res[i]['mukosa_mulut'] + '</td>';
eTable += '<td>' + res[i]['karies'] + '</td>';
eTable += '<td>' + res[i]['karang_gigi'] + '</td>';
eTable += '<td>' + res[i]['gingiva'] + '</td>';
eTable += '<td>' + res[i]['palatum'] + '</td>';
eTable += '<td>' + res[i]['rencana'] + '</td>';
eTable += '<td>' + res[i]['nip'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_mlite_penilaian_awal_keperawatan_gigi').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_mlite_penilaian_awal_keperawatan_gigi").modal();
    });

    // ==============================================================
    // TOMBOL DETAIL mlite_penilaian_awal_keperawatan_gigi DI CLICK
    // ==============================================================
    jQuery("#lihat_detail_mlite_penilaian_awal_keperawatan_gigi").click(function (event) {

        var rowData = var_tbl_mlite_penilaian_awal_keperawatan_gigi.rows({ selected: true }).data()[0];

        if (rowData) {
            var no_rawat = rowData['no_rawat'];
            var baseURL = mlite.url + '/' + mlite.admin;
            event.preventDefault();
            var loadURL =  baseURL + '/penilaian_keperawatan_gigi/detail/' + no_rawat.replace(/\//g,'') + '?t=' + mlite.token;
        
            var modal = $('#modal_detail_mlite_penilaian_awal_keperawatan_gigi');
            var modalContent = $('#modal_detail_mlite_penilaian_awal_keperawatan_gigi .modal-content');
        
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
        doc.text("Tabel Data Mlite Penilaian Awal Keperawatan Gigi", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_mlite_penilaian_awal_keperawatan_gigi',
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
        // doc.save('table_data_mlite_penilaian_awal_keperawatan_gigi.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_mlite_penilaian_awal_keperawatan_gigi");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data mlite_penilaian_awal_keperawatan_gigi");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })
});