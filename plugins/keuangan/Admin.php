<?php
namespace Plugins\Keuangan;

use Systems\AdminModule;
use Plugins\Keuangan\Src\Akunrekening;

class Admin extends AdminModule
{
    protected $akunrekening;
    protected $assign = [];
    
    public function init()
    {
      $this->akunrekening = new Akunrekening();
    }

    public function navigation()
    {
        return [
            'Kelola'   => 'manage',
            'Akun Rekening' => 'akunrekening',
            'Rekening Tahun' => 'rekeningtahun',
            'Pengaturan Rekening' => 'pengaturanrekening',
            'Posting Jurnal' => 'postingjurnal',
            'Jurnal Harian' => 'jurnalharian',
            'Buku Besar' => 'bukubesar',
            'Cash Flow' => 'cashflow',
            'Neraca Keuangan' => 'neraca',
        ];
    }

    public function getManage()
    {
      $sub_modules = [
        ['name' => 'Akun Rekening', 'url' => url([ADMIN, 'keuangan', 'akunrekening']), 'icon' => 'money', 'desc' => 'Akun Rekening'],
        ['name' => 'Rekening Tahun', 'url' => url([ADMIN, 'keuangan', 'rekeningtahun']), 'icon' => 'money', 'desc' => 'Rekening Tahun'],
        ['name' => 'Pengaturan Rekening', 'url' => url([ADMIN, 'keuangan', 'pengaturanrekening']), 'icon' => 'money', 'desc' => 'Pengaturan Rekening'],
        ['name' => 'Posting Jurnal', 'url' => url([ADMIN, 'keuangan', 'postingjurnal']), 'icon' => 'money', 'desc' => 'Posting Jurnal'],
        ['name' => 'Jurnal Harian', 'url' => url([ADMIN, 'keuangan', 'jurnalharian']), 'icon' => 'money', 'desc' => 'Jurnal Harian'],
        ['name' => 'Buku Besar', 'url' => url([ADMIN, 'keuangan', 'bukubesar']), 'icon' => 'money', 'desc' => 'Buku Besar'],
        ['name' => 'Cash Flow', 'url' => url([ADMIN, 'keuangan', 'cashflow']), 'icon' => 'money', 'desc' => 'Cash Flow'],
        ['name' => 'Neraca Keuangan', 'url' => url([ADMIN, 'keuangan', 'neraca']), 'icon' => 'money', 'desc' => 'Neraca Keuangan'],
        ['name' => 'Pengaturan Keuangan', 'url' => url([ADMIN, 'keuangan', 'settings']), 'icon' => 'money', 'desc' => 'Pengaturan Modul Keuangan'],
      ];
      return $this->draw('manage.html', ['sub_modules' => htmlspecialchars_array($sub_modules)]);
    }

    /* Start Bahasa Section */
    public function getAkunRekening()
    {
      $this->core->addJS(url([ADMIN, 'keuangan', 'akunrekeningjs']), 'footer');
      $return = $this->akunrekening->getIndex();
      return $this->draw('akunrekening.html', [
        'akunrekening' => $return
      ]);

    }

    public function anyAkunRekeningForm()
    {
        $return = $this->akunrekening->anyForm();
        echo $this->draw('akunrekening.form.html', ['akunrekening' => $return]);
        exit();
    }

    public function anyAkunRekeningDisplay()
    {
        $return = $this->akunrekening->anyDisplay();
        echo $this->draw('akunrekening.display.html', ['akunrekening' => $return]);
        exit();
    }

    public function postAkunRekeningSave()
    {
      $this->akunrekening->postSave();
      exit();
    }

    public function postAkunRekeningHapus()
    {
      $this->akunrekening->postHapus();
      exit();
    }

    public function getAkunRekeningJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/keuangan/js/admin/akunrekening.js');
        exit();
    }
    /* End Bahasa Section */

    public function getRekeningTahun()
    {
      $this->core->addJS(url([ADMIN, 'keuangan', 'akunrekeningjs']), 'footer');
      $this->_addHeaderFiles();
      $curr_year = date('Y');
      $akunrekening = $this->db('mlite_rekening')->toArray();
      $rekeningtahun = $this->db('mlite_rekeningtahun')
      ->join('mlite_rekening', 'mlite_rekening.kd_rek=mlite_rekeningtahun.kd_rek')
      ->where('thn', $curr_year)
      ->toArray();
      return $this->draw('rekening.tahun.html', ['akunrekening' => $akunrekening, 'rekeningtahun' => $rekeningtahun]);
    }

    public function postSaveRekeningTahun()
    {
      if(isset($_POST['simpan']) && $_POST['simpan']) {
        $this->db('mlite_rekeningtahun')
        ->save([
          'thn' => $_POST['tahun'],
          'kd_rek' => $_POST['kd_rek'],
          'saldo_awal' => $_POST['saldo_awal']
        ]);
        $this->notify('success', 'Rekening tahun telah disimpan');
      } else if (isset($_POST['update']) && $_POST['update']) {
        $this->db('mlite_rekeningtahun')
        ->where('thn', $_POST['tahun'])
        ->where('kd_rek', $_POST['kd_rek'])
        ->save([
          'saldo_awal' => $_POST['saldo_awal']
        ]);
        $this->notify('success', 'Rekening tahun telah diubah');
      } else if (isset($_POST['hapus']) && $_POST['hapus']) {
        $this->db('mlite_rekeningtahun')
        ->where('thn', $_POST['tahun'])
        ->where('kd_rek', $_POST['kd_rek'])
        ->delete();
        $this->notify('success', 'Rekening tahun telah dihapus');
      }
      redirect(url([ADMIN, 'keuangan', 'rekeningtahun']));
    }

    public function getPengaturanRekening()
    {
      $this->core->addJS(url([ADMIN, 'keuangan', 'akunrekeningjs']), 'footer');
      $akunkegiatan = $this->db('mlite_akun_kegiatan')->toArray();
      $akunrekening = $this->db('mlite_rekening')->toArray();
      return $this->draw('pengaturan.rekening.html', ['akunkegiatan' => $akunkegiatan, 'akunrekening' => $akunrekening]);
    }

    public function getPostingJurnal()
    {
      $this->_addHeaderFiles();
      $kegiatan = $this->db('mlite_akun_kegiatan')->toArray();
      $akunrekening = $this->db('mlite_rekening')->toArray();
      return $this->draw('posting.jurnal.html', ['kegiatan' => $kegiatan, 'akunrekening' => $akunrekening, 'no_jurnal' => $this->core->setNoJurnal()]);
    }

    public function getJurnalHarian()
    {
      $this->_addHeaderFiles();
      $settings = $this->settings('settings');
      $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));

      $tgl_awal = date('Y-m-d');
      $tgl_akhir = date('Y-m-d');

      if(isset($_GET['tgl_awal'])) {
        $tgl_awal = $_GET['tgl_awal'];
      }
      if(isset($_GET['tgl_akhir'])) {
        $tgl_akhir = $_GET['tgl_akhir'];
      }

      $jurnalharian = [];
      $rows = $this->db('mlite_jurnal')
        ->join('mlite_detailjurnal', 'mlite_detailjurnal.no_jurnal=mlite_jurnal.no_jurnal')
        ->join('mlite_rekening', 'mlite_rekening.kd_rek=mlite_detailjurnal.kd_rek')
        ->where('tgl_jurnal', '>=', $tgl_awal)
        ->where('tgl_jurnal', '<=', $tgl_akhir)
        ->toArray();
      foreach ($rows as $row) {
        if($row['jenis'] == 'U') {
          $row['jenis'] = 'Umum';
        } else {
          $row['jenis'] = 'Penyesuaian';
        }
        $rekening = $this->db('mlite_rekening')
          ->where('kd_rek', $row['kd_rek'])->oneArray();
        $row['nm_rek'] = $rekening['nm_rek'];
        $jurnalharian[] = $row;
      }
      if(isset($_GET['action']) && $_GET['action'] == 'print') {
        echo $this->draw('jurnal.harian.print.html', [
          'jurnalharian' => $jurnalharian,
          'action' => url([ADMIN,'keuangan','jurnalharian'])
        ]);
        exit();
      } else {
        return $this->draw('jurnal.harian.html', ['jurnalharian' => $jurnalharian]);
      }
    }

    public function getBukuBesar()
    {
      $this->_addHeaderFiles();
      $this->core->addJS(url([ADMIN, 'keuangan', 'akunrekeningjs']), 'footer');
      $settings = $this->settings('settings');
      $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));

      $tgl_awal = date('Y-m-d');
      $tgl_akhir = date('Y-m-d');

      if(isset($_GET['tgl_awal'])) {
        $tgl_awal = $_GET['tgl_awal'];
      }
      if(isset($_GET['tgl_akhir'])) {
        $tgl_akhir = $_GET['tgl_akhir'];
      }

      $kd_rek = isset($_GET['kd_rek']) ? $_GET['kd_rek'] : '';

      $sql = "SELECT mlite_detailjurnal.no_jurnal, tgl_jurnal, keterangan, debet, kredit, mlite_rekening.balance FROM mlite_detailjurnal JOIN mlite_jurnal ON mlite_detailjurnal.no_jurnal = mlite_jurnal.no_jurnal JOIN mlite_rekening ON mlite_detailjurnal.kd_rek = mlite_rekening.kd_rek WHERE (mlite_jurnal.tgl_jurnal BETWEEN ? AND ?)";
      $params = [$tgl_awal, $tgl_akhir];

      if(!empty($kd_rek)) {
        $sql .= " AND mlite_detailjurnal.kd_rek = ?";
        $params[] = $kd_rek;
      }

      $sql .= " ORDER BY mlite_detailjurnal.no_jurnal ASC";

      $query = $this->db()->pdo()->prepare($sql);
      $query->execute($params);
      $bukubesar = $query->fetchAll(\PDO::FETCH_ASSOC);
      $saldo = 0;
      foreach ($bukubesar as $key => $row) {
        $debet = (float)$row['debet'];
        $kredit = (float)$row['kredit'];
        if ($row['balance'] === 'D') {
          $saldo += ($debet - $kredit);
        } else {
          $saldo += ($kredit - $debet);
        }
        $bukubesar[$key]['saldo'] = $saldo;
      }

      $akunrekening = $this->db('mlite_rekening')->toArray();

      if(isset($_GET['action']) && $_GET['action'] == 'print') {
        echo $this->draw('buku.besar.print.html', [
          'bukubesar' => $bukubesar,
          'action' => url([ADMIN,'keuangan','bukubesar'])
        ]);
        exit();
      } else {
        return $this->draw('buku.besar.html', [
          'bukubesar' => $bukubesar,
          'akunrekening' => $akunrekening,
          'kd_rek_filter' => $kd_rek,
          'tgl_awal' => htmlspecialchars($tgl_awal, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
          'tgl_akhir' => htmlspecialchars($tgl_akhir, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        ]);
      }
    }

    public function getCashFlow()
    {
      $this->_addHeaderFiles();
      $settings = $this->settings('settings');
      $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));
      $aruskas = [];

      $tgl_awal = date('Y-01-01');
      $tgl_akhir = date('Y-m-d');

      if(isset($_GET['tgl_awal'])) {
        $tgl_awal = $_GET['tgl_awal'];
      }
      if(isset($_GET['tgl_akhir'])) {
        $tgl_akhir = $_GET['tgl_akhir'];
      }

      // Definisi kategori arus kas — mapping sesuai tipe rekening:
      // R (Rugi/Laba) = Kegiatan Operasional
      // N (Neraca/Aset-Liabilitas) = Kegiatan Investasi
      // M (Modal) = Kegiatan Pendanaan
      $rows_aruskas = array(
          array("tipe" => "R", "arus_kas" => "Kegiatan Operasional"),
          array("tipe" => "N", "arus_kas" => "Kegiatan Investasi"),
          array("tipe" => "M", "arus_kas" => "Kegiatan Pendanaan"),
      );

      // Hitung saldo awal kas dari akun kas (1101-1105) sebelum periode
      $query_saldo_awal = "
          SELECT COALESCE(SUM(
              CASE
                  WHEN r.balance = 'D' THEN COALESCE(jd.debet, 0) - COALESCE(jd.kredit, 0)
                  ELSE COALESCE(jd.kredit, 0) - COALESCE(jd.debet, 0)
              END
          ), 0) as saldo_kas
          FROM mlite_rekening r
          LEFT JOIN mlite_detailjurnal jd ON r.kd_rek = jd.kd_rek
          LEFT JOIN mlite_jurnal j ON j.no_jurnal = jd.no_jurnal AND j.tgl_jurnal < ?
          WHERE r.kd_rek IN ('1101', '1102', '1103', '1104', '1105')
          AND r.tipe = 'N'
      ";

      $stmt_saldo = $this->db()->pdo()->prepare($query_saldo_awal);
      $stmt_saldo->execute([$tgl_awal]);
      $result_saldo = $stmt_saldo->fetch();
      $saldo_awal_kas = $result_saldo['saldo_kas'] ?? 0;

      $total_kredit = 0;
      $total_debet = 0;
      $total_saldo_kredit = 0;
      $total_saldo_debet = 0;
      $n = 1;

      foreach ($rows_aruskas as $row) {
        $row['nomor'] = $n++;
        $row['total_masuk'] = 0;
        $row['total_keluar'] = 0;
        $row['total_saldo_awal_masuk'] = 0;
        $row['total_saldo_awal_keluar'] = 0;

        // Arus kas masuk (transaksi yang menambah kas) dalam periode
        $query_masuk = "
            SELECT
                jd.kd_rek,
                r.nm_rek,
                r.tipe,
                r.balance,
                SUM(CASE
                    WHEN jd.kd_rek IN ('1101', '1102', '1103', '1104', '1105') THEN jd.debet
                    ELSE jd.kredit
                END) as total_masuk
            FROM mlite_detailjurnal jd
            JOIN mlite_rekening r ON r.kd_rek = jd.kd_rek
            JOIN mlite_jurnal j ON j.no_jurnal = jd.no_jurnal
            WHERE r.tipe = ?
            AND j.tgl_jurnal >= ? AND j.tgl_jurnal <= ?
            AND ((jd.kd_rek IN ('1101', '1102', '1103', '1104', '1105') AND jd.debet > 0)
                 OR (jd.kd_rek NOT IN ('1101', '1102', '1103', '1104', '1105') AND jd.kredit > 0))
            GROUP BY jd.kd_rek, r.nm_rek, r.tipe, r.balance
            HAVING total_masuk > 0
        ";

        $stmt_masuk = $this->db()->pdo()->prepare($query_masuk);
        $stmt_masuk->execute([$row['tipe'], $tgl_awal, $tgl_akhir]);
        $rows_masuk = $stmt_masuk->fetchAll();

        $row['jurnal_masuk'] = [];
        foreach ($rows_masuk as $row_masuk) {
          $row_masuk['kredit_all'] = $row_masuk['total_masuk'];
          $row_masuk['saldo_awal'] = 0;
          $row['total_masuk'] += $row_masuk['total_masuk'];
          $row['jurnal_masuk'][] = $row_masuk;
          $total_kredit += $row_masuk['total_masuk'];
        }

        // Arus kas keluar (transaksi yang mengurangi kas) dalam periode
        $query_keluar = "
            SELECT
                jd.kd_rek,
                r.nm_rek,
                r.tipe,
                r.balance,
                SUM(CASE
                    WHEN jd.kd_rek IN ('1101', '1102', '1103', '1104', '1105') THEN jd.kredit
                    ELSE jd.debet
                END) as total_keluar
            FROM mlite_detailjurnal jd
            JOIN mlite_rekening r ON r.kd_rek = jd.kd_rek
            JOIN mlite_jurnal j ON j.no_jurnal = jd.no_jurnal
            WHERE r.tipe = ?
            AND j.tgl_jurnal >= ? AND j.tgl_jurnal <= ?
            AND ((jd.kd_rek IN ('1101', '1102', '1103', '1104', '1105') AND jd.kredit > 0)
                 OR (jd.kd_rek NOT IN ('1101', '1102', '1103', '1104', '1105') AND jd.debet > 0))
            GROUP BY jd.kd_rek, r.nm_rek, r.tipe, r.balance
            HAVING total_keluar > 0
        ";

        $stmt_keluar = $this->db()->pdo()->prepare($query_keluar);
        $stmt_keluar->execute([$row['tipe'], $tgl_awal, $tgl_akhir]);
        $rows_keluar = $stmt_keluar->fetchAll();

        $row['jurnal_keluar'] = [];
        foreach ($rows_keluar as $row_keluar) {
          $row_keluar['debet_all'] = $row_keluar['total_keluar'];
          $row_keluar['saldo_awal'] = 0;
          $row['total_keluar'] += $row_keluar['total_keluar'];
          $row['jurnal_keluar'][] = $row_keluar;
          $total_debet += $row_keluar['total_keluar'];
        }

        $aruskas[] = $row;
      }

      $akunrekening = $this->db('mlite_rekening')->toArray();
      $tgl_awal_escaped = htmlspecialchars($tgl_awal, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
      $tgl_akhir_escaped = htmlspecialchars($tgl_akhir, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

      $template_data = [
        'aruskas' => $aruskas,
        'akunrekening' => $akunrekening,
        'masuk_all' => $total_kredit,
        'keluar_all' => $total_debet,
        'saldo_masuk' => $total_saldo_kredit,
        'saldo_keluar' => $total_saldo_debet,
        'jumlah_total_saldo' => $saldo_awal_kas,
        'tgl_awal' => $tgl_awal_escaped,
        'tgl_akhir' => $tgl_akhir_escaped,
      ];

      if(isset($_GET['action']) && $_GET['action'] == 'print') {
        echo $this->draw('cash.flow.print.html', $template_data);
        exit();
      } else {
        return $this->draw('cash.flow.html', $template_data);
      }
    }

    public function getNeraca()
    {
      $this->_addHeaderFiles();
      $settings = $this->settings('settings');
      $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));

      $tgl_awal = date('Y-01-01'); // Default awal tahun
      $tgl_akhir = date('Y-m-d');  // Default hari ini

      if(isset($_GET['tgl_awal'])) {
        $tgl_awal = $_GET['tgl_awal'];
      }
      if(isset($_GET['tgl_akhir'])) {
        $tgl_akhir = $_GET['tgl_akhir'];
      }

      // Inisialisasi variabel neraca
      $aktiva_lancar = [];
      $aktiva_tetap = [];
      $hutang_lancar = [];
      $hutang_jangka_panjang = [];
      $modal = [];
      
      $total_aktiva_lancar = 0;
      $total_aktiva_tetap = 0;
      $total_hutang_lancar = 0;
      $total_hutang_jangka_panjang = 0;
      $total_modal = 0;

      // Query untuk mendapatkan saldo awal rekening (sebelum periode)
      $query_saldo_awal = "
          SELECT 
              r.kd_rek,
              r.nm_rek,
              r.balance as saldo_normal,
              COALESCE(SUM(jd.debet), 0) as total_debet_awal,
              COALESCE(SUM(jd.kredit), 0) as total_kredit_awal,
              CASE 
                  WHEN r.balance = 'D' THEN COALESCE(SUM(jd.debet), 0) - COALESCE(SUM(jd.kredit), 0)
                  ELSE COALESCE(SUM(jd.kredit), 0) - COALESCE(SUM(jd.debet), 0)
              END as saldo_awal
          FROM mlite_rekening r
          LEFT JOIN mlite_detailjurnal jd ON r.kd_rek = jd.kd_rek
          LEFT JOIN mlite_jurnal j ON j.no_jurnal = jd.no_jurnal AND j.tgl_jurnal < ?
          WHERE r.tipe IN ('N', 'M')
          GROUP BY r.kd_rek, r.nm_rek, r.balance
      ";
      
      $stmt_awal = $this->db()->pdo()->prepare($query_saldo_awal);
      $stmt_awal->execute([$tgl_awal]);
      $saldo_awal_data = [];
      foreach($stmt_awal->fetchAll() as $row) {
          $saldo_awal_data[$row['kd_rek']] = $row['saldo_awal'];
      }

      // Query untuk mendapatkan mutasi dalam periode
      $query_mutasi = "
          SELECT 
              r.kd_rek,
              r.nm_rek,
              r.balance as saldo_normal,
              COALESCE(SUM(jd.debet), 0) as total_debet_periode,
              COALESCE(SUM(jd.kredit), 0) as total_kredit_periode,
              CASE 
                  WHEN r.balance = 'D' THEN COALESCE(SUM(jd.debet), 0) - COALESCE(SUM(jd.kredit), 0)
                  ELSE COALESCE(SUM(jd.kredit), 0) - COALESCE(SUM(jd.debet), 0)
              END as mutasi_periode
          FROM mlite_rekening r
          LEFT JOIN mlite_detailjurnal jd ON r.kd_rek = jd.kd_rek
          LEFT JOIN mlite_jurnal j ON j.no_jurnal = jd.no_jurnal
          WHERE r.tipe IN ('N', 'M')
          AND j.tgl_jurnal >= ? AND j.tgl_jurnal <= ?
          GROUP BY r.kd_rek, r.nm_rek, r.balance
      ";
      
      $stmt_mutasi = $this->db()->pdo()->prepare($query_mutasi);
      $stmt_mutasi->execute([$tgl_awal, $tgl_akhir]);
      $mutasi_data = [];
      foreach($stmt_mutasi->fetchAll() as $row) {
          $mutasi_data[$row['kd_rek']] = $row['mutasi_periode'];
      }

      // Ambil semua rekening aktif
      $query_rekening = "SELECT kd_rek, nm_rek, balance FROM mlite_rekening WHERE tipe IN ('N', 'M') ORDER BY kd_rek";
      $stmt_rekening = $this->db()->pdo()->prepare($query_rekening);
      $stmt_rekening->execute();
      $result = $stmt_rekening->fetchAll();
      foreach($result as $rek) {
        // Hitung saldo akhir = saldo awal + mutasi periode
        $saldo_awal = isset($saldo_awal_data[$rek['kd_rek']]) ? $saldo_awal_data[$rek['kd_rek']] : 0;
        $mutasi = isset($mutasi_data[$rek['kd_rek']]) ? $mutasi_data[$rek['kd_rek']] : 0;
        $saldo_akhir = $saldo_awal + $mutasi;
        
        // Skip akun dengan saldo 0
        if(abs($saldo_akhir) < 0.01) continue;
        
        // Klasifikasi berdasarkan kode rekening
        $kode_awal = substr($rek['kd_rek'], 0, 1);
        $kode_dua_digit = substr($rek['kd_rek'], 0, 2);
        
        if($kode_awal == '1') {
          // Aktiva
          if(in_array($kode_dua_digit, ['11', '12', '13'])) {
            // Aktiva Lancar (Kas, Bank, Piutang, Persediaan)
            $aktiva_lancar[] = [
              'kd_rek' => $rek['kd_rek'],
              'nm_rek' => $rek['nm_rek'],
              'saldo' => $saldo_akhir,
              'saldo_awal' => $saldo_awal,
              'mutasi' => $mutasi
            ];
            $total_aktiva_lancar += $saldo_akhir;
          } else {
            // Aktiva Tetap (14, 15, 16, 17, 18, 19)
            $aktiva_tetap[] = [
              'kd_rek' => $rek['kd_rek'],
              'nm_rek' => $rek['nm_rek'],
              'saldo' => $saldo_akhir,
              'saldo_awal' => $saldo_awal,
              'mutasi' => $mutasi
            ];
            $total_aktiva_tetap += $saldo_akhir;
          }
        } elseif($kode_awal == '2') {
          // Hutang/Kewajiban
          if(in_array($kode_dua_digit, ['21'])) {
            // Hutang Lancar
            $hutang_lancar[] = [
              'kd_rek' => $rek['kd_rek'],
              'nm_rek' => $rek['nm_rek'],
              'saldo' => $saldo_akhir,
              'saldo_awal' => $saldo_awal,
              'mutasi' => $mutasi
            ];
            $total_hutang_lancar += $saldo_akhir;
          } else {
            // Hutang Jangka Panjang (22, 23, 24, 25, 26, 27, 28, 29)
            $hutang_jangka_panjang[] = [
              'kd_rek' => $rek['kd_rek'],
              'nm_rek' => $rek['nm_rek'],
              'saldo' => $saldo_akhir,
              'saldo_awal' => $saldo_awal,
              'mutasi' => $mutasi
            ];
            $total_hutang_jangka_panjang += $saldo_akhir;
          }
        } elseif($kode_awal == '3') {
          // Modal
          $modal[] = [
            'kd_rek' => $rek['kd_rek'],
            'nm_rek' => $rek['nm_rek'],
            'saldo' => $saldo_akhir,
            'saldo_awal' => $saldo_awal,
            'mutasi' => $mutasi
          ];
          $total_modal += $saldo_akhir;
        }
      }
      
      // Hitung laba rugi periode berjalan untuk penyesuaian modal
      $query_labarugi = "
          SELECT 
              COALESCE(SUM(CASE WHEN r.balance = 'K' THEN jd.kredit - jd.debet ELSE jd.debet - jd.kredit END), 0) as laba_rugi
          FROM mlite_rekening r
          LEFT JOIN mlite_detailjurnal jd ON r.kd_rek = jd.kd_rek
          LEFT JOIN mlite_jurnal j ON j.no_jurnal = jd.no_jurnal
          WHERE r.tipe = 'R'
          AND SUBSTR(r.kd_rek, 1, 1) IN ('4', '5', '6', '7', '8', '9')
          AND j.tgl_jurnal >= ? AND j.tgl_jurnal <= ?
      ";
      
      $stmt_labarugi = $this->db()->pdo()->prepare($query_labarugi);
      $stmt_labarugi->execute([$tgl_awal, $tgl_akhir]);
      $laba_rugi = $stmt_labarugi->fetchColumn();
      
      // Tambahkan laba rugi ke total modal
      $total_modal += $laba_rugi;
      
      // Hitung total aktiva dan pasiva
      $total_aktiva = $total_aktiva_lancar + $total_aktiva_tetap;
      $total_pasiva = $total_hutang_lancar + $total_hutang_jangka_panjang + $total_modal;
      
      // Validasi keseimbangan neraca
      $selisih = $total_aktiva - $total_pasiva;
      $seimbang = (abs($selisih) < 0.01); // Toleransi untuk pembulatan
      
      $data = [
        'settings' => $settings,
        'tgl_awal' => htmlspecialchars($tgl_awal, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        'tgl_akhir' => htmlspecialchars($tgl_akhir, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        'aktiva_lancar' => $aktiva_lancar,
        'aktiva_tetap' => $aktiva_tetap,
        'hutang_lancar' => $hutang_lancar,
        'hutang_jangka_panjang' => $hutang_jangka_panjang,
        'modal' => $modal,
        'total_aktiva_lancar' => $total_aktiva_lancar,
        'total_aktiva_tetap' => $total_aktiva_tetap,
        'total_hutang_lancar' => $total_hutang_lancar,
        'total_hutang_jangka_panjang' => $total_hutang_jangka_panjang,
        'total_modal' => $total_modal,
        'laba_rugi_periode' => $laba_rugi,
        'total_aktiva' => $total_aktiva,
        'total_pasiva' => $total_pasiva,
        'selisih' => $selisih,
        'seimbang' => $seimbang
      ];
      
      if(isset($_GET['action']) && $_GET['action'] == 'print') {
        echo $this->draw('neraca.print.html', $data);
        exit();
      } elseif(isset($_GET['action']) && $_GET['action'] == 'excel') {
        $this->_exportNeracaExcel($data);
      } else {
        return $this->draw('neraca.html', $data);
      }
    }

    public function getSettings()
    {
        $this->assign['title'] = 'Pengaturan Modul Keuangan';
        $this->assign['keuangan'] = htmlspecialchars_array($this->settings('keuangan'));
        $akunkegiatan = $this->db('mlite_settings')->where('module', 'keuangan')->where('field', '<>', 'jurnal_kasir')->toArray();
        $akunrekening = $this->db('mlite_rekening')->toArray();
        return $this->draw('settings.html', ['settings' => htmlspecialchars_array($this->assign), 'akunkegiatan' => $akunkegiatan, 'akunrekening' => $akunrekening]);
    }

    public function postSaveSettings()
    {
        foreach ($_POST['keuangan'] as $key => $val) {
            $this->settings('keuangan', $key, $val);
        }
        $this->notify('success', 'Pengaturan keuangan telah disimpan');
        redirect(url([ADMIN, 'keuangan', 'settings']));
    }

    public function postInsertDummyKeuangan()
    {
        $rekeningtahun = [
            ['thn' => 2025, 'kd_rek' => '1101', 'saldo_awal' => 50000000],
            ['thn' => 2025, 'kd_rek' => '1201', 'saldo_awal' => 200000000],
            ['thn' => 2025, 'kd_rek' => '1301', 'saldo_awal' => 75000000],
            ['thn' => 2025, 'kd_rek' => '1302', 'saldo_awal' => 15000000],
            ['thn' => 2025, 'kd_rek' => '1401', 'saldo_awal' => 80000000],
            ['thn' => 2025, 'kd_rek' => '1601', 'saldo_awal' => 500000000],
            ['thn' => 2025, 'kd_rek' => '1701', 'saldo_awal' => 300000000],
            ['thn' => 2025, 'kd_rek' => '1801', 'saldo_awal' => 150000000],
            ['thn' => 2025, 'kd_rek' => '1901', 'saldo_awal' => 50000000],
            ['thn' => 2025, 'kd_rek' => '2101', 'saldo_awal' => 25000000],
            ['thn' => 2025, 'kd_rek' => '2102', 'saldo_awal' => 20000000],
            ['thn' => 2025, 'kd_rek' => '2201', 'saldo_awal' => 200000000],
            ['thn' => 2025, 'kd_rek' => '3101', 'saldo_awal' => 800000000],
            ['thn' => 2025, 'kd_rek' => '3201', 'saldo_awal' => 375000000],
            ['thn' => 2026, 'kd_rek' => '1101', 'saldo_awal' => 230000000],
            ['thn' => 2026, 'kd_rek' => '1201', 'saldo_awal' => 200000000],
            ['thn' => 2026, 'kd_rek' => '1301', 'saldo_awal' => 75000000],
            ['thn' => 2026, 'kd_rek' => '1302', 'saldo_awal' => 15000000],
            ['thn' => 2026, 'kd_rek' => '1401', 'saldo_awal' => 80000000],
            ['thn' => 2026, 'kd_rek' => '1601', 'saldo_awal' => 495000000],
            ['thn' => 2026, 'kd_rek' => '1701', 'saldo_awal' => 293000000],
            ['thn' => 2026, 'kd_rek' => '1801', 'saldo_awal' => 147000000],
            ['thn' => 2026, 'kd_rek' => '1901', 'saldo_awal' => 50000000],
            ['thn' => 2026, 'kd_rek' => '2101', 'saldo_awal' => 25000000],
            ['thn' => 2026, 'kd_rek' => '2102', 'saldo_awal' => 20000000],
            ['thn' => 2026, 'kd_rek' => '2201', 'saldo_awal' => 200000000],
            ['thn' => 2026, 'kd_rek' => '3101', 'saldo_awal' => 800000000],
            ['thn' => 2026, 'kd_rek' => '3201', 'saldo_awal' => 540000000]
        ];

        $jurnal = [
            ['no_jurnal' => 'JU-2025-001', 'no_bukti' => 'BKT-2025-001', 'tgl_jurnal' => '2025-01-15', 'jenis' => 'U', 'kegiatan' => 'Penerimaan Kasir Rawat Jalan', 'keterangan' => 'Penerimaan pendapatan layanan Q1 Januari 2025. Diposting oleh Administrator.'],
            ['no_jurnal' => 'JU-2025-002', 'no_bukti' => 'BKT-2025-002', 'tgl_jurnal' => '2025-01-31', 'jenis' => 'U', 'kegiatan' => 'Pembayaran Gaji Karyawan', 'keterangan' => 'Pembayaran gaji seluruh karyawan bulan Januari 2025. Diposting oleh Administrator.'],
            ['no_jurnal' => 'JU-2025-003', 'no_bukti' => 'BKT-2025-003', 'tgl_jurnal' => '2025-04-15', 'jenis' => 'U', 'kegiatan' => 'Penerimaan Kasir Q2', 'keterangan' => 'Penerimaan pendapatan layanan Q2 April 2025. Diposting oleh Administrator.'],
            ['no_jurnal' => 'JU-2025-004', 'no_bukti' => 'BKT-2025-004', 'tgl_jurnal' => '2025-04-30', 'jenis' => 'U', 'kegiatan' => 'Pembayaran Biaya Operasional Q2', 'keterangan' => 'Pembayaran biaya operasional bulan April 2025. Diposting oleh Administrator.'],
            ['no_jurnal' => 'JU-2025-005', 'no_bukti' => 'BKT-2025-005', 'tgl_jurnal' => '2025-07-15', 'jenis' => 'U', 'kegiatan' => 'Penerimaan Kasir Q3', 'keterangan' => 'Penerimaan pendapatan layanan Q3 Juli 2025. Diposting oleh Administrator.'],
            ['no_jurnal' => 'JU-2025-006', 'no_bukti' => 'BKT-2025-006', 'tgl_jurnal' => '2025-07-31', 'jenis' => 'U', 'kegiatan' => 'Pembayaran Biaya Operasional Q3', 'keterangan' => 'Pembayaran biaya operasional bulan Juli 2025. Diposting oleh Administrator.'],
            ['no_jurnal' => 'JU-2025-007', 'no_bukti' => 'BKT-2025-007', 'tgl_jurnal' => '2025-10-15', 'jenis' => 'U', 'kegiatan' => 'Penerimaan Kasir Q4', 'keterangan' => 'Penerimaan pendapatan layanan Q4 Oktober 2025. Diposting oleh Administrator.'],
            ['no_jurnal' => 'JU-2025-008', 'no_bukti' => 'BKT-2025-008', 'tgl_jurnal' => '2025-10-31', 'jenis' => 'U', 'kegiatan' => 'Pembayaran Biaya Operasional Q4', 'keterangan' => 'Pembayaran biaya operasional bulan Oktober 2025. Diposting oleh Administrator.'],
            ['no_jurnal' => 'JU-2025-009', 'no_bukti' => 'BKT-2025-009', 'tgl_jurnal' => '2025-12-31', 'jenis' => 'P', 'kegiatan' => 'Penyesuaian Akhir Tahun 2025', 'keterangan' => 'Jurnal penyesuaian beban penyusutan aset tetap tahun 2025. Diposting oleh Administrator.'],
            ['no_jurnal' => 'JU-2026-001', 'no_bukti' => 'BKT-2026-001', 'tgl_jurnal' => '2026-01-15', 'jenis' => 'U', 'kegiatan' => 'Penerimaan Kasir Januari 2026', 'keterangan' => 'Penerimaan pendapatan layanan bulan Januari 2026. Diposting oleh Administrator.'],
            ['no_jurnal' => 'JU-2026-002', 'no_bukti' => 'BKT-2026-002', 'tgl_jurnal' => '2026-01-31', 'jenis' => 'U', 'kegiatan' => 'Pembayaran Biaya Januari 2026', 'keterangan' => 'Pembayaran biaya operasional bulan Januari 2026. Diposting oleh Administrator.'],
            ['no_jurnal' => 'JU-2026-003', 'no_bukti' => 'BKT-2026-003', 'tgl_jurnal' => '2026-02-15', 'jenis' => 'U', 'kegiatan' => 'Penerimaan Kasir Februari 2026', 'keterangan' => 'Penerimaan pendapatan layanan bulan Februari 2026. Diposting oleh Administrator.'],
            ['no_jurnal' => 'JU-2026-004', 'no_bukti' => 'BKT-2026-004', 'tgl_jurnal' => '2026-02-28', 'jenis' => 'U', 'kegiatan' => 'Pembayaran Biaya Februari 2026', 'keterangan' => 'Pembayaran biaya operasional bulan Februari 2026. Diposting oleh Administrator.'],
            ['no_jurnal' => 'JU-2026-005', 'no_bukti' => 'BKT-2026-005', 'tgl_jurnal' => '2026-03-15', 'jenis' => 'U', 'kegiatan' => 'Penerimaan Kasir Maret 2026', 'keterangan' => 'Penerimaan pendapatan layanan bulan Maret 2026. Diposting oleh Administrator.'],
            ['no_jurnal' => 'JU-2026-006', 'no_bukti' => 'BKT-2026-006', 'tgl_jurnal' => '2026-03-31', 'jenis' => 'U', 'kegiatan' => 'Pembayaran Biaya Maret 2026', 'keterangan' => 'Pembayaran biaya operasional bulan Maret 2026. Diposting oleh Administrator.']
        ];

        $detailjurnal = [
            ['no_jurnal' => 'JU-2025-001', 'kd_rek' => '1101', 'arus_kas' => 0, 'debet' => 75000000, 'kredit' => 0], ['no_jurnal' => 'JU-2025-001', 'kd_rek' => '4101', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 30000000], ['no_jurnal' => 'JU-2025-001', 'kd_rek' => '4102', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 20000000], ['no_jurnal' => 'JU-2025-001', 'kd_rek' => '4103', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 15000000], ['no_jurnal' => 'JU-2025-001', 'kd_rek' => '4104', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 5000000], ['no_jurnal' => 'JU-2025-001', 'kd_rek' => '4105', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 5000000],
            ['no_jurnal' => 'JU-2025-002', 'kd_rek' => '5101', 'arus_kas' => 0, 'debet' => 10000000, 'kredit' => 0], ['no_jurnal' => 'JU-2025-002', 'kd_rek' => '5102', 'arus_kas' => 0, 'debet' => 8000000, 'kredit' => 0], ['no_jurnal' => 'JU-2025-002', 'kd_rek' => '5103', 'arus_kas' => 0, 'debet' => 5000000, 'kredit' => 0], ['no_jurnal' => 'JU-2025-002', 'kd_rek' => '1101', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 23000000],
            ['no_jurnal' => 'JU-2025-003', 'kd_rek' => '1101', 'arus_kas' => 0, 'debet' => 90000000, 'kredit' => 0], ['no_jurnal' => 'JU-2025-003', 'kd_rek' => '4101', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 35000000], ['no_jurnal' => 'JU-2025-003', 'kd_rek' => '4102', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 25000000], ['no_jurnal' => 'JU-2025-003', 'kd_rek' => '4103', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 20000000], ['no_jurnal' => 'JU-2025-003', 'kd_rek' => '4104', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 5000000], ['no_jurnal' => 'JU-2025-003', 'kd_rek' => '4105', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 5000000],
            ['no_jurnal' => 'JU-2025-004', 'kd_rek' => '5101', 'arus_kas' => 0, 'debet' => 10000000, 'kredit' => 0], ['no_jurnal' => 'JU-2025-004', 'kd_rek' => '5102', 'arus_kas' => 0, 'debet' => 8000000, 'kredit' => 0], ['no_jurnal' => 'JU-2025-004', 'kd_rek' => '5103', 'arus_kas' => 0, 'debet' => 5000000, 'kredit' => 0], ['no_jurnal' => 'JU-2025-004', 'kd_rek' => '5201', 'arus_kas' => 0, 'debet' => 25000000, 'kredit' => 0], ['no_jurnal' => 'JU-2025-004', 'kd_rek' => '5301', 'arus_kas' => 0, 'debet' => 2000000, 'kredit' => 0], ['no_jurnal' => 'JU-2025-004', 'kd_rek' => '1101', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 50000000],
            ['no_jurnal' => 'JU-2025-005', 'kd_rek' => '1101', 'arus_kas' => 0, 'debet' => 85000000, 'kredit' => 0], ['no_jurnal' => 'JU-2025-005', 'kd_rek' => '4101', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 30000000], ['no_jurnal' => 'JU-2025-005', 'kd_rek' => '4102', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 25000000], ['no_jurnal' => 'JU-2025-005', 'kd_rek' => '4103', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 18000000], ['no_jurnal' => 'JU-2025-005', 'kd_rek' => '4104', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 7000000], ['no_jurnal' => 'JU-2025-005', 'kd_rek' => '4105', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 5000000],
            ['no_jurnal' => 'JU-2025-006', 'kd_rek' => '5101', 'arus_kas' => 0, 'debet' => 10000000, 'kredit' => 0], ['no_jurnal' => 'JU-2025-006', 'kd_rek' => '5102', 'arus_kas' => 0, 'debet' => 8000000, 'kredit' => 0], ['no_jurnal' => 'JU-2025-006', 'kd_rek' => '5103', 'arus_kas' => 0, 'debet' => 5000000, 'kredit' => 0], ['no_jurnal' => 'JU-2025-006', 'kd_rek' => '5201', 'arus_kas' => 0, 'debet' => 20000000, 'kredit' => 0], ['no_jurnal' => 'JU-2025-006', 'kd_rek' => '5301', 'arus_kas' => 0, 'debet' => 2000000, 'kredit' => 0], ['no_jurnal' => 'JU-2025-006', 'kd_rek' => '1101', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 45000000],
            ['no_jurnal' => 'JU-2025-007', 'kd_rek' => '1101', 'arus_kas' => 0, 'debet' => 95000000, 'kredit' => 0], ['no_jurnal' => 'JU-2025-007', 'kd_rek' => '4101', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 35000000], ['no_jurnal' => 'JU-2025-007', 'kd_rek' => '4102', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 30000000], ['no_jurnal' => 'JU-2025-007', 'kd_rek' => '4103', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 18000000], ['no_jurnal' => 'JU-2025-007', 'kd_rek' => '4104', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 7000000], ['no_jurnal' => 'JU-2025-007', 'kd_rek' => '4105', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 5000000],
            ['no_jurnal' => 'JU-2025-008', 'kd_rek' => '5101', 'arus_kas' => 0, 'debet' => 10000000, 'kredit' => 0], ['no_jurnal' => 'JU-2025-008', 'kd_rek' => '5102', 'arus_kas' => 0, 'debet' => 8000000, 'kredit' => 0], ['no_jurnal' => 'JU-2025-008', 'kd_rek' => '5103', 'arus_kas' => 0, 'debet' => 5000000, 'kredit' => 0], ['no_jurnal' => 'JU-2025-008', 'kd_rek' => '5201', 'arus_kas' => 0, 'debet' => 22000000, 'kredit' => 0], ['no_jurnal' => 'JU-2025-008', 'kd_rek' => '5301', 'arus_kas' => 0, 'debet' => 2000000, 'kredit' => 0], ['no_jurnal' => 'JU-2025-008', 'kd_rek' => '1101', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 47000000],
            ['no_jurnal' => 'JU-2025-009', 'kd_rek' => '5401', 'arus_kas' => 0, 'debet' => 15000000, 'kredit' => 0], ['no_jurnal' => 'JU-2025-009', 'kd_rek' => '1601', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 5000000], ['no_jurnal' => 'JU-2025-009', 'kd_rek' => '1701', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 7000000], ['no_jurnal' => 'JU-2025-009', 'kd_rek' => '1801', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 3000000],
            ['no_jurnal' => 'JU-2026-001', 'kd_rek' => '1101', 'arus_kas' => 0, 'debet' => 80000000, 'kredit' => 0], ['no_jurnal' => 'JU-2026-001', 'kd_rek' => '4101', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 30000000], ['no_jurnal' => 'JU-2026-001', 'kd_rek' => '4102', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 25000000], ['no_jurnal' => 'JU-2026-001', 'kd_rek' => '4103', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 15000000], ['no_jurnal' => 'JU-2026-001', 'kd_rek' => '4104', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 5000000], ['no_jurnal' => 'JU-2026-001', 'kd_rek' => '4105', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 5000000],
            ['no_jurnal' => 'JU-2026-002', 'kd_rek' => '5101', 'arus_kas' => 0, 'debet' => 10000000, 'kredit' => 0], ['no_jurnal' => 'JU-2026-002', 'kd_rek' => '5102', 'arus_kas' => 0, 'debet' => 8000000, 'kredit' => 0], ['no_jurnal' => 'JU-2026-002', 'kd_rek' => '5103', 'arus_kas' => 0, 'debet' => 5000000, 'kredit' => 0], ['no_jurnal' => 'JU-2026-002', 'kd_rek' => '5201', 'arus_kas' => 0, 'debet' => 20000000, 'kredit' => 0], ['no_jurnal' => 'JU-2026-002', 'kd_rek' => '5301', 'arus_kas' => 0, 'debet' => 2500000, 'kredit' => 0], ['no_jurnal' => 'JU-2026-002', 'kd_rek' => '1101', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 45500000],
            ['no_jurnal' => 'JU-2026-003', 'kd_rek' => '1101', 'arus_kas' => 0, 'debet' => 75000000, 'kredit' => 0], ['no_jurnal' => 'JU-2026-003', 'kd_rek' => '4101', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 28000000], ['no_jurnal' => 'JU-2026-003', 'kd_rek' => '4102', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 22000000], ['no_jurnal' => 'JU-2026-003', 'kd_rek' => '4103', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 14000000], ['no_jurnal' => 'JU-2026-003', 'kd_rek' => '4104', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 6000000], ['no_jurnal' => 'JU-2026-003', 'kd_rek' => '4105', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 5000000],
            ['no_jurnal' => 'JU-2026-004', 'kd_rek' => '5101', 'arus_kas' => 0, 'debet' => 10000000, 'kredit' => 0], ['no_jurnal' => 'JU-2026-004', 'kd_rek' => '5102', 'arus_kas' => 0, 'debet' => 8000000, 'kredit' => 0], ['no_jurnal' => 'JU-2026-004', 'kd_rek' => '5103', 'arus_kas' => 0, 'debet' => 5000000, 'kredit' => 0], ['no_jurnal' => 'JU-2026-004', 'kd_rek' => '5201', 'arus_kas' => 0, 'debet' => 18000000, 'kredit' => 0], ['no_jurnal' => 'JU-2026-004', 'kd_rek' => '5302', 'arus_kas' => 0, 'debet' => 600000, 'kredit' => 0], ['no_jurnal' => 'JU-2026-004', 'kd_rek' => '1101', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 41600000],
            ['no_jurnal' => 'JU-2026-005', 'kd_rek' => '1101', 'arus_kas' => 0, 'debet' => 85000000, 'kredit' => 0], ['no_jurnal' => 'JU-2026-005', 'kd_rek' => '4101', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 32000000], ['no_jurnal' => 'JU-2026-005', 'kd_rek' => '4102', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 27000000], ['no_jurnal' => 'JU-2026-005', 'kd_rek' => '4103', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 16000000], ['no_jurnal' => 'JU-2026-005', 'kd_rek' => '4104', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 5000000], ['no_jurnal' => 'JU-2026-005', 'kd_rek' => '4105', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 5000000],
            ['no_jurnal' => 'JU-2026-006', 'kd_rek' => '5101', 'arus_kas' => 0, 'debet' => 10000000, 'kredit' => 0], ['no_jurnal' => 'JU-2026-006', 'kd_rek' => '5102', 'arus_kas' => 0, 'debet' => 8000000, 'kredit' => 0], ['no_jurnal' => 'JU-2026-006', 'kd_rek' => '5103', 'arus_kas' => 0, 'debet' => 5000000, 'kredit' => 0], ['no_jurnal' => 'JU-2026-006', 'kd_rek' => '5201', 'arus_kas' => 0, 'debet' => 22000000, 'kredit' => 0], ['no_jurnal' => 'JU-2026-006', 'kd_rek' => '5301', 'arus_kas' => 0, 'debet' => 2500000, 'kredit' => 0], ['no_jurnal' => 'JU-2026-006', 'kd_rek' => '5302', 'arus_kas' => 0, 'debet' => 600000, 'kredit' => 0], ['no_jurnal' => 'JU-2026-006', 'kd_rek' => '1101', 'arus_kas' => 0, 'debet' => 0, 'kredit' => 48100000]
        ];

        $defaultRekening = [
            '1101' => ['kd_rek' => '1101', 'nm_rek' => 'Kas Umum', 'tipe' => 'N', 'balance' => 'D', 'level' => '1'],
            '1102' => ['kd_rek' => '1102', 'nm_rek' => 'Kas Kasir Rawat Jalan', 'tipe' => 'N', 'balance' => 'D', 'level' => '1'],
            '1103' => ['kd_rek' => '1103', 'nm_rek' => 'Kas Kasir Rawat Inap', 'tipe' => 'N', 'balance' => 'D', 'level' => '1'],
            '1104' => ['kd_rek' => '1104', 'nm_rek' => 'Kas Farmasi', 'tipe' => 'N', 'balance' => 'D', 'level' => '1'],
            '1105' => ['kd_rek' => '1105', 'nm_rek' => 'Kas Kecil', 'tipe' => 'N', 'balance' => 'D', 'level' => '1'],
            '1201' => ['kd_rek' => '1201', 'nm_rek' => 'Bank BRI', 'tipe' => 'N', 'balance' => 'D', 'level' => '1'],
            '1301' => ['kd_rek' => '1301', 'nm_rek' => 'Piutang BPJS', 'tipe' => 'N', 'balance' => 'D', 'level' => '1'],
            '1302' => ['kd_rek' => '1302', 'nm_rek' => 'Piutang Pasien Umum', 'tipe' => 'N', 'balance' => 'D', 'level' => '1'],
            '1401' => ['kd_rek' => '1401', 'nm_rek' => 'Persediaan Obat & BHP', 'tipe' => 'N', 'balance' => 'D', 'level' => '1'],
            '1601' => ['kd_rek' => '1601', 'nm_rek' => 'Gedung & Bangunan', 'tipe' => 'N', 'balance' => 'D', 'level' => '1'],
            '1701' => ['kd_rek' => '1701', 'nm_rek' => 'Peralatan Medis', 'tipe' => 'N', 'balance' => 'D', 'level' => '1'],
            '1801' => ['kd_rek' => '1801', 'nm_rek' => 'Kendaraan', 'tipe' => 'N', 'balance' => 'D', 'level' => '1'],
            '1901' => ['kd_rek' => '1901', 'nm_rek' => 'Inventaris Kantor', 'tipe' => 'N', 'balance' => 'D', 'level' => '1'],
            '2101' => ['kd_rek' => '2101', 'nm_rek' => 'Hutang Usaha', 'tipe' => 'N', 'balance' => 'K', 'level' => '1'],
            '2102' => ['kd_rek' => '2102', 'nm_rek' => 'Hutang Gaji', 'tipe' => 'N', 'balance' => 'K', 'level' => '1'],
            '2201' => ['kd_rek' => '2201', 'nm_rek' => 'Hutang Bank', 'tipe' => 'N', 'balance' => 'K', 'level' => '1'],
            '3101' => ['kd_rek' => '3101', 'nm_rek' => 'Modal Disetor', 'tipe' => 'M', 'balance' => 'K', 'level' => '1'],
            '3201' => ['kd_rek' => '3201', 'nm_rek' => 'Laba Ditahan', 'tipe' => 'M', 'balance' => 'K', 'level' => '1'],
            '4101' => ['kd_rek' => '4101', 'nm_rek' => 'Pendapatan Rawat Jalan', 'tipe' => 'R', 'balance' => 'K', 'level' => '1'],
            '4102' => ['kd_rek' => '4102', 'nm_rek' => 'Pendapatan Rawat Inap', 'tipe' => 'R', 'balance' => 'K', 'level' => '1'],
            '4103' => ['kd_rek' => '4103', 'nm_rek' => 'Pendapatan Obat & BHP', 'tipe' => 'R', 'balance' => 'K', 'level' => '1'],
            '4104' => ['kd_rek' => '4104', 'nm_rek' => 'Pendapatan Laboratorium', 'tipe' => 'R', 'balance' => 'K', 'level' => '1'],
            '4105' => ['kd_rek' => '4105', 'nm_rek' => 'Pendapatan Radiologi', 'tipe' => 'R', 'balance' => 'K', 'level' => '1'],
            '4201' => ['kd_rek' => '4201', 'nm_rek' => 'Pendapatan Lain-lain', 'tipe' => 'R', 'balance' => 'K', 'level' => '1'],
            '5101' => ['kd_rek' => '5101', 'nm_rek' => 'Beban Gaji Dokter', 'tipe' => 'R', 'balance' => 'D', 'level' => '1'],
            '5102' => ['kd_rek' => '5102', 'nm_rek' => 'Beban Gaji Paramedis', 'tipe' => 'R', 'balance' => 'D', 'level' => '1'],
            '5103' => ['kd_rek' => '5103', 'nm_rek' => 'Beban Gaji Karyawan', 'tipe' => 'R', 'balance' => 'D', 'level' => '1'],
            '5201' => ['kd_rek' => '5201', 'nm_rek' => 'Beban Obat & BHP', 'tipe' => 'R', 'balance' => 'D', 'level' => '1'],
            '5301' => ['kd_rek' => '5301', 'nm_rek' => 'Beban Listrik', 'tipe' => 'R', 'balance' => 'D', 'level' => '1'],
            '5302' => ['kd_rek' => '5302', 'nm_rek' => 'Beban Air & Kebersihan', 'tipe' => 'R', 'balance' => 'D', 'level' => '1'],
            '5401' => ['kd_rek' => '5401', 'nm_rek' => 'Beban Penyusutan', 'tipe' => 'R', 'balance' => 'D', 'level' => '1'],
            '5501' => ['kd_rek' => '5501', 'nm_rek' => 'Beban Administrasi Umum', 'tipe' => 'R', 'balance' => 'D', 'level' => '1']
        ];

        $requiredRekening = [];
        foreach ($rekeningtahun as $item) {
            $requiredRekening[$item['kd_rek']] = true;
        }
        foreach ($detailjurnal as $item) {
            $requiredRekening[$item['kd_rek']] = true;
        }

        $missingRekening = [];
        $rekeningToInsert = [];
        foreach (array_keys($requiredRekening) as $kd_rek) {
            $rekening = $this->db('mlite_rekening')->where('kd_rek', $kd_rek)->oneArray();
            if (empty($rekening)) {
                if (isset($defaultRekening[$kd_rek])) {
                    $rekeningToInsert[$kd_rek] = $defaultRekening[$kd_rek];
                } else {
                    $missingRekening[] = $kd_rek;
                }
            }
        }

        if (!empty($missingRekening)) {
            $this->notify('failure', 'Insert data dummy gagal. Akun rekening tidak ditemukan: '.implode(', ', $missingRekening));
            redirect(url([ADMIN, 'keuangan', 'settings']));
            return;
        }

        $this->db()->pdo()->beginTransaction();

        try {
            foreach ($rekeningToInsert as $item) {
                $exists = $this->db('mlite_rekening')->where('kd_rek', $item['kd_rek'])->oneArray();
                if (empty($exists)) {
                    $this->db('mlite_rekening')->save($item);
                }
            }

            $insertedRekeningTahun = 0;
            foreach ($rekeningtahun as $item) {
                $exists = $this->db('mlite_rekeningtahun')->where('thn', $item['thn'])->where('kd_rek', $item['kd_rek'])->oneArray();
                if (empty($exists)) {
                    $this->db('mlite_rekeningtahun')->save($item);
                    $insertedRekeningTahun++;
                }
            }

            $insertedJurnal = 0;
            foreach ($jurnal as $item) {
                $exists = $this->db('mlite_jurnal')->where('no_jurnal', $item['no_jurnal'])->oneArray();
                if (empty($exists)) {
                    $this->db('mlite_jurnal')->save($item);
                    $insertedJurnal++;
                }
            }

            $insertedDetail = 0;
            foreach ($detailjurnal as $item) {
                $exists = $this->db('mlite_detailjurnal')
                    ->where('no_jurnal', $item['no_jurnal'])
                    ->where('kd_rek', $item['kd_rek'])
                    ->where('arus_kas', $item['arus_kas'])
                    ->where('debet', $item['debet'])
                    ->where('kredit', $item['kredit'])
                    ->oneArray();
                if (empty($exists)) {
                    $this->db('mlite_detailjurnal')->save($item);
                    $insertedDetail++;
                }
            }

            $this->db()->pdo()->commit();
            $this->notify('success', 'Data dummy keuangan diproses. Insert baru: rekening tahun '.$insertedRekeningTahun.', jurnal '.$insertedJurnal.', detail jurnal '.$insertedDetail.'.');
        } catch (\Exception $e) {
            $this->db()->pdo()->rollBack();
            $this->notify('failure', 'Insert data dummy keuangan gagal: '.$e->getMessage());
        }

        redirect(url([ADMIN, 'keuangan', 'settings']));
    }


    public function postSaveAkunKegiatan()
    {
        if(isset($_POST['simpan']) && $_POST['simpan']) {
          $this->db('mlite_akun_kegiatan')
          ->save([
            'kegiatan' => $_POST['nama_kegiatan'],
            'kd_rek' => $_POST['kd_rek']
          ]);
          $this->notify('success', 'Nama kegiatan keuangan telah disimpan');
        } else if (isset($_POST['update']) && $_POST['update']) {
          $this->db('mlite_akun_kegiatan')
          ->where('id', $_POST['id'])
          ->save([
            'kegiatan' => $_POST['nama_kegiatan'],
            'kd_rek' => $_POST['kd_rek']
          ]);
          $this->notify('success', 'Nama kegiatan keuangan telah diubah');
        } else if (isset($_POST['hapus']) && $_POST['hapus']) {
          $this->db('mlite_akun_kegiatan')
          ->where('id', $_POST['id'])
          ->delete();
          $this->notify('success', 'Nama kegiatan keuangan telah dihapus');
        }
        redirect(url([ADMIN, 'keuangan', 'pengaturanrekening']));
    }

    public function postSaveSettingsRekening()
    {
        foreach ($_POST['kegiatan'] as $key => $val) {
            $this->db('mlite_akun_kegiatan')
            ->where('id', $key)
            ->save([
              'kd_rek' => $val
            ]);
        }
        $this->notify('success', 'Pengaturan rekening keuangan telah disimpan');
        redirect(url([ADMIN, 'keuangan', 'pengaturanrekening']));
    }

    public function postSaveJurnal()
    {
        // Validasi input
        if(empty($_POST['entries']) || !is_array($_POST['entries'])) {
            $this->notify('failure', 'Data jurnal tidak valid. Minimal harus ada 2 entry (debet dan kredit).');
            redirect(url([ADMIN, 'keuangan', 'postingjurnal']));
            return;
        }

        $entries = $_POST['entries'];
        $total_debet = 0;
        $total_kredit = 0;
        $valid_entries = [];

        // Validasi setiap entry
        foreach($entries as $entry) {
            if(empty($entry['kd_rek'])) continue;
            
            $debet = floatval($entry['debet'] ?? 0);
            $kredit = floatval($entry['kredit'] ?? 0);
            
            // Validasi: tidak boleh debet dan kredit bersamaan atau keduanya kosong
            if(($debet > 0 && $kredit > 0) || ($debet == 0 && $kredit == 0)) {
                $this->notify('failure', 'Setiap entry harus memiliki DEBET atau KREDIT saja, tidak boleh keduanya atau kosong.');
                redirect(url([ADMIN, 'keuangan', 'postingjurnal']));
                return;
            }
            
            $total_debet += $debet;
            $total_kredit += $kredit;
            
            $valid_entries[] = [
                'kd_rek' => $entry['kd_rek'],
                'debet' => $debet,
                'kredit' => $kredit,
                'keterangan' => $entry['keterangan'] ?? ''
            ];
        }

        // Validasi minimal 2 entries
        if(count($valid_entries) < 2) {
            $this->notify('failure', 'Jurnal harus memiliki minimal 2 entry (debet dan kredit).');
            redirect(url([ADMIN, 'keuangan', 'postingjurnal']));
            return;
        }

        // Validasi balance: total debet harus sama dengan total kredit
        if(abs($total_debet - $total_kredit) > 0.01) { // toleransi 1 sen untuk floating point
            $selisih = abs($total_debet - $total_kredit);
            $this->notify('failure', 'Jurnal tidak balance! Total Debet: '.number_format($total_debet,2).' - Total Kredit: '.number_format($total_kredit,2).' (Selisih: '.number_format($selisih,2).')');
            redirect(url([ADMIN, 'keuangan', 'postingjurnal']));
            return;
        }

        // Mulai transaksi database
        $this->db()->pdo()->beginTransaction();
        
        try {
            // Simpan header jurnal
            $this->db('mlite_jurnal')->save([
                'no_jurnal' => $_POST['no_jurnal'],
                'no_bukti' => $_POST['no_bukti'],
                'tgl_jurnal' => $_POST['tgl_jurnal'],
                'jenis' => $_POST['jenis'],
                'kegiatan' => $_POST['kegiatan'],
                'keterangan' => $_POST['kegiatan'].'. Diposting oleh '.$this->core->getUserInfo('fullname', null, true).'. ('.$_POST['keterangan_umum'].')'
            ]);

            // Hapus detail jurnal lama jika ada (untuk update)
            $this->db('mlite_detailjurnal')->where('no_jurnal', $_POST['no_jurnal'])->delete();

            // Simpan setiap detail jurnal
            foreach($valid_entries as $entry) {
                $this->db('mlite_detailjurnal')->save([
                    'no_jurnal' => $_POST['no_jurnal'],
                    'kd_rek' => $entry['kd_rek'],
                    'arus_kas' => 0, // Default value untuk field arus_kas
                    'debet' => $entry['debet'],
                    'kredit' => $entry['kredit']
                ]);
            }

            // Commit transaksi
            $this->db()->pdo()->commit();
            
            $this->notify('success', 'Posting jurnal berhasil disimpan. Total Debet: '.number_format($total_debet,2).' - Total Kredit: '.number_format($total_kredit,2));
            
        } catch(\Exception $e) {
            // Rollback jika ada error
            $this->db()->pdo()->rollback();
            $this->notify('failure', 'Gagal menyimpan jurnal: '.$e->getMessage());
        }
        
        redirect(url([ADMIN, 'keuangan', 'postingjurnal']));
    }

    private function _exportNeracaExcel($data)
    {
        $settings = $this->settings('settings');
        
        // Set headers untuk download Excel
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="Neraca_'.date('Y-m-d', strtotime($data['tgl_awal'])).'_to_'.date('Y-m-d', strtotime($data['tgl_akhir'])).'.xls"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo '<html>';
        echo '<head>';
        echo '<meta charset="UTF-8">';
        echo '<style>';
        echo 'table { border-collapse: collapse; width: 100%; }';
        echo 'th, td { border: 1px solid black; padding: 8px; text-align: left; }';
        echo 'th { background-color: #f0f0f0; font-weight: bold; }';
        echo '.text-center { text-align: center; }';
        echo '.text-right { text-align: right; }';
        echo '.bg-info { background-color: #d9edf7; font-weight: bold; }';
        echo '.bg-light { background-color: #f9f9f9; font-weight: bold; }';
        echo '.bg-success { background-color: #dff0d8; font-weight: bold; }';
        echo '</style>';
        echo '</head>';
        echo '<body>';
        
        // Header
        echo '<table>';
        echo '<tr><td colspan="6" class="text-center"><h2>'.$settings['nama_instansi'].'</h2></td></tr>';
        echo '<tr><td colspan="6" class="text-center">'.$settings['alamat'].'</td></tr>';
        echo '<tr><td colspan="6" class="text-center"><h3>LAPORAN NERACA</h3></td></tr>';
        echo '<tr><td colspan="6" class="text-center">Periode '.date('d-m-Y', strtotime($data['tgl_awal'])).' s/d '.date('d-m-Y', strtotime($data['tgl_akhir'])).'</td></tr>';
        echo '<tr><td colspan="6"></td></tr>';
        
        // Tabel Neraca
        echo '<tr>';
        echo '<th colspan="3" class="text-center">AKTIVA</th>';
        echo '<th colspan="3" class="text-center">PASIVA</th>';
        echo '</tr>';
        
        // Header kolom
        echo '<tr>';
        echo '<th>Kode</th><th>Nama Akun</th><th class="text-right">Jumlah (Rp)</th>';
        echo '<th>Kode</th><th>Nama Akun</th><th class="text-right">Jumlah (Rp)</th>';
        echo '</tr>';
        
        // Aktiva Lancar vs Hutang Lancar
        echo '<tr class="bg-info">';
        echo '<td colspan="3">AKTIVA LANCAR</td>';
        echo '<td colspan="3">HUTANG LANCAR</td>';
        echo '</tr>';
        
        $max_rows = max(count($data['aktiva_lancar']), count($data['hutang_lancar']));
        for($i = 0; $i < $max_rows; $i++) {
            echo '<tr>';
            if(isset($data['aktiva_lancar'][$i])) {
                $item = $data['aktiva_lancar'][$i];
                echo '<td>'.$item['kd_rek'].'</td>';
                echo '<td>'.$item['nm_rek'].'</td>';
                echo '<td class="text-right">'.number_format($item['saldo'],2,',','.').'</td>';
            } else {
                echo '<td></td><td></td><td></td>';
            }
            
            if(isset($data['hutang_lancar'][$i])) {
                $item = $data['hutang_lancar'][$i];
                echo '<td>'.$item['kd_rek'].'</td>';
                echo '<td>'.$item['nm_rek'].'</td>';
                echo '<td class="text-right">'.number_format($item['saldo'],2,',','.').'</td>';
            } else {
                echo '<td></td><td></td><td></td>';
            }
            echo '</tr>';
        }
        
        // Total Aktiva Lancar vs Total Hutang Lancar
        echo '<tr class="bg-light">';
        echo '<td colspan="2">TOTAL AKTIVA LANCAR</td>';
        echo '<td class="text-right">'.number_format($data['total_aktiva_lancar'],2,',','.').'</td>';
        echo '<td colspan="2">TOTAL HUTANG LANCAR</td>';
        echo '<td class="text-right">'.number_format($data['total_hutang_lancar'],2,',','.').'</td>';
        echo '</tr>';
        
        // Aktiva Tetap vs Hutang Jangka Panjang
        echo '<tr class="bg-info">';
        echo '<td colspan="3">AKTIVA TETAP</td>';
        echo '<td colspan="3">HUTANG JANGKA PANJANG</td>';
        echo '</tr>';
        
        $max_rows = max(count($data['aktiva_tetap']), count($data['hutang_jangka_panjang']));
        for($i = 0; $i < $max_rows; $i++) {
            echo '<tr>';
            if(isset($data['aktiva_tetap'][$i])) {
                $item = $data['aktiva_tetap'][$i];
                echo '<td>'.$item['kd_rek'].'</td>';
                echo '<td>'.$item['nm_rek'].'</td>';
                echo '<td class="text-right">'.number_format($item['saldo'],2,',','.').'</td>';
            } else {
                echo '<td></td><td></td><td></td>';
            }
            
            if(isset($data['hutang_jangka_panjang'][$i])) {
                $item = $data['hutang_jangka_panjang'][$i];
                echo '<td>'.$item['kd_rek'].'</td>';
                echo '<td>'.$item['nm_rek'].'</td>';
                echo '<td class="text-right">'.number_format($item['saldo'],2,',','.').'</td>';
            } else {
                echo '<td></td><td></td><td></td>';
            }
            echo '</tr>';
        }
        
        // Total Aktiva Tetap vs Total Hutang Jangka Panjang
        echo '<tr class="bg-light">';
        echo '<td colspan="2">TOTAL AKTIVA TETAP</td>';
        echo '<td class="text-right">'.number_format($data['total_aktiva_tetap'],2,',','.').'</td>';
        echo '<td colspan="2">TOTAL HUTANG JANGKA PANJANG</td>';
        echo '<td class="text-right">'.number_format($data['total_hutang_jangka_panjang'],2,',','.').'</td>';
        echo '</tr>';
        
        // Modal
        echo '<tr class="bg-info">';
        echo '<td colspan="3"></td>';
        echo '<td colspan="3">MODAL</td>';
        echo '</tr>';
        
        foreach($data['modal'] as $item) {
            echo '<tr>';
            echo '<td></td><td></td><td></td>';
            echo '<td>'.$item['kd_rek'].'</td>';
            echo '<td>'.$item['nm_rek'].'</td>';
            echo '<td class="text-right">'.number_format($item['saldo'],2,',','.').'</td>';
            echo '</tr>';
        }
        
        // Total Modal
        echo '<tr class="bg-light">';
        echo '<td colspan="3"></td>';
        echo '<td colspan="2">TOTAL MODAL</td>';
        echo '<td class="text-right">'.number_format($data['total_modal'],2,',','.').'</td>';
        echo '</tr>';
        
        // Total Aktiva vs Total Pasiva
        echo '<tr class="bg-success">';
        echo '<td colspan="2">TOTAL AKTIVA</td>';
        echo '<td class="text-right">'.number_format($data['total_aktiva'],2,',','.').'</td>';
        echo '<td colspan="2">TOTAL PASIVA</td>';
        echo '<td class="text-right">'.number_format($data['total_pasiva'],2,',','.').'</td>';
        echo '</tr>';
        
        // Validasi Keseimbangan
        echo '<tr><td colspan="6"></td></tr>';
        echo '<tr>';
        echo '<td colspan="6"><strong>Validasi Keseimbangan:</strong></td>';
        echo '</tr>';
        if($data['seimbang']) {
            echo '<tr><td colspan="6" style="color: green;">✓ NERACA SEIMBANG</td></tr>';
        } else {
            echo '<tr><td colspan="6" style="color: red;">✗ NERACA TIDAK SEIMBANG - Selisih: '.number_format(abs($data['selisih']),2,',','.').'</td></tr>';
        }
        
        echo '</table>';
        echo '</body>';
        echo '</html>';
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
    }

}
