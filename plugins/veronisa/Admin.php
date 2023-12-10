<?php
namespace Plugins\Veronisa;

use Systems\AdminModule;
use Systems\Lib\BpjsService;
use Systems\Lib\QRCode;

class Admin extends AdminModule
{

  private $_uploads = WEBAPPS_PATH . '/berkasrawat/pages/upload';

  protected $consid;
  protected $secretkey;
  protected $user_key;
  protected $api_url;
  protected $assign;

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
      'Pengaturan' => 'settings',
    ];
  }

  public function getManage()
  {
    $sub_modules = [
      ['name' => 'Index', 'url' => url([ADMIN, 'veronisa', 'index']), 'icon' => 'code', 'desc' => 'Index veronisa'],
      ['name' => 'Pengaturan', 'url' => url([ADMIN, 'veronisa', 'settings']), 'icon' => 'code', 'desc' => 'Pengaturan veronisa']
    ];
    return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
  }

  public function anyIndex($page = 1)
  {
    if (isset($_POST['submit'])) {
      if (!$this->db('mlite_veronisa')->where('nosep', $_POST['nosep'])->oneArray()) {
        $simpan_status = $this->db('mlite_veronisa')->save([
          'id' => NULL,
          'tanggal' => date('Y-m-d'),
          'no_rkm_medis' => $_POST['no_rkm_medis'],
          'no_rawat' => $_POST['no_rawat'],
          'tgl_registrasi' => $_POST['tgl_registrasi'],
          'nosep' => $_POST['nosep'],
          'username' => $this->core->getUserInfo('username', null, true)
        ]);
      } else {
        $simpan_status = $this->db('mlite_veronisa')
          ->where('nosep', $_POST['nosep'])
          ->save([
            'tanggal' => date('Y-m-d'),
            'status' => $_POST['status']
          ]);
      }
      if ($simpan_status) {
        $this->db('mlite_veronisa_feedback')->save([
          'id' => NULL,
          'nosep' => $_POST['nosep'],
          'tanggal' => date('Y-m-d'),
          'catatan' => $_POST['catatan'],
          'username' => $this->core->getUserInfo('username', null, true)
        ]);
      }

    }

    if (isset($_POST['simpanberkas'])) {

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
        $json = json_decode($response, true);
        if($json['status'] == 'Success') {
          echo '<br><img src="'.WEBAPPS_URL.'/berkasrawat/'.$json['msg'].'" width="150" />';
        } else {
          echo 'Gagal menambahkan gambar';
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
          $query = $this->db('berkas_digital_perawatan')->save(['no_rawat' => $_POST['no_rawat'], 'kode' => $_POST['kode'], 'lokasi_file' => $lokasi_file]);
          if ($query) {
            $this->notify('success', 'Simpan berkas digital perawatan sukses.');
          }
        }
      }
    }

    //DELETE BERKAS DIGITAL PERAWATAN
    if (isset($_POST['deleteberkas'])) {
      if ($berkasPerawatan = $this->db('berkas_digital_perawatan')
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
          $query = $this->db('berkas_digital_perawatan')->where('no_rawat', $no_rawat_file)->where('lokasi_file', $lokasi_file)->delete();

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
    $totalRecords = $this->db()->pdo()->prepare("SELECT reg_periksa.no_rawat FROM reg_periksa, pasien, mlite_veronisa WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.no_rawat = mlite_veronisa.no_rawat AND (reg_periksa.no_rkm_medis LIKE ? OR reg_periksa.no_rawat LIKE ? OR pasien.nm_pasien LIKE ?) AND reg_periksa.tgl_registrasi BETWEEN '$start_date' AND '$end_date' AND reg_periksa.status_lanjut = 'Ralan'");
    $totalRecords->execute(['%' . $phrase . '%', '%' . $phrase . '%', '%' . $phrase . '%']);
    $totalRecords = $totalRecords->fetchAll();

    $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url([ADMIN, 'veronisa', 'index', '%d?s=' . $phrase . '&start_date=' . $start_date . '&end_date=' . $end_date]));
    $this->assign['pagination'] = $pagination->nav('pagination', '5');
    $this->assign['totalRecords'] = $totalRecords;

    $offset = $pagination->offset();
    $query = $this->db()->pdo()->prepare("SELECT reg_periksa.*, pasien.*, dokter.nm_dokter, poliklinik.nm_poli, mlite_veronisa.no_rawat, mlite_veronisa.nosep FROM reg_periksa, pasien, dokter, poliklinik, mlite_veronisa WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.kd_dokter = dokter.kd_dokter AND reg_periksa.kd_poli = poliklinik.kd_poli AND reg_periksa.no_rawat = mlite_veronisa.no_rawat AND (reg_periksa.no_rkm_medis LIKE ? OR reg_periksa.no_rawat LIKE ? OR pasien.nm_pasien LIKE ?) AND reg_periksa.tgl_registrasi BETWEEN '$start_date' AND '$end_date' AND reg_periksa.status_lanjut = 'Ralan' LIMIT $perpage OFFSET $offset");
    $query->execute(['%' . $phrase . '%', '%' . $phrase . '%', '%' . $phrase . '%']);
    $rows = $query->fetchAll();

    $this->assign['list'] = [];
    if (count($rows)) {
      foreach ($rows as $row) {
        $berkas_digital = $this->db('berkas_digital_perawatan')
          ->join('master_berkas_digital', 'master_berkas_digital.kode=berkas_digital_perawatan.kode')
          ->where('berkas_digital_perawatan.no_rawat', $row['no_rawat'])
          ->asc('master_berkas_digital.nama')
          ->toArray();

        $row = htmlspecialchars_array($row);
        $row['pdfURL'] = url([ADMIN, 'veronisa', 'pdf', $this->convertNorawat($row['no_rawat'])]);
        $row['batalURL'] = url([ADMIN, 'veronisa', 'batal', $this->convertNorawat($row['no_rawat'])]);
        $row['berkas_digital'] = $berkas_digital;
        $row['formSepURL'] = url([ADMIN, 'veronisa', 'formsepvclaim', '?no_rawat=' . $row['no_rawat']]);
        $row['setstatusURL']  = url([ADMIN, 'veronisa', 'setstatus', $this->_getSEPInfo('no_sep', $row['no_rawat'])]);
        $row['status_pengajuan'] = $this->db('mlite_veronisa')->where('nosep', $this->_getSEPInfo('no_sep', $row['no_rawat']))->desc('id')->limit(1)->toArray();
        $row['berkasPasien'] = url([ADMIN, 'veronisa', 'berkaspasien', $this->core->getRegPeriksaInfo('no_rkm_medis', $row['no_rawat'])]);
        $row['berkasPerawatan'] = url([ADMIN, 'veronisa', 'berkasperawatan', $this->convertNorawat($row['no_rawat'])]);
        $this->assign['list'][] = $row;
      }
    }

    $this->core->addCSS(url('assets/jscripts/lightbox/lightbox.min.css'));
    $this->core->addJS(url('assets/jscripts/lightbox/lightbox.min.js'));

    $this->assign['searchUrl'] =  url([ADMIN, 'veronisa', 'index', $page . '?s=' . $phrase . '&start_date=' . $start_date . '&end_date=' . $end_date]);
    return $this->draw('index.html', ['veronisa' => $this->assign]);
  }

  public function getFormSEPVClaim()
  {
    $this->tpl->set('poliklinik', $this->db('poliklinik')->where('status', '1')->toArray());
    $this->tpl->set('dokter', $this->db('dokter')->where('status', '1')->toArray());
    echo $this->tpl->draw(MODULES . '/veronisa/view/admin/form.sepvclaim.html', true);
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

    if ($data['metaData']['code'] == 200) {
      $insert = $this->db('bridging_sep')->save([
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
        'klsrawat' =>  $data['response']['klsRawat']['klsRawatHak'],
        'klsnaik' => $data['response']['klsRawat']['klsRawatNaik'] == null ? "" : $data['response']['klsRawat']['klsRawatNaik'],
        'pembiayaan' => $data['response']['klsRawat']['pembiayaan']  == null ? "" : $data['response']['klsRawat']['pembiayaan'],
        'pjnaikkelas' => $data['response']['klsRawat']['penanggungJawab']  == null ? "" : $data['response']['klsRawat']['penanggungJawab'],
        'lakalantas' => '0',
        'user' => $this->core->getUserInfo('username', null, true),
        'nomr' => $this->core->getRegPeriksaInfo('no_rkm_medis', $_POST['no_rawat']),
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
        'kddpjp' => $this->db('maping_dokter_dpjpvclaim')->where('kd_dokter', $_POST['kd_dokter'])->oneArray()['kd_dokter_bpjs'],
        'nmdpdjp' => $this->db('maping_dokter_dpjpvclaim')->where('kd_dokter', $_POST['kd_dokter'])->oneArray()['nm_dokter_bpjs'],
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
      $this->db('bpjs_prb')->save(['no_sep' => $data['response']['noSep'], 'prb' => $data_rujukan['response']['rujukan']['peserta']['informasi']['prolanisPRB']]);
      $this->notify('success', 'Simpan sukes');
    } else {
      $this->notify('failure', 'Simpan gagal');
    }
  }

  public function getBatal($id)
  {
    $delete = $this->db('mlite_veronisa')->where('no_rawat', $this->revertNorawat($id))->delete();
    redirect(url([ADMIN, 'veronisa', 'index']));
  }

  public function getPDF($id)
  {
    $berkas_digital = $this->db('berkas_digital_perawatan')
      ->join('master_berkas_digital', 'master_berkas_digital.kode=berkas_digital_perawatan.kode')
      ->where('berkas_digital_perawatan.no_rawat', $this->revertNorawat($id))
      ->asc('master_berkas_digital.nama')
      ->toArray();

    $no_rawat = $this->revertNorawat($id);

    /** Billing versi mlite */

    $billing_mlite_settings = $this->settings('settings');
    $this->tpl->set('billing_mlite_settings', $this->tpl->noParse_array(htmlspecialchars_array($billing_mlite_settings)));
    $billing_mlite_reg_periksa = $this->db('reg_periksa')->where('no_rawat', $no_rawat)->oneArray();
    $billing_mlite_pasien = $this->db('pasien')->where('no_rkm_medis', $billing_mlite_reg_periksa['no_rkm_medis'])->oneArray();

    $billing_result = $this->db('mlite_billing')->where('no_rawat', $no_rawat)->like('kd_billing', 'RJ%')->desc('id_billing')->oneArray();

    $result_detail['poliklinik'] = $this->db('poliklinik')
      ->join('reg_periksa', 'reg_periksa.kd_poli = poliklinik.kd_poli')
      ->where('reg_periksa.no_rawat', $no_rawat)
      ->oneArray();

    $result_detail['rawat_jl_dr'] = $this->db('rawat_jl_dr')
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

    $result_detail['rawat_jl_pr'] = $this->db('rawat_jl_pr')
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

    $result_detail['rawat_jl_drpr'] = $this->db('rawat_jl_drpr')
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

    $result_detail['detail_pemberian_obat'] = $this->db('detail_pemberian_obat')
      ->join('databarang', 'databarang.kode_brng=detail_pemberian_obat.kode_brng')
      ->where('no_rawat', $no_rawat)
      ->where('detail_pemberian_obat.status', 'Ralan')
      ->toArray();

    $total_detail_pemberian_obat = 0;
    foreach ($result_detail['detail_pemberian_obat'] as $row) {
      $total_detail_pemberian_obat += $row['total'];
    }

    $result_detail['periksa_lab'] = $this->db('periksa_lab')
      ->join('jns_perawatan_lab', 'jns_perawatan_lab.kd_jenis_prw=periksa_lab.kd_jenis_prw')
      ->where('no_rawat', $no_rawat)
      ->where('periksa_lab.status', 'Ralan')
      ->toArray();

    $total_periksa_lab = 0;
    foreach ($result_detail['periksa_lab'] as $row) {
      $total_periksa_lab += $row['biaya'];
    }

    $result_detail['periksa_radiologi'] = $this->db('periksa_radiologi')
      ->join('jns_perawatan_radiologi', 'jns_perawatan_radiologi.kd_jenis_prw=periksa_radiologi.kd_jenis_prw')
      ->where('no_rawat', $no_rawat)
      ->where('periksa_radiologi.status', 'Ralan')
      ->toArray();

    $total_periksa_radiologi = 0;
    foreach ($result_detail['periksa_radiologi'] as $row) {
      $total_periksa_radiologi += $row['biaya'];
    }

    $jumlah_total_operasi = 0;
    $operasis = $this->db('operasi')->join('paket_operasi', 'paket_operasi.kode_paket=operasi.kode_paket')->where('no_rawat', $no_rawat)->where('operasi.status', 'Ralan')->toArray();
    $result_detail['operasi'] = [];
    foreach ($operasis as $operasi) {
      $operasi['jumlah'] = $operasi['biayaoperator1']+$operasi['biayaoperator2']+$operasi['biayaoperator3']+$operasi['biayaasisten_operator1']+$operasi['biayaasisten_operator2']+$operasi['biayadokter_anak']+$operasi['biayaperawaat_resusitas']+$operasi['biayadokter_anestesi']+$operasi['biayaasisten_anestesi']+$operasi['biayabidan']+$operasi['biayaperawat_luar'];
      $jumlah_total_operasi += $operasi['jumlah'];
      $result_detail['operasi'][] = $operasi;
    }
    $jumlah_total_obat_operasi = 0;
    $obat_operasis = $this->db('beri_obat_operasi')->join('obatbhp_ok', 'obatbhp_ok.kd_obat=beri_obat_operasi.kd_obat')->where('no_rawat', $no_rawat)->toArray();
    $result_detail['obat_operasi'] = [];
    foreach ($obat_operasis as $obat_operasi) {
      $obat_operasi['harga'] = $obat_operasi['hargasatuan'] * $obat_operasi['jumlah'];
      $jumlah_total_obat_operasi += $obat_operasi['harga'];
      $result_detail['obat_operasi'][] = $obat_operasi;
    }

    $qr=QRCode::getMinimumQRCode($this->core->getUserInfo('fullname', null, true),QR_ERROR_CORRECT_LEVEL_L);
    //$qr=QRCode::getMinimumQRCode('Petugas: '.$this->core->getUserInfo('fullname', null, true).'; Lokasi: '.UPLOADS.'/invoices/'.$result['kd_billing'].'.pdf',QR_ERROR_CORRECT_LEVEL_L);
    $im=$qr->createImage(4,4);
    imagepng($im,BASE_DIR.'/'.ADMIN.'/tmp/qrcode.png');
    imagedestroy($im);

    $image = BASE_DIR."/".ADMIN."/tmp/qrcode.png";
    $qrCode = "../../../".ADMIN."/tmp/qrcode.png";

    $this->tpl->set('billing_mlite_detail', $result_detail);
    $this->tpl->set('billing_mlite', $billing_result);
    $this->tpl->set('billing_mlite_qrcode', $qrCode);
    $this->tpl->set('billing_mlite_kasir', $this->core->getUserInfo('fullname', null, true));
    $this->tpl->set('billing_mlite_pasien', $billing_mlite_pasien);
    $this->tpl->set('billing_mlite_veronisa', htmlspecialchars_array($this->settings('veronisa')));

    /** End billing versi mlite **/

    $instansi['logo'] = $this->settings->get('settings.logo');
    $instansi['nama_instansi'] = $this->settings->get('settings.nama_instansi');
    $instansi['alamat'] = $this->settings->get('settings.alamat');
    $instansi['kota'] = $this->settings->get('settings.kota');
    $instansi['propinsi'] = $this->settings->get('settings.propinsi');
    $instansi['nomor_telepon'] = $this->settings->get('settings.nomor_telepon');
    $instansi['email'] = $this->settings->get('settings.email');

    $this->tpl->set('instansi', $instansi);

    $print_sep = array();
    if (!empty($this->_getSEPInfo('no_sep', $no_rawat))) {
      $print_sep['bridging_sep'] = $this->db('bridging_sep')->where('no_sep', $this->_getSEPInfo('no_sep', $no_rawat))->oneArray();
      $print_sep['bpjs_prb'] = $this->db('bpjs_prb')->where('no_sep', $this->_getSEPInfo('no_sep', $no_rawat))->oneArray();
      $batas_rujukan = $this->db('bridging_sep')->select('DATE_ADD(tglrujukan , INTERVAL 85 DAY) AS batas_rujukan')->where('no_sep', $id)->oneArray();
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

    /*
    $resume_pasien = $this->db('resume_pasien')
      ->join('dokter', 'dokter.kd_dokter = resume_pasien.kd_dokter')
      ->where('no_rawat', $this->revertNorawat($id))
      ->oneArray();
    $this->tpl->set('resume_pasien', $resume_pasien);
    */

    $pasien = $this->db('pasien')
      ->join('kecamatan', 'kecamatan.kd_kec = pasien.kd_kec')
      ->join('kabupaten', 'kabupaten.kd_kab = pasien.kd_kab')
      ->where('no_rkm_medis', $this->core->getRegPeriksaInfo('no_rkm_medis', $this->revertNorawat($id)))
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
    /*
    $rujukan_internal = $this->db('rujukan_internal_poli')
      ->join('poliklinik', 'poliklinik.kd_poli = rujukan_internal_poli.kd_poli')
      ->join('dokter', 'dokter.kd_dokter = rujukan_internal_poli.kd_dokter')
      ->where('no_rawat', $this->revertNorawat($id))
      ->oneArray();
    */
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
    $riwayat_obat = [];
    $list_riwayat = $this->db('reg_periksa')
    ->where('no_rkm_medis',$this->core->getRegPeriksaInfo('no_rkm_medis', $this->revertNorawat($id)))
    ->toArray();
    foreach($list_riwayat as $list_riw){
      $beri_obat = $this->db('detail_pemberian_obat')
        ->join('databarang', 'detail_pemberian_obat.kode_brng=databarang.kode_brng')
        ->where('no_rawat', $list_riw['no_rawat'])
        ->toArray();
        foreach($beri_obat as $row){
          $row['resep_obat_ku'] = $this->db('aturan_pakai')
          ->where('aturan_pakai.no_rawat',$row['no_rawat'])
          ->where('aturan_pakai.kode_brng',$row['kode_brng'])
          ->oneArray();
          $riwayat_obat[] = $row;
        }
    }
    $obat_operasi = $this->db('beri_obat_operasi')
      ->join('obatbhp_ok', 'beri_obat_operasi.kd_obat=obatbhp_ok.kd_obat')
      ->where('no_rawat', $this->revertNorawat($id))
      ->toArray();
    /*
    $resep_pulang = $this->db('resep_pulang')
      ->join('databarang', 'resep_pulang.kode_brng=databarang.kode_brng')
      ->where('no_rawat', $this->revertNorawat($id))
      ->toArray();
    */
    $laporan_operasi = $this->db('laporan_operasi')
      ->where('no_rawat', $this->revertNorawat($id))
      ->oneArray();

    $this->tpl->set('pasien', $pasien);
    $this->tpl->set('reg_periksa', $reg_periksa);
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
    $this->tpl->set('laporan_operasi', $laporan_operasi);
    $this->tpl->set('riwayat_obat', $riwayat_obat);

    $this->tpl->set('berkas_digital', $berkas_digital);
    $this->tpl->set('hasil_radiologi', $this->db('hasil_radiologi')->where('no_rawat', $this->revertNorawat($id))->toArray());
    $this->tpl->set('gambar_radiologi', $this->db('gambar_radiologi')->where('no_rawat', $this->revertNorawat($id))->toArray());
    $this->tpl->set('veronisa', htmlspecialchars_array($this->settings('veronisa')));
    echo $this->tpl->draw(MODULES . '/veronisa/view/admin/pdf.html', true);
    exit();
  }

  public function getSetStatus($id)
  {
    $set_status = $this->db('bridging_sep')->where('no_sep', $id)->oneArray();
    $veronisa = $this->db('mlite_veronisa')->join('mlite_veronisa_feedback','mlite_veronisa_feedback.nosep=mlite_veronisa.nosep')->where('status','<>','')->where('mlite_veronisa.nosep', $id)->asc('mlite_veronisa.id')->toArray();
    $this->tpl->set('logo', $this->settings->get('settings.logo'));
    $this->tpl->set('nama_instansi', $this->settings->get('settings.nama_instansi'));
    $this->tpl->set('set_status', $set_status);
    $this->tpl->set('veronisa', $veronisa);
    echo $this->tpl->draw(MODULES . '/veronisa/view/admin/setstatus.html', true);
    exit();
  }

  public function getBerkasPasien()
  {
    echo $this->tpl->draw(MODULES . '/veronisa/view/admin/berkaspasien.html', true);
    exit();
  }

  public function anyBerkasPerawatan($no_rawat)
  {
    $row_berkasdig = $this->db('berkas_digital_perawatan')
      ->join('master_berkas_digital', 'master_berkas_digital.kode=berkas_digital_perawatan.kode')
      ->where('berkas_digital_perawatan.kode', $this->settings->get('veronisa.obat_kronis'))
      ->where('berkas_digital_perawatan.no_rawat', revertNorawat($no_rawat))
      ->toArray();

    $this->assign['master_berkas_digital'] = $this->db('master_berkas_digital')->toArray();
    $this->assign['berkas_digital'] = $row_berkasdig;

    $this->assign['no_rawat'] = revertNorawat($no_rawat);
    $this->assign['user_role'] = $this->core->getUserInfo('role');
    $this->tpl->set('berkasperawatan', $this->assign);

    echo $this->tpl->draw(MODULES . '/veronisa/view/admin/berkasperawatan.html', true);
    exit();
  }

  private function _getSEPInfo($field, $no_rawat)
  {
      $row = $this->db('bridging_sep')->where('no_rawat', $no_rawat)->oneArray();
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

  public function getSettings()
  {
    $this->_addHeaderFiles();
    $this->assign['title'] = 'Pengaturan Modul veronisa';
    $this->assign['veronisa'] = htmlspecialchars_array($this->settings('veronisa'));
    $this->assign['master_berkas_digital'] = $this->db('master_berkas_digital')->toArray();
    return $this->draw('settings.html', ['settings' => $this->assign]);
  }

  public function postSaveSettings()
  {
    foreach ($_POST['veronisa'] as $key => $val) {
      $this->settings('veronisa', $key, $val);
    }
    $this->notify('success', 'Pengaturan telah disimpan');
    redirect(url([ADMIN, 'veronisa', 'settings']));
  }

  public function getJavascript()
  {
    header('Content-type: text/javascript');
    echo $this->draw(MODULES . '/veronisa/js/admin/scripts.js');
    exit();
  }

  public function getCss()
  {
    header('Content-type: text/css');
    echo $this->draw(MODULES . '/veronisa/css/admin/styles.css');
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
    $this->core->addCSS(url([ADMIN, 'veronisa', 'css']));
    $this->core->addJS(url([ADMIN, 'veronisa', 'javascript']), 'footer');
  }

}
