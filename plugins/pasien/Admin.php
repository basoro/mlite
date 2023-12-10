<?php
namespace Plugins\Pasien;

use Systems\AdminModule;
use Systems\Lib\Fpdf\PDF_MC_Table;
use Plugins\Pasien\DB_Wilayah;
use Plugins\Icd\DB_ICD;

class Admin extends AdminModule
{

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

        $perpage = '10';

        $totalRecords = $this->db('pasien')
          ->select('no_rkm_medis')
          ->toArray();
        $jumlah_data    = count($totalRecords);
  			$offset         = 10;
  			$jml_halaman    = ceil($jumlah_data / $offset);
        $halaman    = 1;

        $rows = $this->db('pasien')
          ->desc('no_rkm_medis')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        $pasien = [];
        foreach ($rows as $row) {
          $row['cekbynokartu'] = url([ADMIN, 'pasien', 'vclaim_bynokartu', $row['no_peserta'], date('Y-m-d')]);
          $row['cekbynik'] = url([ADMIN, 'pasien', 'vclaim_bynik', $row['no_ktp'], date('Y-m-d')]);
          $pasien[] = $row;
        }

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
          'admin_mode' => $this->settings->get('settings.admin_mode')
        ]);

    }

    public function anyDisplay()
    {
        $this->_addHeaderFiles();

        $perpage = '10';

        $totalRecords = $this->db('pasien')->select('no_rkm_medis')->toArray();
        $jumlah_data    = count($totalRecords);
  			$offset         = 10;
  			$jml_halaman    = ceil($jumlah_data / $offset);
        $halaman    = 1;

        if(isset($_POST['cari'])) {
          if(isset($_POST['halaman']) && $_POST['halaman'] !='') {
            $_offset = (($_POST['halaman'] - 1) * $perpage);
            $totalRecords = $this->db('pasien')
              ->select('no_rkm_medis')
              ->like('no_rkm_medis', '%'.$_POST['cari'].'%')
              ->orLike('nm_pasien', '%'.$_POST['cari'].'%')
              ->orLike('alamat', '%'.$_POST['cari'].'%')
              ->orLike('no_ktp', '%'.$_POST['cari'].'%')
              ->orLike('no_peserta', '%'.$_POST['cari'].'%')
              ->orLike('no_tlp', '%'.$_POST['cari'].'%')
              ->toArray();
            $rows = $this->db('pasien')
              ->like('no_rkm_medis', '%'.$_POST['cari'].'%')
              ->orLike('nm_pasien', '%'.$_POST['cari'].'%')
              ->orLike('alamat', '%'.$_POST['cari'].'%')
              ->orLike('no_ktp', '%'.$_POST['cari'].'%')
              ->orLike('no_peserta', '%'.$_POST['cari'].'%')
              ->orLike('no_tlp', '%'.$_POST['cari'].'%')
              ->desc('no_rkm_medis')
              ->offset($_offset)
              ->limit($perpage)
              ->toArray();
            $jumlah_data = count($totalRecords);
            $jml_halaman = ceil($jumlah_data / $offset);
            $halaman = $_POST['halaman'];
          } else {
            $totalRecords = $this->db('pasien')
              ->select('no_rkm_medis')
              ->like('no_rkm_medis', '%'.$_POST['cari'].'%')
              ->orLike('nm_pasien', '%'.$_POST['cari'].'%')
              ->orLike('alamat', '%'.$_POST['cari'].'%')
              ->orLike('no_ktp', '%'.$_POST['cari'].'%')
              ->orLike('no_peserta', '%'.$_POST['cari'].'%')
              ->orLike('no_tlp', '%'.$_POST['cari'].'%')
              ->toArray();
            $rows = $this->db('pasien')
              ->like('no_rkm_medis', '%'.$_POST['cari'].'%')
              ->orLike('nm_pasien', '%'.$_POST['cari'].'%')
              ->orLike('alamat', '%'.$_POST['cari'].'%')
              ->orLike('no_ktp', '%'.$_POST['cari'].'%')
              ->orLike('no_peserta', '%'.$_POST['cari'].'%')
              ->orLike('no_tlp', '%'.$_POST['cari'].'%')
              ->desc('no_rkm_medis')
              ->offset(0)
              ->limit($perpage)
              ->toArray();
            $jumlah_data = count($totalRecords);
      			$jml_halaman = ceil($jumlah_data / $offset);
          }
        }elseif(isset($_POST['halaman'])){
          if(isset($_POST['cari']) && $_POST['cari'] !='') {
            $_offset = (($_POST['halaman'] - 1) * $perpage);
            $totalRecords = $this->db('pasien')
              ->select('no_rkm_medis')
              ->like('no_rkm_medis', '%'.$_POST['cari'].'%')
              ->orLike('nm_pasien', '%'.$_POST['cari'].'%')
              ->orLike('alamat', '%'.$_POST['cari'].'%')
              ->orLike('no_ktp', '%'.$_POST['cari'].'%')
              ->orLike('no_peserta', '%'.$_POST['cari'].'%')
              ->orLike('no_tlp', '%'.$_POST['cari'].'%')
              ->toArray();
            $rows = $this->db('pasien')
              ->like('no_rkm_medis', '%'.$_POST['cari'].'%')
              ->orLike('nm_pasien', '%'.$_POST['cari'].'%')
              ->orLike('alamat', '%'.$_POST['cari'].'%')
              ->orLike('no_ktp', '%'.$_POST['cari'].'%')
              ->orLike('no_peserta', '%'.$_POST['cari'].'%')
              ->orLike('no_tlp', '%'.$_POST['cari'].'%')
              ->desc('no_rkm_medis')
              ->offset($_offset)
              ->limit($perpage)
              ->toArray();
            $jumlah_data = count($totalRecords);
            $jml_halaman = ceil($jumlah_data / $offset);
            $halaman = $_POST['halaman'];
          } else {
            $_offset = (($_POST['halaman'] - 1) * $perpage);
            $rows = $this->db('pasien')
              ->desc('no_rkm_medis')
              ->offset($_offset)
              ->limit($perpage)
              ->toArray();
              $halaman = $_POST['halaman'];
          }
        }else{
          $rows = $this->db('pasien')
            ->desc('no_rkm_medis')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
        }

        $pasien = [];
        foreach ($rows as $row) {
          $row['cekbynokartu'] = url([ADMIN, 'pasien', 'vclaim_bynokartu', $row['no_peserta'], date('Y-m-d')]);
          $row['cekbynik'] = url([ADMIN, 'pasien', 'vclaim_bynik', $row['no_ktp'], date('Y-m-d')]);
          $pasien[] = $row;
        }

        $cek_vclaim = $this->db('mlite_modules')->where('dir', 'vclaim')->oneArray();
        $cek_pcare = $this->db('mlite_modules')->where('dir', 'pcare')->oneArray();

        echo $this->draw('display.html', [
          'pasien' => $pasien,
          'halaman' => $halaman,
          'jumlah_data' => $jumlah_data,
          'jml_halaman' => $jml_halaman,
          'cek_vclaim' => $cek_vclaim,
          'cek_pcare' => $cek_pcare, 
          'offset' => $offset,
          'admin_mode' => $this->settings->get('settings.admin_mode')
        ]);

        exit();
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
          'usernamePcare' => $usernamePcare
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
          'usernamePcare' => $usernamePcare
        ]);
      }
      exit();
    }

    public function postSave()
    {
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

      if (!$pasien) {
        $_POST['no_rkm_medis'] = $this->core->setNoRM();
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
        if($this->db('pasien')->where('no_rkm_medis', $_POST['no_rkm_medis'])->oneArray()) {
          $this->db()->pdo()->exec("UPDATE set_no_rkm_medis SET no_rkm_medis='$_POST[no_rkm_medis]'");
          $data['status'] = 'success';
          echo json_encode($data);
        } else {
          $data['status'] = 'error';
          echo json_encode($data);
        }
      } else {
        unset($_POST['nm_prop']);
        unset($_POST['nm_kab']);
        unset($_POST['nm_kec']);
        unset($_POST['nm_kel']);
        $query = $this->db('pasien')->where('no_rkm_medis', $_POST['no_rkm_medis'])->update($_POST);
        if($query) {
          $data['status'] = 'success';
          echo json_encode($data);
        } else {
          $data['status'] = 'error';
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
      $this->db('pasien')->where('no_rkm_medis', $_POST['no_rkm_medis'])->delete();
      exit();
    }

    public function getKartu()
    {
      $kartu['settings'] = $this->settings('settings');
      $kartu['pasien'] = $this->db('pasien')->where('no_rkm_medis', $_GET['no_rkm_medis'])->oneArray();
      $this->tpl->set('kartu', $this->tpl->noParse_array(htmlspecialchars_array($kartu)));
      echo $this->draw('kartu.html');
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

    public function getPcare_ByNoKartu($noKartu, $tglPelayananSEP)
    {
      $url = url([ADMIN, 'pcare', 'byjeniskartu', 'noka', $noKartu]);
      echo $this->draw('pcare.bynokartu.html', ['url' => $url]);
      exit();
    }

    public function getPcare_ByNIK($nik, $tglPelayananSEP)
    {
      $url = url([ADMIN, 'pcare', 'byjeniskartu', 'nik', $nik]);
      echo $this->draw('pcare.bynik.html', ['url' => $url]);
      exit();
    }

    public function getRiwayatPerawatan($no_rkm_medis)
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
          ->toArray();
        $row['rawat_jl_pr'] = $this->db('rawat_jl_pr')
          ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_jl_pr.kd_jenis_prw')
          ->join('petugas', 'petugas.nip=rawat_jl_pr.nip')
          ->where('no_rawat', $row['no_rawat'])
          ->toArray();
        $rows['rawat_jl_drpr'] = $this->db('rawat_jl_drpr')
          ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_jl_drpr.kd_jenis_prw')
          ->where('no_rawat', $row['no_rawat'])
          ->toArray();
        $row['rawat_jl_drpr'] = [];
        foreach ($rows['rawat_jl_drpr'] as $row) {
          $dokter = $this->db('dokter')->where('kd_dokter', $row['kd_dokter'])->oneArray();
          $petugas = $this->db('petugas')->where('nip', $row['nip'])->oneArray();
          $row['nm_dokter'] = $dokter['nm_dokter'];
          $row['nama'] = $petugas['nama'];
          $row['rawat_jl_drpr'][] = $row;
        }
        $row['pemeriksaan_ranap'] = [];
        $row['rawat_inap_dr'] = [];
        $row['rawat_inap_pr'] = [];
        $row['rawat_inap_drpr'] = [];
        $check_table = $this->db()->pdo()->query("SHOW TABLES LIKE 'pemeriksaan_ranap'");
        $check_table->execute();
        $check_table = $check_table->fetch();
        if($check_table) {
          $row['pemeriksaan_ranap'] = $this->db('pemeriksaan_ranap')->where('no_rawat', $row['no_rawat'])->toArray();
          $row['rawat_inap_dr'] = $this->db('rawat_inap_dr')
            ->join('jns_perawatan_inap', 'jns_perawatan_inap.kd_jenis_prw=rawat_inap_dr.kd_jenis_prw')
            ->join('dokter', 'dokter.kd_dokter=rawat_inap_dr.kd_dokter')
            ->where('no_rawat', $row['no_rawat'])
            ->toArray();
          $row['rawat_inap_pr'] = $this->db('rawat_inap_pr')
            ->join('jns_perawatan_inap', 'jns_perawatan_inap.kd_jenis_prw=rawat_inap_pr.kd_jenis_prw')
            ->join('petugas', 'petugas.nip=rawat_inap_pr.nip')
            ->where('no_rawat', $row['no_rawat'])
            ->toArray();
          $rows['rawat_inap_drpr'] = $this->db('rawat_inap_drpr')
            ->join('jns_perawatan_inap', 'jns_perawatan_inap.kd_jenis_prw=rawat_inap_drpr.kd_jenis_prw')
            ->where('no_rawat', $row['no_rawat'])
            ->toArray();
          foreach ($rows['rawat_inap_drpr'] as $row) {
            $dokter = $this->db('dokter')->where('kd_dokter', $row['kd_dokter'])->oneArray();
            $petugas = $this->db('petugas')->where('nip', $row['nip'])->oneArray();
            $row['nm_dokter'] = $dokter['nm_dokter'];
            $row['nama'] = $petugas['nama'];
            $row['rawat_inap_drpr'][] = $row;
          }
        }

        $rows_periksa_lab = $this->db('periksa_lab')
          ->join('jns_perawatan_lab', 'jns_perawatan_lab.kd_jenis_prw=periksa_lab.kd_jenis_prw')
          ->where('no_rawat', $row['no_rawat'])
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
        $rows_radiologi = $this->db('periksa_radiologi')
          ->where('periksa_radiologi.no_rawat', $row['no_rawat'])
          ->group('tgl_periksa')
          ->group('jam')
          ->toArray();

        foreach ($rows_radiologi as $value) {
          $value['pemeriksaan_radiologi'] = $this->db('periksa_radiologi')
            ->join('jns_perawatan_radiologi', 'jns_perawatan_radiologi.kd_jenis_prw=periksa_radiologi.kd_jenis_prw')
            ->where('no_rawat', $value['no_rawat'])
            ->where('tgl_periksa', $value['tgl_periksa'])
            ->where('jam', $value['jam'])
            ->toArray();
          $value['hasil_radiologi'] = $this->db('hasil_radiologi')
            ->where('no_rawat', $value['no_rawat'])
            ->where('tgl_periksa', $value['tgl_periksa'])
            ->where('jam', $value['jam'])
            ->toArray();
          $value['gambar_radiologi'] = $this->db('gambar_radiologi')
            ->where('no_rawat', $value['no_rawat'])
            ->where('tgl_periksa', $value['tgl_periksa'])
            ->where('jam', $value['jam'])
            ->toArray();
          $row['periksa_radiologi'][] = $value;
        }

        $detail_pemberian_obat = $this->db('detail_pemberian_obat')
          ->where('no_rawat', $row['no_rawat'])
          ->group('tgl_perawatan')
          ->group('jam')
          ->toArray();

        $row['pemberian_obat'] = [];
        foreach ($detail_pemberian_obat as $row_pemberian_obat) {
          $row_pemberian_obat['data_pemberian_obat'] = $this->db('detail_pemberian_obat')
            ->join('databarang', 'databarang.kode_brng=detail_pemberian_obat.kode_brng')
            ->where('detail_pemberian_obat.no_rawat', $row_pemberian_obat['no_rawat'])
            ->where('detail_pemberian_obat.tgl_perawatan', $row_pemberian_obat['tgl_perawatan'])
            ->where('detail_pemberian_obat.jam', $row_pemberian_obat['jam'])
            ->toArray();
          $row['pemberian_obat'][] = $row_pemberian_obat;
        }

        $row['operasi'] = $this->db('operasi')
          ->join('paket_operasi', 'paket_operasi.kode_paket=operasi.kode_paket')
          ->where('no_rawat', $row['no_rawat'])
          ->toArray();

        $row['obat_operasi'] = $this->db('beri_obat_operasi')
          ->join('obatbhp_ok', 'obatbhp_ok.kd_obat=beri_obat_operasi.kd_obat')
          ->where('no_rawat', $row['no_rawat'])
          ->toArray();

        $row['catatan_perawatan'] = $this->db('catatan_perawatan')->where('no_rawat', $row['no_rawat'])->oneArray();
        $row['berkas_digital'] = $this->db('berkas_digital_perawatan')
          ->join('master_berkas_digital', 'master_berkas_digital.kode=berkas_digital_perawatan.kode')
          ->where('no_rawat', $row['no_rawat'])
          ->toArray();

        $riwayat['reg_periksa'][] = $row;
      }
      $this->tpl->set('riwayat', $this->tpl->noParse_array(htmlspecialchars_array($riwayat)));
      echo $this->draw('riwayat.perawatan.html');
      exit();
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
      exit();
    }

    public function getCetakPdf()
    {
      $tmp = $this->db('mlite_temporary')->toArray();
      $logo = $this->settings->get('settings.logo');

      $pdf = new PDF_MC_Table('L','mm','Legal');
      $pdf->AddPage();
      $pdf->SetAutoPageBreak(true, 10);
      $pdf->SetTopMargin(10);
      $pdf->SetLeftMargin(10);
      $pdf->SetRightMargin(10);

      $pdf->Image('../'.$logo, 10, 8, '18', '18', 'png');
      $pdf->SetFont('Arial', '', 24);
      $pdf->Text(30, 16, $this->settings->get('settings.nama_instansi'));
      $pdf->SetFont('Arial', '', 10);
      $pdf->Text(30, 21, $this->settings->get('settings.alamat').' - '.$this->settings->get('settings.kota'));
      $pdf->Text(30, 25, $this->settings->get('settings.nomor_telepon').' - '.$this->settings->get('settings.email'));
      $pdf->Line(10, 30, 345, 30);
      $pdf->Line(10, 31, 345, 31);
      $pdf->SetFont('Arial', 'B', 13);
      $pdf->Text(10, 40, 'DATA PASIEN');
      $pdf->Ln(34);
      $pdf->SetFont('Arial', 'B', 11);
      $pdf->SetWidths(array(20,65,35,25,25,70,25,30,40));
      $pdf->Row(array('No. RM','Nama Pasien','No. KTP','J. Kelamin','Tgl. Lahir','Alamat','Tgl. Daftar','No. Telp','Email'));
      $pdf->SetFont('Arial', '', 10);
      foreach ($tmp as $hasil) {
        $j_kelamin = 'Laki-Laki';
        if($hasil['temp4'] == 'P') {
          $j_kelamin = 'Perempuan';
        }
        $pdf->Row(array($hasil['temp1'],$hasil['temp2'],$hasil['temp3'],$j_kelamin,$hasil['temp6'],$hasil['temp8'],$hasil['temp13'],$hasil['temp14'],$hasil['temp33']));
      }
      $pdf->Output('cetak'.date('Y-m-d').'.pdf','I');
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
            $rows = $this->data_wilayah('propinsi')->like('nm_prop', $key)->asc('kd_prop')->limit(10)->toArray();
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
            $rows = $this->data_wilayah('kabupaten')->like('nm_kab', $key)->asc('kd_kab')->limit(10)->toArray();
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
            $rows = $this->data_wilayah('kecamatan')->like('nm_kec', $key)->asc('kd_kec')->limit(10)->toArray();
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

    public function getPasien()
    {
        // CSS
        $this->core->addCSS(url('assets/css/jquery-ui.css'));
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        // JS
        $this->core->addJS(url('assets/jscripts/jquery-ui.js'));
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
        $this->core->addJS(url('https://datatables.net/media/blog/2017-12-31/dataTables.scrollResize.js'));
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));

        $penjab = $this->db('penjab')->desc('kd_pj')->toArray();
        return $this->draw('pasien.html', ['penjab' => $penjab]);
    }
    
    public function postDataPasien()
    {
  
        // $_POST['length'] = '';
        // $_POST['start'] = '';
        // $_POST['order'] = '';
        // $_POST['search'] = '';
        // $_POST['draw'] = '';

        $columns = array( 
          0 => 'no_rkm_medis',
          1 => 'nm_pasien',
          2 => 'tgl_lahir',
          3 => 'jk', 
          4 => 'gol_darah', 
          5 => 'pekerjaan', 
          6 => 'alamat', 
          7 => 'no_tlp', 
          8 => 'tgl_daftar', 
          9 => 'email'
      );
  
        // $start_date = date('Y-m-d');
        $start_date = '1970-01-01';
        $end_date = date('Y-m-d');
        if(isset($_POST['searchByFromdate']) && $_POST['searchByFromdate'] !='') {
          $start_date = $_POST['searchByFromdate'];
        }
        if(isset($_POST['searchByTodate']) && $_POST['searchByTodate'] !='') {
          $end_date = $_POST['searchByTodate'];
        }    
        
        $limit = $_POST['length'];
        $start = $_POST['start'];
        $order = $columns[$_POST['order']['0']['column']];
        $dir = $_POST['order']['0']['dir'];
              
        $keyword = $_POST['search']['value']; 
        $penjab = isset($_POST['sortByPenjab']) ? $_POST['sortByPenjab'] : '';
  
        $sql_query = "select * from pasien where ";
        $sql_query .= "tgl_daftar between ? and ? and ";
        $sql_query .= "(no_rkm_medis like ? or nm_pasien like ?) ";

        $total = $this->db()->pdo()->prepare($sql_query);
        $total->execute([$start_date, $end_date, '%'.$keyword.'%', '%'.$keyword.'%']);
        $total = $total->fetchAll(\PDO::FETCH_ASSOC);          

        $totalData = count($total);
              
        $totalFiltered = $totalData; 

        $sql_query .= "order by $order $dir LIMIT $start,$limit";    

        $query = $this->db()->pdo()->prepare($sql_query);
        $query->execute([$start_date, $end_date, '%'.$keyword.'%', '%'.$keyword.'%']);
        $query = $query->fetchAll(\PDO::FETCH_ASSOC);
  
        $data = array();
        if(!empty($query))
        {
            foreach($query as $row) {
              $row['aksi'] = '
              <div class="dropdown">
              <button data-toggle="dropdown" class="btn btn-sm btn-default dropdown-toggle" aria-haspopup="true" aria-expanded="false">Action <span class="caret"></span></button>
                                
              <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                      <li><a href="">Edit</a></li>
                      <li><a href="">Delete</a></li>
                      <li class="divider"></li>
                      <li><a href="">Separated link</a></li>
                      <li class="dropdown-submenu">
                      <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Surat-Surat</a>
                      <ul class="dropdown-menu">
                        <li><a href="#surat_kontrol" data-no_rawat="2023/12/03/000002" data-no_rkm_medis="000009" data-nm_pasien="Coba ajah" data-tgl_registrasi="2023-12-03">Surat Kontrol</a></li>
                        <li><a href="http://mlite.loc/admin/rawat_jalan/suratrujukan/20231203000002?t=a3fb0aa946a3" target="_blank">Surat Rujukan</a></li>
                        <li><a href="http://mlite.loc/admin/rawat_jalan/suratsehat/20231203000002?t=a3fb0aa946a3" target="_blank">Surat Keterangan Sehat</a></li>
                        <li><a href="http://mlite.loc/admin/rawat_jalan/suratsakit/20231203000002?t=a3fb0aa946a3" target="_blank">Surat Keterangan Sakit</a></li>
                      </ul>
                    </li>
                      
              </ul>
              </div>
              ';
              // $row['aksi'] = '<a href="#" data-toggle="modal" data-target="#statusModal" class="btn btn-sm btn-warning"><i class="fa fa-refresh"></i> '.$row['no_rkm_medis'].'</a>';
              $data[] = $row;
            }
        }
            
        
        $json_data = array(
            "draw"            => intval($_POST['draw']),  
            "recordsTotal"    => intval($totalData),  
            "recordsFiltered" => intval($totalFiltered), 
            "data"            => $data 
        );
              
        echo json_encode($json_data); 
        exit();
      
    } 

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/pasien/js/admin/pasien.js');
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

    protected function data_wilayah($table)
    {
        return new DB_Wilayah($table);
    }

    protected function data_icd($table)
    {
        return new DB_ICD($table);
    }

}
