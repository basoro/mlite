<?php

namespace Plugins\Vedika;

use Systems\AdminModule;
use Systems\Lib\BpjsService;

class Admin extends AdminModule
{
    private $_uploads = WEBAPPS_PATH.'/berkasrawat/pages/upload';

    public function init()
    {
      $this->consid = $this->settings->get('settings.BpjsConsID');
      $this->secretkey = $this->settings->get('settings.BpjsSecretKey');
      $this->user_key = $this->settings->get('settings.BpjsUserKey');
      $this->api_url = $this->settings->get('settings.BpjsApiUrl');
      $this->vclaim_version = $this->settings->get('settings.vClaimVersion');
    }

    public function navigation()
    {
        return [
            'Manage' => 'manage',
            'Index' => 'index',
            'Pengajuan' => 'pengajuan',
            'Perbaikan' => 'perbaikan',
            'Pengaturan' => 'settings',
        ];
    }

    public function getManage()
    {
      $sub_modules = [
        ['name' => 'Index', 'url' => url([ADMIN, 'vedika', 'index']), 'icon' => 'code', 'desc' => 'Index Vedika'],
        ['name' => 'Pengajuan', 'url' => url([ADMIN, 'vedika', 'pengajuan']), 'icon' => 'code', 'desc' => 'Index Pengajuan Vedika'],
        ['name' => 'Perbaikan', 'url' => url([ADMIN, 'vedika', 'perbaikan']), 'icon' => 'code', 'desc' => 'Index Perbaikan Vedika'],
        ['name' => 'Pengaturan', 'url' => url([ADMIN, 'vedika', 'settings']), 'icon' => 'code', 'desc' => 'Pengaturan Vedika'],
      ];
      return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
    }

    public function anyIndex($type = 'ralan', $page = 1)
    {
      if(isset($_POST['submit'])) {
        if(!$this->db('mlite_vedika')->where('nosep', $_POST['nosep'])->oneArray()) {
          $simpan_status = $this->db('mlite_vedika')->save([
            'id' => NULL,
            'tanggal' => date('Y-m-d'),
            'no_rkm_medis' => $_POST['no_rkm_medis'],
            'no_rawat' => $_POST['no_rawat'],
            'tgl_registrasi' => $_POST['tgl_registrasi'],
            'nosep' => $_POST['nosep'],
            'jenis' => $_POST['jnspelayanan'],
            'status' => $_POST['status'],
            'username' => $this->core->getUserInfo('username', null, true)
          ]);
        } else {
          $simpan_status = $this->db('mlite_vedika')
          ->where('nosep', $_POST['nosep'])
          ->save([
            'tanggal' => date('Y-m-d'),
            'status' => $_POST['status']
          ]);
        }
        if($simpan_status) {
          $this->db('mlite_vedika_feedback')->save([
            'id' => NULL,
            'nosep' => $_POST['nosep'],
            'tanggal' => date('Y-m-d'),
            'catatan' => $_POST['catatan'],
            'username' => $this->core->getUserInfo('username', null, true)
          ]);
        }
      }

      if(isset($_POST['simpanberkas'])) {
        $dir    = $this->_uploads;
        $cntr   = 0;

        $image = $_FILES['files']['tmp_name'];
        $img = new \Systems\Lib\Image();
        $id = convertNorawat($_POST['no_rawat']);
        if ($img->load($image)) {
            $imgName = time().$cntr++;
            $imgPath = $dir.'/'.$id.'_'.$imgName.'.'.$img->getInfos('type');
            $lokasi_file = 'pages/upload/'.$id.'_'.$imgName.'.'.$img->getInfos('type');
            $img->save($imgPath);
            $query = $this->db('berkas_digital_perawatan')->save(['no_rawat' => $_POST['no_rawat'], 'kode' => $_POST['kode'], 'lokasi_file' => $lokasi_file]);
            if($query) {
              $this->notify('success', 'Simpan berkar digital perawatan sukses.');
            }
        }
      }

      $this->_addHeaderFiles();
      $start_date = date('Y-m-d');
      if(isset($_GET['start_date']) && $_GET['start_date'] !='')
        $start_date = $_GET['start_date'];
      $end_date = date('Y-m-d');
      if(isset($_GET['end_date']) && $_GET['end_date'] !='')
        $end_date = $_GET['end_date'];
      $perpage = '10';
      $phrase = '';
      if(isset($_GET['s']))
        $phrase = $_GET['s'];

      // pagination
      $totalRecords = $this->db()->pdo()->prepare("SELECT reg_periksa.no_rawat FROM reg_periksa, pasien, penjab WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.kd_pj = penjab.kd_pj AND penjab.png_jawab LIKE '%BPJS%' AND (reg_periksa.no_rkm_medis LIKE ? OR reg_periksa.no_rawat LIKE ? OR pasien.nm_pasien LIKE ?) AND reg_periksa.tgl_registrasi BETWEEN '$start_date' AND '$end_date' AND reg_periksa.status_lanjut = 'Ralan'");
      $totalRecords->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
      $totalRecords = $totalRecords->fetchAll();

      $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url([ADMIN, 'vedika', 'index', $type, '%d?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]));
      $this->assign['pagination'] = $pagination->nav('pagination','5');
      $this->assign['totalRecords'] = $totalRecords;

      $offset = $pagination->offset();
      $query = $this->db()->pdo()->prepare("SELECT reg_periksa.*, pasien.*, dokter.nm_dokter, poliklinik.nm_poli, penjab.png_jawab FROM reg_periksa, pasien, dokter, poliklinik, penjab WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.kd_dokter = dokter.kd_dokter AND reg_periksa.kd_poli = poliklinik.kd_poli AND reg_periksa.kd_pj = penjab.kd_pj AND penjab.png_jawab LIKE '%BPJS%' AND (reg_periksa.no_rkm_medis LIKE ? OR reg_periksa.no_rawat LIKE ? OR pasien.nm_pasien LIKE ?) AND reg_periksa.tgl_registrasi BETWEEN '$start_date' AND '$end_date' AND reg_periksa.status_lanjut = 'Ralan' LIMIT $perpage OFFSET $offset");
      $query->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
      $rows = $query->fetchAll();

      if($type == 'ranap') {
        // pagination
        $totalRecords = $this->db()->pdo()->prepare("SELECT reg_periksa.no_rawat FROM reg_periksa, pasien, penjab WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.kd_pj = penjab.kd_pj AND penjab.png_jawab LIKE '%BPJS%' AND (reg_periksa.no_rkm_medis LIKE ? OR reg_periksa.no_rawat LIKE ? OR pasien.nm_pasien LIKE ?) AND reg_periksa.tgl_registrasi BETWEEN '$start_date' AND '$end_date' AND reg_periksa.status_lanjut = 'Ranap'");
        $totalRecords->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
        $totalRecords = $totalRecords->fetchAll();

        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url([ADMIN, 'vedika', 'index', $type, '%d?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]));
        $this->assign['pagination'] = $pagination->nav('pagination','5');
        $this->assign['totalRecords'] = $totalRecords;

        $offset = $pagination->offset();
        $query = $this->db()->pdo()->prepare("SELECT reg_periksa.*, pasien.*, dokter.nm_dokter, poliklinik.nm_poli, penjab.png_jawab FROM reg_periksa, pasien, dokter, poliklinik, penjab WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.kd_dokter = dokter.kd_dokter AND reg_periksa.kd_poli = poliklinik.kd_poli AND reg_periksa.kd_pj = penjab.kd_pj AND penjab.png_jawab LIKE '%BPJS%' AND (reg_periksa.no_rkm_medis LIKE ? OR reg_periksa.no_rawat LIKE ? OR pasien.nm_pasien LIKE ?) AND reg_periksa.tgl_registrasi BETWEEN '$start_date' AND '$end_date' AND reg_periksa.status_lanjut = 'Ranap' LIMIT $perpage OFFSET $offset");
        $query->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
        $rows = $query->fetchAll();
      }
      $this->assign['list'] = [];
      if (count($rows)) {
          foreach ($rows as $row) {
              $berkas_digital = $this->db('berkas_digital_perawatan')
                ->join('master_berkas_digital', 'master_berkas_digital.kode=berkas_digital_perawatan.kode')
                ->where('berkas_digital_perawatan.no_rawat', $row['no_rawat'])
                ->asc('master_berkas_digital.nama')
                ->toArray();
              $galleri_pasien = $this->db('mlite_pasien_galleries_items')
                ->join('mlite_pasien_galleries', 'mlite_pasien_galleries.id = mlite_pasien_galleries_items.gallery')
                ->where('mlite_pasien_galleries.slug', $row['no_rkm_medis'])
                ->toArray();

              $berkas_digital_pasien = array();
              if (count($galleri_pasien)) {
                  foreach ($galleri_pasien as $galleri) {
                      $galleri['src'] = unserialize($galleri['src']);

                      if (!isset($galleri['src']['sm'])) {
                          $galleri['src']['sm'] = isset($galleri['src']['xs']) ? $galleri['src']['xs'] : $galleri['src']['lg'];
                      }

                      $berkas_digital_pasien[] = $galleri;
                  }
              }

              $row = htmlspecialchars_array($row);
              $row['no_sep'] = $this->_getSEPInfo('no_sep', $row['no_rawat']);
              $row['no_peserta'] = $this->_getSEPInfo('no_kartu', $row['no_rawat']);
              $row['no_rujukan'] = $this->_getSEPInfo('no_rujukan', $row['no_rawat']);
              $row['kd_penyakit'] = $this->_getDiagnosa('kd_penyakit', $row['no_rawat'], $row['status_lanjut']);
              $row['nm_penyakit'] = $this->_getDiagnosa('nm_penyakit', $row['no_rawat'], $row['status_lanjut']);
              $row['berkas_digital'] = $berkas_digital;
              $row['berkas_digital_pasien'] = $berkas_digital_pasien;
              $row['formSepURL'] = url([ADMIN, 'vedika', 'formsepvclaim', '?no_rawat='.$row['no_rawat']]);
              $row['pdfURL'] = url([ADMIN, 'vedika', 'pdf', $this->convertNorawat($row['no_rawat'])]);
              $row['setstatusURL']  = url([ADMIN, 'vedika', 'setstatus', $this->_getSEPInfo('no_sep', $row['no_rawat'])]);
              $row['status_pengajuan'] = $this->db('mlite_vedika')->where('nosep', $this->_getSEPInfo('no_sep', $row['no_rawat']))->desc('id')->limit(1)->toArray();
              $row['berkasPasien'] = url([ADMIN, 'vedika', 'berkaspasien', $this->getRegPeriksaInfo('no_rkm_medis', $row['no_rawat'])]);
              $row['berkasPerawatan'] = url([ADMIN, 'vedika', 'berkasperawatan', $this->convertNorawat($row['no_rawat'])]);
              $this->assign['list'][] = $row;
          }
      }

      $this->core->addCSS(url('assets/jscripts/lightbox/lightbox.min.css'));
      $this->core->addJS(url('assets/jscripts/lightbox/lightbox.min.js'));

      $this->assign['searchUrl'] =  url([ADMIN, 'vedika', 'index', $type, $page.'?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]);
      $this->assign['ralanUrl'] =  url([ADMIN, 'vedika', 'index', 'ralan', $page.'?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]);
      $this->assign['ranapUrl'] =  url([ADMIN, 'vedika', 'index', 'ranap', $page.'?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]);
      return $this->draw('index.html', ['tab' => $type, 'vedika' => $this->assign]);

    }

    public function anyPengajuan($type = 'ralan', $page = 1)
    {
      if(isset($_POST['submit'])) {
        $this->db('mlite_vedika')->save([
          'id' => NULL,
          'tanggal' => date('Y-m-d'),
          'no_rkm_medis' => $_POST['no_rkm_medis'],
          'no_rawat' => $_POST['no_rawat'],
          'nosep' => $_POST['nosep'],
          'catatan' => $_POST['catatan'],
          'status' => $_POST['status'],
          'username' => $this->core->getUserInfo('username', null, true)
        ]);
      }

      if(isset($_POST['simpanberkas'])) {
        $dir    = $this->_uploads;
        $cntr   = 0;

        $image = $_FILES['files']['tmp_name'];
        $img = new \Systems\Lib\Image();
        $id = convertNorawat($_POST['no_rawat']);
        if ($img->load($image)) {
            $imgName = time().$cntr++;
            $imgPath = $dir.'/'.$id.'_'.$imgName.'.'.$img->getInfos('type');
            $lokasi_file = 'pages/upload/'.$id.'_'.$imgName.'.'.$img->getInfos('type');
            $img->save($imgPath);
            $query = $this->db('berkas_digital_perawatan')->save(['no_rawat' => $_POST['no_rawat'], 'kode' => $_POST['kode'], 'lokasi_file' => $lokasi_file]);
            if($query) {
              $this->notify('success', 'Simpan berkar digital perawatan sukses.');
            }
        }
      }

      $this->_addHeaderFiles();
      $start_date = date('Y-m-d');
      if(isset($_GET['start_date']) && $_GET['start_date'] !='')
        $start_date = $_GET['start_date'];
      $end_date = date('Y-m-d');
      if(isset($_GET['end_date']) && $_GET['end_date'] !='')
        $end_date = $_GET['end_date'];
      $perpage = '10';
      $phrase = '';
      if(isset($_GET['s']))
        $phrase = $_GET['s'];

      // pagination
      $totalRecords = $this->db()->pdo()->prepare("SELECT no_rawat FROM mlite_vedika WHERE status = 'Pengajuan' AND jenis = '2' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND tgl_registrasi BETWEEN '$start_date' AND '$end_date'");
      $totalRecords->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
      $totalRecords = $totalRecords->fetchAll();

      $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url([ADMIN, 'vedika', 'index', $type, '%d?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]));
      $this->assign['pagination'] = $pagination->nav('pagination','5');
      $this->assign['totalRecords'] = $totalRecords;

      $offset = $pagination->offset();
      $query = $this->db()->pdo()->prepare("SELECT * FROM mlite_vedika WHERE status = 'Pengajuan' AND jenis = '2' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND tgl_registrasi BETWEEN '$start_date' AND '$end_date' ORDER BY nosep LIMIT $perpage OFFSET $offset");
      $query->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
      $rows = $query->fetchAll();

      if($type == 'ranap') {
        // pagination
        $totalRecords = $this->db()->pdo()->prepare("SELECT no_rawat FROM mlite_vedika WHERE status = 'Pengajuan' AND jenis = '1' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND tgl_registrasi BETWEEN '$start_date' AND '$end_date'");
        $totalRecords->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
        $totalRecords = $totalRecords->fetchAll();

        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url([ADMIN, 'vedika', 'pengajuan', $type, '%d?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]));
        $this->assign['pagination'] = $pagination->nav('pagination','5');
        $this->assign['totalRecords'] = $totalRecords;

        $offset = $pagination->offset();
        $query = $this->db()->pdo()->prepare("SELECT * FROM mlite_vedika WHERE status = 'Pengajuan' AND jenis = '1' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND tgl_registrasi BETWEEN '$start_date' AND '$end_date' ORDER BY nosep LIMIT $perpage OFFSET $offset");
        $query->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
        $rows = $query->fetchAll();
      }
      $this->assign['list'] = [];
      if (count($rows)) {
          foreach ($rows as $row) {
              $berkas_digital = $this->db('berkas_digital_perawatan')
                ->join('master_berkas_digital', 'master_berkas_digital.kode=berkas_digital_perawatan.kode')
                ->where('berkas_digital_perawatan.no_rawat', $row['no_rawat'])
                ->asc('master_berkas_digital.nama')
                ->toArray();
              $galleri_pasien = $this->db('mlite_pasien_galleries_items')
                ->join('mlite_pasien_galleries', 'mlite_pasien_galleries.id = mlite_pasien_galleries_items.gallery')
                ->where('mlite_pasien_galleries.slug', $row['no_rkm_medis'])
                ->toArray();

              $berkas_digital_pasien = array();
              if (count($galleri_pasien)) {
                  foreach ($galleri_pasien as $galleri) {
                      $galleri['src'] = unserialize($galleri['src']);

                      if (!isset($galleri['src']['sm'])) {
                          $galleri['src']['sm'] = isset($galleri['src']['xs']) ? $galleri['src']['xs'] : $galleri['src']['lg'];
                      }

                      $berkas_digital_pasien[] = $galleri;
                  }
              }

              $row = htmlspecialchars_array($row);
              $row['nm_pasien'] = $this->core->getPasienInfo('nm_pasien', $row['no_rkm_medis']);
              $row['almt_pj'] = $this->core->getPasienInfo('alamat', $row['no_rkm_medis']);
              $row['jk'] = $this->core->getPasienInfo('jk', $row['no_rkm_medis']);
              $row['umur'] = $this->core->getRegPeriksaInfo('umurdaftar', $row['no_rawat']);
              $row['sttsumur'] = $this->core->getRegPeriksaInfo('sttsumur', $row['no_rawat']);
              $row['tgl_registrasi'] = $this->core->getRegPeriksaInfo('tgl_registrasi', $row['no_rawat']);
              $row['status_lanjut'] = $this->core->getRegPeriksaInfo('status_lanjut', $row['no_rawat']);
              $row['png_jawab'] = $this->core->getPenjabInfo('png_jawab', $this->core->getRegPeriksaInfo('kd_pj', $row['no_rawat']));
              $row['jam_reg'] = $this->core->getRegPeriksaInfo('jam_reg', $row['no_rawat']);
              $row['nm_dokter'] = $this->core->getDokterInfo('nm_dokter', $this->core->getRegPeriksaInfo('kd_dokter', $row['no_rawat']));
              $row['nm_poli'] = $this->core->getPoliklinikInfo('nm_poli', $this->core->getRegPeriksaInfo('kd_poli', $row['no_rawat']));
              $row['no_sep'] = $this->_getSEPInfo('no_sep', $row['no_rawat']);
              $row['no_peserta'] = $this->_getSEPInfo('no_kartu', $row['no_rawat']);
              $row['no_rujukan'] = $this->_getSEPInfo('no_rujukan', $row['no_rawat']);
              $row['kd_penyakit'] = $this->_getDiagnosa('kd_penyakit', $row['no_rawat'], $row['status_lanjut']);
              $row['nm_penyakit'] = $this->_getDiagnosa('nm_penyakit', $row['no_rawat'], $row['status_lanjut']);
              $row['berkas_digital'] = $berkas_digital;
              $row['berkas_digital_pasien'] = $berkas_digital_pasien;
              $row['formSepURL'] = url([ADMIN, 'vedika', 'formsepvclaim', '?no_rawat='.$row['no_rawat']]);
              $row['pdfURL'] = url([ADMIN, 'vedika', 'pdf', $this->convertNorawat($row['no_rawat'])]);
              $row['setstatusURL']  = url([ADMIN, 'vedika', 'setstatus', $this->_getSEPInfo('no_sep', $row['no_rawat'])]);
              $row['status_pengajuan'] = $this->db('mlite_vedika')->where('nosep', $this->_getSEPInfo('no_sep', $row['no_rawat']))->desc('id')->limit(1)->toArray();
              $row['berkasPasien'] = url([ADMIN, 'vedika', 'berkaspasien', $this->getRegPeriksaInfo('no_rkm_medis', $row['no_rawat'])]);
              $row['berkasPerawatan'] = url([ADMIN, 'vedika', 'berkasperawatan', $this->convertNorawat($row['no_rawat'])]);
              $this->assign['list'][] = $row;
          }
      }

      $this->core->addCSS(url('assets/jscripts/lightbox/lightbox.min.css'));
      $this->core->addJS(url('assets/jscripts/lightbox/lightbox.min.js'));

      $this->assign['searchUrl'] =  url([ADMIN, 'vedika', 'pengajuan', $type, $page.'?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]);
      $this->assign['ralanUrl'] =  url([ADMIN, 'vedika', 'pengajuan', 'ralan', $page.'?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]);
      $this->assign['ranapUrl'] =  url([ADMIN, 'vedika', 'pengajuan', 'ranap', $page.'?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]);
      return $this->draw('pengajuan.html', ['tab' => $type, 'vedika' => $this->assign]);

    }

    public function anyPerbaikan($type = 'ralan', $page = 1)
    {
      if(isset($_POST['submit'])) {
        $this->db('mlite_vedika')->save([
          'id' => NULL,
          'tanggal' => date('Y-m-d'),
          'no_rkm_medis' => $_POST['no_rkm_medis'],
          'no_rawat' => $_POST['no_rawat'],
          'nosep' => $_POST['nosep'],
          'catatan' => $_POST['catatan'],
          'status' => $_POST['status'],
          'username' => $this->core->getUserInfo('username', null, true)
        ]);
      }

      if(isset($_POST['simpanberkas'])) {
        $dir    = $this->_uploads;
        $cntr   = 0;

        $image = $_FILES['files']['tmp_name'];
        $img = new \Systems\Lib\Image();
        $id = convertNorawat($_POST['no_rawat']);
        if ($img->load($image)) {
            $imgName = time().$cntr++;
            $imgPath = $dir.'/'.$id.'_'.$imgName.'.'.$img->getInfos('type');
            $lokasi_file = 'pages/upload/'.$id.'_'.$imgName.'.'.$img->getInfos('type');
            $img->save($imgPath);
            $query = $this->db('berkas_digital_perawatan')->save(['no_rawat' => $_POST['no_rawat'], 'kode' => $_POST['kode'], 'lokasi_file' => $lokasi_file]);
            if($query) {
              $this->notify('success', 'Simpan berkar digital perawatan sukses.');
            }
        }
      }

      $this->_addHeaderFiles();
      $start_date = date('Y-m-d');
      if(isset($_GET['start_date']) && $_GET['start_date'] !='')
        $start_date = $_GET['start_date'];
      $end_date = date('Y-m-d');
      if(isset($_GET['end_date']) && $_GET['end_date'] !='')
        $end_date = $_GET['end_date'];
      $perpage = '10';
      $phrase = '';
      if(isset($_GET['s']))
        $phrase = $_GET['s'];

      // pagination
      $totalRecords = $this->db()->pdo()->prepare("SELECT no_rawat FROM mlite_vedika WHERE status = 'Perbaiki' AND jenis = '2' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND tgl_registrasi BETWEEN '$start_date' AND '$end_date'");
      $totalRecords->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
      $totalRecords = $totalRecords->fetchAll();

      $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url([ADMIN, 'vedika', 'perbaikan', $type, '%d?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]));
      $this->assign['pagination'] = $pagination->nav('pagination','5');
      $this->assign['totalRecords'] = $totalRecords;

      $offset = $pagination->offset();
      $query = $this->db()->pdo()->prepare("SELECT * FROM mlite_vedika WHERE status = 'Perbaiki' AND jenis = '2' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND tgl_registrasi BETWEEN '$start_date' AND '$end_date' LIMIT $perpage OFFSET $offset");
      $query->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
      $rows = $query->fetchAll();

      if($type == 'ranap') {
        // pagination
        $totalRecords = $this->db()->pdo()->prepare("SELECT no_rawat FROM mlite_vedika WHERE status = 'Perbaiki' AND jenis = '1' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND tgl_registrasi BETWEEN '$start_date' AND '$end_date'");
        $totalRecords->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
        $totalRecords = $totalRecords->fetchAll();

        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url([ADMIN, 'vedika', 'index', $type, '%d?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]));
        $this->assign['pagination'] = $pagination->nav('pagination','5');
        $this->assign['totalRecords'] = $totalRecords;

        $offset = $pagination->offset();
        $query = $this->db()->pdo()->prepare("SELECT * FROM mlite_vedika WHERE status = 'Perbaiki' AND jenis = '1' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND tgl_registrasi BETWEEN '$start_date' AND '$end_date' LIMIT $perpage OFFSET $offset");
        $query->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
        $rows = $query->fetchAll();
      }
      $this->assign['list'] = [];
      if (count($rows)) {
          foreach ($rows as $row) {
              $berkas_digital = $this->db('berkas_digital_perawatan')
                ->join('master_berkas_digital', 'master_berkas_digital.kode=berkas_digital_perawatan.kode')
                ->where('berkas_digital_perawatan.no_rawat', $row['no_rawat'])
                ->asc('master_berkas_digital.nama')
                ->toArray();
              $galleri_pasien = $this->db('mlite_pasien_galleries_items')
                ->join('mlite_pasien_galleries', 'mlite_pasien_galleries.id = mlite_pasien_galleries_items.gallery')
                ->where('mlite_pasien_galleries.slug', $row['no_rkm_medis'])
                ->toArray();

              $berkas_digital_pasien = array();
              if (count($galleri_pasien)) {
                  foreach ($galleri_pasien as $galleri) {
                      $galleri['src'] = unserialize($galleri['src']);

                      if (!isset($galleri['src']['sm'])) {
                          $galleri['src']['sm'] = isset($galleri['src']['xs']) ? $galleri['src']['xs'] : $galleri['src']['lg'];
                      }

                      $berkas_digital_pasien[] = $galleri;
                  }
              }

              $row = htmlspecialchars_array($row);
              $row['nm_pasien'] = $this->core->getPasienInfo('nm_pasien', $row['no_rkm_medis']);
              $row['almt_pj'] = $this->core->getPasienInfo('alamat', $row['no_rkm_medis']);
              $row['jk'] = $this->core->getPasienInfo('jk', $row['no_rkm_medis']);
              $row['umur'] = $this->core->getRegPeriksaInfo('umurdaftar', $row['no_rawat']);
              $row['sttsumur'] = $this->core->getRegPeriksaInfo('sttsumur', $row['no_rawat']);
              $row['tgl_registrasi'] = $this->core->getRegPeriksaInfo('tgl_registrasi', $row['no_rawat']);
              $row['status_lanjut'] = $this->core->getRegPeriksaInfo('status_lanjut', $row['no_rawat']);
              $row['png_jawab'] = $this->core->getPenjabInfo('png_jawab', $this->core->getRegPeriksaInfo('kd_pj', $row['no_rawat']));
              $row['jam_reg'] = $this->core->getRegPeriksaInfo('jam_reg', $row['no_rawat']);
              $row['nm_dokter'] = $this->core->getDokterInfo('nm_dokter', $this->core->getRegPeriksaInfo('kd_dokter', $row['no_rawat']));
              $row['nm_poli'] = $this->core->getPoliklinikInfo('nm_poli', $this->core->getRegPeriksaInfo('kd_poli', $row['no_rawat']));
              $row['no_sep'] = $this->_getSEPInfo('no_sep', $row['no_rawat']);
              $row['no_peserta'] = $this->_getSEPInfo('no_kartu', $row['no_rawat']);
              $row['no_rujukan'] = $this->_getSEPInfo('no_rujukan', $row['no_rawat']);
              $row['kd_penyakit'] = $this->_getDiagnosa('kd_penyakit', $row['no_rawat'], $row['status_lanjut']);
              $row['nm_penyakit'] = $this->_getDiagnosa('nm_penyakit', $row['no_rawat'], $row['status_lanjut']);
              $row['berkas_digital'] = $berkas_digital;
              $row['berkas_digital_pasien'] = $berkas_digital_pasien;
              $row['formSepURL'] = url([ADMIN, 'vedika', 'formsepvclaim', '?no_rawat='.$row['no_rawat']]);
              $row['pdfURL'] = url([ADMIN, 'vedika', 'pdf', $this->convertNorawat($row['no_rawat'])]);
              $row['setstatusURL']  = url([ADMIN, 'vedika', 'setstatus', $this->_getSEPInfo('no_sep', $row['no_rawat'])]);
              $row['status_pengajuan'] = $this->db('mlite_vedika')->where('nosep', $this->_getSEPInfo('no_sep', $row['no_rawat']))->desc('id')->limit(1)->toArray();
              $row['berkasPasien'] = url([ADMIN, 'vedika', 'berkaspasien', $this->getRegPeriksaInfo('no_rkm_medis', $row['no_rawat'])]);
              $row['berkasPerawatan'] = url([ADMIN, 'vedika', 'berkasperawatan', $this->convertNorawat($row['no_rawat'])]);
              $this->assign['list'][] = $row;
          }
      }

      $this->core->addCSS(url('assets/jscripts/lightbox/lightbox.min.css'));
      $this->core->addJS(url('assets/jscripts/lightbox/lightbox.min.js'));

      $this->assign['searchUrl'] =  url([ADMIN, 'vedika', 'perbaikan', $type, $page.'?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]);
      $this->assign['ralanUrl'] =  url([ADMIN, 'vedika', 'perbaikan', 'ralan', $page.'?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]);
      $this->assign['ranapUrl'] =  url([ADMIN, 'vedika', 'perbaikan', 'ranap', $page.'?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]);
      return $this->draw('perbaikan.html', ['tab' => $type, 'vedika' => $this->assign]);

    }

    public function getFormSEPVClaim()
    {
      $this->tpl->set('poliklinik', $this->core->db('poliklinik')->where('status', '1')->toArray());
      $this->tpl->set('dokter', $this->core->db('dokter')->where('status', '1')->toArray());
      echo $this->tpl->draw(MODULES.'/vedika/view/admin/form.sepvclaim.html', true);
      exit();
    }

    public function getHapus($no_sep)
    {
      $query = $this->db('bridging_sep')->where('no_sep', $no_sep)->delete();
      if($query) {
        $this->db('bpjs_prb')->where('no_sep', $no_sep)->delete();
      }
      echo 'No SEP '.$no_sep.' telah dihapus.!!';
      exit();
    }

    public function postSaveSEP()
    {
      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
      $key = $this->consid.$this->secretkey.$tStamp;

      date_default_timezone_set($this->settings->get('settings.timezone'));

      header('Content-type: text/html');
      $date = date('Y-m-d');
      $url = $this->settings->get('settings.BpjsApiUrl').'SEP/'.$_POST['no_sep'];
      $consid = $this->settings->get('settings.BpjsConsID');
      $secretkey = $this->settings->get('settings.BpjsSecretKey');
      $userkey = $this->settings->get('settings.BpjsUserKey');
      $output = BpjsService::get($url, NULL, $consid, $secretkey, $userkey, $tStamp);
      $data = json_decode($output, true);
      //print_r($output);
      $code = $data['metaData']['code'];
      $message = $data['metaData']['message'];
      if($this->vclaim_version == 1) {
        //echo json_encode($data);
        $data = $data;
      } else {
        $stringDecrypt = stringDecrypt($key, $json['response']);
        $decompress = '""';
        if(!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
        }
        if($data != null) {
          $data = '{
            "metaData": {
              "code": "'.$code.'",
              "message": "'.$message.'"
            },
            "response": '.$decompress.'}';
          $data = json_decode($data, true);
        } else {
          $data = '{
            "metaData": {
              "code": "5000",
              "message": "ERROR"
            },
            "response": "ADA KESALAHAN ATAU SAMBUNGAN KE SERVER BPJS TERPUTUS."}';
          $data = json_decode($data, true);
        }
      }

      $url_rujukan = $this->settings->get('settings.BpjsApiUrl').'Rujukan/'.$data['response']['noRujukan'];
      if($_POST['asal_rujukan'] == 2) {
        $url_rujukan = $this->settings->get('settings.BpjsApiUrl').'Rujukan/RS/'.$data['response']['noRujukan'];
      }
      $rujukan = BpjsService::get($url_rujukan, NULL, $consid, $secretkey, $userkey, $tStamp);
      $data_rujukan = json_decode($rujukan, true);
      //print_r($rujukan);

      $code = $data_rujukan['metaData']['code'];
      $message = $data_rujukan['metaData']['message'];
      if($this->vclaim_version == 1) {
        //echo json_encode($data);
        $data_rujukan = $data_rujukan;
      } else {
        $stringDecrypt = stringDecrypt($key, $json['response']);
        $decompress = '""';
        if(!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
        }
        if($data_rujukan != null) {
          $data_rujukan = '{
            "metaData": {
              "code": "'.$code.'",
              "message": "'.$message.'"
            },
            "response": '.$decompress.'}';
          $data_rujukan = json_decode($data_rujukan, true);
        } else {
          $data_rujukan = '{
            "metaData": {
              "code": "5000",
              "message": "ERROR"
            },
            "response": "ADA KESALAHAN ATAU SAMBUNGAN KE SERVER BPJS TERPUTUS."}';
          $data_rujukan = json_decode($data_rujukan, true);
        }
      }

      $no_telp = $data_rujukan['response']['rujukan']['peserta']['mr']['noTelepon'];
      if(empty($data_rujukan['response']['rujukan']['peserta']['mr']['noTelepon'])){
        $no_telp = '00000000';
      }

      $jenis_pelayanan = '2';
      if($data['response']['jnsPelayanan'] == 'Rawat Inap') {
        $jenis_pelayanan = '1';
      }

      if($data_rujukan['metaData']['code'] == 201) {
        $data_rujukan['response']['rujukan']['tglKunjungan'] = $_POST['tgl_kunjungan'];
        $data_rujukan['response']['rujukan']['provPerujuk']['kode'] = $this->settings->get('settings.ppk_bpjs');
        $data_rujukan['response']['rujukan']['provPerujuk']['nama'] = $this->settings->get('settings.nama_instansi');
        $data_rujukan['response']['rujukan']['diagnosa']['kode'] = $_POST['kd_diagnosa'];
        $data_rujukan['response']['rujukan']['diagnosa']['nama'] = $data['response']['diagnosa'];
        $data_rujukan['response']['rujukan']['pelayanan']['kode'] = $jenis_pelayanan;
      }

      if($data['metaData']['code'] == 200)
      {
        $insert = $this->db('bridging_sep')
          ->save([
            'no_sep' => $data['response']['noSep'],
            'no_rawat' => $_POST['no_rawat'],
            'tglsep' => $data['response']['tglSep'],
            'tglrujukan' => $data_rujukan['response']['rujukan']['tglKunjungan'],
            'no_rujukan' => $data['response']['noRujukan'],
            'kdppkrujukan' => $data_rujukan['response']['rujukan']['provPerujuk']['kode'],
            'nmppkrujukan' => $data_rujukan['response']['rujukan']['provPerujuk']['nama'],
            'kdppkpelayanan' => $this->settings->get('settings.ppk_bpjs'),
            'nmppkpelayanan' => $this->settings->get('settings.nama_instansi'),
            'jnspelayanan' => $jenis_pelayanan,
            'catatan' => $data['response']['catatan'],
            'diagawal' => $data_rujukan['response']['rujukan']['diagnosa']['kode'],
            'nmdiagnosaawal' => $data_rujukan['response']['rujukan']['diagnosa']['nama'],
            'kdpolitujuan' => $this->db('maping_poli_bpjs')->where('kd_poli_rs', $_POST['kd_poli'])->oneArray()['kd_poli_bpjs'],
            'nmpolitujuan' => $this->db('maping_poli_bpjs')->where('kd_poli_rs', $_POST['kd_poli'])->oneArray()['nm_poli_bpjs'],
            'klsrawat' =>  substr($data['response']['kelasRawat'], 6),
            'lakalantas' => '0',
            'user' => $this->core->getUserInfo('username', null, true),
            'nomr' => $this->getRegPeriksaInfo('no_rkm_medis', $_POST['no_rawat']),
            'nama_pasien' => $data['response']['peserta']['nama'],
            'tanggal_lahir' => $data['response']['peserta']['tglLahir'],
            'peserta' => $data['response']['peserta']['jnsPeserta'],
            'jkel' => $data['response']['peserta']['kelamin'],
            'no_kartu' => $data['response']['peserta']['noKartu'],
            'tglpulang' => '1900-01-01 00:00:00',
            'asal_rujukan' => $_POST['asal_rujukan'],
            'eksekutif' => $data['response']['poliEksekutif'],
            'cob' => '0',
            'penjamin' => '-',
            'notelep' => $no_telp,
            'katarak' => '0',
            'tglkkl' => '1900-01-01',
            'keterangankkl' => '-',
            'suplesi' => '0',
            'no_sep_suplesi' => '-',
            'kdprop' => '-',
            'nmprop' => '-',
            'kdkab' => '-',
            'nmkab' => '-',
            'kdkec' => '-',
            'nmkec' => '-',
            'noskdp' => '0',
            'kddpjp' => $this->db('maping_dokter_dpjpvclaim')->where('kd_dokter', $_POST['kd_dokter'])->oneArray()['kd_dokter_bpjs'],
            'nmdpdjp' => $this->db('maping_dokter_dpjpvclaim')->where('kd_dokter', $_POST['kd_dokter'])->oneArray()['nm_dokter_bpjs']
          ]);
      }

      if ($insert) {
          $this->db('bpjs_prb')->save(['no_sep' => $data['response']['noSep'], 'prb' => $data_rujukan['response']['rujukan']['peserta']['informasi']['prolanisPRB']]);
          $this->notify('success', 'Simpan sukes');
      } else {
          $this->notify('failure', 'Simpan gagal');
      }
    }
    public function getPDF($id)
    {
      $berkas_digital = $this->db('berkas_digital_perawatan')
        ->join('master_berkas_digital', 'master_berkas_digital.kode=berkas_digital_perawatan.kode')
        ->where('berkas_digital_perawatan.no_rawat', $this->revertNorawat($id))
        ->asc('master_berkas_digital.nama')
        ->toArray();

      $galleri_pasien = $this->db('mlite_pasien_galleries_items')
        ->join('mlite_pasien_galleries', 'mlite_pasien_galleries.id = mlite_pasien_galleries_items.gallery')
        ->where('mlite_pasien_galleries.slug', $this->getRegPeriksaInfo('no_rkm_medis', $this->revertNorawat($id)))
        ->toArray();
      $berkas_digital_pasien = array();
      if (count($galleri_pasien)) {
          foreach ($galleri_pasien as $galleri) {
              $galleri['src'] = unserialize($galleri['src']);

              if (!isset($galleri['src']['sm'])) {
                  $galleri['src']['sm'] = isset($galleri['src']['xs']) ? $galleri['src']['xs'] : $galleri['src']['lg'];
              }

              $berkas_digital_pasien[] = $galleri;
          }
      }

      $no_rawat = $this->revertNorawat($id);
      $query = $this->db()->pdo()->prepare("select no,nm_perawatan,pemisah,if(biaya=0,'',biaya),if(jumlah=0,'',jumlah),if(tambahan=0,'',tambahan),if(totalbiaya=0,'',totalbiaya),totalbiaya from billing where no_rawat='$no_rawat'");
      $query->execute();
      $rows = $query->fetchAll();
      $total=0;
      foreach ($rows as $key => $value) {
        $total = $total+$value['7'];
      }
      $total = $total;
      $this->tpl->set('total', $total);

      $instansi['logo'] = $this->settings->get('settings.logo');
      $instansi['nama_instansi'] = $this->settings->get('settings.nama_instansi');
      $instansi['alamat'] = $this->settings->get('settings.alamat');
      $instansi['kota'] = $this->settings->get('settings.kota');
      $instansi['propinsi'] = $this->settings->get('settings.propinsi');
      $instansi['nomor_telepon'] = $this->settings->get('settings.nomor_telepon');
      $instansi['email'] = $this->settings->get('settings.email');

      $this->tpl->set('billing', $rows);
      $this->tpl->set('instansi', $instansi);

      $print_sep = array();
      if(!empty($this->_getSEPInfo('no_sep', $no_rawat))) {
        $print_sep['bridging_sep'] = $this->db('bridging_sep')->where('no_sep', $this->_getSEPInfo('no_sep', $no_rawat))->oneArray();
        $print_sep['bpjs_prb'] = $this->db('bpjs_prb')->where('no_sep', $this->_getSEPInfo('no_sep', $no_rawat))->oneArray();
        $batas_rujukan = $this->db('bridging_sep')->select('DATE_ADD(tglrujukan , INTERVAL 85 DAY) AS batas_rujukan')->where('no_sep', $id)->oneArray();
        $print_sep['batas_rujukan'] = $batas_rujukan['batas_rujukan'];
      }
      $print_sep['nama_instansi'] = $this->settings->get('settings.nama_instansi');
      $print_sep['logoURL'] = url(MODULES.'/vclaim/img/bpjslogo.png');
      $this->tpl->set('print_sep', $print_sep);

      $resume_pasien = $this->db('resume_pasien')
        ->join('dokter', 'dokter.kd_dokter = resume_pasien.kd_dokter')
        ->where('no_rawat', $this->revertNorawat($id))
        ->oneArray();
      $this->tpl->set('resume_pasien', $resume_pasien);

      $pasien = $this->db('pasien')
        ->join('kecamatan', 'kecamatan.kd_kec = pasien.kd_kec')
        ->join('kabupaten', 'kabupaten.kd_kab = pasien.kd_kab')
        ->where('no_rkm_medis', $this->getRegPeriksaInfo('no_rkm_medis', $this->revertNorawat($id)))
        ->oneArray();
      $reg_periksa = $this->db('reg_periksa')
        ->join('dokter', 'dokter.kd_dokter = reg_periksa.kd_dokter')
        ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
        ->join('penjab', 'penjab.kd_pj = reg_periksa.kd_pj')
        ->where('stts', '<>', 'Batal')
        ->where('no_rawat', $this->revertNorawat($id))
        ->oneArray();
      $rows_dpjp_ranap = $this->db('dpjp_ranap')
        ->join('dokter', 'dokter.kd_dokter = dpjp_ranap.kd_dokter')
        ->where('no_rawat', $this->revertNorawat($id))
        ->toArray();
      $dpjp_i = 1;
      $dpjp_ranap = [];
      foreach ($rows_dpjp_ranap as $row) {
        $row['nomor'] = $dpjp_i++;
        $dpjp_ranap[] = $row;
      }
      $rujukan_internal = $this->db('rujukan_internal_poli')
        ->join('poliklinik', 'poliklinik.kd_poli = rujukan_internal_poli.kd_poli')
        ->join('dokter', 'dokter.kd_dokter = rujukan_internal_poli.kd_dokter')
        ->where('no_rawat', $this->revertNorawat($id))
        ->oneArray();
      $diagnosa_pasien = $this->db('diagnosa_pasien')
        ->join('penyakit', 'penyakit.kd_penyakit = diagnosa_pasien.kd_penyakit')
        ->where('no_rawat', $this->revertNorawat($id))
        ->toArray();
      $prosedur_pasien = $this->db('prosedur_pasien')
        ->join('icd9', 'icd9.kode = prosedur_pasien.kode')
        ->where('no_rawat', $this->revertNorawat($id))
        ->toArray();
      $pemeriksaan_ralan = $this->db('pemeriksaan_ralan')
        ->where('no_rawat', $this->revertNorawat($id))
        ->asc('tgl_perawatan')
        ->asc('jam_rawat')
        ->toArray();
      $pemeriksaan_ranap = $this->db('pemeriksaan_ranap')
        ->where('no_rawat', $this->revertNorawat($id))
        ->asc('tgl_perawatan')
        ->asc('jam_rawat')
        ->toArray();
      $rawat_jl_dr = $this->db('rawat_jl_dr')
        ->join('jns_perawatan', 'rawat_jl_dr.kd_jenis_prw=jns_perawatan.kd_jenis_prw')
        ->join('dokter', 'rawat_jl_dr.kd_dokter=dokter.kd_dokter')
        ->where('no_rawat', $this->revertNorawat($id))
        ->toArray();
      $rawat_jl_pr = $this->db('rawat_jl_pr')
        ->join('jns_perawatan', 'rawat_jl_pr.kd_jenis_prw=jns_perawatan.kd_jenis_prw')
        ->join('petugas', 'rawat_jl_pr.nip=petugas.nip')
        ->where('no_rawat', $this->revertNorawat($id))
        ->toArray();
      $rawat_jl_drpr = $this->db('rawat_jl_drpr')
        ->join('jns_perawatan', 'rawat_jl_drpr.kd_jenis_prw=jns_perawatan.kd_jenis_prw')
        ->join('dokter', 'rawat_jl_drpr.kd_dokter=dokter.kd_dokter')
        ->join('petugas', 'rawat_jl_drpr.nip=petugas.nip')
        ->where('no_rawat', $this->revertNorawat($id))
        ->toArray();
      $rawat_inap_dr = $this->db('rawat_inap_dr')
        ->join('jns_perawatan_inap', 'rawat_inap_dr.kd_jenis_prw=jns_perawatan_inap.kd_jenis_prw')
        ->join('dokter', 'rawat_inap_dr.kd_dokter=dokter.kd_dokter')
        ->where('no_rawat', $this->revertNorawat($id))
        ->toArray();
      $rawat_inap_pr = $this->db('rawat_inap_pr')
        ->join('jns_perawatan_inap', 'rawat_inap_pr.kd_jenis_prw=jns_perawatan_inap.kd_jenis_prw')
        ->join('petugas', 'rawat_inap_pr.nip=petugas.nip')
        ->where('no_rawat', $this->revertNorawat($id))
        ->toArray();
      $rawat_inap_drpr = $this->db('rawat_inap_drpr')
        ->join('jns_perawatan_inap', 'rawat_inap_drpr.kd_jenis_prw=jns_perawatan_inap.kd_jenis_prw')
        ->join('dokter', 'rawat_inap_drpr.kd_dokter=dokter.kd_dokter')
        ->join('petugas', 'rawat_inap_drpr.nip=petugas.nip')
        ->where('no_rawat', $this->revertNorawat($id))
        ->toArray();
      $kamar_inap = $this->db('kamar_inap')
        ->join('kamar', 'kamar_inap.kd_kamar=kamar.kd_kamar')
        ->join('bangsal', 'kamar.kd_bangsal=bangsal.kd_bangsal')
        ->where('no_rawat', $this->revertNorawat($id))
        ->toArray();
      $operasi = $this->db('operasi')
        ->join('paket_operasi', 'operasi.kode_paket=paket_operasi.kode_paket')
        ->where('no_rawat', $this->revertNorawat($id))
        ->toArray();
      $tindakan_radiologi = $this->db('periksa_radiologi')
        ->join('jns_perawatan_radiologi', 'periksa_radiologi.kd_jenis_prw=jns_perawatan_radiologi.kd_jenis_prw')
        ->join('dokter', 'periksa_radiologi.kd_dokter=dokter.kd_dokter')
        ->join('petugas', 'periksa_radiologi.nip=petugas.nip')
        ->where('no_rawat', $this->revertNorawat($id))
        ->toArray();
      $hasil_radiologi = $this->db('hasil_radiologi')
        ->where('no_rawat', $this->revertNorawat($id))
        ->toArray();
      $pemeriksaan_laboratorium = [];
      $rows_pemeriksaan_laboratorium = $this->db('periksa_lab')
        ->join('jns_perawatan_lab', 'jns_perawatan_lab.kd_jenis_prw=periksa_lab.kd_jenis_prw')
        ->where('no_rawat', $this->revertNorawat($id))
        ->toArray();
      foreach ($rows_pemeriksaan_laboratorium as $value) {
        $value['detail_periksa_lab'] = $this->db('detail_periksa_lab')
          ->join('template_laboratorium', 'template_laboratorium.id_template=detail_periksa_lab.id_template')
          ->where('detail_periksa_lab.no_rawat', $value['no_rawat'])
          ->where('detail_periksa_lab.kd_jenis_prw', $value['kd_jenis_prw'])
          ->toArray();
        $pemeriksaan_laboratorium[] = $value;
      }
      $pemberian_obat = $this->db('detail_pemberian_obat')
        ->join('databarang', 'detail_pemberian_obat.kode_brng=databarang.kode_brng')
        ->where('no_rawat', $this->revertNorawat($id))
        ->toArray();
      $obat_operasi = $this->db('beri_obat_operasi')
        ->join('obatbhp_ok', 'beri_obat_operasi.kd_obat=obatbhp_ok.kd_obat')
        ->where('no_rawat', $this->revertNorawat($id))
        ->toArray();
      $resep_pulang = $this->db('resep_pulang')
        ->join('databarang', 'resep_pulang.kode_brng=databarang.kode_brng')
        ->where('no_rawat', $this->revertNorawat($id))
        ->toArray();
      $laporan_operasi = $this->db('laporan_operasi')
        ->where('no_rawat', $this->revertNorawat($id))
        ->oneArray();

      $this->tpl->set('pasien', $pasien);
      $this->tpl->set('reg_periksa', $reg_periksa);
      $this->tpl->set('rujukan_internal', $rujukan_internal);
      $this->tpl->set('dpjp_ranap', $dpjp_ranap);
      $this->tpl->set('diagnosa_pasien', $diagnosa_pasien);
      $this->tpl->set('prosedur_pasien', $prosedur_pasien);
      $this->tpl->set('pemeriksaan_ralan', $pemeriksaan_ralan);
      $this->tpl->set('pemeriksaan_ranap', $pemeriksaan_ranap);
      $this->tpl->set('rawat_jl_dr', $rawat_jl_dr);
      $this->tpl->set('rawat_jl_pr', $rawat_jl_pr);
      $this->tpl->set('rawat_jl_drpr', $rawat_jl_drpr);
      $this->tpl->set('rawat_inap_dr', $rawat_inap_dr);
      $this->tpl->set('rawat_inap_pr', $rawat_inap_pr);
      $this->tpl->set('rawat_inap_drpr', $rawat_inap_drpr);
      $this->tpl->set('kamar_inap', $kamar_inap);
      $this->tpl->set('operasi', $operasi);
      $this->tpl->set('tindakan_radiologi', $tindakan_radiologi);
      $this->tpl->set('hasil_radiologi', $hasil_radiologi);
      $this->tpl->set('pemeriksaan_laboratorium', $pemeriksaan_laboratorium);
      $this->tpl->set('pemberian_obat', $pemberian_obat);
      $this->tpl->set('obat_operasi', $obat_operasi);
      $this->tpl->set('resep_pulang', $resep_pulang);
      $this->tpl->set('laporan_operasi', $laporan_operasi);

      $this->tpl->set('berkas_digital', $berkas_digital);
      $this->tpl->set('berkas_digital_pasien', $berkas_digital_pasien);
      $this->tpl->set('hasil_radiologi', $this->db('hasil_radiologi')->where('no_rawat', $this->revertNorawat($id))->oneArray());
      $this->tpl->set('gambar_radiologi', $this->db('gambar_radiologi')->where('no_rawat', $this->revertNorawat($id))->toArray());
      $this->tpl->set('vedika', htmlspecialchars_array($this->settings('vedika')));
      echo $this->tpl->draw(MODULES.'/vedika/view/admin/pdf.html', true);
      exit();
    }

    public function getSetStatus($id)
    {
      $set_status = $this->db('bridging_sep')->where('no_sep', $id)->oneArray();
      $vedika = $this->db('mlite_vedika')->where('nosep', $id)->asc('id')->toArray();
      $this->tpl->set('logo', $this->settings->get('settings.logo'));
      $this->tpl->set('nama_instansi', $this->settings->get('settings.nama_instansi'));
      $this->tpl->set('set_status', $set_status);
      $this->tpl->set('vedika', $vedika);
      echo $this->tpl->draw(MODULES.'/vedika/view/admin/setstatus.html', true);
      exit();
    }

    public function getBerkasPasien()
    {
      echo $this->tpl->draw(MODULES.'/vedika/view/admin/berkaspasien.html', true);
      exit();
    }

    public function anyBerkasPerawatan($no_rawat)
    {
      $this->assign['master_berkas_digital'] = $this->db('master_berkas_digital')->toArray();
      $this->assign['berkas_digital'] = $this->db('berkas_digital_perawatan')->where('no_rawat', $no_rawat)->toArray();
      $this->assign['no_rawat'] = revertNorawat($no_rawat);
      $this->tpl->set('berkasperawatan', $this->assign);

      echo $this->tpl->draw(MODULES.'/vedika/view/admin/berkasperawatan.html', true);
      exit();
    }

    public function postSaveBerkasDigital()
    {

      $dir    = $this->_uploads;
      $cntr   = 0;

      $image = $_FILES['files']['tmp_name'];
      $img = new \Systems\Lib\Image();
      $id = convertNorawat($_POST['no_rawat']);
      if ($img->load($image)) {
          $imgName = time().$cntr++;
          $imgPath = $dir.'/'.$id.'_'.$imgName.'.'.$img->getInfos('type');
          $lokasi_file = 'pages/upload/'.$id.'_'.$imgName.'.'.$img->getInfos('type');
          $img->save($imgPath);
          $query = $this->db('berkas_digital_perawatan')->save(['no_rawat' => $_POST['no_rawat'], 'kode' => $_POST['kode'], 'lokasi_file' => $lokasi_file]);
          if($query) {
            echo '<br><img src="'.WEBAPPS_URL.'/berkasrawat/'.$lokasi_file.'" width="150" />';
          }
      }

      exit();

    }

    public function postSaveStatus()
    {
      redirect(url([ADMIN,'vedika','index']));
      //redirect(parseURL());
    }

    private function _getSEPInfo($field, $no_rawat)
    {
        $row = $this->db('bridging_sep')->where('no_rawat', $no_rawat)->oneArray();
        return $row[$field];
    }

    private function _getDiagnosa($field, $no_rawat, $status_lanjut)
    {
        $row = $this->db('diagnosa_pasien')->join('penyakit', 'penyakit.kd_penyakit = diagnosa_pasien.kd_penyakit')->where('diagnosa_pasien.no_rawat', $no_rawat)->where('diagnosa_pasien.prioritas', 1)->where('diagnosa_pasien.status', $status_lanjut)->oneArray();
        return $row[$field];
    }

    public function getSettings()
    {
        $this->_addHeaderFiles();
        $this->assign['title'] = 'Pengaturan Modul Vedika';
        $this->assign['vedika'] = htmlspecialchars_array($this->settings('vedika'));
        $this->assign['master_berkas_digital'] = $this->db('master_berkas_digital')->toArray();
        return $this->draw('settings.html', ['settings' => $this->assign]);
    }

    public function postSaveSettings()
    {
        foreach ($_POST['vedika'] as $key => $val) {
            $this->settings('vedika', $key, $val);
        }
        $this->notify('success', 'Pengaturan telah disimpan');
        redirect(url([ADMIN, 'vedika', 'settings']));
    }

    public function getPegawaiInfo($field, $nik)
    {
        $row = $this->db('pegawai')->where('nik', $nik)->oneArray();
        return $row[$field];
    }

    public function getPasienInfo($field, $no_rkm_medis)
    {
        $row = $this->db('pasien')->where('no_rkm_medis', $no_rkm_medis)->oneArray();
        return $row[$field];
    }

    public function getRegPeriksaInfo($field, $no_rawat)
    {
        $row = $this->db('reg_periksa')->where('no_rawat', $no_rawat)->oneArray();
        return $row[$field];
    }

    public function convertNorawat($text)
    {
        setlocale(LC_ALL, 'en_EN');
        $text = str_replace('/', '', trim($text));
        return $text;
    }

    public function revertNorawat($text)
    {
        setlocale(LC_ALL, 'en_EN');
        $tahun = substr($text, 0, 4);
        $bulan = substr($text, 4, 2);
        $tanggal = substr($text, 6, 2);
        $nomor = substr($text, 8, 6);
        $result = $tahun.'/'.$bulan.'/'.$tanggal.'/'.$nomor;
        return $result;
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/vedika/js/admin/scripts.js');
        exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/vedika/css/admin/styles.css');
        exit();
    }

    private function _addHeaderFiles()
    {
        // CSS
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));

        // JS
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));

        // MODULE SCRIPTS
        $this->core->addCSS(url([ADMIN, 'vedika', 'css']));
        $this->core->addJS(url([ADMIN, 'vedika', 'javascript']), 'footer');
    }

}
