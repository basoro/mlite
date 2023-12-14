<?php
namespace Plugins\Kasir_Rawat_Inap;

use Systems\AdminModule;
use Systems\Lib\QRCode;
use Systems\Lib\Fpdf\PDF_MC_Table;
use Systems\Lib\PHPMailer\PHPMailer;
use Systems\Lib\PHPMailer\SMTP;
use Systems\Lib\PHPMailer\Exception;

class Admin extends AdminModule
{

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
        $cek_vclaim = $this->db('mlite_modules')->where('dir', 'vclaim')->oneArray();
        $this->_Display($tgl_masuk, $tgl_masuk_akhir, $status_pulang, $status_periksa);
        echo $this->draw('display.html', ['rawat_inap' => $this->assign, 'cek_vclaim' => $cek_vclaim]);
        exit();
    }

    public function _Display($tgl_masuk='', $tgl_masuk_akhir='', $status_pulang='', $status_periksa='')
    {
        $this->_addHeaderFiles();

        $this->assign['kd_billing'] = 'RI.'.date('d.m.Y.H.i.s');
        $this->assign['kamar'] = $this->db('kamar')->join('bangsal', 'bangsal.kd_bangsal=kamar.kd_bangsal')->where('statusdata', '1')->toArray();
        $this->assign['dokter']         = $this->db('dokter')->where('status', '1')->toArray();
        $this->assign['penjab']       = $this->db('penjab')->where('status', '1')->toArray();
        $this->assign['no_rawat'] = '';
        $this->assign['tgl_registrasi']= date('Y-m-d');
        $this->assign['jam_reg']= date('H:i:s');

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

        if($status_pulang == '') {
          $sql .= " AND kamar_inap.stts_pulang = '-'";
        }
        if($status_pulang == 'all') {
          $sql .= " AND kamar_inap.stts_pulang = '-' AND kamar_inap.tgl_masuk BETWEEN '$tgl_masuk' AND '$tgl_masuk_akhir'";
        }
        if($status_pulang == 'masuk') {
          $sql .= " AND kamar_inap.tgl_masuk BETWEEN '$tgl_masuk' AND '$tgl_masuk_akhir'";
        }
        if($status_pulang == 'pulang') {
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
            ->oneArray();
          $row['nm_dokter'] = $dpjp_ranap['nm_dokter'];
          if(!$dpjp_ranap) {
            $row['nm_dokter'] = '---';
          }
          $this->assign['list'][] = $row;
        }

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

        $get_gudangbarang = $this->db('gudangbarang')->where('kode_brng', $_POST['kd_jenis_prw'])->where('kd_bangsal', $this->settings->get('farmasi.deporanap'))->oneArray();

        $this->db('gudangbarang')
          ->where('kode_brng', $_POST['kd_jenis_prw'])
          ->where('kd_bangsal', $this->settings->get('farmasi.deporanap'))
          ->update([
            'stok' => $get_gudangbarang['stok'] - $_POST['jml']
          ]);

        $this->db('riwayat_barang_medis')
          ->save([
            'kode_brng' => $_POST['kd_jenis_prw'],
            'stok_awal' => $get_gudangbarang['stok'],
            'masuk' => '0',
            'keluar' => $_POST['jml'],
            'stok_akhir' => $get_gudangbarang['stok'] - $_POST['jml'],
            'posisi' => 'Pemberian Obat',
            'tanggal' => $_POST['tgl_perawatan'],
            'jam' => $_POST['jam_rawat'],
            'petugas' => $this->core->getUserInfo('fullname', null, true),
            'kd_bangsal' => $this->settings->get('farmasi.deporanap'),
            'status' => 'Simpan',
            'no_batch' => $get_gudangbarang['no_batch'],
            'no_faktur' => $get_gudangbarang['no_faktur'],
            'keterangan' => $_POST['no_rawat'] . ' ' . $this->core->getRegPeriksaInfo('no_rkm_medis', $_POST['no_rawat']) . ' ' . $this->core->getPasienInfo('nm_pasien', $this->core->getRegPeriksaInfo('no_rkm_medis', $_POST['no_rawat']))
          ]);

        $this->db('detail_pemberian_obat')
          ->save([
            'tgl_perawatan' => $_POST['tgl_perawatan'],
            'jam' => $_POST['jam_rawat'],
            'no_rawat' => $_POST['no_rawat'],
            'kode_brng' => $_POST['kd_jenis_prw'],
            'h_beli' => $_POST['biaya'],
            'biaya_obat' => $_POST['biaya'],
            'jml' => $_POST['jml'],
            'embalase' => '0',
            'tuslah' => '0',
            'total' => $_POST['biaya'] * $_POST['jml'],
            'status' => 'Ranap',
            'kd_bangsal' => $this->settings->get('farmasi.deporanap'),
            'no_batch' => $get_gudangbarang['no_batch'],
            'no_faktur' => $get_gudangbarang['no_faktur']
          ]);

        $this->db('aturan_pakai')
          ->save([
            'tgl_perawatan' => $_POST['tgl_perawatan'],
            'jam' => $_POST['jam_rawat'],
            'no_rawat' => $_POST['no_rawat'],
            'kode_brng' => $_POST['kd_jenis_prw'],
            'aturan' => $_POST['aturan_pakai']
          ]);

      }
      if($_POST['kat'] == 'tambahan_biaya') {
        $this->db('tambahan_biaya')
          ->save([
            'no_rawat' => $_POST['no_rawat'],
            'nama_biaya' => $_POST['nm_perawatan'],
            'besar_biaya' => $_POST['biaya']
          ]);
      }

      if($_POST['kat'] == 'laboratorium') {
        $jns_perawatan = $this->db('jns_perawatan_lab')->where('kd_jenis_prw', $_POST['kd_jenis_prw'])->oneArray();
        $this->db('periksa_lab')
          ->save([
            'no_rawat' => $_POST['no_rawat'],
            'nip' => $_POST['kode_provider2'],
            'kd_jenis_prw' => $_POST['kd_jenis_prw'],
            'tgl_periksa' => $_POST['tgl_perawatan'],
            'jam' => $_POST['jam_rawat'],
            'dokter_perujuk' => $_POST['kode_provider'],
            'bagian_rs' => $jns_perawatan['bagian_rs'],
            'bhp' => $jns_perawatan['bhp'],
            'tarif_perujuk' => $jns_perawatan['tarif_perujuk'],
            'tarif_tindakan_dokter' => $jns_perawatan['tarif_tindakan_dokter'],
            'tarif_tindakan_petugas' => $jns_perawatan['tarif_tindakan_petugas'],
            'kso' => $jns_perawatan['kso'],
            'menejemen' => $jns_perawatan['menejemen'],
            'biaya' => $jns_perawatan['total_byr'],
            'kd_dokter' => $this->settings->get('settings.pj_laboratorium'),
            'status' => 'Ranap'
          ]);
      }

      if($_POST['kat'] == 'radiologi') {
        $jns_perawatan = $this->db('jns_perawatan_radiologi')->where('kd_jenis_prw', $_POST['kd_jenis_prw'])->oneArray();
        $this->db('periksa_radiologi')
          ->save([
            'no_rawat' => $_POST['no_rawat'],
            'nip' => $_POST['kode_provider2'],
            'kd_jenis_prw' => $_POST['kd_jenis_prw'],
            'tgl_periksa' => $_POST['tgl_perawatan'],
            'jam' => $_POST['jam_rawat'],
            'dokter_perujuk' => $_POST['kode_provider'],
            'bagian_rs' => $jns_perawatan['bagian_rs'],
            'bhp' => $jns_perawatan['bhp'],
            'tarif_perujuk' => $jns_perawatan['tarif_perujuk'],
            'tarif_tindakan_dokter' => $jns_perawatan['tarif_tindakan_dokter'],
            'tarif_tindakan_petugas' => $jns_perawatan['tarif_tindakan_petugas'],
            'kso' => $jns_perawatan['kso'],
            'menejemen' => $jns_perawatan['menejemen'],
            'biaya' => $jns_perawatan['total_byr'],
            'kd_dokter' => $this->settings->get('settings.pj_radiologi'),
            'status' => 'Ranap'
          ]);
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

    public function postHapusLaboratorium()
    {
      $this->db('periksa_lab')
      ->where('no_rawat', $_POST['no_rawat'])
      ->where('kd_jenis_prw', $_POST['kd_jenis_prw'])
      ->where('tgl_periksa', $_POST['tgl_perawatan'])
      ->where('jam', $_POST['jam_rawat'])
      ->where('status', 'Ranap')
      ->delete();
      exit();
    }

    public function postHapusRadiologi()
    {
      $this->db('periksa_radiologi')
      ->where('no_rawat', $_POST['no_rawat'])
      ->where('kd_jenis_prw', $_POST['kd_jenis_prw'])
      ->where('tgl_periksa', $_POST['tgl_perawatan'])
      ->where('jam', $_POST['jam_rawat'])
      ->where('status', 'Ranap')
      ->delete();
      exit();
    }

    public function postHapusTambahanBiaya()
    {
      $this->db('tambahan_biaya')
      ->where('no_rawat', $_POST['no_rawat'])
      ->where('nama_biaya', $_POST['nama_biaya'])
      ->delete();
      exit();
    }

    public function postHapusObat()
    {
      $get_gudangbarang = $this->db('gudangbarang')->where('kode_brng', $_POST['kode_brng'])->where('kd_bangsal', $this->settings->get('farmasi.deporanap'))->oneArray();

      $this->db('gudangbarang')
        ->where('kode_brng', $_POST['kode_brng'])
        ->where('kd_bangsal', $this->settings->get('farmasi.deporanap'))
        ->update([
          'stok' => $get_gudangbarang['stok'] + $_POST['jml']
        ]);

      $this->db('riwayat_barang_medis')
        ->save([
          'kode_brng' => $_POST['kode_brng'],
          'stok_awal' => $get_gudangbarang['stok'],
          'masuk' => $_POST['jml'],
          'keluar' => '0',
          'stok_akhir' => $get_gudangbarang['stok'] + $_POST['jml'],
          'posisi' => 'Pemberian Obat',
          'tanggal' => $_POST['tgl_peresepan'],
          'jam' => $_POST['jam_peresepan'],
          'petugas' => $this->core->getUserInfo('fullname', null, true),
          'kd_bangsal' => $this->settings->get('farmasi.deporanap'),
          'status' => 'Hapus',
          'no_batch' => $get_gudangbarang['no_batch'],
          'no_faktur' => $get_gudangbarang['no_faktur'],
          'keterangan' => $_POST['no_rawat'] . ' ' . $this->core->getRegPeriksaInfo('no_rkm_medis', $_POST['no_rawat']) . ' ' . $this->core->getPasienInfo('nm_pasien', $this->core->getRegPeriksaInfo('no_rkm_medis', $_POST['no_rawat']))
        ]);

      $this->db('detail_pemberian_obat')
        ->where('tgl_perawatan', $_POST['tgl_peresepan'])
        ->where('jam', $_POST['jam_peresepan'])
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('kode_brng', $_POST['kode_brng'])
        ->where('jml', $_POST['jml'])
        ->where('status', 'Ranap')
        ->where('kd_bangsal', $this->settings->get('farmasi.deporanap'))
        ->delete();

      exit();
    }

    public function anyRincian()
    {

      $rows_bangsal = $this->db('bangsal')
        ->join('kamar', 'kamar.kd_bangsal=bangsal.kd_bangsal')
        ->join('kamar_inap', 'kamar_inap.kd_kamar=kamar.kd_kamar')
        ->where('no_rawat', $_POST['no_rawat'])
        ->toArray();

      $bangsal = [];
      $no_bangsal = 1;
      $jumlah_total_bangsal = 0;
      foreach ($rows_bangsal as $row) {
        $row['nomor'] = $no_bangsal++;
        $jumlah_total_bangsal += $row['ttl_biaya'];
        $bangsal[] = $row;
      }

      $rows_rawat_inap_dr = $this->db('rawat_inap_dr')->where('no_rawat', $_POST['no_rawat'])->toArray();
      $rows_rawat_inap_pr = $this->db('rawat_inap_pr')->where('no_rawat', $_POST['no_rawat'])->toArray();
      $rows_rawat_inap_drpr = $this->db('rawat_inap_drpr')->where('no_rawat', $_POST['no_rawat'])->toArray();

      $jumlah_total = 0;
      $rawat_inap_dr = [];
      $rawat_inap_pr = [];
      $rawat_inap_drpr = [];
      $no_tindakan = 1;

      if($rows_rawat_inap_dr) {
        foreach ($rows_rawat_inap_dr as $row) {
          $jns_perawatan = $this->db('jns_perawatan_inap')->where('kd_jenis_prw', $row['kd_jenis_prw'])->oneArray();
          $row['nm_perawatan'] = $jns_perawatan['nm_perawatan'];
          $jumlah_total = $jumlah_total + $row['biaya_rawat'];
          $row['provider'] = 'Dokter';
          $rawat_inap_dr[] = $row;
        }
      }

      if($rows_rawat_inap_pr) {
        foreach ($rows_rawat_inap_pr as $row) {
          $jns_perawatan = $this->db('jns_perawatan_inap')->where('kd_jenis_prw', $row['kd_jenis_prw'])->oneArray();
          $row['nm_perawatan'] = $jns_perawatan['nm_perawatan'];
          $jumlah_total = $jumlah_total + $row['biaya_rawat'];
          $row['provider'] = 'Perawat';
          $rawat_inap_pr[] = $row;
        }
      }

      if($rows_rawat_inap_drpr) {
        foreach ($rows_rawat_inap_drpr as $row) {
          $jns_perawatan = $this->db('jns_perawatan_inap')->where('kd_jenis_prw', $row['kd_jenis_prw'])->oneArray();
          $row['nm_perawatan'] = $jns_perawatan['nm_perawatan'];
          $jumlah_total = $jumlah_total + $row['biaya_rawat'];
          $row['provider'] = 'Dokter & Perawat';
          $rawat_inap_drpr[] = $row;
        }
      }

      $merge_tindakan = array_merge($rawat_inap_dr, $rawat_inap_pr, $rawat_inap_drpr);
      $tindakan = [];
      foreach ($merge_tindakan as $row) {
        $row['nomor'] = $no_tindakan++;
        $tindakan[] = $row;
      }

      $rows_pemberian_obat = $this->db('detail_pemberian_obat')
      ->join('databarang', 'databarang.kode_brng=detail_pemberian_obat.kode_brng')
      ->where('no_rawat', $_POST['no_rawat'])
      ->where('detail_pemberian_obat.status', 'Ranap')
      ->toArray();

      $detail_pemberian_obat = [];
      $jumlah_total_obat = 0;
      $no_obat = 1;
      foreach ($rows_pemberian_obat as $row) {
        $row['nomor'] = $no_obat++;
        $jumlah_total_obat += floatval($row['total']);
        $detail_pemberian_obat[] = $row;
      }

      $rows_periksa_lab = $this->db('periksa_lab')
      ->join('jns_perawatan_lab', 'jns_perawatan_lab.kd_jenis_prw=periksa_lab.kd_jenis_prw')
      ->where('no_rawat', $_POST['no_rawat'])
      ->where('periksa_lab.status', 'Ranap')
      ->toArray();

      $periksa_lab = [];
      $jumlah_total_lab = 0;
      $no_lab = 1;
      foreach ($rows_periksa_lab as $row) {
        $jumlah_total_lab += $row['biaya'];
        $row['nomor'] = $no_lab++;
        $periksa_lab[] = $row;
      }

      $rows_periksa_radiologi = $this->db('periksa_radiologi')
      ->join('jns_perawatan_radiologi', 'jns_perawatan_radiologi.kd_jenis_prw=periksa_radiologi.kd_jenis_prw')
      ->where('no_rawat', $_POST['no_rawat'])
      ->where('periksa_radiologi.status', 'Ranap')
      ->toArray();

      $periksa_radiologi = [];
      $jumlah_total_radiologi = 0;
      $no_rad = 1;
      foreach ($rows_periksa_radiologi as $row) {
        $jumlah_total_radiologi += $row['biaya'];
        $row['nomor'] = $no_rad++;
        $periksa_radiologi[] = $row;
      }

      $jumlah_total_operasi = 0;
      $operasis = $this->db('operasi')->where('no_rawat', $_POST['no_rawat'])->where('status', 'Ranap')->toArray();
      foreach ($operasis as $operasi) {
        $operasi['jumlah'] = $operasi['biayaoperator1']+$operasi['biayaoperator2']+$operasi['biayaoperator3']+$operasi['biayaasisten_operator1']+$operasi['biayaasisten_operator2']+$operasi['biayadokter_anak']+$operasi['biayaperawaat_resusitas']+$operasi['biayadokter_anestesi']+$operasi['biayaasisten_anestesi']+$operasi['biayabidan']+$operasi['biayaperawat_luar'];
        $jumlah_total_operasi += $operasi['jumlah'];
      }
      $jumlah_total_obat_operasi = 0;
      $obat_operasis = $this->db('beri_obat_operasi')->where('no_rawat', $_POST['no_rawat'])->toArray();
      foreach ($obat_operasis as $obat_operasi) {
        $obat_operasi['harga'] = $obat_operasi['hargasatuan'] * $obat_operasi['jumlah'];
        $jumlah_total_obat_operasi += $obat_operasi['harga'];
      }

      $rows_tambahan_biaya = $this->db('tambahan_biaya')
        ->where('no_rawat', $_POST['no_rawat'])
        ->toArray();
      $tambahan_biaya = [];
      $no_tambahan_biaya = 1;
      $jumlah_total_tambahan = 0;
      foreach ($rows_tambahan_biaya as $row) {
        $row['nomor'] = $no_tambahan_biaya++;
        $jumlah_total_tambahan += $row['besar_biaya'];
        $tambahan_biaya[] = $row;
      }

      echo $this->draw('rincian.html', [
        'rawat_inap_dr' => $rawat_inap_dr,
        'rawat_inap_pr' => $rawat_inap_pr,
        'rawat_inap_drpr' => $rawat_inap_drpr,
        'tindakan' => $tindakan,
        'jumlah_total' => $jumlah_total,
        'jumlah_total_obat' => $jumlah_total_obat,
        'bangsal' => $bangsal,
        'jumlah_total_bangsal' => $jumlah_total_bangsal,
        'detail_pemberian_obat' => $detail_pemberian_obat,
        'periksa_lab' => $periksa_lab,
        'jumlah_total_lab' => $jumlah_total_lab,
        'periksa_radiologi' => $periksa_radiologi,
        'jumlah_total_radiologi' => $jumlah_total_radiologi,
        'tambahan_biaya' => $tambahan_biaya,
        'jumlah_total_tambahan' => $jumlah_total_tambahan,
        'jumlah_total_operasi' => $jumlah_total_operasi,
        'jumlah_total_obat_operasi' => $jumlah_total_obat_operasi,
        'no_rawat' => $_POST['no_rawat']
      ]);
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
        ->where('kd_bangsal', $this->settings->get('farmasi.deporanap'))
        ->where('status', '1')
        ->like('databarang.nama_brng', '%'.$_POST['obat'].'%')
        ->limit(10)
        ->toArray();
      echo $this->draw('obat.html', ['obat' => $obat]);
      exit();
    }

    public function anyTambahanBiaya()
    {
      $tambahan_biaya = $this->db('tambahan_biaya')
        ->like('nama_biaya', '%'.$_POST['tambahan_biaya'].'%')
        ->limit(10)
        ->toArray();
      echo $this->draw('tambahan_biaya.html', ['tambahan_biaya' => $tambahan_biaya]);
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

    public function postSave()
    {
      $_POST['id_user']	= $this->core->getUserInfo('id');
      $query = $this->db('mlite_billing')->save($_POST);
      if($query) {
        $this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->update(['status_bayar' => 'Sudah Bayar']);
      }
      exit();
    }

    public function anyFaktur()
    {
      $settings = $this->settings('settings');
      $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));
      $show = isset($_GET['show']) ? $_GET['show'] : "";
      switch($show){
       default:
        if($this->db('mlite_billing')->where('no_rawat', $_POST['no_rawat'])->like('kd_billing', 'RI%')->oneArray()) {
          echo 'OK';
        }
        break;
        case "besar":
        $result = $this->db('mlite_billing')->where('no_rawat', $_GET['no_rawat'])->like('kd_billing', 'RI%')->desc('id_billing')->oneArray();

        $result_detail['kamar_inap'] = $this->db('kamar_inap')
          ->join('reg_periksa', 'reg_periksa.no_rawat = kamar_inap.no_rawat')
          ->where('reg_periksa.no_rawat', $_GET['no_rawat'])
          ->oneArray();

        $result_detail['rawat_inap_dr'] = $this->db('rawat_inap_dr')
          ->select('jns_perawatan_inap.nm_perawatan')
          ->select(['biaya_rawat' => 'rawat_inap_dr.biaya_rawat'])
          ->select(['jml' => 'COUNT(rawat_inap_dr.kd_jenis_prw)'])
          ->select(['total_biaya_rawat_dr' => 'SUM(rawat_inap_dr.biaya_rawat)'])
          ->join('jns_perawatan_inap', 'jns_perawatan_inap.kd_jenis_prw = rawat_inap_dr.kd_jenis_prw')
          ->where('rawat_inap_dr.no_rawat', $_GET['no_rawat'])
          ->group('jns_perawatan_inap.nm_perawatan')
          ->toArray();

        $result_detail['rawat_inap_pr'] = $this->db('rawat_inap_pr')
          ->select('jns_perawatan_inap.nm_perawatan')
          ->select(['biaya_rawat' => 'rawat_inap_pr.biaya_rawat'])
          ->select(['jml' => 'COUNT(rawat_inap_pr.kd_jenis_prw)'])
          ->select(['total_biaya_rawat_pr' => 'SUM(rawat_inap_pr.biaya_rawat)'])
          ->join('jns_perawatan_inap', 'jns_perawatan_inap.kd_jenis_prw = rawat_inap_pr.kd_jenis_prw')
          ->where('rawat_inap_pr.no_rawat', $_GET['no_rawat'])
          ->group('jns_perawatan_inap.nm_perawatan')
          ->toArray();

        $result_detail['rawat_inap_drpr'] = $this->db('rawat_inap_drpr')
          ->select('jns_perawatan_inap.nm_perawatan')
          ->select(['biaya_rawat' => 'rawat_inap_drpr.biaya_rawat'])
          ->select(['jml' => 'COUNT(rawat_inap_drpr.kd_jenis_prw)'])
          ->select(['total_biaya_rawat_drpr' => 'SUM(rawat_inap_drpr.biaya_rawat)'])
          ->join('jns_perawatan_inap', 'jns_perawatan_inap.kd_jenis_prw = rawat_inap_drpr.kd_jenis_prw')
          ->where('rawat_inap_drpr.no_rawat', $_GET['no_rawat'])
          ->group('jns_perawatan_inap.nm_perawatan')
          ->toArray();

        $result_detail['detail_pemberian_obat'] = $this->db('detail_pemberian_obat')
          ->join('databarang', 'databarang.kode_brng=detail_pemberian_obat.kode_brng')
          ->where('no_rawat', $_GET['no_rawat'])
          ->where('detail_pemberian_obat.status', 'Ranap')
          ->toArray();

        $result_detail['periksa_lab'] = $this->db('periksa_lab')
          ->join('jns_perawatan_lab', 'jns_perawatan_lab.kd_jenis_prw=periksa_lab.kd_jenis_prw')
          ->where('no_rawat', $_GET['no_rawat'])
          ->where('periksa_lab.status', 'Ranap')
          ->toArray();

        $result_detail['periksa_radiologi'] = $this->db('periksa_radiologi')
          ->join('jns_perawatan_radiologi', 'jns_perawatan_radiologi.kd_jenis_prw=periksa_radiologi.kd_jenis_prw')
          ->where('no_rawat', $_GET['no_rawat'])
          ->where('periksa_radiologi.status', 'Ranap')
          ->toArray();

        $result_detail['tambahan_biaya'] = $this->db('tambahan_biaya')
          ->where('no_rawat', $_GET['no_rawat'])
          ->toArray();

        $jumlah_total_operasi = 0;
        $operasis = $this->db('operasi')->join('paket_operasi', 'paket_operasi.kode_paket=operasi.kode_paket')->where('no_rawat', $_GET['no_rawat'])->where('operasi.status', 'Ranap')->toArray();
        $result_detail['operasi'] = [];
        foreach ($operasis as $operasi) {
          $operasi['jumlah'] = $operasi['biayaoperator1']+$operasi['biayaoperator2']+$operasi['biayaoperator3']+$operasi['biayaasisten_operator1']+$operasi['biayaasisten_operator2']+$operasi['biayadokter_anak']+$operasi['biayaperawaat_resusitas']+$operasi['biayadokter_anestesi']+$operasi['biayaasisten_anestesi']+$operasi['biayabidan']+$operasi['biayaperawat_luar'];
          $jumlah_total_operasi += $operasi['jumlah'];
          $result_detail['operasi'][] = $operasi;
        }
        $jumlah_total_obat_operasi = 0;
        $obat_operasis = $this->db('beri_obat_operasi')->join('obatbhp_ok', 'obatbhp_ok.kd_obat=beri_obat_operasi.kd_obat')->where('no_rawat', $_GET['no_rawat'])->toArray();
        $result_detail['obat_operasi'] = [];
        foreach ($obat_operasis as $obat_operasi) {
          $obat_operasi['harga'] = $obat_operasi['hargasatuan'] * $obat_operasi['jumlah'];
          $jumlah_total_obat_operasi += $obat_operasi['harga'];
          $result_detail['obat_operasi'][] = $obat_operasi;
        }

        $reg_periksa = $this->db('reg_periksa')->where('no_rawat', $_GET['no_rawat'])->oneArray();
        $pasien = $this->db('pasien')->where('no_rkm_medis', $reg_periksa['no_rkm_medis'])->oneArray();

        /* Print as pdf */
        $pdf = new PDF_MC_Table('P','mm','A4');
        $pdf->AddPage();

        $pdf->Image('../'.$settings['logo'], 10, 10, '18', '18', 'png');

        //set font to arial, bold, 14pt
        $pdf->SetFont('Arial','B',14);

        //Cell(width , height , text , border , end line , [align] )

        $pdf->Cell(20 ,5,'',0,0);
        $pdf->Cell(100 ,5,$settings['nama_instansi'],0,0);
        $pdf->Cell(69 ,5,'INVOICE',0,1);//end of line

        //set font to arial, regular, 12pt
        $pdf->SetFont('Arial','',12);

        $pdf->Cell(20 ,5,'',0,0);
        $pdf->Cell(100 ,5,$settings['alamat'],0,0);
        $pdf->Cell(69 ,5,'',0,1);//end of line

        $pdf->Cell(20 ,5,'',0,0);
        $pdf->Cell(100 ,5,$settings['kota'].' - '.$settings['propinsi'],0,0);
        $pdf->Cell(25 ,5,'Tanggal',0,0);
        $pdf->Cell(44 ,5,': '.$result['tgl_billing'],0,1);//end of line

        $pdf->Cell(20 ,5,'',0,0);
        $pdf->Cell(100 ,5,$settings['nomor_telepon'],0,0);
        $pdf->Cell(25 ,5,'Faktur',0,0);
        $pdf->Cell(44 ,5,': '.$result['kd_billing'],0,1);//end of line

        $pdf->Cell(20 ,5,'',0,0);
        $pdf->Cell(100 ,5,$settings['email'],0,0);
        $pdf->Cell(25 ,5,'Nomor RM',0,0);
        $pdf->Cell(44 ,5,': '.$pasien['no_rkm_medis'],0,1);//end of line

        //make a dummy empty cell as a vertical spacer
        $pdf->Cell(189 ,10,'',0,1);//end of line

        //billing address
        $pdf->Cell(20 ,5,'Kepada :',0,0);//end of line
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(90 ,5,$pasien['nm_pasien'],0,1);

        $pdf->Cell(20 ,5,'',0,0);
        $pdf->Cell(90 ,5,$pasien['alamat'],0,1);

        $pdf->Cell(20 ,5,'',0,0);
        $pdf->Cell(90 ,5,$pasien['no_tlp'],0,1);

        //make a dummy empty cell as a vertical spacer
        $pdf->Cell(189 ,10,'',0,1);//end of line

        //invoice contents
        $pdf->SetFont('Arial','B',12);

        $pdf->Cell(10 ,7,'No',1,0);
        $pdf->Cell(110 ,7,'Item',1,0);
        $pdf->Cell(25 ,7,'Jumlah',1,0);
        $pdf->Cell(44 ,7,'Total',1,1);//end of line

        $pdf->SetFont('Arial','',11);

        //Numbers are right-aligned so we give 'R' after new line parameter

        $pdf->Cell(10 ,5,'1',1,0);
        $pdf->Cell(110 ,5,'Biaya Kamar',1,0);
        $pdf->Cell(25 ,5,'1',1,0, 'C');
        $pdf->Cell(44 ,5,number_format($result_detail['kamar_inap']['ttl_biaya'],2,',','.'),1,1,'R');//end of line

        $pdf->Cell(10 ,5,'2',1,0);
        $pdf->Cell(110 ,5,'Biaya Obat & BHP',1,0);
        $pdf->Cell(25 ,5,count($result_detail['detail_pemberian_obat']),1,0, 'C');
        $pdf->Cell(44 ,5,number_format(array_sum(array_column($result_detail['detail_pemberian_obat'], 'total')),2,',','.'),1,1,'R');//end of line

        $pdf->Cell(10 ,5,'3',1,0);
        $pdf->Cell(110 ,5,'Jasa Dokter',1,0);
        $pdf->Cell(25 ,5,count($result_detail['rawat_inap_dr']),1,0, 'C');
        $pdf->Cell(44 ,5,number_format(array_sum(array_column($result_detail['rawat_inap_dr'], 'total_biaya_rawat_dr')),2,',','.'),1,1,'R');//end of line

        $pdf->Cell(10 ,5,'4',1,0);
        $pdf->Cell(110 ,5,'Jasa Perawat',1,0);
        $pdf->Cell(25 ,5,count($result_detail['rawat_inap_pr']),1,0, 'C');
        $pdf->Cell(44 ,5,number_format(array_sum(array_column($result_detail['rawat_inap_pr'], 'total_biaya_rawat_pr')),2,',','.'),1,1,'R');//end of line

        $pdf->Cell(10 ,5,'5',1,0);
        $pdf->Cell(110 ,5,'Jasa Dokter & Perawat',1,0);
        $pdf->Cell(25 ,5,count($result_detail['rawat_inap_drpr']),1,0, 'C');
        $pdf->Cell(44 ,5,number_format(array_sum(array_column($result_detail['rawat_inap_drpr'], 'total_biaya_rawat_drpr')),2,',','.'),1,1,'R');//end of line

        $pdf->Cell(10 ,5,'6',1,0);
        $pdf->Cell(110 ,5,'Jasa Laboratorium',1,0);
        $pdf->Cell(25 ,5,count($result_detail['periksa_lab']),1,0, 'C');
        $pdf->Cell(44 ,5,number_format(array_sum(array_column($result_detail['periksa_lab'], 'biaya')),2,',','.'),1,1,'R');//end of line

        $pdf->Cell(10 ,5,'7',1,0);
        $pdf->Cell(110 ,5,'Jasa Radiologi',1,0);
        $pdf->Cell(25 ,5,count($result_detail['periksa_radiologi']),1,0, 'C');
        $pdf->Cell(44 ,5,number_format(array_sum(array_column($result_detail['periksa_radiologi'], 'biaya')),2,',','.'),1,1,'R');//end of line

        $pdf->Cell(10 ,5,'8',1,0);
        $pdf->Cell(110 ,5,'Jasa Operasi',1,0);
        $pdf->Cell(25 ,5,count($result_detail['operasi']),1,0, 'C');
        $pdf->Cell(44 ,5,number_format(array_sum(array_column($result_detail['operasi'], 'jumlah')),2,',','.'),1,1,'R');//end of line

        $pdf->Cell(10 ,5,'9',1,0);
        $pdf->Cell(110 ,5,'Obat dan BHP Operasi',1,0);
        $pdf->Cell(25 ,5,count($result_detail['obat_operasi']),1,0, 'C');
        $pdf->Cell(44 ,5,number_format(array_sum(array_column($result_detail['obat_operasi'], 'harga')),2,',','.'),1,1,'R');//end of line

        $pdf->Cell(10 ,5,'10',1,0);
        $pdf->Cell(110 ,5,'Biaya Tambahan',1,0);
        $pdf->Cell(25 ,5,count($result_detail['tambahan_biaya']),1,0, 'C');
        $pdf->Cell(44 ,5,number_format(array_sum(array_column($result_detail['tambahan_biaya'], 'besar_biaya')),2,',','.'),1,1,'R');//end of line

        $pdf->SetFont('Arial','B',14);

        //summary
        /*$pdf->Cell(120 ,5,'',0,0);
        $pdf->Cell(25 ,5,'Subtotal',0,0);
        $pdf->Cell(44 ,5,'4,450',1,1,'R');//end of line

        $pdf->Cell(120 ,5,'',0,0);
        $pdf->Cell(25 ,5,'Taxable',0,0);
        $pdf->Cell(44 ,5,'0',1,1,'R');//end of line

        $pdf->Cell(120 ,5,'',0,0);
        $pdf->Cell(25 ,5,'Tax Rate',0,0);
        $pdf->Cell(44 ,5,'10%',1,1,'R');//end of line*/

        $pdf->Cell(120 ,15,'',0,0);
        $pdf->Cell(25 ,15,'Total',0,0);
        $pdf->Cell(44 ,15,'Rp. '.number_format(
          $result_detail['kamar_inap']['ttl_biaya']+
          array_sum(array_column($result_detail['detail_pemberian_obat'], 'total'))+
          array_sum(array_column($result_detail['rawat_inap_dr'], 'total_biaya_rawat_dr'))+
          array_sum(array_column($result_detail['rawat_inap_pr'], 'total_biaya_rawat_pr'))+
          array_sum(array_column($result_detail['rawat_inap_drpr'], 'total_biaya_rawat_drpr'))+
          array_sum(array_column($result_detail['periksa_lab'], 'biaya'))+
          array_sum(array_column($result_detail['periksa_radiologi'], 'biaya'))+
          array_sum(array_column($result_detail['operasi'], 'jumlah'))+
          array_sum(array_column($result_detail['obat_operasi'], 'harga'))+
          array_sum(array_column($result_detail['tambahan_biaya'], 'besar_biaya')),
          2,',','.'),0,0,'R');//end of line

        $pdf->Cell(189 ,20,'',0,1);//end of line

        $pdf->SetFont('Arial','',11);

        $pdf->Cell(120 ,5,'',0,0);
        $pdf->Cell(69 ,10,$settings['kota'].', '.date('Y-m-d'),0,1);//end of line

        $qr=QRCode::getMinimumQRCode($this->core->getUserInfo('fullname', null, true),QR_ERROR_CORRECT_LEVEL_L);
        //$qr=QRCode::getMinimumQRCode('Petugas: '.$this->core->getUserInfo('fullname', null, true).'; Lokasi: '.UPLOADS.'/invoices/'.$result['kd_billing'].'.pdf',QR_ERROR_CORRECT_LEVEL_L);
        $im=$qr->createImage(4,4);
        imagepng($im,BASE_DIR.'/'.ADMIN.'/tmp/qrcode.png');
        imagedestroy($im);

        $image = BASE_DIR."/".ADMIN."/tmp/qrcode.png";
        $qrCode = "../../".ADMIN."/tmp/qrcode.png";

        $pdf->Cell(120 ,5,'',0,0);
        $pdf->Cell(64, 5, $pdf->Image($image, $pdf->GetX(), $pdf->GetY(),30,30,'png'), 0, 0, 'C', false );
        $pdf->Cell(189 ,32,'',0,1);//end of line
        $pdf->Cell(120 ,5,'',0,0);
        $pdf->Cell(69 ,5,$this->core->getUserInfo('fullname', null, true),0,1);//end of line

        if (file_exists(UPLOADS.'/invoices/'.$result['kd_billing'].'.pdf')) {
          unlink(UPLOADS.'/invoices/'.$result['kd_billing'].'.pdf');
        }

        $pdf->Output('F', UPLOADS.'/invoices/'.$result['kd_billing'].'.pdf', true);
        //$pdf->Output();        
        echo $this->draw('billing.besar.html', ['billing' => $result, 'billing_besar_detail' => $result_detail, 'jumlah_total_operasi' => $jumlah_total_operasi, 'pasien' => $pasien, 'qrCode' => $qrCode, 'fullname' => $this->core->getUserInfo('fullname', null, true)]);
        break;
        case "kecil":
        $result = $this->db('mlite_billing')->where('no_rawat', $_GET['no_rawat'])->like('kd_billing', 'RI%')->desc('id_billing')->oneArray();
        $reg_periksa = $this->db('reg_periksa')->where('no_rawat', $_GET['no_rawat'])->oneArray();
        $pasien = $this->db('pasien')->where('no_rkm_medis', $reg_periksa['no_rkm_medis'])->oneArray();
        echo $this->draw('billing.kecil.html', ['billing' => $result, 'pasien' => $pasien, 'fullname' => $this->core->getUserInfo('fullname', null, true)]);
        break;
      }
      exit();
    }

    public function postKirimEmail() {
      $email = $_POST['email'];
      $nama_lengkap = $_POST['receiver'];
      $file = $_POST['file'];
      $this->sendEmail($email, $nama_lengkap, $file);
      exit();
    }

    private function sendEmail($email, $receiver, $file)
    {
      $binary_content = file_get_contents($file);

      if ($binary_content === false) {
         throw new Exception("Could not fetch remote content from: '$file'");
      }

	    $mail = new PHPMailer(true);
      $temp  = @file_get_contents(MODULES."/kasir_rawat_inap/email/email.send.html");

      $temp  = str_replace("{SITENAME}", $this->core->settings->get('settings.nama_instansi'), $temp);
      $temp  = str_replace("{ADDRESS}", $this->core->settings->get('settings.alamat')." - ".$this->core->settings->get('settings.kota'), $temp);
      $temp  = str_replace("{TELP}", $this->core->settings->get('settings.nomor_telepon'), $temp);
      //$temp  = str_replace("{NUMBER}", $number, $temp);

	    //$mail->SMTPDebug = SMTP::DEBUG_SERVER; // for detailed debug output
      $mail->isSMTP();
      $mail->Host = $this->settings->get('api.apam_smtp_host');
      $mail->SMTPAuth = true;
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port = $this->settings->get('api.apam_smtp_port');

      $mail->Username = $this->settings->get('api.apam_smtp_username');
      $mail->Password = $this->settings->get('api.apam_smtp_password');

      // Sender and recipient settings
      $mail->setFrom($this->core->settings->get('settings.email'), $this->core->settings->get('settings.nama_instansi'));
      $mail->addAddress($email, $receiver);
      $mail->AddStringAttachment($binary_content, "invoice.pdf", $encoding = 'base64', $type = 'application/pdf');

      // Setting the email content
      $mail->IsHTML(true);
      $mail->Subject = "Detail pembayaran anda di ".$this->core->settings->get('settings.nama_instansi');
      $mail->Body = $temp;

      $mail->send();
    }

    public function postCekWaktu()
    {
      echo date('H:i:s');
      exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/kasir_rawat_inap/js/admin/kasir_rawat_inap.js');
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
        $this->core->addJS(url([ADMIN, 'kasir_rawat_inap', 'javascript']), 'footer');
    }

}
