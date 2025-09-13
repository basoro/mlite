<?php
namespace Plugins\Dokter_Ranap;

use Systems\AdminModule;

class Admin extends AdminModule
{
    protected array $assign = [];

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
        return $this->draw('manage.html', ['rawat_inap' => $this->assign, 'cek_vclaim' => $cek_vclaim]);
    }

    public function anyDisplay()
    {
        $tgl_masuk = '';
        $tgl_masuk_akhir = '';
        $status_pulang = '';
        $status_periksa = '';

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
        $this->_Display($tgl_masuk, $tgl_masuk_akhir, $status_pulang, $status_periksa);
        echo $this->draw('display.html', ['rawat_inap' => $this->assign]);
        exit();
    }

    public function _Display($tgl_masuk='', $tgl_masuk_akhir='', $status_pulang='', $status_periksa='')
    {
        $this->_addHeaderFiles();

        $this->assign['kamar'] = $this->db('kamar')->join('bangsal', 'bangsal.kd_bangsal=kamar.kd_bangsal')->where('statusdata', '1')->toArray();
        $this->assign['dokter']         = $this->db('dokter')->where('status', '1')->toArray();
        $this->assign['penjab']       = $this->db('penjab')->where('status', '1')->toArray();
        $this->assign['no_rawat'] = '';
        $username = $this->core->getUserInfo('username', null, true);

        $bangsal = str_replace(",","','", $this->core->getUserInfo('cap', null, true));

        $sql = "SELECT DISTINCT
            reg_periksa.no_rawat,
            reg_periksa.no_rkm_medis,
            reg_periksa.tgl_registrasi,
            reg_periksa.jam_reg,
            reg_periksa.kd_dokter,
            reg_periksa.no_reg,
            reg_periksa.kd_poli,
            reg_periksa.p_jawab,
            reg_periksa.almt_pj,
            reg_periksa.hubunganpj,
            reg_periksa.biaya_reg,
            reg_periksa.stts,
            reg_periksa.stts_daftar,
            reg_periksa.status_lanjut,
            reg_periksa.kd_pj,
            reg_periksa.umurdaftar,
            reg_periksa.sttsumur,
            reg_periksa.status_bayar,
            reg_periksa.status_poli,
            pasien.nm_pasien,
            pasien.no_ktp,
            pasien.jk,
            pasien.tmp_lahir,
            pasien.tgl_lahir,
            pasien.nm_ibu,
            pasien.alamat,
            pasien.gol_darah,
            pasien.pekerjaan,
            pasien.stts_nikah,
            pasien.agama,
            pasien.tgl_daftar,
            pasien.no_tlp,
            pasien.umur,
            pasien.pnd,
            pasien.keluarga,
            pasien.namakeluarga,
            pasien.kd_pj as pasien_kd_pj,
            pasien.no_peserta,
            pasien.kd_kel,
            pasien.kd_kec,
            pasien.kd_kab,
            pasien.pekerjaanpj,
            pasien.alamatpj,
            pasien.kelurahanpj,
            pasien.kecamatanpj,
            pasien.kabupatenpj,
            pasien.perusahaan_pasien,
            pasien.suku_bangsa,
            pasien.bahasa_pasien,
            pasien.cacat_fisik,
            pasien.email,
            pasien.nip,
            pasien.kd_prop,
            pasien.propinsipj,
            kamar_inap.no_rawat as kamar_inap_no_rawat,
            kamar_inap.kd_kamar,
            kamar_inap.trf_kamar,
            kamar_inap.diagnosa_awal,
            kamar_inap.diagnosa_akhir,
            kamar_inap.tgl_masuk,
            kamar_inap.jam_masuk,
            kamar_inap.tgl_keluar,
            kamar_inap.jam_keluar,
            kamar_inap.lama,
            kamar_inap.ttl_biaya,
            kamar_inap.stts_pulang,
            kamar.kd_bangsal,
            kamar.kelas,
            kamar.status,
            kamar.kd_kamar as kamar_kd_kamar,
            bangsal.nm_bangsal,
            bangsal.kd_bangsal as bangsal_kd_bangsal,
            penjab.png_jawab,
            penjab.nama_perusahaan,
            penjab.alamat_asuransi,
            penjab.no_telp,
            penjab.attn,
            penjab.status as penjab_status,
            dpjp_ranap.no_rawat as dpjp_no_rawat,
            dpjp_ranap.kd_dokter as dpjp_kd_dokter
          FROM
            kamar_inap,
            reg_periksa,
            pasien,
            kamar,
            bangsal,
            penjab,
            dpjp_ranap
          WHERE
            kamar_inap.no_rawat=reg_periksa.no_rawat
          AND
            kamar_inap.no_rawat=dpjp_ranap.no_rawat
          AND
            reg_periksa.no_rkm_medis=pasien.no_rkm_medis
          AND
            kamar_inap.kd_kamar=kamar.kd_kamar
          AND
            bangsal.kd_bangsal=kamar.kd_bangsal
          AND
            reg_periksa.kd_pj=penjab.kd_pj";

        if ($this->core->getUserInfo('role') != 'admin') {
          $sql .= " AND dpjp_ranap.kd_dokter = '$username'";
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
        // Removed GROUP BY clause since we're using DISTINCT

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
          $this->assign['list'][] = $row;
        }

    }

    public function postSaveDetail()
    {
      if($_POST['kat'] == 'tindakan') {
        $jns_perawatan = $this->db('jns_perawatan_inap')->where('kd_jenis_prw', $_POST['kd_jenis_prw'])->oneArray();
        $this->db('rawat_inap_dr')->save([
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
          'biaya_rawat' => $jns_perawatan['total_byrdr']
        ]);
      }
      if($_POST['kat'] == 'obat') {

        $no_resep = $this->core->setNoResep($_POST['tgl_perawatan']);
        $cek_resep = $this->db('resep_obat')->where('no_rawat', $_POST['no_rawat'])->where('tgl_peresepan', $_POST['tgl_perawatan'])->where('tgl_perawatan', '0000-00-00')->where('status', 'ranap')->oneArray();

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
              'status' => 'ranap',
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
        $cek_resep = $this->db('resep_obat')->where('no_rawat', $_POST['no_rawat'])->where('tgl_peresepan', $_POST['tgl_perawatan'])->where('tgl_perawatan', '0000-00-00')->where('status', 'ranap')->oneArray();

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
              'status' => 'ranap',
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
        $cek_lab = $this->db('permintaan_lab')->where('no_rawat', $_POST['no_rawat'])->where('tgl_permintaan', date('Y-m-d'))->where('tgl_sampel', '<>', '0000-00-00')->where('status', 'ranap')->oneArray();
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
              'status' => 'ranap',
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
        $cek_rad = $this->db('permintaan_radiologi')->where('no_rawat', $_POST['no_rawat'])->where('tgl_permintaan', date('Y-m-d'))->where('tgl_sampel', '<>', '0000-00-00')->where('status', 'ranap')->oneArray();
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
              'status' => 'ranap',
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

    public function postHapusNomorPermintaanLaboratorium()
    {
      $this->db('permintaan_lab')
      ->where('no_rawat', $_POST['no_rawat'])
      ->where('noorder', $_POST['noorder'])
      ->where('tgl_permintaan', $_POST['tgl_permintaan'])
      ->where('jam_permintaan', $_POST['jam_permintaan'])
      ->where('status', 'Ranap')
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

    public function postHapusPermintaanLaboratorium()
    {
      $this->db('permintaan_pemeriksaan_lab')
      ->where('noorder', $_POST['noorder'])
      ->where('kd_jenis_prw', $_POST['kd_jenis_prw'])
      ->where('stts_bayar', 'Belum')
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
      echo $this->tpl->draw(MODULES.'/dokter_ranap/view/admin/details.html', true);
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

    public function postHapusPermintaanRad()
    {
      $this->db('permintaan_radiologi')
      ->where('noorder', $_POST['noorder'])
      ->where('no_rawat', $_POST['no_rawat'])
      ->where('dokter_perujuk', $_POST['dokter_perujuk'])
      ->delete();
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
        ->where('kd_bangsal', $this->settings->get('farmasi.deporanap'))
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
          'status' => 'ranap',
          'tgl_penyerahan' => '0000-00-00',
          'jam_penyerahan' => '00:00:00'
        ]);

      $kode_brng_count = count($_POST['kode_brng']);
      for ($i = 0; $i < $kode_brng_count; $i++) {
        /*$cek_stok = $this->db('gudangbarang')
          ->join('databarang', 'databarang.kode_brng=gudangbarang.kode_brng')
          ->where('gudangbarang.kode_brng', $_POST['kode_brng'][$i]['value'])
          ->where('kd_bangsal', $this->settings->get('farmasi.deporanap'))
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
        ->select('resep_obat.no_resep')
        ->select('resep_obat.no_rawat')
        ->select('resep_obat.kd_dokter')
        ->select('resep_obat.tgl_peresepan')
        ->select('resep_obat.jam_peresepan')
        ->select('resep_obat.status')
        ->select('dokter.nm_dokter')
        ->join('dokter', 'dokter.kd_dokter=resep_obat.kd_dokter')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('resep_obat.status', 'ranap')
        ->group('resep_obat.no_resep')
        ->group('resep_obat.no_rawat')
        ->group('resep_obat.kd_dokter')
        ->group('resep_obat.tgl_peresepan')
        ->group('resep_obat.jam_peresepan')
        ->group('resep_obat.status')
        ->group('dokter.nm_dokter')
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

      $rows_racikan = $this->db('resep_obat')
        ->select('resep_obat.no_resep')
        ->select('resep_obat.no_rawat')
        ->select('resep_obat.kd_dokter')
        ->select('resep_obat.tgl_peresepan')
        ->select('resep_obat.jam_peresepan')
        ->select('resep_obat.status')
        ->select('dokter.nm_dokter')
        ->select('resep_dokter_racikan.nama_racik')
        ->select('resep_dokter_racikan.jml_dr')
        ->select('resep_dokter_racikan.aturan_pakai')
        ->select('resep_dokter_racikan.keterangan')
        ->join('dokter', 'dokter.kd_dokter=resep_obat.kd_dokter')
        ->join('resep_dokter_racikan', 'resep_dokter_racikan.no_resep=resep_obat.no_resep')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('resep_obat.status', 'ranap')
        ->group('resep_obat.no_resep')
        ->group('resep_obat.no_rawat')
        ->group('resep_obat.kd_dokter')
        ->group('resep_obat.tgl_peresepan')
        ->group('resep_obat.jam_peresepan')
        ->group('resep_obat.status')
        ->group('dokter.nm_dokter')
        ->group('resep_dokter_racikan.nama_racik')
        ->group('resep_dokter_racikan.jml_dr')
        ->group('resep_dokter_racikan.aturan_pakai')
        ->group('resep_dokter_racikan.keterangan')
        ->toArray();
      $resep_racikan = [];
      $jumlah_total_resep_racikan = 0;
      foreach ($rows_racikan as $row) {
        $row['nomor'] = $i++;
        $row['resep_dokter_racikan_detail'] = $this->db('resep_dokter_racikan_detail')->join('databarang', 'databarang.kode_brng=resep_dokter_racikan_detail.kode_brng')->where('no_resep', $row['no_resep'])->toArray();
        foreach ($row['resep_dokter_racikan_detail'] as $value) {
          $value['dasar'] = $value['jml'] * $value['dasar'];
          $jumlah_total_resep_racikan += floatval($value['dasar']);
        }
        $resep_racikan[] = $row;
      }

      // $rows_laboratorium = $this->db('permintaan_lab')
      //   ->join('permintaan_pemeriksaan_lab', 'permintaan_pemeriksaan_lab.noorder=permintaan_lab.noorder')
      //   ->where('no_rawat', $_POST['no_rawat'])
      //   ->where('permintaan_lab.status', 'ranap')
      //   ->toArray();
      // $jumlah_total_lab = 0;
      // $laboratorium = [];

      // if($rows_laboratorium) {
      //   foreach ($rows_laboratorium as $row) {
      //     $jns_perawatan = $this->db('jns_perawatan_lab')->where('kd_jenis_prw', $row['kd_jenis_prw'])->oneArray();
      //     $row['nm_perawatan'] = $jns_perawatan['nm_perawatan'];
      //     $row['kelas'] = $jns_perawatan['kelas'];
      //     $row['total_byr'] = $jns_perawatan['total_byr'];
      //     $jumlah_total_lab += $jns_perawatan['total_byr'];
      //     $laboratorium[] = $row;
      //   }
      // }

      $rows_laboratorium = $this->db('permintaan_lab')
      ->join('dokter', 'dokter.kd_dokter=permintaan_lab.dokter_perujuk')
      ->where('no_rawat', $_POST['no_rawat'])
      ->where('permintaan_lab.status', 'ranap')
      ->toArray();
      $jumlah_total_lab = 0;
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
        ->where('permintaan_radiologi.status', 'ranap')
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
        $data_resep[] = $row;
      }

      echo $this->draw('rincian.html', [
        'rawat_inap_dr' => $rawat_inap_dr,
        'rawat_inap_pr' => $rawat_inap_pr,
        'rawat_inap_drpr' => $rawat_inap_drpr,
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

    public function postHapusSOAP()
    {
      $this->db('pemeriksaan_ranap')->where('no_rawat', $_POST['no_rawat'])->where('tgl_perawatan', $_POST['tgl_perawatan'])->where('jam_rawat', $_POST['jam_rawat'])->delete();
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
            'no_reg' => $this->core->setNoBooking($this->core->getUserInfo('username', null, true), $this->core->getRegPeriksaInfo('kd_poli', $no_rawat), $_POST['tanggal_rujukan']),
            'kd_pj' => $this->core->getRegPeriksaInfo('kd_pj', $_POST['no_rawat']),
            'limit_reg' => 0,
            'waktu_kunjungan' => $_POST['tanggal_datang'].' '.date('H:i:s'),
            'status' => 'Belum'
          ]);
      }

      /*if(!$this->db('pemeriksaan_ranap')->where('no_rawat', $_POST['no_rawat'])->where('tgl_perawatan', $_POST['tgl_perawatan'])->where('jam_rawat', $_POST['jam_rawat'])->oneArray()) {
        $this->db('pemeriksaan_ranap')->save($_POST);
      } else {
        $this->db('pemeriksaan_ranap')->where('no_rawat', $_POST['no_rawat'])->save($_POST);
      }*/
      exit();
    }

    public function postHapusKontrol()
    {
      $this->db('pemeriksaan_ranap')->where('no_rawat', $_POST['no_rawat'])->delete();
      exit();
    }

    public function anyLayanan()
    {
      $layanan = $this->db('jns_perawatan_inap')
        ->where('total_byrdr', '<>', '0')
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

    public function anyObatRacikan()
    {
      $obat = $this->db('databarang')
        ->join('gudangbarang', 'gudangbarang.kode_brng=databarang.kode_brng')
        ->where('status', '1')
        ->where('gudangbarang.kd_bangsal', $this->settings->get('farmasi.deporanap'))
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
            ->where('gudangbarang.kd_bangsal', $this->settings->get('farmasi.deporanap'))
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
        }
        exit();
    }

    public function postCekWaktu()
    {
      echo date('H:i:s');
      exit();
    }

    public function getResume($no_rawat)
    {
      $data_resume['pemeriksaan_ranap'] = $this->db('pemeriksaan_ranap')->where('no_rawat', revertNoRawat($no_rawat))->oneArray();
      $data_resume['diagnosa'] = $this->db('diagnosa_pasien')->join('penyakit', 'penyakit.kd_penyakit=diagnosa_pasien.kd_penyakit')->where('no_rawat', revertNoRawat($no_rawat))->where('prioritas', 1)->where('diagnosa_pasien.status', 'Ralan')->oneArray();
      $data_resume['prosedur'] = $this->db('prosedur_pasien')->join('icd9', 'icd9.kode=prosedur_pasien.kode')->where('no_rawat', revertNoRawat($no_rawat))->where('prioritas', 1)->where('status', 'Ranap')->oneArray();
      echo $this->draw('resume.html', [
        'reg_periksa' => $this->db('reg_periksa')->where('no_rawat', revertNoRawat($no_rawat))->oneArray(),
        'resume_pasien' => $this->db('resume_pasien_ranap')->where('no_rawat', revertNoRawat($no_rawat))->join('dokter', 'dokter.kd_dokter=resume_pasien_ranap.kd_dokter')->toArray(),
        'data_resume' => $data_resume
      ]);
      exit();
    }

    public function getResumeTampil($no_rawat)
    {
      echo $this->draw('resume.tampil.html', ['resume_pasien' => $this->db('resume_pasien_ranap')->where('no_rawat', revertNoRawat($no_rawat))->join('dokter', 'dokter.kd_dokter=resume_pasien_ranap.kd_dokter')->toArray()]);
      exit();
    }

    public function postResumeSave()
    {
      $_POST['kd_dokter']	= $this->core->getUserInfo('username', $_SESSION['mlite_user']);

      if($this->db('resume_pasien_ranap')->where('no_rawat', $_POST['no_rawat'])->where('kd_dokter', $_POST['kd_dokter'])->oneArray()) {
        $this->db('resume_pasien_ranap')
          ->where('no_rawat', $_POST['no_rawat'])
          ->save([
            'kd_dokter'  => $_POST['kd_dokter'],
            'diagnosa_awal' => $_POST['diagnosa_masuk'],
            'alasan' => '-',
            'keluhan_utama' => $_POST['keluhan_utama'],
            'pemeriksaan_fisik' => '-',
            'jalannya_penyakit' => $_POST['riwayat_penyakit'],
            'pemeriksaan_penunjang' => '-',
            'hasil_laborat' => '-',
            'tindakan_dan_operasi' => '-',
            'obat_di_rs' => '-',
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
            'alergi' => '-',
            'diet' => '-',
            'lab_belum' => '-',
            'edukasi' => '-',
            'cara_keluar' => 'Lainnya',
            'ket_keluar' => '-',
            'keadaan'  => $_POST['kondisi_pulang'],
            'ket_keadaan' => '-',
            'dilanjutkan' => 'Lainnya',
            'ket_dilanjutkan' => '-',
            'kontrol' => date('Y-m-d H:i:s'),
            'obat_pulang' => '-'
        ]);
      } else {
        $this->db('resume_pasien_ranap')->save([
          'no_rawat' => $_POST['no_rawat'],
          'kd_dokter'  => $_POST['kd_dokter'],
          'diagnosa_awal' => $_POST['diagnosa_masuk'],
          'alasan' => '-',
          'keluhan_utama' => $_POST['keluhan_utama'],
          'pemeriksaan_fisik' => '-',
          'jalannya_penyakit' => $_POST['riwayat_penyakit'],
          'pemeriksaan_penunjang' => '-',
          'hasil_laborat' => '-',
          'tindakan_dan_operasi' => '-',
          'obat_di_rs' => '-',
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
          'alergi' => '-',
          'diet' => '-',
          'lab_belum' => '-',
          'edukasi' => '-',
          'cara_keluar' => 'Lainnya',
          'ket_keluar' => '-',
          'keadaan'  => $_POST['kondisi_pulang'],
          'ket_keadaan' => '-',
          'dilanjutkan' => 'Lainnya',
          'ket_dilanjutkan' => '-',
          'kontrol' => date('Y-m-d H:i:s'),
          'obat_pulang' => '-'
        ]);
      }
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

    public function getMedisRanap($no_rawat)
    {
        $no_rawat = revertNorawat($no_rawat);
        $reg_periksa = $this->db('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
            ->join('dokter', 'dokter.kd_dokter=reg_periksa.kd_dokter')
            ->where('no_rawat', $no_rawat)
            ->oneArray();
        
        $penilaian_medis_ranap = $this->db('penilaian_medis_ranap')
            ->join('dokter', 'dokter.kd_dokter=penilaian_medis_ranap.kd_dokter')
            ->where('no_rawat', $no_rawat)
            ->oneArray();
        
        if(!$penilaian_medis_ranap) {
            // Fallback ke pemeriksaan_ranap jika ada
            $pemeriksaan_ranap = $this->db('pemeriksaan_ranap')
                ->where('no_rawat', $no_rawat)
                ->oneArray();
            
            if($pemeriksaan_ranap) {
                $penilaian_medis_ranap = [
                    'no_rawat' => $no_rawat,
                    'tanggal' => date('Y-m-d H:i:s'),
                    'kd_dokter' => $this->core->getUserInfo('username', null, true),
                    'anamnesis' => 'Autoanamnesis',
                    'hubungan' => '',
                    'keluhan_utama' => isset($pemeriksaan_ranap['keluhan']) ? $pemeriksaan_ranap['keluhan'] : '',
                    'rps' => isset($pemeriksaan_ranap['pemeriksaan']) ? $pemeriksaan_ranap['pemeriksaan'] : '',
                    'rpd' => '',
                    'rpk' => '',
                    'rpo' => '',
                    'alergi' => isset($pemeriksaan_ranap['alergi']) ? $pemeriksaan_ranap['alergi'] : '',
                    'keadaan' => 'Sehat',
                    'gcs' => '',
                    'kesadaran' => isset($pemeriksaan_ranap['kesadaran']) ? $pemeriksaan_ranap['kesadaran'] : 'Compos Mentis',
                    'td' => isset($pemeriksaan_ranap['tensi']) ? $pemeriksaan_ranap['tensi'] : '',
                    'nadi' => isset($pemeriksaan_ranap['nadi']) ? $pemeriksaan_ranap['nadi'] : '',
                    'rr' => isset($pemeriksaan_ranap['respirasi']) ? $pemeriksaan_ranap['respirasi'] : '',
                    'suhu' => isset($pemeriksaan_ranap['suhu_tubuh']) ? $pemeriksaan_ranap['suhu_tubuh'] : '',
                    'spo' => '',
                    'bb' => isset($pemeriksaan_ranap['berat']) ? $pemeriksaan_ranap['berat'] : '',
                    'tb' => isset($pemeriksaan_ranap['tinggi']) ? $pemeriksaan_ranap['tinggi'] : '',
                    'kepala' => 'Normal',
                    'mata' => 'Normal',
                    'gigi' => 'Normal',
                    'tht' => 'Normal',
                    'thoraks' => 'Normal',
                    'jantung' => 'Normal',
                    'paru' => 'Normal',
                    'abdomen' => 'Normal',
                    'genital' => 'Normal',
                    'ekstremitas' => 'Normal',
                    'kulit' => 'Normal',
                    'ket_fisik' => '',
                    'ket_lokalis' => '',
                    'lab' => '',
                    'rad' => '',
                    'penunjang' => '',
                    'diagnosis' => isset($pemeriksaan_ranap['penilaian']) ? $pemeriksaan_ranap['penilaian'] : '',
                    'tata' => isset($pemeriksaan_ranap['rtl']) ? $pemeriksaan_ranap['rtl'] : '',
                    'edukasi' => ''
                ];
            }
        }
        
        // Calculate BMI if height and weight available
        if($penilaian_medis_ranap && $penilaian_medis_ranap['tb'] > 0 && $penilaian_medis_ranap['bb'] > 0) {
            $tinggi_m = $penilaian_medis_ranap['tb'] / 100;
            $bmi = $penilaian_medis_ranap['bb'] / ($tinggi_m * $tinggi_m);
            $penilaian_medis_ranap['bmi'] = number_format($bmi, 2);
        }
        
        echo $this->draw('medis.ranap.html', [
            'reg_periksa' => $reg_periksa,
            'pasien' => $reg_periksa,
            'dokter' => $reg_periksa,
            'penilaian_medis_ranap' => $penilaian_medis_ranap
        ]);
        exit();
    }
    
    public function postMedisRanap()
    {
      $_POST['kd_dokter'] = $this->core->getUserInfo('username', $_SESSION['mlite_user']);
      
      // Handle edit mode
      if(isset($_POST['mode']) && $_POST['mode'] == 'edit' && isset($_POST['original_tanggal'])) {
        $this->db('penilaian_medis_ranap')
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
          'tht'                 =>  $_POST['tht'],    
          'thoraks'             =>  $_POST['thoraks'],    
          'jantung'             =>  $_POST['jantung'],    
          'paru'                =>  $_POST['paru'],    
          'abdomen'             =>  $_POST['abdomen'],    
          'genital'             =>  $_POST['genital'],    
          'ekstremitas'         =>  $_POST['ekstremitas'],    
          'kulit'               =>  $_POST['kulit'],    
          'ket_fisik'           =>  $_POST['ket_fisik'],    
          'ket_lokalis'         =>  $_POST['ket_lokalis'],    
          'lab'                 =>  $_POST['lab'],    
          'rad'                 =>  $_POST['rad'],    
          'penunjang'           =>  $_POST['penunjang'] ,    
          'diagnosis'           =>  $_POST['diagnosis'],    
          'tata'                =>  $_POST['tata'],    
          'edukasi'             =>  $_POST['edukasi']
        ]);
      } else if($this->db('penilaian_medis_ranap')->where('no_rawat', $_POST['no_rawat'])->where('kd_dokter', $_POST['kd_dokter'])->oneArray()) {
        $this->db('penilaian_medis_ranap')
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
          'tht'                 =>  $_POST['tht'],    
          'thoraks'             =>  $_POST['thoraks'],    
          'jantung'             =>  $_POST['jantung'],    
          'paru'                =>  $_POST['paru'],    
          'abdomen'             =>  $_POST['abdomen'],    
          'genital'             =>  $_POST['genital'],    
          'ekstremitas'         =>  $_POST['ekstremitas'],    
          'kulit'               =>  $_POST['kulit'],    
          'ket_fisik'           =>  $_POST['ket_fisik'],    
          'ket_lokalis'         =>  $_POST['ket_lokalis'],    
          'lab'                 =>  $_POST['lab'],    
          'rad'                 =>  $_POST['rad'],    
          'penunjang'           =>  $_POST['penunjang'] ,    
          'diagnosis'           =>  $_POST['diagnosis'],    
          'tata'                =>  $_POST['tata'],    
          'edukasi'             =>  $_POST['edukasi']
        ]);
      } else {
        $this->db('penilaian_medis_ranap')->save([
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
          'tht'                 =>  $_POST['tht'],    
          'thoraks'             =>  $_POST['thoraks'],    
          'jantung'             =>  $_POST['jantung'],    
          'paru'                =>  $_POST['paru'],    
          'abdomen'             =>  $_POST['abdomen'],    
          'genital'             =>  $_POST['genital'],    
          'ekstremitas'         =>  $_POST['ekstremitas'],    
          'kulit'               =>  $_POST['kulit'],     
          'ket_fisik'           =>  $_POST['ket_fisik'],    
          'ket_lokalis'         =>  $_POST['ket_lokalis'],    
          'lab'                 =>  $_POST['lab'],    
          'rad'                 =>  $_POST['rad'],    
          'penunjang'           =>  $_POST['penunjang'] ,    
          'diagnosis'           =>  $_POST['diagnosis'],    
          'tata'                =>  $_POST['tata'],    
          'edukasi'             =>  $_POST['edukasi']
        ]);
      }
      exit();
    }
    
    public function getMedisRanapTampil($no_rawat)
    {
        $no_rawat = revertNorawat($no_rawat);
        $penilaian_medis_ranap = $this->db('penilaian_medis_ranap')
            ->join('dokter', 'dokter.kd_dokter=penilaian_medis_ranap.kd_dokter')
            ->where('no_rawat', $no_rawat)
            ->oneArray();
        
        if($penilaian_medis_ranap) {
            echo '<table class="table" width="100%">';
            echo '<thead><tr><th>Nomor Rawat</th><th>Keluhan</th><th>Hubungan</th><th>Dokter</th><th>Aksi</th></tr></thead>';
            echo '<tbody>';
            echo '<tr>';
            echo '<td>'.$penilaian_medis_ranap['no_rawat'].'</td>';
            echo '<td>'.substr($penilaian_medis_ranap['keluhan_utama'], 0, 50).'...</td>';
            echo '<td>'.$penilaian_medis_ranap['hubungan'].'</td>';
            echo '<td>'.$penilaian_medis_ranap['nm_dokter'].'</td>';
            echo '<td>';
            echo '<button type="button" class="btn btn-success btn-xs edit_medis_ranap" ';
            echo 'data-no_rawat="'.$penilaian_medis_ranap['no_rawat'].'" ';
            echo 'data-tanggal="'.$penilaian_medis_ranap['tanggal'].'" ';
            echo 'data-anamnesis="'.$penilaian_medis_ranap['anamnesis'].'" ';
            echo 'data-hubungan="'.$penilaian_medis_ranap['hubungan'].'" ';
            echo 'data-keluhan_utama="'.htmlspecialchars($penilaian_medis_ranap['keluhan_utama']).'" ';
            echo 'data-rps="'.htmlspecialchars($penilaian_medis_ranap['rps']).'" ';
            echo 'data-rpd="'.htmlspecialchars($penilaian_medis_ranap['rpd']).'" ';
            echo 'data-rpk="'.htmlspecialchars($penilaian_medis_ranap['rpk']).'" ';
            echo 'data-rpo="'.htmlspecialchars($penilaian_medis_ranap['rpo']).'" ';
            echo 'data-alergi="'.htmlspecialchars($penilaian_medis_ranap['alergi']).'" ';
            echo 'data-keadaan="'.$penilaian_medis_ranap['keadaan'].'" ';
            echo 'data-gcs="'.$penilaian_medis_ranap['gcs'].'" ';
            echo 'data-kesadaran="'.$penilaian_medis_ranap['kesadaran'].'" ';
            echo 'data-td="'.$penilaian_medis_ranap['td'].'" ';
            echo 'data-nadi="'.$penilaian_medis_ranap['nadi'].'" ';
            echo 'data-rr="'.$penilaian_medis_ranap['rr'].'" ';
            echo 'data-suhu="'.$penilaian_medis_ranap['suhu'].'" ';
            echo 'data-spo="'.$penilaian_medis_ranap['spo'].'" ';
            echo 'data-bb="'.$penilaian_medis_ranap['bb'].'" ';
            echo 'data-tb="'.$penilaian_medis_ranap['tb'].'" ';
            echo 'data-kepala="'.$penilaian_medis_ranap['kepala'].'" ';
            echo 'data-mata="'.$penilaian_medis_ranap['mata'].'" ';
            echo 'data-gigi="'.$penilaian_medis_ranap['gigi'].'" ';
            echo 'data-tht="'.$penilaian_medis_ranap['tht'].'" ';
            echo 'data-thoraks="'.$penilaian_medis_ranap['thoraks'].'" ';
            echo 'data-jantung="'.$penilaian_medis_ranap['jantung'].'" ';
            echo 'data-paru="'.$penilaian_medis_ranap['paru'].'" ';
            echo 'data-abdomen="'.$penilaian_medis_ranap['abdomen'].'" ';
            echo 'data-genital="'.$penilaian_medis_ranap['genital'].'" ';
            echo 'data-ekstremitas="'.$penilaian_medis_ranap['ekstremitas'].'" ';
            echo 'data-kulit="'.$penilaian_medis_ranap['kulit'].'" ';
            echo 'data-ket_fisik="'.htmlspecialchars($penilaian_medis_ranap['ket_fisik']).'" ';
            echo 'data-ket_lokalis="'.htmlspecialchars($penilaian_medis_ranap['ket_lokalis']).'" ';
            echo 'data-lab="'.htmlspecialchars($penilaian_medis_ranap['lab']).'" ';
            echo 'data-rad="'.htmlspecialchars($penilaian_medis_ranap['rad']).'" ';
            echo 'data-penunjang="'.htmlspecialchars($penilaian_medis_ranap['penunjang']).'" ';
            echo 'data-diagnosis="'.htmlspecialchars($penilaian_medis_ranap['diagnosis']).'" ';
            echo 'data-tata="'.htmlspecialchars($penilaian_medis_ranap['tata']).'" ';
            echo 'data-edukasi="'.htmlspecialchars($penilaian_medis_ranap['edukasi']).'">';
            echo '<i class="fa fa-edit"></i> Edit</button> ';
            echo '<button type="button" class="btn btn-danger btn-xs hapus_medis_ranap" ';
            echo 'data-no_rawat="'.$penilaian_medis_ranap['no_rawat'].'" ';
            echo 'data-tanggal="'.$penilaian_medis_ranap['tanggal'].'">';
            echo '<i class="fa fa-trash"></i> Hapus</button>';
            echo '</td>';
            echo '</tr>';
            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>Belum ada data assessment.</p>';
        }
        exit();
    }
    
    public function postHapusMedisRanap()
    {
        header('Content-Type: application/json');
        
        try {
            // Validate required fields
            if(empty($_POST['no_rawat']) || empty($_POST['tanggal'])) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Data tidak lengkap. Nomor rawat dan tanggal harus diisi.'
                ]);
                exit();
            }
            
            $result = $this->db('penilaian_medis_ranap')
                 ->where('no_rawat', $_POST['no_rawat'])
                 ->where('tanggal', $_POST['tanggal'])
                 ->delete();
                
            if($result) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Data assessment berhasil dihapus!'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Gagal menghapus data assessment atau data tidak ditemukan.'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
        exit();
    }

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
        
        echo $this->draw('assessment.nyeri.html', [
            'reg_periksa' => $reg_periksa,
            'pasien' => $reg_periksa,
            'dokter' => $reg_periksa,
            'petugas' => $petugas,
            'penilaian_ulang_nyeri' => $penilaian_ulang_nyeri
        ]);
        exit();
    }
    
    public function postAssessmentNyeri()
    {
        $_POST['nip'] = $this->core->getUserInfo('username', $_SESSION['mlite_user']);
        
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
        } else if($this->db('penilaian_ulang_nyeri')->where('no_rawat', $_POST['no_rawat'])->where('nip', $_POST['nip'])->oneArray()) {
            $this->db('penilaian_ulang_nyeri')
                ->where('no_rawat', $_POST['no_rawat'])
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
            echo '<button type="button" class="btn btn-success btn-xs edit_assessment_nyeri" ';
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
            echo 'data-ket_nyeri="'.htmlspecialchars($penilaian_ulang_nyeri['ket_nyeri']).'">';
            echo '<i class="fa fa-edit"></i> Edit</button> ';
            echo '<button type="button" class="btn btn-danger btn-xs hapus_assessment_nyeri" ';
            echo 'data-no_rawat="'.$penilaian_ulang_nyeri['no_rawat'].'" ';
            echo 'data-tanggal="'.$penilaian_ulang_nyeri['tanggal'].'">';
            echo '<i class="fa fa-trash"></i> Hapus</button>';
            echo '</td>';
            echo '</tr>';
            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>Belum ada data assessment nyeri.</p>';
        }
        exit();
    }
    
    public function postHapusAssessmentNyeri()
    {
        header('Content-Type: application/json');
        
        try {
            // Validate required fields
            if(empty($_POST['no_rawat']) || empty($_POST['tanggal'])) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Data tidak lengkap. Nomor rawat dan tanggal harus diisi.'
                ]);
                exit();
            }
            
            $result = $this->db('penilaian_ulang_nyeri')
                 ->where('no_rawat', $_POST['no_rawat'])
                 ->where('tanggal', $_POST['tanggal'])
                 ->delete();
                
            if($result) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Data assessment nyeri berhasil dihapus!'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Gagal menghapus data assessment nyeri atau data tidak ditemukan.'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
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
      
      // Sort berdasarkan tanggal dan jam
      usort($vital_signs, function($a, $b) {
        $datetime_a = strtotime($a['tgl_perawatan'] . ' ' . $a['jam_rawat']);
        $datetime_b = strtotime($b['tgl_perawatan'] . ' ' . $b['jam_rawat']);
        return $datetime_a - $datetime_b;
      });
      
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
    
    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $cek_pegawai = $this->db('pegawai')->where('nik', $this->core->getUserInfo('username', $_SESSION['mlite_user']))->oneArray();
        $cek_role = '';
        if($cek_pegawai) {
          $cek_role = $this->core->getPegawaiInfo('nik', $this->core->getUserInfo('username', $_SESSION['mlite_user']));
        }
        echo $this->draw(MODULES.'/dokter_ranap/js/admin/dokter_ranap.js', ['cek_role' => $cek_role]);
        exit();
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));
        $this->core->addJS('https://cdn.jsdelivr.net/npm/chart.js');
        $this->core->addJS(url([ADMIN, 'dokter_ranap', 'javascript']), 'footer');
    }

}
