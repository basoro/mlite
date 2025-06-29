jQuery().ready(function () {
    var var_tbl_mlite_penilaian_awal_keperawatan_ranap = $('#tbl_mlite_penilaian_awal_keperawatan_ranap').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'dom': 'Bfrtip',
        'searching': false,
        'select': true,
        'colReorder': true,
        "bInfo" : false,
        "ajax": {
            "url": "{?=url([ADMIN,'penilaian_keperawatan_ranap','data'])?}",
            "dataType": "json",
            "type": "POST",
            "data": function (data) {

                // Read values
                var search_field_mlite_penilaian_awal_keperawatan_ranap = $('#search_field_mlite_penilaian_awal_keperawatan_ranap').val();
                var search_text_mlite_penilaian_awal_keperawatan_ranap = $('#search_text_mlite_penilaian_awal_keperawatan_ranap').val();
                
                data.search_field_mlite_penilaian_awal_keperawatan_ranap = search_field_mlite_penilaian_awal_keperawatan_ranap;
                data.search_text_mlite_penilaian_awal_keperawatan_ranap = search_text_mlite_penilaian_awal_keperawatan_ranap;
                
            }
        },
        "columns": [
{ 'data': 'no_rawat' },
{ 'data': 'tanggal' },
{ 'data': 'informasi' },
{ 'data': 'ket_informasi' },
{ 'data': 'tiba_diruang_rawat' },
{ 'data': 'kasus_trauma' },
{ 'data': 'cara_masuk' },
{ 'data': 'rps' },
{ 'data': 'rpd' },
{ 'data': 'rpk' },
{ 'data': 'rpo' },
{ 'data': 'riwayat_pembedahan' },
{ 'data': 'riwayat_dirawat_dirs' },
{ 'data': 'alat_bantu_dipakai' },
{ 'data': 'riwayat_kehamilan' },
{ 'data': 'riwayat_kehamilan_perkiraan' },
{ 'data': 'riwayat_tranfusi' },
{ 'data': 'riwayat_alergi' },
{ 'data': 'riwayat_merokok' },
{ 'data': 'riwayat_merokok_jumlah' },
{ 'data': 'riwayat_alkohol' },
{ 'data': 'riwayat_alkohol_jumlah' },
{ 'data': 'riwayat_narkoba' },
{ 'data': 'riwayat_olahraga' },
{ 'data': 'pemeriksaan_mental' },
{ 'data': 'pemeriksaan_keadaan_umum' },
{ 'data': 'pemeriksaan_gcs' },
{ 'data': 'pemeriksaan_td' },
{ 'data': 'pemeriksaan_nadi' },
{ 'data': 'pemeriksaan_rr' },
{ 'data': 'pemeriksaan_suhu' },
{ 'data': 'pemeriksaan_spo2' },
{ 'data': 'pemeriksaan_bb' },
{ 'data': 'pemeriksaan_tb' },
{ 'data': 'pemeriksaan_susunan_kepala' },
{ 'data': 'pemeriksaan_susunan_wajah' },
{ 'data': 'pemeriksaan_susunan_leher' },
{ 'data': 'pemeriksaan_susunan_kejang' },
{ 'data': 'pemeriksaan_susunan_sensorik' },
{ 'data': 'pemeriksaan_kardiovaskuler_denyut_nadi' },
{ 'data': 'pemeriksaan_kardiovaskuler_sirkulasi' },
{ 'data': 'pemeriksaan_kardiovaskuler_pulsasi' },
{ 'data': 'pemeriksaan_respirasi_pola_nafas' },
{ 'data': 'pemeriksaan_respirasi_retraksi' },
{ 'data': 'pemeriksaan_respirasi_suara_nafas' },
{ 'data': 'pemeriksaan_respirasi_volume_pernafasan' },
{ 'data': 'pemeriksaan_respirasi_jenis_pernafasan' },
{ 'data': 'pemeriksaan_respirasi_irama_nafas' },
{ 'data': 'pemeriksaan_respirasi_batuk' },
{ 'data': 'pemeriksaan_gastrointestinal_mulut' },
{ 'data': 'pemeriksaan_gastrointestinal_gigi' },
{ 'data': 'pemeriksaan_gastrointestinal_lidah' },
{ 'data': 'pemeriksaan_gastrointestinal_tenggorokan' },
{ 'data': 'pemeriksaan_gastrointestinal_abdomen' },
{ 'data': 'pemeriksaan_gastrointestinal_peistatik_usus' },
{ 'data': 'pemeriksaan_gastrointestinal_anus' },
{ 'data': 'pemeriksaan_neurologi_pengelihatan' },
{ 'data': 'pemeriksaan_neurologi_alat_bantu_penglihatan' },
{ 'data': 'pemeriksaan_neurologi_pendengaran' },
{ 'data': 'pemeriksaan_neurologi_bicara' },
{ 'data': 'pemeriksaan_neurologi_sensorik' },
{ 'data': 'pemeriksaan_neurologi_motorik' },
{ 'data': 'pemeriksaan_neurologi_kekuatan_otot' },
{ 'data': 'pemeriksaan_integument_warnakulit' },
{ 'data': 'pemeriksaan_integument_turgor' },
{ 'data': 'pemeriksaan_integument_kulit' },
{ 'data': 'pemeriksaan_integument_dekubitas' },
{ 'data': 'pemeriksaan_muskuloskletal_pergerakan_sendi' },
{ 'data': 'pemeriksaan_muskuloskletal_kekauatan_otot' },
{ 'data': 'pemeriksaan_muskuloskletal_nyeri_sendi' },
{ 'data': 'pemeriksaan_muskuloskletal_oedema' },
{ 'data': 'pemeriksaan_muskuloskletal_fraktur' },
{ 'data': 'pemeriksaan_eliminasi_bab_frekuensi_jumlah' },
{ 'data': 'pemeriksaan_eliminasi_bab_frekuensi_durasi' },
{ 'data': 'pemeriksaan_eliminasi_bab_konsistensi' },
{ 'data': 'pemeriksaan_eliminasi_bab_warna' },
{ 'data': 'pemeriksaan_eliminasi_bak_frekuensi_jumlah' },
{ 'data': 'pemeriksaan_eliminasi_bak_frekuensi_durasi' },
{ 'data': 'pemeriksaan_eliminasi_bak_warna' },
{ 'data': 'pemeriksaan_eliminasi_bak_lainlain' },
{ 'data': 'pola_aktifitas_makanminum' },
{ 'data': 'pola_aktifitas_mandi' },
{ 'data': 'pola_aktifitas_eliminasi' },
{ 'data': 'pola_aktifitas_berpakaian' },
{ 'data': 'pola_aktifitas_berpindah' },
{ 'data': 'pola_nutrisi_frekuesi_makan' },
{ 'data': 'pola_nutrisi_jenis_makanan' },
{ 'data': 'pola_nutrisi_porsi_makan' },
{ 'data': 'pola_tidur_lama_tidur' },
{ 'data': 'pola_tidur_gangguan' },
{ 'data': 'pengkajian_fungsi_kemampuan_sehari' },
{ 'data': 'pengkajian_fungsi_aktifitas' },
{ 'data': 'pengkajian_fungsi_berjalan' },
{ 'data': 'pengkajian_fungsi_ambulasi' },
{ 'data': 'pengkajian_fungsi_ekstrimitas_atas' },
{ 'data': 'pengkajian_fungsi_ekstrimitas_bawah' },
{ 'data': 'pengkajian_fungsi_menggenggam' },
{ 'data': 'pengkajian_fungsi_koordinasi' },
{ 'data': 'pengkajian_fungsi_kesimpulan' },
{ 'data': 'riwayat_psiko_kondisi_psiko' },
{ 'data': 'riwayat_psiko_gangguan_jiwa' },
{ 'data': 'riwayat_psiko_perilaku' },
{ 'data': 'riwayat_psiko_hubungan_keluarga' },
{ 'data': 'riwayat_psiko_tinggal' },
{ 'data': 'riwayat_psiko_nilai_kepercayaan' },
{ 'data': 'riwayat_psiko_pendidikan_pj' },
{ 'data': 'riwayat_psiko_edukasi_diberikan' },
{ 'data': 'penilaian_nyeri' },
{ 'data': 'penilaian_nyeri_penyebab' },
{ 'data': 'penilaian_nyeri_kualitas' },
{ 'data': 'penilaian_nyeri_lokasi' },
{ 'data': 'penilaian_nyeri_menyebar' },
{ 'data': 'penilaian_nyeri_skala' },
{ 'data': 'penilaian_nyeri_waktu' },
{ 'data': 'penilaian_nyeri_hilang' },
{ 'data': 'penilaian_nyeri_diberitahukan_dokter' },
{ 'data': 'penilaian_nyeri_jam_diberitahukan_dokter' },
{ 'data': 'penilaian_jatuhmorse_skala1' },
{ 'data': 'penilaian_jatuhmorse_nilai1' },
{ 'data': 'penilaian_jatuhmorse_skala2' },
{ 'data': 'penilaian_jatuhmorse_nilai2' },
{ 'data': 'penilaian_jatuhmorse_skala3' },
{ 'data': 'penilaian_jatuhmorse_nilai3' },
{ 'data': 'penilaian_jatuhmorse_skala4' },
{ 'data': 'penilaian_jatuhmorse_nilai4' },
{ 'data': 'penilaian_jatuhmorse_skala5' },
{ 'data': 'penilaian_jatuhmorse_nilai5' },
{ 'data': 'penilaian_jatuhmorse_skala6' },
{ 'data': 'penilaian_jatuhmorse_nilai6' },
{ 'data': 'penilaian_jatuhmorse_totalnilai' },
{ 'data': 'penilaian_jatuhsydney_skala1' },
{ 'data': 'penilaian_jatuhsydney_nilai1' },
{ 'data': 'penilaian_jatuhsydney_skala2' },
{ 'data': 'penilaian_jatuhsydney_nilai2' },
{ 'data': 'penilaian_jatuhsydney_skala3' },
{ 'data': 'penilaian_jatuhsydney_nilai3' },
{ 'data': 'penilaian_jatuhsydney_skala4' },
{ 'data': 'penilaian_jatuhsydney_nilai4' },
{ 'data': 'penilaian_jatuhsydney_skala5' },
{ 'data': 'penilaian_jatuhsydney_nilai5' },
{ 'data': 'penilaian_jatuhsydney_skala6' },
{ 'data': 'penilaian_jatuhsydney_nilai6' },
{ 'data': 'penilaian_jatuhsydney_skala7' },
{ 'data': 'penilaian_jatuhsydney_nilai7' },
{ 'data': 'penilaian_jatuhsydney_skala8' },
{ 'data': 'penilaian_jatuhsydney_nilai8' },
{ 'data': 'penilaian_jatuhsydney_skala9' },
{ 'data': 'penilaian_jatuhsydney_nilai9' },
{ 'data': 'penilaian_jatuhsydney_skala10' },
{ 'data': 'penilaian_jatuhsydney_nilai10' },
{ 'data': 'penilaian_jatuhsydney_skala11' },
{ 'data': 'penilaian_jatuhsydney_nilai11' },
{ 'data': 'penilaian_jatuhsydney_totalnilai' },
{ 'data': 'skrining_gizi1' },
{ 'data': 'nilai_gizi1' },
{ 'data': 'skrining_gizi2' },
{ 'data': 'nilai_gizi2' },
{ 'data': 'nilai_total_gizi' },
{ 'data': 'skrining_gizi_diagnosa_khusus' },
{ 'data': 'skrining_gizi_diketahui_dietisen' },
{ 'data': 'skrining_gizi_jam_diketahui_dietisen' },
{ 'data': 'rencana' },
{ 'data': 'nip1' },
{ 'data': 'nip2' },
{ 'data': 'kd_dokter' }

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
{ 'targets': 56},
{ 'targets': 57},
{ 'targets': 58},
{ 'targets': 59},
{ 'targets': 60},
{ 'targets': 61},
{ 'targets': 62},
{ 'targets': 63},
{ 'targets': 64},
{ 'targets': 65},
{ 'targets': 66},
{ 'targets': 67},
{ 'targets': 68},
{ 'targets': 69},
{ 'targets': 70},
{ 'targets': 71},
{ 'targets': 72},
{ 'targets': 73},
{ 'targets': 74},
{ 'targets': 75},
{ 'targets': 76},
{ 'targets': 77},
{ 'targets': 78},
{ 'targets': 79},
{ 'targets': 80},
{ 'targets': 81},
{ 'targets': 82},
{ 'targets': 83},
{ 'targets': 84},
{ 'targets': 85},
{ 'targets': 86},
{ 'targets': 87},
{ 'targets': 88},
{ 'targets': 89},
{ 'targets': 90},
{ 'targets': 91},
{ 'targets': 92},
{ 'targets': 93},
{ 'targets': 94},
{ 'targets': 95},
{ 'targets': 96},
{ 'targets': 97},
{ 'targets': 98},
{ 'targets': 99},
{ 'targets': 100},
{ 'targets': 101},
{ 'targets': 102},
{ 'targets': 103},
{ 'targets': 104},
{ 'targets': 105},
{ 'targets': 106},
{ 'targets': 107},
{ 'targets': 108},
{ 'targets': 109},
{ 'targets': 110},
{ 'targets': 111},
{ 'targets': 112},
{ 'targets': 113},
{ 'targets': 114},
{ 'targets': 115},
{ 'targets': 116},
{ 'targets': 117},
{ 'targets': 118},
{ 'targets': 119},
{ 'targets': 120},
{ 'targets': 121},
{ 'targets': 122},
{ 'targets': 123},
{ 'targets': 124},
{ 'targets': 125},
{ 'targets': 126},
{ 'targets': 127},
{ 'targets': 128},
{ 'targets': 129},
{ 'targets': 130},
{ 'targets': 131},
{ 'targets': 132},
{ 'targets': 133},
{ 'targets': 134},
{ 'targets': 135},
{ 'targets': 136},
{ 'targets': 137},
{ 'targets': 138},
{ 'targets': 139},
{ 'targets': 140},
{ 'targets': 141},
{ 'targets': 142},
{ 'targets': 143},
{ 'targets': 144},
{ 'targets': 145},
{ 'targets': 146},
{ 'targets': 147},
{ 'targets': 148},
{ 'targets': 149},
{ 'targets': 150},
{ 'targets': 151},
{ 'targets': 152},
{ 'targets': 153},
{ 'targets': 154},
{ 'targets': 155},
{ 'targets': 156},
{ 'targets': 157},
{ 'targets': 158},
{ 'targets': 159},
{ 'targets': 160},
{ 'targets': 161},
{ 'targets': 162},
{ 'targets': 163},
{ 'targets': 164}

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

    $("form[name='form_mlite_penilaian_awal_keperawatan_ranap']").validate({
        rules: {
no_rawat: 'required',
tanggal: 'required',
// informasi: 'required',
// ket_informasi: 'required',
// tiba_diruang_rawat: 'required',
// kasus_trauma: 'required',
// cara_masuk: 'required',
// rps: 'required',
// rpd: 'required',
// rpk: 'required',
// rpo: 'required',
// riwayat_pembedahan: 'required',
// riwayat_dirawat_dirs: 'required',
// alat_bantu_dipakai: 'required',
// riwayat_kehamilan: 'required',
// riwayat_kehamilan_perkiraan: 'required',
// riwayat_tranfusi: 'required',
// riwayat_alergi: 'required',
// riwayat_merokok: 'required',
// riwayat_merokok_jumlah: 'required',
// riwayat_alkohol: 'required',
// riwayat_alkohol_jumlah: 'required',
// riwayat_narkoba: 'required',
// riwayat_olahraga: 'required',
// pemeriksaan_mental: 'required',
// pemeriksaan_keadaan_umum: 'required',
// pemeriksaan_gcs: 'required',
// pemeriksaan_td: 'required',
// pemeriksaan_nadi: 'required',
// pemeriksaan_rr: 'required',
// pemeriksaan_suhu: 'required',
// pemeriksaan_spo2: 'required',
// pemeriksaan_bb: 'required',
// pemeriksaan_tb: 'required',
// pemeriksaan_susunan_kepala: 'required',
// pemeriksaan_susunan_wajah: 'required',
// pemeriksaan_susunan_leher: 'required',
// pemeriksaan_susunan_kejang: 'required',
// pemeriksaan_susunan_sensorik: 'required',
// pemeriksaan_kardiovaskuler_denyut_nadi: 'required',
// pemeriksaan_kardiovaskuler_sirkulasi: 'required',
// pemeriksaan_kardiovaskuler_pulsasi: 'required',
// pemeriksaan_respirasi_pola_nafas: 'required',
// pemeriksaan_respirasi_retraksi: 'required',
// pemeriksaan_respirasi_suara_nafas: 'required',
// pemeriksaan_respirasi_volume_pernafasan: 'required',
// pemeriksaan_respirasi_jenis_pernafasan: 'required',
// pemeriksaan_respirasi_irama_nafas: 'required',
// pemeriksaan_respirasi_batuk: 'required',
// pemeriksaan_gastrointestinal_mulut: 'required',
// pemeriksaan_gastrointestinal_gigi: 'required',
// pemeriksaan_gastrointestinal_lidah: 'required',
// pemeriksaan_gastrointestinal_tenggorokan: 'required',
// pemeriksaan_gastrointestinal_abdomen: 'required',
// pemeriksaan_gastrointestinal_peistatik_usus: 'required',
// pemeriksaan_gastrointestinal_anus: 'required',
// pemeriksaan_neurologi_pengelihatan: 'required',
// pemeriksaan_neurologi_alat_bantu_penglihatan: 'required',
// pemeriksaan_neurologi_pendengaran: 'required',
// pemeriksaan_neurologi_bicara: 'required',
// pemeriksaan_neurologi_sensorik: 'required',
// pemeriksaan_neurologi_motorik: 'required',
// pemeriksaan_neurologi_kekuatan_otot: 'required',
// pemeriksaan_integument_warnakulit: 'required',
// pemeriksaan_integument_turgor: 'required',
// pemeriksaan_integument_kulit: 'required',
// pemeriksaan_integument_dekubitas: 'required',
// pemeriksaan_muskuloskletal_pergerakan_sendi: 'required',
// pemeriksaan_muskuloskletal_kekauatan_otot: 'required',
// pemeriksaan_muskuloskletal_nyeri_sendi: 'required',
// pemeriksaan_muskuloskletal_oedema: 'required',
// pemeriksaan_muskuloskletal_fraktur: 'required',
// pemeriksaan_eliminasi_bab_frekuensi_jumlah: 'required',
// pemeriksaan_eliminasi_bab_frekuensi_durasi: 'required',
// pemeriksaan_eliminasi_bab_konsistensi: 'required',
// pemeriksaan_eliminasi_bab_warna: 'required',
// pemeriksaan_eliminasi_bak_frekuensi_jumlah: 'required',
// pemeriksaan_eliminasi_bak_frekuensi_durasi: 'required',
// pemeriksaan_eliminasi_bak_warna: 'required',
// pemeriksaan_eliminasi_bak_lainlain: 'required',
// pola_aktifitas_makanminum: 'required',
// pola_aktifitas_mandi: 'required',
// pola_aktifitas_eliminasi: 'required',
// pola_aktifitas_berpakaian: 'required',
// pola_aktifitas_berpindah: 'required',
// pola_nutrisi_frekuesi_makan: 'required',
// pola_nutrisi_jenis_makanan: 'required',
// pola_nutrisi_porsi_makan: 'required',
// pola_tidur_lama_tidur: 'required',
// pola_tidur_gangguan: 'required',
// pengkajian_fungsi_kemampuan_sehari: 'required',
// pengkajian_fungsi_aktifitas: 'required',
// pengkajian_fungsi_berjalan: 'required',
// pengkajian_fungsi_ambulasi: 'required',
// pengkajian_fungsi_ekstrimitas_atas: 'required',
// pengkajian_fungsi_ekstrimitas_bawah: 'required',
// pengkajian_fungsi_menggenggam: 'required',
// pengkajian_fungsi_koordinasi: 'required',
// pengkajian_fungsi_kesimpulan: 'required',
// riwayat_psiko_kondisi_psiko: 'required',
// riwayat_psiko_gangguan_jiwa: 'required',
// riwayat_psiko_perilaku: 'required',
// riwayat_psiko_hubungan_keluarga: 'required',
// riwayat_psiko_tinggal: 'required',
// riwayat_psiko_nilai_kepercayaan: 'required',
// riwayat_psiko_pendidikan_pj: 'required',
// riwayat_psiko_edukasi_diberikan: 'required',
// penilaian_nyeri: 'required',
// penilaian_nyeri_penyebab: 'required',
// penilaian_nyeri_kualitas: 'required',
// penilaian_nyeri_lokasi: 'required',
// penilaian_nyeri_menyebar: 'required',
// penilaian_nyeri_skala: 'required',
// penilaian_nyeri_waktu: 'required',
// penilaian_nyeri_hilang: 'required',
// penilaian_nyeri_diberitahukan_dokter: 'required',
// penilaian_nyeri_jam_diberitahukan_dokter: 'required',
// penilaian_jatuhmorse_skala1: 'required',
// penilaian_jatuhmorse_nilai1: 'required',
// penilaian_jatuhmorse_skala2: 'required',
// penilaian_jatuhmorse_nilai2: 'required',
// penilaian_jatuhmorse_skala3: 'required',
// penilaian_jatuhmorse_nilai3: 'required',
// penilaian_jatuhmorse_skala4: 'required',
// penilaian_jatuhmorse_nilai4: 'required',
// penilaian_jatuhmorse_skala5: 'required',
// penilaian_jatuhmorse_nilai5: 'required',
// penilaian_jatuhmorse_skala6: 'required',
// penilaian_jatuhmorse_nilai6: 'required',
// penilaian_jatuhmorse_totalnilai: 'required',
// penilaian_jatuhsydney_skala1: 'required',
// penilaian_jatuhsydney_nilai1: 'required',
// penilaian_jatuhsydney_skala2: 'required',
// penilaian_jatuhsydney_nilai2: 'required',
// penilaian_jatuhsydney_skala3: 'required',
// penilaian_jatuhsydney_nilai3: 'required',
// penilaian_jatuhsydney_skala4: 'required',
// penilaian_jatuhsydney_nilai4: 'required',
// penilaian_jatuhsydney_skala5: 'required',
// penilaian_jatuhsydney_nilai5: 'required',
// penilaian_jatuhsydney_skala6: 'required',
// penilaian_jatuhsydney_nilai6: 'required',
// penilaian_jatuhsydney_skala7: 'required',
// penilaian_jatuhsydney_nilai7: 'required',
// penilaian_jatuhsydney_skala8: 'required',
// penilaian_jatuhsydney_nilai8: 'required',
// penilaian_jatuhsydney_skala9: 'required',
// penilaian_jatuhsydney_nilai9: 'required',
// penilaian_jatuhsydney_skala10: 'required',
// penilaian_jatuhsydney_nilai10: 'required',
// penilaian_jatuhsydney_skala11: 'required',
// penilaian_jatuhsydney_nilai11: 'required',
// penilaian_jatuhsydney_totalnilai: 'required',
// skrining_gizi1: 'required',
// nilai_gizi1: 'required',
// skrining_gizi2: 'required',
// nilai_gizi2: 'required',
// nilai_total_gizi: 'required',
// skrining_gizi_diagnosa_khusus: 'required',
// skrining_gizi_diketahui_dietisen: 'required',
// skrining_gizi_jam_diketahui_dietisen: 'required',
// rencana: 'required',
// nip1: 'required',
// nip2: 'required',
kd_dokter: 'required'

        },
        messages: {
no_rawat:'no_rawat tidak boleh kosong!',
tanggal:'tanggal tidak boleh kosong!',
informasi:'informasi tidak boleh kosong!',
ket_informasi:'ket_informasi tidak boleh kosong!',
tiba_diruang_rawat:'tiba_diruang_rawat tidak boleh kosong!',
kasus_trauma:'kasus_trauma tidak boleh kosong!',
cara_masuk:'cara_masuk tidak boleh kosong!',
rps:'rps tidak boleh kosong!',
rpd:'rpd tidak boleh kosong!',
rpk:'rpk tidak boleh kosong!',
rpo:'rpo tidak boleh kosong!',
riwayat_pembedahan:'riwayat_pembedahan tidak boleh kosong!',
riwayat_dirawat_dirs:'riwayat_dirawat_dirs tidak boleh kosong!',
alat_bantu_dipakai:'alat_bantu_dipakai tidak boleh kosong!',
riwayat_kehamilan:'riwayat_kehamilan tidak boleh kosong!',
riwayat_kehamilan_perkiraan:'riwayat_kehamilan_perkiraan tidak boleh kosong!',
riwayat_tranfusi:'riwayat_tranfusi tidak boleh kosong!',
riwayat_alergi:'riwayat_alergi tidak boleh kosong!',
riwayat_merokok:'riwayat_merokok tidak boleh kosong!',
riwayat_merokok_jumlah:'riwayat_merokok_jumlah tidak boleh kosong!',
riwayat_alkohol:'riwayat_alkohol tidak boleh kosong!',
riwayat_alkohol_jumlah:'riwayat_alkohol_jumlah tidak boleh kosong!',
riwayat_narkoba:'riwayat_narkoba tidak boleh kosong!',
riwayat_olahraga:'riwayat_olahraga tidak boleh kosong!',
pemeriksaan_mental:'pemeriksaan_mental tidak boleh kosong!',
pemeriksaan_keadaan_umum:'pemeriksaan_keadaan_umum tidak boleh kosong!',
pemeriksaan_gcs:'pemeriksaan_gcs tidak boleh kosong!',
pemeriksaan_td:'pemeriksaan_td tidak boleh kosong!',
pemeriksaan_nadi:'pemeriksaan_nadi tidak boleh kosong!',
pemeriksaan_rr:'pemeriksaan_rr tidak boleh kosong!',
pemeriksaan_suhu:'pemeriksaan_suhu tidak boleh kosong!',
pemeriksaan_spo2:'pemeriksaan_spo2 tidak boleh kosong!',
pemeriksaan_bb:'pemeriksaan_bb tidak boleh kosong!',
pemeriksaan_tb:'pemeriksaan_tb tidak boleh kosong!',
pemeriksaan_susunan_kepala:'pemeriksaan_susunan_kepala tidak boleh kosong!',
pemeriksaan_susunan_wajah:'pemeriksaan_susunan_wajah tidak boleh kosong!',
pemeriksaan_susunan_leher:'pemeriksaan_susunan_leher tidak boleh kosong!',
pemeriksaan_susunan_kejang:'pemeriksaan_susunan_kejang tidak boleh kosong!',
pemeriksaan_susunan_sensorik:'pemeriksaan_susunan_sensorik tidak boleh kosong!',
pemeriksaan_kardiovaskuler_denyut_nadi:'pemeriksaan_kardiovaskuler_denyut_nadi tidak boleh kosong!',
pemeriksaan_kardiovaskuler_sirkulasi:'pemeriksaan_kardiovaskuler_sirkulasi tidak boleh kosong!',
pemeriksaan_kardiovaskuler_pulsasi:'pemeriksaan_kardiovaskuler_pulsasi tidak boleh kosong!',
pemeriksaan_respirasi_pola_nafas:'pemeriksaan_respirasi_pola_nafas tidak boleh kosong!',
pemeriksaan_respirasi_retraksi:'pemeriksaan_respirasi_retraksi tidak boleh kosong!',
pemeriksaan_respirasi_suara_nafas:'pemeriksaan_respirasi_suara_nafas tidak boleh kosong!',
pemeriksaan_respirasi_volume_pernafasan:'pemeriksaan_respirasi_volume_pernafasan tidak boleh kosong!',
pemeriksaan_respirasi_jenis_pernafasan:'pemeriksaan_respirasi_jenis_pernafasan tidak boleh kosong!',
pemeriksaan_respirasi_irama_nafas:'pemeriksaan_respirasi_irama_nafas tidak boleh kosong!',
pemeriksaan_respirasi_batuk:'pemeriksaan_respirasi_batuk tidak boleh kosong!',
pemeriksaan_gastrointestinal_mulut:'pemeriksaan_gastrointestinal_mulut tidak boleh kosong!',
pemeriksaan_gastrointestinal_gigi:'pemeriksaan_gastrointestinal_gigi tidak boleh kosong!',
pemeriksaan_gastrointestinal_lidah:'pemeriksaan_gastrointestinal_lidah tidak boleh kosong!',
pemeriksaan_gastrointestinal_tenggorokan:'pemeriksaan_gastrointestinal_tenggorokan tidak boleh kosong!',
pemeriksaan_gastrointestinal_abdomen:'pemeriksaan_gastrointestinal_abdomen tidak boleh kosong!',
pemeriksaan_gastrointestinal_peistatik_usus:'pemeriksaan_gastrointestinal_peistatik_usus tidak boleh kosong!',
pemeriksaan_gastrointestinal_anus:'pemeriksaan_gastrointestinal_anus tidak boleh kosong!',
pemeriksaan_neurologi_pengelihatan:'pemeriksaan_neurologi_pengelihatan tidak boleh kosong!',
pemeriksaan_neurologi_alat_bantu_penglihatan:'pemeriksaan_neurologi_alat_bantu_penglihatan tidak boleh kosong!',
pemeriksaan_neurologi_pendengaran:'pemeriksaan_neurologi_pendengaran tidak boleh kosong!',
pemeriksaan_neurologi_bicara:'pemeriksaan_neurologi_bicara tidak boleh kosong!',
pemeriksaan_neurologi_sensorik:'pemeriksaan_neurologi_sensorik tidak boleh kosong!',
pemeriksaan_neurologi_motorik:'pemeriksaan_neurologi_motorik tidak boleh kosong!',
pemeriksaan_neurologi_kekuatan_otot:'pemeriksaan_neurologi_kekuatan_otot tidak boleh kosong!',
pemeriksaan_integument_warnakulit:'pemeriksaan_integument_warnakulit tidak boleh kosong!',
pemeriksaan_integument_turgor:'pemeriksaan_integument_turgor tidak boleh kosong!',
pemeriksaan_integument_kulit:'pemeriksaan_integument_kulit tidak boleh kosong!',
pemeriksaan_integument_dekubitas:'pemeriksaan_integument_dekubitas tidak boleh kosong!',
pemeriksaan_muskuloskletal_pergerakan_sendi:'pemeriksaan_muskuloskletal_pergerakan_sendi tidak boleh kosong!',
pemeriksaan_muskuloskletal_kekauatan_otot:'pemeriksaan_muskuloskletal_kekauatan_otot tidak boleh kosong!',
pemeriksaan_muskuloskletal_nyeri_sendi:'pemeriksaan_muskuloskletal_nyeri_sendi tidak boleh kosong!',
pemeriksaan_muskuloskletal_oedema:'pemeriksaan_muskuloskletal_oedema tidak boleh kosong!',
pemeriksaan_muskuloskletal_fraktur:'pemeriksaan_muskuloskletal_fraktur tidak boleh kosong!',
pemeriksaan_eliminasi_bab_frekuensi_jumlah:'pemeriksaan_eliminasi_bab_frekuensi_jumlah tidak boleh kosong!',
pemeriksaan_eliminasi_bab_frekuensi_durasi:'pemeriksaan_eliminasi_bab_frekuensi_durasi tidak boleh kosong!',
pemeriksaan_eliminasi_bab_konsistensi:'pemeriksaan_eliminasi_bab_konsistensi tidak boleh kosong!',
pemeriksaan_eliminasi_bab_warna:'pemeriksaan_eliminasi_bab_warna tidak boleh kosong!',
pemeriksaan_eliminasi_bak_frekuensi_jumlah:'pemeriksaan_eliminasi_bak_frekuensi_jumlah tidak boleh kosong!',
pemeriksaan_eliminasi_bak_frekuensi_durasi:'pemeriksaan_eliminasi_bak_frekuensi_durasi tidak boleh kosong!',
pemeriksaan_eliminasi_bak_warna:'pemeriksaan_eliminasi_bak_warna tidak boleh kosong!',
pemeriksaan_eliminasi_bak_lainlain:'pemeriksaan_eliminasi_bak_lainlain tidak boleh kosong!',
pola_aktifitas_makanminum:'pola_aktifitas_makanminum tidak boleh kosong!',
pola_aktifitas_mandi:'pola_aktifitas_mandi tidak boleh kosong!',
pola_aktifitas_eliminasi:'pola_aktifitas_eliminasi tidak boleh kosong!',
pola_aktifitas_berpakaian:'pola_aktifitas_berpakaian tidak boleh kosong!',
pola_aktifitas_berpindah:'pola_aktifitas_berpindah tidak boleh kosong!',
pola_nutrisi_frekuesi_makan:'pola_nutrisi_frekuesi_makan tidak boleh kosong!',
pola_nutrisi_jenis_makanan:'pola_nutrisi_jenis_makanan tidak boleh kosong!',
pola_nutrisi_porsi_makan:'pola_nutrisi_porsi_makan tidak boleh kosong!',
pola_tidur_lama_tidur:'pola_tidur_lama_tidur tidak boleh kosong!',
pola_tidur_gangguan:'pola_tidur_gangguan tidak boleh kosong!',
pengkajian_fungsi_kemampuan_sehari:'pengkajian_fungsi_kemampuan_sehari tidak boleh kosong!',
pengkajian_fungsi_aktifitas:'pengkajian_fungsi_aktifitas tidak boleh kosong!',
pengkajian_fungsi_berjalan:'pengkajian_fungsi_berjalan tidak boleh kosong!',
pengkajian_fungsi_ambulasi:'pengkajian_fungsi_ambulasi tidak boleh kosong!',
pengkajian_fungsi_ekstrimitas_atas:'pengkajian_fungsi_ekstrimitas_atas tidak boleh kosong!',
pengkajian_fungsi_ekstrimitas_bawah:'pengkajian_fungsi_ekstrimitas_bawah tidak boleh kosong!',
pengkajian_fungsi_menggenggam:'pengkajian_fungsi_menggenggam tidak boleh kosong!',
pengkajian_fungsi_koordinasi:'pengkajian_fungsi_koordinasi tidak boleh kosong!',
pengkajian_fungsi_kesimpulan:'pengkajian_fungsi_kesimpulan tidak boleh kosong!',
riwayat_psiko_kondisi_psiko:'riwayat_psiko_kondisi_psiko tidak boleh kosong!',
riwayat_psiko_gangguan_jiwa:'riwayat_psiko_gangguan_jiwa tidak boleh kosong!',
riwayat_psiko_perilaku:'riwayat_psiko_perilaku tidak boleh kosong!',
riwayat_psiko_hubungan_keluarga:'riwayat_psiko_hubungan_keluarga tidak boleh kosong!',
riwayat_psiko_tinggal:'riwayat_psiko_tinggal tidak boleh kosong!',
riwayat_psiko_nilai_kepercayaan:'riwayat_psiko_nilai_kepercayaan tidak boleh kosong!',
riwayat_psiko_pendidikan_pj:'riwayat_psiko_pendidikan_pj tidak boleh kosong!',
riwayat_psiko_edukasi_diberikan:'riwayat_psiko_edukasi_diberikan tidak boleh kosong!',
penilaian_nyeri:'penilaian_nyeri tidak boleh kosong!',
penilaian_nyeri_penyebab:'penilaian_nyeri_penyebab tidak boleh kosong!',
penilaian_nyeri_kualitas:'penilaian_nyeri_kualitas tidak boleh kosong!',
penilaian_nyeri_lokasi:'penilaian_nyeri_lokasi tidak boleh kosong!',
penilaian_nyeri_menyebar:'penilaian_nyeri_menyebar tidak boleh kosong!',
penilaian_nyeri_skala:'penilaian_nyeri_skala tidak boleh kosong!',
penilaian_nyeri_waktu:'penilaian_nyeri_waktu tidak boleh kosong!',
penilaian_nyeri_hilang:'penilaian_nyeri_hilang tidak boleh kosong!',
penilaian_nyeri_diberitahukan_dokter:'penilaian_nyeri_diberitahukan_dokter tidak boleh kosong!',
penilaian_nyeri_jam_diberitahukan_dokter:'penilaian_nyeri_jam_diberitahukan_dokter tidak boleh kosong!',
penilaian_jatuhmorse_skala1:'penilaian_jatuhmorse_skala1 tidak boleh kosong!',
penilaian_jatuhmorse_nilai1:'penilaian_jatuhmorse_nilai1 tidak boleh kosong!',
penilaian_jatuhmorse_skala2:'penilaian_jatuhmorse_skala2 tidak boleh kosong!',
penilaian_jatuhmorse_nilai2:'penilaian_jatuhmorse_nilai2 tidak boleh kosong!',
penilaian_jatuhmorse_skala3:'penilaian_jatuhmorse_skala3 tidak boleh kosong!',
penilaian_jatuhmorse_nilai3:'penilaian_jatuhmorse_nilai3 tidak boleh kosong!',
penilaian_jatuhmorse_skala4:'penilaian_jatuhmorse_skala4 tidak boleh kosong!',
penilaian_jatuhmorse_nilai4:'penilaian_jatuhmorse_nilai4 tidak boleh kosong!',
penilaian_jatuhmorse_skala5:'penilaian_jatuhmorse_skala5 tidak boleh kosong!',
penilaian_jatuhmorse_nilai5:'penilaian_jatuhmorse_nilai5 tidak boleh kosong!',
penilaian_jatuhmorse_skala6:'penilaian_jatuhmorse_skala6 tidak boleh kosong!',
penilaian_jatuhmorse_nilai6:'penilaian_jatuhmorse_nilai6 tidak boleh kosong!',
penilaian_jatuhmorse_totalnilai:'penilaian_jatuhmorse_totalnilai tidak boleh kosong!',
penilaian_jatuhsydney_skala1:'penilaian_jatuhsydney_skala1 tidak boleh kosong!',
penilaian_jatuhsydney_nilai1:'penilaian_jatuhsydney_nilai1 tidak boleh kosong!',
penilaian_jatuhsydney_skala2:'penilaian_jatuhsydney_skala2 tidak boleh kosong!',
penilaian_jatuhsydney_nilai2:'penilaian_jatuhsydney_nilai2 tidak boleh kosong!',
penilaian_jatuhsydney_skala3:'penilaian_jatuhsydney_skala3 tidak boleh kosong!',
penilaian_jatuhsydney_nilai3:'penilaian_jatuhsydney_nilai3 tidak boleh kosong!',
penilaian_jatuhsydney_skala4:'penilaian_jatuhsydney_skala4 tidak boleh kosong!',
penilaian_jatuhsydney_nilai4:'penilaian_jatuhsydney_nilai4 tidak boleh kosong!',
penilaian_jatuhsydney_skala5:'penilaian_jatuhsydney_skala5 tidak boleh kosong!',
penilaian_jatuhsydney_nilai5:'penilaian_jatuhsydney_nilai5 tidak boleh kosong!',
penilaian_jatuhsydney_skala6:'penilaian_jatuhsydney_skala6 tidak boleh kosong!',
penilaian_jatuhsydney_nilai6:'penilaian_jatuhsydney_nilai6 tidak boleh kosong!',
penilaian_jatuhsydney_skala7:'penilaian_jatuhsydney_skala7 tidak boleh kosong!',
penilaian_jatuhsydney_nilai7:'penilaian_jatuhsydney_nilai7 tidak boleh kosong!',
penilaian_jatuhsydney_skala8:'penilaian_jatuhsydney_skala8 tidak boleh kosong!',
penilaian_jatuhsydney_nilai8:'penilaian_jatuhsydney_nilai8 tidak boleh kosong!',
penilaian_jatuhsydney_skala9:'penilaian_jatuhsydney_skala9 tidak boleh kosong!',
penilaian_jatuhsydney_nilai9:'penilaian_jatuhsydney_nilai9 tidak boleh kosong!',
penilaian_jatuhsydney_skala10:'penilaian_jatuhsydney_skala10 tidak boleh kosong!',
penilaian_jatuhsydney_nilai10:'penilaian_jatuhsydney_nilai10 tidak boleh kosong!',
penilaian_jatuhsydney_skala11:'penilaian_jatuhsydney_skala11 tidak boleh kosong!',
penilaian_jatuhsydney_nilai11:'penilaian_jatuhsydney_nilai11 tidak boleh kosong!',
penilaian_jatuhsydney_totalnilai:'penilaian_jatuhsydney_totalnilai tidak boleh kosong!',
skrining_gizi1:'skrining_gizi1 tidak boleh kosong!',
nilai_gizi1:'nilai_gizi1 tidak boleh kosong!',
skrining_gizi2:'skrining_gizi2 tidak boleh kosong!',
nilai_gizi2:'nilai_gizi2 tidak boleh kosong!',
nilai_total_gizi:'nilai_total_gizi tidak boleh kosong!',
skrining_gizi_diagnosa_khusus:'skrining_gizi_diagnosa_khusus tidak boleh kosong!',
skrining_gizi_diketahui_dietisen:'skrining_gizi_diketahui_dietisen tidak boleh kosong!',
skrining_gizi_jam_diketahui_dietisen:'skrining_gizi_jam_diketahui_dietisen tidak boleh kosong!',
rencana:'rencana tidak boleh kosong!',
nip1:'nip1 tidak boleh kosong!',
nip2:'nip2 tidak boleh kosong!',
kd_dokter:'kd_dokter tidak boleh kosong!'

        },
        submitHandler: function (form) {
 var no_rawat= $('#no_rawat').val();
var tanggal= $('#tanggal').val();
var informasi= $('#informasi').val();
var ket_informasi= $('#ket_informasi').val();
var tiba_diruang_rawat= $('#tiba_diruang_rawat').val();
var kasus_trauma= $('#kasus_trauma').val();
var cara_masuk= $('#cara_masuk').val();
var rps= $('#rps').val();
var rpd= $('#rpd').val();
var rpk= $('#rpk').val();
var rpo= $('#rpo').val();
var riwayat_pembedahan= $('#riwayat_pembedahan').val();
var riwayat_dirawat_dirs= $('#riwayat_dirawat_dirs').val();
var alat_bantu_dipakai= $('#alat_bantu_dipakai').val();
var riwayat_kehamilan= $('#riwayat_kehamilan').val();
var riwayat_kehamilan_perkiraan= $('#riwayat_kehamilan_perkiraan').val();
var riwayat_tranfusi= $('#riwayat_tranfusi').val();
var riwayat_alergi= $('#riwayat_alergi').val();
var riwayat_merokok= $('#riwayat_merokok').val();
var riwayat_merokok_jumlah= $('#riwayat_merokok_jumlah').val();
var riwayat_alkohol= $('#riwayat_alkohol').val();
var riwayat_alkohol_jumlah= $('#riwayat_alkohol_jumlah').val();
var riwayat_narkoba= $('#riwayat_narkoba').val();
var riwayat_olahraga= $('#riwayat_olahraga').val();
var pemeriksaan_mental= $('#pemeriksaan_mental').val();
var pemeriksaan_keadaan_umum= $('#pemeriksaan_keadaan_umum').val();
var pemeriksaan_gcs= $('#pemeriksaan_gcs').val();
var pemeriksaan_td= $('#pemeriksaan_td').val();
var pemeriksaan_nadi= $('#pemeriksaan_nadi').val();
var pemeriksaan_rr= $('#pemeriksaan_rr').val();
var pemeriksaan_suhu= $('#pemeriksaan_suhu').val();
var pemeriksaan_spo2= $('#pemeriksaan_spo2').val();
var pemeriksaan_bb= $('#pemeriksaan_bb').val();
var pemeriksaan_tb= $('#pemeriksaan_tb').val();
var pemeriksaan_susunan_kepala= $('#pemeriksaan_susunan_kepala').val();
var pemeriksaan_susunan_wajah= $('#pemeriksaan_susunan_wajah').val();
var pemeriksaan_susunan_leher= $('#pemeriksaan_susunan_leher').val();
var pemeriksaan_susunan_kejang= $('#pemeriksaan_susunan_kejang').val();
var pemeriksaan_susunan_sensorik= $('#pemeriksaan_susunan_sensorik').val();
var pemeriksaan_kardiovaskuler_denyut_nadi= $('#pemeriksaan_kardiovaskuler_denyut_nadi').val();
var pemeriksaan_kardiovaskuler_sirkulasi= $('#pemeriksaan_kardiovaskuler_sirkulasi').val();
var pemeriksaan_kardiovaskuler_pulsasi= $('#pemeriksaan_kardiovaskuler_pulsasi').val();
var pemeriksaan_respirasi_pola_nafas= $('#pemeriksaan_respirasi_pola_nafas').val();
var pemeriksaan_respirasi_retraksi= $('#pemeriksaan_respirasi_retraksi').val();
var pemeriksaan_respirasi_suara_nafas= $('#pemeriksaan_respirasi_suara_nafas').val();
var pemeriksaan_respirasi_volume_pernafasan= $('#pemeriksaan_respirasi_volume_pernafasan').val();
var pemeriksaan_respirasi_jenis_pernafasan= $('#pemeriksaan_respirasi_jenis_pernafasan').val();
var pemeriksaan_respirasi_irama_nafas= $('#pemeriksaan_respirasi_irama_nafas').val();
var pemeriksaan_respirasi_batuk= $('#pemeriksaan_respirasi_batuk').val();
var pemeriksaan_gastrointestinal_mulut= $('#pemeriksaan_gastrointestinal_mulut').val();
var pemeriksaan_gastrointestinal_gigi= $('#pemeriksaan_gastrointestinal_gigi').val();
var pemeriksaan_gastrointestinal_lidah= $('#pemeriksaan_gastrointestinal_lidah').val();
var pemeriksaan_gastrointestinal_tenggorokan= $('#pemeriksaan_gastrointestinal_tenggorokan').val();
var pemeriksaan_gastrointestinal_abdomen= $('#pemeriksaan_gastrointestinal_abdomen').val();
var pemeriksaan_gastrointestinal_peistatik_usus= $('#pemeriksaan_gastrointestinal_peistatik_usus').val();
var pemeriksaan_gastrointestinal_anus= $('#pemeriksaan_gastrointestinal_anus').val();
var pemeriksaan_neurologi_pengelihatan= $('#pemeriksaan_neurologi_pengelihatan').val();
var pemeriksaan_neurologi_alat_bantu_penglihatan= $('#pemeriksaan_neurologi_alat_bantu_penglihatan').val();
var pemeriksaan_neurologi_pendengaran= $('#pemeriksaan_neurologi_pendengaran').val();
var pemeriksaan_neurologi_bicara= $('#pemeriksaan_neurologi_bicara').val();
var pemeriksaan_neurologi_sensorik= $('#pemeriksaan_neurologi_sensorik').val();
var pemeriksaan_neurologi_motorik= $('#pemeriksaan_neurologi_motorik').val();
var pemeriksaan_neurologi_kekuatan_otot= $('#pemeriksaan_neurologi_kekuatan_otot').val();
var pemeriksaan_integument_warnakulit= $('#pemeriksaan_integument_warnakulit').val();
var pemeriksaan_integument_turgor= $('#pemeriksaan_integument_turgor').val();
var pemeriksaan_integument_kulit= $('#pemeriksaan_integument_kulit').val();
var pemeriksaan_integument_dekubitas= $('#pemeriksaan_integument_dekubitas').val();
var pemeriksaan_muskuloskletal_pergerakan_sendi= $('#pemeriksaan_muskuloskletal_pergerakan_sendi').val();
var pemeriksaan_muskuloskletal_kekauatan_otot= $('#pemeriksaan_muskuloskletal_kekauatan_otot').val();
var pemeriksaan_muskuloskletal_nyeri_sendi= $('#pemeriksaan_muskuloskletal_nyeri_sendi').val();
var pemeriksaan_muskuloskletal_oedema= $('#pemeriksaan_muskuloskletal_oedema').val();
var pemeriksaan_muskuloskletal_fraktur= $('#pemeriksaan_muskuloskletal_fraktur').val();
var pemeriksaan_eliminasi_bab_frekuensi_jumlah= $('#pemeriksaan_eliminasi_bab_frekuensi_jumlah').val();
var pemeriksaan_eliminasi_bab_frekuensi_durasi= $('#pemeriksaan_eliminasi_bab_frekuensi_durasi').val();
var pemeriksaan_eliminasi_bab_konsistensi= $('#pemeriksaan_eliminasi_bab_konsistensi').val();
var pemeriksaan_eliminasi_bab_warna= $('#pemeriksaan_eliminasi_bab_warna').val();
var pemeriksaan_eliminasi_bak_frekuensi_jumlah= $('#pemeriksaan_eliminasi_bak_frekuensi_jumlah').val();
var pemeriksaan_eliminasi_bak_frekuensi_durasi= $('#pemeriksaan_eliminasi_bak_frekuensi_durasi').val();
var pemeriksaan_eliminasi_bak_warna= $('#pemeriksaan_eliminasi_bak_warna').val();
var pemeriksaan_eliminasi_bak_lainlain= $('#pemeriksaan_eliminasi_bak_lainlain').val();
var pola_aktifitas_makanminum= $('#pola_aktifitas_makanminum').val();
var pola_aktifitas_mandi= $('#pola_aktifitas_mandi').val();
var pola_aktifitas_eliminasi= $('#pola_aktifitas_eliminasi').val();
var pola_aktifitas_berpakaian= $('#pola_aktifitas_berpakaian').val();
var pola_aktifitas_berpindah= $('#pola_aktifitas_berpindah').val();
var pola_nutrisi_frekuesi_makan= $('#pola_nutrisi_frekuesi_makan').val();
var pola_nutrisi_jenis_makanan= $('#pola_nutrisi_jenis_makanan').val();
var pola_nutrisi_porsi_makan= $('#pola_nutrisi_porsi_makan').val();
var pola_tidur_lama_tidur= $('#pola_tidur_lama_tidur').val();
var pola_tidur_gangguan= $('#pola_tidur_gangguan').val();
var pengkajian_fungsi_kemampuan_sehari= $('#pengkajian_fungsi_kemampuan_sehari').val();
var pengkajian_fungsi_aktifitas= $('#pengkajian_fungsi_aktifitas').val();
var pengkajian_fungsi_berjalan= $('#pengkajian_fungsi_berjalan').val();
var pengkajian_fungsi_ambulasi= $('#pengkajian_fungsi_ambulasi').val();
var pengkajian_fungsi_ekstrimitas_atas= $('#pengkajian_fungsi_ekstrimitas_atas').val();
var pengkajian_fungsi_ekstrimitas_bawah= $('#pengkajian_fungsi_ekstrimitas_bawah').val();
var pengkajian_fungsi_menggenggam= $('#pengkajian_fungsi_menggenggam').val();
var pengkajian_fungsi_koordinasi= $('#pengkajian_fungsi_koordinasi').val();
var pengkajian_fungsi_kesimpulan= $('#pengkajian_fungsi_kesimpulan').val();
var riwayat_psiko_kondisi_psiko= $('#riwayat_psiko_kondisi_psiko').val();
var riwayat_psiko_gangguan_jiwa= $('#riwayat_psiko_gangguan_jiwa').val();
var riwayat_psiko_perilaku= $('#riwayat_psiko_perilaku').val();
var riwayat_psiko_hubungan_keluarga= $('#riwayat_psiko_hubungan_keluarga').val();
var riwayat_psiko_tinggal= $('#riwayat_psiko_tinggal').val();
var riwayat_psiko_nilai_kepercayaan= $('#riwayat_psiko_nilai_kepercayaan').val();
var riwayat_psiko_pendidikan_pj= $('#riwayat_psiko_pendidikan_pj').val();
var riwayat_psiko_edukasi_diberikan= $('#riwayat_psiko_edukasi_diberikan').val();
var penilaian_nyeri= $('#penilaian_nyeri').val();
var penilaian_nyeri_penyebab= $('#penilaian_nyeri_penyebab').val();
var penilaian_nyeri_kualitas= $('#penilaian_nyeri_kualitas').val();
var penilaian_nyeri_lokasi= $('#penilaian_nyeri_lokasi').val();
var penilaian_nyeri_menyebar= $('#penilaian_nyeri_menyebar').val();
var penilaian_nyeri_skala= $('#penilaian_nyeri_skala').val();
var penilaian_nyeri_waktu= $('#penilaian_nyeri_waktu').val();
var penilaian_nyeri_hilang= $('#penilaian_nyeri_hilang').val();
var penilaian_nyeri_diberitahukan_dokter= $('#penilaian_nyeri_diberitahukan_dokter').val();
var penilaian_nyeri_jam_diberitahukan_dokter= $('#penilaian_nyeri_jam_diberitahukan_dokter').val();
var penilaian_jatuhmorse_skala1= $('#penilaian_jatuhmorse_skala1').val();
var penilaian_jatuhmorse_nilai1= $('#penilaian_jatuhmorse_nilai1').val();
var penilaian_jatuhmorse_skala2= $('#penilaian_jatuhmorse_skala2').val();
var penilaian_jatuhmorse_nilai2= $('#penilaian_jatuhmorse_nilai2').val();
var penilaian_jatuhmorse_skala3= $('#penilaian_jatuhmorse_skala3').val();
var penilaian_jatuhmorse_nilai3= $('#penilaian_jatuhmorse_nilai3').val();
var penilaian_jatuhmorse_skala4= $('#penilaian_jatuhmorse_skala4').val();
var penilaian_jatuhmorse_nilai4= $('#penilaian_jatuhmorse_nilai4').val();
var penilaian_jatuhmorse_skala5= $('#penilaian_jatuhmorse_skala5').val();
var penilaian_jatuhmorse_nilai5= $('#penilaian_jatuhmorse_nilai5').val();
var penilaian_jatuhmorse_skala6= $('#penilaian_jatuhmorse_skala6').val();
var penilaian_jatuhmorse_nilai6= $('#penilaian_jatuhmorse_nilai6').val();
var penilaian_jatuhmorse_totalnilai= $('#penilaian_jatuhmorse_totalnilai').val();
var penilaian_jatuhsydney_skala1= $('#penilaian_jatuhsydney_skala1').val();
var penilaian_jatuhsydney_nilai1= $('#penilaian_jatuhsydney_nilai1').val();
var penilaian_jatuhsydney_skala2= $('#penilaian_jatuhsydney_skala2').val();
var penilaian_jatuhsydney_nilai2= $('#penilaian_jatuhsydney_nilai2').val();
var penilaian_jatuhsydney_skala3= $('#penilaian_jatuhsydney_skala3').val();
var penilaian_jatuhsydney_nilai3= $('#penilaian_jatuhsydney_nilai3').val();
var penilaian_jatuhsydney_skala4= $('#penilaian_jatuhsydney_skala4').val();
var penilaian_jatuhsydney_nilai4= $('#penilaian_jatuhsydney_nilai4').val();
var penilaian_jatuhsydney_skala5= $('#penilaian_jatuhsydney_skala5').val();
var penilaian_jatuhsydney_nilai5= $('#penilaian_jatuhsydney_nilai5').val();
var penilaian_jatuhsydney_skala6= $('#penilaian_jatuhsydney_skala6').val();
var penilaian_jatuhsydney_nilai6= $('#penilaian_jatuhsydney_nilai6').val();
var penilaian_jatuhsydney_skala7= $('#penilaian_jatuhsydney_skala7').val();
var penilaian_jatuhsydney_nilai7= $('#penilaian_jatuhsydney_nilai7').val();
var penilaian_jatuhsydney_skala8= $('#penilaian_jatuhsydney_skala8').val();
var penilaian_jatuhsydney_nilai8= $('#penilaian_jatuhsydney_nilai8').val();
var penilaian_jatuhsydney_skala9= $('#penilaian_jatuhsydney_skala9').val();
var penilaian_jatuhsydney_nilai9= $('#penilaian_jatuhsydney_nilai9').val();
var penilaian_jatuhsydney_skala10= $('#penilaian_jatuhsydney_skala10').val();
var penilaian_jatuhsydney_nilai10= $('#penilaian_jatuhsydney_nilai10').val();
var penilaian_jatuhsydney_skala11= $('#penilaian_jatuhsydney_skala11').val();
var penilaian_jatuhsydney_nilai11= $('#penilaian_jatuhsydney_nilai11').val();
var penilaian_jatuhsydney_totalnilai= $('#penilaian_jatuhsydney_totalnilai').val();
var skrining_gizi1= $('#skrining_gizi1').val();
var nilai_gizi1= $('#nilai_gizi1').val();
var skrining_gizi2= $('#skrining_gizi2').val();
var nilai_gizi2= $('#nilai_gizi2').val();
var nilai_total_gizi= $('#nilai_total_gizi').val();
var skrining_gizi_diagnosa_khusus= $('#skrining_gizi_diagnosa_khusus').val();
var skrining_gizi_diketahui_dietisen= $('#skrining_gizi_diketahui_dietisen').val();
var skrining_gizi_jam_diketahui_dietisen= $('#skrining_gizi_jam_diketahui_dietisen').val();
var rencana= $('#rencana').val();
var nip1= $('#nip1').val();
var nip2= $('#nip2').val();
var kd_dokter= $('#kd_dokter').val();

 var typeact = $('#typeact').val();

 var formData = new FormData(form); // tambahan
 formData.append('typeact', typeact); // tambahan

            $.ajax({
                url: "{?=url([ADMIN,'penilaian_keperawatan_ranap','aksi'])?}",
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
    $('#search_text_mlite_penilaian_awal_keperawatan_ranap').keyup(function () {
        var_tbl_mlite_penilaian_awal_keperawatan_ranap.draw();
    });
    // ==============================================================
    // CLICK TANDA X DI INPUT SEARCH
    // ==============================================================
    $("#searchclear_mlite_penilaian_awal_keperawatan_ranap").click(function () {
        $("#search_text_mlite_penilaian_awal_keperawatan_ranap").val("");
        var_tbl_mlite_penilaian_awal_keperawatan_ranap.draw();
    });

    // ===========================================
    // Ketika tombol Edit di tekan
    // ===========================================

    $("#edit_data_mlite_penilaian_awal_keperawatan_ranap").click(function () {
        var rowData = var_tbl_mlite_penilaian_awal_keperawatan_ranap.rows({ selected: true }).data()[0];
        if (rowData != null) {

            var no_rawat = rowData['no_rawat'];
var tanggal = rowData['tanggal'];
var informasi = rowData['informasi'];
var ket_informasi = rowData['ket_informasi'];
var tiba_diruang_rawat = rowData['tiba_diruang_rawat'];
var kasus_trauma = rowData['kasus_trauma'];
var cara_masuk = rowData['cara_masuk'];
var rps = rowData['rps'];
var rpd = rowData['rpd'];
var rpk = rowData['rpk'];
var rpo = rowData['rpo'];
var riwayat_pembedahan = rowData['riwayat_pembedahan'];
var riwayat_dirawat_dirs = rowData['riwayat_dirawat_dirs'];
var alat_bantu_dipakai = rowData['alat_bantu_dipakai'];
var riwayat_kehamilan = rowData['riwayat_kehamilan'];
var riwayat_kehamilan_perkiraan = rowData['riwayat_kehamilan_perkiraan'];
var riwayat_tranfusi = rowData['riwayat_tranfusi'];
var riwayat_alergi = rowData['riwayat_alergi'];
var riwayat_merokok = rowData['riwayat_merokok'];
var riwayat_merokok_jumlah = rowData['riwayat_merokok_jumlah'];
var riwayat_alkohol = rowData['riwayat_alkohol'];
var riwayat_alkohol_jumlah = rowData['riwayat_alkohol_jumlah'];
var riwayat_narkoba = rowData['riwayat_narkoba'];
var riwayat_olahraga = rowData['riwayat_olahraga'];
var pemeriksaan_mental = rowData['pemeriksaan_mental'];
var pemeriksaan_keadaan_umum = rowData['pemeriksaan_keadaan_umum'];
var pemeriksaan_gcs = rowData['pemeriksaan_gcs'];
var pemeriksaan_td = rowData['pemeriksaan_td'];
var pemeriksaan_nadi = rowData['pemeriksaan_nadi'];
var pemeriksaan_rr = rowData['pemeriksaan_rr'];
var pemeriksaan_suhu = rowData['pemeriksaan_suhu'];
var pemeriksaan_spo2 = rowData['pemeriksaan_spo2'];
var pemeriksaan_bb = rowData['pemeriksaan_bb'];
var pemeriksaan_tb = rowData['pemeriksaan_tb'];
var pemeriksaan_susunan_kepala = rowData['pemeriksaan_susunan_kepala'];
var pemeriksaan_susunan_wajah = rowData['pemeriksaan_susunan_wajah'];
var pemeriksaan_susunan_leher = rowData['pemeriksaan_susunan_leher'];
var pemeriksaan_susunan_kejang = rowData['pemeriksaan_susunan_kejang'];
var pemeriksaan_susunan_sensorik = rowData['pemeriksaan_susunan_sensorik'];
var pemeriksaan_kardiovaskuler_denyut_nadi = rowData['pemeriksaan_kardiovaskuler_denyut_nadi'];
var pemeriksaan_kardiovaskuler_sirkulasi = rowData['pemeriksaan_kardiovaskuler_sirkulasi'];
var pemeriksaan_kardiovaskuler_pulsasi = rowData['pemeriksaan_kardiovaskuler_pulsasi'];
var pemeriksaan_respirasi_pola_nafas = rowData['pemeriksaan_respirasi_pola_nafas'];
var pemeriksaan_respirasi_retraksi = rowData['pemeriksaan_respirasi_retraksi'];
var pemeriksaan_respirasi_suara_nafas = rowData['pemeriksaan_respirasi_suara_nafas'];
var pemeriksaan_respirasi_volume_pernafasan = rowData['pemeriksaan_respirasi_volume_pernafasan'];
var pemeriksaan_respirasi_jenis_pernafasan = rowData['pemeriksaan_respirasi_jenis_pernafasan'];
var pemeriksaan_respirasi_irama_nafas = rowData['pemeriksaan_respirasi_irama_nafas'];
var pemeriksaan_respirasi_batuk = rowData['pemeriksaan_respirasi_batuk'];
var pemeriksaan_gastrointestinal_mulut = rowData['pemeriksaan_gastrointestinal_mulut'];
var pemeriksaan_gastrointestinal_gigi = rowData['pemeriksaan_gastrointestinal_gigi'];
var pemeriksaan_gastrointestinal_lidah = rowData['pemeriksaan_gastrointestinal_lidah'];
var pemeriksaan_gastrointestinal_tenggorokan = rowData['pemeriksaan_gastrointestinal_tenggorokan'];
var pemeriksaan_gastrointestinal_abdomen = rowData['pemeriksaan_gastrointestinal_abdomen'];
var pemeriksaan_gastrointestinal_peistatik_usus = rowData['pemeriksaan_gastrointestinal_peistatik_usus'];
var pemeriksaan_gastrointestinal_anus = rowData['pemeriksaan_gastrointestinal_anus'];
var pemeriksaan_neurologi_pengelihatan = rowData['pemeriksaan_neurologi_pengelihatan'];
var pemeriksaan_neurologi_alat_bantu_penglihatan = rowData['pemeriksaan_neurologi_alat_bantu_penglihatan'];
var pemeriksaan_neurologi_pendengaran = rowData['pemeriksaan_neurologi_pendengaran'];
var pemeriksaan_neurologi_bicara = rowData['pemeriksaan_neurologi_bicara'];
var pemeriksaan_neurologi_sensorik = rowData['pemeriksaan_neurologi_sensorik'];
var pemeriksaan_neurologi_motorik = rowData['pemeriksaan_neurologi_motorik'];
var pemeriksaan_neurologi_kekuatan_otot = rowData['pemeriksaan_neurologi_kekuatan_otot'];
var pemeriksaan_integument_warnakulit = rowData['pemeriksaan_integument_warnakulit'];
var pemeriksaan_integument_turgor = rowData['pemeriksaan_integument_turgor'];
var pemeriksaan_integument_kulit = rowData['pemeriksaan_integument_kulit'];
var pemeriksaan_integument_dekubitas = rowData['pemeriksaan_integument_dekubitas'];
var pemeriksaan_muskuloskletal_pergerakan_sendi = rowData['pemeriksaan_muskuloskletal_pergerakan_sendi'];
var pemeriksaan_muskuloskletal_kekauatan_otot = rowData['pemeriksaan_muskuloskletal_kekauatan_otot'];
var pemeriksaan_muskuloskletal_nyeri_sendi = rowData['pemeriksaan_muskuloskletal_nyeri_sendi'];
var pemeriksaan_muskuloskletal_oedema = rowData['pemeriksaan_muskuloskletal_oedema'];
var pemeriksaan_muskuloskletal_fraktur = rowData['pemeriksaan_muskuloskletal_fraktur'];
var pemeriksaan_eliminasi_bab_frekuensi_jumlah = rowData['pemeriksaan_eliminasi_bab_frekuensi_jumlah'];
var pemeriksaan_eliminasi_bab_frekuensi_durasi = rowData['pemeriksaan_eliminasi_bab_frekuensi_durasi'];
var pemeriksaan_eliminasi_bab_konsistensi = rowData['pemeriksaan_eliminasi_bab_konsistensi'];
var pemeriksaan_eliminasi_bab_warna = rowData['pemeriksaan_eliminasi_bab_warna'];
var pemeriksaan_eliminasi_bak_frekuensi_jumlah = rowData['pemeriksaan_eliminasi_bak_frekuensi_jumlah'];
var pemeriksaan_eliminasi_bak_frekuensi_durasi = rowData['pemeriksaan_eliminasi_bak_frekuensi_durasi'];
var pemeriksaan_eliminasi_bak_warna = rowData['pemeriksaan_eliminasi_bak_warna'];
var pemeriksaan_eliminasi_bak_lainlain = rowData['pemeriksaan_eliminasi_bak_lainlain'];
var pola_aktifitas_makanminum = rowData['pola_aktifitas_makanminum'];
var pola_aktifitas_mandi = rowData['pola_aktifitas_mandi'];
var pola_aktifitas_eliminasi = rowData['pola_aktifitas_eliminasi'];
var pola_aktifitas_berpakaian = rowData['pola_aktifitas_berpakaian'];
var pola_aktifitas_berpindah = rowData['pola_aktifitas_berpindah'];
var pola_nutrisi_frekuesi_makan = rowData['pola_nutrisi_frekuesi_makan'];
var pola_nutrisi_jenis_makanan = rowData['pola_nutrisi_jenis_makanan'];
var pola_nutrisi_porsi_makan = rowData['pola_nutrisi_porsi_makan'];
var pola_tidur_lama_tidur = rowData['pola_tidur_lama_tidur'];
var pola_tidur_gangguan = rowData['pola_tidur_gangguan'];
var pengkajian_fungsi_kemampuan_sehari = rowData['pengkajian_fungsi_kemampuan_sehari'];
var pengkajian_fungsi_aktifitas = rowData['pengkajian_fungsi_aktifitas'];
var pengkajian_fungsi_berjalan = rowData['pengkajian_fungsi_berjalan'];
var pengkajian_fungsi_ambulasi = rowData['pengkajian_fungsi_ambulasi'];
var pengkajian_fungsi_ekstrimitas_atas = rowData['pengkajian_fungsi_ekstrimitas_atas'];
var pengkajian_fungsi_ekstrimitas_bawah = rowData['pengkajian_fungsi_ekstrimitas_bawah'];
var pengkajian_fungsi_menggenggam = rowData['pengkajian_fungsi_menggenggam'];
var pengkajian_fungsi_koordinasi = rowData['pengkajian_fungsi_koordinasi'];
var pengkajian_fungsi_kesimpulan = rowData['pengkajian_fungsi_kesimpulan'];
var riwayat_psiko_kondisi_psiko = rowData['riwayat_psiko_kondisi_psiko'];
var riwayat_psiko_gangguan_jiwa = rowData['riwayat_psiko_gangguan_jiwa'];
var riwayat_psiko_perilaku = rowData['riwayat_psiko_perilaku'];
var riwayat_psiko_hubungan_keluarga = rowData['riwayat_psiko_hubungan_keluarga'];
var riwayat_psiko_tinggal = rowData['riwayat_psiko_tinggal'];
var riwayat_psiko_nilai_kepercayaan = rowData['riwayat_psiko_nilai_kepercayaan'];
var riwayat_psiko_pendidikan_pj = rowData['riwayat_psiko_pendidikan_pj'];
var riwayat_psiko_edukasi_diberikan = rowData['riwayat_psiko_edukasi_diberikan'];
var penilaian_nyeri = rowData['penilaian_nyeri'];
var penilaian_nyeri_penyebab = rowData['penilaian_nyeri_penyebab'];
var penilaian_nyeri_kualitas = rowData['penilaian_nyeri_kualitas'];
var penilaian_nyeri_lokasi = rowData['penilaian_nyeri_lokasi'];
var penilaian_nyeri_menyebar = rowData['penilaian_nyeri_menyebar'];
var penilaian_nyeri_skala = rowData['penilaian_nyeri_skala'];
var penilaian_nyeri_waktu = rowData['penilaian_nyeri_waktu'];
var penilaian_nyeri_hilang = rowData['penilaian_nyeri_hilang'];
var penilaian_nyeri_diberitahukan_dokter = rowData['penilaian_nyeri_diberitahukan_dokter'];
var penilaian_nyeri_jam_diberitahukan_dokter = rowData['penilaian_nyeri_jam_diberitahukan_dokter'];
var penilaian_jatuhmorse_skala1 = rowData['penilaian_jatuhmorse_skala1'];
var penilaian_jatuhmorse_nilai1 = rowData['penilaian_jatuhmorse_nilai1'];
var penilaian_jatuhmorse_skala2 = rowData['penilaian_jatuhmorse_skala2'];
var penilaian_jatuhmorse_nilai2 = rowData['penilaian_jatuhmorse_nilai2'];
var penilaian_jatuhmorse_skala3 = rowData['penilaian_jatuhmorse_skala3'];
var penilaian_jatuhmorse_nilai3 = rowData['penilaian_jatuhmorse_nilai3'];
var penilaian_jatuhmorse_skala4 = rowData['penilaian_jatuhmorse_skala4'];
var penilaian_jatuhmorse_nilai4 = rowData['penilaian_jatuhmorse_nilai4'];
var penilaian_jatuhmorse_skala5 = rowData['penilaian_jatuhmorse_skala5'];
var penilaian_jatuhmorse_nilai5 = rowData['penilaian_jatuhmorse_nilai5'];
var penilaian_jatuhmorse_skala6 = rowData['penilaian_jatuhmorse_skala6'];
var penilaian_jatuhmorse_nilai6 = rowData['penilaian_jatuhmorse_nilai6'];
var penilaian_jatuhmorse_totalnilai = rowData['penilaian_jatuhmorse_totalnilai'];
var penilaian_jatuhsydney_skala1 = rowData['penilaian_jatuhsydney_skala1'];
var penilaian_jatuhsydney_nilai1 = rowData['penilaian_jatuhsydney_nilai1'];
var penilaian_jatuhsydney_skala2 = rowData['penilaian_jatuhsydney_skala2'];
var penilaian_jatuhsydney_nilai2 = rowData['penilaian_jatuhsydney_nilai2'];
var penilaian_jatuhsydney_skala3 = rowData['penilaian_jatuhsydney_skala3'];
var penilaian_jatuhsydney_nilai3 = rowData['penilaian_jatuhsydney_nilai3'];
var penilaian_jatuhsydney_skala4 = rowData['penilaian_jatuhsydney_skala4'];
var penilaian_jatuhsydney_nilai4 = rowData['penilaian_jatuhsydney_nilai4'];
var penilaian_jatuhsydney_skala5 = rowData['penilaian_jatuhsydney_skala5'];
var penilaian_jatuhsydney_nilai5 = rowData['penilaian_jatuhsydney_nilai5'];
var penilaian_jatuhsydney_skala6 = rowData['penilaian_jatuhsydney_skala6'];
var penilaian_jatuhsydney_nilai6 = rowData['penilaian_jatuhsydney_nilai6'];
var penilaian_jatuhsydney_skala7 = rowData['penilaian_jatuhsydney_skala7'];
var penilaian_jatuhsydney_nilai7 = rowData['penilaian_jatuhsydney_nilai7'];
var penilaian_jatuhsydney_skala8 = rowData['penilaian_jatuhsydney_skala8'];
var penilaian_jatuhsydney_nilai8 = rowData['penilaian_jatuhsydney_nilai8'];
var penilaian_jatuhsydney_skala9 = rowData['penilaian_jatuhsydney_skala9'];
var penilaian_jatuhsydney_nilai9 = rowData['penilaian_jatuhsydney_nilai9'];
var penilaian_jatuhsydney_skala10 = rowData['penilaian_jatuhsydney_skala10'];
var penilaian_jatuhsydney_nilai10 = rowData['penilaian_jatuhsydney_nilai10'];
var penilaian_jatuhsydney_skala11 = rowData['penilaian_jatuhsydney_skala11'];
var penilaian_jatuhsydney_nilai11 = rowData['penilaian_jatuhsydney_nilai11'];
var penilaian_jatuhsydney_totalnilai = rowData['penilaian_jatuhsydney_totalnilai'];
var skrining_gizi1 = rowData['skrining_gizi1'];
var nilai_gizi1 = rowData['nilai_gizi1'];
var skrining_gizi2 = rowData['skrining_gizi2'];
var nilai_gizi2 = rowData['nilai_gizi2'];
var nilai_total_gizi = rowData['nilai_total_gizi'];
var skrining_gizi_diagnosa_khusus = rowData['skrining_gizi_diagnosa_khusus'];
var skrining_gizi_diketahui_dietisen = rowData['skrining_gizi_diketahui_dietisen'];
var skrining_gizi_jam_diketahui_dietisen = rowData['skrining_gizi_jam_diketahui_dietisen'];
var rencana = rowData['rencana'];
var nip1 = rowData['nip1'];
var nip2 = rowData['nip2'];
var kd_dokter = rowData['kd_dokter'];



            $("#typeact").val("edit");
  
            $('#no_rawat').val(no_rawat);
$('#tanggal').val(tanggal);
$('#informasi').val(informasi).change();
$('#ket_informasi').val(ket_informasi);
$('#tiba_diruang_rawat').val(tiba_diruang_rawat).change();
$('#kasus_trauma').val(kasus_trauma).change();
$('#cara_masuk').val(cara_masuk).change();
$('#rps').val(rps);
$('#rpd').val(rpd);
$('#rpk').val(rpk);
$('#rpo').val(rpo);
$('#riwayat_pembedahan').val(riwayat_pembedahan);
$('#riwayat_dirawat_dirs').val(riwayat_dirawat_dirs);
$('#alat_bantu_dipakai').val(alat_bantu_dipakai).change();
$('#riwayat_kehamilan').val(riwayat_kehamilan).change();
$('#riwayat_kehamilan_perkiraan').val(riwayat_kehamilan_perkiraan);
$('#riwayat_tranfusi').val(riwayat_tranfusi);
$('#riwayat_alergi').val(riwayat_alergi);
$('#riwayat_merokok').val(riwayat_merokok).change();
$('#riwayat_merokok_jumlah').val(riwayat_merokok_jumlah);
$('#riwayat_alkohol').val(riwayat_alkohol).change();
$('#riwayat_alkohol_jumlah').val(riwayat_alkohol_jumlah);
$('#riwayat_narkoba').val(riwayat_narkoba).change();
$('#riwayat_olahraga').val(riwayat_olahraga).change();
$('#pemeriksaan_mental').val(pemeriksaan_mental);
$('#pemeriksaan_keadaan_umum').val(pemeriksaan_keadaan_umum).change();
$('#pemeriksaan_gcs').val(pemeriksaan_gcs);
$('#pemeriksaan_td').val(pemeriksaan_td);
$('#pemeriksaan_nadi').val(pemeriksaan_nadi);
$('#pemeriksaan_rr').val(pemeriksaan_rr);
$('#pemeriksaan_suhu').val(pemeriksaan_suhu);
$('#pemeriksaan_spo2').val(pemeriksaan_spo2);
$('#pemeriksaan_bb').val(pemeriksaan_bb);
$('#pemeriksaan_tb').val(pemeriksaan_tb);
$('#pemeriksaan_susunan_kepala').val(pemeriksaan_susunan_kepala).change();
$('#pemeriksaan_susunan_wajah').val(pemeriksaan_susunan_wajah).change();
$('#pemeriksaan_susunan_leher').val(pemeriksaan_susunan_leher).change();
$('#pemeriksaan_susunan_kejang').val(pemeriksaan_susunan_kejang).change();
$('#pemeriksaan_susunan_sensorik').val(pemeriksaan_susunan_sensorik).change();
$('#pemeriksaan_kardiovaskuler_denyut_nadi').val(pemeriksaan_kardiovaskuler_denyut_nadi).change();
$('#pemeriksaan_kardiovaskuler_sirkulasi').val(pemeriksaan_kardiovaskuler_sirkulasi).change();
$('#pemeriksaan_kardiovaskuler_pulsasi').val(pemeriksaan_kardiovaskuler_pulsasi).change();
$('#pemeriksaan_respirasi_pola_nafas').val(pemeriksaan_respirasi_pola_nafas).change();
$('#pemeriksaan_respirasi_retraksi').val(pemeriksaan_respirasi_retraksi).change();
$('#pemeriksaan_respirasi_suara_nafas').val(pemeriksaan_respirasi_suara_nafas).change();
$('#pemeriksaan_respirasi_volume_pernafasan').val(pemeriksaan_respirasi_volume_pernafasan).change();
$('#pemeriksaan_respirasi_jenis_pernafasan').val(pemeriksaan_respirasi_jenis_pernafasan).change();
$('#pemeriksaan_respirasi_irama_nafas').val(pemeriksaan_respirasi_irama_nafas).change();
$('#pemeriksaan_respirasi_batuk').val(pemeriksaan_respirasi_batuk).change();
$('#pemeriksaan_gastrointestinal_mulut').val(pemeriksaan_gastrointestinal_mulut).change();
$('#pemeriksaan_gastrointestinal_gigi').val(pemeriksaan_gastrointestinal_gigi).change();
$('#pemeriksaan_gastrointestinal_lidah').val(pemeriksaan_gastrointestinal_lidah).change();
$('#pemeriksaan_gastrointestinal_tenggorokan').val(pemeriksaan_gastrointestinal_tenggorokan).change();
$('#pemeriksaan_gastrointestinal_abdomen').val(pemeriksaan_gastrointestinal_abdomen).change();
$('#pemeriksaan_gastrointestinal_peistatik_usus').val(pemeriksaan_gastrointestinal_peistatik_usus).change();
$('#pemeriksaan_gastrointestinal_anus').val(pemeriksaan_gastrointestinal_anus).change();
$('#pemeriksaan_neurologi_pengelihatan').val(pemeriksaan_neurologi_pengelihatan).change();
$('#pemeriksaan_neurologi_alat_bantu_penglihatan').val(pemeriksaan_neurologi_alat_bantu_penglihatan).change();
$('#pemeriksaan_neurologi_pendengaran').val(pemeriksaan_neurologi_pendengaran).change();
$('#pemeriksaan_neurologi_bicara').val(pemeriksaan_neurologi_bicara).change();
$('#pemeriksaan_neurologi_sensorik').val(pemeriksaan_neurologi_sensorik).change();
$('#pemeriksaan_neurologi_motorik').val(pemeriksaan_neurologi_motorik).change();
$('#pemeriksaan_neurologi_kekuatan_otot').val(pemeriksaan_neurologi_kekuatan_otot).change();
$('#pemeriksaan_integument_warnakulit').val(pemeriksaan_integument_warnakulit).change();
$('#pemeriksaan_integument_turgor').val(pemeriksaan_integument_turgor).change();
$('#pemeriksaan_integument_kulit').val(pemeriksaan_integument_kulit).change();
$('#pemeriksaan_integument_dekubitas').val(pemeriksaan_integument_dekubitas).change();
$('#pemeriksaan_muskuloskletal_pergerakan_sendi').val(pemeriksaan_muskuloskletal_pergerakan_sendi).change();
$('#pemeriksaan_muskuloskletal_kekauatan_otot').val(pemeriksaan_muskuloskletal_kekauatan_otot).change();
$('#pemeriksaan_muskuloskletal_nyeri_sendi').val(pemeriksaan_muskuloskletal_nyeri_sendi).change();
$('#pemeriksaan_muskuloskletal_oedema').val(pemeriksaan_muskuloskletal_oedema).change();
$('#pemeriksaan_muskuloskletal_fraktur').val(pemeriksaan_muskuloskletal_fraktur).change();
$('#pemeriksaan_eliminasi_bab_frekuensi_jumlah').val(pemeriksaan_eliminasi_bab_frekuensi_jumlah);
$('#pemeriksaan_eliminasi_bab_frekuensi_durasi').val(pemeriksaan_eliminasi_bab_frekuensi_durasi);
$('#pemeriksaan_eliminasi_bab_konsistensi').val(pemeriksaan_eliminasi_bab_konsistensi);
$('#pemeriksaan_eliminasi_bab_warna').val(pemeriksaan_eliminasi_bab_warna);
$('#pemeriksaan_eliminasi_bak_frekuensi_jumlah').val(pemeriksaan_eliminasi_bak_frekuensi_jumlah);
$('#pemeriksaan_eliminasi_bak_frekuensi_durasi').val(pemeriksaan_eliminasi_bak_frekuensi_durasi);
$('#pemeriksaan_eliminasi_bak_warna').val(pemeriksaan_eliminasi_bak_warna);
$('#pemeriksaan_eliminasi_bak_lainlain').val(pemeriksaan_eliminasi_bak_lainlain);
$('#pola_aktifitas_makanminum').val(pola_aktifitas_makanminum).change();
$('#pola_aktifitas_mandi').val(pola_aktifitas_mandi).change();
$('#pola_aktifitas_eliminasi').val(pola_aktifitas_eliminasi).change();
$('#pola_aktifitas_berpakaian').val(pola_aktifitas_berpakaian).change();
$('#pola_aktifitas_berpindah').val(pola_aktifitas_berpindah).change();
$('#pola_nutrisi_frekuesi_makan').val(pola_nutrisi_frekuesi_makan);
$('#pola_nutrisi_jenis_makanan').val(pola_nutrisi_jenis_makanan);
$('#pola_nutrisi_porsi_makan').val(pola_nutrisi_porsi_makan);
$('#pola_tidur_lama_tidur').val(pola_tidur_lama_tidur);
$('#pola_tidur_gangguan').val(pola_tidur_gangguan).change();
$('#pengkajian_fungsi_kemampuan_sehari').val(pengkajian_fungsi_kemampuan_sehari).change();
$('#pengkajian_fungsi_aktifitas').val(pengkajian_fungsi_aktifitas).change();
$('#pengkajian_fungsi_berjalan').val(pengkajian_fungsi_berjalan).change();
$('#pengkajian_fungsi_ambulasi').val(pengkajian_fungsi_ambulasi).change();
$('#pengkajian_fungsi_ekstrimitas_atas').val(pengkajian_fungsi_ekstrimitas_atas).change();
$('#pengkajian_fungsi_ekstrimitas_bawah').val(pengkajian_fungsi_ekstrimitas_bawah).change();
$('#pengkajian_fungsi_menggenggam').val(pengkajian_fungsi_menggenggam).change();
$('#pengkajian_fungsi_koordinasi').val(pengkajian_fungsi_koordinasi).change();
$('#pengkajian_fungsi_kesimpulan').val(pengkajian_fungsi_kesimpulan).change();
$('#riwayat_psiko_kondisi_psiko').val(riwayat_psiko_kondisi_psiko).change();
$('#riwayat_psiko_gangguan_jiwa').val(riwayat_psiko_gangguan_jiwa).change();
$('#riwayat_psiko_perilaku').val(riwayat_psiko_perilaku).change();
$('#riwayat_psiko_hubungan_keluarga').val(riwayat_psiko_hubungan_keluarga).change();
$('#riwayat_psiko_tinggal').val(riwayat_psiko_tinggal).change();
$('#riwayat_psiko_nilai_kepercayaan').val(riwayat_psiko_nilai_kepercayaan).change();
$('#riwayat_psiko_pendidikan_pj').val(riwayat_psiko_pendidikan_pj).change();
$('#riwayat_psiko_edukasi_diberikan').val(riwayat_psiko_edukasi_diberikan).change();
$('#penilaian_nyeri').val(penilaian_nyeri).change();
$('#penilaian_nyeri_penyebab').val(penilaian_nyeri_penyebab).change();
$('#penilaian_nyeri_kualitas').val(penilaian_nyeri_kualitas).change();
$('#penilaian_nyeri_lokasi').val(penilaian_nyeri_lokasi);
$('#penilaian_nyeri_menyebar').val(penilaian_nyeri_menyebar).change();
$('#penilaian_nyeri_skala').val(penilaian_nyeri_skala).change();
$('#penilaian_nyeri_waktu').val(penilaian_nyeri_waktu);
$('#penilaian_nyeri_hilang').val(penilaian_nyeri_hilang).change();
$('#penilaian_nyeri_diberitahukan_dokter').val(penilaian_nyeri_diberitahukan_dokter).change();
$('#penilaian_nyeri_jam_diberitahukan_dokter').val(penilaian_nyeri_jam_diberitahukan_dokter);
$('#penilaian_jatuhmorse_skala1').val(penilaian_jatuhmorse_skala1).change();
$('#penilaian_jatuhmorse_nilai1').val(penilaian_jatuhmorse_nilai1);
$('#penilaian_jatuhmorse_skala2').val(penilaian_jatuhmorse_skala2).change();
$('#penilaian_jatuhmorse_nilai2').val(penilaian_jatuhmorse_nilai2);
$('#penilaian_jatuhmorse_skala3').val(penilaian_jatuhmorse_skala3).change();
$('#penilaian_jatuhmorse_nilai3').val(penilaian_jatuhmorse_nilai3);
$('#penilaian_jatuhmorse_skala4').val(penilaian_jatuhmorse_skala4).change();
$('#penilaian_jatuhmorse_nilai4').val(penilaian_jatuhmorse_nilai4);
$('#penilaian_jatuhmorse_skala5').val(penilaian_jatuhmorse_skala5).change();
$('#penilaian_jatuhmorse_nilai5').val(penilaian_jatuhmorse_nilai5);
$('#penilaian_jatuhmorse_skala6').val(penilaian_jatuhmorse_skala6).change();
$('#penilaian_jatuhmorse_nilai6').val(penilaian_jatuhmorse_nilai6);
$('#penilaian_jatuhmorse_totalnilai').val(penilaian_jatuhmorse_totalnilai);
$('#penilaian_jatuhsydney_skala1').val(penilaian_jatuhsydney_skala1).change();
$('#penilaian_jatuhsydney_nilai1').val(penilaian_jatuhsydney_nilai1);
$('#penilaian_jatuhsydney_skala2').val(penilaian_jatuhsydney_skala2).change();
$('#penilaian_jatuhsydney_nilai2').val(penilaian_jatuhsydney_nilai2);
$('#penilaian_jatuhsydney_skala3').val(penilaian_jatuhsydney_skala3).change();
$('#penilaian_jatuhsydney_nilai3').val(penilaian_jatuhsydney_nilai3);
$('#penilaian_jatuhsydney_skala4').val(penilaian_jatuhsydney_skala4).change();
$('#penilaian_jatuhsydney_nilai4').val(penilaian_jatuhsydney_nilai4);
$('#penilaian_jatuhsydney_skala5').val(penilaian_jatuhsydney_skala5).change();
$('#penilaian_jatuhsydney_nilai5').val(penilaian_jatuhsydney_nilai5);
$('#penilaian_jatuhsydney_skala6').val(penilaian_jatuhsydney_skala6).change();
$('#penilaian_jatuhsydney_nilai6').val(penilaian_jatuhsydney_nilai6);
$('#penilaian_jatuhsydney_skala7').val(penilaian_jatuhsydney_skala7).change();
$('#penilaian_jatuhsydney_nilai7').val(penilaian_jatuhsydney_nilai7);
$('#penilaian_jatuhsydney_skala8').val(penilaian_jatuhsydney_skala8).change();
$('#penilaian_jatuhsydney_nilai8').val(penilaian_jatuhsydney_nilai8);
$('#penilaian_jatuhsydney_skala9').val(penilaian_jatuhsydney_skala9).change();
$('#penilaian_jatuhsydney_nilai9').val(penilaian_jatuhsydney_nilai9);
$('#penilaian_jatuhsydney_skala10').val(penilaian_jatuhsydney_skala10).change();
$('#penilaian_jatuhsydney_nilai10').val(penilaian_jatuhsydney_nilai10);
$('#penilaian_jatuhsydney_skala11').val(penilaian_jatuhsydney_skala11).change();
$('#penilaian_jatuhsydney_nilai11').val(penilaian_jatuhsydney_nilai11);
$('#penilaian_jatuhsydney_totalnilai').val(penilaian_jatuhsydney_totalnilai);
$('#skrining_gizi1').val(skrining_gizi1).change();
$('#nilai_gizi1').val(nilai_gizi1);
$('#skrining_gizi2').val(skrining_gizi2).change();
$('#nilai_gizi2').val(nilai_gizi2);
$('#nilai_total_gizi').val(nilai_total_gizi);
$('#skrining_gizi_diagnosa_khusus').val(skrining_gizi_diagnosa_khusus).change();
$('#skrining_gizi_diketahui_dietisen').val(skrining_gizi_diketahui_dietisen).change();
$('#skrining_gizi_jam_diketahui_dietisen').val(skrining_gizi_jam_diketahui_dietisen);
$('#rencana').val(rencana);
$('#nip1').val(nip1);
$('#nip2').val(nip2);
$('#kd_dokter').val(kd_dokter).change();

            //$("#no_rawat").prop('disabled', true); // GA BISA DIEDIT KALI DISABLE
            $('#modal-title').text("Edit Data Penilaian Keperawatan Ranap");
            $("#modal_mlite_penilaian_awal_keperawatan_ranap").modal();
        }
        else {
            alert("Silakan pilih data yang akan di edit.");
        }

    });

    // ==============================================================
    // TOMBOL  DELETE DI CLICK
    // ==============================================================
    jQuery("#hapus_data_mlite_penilaian_awal_keperawatan_ranap").click(function () {
        var rowData = var_tbl_mlite_penilaian_awal_keperawatan_ranap.rows({ selected: true }).data()[0];


        if (rowData) {
var no_rawat = rowData['no_rawat'];
            var a = confirm("Anda yakin akan menghapus data dengan no_rawat=" + no_rawat);
            if (a) {

                $.ajax({
                    url: "{?=url([ADMIN,'penilaian_keperawatan_ranap','aksi'])?}",
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

    if(window.location.search.indexOf('no_rawat') !== -1) { 
        let searchParams = new URLSearchParams(window.location.search)
        $('#search_text_mlite_penilaian_awal_keperawatan_ranap').val(searchParams.get('no_rawat'));
        var_tbl_mlite_penilaian_awal_keperawatan_ranap.draw();
        if(searchParams.get('modal') == 'true') {
            $("#modal_mlite_penilaian_awal_keperawatan_ranap").modal();
            $('#no_rawat').val(searchParams.get('no_rawat'));    
        }
    }

    jQuery("#tambah_data_mlite_penilaian_awal_keperawatan_ranap").click(function () {

        $('#no_rawat').val('');

        if(window.location.search.indexOf('no_rawat') !== -1) { 
            $('#no_rawat').val(searchParams.get('no_rawat'));
        }

$('#tanggal').val('');
$('#informasi').val('');
$('#ket_informasi').val('');
$('#tiba_diruang_rawat').val('');
$('#kasus_trauma').val('');
$('#cara_masuk').val('');
$('#rps').val('');
$('#rpd').val('');
$('#rpk').val('');
$('#rpo').val('');
$('#riwayat_pembedahan').val('');
$('#riwayat_dirawat_dirs').val('');
$('#alat_bantu_dipakai').val('');
$('#riwayat_kehamilan').val('');
$('#riwayat_kehamilan_perkiraan').val('');
$('#riwayat_tranfusi').val('');
$('#riwayat_alergi').val('');
$('#riwayat_merokok').val('');
$('#riwayat_merokok_jumlah').val('');
$('#riwayat_alkohol').val('');
$('#riwayat_alkohol_jumlah').val('');
$('#riwayat_narkoba').val('');
$('#riwayat_olahraga').val('');
$('#pemeriksaan_mental').val('');
$('#pemeriksaan_keadaan_umum').val('');
$('#pemeriksaan_gcs').val('');
$('#pemeriksaan_td').val('');
$('#pemeriksaan_nadi').val('');
$('#pemeriksaan_rr').val('');
$('#pemeriksaan_suhu').val('');
$('#pemeriksaan_spo2').val('');
$('#pemeriksaan_bb').val('');
$('#pemeriksaan_tb').val('');
$('#pemeriksaan_susunan_kepala').val('');
$('#pemeriksaan_susunan_wajah').val('');
$('#pemeriksaan_susunan_leher').val('');
$('#pemeriksaan_susunan_kejang').val('');
$('#pemeriksaan_susunan_sensorik').val('');
$('#pemeriksaan_kardiovaskuler_denyut_nadi').val('');
$('#pemeriksaan_kardiovaskuler_sirkulasi').val('');
$('#pemeriksaan_kardiovaskuler_pulsasi').val('');
$('#pemeriksaan_respirasi_pola_nafas').val('');
$('#pemeriksaan_respirasi_retraksi').val('');
$('#pemeriksaan_respirasi_suara_nafas').val('');
$('#pemeriksaan_respirasi_volume_pernafasan').val('');
$('#pemeriksaan_respirasi_jenis_pernafasan').val('');
$('#pemeriksaan_respirasi_irama_nafas').val('');
$('#pemeriksaan_respirasi_batuk').val('');
$('#pemeriksaan_gastrointestinal_mulut').val('');
$('#pemeriksaan_gastrointestinal_gigi').val('');
$('#pemeriksaan_gastrointestinal_lidah').val('');
$('#pemeriksaan_gastrointestinal_tenggorokan').val('');
$('#pemeriksaan_gastrointestinal_abdomen').val('');
$('#pemeriksaan_gastrointestinal_peistatik_usus').val('');
$('#pemeriksaan_gastrointestinal_anus').val('');
$('#pemeriksaan_neurologi_pengelihatan').val('');
$('#pemeriksaan_neurologi_alat_bantu_penglihatan').val('');
$('#pemeriksaan_neurologi_pendengaran').val('');
$('#pemeriksaan_neurologi_bicara').val('');
$('#pemeriksaan_neurologi_sensorik').val('');
$('#pemeriksaan_neurologi_motorik').val('');
$('#pemeriksaan_neurologi_kekuatan_otot').val('');
$('#pemeriksaan_integument_warnakulit').val('');
$('#pemeriksaan_integument_turgor').val('');
$('#pemeriksaan_integument_kulit').val('');
$('#pemeriksaan_integument_dekubitas').val('');
$('#pemeriksaan_muskuloskletal_pergerakan_sendi').val('');
$('#pemeriksaan_muskuloskletal_kekauatan_otot').val('');
$('#pemeriksaan_muskuloskletal_nyeri_sendi').val('');
$('#pemeriksaan_muskuloskletal_oedema').val('');
$('#pemeriksaan_muskuloskletal_fraktur').val('');
$('#pemeriksaan_eliminasi_bab_frekuensi_jumlah').val('');
$('#pemeriksaan_eliminasi_bab_frekuensi_durasi').val('');
$('#pemeriksaan_eliminasi_bab_konsistensi').val('');
$('#pemeriksaan_eliminasi_bab_warna').val('');
$('#pemeriksaan_eliminasi_bak_frekuensi_jumlah').val('');
$('#pemeriksaan_eliminasi_bak_frekuensi_durasi').val('');
$('#pemeriksaan_eliminasi_bak_warna').val('');
$('#pemeriksaan_eliminasi_bak_lainlain').val('');
$('#pola_aktifitas_makanminum').val('');
$('#pola_aktifitas_mandi').val('');
$('#pola_aktifitas_eliminasi').val('');
$('#pola_aktifitas_berpakaian').val('');
$('#pola_aktifitas_berpindah').val('');
$('#pola_nutrisi_frekuesi_makan').val('');
$('#pola_nutrisi_jenis_makanan').val('');
$('#pola_nutrisi_porsi_makan').val('');
$('#pola_tidur_lama_tidur').val('');
$('#pola_tidur_gangguan').val('');
$('#pengkajian_fungsi_kemampuan_sehari').val('');
$('#pengkajian_fungsi_aktifitas').val('');
$('#pengkajian_fungsi_berjalan').val('');
$('#pengkajian_fungsi_ambulasi').val('');
$('#pengkajian_fungsi_ekstrimitas_atas').val('');
$('#pengkajian_fungsi_ekstrimitas_bawah').val('');
$('#pengkajian_fungsi_menggenggam').val('');
$('#pengkajian_fungsi_koordinasi').val('');
$('#pengkajian_fungsi_kesimpulan').val('');
$('#riwayat_psiko_kondisi_psiko').val('');
$('#riwayat_psiko_gangguan_jiwa').val('');
$('#riwayat_psiko_perilaku').val('');
$('#riwayat_psiko_hubungan_keluarga').val('');
$('#riwayat_psiko_tinggal').val('');
$('#riwayat_psiko_nilai_kepercayaan').val('');
$('#riwayat_psiko_pendidikan_pj').val('');
$('#riwayat_psiko_edukasi_diberikan').val('');
$('#penilaian_nyeri').val('');
$('#penilaian_nyeri_penyebab').val('');
$('#penilaian_nyeri_kualitas').val('');
$('#penilaian_nyeri_lokasi').val('');
$('#penilaian_nyeri_menyebar').val('');
$('#penilaian_nyeri_skala').val('');
$('#penilaian_nyeri_waktu').val('');
$('#penilaian_nyeri_hilang').val('');
$('#penilaian_nyeri_diberitahukan_dokter').val('');
$('#penilaian_nyeri_jam_diberitahukan_dokter').val('');
$('#penilaian_jatuhmorse_skala1').val('');
$('#penilaian_jatuhmorse_nilai1').val('');
$('#penilaian_jatuhmorse_skala2').val('');
$('#penilaian_jatuhmorse_nilai2').val('');
$('#penilaian_jatuhmorse_skala3').val('');
$('#penilaian_jatuhmorse_nilai3').val('');
$('#penilaian_jatuhmorse_skala4').val('');
$('#penilaian_jatuhmorse_nilai4').val('');
$('#penilaian_jatuhmorse_skala5').val('');
$('#penilaian_jatuhmorse_nilai5').val('');
$('#penilaian_jatuhmorse_skala6').val('');
$('#penilaian_jatuhmorse_nilai6').val('');
$('#penilaian_jatuhmorse_totalnilai').val('');
$('#penilaian_jatuhsydney_skala1').val('');
$('#penilaian_jatuhsydney_nilai1').val('');
$('#penilaian_jatuhsydney_skala2').val('');
$('#penilaian_jatuhsydney_nilai2').val('');
$('#penilaian_jatuhsydney_skala3').val('');
$('#penilaian_jatuhsydney_nilai3').val('');
$('#penilaian_jatuhsydney_skala4').val('');
$('#penilaian_jatuhsydney_nilai4').val('');
$('#penilaian_jatuhsydney_skala5').val('');
$('#penilaian_jatuhsydney_nilai5').val('');
$('#penilaian_jatuhsydney_skala6').val('');
$('#penilaian_jatuhsydney_nilai6').val('');
$('#penilaian_jatuhsydney_skala7').val('');
$('#penilaian_jatuhsydney_nilai7').val('');
$('#penilaian_jatuhsydney_skala8').val('');
$('#penilaian_jatuhsydney_nilai8').val('');
$('#penilaian_jatuhsydney_skala9').val('');
$('#penilaian_jatuhsydney_nilai9').val('');
$('#penilaian_jatuhsydney_skala10').val('');
$('#penilaian_jatuhsydney_nilai10').val('');
$('#penilaian_jatuhsydney_skala11').val('');
$('#penilaian_jatuhsydney_nilai11').val('');
$('#penilaian_jatuhsydney_totalnilai').val('');
$('#skrining_gizi1').val('');
$('#nilai_gizi1').val('');
$('#skrining_gizi2').val('');
$('#nilai_gizi2').val('');
$('#nilai_total_gizi').val('');
$('#skrining_gizi_diagnosa_khusus').val('');
$('#skrining_gizi_diketahui_dietisen').val('');
$('#skrining_gizi_jam_diketahui_dietisen').val('');
$('#rencana').val('');
$('#nip1').val('{?=$this->core->getUserInfo('username', null, true)?}');
$('#nip2').val('{?=$this->core->getUserInfo('username', null, true)?}');
$('#kd_dokter').val('');


        $("#typeact").val("add");
        $("#no_rawat").prop('disabled', false);
        
        $('#modal-title').text("Tambah Data Penilaian Keperawatan Ranap");
        $("#modal_mlite_penilaian_awal_keperawatan_ranap").modal();
    });

    // ===========================================
    // Ketika tombol lihat data di tekan
    // ===========================================
    $("#lihat_data_mlite_penilaian_awal_keperawatan_ranap").click(function () {

        var search_field_mlite_penilaian_awal_keperawatan_ranap = $('#search_field_mlite_penilaian_awal_keperawatan_ranap').val();
        var search_text_mlite_penilaian_awal_keperawatan_ranap = $('#search_text_mlite_penilaian_awal_keperawatan_ranap').val();

        $.ajax({
            url: "{?=url([ADMIN,'penilaian_keperawatan_ranap','aksi'])?}",
            method: "POST",
            data: {
                typeact: 'lihat', 
                search_field_mlite_penilaian_awal_keperawatan_ranap: search_field_mlite_penilaian_awal_keperawatan_ranap, 
                search_text_mlite_penilaian_awal_keperawatan_ranap: search_text_mlite_penilaian_awal_keperawatan_ranap
            },
            dataType: 'json',
            success: function (res) {
                var eTable = "<div class='table-responsive'><table id='tbl_lihat_mlite_penilaian_awal_keperawatan_ranap' class='table display dataTable' style='width:100%'><thead><th>No Rawat</th><th>Tanggal</th><th>Informasi</th><th>Ket Informasi</th><th>Tiba Diruang Rawat</th><th>Kasus Trauma</th><th>Cara Masuk</th><th>Rps</th><th>Rpd</th><th>Rpk</th><th>Rpo</th><th>Riwayat Pembedahan</th><th>Riwayat Dirawat Dirs</th><th>Alat Bantu Dipakai</th><th>Riwayat Kehamilan</th><th>Riwayat Kehamilan Perkiraan</th><th>Riwayat Tranfusi</th><th>Riwayat Alergi</th><th>Riwayat Merokok</th><th>Riwayat Merokok Jumlah</th><th>Riwayat Alkohol</th><th>Riwayat Alkohol Jumlah</th><th>Riwayat Narkoba</th><th>Riwayat Olahraga</th><th>Pemeriksaan Mental</th><th>Pemeriksaan Keadaan Umum</th><th>Pemeriksaan Gcs</th><th>Pemeriksaan Td</th><th>Pemeriksaan Nadi</th><th>Pemeriksaan Rr</th><th>Pemeriksaan Suhu</th><th>Pemeriksaan Spo2</th><th>Pemeriksaan Bb</th><th>Pemeriksaan Tb</th><th>Pemeriksaan Susunan Kepala</th><th>Pemeriksaan Susunan Wajah</th><th>Pemeriksaan Susunan Leher</th><th>Pemeriksaan Susunan Kejang</th><th>Pemeriksaan Susunan Sensorik</th><th>Pemeriksaan Kardiovaskuler Denyut Nadi</th><th>Pemeriksaan Kardiovaskuler Sirkulasi</th><th>Pemeriksaan Kardiovaskuler Pulsasi</th><th>Pemeriksaan Respirasi Pola Nafas</th><th>Pemeriksaan Respirasi Retraksi</th><th>Pemeriksaan Respirasi Suara Nafas</th><th>Pemeriksaan Respirasi Volume Pernafasan</th><th>Pemeriksaan Respirasi Jenis Pernafasan</th><th>Pemeriksaan Respirasi Irama Nafas</th><th>Pemeriksaan Respirasi Batuk</th><th>Pemeriksaan Gastrointestinal Mulut</th><th>Pemeriksaan Gastrointestinal Gigi</th><th>Pemeriksaan Gastrointestinal Lidah</th><th>Pemeriksaan Gastrointestinal Tenggorokan</th><th>Pemeriksaan Gastrointestinal Abdomen</th><th>Pemeriksaan Gastrointestinal Peistatik Usus</th><th>Pemeriksaan Gastrointestinal Anus</th><th>Pemeriksaan Neurologi Pengelihatan</th><th>Pemeriksaan Neurologi Alat Bantu Penglihatan</th><th>Pemeriksaan Neurologi Pendengaran</th><th>Pemeriksaan Neurologi Bicara</th><th>Pemeriksaan Neurologi Sensorik</th><th>Pemeriksaan Neurologi Motorik</th><th>Pemeriksaan Neurologi Kekuatan Otot</th><th>Pemeriksaan Integument Warnakulit</th><th>Pemeriksaan Integument Turgor</th><th>Pemeriksaan Integument Kulit</th><th>Pemeriksaan Integument Dekubitas</th><th>Pemeriksaan Muskuloskletal Pergerakan Sendi</th><th>Pemeriksaan Muskuloskletal Kekauatan Otot</th><th>Pemeriksaan Muskuloskletal Nyeri Sendi</th><th>Pemeriksaan Muskuloskletal Oedema</th><th>Pemeriksaan Muskuloskletal Fraktur</th><th>Pemeriksaan Eliminasi Bab Frekuensi Jumlah</th><th>Pemeriksaan Eliminasi Bab Frekuensi Durasi</th><th>Pemeriksaan Eliminasi Bab Konsistensi</th><th>Pemeriksaan Eliminasi Bab Warna</th><th>Pemeriksaan Eliminasi Bak Frekuensi Jumlah</th><th>Pemeriksaan Eliminasi Bak Frekuensi Durasi</th><th>Pemeriksaan Eliminasi Bak Warna</th><th>Pemeriksaan Eliminasi Bak Lainlain</th><th>Pola Aktifitas Makanminum</th><th>Pola Aktifitas Mandi</th><th>Pola Aktifitas Eliminasi</th><th>Pola Aktifitas Berpakaian</th><th>Pola Aktifitas Berpindah</th><th>Pola Nutrisi Frekuesi Makan</th><th>Pola Nutrisi Jenis Makanan</th><th>Pola Nutrisi Porsi Makan</th><th>Pola Tidur Lama Tidur</th><th>Pola Tidur Gangguan</th><th>Pengkajian Fungsi Kemampuan Sehari</th><th>Pengkajian Fungsi Aktifitas</th><th>Pengkajian Fungsi Berjalan</th><th>Pengkajian Fungsi Ambulasi</th><th>Pengkajian Fungsi Ekstrimitas Atas</th><th>Pengkajian Fungsi Ekstrimitas Bawah</th><th>Pengkajian Fungsi Menggenggam</th><th>Pengkajian Fungsi Koordinasi</th><th>Pengkajian Fungsi Kesimpulan</th><th>Riwayat Psiko Kondisi Psiko</th><th>Riwayat Psiko Gangguan Jiwa</th><th>Riwayat Psiko Perilaku</th><th>Riwayat Psiko Hubungan Keluarga</th><th>Riwayat Psiko Tinggal</th><th>Riwayat Psiko Nilai Kepercayaan</th><th>Riwayat Psiko Pendidikan Pj</th><th>Riwayat Psiko Edukasi Diberikan</th><th>Penilaian Nyeri</th><th>Penilaian Nyeri Penyebab</th><th>Penilaian Nyeri Kualitas</th><th>Penilaian Nyeri Lokasi</th><th>Penilaian Nyeri Menyebar</th><th>Penilaian Nyeri Skala</th><th>Penilaian Nyeri Waktu</th><th>Penilaian Nyeri Hilang</th><th>Penilaian Nyeri Diberitahukan Dokter</th><th>Penilaian Nyeri Jam Diberitahukan Dokter</th><th>Penilaian Jatuhmorse Skala1</th><th>Penilaian Jatuhmorse Nilai1</th><th>Penilaian Jatuhmorse Skala2</th><th>Penilaian Jatuhmorse Nilai2</th><th>Penilaian Jatuhmorse Skala3</th><th>Penilaian Jatuhmorse Nilai3</th><th>Penilaian Jatuhmorse Skala4</th><th>Penilaian Jatuhmorse Nilai4</th><th>Penilaian Jatuhmorse Skala5</th><th>Penilaian Jatuhmorse Nilai5</th><th>Penilaian Jatuhmorse Skala6</th><th>Penilaian Jatuhmorse Nilai6</th><th>Penilaian Jatuhmorse Totalnilai</th><th>Penilaian Jatuhsydney Skala1</th><th>Penilaian Jatuhsydney Nilai1</th><th>Penilaian Jatuhsydney Skala2</th><th>Penilaian Jatuhsydney Nilai2</th><th>Penilaian Jatuhsydney Skala3</th><th>Penilaian Jatuhsydney Nilai3</th><th>Penilaian Jatuhsydney Skala4</th><th>Penilaian Jatuhsydney Nilai4</th><th>Penilaian Jatuhsydney Skala5</th><th>Penilaian Jatuhsydney Nilai5</th><th>Penilaian Jatuhsydney Skala6</th><th>Penilaian Jatuhsydney Nilai6</th><th>Penilaian Jatuhsydney Skala7</th><th>Penilaian Jatuhsydney Nilai7</th><th>Penilaian Jatuhsydney Skala8</th><th>Penilaian Jatuhsydney Nilai8</th><th>Penilaian Jatuhsydney Skala9</th><th>Penilaian Jatuhsydney Nilai9</th><th>Penilaian Jatuhsydney Skala10</th><th>Penilaian Jatuhsydney Nilai10</th><th>Penilaian Jatuhsydney Skala11</th><th>Penilaian Jatuhsydney Nilai11</th><th>Penilaian Jatuhsydney Totalnilai</th><th>Skrining Gizi1</th><th>Nilai Gizi1</th><th>Skrining Gizi2</th><th>Nilai Gizi2</th><th>Nilai Total Gizi</th><th>Skrining Gizi Diagnosa Khusus</th><th>Skrining Gizi Diketahui Dietisen</th><th>Skrining Gizi Jam Diketahui Dietisen</th><th>Rencana</th><th>Nip1</th><th>Nip2</th><th>Kd Dokter</th></thead>";
                for (var i = 0; i < res.length; i++) {
                    eTable += "<tr>";
                    eTable += '<td>' + res[i]['no_rawat'] + '</td>';
eTable += '<td>' + res[i]['tanggal'] + '</td>';
eTable += '<td>' + res[i]['informasi'] + '</td>';
eTable += '<td>' + res[i]['ket_informasi'] + '</td>';
eTable += '<td>' + res[i]['tiba_diruang_rawat'] + '</td>';
eTable += '<td>' + res[i]['kasus_trauma'] + '</td>';
eTable += '<td>' + res[i]['cara_masuk'] + '</td>';
eTable += '<td>' + res[i]['rps'] + '</td>';
eTable += '<td>' + res[i]['rpd'] + '</td>';
eTable += '<td>' + res[i]['rpk'] + '</td>';
eTable += '<td>' + res[i]['rpo'] + '</td>';
eTable += '<td>' + res[i]['riwayat_pembedahan'] + '</td>';
eTable += '<td>' + res[i]['riwayat_dirawat_dirs'] + '</td>';
eTable += '<td>' + res[i]['alat_bantu_dipakai'] + '</td>';
eTable += '<td>' + res[i]['riwayat_kehamilan'] + '</td>';
eTable += '<td>' + res[i]['riwayat_kehamilan_perkiraan'] + '</td>';
eTable += '<td>' + res[i]['riwayat_tranfusi'] + '</td>';
eTable += '<td>' + res[i]['riwayat_alergi'] + '</td>';
eTable += '<td>' + res[i]['riwayat_merokok'] + '</td>';
eTable += '<td>' + res[i]['riwayat_merokok_jumlah'] + '</td>';
eTable += '<td>' + res[i]['riwayat_alkohol'] + '</td>';
eTable += '<td>' + res[i]['riwayat_alkohol_jumlah'] + '</td>';
eTable += '<td>' + res[i]['riwayat_narkoba'] + '</td>';
eTable += '<td>' + res[i]['riwayat_olahraga'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_mental'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_keadaan_umum'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_gcs'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_td'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_nadi'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_rr'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_suhu'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_spo2'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_bb'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_tb'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_susunan_kepala'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_susunan_wajah'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_susunan_leher'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_susunan_kejang'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_susunan_sensorik'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_kardiovaskuler_denyut_nadi'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_kardiovaskuler_sirkulasi'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_kardiovaskuler_pulsasi'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_respirasi_pola_nafas'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_respirasi_retraksi'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_respirasi_suara_nafas'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_respirasi_volume_pernafasan'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_respirasi_jenis_pernafasan'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_respirasi_irama_nafas'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_respirasi_batuk'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_gastrointestinal_mulut'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_gastrointestinal_gigi'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_gastrointestinal_lidah'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_gastrointestinal_tenggorokan'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_gastrointestinal_abdomen'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_gastrointestinal_peistatik_usus'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_gastrointestinal_anus'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_neurologi_pengelihatan'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_neurologi_alat_bantu_penglihatan'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_neurologi_pendengaran'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_neurologi_bicara'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_neurologi_sensorik'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_neurologi_motorik'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_neurologi_kekuatan_otot'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_integument_warnakulit'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_integument_turgor'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_integument_kulit'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_integument_dekubitas'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_muskuloskletal_pergerakan_sendi'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_muskuloskletal_kekauatan_otot'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_muskuloskletal_nyeri_sendi'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_muskuloskletal_oedema'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_muskuloskletal_fraktur'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_eliminasi_bab_frekuensi_jumlah'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_eliminasi_bab_frekuensi_durasi'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_eliminasi_bab_konsistensi'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_eliminasi_bab_warna'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_eliminasi_bak_frekuensi_jumlah'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_eliminasi_bak_frekuensi_durasi'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_eliminasi_bak_warna'] + '</td>';
eTable += '<td>' + res[i]['pemeriksaan_eliminasi_bak_lainlain'] + '</td>';
eTable += '<td>' + res[i]['pola_aktifitas_makanminum'] + '</td>';
eTable += '<td>' + res[i]['pola_aktifitas_mandi'] + '</td>';
eTable += '<td>' + res[i]['pola_aktifitas_eliminasi'] + '</td>';
eTable += '<td>' + res[i]['pola_aktifitas_berpakaian'] + '</td>';
eTable += '<td>' + res[i]['pola_aktifitas_berpindah'] + '</td>';
eTable += '<td>' + res[i]['pola_nutrisi_frekuesi_makan'] + '</td>';
eTable += '<td>' + res[i]['pola_nutrisi_jenis_makanan'] + '</td>';
eTable += '<td>' + res[i]['pola_nutrisi_porsi_makan'] + '</td>';
eTable += '<td>' + res[i]['pola_tidur_lama_tidur'] + '</td>';
eTable += '<td>' + res[i]['pola_tidur_gangguan'] + '</td>';
eTable += '<td>' + res[i]['pengkajian_fungsi_kemampuan_sehari'] + '</td>';
eTable += '<td>' + res[i]['pengkajian_fungsi_aktifitas'] + '</td>';
eTable += '<td>' + res[i]['pengkajian_fungsi_berjalan'] + '</td>';
eTable += '<td>' + res[i]['pengkajian_fungsi_ambulasi'] + '</td>';
eTable += '<td>' + res[i]['pengkajian_fungsi_ekstrimitas_atas'] + '</td>';
eTable += '<td>' + res[i]['pengkajian_fungsi_ekstrimitas_bawah'] + '</td>';
eTable += '<td>' + res[i]['pengkajian_fungsi_menggenggam'] + '</td>';
eTable += '<td>' + res[i]['pengkajian_fungsi_koordinasi'] + '</td>';
eTable += '<td>' + res[i]['pengkajian_fungsi_kesimpulan'] + '</td>';
eTable += '<td>' + res[i]['riwayat_psiko_kondisi_psiko'] + '</td>';
eTable += '<td>' + res[i]['riwayat_psiko_gangguan_jiwa'] + '</td>';
eTable += '<td>' + res[i]['riwayat_psiko_perilaku'] + '</td>';
eTable += '<td>' + res[i]['riwayat_psiko_hubungan_keluarga'] + '</td>';
eTable += '<td>' + res[i]['riwayat_psiko_tinggal'] + '</td>';
eTable += '<td>' + res[i]['riwayat_psiko_nilai_kepercayaan'] + '</td>';
eTable += '<td>' + res[i]['riwayat_psiko_pendidikan_pj'] + '</td>';
eTable += '<td>' + res[i]['riwayat_psiko_edukasi_diberikan'] + '</td>';
eTable += '<td>' + res[i]['penilaian_nyeri'] + '</td>';
eTable += '<td>' + res[i]['penilaian_nyeri_penyebab'] + '</td>';
eTable += '<td>' + res[i]['penilaian_nyeri_kualitas'] + '</td>';
eTable += '<td>' + res[i]['penilaian_nyeri_lokasi'] + '</td>';
eTable += '<td>' + res[i]['penilaian_nyeri_menyebar'] + '</td>';
eTable += '<td>' + res[i]['penilaian_nyeri_skala'] + '</td>';
eTable += '<td>' + res[i]['penilaian_nyeri_waktu'] + '</td>';
eTable += '<td>' + res[i]['penilaian_nyeri_hilang'] + '</td>';
eTable += '<td>' + res[i]['penilaian_nyeri_diberitahukan_dokter'] + '</td>';
eTable += '<td>' + res[i]['penilaian_nyeri_jam_diberitahukan_dokter'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhmorse_skala1'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhmorse_nilai1'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhmorse_skala2'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhmorse_nilai2'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhmorse_skala3'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhmorse_nilai3'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhmorse_skala4'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhmorse_nilai4'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhmorse_skala5'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhmorse_nilai5'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhmorse_skala6'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhmorse_nilai6'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhmorse_totalnilai'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhsydney_skala1'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhsydney_nilai1'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhsydney_skala2'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhsydney_nilai2'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhsydney_skala3'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhsydney_nilai3'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhsydney_skala4'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhsydney_nilai4'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhsydney_skala5'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhsydney_nilai5'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhsydney_skala6'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhsydney_nilai6'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhsydney_skala7'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhsydney_nilai7'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhsydney_skala8'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhsydney_nilai8'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhsydney_skala9'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhsydney_nilai9'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhsydney_skala10'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhsydney_nilai10'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhsydney_skala11'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhsydney_nilai11'] + '</td>';
eTable += '<td>' + res[i]['penilaian_jatuhsydney_totalnilai'] + '</td>';
eTable += '<td>' + res[i]['skrining_gizi1'] + '</td>';
eTable += '<td>' + res[i]['nilai_gizi1'] + '</td>';
eTable += '<td>' + res[i]['skrining_gizi2'] + '</td>';
eTable += '<td>' + res[i]['nilai_gizi2'] + '</td>';
eTable += '<td>' + res[i]['nilai_total_gizi'] + '</td>';
eTable += '<td>' + res[i]['skrining_gizi_diagnosa_khusus'] + '</td>';
eTable += '<td>' + res[i]['skrining_gizi_diketahui_dietisen'] + '</td>';
eTable += '<td>' + res[i]['skrining_gizi_jam_diketahui_dietisen'] + '</td>';
eTable += '<td>' + res[i]['rencana'] + '</td>';
eTable += '<td>' + res[i]['nip1'] + '</td>';
eTable += '<td>' + res[i]['nip2'] + '</td>';
eTable += '<td>' + res[i]['kd_dokter'] + '</td>';
                    eTable += "</tr>";
                }
                eTable += "</tbody></table></div>";
                $('#forTable_mlite_penilaian_awal_keperawatan_ranap').html(eTable);
            }
        });

        $('#modal-title').text("Lihat Data");
        $("#modal_lihat_mlite_penilaian_awal_keperawatan_ranap").modal();
    });

    // ==============================================================
    // TOMBOL DETAIL mlite_penilaian_awal_keperawatan_ranap DI CLICK
    // ==============================================================
    jQuery("#lihat_detail_mlite_penilaian_awal_keperawatan_ranap").click(function (event) {

        var rowData = var_tbl_mlite_penilaian_awal_keperawatan_ranap.rows({ selected: true }).data()[0];

        if (rowData) {
            var no_rawat = rowData['no_rawat'];
            var baseURL = mlite.url + '/' + mlite.admin;
            event.preventDefault();
            var loadURL =  baseURL + '/penilaian_keperawatan_ranap/detail/' + no_rawat.replace(/\//g,'') + '?t=' + mlite.token;
        
            var modal = $('#modal_detail_mlite_penilaian_awal_keperawatan_ranap');
            var modalContent = $('#modal_detail_mlite_penilaian_awal_keperawatan_ranap .modal-content');
        
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
        doc.text("Tabel Data Mlite Penilaian Awal Keperawatan Ranap", 20, 95, null, null, null);
        const totalPagesExp = "{total_pages_count_string}";        
        doc.autoTable({
            html: '#tbl_lihat_mlite_penilaian_awal_keperawatan_ranap',
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
        // doc.save('table_data_mlite_penilaian_awal_keperawatan_ranap.pdf')
        window.open(doc.output('bloburl'), '_blank',"toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes");  
              
    })

    // ===========================================
    // Ketika tombol export xlsx di tekan
    // ===========================================
    $("#export_xlsx").click(function () {
        let tbl1 = document.getElementById("tbl_lihat_mlite_penilaian_awal_keperawatan_ranap");
        let worksheet_tmp1 = XLSX.utils.table_to_sheet(tbl1);
        let a = XLSX.utils.sheet_to_json(worksheet_tmp1, { header: 1 });
        let worksheet1 = XLSX.utils.json_to_sheet(a, { skipHeader: true });
        const new_workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(new_workbook, worksheet1, "Data mlite_penilaian_awal_keperawatan_ranap");
        XLSX.writeFile(new_workbook, 'tmp_file.xls');
    })
});