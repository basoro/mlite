<?php
namespace Plugins\Kasir_Rawat_Inap;

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
        if (!$this->core->checkPermission($username, 'can_read', 'kasir_rawat_inap')) {
            echo json_encode(['status' => 'error', 'message' => 'You do not have permission to access this resource']);
            exit;
        }
        
        $page = $_GET['page'] ?? 1;
        $per_page = $_GET['per_page'] ?? 10;
        $offset = ($page - 1) * $per_page;
        $search = $_GET['s'] ?? '';
        $tgl_awal = $_GET['tgl_awal'] ?? date('Y-m-d');
        $tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');

        $query = $this->db('kamar_inap')
            ->join('reg_periksa', 'reg_periksa.no_rawat = kamar_inap.no_rawat')
            ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
            ->join('kamar', 'kamar.kd_kamar = kamar_inap.kd_kamar')
            ->join('bangsal', 'bangsal.kd_bangsal = kamar.kd_bangsal')
            ->where('kamar_inap.tgl_masuk', '>=', $tgl_awal)
            ->where('kamar_inap.tgl_masuk', '<=', $tgl_akhir);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('reg_periksa.no_rawat', 'LIKE', "%$search%")
                  ->orWhere('pasien.nm_pasien', 'LIKE', "%$search%")
                  ->orWhere('pasien.no_rkm_medis', 'LIKE', "%$search%");
            });
        }

        $total = $query->count();
        $data = $query->select('kamar_inap.*')
            ->select('reg_periksa.no_rkm_medis')
            ->select('pasien.nm_pasien')
            ->select('kamar.kd_kamar')
            ->select('bangsal.nm_bangsal')
            ->offset($offset)
            ->limit($per_page)
            ->desc('kamar_inap.tgl_masuk')
            ->desc('kamar_inap.jam_masuk')
            ->toArray();

        foreach ($data as &$row) {
             $billing = $this->db('mlite_billing')->where('no_rawat', $row['no_rawat'])->like('kd_billing', 'RI%')->oneArray();
             $row['total_tagihan'] = $billing ? $billing['jumlah_harus_bayar'] : 0;
             $row['status_bayar'] = $this->db('reg_periksa')->where('no_rawat', $row['no_rawat'])->oneArray()['status_bayar'] ?? '';
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
        if (!$this->core->checkPermission($username, 'can_read', 'kasir_rawat_inap')) {
             echo json_encode(['status' => 'error', 'message' => 'You do not have permission to access this resource']);
             exit;
        }
        $no_rawat = revertNorawat($no_rawat);

        // Basic Info
        $reg_periksa = $this->db('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
            ->where('reg_periksa.no_rawat', $no_rawat)
            ->oneArray();

        if (!$reg_periksa) {
            echo json_encode(['status' => 'error', 'message' => 'Data not found']);
            exit;
        }
        
        $kamar_inap = $this->db('kamar_inap')
            ->join('kamar', 'kamar.kd_kamar = kamar_inap.kd_kamar')
            ->join('bangsal', 'bangsal.kd_bangsal = kamar.kd_bangsal')
            ->where('no_rawat', $no_rawat)
            ->desc('tgl_masuk')
            ->limit(1)
            ->oneArray();

        $details = [];
        $total_biaya = 0;

        // 1. Registrasi (Usually for Ranap, is there registration fee? Maybe just Room charge)
        // Check if there is a 'registrasi' component or if it's covered by Kamar
        
        // 2. Kamar (Room Charge)
        if ($kamar_inap) {
             // Calculate days
             $masuk = new \DateTime($kamar_inap['tgl_masuk'] . ' ' . $kamar_inap['jam_masuk']);
             $keluar = $kamar_inap['tgl_keluar'] == '0000-00-00' ? new \DateTime() : new \DateTime($kamar_inap['tgl_keluar'] . ' ' . $kamar_inap['jam_keluar']);
             $interval = $masuk->diff($keluar);
             $days = $interval->days;
             if ($days == 0) $days = 1; // Minimum 1 day
             
             $biaya_kamar = $kamar_inap['trf_kamar'];
             $total_kamar = $biaya_kamar * $days;
             
             $details[] = [
                'kategori' => 'Kamar',
                'nama' => $kamar_inap['nm_bangsal'] . ' (' . $days . ' hari)',
                'biaya' => $biaya_kamar,
                'jumlah' => $days,
                'subtotal' => $total_kamar
             ];
             $total_biaya += $total_kamar;
        }

        // 3. Tindakan Dokter
        $tindakan_dr = $this->db('rawat_inap_dr')
            ->join('jns_perawatan_inap', 'jns_perawatan_inap.kd_jenis_prw = rawat_inap_dr.kd_jenis_prw')
            ->where('no_rawat', $no_rawat)->toArray();
        foreach ($tindakan_dr as $t) {
            $details[] = [
                'kategori' => 'Tindakan Dokter',
                'nama' => $t['nm_perawatan'],
                'biaya' => $t['biaya_rawat'],
                'jumlah' => 1,
                'subtotal' => $t['biaya_rawat']
            ];
            $total_biaya += $t['biaya_rawat'];
        }

        // 4. Tindakan Perawat
        $tindakan_pr = $this->db('rawat_inap_pr')
            ->join('jns_perawatan_inap', 'jns_perawatan_inap.kd_jenis_prw = rawat_inap_pr.kd_jenis_prw')
            ->where('no_rawat', $no_rawat)->toArray();
        foreach ($tindakan_pr as $t) {
            $details[] = [
                'kategori' => 'Tindakan Perawat',
                'nama' => $t['nm_perawatan'],
                'biaya' => $t['biaya_rawat'],
                'jumlah' => 1,
                'subtotal' => $t['biaya_rawat']
            ];
            $total_biaya += $t['biaya_rawat'];
        }

        // 5. Tindakan Dokter & Perawat
        $tindakan_drpr = $this->db('rawat_inap_drpr')
            ->join('jns_perawatan_inap', 'jns_perawatan_inap.kd_jenis_prw = rawat_inap_drpr.kd_jenis_prw')
            ->where('no_rawat', $no_rawat)->toArray();
        foreach ($tindakan_drpr as $t) {
            $details[] = [
                'kategori' => 'Tindakan Dokter & Perawat',
                'nama' => $t['nm_perawatan'],
                'biaya' => $t['biaya_rawat'],
                'jumlah' => 1,
                'subtotal' => $t['biaya_rawat']
            ];
            $total_biaya += $t['biaya_rawat'];
        }

        // 6. Obat & BHP
        $obat = $this->db('detail_pemberian_obat')
            ->join('databarang', 'databarang.kode_brng = detail_pemberian_obat.kode_brng')
            ->where('no_rawat', $no_rawat)
            ->where('status', 'Ranap')
            ->toArray();
            
        foreach ($obat as $o) {
            $details[] = [
                'kategori' => 'Obat & BHP',
                'nama' => $o['nama_brng'],
                'biaya' => $o['biaya_obat'],
                'jumlah' => $o['jml'],
                'subtotal' => $o['total'] + $o['embalase'] + $o['tuslah']
            ];
            $total_biaya += ($o['total'] + $o['embalase'] + $o['tuslah']);
        }

        // 7. Laboratorium
        $lab = $this->db('periksa_lab')
            ->join('jns_perawatan_lab', 'jns_perawatan_lab.kd_jenis_prw = periksa_lab.kd_jenis_prw')
            ->where('no_rawat', $no_rawat)
            ->where('periksa_lab.status', 'Ranap')
            ->toArray();
        foreach ($lab as $l) {
            $details[] = [
                'kategori' => 'Laboratorium',
                'nama' => $l['nm_perawatan'],
                'biaya' => $l['biaya'],
                'jumlah' => 1,
                'subtotal' => $l['biaya']
            ];
            $total_biaya += $l['biaya'];
        }

        // 8. Radiologi
        $rad = $this->db('periksa_radiologi')
            ->join('jns_perawatan_radiologi', 'jns_perawatan_radiologi.kd_jenis_prw = periksa_radiologi.kd_jenis_prw')
            ->where('no_rawat', $no_rawat)
            ->where('periksa_radiologi.status', 'Ranap')
            ->toArray();
        foreach ($rad as $r) {
            $details[] = [
                'kategori' => 'Radiologi',
                'nama' => $r['nm_perawatan'],
                'biaya' => $r['biaya'],
                'jumlah' => 1,
                'subtotal' => $r['biaya']
            ];
            $total_biaya += $r['biaya'];
        }
        
        // 9. Tambahan Biaya
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
                'kamar_inap' => $kamar_inap,
                'details' => $details,
                'total' => $total_biaya
            ]
        ]);
        exit;
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
            redirect(url([ADMIN, 'kasir_rawat_inap', 'shift']));
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
        redirect(url([ADMIN, 'kasir_rawat_inap', 'shift']));
    }

    public function postCloseKasir()
    {
        $user_id = $this->core->getUserInfo('id');
        $shift = $this->db('mlite_kasir_shift')->where('user_id', $user_id)->isNull('waktu_tutup')->desc('id_shift')->oneArray();
        if (!$shift) {
            $this->notify('danger', 'Tidak ada shift terbuka');
            redirect(url([ADMIN, 'kasir_rawat_inap', 'shift']));
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
        redirect(url([ADMIN, 'kasir_rawat_inap', 'shift']));
    }

    public function anyReport()
    {
        $this->_addHeaderFiles();
        $awal = isset($_GET['awal']) ? $_GET['awal'] : date('Y-m-d').' 00:00:00';
        $akhir = isset($_GET['akhir']) ? $_GET['akhir'] : date('Y-m-d').' 23:59:59';
        $pdo = $this->db()->pdo();
        $stmt = $pdo->prepare("SELECT b.id_user, COALESCE(p.nama, u.fullname) AS nama_kasir, COUNT(*) transaksi, IFNULL(SUM(b.jumlah_harus_bayar),0) total, MIN(CONCAT(b.tgl_billing,' ',b.jam_billing)) mulai, MAX(CONCAT(b.tgl_billing,' ',b.jam_billing)) selesai FROM mlite_billing b INNER JOIN ( SELECT no_rawat, MAX(CONCAT(tgl_billing,' ',jam_billing)) AS max_waktu FROM mlite_billing WHERE kd_billing LIKE 'RI%' AND CONCAT(tgl_billing,' ',jam_billing) BETWEEN ? AND ? GROUP BY no_rawat ) latest ON latest.no_rawat=b.no_rawat AND CONCAT(b.tgl_billing,' ',b.jam_billing)=latest.max_waktu LEFT JOIN mlite_users u ON u.id=b.id_user LEFT JOIN pegawai p ON p.nik=u.username WHERE b.kd_billing LIKE 'RI%' GROUP BY b.id_user, nama_kasir");
        $stmt->execute([$awal, $akhir]);
        $rows = $stmt->fetchAll();

        $detailStmt = $pdo->prepare("SELECT b.id_user, COALESCE(p.nama, u.fullname) AS nama_kasir, b.kd_billing, b.no_rawat, b.jumlah_harus_bayar, CONCAT(b.tgl_billing,' ',b.jam_billing) AS waktu, b.keterangan FROM mlite_billing b INNER JOIN ( SELECT no_rawat, MAX(CONCAT(tgl_billing,' ',jam_billing)) AS max_waktu FROM mlite_billing WHERE kd_billing LIKE 'RI%' AND CONCAT(tgl_billing,' ',jam_billing) BETWEEN ? AND ? GROUP BY no_rawat ) latest ON latest.no_rawat=b.no_rawat AND CONCAT(b.tgl_billing,' ',b.jam_billing)=latest.max_waktu LEFT JOIN mlite_users u ON u.id=b.id_user LEFT JOIN pegawai p ON p.nik=u.username WHERE b.kd_billing LIKE 'RI%' ORDER BY waktu ASC");
        $detailStmt->execute([$awal, $akhir]);
        $details = $detailStmt->fetchAll();

        return $this->draw('report.html', ['awal' => $awal, 'akhir' => $akhir, 'rows' => $rows, 'details' => $details]);
    }

    public function anyReportExport()
    {
        $awal = isset($_GET['awal']) ? $_GET['awal'] : date('Y-m-d').' 00:00:00';
        $akhir = isset($_GET['akhir']) ? $_GET['akhir'] : date('Y-m-d').' 23:59:59';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="laporan_kasir_inap.csv"');
        $pdo = $this->db()->pdo();
        $stmt = $pdo->prepare("SELECT b.id_user, COALESCE(p.nama, u.fullname) AS nama_kasir, b.kd_billing, b.jumlah_harus_bayar, CONCAT(b.tgl_billing,' ',b.jam_billing) waktu FROM mlite_billing b INNER JOIN ( SELECT no_rawat, MAX(CONCAT(tgl_billing,' ',jam_billing)) AS max_waktu FROM mlite_billing WHERE kd_billing LIKE 'RI%' AND CONCAT(tgl_billing,' ',jam_billing) BETWEEN ? AND ? GROUP BY no_rawat ) latest ON latest.no_rawat=b.no_rawat AND CONCAT(b.tgl_billing,' ',b.jam_billing)=latest.max_waktu LEFT JOIN mlite_users u ON u.id=b.id_user LEFT JOIN pegawai p ON p.nik=u.username WHERE b.kd_billing LIKE 'RI%' ORDER BY waktu ASC");
        $stmt->execute([$awal, $akhir]);
        echo "id_user,nama_kasir,kd_billing,jumlah_harus_bayar,waktu\n";
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            echo $row['id_user'].",".($row['nama_kasir'] ?? '').",".$row['kd_billing'].",".$row['jumlah_harus_bayar'].",".$row['waktu']."\n";
        }
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

        $get_gudangbarang = $this->db('gudangbarang')->where('kode_brng', $_POST['kd_jenis_prw'])->where('kd_bangsal', $this->settings->get('farmasi.deporanap'))->oneArray();
        $get_databarang = $this->db('databarang')->where('kode_brng', $_POST['kd_jenis_prw'])->oneArray();

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
            'h_beli' => $get_databarang['h_beli'],
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
      ->where('no_rawat', $_POST['no_rawat'])
      ->where('detail_pemberian_obat.status', 'Ranap')
      ->toArray();

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
        'jumlah_total_embalase' => $jumlah_total_embalase,
        'jumlah_total_tuslah' => $jumlah_total_tuslah,
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

        $qr=QRCode::getMinimumQRCode($this->core->getUserInfo('fullname', null, true),QR_ERROR_CORRECT_LEVEL_L);
        $im=$qr->createImage(4,4);
        imagepng($im,BASE_DIR.'/'.ADMIN.'/tmp/qrcode.png');
        imagedestroy($im);

        $image = BASE_DIR."/".ADMIN."/tmp/qrcode.png";
        $qrCode = url()."/".ADMIN."/tmp/qrcode.png";

        if (file_exists(UPLOADS.'/invoices/'.$result['kd_billing'].'.pdf')) {
          unlink(UPLOADS.'/invoices/'.$result['kd_billing'].'.pdf');
        }

        $mpdf = new \Mpdf\Mpdf([
          'mode' => 'utf-8',
          'format' => 'A4', 
          'orientation' => 'P'
        ]);
  
        $css = '
        <style>
          del { 
            display: none;
          }
          table {
            padding-top: 1cm;
            padding-bottom: 1cm;
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
        
        $url = url(ADMIN.'/tmp/billing.besar.html');
        $html = file_get_contents($url);
        $mpdf->WriteHTML($this->core->setPrintCss(),\Mpdf\HTMLParserMode::HEADER_CSS);
        $mpdf->WriteHTML($css);
        $mpdf->WriteHTML($html);
    
        // Output a PDF file save to server
        $mpdf->Output(UPLOADS.'/invoices/'.$result['kd_billing'].'.pdf','F');
                
        echo $this->draw('billing.besar.html', ['wagateway' => $this->settings->get('wagateway'), 'billing' => $result, 'billing_besar_detail' => $result_detail, 'jumlah_total_operasi' => $jumlah_total_operasi, 'pasien' => $pasien, 'qrCode' => $qrCode, 'fullname' => $this->core->getUserInfo('fullname', null, true)]);
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
         throw new \Exception("Could not fetch remote content from: '$file'");
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
