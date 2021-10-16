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

    /**
    * add new user
    */
    public function getAdd()
    {
        $this->_addInfoUser();
        $this->_getInfoRole();
        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = ['username' => '', 'email' => '', 'fullname' => '', 'description' => '', 'role' => '', 'cap' => ''];
        }

        $this->assign['title'] = 'Pengguna baru';
        $this->assign['modules'] = $this->_getModules('all');
        $this->assign['cap'] = $this->_getInfoCap();
        $this->assign['avatarURL'] = url(MODULES.'/users/img/default.png');

        return $this->draw('form.html', ['users' => $this->assign]);
    }

    /**
    * edit user
    */
    public function getEdit($id)
    {
        $this->_addInfoUser();
        $this->_getInfoRole();
        $user = $this->db('mlite_users')->oneArray($id);

        if (!empty($user)) {
            $this->assign['form'] = $user;
            $this->assign['title'] = 'Sunting pengguna';
            $this->assign['modules'] = $this->_getModules($user['access']);
            $this->assign['cap'] = $this->_getInfoCap($user['cap']);
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

}
