<?php
namespace Plugins\Log_Antrian_TaskID;

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
        $search_field_mlite_antrian_referensi_taskid= $_POST['search_field_mlite_antrian_referensi_taskid'];
        $search_text_mlite_antrian_referensi_taskid = $_POST['search_text_mlite_antrian_referensi_taskid'];

        $searchQuery = " ";
        if($search_text_mlite_antrian_referensi_taskid != ''){
            $searchQuery .= " and (".$search_field_mlite_antrian_referensi_taskid." like '%".$search_text_mlite_antrian_referensi_taskid."%' ) ";
        }

        ## Total number of records without filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from mlite_antrian_referensi_taskid");
        $sel->execute();
        $records = $sel->fetch();
        $totalRecords = $records['allcount'];

        ## Total number of records with filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from mlite_antrian_referensi_taskid WHERE 1 ".$searchQuery);
        $sel->execute();
        $records = $sel->fetch();
        $totalRecordwithFilter = $records['allcount'];

        ## Fetch records
        $sel = $this->db()->pdo()->prepare("select * from mlite_antrian_referensi_taskid WHERE 1 ".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row1.",".$rowperpage);
        $sel->execute();
        $result = $sel->fetchAll(\PDO::FETCH_ASSOC);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'tanggal_periksa'=>$row['tanggal_periksa'],
'nomor_referensi'=>$row['nomor_referensi'],
'taskid'=>$row['taskid'],
'waktu'=>$row['waktu'],
'status'=>$row['status'],
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

        if ($act=='add') {

        $tanggal_periksa = $_POST['tanggal_periksa'];
        $nomor_referensi = $_POST['nomor_referensi'];
        $taskid = $_POST['taskid'];
        $waktu = $_POST['waktu'];
        $status = $_POST['status'];
        $keterangan = $_POST['keterangan'];

            
            $mlite_antrian_referensi_taskid_add = $this->db()->pdo()->prepare('INSERT INTO mlite_antrian_referensi_taskid VALUES (?, ?, ?, ?, ?, ?)');
            $mlite_antrian_referensi_taskid_add->execute([$tanggal_periksa, $nomor_referensi, $taskid, $waktu, $status, $keterangan]);

        }
        if ($act=="edit") {

        $tanggal_periksa = $_POST['tanggal_periksa'];
        $nomor_referensi = $_POST['nomor_referensi'];
        $taskid = $_POST['taskid'];
        $waktu = $_POST['waktu'];
        $status = $_POST['status'];
        $keterangan = $_POST['keterangan'];


        // BUANG FIELD PERTAMA

            $mlite_antrian_referensi_taskid_edit = $this->db()->pdo()->prepare("UPDATE mlite_antrian_referensi_taskid SET tanggal_periksa=?, nomor_referensi=?, taskid=?, waktu=?, status=?, keterangan=? WHERE tanggal_periksa=?");
            $mlite_antrian_referensi_taskid_edit->execute([$tanggal_periksa, $nomor_referensi, $taskid, $waktu, $status, $keterangan,$tanggal_periksa]);
        
        }

        if ($act=="del") {
            $tanggal_periksa= $_POST['tanggal_periksa'];
            $check_db = $this->db()->pdo()->prepare("DELETE FROM mlite_antrian_referensi_taskid WHERE tanggal_periksa='$tanggal_periksa'");
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

            $search_field_mlite_antrian_referensi_taskid= $_POST['search_field_mlite_antrian_referensi_taskid'];
            $search_text_mlite_antrian_referensi_taskid = $_POST['search_text_mlite_antrian_referensi_taskid'];

            $searchQuery = " ";
            if($search_text_mlite_antrian_referensi_taskid != ''){
                $searchQuery .= " and (".$search_field_mlite_antrian_referensi_taskid." like '%".$search_text_mlite_antrian_referensi_taskid."%' ) ";
            }

            $user_lihat = $this->db()->pdo()->prepare("SELECT keterangan, count('keterangan') as jumlah from mlite_antrian_referensi_taskid WHERE 1 $searchQuery group by keterangan order by jumlah desc");
            $user_lihat->execute();
            $result = $user_lihat->fetchAll(\PDO::FETCH_ASSOC);

            $data = array();

            foreach($result as $row) {
                $data[] = array(
                    'keterangan'=>$row['keterangan'],
                    'jumlah'=>$row['jumlah']
                );
            }

            echo json_encode($data);
        }
        exit();
    }

    public function getDetail($tanggal_periksa)
    {
        $detail = $this->db('mlite_antrian_referensi_taskid')->where('tanggal_periksa', $tanggal_periksa)->toArray();
        echo $this->draw('detail.html', ['detail' => $detail]);
        exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/log_antrian_taskid/css/admin/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/log_antrian_taskid/js/admin/scripts.js', ['settings' => $settings]);
        exit();
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('plugins/log_antrian_taskid/css/admin/datatables.min.css'));
        $this->core->addJS(url('plugins/log_antrian_taskid/js/admin/jqueryvalidation.js'));
        $this->core->addJS(url('plugins/log_antrian_taskid/js/admin/xlsx.js'));
        $this->core->addJS(url('plugins/log_antrian_taskid/js/admin/jspdf.min.js'));
        $this->core->addJS(url('plugins/log_antrian_taskid/js/admin/jspdf.plugin.autotable.min.js'));
        $this->core->addJS(url('plugins/log_antrian_taskid/js/admin/datatables.min.js'));

        $this->core->addCSS(url([ADMIN, 'log_antrian_taskid', 'css']));
        $this->core->addJS(url([ADMIN, 'log_antrian_taskid', 'javascript']), 'footer');
    }

}
