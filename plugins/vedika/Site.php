<?php

namespace Plugins\Vedika;

use Systems\SiteModule;

class Site extends SiteModule
{

    public function init()
    {
        $this->mlite['notify']         = $this->core->getNotify();
        $this->mlite['logo']           = $this->settings->get('settings.logo');
        $this->mlite['nama_instansi']  = $this->settings->get('settings.nama_instansi');
        $this->mlite['path']           = url();
        $this->mlite['version']        = $this->core->settings->get('settings.version');
        $this->mlite['token']          = '  ';
        if ($this->_loginCheck()) {
            //$vedika = $this->db('mlite_users')->where('username', $_SESSION['vedika_user'])->oneArray();
            $this->mlite['vedika_user']    = $_SESSION['vedika_user'];
            $this->mlite['vedika_token']   = $_SESSION['vedika_token'];
        }
        $this->mlite['slug']           = parseURL();
    }

    public function routes()
    {
        $this->route('veda', 'getIndex');
        $this->route('veda/pengajuan/ralan', 'getIndexPengajuanRalan');
        $this->route('veda/pengajuan/ralan/(:int)', 'getIndexPengajuanRalan');
        $this->route('veda/pengajuan/ranap', 'getIndexPengajuanRanap');
        $this->route('veda/pengajuan/ranap/(:int)', 'getIndexPengajuanRanap');
        $this->route('veda/perbaikan', 'getIndexPerbaikan');
        $this->route('veda/perbaikan/(:int)', 'getIndexPerbaikan');
        $this->route('veda/perbaikan/excel', 'getPerbaikanExport');
        $this->route('veda/ralan', 'getIndexRalan');
        $this->route('veda/ranap', 'getIndexRanap');
        $this->route('veda/css', 'getCss');
        $this->route('veda/javascript', 'getJavascript');
        $this->route('veda/pdf/(:str)', 'getPDF');
        $this->route('veda/createpdf/(:str)', 'getCreatePDF');
        $this->route('veda/downloadpdf/(:str)', 'getDownloadPDF');
        $this->route('veda/catatan/(:str)', 'getCatatan');
        $this->route('veda/logout', function () {
            $this->logout();
        });
    }

    public function getIndex()
    {
        if ($this->_loginCheck()) {
            $page = [
                'title' => 'VEDA',
                'desc' => 'Dashboard Verifikasi Digital Klaim BPJS',
                'content' => $this->_getManage()
            ];
        } else {
            if (isset($_POST['login'])) {
                if ($this->_login($_POST['username'], $_POST['password'])) {
                    if (count($arrayURL = parseURL()) > 1) {
                        $url = array_merge(['veda'], $arrayURL);
                        redirect(url($url));
                    }
                }
                redirect(url(['veda', '']));
            }
            $page = [
                'title' => 'VEDA',
                'desc' => 'Dashboard Verifikasi Digital Klaim BPJS',
                'content' => $this->draw('login.html', ['mlite' => $this->mlite])
            ];
        }

        $this->setTemplate('fullpage.html');
        $this->tpl->set('page', $page);

    }

    public function getIndexPengajuanRalan()
    {
      if ($this->_loginCheck()) {
        if(isset($_POST['perbaiki'])) {
          $simpan_status = $this->db('mlite_vedika')
          ->where('nosep', $_POST['nosep'])
          ->save([
            'status' => 'Perbaiki'
          ]);
          if($simpan_status) {
            $this->db('mlite_vedika_feedback')->save([
              'id' => NULL,
              'nosep' => $_POST['nosep'],
              'tanggal' => date('Y-m-d'),
              'catatan' => $_POST['catatan'],
              'username' => $_SESSION['vedika_user']
            ]);
          }
        }
        $page = [
            'title' => 'VEDA',
            'desc' => 'Dashboard Verifikasi Digital Klaim BPJS',
            'content' => $this->_getIndexPengajuanRalan($page = 1)
        ];
      } else {
        $page = [
            'title' => 'VEDA',
            'desc' => 'Dashboard Verifikasi Digital Klaim BPJS',
            'content' => $this->draw('login.html', ['mlite' => $this->mlite])
        ];
      }
      $this->setTemplate('fullpage.html');
      $this->tpl->set('page', $page);
    }

    public function _getIndexPengajuanRalan($page = 1)
    {
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

      $slug = parseURL();
      if (count($slug) == 4 && $slug[0] == 'veda' && $slug[1] == 'pengajuan') {
        $page = $slug[3];
      }
      // pagination
      $totalRecords = $this->db()->pdo()->prepare("SELECT no_rawat FROM mlite_vedika WHERE status = 'Pengajuan' AND jenis = '2' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND tgl_registrasi BETWEEN '$start_date' AND '$end_date'");
      $totalRecords->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
      $totalRecords = $totalRecords->fetchAll();

      $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url(['veda', 'pengajuan', 'ralan', '%d?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]));
      $this->assign['pagination'] = $pagination->nav('pagination','5');
      $this->assign['totalRecords'] = $totalRecords;

      $offset = $pagination->offset();
      $query = $this->db()->pdo()->prepare("SELECT * FROM mlite_vedika WHERE status = 'Pengajuan' AND jenis = '2' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND tgl_registrasi BETWEEN '$start_date' AND '$end_date' ORDER BY nosep LIMIT $perpage OFFSET $offset");
      $query->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
      $rows = $query->fetchAll();
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
              $row['umurdaftar'] = $this->core->getRegPeriksaInfo('umurdaftar', $row['no_rawat']);
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
              $row['sepURL'] = url(['veda', 'sep', $row['no_sep']]);
              $row['pdfURL'] = url(['veda', 'pdf', $this->convertNorawat($row['no_rawat'])]);
              $row['downloadURL'] = url(['veda', 'downloadpdf', $this->convertNorawat($row['no_rawat'])]);
              $row['catatanURL'] = url(['veda', 'catatan', $this->_getSEPInfo('no_sep', $row['no_rawat'])]);
              $row['status_pengajuan'] = $this->db('mlite_vedika')->where('nosep', $this->_getSEPInfo('no_sep', $row['no_rawat']))->desc('id')->limit(1)->toArray();
              $row['resumeURL']  = url(['veda', 'resume', $this->convertNorawat($row['no_rawat'])]);
              $row['billingURL'] = url(['veda', 'billing', $this->convertNorawat($row['no_rawat'])]);
              $this->assign['list'][] = $row;
          }
      }

      $this->assign['vedika_username'] = $this->settings->get('vedika.username');
      $this->assign['vedika_password'] = $this->settings->get('vedika.password');

      $this->assign['searchUrl'] =  url(['veda', 'pengajuan', 'ralan', $page]);
      return $this->draw('pengajuan_ralan.html', ['vedika' => $this->assign]);
    }

    public function getIndexPengajuanRanap()
    {
      if ($this->_loginCheck()) {
        if(isset($_POST['perbaiki'])) {
          $simpan_status = $this->db('mlite_vedika')
          ->where('nosep', $_POST['nosep'])
          ->save([
            'status' => 'Perbaiki'
          ]);
          if($simpan_status) {
            $this->db('mlite_vedika_feedback')->save([
              'id' => NULL,
              'nosep' => $_POST['nosep'],
              'tanggal' => date('Y-m-d'),
              'catatan' => $_POST['catatan'],
              'username' => $_SESSION['vedika_user']
            ]);
          }
        }
        $page = [
            'title' => 'VEDA',
            'desc' => 'Dashboard Verifikasi Digital Klaim BPJS',
            'content' => $this->_getIndexPengajuanRanap($page = 1)
        ];
      } else {
        $page = [
            'title' => 'VEDA',
            'desc' => 'Dashboard Verifikasi Digital Klaim BPJS',
            'content' => $this->draw('login.html', ['mlite' => $this->mlite])
        ];
      }
      $this->setTemplate('fullpage.html');
      $this->tpl->set('page', $page);
    }

    public function _getIndexPengajuanRanap($page = 1)
    {
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

      $slug = parseURL();
      if (count($slug) == 4 && $slug[0] == 'veda' && $slug[1] == 'pengajuan') {
        $page = $slug[3];
      }
      // pagination
      $totalRecords = $this->db()->pdo()->prepare("SELECT no_rawat FROM mlite_vedika WHERE status = 'Pengajuan' AND jenis = '1' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND tgl_registrasi BETWEEN '$start_date' AND '$end_date'");
      $totalRecords->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
      $totalRecords = $totalRecords->fetchAll();

      $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url(['veda', 'pengajuan', 'ranap', '%d?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]));
      $this->assign['pagination'] = $pagination->nav('pagination','5');
      $this->assign['totalRecords'] = $totalRecords;

      $offset = $pagination->offset();
      $query = $this->db()->pdo()->prepare("SELECT * FROM mlite_vedika WHERE status = 'Pengajuan' AND jenis = '1' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND tgl_registrasi BETWEEN '$start_date' AND '$end_date' ORDER BY nosep LIMIT $perpage OFFSET $offset");
      $query->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
      $rows = $query->fetchAll();
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
              $row['umurdaftar'] = $this->core->getRegPeriksaInfo('umurdaftar', $row['no_rawat']);
              $row['sttsumur'] = $this->core->getRegPeriksaInfo('sttsumur', $row['no_rawat']);
              $row['tgl_registrasi'] = $this->core->getRegPeriksaInfo('tgl_registrasi', $row['no_rawat']);
              $row['tgl_masuk'] = $this->core->getKamarInapInfo('tgl_masuk', $row['no_rawat']);
              $row['jam_masuk'] = $this->core->getKamarInapInfo('jam_masuk', $row['no_rawat']);
              $row['tgl_keluar'] = $this->core->getKamarInapInfo('tgl_keluar', $row['no_rawat']);
              $row['jam_keluar'] = $this->core->getKamarInapInfo('jam_keluar', $row['no_rawat']);
              $row['kd_kamar'] = $this->core->getKamarInapInfo('kd_kamar', $row['no_rawat']);
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
              $row['sepURL'] = url(['veda', 'sep', $row['no_sep']]);
              $row['pdfURL'] = url(['veda', 'pdf', $this->convertNorawat($row['no_rawat'])]);
              $row['downloadURL'] = url(['veda', 'downloadpdf', $this->convertNorawat($row['no_rawat'])]);
              $row['catatanURL'] = url(['veda', 'catatan', $this->_getSEPInfo('no_sep', $row['no_rawat'])]);
              $row['status_pengajuan'] = $this->db('mlite_vedika')->where('nosep', $this->_getSEPInfo('no_sep', $row['no_rawat']))->desc('id')->limit(1)->toArray();
              $row['resumeURL']  = url(['veda', 'resume', $this->convertNorawat($row['no_rawat'])]);
              $row['billingURL'] = url(['veda', 'billing', $this->convertNorawat($row['no_rawat'])]);
              $this->assign['list'][] = $row;
          }
      }

      $this->assign['vedika_username'] = $this->settings->get('vedika.username');
      $this->assign['vedika_password'] = $this->settings->get('vedika.password');

      $this->assign['searchUrl'] =  url(['veda', 'pengajuan', 'ralan', $page]);
      return $this->draw('pengajuan_ranap.html', ['vedika' => $this->assign]);
    }

    public function getIndexPerbaikan()
    {
      if ($this->_loginCheck()) {
        if(isset($_POST['perbaiki'])) {
          $simpan_status = $this->db('mlite_vedika')
          ->where('nosep', $_POST['nosep'])
          ->save([
            'status' => 'Perbaiki'
          ]);
          if($simpan_status) {
            $this->db('mlite_vedika_feedback')->save([
              'id' => NULL,
              'nosep' => $_POST['nosep'],
              'tanggal' => date('Y-m-d'),
              'catatan' => $_POST['catatan'],
              'username' => $_SESSION['vedika_user']
            ]);
          }
        }
        $page = [
            'title' => 'VEDA',
            'desc' => 'Dashboard Verifikasi Digital Klaim BPJS',
            'content' => $this->_getIndexPerbaikan($page = 1)
        ];
      } else {
        $page = [
            'title' => 'VEDA',
            'desc' => 'Dashboard Verifikasi Digital Klaim BPJS',
            'content' => $this->draw('login.html', ['mlite' => $this->mlite])
        ];
      }
      $this->setTemplate('fullpage.html');
      $this->tpl->set('page', $page);
    }

    public function _getIndexPerbaikan($page = 1)
    {
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

      $slug = parseURL();
      if (count($slug) == 3 && $slug[0] == 'veda' && $slug[1] == 'perbaikan') {
        $page = $slug[2];
      }
      // pagination
      $totalRecords = $this->db()->pdo()->prepare("SELECT no_rawat FROM mlite_vedika WHERE status = 'Perbaiki' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND tgl_registrasi BETWEEN '$start_date' AND '$end_date'");
      $totalRecords->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
      $totalRecords = $totalRecords->fetchAll();

      $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url(['veda', 'perbaikan', '%d?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]));
      $this->assign['pagination'] = $pagination->nav('pagination','5');
      $this->assign['totalRecords'] = $totalRecords;

      $offset = $pagination->offset();
      $query = $this->db()->pdo()->prepare("SELECT * FROM mlite_vedika WHERE status = 'Perbaiki' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND tgl_registrasi BETWEEN '$start_date' AND '$end_date' ORDER BY nosep LIMIT $perpage OFFSET $offset");
      $query->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
      $rows = $query->fetchAll();
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
              $row['umurdaftar'] = $this->core->getRegPeriksaInfo('umurdaftar', $row['no_rawat']);
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
              $row['sepURL'] = url(['veda', 'sep', $row['no_sep']]);
              $row['pdfURL'] = url(['veda', 'pdf', $this->convertNorawat($row['no_rawat'])]);
              $row['downloadURL'] = url(['veda', 'downloadpdf', $this->convertNorawat($row['no_rawat'])]);
              $row['catatanURL'] = url(['veda', 'catatan', $this->_getSEPInfo('no_sep', $row['no_rawat'])]);
              $row['status_pengajuan'] = $this->db('mlite_vedika')->where('nosep', $this->_getSEPInfo('no_sep', $row['no_rawat']))->desc('id')->limit(1)->toArray();
              $row['resumeURL']  = url(['veda', 'resume', $this->convertNorawat($row['no_rawat'])]);
              $row['billingURL'] = url(['veda', 'billing', $this->convertNorawat($row['no_rawat'])]);
              $this->assign['list'][] = $row;
          }
      }

      $this->assign['vedika_username'] = $this->settings->get('vedika.username');
      $this->assign['vedika_password'] = $this->settings->get('vedika.password');

      $this->assign['searchUrl'] =  url(['veda', 'perbaikan', $page]);
      return $this->draw('perbaikan.html', ['vedika' => $this->assign]);
    }

    public function getPerbaikanExport()
    {
      $start_date = $_GET['start_date'];
      $end_date = $_GET['end_date'];
      $rows = $this->db('mlite_vedika')->where('status', 'Perbaiki')->where('tgl_registrasi','>=',$start_date)->where('tgl_registrasi','<=', $end_date)->toArray();
      if(isset($_GET['jenis']) && $_GET['jenis'] == 1) {
        $rows = $this->db('mlite_vedika')->where('status', 'Perbaiki')->where('tgl_registrasi','>=',$start_date)->where('tgl_registrasi','<=', $end_date)->where('jenis', 1)->toArray();
      }
      if(isset($_GET['jenis']) && $_GET['jenis'] == 2) {
        $rows = $this->db('mlite_vedika')->where('status', 'Perbaiki')->where('tgl_registrasi','>=',$start_date)->where('tgl_registrasi','<=', $end_date)->where('jenis', 2)->toArray();
      }
      $i = 1;
      foreach ($rows as $row) {
        $row['status_lanjut'] = 'Ralan';
        if($row['jenis'] == 1) {
          $row['status_lanjut'] = 'Ranap';
        }
        $row['no'] = $i++;
        $row['tgl_masuk'] = $this->core->getRegPeriksaInfo('tgl_registrasi', $row['no_rawat']);
        $row['tgl_keluar'] = $this->core->getRegPeriksaInfo('tgl_registrasi', $row['no_rawat']);
        if($row['jenis'] == 1) {
          $row['tgl_masuk'] = $this->core->getKamarInapInfo('tgl_masuk', $row['no_rawat']);
          $row['tgl_keluar'] = $this->core->getKamarInapInfo('tgl_keluar', $row['no_rawat']);
        }
        $row['nm_pasien'] = $this->core->getPasienInfo('nm_pasien', $row['no_rkm_medis']);
        $row['no_peserta'] = $this->core->getPasienInfo('no_peserta', $row['no_rkm_medis']);
        $row['kd_penyakit'] = $this->_getDiagnosa('kd_penyakit', $row['no_rawat'], $row['status_lanjut']);
        $row['kd_prosedur'] = $this->_getProsedur('kode', $row['no_rawat'], $row['status_lanjut']);
        $get_feedback_bpjs = $this->db('mlite_vedika_feedback')->where('nosep', $row['nosep'])->where('username', 'bpjs')->oneArray();
        $row['konfirmasi_bpjs'] = $get_feedback_bpjs['catatan'];
        $get_feedback_rs = $this->db('mlite_vedika_feedback')->where('nosep', $row['nosep'])->where('username','!=','bpjs')->oneArray();
        $row['konfirmasi_rs'] = $get_feedback_rs['catatan'];
        $display[] = $row;
      }
      $content = $this->draw('perbaikan_excel.html', [
        'powered' => 'Powered by <a href="https://basoro.org/">KhanzaLITE</a>',
        'display' => $display
      ]);

      $assign = [
          'title' => $this->settings->get('settings.nama_instansi'),
          'desc' => $this->settings->get('settings.alamat'),
          'content' => $content
      ];
      $this->setTemplate("canvas.html");
      $this->tpl->set('page', ['title' => $assign['title'], 'desc' => $assign['desc'], 'content' => $assign['content']]);
    }

    public function getIndexRalan()
    {
      if ($this->_loginCheck()) {
        if(isset($_POST['setuju'])) {
          $this->db('mlite_vedika')->save([
            'id' => NULL,
            'tanggal' => date('Y-m-d'),
            'no_rkm_medis' => $_POST['no_rkm_medis'],
            'no_rawat' => $_POST['no_rawat'],
            'nosep' => $_POST['nosep'],
            'catatan' => $_POST['catatan'],
            'status' => 'Setuju',
            'username' => $_SESSION['vedika_user']
          ]);
        }

        if(isset($_POST['perbaiki'])) {
          $this->db('mlite_vedika')->save([
            'id' => NULL,
            'tanggal' => date('Y-m-d'),
            'no_rkm_medis' => $_POST['no_rkm_medis'],
            'no_rawat' => $_POST['no_rawat'],
            'nosep' => $_POST['nosep'],
            'catatan' => $_POST['catatan'],
            'status' => 'Perbaiki',
            'username' => $_SESSION['vedika_user']
          ]);
        }
        $page = [
            'title' => 'VEDA',
            'desc' => 'Dashboard Verifikasi Digital Klaim BPJS',
            'content' => $this->_getManageRalan($page = 1)
        ];
      } else {
        $page = [
            'title' => 'VEDA',
            'desc' => 'Dashboard Verifikasi Digital Klaim BPJS',
            'content' => $this->draw('login.html', ['mlite' => $this->mlite])
        ];
      }
      $this->setTemplate('fullpage.html');
      $this->tpl->set('page', $page);
    }

    public function getIndexRanap()
    {
      if($this->_loginCheck()) {
        if(isset($_POST['setuju'])) {
          $this->db('mlite_vedika')->save([
            'id' => NULL,
            'tanggal' => date('Y-m-d'),
            'no_rkm_medis' => $_POST['no_rkm_medis'],
            'no_rawat' => $_POST['no_rawat'],
            'nosep' => $_POST['nosep'],
            'catatan' => $_POST['catatan'],
            'status' => 'Setuju',
            'username' => $_SESSION['vedika_user']
          ]);
        }

        if(isset($_POST['perbaiki'])) {
          $this->db('mlite_vedika')->save([
            'id' => NULL,
            'tanggal' => date('Y-m-d'),
            'no_rkm_medis' => $_POST['no_rkm_medis'],
            'no_rawat' => $_POST['no_rawat'],
            'nosep' => $_POST['nosep'],
            'catatan' => $_POST['catatan'],
            'status' => 'Perbaiki',
            'username' => $_SESSION['vedika_user']
          ]);
        }
        $page = [
            'title' => 'VEDA',
            'desc' => 'Dashboard Verifikasi Digital Klaim BPJS',
            'content' => $this->_getManageRanap($page = 1)
        ];
      } else {
        $page = [
            'title' => 'VEDA',
            'desc' => 'Dashboard Verifikasi Digital Klaim BPJS',
            'content' => $this->draw('login.html', ['mlite' => $this->mlite])
        ];
      }

        $this->setTemplate('fullpage.html');
        $this->tpl->set('page', $page);
    }

    public function _getManage()
    {
      $this->_addHeaderFiles();
      $pengajuan_ralan = $this->db('mlite_vedika')->select(['count' => 'COUNT(DISTINCT no_rawat)'])->where('status', 'Pengajuan')->where('jenis', 2)->oneArray();
      $pengajuan_ranap = $this->db('mlite_vedika')->select(['count' => 'COUNT(DISTINCT no_rawat)'])->where('status', 'Pengajuan')->where('jenis', 1)->oneArray();
      $perbaiki = $this->db('mlite_vedika')->select(['count' => 'COUNT(DISTINCT no_rawat)'])->where('status', 'Perbaiki')->oneArray();

      $stat['pengajuan_ralan'] = $pengajuan_ralan['count'];
      $stat['pengajuan_ranap'] = $pengajuan_ranap['count'];
      $stat['perbaiki'] = $perbaiki['count'];
      return $this->draw('index.html', ['stat' => $stat]);
    }

    public function _getManageRalan($page = 1)
    {
      $this->_addHeaderFiles();
      $perpage = '1';
      $phrase = '';
      if(isset($_GET['s']))
        $phrase = $_GET['s'];

      // pagination
      $totalRecords = $this->db()->pdo()->prepare("SELECT reg_periksa.no_rawat FROM reg_periksa, pasien, mlite_vedika WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND (reg_periksa.no_rkm_medis LIKE ? OR reg_periksa.no_rawat LIKE ? OR pasien.nm_pasien LIKE ?) AND reg_periksa.status_lanjut = 'Ralan' AND mlite_vedika.status = 'Pengajuan'");
      $totalRecords->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
      $totalRecords = $totalRecords->fetchAll();

      $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url([ADMIN, 'vedika', 'index', '%d?s='.$phrase]));
      $this->assign['pagination'] = $pagination->nav('pagination','5');
      $this->assign['totalRecords'] = $totalRecords;

      $offset = $pagination->offset();
      $query = $this->db()->pdo()->prepare("SELECT reg_periksa.*, pasien.*, dokter.nm_dokter, poliklinik.nm_poli, penjab.png_jawab FROM reg_periksa, pasien, dokter, poliklinik, penjab, mlite_vedika WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.kd_dokter = dokter.kd_dokter AND reg_periksa.kd_poli = poliklinik.kd_poli AND reg_periksa.kd_pj = penjab.kd_pj AND (reg_periksa.no_rkm_medis LIKE ? OR reg_periksa.no_rawat LIKE ? OR pasien.nm_pasien LIKE ?) AND mlite_vedika.status = 'Pengajuan' AND reg_periksa.status_lanjut = 'Ralan' LIMIT $perpage OFFSET $offset");
      $query->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
      $rows = $query->fetchAll();

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
              $row['sepURL'] = url(['veda', 'sep', $row['no_sep']]);
              $row['pdfURL'] = url(['veda', 'pdf', $this->convertNorawat($row['no_rawat'])]);
              $row['downloadURL'] = url(['veda', 'downloadpdf', $this->convertNorawat($row['no_rawat'])]);
              $row['catatanURL'] = url(['veda', 'catatan', $this->_getSEPInfo('no_sep', $row['no_rawat'])]);
              $row['status_pengajuan'] = $this->db('mlite_vedika')->where('nosep', $this->_getSEPInfo('no_sep', $row['no_rawat']))->desc('id')->limit(1)->toArray();
              $row['resumeURL']  = url(['veda', 'resume', $this->convertNorawat($row['no_rawat'])]);
              $row['billingURL'] = url(['veda', 'billing', $this->convertNorawat($row['no_rawat'])]);
              $this->assign['list'][] = $row;
          }
      }

      $this->assign['vedika_username'] = $this->settings->get('vedika.username');
      $this->assign['vedika_password'] = $this->settings->get('vedika.password');

      $this->assign['searchUrl'] =  url(['veda', 'ralan', $page.'?start_date='.$start_date.'&end_date='.$end_date]);
      return $this->draw('manage_ralan.html', ['vedika' => $this->assign]);

    }

    public function _getManageRanap($page = 1)
    {
      $this->_addHeaderFiles();

      $query = $this->db()->pdo()->prepare("SELECT reg_periksa.*, pasien.*, dokter.nm_dokter, poliklinik.nm_poli, penjab.png_jawab FROM reg_periksa, pasien, dokter, poliklinik, penjab, mlite_vedika WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.kd_dokter = dokter.kd_dokter AND reg_periksa.kd_poli = poliklinik.kd_poli AND reg_periksa.kd_pj = penjab.kd_pj AND reg_periksa.status_lanjut = 'Ranap' AND reg_periksa.no_rawat = mlite_vedika.no_rawat AND mlite_vedika.status = 'Pengajuan'");
      $query->execute();
      $rows = $query->fetchAll();

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
              $row['sepURL'] = url(['veda', 'sep', $row['no_sep']]);
              $row['pdfURL'] = url(['veda', 'pdf', $this->convertNorawat($row['no_rawat'])]);
              $row['downloadURL'] = url(['veda', 'downloadpdf', $this->convertNorawat($row['no_rawat'])]);
              $row['catatanURL'] = url(['veda', 'catatan', $this->convertNorawat($row['no_rawat'])]);
              $row['resumeURL']  = url(['veda', 'resume', $this->convertNorawat($row['no_rawat'])]);
              $row['billingURL'] = url(['veda', 'billing', $this->convertNorawat($row['no_rawat'])]);
              $this->assign['list'][] = $row;
          }
      }

      $this->assign['vedika_username'] = $this->settings->get('vedika.username');
      $this->assign['vedika_password'] = $this->settings->get('vedika.password');

      $this->assign['searchUrl'] =  url(['veda', 'ranap', $page.'?start_date='.$start_date.'&end_date='.$end_date]);
      return $this->draw('manage_ranap.html', ['vedika' => $this->assign]);

    }

    public function getPDF($id)
    {
      if ($this->_loginCheck()) {

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

        $settings = $this->settings('settings');
        $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));

        $this->tpl->set('billing', $rows);
        //$this->tpl->set('instansi', $instansi);

        $print_sep = array();
        if(!empty($this->_getSEPInfo('no_sep', $no_rawat))) {
          $print_sep['bridging_sep'] = $this->db('bridging_sep')->where('no_sep', $this->_getSEPInfo('no_sep', $no_rawat))->oneArray();
          $print_sep['bpjs_prb'] = $this->db('bpjs_prb')->where('no_sep', $this->_getSEPInfo('no_sep', $no_rawat))->oneArray();
          $batas_rujukan = $this->db('bridging_sep')->select('DATE_ADD(tglrujukan , INTERVAL 85 DAY) AS batas_rujukan')->where('no_sep', $id)->oneArray();
          $print_sep['batas_rujukan'] = $batas_rujukan['batas_rujukan'];
        }

        $print_sep['logoURL'] = url(MODULES.'/pendaftaran/img/bpjslogo.png');
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
        $rujukan_internal = $this->db('rujukan_internal_poli')
          ->join('poliklinik', 'poliklinik.kd_poli = rujukan_internal_poli.kd_poli')
          ->join('dokter', 'dokter.kd_dokter = rujukan_internal_poli.kd_dokter')
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
        echo $this->tpl->draw(MODULES.'/vedika/view/pdf.html', true);
        exit();
      } else {
        redirect(url(['veda', '']));
      }
    }

    public function getCreatePDF($id)
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

        $settings = $this->settings('settings');
        $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));

        $this->tpl->set('billing', $rows);
        //$this->tpl->set('instansi', $instansi);

        $print_sep = array();
        if(!empty($this->_getSEPInfo('no_sep', $no_rawat))) {
          $print_sep['bridging_sep'] = $this->db('bridging_sep')->where('no_sep', $this->_getSEPInfo('no_sep', $no_rawat))->oneArray();
          $print_sep['bpjs_prb'] = $this->db('bpjs_prb')->where('no_sep', $this->_getSEPInfo('no_sep', $no_rawat))->oneArray();
          $batas_rujukan = $this->db('bridging_sep')->select('DATE_ADD(tglrujukan , INTERVAL 85 DAY) AS batas_rujukan')->where('no_sep', $id)->oneArray();
          $print_sep['batas_rujukan'] = $batas_rujukan['batas_rujukan'];
        }

        $print_sep['logoURL'] = url(MODULES.'/pendaftaran/img/bpjslogo.png');
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
        $rujukan_internal = $this->db('rujukan_internal_poli')
          ->join('poliklinik', 'poliklinik.kd_poli = rujukan_internal_poli.kd_poli')
          ->join('dokter', 'dokter.kd_dokter = rujukan_internal_poli.kd_dokter')
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
        echo $this->tpl->draw(MODULES.'/vedika/view/pdf.html', true);
        exit();
    }

    public function getCatatan($id)
    {
      $set_status = $this->db('bridging_sep')->where('no_sep', $id)->oneArray();
      $vedika = $this->db('mlite_vedika')->where('nosep', $id)->asc('id')->toArray();
      $vedika_feedback = $this->db('mlite_vedika_feedback')->where('nosep', $id)->asc('id')->toArray();
      $this->tpl->set('logo', $this->settings->get('settings.logo'));
      $this->tpl->set('nama_instansi', $this->settings->get('settings.nama_instansi'));
      $this->tpl->set('set_status', $set_status);
      $this->tpl->set('vedika', $vedika);
      $this->tpl->set('vedika_feedback', $vedika_feedback);
      $this->tpl->set('username', $_SESSION['vedika_user']);
      echo $this->tpl->draw(MODULES.'/vedika/view/catatan.html', true);
      exit();
    }

    public function getDownloadPDF($id)
    {
      $apikey = 'c811af07-d551-40ec-8e87-9abbf03abe16';
      $value = url().'/veda/createpdf/'.$id; // can aso be a url, starting with http..

      $bridging_sep = $this->db('bridging_sep')->where('no_rawat', $this->revertNorawat($id))->oneArray();

      // Convert the HTML string to a PDF using those parameters.  Note if you have a very long HTML string use POST rather than get.  See example #5
      $result = file_get_contents("http://url2pdf.basoro.id/?apikey=" . urlencode($apikey) . "&url=" . urlencode($value));

      // Save to root folder in website
      //file_put_contents('mypdf-1.pdf', $result);

      // Output headers so that the file is downloaded rather than displayed
      // Remember that header() must be called before any actual output is sent
      header('Content-Description: File Transfer');
      header('Content-Type: application/pdf');
      header('Expires: 0');
      header('Cache-Control: must-revalidate');
      header('Pragma: public');
      header('Content-Length: ' . strlen($result));

      // Make the file a downloadable attachment - comment this out to show it directly inside the
      // web browser.  Note that you can give the file any name you want, e.g. alias-name.pdf below:
      header('Content-Disposition: attachment; filename=' . 'e-vedika-'.$bridging_sep['tglsep'].'-'.$bridging_sep['no_sep'].'.pdf' );

      // Stream PDF to user
      echo $result;
      exit();
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

    private function _getProsedur($field, $no_rawat, $status_lanjut)
    {
        $row = $this->db('prosedur_pasien')->join('icd9', 'icd9.kode = prosedur_pasien.kode')->where('prosedur_pasien.no_rawat', $no_rawat)->where('prosedur_pasien.prioritas', 1)->where('prosedur_pasien.status', $status_lanjut)->oneArray();
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

    private function _login($username, $password)
    {
        // Check attempt
        $attempt = $this->db('mlite_login_attempts')->where('ip', $_SERVER['REMOTE_ADDR'])->oneArray();

        // Create attempt if does not exist
        if (!$attempt) {
            $this->db('mlite_login_attempts')->save(['ip' => $_SERVER['REMOTE_ADDR'], 'attempts' => 0]);
            $attempt = ['ip' => $_SERVER['REMOTE_ADDR'], 'attempts' => 0, 'expires' => 0];
        } else {
            $attempt['attempts'] = intval($attempt['attempts']);
            $attempt['expires'] = intval($attempt['expires']);
        }

        $row_username = $this->settings->get('vedika.username');
        $row_password = $this->settings->get('vedika.password');

        if ($row_username == $username && $row_password == $password) {
            // Reset fail attempts for this IP
            $this->db('mlite_login_attempts')->where('ip', $_SERVER['REMOTE_ADDR'])->save(['attempts' => 0]);

            $_SESSION['vedika_user']       = $row_username;
            $_SESSION['vedika_token']      = bin2hex(openssl_random_pseudo_bytes(6));
            $_SESSION['vedika_userAgent']  = $_SERVER['HTTP_USER_AGENT'];
            $_SESSION['vedika_IPaddress']  = $_SERVER['REMOTE_ADDR'];

            return true;
        } else {
            // Increase attempt
            $this->db('mlite_login_attempts')->where('ip', $_SERVER['REMOTE_ADDR'])->save(['attempts' => $attempt['attempts']+1]);
            $attempt['attempts'] += 1;

            // ... and block if reached maximum attempts
            if ($attempt['attempts'] % 3 == 0) {
                $this->db('mlite_login_attempts')->where('ip', $_SERVER['REMOTE_ADDR'])->save(['expires' => strtotime("+10 minutes")]);
                $attempt['expires'] = strtotime("+10 minutes");

                $this->core->setNotify('failure', sprintf('Batas maksimum login tercapai. Tunggu %s menit untuk coba lagi.', ceil(($attempt['expires']-time())/60)));
            } else {
                $this->core->setNotify('failure', 'Username atau password salah!');
            }

            return false;
        }
    }

    private function _loginCheck()
    {
        if (isset($_SESSION['vedika_user']) && isset($_SESSION['vedika_token']) && isset($_SESSION['vedika_userAgent']) && isset($_SESSION['vedika_IPaddress'])) {
            if ($_SESSION['vedika_IPaddress'] != $_SERVER['REMOTE_ADDR']) {
                return false;
            }
            if ($_SESSION['vedika_userAgent'] != $_SERVER['HTTP_USER_AGENT']) {
                return false;
            }

            if (empty(parseURL(1))) {
                redirect(url('veda'));
            } elseif (!isset($_GET['t']) || ($_SESSION['vedika_token'] != @$_GET['t'])) {
                return false;
            }

            return true;
        }

        return false;
    }

    private function logout()
    {
        unset($_SESSION['vedika_user']);
        unset($_SESSION['vedika_token']);
        unset($_SESSION['vedika_userAgent']);
        unset($_SESSION['vedika_IPaddress']);

        redirect(url('veda'));
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/vedika/js/scripts.js');
        exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/vedika/css/styles.css');
        exit();
    }

    private function _addHeaderFiles()
    {
        // CSS
        $this->core->addCSS(url('assets/css/jquery-ui.css'));
        $this->core->addCSS(url('assets/css/jquery.timepicker.css'));
        //$this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));

        // JS
        $this->core->addJS(url('assets/jscripts/jquery-ui.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/jquery.timepicker.js'), 'footer');
        //$this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'), 'footer');
        //$this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'), 'footer');

        // MODULE SCRIPTS
        $this->core->addCSS(url(['veda', 'css']));
        $this->core->addJS(url(['veda', 'javascript']), 'footer');
    }

}
