<?php

namespace Plugins\Master;

use Systems\AdminModule;
use Systems\Lib\Fpdf\PDF_MC_Table;

class Admin extends AdminModule
{
    public function navigation()
    {
        return [
            'Dokter' => 'dokter',
            'Petugas' => 'petugas',
            'Poliklinik' => 'poliklinik',
            'Bangsal' => 'bangsal',
            'Data Barang' => 'databarang',
            'Perawatan Ralan' => 'jnsperawatan',
            'Perawatan Laboratorium' => 'jnsperawatanlab',
            'Perawatan Radiologi' => 'jnsperawatanrad',
        ];
    }

    /* Master Dokter Section */
    public function getDokter($page = 1)
    {
        $this->_addHeaderFiles();
        $perpage = '10';
        $phrase = '';
        if(isset($_GET['s']))
          $phrase = $_GET['s'];

        $status = '1';
        if(isset($_GET['status']))
          $status = $_GET['status'];

        // pagination
        $totalRecords = $this->db('dokter')
          ->select('kd_dokter')
          ->like('kd_dokter', '%'.$phrase.'%')
          ->like('nm_dokter', '%'.$phrase.'%')
          ->where('status', $status)
          ->toArray();
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'master', 'dokter', '%d']));
        $this->assign['pagination'] = $pagination->nav('pagination','5');
        $this->assign['totalRecords'] = $totalRecords;

        // list
        $offset = $pagination->offset();
        $rows = $this->db('dokter')
          ->like('kd_dokter', '%'.$phrase.'%')
          ->like('nm_dokter', '%'.$phrase.'%')
          ->where('status', $status)
          ->offset($offset)
          ->limit($perpage)
          ->toArray();

        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                $row['editURL'] = url([ADMIN, 'master', 'dokteredit', $row['kd_dokter']]);
                $row['delURL']  = url([ADMIN, 'master', 'dokterdelete', $row['kd_dokter']]);
                $row['restoreURL']  = url([ADMIN, 'master', 'dokterrestore', $row['kd_dokter']]);
                $row['viewURL'] = url([ADMIN, 'master', 'dokterview', $row['kd_dokter']]);
                $this->assign['list'][] = $row;
            }
        }

        $this->assign['getStatus'] = isset($_GET['status']);
        $this->assign['addURL'] = url([ADMIN, 'master', 'dokteradd']);
        $this->assign['printURL'] = url([ADMIN, 'master', 'dokterprint']);

        return $this->draw('dokter.manage.html', ['dokter' => $this->assign]);

    }

    public function getDokterAdd()
    {
        $this->_addHeaderFiles();
        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = [
              'kd_dokter' => '',
              'nm_dokter' => '',
              'jk' => '',
              'tmp_lahir' => '',
              'tgl_lahir' => '',
              'gol_drh' => '',
              'agama' => '',
              'almt_tgl' => '',
              'no_telp' => '',
              'stts_nikah' => '',
              'kd_sps' => '',
              'alumni' => '',
              'no_ijn_praktek' => '',
              'status' => ''
            ];
        }

        $this->assign['title'] = 'Tambah Dokter';
        $this->assign['kd_dokter'] = $this->db('pegawai')->toArray();
        $this->assign['jk'] = $this->core->getEnum('dokter', 'jk');
        $this->assign['gol_drh'] = $this->core->getEnum('dokter', 'gol_drh');
        $this->assign['agama'] = array('ISLAM', 'KRISTEN', 'PROTESTAN', 'HINDU', 'BUDHA', 'KONGHUCU', 'KEPERCAYAAN');
        $this->assign['stts_nikah'] = $this->core->getEnum('dokter', 'stts_nikah');
        $this->assign['kd_sps'] = $this->db('spesialis')->toArray();

        return $this->draw('dokter.form.html', ['dokter' => $this->assign]);
    }

    public function getDokterEdit($id)
    {
        $this->_addHeaderFiles();
        $row = $this->db('dokter')->where('kd_dokter', $id)->oneArray();
        if (!empty($row)) {
            $this->assign['form'] = $row;
            $this->assign['title'] = 'Edit Dokter';
            $this->assign['kd_dokter'] = $this->db('pegawai')->toArray();
            $this->assign['jk'] = $this->core->getEnum('dokter', 'jk');
            $this->assign['gol_drh'] = $this->core->getEnum('dokter', 'gol_drh');
            $this->assign['agama'] = array('ISLAM', 'KRISTEN', 'PROTESTAN', 'HINDU', 'BUDHA', 'KONGHUCU', 'KEPERCAYAAN');
            $this->assign['stts_nikah'] = $this->core->getEnum('dokter', 'stts_nikah');
            $this->assign['kd_sps'] = $this->db('spesialis')->toArray();

            return $this->draw('dokter.form.html', ['dokter' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'master', 'dokter']));
        }
    }

    public function getDokterDelete($id)
    {
        if ($this->core->db('dokter')->where('kd_dokter', $id)->update('status', '0')) {
            $this->notify('success', 'Hapus sukses');
        } else {
            $this->notify('failure', 'Hapus gagal');
        }
        redirect(url([ADMIN, 'master', 'dokter']));
    }

    public function getDokterRestore($id)
    {
        if ($this->core->db('dokter')->where('kd_dokter', $id)->update('status', '1')) {
            $this->notify('success', 'Restore sukses');
        } else {
            $this->notify('failure', 'Restore gagal');
        }
        redirect(url([ADMIN, 'master', 'dokter']));
    }

    public function postDokterSave($id = null)
    {
        $errors = 0;

        if (!$id) {
            $location = url([ADMIN, 'master', 'dokteradd']);
        } else {
            $location = url([ADMIN, 'master', 'dokteredit', $id]);
        }

        if (checkEmptyFields(['kd_dokter', 'nm_dokter'], $_POST)) {
            $this->notify('failure', 'Isian kosong');
            redirect($location, $_POST);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (!$id) {    // new
                $_POST['status'] = '1';
                $query = $this->db('dokter')->save($_POST);
            } else {        // edit
                $query = $this->db('dokter')->where('kd_dokter', $id)->save($_POST);
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
    /* End Master Dokter Section */

    /* Master Petugas Section */
    public function getPetugas($page = 1)
    {
        $this->_addHeaderFiles();
        $perpage = '10';
        $phrase = '';
        if(isset($_GET['s']))
          $phrase = $_GET['s'];

        $status = '1';
        if(isset($_GET['status']))
          $status = $_GET['status'];

        // pagination
        $totalRecords = $this->db('petugas')
            ->where('status', $status)
            ->like('nip', '%'.$phrase.'%')
            ->like('nama', '%'.$phrase.'%')
            ->toArray();
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'master', 'petugas', '%d']));
        $this->assign['pagination'] = $pagination->nav('pagination','5');
        $this->assign['totalRecords'] = $totalRecords;

        // list
        $offset = $pagination->offset();
        $rows = $this->db('petugas')
            ->where('status', $status)
            ->like('nip', '%'.$phrase.'%')
            ->like('nama', '%'.$phrase.'%')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();

        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                $row['editURL'] = url([ADMIN, 'master', 'petugasedit', $row['nip']]);
                $row['delURL']  = url([ADMIN, 'master', 'petugasdelete', $row['nip']]);
                $row['restoreURL']  = url([ADMIN, 'master', 'petugasrestore', $row['nip']]);
                $row['viewURL'] = url([ADMIN, 'master', 'petugasview', $row['nip']]);
                $this->assign['list'][] = $row;
            }
        }

        $this->assign['getStatus'] = isset($_GET['status']);
        $this->assign['addURL'] = url([ADMIN, 'master', 'petugasadd']);
        $this->assign['printURL'] = url([ADMIN, 'master', 'petugasprint']);

        return $this->draw('petugas.manage.html', ['petugas' => $this->assign]);

    }

    public function getPetugasAdd()
    {
        $this->_addHeaderFiles();
        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = [
              'nip' => '',
              'nama' => '',
              'jk' => '',
              'tmp_lahir' => '',
              'tgl_lahir' => '',
              'gol_darah' => '',
              'agama' => '',
              'alamat' => '',
              'no_telp' => '',
              'stts_nikah' => '',
              'kd_jbtn' => '',
              'status' => ''
            ];
        }

        $this->assign['title'] = 'Tambah Petugas';
        $this->assign['nip'] = $this->db('pegawai')->toArray();
        $this->assign['jk'] = $this->core->getEnum('petugas', 'jk');
        $this->assign['gol_darah'] = $this->core->getEnum('petugas', 'gol_darah');
        $this->assign['agama'] = array('ISLAM', 'KRISTEN', 'PROTESTAN', 'HINDU', 'BUDHA', 'KONGHUCU', 'KEPERCAYAAN');
        $this->assign['stts_nikah'] = $this->core->getEnum('petugas', 'stts_nikah');
        $this->assign['kd_jbtn'] = $this->db('jabatan')->toArray();

        return $this->draw('petugas.form.html', ['petugas' => $this->assign]);
    }

    public function getPetugasEdit($id)
    {
        $this->_addHeaderFiles();
        $row = $this->db('petugas')->where('nip', $id)->oneArray();
        if (!empty($row)) {
            $this->assign['form'] = $row;
            $this->assign['title'] = 'Edit Petugas';
            $this->assign['nip'] = $this->db('pegawai')->toArray();
            $this->assign['jk'] = $this->core->getEnum('petugas', 'jk');
            $this->assign['gol_darah'] = $this->core->getEnum('petugas', 'gol_darah');
            $this->assign['agama'] = array('ISLAM', 'KRISTEN', 'PROTESTAN', 'HINDU', 'BUDHA', 'KONGHUCU', 'KEPERCAYAAN');
            $this->assign['stts_nikah'] = $this->core->getEnum('petugas', 'stts_nikah');
            $this->assign['kd_jbtn'] = $this->db('jabatan')->toArray();

            return $this->draw('petugas.form.html', ['petugas' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'master', 'petugas']));
        }
    }

    public function getPetugasDelete($id)
    {
        if ($this->core->db('petugas')->where('nip', $id)->update('status', '0')) {
            $this->notify('success', 'Hapus sukses');
        } else {
            $this->notify('failure', 'Hapus gagal');
        }
        redirect(url([ADMIN, 'master', 'petugas']));
    }

    public function getPetugasRestore($id)
    {
        if ($this->core->db('petugas')->where('nip', $id)->update('status', '1')) {
            $this->notify('success', 'Restore sukses');
        } else {
            $this->notify('failure', 'Restore gagal');
        }
        redirect(url([ADMIN, 'master', 'petugas']));
    }

    public function postPetugasSave($id = null)
    {
        $errors = 0;

        if (!$id) {
            $location = url([ADMIN, 'master', 'petugasadd']);
        } else {
            $location = url([ADMIN, 'master', 'petugasedit', $id]);
        }

        //$get_pegawai = $this->db('pegawai')->select('nama')->where('nik', $_POST['nip'])->oneArray();
        //$_POST['nama'] = $get_pegawai['nama'];

        if (checkEmptyFields(['nip', 'nama'], $_POST)) {
            $this->notify('failure', 'Isian kosong');
            redirect($location, $_POST);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (!$id) {    // new
                $_POST['status'] = '1';
                $query = $this->db('petugas')->save($_POST);
            } else {        // edit
                $query = $this->db('petugas')->where('nip', $id)->save($_POST);
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
    /* End Master Petugas Section */

    /* Master Poliklinik Section */
    public function getPoliklinik($page = 1)
    {
        $this->_addHeaderFiles();
        $perpage = '10';

        $phrase = '';
        if(isset($_GET['s']))
          $phrase = $_GET['s'];

        $status = '1';
        if(isset($_GET['status']))
          $status = $_GET['status'];

        // pagination
        $totalRecords = $this->db('poliklinik')
            ->select('kd_poli')
            ->where('status', $status)
            ->like('kd_poli', '%'.$phrase.'%')
            ->like('nm_poli', '%'.$phrase.'%')
            ->toArray();
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'master', 'poliklinik', '%d']));
        $this->assign['pagination'] = $pagination->nav('pagination','5');
        $this->assign['totalRecords'] = $totalRecords;

        // list
        $offset = $pagination->offset();
        $rows = $this->db('poliklinik')
            ->where('status', $status)
            ->like('kd_poli', '%'.$phrase.'%')
            ->like('nm_poli', '%'.$phrase.'%')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();

        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                $row['editURL'] = url([ADMIN, 'master', 'poliklinikedit', $row['kd_poli']]);
                $row['delURL']  = url([ADMIN, 'master', 'poliklinikdelete', $row['kd_poli']]);
                $row['restoreURL']  = url([ADMIN, 'master', 'poliklinikrestore', $row['kd_poli']]);
                $row['viewURL'] = url([ADMIN, 'master', 'poliklinikview', $row['kd_poli']]);
                $this->assign['list'][] = $row;
            }
        }

        $this->assign['getStatus'] = isset($_GET['status']);
        $this->assign['addURL'] = url([ADMIN, 'master', 'poliklinikadd']);
        $this->assign['printURL'] = url([ADMIN, 'master', 'poliklinikprint']);

        return $this->draw('poliklinik.manage.html', ['poliklinik' => $this->assign]);

    }

    public function getPoliklinikAdd()
    {
        $this->_addHeaderFiles();
        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = [
              'kd_poli' => '',
              'nm_poli' => '',
              'registrasi' => '',
              'registrasilama' => '',
              'status' => ''
            ];
        }

        $this->assign['title'] = 'Tambah Poliklinik';

        return $this->draw('poliklinik.form.html', ['poliklinik' => $this->assign]);
    }

    public function getPoliklinikEdit($id)
    {
        $this->_addHeaderFiles();
        $row = $this->db('poliklinik')->where('kd_poli', $id)->oneArray();
        if (!empty($row)) {
            $this->assign['form'] = $row;
            $this->assign['title'] = 'Edit Poliklinik';

            return $this->draw('poliklinik.form.html', ['poliklinik' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'master', 'poliklinik']));
        }
    }

    public function getPoliklinikDelete($id)
    {
        if ($this->core->db('poliklinik')->where('kd_poli', $id)->update('status', '0')) {
            $this->notify('success', 'Hapus sukses');
        } else {
            $this->notify('failure', 'Hapus gagal');
        }
        redirect(url([ADMIN, 'master', 'poliklinik']));
    }

    public function getPoliklinikRestore($id)
    {
        if ($this->core->db('poliklinik')->where('kd_poli', $id)->update('status', '1')) {
            $this->notify('success', 'Restore sukses');
        } else {
            $this->notify('failure', 'Restore gagal');
        }
        redirect(url([ADMIN, 'master', 'poliklinik']));
    }

    public function postPoliklinikSave($id = null)
    {
        $errors = 0;

        if (!$id) {
            $location = url([ADMIN, 'master', 'poliklinikadd']);
        } else {
            $location = url([ADMIN, 'master', 'poliklinikedit', $id]);
        }

        if (checkEmptyFields(['kd_poli', 'nm_poli'], $_POST)) {
            $this->notify('failure', 'Isian kosong');
            redirect($location, $_POST);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (!$id) {    // new
                $_POST['status'] = '1';
                $query = $this->db('poliklinik')->save($_POST);
            } else {        // edit
                $query = $this->db('poliklinik')->where('kd_poli', $id)->save($_POST);
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

    public function getPoliklinikPrint()
    {
      $pasien = $this->db('poliklinik')->toArray();
      $logo = 'data:image/png;base64,' . base64_encode($this->core->getSettings('logo'));

      $pdf = new PDF_MC_Table();
      $pdf->AddPage();
      $pdf->SetAutoPageBreak(true, 10);
      $pdf->SetTopMargin(10);
      $pdf->SetLeftMargin(10);
      $pdf->SetRightMargin(10);

      $pdf->Image($logo, 10, 8, '18', '18', 'png');
      $pdf->SetFont('Arial', '', 24);
      $pdf->Text(30, 16, $this->core->getSettings('nama_instansi'));
      $pdf->SetFont('Arial', '', 10);
      $pdf->Text(30, 21, $this->core->getSettings('alamat_instansi').' - '.$this->core->getSettings('kabupaten'));
      $pdf->Text(30, 25, $this->core->getSettings('kontak').' - '.$this->core->getSettings('email'));
      $pdf->Line(10, 30, 200, 30);
      $pdf->Line(10, 31, 200, 31);
      $pdf->Text(10, 40, 'DATA POLIKLINIK');
      $pdf->Ln(34);
      $pdf->SetFont('Arial', '', 10);
      $pdf->SetWidths(array(20,80,25,25,40));
      $pdf->Row(array('Kode Poli','Nama Poli','Daftar Baru', 'Daftar Lama', 'Status'));
      foreach ($pasien as $hasil) {
        $status = 'Aktif';
        if($hasil['status'] == '0') {
          $status = 'Tidak Aktif';
        }
        $pdf->Row(array($hasil['kd_poli'],$hasil['nm_poli'],$hasil['registrasi'],$hasil['registrasilama'],$status));
      }
      $pdf->Output('laporan_pasien_'.date('Y-m-d').'.pdf','I');

    }
    /* End Master Poliklinik Section */

    /* Master Bangsal Section */
    public function getBangsal($page = 1)
    {
        $this->_addHeaderFiles();
        $perpage = '10';

        $phrase = '';
        if(isset($_GET['s']))
          $phrase = $_GET['s'];

        $status = '1';
        if(isset($_GET['status']))
          $status = $_GET['status'];

        // pagination
        $totalRecords = $this->db('bangsal')
            ->select('kd_bangsal')
            ->where('status', $status)
            ->like('kd_bangsal', '%'.$phrase.'%')
            ->like('nm_bangsal', '%'.$phrase.'%')
            ->toArray();
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'master', 'bangsal', '%d']));
        $this->assign['pagination'] = $pagination->nav('pagination','5');
        $this->assign['totalRecords'] = $totalRecords;

        // list
        $offset = $pagination->offset();
        $rows = $this->db('bangsal')
            ->where('status', $status)
            ->like('kd_bangsal', '%'.$phrase.'%')
            ->like('nm_bangsal', '%'.$phrase.'%')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();

        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                $row['editURL'] = url([ADMIN, 'master', 'bangsaledit', $row['kd_bangsal']]);
                $row['delURL']  = url([ADMIN, 'master', 'bangsaldelete', $row['kd_bangsal']]);
                $row['restoreURL']  = url([ADMIN, 'master', 'bangsalrestore', $row['kd_bangsal']]);
                $row['viewURL'] = url([ADMIN, 'master', 'bangsalview', $row['kd_bangsal']]);
                $this->assign['list'][] = $row;
            }
        }

        $this->assign['getStatus'] = isset($_GET['status']);
        $this->assign['addURL'] = url([ADMIN, 'master', 'bangsaladd']);
        $this->assign['printURL'] = url([ADMIN, 'master', 'bangsalprint']);

        return $this->draw('bangsal.manage.html', ['bangsal' => $this->assign]);

    }

    public function getBangsalAdd()
    {
        $this->_addHeaderFiles();
        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = [
              'kd_bangsal' => '',
              'nm_bangsal' => '',
              'status' => ''
            ];
        }

        $this->assign['title'] = 'Tambah Bangsal';

        return $this->draw('bangsal.form.html', ['bangsal' => $this->assign]);
    }

    public function getBangsalEdit($id)
    {
        $this->_addHeaderFiles();
        $row = $this->db('bangsal')->where('kd_bangsal', $id)->oneArray();
        if (!empty($row)) {
            $this->assign['form'] = $row;
            $this->assign['title'] = 'Edit Bangsal';

            return $this->draw('bangsal.form.html', ['bangsal' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'master', 'bangsal']));
        }
    }

    public function getBangsalDelete($id)
    {
        if ($this->core->db('bangsal')->where('kd_bangsal', $id)->update('status', '0')) {
            $this->notify('success', 'Hapus sukses');
        } else {
            $this->notify('failure', 'Hapus gagal');
        }
        redirect(url([ADMIN, 'master', 'bangsal']));
    }

    public function getBangsalRestore($id)
    {
        if ($this->core->db('bangsal')->where('kd_bangsal', $id)->update('status', '1')) {
            $this->notify('success', 'Restore sukses');
        } else {
            $this->notify('failure', 'Restore gagal');
        }
        redirect(url([ADMIN, 'master', 'bangsal']));
    }

    public function postBangsalSave($id = null)
    {
        $errors = 0;

        if (!$id) {
            $location = url([ADMIN, 'master', 'bangsaladd']);
        } else {
            $location = url([ADMIN, 'master', 'bangsaledit', $id]);
        }

        if (checkEmptyFields(['kd_bangsal', 'nm_bangsal'], $_POST)) {
            $this->notify('failure', 'Isian kosong');
            redirect($location, $_POST);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (!$id) {    // new
                $_POST['status'] = '1';
                $query = $this->db('bangsal')->save($_POST);
            } else {        // edit
                $query = $this->db('bangsal')->where('kd_bangsal', $id)->save($_POST);
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

    public function getBangsalPrint()
    {
      $pasien = $this->db('bangsal')->toArray();
      $logo = 'data:image/png;base64,' . base64_encode($this->core->getSettings('logo'));

      $pdf = new PDF_MC_Table();
      $pdf->AddPage();
      $pdf->SetAutoPageBreak(true, 10);
      $pdf->SetTopMargin(10);
      $pdf->SetLeftMargin(10);
      $pdf->SetRightMargin(10);

      $pdf->Image($logo, 10, 8, '18', '18', 'png');
      $pdf->SetFont('Arial', '', 24);
      $pdf->Text(30, 16, $this->core->getSettings('nama_instansi'));
      $pdf->SetFont('Arial', '', 10);
      $pdf->Text(30, 21, $this->core->getSettings('alamat_instansi').' - '.$this->core->getSettings('kabupaten'));
      $pdf->Text(30, 25, $this->core->getSettings('kontak').' - '.$this->core->getSettings('email'));
      $pdf->Line(10, 30, 200, 30);
      $pdf->Line(10, 31, 200, 31);
      $pdf->Text(10, 40, 'DATA Bangsal');
      $pdf->Ln(34);
      $pdf->SetFont('Arial', '', 10);
      $pdf->SetWidths(array(30,120,40));
      $pdf->Row(array('Kode Bangsal','Nama Bangsal','Status'));
      foreach ($pasien as $hasil) {
        $status = 'Aktif';
        if($hasil['status'] == '0') {
          $status = 'Tidak Aktif';
        }
        $pdf->Row(array($hasil['kd_bangsal'],$hasil['nm_bangsal'],$status));
      }
      $pdf->Output('laporan_bangsal_'.date('Y-m-d').'.pdf','I');

    }
    /* End Master Bangsal Section */

    /* Master Databarang Section */
    public function getDatabarang($page = 1)
    {
        $this->_addHeaderFiles();
        $perpage = '10';
        $phrase = '';
        if(isset($_GET['s']))
          $phrase = $_GET['s'];

        $status = '1';
        if(isset($_GET['status']))
          $status = $_GET['status'];

        // pagination
        $totalRecords = $this->db('databarang')
            ->select('kode_brng')
            ->where('status', $status)
            ->like('kode_brng', '%'.$phrase.'%')
            ->like('nama_brng', '%'.$phrase.'%')
            ->toArray();
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'master', 'databarang', '%d']));
        $this->assign['pagination'] = $pagination->nav('pagination','5');
        $this->assign['totalRecords'] = $totalRecords;

        // list
        $offset = $pagination->offset();
        $rows = $this->db('databarang')
            ->where('status', $status)
            ->like('kode_brng', '%'.$phrase.'%')
            ->like('nama_brng', '%'.$phrase.'%')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();

        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                $row['editURL'] = url([ADMIN, 'master', 'databarangedit', $row['kode_brng']]);
                $row['delURL']  = url([ADMIN, 'master', 'databarangdelete', $row['kode_brng']]);
                $row['restoreURL']  = url([ADMIN, 'master', 'databarangrestore', $row['kode_brng']]);
                $row['viewURL'] = url([ADMIN, 'master', 'databarangview', $row['kode_brng']]);
                $this->assign['list'][] = $row;
            }
        }

        $this->assign['title'] = 'Kelola Databarang';
        $this->assign['getStatus'] = isset($_GET['status']);
        $this->assign['addURL'] = url([ADMIN, 'master', 'databarangadd']);
        $this->assign['printURL'] = url([ADMIN, 'master', 'databarangprint']);

        return $this->draw('databarang.manage.html', ['databarang' => $this->assign]);

    }

    public function getDatabarangAdd()
    {
        $this->_addHeaderFiles();
        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = [
              'kode_brng' => '',
              'nama_brng' => '',
              'kode_satbesar' => '',
              'kode_sat' => '',
              'letak_barang' => '',
              'dasar' => '',
              'h_beli' => '',
              'ralan' => '',
              'kelas1' => '',
              'kelas2' => '',
              'kelas3' => '',
              'utama' => '',
              'vip' => '',
              'vvip' => '',
              'beliluar' => '',
              'jualbebas' => '',
              'karyawan' => '',
              'stokminimal' => '',
              'kdjns' => '',
              'isi' => '',
              'kapasitas' => '',
              'expire' => '',
              'status' => '',
              'kode_industri' => '',
              'kode_kategori' => '',
              'kode_golongan' => ''
            ];
        }

        $this->assign['title'] = 'Tambah Databarang';
        $this->assign['status'] = $this->core->getEnum('databarang', 'status');
        $this->assign['kdjns'] = $this->db('jenis')->toArray();
        $this->assign['kode_sat'] = $this->db('kodesatuan')->toArray();
        $this->assign['kode_industri'] = $this->db('industrifarmasi')->toArray();
        $this->assign['kode_kategori'] = $this->db('kategori_barang')->toArray();
        $this->assign['kode_golongan'] = $this->db('golongan_barang')->toArray();

        return $this->draw('databarang.form.html', ['databarang' => $this->assign]);
    }

    public function getDatabarangEdit($id)
    {
        $this->_addHeaderFiles();
        $row = $this->db('databarang')->where('kode_brng', $id)->oneArray();
        if (!empty($row)) {
            $this->assign['form'] = $row;
            $this->assign['title'] = 'Edit Databarang';
            $this->assign['status'] = $this->core->getEnum('databarang', 'status');
            $this->assign['kdjns'] = $this->db('jenis')->toArray();
            $this->assign['kode_sat'] = $this->db('kodesatuan')->toArray();
            $this->assign['kode_industri'] = $this->db('industrifarmasi')->toArray();
            $this->assign['kode_kategori'] = $this->db('kategori_barang')->toArray();
            $this->assign['kode_golongan'] = $this->db('golongan_barang')->toArray();

            return $this->draw('databarang.form.html', ['databarang' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'master', 'databarang']));
        }
    }

    public function getDatabarangDelete($id)
    {
        if ($this->core->db('databarang')->where('kode_brng', $id)->update('status', '0')) {
            $this->notify('success', 'Hapus sukses');
        } else {
            $this->notify('failure', 'Hapus gagal');
        }
        redirect(url([ADMIN, 'master', 'databarang']));
    }

    public function getDatabarangRestore($id)
    {
        if ($this->core->db('databarang')->where('kode_brng', $id)->update('status', '1')) {
            $this->notify('success', 'Restore sukses');
        } else {
            $this->notify('failure', 'Restore gagal');
        }
        redirect(url([ADMIN, 'master', 'databarang']));
    }

    public function postDatabarangSave($id = null)
    {
        $errors = 0;

        if (!$id) {
            $location = url([ADMIN, 'master', 'databarangadd']);
        } else {
            $location = url([ADMIN, 'master', 'databarangedit', $id]);
        }

        if (checkEmptyFields(['kode_brng', 'nama_brng'], $_POST)) {
            $this->notify('failure', 'Isian kosong');
            redirect($location, $_POST);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (!$id) {    // new
                $_POST['status'] = '1';
                $query = $this->db('databarang')->save($_POST);
            } else {        // edit
                $query = $this->db('databarang')->where('kode_brng', $id)->save($_POST);
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

    public function getDatabarangPrint()
    {
      $pasien = $this->db('databarang')->toArray();
      $logo = 'data:image/png;base64,' . base64_encode($this->core->getSettings('logo'));

      $pdf = new PDF_MC_Table('L','mm','Legal');
      $pdf->AddPage();
      $pdf->SetAutoPageBreak(true, 10);
      $pdf->SetTopMargin(10);
      $pdf->SetLeftMargin(10);
      $pdf->SetRightMargin(10);

      $pdf->Image($logo, 10, 8, '18', '18', 'png');
      $pdf->SetFont('Arial', '', 24);
      $pdf->Text(30, 16, $this->core->getSettings('nama_instansi'));
      $pdf->SetFont('Arial', '', 10);
      $pdf->Text(30, 21, $this->core->getSettings('alamat_instansi').' - '.$this->core->getSettings('kabupaten'));
      $pdf->Text(30, 25, $this->core->getSettings('kontak').' - '.$this->core->getSettings('email'));
      $pdf->Line(10, 30, 345, 30);
      $pdf->Line(10, 31, 345, 31);
      $pdf->Text(10, 40, 'DATA DATABARANG');
      $pdf->Ln(34);
      $pdf->SetFont('Arial', '', 10);
      $pdf->SetWidths(array(25,50,20,20,20,20,20,20,20,20,20,20,20,20,20,20));
      $pdf->Row(array('Kode Barang', 'Nama Barang', 'H. Dasar', 'H. Beli', 'Ralan', 'Kelas 1', 'Kelas 2', 'Kelas 3', 'Utama', 'VIP', 'VVIP', 'Beli Luar', 'Jual Bebas', 'Karyawan', 'Status'));

      foreach ($pasien as $hasil) {
        $status = 'Aktif';
        if($hasil['status'] == '0') {
          $status = 'Tidak Aktif';
        }
        $pdf->Row(array(
          $hasil['kode_brng'],
          $hasil['nama_brng'],
          number_format($hasil['dasar'],0,",","."),
          number_format($hasil['h_beli'],0,",","."),
          number_format($hasil['ralan'],0,",","."),
          number_format($hasil['kelas1'],0,",","."),
          number_format($hasil['kelas2'],0,",","."),
          number_format($hasil['kelas3'],0,",","."),
          number_format($hasil['utama'],0,",","."),
          number_format($hasil['vip'],0,",","."),
          number_format($hasil['vvip'],0,",","."),
          number_format($hasil['beliluar'],0,",","."),
          number_format($hasil['jualbebas'],0,",","."),
          number_format($hasil['karyawan'],0,",","."),
          $status
        ));
      }
      $pdf->Output('laporan_pasien_'.date('Y-m-d').'.pdf','I');

    }

    /* End Master Databarang Section */

    /* Master Jns_Perawatan Section */
    public function getJnsPerawatan($page = 1)
    {
        $this->_addHeaderFiles();
        $perpage = '10';
        $phrase = '';
        if(isset($_GET['s']))
          $phrase = $_GET['s'];

        $status = '1';
        if(isset($_GET['status']))
          $status = $_GET['status'];

        // pagination
        $totalRecords = $this->db('jns_perawatan')
            ->select('kd_jenis_prw')
            ->where('status', $status)
            ->like('kd_jenis_prw', '%'.$phrase.'%')
            ->like('nm_perawatan', '%'.$phrase.'%')
            ->toArray();
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'master', 'jnsperawatan', '%d']));
        $this->assign['pagination'] = $pagination->nav('pagination','5');
        $this->assign['totalRecords'] = $totalRecords;

        // list
        $offset = $pagination->offset();
        $rows = $this->db('jns_perawatan')
            ->where('status', $status)
            ->like('kd_jenis_prw', '%'.$phrase.'%')
            ->like('nm_perawatan', '%'.$phrase.'%')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();

        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                $row['editURL'] = url([ADMIN, 'master', 'jnsperawatanedit', $row['kd_jenis_prw']]);
                $row['delURL']  = url([ADMIN, 'master', 'jnsperawatandelete', $row['kd_jenis_prw']]);
                $row['restoreURL']  = url([ADMIN, 'master', 'jnsperawatanrestore', $row['kd_jenis_prw']]);
                $row['viewURL'] = url([ADMIN, 'master', 'jnsperawatanview', $row['kd_jenis_prw']]);
                $this->assign['list'][] = $row;
            }
        }

        $this->assign['title'] = 'Kelola Jenis Perawatan';
        $this->assign['getStatus'] = isset($_GET['status']);
        $this->assign['addURL'] = url([ADMIN, 'master', 'jnsperawatanadd']);
        $this->assign['printURL'] = url([ADMIN, 'master', 'jnsperawatanprint']);

        return $this->draw('jnsperawatan.manage.html', ['jnsperawatan' => $this->assign]);

    }

    public function getJnsPerawatanAdd()
    {
        $this->_addHeaderFiles();
        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = [
              'kd_jenis_prw' => '',
              'nm_perawatan' => '',
              'kd_kategori' => '',
              'material' => '',
              'bhp' => '',
              'tarif_tindakandr' => '',
              'tarif_tindakanpr' => '',
              'kso' => '',
              'menejemen' => '',
              'total_byrdr' => '',
              'total_byrpr' => '',
              'total_byrdrpr' => '',
              'kd_pj' => '',
              'kd_poli' => '',
              'status' => ''
            ];
        }

        $this->assign['title'] = 'Tambah Jenis Perawatan';
        $this->assign['status'] = $this->core->getEnum('jns_perawatan', 'status');
        $this->assign['kd_kategori'] = $this->db('kategori_perawatan')->toArray();
        $this->assign['kd_pj'] = $this->db('penjab')->toArray();
        $this->assign['kd_poli'] = $this->db('poliklinik')->toArray();

        return $this->draw('jnsperawatan.form.html', ['jnsperawatan' => $this->assign]);
    }

    public function getJnsPerawatanEdit($id)
    {
        $this->_addHeaderFiles();
        $row = $this->db('jns_perawatan')->where('kd_jenis_prw', $id)->oneArray();
        if (!empty($row)) {
            $this->assign['form'] = $row;
            $this->assign['title'] = 'Edit Jenis Perawatan';
            $this->assign['status'] = $this->core->getEnum('jns_perawatan', 'status');
            $this->assign['kd_kategori'] = $this->db('kategori_perawatan')->toArray();
            $this->assign['kd_pj'] = $this->db('penjab')->toArray();
            $this->assign['kd_poli'] = $this->db('poliklinik')->toArray();

            return $this->draw('jnsperawatan.form.html', ['jnsperawatan' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'master', 'jnsperawatan']));
        }
    }

    public function getJnsPerawatanDelete($id)
    {
        if ($this->core->db('jns_perawatan')->where('kd_jenis_prw', $id)->update('status', '0')) {
            $this->notify('success', 'Hapus sukses');
        } else {
            $this->notify('failure', 'Hapus gagal');
        }
        redirect(url([ADMIN, 'master', 'jnsperawatan']));
    }

    public function getJnsPerawatanRestore($id)
    {
        if ($this->core->db('jns_perawatan')->where('kd_jenis_prw', $id)->update('status', '1')) {
            $this->notify('success', 'Restore sukses');
        } else {
            $this->notify('failure', 'Restore gagal');
        }
        redirect(url([ADMIN, 'master', 'jnsperawatan']));
    }

    public function postJnsPerawatanSave($id = null)
    {
        $errors = 0;

        if (!$id) {
            $location = url([ADMIN, 'master', 'jnsperawatanadd']);
        } else {
            $location = url([ADMIN, 'master', 'jnsperawatanedit', $id]);
        }

        if (checkEmptyFields(['kd_jenis_prw', 'nm_perawatan'], $_POST)) {
            $this->notify('failure', 'Isian masih ada yang kosong');
            redirect($location, $_POST);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (!$id) {    // new
                $_POST['status'] = '1';
                $query = $this->db('jns_perawatan')->save($_POST);
            } else {        // edit
                $query = $this->db('jns_perawatan')->where('kd_jenis_prw', $id)->save($_POST);
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

    public function getJnsPerawatanPrint()
    {
      $pasien = $this->db('jns_perawatan')->toArray();
      $logo = 'data:image/png;base64,' . base64_encode($this->core->getSettings('logo'));

      $pdf = new PDF_MC_Table('L','mm','Legal');
      $pdf->AddPage();
      $pdf->SetAutoPageBreak(true, 10);
      $pdf->SetTopMargin(10);
      $pdf->SetLeftMargin(10);
      $pdf->SetRightMargin(10);

      $pdf->Image($logo, 10, 8, '18', '18', 'png');
      $pdf->SetFont('Arial', '', 24);
      $pdf->Text(30, 16, $this->core->getSettings('nama_instansi'));
      $pdf->SetFont('Arial', '', 10);
      $pdf->Text(30, 21, $this->core->getSettings('alamat_instansi').' - '.$this->core->getSettings('kabupaten'));
      $pdf->Text(30, 25, $this->core->getSettings('kontak').' - '.$this->core->getSettings('email'));
      $pdf->Line(10, 30, 345, 30);
      $pdf->Line(10, 31, 345, 31);
      $pdf->Text(10, 40, 'DATA JENIS PERAWATAN RAWAT JALAN');
      $pdf->Ln(34);
      $pdf->SetFont('Arial', '', 10);
      $pdf->SetWidths(array(30,75,20,20,20,20,20,25,20,30,35,20));
      $pdf->Row(array('Kd. Perawatan', 'Nama Perawatan', 'B. Material', 'B. BHP', 'B. Dokter', 'B. Perawat', 'KSO', 'Manajemen', 'Ttl. Dokter', 'Ttl. Perawat', 'Ttl. Dokter/Perawat', 'Status'));

      foreach ($pasien as $hasil) {
        $status = 'Aktif';
        if($hasil['status'] == '0') {
          $status = 'Tidak Aktif';
        }
        $pdf->Row(array($hasil['kd_jenis_prw'], $hasil['nm_perawatan'], $hasil['material'], $hasil['bhp'], $hasil['tarif_tindakandr'], $hasil['tarif_tindakanpr'], $hasil['kso'], $hasil['menejemen'], $hasil['total_byrdr'], $hasil['total_byrpr'], $hasil['total_byrdrpr'], $status));
      }
      $pdf->Output('laporan_pasien_'.date('Y-m-d').'.pdf','I');

    }

    /* End Master Jns_Perawatan Section */

    /* Master Jns_Perawatan Lab Section */
    public function getJnsPerawatanLab($page = 1)
    {
        $this->_addHeaderFiles();
        $perpage = '10';
        $phrase = '';
        if(isset($_GET['s']))
          $phrase = $_GET['s'];

        $status = '1';
        if(isset($_GET['status']))
          $status = $_GET['status'];

        // pagination
        $totalRecords = $this->db('jns_perawatan_lab')
            ->select('kd_jenis_prw')
            ->where('status', $status)
            ->like('kd_jenis_prw', '%'.$phrase.'%')
            ->like('nm_perawatan', '%'.$phrase.'%')
            ->toArray();
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'master', 'jnsperawatanlab', '%d']));
        $this->assign['pagination'] = $pagination->nav('pagination','5');
        $this->assign['totalRecords'] = $totalRecords;

        // list
        $offset = $pagination->offset();
        $rows = $this->db('jns_perawatan_lab')
            ->where('status', $status)
            ->like('kd_jenis_prw', '%'.$phrase.'%')
            ->like('nm_perawatan', '%'.$phrase.'%')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();

        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                $row['editURL'] = url([ADMIN, 'master', 'jnsperawatanlabedit', $row['kd_jenis_prw']]);
                $row['delURL']  = url([ADMIN, 'master', 'jnsperawatanlabdelete', $row['kd_jenis_prw']]);
                $row['restoreURL']  = url([ADMIN, 'master', 'jnsperawatanlabrestore', $row['kd_jenis_prw']]);
                $row['viewURL'] = url([ADMIN, 'master', 'jnsperawatanlabview', $row['kd_jenis_prw']]);
                $this->assign['list'][] = $row;
            }
        }

        $this->assign['title'] = 'Kelola Jenis Perawatan Laboratorium';
        $this->assign['getStatus'] = isset($_GET['status']);
        $this->assign['addURL'] = url([ADMIN, 'master', 'jnsperawatanlabadd']);
        $this->assign['printURL'] = url([ADMIN, 'master', 'jnsperawatanlabprint']);

        return $this->draw('jnsperawatanlab.manage.html', ['jnsperawatanlab' => $this->assign]);

    }

    public function getJnsPerawatanLabAdd()
    {
        $this->_addHeaderFiles();
        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = [
              'kd_jenis_prw' => '',
              'nm_perawatan' => '',
              'bagian_rs' => '',
              'bhp' => '',
              'tarif_perujuk' => '',
              'tarif_tindakan_dokter' => '',
              'tarif_tindakan_petugas' => '',
              'kso' => '',
              'menejemen' => '',
              'total_byr' => '',
              'kd_pj' => '',
              'status' => '',
              'kelas' => ''
            ];
        }

        $this->assign['title'] = 'Tambah Jenis Perawatan Laboratorium';
        $this->assign['status'] = $this->core->getEnum('jns_perawatan_lab', 'status');
        $this->assign['kelas'] = $this->core->getEnum('jns_perawatan_lab', 'kelas');
        $this->assign['kd_pj'] = $this->db('penjab')->toArray();

        return $this->draw('jnsperawatanlab.form.html', ['jnsperawatanlab' => $this->assign]);
    }

    public function getJnsPerawatanLabEdit($id)
    {
        $this->_addHeaderFiles();
        $row = $this->db('jns_perawatan_lab')->where('kd_jenis_prw', $id)->oneArray();
        if (!empty($row)) {
            $this->assign['form'] = $row;
            $this->assign['title'] = 'Edit Jenis Perawatan Laboratorium';
            $this->assign['status'] = $this->core->getEnum('jns_perawatan_lab', 'status');
            $this->assign['kelas'] = $this->core->getEnum('jns_perawatan_lab', 'kelas');
            $this->assign['kd_pj'] = $this->db('penjab')->toArray();

            return $this->draw('jnsperawatanlab.form.html', ['jnsperawatanlab' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'master', 'jnsperawatanlab']));
        }
    }

    public function getJnsPerawatanLabDelete($id)
    {
        if ($this->core->db('jns_perawatan_lab')->where('kd_jenis_prw', $id)->update('status', '0')) {
            $this->notify('success', 'Hapus sukses');
        } else {
            $this->notify('failure', 'Hapus gagal');
        }
        redirect(url([ADMIN, 'master', 'jnsperawatanlab']));
    }

    public function getJnsPerawatanLabRestore($id)
    {
        if ($this->core->db('jns_perawatan_lab')->where('kd_jenis_prw', $id)->update('status', '1')) {
            $this->notify('success', 'Restore sukses');
        } else {
            $this->notify('failure', 'Restore gagal');
        }
        redirect(url([ADMIN, 'master', 'jnsperawatanlab']));
    }

    public function postJnsPerawatanLabSave($id = null)
    {
        $errors = 0;

        if (!$id) {
            $location = url([ADMIN, 'master', 'jnsperawatanlabadd']);
        } else {
            $location = url([ADMIN, 'master', 'jnsperawatanlabedit', $id]);
        }

        if (checkEmptyFields(['kd_jenis_prw', 'nm_perawatan'], $_POST)) {
            $this->notify('failure', 'Isian masih ada yang kosong');
            redirect($location, $_POST);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (!$id) {    // new
                $_POST['status'] = '1';
                $query = $this->db('jns_perawatan_lab')->save($_POST);
            } else {        // edit
                $query = $this->db('jns_perawatan_lab')->where('kd_jenis_prw', $id)->save($_POST);
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

    public function getJnsPerawatanLabPrint()
    {
      $pasien = $this->db('jns_perawatan_lab')->toArray();
      $logo = 'data:image/png;base64,' . base64_encode($this->core->getSettings('logo'));

      $pdf = new PDF_MC_Table('L','mm','Legal');
      $pdf->AddPage();
      $pdf->SetAutoPageBreak(true, 10);
      $pdf->SetTopMargin(10);
      $pdf->SetLeftMargin(10);
      $pdf->SetRightMargin(10);

      $pdf->Image($logo, 10, 8, '18', '18', 'png');
      $pdf->SetFont('Arial', '', 24);
      $pdf->Text(30, 16, $this->core->getSettings('nama_instansi'));
      $pdf->SetFont('Arial', '', 10);
      $pdf->Text(30, 21, $this->core->getSettings('alamat_instansi').' - '.$this->core->getSettings('kabupaten'));
      $pdf->Text(30, 25, $this->core->getSettings('kontak').' - '.$this->core->getSettings('email'));
      $pdf->Line(10, 30, 345, 30);
      $pdf->Line(10, 31, 345, 31);
      $pdf->Ln(34);
      $pdf->Text(10, 40, 'DATA JENIS PERAWATAN LABORATORIUM');
      $pdf->SetFont('Arial', '', 10);
      $pdf->SetWidths(array(30,80,30,20,20,20,20,20,25,25,25,20));
      $pdf->Row(array('Kd. Perawatan', 'Nama Perawatan', 'B. Rumah Sakit', 'B. BHP', 'B. Perujuk', 'B. Dokter', 'B. Petugas', 'KSO', 'Manajemen', 'Total Biaya', 'Kelas', 'Status'));

      foreach ($pasien as $hasil) {
        $status = 'Aktif';
        if($hasil['status'] == '0') {
          $status = 'Tidak Aktif';
        }
        $pdf->Row(array($hasil['kd_jenis_prw'], $hasil['nm_perawatan'], $hasil['bagian_rs'], $hasil['bhp'], $hasil['tarif_perujuk'], $hasil['tarif_tindakan_dokter'], $hasil['tarif_tindakan_petugas'], $hasil['kso'], $hasil['menejemen'], $hasil['total_byr'], $hasil['kelas'], $status));
      }
      $pdf->Output('laporan_pasien_'.date('Y-m-d').'.pdf','I');

    }

    /* End Master Jns_Perawatan Lab Section */

    /* Master Jns_Perawatan Rad Section */
    public function getJnsPerawatanRad($page = 1)
    {
        $this->_addHeaderFiles();
        $perpage = '10';
        $phrase = '';
        if(isset($_GET['s']))
          $phrase = $_GET['s'];

        $status = '1';
        if(isset($_GET['status']))
          $status = $_GET['status'];

        // pagination
        $totalRecords = $this->db('jns_perawatan_radiologi')
            ->select('kd_jenis_prw')
            ->where('status', $status)
            ->like('kd_jenis_prw', '%'.$phrase.'%')
            ->like('nm_perawatan', '%'.$phrase.'%')
            ->toArray();
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'master', 'jns_perawatan_radiologi', '%d']));
        $this->assign['pagination'] = $pagination->nav('pagination','5');
        $this->assign['totalRecords'] = $totalRecords;

        // list
        $offset = $pagination->offset();
        $rows = $this->db('jns_perawatan_radiologi')
            ->where('status', $status)
            ->like('kd_jenis_prw', '%'.$phrase.'%')
            ->like('nm_perawatan', '%'.$phrase.'%')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();

        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                $row['editURL'] = url([ADMIN, 'master', 'jnsperawatanradedit', $row['kd_jenis_prw']]);
                $row['delURL']  = url([ADMIN, 'master', 'jnsperawatanraddelete', $row['kd_jenis_prw']]);
                $row['restoreURL']  = url([ADMIN, 'master', 'jnsperawatanradrestore', $row['kd_jenis_prw']]);
                $row['viewURL'] = url([ADMIN, 'master', 'jnsperawatanradview', $row['kd_jenis_prw']]);
                $this->assign['list'][] = $row;
            }
        }

        $this->assign['title'] = 'Kelola Jenis Perawatan Laboratorium';
        $this->assign['getStatus'] = isset($_GET['status']);
        $this->assign['addURL'] = url([ADMIN, 'master', 'jnsperawatanradadd']);
        $this->assign['printURL'] = url([ADMIN, 'master', 'jnsperawatanradprint']);

        return $this->draw('jnsperawatanrad.manage.html', ['jnsperawatanrad' => $this->assign]);

    }

    public function getJnsPerawatanRadAdd()
    {
        $this->_addHeaderFiles();
        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = [
              'kd_jenis_prw' => '',
              'nm_perawatan' => '',
              'bagian_rs' => '',
              'bhp' => '',
              'tarif_perujuk' => '',
              'tarif_tindakan_dokter' => '',
              'tarif_tindakan_petugas' => '',
              'kso' => '',
              'menejemen' => '',
              'total_byr' => '',
              'kd_pj' => '',
              'status' => '',
              'kelas' => ''
            ];
        }

        $this->assign['title'] = 'Tambah Jenis Perawatan Laboratorium';
        $this->assign['status'] = $this->core->getEnum('jns_perawatan_radiologi', 'status');
        $this->assign['kelas'] = $this->core->getEnum('jns_perawatan_radiologi', 'kelas');
        $this->assign['kd_pj'] = $this->db('penjab')->toArray();

        return $this->draw('jnsperawatanrad.form.html', ['jnsperawatanrad' => $this->assign]);
    }

    public function getJnsPerawatanRadEdit($id)
    {
        $this->_addHeaderFiles();
        $row = $this->db('jns_perawatan_radiologi')->where('kd_jenis_prw', $id)->oneArray();
        if (!empty($row)) {
            $this->assign['form'] = $row;
            $this->assign['title'] = 'Edit Jenis Perawatan Laboratorium';
            $this->assign['status'] = $this->core->getEnum('jns_perawatan_radiologi', 'status');
            $this->assign['kelas'] = $this->core->getEnum('jns_perawatan_radiologi', 'kelas');
            $this->assign['kd_pj'] = $this->db('penjab')->toArray();

            return $this->draw('jnsperawatanrad.form.html', ['jnsperawatanrad' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'master', 'jnsperawatanrad']));
        }
    }

    public function getJnsPerawatanRadDelete($id)
    {
        if ($this->core->db('jns_perawatan_radiologi')->where('kd_jenis_prw', $id)->update('status', '0')) {
            $this->notify('success', 'Hapus sukses');
        } else {
            $this->notify('failure', 'Hapus gagal');
        }
        redirect(url([ADMIN, 'master', 'jnsperawatanrad']));
    }

    public function getJnsPerawatanRadRestore($id)
    {
        if ($this->core->db('jns_perawatan_radiologi')->where('kd_jenis_prw', $id)->update('status', '1')) {
            $this->notify('success', 'Restore sukses');
        } else {
            $this->notify('failure', 'Restore gagal');
        }
        redirect(url([ADMIN, 'master', 'jnsperawatanrad']));
    }

    public function postJnsPerawatanRadSave($id = null)
    {
        $errors = 0;

        if (!$id) {
            $location = url([ADMIN, 'master', 'jnsperawatanradadd']);
        } else {
            $location = url([ADMIN, 'master', 'jnsperawatanradedit', $id]);
        }

        if (checkEmptyFields(['kd_jenis_prw', 'nm_perawatan'], $_POST)) {
            $this->notify('failure', 'Isian masih ada yang kosong');
            redirect($location, $_POST);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (!$id) {    // new
                $_POST['status'] = '1';
                $query = $this->db('jns_perawatan_radiologi')->save($_POST);
            } else {        // edit
                $query = $this->db('jns_perawatan_radiologi')->where('kd_jenis_prw', $id)->save($_POST);
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

    public function getJnsPerawatanRadPrint()
    {
      $pasien = $this->db('jns_perawatan_radiologi')->toArray();
      $logo = 'data:image/png;base64,' . base64_encode($this->core->getSettings('logo'));

      $pdf = new PDF_MC_Table('L','mm','Legal');
      $pdf->AddPage();
      $pdf->SetAutoPageBreak(true, 10);
      $pdf->SetTopMargin(10);
      $pdf->SetLeftMargin(10);
      $pdf->SetRightMargin(10);

      $pdf->Image($logo, 10, 8, '18', '18', 'png');
      $pdf->SetFont('Arial', '', 24);
      $pdf->Text(30, 16, $this->core->getSettings('nama_instansi'));
      $pdf->SetFont('Arial', '', 10);
      $pdf->Text(30, 21, $this->core->getSettings('alamat_instansi').' - '.$this->core->getSettings('kabupaten'));
      $pdf->Text(30, 25, $this->core->getSettings('kontak').' - '.$this->core->getSettings('email'));
      $pdf->Line(10, 30, 345, 30);
      $pdf->Line(10, 31, 345, 31);
      $pdf->Ln(34);
      $pdf->Text(10, 40, 'DATA JENIS PERAWATAN RADIOLOGI');
      $pdf->SetFont('Arial', '', 10);
      $pdf->SetWidths(array(30,80,30,20,20,20,20,20,25,25,25,20));
      $pdf->Row(array('Kd. Perawatan', 'Nama Perawatan', 'B. Rumah Sakit', 'B. BHP', 'B. Perujuk', 'B. Dokter', 'B. Petugas', 'KSO', 'Manajemen', 'Total Biaya', 'Kelas', 'Status'));

      foreach ($pasien as $hasil) {
        $status = 'Aktif';
        if($hasil['status'] == '0') {
          $status = 'Tidak Aktif';
        }
        $pdf->Row(array($hasil['kd_jenis_prw'], $hasil['nm_perawatan'], $hasil['bagian_rs'], $hasil['bhp'], $hasil['tarif_perujuk'], $hasil['tarif_tindakan_dokter'], $hasil['tarif_tindakan_petugas'], $hasil['kso'], $hasil['menejemen'], $hasil['total_byr'], $hasil['kelas'], $status));
      }
      $pdf->Output('laporan_pasien_'.date('Y-m-d').'.pdf','I');

    }

    /* End Master Jns_Perawatan Rad Section */

    public function getCSS()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/master/css/admin/master.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/master/js/admin/master.js');
        exit();
    }

    private function _addHeaderFiles()
    {
        // CSS
        $this->core->addCSS(url('assets/css/jquery-ui.css'));

        // JS
        $this->core->addJS(url('assets/jscripts/jquery-ui.js'), 'footer');

        // MODULE SCRIPTS
        $this->core->addCSS(url([ADMIN, 'master', 'css']));
        $this->core->addJS(url([ADMIN, 'master', 'javascript']), 'footer');
    }

}
