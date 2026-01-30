<?php
namespace Plugins\Rawat_Inap;

use Systems\AdminModule;
use Systems\Lib\BpjsService;

class Admin extends AdminModule
{

    private $_uploads = WEBAPPS_PATH.'/berkasrawat/pages/upload';
    protected array $assign = [];
    private $consid = '';
    private $secretkey = '';
    private $api_url = '';
    private $user_key = '';
    
    public function navigation()
    {
        return [
            'Kelola'   => 'manage',
        ];
    }

    public function anyManage()
    {
        $tgl_masuk = '';
        $tgl_masuk_akhir = '';
        $status_pulang = '';
        $status_periksa = '';
        if (!is_array($this->assign)) {
            $this->assign = []; // atau bisa langsung array dengan default
        }

        $this->assign['stts_pulang'] = [];


        if(isset($_POST['periode_rawat_inap'])) {
          $tgl_masuk = $_POST['periode_rawat_inap'];
        }
        if(isset($_POST['periode_rawat_inap_akhir'])) {
          $tgl_masuk_akhir = $_POST['periode_rawat_inap_akhir'];
        }
        if(isset($_POST['status_pulang'])) {
          $status_pulang = $_POST['status_pulang'];
        }
        if(isset($_POST['status_periksa'])) {
          $status_periksa = $_POST['status_periksa'];
        }
        $cek_vclaim = $this->db('mlite_modules')->where('dir', 'vclaim')->oneArray();
        $master_berkas_digital = $this->db('master_berkas_digital')->toArray();
        $this->_Display($tgl_masuk, $tgl_masuk_akhir, $status_pulang, $status_periksa);
        return $this->draw('manage.html', ['rawat_inap' => $this->assign, 'cek_vclaim' => $cek_vclaim, 'master_berkas_digital' => $master_berkas_digital]);
    }

    public function anyDisplay()
    {
        $tgl_masuk = '';
        $tgl_masuk_akhir = '';
        $status_pulang = '';
        $status_periksa = '';
        $this->assign['stts_pulang'] = [];

        if(isset($_POST['periode_rawat_inap'])) {
          $tgl_masuk = $_POST['periode_rawat_inap'];
        }
        if(isset($_POST['periode_rawat_inap_akhir'])) {
          $tgl_masuk_akhir = $_POST['periode_rawat_inap_akhir'];
        }
        if(isset($_POST['status_pulang'])) {
          $status_pulang = $_POST['status_pulang'];
        }
        if(isset($_POST['status_periksa'])) {
          $status_periksa = $_POST['status_periksa'];
        }
        $cek_vclaim = $this->db('mlite_modules')->where('dir', 'vclaim')->oneArray();
        $this->_Display($tgl_masuk, $tgl_masuk_akhir, $status_pulang, $status_periksa);
        echo $this->draw('display.html', ['rawat_inap' => $this->assign, 'cek_vclaim' => $cek_vclaim]);
        exit();
    }

    public function _Display($tgl_masuk='', $tgl_masuk_akhir='', $status_pulang='', $status_periksa='')
    {
        $this->_addHeaderFiles();

        $this->assign['kamar'] = $this->db('kamar')->join('bangsal', 'bangsal.kd_bangsal=kamar.kd_bangsal')->where('statusdata', '1')->toArray();
        $this->assign['dokter']         = $this->db('dokter')->where('status', '1')->toArray();
        $this->assign['penjab']       = $this->db('penjab')->where('status', '1')->toArray();
        $this->assign['no_rawat'] = '';

        $bangsal = str_replace(",","','", $this->core->getUserInfo('cap', null, true));

        $sql = "SELECT
            kamar_inap.*,
            reg_periksa.*,
            pasien.*,
            kamar.*,
            bangsal.*,
            penjab.*
          FROM
            kamar_inap,
            reg_periksa,
            pasien,
            kamar,
            bangsal,
            penjab
          WHERE
            kamar_inap.no_rawat=reg_periksa.no_rawat
          AND
            reg_periksa.no_rkm_medis=pasien.no_rkm_medis
          AND
            kamar_inap.kd_kamar=kamar.kd_kamar
          AND
            bangsal.kd_bangsal=kamar.kd_bangsal
          AND
            reg_periksa.kd_pj=penjab.kd_pj";

        if ($this->core->getUserInfo('role') != 'admin') {
          $sql .= " AND bangsal.kd_bangsal IN ('$bangsal')";
        }
        if($status_pulang == '') {
          $sql .= " AND kamar_inap.stts_pulang = '-'";
        }
        if($status_pulang == 'all' && $tgl_masuk !== '' && $tgl_masuk_akhir !== '') {
          $sql .= " AND kamar_inap.stts_pulang = '-' AND kamar_inap.tgl_masuk BETWEEN '$tgl_masuk' AND '$tgl_masuk_akhir'";
        }
        if($status_pulang == 'masuk' && $tgl_masuk !== '' && $tgl_masuk_akhir !== '') {
          $sql .= " AND kamar_inap.tgl_masuk BETWEEN '$tgl_masuk' AND '$tgl_masuk_akhir'";
        }
        if($status_pulang == 'pulang' && $tgl_masuk !== '' && $tgl_masuk_akhir !== '') {
          $sql .= " AND kamar_inap.tgl_keluar BETWEEN '$tgl_masuk' AND '$tgl_masuk_akhir'";
        }
        if($status_periksa == 'lunas' && $status_pulang == '-' && $tgl_masuk !== '' && $tgl_masuk_akhir !== '') {
          $sql .= " AND reg_periksa.status_bayar = 'Sudah Bayar' AND kamar_inap.tgl_masuk BETWEEN '$tgl_masuk' AND '$tgl_masuk_akhir'";
        }

        $stmt = $this->db()->pdo()->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $this->assign['list'] = [];
        foreach ($rows as $row) {
          $row['status_billing'] = 'Sudah Bayar';
          $get_billing = $this->db('mlite_billing')->where('no_rawat', $row['no_rawat'])->like('kd_billing', 'RI%')->oneArray();
          if(empty($get_billing['kd_billing'])) {
            $row['kd_billing'] = 'RI.'.date('d.m.Y.H.i.s');
            $row['tgl_billing'] = date('Y-m-d H:i');
            $row['status_billing'] = 'Belum Bayar';
          }

          $dpjp_ranap = $this->db('dpjp_ranap')
            ->join('dokter', 'dokter.kd_dokter=dpjp_ranap.kd_dokter')
            ->where('no_rawat', $row['no_rawat'])
            ->toArray();
          $row['dokter'] = $dpjp_ranap;
          $bridging_sep = $this->db('bridging_sep')->where('no_rawat', $row['no_rawat'])->oneArray();
          $row['no_sep'] = isset_or($bridging_sep['no_sep']);
          $this->assign['list'][] = $row;
        }

        if (isset($_POST['no_rawat'])){
          $this->assign['kamar_inap'] = $this->db('kamar_inap')
            ->join('reg_periksa', 'reg_periksa.no_rawat=kamar_inap.no_rawat')
            ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
            ->join('kamar', 'kamar.kd_kamar=kamar_inap.kd_kamar')
            ->join('dpjp_ranap', 'dpjp_ranap.no_rawat=kamar_inap.no_rawat')
            ->join('dokter', 'dokter.kd_dokter=dpjp_ranap.kd_dokter')
            ->join('penjab', 'penjab.kd_pj=reg_periksa.kd_pj')
            ->where('kamar_inap.no_rawat', $_POST['no_rawat'])
            ->oneArray();
        } else {
          $this->assign['kamar_inap'] = [
            'tgl_masuk' => date('Y-m-d'),
            'jam_masuk' => date('H:i:s'),
            'tgl_keluar' => date('Y-m-d'),
            'jam_keluar' => date('H:i:s'),
            'no_rkm_medis' => '',
            'nm_pasien' => '',
            'no_rawat' => '',
            'kd_dokter' => '',
            'kd_kamar' => '',
            'kd_pj' => '',
            'diagnosa_awal' => '',
            'diagnosa_akhir' => '',
            'stts_pulang' => '',
            'lama' => ''
          ];
        }
    }

    public function anyForm()
    {

      $this->assign['kamar'] = $this->db('kamar')->join('bangsal', 'bangsal.kd_bangsal=kamar.kd_bangsal')->where('statusdata', '1')->toArray();
      $this->assign['dokter'] = $this->db('dokter')->where('status', '1')->toArray();
      $this->assign['penjab'] = $this->db('penjab')->where('status', '1')->toArray();
      $this->assign['stts_pulang'] = ['Sehat','Rujuk','APS','+','Meninggal','Sembuh','Membaik','Pulang Paksa','-','Pindah Kamar','Status Belum Lengkap','Atas Persetujuan Dokter','Atas Permintaan Sendiri','Lain-lain'];
      $this->assign['no_rawat'] = '';
      if (isset($_POST['no_rawat'])){
        $this->assign['kamar_inap'] = $this->db('kamar_inap')
          ->join('reg_periksa', 'reg_periksa.no_rawat=kamar_inap.no_rawat')
          ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
          ->join('kamar', 'kamar.kd_kamar=kamar_inap.kd_kamar')
          ->join('dpjp_ranap', 'dpjp_ranap.no_rawat=kamar_inap.no_rawat')
          ->join('dokter', 'dokter.kd_dokter=dpjp_ranap.kd_dokter')
          ->join('penjab', 'penjab.kd_pj=reg_periksa.kd_pj')
          ->where('kamar_inap.no_rawat', $_POST['no_rawat'])
          ->oneArray();
        echo $this->draw('form.html', [
          'rawat_inap' => $this->assign
        ]);
      } else {
        $this->assign['kamar_inap'] = [
          'tgl_masuk' => date('Y-m-d'),
          'jam_masuk' => date('H:i:s'),
          'tgl_keluar' => date('Y-m-d'),
          'jam_keluar' => date('H:i:s'),
          'no_rkm_medis' => '',
          'nm_pasien' => '',
          'no_rawat' => '',
          'kd_dokter' => '',
          'kd_kamar' => '',
          'kd_pj' => '',
          'diagnosa_awal' => '',
          'diagnosa_akhir' => '',
          'stts_pulang' => '',
          'lama' => ''
        ];
        echo $this->draw('form.html', [
          'rawat_inap' => $this->assign
        ]);
      }
      exit();
    }

    public function anyStatusDaftar()
    {
      if(isset($_POST['no_rkm_medis'])) {
        $rawat = $this->db('reg_periksa')
          ->where('no_rkm_medis', $_POST['no_rkm_medis'])
          ->where('status_bayar', 'Belum Bayar')
          ->limit(1)
          ->oneArray();
          if($rawat) {
            $stts_daftar ="Transaki tanggal ".date('Y-m-d', strtotime($rawat['tgl_registrasi']))." belum diselesaikan" ;
            $bg_status = 'text-danger';
          } else {
            $result = $this->db('reg_periksa')->where('no_rkm_medis', $_POST['no_rkm_medis'])->oneArray();
            if(!empty($result['no_rawat'])) {
              $stts_daftar = 'Lama';
              $bg_status = 'text-info';
            } else {
              $stts_daftar = 'Baru';
              $bg_status = 'text-success';
            }
          }
        echo $this->draw('stts.daftar.html', ['stts_daftar' => $stts_daftar, 'stts_daftar_hidden' => $stts_daftar, 'bg_status' => $bg_status]);
      } else {
        $rawat = $this->db('reg_periksa')
          ->where('no_rawat', $_POST['no_rawat'])
          ->oneArray();
        echo $this->draw('stts.daftar.html', ['stts_daftar' => $rawat['stts_daftar'], 'stts_daftar_hidden' => $rawat['stts_daftar'], 'bg_status' => 'text-info']);
      }
      exit();
    }

    public function postSave()
    {
      $kamar = $this->db('kamar')->where('kd_kamar', $_POST['kd_kamar'])->oneArray();
      $kamar_inap = $this->db('kamar_inap')->save([
        'no_rawat' => $_POST['no_rawat'],
        'kd_kamar' => $_POST['kd_kamar'],
        'trf_kamar' => $kamar['trf_kamar'],
        'lama' => $_POST['lama'],
        'tgl_masuk' => $_POST['tgl_masuk'],
        'jam_masuk' => $_POST['jam_masuk'],
        'ttl_biaya' => $kamar['trf_kamar']*$_POST['lama'],
        'tgl_keluar' => null,
        'jam_keluar' => null,
        'diagnosa_akhir' => '',
        'diagnosa_awal' => $_POST['diagnosa_awal'],
        'stts_pulang' => '-'
      ]);
      if($kamar_inap) {
        $this->db('dpjp_ranap')->save(['no_rawat' => $_POST['no_rawat'], 'kd_dokter' => $_POST['kd_dokter']]);
        $this->db('kamar')->where('kd_kamar', $_POST['kd_kamar'])->save(['status' => 'ISI']);
      }
      exit();
    }

    public function postSaveKeluar()
    {
      $kamar = $this->db('kamar')->where('kd_kamar', $_POST['kd_kamar'])->oneArray();
      $this->db('kamar_inap')->where('no_rawat', $_POST['no_rawat'])->save([
        'stts_pulang' => $_POST['stts_pulang'],
        'lama' => $_POST['lama'],
        'tgl_keluar' => $_POST['tgl_keluar'],
        'jam_keluar' => $_POST['jam_keluar'],
        'diagnosa_akhir' => $_POST['diagnosa_akhir'],
        'ttl_biaya' => $kamar['trf_kamar']*$_POST['lama']
      ]);
      $this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->save([
        'kd_pj' => $_POST['kd_pj'],
        'stts' => 'Sudah'
      ]);
      $this->db('kamar')->where('kd_kamar', $_POST['kd_kamar'])->save(['status' => 'KOSONG']);
      exit();
    }

    public function postSetDPJP()
    {
      $this->db('dpjp_ranap')->save(['no_rawat' => $_POST['no_rawat'], 'kd_dokter' => $_POST['kd_dokter']]);
      exit();
    }

    public function postHapusDPJP()
    {
      $this->db('dpjp_ranap')->where('no_rawat', $_POST['no_rawat'])->where('kd_dokter', $_POST['kd_dokter'])->delete();
      exit();
    }

    public function postUbahPenjab()
    {
      $this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->save([
        'kd_pj' => $_POST['kd_pj']
      ]);
      exit();
    }

    public function anyPasien()
    {
      $cari = $_POST['cari'];
      if(isset($_POST['cari'])) {
        $sql = "SELECT
            pasien.nm_pasien,
            pasien.no_rkm_medis,
            reg_periksa.no_rawat
          FROM
            reg_periksa,
            pasien
          WHERE
            reg_periksa.status_lanjut='Ranap'
          AND
            pasien.no_rkm_medis=reg_periksa.no_rkm_medis
          AND
            (reg_periksa.no_rkm_medis LIKE ? OR reg_periksa.no_rawat LIKE ? OR pasien.nm_pasien LIKE ?)
          LIMIT 10";

        $stmt = $this->db()->pdo()->prepare($sql);
        $stmt->execute(['%'.$cari.'%', '%'.$cari.'%', '%'.$cari.'%']);
        $pasien = $stmt->fetchAll();

        /*$pasien = $this->db('reg_periksa')
          ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
          ->like('reg_periksa.no_rkm_medis', '%'.$_POST['cari'].'%')
          ->where('status_lanjut', 'Ranap')
          ->asc('reg_periksa.no_rkm_medis')
          ->limit(15)
          ->toArray();*/

      }
      echo $this->draw('pasien.html', ['pasien' => $pasien]);
      exit();
    }

    public function getAntrian()
    {
      $settings = $this->settings('settings');
      $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));
      $rawat_inap = $this->db('reg_periksa')
        ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
        ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
        ->join('dokter', 'dokter.kd_dokter=reg_periksa.kd_dokter')
        ->join('penjab', 'penjab.kd_pj=reg_periksa.kd_pj')
        ->where('no_rawat', $_GET['no_rawat'])
        ->oneArray();
      echo $this->draw('antrian.html', ['rawat_inap' => $rawat_inap]);
      exit();
    }

    public function postHapus()
    {
      $this->db('kamar_inap')->where('no_rawat', $_POST['no_rawat'])->delete();
      exit();
    }

    public function postSaveDetail()
    {
      if($_POST['kat'] == 'tindakan') {
        $jns_perawatan = $this->db('jns_perawatan_inap')->where('kd_jenis_prw', $_POST['kd_jenis_prw'])->oneArray();
        if($_POST['provider'] == 'rawat_inap_dr') {
          for ($i = 0; $i < $_POST['jml_tindakan']; $i++) {
            $this->db('rawat_inap_dr')->save([
              'no_rawat' => $_POST['no_rawat'],
              'kd_jenis_prw' => $_POST['kd_jenis_prw'],
              'kd_dokter' => $_POST['kode_provider'],
              'tgl_perawatan' => $_POST['tgl_perawatan'],
              'jam_rawat' => date('H:i:s', strtotime($_POST['jam_rawat']. ' +'.$i.'0 seconds')),
              'material' => $jns_perawatan['material'],
              'bhp' => $jns_perawatan['bhp'],
              'tarif_tindakandr' => $jns_perawatan['tarif_tindakandr'],
              'kso' => $jns_perawatan['kso'],
              'menejemen' => $jns_perawatan['menejemen'],
              'biaya_rawat' => $jns_perawatan['total_byrdr']
            ]);
          }
        }
        if($_POST['provider'] == 'rawat_inap_pr') {
          for ($i = 0; $i < $_POST['jml_tindakan']; $i++) {
            $this->db('rawat_inap_pr')->save([
              'no_rawat' => $_POST['no_rawat'],
              'kd_jenis_prw' => $_POST['kd_jenis_prw'],
              'nip' => $_POST['kode_provider2'],
              'tgl_perawatan' => $_POST['tgl_perawatan'],
              'jam_rawat' => date('H:i:s', strtotime($_POST['jam_rawat']. ' +'.$i.'0 seconds')),
              'material' => $jns_perawatan['material'],
              'bhp' => $jns_perawatan['bhp'],
              'tarif_tindakanpr' => $jns_perawatan['tarif_tindakanpr'],
              'kso' => $jns_perawatan['kso'],
              'menejemen' => $jns_perawatan['menejemen'],
              'biaya_rawat' => $jns_perawatan['total_byrpr']
            ]);
          }
        }
        if($_POST['provider'] == 'rawat_inap_drpr') {
          for ($i = 0; $i < $_POST['jml_tindakan']; $i++) {
            $this->db('rawat_inap_drpr')->save([
              'no_rawat' => $_POST['no_rawat'],
              'kd_jenis_prw' => $_POST['kd_jenis_prw'],
              'kd_dokter' => $_POST['kode_provider'],
              'nip' => $_POST['kode_provider2'],
              'tgl_perawatan' => $_POST['tgl_perawatan'],
              'jam_rawat' => date('H:i:s', strtotime($_POST['jam_rawat']. ' +'.$i.'0 seconds')),
              'material' => $jns_perawatan['material'],
              'bhp' => $jns_perawatan['bhp'],
              'tarif_tindakandr' => $jns_perawatan['tarif_tindakandr'],
              'tarif_tindakanpr' => $jns_perawatan['tarif_tindakanpr'],
              'kso' => $jns_perawatan['kso'],
              'menejemen' => $jns_perawatan['menejemen'],
              'biaya_rawat' => $jns_perawatan['total_byrdrpr']
            ]);
          }
        }
      }
      if($_POST['kat'] == 'obat') {

        $no_resep = $this->core->setNoResep($_POST['tgl_perawatan']);
        $cek_resep = $this->db('resep_obat')->join('resep_dokter', 'resep_obat.no_resep = resep_dokter.no_resep')->where('no_rawat', $_POST['no_rawat'])->where('tgl_peresepan', $_POST['tgl_perawatan'])->where('tgl_perawatan', 'IS', 'NULL')->where('status', 'ranap')->oneArray();

        if(empty($cek_resep)) {

          $resep_obat = $this->db('resep_obat')
            ->save([
              'no_resep' => $no_resep,
              'tgl_perawatan' => null,
              'jam' => null,
              'no_rawat' => $_POST['no_rawat'],
              'kd_dokter' => $_POST['kode_provider'],
              'tgl_peresepan' => $_POST['tgl_perawatan'],
              'jam_peresepan' => $_POST['jam_rawat'],
              'status' => 'ranap',
              'tgl_penyerahan' => null,
              'jam_penyerahan' => null
            ]);

          if ($this->db('resep_obat')->where('no_resep', $no_resep)->where('kd_dokter', $_POST['kode_provider'])->oneArray()) {
            $this->db('resep_dokter')
              ->save([
                'no_resep' => $no_resep,
                'kode_brng' => $_POST['kd_jenis_prw'],
                'jml' => $_POST['jml'],
                'aturan_pakai' => $_POST['aturan_pakai']
              ]);
          }

        } else {

          $no_resep = $cek_resep['no_resep'];

          $this->db('resep_dokter')
            ->save([
              'no_resep' => $no_resep,
              'kode_brng' => $_POST['kd_jenis_prw'],
              'jml' => $_POST['jml'],
              'aturan_pakai' => $_POST['aturan_pakai']
            ]);

        }

      }
      exit();
    }

    public function postHapusDetail()
    {
      if($_POST['provider'] == 'rawat_inap_dr') {
        $this->db('rawat_inap_dr')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('kd_jenis_prw', $_POST['kd_jenis_prw'])
        ->where('tgl_perawatan', $_POST['tgl_perawatan'])
        ->where('jam_rawat', $_POST['jam_rawat'])
        ->delete();
      }
      if($_POST['provider'] == 'rawat_inap_pr') {
        $this->db('rawat_inap_pr')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('kd_jenis_prw', $_POST['kd_jenis_prw'])
        ->where('tgl_perawatan', $_POST['tgl_perawatan'])
        ->where('jam_rawat', $_POST['jam_rawat'])
        ->delete();
      }
      if($_POST['provider'] == 'rawat_inap_drpr') {
        $this->db('rawat_inap_drpr')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('kd_jenis_prw', $_POST['kd_jenis_prw'])
        ->where('tgl_perawatan', $_POST['tgl_perawatan'])
        ->where('jam_rawat', $_POST['jam_rawat'])
        ->delete();
      }
      exit();
    }

    public function postHapusResep()
    {
      if(isset($_POST['kd_jenis_prw'])) {
        $this->db('resep_dokter')
        ->where('no_resep', $_POST['no_resep'])
        ->where('kode_brng', $_POST['kd_jenis_prw'])
        ->delete();
      } else {
        $this->db('resep_obat')
        ->where('no_resep', $_POST['no_resep'])
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('tgl_peresepan', $_POST['tgl_peresepan'])
        ->where('jam_peresepan', $_POST['jam_peresepan'])
        ->delete();
      }

      exit();
    }

    public function anyRincian()
    {
      $rows_rawat_inap_dr = $this->db('rawat_inap_dr')->where('no_rawat', $_POST['no_rawat'])->toArray();
      $rows_rawat_inap_pr = $this->db('rawat_inap_pr')->where('no_rawat', $_POST['no_rawat'])->toArray();
      $rows_rawat_inap_drpr = $this->db('rawat_inap_drpr')->where('no_rawat', $_POST['no_rawat'])->toArray();

      $jumlah_total = 0;
      $rawat_inap_dr = [];
      $rawat_inap_pr = [];
      $rawat_inap_drpr = [];
      $i = 1;

      if($rows_rawat_inap_dr) {
        foreach ($rows_rawat_inap_dr as $row) {
          $jns_perawatan = $this->db('jns_perawatan_inap')->where('kd_jenis_prw', $row['kd_jenis_prw'])->oneArray();
          $row['nm_perawatan'] = $jns_perawatan['nm_perawatan'];
          $jumlah_total = $jumlah_total + $row['biaya_rawat'];
          $row['provider'] = 'rawat_inap_dr';
          $rawat_inap_dr[] = $row;
        }
      }

      if($rows_rawat_inap_pr) {
        foreach ($rows_rawat_inap_pr as $row) {
          $jns_perawatan = $this->db('jns_perawatan_inap')->where('kd_jenis_prw', $row['kd_jenis_prw'])->oneArray();
          $row['nm_perawatan'] = $jns_perawatan['nm_perawatan'];
          $jumlah_total = $jumlah_total + $row['biaya_rawat'];
          $row['provider'] = 'rawat_inap_pr';
          $rawat_inap_pr[] = $row;
        }
      }

      if($rows_rawat_inap_drpr) {
        foreach ($rows_rawat_inap_drpr as $row) {
          $jns_perawatan = $this->db('jns_perawatan_inap')->where('kd_jenis_prw', $row['kd_jenis_prw'])->oneArray();
          $row['nm_perawatan'] = $jns_perawatan['nm_perawatan'];
          $jumlah_total = $jumlah_total + $row['biaya_rawat'];
          $row['provider'] = 'rawat_inap_drpr';
          $rawat_inap_drpr[] = $row;
        }
      }

      $rows = $this->db('resep_obat')
        ->join('dokter', 'dokter.kd_dokter=resep_obat.kd_dokter')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('resep_obat.status', 'ranap')
        ->toArray();
      $resep = [];
      $jumlah_total_resep = 0;
      foreach ($rows as $row) {
        $row['nomor'] = $i++;
        $row['resep_dokter'] = $this->db('resep_dokter')->join('databarang', 'databarang.kode_brng=resep_dokter.kode_brng')->where('no_resep', $row['no_resep'])->toArray();
        foreach ($row['resep_dokter'] as $value) {
          $value['dasar'] = $value['jml'] * $value['dasar'];
          $jumlah_total_resep += floatval($value['dasar']);
        }
        $resep[] = $row;
      }
      echo $this->draw('rincian.html', ['rawat_inap_dr' => $rawat_inap_dr, 'rawat_inap_pr' => $rawat_inap_pr, 'rawat_inap_drpr' => $rawat_inap_drpr, 'jumlah_total' => $jumlah_total, 'jumlah_total_resep' => $jumlah_total_resep, 'resep' =>$resep, 'no_rawat' => $_POST['no_rawat']]);
      exit();
    }

    public function anySoap()
    {

      $prosedurs = $this->db('prosedur_pasien')
         ->where('no_rawat', $_POST['no_rawat'])
         ->asc('prioritas')
         ->toArray();
       $prosedur = [];
       foreach ($prosedurs as $row) {
         $icd9 = $this->db('icd9')->where('kode', $row['kode'])->oneArray();
         $row['nama'] = $icd9['deskripsi_panjang'];
         $prosedur[] = $row;
       }
       $diagnosas = $this->db('diagnosa_pasien')
         ->where('no_rawat', $_POST['no_rawat'])
         ->asc('prioritas')
         ->toArray();
       $diagnosa = [];
       foreach ($diagnosas as $row) {
         $icd10 = $this->db('penyakit')->where('kd_penyakit', $row['kd_penyakit'])->oneArray();
         $row['nama'] = $icd10['nm_penyakit'];
         $diagnosa[] = $row;
       }

      $i = 1;
      $row['nama_petugas'] = '';
      $row['departemen_petugas'] = '';
      $rows = $this->db('pemeriksaan_ralan')
        ->where('no_rawat', $_POST['no_rawat'])
        ->toArray();
      $result = [];
      foreach ($rows as $row) {
        $row['nomor'] = $i++;
        $row['nama_petugas'] = $this->core->getPegawaiInfo('nama',$row['nip']);
        $row['departemen_petugas'] = $this->core->getDepartemenInfo($this->core->getPegawaiInfo('departemen',$row['nip']));
        $result[] = $row;
      }

      $rows_ranap = $this->db('pemeriksaan_ranap')
        ->where('no_rawat', $_POST['no_rawat'])
        ->toArray();
      $result_ranap = [];
      foreach ($rows_ranap as $row) {
        $row['nomor'] = $i++;
        $row['nama_petugas'] = $this->core->getPegawaiInfo('nama',$row['nip']);
        $row['departemen_petugas'] = $this->core->getDepartemenInfo($this->core->getPegawaiInfo('departemen',$row['nip']));
        $result_ranap[] = $row;
      }

      echo $this->draw('soap.html', ['pemeriksaan' => $result, 'pemeriksaan_ranap' => $result_ranap, 'diagnosa' => $diagnosa, 'prosedur' => $prosedur]);
      exit();
    }

    public function anyFormSoap()
    {
      // Ambil data pasien dari reg_periksa dan pasien
      $pasien_data = $this->db('reg_periksa')
        ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
        ->where('reg_periksa.no_rawat', $_POST['no_rawat'])
        ->oneArray();
      
      // Hitung umur
      $umur = '';
      if($pasien_data) {
        $tgl_lahir = new \DateTime($pasien_data['tgl_lahir']);
        $tgl_daftar = new \DateTime($pasien_data['tgl_registrasi']);
        $umur_tahun = $tgl_daftar->diff($tgl_lahir)->y;
        $umur_bulan = $tgl_daftar->diff($tgl_lahir)->m;
        $umur_hari = $tgl_daftar->diff($tgl_lahir)->d;
        
        if($umur_tahun > 0) {
          $umur = $umur_tahun . ' Th';
        } elseif($umur_bulan > 0) {
          $umur = $umur_bulan . ' Bl';
        } else {
          $umur = $umur_hari . ' Hr';
        }
      }
      
      // Ambil data pemeriksaan_ralan terbaru sebagai fallback
      $pemeriksaan_ralan = $this->db('pemeriksaan_ralan')
        ->where('no_rawat', $_POST['no_rawat'])
        ->desc('tgl_perawatan')
        ->desc('jam_rawat')
        ->oneArray();
      
      // Set default values dari pemeriksaan_ralan jika ada
      $default_values = [
        'suhu_tubuh' => $pemeriksaan_ralan['suhu_tubuh'] ?? '',
        'tensi' => $pemeriksaan_ralan['tensi'] ?? '',
        'nadi' => $pemeriksaan_ralan['nadi'] ?? '',
        'respirasi' => $pemeriksaan_ralan['respirasi'] ?? '',
        'tinggi' => $pemeriksaan_ralan['tinggi'] ?? '',
        'berat' => $pemeriksaan_ralan['berat'] ?? '',
        'gcs' => $pemeriksaan_ralan['gcs'] ?? '',
        'kesadaran' => $pemeriksaan_ralan['kesadaran'] ?? '',
        'alergi' => $pemeriksaan_ralan['alergi'] ?? '',
        'lingkar_perut' => $pemeriksaan_ralan['lingkar_perut'] ?? '',
        'keluhan' => $pemeriksaan_ralan['keluhan'] ?? '',
        'pemeriksaan' => $pemeriksaan_ralan['pemeriksaan'] ?? '',
        'penilaian' => $pemeriksaan_ralan['penilaian'] ?? '',
        'rtl' => $pemeriksaan_ralan['rtl'] ?? '',
        'instruksi' => $pemeriksaan_ralan['instruksi'] ?? '',
        'evaluasi' => $pemeriksaan_ralan['evaluasi'] ?? '',
        'spo2' => $pemeriksaan_ralan['spo2'] ?? ''
      ];
      
      // Data pasien untuk form
      $patient_data = [
        'no_rawat' => $_POST['no_rawat'],
        'no_rkm_medis' => $pasien_data['no_rkm_medis'] ?? '',
        'nm_pasien' => $pasien_data['nm_pasien'] ?? '',
        'umur' => $umur
      ];
      
      echo $this->draw('form.soap.html', [
        'default_values' => $default_values,
        'patient_data' => $patient_data
      ]);
      exit();
    }

    public function postSaveSOAP()
    {
      $_POST['nip'] = $this->core->getUserInfo('username', null, true);

      if(!$this->db('pemeriksaan_ranap')->where('no_rawat', $_POST['no_rawat'])->where('tgl_perawatan', $_POST['tgl_perawatan'])->where('jam_rawat', $_POST['jam_rawat'])->where('nip', $_POST['nip'])->oneArray()) {
        $this->db('pemeriksaan_ranap')->save($_POST);
      } else {
        $this->db('pemeriksaan_ranap')->where('no_rawat', $_POST['no_rawat'])->where('tgl_perawatan', $_POST['tgl_perawatan'])->where('jam_rawat', $_POST['jam_rawat'])->where('nip', $_POST['nip'])->save($_POST);
      }
      exit();
    }

    public function anyVitalSignsChart()
    {
      $no_rawat = $_POST['no_rawat'];
      
      // Ambil data tanda vital dari pemeriksaan_ralan dan pemeriksaan_ranap
      $vital_signs_ralan = $this->db('pemeriksaan_ralan')
        ->where('no_rawat', $no_rawat)
        ->asc('tgl_perawatan')
        ->asc('jam_rawat')
        ->toArray();
        
      $vital_signs_ranap = $this->db('pemeriksaan_ranap')
        ->where('no_rawat', $no_rawat)
        ->asc('tgl_perawatan')
        ->asc('jam_rawat')
        ->toArray();
      
      // Gabungkan data dari kedua tabel
      $vital_signs = array_merge($vital_signs_ralan, $vital_signs_ranap);
      
      // Urutkan berdasarkan tanggal dan jam
      usort($vital_signs, function($a, $b) {
        $datetime_a = $a['tgl_perawatan'] . ' ' . $a['jam_rawat'];
        $datetime_b = $b['tgl_perawatan'] . ' ' . $b['jam_rawat'];
        return strtotime($datetime_a) - strtotime($datetime_b);
      });
      
      // Debug: Log jumlah data yang ditemukan
      error_log('VitalSignsChart - no_rawat: ' . $no_rawat);
      error_log('VitalSignsChart - ralan count: ' . count($vital_signs_ralan));
      error_log('VitalSignsChart - ranap count: ' . count($vital_signs_ranap));
      error_log('VitalSignsChart - total count: ' . count($vital_signs));
      if(count($vital_signs) > 0) {
        error_log('VitalSignsChart - first record: ' . json_encode($vital_signs[0]));
      }
      
      $chart_data = [
        'labels' => [],
        'datasets' => [
          [
            'label' => 'Suhu (°C)',
            'data' => [],
            'borderColor' => 'rgb(255, 99, 132)',
            'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
            'borderWidth' => 1,
            'tension' => 0.1
          ],
          [
            'label' => 'Tensi Sistol (mmHg)',
            'data' => [],
            'borderColor' => 'rgb(54, 162, 235)',
            'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
            'borderWidth' => 1,
            'tension' => 0.1
          ],
          [
            'label' => 'Tensi Diastol (mmHg)',
            'data' => [],
            'borderColor' => 'rgb(75, 192, 192)',
            'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
            'borderWidth' => 1,
            'tension' => 0.1
          ],
          [
            'label' => 'Nadi (/menit)',
            'data' => [],
            'borderColor' => 'rgb(255, 205, 86)',
            'backgroundColor' => 'rgba(255, 205, 86, 0.2)',
            'borderWidth' => 1,
            'tension' => 0.1
          ],
          [
            'label' => 'RR (/menit)',
            'data' => [],
            'borderColor' => 'rgb(153, 102, 255)',
            'backgroundColor' => 'rgba(153, 102, 255, 0.2)',
            'borderWidth' => 1,
            'tension' => 0.1
          ],
          [
            'label' => 'Tinggi (cm)',
            'data' => [],
            'borderColor' => 'rgb(255, 159, 64)',
            'backgroundColor' => 'rgba(255, 159, 64, 0.2)',
            'borderWidth' => 1,
            'tension' => 0.1,
            'yAxisID' => 'y1'
          ],
          [
            'label' => 'Berat (kg)',
            'data' => [],
            'borderColor' => 'rgb(199, 199, 199)',
            'backgroundColor' => 'rgba(199, 199, 199, 0.2)',
            'borderWidth' => 1,
            'tension' => 0.1,
            'yAxisID' => 'y1'
          ],
          [
            'label' => 'SPO2 (%)',
            'data' => [],
            'borderColor' => 'rgb(83, 102, 255)',
            'backgroundColor' => 'rgba(83, 102, 255, 0.2)',
            'borderWidth' => 1,
            'tension' => 0.1
          ]
        ]
      ];
      
      foreach($vital_signs as $vital) {
        $date_label = date('d/m H:i', strtotime($vital['tgl_perawatan'] . ' ' . $vital['jam_rawat']));
        $chart_data['labels'][] = $date_label;
        
        // Suhu
        $chart_data['datasets'][0]['data'][] = floatval($vital['suhu_tubuh']) ?: null;
        
        // Tensi - pisahkan sistol dan diastol
        $tensi_parts = explode('/', $vital['tensi']);
        $sistol = isset($tensi_parts[0]) ? floatval($tensi_parts[0]) : null;
        $diastol = isset($tensi_parts[1]) ? floatval($tensi_parts[1]) : null;
        $chart_data['datasets'][1]['data'][] = $sistol;
        $chart_data['datasets'][2]['data'][] = $diastol;
        
        // Nadi
        $chart_data['datasets'][3]['data'][] = floatval($vital['nadi']) ?: null;
        
        // RR
        $chart_data['datasets'][4]['data'][] = floatval($vital['respirasi']) ?: null;
        
        // Tinggi
        $chart_data['datasets'][5]['data'][] = floatval($vital['tinggi']) ?: null;
        
        // Berat
        $chart_data['datasets'][6]['data'][] = floatval($vital['berat']) ?: null;
        
        // SPO2
        $chart_data['datasets'][7]['data'][] = floatval($vital['spo2']) ?: null;
      }
      
      header('Content-Type: application/json');
      echo json_encode($chart_data);
      exit();
    }

    public function postHapusSOAP()
    {
      $this->db('pemeriksaan_ranap')->where('no_rawat', $_POST['no_rawat'])->where('tgl_perawatan', $_POST['tgl_perawatan'])->where('jam_rawat', $_POST['jam_rawat'])->delete();
      exit();
    }

    public function anyLayanan()
    {
      $layanan = $this->db('jns_perawatan_inap')
        ->where('status', '1')
        ->like('nm_perawatan', '%'.$_POST['layanan'].'%')
        ->limit(10)
        ->toArray();
      echo $this->draw('layanan.html', ['layanan' => $layanan]);
      exit();
    }

    public function anyObat()
    {
      $obat = $this->db('databarang')
        ->join('gudangbarang', 'gudangbarang.kode_brng=databarang.kode_brng')
        ->where('status', '1')
        ->where('gudangbarang.kd_bangsal', $this->settings->get('farmasi.deporanap'))
        ->like('databarang.nama_brng', '%'.$_POST['obat'].'%')
        ->limit(10)
        ->toArray();
      echo $this->draw('obat.html', ['obat' => $obat]);
      exit();
    }

    public function postAturanPakai()
    {

      if(isset($_POST["query"])){
        $output = '';
        $key = "%".$_POST["query"]."%";
        $rows = $this->db('master_aturan_pakai')->like('aturan', $key)->limit(10)->toArray();
        $output = '';
        if(count($rows)){
          foreach ($rows as $row) {
            $output .= '<li class="list-group-item link-class">'.$row["aturan"].'</li>';
          }
        }
        echo $output;
      }

      exit();

    }

    public function anyBerkasDigital()
    {
      $berkas_digital = $this->db('berkas_digital_perawatan')->where('no_rawat', $_POST['no_rawat'])->toArray();
      echo $this->draw('berkasdigital.html', ['berkas_digital' => $berkas_digital]);
      exit();
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

        $image = $_FILES['file']['tmp_name'];
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
      }
      exit();

    }

    public function postProviderList()
    {

      if(isset($_POST["query"])){
        $output = '';
        $key = "%".$_POST["query"]."%";
        $rows = $this->db('dokter')->like('nm_dokter', $key)->where('status', '1')->limit(10)->toArray();
        $output = '';
        if(count($rows)){
          foreach ($rows as $row) {
            $output .= '<li class="list-group-item link-class">'.$row["kd_dokter"].': '.$row["nm_dokter"].'</li>';
          }
        }
        echo $output;
      }

      exit();

    }

    public function postProviderList2()
    {

      if(isset($_POST["query"])){
        $output = '';
        $key = "%".$_POST["query"]."%";
        $rows = $this->db('petugas')->like('nama', $key)->limit(10)->toArray();
        $output = '';
        if(count($rows)){
          foreach ($rows as $row) {
            $output .= '<li class="list-group-item link-class">'.$row["nip"].': '.$row["nama"].'</li>';
          }
        }
        echo $output;
      }

      exit();

    }

    public function postCekWaktu()
    {
      echo date('H:i:s');
      exit();
    }

    public function postMaxid()
    {
      $urut = $this->db('reg_periksa')
          ->where('kd_poli', $this->settings->get('settings.rawat_inap'))
          ->where('tgl_registrasi', date('Y-m-d'))
          ->nextRightNumber('no_rawat', 6);

      $next_no_rawat = date('Y/m/d').'/'.$urut;
      echo $next_no_rawat;
      exit();
    }

    public function postMaxAntrian()
    {
      $urut = $this->db('reg_periksa')
          ->where('kd_poli', $this->settings->get('settings.rawat_inap'))
          ->where('tgl_registrasi', date('Y-m-d'))
          ->nextRightNumber('no_reg', 3);

      echo sprintf('%03d', $urut);
      exit();
    }

    public function convertNorawat($text)
    {
        setlocale(LC_ALL, 'en_EN');
        $text = str_replace('/', '', trim($text));
        return $text;
    }

    public function getSepDetail($no_sep){
      $sep = $this->db('bridging_sep')->where('no_sep', $no_sep)->oneArray();
      $this->tpl->set('sep', $this->tpl->noParse_array(htmlspecialchars_array($sep)));

      $potensi_prb = $this->db('bpjs_prb')->where('no_sep', $no_sep)->oneArray();
      $data_sep['potensi_prb'] = $potensi_prb['prb'];
      echo $this->draw('sep.detail.html', ['data_sep' => $data_sep]);
      exit();
    }

    public function getSuratRujukan($no_rawat)
    {
        $kd_dokter = $this->core->getRegPeriksaInfo('kd_dokter', revertNoRawat($no_rawat));
        $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis', revertNoRawat($no_rawat));
        $pasien = $this->db('pasien')
          ->join('kelurahan', 'kelurahan.kd_kel=pasien.kd_kel')
          ->join('kecamatan', 'kecamatan.kd_kec=pasien.kd_kec')
          ->join('kabupaten', 'kabupaten.kd_kab=pasien.kd_kab')
          ->join('propinsi', 'propinsi.kd_prop=pasien.kd_prop')
          ->where('no_rkm_medis', $no_rkm_medis)
          ->oneArray();
        $nm_dokter = $this->core->getPegawaiInfo('nama', $kd_dokter);
        $sip_dokter = $this->core->getDokterInfo('no_ijn_praktek', $kd_dokter);
        $this->tpl->set('pasien', $this->tpl->noParse_array(htmlspecialchars_array($pasien)));
        $this->tpl->set('nm_dokter', $nm_dokter);
        $this->tpl->set('sip_dokter', $sip_dokter);
        $this->tpl->set('no_rawat', revertNoRawat($no_rawat));
        $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($this->settings('settings'))));
        $this->tpl->set('surat', $this->db('mlite_surat_rujukan')->where('no_rawat', revertNoRawat($no_rawat))->oneArray());
        $this->tpl->set('nomor_surat', $this->settings->get('settings.set_nomor_surat').'/'.$this->settings->get('settings.prefix_surat').'/'.getRomawi(date('m')).'/'.date('Y'));
        echo $this->tpl->draw(MODULES.'/rawat_inap/view/admin/surat.rujukan.html', true);
        exit();
    }

    public function getSuratSehat($no_rawat)
    {
        $kd_dokter = $this->core->getRegPeriksaInfo('kd_dokter', revertNoRawat($no_rawat));
        $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis', revertNoRawat($no_rawat));
        $pasien = $this->db('pasien')
          ->join('kelurahan', 'kelurahan.kd_kel=pasien.kd_kel')
          ->join('kecamatan', 'kecamatan.kd_kec=pasien.kd_kec')
          ->join('kabupaten', 'kabupaten.kd_kab=pasien.kd_kab')
          ->join('propinsi', 'propinsi.kd_prop=pasien.kd_prop')
          ->where('no_rkm_medis', $no_rkm_medis)
          ->oneArray();
        $nm_dokter = $this->core->getPegawaiInfo('nama', $kd_dokter);
        $sip_dokter = $this->core->getDokterInfo('no_ijn_praktek', $kd_dokter);
        $this->tpl->set('pasien', $this->tpl->noParse_array(htmlspecialchars_array($pasien)));
        $this->tpl->set('nm_dokter', $nm_dokter);
        $this->tpl->set('sip_dokter', $sip_dokter);
        $this->tpl->set('no_rawat', revertNoRawat($no_rawat));
        $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($this->settings('settings'))));
        $this->tpl->set('surat', $this->db('mlite_surat_sehat')->where('no_rawat', revertNoRawat($no_rawat))->oneArray());
        $this->tpl->set('nomor_surat', $this->settings->get('settings.set_nomor_surat').'/'.$this->settings->get('settings.prefix_surat').'/'.getRomawi(date('m')).'/'.date('Y'));
        echo $this->tpl->draw(MODULES.'/rawat_inap/view/admin/surat.sehat.html', true);
        exit();
    }

    public function getSuratSakit($no_rawat)
    {
        $kd_dokter = $this->core->getRegPeriksaInfo('kd_dokter', revertNoRawat($no_rawat));
        $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis', revertNoRawat($no_rawat));
        $pasien = $this->db('pasien')
          ->join('kelurahan', 'kelurahan.kd_kel=pasien.kd_kel')
          ->join('kecamatan', 'kecamatan.kd_kec=pasien.kd_kec')
          ->join('kabupaten', 'kabupaten.kd_kab=pasien.kd_kab')
          ->join('propinsi', 'propinsi.kd_prop=pasien.kd_prop')
          ->where('no_rkm_medis', $no_rkm_medis)
          ->oneArray();
        $nm_dokter = $this->core->getPegawaiInfo('nama', $kd_dokter);
        $sip_dokter = $this->core->getDokterInfo('no_ijn_praktek', $kd_dokter);
        $this->tpl->set('pasien', $this->tpl->noParse_array(htmlspecialchars_array($pasien)));
        $this->tpl->set('nm_dokter', $nm_dokter);
        $this->tpl->set('sip_dokter', $sip_dokter);
        $this->tpl->set('no_rawat', revertNoRawat($no_rawat));
        $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($this->settings('settings'))));
        $this->tpl->set('surat', $this->db('mlite_surat_sakit')->where('no_rawat', revertNoRawat($no_rawat))->oneArray());
        $this->tpl->set('nomor_surat', $this->settings->get('settings.set_nomor_surat').'/'.$this->settings->get('settings.prefix_surat').'/'.getRomawi(date('m')).'/'.date('Y'));
        echo $this->tpl->draw(MODULES.'/rawat_inap/view/admin/surat.sakit.html', true);
        exit();
    }

    public function postSimpanSuratSakit()
    {
      $query = $this->db('mlite_surat_sakit')->save([
        'nomor_surat' => $_POST['nomor_surat'], 
        'no_rawat' => $_POST['no_rawat'], 
        'no_rkm_medis' => $_POST['no_rkm_medis'], 
        'nm_pasien' => $_POST['nm_pasien'], 
        'tgl_lahir' => $_POST['tgl_lahir'], 
        'umur' => $_POST['umur'], 
        'jk' => $_POST['jk'], 
        'alamat' => $_POST['alamat'], 
        'keadaan' => $_POST['keadaan'], 
        'diagnosa' => $_POST['diagnosa'], 
        'lama_angka' => $_POST['lama_angka'], 
        'lama_huruf' => $_POST['lama_huruf'], 
        'tanggal_mulai' => $_POST['tanggal_mulai'], 
        'tanggal_selesai' => $_POST['tanggal_selesai'], 
        'dokter' => $_POST['dokter'], 
        'petugas' => $_POST['petugas']
      ]);

      if($query) {
        $nomor_surat = ltrim($this->settings->get('settings.set_nomor_surat'));
        $nomor_surat = sprintf('%03s', ($nomor_surat + 1));
        $this->db('mlite_settings')->where('module', 'settings')->where('field', 'set_nomor_surat')->set('value', $nomor_surat)->update();
        $data['status'] = 'success';
        echo json_encode($data);
      } else {
        $data['status'] = 'error';
        $data['msg'] = $query->errorInfo()['2'];
        echo json_encode($data);
      }

      exit();
    }

    public function postSimpanSuratSehat()
    {
      $query = $this->db('mlite_surat_sehat')->save([
        'nomor_surat' => $_POST['nomor_surat'], 
        'no_rawat' => $_POST['no_rawat'], 
        'no_rkm_medis' => $_POST['no_rkm_medis'], 
        'nm_pasien' => $_POST['nm_pasien'], 
        'tgl_lahir' => $_POST['tgl_lahir'], 
        'umur' => $_POST['umur'], 
        'jk' => $_POST['jk'], 
        'alamat' => $_POST['alamat'], 
        'tanggal' => $_POST['tanggal'], 
        'berat_badan' => $_POST['berat_badan'], 
        'tinggi_badan' => $_POST['tinggi_badan'], 
        'tensi' => $_POST['tensi'], 
        'gol_darah' => $_POST['gol_darah'], 
        'riwayat_penyakit' => $_POST['riwayat_penyakit'], 
        'keperluan' => $_POST['keperluan'], 
        'dokter' => $_POST['dokter'], 
        'petugas' => $_POST['petugas']
      ]);

      if($query) {
        $nomor_surat = ltrim($this->settings->get('settings.set_nomor_surat'));
        $nomor_surat = sprintf('%03s', ($nomor_surat + 1));
        $this->db('mlite_settings')->where('module', 'settings')->where('field', 'set_nomor_surat')->set('value', $nomor_surat)->update();
        $data['status'] = 'success';
        echo json_encode($data);
      } else {
        $data['status'] = 'error';
        $data['msg'] = $query->errorInfo()['2'];
        echo json_encode($data);
      }

      exit();
    }

    public function postSimpanSuratRujukan()
    {
      $query = $this->db('mlite_surat_rujukan')->save([
        'nomor_surat' => $_POST['nomor_surat'], 
        'no_rawat' => $_POST['no_rawat'], 
        'no_rkm_medis' => $_POST['no_rkm_medis'], 
        'nm_pasien' => $_POST['nm_pasien'], 
        'tgl_lahir' => $_POST['tgl_lahir'], 
        'umur' => $_POST['umur'], 
        'jk' => $_POST['jk'], 
        'alamat' => $_POST['alamat'], 
        'kepada' => $_POST['kepada'], 
        'di' => $_POST['di'], 
        'anamnesa' => $_POST['anamnesa'], 
        'pemeriksaan_fisik' => $_POST['pemeriksaan_fisik'], 
        'pemeriksaan_penunjang' => $_POST['pemeriksaan_penunjang'], 
        'diagnosa' => $_POST['diagnosa'], 
        'terapi' => $_POST['terapi'], 
        'alasan_dirujuk' => $_POST['alasan_dirujuk'], 
        'dokter' => $_POST['dokter'], 
        'petugas' => $_POST['petugas']
      ]);

      if($query) {
        $nomor_surat = ltrim($this->settings->get('settings.set_nomor_surat'));
        $nomor_surat = sprintf('%03s', ($nomor_surat + 1));
        $this->db('mlite_settings')->where('module', 'settings')->where('field', 'set_nomor_surat')->set('value', $nomor_surat)->update();
        $data['status'] = 'success';
        echo json_encode($data);
      } else {
        $data['status'] = 'error';
        $data['msg'] = $query->errorInfo()['2'];
        echo json_encode($data);
      }

      exit();
    }

    public function anyKontrol()
    {
      $rows = $this->db('booking_registrasi')
        ->join('poliklinik', 'poliklinik.kd_poli=booking_registrasi.kd_poli')
        ->join('dokter', 'dokter.kd_dokter=booking_registrasi.kd_dokter')
        ->join('penjab', 'penjab.kd_pj=booking_registrasi.kd_pj')
        ->where('no_rkm_medis', $_POST['no_rkm_medis'])
        ->toArray();
      $i = 1;
      $result = [];
      foreach ($rows as $row) {
        $row['nomor'] = $i++;
        $result[] = $row;
      }
      echo $this->draw('kontrol.html', ['booking_registrasi' => $result]);
      exit();
    }

    public function postSaveKontrol()
    {

      $query = $this->db('skdp_bpjs')->save([
        'tahun' => date('Y'),
        'no_rkm_medis' => $_POST['no_rkm_medis'],
        'diagnosa' => $_POST['diagnosa'],
        'terapi' => $_POST['terapi'],
        'alasan1' => $_POST['alasan1'],
        'alasan2' => '',
        'rtl1' => $_POST['rtl1'],
        'rtl2' => '',
        'tanggal_datang' => $_POST['tanggal_datang'],
        'tanggal_rujukan' => $_POST['tanggal_rujukan'],
        'no_antrian' => $this->core->setNoSKDP(),
        'kd_dokter' => $this->core->getRegPeriksaInfo('kd_dokter', $_POST['no_rawat']),
        'status' => 'Menunggu'
      ]);

      if ($query) {
        $this->db('booking_registrasi')
          ->save([
            'tanggal_booking' => date('Y-m-d'),
            'jam_booking' => date('H:i:s'),
            'no_rkm_medis' => $_POST['no_rkm_medis'],
            'tanggal_periksa' => $_POST['tanggal_datang'],
            'kd_dokter' => $this->core->getRegPeriksaInfo('kd_dokter', $_POST['no_rawat']),
            'kd_poli' => $this->core->getRegPeriksaInfo('kd_poli', $_POST['no_rawat']),
            'no_reg' => $this->core->setNoBooking($this->core->getRegPeriksaInfo('kd_dokter', $_POST['no_rawat']), $_POST['tanggal_datang'], $this->core->getRegPeriksaInfo('kd_poli', $_POST['no_rawat'])),
            'kd_pj' => $this->core->getRegPeriksaInfo('kd_pj', $_POST['no_rawat']),
            'limit_reg' => 0,
            'waktu_kunjungan' => $_POST['tanggal_datang'].' '.date('H:i:s'),
            'status' => 'Belum'
          ]);
      }

      exit();
    }

    public function postSaveKontrolBPJS()
    {

      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
      $key = $this->consid . $this->secretkey . $tStamp;
      $_POST['sep_user']  = $this->core->getUserInfo('fullname', null, true);

      $maping_dokter_dpjpvclaim = $this->db('maping_dokter_dpjpvclaim')->where('kd_dokter', $this->core->getRegPeriksaInfo('kd_dokter', $_POST['no_rawat']))->oneArray();
      $maping_poli_bpjs = $this->db('maping_poli_bpjs')->where('kd_poli_rs', $this->core->getRegPeriksaInfo('kd_poli', $_POST['no_rawat']))->oneArray();
      $get_sep = $this->db('bridging_sep')->where('no_rawat', $_POST['no_rawat'])->oneArray();
      $_POST['no_sep'] = $get_sep['no_sep'];
      $get_sep_internal = $this->db('bridging_sep_internal')->where('no_rawat', $_POST['no_rawat'])->oneArray();

      if(empty($get_sep['no_sep'])) {
        $_POST['no_sep'] = $get_sep_internal['no_sep'];
      }

      $data = [
        'request' => [
          'noSEP' => $_POST['no_sep'],
          'kodeDokter' => $maping_dokter_dpjpvclaim['kd_dokter_bpjs'],
          'poliKontrol' => $maping_poli_bpjs['kd_poli_bpjs'],
          'tglRencanaKontrol' => $_POST['tanggal_datang'],
          'user' => $_POST['sep_user']
        ]
      ];
      $statusUrl = 'insert';
      $method = 'post';

      $data = json_encode($data);

      $url = $this->api_url . 'RencanaKontrol/insert';
      $output = BpjsService::post($url, $data, $this->consid, $this->secretkey, $this->user_key, $tStamp);
      $data = json_decode($output, true);
      //echo $data['metaData']['message'];
      if ($data == NULL) {
        echo 'Koneksi ke server BPJS terputus. Silahkan ulangi beberapa saat lagi!';
      } else if ($data['metaData']['code'] == 200) {
        $stringDecrypt = stringDecrypt($key, $data['response']);
        $decompress = '""';
        $decompress = \LZCompressor\LZString::decompressFromEncodedURIComponent(($stringDecrypt));
        $spri = json_decode($decompress, true);
        //echo $spri['noSuratKontrol'];

        $bridging_surat_pri_bpjs = $this->db('bridging_surat_kontrol_bpjs')->save([
          'no_sep' => $_POST['no_sep'],
          'tgl_surat' => $_POST['tanggal_rujukan'],
          'no_surat' => $spri['noSuratKontrol'],
          'tgl_rencana' => $_POST['tanggal_datang'],
          'kd_dokter_bpjs' => $maping_dokter_dpjpvclaim['kd_dokter_bpjs'],
          'nm_dokter_bpjs' => $maping_dokter_dpjpvclaim['nm_dokter_bpjs'],
          'kd_poli_bpjs' => $maping_poli_bpjs['kd_poli_bpjs'],
          'nm_poli_bpjs' => $maping_poli_bpjs['nm_poli_bpjs']
        ]);

      }

      exit();
    }

    public function postHapusKontrol()
    {
      $this->db('booking_registrasi')->where('kd_dokter', $_POST['kd_dokter'])->where('no_rkm_medis', $_POST['no_rkm_medis'])->where('tanggal_periksa', $_POST['tanggal_periksa'])->where('status', 'Belum')->delete();
      $this->db('skdp_bpjs')->where('kd_dokter', $_POST['kd_dokter'])->where('no_rkm_medis', $_POST['no_rkm_medis'])->where('tanggal_datang', $_POST['tanggal_periksa'])->where('status', 'Menunggu')->delete();
      exit();
    }

    public function getPersetujuanUmum($no_rkm_medis)
    {
      $settings = $this->settings('settings');
      $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));
      $pasien = $this->db('pasien')->where('no_rkm_medis', $no_rkm_medis)->oneArray();
      echo $this->draw('persetujuan.umum.html', ['pasien' => $pasien]);
      exit();
    }

    public function postSaveICD10()
    {
      $_POST['status_penyakit'] = 'Baru';
      unset($_POST['nama']);
      $this->db('diagnosa_pasien')->save($_POST);
      exit();
    }  

    public function postHapusICD10()
    {
      $this->db('diagnosa_pasien')->where('no_rawat', $_POST['no_rawat'])->where('prioritas', $_POST['prioritas'])->delete();
      exit();
    }
  
    public function postICD10()
    {
  
      if(isset($_POST["query"])){
        $output = '';
        $key = "%".$_POST["query"]."%";
        $rows = $this->db('penyakit')->like('kd_penyakit', $key)->orLike('nm_penyakit', $key)->asc('kd_penyakit')->limit(10)->toArray();
        $output = '';
        if(count($rows)){
          foreach ($rows as $row) {
            $output .= '<li class="list-group-item link-class">'.$row["kd_penyakit"].': '.$row["nm_penyakit"].'</li>';
          }
        } else {
          $output .= '<li class="list-group-item link-class">Tidak ada yang cocok.</li>';
        }
        echo $output;
      }
  
      exit();
  
    }

    public function postSaveICD9()
    {
      unset($_POST['nama']);
      $this->db('prosedur_pasien')->save($_POST);
      exit();
    }

    public function postHapusICD9()
    {
      $this->db('prosedur_pasien')->where('no_rawat', $_POST['no_rawat'])->where('prioritas', $_POST['prioritas'])->delete();
      exit();
    }

    public function postICD9()
    {
  
      if(isset($_POST["query"])){
        $output = '';
        $key = "%".$_POST["query"]."%";
        $rows = $this->db('icd9')->like('kode', $key)->orLike('deskripsi_panjang', $key)->asc('kode')->limit(10)->toArray();
        $output = '';
        if(count($rows)){
          foreach ($rows as $row) {
            $output .= '<li class="list-group-item link-class">'.$row["kode"].': '.$row["deskripsi_panjang"].'</li>';
          }
        } else {
          $output .= '<li class="list-group-item link-class">Tidak ada yang cocok.</li>';
        }
        echo $output;
      }
  
      exit();
  
    }

    public function getDisplayICD()
    {
      $no_rawat = $_GET['no_rawat'];
      $prosedurs = $this->db('prosedur_pasien')
        ->where('no_rawat', $no_rawat)
        ->asc('prioritas')
        ->toArray();
      $prosedur = [];
      foreach ($prosedurs as $row_prosedur) {
        $icd9 = $this->db('icd9')->where('kode', $row_prosedur['kode'])->oneArray();
        $row_prosedur['nama'] = $icd9['deskripsi_panjang'];
        $prosedur[] = $row_prosedur;
      }
  
      $diagnosas = $this->db('diagnosa_pasien')
        ->where('no_rawat', $no_rawat)
        ->asc('prioritas')
        ->toArray();
      $diagnosa = [];
      foreach ($diagnosas as $row_diagnosa) {
        $icd10 = $this->db('penyakit')->where('kd_penyakit', $row_diagnosa['kd_penyakit'])->oneArray();
        $row_diagnosa['nama'] = $icd10['nm_penyakit'];
        $diagnosa[] = $row_diagnosa;
      }
  
      echo $this->draw('display.icd.html', ['diagnosa' => $diagnosa, 'prosedur' => $prosedur]);
      exit();
    }

    public function getAssessment($no_rawat)
    {
      $no_rawat = revertNoRawat($no_rawat);
      
      // Cek apakah sudah ada data assessment
      $penilaian_ranap = $this->db('penilaian_awal_keperawatan_ranap')
        ->where('no_rawat', $no_rawat)
        ->oneArray();
      
      // Jika belum ada, ambil data fallback dari pemeriksaan_ralan dan pemeriksaan_ranap
      if(!$penilaian_ranap) {
        $pemeriksaan_ralan = $this->db('pemeriksaan_ralan')
          ->where('no_rawat', $no_rawat)
          ->desc('tgl_perawatan')
          ->desc('jam_rawat')
          ->oneArray();
          
        $pemeriksaan_ranap = $this->db('pemeriksaan_ranap')
          ->where('no_rawat', $no_rawat)
          ->desc('tgl_perawatan')
          ->desc('jam_rawat')
          ->oneArray();
        
        $penilaian_ranap = [
          'no_rawat' => $no_rawat,
          'tanggal' => date('Y-m-d H:i:s'),
          'informasi' => 'Autoanamnesis',
          'ket_informasi' => '',
          'tiba_diruang_rawat' => 'Jalan Tanpa Bantuan',
          'kasus_trauma' => 'Non Trauma',
          'cara_masuk' => 'Poli',
          'rps' => $pemeriksaan_ralan['keluhan'] ?? $pemeriksaan_ranap['keluhan'] ?? '',
          'rpd' => '',
          'rpk' => '',
          'rpo' => '',
          'riwayat_pembedahan' => '',
          'riwayat_dirawat_dirs' => '',
          'alat_bantu_dipakai' => 'Kacamata',
          'riwayat_kehamilan' => 'Tidak',
          'riwayat_kehamilan_perkiraan' => '',
          'riwayat_tranfusi' => '',
          'riwayat_alergi' => $pemeriksaan_ralan['alergi'] ?? $pemeriksaan_ranap['alergi'] ?? '',
          'riwayat_merokok' => 'Tidak',
          'riwayat_merokok_jumlah' => '',
          'riwayat_alkohol' => 'Tidak',
          'riwayat_alkohol_jumlah' => '',
          'riwayat_narkoba' => 'Tidak',
          'riwayat_olahraga' => 'Tidak',
          'pemeriksaan_mental' => '',
          'pemeriksaan_keadaan_umum' => 'Baik',
          'pemeriksaan_gcs' => $pemeriksaan_ralan['gcs'] ?? $pemeriksaan_ranap['gcs'] ?? '',
          'pemeriksaan_td' => $pemeriksaan_ralan['tensi'] ?? $pemeriksaan_ranap['tensi'] ?? '',
          'pemeriksaan_nadi' => $pemeriksaan_ralan['nadi'] ?? $pemeriksaan_ranap['nadi'] ?? '',
          'pemeriksaan_rr' => $pemeriksaan_ralan['respirasi'] ?? $pemeriksaan_ranap['respirasi'] ?? '',
          'pemeriksaan_suhu' => $pemeriksaan_ralan['suhu_tubuh'] ?? $pemeriksaan_ranap['suhu_tubuh'] ?? '',
          'pemeriksaan_spo2' => $pemeriksaan_ralan['spo2'] ?? $pemeriksaan_ranap['spo2'] ?? '',
          'pemeriksaan_bb' => $pemeriksaan_ralan['berat'] ?? $pemeriksaan_ranap['berat'] ?? '',
          'pemeriksaan_tb' => $pemeriksaan_ralan['tinggi'] ?? $pemeriksaan_ranap['tinggi'] ?? '',
          'pemeriksaan_susunan_kepala' => 'TAK',
          'pemeriksaan_susunan_kepala_keterangan' => '',
          'pemeriksaan_susunan_wajah' => 'TAK',
          'pemeriksaan_susunan_wajah_keterangan' => '',
          'pemeriksaan_susunan_leher' => 'TAK',
          'pemeriksaan_susunan_leher_keterangan' => '',
          'pemeriksaan_susunan_kejang' => 'TAK',
          'pemeriksaan_susunan_kejang_keterangan' => '',
          'pemeriksaan_susunan_sensorik' => 'TAK',
          'pemeriksaan_susunan_sensorik_keterangan' => '',
          'pemeriksaan_kardiovaskuler_denyut_nadi' => 'Teratur',
          'pemeriksaan_kardiovaskuler_sirkulasi' => 'Akral Hangat',
          'pemeriksaan_kardiovaskuler_sirkulasi_keterangan' => '',
          'pemeriksaan_kardiovaskuler_pulsasi' => 'Kuat',
          'pemeriksaan_respirasi_pola_nafas' => 'Normal',
          'pemeriksaan_respirasi_retraksi' => 'Tidak Ada',
          'pemeriksaan_respirasi_suara_nafas' => 'Vesikuler',
          'pemeriksaan_respirasi_volume_pernafasan' => 'Normal',
          'pemeriksaan_respirasi_jenis_pernafasan' => 'Pernafasan Dada',
          'pemeriksaan_respirasi_jenis_pernafasan_keterangan' => '',
          'pemeriksaan_respirasi_irama_nafas' => 'Teratur',
          'pemeriksaan_respirasi_batuk' => 'Tidak',
          'pemeriksaan_gastrointestinal_mulut' => 'TAK',
          'pemeriksaan_gastrointestinal_mulut_keterangan' => '',
          'pemeriksaan_gastrointestinal_gigi' => 'TAK',
          'pemeriksaan_gastrointestinal_gigi_keterangan' => '',
          'pemeriksaan_gastrointestinal_lidah' => 'TAK',
          'pemeriksaan_gastrointestinal_lidah_keterangan' => '',
          'pemeriksaan_gastrointestinal_tenggorokan' => 'TAK',
          'pemeriksaan_gastrointestinal_tenggorokan_keterangan' => '',
          'pemeriksaan_gastrointestinal_abdomen' => 'Supel',
          'pemeriksaan_gastrointestinal_abdomen_keterangan' => '',
          'pemeriksaan_gastrointestinal_peistatik_usus' => 'TAK',
          'pemeriksaan_gastrointestinal_peistatik_usus_keterangan' => '',
          'pemeriksaan_gastrointestinal_anus' => 'TAK',
          'pemeriksaan_gastrointestinal_anus_keterangan' => '',
          'pemeriksaan_neurologi_pengelihatan' => 'TAK',
          'pemeriksaan_neurologi_pengelihatan_keterangan' => '',
          'pemeriksaan_neurologi_alat_bantu_penglihatan' => 'Tidak',
          'pemeriksaan_neurologi_pendengaran' => 'TAK',
          'pemeriksaan_neurologi_pendengaran_keterangan' => '',
          'pemeriksaan_neurologi_bicara' => 'Jelas',
          'pemeriksaan_neurologi_bicara_keterangan' => '',
          'pemeriksaan_neurologi_sensorik' => 'TAK',
          'pemeriksaan_neurologi_sensorik_keterangan' => '',
          'pemeriksaan_neurologi_motorik' => 'TAK',
          'pemeriksaan_neurologi_motorik_keterangan' => '',
          'pemeriksaan_neurologi_kekuatan_otot' => 'Kuat',
          'pemeriksaan_neurologi_kekuatan_otot_keterangan' => '',
          'pemeriksaan_integument_warnakulit' => 'Normal',
          'pemeriksaan_integument_warnakulit_keterangan' => '',
          'pemeriksaan_integument_turgor' => 'Baik',
          'pemeriksaan_integument_turgor_keterangan' => '',
          'pemeriksaan_integument_kulit' => 'Normal',
          'pemeriksaan_integument_kulit_keterangan' => '',
          'pemeriksaan_integument_dekubitas' => 'Tidak Ada',
          'pemeriksaan_integument_dekubitas_keterangan' => '',
          'pemeriksaan_muskuloskletal_pergerakan_sendi' => 'Bebas',
          'pemeriksaan_muskuloskletal_pergerakan_sendi_keterangan' => '',
          'pemeriksaan_muskuloskletal_kekauatan_otot' => 'Baik',
          'pemeriksaan_muskuloskletal_kekauatan_otot_keterangan' => '',
          'pemeriksaan_muskuloskletal_nyeri_sendi' => 'Tidak Ada',
          'pemeriksaan_muskuloskletal_nyeri_sendi_keterangan' => '',
          'pemeriksaan_muskuloskletal_oedema' => 'Tidak Ada',
          'pemeriksaan_muskuloskletal_oedema_keterangan' => '',
          'pemeriksaan_muskuloskletal_fraktur' => 'Tidak Ada',
          'pemeriksaan_muskuloskletal_fraktur_keterangan' => '',
          'pemeriksaan_eliminasi_bab_frekuensi_jumlah' => '',
          'pemeriksaan_eliminasi_bab_frekuensi_durasi' => '',
          'pemeriksaan_eliminasi_bab_konsistensi' => '',
          'pemeriksaan_eliminasi_bab_warna' => '',
          'pemeriksaan_eliminasi_bak_frekuensi_jumlah' => '',
          'pemeriksaan_eliminasi_bak_frekuensi_durasi' => '',
          'pemeriksaan_eliminasi_bak_warna' => '',
          'pemeriksaan_eliminasi_bak_lainlain' => '',
          'pola_aktifitas_makanminum' => 'Mandiri',
          'pola_aktifitas_mandi' => 'Mandiri',
          'pola_aktifitas_eliminasi' => 'Mandiri',
          'pola_aktifitas_berpakaian' => 'Mandiri',
          'pola_aktifitas_berpindah' => 'Mandiri',
          'pola_nutrisi_frekuesi_makan' => '',
          'pola_nutrisi_jenis_makanan' => '',
          'pola_nutrisi_porsi_makan' => '',
          'pola_tidur_lama_tidur' => '',
          'pola_tidur_gangguan' => 'Tidak Ada Gangguan',
          'pengkajian_fungsi_kemampuan_sehari' => 'Mandiri',
          'pengkajian_fungsi_aktifitas' => 'Berjalan',
          'pengkajian_fungsi_berjalan' => 'TAK',
          'pengkajian_fungsi_berjalan_keterangan' => '',
          'pengkajian_fungsi_ambulasi' => 'Tidak Menggunakan',
          'pengkajian_fungsi_ekstrimitas_atas' => 'TAK',
          'pengkajian_fungsi_ekstrimitas_atas_keterangan' => '',
          'pengkajian_fungsi_ekstrimitas_bawah' => 'TAK',
          'pengkajian_fungsi_ekstrimitas_bawah_keterangan' => '',
          'pengkajian_fungsi_menggenggam' => 'Tidak Ada Kesulitan',
          'pengkajian_fungsi_menggenggam_keterangan' => '',
          'pengkajian_fungsi_koordinasi' => 'Tidak Ada Kesulitan',
          'pengkajian_fungsi_koordinasi_keterangan' => '',
          'pengkajian_fungsi_kesimpulan' => 'Tidak (Tidak Perlu Co DPJP)',
          'riwayat_psiko_kondisi_psiko' => 'Tidak Ada Masalah',
          'riwayat_psiko_gangguan_jiwa' => 'Tidak',
          'riwayat_psiko_perilaku' => 'Tidak Ada Masalah',
          'riwayat_psiko_perilaku_keterangan' => '',
          'riwayat_psiko_hubungan_keluarga' => 'Harmonis',
          'riwayat_psiko_tinggal' => 'Keluarga',
          'riwayat_psiko_tinggal_keterangan' => '',
          'riwayat_psiko_nilai_kepercayaan' => 'Tidak Ada',
          'riwayat_psiko_nilai_kepercayaan_keterangan' => '',
          'riwayat_psiko_pendidikan_pj' => '-',
          'riwayat_psiko_edukasi_diberikan' => 'Pasien',
          'riwayat_psiko_edukasi_diberikan_keterangan' => '',
          'penilaian_nyeri' => 'Tidak Ada Nyeri',
          'penilaian_nyeri_penyebab' => 'Proses Penyakit',
          'penilaian_nyeri_ket_penyebab' => '',
          'penilaian_nyeri_kualitas' => 'Seperti Tertusuk',
          'penilaian_nyeri_ket_kualitas' => '',
          'penilaian_nyeri_lokasi' => '',
          'penilaian_nyeri_ket_lokasi' => '',
          'penilaian_nyeri_menyebar' => 'Tidak',
          'penilaian_nyeri_ket_menjalar' => '',
          'penilaian_nyeri_skala' => '0',
          'penilaian_nyeri_ket_skala' => '',
          'penilaian_nyeri_waktu' => '',
          'penilaian_nyeri_ket_waktu' => '',
          'penilaian_nyeri_hilang' => 'Istirahat',
          'penilaian_nyeri_ket_hilang' => '',
          'penilaian_nyeri_diberitahukan_dokter' => 'Tidak',
          'penilaian_nyeri_ket_diberitahukan_dokter' => '',
          'penilaian_nyeri_jam_diberitahukan_dokter' => '',
          'penilaian_nyeri_ket_pada_dokter' => '',
          'penilaian_jatuhmorse_skala1' => 'Tidak',
          'penilaian_jatuhmorse_nilai1' => 0,
          'penilaian_jatuhmorse_skala2' => 'Tidak',
          'penilaian_jatuhmorse_nilai2' => 0,
          'penilaian_jatuhmorse_skala3' => 'Tidak Ada/Kursi Roda/Perawat/Tirah Baring',
          'penilaian_jatuhmorse_nilai3' => 0,
          'penilaian_jatuhmorse_skala4' => 'Tidak',
          'penilaian_jatuhmorse_nilai4' => 0,
          'penilaian_jatuhmorse_skala5' => 'Normal/Tirah Baring/Imobilisasi',
          'penilaian_jatuhmorse_nilai5' => 0,
          'penilaian_jatuhmorse_skala6' => 'Sadar Akan Kemampuan Diri Sendiri',
          'penilaian_jatuhmorse_nilai6' => 0,
          'penilaian_jatuhmorse_totalnilai' => 0,
          'penilaian_jatuhsydney_skala1' => 'Tidak',
          'penilaian_jatuhsydney_nilai1' => 0,
          'penilaian_jatuhsydney_skala2' => 'Tidak',
          'penilaian_jatuhsydney_nilai2' => 0,
          'penilaian_jatuhsydney_skala3' => 'Tidak',
          'penilaian_jatuhsydney_nilai3' => 0,
          'penilaian_jatuhsydney_skala4' => 'Tidak',
          'penilaian_jatuhsydney_nilai4' => 0,
          'penilaian_jatuhsydney_skala5' => 'Tidak',
          'penilaian_jatuhsydney_nilai5' => 0,
          'penilaian_jatuhsydney_skala6' => 'Tidak',
          'penilaian_jatuhsydney_nilai6' => 0,
          'penilaian_jatuhsydney_skala7' => 'Tidak',
          'penilaian_jatuhsydney_nilai7' => 0,
          'penilaian_jatuhsydney_skala8' => 'Tidak',
          'penilaian_jatuhsydney_nilai8' => 0,
          'penilaian_jatuhsydney_skala9' => 'Tidak',
          'penilaian_jatuhsydney_nilai9' => 0,
          'penilaian_jatuhsydney_skala10' => 'Tidak',
          'penilaian_jatuhsydney_nilai10' => 0,
          'penilaian_jatuhsydney_skala11' => 'Tidak',
          'penilaian_jatuhsydney_nilai11' => 0,
          'penilaian_jatuhsydney_totalnilai' => 0,
          'skrining_gizi1' => 'Tidak ada penurunan berat badan',
          'nilai_gizi1' => 0,
          'skrining_gizi2' => 'Tidak',
          'nilai_gizi2' => 0,
          'nilai_total_gizi' => 0,
          'skrining_gizi_diagnosa_khusus' => 'Tidak',
          'skrining_gizi_ket_diagnosa_khusus' => '',
          'skrining_gizi_diketahui_dietisen' => 'Tidak',
          'skrining_gizi_jam_diketahui_dietisen' => '',
          'rencana' => '',
          'nip1' => $this->core->getUserInfo('username', null, true),
          'nip2' => $this->core->getUserInfo('username', null, true),
          'kd_dokter' => $this->core->getRegPeriksaInfo('kd_dokter', $no_rawat),
        ];
      }
      
      echo $this->draw('assesment.html', ['penilaian_ranap' => $penilaian_ranap]);
      exit();
    }

    public function postAssessmentsave()
    {
      $_POST['nip1'] = $this->core->getUserInfo('username', null, true);
      $_POST['nip2'] = $this->core->getUserInfo('username', null, true);
      
      // Remove fields that don't exist in database
      $data = $_POST;
      unset($data['no_rawat_display']);
      
      // Cek apakah sudah ada data
      $existing = $this->db('penilaian_awal_keperawatan_ranap')
        ->where('no_rawat', $data['no_rawat'])
        ->oneArray();
      
      if($existing) {
        // Update data yang sudah ada
        $query = $this->db('penilaian_awal_keperawatan_ranap')
          ->where('no_rawat', $data['no_rawat'])
          ->save($data);
      } else {
        // Insert data baru
        $query = $this->db('penilaian_awal_keperawatan_ranap')->save($data);
      }
      
      if($query) {
        $data['status'] = 'success';
        echo json_encode($data);
      } else {
        $data['status'] = 'error';
        $data['msg'] = 'Gagal menyimpan data assessment';
        echo json_encode($data);
      }
      exit();
    }

    public function getAssessmenttampil($no_rawat)
    {
      $no_rawat = revertNoRawat($no_rawat);
      
      $penilaian_ranap = $this->db('penilaian_awal_keperawatan_ranap')
        ->join('petugas as p1', 'p1.nip=penilaian_awal_keperawatan_ranap.nip1')
        ->join('petugas as p2', 'p2.nip=penilaian_awal_keperawatan_ranap.nip2')
        ->where('no_rawat', $no_rawat)
        ->oneArray();
      
      if($penilaian_ranap) {
        $penilaian_ranap['nama_petugas1'] = $penilaian_ranap['p1.nama'];
        $penilaian_ranap['nama_petugas2'] = $penilaian_ranap['p2.nama'];
      }
      
      echo $this->draw('assesment.tampil.html', ['penilaian_ranap' => $penilaian_ranap]);
      exit();
    }

    public function postAssessmentdelete()
    {
      $query = $this->db('penilaian_awal_keperawatan_ranap')
        ->where('no_rawat', $_POST['no_rawat'])
        ->delete();
      
      if($query) {
        $data['status'] = 'success';
        echo json_encode($data);
      } else {
        $data['status'] = 'error';
        $data['msg'] = 'Gagal menghapus data assessment';
        echo json_encode($data);
      }
      exit();
    }

    // ADIME Gizi Methods
    public function postSaveadimegizi()
    {
        $data = [
            'no_rawat' => $_POST['no_rawat'],
            'tanggal' => $_POST['tanggal'],
            'asesmen' => $_POST['asesmen'] ?? null,
            'diagnosis' => $_POST['diagnosis'] ?? null,
            'intervensi' => $_POST['intervensi'] ?? null,
            'monitoring' => $_POST['monitoring'] ?? null,
            'evaluasi' => $_POST['evaluasi'] ?? null,
            'instruksi' => $_POST['instruksi'] ?? null,
            'nip' => $this->core->getUserInfo('username', null, true)
        ];
        
        // Cek apakah sudah ada data dengan no_rawat dan tanggal yang sama
        $existing = $this->db('catatan_adime_gizi')
            ->where('no_rawat', $data['no_rawat'])
            ->where('tanggal', $data['tanggal'])
            ->oneArray();
        
        try {
            if($existing) {
                // Update data yang sudah ada
                $query = $this->db('catatan_adime_gizi')
                    ->where('no_rawat', $data['no_rawat'])
                    ->where('tanggal', $data['tanggal'])
                    ->save($data);
            } else {
                // Insert data baru
                $query = $this->db('catatan_adime_gizi')->save($data);
            }
            
            if($query) {
                echo json_encode(['status' => 'success', 'message' => 'Data ADIME Gizi berhasil disimpan']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data ADIME Gizi']);
            }
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
        
        exit();
    }
    
    public function postGetadimegizi()
    {
        $no_rawat = $_POST['no_rawat'];
        
        try {
            $data = $this->db('catatan_adime_gizi')
                ->join('petugas', 'petugas.nip=catatan_adime_gizi.nip')
                ->select([
                    'catatan_adime_gizi.no_rawat',
                    'catatan_adime_gizi.tanggal',
                    'catatan_adime_gizi.asesmen',
                    'catatan_adime_gizi.diagnosis',
                    'catatan_adime_gizi.intervensi',
                    'catatan_adime_gizi.monitoring',
                    'catatan_adime_gizi.evaluasi',
                    'catatan_adime_gizi.instruksi',
                    'petugas.nama as nama_petugas'
                ])
                ->where('catatan_adime_gizi.no_rawat', $no_rawat)
                ->desc('catatan_adime_gizi.tanggal')
                ->toArray();
            
            echo json_encode($data);
        } catch (\Exception $e) {
            echo json_encode([]);
        }
        
        exit();
    }
    
    public function postGetadimegizidetail()
    {
        $no_rawat = $_POST['no_rawat'];
        $tanggal = $_POST['tanggal'];
        
        try {
            $data = $this->db('catatan_adime_gizi')
                ->where('no_rawat', $no_rawat)
                ->where('tanggal', $tanggal)
                ->oneArray();
            
            echo json_encode($data);
        } catch (\Exception $e) {
            echo json_encode(null);
        }
        
        exit();
    }
    
    public function postDeleteadimegizi()
    {
        $no_rawat = $_POST['no_rawat'];
        $tanggal = $_POST['tanggal'];
        
        try {
            $query = $this->db('catatan_adime_gizi')
                ->where('no_rawat', $no_rawat)
                ->where('tanggal', $tanggal)
                ->delete();
            
            if($query) {
                echo json_encode(['status' => 'success', 'message' => 'Data ADIME Gizi berhasil dihapus']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data ADIME Gizi']);
            }
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
        
        exit();
    }

    // Assessment Nyeri Methods
    public function getAssessmentNyeri($no_rawat)
    {
        $no_rawat = revertNorawat($no_rawat);
        $reg_periksa = $this->db('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
            ->join('dokter', 'dokter.kd_dokter=reg_periksa.kd_dokter')
            ->where('no_rawat', $no_rawat)
            ->oneArray();
        
        $penilaian_ulang_nyeri = $this->db('penilaian_ulang_nyeri')
            ->join('petugas', 'petugas.nip=penilaian_ulang_nyeri.nip')
            ->where('no_rawat', $no_rawat)
            ->oneArray();
        
        // Get petugas info for current user
        $petugas = $this->db('petugas')
            ->where('nip', $this->core->getUserInfo('username', null, true))
            ->oneArray();
        
        echo $this->draw('assessment.nyeri.html', ['reg_periksa' => $reg_periksa, 'penilaian_ulang_nyeri' => $penilaian_ulang_nyeri, 'petugas' => $petugas]);
        exit();
    }
    
    public function postAssessmentNyeri()
    {
        $_POST['nip'] = $this->core->getUserInfo('username', null, true);
        
        // Handle edit mode
        if(isset($_POST['mode']) && $_POST['mode'] == 'edit' && isset($_POST['original_tanggal'])) {
            $this->db('penilaian_ulang_nyeri')
                ->where('no_rawat', $_POST['no_rawat'])
                ->where('tanggal', $_POST['original_tanggal'])
                ->save([
                    'nip'               => $_POST['nip'],
                    'tanggal'           => $_POST['tanggal'],
                    'nyeri'             => $_POST['nyeri'],
                    'provokes'          => $_POST['provokes'],
                    'ket_provokes'      => $_POST['ket_provokes'],
                    'quality'           => $_POST['quality'],
                    'ket_quality'       => $_POST['ket_quality'],
                    'lokasi'            => $_POST['lokasi'],
                    'menyebar'          => $_POST['menyebar'],
                    'skala_nyeri'       => $_POST['skala_nyeri'],
                    'durasi'            => $_POST['durasi'],
                    'nyeri_hilang'      => $_POST['nyeri_hilang'],
                    'ket_nyeri'         => $_POST['ket_nyeri']
                ]);
        } else {
            // Check if record already exists
            $existing = $this->db('penilaian_ulang_nyeri')
                ->where('no_rawat', $_POST['no_rawat'])
                ->where('tanggal', $_POST['tanggal'])
                ->oneArray();
            
            if($existing) {
                // Update existing record
                $this->db('penilaian_ulang_nyeri')
                    ->where('no_rawat', $_POST['no_rawat'])
                    ->where('tanggal', $_POST['tanggal'])
                    ->save([
                        'nip'               => $_POST['nip'],
                        'nyeri'             => $_POST['nyeri'],
                        'provokes'          => $_POST['provokes'],
                        'ket_provokes'      => $_POST['ket_provokes'],
                        'quality'           => $_POST['quality'],
                        'ket_quality'       => $_POST['ket_quality'],
                        'lokasi'            => $_POST['lokasi'],
                        'menyebar'          => $_POST['menyebar'],
                        'skala_nyeri'       => $_POST['skala_nyeri'],
                        'durasi'            => $_POST['durasi'],
                        'nyeri_hilang'      => $_POST['nyeri_hilang'],
                        'ket_nyeri'         => $_POST['ket_nyeri']
                    ]);
            } else {
                // Insert new record
                $this->db('penilaian_ulang_nyeri')->save([
                    'no_rawat'          => $_POST['no_rawat'],
                    'nip'               => $_POST['nip'],
                    'tanggal'           => $_POST['tanggal'],
                    'nyeri'             => $_POST['nyeri'],
                    'provokes'          => $_POST['provokes'],
                    'ket_provokes'      => $_POST['ket_provokes'],
                    'quality'           => $_POST['quality'],
                    'ket_quality'       => $_POST['ket_quality'],
                    'lokasi'            => $_POST['lokasi'],
                    'menyebar'          => $_POST['menyebar'],
                    'skala_nyeri'       => $_POST['skala_nyeri'],
                    'durasi'            => $_POST['durasi'],
                    'nyeri_hilang'      => $_POST['nyeri_hilang'],
                    'ket_nyeri'         => $_POST['ket_nyeri']
                ]);
            }
        }
        exit();
    }
    
    public function getAssessmentNyeriTampil($no_rawat)
    {
        $no_rawat = revertNorawat($no_rawat);
        $penilaian_ulang_nyeri = $this->db('penilaian_ulang_nyeri')
            ->join('petugas', 'petugas.nip=penilaian_ulang_nyeri.nip')
            ->where('no_rawat', $no_rawat)
            ->oneArray();
        
        if($penilaian_ulang_nyeri) {
            echo '<table class="table" width="100%">';
            echo '<thead><tr><th>Tanggal</th><th>Jenis Nyeri</th><th>Skala</th><th>Lokasi</th><th>Petugas</th><th>Aksi</th></tr></thead>';
            echo '<tbody>';
            echo '<tr>';
            echo '<td>'.$penilaian_ulang_nyeri['tanggal'].'</td>';
            echo '<td>'.$penilaian_ulang_nyeri['nyeri'].'</td>';
            echo '<td>'.$penilaian_ulang_nyeri['skala_nyeri'].'</td>';
            echo '<td>'.$penilaian_ulang_nyeri['lokasi'].'</td>';
            echo '<td>'.$penilaian_ulang_nyeri['nama'].'</td>';
            echo '<td>';
            echo '<button type="button" class="btn btn-warning btn-xs edit_assessment_nyeri" ';
            echo 'data-no_rawat="'.$penilaian_ulang_nyeri['no_rawat'].'" ';
            echo 'data-tanggal="'.$penilaian_ulang_nyeri['tanggal'].'" ';
            echo 'data-nyeri="'.$penilaian_ulang_nyeri['nyeri'].'" ';
            echo 'data-provokes="'.$penilaian_ulang_nyeri['provokes'].'" ';
            echo 'data-ket_provokes="'.htmlspecialchars($penilaian_ulang_nyeri['ket_provokes']).'" ';
            echo 'data-quality="'.$penilaian_ulang_nyeri['quality'].'" ';
            echo 'data-ket_quality="'.htmlspecialchars($penilaian_ulang_nyeri['ket_quality']).'" ';
            echo 'data-lokasi="'.htmlspecialchars($penilaian_ulang_nyeri['lokasi']).'" ';
            echo 'data-menyebar="'.$penilaian_ulang_nyeri['menyebar'].'" ';
            echo 'data-skala_nyeri="'.$penilaian_ulang_nyeri['skala_nyeri'].'" ';
            echo 'data-durasi="'.htmlspecialchars($penilaian_ulang_nyeri['durasi']).'" ';
            echo 'data-nyeri_hilang="'.$penilaian_ulang_nyeri['nyeri_hilang'].'" ';
            echo 'data-ket_nyeri="'.htmlspecialchars($penilaian_ulang_nyeri['ket_nyeri']).'" ';
            echo '>Edit</button> ';
            echo '<button type="button" class="btn btn-danger btn-xs hapus_assessment_nyeri" data-no_rawat="'.$penilaian_ulang_nyeri['no_rawat'].'" data-tanggal="'.$penilaian_ulang_nyeri['tanggal'].'">Hapus</button>';
            echo '</td>';
            echo '</tr>';
            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<div class="alert alert-info">Belum ada data assessment nyeri untuk pasien ini.</div>';
        }
        exit();
    }
    
    public function postHapusAssessmentNyeri()
    {
        header('Content-Type: application/json');
        
        try {
            // Validate required fields
            if(empty($_POST['no_rawat']) || empty($_POST['tanggal'])) {
                echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
                exit();
            }
            
            $result = $this->db('penilaian_ulang_nyeri')
                ->where('no_rawat', $_POST['no_rawat'])
                ->where('tanggal', $_POST['tanggal'])
                ->delete();
            
            if($result) {
                echo json_encode(['status' => 'success', 'message' => 'Data assessment nyeri berhasil dihapus']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data assessment nyeri']);
            }
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
        
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $cek_pegawai = $this->db('pegawai')->where('nik', $this->core->getUserInfo('username', $_SESSION['mlite_user']))->oneArray();
        $cek_role = '';
        if($cek_pegawai) {
          $cek_role = $this->core->getPegawaiInfo('nik', $this->core->getUserInfo('username', $_SESSION['mlite_user']));
        }
        echo $this->draw(MODULES.'/rawat_inap/js/admin/rawat_inap.js', ['cek_role' => $cek_role]);
        exit();
    }

    public function init()
    {
      $this->consid = $this->settings->get('settings.BpjsConsID');
      $this->secretkey = $this->settings->get('settings.BpjsSecretKey');
      $this->user_key = $this->settings->get('settings.BpjsUserKey');
      $this->api_url = $this->settings->get('settings.BpjsApiUrl');
    }

    public function apiList()
    {
        $username = $this->core->checkAuth('GET');
        if (!$this->core->checkPermission($username, 'can_read', 'rawat_inap')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $user_id = $this->db('mlite_users')->where('username', $username)->oneArray()['id'];
        $bangsal = str_replace(",","','", $this->core->getUserInfo('cap', $user_id, true));

        $draw = $_GET['draw'] ?? 0;
        $start = $_GET['start'] ?? 0;
        $length = $_GET['length'] ?? 10;
        $search = $_GET['search'] ?? '';
        $tgl_awal = $_GET['tgl_awal'] ?? null;
        $tgl_akhir = $_GET['tgl_akhir'] ?? null;
        $status_pulang = $_GET['status_pulang'] ?? '-';

        $sql = "SELECT kamar_inap.*, reg_periksa.no_rkm_medis, pasien.nm_pasien, kamar.kd_kamar, bangsal.nm_bangsal, dokter.nm_dokter 
                FROM kamar_inap 
                JOIN reg_periksa ON kamar_inap.no_rawat = reg_periksa.no_rawat
                JOIN pasien ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis
                JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar
                JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal
                JOIN dpjp_ranap ON kamar_inap.no_rawat = dpjp_ranap.no_rawat
                JOIN dokter ON dpjp_ranap.kd_dokter = dokter.kd_dokter
                WHERE 1=1";
        

        if ($this->core->getUserInfo('role', $user_id, true) != 'admin') {
          $sql .= " AND bangsal.kd_bangsal IN ('$bangsal')";
        }
        if ($status_pulang == '-') {
            $sql .= " AND kamar_inap.stts_pulang = '-'";
        } elseif ($status_pulang != 'all') {
            $sql .= " AND kamar_inap.stts_pulang = '$status_pulang'";
            if ($tgl_awal && $tgl_akhir) {
                $sql .= " AND kamar_inap.tgl_masuk BETWEEN '$tgl_awal' AND '$tgl_akhir'";
            }
        } else {
             if ($tgl_awal && $tgl_akhir) {
                $sql .= " AND kamar_inap.tgl_masuk BETWEEN '$tgl_awal' AND '$tgl_akhir'";
            }
        }

        if (!empty($search)) {
            $sql .= " AND (pasien.nm_pasien LIKE '%$search%' OR reg_periksa.no_rkm_medis LIKE '%$search%' OR kamar_inap.no_rawat LIKE '%$search%')";
        }

        $sql .= " GROUP BY kamar_inap.no_rawat";

        $stmt = $this->db()->pdo()->prepare($sql);
        $stmt->execute();
        $totalRecords = $stmt->rowCount();

        $sql .= " LIMIT $start, $length";
        $stmt = $this->db()->pdo()->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            "status" => "success",
            "data" => $rows,
            "meta" => [
                "page" => floor($start / $length) + 1,
                "per_page" => intval($length),
                "total" => $totalRecords
            ]
        ];
    }

    public function apiShow($no_rawat = null)
    {
        $username = $this->core->checkAuth('GET');
        if (!$this->core->checkPermission($username, 'can_read', 'rawat_inap')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        if(!$no_rawat) {
             return ['status' => 'error', 'message' => 'No rawat missing'];
        }
        $no_rawat = revertNoRawat($no_rawat);
        $row = $this->db('kamar_inap')
            ->join('reg_periksa', 'kamar_inap.no_rawat=reg_periksa.no_rawat')
            ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
            ->join('kamar', 'kamar_inap.kd_kamar=kamar.kd_kamar')
            ->join('bangsal', 'kamar.kd_bangsal=bangsal.kd_bangsal')
            ->join('dpjp_ranap', 'kamar_inap.no_rawat=dpjp_ranap.no_rawat')
            ->join('dokter', 'dpjp_ranap.kd_dokter=dokter.kd_dokter')
            ->where('kamar_inap.no_rawat', $no_rawat)
            ->oneArray();
            
        if($row) {
            return ['status' => 'success', 'data' => $row];
        } else {
            return ['status' => 'error', 'message' => 'Not found'];
        }
    }

    public function apiCreate()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_create', 'rawat_inap')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        if (empty($input['no_rawat']) || empty($input['kd_kamar'])) {
             return ['status' => 'error', 'message' => 'Data incomplete'];
        }

        $kamar = $this->db('kamar')->where('kd_kamar', $input['kd_kamar'])->oneArray();
        
        $data = [
            'no_rawat' => $input['no_rawat'],
            'kd_kamar' => $input['kd_kamar'],
            'trf_kamar' => $kamar['trf_kamar'],
            'lama' => $input['lama'] ?? 1,
            'tgl_masuk' => $input['tgl_masuk'] ?? date('Y-m-d'),
            'jam_masuk' => $input['jam_masuk'] ?? date('H:i:s'),
            'ttl_biaya' => $kamar['trf_kamar'] * ($input['lama'] ?? 1),
            'tgl_keluar' => '0000-00-00',
            'jam_keluar' => '00:00:00',
            'diagnosa_awal' => $input['diagnosa_awal'] ?? '-',
            'diagnosa_akhir' => '-',
            'stts_pulang' => '-'
        ];

        try {
            $this->db('kamar_inap')->save($data);
            $this->db('kamar')->where('kd_kamar', $input['kd_kamar'])->save(['status' => 'ISI']);
            
            if(!empty($input['kd_dokter'])) {
                 $this->db('dpjp_ranap')->save(['no_rawat' => $input['no_rawat'], 'kd_dokter' => $input['kd_dokter']]);
            }
            
            $this->db('reg_periksa')->where('no_rawat', $input['no_rawat'])->save(['status_lanjut' => 'Ranap']);

            return ['status' => 'created', 'data' => $data];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiUpdate($no_rawat = null)
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_update', 'rawat_inap')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        if(!$no_rawat) {
             return ['status' => 'error', 'message' => 'No rawat missing'];
        }
        
        $no_rawat = revertNoRawat($no_rawat);
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        try {
            $this->db('kamar_inap')->where('no_rawat', $no_rawat)->save($input);
            
            // If discharge
            if(isset($input['stts_pulang']) && $input['stts_pulang'] != '-') {
                 $kamar_inap = $this->db('kamar_inap')->where('no_rawat', $no_rawat)->oneArray();
                 $this->db('kamar')->where('kd_kamar', $kamar_inap['kd_kamar'])->save(['status' => 'KOSONG']);
                 $this->db('reg_periksa')->where('no_rawat', $no_rawat)->save(['stts' => 'Sudah']);
            }
            
            return ['status' => 'success', 'data' => $input];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiDelete($no_rawat = null)
    {
        $username = $this->core->checkAuth('DELETE');
        if (!$this->core->checkPermission($username, 'can_delete', 'rawat_inap')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        if(!$no_rawat) {
             return ['status' => 'error', 'message' => 'No rawat missing'];
        }

        $no_rawat = revertNoRawat($no_rawat);
        $kamar_inap = $this->db('kamar_inap')->where('no_rawat', $no_rawat)->oneArray();

        try {
            $this->db('kamar_inap')->where('no_rawat', $no_rawat)->delete();
            if($kamar_inap) {
                $this->db('kamar')->where('kd_kamar', $kamar_inap['kd_kamar'])->save(['status' => 'KOSONG']);
                $this->db('dpjp_ranap')->where('no_rawat', $no_rawat)->delete();
                $this->db('reg_periksa')->where('no_rawat', $no_rawat)->save(['status_lanjut' => 'Ralan']);
            }
            return ['status' => 'success', 'message' => 'Data deleted'];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    public function apiShowSoap($no_rawat = null)
    {
        $username = $this->core->checkAuth('GET');
        if (!$this->core->checkPermission($username, 'can_read', 'rawat_inap')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }
        
        if(!$no_rawat) {
             return ['status' => 'error', 'message' => 'No rawat missing'];
        }
        $no_rawat = revertNoRawat($no_rawat);
        
        $pemeriksaan = $this->db('pemeriksaan_ranap')->where('no_rawat', $no_rawat)->toArray();
        return ['status' => 'success', 'data' => $pemeriksaan];
    }
    
    public function apiSaveSoap()
    {
        $username = $this->core->checkAuth('POST');
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;
        
        $nip = $username;

        // Map input to schema columns
        $data = [
            'no_rawat' => $input['no_rawat'],
            'tgl_perawatan' => $input['tgl_perawatan'],
            'jam_rawat' => $input['jam_rawat'],
            'suhu_tubuh' => $input['suhu_tubuh'] ?? null,
            'tensi' => $input['tensi'] ?? '-',
            'nadi' => $input['nadi'] ?? null,
            'respirasi' => $input['respirasi'] ?? null,
            'tinggi' => $input['tinggi'] ?? null,
            'berat' => $input['berat'] ?? null,
            'spo2' => $input['spo2'] ?? '-',
            'gcs' => $input['gcs'] ?? null,
            'kesadaran' => $input['kesadaran'] ?? 'Compos Mentis',
            'keluhan' => $input['keluhan'] ?? null,
            'pemeriksaan' => $input['pemeriksaan'] ?? null,
            'alergi' => $input['alergi'] ?? null,
            'penilaian' => $input['penilaian'] ?? '-', 
            'rtl' => $input['rtl'] ?? '-',
            'instruksi' => $input['instruksi'] ?? '-',
            'evaluasi' => $input['evaluasi'] ?? '-',
            'nip' => $nip
        ];
        
        try {
             // Check based on primary key (no_rawat, tgl_perawatan, jam_rawat)
             if(!$this->db('pemeriksaan_ranap')
                ->where('no_rawat', $data['no_rawat'])
                ->where('tgl_perawatan', $data['tgl_perawatan'])
                ->where('jam_rawat', $data['jam_rawat'])
                ->oneArray()) {

                if (!$this->core->checkPermission($username, 'can_create', 'rawat_inap')) {
                    return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
                }

                $this->db('pemeriksaan_ranap')->save($data);
              } else {

                if (!$this->core->checkPermission($username, 'can_update', 'rawat_inap')) {
                    return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
                }

                $this->db('pemeriksaan_ranap')
                    ->where('no_rawat', $data['no_rawat'])
                    ->where('tgl_perawatan', $data['tgl_perawatan'])
                    ->where('jam_rawat', $data['jam_rawat'])
                    ->save($data);
              }
              return ['status' => 'success', 'data' => $data];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    public function apiDeleteSoap()
    {
        $username = $this->core->checkAuth('DELETE');
        if (!$this->core->checkPermission($username, 'can_delete', 'rawat_inap')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;
        
        try {
            $this->db('pemeriksaan_ranap')
                ->where('no_rawat', $input['no_rawat'])
                ->where('tgl_perawatan', $input['tgl_perawatan'])
                ->where('jam_rawat', $input['jam_rawat'])
                ->delete();
             return ['status' => 'success', 'message' => 'Deleted'];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiShowDetail($category = null, $no_rawat = null)
    {
        $username = $this->core->checkAuth('GET');
        if (!$this->core->checkPermission($username, 'can_read', 'rawat_inap')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        if(!$no_rawat) {
             return ['status' => 'error', 'message' => 'No rawat missing'];
        }
        $no_rawat = revertNorawat($no_rawat);
        $kategori = trim($category);
        $no_resep = isset($_GET['no_resep']) ? $_GET['no_resep'] : null;

        $pasien = $this->db('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
            ->where('no_rawat', $no_rawat)
            ->oneArray();

        $patient_info = [
            'nm_pasien' => $pasien['nm_pasien'] ?? '',
            'no_rkm_medis' => $pasien['no_rkm_medis'] ?? ''
        ];

        $data = [];
        try {        
            if($kategori == 'tindakan') {
                $data['rawat_inap_dr'] = $this->db('rawat_inap_dr')
                    ->join('jns_perawatan_inap', 'jns_perawatan_inap.kd_jenis_prw=rawat_inap_dr.kd_jenis_prw')
                    ->join('dokter', 'dokter.kd_dokter=rawat_inap_dr.kd_dokter')
                    ->where('no_rawat', $no_rawat)
                    ->toArray();
                $data['rawat_inap_pr'] = $this->db('rawat_inap_pr')
                    ->join('jns_perawatan_inap', 'jns_perawatan_inap.kd_jenis_prw=rawat_inap_pr.kd_jenis_prw')
                    ->join('petugas', 'petugas.nip=rawat_inap_pr.nip')
                    ->where('no_rawat', $no_rawat)
                    ->toArray();
                $data['rawat_inap_drpr'] = $this->db('rawat_inap_drpr')
                    ->join('jns_perawatan_inap', 'jns_perawatan_inap.kd_jenis_prw=rawat_inap_drpr.kd_jenis_prw')
                    ->join('dokter', 'dokter.kd_dokter=rawat_inap_drpr.kd_dokter')
                    ->join('petugas', 'petugas.nip=rawat_inap_drpr.nip')
                    ->where('no_rawat', $no_rawat)
                    ->toArray();

                return [
                    'status' => 'success',
                    'patient' => $patient_info,
                    'data' => [
                        'rawat_jl_dr' => $data['rawat_inap_dr'],
                        'rawat_jl_pr' => $data['rawat_inap_pr'],
                        'rawat_jl_drpr' => $data['rawat_inap_drpr']
                    ]
                ];

            } elseif($kategori == 'obat') {
                $query = $this->db('resep_dokter')
                    ->join('resep_obat', 'resep_obat.no_resep = resep_dokter.no_resep')
                    ->join('databarang', 'databarang.kode_brng = resep_dokter.kode_brng')
                    ->where('resep_obat.no_rawat', $no_rawat)
                    ->where('resep_obat.status', 'ranap');
                
                if ($no_resep) {
                    $query->where('resep_obat.no_resep', $no_resep);
                }

                $data['obat'] = $query->toArray();

                return ['status' => 'success', 'patient' => $patient_info, 'data' => $data['obat']];

            } elseif($kategori == 'racikan') {
                $query = $this->db('resep_dokter_racikan')
                    ->join('resep_obat', 'resep_obat.no_resep = resep_dokter_racikan.no_resep')
                    ->join('metode_racik', 'metode_racik.kd_racik = resep_dokter_racikan.kd_racik')
                    ->where('resep_obat.no_rawat', $no_rawat)
                    ->where('resep_obat.status', 'ranap');

                if ($no_resep) {
                    $query->where('resep_obat.no_resep', $no_resep);
                }

                $resep_racikan = $query->toArray();

                foreach ($resep_racikan as &$racikan) {
                    $racikan['detail'] = $this->db('resep_dokter_racikan_detail')
                        ->join('databarang', 'databarang.kode_brng = resep_dokter_racikan_detail.kode_brng')
                        ->where('no_resep', $racikan['no_resep'])
                        ->where('no_racik', $racikan['no_racik'])
                        ->toArray();
                }
                $data['racikan'] = $resep_racikan;
                return ['status' => 'success', 'patient' => $patient_info, 'data' => $data['racikan']];

            } else {
                return ['status' => 'error', 'message' => 'Category not supported: ' . $kategori];
            }
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
        
    }
    
    public function apiSaveDetail()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_create', 'rawat_inap')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;
        
        $kategori = trim($input['kat']);

        if (empty($kategori) || empty($input['no_rawat']) || empty($input['kd_jenis_prw'])) {
            return ['status' => 'error', 'message' => 'Data incomplete'];
        }

        try {

            if($kategori == 'tindakan') {
                $jns_perawatan = $this->db('jns_perawatan_inap')->where('kd_jenis_prw', $input['kd_jenis_prw'])->oneArray();
                if($input['provider'] == 'rawat_inap_dr') {
                  for ($i = 0; $i < $input['jml_tindakan']; $i++) {
                    $this->db('rawat_inap_dr')->save([
                      'no_rawat' => $input['no_rawat'],
                      'kd_jenis_prw' => $input['kd_jenis_prw'],
                      'kd_dokter' => $input['kode_provider'],
                      'tgl_perawatan' => $input['tgl_perawatan'],
                      'jam_rawat' => date('H:i:s', strtotime($input['jam_rawat']. ' +'.$i.'0 seconds')),
                      'material' => $jns_perawatan['material'],
                      'bhp' => $jns_perawatan['bhp'],
                      'tarif_tindakandr' => $jns_perawatan['tarif_tindakandr'],
                      'kso' => $jns_perawatan['kso'],
                      'menejemen' => $jns_perawatan['menejemen'],
                      'biaya_rawat' => $jns_perawatan['total_byrdr']
                    ]);
                  }
                }
                if($input['provider'] == 'rawat_inap_pr') {
                  for ($i = 0; $i < $input['jml_tindakan']; $i++) {
                    $this->db('rawat_inap_pr')->save([
                      'no_rawat' => $input['no_rawat'],
                      'kd_jenis_prw' => $input['kd_jenis_prw'],
                      'nip' => $input['kode_provider2'] ?? $input['kode_provider'], 
                      'tgl_perawatan' => $input['tgl_perawatan'],
                      'jam_rawat' => date('H:i:s', strtotime($input['jam_rawat']. ' +'.$i.'0 seconds')),
                      'material' => $jns_perawatan['material'],
                      'bhp' => $jns_perawatan['bhp'],
                      'tarif_tindakanpr' => $jns_perawatan['tarif_tindakanpr'],
                      'kso' => $jns_perawatan['kso'],
                      'menejemen' => $jns_perawatan['menejemen'],
                      'biaya_rawat' => $jns_perawatan['total_byrpr']
                    ]);
                  }
                }
                if($input['provider'] == 'rawat_inap_drpr') {
                  for ($i = 0; $i < $input['jml_tindakan']; $i++) {
                    $this->db('rawat_inap_drpr')->save([
                      'no_rawat' => $input['no_rawat'],
                      'kd_jenis_prw' => $input['kd_jenis_prw'],
                      'kd_dokter' => $input['kode_provider'],
                      'nip' => $input['kode_provider2'],
                      'tgl_perawatan' => $input['tgl_perawatan'],
                      'jam_rawat' => date('H:i:s', strtotime($input['jam_rawat']. ' +'.$i.'0 seconds')),
                      'material' => $jns_perawatan['material'],
                      'bhp' => $jns_perawatan['bhp'],
                      'tarif_tindakandr' => $jns_perawatan['tarif_tindakandr'],
                      'tarif_tindakanpr' => $jns_perawatan['tarif_tindakanpr'],
                      'kso' => $jns_perawatan['kso'],
                      'menejemen' => $jns_perawatan['menejemen'],
                      'biaya_rawat' => $jns_perawatan['total_byrdrpr']
                    ]);
                  }
                }
                return ['status' => 'success', 'message' => 'Detail saved'];
            } elseif ($kategori == 'obat') {
                $no_resep = $this->core->setNoResep($input['tgl_perawatan']);
                $cek_resep = $this->db('resep_obat')->where('no_rawat', $input['no_rawat'])->where('tgl_peresepan', $input['tgl_perawatan'])->where('tgl_perawatan', '0000-00-00')->where('status', 'ralan')->oneArray();

                if (empty($cek_resep)) {
                    $this->db('resep_obat')
                        ->save([
                            'no_resep' => $no_resep,
                            'tgl_perawatan' => '0000-00-00',
                            'jam' => '00:00:00',
                            'no_rawat' => $input['no_rawat'],
                            'kd_dokter' => $input['kode_provider'] ?? $username,
                            'tgl_peresepan' => $input['tgl_perawatan'],
                            'jam_peresepan' => $input['jam_rawat'],
                            'status' => 'ranap',
                            'tgl_penyerahan' => '0000-00-00',
                            'jam_penyerahan' => '00:00:00'
                        ]);

                    if ($this->db('resep_obat')->where('no_resep', $no_resep)->oneArray()) {
                        $this->db('resep_dokter')
                            ->save([
                                'no_resep' => $no_resep,
                                'kode_brng' => $input['kd_jenis_prw'],
                                'jml' => $input['jml'],
                                'aturan_pakai' => $input['aturan_pakai']
                            ]);
                    }
                } else {
                    $no_resep = $cek_resep['no_resep'];
                    $this->db('resep_dokter')
                        ->save([
                            'no_resep' => $no_resep,
                            'kode_brng' => $input['kd_jenis_prw'],
                            'jml' => $input['jml'],
                            'aturan_pakai' => $input['aturan_pakai']
                        ]);
                }
                return ['status' => 'success', 'message' => 'Obat saved'];
            } elseif ($kategori == 'racikan') {
                $no_resep = $this->core->setNoResep($input['tgl_perawatan']);
                $cek_resep = $this->db('resep_obat')->where('no_rawat', $input['no_rawat'])->where('tgl_peresepan', $input['tgl_perawatan'])->where('tgl_perawatan', '0000-00-00')->where('status', 'ralan')->oneArray();
                
                $jam_rawat = $input['jam_rawat'] ?? date('H:i:s');

                if (empty($cek_resep)) {
                    $this->db('resep_obat')
                        ->save([
                            'no_resep' => $no_resep,
                            'tgl_perawatan' => '0000-00-00',
                            'jam' => '00:00:00',
                            'no_rawat' => $input['no_rawat'],
                            'kd_dokter' => $input['kode_provider'] ?? $username,
                            'tgl_peresepan' => $input['tgl_perawatan'],
                            'jam_peresepan' => $jam_rawat,
                            'status' => 'ranap',
                            'tgl_penyerahan' => '0000-00-00',
                            'jam_penyerahan' => '00:00:00'
                        ]);

                    if ($this->db('resep_obat')->where('no_resep', $no_resep)->oneArray()) {
                        $no_racik = $this->db('resep_dokter_racikan')->where('no_resep', $no_resep)->count();
                        $no_racik = $no_racik + 1;
                        $this->db('resep_dokter_racikan')
                            ->save([
                                'no_resep' => $no_resep,
                                'no_racik' => $no_racik,
                                'nama_racik' => $input['nama_racik'],
                                'kd_racik' => $input['kd_jenis_prw'],
                                'jml_dr' => $input['jml'],
                                'aturan_pakai' => $input['aturan_pakai'],
                                'keterangan' => $input['keterangan']
                            ]);
                        
                        $kode_brng_list = is_string($input['kode_brng']) ? json_decode($input['kode_brng'], true) : $input['kode_brng'];
                        $kandungan_list = is_string($input['kandungan']) ? json_decode($input['kandungan'], true) : $input['kandungan'];
                        
                        $kode_brng_count = count($kode_brng_list);
                        for ($i = 0; $i < $kode_brng_count; $i++) {
                            $kapasitas = $this->db('databarang')->where('kode_brng', $kode_brng_list[$i]['value'])->oneArray();
                            $jml = $input['jml'] * $kandungan_list[$i]['value'];
                            if ($kapasitas['kapasitas'] > 0) {
                                $jml = round(($jml / $kapasitas['kapasitas']), 1);
                            }
                            $this->db('resep_dokter_racikan_detail')
                                ->save([
                                    'no_resep' => $no_resep,
                                    'no_racik' => $no_racik,
                                    'kode_brng' => $kode_brng_list[$i]['value'],
                                    'p1' => '1',
                                    'p2' => '1',
                                    'kandungan' => $kandungan_list[$i]['value'],
                                    'jml' => $jml
                                ]);
                        }
                    }
                } else {
                    $no_resep = $cek_resep['no_resep'];
                    $no_racik = $this->db('resep_dokter_racikan')->where('no_resep', $no_resep)->count();
                    $no_racik = $no_racik + 1;
                    $this->db('resep_dokter_racikan')
                        ->save([
                            'no_resep' => $no_resep,
                            'no_racik' => $no_racik,
                            'nama_racik' => $input['nama_racik'],
                            'kd_racik' => $input['kd_jenis_prw'],
                            'jml_dr' => $input['jml'],
                            'aturan_pakai' => $input['aturan_pakai'],
                            'keterangan' => $input['keterangan']
                        ]);

                    $kode_brng_list = is_string($input['kode_brng']) ? json_decode($input['kode_brng'], true) : $input['kode_brng'];
                    $kandungan_list = is_string($input['kandungan']) ? json_decode($input['kandungan'], true) : $input['kandungan'];

                    $kode_brng_count = count($kode_brng_list);
                    for ($i = 0; $i < $kode_brng_count; $i++) {
                        $kapasitas = $this->db('databarang')->where('kode_brng', $kode_brng_list[$i]['value'])->oneArray();
                        $jml = $input['jml'] * $kandungan_list[$i]['value'];
                        if ($kapasitas['kapasitas'] > 0) {
                             $jml = round(($jml / $kapasitas['kapasitas']), 1);
                        }
                        $this->db('resep_dokter_racikan_detail')
                            ->save([
                                'no_resep' => $no_resep,
                                'no_racik' => $no_racik,
                                'kode_brng' => $kode_brng_list[$i]['value'],
                                'p1' => '1',
                                'p2' => '1',
                                'kandungan' => $kandungan_list[$i]['value'],
                                'jml' => $jml
                            ]);
                    }
                }
                return ['status' => 'success', 'message' => 'Racikan saved'];
            } else {
                return ['status' => 'error', 'message' => 'Category not supported'];
            }
        } catch (\PDOException $e) {
            $message = $e->getMessage();
            $message = preg_replace('/`[^`]+`\./', '', $message);
            return ['status' => 'error', 'message' => $message];
        }
    }
    
    public function apiDeleteDetail()
    {
        $username = $this->core->checkAuth('DELETE');
        if (!$this->core->checkPermission($username, 'can_delete', 'rawat_inap')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;
        
        $kategori = trim($input['kat']);

        if($kategori == 'tindakan') {
            if($input['provider'] == 'rawat_inap_dr') {
                $this->db('rawat_inap_dr')
                ->where('no_rawat', $input['no_rawat'])
                ->where('kd_jenis_prw', $input['kd_jenis_prw'])
                ->where('tgl_perawatan', $input['tgl_perawatan'])
                ->where('jam_rawat', $input['jam_rawat'])
                ->delete();
            }
             if($input['provider'] == 'rawat_inap_pr') {
                $this->db('rawat_inap_pr')
                ->where('no_rawat', $input['no_rawat'])
                ->where('kd_jenis_prw', $input['kd_jenis_prw'])
                ->where('tgl_perawatan', $input['tgl_perawatan'])
                ->where('jam_rawat', $input['jam_rawat'])
                ->delete();
            }
             if($input['provider'] == 'rawat_inap_drpr') {
                $this->db('rawat_inap_drpr')
                ->where('no_rawat', $input['no_rawat'])
                ->where('kd_jenis_prw', $input['kd_jenis_prw'])
                ->where('tgl_perawatan', $input['tgl_perawatan'])
                ->where('jam_rawat', $input['jam_rawat'])
                ->delete();
            }
        } elseif ($kategori == 'obat') {
            if(isset($input['kd_jenis_prw'])) {
                $this->db('resep_dokter')
                ->where('no_resep', $input['no_resep'])
                ->where('kode_brng', $input['kd_jenis_prw'])
                ->delete();
            } else {
                $this->db('resep_obat')
                ->where('no_resep', $input['no_resep'])
                ->where('no_rawat', $input['no_rawat'])
                ->where('tgl_peresepan', $input['tgl_peresepan'])
                ->where('jam_peresepan', $input['jam_peresepan'])
                ->delete();
            }
        } elseif ($kategori == 'racikan') {
            if(isset($input['kd_jenis_prw']) && isset($input['no_racik'])) {
                $this->db('resep_dokter_racikan_detail')
                    ->where('no_resep', $input['no_resep'])
                    ->where('no_racik', $input['no_racik'])
                    ->where('kode_brng', $input['kd_jenis_prw'])
                    ->delete();
            } elseif (isset($input['no_racik'])) {
                $this->db('resep_dokter_racikan')
                    ->where('no_resep', $input['no_resep'])
                    ->where('no_racik', $input['no_racik'])
                    ->delete();
                $this->db('resep_dokter_racikan_detail')
                    ->where('no_resep', $input['no_resep'])
                    ->where('no_racik', $input['no_racik'])
                    ->delete();
            }
        }
        
        return ['status' => 'success'];
    }

    public function apiSaveDiagnosa()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_create', 'rawat_inap')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        $data = [
            'no_rawat' => $input['no_rawat'],
            'kd_penyakit' => $input['kd_penyakit'],
            'status' => $input['status'] ?? 'Ranap',
            'prioritas' => $input['prioritas'],
            'status_penyakit' => 'Baru'
        ];

        try {
            $this->db('diagnosa_pasien')->save($data);
            return ['status' => 'success', 'data' => $data];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiDeleteDiagnosa()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_delete', 'rawat_inap')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        try {
            $this->db('diagnosa_pasien')
                ->where('no_rawat', $input['no_rawat'])
                ->where('kd_penyakit', $input['kd_penyakit'])
                ->where('prioritas', $input['prioritas'])
                ->delete();
            return ['status' => 'success'];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiSaveProsedur()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_create', 'rawat_inap')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        $data = [
            'no_rawat' => $input['no_rawat'],
            'kode' => $input['kode'],
            'status' => $input['status'] ?? 'Ranap',
            'prioritas' => $input['prioritas']
        ];

        try {
            $this->db('prosedur_pasien')->save($data);
            return ['status' => 'success', 'data' => $data];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiDeleteProsedur()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_delete', 'rawat_inap')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        try {
            $this->db('prosedur_pasien')
                ->where('no_rawat', $input['no_rawat'])
                ->where('kode', $input['kode'])
                ->where('prioritas', $input['prioritas'])
                ->delete();
            return ['status' => 'success'];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiSaveCatatan()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_create', 'rawat_inap')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        $kd_dokter = $this->core->getRegPeriksaInfo('kd_dokter', $input['no_rawat']);

        $data = [
            'tanggal' => date('Y-m-d'),
            'jam' => date('H:i:s'),
            'no_rawat' => $input['no_rawat'],
            'kd_dokter' => $kd_dokter,
            'catatan' => $input['catatan']
        ];

        try {
            $this->db('catatan_perawatan')->save($data);
            return ['status' => 'success', 'data' => $data];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiDeleteCatatan()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_delete', 'rawat_jalan')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        try {
            $this->db('catatan_perawatan')
                ->where('no_rawat', $input['no_rawat'])
                ->where('tanggal', $input['tanggal'])
                ->where('jam', $input['jam'])
                ->delete();
            return ['status' => 'success'];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiSaveBerkas()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_create', 'rawat_inap')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        $data = [
            'no_rawat' => $input['no_rawat'],
            'kode' => $input['judul'] ?? '',
            'lokasi_file' => $input['deskripsi'] ?? ''
        ];

        try {
            $this->db('berkas_digital_perawatan')->save($data);
            return ['status' => 'success', 'data' => $data];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiDeleteBerkas()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_delete', 'rawat_jalan')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        try {
            $this->db('berkas_digital_perawatan')
                ->where('no_rawat', $input['no_rawat'])
                ->where('kode', $input['judul'] ?? $input['kode'])
                ->where('lokasi_file', $input['deskripsi'] ?? $input['lokasi_file'])
                ->delete();
            return ['status' => 'success'];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiSaveResume()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_create', 'rawat_inap')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        $kd_dokter = $this->core->getRegPeriksaInfo('kd_dokter', $input['no_rawat']);

        // Check if exists
        $exists = $this->db('resume_pasien')->where('no_rawat', $input['no_rawat'])->oneArray();

        $data = [
            'no_rawat' => $input['no_rawat'],
            'kd_dokter' => $kd_dokter,
            'diagnosa_utama' => $input['diagnosa_utama'] ?? '',
            'diagnosa_sekunder' => $input['diagnosa_sekunder'] ?? '',
            'jalannya_penyakit' => $input['jalannya_penyakit'] ?? '',
            'obat_pulang' => $input['terapi'] ?? '',
            'kondisi_pulang' => $input['kondisi_pulang'] ?? 'Hidup',
            // Default required fields
            'keluhan_utama' => '',
            'pemeriksaan_penunjang' => '',
            'hasil_laborat' => '',
            'kd_diagnosa_utama' => '',
            'kd_diagnosa_sekunder' => '',
            'diagnosa_sekunder2' => '',
            'kd_diagnosa_sekunder2' => '',
            'diagnosa_sekunder3' => '',
            'kd_diagnosa_sekunder3' => '',
            'diagnosa_sekunder4' => '',
            'kd_diagnosa_sekunder4' => '',
            'prosedur_utama' => '',
            'kd_prosedur_utama' => '',
            'prosedur_sekunder' => '',
            'kd_prosedur_sekunder' => '',
            'prosedur_sekunder2' => '',
            'kd_prosedur_sekunder2' => '',
            'prosedur_sekunder3' => '',
            'kd_prosedur_sekunder3' => ''
        ];

        try {
            if ($exists) {
                $this->db('resume_pasien')->where('no_rawat', $input['no_rawat'])->save($data);
            } else {
                $this->db('resume_pasien')->save($data);
            }
            return ['status' => 'success', 'data' => $data];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiSaveRujukanInternal()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_create', 'rawat_inap')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        $data = [
            'no_rawat' => $input['no_rawat'],
            'kd_poli' => $input['kd_poli'],
            'kd_dokter' => $input['kd_dokter'],
            'isi_rujukan' => $input['catatan'] ?? ''
        ];

        try {
            $this->db('mlite_rujukan_internal_poli')->save($data);
            return ['status' => 'success', 'data' => $data];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiDeleteRujukanInternal()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_delete', 'rawat_inap')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        try {
            $this->db('mlite_rujukan_internal_poli')
                ->where('no_rawat', $input['no_rawat'])
                ->where('kd_poli', $input['kd_poli'])
                ->where('kd_dokter', $input['kd_dokter'])
                ->delete();
            return ['status' => 'success'];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiSaveLaporanOperasi()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_create', 'rawat_inap')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        $data = [
            'no_rawat' => $input['no_rawat'],
            'tanggal' => $input['tanggal'] . ' ' . $input['jam'],
            'operator' => $input['operator'],
            'laporan_operasi' => $input['laporan'] ?? '',
            // Defaults
            'operator2' => '-', 'operator3' => '-', 'asisten_operator1' => '-', 'asisten_operator2' => '-', 'asisten_operator3' => '-',
            'instrumen' => '-', 'dokter_anak' => '-', 'perawat_resusitas' => '-', 'dokter_anestesi' => '-', 'asisten_anestesi' => '-',
            'bidan' => '-', 'perawat_luar' => '-', 'omloop' => '-', 'omloop2' => '-', 'omloop3' => '-', 'omloop4' => '-', 'omloop5' => '-'
        ];

        try {
            $this->db('laporan_operasi')->save($data);
            return ['status' => 'success', 'data' => $data];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiDeleteLaporanOperasi()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_delete', 'rawat_inap')) {
            return ['status' => 'error', 'message' => 'Invalid User Permission Credentials'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;

        try {
            $tanggal = $input['tanggal'];
            if(isset($input['jam'])) {
                $tanggal .= ' ' . $input['jam'];
            }

            $this->db('laporan_operasi')
                ->where('no_rawat', $input['no_rawat'])
                ->where('tanggal', $tanggal)
                ->delete();
            return ['status' => 'success'];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
        $this->core->addJS(url('assets/jscripts/lightbox/lightbox.min.js'));
        $this->core->addCSS(url('assets/jscripts/lightbox/lightbox.min.css'));
        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));
        $this->core->addJS('https://cdn.jsdelivr.net/npm/chart.js');
        $this->core->addJS(url([ADMIN, 'rawat_inap', 'javascript']), 'footer');
    }

}