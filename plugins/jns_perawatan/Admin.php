<?php
namespace Plugins\Jns_Perawatan;

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
        $this->assign['kategori_perawatan'] = $this->core->db->select('kategori_perawatan', '*');
        $this->assign['penjab'] = $this->core->db->select('penjab', '*', ['status' => '1']);
        $this->assign['poliklinik'] = $this->core->db->select('poliklinik', '*', ['status' => '1']);
        $disabled_menu = $this->core->loadDisabledMenu('jns_perawatan'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['jns_perawatan' => $this->assign, 'disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'kd_jenis_prw');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_jns_perawatan= isset_or($_POST['search_field_jns_perawatan']);
        $search_text_jns_perawatan = isset_or($_POST['search_text_jns_perawatan']);

        if ($search_text_jns_perawatan != '') {
          $where[$search_field_jns_perawatan.'[~]'] = $search_text_jns_perawatan;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('jns_perawatan', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('jns_perawatan', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('jns_perawatan', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'kd_jenis_prw'=>$row['kd_jenis_prw'],
                'nm_perawatan'=>$row['nm_perawatan'],
                'kd_kategori'=>$row['kd_kategori'],
                'material'=>$row['material'],
                'bhp'=>$row['bhp'],
                'tarif_tindakandr'=>$row['tarif_tindakandr'],
                'tarif_tindakanpr'=>$row['tarif_tindakanpr'],
                'kso'=>$row['kso'],
                'menejemen'=>$row['menejemen'],
                'total_byrdr'=>$row['total_byrdr'],
                'total_byrpr'=>$row['total_byrpr'],
                'total_byrdrpr'=>$row['total_byrdrpr'],
                'kd_pj'=>$row['kd_pj'],
                'kd_poli'=>$row['kd_poli'],
                'status'=>$row['status']
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
          $this->core->LogQuery('jns_perawatan => postData');
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

            if($this->core->loadDisabledMenu('jns_perawatan')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kd_jenis_prw = $_POST['kd_jenis_prw'];
            $nm_perawatan = $_POST['nm_perawatan'];
            $kd_kategori = $_POST['kd_kategori'];
            $material = $_POST['material'];
            $bhp = $_POST['bhp'];
            $tarif_tindakandr = $_POST['tarif_tindakandr'];
            $tarif_tindakanpr = $_POST['tarif_tindakanpr'];
            $kso = $_POST['kso'];
            $menejemen = $_POST['menejemen'];
            $total_byrdr = $_POST['total_byrdr'];
            $total_byrpr = $_POST['total_byrpr'];
            $total_byrdrpr = $_POST['total_byrdrpr'];
            $kd_pj = $_POST['kd_pj'];
            $kd_poli = $_POST['kd_poli'];
            $status = $_POST['status'];
            
            $result = $this->core->db->insert('jns_perawatan', [
              'kd_jenis_prw'=>$kd_jenis_prw, 'nm_perawatan'=>$nm_perawatan, 'kd_kategori'=>$kd_kategori, 'material'=>$material, 'bhp'=>$bhp, 'tarif_tindakandr'=>$tarif_tindakandr, 'tarif_tindakanpr'=>$tarif_tindakanpr, 'kso'=>$kso, 'menejemen'=>$menejemen, 'total_byrdr'=>$total_byrdr, 'total_byrpr'=>$total_byrpr, 'total_byrdrpr'=>$total_byrdrpr, 'kd_pj'=>$kd_pj, 'kd_poli'=>$kd_poli, 'status'=>$status
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
              $this->core->LogQuery('jns_perawatan => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('jns_perawatan')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kd_jenis_prw = $_POST['kd_jenis_prw'];
            $nm_perawatan = $_POST['nm_perawatan'];
            $kd_kategori = $_POST['kd_kategori'];
            $material = $_POST['material'];
            $bhp = $_POST['bhp'];
            $tarif_tindakandr = $_POST['tarif_tindakandr'];
            $tarif_tindakanpr = $_POST['tarif_tindakanpr'];
            $kso = $_POST['kso'];
            $menejemen = $_POST['menejemen'];
            $total_byrdr = $_POST['total_byrdr'];
            $total_byrpr = $_POST['total_byrpr'];
            $total_byrdrpr = $_POST['total_byrdrpr'];
            $kd_pj = $_POST['kd_pj'];
            $kd_poli = $_POST['kd_poli'];
            $status = $_POST['status'];

            // BUANG FIELD PERTAMA

            $result = $this->core->db->update('jns_perawatan', [
              'nm_perawatan'=>$nm_perawatan, 'kd_kategori'=>$kd_kategori, 'material'=>$material, 'bhp'=>$bhp, 'tarif_tindakandr'=>$tarif_tindakandr, 'tarif_tindakanpr'=>$tarif_tindakanpr, 'kso'=>$kso, 'menejemen'=>$menejemen, 'total_byrdr'=>$total_byrdr, 'total_byrpr'=>$total_byrpr, 'total_byrdrpr'=>$total_byrdrpr, 'kd_pj'=>$kd_pj, 'kd_poli'=>$kd_poli, 'status'=>$status
            ], [
              'kd_jenis_prw'=>$kd_jenis_prw
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
              $this->core->LogQuery('jns_perawatan => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('jns_perawatan')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kd_jenis_prw= $_POST['kd_jenis_prw'];
            $result = $this->core->db->delete('jns_perawatan', [
              'AND' => [
                'kd_jenis_prw'=>$kd_jenis_prw
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
              $this->core->LogQuery('jns_perawatan => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('jns_perawatan')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_jns_perawatan= $_POST['search_field_jns_perawatan'];
            $search_text_jns_perawatan = $_POST['search_text_jns_perawatan'];

            if ($search_text_jns_perawatan != '') {
              $where[$search_field_jns_perawatan.'[~]'] = $search_text_jns_perawatan;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('jns_perawatan', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'kd_jenis_prw'=>$row['kd_jenis_prw'],
                    'nm_perawatan'=>$row['nm_perawatan'],
                    'kd_kategori'=>$row['kd_kategori'],
                    'material'=>$row['material'],
                    'bhp'=>$row['bhp'],
                    'tarif_tindakandr'=>$row['tarif_tindakandr'],
                    'tarif_tindakanpr'=>$row['tarif_tindakanpr'],
                    'kso'=>$row['kso'],
                    'menejemen'=>$row['menejemen'],
                    'total_byrdr'=>$row['total_byrdr'],
                    'total_byrpr'=>$row['total_byrpr'],
                    'total_byrdrpr'=>$row['total_byrdrpr'],
                    'kd_pj'=>$row['kd_pj'],
                    'kd_poli'=>$row['kd_poli'],
                    'status'=>$row['status']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('jns_perawatan => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($kd_jenis_prw)
    {

        if($this->core->loadDisabledMenu('jns_perawatan')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('jns_perawatan', '*', ['kd_jenis_prw' => $kd_jenis_prw]);

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
          $this->core->LogQuery('jns_perawatan => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($kd_jenis_prw)
    {

        if($this->core->loadDisabledMenu('jns_perawatan')['read'] == 'true') {
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
          $this->core->LogQuery('jns_perawatan => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'kd_jenis_prw' => $kd_jenis_prw]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('jns_perawatan', 'kd_kategori', ['GROUP' => 'kd_kategori']);
      $datasets = $this->core->db->select('jns_perawatan', ['count' => \Medoo\Medoo::raw('COUNT(<kd_kategori>)')], ['GROUP' => 'kd_kategori']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('jns_perawatan', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('jns_perawatan', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'jns_perawatan';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('jns_perawatan => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/jns_perawatan/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/jns_perawatan/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('jns_perawatan')]);
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

        $this->core->addCSS(url([ 'jns_perawatan', 'css']));
        $this->core->addJS(url([ 'jns_perawatan', 'javascript']), 'footer');
    }

}
