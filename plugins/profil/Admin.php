<?php

namespace Plugins\Profil;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Kelola' => 'manage',
            'Biodata' => 'biodata',
            'Ganti Password' => 'ganti_pass'
        ];
    }

    public function getManage()
    {
        $sub_modules = [
            ['name' => 'Biodata', 'url' => url([ADMIN, 'profil', 'biodata']), 'icon' => 'cubes', 'desc' => 'Biodata Pegawai'],
            ['name' => 'Ganti Password', 'url' => url([ADMIN, 'profil', 'ganti_pass']), 'icon' => 'cubes', 'desc' => 'Ganti Pasword'],
        ];
        $username = $this->core->getUserInfo('username', null, true);
        $cek_profil = $this->core->mysql('pegawai')->where('nik', $username)->oneArray();
        if(!$cek_profil) {
          $profil['nama'] = 'Admin Utama';
          $profil['nik'] = 'admin';
        } else {
          $profil['nama'] = $cek_profil['nama'];
          $profil['nik'] = $cek_profil['nik'];
        }
        $tanggal = getDayIndonesia(date('Y-m-d')) . ', ' . dateIndonesia(date('Y-m-d'));
        $fotoURL = url(MODULES . '/kepegawaian/img/default.png');
        if (!empty($profil['photo'])) {
            $fotoURL = WEBAPPS_URL . '/penggajian/' . $profil['photo'];
        }
        return $this->draw('manage.html', ['sub_modules' => $sub_modules, 'profil' => $profil, 'tanggal' => $tanggal, 'fotoURL' => $fotoURL]);
    }

    public function getBiodata()
    {
        $this->_addHeaderFiles();
        $username = $this->core->getUserInfo('username', null, true);

        $row = $this->core->mysql('pegawai')->where('nik', $username)->oneArray();
        $this->assign['form'] = $row;
        $this->assign['title'] = 'Edit Biodata';
        $this->assign['jk'] = ['Pria', 'Wanita'];
        $this->assign['departemen'] = $this->core->mysql('departemen')->toArray();
        $this->assign['bidang'] = $this->core->mysql('bidang')->toArray();
        $this->assign['stts_wp'] = $this->core->mysql('stts_wp')->toArray();
        $this->assign['pendidikan'] = $this->core->mysql('pendidikan')->toArray();
        $this->assign['jnj_jabatan'] = $this->core->mysql('jnj_jabatan')->toArray();

        $this->assign['fotoURL'] = url(WEBAPPS_PATH . '/penggajian/' . $row['photo']);

        return $this->draw('biodata.html', ['biodata' => $this->assign]);
    }

    public function postBiodataSave($id = null)
    {
        $errors = 0;

        if (!$id) {
            $location = url([ADMIN, 'profil', 'biodata']);
        } else {
            $location = url([ADMIN, 'profil', 'biodata', $id]);
        }

        if (checkEmptyFields(['nama'], $_POST)) {
            $this->notify('failure', 'Isian kosong');
            redirect($location, $_POST);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (($photo = isset_or($_FILES['photo']['tmp_name'], false)) || !$id) {
                $img = new \Systems\Lib\Image;

                if (empty($photo) && !$id) {
                    $photo = MODULES . '/profil/img/default.png';
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
                        $pegawai = $this->core->mysql('pegawai')->oneArray($id);
                    }

                    $_POST['photo'] = "pages/pegawai/photo/" . $pegawai['nik'] . "." . $img->getInfos('type');
                }
            }

            if (!$id) {    // new
                $query = $this->core->mysql('pegawai')->save($_POST);
            } else {        // edit
                $query = $this->core->mysql('pegawai')->where('id', $id)->save($_POST);
            }

            if ($query) {
                if (isset($img) && $img->getInfos('width')) {
                    if (isset($pegawai)) {
                        unlink(WEBAPPS_PATH . "/penggajian/" . $pegawai['photo']);
                    }

                    $img->save(WEBAPPS_PATH . "/penggajian/" . $_POST['photo']);
                }

                $this->notify('success', 'Simpan sukes');
            } else {
                $this->notify('failure', 'Simpan gagal');
            }

            redirect($location);
        }

        redirect($location, $_POST);
    }

    public function getGanti_Pass()
    {
        $this->_addHeaderFiles();
        $username = $this->core->getUserInfo('username', null, true);
        $this->assign['username'] = $username;
        $this->assign['title'] = 'Ganti Password';

        return $this->draw('ganti_pass.html', ['ganti_pass' => $this->assign]);
    }

    public function postGanti_Save($id = null)
    {
        $errors = 0;

        $row_user = $this->db('mlite_users')->where('id', $this->core->getUserInfo('id'))->oneArray();

        // location to redirect
        if (!$id) {
            $location = url([ADMIN, 'profil', 'ganti_pass']);
        } else {
            $location = url([ADMIN, 'profil', 'ganti_pass', $id]);
        }

        // check if required fields are empty
        if (checkEmptyFields(['pass_lama', 'pass_baru'], $_POST)) {
            $this->notify('failure', 'Isian kosong');
            redirect($location, $_POST);
        }

        // check if password is longer than 5 characters
        if ($_POST['pass_baru'] == $_POST['pass_lama']) {
            $errors++;
            $this->notify('failure', 'Kata kunci sama');
        }

        // CREATE / EDIT
        if (!$errors) {
            unset($_POST['save']);

            if ($row_user && password_verify(trim($_POST['pass_lama']), $row_user['password'])) {
                $password = password_hash($_POST['pass_baru'], PASSWORD_BCRYPT);
                $query = $this->db('mlite_users')->where('id', $this->core->getUserInfo('id'))->save(['password' => $password]);
            }

            if ($query) {
                $this->notify('success', 'Simpan sukses');
            } else {
                $this->notify('failure', 'Kata kunci lama salah');
            }

            redirect($location);
        }

        redirect($location, $_POST);
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES . '/profil/js/admin/app.js');
        exit();
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));

        // MODULE SCRIPTS
        $this->core->addJS(url([ADMIN, 'profil', 'javascript']), 'footer');
    }

}
