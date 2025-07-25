<?php

namespace Plugins\Users;

use Systems\AdminModule;

class Admin extends AdminModule
{
    private $assign = [];

    public function navigation()
    {
        return [
            'Kelola'    => 'index',
            'Data Pengguna' => 'manage',
            'Tambah Baru'                => 'add'
        ];
    }

    public function getIndex()
    {
      $sub_modules = [
        ['name' => 'Data Pengguna', 'url' => url([ADMIN, 'users', 'manage']), 'icon' => 'users', 'desc' => 'Data pengguna'],
        ['name' => 'Tambah Baru', 'url' => url([ADMIN, 'users', 'add']), 'icon' => 'user-plus', 'desc' => 'Tambah pengguna baru'],
      ];
      return $this->draw('index.html', ['sub_modules' => $sub_modules]);
    }

    /**
    * users list
    */
    public function getManage()
    {
        if($this->settings->get('settings.versi_beta') == 'ya') { 
            $this->_addHeaderFilesBeta();
            return $this->draw('manage.beta.html');    
        } else {
            $rows = $this->db('mlite_users')->toArray();
            foreach ($rows as &$row) {
                if (empty($row['fullname'])) {
                    $row['fullname'] = '----';
                }
                $row['editURL'] = url([ADMIN, 'users', 'edit', $row['id']]);
                $row['delURL']  = url([ADMIN, 'users', 'delete', $row['id']]);
            }
            $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
            $this->core->addCSS(url([ADMIN, 'users', 'css']));
            $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
            $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
            return $this->draw('manage.html', ['myId' => $this->core->getUserInfo('id'), 'users' => $rows]);
        }
    }

    /**
    * add new user
    */
    public function getAdd()
    {
        if($this->db('mlite_modules')->where('dir', 'kepegawaian')->oneArray()) {
          $this->_addInfoUser();
        }
        $this->_getInfoRole();
        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = ['username' => '', 'email' => '', 'fullname' => '', 'description' => '', 'role' => '', 'cap' => ''];
        }

        $this->assign['title'] = 'Pengguna baru';
        $this->assign['modules'] = $this->_getModules('all');
        $this->assign['cap'] = [];
        if($this->db('mlite_modules')->where('dir', 'kepegawaian')->oneArray()) {
          $this->assign['cap'] = $this->_getInfoCap();
        }
        $this->assign['avatarURL'] = url(MODULES.'/users/img/default.png');

        return $this->draw('form.html', ['users' => $this->assign]);
    }

    /**
    * edit user
    */
    public function getEdit($id)
    {
        if($this->db('mlite_modules')->where('dir', 'kepegawaian')->oneArray()) {
          $this->_addInfoUser();
        }
        $this->_getInfoRole();
        $user = $this->db('mlite_users')->oneArray($id);

        if (!empty($user)) {
            $this->assign['form'] = $user;
            $this->assign['title'] = 'Sunting pengguna';
            $this->assign['modules'] = $this->_getModules($user['access']);
            $this->assign['cap'] = [];
            if($this->db('mlite_modules')->where('dir', 'kepegawaian')->oneArray()) {
              $this->assign['cap'] = $this->_getInfoCap($user['cap']);
            }
            $this->assign['avatarURL'] = url(UPLOADS.'/users/'.$user['avatar']);

            return $this->draw('form.html', ['users' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'users', 'manage']));
        }
    }

    /**
    * save user data
    */
    public function postSave($id = null)
    {
        $errors = 0;

        // location to redirect
        if (!$id) {
            $location = url([ADMIN, 'users', 'add']);
        } else {
            $location = url([ADMIN, 'users', 'edit', $id]);
        }

        // admin
        if ($id == 1) {
            $_POST['access'] = ['all'];
        }

        // check if required fields are empty
        if (checkEmptyFields(['username', 'email', 'access'], $_POST)) {
            $this->notify('failure', 'Isian kosong.');
            redirect($location, $_POST);
        }

        // check if user already exists
        if ($this->_userAlreadyExists($id)) {
            $errors++;
            $this->notify('failure', 'Pengguna sudah terdaftar.');
        }
        // chech if e-mail adress is correct
        $_POST['email'] = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors++;
            $this->notify('failure', 'Email salah');
        }
        // check if password is longer than 5 characters
        if (isset($_POST['password']) && strlen($_POST['password']) < 8) {
            $errors++;
            $this->notify('failure', 'Password terlalu pendek. Minimal 8 karakter serta memuat kombinasi huruf besar, huruf kecil, angka, dan karakter khusus ');
        }
        // access to modules
        if ((count($_POST['access']) == count($this->_getModules())) || ($id == 1)) {
            $_POST['access'] = 'all';
        } else {
            $_POST['access'][] = 'dashboard';
            $_POST['access'] = implode(',', $_POST['access']);
        }

        if($_POST['cap'] == '') {
          $_POST['cap'] = [];
        }

        $_POST['cap'] = implode(',', $_POST['cap']);

        // CREATE / EDIT
        if (!$errors) {
            unset($_POST['save']);

            if (!empty($_POST['password'])) {
                $_POST['password'] = password_hash($_POST['password'], PASSWORD_BCRYPT);
            }

            if (($photo = isset_or($_FILES['photo']['tmp_name'], false)) || !$id) {
                $img = new \Systems\Lib\Image;

                if (empty($photo) && !$id) {
                    $photo = MODULES.'/users/img/default.png';
                }
                if ($img->load($photo)) {
                    if ($img->getInfos('width') < $img->getInfos('height')) {
                        $img->crop(0, 0, $img->getInfos('width'), $img->getInfos('width'));
                    } else {
                        $img->crop(0, 0, $img->getInfos('height'), $img->getInfos('height'));
                    }

                    if ($img->getInfos('width') > 512) {
                        $img->resize(512, 512);
                    }

                    if ($id) {
                        $user = $this->db('mlite_users')->oneArray($id);
                    }

                    $_POST['avatar'] = uniqid('avatar').".".$img->getInfos('type');
                }
            }

            if (!$id) {    // new
                $query = $this->db('mlite_users')->save($_POST);
            } else {        // edit
                $query = $this->db('mlite_users')->where('id', $id)->save($_POST);
            }

            if ($query) {
                if (isset($img) && $img->getInfos('width')) {
                    if (isset($user)) {
                        unlink(UPLOADS."/users/".$user['avatar']);
                    }

                    $img->save(UPLOADS."/users/".$_POST['avatar']);
                }

                $this->notify('success', 'Pengguna berhasil disimpan.');
            } else {
                $this->notify('failure', 'Gagak menyimpan pengguna.');
            }

            redirect($location);
        }

        redirect($location, $_POST);
    }

    /**
    * remove user
    */
    public function getDelete($id)
    {
        if ($id != 1 && $this->core->getUserInfo('id') != $id && ($user = $this->db('mlite_users')->oneArray($id))) {
            if ($this->db('mlite_users')->delete($id)) {
                if (!empty($user['avatar'])) {
                    unlink(UPLOADS."/users/".$user['avatar']);
                }

                $this->notify('success', 'Pengguna berhasil dihapus.');
            } else {
                $this->notify('failure', 'Tak dapat menghapus pengguna.');
            }
        }
        redirect(url([ADMIN, 'users', 'manage']));
    }

    private function _addInfoUser() {
        // get users
        $rows = $this->db('pegawai')->where('stts_aktif', '!=', 'KELUAR')->toArray();

        if (count($rows)) {
          $this->assign['user'] = [];
          foreach($rows as $row) {
              $this->assign['user'][] = $row;
          }
        }
    }

    /**
    * list of active user roles
    * @return array
    */

    private function _getInfoRole() {
      $role = array('pengguna','kasir','rekammedis','radiologi','laboratorium','paramedis','apoteker','medis','manajemen','admin');
      if (count($role)) {
        $this->assign['role'] = [];
        foreach($role as $row) {
            $this->assign['role'][] = $row;
        }
      }
    }

    private function _getInfoCap($kd_poli = null)
    {
        $result = [];
        $rows = $this->db()->pdo()->prepare("(SELECT kd_poli AS cap, nm_poli AS nm_cap FROM poliklinik) UNION (SELECT kd_bangsal AS cap, nm_bangsal AS nm_cap FROM bangsal)");
        $rows->execute();
        $rows = $rows->fetchAll();

        if (!$kd_poli) {
            $kd_poliArray = [];
        } else {
            $kd_poliArray = explode(',', $kd_poli);
        }

        foreach ($rows as $row) {
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
        return $result;
    }

    /**
    * list of active modules
    * @return array
    */
    private function _getModules($access = null)
    {
        $result = [];
        $rows = $this->db('mlite_modules')->toArray();

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
                $result[] = ['dir' => $row['dir'], 'name' => $details['name'], 'icon' => $details['icon'], 'attr' => $attr];
            }
        }
        return $result;
    }

    /**
    * check if user already exists
    * @return array
    */
    private function _userAlreadyExists($id = null)
    {
        if (!$id) {    // new
            $count = $this->db('mlite_users')->where('username', $_POST['username'])->count();
        } else {        // edit
            $count = $this->db('mlite_users')->where('username', $_POST['username'])->where('id', '<>', $id)->count();
        }
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/users/css/admin/users.css');
        exit();
    }

    public function getManageBeta()
    {
        $this->_addHeaderFilesBeta();
        return $this->draw('manage.beta.html');
    }

    public function getMenu($id)
    {
        $this->_addHeaderFilesBeta();
        $access = [];
        // $modules = explode(",",$this->db('mlite_users')->select('access')->where('id', $id)->oneArray());
        $modules = explode(',', $this->core->getUserInfo('access', $id, true));
        foreach($modules as $value) {
            $row['module'] = $value;
            $mlite_disabled_menu = $this->db('mlite_disabled_menu')->where('user', $this->core->getUserInfo('username', $id, true))->where('module', $value)->oneArray();
            $row['create'] = isset_or($mlite_disabled_menu['can_create']);
            $row['read'] = isset_or($mlite_disabled_menu['can_read']);
            $row['update'] = isset_or($mlite_disabled_menu['can_update']);
            $row['delete'] = isset_or($mlite_disabled_menu['can_delete']);
            $access[] = $row;
        }
        return $this->draw('menu.html', ['settings' => $this->settings('settings'), 'access' => $access, 'user' =>  $this->core->getUserInfo('username', $id, true), 'fullname' =>  $this->core->getUserInfo('fullname', $id, true)]);
    }

    public function postAksiMenu()
    {
        $user = $_POST['user'];
        $modules = $_POST['module'];

        $this->db('mlite_disabled_menu')->where('user', $user)->delete();
        
        foreach($modules as $module) {
            $create = 'false';
            $read = 'false';
            $update = 'false';
            $delete = 'false';
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

            $result = $this->db('mlite_disabled_menu')->save([
                'id' => NULL, 
                'user' => $user, 
                'module' => $module, 
                'can_create' => $create, 
                'can_read' => $read, 
                'can_update' => $update, 
                'can_delete' => $delete
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
                    'msg' => 'error'
                );
            }

        }
        echo json_encode($data);

        exit();
    }

    public function postData()
    {
        $draw = $_POST['draw'] ?? 0;
        $row1 = $_POST['start'] ?? 0;
        $rowperpage = $_POST['length'] ?? 10;
        $columnIndex = $_POST['order'][0]['column'] ?? 0;
        $columnName = $_POST['columns'][$columnIndex]['data'] ?? 'role';
        $columnSortOrder = $_POST['order'][0]['dir'] ?? 'asc';
        $searchValue = $_POST['search']['value'] ?? '';

        $search_field = $_POST['search_field_mlite_users'] ?? '';
        $search_text = $_POST['search_text_mlite_users'] ?? '';

        $searchQuery = "";
        if (!empty($search_text)) {
            $searchQuery .= " AND (" . $search_field . " LIKE :search_text) ";
        }

        $stmt = $this->db()->pdo()->prepare("SELECT COUNT(*) AS allcount FROM mlite_users");
        $stmt->execute();
        $records = $stmt->fetch();
        $totalRecords = $records['allcount'];

        $stmt = $this->db()->pdo()->prepare("SELECT COUNT(*) AS allcount FROM mlite_users WHERE 1=1 $searchQuery");
        if (!empty($search_text)) {
            $stmt->bindValue(':search_text', "%$search_text%", \PDO::PARAM_STR);
        }
        $stmt->execute();
        $records = $stmt->fetch();
        $totalRecordwithFilter = $records['allcount'];

        $sql = "SELECT * FROM mlite_users WHERE 1=1 $searchQuery ORDER BY $columnName $columnSortOrder LIMIT $row1, $rowperpage";
        $stmt = $this->db()->pdo()->prepare($sql);
        if (!empty($search_text)) {
            $stmt->bindValue(':search_text', "%$search_text%", \PDO::PARAM_STR);
        }
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $data = [];
        foreach ($result as $row) {
            $data[] = [
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

            ];
        }

        echo json_encode([
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordwithFilter,
            "aaData" => $data
        ]);
        exit();
    }

    public function postAksi()
    {
        $act = $_POST['typeact'] ?? '';

        if (!in_array($act, ['add', 'edit', 'del', 'lihat'])) {
            echo json_encode(["status" => "error", "message" => "Aksi tidak dikenali."]);
            exit();
        }

        try {
            if ($act == 'add') {
                $id = $_POST['id'];
$username = $_POST['username'];
$fullname = $_POST['fullname'];
$description = $_POST['description'];
$password = $_POST['password'];
$avatar = $_POST['avatar'];
$email = $_POST['email'];
$role = $_POST['role'];
$cap = $_POST['cap'];
$access = $_POST['access'];


                $sql = "INSERT INTO mlite_users VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $binds = [$id, $username, $fullname, $description, $password, $avatar, $email, $role, $cap, $access];
                $stmt = $this->db()->pdo()->prepare($sql);
                $stmt->execute($binds);

                if($this->settings->get('settings.log_query') == 'ya') {
                    \Systems\Lib\QueryWrapper::logPdoQuery($sql, $binds);
                }

                echo json_encode(["status" => "success", "message" => "Data berhasil ditambahkan."]);

            } elseif ($act == 'edit') {
                $id = $_POST['id'];
$username = $_POST['username'];
$fullname = $_POST['fullname'];
$description = $_POST['description'];
$password = $_POST['password'];
$avatar = $_POST['avatar'];
$email = $_POST['email'];
$role = $_POST['role'];
$cap = $_POST['cap'];
$access = $_POST['access'];


                $sql = "UPDATE mlite_users SET id=?, username=?, fullname=?, description=?, password=?, avatar=?, email=?, role=?, cap=?, access=? WHERE id=?";
                $binds = [$id, $username, $fullname, $description, $password, $avatar, $email, $role, $cap, $access,$id];
                $stmt = $this->db()->pdo()->prepare($sql);
                $stmt->execute($binds);

                if($this->settings->get('settings.log_query') == 'ya') {
                    \Systems\Lib\QueryWrapper::logPdoQuery($sql, $binds);
                }
                echo json_encode(["status" => "success", "message" => "Data berhasil diperbarui."]);

            } elseif ($act == 'del') {
                $id= $_POST['id'];

                $sql = "DELETE FROM mlite_users WHERE id='$id'";
                $binds = [];

                $stmt = $this->db()->pdo()->prepare($sql);
                $stmt->execute();

                if($this->settings->get('settings.log_query') == 'ya') {
                    \Systems\Lib\QueryWrapper::logPdoQuery($sql, $binds);
                }

                if ($stmt->rowCount() > 0) {
                    echo json_encode(["status" => "success", "message" => "Data berhasil dihapus."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Data tidak ditemukan atau gagal dihapus."]);
                }

            } elseif ($act == 'lihat') {
                $search_field = $_POST['search_field_mlite_users'] ?? '';
                $search_text = $_POST['search_text_mlite_users'] ?? '';

                $searchQuery = "";
                if (!empty($search_text)) {
                    $searchQuery .= " AND (" . $search_field . " LIKE :search_text) ";
                }

                $stmt = $this->db()->pdo()->prepare("SELECT * FROM mlite_users WHERE 1=1 $searchQuery");

                if (!empty($search_text)) {
                    $stmt->bindValue(':search_text', "%$search_text%", \PDO::PARAM_STR);
                }

                $stmt->execute();
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                $data = [];
                foreach ($result as $row) {
                    $data[] = [
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
                    ];
                }

                echo json_encode($data);
            }
        } catch (\PDOException $e) {
            if($this->settings->get('settings.log_query') == 'ya') {            
                if (in_array($act, ['add', 'edit', 'del'])) {
                \Systems\Lib\QueryWrapper::logPdoQuery($sql, $binds, $e->getMessage());   
                } 
            }
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }

        exit();
    }

    public function getDetail($id)
    {
        $detail = $this->db('mlite_users')->where('id', $id)->toArray();
        $settings =  $this->settings('settings');
        echo $this->draw('detail.html', ['detail' => $detail, 'settings' => $settings]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
        if ($type == '') {
            $type = 'pie';
        }

        $labels = $this->db('mlite_users')->select('role')->group('role')->toArray();
        $labels = json_encode(array_column($labels, 'role'));
        $datasets = $this->db('mlite_users')->select('COUNT(role)')->group('role')->toArray();
        $datasets = json_encode(array_column($datasets, 'COUNT(role)'));

        if (!empty($column)) {
            $labels = $this->db('mlite_users')->select($column)->group($column)->toArray();
            $labels = json_encode(array_column($labels, $column));
            $datasets = $this->db('mlite_users')->select("COUNT($column)")->group($column)->toArray();
            $datasets = json_encode(array_column($datasets, "COUNT($column)"));
        }

        $database = DBNAME;
        $nama_table = 'mlite_users';

        $stmt = $this->db()->pdo()->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=?");
        $stmt->execute([$database, $nama_table]);
        $result = $stmt->fetchAll();

        echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => $labels, 'datasets' => $datasets]);
        exit();
    }

    public function getCssBeta()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/users/css/admin/styles.css');
        exit();
    }

    public function getJavascriptBeta()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/users/js/admin/scripts.js', ['settings' => $settings]);
        exit();
    }

    private function _addHeaderFilesBeta()
    {
        $this->core->addCSS(url('assets/css/datatables.min.css'));
        $this->core->addCSS(url('assets/css/jquery.contextMenu.min.css'));
        $this->core->addJS(url('assets/jscripts/jqueryvalidation.js'));
        $this->core->addJS(url('assets/jscripts/xlsx.js'));
        $this->core->addJS(url('assets/jscripts/jspdf.min.js'));
        $this->core->addJS(url('assets/jscripts/jspdf.plugin.autotable.min.js'));
        $this->core->addJS(url('assets/jscripts/datatables.min.js'));
        $this->core->addJS(url('assets/jscripts/jquery.contextMenu.min.js'));

        $this->core->addCSS(url([ADMIN, 'users', 'cssbeta']));
        $this->core->addJS(url([ADMIN, 'users', 'javascriptbeta']), 'footer');
    }

}
