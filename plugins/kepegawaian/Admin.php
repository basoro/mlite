<?php

namespace Plugins\Kepegawaian;

use Systems\AdminModule;
use Systems\Lib\Fpdf\PDF_MC_Table;

class Admin extends AdminModule
{
    public function navigation()
    {
        return [
            'Kelola' => 'manage',
            'Data Pegawai' => 'index',
            'Tambah Baru' => 'add',
            //'Master Pegawai' => 'master',
        ];
    }

    public function getManage()
    {
      $sub_modules = [
        ['name' => 'Data Pegawai', 'url' => url([ADMIN, 'kepegawaian', 'index']), 'icon' => 'group', 'desc' => 'Data Pegawai'],
        ['name' => 'Add Pegawai', 'url' => url([ADMIN, 'kepegawaian', 'add']), 'icon' => 'group', 'desc' => 'Tambah Data Pegawai'],
        //['name' => 'Master Kepegawaian', 'url' => url([ADMIN, 'kepegawaian', 'master']), 'icon' => 'group', 'desc' => 'Master data Kepegawaian'],
      ];
      return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
    }

    public function getIndex($page = 1)
    {

        $this->_addHeaderFiles();

        $rows = $this->db('pegawai')->toArray();

        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                $row['editURL'] = url([ADMIN, 'kepegawaian', 'edit', $row['id']]);
                $row['viewURL'] = url([ADMIN, 'kepegawaian', 'view', $row['id']]);
                $this->assign['list'][] = $row;
            }
        }

        $this->assign['getStatus'] = isset($_GET['status']);
        $this->assign['printURL'] = url([ADMIN, 'kepegawaian', 'print']);

        return $this->draw('index.html', ['pegawai' => $this->assign]);

    }

    public function getAdd()
    {
        $this->_addHeaderFiles();
        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = [
              'nik' => '',
              'nama' => '',
              'jk' => '',
              'jbtn' => '-',
              'jnj_jabatan' => '',
              'kode_kelompok' => '',
              'kode_resiko' => '',
              'kode_emergency' => '',
              'departemen' => '',
              'bidang' => '',
              'stts_wp' => '',
              'stts_kerja' => '',
              'npwp' => '0',
              'pendidikan' => '',
              'gapok' => '0',
              'tmp_lahir' => '',
              'tgl_lahir' => '',
              'alamat' => '',
              'kota' => '',
              'mulai_kerja' => date('Y-m-d'),
              'ms_kerja' => '',
              'indexins' => '',
              'bpd' => '',
              'rekening' => '0',
              'stts_aktif' => '',
              'wajibmasuk' => '0',
              'pengurang' => '0',
              'indek' => '0',
              'mulai_kontrak' => date('Y-m-d'),
              'cuti_diambil' => '0',
              'dankes' => '0',
              'photo' => '',
              'no_ktp' => '0',
              'qrCode' => ''
            ];
        }

        $this->assign['title'] = 'Tambah Pegawai';
        $this->assign['jk'] = ['Pria','Wanita'];
        $this->assign['ms_kerja'] = ['<1','PT','FT>1'];
        $this->assign['stts_aktif'] = ['AKTIF','CUTI','KELUAR','TENAGA LUAR'];
        $this->assign['jnj_jabatan'] = $this->db('jnj_jabatan')->toArray();
        $this->assign['kelompok_jabatan'] = $this->db('kelompok_jabatan')->toArray();
        $this->assign['resiko_kerja'] = $this->db('resiko_kerja')->toArray();
        $this->assign['departemen'] = $this->db('departemen')->toArray();
        $this->assign['bidang'] = $this->db('bidang')->toArray();
        $this->assign['stts_wp'] = $this->db('stts_wp')->toArray();
        $this->assign['stts_kerja'] = $this->db('stts_kerja')->toArray();
        $this->assign['pendidikan'] = $this->db('pendidikan')->toArray();
        $this->assign['bank'] = $this->db('bank')->toArray();
        $this->assign['emergency_index'] = $this->db('emergency_index')->toArray();

        $this->assign['fotoURL'] = url(MODULES.'/kepegawaian/img/default.png');

        return $this->draw('form.html', ['pegawai' => $this->assign]);
    }

    public function getEdit($id)
    {
        $this->_addHeaderFiles();
        $row = $this->db('pegawai')->oneArray($id);
        if (!empty($row)) {
            $this->assign['form'] = $row;
            $this->assign['title'] = 'Edit Pegawai';

            $this->assign['jk'] = ['Pria','Wanita'];
            $this->assign['ms_kerja'] = ['<1','PT','FT>1'];
            $this->assign['stts_aktif'] = ['AKTIF','CUTI','KELUAR','TENAGA LUAR'];
            $this->assign['jnj_jabatan'] = $this->db('jnj_jabatan')->toArray();
            $this->assign['kelompok_jabatan'] = $this->db('kelompok_jabatan')->toArray();
            $this->assign['resiko_kerja'] = $this->db('resiko_kerja')->toArray();
            $this->assign['departemen'] = $this->db('departemen')->toArray();
            $this->assign['bidang'] = $this->db('bidang')->toArray();
            $this->assign['stts_wp'] = $this->db('stts_wp')->toArray();
            $this->assign['stts_kerja'] = $this->db('stts_kerja')->toArray();
            $this->assign['pendidikan'] = $this->db('pendidikan')->toArray();
            $this->assign['bank'] = $this->db('bank')->toArray();
            $this->assign['emergency_index'] = $this->db('emergency_index')->toArray();

            $this->assign['fotoURL'] = WEBAPPS_URL.'/penggajian/'.$row['photo'];

            return $this->draw('form.html', ['pegawai' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'kepegawaian', 'index']));
        }
    }

    public function getView($id)
    {
        $this->_addHeaderFiles();
        $row = $this->db('pegawai')->oneArray($id);

        if (!empty($row)) {
            $this->assign['pegawai'] = $row;
            $this->assign['petugas'] = $this->db('petugas')->where('nip',$row['nik'])->oneArray();
            $this->assign['stts_wp'] = $this->db('stts_wp')->where('stts',$row['stts_wp'])->oneArray();
            $this->assign['manageURL'] = url([ADMIN, 'kepegawaian', 'index']);

            $this->assign['fotoURL'] = url(MODULES.'/kepegawaian/img/default.png');
            if(!empty($row['photo'])) {
              $this->assign['fotoURL'] = WEBAPPS_URL.'/penggajian/'.$row['photo'];
            }

            return $this->draw('view.html', ['kepegawaian' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'kepegawaian', 'index']));
        }
    }

    public function postSave($id = null)
    {
        $errors = 0;

        if (!$id) {
            $location = url([ADMIN, 'kepegawaian', 'add']);
        } else {
            $location = url([ADMIN, 'kepegawaian', 'edit', $id]);
        }

        if (checkEmptyFields(['nik', 'nama'], $_POST)) {
            $this->notify('failure', 'Isian kosong');
            redirect($location, $_POST);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (($photo = isset_or($_FILES['photo']['tmp_name'], false)) || !$id) {
                $img = new \Systems\Lib\Image;

                if (empty($photo) && !$id) {
                    $photo = MODULES.'/kepegawaian/img/default.png';
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
                        $pegawai = $this->db('pegawai')->oneArray($id);
                    }

                    $_POST['photo'] = "pages/pegawai/photo/".$pegawai['nik'].".".$img->getInfos('type');
                }
            }

            if (!$id) {    // new
                $query = $this->db('pegawai')->save($_POST);
            } else {        // edit
                $query = $this->db('pegawai')->where('id', $id)->save($_POST);
            }

            if ($query) {
                if (isset($img) && $img->getInfos('width')) {
                    if (isset($pegawai)) {
                        unlink(WEBAPPS_PATH."/penggajian/".$pegawai['photo']);
                    }

                    $img->save(WEBAPPS_PATH."/penggajian/".$_POST['photo']);
                }

                $this->notify('success', 'Simpan sukes');
            } else {
                $this->notify('failure', 'Simpan gagal');
            }

            redirect($location);
        }

        redirect($location, $_POST);
    }

    public function getPrint()
    {
      $pasien = $this->db('pegawai')->toArray();
      $logo = $this->settings->get('settings.logo');

      $pdf = new PDF_MC_Table();
      $pdf->AddPage();
      $pdf->SetAutoPageBreak(true, 10);
      $pdf->SetTopMargin(10);
      $pdf->SetLeftMargin(10);
      $pdf->SetRightMargin(10);

      $pdf->Image('../'.$logo, 10, 8, '18', '18', 'png');
      $pdf->SetFont('Arial', '', 24);
      $pdf->Text(30, 16, $this->settings->get('settings.nama_instansi'));
      $pdf->SetFont('Arial', '', 10);
      $pdf->Text(30, 21, $this->settings->get('settings.alamat').' - '.$this->settings->get('settings.kota'));
      $pdf->Text(30, 25, $this->settings->get('settings.nomor_telepon').' - '.$this->settings->get('settings.email'));
      $pdf->Line(10, 30, 200, 30);
      $pdf->Line(10, 31, 200, 31);
      $pdf->Text(10, 40, 'DATA PEGAWAI');
      $pdf->Ln(34);
      $pdf->SetFont('Arial', '', 10);
      $pdf->SetWidths(array(50,70,25,25,20));
      $pdf->Row(array('Kode Pegawai','Nama Pegawai','Tempat Lahir', 'Tanggal Lahir', 'Status'));
      foreach ($pasien as $hasil) {
        $pdf->Row(array($hasil['nik'],$hasil['nama'],$hasil['tmp_lahir'],$hasil['tgl_lahir'],$hasil['stts_aktif']));
      }
      $pdf->Output('laporan_pegawai_'.date('Y-m-d').'.pdf','I');

    }

    public function getCSS()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/kepegawaian/css/admin/kepegawaian.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/kepegawaian/js/admin/kepegawaian.js');
        exit();
    }

    private function _addHeaderFiles()
    {
        // CSS
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));

        // JS
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'), 'footer');

        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));

        // MODULE SCRIPTS
        $this->core->addCSS(url([ADMIN, 'kepegawaian', 'css']));
        $this->core->addJS(url([ADMIN, 'kepegawaian', 'javascript']), 'footer');
    }

}
