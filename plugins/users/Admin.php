<?php

namespace Plugins\Users;

use Systems\AdminModule;

class Admin extends AdminModule
{
    private $assign = [];

    public function navigation()
    {
        return [
            'Kelola'    => 'manage',
            'Tambah Baru'                => 'add'
        ];
    }

    /**
    * users list
    */
    public function getManage()
    {
        $this->_addHeaderFiles();
        $rows = $this->db()->pdo()->prepare("SELECT lite_roles.*, pegawai.nama as nama, AES_DECRYPT(user.password,'windi') as password FROM lite_roles, pegawai, user WHERE pegawai.nik = lite_roles.username AND pegawai.nik = AES_DECRYPT(user.id_user,'nur') AND lite_roles.id !=1");
        $rows->execute();
        $rows = $rows->fetchAll();

        foreach ($rows as &$row) {
            if (empty($row['nama'])) {
                $row['nama'] = '----';
            }
            $row['editURL'] = url([ADMIN, 'users', 'edit', $row['id']]);
            $row['delURL']  = url([ADMIN, 'users', 'delete', $row['id']]);
        }

        return $this->draw('manage.html', ['myId' => $this->core->getUserInfo('id'), 'users' => $rows]);
    }


    /**
    * add new user
    */
    public function getAdd()
    {
        $this->_addHeaderFiles();
        $this->_addInfoUser();
        $this->_addInfoRole();
        $this->_addInfoCap();

        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = ['username' => '', 'role' => '', 'cap' => '', 'access' => ''];
        }

        $this->assign['title'] = 'Pengguna baru';
        $this->assign['modules'] = $this->_getModules('all');

        return $this->draw('form.html', ['users' => $this->assign]);
    }

    /**
    * edit user
    */
    public function getEdit($id)
    {
        $row = $this->db('lite_roles')->oneArray($id);

        $this->_addHeaderFiles();
        $this->_addInfoUser();
        $this->_addInfoRole();
        $this->_addInfoCap();

        if (!empty($row)) {
            $this->assign['form'] = $row;
            $this->assign['title'] = 'Edit pengguna';
            $this->assign['modules'] = $this->_getModules($row['access']);

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
        if (checkEmptyFields(['username', 'access'], $_POST)) {
            $this->notify('failure', 'Isian kosong');
            redirect($location, $_POST);
        }

        // check if user already exists
        if ($this->_userAlreadyExists($id)) {
            $errors++;
            $this->notify('failure', 'Pengguna sudah ada');
        }
        // check if password is longer than 5 characters
        if (isset($_POST['password']) && strlen($_POST['password']) < 5) {
            $errors++;
            $this->notify('failure', 'Kata kunci terlalu pendek');
        }
        // access to modules
        if ((count($_POST['access']) == count($this->_getModules())) || ($id == 1)) {
            $_POST['access'] = 'all';
        } else {
            $_POST['access'][] = 'dashboard';
            $_POST['access'] = implode(',', $_POST['access']);
        }

        // CREATE / EDIT
        if (!$errors) {
            unset($_POST['save']);

            if (!$id) {    // new
                $this->core->db()->pdo()->exec("DROP TABLE IF EXISTS temp_user");
                $this->core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS temp_user LIKE user");
                $this->core->db()->pdo()->exec("INSERT INTO temp_user SELECT * FROM user WHERE id_user=(SELECT id_user FROM user LIMIT 1)");
                $this->core->db()->pdo()->exec("UPDATE temp_user SET id_user=AES_ENCRYPT('$_POST[username]','nur'), password=AES_ENCRYPT('$_POST[username]','windi')");

                $row_user = $this->db()->pdo()->prepare("SELECT AES_DECRYPT('$_POST[username]','nur') FROM user");
                $row_user->execute();
                $row_user = $row_user->fetch();

                if(!$row_user) {
                  $this->core->db()->pdo()->exec("INSERT INTO user SELECT * FROM temp_user");
                }

                $this->core->db()->pdo()->exec("DROP TABLE temp_user");
                $query = $this->db('lite_roles')->save($_POST);

            } else {        // edit
                $query = $this->db('lite_roles')->where('id', $id)->save($_POST);
            }

            if ($query) {
                $this->notify('success', 'Simpan sukes');
            } else {
                $this->notify('failure', 'Simpan gagal');
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
        if ($id != 1 && $this->core->getUserInfo('id') != $id && ($row = $this->db('lite_roles')->oneArray($id))) {
            if ($this->db('lite_roles')->delete($id)) {
                $this->notify('success', 'Hapus sukses');
            } else {
                $this->notify('failure', 'Hapus gagal');
            }
        }
        redirect(url([ADMIN, 'users', 'manage']));
    }

    /**
    * list of active modules
    * @return array
    */
    private function _getModules($access = null)
    {
        $result = [];
        $rows = $this->db('lite_modules')->toArray();

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
            $count = $this->db('lite_roles')->where('username', $_POST['username'])->count();
        } else {        // edit
            $count = $this->db('lite_roles')->where('username', $_POST['username'])->where('id', '<>', $id)->count();
        }
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * module JavaScript
    */
    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/users/js/admin/users.js');
        exit();
    }

    private function _addHeaderFiles()
    {
        // CSS
        $this->core->addCSS(url('assets/css/jquery-ui.css'));
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));

        // JS
        $this->core->addJS(url('assets/jscripts/jquery-ui.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'), 'footer');

        // MODULE SCRIPTS
        $this->core->addJS(url([ADMIN, 'users', 'javascript']), 'footer');
    }

    private function _addInfoUser() {
        // get users
        $rows = $this->db('pegawai')->toArray();

        if (count($rows)) {
          $this->assign['user'] = [];
          foreach($rows as $row) {
              $this->assign['user'][] = $row;
          }
        }
    }

    private function _addInfoRole() {
      $role = array('admin','manajemen','medis','paramedis','apoteker','rekammedis','kasir');
      if (count($role)) {
        $this->assign['role'] = [];
        foreach($role as $row) {
            $this->assign['role'][] = $row;
        }
      }
    }

    private function _addInfoCap() {
      $cap = $this->db()->pdo()->prepare("(SELECT kd_poli AS cap, nm_poli AS nm_cap FROM poliklinik) UNION (SELECT kd_bangsal AS cap, nm_bangsal AS nm_cap FROM bangsal)");
      //$cap = $this->db()->pdo()->prepare("SELECT kd_poli AS cap, nm_poli AS nm_cap FROM poliklinik");
      $cap->execute();
      $cap = $cap->fetchAll();

      if (count($cap)) {
        $this->assign['cap'] = [];
        foreach($cap as $row) {
            $this->assign['cap'][] = $row;
        }
      }
    }

}
