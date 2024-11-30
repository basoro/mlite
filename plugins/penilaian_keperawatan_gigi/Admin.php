<?php
namespace Plugins\Penilaian_Keperawatan_Gigi;

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
        $enum['informasi'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_gigi', 'informasi');
        $enum['riwayat_penyakit'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_gigi', 'riwayat_penyakit');
        $enum['riwayat_perawatan_gigi'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_gigi', 'riwayat_perawatan_gigi');
        $enum['kebiasaan_sikat_gigi'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_gigi', 'kebiasaan_sikat_gigi');
        $enum['kebiasaan_lain'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_gigi', 'kebiasaan_lain');
        $enum['alat_bantu'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_gigi', 'alat_bantu');
        $enum['prothesa'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_gigi', 'prothesa');
        $enum['status_psiko'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_gigi', 'status_psiko');
        $enum['hub_keluarga'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_gigi', 'hub_keluarga');
        $enum['tinggal_dengan'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_gigi', 'tinggal_dengan');
        $enum['ekonomi'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_gigi', 'ekonomi');
        $enum['budaya'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_gigi', 'budaya');
        $enum['edukasi'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_gigi', 'edukasi');
        $enum['berjalan_a'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_gigi', 'berjalan_a');
        $enum['berjalan_b'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_gigi', 'berjalan_b');
        $enum['berjalan_c'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_gigi', 'berjalan_c');
        $enum['hasil'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_gigi', 'hasil');
        $enum['lapor'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_gigi', 'lapor');
        $enum['nyeri'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_gigi', 'nyeri');
        $enum['skala_nyeri'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_gigi', 'skala_nyeri');
        $enum['nyeri_hilang'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_gigi', 'nyeri_hilang');
        $enum['pada_dokter'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_gigi', 'pada_dokter');
        $enum['kebersihan_mulut'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_gigi', 'kebersihan_mulut');
        $enum['mukosa_mulut'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_gigi', 'mukosa_mulut');
        $enum['karies'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_gigi', 'karies');
        $enum['karang_gigi'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_gigi', 'karang_gigi');
        $enum['gingiva'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_gigi', 'gingiva');
        $enum['palatum'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_gigi', 'palatum');
        return $this->draw('manage.html', ['enum' => $enum]);
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
        $search_field_mlite_penilaian_awal_keperawatan_gigi= $_POST['search_field_mlite_penilaian_awal_keperawatan_gigi'];
        $search_text_mlite_penilaian_awal_keperawatan_gigi = $_POST['search_text_mlite_penilaian_awal_keperawatan_gigi'];

        $searchQuery = " ";
        if($search_text_mlite_penilaian_awal_keperawatan_gigi != ''){
            $searchQuery .= " and (".$search_field_mlite_penilaian_awal_keperawatan_gigi." like '%".$search_text_mlite_penilaian_awal_keperawatan_gigi."%' ) ";
        }

        ## Total number of records without filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from mlite_penilaian_awal_keperawatan_gigi");
        $sel->execute();
        $records = $sel->fetch();
        $totalRecords = $records['allcount'];

        ## Total number of records with filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from mlite_penilaian_awal_keperawatan_gigi WHERE 1 ".$searchQuery);
        $sel->execute();
        $records = $sel->fetch();
        $totalRecordwithFilter = $records['allcount'];

        ## Fetch records
        $sel = $this->db()->pdo()->prepare("select * from mlite_penilaian_awal_keperawatan_gigi WHERE 1 ".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row1.",".$rowperpage);
        $sel->execute();
        $result = $sel->fetchAll(\PDO::FETCH_ASSOC);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_rawat'=>$row['no_rawat'],
'tanggal'=>$row['tanggal'],
'informasi'=>$row['informasi'],
'td'=>$row['td'],
'nadi'=>$row['nadi'],
'rr'=>$row['rr'],
'suhu'=>$row['suhu'],
'bb'=>$row['bb'],
'tb'=>$row['tb'],
'bmi'=>$row['bmi'],
'keluhan_utama'=>$row['keluhan_utama'],
'riwayat_penyakit'=>$row['riwayat_penyakit'],
'ket_riwayat_penyakit'=>$row['ket_riwayat_penyakit'],
'alergi'=>$row['alergi'],
'riwayat_perawatan_gigi'=>$row['riwayat_perawatan_gigi'],
'ket_riwayat_perawatan_gigi'=>$row['ket_riwayat_perawatan_gigi'],
'kebiasaan_sikat_gigi'=>$row['kebiasaan_sikat_gigi'],
'kebiasaan_lain'=>$row['kebiasaan_lain'],
'ket_kebiasaan_lain'=>$row['ket_kebiasaan_lain'],
'obat_yang_diminum_saatini'=>$row['obat_yang_diminum_saatini'],
'alat_bantu'=>$row['alat_bantu'],
'ket_alat_bantu'=>$row['ket_alat_bantu'],
'prothesa'=>$row['prothesa'],
'ket_pro'=>$row['ket_pro'],
'status_psiko'=>$row['status_psiko'],
'ket_psiko'=>$row['ket_psiko'],
'hub_keluarga'=>$row['hub_keluarga'],
'tinggal_dengan'=>$row['tinggal_dengan'],
'ket_tinggal'=>$row['ket_tinggal'],
'ekonomi'=>$row['ekonomi'],
'budaya'=>$row['budaya'],
'ket_budaya'=>$row['ket_budaya'],
'edukasi'=>$row['edukasi'],
'ket_edukasi'=>$row['ket_edukasi'],
'berjalan_a'=>$row['berjalan_a'],
'berjalan_b'=>$row['berjalan_b'],
'berjalan_c'=>$row['berjalan_c'],
'hasil'=>$row['hasil'],
'lapor'=>$row['lapor'],
'ket_lapor'=>$row['ket_lapor'],
'nyeri'=>$row['nyeri'],
'lokasi'=>$row['lokasi'],
'skala_nyeri'=>$row['skala_nyeri'],
'durasi'=>$row['durasi'],
'frekuensi'=>$row['frekuensi'],
'nyeri_hilang'=>$row['nyeri_hilang'],
'ket_nyeri'=>$row['ket_nyeri'],
'pada_dokter'=>$row['pada_dokter'],
'ket_dokter'=>$row['ket_dokter'],
'kebersihan_mulut'=>$row['kebersihan_mulut'],
'mukosa_mulut'=>$row['mukosa_mulut'],
'karies'=>$row['karies'],
'karang_gigi'=>$row['karang_gigi'],
'gingiva'=>$row['gingiva'],
'palatum'=>$row['palatum'],
'rencana'=>$row['rencana'],
'nip'=>$row['nip']

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
$td = $_POST['td'];
$nadi = $_POST['nadi'];
$rr = $_POST['rr'];
$suhu = $_POST['suhu'];
$bb = $_POST['bb'];
$tb = $_POST['tb'];
$bmi = $_POST['bmi'];
$keluhan_utama = $_POST['keluhan_utama'];
$riwayat_penyakit = $_POST['riwayat_penyakit'];
$ket_riwayat_penyakit = $_POST['ket_riwayat_penyakit'];
$alergi = $_POST['alergi'];
$riwayat_perawatan_gigi = $_POST['riwayat_perawatan_gigi'];
$ket_riwayat_perawatan_gigi = $_POST['ket_riwayat_perawatan_gigi'];
$kebiasaan_sikat_gigi = $_POST['kebiasaan_sikat_gigi'];
$kebiasaan_lain = $_POST['kebiasaan_lain'];
$ket_kebiasaan_lain = $_POST['ket_kebiasaan_lain'];
$obat_yang_diminum_saatini = $_POST['obat_yang_diminum_saatini'];
$alat_bantu = $_POST['alat_bantu'];
$ket_alat_bantu = $_POST['ket_alat_bantu'];
$prothesa = $_POST['prothesa'];
$ket_pro = $_POST['ket_pro'];
$status_psiko = $_POST['status_psiko'];
$ket_psiko = $_POST['ket_psiko'];
$hub_keluarga = $_POST['hub_keluarga'];
$tinggal_dengan = $_POST['tinggal_dengan'];
$ket_tinggal = $_POST['ket_tinggal'];
$ekonomi = $_POST['ekonomi'];
$budaya = $_POST['budaya'];
$ket_budaya = $_POST['ket_budaya'];
$edukasi = $_POST['edukasi'];
$ket_edukasi = $_POST['ket_edukasi'];
$berjalan_a = $_POST['berjalan_a'];
$berjalan_b = $_POST['berjalan_b'];
$berjalan_c = $_POST['berjalan_c'];
$hasil = $_POST['hasil'];
$lapor = $_POST['lapor'];
$ket_lapor = $_POST['ket_lapor'];
$nyeri = $_POST['nyeri'];
$lokasi = $_POST['lokasi'];
$skala_nyeri = $_POST['skala_nyeri'];
$durasi = $_POST['durasi'];
$frekuensi = $_POST['frekuensi'];
$nyeri_hilang = $_POST['nyeri_hilang'];
$ket_nyeri = $_POST['ket_nyeri'];
$pada_dokter = $_POST['pada_dokter'];
$ket_dokter = $_POST['ket_dokter'];
$kebersihan_mulut = $_POST['kebersihan_mulut'];
$mukosa_mulut = $_POST['mukosa_mulut'];
$karies = $_POST['karies'];
$karang_gigi = $_POST['karang_gigi'];
$gingiva = $_POST['gingiva'];
$palatum = $_POST['palatum'];
$rencana = $_POST['rencana'];
$nip = $_POST['nip'];

            
            $mlite_penilaian_awal_keperawatan_gigi_add = $this->db()->pdo()->prepare('INSERT INTO mlite_penilaian_awal_keperawatan_gigi VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $mlite_penilaian_awal_keperawatan_gigi_add->execute([$no_rawat, $tanggal, $informasi, $td, $nadi, $rr, $suhu, $bb, $tb, $bmi, $keluhan_utama, $riwayat_penyakit, $ket_riwayat_penyakit, $alergi, $riwayat_perawatan_gigi, $ket_riwayat_perawatan_gigi, $kebiasaan_sikat_gigi, $kebiasaan_lain, $ket_kebiasaan_lain, $obat_yang_diminum_saatini, $alat_bantu, $ket_alat_bantu, $prothesa, $ket_pro, $status_psiko, $ket_psiko, $hub_keluarga, $tinggal_dengan, $ket_tinggal, $ekonomi, $budaya, $ket_budaya, $edukasi, $ket_edukasi, $berjalan_a, $berjalan_b, $berjalan_c, $hasil, $lapor, $ket_lapor, $nyeri, $lokasi, $skala_nyeri, $durasi, $frekuensi, $nyeri_hilang, $ket_nyeri, $pada_dokter, $ket_dokter, $kebersihan_mulut, $mukosa_mulut, $karies, $karang_gigi, $gingiva, $palatum, $rencana, $nip]);

        }
        if ($act=="edit") {

        $no_rawat = $_POST['no_rawat'];
$tanggal = $_POST['tanggal'];
$informasi = $_POST['informasi'];
$td = $_POST['td'];
$nadi = $_POST['nadi'];
$rr = $_POST['rr'];
$suhu = $_POST['suhu'];
$bb = $_POST['bb'];
$tb = $_POST['tb'];
$bmi = $_POST['bmi'];
$keluhan_utama = $_POST['keluhan_utama'];
$riwayat_penyakit = $_POST['riwayat_penyakit'];
$ket_riwayat_penyakit = $_POST['ket_riwayat_penyakit'];
$alergi = $_POST['alergi'];
$riwayat_perawatan_gigi = $_POST['riwayat_perawatan_gigi'];
$ket_riwayat_perawatan_gigi = $_POST['ket_riwayat_perawatan_gigi'];
$kebiasaan_sikat_gigi = $_POST['kebiasaan_sikat_gigi'];
$kebiasaan_lain = $_POST['kebiasaan_lain'];
$ket_kebiasaan_lain = $_POST['ket_kebiasaan_lain'];
$obat_yang_diminum_saatini = $_POST['obat_yang_diminum_saatini'];
$alat_bantu = $_POST['alat_bantu'];
$ket_alat_bantu = $_POST['ket_alat_bantu'];
$prothesa = $_POST['prothesa'];
$ket_pro = $_POST['ket_pro'];
$status_psiko = $_POST['status_psiko'];
$ket_psiko = $_POST['ket_psiko'];
$hub_keluarga = $_POST['hub_keluarga'];
$tinggal_dengan = $_POST['tinggal_dengan'];
$ket_tinggal = $_POST['ket_tinggal'];
$ekonomi = $_POST['ekonomi'];
$budaya = $_POST['budaya'];
$ket_budaya = $_POST['ket_budaya'];
$edukasi = $_POST['edukasi'];
$ket_edukasi = $_POST['ket_edukasi'];
$berjalan_a = $_POST['berjalan_a'];
$berjalan_b = $_POST['berjalan_b'];
$berjalan_c = $_POST['berjalan_c'];
$hasil = $_POST['hasil'];
$lapor = $_POST['lapor'];
$ket_lapor = $_POST['ket_lapor'];
$nyeri = $_POST['nyeri'];
$lokasi = $_POST['lokasi'];
$skala_nyeri = $_POST['skala_nyeri'];
$durasi = $_POST['durasi'];
$frekuensi = $_POST['frekuensi'];
$nyeri_hilang = $_POST['nyeri_hilang'];
$ket_nyeri = $_POST['ket_nyeri'];
$pada_dokter = $_POST['pada_dokter'];
$ket_dokter = $_POST['ket_dokter'];
$kebersihan_mulut = $_POST['kebersihan_mulut'];
$mukosa_mulut = $_POST['mukosa_mulut'];
$karies = $_POST['karies'];
$karang_gigi = $_POST['karang_gigi'];
$gingiva = $_POST['gingiva'];
$palatum = $_POST['palatum'];
$rencana = $_POST['rencana'];
$nip = $_POST['nip'];


        // BUANG FIELD PERTAMA

            $mlite_penilaian_awal_keperawatan_gigi_edit = $this->db()->pdo()->prepare("UPDATE mlite_penilaian_awal_keperawatan_gigi SET no_rawat=?, tanggal=?, informasi=?, td=?, nadi=?, rr=?, suhu=?, bb=?, tb=?, bmi=?, keluhan_utama=?, riwayat_penyakit=?, ket_riwayat_penyakit=?, alergi=?, riwayat_perawatan_gigi=?, ket_riwayat_perawatan_gigi=?, kebiasaan_sikat_gigi=?, kebiasaan_lain=?, ket_kebiasaan_lain=?, obat_yang_diminum_saatini=?, alat_bantu=?, ket_alat_bantu=?, prothesa=?, ket_pro=?, status_psiko=?, ket_psiko=?, hub_keluarga=?, tinggal_dengan=?, ket_tinggal=?, ekonomi=?, budaya=?, ket_budaya=?, edukasi=?, ket_edukasi=?, berjalan_a=?, berjalan_b=?, berjalan_c=?, hasil=?, lapor=?, ket_lapor=?, nyeri=?, lokasi=?, skala_nyeri=?, durasi=?, frekuensi=?, nyeri_hilang=?, ket_nyeri=?, pada_dokter=?, ket_dokter=?, kebersihan_mulut=?, mukosa_mulut=?, karies=?, karang_gigi=?, gingiva=?, palatum=?, rencana=?, nip=? WHERE no_rawat=?");
            $mlite_penilaian_awal_keperawatan_gigi_edit->execute([$no_rawat, $tanggal, $informasi, $td, $nadi, $rr, $suhu, $bb, $tb, $bmi, $keluhan_utama, $riwayat_penyakit, $ket_riwayat_penyakit, $alergi, $riwayat_perawatan_gigi, $ket_riwayat_perawatan_gigi, $kebiasaan_sikat_gigi, $kebiasaan_lain, $ket_kebiasaan_lain, $obat_yang_diminum_saatini, $alat_bantu, $ket_alat_bantu, $prothesa, $ket_pro, $status_psiko, $ket_psiko, $hub_keluarga, $tinggal_dengan, $ket_tinggal, $ekonomi, $budaya, $ket_budaya, $edukasi, $ket_edukasi, $berjalan_a, $berjalan_b, $berjalan_c, $hasil, $lapor, $ket_lapor, $nyeri, $lokasi, $skala_nyeri, $durasi, $frekuensi, $nyeri_hilang, $ket_nyeri, $pada_dokter, $ket_dokter, $kebersihan_mulut, $mukosa_mulut, $karies, $karang_gigi, $gingiva, $palatum, $rencana, $nip,$no_rawat]);
        
        }

        if ($act=="del") {
            $no_rawat= $_POST['no_rawat'];
            $check_db = $this->db()->pdo()->prepare("DELETE FROM mlite_penilaian_awal_keperawatan_gigi WHERE no_rawat='$no_rawat'");
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

            $search_field_mlite_penilaian_awal_keperawatan_gigi= $_POST['search_field_mlite_penilaian_awal_keperawatan_gigi'];
            $search_text_mlite_penilaian_awal_keperawatan_gigi = $_POST['search_text_mlite_penilaian_awal_keperawatan_gigi'];

            $searchQuery = " ";
            if($search_text_mlite_penilaian_awal_keperawatan_gigi != ''){
                $searchQuery .= " and (".$search_field_mlite_penilaian_awal_keperawatan_gigi." like '%".$search_text_mlite_penilaian_awal_keperawatan_gigi."%' ) ";
            }

            $user_lihat = $this->db()->pdo()->prepare("SELECT * from mlite_penilaian_awal_keperawatan_gigi WHERE 1 ".$searchQuery);
            $user_lihat->execute();
            $result = $user_lihat->fetchAll(\PDO::FETCH_ASSOC);

            $data = array();

            foreach($result as $row) {
                $data[] = array(
                    'no_rawat'=>$row['no_rawat'],
'tanggal'=>$row['tanggal'],
'informasi'=>$row['informasi'],
'td'=>$row['td'],
'nadi'=>$row['nadi'],
'rr'=>$row['rr'],
'suhu'=>$row['suhu'],
'bb'=>$row['bb'],
'tb'=>$row['tb'],
'bmi'=>$row['bmi'],
'keluhan_utama'=>$row['keluhan_utama'],
'riwayat_penyakit'=>$row['riwayat_penyakit'],
'ket_riwayat_penyakit'=>$row['ket_riwayat_penyakit'],
'alergi'=>$row['alergi'],
'riwayat_perawatan_gigi'=>$row['riwayat_perawatan_gigi'],
'ket_riwayat_perawatan_gigi'=>$row['ket_riwayat_perawatan_gigi'],
'kebiasaan_sikat_gigi'=>$row['kebiasaan_sikat_gigi'],
'kebiasaan_lain'=>$row['kebiasaan_lain'],
'ket_kebiasaan_lain'=>$row['ket_kebiasaan_lain'],
'obat_yang_diminum_saatini'=>$row['obat_yang_diminum_saatini'],
'alat_bantu'=>$row['alat_bantu'],
'ket_alat_bantu'=>$row['ket_alat_bantu'],
'prothesa'=>$row['prothesa'],
'ket_pro'=>$row['ket_pro'],
'status_psiko'=>$row['status_psiko'],
'ket_psiko'=>$row['ket_psiko'],
'hub_keluarga'=>$row['hub_keluarga'],
'tinggal_dengan'=>$row['tinggal_dengan'],
'ket_tinggal'=>$row['ket_tinggal'],
'ekonomi'=>$row['ekonomi'],
'budaya'=>$row['budaya'],
'ket_budaya'=>$row['ket_budaya'],
'edukasi'=>$row['edukasi'],
'ket_edukasi'=>$row['ket_edukasi'],
'berjalan_a'=>$row['berjalan_a'],
'berjalan_b'=>$row['berjalan_b'],
'berjalan_c'=>$row['berjalan_c'],
'hasil'=>$row['hasil'],
'lapor'=>$row['lapor'],
'ket_lapor'=>$row['ket_lapor'],
'nyeri'=>$row['nyeri'],
'lokasi'=>$row['lokasi'],
'skala_nyeri'=>$row['skala_nyeri'],
'durasi'=>$row['durasi'],
'frekuensi'=>$row['frekuensi'],
'nyeri_hilang'=>$row['nyeri_hilang'],
'ket_nyeri'=>$row['ket_nyeri'],
'pada_dokter'=>$row['pada_dokter'],
'ket_dokter'=>$row['ket_dokter'],
'kebersihan_mulut'=>$row['kebersihan_mulut'],
'mukosa_mulut'=>$row['mukosa_mulut'],
'karies'=>$row['karies'],
'karang_gigi'=>$row['karang_gigi'],
'gingiva'=>$row['gingiva'],
'palatum'=>$row['palatum'],
'rencana'=>$row['rencana'],
'nip'=>$row['nip']
                );
            }

            echo json_encode($data);
        }
        exit();
    }

    public function getDetail($no_rawat)
    {
        $detail = $this->db('mlite_penilaian_awal_keperawatan_gigi')->where('no_rawat', $no_rawat)->toArray();
        $settings =  $this->settings('settings');
        echo $this->draw('detail.html', ['detail' => $detail, 'settings' => $settings]);
        exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/penilaian_keperawatan_gigi/css/admin/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/penilaian_keperawatan_gigi/js/admin/scripts.js', ['settings' => $settings]);
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

        $this->core->addCSS(url([ADMIN, 'penilaian_keperawatan_gigi', 'css']));
        $this->core->addJS(url([ADMIN, 'penilaian_keperawatan_gigi', 'javascript']), 'footer');
    }

}
