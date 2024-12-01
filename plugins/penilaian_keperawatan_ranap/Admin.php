<?php
namespace Plugins\Penilaian_Keperawatan_Ranap;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Kelola'   => 'manage',
        ];
    }

    public function getManage(){
        $this->_addHeaderFiles();
        $enum['informasi'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'informasi');
        $enum['tiba_diruang_rawat'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'tiba_diruang_rawat');
        $enum['kasus_trauma'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'kasus_trauma');
        $enum['cara_masuk'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'cara_masuk');
        $enum['alat_bantu_dipakai'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'alat_bantu_dipakai');
        $enum['riwayat_kehamilan'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'riwayat_kehamilan');
        $enum['riwayat_merokok'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'riwayat_merokok');
        $enum['riwayat_alkohol'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'riwayat_alkohol');
        $enum['riwayat_narkoba'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'riwayat_narkoba');
        $enum['riwayat_olahraga'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'riwayat_olahraga');
        $enum['pemeriksaan_keadaan_umum'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_keadaan_umum');
        $enum['pemeriksaan_susunan_kepala'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_susunan_kepala');
        $enum['pemeriksaan_susunan_wajah'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_susunan_wajah');
        $enum['pemeriksaan_susunan_leher'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_susunan_leher');
        $enum['pemeriksaan_susunan_kejang'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_susunan_kejang');
        $enum['pemeriksaan_susunan_sensorik'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_susunan_sensorik');
        $enum['pemeriksaan_kardiovaskuler_denyut_nadi'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_kardiovaskuler_denyut_nadi');
        $enum['pemeriksaan_kardiovaskuler_sirkulasi'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_kardiovaskuler_sirkulasi');
        $enum['pemeriksaan_kardiovaskuler_pulsasi'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_kardiovaskuler_pulsasi');
        $enum['pemeriksaan_respirasi_pola_nafas'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_respirasi_pola_nafas');
        $enum['pemeriksaan_respirasi_retraksi'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_respirasi_retraksi');
        $enum['pemeriksaan_respirasi_suara_nafas'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_respirasi_suara_nafas');
        $enum['pemeriksaan_respirasi_volume_pernafasan'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_respirasi_volume_pernafasan');
        $enum['pemeriksaan_respirasi_jenis_pernafasan'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_respirasi_jenis_pernafasan');
        $enum['pemeriksaan_respirasi_irama_nafas'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_respirasi_irama_nafas');
        $enum['pemeriksaan_respirasi_batuk'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_respirasi_batuk');
        $enum['pemeriksaan_gastrointestinal_mulut'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_gastrointestinal_mulut');
        $enum['pemeriksaan_gastrointestinal_gigi'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_gastrointestinal_gigi');
        $enum['pemeriksaan_gastrointestinal_lidah'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_gastrointestinal_lidah');
        $enum['pemeriksaan_gastrointestinal_tenggorokan'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_gastrointestinal_tenggorokan');
        $enum['pemeriksaan_gastrointestinal_abdomen'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_gastrointestinal_abdomen');
        $enum['pemeriksaan_gastrointestinal_peistatik_usus'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_gastrointestinal_peistatik_usus');
        $enum['pemeriksaan_gastrointestinal_anus'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_gastrointestinal_anus');
        $enum['pemeriksaan_neurologi_pengelihatan'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_neurologi_pengelihatan');
        $enum['pemeriksaan_neurologi_alat_bantu_penglihatan'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_neurologi_alat_bantu_penglihatan');
        $enum['pemeriksaan_neurologi_pendengaran'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_neurologi_pendengaran');
        $enum['pemeriksaan_neurologi_bicara'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_neurologi_bicara');
        $enum['pemeriksaan_neurologi_sensorik'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_neurologi_sensorik');
        $enum['pemeriksaan_neurologi_motorik'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_neurologi_motorik');
        $enum['pemeriksaan_neurologi_kekuatan_otot'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_neurologi_kekuatan_otot');
        $enum['pemeriksaan_integument_warnakulit'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_integument_warnakulit');
        $enum['pemeriksaan_integument_turgor'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_integument_turgor');
        $enum['pemeriksaan_integument_kulit'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_integument_kulit');
        $enum['pemeriksaan_integument_dekubitas'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_integument_dekubitas');
        $enum['pemeriksaan_muskuloskletal_pergerakan_sendi'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_muskuloskletal_pergerakan_sendi');
        $enum['pemeriksaan_muskuloskletal_kekauatan_otot'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_muskuloskletal_kekauatan_otot');
        $enum['pemeriksaan_muskuloskletal_nyeri_sendi'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_muskuloskletal_nyeri_sendi');
        $enum['pemeriksaan_muskuloskletal_oedema'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_muskuloskletal_oedema');
        $enum['pemeriksaan_muskuloskletal_fraktur'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pemeriksaan_muskuloskletal_fraktur');
        $enum['pola_aktifitas_makanminum'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pola_aktifitas_makanminum');
        $enum['pola_aktifitas_mandi'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pola_aktifitas_mandi');
        $enum['pola_aktifitas_eliminasi'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pola_aktifitas_eliminasi');
        $enum['pola_aktifitas_berpakaian'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pola_aktifitas_berpakaian');
        $enum['pola_aktifitas_berpindah'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pola_aktifitas_berpindah');
        $enum['pola_tidur_gangguan'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pola_tidur_gangguan');
        $enum['pengkajian_fungsi_kemampuan_sehari'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pengkajian_fungsi_kemampuan_sehari');
        $enum['pengkajian_fungsi_aktifitas'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pengkajian_fungsi_aktifitas');
        $enum['pengkajian_fungsi_berjalan'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pengkajian_fungsi_berjalan');
        $enum['pengkajian_fungsi_ambulasi'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pengkajian_fungsi_ambulasi');
        $enum['pengkajian_fungsi_ekstrimitas_atas'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pengkajian_fungsi_ekstrimitas_atas');
        $enum['pengkajian_fungsi_ekstrimitas_bawah'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pengkajian_fungsi_ekstrimitas_bawah');
        $enum['pengkajian_fungsi_menggenggam'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pengkajian_fungsi_menggenggam');
        $enum['pengkajian_fungsi_koordinasi'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pengkajian_fungsi_koordinasi');
        $enum['pengkajian_fungsi_kesimpulan'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'pengkajian_fungsi_kesimpulan');
        $enum['riwayat_psiko_kondisi_psiko'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'riwayat_psiko_kondisi_psiko');
        $enum['riwayat_psiko_gangguan_jiwa'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'riwayat_psiko_gangguan_jiwa');
        $enum['riwayat_psiko_perilaku'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'riwayat_psiko_perilaku');
        $enum['riwayat_psiko_hubungan_keluarga'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'riwayat_psiko_hubungan_keluarga');
        $enum['riwayat_psiko_tinggal'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'riwayat_psiko_tinggal');
        $enum['riwayat_psiko_nilai_kepercayaan'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'riwayat_psiko_nilai_kepercayaan');
        $enum['riwayat_psiko_pendidikan_pj'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'riwayat_psiko_pendidikan_pj');
        $enum['riwayat_psiko_edukasi_diberikan'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'riwayat_psiko_edukasi_diberikan');
        $enum['penilaian_nyeri'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'penilaian_nyeri');
        $enum['penilaian_nyeri_penyebab'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'penilaian_nyeri_penyebab');
        $enum['penilaian_nyeri_kualitas'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'penilaian_nyeri_kualitas');
        $enum['penilaian_nyeri_menyebar'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'penilaian_nyeri_menyebar');
        $enum['penilaian_nyeri_skala'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'penilaian_nyeri_skala');
        $enum['penilaian_nyeri_hilang'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'penilaian_nyeri_hilang');
        $enum['penilaian_nyeri_diberitahukan_dokter'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'penilaian_nyeri_diberitahukan_dokter');
        $enum['penilaian_jatuhmorse_skala1'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'penilaian_jatuhmorse_skala1');
        $enum['penilaian_jatuhmorse_skala2'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'penilaian_jatuhmorse_skala2');
        $enum['penilaian_jatuhmorse_skala3'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'penilaian_jatuhmorse_skala3');
        $enum['penilaian_jatuhmorse_skala4'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'penilaian_jatuhmorse_skala4');
        $enum['penilaian_jatuhmorse_skala5'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'penilaian_jatuhmorse_skala5');
        $enum['penilaian_jatuhmorse_skala6'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'penilaian_jatuhmorse_skala6');
        $enum['penilaian_jatuhsydney_skala1'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'penilaian_jatuhsydney_skala1');
        $enum['penilaian_jatuhsydney_skala2'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'penilaian_jatuhsydney_skala2');
        $enum['penilaian_jatuhsydney_skala3'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'penilaian_jatuhsydney_skala3');
        $enum['penilaian_jatuhsydney_skala4'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'penilaian_jatuhsydney_skala4');
        $enum['penilaian_jatuhsydney_skala5'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'penilaian_jatuhsydney_skala5');
        $enum['penilaian_jatuhsydney_skala6'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'penilaian_jatuhsydney_skala6');
        $enum['penilaian_jatuhsydney_skala7'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'penilaian_jatuhsydney_skala7');
        $enum['penilaian_jatuhsydney_skala8'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'penilaian_jatuhsydney_skala8');
        $enum['penilaian_jatuhsydney_skala9'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'penilaian_jatuhsydney_skala9');
        $enum['penilaian_jatuhsydney_skala10'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'penilaian_jatuhsydney_skala10');
        $enum['penilaian_jatuhsydney_skala11'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'penilaian_jatuhsydney_skala11');
        $enum['skrining_gizi1'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'skrining_gizi1');
        $enum['skrining_gizi2'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'skrining_gizi2');
        $enum['skrining_gizi_diagnosa_khusus'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'skrining_gizi_diagnosa_khusus');
        $enum['skrining_gizi_diketahui_dietisen'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ranap', 'skrining_gizi_diketahui_dietisen');
        $dokter = $this->db('dokter')->where('status', '1')->toArray();
        return $this->draw('manage.html', ['enum' => $enum, 'dokter' => $dokter]);
    }

    public function postData(){
        $draw = $_POST['draw'];
        $row1 = $_POST['start'];
        $rowperpage = $_POST['length']; // Rows display per page
        $columnIndex = $_POST['order'][0]['column']; // Column index
        $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
        $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
        $searchValue = $_POST['search']['value']; // Search value

        ## Custom Field value
        $search_field_mlite_penilaian_awal_keperawatan_ranap= $_POST['search_field_mlite_penilaian_awal_keperawatan_ranap'];
        $search_text_mlite_penilaian_awal_keperawatan_ranap = $_POST['search_text_mlite_penilaian_awal_keperawatan_ranap'];

        $searchQuery = " ";
        if($search_text_mlite_penilaian_awal_keperawatan_ranap != ''){
            $searchQuery .= " and (".$search_field_mlite_penilaian_awal_keperawatan_ranap." like '%".$search_text_mlite_penilaian_awal_keperawatan_ranap."%' ) ";
        }

        ## Total number of records without filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from mlite_penilaian_awal_keperawatan_ranap");
        $sel->execute();
        $records = $sel->fetch();
        $totalRecords = $records['allcount'];

        ## Total number of records with filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from mlite_penilaian_awal_keperawatan_ranap WHERE 1 ".$searchQuery);
        $sel->execute();
        $records = $sel->fetch();
        $totalRecordwithFilter = $records['allcount'];

        ## Fetch records
        $sel = $this->db()->pdo()->prepare("select * from mlite_penilaian_awal_keperawatan_ranap WHERE 1 ".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row1.",".$rowperpage);
        $sel->execute();
        $result = $sel->fetchAll(\PDO::FETCH_ASSOC);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_rawat'=>$row['no_rawat'],
'tanggal'=>$row['tanggal'],
'informasi'=>$row['informasi'],
'ket_informasi'=>$row['ket_informasi'],
'tiba_diruang_rawat'=>$row['tiba_diruang_rawat'],
'kasus_trauma'=>$row['kasus_trauma'],
'cara_masuk'=>$row['cara_masuk'],
'rps'=>$row['rps'],
'rpd'=>$row['rpd'],
'rpk'=>$row['rpk'],
'rpo'=>$row['rpo'],
'riwayat_pembedahan'=>$row['riwayat_pembedahan'],
'riwayat_dirawat_dirs'=>$row['riwayat_dirawat_dirs'],
'alat_bantu_dipakai'=>$row['alat_bantu_dipakai'],
'riwayat_kehamilan'=>$row['riwayat_kehamilan'],
'riwayat_kehamilan_perkiraan'=>$row['riwayat_kehamilan_perkiraan'],
'riwayat_tranfusi'=>$row['riwayat_tranfusi'],
'riwayat_alergi'=>$row['riwayat_alergi'],
'riwayat_merokok'=>$row['riwayat_merokok'],
'riwayat_merokok_jumlah'=>$row['riwayat_merokok_jumlah'],
'riwayat_alkohol'=>$row['riwayat_alkohol'],
'riwayat_alkohol_jumlah'=>$row['riwayat_alkohol_jumlah'],
'riwayat_narkoba'=>$row['riwayat_narkoba'],
'riwayat_olahraga'=>$row['riwayat_olahraga'],
'pemeriksaan_mental'=>$row['pemeriksaan_mental'],
'pemeriksaan_keadaan_umum'=>$row['pemeriksaan_keadaan_umum'],
'pemeriksaan_gcs'=>$row['pemeriksaan_gcs'],
'pemeriksaan_td'=>$row['pemeriksaan_td'],
'pemeriksaan_nadi'=>$row['pemeriksaan_nadi'],
'pemeriksaan_rr'=>$row['pemeriksaan_rr'],
'pemeriksaan_suhu'=>$row['pemeriksaan_suhu'],
'pemeriksaan_spo2'=>$row['pemeriksaan_spo2'],
'pemeriksaan_bb'=>$row['pemeriksaan_bb'],
'pemeriksaan_tb'=>$row['pemeriksaan_tb'],
'pemeriksaan_susunan_kepala'=>$row['pemeriksaan_susunan_kepala'],
'pemeriksaan_susunan_wajah'=>$row['pemeriksaan_susunan_wajah'],
'pemeriksaan_susunan_leher'=>$row['pemeriksaan_susunan_leher'],
'pemeriksaan_susunan_kejang'=>$row['pemeriksaan_susunan_kejang'],
'pemeriksaan_susunan_sensorik'=>$row['pemeriksaan_susunan_sensorik'],
'pemeriksaan_kardiovaskuler_denyut_nadi'=>$row['pemeriksaan_kardiovaskuler_denyut_nadi'],
'pemeriksaan_kardiovaskuler_sirkulasi'=>$row['pemeriksaan_kardiovaskuler_sirkulasi'],
'pemeriksaan_kardiovaskuler_pulsasi'=>$row['pemeriksaan_kardiovaskuler_pulsasi'],
'pemeriksaan_respirasi_pola_nafas'=>$row['pemeriksaan_respirasi_pola_nafas'],
'pemeriksaan_respirasi_retraksi'=>$row['pemeriksaan_respirasi_retraksi'],
'pemeriksaan_respirasi_suara_nafas'=>$row['pemeriksaan_respirasi_suara_nafas'],
'pemeriksaan_respirasi_volume_pernafasan'=>$row['pemeriksaan_respirasi_volume_pernafasan'],
'pemeriksaan_respirasi_jenis_pernafasan'=>$row['pemeriksaan_respirasi_jenis_pernafasan'],
'pemeriksaan_respirasi_irama_nafas'=>$row['pemeriksaan_respirasi_irama_nafas'],
'pemeriksaan_respirasi_batuk'=>$row['pemeriksaan_respirasi_batuk'],
'pemeriksaan_gastrointestinal_mulut'=>$row['pemeriksaan_gastrointestinal_mulut'],
'pemeriksaan_gastrointestinal_gigi'=>$row['pemeriksaan_gastrointestinal_gigi'],
'pemeriksaan_gastrointestinal_lidah'=>$row['pemeriksaan_gastrointestinal_lidah'],
'pemeriksaan_gastrointestinal_tenggorokan'=>$row['pemeriksaan_gastrointestinal_tenggorokan'],
'pemeriksaan_gastrointestinal_abdomen'=>$row['pemeriksaan_gastrointestinal_abdomen'],
'pemeriksaan_gastrointestinal_peistatik_usus'=>$row['pemeriksaan_gastrointestinal_peistatik_usus'],
'pemeriksaan_gastrointestinal_anus'=>$row['pemeriksaan_gastrointestinal_anus'],
'pemeriksaan_neurologi_pengelihatan'=>$row['pemeriksaan_neurologi_pengelihatan'],
'pemeriksaan_neurologi_alat_bantu_penglihatan'=>$row['pemeriksaan_neurologi_alat_bantu_penglihatan'],
'pemeriksaan_neurologi_pendengaran'=>$row['pemeriksaan_neurologi_pendengaran'],
'pemeriksaan_neurologi_bicara'=>$row['pemeriksaan_neurologi_bicara'],
'pemeriksaan_neurologi_sensorik'=>$row['pemeriksaan_neurologi_sensorik'],
'pemeriksaan_neurologi_motorik'=>$row['pemeriksaan_neurologi_motorik'],
'pemeriksaan_neurologi_kekuatan_otot'=>$row['pemeriksaan_neurologi_kekuatan_otot'],
'pemeriksaan_integument_warnakulit'=>$row['pemeriksaan_integument_warnakulit'],
'pemeriksaan_integument_turgor'=>$row['pemeriksaan_integument_turgor'],
'pemeriksaan_integument_kulit'=>$row['pemeriksaan_integument_kulit'],
'pemeriksaan_integument_dekubitas'=>$row['pemeriksaan_integument_dekubitas'],
'pemeriksaan_muskuloskletal_pergerakan_sendi'=>$row['pemeriksaan_muskuloskletal_pergerakan_sendi'],
'pemeriksaan_muskuloskletal_kekauatan_otot'=>$row['pemeriksaan_muskuloskletal_kekauatan_otot'],
'pemeriksaan_muskuloskletal_nyeri_sendi'=>$row['pemeriksaan_muskuloskletal_nyeri_sendi'],
'pemeriksaan_muskuloskletal_oedema'=>$row['pemeriksaan_muskuloskletal_oedema'],
'pemeriksaan_muskuloskletal_fraktur'=>$row['pemeriksaan_muskuloskletal_fraktur'],
'pemeriksaan_eliminasi_bab_frekuensi_jumlah'=>$row['pemeriksaan_eliminasi_bab_frekuensi_jumlah'],
'pemeriksaan_eliminasi_bab_frekuensi_durasi'=>$row['pemeriksaan_eliminasi_bab_frekuensi_durasi'],
'pemeriksaan_eliminasi_bab_konsistensi'=>$row['pemeriksaan_eliminasi_bab_konsistensi'],
'pemeriksaan_eliminasi_bab_warna'=>$row['pemeriksaan_eliminasi_bab_warna'],
'pemeriksaan_eliminasi_bak_frekuensi_jumlah'=>$row['pemeriksaan_eliminasi_bak_frekuensi_jumlah'],
'pemeriksaan_eliminasi_bak_frekuensi_durasi'=>$row['pemeriksaan_eliminasi_bak_frekuensi_durasi'],
'pemeriksaan_eliminasi_bak_warna'=>$row['pemeriksaan_eliminasi_bak_warna'],
'pemeriksaan_eliminasi_bak_lainlain'=>$row['pemeriksaan_eliminasi_bak_lainlain'],
'pola_aktifitas_makanminum'=>$row['pola_aktifitas_makanminum'],
'pola_aktifitas_mandi'=>$row['pola_aktifitas_mandi'],
'pola_aktifitas_eliminasi'=>$row['pola_aktifitas_eliminasi'],
'pola_aktifitas_berpakaian'=>$row['pola_aktifitas_berpakaian'],
'pola_aktifitas_berpindah'=>$row['pola_aktifitas_berpindah'],
'pola_nutrisi_frekuesi_makan'=>$row['pola_nutrisi_frekuesi_makan'],
'pola_nutrisi_jenis_makanan'=>$row['pola_nutrisi_jenis_makanan'],
'pola_nutrisi_porsi_makan'=>$row['pola_nutrisi_porsi_makan'],
'pola_tidur_lama_tidur'=>$row['pola_tidur_lama_tidur'],
'pola_tidur_gangguan'=>$row['pola_tidur_gangguan'],
'pengkajian_fungsi_kemampuan_sehari'=>$row['pengkajian_fungsi_kemampuan_sehari'],
'pengkajian_fungsi_aktifitas'=>$row['pengkajian_fungsi_aktifitas'],
'pengkajian_fungsi_berjalan'=>$row['pengkajian_fungsi_berjalan'],
'pengkajian_fungsi_ambulasi'=>$row['pengkajian_fungsi_ambulasi'],
'pengkajian_fungsi_ekstrimitas_atas'=>$row['pengkajian_fungsi_ekstrimitas_atas'],
'pengkajian_fungsi_ekstrimitas_bawah'=>$row['pengkajian_fungsi_ekstrimitas_bawah'],
'pengkajian_fungsi_menggenggam'=>$row['pengkajian_fungsi_menggenggam'],
'pengkajian_fungsi_koordinasi'=>$row['pengkajian_fungsi_koordinasi'],
'pengkajian_fungsi_kesimpulan'=>$row['pengkajian_fungsi_kesimpulan'],
'riwayat_psiko_kondisi_psiko'=>$row['riwayat_psiko_kondisi_psiko'],
'riwayat_psiko_gangguan_jiwa'=>$row['riwayat_psiko_gangguan_jiwa'],
'riwayat_psiko_perilaku'=>$row['riwayat_psiko_perilaku'],
'riwayat_psiko_hubungan_keluarga'=>$row['riwayat_psiko_hubungan_keluarga'],
'riwayat_psiko_tinggal'=>$row['riwayat_psiko_tinggal'],
'riwayat_psiko_nilai_kepercayaan'=>$row['riwayat_psiko_nilai_kepercayaan'],
'riwayat_psiko_pendidikan_pj'=>$row['riwayat_psiko_pendidikan_pj'],
'riwayat_psiko_edukasi_diberikan'=>$row['riwayat_psiko_edukasi_diberikan'],
'penilaian_nyeri'=>$row['penilaian_nyeri'],
'penilaian_nyeri_penyebab'=>$row['penilaian_nyeri_penyebab'],
'penilaian_nyeri_kualitas'=>$row['penilaian_nyeri_kualitas'],
'penilaian_nyeri_lokasi'=>$row['penilaian_nyeri_lokasi'],
'penilaian_nyeri_menyebar'=>$row['penilaian_nyeri_menyebar'],
'penilaian_nyeri_skala'=>$row['penilaian_nyeri_skala'],
'penilaian_nyeri_waktu'=>$row['penilaian_nyeri_waktu'],
'penilaian_nyeri_hilang'=>$row['penilaian_nyeri_hilang'],
'penilaian_nyeri_diberitahukan_dokter'=>$row['penilaian_nyeri_diberitahukan_dokter'],
'penilaian_nyeri_jam_diberitahukan_dokter'=>$row['penilaian_nyeri_jam_diberitahukan_dokter'],
'penilaian_jatuhmorse_skala1'=>$row['penilaian_jatuhmorse_skala1'],
'penilaian_jatuhmorse_nilai1'=>$row['penilaian_jatuhmorse_nilai1'],
'penilaian_jatuhmorse_skala2'=>$row['penilaian_jatuhmorse_skala2'],
'penilaian_jatuhmorse_nilai2'=>$row['penilaian_jatuhmorse_nilai2'],
'penilaian_jatuhmorse_skala3'=>$row['penilaian_jatuhmorse_skala3'],
'penilaian_jatuhmorse_nilai3'=>$row['penilaian_jatuhmorse_nilai3'],
'penilaian_jatuhmorse_skala4'=>$row['penilaian_jatuhmorse_skala4'],
'penilaian_jatuhmorse_nilai4'=>$row['penilaian_jatuhmorse_nilai4'],
'penilaian_jatuhmorse_skala5'=>$row['penilaian_jatuhmorse_skala5'],
'penilaian_jatuhmorse_nilai5'=>$row['penilaian_jatuhmorse_nilai5'],
'penilaian_jatuhmorse_skala6'=>$row['penilaian_jatuhmorse_skala6'],
'penilaian_jatuhmorse_nilai6'=>$row['penilaian_jatuhmorse_nilai6'],
'penilaian_jatuhmorse_totalnilai'=>$row['penilaian_jatuhmorse_totalnilai'],
'penilaian_jatuhsydney_skala1'=>$row['penilaian_jatuhsydney_skala1'],
'penilaian_jatuhsydney_nilai1'=>$row['penilaian_jatuhsydney_nilai1'],
'penilaian_jatuhsydney_skala2'=>$row['penilaian_jatuhsydney_skala2'],
'penilaian_jatuhsydney_nilai2'=>$row['penilaian_jatuhsydney_nilai2'],
'penilaian_jatuhsydney_skala3'=>$row['penilaian_jatuhsydney_skala3'],
'penilaian_jatuhsydney_nilai3'=>$row['penilaian_jatuhsydney_nilai3'],
'penilaian_jatuhsydney_skala4'=>$row['penilaian_jatuhsydney_skala4'],
'penilaian_jatuhsydney_nilai4'=>$row['penilaian_jatuhsydney_nilai4'],
'penilaian_jatuhsydney_skala5'=>$row['penilaian_jatuhsydney_skala5'],
'penilaian_jatuhsydney_nilai5'=>$row['penilaian_jatuhsydney_nilai5'],
'penilaian_jatuhsydney_skala6'=>$row['penilaian_jatuhsydney_skala6'],
'penilaian_jatuhsydney_nilai6'=>$row['penilaian_jatuhsydney_nilai6'],
'penilaian_jatuhsydney_skala7'=>$row['penilaian_jatuhsydney_skala7'],
'penilaian_jatuhsydney_nilai7'=>$row['penilaian_jatuhsydney_nilai7'],
'penilaian_jatuhsydney_skala8'=>$row['penilaian_jatuhsydney_skala8'],
'penilaian_jatuhsydney_nilai8'=>$row['penilaian_jatuhsydney_nilai8'],
'penilaian_jatuhsydney_skala9'=>$row['penilaian_jatuhsydney_skala9'],
'penilaian_jatuhsydney_nilai9'=>$row['penilaian_jatuhsydney_nilai9'],
'penilaian_jatuhsydney_skala10'=>$row['penilaian_jatuhsydney_skala10'],
'penilaian_jatuhsydney_nilai10'=>$row['penilaian_jatuhsydney_nilai10'],
'penilaian_jatuhsydney_skala11'=>$row['penilaian_jatuhsydney_skala11'],
'penilaian_jatuhsydney_nilai11'=>$row['penilaian_jatuhsydney_nilai11'],
'penilaian_jatuhsydney_totalnilai'=>$row['penilaian_jatuhsydney_totalnilai'],
'skrining_gizi1'=>$row['skrining_gizi1'],
'nilai_gizi1'=>$row['nilai_gizi1'],
'skrining_gizi2'=>$row['skrining_gizi2'],
'nilai_gizi2'=>$row['nilai_gizi2'],
'nilai_total_gizi'=>$row['nilai_total_gizi'],
'skrining_gizi_diagnosa_khusus'=>$row['skrining_gizi_diagnosa_khusus'],
'skrining_gizi_diketahui_dietisen'=>$row['skrining_gizi_diketahui_dietisen'],
'skrining_gizi_jam_diketahui_dietisen'=>$row['skrining_gizi_jam_diketahui_dietisen'],
'rencana'=>$row['rencana'],
'nip1'=>$row['nip1'],
'nip2'=>$row['nip2'],
'kd_dokter'=>$row['kd_dokter']

            );
        }

        ## Response
        $response = array(
            "draw" => intval($draw), 
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordwithFilter,
            "aaData" => $data
        );

        echo json_encode($response);
        exit();
    }

    public function postAksi()
    {
        if(isset($_POST['typeact'])){ 
            $act = $_POST['typeact']; 
        }else{ 
            $act = ''; 
        }

        if ($act=='add') {

        $no_rawat = $_POST['no_rawat'];
$tanggal = $_POST['tanggal'];
$informasi = $_POST['informasi'];
$ket_informasi = $_POST['ket_informasi'];
$tiba_diruang_rawat = $_POST['tiba_diruang_rawat'];
$kasus_trauma = $_POST['kasus_trauma'];
$cara_masuk = $_POST['cara_masuk'];
$rps = $_POST['rps'];
$rpd = $_POST['rpd'];
$rpk = $_POST['rpk'];
$rpo = $_POST['rpo'];
$riwayat_pembedahan = $_POST['riwayat_pembedahan'];
$riwayat_dirawat_dirs = $_POST['riwayat_dirawat_dirs'];
$alat_bantu_dipakai = $_POST['alat_bantu_dipakai'];
$riwayat_kehamilan = $_POST['riwayat_kehamilan'];
$riwayat_kehamilan_perkiraan = $_POST['riwayat_kehamilan_perkiraan'];
$riwayat_tranfusi = $_POST['riwayat_tranfusi'];
$riwayat_alergi = $_POST['riwayat_alergi'];
$riwayat_merokok = $_POST['riwayat_merokok'];
$riwayat_merokok_jumlah = $_POST['riwayat_merokok_jumlah'];
$riwayat_alkohol = $_POST['riwayat_alkohol'];
$riwayat_alkohol_jumlah = $_POST['riwayat_alkohol_jumlah'];
$riwayat_narkoba = $_POST['riwayat_narkoba'];
$riwayat_olahraga = $_POST['riwayat_olahraga'];
$pemeriksaan_mental = $_POST['pemeriksaan_mental'];
$pemeriksaan_keadaan_umum = $_POST['pemeriksaan_keadaan_umum'];
$pemeriksaan_gcs = $_POST['pemeriksaan_gcs'];
$pemeriksaan_td = $_POST['pemeriksaan_td'];
$pemeriksaan_nadi = $_POST['pemeriksaan_nadi'];
$pemeriksaan_rr = $_POST['pemeriksaan_rr'];
$pemeriksaan_suhu = $_POST['pemeriksaan_suhu'];
$pemeriksaan_spo2 = $_POST['pemeriksaan_spo2'];
$pemeriksaan_bb = $_POST['pemeriksaan_bb'];
$pemeriksaan_tb = $_POST['pemeriksaan_tb'];
$pemeriksaan_susunan_kepala = $_POST['pemeriksaan_susunan_kepala'];
$pemeriksaan_susunan_wajah = $_POST['pemeriksaan_susunan_wajah'];
$pemeriksaan_susunan_leher = $_POST['pemeriksaan_susunan_leher'];
$pemeriksaan_susunan_kejang = $_POST['pemeriksaan_susunan_kejang'];
$pemeriksaan_susunan_sensorik = $_POST['pemeriksaan_susunan_sensorik'];
$pemeriksaan_kardiovaskuler_denyut_nadi = $_POST['pemeriksaan_kardiovaskuler_denyut_nadi'];
$pemeriksaan_kardiovaskuler_sirkulasi = $_POST['pemeriksaan_kardiovaskuler_sirkulasi'];
$pemeriksaan_kardiovaskuler_pulsasi = $_POST['pemeriksaan_kardiovaskuler_pulsasi'];
$pemeriksaan_respirasi_pola_nafas = $_POST['pemeriksaan_respirasi_pola_nafas'];
$pemeriksaan_respirasi_retraksi = $_POST['pemeriksaan_respirasi_retraksi'];
$pemeriksaan_respirasi_suara_nafas = $_POST['pemeriksaan_respirasi_suara_nafas'];
$pemeriksaan_respirasi_volume_pernafasan = $_POST['pemeriksaan_respirasi_volume_pernafasan'];
$pemeriksaan_respirasi_jenis_pernafasan = $_POST['pemeriksaan_respirasi_jenis_pernafasan'];
$pemeriksaan_respirasi_irama_nafas = $_POST['pemeriksaan_respirasi_irama_nafas'];
$pemeriksaan_respirasi_batuk = $_POST['pemeriksaan_respirasi_batuk'];
$pemeriksaan_gastrointestinal_mulut = $_POST['pemeriksaan_gastrointestinal_mulut'];
$pemeriksaan_gastrointestinal_gigi = $_POST['pemeriksaan_gastrointestinal_gigi'];
$pemeriksaan_gastrointestinal_lidah = $_POST['pemeriksaan_gastrointestinal_lidah'];
$pemeriksaan_gastrointestinal_tenggorokan = $_POST['pemeriksaan_gastrointestinal_tenggorokan'];
$pemeriksaan_gastrointestinal_abdomen = $_POST['pemeriksaan_gastrointestinal_abdomen'];
$pemeriksaan_gastrointestinal_peistatik_usus = $_POST['pemeriksaan_gastrointestinal_peistatik_usus'];
$pemeriksaan_gastrointestinal_anus = $_POST['pemeriksaan_gastrointestinal_anus'];
$pemeriksaan_neurologi_pengelihatan = $_POST['pemeriksaan_neurologi_pengelihatan'];
$pemeriksaan_neurologi_alat_bantu_penglihatan = $_POST['pemeriksaan_neurologi_alat_bantu_penglihatan'];
$pemeriksaan_neurologi_pendengaran = $_POST['pemeriksaan_neurologi_pendengaran'];
$pemeriksaan_neurologi_bicara = $_POST['pemeriksaan_neurologi_bicara'];
$pemeriksaan_neurologi_sensorik = $_POST['pemeriksaan_neurologi_sensorik'];
$pemeriksaan_neurologi_motorik = $_POST['pemeriksaan_neurologi_motorik'];
$pemeriksaan_neurologi_kekuatan_otot = $_POST['pemeriksaan_neurologi_kekuatan_otot'];
$pemeriksaan_integument_warnakulit = $_POST['pemeriksaan_integument_warnakulit'];
$pemeriksaan_integument_turgor = $_POST['pemeriksaan_integument_turgor'];
$pemeriksaan_integument_kulit = $_POST['pemeriksaan_integument_kulit'];
$pemeriksaan_integument_dekubitas = $_POST['pemeriksaan_integument_dekubitas'];
$pemeriksaan_muskuloskletal_pergerakan_sendi = $_POST['pemeriksaan_muskuloskletal_pergerakan_sendi'];
$pemeriksaan_muskuloskletal_kekauatan_otot = $_POST['pemeriksaan_muskuloskletal_kekauatan_otot'];
$pemeriksaan_muskuloskletal_nyeri_sendi = $_POST['pemeriksaan_muskuloskletal_nyeri_sendi'];
$pemeriksaan_muskuloskletal_oedema = $_POST['pemeriksaan_muskuloskletal_oedema'];
$pemeriksaan_muskuloskletal_fraktur = $_POST['pemeriksaan_muskuloskletal_fraktur'];
$pemeriksaan_eliminasi_bab_frekuensi_jumlah = $_POST['pemeriksaan_eliminasi_bab_frekuensi_jumlah'];
$pemeriksaan_eliminasi_bab_frekuensi_durasi = $_POST['pemeriksaan_eliminasi_bab_frekuensi_durasi'];
$pemeriksaan_eliminasi_bab_konsistensi = $_POST['pemeriksaan_eliminasi_bab_konsistensi'];
$pemeriksaan_eliminasi_bab_warna = $_POST['pemeriksaan_eliminasi_bab_warna'];
$pemeriksaan_eliminasi_bak_frekuensi_jumlah = $_POST['pemeriksaan_eliminasi_bak_frekuensi_jumlah'];
$pemeriksaan_eliminasi_bak_frekuensi_durasi = $_POST['pemeriksaan_eliminasi_bak_frekuensi_durasi'];
$pemeriksaan_eliminasi_bak_warna = $_POST['pemeriksaan_eliminasi_bak_warna'];
$pemeriksaan_eliminasi_bak_lainlain = $_POST['pemeriksaan_eliminasi_bak_lainlain'];
$pola_aktifitas_makanminum = $_POST['pola_aktifitas_makanminum'];
$pola_aktifitas_mandi = $_POST['pola_aktifitas_mandi'];
$pola_aktifitas_eliminasi = $_POST['pola_aktifitas_eliminasi'];
$pola_aktifitas_berpakaian = $_POST['pola_aktifitas_berpakaian'];
$pola_aktifitas_berpindah = $_POST['pola_aktifitas_berpindah'];
$pola_nutrisi_frekuesi_makan = $_POST['pola_nutrisi_frekuesi_makan'];
$pola_nutrisi_jenis_makanan = $_POST['pola_nutrisi_jenis_makanan'];
$pola_nutrisi_porsi_makan = $_POST['pola_nutrisi_porsi_makan'];
$pola_tidur_lama_tidur = $_POST['pola_tidur_lama_tidur'];
$pola_tidur_gangguan = $_POST['pola_tidur_gangguan'];
$pengkajian_fungsi_kemampuan_sehari = $_POST['pengkajian_fungsi_kemampuan_sehari'];
$pengkajian_fungsi_aktifitas = $_POST['pengkajian_fungsi_aktifitas'];
$pengkajian_fungsi_berjalan = $_POST['pengkajian_fungsi_berjalan'];
$pengkajian_fungsi_ambulasi = $_POST['pengkajian_fungsi_ambulasi'];
$pengkajian_fungsi_ekstrimitas_atas = $_POST['pengkajian_fungsi_ekstrimitas_atas'];
$pengkajian_fungsi_ekstrimitas_bawah = $_POST['pengkajian_fungsi_ekstrimitas_bawah'];
$pengkajian_fungsi_menggenggam = $_POST['pengkajian_fungsi_menggenggam'];
$pengkajian_fungsi_koordinasi = $_POST['pengkajian_fungsi_koordinasi'];
$pengkajian_fungsi_kesimpulan = $_POST['pengkajian_fungsi_kesimpulan'];
$riwayat_psiko_kondisi_psiko = $_POST['riwayat_psiko_kondisi_psiko'];
$riwayat_psiko_gangguan_jiwa = $_POST['riwayat_psiko_gangguan_jiwa'];
$riwayat_psiko_perilaku = $_POST['riwayat_psiko_perilaku'];
$riwayat_psiko_hubungan_keluarga = $_POST['riwayat_psiko_hubungan_keluarga'];
$riwayat_psiko_tinggal = $_POST['riwayat_psiko_tinggal'];
$riwayat_psiko_nilai_kepercayaan = $_POST['riwayat_psiko_nilai_kepercayaan'];
$riwayat_psiko_pendidikan_pj = $_POST['riwayat_psiko_pendidikan_pj'];
$riwayat_psiko_edukasi_diberikan = $_POST['riwayat_psiko_edukasi_diberikan'];
$penilaian_nyeri = $_POST['penilaian_nyeri'];
$penilaian_nyeri_penyebab = $_POST['penilaian_nyeri_penyebab'];
$penilaian_nyeri_kualitas = $_POST['penilaian_nyeri_kualitas'];
$penilaian_nyeri_lokasi = $_POST['penilaian_nyeri_lokasi'];
$penilaian_nyeri_menyebar = $_POST['penilaian_nyeri_menyebar'];
$penilaian_nyeri_skala = $_POST['penilaian_nyeri_skala'];
$penilaian_nyeri_waktu = $_POST['penilaian_nyeri_waktu'];
$penilaian_nyeri_hilang = $_POST['penilaian_nyeri_hilang'];
$penilaian_nyeri_diberitahukan_dokter = $_POST['penilaian_nyeri_diberitahukan_dokter'];
$penilaian_nyeri_jam_diberitahukan_dokter = $_POST['penilaian_nyeri_jam_diberitahukan_dokter'];
$penilaian_jatuhmorse_skala1 = $_POST['penilaian_jatuhmorse_skala1'];
$penilaian_jatuhmorse_nilai1 = $_POST['penilaian_jatuhmorse_nilai1'];
$penilaian_jatuhmorse_skala2 = $_POST['penilaian_jatuhmorse_skala2'];
$penilaian_jatuhmorse_nilai2 = $_POST['penilaian_jatuhmorse_nilai2'];
$penilaian_jatuhmorse_skala3 = $_POST['penilaian_jatuhmorse_skala3'];
$penilaian_jatuhmorse_nilai3 = $_POST['penilaian_jatuhmorse_nilai3'];
$penilaian_jatuhmorse_skala4 = $_POST['penilaian_jatuhmorse_skala4'];
$penilaian_jatuhmorse_nilai4 = $_POST['penilaian_jatuhmorse_nilai4'];
$penilaian_jatuhmorse_skala5 = $_POST['penilaian_jatuhmorse_skala5'];
$penilaian_jatuhmorse_nilai5 = $_POST['penilaian_jatuhmorse_nilai5'];
$penilaian_jatuhmorse_skala6 = $_POST['penilaian_jatuhmorse_skala6'];
$penilaian_jatuhmorse_nilai6 = $_POST['penilaian_jatuhmorse_nilai6'];
$penilaian_jatuhmorse_totalnilai = $_POST['penilaian_jatuhmorse_totalnilai'];
$penilaian_jatuhsydney_skala1 = $_POST['penilaian_jatuhsydney_skala1'];
$penilaian_jatuhsydney_nilai1 = $_POST['penilaian_jatuhsydney_nilai1'];
$penilaian_jatuhsydney_skala2 = $_POST['penilaian_jatuhsydney_skala2'];
$penilaian_jatuhsydney_nilai2 = $_POST['penilaian_jatuhsydney_nilai2'];
$penilaian_jatuhsydney_skala3 = $_POST['penilaian_jatuhsydney_skala3'];
$penilaian_jatuhsydney_nilai3 = $_POST['penilaian_jatuhsydney_nilai3'];
$penilaian_jatuhsydney_skala4 = $_POST['penilaian_jatuhsydney_skala4'];
$penilaian_jatuhsydney_nilai4 = $_POST['penilaian_jatuhsydney_nilai4'];
$penilaian_jatuhsydney_skala5 = $_POST['penilaian_jatuhsydney_skala5'];
$penilaian_jatuhsydney_nilai5 = $_POST['penilaian_jatuhsydney_nilai5'];
$penilaian_jatuhsydney_skala6 = $_POST['penilaian_jatuhsydney_skala6'];
$penilaian_jatuhsydney_nilai6 = $_POST['penilaian_jatuhsydney_nilai6'];
$penilaian_jatuhsydney_skala7 = $_POST['penilaian_jatuhsydney_skala7'];
$penilaian_jatuhsydney_nilai7 = $_POST['penilaian_jatuhsydney_nilai7'];
$penilaian_jatuhsydney_skala8 = $_POST['penilaian_jatuhsydney_skala8'];
$penilaian_jatuhsydney_nilai8 = $_POST['penilaian_jatuhsydney_nilai8'];
$penilaian_jatuhsydney_skala9 = $_POST['penilaian_jatuhsydney_skala9'];
$penilaian_jatuhsydney_nilai9 = $_POST['penilaian_jatuhsydney_nilai9'];
$penilaian_jatuhsydney_skala10 = $_POST['penilaian_jatuhsydney_skala10'];
$penilaian_jatuhsydney_nilai10 = $_POST['penilaian_jatuhsydney_nilai10'];
$penilaian_jatuhsydney_skala11 = $_POST['penilaian_jatuhsydney_skala11'];
$penilaian_jatuhsydney_nilai11 = $_POST['penilaian_jatuhsydney_nilai11'];
$penilaian_jatuhsydney_totalnilai = $_POST['penilaian_jatuhsydney_totalnilai'];
$skrining_gizi1 = $_POST['skrining_gizi1'];
$nilai_gizi1 = $_POST['nilai_gizi1'];
$skrining_gizi2 = $_POST['skrining_gizi2'];
$nilai_gizi2 = $_POST['nilai_gizi2'];
$nilai_total_gizi = $_POST['nilai_total_gizi'];
$skrining_gizi_diagnosa_khusus = $_POST['skrining_gizi_diagnosa_khusus'];
$skrining_gizi_diketahui_dietisen = $_POST['skrining_gizi_diketahui_dietisen'];
$skrining_gizi_jam_diketahui_dietisen = $_POST['skrining_gizi_jam_diketahui_dietisen'];
$rencana = $_POST['rencana'];
$nip1 = $_POST['nip1'];
$nip2 = $_POST['nip2'];
$kd_dokter = $_POST['kd_dokter'];

            
            $mlite_penilaian_awal_keperawatan_ranap_add = $this->db()->pdo()->prepare('INSERT INTO mlite_penilaian_awal_keperawatan_ranap VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $mlite_penilaian_awal_keperawatan_ranap_add->execute([$no_rawat, $tanggal, $informasi, $ket_informasi, $tiba_diruang_rawat, $kasus_trauma, $cara_masuk, $rps, $rpd, $rpk, $rpo, $riwayat_pembedahan, $riwayat_dirawat_dirs, $alat_bantu_dipakai, $riwayat_kehamilan, $riwayat_kehamilan_perkiraan, $riwayat_tranfusi, $riwayat_alergi, $riwayat_merokok, $riwayat_merokok_jumlah, $riwayat_alkohol, $riwayat_alkohol_jumlah, $riwayat_narkoba, $riwayat_olahraga, $pemeriksaan_mental, $pemeriksaan_keadaan_umum, $pemeriksaan_gcs, $pemeriksaan_td, $pemeriksaan_nadi, $pemeriksaan_rr, $pemeriksaan_suhu, $pemeriksaan_spo2, $pemeriksaan_bb, $pemeriksaan_tb, $pemeriksaan_susunan_kepala, $pemeriksaan_susunan_wajah, $pemeriksaan_susunan_leher, $pemeriksaan_susunan_kejang, $pemeriksaan_susunan_sensorik, $pemeriksaan_kardiovaskuler_denyut_nadi, $pemeriksaan_kardiovaskuler_sirkulasi, $pemeriksaan_kardiovaskuler_pulsasi, $pemeriksaan_respirasi_pola_nafas, $pemeriksaan_respirasi_retraksi, $pemeriksaan_respirasi_suara_nafas, $pemeriksaan_respirasi_volume_pernafasan, $pemeriksaan_respirasi_jenis_pernafasan, $pemeriksaan_respirasi_irama_nafas, $pemeriksaan_respirasi_batuk, $pemeriksaan_gastrointestinal_mulut, $pemeriksaan_gastrointestinal_gigi, $pemeriksaan_gastrointestinal_lidah, $pemeriksaan_gastrointestinal_tenggorokan, $pemeriksaan_gastrointestinal_abdomen, $pemeriksaan_gastrointestinal_peistatik_usus, $pemeriksaan_gastrointestinal_anus, $pemeriksaan_neurologi_pengelihatan, $pemeriksaan_neurologi_alat_bantu_penglihatan, $pemeriksaan_neurologi_pendengaran, $pemeriksaan_neurologi_bicara, $pemeriksaan_neurologi_sensorik, $pemeriksaan_neurologi_motorik, $pemeriksaan_neurologi_kekuatan_otot, $pemeriksaan_integument_warnakulit, $pemeriksaan_integument_turgor, $pemeriksaan_integument_kulit, $pemeriksaan_integument_dekubitas, $pemeriksaan_muskuloskletal_pergerakan_sendi, $pemeriksaan_muskuloskletal_kekauatan_otot, $pemeriksaan_muskuloskletal_nyeri_sendi, $pemeriksaan_muskuloskletal_oedema, $pemeriksaan_muskuloskletal_fraktur, $pemeriksaan_eliminasi_bab_frekuensi_jumlah, $pemeriksaan_eliminasi_bab_frekuensi_durasi, $pemeriksaan_eliminasi_bab_konsistensi, $pemeriksaan_eliminasi_bab_warna, $pemeriksaan_eliminasi_bak_frekuensi_jumlah, $pemeriksaan_eliminasi_bak_frekuensi_durasi, $pemeriksaan_eliminasi_bak_warna, $pemeriksaan_eliminasi_bak_lainlain, $pola_aktifitas_makanminum, $pola_aktifitas_mandi, $pola_aktifitas_eliminasi, $pola_aktifitas_berpakaian, $pola_aktifitas_berpindah, $pola_nutrisi_frekuesi_makan, $pola_nutrisi_jenis_makanan, $pola_nutrisi_porsi_makan, $pola_tidur_lama_tidur, $pola_tidur_gangguan, $pengkajian_fungsi_kemampuan_sehari, $pengkajian_fungsi_aktifitas, $pengkajian_fungsi_berjalan, $pengkajian_fungsi_ambulasi, $pengkajian_fungsi_ekstrimitas_atas, $pengkajian_fungsi_ekstrimitas_bawah, $pengkajian_fungsi_menggenggam, $pengkajian_fungsi_koordinasi, $pengkajian_fungsi_kesimpulan, $riwayat_psiko_kondisi_psiko, $riwayat_psiko_gangguan_jiwa, $riwayat_psiko_perilaku, $riwayat_psiko_hubungan_keluarga, $riwayat_psiko_tinggal, $riwayat_psiko_nilai_kepercayaan, $riwayat_psiko_pendidikan_pj, $riwayat_psiko_edukasi_diberikan, $penilaian_nyeri, $penilaian_nyeri_penyebab, $penilaian_nyeri_kualitas, $penilaian_nyeri_lokasi, $penilaian_nyeri_menyebar, $penilaian_nyeri_skala, $penilaian_nyeri_waktu, $penilaian_nyeri_hilang, $penilaian_nyeri_diberitahukan_dokter, $penilaian_nyeri_jam_diberitahukan_dokter, $penilaian_jatuhmorse_skala1, $penilaian_jatuhmorse_nilai1, $penilaian_jatuhmorse_skala2, $penilaian_jatuhmorse_nilai2, $penilaian_jatuhmorse_skala3, $penilaian_jatuhmorse_nilai3, $penilaian_jatuhmorse_skala4, $penilaian_jatuhmorse_nilai4, $penilaian_jatuhmorse_skala5, $penilaian_jatuhmorse_nilai5, $penilaian_jatuhmorse_skala6, $penilaian_jatuhmorse_nilai6, $penilaian_jatuhmorse_totalnilai, $penilaian_jatuhsydney_skala1, $penilaian_jatuhsydney_nilai1, $penilaian_jatuhsydney_skala2, $penilaian_jatuhsydney_nilai2, $penilaian_jatuhsydney_skala3, $penilaian_jatuhsydney_nilai3, $penilaian_jatuhsydney_skala4, $penilaian_jatuhsydney_nilai4, $penilaian_jatuhsydney_skala5, $penilaian_jatuhsydney_nilai5, $penilaian_jatuhsydney_skala6, $penilaian_jatuhsydney_nilai6, $penilaian_jatuhsydney_skala7, $penilaian_jatuhsydney_nilai7, $penilaian_jatuhsydney_skala8, $penilaian_jatuhsydney_nilai8, $penilaian_jatuhsydney_skala9, $penilaian_jatuhsydney_nilai9, $penilaian_jatuhsydney_skala10, $penilaian_jatuhsydney_nilai10, $penilaian_jatuhsydney_skala11, $penilaian_jatuhsydney_nilai11, $penilaian_jatuhsydney_totalnilai, $skrining_gizi1, $nilai_gizi1, $skrining_gizi2, $nilai_gizi2, $nilai_total_gizi, $skrining_gizi_diagnosa_khusus, $skrining_gizi_diketahui_dietisen, $skrining_gizi_jam_diketahui_dietisen, $rencana, $nip1, $nip2, $kd_dokter]);

        }
        if ($act=="edit") {

        $no_rawat = $_POST['no_rawat'];
$tanggal = $_POST['tanggal'];
$informasi = $_POST['informasi'];
$ket_informasi = $_POST['ket_informasi'];
$tiba_diruang_rawat = $_POST['tiba_diruang_rawat'];
$kasus_trauma = $_POST['kasus_trauma'];
$cara_masuk = $_POST['cara_masuk'];
$rps = $_POST['rps'];
$rpd = $_POST['rpd'];
$rpk = $_POST['rpk'];
$rpo = $_POST['rpo'];
$riwayat_pembedahan = $_POST['riwayat_pembedahan'];
$riwayat_dirawat_dirs = $_POST['riwayat_dirawat_dirs'];
$alat_bantu_dipakai = $_POST['alat_bantu_dipakai'];
$riwayat_kehamilan = $_POST['riwayat_kehamilan'];
$riwayat_kehamilan_perkiraan = $_POST['riwayat_kehamilan_perkiraan'];
$riwayat_tranfusi = $_POST['riwayat_tranfusi'];
$riwayat_alergi = $_POST['riwayat_alergi'];
$riwayat_merokok = $_POST['riwayat_merokok'];
$riwayat_merokok_jumlah = $_POST['riwayat_merokok_jumlah'];
$riwayat_alkohol = $_POST['riwayat_alkohol'];
$riwayat_alkohol_jumlah = $_POST['riwayat_alkohol_jumlah'];
$riwayat_narkoba = $_POST['riwayat_narkoba'];
$riwayat_olahraga = $_POST['riwayat_olahraga'];
$pemeriksaan_mental = $_POST['pemeriksaan_mental'];
$pemeriksaan_keadaan_umum = $_POST['pemeriksaan_keadaan_umum'];
$pemeriksaan_gcs = $_POST['pemeriksaan_gcs'];
$pemeriksaan_td = $_POST['pemeriksaan_td'];
$pemeriksaan_nadi = $_POST['pemeriksaan_nadi'];
$pemeriksaan_rr = $_POST['pemeriksaan_rr'];
$pemeriksaan_suhu = $_POST['pemeriksaan_suhu'];
$pemeriksaan_spo2 = $_POST['pemeriksaan_spo2'];
$pemeriksaan_bb = $_POST['pemeriksaan_bb'];
$pemeriksaan_tb = $_POST['pemeriksaan_tb'];
$pemeriksaan_susunan_kepala = $_POST['pemeriksaan_susunan_kepala'];
$pemeriksaan_susunan_wajah = $_POST['pemeriksaan_susunan_wajah'];
$pemeriksaan_susunan_leher = $_POST['pemeriksaan_susunan_leher'];
$pemeriksaan_susunan_kejang = $_POST['pemeriksaan_susunan_kejang'];
$pemeriksaan_susunan_sensorik = $_POST['pemeriksaan_susunan_sensorik'];
$pemeriksaan_kardiovaskuler_denyut_nadi = $_POST['pemeriksaan_kardiovaskuler_denyut_nadi'];
$pemeriksaan_kardiovaskuler_sirkulasi = $_POST['pemeriksaan_kardiovaskuler_sirkulasi'];
$pemeriksaan_kardiovaskuler_pulsasi = $_POST['pemeriksaan_kardiovaskuler_pulsasi'];
$pemeriksaan_respirasi_pola_nafas = $_POST['pemeriksaan_respirasi_pola_nafas'];
$pemeriksaan_respirasi_retraksi = $_POST['pemeriksaan_respirasi_retraksi'];
$pemeriksaan_respirasi_suara_nafas = $_POST['pemeriksaan_respirasi_suara_nafas'];
$pemeriksaan_respirasi_volume_pernafasan = $_POST['pemeriksaan_respirasi_volume_pernafasan'];
$pemeriksaan_respirasi_jenis_pernafasan = $_POST['pemeriksaan_respirasi_jenis_pernafasan'];
$pemeriksaan_respirasi_irama_nafas = $_POST['pemeriksaan_respirasi_irama_nafas'];
$pemeriksaan_respirasi_batuk = $_POST['pemeriksaan_respirasi_batuk'];
$pemeriksaan_gastrointestinal_mulut = $_POST['pemeriksaan_gastrointestinal_mulut'];
$pemeriksaan_gastrointestinal_gigi = $_POST['pemeriksaan_gastrointestinal_gigi'];
$pemeriksaan_gastrointestinal_lidah = $_POST['pemeriksaan_gastrointestinal_lidah'];
$pemeriksaan_gastrointestinal_tenggorokan = $_POST['pemeriksaan_gastrointestinal_tenggorokan'];
$pemeriksaan_gastrointestinal_abdomen = $_POST['pemeriksaan_gastrointestinal_abdomen'];
$pemeriksaan_gastrointestinal_peistatik_usus = $_POST['pemeriksaan_gastrointestinal_peistatik_usus'];
$pemeriksaan_gastrointestinal_anus = $_POST['pemeriksaan_gastrointestinal_anus'];
$pemeriksaan_neurologi_pengelihatan = $_POST['pemeriksaan_neurologi_pengelihatan'];
$pemeriksaan_neurologi_alat_bantu_penglihatan = $_POST['pemeriksaan_neurologi_alat_bantu_penglihatan'];
$pemeriksaan_neurologi_pendengaran = $_POST['pemeriksaan_neurologi_pendengaran'];
$pemeriksaan_neurologi_bicara = $_POST['pemeriksaan_neurologi_bicara'];
$pemeriksaan_neurologi_sensorik = $_POST['pemeriksaan_neurologi_sensorik'];
$pemeriksaan_neurologi_motorik = $_POST['pemeriksaan_neurologi_motorik'];
$pemeriksaan_neurologi_kekuatan_otot = $_POST['pemeriksaan_neurologi_kekuatan_otot'];
$pemeriksaan_integument_warnakulit = $_POST['pemeriksaan_integument_warnakulit'];
$pemeriksaan_integument_turgor = $_POST['pemeriksaan_integument_turgor'];
$pemeriksaan_integument_kulit = $_POST['pemeriksaan_integument_kulit'];
$pemeriksaan_integument_dekubitas = $_POST['pemeriksaan_integument_dekubitas'];
$pemeriksaan_muskuloskletal_pergerakan_sendi = $_POST['pemeriksaan_muskuloskletal_pergerakan_sendi'];
$pemeriksaan_muskuloskletal_kekauatan_otot = $_POST['pemeriksaan_muskuloskletal_kekauatan_otot'];
$pemeriksaan_muskuloskletal_nyeri_sendi = $_POST['pemeriksaan_muskuloskletal_nyeri_sendi'];
$pemeriksaan_muskuloskletal_oedema = $_POST['pemeriksaan_muskuloskletal_oedema'];
$pemeriksaan_muskuloskletal_fraktur = $_POST['pemeriksaan_muskuloskletal_fraktur'];
$pemeriksaan_eliminasi_bab_frekuensi_jumlah = $_POST['pemeriksaan_eliminasi_bab_frekuensi_jumlah'];
$pemeriksaan_eliminasi_bab_frekuensi_durasi = $_POST['pemeriksaan_eliminasi_bab_frekuensi_durasi'];
$pemeriksaan_eliminasi_bab_konsistensi = $_POST['pemeriksaan_eliminasi_bab_konsistensi'];
$pemeriksaan_eliminasi_bab_warna = $_POST['pemeriksaan_eliminasi_bab_warna'];
$pemeriksaan_eliminasi_bak_frekuensi_jumlah = $_POST['pemeriksaan_eliminasi_bak_frekuensi_jumlah'];
$pemeriksaan_eliminasi_bak_frekuensi_durasi = $_POST['pemeriksaan_eliminasi_bak_frekuensi_durasi'];
$pemeriksaan_eliminasi_bak_warna = $_POST['pemeriksaan_eliminasi_bak_warna'];
$pemeriksaan_eliminasi_bak_lainlain = $_POST['pemeriksaan_eliminasi_bak_lainlain'];
$pola_aktifitas_makanminum = $_POST['pola_aktifitas_makanminum'];
$pola_aktifitas_mandi = $_POST['pola_aktifitas_mandi'];
$pola_aktifitas_eliminasi = $_POST['pola_aktifitas_eliminasi'];
$pola_aktifitas_berpakaian = $_POST['pola_aktifitas_berpakaian'];
$pola_aktifitas_berpindah = $_POST['pola_aktifitas_berpindah'];
$pola_nutrisi_frekuesi_makan = $_POST['pola_nutrisi_frekuesi_makan'];
$pola_nutrisi_jenis_makanan = $_POST['pola_nutrisi_jenis_makanan'];
$pola_nutrisi_porsi_makan = $_POST['pola_nutrisi_porsi_makan'];
$pola_tidur_lama_tidur = $_POST['pola_tidur_lama_tidur'];
$pola_tidur_gangguan = $_POST['pola_tidur_gangguan'];
$pengkajian_fungsi_kemampuan_sehari = $_POST['pengkajian_fungsi_kemampuan_sehari'];
$pengkajian_fungsi_aktifitas = $_POST['pengkajian_fungsi_aktifitas'];
$pengkajian_fungsi_berjalan = $_POST['pengkajian_fungsi_berjalan'];
$pengkajian_fungsi_ambulasi = $_POST['pengkajian_fungsi_ambulasi'];
$pengkajian_fungsi_ekstrimitas_atas = $_POST['pengkajian_fungsi_ekstrimitas_atas'];
$pengkajian_fungsi_ekstrimitas_bawah = $_POST['pengkajian_fungsi_ekstrimitas_bawah'];
$pengkajian_fungsi_menggenggam = $_POST['pengkajian_fungsi_menggenggam'];
$pengkajian_fungsi_koordinasi = $_POST['pengkajian_fungsi_koordinasi'];
$pengkajian_fungsi_kesimpulan = $_POST['pengkajian_fungsi_kesimpulan'];
$riwayat_psiko_kondisi_psiko = $_POST['riwayat_psiko_kondisi_psiko'];
$riwayat_psiko_gangguan_jiwa = $_POST['riwayat_psiko_gangguan_jiwa'];
$riwayat_psiko_perilaku = $_POST['riwayat_psiko_perilaku'];
$riwayat_psiko_hubungan_keluarga = $_POST['riwayat_psiko_hubungan_keluarga'];
$riwayat_psiko_tinggal = $_POST['riwayat_psiko_tinggal'];
$riwayat_psiko_nilai_kepercayaan = $_POST['riwayat_psiko_nilai_kepercayaan'];
$riwayat_psiko_pendidikan_pj = $_POST['riwayat_psiko_pendidikan_pj'];
$riwayat_psiko_edukasi_diberikan = $_POST['riwayat_psiko_edukasi_diberikan'];
$penilaian_nyeri = $_POST['penilaian_nyeri'];
$penilaian_nyeri_penyebab = $_POST['penilaian_nyeri_penyebab'];
$penilaian_nyeri_kualitas = $_POST['penilaian_nyeri_kualitas'];
$penilaian_nyeri_lokasi = $_POST['penilaian_nyeri_lokasi'];
$penilaian_nyeri_menyebar = $_POST['penilaian_nyeri_menyebar'];
$penilaian_nyeri_skala = $_POST['penilaian_nyeri_skala'];
$penilaian_nyeri_waktu = $_POST['penilaian_nyeri_waktu'];
$penilaian_nyeri_hilang = $_POST['penilaian_nyeri_hilang'];
$penilaian_nyeri_diberitahukan_dokter = $_POST['penilaian_nyeri_diberitahukan_dokter'];
$penilaian_nyeri_jam_diberitahukan_dokter = $_POST['penilaian_nyeri_jam_diberitahukan_dokter'];
$penilaian_jatuhmorse_skala1 = $_POST['penilaian_jatuhmorse_skala1'];
$penilaian_jatuhmorse_nilai1 = $_POST['penilaian_jatuhmorse_nilai1'];
$penilaian_jatuhmorse_skala2 = $_POST['penilaian_jatuhmorse_skala2'];
$penilaian_jatuhmorse_nilai2 = $_POST['penilaian_jatuhmorse_nilai2'];
$penilaian_jatuhmorse_skala3 = $_POST['penilaian_jatuhmorse_skala3'];
$penilaian_jatuhmorse_nilai3 = $_POST['penilaian_jatuhmorse_nilai3'];
$penilaian_jatuhmorse_skala4 = $_POST['penilaian_jatuhmorse_skala4'];
$penilaian_jatuhmorse_nilai4 = $_POST['penilaian_jatuhmorse_nilai4'];
$penilaian_jatuhmorse_skala5 = $_POST['penilaian_jatuhmorse_skala5'];
$penilaian_jatuhmorse_nilai5 = $_POST['penilaian_jatuhmorse_nilai5'];
$penilaian_jatuhmorse_skala6 = $_POST['penilaian_jatuhmorse_skala6'];
$penilaian_jatuhmorse_nilai6 = $_POST['penilaian_jatuhmorse_nilai6'];
$penilaian_jatuhmorse_totalnilai = $_POST['penilaian_jatuhmorse_totalnilai'];
$penilaian_jatuhsydney_skala1 = $_POST['penilaian_jatuhsydney_skala1'];
$penilaian_jatuhsydney_nilai1 = $_POST['penilaian_jatuhsydney_nilai1'];
$penilaian_jatuhsydney_skala2 = $_POST['penilaian_jatuhsydney_skala2'];
$penilaian_jatuhsydney_nilai2 = $_POST['penilaian_jatuhsydney_nilai2'];
$penilaian_jatuhsydney_skala3 = $_POST['penilaian_jatuhsydney_skala3'];
$penilaian_jatuhsydney_nilai3 = $_POST['penilaian_jatuhsydney_nilai3'];
$penilaian_jatuhsydney_skala4 = $_POST['penilaian_jatuhsydney_skala4'];
$penilaian_jatuhsydney_nilai4 = $_POST['penilaian_jatuhsydney_nilai4'];
$penilaian_jatuhsydney_skala5 = $_POST['penilaian_jatuhsydney_skala5'];
$penilaian_jatuhsydney_nilai5 = $_POST['penilaian_jatuhsydney_nilai5'];
$penilaian_jatuhsydney_skala6 = $_POST['penilaian_jatuhsydney_skala6'];
$penilaian_jatuhsydney_nilai6 = $_POST['penilaian_jatuhsydney_nilai6'];
$penilaian_jatuhsydney_skala7 = $_POST['penilaian_jatuhsydney_skala7'];
$penilaian_jatuhsydney_nilai7 = $_POST['penilaian_jatuhsydney_nilai7'];
$penilaian_jatuhsydney_skala8 = $_POST['penilaian_jatuhsydney_skala8'];
$penilaian_jatuhsydney_nilai8 = $_POST['penilaian_jatuhsydney_nilai8'];
$penilaian_jatuhsydney_skala9 = $_POST['penilaian_jatuhsydney_skala9'];
$penilaian_jatuhsydney_nilai9 = $_POST['penilaian_jatuhsydney_nilai9'];
$penilaian_jatuhsydney_skala10 = $_POST['penilaian_jatuhsydney_skala10'];
$penilaian_jatuhsydney_nilai10 = $_POST['penilaian_jatuhsydney_nilai10'];
$penilaian_jatuhsydney_skala11 = $_POST['penilaian_jatuhsydney_skala11'];
$penilaian_jatuhsydney_nilai11 = $_POST['penilaian_jatuhsydney_nilai11'];
$penilaian_jatuhsydney_totalnilai = $_POST['penilaian_jatuhsydney_totalnilai'];
$skrining_gizi1 = $_POST['skrining_gizi1'];
$nilai_gizi1 = $_POST['nilai_gizi1'];
$skrining_gizi2 = $_POST['skrining_gizi2'];
$nilai_gizi2 = $_POST['nilai_gizi2'];
$nilai_total_gizi = $_POST['nilai_total_gizi'];
$skrining_gizi_diagnosa_khusus = $_POST['skrining_gizi_diagnosa_khusus'];
$skrining_gizi_diketahui_dietisen = $_POST['skrining_gizi_diketahui_dietisen'];
$skrining_gizi_jam_diketahui_dietisen = $_POST['skrining_gizi_jam_diketahui_dietisen'];
$rencana = $_POST['rencana'];
$nip1 = $_POST['nip1'];
$nip2 = $_POST['nip2'];
$kd_dokter = $_POST['kd_dokter'];


        // BUANG FIELD PERTAMA

            $mlite_penilaian_awal_keperawatan_ranap_edit = $this->db()->pdo()->prepare("UPDATE mlite_penilaian_awal_keperawatan_ranap SET no_rawat=?, tanggal=?, informasi=?, ket_informasi=?, tiba_diruang_rawat=?, kasus_trauma=?, cara_masuk=?, rps=?, rpd=?, rpk=?, rpo=?, riwayat_pembedahan=?, riwayat_dirawat_dirs=?, alat_bantu_dipakai=?, riwayat_kehamilan=?, riwayat_kehamilan_perkiraan=?, riwayat_tranfusi=?, riwayat_alergi=?, riwayat_merokok=?, riwayat_merokok_jumlah=?, riwayat_alkohol=?, riwayat_alkohol_jumlah=?, riwayat_narkoba=?, riwayat_olahraga=?, pemeriksaan_mental=?, pemeriksaan_keadaan_umum=?, pemeriksaan_gcs=?, pemeriksaan_td=?, pemeriksaan_nadi=?, pemeriksaan_rr=?, pemeriksaan_suhu=?, pemeriksaan_spo2=?, pemeriksaan_bb=?, pemeriksaan_tb=?, pemeriksaan_susunan_kepala=?, pemeriksaan_susunan_wajah=?, pemeriksaan_susunan_leher=?, pemeriksaan_susunan_kejang=?, pemeriksaan_susunan_sensorik=?, pemeriksaan_kardiovaskuler_denyut_nadi=?, pemeriksaan_kardiovaskuler_sirkulasi=?, pemeriksaan_kardiovaskuler_pulsasi=?, pemeriksaan_respirasi_pola_nafas=?, pemeriksaan_respirasi_retraksi=?, pemeriksaan_respirasi_suara_nafas=?, pemeriksaan_respirasi_volume_pernafasan=?, pemeriksaan_respirasi_jenis_pernafasan=?, pemeriksaan_respirasi_irama_nafas=?, pemeriksaan_respirasi_batuk=?, pemeriksaan_gastrointestinal_mulut=?, pemeriksaan_gastrointestinal_gigi=?, pemeriksaan_gastrointestinal_lidah=?, pemeriksaan_gastrointestinal_tenggorokan=?, pemeriksaan_gastrointestinal_abdomen=?, pemeriksaan_gastrointestinal_peistatik_usus=?, pemeriksaan_gastrointestinal_anus=?, pemeriksaan_neurologi_pengelihatan=?, pemeriksaan_neurologi_alat_bantu_penglihatan=?, pemeriksaan_neurologi_pendengaran=?, pemeriksaan_neurologi_bicara=?, pemeriksaan_neurologi_sensorik=?, pemeriksaan_neurologi_motorik=?, pemeriksaan_neurologi_kekuatan_otot=?, pemeriksaan_integument_warnakulit=?, pemeriksaan_integument_turgor=?, pemeriksaan_integument_kulit=?, pemeriksaan_integument_dekubitas=?, pemeriksaan_muskuloskletal_pergerakan_sendi=?, pemeriksaan_muskuloskletal_kekauatan_otot=?, pemeriksaan_muskuloskletal_nyeri_sendi=?, pemeriksaan_muskuloskletal_oedema=?, pemeriksaan_muskuloskletal_fraktur=?, pemeriksaan_eliminasi_bab_frekuensi_jumlah=?, pemeriksaan_eliminasi_bab_frekuensi_durasi=?, pemeriksaan_eliminasi_bab_konsistensi=?, pemeriksaan_eliminasi_bab_warna=?, pemeriksaan_eliminasi_bak_frekuensi_jumlah=?, pemeriksaan_eliminasi_bak_frekuensi_durasi=?, pemeriksaan_eliminasi_bak_warna=?, pemeriksaan_eliminasi_bak_lainlain=?, pola_aktifitas_makanminum=?, pola_aktifitas_mandi=?, pola_aktifitas_eliminasi=?, pola_aktifitas_berpakaian=?, pola_aktifitas_berpindah=?, pola_nutrisi_frekuesi_makan=?, pola_nutrisi_jenis_makanan=?, pola_nutrisi_porsi_makan=?, pola_tidur_lama_tidur=?, pola_tidur_gangguan=?, pengkajian_fungsi_kemampuan_sehari=?, pengkajian_fungsi_aktifitas=?, pengkajian_fungsi_berjalan=?, pengkajian_fungsi_ambulasi=?, pengkajian_fungsi_ekstrimitas_atas=?, pengkajian_fungsi_ekstrimitas_bawah=?, pengkajian_fungsi_menggenggam=?, pengkajian_fungsi_koordinasi=?, pengkajian_fungsi_kesimpulan=?, riwayat_psiko_kondisi_psiko=?, riwayat_psiko_gangguan_jiwa=?, riwayat_psiko_perilaku=?, riwayat_psiko_hubungan_keluarga=?, riwayat_psiko_tinggal=?, riwayat_psiko_nilai_kepercayaan=?, riwayat_psiko_pendidikan_pj=?, riwayat_psiko_edukasi_diberikan=?, penilaian_nyeri=?, penilaian_nyeri_penyebab=?, penilaian_nyeri_kualitas=?, penilaian_nyeri_lokasi=?, penilaian_nyeri_menyebar=?, penilaian_nyeri_skala=?, penilaian_nyeri_waktu=?, penilaian_nyeri_hilang=?, penilaian_nyeri_diberitahukan_dokter=?, penilaian_nyeri_jam_diberitahukan_dokter=?, penilaian_jatuhmorse_skala1=?, penilaian_jatuhmorse_nilai1=?, penilaian_jatuhmorse_skala2=?, penilaian_jatuhmorse_nilai2=?, penilaian_jatuhmorse_skala3=?, penilaian_jatuhmorse_nilai3=?, penilaian_jatuhmorse_skala4=?, penilaian_jatuhmorse_nilai4=?, penilaian_jatuhmorse_skala5=?, penilaian_jatuhmorse_nilai5=?, penilaian_jatuhmorse_skala6=?, penilaian_jatuhmorse_nilai6=?, penilaian_jatuhmorse_totalnilai=?, penilaian_jatuhsydney_skala1=?, penilaian_jatuhsydney_nilai1=?, penilaian_jatuhsydney_skala2=?, penilaian_jatuhsydney_nilai2=?, penilaian_jatuhsydney_skala3=?, penilaian_jatuhsydney_nilai3=?, penilaian_jatuhsydney_skala4=?, penilaian_jatuhsydney_nilai4=?, penilaian_jatuhsydney_skala5=?, penilaian_jatuhsydney_nilai5=?, penilaian_jatuhsydney_skala6=?, penilaian_jatuhsydney_nilai6=?, penilaian_jatuhsydney_skala7=?, penilaian_jatuhsydney_nilai7=?, penilaian_jatuhsydney_skala8=?, penilaian_jatuhsydney_nilai8=?, penilaian_jatuhsydney_skala9=?, penilaian_jatuhsydney_nilai9=?, penilaian_jatuhsydney_skala10=?, penilaian_jatuhsydney_nilai10=?, penilaian_jatuhsydney_skala11=?, penilaian_jatuhsydney_nilai11=?, penilaian_jatuhsydney_totalnilai=?, skrining_gizi1=?, nilai_gizi1=?, skrining_gizi2=?, nilai_gizi2=?, nilai_total_gizi=?, skrining_gizi_diagnosa_khusus=?, skrining_gizi_diketahui_dietisen=?, skrining_gizi_jam_diketahui_dietisen=?, rencana=?, nip1=?, nip2=?, kd_dokter=? WHERE no_rawat=?");
            $mlite_penilaian_awal_keperawatan_ranap_edit->execute([$no_rawat, $tanggal, $informasi, $ket_informasi, $tiba_diruang_rawat, $kasus_trauma, $cara_masuk, $rps, $rpd, $rpk, $rpo, $riwayat_pembedahan, $riwayat_dirawat_dirs, $alat_bantu_dipakai, $riwayat_kehamilan, $riwayat_kehamilan_perkiraan, $riwayat_tranfusi, $riwayat_alergi, $riwayat_merokok, $riwayat_merokok_jumlah, $riwayat_alkohol, $riwayat_alkohol_jumlah, $riwayat_narkoba, $riwayat_olahraga, $pemeriksaan_mental, $pemeriksaan_keadaan_umum, $pemeriksaan_gcs, $pemeriksaan_td, $pemeriksaan_nadi, $pemeriksaan_rr, $pemeriksaan_suhu, $pemeriksaan_spo2, $pemeriksaan_bb, $pemeriksaan_tb, $pemeriksaan_susunan_kepala, $pemeriksaan_susunan_wajah, $pemeriksaan_susunan_leher, $pemeriksaan_susunan_kejang, $pemeriksaan_susunan_sensorik, $pemeriksaan_kardiovaskuler_denyut_nadi, $pemeriksaan_kardiovaskuler_sirkulasi, $pemeriksaan_kardiovaskuler_pulsasi, $pemeriksaan_respirasi_pola_nafas, $pemeriksaan_respirasi_retraksi, $pemeriksaan_respirasi_suara_nafas, $pemeriksaan_respirasi_volume_pernafasan, $pemeriksaan_respirasi_jenis_pernafasan, $pemeriksaan_respirasi_irama_nafas, $pemeriksaan_respirasi_batuk, $pemeriksaan_gastrointestinal_mulut, $pemeriksaan_gastrointestinal_gigi, $pemeriksaan_gastrointestinal_lidah, $pemeriksaan_gastrointestinal_tenggorokan, $pemeriksaan_gastrointestinal_abdomen, $pemeriksaan_gastrointestinal_peistatik_usus, $pemeriksaan_gastrointestinal_anus, $pemeriksaan_neurologi_pengelihatan, $pemeriksaan_neurologi_alat_bantu_penglihatan, $pemeriksaan_neurologi_pendengaran, $pemeriksaan_neurologi_bicara, $pemeriksaan_neurologi_sensorik, $pemeriksaan_neurologi_motorik, $pemeriksaan_neurologi_kekuatan_otot, $pemeriksaan_integument_warnakulit, $pemeriksaan_integument_turgor, $pemeriksaan_integument_kulit, $pemeriksaan_integument_dekubitas, $pemeriksaan_muskuloskletal_pergerakan_sendi, $pemeriksaan_muskuloskletal_kekauatan_otot, $pemeriksaan_muskuloskletal_nyeri_sendi, $pemeriksaan_muskuloskletal_oedema, $pemeriksaan_muskuloskletal_fraktur, $pemeriksaan_eliminasi_bab_frekuensi_jumlah, $pemeriksaan_eliminasi_bab_frekuensi_durasi, $pemeriksaan_eliminasi_bab_konsistensi, $pemeriksaan_eliminasi_bab_warna, $pemeriksaan_eliminasi_bak_frekuensi_jumlah, $pemeriksaan_eliminasi_bak_frekuensi_durasi, $pemeriksaan_eliminasi_bak_warna, $pemeriksaan_eliminasi_bak_lainlain, $pola_aktifitas_makanminum, $pola_aktifitas_mandi, $pola_aktifitas_eliminasi, $pola_aktifitas_berpakaian, $pola_aktifitas_berpindah, $pola_nutrisi_frekuesi_makan, $pola_nutrisi_jenis_makanan, $pola_nutrisi_porsi_makan, $pola_tidur_lama_tidur, $pola_tidur_gangguan, $pengkajian_fungsi_kemampuan_sehari, $pengkajian_fungsi_aktifitas, $pengkajian_fungsi_berjalan, $pengkajian_fungsi_ambulasi, $pengkajian_fungsi_ekstrimitas_atas, $pengkajian_fungsi_ekstrimitas_bawah, $pengkajian_fungsi_menggenggam, $pengkajian_fungsi_koordinasi, $pengkajian_fungsi_kesimpulan, $riwayat_psiko_kondisi_psiko, $riwayat_psiko_gangguan_jiwa, $riwayat_psiko_perilaku, $riwayat_psiko_hubungan_keluarga, $riwayat_psiko_tinggal, $riwayat_psiko_nilai_kepercayaan, $riwayat_psiko_pendidikan_pj, $riwayat_psiko_edukasi_diberikan, $penilaian_nyeri, $penilaian_nyeri_penyebab, $penilaian_nyeri_kualitas, $penilaian_nyeri_lokasi, $penilaian_nyeri_menyebar, $penilaian_nyeri_skala, $penilaian_nyeri_waktu, $penilaian_nyeri_hilang, $penilaian_nyeri_diberitahukan_dokter, $penilaian_nyeri_jam_diberitahukan_dokter, $penilaian_jatuhmorse_skala1, $penilaian_jatuhmorse_nilai1, $penilaian_jatuhmorse_skala2, $penilaian_jatuhmorse_nilai2, $penilaian_jatuhmorse_skala3, $penilaian_jatuhmorse_nilai3, $penilaian_jatuhmorse_skala4, $penilaian_jatuhmorse_nilai4, $penilaian_jatuhmorse_skala5, $penilaian_jatuhmorse_nilai5, $penilaian_jatuhmorse_skala6, $penilaian_jatuhmorse_nilai6, $penilaian_jatuhmorse_totalnilai, $penilaian_jatuhsydney_skala1, $penilaian_jatuhsydney_nilai1, $penilaian_jatuhsydney_skala2, $penilaian_jatuhsydney_nilai2, $penilaian_jatuhsydney_skala3, $penilaian_jatuhsydney_nilai3, $penilaian_jatuhsydney_skala4, $penilaian_jatuhsydney_nilai4, $penilaian_jatuhsydney_skala5, $penilaian_jatuhsydney_nilai5, $penilaian_jatuhsydney_skala6, $penilaian_jatuhsydney_nilai6, $penilaian_jatuhsydney_skala7, $penilaian_jatuhsydney_nilai7, $penilaian_jatuhsydney_skala8, $penilaian_jatuhsydney_nilai8, $penilaian_jatuhsydney_skala9, $penilaian_jatuhsydney_nilai9, $penilaian_jatuhsydney_skala10, $penilaian_jatuhsydney_nilai10, $penilaian_jatuhsydney_skala11, $penilaian_jatuhsydney_nilai11, $penilaian_jatuhsydney_totalnilai, $skrining_gizi1, $nilai_gizi1, $skrining_gizi2, $nilai_gizi2, $nilai_total_gizi, $skrining_gizi_diagnosa_khusus, $skrining_gizi_diketahui_dietisen, $skrining_gizi_jam_diketahui_dietisen, $rencana, $nip1, $nip2, $kd_dokter,$no_rawat]);
        
        }

        if ($act=="del") {
            $no_rawat= $_POST['no_rawat'];
            $check_db = $this->db()->pdo()->prepare("DELETE FROM mlite_penilaian_awal_keperawatan_ranap WHERE no_rawat='$no_rawat'");
            $result = $check_db->execute();
            $error = $check_db->errorInfo();
            if (!empty($result)){
              $data = array(
                'status' => 'success', 
                'msg' => $no_rkm_medis
              );
            } else {
              $data = array(
                'status' => 'error', 
                'msg' => $error['2']
              );
            }
            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            $search_field_mlite_penilaian_awal_keperawatan_ranap= $_POST['search_field_mlite_penilaian_awal_keperawatan_ranap'];
            $search_text_mlite_penilaian_awal_keperawatan_ranap = $_POST['search_text_mlite_penilaian_awal_keperawatan_ranap'];

            $searchQuery = " ";
            if($search_text_mlite_penilaian_awal_keperawatan_ranap != ''){
                $searchQuery .= " and (".$search_field_mlite_penilaian_awal_keperawatan_ranap." like '%".$search_text_mlite_penilaian_awal_keperawatan_ranap."%' ) ";
            }

            $user_lihat = $this->db()->pdo()->prepare("SELECT * from mlite_penilaian_awal_keperawatan_ranap WHERE 1 ".$searchQuery);
            $user_lihat->execute();
            $result = $user_lihat->fetchAll(\PDO::FETCH_ASSOC);

            $data = array();

            foreach($result as $row) {
                $data[] = array(
                    'no_rawat'=>$row['no_rawat'],
'tanggal'=>$row['tanggal'],
'informasi'=>$row['informasi'],
'ket_informasi'=>$row['ket_informasi'],
'tiba_diruang_rawat'=>$row['tiba_diruang_rawat'],
'kasus_trauma'=>$row['kasus_trauma'],
'cara_masuk'=>$row['cara_masuk'],
'rps'=>$row['rps'],
'rpd'=>$row['rpd'],
'rpk'=>$row['rpk'],
'rpo'=>$row['rpo'],
'riwayat_pembedahan'=>$row['riwayat_pembedahan'],
'riwayat_dirawat_dirs'=>$row['riwayat_dirawat_dirs'],
'alat_bantu_dipakai'=>$row['alat_bantu_dipakai'],
'riwayat_kehamilan'=>$row['riwayat_kehamilan'],
'riwayat_kehamilan_perkiraan'=>$row['riwayat_kehamilan_perkiraan'],
'riwayat_tranfusi'=>$row['riwayat_tranfusi'],
'riwayat_alergi'=>$row['riwayat_alergi'],
'riwayat_merokok'=>$row['riwayat_merokok'],
'riwayat_merokok_jumlah'=>$row['riwayat_merokok_jumlah'],
'riwayat_alkohol'=>$row['riwayat_alkohol'],
'riwayat_alkohol_jumlah'=>$row['riwayat_alkohol_jumlah'],
'riwayat_narkoba'=>$row['riwayat_narkoba'],
'riwayat_olahraga'=>$row['riwayat_olahraga'],
'pemeriksaan_mental'=>$row['pemeriksaan_mental'],
'pemeriksaan_keadaan_umum'=>$row['pemeriksaan_keadaan_umum'],
'pemeriksaan_gcs'=>$row['pemeriksaan_gcs'],
'pemeriksaan_td'=>$row['pemeriksaan_td'],
'pemeriksaan_nadi'=>$row['pemeriksaan_nadi'],
'pemeriksaan_rr'=>$row['pemeriksaan_rr'],
'pemeriksaan_suhu'=>$row['pemeriksaan_suhu'],
'pemeriksaan_spo2'=>$row['pemeriksaan_spo2'],
'pemeriksaan_bb'=>$row['pemeriksaan_bb'],
'pemeriksaan_tb'=>$row['pemeriksaan_tb'],
'pemeriksaan_susunan_kepala'=>$row['pemeriksaan_susunan_kepala'],
'pemeriksaan_susunan_wajah'=>$row['pemeriksaan_susunan_wajah'],
'pemeriksaan_susunan_leher'=>$row['pemeriksaan_susunan_leher'],
'pemeriksaan_susunan_kejang'=>$row['pemeriksaan_susunan_kejang'],
'pemeriksaan_susunan_sensorik'=>$row['pemeriksaan_susunan_sensorik'],
'pemeriksaan_kardiovaskuler_denyut_nadi'=>$row['pemeriksaan_kardiovaskuler_denyut_nadi'],
'pemeriksaan_kardiovaskuler_sirkulasi'=>$row['pemeriksaan_kardiovaskuler_sirkulasi'],
'pemeriksaan_kardiovaskuler_pulsasi'=>$row['pemeriksaan_kardiovaskuler_pulsasi'],
'pemeriksaan_respirasi_pola_nafas'=>$row['pemeriksaan_respirasi_pola_nafas'],
'pemeriksaan_respirasi_retraksi'=>$row['pemeriksaan_respirasi_retraksi'],
'pemeriksaan_respirasi_suara_nafas'=>$row['pemeriksaan_respirasi_suara_nafas'],
'pemeriksaan_respirasi_volume_pernafasan'=>$row['pemeriksaan_respirasi_volume_pernafasan'],
'pemeriksaan_respirasi_jenis_pernafasan'=>$row['pemeriksaan_respirasi_jenis_pernafasan'],
'pemeriksaan_respirasi_irama_nafas'=>$row['pemeriksaan_respirasi_irama_nafas'],
'pemeriksaan_respirasi_batuk'=>$row['pemeriksaan_respirasi_batuk'],
'pemeriksaan_gastrointestinal_mulut'=>$row['pemeriksaan_gastrointestinal_mulut'],
'pemeriksaan_gastrointestinal_gigi'=>$row['pemeriksaan_gastrointestinal_gigi'],
'pemeriksaan_gastrointestinal_lidah'=>$row['pemeriksaan_gastrointestinal_lidah'],
'pemeriksaan_gastrointestinal_tenggorokan'=>$row['pemeriksaan_gastrointestinal_tenggorokan'],
'pemeriksaan_gastrointestinal_abdomen'=>$row['pemeriksaan_gastrointestinal_abdomen'],
'pemeriksaan_gastrointestinal_peistatik_usus'=>$row['pemeriksaan_gastrointestinal_peistatik_usus'],
'pemeriksaan_gastrointestinal_anus'=>$row['pemeriksaan_gastrointestinal_anus'],
'pemeriksaan_neurologi_pengelihatan'=>$row['pemeriksaan_neurologi_pengelihatan'],
'pemeriksaan_neurologi_alat_bantu_penglihatan'=>$row['pemeriksaan_neurologi_alat_bantu_penglihatan'],
'pemeriksaan_neurologi_pendengaran'=>$row['pemeriksaan_neurologi_pendengaran'],
'pemeriksaan_neurologi_bicara'=>$row['pemeriksaan_neurologi_bicara'],
'pemeriksaan_neurologi_sensorik'=>$row['pemeriksaan_neurologi_sensorik'],
'pemeriksaan_neurologi_motorik'=>$row['pemeriksaan_neurologi_motorik'],
'pemeriksaan_neurologi_kekuatan_otot'=>$row['pemeriksaan_neurologi_kekuatan_otot'],
'pemeriksaan_integument_warnakulit'=>$row['pemeriksaan_integument_warnakulit'],
'pemeriksaan_integument_turgor'=>$row['pemeriksaan_integument_turgor'],
'pemeriksaan_integument_kulit'=>$row['pemeriksaan_integument_kulit'],
'pemeriksaan_integument_dekubitas'=>$row['pemeriksaan_integument_dekubitas'],
'pemeriksaan_muskuloskletal_pergerakan_sendi'=>$row['pemeriksaan_muskuloskletal_pergerakan_sendi'],
'pemeriksaan_muskuloskletal_kekauatan_otot'=>$row['pemeriksaan_muskuloskletal_kekauatan_otot'],
'pemeriksaan_muskuloskletal_nyeri_sendi'=>$row['pemeriksaan_muskuloskletal_nyeri_sendi'],
'pemeriksaan_muskuloskletal_oedema'=>$row['pemeriksaan_muskuloskletal_oedema'],
'pemeriksaan_muskuloskletal_fraktur'=>$row['pemeriksaan_muskuloskletal_fraktur'],
'pemeriksaan_eliminasi_bab_frekuensi_jumlah'=>$row['pemeriksaan_eliminasi_bab_frekuensi_jumlah'],
'pemeriksaan_eliminasi_bab_frekuensi_durasi'=>$row['pemeriksaan_eliminasi_bab_frekuensi_durasi'],
'pemeriksaan_eliminasi_bab_konsistensi'=>$row['pemeriksaan_eliminasi_bab_konsistensi'],
'pemeriksaan_eliminasi_bab_warna'=>$row['pemeriksaan_eliminasi_bab_warna'],
'pemeriksaan_eliminasi_bak_frekuensi_jumlah'=>$row['pemeriksaan_eliminasi_bak_frekuensi_jumlah'],
'pemeriksaan_eliminasi_bak_frekuensi_durasi'=>$row['pemeriksaan_eliminasi_bak_frekuensi_durasi'],
'pemeriksaan_eliminasi_bak_warna'=>$row['pemeriksaan_eliminasi_bak_warna'],
'pemeriksaan_eliminasi_bak_lainlain'=>$row['pemeriksaan_eliminasi_bak_lainlain'],
'pola_aktifitas_makanminum'=>$row['pola_aktifitas_makanminum'],
'pola_aktifitas_mandi'=>$row['pola_aktifitas_mandi'],
'pola_aktifitas_eliminasi'=>$row['pola_aktifitas_eliminasi'],
'pola_aktifitas_berpakaian'=>$row['pola_aktifitas_berpakaian'],
'pola_aktifitas_berpindah'=>$row['pola_aktifitas_berpindah'],
'pola_nutrisi_frekuesi_makan'=>$row['pola_nutrisi_frekuesi_makan'],
'pola_nutrisi_jenis_makanan'=>$row['pola_nutrisi_jenis_makanan'],
'pola_nutrisi_porsi_makan'=>$row['pola_nutrisi_porsi_makan'],
'pola_tidur_lama_tidur'=>$row['pola_tidur_lama_tidur'],
'pola_tidur_gangguan'=>$row['pola_tidur_gangguan'],
'pengkajian_fungsi_kemampuan_sehari'=>$row['pengkajian_fungsi_kemampuan_sehari'],
'pengkajian_fungsi_aktifitas'=>$row['pengkajian_fungsi_aktifitas'],
'pengkajian_fungsi_berjalan'=>$row['pengkajian_fungsi_berjalan'],
'pengkajian_fungsi_ambulasi'=>$row['pengkajian_fungsi_ambulasi'],
'pengkajian_fungsi_ekstrimitas_atas'=>$row['pengkajian_fungsi_ekstrimitas_atas'],
'pengkajian_fungsi_ekstrimitas_bawah'=>$row['pengkajian_fungsi_ekstrimitas_bawah'],
'pengkajian_fungsi_menggenggam'=>$row['pengkajian_fungsi_menggenggam'],
'pengkajian_fungsi_koordinasi'=>$row['pengkajian_fungsi_koordinasi'],
'pengkajian_fungsi_kesimpulan'=>$row['pengkajian_fungsi_kesimpulan'],
'riwayat_psiko_kondisi_psiko'=>$row['riwayat_psiko_kondisi_psiko'],
'riwayat_psiko_gangguan_jiwa'=>$row['riwayat_psiko_gangguan_jiwa'],
'riwayat_psiko_perilaku'=>$row['riwayat_psiko_perilaku'],
'riwayat_psiko_hubungan_keluarga'=>$row['riwayat_psiko_hubungan_keluarga'],
'riwayat_psiko_tinggal'=>$row['riwayat_psiko_tinggal'],
'riwayat_psiko_nilai_kepercayaan'=>$row['riwayat_psiko_nilai_kepercayaan'],
'riwayat_psiko_pendidikan_pj'=>$row['riwayat_psiko_pendidikan_pj'],
'riwayat_psiko_edukasi_diberikan'=>$row['riwayat_psiko_edukasi_diberikan'],
'penilaian_nyeri'=>$row['penilaian_nyeri'],
'penilaian_nyeri_penyebab'=>$row['penilaian_nyeri_penyebab'],
'penilaian_nyeri_kualitas'=>$row['penilaian_nyeri_kualitas'],
'penilaian_nyeri_lokasi'=>$row['penilaian_nyeri_lokasi'],
'penilaian_nyeri_menyebar'=>$row['penilaian_nyeri_menyebar'],
'penilaian_nyeri_skala'=>$row['penilaian_nyeri_skala'],
'penilaian_nyeri_waktu'=>$row['penilaian_nyeri_waktu'],
'penilaian_nyeri_hilang'=>$row['penilaian_nyeri_hilang'],
'penilaian_nyeri_diberitahukan_dokter'=>$row['penilaian_nyeri_diberitahukan_dokter'],
'penilaian_nyeri_jam_diberitahukan_dokter'=>$row['penilaian_nyeri_jam_diberitahukan_dokter'],
'penilaian_jatuhmorse_skala1'=>$row['penilaian_jatuhmorse_skala1'],
'penilaian_jatuhmorse_nilai1'=>$row['penilaian_jatuhmorse_nilai1'],
'penilaian_jatuhmorse_skala2'=>$row['penilaian_jatuhmorse_skala2'],
'penilaian_jatuhmorse_nilai2'=>$row['penilaian_jatuhmorse_nilai2'],
'penilaian_jatuhmorse_skala3'=>$row['penilaian_jatuhmorse_skala3'],
'penilaian_jatuhmorse_nilai3'=>$row['penilaian_jatuhmorse_nilai3'],
'penilaian_jatuhmorse_skala4'=>$row['penilaian_jatuhmorse_skala4'],
'penilaian_jatuhmorse_nilai4'=>$row['penilaian_jatuhmorse_nilai4'],
'penilaian_jatuhmorse_skala5'=>$row['penilaian_jatuhmorse_skala5'],
'penilaian_jatuhmorse_nilai5'=>$row['penilaian_jatuhmorse_nilai5'],
'penilaian_jatuhmorse_skala6'=>$row['penilaian_jatuhmorse_skala6'],
'penilaian_jatuhmorse_nilai6'=>$row['penilaian_jatuhmorse_nilai6'],
'penilaian_jatuhmorse_totalnilai'=>$row['penilaian_jatuhmorse_totalnilai'],
'penilaian_jatuhsydney_skala1'=>$row['penilaian_jatuhsydney_skala1'],
'penilaian_jatuhsydney_nilai1'=>$row['penilaian_jatuhsydney_nilai1'],
'penilaian_jatuhsydney_skala2'=>$row['penilaian_jatuhsydney_skala2'],
'penilaian_jatuhsydney_nilai2'=>$row['penilaian_jatuhsydney_nilai2'],
'penilaian_jatuhsydney_skala3'=>$row['penilaian_jatuhsydney_skala3'],
'penilaian_jatuhsydney_nilai3'=>$row['penilaian_jatuhsydney_nilai3'],
'penilaian_jatuhsydney_skala4'=>$row['penilaian_jatuhsydney_skala4'],
'penilaian_jatuhsydney_nilai4'=>$row['penilaian_jatuhsydney_nilai4'],
'penilaian_jatuhsydney_skala5'=>$row['penilaian_jatuhsydney_skala5'],
'penilaian_jatuhsydney_nilai5'=>$row['penilaian_jatuhsydney_nilai5'],
'penilaian_jatuhsydney_skala6'=>$row['penilaian_jatuhsydney_skala6'],
'penilaian_jatuhsydney_nilai6'=>$row['penilaian_jatuhsydney_nilai6'],
'penilaian_jatuhsydney_skala7'=>$row['penilaian_jatuhsydney_skala7'],
'penilaian_jatuhsydney_nilai7'=>$row['penilaian_jatuhsydney_nilai7'],
'penilaian_jatuhsydney_skala8'=>$row['penilaian_jatuhsydney_skala8'],
'penilaian_jatuhsydney_nilai8'=>$row['penilaian_jatuhsydney_nilai8'],
'penilaian_jatuhsydney_skala9'=>$row['penilaian_jatuhsydney_skala9'],
'penilaian_jatuhsydney_nilai9'=>$row['penilaian_jatuhsydney_nilai9'],
'penilaian_jatuhsydney_skala10'=>$row['penilaian_jatuhsydney_skala10'],
'penilaian_jatuhsydney_nilai10'=>$row['penilaian_jatuhsydney_nilai10'],
'penilaian_jatuhsydney_skala11'=>$row['penilaian_jatuhsydney_skala11'],
'penilaian_jatuhsydney_nilai11'=>$row['penilaian_jatuhsydney_nilai11'],
'penilaian_jatuhsydney_totalnilai'=>$row['penilaian_jatuhsydney_totalnilai'],
'skrining_gizi1'=>$row['skrining_gizi1'],
'nilai_gizi1'=>$row['nilai_gizi1'],
'skrining_gizi2'=>$row['skrining_gizi2'],
'nilai_gizi2'=>$row['nilai_gizi2'],
'nilai_total_gizi'=>$row['nilai_total_gizi'],
'skrining_gizi_diagnosa_khusus'=>$row['skrining_gizi_diagnosa_khusus'],
'skrining_gizi_diketahui_dietisen'=>$row['skrining_gizi_diketahui_dietisen'],
'skrining_gizi_jam_diketahui_dietisen'=>$row['skrining_gizi_jam_diketahui_dietisen'],
'rencana'=>$row['rencana'],
'nip1'=>$row['nip1'],
'nip2'=>$row['nip2'],
'kd_dokter'=>$row['kd_dokter']
                );
            }

            echo json_encode($data);
        }
        exit();
    }

    public function getDetail($no_rawat)
    {
        $detail = $this->db('mlite_penilaian_awal_keperawatan_ranap')->where('no_rawat', $no_rawat)->toArray();
        $settings =  $this->settings('settings');
        echo $this->draw('detail.html', ['detail' => $detail, 'settings' => $settings]);
        exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/penilaian_keperawatan_ranap/css/admin/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/penilaian_keperawatan_ranap/js/admin/scripts.js', ['settings' => $settings]);
        exit();
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/css/datatables.min.css'));
        $this->core->addJS(url('assets/jscripts/jqueryvalidation.js'));
        $this->core->addJS(url('assets/jscripts/xlsx.js'));
        $this->core->addJS(url('assets/jscripts/jspdf.min.js'));
        $this->core->addJS(url('assets/jscripts/jspdf.plugin.autotable.min.js'));
        $this->core->addJS(url('assets/jscripts/datatables.min.js'));

        $this->core->addCSS(url([ADMIN, 'penilaian_keperawatan_ranap', 'css']));
        $this->core->addJS(url([ADMIN, 'penilaian_keperawatan_ranap', 'javascript']), 'footer');
    }

}
