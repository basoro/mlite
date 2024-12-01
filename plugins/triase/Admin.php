<?php
namespace Plugins\Triase;

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
        $enum['cara_masuk'] = $this->core->getEnum('mlite_triase', 'cara_masuk');
        $enum['alat_transportasi'] = $this->core->getEnum('mlite_triase', 'alat_transportasi');
        $enum['alasan_kedatangan'] = $this->core->getEnum('mlite_triase', 'alasan_kedatangan');
        $enum['macam_kasus'] = $this->core->getEnum('mlite_triase', 'macam_kasus');
        $enum['jenis_triase'] = $this->core->getEnum('mlite_triase', 'jenis_triase');
        $enum['kebutuhan_khusus'] = $this->core->getEnum('mlite_triase', 'kebutuhan_khusus');
        $enum['plan'] = $this->core->getEnum('mlite_triase', 'plan');
        $triase_pemeriksaan = $this->db('mlite_triase_pemeriksaan')->toArray();
        $triase_skala = $this->db('mlite_triase_skala')->toArray();
        $kode_pemeriksaan['001'] = $this->db('mlite_triase_skala')->where('kode_pemeriksaan', '001')->toArray();
        $kode_pemeriksaan['002'] = $this->db('mlite_triase_skala')->where('kode_pemeriksaan', '002')->toArray();
        $kode_pemeriksaan['003'] = $this->db('mlite_triase_skala')->where('kode_pemeriksaan', '003')->toArray();
        $kode_pemeriksaan['004'] = $this->db('mlite_triase_skala')->where('kode_pemeriksaan', '004')->toArray();
        $kode_pemeriksaan['005'] = $this->db('mlite_triase_skala')->where('kode_pemeriksaan', '005')->toArray();
        $kode_pemeriksaan['006'] = $this->db('mlite_triase_skala')->where('kode_pemeriksaan', '006')->toArray();
        $kode_pemeriksaan['007'] = $this->db('mlite_triase_skala')->where('kode_pemeriksaan', '007')->toArray();
        $kode_pemeriksaan['008'] = $this->db('mlite_triase_skala')->where('kode_pemeriksaan', '008')->toArray();

        $rows_triase_pemeriksaan = $this->db('mlite_triase_pemeriksaan')->toArray();
        $skala_pemeriksaan = [];
        foreach($rows_triase_pemeriksaan as $row) {
            $rows_kode_pemeriksaan = $this->db('mlite_triase_skala')->where('kode_pemeriksaan', $row['kode_pemeriksaan'])->toArray();
            $row_['kode'] = $row['kode_pemeriksaan'];
            $row_['nama_pemeriksaan'] = $row['nama_pemeriksaan'];
            foreach($rows_kode_pemeriksaan as $row1) {
                $rows_skala = $this->db('mlite_triase_skala')->where('kode_pemeriksaan', $row_['kode'])->group('skala')->toArray();
                $row_['skala'] = [];
                foreach($rows_skala as $row2) {
                    $row2_['skala'] = $row2['skala'];
                    $row2_['skala_nilai'] = $this->db('mlite_triase_skala')->where('kode_pemeriksaan', $row_['kode'])->where('skala', $row2['skala'])->toArray();
                    $row_['skala'][] = $row2_;
                }
            }
            $skala_pemeriksaan[] = $row_;
        }

        return $this->draw('manage.html', ['enum' => $enum, 'triase_pemeriksaan' => $triase_pemeriksaan, 'kode_pemeriksaan' => $kode_pemeriksaan, 'skala_pemeriksaan' => $skala_pemeriksaan]);
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
        $search_field_mlite_triase= $_POST['search_field_mlite_triase'];
        $search_text_mlite_triase = $_POST['search_text_mlite_triase'];

        $searchQuery = " ";
        if($search_text_mlite_triase != ''){
            $searchQuery .= " and (".$search_field_mlite_triase." like '%".$search_text_mlite_triase."%' ) ";
        }

        ## Total number of records without filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from mlite_triase");
        $sel->execute();
        $records = $sel->fetch();
        $totalRecords = $records['allcount'];

        ## Total number of records with filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from mlite_triase WHERE 1 ".$searchQuery);
        $sel->execute();
        $records = $sel->fetch();
        $totalRecordwithFilter = $records['allcount'];

        ## Fetch records
        $sel = $this->db()->pdo()->prepare("select * from mlite_triase WHERE 1 ".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row1.",".$rowperpage);
        $sel->execute();
        $result = $sel->fetchAll(\PDO::FETCH_ASSOC);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_rawat'=>$row['no_rawat'],
'tgl_kunjungan'=>$row['tgl_kunjungan'],
'cara_masuk'=>$row['cara_masuk'],
'alat_transportasi'=>$row['alat_transportasi'],
'alasan_kedatangan'=>$row['alasan_kedatangan'],
'keterangan_kedatangan'=>$row['keterangan_kedatangan'],
'macam_kasus'=>$row['macam_kasus'],
'tekanan_darah'=>$row['tekanan_darah'],
'nadi'=>$row['nadi'],
'pernapasan'=>$row['pernapasan'],
'suhu'=>$row['suhu'],
'saturasi_o2'=>$row['saturasi_o2'],
'nyeri'=>$row['nyeri'],
'jenis_triase'=>$row['jenis_triase'],
'keluhan_utama'=>$row['keluhan_utama'],
'kebutuhan_khusus'=>$row['kebutuhan_khusus'],
'catatan'=>$row['catatan'],
'plan'=>$row['plan'],
'nik'=>$row['nik']

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
$tgl_kunjungan = $_POST['tgl_kunjungan'];
$cara_masuk = $_POST['cara_masuk'];
$alat_transportasi = $_POST['alat_transportasi'];
$alasan_kedatangan = $_POST['alasan_kedatangan'];
$keterangan_kedatangan = $_POST['keterangan_kedatangan'];
$macam_kasus = $_POST['macam_kasus'];
$tekanan_darah = $_POST['tekanan_darah'];
$nadi = $_POST['nadi'];
$pernapasan = $_POST['pernapasan'];
$suhu = $_POST['suhu'];
$saturasi_o2 = $_POST['saturasi_o2'];
$nyeri = $_POST['nyeri'];
$jenis_triase = $_POST['jenis_triase'];
$keluhan_utama = $_POST['keluhan_utama'];
$kebutuhan_khusus = $_POST['kebutuhan_khusus'];
$catatan = $_POST['catatan'];
$plan = $_POST['plan'];
$nik = $_POST['nik'];
            
            $mlite_triase_add = $this->db()->pdo()->prepare('INSERT INTO mlite_triase VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $mlite_triase_add->execute([$no_rawat, $tgl_kunjungan, $cara_masuk, $alat_transportasi, $alasan_kedatangan, $keterangan_kedatangan, $macam_kasus, $tekanan_darah, $nadi, $pernapasan, $suhu, $saturasi_o2, $nyeri, $jenis_triase, $keluhan_utama, $kebutuhan_khusus, $catatan, $plan, $nik]);

            for($l=0; $l < count($_POST['id_skala_triase']); $l++){
                $mlite_triase_skala = $this->db('mlite_triase_skala')->where('id', $_POST['id_skala_triase'][$l])->oneArray();
                $mlite_triase_detail = $this->db('mlite_triase_detail')->save(['no_rawat' => $no_rawat, 'skala' => $mlite_triase_skala['skala'], 'kode_skala' => $mlite_triase_skala['kode_skala']]);
            }

        }
        if ($act=="edit") {

        $no_rawat = $_POST['no_rawat'];
$tgl_kunjungan = $_POST['tgl_kunjungan'];
$cara_masuk = $_POST['cara_masuk'];
$alat_transportasi = $_POST['alat_transportasi'];
$alasan_kedatangan = $_POST['alasan_kedatangan'];
$keterangan_kedatangan = $_POST['keterangan_kedatangan'];
$macam_kasus = $_POST['macam_kasus'];
$tekanan_darah = $_POST['tekanan_darah'];
$nadi = $_POST['nadi'];
$pernapasan = $_POST['pernapasan'];
$suhu = $_POST['suhu'];
$saturasi_o2 = $_POST['saturasi_o2'];
$nyeri = $_POST['nyeri'];
$jenis_triase = $_POST['jenis_triase'];
$keluhan_utama = $_POST['keluhan_utama'];
$kebutuhan_khusus = $_POST['kebutuhan_khusus'];
$catatan = $_POST['catatan'];
$plan = $_POST['plan'];
$nik = $_POST['nik'];


        // BUANG FIELD PERTAMA

            $mlite_triase_edit = $this->db()->pdo()->prepare("UPDATE mlite_triase SET no_rawat=?, tgl_kunjungan=?, cara_masuk=?, alat_transportasi=?, alasan_kedatangan=?, keterangan_kedatangan=?, macam_kasus=?, tekanan_darah=?, nadi=?, pernapasan=?, suhu=?, saturasi_o2=?, nyeri=?, jenis_triase=?, keluhan_utama=?, kebutuhan_khusus=?, catatan=?, plan=?, nik=? WHERE no_rawat=?");
            $mlite_triase_edit->execute([$no_rawat, $tgl_kunjungan, $cara_masuk, $alat_transportasi, $alasan_kedatangan, $keterangan_kedatangan, $macam_kasus, $tekanan_darah, $nadi, $pernapasan, $suhu, $saturasi_o2, $nyeri, $jenis_triase, $keluhan_utama, $kebutuhan_khusus, $catatan, $plan, $nik,$no_rawat]);
        
        }

        if ($act=="del") {
            $no_rawat= $_POST['no_rawat'];
            $check_db = $this->db()->pdo()->prepare("DELETE FROM mlite_triase WHERE no_rawat='$no_rawat'");
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

            $search_field_mlite_triase= $_POST['search_field_mlite_triase'];
            $search_text_mlite_triase = $_POST['search_text_mlite_triase'];

            $searchQuery = " ";
            if($search_text_mlite_triase != ''){
                $searchQuery .= " and (".$search_field_mlite_triase." like '%".$search_text_mlite_triase."%' ) ";
            }

            $user_lihat = $this->db()->pdo()->prepare("SELECT * from mlite_triase WHERE 1 ".$searchQuery);
            $user_lihat->execute();
            $result = $user_lihat->fetchAll(\PDO::FETCH_ASSOC);

            $data = array();

            foreach($result as $row) {
                $data[] = array(
                    'no_rawat'=>$row['no_rawat'],
'tgl_kunjungan'=>$row['tgl_kunjungan'],
'cara_masuk'=>$row['cara_masuk'],
'alat_transportasi'=>$row['alat_transportasi'],
'alasan_kedatangan'=>$row['alasan_kedatangan'],
'keterangan_kedatangan'=>$row['keterangan_kedatangan'],
'macam_kasus'=>$row['macam_kasus'],
'tekanan_darah'=>$row['tekanan_darah'],
'nadi'=>$row['nadi'],
'pernapasan'=>$row['pernapasan'],
'suhu'=>$row['suhu'],
'saturasi_o2'=>$row['saturasi_o2'],
'nyeri'=>$row['nyeri'],
'jenis_triase'=>$row['jenis_triase'],
'keluhan_utama'=>$row['keluhan_utama'],
'kebutuhan_khusus'=>$row['kebutuhan_khusus'],
'catatan'=>$row['catatan'],
'plan'=>$row['plan'],
'nik'=>$row['nik']
                );
            }

            echo json_encode($data);
        }
        exit();
    }

    public function getDetail($no_rawat)
    {
        $detail = $this->db('mlite_triase')->where('no_rawat', revertNoRawat($no_rawat))->toArray();
        $settings =  $this->settings('settings');
        echo $this->draw('detail.html', ['detail' => $detail, 'settings' => $settings]);
        exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/triase/css/admin/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/triase/js/admin/scripts.js', ['settings' => $settings]);
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

        $this->core->addCSS(url([ADMIN, 'triase', 'css']));
        $this->core->addJS(url([ADMIN, 'triase', 'javascript']), 'footer');
    }

}
