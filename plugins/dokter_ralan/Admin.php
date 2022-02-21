<?php
namespace Plugins\Dokter_Ralan;

use Systems\AdminModule;
use Plugins\Icd\DB_ICD;

class Admin extends AdminModule
{

    private $_uploads = WEBAPPS_PATH.'/berkasrawat/pages/upload';
    public function navigation()
    {
        return [
            'Kelola'   => 'manage',
        ];
    }

    public function anyManage()
    {
        $tgl_kunjungan = date('Y-m-d');
        $tgl_kunjungan_akhir = date('Y-m-d');
        $status_periksa = '';

        if(isset($_POST['periode_rawat_jalan'])) {
          $tgl_kunjungan = $_POST['periode_rawat_jalan'];
        }
        if(isset($_POST['periode_rawat_jalan_akhir'])) {
          $tgl_kunjungan_akhir = $_POST['periode_rawat_jalan_akhir'];
        }
        if(isset($_POST['status_periksa'])) {
          $status_periksa = $_POST['status_periksa'];
        }
        $cek_vclaim = $this->db('mlite_modules')->where('dir', 'vclaim')->oneArray();
        $responsivevoice =  $this->settings->get('settings.responsivevoice');
        $this->_Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa);
        return $this->draw('manage.html', ['rawat_jalan' => $this->assign, 'cek_vclaim' => $cek_vclaim, 'responsivevoice' => $responsivevoice, 'admin_mode' => $this->settings->get('settings.admin_mode')]);
    }

    public function anyDisplay()
    {
        $tgl_kunjungan = date('Y-m-d');
        $tgl_kunjungan_akhir = date('Y-m-d');
        $status_periksa = '';

        if(isset($_POST['periode_rawat_jalan'])) {
          $tgl_kunjungan = $_POST['periode_rawat_jalan'];
        }
        if(isset($_POST['periode_rawat_jalan_akhir'])) {
          $tgl_kunjungan_akhir = $_POST['periode_rawat_jalan_akhir'];
        }
        if(isset($_POST['status_periksa'])) {
          $status_periksa = $_POST['status_periksa'];
        }
        $cek_vclaim = $this->db('mlite_modules')->where('dir', 'vclaim')->oneArray();
        $responsivevoice =  $this->settings->get('settings.responsivevoice');
        $this->_Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa);
        echo $this->draw('display.html', ['rawat_jalan' => $this->assign, 'cek_vclaim' => $cek_vclaim, 'responsivevoice' => $responsivevoice, 'admin_mode' => $this->settings->get('settings.admin_mode')]);
        exit();
    }

    public function _Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa='')
    {
        if($this->settings->get('settings.responsivevoice') == 'true') {
          $this->core->addJS(url('assets/jscripts/responsivevoice.js'));
        }
        $this->_addHeaderFiles();
        $username = $this->core->getUserInfo('username', null, true);
        $this->assign['poliklinik']     = $this->db('poliklinik')->where('status', '1')->toArray();
        $this->assign['dokter']         = $this->db('dokter')->where('status', '1')->toArray();
        $this->assign['penjab']       = $this->db('penjab')->toArray();
        $this->assign['no_rawat'] = '';
        $this->assign['no_reg']     = '';
        $this->assign['tgl_registrasi']= date('Y-m-d');
        $this->assign['jam_reg']= date('H:i:s');

        $poliklinik = str_replace(",","','", $this->core->getUserInfo('cap', null, true));
        $igd = $this->settings('settings', 'igd');
        $sql = "SELECT reg_periksa.*,
            pasien.*,
            dokter.*,
            poliklinik.*,
            penjab.*
          FROM reg_periksa, pasien, dokter, poliklinik, penjab
          WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis
          AND reg_periksa.kd_poli != '$igd'
          AND reg_periksa.tgl_registrasi BETWEEN '$tgl_kunjungan' AND '$tgl_kunjungan_akhir'
          AND reg_periksa.kd_dokter = dokter.kd_dokter
          AND reg_periksa.kd_poli = poliklinik.kd_poli
          AND reg_periksa.kd_pj = penjab.kd_pj";

        if ($this->core->getUserInfo('role') != 'admin') {
          if($this->settings->get('settings.dokter_ralan_per_dokter') == 'true') {
            $sql .= " AND reg_periksa.kd_dokter = '$username'";
          } else {
            $sql .= " AND reg_periksa.kd_poli IN ('$poliklinik')";
          }
        }
        if($status_periksa == 'belum') {
          $sql .= " AND reg_periksa.stts = 'Belum'";
        }
        if($status_periksa == 'selesai') {
          $sql .= " AND reg_periksa.stts = 'Sudah'";
        }
        if($status_periksa == 'lunas') {
          $sql .= " AND reg_periksa.status_bayar = 'Sudah Bayar'";
        }

        $stmt = $this->db()->pdo()->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $this->assign['list'] = [];
        foreach ($rows as $row) {
          $row['viewURL'] = url([ADMIN, 'dokter_ralan', 'view', convertNorawat($row['no_rawat'])]);
          $this->assign['list'][] = $row;
        }

    }

    public function postSaveDetail()
    {
      if($_POST['kat'] == 'tindakan') {
        $jns_perawatan = $this->db('jns_perawatan')->where('kd_jenis_prw', $_POST['kd_jenis_prw'])->oneArray();
        $this->db('rawat_jl_dr')->save([
          'no_rawat' => $_POST['no_rawat'],
          'kd_jenis_prw' => $_POST['kd_jenis_prw'],
          'kd_dokter' => $this->core->getUserInfo('username', null, true),
          'tgl_perawatan' => $_POST['tgl_perawatan'],
          'jam_rawat' => $_POST['jam_rawat'],
          'material' => $jns_perawatan['material'],
          'bhp' => $jns_perawatan['bhp'],
          'tarif_tindakandr' => $jns_perawatan['tarif_tindakandr'],
          'kso' => $jns_perawatan['kso'],
          'menejemen' => $jns_perawatan['menejemen'],
          'biaya_rawat' => $jns_perawatan['total_byrdr'],
          'stts_bayar' => 'Belum'
        ]);
      }
      if($_POST['kat'] == 'obat') {

        $cek_resep = $this->db('resep_obat')->where('no_rawat', $_POST['no_rawat'])->where('tgl_perawatan', date('Y-m-d'))->oneArray();
        if(!$cek_resep) {
          $max_id = $this->db('resep_obat')->select(['no_resep' => 'ifnull(MAX(CONVERT(RIGHT(no_resep,4),signed)),0)'])->where('tgl_perawatan', date('Y-m-d'))->oneArray();
          if(empty($max_id['no_resep'])) {
            $max_id['no_resep'] = '0000';
          }
          $_next_no_resep = sprintf('%04s', ($max_id['no_resep'] + 1));
          $no_resep = date('Ymd').''.$_next_no_resep;

          $resep_obat = $this->db('resep_obat')
            ->save([
              'no_resep' => $no_resep,
              'tgl_perawatan' => $_POST['tgl_perawatan'],
              'jam' => $_POST['jam_rawat'],
              'no_rawat' => $_POST['no_rawat'],
              'kd_dokter' => $this->core->getUserInfo('username', null, true),
              'tgl_peresepan' => $_POST['tgl_perawatan'],
              'jam_peresepan' => $_POST['jam_rawat'],
              'status' => 'ralan'
            ]);
          $this->db('resep_dokter')
            ->save([
              'no_resep' => $no_resep,
              'kode_brng' => $_POST['kd_jenis_prw'],
              'jml' => $_POST['jml'],
              'aturan_pakai' => $_POST['aturan_pakai']
            ]);

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

      if($_POST['kat'] == 'racikan') {
        $max_id = $this->db('resep_obat')->select(['no_resep' => 'ifnull(MAX(CONVERT(RIGHT(no_resep,4),signed)),0)'])->where('tgl_perawatan', date('Y-m-d'))->oneArray();
        if(empty($max_id['no_resep'])) {
          $max_id['no_resep'] = '0000';
        }
        $_next_no_resep = sprintf('%04s', ($max_id['no_resep'] + 1));
        $no_resep = date('Ymd').''.$_next_no_resep;

        $_POST['jam_rawat'] = date('H:i:s');

        $resep_obat = $this->db('resep_obat')
          ->save([
            'no_resep' => $no_resep,
            'tgl_perawatan' => $_POST['tgl_perawatan'],
            'jam' => $_POST['jam_rawat'],
            'no_rawat' => $_POST['no_rawat'],
            'kd_dokter' => $this->core->getUserInfo('username', null, true),
            'tgl_peresepan' => $_POST['tgl_perawatan'],
            'jam_peresepan' => $_POST['jam_rawat'],
            'status' => 'ralan'
          ]);

        if ($resep_obat) {
          $no_racik = $this->db('resep_dokter_racikan')->where('no_resep', $no_resep)->count();
          $no_racik = $no_racik+1;
          $this->db('resep_dokter_racikan')
            ->save([
              'no_resep' => $no_resep,
              'no_racik' => $no_racik,
              'nama_racik' => $_POST['nama_racik'],
              'kd_racik' => $_POST['kd_jenis_prw'],
              'jml_dr' => $_POST['jml'],
              'aturan_pakai' => $_POST['aturan_pakai'],
              'keterangan' => $_POST['keterangan']
            ]);
          $_POST['kode_brng'] = json_decode($_POST['kode_brng'], true);
          $_POST['kandungan'] = json_decode($_POST['kandungan'], true);
          for ($i = 0; $i < count($_POST['kode_brng']); $i++) {
            $kapasitas = $this->db('databarang')->where('kode_brng', $_POST['kode_brng'][$i]['value'])->oneArray();
            $jml = $_POST['jml']*$_POST['kandungan'][$i]['value'];
            $jml = round(($jml/$kapasitas['kapasitas']),1);
            $this->db('resep_dokter_racikan_detail')
              ->save([
                'no_resep' => $no_resep,
                'no_racik' => $no_racik,
                'kode_brng' => $_POST['kode_brng'][$i]['value'],
                'p1' => '1',
                'p2' => '1',
                'kandungan' => $_POST['kandungan'][$i]['value'],
                'jml' => $jml
              ]);
          }
        }
      }

      if($_POST['kat'] == 'laboratorium') {
        $cek_lab = $this->db('permintaan_lab')->where('no_rawat', $_POST['no_rawat'])->where('tgl_permintaan', date('Y-m-d'))->oneArray();
        if(!$cek_lab) {
          $max_id = $this->db('permintaan_lab')->select(['noorder' => 'ifnull(MAX(CONVERT(RIGHT(noorder,4),signed)),0)'])->where('tgl_permintaan', date('Y-m-d'))->oneArray();
          if(empty($max_id['noorder'])) {
            $max_id['noorder'] = '0000';
          }
          $_next_noorder = sprintf('%04s', ($max_id['noorder'] + 1));
          $noorder = 'PL'.date('Ymd').''.$_next_noorder;

          $permintaan_lab = $this->db('permintaan_lab')
            ->save([
              'noorder' => $noorder,
              'no_rawat' => $_POST['no_rawat'],
              'tgl_permintaan' => $_POST['tgl_perawatan'],
              'jam_permintaan' => $_POST['jam_rawat'],
              'tgl_sampel' => '0000-00-00',
              'jam_sampel' => '00:00:00',
              'tgl_hasil' => '0000-00-00',
              'jam_hasil' => '00:00:00',
              'dokter_perujuk' => $this->core->getUserInfo('username', null, true),
              'status' => 'ralan',
              'informasi_tambahan' => $_POST['informasi_tambahan'],
              'diagnosa_klinis' => $_POST['diagnosa_klinis']
            ]);
          $this->db('permintaan_pemeriksaan_lab')
            ->save([
              'noorder' => $noorder,
              'kd_jenis_prw' => $_POST['kd_jenis_prw'],
              'stts_bayar' => 'Belum'
            ]);

        } else {
          $noorder = $cek_lab['noorder'];
          $this->db('permintaan_pemeriksaan_lab')
            ->save([
              'noorder' => $noorder,
              'kd_jenis_prw' => $_POST['kd_jenis_prw'],
              'stts_bayar' => 'Belum'
            ]);
        }
      }

      if($_POST['kat'] == 'radiologi') {
        $cek_rad = $this->db('permintaan_radiologi')->where('no_rawat', $_POST['no_rawat'])->where('tgl_permintaan', date('Y-m-d'))->oneArray();
        if(!$cek_rad) {
          $max_id = $this->db('permintaan_radiologi')->select(['noorder' => 'ifnull(MAX(CONVERT(RIGHT(noorder,4),signed)),0)'])->where('tgl_permintaan', date('Y-m-d'))->oneArray();
          if(empty($max_id['noorder'])) {
            $max_id['noorder'] = '0000';
          }
          $_next_noorder = sprintf('%04s', ($max_id['noorder'] + 1));
          $noorder = 'PR'.date('Ymd').''.$_next_noorder;

          $permintaan_rad = $this->db('permintaan_radiologi')
            ->save([
              'noorder' => $noorder,
              'no_rawat' => $_POST['no_rawat'],
              'tgl_permintaan' => $_POST['tgl_perawatan'],
              'jam_permintaan' => $_POST['jam_rawat'],
              'tgl_sampel' => '0000-00-00',
              'jam_sampel' => '00:00:00',
              'tgl_hasil' => '0000-00-00',
              'jam_hasil' => '00:00:00',
              'dokter_perujuk' => $this->core->getUserInfo('username', null, true),
              'status' => 'ralan',
              'informasi_tambahan' => $_POST['informasi_tambahan'],
              'diagnosa_klinis' => $_POST['diagnosa_klinis']
            ]);
          $this->db('permintaan_pemeriksaan_radiologi')
            ->save([
              'noorder' => $noorder,
              'kd_jenis_prw' => $_POST['kd_jenis_prw'],
              'stts_bayar' => 'Belum'
            ]);

        } else {
          $noorder = $cek_rad['noorder'];
          $this->db('permintaan_pemeriksaan_radiologi')
            ->save([
              'noorder' => $noorder,
              'kd_jenis_prw' => $_POST['kd_jenis_prw'],
              'stts_bayar' => 'Belum'
            ]);
        }
      }

      exit();
    }

    public function postHapusDetail()
    {
      if($_POST['provider'] == 'rawat_jl_dr') {
        $this->db('rawat_jl_dr')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('kd_jenis_prw', $_POST['kd_jenis_prw'])
        ->where('tgl_perawatan', $_POST['tgl_perawatan'])
        ->where('jam_rawat', $_POST['jam_rawat'])
        ->delete();
      }
      if($_POST['provider'] == 'rawat_jl_pr') {
        $this->db('rawat_jl_pr')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('kd_jenis_prw', $_POST['kd_jenis_prw'])
        ->where('tgl_perawatan', $_POST['tgl_perawatan'])
        ->where('jam_rawat', $_POST['jam_rawat'])
        ->delete();
      }
      if($_POST['provider'] == 'rawat_jl_drpr') {
        $this->db('rawat_jl_drpr')
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

    public function anyCopyResep()
    {
      $return = $this->db('resep_dokter')
        ->join('databarang', 'databarang.kode_brng=resep_dokter.kode_brng')
        ->join('gudangbarang', 'gudangbarang.kode_brng=resep_dokter.kode_brng')
        ->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))
        ->where('no_resep', $_POST['no_resep'])
        ->toArray();
      echo $this->draw('copyresep.display.html', ['copy_resep' => $return]);
      exit();
    }

    public function postSaveCopyResep()
    {
      $_POST['kode_brng'] = json_decode($_POST['kode_brng'], true);
      $_POST['jml'] = json_decode($_POST['jml'], true);
      $_POST['aturan_pakai'] = json_decode($_POST['aturan_pakai'], true);

      $max_id = $this->db('resep_obat')->select(['no_resep' => 'ifnull(MAX(CONVERT(RIGHT(no_resep,4),signed)),0)'])->where('tgl_perawatan', date('Y-m-d'))->oneArray();
      if(empty($max_id['no_resep'])) {
        $max_id['no_resep'] = '0000';
      }
      $_next_no_resep = sprintf('%04s', ($max_id['no_resep'] + 1));
      $no_resep = date('Ymd').''.$_next_no_resep;

      $resep_obat = $this->db('resep_obat')
        ->save([
          'no_resep' => $no_resep,
          'tgl_perawatan' => $_POST['tgl_perawatan'],
          'jam' => $_POST['jam_rawat'],
          'no_rawat' => $_POST['no_rawat'],
          'kd_dokter' => $this->core->getUserInfo('username', null, true),
          'tgl_peresepan' => $_POST['tgl_perawatan'],
          'jam_peresepan' => $_POST['jam_rawat'],
          'status' => 'ralan'
        ]);

      for ($i = 0; $i < count($_POST['kode_brng']); $i++) {
        /*$cek_stok = $this->db('gudangbarang')
          ->join('databarang', 'databarang.kode_brng=gudangbarang.kode_brng')
          ->where('gudangbarang.kode_brng', $_POST['kode_brng'][$i]['value'])
          ->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))
          ->oneArray();*/

        //if($cek_stok['stok'] < $cek_stok['stokminimal']) {
        //  echo "Error";
        //} else {
          $this->db('resep_dokter')
            ->save([
              'no_resep' => $no_resep,
              'kode_brng' => $_POST['kode_brng'][$i]['value'],
              'jml' => $_POST['jml'][$i]['value'],
              'aturan_pakai' => $_POST['aturan_pakai'][$i]['value']
            ]);
        //}

      }

      exit();
    }

    public function anyRincian()
    {
      $rows_rawat_jl_dr = $this->db('rawat_jl_dr')->where('no_rawat', $_POST['no_rawat'])->toArray();
      $rows_rawat_jl_pr = $this->db('rawat_jl_pr')->where('no_rawat', $_POST['no_rawat'])->toArray();
      $rows_rawat_jl_drpr = $this->db('rawat_jl_drpr')->where('no_rawat', $_POST['no_rawat'])->toArray();

      $jumlah_total = 0;
      $rawat_jl_dr = [];
      $rawat_jl_pr = [];
      $rawat_jl_drpr = [];
      $i = 1;

      if($rows_rawat_jl_dr) {
        foreach ($rows_rawat_jl_dr as $row) {
          $jns_perawatan = $this->db('jns_perawatan')->where('kd_jenis_prw', $row['kd_jenis_prw'])->oneArray();
          $row['nm_perawatan'] = $jns_perawatan['nm_perawatan'];
          $jumlah_total = $jumlah_total + $row['biaya_rawat'];
          $row['provider'] = 'rawat_jl_dr';
          $rawat_jl_dr[] = $row;
        }
      }

      if($rows_rawat_jl_pr) {
        foreach ($rows_rawat_jl_pr as $row) {
          $jns_perawatan = $this->db('jns_perawatan')->where('kd_jenis_prw', $row['kd_jenis_prw'])->oneArray();
          $row['nm_perawatan'] = $jns_perawatan['nm_perawatan'];
          $jumlah_total = $jumlah_total + $row['biaya_rawat'];
          $row['provider'] = 'rawat_jl_pr';
          $rawat_jl_pr[] = $row;
        }
      }

      if($rows_rawat_jl_drpr) {
        foreach ($rows_rawat_jl_drpr as $row) {
          $jns_perawatan = $this->db('jns_perawatan')->where('kd_jenis_prw', $row['kd_jenis_prw'])->oneArray();
          $row['nm_perawatan'] = $jns_perawatan['nm_perawatan'];
          $jumlah_total = $jumlah_total + $row['biaya_rawat'];
          $row['provider'] = 'rawat_jl_drpr';
          $rawat_jl_drpr[] = $row;
        }
      }

      $rows = $this->db('resep_obat')
        ->join('dokter', 'dokter.kd_dokter=resep_obat.kd_dokter')
        ->join('resep_dokter', 'resep_dokter.no_resep=resep_obat.no_resep')
        ->where('no_rawat', $_POST['no_rawat'])
        ->group('resep_dokter.no_resep')
        ->toArray();
      $resep = [];
      $jumlah_total_resep = 0;
      foreach ($rows as $row) {
        $row['nomor'] = $i++;
        $row['resep_dokter'] = $this->db('resep_dokter')->join('databarang', 'databarang.kode_brng=resep_dokter.kode_brng')->where('no_resep', $row['no_resep'])->toArray();
        foreach ($row['resep_dokter'] as $value) {
          $value['ralan'] = $value['jml'] * $value['ralan'];
          $jumlah_total_resep += floatval($value['ralan']);
        }
        $resep[] = $row;
      }

      $rows_racikan = $this->db('resep_obat')
        ->join('dokter', 'dokter.kd_dokter=resep_obat.kd_dokter')
        ->join('resep_dokter_racikan', 'resep_dokter_racikan.no_resep=resep_obat.no_resep')
        ->where('no_rawat', $_POST['no_rawat'])
        ->group('resep_dokter_racikan.no_resep')
        ->toArray();
      $resep_racikan = [];
      $jumlah_total_resep_racikan = 0;
      foreach ($rows_racikan as $row) {
        $row['nomor'] = $i++;
        $row['resep_dokter_racikan_detail'] = $this->db('resep_dokter_racikan_detail')->join('databarang', 'databarang.kode_brng=resep_dokter_racikan_detail.kode_brng')->where('no_resep', $row['no_resep'])->toArray();
        foreach ($row['resep_dokter_racikan_detail'] as $value) {
          $value['ralan'] = $value['jml'] * $value['ralan'];
          $jumlah_total_resep_racikan += floatval($value['ralan']);
        }
        $resep_racikan[] = $row;
      }

      $rows_laboratorium = $this->db('permintaan_lab')->join('permintaan_pemeriksaan_lab', 'permintaan_pemeriksaan_lab.noorder=permintaan_lab.noorder')->where('no_rawat', $_POST['no_rawat'])->toArray();
      $jumlah_total_lab = 0;
      $laboratorium = [];

      if($rows_laboratorium) {
        foreach ($rows_laboratorium as $row) {
          $jns_perawatan = $this->db('jns_perawatan_lab')->where('kd_jenis_prw', $row['kd_jenis_prw'])->oneArray();
          $row['nm_perawatan'] = $jns_perawatan['nm_perawatan'];
          $row['kelas'] = $jns_perawatan['kelas'];
          $row['total_byr'] = $jns_perawatan['total_byr'];
          $jumlah_total_lab += $jns_perawatan['total_byr'];
          $laboratorium[] = $row;
        }
      }

      $rows_radiologi = $this->db('permintaan_radiologi')->join('permintaan_pemeriksaan_radiologi', 'permintaan_pemeriksaan_radiologi.noorder=permintaan_radiologi.noorder')->where('no_rawat', $_POST['no_rawat'])->toArray();
      $jumlah_total_rad = 0;
      $radiologi = [];

      if($rows_radiologi) {
        foreach ($rows_radiologi as $row) {
          $jns_perawatan = $this->db('jns_perawatan_radiologi')->where('kd_jenis_prw', $row['kd_jenis_prw'])->oneArray();
          $row['nm_perawatan'] = $jns_perawatan['nm_perawatan'];
          $row['kelas'] = $jns_perawatan['kelas'];
          $row['total_byr'] = $jns_perawatan['total_byr'];
          $jumlah_total_rad += $jns_perawatan['total_byr'];
          $radiologi[] = $row;
        }
      }

      $reg_periksa = $this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->oneArray();
      $rows_data_resep = $this->db('resep_obat')
      ->join('reg_periksa', 'reg_periksa.no_rawat=resep_obat.no_rawat')
      ->where('resep_obat.kd_dokter', $this->core->getUserInfo('username', null, true))
      ->where('reg_periksa.no_rkm_medis', $reg_periksa['no_rkm_medis'])
      ->toArray();

      $data_resep = [];
      foreach ($rows_data_resep as $row) {
        $row['resep_dokter'] = $this->db('resep_dokter')
          ->join('databarang', 'databarang.kode_brng=resep_dokter.kode_brng')
          ->where('no_resep', $row['no_resep'])
          ->toArray();
        $data_resep[] = $row;
      }

      echo $this->draw('rincian.html', [
        'rawat_jl_dr' => $rawat_jl_dr,
        'rawat_jl_pr' => $rawat_jl_pr,
        'rawat_jl_drpr' => $rawat_jl_drpr,
        'resep' => $resep,
        'resep_racikan' => $resep_racikan,
        'data_resep' => $data_resep,
        'laboratorium' => $laboratorium,
        'radiologi' => $radiologi,
        'jumlah_total' => $jumlah_total,
        'jumlah_total_resep' => $jumlah_total_resep,
        'jumlah_total_resep_racikan' => $jumlah_total_resep_racikan,
        'jumlah_total_lab' => $jumlah_total_lab,
        'jumlah_total_rad' => $jumlah_total_rad,
        'no_rawat' => $_POST['no_rawat']
      ]);
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

      $result_ranap = [];
      if($this->db('mlite_settings')->where('module', 'rawat_inap')->oneArray()) {
        $rows_ranap = $this->db('pemeriksaan_ranap')
         ->where('no_rawat', $_POST['no_rawat'])
         ->toArray();
        foreach ($rows_ranap as $row) {
         $row['nomor'] = $i++;
         $row['nama_petugas'] = $this->core->getPegawaiInfo('nama',$row['nip']);
         $row['departemen_petugas'] = $this->core->getDepartemenInfo($this->core->getPegawaiInfo('departemen',$row['nip']));
         $result_ranap[] = $row;
        }
      }

      echo $this->draw('soap.html', ['pemeriksaan' => $result, 'pemeriksaan_ranap' => $result_ranap, 'diagnosa' => $diagnosa, 'prosedur' => $prosedur, 'admin_mode' => $this->settings->get('settings.admin_mode')]);
      exit();
    }

    public function postSaveSOAP()
    {
      $check_db = $this->db()->pdo()->query("SHOW COLUMNS FROM `pemeriksaan_ralan` LIKE 'instruksi'");
      $check_db->execute();
      $check_db = $check_db->fetch();

      if($check_db) {
        $_POST['nip'] = $this->core->getUserInfo('username', null, true);
      } else {
        unset($_POST['instruksi']);
      }
      if(!$this->db('pemeriksaan_ralan')->where('no_rawat', $_POST['no_rawat'])->where('tgl_perawatan', $_POST['tgl_perawatan'])->where('jam_rawat', $_POST['jam_rawat'])->oneArray()) {
        $this->db('pemeriksaan_ralan')->save($_POST);
      } else {
        $this->db('pemeriksaan_ralan')->where('no_rawat', $_POST['no_rawat'])->where('tgl_perawatan', $_POST['tgl_perawatan'])->where('jam_rawat', $_POST['jam_rawat'])->save($_POST);
      }
      exit();
    }

    public function postHapusSOAP()
    {
      $this->db('pemeriksaan_ralan')->where('no_rawat', $_POST['no_rawat'])->where('tgl_perawatan', $_POST['tgl_perawatan'])->where('jam_rawat', $_POST['jam_rawat'])->delete();
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
        'kd_dokter' => $this->core->getUserInfo('username', null, true),
        'status' => 'Menunggu'
      ]);

      if ($query) {
        $this->db('booking_registrasi')
          ->save([
            'tanggal_booking' => date('Y-m-d'),
            'jam_booking' => date('H:i:s'),
            'no_rkm_medis' => $_POST['no_rkm_medis'],
            'tanggal_periksa' => $_POST['tanggal_datang'],
            'kd_dokter' => $this->core->getUserInfo('username', null, true),
            'kd_poli' => $this->core->getRegPeriksaInfo('kd_poli', $_POST['no_rawat']),
            'no_reg' => $this->core->setNoBooking($this->core->getUserInfo('username', null, true), $_POST['tanggal_rujukan']),
            'kd_pj' => $this->core->getRegPeriksaInfo('kd_pj', $_POST['no_rawat']),
            'limit_reg' => 0,
            'waktu_kunjungan' => $_POST['tanggal_datang'].' '.date('H:i:s'),
            'status' => 'Belum'
          ]);
      }

      exit();
    }

    public function postHapusKontrol()
    {
      $this->db('pemeriksaan_ralan')->where('no_rawat', $_POST['no_rawat'])->delete();
      exit();
    }

    public function anyLayanan()
    {
      $layanan = $this->db('jns_perawatan')
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
        ->where('gudangbarang.kd_bangsal', $this->settings->get('farmasi.deporalan'))
        ->like('databarang.nama_brng', '%'.$_POST['obat'].'%')
        ->limit(10)
        ->toArray();
      echo $this->draw('obat.html', ['obat' => $obat]);
      exit();
    }

    public function anyObatRacikan()
    {
      $obat = $this->db('databarang')
        ->join('gudangbarang', 'gudangbarang.kode_brng=databarang.kode_brng')
        ->where('status', '1')
        ->where('gudangbarang.kd_bangsal', $this->settings->get('farmasi.deporalan'))
        ->like('databarang.nama_brng', '%'.$_POST['obat'].'%')
        ->limit(10)
        ->toArray();
      echo $this->draw('obat.racikan.html', ['obat' => $obat]);
      exit();
    }

    public function anyRacikan()
    {
      $racikan = $this->db('metode_racik')
        ->like('nm_racik', '%'.$_POST['racikan'].'%')
        ->toArray();
      echo $this->draw('racikan.html', ['racikan' => $racikan]);
      exit();
    }

    public function anyLaboratorium()
    {
      $laboratorium = $this->db('jns_perawatan_lab')
        ->where('status', '1')
        ->like('nm_perawatan', '%'.$_POST['laboratorium'].'%')
        ->limit(10)
        ->toArray();
      echo $this->draw('laboratorium.html', ['laboratorium' => $laboratorium]);
      exit();
    }

    public function anyRadiologi()
    {
      $radiologi = $this->db('jns_perawatan_radiologi')
        ->where('status', '1')
        ->like('nm_perawatan', '%'.$_POST['radiologi'].'%')
        ->limit(10)
        ->toArray();
      echo $this->draw('radiologi.html', ['radiologi' => $radiologi]);
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

    public function getView($id, $page = 1)
    {
        $id = revertNorawat($id);
        $this->_addHeaderFiles();
        $reg_periksa = $this->db('reg_periksa')->where('no_rawat', $id)->oneArray();
        $pasien = $this->db('pasien')->where('no_rkm_medis', $reg_periksa['no_rkm_medis'])->oneArray();
        $personal_pasien = $this->db('personal_pasien')->where('no_rkm_medis', $reg_periksa['no_rkm_medis'])->oneArray();
        $count_ralan = $this->db('reg_periksa')->where('no_rkm_medis', $reg_periksa['no_rkm_medis'])->where('status_lanjut', 'Ralan')->count();
        $count_ranap = $this->db('reg_periksa')->where('no_rkm_medis', $reg_periksa['no_rkm_medis'])->where('status_lanjut', 'Ranap')->count();
        $this->assign['print_rm'] = url([ADMIN, 'dokter_ralan', 'print_rm', $reg_periksa['no_rkm_medis']]);

        if (!empty($reg_periksa)) {
	        $perpage = '5';
            $this->assign['no_rawat'] = convertNorawat($id);
            $this->assign['pemeriksaan_ralan']['kesadaran'] = $this->core->getEnum('pemeriksaan_ralan','kesadaran');
            $this->assign['pemeriksaan_ralan']['imun_ke'] = $this->core->getEnum('pemeriksaan_ralan','imun_ke');
            $this->assign['view'] = $reg_periksa;
            $this->assign['view']['pasien'] = $pasien;
            $this->assign['view']['count_ralan'] = $count_ralan;
            $this->assign['view']['count_ranap'] = $count_ranap;
            $this->assign['soap'] = $this->db('pemeriksaan_ralan')->where('no_rawat', $id)->oneArray();
            $this->assign['metode_racik'] = $this->core->db('metode_racik')->toArray();
            $this->assign['diagnosa_pasien'] = $this->db('diagnosa_pasien')->join('penyakit', 'penyakit.kd_penyakit = diagnosa_pasien.kd_penyakit')->where('no_rawat', $id)->toArray();
            $this->assign['prosedur_pasien'] = $this->db('prosedur_pasien')->join('icd9', 'icd9.kode = prosedur_pasien.kode')->where('no_rawat', $id)->toArray();
            $this->assign['rawat_jl_dr'] = $this->db('rawat_jl_dr')->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_jl_dr.kd_jenis_prw')->where('no_rawat', $id)->toArray();
            $this->assign['rawat_jl_pr'] = $this->db('rawat_jl_pr')->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_jl_pr.kd_jenis_prw')->where('no_rawat', $id)->toArray();
            $this->assign['rawat_jl_drpr'] = $this->db('rawat_jl_drpr')->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_jl_drpr.kd_jenis_prw')->where('no_rawat', $id)->toArray();
            $this->assign['rawat_inap_dr'] = [];
            $this->assign['rawat_inap_pr'] = [];
            $this->assign['rawat_inap_drpr'] = [];
            if($this->db('mlite_settings')->where('module', 'rawat_inap')->oneArray()) {
              $this->assign['rawat_inap_dr'] = $this->db('rawat_inap_dr')->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_inap_dr.kd_jenis_prw')->where('no_rawat', $id)->toArray();
              $this->assign['rawat_inap_pr'] = $this->db('rawat_inap_pr')->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_inap_pr.kd_jenis_prw')->where('no_rawat', $id)->toArray();
              $this->assign['rawat_inap_drpr'] = $this->db('rawat_inap_drpr')->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_inap_drpr.kd_jenis_prw')->where('no_rawat', $id)->toArray();
            }
            $this->assign['catatan'] = $this->db('catatan_perawatan')->where('no_rawat', $id)->oneArray();
            $rows_permintaan_resep = $this->db('resep_obat')
                ->join('resep_dokter', 'resep_dokter.no_resep = resep_obat.no_resep')
                ->join('databarang', 'databarang.kode_brng = resep_dokter.kode_brng')
                ->where('no_rawat', $id)
                ->toArray();

            $this->assign['permintaan_resep'] = [];
            if (count($rows_permintaan_resep)) {
                foreach ($rows_permintaan_resep as $row) {
                    $row = htmlspecialchars_array($row);
                    $row['delURL'] = url([ADMIN, 'dokter_ralan', 'delpermintaanresep', convertNorawat($id), $row['no_resep'], $row['kode_brng']]);
                    $this->assign['permintaan_resep'][] = $row;
                }
            }

            $this->assign['permintaan_resep_racikan'] = $this->db('resep_obat')
                ->join('resep_dokter_racikan', 'resep_dokter_racikan.no_resep = resep_obat.no_resep')
                ->join('metode_racik', 'metode_racik.kd_racik = resep_dokter_racikan.kd_racik')
                ->join('resep_dokter_racikan_detail', 'resep_dokter_racikan_detail.no_resep = resep_dokter_racikan.no_resep')
                ->join('databarang', 'databarang.kode_brng = resep_dokter_racikan_detail.kode_brng')
                ->where('resep_obat.no_rawat', $id)
                ->group('resep_dokter_racikan.no_resep')
                ->select('resep_obat.no_resep')
                ->select('resep_dokter_racikan.nama_racik')
                ->select('metode_racik.nm_racik')
                ->select('resep_dokter_racikan.jml_dr')
                ->select('resep_dokter_racikan.aturan_pakai')
                ->select('resep_dokter_racikan.keterangan')
                ->select('group_concat(distinct concat(databarang.nama_brng, \'<br> - Kandungan: \', resep_dokter_racikan_detail.kandungan, \'<br> - Jumlah: \', resep_dokter_racikan_detail.jml) separator \'<br><br>\') AS detail_racikan')
                ->toArray();
            $this->assign['permintaan_lab'] = $this->db('permintaan_lab')
                ->join('permintaan_pemeriksaan_lab', 'permintaan_pemeriksaan_lab.noorder = permintaan_lab.noorder')
                ->join('jns_perawatan_lab', 'jns_perawatan_lab.kd_jenis_prw = permintaan_pemeriksaan_lab.kd_jenis_prw')
                ->where('no_rawat', $id)
                ->toArray();
            $this->assign['permintaan_rad'] = $this->db('permintaan_radiologi')
                ->join('permintaan_pemeriksaan_radiologi', 'permintaan_pemeriksaan_radiologi.noorder = permintaan_radiologi.noorder')
                ->join('jns_perawatan_radiologi', 'jns_perawatan_radiologi.kd_jenis_prw = permintaan_pemeriksaan_radiologi.kd_jenis_prw')
                ->where('no_rawat', $id)
                ->toArray();
            $this->assign['fotoURL'] = url(MODULES.'/dokter_ralan/img/'.$pasien['jk'].'.png');
            if(!empty($personal_pasien['gambar'])) {
              $this->assign['fotoURL'] = WEBAPPS_URL.'/photopasien/'.$personal_pasien['gambar'];
            }
            $this->assign['master_berkas_digital'] = $this->db('master_berkas_digital')->toArray();
            $this->assign['berkas_digital'] = $this->db('berkas_digital_perawatan')->where('no_rawat', $id)->toArray();

            $this->assign['manageURL'] = url([ADMIN, 'dokter_ralan', 'manage']);
            $totalRecords = $this->db('reg_periksa')
                ->where('no_rkm_medis', $reg_periksa['no_rkm_medis'])
                ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
                ->join('dokter', 'dokter.kd_dokter = reg_periksa.kd_dokter')
                ->desc('tgl_registrasi')
                ->toArray();
  	        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'dokter_ralan', 'view', convertNorawat($id), '%d']));
  	        $this->assign['pagination'] = $pagination->nav('pagination','5');
  	        $offset = $pagination->offset();
            $rows = $this->db('reg_periksa')
                ->where('no_rkm_medis', $reg_periksa['no_rkm_medis'])
                ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
                ->join('dokter', 'dokter.kd_dokter = reg_periksa.kd_dokter')
                ->offset($offset)
                ->limit($perpage)
                ->desc('tgl_registrasi')
                ->toArray();

            foreach ($rows as &$row) {
                $pemeriksaan_ralan = $this->db('pemeriksaan_ralan')->where('no_rawat', $row['no_rawat'])->oneArray();
                if($row['status_lanjut'] == 'Ranap') {
                  $pemeriksaan_ralan = $this->db('pemeriksaan_ranap')->where('no_rawat', $row['no_rawat'])->oneArray();
                }
                $diagnosa_pasien = $this->db('diagnosa_pasien')->join('penyakit', 'penyakit.kd_penyakit = diagnosa_pasien.kd_penyakit')->where('no_rawat', $row['no_rawat'])->toArray();
                $prosedur_pasien = $this->db('prosedur_pasien')->join('icd9', 'icd9.kode = prosedur_pasien.kode')->where('no_rawat', $row['no_rawat'])->toArray();
                $rawat_jl_dr = $this->db('rawat_jl_dr')->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_jl_dr.kd_jenis_prw')->where('no_rawat', $row['no_rawat'])->toArray();
                $rawat_jl_pr = $this->db('rawat_jl_pr')->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_jl_pr.kd_jenis_prw')->where('no_rawat', $row['no_rawat'])->toArray();
                $rawat_jl_drpr = $this->db('rawat_jl_drpr')->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_jl_drpr.kd_jenis_prw')->where('no_rawat', $row['no_rawat'])->toArray();
                $rawat_inap_dr = [];
                $rawat_inap_pr = [];
                $rawat_inap_drpr = [];
                if($this->db('mlite_settings')->where('module', 'rawat_inap')->oneArray()) {
                  $rawat_inap_dr = $this->db('rawat_inap_dr')->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_inap_dr.kd_jenis_prw')->where('no_rawat', $row['no_rawat'])->toArray();
                  $rawat_inap_pr = $this->db('rawat_inap_pr')->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_inap_pr.kd_jenis_prw')->where('no_rawat', $row['no_rawat'])->toArray();
                  $rawat_inap_drpr = $this->db('rawat_inap_drpr')->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_inap_drpr.kd_jenis_prw')->where('no_rawat', $row['no_rawat'])->toArray();
                }
                $detail_pemberian_obat = $this->db('aturan_pakai')
                  ->join('databarang', 'databarang.kode_brng = aturan_pakai.kode_brng')
                  ->join('detail_pemberian_obat', 'detail_pemberian_obat.no_rawat = aturan_pakai.no_rawat')
                  //->join('resep_dokter', 'resep_dokter.no_resep = resep_obat.no_resep')
                  ->where('aturan_pakai.no_rawat', $row['no_rawat'])
                  //->where('resep_dokter.kode_brng', 'detail_pemberian_obat.kode_brng')
                  ->group('aturan_pakai.kode_brng')
                  //->select('databarang.nama_brng')
                  //->select('detail_pemberian_obat.jml')
                  //->select('resep_dokter.aturan_pakai')
                  ->toArray();
                $detail_periksa_lab = $this->db('detail_periksa_lab')->join('template_laboratorium', 'template_laboratorium.id_template = detail_periksa_lab.id_template')->where('no_rawat', $row['no_rawat'])->toArray();
                $hasil_radiologi = $this->db('hasil_radiologi')->where('no_rawat', $row['no_rawat'])->oneArray();
                $gambar_radiologi = $this->db('gambar_radiologi')->where('no_rawat', $row['no_rawat'])->toArray();
                $catatan_perawatan = $this->db('catatan_perawatan')->where('no_rawat', $row['no_rawat'])->oneArray();
                $berkas_digital = $this->db('berkas_digital_perawatan')->where('no_rawat', $row['no_rawat'])->toArray();
                $row['keluhan'] = $pemeriksaan_ralan['keluhan'];
                $row['suhu_tubuh'] = $pemeriksaan_ralan['suhu_tubuh'];
                $row['tensi'] = $pemeriksaan_ralan['tensi'];
                $row['nadi'] = $pemeriksaan_ralan['nadi'];
                $row['respirasi'] = $pemeriksaan_ralan['respirasi'];
                $row['tinggi'] = $pemeriksaan_ralan['tinggi'];
                $row['berat'] = $pemeriksaan_ralan['berat'];
                $row['gcs'] = $pemeriksaan_ralan['gcs'];
                $row['pemeriksaan'] = $pemeriksaan_ralan['pemeriksaan'];
                $row['rtl'] = $pemeriksaan_ralan['rtl'];
                $row['catatan_perawatan'] = $catatan_perawatan['catatan'];
                $row['diagnosa_pasien'] = $diagnosa_pasien;
                $row['prosedur_pasien'] = $prosedur_pasien;
                $row['rawat_jl_dr'] = $rawat_jl_dr;
                $row['rawat_jl_pr'] = $rawat_jl_pr;
                $row['rawat_jl_drpr'] = $rawat_jl_drpr;
                $row['rawat_inap_dr'] = $rawat_inap_dr;
                $row['rawat_inap_pr'] = $rawat_inap_pr;
                $row['rawat_inap_drpr'] = $rawat_inap_drpr;
                $row['detail_pemberian_obat'] = $detail_pemberian_obat;
                $row['detail_periksa_lab'] = $detail_periksa_lab;
                $row['hasil_radiologi'] = str_replace("\n","<br>",$hasil_radiologi['hasil']);
                $row['gambar_radiologi'] = $gambar_radiologi;
                $row['berkas_digital'] = $berkas_digital;
                $this->assign['riwayat'][] = $row;
            }

            return $this->draw('view.html', ['dokter_ralan' => $this->assign, 'admin_mode' => $this->settings->get('settings.admin_mode')]);
        } else {
            redirect(url([ADMIN, 'dokter_ralan', 'manage']));
        }
    }

    public function postSOAPSave($id = null)
    {
        $errors = 0;
        $location = url([ADMIN, 'dokter_ralan', 'view', $id]);

        if (!$errors) {
            unset($_POST['save']);

            $check_db = $this->db()->pdo()->query("SHOW COLUMNS FROM `pemeriksaan_ralan` LIKE 'instruksi'");
            $check_db->execute();
            $check_db = $check_db->fetch();

            if($check_db) {
              $_POST['instruksi'] = '';
              $_POST['nip'] = $this->core->getUserInfo('username', null, true);
            }
            $cek_no_rawat = $this->db('pemeriksaan_ralan')->where('no_rawat', revertNorawat($id))->oneArray();
            if(empty($cek_no_rawat['no_rawat'])) {
              $check_db = $this->db()->pdo()->query("SHOW COLUMNS FROM `pemeriksaan_ralan` LIKE 'instruksi'");
              $check_db->execute();
              $check_db = $check_db->fetch();

              if($check_db) {
                $_POST['instruksi'] = '';
                $_POST['nip'] = $this->core->getUserInfo('username', null, true);
                $query = $this->db('pemeriksaan_ralan')
                  ->save([
                    'no_rawat' => revertNorawat($id),
                    'tgl_perawatan' => date('Y-m-d'),
                    'jam_rawat' => date('H:i:s'),
                    'suhu_tubuh' => $_POST['suhu_tubuh'],
                    'tensi' => $_POST['tensi'],
                    'nadi' => $_POST['nadi'],
                    'respirasi' => $_POST['respirasi'],
                    'tinggi' => $_POST['tinggi'],
                    'berat' => $_POST['berat'],
                    'gcs' => $_POST['gcs'],
                    'kesadaran' => $_POST['kesadaran'],
                    'keluhan' => $_POST['keluhan'],
                    'pemeriksaan' => $_POST['pemeriksaan'],
                    'alergi' => $_POST['alergi'],
                    'imun_ke' => $_POST['imun_ke'],
                    'rtl' => $_POST['rtl'],
                    'penilaian' => $_POST['penilaian'],
                    'instruksi' => $_POST['instruksi'],
                    'nip' => $_POST['nip']
                ]);
              } else {
                $query = $this->db('pemeriksaan_ralan')
                  ->save([
                    'no_rawat' => revertNorawat($id),
                    'tgl_perawatan' => date('Y-m-d'),
                    'jam_rawat' => date('H:i:s'),
                    'suhu_tubuh' => $_POST['suhu_tubuh'],
                    'tensi' => $_POST['tensi'],
                    'nadi' => $_POST['nadi'],
                    'respirasi' => $_POST['respirasi'],
                    'tinggi' => $_POST['tinggi'],
                    'berat' => $_POST['berat'],
                    'gcs' => $_POST['gcs'],
                    'kesadaran' => $_POST['kesadaran'],
                    'keluhan' => $_POST['keluhan'],
                    'pemeriksaan' => $_POST['pemeriksaan'],
                    'alergi' => $_POST['alergi'],
                    'imun_ke' => $_POST['imun_ke'],
                    'rtl' => $_POST['rtl'],
                    'penilaian' => $_POST['penilaian']
                ]);
              }



              $get_kd_penyakit = $_POST['kd_penyakit'];
              for ($i = 0; $i < count($get_kd_penyakit); $i++) {
                $kd_penyakit = $get_kd_penyakit[$i];
                $query = $this->db('diagnosa_pasien')
                  ->save([
                    'no_rawat' => revertNorawat($id),
                    'kd_penyakit' => $kd_penyakit,
                    'status' => 'Ralan',
                    'prioritas' => $i+1,
                    'status_penyakit' => 'Lama'
                ]);
              }

              $get_kode = $_POST['kode'];
              for ($i = 0; $i < count($get_kode); $i++) {
                $kode = $get_kode[$i];
                $query = $this->db('prosedur_pasien')
                  ->save([
                    'no_rawat' => revertNorawat($id),
                    'kode' => $kode,
                    'status' => 'Ralan',
                    'prioritas' => $i+1
                ]);
              }

              $get_kd_jenis_prw = $_POST['kd_jenis_prw'];
              for ($i = 0; $i < count($get_kd_jenis_prw); $i++) {
                  $kd_jenis_prw = $get_kd_jenis_prw[$i];
                  $row = $this->db('jns_perawatan')->where('kd_jenis_prw', $kd_jenis_prw)->oneArray();
                  $query = $this->db('rawat_jl_dr')
                    ->save([
                      'no_rawat' => revertNorawat($id),
                      'kd_jenis_prw' => $kd_jenis_prw,
                      'kd_dokter' => $this->core->getUserInfo('username', null, true),
                      'tgl_perawatan' => date('Y-m-d'),
                      'jam_rawat' => date('H:i:s'),
                      'material' => $row['material'],
                      'bhp' => $row['bhp'],
                      'tarif_tindakandr' => $row['tarif_tindakandr'],
                      'kso' => $row['kso'],
                      'menejemen' => $row['menejemen'],
                      'biaya_rawat' => $row['biaya_rawat'],
                      'stts_bayar' => 'Belum'
                  ]);
              }

              $query = $this->db('catatan_perawatan')
                ->save([
                  'tanggal' => date('Y-m-d'),
                  'jam' => date('H:i:s'),
                  'no_rawat' => revertNorawat($id),
                  'kd_dokter' => $this->core->getUserInfo('username', null, true),
                  'catatan' => $_POST['catatan']
              ]);

            } else {

              $check_db = $this->db()->pdo()->query("SHOW COLUMNS FROM `pemeriksaan_ralan` LIKE 'instruksi'");
              $check_db->execute();
              $check_db = $check_db->fetch();

              if($check_db) {
                $_POST['instruksi'] = '';
                $_POST['nip'] = $this->core->getUserInfo('username', null, true);
                $query = $this->db('pemeriksaan_ralan')
                  ->where('no_rawat', revertNorawat($id))
                  ->update([
                    'suhu_tubuh' => $_POST['suhu_tubuh'],
                    'tensi' => $_POST['tensi'],
                    'nadi' => $_POST['nadi'],
                    'respirasi' => $_POST['respirasi'],
                    'tinggi' => $_POST['tinggi'],
                    'berat' => $_POST['berat'],
                    'gcs' => $_POST['gcs'],
                    'kesadaran' => $_POST['kesadaran'],
                    'keluhan' => $_POST['keluhan'],
                    'pemeriksaan' => $_POST['pemeriksaan'],
                    'alergi' => $_POST['alergi'],
                    'imun_ke' => $_POST['imun_ke'],
                    'rtl' => $_POST['rtl'],
                    'penilaian' => $_POST['penilaian'],
                    'instruksi' => $_POST['instruksi'],
                    'nip' => $_POST['nip']
                ]);
              } else {
                $query = $this->db('pemeriksaan_ralan')
                  ->where('no_rawat', revertNorawat($id))
                  ->update([
                    'suhu_tubuh' => $_POST['suhu_tubuh'],
                    'tensi' => $_POST['tensi'],
                    'nadi' => $_POST['nadi'],
                    'respirasi' => $_POST['respirasi'],
                    'tinggi' => $_POST['tinggi'],
                    'berat' => $_POST['berat'],
                    'gcs' => $_POST['gcs'],
                    'kesadaran' => $_POST['kesadaran'],
                    'keluhan' => $_POST['keluhan'],
                    'pemeriksaan' => $_POST['pemeriksaan'],
                    'alergi' => $_POST['alergi'],
                    'imun_ke' => $_POST['imun_ke'],
                    'rtl' => $_POST['rtl'],
                    'penilaian' => $_POST['penilaian']
                ]);
              }

              $get_kd_penyakit = $_POST['kd_penyakit'];
              $this->db('diagnosa_pasien')->where('no_rawat', revertNorawat($id))->delete();
              for ($i = 0; $i < count($get_kd_penyakit); $i++) {
                $kd_penyakit = $get_kd_penyakit[$i];
                $query = $this->db('diagnosa_pasien')
                  ->save([
                    'no_rawat' => revertNorawat($id),
                    'kd_penyakit' => $kd_penyakit,
                    'status' => 'Ralan',
                    'prioritas' => $i+1,
                    'status_penyakit' => 'Lama'
                ]);
              }

              $get_kode = $_POST['kode'];
              $this->db('prosedur_pasien')->where('no_rawat', revertNorawat($id))->delete();
              for ($i = 0; $i < count($get_kode); $i++) {
                $kode = $get_kode[$i];
                $query = $this->db('prosedur_pasien')
                  ->save([
                    'no_rawat' => revertNorawat($id),
                    'kode' => $kode,
                    'status' => 'Ralan',
                    'prioritas' => $i+1
                ]);
              }

              $get_kd_jenis_prw = $_POST['kd_jenis_prw'];
              $this->db('rawat_jl_dr')->where('no_rawat', revertNorawat($id))->delete();
              for ($i = 0; $i < count($get_kd_jenis_prw); $i++) {
                  $kd_jenis_prw = $get_kd_jenis_prw[$i];
                  $row = $this->db('jns_perawatan')->where('kd_jenis_prw', $kd_jenis_prw)->oneArray();
                  $query = $this->db('rawat_jl_dr')
                    ->save([
                      'no_rawat' => revertNorawat($id),
                      'kd_jenis_prw' => $kd_jenis_prw,
                      'kd_dokter' => $this->core->getUserInfo('username', null, true),
                      'tgl_perawatan' => date('Y-m-d'),
                      'jam_rawat' => date('H:i:s'),
                      'material' => $row['material'],
                      'bhp' => $row['bhp'],
                      'tarif_tindakandr' => $row['tarif_tindakandr'],
                      'kso' => $row['kso'],
                      'menejemen' => $row['menejemen'],
                      'biaya_rawat' => $row['biaya_rawat'],
                      'stts_bayar' => 'Belum'
                  ]);
              }

              if($this->db('catatan_perawatan')->where('no_rawat', revertNorawat($id))->oneArray()){
                $query = $this->db('catatan_perawatan')
                  ->where('no_rawat', revertNorawat($id))
                  ->where('kd_dokter', $this->core->getUserInfo('username', null, true))
                  ->update([
                    'catatan' => $_POST['catatan']
                ]);
              } else {
                $query = $this->db('catatan_perawatan')
                  ->save([
                    'tanggal' => date('Y-m-d'),
                    'jam' => date('H:i:s'),
                    'no_rawat' => revertNorawat($id),
                    'kd_dokter' => $this->core->getUserInfo('username', null, true),
                    'catatan' => $_POST['catatan']
                ]);
              }

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

    public function postRadiologiSave($id = null)
    {
        $errors = 0;
        $location = url([ADMIN, 'dokter_ralan', 'view', $id]);

        if (!$errors) {
            unset($_POST['save']);
            $no_order = $this->core->setNoOrderRad();
            $query = $this->db('permintaan_radiologi')
              ->save([
                'noorder' => $no_order,
                'no_rawat' => revertNorawat($id),
                'tgl_permintaan' => date('Y-m-d'),
                'jam_permintaan' => date('H:i:s'),
                'tgl_sampel' => '0000-00-00',
                'jam_sampel' => '00:00:00',
                'tgl_hasil' => '0000-00-00',
                'jam_hasil' => '00:00:00',
                'dokter_perujuk' => $this->core->getUserInfo('username', null, true),
                'status' => 'ralan',
                'informasi_tambahan' => $_POST['informasi_tambahan'],
                'diagnosa_klinis' => $_POST['diagnosa_klinis']
              ]);

            if ($query) {
                for ($i = 0; $i < count($_POST['kd_jenis_prw']); $i++) {
                  $query = $this->db('permintaan_pemeriksaan_radiologi')
                    ->save([
                      'noorder' => $no_order,
                      'kd_jenis_prw' => $_POST['kd_jenis_prw'][$i],
                      'stts_bayar' => 'Belum'
                    ]);
                }
                $this->notify('success', 'Simpan sukes');
            } else {
                $this->notify('failure', 'Simpan gagal');
            }

            redirect($location);
        }

        redirect($location, $_POST);
    }

    public function postLaboratoriumSave($id = null)
    {
        $errors = 0;
        $location = url([ADMIN, 'dokter_ralan', 'view', $id]);

        if (!$errors) {
            unset($_POST['save']);
            $no_order = $this->core->setNoOrderLab();
            $query = $this->db('permintaan_lab')
              ->save([
                'noorder' => $no_order,
                'no_rawat' => revertNorawat($id),
                'tgl_permintaan' => date('Y-m-d'),
                'jam_permintaan' => date('H:i:s'),
                'tgl_sampel' => '0000-00-00',
                'jam_sampel' => '00:00:00',
                'tgl_hasil' => '0000-00-00',
                'jam_hasil' => '00:00:00',
                'dokter_perujuk' => $this->core->getUserInfo('username', null, true),
                'status' => 'ralan',
                'informasi_tambahan' => $_POST['informasi_tambahan'],
                'diagnosa_klinis' => $_POST['diagnosa_klinis']
              ]);

            if ($query) {
                for ($i = 0; $i < count($_POST['kd_jenis_prw']); $i++) {
                  $query = $this->db('permintaan_pemeriksaan_lab')
                    ->save([
                      'noorder' => $no_order,
                      'kd_jenis_prw' => $_POST['kd_jenis_prw'][$i],
                      'stts_bayar' => 'Belum'
                    ]);
                }
                $this->notify('success', 'Simpan sukes');
            } else {
                $this->notify('failure', 'Simpan gagal');
            }

            redirect($location);
        }

        redirect($location, $_POST);
    }

    public function postResepSave($id = null)
    {
        $errors = 0;
        $location = url([ADMIN, 'dokter_ralan', 'view', $id]);

        if (!$errors) {
            unset($_POST['save']);
            $query = $this->db('resep_obat')->where('no_rawat', revertNorawat($id))->where('status', 'ralan')->oneArray();
            $no_resep = $query['no_resep'];
            if(empty($query)) {
              $no_resep = $this->core->setNoResep();
              $query = $this->db('resep_obat')
                ->save([
                  'no_resep' => $no_resep,
                  'tgl_perawatan' => date('Y-m-d'),
                  'jam' => date('H:i:s'),
                  'no_rawat' => revertNorawat($id),
                  'kd_dokter' => $this->core->getUserInfo('username', null, true),
                  'tgl_peresepan' => date('Y-m-d'),
                  'jam_peresepan' => date('H:i:s'),
                  'status' => 'ralan'
                ]);
            }
            if ($query) {
                for ($i = 0; $i < count($_POST['kode_brng']); $i++) {
                  $this->db('resep_dokter')
                    ->save([
                      'no_resep' => $no_resep,
                      'kode_brng' => $_POST['kode_brng'][$i],
                      'jml' => $_POST['jml'][$i],
                      'aturan_pakai' => $_POST['aturan_pakai'][$i]
                    ]);
                }
                $this->notify('success', 'Simpan sukes');
            } else {
                $this->notify('failure', 'Simpan gagal');
            }

            redirect($location);
        }

        redirect($location, $_POST);
    }

    public function postResepRacikanSave($id = null)
    {
        $errors = 0;
        $location = url([ADMIN, 'dokter_ralan', 'view', $id]);

        if (!$errors) {
            unset($_POST['save']);
            $no_resep = $this->core->setNoResep();
            $query = $this->db('resep_obat')
              ->save([
                'no_resep' => $no_resep,
                'tgl_perawatan' => date('Y-m-d'),
                'jam' => date('H:i:s'),
                'no_rawat' => revertNorawat($id),
                'kd_dokter' => $this->core->getUserInfo('username', null, true),
                'tgl_peresepan' => date('Y-m-d'),
                'jam_peresepan' => date('H:i:s'),
                'status' => 'ralan'
              ]);

            if ($query) {
              $no_racik = $this->db('resep_dokter_racikan')->where('no_resep', $no_resep)->count();
              $no_racik = $no_racik+1;
              $this->db('resep_dokter_racikan')
                ->save([
                  'no_resep' => $no_resep,
                  'no_racik' => $no_racik,
                  'nama_racik' => $_POST['nama_racik'],
                  'kd_racik' => $_POST['kd_racik'],
                  'jml_dr' => $_POST['jml_dr'],
                  'aturan_pakai' => $_POST['aturan_pakai'],
                  'keterangan' => $_POST['keterangan']
                ]);

                for ($i = 0; $i < count($_POST['kode_brng']); $i++) {
                  $kapasitas = $this->db('databarang')->where('kode_brng', $_POST['kode_brng'][$i])->oneArray();
                  $jml = $_POST['jml_dr']*$_POST['kandungan'][$i];
                  $jml = $jml/$kapasitas['kapasitas'];
                  $this->db('resep_dokter_racikan_detail')
                    ->save([
                      'no_resep' => $no_resep,
                      'no_racik' => $no_racik,
                      'kode_brng' => $_POST['kode_brng'][$i],
                      'p1' => '1',
                      'p2' => '1',
                      'kandungan' => $_POST['kandungan'][$i],
                      'jml' => $jml
                    ]);
                }
                $this->notify('success', 'Simpan sukes');
            } else {
                $this->notify('failure', 'Simpan gagal');
            }

            redirect($location);
        }

        redirect($location, $_POST);
    }

    public function getDelPermintaanResep($no_rawat, $no_resep, $kode_brng)
    {
        if ($pendaftaran = $this->db('resep_dokter')->where('no_resep', $no_resep)->where('kode_brng', $kode_brng)->oneArray()) {
            if ($this->db('resep_dokter')->where('no_resep', $no_resep)->where('kode_brng', $kode_brng)->delete()) {
                $this->notify('success', 'Hapus sukses');
            } else {
                $this->notify('failure', 'Hapus gagal');
            }
        }
        redirect(url([ADMIN, 'dokter_ralan', 'view', $no_rawat]).'#resep');
    }

    public function postKontrolSave($id = null)
    {
        $errors = 0;
        $location = url([ADMIN, 'dokter_ralan', 'view', $id]);

        if (checkEmptyFields(['diagnosa','alasan1','tanggal_rujukan'], $_POST)) {
            $this->notify('failure', 'Nama dokter masih kosong');
            redirect($location, $_POST);
        }

        if (!$errors) {
            unset($_POST['save']);
            $no_rawat = revertNorawat($id);
            $_POST['kd_dokter'] = $this->core->getUserInfo('username', null, true);
            $_POST['tahun'] = date('Y');
            $_POST['no_rkm_medis'] = $this->core->getRegPeriksaInfo('no_rkm_medis', $no_rawat);
            $_POST['no_antrian'] = $this->core->setNoSKDP();
            $query = $this->db('skdp_bpjs')->save($_POST);

            if ($query) {
              $this->db('booking_registrasi')
                ->save([
                  'tanggal_booking' => date('Y-m-d'),
                  'jam_booking' => date('H:i:s'),
                  'no_rkm_medis' => $_POST['no_rkm_medis'],
                  'tanggal_periksa' => $_POST['tanggal_datang'],
                  'kd_dokter' => $_POST['kd_dokter'],
                  'kd_poli' => $this->core->getRegPeriksaInfo('kd_poli', $no_rawat),
                  'no_reg' => $this->core->setNoBooking($_POST['kd_dokter'], $_POST['tanggal_rujukan']),
                  'kd_pj' => $this->core->getRegPeriksaInfo('kd_pj', $no_rawat),
                  'limit_reg' => 0,
                  'waktu_kunjungan' => $_POST['tanggal_datang'].' '.date('H:i:s'),
                  'status' => 'Belum'
                ]);
                $this->notify('success', 'Simpan sukes');
            } else {
                $this->notify('failure', 'Simpan gagal');
            }

            redirect($location);
        }

        redirect($location, $_POST);
    }

    public function postUploadDigital($id = null) {
      $dir    = $this->_uploads;
      $cntr   = 0;

      if (!is_uploaded_file($_FILES['files']['tmp_name'][0])) {
          $this->notify('failure', 'Tidak ada berkas');
      } else {
          foreach ($_FILES['files']['tmp_name'] as $image) {
              $img = new \Systems\Lib\Image();

              if ($img->load($image)) {
                  $imgName = time().$cntr++;
                  $imgPath = $dir.'/'.$id.'_'.$imgName.'.'.$img->getInfos('type');
                  $lokasi_file = 'pages/upload/'.$id.'_'.$imgName.'.'.$img->getInfos('type');
                  $img->save($imgPath);
                  $query = $this->db('berkas_digital_perawatan')->save(['no_rawat' => revertNorawat($id), 'kode' => $_POST['kode'], 'lokasi_file' => $lokasi_file]);
              } else {
                  $this->notify('failure', 'Exstensi berkas salah', 'jpg, png, gif');
              }
          }

          if ($query) {
              $this->notify('success', 'Sukses menambahkan gambar');
          };
      }
      redirect(url([ADMIN, 'dokter_ralan', 'view', $id]));
    }

    public function getAjax()
    {
        header('Content-type: text/html');
        $show = isset($_GET['show']) ? $_GET['show'] : "";
        switch($show){
        	default:
          break;
          case "databarang":
          $rows = $this->db('databarang')
            ->join('gudangbarang', 'gudangbarang.kode_brng=databarang.kode_brng')
            ->where('status', '1')
            ->where('stok', '>', '10')
            ->where('gudangbarang.kd_bangsal', $this->settings->get('farmasi.deporalan'))
            ->like('databarang.nama_brng', '%'.$_GET['nama_brng'].'%')
            ->limit(10)
            ->toArray();

          foreach ($rows as $row) {
            $array[] = array(
                'kode_brng' => $row['kode_brng'],
                'nama_brng'  => $row['nama_brng']
            );
          }
          echo json_encode($array, true);
          break;
          case "aturan_pakai":
          $rows = $this->db('master_aturan_pakai')->like('aturan', '%'.$_GET['aturan'].'%')->toArray();
          foreach ($rows as $row) {
            $array[] = array(
                'aturan'  => $row['aturan']
            );
          }
          echo json_encode($array, true);
          break;
          case "jns_perawatan":
          $rows = $this->db('jns_perawatan')->like('nm_perawatan', '%'.$_GET['nm_perawatan'].'%')->toArray();
          foreach ($rows as $row) {
            $array[] = array(
                'kd_jenis_prw' => $row['kd_jenis_prw'],
                'nm_perawatan'  => $row['nm_perawatan']
            );
          }
          echo json_encode($array, true);
          break;
          case "jns_perawatan_lab":
          $rows = $this->db('jns_perawatan_lab')->like('nm_perawatan', '%'.$_GET['nm_perawatan'].'%')->toArray();
          foreach ($rows as $row) {
            $array[] = array(
                'kd_jenis_prw' => $row['kd_jenis_prw'],
                'nm_perawatan'  => $row['nm_perawatan']
            );
          }
          echo json_encode($array, true);
          break;
          case "jns_perawatan_radiologi":
          $rows = $this->db('jns_perawatan_radiologi')->like('nm_perawatan', '%'.$_GET['nm_perawatan'].'%')->toArray();
          foreach ($rows as $row) {
            $array[] = array(
                'kd_jenis_prw' => $row['kd_jenis_prw'],
                'nm_perawatan'  => $row['nm_perawatan']
            );
          }
          echo json_encode($array, true);
          break;
          case "icd10":
          $phrase = '';
          if(isset($_GET['s']))
            $phrase = $_GET['s'];

          $rows = $this->data_icd('icd10')->like('kode', '%'.$phrase.'%')->orLike('nama', '%'.$phrase.'%')->toArray();
          foreach ($rows as $row) {
            $array[] = array(
                'kd_penyakit' => $row['kode'],
                'nm_penyakit'  => $row['nama']
            );
          }
          echo json_encode($array, true);
          break;
          case "icd9":
          $phrase = '';
          if(isset($_GET['s']))
            $phrase = $_GET['s'];

          $rows = $this->data_icd('icd9')->like('kode', '%'.$phrase.'%')->orLike('nama', '%'.$phrase.'%')->toArray();
          foreach ($rows as $row) {
            $array[] = array(
                'kode' => $row['kode'],
                'deskripsi_panjang'  => $row['nama']
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
        echo $this->draw(MODULES.'/dokter_ralan/js/admin/dokter_ralan.js');
        exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/dokter_ralan/css/admin/dokter_ralan.css');
        exit();
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/jscripts/lightbox/lightbox.min.css'));
        $this->core->addJS(url('assets/jscripts/lightbox/lightbox.min.js'));

        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));
        $this->core->addCSS(url([ADMIN, 'dokter_ralan', 'css']));
        $this->core->addJS(url([ADMIN, 'dokter_ralan', 'javascript']), 'footer');
    }

    protected function data_icd($table)
    {
        return new DB_ICD($table);
    }

}
