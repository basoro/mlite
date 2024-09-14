<?php
namespace Plugins\Mlite_Api_Tools;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Manage API'   => 'manage',
            'Alat Pengujian'   => 'tools',
            'Dokumentasi'   => 'documentations',
        ];
    }

    public function getManage(){
        $this->_addHeaderFiles();
        $disabled_menu = $this->core->loadDisabledMenu('mlite_api_tools'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        $mlite_users = $this->core->db->select('mlite_users', '*');
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu, 'mlite_users' => $mlite_users]);
    }

    public function getTools(){
        $this->_addHeaderFiles();
        return $this->draw('tools.html');
    }

    public function getDocumentations(){
        $slug = parseUrl();
        $this->_addHeaderFiles();
        $access = $this->core->getUserInfo('access');
        $access = explode(',', isset_or($access, ''));
        if($this->core->getUserInfo('role') == 'admin') {
          $access = array_column($this->core->db->select('mlite_modules', '*', ['ORDER' => 'sequence']), 'dir');
        }
        $data_json = [];
        $mlite_disabled_menu = [];
        if(!empty($slug['2'])) {
          $response = array(
            "draw" => 0, 
            "iTotalRecords" => 2,
            "iTotalDisplayRecords" => 2,
            "aaData" => $this->core->db->select($slug['2'], '*', ['LIMIT' => '2'])
          );          
          $data_json = json_encode($response, JSON_PRETTY_PRINT);
          $mlite_disabled_menu = $this->core->db->get('mlite_disabled_menu', '*', ['module' => $slug['2'], 'user' => $this->core->getUserInfo('username')]);
          if($this->core->getUserInfo('role') == 'admin') {
            $mlite_disabled_menu = [
              'create' => 'false', 
              'read' =>'false', 
              'update' =>'false', 
              'delete' => 'false'
            ];
          }
        }
        return $this->draw('documentations.html', ['modules' => $access, 'mlite_disabled_menu' => $mlite_disabled_menu, 'slug' => $slug, 'data_json' => $data_json]);
    }

    public function postData(){
        $draw = $_POST['draw'];
        $row1 = $_POST['start'];
        $rowperpage = $_POST['length']; // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], 'id'); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], 'asc'); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_mlite_api_key= isset_or($_POST['search_field_mlite_api_key']);
        $search_text_mlite_api_key = isset_or($_POST['search_text_mlite_api_key']);

        if ($search_text_mlite_api_key != '') {
          $where[$search_field_mlite_api_key.'[~]'] = $search_text_mlite_api_key;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('mlite_api_key', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('mlite_api_key', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('mlite_api_key', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'id'=>$row['id'],
                'api_key'=>$row['api_key'],
                'username'=>$row['username'],
                'method'=>$row['method'],
                'ip_range'=>$row['ip_range'],
                'exp_time'=>$row['exp_time']
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
          $this->core->LogQuery('mlite_api_tools => postData');
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

            if($this->core->loadDisabledMenu('mlite_api_tools')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $id = $_POST['id'];
            $api_key = $_POST['api_key'];
            $username = $_POST['username'];
            $method = implode(',', $_POST['method']);
            $ip_range = $_POST['ip_range'];
            $exp_time = $_POST['exp_time'];

            
            $result = $this->core->db->insert('mlite_api_key', [
              'id'=>$id, 'api_key'=>$api_key, 'username'=>$username, 'method'=>$method, 'ip_range' => $ip_range, 'exp_time'=>$exp_time
            ]);


            if (!empty($result)){
              $data = array(
                'status' => 'success', 
                'msg' => 'Data telah ditambah'
              );
            } else {
              $data = array(
                'status' => 'error', 
                'msg' => $this->core->db->errorInfo[2]
              );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('mlite_api_tools => postAksi => add');
            }
    
            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('mlite_api_tools')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $id = $_POST['id'];
            $api_key = $_POST['api_key'];
            $username = $_POST['username'];
            $method = implode(',', $_POST['method']);
            $ip_range = $_POST['ip_range'];
            $exp_time = $_POST['exp_time'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('mlite_api_key', [
              'api_key'=>$api_key, 'username'=>$username, 'method'=>$method, 'ip_range'=>$ip_range, 'exp_time'=>$exp_time
            ], [
              'id'=>$id
            ]);


            if (!empty($result)){
              $data = array(
                'status' => 'success', 
                'msg' => 'Data telah diubah'
              );
            } else {
              $data = array(
                'status' => 'error', 
                'msg' => $this->core->db->errorInfo[2]
              );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('mlite_api_tools => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('mlite_api_tools')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $id= $_POST['id'];
            $result = $this->core->db->delete('mlite_api_key', [
              'AND' => [
                'id'=>$id
              ]
            ]);

            if (!empty($result)){
              $data = array(
                'status' => 'success', 
                'msg' => 'Data telah dihapus'
              );
            } else {
              $data = array(
                'status' => 'error', 
                'msg' => $this->core->db->errorInfo[2]
              );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('mlite_api_tools => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('mlite_api_tools')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_mlite_api_key= $_POST['search_field_mlite_api_key'];
            $search_text_mlite_api_key = $_POST['search_text_mlite_api_key'];

            if ($search_text_mlite_api_key != '') {
              $where[$search_field_mlite_api_key.'[~]'] = $search_text_mlite_api_key;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('mlite_api_key', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'id'=>$row['id'],
                    'api_key'=>$row['api_key'],
                    'username'=>$row['username'],
                    'method'=>$row['method'],
                    'ip_range'=>$row['ip_range'],
                    'exp_time'=>$row['exp_time']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('mlite_api_tools => postAksi => lihat');
            }

            echo json_encode($data);
        }
        exit();
    }

    public function getRead($id)
    {

        if($this->core->loadDisabledMenu('mlite_api_tools')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('mlite_api_key', '*', ['id' => $id]);

        if (!empty($result)){
          $data = array(
            'status' => 'success', 
            'msg' => $result
          );
        } else {
          $data = array(
            'status' => 'error', 
            'msg' => 'Data tidak ditemukan'
          );
        }

        if($this->settings('settings', 'logquery') == true) {
          $this->core->LogQuery('mlite_api_tools => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($id)
    {

        if($this->core->loadDisabledMenu('mlite_api_tools')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $settings =  $this->settings('settings');

        if($this->settings('settings', 'logquery') == true) {
          $this->core->LogQuery('mlite_api_tools => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'id' => $id]);
        exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/mlite_api_tools/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        $apikey = substr(strtoupper(md5(microtime().rand(1000, 9999))), 0, 32);
        echo $this->draw(MODULES.'/mlite_api_tools/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('mlite_api_tools'), 'apikey' => $apikey]);
        exit();
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/vendor/datatables/datatables.min.css'));
        $this->core->addCSS(url('assets/vendor/daterange/daterange.css'));
        $this->core->addCSS(url('assets/css/jquery.contextMenu.css'));
        $this->core->addJS(url('assets/js/jqueryvalidation.js'), 'footer');
        $this->core->addJS(url('assets/vendor/jspdf/xlsx.js'), 'footer');
        $this->core->addJS(url('assets/vendor/jspdf/jspdf.min.js'), 'footer');
        $this->core->addJS(url('assets/vendor/jspdf/jspdf.plugin.autotable.min.js'), 'footer');
        $this->core->addJS(url('assets/vendor/datatables/datatables.min.js'), 'footer');
        $this->core->addJS(url('assets/js/jquery.contextMenu.js'), 'footer');
        $this->core->addJS(url('assets/js/prism.js'), 'footer');
        $this->core->addJS(url('assets/vendor/daterange/daterange.js'), 'footer');

        $this->core->addCSS(url([ 'mlite_api_tools', 'css']));
        $this->core->addJS(url([ 'mlite_api_tools', 'javascript']), 'footer');
    }
    
}
