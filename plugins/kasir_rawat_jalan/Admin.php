<?php
namespace Plugins\Kasir_Rawat_Jalan;

use Systems\AdminModule;
use Systems\Lib\QRCode;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Admin extends AdminModule
{
    public $assign = [];

    public function navigation()
    {
        return [
            'Kelola'   => 'manage',
            'Kasir'    => 'shift',
            'Laporan'  => 'report',
        ];
    }

    public function apiBillingList()
    {
        $username = $this->core->checkAuth('GET');
        if (!$this->core->checkPermission($username, 'can_read', 'kasir_rawat_jalan')) {
            echo json_encode(['status' => 'error', 'message' => 'You do not have permission to access this resource']);
            exit;
        }
        
        $page = $_GET['page'] ?? 1;
        $per_page = $_GET['per_page'] ?? 10;
        $offset = ($page - 1) * $per_page;
        $search = $_GET['s'] ?? '';
        $tgl_awal = $_GET['tgl_awal'] ?? date('Y-m-d');
        $tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');

        $query = $this->db('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
            ->join('dokter', 'dokter.kd_dokter = reg_periksa.kd_dokter')
            ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
            ->where('reg_periksa.tgl_registrasi', '>=', $tgl_awal)
            ->where('reg_periksa.tgl_registrasi', '<=', $tgl_akhir);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('reg_periksa.no_rawat', 'LIKE', "%$search%")
                  ->orWhere('pasien.nm_pasien', 'LIKE', "%$search%")
                  ->orWhere('pasien.no_rkm_medis', 'LIKE', "%$search%");
            });
        }

        $total = $query->count();
        $data = $query->select('reg_periksa.*')
            ->select('pasien.nm_pasien')
            ->select('pasien.no_rkm_medis')
            ->select('dokter.nm_dokter')
            ->select('poliklinik.nm_poli')
            ->offset($offset)
            ->limit($per_page)
            ->desc('reg_periksa.tgl_registrasi')
            ->desc('reg_periksa.jam_reg')
            ->toArray();

        foreach ($data as &$row) {
             $billing = $this->db('mlite_billing')->where('no_rawat', $row['no_rawat'])->like('kd_billing', 'RJ%')->oneArray();
             $row['total_tagihan'] = $billing ? $billing['jumlah_harus_bayar'] : 0;
             $row['status_bayar'] = $row['status_bayar']; 
        }

        echo json_encode([
            'status' => 'success',
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page
        ]);
        exit;
    }

    public function apiBillingDetail($no_rawat)
    {
        $username = $this->core->checkAuth('GET');
        if (!$this->core->checkPermission($username, 'can_read', 'kasir_rawat_jalan')) {
             echo json_encode(['status' => 'error', 'message' => 'You do not have permission to access this resource']);
             exit;
        }
        $no_rawat = revertNorawat($no_rawat);

        // Basic Info
        $reg_periksa = $this->db('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
            ->join('dokter', 'dokter.kd_dokter = reg_periksa.kd_dokter')
            ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
            ->where('reg_periksa.no_rawat', $no_rawat)
            ->oneArray();

        if (!$reg_periksa) {
            echo json_encode(['status' => 'error', 'message' => 'Data not found']);
            exit;
        }

        $details = [];
        $total_biaya = 0;

        // 1. Registrasi
        $poliklinik = $this->db('poliklinik')->where('kd_poli', $reg_periksa['kd_poli'])->oneArray();
        $biaya_reg = ($reg_periksa['stts_daftar'] == 'Lama') ? $poliklinik['registrasilama'] : $poliklinik['registrasi'];
        $details[] = [
            'kategori' => 'Registrasi',
            'nama' => 'Biaya Registrasi',
            'biaya' => $biaya_reg,
            'jumlah' => 1,
            'subtotal' => $biaya_reg
        ];
        $total_biaya += $biaya_reg;

        // 2. Tindakan Dokter
        $tindakan_dr = $this->db('rawat_jl_dr')
            ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_jl_dr.kd_jenis_prw')
            ->where('no_rawat', $no_rawat)->toArray();
        foreach ($tindakan_dr as $t) {
            $details[] = [
                'kategori' => 'Tindakan Dokter',
                'nama' => $t['nm_perawatan'],
                'biaya' => $t['biaya_rawat'],
                'jumlah' => 1,
                'subtotal' => $t['biaya_rawat'],
                'kd_jenis_prw' => $t['kd_jenis_prw'],
                'tgl_perawatan' => $t['tgl_perawatan'],
                'jam_rawat' => $t['jam_rawat'],
                'provider' => 'rawat_jl_dr',
                'type' => 'tindakan'
            ];
            $total_biaya += $t['biaya_rawat'];
        }

        // 3. Tindakan Perawat
        $tindakan_pr = $this->db('rawat_jl_pr')
            ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_jl_pr.kd_jenis_prw')
            ->where('no_rawat', $no_rawat)->toArray();
        foreach ($tindakan_pr as $t) {
            $details[] = [
                'kategori' => 'Tindakan Perawat',
                'nama' => $t['nm_perawatan'],
                'biaya' => $t['biaya_rawat'],
                'jumlah' => 1,
                'subtotal' => $t['biaya_rawat'],
                'kd_jenis_prw' => $t['kd_jenis_prw'],
                'tgl_perawatan' => $t['tgl_perawatan'],
                'jam_rawat' => $t['jam_rawat'],
                'provider' => 'rawat_jl_pr',
                'type' => 'tindakan'
            ];
            $total_biaya += $t['biaya_rawat'];
        }

        // 4. Tindakan Dokter & Perawat
        $tindakan_drpr = $this->db('rawat_jl_drpr')
            ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_jl_drpr.kd_jenis_prw')
            ->where('no_rawat', $no_rawat)->toArray();
        foreach ($tindakan_drpr as $t) {
            $details[] = [
                'kategori' => 'Tindakan Dokter & Perawat',
                'nama' => $t['nm_perawatan'],
                'biaya' => $t['biaya_rawat'],
                'jumlah' => 1,
                'subtotal' => $t['biaya_rawat'],
                'kd_jenis_prw' => $t['kd_jenis_prw'],
                'tgl_perawatan' => $t['tgl_perawatan'],
                'jam_rawat' => $t['jam_rawat'],
                'provider' => 'rawat_jl_drpr',
                'type' => 'tindakan'
            ];
            $total_biaya += $t['biaya_rawat'];
        }

        // 5. Obat & BHP
        $obat = $this->db('detail_pemberian_obat')
            ->join('databarang', 'databarang.kode_brng = detail_pemberian_obat.kode_brng')
            ->where('no_rawat', $no_rawat)
            ->where('detail_pemberian_obat.status', 'Ralan')
            ->toArray();
            
        foreach ($obat as $o) {
            $details[] = [
                'kategori' => 'Obat & BHP',
                'nama' => $o['nama_brng'],
                'biaya' => $o['biaya_obat'],
                'jumlah' => $o['jml'],
                'subtotal' => $o['total'] + $o['embalase'] + $o['tuslah'],
                'kode_brng' => $o['kode_brng'],
                'tgl_peresepan' => $o['tgl_perawatan'],
                'jam_peresepan' => $o['jam'],
                'kd_bangsal' => $o['kd_bangsal'],
                'type' => 'obat'
            ];
            $total_biaya += ($o['total'] + $o['embalase'] + $o['tuslah']);
        }

        // 6. Laboratorium
        $lab = $this->db('periksa_lab')
            ->join('jns_perawatan_lab', 'jns_perawatan_lab.kd_jenis_prw = periksa_lab.kd_jenis_prw')
            ->where('no_rawat', $no_rawat)
            ->where('periksa_lab.status', 'Ralan')
            ->toArray();
        foreach ($lab as $l) {
            $details[] = [
                'kategori' => 'Laboratorium',
                'nama' => $l['nm_perawatan'],
                'biaya' => $l['biaya'],
                'jumlah' => 1,
                'subtotal' => $l['biaya'],
                'kd_jenis_prw' => $l['kd_jenis_prw'],
                'tgl_perawatan' => $l['tgl_periksa'],
                'jam_rawat' => $l['jam'],
                'type' => 'lab'
            ];
            $total_biaya += $l['biaya'];
        }

        // 7. Radiologi
        $rad = $this->db('periksa_radiologi')
            ->join('jns_perawatan_radiologi', 'jns_perawatan_radiologi.kd_jenis_prw = periksa_radiologi.kd_jenis_prw')
            ->where('no_rawat', $no_rawat)
            ->where('periksa_radiologi.status', 'Ralan')
            ->toArray();
        foreach ($rad as $r) {
            $details[] = [
                'kategori' => 'Radiologi',
                'nama' => $r['nm_perawatan'],
                'biaya' => $r['biaya'],
                'jumlah' => 1,
                'subtotal' => $r['biaya'],
                'kd_jenis_prw' => $r['kd_jenis_prw'],
                'tgl_perawatan' => $r['tgl_periksa'],
                'jam_rawat' => $r['jam'],
                'type' => 'rad'
            ];
            $total_biaya += $r['biaya'];
        }
        
        // 8. Tambahan Biaya
        $tambahan = $this->db('tambahan_biaya')->where('no_rawat', $no_rawat)->toArray();
        foreach ($tambahan as $t) {
             $details[] = [
                'kategori' => 'Tambahan',
                'nama' => $t['nama_biaya'],
                'biaya' => $t['besar_biaya'],
                'jumlah' => 1,
                'subtotal' => $t['besar_biaya']
            ];
            $total_biaya += $t['besar_biaya'];
        }

        echo json_encode([
            'status' => 'success',
            'data' => [
                'registrasi' => $reg_periksa,
                'details' => $details,
                'total' => $total_biaya
            ]
        ]);
        exit;
    }

    public function apiBilling($no_rawat)
    {
        $username = $this->core->checkAuth('GET');
        if (!$this->core->checkPermission($username, 'can_read', 'kasir_rawat_jalan')) {
             echo json_encode(['status' => 'error', 'message' => 'You do not have permission to access this resource']);
             exit;
        }
        $no_rawat = revertNorawat($no_rawat);

        $query = $this->db('mlite_billing')
            ->where('no_rawat', $no_rawat)
            ->like('kd_billing', 'RJ%');

        if (isset($_GET['tgl_awal']) && isset($_GET['tgl_akhir'])) {
            $query->where('tgl_billing', '>=', $_GET['tgl_awal'])
                  ->where('tgl_billing', '<=', $_GET['tgl_akhir']);
        }

        $billing = $query
            ->desc('tgl_billing')
            ->desc('jam_billing')
            ->oneArray();

        echo json_encode([
            'status' => 'success',
            'data' => $billing
        ]);
        exit;
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
        $this->assign['penjab']       = $this->db('penjab')->where('status', '1')->toArray();
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
          for ($i = 0; $i < $_POST['jml_tindakan']; $i++) {
            $this->db('rawat_jl_dr')->save([
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
              'biaya_rawat' => $jns_perawatan['total_byrdr'],
              'stts_bayar' => 'Belum'
            ]);
          }
        }
        if($_POST['provider'] == 'rawat_jl_pr') {
          for ($i = 0; $i < $_POST['jml_tindakan']; $i++) {
            $this->db('rawat_jl_pr')->save([
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
              'biaya_rawat' => $jns_perawatan['total_byrpr'],
              'stts_bayar' => 'Belum'
            ]);
          }
        }
        if($_POST['provider'] == 'rawat_jl_drpr') {
          for ($i = 0; $i < $_POST['jml_tindakan']; $i++) {
            $this->db('rawat_jl_drpr')->save([
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
              'biaya_rawat' => $jns_perawatan['total_byrdrpr'],
              'stts_bayar' => 'Belum'
            ]);
          }
        }
      }
      if($_POST['kat'] == 'obat') {

        $get_gudangbarang = $this->db('gudangbarang')->where('kode_brng', $_POST['kd_jenis_prw'])->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))->oneArray();
        $get_databarang = $this->db('databarang')->where('kode_brng', $_POST['kd_jenis_prw'])->oneArray();

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
            'no_faktur' => $get_gudangbarang['no_faktur'],
            'keterangan' => $_POST['no_rawat'] . ' ' . $this->core->getRegPeriksaInfo('no_rkm_medis', $_POST['no_rawat']) . ' ' . $this->core->getPasienInfo('nm_pasien', $this->core->getRegPeriksaInfo('no_rkm_medis', $_POST['no_rawat']))
          ]);

        $this->db('detail_pemberian_obat')
          ->save([
            'tgl_perawatan' => $_POST['tgl_perawatan'],
            'jam' => $_POST['jam_rawat'],
            'no_rawat' => $_POST['no_rawat'],
            'kode_brng' => $_POST['kd_jenis_prw'],
            'h_beli' => $get_databarang['h_beli'],
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
          'no_faktur' => $get_gudangbarang['no_faktur'],
          'keterangan' => $_POST['no_rawat'] . ' ' . $this->core->getRegPeriksaInfo('no_rkm_medis', $_POST['no_rawat']) . ' ' . $this->core->getPasienInfo('nm_pasien', $this->core->getRegPeriksaInfo('no_rkm_medis', $_POST['no_rawat']))
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
      if($poliklinik['stts_daftar'] == 'Lama') {
        $poliklinik['registrasi'] = $poliklinik['registrasilama'];
      }

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

      // Collect racikan timestamps to exclude from detail_pemberian_obat
      $exclude_jams = [];
      $racikan_jams = $this->db('detail_obat_racikan')->where('no_rawat', $_POST['no_rawat'])->toArray();
      foreach($racikan_jams as $rj) {
          $exclude_jams[] = $rj['jam'];
      }

      $query_pemberian = $this->db('detail_pemberian_obat')
        ->where('detail_pemberian_obat.no_rawat', $_POST['no_rawat'])
        ->where('detail_pemberian_obat.status', 'Ralan');

      if(!empty($exclude_jams)) {
          $query_pemberian->notIn('jam', $exclude_jams);
      }

      $rows_pemberian_obat = $query_pemberian->toArray();

      $detail_pemberian_obat = [];
      $jumlah_total_obat = 0;
      $jumlah_total_embalase = 0;
      $jumlah_total_tuslah = 0;
      $no_obat = 1;
      foreach ($rows_pemberian_obat as $row) {
        $row['nomor'] = $no_obat++;
        $databarang = $this->db('databarang')->where('kode_brng', $row['kode_brng'])->oneArray();
        $row['nama_brng'] = $databarang['nama_brng'];
        $jumlah_total_obat += floatval($row['total']);
        $jumlah_total_embalase += floatval($row['embalase']);
        $jumlah_total_tuslah += floatval($row['tuslah']);
        $detail_pemberian_obat[] = $row;
      }

      // Fetch detail_obat_racikan linked with riwayat_barang_medis for quantity
      $rows_obat_racikan = $this->db('obat_racikan')
        ->where('no_rawat', $_POST['no_rawat'])
        ->toArray();

      foreach ($rows_obat_racikan as $header) {
        $ingredients_map = $this->db('detail_obat_racikan')
            ->where('no_rawat', $header['no_rawat'])
            ->where('no_racik', $header['no_racik'])
            ->where('tgl_perawatan', $header['tgl_perawatan'])
            ->where('jam', $header['jam'])
            ->toArray();

        $total_racikan = 0;
        $total_embalase_racikan = 0;
        $total_tuslah_racikan = 0;
        $ingredient_rows = [];

        foreach ($ingredients_map as $map) {
            $row = $this->db('detail_pemberian_obat')
                ->join('databarang', 'databarang.kode_brng=detail_pemberian_obat.kode_brng')
                ->where('detail_pemberian_obat.no_rawat', $map['no_rawat'])
                ->where('detail_pemberian_obat.kode_brng', $map['kode_brng'])
                ->where('detail_pemberian_obat.tgl_perawatan', $map['tgl_perawatan'])
                ->where('detail_pemberian_obat.jam', $map['jam'])
                ->where('detail_pemberian_obat.status', 'Ralan')
                ->oneArray();

            if ($row) {
                $subtotal = $row['total'];
                
                $total_racikan += $subtotal;
                $total_embalase_racikan += $row['embalase'];
                $total_tuslah_racikan += $row['tuslah'];
    
                // Prepare ingredient row (hide prices)
                $row['nomor'] = ''; 
                $row['nama_brng'] = "&nbsp;&nbsp;&nbsp;&nbsp; - " . $row['nama_brng'];
                $row['total'] = 0;
                $row['embalase'] = 0;
                $row['tuslah'] = 0;
                $row['biaya_obat'] = 0;
                $row['jml'] = 0;
                
                $ingredient_rows[] = $row;
            }
        }

        $header_row = [];
        $header_row['nomor'] = $no_obat++;
        $header_row['nama_brng'] = ">> Racikan " . $header['no_racik'] . ": " . $header['nama_racik'] . " (" . $header['jml_dr'] . " Bungkus)";
        $header_row['jml'] = $header['jml_dr'];
        
        // Calculate unit price for display: total / qty
        if ($header['jml_dr'] > 0) {
            $header_row['biaya_obat'] = $total_racikan / $header['jml_dr'];
        } else {
            $header_row['biaya_obat'] = $total_racikan;
        }

        $header_row['total'] = $total_racikan;
        $header_row['embalase'] = $total_embalase_racikan;
        $header_row['tuslah'] = $total_tuslah_racikan;
        $header_row['no_rawat'] = $header['no_rawat'];
        $header_row['tgl_perawatan'] = $header['tgl_perawatan'];
        $header_row['jam'] = $header['jam'];
        $header_row['kode_brng'] = '-'; 
        
        $jumlah_total_obat += $total_racikan;
        $jumlah_total_embalase += $total_embalase_racikan;
        $jumlah_total_tuslah += $total_tuslah_racikan;

        $detail_pemberian_obat[] = $header_row;
        
        foreach ($ingredient_rows as $row) {
            $detail_pemberian_obat[] = $row;
        }
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

      $jumlah_total_operasi = 0;
      $operasis = $this->db('operasi')->where('no_rawat', $_POST['no_rawat'])->where('status', 'Ralan')->toArray();
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

      echo $this->draw('rincian.html', [
        'rawat_jl_dr' => $rawat_jl_dr,
        'rawat_jl_pr' => $rawat_jl_pr,
        'rawat_jl_drpr' => $rawat_jl_drpr,
        'tindakan' => $tindakan,
        'jumlah_total' => $jumlah_total,
        'jumlah_total_obat' => $jumlah_total_obat,
        'jumlah_total_embalase' => $jumlah_total_embalase,
        'jumlah_total_tuslah' => $jumlah_total_tuslah,
        'poliklinik' => $poliklinik,
        'biaya_registrasi' => $poliklinik['registrasi'],
        'detail_pemberian_obat' => $detail_pemberian_obat,
        'periksa_lab' => $periksa_lab,
        'jumlah_total_lab' => $jumlah_total_lab,
        'periksa_radiologi' => $periksa_radiologi,
        'jumlah_total_radiologi' => $jumlah_total_radiologi,
        'jumlah_total_operasi' => $jumlah_total_operasi,
        'jumlah_total_obat_operasi' => $jumlah_total_obat_operasi,
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
          if($_POST['jurnal_pendaftaran'] != '0,00') {
              $no_jurnal_pendaftaran = $this->core->setNoJurnal();
              $keterangan = $this->db('mlite_rekening')
                  ->where('kd_rek', $this->settings('keuangan', 'akun_kredit_pendaftaran'))
                  ->oneArray();
              $jumlah = str_replace(".", "", substr($_POST['jurnal_pendaftaran'], 0, strpos($_POST['jurnal_pendaftaran'], ",")));
              
              $query_jurnal_pendaftaran = $this->db('mlite_jurnal')->save([
                  'no_jurnal' => $no_jurnal_pendaftaran,
                  'no_bukti' => $_POST['no_rawat'],
                  'tgl_jurnal' => date('Y-m-d'),
                  'jenis' => 'U',
                  'kegiatan' => $keterangan['nm_rek'],
                  'keterangan' => $keterangan['nm_rek'].' '.$_POST['no_rawat'].'. Diposting oleh '.$this->core->getUserInfo('fullname', null, true).'.'
              ]);
              
              if($query_jurnal_pendaftaran) {
                  // DEBET: Kas/Piutang Usaha (Aset bertambah)
                  $this->db('mlite_detailjurnal')->save([
                      'no_jurnal' => $no_jurnal_pendaftaran,
                      'kd_rek' => $this->settings('keuangan', 'akun_debet_kas'), // Setting untuk akun kas
                      'arus_kas' => '1',
                      'debet' => $jumlah,
                      'kredit' => '0'
                  ]);
                  
                  // KREDIT: Pendapatan Pendaftaran (Pendapatan bertambah)
                  $this->db('mlite_detailjurnal')->save([
                      'no_jurnal' => $no_jurnal_pendaftaran,
                      'kd_rek' => $this->settings('keuangan', 'akun_kredit_pendaftaran'),
                      'arus_kas' => '0',
                      'debet' => '0',
                      'kredit' => $jumlah
                  ]);
              }
              unset($_POST['jurnal_pendaftaran']);
          }
          // End jurnal_pendaftaran // 

          // jurnal_tindakan_ralan //
          if($_POST['jurnal_tindakan_ralan'] != '0,00') {
              $no_jurnal_tindakan_ralan = $this->core->setNoJurnal();
              $keterangan = $this->db('mlite_rekening')
                  ->where('kd_rek', $this->settings('keuangan', 'akun_kredit_tindakan'))
                  ->oneArray();
              $jumlah = str_replace(".", "", substr($_POST['jurnal_tindakan_ralan'], 0, strpos($_POST['jurnal_tindakan_ralan'], ",")));
              
              $query_jurnal_tindakan_ralan = $this->db('mlite_jurnal')->save([
                  'no_jurnal' => $no_jurnal_tindakan_ralan,
                  'no_bukti' => $_POST['no_rawat'],
                  'tgl_jurnal' => date('Y-m-d'),
                  'jenis' => 'U',
                  'kegiatan' => $keterangan['nm_rek'],
                  'keterangan' => $keterangan['nm_rek'].' '.$_POST['no_rawat'].'. Diposting oleh '.$this->core->getUserInfo('fullname', null, true).'.'
              ]);
              
              if($query_jurnal_tindakan_ralan) {
                  // DEBET: Kas/Piutang Usaha
                  $this->db('mlite_detailjurnal')->save([
                      'no_jurnal' => $no_jurnal_tindakan_ralan,
                      'kd_rek' => $this->settings('keuangan', 'akun_debet_kas'),
                      'arus_kas' => '1',
                      'debet' => $jumlah,
                      'kredit' => '0'
                  ]);
                  
                  // KREDIT: Pendapatan Tindakan
                  $this->db('mlite_detailjurnal')->save([
                      'no_jurnal' => $no_jurnal_tindakan_ralan,
                      'kd_rek' => $this->settings('keuangan', 'akun_kredit_tindakan'),
                      'arus_kas' => '0',
                      'debet' => '0',
                      'kredit' => $jumlah
                  ]);
              }
              unset($_POST['jurnal_tindakan_ralan']);
          }
          // End jurnal_tindakan_ralan //

          // jurnal_obat_bhp //
          if($_POST['jurnal_obat_bhp'] != '0,00') {
              $no_jurnal_obat_bhp = $this->core->setNoJurnal();
              $keterangan = $this->db('mlite_rekening')
                  ->where('kd_rek', $this->settings('keuangan', 'akun_kredit_obat_bhp'))
                  ->oneArray();
              $jumlah = str_replace(".", "", substr($_POST['jurnal_obat_bhp'], 0, strpos($_POST['jurnal_obat_bhp'], ",")));
              
              $query_jurnal_obat_bhp = $this->db('mlite_jurnal')->save([
                  'no_jurnal' => $no_jurnal_obat_bhp,
                  'no_bukti' => $_POST['no_rawat'],
                  'tgl_jurnal' => date('Y-m-d'),
                  'jenis' => 'U',
                  'kegiatan' => $keterangan['nm_rek'],
                  'keterangan' => $keterangan['nm_rek'].' '.$_POST['no_rawat'].'. Diposting oleh '.$this->core->getUserInfo('fullname', null, true).'.'
              ]);
              
              if($query_jurnal_obat_bhp) {
                  // DEBET: Kas/Piutang Usaha
                  $this->db('mlite_detailjurnal')->save([
                      'no_jurnal' => $no_jurnal_obat_bhp,
                      'kd_rek' => $this->settings('keuangan', 'akun_debet_kas'),
                      'arus_kas' => '1',
                      'debet' => $jumlah,
                      'kredit' => '0'
                  ]);
                  
                  // KREDIT: Pendapatan Obat dan BHP
                  $this->db('mlite_detailjurnal')->save([
                      'no_jurnal' => $no_jurnal_obat_bhp,
                      'kd_rek' => $this->settings('keuangan', 'akun_kredit_obat_bhp'),
                      'arus_kas' => '0',
                      'debet' => '0',
                      'kredit' => $jumlah
                  ]);
              }
              unset($_POST['jurnal_obat_bhp']);
          }
          // End jurnal_obat_bhp //

          // jurnal_laboratorium //
          if($_POST['jurnal_laboratorium'] != '0,00') {
              $no_jurnal_laboratorium = $this->core->setNoJurnal();
              $keterangan = $this->db('mlite_rekening')
                  ->where('kd_rek', $this->settings('keuangan', 'akun_kredit_laboratorium'))
                  ->oneArray();
              $jumlah = str_replace(".", "", substr($_POST['jurnal_laboratorium'], 0, strpos($_POST['jurnal_laboratorium'], ",")));
              
              $query_jurnal_laboratorium = $this->db('mlite_jurnal')->save([
                  'no_jurnal' => $no_jurnal_laboratorium,
                  'no_bukti' => $_POST['no_rawat'],
                  'tgl_jurnal' => date('Y-m-d'),
                  'jenis' => 'U',
                  'kegiatan' => $keterangan['nm_rek'],
                  'keterangan' => $keterangan['nm_rek'].' '.$_POST['no_rawat'].'. Diposting oleh '.$this->core->getUserInfo('fullname', null, true).'.'
              ]);
              
              if($query_jurnal_laboratorium) {
                  // DEBET: Kas/Piutang Usaha
                  $this->db('mlite_detailjurnal')->save([
                      'no_jurnal' => $no_jurnal_laboratorium,
                      'kd_rek' => $this->settings('keuangan', 'akun_debet_kas'),
                      'arus_kas' => '1',
                      'debet' => $jumlah,
                      'kredit' => '0'
                  ]);
                  
                  // KREDIT: Pendapatan Laboratorium
                  $this->db('mlite_detailjurnal')->save([
                      'no_jurnal' => $no_jurnal_laboratorium,
                      'kd_rek' => $this->settings('keuangan', 'akun_kredit_laboratorium'),
                      'arus_kas' => '0',
                      'debet' => '0',
                      'kredit' => $jumlah
                  ]);
              }
              unset($_POST['jurnal_laboratorium']);
          }
          // End jurnal_laboratorium //

          // jurnal_radiologi//
          if($_POST['jurnal_radiologi'] != '0,00') {
              $no_jurnal_radiologi = $this->core->setNoJurnal();
              $keterangan = $this->db('mlite_rekening')
                  ->where('kd_rek', $this->settings('keuangan', 'akun_kredit_radiologi'))
                  ->oneArray();
              $jumlah = str_replace(".", "", substr($_POST['jurnal_radiologi'], 0, strpos($_POST['jurnal_radiologi'], ",")));
              
              $query_jurnal_radiologi = $this->db('mlite_jurnal')->save([
                  'no_jurnal' => $no_jurnal_radiologi,
                  'no_bukti' => $_POST['no_rawat'],
                  'tgl_jurnal' => date('Y-m-d'),
                  'jenis' => 'U',
                  'kegiatan' => $keterangan['nm_rek'],
                  'keterangan' => $keterangan['nm_rek'].' '.$_POST['no_rawat'].'. Diposting oleh '.$this->core->getUserInfo('fullname', null, true).'.'
              ]);
              
              if($query_jurnal_radiologi) {
                  // DEBET: Kas/Piutang Usaha
                  $this->db('mlite_detailjurnal')->save([
                      'no_jurnal' => $no_jurnal_radiologi,
                      'kd_rek' => $this->settings('keuangan', 'akun_debet_kas'),
                      'arus_kas' => '1',
                      'debet' => $jumlah,
                      'kredit' => '0'
                  ]);
                  
                  // KREDIT: Pendapatan Radiologi
                  $this->db('mlite_detailjurnal')->save([
                      'no_jurnal' => $no_jurnal_radiologi,
                      'kd_rek' => $this->settings('keuangan', 'akun_kredit_radiologi'),
                      'arus_kas' => '0',
                      'debet' => '0',
                      'kredit' => $jumlah
                  ]);
              }
              unset($_POST['jurnal_radiologi']);
          }
          // End jurnal_radiologi //
      }

      unset($_POST['jurnal_pendaftaran']);
      unset($_POST['jurnal_tindakan_ralan']);
      unset($_POST['jurnal_obat_bhp']);
      unset($_POST['jurnal_laboratorium']);
      unset($_POST['jurnal_radiologi']);

      $query = $this->db('mlite_billing')->save($_POST);
      if($query) {
        $this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->update(['status_bayar' => 'Sudah Bayar']);
      }
      exit();
    }

    public function apiSimpanKasir()
    {
        $payload = json_decode(file_get_contents('php://input'), true);
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_create', 'kasir_rawat_jalan')) {
            echo json_encode(['status' => 'error', 'message' => 'You do not have permission to access this resource']);
            exit;
        }

        $payload['id_user'] = $this->db('mlite_users')->where('username', $username)->oneArray()['id'];
        $payload['kd_billing'] = 'RJ.'.date('d.m.Y.H.i.s');
        $payload['tgl_billing'] = $payload['tgl_bayar'] ?? date('Y-m-d');
        $payload['jam_billing'] = $payload['jam_bayar'] ?? date('H:i:s');
        
        unset($payload['tgl_bayar']);
        unset($payload['jam_bayar']);

        if($this->settings('keuangan', 'jurnal_kasir') == 1) {
            // jurnal_pendaftaran //
            if(isset($payload['jurnal_pendaftaran']) && $payload['jurnal_pendaftaran'] != '0' && $payload['jurnal_pendaftaran'] != 0) {
                $no_jurnal_pendaftaran = $this->core->setNoJurnal();
                $keterangan = $this->db('mlite_rekening')
                    ->where('kd_rek', $this->settings('keuangan', 'akun_kredit_pendaftaran'))
                    ->oneArray();
                $jumlah = $payload['jurnal_pendaftaran'];
                
                $query_jurnal_pendaftaran = $this->db('mlite_jurnal')->save([
                    'no_jurnal' => $no_jurnal_pendaftaran,
                    'no_bukti' => $payload['no_rawat'],
                    'tgl_jurnal' => date('Y-m-d'),
                    'jenis' => 'U',
                    'kegiatan' => $keterangan['nm_rek'],
                    'keterangan' => $keterangan['nm_rek'].' '.$payload['no_rawat'].'. Diposting oleh '.$this->core->getUserInfo('fullname', null, true).'.'
                ]);
                
                if($query_jurnal_pendaftaran) {
                    // DEBET: Kas/Piutang Usaha (Aset bertambah)
                    $this->db('mlite_detailjurnal')->save([
                        'no_jurnal' => $no_jurnal_pendaftaran,
                        'kd_rek' => $this->settings('keuangan', 'akun_debet_kas'), // Setting untuk akun kas
                        'arus_kas' => '1',
                        'debet' => $jumlah,
                        'kredit' => '0'
                    ]);
                    
                    // KREDIT: Pendapatan Pendaftaran (Pendapatan bertambah)
                    $this->db('mlite_detailjurnal')->save([
                        'no_jurnal' => $no_jurnal_pendaftaran,
                        'kd_rek' => $this->settings('keuangan', 'akun_kredit_pendaftaran'),
                        'arus_kas' => '0',
                        'debet' => '0',
                        'kredit' => $jumlah
                    ]);
                }
            }
            // End jurnal_pendaftaran // 

            // jurnal_tindakan_ralan //
            if(isset($payload['jurnal_tindakan_ralan']) && $payload['jurnal_tindakan_ralan'] != '0' && $payload['jurnal_tindakan_ralan'] != 0) {
                $no_jurnal_tindakan_ralan = $this->core->setNoJurnal();
                $keterangan = $this->db('mlite_rekening')
                    ->where('kd_rek', $this->settings('keuangan', 'akun_kredit_tindakan'))
                    ->oneArray();
                $jumlah = $payload['jurnal_tindakan_ralan'];
                
                $query_jurnal_tindakan_ralan = $this->db('mlite_jurnal')->save([
                    'no_jurnal' => $no_jurnal_tindakan_ralan,
                    'no_bukti' => $payload['no_rawat'],
                    'tgl_jurnal' => date('Y-m-d'),
                    'jenis' => 'U',
                    'kegiatan' => $keterangan['nm_rek'],
                    'keterangan' => $keterangan['nm_rek'].' '.$payload['no_rawat'].'. Diposting oleh '.$this->core->getUserInfo('fullname', null, true).'.'
                ]);
                
                if($query_jurnal_tindakan_ralan) {
                    // DEBET: Kas/Piutang Usaha
                    $this->db('mlite_detailjurnal')->save([
                        'no_jurnal' => $no_jurnal_tindakan_ralan,
                        'kd_rek' => $this->settings('keuangan', 'akun_debet_kas'),
                        'arus_kas' => '1',
                        'debet' => $jumlah,
                        'kredit' => '0'
                    ]);
                    
                    // KREDIT: Pendapatan Tindakan
                    $this->db('mlite_detailjurnal')->save([
                        'no_jurnal' => $no_jurnal_tindakan_ralan,
                        'kd_rek' => $this->settings('keuangan', 'akun_kredit_tindakan'),
                        'arus_kas' => '0',
                        'debet' => '0',
                        'kredit' => $jumlah
                    ]);
                }
            }
            // End jurnal_tindakan_ralan //

            // jurnal_obat_bhp //
            if(isset($payload['jurnal_obat_bhp']) && $payload['jurnal_obat_bhp'] != '0' && $payload['jurnal_obat_bhp'] != 0) {
                $no_jurnal_obat_bhp = $this->core->setNoJurnal();
                $keterangan = $this->db('mlite_rekening')
                    ->where('kd_rek', $this->settings('keuangan', 'akun_kredit_obat_bhp'))
                    ->oneArray();
                $jumlah = $payload['jurnal_obat_bhp'];
                
                $query_jurnal_obat_bhp = $this->db('mlite_jurnal')->save([
                    'no_jurnal' => $no_jurnal_obat_bhp,
                    'no_bukti' => $payload['no_rawat'],
                    'tgl_jurnal' => date('Y-m-d'),
                    'jenis' => 'U',
                    'kegiatan' => $keterangan['nm_rek'],
                    'keterangan' => $keterangan['nm_rek'].' '.$payload['no_rawat'].'. Diposting oleh '.$this->core->getUserInfo('fullname', null, true).'.'
                ]);
                
                if($query_jurnal_obat_bhp) {
                    // DEBET: Kas/Piutang Usaha
                    $this->db('mlite_detailjurnal')->save([
                        'no_jurnal' => $no_jurnal_obat_bhp,
                        'kd_rek' => $this->settings('keuangan', 'akun_debet_kas'),
                        'arus_kas' => '1',
                        'debet' => $jumlah,
                        'kredit' => '0'
                    ]);
                    
                    // KREDIT: Pendapatan Obat dan BHP
                    $this->db('mlite_detailjurnal')->save([
                        'no_jurnal' => $no_jurnal_obat_bhp,
                        'kd_rek' => $this->settings('keuangan', 'akun_kredit_obat_bhp'),
                        'arus_kas' => '0',
                        'debet' => '0',
                        'kredit' => $jumlah
                    ]);
                }
            }
            // End jurnal_obat_bhp //

            // jurnal_laboratorium //
            if(isset($payload['jurnal_laboratorium']) && $payload['jurnal_laboratorium'] != '0' && $payload['jurnal_laboratorium'] != 0) {
                $no_jurnal_laboratorium = $this->core->setNoJurnal();
                $keterangan = $this->db('mlite_rekening')
                    ->where('kd_rek', $this->settings('keuangan', 'akun_kredit_laboratorium'))
                    ->oneArray();
                $jumlah = $payload['jurnal_laboratorium'];
                
                $query_jurnal_laboratorium = $this->db('mlite_jurnal')->save([
                    'no_jurnal' => $no_jurnal_laboratorium,
                    'no_bukti' => $payload['no_rawat'],
                    'tgl_jurnal' => date('Y-m-d'),
                    'jenis' => 'U',
                    'kegiatan' => $keterangan['nm_rek'],
                    'keterangan' => $keterangan['nm_rek'].' '.$payload['no_rawat'].'. Diposting oleh '.$this->core->getUserInfo('fullname', null, true).'.'
                ]);
                
                if($query_jurnal_laboratorium) {
                    // DEBET: Kas/Piutang Usaha
                    $this->db('mlite_detailjurnal')->save([
                        'no_jurnal' => $no_jurnal_laboratorium,
                        'kd_rek' => $this->settings('keuangan', 'akun_debet_kas'),
                        'arus_kas' => '1',
                        'debet' => $jumlah,
                        'kredit' => '0'
                    ]);
                    
                    // KREDIT: Pendapatan Laboratorium
                    $this->db('mlite_detailjurnal')->save([
                        'no_jurnal' => $no_jurnal_laboratorium,
                        'kd_rek' => $this->settings('keuangan', 'akun_kredit_laboratorium'),
                        'arus_kas' => '0',
                        'debet' => '0',
                        'kredit' => $jumlah
                    ]);
                }
            }
            // End jurnal_laboratorium //

            // jurnal_radiologi//
            if(isset($payload['jurnal_radiologi']) && $payload['jurnal_radiologi'] != '0' && $payload['jurnal_radiologi'] != 0) {
                $no_jurnal_radiologi = $this->core->setNoJurnal();
                $keterangan = $this->db('mlite_rekening')
                    ->where('kd_rek', $this->settings('keuangan', 'akun_kredit_radiologi'))
                    ->oneArray();
                $jumlah = $payload['jurnal_radiologi'];
                
                $query_jurnal_radiologi = $this->db('mlite_jurnal')->save([
                    'no_jurnal' => $no_jurnal_radiologi,
                    'no_bukti' => $payload['no_rawat'],
                    'tgl_jurnal' => date('Y-m-d'),
                    'jenis' => 'U',
                    'kegiatan' => $keterangan['nm_rek'],
                    'keterangan' => $keterangan['nm_rek'].' '.$payload['no_rawat'].'. Diposting oleh '.$this->core->getUserInfo('fullname', null, true).'.'
                ]);
                
                if($query_jurnal_radiologi) {
                    // DEBET: Kas/Piutang Usaha
                    $this->db('mlite_detailjurnal')->save([
                        'no_jurnal' => $no_jurnal_radiologi,
                        'kd_rek' => $this->settings('keuangan', 'akun_debet_kas'),
                        'arus_kas' => '1',
                        'debet' => $jumlah,
                        'kredit' => '0'
                    ]);
                    
                    // KREDIT: Pendapatan Radiologi
                    $this->db('mlite_detailjurnal')->save([
                        'no_jurnal' => $no_jurnal_radiologi,
                        'kd_rek' => $this->settings('keuangan', 'akun_kredit_radiologi'),
                        'arus_kas' => '0',
                        'debet' => '0',
                        'kredit' => $jumlah
                    ]);
                }
            }
            // End jurnal_radiologi //
        }

        unset($payload['jurnal_pendaftaran']);
        unset($payload['jurnal_tindakan_ralan']);
        unset($payload['jurnal_obat_bhp']);
        unset($payload['jurnal_laboratorium']);
        unset($payload['jurnal_radiologi']);

        // Pastikan tidak ada field lain yang tidak dikenali oleh tabel mlite_billing
        // Filter payload hanya untuk kolom yang ada (jika kita tahu strukturnya, atau biarkan DB wrapper menangani)
        // Namun, jika DB wrapper menghapus field unknown tanpa error, maka insert bisa berhasil tapi datanya kosong.
        
        // Cek apakah data sudah ada sebelumnya? (Mencegah duplikat jika no_rawat+kd_billing unik?)
        // kd_billing baru digenerate, jadi harusnya unik.

        // echo json_encode(['status' => 'error', 'message' => json_encode($payload)]);
        try {
            $query = $this->db('mlite_billing')->save([
                'kd_billing' => $payload['kd_billing'],
                'no_rawat' => $payload['no_rawat'],
                'jumlah_total' => $payload['jumlah_harus_bayar'],
                'potongan' => $payload['potongan'],
                'jumlah_harus_bayar' => $payload['jumlah_harus_bayar'] - $payload['potongan'],
                'jumlah_bayar' => $payload['bayar'],
                'tgl_billing' => $payload['tgl_billing'],
                'jam_billing' => $payload['jam_billing'], 
                'id_user' => $payload['id_user'],
            ]);
            if($query) {
                $this->db('reg_periksa')->where('no_rawat', $payload['no_rawat'])->update(['status_bayar' => 'Sudah Bayar']);
                echo json_encode(['status' => 'success', 'message' => 'Pembayaran berhasil disimpan']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan pembayaran']);
            }
        } catch (\PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit();
    }

    public function apiHapusItem()
    {
        try {
            $payload = json_decode(file_get_contents('php://input'), true);
            $type = $payload['type'] ?? '';
            
            if ($type === 'tindakan') {
                if($payload['provider'] == 'rawat_jl_dr') {
                    $this->db('rawat_jl_dr')
                    ->where('no_rawat', $payload['no_rawat'])
                    ->where('kd_jenis_prw', $payload['kd_jenis_prw'])
                    ->where('tgl_perawatan', $payload['tgl_perawatan'])
                    ->where('jam_rawat', $payload['jam_rawat'])
                    ->delete();
                }
                if($payload['provider'] == 'rawat_jl_pr') {
                    $this->db('rawat_jl_pr')
                    ->where('no_rawat', $payload['no_rawat'])
                    ->where('kd_jenis_prw', $payload['kd_jenis_prw'])
                    ->where('tgl_perawatan', $payload['tgl_perawatan'])
                    ->where('jam_rawat', $payload['jam_rawat'])
                    ->delete();
                }
                if($payload['provider'] == 'rawat_jl_drpr') {
                    $this->db('rawat_jl_drpr')
                    ->where('no_rawat', $payload['no_rawat'])
                    ->where('kd_jenis_prw', $payload['kd_jenis_prw'])
                    ->where('tgl_perawatan', $payload['tgl_perawatan'])
                    ->where('jam_rawat', $payload['jam_rawat'])
                    ->delete();
                }
            }
            
            if ($type === 'obat') {
                $payload['jml'] = $payload['jml'] ?? $payload['jumlah'] ?? 0;
                $kd_bangsal = $payload['kd_bangsal'] ?? $this->settings->get('farmasi.deporalan');
                $get_gudangbarang = $this->db('gudangbarang')->where('kode_brng', $payload['kode_brng'])->where('kd_bangsal', $kd_bangsal)->oneArray();

                $this->db('gudangbarang')
                    ->where('kode_brng', $payload['kode_brng'])
                    ->where('kd_bangsal', $kd_bangsal)
                    ->update([
                        'stok' => $get_gudangbarang['stok'] + $payload['jml']
                    ]);

                $this->db('riwayat_barang_medis')
                    ->save([
                        'kode_brng' => $payload['kode_brng'],
                        'stok_awal' => $get_gudangbarang['stok'],
                        'masuk' => $payload['jml'],
                        'keluar' => '0',
                        'stok_akhir' => $get_gudangbarang['stok'] + $payload['jml'],
                        'posisi' => 'Pemberian Obat',
                        'tanggal' => $payload['tgl_peresepan'],
                        'jam' => $payload['jam_peresepan'],
                        'petugas' => $this->core->getUserInfo('fullname', null, true),
                        'kd_bangsal' => $kd_bangsal,
                        'status' => 'Hapus',
                        'no_batch' => $get_gudangbarang['no_batch'],
                        'no_faktur' => $get_gudangbarang['no_faktur'],
                        'keterangan' => $payload['no_rawat'] . ' ' . $this->core->getRegPeriksaInfo('no_rkm_medis', $payload['no_rawat']) . ' ' . $this->core->getPasienInfo('nm_pasien', $this->core->getRegPeriksaInfo('no_rkm_medis', $payload['no_rawat']))
                    ]);

                $this->db('detail_pemberian_obat')
                    ->where('tgl_perawatan', $payload['tgl_peresepan'])
                    ->where('jam', $payload['jam_peresepan'])
                    ->where('no_rawat', $payload['no_rawat'])
                    ->where('kode_brng', $payload['kode_brng'])
                    ->where('jml', $payload['jml'])
                    ->where('status', 'Ralan')
                    ->where('kd_bangsal', $kd_bangsal)
                    ->delete();
            }
            
            if ($type === 'lab') {
                $this->db('periksa_lab')
                ->where('no_rawat', $payload['no_rawat'])
                ->where('kd_jenis_prw', $payload['kd_jenis_prw'])
                ->where('tgl_periksa', $payload['tgl_perawatan'])
                ->where('jam', $payload['jam_rawat'])
                ->where('status', 'Ralan')
                ->delete();
            }
            
            if ($type === 'rad') {
                $this->db('periksa_radiologi')
                ->where('no_rawat', $payload['no_rawat'])
                ->where('kd_jenis_prw', $payload['kd_jenis_prw'])
                ->where('tgl_periksa', $payload['tgl_perawatan'])
                ->where('jam', $payload['jam_rawat'])
                ->where('status', 'Ralan')
                ->delete();
            }

            echo json_encode(['status' => 'success', 'message' => 'Item berhasil dihapus ' . json_encode($payload)]);

        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function apiSimpanItem()
    {
        $payload = json_decode(file_get_contents('php://input'), true);
        $kat = $payload['kat'] ?? '';

        try {
            if($kat == 'tindakan') {
                $jns_perawatan = $this->db('jns_perawatan')->where('kd_jenis_prw', $payload['kd_jenis_prw'])->oneArray();
                if($payload['provider'] == 'rawat_jl_dr') {
                    for ($i = 0; $i < $payload['jml_tindakan']; $i++) {
                        $this->db('rawat_jl_dr')->save([
                            'no_rawat' => $payload['no_rawat'],
                            'kd_jenis_prw' => $payload['kd_jenis_prw'],
                            'kd_dokter' => $payload['kode_provider'],
                            'tgl_perawatan' => $payload['tgl_perawatan'],
                            'jam_rawat' => date('H:i:s', strtotime($payload['jam_rawat']. ' +'.$i.'0 seconds')),
                            'material' => $jns_perawatan['material'],
                            'bhp' => $jns_perawatan['bhp'],
                            'tarif_tindakandr' => $jns_perawatan['tarif_tindakandr'],
                            'kso' => $jns_perawatan['kso'],
                            'menejemen' => $jns_perawatan['menejemen'],
                            'biaya_rawat' => $jns_perawatan['total_byrdr'],
                            'stts_bayar' => 'Belum'
                        ]);
                    }
                }
                if($payload['provider'] == 'rawat_jl_pr') {
                    for ($i = 0; $i < $payload['jml_tindakan']; $i++) {
                        $this->db('rawat_jl_pr')->save([
                            'no_rawat' => $payload['no_rawat'],
                            'kd_jenis_prw' => $payload['kd_jenis_prw'],
                            'nip' => $payload['kode_provider2'],
                            'tgl_perawatan' => $payload['tgl_perawatan'],
                            'jam_rawat' => date('H:i:s', strtotime($payload['jam_rawat']. ' +'.$i.'0 seconds')),
                            'material' => $jns_perawatan['material'],
                            'bhp' => $jns_perawatan['bhp'],
                            'tarif_tindakanpr' => $jns_perawatan['tarif_tindakanpr'],
                            'kso' => $jns_perawatan['kso'],
                            'menejemen' => $jns_perawatan['menejemen'],
                            'biaya_rawat' => $jns_perawatan['total_byrpr'],
                            'stts_bayar' => 'Belum'
                        ]);
                    }
                }
                if($payload['provider'] == 'rawat_jl_drpr') {
                    for ($i = 0; $i < $payload['jml_tindakan']; $i++) {
                        $this->db('rawat_jl_drpr')->save([
                            'no_rawat' => $payload['no_rawat'],
                            'kd_jenis_prw' => $payload['kd_jenis_prw'],
                            'kd_dokter' => $payload['kode_provider'],
                            'nip' => $payload['kode_provider2'],
                            'tgl_perawatan' => $payload['tgl_perawatan'],
                            'jam_rawat' => date('H:i:s', strtotime($payload['jam_rawat']. ' +'.$i.'0 seconds')),
                            'material' => $jns_perawatan['material'],
                            'bhp' => $jns_perawatan['bhp'],
                            'tarif_tindakandr' => $jns_perawatan['tarif_tindakandr'],
                            'tarif_tindakanpr' => $jns_perawatan['tarif_tindakanpr'],
                            'kso' => $jns_perawatan['kso'],
                            'menejemen' => $jns_perawatan['menejemen'],
                            'biaya_rawat' => $jns_perawatan['total_byrdrpr'],
                            'stts_bayar' => 'Belum'
                        ]);
                    }
                }
            }

            if($kat == 'obat') {
                $get_gudangbarang = $this->db('gudangbarang')->where('kode_brng', $payload['kd_jenis_prw'])->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))->oneArray();
                $get_databarang = $this->db('databarang')->where('kode_brng', $payload['kd_jenis_prw'])->oneArray();

                $this->db('gudangbarang')
                    ->where('kode_brng', $payload['kd_jenis_prw'])
                    ->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))
                    ->update([
                        'stok' => $get_gudangbarang['stok'] - $payload['jml']
                    ]);

                $this->db('riwayat_barang_medis')
                    ->save([
                        'kode_brng' => $payload['kd_jenis_prw'],
                        'stok_awal' => $get_gudangbarang['stok'],
                        'masuk' => '0',
                        'keluar' => $payload['jml'],
                        'stok_akhir' => $get_gudangbarang['stok'] - $payload['jml'],
                        'posisi' => 'Pemberian Obat',
                        'tanggal' => $payload['tgl_perawatan'],
                        'jam' => $payload['jam_rawat'],
                        'petugas' => $this->core->getUserInfo('fullname', null, true),
                        'kd_bangsal' => $this->settings->get('farmasi.deporalan'),
                        'status' => 'Simpan',
                        'no_batch' => $get_gudangbarang['no_batch'],
                        'no_faktur' => $get_gudangbarang['no_faktur'],
                        'keterangan' => $payload['no_rawat'] . ' ' . $this->core->getRegPeriksaInfo('no_rkm_medis', $payload['no_rawat']) . ' ' . $this->core->getPasienInfo('nm_pasien', $this->core->getRegPeriksaInfo('no_rkm_medis', $payload['no_rawat']))
                    ]);

                $this->db('detail_pemberian_obat')
                    ->save([
                        'tgl_perawatan' => $payload['tgl_perawatan'],
                        'jam' => $payload['jam_rawat'],
                        'no_rawat' => $payload['no_rawat'],
                        'kode_brng' => $payload['kd_jenis_prw'],
                        'h_beli' => $get_databarang['h_beli'],
                        'biaya_obat' => $payload['biaya'],
                        'jml' => $payload['jml'],
                        'embalase' => '0',
                        'tuslah' => '0',
                        'total' => $payload['biaya'] * $payload['jml'],
                        'status' => 'Ralan',
                        'kd_bangsal' => $this->settings->get('farmasi.deporalan'),
                        'no_batch' => $get_gudangbarang['no_batch'],
                        'no_faktur' => $get_gudangbarang['no_faktur']
                    ]);

                if (isset($payload['aturan_pakai'])) {
                    $this->db('aturan_pakai')
                    ->save([
                        'tgl_perawatan' => $payload['tgl_perawatan'],
                        'jam' => $payload['jam_rawat'],
                        'no_rawat' => $payload['no_rawat'],
                        'kode_brng' => $payload['kd_jenis_prw'],
                        'aturan' => $payload['aturan_pakai']
                    ]);
                }
            }

            if($kat == 'racikan') {
                $no_racik = $this->db('obat_racikan')->where('no_rawat', $payload['no_rawat'])->where('tgl_perawatan', $payload['tgl_perawatan'])->count();
                $no_racik = $no_racik+1;
                $this->db('obat_racikan')
                  ->save([
                    'tgl_perawatan' => $payload['tgl_perawatan'],
                    'jam' => $payload['jam_rawat'],
                    'no_rawat' => $payload['no_rawat'],
                    'no_racik' => $no_racik,
                    'nama_racik' => $payload['nama_racik'],
                    'kd_racik' => $payload['kd_jenis_prw'],
                    'jml_dr' => $payload['jml'],
                    'aturan_pakai' => $payload['aturan_pakai'],
                    'keterangan' => $payload['keterangan']
                  ]);
                
                $items = $payload['items'] ?? [];
                
                foreach ($items as $item) {
                  $get_gudangbarang = $this->db('gudangbarang')->where('kode_brng', $item['kode_brng'])->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))->oneArray();
                  $kapasitas = $this->db('databarang')->where('kode_brng', $item['kode_brng'])->oneArray();
                  
                  // Calculate total quantity needed: (packets * content per packet) / capacity
                  $jml = $payload['jml'] * $item['kandungan'];
                  $jml = round(($jml / ($kapasitas['kapasitas'] > 0 ? $kapasitas['kapasitas'] : 1)), 1);

                  $this->db('gudangbarang')
                  ->where('kode_brng', $item['kode_brng'])
                  ->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))
                  ->update([
                    'stok' => $get_gudangbarang['stok'] - $jml
                  ]);

                  $this->db('riwayat_barang_medis')
                    ->save([
                      'kode_brng' => $item['kode_brng'],
                      'stok_awal' => $get_gudangbarang['stok'],
                      'masuk' => '0',
                      'keluar' => $jml,
                      'stok_akhir' => $get_gudangbarang['stok'] - $jml,
                      'posisi' => 'Pemberian Obat',
                      'tanggal' => $payload['tgl_perawatan'],
                      'jam' => $payload['jam_rawat'],
                      'petugas' => $this->core->getUserInfo('fullname', null, true),
                      'kd_bangsal' => $this->settings->get('farmasi.deporalan'),
                      'status' => 'Simpan',
                      'no_batch' => $get_gudangbarang['no_batch'],
                      'no_faktur' => $get_gudangbarang['no_faktur'],
                      'keterangan' => $payload['no_rawat'] . ' ' . $this->core->getRegPeriksaInfo('no_rkm_medis', $payload['no_rawat']) . ' ' . $this->core->getPasienInfo('nm_pasien', $this->core->getRegPeriksaInfo('no_rkm_medis', $payload['no_rawat']))
                    ]);

                  $this->db('detail_pemberian_obat')
                    ->save([
                      'tgl_perawatan' => $payload['tgl_perawatan'],
                      'jam' => $payload['jam_rawat'],
                      'no_rawat' => $payload['no_rawat'],
                      'kode_brng' => $item['kode_brng'],
                      'h_beli' => $kapasitas['h_beli'],
                      'biaya_obat' => $kapasitas['dasar'],
                      'jml' => $jml,
                      'embalase' => '0',
                      'tuslah' => '0',
                      'total' => $kapasitas['dasar'] * $jml,
                      'status' => 'Ralan',
                      'kd_bangsal' => $this->settings->get('farmasi.deporalan'),
                      'no_batch' => $get_gudangbarang['no_batch'],
                      'no_faktur' => $get_gudangbarang['no_faktur']
                    ]);

                  $this->db('detail_obat_racikan')
                    ->save([
                      'tgl_perawatan' => $payload['tgl_perawatan'],
                      'jam' => $payload['jam_rawat'],
                      'no_rawat' => $payload['no_rawat'],
                      'no_racik' => $no_racik,
                      'kode_brng' => $item['kode_brng']
                    ]);          
                }        
            }

            if($kat == 'laboratorium') {
                $jns_perawatan = $this->db('jns_perawatan_lab')->where('kd_jenis_prw', $payload['kd_jenis_prw'])->oneArray();
                $this->db('periksa_lab')
                  ->save([
                    'no_rawat' => $payload['no_rawat'],
                    'nip' => $payload['kode_provider2'],
                    'kd_jenis_prw' => $payload['kd_jenis_prw'],
                    'tgl_periksa' => $payload['tgl_perawatan'],
                    'jam' => $payload['jam_rawat'],
                    'dokter_perujuk' => $payload['kode_provider'],
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

            if($kat == 'radiologi') {
                $jns_perawatan = $this->db('jns_perawatan_radiologi')->where('kd_jenis_prw', $payload['kd_jenis_prw'])->oneArray();
                $this->db('periksa_radiologi')
                  ->save([
                    'no_rawat' => $payload['no_rawat'],
                    'nip' => $payload['kode_provider2'],
                    'kd_jenis_prw' => $payload['kd_jenis_prw'],
                    'tgl_periksa' => $payload['tgl_perawatan'],
                    'jam' => $payload['jam_rawat'],
                    'dokter_perujuk' => $payload['kode_provider'],
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

            echo json_encode(['status' => 'success', 'message' => 'Item berhasil ditambahkan']);
        } catch (\PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
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
        $result = $this->db('mlite_billing')->where('no_rawat', $_GET['no_rawat'])->like('kd_billing', 'RJ%')->desc('id_billing')->oneArray();

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

        // Exclude racikan items from detail_pemberian_obat
        $exclude_jams = [];
        $racikan_jams = $this->db('detail_obat_racikan')->where('no_rawat', $_GET['no_rawat'])->toArray();
        foreach($racikan_jams as $rj) {
            $exclude_jams[] = $rj['jam'];
        }

        $query_pemberian = $this->db('detail_pemberian_obat')
          ->join('databarang', 'databarang.kode_brng=detail_pemberian_obat.kode_brng')
          ->where('no_rawat', $_GET['no_rawat'])
          ->where('detail_pemberian_obat.status', 'Ralan');
        
        if(!empty($exclude_jams)) {
            $query_pemberian->notIn('jam', $exclude_jams);
        }

        $result_detail['detail_pemberian_obat'] = $query_pemberian->toArray();

        // Fetch racikan items
        $rows_obat_racikan = $this->db('obat_racikan')
          ->where('no_rawat', $_GET['no_rawat'])
          ->toArray();

        foreach ($rows_obat_racikan as $header) {
          $ingredients_map = $this->db('detail_obat_racikan')
            ->where('no_rawat', $header['no_rawat'])
            ->where('no_racik', $header['no_racik'])
            ->toArray();

          $total_racikan = 0;
          $ingredient_rows = [];

          foreach ($ingredients_map as $map) {
            $row = $this->db('detail_pemberian_obat')
                ->join('databarang', 'databarang.kode_brng=detail_pemberian_obat.kode_brng')
                ->where('detail_pemberian_obat.no_rawat', $map['no_rawat'])
                ->where('detail_pemberian_obat.kode_brng', $map['kode_brng'])
                ->where('detail_pemberian_obat.tgl_perawatan', $map['tgl_perawatan'])
                ->where('detail_pemberian_obat.jam', $map['jam'])
                ->where('detail_pemberian_obat.status', 'Ralan')
                ->oneArray();

            if ($row) {
                $total_racikan += $row['total'];

                $row['nama_brng'] = "&nbsp;&nbsp;&nbsp;&nbsp; - " . $row['nama_brng'];
                $row['total'] = 0;
                $row['jml'] = 0;
                $row['biaya_obat'] = 0;
                $ingredient_rows[] = $row;
            }
          }

          $header_row = [];
          $header_row['nama_brng'] = ">> Racikan " . $header['no_racik'] . ": " . $header['nama_racik'] . " (" . $header['jml_dr'] . " Bungkus)";
          $header_row['jml'] = $header['jml_dr'];
          
          if ($header['jml_dr'] > 0) {
              $header_row['biaya_obat'] = $total_racikan / $header['jml_dr'];
          } else {
              $header_row['biaya_obat'] = $total_racikan;
          }

          $header_row['total'] = $total_racikan;
          
          $result_detail['detail_pemberian_obat'][] = $header_row;
          
          foreach ($ingredient_rows as $row) {
              $result_detail['detail_pemberian_obat'][] = $row;
          }
        }

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

        $jumlah_total_operasi = 0;
        $operasis = $this->db('operasi')->join('paket_operasi', 'paket_operasi.kode_paket=operasi.kode_paket')->where('no_rawat', $_GET['no_rawat'])->where('operasi.status', 'Ralan')->toArray();
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


        /* ===============================
        * QR CODE
        * =============================== */
        $qr = QRCode::getMinimumQRCode(
            $this->core->getUserInfo('fullname', null, true),
            QR_ERROR_CORRECT_LEVEL_L
        );
        $im = $qr->createImage(4,4);
        imagepng($im, BASE_DIR.'/'.ADMIN.'/tmp/qrcode.png');
        imagedestroy($im);

        $qrCode = url()."/".ADMIN."/tmp/qrcode.png";

        /* ===============================
        * PREPARE TEMPLATE DATA
        * =============================== */
        $this->tpl->set('billing_obat', $this->settings->get('settings.billing_obat'));
        $this->tpl->set('wagateway', $this->settings->get('wagateway'));
        $this->tpl->set('billing', $result);
        $this->tpl->set('total_billing_obat', $total_detail_pemberian_obat);
        $this->tpl->set('billing_besar_detail', $result_detail);
        $this->tpl->set('pasien', $pasien);
        $this->tpl->set('qrCode', $qrCode);
        $this->tpl->set('fullname', $this->core->getUserInfo('fullname', null, true));

        /* ===============================
        * RENDER HTML SEKALI
        * =============================== */
        $html = $this->draw('billing.besar.html');

        /* ===============================
        * GENERATE PDF
        * =============================== */
        if (file_exists(UPLOADS.'/invoices/'.$result['kd_billing'].'.pdf')) {
            unlink(UPLOADS.'/invoices/'.$result['kd_billing'].'.pdf');
        }

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P'
        ]);

        $mpdf->WriteHTML(
            $this->core->setPrintCss(),
            \Mpdf\HTMLParserMode::HEADER_CSS
        );

        $mpdf->WriteHTML($html);

        $mpdf->Output(
            UPLOADS.'/invoices/'.$result['kd_billing'].'.pdf',
            'F'
        );

        /* ===============================
        * OUTPUT HTML (PREVIEW)
        * =============================== */
        echo $html;
        break;
        case "kecil":
        $result = $this->db('mlite_billing')->where('no_rawat', $_GET['no_rawat'])->like('kd_billing', 'RJ%')->desc('id_billing')->oneArray();
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
         throw new \Exception("Could not fetch remote content from: '$file'");
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

    public function postCekWaktu()
    {
      echo date('H:i:s');
      exit();
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

    public function anyShift()
    {
        $this->_addHeaderFiles();
        $user_id = $this->core->getUserInfo('id');
        $open_shift = $this->db('mlite_kasir_shift')->where('user_id', $user_id)->isNull('waktu_tutup')->desc('id_shift')->oneArray();
        return $this->draw('shift.html', ['open_shift' => $open_shift]);
    }

    public function postOpenKasir()
    {
        $user_id = $this->core->getUserInfo('id');
        $cek = $this->db('mlite_kasir_shift')->where('user_id', $user_id)->isNull('waktu_tutup')->oneArray();
        if ($cek) {
            $this->notify('danger', 'Shift kasir masih terbuka');
            redirect(url([ADMIN, 'kasir_rawat_jalan', 'shift']));
        }
        $kas_awal = floatval($_POST['kas_awal'] ?? 0);
        $this->db('mlite_kasir_shift')->save([
            'user_id' => $user_id,
            'waktu_buka' => date('Y-m-d H:i:s'),
            'waktu_tutup' => null,
            'kas_awal' => $kas_awal,
            'keterangan' => $_POST['keterangan'] ?? ''
        ]);
        $this->notify('success', 'Shift kasir dibuka');
        redirect(url([ADMIN, 'kasir_rawat_jalan', 'shift']));
    }

    public function postCloseKasir()
    {
        $user_id = $this->core->getUserInfo('id');
        $shift = $this->db('mlite_kasir_shift')->where('user_id', $user_id)->isNull('waktu_tutup')->desc('id_shift')->oneArray();
        if (!$shift) {
            $this->notify('danger', 'Tidak ada shift terbuka');
            redirect(url([ADMIN, 'kasir_rawat_jalan', 'shift']));
        }
        $kas_akhir = floatval($_POST['kas_akhir'] ?? 0);
        $pdo = $this->db()->pdo();
        $stmt = $pdo->prepare("SELECT IFNULL(SUM(jumlah_harus_bayar),0) as total FROM mlite_billing WHERE id_user = ? AND CONCAT(tgl_billing,' ',jam_billing) BETWEEN ? AND ?");
        $stmt->execute([$user_id, $shift['waktu_buka'], date('Y-m-d H:i:s')]);
        $row = $stmt->fetch();
        $total = floatval($row[0] ?? 0);
        $selisih = $kas_akhir - ($shift['kas_awal'] + $total);
        $this->db('mlite_kasir_shift')->where('id_shift', $shift['id_shift'])->save([
            'waktu_tutup' => date('Y-m-d H:i:s'),
            'kas_akhir' => $kas_akhir,
            'total_transaksi' => $total,
            'selisih' => $selisih
        ]);
        $this->notify('success', 'Shift kasir ditutup');
        redirect(url([ADMIN, 'kasir_rawat_jalan', 'shift']));
    }

    public function anyReport()
    {
        $this->_addHeaderFiles();
        $awal = isset($_GET['awal']) ? $_GET['awal'] : date('Y-m-d').' 00:00:00';
        $akhir = isset($_GET['akhir']) ? $_GET['akhir'] : date('Y-m-d').' 23:59:59';
        $pdo = $this->db()->pdo();
        $stmt = $pdo->prepare("SELECT b.id_user, COALESCE(p.nama, u.fullname) AS nama_kasir, COUNT(*) transaksi, IFNULL(SUM(b.jumlah_harus_bayar),0) total, MIN(CONCAT(b.tgl_billing,' ',b.jam_billing)) mulai, MAX(CONCAT(b.tgl_billing,' ',b.jam_billing)) selesai FROM mlite_billing b INNER JOIN ( SELECT no_rawat, MAX(CONCAT(tgl_billing,' ',jam_billing)) AS max_waktu FROM mlite_billing WHERE kd_billing LIKE 'RJ%' AND CONCAT(tgl_billing,' ',jam_billing) BETWEEN ? AND ? GROUP BY no_rawat ) latest ON latest.no_rawat=b.no_rawat AND CONCAT(b.tgl_billing,' ',b.jam_billing)=latest.max_waktu LEFT JOIN mlite_users u ON u.id=b.id_user LEFT JOIN pegawai p ON p.nik=u.username WHERE b.kd_billing LIKE 'RJ%' GROUP BY b.id_user, nama_kasir");
        $stmt->execute([$awal, $akhir]);
        $rows = $stmt->fetchAll();

        $detailStmt = $pdo->prepare("SELECT b.id_user, COALESCE(p.nama, u.fullname) AS nama_kasir, b.kd_billing, b.no_rawat, b.jumlah_harus_bayar, CONCAT(b.tgl_billing,' ',b.jam_billing) AS waktu, b.keterangan FROM mlite_billing b INNER JOIN ( SELECT no_rawat, MAX(CONCAT(tgl_billing,' ',jam_billing)) AS max_waktu FROM mlite_billing WHERE kd_billing LIKE 'RJ%' AND CONCAT(tgl_billing,' ',jam_billing) BETWEEN ? AND ? GROUP BY no_rawat ) latest ON latest.no_rawat=b.no_rawat AND CONCAT(b.tgl_billing,' ',b.jam_billing)=latest.max_waktu LEFT JOIN mlite_users u ON u.id=b.id_user LEFT JOIN pegawai p ON p.nik=u.username WHERE b.kd_billing LIKE 'RJ%' ORDER BY waktu ASC");
        $detailStmt->execute([$awal, $akhir]);
        $details = $detailStmt->fetchAll();

        return $this->draw('report.html', ['awal' => $awal, 'akhir' => $akhir, 'rows' => $rows, 'details' => $details]);
    }

    public function anyReportExport()
    {
        $awal = isset($_GET['awal']) ? $_GET['awal'] : date('Y-m-d').' 00:00:00';
        $akhir = isset($_GET['akhir']) ? $_GET['akhir'] : date('Y-m-d').' 23:59:59';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="laporan_kasir.csv"');
        $pdo = $this->db()->pdo();
        $stmt = $pdo->prepare("SELECT b.id_user, COALESCE(p.nama, u.fullname) AS nama_kasir, b.kd_billing, b.jumlah_harus_bayar, CONCAT(b.tgl_billing,' ',b.jam_billing) waktu FROM mlite_billing b INNER JOIN ( SELECT no_rawat, MAX(CONCAT(tgl_billing,' ',jam_billing)) AS max_waktu FROM mlite_billing WHERE kd_billing LIKE 'RJ%' AND CONCAT(tgl_billing,' ',jam_billing) BETWEEN ? AND ? GROUP BY no_rawat ) latest ON latest.no_rawat=b.no_rawat AND CONCAT(b.tgl_billing,' ',b.jam_billing)=latest.max_waktu LEFT JOIN mlite_users u ON u.id=b.id_user LEFT JOIN pegawai p ON p.nik=u.username WHERE b.kd_billing LIKE 'RJ%' ORDER BY waktu ASC");
        $stmt->execute([$awal, $akhir]);
        echo "id_user,nama_kasir,kd_billing,jumlah_harus_bayar,waktu\n";
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            echo $row['id_user'].",".($row['nama_kasir'] ?? '').",".$row['kd_billing'].",".$row['jumlah_harus_bayar'].",".$row['waktu']."\n";
        }
        exit();
    }

}
