<?php

namespace Plugins\Presensi;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Presensi Masuk' => 'presensi',
            'Rekap Presensi' => 'rekap_presensi',
            'Barcode Presensi' => 'barcode',
            'Jam Jaga' => 'jamjaga',
            'Jadwal Pegawai' => 'jadwal'
        ];
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

        $status = '1';
        if(isset($_GET['status']))
          $status = $_GET['status'];

        // pagination
        $totalRecords = $this->db('jadwal_pegawai')
            ->join('pegawai','pegawai.id=jadwal_pegawai.id')
            ->where('jadwal_pegawai.tahun',date('Y'))
            ->where('jadwal_pegawai.bulan',date('m'))
            // ->like('pegawai.nama', '%'.$phrase.'%')
            ->toArray();
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'presensi', 'jadwal', '%d']));
        $this->assign['pagination'] = $pagination->nav('pagination','5');
        $this->assign['totalRecords'] = $totalRecords;

        // list
        $offset = $pagination->offset();
        $rows = $this->db('jadwal_pegawai')
            ->join('pegawai','pegawai.id=jadwal_pegawai.id')
            ->where('jadwal_pegawai.tahun',date('Y'))
            ->where('jadwal_pegawai.bulan',date('m'))
            // ->where('pegawai.nama', '%'.$phrase.'%')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();

        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                $row['editURL'] = url([ADMIN, 'presensi', 'jadwaledit', $row['id']]);
                // $row['delURL']  = url([ADMIN, 'master', 'petugasdelete', $row['nip']]);
                // $row['restoreURL']  = url([ADMIN, 'master', 'petugasrestore', $row['nip']]);
                // $row['viewURL'] = url([ADMIN, 'master', 'petugasview', $row['nip']]);
                $this->assign['list'][] = $row;
            }
        }

        $this->assign['getStatus'] = isset($_GET['status']);
        $this->assign['addURL'] = url([ADMIN, 'presensi', 'jadwaladd']);
        // $this->assign['printURL'] = url([ADMIN, 'master', 'petugasprint']);

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

        $this->assign['id'] = $this->db('pegawai')->toArray();
        $this->assign['h1'] = array('','PAGI', 'SIANG', 'MALAM');
        $this->assign['tahun'] = date('Y');
        $this->assign['bulan'] = array('','01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');

        return $this->draw('jadwal.form.html', ['jadwal' => $this->assign]);
    }

    public function getJadwalEdit($id)
    {
        $this->_addHeaderFiles();
        $row = $this->db('jadwal_pegawai')->where('id', $id)->where('tahun', date('Y'))->where('bulan', date('m'))->oneArray();
        if (!empty($row)){
            $this->assign['form'] = $row;
            $this->assign['id'] = $this->db('pegawai')->toArray();
            $this->assign['h1'] = array('','Pagi', 'Siang', 'Malam');
            $this->assign['tahun'] = date('Y');
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

    public function getRekap_Presensi($page = 1)
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
        // $date = date();
        // $dt = $date->format('Y-m');
        $totalRecords = $this->db('rekap_presensi')
            ->join('pegawai','pegawai.id = rekap_presensi.id')
            ->like('jam_datang', '%'.date('Y-m').'%')
            ->asc('jam_datang')
            ->toArray();
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'presensi', 'rekap_presensi', '%d']));
        $this->assign['pagination'] = $pagination->nav('pagination','5');
        $this->assign['totalRecords'] = $totalRecords;

        // list
        $offset = $pagination->offset();
        $rows = $this->db('rekap_presensi')
            ->select([
              'nama' => 'pegawai.nama',
              'id' => 'rekap_presensi.id',
              'shift' => 'rekap_presensi.shift',
              'jam_datang' => 'rekap_presensi.jam_datang',
              'jam_pulang' => 'rekap_presensi.jam_pulang',
              'status' => 'rekap_presensi.status',
              'durasi' => 'rekap_presensi.durasi',
              'photo' => 'rekap_presensi.photo'
            ])
            ->join('pegawai','pegawai.id = rekap_presensi.id')
            ->like('jam_datang', '%'.date('Y-m').'%')
            ->asc('jam_datang')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();

        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                $row['mapURL']  = url([ADMIN, 'presensi', 'googlemap', $row['id'], date('Y-m-d', strtotime($row['jam_datang']))]);
                $this->assign['list'][] = $row;
            }
        }

        $this->assign['getStatus'] = isset($_GET['status']);

        return $this->draw('rekap_presensi.html', ['rekap' => $this->assign]);
    }

    public function getGoogleMap($id,$tanggal)
    {
      $geo = $this->db('geolocation_presensi')->where('id', $id)->where('tanggal', $tanggal)->oneArray();
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

        // pagination
        $totalRecords = $this->db('temporary_presensi')
            ->join('pegawai','pegawai.id = temporary_presensi.id')
            ->like('jam_datang', '%'.date('Y-m').'%')
            ->asc('jam_datang')
            ->toArray();
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'presensi', 'presensi', '%d']));
        $this->assign['pagination'] = $pagination->nav('pagination','5');
        $this->assign['totalRecords'] = $totalRecords;

        // list
        $offset = $pagination->offset();
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
            ->like('jam_datang', '%'.date('Y-m').'%')
            ->asc('jam_datang')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();

        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                $row['mapURL']  = url([ADMIN, 'presensi', 'googlemap', $row['id'], date('Y-m-d', strtotime($row['jam_datang']))]);
                $this->assign['list'][] = $row;
            }
        }

        return $this->draw('presensi.html', ['presensi' => $this->assign]);
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

    public function getAjax()
    {
        header('Content-type: text/html');
        $show = isset($_GET['show']) ? $_GET['show'] : "";
        switch($show){
            default;
            break;
            case "jam_masuk";
            $rows = $this->db('jam_masuk')->where('shift', '%'.$_GET['shift'].'%')->toArray();
            foreach ($rows as $row) {
                $array[] = array(
                    'jam_masuk' => $row['jam_masuk'],
                    'jam_pulang' => $row['jam_pulang']
                );
            }
            echo json_encode($array, true);
            break;
        }
        exit();
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
        $this->core->addCSS(url('assets/jscripts/lightbox/lightbox.min.css'));

        // JS
        $this->core->addJS(url('assets/jscripts/lightbox/lightbox.min.js'), 'footer');

        // MODULE SCRIPTS
        $this->core->addJS(url([ADMIN, 'presensi', 'javascript']), 'footer');
    }
}
