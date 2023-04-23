<?php

namespace Plugins\Vedika;

use Systems\AdminModule;
use Systems\Lib\BpjsService;

class Admin extends AdminModule
{
  private $_uploads = WEBAPPS_PATH . '/berkasrawat/pages/upload';

  public function init()
  {
    $this->consid = $this->settings->get('settings.BpjsConsID');
    $this->secretkey = $this->settings->get('settings.BpjsSecretKey');
    $this->user_key = $this->settings->get('settings.BpjsUserKey');
    $this->api_url = $this->settings->get('settings.BpjsApiUrl');
  }

  public function navigation()
  {
    return [
      'Manage' => 'manage',
      'Index' => 'index',
      'Lengkap' => 'lengkap',
      'Pengajuan' => 'pengajuan',
      'Perbaikan' => 'perbaikan',
      'Mapping Inacbgs' => 'mappinginacbgs',
      'Bridging Eklaim' => 'bridgingeklaim',
      'User Vedika' => 'uservedika',
      'Pengaturan' => 'settings',
    ];
  }

  public function getManage()
  {
    $this->_addHeaderFiles();
    $this->core->addJS(url(BASE_DIR.'/assets/jscripts/Chart.bundle.min.js'));
    $carabayar = str_replace(",","','", $this->settings->get('vedika.carabayar'));
    $stats['Chart'] = $this->Chart();
    $date = $this->settings->get('vedika.periode');
    if(isset($_GET['periode']) && $_GET['periode'] !=''){
      $date = $_GET['periode'];
    }

    $KlaimRalan = $this->core->mysql()->pdo()->prepare("SELECT reg_periksa.no_rawat FROM reg_periksa, penjab WHERE reg_periksa.kd_pj = penjab.kd_pj AND penjab.kd_pj IN ('$carabayar') AND reg_periksa.tgl_registrasi LIKE '{$date}%' AND reg_periksa.status_lanjut = 'Ralan'");
    $KlaimRalan->execute();
    $KlaimRalan = $KlaimRalan->fetchAll();
    $stats['KlaimRalan'] = 0;
    if(count($KlaimRalan) > 0) {
      $stats['KlaimRalan'] = count($KlaimRalan);
    }

    $KlaimRanap = $this->core->mysql()->pdo()->prepare("SELECT reg_periksa.no_rawat FROM reg_periksa, penjab, kamar_inap WHERE reg_periksa.no_rawat = kamar_inap.no_rawat AND reg_periksa.kd_pj = penjab.kd_pj AND penjab.kd_pj IN ('$carabayar') AND kamar_inap.tgl_keluar LIKE '{$date}%' AND reg_periksa.status_lanjut = 'Ranap'");
    $KlaimRanap->execute();
    $KlaimRanap = $KlaimRanap->fetchAll();
    $stats['KlaimRanap'] = 0;
    if(count($KlaimRanap) > 0) {
      $stats['KlaimRanap'] = count($KlaimRanap);
    }

    $stats['totalKlaim'] = $stats['KlaimRalan'] + $stats['KlaimRanap'];

    $LengkapRalan = $this->core->mysql()->pdo()->prepare("SELECT no_rawat FROM mlite_vedika WHERE status = 'Lengkap' AND jenis = '2' AND tgl_registrasi LIKE '{$date}%'");
    $LengkapRalan->execute();
    $LengkapRalan = $LengkapRalan->fetchAll();
    $stats['LengkapRalan'] = 0;
    if(count($LengkapRalan) > 0) {
      $stats['LengkapRalan'] = count($LengkapRalan);
    }

    $LengkapRanap = $this->core->mysql()->pdo()->prepare("SELECT no_rawat FROM mlite_vedika WHERE status = 'Lengkap' AND jenis = '1' AND no_rawat IN (SELECT no_rawat FROM kamar_inap WHERE tgl_keluar LIKE '{$date}%')");
    $LengkapRanap->execute();
    $LengkapRanap = $LengkapRanap->fetchAll();
    $stats['LengkapRanap'] = 0;
    if(count($LengkapRanap) > 0) {
      $stats['LengkapRanap'] = count($LengkapRanap);
    }

    $stats['totalLengkap'] = $stats['LengkapRalan'] + $stats['LengkapRanap'];

    $PengajuanRalan = $this->core->mysql()->pdo()->prepare("SELECT no_rawat FROM mlite_vedika WHERE status = 'Pengajuan' AND jenis = '2' AND tgl_registrasi LIKE '{$date}%'");
    $PengajuanRalan->execute();
    $PengajuanRalan = $PengajuanRalan->fetchAll();
    $stats['PengajuanRalan'] = count($PengajuanRalan);

    $PengajuanRanap = $this->core->mysql()->pdo()->prepare("SELECT no_rawat FROM mlite_vedika WHERE status = 'Pengajuan' AND jenis = '1' AND no_rawat IN (SELECT no_rawat FROM kamar_inap WHERE tgl_keluar LIKE '{$date}%')");
    $PengajuanRanap->execute();
    $PengajuanRanap = $PengajuanRanap->fetchAll();
    $stats['PengajuanRanap'] = count($PengajuanRanap);

    $stats['totalPengajuan'] = $stats['PengajuanRalan'] + $stats['PengajuanRanap'];

    $PerbaikanRalan = $this->core->mysql()->pdo()->prepare("SELECT no_rawat FROM mlite_vedika WHERE status = 'Perbaiki' AND jenis = '2' AND tgl_registrasi LIKE '{$date}%'");
    $PerbaikanRalan->execute();
    $PerbaikanRalan = $PerbaikanRalan->fetchAll();
    $stats['PerbaikanRalan'] = count($PerbaikanRalan);

    $PerbaikanRanap = $this->core->mysql()->pdo()->prepare("SELECT no_rawat FROM mlite_vedika WHERE status = 'Perbaiki' AND jenis = '1' AND tgl_registrasi LIKE '{$date}%'");
    $PerbaikanRanap->execute();
    $PerbaikanRanap = $PerbaikanRanap->fetchAll();
    $stats['PerbaikanRanap'] = count($PerbaikanRanap);

    $stats['totalPerbaikan'] = $stats['PerbaikanRalan'] + $stats['PerbaikanRanap'];

    //$stats['rencanaRalan'] = $stats['LengkapRalan'] + $stats['PengajuanRalan'];
    //$stats['rencanaRanap'] = $stats['LengkapRanap'] + $stats['PengajuanRanap'];
    $stats['rencanaRalan'] = $stats['KlaimRalan'];
    $stats['rencanaRanap'] = $stats['KlaimRanap'];

    $sub_modules = [
      ['name' => 'Index', 'url' => url([ADMIN, 'vedika', 'index']), 'icon' => 'code', 'desc' => 'Index Vedika'],
      ['name' => 'Lengkap', 'url' => url([ADMIN, 'vedika', 'lengkap']), 'icon' => 'code', 'desc' => 'Index Lengkap Vedika'],
      ['name' => 'Pengajuan', 'url' => url([ADMIN, 'vedika', 'pengajuan']), 'icon' => 'code', 'desc' => 'Index Pengajuan Vedika'],
      ['name' => 'Perbaikan', 'url' => url([ADMIN, 'vedika', 'perbaikan']), 'icon' => 'code', 'desc' => 'Index Perbaikan Vedika'],
      ['name' => 'Mapping Inacbgs', 'url' => url([ADMIN, 'vedika', 'mappinginacbgs']), 'icon' => 'code', 'desc' => 'Pengaturan Mapping Inacbgs'],
      ['name' => 'Bridging Eklaim', 'url' => url([ADMIN, 'vedika', 'bridgingeklaim']), 'icon' => 'code', 'desc' => 'Bridging Eklaim'],
      ['name' => 'User Vedika', 'url' => url([ADMIN, 'vedika', 'users']), 'icon' => 'code', 'desc' => 'User Vedika'],
      ['name' => 'Pengaturan', 'url' => url([ADMIN, 'vedika', 'settings']), 'icon' => 'code', 'desc' => 'Pengaturan Vedika'],
    ];
    return $this->draw('manage.html', ['sub_modules' => $sub_modules, 'stats' => $stats, 'periode' => $date]);
  }

  public function Chart()
  {

      $query = $this->core->mysql('reg_periksa')
          ->select([
            'count'       => 'COUNT(DISTINCT kd_pj)',
            'tgl_registrasi'     => 'tgl_registrasi',
          ])
          //->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
          ->where('tgl_registrasi', '>=', date('Y-m'))
          //->group(['reg_periksa.kd_pj'])
          ->desc('kd_pj');


          $data = $query->toArray();

          $return = [
              'labels'  => [],
              'visits'  => [],
          ];

          foreach ($data as $value) {
              $return['labels'][] = $value['tgl_registrasi'];
              $return['visits'][] = $value['count'];
          }

      return $return;
  }

  public function anyIndex($type = 'ralan', $page = 1)
  {
    if (isset($_POST['submit'])) {
      if (!$this->core->mysql('mlite_vedika')->where('nosep', $_POST['nosep'])->oneArray()) {
        $simpan_status = $this->core->mysql('mlite_vedika')->save([
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
        $simpan_status = $this->core->mysql('mlite_vedika')
          ->where('nosep', $_POST['nosep'])
          ->save([
            'tanggal' => date('Y-m-d'),
            'status' => $_POST['status']
          ]);
      }
      if ($simpan_status) {
        $this->core->mysql('mlite_vedika_feedback')->save([
          'id' => NULL,
          'nosep' => $_POST['nosep'],
          'tanggal' => date('Y-m-d'),
          'catatan' => $_POST['status'].' - '.$_POST['catatan'],
          'username' => $this->core->getUserInfo('username', null, true)
        ]);
      }
    }

    if (isset($_POST['simpanberkas'])) {
      if(MULTI_APP) {

        $curl = curl_init();
        $filePath = $_FILES['files']['tmp_name'];
        $file_type = $_FILES['files']['type'];
        if($file_type=='application/pdf'){
          $imagick = new \Imagick();
          $imagick->readImage($image);
          $imagick->writeImages($image.'.jpg', false);
          $filePath = $image.'.jpg';
        }

        curl_setopt_array($curl, array(
          CURLOPT_URL => str_replace('webapps','',WEBAPPS_URL).'api/berkasdigital',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => array('file'=> new \CURLFILE($filePath),'token' => $this->settings->get('api.berkasdigital_key'), 'no_rawat' => $_POST['no_rawat'], 'kode' => $_POST['kode']),
          CURLOPT_HTTPHEADER => array(),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        //echo $response;
        if($response == 'Success') {
          $this->notify('success', 'Sukses menambahkan gambar');
        } else {
          $this->notify('failure', 'Gagal menambahkan gambar');
        }

      } else {
        $dir    = $this->_uploads;
        $cntr   = 0;

        $image = $_FILES['files']['tmp_name'];

        $file_type = $_FILES['files']['type'];
        if($file_type=='application/pdf'){
          $imagick = new \Imagick();
          $imagick->readImage($image);
          $imagick->writeImages($image.'.jpg', false);
          $image = $image.'.jpg';
        }

        $img = new \Systems\Lib\Image();
        $id = convertNorawat($_POST['no_rawat']);
        if ($img->load($image)) {
          $imgName = time() . $cntr++;
          $imgPath = $dir . '/' . $id . '_' . $imgName . '.' . $img->getInfos('type');
          $lokasi_file = 'pages/upload/' . $id . '_' . $imgName . '.' . $img->getInfos('type');
          $img->save($imgPath);
          $query = $this->core->mysql('berkas_digital_perawatan')->save(['no_rawat' => $_POST['no_rawat'], 'kode' => $_POST['kode'], 'lokasi_file' => $lokasi_file]);
          if ($query) {
            $this->notify('success', 'Simpan berkas digital perawatan sukses.');
          }
        }
      }
    }

    //DELETE BERKAS DIGITAL PERAWATAN
    if (isset($_POST['deleteberkas'])) {
      if ($berkasPerawatan = $this->core->mysql('berkas_digital_perawatan')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('lokasi_file', $_POST['lokasi_file'])
        ->oneArray()
      ) {

        $lokasi_file = $berkasPerawatan['lokasi_file'];
        $no_rawat_file = $berkasPerawatan['no_rawat'];

        chdir('../../'); //directory di mlite/admin/, harus dirubah terlebih dahulu ke /www
        $fileLoc = getcwd() . '/webapps/berkasrawat/' . $lokasi_file;
        if (file_exists($fileLoc)) {
          unlink($fileLoc);
          $query = $this->core->mysql('berkas_digital_perawatan')->where('no_rawat', $no_rawat_file)->where('lokasi_file', $lokasi_file)->delete();

          if ($query) {
            $this->notify('success', 'Hapus berkas sukses');
          } else {
            $this->notify('failure', 'Hapus berkas gagal');
          }
        } else {
          $this->notify('failure', 'Hapus berkas gagal, File tidak ada');
        }
        chdir('mlite/admin/'); //mengembalikan directory ke mlite/admin/
      }
    }

    $this->_addHeaderFiles();
    $start_date = date('Y-m-d');
    if (isset($_GET['start_date']) && $_GET['start_date'] != '')
      $start_date = $_GET['start_date'];
    $end_date = date('Y-m-d');
    if (isset($_GET['end_date']) && $_GET['end_date'] != '')
      $end_date = $_GET['end_date'];
    $perpage = '10';
    $phrase = '';
    if (isset($_GET['s']))
      $phrase = $_GET['s'];

    $carabayar = str_replace(",","','", $this->settings->get('vedika.carabayar'));

    // pagination
    $totalRecords = $this->core->mysql()->pdo()->prepare("SELECT reg_periksa.no_rawat FROM reg_periksa, pasien, penjab WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.kd_pj = penjab.kd_pj AND penjab.kd_pj IN ('$carabayar') AND (reg_periksa.no_rkm_medis LIKE ? OR reg_periksa.no_rawat LIKE ? OR pasien.nm_pasien LIKE ?) AND reg_periksa.tgl_registrasi BETWEEN '$start_date' AND '$end_date' AND reg_periksa.status_lanjut = 'Ralan'");
    $totalRecords->execute(['%' . $phrase . '%', '%' . $phrase . '%', '%' . $phrase . '%']);
    $totalRecords = $totalRecords->fetchAll();

    $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url([ADMIN, 'vedika', 'index', $type, '%d?s=' . $phrase . '&start_date=' . $start_date . '&end_date=' . $end_date]));
    $this->assign['pagination'] = $pagination->nav('pagination', '5');
    $this->assign['totalRecords'] = $totalRecords;

    $offset = $pagination->offset();
    $query = $this->core->mysql()->pdo()->prepare("SELECT reg_periksa.*, pasien.*, dokter.nm_dokter, poliklinik.nm_poli, penjab.png_jawab FROM reg_periksa, pasien, dokter, poliklinik, penjab WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.kd_dokter = dokter.kd_dokter AND reg_periksa.kd_poli = poliklinik.kd_poli AND reg_periksa.kd_pj = penjab.kd_pj AND penjab.kd_pj IN ('$carabayar') AND (reg_periksa.no_rkm_medis LIKE ? OR reg_periksa.no_rawat LIKE ? OR pasien.nm_pasien LIKE ?) AND reg_periksa.tgl_registrasi BETWEEN '$start_date' AND '$end_date' AND reg_periksa.status_lanjut = 'Ralan' LIMIT $perpage OFFSET $offset");
    $query->execute(['%' . $phrase . '%', '%' . $phrase . '%', '%' . $phrase . '%']);
    $rows = $query->fetchAll();

    if (isset($_GET['debug']) && $_GET['debug'] == 'yes') {
      $totalRecords = $this->core->mysql()->pdo()->prepare("SELECT reg_periksa.no_rawat FROM reg_periksa, pasien, penjab WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.kd_pj = penjab.kd_pj AND penjab.kd_pj IN ('$carabayar') AND (reg_periksa.no_rkm_medis LIKE ? OR reg_periksa.no_rawat LIKE ? OR pasien.nm_pasien LIKE ?) AND reg_periksa.tgl_registrasi BETWEEN '$start_date' AND '$end_date' AND reg_periksa.status_lanjut = 'Ralan'");
      $totalRecords->execute(['%' . $phrase . '%', '%' . $phrase . '%', '%' . $phrase . '%']);
      $totalRecords = $totalRecords->fetchAll();

      $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url([ADMIN, 'vedika', 'index', $type, '%d?s=' . $phrase . '&start_date=' . $start_date . '&end_date=' . $end_date]));
      $this->assign['pagination'] = $pagination->nav('pagination', '5');
      $this->assign['totalRecords'] = $totalRecords;

      $offset = $pagination->offset();
      $query = $this->core->mysql()->pdo()->prepare("SELECT reg_periksa.*, pasien.*, dokter.nm_dokter, poliklinik.nm_poli, penjab.png_jawab FROM reg_periksa, pasien, dokter, poliklinik, penjab WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.kd_dokter = dokter.kd_dokter AND reg_periksa.kd_poli = poliklinik.kd_poli AND reg_periksa.kd_pj = penjab.kd_pj AND penjab.kd_pj IN ('$carabayar') AND (reg_periksa.no_rkm_medis LIKE ? OR reg_periksa.no_rawat LIKE ? OR pasien.nm_pasien LIKE ?) AND reg_periksa.tgl_registrasi BETWEEN '$start_date' AND '$end_date' AND reg_periksa.status_lanjut = 'Ralan' LIMIT $perpage OFFSET $offset");
      $query->execute(['%' . $phrase . '%', '%' . $phrase . '%', '%' . $phrase . '%']);
      $rows = $query->fetchAll();
    }

    if ($type == 'ranap') {
      // pagination
      $totalRecords = $this->core->mysql()->pdo()->prepare("SELECT reg_periksa.no_rawat FROM reg_periksa, pasien, penjab, kamar_inap WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.no_rawat = kamar_inap.no_rawat AND reg_periksa.kd_pj = penjab.kd_pj AND penjab.kd_pj IN ('$carabayar') AND (reg_periksa.no_rkm_medis LIKE ? OR reg_periksa.no_rawat LIKE ? OR pasien.nm_pasien LIKE ?) AND kamar_inap.tgl_keluar BETWEEN '$start_date' AND '$end_date' AND reg_periksa.status_lanjut = 'Ranap'");
      $totalRecords->execute(['%' . $phrase . '%', '%' . $phrase . '%', '%' . $phrase . '%']);
      $totalRecords = $totalRecords->fetchAll();

      $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url([ADMIN, 'vedika', 'index', $type, '%d?s=' . $phrase . '&start_date=' . $start_date . '&end_date=' . $end_date]));
      $this->assign['pagination'] = $pagination->nav('pagination', '5');
      $this->assign['totalRecords'] = $totalRecords;

      $offset = $pagination->offset();
      $query = $this->core->mysql()->pdo()->prepare("SELECT reg_periksa.*, pasien.*, dokter.nm_dokter, poliklinik.nm_poli, penjab.png_jawab, kamar_inap.tgl_keluar, kamar_inap.jam_keluar, kamar_inap.kd_kamar FROM reg_periksa, pasien, dokter, poliklinik, penjab, kamar_inap WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.no_rawat = kamar_inap.no_rawat AND reg_periksa.kd_dokter = dokter.kd_dokter AND reg_periksa.kd_poli = poliklinik.kd_poli AND reg_periksa.kd_pj = penjab.kd_pj AND penjab.kd_pj IN ('$carabayar') AND (reg_periksa.no_rkm_medis LIKE ? OR reg_periksa.no_rawat LIKE ? OR pasien.nm_pasien LIKE ?) AND kamar_inap.tgl_keluar BETWEEN '$start_date' AND '$end_date' AND reg_periksa.status_lanjut = 'Ranap' LIMIT $perpage OFFSET $offset");
      $query->execute(['%' . $phrase . '%', '%' . $phrase . '%', '%' . $phrase . '%']);
      $rows = $query->fetchAll();
    }
    $this->assign['list'] = [];
    if (count($rows)) {
      foreach ($rows as $row) {
        $berkas_digital = $this->core->mysql('berkas_digital_perawatan')
          ->join('master_berkas_digital', 'master_berkas_digital.kode=berkas_digital_perawatan.kode')
          ->where('berkas_digital_perawatan.no_rawat', $row['no_rawat'])
          ->asc('master_berkas_digital.nama')
          ->toArray();

        $row = htmlspecialchars_array($row);
        $row['no_sep'] = $this->_getSEPInfo('no_sep', $row['no_rawat']);
        $row['no_peserta'] = $this->_getSEPInfo('no_kartu', $row['no_rawat']);
        $row['no_rujukan'] = $this->_getSEPInfo('no_rujukan', $row['no_rawat']);
        $row['kd_penyakit'] = $this->_getDiagnosa('kd_penyakit', $row['no_rawat'], $row['status_lanjut']);
        $row['nm_penyakit'] = $this->_getDiagnosa('nm_penyakit', $row['no_rawat'], $row['status_lanjut']);
        $row['kode'] = $this->_getProsedur('kode', $row['no_rawat'], $row['status_lanjut']);
        $row['deskripsi_panjang'] = $this->_getProsedur('deskripsi_panjang', $row['no_rawat'], $row['status_lanjut']);
        $row['berkas_digital'] = $berkas_digital;
        $row['formSepURL'] = url([ADMIN, 'vedika', 'formsepvclaim', '?no_rawat=' . $row['no_rawat']]);
        $row['pdfURL'] = url([ADMIN, 'vedika', 'pdf', $this->convertNorawat($row['no_rawat'])]);
        $row['setstatusURL']  = url([ADMIN, 'vedika', 'setstatus', $this->_getSEPInfo('no_sep', $row['no_rawat'])]);
        $row['status_pengajuan'] = $this->core->mysql('mlite_vedika')->where('nosep', $this->_getSEPInfo('no_sep', $row['no_rawat']))->desc('id')->limit(1)->toArray();
        $row['berkasPasien'] = url([ADMIN, 'vedika', 'berkaspasien', $this->getRegPeriksaInfo('no_rkm_medis', $row['no_rawat'])]);
        $row['berkasPerawatan'] = url([ADMIN, 'vedika', 'berkasperawatan', $this->convertNorawat($row['no_rawat'])]);
        if ($type == 'ranap') {
          $_get_kamar_inap = $this->core->mysql('kamar_inap')->where('no_rawat', $row['no_rawat'])->limit(1)->desc('tgl_keluar')->toArray();
          $row['tgl_registrasi'] = $_get_kamar_inap[0]['tgl_keluar'];
          $row['jam_reg'] = $_get_kamar_inap[0]['jam_keluar'];
          $get_kamar = $this->core->mysql('kamar')->where('kd_kamar', $_get_kamar_inap[0]['kd_kamar'])->oneArray();
          $get_bangsal = $this->core->mysql('bangsal')->where('kd_bangsal', $get_kamar['kd_bangsal'])->oneArray();
          $row['nm_poli'] = $get_bangsal['nm_bangsal'].'/'.$get_kamar['kd_kamar'];
          $row['nm_dokter'] = $this->core->mysql('dpjp_ranap')
            ->join('dokter', 'dokter.kd_dokter=dpjp_ranap.kd_dokter')
            ->where('no_rawat', $row['no_rawat'])
            ->toArray();
        }
        $this->assign['list'][] = $row;
      }
    }

    $this->core->addCSS(url('assets/jscripts/lightbox/lightbox.min.css'));
    $this->core->addJS(url('assets/jscripts/lightbox/lightbox.min.js'));

    $this->assign['searchUrl'] =  url([ADMIN, 'vedika', 'index', $type, $page . '?s=' . $phrase . '&start_date=' . $start_date . '&end_date=' . $end_date]);
    $this->assign['ralanUrl'] =  url([ADMIN, 'vedika', 'index', 'ralan', $page . '?s=' . $phrase . '&start_date=' . $start_date . '&end_date=' . $end_date]);
    $this->assign['ranapUrl'] =  url([ADMIN, 'vedika', 'index', 'ranap', $page . '?s=' . $phrase . '&start_date=' . $start_date . '&end_date=' . $end_date]);
    return $this->draw('index.html', ['tab' => $type, 'vedika' => $this->assign]);
  }

  public function anyLengkap($type = 'ralan', $page = 1)
  {
    if (isset($_POST['submit'])) {
      if (!$this->core->mysql('mlite_vedika')->where('nosep', $_POST['nosep'])->oneArray()) {
        $simpan_status = $this->core->mysql('mlite_vedika')->save([
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
        $simpan_status = $this->core->mysql('mlite_vedika')
          ->where('nosep', $_POST['nosep'])
          ->save([
            'tanggal' => date('Y-m-d'),
            'status' => $_POST['status']
          ]);
      }
      if ($simpan_status) {
        $this->core->mysql('mlite_vedika_feedback')->save([
          'id' => NULL,
          'nosep' => $_POST['nosep'],
          'tanggal' => date('Y-m-d'),
          'catatan' => $_POST['status'].' - '.$_POST['catatan'],
          'username' => $this->core->getUserInfo('username', null, true)
        ]);
      }
    }

    if (isset($_POST['simpanberkas'])) {
      if(MULTI_APP) {

        $curl = curl_init();
        $filePath = $_FILES['files']['tmp_name'];
        $file_type = $_FILES['files']['type'];
        if($file_type=='application/pdf'){
          $imagick = new \Imagick();
          $imagick->readImage($image);
          $imagick->writeImages($image.'.jpg', false);
          $filePath = $image.'.jpg';
        }

        curl_setopt_array($curl, array(
          CURLOPT_URL => str_replace('webapps','',WEBAPPS_URL).'api/berkasdigital',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => array('file'=> new \CURLFILE($filePath),'token' => $this->settings->get('api.berkasdigital_key'), 'no_rawat' => $_POST['no_rawat'], 'kode' => $_POST['kode']),
          CURLOPT_HTTPHEADER => array(),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        //echo $response;
        if($response == 'Success') {
          $this->notify('success', 'Sukses menambahkan gambar');
        } else {
          $this->notify('failure', 'Gagal menambahkan gambar');
        }

      } else {
        $dir    = $this->_uploads;
        $cntr   = 0;

        $image = $_FILES['files']['tmp_name'];

        $file_type = $_FILES['files']['type'];
        if($file_type=='application/pdf'){
          $imagick = new \Imagick();
          $imagick->readImage($image);
          $imagick->writeImages($image.'.jpg', false);
          $image = $image.'.jpg';
        }

        $img = new \Systems\Lib\Image();
        $id = convertNorawat($_POST['no_rawat']);
        if ($img->load($image)) {
          $imgName = time() . $cntr++;
          $imgPath = $dir . '/' . $id . '_' . $imgName . '.' . $img->getInfos('type');
          $lokasi_file = 'pages/upload/' . $id . '_' . $imgName . '.' . $img->getInfos('type');
          $img->save($imgPath);
          $query = $this->core->mysql('berkas_digital_perawatan')->save(['no_rawat' => $_POST['no_rawat'], 'kode' => $_POST['kode'], 'lokasi_file' => $lokasi_file]);
          if ($query) {
            $this->notify('success', 'Simpan berkar digital perawatan sukses.');
          }
        }
      }
    }

    //DELETE BERKAS DIGITAL PERAWATAN
    if (isset($_POST['deleteberkas'])) {
      if ($berkasPerawatan = $this->core->mysql('berkas_digital_perawatan')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('lokasi_file', $_POST['lokasi_file'])
        ->oneArray()
      ) {

        $lokasi_file = $berkasPerawatan['lokasi_file'];
        $no_rawat_file = $berkasPerawatan['no_rawat'];

        chdir('../../'); //directory di mlite/admin/, harus dirubah terlebih dahulu ke /www
        $fileLoc = getcwd() . '/webapps/berkasrawat/' . $lokasi_file;
        if (file_exists($fileLoc)) {
          unlink($fileLoc);
          $query = $this->core->mysql('berkas_digital_perawatan')->where('no_rawat', $no_rawat_file)->where('lokasi_file', $lokasi_file)->delete();

          if ($query) {
            $this->notify('success', 'Hapus berkas sukses');
          } else {
            $this->notify('failure', 'Hapus berkas gagal');
          }
        } else {
          $this->notify('failure', 'Hapus berkas gagal, File tidak ada');
        }
        chdir('mlite/admin/'); //mengembalikan directory ke mlite/admin/
      }
    }

    $this->_addHeaderFiles();
    $start_date = date('Y-m-d');
    if (isset($_GET['start_date']) && $_GET['start_date'] != '')
      $start_date = $_GET['start_date'];
    $end_date = date('Y-m-d');
    if (isset($_GET['end_date']) && $_GET['end_date'] != '')
      $end_date = $_GET['end_date'];
    $perpage = '10';
    $phrase = '';
    if (isset($_GET['s']))
      $phrase = $_GET['s'];

    // pagination
    $totalRecords = $this->core->mysql()->pdo()->prepare("SELECT no_rawat FROM mlite_vedika WHERE status = 'Lengkap' AND jenis = '2' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND tgl_registrasi BETWEEN '$start_date' AND '$end_date'");
    $totalRecords->execute(['%' . $phrase . '%', '%' . $phrase . '%', '%' . $phrase . '%']);
    $totalRecords = $totalRecords->fetchAll();

    $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url([ADMIN, 'vedika', 'lengkap', $type, '%d?s=' . $phrase . '&start_date=' . $start_date . '&end_date=' . $end_date]));
    $this->assign['pagination'] = $pagination->nav('pagination', '5');
    $this->assign['totalRecords'] = $totalRecords;

    $offset = $pagination->offset();
    $query = $this->core->mysql()->pdo()->prepare("SELECT * FROM mlite_vedika WHERE status = 'Lengkap' AND jenis = '2' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND tgl_registrasi BETWEEN '$start_date' AND '$end_date' ORDER BY nosep ASC LIMIT $perpage OFFSET $offset");
    $query->execute(['%' . $phrase . '%', '%' . $phrase . '%', '%' . $phrase . '%']);
    $rows = $query->fetchAll();

    if ($type == 'ranap') {
      // pagination
      $totalRecords = $this->core->mysql()->pdo()->prepare("SELECT no_rawat FROM mlite_vedika WHERE status = 'Lengkap' AND jenis = '1' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND no_rawat IN (SELECT no_rawat FROM kamar_inap WHERE tgl_keluar BETWEEN '$start_date' AND '$end_date')");
      $totalRecords->execute(['%' . $phrase . '%', '%' . $phrase . '%', '%' . $phrase . '%']);
      $totalRecords = $totalRecords->fetchAll();

      $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url([ADMIN, 'vedika', 'lengkap', $type, '%d?s=' . $phrase . '&start_date=' . $start_date . '&end_date=' . $end_date]));
      $this->assign['pagination'] = $pagination->nav('pagination', '5');
      $this->assign['totalRecords'] = $totalRecords;

      $offset = $pagination->offset();
      $query = $this->core->mysql()->pdo()->prepare("SELECT * FROM mlite_vedika WHERE status = 'Lengkap' AND jenis = '1' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND no_rawat IN (SELECT no_rawat FROM kamar_inap WHERE tgl_keluar BETWEEN '$start_date' AND '$end_date') order by mlite_vedika.nosep LIMIT $perpage OFFSET $offset");
      $query->execute(['%' . $phrase . '%', '%' . $phrase . '%', '%' . $phrase . '%']);
      $rows = $query->fetchAll();
    }
    $this->assign['list'] = [];
    if (count($rows)) {
      foreach ($rows as $row) {
        $berkas_digital = $this->core->mysql('berkas_digital_perawatan')
          ->join('master_berkas_digital', 'master_berkas_digital.kode=berkas_digital_perawatan.kode')
          ->where('berkas_digital_perawatan.no_rawat', $row['no_rawat'])
          ->asc('master_berkas_digital.nama')
          ->toArray();

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
        $row['kode'] = $this->_getProsedur('kode', $row['no_rawat'], $row['status_lanjut']);
        $row['deskripsi_panjang'] = $this->_getProsedur('deskripsi_panjang', $row['no_rawat'], $row['status_lanjut']);
        $row['berkas_digital'] = $berkas_digital;
        $row['formSepURL'] = url([ADMIN, 'vedika', 'formsepvclaim', '?no_rawat=' . $row['no_rawat']]);
        $row['pdfURL'] = url([ADMIN, 'vedika', 'pdf', $this->convertNorawat($row['no_rawat'])]);
        $row['setstatusURL']  = url([ADMIN, 'vedika', 'setstatus', $this->_getSEPInfo('no_sep', $row['no_rawat'])]);
        $row['status_lengkap'] = $this->core->mysql('mlite_vedika')->where('nosep', $this->_getSEPInfo('no_sep', $row['no_rawat']))->desc('id')->limit(1)->toArray();
        $row['berkasPasien'] = url([ADMIN, 'vedika', 'berkaspasien', $this->getRegPeriksaInfo('no_rkm_medis', $row['no_rawat'])]);
        $row['berkasPerawatan'] = url([ADMIN, 'vedika', 'berkasperawatan', $this->convertNorawat($row['no_rawat'])]);
        $row['pegawai'] = $this->core->mysql('mlite_vedika_feedback')->join('pegawai','pegawai.nik=mlite_vedika_feedback.username')->where('nosep', $this->_getSEPInfo('no_sep', $row['no_rawat']))->desc('mlite_vedika_feedback.id')->limit(1)->toArray();
        //$row['pegawai'] = $this->core->getPegawaiInfo('nama', $row['username']);
        if ($type == 'ranap') {
          $_get_kamar_inap = $this->core->mysql('kamar_inap')->where('no_rawat', $row['no_rawat'])->limit(1)->desc('tgl_keluar')->toArray();
          $row['tgl_registrasi'] = $_get_kamar_inap[0]['tgl_keluar'];
          $row['jam_reg'] = $_get_kamar_inap[0]['jam_keluar'];
          $get_kamar = $this->core->mysql('kamar')->where('kd_kamar', $_get_kamar_inap[0]['kd_kamar'])->oneArray();
          $get_bangsal = $this->core->mysql('bangsal')->where('kd_bangsal', $get_kamar['kd_bangsal'])->oneArray();
          $row['nm_poli'] = $get_bangsal['nm_bangsal'].'/'.$get_kamar['kd_kamar'];
          $row['nm_dokter'] = $this->core->mysql('dpjp_ranap')
            ->join('dokter', 'dokter.kd_dokter=dpjp_ranap.kd_dokter')
            ->where('no_rawat', $row['no_rawat'])
            ->toArray();
        }
        $this->assign['list'][] = $row;
      }
    }

    $this->core->addCSS(url('assets/jscripts/lightbox/lightbox.min.css'));
    $this->core->addJS(url('assets/jscripts/lightbox/lightbox.min.js'));

    $this->assign['searchUrl'] =  url([ADMIN, 'vedika', 'lengkap', $type, $page . '?s=' . $phrase . '&start_date=' . $start_date . '&end_date=' . $end_date]);
    $this->assign['ralanUrl'] =  url([ADMIN, 'vedika', 'lengkap', 'ralan', $page . '?s=' . $phrase . '&start_date=' . $start_date . '&end_date=' . $end_date]);
    $this->assign['ranapUrl'] =  url([ADMIN, 'vedika', 'lengkap', 'ranap', $page . '?s=' . $phrase . '&start_date=' . $start_date . '&end_date=' . $end_date]);
    return $this->draw('lengkap.html', ['tab' => $type, 'vedika' => $this->assign]);
  }

  public function anyPengajuan($type = 'ralan', $page = 1)
  {
    if (isset($_POST['submit'])) {
      if (!$this->core->mysql('mlite_vedika')->where('nosep', $_POST['nosep'])->oneArray()) {
        $simpan_status = $this->core->mysql('mlite_vedika')->save([
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
        $simpan_status = $this->core->mysql('mlite_vedika')
          ->where('nosep', $_POST['nosep'])
          ->save([
            'tanggal' => date('Y-m-d'),
            'status' => $_POST['status']
          ]);
      }
      if ($simpan_status) {
        $this->core->mysql('mlite_vedika_feedback')->save([
          'id' => NULL,
          'nosep' => $_POST['nosep'],
          'tanggal' => date('Y-m-d'),
          'catatan' => $_POST['status'].' - '.$_POST['catatan'],
          'username' => $this->core->getUserInfo('username', null, true)
        ]);
      }
    }

    if (isset($_POST['simpanberkas'])) {
      if(MULTI_APP) {

        $curl = curl_init();
        $filePath = $_FILES['files']['tmp_name'];
        $file_type = $_FILES['files']['type'];
        if($file_type=='application/pdf'){
          $imagick = new \Imagick();
          $imagick->readImage($image);
          $imagick->writeImages($image.'.jpg', false);
          $filePath = $image.'.jpg';
        }

        curl_setopt_array($curl, array(
          CURLOPT_URL => str_replace('webapps','',WEBAPPS_URL).'api/berkasdigital',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => array('file'=> new \CURLFILE($filePath),'token' => $this->settings->get('api.berkasdigital_key'), 'no_rawat' => $_POST['no_rawat'], 'kode' => $_POST['kode']),
          CURLOPT_HTTPHEADER => array(),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        //echo $response;
        if($response == 'Success') {
          $this->notify('success', 'Sukses menambahkan gambar');
        } else {
          $this->notify('failure', 'Gagal menambahkan gambar');
        }

      } else {
        $dir    = $this->_uploads;
        $cntr   = 0;

        $image = $_FILES['files']['tmp_name'];

        $file_type = $_FILES['files']['type'];
        if($file_type=='application/pdf'){
          $imagick = new \Imagick();
          $imagick->readImage($image);
          $imagick->writeImages($image.'.jpg', false);
          $image = $image.'.jpg';
        }

        $img = new \Systems\Lib\Image();
        $id = convertNorawat($_POST['no_rawat']);
        if ($img->load($image)) {
          $imgName = time() . $cntr++;
          $imgPath = $dir . '/' . $id . '_' . $imgName . '.' . $img->getInfos('type');
          $lokasi_file = 'pages/upload/' . $id . '_' . $imgName . '.' . $img->getInfos('type');
          $img->save($imgPath);
          $query = $this->core->mysql('berkas_digital_perawatan')->save(['no_rawat' => $_POST['no_rawat'], 'kode' => $_POST['kode'], 'lokasi_file' => $lokasi_file]);
          if ($query) {
            $this->notify('success', 'Simpan berkar digital perawatan sukses.');
          }
        }
      }
    }

    //DELETE BERKAS DIGITAL PERAWATAN
    if (isset($_POST['deleteberkas'])) {
      if ($berkasPerawatan = $this->core->mysql('berkas_digital_perawatan')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('lokasi_file', $_POST['lokasi_file'])
        ->oneArray()
      ) {

        $lokasi_file = $berkasPerawatan['lokasi_file'];
        $no_rawat_file = $berkasPerawatan['no_rawat'];

        chdir('../../'); //directory di mlite/admin/, harus dirubah terlebih dahulu ke /www
        $fileLoc = getcwd() . '/webapps/berkasrawat/' . $lokasi_file;
        if (file_exists($fileLoc)) {
          unlink($fileLoc);
          $query = $this->core->mysql('berkas_digital_perawatan')->where('no_rawat', $no_rawat_file)->where('lokasi_file', $lokasi_file)->delete();

          if ($query) {
            $this->notify('success', 'Hapus berkas sukses');
          } else {
            $this->notify('failure', 'Hapus berkas gagal');
          }
        } else {
          $this->notify('failure', 'Hapus berkas gagal, File tidak ada');
        }
        chdir('mlite/admin/'); //mengembalikan directory ke mlite/admin/
      }
    }

    $this->_addHeaderFiles();
    $start_date = date('Y-m-d');
    if (isset($_GET['start_date']) && $_GET['start_date'] != '')
      $start_date = $_GET['start_date'];
    $end_date = date('Y-m-d');
    if (isset($_GET['end_date']) && $_GET['end_date'] != '')
      $end_date = $_GET['end_date'];
    $perpage = '10';
    $phrase = '';
    if (isset($_GET['s']))
      $phrase = $_GET['s'];

    // pagination
    $totalRecords = $this->core->mysql()->pdo()->prepare("SELECT no_rawat FROM mlite_vedika WHERE status = 'Pengajuan' AND jenis = '2' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND tgl_registrasi BETWEEN '$start_date' AND '$end_date'");
    $totalRecords->execute(['%' . $phrase . '%', '%' . $phrase . '%', '%' . $phrase . '%']);
    $totalRecords = $totalRecords->fetchAll();

    $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url([ADMIN, 'vedika', 'pengajuan', $type, '%d?s=' . $phrase . '&start_date=' . $start_date . '&end_date=' . $end_date]));
    $this->assign['pagination'] = $pagination->nav('pagination', '5');
    $this->assign['totalRecords'] = $totalRecords;

    $offset = $pagination->offset();
    $query = $this->core->mysql()->pdo()->prepare("SELECT * FROM mlite_vedika WHERE status = 'Pengajuan' AND jenis = '2' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND tgl_registrasi BETWEEN '$start_date' AND '$end_date' ORDER BY nosep LIMIT $perpage OFFSET $offset");
    $query->execute(['%' . $phrase . '%', '%' . $phrase . '%', '%' . $phrase . '%']);
    $rows = $query->fetchAll();

    if ($type == 'ranap') {
      // pagination
      $totalRecords = $this->core->mysql()->pdo()->prepare("SELECT no_rawat FROM mlite_vedika WHERE status = 'Pengajuan' AND jenis = '1' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND no_rawat IN (SELECT no_rawat FROM kamar_inap WHERE tgl_keluar BETWEEN '$start_date' AND '$end_date')");
      $totalRecords->execute(['%' . $phrase . '%', '%' . $phrase . '%', '%' . $phrase . '%']);
      $totalRecords = $totalRecords->fetchAll();

      $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url([ADMIN, 'vedika', 'pengajuan', $type, '%d?s=' . $phrase . '&start_date=' . $start_date . '&end_date=' . $end_date]));
      $this->assign['pagination'] = $pagination->nav('pagination', '5');
      $this->assign['totalRecords'] = $totalRecords;

      $offset = $pagination->offset();
      $query = $this->core->mysql()->pdo()->prepare("SELECT * FROM mlite_vedika WHERE status = 'Pengajuan' AND jenis = '1' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND no_rawat IN (SELECT no_rawat FROM kamar_inap WHERE tgl_keluar BETWEEN '$start_date' AND '$end_date') order by mlite_vedika.nosep LIMIT $perpage OFFSET $offset");
      $query->execute(['%' . $phrase . '%', '%' . $phrase . '%', '%' . $phrase . '%']);
      $rows = $query->fetchAll();
    }
    $this->assign['list'] = [];
    if (count($rows)) {
      foreach ($rows as $row) {
        $berkas_digital = $this->core->mysql('berkas_digital_perawatan')
          ->join('master_berkas_digital', 'master_berkas_digital.kode=berkas_digital_perawatan.kode')
          ->where('berkas_digital_perawatan.no_rawat', $row['no_rawat'])
          ->asc('master_berkas_digital.nama')
          ->toArray();
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
        $row['kode'] = $this->_getProsedur('kode', $row['no_rawat'], $row['status_lanjut']);
        $row['deskripsi_panjang'] = $this->_getProsedur('deskripsi_panjang', $row['no_rawat'], $row['status_lanjut']);
        $row['berkas_digital'] = $berkas_digital;
        $row['formSepURL'] = url([ADMIN, 'vedika', 'formsepvclaim', '?no_rawat=' . $row['no_rawat']]);
        $row['pdfURL'] = url([ADMIN, 'vedika', 'pdf', $this->convertNorawat($row['no_rawat'])]);
        $row['setstatusURL']  = url([ADMIN, 'vedika', 'setstatus', $this->_getSEPInfo('no_sep', $row['no_rawat'])]);
        $row['status_pengajuan'] = $this->core->mysql('mlite_vedika')->where('nosep', $this->_getSEPInfo('no_sep', $row['no_rawat']))->desc('id')->limit(1)->toArray();
        $row['berkasPasien'] = url([ADMIN, 'vedika', 'berkaspasien', $this->getRegPeriksaInfo('no_rkm_medis', $row['no_rawat'])]);
        $row['berkasPerawatan'] = url([ADMIN, 'vedika', 'berkasperawatan', $this->convertNorawat($row['no_rawat'])]);
        $row['pegawai'] = $this->core->mysql('mlite_vedika_feedback')->join('pegawai','pegawai.nik=mlite_vedika_feedback.username')->where('nosep', $this->_getSEPInfo('no_sep', $row['no_rawat']))->desc('mlite_vedika_feedback.id')->limit(1)->toArray();
        //$row['pegawai'] = $this->core->getPegawaiInfo('nama', $row['username']);
        if ($type == 'ranap') {
          $_get_kamar_inap = $this->core->mysql('kamar_inap')->where('no_rawat', $row['no_rawat'])->limit(1)->desc('tgl_keluar')->toArray();
          $row['tgl_registrasi'] = $_get_kamar_inap[0]['tgl_keluar'];
          $row['jam_reg'] = $_get_kamar_inap[0]['jam_keluar'];
          $get_kamar = $this->core->mysql('kamar')->where('kd_kamar', $_get_kamar_inap[0]['kd_kamar'])->oneArray();
          $get_bangsal = $this->core->mysql('bangsal')->where('kd_bangsal', $get_kamar['kd_bangsal'])->oneArray();
          $row['nm_poli'] = $get_bangsal['nm_bangsal'].'/'.$get_kamar['kd_kamar'];
          $row['nm_dokter'] = $this->core->mysql('dpjp_ranap')
            ->join('dokter', 'dokter.kd_dokter=dpjp_ranap.kd_dokter')
            ->where('no_rawat', $row['no_rawat'])
            ->toArray();
        }
        $this->assign['list'][] = $row;
      }
    }

    $this->core->addCSS(url('assets/jscripts/lightbox/lightbox.min.css'));
    $this->core->addJS(url('assets/jscripts/lightbox/lightbox.min.js'));

    $this->assign['searchUrl'] =  url([ADMIN, 'vedika', 'pengajuan', $type, $page . '?s=' . $phrase . '&start_date=' . $start_date . '&end_date=' . $end_date]);
    $this->assign['ralanUrl'] =  url([ADMIN, 'vedika', 'pengajuan', 'ralan', $page . '?s=' . $phrase . '&start_date=' . $start_date . '&end_date=' . $end_date]);
    $this->assign['ranapUrl'] =  url([ADMIN, 'vedika', 'pengajuan', 'ranap', $page . '?s=' . $phrase . '&start_date=' . $start_date . '&end_date=' . $end_date]);
    return $this->draw('pengajuan.html', ['tab' => $type, 'vedika' => $this->assign]);
  }

  public function getLengkapExcel()
  {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
    $rows = $this->core->mysql('mlite_vedika')->where('status', 'Lengkap')->where('tgl_registrasi','>=',$start_date)->where('tgl_registrasi','<=', $end_date)->toArray();
    if(isset($_GET['jenis']) && $_GET['jenis'] == 1) {
      $rows = $this->core->mysql('mlite_vedika')->where('status', 'Lengkap')->where('tgl_registrasi','>=',$start_date)->where('tgl_registrasi','<=', $end_date)->where('jenis', 1)->toArray();
    }
    if(isset($_GET['jenis']) && $_GET['jenis'] == 2) {
      $rows = $this->core->mysql('mlite_vedika')->where('status', 'Lengkap')->where('tgl_registrasi','>=',$start_date)->where('tgl_registrasi','<=', $end_date)->where('jenis', 2)->toArray();
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
      $get_feedback_bpjs = $this->core->mysql('mlite_vedika_feedback')->where('nosep', $row['nosep'])->where('username', 'bpjs')->oneArray();
      $row['konfirmasi_bpjs'] = $get_feedback_bpjs['catatan'];
      $get_feedback_rs = $this->core->mysql('mlite_vedika_feedback')->where('nosep', $row['nosep'])->where('username','!=','bpjs')->oneArray();
      $row['konfirmasi_rs'] = $get_feedback_rs['catatan'];
      $display[] = $row;
    }

    $this->tpl->set('display', $display);

    echo $this->tpl->draw(MODULES . '/vedika/view/admin/lengkap_excel.html', true);
    exit();
  }

  public function getPengajuanExcel()
  {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
    $rows = $this->core->mysql('mlite_vedika')->where('status', 'Pengajuan')->where('tgl_registrasi','>=',$start_date)->where('tgl_registrasi','<=', $end_date)->toArray();
    if(isset($_GET['jenis']) && $_GET['jenis'] == 1) {
      $rows = $this->core->mysql('mlite_vedika')->where('status', 'Pengajuan')->where('tgl_registrasi','>=',$start_date)->where('tgl_registrasi','<=', $end_date)->where('jenis', 1)->toArray();
    }
    if(isset($_GET['jenis']) && $_GET['jenis'] == 2) {
      $rows = $this->core->mysql('mlite_vedika')->where('status', 'Pengajuan')->where('tgl_registrasi','>=',$start_date)->where('tgl_registrasi','<=', $end_date)->where('jenis', 2)->toArray();
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
      $get_feedback_bpjs = $this->core->mysql('mlite_vedika_feedback')->where('nosep', $row['nosep'])->where('username', 'bpjs')->oneArray();
      $row['konfirmasi_bpjs'] = $get_feedback_bpjs['catatan'];
      $get_feedback_rs = $this->core->mysql('mlite_vedika_feedback')->where('nosep', $row['nosep'])->where('username','!=','bpjs')->oneArray();
      $row['konfirmasi_rs'] = $get_feedback_rs['catatan'];
      $display[] = $row;
    }

    $this->tpl->set('display', $display);

    echo $this->tpl->draw(MODULES . '/vedika/view/admin/pengajuan_excel.html', true);
    exit();
  }

  public function getPerbaikanExcel()
  {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
    $rows = $this->core->mysql('mlite_vedika')->where('status', 'Perbaikan')->where('tgl_registrasi','>=',$start_date)->where('tgl_registrasi','<=', $end_date)->toArray();
    if(isset($_GET['jenis']) && $_GET['jenis'] == 1) {
      $rows = $this->core->mysql('mlite_vedika')->where('status', 'Perbaikan')->where('tgl_registrasi','>=',$start_date)->where('tgl_registrasi','<=', $end_date)->where('jenis', 1)->toArray();
    }
    if(isset($_GET['jenis']) && $_GET['jenis'] == 2) {
      $rows = $this->core->mysql('mlite_vedika')->where('status', 'Perbaikan')->where('tgl_registrasi','>=',$start_date)->where('tgl_registrasi','<=', $end_date)->where('jenis', 2)->toArray();
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
      $get_feedback_bpjs = $this->core->mysql('mlite_vedika_feedback')->where('nosep', $row['nosep'])->where('username', 'bpjs')->oneArray();
      $row['konfirmasi_bpjs'] = $get_feedback_bpjs['catatan'];
      $get_feedback_rs = $this->core->mysql('mlite_vedika_feedback')->where('nosep', $row['nosep'])->where('username','!=','bpjs')->oneArray();
      $row['konfirmasi_rs'] = $get_feedback_rs['catatan'];
      $display[] = $row;
    }

    $this->tpl->set('display', $display);

    echo $this->tpl->draw(MODULES . '/vedika/view/admin/perbaikan_excel.html', true);
    exit();
  }

  public function anyPerbaikan($type = 'ralan', $page = 1)
  {
    if (isset($_POST['submit'])) {
      if (!$this->core->mysql('mlite_vedika')->where('nosep', $_POST['nosep'])->oneArray()) {
        $simpan_status = $this->core->mysql('mlite_vedika')->save([
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
        $simpan_status = $this->core->mysql('mlite_vedika')
          ->where('nosep', $_POST['nosep'])
          ->save([
            'tanggal' => date('Y-m-d'),
            'status' => $_POST['status']
          ]);
      }
      if ($simpan_status) {
        $this->core->mysql('mlite_vedika_feedback')->save([
          'id' => NULL,
          'nosep' => $_POST['nosep'],
          'tanggal' => date('Y-m-d'),
          'catatan' => $_POST['status'].' - '.$_POST['catatan'],
          'username' => $this->core->getUserInfo('username', null, true)
        ]);
      }
    }

    if (isset($_POST['simpanberkas'])) {
      if(MULTI_APP) {

        $curl = curl_init();
        $filePath = $_FILES['files']['tmp_name'];
        $file_type = $_FILES['files']['type'];
        if($file_type=='application/pdf'){
          $imagick = new \Imagick();
          $imagick->readImage($image);
          $imagick->writeImages($image.'.jpg', false);
          $filePath = $image.'.jpg';
        }

        curl_setopt_array($curl, array(
          CURLOPT_URL => str_replace('webapps','',WEBAPPS_URL).'api/berkasdigital',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => array('file'=> new \CURLFILE($filePath),'token' => $this->settings->get('api.berkasdigital_key'), 'no_rawat' => $_POST['no_rawat'], 'kode' => $_POST['kode']),
          CURLOPT_HTTPHEADER => array(),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        //echo $response;
        if($response == 'Success') {
          $this->notify('success', 'Sukses menambahkan gambar');
        } else {
          $this->notify('failure', 'Gagal menambahkan gambar');
        }

      } else {
        $dir    = $this->_uploads;
        $cntr   = 0;

        $image = $_FILES['files']['tmp_name'];

        $file_type = $_FILES['files']['type'];
        if($file_type=='application/pdf'){
          $imagick = new \Imagick();
          $imagick->readImage($image);
          $imagick->writeImages($image.'.jpg', false);
          $image = $image.'.jpg';
        }

        $img = new \Systems\Lib\Image();
        $id = convertNorawat($_POST['no_rawat']);
        if ($img->load($image)) {
          $imgName = time() . $cntr++;
          $imgPath = $dir . '/' . $id . '_' . $imgName . '.' . $img->getInfos('type');
          $lokasi_file = 'pages/upload/' . $id . '_' . $imgName . '.' . $img->getInfos('type');
          $img->save($imgPath);
          $query = $this->core->mysql('berkas_digital_perawatan')->save(['no_rawat' => $_POST['no_rawat'], 'kode' => $_POST['kode'], 'lokasi_file' => $lokasi_file]);
          if ($query) {
            $this->notify('success', 'Simpan berkar digital perawatan sukses.');
          }
        }
      }
    }

    //DELETE BERKAS DIGITAL PERAWATAN
    if (isset($_POST['deleteberkas'])) {
      if ($berkasPerawatan = $this->core->mysql('berkas_digital_perawatan')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('lokasi_file', $_POST['lokasi_file'])
        ->oneArray()
      ) {

        $lokasi_file = $berkasPerawatan['lokasi_file'];
        $no_rawat_file = $berkasPerawatan['no_rawat'];

        chdir('../../'); //directory di mlite/admin/, harus dirubah terlebih dahulu ke /www
        $fileLoc = getcwd() . '/webapps/berkasrawat/' . $lokasi_file;
        if (file_exists($fileLoc)) {
          unlink($fileLoc);
          $query = $this->core->mysql('berkas_digital_perawatan')->where('no_rawat', $no_rawat_file)->where('lokasi_file', $lokasi_file)->delete();

          if ($query) {
            $this->notify('success', 'Hapus berkas sukses');
          } else {
            $this->notify('failure', 'Hapus berkas gagal');
          }
        } else {
          $this->notify('failure', 'Hapus berkas gagal, File tidak ada');
        }
        chdir('mlite/admin/'); //mengembalikan directory ke mlite/admin/
      }
    }

    $this->_addHeaderFiles();
    $start_date = date('Y-m-d');
    if (isset($_GET['start_date']) && $_GET['start_date'] != '')
      $start_date = $_GET['start_date'];
    $end_date = date('Y-m-d');
    if (isset($_GET['end_date']) && $_GET['end_date'] != '')
      $end_date = $_GET['end_date'];
    $perpage = '10';
    $phrase = '';
    if (isset($_GET['s']))
      $phrase = $_GET['s'];

    // pagination
    $totalRecords = $this->core->mysql()->pdo()->prepare("SELECT no_rawat FROM mlite_vedika WHERE status = 'Perbaiki' AND jenis = '2' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND tgl_registrasi BETWEEN '$start_date' AND '$end_date'");
    $totalRecords->execute(['%' . $phrase . '%', '%' . $phrase . '%', '%' . $phrase . '%']);
    $totalRecords = $totalRecords->fetchAll();

    $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url([ADMIN, 'vedika', 'perbaikan', $type, '%d?s=' . $phrase . '&start_date=' . $start_date . '&end_date=' . $end_date]));
    $this->assign['pagination'] = $pagination->nav('pagination', '5');
    $this->assign['totalRecords'] = $totalRecords;

    $offset = $pagination->offset();
    $query = $this->core->mysql()->pdo()->prepare("SELECT * FROM mlite_vedika WHERE status = 'Perbaiki' AND jenis = '2' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND tgl_registrasi BETWEEN '$start_date' AND '$end_date' LIMIT $perpage OFFSET $offset");
    $query->execute(['%' . $phrase . '%', '%' . $phrase . '%', '%' . $phrase . '%']);
    $rows = $query->fetchAll();

    if ($type == 'ranap') {
      // pagination
      $totalRecords = $this->core->mysql()->pdo()->prepare("SELECT no_rawat FROM mlite_vedika WHERE status = 'Perbaiki' AND jenis = '1' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND tgl_registrasi BETWEEN '$start_date' AND '$end_date'");
      $totalRecords->execute(['%' . $phrase . '%', '%' . $phrase . '%', '%' . $phrase . '%']);
      $totalRecords = $totalRecords->fetchAll();

      $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url([ADMIN, 'vedika', 'index', $type, '%d?s=' . $phrase . '&start_date=' . $start_date . '&end_date=' . $end_date]));
      $this->assign['pagination'] = $pagination->nav('pagination', '5');
      $this->assign['totalRecords'] = $totalRecords;

      $offset = $pagination->offset();
      $query = $this->core->mysql()->pdo()->prepare("SELECT * FROM mlite_vedika WHERE status = 'Perbaiki' AND jenis = '1' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND tgl_registrasi BETWEEN '$start_date' AND '$end_date' LIMIT $perpage OFFSET $offset");
      $query->execute(['%' . $phrase . '%', '%' . $phrase . '%', '%' . $phrase . '%']);
      $rows = $query->fetchAll();
    }
    $this->assign['list'] = [];
    if (count($rows)) {
      foreach ($rows as $row) {
        $berkas_digital = $this->core->mysql('berkas_digital_perawatan')
          ->join('master_berkas_digital', 'master_berkas_digital.kode=berkas_digital_perawatan.kode')
          ->where('berkas_digital_perawatan.no_rawat', $row['no_rawat'])
          ->asc('master_berkas_digital.nama')
          ->toArray();
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
        $row['kode'] = $this->_getProsedur('kode', $row['no_rawat'], $row['status_lanjut']);
        $row['deskripsi_panjang'] = $this->_getProsedur('deskripsi_panjang', $row['no_rawat'], $row['status_lanjut']);
        $row['berkas_digital'] = $berkas_digital;
        $row['formSepURL'] = url([ADMIN, 'vedika', 'formsepvclaim', '?no_rawat=' . $row['no_rawat']]);
        $row['pdfURL'] = url([ADMIN, 'vedika', 'pdf', $this->convertNorawat($row['no_rawat'])]);
        $row['setstatusURL']  = url([ADMIN, 'vedika', 'setstatus', $this->_getSEPInfo('no_sep', $row['no_rawat'])]);
        $row['status_pengajuan'] = $this->core->mysql('mlite_vedika')->where('nosep', $this->_getSEPInfo('no_sep', $row['no_rawat']))->desc('id')->limit(1)->toArray();
        $row['berkasPasien'] = url([ADMIN, 'vedika', 'berkaspasien', $this->getRegPeriksaInfo('no_rkm_medis', $row['no_rawat'])]);
        $row['berkasPerawatan'] = url([ADMIN, 'vedika', 'berkasperawatan', $this->convertNorawat($row['no_rawat'])]);
        if ($type == 'ranap') {
          $row['tgl_registrasi'] = $this->core->getKamarInapInfo('tgl_keluar', $row['no_rawat']);
          $row['jam_reg'] = $this->core->getKamarInapInfo('jam_keluar', $row['no_rawat']);
          $get_kamar = $this->core->mysql('kamar')->where('kd_kamar', $this->core->getKamarInapInfo('kd_kamar', $row['no_rawat']))->oneArray();
          $get_bangsal = $this->core->mysql('bangsal')->where('kd_bangsal', $get_kamar['kd_bangsal'])->oneArray();
          $row['nm_poli'] = $get_bangsal['nm_bangsal'].'/'.$get_kamar['kd_kamar'];
        }
        $this->assign['list'][] = $row;
      }
    }

    $this->core->addCSS(url('assets/jscripts/lightbox/lightbox.min.css'));
    $this->core->addJS(url('assets/jscripts/lightbox/lightbox.min.js'));

    $this->assign['searchUrl'] =  url([ADMIN, 'vedika', 'perbaikan', $type, $page . '?s=' . $phrase . '&start_date=' . $start_date . '&end_date=' . $end_date]);
    $this->assign['ralanUrl'] =  url([ADMIN, 'vedika', 'perbaikan', 'ralan', $page . '?s=' . $phrase . '&start_date=' . $start_date . '&end_date=' . $end_date]);
    $this->assign['ranapUrl'] =  url([ADMIN, 'vedika', 'perbaikan', 'ranap', $page . '?s=' . $phrase . '&start_date=' . $start_date . '&end_date=' . $end_date]);
    return $this->draw('perbaikan.html', ['tab' => $type, 'vedika' => $this->assign]);
  }

  public function getFormSEPVClaim()
  {
    $this->tpl->set('poliklinik', $this->core->mysql('poliklinik')->where('status', '1')->toArray());
    $this->tpl->set('dokter', $this->core->mysql('dokter')->where('status', '1')->toArray());
    echo $this->tpl->draw(MODULES . '/vedika/view/admin/form.sepvclaim.html', true);
    exit();
  }

  public function getHapus($no_sep)
  {
    $query = $this->core->mysql('bridging_sep')->where('no_sep', $no_sep)->delete();
    if ($query) {
      $this->core->mysql('bpjs_prb')->where('no_sep', $no_sep)->delete();
    }
    echo 'No SEP ' . $no_sep . ' telah dihapus.!!';
    exit();
  }

  public function getHapusBerkas($no_rawat, $nama_file)
  {
    $berkasPerawatan = $this->core->mysql('berkas_digital_perawatan')->where('no_rawat', revertNorawat($no_rawat))->like('lokasi_file', '%'.$nama_file.'%')->oneArray();
    if ($berkasPerawatan) {
      $lokasi_file = $berkasPerawatan['lokasi_file'];
      $fileLoc = WEBAPPS_PATH . '/berkasrawat/' . $lokasi_file;
      if (file_exists($fileLoc)) {
        //unlink($fileLoc);
        $query = $this->core->mysql('berkas_digital_perawatan')->where('no_rawat', revertNorawat($no_rawat))->where('lokasi_file', $lokasi_file)->delete();
        if ($query) {
          echo 'Hapus berkas sukses';
        } else {
          echo 'Hapus berkas gagal';
        }
      } else {
        echo 'Hapus berkas gagal, berkas tidak ditemukan.';
      }
    } else {
      echo 'Hapus berkas gagal, tidak ada data perawatan.';
    }
    exit();
  }

  public function postSaveSEP()
  {
    $date = date('Y-m-d');
    date_default_timezone_set('UTC');
    $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
    $key = $this->consid . $this->secretkey . $tStamp;

    header('Content-type: text/html');
    $url = $this->settings->get('settings.BpjsApiUrl') . 'SEP/' . $_POST['no_sep'];
    $consid = $this->settings->get('settings.BpjsConsID');
    $secretkey = $this->settings->get('settings.BpjsSecretKey');
    $userkey = $this->settings->get('settings.BpjsUserKey');
    $output = BpjsService::get($url, NULL, $consid, $secretkey, $userkey, $tStamp);
    $data = json_decode($output, true);
    // print_r($output);
    $code = $data['metaData']['code'];
    $message = $data['metaData']['message'];
    $stringDecrypt = stringDecrypt($key, $data['response']);
    $decompress = '""';
    if (!empty($stringDecrypt)) {
      $decompress = decompress($stringDecrypt);
    }
    if ($data != null) {
      $data = '{
          "metaData": {
            "code": "' . $code . '",
            "message": "' . $message . '"
          },
          "response": ' . $decompress . '}';
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

    $jenis_pelayanan = '2';
    if ($data['response']['jnsPelayanan'] == 'Rawat Inap') {
      $jenis_pelayanan = '1';
    }
    // echo json_encode($data);
    $data_rujukan = [];
    $no_telp = "00000000";
    if ($data['response']['noRujukan'] == "") {
      $data_rujukan['response']['rujukan']['tglKunjungan'] = $_POST['tgl_kunjungan'];
      $data_rujukan['response']['rujukan']['provPerujuk']['kode'] = $this->settings->get('settings.ppk_bpjs');
      $data_rujukan['response']['rujukan']['provPerujuk']['nama'] = $this->settings->get('settings.nama_instansi');
      $data_rujukan['response']['rujukan']['diagnosa']['kode'] = $_POST['kd_diagnosa'];
      $data_rujukan['response']['rujukan']['diagnosa']['nama'] = $data['response']['diagnosa'];
      $data_rujukan['response']['rujukan']['pelayanan']['kode'] = $jenis_pelayanan;
    } else {
      $url_rujukan = $this->settings->get('settings.BpjsApiUrl') . 'Rujukan/' . $data['response']['noRujukan'];
      if ($_POST['asal_rujukan'] == 2) {
        $url_rujukan = $this->settings->get('settings.BpjsApiUrl') . 'Rujukan/RS/' . $data['response']['noRujukan'];
      }
      $rujukan = BpjsService::get($url_rujukan, NULL, $consid, $secretkey, $userkey, $tStamp);
      $data_rujukan = json_decode($rujukan, true);
      // print_r($rujukan);

      $code = $data_rujukan['metaData']['code'];
      $message = $data_rujukan['metaData']['message'];
      $stringDecrypt = stringDecrypt($key, $data_rujukan['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
        $decompress = decompress($stringDecrypt);
      }
      if ($data_rujukan != null) {
        $data_rujukan = '{
            "metaData": {
              "code": "' . $code . '",
              "message": "' . $message . '"
            },
            "response": ' . $decompress . '}';
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

      // echo json_encode($data_rujukan);
      $no_telp = $data_rujukan['response']['rujukan']['peserta']['mr']['noTelepon'];
      if (empty($data_rujukan['response']['rujukan']['peserta']['mr']['noTelepon'])) {
        $no_telp = '00000000';
      }

      if ($data_rujukan['metaData']['code'] == 201) {
        $data_rujukan['response']['rujukan']['tglKunjungan'] = $_POST['tgl_kunjungan'];
        $data_rujukan['response']['rujukan']['provPerujuk']['kode'] = $this->settings->get('settings.ppk_bpjs');
        $data_rujukan['response']['rujukan']['provPerujuk']['nama'] = $this->settings->get('settings.nama_instansi');
        $data_rujukan['response']['rujukan']['diagnosa']['kode'] = $_POST['kd_diagnosa'];
        $data_rujukan['response']['rujukan']['diagnosa']['nama'] = $data['response']['diagnosa'];
        $data_rujukan['response']['rujukan']['pelayanan']['kode'] = $jenis_pelayanan;
      } else if ($data_rujukan['metaData']['code'] == 202) {
        $data_rujukan['response']['rujukan']['tglKunjungan'] = $_POST['tgl_kunjungan'];
        $data_rujukan['response']['rujukan']['provPerujuk']['kode'] = $this->settings->get('settings.ppk_bpjs');
        $data_rujukan['response']['rujukan']['provPerujuk']['nama'] = $this->settings->get('settings.nama_instansi');
        $data_rujukan['response']['rujukan']['diagnosa']['kode'] = $_POST['kd_diagnosa'];
        $data_rujukan['response']['rujukan']['diagnosa']['nama'] = $data['response']['diagnosa'];
        $data_rujukan['response']['rujukan']['pelayanan']['kode'] = $jenis_pelayanan;
      }
    }

    if($data['response']['dpjp']['kdDPJP'] =='0')
    {
	    $data['response']['dpjp']['kdDPJP'] = $this->core->mysql('maping_dokter_dpjpvclaim')->where('kd_dokter', $_POST['kd_dokter'])->oneArray()['kd_dokter_bpjs'];
      $data['response']['dpjp']['nmDPJP'] = $this->core->mysql('maping_dokter_dpjpvclaim')->where('kd_dokter', $_POST['kd_dokter'])->oneArray()['nm_dokter_bpjs'];
    }

    if ($data['metaData']['code'] == 200) {
      $insert = $this->core->mysql('bridging_sep')->save([
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
        'kdpolitujuan' => $this->core->mysql('maping_poli_bpjs')->where('kd_poli_rs', $_POST['kd_poli'])->oneArray()['kd_poli_bpjs'],
        'nmpolitujuan' => $this->core->mysql('maping_poli_bpjs')->where('kd_poli_rs', $_POST['kd_poli'])->oneArray()['nm_poli_bpjs'],
        'klsrawat' =>  $data['response']['klsRawat']['klsRawatHak'],
        'klsnaik' => $data['response']['klsRawat']['klsRawatNaik'] == null ? "" : $data['response']['klsRawat']['klsRawatNaik'],
        'pembiayaan' => $data['response']['klsRawat']['pembiayaan']  == null ? "" : $data['response']['klsRawat']['pembiayaan'],
        'pjnaikkelas' => $data['response']['klsRawat']['penanggungJawab']  == null ? "" : $data['response']['klsRawat']['penanggungJawab'],
        'lakalantas' => '0',
        'user' => $this->core->getUserInfo('username', null, true),
        'nomr' => $this->getRegPeriksaInfo('no_rkm_medis', $_POST['no_rawat']),
        'nama_pasien' => $data['response']['peserta']['nama'],
        'tanggal_lahir' => $data['response']['peserta']['tglLahir'],
        'peserta' => $data['response']['peserta']['jnsPeserta'],
        'jkel' => $data['response']['peserta']['kelamin'],
        'no_kartu' => $data['response']['peserta']['noKartu'],
        'tglpulang' => '0000-00-00 00:00:00',
        'asal_rujukan' => $_POST['asal_rujukan'],
        'eksekutif' => $data['response']['poliEksekutif'],
        'cob' => '0',
        'notelep' => $no_telp,
        'katarak' => '0',
        'tglkkl' => '0000-00-00',
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
        'kddpjp' => $this->core->mysql('maping_dokter_dpjpvclaim')->where('kd_dokter', $_POST['kd_dokter'])->oneArray()['kd_dokter_bpjs'],
        'nmdpdjp' => $this->core->mysql('maping_dokter_dpjpvclaim')->where('kd_dokter', $_POST['kd_dokter'])->oneArray()['nm_dokter_bpjs'],
        'tujuankunjungan' => '0',
        'flagprosedur' => '',
        'penunjang' => '',
        'asesmenpelayanan' => '',
        'kddpjplayanan' => $data['response']['dpjp']['kdDPJP'],
        'nmdpjplayanan' => $data['response']['dpjp']['nmDPJP']
      ]);
    }
    // print_r($insert);
    if ($insert) {
      $this->core->mysql('bpjs_prb')->save(['no_sep' => $data['response']['noSep'], 'prb' => $data_rujukan['response']['rujukan']['peserta']['informasi']['prolanisPRB']]);
      $this->notify('success', 'Simpan sukes');
    } else {
      $this->notify('failure', 'Simpan gagal');
      // redirect(url([ADMIN, 'vedika', 'index']));
    }
  }

  public function getPDF($id)
  {
    $berkas_digital = $this->core->mysql('berkas_digital_perawatan')
      ->join('master_berkas_digital', 'master_berkas_digital.kode=berkas_digital_perawatan.kode')
      ->where('berkas_digital_perawatan.no_rawat', $this->revertNorawat($id))
      ->asc('master_berkas_digital.nama')
      ->toArray();

    $no_rawat = $this->revertNorawat($id);

    $check_billing = $this->core->mysql()->pdo()->query("SHOW TABLES LIKE 'billing'");
    $check_billing->execute();
    $check_billing = $check_billing->fetch();

    if($check_billing) {
      $query = $this->core->mysql()->pdo()->prepare("select no,nm_perawatan,pemisah,if(biaya=0,'',biaya),if(jumlah=0,'',jumlah),if(tambahan=0,'',tambahan),if(totalbiaya=0,'',totalbiaya),totalbiaya from billing where no_rawat='$no_rawat'");
      $query->execute();
      $rows = $query->fetchAll();
      $total = 0;
      foreach ($rows as $key => $value) {
        $total = $total + $value['7'];
      }
      $total = $total;
    } else {
      $rows = [];
      $total = '';
    }

    $this->tpl->set('total', $total);

    $instansi['logo'] = $this->settings->get('settings.logo');
    $instansi['nama_instansi'] = $this->settings->get('settings.nama_instansi');
    $instansi['alamat'] = $this->settings->get('settings.alamat');
    $instansi['kota'] = $this->settings->get('settings.kota');
    $instansi['propinsi'] = $this->settings->get('settings.propinsi');
    $instansi['nomor_telepon'] = $this->settings->get('settings.nomor_telepon');
    $instansi['email'] = $this->settings->get('settings.email');

    $this->tpl->set('billing', $rows);

    /* Menggunakan billing bawaan mLITE */

    if($this->settings->get('vedika.billing') == 'mlite') {
        $settings = $this->settings('settings');
        $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));

       $reg_periksa = $this->core->mysql('reg_periksa')->where('no_rawat', $no_rawat)->oneArray();
       if($reg_periksa['status_lanjut'] == 'Ralan') {
          $result_detail['billing'] = $this->core->mysql('mlite_billing')->where('no_rawat', $no_rawat)->like('kd_billing', 'RJ%')->desc('id_billing')->oneArray();
          $result_detail['fullname'] = $this->core->getUserInfo('fullname', $result_detail['billing']['id_user'], true);

          $result_detail['poliklinik'] = $this->core->mysql('poliklinik')
            ->join('reg_periksa', 'reg_periksa.kd_poli = poliklinik.kd_poli')
            ->where('reg_periksa.no_rawat', $no_rawat)
            ->oneArray();

          $result_detail['rawat_jl_dr'] = $this->core->mysql('rawat_jl_dr')
            ->select('jns_perawatan.nm_perawatan')
            ->select(['biaya_rawat' => 'rawat_jl_dr.biaya_rawat'])
            ->select(['jml' => 'COUNT(rawat_jl_dr.kd_jenis_prw)'])
            ->select(['total_biaya_rawat_dr' => 'SUM(rawat_jl_dr.biaya_rawat)'])
            ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_jl_dr.kd_jenis_prw')
            ->where('rawat_jl_dr.no_rawat', $no_rawat)
            ->group('jns_perawatan.nm_perawatan')
            ->toArray();

          $total_rawat_jl_dr = 0;
          foreach ($result_detail['rawat_jl_dr'] as $row) {
            $total_rawat_jl_dr += $row['biaya_rawat'];
          }

          $result_detail['rawat_jl_pr'] = $this->core->mysql('rawat_jl_pr')
            ->select('jns_perawatan.nm_perawatan')
            ->select(['biaya_rawat' => 'rawat_jl_pr.biaya_rawat'])
            ->select(['jml' => 'COUNT(rawat_jl_pr.kd_jenis_prw)'])
            ->select(['total_biaya_rawat_pr' => 'SUM(rawat_jl_pr.biaya_rawat)'])
            ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_jl_pr.kd_jenis_prw')
            ->where('rawat_jl_pr.no_rawat', $no_rawat)
            ->group('jns_perawatan.nm_perawatan')
            ->toArray();

          $total_rawat_jl_pr = 0;
          foreach ($result_detail['rawat_jl_pr'] as $row) {
            $total_rawat_jl_pr += $row['biaya_rawat'];
          }

          $result_detail['rawat_jl_drpr'] = $this->core->mysql('rawat_jl_drpr')
            ->select('jns_perawatan.nm_perawatan')
            ->select(['biaya_rawat' => 'rawat_jl_drpr.biaya_rawat'])
            ->select(['jml' => 'COUNT(rawat_jl_drpr.kd_jenis_prw)'])
            ->select(['total_biaya_rawat_drpr' => 'SUM(rawat_jl_drpr.biaya_rawat)'])
            ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_jl_drpr.kd_jenis_prw')
            ->where('rawat_jl_drpr.no_rawat', $no_rawat)
            ->group('jns_perawatan.nm_perawatan')
            ->toArray();

          $total_rawat_jl_drpr = 0;
          foreach ($result_detail['rawat_jl_drpr'] as $row) {
            $total_rawat_jl_drpr += $row['biaya_rawat'];
          }

          $result_detail['detail_pemberian_obat'] = $this->core->mysql('detail_pemberian_obat')
            ->join('databarang', 'databarang.kode_brng=detail_pemberian_obat.kode_brng')
            ->where('no_rawat', $no_rawat)
            ->where('detail_pemberian_obat.status', 'Ralan')
            ->toArray();

          $total_detail_pemberian_obat = 0;
          foreach ($result_detail['detail_pemberian_obat'] as $row) {
            $total_detail_pemberian_obat += $row['total'];
          }

          $result_detail['periksa_lab'] = $this->core->mysql('periksa_lab')
            ->join('jns_perawatan_lab', 'jns_perawatan_lab.kd_jenis_prw=periksa_lab.kd_jenis_prw')
            ->where('no_rawat', $no_rawat)
            ->where('periksa_lab.status', 'Ralan')
            ->toArray();

          $total_periksa_lab = 0;
          foreach ($result_detail['periksa_lab'] as $row) {
            $total_periksa_lab += $row['biaya'];
          }

          $result_detail['periksa_radiologi'] = $this->core->mysql('periksa_radiologi')
            ->join('jns_perawatan_radiologi', 'jns_perawatan_radiologi.kd_jenis_prw=periksa_radiologi.kd_jenis_prw')
            ->where('no_rawat', $no_rawat)
            ->where('periksa_radiologi.status', 'Ralan')
            ->toArray();

          $total_periksa_radiologi = 0;
          foreach ($result_detail['periksa_radiologi'] as $row) {
            $total_periksa_radiologi += $row['biaya'];
          }

          $jumlah_total_operasi = 0;
          $operasis = $this->core->mysql('operasi')->join('paket_operasi', 'paket_operasi.kode_paket=operasi.kode_paket')->where('no_rawat', $no_rawat)->where('operasi.status', 'Ralan')->toArray();
          $result_detail['operasi'] = [];
          foreach ($operasis as $operasi) {
            $operasi['jumlah'] = $operasi['biayaoperator1']+$operasi['biayaoperator2']+$operasi['biayaoperator3']+$operasi['biayaasisten_operator1']+$operasi['biayaasisten_operator2']+$operasi['biayadokter_anak']+$operasi['biayaperawaat_resusitas']+$operasi['biayadokter_anestesi']+$operasi['biayaasisten_anestesi']+$operasi['biayabidan']+$operasi['biayaperawat_luar'];
            $jumlah_total_operasi += $operasi['jumlah'];
            $result_detail['operasi'][] = $operasi;
          }
          $jumlah_total_obat_operasi = 0;
          $obat_operasis = $this->core->mysql('beri_obat_operasi')->join('obatbhp_ok', 'obatbhp_ok.kd_obat=beri_obat_operasi.kd_obat')->where('no_rawat', $no_rawat)->toArray();
          $result_detail['obat_operasi'] = [];
          foreach ($obat_operasis as $obat_operasi) {
            $obat_operasi['harga'] = $obat_operasi['hargasatuan'] * $obat_operasi['jumlah'];
            $jumlah_total_obat_operasi += $obat_operasi['harga'];
            $result_detail['obat_operasi'][] = $obat_operasi;
          }

       } else {

         $result_detail['billing'] = $this->core->mysql('mlite_billing')->where('no_rawat', $no_rawat)->like('kd_billing', 'RI%')->desc('id_billing')->oneArray();
         $result_detail['fullname'] = $this->core->getUserInfo('fullname', $result_detail['billing']['id_user'], true);

         $result_detail['kamar_inap'] = $this->core->mysql('kamar_inap')
           ->join('reg_periksa', 'reg_periksa.no_rawat = kamar_inap.no_rawat')
           ->where('reg_periksa.no_rawat', $no_rawat)
           ->oneArray();

         $result_detail['rawat_inap_dr'] = $this->core->mysql('rawat_inap_dr')
           ->select('jns_perawatan_inap.nm_perawatan')
           ->select(['biaya_rawat' => 'rawat_inap_dr.biaya_rawat'])
           ->select(['jml' => 'COUNT(rawat_inap_dr.kd_jenis_prw)'])
           ->select(['total_biaya_rawat_dr' => 'SUM(rawat_inap_dr.biaya_rawat)'])
           ->join('jns_perawatan_inap', 'jns_perawatan_inap.kd_jenis_prw = rawat_inap_dr.kd_jenis_prw')
           ->where('rawat_inap_dr.no_rawat', $no_rawat)
           ->group('jns_perawatan_inap.nm_perawatan')
           ->toArray();

         $result_detail['rawat_inap_pr'] = $this->core->mysql('rawat_inap_pr')
           ->select('jns_perawatan_inap.nm_perawatan')
           ->select(['biaya_rawat' => 'rawat_inap_pr.biaya_rawat'])
           ->select(['jml' => 'COUNT(rawat_inap_pr.kd_jenis_prw)'])
           ->select(['total_biaya_rawat_pr' => 'SUM(rawat_inap_pr.biaya_rawat)'])
           ->join('jns_perawatan_inap', 'jns_perawatan_inap.kd_jenis_prw = rawat_inap_pr.kd_jenis_prw')
           ->where('rawat_inap_pr.no_rawat', $no_rawat)
           ->group('jns_perawatan_inap.nm_perawatan')
           ->toArray();

         $result_detail['rawat_inap_drpr'] = $this->core->mysql('rawat_inap_drpr')
           ->select('jns_perawatan_inap.nm_perawatan')
           ->select(['biaya_rawat' => 'rawat_inap_drpr.biaya_rawat'])
           ->select(['jml' => 'COUNT(rawat_inap_drpr.kd_jenis_prw)'])
           ->select(['total_biaya_rawat_drpr' => 'SUM(rawat_inap_drpr.biaya_rawat)'])
           ->join('jns_perawatan_inap', 'jns_perawatan_inap.kd_jenis_prw = rawat_inap_drpr.kd_jenis_prw')
           ->where('rawat_inap_drpr.no_rawat', $no_rawat)
           ->group('jns_perawatan_inap.nm_perawatan')
           ->toArray();

         $result_detail['detail_pemberian_obat'] = $this->core->mysql('detail_pemberian_obat')
           ->join('databarang', 'databarang.kode_brng=detail_pemberian_obat.kode_brng')
           ->where('no_rawat', $no_rawat)
           ->where('detail_pemberian_obat.status', 'Ranap')
           ->toArray();

         $result_detail['periksa_lab'] = $this->core->mysql('periksa_lab')
           ->join('jns_perawatan_lab', 'jns_perawatan_lab.kd_jenis_prw=periksa_lab.kd_jenis_prw')
           ->where('no_rawat', $no_rawat)
           ->where('periksa_lab.status', 'Ranap')
           ->toArray();

         $result_detail['periksa_radiologi'] = $this->core->mysql('periksa_radiologi')
           ->join('jns_perawatan_radiologi', 'jns_perawatan_radiologi.kd_jenis_prw=periksa_radiologi.kd_jenis_prw')
           ->where('no_rawat', $no_rawat)
           ->where('periksa_radiologi.status', 'Ranap')
           ->toArray();

         $result_detail['tambahan_biaya'] = $this->core->mysql('tambahan_biaya')
           ->where('status', 'ranap')
           ->where('no_rawat', $no_rawat)
           ->toArray();

         $jumlah_total_operasi = 0;
         $operasis = $this->core->mysql('operasi')->join('paket_operasi', 'paket_operasi.kode_paket=operasi.kode_paket')->where('no_rawat', $no_rawat)->where('operasi.status', 'Ranap')->toArray();
         $result_detail['operasi'] = [];
         foreach ($operasis as $operasi) {
           $operasi['jumlah'] = $operasi['biayaoperator1']+$operasi['biayaoperator2']+$operasi['biayaoperator3']+$operasi['biayaasisten_operator1']+$operasi['biayaasisten_operator2']+$operasi['biayadokter_anak']+$operasi['biayaperawaat_resusitas']+$operasi['biayadokter_anestesi']+$operasi['biayaasisten_anestesi']+$operasi['biayabidan']+$operasi['biayaperawat_luar'];
           $jumlah_total_operasi += $operasi['jumlah'];
           $result_detail['operasi'][] = $operasi;
         }
         $jumlah_total_obat_operasi = 0;
         $obat_operasis = $this->core->mysql('beri_obat_operasi')->join('obatbhp_ok', 'obatbhp_ok.kd_obat=beri_obat_operasi.kd_obat')->where('no_rawat', $no_rawat)->toArray();
         $result_detail['obat_operasi'] = [];
         foreach ($obat_operasis as $obat_operasi) {
           $obat_operasi['harga'] = $obat_operasi['hargasatuan'] * $obat_operasi['jumlah'];
           $jumlah_total_obat_operasi += $obat_operasi['harga'];
           $result_detail['obat_operasi'][] = $obat_operasi;
         }

       }

       $this->tpl->set('billing', $result_detail);

    }

    /* End menggunakan billing bawaan mlITE */

    $this->tpl->set('instansi', $instansi);

    $print_sep = array();
    if (!empty($this->_getSEPInfo('no_sep', $no_rawat))) {
      $print_sep['bridging_sep'] = $this->core->mysql('bridging_sep')->where('no_sep', $this->_getSEPInfo('no_sep', $no_rawat))->oneArray();
      $print_sep['bpjs_prb'] = $this->core->mysql('bpjs_prb')->where('no_sep', $this->_getSEPInfo('no_sep', $no_rawat))->oneArray();
      $batas_rujukan = $this->core->mysql('bridging_sep')->select('DATE_ADD(tglrujukan , INTERVAL 85 DAY) AS batas_rujukan')->where('no_sep', $id)->oneArray();
      $print_sep['batas_rujukan'] = $batas_rujukan['batas_rujukan'];
      switch ($print_sep['bridging_sep']['klsnaik']) {
        case '2':
          $print_sep['kelas_naik'] = 'Kelas VIP';
          break;
        case '3':
          $print_sep['kelas_naik'] = 'Kelas 1';
          break;
        case '4':
          $print_sep['kelas_naik'] = 'Kelas 2';
          break;

        default:
          $print_sep['kelas_naik'] = "";
          break;
      }
    }
    $print_sep['nama_instansi'] = $this->settings->get('settings.nama_instansi');
    $print_sep['logoURL'] = url(MODULES . '/vclaim/img/bpjslogo.png');
    $this->tpl->set('print_sep', $print_sep);

    $cek_spri = $this->core->mysql('bridging_surat_pri_bpjs')->where('no_rawat', $this->revertNorawat($id))->oneArray();
    $this->tpl->set('cek_spri', $cek_spri);

    $print_spri = array();
    if (!empty($this->_getSPRIInfo('no_surat', $no_rawat))) {
      $print_spri['bridging_surat_pri_bpjs'] = $this->core->mysql('bridging_surat_pri_bpjs')->where('no_surat', $this->_getSPRIInfo('no_surat', $no_rawat))->oneArray();
    }
    $print_spri['nama_instansi'] = $this->settings->get('settings.nama_instansi');
    $print_spri['logoURL'] = url(MODULES . '/vclaim/img/bpjslogo.png');
    $this->tpl->set('print_spri', $print_spri);

    $resume_pasien = $this->core->mysql('resume_pasien')
      ->join('dokter', 'dokter.kd_dokter = resume_pasien.kd_dokter')
      ->where('no_rawat', $this->revertNorawat($id))
      ->oneArray();
    if(!$this->core->mysql('resume_pasien')->where('no_rawat', $this->revertNorawat($id))->oneArray()) {
      $resume_pasien = $this->core->mysql('resume_pasien_ranap')
        ->join('dokter', 'dokter.kd_dokter = resume_pasien_ranap.kd_dokter')
        ->where('no_rawat', $this->revertNorawat($id))
        ->oneArray();
    }
    $this->tpl->set('resume_pasien', $resume_pasien);

    $pasien = $this->core->mysql('pasien')
      ->join('kecamatan', 'kecamatan.kd_kec = pasien.kd_kec')
      ->join('kabupaten', 'kabupaten.kd_kab = pasien.kd_kab')
      ->where('no_rkm_medis', $this->getRegPeriksaInfo('no_rkm_medis', $this->revertNorawat($id)))
      ->oneArray();
    $reg_periksa = $this->core->mysql('reg_periksa')
      ->join('dokter', 'dokter.kd_dokter = reg_periksa.kd_dokter')
      ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
      ->join('penjab', 'penjab.kd_pj = reg_periksa.kd_pj')
      ->where('stts', '<>', 'Batal')
      ->where('no_rawat', $this->revertNorawat($id))
      ->oneArray();
    $rows_dpjp_ranap = $this->core->mysql('dpjp_ranap')
      ->join('dokter', 'dokter.kd_dokter = dpjp_ranap.kd_dokter')
      ->where('no_rawat', $this->revertNorawat($id))
      ->toArray();
    $dpjp_i = 1;
    $dpjp_ranap = [];
    foreach ($rows_dpjp_ranap as $row) {
      $row['nomor'] = $dpjp_i++;
      $dpjp_ranap[] = $row;
    }
    /*
    $rujukan_internal = $this->core->mysql('rujukan_internal_poli')
      ->join('poliklinik', 'poliklinik.kd_poli = rujukan_internal_poli.kd_poli')
      ->join('dokter', 'dokter.kd_dokter = rujukan_internal_poli.kd_dokter')
      ->where('no_rawat', $this->revertNorawat($id))
      ->oneArray();
    */
    $diagnosa_pasien = $this->core->mysql('diagnosa_pasien')
      ->join('penyakit', 'penyakit.kd_penyakit = diagnosa_pasien.kd_penyakit')
      ->where('no_rawat', $this->revertNorawat($id))
      ->where('diagnosa_pasien.status', 'Ralan')
      ->toArray();
    if($reg_periksa['status_lanjut'] == 'Ranap'){
      $diagnosa_pasien = $this->core->mysql('diagnosa_pasien')
        ->join('penyakit', 'penyakit.kd_penyakit = diagnosa_pasien.kd_penyakit')
        ->where('no_rawat', $this->revertNorawat($id))
        ->where('diagnosa_pasien.status', 'Ranap')
        ->toArray();
    }
    $prosedur_pasien = $this->core->mysql('prosedur_pasien')
      ->join('icd9', 'icd9.kode = prosedur_pasien.kode')
      ->where('no_rawat', $this->revertNorawat($id))
      ->toArray();
    $pemeriksaan_ralan = $this->core->mysql('pemeriksaan_ralan')
      ->where('no_rawat', $this->revertNorawat($id))
      ->asc('tgl_perawatan')
      ->asc('jam_rawat')
      ->toArray();
    $pemeriksaan_ranap = $this->core->mysql('pemeriksaan_ranap')
      ->where('no_rawat', $this->revertNorawat($id))
      ->asc('tgl_perawatan')
      ->asc('jam_rawat')
      ->toArray();
    $rawat_jl_dr = $this->core->mysql('rawat_jl_dr')
      ->join('jns_perawatan', 'rawat_jl_dr.kd_jenis_prw=jns_perawatan.kd_jenis_prw')
      ->join('dokter', 'rawat_jl_dr.kd_dokter=dokter.kd_dokter')
      ->where('no_rawat', $this->revertNorawat($id))
      ->toArray();
    $rawat_jl_pr = $this->core->mysql('rawat_jl_pr')
      ->join('jns_perawatan', 'rawat_jl_pr.kd_jenis_prw=jns_perawatan.kd_jenis_prw')
      ->join('petugas', 'rawat_jl_pr.nip=petugas.nip')
      ->where('no_rawat', $this->revertNorawat($id))
      ->toArray();
    $rawat_jl_drpr = $this->core->mysql('rawat_jl_drpr')
      ->join('jns_perawatan', 'rawat_jl_drpr.kd_jenis_prw=jns_perawatan.kd_jenis_prw')
      ->join('dokter', 'rawat_jl_drpr.kd_dokter=dokter.kd_dokter')
      ->join('petugas', 'rawat_jl_drpr.nip=petugas.nip')
      ->where('no_rawat', $this->revertNorawat($id))
      ->toArray();
    $rawat_inap_dr = $this->core->mysql('rawat_inap_dr')
      ->join('jns_perawatan_inap', 'rawat_inap_dr.kd_jenis_prw=jns_perawatan_inap.kd_jenis_prw')
      ->join('dokter', 'rawat_inap_dr.kd_dokter=dokter.kd_dokter')
      ->where('no_rawat', $this->revertNorawat($id))
      ->toArray();
    $rawat_inap_pr = $this->core->mysql('rawat_inap_pr')
      ->join('jns_perawatan_inap', 'rawat_inap_pr.kd_jenis_prw=jns_perawatan_inap.kd_jenis_prw')
      ->join('petugas', 'rawat_inap_pr.nip=petugas.nip')
      ->where('no_rawat', $this->revertNorawat($id))
      ->toArray();
    $rawat_inap_drpr = $this->core->mysql('rawat_inap_drpr')
      ->join('jns_perawatan_inap', 'rawat_inap_drpr.kd_jenis_prw=jns_perawatan_inap.kd_jenis_prw')
      ->join('dokter', 'rawat_inap_drpr.kd_dokter=dokter.kd_dokter')
      ->join('petugas', 'rawat_inap_drpr.nip=petugas.nip')
      ->where('no_rawat', $this->revertNorawat($id))
      ->toArray();
    $kamar_inap = $this->core->mysql('kamar_inap')
      ->join('kamar', 'kamar_inap.kd_kamar=kamar.kd_kamar')
      ->join('bangsal', 'kamar.kd_bangsal=bangsal.kd_bangsal')
      ->where('no_rawat', $this->revertNorawat($id))
      ->toArray();
    $operasi = $this->core->mysql('operasi')
      ->join('paket_operasi', 'operasi.kode_paket=paket_operasi.kode_paket')
      ->where('no_rawat', $this->revertNorawat($id))
      ->toArray();
    $tindakan_radiologi = $this->core->mysql('periksa_radiologi')
      ->join('jns_perawatan_radiologi', 'periksa_radiologi.kd_jenis_prw=jns_perawatan_radiologi.kd_jenis_prw')
      ->join('dokter', 'periksa_radiologi.kd_dokter=dokter.kd_dokter')
      ->join('petugas', 'periksa_radiologi.nip=petugas.nip')
      ->where('no_rawat', $this->revertNorawat($id))
      ->toArray();
    $hasil_radiologi = $this->core->mysql('hasil_radiologi')
      ->where('no_rawat', $this->revertNorawat($id))
      ->toArray();

     $klinis_radiologi = $this->core->mysql('diagnosa_pasien_klinis')
      ->join('permintaan_radiologi', 'permintaan_radiologi.noorder=diagnosa_pasien_klinis.noorder')
      ->where('no_rawat', $this->revertNorawat($id))
      ->toArray();
    $saran_rad = $this->core->mysql('saran_kesan_rad')
      ->where('no_rawat', $this->revertNorawat($id))
      ->toArray();
    $pemeriksaan_laboratorium = [];
    $rows_pemeriksaan_laboratorium = $this->core->mysql('periksa_lab')
      ->join('jns_perawatan_lab', 'jns_perawatan_lab.kd_jenis_prw=periksa_lab.kd_jenis_prw')
      ->where('no_rawat', $this->revertNorawat($id))
      ->toArray();
    foreach ($rows_pemeriksaan_laboratorium as $value) {
      $value['detail_periksa_lab'] = $this->core->mysql('detail_periksa_lab')
        ->join('template_laboratorium', 'template_laboratorium.id_template=detail_periksa_lab.id_template')
        ->where('detail_periksa_lab.no_rawat', $value['no_rawat'])
        ->where('detail_periksa_lab.kd_jenis_prw', $value['kd_jenis_prw'])
        ->toArray();
      $pemeriksaan_laboratorium[] = $value;
    }
    $pemberian_obat = $this->core->mysql('detail_pemberian_obat')
      ->join('databarang', 'detail_pemberian_obat.kode_brng=databarang.kode_brng')
      ->where('no_rawat', $this->revertNorawat($id))
      ->toArray();
    $obat_operasi = $this->core->mysql('beri_obat_operasi')
      ->join('obatbhp_ok', 'beri_obat_operasi.kd_obat=obatbhp_ok.kd_obat')
      ->where('no_rawat', $this->revertNorawat($id))
      ->toArray();
    $resep_pulang = $this->core->mysql('resep_pulang')
      ->join('databarang', 'resep_pulang.kode_brng=databarang.kode_brng')
      ->where('no_rawat', $this->revertNorawat($id))
      ->toArray();
    $laporan_operasi = $this->core->mysql('laporan_operasi')
      ->where('no_rawat', $this->revertNorawat($id))
      ->oneArray();

    $rujukan_internal = $this->core->mysql('rujukan_internal_poli')
    ->join('dokter', ' rujukan_internal_poli.kd_dokter=dokter.kd_dokter')
    ->join('poliklinik', 'poliklinik.kd_poli=rujukan_internal_poli.kd_poli')
    ->where('no_rawat', $this->revertNorawat($id))
    ->toArray();

   $rujukan_internal_poli_detail = $this->core->mysql('rujukan_internal_poli_detail')
    ->where('no_rawat', $this->revertNorawat($id))
    ->oneArray();
    $this->tpl->set('rujukan_internal_poli_detail', $rujukan_internal_poli_detail);

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
    $this->tpl->set('klinis_radiologi', $klinis_radiologi);
    $this->tpl->set('saran_rad', $saran_rad);
    $this->tpl->set('pemeriksaan_laboratorium', $pemeriksaan_laboratorium);
    $this->tpl->set('pemberian_obat', $pemberian_obat);
    $this->tpl->set('obat_operasi', $obat_operasi);
    $this->tpl->set('resep_pulang', $resep_pulang);
    $this->tpl->set('laporan_operasi', $laporan_operasi);

    $this->tpl->set('berkas_digital', $berkas_digital);
    $this->tpl->set('hasil_radiologi', $this->core->mysql('hasil_radiologi')->where('no_rawat', $this->revertNorawat($id))->toArray());
    $this->tpl->set('gambar_radiologi', $this->core->mysql('gambar_radiologi')->where('no_rawat', $this->revertNorawat($id))->toArray());
    $this->tpl->set('vedika', htmlspecialchars_array($this->settings('vedika')));
    $this->tpl->set('pengaturan_billing', $this->settings->get('vedika.billing'));
    echo $this->tpl->draw(MODULES . '/vedika/view/admin/pdf.html', true);
    exit();
  }

  public function getSetStatus($id)
  {
    $set_status = $this->core->mysql('bridging_sep')->where('no_sep', $id)->oneArray();
    $vedika = $this->core->mysql('mlite_vedika')->join('mlite_vedika_feedback','mlite_vedika_feedback.nosep=mlite_vedika.nosep')->where('mlite_vedika.nosep', $id)->asc('mlite_vedika.id')->toArray();
    $this->tpl->set('logo', $this->settings->get('settings.logo'));
    $this->tpl->set('nama_instansi', $this->settings->get('settings.nama_instansi'));
    $this->tpl->set('set_status', $set_status);
    $this->tpl->set('vedika', $vedika);
    echo $this->tpl->draw(MODULES . '/vedika/view/admin/setstatus.html', true);
    exit();
  }

  public function getBerkasPasien()
  {
    echo $this->tpl->draw(MODULES . '/vedika/view/admin/berkaspasien.html', true);
    exit();
  }

  public function anyBerkasPerawatan($no_rawat)
  {
    $row_berkasdig = $this->core->mysql('berkas_digital_perawatan')
      ->join('master_berkas_digital', 'master_berkas_digital.kode=berkas_digital_perawatan.kode')
      ->where('berkas_digital_perawatan.no_rawat', revertNorawat($no_rawat))
      ->toArray();

    $this->assign['master_berkas_digital'] = $this->core->mysql('master_berkas_digital')->toArray();
    $this->assign['berkas_digital'] = $row_berkasdig;

    $this->assign['no_rawat'] = revertNorawat($no_rawat);
    $this->assign['user_role'] = $this->core->getUserInfo('role');
    $this->tpl->set('berkasperawatan', $this->assign);

    echo $this->tpl->draw(MODULES . '/vedika/view/admin/berkasperawatan.html', true);
    exit();
  }

  public function postSaveBerkasDigital()
  {
    if(MULTI_APP) {

      $curl = curl_init();
      $filePath = $_FILES['files']['tmp_name'];

      curl_setopt_array($curl, array(
        CURLOPT_URL => str_replace('webapps','',WEBAPPS_URL).'api/berkasdigital',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('file'=> new \CURLFILE($filePath),'token' => $this->settings->get('api.berkasdigital_key'), 'no_rawat' => $_POST['no_rawat'], 'kode' => $_POST['kode']),
        CURLOPT_HTTPHEADER => array(),
      ));

      $response = curl_exec($curl);

      curl_close($curl);
      //echo $response;
      if($response == 'Success') {
        echo '<br><img src="'.WEBAPPS_URL.'/berkasrawat/'.$lokasi_file.'" width="150" />';
      }

    } else {
      $dir    = $this->_uploads;
      $cntr   = 0;

      $image = $_FILES['files']['tmp_name'];
      $img = new \Systems\Lib\Image();
      $id = convertNorawat($_POST['no_rawat']);
      if ($img->load($image)) {
        $imgName = time() . $cntr++;
        $imgPath = $dir . '/' . $id . '_' . $imgName . '.' . $img->getInfos('type');
        $lokasi_file = 'pages/upload/' . $id . '_' . $imgName . '.' . $img->getInfos('type');
        $img->save($imgPath);
        $query = $this->core->mysql('berkas_digital_perawatan')->save(['no_rawat' => $_POST['no_rawat'], 'kode' => $_POST['kode'], 'lokasi_file' => $lokasi_file]);
        if ($query) {
          echo '<br><img src="' . WEBAPPS_URL . '/berkasrawat/' . $lokasi_file . '" width="150" />';
        }
      }
    }
    exit();
  }

  public function postSaveStatus()
  {
    redirect(url([ADMIN, 'vedika', 'index']));
    //redirect(parseURL());
  }

  private function _getSEPInfo($field, $no_rawat)
  {
    $row = $this->core->mysql('bridging_sep')->where('no_rawat', $no_rawat)->oneArray();
    if(!$row) {
      $row[$field] = '';
    }
    return $row[$field];
  }

  private function _getSPRIInfo($field, $no_rawat)
  {
    $row = $this->core->mysql('bridging_surat_pri_bpjs')->where('no_rawat', $no_rawat)->oneArray();
    if(!$row) {
      $row[$field] = '';
    }
    return $row[$field];
  }

  private function _getDiagnosa($field, $no_rawat, $status_lanjut)
  {
    $row = $this->core->mysql('diagnosa_pasien')->join('penyakit', 'penyakit.kd_penyakit = diagnosa_pasien.kd_penyakit')->where('diagnosa_pasien.no_rawat', $no_rawat)->where('diagnosa_pasien.prioritas', 1)->where('diagnosa_pasien.status', $status_lanjut)->oneArray();
    if(!$row) {
      $row[$field] = '';
    }
    return $row[$field];
  }

  public function getSettings()
  {
    $this->_addHeaderFiles();
    $this->assign['title'] = 'Pengaturan Modul Vedika';
    $this->assign['vedika'] = htmlspecialchars_array($this->settings('vedika'));
    $this->assign['penjab'] = $this->_getPenjab($this->settings->get('vedika.carabayar'));
    $this->assign['master_berkas_digital'] = $this->core->mysql('master_berkas_digital')->toArray();
    return $this->draw('settings.html', ['settings' => $this->assign]);
  }

  public function postSaveSettings()
  {
    $_POST['vedika']['carabayar'] = implode(',', $_POST['vedika']['carabayar']);
    foreach ($_POST['vedika'] as $key => $val) {
      $this->settings('vedika', $key, $val);
    }
    $this->notify('success', 'Pengaturan telah disimpan');
    redirect(url([ADMIN, 'vedika', 'settings']));
  }

  public function getMappingInacbgs()
  {
    $this->_addHeaderFiles();
    $this->assign['title'] = 'Pengaturan Mapping Inacbgs';
    $this->assign['vedika'] = htmlspecialchars_array($this->settings('vedika'));
    $this->assign['penjab'] = $this->_getPenjab($this->settings->get('vedika.carabayar'));
    $this->assign['kategori_perawatan'] = $this->core->mysql('kategori_perawatan')->toArray();
    return $this->draw('mapping.inacbgs.html', ['settings' => $this->assign]);
  }

  public function postSaveMappingInacbgs()
  {
    $_POST['vedika']['carabayar'] = implode(',', $_POST['vedika']['carabayar']);
    foreach ($_POST['vedika'] as $key => $val) {
      $this->settings('vedika', $key, $val);
    }
    $this->notify('success', 'Pengaturan telah disimpan');
    redirect(url([ADMIN, 'vedika', 'mappinginacbgs']));
  }

  public function getBridgingEklaim()
  {
    $this->_addHeaderFiles();
    $this->assign['title'] = 'Pengaturan Modul Vedika';
    $this->assign['vedika'] = htmlspecialchars_array($this->settings('vedika'));
    return $this->draw('bridging.eklaim.html', ['settings' => $this->assign]);
  }

  public function postSaveBridgingEklaim()
  {
    foreach ($_POST['vedika'] as $key => $val) {
      $this->settings('vedika', $key, $val);
    }
    $this->notify('success', 'Pengaturan telah disimpan');
    redirect(url([ADMIN, 'vedika', 'bridgingeklaim']));
  }

  public function getUsers()
  {
    $rows = $this->core->mysql('mlite_users_vedika')->toArray();
    foreach ($rows as &$row) {
        $row['editURL'] = url([ADMIN, 'vedika', 'useredit', $row['id']]);
        $row['delURL']  = url([ADMIN, 'vedika', 'userdelete', $row['id']]);
    }
    return $this->draw('users.html', ['users' => $rows]);
  }

  public function getUserAdd()
  {
    $this->assign['form'] = ['username' => '', 'fullname' => '', 'password' => ''];
    return $this->draw('user.form.html', ['users' => $this->assign]);
  }

  public function getUserEdit($id)
  {
    $this->assign['form'] = $this->core->mysql('mlite_users_vedika')->where('id', $id)->oneArray();
    return $this->draw('user.form.html', ['users' => $this->assign]);
  }

  public function postUserSave($id = null)
  {
    if (!$id) {    // new
      $query = $this->core->mysql('mlite_users_vedika')
      ->save([
        'username' => $_POST['username'],
        'fullname' => $_POST['fullname'],
        'password' => $_POST['password']
      ]);
    } else {        // edit
      $query = $this->core->mysql('mlite_users_vedika')
      ->where('id', $id)
      ->save([
        'username' => $_POST['username'],
        'fullname' => $_POST['fullname'],
        'password' => $_POST['password']
      ]);
    }

    if ($query) {
        $this->notify('success', 'Pengguna berhasil disimpan.');
    } else {
        $this->notify('failure', 'Gagak menyimpan pengguna.');
    }

    redirect(url([ADMIN, 'vedika', 'users']));
  }

  public function getUserDelete($id)
  {
    if ($this->core->mysql('mlite_users_vedika')->delete($id)) {
        $this->notify('success', 'Pengguna berhasil dihapus.');
    } else {
        $this->notify('failure', 'Tak dapat menghapus pengguna.');
    }
    redirect(url([ADMIN, 'vedika', 'users']));
  }

  public function getPegawaiInfo($field, $nik)
  {
    $row = $this->core->mysql('pegawai')->where('nik', $nik)->oneArray();
    if(!$row) {
      $row[$field] = '';
    }
    return $row[$field];
  }

  public function getPasienInfo($field, $no_rkm_medis)
  {
    $row = $this->core->mysql('pasien')->where('no_rkm_medis', $no_rkm_medis)->oneArray();
    if(!$row) {
      $row[$field] = '';
    }
    return $row[$field];
  }

  private function _getProsedur($field, $no_rawat, $status_lanjut)
  {
      $row = $this->core->mysql('prosedur_pasien')->join('icd9', 'icd9.kode = prosedur_pasien.kode')->where('prosedur_pasien.no_rawat', $no_rawat)->where('prosedur_pasien.prioritas', 1)->where('prosedur_pasien.status', $status_lanjut)->oneArray();
      if(!$row) {
        $row[$field] = '';
      }
      return $row[$field];
  }

  private function _getPenjab($kd_pj = null)
  {
      $result = [];
      $rows = $this->core->mysql('penjab')->where('status', '1')->toArray();

      if (!$kd_pj) {
          $kd_pjArray = [];
      } else {
          $kd_pjArray = explode(',', $kd_pj);
      }

      foreach ($rows as $row) {
          if (empty($kd_pjArray)) {
              $attr = '';
          } else {
              if (in_array($row['kd_pj'], $kd_pjArray)) {
                  $attr = 'selected';
              } else {
                  $attr = '';
              }
          }
          $result[] = ['kd_pj' => $row['kd_pj'], 'png_jawab' => $row['png_jawab'], 'attr' => $attr];
      }
      return $result;
  }

  public function getRegPeriksaInfo($field, $no_rawat)
  {
    $row = $this->core->mysql('reg_periksa')->where('no_rawat', $no_rawat)->oneArray();
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
    $result = $tahun . '/' . $bulan . '/' . $tanggal . '/' . $nomor;
    return $result;
  }

  public function getResume($status_lanjut, $no_rawat)
  {
    echo $this->draw('form.resume.html', ['status_lanjut' => $status_lanjut, 'reg_periksa' => $this->core->mysql('reg_periksa')->where('no_rawat', revertNoRawat($no_rawat))->oneArray(), 'resume_pasien' => $this->core->mysql('resume_pasien')->where('no_rawat', revertNoRawat($no_rawat))->oneArray()]);
    exit();
  }

  public function postSaveResume()
  {

    if($this->core->mysql('resume_pasien')->where('no_rawat', $_POST['no_rawat'])->oneArray()) {
      $this->core->mysql('resume_pasien')
        ->where('no_rawat', $_POST['no_rawat'])
        ->save([
        'kd_dokter'  => $this->getRegPeriksaInfo('kd_dokter', $_POST['no_rawat']),
        'keluhan_utama' => '-',
        'jalannya_penyakit' => '-',
        'pemeriksaan_penunjang' => '-',
        'hasil_laborat' => '-',
        'diagnosa_utama' => $_POST['diagnosa_utama'],
        'kd_diagnosa_utama' => '-',
        'diagnosa_sekunder' => '-',
        'kd_diagnosa_sekunder' => '-',
        'diagnosa_sekunder2' => '-',
        'kd_diagnosa_sekunder2' => '-',
        'diagnosa_sekunder3' => '-',
        'kd_diagnosa_sekunder3' => '-',
        'diagnosa_sekunder4' => '-',
        'kd_diagnosa_sekunder4' => '-',
        'prosedur_utama' => $_POST['prosedur_utama'],
        'kd_prosedur_utama' => '-',
        'prosedur_sekunder' => '-',
        'kd_prosedur_sekunder' => '-',
        'prosedur_sekunder2' => '-',
        'kd_prosedur_sekunder2' => '-',
        'prosedur_sekunder3' => '-',
        'kd_prosedur_sekunder3' => '-',
        'kondisi_pulang'  => $_POST['kondisi_pulang'],
        'obat_pulang' => '-'
      ]);
    } else {
      $this->core->mysql('resume_pasien')->save([
        'no_rawat' => $_POST['no_rawat'],
        'kd_dokter'  => $this->getRegPeriksaInfo('kd_dokter', $_POST['no_rawat']),
        'keluhan_utama' => '-',
        'jalannya_penyakit' => '-',
        'pemeriksaan_penunjang' => '-',
        'hasil_laborat' => '-',
        'diagnosa_utama' => $_POST['diagnosa_utama'],
        'kd_diagnosa_utama' => '-',
        'diagnosa_sekunder' => '-',
        'kd_diagnosa_sekunder' => '-',
        'diagnosa_sekunder2' => '-',
        'kd_diagnosa_sekunder2' => '-',
        'diagnosa_sekunder3' => '-',
        'kd_diagnosa_sekunder3' => '-',
        'diagnosa_sekunder4' => '-',
        'kd_diagnosa_sekunder4' => '-',
        'prosedur_utama' => $_POST['prosedur_utama'],
        'kd_prosedur_utama' => '-',
        'prosedur_sekunder' => '-',
        'kd_prosedur_sekunder' => '-',
        'prosedur_sekunder2' => '-',
        'kd_prosedur_sekunder2' => '-',
        'prosedur_sekunder3' => '-',
        'kd_prosedur_sekunder3' => '-',
        'kondisi_pulang'  => $_POST['kondisi_pulang'],
        'obat_pulang' => '-'
      ]);
    }
    exit();
  }

  public function getDisplayResume($no_rawat)
  {
    $resume_pasien = $this->core->mysql('resume_pasien')->where('no_rawat', revertNoRawat($no_rawat))->oneArray();
    echo $this->draw('display.resume.html', ['resume_pasien' => $resume_pasien]);
    exit();
  }

  public function getUbahDiagnosa($status_lanjut, $no_rawat)
  {
    $diagnosa_pasien = $this->core->mysql('diagnosa_pasien')->join('penyakit', 'penyakit.kd_penyakit = diagnosa_pasien.kd_penyakit')->where('diagnosa_pasien.no_rawat', revertNoRawat($no_rawat))->where('diagnosa_pasien.status', $status_lanjut)->asc('prioritas')->toArray();
    echo $this->draw('ubah.diagnosa.html', ['no_rawat' => revertNoRawat($no_rawat), 'diagnosa_pasien' => $diagnosa_pasien, 'status_lanjut' => $status_lanjut]);
    exit();
  }

  public function getDisplayDiagnosa($status_lanjut, $no_rawat)
  {
    $diagnosa_pasien = $this->core->mysql('diagnosa_pasien')->join('penyakit', 'penyakit.kd_penyakit = diagnosa_pasien.kd_penyakit')->where('diagnosa_pasien.no_rawat', revertNoRawat($no_rawat))->where('diagnosa_pasien.status', $status_lanjut)->asc('prioritas')->toArray();
    echo $this->draw('display.diagnosa.html', ['no_rawat' => revertNoRawat($no_rawat), 'diagnosa_pasien' => $diagnosa_pasien, 'status_lanjut' => $status_lanjut]);
    exit();
  }

  public function postHapusDiagnosa()
  {
    $query = $this->core->mysql('diagnosa_pasien')->where('no_rawat', $_POST['no_rawat'])->where('kd_penyakit', $_POST['kd_penyakit'])->where('prioritas', $_POST['prioritas'])->delete();
    //echo 'Hapus';
    exit();
  }

  public function getUbahProsedur($status_lanjut, $no_rawat)
  {
    $prosedur_pasien = $this->core->mysql('prosedur_pasien')->join('icd9', 'icd9.kode = prosedur_pasien.kode')->where('prosedur_pasien.no_rawat', revertNoRawat($no_rawat))->where('prosedur_pasien.status', $status_lanjut)->asc('prioritas')->toArray();
    echo $this->draw('ubah.prosedur.html', ['no_rawat' => revertNoRawat($no_rawat), 'prosedur_pasien' => $prosedur_pasien, 'status_lanjut' => $status_lanjut]);
    exit();
  }

  public function getDisplayProsedur($status_lanjut, $no_rawat)
  {
    $prosedur_pasien = $this->core->mysql('prosedur_pasien')->join('icd9', 'icd9.kode = prosedur_pasien.kode')->where('prosedur_pasien.no_rawat', revertNoRawat($no_rawat))->where('prosedur_pasien.status', $status_lanjut)->asc('prioritas')->toArray();
    echo $this->draw('display.prosedur.html', ['no_rawat' => revertNoRawat($no_rawat), 'prosedur_pasien' => $prosedur_pasien, 'status_lanjut' => $status_lanjut]);
    exit();
  }

  public function postHapusProsedur()
  {
    $query = $this->core->mysql('prosedur_pasien')->where('no_rawat', $_POST['no_rawat'])->where('kode', $_POST['kode'])->where('prioritas', $_POST['prioritas'])->delete();
    //echo 'Hapus';
    exit();
  }

  public function getBridgingInacbgs($no_rawat)
  {
    $reg_periksa = $this->core->mysql('reg_periksa')
      ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
      ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
      ->join('dokter', 'dokter.kd_dokter=reg_periksa.kd_dokter')
      ->join('penjab', 'penjab.kd_pj=reg_periksa.kd_pj')
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->oneArray();
    $reg_periksa['no_sep'] = $this->_getSEPInfo('no_sep', revertNoRawat($no_rawat));
    $reg_periksa['stts_pulang'] = '';
    $reg_periksa['tgl_keluar'] = $reg_periksa['tgl_registrasi'];
    if($reg_periksa['status_lanjut'] == 'Ranap') {
      $_get_kamar_inap = $this->core->mysql('kamar_inap')->where('no_rawat', revertNoRawat($no_rawat))->limit(1)->desc('tgl_keluar')->toArray();
      $reg_periksa['tgl_keluar'] = $_get_kamar_inap[0]['tgl_keluar'].' '.$_get_kamar_inap[0]['jam_keluar'];
      $reg_periksa['stts_pulang'] = $_get_kamar_inap[0]['stts_pulang'];
      $get_kamar = $this->core->mysql('kamar')->where('kd_kamar', $_get_kamar_inap[0]['kd_kamar'])->oneArray();
      $get_bangsal = $this->core->mysql('bangsal')->where('kd_bangsal', $get_kamar['kd_bangsal'])->oneArray();
      $reg_periksa['nm_poli'] = $get_bangsal['nm_bangsal'].'/'.$get_kamar['kd_kamar'];
      $reg_periksa['nm_dokter'] = $this->core->mysql('dpjp_ranap')
        ->join('dokter', 'dokter.kd_dokter=dpjp_ranap.kd_dokter')
        ->where('no_rawat', revertNoRawat($no_rawat))
        ->toArray();
    }

    $row_diagnosa = $this->core->mysql('diagnosa_pasien')
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->asc('prioritas')
      ->toArray();
    $a_diagnosa=1;
    foreach ($row_diagnosa as $row) {
      if($a_diagnosa==1){
          $penyakit=$row["kd_penyakit"];
      }else{
          $penyakit=$penyakit."#".$row["kd_penyakit"];
      }
      $a_diagnosa++;
    }

    $row_prosedur = $this->core->mysql('prosedur_pasien')
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->asc('prioritas')
      ->toArray();
    $prosedur= '';
    $a_prosedur=1;
    foreach ($row_prosedur as $row) {
      /* == Khusus RSHD karena data ICD nya kacau == */

      $kode = $row["kode"];
      if(strpos($row["kode"],'.') == false) {
        $kode = substr_replace($row["kode"],".", 2, 0);
      }
      if($a_prosedur==1){
          $prosedur=$kode;
      }else{
          $prosedur=$prosedur."#".$kode;
      }

      /*
      if($a_prosedur==1){
          $prosedur=$row["kode"];
      }else{
          $prosedur=$prosedur."#".$row["kode"];
      }
      */
      $a_prosedur++;
    }

    /* Prosedur non bedah ralan */
    $biaya_non_bedah_dr = $this->core->mysql('rawat_jl_dr')
      ->select(['biaya_rawat' => 'SUM(biaya_rawat)'])
      ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_jl_dr.kd_jenis_prw')
      ->where('jns_perawatan.kd_kategori', $this->settings->get('vedika.inacbgs_prosedur_non_bedah'))
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();

    $biaya_non_bedah_pr = $this->core->mysql('rawat_jl_pr')
      ->select(['biaya_rawat' => 'SUM(biaya_rawat)'])
      ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_jl_pr.kd_jenis_prw')
      ->where('jns_perawatan.kd_kategori', $this->settings->get('vedika.inacbgs_prosedur_non_bedah'))
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();

    $biaya_non_bedah_drpr = $this->core->mysql('rawat_jl_drpr')
      ->select(['biaya_rawat' => 'SUM(biaya_rawat)'])
      ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_jl_drpr.kd_jenis_prw')
      ->where('jns_perawatan.kd_kategori', $this->settings->get('vedika.inacbgs_prosedur_non_bedah'))
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    /* End prosedur non bedah ralan */

    /* Prosedur non bedah ranap */
    $biaya_non_bedah_dr_ranap = $this->core->mysql('rawat_inap_dr')
      ->select(['biaya_rawat' => 'SUM(biaya_rawat)'])
      ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_inap_dr.kd_jenis_prw')
      ->where('jns_perawatan.kd_kategori', $this->settings->get('vedika.inacbgs_prosedur_non_bedah'))
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();

    $biaya_non_bedah_pr_ranap = $this->core->mysql('rawat_inap_pr')
      ->select(['biaya_rawat' => 'SUM(biaya_rawat)'])
      ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_inap_pr.kd_jenis_prw')
      ->where('jns_perawatan.kd_kategori', $this->settings->get('vedika.inacbgs_prosedur_non_bedah'))
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();

    $biaya_non_bedah_drpr_ranap = $this->core->mysql('rawat_inap_drpr')
      ->select(['biaya_rawat' => 'SUM(biaya_rawat)'])
      ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_inap_drpr.kd_jenis_prw')
      ->where('jns_perawatan.kd_kategori', $this->settings->get('vedika.inacbgs_prosedur_non_bedah'))
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    /* End prosedur non bedah ranap */

    $total_biaya_non_bedah = 0;
    foreach (array_merge($biaya_non_bedah_dr, $biaya_non_bedah_pr, $biaya_non_bedah_drpr, $biaya_non_bedah_dr_ranap, $biaya_non_bedah_pr_ranap, $biaya_non_bedah_drpr_ranap) as $row) {
      $total_biaya_non_bedah += $row['biaya_rawat'];
    }

    /* Prosedur bedah ralan */
    $biaya_bedah_dr = $this->core->mysql('rawat_jl_dr')
      ->select(['biaya_rawat' => 'SUM(biaya_rawat)'])
      ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_jl_dr.kd_jenis_prw')
      ->where('jns_perawatan.kd_kategori', $this->settings->get('vedika.inacbgs_prosedur_bedah'))
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();

    $biaya_bedah_pr = $this->core->mysql('rawat_jl_pr')
      ->select(['biaya_rawat' => 'SUM(biaya_rawat)'])
      ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_jl_pr.kd_jenis_prw')
      ->where('jns_perawatan.kd_kategori', $this->settings->get('vedika.inacbgs_prosedur_bedah'))
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();

    $biaya_bedah_drpr = $this->core->mysql('rawat_jl_drpr')
      ->select(['biaya_rawat' => 'SUM(biaya_rawat)'])
      ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_jl_drpr.kd_jenis_prw')
      ->where('jns_perawatan.kd_kategori', $this->settings->get('vedika.inacbgs_prosedur_bedah'))
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    /* End prosedur bedah ralan */

    /* Prosedur bedah ranap */
    $biaya_bedah_dr_ranap = $this->core->mysql('rawat_inap_dr')
      ->select(['biaya_rawat' => 'SUM(biaya_rawat)'])
      ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_inap_dr.kd_jenis_prw')
      ->where('jns_perawatan.kd_kategori', $this->settings->get('vedika.inacbgs_prosedur_bedah'))
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();

    $biaya_bedah_pr_ranap = $this->core->mysql('rawat_inap_pr')
      ->select(['biaya_rawat' => 'SUM(biaya_rawat)'])
      ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_inap_pr.kd_jenis_prw')
      ->where('jns_perawatan.kd_kategori', $this->settings->get('vedika.inacbgs_prosedur_bedah'))
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();

    $biaya_bedah_drpr_ranap = $this->core->mysql('rawat_inap_drpr')
      ->select(['biaya_rawat' => 'SUM(biaya_rawat)'])
      ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_inap_drpr.kd_jenis_prw')
      ->where('jns_perawatan.kd_kategori', $this->settings->get('vedika.inacbgs_prosedur_bedah'))
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    /* End prosedur bedah ranap */

    /* Start biaya operasi */
    $biaya_operasi = $this->core->mysql('operasi')
      ->select(['biaya_rawat' => 'SUM(biayaoperator1 + biayaoperator2)'])
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    /* End biaya operasi */

    $total_biaya_bedah = 0;
    foreach (array_merge($biaya_bedah_dr, $biaya_bedah_pr, $biaya_bedah_drpr, $biaya_bedah_dr_ranap, $biaya_bedah_pr_ranap, $biaya_bedah_drpr_ranap, $biaya_operasi) as $row) {
      $total_biaya_bedah += $row['biaya_rawat'];
    }

    /* Biaya Konsultasi */
    $biaya_poliklinik = $this->core->mysql('reg_periksa')
      ->select(['biaya_rawat' => 'SUM(registrasi)'])
      ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();

    $biaya_konsultasi_dr = $this->core->mysql('rawat_inap_dr')
      ->select(['biaya_rawat' => 'SUM(biaya_rawat)'])
      ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_inap_dr.kd_jenis_prw')
      ->where('jns_perawatan.kd_kategori', $this->settings->get('vedika.inacbgs_konsultasi'))
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();

    $biaya_konsultasi_pr = $this->core->mysql('rawat_inap_pr')
      ->select(['biaya_rawat' => 'SUM(biaya_rawat)'])
      ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_inap_pr.kd_jenis_prw')
      ->where('jns_perawatan.kd_kategori', $this->settings->get('vedika.inacbgs_konsultasi'))
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();

    $biaya_konsultasi_drpr = $this->core->mysql('rawat_inap_drpr')
      ->select(['biaya_rawat' => 'SUM(biaya_rawat)'])
      ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_inap_drpr.kd_jenis_prw')
      ->where('jns_perawatan.kd_kategori', $this->settings->get('vedika.inacbgs_konsultasi'))
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    /* End Biaya Konsultasi */

    $total_biaya_konsultasi = 0;
    foreach (array_merge($biaya_poliklinik, $biaya_konsultasi_dr, $biaya_konsultasi_pr, $biaya_konsultasi_drpr) as $row) {
      $total_biaya_konsultasi += $row['biaya_rawat'];
    }

    /* Biaya Tenaga Ahli */
    $biaya_tenaga_ahli_dr = $this->core->mysql('rawat_inap_dr')
      ->select(['biaya_rawat' => 'SUM(biaya_rawat)'])
      ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_inap_dr.kd_jenis_prw')
      ->where('jns_perawatan.kd_kategori', $this->settings->get('vedika.inacbgs_tenaga_ahli'))
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();

    $biaya_tenaga_ahli_pr = $this->core->mysql('rawat_inap_pr')
      ->select(['biaya_rawat' => 'SUM(biaya_rawat)'])
      ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_inap_pr.kd_jenis_prw')
      ->where('jns_perawatan.kd_kategori', $this->settings->get('vedika.inacbgs_tenaga_ahli'))
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();

    $biaya_tenaga_ahli_drpr = $this->core->mysql('rawat_inap_drpr')
      ->select(['biaya_rawat' => 'SUM(biaya_rawat)'])
      ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_inap_drpr.kd_jenis_prw')
      ->where('jns_perawatan.kd_kategori', $this->settings->get('vedika.inacbgs_tenaga_ahli'))
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    /* End Biaya Tenaga Ahli */

    $total_biaya_tenaga_ahli = 0;
    foreach (array_merge($biaya_tenaga_ahli_dr, $biaya_tenaga_ahli_pr, $biaya_tenaga_ahli_drpr) as $row) {
      $total_biaya_tenaga_ahli += $row['biaya_rawat'];
    }

    /* Biaya Keperawatan */
    $biaya_keperawatan_jl_pr = $this->core->mysql('rawat_jl_pr')
      ->select(['biaya_rawat' => 'SUM(tarif_tindakanpr)'])
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    $biaya_keperawatan_jl_drpr = $this->core->mysql('rawat_jl_drpr')
      ->select(['biaya_rawat' => 'SUM(tarif_tindakanpr)'])
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    $biaya_keperawatan_inap_pr = $this->core->mysql('rawat_inap_pr')
      ->select(['biaya_rawat' => 'SUM(tarif_tindakanpr)'])
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    $biaya_keperawatan_inap_drpr = $this->core->mysql('rawat_inap_drpr')
      ->select(['biaya_rawat' => 'SUM(tarif_tindakanpr)'])
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    /* End Biaya Keperawatan */

    $total_biaya_keperawatan = 0;
    foreach (array_merge($biaya_keperawatan_jl_pr, $biaya_keperawatan_jl_drpr, $biaya_keperawatan_inap_pr, $biaya_keperawatan_inap_drpr) as $row) {
      $total_biaya_keperawatan += $row['biaya_rawat'];
    }

    /* Biaya Penunjang */
    $biaya_penunjang_jl_dr = $this->core->mysql('rawat_jl_dr')
      ->select(['biaya_rawat' => 'SUM(manajemen)'])
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    $biaya_penunjang_jl_pr = $this->core->mysql('rawat_jl_pr')
      ->select(['biaya_rawat' => 'SUM(manajemen)'])
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    $biaya_penunjang_jl_drpr = $this->core->mysql('rawat_jl_drpr')
      ->select(['biaya_rawat' => 'SUM(manajemen)'])
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    $biaya_penunjang_inap_dr = $this->core->mysql('rawat_inap_dr')
      ->select(['biaya_rawat' => 'SUM(manajemen)'])
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    $biaya_penunjang_inap_pr = $this->core->mysql('rawat_inap_pr')
      ->select(['biaya_rawat' => 'SUM(manajemen)'])
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    $biaya_penunjang_inap_drpr = $this->core->mysql('rawat_inap_drpr')
      ->select(['biaya_rawat' => 'SUM(manajemen)'])
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    /* End Biaya Penunjang */

    $total_biaya_penunjang = 0;
    foreach (array_merge($biaya_penunjang_jl_dr, $biaya_penunjang_jl_pr, $biaya_penunjang_jl_drpr, $biaya_penunjang_inap_dr, $biaya_penunjang_inap_pr, $biaya_penunjang_inap_drpr) as $row) {
      $total_biaya_penunjang += $row['biaya_rawat'];
    }

    $total_biaya_radiologi = 0;
    $total_biaya_laboratorium = 0;
    $total_biaya_pelayanan_darah = 0;

    /* Biaya Rehabilitasi */
    $biaya_rehabilitasi_dr = $this->core->mysql('rawat_inap_dr')
      ->select(['biaya_rawat' => 'SUM(biaya_rawat)'])
      ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_inap_dr.kd_jenis_prw')
      ->where('jns_perawatan.kd_kategori', $this->settings->get('vedika.inacbgs_rehabilitasi'))
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();

    $biaya_rehabilitasi_pr = $this->core->mysql('rawat_inap_pr')
      ->select(['biaya_rawat' => 'SUM(biaya_rawat)'])
      ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_inap_pr.kd_jenis_prw')
      ->where('jns_perawatan.kd_kategori', $this->settings->get('vedika.inacbgs_rehabilitasi'))
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();

    $biaya_rehabilitasi_drpr = $this->core->mysql('rawat_inap_drpr')
      ->select(['biaya_rawat' => 'SUM(biaya_rawat)'])
      ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_inap_drpr.kd_jenis_prw')
      ->where('jns_perawatan.kd_kategori', $this->settings->get('vedika.inacbgs_rehabilitasi'))
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    /* End Biaya Rehabilitasi */

    $total_biaya_rehabilitasi = 0;
    foreach (array_merge($biaya_rehabilitasi_dr, $biaya_rehabilitasi_pr, $biaya_rehabilitasi_drpr) as $row) {
      $total_biaya_rehabilitasi += $row['biaya_rawat'];
    }

    $total_biaya_kamar = 0;
    if($reg_periksa['status_lanjut'] == 'Ralan') {
      $total_biaya_kamar = $reg_periksa['registrasi'];
    }
    if($reg_periksa['status_lanjut'] == 'Ranap') {
      $__get_kamar_inap = $this->core->mysql('kamar_inap')->where('no_rawat', revertNoRawat($no_rawat))->limit(1)->desc('tgl_keluar')->toArray();
      foreach ($__get_kamar_inap as $row) {
        $total_biaya_kamar += $row['ttl_biaya'];
      }

    }

    /* Biaya Rawat Intensif */
    $biaya_rawat_intensif_dr = $this->core->mysql('rawat_inap_dr')
      ->select(['biaya_rawat' => 'SUM(biaya_rawat)'])
      ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_inap_dr.kd_jenis_prw')
      ->where('jns_perawatan.kd_kategori', $this->settings->get('vedika.inacbgs_rawat_intensif'))
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();

    $biaya_rawat_intensif_pr = $this->core->mysql('rawat_inap_pr')
      ->select(['biaya_rawat' => 'SUM(biaya_rawat)'])
      ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_inap_pr.kd_jenis_prw')
      ->where('jns_perawatan.kd_kategori', $this->settings->get('vedika.inacbgs_rawat_intensif'))
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();

    $biaya_rawat_intensif_drpr = $this->core->mysql('rawat_inap_drpr')
      ->select(['biaya_rawat' => 'SUM(biaya_rawat)'])
      ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_inap_drpr.kd_jenis_prw')
      ->where('jns_perawatan.kd_kategori', $this->settings->get('vedika.inacbgs_rawat_intensif'))
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    /* End Biaya Rawat Intensif */

    $total_biaya_rawat_intensif = 0;
    foreach (array_merge($biaya_rawat_intensif_dr, $biaya_rawat_intensif_pr, $biaya_rawat_intensif_drpr) as $row) {
      $total_biaya_rawat_intensif += $row['biaya_rawat'];
    }

    $total_biaya_obat = 0;
    $total_biaya_obat_kronis = 0;
    $total_biaya_obat_kemoterapi = 0;

    /* Biaya Alkes */
    $biaya_alkes_jl_dr = $this->core->mysql('rawat_jl_dr')
      ->select(['biaya_rawat' => 'SUM(material)'])
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    $biaya_alkes_jl_pr = $this->core->mysql('rawat_jl_pr')
      ->select(['biaya_rawat' => 'SUM(material)'])
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    $biaya_alkes_jl_drpr = $this->core->mysql('rawat_jl_drpr')
      ->select(['biaya_rawat' => 'SUM(material)'])
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    $biaya_alkes_inap_dr = $this->core->mysql('rawat_inap_dr')
      ->select(['biaya_rawat' => 'SUM(material)'])
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    $biaya_alkes_inap_pr = $this->core->mysql('rawat_inap_pr')
      ->select(['biaya_rawat' => 'SUM(material)'])
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    $biaya_alkes_inap_drpr = $this->core->mysql('rawat_inap_drpr')
      ->select(['biaya_rawat' => 'SUM(material)'])
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    /* End Biaya Alkes */

    $total_biaya_alkes = 0;
    foreach (array_merge($biaya_alkes_jl_dr, $biaya_alkes_jl_pr, $biaya_alkes_jl_drpr, $biaya_alkes_inap_dr, $biaya_alkes_inap_pr, $biaya_alkes_inap_drpr) as $row) {
      $total_biaya_alkes += $row['biaya_rawat'];
    }

    /* Biaya BMHP */
    $biaya_bmhp_jl_dr = $this->core->mysql('rawat_jl_dr')
      ->select(['biaya_rawat' => 'SUM(bhp)'])
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    $biaya_bmhp_jl_pr = $this->core->mysql('rawat_jl_pr')
      ->select(['biaya_rawat' => 'SUM(bhp)'])
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    $biaya_bmhp_jl_drpr = $this->core->mysql('rawat_jl_drpr')
      ->select(['biaya_rawat' => 'SUM(bhp)'])
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    $biaya_bmhp_inap_dr = $this->core->mysql('rawat_inap_dr')
      ->select(['biaya_rawat' => 'SUM(bhp)'])
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    $biaya_bmhp_inap_pr = $this->core->mysql('rawat_inap_pr')
      ->select(['biaya_rawat' => 'SUM(bhp)'])
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    $biaya_bmhp_inap_drpr = $this->core->mysql('rawat_inap_drpr')
      ->select(['biaya_rawat' => 'SUM(bhp)'])
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    /* End Biaya BMHP */

    $total_biaya_bmhp = 0;
    foreach (array_merge($biaya_bmhp_jl_dr, $biaya_bmhp_jl_pr, $biaya_bmhp_jl_drpr, $biaya_bmhp_inap_dr, $biaya_bmhp_inap_pr, $biaya_bmhp_inap_drpr) as $row) {
      $total_biaya_bmhp += $row['biaya_rawat'];
    }

    /* Biaya KSO */
    $biaya_sewa_alat_jl_dr = $this->core->mysql('rawat_jl_dr')
      ->select(['biaya_rawat' => 'SUM(kso)'])
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    $biaya_sewa_alat_jl_pr = $this->core->mysql('rawat_jl_pr')
      ->select(['biaya_rawat' => 'SUM(kso)'])
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    $biaya_sewa_alat_jl_drpr = $this->core->mysql('rawat_jl_drpr')
      ->select(['biaya_rawat' => 'SUM(kso)'])
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    $biaya_sewa_alat_inap_dr = $this->core->mysql('rawat_inap_dr')
      ->select(['biaya_rawat' => 'SUM(kso)'])
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    $biaya_sewa_alat_inap_pr = $this->core->mysql('rawat_inap_pr')
      ->select(['biaya_rawat' => 'SUM(kso)'])
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    $biaya_sewa_alat_inap_drpr = $this->core->mysql('rawat_inap_drpr')
      ->select(['biaya_rawat' => 'SUM(kso)'])
      ->where('no_rawat', revertNoRawat($no_rawat))
      ->toArray();
    /* End Biaya KSO */

    $total_biaya_sewa_alat = 0;
    foreach (array_merge($biaya_sewa_alat_jl_dr, $biaya_sewa_alat_jl_pr, $biaya_sewa_alat_jl_drpr, $biaya_sewa_alat_inap_dr, $biaya_sewa_alat_inap_pr, $biaya_sewa_alat_inap_drpr) as $row) {
      $total_biaya_sewa_alat += $row['biaya_rawat'];
    }

    /* Yang belum
    ======================
    radiologi, --> radiologi
    laboratorium, --> laboratorium
    pelayanan_darah, --> UTD atau by kategori pelayanan darah

    obat, --> resep dokter by kategori obat
    obat_kronis, --> resep dokter by kategori obat
    obat_kemoterapi, --> resep dokter by kategori obat
    ======================
    */

    $total_biaya_tarif_poli_eks = 0;
    $total_biaya_add_payment_pct = 0;

    $piutang_pasien = $this->core->mysql('piutang_pasien')->where('no_rawat', revertNoRawat($no_rawat))->oneArray();
    $total_biaya_kamar = $piutang_pasien['totalpiutang'] - $total_biaya_non_bedah - $total_biaya_bedah - $total_biaya_konsultasi - $total_biaya_keperawatan - $total_biaya_penunjang - $total_biaya_radiologi - $total_biaya_laboratorium - $total_biaya_pelayanan_darah - $total_biaya_rehabilitasi - $total_biaya_rawat_intensif - $total_biaya_obat - $total_biaya_obat_kronis - $total_biaya_obat_kemoterapi - $total_biaya_alkes - $total_biaya_bmhp - $total_biaya_sewa_alat - $total_biaya_tarif_poli_eks - $total_biaya_add_payment_pct;

    $request ='{
                     "metadata": {
                         "method":"get_claim_data"
                     },
                     "data": {
                         "nomor_sep":"'.$this->_getSEPInfo('no_sep', revertNoRawat($no_rawat)).'"
                     }
                }';

    $msg = $this->Request($request);
    $get_claim_data = [];
    if($msg['metadata']['message']=="Ok"){
      $get_claim_data = $msg;
      //echo json_encode($msg, true);
    }

    $adl = [];
    for($i=12; $i<=60; $i++){
       $adl[] = $i;
    }
    //echo json_encode($adl, true);

    echo $this->draw('inacbgs.html', [
      'reg_periksa' => $reg_periksa,
      'biaya_non_bedah' => $total_biaya_non_bedah,
      'biaya_bedah' => $total_biaya_bedah,
      'biaya_konsultasi' => $total_biaya_konsultasi,
      'biaya_tenaga_ahli' => $total_biaya_tenaga_ahli,
      'biaya_keperawatan' => $total_biaya_keperawatan,
      'biaya_penunjang' => $total_biaya_penunjang,
      'biaya_radiologi' => $total_biaya_radiologi,
      'biaya_laboratorium' => $total_biaya_laboratorium,
      'biaya_pelayanan_darah' => $total_biaya_pelayanan_darah,
      'biaya_rehabilitasi' => $total_biaya_rehabilitasi,
      'biaya_kamar' => $total_biaya_kamar,
      'biaya_rawat_intensif' => $total_biaya_rawat_intensif,
      'biaya_obat' => $total_biaya_obat,
      'biaya_obat_kronis' => $total_biaya_obat_kronis,
      'biaya_obat_kemoterapi' => $total_biaya_obat_kemoterapi,
      'biaya_alkes' => $total_biaya_alkes,
      'biaya_bmhp' => $total_biaya_bmhp,
      'biaya_sewa_alat' => $total_biaya_sewa_alat,
      'biaya_tarif_poli_eks' => $total_biaya_tarif_poli_eks,
      'biaya_add_payment_pct' => $total_biaya_add_payment_pct,
      'get_claim_data' => $get_claim_data,
      'penyakit' => $penyakit,
      'prosedur' => $prosedur,
      'adl' => $adl
    ]);
    exit();
  }

  public function postKirimInacbgs()
  {
    $_POST['ventilator_hour'] = '0';
    $_POST['jk'] = $this->core->getPasienInfo('jk', $_POST['no_rkm_medis']);;
    $_POST['tgl_lahir'] = $this->core->getPasienInfo('tgl_lahir', $_POST['no_rkm_medis']);;


    $no_rkm_medis      = $this->validTeks(trim($_POST['no_rkm_medis']));

    $norawat           = $this->validTeks(trim($_POST['no_rawat']));
    $tgl_registrasi    = $this->validTeks(trim($_POST['tgl_registrasi']));
    $nosep             = $this->validTeks(trim($_POST['nosep']));
    $nokartu           = $this->validTeks(trim($_POST['nokartu']));
    $nm_pasien         = $this->validTeks(trim($_POST['nm_pasien']));
    $keluar            = $this->validTeks(trim($_POST['keluar']));
    $kelas_rawat       = $this->validTeks(trim($_POST['kelas_rawat']));
    $adl_sub_acute     = $this->validTeks(trim($_POST['adl_sub_acute']));
    $adl_chronic       = $this->validTeks(trim($_POST['adl_chronic']));
    $icu_indikator     = $this->validTeks(trim($_POST['icu_indikator']));
    $icu_los           = $this->validTeks(trim($_POST['icu_los']));
    $ventilator_hour   = $this->validTeks(trim($_POST['ventilator_hour']));
    $upgrade_class_ind = $this->validTeks(trim($_POST['upgrade_class_ind']));
    $upgrade_class_class = $this->validTeks(trim($_POST['upgrade_class_class']));
    $upgrade_class_los = $this->validTeks(trim($_POST['upgrade_class_los']));
    $add_payment_pct   = $this->validTeks(trim($_POST['add_payment_pct']));
    $birth_weight      = $this->validTeks(trim($_POST['birth_weight']));
    $discharge_status  = $this->validTeks(trim($_POST['discharge_status']));
    $diagnosa          = $this->validTeks(trim($_POST['diagnosa']));
    $procedure         = $this->validTeks(trim($_POST['procedure']));
    $prosedur_non_bedah = $this->validTeks(trim($_POST['prosedur_non_bedah']));
    $prosedur_bedah    = $this->validTeks(trim($_POST['prosedur_bedah']));
    $konsultasi        = $this->validTeks(trim($_POST['konsultasi']));
    $tenaga_ahli       = $this->validTeks(trim($_POST['tenaga_ahli']));
    $keperawatan       = $this->validTeks(trim($_POST['keperawatan']));
    $penunjang         = $this->validTeks(trim($_POST['penunjang']));
    $radiologi         = $this->validTeks(trim($_POST['radiologi']));
    $laboratorium      = $this->validTeks(trim($_POST['laboratorium']));
    $pelayanan_darah   = $this->validTeks(trim($_POST['pelayanan_darah']));
    $rehabilitasi      = $this->validTeks(trim($_POST['rehabilitasi']));
    $kamar             = $this->validTeks(trim($_POST['kamar']));
    $rawat_intensif    = $this->validTeks(trim($_POST['rawat_intensif']));
    $obat              = $this->validTeks(trim($_POST['obat']));
    $obat_kronis       = $this->validTeks(trim($_POST['obat_kronis']));
    $obat_kemoterapi   = $this->validTeks(trim($_POST['obat_kemoterapi']));
    $alkes             = $this->validTeks(trim($_POST['alkes']));
    $bmhp              = $this->validTeks(trim($_POST['bmhp']));
    $sewa_alat         = $this->validTeks(trim($_POST['sewa_alat']));
    $tarif_poli_eks    = $this->validTeks(trim($_POST['tarif_poli_eks']));
    $nama_dokter       = $this->validTeks(trim($_POST['nama_dokter']));
    $jk                = $this->validTeks(trim($_POST['jk']));
    $tgl_lahir         = $this->validTeks(trim($_POST['tgl_lahir']));

    $jnsrawat="2";
    if($this->getRegPeriksaInfo('status_lanjut', $_POST['no_rawat']) == "Ranap"){
        $jnsrawat="1";
    }

    $gender = "";
    if($jk=="L"){
        $gender="1";
    }else{
        $gender="2";
    }


    $this->BuatKlaimBaru2($nokartu,$nosep,$no_rkm_medis,$nm_pasien,$tgl_lahir." 00:00:00", $gender,$norawat);
    $this->EditUlangKlaim($nosep);
    $this->UpdateDataKlaim2($nosep,$nokartu,$tgl_registrasi,$keluar,$jnsrawat,$kelas_rawat,$adl_sub_acute,
        $adl_chronic,$icu_indikator,$icu_los,$ventilator_hour,$upgrade_class_ind,$upgrade_class_class,
        $upgrade_class_los,$add_payment_pct,$birth_weight,$discharge_status,$diagnosa,$procedure,
        $tarif_poli_eks,$nama_dokter,$this->settings->get('vedika.eklaim_kelasrs'),$this->settings->get('vedika.eklaim_payor_id'),$this->settings->get('vedika.eklaim_payor_cd'),$this->settings->get('vedika.eklaim_cob_cd'),$this->core->getPegawaiInfo('no_ktp', $this->core->getUserInfo('username', null, true)),
        $prosedur_non_bedah,$prosedur_bedah,$konsultasi,$tenaga_ahli,$keperawatan,$penunjang,
        $radiologi,$laboratorium,$pelayanan_darah,$rehabilitasi,$kamar,$rawat_intensif,$obat,
        $obat_kronis,$obat_kemoterapi,$alkes,$bmhp,$sewa_alat);

    exit();
  }

  public function postKirimDataCenter()
  {
    $nosep = $_POST['nosep'];
    $this->KirimKlaimIndividualKeDC($nosep);
    $cntr   = 0;
    $imgTime = time() . $cntr++;
    $bridging_sep = $this->core->mysql('bridging_sep')->where('no_sep', $nosep)->oneArray();
    $no_rawat = convertNorawat($bridging_sep['no_rawat']);
    $berkas_digital_perawatan = $this->core->mysql('berkas_digital_perawatan')->where('no_rawat', $bridging_sep['no_rawat'])->where('kode', $this->settings->get('vedika.individual'))->oneArray();
    if(!$berkas_digital_perawatan) {

      $request ='{
                      "metadata": {
                          "method":"claim_print"
                      },
                      "data": {
                          "nomor_sep":"'.$nosep.'"
                      }
                 }';

      $msg = $this->Request($request);
      if($msg['metadata']['message']=="Ok"){
          $pdf = base64_decode($msg['data']);
          file_put_contents(WEBAPPS_PATH.'/berkasrawat/pages/upload/'.$no_rawat.'_'.$imgTime,$pdf);
      } else {
        echo json_encode($msg, true);
      }

      $image = WEBAPPS_PATH.'/berkasrawat/pages/upload/' . $no_rawat . '_' . $imgTime;
      $imagick = new \Imagick();
      $imagick->readImage($image);
      $imagick->writeImages($image.'.jpg', false);

      $query = $this->core->mysql('berkas_digital_perawatan')->save(['no_rawat' => $bridging_sep['no_rawat'], 'kode' => $this->settings->get('vedika.individual'), 'lokasi_file' => 'pages/upload/' . $no_rawat . '_' . $imgTime . '.jpg']);
      if($query) {
        $simpan_status = $this->core->mysql('mlite_vedika')
          ->where('nosep', $nosep)
          ->save([
            'tanggal' => date('Y-m-d'),
            'status' => 'Pengajuan'
          ]);
        if ($simpan_status) {
          $this->core->mysql('mlite_vedika_feedback')->save([
            'id' => NULL,
            'nosep' => $nosep,
            'tanggal' => date('Y-m-d'),
            'catatan' => 'Pengajuan - Kirim ke Data Center',
            'username' => $this->core->getUserInfo('username', null, true)
          ]);
        }

      }
      unlink($image);

    }

    exit();
  }

  public function getKlaimPDF($nosep)
  {
    $request ='{
                    "metadata": {
                        "method":"claim_print"
                    },
                    "data": {
                        "nomor_sep":"'.$nosep.'"
                    }
               }';

    $msg = $this->Request($request);
    if($msg['metadata']['message']=="Ok"){
        // variable data adalah base64 dari file pdf
        $pdf = base64_decode($msg['data']);
        // atau untuk ditampilkan dengan perintah:
        header("Content-type:application/pdf");
        ob_clean();
        flush();
        echo $pdf;
    }

    exit();
  }

  private function Request($request){
      $json = $this->mc_encrypt ($request, $this->settings->get('vedika.eklaim_key'));
      $header = array("Content-Type: application/x-www-form-urlencoded");
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $this->settings->get('vedika.eklaim_url'));
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
      $response = curl_exec($ch);
      $first = strpos($response, "\n")+1;
      $last = strrpos($response, "\n")-1;
      $hasilresponse = substr($response,$first,strlen($response) - $first - $last);
      $hasildecrypt = $this->mc_decrypt($hasilresponse, $this->settings->get('vedika.eklaim_key'));
      //echo $hasildecrypt;
      $msg = json_decode($hasildecrypt,true);
      return $msg;
  }

  private function mc_encrypt($data, $strkey) {
      $key = hex2bin($strkey);
      if (mb_strlen($key, "8bit") !== 32) {
              throw new Exception("Needs a 256-bit key!");
      }

      $iv_size = openssl_cipher_iv_length("aes-256-cbc");
      $iv = openssl_random_pseudo_bytes($iv_size);
      $encrypted = openssl_encrypt($data,"aes-256-cbc",$key,OPENSSL_RAW_DATA,$iv );
      $signature = mb_substr(hash_hmac("sha256",$encrypted,$key,true),0,10,"8bit");
      $encoded = chunk_split(base64_encode($signature.$iv.$encrypted));
      return $encoded;
  }

  private function mc_decrypt($str, $strkey){
      $key = hex2bin($strkey);
      if (mb_strlen($key, "8bit") !== 32) {
          throw new Exception("Needs a 256-bit key!");
      }

      $iv_size = openssl_cipher_iv_length("aes-256-cbc");
      $decoded = base64_decode($str);
      $signature = mb_substr($decoded,0,10,"8bit");
      $iv = mb_substr($decoded,10,$iv_size,"8bit");
      $encrypted = mb_substr($decoded,$iv_size+10,NULL,"8bit");
      $calc_signature = mb_substr(hash_hmac("sha256",$encrypted,$key,true),0,10,"8bit");
      if(!$this->mc_compare($signature,$calc_signature)) {
          return "SIGNATURE_NOT_MATCH";
      }

      $decrypted = openssl_decrypt($encrypted,"aes-256-cbc",$key,OPENSSL_RAW_DATA,$iv);
      return $decrypted;
  }

  private function mc_compare($a, $b) {
      if (strlen($a) !== strlen($b)) {
          return false;
      }

      $result = 0;

      for($i = 0; $i < strlen($a); $i ++) {
          $result |= ord($a[$i]) ^ ord($b[$i]);
      }

      return $result == 0;
  }

  private function validTeks($data){
      $save=str_replace("'","",$data);
      $save=str_replace("\\","",$save);
      $save=str_replace(";","",$save);
      $save=str_replace("`","",$save);
      $save=str_replace("--","",$save);
      $save=str_replace("/*","",$save);
      $save=str_replace("*/","",$save);
      //$save=str_replace("#","",$save);
      return $save;
  }

  private function Grouper($nomor_sep,$coder_nik){
      $request ='{
                      "metadata": {
                          "method":"grouper",
                          "stage":"1"
                      },
                      "data": {
                          "nomor_sep":"'.$nomor_sep.'"
                      }
                 }';
      $msg= $this->Request($request);
      if($msg['metadata']['message']=="Ok"){
          if($msg['response']['cbg']['tariff'] == '') {
            $tarif = '0';
          } else {
            $tarif = $msg['response']['cbg']['tariff'];
          }
          echo '<dt>Grouper</dt> <dd>'.$msg['response']['cbg']['code'].'</dd><br>';
          echo '<dt>Deskripsi</dt> <dd>'.$msg['response']['cbg']['description'].'</dd><br>';
          echo '<dt>Tarif INACBG\'s</dt> <dd>Rp. '.number_format($tarif,0,",",".").'</dd><br><br>';
      }
  }

  private function BuatKlaimBaru2($nomor_kartu,$nomor_sep,$nomor_rm,$nama_pasien,$tgl_lahir,$gender,$norawat){
      $request ='{
                      "metadata":{
                          "method":"new_claim"
                      },
                      "data":{
                          "nomor_kartu":"'.$nomor_kartu.'",
                          "nomor_sep":"'.$nomor_sep.'",
                          "nomor_rm":"'.$nomor_rm.'",
                          "nama_pasien":"'.$nama_pasien.'",
                          "tgl_lahir":"'.$tgl_lahir.'",
                          "gender":"'.$gender.'"
                      }
                  }';
      $msg= $this->Request($request);
      if($msg['metadata']['message']=="Ok"){
          //InsertData2("inacbg_klaim_baru2","'".$norawat."','".$nomor_sep."','".$msg['response']['patient_id']."','".$msg['response']['admission_id']."','".$msg['response']['hospital_admission_id']."'");
      }
      return $msg['metadata']['message'];
  }

  private function EditUlangKlaim($nomor_sep){
      $request ='{
                      "metadata": {
                          "method":"reedit_claim"
                      },
                      "data": {
                          "nomor_sep":"'.$nomor_sep.'"
                      }
                 }';
      $msg= $this->Request($request);
      //echo $msg['metadata']['message']."";
  }

  private function UpdateDataKlaim2($nomor_sep,$nomor_kartu,$tgl_masuk,$tgl_pulang,$jenis_rawat,$kelas_rawat,$adl_sub_acute,
                          $adl_chronic,$icu_indikator,$icu_los,$ventilator_hour,$upgrade_class_ind,$upgrade_class_class,
                          $upgrade_class_los,$add_payment_pct,$birth_weight,$discharge_status,$diagnosa,$procedure,
                          $tarif_poli_eks,$nama_dokter,$kode_tarif,$payor_id,$payor_cd,$cob_cd,$coder_nik,
                          $prosedur_non_bedah,$prosedur_bedah,$konsultasi,$tenaga_ahli,$keperawatan,$penunjang,
                          $radiologi,$laboratorium,$pelayanan_darah,$rehabilitasi,$kamar,$rawat_intensif,$obat,
                          $obat_kronis,$obat_kemoterapi,$alkes,$bmhp,$sewa_alat){
      $request ='{
                      "metadata": {
                          "method": "set_claim_data",
                          "nomor_sep": "'.$nomor_sep.'"
                      },
                      "data": {
                          "nomor_sep": "'.$nomor_sep.'",
                          "nomor_kartu": "'.$nomor_kartu.'",
                          "tgl_masuk": "'.$tgl_masuk.' 00:00:01",
                          "tgl_pulang": "'.$tgl_pulang.' 23:59:59",
                          "jenis_rawat": "'.$jenis_rawat.'",
                          "kelas_rawat": "'.$kelas_rawat.'",
                          "adl_sub_acute": "'.$adl_sub_acute.'",
                          "adl_chronic": "'.$adl_chronic.'",
                          "icu_indikator": "'.$icu_indikator.'",
                          "icu_los": "'.$icu_los.'",
                          "ventilator_hour": "'.$ventilator_hour.'",
                          "upgrade_class_ind": "'.$upgrade_class_ind.'",
                          "upgrade_class_class": "'.$upgrade_class_class.'",
                          "upgrade_class_los": "'.$upgrade_class_los.'",
                          "add_payment_pct": "'.$add_payment_pct.'",
                          "birth_weight": "'.$birth_weight.'",
                          "discharge_status": "'.$discharge_status.'",
                          "diagnosa": "'.$diagnosa.'",
                          "procedure": "'.$procedure.'",
                          "diagnosa_inagrouper": "'.$diagnosa.'",
                          "procedure_inagrouper": "'.$procedure.'",
                          "tarif_rs": {
                              "prosedur_non_bedah": "'.$prosedur_non_bedah.'",
                              "prosedur_bedah": "'.$prosedur_bedah.'",
                              "konsultasi": "'.$konsultasi.'",
                              "tenaga_ahli": "'.$tenaga_ahli.'",
                              "keperawatan": "'.$keperawatan.'",
                              "penunjang": "'.$penunjang.'",
                              "radiologi": "'.$radiologi.'",
                              "laboratorium": "'.$laboratorium.'",
                              "pelayanan_darah": "'.$pelayanan_darah.'",
                              "rehabilitasi": "'.$rehabilitasi.'",
                              "kamar": "'.$kamar.'",
                              "rawat_intensif": "'.$rawat_intensif.'",
                              "obat": "'.$obat.'",
                              "obat_kronis": "'.$obat_kronis.'",
                              "obat_kemoterapi": "'.$obat_kemoterapi.'",
                              "alkes": "'.$alkes.'",
                              "bmhp": "'.$bmhp.'",
                              "sewa_alat": "'.$sewa_alat.'"
                           },
                          "tarif_poli_eks": "'.$tarif_poli_eks.'",
                          "nama_dokter": "'.$nama_dokter.'",
                          "kode_tarif": "'.$kode_tarif.'",
                          "payor_id": "'.$payor_id.'",
                          "payor_cd": "'.$payor_cd.'",
                          "cob_cd": "'.$cob_cd.'",
                          "coder_nik": "'.$coder_nik.'"
                      }
                 }';
      echo "Data : ".$request;
      $msg= $this->Request($request);
      if($msg['metadata']['message']=="Ok"){
          //echo 'Sukses';
          //Hapus2("inacbg_data_terkirim2", "no_sep='".$nomor_sep."'");
          //InsertData2("inacbg_data_terkirim2","'".$nomor_sep."','".$coder_nik."'");
          $this->GroupingStage12($nomor_sep,$coder_nik);
      } else {
        echo json_encode($msg);
      }
  }

  private function GroupingStage12($nomor_sep,$coder_nik){
      $request ='{
                      "metadata": {
                          "method":"grouper",
                          "stage":"1"
                      },
                      "data": {
                          "nomor_sep":"'.$nomor_sep.'"
                      }
                 }';
      $msg= $this->Request($request);
      if($msg['metadata']['message']=="Ok"){
          //Hapus2("inacbg_grouping_stage12", "no_sep='".$nomor_sep."'");
          /*
          $cbg                = validangka($msg['response']['cbg']['tariff']);
          $sub_acute          = validangka($msg['response']['sub_acute']['tariff']);
          $chronic            = validangka($msg['response']['chronic']['tariff']);
          $add_payment_amt    = validangka($msg['response']['add_payment_amt']);
          */
          //InsertData2("inacbg_grouping_stage12","'".$nomor_sep."','".$msg['response']['cbg']['code']."','".$msg['response']['cbg']['description']."','".($cbg+$sub_acute+$chronic+$add_payment_amt)."'");
          $this->FinalisasiKlaim($nomor_sep,$coder_nik);
      }
  }

  private function FinalisasiKlaim($nomor_sep,$coder_nik){
      $request ='{
                      "metadata": {
                          "method":"claim_final"
                      },
                      "data": {
                          "nomor_sep":"'.$nomor_sep.'",
                          "coder_nik": "'.$coder_nik.'"
                      }
                 }';
      $msg= $this->Request($request);
      if($msg['metadata']['message']=="Ok"){
          //KirimKlaimIndividualKeDC($nomor_sep);
      }
  }

  private function KirimKlaimIndividualKeDC($nomor_sep){
      $request ='{
                      "metadata": {
                          "method":"send_claim_individual"
                      },
                      "data": {
                          "nomor_sep":"'.$nomor_sep.'"
                      }
                 }';
      $msg= $this->Request($request);
      echo $msg['metadata']['message']."";
  }

  public function anySavePrioritas()
  {
    $this->core->mysql('diagnosa_pasien')
      ->where('no_rawat', $_REQUEST['no_rawat'])
      ->where('kd_penyakit', $_REQUEST['kd_penyakit'])
      ->where('status', $_REQUEST['status'])
      ->save([
        'prioritas' => $_REQUEST['prioritas']
      ]);

    exit();
  }

  public function getJavascript()
  {
    header('Content-type: text/javascript');
    echo $this->draw(MODULES . '/vedika/js/admin/scripts.js');
    exit();
  }

  public function getCss()
  {
    header('Content-type: text/css');
    echo $this->draw(MODULES . '/vedika/css/admin/styles.css');
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
