<?php
namespace Plugins\Pasien;

use Systems\AdminModule;

class Admin extends AdminModule
{
    private $_uploads = WEBAPPS_PATH.'/berkasrawat/pages/upload';
    protected $assign = [];

    public function navigation()
    {
        return [
            'Kelola'   => 'manage',
        ];
    }

    public function getManage()
    {
        $this->core->addJS(url(MODULES.'/dashboard/js/admin/webcam.js?v={$mlite.version}'));
        $this->_addHeaderFiles();

        if(isset($_POST['cari'])) $_GET['s'] = $_POST['cari'];
        if(isset($_POST['halaman'])) $_GET['page'] = $_POST['halaman'];

        $result = $this->apiList();

        if (isset($result['status']) && $result['status'] == 'error') {
            $pasien = [];
            $meta = [
                'page' => 1,
                'per_page' => 10,
                'total' => 0
            ];
        } else {
            $pasien = $result['data'];
            $meta = $result['meta'];
        }

        $halaman = $meta['page'];
        $jumlah_data = $meta['total'];
        $offset = $meta['per_page'];
        $jml_halaman = ceil($jumlah_data / $offset);

        $cek_vclaim = $this->db('mlite_modules')->where('dir', 'vclaim')->oneArray();
        $cek_pcare = $this->db('mlite_modules')->where('dir', 'pcare')->oneArray();

        return $this->draw('manage.html', [
          'pasien' => $pasien,
          'halaman' => $halaman,
          'jumlah_data' => $jumlah_data,
          'jml_halaman' => $jml_halaman,
          'cek_vclaim' => $cek_vclaim,
          'cek_pcare' => $cek_pcare,
          'offset' => $offset,
          'admin_mode' => $this->settings->get('settings.admin_mode'),
          'mlite_crud_permissions' => $this->core->loadCrudPermissions('pasien'),
          'token' => $_SESSION['token']
        ]);
    }

    public function anyForm()
    {
      $cek_pcare = $this->db('mlite_modules')->where('dir', 'pcare')->oneArray();
      $usernamePcare = '';
      if($cek_pcare) {
        $usernamePcare = $this->settings('pcare', 'usernamePcare');
      }
      $penjab = $this->db('penjab')->where('status', '1')->toArray();
      $stts_nikah = array('BELUM MENIKAH','MENIKAH','JANDA','DUDHA','JOMBLO');
      $agama = array('ISLAM', 'KRISTEN', 'PROTESTAN', 'HINDU', 'BUDHA', 'KONGHUCU', 'KEPERCAYAAN');
      $pnd = array('TS','TK','SD','SMP','SMA','SLTA/SEDERAJAT','D1','D2','D3','D4','S1','S2','S3','-');
      $keluarga = array('AYAH','IBU','ISTRI','SUAMI','SAUDARA','ANAK');
      if (isset($_POST['no_rkm_medis'])){
        $pasien = $this->db('pasien')->where('no_rkm_medis', $_POST['no_rkm_medis'])->oneArray();
        $pasien['propinsi'] = $this->db('propinsi')->where('kd_prop', $pasien['kd_prop'])->oneArray();
        $pasien['kabupaten'] = $this->db('kabupaten')->where('kd_kab', $pasien['kd_kab'])->oneArray();
        $pasien['kecamatan'] = $this->db('kecamatan')->where('kd_kec', $pasien['kd_kec'])->oneArray();
        $pasien['kelurahan'] = $this->db('kelurahan')->where('kd_kel', $pasien['kd_kel'])->oneArray();
        echo $this->draw('form.html', [
          'pasien' => $pasien,
          'penjab' => $penjab,
          'stts_nikah' => $stts_nikah,
          'agama' => $agama,
          'pnd' => $pnd,
          'keluarga' => $keluarga,
          'no_rkm_medis_baru' => $this->core->setNoRM(),
          'waapitoken' => $this->settings->get('wagateway.token'),
          'waapiphonenumber' => $this->settings->get('wagateway.phonenumber'),
          'admin_mode' => $this->settings->get('settings.admin_mode'),
          'urlUploadPhoto' => url([ADMIN,'pasien','uploadphoto',$_POST['no_rkm_medis']]),
          'cek_pcare' => $cek_pcare,
          'usernamePcare' => $usernamePcare,
          'mlite_crud_permissions' => $this->core->loadCrudPermissions('pasien')
        ]);
      } else {
        $pasien = [
          'no_rkm_medis' => '',
          'nm_pasien' => '',
          'no_ktp' => '',
          'jk' => '',
          'tmp_lahir' => '',
          'tgl_lahir' => '',
          'nm_ibu' => '-',
          'alamat' => '',
          'gol_darah' => '-',
          'pekerjaan' => '-',
          'stts_nikah' => '',
          'agama' => 'ISLAM',
          'tgl_daftar' => date('Y-m-d'),
          'no_tlp' => '',
          'umur' => '',
          'pnd' => '-',
          'keluarga' => '',
          'namakeluarga' => '-',
          'kd_pj' => '',
          'no_peserta' => '',
          'kd_kel' => '1',
          'kd_kec' => '1',
          'kd_kab' => '1',
          'pekerjaanpj' => '',
          'alamatpj' => '',
          'kelurahanpj' => '',
          'kecamatanpj' => '',
          'kabupatenpj' => '',
          'perusahaan_pasien' => '',
          'suku_bangsa' => '',
          'bahasa_pasien' => '',
          'cacat_fisik' => '',
          'email' => '-',
          'nip' => '',
          'kd_prop' => '1',
          'propinsipj' => '',
          'propinsi' => ['nm_prop' => '-'],
          'kabupaten' => ['nm_kab' => '-'],
          'kecamatan' => ['nm_kec' => '-'],
          'kelurahan' => ['nm_kel' => '-']
        ];
        echo $this->draw('form.html', [
          'pasien' => $pasien,
          'penjab' => $penjab,
          'stts_nikah' => $stts_nikah,
          'agama' => $agama,
          'pnd' => $pnd,
          'keluarga' => $keluarga,
          'no_rkm_medis_baru' => $this->core->setNoRM(),
          'waapitoken' => $this->settings->get('wagateway.token'),
          'waapiphonenumber' => $this->settings->get('wagateway.phonenumber'),
          'admin_mode' => $this->settings->get('settings.admin_mode'),
          'urlUploadPhoto' => '',
          'cek_pcare' => $cek_pcare,
          'usernamePcare' => $usernamePcare,
          'mlite_crud_permissions' => $this->core->loadCrudPermissions('pasien')
        ]);
      }
      exit();
    }

    public function postMaxid()
    {
      echo $this->core->setNoRM();
      exit();
    }

    public function postSave()
    {
      $mlite_crud_permissions = $this->core->loadCrudPermissions('pasien');
      $_POST['tgl_daftar'] = date('Y-m-d H:i', strtotime($_POST['tgl_daftar']));
      $pasien = $this->db('pasien')->where('no_rkm_medis', $_POST['no_rkm_medis'])->oneArray();
      $cek_prop = $this->db('propinsi')->where('kd_prop', $_POST['kd_prop'])->oneArray();
      if(!$cek_prop){
        $this->db('propinsi')->save(['kd_prop' => $_POST['kd_prop'], 'nm_prop' => $_POST['nm_prop']]);
      }
      $cek_kab = $this->db('kabupaten')->where('kd_kab', $_POST['kd_kab'])->oneArray();
      if(!$cek_kab){
        $this->db('kabupaten')->save(['kd_kab' => $_POST['kd_kab'], 'nm_kab' => $_POST['nm_kab']]);
      }
      $cek_kec = $this->db('kecamatan')->where('kd_kec', $_POST['kd_kec'])->oneArray();
      if(!$cek_kec){
        $this->db('kecamatan')->save(['kd_kec' => $_POST['kd_kec'], 'nm_kec' => $_POST['nm_kec']]);
      }
      $cek_kel = $this->db('kelurahan')->where('nm_kel', $_POST['nm_kel'])->oneArray();
      if(!$cek_kel){
        $result = $this->db('kelurahan')->select('kd_kel')->desc('kd_kel')->limit(1)->oneArray();
        $_POST['kd_kel'] = $result['kd_kel'] + 1;
        $this->db('kelurahan')->save(['kd_kel' => $_POST['kd_kel'], 'nm_kel' => $_POST['nm_kel']]);
      }

      $manual = $_POST['manual'];
      unset($_POST['manual']);

      if (!$pasien) {
        if ($mlite_crud_permissions['can_create'] == 'false') {
           echo json_encode(['status' => 'error', 'msg' => 'Anda tidak memiliki hak akses untuk menambah data!']);
           exit();
        }
        if($manual == '0') {
          $_POST['no_rkm_medis'] = $this->core->setNoRM();
        }
        $_POST['tmp_lahir'] = '-';
        $_POST['umur'] = $this->hitungUmur($_POST['tgl_lahir']);
        $_POST['pekerjaanpj'] = '-';
        $_POST['alamatpj'] = $_POST['alamat'];
        $_POST['kelurahanpj'] = $_POST['nm_kel'];
        $_POST['kecamatanpj'] = $_POST['nm_kec'];
        $_POST['kabupatenpj'] = $_POST['nm_kab'];
        $_POST['perusahaan_pasien'] = '-';
        $_POST['suku_bangsa'] = '1';
        $_POST['bahasa_pasien'] = '1';
        $_POST['cacat_fisik'] = '1';
        $_POST['nip'] = '-';
        $_POST['propinsipj'] = $_POST['nm_prop'];
        unset($_POST['nm_prop']);
        unset($_POST['nm_kab']);
        unset($_POST['nm_kec']);
        unset($_POST['nm_kel']);
        $query = $this->db('pasien')->save($_POST);

        if($query) {
          if($manual == '0') {
            $this->db()->pdo()->exec("UPDATE set_no_rkm_medis SET no_rkm_medis='$_POST[no_rkm_medis]'");
          }
          $data['status'] = 'success';
          echo json_encode($data);
        } else {
          $data['status'] = 'error';
          $data['msg'] = $query->errorInfo()['2'];
          echo json_encode($data);
        }
  
      } else {
        if ($mlite_crud_permissions['can_update'] == 'false') {
           echo json_encode(['status' => 'error', 'msg' => 'Anda tidak memiliki hak akses untuk mengubah data!']);
           exit();
        }
        unset($_POST['nm_prop']);
        unset($_POST['nm_kab']);
        unset($_POST['nm_kec']);
        unset($_POST['nm_kel']);
        $_POST['umur'] = $this->hitungUmur($_POST['tgl_lahir']);
        $query = $this->db('pasien')->where('no_rkm_medis', $_POST['no_rkm_medis'])->update($_POST);

        if($query) {
          $data['status'] = 'success';
          echo json_encode($data);
        } else {
          $data['status'] = 'error';
          $data['msg'] = $query->errorInfo()['2'];
          echo json_encode($data);
        }
  
      }

      exit();
    }

    public function getUploadPhoto()
    {
      $no_rkm_medis = parseURL()[2];
      $this->core->addJS(url(MODULES.'/dashboard/js/admin/webcam.js?v={$mlite.version}'));
      return $this->draw('uploadphoto.html', ['no_rkm_medis' => $no_rkm_medis]);
    }

    public function postSavePhoto($no_rkm_medis = null)
    {

      /*if($_FILES['file']['name'] != ''){
          $test = explode('.', $_FILES['file']['name']);
          $extension = end($test);
          $name = $_POST['no_rkm_medis'].'.'.$extension;

          $location = UPLOADS.'/'.$name;
          move_uploaded_file($_FILES['file']['tmp_name'], $location);

          echo '<img src="'.url().'/uploads/'.$name.'" height="100" width="100" />';
      }*/

        if($no_rkm_medis != null) {
          $_POST['no_rkm_medis'] = $no_rkm_medis;
        }

        $personal_pasien = $this->db('personal_pasien')->where('no_rkm_medis', $_POST['no_rkm_medis'])->oneArray();

        if (($photo = isset_or($_FILES['file']['tmp_name'], false)) || !$_POST['no_rkm_medis']) {
            $img = new \Systems\Lib\Image;
            if ($img->load($photo)) {
                if ($img->getInfos('width') < $img->getInfos('height')) {
                    $img->crop(0, 0, $img->getInfos('width'), $img->getInfos('width'));
                } else {
                    $img->crop(0, 0, $img->getInfos('height'), $img->getInfos('height'));
                }

                if ($img->getInfos('width') > 512) {
                    $img->resize(512, 512);
                }

                $gambar = "pages/upload/".uniqid('photo').".".$img->getInfos('type');
                //$gambar = "pages/upload/".$_POST['no_rkm_medis'].".".$img->getInfos('type');
            }

        }

        //if (($photo = isset_or($_FILES['webcam']['tmp_name'], false)) || !$_POST['no_rkm_medis']) {
        if ($photo = isset_or($_FILES['webcam']['tmp_name'], false)) {
            $img = new \Systems\Lib\Image;
            if ($img->load($photo)) {
                if ($img->getInfos('width') < $img->getInfos('height')) {
                    $img->crop(0, 0, $img->getInfos('width'), $img->getInfos('width'));
                } else {
                    $img->crop(0, 0, $img->getInfos('height'), $img->getInfos('height'));
                }

                if ($img->getInfos('width') > 512) {
                    $img->resize(512, 512);
                }

                $gambar = "pages/upload/".uniqid('photo').".".$img->getInfos('type');
                //$gambar = "pages/upload/".$_POST['no_rkm_medis'].".".$img->getInfos('type');
            }

        }

        if ($personal_pasien == 0) {
          $this->db()->pdo()->exec("INSERT INTO `personal_pasien` (`no_rkm_medis`, `gambar`, `password`) VALUES ('{$_POST['no_rkm_medis']}', '$gambar', AES_ENCRYPT('{$_POST['no_rkm_medis']}','windi'))");
        } else{
          $this->db('personal_pasien')->where('no_rkm_medis', $_POST['no_rkm_medis'])->update(['gambar' => $gambar]);
        }

        if (isset($img) && $img->getInfos('width')) {
            if ($personal_pasien) {
                unlink(WEBAPPS_PATH."/photopasien/".$personal_pasien['gambar']);
            }

            $img->save(WEBAPPS_PATH."/photopasien/".$gambar);

            echo '<img src="'.WEBAPPS_URL.'/photopasien/'.$gambar.'" height="100" width="100" />';

        }

        exit();

    }

    public function postHapus()
    {
      $mlite_crud_permissions = $this->core->loadCrudPermissions('pasien');
      if ($mlite_crud_permissions['can_delete'] == 'false') {
         echo json_encode(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses untuk menghapus data!']);
         exit();
      }

      try {
        $query = $this->db('pasien')->where('no_rkm_medis', $_POST['no_rkm_medis'])->delete();
        if($query) {
          echo json_encode(['status' => 'success', 'message' => 'Data berhasil dihapus']);
        } else {
          echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data']);
        }
      } catch (\Throwable $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
      }
      exit();
    }

    public function getCetakKartu($no_rkm_medis)
    {
      $kartu['settings'] = $this->settings('settings');
      $kartu['pasien'] = $this->db('pasien')->where('no_rkm_medis', $no_rkm_medis)->oneArray();
      $this->tpl->set('kartu', $this->tpl->noParse_array(htmlspecialchars_array($kartu)));
      echo $this->draw('kartu.html');

      $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => [100, 70], 
        'margin_left' => 4,
        'margin_right' => 4,
        'margin_top' => 4,
        'margin_bottom' => 4
      ]);

      $url = url(ADMIN.'/tmp/kartu.html');
      $html = file_get_contents($url);
      $mpdf->WriteHTML($html);

      // Output a PDF file directly to the browser
      $mpdf->Output();
            
      exit();
    }

    public function getFolder($no_rkm_medis, $no_rawat='')
    {
      $this->_addHeaderFiles();
      $pasien = $this->db('pasien')->where('no_rkm_medis', $no_rkm_medis)->oneArray();
      $reg_periksa = $this->db('reg_periksa')
        ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
        ->join('dokter', 'dokter.kd_dokter=reg_periksa.kd_dokter')
        ->where('no_rkm_medis', $no_rkm_medis)
        ->toArray();
      $no_rawat_array = [];
      foreach($reg_periksa as $row) {
        $no_rawat_array[] = $row['no_rawat'];
      }
      $berkas_digital_perawatan = [];
      if (!empty($no_rawat_array)) {
        $berkas_digital_perawatan = $this->db('berkas_digital_perawatan')
          ->join('master_berkas_digital', 'master_berkas_digital.kode = berkas_digital_perawatan.kode')
          ->in('no_rawat', $no_rawat_array)
          ->toArray();
      }
      if($no_rawat) {
        $berkas_digital_perawatan = $this->db('berkas_digital_perawatan')
        ->join('master_berkas_digital', 'master_berkas_digital.kode = berkas_digital_perawatan.kode')
        ->where('no_rawat', revertNoRawat($no_rawat))
        ->toArray();
      }
      $master_berkas_digital = $this->db('master_berkas_digital')->toArray();
      return $this->draw('folder.html', ['pasien' => $pasien, 'reg_periksa' => $reg_periksa, 'berkas_digital_perawatan' => $berkas_digital_perawatan, 'master_berkas_digital' => $master_berkas_digital]);
    }

    public function postSaveBerkasDigital()
    {

      if(MULTI_APP) {

        $curl = curl_init();
        $filePath = $_FILES['file']['tmp_name'];

        curl_setopt_array($curl, array(
          CURLOPT_URL => substr(rtrim(WEBAPPS_URL, '/'), 0, strrpos(rtrim(WEBAPPS_URL, '/'), '/')).'/api/berkasdigital',
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

        $id = convertNorawat($_POST['no_rawat']);
        $temp = explode(".", $_FILES["file"]["name"]);
        $imgName = time().$cntr++;
        $lokasi_file = 'pages/upload/'.$id.'_'.$imgName.'.'.end($temp);
        $FileName = $id.'_'.$imgName.'.'.end($temp);
        $tmpFileName = $_FILES['file']['tmp_name'];
        $uploaded = move_uploaded_file($tmpFileName, $dir.'/'.$FileName);
        if($uploaded) {
            $query = $this->db('berkas_digital_perawatan')->save(['no_rawat' => $_POST['no_rawat'], 'kode' => $_POST['kode'], 'lokasi_file' => $lokasi_file]);
            if($query) {
              echo '<br><img src="'.WEBAPPS_URL.'/berkasrawat/'.$lokasi_file.'" width="150" />';
            }          
        } else {
          echo 'Upload gagal';
        }
      }
      exit();

    }

    public function postHapusBerkasDigital()
    {
      if (file_exists(UPLOADS.'/berkasrawat/'.$_POST['lokasi_file'])) {
        $hapus = unlink(UPLOADS.'/berkasrawat/'.$_POST['lokasi_file']);
        if($hapus) {
          $this->db('berkas_digital_perawatan')->where('lokasi_file', $_POST['lokasi_file'])->delete();
        }
      }
      exit();
    }

    public function getDownloadBerkasDigital()
    {
      $file = explode('/', $_GET['lokasi_file']);
      $file_name = $file['2'];
      $file_url = WEBAPPS_URL.'/berkasrawat/' . $_GET['lokasi_file'];
      
      // Configure.
      header('Content-Type: application/octet-stream');
      header("Content-Transfer-Encoding: Binary"); 
      header("Content-disposition: attachment; filename=\"".$file_name."\"");
      
      // Actual download.
      readfile($file_url);

      exit();
    }

    public function getVclaim_ByNoKartu($noKartu, $tglPelayananSEP)
    {
      $url = url([ADMIN, 'vclaim', 'bynokartu', $noKartu, $tglPelayananSEP]);
      echo $this->draw('vclaim.bynokartu.html', ['url' => $url]);
      exit();
    }

    public function getVclaim_ByNIK($nik, $tglPelayananSEP)
    {
      $url = url([ADMIN, 'vclaim', 'bynik', $nik, $tglPelayananSEP]);
      echo $this->draw('vclaim.bynik.html', ['url' => $url]);
      exit();
    }

    public function getPcare_ByNoKartu($noKartu)
    {
      $url = url([ADMIN, 'pcare', 'byjeniskartu', 'noka', $noKartu]);
      echo $this->draw('pcare.bynokartu.html', ['url' => $url]);
      exit();
    }

    public function getPcare_ByNIK($nik)
    {
      $url = url([ADMIN, 'pcare', 'byjeniskartu', 'nik', $nik]);
      echo $this->draw('pcare.bynik.html', ['url' => $url]);
      exit();
    }

    public function getRiwayatPerawatan($no_rkm_medis)
    {
        $riwayat = $this->_getRiwayatData($no_rkm_medis);
        $this->tpl->set('riwayat', $this->tpl->noParse_array(htmlspecialchars_array($riwayat)));
        echo $this->draw('riwayat.perawatan.html');
        exit();
    }

    public function apiRiwayatPerawatan($no_rkm_medis = null)
    {
        $username = $this->core->checkAuth('GET');
        if (!$this->core->checkPermission($username, 'can_read', 'pasien')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        if (!$no_rkm_medis) {
            return ['status' => 'error', 'message' => 'no_rkm_medis required'];
        }
        
        $data = $this->_getRiwayatData($no_rkm_medis);
        if (!$data['pasien']) {
             return ['status' => 'error', 'message' => 'Pasien not found'];
        }

        return ['status' => 'success', 'data' => $data];
    }

    private function _getRiwayatData($no_rkm_medis)
    {
      $riwayat['settings'] = $this->settings('settings');
      $riwayat['pasien'] = $this->db('pasien')->where('no_rkm_medis', $no_rkm_medis)->oneArray();
      $reg_periksa = $this->db('reg_periksa')
        ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
        ->join('dokter', 'dokter.kd_dokter=reg_periksa.kd_dokter')
        ->join('penjab', 'penjab.kd_pj=reg_periksa.kd_pj')
        ->where('no_rkm_medis', $no_rkm_medis)
        ->desc('tgl_registrasi')
        ->toArray();

      $riwayat['reg_periksa'] = [];
      foreach ($reg_periksa as $row) {

        $row['diagnosa_pasien'] = $this->db('diagnosa_pasien')
          ->join('penyakit', 'penyakit.kd_penyakit=diagnosa_pasien.kd_penyakit')
          ->where('no_rawat', $row['no_rawat'])
          ->asc('prioritas')
          ->toArray();
        $row['prosedur_pasien'] = $this->db('prosedur_pasien')
          ->join('icd9', 'icd9.kode=prosedur_pasien.kode')
          ->where('no_rawat', $row['no_rawat'])
          ->asc('prioritas')
          ->toArray();
        $row['pemeriksaan_ralan'] = $this->db('pemeriksaan_ralan')->where('no_rawat', $row['no_rawat'])->toArray();
        $row['rawat_jl_dr'] = $this->db('rawat_jl_dr')
          ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_jl_dr.kd_jenis_prw')
          ->join('dokter', 'dokter.kd_dokter=rawat_jl_dr.kd_dokter')
          ->where('no_rawat', $row['no_rawat'])
          ->desc('tgl_perawatan')
          ->desc('jam_rawat')
          ->toArray();
        $row['rawat_jl_pr'] = $this->db('rawat_jl_pr')
          ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_jl_pr.kd_jenis_prw')
          ->join('petugas', 'petugas.nip=rawat_jl_pr.nip')
          ->where('no_rawat', $row['no_rawat'])
          ->desc('tgl_perawatan')
          ->desc('jam_rawat')
          ->toArray();
        $rawat_jl_drpr_data = $this->db('rawat_jl_drpr')
          ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_jl_drpr.kd_jenis_prw')
          ->where('no_rawat', $row['no_rawat'])
          ->desc('tgl_perawatan')
          ->desc('jam_rawat')
          ->toArray();
        $row['rawat_jl_drpr'] = [];
        foreach ($rawat_jl_drpr_data as $drpr_item) {
          $dokter = $this->db('dokter')->where('kd_dokter', $drpr_item['kd_dokter'])->oneArray();
          $petugas = $this->db('petugas')->where('nip', $drpr_item['nip'])->oneArray();
          $drpr_item['nm_dokter'] = $dokter['nm_dokter'] ?? '';
          $drpr_item['nama'] = $petugas['nama'] ?? '';
          $row['rawat_jl_drpr'][] = $drpr_item;
        }
        $row['pemeriksaan_ranap'] = [];
        $row['rawat_inap_dr'] = [];
        $row['rawat_inap_pr'] = [];
        $row['rawat_inap_drpr'] = [];
        $check_table = $this->db()->pdo()->query("SHOW TABLES LIKE 'pemeriksaan_ranap'");
        $check_table->execute();
        $check_table = $check_table->fetch();
        if($check_table) {
          $row['pemeriksaan_ranap'] = $this->db('pemeriksaan_ranap')
            ->where('no_rawat', $row['no_rawat'])
            ->desc('tgl_perawatan')
          ->desc('jam_rawat')
            ->toArray();
          $row['rawat_inap_dr'] = $this->db('rawat_inap_dr')
            ->join('jns_perawatan_inap', 'jns_perawatan_inap.kd_jenis_prw=rawat_inap_dr.kd_jenis_prw')
            ->join('dokter', 'dokter.kd_dokter=rawat_inap_dr.kd_dokter')
            ->where('no_rawat', $row['no_rawat'])
            ->desc('tgl_perawatan')
          ->desc('jam_rawat')
            ->toArray();
          $row['rawat_inap_pr'] = $this->db('rawat_inap_pr')
            ->join('jns_perawatan_inap', 'jns_perawatan_inap.kd_jenis_prw=rawat_inap_pr.kd_jenis_prw')
            ->join('petugas', 'petugas.nip=rawat_inap_pr.nip')
            ->where('no_rawat', $row['no_rawat'])
            ->desc('tgl_perawatan')
             ->desc('jam_rawat')
            ->toArray();
          $rawat_inap_drpr_data = $this->db('rawat_inap_drpr')
            ->join('jns_perawatan_inap', 'jns_perawatan_inap.kd_jenis_prw=rawat_inap_drpr.kd_jenis_prw')
            ->where('no_rawat', $row['no_rawat'])
            ->desc('tgl_perawatan')
            ->desc('jam_rawat')
            ->toArray();
          foreach ($rawat_inap_drpr_data as $inap_drpr_item) {
            $dokter = $this->db('dokter')->where('kd_dokter', $inap_drpr_item['kd_dokter'])->oneArray();
            $petugas = $this->db('petugas')->where('nip', $inap_drpr_item['nip'])->oneArray();
            $inap_drpr_item['nm_dokter'] = $dokter['nm_dokter'] ?? '';
            $inap_drpr_item['nama'] = $petugas['nama'] ?? '';
            $row['rawat_inap_drpr'][] = $inap_drpr_item;
          }
        }

        $rows_periksa_lab = $this->db('periksa_lab')
          ->join('jns_perawatan_lab', 'jns_perawatan_lab.kd_jenis_prw=periksa_lab.kd_jenis_prw')
          ->where('no_rawat', $row['no_rawat'])
          ->desc('tgl_periksa')
          ->desc('jam')
          ->toArray();

        $row['periksa_lab'] = [];
        foreach ($rows_periksa_lab as $value) {
          $value['detail_periksa_lab'] = $this->db('detail_periksa_lab')
            ->join('template_laboratorium', 'template_laboratorium.id_template=detail_periksa_lab.id_template')
            ->where('detail_periksa_lab.no_rawat', $value['no_rawat'])
            ->where('detail_periksa_lab.kd_jenis_prw', $value['kd_jenis_prw'])
            ->where('tgl_periksa', $value['tgl_periksa'])
            ->where('jam', $value['jam'])
            ->toArray();
          $row['periksa_lab'][] = $value;
        }

        $row['periksa_radiologi'] = [];
        $radiologi_sessions = $this->db('periksa_radiologi')
          ->select(['tgl_periksa', 'jam', 'nip', 'kd_jenis_prw'])
          ->where('periksa_radiologi.no_rawat', $row['no_rawat'])
          ->group('tgl_periksa')
          ->group('jam')
          ->group('nip')
          ->group('kd_jenis_prw')
          ->desc('tgl_periksa')
            ->desc('jam')
          ->toArray();

        foreach ($radiologi_sessions as $radiologi_session) {
          $radiologi_session['no_rawat'] = $row['no_rawat'];
          $radiologi_session['pemeriksaan_radiologi'] = $this->db('periksa_radiologi')
            ->join('jns_perawatan_radiologi', 'jns_perawatan_radiologi.kd_jenis_prw=periksa_radiologi.kd_jenis_prw')
            ->where('no_rawat', $row['no_rawat'])
            ->where('tgl_periksa', $radiologi_session['tgl_periksa'])
            ->where('jam', $radiologi_session['jam'])
            ->asc('periksa_radiologi.kd_jenis_prw')
            ->toArray();
          $radiologi_session['hasil_radiologi'] = $this->db('hasil_radiologi')
            ->where('no_rawat', $row['no_rawat'])
            ->where('tgl_periksa', $radiologi_session['tgl_periksa'])
            ->where('jam', $radiologi_session['jam'])
            ->toArray();
          $radiologi_session['gambar_radiologi'] = $this->db('gambar_radiologi')
            ->where('no_rawat', $row['no_rawat'])
            ->where('tgl_periksa', $radiologi_session['tgl_periksa'])
            ->where('jam', $radiologi_session['jam'])
            ->toArray();
          $row['periksa_radiologi'][] = $radiologi_session;
        }

        $pemberian_obat_sessions = $this->db('detail_pemberian_obat')
          ->select(['tgl_perawatan', 'jam'])
          ->where('no_rawat', $row['no_rawat'])
          ->group('tgl_perawatan')
          ->group('jam')
          ->desc('tgl_perawatan')
          ->desc('jam')
          ->toArray();

        $row['pemberian_obat'] = [];
        foreach ($pemberian_obat_sessions as $obat_session) {
          $obat_session['no_rawat'] = $row['no_rawat'];
          $obat_session['data_pemberian_obat'] = $this->db('detail_pemberian_obat')
            ->join('databarang', 'databarang.kode_brng=detail_pemberian_obat.kode_brng')
            ->where('detail_pemberian_obat.no_rawat', $row['no_rawat'])
            ->where('detail_pemberian_obat.tgl_perawatan', $obat_session['tgl_perawatan'])
            ->where('detail_pemberian_obat.jam', $obat_session['jam'])
            ->asc('detail_pemberian_obat.kode_brng')
            ->toArray();
          $row['pemberian_obat'][] = $obat_session;
        }

        $row['operasi'] = $this->db('operasi')
          ->join('paket_operasi', 'paket_operasi.kode_paket=operasi.kode_paket')
          ->where('no_rawat', $row['no_rawat'])
          ->desc('tgl_operasi')
          ->toArray();

        $row['obat_operasi'] = $this->db('beri_obat_operasi')
          ->join('obatbhp_ok', 'obatbhp_ok.kd_obat=beri_obat_operasi.kd_obat')
          ->where('no_rawat', $row['no_rawat'])
          ->desc('tanggal')
          ->toArray();

        $row['catatan_perawatan'] = $this->db('catatan_perawatan')->where('no_rawat', $row['no_rawat'])->oneArray();
        $row['berkas_digital'] = $this->db('berkas_digital_perawatan')
          ->join('master_berkas_digital', 'master_berkas_digital.kode=berkas_digital_perawatan.kode')
          ->where('no_rawat', $row['no_rawat'])
          ->toArray();

        $row['penilaian_medis_ralan'] = $this->db('penilaian_medis_ralan')
        ->join('dokter', 'dokter.kd_dokter=penilaian_medis_ralan.kd_dokter')
        ->where('no_rawat', $row['no_rawat'])
        ->toArray();

        $row['penilaian_medis_igd'] = $this->db('penilaian_medis_igd')
        ->join('dokter', 'dokter.kd_dokter=penilaian_medis_igd.kd_dokter')
        ->where('no_rawat', $row['no_rawat'])
        ->desc('tanggal')
        ->toArray();

        $row['penilaian_medis_ranap'] = $this->db('penilaian_medis_ranap')
        ->join('dokter', 'dokter.kd_dokter=penilaian_medis_ranap.kd_dokter')
        ->where('no_rawat', $row['no_rawat'])
        ->desc('tanggal')
        ->toArray();

        $row['triase_igd'] = $this->db('mlite_triase_igd')
          ->where('no_rawat', $row['no_rawat'])
          ->desc('tgl_triase')
          ->toArray();

        $row['penilaian_keperawatan_igd'] = $this->db('penilaian_awal_keperawatan_igd')
          ->join('petugas', 'petugas.nip=penilaian_awal_keperawatan_igd.nip')
          ->where('no_rawat', $row['no_rawat'])
          ->desc('tanggal')
          ->toArray();

        $row['penilaian_awal_keperawatan_ralan'] = $this->db('penilaian_awal_keperawatan_ralan')
          ->join('petugas', 'petugas.nip=penilaian_awal_keperawatan_ralan.nip')
          ->where('no_rawat', $row['no_rawat'])
          ->desc('tanggal')
          ->toArray();

        $row['penilaian_awal_keperawatan_ranap'] = $this->db('penilaian_awal_keperawatan_ranap')
          ->join('petugas', 'petugas.nip=penilaian_awal_keperawatan_ranap.nip1')
          ->where('no_rawat', $row['no_rawat'])
          ->desc('tanggal')
          ->toArray();

        $row['catatan_adime_gizi'] = $this->db('catatan_adime_gizi')
          ->join('petugas', 'petugas.nip=catatan_adime_gizi.nip')
          ->where('no_rawat', $row['no_rawat'])
          ->desc('tanggal')
          ->toArray();

        $row['penilaian_ulang_nyeri'] = $this->db('penilaian_ulang_nyeri')
          ->join('petugas', 'petugas.nip=penilaian_ulang_nyeri.nip')
          ->where('no_rawat', $row['no_rawat'])
          ->desc('tanggal')
          ->toArray();

        $row['resume_pasien'] = $this->db('resume_pasien')
          ->join('dokter', 'dokter.kd_dokter=resume_pasien.kd_dokter')
          ->where('no_rawat', $row['no_rawat'])
          ->toArray();

        $row['laporan_operasi'] = $this->db('laporan_operasi')
          ->where('no_rawat', $row['no_rawat'])
          ->desc('tanggal')
          ->toArray();

        $row['mlite_odontogram'] = $this->db('mlite_odontogram')
          ->where('no_rkm_medis', $no_rkm_medis)
          ->where('tgl_input', $row['tgl_registrasi'])
          ->desc('tgl_input')
          ->toArray();

        $riwayat['reg_periksa'][] = $row;
      }
      
      return $riwayat;
    }

    public function postCetak()
    {
      $this->db()->pdo()->exec("DELETE FROM `mlite_temporary`");
      $cari = $_POST['cari'];
      $this->db()->pdo()->exec("INSERT INTO `mlite_temporary` (
        `temp1`,
        `temp2`,
        `temp3`,
        `temp4`,
        `temp5`,
        `temp6`,
        `temp7`,
        `temp8`,
        `temp9`,
        `temp10`,
        `temp11`,
        `temp12`,
        `temp13`,
        `temp14`,
        `temp15`,
        `temp16`,
        `temp17`,
        `temp18`,
        `temp19`,
        `temp20`,
        `temp21`,
        `temp22`,
        `temp23`,
        `temp24`,
        `temp25`,
        `temp26`,
        `temp27`,
        `temp28`,
        `temp29`,
        `temp30`,
        `temp31`,
        `temp32`,
        `temp33`,
        `temp34`,
        `temp35`,
        `temp36`
      )
      SELECT *
      FROM `pasien`
      WHERE (`no_rkm_medis` LIKE '%$cari%' OR `nm_pasien` LIKE '%$cari%' OR `alamat` LIKE '%$cari%')
      ");

      $cetak = $this->db('mlite_temporary')->toArray();
      return $this->draw('cetak.pasien.html', ['cetak' => $cetak]);
      exit();
    }

    public function anyWilayah()
    {
      $show = isset($_GET['show']) ? $_GET['show'] : "";
      switch($show){
      	default:
        break;
        case "caripropinsi":
          if(isset($_POST["query"])){
            $output = '';
            $key = "%".$_POST["query"]."%";
            $rows = $this->db('propinsi')->like('nm_prop', $key)->asc('kd_prop')->limit(10)->toArray();
            $output = '';
            if(count($rows)){
              foreach ($rows as $row) {
                $output .= '<li class="list-group-item link-class">'.$row["kd_prop"].': '.$row["nm_prop"].'</li>';
              }
            }
            echo $output;
          }
        break;
        case "carikabupaten":
          if(isset($_POST["query"])){
            $output = '';
            $key = "%".$_POST["query"]."%";
            $rows = $this->db('kabupaten')->like('nm_kab', $key)->asc('kd_kab')->limit(10)->toArray();
            $output = '';
            if(count($rows)){
              foreach ($rows as $row) {
                $output .= '<li class="list-group-item link-class">'.$row["kd_kab"].': '.$row["nm_kab"].'</li>';
              }
            }
            echo $output;
          }
        break;
        case "carikecamatan":
          if(isset($_POST["query"])){
            $output = '';
            $key = "%".$_POST["query"]."%";
            $rows = $this->db('kecamatan')->like('nm_kec', $key)->asc('kd_kec')->limit(10)->toArray();
            $output = '';
            if(count($rows)){
              foreach ($rows as $row) {
                $output .= '<li class="list-group-item link-class">'.$row["kd_kec"].': '.$row["nm_kec"].'</li>';
              }
            }
            echo $output;
          }
        break;
        case "carikelurahan":
          if(isset($_POST["query"])){
            $output = '';
            $key = "%".$_POST["query"]."%";
            $rows = $this->db('kelurahan')->like('nm_kel', $key)->asc('kd_kel')->limit(10)->toArray();
            $output = '';
            if(count($rows)){
              foreach ($rows as $row) {
                $output .= '<li class="list-group-item link-class">'.$row["kd_kel"].': '.$row["nm_kel"].'</li>';
              }
            }
            echo $output;
          }
        break;
      }
      exit();
    }

    public function hitungUmur($tanggal_lahir)
    {
      	$birthDate = new \DateTime($tanggal_lahir);
      	$today = new \DateTime("today");
      	$umur = "0 Th 0 Bl 0 Hr";
        if ($birthDate < $today) {
        	$y = $today->diff($birthDate)->y;
        	$m = $today->diff($birthDate)->m;
        	$d = $today->diff($birthDate)->d;
          $umur =  $y." Th ".$m." Bl ".$d." Hr";
        }
      	return $umur;
    }

    public function getExportPDF()
    {
      $query = $_GET['query'];
      $tgl_awal = $_GET['tgl_awal'];
      $tgl_akhir = $_GET['tgl_akhir'];
      $filter = $_GET['filter'];

      $sql = "SELECT * FROM pasien";
        if(isset($_GET['tgl_awal']) && isset($_GET['tgl_akhir']) && $_GET['tgl_awal'] !='' && $_GET['tgl_akhir'] !='') {
          $sql .=" WHERE tgl_daftar BETWEEN '$tgl_awal' AND '$tgl_akhir'";
        }
        if(isset($_GET['query']) && $_GET['query'] !='') {
          $sql .=" AND nm_pasien LIKE '%$query%'";
        }
        if(isset($_GET['filter']) && $_GET['filter'] !='') {
          $sql .=" AND kd_pj = '$filter'";
        }
      $stmt = $this->db()->pdo()->prepare($sql);
      $stmt->execute();
      $rows = $stmt->fetchAll();        

      echo $this->draw('pasien.export.pdf.html', ['pasien' => $rows]);

      $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'orientation' => 'L'
      ]);

      $mpdf->SetHTMLHeader($this->core->setPrintHeader());
      $mpdf->SetHTMLFooter($this->core->setPrintFooter());
            
      $url = url(ADMIN.'/tmp/pasien.export.pdf.html');
      $html = file_get_contents($url);
      $mpdf->WriteHTML($this->core->setPrintCss(),\Mpdf\HTMLParserMode::HEADER_CSS);
      $mpdf->WriteHTML($html,\Mpdf\HTMLParserMode::HTML_BODY);

      // Output a PDF file directly to the browser
      $mpdf->Output();
      exit();
    }

    public function getExportXLS()
    {
      echo "Cetak XLS";
      exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $this->assign['websocket'] = $this->settings->get('settings.websocket');
        $this->assign['websocket_proxy'] = $this->settings->get('settings.websocket_proxy');
        echo $this->draw(MODULES.'/pasien/js/admin/pasien.js', ['mlite' => $this->assign]);
        exit();
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        $this->core->addJS(url('assets/jscripts/lightbox/lightbox.min.js'));
        $this->core->addCSS(url('assets/jscripts/lightbox/lightbox.min.css'));
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));
        $this->core->addJS(url([ADMIN, 'pasien', 'javascript']), 'footer');
    }

    public function getSettings()
    {
      $set_no_rkm_medis = $this->db('set_no_rkm_medis')->oneArray();
      return $this->draw('settings.html', ['set_no_rkm_medis' => $set_no_rkm_medis]);
    }

    public function postSaveSettings()
    {
        $this->db()->pdo()->exec("DELETE FROM `set_no_rkm_medis`");
        $set_no_rkm_medis = $this->db('set_no_rkm_medis')->save(['no_rkm_medis' => $_POST['set_no_rkm_medis']]);
        if($set_no_rkm_medis) {
          $this->notify('success', 'Pengaturan telah disimpan');
        } else {
          $this->notify('error', 'Pengaturan gagal disimpan');
        }
        redirect(url([ADMIN, 'pasien', 'settings']));
    }

    public function getCetakMpdf()
    {
      $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'orientation' => 'L'
      ]);

      $mpdf->SetHTMLHeader($this->core->setPrintHeader());
      $mpdf->SetHTMLFooter($this->core->setPrintFooter());
            
      $url = url(ADMIN.'/tmp/cetak.pasien.html');
      $html = file_get_contents($url);
      $mpdf->WriteHTML($this->core->setPrintCss(),\Mpdf\HTMLParserMode::HEADER_CSS);
      $mpdf->WriteHTML($html,\Mpdf\HTMLParserMode::HTML_BODY);

      // Output a PDF file directly to the browser
      $mpdf->Output();
      exit();      
    }

    public function getCetakRiwayatMpdf()
    {
      $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'orientation' => 'L'
      ]);

      $css = '
      <style>
        del { 
          display: none;
        }
        table {
          padding-top: 1cm;
          padding-bottom: 1cm;
          font-size: 10px;
        }
        td, th {
          border-bottom: 1px solid #dddddd;
          padding: 5px;
        }        
        tr:nth-child(even) {
          background-color: #ffffff;
        }
      </style>
      ';

      // $mpdf->SetHTMLHeader($this->core->setPrintHeader());
      // $mpdf->SetHTMLFooter($this->core->setPrintFooter());
            
      $url = url(ADMIN.'/tmp/riwayat.perawatan.html');
      $html = file_get_contents($url);
      $mpdf->WriteHTML($this->core->setPrintCss(),\Mpdf\HTMLParserMode::HEADER_CSS);
      $mpdf->WriteHTML($css);
      $mpdf->WriteHTML($html);

      // Output a PDF file directly to the browser
      $mpdf->Output();
      exit();      
    } 

    public function getExcel()
    {
      $file = "data.pasien.xls";
      $html = file_get_contents(url(ADMIN.'/tmp/cetak.pasien.html'));
      header("Content-type: application/vnd-ms-excel");
      header("Content-Disposition: attachment; filename=$file");
      echo "<!DOCTYPE html><html><head></head><body>";
      echo $html;
      echo "</body></html>";
      exit();
    }

    /**
     * API Routes
     */
    public function apiList()
    {

        if(isset($_SERVER['HTTP_X_API_KEY'])) {
          $username = $this->core->checkAuth('GET');
          if (!$this->core->checkPermission($username, 'can_read', 'pasien')) {
              return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
          }
        }

        $perpage = intval($_GET['per_page'] ?? 10);
        if ($perpage <= 0) $perpage = 10;
        $page = intval($_GET['page'] ?? 1);
        if ($page <= 0) $page = 1;
        $offset = ($page - 1) * $perpage;
        $phrase = trim((string)($_GET['s'] ?? ''));

        $query = $this->db('pasien')->desc('no_rkm_medis');
        if ($phrase !== '') {
            $query = $query
                ->like('no_rkm_medis', '%'.$phrase.'%')
                ->orLike('nm_pasien', '%'.$phrase.'%')
                ->orLike('alamat', '%'.$phrase.'%')
                ->orLike('no_ktp', '%'.$phrase.'%')
                ->orLike('no_peserta', '%'.$phrase.'%')
                ->orLike('no_tlp', '%'.$phrase.'%');
        }

        $total = $query->count();
        $rows = $query->offset($offset)->limit($perpage)->toArray();

        // Add extra data for display
        $pasien = [];
        $cek_vclaim = $this->db('mlite_modules')->where('dir', 'vclaim')->oneArray();
        $cek_pcare = $this->db('mlite_modules')->where('dir', 'pcare')->oneArray();
        
        foreach ($rows as $row) {          
          // Add extra URLs
          if(!isset($_SERVER['HTTP_X_API_KEY'])) {
            $row['cekbynokartu'] = url([ADMIN, 'pasien', 'vclaim_bynokartu', $row['no_peserta'], date('Y-m-d')]);
            $row['cekbynik'] = url([ADMIN, 'pasien', 'vclaim_bynik', $row['no_ktp'], date('Y-m-d')]);
            $row['pcare_bynokartu'] = url([ADMIN, 'pasien', 'pcare_bynokartu', $row['no_peserta']]);
            $row['pcare_bynik'] = url([ADMIN, 'pasien', 'pcare_bynik', $row['no_ktp']]);
            $row['oral_diagnostic'] = url([ADMIN,'oral_diagnostic','manage']).'&no_rkm_medis='.$row['no_rkm_medis'];
            $row['igd'] = url([ADMIN,'igd','manage']).'&no_rkm_medis='.$row['no_rkm_medis'];
            $row['rawat_jalan'] = url([ADMIN,'rawat_jalan','manage']).'&no_rkm_medis='.$row['no_rkm_medis'];
            $row['riwayatperawatan'] = url([ADMIN, 'pasien', 'riwayatperawatan', $row['no_rkm_medis']]);
            $row['folder'] = url([ADMIN, 'pasien', 'folder', $row['no_rkm_medis']]);
          }
          
          $pasien[] = $row;
        }

        $meta = [
            'page' => $page,
            'per_page' => $perpage,
            'total' => $total,
        ];

        if(!isset($_SERVER['HTTP_X_API_KEY'])) {
            $meta['cek_vclaim'] = $cek_vclaim ? true : false;
            $meta['cek_pcare'] = $cek_pcare ? true : false;
            $meta['active_modules'] = [
                'oral_diagnostic' => $this->core->ActiveModule('oral_diagnostic'),
                'igd' => $this->core->ActiveModule('igd')
            ];
        }

        return [
            'status' => 'success',
            'data' => $pasien,
            'meta' => $meta
        ];
    }

    public function apiShow($no_rkm_medis = null)
    {
        $username = $this->core->checkAuth('GET');
        if (!$this->core->checkPermission($username, 'can_read', 'pasien')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        if (!$no_rkm_medis) {
            return ['status' => 'error', 'message' => 'no_rkm_medis required'];
        }
        $row = $this->db('pasien')->where('no_rkm_medis', $no_rkm_medis)->oneArray();
        if (!$row) {
            return ['status' => 'error', 'message' => 'Not found'];
        }
        return ['status' => 'success', 'data' => $row];
    }

    public function apiCreate()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_create', 'pasien')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        if (empty($input['nm_pasien'])) {
            return ['status' => 'error', 'message' => 'nm_pasien required'];
        }

        if (empty($input['no_rkm_medis'])) {
            $input['no_rkm_medis'] = $this->core->setNoRM();
        } else {
            $exists = $this->db('pasien')->where('no_rkm_medis', $input['no_rkm_medis'])->oneArray();
            if ($exists) {
                return ['status' => 'error', 'message' => 'no_rkm_medis exists'];
            }
        }
        
        // Set default values for required fields
        $defaults = [
            'nm_ibu' => '-',
            'alamat' => '-',
            'gol_darah' => '-',
            'pekerjaan' => '-',
            'stts_nikah' => 'BELUM MENIKAH',
            'agama' => 'ISLAM',
            'tgl_daftar' => date('Y-m-d'),
            'no_tlp' => '-',
            'umur' => '0 Th 0 Bl 0 Hr',
            'pnd' => '-',
            'keluarga' => 'AYAH',
            'namakeluarga' => '-',
            'kd_pj' => 'UMUM',
            'no_peserta' => '-',
            'kd_kel' => '1',
            'kd_kec' => '1',
            'kd_kab' => '1',
            'pekerjaanpj' => '-',
            'alamatpj' => '-',
            'kelurahanpj' => '-',
            'kecamatanpj' => '-',
            'kabupatenpj' => '-',
            'perusahaan_pasien' => '-',
            'suku_bangsa' => '1',
            'bahasa_pasien' => '1',
            'cacat_fisik' => '1',
            'email' => '-',
            'nip' => '-',
            'kd_prop' => '1',
            'propinsipj' => '-',
            'tmp_lahir' => '-',
            'tgl_lahir' => date('Y-m-d'),
            'jk' => 'L',
            'no_ktp' => '-'
        ];

        foreach ($defaults as $key => $value) {
            if (empty($input[$key])) {
                $input[$key] = $value;
            }
        }

        // Calculate umur if tgl_lahir provided
        if (!empty($input['tgl_lahir'])) {
            $input['umur'] = $this->hitungUmur($input['tgl_lahir']);
        }

        $input['tgl_daftar'] = date('Y-m-d H:i');

        try {
            $saved = $this->db('pasien')->save($input);
            if ($saved) {
                $this->db()->pdo()->exec("UPDATE set_no_rkm_medis SET no_rkm_medis='{$input['no_rkm_medis']}'");
                return ['status' => 'success', 'data' => $input];
            } else {
                $errorInfo = $this->db()->pdo()->errorInfo();
                return ['status' => 'error', 'message' => 'Failed to create: ' . ($errorInfo[2] ?? 'Unknown error')];
            }
        } catch (\PDOException $e) {
            $message = $e->getMessage();
            $message = preg_replace('/`[^`]+`\./', '', $message);
            return ['status' => 'error', 'message' => $message];
        }
    }

    public function apiUpdate($no_rkm_medis = null)
    {
        $username = $this->core->checkAuth('POST'); // Use POST for update if PUT not supported
        if (!$this->core->checkPermission($username, 'can_update', 'pasien')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        if (!$no_rkm_medis) {
            return ['status' => 'error', 'message' => 'no_rkm_medis required'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        $exists = $this->db('pasien')->where('no_rkm_medis', $no_rkm_medis)->oneArray();
        if (!$exists) {
            return ['status' => 'error', 'message' => 'Not found'];
        }

        $input['no_rkm_medis'] = $no_rkm_medis;
        $updated = $this->db('pasien')->where('no_rkm_medis', $no_rkm_medis)->update($input);
        
        if ($updated) {
            return ['status' => 'success', 'message' => 'Updated'];
        } else {
            return ['status' => 'error', 'message' => 'Failed to update'];
        }
    }

    public function apiDelete($no_rkm_medis = null)
    {
        $username = $this->core->checkAuth('DELETE');
        if (!$this->core->checkPermission($username, 'can_delete', 'pasien')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        if (!$no_rkm_medis) {
            return ['status' => 'error', 'message' => 'no_rkm_medis required'];
        }

        try {
            $deleted = $this->db('pasien')->where('no_rkm_medis', $no_rkm_medis)->delete();
            if ($deleted) {
                return ['status' => 'success', 'message' => 'Deleted'];
            } else {
                return ['status' => 'error', 'message' => 'Failed to delete'];
            }
        } catch (\Throwable $e) {
            $message = $e->getMessage();
            $message = preg_replace('/`[^`]+`\./', '', $message);
            return ['status' => 'error', 'message' => $message];
        }
    }



}
