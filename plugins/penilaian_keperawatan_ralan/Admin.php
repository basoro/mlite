<?php
namespace Plugins\Penilaian_Keperawatan_Ralan;

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
        $enum['informasi'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ralan', 'informasi');
        $enum['alat_bantu'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ralan', 'alat_bantu');
        $enum['prothesa'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ralan', 'prothesa');
        $enum['adl'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ralan', 'adl');
        $enum['status_psiko'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ralan', 'status_psiko');
        $enum['tinggal_dengan'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ralan', 'tinggal_dengan');
        $enum['hub_keluarga'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ralan', 'hub_keluarga');
        $enum['budaya'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ralan', 'budaya');
        $enum['edukasi'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ralan', 'edukasi');
        $enum['ket_budaya'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ralan', 'ket_budaya');
        $enum['ekonomi'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ralan', 'ekonomi');
        $enum['berjalan_a'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ralan', 'berjalan_a');
        $enum['berjalan_b'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ralan', 'berjalan_b');
        $enum['berjalan_c'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ralan', 'berjalan_c');
        $enum['sg1'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ralan', 'sg1');
        $enum['nilai1'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ralan', 'nilai1');
        $enum['sg2'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ralan', 'sg2');
        $enum['nilai2'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ralan', 'nilai2');
        $enum['nyeri'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ralan', 'nyeri');
        $enum['skala_nyeri'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ralan', 'skala_nyeri');
        $enum['nyeri_hilang'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ralan', 'nyeri_hilang');
        $enum['provokes'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ralan', 'provokes');
        $enum['quality'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ralan', 'quality');
        $enum['menyebar'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ralan', 'menyebar');
        $enum['pada_dokter'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ralan', 'pada_dokter');
        $enum['hasil'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ralan', 'hasil');
        $enum['lapor'] = $this->core->getEnum('mlite_penilaian_awal_keperawatan_ralan', 'lapor');
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
        $search_field_mlite_penilaian_awal_keperawatan_ralan= $_POST['search_field_mlite_penilaian_awal_keperawatan_ralan'];
        $search_text_mlite_penilaian_awal_keperawatan_ralan = $_POST['search_text_mlite_penilaian_awal_keperawatan_ralan'];

        $searchQuery = " ";
        if($search_text_mlite_penilaian_awal_keperawatan_ralan != ''){
            $searchQuery .= " and (".$search_field_mlite_penilaian_awal_keperawatan_ralan." like '%".$search_text_mlite_penilaian_awal_keperawatan_ralan."%' ) ";
        }

        ## Total number of records without filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from mlite_penilaian_awal_keperawatan_ralan");
        $sel->execute();
        $records = $sel->fetch();
        $totalRecords = $records['allcount'];

        ## Total number of records with filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from mlite_penilaian_awal_keperawatan_ralan WHERE 1 ".$searchQuery);
        $sel->execute();
        $records = $sel->fetch();
        $totalRecordwithFilter = $records['allcount'];

        ## Fetch records
        $sel = $this->db()->pdo()->prepare("select * from mlite_penilaian_awal_keperawatan_ralan WHERE 1 ".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row1.",".$rowperpage);
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
'gcs'=>$row['gcs'],
'bb'=>$row['bb'],
'tb'=>$row['tb'],
'bmi'=>$row['bmi'],
'keluhan_utama'=>$row['keluhan_utama'],
'rpd'=>$row['rpd'],
'rpk'=>$row['rpk'],
'rpo'=>$row['rpo'],
'alergi'=>$row['alergi'],
'alat_bantu'=>$row['alat_bantu'],
'ket_bantu'=>$row['ket_bantu'],
'prothesa'=>$row['prothesa'],
'ket_pro'=>$row['ket_pro'],
'adl'=>$row['adl'],
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
'sg1'=>$row['sg1'],
'nilai1'=>$row['nilai1'],
'sg2'=>$row['sg2'],
'nilai2'=>$row['nilai2'],
'total_hasil'=>$row['total_hasil'],
'nyeri'=>$row['nyeri'],
'provokes'=>$row['provokes'],
'ket_provokes'=>$row['ket_provokes'],
'quality'=>$row['quality'],
'ket_quality'=>$row['ket_quality'],
'lokasi'=>$row['lokasi'],
'menyebar'=>$row['menyebar'],
'skala_nyeri'=>$row['skala_nyeri'],
'durasi'=>$row['durasi'],
'nyeri_hilang'=>$row['nyeri_hilang'],
'ket_nyeri'=>$row['ket_nyeri'],
'pada_dokter'=>$row['pada_dokter'],
'ket_dokter'=>$row['ket_dokter'],
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
$gcs = $_POST['gcs'];
$bb = $_POST['bb'];
$tb = $_POST['tb'];
$bmi = $_POST['bmi'];
$keluhan_utama = $_POST['keluhan_utama'];
$rpd = $_POST['rpd'];
$rpk = $_POST['rpk'];
$rpo = $_POST['rpo'];
$alergi = $_POST['alergi'];
$alat_bantu = $_POST['alat_bantu'];
$ket_bantu = $_POST['ket_bantu'];
$prothesa = $_POST['prothesa'];
$ket_pro = $_POST['ket_pro'];
$adl = $_POST['adl'];
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
$sg1 = $_POST['sg1'];
$nilai1 = $_POST['nilai1'];
$sg2 = $_POST['sg2'];
$nilai2 = $_POST['nilai2'];
$total_hasil = $_POST['total_hasil'];
$nyeri = $_POST['nyeri'];
$provokes = $_POST['provokes'];
$ket_provokes = $_POST['ket_provokes'];
$quality = $_POST['quality'];
$ket_quality = $_POST['ket_quality'];
$lokasi = $_POST['lokasi'];
$menyebar = $_POST['menyebar'];
$skala_nyeri = $_POST['skala_nyeri'];
$durasi = $_POST['durasi'];
$nyeri_hilang = $_POST['nyeri_hilang'];
$ket_nyeri = $_POST['ket_nyeri'];
$pada_dokter = $_POST['pada_dokter'];
$ket_dokter = $_POST['ket_dokter'];
$rencana = $_POST['rencana'];
$nip = $_POST['nip'];

            
            $mlite_penilaian_awal_keperawatan_ralan_add = $this->db()->pdo()->prepare('INSERT INTO mlite_penilaian_awal_keperawatan_ralan VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $mlite_penilaian_awal_keperawatan_ralan_add->execute([$no_rawat, $tanggal, $informasi, $td, $nadi, $rr, $suhu, $gcs, $bb, $tb, $bmi, $keluhan_utama, $rpd, $rpk, $rpo, $alergi, $alat_bantu, $ket_bantu, $prothesa, $ket_pro, $adl, $status_psiko, $ket_psiko, $hub_keluarga, $tinggal_dengan, $ket_tinggal, $ekonomi, $budaya, $ket_budaya, $edukasi, $ket_edukasi, $berjalan_a, $berjalan_b, $berjalan_c, $hasil, $lapor, $ket_lapor, $sg1, $nilai1, $sg2, $nilai2, $total_hasil, $nyeri, $provokes, $ket_provokes, $quality, $ket_quality, $lokasi, $menyebar, $skala_nyeri, $durasi, $nyeri_hilang, $ket_nyeri, $pada_dokter, $ket_dokter, $rencana, $nip]);

        }
        if ($act=="edit") {

        $no_rawat = $_POST['no_rawat'];
$tanggal = $_POST['tanggal'];
$informasi = $_POST['informasi'];
$td = $_POST['td'];
$nadi = $_POST['nadi'];
$rr = $_POST['rr'];
$suhu = $_POST['suhu'];
$gcs = $_POST['gcs'];
$bb = $_POST['bb'];
$tb = $_POST['tb'];
$bmi = $_POST['bmi'];
$keluhan_utama = $_POST['keluhan_utama'];
$rpd = $_POST['rpd'];
$rpk = $_POST['rpk'];
$rpo = $_POST['rpo'];
$alergi = $_POST['alergi'];
$alat_bantu = $_POST['alat_bantu'];
$ket_bantu = $_POST['ket_bantu'];
$prothesa = $_POST['prothesa'];
$ket_pro = $_POST['ket_pro'];
$adl = $_POST['adl'];
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
$sg1 = $_POST['sg1'];
$nilai1 = $_POST['nilai1'];
$sg2 = $_POST['sg2'];
$nilai2 = $_POST['nilai2'];
$total_hasil = $_POST['total_hasil'];
$nyeri = $_POST['nyeri'];
$provokes = $_POST['provokes'];
$ket_provokes = $_POST['ket_provokes'];
$quality = $_POST['quality'];
$ket_quality = $_POST['ket_quality'];
$lokasi = $_POST['lokasi'];
$menyebar = $_POST['menyebar'];
$skala_nyeri = $_POST['skala_nyeri'];
$durasi = $_POST['durasi'];
$nyeri_hilang = $_POST['nyeri_hilang'];
$ket_nyeri = $_POST['ket_nyeri'];
$pada_dokter = $_POST['pada_dokter'];
$ket_dokter = $_POST['ket_dokter'];
$rencana = $_POST['rencana'];
$nip = $_POST['nip'];


        // BUANG FIELD PERTAMA

            $mlite_penilaian_awal_keperawatan_ralan_edit = $this->db()->pdo()->prepare("UPDATE mlite_penilaian_awal_keperawatan_ralan SET no_rawat=?, tanggal=?, informasi=?, td=?, nadi=?, rr=?, suhu=?, gcs=?, bb=?, tb=?, bmi=?, keluhan_utama=?, rpd=?, rpk=?, rpo=?, alergi=?, alat_bantu=?, ket_bantu=?, prothesa=?, ket_pro=?, adl=?, status_psiko=?, ket_psiko=?, hub_keluarga=?, tinggal_dengan=?, ket_tinggal=?, ekonomi=?, budaya=?, ket_budaya=?, edukasi=?, ket_edukasi=?, berjalan_a=?, berjalan_b=?, berjalan_c=?, hasil=?, lapor=?, ket_lapor=?, sg1=?, nilai1=?, sg2=?, nilai2=?, total_hasil=?, nyeri=?, provokes=?, ket_provokes=?, quality=?, ket_quality=?, lokasi=?, menyebar=?, skala_nyeri=?, durasi=?, nyeri_hilang=?, ket_nyeri=?, pada_dokter=?, ket_dokter=?, rencana=?, nip=? WHERE no_rawat=?");
            $mlite_penilaian_awal_keperawatan_ralan_edit->execute([$no_rawat, $tanggal, $informasi, $td, $nadi, $rr, $suhu, $gcs, $bb, $tb, $bmi, $keluhan_utama, $rpd, $rpk, $rpo, $alergi, $alat_bantu, $ket_bantu, $prothesa, $ket_pro, $adl, $status_psiko, $ket_psiko, $hub_keluarga, $tinggal_dengan, $ket_tinggal, $ekonomi, $budaya, $ket_budaya, $edukasi, $ket_edukasi, $berjalan_a, $berjalan_b, $berjalan_c, $hasil, $lapor, $ket_lapor, $sg1, $nilai1, $sg2, $nilai2, $total_hasil, $nyeri, $provokes, $ket_provokes, $quality, $ket_quality, $lokasi, $menyebar, $skala_nyeri, $durasi, $nyeri_hilang, $ket_nyeri, $pada_dokter, $ket_dokter, $rencana, $nip,$no_rawat]);
        
        }

        if ($act=="del") {
            $no_rawat= $_POST['no_rawat'];
            $check_db = $this->db()->pdo()->prepare("DELETE FROM mlite_penilaian_awal_keperawatan_ralan WHERE no_rawat='$no_rawat'");
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

            $search_field_mlite_penilaian_awal_keperawatan_ralan= $_POST['search_field_mlite_penilaian_awal_keperawatan_ralan'];
            $search_text_mlite_penilaian_awal_keperawatan_ralan = $_POST['search_text_mlite_penilaian_awal_keperawatan_ralan'];

            $searchQuery = " ";
            if($search_text_mlite_penilaian_awal_keperawatan_ralan != ''){
                $searchQuery .= " and (".$search_field_mlite_penilaian_awal_keperawatan_ralan." like '%".$search_text_mlite_penilaian_awal_keperawatan_ralan."%' ) ";
            }

            $user_lihat = $this->db()->pdo()->prepare("SELECT * from mlite_penilaian_awal_keperawatan_ralan WHERE 1 ".$searchQuery);
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
'gcs'=>$row['gcs'],
'bb'=>$row['bb'],
'tb'=>$row['tb'],
'bmi'=>$row['bmi'],
'keluhan_utama'=>$row['keluhan_utama'],
'rpd'=>$row['rpd'],
'rpk'=>$row['rpk'],
'rpo'=>$row['rpo'],
'alergi'=>$row['alergi'],
'alat_bantu'=>$row['alat_bantu'],
'ket_bantu'=>$row['ket_bantu'],
'prothesa'=>$row['prothesa'],
'ket_pro'=>$row['ket_pro'],
'adl'=>$row['adl'],
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
'sg1'=>$row['sg1'],
'nilai1'=>$row['nilai1'],
'sg2'=>$row['sg2'],
'nilai2'=>$row['nilai2'],
'total_hasil'=>$row['total_hasil'],
'nyeri'=>$row['nyeri'],
'provokes'=>$row['provokes'],
'ket_provokes'=>$row['ket_provokes'],
'quality'=>$row['quality'],
'ket_quality'=>$row['ket_quality'],
'lokasi'=>$row['lokasi'],
'menyebar'=>$row['menyebar'],
'skala_nyeri'=>$row['skala_nyeri'],
'durasi'=>$row['durasi'],
'nyeri_hilang'=>$row['nyeri_hilang'],
'ket_nyeri'=>$row['ket_nyeri'],
'pada_dokter'=>$row['pada_dokter'],
'ket_dokter'=>$row['ket_dokter'],
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
        $no_rawat = revertNoRawat($no_rawat);
        $detail = $this->db('mlite_penilaian_awal_keperawatan_ralan')->where('no_rawat', $no_rawat)->toArray();
        $settings =  $this->settings('settings');
        echo $this->draw('detail.html', ['detail' => $detail, 'settings' => $settings]);
        exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/penilaian_keperawatan_ralan/css/admin/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/penilaian_keperawatan_ralan/js/admin/scripts.js', ['settings' => $settings]);
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

        $this->core->addCSS(url([ADMIN, 'penilaian_keperawatan_ralan', 'css']));
        $this->core->addJS(url([ADMIN, 'penilaian_keperawatan_ralan', 'javascript']), 'footer');
    }

}
