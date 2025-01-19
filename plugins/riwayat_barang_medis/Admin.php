<?php
namespace Plugins\Riwayat_Barang_Medis;

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
        return $this->draw('manage.html');
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
        $search_field_riwayat_barang_medis= $_POST['search_field_riwayat_barang_medis'];
        $search_text_riwayat_barang_medis = $_POST['search_text_riwayat_barang_medis'];

        $tgl_awal = isset_or($_POST['tgl_awal'], date('Y-m-d'));
        $tgl_akhir = isset_or($_POST['tgl_akhir'], date('Y-m-d'));

        $searchQuery = " ";
        if($search_text_riwayat_barang_medis != ''){
            $searchQuery .= " and (".$search_field_riwayat_barang_medis." like '%".$search_text_riwayat_barang_medis."%' ) ";
        }

        $searchQuery .= " and (tanggal between '".$tgl_awal."' and '".$tgl_akhir."') ";

        ## Total number of records without filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from riwayat_barang_medis");
        $sel->execute();
        $records = $sel->fetch();
        $totalRecords = $records['allcount'];

        ## Total number of records with filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from riwayat_barang_medis WHERE 1 ".$searchQuery);
        $sel->execute();
        $records = $sel->fetch();
        $totalRecordwithFilter = $records['allcount'];

        ## Fetch records
        $sel = $this->db()->pdo()->prepare("select * from riwayat_barang_medis WHERE 1 ".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row1.",".$rowperpage);
        $sel->execute();
        $result = $sel->fetchAll(\PDO::FETCH_ASSOC);

        $data = array();
        foreach($result as $row) {
            $databarang = $this->db('databarang')->select('nama_brng')->where('kode_brng', $row['kode_brng'])->oneArray();
            $bangsal = $this->db('bangsal')->select('nm_bangsal')->where('kd_bangsal', $row['kd_bangsal'])->oneArray();
            $data[] = array(
                'kode_brng'=>$row['kode_brng'],
                'nama_brng'=>$databarang['nama_brng'],
                'stok_awal'=>$row['stok_awal'],
                'masuk'=>$row['masuk'],
                'keluar'=>$row['keluar'],
                'stok_akhir'=>$row['stok_akhir'],
                'posisi'=>$row['posisi'],
                'tanggal'=>$row['tanggal'],
                'jam'=>$row['jam'],
                'petugas'=>$row['petugas'],
                'kd_bangsal'=>$row['kd_bangsal'],
                'nm_bangsal'=>$bangsal['nm_bangsal'],
                'status'=>$row['status'],
                'no_batch'=>$row['no_batch'],
                'no_faktur'=>$row['no_faktur'],
                'keterangan'=>$row['keterangan']
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

        if ($act=="lihat") {

            $search_field_riwayat_barang_medis= $_POST['search_field_riwayat_barang_medis'];
            $search_text_riwayat_barang_medis = $_POST['search_text_riwayat_barang_medis'];

            $tgl_awal = isset_or($_POST['tgl_awal'], date('Y-m-d'));
            $tgl_akhir = isset_or($_POST['tgl_akhir'], date('Y-m-d'));
    
            $searchQuery = " ";
            if($search_text_riwayat_barang_medis != ''){
                $searchQuery .= " and (".$search_field_riwayat_barang_medis." like '%".$search_text_riwayat_barang_medis."%' ) ";
            }

            $searchQuery .= " and (tanggal between '".$tgl_awal."' and '".$tgl_akhir."') ";

            $user_lihat = $this->db()->pdo()->prepare("SELECT * from riwayat_barang_medis WHERE 1 ".$searchQuery);
            $user_lihat->execute();
            $result = $user_lihat->fetchAll(\PDO::FETCH_ASSOC);

            $data = array();

            foreach($result as $row) {
                $databarang = $this->db('databarang')->select('nama_brng')->where('kode_brng', $row['kode_brng'])->oneArray();
                $bangsal = $this->db('bangsal')->select('nm_bangsal')->where('kd_bangsal', $row['kd_bangsal'])->oneArray();
                $data[] = array(
                    'kode_brng'=>$row['kode_brng'],
                    'nama_brng'=>$databarang['nama_brng'],
                    'stok_awal'=>$row['stok_awal'],
                    'masuk'=>$row['masuk'],
                    'keluar'=>$row['keluar'],
                    'stok_akhir'=>$row['stok_akhir'],
                    'posisi'=>$row['posisi'],
                    'tanggal'=>$row['tanggal'],
                    'jam'=>$row['jam'],
                    'petugas'=>$row['petugas'],
                    'kd_bangsal'=>$row['kd_bangsal'],
                    'nm_bangsal'=>$bangsal['nm_bangsal'],
                    'status'=>$row['status'],
                    'no_batch'=>$row['no_batch'],
                    'no_faktur'=>$row['no_faktur'],
                    'keterangan'=>$row['keterangan']
                );
            }

            echo json_encode($data);
        }
        exit();
    }

    public function getDetail($kode_brng)
    {
        $detail = $this->db('riwayat_barang_medis')->where('kode_brng', $kode_brng)->toArray();
        echo $this->draw('detail.html', ['detail' => $detail]);
        exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/riwayat_barang_medis/css/admin/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/riwayat_barang_medis/js/admin/scripts.js', ['settings' => $settings]);
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
        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));

        $this->core->addCSS(url([ADMIN, 'riwayat_barang_medis', 'css']));
        $this->core->addJS(url([ADMIN, 'riwayat_barang_medis', 'javascript']), 'footer');
    }

}
