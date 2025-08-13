<?php
namespace Plugins\Veronisa;

use Systems\AdminModule;
use Systems\Lib\BpjsService;
use Systems\Lib\QRCode;
use LZCompressor\LZString;

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
    $this->consid = $this->settings->get('veronisa.cons_id');
    $this->secretkey = $this->settings->get('veronisa.secret_key');
    $this->user_key = $this->settings->get('veronisa.user_key');
    $this->api_url = $this->settings->get('veronisa.bpjs_api_url');
  }

  public function navigation()
  {
    return [
      'Manage' => 'manage',
      'Index' => 'index',
      'Apotek Online' => 'apotekonline',
      'Log Apotek Online' => 'logapotikonline',
      'Mapping Obat' => 'mappingobat',
      'Monitoring Data Klaim' => 'monitoringdataklaim',
      'Pengaturan' => 'settings',
    ];
  }

  public function getManage()
  {
    $sub_modules = [
      ['name' => 'Index', 'url' => url([ADMIN, 'veronisa', 'index']), 'icon' => 'list', 'desc' => 'Index veronisa'],
      ['name' => 'Apotek Online', 'url' => url([ADMIN, 'veronisa', 'apotekonline']), 'icon' => 'medkit', 'desc' => 'Apotek Online veronisa'],
      ['name' => 'Log Apotek Online', 'url' => url([ADMIN, 'veronisa', 'logapotikonline']), 'icon' => 'file-text', 'desc' => 'Log Pengiriman Apotek Online'],
      ['name' => 'Mapping Obat', 'url' => url([ADMIN, 'veronisa', 'mappingobat']), 'icon' => 'exchange', 'desc' => 'Mapping Obat veronisa'],
      ['name' => 'Monitoring Data Klaim', 'url' => url([ADMIN, 'veronisa', 'monitoringdataklaim']), 'icon' => 'bar-chart', 'desc' => 'Monitoring Data Klaim veronisa'],
      ['name' => 'Pengaturan', 'url' => url([ADMIN, 'veronisa', 'settings']), 'icon' => 'cog', 'desc' => 'Pengaturan veronisa']
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
        $row['setstatusURL']  = url([ADMIN, 'veronisa', 'setstatus', $this->convertNorawat($row['no_rawat'])]);
        $row['status_pengajuan'] = $this->db('mlite_veronisa')->where('no_rawat', $row['no_rawat'])->desc('id')->limit(1)->toArray();
        $row['berkasPasien'] = url([ADMIN, 'veronisa', 'berkaspasien', $this->core->getRegPeriksaInfo('no_rkm_medis', $row['no_rawat'])]);
        $row['berkasPerawatan'] = url([ADMIN, 'veronisa', 'berkasperawatan', $this->convertNorawat($row['no_rawat'])]);
        $bridging = $this->db('bridging_sep')->where('no_rawat', $row['no_rawat'])->oneArray();
        $row['bridgeStatus'] = isset_or($bridging['no_sep'], '');
        
        // Cek apakah data sudah ada di tabel mlite_apotek_online_resep_response_log
        $resep_response_exists = $this->db('mlite_apotek_online_resep_response_log')
          ->where('no_rawat', $row['no_rawat'])
          ->oneArray();
        $row['resep_response_exists'] = !empty($resep_response_exists);
        $this->assign['list'][] = $row;
      }
    }

    $this->core->addCSS(url('assets/jscripts/lightbox/lightbox.min.css'));
    $this->core->addJS(url('assets/jscripts/lightbox/lightbox.min.js'));

    $this->assign['searchUrl'] =  url([ADMIN, 'veronisa', 'index', $page . '?s=' . $phrase . '&start_date=' . $start_date . '&end_date=' . $end_date]);
    return $this->draw('index.html', ['veronisa' => $this->assign]);
  }

  public function postHapusVeronisa()
  {
    if (isset($_POST['no_rawat'])) {
       $no_rawat = $_POST['no_rawat'];
       
       // Ambil data nosep sebelum dihapus untuk menghapus data terkait
       $veronisa_data = $this->db('mlite_veronisa')
         ->where('no_rawat', $no_rawat)
         ->oneArray();
       
       // Hapus data dari tabel mlite_veronisa berdasarkan no_rawat
       $delete_result = $this->db('mlite_veronisa')
         ->where('no_rawat', $no_rawat)
         ->delete();
       
       if ($delete_result) {
         if ($veronisa_data && !empty($veronisa_data['nosep'])) {
           // Hapus data di mlite_apotek_online_sep_data berdasarkan no_sep
           $this->db('mlite_apotek_online_sep_data')
             ->where('no_sep', $veronisa_data['nosep'])
             ->delete();
             
           // Juga hapus data feedback terkait jika ada
           $this->db('mlite_veronisa_feedback')
             ->where('nosep', $veronisa_data['nosep'])
             ->delete();
         }
         
         echo json_encode([
           'status' => 'success',
           'message' => 'Data veronisa dan data terkait berhasil dihapus'
         ]);
      } else {
        echo json_encode([
          'status' => 'error',
          'message' => 'Gagal menghapus data veronisa'
        ]);
      }
    } else {
      echo json_encode([
        'status' => 'error',
        'message' => 'Parameter no_rawat tidak ditemukan'
      ]);
    }
    exit();
  }

  public function getApotekOnline()
  {
    $parsedown = new \Systems\Lib\Parsedown();
    $readme_file = MODULES . '/veronisa/Help.md';
    $readme =  $parsedown->text($this->tpl->noParse(file_get_contents($readme_file)));
    return $this->draw('apotekonline.html', ['readme' => $readme]);
  }

  public function getReferensi()
  {
    return $this->draw('referensi.html', ['veronisa' => $this->assign]);
  }
  
  public function getObat()
  {
    return $this->draw('obat.html', ['veronisa' => $this->assign]);
  }

  public function getPelayananObat()
  {
    return $this->draw('pelayananobat.html', ['veronisa' => $this->assign]);
  }

  public function getResep()
  {
    return $this->draw('resep.html', ['veronisa' => $this->assign]);
  }

  public function getCariSEP()
  {
    return $this->draw('carisep.html', ['veronisa' => $this->assign]);
  }

  public function getMonitoringKlaim()
  {
    return $this->draw('monitoringklaim.html', ['veronisa' => $this->assign]);
  }

  public function getMonitoringDataKlaim()
  {
    $this->_addHeaderFiles();
    return $this->draw('monitoringdataklaim.html', ['veronisa' => $this->assign]);
  }

  public function getKirimApotikOnline($no_rawat)
  {    
    // Ambil data SEP
    $sep_data = $this->db('mlite_apotek_online_sep_data')
      ->where('no_rawat', $this->revertNorawat($no_rawat))
      ->oneArray();
    
    // Ambil data obat yang diberikan dengan mapping obat apotek online (non-racikan dan racikan)
    $no_rawat_reverted = $this->revertNorawat($no_rawat);
    $obat_data = $this->db()->pdo()->prepare("
      SELECT 
        ro.no_resep, 
        ro.tgl_perawatan, 
        ro.jam, 
        ro.tgl_peresepan, 
        ro.jam_peresepan, 
        ro.status, 
        ro.tgl_penyerahan, 
        ro.jam_penyerahan, 
        'non_racikan' as jenis_resep, 
        rd.kode_brng, 
        rd.jml, 
        rd.aturan_pakai, 
        db.nama_brng as nama_item, 
        NULL as no_racik, 
        NULL as nama_racik, 
        NULL as kd_racik, 
        NULL as jml_dr, 
        NULL as nm_racik, 
        NULL as detail_racikan,
        maom.kd_obat_bpjs, 
        maom.nama_obat_bpjs 
      FROM resep_obat ro 
      LEFT JOIN resep_dokter rd ON ro.no_resep = rd.no_resep 
      LEFT JOIN databarang db ON rd.kode_brng = db.kode_brng 
      LEFT JOIN mlite_apotek_online_maping_obat maom ON rd.kode_brng = maom.kode_brng 
      WHERE rd.kode_brng IS NOT NULL 
      AND ro.no_rawat = ? 
      
      UNION ALL 
      
      SELECT 
        ro.no_resep, 
        ro.tgl_perawatan, 
        ro.jam, 
        ro.tgl_peresepan, 
        ro.jam_peresepan, 
        ro.status, 
        ro.tgl_penyerahan, 
        ro.jam_penyerahan, 
        'racikan' as jenis_resep, 
        NULL as kode_brng,
        NULL as jml,
        rdr.aturan_pakai, 
        rdr.nama_racik as nama_item, 
        rdr.no_racik, 
        rdr.nama_racik, 
        rdr.kd_racik, 
        rdr.jml_dr, 
        mr.nm_racik, 
        (
          SELECT JSON_ARRAYAGG(
            JSON_OBJECT(
              'kode_brng', rdrd.kode_brng,
              'jml', rdrd.jml,
              'nama_brng', db.nama_brng,
              'kd_obat_bpjs', maom.kd_obat_bpjs,
              'nama_obat_bpjs', maom.nama_obat_bpjs
            )
          )
          FROM resep_dokter_racikan_detail rdrd
          LEFT JOIN databarang db ON rdrd.kode_brng = db.kode_brng
          LEFT JOIN mlite_apotek_online_maping_obat maom ON rdrd.kode_brng = maom.kode_brng
          WHERE rdrd.no_resep = rdr.no_resep AND rdrd.no_racik = rdr.no_racik
        ) as detail_racikan,
        NULL as kd_obat_bpjs, 
        NULL as nama_obat_bpjs 
      FROM resep_obat ro 
      LEFT JOIN resep_dokter_racikan rdr ON ro.no_resep = rdr.no_resep 
      LEFT JOIN metode_racik mr ON rdr.kd_racik = mr.kd_racik
      WHERE rdr.no_racik IS NOT NULL 
      AND ro.no_rawat = ? 
      ORDER BY no_resep, jenis_resep, no_racik
    ");
    $obat_data->execute([$no_rawat_reverted, $no_rawat_reverted]);
    $obat_data = $obat_data->fetchAll();
    
    $this->assign['sep_data'] = $sep_data;
    $this->assign['obat_data'] = $obat_data;
    $this->assign['no_rawat'] = $this->revertNorawat($no_rawat);
    $this->assign['user'] = $this->core->getUserInfo('username', $_SESSION['mlite_user']);
    $this->assign['kd_dokter'] = isset_or($obat_data['0']['kd_dokter'], '');
    
    echo $this->draw('kirimapotikonline.html', ['veronisa' => $this->assign]);
    exit();
  }

public function postHapusResepResponse()
  {
    if (ob_get_level()) {
      ob_clean();
    }
    header('Content-Type: application/json');
    http_response_code(200);

    try {
      $no_rawat = $_POST['no_rawat'] ?? '';
      
      if (empty($no_rawat)) {
        throw new \Exception('No rawat tidak boleh kosong');
      }

      // Ambil data resep yang akan dihapus untuk bridging API
      $resep_data = $this->db('mlite_apotek_online_resep_response_log')
        ->where('no_rawat', $no_rawat)
        ->oneArray();

      if (!$resep_data) {
        throw new \Exception('Data resep tidak ditemukan');
      }

      // Bridging API hapus pelayanan obat dan resep ke BPJS
      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
      
      // Parse raw response untuk mendapatkan data yang diperlukan
      // $raw_response = json_decode($resep_data['raw_response'], true);
      // $response = $raw_response['response'] ?? [];
      
      $hapus_resep_data = [
        'nosjp' => $resep_data['no_apotik'] ?? '',
        'refasalsjp' => $resep_data['no_sep_kunjungan'] ?? '',
        'noresep' => $resep_data['no_resep'] ?? ''
      ];

      // Validasi data yang diperlukan untuk API hapus
      if (empty($hapus_resep_data['nosjp']) || empty($hapus_resep_data['noresep'])) {
        throw new \Exception('Data tidak lengkap untuk menghapus resep di BPJS');
      }

      // Ambil data obat dari resep untuk dihapus satu per satu
      $obat_resep = $this->db('mlite_apotek_online_resep_response_log')
        ->join('detail_pemberian_obat', 'detail_pemberian_obat.no_rawat=mlite_apotek_online_resep_response_log.no_rawat')
        ->join('mlite_apotek_online_maping_obat', 'mlite_apotek_online_maping_obat.kode_brng=detail_pemberian_obat.kode_brng')
        ->where('mlite_apotek_online_resep_response_log.no_rawat', $no_rawat)
        ->toArray();

      $hapus_obat_responses = [];
      
      // Hapus setiap pelayanan obat terlebih dahulu
      foreach ($obat_resep as $index => $obat) {
        $hapus_obat_data = [
          'nosepapotek' => $resep_data['no_apotik'] ?? '',
          'noresep' => $resep_data['no_resep'] ?? '',
          'kodeobat' => $obat['kd_obat_bpjs'] ?? '',
          'tipeobat' => 'N' // Default tipe obat Non-Racikan
        ];

        $url_hapus_obat = $this->api_url . 'pelayanan/obat/hapus';
        $output_hapus_obat = BpjsService::delete($url_hapus_obat, json_encode($hapus_obat_data), $this->consid, $this->secretkey, $this->user_key, $tStamp);
        $json_hapus_obat = json_decode($output_hapus_obat, true);
        
        $hapus_obat_responses[] = [
          'kode_obat' => $obat['kode_brng'],
          'nama_obat' => $obat['nama_brng'] ?? 'Unknown',
          'kd_obat_bpjs' => $obat['kd_obat_bpjs'] ?? '',
          'response' => $json_hapus_obat
        ];

        // Debug log untuk troubleshooting
        file_put_contents("debug_hapus_obat_{$index}.json", json_encode([
          'nosepapotek' => $resep_data['no_sep_kunjungan'] ?? '',
          'noresep' => $resep_data['no_resep'] ?? '',
          'kodeobat' => $obat['kd_obat_bpjs'] ?? '',
          'tipeobat' => 'N' // Default tipe obat Non-Racikan
        ], JSON_PRETTY_PRINT));

        // Jika ada error saat hapus obat, catat tapi lanjutkan
        if ($json_hapus_obat['metaData']['code'] !== '200') {
          error_log('Gagal hapus obat ' . $obat['kode_brng'] . ': ' . $json_hapus_obat['metaData']['message']);
        }
      }

      // Setelah hapus semua obat, baru hapus resep
      $url_hapus_resep = $this->api_url . 'hapusresep';
      $output_hapus = BpjsService::delete($url_hapus_resep, json_encode($hapus_resep_data), $this->consid, $this->secretkey, $this->user_key, $tStamp);
      $json_hapus = json_decode($output_hapus, true);

      // Cek response dari BPJS
      if ($json_hapus['metaData']['code'] !== '200') {
        throw new \Exception('Gagal menghapus resep di BPJS: ' . $json_hapus['metaData']['message']);
      }

      // Hapus data dari tabel mlite_apotek_online_resep_response_log setelah berhasil hapus di BPJS
      $deleted = $this->db('mlite_apotek_online_resep_response_log')
        ->where('no_rawat', $no_rawat)
        ->delete();

      if ($deleted) {
        // Siapkan data request untuk log hapus
        $hapus_request_data = [
          'action' => 'hapus_resep',
          'no_rawat' => $no_rawat,
          'resep_data' => $hapus_resep_data,
          'obat_data' => $hapus_obat_data ?? []
        ];

        // Log aktivitas hapus
        $this->db('mlite_apotek_online_log')->save([
          'no_rawat' => $no_rawat,
          'noresep' => $hapus_resep_data['noresep'],
          'tanggal_kirim' => date('Y-m-d H:i:s'),
          'status' => 'success',
          'response_resep' => 'Data resep berhasil dihapus dari BPJS dan lokal. Response BPJS: ' . json_encode($json_hapus),
          'response_obat' => 'Hapus obat responses: ' . json_encode($hapus_obat_responses),
          'request' => json_encode($hapus_request_data),
          'user' => $this->core->getUserInfo('username', $_SESSION['mlite_user'])
        ]);

        echo json_encode([
          'success' => true,
          'message' => 'Data resep dan obat berhasil dihapus dari BPJS dan database lokal',
          'bpjs_response' => $json_hapus,
          'obat_responses' => $hapus_obat_responses
        ]);
      } else {
        throw new \Exception('Berhasil hapus di BPJS tapi gagal hapus data lokal');
      }
    } catch (\Exception $e) {
      // Log error untuk debugging
      error_log('Error hapus resep response: ' . $e->getMessage());
      error_log('Stack trace: ' . $e->getTraceAsString());
      
      echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug_info' => [
          'file' => $e->getFile(),
          'line' => $e->getLine(),
          'no_rawat' => $_POST['no_rawat'] ?? 'not set'
        ]
      ]);
    } catch (\Error $e) {
      // Log error untuk debugging
      error_log('Fatal error hapus resep response: ' . $e->getMessage());
      error_log('Stack trace: ' . $e->getTraceAsString());
      
      echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
        'debug_info' => [
          'file' => $e->getFile(),
          'line' => $e->getLine(),
          'no_rawat' => $_POST['no_rawat'] ?? 'not set'
        ]
      ]);
    }
    exit();
  }

  public function postKirimApotikOnline()
  {
    if (ob_get_level()) {
      ob_clean();
    }
    header('Content-Type: application/json');

    // Debug: Simpan semua data $_POST ke file JSON
    file_put_contents('debug_post_data_' . date('Y-m-d_H-i-s') . '.json', json_encode([
      'timestamp' => date('Y-m-d H:i:s'),
      'post_data' => $_POST
    ], JSON_PRETTY_PRINT));

    try {
      $this->db('mlite_apotek_online_log')->limit(1)->toArray();
    } catch (\Exception $e) {
      echo json_encode([
        'success' => false,
        'message' => 'Tabel mlite_apotek_online_log belum dibuat. Silakan jalankan SQL: mlite_apotek_online_log.sql'
      ]);
      exit();
    }


    try {
      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));

      $key = $this->consid . $this->secretkey . $tStamp;

      $tglsjp = str_replace('T', ' ', $_POST['TGLSJP']);
      $tglrsp = str_replace('T', ' ', $_POST['TGLRSP']);
      $tglpelrsp = str_replace('T', ' ', $_POST['TGLPELRSP']);

      $resep_data = [
        'TGLSJP' => $tglsjp,
        'REFASALSJP' => $_POST['REFASALSJP'],
        'POLIRSP' => $_POST['POLIRSP'],
        'KDJNSOBAT' => $_POST['KDJNSOBAT'],
        'NORESEP' => substr($_POST['NORESEP'], -5),
        'IDUSERSJP' => $_POST['IDUSERSJP'],
        'TGLRSP' => $tglrsp,
        'TGLPELRSP' => $tglpelrsp,
        'KdDokter' => $_POST['KdDokter'] ?? '0',
        'iterasi' => $_POST['iterasi'] ?? '0'
      ];

      // Validasi
      foreach (['TGLSJP', 'REFASALSJP', 'POLIRSP', 'KDJNSOBAT', 'NORESEP', 'IDUSERSJP', 'TGLRSP', 'TGLPELRSP'] as $field) {
        if (empty($resep_data[$field])) {
          throw new \Exception("Field {$field} tidak boleh kosong");
        }
      }    

      $url_resep = $this->api_url . 'sjpresep/v3/insert';
      $output_resep = BpjsService::post($url_resep, json_encode($resep_data), $this->consid, $this->secretkey, $this->user_key, $tStamp);
      $json_resep = json_decode($output_resep, true);
      
      file_put_contents('debug_kirim_resep.json', json_encode([
        'url' => $url_resep,
        'payload' => $resep_data,
        'response' => $json_resep
      ], JSON_PRETTY_PRINT));

      $stringDecrypt = stringDecrypt($key, $json_resep['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
        $decompress = \LZCompressor\LZString::decompressFromEncodedURIComponent(($stringDecrypt));
      }

      file_put_contents('debug_kirim_resep_response.json', json_encode([
        'decompress' => $decompress
      ], JSON_PRETTY_PRINT));

      if ($json_resep['metaData']['code'] !== '200') {
        throw new \Exception('Gagal mengirim data resep: ' . $json_resep['metaData']['message']);
      }

      // Simpan respons resep ke tabel khusus
      if (isset($json_resep['response'])) {
        $code = $json_resep['metaData']['code'];
        $message = $json_resep['metaData']['message'];
        $raw_response = '{
                "metaData": {
                  "code": "' . $code . '",
                  "message": "' . $message . '"
                },
                "response": ' . $decompress . '}';
                      
        $response = json_decode($decompress, true); 
        $this->db('mlite_apotek_online_resep_response_log')->save([
          'no_rawat' => $_POST['no_rawat'],
          'no_sep_kunjungan' => $response['noSep_Kunjungan'] ?? null,
          'no_kartu' => $response['noKartu'] ?? null,
          'nama' => $response['nama'] ?? null,
          'faskes_asal' => $response['faskesAsal'] ?? null,
          'no_apotik' => $response['noApotik'] ?? null,
          'no_resep' => $response['noResep'] ?? null,
          'tgl_resep' => $response['tglResep'] ?? null,
          'kd_jns_obat' => $response['kdJnsObat'] ?? null,
          'by_tag_rsp' => $response['byTagRsp'] ?? null,
          'by_ver_rsp' => $response['byVerRsp'] ?? null,
          'tgl_entry' => $response['tglEntry'] ?? null,
          'meta_code' => $json_resep['metaData']['code'],
          'meta_message' => $json_resep['metaData']['message'],
          'raw_response' => $raw_response,
          'user' => $this->core->getUserInfo('username', null, true)
        ]);
      }

      // === Kirim Obat
      $obat_responses = [];
      $obat_errors = [];

      if (isset($_POST['obat']) && is_array($_POST['obat']) && $response['noApotik'] !='') {
        foreach ($_POST['obat'] as $index => $obat) {
          try {
            $obat_data = [
              'NOSJP' => $response['noApotik'],
              'NORESEP' => $response['noResep'],
              'KDOBT' => $obat['KDOBT'],
              'NMOBAT' => $obat['NMOBAT'],
              'SIGNA1OBT' => (int)$obat['SIGNA1OBT'],
              'SIGNA2OBT' => (int)$obat['SIGNA2OBT'],
              'JMLOBT' => (int)$obat['JMLOBT'],
              'JHO' => (int)$obat['JHO'],
              'CatKhsObt' => $obat['CatKhsObt'] ?? ''
            ];
            $url_obat = $this->api_url . 'obatnonracikan/v3/insert';

            $output_obat = BpjsService::post($url_obat, json_encode($obat_data), $this->consid, $this->secretkey, $this->user_key, $tStamp);
            $json_obat = json_decode($output_obat, true);
            
            // Simpan ke file debug untuk setiap obat
            file_put_contents("debug_kirim_obat_{$index}.json", json_encode([
              'url' => $url_obat,
              'payload' => $obat_data,
              'response' => $json_obat
            ], JSON_PRETTY_PRINT));

            $obat_responses[] = $json_obat;

            if ($json_obat['metaData']['code'] !== '200') {
              $obat_errors[] = [
                'index' => $index,
                'message' => $json_obat['metaData']['message'],
                'data' => $obat_data
              ];
            }

          } catch (\Exception $ex) {
            file_put_contents("debug_kirim_obat_{$index}.json", json_encode([
              'error' => $ex->getMessage(),
              'data' => $obat ?? []
            ], JSON_PRETTY_PRINT));

            $obat_responses[] = ['metaData' => ['code' => '500', 'message' => $ex->getMessage()]];

            $obat_errors[] = [
              'index' => $index,
              'message' => $ex->getMessage(),
              'data' => $obat_data ?? $obat
            ];
          }
        }
      }

      // === Kirim Racikan
      if (isset($_POST['racikan']) && is_array($_POST['racikan']) && $response['noApotik'] !='') {
        foreach ($_POST['racikan'] as $index => $racikan) {
          // Process each detail item in racikan
          if (isset($racikan['detail']) && is_array($racikan['detail'])) {
            foreach ($racikan['detail'] as $detail_index => $detail) {
              try {
                $racikan_data = [
                  'NOSJP' => $response['noApotik'],
                  'NORESEP' => $response['noResep'],
                  'JNSROBT' => $racikan['JNSROBT'],
                  'KDOBT' => $detail['kd_obat_bpjs'] ?? $detail['kode_brng'] ?? '',
                  'NMOBAT' => $detail['nama_obat_bpjs'] ?? $detail['nama_brng'] ?? '',
                  'SIGNA1OBT' => (int)$racikan['SIGNA1RACIKAN'],
                  'SIGNA2OBT' => (int)$racikan['SIGNA2RACIKAN'],
                  'PERMINTAAN' => (int)$detail['jml'],
                  'JMLOBT' => (int)$racikan['JMLRACIKAN'],
                  'JHO' => (int)$racikan['JHORACIKAN'],
                  'CatKhsObt' => $racikan['CatKhsObt'] ?? ''
                ];
                $url_racikan = $this->api_url . 'obatracikan/v3/insert';

                $output_racikan = BpjsService::post($url_racikan, json_encode($racikan_data), $this->consid, $this->secretkey, $this->user_key, $tStamp);
                $json_racikan = json_decode($output_racikan, true);
                
                // Simpan ke file debug untuk setiap detail racikan
                file_put_contents("debug_kirim_obat_racikan_{$index}_{$detail_index}.json", json_encode([
                  'url' => $url_racikan,
                  'payload' => $racikan_data,
                  'response' => $json_racikan
                ], JSON_PRETTY_PRINT));

                $obat_responses[] = $json_racikan;

                if ($json_racikan['metaData']['code'] !== '200') {
                  $obat_errors[] = [
                    'index' => "racikan_{$index}_detail_{$detail_index}",
                    'message' => $json_racikan['metaData']['message'],
                    'data' => $racikan_data
                  ];
                }

              } catch (\Exception $ex) {
                file_put_contents("debug_kirim_obat_racikan_{$index}_{$detail_index}.json", json_encode([
                  'error' => $ex->getMessage(),
                  'data' => $detail ?? []
                ], JSON_PRETTY_PRINT));

                $obat_responses[] = ['metaData' => ['code' => '500', 'message' => $ex->getMessage()]];

                $obat_errors[] = [
                  'index' => "racikan_{$index}_detail_{$detail_index}",
                  'message' => $ex->getMessage(),
                  'data' => $racikan_data ?? $detail
                ];
              }
            }
          }
        }
      }

      // Siapkan data request untuk disimpan
       $request_data = [
         'resep' => $resep_data,
         'obat' => $_POST['obat'] ?? [],
         'racikan' => $_POST['racikan'] ?? []
       ];

      // === DEBUG: Simpan response lengkap dari API BPJS ===
      $debug_response_data = [
        'timestamp' => date('Y-m-d H:i:s'),
        'no_rawat' => $_POST['no_rawat'] ?? '',
        'user' => $this->core->getUserInfo('username', null, true),
        'resep_response' => [
          'raw_output' => $output_resep ?? '',
          'json_decoded' => $json_resep ?? [],
          'decompressed' => $decompress ?? '',
          'final_response' => $response ?? []
        ],
        'obat_responses' => $obat_responses,
        'obat_errors' => $obat_errors,
        'success_summary' => [
          'resep_success' => isset($json_resep['metaData']) && $json_resep['metaData']['code'] === '200',
          'obat_success_count' => count(array_filter($obat_responses, function($resp) {
            return isset($resp['metaData']) && $resp['metaData']['code'] === '200';
          })),
          'obat_error_count' => count($obat_errors)
        ]
      ];
      
      // Simpan debug response file
      $debug_response_filename = 'debug_apotek_online_response_' . date('Y-m-d_H-i-s') . '_' . ($_POST['no_rawat'] ?? 'unknown') . '.json';
      file_put_contents($debug_response_filename, json_encode($debug_response_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

      // Simpan ke database
      $this->db('mlite_apotek_online_log')->save([
        'no_rawat' => $_POST['no_rawat'],
        'noresep' => $resep_data['NORESEP'],
        'tanggal_kirim' => date('Y-m-d H:i:s'),
        'status' => 'success',
        'response_resep' => $raw_response,
        'response_obat' => json_encode($obat_responses),
        'request' => json_encode($request_data),
        'user' => $this->core->getUserInfo('username', null, true)
      ]);

      echo json_encode([
        'success' => true,
        'message' => 'Data berhasil dikirim ke Apotek Online BPJS',
        'resep_response' => $json_resep,
        'obat_responses' => $obat_responses
      ]);

    } catch (\Exception $e) {
      ob_clean();
      
      // === DEBUG: Simpan error lengkap ===
      $debug_error_data = [
        'timestamp' => date('Y-m-d H:i:s'),
        'no_rawat' => $_POST['no_rawat'] ?? '',
        'user' => $this->core->getUserInfo('username', null, true),
        'error_details' => [
          'message' => $e->getMessage(),
          'file' => $e->getFile(),
          'line' => $e->getLine(),
          'trace' => $e->getTraceAsString()
        ],
        'request_data' => [
          'resep' => $resep_data ?? [],
          'obat' => $_POST['obat'] ?? [],
          'raw_post' => $_POST
        ],
        'api_config' => [
          'api_url' => $this->api_url ?? '',
          'consid' => $this->consid ?? '',
          'user_key' => $this->user_key ?? ''
        ]
      ];
      
      // Simpan debug error file
      $debug_error_filename = 'debug_apotek_online_error_' . date('Y-m-d_H-i-s') . '_' . ($_POST['no_rawat'] ?? 'unknown') . '.json';
      file_put_contents($debug_error_filename, json_encode($debug_error_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
      
      try {
        // Siapkan data request untuk error log
        $error_request_data = [
          'resep' => $resep_data ?? [],
          'obat' => $_POST['obat'] ?? [],
          'raw_post' => $_POST
        ];

        $this->db('mlite_apotek_online_log')->save([
          'no_rawat' => $_POST['no_rawat'] ?? '',
          'noresep' => $_POST['NORESEP'] ?? '',
          'tanggal_kirim' => date('Y-m-d H:i:s'),
          'status' => 'error',
          'response_resep' => $e->getMessage(),
          'response_obat' => '',
          'request' => json_encode($error_request_data),
          'user' => $this->core->getUserInfo('username', null, true)
        ]);
      } catch (\Exception $logError) {}
      
      echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
      ]);
    } catch (\Error $e) {
      ob_clean();
      echo json_encode([
        'success' => false,
        'message' => 'Fatal error: ' . $e->getMessage()
      ]);
    }

    exit();
  }

  public function getMappingObat()
  {
    $this->_addHeaderFiles();
    $this->assign['row'] = $this->db('mlite_apotek_online_maping_obat')->toArray();
    $this->assign['obat'] = $this->db('databarang')->where('status', '1')->toArray();
    return $this->draw('mappingobat.html', ['obat' => $this->assign['obat'], 'row' => $this->assign['row']]);
  }

  public function postSaveObat()
  {
      $kode_obat_bpjs = $_POST['obat_kode'] ?? '';
      $nama_obat_bpjs = $_POST['obat_nama'] ?? '';
      $kd_obat_rs     = $_POST['kode_obat_rs'] ?? '';

      if (!$kode_obat_bpjs || !$nama_obat_bpjs || !$kd_obat_rs) {
          $_SESSION['error'] = 'Semua field wajib diisi.';
          redirect(url([ADMIN, 'veronisa', 'mappingobat']));
      }

      // Simpan atau update mapping obat
      $this->db('mlite_apotek_online_maping_obat')->save([
          'kode_brng'       => $kd_obat_rs,
          'kd_obat_bpjs'  => $kode_obat_bpjs,
          'nama_obat_bpjs'  => $nama_obat_bpjs,
      ]);

      $_SESSION['success'] = 'Mapping obat berhasil disimpan.';
      redirect(url([ADMIN, 'veronisa', 'mappingobat']));
  }

  public function getObatDelete($kode_brng)
  {
      // Hapus mapping obat berdasarkan kode_brng
      $delete = $this->db('mlite_apotek_online_maping_obat')
          ->where('kode_brng', $kode_brng)
          ->delete();

      if ($delete) {
          $this->notify('success', 'Mapping obat berhasil dihapus.');
      } else {
          $this->notify('failure', 'Gagal menghapus mapping obat.');
      }

      redirect(url([ADMIN, 'veronisa', 'mappingobat']));
  }

  public function getDPHO()
  {
    date_default_timezone_set('UTC');
    $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
    $key = $this->consid . $this->secretkey . $tStamp;

    $url = $this->api_url . 'referensi/dpho';
    $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey, $this->user_key, $tStamp);
    $json = json_decode($output, true);
    
    $code = $json['metaData']['code'];
    $message = $json['metaData']['message'];
    $stringDecrypt = stringDecrypt($key, $json['response']);
    $decompress = '""';
    if (!empty($stringDecrypt)) {
      $decompress = \LZCompressor\LZString::decompressFromEncodedURIComponent(($stringDecrypt));
    }
    
    if ($json != null) {
      header('Content-Type: application/json');
      echo '{
          "metaData": {
            "code": "' . $code . '",
            "message": "' . $message . '"
          },
          "response": ' . $decompress . '}';
    } else {
      header('Content-Type: application/json');
      echo '{
          "metaData": {
            "code": "5000",
            "message": "ERROR"
          },
          "response": "ADA KESALAHAN ATAU SAMBUNGAN KE SERVER BPJS TERPUTUS."}';
    }
    exit();
  }

  public function postTestReferensi()
  {
    // Set header JSON terlebih dahulu
    header('Content-Type: application/json');
    
    try {
        date_default_timezone_set('UTC');
        $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
        $key = $this->consid . $this->secretkey . $tStamp;

        $base_url = $_POST['base_url'] ?? '';
        $endpoint = $_POST['endpoint'] ?? '';
        $method = $_POST['method'] ?? 'GET';
        $parameters = $_POST['parameters'] ?? [];
        $form_data = $_POST['form'] ?? '';

        // Validasi input
        if (empty($endpoint)) {
            echo json_encode([
                'metaData' => [
                    'code' => '5000',
                    'message' => 'Endpoint tidak boleh kosong'
                ],
                'response' => 'Parameter endpoint diperlukan'
            ]);
            exit();
        }

        // Set API URL berdasarkan base_url
        if ($base_url === 'dev') {
            $api_url = 'https://apijkn-dev.bpjs-kesehatan.go.id/apotek-rest-dev/';
        } else {
            $api_url = $this->api_url; // Production URL dari settings
        }

        // Build URL dengan parameter
        $url = $api_url . $endpoint;
        if (!empty($parameters)) {
            $url_params = [];
            foreach ($parameters as $param_key => $value) {
                if (!empty($value)) {
                    $url_params[] = urlencode($value);
                }
            }
            if (!empty($url_params)) {
                $url .= '/' . implode('/', $url_params);
            }
        }

        // Panggil API BPJS
        if ($method === 'GET') {
            $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey, $this->user_key, $tStamp);
        } elseif ($method === 'DELETE') {
            $output = BpjsService::delete($url, $form_data, $this->consid, $this->secretkey, $this->user_key, $tStamp);
        } else {
            $output = BpjsService::post($url, $form_data, $this->consid, $this->secretkey, $this->user_key, $tStamp);
        }

        $json = json_decode($output, true);
        
        if ($json && isset($json['metaData'])) {
            $code = $json['metaData']['code'];
            $message = $json['metaData']['message'];
            $stringDecrypt = stringDecrypt($key, $json['response']);
            $decompress = '""';
            if (!empty($stringDecrypt)) {
                $decompress = \LZCompressor\LZString::decompressFromEncodedURIComponent(($stringDecrypt));
            }
            
            echo json_encode([
                'metaData' => [
                    'code' => $code,
                    'message' => $message
                ],
                'response' => json_decode($decompress, true),
                'request_info' => [
                    'url' => $url,
                    'method' => $method,
                    'timestamp' => $tStamp
                ]
            ]);
        } else {
            echo json_encode([
                'metaData' => [
                    'code' => '5000',
                    'message' => 'Invalid response from BPJS API'
                ],
                'response' => 'Response tidak valid dari server BPJS',
                'raw_response' => $output
            ]);
        }
    } catch (\Exception $e) {
        echo json_encode([
            'metaData' => [
                'code' => '5000',
                'message' => 'Error: ' . $e->getMessage()
            ],
            'response' => 'Terjadi kesalahan saat menghubungi server BPJS'
        ]);
    }
    exit();
  }

  public function postMonitoringDataKlaim()
  {
    // Set header JSON terlebih dahulu
    header('Content-Type: application/json');
    
    try {
        date_default_timezone_set('UTC');
        $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
        $key = $this->consid . $this->secretkey . $tStamp;

        $bulan = $_POST['bulan'] ?? '';
        $tahun = $_POST['tahun'] ?? '';
        $jenis_obat = $_POST['jenis_obat'] ?? '0';
        $status = $_POST['status'] ?? '1';
        $base_url = $_POST['base_url'] ?? 'dev';

        // Validasi input
        if (empty($bulan) || empty($tahun)) {
            echo json_encode([
                'metaData' => [
                    'code' => '5000',
                    'message' => 'Parameter bulan dan tahun harus diisi'
                ],
                'response' => 'Parameter bulan dan tahun diperlukan'
            ]);
            exit();
        }

        // Validasi konfigurasi BPJS
        if (empty($this->consid) || empty($this->secretkey) || empty($this->user_key)) {
            echo json_encode([
                'metaData' => [
                    'code' => '5000',
                    'message' => 'Konfigurasi BPJS belum lengkap. Silakan periksa pengaturan Cons ID, Secret Key, dan User Key.'
                ],
                'response' => 'Konfigurasi BPJS tidak lengkap'
            ]);
            exit();
        }

        // Set API URL berdasarkan base_url
        if ($base_url === 'dev') {
            $api_url = 'https://apijkn-dev.bpjs-kesehatan.go.id/apotek-rest-dev/';
        } else {
            $api_url = $this->api_url; // Production URL dari settings
            if (empty($api_url)) {
                echo json_encode([
                    'metaData' => [
                        'code' => '5000',
                        'message' => 'URL API production belum dikonfigurasi'
                    ],
                    'response' => 'URL production tidak tersedia'
                ]);
                exit();
            }
        }

        // Konstruksi URL endpoint
        $url = $api_url . 'monitoring/klaim/' . $bulan . '/' . $tahun . '/' . $jenis_obat . '/' . $status;

        // Panggil BPJS API menggunakan BpjsService::get
        $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey, $this->user_key, $tStamp);
        
        $json = json_decode($output, true);
        
        if ($json && isset($json['metaData'])) {
            $code = $json['metaData']['code'];
            $message = $json['metaData']['message'];
            $stringDecrypt = stringDecrypt($key, $json['response']);
            $decompress = '""';
            if (!empty($stringDecrypt)) {
                $decompress = \LZCompressor\LZString::decompressFromEncodedURIComponent(($stringDecrypt));
            }
            
            echo json_encode([
                'metaData' => [
                    'code' => $code,
                    'message' => $message
                ],
                'response' => json_decode($decompress, true),
                'request_info' => [
                    'url' => $url,
                    'method' => 'GET',
                    'timestamp' => $tStamp
                ]
            ]);
        } else {
            echo json_encode([
                'metaData' => [
                    'code' => '5000',
                    'message' => 'Invalid response from BPJS API'
                ],
                'response' => 'Response tidak valid dari server BPJS',
                'raw_response' => $output
            ]);
        }
    } catch (\Exception $e) {
        error_log('Error in postMonitoringDataKlaim: ' . $e->getMessage());
        echo json_encode([
            'metaData' => [
                'code' => '5000',
                'message' => 'Error: ' . $e->getMessage()
            ],
            'response' => 'Terjadi kesalahan saat menghubungi server BPJS',
            'debug' => [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]
        ]);
    }
    
    exit();
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
      $decompress = \LZCompressor\LZString::decompressFromEncodedURIComponent(($stringDecrypt));
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
        $decompress = \LZCompressor\LZString::decompressFromEncodedURIComponent(($stringDecrypt));
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
    $qrCode = url()."/".ADMIN."/tmp/qrcode.png";

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
    $id = $this->revertNorawat($id);
    $set_status = $this->db('bridging_sep')->where('no_rawat', $id)->oneArray();
    $veronisa = $this->db('mlite_veronisa')->join('mlite_veronisa_feedback','mlite_veronisa_feedback.nosep=mlite_veronisa.nosep')->where('status','<>','')->where('mlite_veronisa.no_rawat', $id)->asc('mlite_veronisa.id')->toArray();
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

  public function postSimpanSep()
  {
    // Start output buffering to prevent any warnings from corrupting JSON
    ob_start();
    header('Content-Type: application/json');
    
    try {
      // Validasi input
      if (!isset($_POST['sep_data']) || empty($_POST['sep_data'])) {
        ob_clean();
        echo json_encode([
          'status' => 'error',
          'message' => 'Data SEP tidak ditemukan'
        ]);
        exit();
      }

      if (!isset($_POST['no_rawat']) || empty($_POST['no_rawat'])) {
        ob_clean();
        echo json_encode([
          'status' => 'error',
          'message' => 'Nomor rawat tidak ditemukan'
        ]);
        exit();
      }

      $sep_data = json_decode($_POST['sep_data'], true);
      $no_rawat = $_POST['no_rawat'];
      
      if (!$sep_data || !isset($sep_data['response'])) {
        ob_clean();
        echo json_encode([
          'status' => 'error',
          'message' => 'Format data SEP tidak valid'
        ]);
        exit();
      }

      $response = $sep_data['response'];
      
      // Cek apakah data SEP sudah ada
      $existing_sep = $this->db('mlite_apotek_online_sep_data')
        ->where('no_sep', $response['noSep'])
        ->oneArray();
      
      if ($existing_sep) {
        ob_clean();
        echo json_encode([
          'status' => 'error',
          'message' => 'Data SEP dengan nomor ' . $response['noSep'] . ' sudah ada'
        ]);
        exit();
      }

      $response['kodedokter'] = '372921';
      $response['namadokter'] = 'Tenaga Medis 372921';
      
      // Simpan data SEP ke database
      $save_data = [
        'no_sep' => $response['noSep'],
        'faskes_asal_resep' => $response['faskesasalresep'],
        'nm_faskes_asal_resep' => $response['nmfaskesasalresep'],
        'no_kartu' => $response['nokartu'],
        'nama_peserta' => $response['namapeserta'],
        'jns_kelamin' => $response['jnskelamin'],
        'tgl_lahir' => $response['tgllhr'],
        'pisat' => $response['pisat'],
        'kd_jenis_peserta' => $response['kdjenispeserta'],
        'nm_jenis_peserta' => $response['nmjenispeserta'],
        'kode_bu' => $response['kodebu'],
        'nama_bu' => $response['namabu'],
        'tgl_sep' => $response['tglsep'],
        'tgl_plg_sep' => $response['tglplgsep'],
        'jns_pelayanan' => $response['jnspelayanan'],
        'nm_diag' => $response['nmdiag'],
        'poli' => $response['poli'],
        'flag_prb' => $response['flagprb'],
        'nama_prb' => $response['namaprb'],
        'kode_dokter' => $response['kodedokter'],
        'nama_dokter' => $response['namadokter'],
        'tanggal_simpan' => date('Y-m-d H:i:s'),
        'user_simpan' => $this->core->getUserInfo('username', null, true),
        'raw_response' => $sep_data,
        'no_rawat' => $no_rawat
      ];

      $result = $this->db('mlite_apotek_online_sep_data')->save($save_data);
      
      if ($result) {
        // Log aktivitas
        $this->db('mlite_apotek_online_log')->save([
          'no_rawat' => $no_rawat,
          'noresep' => '',
          'tanggal_kirim' => date('Y-m-d H:i:s'),
          'status' => 'success',
          'response_resep' => 'SEP Data Saved: ' . $response['noSep'],
          'response_obat' => json_encode($save_data),
          'user' => $this->core->getUserInfo('username', null, true)
        ]);

        $this->db('mlite_veronisa')->save([
          'id' => NULL,
          'tanggal' => date('Y-m-d'),
          'no_rawat' => $no_rawat,
          'no_rkm_medis' => $this->core->getRegPeriksaInfo('no_rkm_medis', $no_rawat),
          'tgl_registrasi' => $this->core->getRegPeriksaInfo('tgl_registrasi', $no_rawat),
          'nosep' => $response['noSep'],
          'status' => 'Belum', 
          'username' => $this->core->getUserInfo('username', null, true)
        ]);
        
        ob_clean();
        echo json_encode([
          'status' => 'success',
          'message' => 'Data SEP berhasil disimpan',
          'data' => [
            'no_sep' => $response['noSep'],
            'nama_peserta' => $response['namapeserta'],
            'tanggal_simpan' => date('Y-m-d H:i:s')
          ]
        ]);
        exit();
      } else {
        ob_clean();
        echo json_encode([
          'status' => 'error',
          'message' => 'Gagal menyimpan data SEP ke database'
        ]);
        exit();
      }
      
    } catch (\Exception $e) {
      error_log('Error in postSimpanSep: ' . $e->getMessage());
      ob_clean();
      echo json_encode([
        'status' => 'error',
        'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
        'debug' => [
          'file' => $e->getFile(),
          'line' => $e->getLine(),
          'trace' => $e->getTraceAsString()
        ]
      ]);
      exit();
    }
  }

  public function postCariSep()
  {
    header('Content-Type: application/json');
    
    try {
      // Validasi input
      if (!isset($_POST['no_kartu']) || empty($_POST['no_kartu'])) {
        echo json_encode([
          'success' => false,
          'message' => 'Nomor kartu BPJS harus diisi'
        ]);
        exit();
      }

      if (!isset($_POST['tgl_sep']) || empty($_POST['tgl_sep'])) {
        echo json_encode([
          'success' => false,
          'message' => 'Tanggal SEP harus diisi'
        ]);
        exit();
      }

      $no_kartu = $_POST['no_kartu'];
      $tgl_sep = $_POST['tgl_sep'];
      $no_sep = isset($_POST['no_sep']) ? $_POST['no_sep'] : '';
      
      // Cek apakah SEP sudah ada di database lokal
      $existing_sep = null;
      if (!empty($no_sep)) {
        $existing_sep = $this->db('mlite_apotek_online_sep_data')
          ->where('no_sep', $no_sep)
          ->oneArray();
      } else {
        // Cari berdasarkan no_kartu dan tanggal
        $existing_sep = $this->db('mlite_apotek_online_sep_data')
          ->where('no_kartu', $no_kartu)
          ->where('tgl_sep', $tgl_sep)
          ->oneArray();
      }
      
      if ($existing_sep) {
        echo json_encode([
          'success' => true,
          'message' => 'SEP ditemukan di database lokal',
          'data' => [
            'no_sep' => $existing_sep['no_sep'],
            'nama_peserta' => $existing_sep['nama_peserta'],
            'tgl_sep' => $existing_sep['tgl_sep']
          ]
        ]);
        exit();
      }
      
      // Jika tidak ada di database lokal, cari di BPJS API
      $bpjsService = new BpjsService();
      
      // Siapkan parameter pencarian
      $search_params = [
        'nokartu' => $no_kartu,
        'tanggal' => $tgl_sep
      ];
      
      if (!empty($no_sep)) {
        $search_params['nosep'] = $no_sep;
      }
      
      // Panggil API BPJS untuk mencari SEP
      $endpoint = 'SEP/peserta/' . $no_kartu . '/tglSEP/' . $tgl_sep;
      if (!empty($no_sep)) {
        $endpoint = 'SEP/' . $no_sep;
      }
      
      $response = $bpjsService->get($endpoint, $this->consid, $this->secretkey, $this->user_key, $this->api_url);
      
      if ($response && isset($response['response'])) {
        // SEP ditemukan di BPJS, simpan ke database lokal
        $sep_response = $response['response'];
        
        if (isset($sep_response['sep'])) {
          $sep_data = $sep_response['sep'];
          
          $save_data = [
            'no_sep' => $sep_data['noSep'],
            'faskes_asal_resep' => isset($sep_data['faskesasalresep']) ? $sep_data['faskesasalresep'] : '',
            'nm_faskes_asal_resep' => isset($sep_data['nmfaskesasalresep']) ? $sep_data['nmfaskesasalresep'] : '',
            'no_kartu' => $sep_data['peserta']['noKartu'],
            'nama_peserta' => $sep_data['peserta']['nama'],
            'jns_kelamin' => $sep_data['peserta']['kelamin'],
            'tgl_lahir' => $sep_data['peserta']['tglLahir'],
            'pisat' => isset($sep_data['peserta']['pisat']) ? $sep_data['peserta']['pisat'] : '',
            'kd_jenis_peserta' => isset($sep_data['peserta']['jenisPeserta']['kode']) ? $sep_data['peserta']['jenisPeserta']['kode'] : '',
            'nm_jenis_peserta' => isset($sep_data['peserta']['jenisPeserta']['keterangan']) ? $sep_data['peserta']['jenisPeserta']['keterangan'] : '',
            'kode_bu' => isset($sep_data['peserta']['hakKelas']['kode']) ? $sep_data['peserta']['hakKelas']['kode'] : '',
            'nama_bu' => isset($sep_data['peserta']['hakKelas']['keterangan']) ? $sep_data['peserta']['hakKelas']['keterangan'] : '',
            'tgl_sep' => $sep_data['tglSep'],
            'tgl_plg_sep' => isset($sep_data['tglPlgSep']) ? $sep_data['tglPlgSep'] : '',
            'jns_pelayanan' => isset($sep_data['jnsPelayanan']) ? $sep_data['jnsPelayanan'] : '',
            'nm_diag' => isset($sep_data['diagnosa']) ? $sep_data['diagnosa'] : '',
            'poli' => isset($sep_data['poli']) ? $sep_data['poli'] : '',
            'flag_prb' => isset($sep_data['flagPRB']) ? $sep_data['flagPRB'] : '',
            'nama_prb' => isset($sep_data['namaPRB']) ? $sep_data['namaPRB'] : '',
            'kode_dokter' => isset($sep_data['dpjp']['kdDPJP']) ? $sep_data['dpjp']['kdDPJP'] : '',
            'nama_dokter' => isset($sep_data['dpjp']['nmDPJP']) ? $sep_data['dpjp']['nmDPJP'] : '',
            'tanggal_simpan' => date('Y-m-d H:i:s'),
            'user_simpan' => $this->core->getUserInfo('username', null, true),
            'raw_response' => json_encode($response),
            'no_rawat' => '' // Akan diisi nanti saat ada rawat inap
          ];
          
          $result = $this->db('mlite_apotek_online_sep_data')->save($save_data);
          
          if ($result) {
            echo json_encode([
              'success' => true,
              'message' => 'SEP ditemukan dan berhasil disimpan',
              'data' => [
                'no_sep' => $sep_data['noSep'],
                'nama_peserta' => $sep_data['peserta']['nama'],
                'tgl_sep' => $sep_data['tglSep']
              ]
            ]);
            exit();
          }
        }
      }
      
      // Jika tidak ditemukan
      echo json_encode([
        'success' => false,
        'message' => 'SEP tidak ditemukan di sistem BPJS'
      ]);
      exit();
      
    } catch (\Exception $e) {
      error_log('Error in postCariSep: ' . $e->getMessage());
      echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
      ]);
      exit();
    }
  }

  public function anyLogApotikOnline($page = 1)
  {
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
    $totalRecords = $this->db()->pdo()->prepare("SELECT id FROM mlite_apotek_online_log WHERE (no_rawat LIKE ? OR noresep LIKE ? OR user LIKE ?) AND DATE(tanggal_kirim) BETWEEN ? AND ?");
    $totalRecords->execute(['%' . $phrase . '%', '%' . $phrase . '%', '%' . $phrase . '%', $start_date, $end_date]);
    $totalRecords = $totalRecords->fetchAll();

    $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url([ADMIN, 'veronisa', 'logapotikonline', '%d?s=' . $phrase . '&start_date=' . $start_date . '&end_date=' . $end_date]));
    $this->assign['pagination'] = $pagination->nav('pagination', '5');
    $this->assign['totalRecords'] = $totalRecords;

    $offset = $pagination->offset();
    $query = $this->db()->pdo()->prepare("SELECT * FROM mlite_apotek_online_log WHERE (no_rawat LIKE ? OR noresep LIKE ? OR user LIKE ?) AND DATE(tanggal_kirim) BETWEEN ? AND ? ORDER BY tanggal_kirim DESC LIMIT $perpage OFFSET $offset");
    $query->execute(['%' . $phrase . '%', '%' . $phrase . '%', '%' . $phrase . '%', $start_date, $end_date]);
    $rows = $query->fetchAll();

    $this->assign['list'] = [];
    if (count($rows)) {
      foreach ($rows as $row) {
        $row = htmlspecialchars_array($row);
        $this->assign['list'][] = $row;
      }
    }

    $this->assign['searchUrl'] = url([ADMIN, 'veronisa', 'logapotikonline', $page]);
    return $this->draw('logapotikonline.html', ['log_apotek' => $this->assign]);
  }

  public function postHapusLogApotikOnline()
  {
    header('Content-Type: application/json');
    
    try {
      if (!isset($_POST['id']) || empty($_POST['id'])) {
        echo json_encode([
          'success' => false,
          'message' => 'ID log harus diisi'
        ]);
        exit();
      }

      $id = $_POST['id'];
      
      // Cek apakah log ada
      $log = $this->db('mlite_apotek_online_log')->where('id', $id)->oneArray();
      if (!$log) {
        echo json_encode([
          'success' => false,
          'message' => 'Log tidak ditemukan'
        ]);
        exit();
      }

      $api_success = true;
      $api_message = '';
      
      // Jika ada noresep, hapus resep dari API BPJS terlebih dahulu
      if (!empty($log['noresep'])) {
        try {
          // Parse request data untuk mendapatkan nosjp dan refasalsjp
          $request_data = json_decode($log['request'], true);
          
          if ($request_data && isset($request_data['resep'])) {
            $resep_data = $request_data['resep'];
            
            // Siapkan data untuk hapus resep
            $hapus_data = [
              'nosjp' => isset($resep_data['NOSJP']) ? $resep_data['NOSJP'] : '',
              'refasalsjp' => isset($resep_data['REFASALSJP']) ? $resep_data['REFASALSJP'] : '',
              'noresep' => $log['noresep']
            ];
            
            // Debug: simpan ke file JSON
            $debug_data = [
              'timestamp' => date('Y-m-d H:i:s'),
              'user' => $this->core->getUserInfo('username', null, true),
              'hapus_data' => $hapus_data,
              'resep_data' => $resep_data,
              'log_noresep' => $log['noresep'],
              'log_id' => $id
            ];
            
            $debug_file = __DIR__ . '/debug_hapus_resep.json';
            $existing_data = [];
            if (file_exists($debug_file)) {
              $existing_data = json_decode(file_get_contents($debug_file), true) ?: [];
            }
            $existing_data[] = $debug_data;
            file_put_contents($debug_file, json_encode($existing_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            error_log('Debug data saved to: ' . $debug_file);
            
            // Panggil API hapus resep jika data lengkap
            if (!empty($hapus_data['nosjp']) && !empty($hapus_data['refasalsjp']) && !empty($hapus_data['noresep'])) {
              $bpjsService = new BpjsService();
              $endpoint = 'apotek-rest-dev/hapusresep';
              $api_url = 'https://apijkn-dev.bpjs-kesehatan.go.id/';
              
              // Log request yang akan dikirim
              error_log('Mengirim request hapus resep ke API BPJS:');
              error_log('Endpoint: ' . $api_url . $endpoint);
              error_log('Request data: ' . json_encode($hapus_data));
              error_log('User: ' . $this->core->getUserInfo('username', null, true));
              
              // Simpan log pengiriman ke database
              $log_data = [
                'tanggal_kirim' => date('Y-m-d H:i:s'),
                'no_rawat' => $log['no_rawat'],
                'noresep' => '',  // Kosong karena ini log hapus resep
                'status' => 'pending',
                'user' => $this->core->getUserInfo('username', null, true),
                'request' => json_encode($hapus_data),
                'response_resep' => '',
                'response_obat' => ''
              ];
              
              $response = $bpjsService->post($endpoint, $hapus_data, $this->consid, $this->secretkey, $this->user_key, $api_url);
              
              // Log response dari API hapus resep
              error_log('Hapus resep API response: ' . json_encode($response));
              
              // Update status log berdasarkan response
              if ($response && isset($response['metaData']['code']) && $response['metaData']['code'] == '200') {
                $log_data['status'] = 'success';
                $log_data['response_resep'] = json_encode($response);
              } else {
                $log_data['status'] = 'error';
                $log_data['response_resep'] = json_encode($response ?: ['error' => 'No response from API']);
              }
              
              // Simpan log ke database
              $this->db('mlite_apotek_online_log')->save($log_data);
              
              // Validasi response API
              if (!$response || (isset($response['metaData']['code']) && $response['metaData']['code'] != '200')) {
                $api_success = false;
                $api_message = isset($response['metaData']['message']) ? $response['metaData']['message'] : 'Gagal menghapus resep dari API BPJS';
                error_log('Error: Gagal menghapus resep dari API BPJS untuk noresep: ' . $log['noresep'] . ' - ' . $api_message);
                
                // Jika API gagal, jangan hapus log lokal
                echo json_encode([
                  'success' => false,
                  'message' => 'Gagal menghapus resep dari sistem BPJS: ' . $api_message
                ]);
                exit();
              } else {
                $api_message = 'Resep berhasil dihapus dari sistem BPJS';
              }
            } else {
              $api_success = false;
              $api_message = 'Data tidak lengkap untuk menghapus resep dari API BPJS';
              echo json_encode([
                'success' => false,
                'message' => $api_message
              ]);
              exit();
            }
          } else {
            $api_success = false;
            $api_message = 'Data request tidak valid untuk menghapus resep dari API BPJS';
            echo json_encode([
              'success' => false,
              'message' => $api_message
            ]);
            exit();
          }
        } catch (\Exception $e) {
          // Jika ada error saat hapus dari API, jangan lanjutkan hapus log lokal
          error_log('Error saat hapus resep dari API: ' . $e->getMessage());
          echo json_encode([
            'success' => false,
            'message' => 'Terjadi kesalahan saat menghapus resep dari sistem BPJS: ' . $e->getMessage()
          ]);
          exit();
        }
      }

      // Hapus log dari database lokal hanya jika API berhasil atau tidak ada noresep
      $result = $this->db('mlite_apotek_online_log')->where('id', $id)->delete();
      
      if ($result) {
        $message = 'Log berhasil dihapus';
        if (!empty($log['noresep']) && $api_success) {
          $message .= ' dan ' . $api_message;
        }
        echo json_encode([
          'success' => true,
          'message' => $message
        ]);
      } else {
        echo json_encode([
          'success' => false,
          'message' => 'Gagal menghapus log dari database lokal'
        ]);
      }
      
    } catch (\Exception $e) {
      error_log('Error in postHapusLogApotikOnline: ' . $e->getMessage());
      echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
      ]);
    }
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
