<?php
namespace Plugins\Kasir_Rawat_Jalan;

use Systems\AdminModule;
//use Systems\Lib\Fpdf\FPDF;
use Systems\Lib\Fpdf\PDF_MC_Table;
use Systems\Lib\QRCode;
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
        $tgl_kunjungan = date('Y-m-d');
        $tgl_kunjungan_akhir = date('Y-m-d');
        $status_periksa = '';
        $status_bayar = '';

        if(isset($_POST['periode_rawat_jalan'])) {
          $tgl_kunjungan = $_POST['periode_rawat_jalan'];
        }
        if(isset($_POST['periode_rawat_jalan_akhir'])) {
          $tgl_kunjungan_akhir = $_POST['periode_rawat_jalan_akhir'];
        }
        if(isset($_POST['status_periksa'])) {
          $status_periksa = $_POST['status_periksa'];
        }
        if(isset($_POST['status_bayar'])) {
          $status_bayar = $_POST['status_bayar'];
        }
        $cek_vclaim = $this->db('mlite_modules')->where('dir', 'vclaim')->oneArray();
        $cek_laboratorium = $this->db('mlite_modules')->where('dir', 'laboratorium')->oneArray();
        $cek_radiologi = $this->db('mlite_modules')->where('dir', 'radiologi')->oneArray();
        $this->_Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa, $status_bayar);
        return $this->draw('manage.html', ['rawat_jalan' => $this->assign, 'cek_vclaim' => $cek_vclaim, 'cek_laboratorium' => $cek_laboratorium, 'cek_radiologi' => $cek_radiologi]);
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
        $cek_laboratorium = $this->db('mlite_modules')->where('dir', 'laboratorium')->oneArray();
        $cek_radiologi = $this->db('mlite_modules')->where('dir', 'radiologi')->oneArray();
        $this->_Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa);
        echo $this->draw('display.html', ['rawat_jalan' => $this->assign, 'cek_vclaim' => $cek_vclaim, 'cek_laboratorium' => $cek_laboratorium, 'cek_radiologi' => $cek_radiologi]);
        exit();
    }

    public function _Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa='')
    {
        $this->_addHeaderFiles();

        $this->assign['kd_billing'] = 'RJ.'.date('d.m.Y.H.i.s');
        $this->assign['poliklinik']     = $this->db('poliklinik')->where('status', '1')->toArray();
        $this->assign['dokter']         = $this->db('dokter')->where('status', '1')->toArray();
        $this->assign['penjab']       = $this->db('penjab')->toArray();
        $this->assign['no_rawat'] = '';
        $this->assign['no_reg']     = '';
        $this->assign['tgl_registrasi']= date('Y-m-d');
        $this->assign['jam_reg']= date('H:i:s');
        $this->assign['input_kasir'] = $this->settings('settings', 'input_kasir');

        $sql = "SELECT reg_periksa.*,
            pasien.*,
            dokter.*,
            poliklinik.*,
            penjab.*
          FROM reg_periksa, pasien, dokter, poliklinik, penjab
          WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis
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
          $get_billing = $this->db('mlite_billing')->where('no_rawat', $row['no_rawat'])->like('kd_billing', 'RJ%')->oneArray();
          if(empty($get_faktur)) {
            $row['kd_billing'] = 'RJ.'.date('d.m.Y.H.i.s');
            $row['tgl_billing'] = date('Y-m-d H:i');
          }
          $this->assign['list'][] = $row;
        }

    }

    public function postSaveDetail()
    {
      if($_POST['kat'] == 'tindakan') {
        $jns_perawatan = $this->db('jns_perawatan')->where('kd_jenis_prw', $_POST['kd_jenis_prw'])->oneArray();
        if($_POST['provider'] == 'rawat_jl_dr') {
          $this->db('rawat_jl_dr')->save([
            'no_rawat' => $_POST['no_rawat'],
            'kd_jenis_prw' => $_POST['kd_jenis_prw'],
            'kd_dokter' => $_POST['kode_provider'],
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
        if($_POST['provider'] == 'rawat_jl_pr') {
          $this->db('rawat_jl_pr')->save([
            'no_rawat' => $_POST['no_rawat'],
            'kd_jenis_prw' => $_POST['kd_jenis_prw'],
            'nip' => $_POST['kode_provider2'],
            'tgl_perawatan' => $_POST['tgl_perawatan'],
            'jam_rawat' => $_POST['jam_rawat'],
            'material' => $jns_perawatan['material'],
            'bhp' => $jns_perawatan['bhp'],
            'tarif_tindakanpr' => $jns_perawatan['tarif_tindakanpr'],
            'kso' => $jns_perawatan['kso'],
            'menejemen' => $jns_perawatan['menejemen'],
            'biaya_rawat' => $jns_perawatan['total_byrdr'],
            'stts_bayar' => 'Belum'
          ]);
        }
        if($_POST['provider'] == 'rawat_jl_drpr') {
          $this->db('rawat_jl_drpr')->save([
            'no_rawat' => $_POST['no_rawat'],
            'kd_jenis_prw' => $_POST['kd_jenis_prw'],
            'kd_dokter' => $_POST['kode_provider'],
            'nip' => $_POST['kode_provider2'],
            'tgl_perawatan' => $_POST['tgl_perawatan'],
            'jam_rawat' => $_POST['jam_rawat'],
            'material' => $jns_perawatan['material'],
            'bhp' => $jns_perawatan['bhp'],
            'tarif_tindakandr' => $jns_perawatan['tarif_tindakandr'],
            'tarif_tindakanpr' => $jns_perawatan['tarif_tindakanpr'],
            'kso' => $jns_perawatan['kso'],
            'menejemen' => $jns_perawatan['menejemen'],
            'biaya_rawat' => $jns_perawatan['total_byrdr'],
            'stts_bayar' => 'Belum'
          ]);
        }
      }
      if($_POST['kat'] == 'obat') {

        $get_gudangbarang = $this->db('gudangbarang')->where('kode_brng', $_POST['kd_jenis_prw'])->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))->oneArray();

        $this->db('gudangbarang')
          ->where('kode_brng', $_POST['kd_jenis_prw'])
          ->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))
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
            'kd_bangsal' => $this->settings->get('farmasi.deporalan'),
            'status' => 'Simpan',
            'no_batch' => $get_gudangbarang['no_batch'],
            'no_faktur' => $get_gudangbarang['no_faktur']
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
            'status' => 'Ralan',
            'kd_bangsal' => $this->settings->get('farmasi.deporalan'),
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
            'status' => 'Ralan'
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
            'status' => 'Ralan'
          ]);
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

    public function postHapusLaboratorium()
    {
      $this->db('periksa_lab')
      ->where('no_rawat', $_POST['no_rawat'])
      ->where('kd_jenis_prw', $_POST['kd_jenis_prw'])
      ->where('tgl_periksa', $_POST['tgl_perawatan'])
      ->where('jam', $_POST['jam_rawat'])
      ->where('status', 'Ralan')
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
      ->where('status', 'Ralan')
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
      $get_gudangbarang = $this->db('gudangbarang')->where('kode_brng', $_POST['kode_brng'])->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))->oneArray();

      $this->db('gudangbarang')
        ->where('kode_brng', $_POST['kode_brng'])
        ->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))
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
          'kd_bangsal' => $this->settings->get('farmasi.deporalan'),
          'status' => 'Hapus',
          'no_batch' => $get_gudangbarang['no_batch'],
          'no_faktur' => $get_gudangbarang['no_faktur']
        ]);

      $this->db('detail_pemberian_obat')
        ->where('tgl_perawatan', $_POST['tgl_peresepan'])
        ->where('jam', $_POST['jam_peresepan'])
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('kode_brng', $_POST['kode_brng'])
        ->where('jml', $_POST['jml'])
        ->where('status', 'Ralan')
        ->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))
        ->delete();

      exit();
    }

    public function anyRincian()
    {

      $cek_laboratorium = $this->db('mlite_modules')->where('dir', 'laboratorium')->oneArray();
      $cek_radiologi = $this->db('mlite_modules')->where('dir', 'radiologi')->oneArray();

      $poliklinik = $this->db('poliklinik')
        ->join('reg_periksa', 'reg_periksa.kd_poli=poliklinik.kd_poli')
        ->where('no_rawat', $_POST['no_rawat'])
        ->oneArray();

      $rows_rawat_jl_dr = $this->db('rawat_jl_dr')->where('no_rawat', $_POST['no_rawat'])->toArray();
      $rows_rawat_jl_pr = $this->db('rawat_jl_pr')->where('no_rawat', $_POST['no_rawat'])->toArray();
      $rows_rawat_jl_drpr = $this->db('rawat_jl_drpr')->where('no_rawat', $_POST['no_rawat'])->toArray();

      $jumlah_total = 0;
      $rawat_jl_dr = [];
      $rawat_jl_pr = [];
      $rawat_jl_drpr = [];
      $no_tindakan = 1;

      if($rows_rawat_jl_dr) {
        foreach ($rows_rawat_jl_dr as $row) {
          $jns_perawatan = $this->db('jns_perawatan')->where('kd_jenis_prw', $row['kd_jenis_prw'])->oneArray();
          $row['nm_perawatan'] = $jns_perawatan['nm_perawatan'];
          $jumlah_total = $jumlah_total + $row['biaya_rawat'];
          $row['provider'] = 'Dokter';
          $rawat_jl_dr[] = $row;
        }
      }

      if($rows_rawat_jl_pr) {
        foreach ($rows_rawat_jl_pr as $row) {
          $jns_perawatan = $this->db('jns_perawatan')->where('kd_jenis_prw', $row['kd_jenis_prw'])->oneArray();
          $row['nm_perawatan'] = $jns_perawatan['nm_perawatan'];
          $jumlah_total = $jumlah_total + $row['biaya_rawat'];
          $row['provider'] = 'Perawat';
          $rawat_jl_pr[] = $row;
        }
      }

      if($rows_rawat_jl_drpr) {
        foreach ($rows_rawat_jl_drpr as $row) {
          $jns_perawatan = $this->db('jns_perawatan')->where('kd_jenis_prw', $row['kd_jenis_prw'])->oneArray();
          $row['nm_perawatan'] = $jns_perawatan['nm_perawatan'];
          $jumlah_total = $jumlah_total + $row['biaya_rawat'];
          $row['provider'] = 'Dokter & Perawat';
          $rawat_jl_drpr[] = $row;
        }
      }

      $merge_tindakan = array_merge($rawat_jl_dr, $rawat_jl_pr, $rawat_jl_drpr);
      $tindakan = [];
      foreach ($merge_tindakan as $row) {
        $row['nomor'] = $no_tindakan++;
        $tindakan[] = $row;
      }

      $rows_pemberian_obat = $this->db('detail_pemberian_obat')
      ->join('databarang', 'databarang.kode_brng=detail_pemberian_obat.kode_brng')
      ->where('detail_pemberian_obat.no_rawat', $_POST['no_rawat'])
      ->where('detail_pemberian_obat.status', 'Ralan')
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
      ->where('periksa_lab.status', 'Ralan')
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
      ->where('periksa_radiologi.status', 'Ralan')
      ->toArray();

      $periksa_radiologi = [];
      $jumlah_total_radiologi = 0;
      $no_rad = 1;
      foreach ($rows_periksa_radiologi as $row) {
        $jumlah_total_radiologi += $row['biaya'];
        $row['nomor'] = $no_rad++;
        $periksa_radiologi[] = $row;
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
        'rawat_jl_dr' => $rawat_jl_dr,
        'rawat_jl_pr' => $rawat_jl_pr,
        'rawat_jl_drpr' => $rawat_jl_drpr,
        'tindakan' => $tindakan,
        'jumlah_total' => $jumlah_total,
        'jumlah_total_obat' => $jumlah_total_obat,
        'poliklinik' => $poliklinik,
        'biaya_registrasi' => $poliklinik['registrasi'],
        'detail_pemberian_obat' => $detail_pemberian_obat,
        'periksa_lab' => $periksa_lab,
        'jumlah_total_lab' => $jumlah_total_lab,
        'periksa_radiologi' => $periksa_radiologi,
        'jumlah_total_radiologi' => $jumlah_total_radiologi,
        'tambahan_biaya' => $tambahan_biaya,
        'jumlah_total_tambahan' => $jumlah_total_tambahan,
        'no_rawat' => $_POST['no_rawat']
      ]);
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
        ->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))
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

      if($this->settings('keuangan', 'jurnal_kasir') == 1) {
          // jurnal_pendaftaran //
          if($_POST['jurnal_pendaftaran'] != 0) {
            $no_jurnal_pendaftaran = $this->setNoJurnal();
            $query_jurnal_pendaftaran =  $this->db('mlite_jurnal')->save([
                'no_jurnal' => $no_jurnal_pendaftaran,
                'no_bukti' => $_POST['no_rawat'],
                'tgl_jurnal' => date('Y-m-d'),
                'jenis' => 'U',
                'keterangan' => 'Pendaftaran rawat jalan '.$_POST['no_rawat'].'. Diposting oleh '.$this->core->getUserInfo('fullname', null, true).'.'
              ]);
            if($query_jurnal_pendaftaran) {
              $this->db('mlite_detailjurnal')->save([
                'no_jurnal' => $no_jurnal_pendaftaran,
                'kd_rek' => $this->settings('keuangan', 'akun_kredit_pendaftaran'),
                'debet' => '0',
                'kredit' => str_replace(".", "", $_POST['jurnal_pendaftaran'])
              ]);
            }
            unset($_POST['jurnal_pendaftaran']);
          }
          // End jurnal_pendaftaran //

          // jurnal_tindakan_ralan //
          if($_POST['jurnal_tindakan_ralan'] != 0) {
            $no_jurnal_tindakan_ralan = $this->setNoJurnal();
            $query_jurnal_tindakan_ralan =  $this->db('mlite_jurnal')->save([
                'no_jurnal' => $no_jurnal_tindakan_ralan,
                'no_bukti' => $_POST['no_rawat'],
                'tgl_jurnal' => date('Y-m-d'),
                'jenis' => 'U',
                'keterangan' => 'Tindakan rawat jalan '.$_POST['no_rawat'].'. Diposting oleh '.$this->core->getUserInfo('fullname', null, true).'.'
              ]);
            if($query_jurnal_tindakan_ralan) {
              $this->db('mlite_detailjurnal')->save([
                'no_jurnal' => $no_jurnal_tindakan_ralan,
                'kd_rek' => $this->settings('keuangan', 'akun_kredit_tindakan_ralan'),
                'debet' => '0',
                'kredit' => str_replace(".", "", $_POST['jurnal_tindakan_ralan'])
              ]);
            }
            unset($_POST['jurnal_tindakan_ralan']);
          }
          // End jurnal_tindakan_ralan //

          // jurnal_obat_bhp //
          if($_POST['jurnal_obat_bhp'] != 0) {
            $no_jurnal_obat_bhp = $this->setNoJurnal();
            $query_jurnal_obat_bhp =  $this->db('mlite_jurnal')->save([
                'no_jurnal' => $no_jurnal_obat_bhp,
                'no_bukti' => $_POST['no_rawat'],
                'tgl_jurnal' => date('Y-m-d'),
                'jenis' => 'U',
                'keterangan' => 'Obat dan BHP '.$_POST['no_rawat'].'. Diposting oleh '.$this->core->getUserInfo('fullname', null, true).'.'
              ]);
            if($query_jurnal_obat_bhp) {
              $this->db('mlite_detailjurnal')->save([
                'no_jurnal' => $no_jurnal_obat_bhp,
                'kd_rek' => $this->settings('keuangan', 'akun_kredit_obat_bhp'),
                'debet' => '0',
                'kredit' => str_replace(".", "", $_POST['jurnal_obat_bhp'])
              ]);
            }
            unset($_POST['jurnal_obat_bhp']);
          }
          // End jurnal_obat_bhp //

          // jurnal_laboratorium //
          if($_POST['jurnal_laboratorium'] != 0) {
            $no_jurnal_laboratorium = $this->setNoJurnal();
            $query_jurnal_laboratorium =  $this->db('mlite_jurnal')->save([
                'no_jurnal' => $no_jurnal_laboratorium,
                'no_bukti' => $_POST['no_rawat'],
                'tgl_jurnal' => date('Y-m-d'),
                'jenis' => 'U',
                'keterangan' => 'Laboratorium '.$_POST['no_rawat'].'. Diposting oleh '.$this->core->getUserInfo('fullname', null, true).'.'
              ]);
            if($query_jurnal_laboratorium) {
              $this->db('mlite_detailjurnal')->save([
                'no_jurnal' => $no_jurnal_laboratorium,
                'kd_rek' => $this->settings('keuangan', 'akun_kredit_laboratorium'),
                'debet' => '0',
                'kredit' => str_replace(".", "", $_POST['jurnal_laboratorium'])
              ]);
            }
            unset($_POST['jurnal_laboratorium']);
          }
          // End jurnal_laboratorium //

          // jurnal_radiologi//
          if($_POST['jurnal_radiologi'] != 0) {
            $no_jurnal_radiologi = $this->setNoJurnal();
            $query_jurnal_radiologi =  $this->db('mlite_jurnal')->save([
                'no_jurnal' => $no_jurnal_radiologi,
                'no_bukti' => $_POST['no_rawat'],
                'tgl_jurnal' => date('Y-m-d'),
                'jenis' => 'U',
                'keterangan' => 'Radiologi '.$_POST['no_rawat'].'. Diposting oleh '.$this->core->getUserInfo('fullname', null, true).'.'
              ]);
            if($query_jurnal_radiologi) {
              $this->db('mlite_detailjurnal')->save([
                'no_jurnal' => $no_jurnal_radiologi,
                'kd_rek' => $this->settings('keuangan', 'akun_kredit_radiologi'),
                'debet' => '0',
                'kredit' => str_replace(".", "", $_POST['jurnal_radiologi'])
              ]);
            }
            unset($_POST['jurnal_radiologi']);
          }
          // End jurnal_radiologi //

          // jurnal_tambahan_biaya //
          if($_POST['jurnal_tambahan_biaya'] != 0) {
            $no_jurnal_tambahan_biaya = $this->setNoJurnal();
            $query_jurnal_tambahan_biaya =  $this->db('mlite_jurnal')->save([
                'no_jurnal' => $no_jurnal_tambahan_biaya,
                'no_bukti' => $_POST['no_rawat'],
                'tgl_jurnal' => date('Y-m-d'),
                'jenis' => 'U',
                'keterangan' => 'Tambahan biaya'.$_POST['no_rawat'].'. Diposting oleh '.$this->core->getUserInfo('fullname', null, true).'.'
              ]);
            if($query_jurnal_tambahan_biaya) {
              $this->db('mlite_detailjurnal')->save([
                'no_jurnal' => $no_jurnal_tambahan_biaya,
                'kd_rek' => $this->settings('keuangan', 'akun_kredit_tambahan_biaya'),
                'debet' => '0',
                'kredit' => str_replace(".", "", $_POST['jurnal_tambahan_biaya'])
              ]);
            }
            unset($_POST['jurnal_tambahan_biaya']);
          }
          // End jurnal_tambahan_biaya //
      }

      unset($_POST['jurnal_pendaftaran']);
      unset($_POST['jurnal_tindakan_ralan']);
      unset($_POST['jurnal_obat_bhp']);
      unset($_POST['jurnal_laboratorium']);
      unset($_POST['jurnal_radiologi']);
      unset($_POST['jurnal_tambahan_biaya']);

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
        if($this->db('mlite_billing')->where('no_rawat', $_POST['no_rawat'])->like('kd_billing', 'RJ%')->oneArray()) {
          echo 'OK';
        }
        break;
        case "besar":
        $result = $this->db('mlite_billing')->where('no_rawat', $_GET['no_rawat'])->like('kd_billing', 'RJ%')->desc('tgl_billing')->desc('jam_billing')->oneArray();

        $result_detail['poliklinik'] = $this->db('poliklinik')
          ->join('reg_periksa', 'reg_periksa.kd_poli = poliklinik.kd_poli')
          ->where('reg_periksa.no_rawat', $_GET['no_rawat'])
          ->oneArray();

        $result_detail['rawat_jl_dr'] = $this->db('rawat_jl_dr')
          ->select('jns_perawatan.nm_perawatan')
          ->select(['biaya_rawat' => 'rawat_jl_dr.biaya_rawat'])
          ->select(['jml' => 'COUNT(rawat_jl_dr.kd_jenis_prw)'])
          ->select(['total_biaya_rawat_dr' => 'SUM(rawat_jl_dr.biaya_rawat)'])
          ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_jl_dr.kd_jenis_prw')
          ->where('rawat_jl_dr.no_rawat', $_GET['no_rawat'])
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
          ->where('rawat_jl_pr.no_rawat', $_GET['no_rawat'])
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
          ->where('rawat_jl_drpr.no_rawat', $_GET['no_rawat'])
          ->group('jns_perawatan.nm_perawatan')
          ->toArray();

        $total_rawat_jl_drpr = 0;
        foreach ($result_detail['rawat_jl_drpr'] as $row) {
          $total_rawat_jl_drpr += $row['biaya_rawat'];
        }

        $result_detail['detail_pemberian_obat'] = $this->db('detail_pemberian_obat')
          ->join('databarang', 'databarang.kode_brng=detail_pemberian_obat.kode_brng')
          ->where('no_rawat', $_GET['no_rawat'])
          ->where('detail_pemberian_obat.status', 'Ralan')
          ->toArray();

        $total_detail_pemberian_obat = 0;
        foreach ($result_detail['detail_pemberian_obat'] as $row) {
          $total_detail_pemberian_obat += $row['total'];
        }

        $result_detail['periksa_lab'] = $this->db('periksa_lab')
          ->join('jns_perawatan_lab', 'jns_perawatan_lab.kd_jenis_prw=periksa_lab.kd_jenis_prw')
          ->where('no_rawat', $_GET['no_rawat'])
          ->where('periksa_lab.status', 'Ralan')
          ->toArray();

        $total_periksa_lab = 0;
        foreach ($result_detail['periksa_lab'] as $row) {
          $total_periksa_lab += $row['biaya'];
        }

        $result_detail['periksa_radiologi'] = $this->db('periksa_radiologi')
          ->join('jns_perawatan_radiologi', 'jns_perawatan_radiologi.kd_jenis_prw=periksa_radiologi.kd_jenis_prw')
          ->where('no_rawat', $_GET['no_rawat'])
          ->where('periksa_radiologi.status', 'Ralan')
          ->toArray();

        $total_periksa_radiologi = 0;
        foreach ($result_detail['periksa_radiologi'] as $row) {
          $total_periksa_radiologi += $row['biaya'];
        }

        $result_detail['tambahan_biaya'] = $this->db('tambahan_biaya')
          ->where('no_rawat', $_GET['no_rawat'])
          ->toArray();

        $total_tambahan_biaya = 0;
        foreach ($result_detail['tambahan_biaya'] as $row) {
          $total_tambahan_biaya += $row['besar_biaya'];
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
        $pdf->Cell(110 ,5,'Biaya Pendaftaran Poliklinik',1,0);
        $pdf->Cell(25 ,5,'1',1,0, 'C');
        $pdf->Cell(44 ,5,number_format($result_detail['poliklinik']['registrasi'],2,',','.'),1,1,'R');//end of line

        $pdf->Cell(10 ,5,'2',1,0);
        $pdf->Cell(110 ,5,'Biaya Obat & BHP',1,0);
        $pdf->Cell(25 ,5,count($result_detail['detail_pemberian_obat']),1,0, 'C');
        $pdf->Cell(44 ,5,number_format($total_detail_pemberian_obat,2,',','.'),1,1,'R');//end of line

        $pdf->Cell(10 ,5,'3',1,0);
        $pdf->Cell(110 ,5,'Jasa Dokter',1,0);
        $pdf->Cell(25 ,5,count($result_detail['rawat_jl_dr']),1,0, 'C');
        $pdf->Cell(44 ,5,number_format($total_rawat_jl_dr,2,',','.'),1,1,'R');//end of line

        $pdf->Cell(10 ,5,'4',1,0);
        $pdf->Cell(110 ,5,'Jasa Perawat',1,0);
        $pdf->Cell(25 ,5,count($result_detail['rawat_jl_pr']),1,0, 'C');
        $pdf->Cell(44 ,5,number_format($total_rawat_jl_pr,2,',','.'),1,1,'R');//end of line

        $pdf->Cell(10 ,5,'5',1,0);
        $pdf->Cell(110 ,5,'Jasa Dokter & Perawat',1,0);
        $pdf->Cell(25 ,5,count($result_detail['rawat_jl_drpr']),1,0, 'C');
        $pdf->Cell(44 ,5,number_format($total_rawat_jl_drpr,2,',','.'),1,1,'R');//end of line

        $pdf->Cell(10 ,5,'6',1,0);
        $pdf->Cell(110 ,5,'Jasa Laboratorium',1,0);
        $pdf->Cell(25 ,5,count($result_detail['periksa_lab']),1,0, 'C');
        $pdf->Cell(44 ,5,number_format($total_periksa_lab,2,',','.'),1,1,'R');//end of line

        $pdf->Cell(10 ,5,'7',1,0);
        $pdf->Cell(110 ,5,'Jasa Radiologi',1,0);
        $pdf->Cell(25 ,5,count($result_detail['periksa_radiologi']),1,0, 'C');
        $pdf->Cell(44 ,5,number_format($total_periksa_radiologi,2,',','.'),1,1,'R');//end of line

        $pdf->Cell(10 ,5,'8',1,0);
        $pdf->Cell(110 ,5,'Biaya Tambahan',1,0);
        $pdf->Cell(25 ,5,count($result_detail['tambahan_biaya']),1,0, 'C');
        $pdf->Cell(44 ,5,number_format($total_tambahan_biaya,2,',','.'),1,1,'R');//end of line

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
        $pdf->Cell(44 ,15,'Rp. '.number_format($result_detail['poliklinik']['registrasi']+$total_detail_pemberian_obat+$total_rawat_jl_dr+$total_rawat_jl_pr+$total_rawat_jl_drpr+$total_periksa_lab+$total_periksa_radiologi+$total_tambahan_biaya,2,',','.'),0,0,'R');//end of line

        $pdf->Cell(189 ,20,'',0,1);//end of line

        $pdf->SetFont('Arial','',11);

        $pdf->Cell(120 ,5,'',0,0);
        $pdf->Cell(69 ,10,$settings['kota'].', '.date('Y-m-d'),0,1);//end of line

        $qr=QRCode::getMinimumQRCode($this->core->getUserInfo('fullname', null, true),QR_ERROR_CORRECT_LEVEL_L);
        //$qr=QRCode::getMinimumQRCode('Petugas: '.$this->core->getUserInfo('fullname', null, true).'; Lokasi: '.UPLOADS.'/invoices/'.$result['kd_billing'].'.pdf',QR_ERROR_CORRECT_LEVEL_L);
        $im=$qr->createImage(4,4);
        imagepng($im,BASE_DIR.'/admin/tmp/qrcode.png');
        imagedestroy($im);

        $image = BASE_DIR."/admin/tmp/qrcode.png";

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

        echo $this->draw('billing.besar.html', ['billing' => $result, 'billing_besar_detail' => $result_detail, 'pasien' => $pasien, 'fullname' => $this->core->getUserInfo('fullname', null, true)]);
        break;
        case "kecil":
        $result = $this->db('mlite_billing')->where('no_rawat', $_GET['no_rawat'])->like('kd_billing', 'RJ%')->oneArray();
        $reg_periksa = $this->db('reg_periksa')->where('no_rawat', $_GET['no_rawat'])->oneArray();
        $pasien = $this->db('pasien')->where('no_rkm_medis', $reg_periksa['no_rkm_medis'])->oneArray();
        echo $this->draw('billing.kecil.html', ['billing' => $result, 'pasien' => $pasien, 'fullname' => $this->core->getUserInfo('fullname', null, true)]);
        break;
      }
      exit();
    }

    public function setNoJurnal()
    {
        $date = date('Y-m-d');
        $last_no_jurnal = $this->db()->pdo()->prepare("SELECT ifnull(MAX(CONVERT(RIGHT(no_jurnal,6),signed)),0) FROM jurnal WHERE tgl_jurnal = '$date'");
        $last_no_jurnal->execute();
        $last_no_jurnal = $last_no_jurnal->fetch();
        if(empty($last_no_jurnal[0])) {
          $last_no_jurnal[0] = '000000';
        }
        $next_no_jurnal = sprintf('%06s', ($last_no_jurnal[0] + 1));
        $next_no_jurnal = 'JR'.date('Ymd').''.$next_no_jurnal;

        return $next_no_jurnal;
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
         throw new Exception("Could not fetch remote content from: '$url'");
      }

	    $mail = new PHPMailer(true);
      $temp  = @file_get_contents(MODULES."/kasir_rawat_jalan/email/email.send.html");

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

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/kasir_rawat_jalan/js/admin/kasir_rawat_jalan.js');
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
        $this->core->addJS(url([ADMIN, 'kasir_rawat_jalan', 'javascript']), 'footer');
    }

}
