<?php
namespace Plugins\Mlite_Users;

use Systems\AdminModule;
use Systems\Lib\JwtManager;

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
        $this->assing = [];
        $pegawai = $this->core->db->select('pegawai', ['nik','nama','photo','jk'], ['stts_aktif' => 'AKTIF']);
        foreach($pegawai as $row) {
            $row['foto'] = $row['photo'];
            if(empty($row['photo'])) {
                $row['foto'] = 'plugins/mlite_users/img/default.png';
            }
            $this->assign['pegawai'][] = $row;
        }

        $role = array('pengguna','kasir','rekammedis','radiologi','laboratorium','paramedis','apoteker','medis','manajemen','admin');
        if (count($role)) {
          $this->assign['role'] = [];
          foreach($role as $row) {
              $this->assign['role'][] = $row;
          }
        }
        $this->assign['cap'] = $this->_getInfoCap();
        $this->assign['modules'] = $this->_getModules();        
        $disabled_menu = $this->core->loadDisabledMenu('mlite_users'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['pengguna' => $this->assign, 'disabled_menu' => $disabled_menu]);
    }

    public function postData(){
        $column_name = isset_or($_POST['column_name'], 'id');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_mlite_users= isset_or($_POST['search_field_mlite_users']);
        $search_text_mlite_users = isset_or($_POST['search_text_mlite_users']);

        if ($search_text_mlite_users != '') {
          $where[$search_field_mlite_users.'[~]'] = $search_text_mlite_users;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->dbmlite->count('mlite_users', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->dbmlite->count('mlite_users', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->dbmlite->select('mlite_users', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'id'=>$row['id'],
                'username'=>$row['username'],
                'fullname'=>$row['fullname'],
                'description'=>$row['description'],
                'password'=>$row['password'],
                'avatar'=>$row['avatar'],
                'email'=>$row['email'],
                'role'=>$row['role'],
                'cap'=>$row['cap'],
                'access'=>$row['access']
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
          $this->core->LogQuery('mlite_users => postData');
        }
        
        echo json_encode($response);
        exit();
    }

    public function postAksi($id = '')
    {
        if(isset($_POST['typeact'])){ 
            $act = $_POST['typeact']; 
        }else{ 
            $act = ''; 
        }

        // access to modules
        if ($id == 1) {
            $_POST['access'] = 'all';
        } else {
            $_POST['access'][] = 'dashboard';
            $_POST['access'] = implode(',', $_POST['access']);
        }

        $_POST['cap'][] = '-';
        $_POST['cap'] = implode(',', $_POST['cap']);


        if ($act=='add') {

            $username = $_POST['username'];
            $fullname = $_POST['fullname'];
            $description = $_POST['description'];
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $email = $_POST['email'];
            $role = $_POST['role'];
            $cap = $_POST['cap'];
            $access = $_POST['access'];
            $fileupload = $_FILES['fileToUpload']['tmp_name'];
            if($id == '1' || $role == 'admin') {
                $access = 'all';
            }

            $img = new \Systems\Lib\Image;
            if ($img->load($fileupload)) {
                if ($img->getInfos('width') < $img->getInfos('height')) {
                    $img->crop(0, 0, $img->getInfos('width'), $img->getInfos('width'));
                } else {
                    $img->crop(0, 0, $img->getInfos('height'), $img->getInfos('height'));
                }

                if ($img->getInfos('width') > 512) {
                    $img->resize(512, 512);
                }

                $avatar = uniqid('avatar').".".$img->getInfos('type');
            }
            
            $result = $this->core->dbmlite->insert('mlite_users', [
              'id'=>$id, 'username'=>$username, 'fullname'=>$fullname, 'description'=>$description, 'password'=>$password, 'avatar'=>$avatar, 'email'=>$email, 'role'=>$role, 'cap'=>$cap, 'access'=>$access
            ]);
            
            if (!empty($result)){
                if (isset($img) && $img->getInfos('width')) {
                    $img->save(UPLOADS."/users/".$avatar);
                }
                http_response_code(200);
                $data = array(
                    'code' => '200', 
                    'status' => 'success', 
                    'msg' => $username
                );
            } else {
                http_response_code(201);
                $data = array(
                    'code' => '201', 
                    'status' => 'error', 
                    'msg' => $this->core->db->errorInfo[2]
                );
            }
            echo json_encode($data);    
        }
        if ($act=="edit") {

            $id = $_POST['id'];
            $username = $_POST['username'];
            $fullname = $_POST['fullname'];
            $description = $_POST['description'];
            $email = $_POST['email'];
            $role = $_POST['role'];
            $cap = $_POST['cap'];
            $access = $_POST['access'];
            
            $fileToUpload = $_FILES['fileToUpload']['tmp_name'];

            if($id == '1' || $role == 'admin') {
                $access = 'all';
            }

            $user = $this->core->dbmlite->get('mlite_users', '*', ['id' => $id]);

            if(empty($_POST['password'])) {
                $password = $user['password'];
            } else {
                $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
            }

            $img = new \Systems\Lib\Image;
            if ($img->load($fileToUpload)) {
                if ($img->getInfos('width') < $img->getInfos('height')) {
                    $img->crop(0, 0, $img->getInfos('width'), $img->getInfos('width'));
                } else {
                    $img->crop(0, 0, $img->getInfos('height'), $img->getInfos('height'));
                }

                if ($img->getInfos('width') > 512) {
                    $img->resize(512, 512);
                }

                $avatar = uniqid('avatar').".".$img->getInfos('type');
            } else {
                $avatar = $user['avatar'];
            }

            // BUANG FIELD PERTAMA
            $result = $this->core->dbmlite->update('mlite_users', ['username'=>$username, 'fullname'=>$fullname, 'description'=>$description, 'password'=>$password, 'avatar'=>$avatar, 'email'=>$email, 'role'=>$role, 'cap'=>$cap, 'access'=>$access
            ], [
              'id'=>$id
            ]);

            if (!empty($result)){
                http_response_code(200);
                $data = array(
                    'code' => '200', 
                    'status' => 'success', 
                    'msg' => 'Data telah diubah! <br>Silahkan sesuaikan pengaturan <a href="'.url(['mlite_users','menu',$id]).'" class="btn btn-sm btn-danger">Menu Individual</a> untuk previlage CRUD.'
                );
                if (isset($img) && $img->getInfos('width')) {
                    if (isset($user)) {
                        unlink(UPLOADS."/users/".$user['avatar']);
                    }
                    $img->save(UPLOADS."/users/".$avatar);
                }
            } else {
                http_response_code(201);
                $data = array(
                    'code' => '201', 
                    'status' => 'error', 
                    'msg' => $this->core->db->errorInfo[2]
                );
            }
            echo json_encode($data);             
        }

        if ($act=="del") {
            $id= $_POST['id'];
            if($id == '1' || $this->core->dbmlite->get('mlite_users', 'role', ['id' => $id]) == 'admin') {
                http_response_code(201);
                $data = array(
                    'code' => '201', 
                    'status' => 'error', 
                    'msg' => 'Data tidak bisa dihapus'
                );
            } else {
                $result = $this->core->dbmlite->delete('mlite_users', [
                'AND' => [
                    'id'=>$id
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
            }
            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            $search_field_mlite_users= $_POST['search_field_mlite_users'];
            $search_text_mlite_users = $_POST['search_text_mlite_users'];

            if ($search_text_mlite_users != '') {
              $where[$search_field_mlite_users.'[~]'] = $search_text_mlite_users;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->dbmlite->select('mlite_users', '*', $where);

            $data = array();
            foreach($result as $row) {
                if($row['avatar'] == '') {
                    $avatar = url().'/plugins/mlite_users/img/default.png';
                } else {
                    $avatar = url().'/uploads/users/'.$row['avatar'];
                }
                $data[] = array(
                    'id'=>$row['id'],
                    'username'=>$row['username'],
                    'fullname'=>$row['fullname'],
                    'description'=>$row['description'],
                    'avatar'=>base64_encode(file_get_contents($avatar)),
                    'email'=>$row['email'],
                    'role'=>$row['role'],
                    'cap'=>$row['cap'],
                    'access'=>$row['access']
                );
            }

            echo json_encode($data);
        }
        exit();
    }

    public function getRead($id)
    {

        if($this->core->loadDisabledMenu('mlite_users')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->dbmlite->get('mlite_users', '*', ['id' => $id]);

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
          $this->core->LogQuery('mlite_users => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getUserMenu()
    {

        $requestHeaders = apache_request_headers();
        $this->requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        $secretKey = trim(isset_or($this->requestHeaders['X-Api-Key'], ''));
        $jwtManager = new JwtManager($secretKey);
        $jwt = $jwtManager->decodeToken(trim(isset_or($this->requestHeaders['X-Access-Token'], '..')));
    
        $id = isset_or($jwt['user_id']);
 
        $result = [];
        $mlite_disabled_menu = $this->core->dbmlite->select('mlite_disabled_menu', 'module', ['user' => $this->core->dbmlite->get('mlite_users', 'username', ['id' => '2']), 'hidden' => 'false']);
        foreach($mlite_disabled_menu as $row) {
            $files = [
                'info'  => MODULES.'/'.$row.'/Info.php'
            ];

            $core = $this->core;
            $info = include($files['info']);
            $row1['icon'] = $info['icon'];
            $row1['module'] = $row;
            $result[] = $row1;
        }

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
          $this->core->LogQuery('mlite_users => getUserMenu');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($id)
    {

        if($this->core->loadDisabledMenu('mlite_users')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $detail = $this->core->dbmlite->get('mlite_users', '*', ['id' => $id]);
        if(!empty($detail['avatar'])) {
            $detail['avatar_img'] = base64_encode(file_get_contents(url().'/uploads/users/'.$detail['avatar']));
        } else {
            $detail['avatar_img'] = base64_encode(file_get_contents(url().'/plugins/mlite_users/img/default.png'));
        }
        $detail['permission'] = $this->core->db->select('mlite_disabled_menu', '*', ['user' => $detail['username']]);
        $detail[] = $detail;

        $settings =  $this->settings('settings');

        if($this->settings('settings', 'logquery') == true) {
          $this->core->LogQuery('pegawai => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'id' => $id, 'detail' => $detail]);
        exit();
    }

    public function getMenu($id)
    {
        $this->_addHeaderFiles();
        $access = [];
        $modules = explode(",",$this->core->dbmlite->get('mlite_users', 'access', ['id' => $id]));
        foreach($modules as $value) {
            $row['module'] = $value;
            $row['create'] = $this->core->dbmlite->get('mlite_disabled_menu', 'create', ['user' => $this->core->getUserInfo('username', $id, true), 'module' => $value]);
            $row['read'] = $this->core->dbmlite->get('mlite_disabled_menu', 'read', ['user' => $this->core->getUserInfo('username', $id, true), 'module' => $value]);
            $row['update'] = $this->core->dbmlite->get('mlite_disabled_menu', 'update', ['user' => $this->core->getUserInfo('username', $id, true), 'module' => $value]);
            $row['delete'] = $this->core->dbmlite->get('mlite_disabled_menu', 'delete', ['user' => $this->core->getUserInfo('username', $id, true), 'module' => $value]);    
            $row['hidden'] = $this->core->dbmlite->get('mlite_disabled_menu', 'hidden', ['user' => $this->core->getUserInfo('username', $id, true), 'module' => $value]);    
            $access[] = $row;
        }
        return $this->draw('menu.html', ['settings' => $this->settings('settings'), 'access' => $access, 'user' =>  $this->core->getUserInfo('username', $id, true), 'fullname' =>  $this->core->getUserInfo('fullname', $id, true)]);
    }

    public function postAksiMenu()
    {
        $user = $_POST['user'];
        $modules = $_POST['module'];

        $this->core->dbmlite->delete('mlite_disabled_menu', ['user' => $user]);
        
        foreach($modules as $module) {
            $create = 'false';
            $read = 'false';
            $update = 'false';
            $delete = 'false';
            $hidden = 'false';
            if(isset($_POST[$module.'-create']) && $_POST[$module.'-create'] == 'on') {
                $create = 'true';
            }
            if(isset($_POST[$module.'-read']) && $_POST[$module.'-read'] == 'on') {
                $read = 'true';
            }
            if(isset($_POST[$module.'-update']) && $_POST[$module.'-update'] == 'on') {
                $update = 'true';
            }
            if(isset($_POST[$module.'-delete']) && $_POST[$module.'-delete'] == 'on') {
                $delete = 'true';
            }
            if(isset($_POST[$module.'-hidden']) && $_POST[$module.'-hidden'] == 'on') {
                $hidden = 'true';
            }

            $result = $this->core->dbmlite->insert('mlite_disabled_menu', [
                'user' => $user, 
                'module' => $module, 
                'create' => $create, 
                'read' => $read, 
                'update' => $update, 
                'delete' => $delete, 
                'hidden' => $hidden
            ]);
            if (!empty($result)){
                http_response_code(200);
                $data = array(
                    'code' => '200', 
                    'status' => 'success', 
                    'msg' => $user
                );
            } else {
                http_response_code(201);
                $data = array(
                    'code' => '201', 
                    'status' => 'error', 
                    'msg' => $this->core->db->errorInfo[2]
                );
            }

        }
        echo json_encode($data);

        exit();
    }


    public function postGetPengguna()
    {
        $username = $_POST['username'];
        $rows = $this->core->dbmlite->get('mlite_users', '*', ['username' => $username]);
        echo json_encode($rows);
        exit();
    }    

    private function _getInfoCap($kd_poli = null)
    {
        $result = [];
        
        $poliklinik = $this->core->db->select('poliklinik', [
            'kd_poli(cap)', 
            'nm_poli(nm_cap)'
        ], ['status' =>'1']);
        $bangsal = $this->core->db->select('bangsal', [
            'kd_bangsal(cap)', 
            'nm_bangsal(nm_cap)'
        ]);

        $rows = array_merge($poliklinik, $bangsal);

        if (!$kd_poli) {
            $kd_poliArray = [];
        } else {
            $kd_poliArray = explode(',', $kd_poli);
        }

        foreach ($rows as $row) {
            if ($row['cap'] != '-') {
                if (empty($kd_poliArray)) {
                    $attr = '';
                } else {
                    if (in_array($row['cap'], $kd_poliArray)) {
                        $attr = 'selected';
                    } else {
                        $attr = '';
                    }
                }
                $result[] = ['cap' => $row['cap'], 'nm_cap' => $row['nm_cap'], 'attr' => $attr];
            }
        }
        return $result;
    }

    private function _getModules($access = null)
    {
        $result = [];
        $rows = $this->core->dbmlite->select('mlite_modules', '*');

        if (!$access) {
            $accessArray = [];
        } else {
            $accessArray = explode(',', $access);
        }

        foreach ($rows as $row) {
            if ($row['dir'] != 'dashboard') {
                $details = $this->core->getModuleInfo($row['dir']);

                if (empty($accessArray)) {
                    $attr = '';
                } else {
                    if (in_array($row['dir'], $accessArray) || ($accessArray[0] == 'all')) {
                        $attr = 'selected';
                    } else {
                        $attr = '';
                    }
                }
                $result[] = ['dir' => $row['dir'], 'name' => $details['name'], 'attr' => $attr];
            }
        }
        return $result;
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/mlite_users/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/mlite_users/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('mlite_users')]);
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

        $this->core->addCSS(url([ 'mlite_users', 'css']));
        $this->core->addJS(url([ 'mlite_users', 'javascript']), 'footer');
    }

}
