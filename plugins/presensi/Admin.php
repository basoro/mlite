<?php

namespace Plugins\Presensi;

use Systems\AdminModule;
use Systems\Lib\Fpdf\PDF_MC_Table;

class Admin extends AdminModule
{

    public function navigation()
    {
        if ($this->core->getUserInfo('role') == 'admin') {
            return [
                'Kelola' => 'manage',
                'Presensi Masuk' => 'presensi',
                'Rekap Presensi' => 'rekap_presensi',
                'Barcode Presensi' => 'barcode',
                'Jam Jaga' => 'jamjaga',
                'Jadwal Pegawai' => 'jadwal',
                'Jadwal Tambahan' => 'jadwal_tambahan'
            ];
        }else{
            return [
                'Kelola' => 'manage',
                'Presensi Masuk' => 'presensi',
                'Rekap Presensi' => 'rekap_presensi',
                'Jadwal Pegawai' => 'jadwal',
                'Jadwal Tambahan' => 'jadwal_tambahan'
            ];
        }
    }

    public function getManage()
    {
      if ($this->core->getUserInfo('role') == 'admin') {
        $sub_modules = [
          ['name' => 'Presensi', 'url' => url([ADMIN, 'presensi', 'presensi']), 'icon' => 'cubes', 'desc' => 'Presensi Pegawai'],
          ['name' => 'Rekap Presensi', 'url' => url([ADMIN, 'presensi', 'rekap_presensi']), 'icon' => 'cubes', 'desc' => 'Rekap Presensi Pegawai'],
          ['name' => 'Barcode Presensi', 'url' => url([ADMIN, 'presensi', 'barcode']), 'icon' => 'cubes', 'desc' => 'Barcode Presensi Pegawai'],
          ['name' => 'Jam Jaga', 'url' => url([ADMIN, 'presensi', 'jamjaga']), 'icon' => 'cubes', 'desc' => 'Jam Jaga Pegawai'],
          ['name' => 'Jadwal', 'url' => url([ADMIN, 'presensi', 'jadwal']), 'icon' => 'cubes', 'desc' => 'Jadwal Pegawai'],
          ['name' => 'Jadwal Tambahan', 'url' => url([ADMIN, 'presensi', 'jadwal_tambahan']), 'icon' => 'cubes', 'desc' => 'Jadwal Tambahan Pegawai'],
        ];
      } else {
        $sub_modules = [
          ['name' => 'Presensi', 'url' => url([ADMIN, 'presensi', 'presensi']), 'icon' => 'cubes', 'desc' => 'Presensi Pegawai'],
          ['name' => 'Rekap Presensi', 'url' => url([ADMIN, 'presensi', 'rekap_presensi']), 'icon' => 'cubes', 'desc' => 'Rekap Presensi Pegawai'],
          ['name' => 'Jadwal', 'url' => url([ADMIN, 'presensi', 'jadwal']), 'icon' => 'cubes', 'desc' => 'Jadwal Pegawai'],
          ['name' => 'Jadwal Tambahan', 'url' => url([ADMIN, 'presensi', 'jadwal_tambahan']), 'icon' => 'cubes', 'desc' => 'Jadwal Tambahan Pegawai'],
        ];
      }
      return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
    }

    public function getJamJaga($page = 1)
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
        $totalRecords = $this->db('jam_jaga')
            ->like('shift', '%'.$phrase.'%')
            ->orLike('dep_id', '%'.$phrase.'%')
            ->toArray();
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'presensi', 'jamjaga', '%d']));
        $this->assign['pagination'] = $pagination->nav('pagination','5');
        $this->assign['totalRecords'] = $totalRecords;

        // list
        $offset = $pagination->offset();
        $rows = $this->db('jam_jaga')
            ->like('shift', '%'.$phrase.'%')
            ->orLike('dep_id', '%'.$phrase.'%')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();

        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                // $row['editURL'] = url([ADMIN, 'master', 'petugasedit', $row['nip']]);
                // $row['delURL']  = url([ADMIN, 'master', 'petugasdelete', $row['nip']]);
                // $row['restoreURL']  = url([ADMIN, 'master', 'petugasrestore', $row['nip']]);
                // $row['viewURL'] = url([ADMIN, 'master', 'petugasview', $row['nip']]);
                $this->assign['list'][] = $row;
            }
        }

        $this->assign['getStatus'] = isset($_GET['status']);
        $this->assign['addURL'] = url([ADMIN, 'presensi', 'jagaadd']);
        // $this->assign['printURL'] = url([ADMIN, 'master', 'petugasprint']);

        return $this->draw('index.html', ['jamjaga' => $this->assign]);
    }

    public function getJagaAdd()
    {
        $this->_addHeaderFiles();
        if(!empty($redirectData = getRedirectData())){
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] =
            [
                'no_id' => '',
                'dep_id' => '',
                'shift' => '',
                'jam_masuk' => '',
                'jam_pulang' => ''
            ];
        }

        $this->assign['dep_id'] = $this->db('departemen')->toArray();
        $this->assign['shift'] = $this->db('jam_masuk')->toArray();

        return $this->draw('jagaadd.form.html', ['jagaadd' => $this->assign]);
    }

    public function postJagaSave($id = null)
    {
        $errors = 0;

        if (!$id){
            $location = url([ADMIN, 'presensi', 'jamjaga']);
        } else {
            $location = url([ADMIN, 'presensi', 'editjaga']);
        }

        if (checkEmptyFields(['dep_id'], $_POST)){
            $this->notify('failure', 'Isian kosong');
            redirect($location, $_POST);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (!$id) {
                $query = $this->db('jam_jaga')->save($_POST);
            } else {
                $query = $this->db('jam_jaga')->where('no_id', $id)->save($_POST);
            }

            if ($query) {
                $this->notify('success', 'Simpan sukses');
            } else {
                $this->notify('failure', 'Simpan gagal');
            }

            redirect($location);
        }

        redirect($location, $_POST);
    }

    public function getJadwal($page = 1)
    {
        $this->_addHeaderFiles();
        $perpage = '10';
        $phrase = '';
        if(isset($_GET['s']))
          $phrase = $_GET['s'];

        $bulan = date('m');
        if (isset($_GET['b'])) {
            $bulan = $_GET['b'];
        }

        $tahun = date('Y');
        if (isset($_GET['y'])) {
            $tahun = $_GET['y'];
        }

        $username = $this->core->getUserInfo('username', null, true);

        // pagination
        if($this->core->getUserInfo('role') == 'admin'){
        $totalRecords = $this->db('jadwal_pegawai')
            ->join('pegawai','pegawai.id=jadwal_pegawai.id')
            ->where('jadwal_pegawai.tahun',$tahun)
            ->like('jadwal_pegawai.bulan',$bulan.'%')
            ->like('pegawai.nama', '%'.$phrase.'%')
            ->toArray();
        }else{
            $totalRecords = $this->db('jadwal_pegawai')
            ->join('pegawai','pegawai.id=jadwal_pegawai.id')
            ->where('jadwal_pegawai.tahun',$tahun)
            ->where('jadwal_pegawai.bulan',$bulan)
            ->where('departemen', $this->core->getPegawaiInfo('departemen',$username))
            ->where('bidang', $this->core->getPegawaiInfo('bidang',$username))
            ->like('pegawai.nama', '%'.$phrase.'%')
            ->toArray();
        }
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'presensi', 'jadwal', '%d?b='.$bulan.'&s='.$phrase]));
        $this->assign['pagination'] = $pagination->nav('pagination','5');
        $this->assign['totalRecords'] = $totalRecords;

        // list
        $offset = $pagination->offset();
        if($this->core->getUserInfo('role') == 'admin'){
            $rows = $this->db('jadwal_pegawai')
            ->join('pegawai','pegawai.id=jadwal_pegawai.id')
            ->where('jadwal_pegawai.tahun',$tahun)
            ->like('jadwal_pegawai.bulan',$bulan.'%')
            ->like('pegawai.nama', '%'.$phrase.'%')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
        }else{
        $rows = $this->db('jadwal_pegawai')
            ->join('pegawai','pegawai.id=jadwal_pegawai.id')
            ->where('jadwal_pegawai.tahun',$tahun)
            ->where('jadwal_pegawai.bulan',$bulan)
            ->where('departemen', $this->core->getPegawaiInfo('departemen',$username))
            ->where('bidang', $this->core->getPegawaiInfo('bidang',$username))
            ->like('pegawai.nama', '%'.$phrase.'%')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
        }
        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                $row['editURL'] = url([ADMIN, 'presensi', 'jadwaledit', $row['id'] , $bulan , $tahun]);
                // $row['delURL']  = url([ADMIN, 'master', 'petugasdelete', $row['nip']]);
                // $row['restoreURL']  = url([ADMIN, 'master', 'petugasrestore', $row['nip']]);
                // $row['viewURL'] = url([ADMIN, 'master', 'petugasview', $row['nip']]);
                $this->assign['list'][] = $row;
            }
        }

        $this->assign['getStatus'] = isset($_GET['status']);
        $this->assign['addURL'] = url([ADMIN, 'presensi', 'jadwaladd']);
        $month = array(
            '01' => 'JAN',
            '02' => 'FEB',
            '03' => 'MAR',
            '04' => 'APR',
            '05' => 'MEI',
            '06' => 'JUN',
            '07' => 'JUL',
            '08' => 'AGU',
            '09' => 'SEP',
            '10' => 'OKT',
            '11' => 'NOV',
            '12' => 'DES',
        );
        $this->assign['showBulan'] = $month[$bulan];
        // $this->assign['printURL'] = url([ADMIN, 'master', 'petugasprint']);
        $this->assign['bulan'] = array('','01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
        $this->assign['tahun'] = array('','2020', '2021', '2022');
        return $this->draw('jadwal.manage.html', ['jadwal' => $this->assign]);
    }

    public function getJadwalAdd()
    {
        $this->_addHeaderFiles();
        if (!empty($redirectData = getRedirectData())){
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = [
                'id' => '',
                'tahun' => '',
                'bulan' => '',
                'h1' => '',
                'h2' => '',
                'h3' => '',
                'h4' => '',
                'h5' => '',
                'h6' => '',
                'h7' => '',
                'h8' => '',
                'h9' => '',
                'h10' => '',
                'h11' => '',
                'h12' => '',
                'h13' => '',
                'h14' => '',
                'h15' => '',
                'h16' => '',
                'h17' => '',
                'h18' => '',
                'h19' => '',
                'h20' => '',
                'h21' => '',
                'h22' => '',
                'h23' => '',
                'h24' => '',
                'h25' => '',
                'h26' => '',
                'h27' => '',
                'h28' => '',
                'h29' => '',
                'h30' => '',
                'h31' => '',
            ];
        }
        $username = $this->core->getUserInfo('username', null, true);
        if($this->core->getUserInfo('role') == 'admin'){
            $this->assign['id'] = $this->db('pegawai')
                                    ->where('stts_aktif','AKTIF')
                                    ->toArray();
        }else{
            $this->assign['id'] = $this->db('pegawai')
                                ->where('departemen', $this->core->getPegawaiInfo('departemen',$username))
                                ->where('bidang', $this->core->getPegawaiInfo('bidang',$username))
                                ->where('stts_aktif','AKTIF')
                                ->toArray();
        }
        if($this->core->getUserInfo('role') == 'admin'){
            $this->assign['h1'] = $this->db('jam_masuk')->toArray();
        }else{
            $this->assign['h1'] = $this->db('jam_jaga')->where('dep_id', $this->core->getPegawaiInfo('departemen',$username))->toArray();
        }
        $this->assign['tahun'] = date('Y');
        $this->assign['bulan'] = array('','01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');

        return $this->draw('jadwal.form.html', ['jadwal' => $this->assign]);
    }

    public function getJadwalEdit($id,$bulan,$tahun)
    {
        $this->_addHeaderFiles();
        $row = $this->db('jadwal_pegawai')->where('id', $id)->where('tahun', $tahun)->where('bulan', $bulan)->oneArray();
        if (!empty($row)){
            $username = $this->core->getUserInfo('username', null, true);
            $this->assign['form'] = $row;
            if($this->core->getUserInfo('role') == 'admin'){
                $this->assign['id'] = $this->db('pegawai')
                                        ->where('stts_aktif','AKTIF')
                                        ->toArray();
            }else{
                $this->assign['id'] = $this->db('pegawai')
                                    ->where('departemen', $this->core->getPegawaiInfo('departemen',$username))
                                    ->where('bidang', $this->core->getPegawaiInfo('bidang',$username))
                                    ->where('stts_aktif','AKTIF')
                                    ->toArray();
            }
            if($this->core->getUserInfo('role') == 'admin'){
            	$this->assign['h1'] = $this->db('jam_masuk')->toArray();
            }else{
                $this->assign['h1'] = $this->db('jam_jaga')->where('dep_id', $this->core->getPegawaiInfo('departemen',$username))->toArray();
            }
            $this->assign['tahun'] = $tahun;
            $this->assign['bulan'] = array('','01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');

            return $this->draw('jadwal.form.html', ['jadwal' => $this->assign]);
        } else {
            redirect(url([ADMIN,'presensi','jadwal']));
        }

    }

    public function postJadwalSave($id = null)
    {
        $errors = 0;

        if (!$id){
            $location = url([ADMIN, 'presensi', 'jadwal']);
        } else {
            $location = url([ADMIN, 'presensi', 'jadwaledit', $id]);
        }

        //if (checkEmptyFields(['id'], $_POST)){
        //    $this->notify('failure', 'Isian kosong');
        //    redirect($location, $_POST);
       // }

        if (!$errors) {
            unset($_POST['save']);

            if (!$id) {
                $query = $this->db('jadwal_pegawai')->save($_POST);
            } else {
                $query = $this->db('jadwal_pegawai')->where('id', $id)->where('tahun', $_POST['tahun'])->where('bulan', $_POST['bulan'])->save($_POST);
            }

            if ($query) {
                $this->notify('success', 'Simpan sukses');
            } else {
                $this->notify('failure', 'Simpan gagal');
            }

            redirect($location);
        }

        redirect($location, $_POST);
    }

    public function getJadwal_Tambahan($page = 1)
    {
        $this->_addHeaderFiles();
        $perpage = '10';
        $phrase = '';
        if(isset($_GET['s']))
          $phrase = $_GET['s'];

        $bulan = date('m');
        if (isset($_GET['b'])) {
            $bulan = $_GET['b'];
        }

        $tahun = date('Y');
        if (isset($_GET['y'])) {
            $tahun = $_GET['y'];
        }

        $username = $this->core->getUserInfo('username', null, true);

        // pagination
        if($this->core->getUserInfo('role') == 'admin'){
        $totalRecords = $this->db('jadwal_tambahan')
            ->join('pegawai','pegawai.id=jadwal_tambahan.id')
            ->where('jadwal_tambahan.tahun',$tahun)
            ->like('jadwal_tambahan.bulan',$bulan.'%')
            ->like('pegawai.nama', '%'.$phrase.'%')
            ->toArray();
        }else{
            $totalRecords = $this->db('jadwal_tambahan')
            ->join('pegawai','pegawai.id=jadwal_tambahan.id')
            ->where('jadwal_tambahan.tahun',$tahun)
            ->where('jadwal_tambahan.bulan',$bulan)
            ->where('departemen', $this->core->getPegawaiInfo('departemen',$username))
            ->where('bidang', $this->core->getPegawaiInfo('bidang',$username))
            ->like('pegawai.nama', '%'.$phrase.'%')
            ->toArray();
        }
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'presensi', 'jadwal_tambahan', '%d?b='.$bulan.'&s='.$phrase]));
        $this->assign['pagination'] = $pagination->nav('pagination','5');
        $this->assign['totalRecords'] = $totalRecords;

        // list
        $offset = $pagination->offset();
        if($this->core->getUserInfo('role') == 'admin'){
            $rows = $this->db('jadwal_tambahan')
            ->join('pegawai','pegawai.id=jadwal_tambahan.id')
            ->where('jadwal_tambahan.tahun',$tahun)
            ->like('jadwal_tambahan.bulan',$bulan.'%')
            ->like('pegawai.nama', '%'.$phrase.'%')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
        }else{
        $rows = $this->db('jadwal_tambahan')
            ->join('pegawai','pegawai.id=jadwal_tambahan.id')
            ->where('jadwal_tambahan.tahun',$tahun)
            ->where('jadwal_tambahan.bulan',$bulan)
            ->where('departemen', $this->core->getPegawaiInfo('departemen',$username))
            ->where('bidang', $this->core->getPegawaiInfo('bidang',$username))
            ->like('pegawai.nama', '%'.$phrase.'%')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
        }
        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                $row['editURL'] = url([ADMIN, 'presensi', 'jadwaltambahedit', $row['id'] , $bulan , $tahun]);
                // $row['delURL']  = url([ADMIN, 'master', 'petugasdelete', $row['nip']]);
                // $row['restoreURL']  = url([ADMIN, 'master', 'petugasrestore', $row['nip']]);
                // $row['viewURL'] = url([ADMIN, 'master', 'petugasview', $row['nip']]);
                $this->assign['list'][] = $row;
            }
        }

        $this->assign['getStatus'] = isset($_GET['status']);
        $this->assign['addURL'] = url([ADMIN, 'presensi', 'jadwaltambahadd']);
        $month = array(
            '01' => 'JAN',
            '02' => 'FEB',
            '03' => 'MAR',
            '04' => 'APR',
            '05' => 'MEI',
            '06' => 'JUN',
            '07' => 'JUL',
            '08' => 'AGU',
            '09' => 'SEP',
            '10' => 'OKT',
            '11' => 'NOV',
            '12' => 'DES',
        );
        $this->assign['showBulan'] = $month[$bulan];
        // $this->assign['printURL'] = url([ADMIN, 'master', 'petugasprint']);
        $this->assign['bulan'] = array('','01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
        $this->assign['tahun'] = array('','2020', '2021', '2022');
        return $this->draw('jadwal_tambah.manage.html', ['jadwal_tambah' => $this->assign]);
    }

    public function getJadwalTambahAdd()
    {
        $this->_addHeaderFiles();
        if (!empty($redirectData = getRedirectData())){
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = [
                'id' => '',
                'tahun' => '',
                'bulan' => '',
                'h1' => '',
                'h2' => '',
                'h3' => '',
                'h4' => '',
                'h5' => '',
                'h6' => '',
                'h7' => '',
                'h8' => '',
                'h9' => '',
                'h10' => '',
                'h11' => '',
                'h12' => '',
                'h13' => '',
                'h14' => '',
                'h15' => '',
                'h16' => '',
                'h17' => '',
                'h18' => '',
                'h19' => '',
                'h20' => '',
                'h21' => '',
                'h22' => '',
                'h23' => '',
                'h24' => '',
                'h25' => '',
                'h26' => '',
                'h27' => '',
                'h28' => '',
                'h29' => '',
                'h30' => '',
                'h31' => '',
            ];
        }
        $username = $this->core->getUserInfo('username', null, true);
        if($this->core->getUserInfo('role') == 'admin'){
            $this->assign['id'] = $this->db('pegawai')
                                    ->where('stts_aktif','AKTIF')
                                    ->toArray();
        }else{
            $this->assign['id'] = $this->db('pegawai')
                                ->where('departemen', $this->core->getPegawaiInfo('departemen',$username))
                                ->where('bidang', $this->core->getPegawaiInfo('bidang',$username))
                                ->where('stts_aktif','AKTIF')
                                ->toArray();
        }
        if($this->core->getUserInfo('role') == 'admin'){
            $this->assign['h1'] = $this->db('jam_masuk')->toArray();
        }else{
            $this->assign['h1'] = $this->db('jam_jaga')->where('dep_id', $this->core->getPegawaiInfo('departemen',$username))->toArray();
        }
        $this->assign['tahun'] = date('Y');
        $this->assign['bulan'] = array('','01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');

        return $this->draw('jadwal_tambah.form.html', ['jadwal' => $this->assign]);
    }

    public function getJadwalTambahEdit($id,$bulan,$tahun)
    {
        $this->_addHeaderFiles();
        $row = $this->db('jadwal_tambahan')->where('id', $id)->where('tahun', $tahun)->where('bulan', $bulan)->oneArray();
        if (!empty($row)){
            $username = $this->core->getUserInfo('username', null, true);
            $this->assign['form'] = $row;
            if($this->core->getUserInfo('role') == 'admin'){
                $this->assign['id'] = $this->db('pegawai')
                                        ->where('stts_aktif','AKTIF')
                                        ->toArray();
            }else{
                $this->assign['id'] = $this->db('pegawai')
                                    ->where('departemen', $this->core->getPegawaiInfo('departemen',$username))
                                    ->where('bidang', $this->core->getPegawaiInfo('bidang',$username))
                                    ->where('stts_aktif','AKTIF')
                                    ->toArray();
            }
            $this->assign['h1'] = $this->db('jam_jaga')->where('dep_id', $this->core->getPegawaiInfo('departemen',$username))->toArray();
            $this->assign['tahun'] = $tahun;
            $this->assign['bulan'] = array('','01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');

            return $this->draw('jadwal_tambah.form.html', ['jadwal' => $this->assign]);
        } else {
            redirect(url([ADMIN,'presensi','jadwal_tambahan']));
        }

    }

    public function postJadwalTambahSave($id = null)
    {
        $errors = 0;

        if (!$id){
            $location = url([ADMIN, 'presensi', 'jadwal_tambahan']);
        } else {
            $location = url([ADMIN, 'presensi', 'jadwaltambahedit', $id]);
        }

        //if (checkEmptyFields(['id'], $_POST)){
        //    $this->notify('failure', 'Isian kosong');
        //    redirect($location, $_POST);
       // }

        if (!$errors) {
            unset($_POST['save']);

            if (!$id) {
                $query = $this->db('jadwal_tambahan')->save($_POST);
            } else {
                $query = $this->db('jadwal_tambahan')->where('id', $id)->where('tahun', $_POST['tahun'])->where('bulan', $_POST['bulan'])->save($_POST);
            }

            if ($query) {
                $this->notify('success', 'Simpan sukses');
            } else {
                $this->notify('failure', 'Simpan gagal');
            }

            redirect($location);
        }

        redirect($location, $_POST);
    }

    public function getRekap_Presensi($page = 1)
    {
        $this->_addHeaderFiles();
        $perpage = '10';
        $phrase = '';
        if(isset($_GET['s']))
          $phrase = $_GET['s'];

        $tgl_kunjungan = date('Y-m-d');
        $tgl_kunjungan_akhir = date('Y-m-d');
        // $status_periksa = '';
        // $status_bayar = '';

        if(isset($_GET['awal'])) {
        $tgl_kunjungan = $_GET['awal'];
        }
        if(isset($_GET['akhir'])) {
        $tgl_kunjungan_akhir = $_GET['akhir'];
        }

        $ruang = '';
        if (isset($_GET['ruang'])){
            $ruang = $_GET['ruang'];
        }

        $username = $this->core->getUserInfo('username', null, true);

        if($this->core->getUserInfo('role') == 'admin'){
            $totalRecords = $this->db('rekap_presensi')
                ->join('pegawai','pegawai.id = rekap_presensi.id')
                ->where('jam_datang', '>=', $tgl_kunjungan.' 00:00:00')
                ->where('jam_datang', '<=', $tgl_kunjungan_akhir.' 23:59:59')
                ->like('bidang','%'.$ruang.'%')
                ->like('nama', '%'.$phrase.'%')
                ->asc('jam_datang')
                ->toArray();
        }else{
            $totalRecords = $this->db('rekap_presensi')
                ->join('pegawai','pegawai.id = rekap_presensi.id')
                ->where('jam_datang', '>=', $tgl_kunjungan.' 00:00:00')
                ->where('jam_datang', '<=', $tgl_kunjungan_akhir.' 23:59:59')
                ->where('departemen', $this->core->getPegawaiInfo('departemen',$username))
                ->where('bidang', $this->core->getPegawaiInfo('bidang',$username))
                ->like('nama', '%'.$phrase.'%')
                ->asc('jam_datang')
                ->toArray();
        }
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'presensi', 'rekap_presensi', '%d?awal='.$tgl_kunjungan.'&akhir='.$tgl_kunjungan_akhir.'&ruang='.$ruang.'&s='.$phrase]));
        $this->assign['pagination'] = $pagination->nav('pagination','5');
        $this->assign['totalRecords'] = $totalRecords;

        // list
        $offset = $pagination->offset();

        if($this->core->getUserInfo('role') == 'admin'){
        $rows = $this->db('rekap_presensi')
            ->select([
              'nama' => 'pegawai.nama',
              'departemen' => 'pegawai.departemen',
              'jbtn' => 'pegawai.jbtn',
              'bidang' => 'pegawai.bidang',
              'id' => 'rekap_presensi.id',
              'shift' => 'rekap_presensi.shift',
              'jam_datang' => 'rekap_presensi.jam_datang',
              'jam_pulang' => 'rekap_presensi.jam_pulang',
              'status' => 'rekap_presensi.status',
              'durasi' => 'rekap_presensi.durasi',
              'photo' => 'rekap_presensi.photo'
            ])
            ->join('pegawai','pegawai.id = rekap_presensi.id')
            ->where('jam_datang', '>=', $tgl_kunjungan.' 00:00:00')
            ->where('jam_datang', '<=', $tgl_kunjungan_akhir.' 23:59:59')
            ->like('bidang','%'.$ruang.'%')
            ->like('nama', '%'.$phrase.'%')
            ->asc('jam_datang')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
        }else{
            $rows = $this->db('rekap_presensi')
            ->select([
              'nama' => 'pegawai.nama',
              'departemen' => 'pegawai.departemen',
              'jbtn' => 'pegawai.jbtn',
              'bidang' => 'pegawai.bidang',
              'id' => 'rekap_presensi.id',
              'shift' => 'rekap_presensi.shift',
              'jam_datang' => 'rekap_presensi.jam_datang',
              'jam_pulang' => 'rekap_presensi.jam_pulang',
              'status' => 'rekap_presensi.status',
              'durasi' => 'rekap_presensi.durasi',
              'photo' => 'rekap_presensi.photo'
            ])
            ->join('pegawai','pegawai.id = rekap_presensi.id')
            ->where('jam_datang', '>=', $tgl_kunjungan.' 00:00:00')
            ->where('jam_datang', '<=', $tgl_kunjungan_akhir.' 23:59:59')
            ->where('departemen', $this->core->getPegawaiInfo('departemen',$username))
            ->where('bidang', $this->core->getPegawaiInfo('bidang',$username))
            ->like('nama', '%'.$phrase.'%')
            ->asc('jam_datang')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
        }

        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                $row['mapURL']  = url([ADMIN, 'presensi', 'googlemap', $row['id'], date('Y-m-d', strtotime($row['jam_datang']))]);

                $day = array(
                    'Sun' => 'AKHAD',
                    'Mon' => 'SENIN',
                    'Tue' => 'SELASA',
                    'Wed' => 'RABU',
                    'Thu' => 'KAMIS',
                    'Fri' => 'JUMAT',
                    'Sat' => 'SABTU'
                );

                $jam_datang = $this->db('rekap_presensi')
                    ->select(['EXTRACT(MONTH FROM rekap_presensi.jam_datang) as month',
                    'EXTRACT(YEAR FROM rekap_presensi.jam_datang) as year',
                    'EXTRACT(DAY FROM rekap_presensi.jam_datang) as day',
                    'shift' => 'rekap_presensi.shift'])
                    ->join('pegawai','pegawai.id = rekap_presensi.id')
                    ->where('rekap_presensi.id',$row['id'])
                    ->where('rekap_presensi.jam_datang',$row['jam_datang'])
                    ->asc('jam_datang')
                    ->oneArray();
                $stts1 = '';
                $stts2 = '';

                $s = $row['jam_datang'];
                $dt = new \DateTime($s);
                $tm = $dt->format('h:i:s');

                $w = $row['jam_pulang'];
                $dd = new \DateTime($w);
                $tp = $dd->format('H:i:s');

                $row['date']=$day[date('D',strtotime(date($jam_datang['year'].'-'.$jam_datang['month'].'-'.$jam_datang['day'])))];
                switch (true) {
                    case ($row['date'] == 'SENIN' and $jam_datang['shift'] == 'Pagi'):
                        $interval = 'INTERVAL 6 * 60 + 30 MINUTE';
                        $efektif = 'INTERVAL 1 HOUR';
                        if ($tm > '08:11:00' and $tm < '08:30:00') {
                            $stts1 = 'TL1';
                        }elseif ($tm > '08:31:00' and $tm < '09:00:00') {
                            $stts1 = 'TL2';
                        }elseif ($tm > '09:01:00' and $tm < '09:30:00') {
                            $stts1 = 'TL3';
                        }elseif ($tm > '09:31:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '14:01:00' and $tp < '14:19:00') {
                            $stts2 = 'PSW1';
                        }elseif ($tp > '13:31:00' and $tp < '14:00:00') {
                            $stts2 = 'PSW2';
                        }elseif ($tp > '13:01:00' and $tp < '13:30:00') {
                            $stts2 = 'PSW3';
                        }elseif ($tp < '13:00:00') {
                            $stts2 = 'PSW4';
                        }
                        break;
                    case ($row['date'] == 'SENIN' and $jam_datang['shift'] == 'Siang'):
                        $interval = 'INTERVAL 5 HOUR';
                        $efektif = 'INTERVAL 1 HOUR';
                        break;
                        if ($tm > '14:41:00' and $tm < '15:00:00') {
                            $stts1 = 'TL1';
                        }elseif ($tm > '15:01:00' and $tm < '15:30:00') {
                            $stts1 = 'TL2';
                        }elseif ($tm > '15:31:00' and $tm < '16:00:00') {
                            $stts1 = 'TL3';
                        }elseif ($tm > '16:00:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '20:01:00' and $tp < '20:19:00') {
                            $stts2 = 'PSW1';
                        }elseif ($tp > '19:31:00' and $tp < '20:00:00') {
                            $stts2 = 'PSW2';
                        }elseif ($tp > '19:01:00' and $tp < '19:30:00') {
                            $stts2 = 'PSW3';
                        }elseif ($tp < '19:00:00') {
                            $stts2 = 'PSW4';
                        }
                    case ($row['date'] == 'SENIN' and $jam_datang['shift'] == 'Siang - Gizi'):
                        $interval = 'INTERVAL 0 HOUR';
                        $efektif = 'INTERVAL 0 HOUR';
                        break;
                    case ($row['date'] == 'SENIN' and $jam_datang['shift'] == 'Malam'):
                        $interval = 'INTERVAL 10 * 60 + 30 MINUTE';
                        $efektif = 'INTERVAL 2 HOUR';
                        if ($tm > '20:41:00' and $tm < '21:00:00') {
                            $stts1 = 'TL1';
                        }elseif ($tm > '21:01:00' and $tm < '21:30:00') {
                            $stts1 = 'TL2';
                        }elseif ($tm > '21:31:00' and $tm < '22:00:00') {
                            $stts1 = 'TL3';
                        }elseif ($tm > '22:01:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '07:31:00' and $tp < '07:49:00') {
                            $stts2 = 'PSW1';
                        }elseif ($tp > '07:01:00' and $tp < '07:30:00') {
                            $stts2 = 'PSW2';
                        }elseif ($tp > '06:31:00' and $tp < '07:00:00') {
                            $stts2 = 'PSW3';
                        }elseif ($tp < '06:30:00') {
                            $stts2 = 'PSW4';
                        }
                        break;
                    case ($row['date'] == 'SENIN' and $jam_datang['shift'] == 'Malam - Gizi (Masak)'):
                        $interval = 'INTERVAL 0 HOUR';
                        $efektif = 'INTERVAL 0 HOUR';
                        break;
                    case ($row['date'] == 'SENIN' and $jam_datang['shift'] == 'Malam - Gizi (Saji)'):
                        $interval = 'INTERVAL 0 HOUR';
                        $efektif = 'INTERVAL 0 HOUR';
                        break;
                    case ($row['date'] == 'SENIN' and $jam_datang['shift'] == 'Malam - Gizi (Cuci)'):
                        $interval = 'INTERVAL 0 HOUR';
                        $efektif = 'INTERVAL 0 HOUR';
                        break;
                    case ($row['date'] == 'SELASA' and $jam_datang['shift'] == 'Pagi'):
                        $interval = 'INTERVAL 6 * 60 + 30 MINUTE';
                        $efektif = 'INTERVAL 1 HOUR';
                        if ($tm > '08:11:00' and $tm < '08:30:00') {
                            $stts1 = 'TL1';
                        }elseif ($tm > '08:31:00' and $tm < '09:00:00') {
                            $stts1 = 'TL2';
                        }elseif ($tm > '09:01:00' and $tm < '09:30:00') {
                            $stts1 = 'TL3';
                        }elseif ($tm > '09:31:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '14:01:00' and $tp < '14:19:00') {
                            $stts2 = 'PSW1';
                        }elseif ($tp > '13:31:00' and $tp < '14:00:00') {
                            $stts2 = 'PSW2';
                        }elseif ($tp > '13:01:00' and $tp < '13:30:00') {
                            $stts2 = 'PSW3';
                        }elseif ($tp < '13:00:00') {
                            $stts2 = 'PSW4';
                        }
                        break;
                    case ($row['date'] == 'SELASA' and $jam_datang['shift'] == 'Siang'):
                        $interval = 'INTERVAL 5 HOUR';
                        $efektif = 'INTERVAL 1 HOUR';
                        if ($tm > '14:41:00' and $tm < '15:00:00') {
                            $stts1 = 'TL1';
                        }elseif ($tm > '15:01:00' and $tm < '15:30:00') {
                            $stts1 = 'TL2';
                        }elseif ($tm > '15:31:00' and $tm < '16:00:00') {
                            $stts1 = 'TL3';
                        }elseif ($tm > '16:00:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '20:01:00' and $tp < '20:19:00') {
                            $stts2 = 'PSW1';
                        }elseif ($tp > '19:31:00' and $tp < '20:00:00') {
                            $stts2 = 'PSW2';
                        }elseif ($tp > '19:01:00' and $tp < '19:30:00') {
                            $stts2 = 'PSW3';
                        }elseif ($tp < '19:00:00') {
                            $stts2 = 'PSW4';
                        }
                        break;
                    case ($row['date'] == 'SELASA' and $jam_datang['shift'] == 'Siang - Gizi'):
                        $interval = 'INTERVAL 0 HOUR';
                        $efektif = 'INTERVAL 0 HOUR';
                        break;
                    case ($row['date'] == 'SELASA' and $jam_datang['shift'] == 'Malam'):
                        $interval = 'INTERVAL 10 * 60 + 30 MINUTE';
                        $efektif = 'INTERVAL 2 HOUR';
                        if ($tm > '20:41:00' and $tm < '21:00:00') {
                            $stts1 = 'TL1';
                        }elseif ($tm > '21:01:00' and $tm < '21:30:00') {
                            $stts1 = 'TL2';
                        }elseif ($tm > '21:31:00' and $tm < '22:00:00') {
                            $stts1 = 'TL3';
                        }elseif ($tm > '22:01:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '07:31:00' and $tp < '07:49:00') {
                            $stts2 = 'PSW1';
                        }elseif ($tp > '07:01:00' and $tp < '07:30:00') {
                            $stts2 = 'PSW2';
                        }elseif ($tp > '06:31:00' and $tp < '07:00:00') {
                            $stts2 = 'PSW3';
                        }elseif ($tp < '06:30:00') {
                            $stts2 = 'PSW4';
                        }
                        break;
                    case ($row['date'] == 'SELASA' and $jam_datang['shift'] == 'Malam - Gizi (Masak)'):
                        $interval = 'INTERVAL 0 HOUR';
                        $efektif = 'INTERVAL 0 HOUR';
                        break;
                    case ($row['date'] == 'SELASA' and $jam_datang['shift'] == 'Malam - Gizi (Saji)'):
                        $interval = 'INTERVAL 0 HOUR';
                        $efektif = 'INTERVAL 0 HOUR';
                        break;
                    case ($row['date'] == 'SELASA' and $jam_datang['shift'] == 'Malam - Gizi (Cuci)'):
                        $interval = 'INTERVAL 0 HOUR';
                        $efektif = 'INTERVAL 0 HOUR';
                        break;
                    case ($row['date'] == 'RABU' and $jam_datang['shift'] == 'Pagi'):
                        $interval = 'INTERVAL 6 * 60 + 30 MINUTE';
                        $efektif = 'INTERVAL 1 HOUR';
                        if ($tm > '08:11:00' and $tm < '08:30:00') {
                            $stts1 = 'TL1';
                        }elseif ($tm > '08:31:00' and $tm < '09:00:00') {
                            $stts1 = 'TL2';
                        }elseif ($tm > '09:01:00' and $tm < '09:30:00') {
                            $stts1 = 'TL3';
                        }elseif ($tm > '09:31:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '14:01:00' and $tp < '14:19:00') {
                            $stts2 = 'PSW1';
                        }elseif ($tp > '13:31:00' and $tp < '14:00:00') {
                            $stts2 = 'PSW2';
                        }elseif ($tp > '13:01:00' and $tp < '13:30:00') {
                            $stts2 = 'PSW3';
                        }elseif ($tp < '13:00:00') {
                            $stts2 = 'PSW4';
                        }
                        break;
                    case ($row['date'] == 'RABU' and $jam_datang['shift'] == 'Siang'):
                        $interval = 'INTERVAL 5 HOUR';
                        $efektif = 'INTERVAL 1 HOUR';
                        if ($tm > '14:41:00' and $tm < '15:00:00') {
                            $stts1 = 'TL1';
                        }elseif ($tm > '15:01:00' and $tm < '15:30:00') {
                            $stts1 = 'TL2';
                        }elseif ($tm > '15:31:00' and $tm < '16:00:00') {
                            $stts1 = 'TL3';
                        }elseif ($tm > '16:00:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '20:01:00' and $tp < '20:19:00') {
                            $stts2 = 'PSW1';
                        }elseif ($tp > '19:31:00' and $tp < '20:00:00') {
                            $stts2 = 'PSW2';
                        }elseif ($tp > '19:01:00' and $tp < '19:30:00') {
                            $stts2 = 'PSW3';
                        }elseif ($tp < '19:00:00') {
                            $stts2 = 'PSW4';
                        }
                        break;
                    case ($row['date'] == 'RABU' and $jam_datang['shift'] == 'Siang - Gizi'):
                        $interval = 'INTERVAL 0 HOUR';
                        $efektif = 'INTERVAL 0 HOUR';
                        break;
                    case ($row['date'] == 'RABU' and $jam_datang['shift'] == 'Malam'):
                        $interval = 'INTERVAL 10 * 60 + 30 MINUTE';
                        $efektif = 'INTERVAL 2 HOUR';
                        if ($tm > '20:41:00' and $tm < '21:00:00') {
                            $stts1 = 'TL1';
                        }elseif ($tm > '21:01:00' and $tm < '21:30:00') {
                            $stts1 = 'TL2';
                        }elseif ($tm > '21:31:00' and $tm < '22:00:00') {
                            $stts1 = 'TL3';
                        }elseif ($tm > '22:01:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '07:31:00' and $tp < '07:49:00') {
                            $stts2 = 'PSW1';
                        }elseif ($tp > '07:01:00' and $tp < '07:30:00') {
                            $stts2 = 'PSW2';
                        }elseif ($tp > '06:31:00' and $tp < '07:00:00') {
                            $stts2 = 'PSW3';
                        }elseif ($tp < '06:30:00') {
                            $stts2 = 'PSW4';
                        }
                        break;
                    case ($row['date'] == 'RABU' and $jam_datang['shift'] == 'Malam - Gizi (Masak)'):
                        $interval = 'INTERVAL 0 HOUR';
                        $efektif = 'INTERVAL 0 HOUR';
                        break;
                    case ($row['date'] == 'RABU' and $jam_datang['shift'] == 'Malam - Gizi (Saji)'):
                        $interval = 'INTERVAL 0 HOUR';
                        $efektif = 'INTERVAL 0 HOUR';
                        break;
                    case ($row['date'] == 'RABU' and $jam_datang['shift'] == 'Malam - Gizi (Cuci)'):
                        $interval = 'INTERVAL 0 HOUR';
                        $efektif = 'INTERVAL 0 HOUR';
                        break;
                    case ($row['date'] == 'KAMIS' and $jam_datang['shift'] == 'Pagi'):
                        $interval = 'INTERVAL 6 * 60 + 30 MINUTE';
                        $efektif = 'INTERVAL 1 HOUR';
                        if ($tm > '08:11:00' and $tm < '08:30:00') {
                            $stts1 = 'TL1';
                        }elseif ($tm > '08:31:00' and $tm < '09:00:00') {
                            $stts1 = 'TL2';
                        }elseif ($tm > '09:01:00' and $tm < '09:30:00') {
                            $stts1 = 'TL3';
                        }elseif ($tm > '09:31:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '14:01:00' and $tp < '14:19:00') {
                            $stts2 = 'PSW1';
                        }elseif ($tp > '13:31:00' and $tp < '14:00:00') {
                            $stts2 = 'PSW2';
                        }elseif ($tp > '13:01:00' and $tp < '13:30:00') {
                            $stts2 = 'PSW3';
                        }elseif ($tp < '13:00:00') {
                            $stts2 = 'PSW4';
                        }
                        break;
                    case ($row['date'] == 'KAMIS' and $jam_datang['shift'] == 'Siang'):
                        $interval = 'INTERVAL 5 HOUR';
                        $efektif = 'INTERVAL 1 HOUR';
                        if ($tm > '14:41:00' and $tm < '15:00:00') {
                            $stts1 = 'TL1';
                        }elseif ($tm > '15:01:00' and $tm < '15:30:00') {
                            $stts1 = 'TL2';
                        }elseif ($tm > '15:31:00' and $tm < '16:00:00') {
                            $stts1 = 'TL3';
                        }elseif ($tm > '16:00:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '20:01:00' and $tp < '20:19:00') {
                            $stts2 = 'PSW1';
                        }elseif ($tp > '19:31:00' and $tp < '20:00:00') {
                            $stts2 = 'PSW2';
                        }elseif ($tp > '19:01:00' and $tp < '19:30:00') {
                            $stts2 = 'PSW3';
                        }elseif ($tp < '19:00:00') {
                            $stts2 = 'PSW4';
                        }
                        break;
                    case ($row['date'] == 'KAMIS' and $jam_datang['shift'] == 'Siang - Gizi'):
                        $interval = 'INTERVAL 0 HOUR';
                        $efektif = 'INTERVAL 0 HOUR';
                        break;
                    case ($row['date'] == 'KAMIS' and $jam_datang['shift'] == 'Malam'):
                        $interval = 'INTERVAL 10 * 60 + 30 MINUTE';
                        $efektif = 'INTERVAL 2 HOUR';
                        if ($tm > '20:41:00' and $tm < '21:00:00') {
                            $stts1 = 'TL1';
                        }elseif ($tm > '21:01:00' and $tm < '21:30:00') {
                            $stts1 = 'TL2';
                        }elseif ($tm > '21:31:00' and $tm < '22:00:00') {
                            $stts1 = 'TL3';
                        }elseif ($tm > '22:01:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '07:31:00' and $tp < '07:49:00') {
                            $stts2 = 'PSW1';
                        }elseif ($tp > '07:01:00' and $tp < '07:30:00') {
                            $stts2 = 'PSW2';
                        }elseif ($tp > '06:31:00' and $tp < '07:00:00') {
                            $stts2 = 'PSW3';
                        }elseif ($tp < '06:30:00') {
                            $stts2 = 'PSW4';
                        }
                        break;
                    case ($row['date'] == 'KAMIS' and $jam_datang['shift'] == 'Malam - Gizi (Masak)'):
                        $interval = 'INTERVAL 0 HOUR';
                        $efektif = 'INTERVAL 0 HOUR';
                        break;
                    case ($row['date'] == 'KAMIS' and $jam_datang['shift'] == 'Malam - Gizi (Saji)'):
                        $interval = 'INTERVAL 0 HOUR';
                        $efektif = 'INTERVAL 0 HOUR';
                        break;
                    case ($row['date'] == 'KAMIS' and $jam_datang['shift'] == 'Malam - Gizi (Cuci)'):
                        $interval = 'INTERVAL 0 HOUR';
                        $efektif = 'INTERVAL 0 HOUR';
                        break;
                    case ($row['date'] == 'JUMAT' and $jam_datang['shift'] == 'Pagi'):
                        $interval = 'INTERVAL 3 HOUR';
                        $efektif = 'INTERVAL 30 MINUTE';
                        if ($tm > '08:11:00' and $tm < '08:30:00') {
                            $stts1 = 'TL1';
                        }elseif ($tm > '08:31:00' and $tm < '09:00:00') {
                            $stts1 = 'TL2';
                        }elseif ($tm > '09:01:00' and $tm < '09:30:00') {
                            $stts1 = 'TL3';
                        }elseif ($tm > '09:31:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '10:30:00' and $tp < '10:49:00') {
                            $stts2 = 'PSW1';
                        }elseif ($tp > '10:01:00' and $tp < '10:30:00') {
                            $stts2 = 'PSW2';
                        }elseif ($tp > '09:30:00' and $tp < '10:00:00') {
                            $stts2 = 'PSW3';
                        }elseif ($tp < '09:29:00') {
                            $stts2 = 'PSW4';
                        }
                        break;
                    case ($row['date'] == 'JUMAT' and $jam_datang['shift'] == 'Siang'):
                        $interval = 'INTERVAL 5 HOUR';
                        $efektif = 'INTERVAL 1 HOUR';
                        if ($tm > '14:41:00' and $tm < '15:00:00') {
                            $stts1 = 'TL1';
                        }elseif ($tm > '15:01:00' and $tm < '15:30:00') {
                            $stts1 = 'TL2';
                        }elseif ($tm > '15:31:00' and $tm < '16:00:00') {
                            $stts1 = 'TL3';
                        }elseif ($tm > '16:00:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '20:01:00' and $tp < '20:19:00') {
                            $stts2 = 'PSW1';
                        }elseif ($tp > '19:31:00' and $tp < '20:00:00') {
                            $stts2 = 'PSW2';
                        }elseif ($tp > '19:01:00' and $tp < '19:30:00') {
                            $stts2 = 'PSW3';
                        }elseif ($tp < '19:00:00') {
                            $stts2 = 'PSW4';
                        }
                        break;
                    case ($row['date'] == 'JUMAT' and $jam_datang['shift'] == 'Siang - Gizi'):
                        $interval = 'INTERVAL 0 HOUR';
                        $efektif = 'INTERVAL 0 HOUR';
                        break;
                    case ($row['date'] == 'JUMAT' and $jam_datang['shift'] == 'Malam'):
                        $interval = 'INTERVAL 10 * 60 + 30 MINUTE';
                        $efektif = 'INTERVAL 2 HOUR';
                        if ($tm > '20:41:00' and $tm < '21:00:00') {
                            $stts1 = 'TL1';
                        }elseif ($tm > '21:01:00' and $tm < '21:30:00') {
                            $stts1 = 'TL2';
                        }elseif ($tm > '21:31:00' and $tm < '22:00:00') {
                            $stts1 = 'TL3';
                        }elseif ($tm > '22:01:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '07:31:00' and $tp < '07:49:00') {
                            $stts2 = 'PSW1';
                        }elseif ($tp > '07:01:00' and $tp < '07:30:00') {
                            $stts2 = 'PSW2';
                        }elseif ($tp > '06:31:00' and $tp < '07:00:00') {
                            $stts2 = 'PSW3';
                        }elseif ($tp < '06:30:00') {
                            $stts2 = 'PSW4';
                        }
                        break;
                    case ($row['date'] == 'JUMAT' and $jam_datang['shift'] == 'Malam - Gizi (Masak)'):
                        $interval = 'INTERVAL 0 HOUR';
                        $efektif = 'INTERVAL 0 HOUR';
                        break;
                    case ($row['date'] == 'JUMAT' and $jam_datang['shift'] == 'Malam - Gizi (Saji)'):
                        $interval = 'INTERVAL 0 HOUR';
                        $efektif = 'INTERVAL 0 HOUR';
                        break;
                    case ($row['date'] == 'JUMAT' and $jam_datang['shift'] == 'Malam - Gizi (Cuci)'):
                        $interval = 'INTERVAL 0 HOUR';
                        $efektif = 'INTERVAL 0 HOUR';
                        break;
                    case ($row['date'] == 'SABTU' and $jam_datang['shift'] == 'Pagi'):
                        $interval = 'INTERVAL 5 * 60 + 30 MINUTE';
                        $efektif = 'INTERVAL 1 HOUR';
                        if ($tm > '08:11:00' and $tm < '08:30:00') {
                            $stts1 = 'TL1';
                        }elseif ($tm > '08:31:00' and $tm < '09:00:00') {
                            $stts1 = 'TL2';
                        }elseif ($tm > '09:01:00' and $tm < '09:30:00') {
                            $stts1 = 'TL3';
                        }elseif ($tm > '09:31:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '13:00:00' and $tp < '13:19:00') {
                            $stts2 = 'PSW1';
                        }elseif ($tp > '13:31:00' and $tp < '14:00:00') {
                            $stts2 = 'PSW2';
                        }elseif ($tp > '13:01:00' and $tp < '13:30:00') {
                            $stts2 = 'PSW3';
                        }elseif ($tp < '13:00:00') {
                            $stts2 = 'PSW4';
                        }
                        break;
                    case ($row['date'] == 'SABTU' and $jam_datang['shift'] == 'Siang'):
                        $interval = 'INTERVAL 5 HOUR';
                        $efektif = 'INTERVAL 1 HOUR';
                        if ($tm > '14:41:00' and $tm < '15:00:00') {
                            $stts1 = 'TL1';
                        }elseif ($tm > '15:01:00' and $tm < '15:30:00') {
                            $stts1 = 'TL2';
                        }elseif ($tm > '15:31:00' and $tm < '16:00:00') {
                            $stts1 = 'TL3';
                        }elseif ($tm > '16:00:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '20:01:00' and $tp < '20:19:00') {
                            $stts2 = 'PSW1';
                        }elseif ($tp > '19:31:00' and $tp < '20:00:00') {
                            $stts2 = 'PSW2';
                        }elseif ($tp > '19:01:00' and $tp < '19:30:00') {
                            $stts2 = 'PSW3';
                        }elseif ($tp < '19:00:00') {
                            $stts2 = 'PSW4';
                        }
                        break;
                    case ($row['date'] == 'SABTU' and $jam_datang['shift'] == 'Siang - Gizi'):
                        $interval = 'INTERVAL 0 HOUR';
                        $efektif = 'INTERVAL 0 HOUR';
                        break;
                    case ($row['date'] == 'SABTU' and $jam_datang['shift'] == 'Malam'):
                        $interval = 'INTERVAL 10 * 60 + 30 MINUTE';
                        $efektif = 'INTERVAL 2 HOUR';
                        if ($tm > '20:41:00' and $tm < '21:00:00') {
                            $stts1 = 'TL1';
                        }elseif ($tm > '21:01:00' and $tm < '21:30:00') {
                            $stts1 = 'TL2';
                        }elseif ($tm > '21:31:00' and $tm < '22:00:00') {
                            $stts1 = 'TL3';
                        }elseif ($tm > '22:01:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '07:31:00' and $tp < '07:49:00') {
                            $stts2 = 'PSW1';
                        }elseif ($tp > '07:01:00' and $tp < '07:30:00') {
                            $stts2 = 'PSW2';
                        }elseif ($tp > '06:31:00' and $tp < '07:00:00') {
                            $stts2 = 'PSW3';
                        }elseif ($tp < '06:30:00') {
                            $stts2 = 'PSW4';
                        }
                        break;
                    case ($row['date'] == 'SABTU' and $jam_datang['shift'] == 'Malam - Gizi (Masak)'):
                        $interval = 'INTERVAL 0 HOUR';
                        $efektif = 'INTERVAL 0 HOUR';
                        break;
                    case ($row['date'] == 'SABTU' and $jam_datang['shift'] == 'Malam - Gizi (Saji)'):
                        $interval = 'INTERVAL 0 HOUR';
                        $efektif = 'INTERVAL 0 HOUR';
                        break;
                    case ($row['date'] == 'SABTU' and $jam_datang['shift'] == 'Malam - Gizi (Cuci)'):
                        $interval = 'INTERVAL 0 HOUR';
                        $efektif = 'INTERVAL 0 HOUR';
                        break;
                    case ($row['date'] == 'AKHAD' and $jam_datang['shift'] == 'Pagi'):
                        $interval = 'INTERVAL 6 * 60 + 30 MINUTE';
                        $efektif = 'INTERVAL 1 HOUR';
                        if ($tm > '08:11:00' and $tm < '08:30:00') {
                            $stts1 = 'TL1';
                        }elseif ($tm > '08:31:00' and $tm < '09:00:00') {
                            $stts1 = 'TL2';
                        }elseif ($tm > '09:01:00' and $tm < '09:30:00') {
                            $stts1 = 'TL3';
                        }elseif ($tm > '09:31:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '14:01:00' and $tp < '14:19:00') {
                            $stts2 = 'PSW1';
                        }elseif ($tp > '13:31:00' and $tp < '14:00:00') {
                            $stts2 = 'PSW2';
                        }elseif ($tp > '13:01:00' and $tp < '13:30:00') {
                            $stts2 = 'PSW3';
                        }elseif ($tp < '13:00:00') {
                            $stts2 = 'PSW4';
                        }
                        break;
                    case ($row['date'] == 'AKHAD' and $jam_datang['shift'] == 'Siang'):
                        $interval = 'INTERVAL 5 HOUR';
                        $efektif = 'INTERVAL 1 HOUR';
                        if ($tm > '14:41:00' and $tm < '15:00:00') {
                            $stts1 = 'TL1';
                        }elseif ($tm > '15:01:00' and $tm < '15:30:00') {
                            $stts1 = 'TL2';
                        }elseif ($tm > '15:31:00' and $tm < '16:00:00') {
                            $stts1 = 'TL3';
                        }elseif ($tm > '16:00:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '20:01:00' and $tp < '20:19:00') {
                            $stts2 = 'PSW1';
                        }elseif ($tp > '19:31:00' and $tp < '20:00:00') {
                            $stts2 = 'PSW2';
                        }elseif ($tp > '19:01:00' and $tp < '19:30:00') {
                            $stts2 = 'PSW3';
                        }elseif ($tp < '19:00:00') {
                            $stts2 = 'PSW4';
                        }
                        break;
                    case ($row['date'] == 'AKHAD' and $jam_datang['shift'] == 'Siang - Gizi'):
                        $interval = 'INTERVAL 0 HOUR';
                        $efektif = 'INTERVAL 0 HOUR';
                        break;
                    case ($row['date'] == 'AKHAD' and $jam_datang['shift'] == 'Malam'):
                        $interval = 'INTERVAL 10 * 60 + 30 MINUTE';
                        $efektif = 'INTERVAL 2 HOUR';
                        if ($tm > '20:41:00' and $tm < '21:00:00') {
                            $stts1 = 'TL1';
                        }elseif ($tm > '21:01:00' and $tm < '21:30:00') {
                            $stts1 = 'TL2';
                        }elseif ($tm > '21:31:00' and $tm < '22:00:00') {
                            $stts1 = 'TL3';
                        }elseif ($tm > '22:01:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '07:31:00' and $tp < '07:49:00') {
                            $stts2 = 'PSW1';
                        }elseif ($tp > '07:01:00' and $tp < '07:30:00') {
                            $stts2 = 'PSW2';
                        }elseif ($tp > '06:31:00' and $tp < '07:00:00') {
                            $stts2 = 'PSW3';
                        }elseif ($tp < '06:30:00') {
                            $stts2 = 'PSW4';
                        }
                        break;
                    case ($row['date'] == 'AKHAD' and $jam_datang['shift'] == 'Malam - Gizi (Masak)'):
                        $interval = 'INTERVAL 0 HOUR';
                        $efektif = 'INTERVAL 0 HOUR';
                        break;
                    case ($row['date'] == 'AKHAD' and $jam_datang['shift'] == 'Malam - Gizi (Saji)'):
                        $interval = 'INTERVAL 0 HOUR';
                        $efektif = 'INTERVAL 0 HOUR';
                        break;
                    case ($row['date'] == 'AKHAD' and $jam_datang['shift'] == 'Malam - Gizi (Cuci)'):
                        $interval = 'INTERVAL 0 HOUR';
                        $efektif = 'INTERVAL 0 HOUR';
                        break;
                    default:
                        $interval = 'INTERVAL 5 * 60 + 30 MINUTE';
                        $efektif = 'INTERVAL 1 HOUR';
                        break;
                }

                $row['efektif'] = $this->db('rekap_presensi')
                        ->select([
                        'efektif' => 'CAST(rekap_presensi.durasi as TIME) - '.$efektif,
                        'kurang' => 'CAST(rekap_presensi.durasi as TIME) - '.$interval])
                        ->where('rekap_presensi.id',$row['id'])
                        ->where('rekap_presensi.jam_datang',$row['jam_datang'])
                        ->oneArray();
                $row['stts1'] = $stts1;
                $row['stts2'] = $stts2;
                $this->assign['list'][] = $row;
            }

        }

        $secondplus = 0;
        $secondminus = 0;
        foreach ($this->assign['list'] as $time) {
            list($hour,$minute,$second) = explode(':',$time['efektif']['kurang']);
            if (strpos($hour, '-') !== false) {
                $hour = 0 - $hour;
                $secondplus += $hour*3600;
                $secondplus += $minute*60;
                $secondplus += $second;
            }else{
                $secondminus += $hour*3600;
                $secondminus += $minute*60;
                $secondminus += $second;
            }
        }

        $hours = floor($secondplus/3600);
        $secondplus -= $hours*3600;
        $minutes = floor($secondplus/60);
        $secondplus -= $minutes*60;
        $timesplus = $hours.':'.$minutes.':'.$secondplus;

        $hours = floor($secondminus/3600);
        $secondminus -= $hours*3600;
        $minutes = floor($secondminus/60);
        $secondminus -= $minutes*60;
        $timesminus = $hours.':'.$minutes.':'.$secondminus;


        $this->assign['totalminus'] = '-'.$timesplus;

        $this->assign['totalplus'] = $timesminus;

        // $this->assign['stts1'] = $stts1;
        // $this->assign['stts2'] = $stts2;
        $this->assign['getStatus'] = isset($_GET['status']);
        $this->assign['tahun'] = array('','2020', '2021', '2022');
        $this->assign['bulan'] = array('','01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
        $this->assign['tanggal'] = array('','01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30' ,'31');
        $this->assign['bidang'] = $this->db('bidang')->toArray();
        //$this->assign['printURL'] = url([ADMIN, 'presensi', 'cetakrekap','?b='.$bulan.'&y='.$tahun.'&s='.$phrase]);
        return $this->draw('rekap_presensi.html', ['rekap' => $this->assign]);
    }

    public function getCetakRekap()
    {
        $phrase = '';
        if(isset($_GET['s']))
          $phrase = $_GET['s'];

        $bulan = date('m');
        if (isset($_GET['b'])) {
            $bulan = $_GET['b'];
        }

        $total = [];
        $count = 0;

        $day = array(
            'Sun' => 'AKHAD',
            'Mon' => 'SENIN',
            'Tue' => 'SELASA',
            'Wed' => 'RABU',
            'Thu' => 'KAMIS',
            'Fri' => 'JUMAT',
            'Sat' => 'SABTU'
        );

        $pasien = $this->db('rekap_presensi')
        ->select([
            'nama' => 'pegawai.nama',
            'departemen' => 'pegawai.departemen',
            'id' => 'rekap_presensi.id',
            'shift' => 'rekap_presensi.shift',
            'jam_datang' => 'rekap_presensi.jam_datang',
            'jam_pulang' => 'rekap_presensi.jam_pulang',
            'status' => 'rekap_presensi.status',
            'durasi' => 'rekap_presensi.durasi',
            'photo' => 'rekap_presensi.photo',
            'EXTRACT(MONTH FROM rekap_presensi.jam_datang) as month',
            'EXTRACT(YEAR FROM rekap_presensi.jam_datang) as year',
            'EXTRACT(DAY FROM rekap_presensi.jam_datang) as day',
          ])
          ->join('pegawai','pegawai.id = rekap_presensi.id')
          ->where('jam_datang', '>', date('Y-'.$bulan).'-01')
          ->where('jam_datang', '<', date('Y-'.$bulan).'-31')
          ->like('nama', '%'.$phrase.'%')
          ->asc('jam_datang')
          ->toArray();

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
        $pdf->Text(30, 21, $this->core->getSettings('alamat').' - '.$this->core->getSettings('kota'));
        $pdf->Text(30, 25, $this->core->getSettings('nomor_telepon').' - '.$this->core->getSettings('email'));
        $pdf->Line(10, 30, 200, 30);
        $pdf->Line(10, 31, 200, 31);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Text(10, 40, 'REKAP PRESENSI ');
        $pdf->Ln(34);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetWidths(array(50,15,35,35,18,18,18));
        $pdf->Row(array('Nama Pegawai','Shift','Jam Datang', 'Jam Pulang', 'Durasi' , 'Efektif', 'Selisih'));
        $pdf->SetFont('Arial', '', 10);
        foreach ($pasien as $hasil) {
            $row['date']=$day[date('D',strtotime(date($hasil['year'].'-'.$hasil['month'].'-'.$hasil['day'])))];
            switch (true) {
                case ($row['date'] == 'SENIN' and $hasil['shift'] == 'Pagi'):
                    $interval = 'INTERVAL 6 * 60 + 30 MINUTE';
                    $efektif = 'INTERVAL 1 HOUR';
                    break;
                case ($row['date'] == 'SENIN' and $hasil['shift'] == 'Siang'):
                    $interval = 'INTERVAL 5 HOUR';
                    $efektif = 'INTERVAL 1 HOUR';
                    break;
                case ($row['date'] == 'SENIN' and $hasil['shift'] == 'Siang - Gizi'):
                    $interval = 'INTERVAL 0 HOUR';
                    $efektif = 'INTERVAL 0 HOUR';
                    break;
                case ($row['date'] == 'SENIN' and $hasil['shift'] == 'Malam'):
                    $interval = 'INTERVAL 10 * 60 + 30 MINUTE';
                    $efektif = 'INTERVAL 2 HOUR';
                    break;
                case ($row['date'] == 'SENIN' and $hasil['shift'] == 'Malam - Gizi (Masak)'):
                    $interval = 'INTERVAL 0 HOUR';
                    $efektif = 'INTERVAL 0 HOUR';
                    break;
                case ($row['date'] == 'SENIN' and $hasil['shift'] == 'Malam - Gizi (Saji)'):
                    $interval = 'INTERVAL 0 HOUR';
                    $efektif = 'INTERVAL 0 HOUR';
                    break;
                case ($row['date'] == 'SENIN' and $hasil['shift'] == 'Malam - Gizi (Cuci)'):
                    $interval = 'INTERVAL 0 HOUR';
                    $efektif = 'INTERVAL 0 HOUR';
                    break;
                case ($row['date'] == 'SELASA' and $hasil['shift'] == 'Pagi'):
                    $interval = 'INTERVAL 6 * 60 + 30 MINUTE';
                    $efektif = 'INTERVAL 1 HOUR';
                    break;
                case ($row['date'] == 'SELASA' and $hasil['shift'] == 'Siang'):
                    $interval = 'INTERVAL 5 HOUR';
                    $efektif = 'INTERVAL 1 HOUR';
                    break;
                case ($row['date'] == 'SELASA' and $hasil['shift'] == 'Siang - Gizi'):
                    $interval = 'INTERVAL 0 HOUR';
                    $efektif = 'INTERVAL 0 HOUR';
                    break;
                case ($row['date'] == 'SELASA' and $hasil['shift'] == 'Malam'):
                    $interval = 'INTERVAL 10 * 60 + 30 MINUTE';
                    $efektif = 'INTERVAL 2 HOUR';
                    break;
                case ($row['date'] == 'SELASA' and $hasil['shift'] == 'Malam - Gizi (Masak)'):
                    $interval = 'INTERVAL 0 HOUR';
                    $efektif = 'INTERVAL 0 HOUR';
                    break;
                case ($row['date'] == 'SELASA' and $hasil['shift'] == 'Malam - Gizi (Saji)'):
                    $interval = 'INTERVAL 0 HOUR';
                    $efektif = 'INTERVAL 0 HOUR';
                    break;
                case ($row['date'] == 'SELASA' and $hasil['shift'] == 'Malam - Gizi (Cuci)'):
                    $interval = 'INTERVAL 0 HOUR';
                    $efektif = 'INTERVAL 0 HOUR';
                    break;
                case ($row['date'] == 'RABU' and $hasil['shift'] == 'Pagi'):
                    $interval = 'INTERVAL 6 * 60 + 30 MINUTE';
                    $efektif = 'INTERVAL 1 HOUR';
                    break;
                case ($row['date'] == 'RABU' and $hasil['shift'] == 'Siang'):
                    $interval = 'INTERVAL 5 HOUR';
                    $efektif = 'INTERVAL 1 HOUR';
                    break;
                case ($row['date'] == 'RABU' and $hasil['shift'] == 'Siang - Gizi'):
                    $interval = 'INTERVAL 0 HOUR';
                    $efektif = 'INTERVAL 0 HOUR';
                    break;
                case ($row['date'] == 'RABU' and $hasil['shift'] == 'Malam'):
                    $interval = 'INTERVAL 10 * 60 + 30 MINUTE';
                    $efektif = 'INTERVAL 2 HOUR';
                    break;
                case ($row['date'] == 'RABU' and $hasil['shift'] == 'Malam - Gizi (Masak)'):
                    $interval = 'INTERVAL 0 HOUR';
                    $efektif = 'INTERVAL 0 HOUR';
                    break;
                case ($row['date'] == 'RABU' and $hasil['shift'] == 'Malam - Gizi (Saji)'):
                    $interval = 'INTERVAL 0 HOUR';
                    $efektif = 'INTERVAL 0 HOUR';
                    break;
                case ($row['date'] == 'RABU' and $hasil['shift'] == 'Malam - Gizi (Cuci)'):
                    $interval = 'INTERVAL 0 HOUR';
                    $efektif = 'INTERVAL 0 HOUR';
                    break;
                case ($row['date'] == 'KAMIS' and $hasil['shift'] == 'Pagi'):
                    $interval = 'INTERVAL 6 * 60 + 30 MINUTE';
                    $efektif = 'INTERVAL 1 HOUR';
                    break;
                case ($row['date'] == 'KAMIS' and $hasil['shift'] == 'Siang'):
                    $interval = 'INTERVAL 5 HOUR';
                    $efektif = 'INTERVAL 1 HOUR';
                    break;
                case ($row['date'] == 'KAMIS' and $hasil['shift'] == 'Siang - Gizi'):
                    $interval = 'INTERVAL 0 HOUR';
                    $efektif = 'INTERVAL 0 HOUR';
                    break;
                case ($row['date'] == 'KAMIS' and $hasil['shift'] == 'Malam'):
                    $interval = 'INTERVAL 10 * 60 + 30 MINUTE';
                    $efektif = 'INTERVAL 2 HOUR';
                    break;
                case ($row['date'] == 'KAMIS' and $hasil['shift'] == 'Malam - Gizi (Masak)'):
                    $interval = 'INTERVAL 0 HOUR';
                    $efektif = 'INTERVAL 0 HOUR';
                    break;
                case ($row['date'] == 'KAMIS' and $hasil['shift'] == 'Malam - Gizi (Saji)'):
                    $interval = 'INTERVAL 0 HOUR';
                    $efektif = 'INTERVAL 0 HOUR';
                    break;
                case ($row['date'] == 'KAMIS' and $hasil['shift'] == 'Malam - Gizi (Cuci)'):
                    $interval = 'INTERVAL 0 HOUR';
                    $efektif = 'INTERVAL 0 HOUR';
                    break;
                case ($row['date'] == 'JUMAT' and $hasil['shift'] == 'Pagi'):
                    $interval = 'INTERVAL 3 HOUR';
                    $efektif = 'INTERVAL 30 MINUTE';
                    break;
                case ($row['date'] == 'JUMAT' and $hasil['shift'] == 'Siang'):
                    $interval = 'INTERVAL 5 HOUR';
                    $efektif = 'INTERVAL 1 HOUR';
                    break;
                case ($row['date'] == 'JUMAT' and $hasil['shift'] == 'Siang - Gizi'):
                    $interval = 'INTERVAL 0 HOUR';
                    $efektif = 'INTERVAL 0 HOUR';
                    break;
                case ($row['date'] == 'JUMAT' and $hasil['shift'] == 'Malam'):
                    $interval = 'INTERVAL 10 * 60 + 30 MINUTE';
                    $efektif = 'INTERVAL 2 HOUR';
                    break;
                case ($row['date'] == 'JUMAT' and $hasil['shift'] == 'Malam - Gizi (Masak)'):
                    $interval = 'INTERVAL 0 HOUR';
                    $efektif = 'INTERVAL 0 HOUR';
                    break;
                case ($row['date'] == 'JUMAT' and $hasil['shift'] == 'Malam - Gizi (Saji)'):
                    $interval = 'INTERVAL 0 HOUR';
                    $efektif = 'INTERVAL 0 HOUR';
                    break;
                case ($row['date'] == 'JUMAT' and $hasil['shift'] == 'Malam - Gizi (Cuci)'):
                    $interval = 'INTERVAL 0 HOUR';
                    $efektif = 'INTERVAL 0 HOUR';
                    break;
                case ($row['date'] == 'SABTU' and $hasil['shift'] == 'Pagi'):
                    $interval = 'INTERVAL 5 * 60 + 30 MINUTE';
                    $efektif = 'INTERVAL 1 HOUR';
                    break;
                case ($row['date'] == 'SABTU' and $hasil['shift'] == 'Siang'):
                    $interval = 'INTERVAL 5 HOUR';
                    $efektif = 'INTERVAL 1 HOUR';
                    break;
                case ($row['date'] == 'SABTU' and $hasil['shift'] == 'Siang - Gizi'):
                    $interval = 'INTERVAL 0 HOUR';
                    $efektif = 'INTERVAL 0 HOUR';
                    break;
                case ($row['date'] == 'SABTU' and $hasil['shift'] == 'Malam'):
                    $interval = 'INTERVAL 10 * 60 + 30 MINUTE';
                    $efektif = 'INTERVAL 2 HOUR';
                    break;
                case ($row['date'] == 'SABTU' and $hasil['shift'] == 'Malam - Gizi (Masak)'):
                    $interval = 'INTERVAL 0 HOUR';
                    $efektif = 'INTERVAL 0 HOUR';
                    break;
                case ($row['date'] == 'SABTU' and $hasil['shift'] == 'Malam - Gizi (Saji)'):
                    $interval = 'INTERVAL 0 HOUR';
                    $efektif = 'INTERVAL 0 HOUR';
                    break;
                case ($row['date'] == 'SABTU' and $hasil['shift'] == 'Malam - Gizi (Cuci)'):
                    $interval = 'INTERVAL 0 HOUR';
                    $efektif = 'INTERVAL 0 HOUR';
                    break;
                case ($row['date'] == 'AKHAD' and $hasil['shift'] == 'Pagi'):
                    $interval = 'INTERVAL 6 * 60 + 30 MINUTE';
                    $efektif = 'INTERVAL 1 HOUR';
                    break;
                case ($row['date'] == 'AKHAD' and $hasil['shift'] == 'Siang'):
                    $interval = 'INTERVAL 5 HOUR';
                    $efektif = 'INTERVAL 1 HOUR';
                    break;
                case ($row['date'] == 'AKHAD' and $hasil['shift'] == 'Siang - Gizi'):
                    $interval = 'INTERVAL 0 HOUR';
                    $efektif = 'INTERVAL 0 HOUR';
                    break;
                case ($row['date'] == 'AKHAD' and $hasil['shift'] == 'Malam'):
                    $interval = 'INTERVAL 10 * 60 + 30 MINUTE';
                    $efektif = 'INTERVAL 2 HOUR';
                    break;
                case ($row['date'] == 'AKHAD' and $hasil['shift'] == 'Malam - Gizi (Masak)'):
                    $interval = 'INTERVAL 0 HOUR';
                    $efektif = 'INTERVAL 0 HOUR';
                    break;
                case ($row['date'] == 'AKHAD' and $hasil['shift'] == 'Malam - Gizi (Saji)'):
                    $interval = 'INTERVAL 0 HOUR';
                    $efektif = 'INTERVAL 0 HOUR';
                    break;
                case ($row['date'] == 'AKHAD' and $hasil['shift'] == 'Malam - Gizi (Cuci)'):
                    $interval = 'INTERVAL 0 HOUR';
                    $efektif = 'INTERVAL 0 HOUR';
                    break;
                default:
                    $interval = 'INTERVAL 5 * 60 + 30 MINUTE';
                    $efektif = 'INTERVAL 1 HOUR';
                    break;
            }
            $hasil['efektif'] = $this->db('rekap_presensi')
                        ->select([
                        'efektif' => 'CAST(rekap_presensi.durasi as TIME) - '.$efektif,
                        'kurang' => 'CAST(rekap_presensi.durasi as TIME) - '.$interval])
                        ->where('rekap_presensi.id',$hasil['id'])
                        ->where('rekap_presensi.jam_datang',$hasil['jam_datang'])
                        ->oneArray();
            $total[] = $hasil;
            $count++;
          $pdf->Row(array($hasil['nama'],$hasil['shift'],$hasil['jam_datang'],$hasil['jam_pulang'],$hasil['durasi'],$hasil['efektif']['efektif'],$hasil['efektif']['kurang']));
        }

        $secondplus = 0;
        $secondminus = 0;
        foreach ($total as $time) {
            list($hour,$minute,$second) = explode(':',$time['efektif']['kurang']);
            if (strpos($hour, '-') !== false) {
                $hour = 0 - $hour;
                $secondplus += $hour*3600;
                $secondplus += $minute*60;
                $secondplus += $second;
            }else{
                $secondminus += $hour*3600;
                $secondminus += $minute*60;
                $secondminus += $second;
            }
        }
        $hours = floor($secondplus/3600);
        $secondplus -= $hours*3600;
        $minutes = floor($secondplus/60);
        $secondplus -= $minutes*60;
        $timesplus = $hours.':'.$minutes.':'.$secondplus;

        $hours = floor($secondminus/3600);
        $secondminus -= $hours*3600;
        $minutes = floor($secondminus/60);
        $secondminus -= $minutes*60;
        $timesminus = $hours.':'.$minutes.':'.$secondminus;

        $pdf->Ln(34);
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetAligns(array("C", "C" , "C"));
        $pdf->SetWidths(array(63, 63, 63));
        $pdf->Row(array("Kelebihan Jam Kerja: ".$timesminus, "Kekurangan Jam Kerja: -".$timesplus, "Jumlah Hari: ".$count), array("", "", ""), 1);

        $pdf->Output('laporan_presensi_'.date('Y-m-d').'.pdf','I');

    }

    public function getPrint($phrase = null, $tanggal = null)
    {
      $phrase = '';
      if(isset($_GET['s']))
        $phrase = $_GET['s'];
      $tanggal = '';
      if(isset($_GET['tanggal']))
        $tanggal = $_GET['tanggal'];

      $rekap_presensi = $this->db('rekap_presensi')
          ->select([
            'nama' => 'pegawai.nama',
            'departemen' => 'pegawai.departemen',
            'id' => 'rekap_presensi.id',
            'shift' => 'rekap_presensi.shift',
            'jam_datang' => 'rekap_presensi.jam_datang',
            'jam_pulang' => 'rekap_presensi.jam_pulang',
            'status' => 'rekap_presensi.status',
            'durasi' => 'rekap_presensi.durasi',
            'photo' => 'rekap_presensi.photo'
          ])
          ->join('pegawai','pegawai.id = rekap_presensi.id')
          ->like('jam_datang', $tanggal.'%')
          ->like('nama', '%'.$phrase.'%')
          ->orLike('shift', '%'.$phrase.'%')
          ->asc('jam_datang')
          ->toArray();
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
      $pdf->Text(30, 21, $this->core->getSettings('alamat').' - '.$this->core->getSettings('kota'));
      $pdf->Text(30, 25, $this->core->getSettings('nomor_telepon').' - '.$this->core->getSettings('email'));
      $pdf->Line(10, 30, 350, 30);
      $pdf->Line(10, 31, 350, 31);
      $pdf->Text(10, 40, 'DATA REKAP PRESENSI PEGAWAI');
      $pdf->Ln(34);
      $pdf->SetFont('Arial', '', 10);
      $pdf->SetWidths(array(110,30,40,40,40,40,40));
      $pdf->Row(array('Nama Pegawai','Shift','Jam Datang', 'Jam Pulang', 'Durasi', 'Efektif', 'Kekurangan'));
      foreach ($rekap_presensi as $hasil) {
        $pdf->Row(array($hasil['nama'],$hasil['shift'],$hasil['jam_datang'],$hasil['jam_pulang'],$hasil['durasi'],'00:00:00','00:00:00'));
      }
      $pdf->Output('laporan_pasien_'.date('Y-m-d').'.pdf','I');

    }

    public function getGoogleMap($id,$tanggal)
    {
      $geo = $this->db('mlite_geolocation_presensi')->where('id', $id)->where('tanggal', $tanggal)->oneArray();
      $pegawai = $this->db('pegawai')->where('id', $id)->oneArray();

      $this->tpl->set('geo', $geo);
      $this->tpl->set('pegawai', $pegawai);
      echo $this->tpl->draw(MODULES.'/presensi/view/admin/google_map.html', true);
      exit();
    }

    public function getPresensi($page = 1)
    {
        $this->_addHeaderFiles();

        $perpage = '10';
        $phrase = '';
        if(isset($_GET['s']))
          $phrase = $_GET['s'];

        $ruang = '';
        if (isset($_GET['ruang'])){
            $ruang = $_GET['ruang'];
        }

        $dep = '';
        if (isset($_GET['dep'])){
            $dep = $_GET['dep'];
        }

        $username = $this->core->getUserInfo('username', null, true);

        // pagination
        if($this->core->getUserInfo('role') == 'admin'){
        $totalRecords = $this->db('temporary_presensi')
            ->join('pegawai','pegawai.id = temporary_presensi.id')
            ->like('departemen','%'.$dep.'%')
            ->like('bidang','%'.$ruang.'%')
            ->like('nama', '%'.$phrase.'%')
            ->asc('jam_datang')
            ->toArray();
        }else{
            $totalRecords = $this->db('temporary_presensi')
            ->join('pegawai','pegawai.id = temporary_presensi.id')
            ->where('departemen', $this->core->getPegawaiInfo('departemen',$username))
            ->where('bidang', $this->core->getPegawaiInfo('bidang',$username))
            ->like('nama', '%'.$phrase.'%')
            ->asc('jam_datang')
            ->toArray();
        }
        //$pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'presensi', 'presensi', '%d']));
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'presensi', 'presensi', '%d', '?s='.$phrase.'&ruang='.$ruang.'&dep='.$dep]));
        $this->assign['pagination'] = $pagination->nav('pagination','5');
        $this->assign['totalRecords'] = $totalRecords;

        // list
        $offset = $pagination->offset();
        if($this->core->getUserInfo('role') == 'admin'){
        $rows = $this->db('temporary_presensi')
            ->select([
              'nama' => 'pegawai.nama',
              'jbtn' => 'pegawai.jbtn',
              'bidang' => 'pegawai.bidang',
              'id' => 'temporary_presensi.id',
              'shift' => 'temporary_presensi.shift',
              'jam_datang' => 'temporary_presensi.jam_datang',
              'status' => 'temporary_presensi.status',
              'photo' => 'temporary_presensi.photo'
            ])
            ->join('pegawai','pegawai.id = temporary_presensi.id')
            ->like('departemen','%'.$dep.'%')
            ->like('bidang','%'.$ruang.'%')
            ->like('nama', '%'.$phrase.'%')
            ->asc('jam_datang')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
        }else{
        $rows = $this->db('temporary_presensi')
            ->select([
              'nama' => 'pegawai.nama',
              'id' => 'temporary_presensi.id',
              'shift' => 'temporary_presensi.shift',
              'jam_datang' => 'temporary_presensi.jam_datang',
              'status' => 'temporary_presensi.status',
              'photo' => 'temporary_presensi.photo'
            ])
            ->join('pegawai','pegawai.id = temporary_presensi.id')
            ->where('departemen', $this->core->getPegawaiInfo('departemen',$username))
            ->where('bidang', $this->core->getPegawaiInfo('bidang',$username))
            ->like('nama', '%'.$phrase.'%')
            ->asc('jam_datang')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
        }
        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                $row['mapURL']  = url([ADMIN, 'presensi', 'googlemap', $row['id'], date('Y-m-d', strtotime($row['jam_datang']))]);
                $row['editURL'] = url([ADMIN, 'presensi', 'presensipulang', $row['id']]);
                $this->assign['list'][] = $row;
            }
        }
        $this->assign['bidang'] = $this->db('bidang')->toArray();
        $this->assign['dep'] = $this->db('departemen')->toArray();

        return $this->draw('presensi.html', ['presensi' => $this->assign]);
    }

  	public function getPresensiPulang($id){
        $cek = $this->db('temporary_presensi')->where('id', $id)->oneArray();
        $jam_jaga       = $this->db('jam_jaga')->join('pegawai', 'pegawai.departemen = jam_jaga.dep_id')->where('pegawai.id', $id)->where('jam_jaga.shift', $cek['shift'])->oneArray();
        $location = url([ADMIN, 'presensi', 'presensi']);
        if($cek){

            $status = $cek['status'];
            if((strtotime(date('Y-m-d H:i:s'))-strtotime(date('Y-m-d').' '.$jam_jaga['jam_pulang']))<0) {
                $status = $cek['status'].' & PSW';
            }

            $awal  = new \DateTime($cek['jam_datang']);
            $akhir = new \DateTime();
            $diff = $akhir->diff($awal,true); // to make the difference to be always positive.
            $durasi = $diff->format('%H:%I:%S');

            $ubah = $this->db('temporary_presensi')
                ->where('id', $id)
                ->save([
                'jam_pulang' => date('Y-m-d H:i:s'),
                'status' => $status,
                'durasi' => $durasi
                ]);

            if($ubah) {
                $presensi = $this->db('temporary_presensi')->where('id', $id)->oneArray();
                $insert = $this->db('rekap_presensi')
                    ->save([
                    'id' => $presensi['id'],
                    'shift' => $presensi['shift'],
                    'jam_datang' => $presensi['jam_datang'],
                    'jam_pulang' => $presensi['jam_pulang'],
                    'status' => $presensi['status'],
                    'keterlambatan' => $presensi['keterlambatan'],
                    'durasi' => $presensi['durasi'],
                    'keterangan' => '-',
                    'photo' => $presensi['photo']
                    ]);
                if($insert) {
                    $this->notify('success', 'Presensi pulang telah disimpan');
                    $this->db('temporary_presensi')->where('id', $cek['id'])->delete();
                    redirect($location);
                }
            }
        }
    }

    /* Master Barcode Section */
    public function getBarcode($page = 1)
    {
        $this->_addHeaderFiles();
        $perpage = '10';

        $phrase = '';
        if(isset($_GET['s']))
          $phrase = $_GET['s'];

        // pagination
        $totalRecords = $this->db('barcode')
            ->select('id')
            ->like('barcode', '%'.$phrase.'%')
            ->toArray();
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'presensi', 'barcode', '%d']));
        $this->assign['pagination'] = $pagination->nav('pagination','5');
        $this->assign['totalRecords'] = $totalRecords;

        // list
        $offset = $pagination->offset();
        $rows = $this->db('barcode')
            ->join('pegawai', 'pegawai.id = barcode.id')
            ->like('barcode', '%'.$phrase.'%')
            ->orLike('nama', '%'.$phrase.'%')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();

        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                $row['editURL'] = url([ADMIN, 'presensi', 'barcodeedit', $row['id']]);
                $row['delURL']  = url([ADMIN, 'presensi', 'barcodedelete', $row['id']]);
                $this->assign['list'][] = $row;
            }
        }

        $this->assign['addURL'] = url([ADMIN, 'presensi', 'barcodeadd']);

        return $this->draw('barcode.manage.html', ['barcode' => $this->assign]);

    }

    public function getBarcodeAdd()
    {
        $this->_addHeaderFiles();
        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = [
              'id' => '',
              'barcode' => ''
            ];
        }

        $this->assign['title'] = 'Tambah Barcode';
        $this->assign['pegawai'] = $this->db('pegawai')
          ->select([
            'id' => 'id',
            'nik' => 'nik',
            'nama' => 'nama'
          ])
          ->toArray();

        return $this->draw('barcode.form.html', ['barcode' => $this->assign]);
    }

    public function getBarcodeEdit($id)
    {
        $this->_addHeaderFiles();
        $row = $this->db('barcode')->oneArray($id);
        if (!empty($row)) {
            $this->assign['form'] = $row;
            $this->assign['title'] = 'Edit Barcode';
            $this->assign['pegawai'] = $this->db('pegawai')
              ->select([
                'id' => 'id',
                'nik' => 'nik',
                'nama' => 'nama'
              ])
              ->toArray();

            return $this->draw('barcode.form.html', ['barcode' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'presensi', 'barcode']));
        }
    }

    public function getBarcodeDelete($id)
    {
        if ($this->core->db('barcode')->delete($id)) {
            $this->notify('success', 'Hapus sukses');
        } else {
            $this->notify('failure', 'Hapus gagal');
        }
        redirect(url([ADMIN, 'presensi', 'barcode']));
    }

    public function postBarcodeSave($id = null)
    {
        $errors = 0;

        if (!$id) {
            $location = url([ADMIN, 'presensi', 'barcodeadd']);
        } else {
            $location = url([ADMIN, 'presensi', 'barcodeedit', $id]);
        }

        if (checkEmptyFields(['id', 'barcode'], $_POST)) {
            $this->notify('failure', 'Isian kosong');
            redirect($location, $_POST);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (!$id) {    // new
                $query = $this->db('barcode')->save($_POST);
            } else {        // edit
                $query = $this->db('barcode')->where('id', $id)->save($_POST);
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

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/presensi/js/admin/app.js');
        exit();
    }

    private function _addHeaderFiles()
    {
        // CSS
        $this->core->addCSS(url('assets/css/jquery-ui.css'));
        $this->core->addCSS(url('assets/jscripts/lightbox/lightbox.min.css'));
        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));

        // JS
        $this->core->addJS(url('assets/jscripts/jquery-ui.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/lightbox/lightbox.min.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));

        // MODULE SCRIPTS
        $this->core->addJS(url([ADMIN, 'presensi', 'javascript']), 'footer');
    }
}
