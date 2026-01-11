<?php
namespace Plugins\Dokter_Igd;

use Systems\AdminModule;

class Admin extends AdminModule
{
    protected array $assign = [];

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
        $this->_Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa);
        return $this->draw('manage.html', ['rawat_jalan' => $this->assign, 'cek_vclaim' => $cek_vclaim, 'admin_mode' => $this->settings->get('settings.admin_mode')]);
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
        $this->_Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa);
        echo $this->draw('display.html', ['rawat_jalan' => $this->assign, 'cek_vclaim' => $cek_vclaim, 'admin_mode' => $this->settings->get('settings.admin_mode')]);
        exit();
    }

    public function _Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa='')
    {
        $this->_addHeaderFiles();

        $this->assign['poliklinik']     = $this->db('poliklinik')->where('status', '1')->toArray();
        $this->assign['dokter']         = $this->db('dokter')->where('status', '1')->toArray();
        $this->assign['penjab']       = $this->db('penjab')->where('status', '1')->toArray();
        $this->assign['no_rawat'] = '';
        $this->assign['no_reg']     = '';
        $this->assign['tgl_registrasi']= date('Y-m-d');
        $this->assign['jam_reg']= date('H:i:s');

        $igd = $this->settings('settings', 'igd');
        $sql = "SELECT reg_periksa.*,
            pasien.*,
            dokter.*,
            poliklinik.*,
            penjab.*
          FROM reg_periksa, pasien, dokter, poliklinik, penjab
          WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis
          AND reg_periksa.kd_poli = '$igd'
          AND reg_periksa.tgl_registrasi BETWEEN '$tgl_kunjungan' AND '$tgl_kunjungan_akhir'
          AND reg_periksa.kd_dokter = dokter.kd_dokter
          AND reg_periksa.kd_poli = poliklinik.kd_poli
          AND reg_periksa.kd_pj = penjab.kd_pj";

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

          $no_resep = $this->core->setNoResep($_POST['tgl_perawatan']);
          $cek_resep = $this->db('resep_obat')->where('no_rawat', $_POST['no_rawat'])->where('tgl_peresepan', $_POST['tgl_perawatan'])->where('tgl_perawatan', '0000-00-00')->where('status', 'ralan')->oneArray();

          if(empty($cek_resep)) {

            $resep_obat = $this->db('resep_obat')
              ->save([
                'no_resep' => $no_resep,
                'tgl_perawatan' => '0000-00-00',
                'jam' => '00:00:00',
                'no_rawat' => $_POST['no_rawat'],
                'kd_dokter' => $this->core->getUserInfo('username', null, true),
                'tgl_peresepan' => $_POST['tgl_perawatan'],
                'jam_peresepan' => $_POST['jam_rawat'],
                'status' => 'ralan',
                'tgl_penyerahan' => '0000-00-00',
                'jam_penyerahan' => '00:00:00'
              ]);
            if ($this->db('resep_obat')->where('no_resep', $no_resep)->where('kd_dokter', $this->core->getUserInfo('username', null, true))->oneArray()) {
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

      if($_POST['kat'] == 'racikan') {

        $no_resep = $this->core->setNoResep($_POST['tgl_perawatan']);
        $cek_resep = $this->db('resep_obat')->where('no_rawat', $_POST['no_rawat'])->where('tgl_peresepan', $_POST['tgl_perawatan'])->where('tgl_perawatan', '0000-00-00')->where('status', 'ralan')->oneArray();

        $_POST['jam_rawat'] = date('H:i:s');

        if(empty($cek_resep)) {

          $resep_obat = $this->db('resep_obat')
            ->save([
              'no_resep' => $no_resep,
              'tgl_perawatan' => '0000-00-00',
              'jam' => '00:00:00',
              'no_rawat' => $_POST['no_rawat'],
              'kd_dokter' => $this->core->getUserInfo('username', null, true),
              'tgl_peresepan' => $_POST['tgl_perawatan'],
              'jam_peresepan' => $_POST['jam_rawat'],
              'status' => 'ralan',
              'tgl_penyerahan' => '0000-00-00',
              'jam_penyerahan' => '00:00:00'
            ]);

          if ($this->db('resep_obat')->where('no_resep', $no_resep)->where('kd_dokter', $this->core->getUserInfo('username', null, true))->oneArray()) {
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
            $kode_brng_count = count($_POST['kode_brng']);
            for ($i = 0; $i < $kode_brng_count; $i++) {
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

        } else {

          $no_resep = $cek_resep['no_resep'];

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
          $kode_brng_count = count($_POST['kode_brng']);
          for ($i = 0; $i < $kode_brng_count; $i++) {
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
        $cek_lab = $this->db('permintaan_lab')->where('no_rawat', $_POST['no_rawat'])->where('tgl_permintaan', date('Y-m-d'))->where('tgl_sampel', '<>', '0000-00-00')->where('status', 'ralan')->oneArray();
        if(!$cek_lab) {
          $urut = $this->db('permintaan_lab')
              ->where('tgl_permintaan', date('Y-m-d'))
              ->nextRightNumber('noorder', 4);
          $noorder = 'PL' . date('Ymd') . sprintf('%04d', $urut);

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
          $template_laboratorium = $this->db('template_laboratorium')->where('kd_jenis_prw', $_POST['kd_jenis_prw'])->toArray();
          $template_count = count($template_laboratorium);
          for ($i = 0; $i < $template_count; $i++) {
            $this->db('permintaan_detail_permintaan_lab')
              ->save([
                'noorder' => $noorder,
                'kd_jenis_prw' => $_POST['kd_jenis_prw'],
                'id_template' => $template_laboratorium[$i]['id_template'],
                'stts_bayar' => 'Belum'
              ]);
          }
        } else {
          $noorder = $cek_lab['noorder'];
          $this->db('permintaan_pemeriksaan_lab')
            ->save([
              'noorder' => $noorder,
              'kd_jenis_prw' => $_POST['kd_jenis_prw'],
              'stts_bayar' => 'Belum'
            ]);
          $template_laboratorium = $this->db('template_laboratorium')->where('kd_jenis_prw', $_POST['kd_jenis_prw'])->toArray();
          $template_count = count($template_laboratorium);
          for ($i = 0; $i < $template_count; $i++) {
            $this->db('permintaan_detail_permintaan_lab')
              ->save([
                'noorder' => $noorder,
                'kd_jenis_prw' => $_POST['kd_jenis_prw'],
                'id_template' => $template_laboratorium[$i]['id_template'],
                'stts_bayar' => 'Belum'
              ]);
          }
        }
      }

      if($_POST['kat'] == 'radiologi') {
        $cek_rad = $this->db('permintaan_radiologi')->where('no_rawat', $_POST['no_rawat'])->where('tgl_permintaan', date('Y-m-d'))->where('tgl_sampel', '<>', '0000-00-00')->where('status', 'ralan')->oneArray();
        if(!$cek_rad) {
          $urut = $this->db('permintaan_radiologi')
              ->where('tgl_permintaan', date('Y-m-d'))
              ->nextRightNumber('noorder', 4);
          $noorder = 'PR' . date('Ymd') . sprintf('%04d', $urut);

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
        ->where('kd_bangsal', $this->settings->get('farmasi.igd'))
        ->where('no_resep', $_POST['no_resep'])
        ->toArray();

      $racikan = $this->db('resep_dokter_racikan')
        ->where('no_resep', $_POST['no_resep'])
        ->toArray();

      foreach ($racikan as &$r) {
        $r['detail'] = $this->db('resep_dokter_racikan_detail')
          ->join('databarang', 'databarang.kode_brng=resep_dokter_racikan_detail.kode_brng')
          ->join('gudangbarang', 'gudangbarang.kode_brng=resep_dokter_racikan_detail.kode_brng')
          ->where('kd_bangsal', $this->settings->get('farmasi.igd'))
          ->where('no_resep', $r['no_resep'])
          ->where('no_racik', $r['no_racik'])
          ->toArray();
      }

      echo $this->draw('copyresep.display.html', ['copy_resep' => $return, 'copy_resep_racikan' => $racikan]);
      exit();
    }

    public function postSaveCopyResep()
    {
      $_POST['kode_brng'] = json_decode($_POST['kode_brng'], true);
      $_POST['jml'] = json_decode($_POST['jml'], true);
      $_POST['aturan_pakai'] = json_decode($_POST['aturan_pakai'], true);

      $no_resep = $this->core->setNoResep($_POST['tgl_perawatan']);

      $resep_obat = $this->db('resep_obat')
        ->save([
          'no_resep' => $no_resep,
          'tgl_perawatan' => '0000-00-00',
          'jam' => '00:00:00',
          'no_rawat' => $_POST['no_rawat'],
          'kd_dokter' => $this->core->getUserInfo('username', null, true),
          'tgl_peresepan' => $_POST['tgl_perawatan'],
          'jam_peresepan' => $_POST['jam_rawat'],
          'status' => 'ralan',
          'tgl_penyerahan' => '0000-00-00',
          'jam_penyerahan' => '00:00:00'
        ]);

      if (!empty($_POST['kode_brng'])) {
          $kode_brng_count = count($_POST['kode_brng']);
          for ($i = 0; $i < $kode_brng_count; $i++) {
              $this->db('resep_dokter')
                ->save([
                  'no_resep' => $no_resep,
                  'kode_brng' => $_POST['kode_brng'][$i]['value'],
                  'jml' => $_POST['jml'][$i]['value'],
                  'aturan_pakai' => $_POST['aturan_pakai'][$i]['value']
                ]);

          }
      }

      if (isset($_POST['nama_racik'])) {
          $_POST['nama_racik'] = json_decode($_POST['nama_racik'], true);
          $_POST['kd_racik'] = json_decode($_POST['kd_racik'], true);
          $_POST['jml_dr'] = json_decode($_POST['jml_dr'], true);
          $_POST['aturan_pakai_racik'] = json_decode($_POST['aturan_pakai_racik'], true);
          $_POST['keterangan'] = json_decode($_POST['keterangan'], true);
          $_POST['no_racik'] = json_decode($_POST['no_racik'], true);

          $count_racik = count($_POST['nama_racik']);
          for ($i = 0; $i < $count_racik; $i++) {
              $no_racik = $_POST['no_racik'][$i]['value'];
              $nama_racik = $_POST['nama_racik'][$i]['value'];
              $kd_racik = $_POST['kd_racik'][$i]['value'];
              $jml_dr = $_POST['jml_dr'][$i]['value'];
              $aturan_pakai = $_POST['aturan_pakai_racik'][$i]['value'];
              $keterangan = $_POST['keterangan'][$i]['value'];

              $this->db('resep_dokter_racikan')
                ->save([
                  'no_resep' => $no_resep,
                  'no_racik' => $no_racik,
                  'nama_racik' => $nama_racik,
                  'kd_racik' => $kd_racik,
                  'jml_dr' => $jml_dr,
                  'aturan_pakai' => $aturan_pakai,
                  'keterangan' => $keterangan
                ]);
              
              $kode_brng_racikan = json_decode($_POST['kode_brng_racikan_'.$no_racik], true);
              $jml_racikan = json_decode($_POST['jml_racikan_'.$no_racik], true);
              $p1 = json_decode($_POST['p1_'.$no_racik], true);
              $p2 = json_decode($_POST['p2_'.$no_racik], true);

              if(!empty($kode_brng_racikan)) {
                $count_detail = count($kode_brng_racikan);
                for ($j = 0; $j < $count_detail; $j++) {
                    $this->db('resep_dokter_racikan_detail')
                        ->save([
                            'no_resep' => $no_resep,
                            'no_racik' => $no_racik,
                            'kode_brng' => $kode_brng_racikan[$j]['value'],
                            'p1' => $p1[$j]['value'],
                            'p2' => $p2[$j]['value'],
                            'kandungan' => '0', // Default or fetch if needed
                            'jml' => $jml_racikan[$j]['value']
                        ]);
                }
              }
          }
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

      // Get list of no_resep that have racikan
      $resep_racikan_nos = $this->db('resep_dokter_racikan')->select('no_resep')->toArray();
      $resep_racikan_nos = array_column($resep_racikan_nos, 'no_resep');

      $rows = $this->db('resep_obat')
        ->join('dokter', 'dokter.kd_dokter=resep_obat.kd_dokter')
        ->where('no_rawat', $_POST['no_rawat'])
        ->group('resep_obat.no_resep')
        ->group('resep_obat.no_rawat')
        ->group('resep_obat.kd_dokter')
        ->toArray();
      
      // Filter out racikan
      $rows = array_filter($rows, function($row) use ($resep_racikan_nos) {
          return !in_array($row['no_resep'], $resep_racikan_nos);
      });

      $resep = [];
      $jumlah_total_resep = 0;
      foreach ($rows as $row) {
        $row['nomor'] = $i++;
        $row['resep_dokter'] = $this->db('resep_dokter')->join('databarang', 'databarang.kode_brng=resep_dokter.kode_brng')->where('no_resep', $row['no_resep'])->toArray();
        foreach ($row['resep_dokter'] as $value) {
          $value['ralan'] = $value['jml'] * $value['dasar'];
          $jumlah_total_resep += floatval($value['ralan']);
        }
        $resep[] = $row;
      }

      $rows_racikan = [];
      if (!empty($resep_racikan_nos)) {
        $rows_racikan = $this->db('resep_obat')
          ->select('resep_obat.*')
          ->select('dokter.nm_dokter')
          ->select('resep_dokter_racikan.nama_racik')
          ->select('resep_dokter_racikan.jml_dr')
          ->select('resep_dokter_racikan.aturan_pakai')
          ->select('resep_dokter_racikan.keterangan')
          ->join('dokter', 'dokter.kd_dokter=resep_obat.kd_dokter')
          ->join('resep_dokter_racikan', 'resep_dokter_racikan.no_resep=resep_obat.no_resep')
          ->where('no_rawat', $_POST['no_rawat'])
          ->in('resep_obat.no_resep', $resep_racikan_nos)
          ->group('resep_obat.no_resep')
          ->group('resep_obat.no_rawat')
          ->group('resep_obat.kd_dokter')
          ->group('resep_dokter_racikan.nama_racik')
          ->group('resep_dokter_racikan.jml_dr')
          ->group('resep_dokter_racikan.aturan_pakai')
          ->group('resep_dokter_racikan.keterangan')
          ->toArray();
      }
      $resep_racikan = [];
      $jumlah_total_resep_racikan = 0;
      foreach ($rows_racikan as $row) {
        $row['nomor'] = $i++;
        $row['resep_dokter_racikan_detail'] = $this->db('resep_dokter_racikan_detail')->join('databarang', 'databarang.kode_brng=resep_dokter_racikan_detail.kode_brng')->where('no_resep', $row['no_resep'])->toArray();
        foreach ($row['resep_dokter_racikan_detail'] as $value) {
          $value['ralan'] = $value['jml'] * $value['dasar'];
          $jumlah_total_resep_racikan += floatval($value['ralan']);
        }
        $resep_racikan[] = $row;
      }

      $rows_laboratorium = $this->db('permintaan_lab')
        ->join('dokter', 'dokter.kd_dokter=permintaan_lab.dokter_perujuk')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('permintaan_lab.status', 'ralan')
        ->toArray();
      $laboratorium = [];
      foreach ($rows_laboratorium as $row) {
        $rows2 = $this->db('permintaan_pemeriksaan_lab')
          ->join('jns_perawatan_lab', 'jns_perawatan_lab.kd_jenis_prw=permintaan_pemeriksaan_lab.kd_jenis_prw')
          //->join('permintaan_detail_permintaan_lab', 'permintaan_detail_permintaan_lab.noorder=permintaan_pemeriksaan_lab.noorder')
          ->where('permintaan_pemeriksaan_lab.noorder', $row['noorder'])
          ->toArray();
          $row['permintaan_pemeriksaan_lab'] = [];
          foreach ($rows2 as $row2) {
            $row2['noorder'] = $row2['noorder'];
            $row2['kd_jenis_prw'] = $row2['kd_jenis_prw'];
            $row2['stts_bayar'] = $row2['stts_bayar'];
            $row2['nm_perawatan'] = $row2['nm_perawatan'];
            $row2['kd_pj'] = $row2['kd_pj'];
            $row2['status'] = $row2['status'];
            $row2['kelas'] = $row2['kelas'];
            $row2['kategori'] = $row2['kategori'];
            $rows3 = $this->db('permintaan_detail_permintaan_lab')->where('noorder', $row2['noorder'])->where('kd_jenis_prw', $row2['kd_jenis_prw'])->toArray();
            $row2['permintaan_detail_permintaan_lab'] = [];
            foreach ($rows3 as $row3) {
              $row3['template_laboratorium'] = $this->db('template_laboratorium')->where('kd_jenis_prw', $row3['kd_jenis_prw'])->where('id_template', $row3['id_template'])->oneArray();
              $row2['permintaan_detail_permintaan_lab'][] = $row3;
            }
            $row['permintaan_pemeriksaan_lab'][] = $row2;
          }
        $laboratorium[] = $row;
      }

      $rows_radiologi = $this->db('permintaan_radiologi')
        ->join('permintaan_pemeriksaan_radiologi', 'permintaan_pemeriksaan_radiologi.noorder=permintaan_radiologi.noorder')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('permintaan_radiologi.status', 'ralan')
        ->toArray();
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
        
        $row['resep_racikan'] = $this->db('resep_dokter_racikan')
          ->where('no_resep', $row['no_resep'])
          ->toArray();
          
        foreach ($row['resep_racikan'] as &$racikan) {
            $racikan['detail'] = $this->db('resep_dokter_racikan_detail')
              ->join('databarang', 'databarang.kode_brng=resep_dokter_racikan_detail.kode_brng')
              ->where('no_resep', $racikan['no_resep'])
              ->where('no_racik', $racikan['no_racik'])
              ->toArray();
        }

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
        //'jumlah_total_lab' => $jumlah_total_lab,
        'jumlah_total_rad' => $jumlah_total_rad,
        'no_rawat' => $_POST['no_rawat']
      ]);
      exit();
    }

    public function anyRincianEresep()
    {
      $i = 1;

      // Get list of no_resep that have racikan
      $resep_racikan_nos = $this->db('resep_dokter_racikan')->select('no_resep')->toArray();
      $resep_racikan_nos = array_column($resep_racikan_nos, 'no_resep');

      $rows = $this->db('resep_obat')
        ->join('dokter', 'dokter.kd_dokter=resep_obat.kd_dokter')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('resep_obat.status', 'ralan')
        ->group('resep_obat.no_resep')
        ->group('resep_obat.no_rawat')
        ->group('resep_obat.kd_dokter')
        ->toArray();
      
      // Filter out racikan
      $rows = array_filter($rows, function($row) use ($resep_racikan_nos) {
          return !in_array($row['no_resep'], $resep_racikan_nos);
      });

      $resep = [];
      $jumlah_total_resep = 0;
      foreach ($rows as $row) {
        $row['nomor'] = $i++;
        $row['resep_dokter'] = $this->db('resep_dokter')->join('databarang', 'databarang.kode_brng=resep_dokter.kode_brng')->where('no_resep', $row['no_resep'])->toArray();
        foreach ($row['resep_dokter'] as $value) {
          $value['ralan'] = $value['jml'] * $value['dasar'];
          $jumlah_total_resep += floatval($value['ralan']);
        }
        $resep[] = $row;
      }

      // Get list of no_resep that have racikan
      $resep_racikan_nos = $this->db('resep_dokter_racikan')->select('no_resep')->toArray();
      $resep_racikan_nos = array_column($resep_racikan_nos, 'no_resep');
      
      $rows_racikan = [];
      if (!empty($resep_racikan_nos)) {
        $rows_racikan = $this->db('resep_obat')
          ->join('dokter', 'dokter.kd_dokter=resep_obat.kd_dokter')
          ->where('no_rawat', $_POST['no_rawat'])
          ->in('resep_obat.no_resep', $resep_racikan_nos)
          ->group('resep_obat.no_resep')
          ->group('resep_obat.no_rawat')
          ->group('resep_obat.kd_dokter')
          ->where('resep_obat.status', 'ralan')
          ->toArray();
      }
      $resep_racikan = [];
      $jumlah_total_resep_racikan = 0;
      foreach ($rows_racikan as $row) {
        $row['nomor'] = $i++;
        $row['resep_dokter_racikan_detail'] = $this->db('resep_dokter_racikan_detail')->join('databarang', 'databarang.kode_brng=resep_dokter_racikan_detail.kode_brng')->where('no_resep', $row['no_resep'])->toArray();
        foreach ($row['resep_dokter_racikan_detail'] as $value) {
          $value['ralan'] = $value['jml'] * $value['dasar'];
          $jumlah_total_resep_racikan += floatval($value['ralan']);
        }
        $resep_racikan[] = $row;
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

      echo $this->draw('rincian.eresep.html', [
        'resep' => $resep,
        'resep_racikan' => $resep_racikan,
        'data_resep' => $data_resep,
        'jumlah_total_resep' => $jumlah_total_resep,
        'jumlah_total_resep_racikan' => $jumlah_total_resep_racikan,
        'no_rawat' => $_POST['no_rawat']
      ]);
      exit();
    }

    public function postHapusNomorPermintaanLaboratorium()
    {
      $this->db('permintaan_lab')
      ->where('no_rawat', $_POST['no_rawat'])
      ->where('noorder', $_POST['noorder'])
      ->where('tgl_permintaan', $_POST['tgl_permintaan'])
      ->where('jam_permintaan', $_POST['jam_permintaan'])
      ->where('status', 'Ralan')
      ->delete();
      exit();
    }

    public function postHapusPermintaanLaboratorium()
    {
      $this->db('permintaan_pemeriksaan_lab')
      ->where('noorder', $_POST['noorder'])
      ->where('kd_jenis_prw', $_POST['kd_jenis_prw'])
      ->where('stts_bayar', 'Belum')
      ->delete();
      exit();
    }

    public function postHapusPermintaanLab()
    {
      $this->db('permintaan_lab')
      ->where('noorder', $_POST['noorder'])
      ->where('no_rawat', $_POST['no_rawat'])
      ->delete();
      exit();
    }

    public function postHapusPermintaanRad()
    {
      $this->db('permintaan_radiologi')
      ->where('noorder', $_POST['noorder'])
      ->where('no_rawat', $_POST['no_rawat'])
      ->delete();
      exit();
    }

    public function getDetailPermintaan($noorder, $kd_jenis_prw)
    {
      $rows = $this->db('permintaan_detail_permintaan_lab')->where('noorder', $noorder)->where('kd_jenis_prw', $kd_jenis_prw)->toArray();
      $detail_permintaan_lab = [];
      foreach ($rows as $row) {
        $row['template_laboratorium'] = $this->db('template_laboratorium')->where('kd_jenis_prw', $row['kd_jenis_prw'])->where('id_template', $row['id_template'])->oneArray();
        $detail_permintaan_lab[] = $row;
      }
      $this->tpl->set('detail', $detail_permintaan_lab);
      echo $this->tpl->draw(MODULES.'/dokter_igd/view/admin/details.html', true);
      exit();
    }

    public function postHapusDetailPermintaan()
    {
      $this->db('permintaan_detail_permintaan_lab')
        ->where('noorder', $_POST['noorder'])
        ->where('kd_jenis_prw', $_POST['kd_jenis_prw'])
        ->where('id_template', $_POST['id_template'])
        ->delete();
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

      echo $this->draw('soap.html', ['pemeriksaan' => $result, 'pemeriksaan_ranap' => $result_ranap, 'diagnosa' => $diagnosa, 'prosedur' => $prosedur, 'admin_mode' => $this->settings->get('settings.admin_mode')]);
      exit();
    }

    public function postSaveSOAP()
    {
      $_POST['nip'] = $this->core->getUserInfo('username', null, true);

      if(!$this->db('pemeriksaan_ralan')->where('no_rawat', $_POST['no_rawat'])->where('tgl_perawatan', $_POST['tgl_perawatan'])->where('jam_rawat', $_POST['jam_rawat'])->where('nip', $_POST['nip'])->oneArray()) {
        $this->db('pemeriksaan_ralan')->save($_POST);
      } else {
        $this->db('pemeriksaan_ralan')->where('no_rawat', $_POST['no_rawat'])->where('tgl_perawatan', $_POST['tgl_perawatan'])->where('jam_rawat', $_POST['jam_rawat'])->where('nip', $_POST['nip'])->save($_POST);
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
        ->select([
          'tanggal_periksa' => 'booking_registrasi.tanggal_periksa',
          'no_reg' => 'booking_registrasi.no_reg',
          'nm_poli' => 'poliklinik.nm_poli',
          'nm_dokter' => 'dokter.nm_dokter',
          'png_jawab' => 'penjab.png_jawab',
          'status' => 'booking_registrasi.status'
        ])
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
            'no_reg' => $this->core->setNoBooking($this->core->getUserInfo('username', null, true), $this->core->getRegPeriksaInfo('kd_poli', $_POST['no_rawat']), $_POST['tanggal_rujukan']),
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
      $poliklinik = $this->db('poliklinik')->select('kd_poli')->where('status', '1')->toArray();
      $poliklinik = implode(",", array_column($poliklinik, 'kd_poli'));
      $poliklinik = explode(',', $poliklinik);
      if($this->core->getUserInfo('role', null, true) != 'admin') {
        $poliklinik = explode(',', $this->core->getUserInfo('cap', null, true));
      }
      
      $layanan = $this->db('jns_perawatan')
        ->where('total_byrdr', '<>', '0')
        ->where('status', '1')
        ->like('nm_perawatan', '%'.$_POST['layanan'].'%')
        ->in('kd_poli', $poliklinik)
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
        ->where('gudangbarang.kd_bangsal', $this->settings->get('farmasi.igd'))
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
        ->where('gudangbarang.kd_bangsal', $this->settings->get('farmasi.igd'))
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
            ->where('stok', '>', '1')
            ->where('gudangbarang.kd_bangsal', $this->settings->get('farmasi.igd'))
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

          $rows = $this->db('penyakit')->like('kd_penyakit', '%'.$phrase.'%')->orLike('nm_penyakit', '%'.$phrase.'%')->toArray();
          foreach ($rows as $row) {
            $array[] = array(
                'kd_penyakit' => $row['kd_penyakit'],
                'nm_penyakit'  => $row['nm_penyakit']
            );
          }
          echo json_encode($array, true);
          break;
          case "icd9":
          $phrase = '';
          if(isset($_GET['s']))
            $phrase = $_GET['s'];

          $rows = $this->db('icd9')->like('kode', '%'.$phrase.'%')->orLike('deskripsi_panjang', '%'.$phrase.'%')->toArray();
          foreach ($rows as $row) {
            $array[] = array(
                'kode' => $row['kode'],
                'deskripsi_panjang'  => $row['deskripsi_panjang']
            );
          }
          echo json_encode($array, true);
          break;
        }
        exit();
    }

    public function getEresep($no_rawat)
    {
      $no_rawat = revertNorawat($no_rawat);
      $i = 1;

      // Get list of no_resep that have racikan
      $racikan_nos = $this->db('resep_dokter_racikan')->select('no_resep')->toArray();
      $racikan_nos = array_column($racikan_nos, 'no_resep');

      $rows = $this->db('resep_obat')
        ->join('dokter', 'dokter.kd_dokter=resep_obat.kd_dokter')
        ->where('no_rawat', $no_rawat)
        ->where('resep_obat.status', 'ralan')
        ->group('resep_obat.no_resep')
        ->group('resep_obat.no_rawat')
        ->group('resep_obat.kd_dokter')
        ->toArray();

      // Filter out racikan from the main list
      $rows = array_filter($rows, function($row) use ($racikan_nos) {
          return !in_array($row['no_resep'], $racikan_nos);
      });

      $resep = [];
      $jumlah_total_resep = 0;
      foreach ($rows as $row) {
        $row['nomor'] = $i++;
        $row['resep_dokter'] = $this->db('resep_dokter')->join('databarang', 'databarang.kode_brng=resep_dokter.kode_brng')->where('no_resep', $row['no_resep'])->toArray();
        foreach ($row['resep_dokter'] as $value) {
          $value['ralan'] = $value['jml'] * $value['dasar'];
          $jumlah_total_resep += floatval($value['ralan']);
        }
        $resep[] = $row;
      }

      $resep_racikan = [];
      $jumlah_total_resep_racikan = 0;

      if (!empty($racikan_nos)) {
        $rows_racikan = $this->db('resep_obat')
          ->join('dokter', 'dokter.kd_dokter=resep_obat.kd_dokter')
          ->join('resep_dokter_racikan', 'resep_dokter_racikan.no_resep=resep_obat.no_resep')
          ->select('resep_obat.*, dokter.nm_dokter, resep_dokter_racikan.nama_racik, resep_dokter_racikan.jml_dr, resep_dokter_racikan.aturan_pakai')
          ->where('no_rawat', $no_rawat)
          ->where('resep_obat.status', 'ralan')
          ->in('resep_obat.no_resep', $racikan_nos)
          ->group('resep_obat.no_resep')
          ->group('resep_obat.no_rawat')
          ->group('resep_obat.kd_dokter')
          ->toArray();

        foreach ($rows_racikan as $row) {
          $row['nomor'] = $i++;
          $row['resep_dokter_racikan_detail'] = $this->db('resep_dokter_racikan_detail')->join('databarang', 'databarang.kode_brng=resep_dokter_racikan_detail.kode_brng')->where('no_resep', $row['no_resep'])->toArray();
          foreach ($row['resep_dokter_racikan_detail'] as $value) {
            $value['ralan'] = $value['jml'] * $value['dasar'];
            $jumlah_total_resep_racikan += floatval($value['ralan']);
          }
          $resep_racikan[] = $row;
        }
      }

      $reg_periksa = $this->db('reg_periksa')->where('no_rawat', $no_rawat)->oneArray();
      $rows_data_resep = $this->db('resep_obat')
      ->join('reg_periksa', 'reg_periksa.no_rawat=resep_obat.no_rawat')
      ->where('resep_obat.kd_dokter', $this->core->getUserInfo('username', null, true))
      ->where('reg_periksa.no_rkm_medis', $this->core->getRegPeriksaInfo('no_rkm_medis', $no_rawat))
      ->toArray();

      $data_resep = [];
      foreach ($rows_data_resep as $row) {
        $row['resep_dokter'] = $this->db('resep_dokter')
          ->join('databarang', 'databarang.kode_brng=resep_dokter.kode_brng')
          ->where('no_resep', $row['no_resep'])
          ->toArray();
        $data_resep[] = $row;
      }

      echo $this->draw('eresep.html', [
        'resep' => $resep,
        'resep_racikan' => $resep_racikan,
        'data_resep' => $data_resep,
        'jumlah_total_resep' => $jumlah_total_resep,
        'jumlah_total_resep_racikan' => $jumlah_total_resep_racikan,
        'no_rawat' => $no_rawat
      ]);
      exit();
    }

    public function postCekWaktu()
    {
      echo date('H:i:s');
      exit();
    }

    public function getLokalis($no_rawat)
    {
      $filename = 'lokalis_' . $no_rawat . '.png';
      $lokalis = UPLOADS . '/lokalis/' . $filename;
      if(!file_exists($lokalis)) {
        $filename = '';
      }
      echo $this->draw('lokalis.html', [
        'no_rawat' => revertNorawat($no_rawat),
        'lokalis' => $filename
      ]);
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

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $cek_pegawai = $this->db('pegawai')->where('nik', $this->core->getUserInfo('username', $_SESSION['mlite_user']))->oneArray();
        $cek_role = '';
        if($cek_pegawai) {
          $cek_role = $this->core->getPegawaiInfo('nik', $this->core->getUserInfo('username', $_SESSION['mlite_user']));
        }
        $this->assign['websocket'] = $this->settings->get('settings.websocket');
        $this->assign['websocket_proxy'] = $this->settings->get('settings.websocket_proxy');
        echo $this->draw(MODULES.'/dokter_igd/js/admin/dokter_igd.js', ['cek_role' => $cek_role, 'mlite' => $this->assign]);
        exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/dokter_igd/css/admin/dokter_igd.css');
        exit();
    }

    public function getResume($no_rawat)
    {
      $data_resume['pemeriksaan_ralan'] = $this->db('pemeriksaan_ralan')->where('no_rawat', revertNoRawat($no_rawat))->oneArray();
      $data_resume['diagnosa'] = $this->db('diagnosa_pasien')->join('penyakit', 'penyakit.kd_penyakit=diagnosa_pasien.kd_penyakit')->where('no_rawat', revertNoRawat($no_rawat))->where('prioritas', 1)->where('diagnosa_pasien.status', 'Ralan')->oneArray();
      $data_resume['prosedur'] = $this->db('prosedur_pasien')->join('icd9', 'icd9.kode=prosedur_pasien.kode')->where('no_rawat', revertNoRawat($no_rawat))->where('prioritas', 1)->where('status', 'Ralan')->oneArray();
      echo $this->draw('resume.html', [
        'reg_periksa' => $this->db('reg_periksa')->where('no_rawat', revertNoRawat($no_rawat))->oneArray(),
        'resume_pasien' => $this->db('resume_pasien')->where('no_rawat', revertNoRawat($no_rawat))->join('dokter', 'dokter.kd_dokter=resume_pasien.kd_dokter')->oneArray(),
        'data_resume' => $data_resume
      ]);
      exit();
    }

    public function getResumeTampil($no_rawat)
    {
      echo $this->draw('resume.tampil.html', ['resume_pasien' => $this->db('resume_pasien')->where('no_rawat', revertNoRawat($no_rawat))->join('dokter', 'dokter.kd_dokter=resume_pasien.kd_dokter')->oneArray()]);
      exit();
    }

    public function postResumeSave()
    {
      $_POST['kd_dokter']	= $this->core->getUserInfo('username', $_SESSION['mlite_user']);

      if($this->db('resume_pasien')->where('no_rawat', $_POST['no_rawat'])->where('kd_dokter', $_POST['kd_dokter'])->oneArray()) {
        $this->db('resume_pasien')
          ->where('no_rawat', $_POST['no_rawat'])
          ->save([
          'kd_dokter'  => $_POST['kd_dokter'],
          'keluhan_utama' => $_POST['keluhan_utama'],
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
        $this->db('resume_pasien')->save([
          'no_rawat' => $_POST['no_rawat'],
          'kd_dokter'  => $_POST['kd_dokter'],
          'keluhan_utama' => $_POST['keluhan_utama'],
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

    public function postResumeDelete()
    {
      $kd_dokter = $this->core->getUserInfo('username', $_SESSION['mlite_user']);
      
      $this->db('resume_pasien')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('kd_dokter', $kd_dokter)
        ->delete();
      
      exit();
    }

    public function getMedisIgd($no_rawat)
    {
      $data_medisIgd['pemeriksaan_ralan'] = $this->db('pemeriksaan_ralan')->where('no_rawat', revertNoRawat($no_rawat))->oneArray();
      
      // Get existing penilaian_medis_igd data
      $penilaian_medis_igd = $this->db('penilaian_medis_igd')
        ->where('no_rawat', revertNoRawat($no_rawat))
        ->join('dokter', 'dokter.kd_dokter=penilaian_medis_igd.kd_dokter')
        ->oneArray();
      
      // If no penilaian_medis_igd data exists, use fallback from pemeriksaan_ralan
      if (empty($penilaian_medis_igd)) {
        // Get latest pemeriksaan_ralan data for fallback
        $pemeriksaan_fallback = $this->db('pemeriksaan_ralan')
          ->where('no_rawat', revertNoRawat($no_rawat))
          ->desc('tgl_perawatan')
          ->desc('jam_rawat')
          ->oneArray();
        
        if (!empty($pemeriksaan_fallback)) {
          // Get current user (dokter) info
          $current_dokter = $this->core->getUserInfo('username', $_SESSION['mlite_user']);
          $dokter_info = $this->db('dokter')->where('kd_dokter', $current_dokter)->oneArray();
          
          // Create fallback data structure matching penilaian_medis_igd
          $penilaian_medis_igd = [
            'no_rawat' => revertNoRawat($no_rawat),
            'kd_dokter' => $current_dokter,
            'tanggal' => date('Y-m-d H:i:s'),
            'anamnesis' => 'Autoanamnesis',
            'hubungan' => '',
            'keluhan_utama' => $pemeriksaan_fallback['keluhan'] ?? '',
            'rps' => '',
            'rpd' => '',
            'rpk' => '',
            'rpo' => '',
            'alergi' => $pemeriksaan_fallback['alergi'] ?? '',
            'keadaan' => 'Sehat',
            'gcs' => $pemeriksaan_fallback['gcs'] ?? '',
            'kesadaran' => $pemeriksaan_fallback['kesadaran'] ?? 'Compos Mentis',
            'td' => $pemeriksaan_fallback['tensi'] ?? '',
            'nadi' => $pemeriksaan_fallback['nadi'] ?? '',
            'rr' => $pemeriksaan_fallback['respirasi'] ?? '',
            'suhu' => $pemeriksaan_fallback['suhu_tubuh'] ?? '',
            'spo' => $pemeriksaan_fallback['spo2'] ?? '',
            'bb' => $pemeriksaan_fallback['berat'] ?? '',
            'tb' => $pemeriksaan_fallback['tinggi'] ?? '',
            'kepala' => 'Normal',
            'mata' => 'Normal',
            'gigi' => 'Normal',
            'leher' => 'Normal',
            'thoraks' => 'Normal',
            'abdomen' => 'Normal',
            'genital' => 'Normal',
            'ekstremitas' => 'Normal',
            'ket_fisik' => '',
            'ket_lokalis' => '',
            'ekg' => '',
            'rad' => '',
            'lab' => '',
            'diagnosis' => '',
            'tata' => '',
            // Add dokter info for join compatibility
            'nm_dokter' => $dokter_info['nm_dokter'] ?? '',
            'jk' => $dokter_info['jk'] ?? '',
            'tmp_lahir' => $dokter_info['tmp_lahir'] ?? '',
            'tgl_lahir' => $dokter_info['tgl_lahir'] ?? '',
            'gol_drh' => $dokter_info['gol_drh'] ?? '',
            'agama' => $dokter_info['agama'] ?? '',
            'almt_tgl' => $dokter_info['almt_tgl'] ?? '',
            'no_telp' => $dokter_info['no_telp'] ?? '',
            'stts_nikah' => $dokter_info['stts_nikah'] ?? '',
            'kd_sps' => $dokter_info['kd_sps'] ?? '',
            'alumni' => $dokter_info['alumni'] ?? '',
            'no_ijn_praktek' => $dokter_info['no_ijn_praktek'] ?? '',
            'status' => $dokter_info['status'] ?? ''
          ];
        }
      }
      
      echo $this->draw('medis.igd.html', [
        'reg_periksa' => $this->db('reg_periksa')->where('no_rawat', revertNoRawat($no_rawat))->oneArray(),
        'penilaian_medis_igd' => $penilaian_medis_igd,
        'pasien'  => $this->db('pasien')->where('no_rawat', revertNoRawat($no_rawat))->join('reg_periksa','pasien.no_rkm_medis=reg_periksa.no_rkm_medis')->oneArray(),
        'dokter'  => $this->db('dokter')->where('no_rawat', revertNoRawat($no_rawat))->join('reg_periksa','dokter.kd_dokter=reg_periksa.kd_dokter')->oneArray(),
        'data_medisIgd' => $data_medisIgd
      ]);
      exit();
    }

    public function getMedisIgdTampil($no_rawat)
    {
      echo $this->draw('medis.igd.tampil.html', ['penilaian_medis_igd' => $this->db('penilaian_medis_igd')->where('no_rawat', revertNoRawat($no_rawat))->join('dokter', 'dokter.kd_dokter=penilaian_medis_igd.kd_dokter')->toArray()]);
      exit();
    }

    public function postMedisIgd()
    {
      $_POST['kd_dokter'] = $this->core->getUserInfo('username', $_SESSION['mlite_user']);
      
      // Handle edit mode
      if(isset($_POST['mode']) && $_POST['mode'] == 'edit' && isset($_POST['original_tanggal'])) {
        $this->db('penilaian_medis_igd')
          ->where('no_rawat', $_POST['no_rawat'])
          ->where('tanggal', $_POST['original_tanggal'])
          ->save([
          'kd_dokter'           =>  $_POST['kd_dokter'],
          'tanggal'             =>  $_POST['tanggal'],  
          'anamnesis'           =>  $_POST['anamnesis'],    
          'hubungan'            =>  $_POST['hubungan'],    
          'keluhan_utama'       =>  $_POST['keluhan_utama'],    
          'rps'                 =>  $_POST['rps'],    
          'rpd'                 =>  $_POST['rpd'],    
          'rpk'                 =>  $_POST['rpk'],    
          'rpo'                 =>  $_POST['rpo'],    
          'alergi'              =>  $_POST['alergi'],    
          'keadaan'             =>  $_POST['keadaan'],    
          'gcs'                 =>  $_POST['gcs'],    
          'kesadaran'           =>  $_POST['kesadaran'],    
          'td'                  =>  $_POST['td'],    
          'nadi'                =>  $_POST['nadi'],    
          'rr'                  =>  $_POST['rr'],    
          'suhu'                =>  $_POST['suhu'],    
          'spo'                 =>  $_POST['spo'],    
          'bb'                  =>  $_POST['bb'],    
          'tb'                  =>  $_POST['tb'],    
          'kepala'              =>  $_POST['kepala'],    
          'mata'                =>  $_POST['mata'],    
          'gigi'                =>  $_POST['gigi'],    
          'leher'               =>  $_POST['leher'],    
          'thoraks'             =>  $_POST['thoraks'],    
          'abdomen'             =>  $_POST['abdomen'],    
          'genital'             =>  $_POST['genital'],    
          'ekstremitas'         =>  $_POST['ekstremitas'],    
          'ket_fisik'           =>  $_POST['ket_fisik'],    
          'ket_lokalis'         =>  $_POST['ket_lokalis'],    
          'ekg'                 =>  $_POST['ekg'],    
          'rad'                 =>  $_POST['rad'],    
          'lab'                 =>  $_POST['lab'],    
          'diagnosis'           =>  $_POST['diagnosis'],    
          'tata'                =>  $_POST['tata']
        ]);
      } else if($this->db('penilaian_medis_igd')->where('no_rawat', $_POST['no_rawat'])->where('kd_dokter', $_POST['kd_dokter'])->oneArray()) {
        $this->db('penilaian_medis_igd')
          ->where('no_rawat', $_POST['no_rawat'])
          ->save([
          'kd_dokter'           =>  $_POST['kd_dokter'],
          'tanggal'             =>  $_POST['tanggal'],  
          'anamnesis'           =>  $_POST['anamnesis'],    
          'hubungan'            =>  $_POST['hubungan'],    
          'keluhan_utama'       =>  $_POST['keluhan_utama'],    
          'rps'                 =>  $_POST['rps'],    
          'rpd'                 =>  $_POST['rpd'],    
          'rpk'                 =>  $_POST['rpk'],    
          'rpo'                 =>  $_POST['rpo'],    
          'alergi'              =>  $_POST['alergi'],    
          'keadaan'             =>  $_POST['keadaan'],    
          'gcs'                 =>  $_POST['gcs'],    
          'kesadaran'           =>  $_POST['kesadaran'],    
          'td'                  =>  $_POST['td'],    
          'nadi'                =>  $_POST['nadi'],    
          'rr'                  =>  $_POST['rr'],    
          'suhu'                =>  $_POST['suhu'],    
          'spo'                 =>  $_POST['spo'],    
          'bb'                  =>  $_POST['bb'],    
          'tb'                  =>  $_POST['tb'],    
          'kepala'              =>  $_POST['kepala'],    
          'mata'                =>  $_POST['mata'],    
          'gigi'                =>  $_POST['gigi'],    
          'leher'               =>  $_POST['leher'],    
          'thoraks'             =>  $_POST['thoraks'],    
          'abdomen'             =>  $_POST['abdomen'],    
          'genital'             =>  $_POST['genital'],    
          'ekstremitas'         =>  $_POST['ekstremitas'],    
          'ket_fisik'           =>  $_POST['ket_fisik'],    
          'ket_lokalis'         =>  $_POST['ket_lokalis'],    
          'ekg'                 =>  $_POST['ekg'],    
          'rad'                 =>  $_POST['rad'],    
          'lab'                 =>  $_POST['lab'],    
          'diagnosis'           =>  $_POST['diagnosis'],    
          'tata'                =>  $_POST['tata']
        ]);
      } else {
        $this->db('penilaian_medis_igd')->save([
          'no_rawat'            => $_POST['no_rawat'],
          'kd_dokter'           => $_POST['kd_dokter'],
          'tanggal'             =>  $_POST['tanggal'],    
          'anamnesis'           =>  $_POST['anamnesis'],    
          'hubungan'            =>  $_POST['hubungan'],    
          'keluhan_utama'       =>  $_POST['keluhan_utama'],    
          'rps'                 =>  $_POST['rps'],    
          'rpd'                 =>  $_POST['rpd'],    
          'rpk'                 =>  $_POST['rpk'],    
          'rpo'                 =>  $_POST['rpo'],    
          'alergi'              =>  $_POST['alergi'],    
          'keadaan'             =>  $_POST['keadaan'],    
          'gcs'                 =>  $_POST['gcs'],    
          'kesadaran'           =>  $_POST['kesadaran'],    
          'td'                  =>  $_POST['td'],    
          'nadi'                =>  $_POST['nadi'],    
          'rr'                  =>  $_POST['rr'],    
          'suhu'                =>  $_POST['suhu'],    
          'spo'                 =>  $_POST['spo'],    
          'bb'                  =>  $_POST['bb'],    
          'tb'                  =>  $_POST['tb'],    
          'kepala'              =>  $_POST['kepala'],    
          'mata'                =>  $_POST['mata'],    
          'gigi'                =>  $_POST['gigi'],    
          'leher'               =>  $_POST['leher'],    
          'thoraks'             =>  $_POST['thoraks'],    
          'abdomen'             =>  $_POST['abdomen'],    
          'genital'             =>  $_POST['genital'],    
          'ekstremitas'         =>  $_POST['ekstremitas'],    
          'ket_fisik'           =>  $_POST['ket_fisik'],    
          'ket_lokalis'         =>  $_POST['ket_lokalis'],    
          'ekg'                 =>  $_POST['ekg'],    
          'rad'                 =>  $_POST['rad'],    
          'lab'                 =>  $_POST['lab'],    
          'diagnosis'           =>  $_POST['diagnosis'],    
          'tata'                =>  $_POST['tata']
        ]);
      }
      exit();
    }

    public function postHapusmedisigd()
    {
      $this->db('penilaian_medis_igd')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('tanggal', $_POST['tanggal'])
        ->delete();
      exit();
    }

    // ===== TRIASE IGD METHODS =====
    
    public function anyTriaseIgd()
    {
        if(isset($_POST['no_rawat'])) {
            $no_rawat = $_POST['no_rawat'];
            $no_rkm_medis = $_POST['no_rkm_medis'];
            $nm_pasien = $_POST['nm_pasien'];
            $tgl_registrasi = $_POST['tgl_registrasi'];
            
            // Cek apakah sudah ada data triase
            $triase_igd = $this->db('mlite_triase_igd')
                ->where('no_rawat', $no_rawat)
                ->oneArray();
            
            // Jika belum ada, buat data default
            if(!$triase_igd) {
                $triase_igd = [
                    'id_triase' => '',
                    'no_rawat' => $no_rawat,
                    'no_rkm_medis' => $no_rkm_medis,
                    'nm_pasien' => $nm_pasien,
                    'tgl_registrasi' => $tgl_registrasi,
                    'tgl_triase' => date('Y-m-d\TH:i'),
                    'petugas_id' => $this->core->getUserInfo('username', null, true),
                    'kesadaran' => '',
                    'airway' => '',
                    'breathing' => '',
                    'circulation' => '',
                    'tekanan_darah' => '',
                    'nadi' => '',
                    'respirasi' => '',
                    'suhu' => '',
                    'spo2' => '',
                    'gcs_e' => '',
                    'gcs_v' => '',
                    'gcs_m' => '',
                    'kategori' => '',
                    'skala_triase' => '',
                    'keluhan_utama' => '',
                    'diagnosa_awal' => ''
                ];
            } else {
                $triase_igd['nm_pasien'] = $nm_pasien;
                $triase_igd['tgl_registrasi'] = $tgl_registrasi;
                // Format datetime untuk input datetime-local
                if($triase_igd['tgl_triase']) {
                    $triase_igd['tgl_triase'] = date('Y-m-d\TH:i', strtotime($triase_igd['tgl_triase']));
                }
            }
            
            // Ambil data petugas untuk dropdown
            $petugas = $this->db('petugas')->where('status', '1')->toArray();
            
            echo $this->draw('triase_igd.html', [
                'triase_igd' => $triase_igd,
                'petugas' => $petugas
            ]);
        }
        exit();
    }
    
    public function postTriaseIgdSave()
    {
        try {
            // Validasi input required
            $required_fields = ['no_rawat', 'no_rkm_medis', 'tgl_triase', 'petugas_id', 'kesadaran_triase', 'airway', 'breathing', 'circulation', 'kategori'];
            foreach($required_fields as $field) {
                if(empty($_POST[$field])) {
                    throw new \Exception("Field {$field} harus diisi");
                }
            }
            
            // Prepare clean data array
            $data_to_save = [];
            
            // Copy only allowed fields
            $allowed_fields = ['no_rawat', 'no_rkm_medis', 'tgl_triase', 'petugas_id', 'kesadaran', 'airway', 'breathing', 'circulation', 'tekanan_darah', 'nadi', 'respirasi', 'suhu', 'spo2', 'gcs_e', 'gcs_v', 'gcs_m', 'kategori', 'skala_triase', 'keluhan_utama', 'diagnosa_awal'];
            
            foreach($allowed_fields as $field) {
                if(isset($_POST[$field]) && $_POST[$field] !== '') {
                    $data_to_save[$field] = $_POST[$field];
                }
            }
            
            // Handle kesadaran_triase mapping
            if(isset($_POST['kesadaran_triase'])) {
                $data_to_save['kesadaran'] = $_POST['kesadaran_triase'];
            }
            
            // Format datetime
            if(isset($data_to_save['tgl_triase'])) {
                $data_to_save['tgl_triase'] = date('Y-m-d H:i:s', strtotime($data_to_save['tgl_triase']));
            }
            
            // Remove null timestamp fields and let MySQL handle them
            unset($data_to_save['created_at']);
            // unset($data_to_save['updated_at']);
            $data_to_save['updated_at'] = date('Y-m-d H:i:s');
            
            // Use direct SQL approach to bypass framework timestamp behavior
            if(!empty($_POST['id_triase'])) {
                // Update data yang sudah ada
                $fields = array_keys($data_to_save);
                $set_clause = [];
                foreach($fields as $field) {
                    $set_clause[] = "{$field} = :{$field}";
                }
                $sql = "UPDATE mlite_triase_igd SET " . implode(', ', $set_clause) . " WHERE id_triase = :id_triase";
                
                // Add id_triase to data for WHERE clause
                $data_to_save['id_triase'] = $_POST['id_triase'];
                
                $stmt = $this->db()->pdo()->prepare($sql);
                $query = $stmt->execute($data_to_save);
                $message = 'Data triase berhasil diupdate';
            } else {
                // Insert data baru
                $fields = array_keys($data_to_save);
                $placeholders = ':' . implode(', :', $fields);
                $sql = "INSERT INTO mlite_triase_igd (" . implode(', ', $fields) . ") VALUES (" . $placeholders . ")";
                
                $stmt = $this->db()->pdo()->prepare($sql);
                $query = $stmt->execute($data_to_save);
                $message = 'Data triase berhasil disimpan';
            }
            
            if($query) {
                $data['status'] = 'success';
                $data['msg'] = $message;
            } else {
                $data['status'] = 'error';
                $data['msg'] = 'Gagal menyimpan data triase';
            }
            
        } catch(\Exception $e) {
            $data['status'] = 'error';
            $data['msg'] = $e->getMessage();
        }
        
        echo json_encode($data);
        exit();
    }
    
    public function postTriaseIgdDelete()
    {
        try {
            if(empty($_POST['no_rawat'])) {
                throw new \Exception('No rawat tidak boleh kosong');
            }
            
            $query = $this->db('mlite_triase_igd')
                ->where('no_rawat', $_POST['no_rawat'])
                ->delete();
            
            if($query) {
                $data['status'] = 'success';
                $data['msg'] = 'Data triase berhasil dihapus';
            } else {
                $data['status'] = 'error';
                $data['msg'] = 'Gagal menghapus data triase';
            }
            
        } catch(\Exception $e) {
            $data['status'] = 'error';
            $data['msg'] = $e->getMessage();
        }
        
        echo json_encode($data);
        exit();
    }
    
    public function postTriaseIgdView()
    {
        if(isset($_POST['no_rawat'])) {
            $triase_igd = $this->db('mlite_triase_igd')
                ->join('petugas', 'petugas.nip=mlite_triase_igd.petugas_id')
                ->join('pasien', 'pasien.no_rkm_medis=mlite_triase_igd.no_rkm_medis')
                ->where('mlite_triase_igd.no_rawat', $_POST['no_rawat'])
                ->oneArray();
            
            if($triase_igd) {
                $triase_igd['nama_petugas'] = $triase_igd['nama'];
                // Pastikan nm_pasien tersedia, jika tidak ada berikan fallback
                if(empty($triase_igd['nm_pasien'])) {
                    $triase_igd['nm_pasien'] = 'Nama Pasien Tidak Ditemukan';
                }
                // Hitung total GCS
                $total_gcs = ($triase_igd['gcs_e'] ?? 0) + ($triase_igd['gcs_v'] ?? 0) + ($triase_igd['gcs_m'] ?? 0);
                $triase_igd['total_gcs'] = $total_gcs > 0 ? $total_gcs : '-';
                
                echo $this->draw('triase_igd_view.html', ['triase_igd' => $triase_igd]);
            } else {
                echo '<div class="alert alert-warning">Data triase tidak ditemukan</div>';
            }
        }
        exit();
    }
    
    public function postSimpanLokalis()
    {
        $img = $_POST['image'] ?? '';
        $no_rawat = $_POST['no_rawat'] ?? '';
        $no_rawat = convertNoRawat($no_rawat);

        if (!$img) {
            http_response_code(400);
            exit('Data kosong');
        }

        $img = str_replace('data:image/png;base64,', '', $img);
        $img = base64_decode($img);

        if(!is_dir(UPLOADS . '/lokalis/')) {
            mkdir(UPLOADS . '/lokalis/', 0755, true);
        }

        $filename = 'lokalis_' . $no_rawat . '.png';
        file_put_contents(UPLOADS . '/lokalis/' . $filename, $img);

        echo json_encode([
            'status' => 'ok',
            'file' => $filename
        ]);
        exit;
    }


    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));
        $this->core->addCSS(url([ADMIN, 'dokter_igd', 'css']));
        $this->core->addJS(url([ADMIN, 'dokter_igd', 'javascript']), 'footer');
    }

}
