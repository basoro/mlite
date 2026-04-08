<?php

namespace Plugins\Presensi;

use Systems\AdminModule;

class Admin extends AdminModule
{
    public $assign;

    public function navigation()
    {
        if ($this->core->getUserInfo('role') == 'admin') {
            return [
                'Kelola' => 'manage',
                'Presensi Masuk' => 'presensi',
                'Rekap Presensi' => 'rekap_presensi',
                'Rekap Bulanan' => 'rekap_bulanan',
                'Barcode Presensi' => 'barcode',
                'Jam Masuk' => 'jammasuk',
                'Jam Jaga' => 'jamjaga',
                'Jadwal Pegawai' => 'jadwal',
                'Jadwal Tambahan' => 'jadwal_tambahan',
                'Pengaturan' => 'settings'
            ];
        } else {
            return [
                'Kelola' => 'manage',
                'Presensi Masuk' => 'presensi',
                'Rekap Presensi' => 'rekap_presensi',
                'Rekap Bulanan' => 'rekap_bulanan',
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
                ['name' => 'Rekap Bulanan', 'url' => url([ADMIN, 'presensi', 'rekap_bulanan']), 'icon' => 'cubes', 'desc' => 'Rekap Bulanan Presensi Pegawai'],
                ['name' => 'Barcode Presensi', 'url' => url([ADMIN, 'presensi', 'barcode']), 'icon' => 'cubes', 'desc' => 'Barcode Presensi Pegawai'],
                ['name' => 'Jam Masuk', 'url' => url([ADMIN, 'presensi', 'jammasuk']), 'icon' => 'cubes', 'desc' => 'Jam Masuk Pegawai'],
                ['name' => 'Jam Jaga', 'url' => url([ADMIN, 'presensi', 'jamjaga']), 'icon' => 'cubes', 'desc' => 'Jam Jaga Pegawai'],
                ['name' => 'Jadwal', 'url' => url([ADMIN, 'presensi', 'jadwal']), 'icon' => 'cubes', 'desc' => 'Jadwal Pegawai'],
                ['name' => 'Jadwal Tambahan', 'url' => url([ADMIN, 'presensi', 'jadwal_tambahan']), 'icon' => 'cubes', 'desc' => 'Jadwal Tambahan Pegawai'],
                ['name' => 'Pengaturan', 'url' => url([ADMIN, 'presensi', 'settings']), 'icon' => 'cubes', 'desc' => 'Pengaturan Presensi']
            ];
        } else {
            $sub_modules = [
                ['name' => 'Presensi', 'url' => url([ADMIN, 'presensi', 'presensi']), 'icon' => 'cubes', 'desc' => 'Presensi Pegawai'],
                ['name' => 'Rekap Presensi', 'url' => url([ADMIN, 'presensi', 'rekap_presensi']), 'icon' => 'cubes', 'desc' => 'Rekap Presensi Pegawai'],
                ['name' => 'Rekap Bulanan', 'url' => url([ADMIN, 'presensi', 'rekap_bulanan']), 'icon' => 'cubes', 'desc' => 'Rekap Bulanan Presensi Pegawai'],
                ['name' => 'Jadwal', 'url' => url([ADMIN, 'presensi', 'jadwal']), 'icon' => 'cubes', 'desc' => 'Jadwal Pegawai'],
                ['name' => 'Jadwal Tambahan', 'url' => url([ADMIN, 'presensi', 'jadwal_tambahan']), 'icon' => 'cubes', 'desc' => 'Jadwal Tambahan Pegawai']
            ];
        }
        return $this->draw('manage.html', ['sub_modules' => htmlspecialchars_array($sub_modules)]);
    }

    public function getJamJaga($page = 1)
    {
        $this->_addHeaderFiles();
        $perpage = '10';
        $phrase = '';
        if (isset($_GET['s']))
            $phrase = $_GET['s'];

        $status = '1';
        if (isset($_GET['status']))
            $status = $_GET['status'];

        // pagination
        $totalRecords = $this->db('jam_jaga')
            ->like('shift', '%' . $phrase . '%')
            ->orLike('dep_id', '%' . $phrase . '%')
            ->toArray();
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'presensi', 'jamjaga', '%d']));
        $this->assign['pagination'] = $pagination->nav('pagination', '5');
        $this->assign['totalRecords'] = $totalRecords;

        // list
        $offset = $pagination->offset();
        $rows = $this->db('jam_jaga')
            ->like('shift', '%' . $phrase . '%')
            ->orLike('dep_id', '%' . $phrase . '%')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();

        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                $this->assign['list'][] = $row;
            }
        }

        $this->assign['addURL'] = url([ADMIN, 'presensi', 'jagaadd']);

        return $this->draw('jam_jaga.html', ['jamjaga' => htmlspecialchars_array($this->assign)]);
    }

    public function getJagaAdd()
    {
        $this->_addHeaderFiles();
        if (!empty($redirectData = getRedirectData())) {
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
        $this->assign['addURL'] = url([ADMIN, 'presensi', 'jagaadd']);

        return $this->draw('jagaadd.form.html', ['jagaadd' => htmlspecialchars_array($this->assign)]);
    }

    public function postJagaSave($id = null)
    {
        $errors = 0;

        if (!$id) {
            $location = url([ADMIN, 'presensi', 'jamjaga']);
        } else {
            $location = url([ADMIN, 'presensi', 'editjaga']);
        }

        if (checkEmptyFields(['dep_id'], $_POST)) {
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

    public function getJagaDel($id = null)
    {
        $this->_addHeaderFiles();
        if (!$id) {
            $this->notify('failure', 'ID tidak ditemukan');
            redirect(url([ADMIN, 'presensi', 'jamjaga']));
        }

        $query = $this->db('jam_jaga')->where('no_id', $id)->delete();
        if ($query) {
            $this->notify('success', 'Hapus sukses');
        } else {
            $this->notify('failure', 'Hapus gagal');
        }

        redirect(url([ADMIN, 'presensi', 'jamjaga']));
    }

    public function getJadwal($page = 1)
    {
        $this->_addHeaderFiles();
        $perpage = '10';
        $phrase = '';
        $bulan = date('m');

        if (isset($_GET['s']))
            $phrase = $_GET['s'];

        if (isset($_GET['b'])) {
            $bulan = $_GET['b'];
        }

        $tahun = date('Y');
        if (isset($_GET['y'])) {
            $tahun = $_GET['y'];
        }

        $username = $this->core->getUserInfo('username', null, true);

        // pagination
        if ($this->core->getUserInfo('role') == 'admin') {
            $totalRecords = $this->db('jadwal_pegawai')
                ->join('pegawai', 'pegawai.id=jadwal_pegawai.id')
                ->where('jadwal_pegawai.tahun', $tahun)
                ->like('jadwal_pegawai.bulan', $bulan . '%')
                ->like('pegawai.nama', '%' . $phrase . '%')
                ->toArray();
        } else {
            $totalRecords = $this->db('jadwal_pegawai')
                ->join('pegawai', 'pegawai.id=jadwal_pegawai.id')
                ->where('jadwal_pegawai.tahun', $tahun)
                ->where('jadwal_pegawai.bulan', $bulan)
                ->where('departemen', $this->core->getPegawaiInfo('departemen', $username))
                ->where('bidang', $this->core->getPegawaiInfo('bidang', $username))
                ->like('pegawai.nama', '%' . $phrase . '%')
                ->toArray();
        }
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'presensi', 'jadwal', '%d?b=' . $bulan . '&s=' . $phrase]));
        $this->assign['pagination'] = $pagination->nav('pagination', '5');
        $this->assign['totalRecords'] = $totalRecords;

        // list
        $offset = $pagination->offset();
        if ($this->core->getUserInfo('role') == 'admin') {
            $rows = $this->db('jadwal_pegawai')
                ->join('pegawai', 'pegawai.id=jadwal_pegawai.id')
                ->where('jadwal_pegawai.tahun', $tahun)
                ->like('jadwal_pegawai.bulan', $bulan . '%')
                ->like('pegawai.nama', '%' . $phrase . '%')
                ->offset($offset)
                ->limit($perpage)
                ->toArray();
        } else {
            $rows = $this->db('jadwal_pegawai')
                ->join('pegawai', 'pegawai.id=jadwal_pegawai.id')
                ->where('jadwal_pegawai.tahun', $tahun)
                ->where('jadwal_pegawai.bulan', $bulan)
                ->where('departemen', $this->core->getPegawaiInfo('departemen', $username))
                ->where('bidang', $this->core->getPegawaiInfo('bidang', $username))
                ->like('pegawai.nama', '%' . $phrase . '%')
                ->offset($offset)
                ->limit($perpage)
                ->toArray();
        }
        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                $row['editURL'] = url([ADMIN, 'presensi', 'jadwaledit', $row['id'], $bulan, $tahun]);
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
        $this->assign['bulan'] = array('', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
        $startYear = 2020;
        $currentYear = date('Y');
        $endYear = $currentYear + 2;
        $tahun = [''];
        for ($i = $startYear; $i <= $endYear; $i++) {
            $tahun[] = (string)$i;
        }
        $this->assign['tahun'] = $tahun;
        return $this->draw('jadwal.manage.html', ['jadwal' => htmlspecialchars_array($this->assign)]);
    }

    public function getJadwalAdd()
    {
        $this->_addHeaderFiles();
        if (!empty($redirectData = getRedirectData())) {
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
        if ($this->core->getUserInfo('role') == 'admin') {
            $this->assign['id'] = $this->db('pegawai')
                ->where('stts_aktif', 'AKTIF')
                ->toArray();
        } else {
            $this->assign['id'] = $this->db('pegawai')
                ->where('departemen', $this->core->getPegawaiInfo('departemen', $username))
                ->where('bidang', $this->core->getPegawaiInfo('bidang', $username))
                ->where('stts_aktif', 'AKTIF')
                ->toArray();
        }
        if ($this->core->getUserInfo('role') == 'admin') {
            $this->assign['h1'] = $this->db('jam_masuk')->toArray();
        } else {
            $this->assign['h1'] = $this->db('jam_jaga')->where('dep_id', $this->core->getPegawaiInfo('departemen', $username))->toArray();
        }
        $startYear = 2020;
        $currentYear = date('Y');
        $endYear = $currentYear + 2;
        $tahun = [''];
        for ($i = $startYear; $i <= $endYear; $i++) {
            $tahun[] = (string)$i;
        }
        $this->assign['tahun'] = $tahun;
        //$this->assign['tahun'] = date('Y');
        $this->assign['bulan'] = array('', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');

        return $this->draw('jadwal.form.html', ['jadwal' => htmlspecialchars_array($this->assign)]);
    }

    public function getJadwalEdit($id = null, $bulan = null, $tahun = null)
    {
        $this->_addHeaderFiles();
        if ($bulan == "") {
            $bulan = date('m');
        }
        if ($tahun == "") {
            $tahun = date('Y');
        }

        $row = $this->db('jadwal_pegawai')->where('id', $id)->where('tahun', $tahun)->where('bulan', $bulan)->oneArray();
        if (!empty($row)) {
            $username = $this->core->getUserInfo('username', null, true);
            $this->assign['form'] = $row;
            if ($this->core->getUserInfo('role') == 'admin') {
                $this->assign['id'] = $this->db('pegawai')
                    ->where('stts_aktif', 'AKTIF')
                    ->toArray();
            } else {
                $this->assign['id'] = $this->db('pegawai')
                    ->where('departemen', $this->core->getPegawaiInfo('departemen', $username))
                    ->where('bidang', $this->core->getPegawaiInfo('bidang', $username))
                    ->where('stts_aktif', 'AKTIF')
                    ->toArray();
            }
            if ($this->core->getUserInfo('role') == 'admin') {
                $this->assign['h1'] = $this->db('jam_masuk')->toArray();
            } else {
                $this->assign['h1'] = $this->db('jam_jaga')->where('dep_id', $this->core->getPegawaiInfo('departemen', $username))->toArray();
            }
            $startYear = 2020;
            $currentYear = date('Y');
            $endYear = $currentYear + 2;
            $tahun = [''];
            for ($i = $startYear; $i <= $endYear; $i++) {
                $tahun[] = (string)$i;
            }
            $this->assign['tahun'] = $tahun;
            //$this->assign['tahun'] = $tahun;
            $this->assign['bulan'] = array('', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');

            return $this->draw('jadwal.form.html', ['jadwal' => htmlspecialchars_array($this->assign)]);
        } else {
            redirect(url([ADMIN, 'presensi', 'jadwal']));
        }
    }

    public function postJadwalSave($id = null)
    {
        $errors = 0;

        if (!$id) {
            $location = url([ADMIN, 'presensi', 'jadwal']);
        } else {
            $location = url([ADMIN, 'presensi', 'jadwaledit', $id, $_POST['bulan'], $_POST['tahun']]);
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
        $this->assign['bulan'] = array('','01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
        $startYear = 2020;
        $currentYear = date('Y');
        $endYear = $currentYear + 2;
        $tahun = [''];
        for ($i = $startYear; $i <= $endYear; $i++) {
            $tahun[] = (string)$i;
        }
        $this->assign['tahun'] = $tahun;
        return $this->draw('jadwal_tambah.manage.html', ['jadwal_tambah' => htmlspecialchars_array($this->assign)]);
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

                                ->where('stts_aktif','AKTIF')
                                ->toArray();
        }
        if($this->core->getUserInfo('role') == 'admin'){
            $this->assign['h1'] = $this->db('jam_masuk')->toArray();
        }else{
            $this->assign['h1'] = $this->db('jam_jaga')->where('dep_id', $this->core->getPegawaiInfo('departemen',$username))->toArray();
        }
        $startYear = 2020;
        $currentYear = date('Y');
        $endYear = $currentYear + 2;
        $tahun = [''];
        for ($i = $startYear; $i <= $endYear; $i++) {
            $tahun[] = (string)$i;
        }
        $this->assign['tahun'] = $tahun;
        $this->assign['bulan'] = array('','01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');

        return $this->draw('jadwal_tambah.form.html', ['jadwal' => htmlspecialchars_array($this->assign)]);
    }

    public function getJadwalTambahEdit($id = null, $bulan = null, $tahun = null)
    {
        $this->_addHeaderFiles();
        if ($bulan == "") {
            $bulan = date('m');
        }
        if ($tahun == "") {
            $tahun = date('Y');
        }
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
                                    ->where('stts_aktif','AKTIF')
                                    ->toArray();
            }
            if ($this->core->getUserInfo('role') == 'admin') {
                $this->assign['h1'] = $this->db('jam_masuk')->toArray();
            } else {
                $this->assign['h1'] = $this->db('jam_jaga')->where('dep_id', $this->core->getPegawaiInfo('departemen', $username))->toArray();
            }
            $startYear = 2020;
            $currentYear = date('Y');
            $endYear = $currentYear + 2;
            $tahun = [''];
            for ($i = $startYear; $i <= $endYear; $i++) {
                $tahun[] = (string)$i;
            }
            $this->assign['tahun'] = $tahun;
            $this->assign['bulan'] = array('','01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');

            return $this->draw('jadwal_tambah.form.html', ['jadwal' => htmlspecialchars_array($this->assign)]);
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
        if (isset($_GET['s']))
            $phrase = $_GET['s'];

        $tgl_kunjungan = date('Y-m-d');
        $tgl_kunjungan_akhir = date('Y-m-d');
        // $status_periksa = '';
        // $status_bayar = '';

        if (isset($_GET['awal'])) {
            $tgl_kunjungan = $_GET['awal'];
        }
        if (isset($_GET['akhir'])) {
            $tgl_kunjungan_akhir = $_GET['akhir'];
        }

        $ruang = '';
        if (isset($_GET['ruang'])) {
            $ruang = $_GET['ruang'];
        }

        $username = $this->core->getUserInfo('username', null, true);

        if ($this->core->getUserInfo('role') == 'admin') {
            $totalRecords = $this->db('rekap_presensi')
                ->join('pegawai', 'pegawai.id = rekap_presensi.id')
                ->where('jam_datang', '>=', $tgl_kunjungan . ' 00:00:00')
                ->where('jam_datang', '<=', $tgl_kunjungan_akhir . ' 23:59:59')
                ->like('bidang', '%' . $ruang . '%')
                ->like('nama', '%' . $phrase . '%')
                ->asc('jam_datang')
                ->toArray();
        } else {
            $totalRecords = $this->db('rekap_presensi')
                ->join('pegawai', 'pegawai.id = rekap_presensi.id')
                ->where('jam_datang', '>=', $tgl_kunjungan . ' 00:00:00')
                ->where('jam_datang', '<=', $tgl_kunjungan_akhir . ' 23:59:59')
                ->where('departemen', $this->core->getPegawaiInfo('departemen', $username))
                ->where('bidang', $this->core->getPegawaiInfo('bidang', $username))
                ->like('nama', '%' . $phrase . '%')
                ->asc('jam_datang')
                ->toArray();
        }
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'presensi', 'rekap_presensi', '%d?awal=' . $tgl_kunjungan . '&akhir=' . $tgl_kunjungan_akhir . '&ruang=' . $ruang . '&s=' . $phrase]));
        $this->assign['pagination'] = $pagination->nav('pagination', '5');
        $this->assign['totalRecords'] = $totalRecords;

        // list
        $offset = $pagination->offset();

        if ($this->core->getUserInfo('role') == 'admin') {
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
                ->join('pegawai', 'pegawai.id = rekap_presensi.id')
                ->where('jam_datang', '>=', $tgl_kunjungan . ' 00:00:00')
                ->where('jam_datang', '<=', $tgl_kunjungan_akhir . ' 23:59:59')
                ->like('bidang', '%' . $ruang . '%')
                ->like('nama', '%' . $phrase . '%')
                ->asc('jam_datang')
                ->offset($offset)
                ->limit($perpage)
                ->toArray();
        } else {
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
                ->join('pegawai', 'pegawai.id = rekap_presensi.id')
                ->where('jam_datang', '>=', $tgl_kunjungan . ' 00:00:00')
                ->where('jam_datang', '<=', $tgl_kunjungan_akhir . ' 23:59:59')
                ->where('departemen', $this->core->getPegawaiInfo('departemen', $username))
                ->where('bidang', $this->core->getPegawaiInfo('bidang', $username))
                ->like('nama', '%' . $phrase . '%')
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

                // Optimized: removed redundant DB query for date parts and shift
                $jam_datang_timestamp = strtotime($row['jam_datang']);
                $jam_datang = [
                    'year' => date('Y', $jam_datang_timestamp),
                    'month' => date('m', $jam_datang_timestamp),
                    'day' => date('d', $jam_datang_timestamp),
                    'shift' => $row['shift']
                ];
                $stts1 = '';
                $stts2 = '';

                $s = $row['jam_datang'];
                $dt = new \DateTime($s);
                $tm = $dt->format('h:i:s');

                $w = $row['jam_pulang'];
                $dd = new \DateTime($w);
                $tp = $dd->format('H:i:s');

                $row['date'] = $day[date('D', strtotime(date($jam_datang['year'] . '-' . $jam_datang['month'] . '-' . $jam_datang['day'])))];
                switch (true) {
                    case ($row['date'] == 'SENIN' and $jam_datang['shift'] == 'Pagi'):
                        $interval = 'INTERVAL 6 * 60 + 30 MINUTE';
                        $efektif = 'INTERVAL 1 HOUR';
                        if ($tm > '08:11:00' and $tm < '08:30:00') {
                            $stts1 = 'TL1';
                        } elseif ($tm > '08:31:00' and $tm < '09:00:00') {
                            $stts1 = 'TL2';
                        } elseif ($tm > '09:01:00' and $tm < '09:30:00') {
                            $stts1 = 'TL3';
                        } elseif ($tm > '09:31:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '14:01:00' and $tp < '14:19:00') {
                            $stts2 = 'PSW1';
                        } elseif ($tp > '13:31:00' and $tp < '14:00:00') {
                            $stts2 = 'PSW2';
                        } elseif ($tp > '13:01:00' and $tp < '13:30:00') {
                            $stts2 = 'PSW3';
                        } elseif ($tp < '13:00:00') {
                            $stts2 = 'PSW4';
                        }
                        break;
                    case ($row['date'] == 'SENIN' and $jam_datang['shift'] == 'Siang'):
                        $interval = 'INTERVAL 5 HOUR';
                        $efektif = 'INTERVAL 1 HOUR';
                        break;
                        if ($tm > '14:41:00' and $tm < '15:00:00') {
                            $stts1 = 'TL1';
                        } elseif ($tm > '15:01:00' and $tm < '15:30:00') {
                            $stts1 = 'TL2';
                        } elseif ($tm > '15:31:00' and $tm < '16:00:00') {
                            $stts1 = 'TL3';
                        } elseif ($tm > '16:00:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '20:01:00' and $tp < '20:19:00') {
                            $stts2 = 'PSW1';
                        } elseif ($tp > '19:31:00' and $tp < '20:00:00') {
                            $stts2 = 'PSW2';
                        } elseif ($tp > '19:01:00' and $tp < '19:30:00') {
                            $stts2 = 'PSW3';
                        } elseif ($tp < '19:00:00') {
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
                        } elseif ($tm > '21:01:00' and $tm < '21:30:00') {
                            $stts1 = 'TL2';
                        } elseif ($tm > '21:31:00' and $tm < '22:00:00') {
                            $stts1 = 'TL3';
                        } elseif ($tm > '22:01:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '07:31:00' and $tp < '07:49:00') {
                            $stts2 = 'PSW1';
                        } elseif ($tp > '07:01:00' and $tp < '07:30:00') {
                            $stts2 = 'PSW2';
                        } elseif ($tp > '06:31:00' and $tp < '07:00:00') {
                            $stts2 = 'PSW3';
                        } elseif ($tp < '06:30:00') {
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
                        } elseif ($tm > '08:31:00' and $tm < '09:00:00') {
                            $stts1 = 'TL2';
                        } elseif ($tm > '09:01:00' and $tm < '09:30:00') {
                            $stts1 = 'TL3';
                        } elseif ($tm > '09:31:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '14:01:00' and $tp < '14:19:00') {
                            $stts2 = 'PSW1';
                        } elseif ($tp > '13:31:00' and $tp < '14:00:00') {
                            $stts2 = 'PSW2';
                        } elseif ($tp > '13:01:00' and $tp < '13:30:00') {
                            $stts2 = 'PSW3';
                        } elseif ($tp < '13:00:00') {
                            $stts2 = 'PSW4';
                        }
                        break;
                    case ($row['date'] == 'SELASA' and $jam_datang['shift'] == 'Siang'):
                        $interval = 'INTERVAL 5 HOUR';
                        $efektif = 'INTERVAL 1 HOUR';
                        if ($tm > '14:41:00' and $tm < '15:00:00') {
                            $stts1 = 'TL1';
                        } elseif ($tm > '15:01:00' and $tm < '15:30:00') {
                            $stts1 = 'TL2';
                        } elseif ($tm > '15:31:00' and $tm < '16:00:00') {
                            $stts1 = 'TL3';
                        } elseif ($tm > '16:00:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '20:01:00' and $tp < '20:19:00') {
                            $stts2 = 'PSW1';
                        } elseif ($tp > '19:31:00' and $tp < '20:00:00') {
                            $stts2 = 'PSW2';
                        } elseif ($tp > '19:01:00' and $tp < '19:30:00') {
                            $stts2 = 'PSW3';
                        } elseif ($tp < '19:00:00') {
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
                        } elseif ($tm > '21:01:00' and $tm < '21:30:00') {
                            $stts1 = 'TL2';
                        } elseif ($tm > '21:31:00' and $tm < '22:00:00') {
                            $stts1 = 'TL3';
                        } elseif ($tm > '22:01:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '07:31:00' and $tp < '07:49:00') {
                            $stts2 = 'PSW1';
                        } elseif ($tp > '07:01:00' and $tp < '07:30:00') {
                            $stts2 = 'PSW2';
                        } elseif ($tp > '06:31:00' and $tp < '07:00:00') {
                            $stts2 = 'PSW3';
                        } elseif ($tp < '06:30:00') {
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
                        } elseif ($tm > '08:31:00' and $tm < '09:00:00') {
                            $stts1 = 'TL2';
                        } elseif ($tm > '09:01:00' and $tm < '09:30:00') {
                            $stts1 = 'TL3';
                        } elseif ($tm > '09:31:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '14:01:00' and $tp < '14:19:00') {
                            $stts2 = 'PSW1';
                        } elseif ($tp > '13:31:00' and $tp < '14:00:00') {
                            $stts2 = 'PSW2';
                        } elseif ($tp > '13:01:00' and $tp < '13:30:00') {
                            $stts2 = 'PSW3';
                        } elseif ($tp < '13:00:00') {
                            $stts2 = 'PSW4';
                        }
                        break;
                    case ($row['date'] == 'RABU' and $jam_datang['shift'] == 'Siang'):
                        $interval = 'INTERVAL 5 HOUR';
                        $efektif = 'INTERVAL 1 HOUR';
                        if ($tm > '14:41:00' and $tm < '15:00:00') {
                            $stts1 = 'TL1';
                        } elseif ($tm > '15:01:00' and $tm < '15:30:00') {
                            $stts1 = 'TL2';
                        } elseif ($tm > '15:31:00' and $tm < '16:00:00') {
                            $stts1 = 'TL3';
                        } elseif ($tm > '16:00:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '20:01:00' and $tp < '20:19:00') {
                            $stts2 = 'PSW1';
                        } elseif ($tp > '19:31:00' and $tp < '20:00:00') {
                            $stts2 = 'PSW2';
                        } elseif ($tp > '19:01:00' and $tp < '19:30:00') {
                            $stts2 = 'PSW3';
                        } elseif ($tp < '19:00:00') {
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
                        } elseif ($tm > '21:01:00' and $tm < '21:30:00') {
                            $stts1 = 'TL2';
                        } elseif ($tm > '21:31:00' and $tm < '22:00:00') {
                            $stts1 = 'TL3';
                        } elseif ($tm > '22:01:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '07:31:00' and $tp < '07:49:00') {
                            $stts2 = 'PSW1';
                        } elseif ($tp > '07:01:00' and $tp < '07:30:00') {
                            $stts2 = 'PSW2';
                        } elseif ($tp > '06:31:00' and $tp < '07:00:00') {
                            $stts2 = 'PSW3';
                        } elseif ($tp < '06:30:00') {
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
                        } elseif ($tm > '08:31:00' and $tm < '09:00:00') {
                            $stts1 = 'TL2';
                        } elseif ($tm > '09:01:00' and $tm < '09:30:00') {
                            $stts1 = 'TL3';
                        } elseif ($tm > '09:31:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '14:01:00' and $tp < '14:19:00') {
                            $stts2 = 'PSW1';
                        } elseif ($tp > '13:31:00' and $tp < '14:00:00') {
                            $stts2 = 'PSW2';
                        } elseif ($tp > '13:01:00' and $tp < '13:30:00') {
                            $stts2 = 'PSW3';
                        } elseif ($tp < '13:00:00') {
                            $stts2 = 'PSW4';
                        }
                        break;
                    case ($row['date'] == 'KAMIS' and $jam_datang['shift'] == 'Siang'):
                        $interval = 'INTERVAL 5 HOUR';
                        $efektif = 'INTERVAL 1 HOUR';
                        if ($tm > '14:41:00' and $tm < '15:00:00') {
                            $stts1 = 'TL1';
                        } elseif ($tm > '15:01:00' and $tm < '15:30:00') {
                            $stts1 = 'TL2';
                        } elseif ($tm > '15:31:00' and $tm < '16:00:00') {
                            $stts1 = 'TL3';
                        } elseif ($tm > '16:00:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '20:01:00' and $tp < '20:19:00') {
                            $stts2 = 'PSW1';
                        } elseif ($tp > '19:31:00' and $tp < '20:00:00') {
                            $stts2 = 'PSW2';
                        } elseif ($tp > '19:01:00' and $tp < '19:30:00') {
                            $stts2 = 'PSW3';
                        } elseif ($tp < '19:00:00') {
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
                        } elseif ($tm > '21:01:00' and $tm < '21:30:00') {
                            $stts1 = 'TL2';
                        } elseif ($tm > '21:31:00' and $tm < '22:00:00') {
                            $stts1 = 'TL3';
                        } elseif ($tm > '22:01:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '07:31:00' and $tp < '07:49:00') {
                            $stts2 = 'PSW1';
                        } elseif ($tp > '07:01:00' and $tp < '07:30:00') {
                            $stts2 = 'PSW2';
                        } elseif ($tp > '06:31:00' and $tp < '07:00:00') {
                            $stts2 = 'PSW3';
                        } elseif ($tp < '06:30:00') {
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
                        } elseif ($tm > '08:31:00' and $tm < '09:00:00') {
                            $stts1 = 'TL2';
                        } elseif ($tm > '09:01:00' and $tm < '09:30:00') {
                            $stts1 = 'TL3';
                        } elseif ($tm > '09:31:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '10:30:00' and $tp < '10:49:00') {
                            $stts2 = 'PSW1';
                        } elseif ($tp > '10:01:00' and $tp < '10:30:00') {
                            $stts2 = 'PSW2';
                        } elseif ($tp > '09:30:00' and $tp < '10:00:00') {
                            $stts2 = 'PSW3';
                        } elseif ($tp < '09:29:00') {
                            $stts2 = 'PSW4';
                        }
                        break;
                    case ($row['date'] == 'JUMAT' and $jam_datang['shift'] == 'Siang'):
                        $interval = 'INTERVAL 5 HOUR';
                        $efektif = 'INTERVAL 1 HOUR';
                        if ($tm > '14:41:00' and $tm < '15:00:00') {
                            $stts1 = 'TL1';
                        } elseif ($tm > '15:01:00' and $tm < '15:30:00') {
                            $stts1 = 'TL2';
                        } elseif ($tm > '15:31:00' and $tm < '16:00:00') {
                            $stts1 = 'TL3';
                        } elseif ($tm > '16:00:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '20:01:00' and $tp < '20:19:00') {
                            $stts2 = 'PSW1';
                        } elseif ($tp > '19:31:00' and $tp < '20:00:00') {
                            $stts2 = 'PSW2';
                        } elseif ($tp > '19:01:00' and $tp < '19:30:00') {
                            $stts2 = 'PSW3';
                        } elseif ($tp < '19:00:00') {
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
                        } elseif ($tm > '21:01:00' and $tm < '21:30:00') {
                            $stts1 = 'TL2';
                        } elseif ($tm > '21:31:00' and $tm < '22:00:00') {
                            $stts1 = 'TL3';
                        } elseif ($tm > '22:01:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '07:31:00' and $tp < '07:49:00') {
                            $stts2 = 'PSW1';
                        } elseif ($tp > '07:01:00' and $tp < '07:30:00') {
                            $stts2 = 'PSW2';
                        } elseif ($tp > '06:31:00' and $tp < '07:00:00') {
                            $stts2 = 'PSW3';
                        } elseif ($tp < '06:30:00') {
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
                        } elseif ($tm > '08:31:00' and $tm < '09:00:00') {
                            $stts1 = 'TL2';
                        } elseif ($tm > '09:01:00' and $tm < '09:30:00') {
                            $stts1 = 'TL3';
                        } elseif ($tm > '09:31:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '13:00:00' and $tp < '13:19:00') {
                            $stts2 = 'PSW1';
                        } elseif ($tp > '13:31:00' and $tp < '14:00:00') {
                            $stts2 = 'PSW2';
                        } elseif ($tp > '13:01:00' and $tp < '13:30:00') {
                            $stts2 = 'PSW3';
                        } elseif ($tp < '13:00:00') {
                            $stts2 = 'PSW4';
                        }
                        break;
                    case ($row['date'] == 'SABTU' and $jam_datang['shift'] == 'Siang'):
                        $interval = 'INTERVAL 5 HOUR';
                        $efektif = 'INTERVAL 1 HOUR';
                        if ($tm > '14:41:00' and $tm < '15:00:00') {
                            $stts1 = 'TL1';
                        } elseif ($tm > '15:01:00' and $tm < '15:30:00') {
                            $stts1 = 'TL2';
                        } elseif ($tm > '15:31:00' and $tm < '16:00:00') {
                            $stts1 = 'TL3';
                        } elseif ($tm > '16:00:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '20:01:00' and $tp < '20:19:00') {
                            $stts2 = 'PSW1';
                        } elseif ($tp > '19:31:00' and $tp < '20:00:00') {
                            $stts2 = 'PSW2';
                        } elseif ($tp > '19:01:00' and $tp < '19:30:00') {
                            $stts2 = 'PSW3';
                        } elseif ($tp < '19:00:00') {
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
                        } elseif ($tm > '21:01:00' and $tm < '21:30:00') {
                            $stts1 = 'TL2';
                        } elseif ($tm > '21:31:00' and $tm < '22:00:00') {
                            $stts1 = 'TL3';
                        } elseif ($tm > '22:01:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '07:31:00' and $tp < '07:49:00') {
                            $stts2 = 'PSW1';
                        } elseif ($tp > '07:01:00' and $tp < '07:30:00') {
                            $stts2 = 'PSW2';
                        } elseif ($tp > '06:31:00' and $tp < '07:00:00') {
                            $stts2 = 'PSW3';
                        } elseif ($tp < '06:30:00') {
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
                        } elseif ($tm > '08:31:00' and $tm < '09:00:00') {
                            $stts1 = 'TL2';
                        } elseif ($tm > '09:01:00' and $tm < '09:30:00') {
                            $stts1 = 'TL3';
                        } elseif ($tm > '09:31:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '14:01:00' and $tp < '14:19:00') {
                            $stts2 = 'PSW1';
                        } elseif ($tp > '13:31:00' and $tp < '14:00:00') {
                            $stts2 = 'PSW2';
                        } elseif ($tp > '13:01:00' and $tp < '13:30:00') {
                            $stts2 = 'PSW3';
                        } elseif ($tp < '13:00:00') {
                            $stts2 = 'PSW4';
                        }
                        break;
                    case ($row['date'] == 'AKHAD' and $jam_datang['shift'] == 'Siang'):
                        $interval = 'INTERVAL 5 HOUR';
                        $efektif = 'INTERVAL 1 HOUR';
                        if ($tm > '14:41:00' and $tm < '15:00:00') {
                            $stts1 = 'TL1';
                        } elseif ($tm > '15:01:00' and $tm < '15:30:00') {
                            $stts1 = 'TL2';
                        } elseif ($tm > '15:31:00' and $tm < '16:00:00') {
                            $stts1 = 'TL3';
                        } elseif ($tm > '16:00:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '20:01:00' and $tp < '20:19:00') {
                            $stts2 = 'PSW1';
                        } elseif ($tp > '19:31:00' and $tp < '20:00:00') {
                            $stts2 = 'PSW2';
                        } elseif ($tp > '19:01:00' and $tp < '19:30:00') {
                            $stts2 = 'PSW3';
                        } elseif ($tp < '19:00:00') {
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
                        } elseif ($tm > '21:01:00' and $tm < '21:30:00') {
                            $stts1 = 'TL2';
                        } elseif ($tm > '21:31:00' and $tm < '22:00:00') {
                            $stts1 = 'TL3';
                        } elseif ($tm > '22:01:00') {
                            $stts1 = 'TL4';
                        }
                        if ($tp > '07:31:00' and $tp < '07:49:00') {
                            $stts2 = 'PSW1';
                        } elseif ($tp > '07:01:00' and $tp < '07:30:00') {
                            $stts2 = 'PSW2';
                        } elseif ($tp > '06:31:00' and $tp < '07:00:00') {
                            $stts2 = 'PSW3';
                        } elseif ($tp < '06:30:00') {
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

                // Optimized: removed redundant DB query and replaced MySQL-specific INTERVAL with PHP calculation
                $durasiSeconds = !empty($row['durasi']) ? strtotime($row['durasi']) - strtotime('TODAY') : 0;
                $efektifSeconds = $this->parseInterval($efektif);
                $intervalSeconds = $this->parseInterval($interval);

                // Calculate efektif (durasi - efektif_interval)
                $diffEfektif = $durasiSeconds - $efektifSeconds;
                $signEfektif = ($diffEfektif < 0) ? '-' : '';
                $diffEfektifAbs = (int) abs($diffEfektif);
                $efektifTime = $signEfektif . sprintf('%02d:%02d:%02d', floor($diffEfektifAbs / 3600), floor($diffEfektifAbs / 60) % 60, $diffEfektifAbs % 60);

                // Calculate kurang (durasi - interval)
                $diffKurang = $durasiSeconds - $intervalSeconds;
                $signKurang = ($diffKurang < 0) ? '-' : '';
                $diffKurangAbs = (int) abs($diffKurang);
                $kurangTime = $signKurang . sprintf('%02d:%02d:%02d', floor($diffKurangAbs / 3600), floor($diffKurangAbs / 60) % 60, $diffKurangAbs % 60);

                $row['efektif'] = [
                    'efektif' => $efektifTime,
                    'kurang' => $kurangTime
                ];
                $row['stts1'] = $stts1;
                $row['stts2'] = $stts2;
                $this->assign['list'][] = $row;
            }
        }

        $secondplus = 0;
        $secondminus = 0;
        foreach ($this->assign['list'] as $time) {
            list($hour, $minute, $second) = explode(':', $time['efektif']['kurang']);
            if (strpos($hour, '-') !== false) {
                $hour = 0 - $hour;
                $secondplus += $hour * 3600;
                $secondplus += $minute * 60;
                $secondplus += $second;
            } else {
                $secondminus += $hour * 3600;
                $secondminus += $minute * 60;
                $secondminus += $second;
            }
        }

        $hours = floor($secondplus / 3600);
        $secondplus -= $hours * 3600;
        $minutes = floor($secondplus / 60);
        $secondplus -= $minutes * 60;
        $timesplus = $hours . ':' . $minutes . ':' . $secondplus;

        $hours = floor($secondminus / 3600);
        $secondminus -= $hours * 3600;
        $minutes = floor($secondminus / 60);
        $secondminus -= $minutes * 60;
        $timesminus = $hours . ':' . $minutes . ':' . $secondminus;


        $this->assign['totalminus'] = '-' . $timesplus;

        $this->assign['totalplus'] = $timesminus;

        $this->assign['getStatus'] = isset($_GET['status']);
        $startYear = 2020;
        $currentYear = date('Y');
        $endYear = $currentYear + 2;
        $tahun = [''];
        for ($i = $startYear; $i <= $endYear; $i++) {
            $tahun[] = (string)$i;
        }
        $this->assign['tahun'] = $tahun;
        $this->assign['bulan'] = array('', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
        $this->assign['tanggal'] = array('', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31');
        $this->assign['bidang'] = $this->db('bidang')->toArray();
        return $this->draw('rekap_presensi.html', ['rekap' => htmlspecialchars_array($this->assign)]);
    }

    public function getCetak_Laporan()
    {
        $phrase = '';
        if (isset($_GET['s']))
            $phrase = $_GET['s'];

        $tgl_kunjungan = date('Y-m-d');
        $tgl_kunjungan_akhir = date('Y-m-d');

        if (isset($_GET['awal'])) {
            $tgl_kunjungan = $_GET['awal'];
        }
        if (isset($_GET['akhir'])) {
            $tgl_kunjungan_akhir = $_GET['akhir'];
        }

        $ruang = '';
        if (isset($_GET['ruang'])) {
            $ruang = $_GET['ruang'];
        }

        $username = $this->core->getUserInfo('username', null, true);

        if ($this->core->getUserInfo('role') == 'admin') {
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
                ->join('pegawai', 'pegawai.id = rekap_presensi.id')
                ->where('jam_datang', '>=', $tgl_kunjungan . ' 00:00:00')
                ->where('jam_datang', '<=', $tgl_kunjungan_akhir . ' 23:59:59')
                ->like('bidang', '%' . $ruang . '%')
                ->like('nama', '%' . $phrase . '%')
                ->asc('jam_datang')
                ->toArray();
        } else {
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
                ->join('pegawai', 'pegawai.id = rekap_presensi.id')
                ->where('jam_datang', '>=', $tgl_kunjungan . ' 00:00:00')
                ->where('jam_datang', '<=', $tgl_kunjungan_akhir . ' 23:59:59')
                ->where('departemen', $this->core->getPegawaiInfo('departemen', $username))
                ->where('bidang', $this->core->getPegawaiInfo('bidang', $username))
                ->like('nama', '%' . $phrase . '%')
                ->asc('jam_datang')
                ->toArray();
        }

        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                $day = array(
                    'Sun' => 'AKHAD',
                    'Mon' => 'SENIN',
                    'Tue' => 'SELASA',
                    'Wed' => 'RABU',
                    'Thu' => 'KAMIS',
                    'Fri' => 'JUMAT',
                    'Sat' => 'SABTU'
                );

                $jam_datang_timestamp = strtotime($row['jam_datang']);
                $jam_datang_arr = [
                    'year' => date('Y', $jam_datang_timestamp),
                    'month' => date('m', $jam_datang_timestamp),
                    'day' => date('d', $jam_datang_timestamp),
                    'shift' => $row['shift']
                ];

                $row['date'] = $day[date('D', $jam_datang_timestamp)];
                $this->assign['list'][] = $row;
            }
        }
        $this->assign['awal'] = $tgl_kunjungan;
        $this->assign['akhir'] = $tgl_kunjungan_akhir;
        $this->assign['ruang'] = $ruang;

        echo $this->draw('cetak.rekap_presensi.html', ['rekap' => htmlspecialchars_array($this->assign)]);
        exit();
    }

    public function getRekap_Bulanan()
    {
        $this->_addHeaderFiles();
        $bulan = date('m');
        if (isset($_GET['b'])) {
            $bulan = $_GET['b'];
        }

        $tahun = date('Y');
        if (isset($_GET['y'])) {
            $tahun = $_GET['y'];
        }

        $ruang = '';
        if (isset($_GET['ruang'])) {
            $ruang = $_GET['ruang'];
        }

        $phrase = '';
        if (isset($_GET['s']))
            $phrase = $_GET['s'];

        $username = $this->core->getUserInfo('username', null, true);

        if ($this->core->getUserInfo('role') == 'admin') {
            $rows = $this->db('rekap_presensi')
                ->select([
                    'id' => 'rekap_presensi.id',
                    'nama' => 'pegawai.nama',
                    'bidang' => 'pegawai.bidang',
                    'jbtn' => 'pegawai.jbtn',
                    'count' => 'COUNT(rekap_presensi.id)'
                ])
                ->join('pegawai', 'pegawai.id = rekap_presensi.id')
                ->where('jam_datang', 'LIKE', "$tahun-$bulan-%")
                ->like('bidang', '%' . $ruang . '%')
                ->like('nama', '%' . $phrase . '%')
                ->group('rekap_presensi.id')
                ->toArray();
        } else {
            $rows = $this->db('rekap_presensi')
                ->select([
                    'id' => 'rekap_presensi.id',
                    'nama' => 'pegawai.nama',
                    'bidang' => 'pegawai.bidang',
                    'jbtn' => 'pegawai.jbtn',
                    'count' => 'COUNT(rekap_presensi.id)'
                ])
                ->join('pegawai', 'pegawai.id = rekap_presensi.id')
                ->where('jam_datang', 'LIKE', "$tahun-$bulan-%")
                ->where('departemen', $this->core->getPegawaiInfo('departemen', $username))
                ->where('bidang', $this->core->getPegawaiInfo('bidang', $username))
                ->like('nama', '%' . $phrase . '%')
                ->group('rekap_presensi.id')
                ->toArray();
        }

        $this->assign['list'] = htmlspecialchars_array($rows);
        $this->assign['bulan_sel'] = $bulan;
        $this->assign['tahun_sel'] = $tahun;
        $this->assign['ruang_sel'] = $ruang;
        $this->assign['bidang'] = $this->db('bidang')->toArray();
        
        $month = array(
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        );
        $this->assign['show_bulan'] = $month[$bulan];
        $this->assign['bulan'] = array('', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');

        $startYear = 2020;
        $currentYear = date('Y');
        $endYear = $currentYear + 2;
        $tahun_list = [''];
        for ($i = $startYear; $i <= $endYear; $i++) {
            $tahun_list[] = (string)$i;
        }
        $this->assign['tahun'] = $tahun_list;
        $this->assign['phrase'] = $phrase;

        return $this->draw('rekap_bulanan.html', ['rekap' => $this->assign]);
    }

    public function getCetak_Rekap_Bulanan()
    {
        $bulan = date('m');
        if (isset($_GET['b'])) {
            $bulan = $_GET['b'];
        }

        $tahun = date('Y');
        if (isset($_GET['y'])) {
            $tahun = $_GET['y'];
        }

        $ruang = '';
        if (isset($_GET['ruang'])) {
            $ruang = $_GET['ruang'];
        }

        $phrase = '';
        if (isset($_GET['s']))
            $phrase = $_GET['s'];

        $username = $this->core->getUserInfo('username', null, true);

        if ($this->core->getUserInfo('role') == 'admin') {
            $rows = $this->db('rekap_presensi')
                ->select([
                    'id' => 'rekap_presensi.id',
                    'nama' => 'pegawai.nama',
                    'bidang' => 'pegawai.bidang',
                    'jbtn' => 'pegawai.jbtn',
                    'count' => 'COUNT(rekap_presensi.id)'
                ])
                ->join('pegawai', 'pegawai.id = rekap_presensi.id')
                ->where('jam_datang', 'LIKE', "$tahun-$bulan-%")
                ->like('bidang', '%' . $ruang . '%')
                ->like('nama', '%' . $phrase . '%')
                ->group('rekap_presensi.id')
                ->toArray();
        } else {
            $rows = $this->db('rekap_presensi')
                ->select([
                    'id' => 'rekap_presensi.id',
                    'nama' => 'pegawai.nama',
                    'bidang' => 'pegawai.bidang',
                    'jbtn' => 'pegawai.jbtn',
                    'count' => 'COUNT(rekap_presensi.id)'
                ])
                ->join('pegawai', 'pegawai.id = rekap_presensi.id')
                ->where('jam_datang', 'LIKE', "$tahun-$bulan-%")
                ->where('departemen', $this->core->getPegawaiInfo('departemen', $username))
                ->where('bidang', $this->core->getPegawaiInfo('bidang', $username))
                ->like('nama', '%' . $phrase . '%')
                ->group('rekap_presensi.id')
                ->toArray();
        }

        $this->assign['list'] = htmlspecialchars_array($rows);
        $this->assign['bulan_sel'] = $bulan;
        $this->assign['tahun_sel'] = $tahun;

        $month = array(
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        );
        $this->assign['show_bulan'] = $month[$bulan];
        $this->assign['phrase'] = $phrase;

        echo $this->draw('cetak.rekap_bulanan.html', ['rekap' => $this->assign]);
        exit();
    }

    public function getGoogleMap($id, $tanggal)
    {
        $geo = $this->db('mlite_geolocation_presensi')->where('id', $id)->where('tanggal', $tanggal)->oneArray();
        $pegawai = $this->db('pegawai')->where('id', $id)->oneArray();

        $this->tpl->set('geo', $geo);
        $this->tpl->set('pegawai', $pegawai);
        echo $this->tpl->draw(MODULES . '/presensi/view/admin/google_map.html', true);
        exit();
    }

    public function getPresensi($page = 1)
    {
        $this->_addHeaderFiles();

        $perpage = '10';
        $phrase = '';
        if (isset($_GET['s']))
            $phrase = $_GET['s'];

        $ruang = '';
        if (isset($_GET['ruang'])) {
            $ruang = $_GET['ruang'];
        }

        $dep = '';
        if (isset($_GET['dep'])) {
            $dep = $_GET['dep'];
        }

        $username = $this->core->getUserInfo('username', null, true);

        // pagination
        if ($this->core->getUserInfo('role') == 'admin') {
            $totalRecords = $this->db('temporary_presensi')
                ->join('pegawai', 'pegawai.id = temporary_presensi.id')
                ->like('departemen', '%' . $dep . '%')
                ->like('bidang', '%' . $ruang . '%')
                ->like('nama', '%' . $phrase . '%')
                ->asc('jam_datang')
                ->toArray();
        } else {
            $totalRecords = $this->db('temporary_presensi')
                ->join('pegawai', 'pegawai.id = temporary_presensi.id')
                ->where('departemen', $this->core->getPegawaiInfo('departemen', $username))
                ->where('bidang', $this->core->getPegawaiInfo('bidang', $username))
                ->like('nama', '%' . $phrase . '%')
                ->asc('jam_datang')
                ->toArray();
        }
        //$pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'presensi', 'presensi', '%d']));
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'presensi', 'presensi', '%d', '?s=' . $phrase . '&ruang=' . $ruang . '&dep=' . $dep]));
        $this->assign['pagination'] = $pagination->nav('pagination', '5');
        $this->assign['totalRecords'] = $totalRecords;

        // list
        $offset = $pagination->offset();
        if ($this->core->getUserInfo('role') == 'admin') {
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
                ->join('pegawai', 'pegawai.id = temporary_presensi.id')
                ->like('departemen', '%' . $dep . '%')
                ->like('bidang', '%' . $ruang . '%')
                ->like('nama', '%' . $phrase . '%')
                ->asc('jam_datang')
                ->offset($offset)
                ->limit($perpage)
                ->toArray();
        } else {
            $rows = $this->db('temporary_presensi')
                ->select([
                    'nama' => 'pegawai.nama',
                    'id' => 'temporary_presensi.id',
                    'shift' => 'temporary_presensi.shift',
                    'jam_datang' => 'temporary_presensi.jam_datang',
                    'status' => 'temporary_presensi.status',
                    'photo' => 'temporary_presensi.photo'
                ])
                ->join('pegawai', 'pegawai.id = temporary_presensi.id')
                ->where('departemen', $this->core->getPegawaiInfo('departemen', $username))
                ->where('bidang', $this->core->getPegawaiInfo('bidang', $username))
                ->like('nama', '%' . $phrase . '%')
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
        $this->assign['bidang'] = htmlspecialchars_array($this->db('bidang')->toArray());
        $this->assign['dep'] = htmlspecialchars_array($this->db('departemen')->toArray());

        return $this->draw('presensi.html', ['presensi' => htmlspecialchars_array($this->assign)]);
    }

    public function getPresensiPulang($id)
    {
        $cek = $this->db('temporary_presensi')->where('id', $id)->oneArray();
        $jam_jaga       = $this->db('jam_jaga')->join('pegawai', 'pegawai.departemen = jam_jaga.dep_id')->where('pegawai.id', $id)->where('jam_jaga.shift', $cek['shift'])->oneArray();
        $location = url([ADMIN, 'presensi', 'presensi']);
        if ($cek) {

            $status = $cek['status'];
            if ((strtotime(date('Y-m-d H:i:s')) - strtotime(date('Y-m-d') . ' ' . $jam_jaga['jam_pulang'])) < 0) {
                $status = $cek['status'] . ' & PSW';
            }

            $awal  = new \DateTime($cek['jam_datang']);
            $akhir = new \DateTime();
            $diff = $akhir->diff($awal, true); // to make the difference to be always positive.
            $durasi = $diff->format('%H:%I:%S');

            $ubah = $this->db('temporary_presensi')
                ->where('id', $id)
                ->save([
                    'jam_pulang' => date('Y-m-d H:i:s'),
                    'status' => $status,
                    'durasi' => $durasi
                ]);

            if ($ubah) {
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
                if ($insert) {
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
        if (isset($_GET['s']))
            $phrase = $_GET['s'];

        // pagination
        $totalRecords = $this->db('barcode')
            ->select('id')
            ->like('barcode', '%' . $phrase . '%')
            ->toArray();
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'presensi', 'barcode', '%d']));
        $this->assign['pagination'] = $pagination->nav('pagination', '5');
        $this->assign['totalRecords'] = $totalRecords;

        // list
        $offset = $pagination->offset();
        $rows = $this->db('barcode')
            ->join('pegawai', 'pegawai.id = barcode.id')
            ->like('barcode', '%' . $phrase . '%')
            ->orLike('nama', '%' . $phrase . '%')
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

        return $this->draw('barcode.manage.html', ['barcode' => htmlspecialchars_array($this->assign)]);
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

        return $this->draw('barcode.form.html', ['barcode' => htmlspecialchars_array($this->assign)]);
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

            return $this->draw('barcode.form.html', ['barcode' => htmlspecialchars_array($this->assign)]);
        } else {
            redirect(url([ADMIN, 'presensi', 'barcode']));
        }
    }

    public function getBarcodeDelete($id)
    {
        if ($this->db('barcode')->delete($id)) {
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

    public function getJamMasuk($page = 1)
    {
        $this->_addHeaderFiles();
        $perpage = '10';
        $phrase = '';
        if (isset($_GET['s']))
            $phrase = $_GET['s'];

        // pagination
        $totalRecords = $this->db('jam_masuk')
            ->like('shift', '%' . $phrase . '%')
            ->toArray();
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'presensi', 'jammasuk', '%d']));
        $this->assign['pagination'] = $pagination->nav('pagination', '5');
        $this->assign['totalRecords'] = $totalRecords;

        // list
        $offset = $pagination->offset();
        $rows = $this->db('jam_masuk')
            ->like('shift', '%' . $phrase . '%')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();

        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                $row['editURL'] = url([ADMIN, 'presensi', 'jammasukedit', $row['shift']]);
                $row['delURL']  = url([ADMIN, 'presensi', 'jammasukhapus', $row['shift']]);
                $this->assign['list'][] = $row;
            }
        }

        $this->assign['getStatus'] = isset($_GET['status']);
        $this->assign['addURL'] = url([ADMIN, 'presensi', 'jammasukadd']);

        return $this->draw('jam_masuk.manage.html', ['jam_masuk' => htmlspecialchars_array($this->assign)]);
    }

    public function getJamMasukAdd()
    {
        $this->_addHeaderFiles();
        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = [
                'shift' => '',
                'jam_masuk' => '',
                'jam_pulang' => ''
            ];
        }

        $this->assign['title'] = 'Tambah Jam Masuk';
        $this->assign['shift_options'] = [
            'Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10',
            'Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10',
            'Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10',
            'Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10',
            'Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10',
            'Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10'
        ];

        return $this->draw('jam_masuk.form.html', ['jam_masuk' => htmlspecialchars_array($this->assign)]);
    }

    public function getJamMasukEdit($shift)
    {
        $this->_addHeaderFiles();
        $row = $this->db('jam_masuk')->where('shift', $shift)->oneArray();
        if (!empty($row)) {
            $this->assign['form'] = $row;
            $this->assign['title'] = 'Edit Jam Masuk';
            $this->assign['shift_options'] = [
                'Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10',
                'Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10',
                'Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10',
                'Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10',
                'Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10',
                'Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10'
            ];

            return $this->draw('jam_masuk.form.html', ['jam_masuk' => htmlspecialchars_array($this->assign)]);
        } else {
            redirect(url([ADMIN, 'presensi', 'jam_masuk']));
        }
    }

    public function postJamMasukSave($shift = null)
    {
        $errors = 0;

        if (!$shift) {
            $location = url([ADMIN, 'presensi', 'jammasukadd']);
        } else {
            $location = url([ADMIN, 'presensi', 'jammasukedit', $shift]);
        }

        if (checkEmptyFields(['shift', 'jam_masuk', 'jam_pulang'], $_POST)) {
            $this->notify('failure', 'Isian kosong');
            redirect($location, $_POST);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (!$shift) {    // new
                $query = $this->db('jam_masuk')->save($_POST);
            } else {        // edit
                $query = $this->db('jam_masuk')->where('shift', $shift)->save($_POST);
            }

            if ($query) {
                $this->notify('success', 'Simpan sukses');
            } else {
                $this->notify('failure', 'Simpan gagal');
            }

            redirect(url([ADMIN, 'presensi', 'jammasuk']));
        }

        redirect($location, $_POST);
    }

    public function getJamMasukHapus($shift)
    {
        if ($this->db('jam_masuk')->where('shift', $shift)->delete()) {
            $this->notify('success', 'Hapus sukses');
        } else {
            $this->notify('failure', 'Hapus gagal');
        }
        redirect(url([ADMIN, 'presensi', 'jammasuk']));
    }

    public function getSettings()
    {
      $this->_addHeaderFiles();
      $this->assign['title'] = 'Pengaturan Presensi';
      $this->assign['presensi'] = htmlspecialchars_array($this->settings('presensi'));
      return $this->draw('settings.html', ['settings' => htmlspecialchars_array($this->assign)]);
    }

    public function postSaveSettings()
    {
      foreach ($_POST['presensi'] as $key => $val) {
        $this->settings('presensi', $key, $val);
      }
      $this->notify('success', 'Pengaturan telah disimpan');
      redirect(url([ADMIN, 'presensi', 'settings']));
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES . '/presensi/js/admin/app.js');
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

    private function parseInterval($intervalStr)
    {
        $seconds = 0;
        if (preg_match('/INTERVAL (\d+) \* 60 \+ (\d+) MINUTE/', $intervalStr, $matches)) {
            $seconds = ($matches[1] * 60 + $matches[2]) * 60;
        } elseif (preg_match('/INTERVAL (\d+) HOUR/', $intervalStr, $matches)) {
            $seconds = $matches[1] * 3600;
        } elseif (preg_match('/INTERVAL (\d+) MINUTE/', $intervalStr, $matches)) {
            $seconds = $matches[1] * 60;
        }
        return $seconds;
    }

    public function apiList()
    {
        $username = $this->core->checkAuth('GET');
        if (!$this->core->checkPermission($username, 'can_read', 'presensi')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $pegawai = $this->db('pegawai')->where('nik', $username)->oneArray();
        if (!$pegawai) {
            return ['status' => 'error', 'message' => 'Pegawai tidak ditemukan'];
        }

        $idpeg = $this->db('barcode')->where('id', $pegawai['id'])->oneArray();
        if (!$idpeg) {
            return ['status' => 'error', 'message' => 'Pegawai tidak terdaftar di tabel barcode'];
        }
        
        $id = $idpeg['id'];

        $draw = $_GET['draw'] ?? 0;
        $start = $_GET['start'] ?? 0;
        $length = $_GET['length'] ?? 10;
        $tgl_awal = $_GET['tgl_awal'] ?? date('Y-m-01');
        $tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-t');

        $totalRecords = $this->db('rekap_presensi')
            ->where('id', $idpeg['id'])
            ->where('jam_datang', '>=', $tgl_awal . ' 00:00:00')
            ->where('jam_datang', '<=', $tgl_akhir . ' 23:59:59')
            ->count();

        $data = $this->db('rekap_presensi')
            ->where('id', $idpeg['id'])
            ->where('jam_datang', '>=', $tgl_awal . ' 00:00:00')
            ->where('jam_datang', '<=', $tgl_akhir . ' 23:59:59')
            ->asc('jam_datang')
            ->offset($start)
            ->limit($length)
            ->toArray();

        return [
            "status" => "success",
            "data" => $data,
            "meta" => [
                "page" => floor($start / $length) + 1,
                "per_page" => intval($length),
                "total" => $totalRecords
            ]
        ];
    }

    public function apiShow()
    {
        $username = $this->core->checkAuth('GET');
        if (!$this->core->checkPermission($username, 'can_read', 'presensi')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $pegawai = $this->db('pegawai')->where('nik', $username)->oneArray();
        if (!$pegawai) {
            return ['status' => 'error', 'message' => 'Pegawai tidak ditemukan'];
        }

        $idpeg = $this->db('barcode')->where('id', $pegawai['id'])->oneArray();
        if (!$idpeg) {
            return ['status' => 'error', 'message' => 'Pegawai tidak terdaftar di tabel barcode'];
        }
        
        $id = $idpeg['id'];

        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');

        $data = $this->db('rekap_presensi')
            ->where('id', $id)
            ->like('jam_datang', '%' . $tanggal . '%')
            ->toArray();

        $temp = $this->db('temporary_presensi')
            ->where('id', $id)
            ->oneArray();

        $pegawai = $this->db('pegawai')->where('id', $id)->oneArray();
        $jam_jaga = [];
        if ($pegawai && !empty($pegawai['departemen'])) {
            $jam_jaga = $this->db('jam_jaga')->where('dep_id', $pegawai['departemen'])->toArray();
        }

        return [
            "status" => "success",
            "data" => [
                "rekap" => $data,
                "ongoing" => $temp,
                "shifts" => $jam_jaga
            ]
        ];
    }

    public function apiCreate()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_create', 'presensi')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        $pegawai = $this->db('pegawai')->where('nik', $username)->oneArray();
        if (!$pegawai) {
            return ['status' => 'error', 'message' => 'Pegawai tidak ditemukan'];
        }

        $idpeg = $this->db('barcode')->where('id', $pegawai['id'])->oneArray();
        if (!$idpeg) {
            return ['status' => 'error', 'message' => 'Pegawai tidak terdaftar di tabel barcode'];
        }
        
        $id = $idpeg['id'];

        $shift = $input['shift'] ?? '';
        $urlnya = $input['photo'] ?? '';

        $bulan = date('m');
        $tahun = date('Y');
        $hari = date('j');

        $jam_jaga = $this->db('jam_jaga')->join('pegawai', 'pegawai.departemen = jam_jaga.dep_id')->where('pegawai.id', $id)->where('jam_jaga.shift', $shift)->oneArray();

        $jadwal_pegawai = $this->db('jadwal_pegawai')->where('id', $id)->where('h' . $hari, $jam_jaga['shift'] ?? '')->where('bulan', $bulan)->where('tahun', $tahun)->oneArray();
        $jadwal_tambahan = $this->db('jadwal_tambahan')->where('id', $id)->where('h' . $hari, $jam_jaga['shift'] ?? '')->where('bulan', $bulan)->where('tahun', $tahun)->oneArray();
        $isFullAbsen = $this->db('rekap_presensi')->where('id', $id)->where('shift', $jam_jaga['shift'] ?? '')->like('jam_datang', date('Y-m-d') . '%')->oneArray();
        $isAbsen = $this->db('temporary_presensi')->where('id', $id)->oneArray();

        $set_keterlambatan  = $this->db('set_keterlambatan')->oneArray();
        $toleransi      = (int)($set_keterlambatan['toleransi'] ?? 0);
        $terlambat1     = (int)($set_keterlambatan['terlambat1'] ?? 0);
        $terlambat2     = (int)($set_keterlambatan['terlambat2'] ?? 0);

        if ($isFullAbsen) {
            return ['status' => 'error', 'message' => 'Anda sudah presensi untuk tanggal ' . date('Y-m-d')];
        }
        if ($isAbsen) {
            return ['status' => 'error', 'message' => 'Anda belum melakukan clock out untuk shift ' . $isAbsen['shift']];
        }

        if (!$jadwal_pegawai && !$jadwal_tambahan) {
            return ['status' => 'error', 'message' => 'ID Pegawai atau jadwal shift tidak sesuai!'];
        }

        if (empty($shift)) {
            return ['status' => 'error', 'message' => 'Pilih shift dulu'];
        }

        $status_presensi = 'Tepat Waktu';
        $jam_masuk = $jam_jaga['jam_masuk'] ?? '00:00:00';

        $diff_sec = strtotime(date('Y-m-d H:i:s')) - strtotime(date('Y-m-d') . ' ' . $jam_masuk);
        if ($diff_sec > ($toleransi * 60)) {
            $status_presensi = 'Terlambat Toleransi';
        }
        if ($diff_sec > ($terlambat1 * 60)) {
            $status_presensi = 'Terlambat I';
        }
        if ($diff_sec > ($terlambat2 * 60)) {
            $status_presensi = 'Terlambat II';
        }

        $keterlambatan = '00:00:00';
        if ($diff_sec > ($toleransi * 60)) {
            $awal  = new \DateTime(date('Y-m-d') . ' ' . $jam_masuk);
            $akhir = new \DateTime();
            $diffFormat = $akhir->diff($awal, true);
            $keterlambatan = ($awal > $akhir) ? '' : $diffFormat->format('%H:%I:%S');
        }

        $jam_datang = date('Y-m-d H:i:s');
        try {
            $this->db('temporary_presensi')->save([
                'id' => $id,
                'shift' => $jam_jaga['shift'],
                'jam_datang' => $jam_datang,
                'jam_pulang' => NULL,
                'status' => $status_presensi,
                'keterlambatan' => $keterlambatan,
                'durasi' => '',
                'photo' => $urlnya
            ]);

            if (isset($input['lat'], $input['lng'])) {
                if (!$this->db('mlite_geolocation_presensi')->where('id', $id)->where('tanggal', date('Y-m-d'))->oneArray()) {
                    $this->db('mlite_geolocation_presensi')->save([
                        'id' => $id,
                        'tanggal' => date('Y-m-d'),
                        'latitude' => $input['lat'],
                        'longitude' => $input['lng']
                    ]);
                }
            }

            return ['status' => 'created', 'message' => 'Presensi Masuk jam ' . $jam_masuk . ' ' . $status_presensi . ' ' . $keterlambatan];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiUpdate()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_update', 'presensi')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $pegawai = $this->db('pegawai')->where('nik', $username)->oneArray();
        if (!$pegawai) {
            return ['status' => 'error', 'message' => 'Pegawai tidak ditemukan'];
        }

        $idpeg = $this->db('barcode')->where('id', $pegawai['id'])->oneArray();
        if (!$idpeg) {
            return ['status' => 'error', 'message' => 'Pegawai tidak terdaftar di tabel barcode'];
        }
        
        $id = $idpeg['id'];

        $isAbsen = $this->db('temporary_presensi')->where('id', $id)->oneArray();
        if (!$isAbsen) {
            return ['status' => 'error', 'message' => 'Anda belum presensi masuk / tidak ditemukan (temporary_presensi)'];
        }

        $jam_jaga = $this->db('jam_jaga')->join('pegawai', 'pegawai.departemen = jam_jaga.dep_id')->where('pegawai.id', $id)->where('jam_jaga.shift', $isAbsen['shift'])->oneArray();

        $jamDatang = substr($isAbsen['jam_datang'], 11);
        if ((strtotime(date('Y-m-d H:i:s')) - strtotime(date('Y-m-d') . ' ' . $jamDatang)) < 2 * 60) {
           return ['status' => 'error', 'message' => 'Sabar ... Jangan pencet terus'];
        }

        $status = $isAbsen['status'];
        $dayShift = date('Y-m-d');
        if ($isAbsen['shift'] == 'Malam') {
            $dayShift = substr($isAbsen['jam_datang'], 0, 10);
            $dayShift = date('Y-m-d', strtotime($dayShift . ' +1 day'));
        }
        $jam_pulang = $jam_jaga['jam_pulang'] ?? '00:00:00';
        if ((strtotime(date('Y-m-d H:i:s')) - strtotime($dayShift . ' ' . $jam_pulang)) < 0) {
            $status = $isAbsen['status'] . ' & PSW';
        }

        $awal  = new \DateTime($isAbsen['jam_datang']);
        $akhir = new \DateTime();
        $diff = $akhir->diff($awal, true);
        $durasi = $diff->format('%H:%I:%S');
        $jam_pulang_sekarang = date('Y-m-d H:i:s');

        try {
            $ubah = $this->db('temporary_presensi')
                ->where('id', $id)
                ->save([
                    'jam_pulang' => $jam_pulang_sekarang,
                    'status' => $status,
                    'durasi' => $durasi
                ]);

            if ($ubah) {
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
                if ($insert) {
                    $this->db('temporary_presensi')->where('id', $id)->delete();
                    return ['status' => 'updated', 'message' => 'Presensi pulang telah disimpan'];
                }
            }
            return ['status' => 'error', 'message' => 'Gagal mengubah presensi sementara'];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiDelete()
    {
        $username = $this->core->checkAuth('DELETE');
        if (!$this->core->checkPermission($username, 'can_delete', 'presensi')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $pegawai = $this->db('pegawai')->where('nik', $username)->oneArray();
        if (!$pegawai) {
            return ['status' => 'error', 'message' => 'Pegawai tidak ditemukan'];
        }

        $idpeg = $this->db('barcode')->where('id', $pegawai['id'])->oneArray();
        if (!$idpeg) {
            return ['status' => 'error', 'message' => 'Pegawai tidak terdaftar di tabel barcode'];
        }
        
        $id = $idpeg['id'];

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        $jam_datang = $input['jam_datang'] ?? '';
        if (empty($jam_datang)) {
            return ['status' => 'error', 'message' => 'jam_datang missing in body payload'];
        }

        try {
            $rekap = $this->db('rekap_presensi')->where('id', $id)->where('jam_datang', $jam_datang)->oneArray();
            if ($rekap) {
                $this->db('rekap_presensi')->where('id', $id)->where('jam_datang', $jam_datang)->delete();
                return ['status' => 'deleted', 'message' => 'Rekap presensi dihapus'];
            }

            $temp = $this->db('temporary_presensi')->where('id', $id)->where('jam_datang', $jam_datang)->oneArray();
            if ($temp) {
                $this->db('temporary_presensi')->where('id', $id)->where('jam_datang', $jam_datang)->delete();
                return ['status' => 'deleted', 'message' => 'Temporary presensi dihapus'];
            }

            return ['status' => 'error', 'message' => 'Data tidak ditemukan'];

        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

}
