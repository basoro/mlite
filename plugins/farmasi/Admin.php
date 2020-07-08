<?php

namespace Plugins\Farmasi;

use Systems\AdminModule;
use Systems\Lib\Fpdf\PDF_MC_Table;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Manage' => 'manage',
            'Pengadaan' => 'pengadaan',
            'Pembelian' => 'pembelian',
            'Stok Opname' => 'opname',
            'Mutasi' => 'mutasi',
            'Master' => 'master',
            'Pengaturan' => 'settings',
        ];
    }

    /* Databarang Section */
    public function getManage($page = 1)
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
            ->orLike('nama_brng', '%'.$phrase.'%')
            ->toArray();
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'farmasi', 'manage', '%d']));
        $this->assign['pagination'] = $pagination->nav('pagination','5');
        $this->assign['totalRecords'] = $totalRecords;

        // list
        $offset = $pagination->offset();
        $rows = $this->db('databarang')
            ->where('status', $status)
            ->like('kode_brng', '%'.$phrase.'%')
            ->orLike('nama_brng', '%'.$phrase.'%')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();

        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                $row['editURL'] = url([ADMIN, 'farmasi', 'edit', $row['kode_brng']]);
                $row['delURL']  = url([ADMIN, 'farmasi', 'delete', $row['kode_brng']]);
                $row['restoreURL']  = url([ADMIN, 'farmasi', 'restore', $row['kode_brng']]);
                $this->assign['list'][] = $row;
            }
        }

        $this->assign['title'] = 'Kelola Databarang';
        $this->assign['getStatus'] = isset($_GET['status']);
        $this->assign['addURL'] = url([ADMIN, 'farmasi', 'add']);
        $this->assign['printURL'] = url([ADMIN, 'farmasi', 'print']);

        return $this->draw('manage.html', ['databarang' => $this->assign]);

    }

    public function getAdd()
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

        $this->assign['title'] = 'Tambah Databarang ';
        $this->assign['status'] = $this->core->getEnum('databarang', 'status');
        $this->assign['kdjns'] = $this->db('jenis')->toArray();
        $this->assign['kode_sat'] = $this->db('kodesatuan')->toArray();
        $this->assign['kode_industri'] = $this->db('industrifarmasi')->toArray();
        $this->assign['kode_kategori'] = $this->db('kategori_barang')->toArray();
        $this->assign['kode_golongan'] = $this->db('golongan_barang')->toArray();

        return $this->draw('form.html', ['databarang' => $this->assign]);
    }

    public function getEdit($id)
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

            return $this->draw('form.html', ['databarang' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'farmasi', 'manage']));
        }
    }

    public function getDelete($id)
    {
        if ($this->core->db('databarang')->where('kode_brng', $id)->update('status', '0')) {
            $this->notify('success', 'Hapus sukses');
        } else {
            $this->notify('failure', 'Hapus gagal');
        }
        redirect(url([ADMIN, 'farmasi', 'manage']));
    }

    public function getRestore($id)
    {
        if ($this->core->db('databarang')->where('kode_brng', $id)->update('status', '1')) {
            $this->notify('success', 'Restore sukses');
        } else {
            $this->notify('failure', 'Restore gagal');
        }
        redirect(url([ADMIN, 'farmasi', 'manage']));
    }

    public function postSave($id = null)
    {
        $errors = 0;

        if (!$id) {
            $location = url([ADMIN, 'farmasi', 'add']);
        } else {
            $location = url([ADMIN, 'farmasi', 'edit', $id]);
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

    public function getPrint()
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

    /* End Databarang Section */

    public function getPengadaan()
    {

    }

    public function getPembelian()
    {

    }

    public function getOpname()
    {

    }

    public function getMutasi()
    {

    }

    /* Settings Farmasi Section */
    public function getSettings()
    {
        $this->assign['title'] = 'Pengaturan Modul Farmasi';
        $this->assign['bangsal'] = $this->db('bangsal')->toArray();
        $this->assign['farmasi'] = htmlspecialchars_array($this->options('farmasi'));
        return $this->draw('settings.html', ['settings' => $this->assign]);
    }

    public function postSaveSettings()
    {
        foreach ($_POST['farmasi'] as $key => $val) {
            $this->options('farmasi', $key, $val);
        }
        $this->notify('success', 'Pengaturan telah disimpan');
        redirect(url([ADMIN, 'farmasi', 'settings']));
    }
    /* End Settings Farmasi Section */

    /* Master Farmasi Section */
    public function getMaster()
    {
        $this->_addHeaderFiles();

        $rows = $this->db('kodesatuan')->toArray();
        $this->assign['kodesatuan'] = [];
        foreach ($rows as $row) {
            $row['editURL'] = url([ADMIN, 'farmasi', 'kodesatuanedit', $row['kode_sat']]);
            $this->assign['kodesatuan'][] = $row;
        }

        $rows = $this->db('jenis')->toArray();
        $this->assign['jenis'] = [];
        foreach ($rows as $row) {
            $row['editURL'] = url([ADMIN, 'farmasi', 'jenisedit', $row['kdjns']]);
            $this->assign['jenis'][] = $row;
        }

        $rows = $this->db('industrifarmasi')->toArray();
        $this->assign['kodeindustri'] = [];
        foreach ($rows as $row) {
            $row['editURL'] = url([ADMIN, 'farmasi', 'kodeindustriedit', $row['kode_industri']]);
            $this->assign['kodeindustri'][] = $row;
        }

        $rows = $this->db('kategori_barang')->toArray();
        $this->assign['kategori'] = [];
        foreach ($rows as $row) {
            $row['editURL'] = url([ADMIN, 'farmasi', 'kategoriedit', $row['kode']]);
            $this->assign['kategori'][] = $row;
        }

        $rows = $this->db('golongan_barang')->toArray();
        $this->assign['golongan'] = [];
        foreach ($rows as $row) {
            $row['editURL'] = url([ADMIN, 'farmasi', 'golonganedit', $row['kode']]);
            $this->assign['golongan'][] = $row;
        }

        $rows = $this->db('master_aturan_pakai')->toArray();
        $this->assign['aturanpakai'] = [];
        foreach ($rows as $row) {
            $row['editURL'] = url([ADMIN, 'farmasi', 'aturanpakaiedit', $row['aturan']]);
            $this->assign['aturanpakai'][] = $row;
        }

        $rows = $this->db('metode_racik')->toArray();
        $this->assign['metoderacik'] = [];
        foreach ($rows as $row) {
            $row['editURL'] = url([ADMIN, 'farmasi', 'metoderacikedit', $row['kd_racik']]);
            $this->assign['metoderacik'][] = $row;
        }

        return $this->draw('master.html', ['master' => $this->assign]);
    }

    public function getKodeSatuanAdd()
    {
        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = [
              'kode_sat' => '',
              'satuan' => ''
            ];
        }
        $this->assign['title'] = 'Tambah Master Kode Satuan';

        return $this->draw('kodesatuan.form.html', ['master' => $this->assign]);
    }

    public function getKodeSatuanEdit($id)
    {
        $row = $this->db('kodesatuan')->where('kode_sat', $id)->oneArray();
        if (!empty($row)) {
            $this->assign['form'] = $row;
            $this->assign['title'] = 'Edit Master Kode Satuan';

            return $this->draw('kodesatuan.form.html', ['master' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'farmasi', 'master']));
        }
    }

    public function postKodeSatuanSave($id = null)
    {
        $errors = 0;

        $cek_penjab = $this->db('kodesatuan')->where('kode_sat', $_POST['kode_sat'])->count();

        if (!$id) {
            $location = url([ADMIN, 'farmasi', 'master']);
        } else {
            $location = url([ADMIN, 'farmasi', 'kodesatuanedit', $id]);
        }

        if (checkEmptyFields(['kode_sat', 'satuan'], $_POST)) {
            $this->notify('failure', 'Isian ada yang masih kosong');
            redirect($location, $_POST);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (!$cek_penjab) {    // new
                $query = $this->db('kodesatuan')->save($_POST);
            } else {        // edit
                $query = $this->db('kodesatuan')->where('kode_sat', $_POST['kode_sat'])->save($_POST);
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

    public function getJenisAdd()
    {
        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = [
              'kdjns' => '',
              'nama' => '',
              'keterangan' => ''
            ];
        }
        $this->assign['title'] = 'Tambah Master Jenis';

        return $this->draw('jenis.form.html', ['master' => $this->assign]);
    }

    public function getJenisEdit($id)
    {
        $row = $this->db('jenis')->where('kdjns', $id)->oneArray();
        if (!empty($row)) {
            $this->assign['form'] = $row;
            $this->assign['title'] = 'Edit Master Jenis';

            return $this->draw('jenis.form.html', ['master' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'farmasi', 'master']));
        }
    }

    public function postJenisSave($id = null)
    {
        $errors = 0;

        $cek_penjab = $this->db('jenis')->where('kdjns', $_POST['kdjns'])->count();

        if (!$id) {
            $location = url([ADMIN, 'farmasi', 'master']);
        } else {
            $location = url([ADMIN, 'farmasi', 'jenisedit', $id]);
        }

        if (checkEmptyFields(['kdjns', 'nama'], $_POST)) {
            $this->notify('failure', 'Isian ada yang masih kosong');
            redirect($location, $_POST);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (!$cek_penjab) {    // new
                $query = $this->db('jenis')->save($_POST);
            } else {        // edit
                $query = $this->db('jenis')->where('kdjns', $_POST['kdjns'])->save($_POST);
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

    public function getKodeIndustriAdd()
    {
        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = [
              'kode_industri' => '',
              'nama_industri' => '',
              'alamat' => '',
              'kota' => '',
              'no_telp' => ''
            ];
        }
        $this->assign['title'] = 'Tambah Master Industri Farmasi';

        return $this->draw('kodeindustri.form.html', ['master' => $this->assign]);
    }

    public function getKodeIndustriEdit($id)
    {
        $row = $this->db('industrifarmasi')->where('kode_industri', $id)->oneArray();
        if (!empty($row)) {
            $this->assign['form'] = $row;
            $this->assign['title'] = 'Edit Master Industri Farmasi';

            return $this->draw('kodeindustri.form.html', ['master' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'farmasi', 'master']));
        }
    }

    public function postKodeIndustriSave($id = null)
    {
        $errors = 0;

        $cek_penjab = $this->db('industrifarmasi')->where('kode_industri', $_POST['kode_industri'])->count();

        if (!$id) {
            $location = url([ADMIN, 'farmasi', 'master']);
        } else {
            $location = url([ADMIN, 'farmasi', 'kodeindustriedit', $id]);
        }

        if (checkEmptyFields(['kode_industri', 'nama_industri'], $_POST)) {
            $this->notify('failure', 'Isian ada yang masih kosong');
            redirect($location, $_POST);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (!$cek_penjab) {    // new
                $query = $this->db('industrifarmasi')->save($_POST);
            } else {        // edit
                $query = $this->db('industrifarmasi')->where('kode_industri', $_POST['kode_industri'])->save($_POST);
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

    public function getKategoriAdd()
    {
        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = [
              'kode' => '',
              'nama' => ''
            ];
        }
        $this->assign['title'] = 'Tambah Master Kategori';

        return $this->draw('kategori.form.html', ['master' => $this->assign]);
    }

    public function getKategoriEdit($id)
    {
        $row = $this->db('kategori_barang')->where('kode', $id)->oneArray();
        if (!empty($row)) {
            $this->assign['form'] = $row;
            $this->assign['title'] = 'Edit Master Kategori Barang';

            return $this->draw('kategori.form.html', ['master' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'farmasi', 'master']));
        }
    }

    public function postKategoriSave($id = null)
    {
        $errors = 0;

        $cek_penjab = $this->db('kategori_barang')->where('kode', $_POST['kode'])->count();

        if (!$id) {
            $location = url([ADMIN, 'farmasi', 'master']);
        } else {
            $location = url([ADMIN, 'farmasi', 'kategoriedit', $id]);
        }

        if (checkEmptyFields(['kode', 'nama'], $_POST)) {
            $this->notify('failure', 'Isian ada yang masih kosong');
            redirect($location, $_POST);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (!$cek_penjab) {    // new
                $query = $this->db('kategori_barang')->save($_POST);
            } else {        // edit
                $query = $this->db('kategori_barang')->where('kode', $_POST['kode'])->save($_POST);
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

    public function getGolonganAdd()
    {
        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = [
              'kode' => '',
              'nama' => ''
            ];
        }
        $this->assign['title'] = 'Tambah Master Kategori';

        return $this->draw('golongan.form.html', ['master' => $this->assign]);
    }

    public function getGolonganEdit($id)
    {
        $row = $this->db('golongan_barang')->where('kode', $id)->oneArray();
        if (!empty($row)) {
            $this->assign['form'] = $row;
            $this->assign['title'] = 'Edit Master Golongan Barang';

            return $this->draw('golongan.form.html', ['master' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'farmasi', 'master']));
        }
    }

    public function postGolonganSave($id = null)
    {
        $errors = 0;

        $cek_penjab = $this->db('golongan_barang')->where('kode', $_POST['kode'])->count();

        if (!$id) {
            $location = url([ADMIN, 'farmasi', 'master']);
        } else {
            $location = url([ADMIN, 'farmasi', 'golonganedit', $id]);
        }

        if (checkEmptyFields(['kode', 'nama'], $_POST)) {
            $this->notify('failure', 'Isian ada yang masih kosong');
            redirect($location, $_POST);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (!$cek_penjab) {    // new
                $query = $this->db('golongan_barang')->save($_POST);
            } else {        // edit
                $query = $this->db('golongan_barang')->where('kode', $_POST['kode'])->save($_POST);
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

    public function getAturanPakaiAdd()
    {
        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = [
              'aturan' => ''
            ];
        }
        $this->assign['title'] = 'Tambah Master Aturan Pakai';

        return $this->draw('aturanpakai.form.html', ['master' => $this->assign]);
    }

    public function getAturanPakaiEdit($id)
    {
        $row = $this->db('master_aturan_pakai')->where('aturan', $id)->oneArray();
        if (!empty($row)) {
            $this->assign['form'] = $row;
            $this->assign['title'] = 'Edit Master Aturan Pakai';

            return $this->draw('aturanpakai.form.html', ['master' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'farmasi', 'master']));
        }
    }

    public function postAturanPakaiSave($id = null)
    {
        $errors = 0;

        $cek_penjab = $this->db('master_aturan_pakai')->where('aturan', $_POST['aturan'])->count();

        if (!$id) {
            $location = url([ADMIN, 'farmasi', 'master']);
        } else {
            $location = url([ADMIN, 'farmasi', 'aturanpakaiedit', $id]);
        }

        if (checkEmptyFields(['aturan'], $_POST)) {
            $this->notify('failure', 'Isian ada yang masih kosong');
            redirect($location, $_POST);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (!$cek_penjab) {    // new
                $query = $this->db('master_aturan_pakai')->save($_POST);
            } else {        // edit
                $query = $this->db('master_aturan_pakai')->where('aturan', $_POST['aturan'])->save($_POST);
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

    public function getMetodeRacikAdd()
    {
        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = [
              'kd_racik' => '',
              'nm_racik' => ''
            ];
        }
        $this->assign['title'] = 'Tambah Master Metode Racik';

        return $this->draw('metoderacik.form.html', ['master' => $this->assign]);
    }

    public function getMetodeRacikEdit($id)
    {
        $row = $this->db('metode_racik')->where('kd_racik', $id)->oneArray();
        if (!empty($row)) {
            $this->assign['form'] = $row;
            $this->assign['title'] = 'Edit Master Metode Racik';

            return $this->draw('metoderacik.form.html', ['master' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'farmasi', 'master']));
        }
    }

    public function postMetodeRacikSave($id = null)
    {
        $errors = 0;

        $cek_penjab = $this->db('metode_racik')->where('kd_racik', $_POST['kd_racik'])->count();

        if (!$id) {
            $location = url([ADMIN, 'farmasi', 'master']);
        } else {
            $location = url([ADMIN, 'farmasi', 'metoderacikedit', $id]);
        }

        if (checkEmptyFields(['kd_racik', 'nm_racik'], $_POST)) {
            $this->notify('failure', 'Isian ada yang masih kosong');
            redirect($location, $_POST);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (!$cek_penjab) {    // new
                $query = $this->db('metode_racik')->save($_POST);
            } else {        // edit
                $query = $this->db('metode_racik')->where('kd_racik', $_POST['kd_racik'])->save($_POST);
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


    public function getCSS()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/farmasi/css/admin/farmasi.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/farmasi/js/admin/farmasi.js');
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
        $this->core->addCSS(url([ADMIN, 'farmasi', 'css']));
        $this->core->addJS(url([ADMIN, 'farmasi', 'javascript']), 'footer');
    }

}
