<?php
namespace Plugins\Resep_Obat;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Kelola'   => 'manage',
        ];
    }

    public function getManage()
    {
        $this->_addHeaderFiles();
        $disabled_menu = $this->core->loadDisabledMenu('resep_obat'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'no_resep');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_resep_obat= isset_or($_POST['search_field_resep_obat']);
        $search_text_resep_obat = isset_or($_POST['search_text_resep_obat']);

        $searchByFromdate = isset_or($_POST['searchByFromdate'], date('Y-m-d'));
        $searchByTodate = isset_or($_POST['searchByTodate'], date('Y-m-d'));
  
        if ($search_text_resep_obat != '') {
          $where[$search_field_resep_obat.'[~]'] = $search_text_resep_obat;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        if ($searchByFromdate != '') {
          $where['tgl_perawatan[<>]'] = [$searchByFromdate,$searchByTodate];
          $where = ["AND" => $where];
        } else {
          $where['tgl_perawatan[<>]'] = [date('Y-m-d'),date('Y-m-d')];
          $where = ["AND" => $where];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('resep_obat', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('resep_obat', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('resep_obat', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_resep'=>$row['no_resep'],
                'tgl_perawatan'=>$row['tgl_perawatan'],
                'jam'=>$row['jam'],
                'no_rawat'=>$row['no_rawat'],
                'kd_dokter'=>$row['kd_dokter'],
                'tgl_peresepan'=>$row['tgl_peresepan'],
                'jam_peresepan'=>$row['jam_peresepan'],
                'status'=>$row['status'],
                'tgl_penyerahan'=>$row['tgl_penyerahan'],
                'jam_penyerahan'=>$row['jam_penyerahan']
            );
        }

        ## Response
        http_response_code(200);
        $response = array(
            "draw" => intval($draw), 
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordwithFilter,
            "aaData" => $data
        );

        if($this->settings('settings', 'logquery') == true) {
          $this->core->LogQuery('resep_obat => postData');
        }

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

            if($this->core->loadDisabledMenu('resep_obat')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $no_resep = $_POST['no_resep'];
$tgl_perawatan = $_POST['tgl_perawatan'];
$jam = $_POST['jam'];
$no_rawat = $_POST['no_rawat'];
$kd_dokter = $_POST['kd_dokter'];
$tgl_peresepan = $_POST['tgl_peresepan'];
$jam_peresepan = $_POST['jam_peresepan'];
$status = $_POST['status'];
$tgl_penyerahan = $_POST['tgl_penyerahan'];
$jam_penyerahan = $_POST['jam_penyerahan'];

            
            $result = $this->core->db->insert('resep_obat', [
'no_resep'=>$no_resep, 'tgl_perawatan'=>$tgl_perawatan, 'jam'=>$jam, 'no_rawat'=>$no_rawat, 'kd_dokter'=>$kd_dokter, 'tgl_peresepan'=>$tgl_peresepan, 'jam_peresepan'=>$jam_peresepan, 'status'=>$status, 'tgl_penyerahan'=>$tgl_penyerahan, 'jam_penyerahan'=>$jam_penyerahan
            ]);


            if (!empty($result)){
              http_response_code(200);
              $data = array(
                'code' => '200', 
                'status' => 'success', 
                'msg' => 'Data telah ditambah'
              );
            } else {
              http_response_code(201);
              $data = array(
                'code' => '201', 
                'status' => 'error', 
                'msg' => $this->core->db->errorInfo[2]
              );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('resep_obat => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('resep_obat')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $no_resep = $_POST['no_resep'];
$tgl_perawatan = $_POST['tgl_perawatan'];
$jam = $_POST['jam'];
$no_rawat = $_POST['no_rawat'];
$kd_dokter = $_POST['kd_dokter'];
$tgl_peresepan = $_POST['tgl_peresepan'];
$jam_peresepan = $_POST['jam_peresepan'];
$status = $_POST['status'];
$tgl_penyerahan = $_POST['tgl_penyerahan'];
$jam_penyerahan = $_POST['jam_penyerahan'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('resep_obat', [
'no_resep'=>$no_resep, 'tgl_perawatan'=>$tgl_perawatan, 'jam'=>$jam, 'no_rawat'=>$no_rawat, 'kd_dokter'=>$kd_dokter, 'tgl_peresepan'=>$tgl_peresepan, 'jam_peresepan'=>$jam_peresepan, 'status'=>$status, 'tgl_penyerahan'=>$tgl_penyerahan, 'jam_penyerahan'=>$jam_penyerahan
            ], [
              'no_resep'=>$no_resep
            ]);


            if (!empty($result)){
              http_response_code(200);
              $data = array(
                'code' => '200', 
                'status' => 'success', 
                'msg' => 'Data telah diubah'
              );
            } else {
              http_response_code(201);
              $data = array(
                'code' => '201', 
                'status' => 'error', 
                'msg' => $this->core->db->errorInfo[2]
              );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('resep_obat => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('resep_obat')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $no_resep= $_POST['no_resep'];
            $result = $this->core->db->delete('resep_obat', [
              'AND' => [
                'no_resep'=>$no_resep
              ]
            ]);

            if (!empty($result)){
              http_response_code(200);
              $data = array(
                'code' => '200', 
                'status' => 'success', 
                'msg' => 'Data telah dihapus'
              );
            } else {
              http_response_code(201);
              $data = array(
                'code' => '201', 
                'status' => 'error', 
                'msg' => $this->core->db->errorInfo[2]
              );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('resep_obat => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('resep_obat')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_resep_obat= $_POST['search_field_resep_obat'];
            $search_text_resep_obat = $_POST['search_text_resep_obat'];

            if ($search_text_resep_obat != '') {
              $where[$search_field_resep_obat.'[~]'] = $search_text_resep_obat;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('resep_obat', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'no_resep'=>$row['no_resep'],
'tgl_perawatan'=>$row['tgl_perawatan'],
'jam'=>$row['jam'],
'no_rawat'=>$row['no_rawat'],
'kd_dokter'=>$row['kd_dokter'],
'tgl_peresepan'=>$row['tgl_peresepan'],
'jam_peresepan'=>$row['jam_peresepan'],
'status'=>$row['status'],
'tgl_penyerahan'=>$row['tgl_penyerahan'],
'jam_penyerahan'=>$row['jam_penyerahan']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('resep_obat => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($no_resep)
    {

        if($this->core->loadDisabledMenu('resep_obat')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('resep_obat', '*', ['no_resep' => $no_resep]);

        if (!empty($result)){
          http_response_code(200);
          $data = array(
            'code' => '200', 
            'status' => 'success', 
            'msg' => $result
          );
        } else {
          http_response_code(201);
          $data = array(
            'code' => '201', 
            'status' => 'error', 
            'msg' => 'Data tidak ditemukan'
          );
        }

        if($this->settings('settings', 'logquery') == true) {
          $this->core->LogQuery('resep_obat => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($no_resep)
    {

        if($this->core->loadDisabledMenu('resep_obat')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $settings =  $this->settings('settings');

        if($this->settings('settings', 'logquery') == true) {
          $this->core->LogQuery('resep_obat => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'no_resep' => $no_resep]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('resep_obat', 'kd_dokter', ['GROUP' => 'kd_dokter']);
      $datasets = $this->core->db->select('resep_obat', ['count' => \Medoo\Medoo::raw('COUNT(<kd_dokter>)')], ['GROUP' => 'kd_dokter']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('resep_obat', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('resep_obat', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'resep_obat';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('resep_obat => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/resep_obat/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/resep_obat/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('resep_obat')]);
        exit();
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/vendor/datatables/datatables.min.css'));
        $this->core->addCSS(url('assets/css/jquery.contextMenu.css'));
        $this->core->addJS(url('assets/js/jqueryvalidation.js'), 'footer');
        $this->core->addJS(url('assets/vendor/jspdf/xlsx.js'), 'footer');
        $this->core->addJS(url('assets/vendor/jspdf/jspdf.min.js'), 'footer');
        $this->core->addJS(url('assets/vendor/jspdf/jspdf.plugin.autotable.min.js'), 'footer');
        $this->core->addJS(url('assets/vendor/datatables/datatables.min.js'), 'footer');
        $this->core->addJS(url('assets/js/jquery.contextMenu.js'), 'footer');

        $this->core->addCSS(url([ 'resep_obat', 'css']));
        $this->core->addJS(url([ 'resep_obat', 'javascript']), 'footer');
    }

}
