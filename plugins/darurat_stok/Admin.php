<?php
namespace Plugins\Darurat_Stok;

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
        $search_field_databarang= $_POST['search_field_databarang'];
        $search_text_databarang = $_POST['search_text_databarang'];

        $searchQuery = " ";
        if($search_text_databarang != ''){
            $searchQuery .= " and (".$search_field_databarang." like '%".$search_text_databarang."%' ) ";
        }

        ## Total number of records without filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from databarang");
        $sel->execute();
        $records = $sel->fetch();
        $totalRecords = $records['allcount'];

        ## Total number of records with filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from databarang WHERE 1 ".$searchQuery);
        $sel->execute();
        $records = $sel->fetch();
        $totalRecordwithFilter = $records['allcount'];

        ## Fetch records
        $sel = $this->db()->pdo()->prepare("select * from databarang WHERE 1 ".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row1.",".$rowperpage);
        $sel->execute();
        $result = $sel->fetchAll(\PDO::FETCH_ASSOC);

        $data = array();
        foreach($result as $row) {
            $stok = $this->db('gudangbarang')->select(['stok' => 'SUM(stok)'])->where('kode_brng', $row['kode_brng'])->toArray();
            $data[] = array(
                'kode_brng'=>$row['kode_brng'],
                'nama_brng'=>$row['nama_brng'],
                'stok'=>$stok[0]['stok'],
                'stokminimal'=>$row['stokminimal'],
                'kode_satbesar'=>$row['kode_satbesar'],
                'kode_sat'=>$row['kode_sat'],
                'dasar'=>$row['dasar'],
                'h_beli'=>$row['h_beli'],
                'isi'=>$row['isi'],
                'kapasitas'=>$row['kapasitas'],
                'expire'=>$row['expire']
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

            $search_field_databarang= $_POST['search_field_databarang'];
            $search_text_databarang = $_POST['search_text_databarang'];

            $searchQuery = " ";
            if($search_text_databarang != ''){
                $searchQuery .= " and (".$search_field_databarang." like '%".$search_text_databarang."%' ) ";
            }

            $user_lihat = $this->db()->pdo()->prepare("SELECT * from databarang WHERE 1 ".$searchQuery);
            $user_lihat->execute();
            $result = $user_lihat->fetchAll(\PDO::FETCH_ASSOC);

            $data = array();

            foreach($result as $row) {
                $stok = $this->db('gudangbarang')->select(['stok' => 'SUM(stok)'])->where('kode_brng', $row['kode_brng'])->toArray();
                $data[] = array(
                    'kode_brng'=>$row['kode_brng'],
                    'nama_brng'=>$row['nama_brng'],
                    'stok'=>$stok[0]['stok'],
                    'stokminimal'=>$row['stokminimal'],
                    'kode_satbesar'=>$row['kode_satbesar'],
                    'kode_sat'=>$row['kode_sat'],
                    'dasar'=>$row['dasar'],
                    'h_beli'=>$row['h_beli'],
                    'isi'=>$row['isi'],
                    'kapasitas'=>$row['kapasitas'],
                    'expire'=>$row['expire']
                );
            }

            echo json_encode($data);
        }
        exit();
    }

    public function getDetail($kode_brng)
    {
        $detail = $this->db('databarang')->where('kode_brng', $kode_brng)->toArray();
        echo $this->draw('detail.html', ['detail' => $detail]);
        exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/darurat_stok/css/admin/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/darurat_stok/js/admin/scripts.js', ['settings' => $settings]);
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

        $this->core->addCSS(url([ADMIN, 'darurat_stok', 'css']));
        $this->core->addJS(url([ADMIN, 'darurat_stok', 'javascript']), 'footer');
    }

}
