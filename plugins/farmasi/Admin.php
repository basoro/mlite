<?php

namespace Plugins\Farmasi;

use Systems\AdminModule;

class Admin extends AdminModule
{
    public $assign = [];

    public function navigation()
    {
        return [
            'Kelola' => 'manage',
            'Mutasi Obat & BHP' => 'mutasi',
            'Pengajuan Obat & BMHP' => 'pengajuanobatbmhp',
            'Pemesanan Obat & BMHP' => 'pemesananobatbmhp',
            'Penerimaan Obat & BMHP' => 'penerimaanobatbmhp',
            'Stok Opname' => 'opname',
            'Daftar Batch Obat' => 'batch',
            'Darurat Stok' => 'daruratstok',
            'Detail Pemberian Obat' => 'detailpemberianobat',
            'Riwayat Barang Medis' => 'riwayatbarangmedis',
            'Pengaturan' => 'settings',
        ];
    }

    public function getManage()
    {
      $mlite_crud_permissions = $this->_crudPermissionsFarmasi();
      $sub_modules = [
        ['name' => 'Mutasi Obat & BHP', 'url' => url([ADMIN, 'farmasi', 'mutasi']), 'icon' => 'medkit', 'desc' => 'Data obat dan barang habis pakai'],
        ['name' => 'Pengajuan Obat & BMHP', 'url' => url([ADMIN, 'farmasi', 'pengajuanobatbmhp']), 'icon' => 'file-text-o', 'desc' => 'Pengajuan kebutuhan obat dan BMHP'],
        ['name' => 'Pemesanan Obat & BMHP', 'url' => url([ADMIN, 'farmasi', 'pemesananobatbmhp']), 'icon' => 'shopping-cart', 'desc' => 'Pemesanan obat/BMHP dan cetak surat pemesanan'],
        ['name' => 'Penerimaan Obat & BMHP', 'url' => url([ADMIN, 'farmasi', 'penerimaanobatbmhp']), 'icon' => 'download', 'desc' => 'Penerimaan obat/BMHP dan penambahan stok gudang'],
        ['name' => 'Stok Opname', 'url' => url([ADMIN, 'farmasi', 'opname']), 'icon' => 'medkit', 'desc' => 'Tambah stok opname'],
        ['name' => 'Daftar Batch Obat', 'url' => url([ADMIN, 'farmasi', 'batch']), 'icon' => 'tags', 'desc' => 'Stok tersisa, harga & kedaluwarsa per batch (FEFO)'],
        ['name' => 'Darurat Stok', 'url' => url([ADMIN, 'farmasi', 'daruratstok']), 'icon' => 'warning', 'desc' => 'Monitoring stok darurat obat dan BHP'],
        ['name' => 'Detail Pemberian Obat', 'url' => url([ADMIN, 'farmasi', 'detailpemberianobat']), 'icon' => 'medkit', 'desc' => 'Detail pemberian obat pasien'],
        ['name' => 'Riwayat Barang Medis', 'url' => url([ADMIN, 'farmasi', 'riwayatbarangmedis']), 'icon' => 'medkit', 'desc' => 'Riwayat pergerakan barang medis'],
        ['name' => 'Pengaturan', 'url' => url([ADMIN, 'farmasi', 'settings']), 'icon' => 'medkit', 'desc' => 'Pengaturan farmasi dan depo'],
      ];
      if ($mlite_crud_permissions['can_read'] !== 'true') {
        $sub_modules = [];
      }

      // Peringatan batch kedaluwarsa / segera kedaluwarsa ≤ 90 hari (stok tersisa)
      $expired_alert = [];
      $stmt = $this->db()->pdo()->prepare("SELECT b.kode_brng, d.nama_brng, g.nm_bangsal, b.no_batch, b.expire, b.stok, d.kode_sat
        FROM mlite_farmasi_obat_batch b
        LEFT JOIN databarang d ON d.kode_brng = b.kode_brng
        LEFT JOIN bangsal g ON g.kd_bangsal = b.kd_bangsal
        WHERE b.stok > 0 AND b.expire IS NOT NULL AND b.expire <= ?
        ORDER BY b.expire ASC, b.kode_brng ASC");
      $stmt->execute([date('Y-m-d', strtotime('+90 day'))]);
      foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
        $selisih = (int) floor((strtotime((string) $row['expire']) - strtotime(date('Y-m-d'))) / 86400);
        $row['selisih_hari'] = $selisih;
        $row['status'] = $selisih < 0 ? 'Kedaluwarsa' : 'Segera Kedaluwarsa';
        $expired_alert[] = $row;
      }

      return $this->draw('manage.html', [
        'sub_modules' => htmlspecialchars_array($sub_modules),
        'mlite_crud_permissions' => htmlspecialchars_array($mlite_crud_permissions),
        'expired_alert' => htmlspecialchars_array($expired_alert)
      ]);
    }

    public function getMutasi($status = '1')
    {
        $this->_addHeaderFiles();
        $databarang['title'] = 'Kelola Mutasi Obat';
        $databarang['bangsal']  = $this->db('bangsal')->toArray();
        $databarang['list'] = $this->_databarangList($status);
        return $this->draw('mutasi.html', ['databarang' => htmlspecialchars_array($databarang), 'tab' => $status]);
    }

    private function _databarangList($status)
    {
        $result = [];

        foreach ($this->db('databarang')->where('status', $status)->toArray() as $row) {
            $row['delURL']  = url([ADMIN, 'farmasi', 'delete', $row['kode_brng']]);
            $row['restoreURL']  = url([ADMIN, 'farmasi', 'restore', $row['kode_brng']]);
            $row['gudangbarang'] = $this->db('gudangbarang')->join('bangsal', 'bangsal.kd_bangsal=gudangbarang.kd_bangsal')->where('kode_brng', $row['kode_brng'])->toArray();
            foreach ($row['gudangbarang'] as &$gb) {
                $ledger = $this->db('mlite_farmasi_obat_batch')
                    ->where('kode_brng', $row['kode_brng'])
                    ->where('kd_bangsal', $gb['kd_bangsal'])
                    ->where('no_batch', $gb['no_batch'])
                    ->where('no_faktur', $gb['no_faktur'])
                    ->oneArray();
                $gb['expire'] = $ledger['expire'] ?? '';
                $gb['h_beli_batch'] = $ledger['h_beli'] ?? 0;
            }
            unset($gb);
            $result[] = $row;
        }
        return $result;
    }

    public function getDelete($id)
    {
        if ($this->db('databarang')->where('kode_brng', $id)->update('status', '0')) {
            $this->notify('success', 'Hapus sukses');
        } else {
            $this->notify('failure', 'Hapus gagal');
        }
        redirect(url([ADMIN, 'farmasi', 'mutasi']));
    }

    public function getRestore($id)
    {
        if ($this->db('databarang')->where('kode_brng', $id)->update('status', '1')) {
            $this->notify('success', 'Restore sukses');
        } else {
            $this->notify('failure', 'Restore gagal');
        }
        redirect(url([ADMIN, 'farmasi', 'mutasi']));
    }

    public function postSetStok()
    {
      $pdo = $this->db()->pdo();
      $transaksi_set_stok = !$pdo->inTransaction();
      if ($transaksi_set_stok) {
        $pdo->beginTransaction();
      }
      try {
      $kode_brng = $_POST['kode_brng'];
      $kd_bangsal = $_POST['kd_bangsal'];
      $stok = (float) $_POST['stok'];
      $no_batch = trim($_POST['no_batch'] ?? '');
      $no_faktur = trim($_POST['no_faktur'] ?? '');
      $harga = (float) str_replace(',', '.', trim((string)($_POST['harga'] ?? '0')));
      $kd_gudang = $this->settings->get('farmasi.gudang');
      $petugas = $this->core->getUserInfo('fullname', null, true);
      $databarang = $this->db('databarang')->where('kode_brng', $kode_brng)->oneArray();

      if ($kd_bangsal == $kd_gudang) {
        // Pengadaan langsung ke gudang: catat sebagai batch (FEFO menyusul expire)
        $row = $this->db('gudangbarang')->where('kode_brng', $kode_brng)->where('kd_bangsal', $kd_gudang)->oneArray();
        $stok_awal = (float)($row['stok'] ?? 0);
        $this->db('riwayat_barang_medis')->save([
          'kode_brng' => $kode_brng, 'stok_awal' => $stok_awal, 'masuk' => $stok, 'keluar' => '0',
          'stok_akhir' => $stok_awal + $stok, 'posisi' => 'Pengadaan', 'tanggal' => date('Y-m-d'),
          'jam' => date('H:i:s'), 'petugas' => $petugas, 'kd_bangsal' => $kd_gudang, 'status' => 'Simpan',
          'no_batch' => $no_batch !== '' ? $no_batch : '0', 'no_faktur' => $no_faktur !== '' ? $no_faktur : '0', 'keterangan' => '-'
        ]);
        \Systems\PharmacyBatches::credit($pdo, $kode_brng, $kd_gudang, $no_batch, $no_faktur, $stok, $harga);
        if ($row) {
          $this->db('gudangbarang')->where('kode_brng', $kode_brng)->where('kd_bangsal', $kd_gudang)->save([
            'stok' => $stok_awal + $stok,
            'no_batch' => $no_batch !== '' ? $no_batch : ($row['no_batch'] ?? '0'),
            'no_faktur' => $no_faktur !== '' ? $no_faktur : ($row['no_faktur'] ?? '0')
          ]);
        } else {
          $this->db('gudangbarang')->save([
            'kode_brng' => $kode_brng, 'kd_bangsal' => $kd_gudang, 'stok' => $stok,
            'no_batch' => $no_batch !== '' ? $no_batch : '0', 'no_faktur' => $no_faktur !== '' ? $no_faktur : '0'
          ]);
        }
        $this->db('mutasibarang')->save([
          'kode_brng' => $kode_brng, 'jml' => $stok, 'harga' => $harga > 0 ? $harga : (float)($databarang['dasar'] ?? 0),
          'kd_bangsaldari' => $kd_gudang, 'kd_bangsalke' => $kd_gudang, 'tanggal' => date('Y-m-d H:i:s'),
          'keterangan' => $_POST['keterangan'] ?? 'Set Stok - Pengadaan', 'no_batch' => $no_batch !== '' ? $no_batch : '0',
          'no_faktur' => $no_faktur !== '' ? $no_faktur : '0'
        ]);
      } else {
        // Mutasi gudang → depot: alokasikan batch FEFO (atau batch pilihan petugas)
        $row = $this->db('gudangbarang')->where('kode_brng', $kode_brng)->where('kd_bangsal', $kd_gudang)->oneArray();
        $stok_awal = (float)($row['stok'] ?? 0);
        $portions = \Systems\PharmacyBatches::allocateWithFallback($pdo, $kode_brng, $kd_gudang, $stok, $no_batch !== '' ? $no_batch : null);
        if ($harga <= 0) {
          $harga = (float)($databarang['h_beli'] ?? 0);
        }
        foreach ($portions as &$portion) {
          if ((float)$portion['h_beli'] <= 0) {
            $portion['h_beli'] = $harga > 0 ? $harga : (float)($databarang['dasar'] ?? 0);
          }
        }
        unset($portion);

        $keluar_total = 0;
        foreach ($portions as $portion) {
          $keluar_total += (float) $portion['jml'];
          $this->db('riwayat_barang_medis')->save([
            'kode_brng' => $kode_brng, 'stok_awal' => $stok_awal, 'masuk' => '0', 'keluar' => $portion['jml'],
            'stok_akhir' => $stok_awal - $keluar_total, 'posisi' => 'Mutasi', 'tanggal' => date('Y-m-d'),
            'jam' => date('H:i:s'), 'petugas' => $petugas, 'kd_bangsal' => $kd_gudang, 'status' => 'Simpan',
            'no_batch' => $portion['no_batch'], 'no_faktur' => $portion['no_faktur'], 'keterangan' => '-'
          ]);
          $depot_awal_row = $this->db('gudangbarang')->where('kode_brng', $kode_brng)->where('kd_bangsal', $kd_bangsal)->oneArray();
          $depot_awal = (float)($depot_awal_row['stok'] ?? 0);
          $this->db('riwayat_barang_medis')->save([
            'kode_brng' => $kode_brng, 'stok_awal' => $depot_awal, 'masuk' => $portion['jml'], 'keluar' => '0',
            'stok_akhir' => $depot_awal + $portion['jml'], 'posisi' => 'Mutasi', 'tanggal' => date('Y-m-d'),
            'jam' => date('H:i:s'), 'petugas' => $petugas, 'kd_bangsal' => $kd_bangsal, 'status' => 'Simpan',
            'no_batch' => $portion['no_batch'], 'no_faktur' => $portion['no_faktur'], 'keterangan' => '-'
          ]);
          // Batch masuk ke depot dengan harga beli & kedaluwarsa batch sumber
          \Systems\PharmacyBatches::credit($pdo, $kode_brng, $kd_bangsal, $portion['no_batch'], $portion['no_faktur'], $portion['jml'], $portion['h_beli'], $portion['expire'] ?? null);
          $this->db('mutasibarang')->save([
            'kode_brng' => $kode_brng, 'jml' => $portion['jml'], 'harga' => $portion['h_beli'],
            'kd_bangsaldari' => $kd_gudang, 'kd_bangsalke' => $kd_bangsal, 'tanggal' => date('Y-m-d H:i:s'),
            'keterangan' => $_POST['keterangan'] ?? 'Set Stok - Mutasi', 'no_batch' => $portion['no_batch'], 'no_faktur' => $portion['no_faktur']
          ]);
        }

        if ($row) {
          $this->db('gudangbarang')->where('kode_brng', $kode_brng)->where('kd_bangsal', $kd_gudang)->save(['stok' => $stok_awal - $keluar_total]);
        }
        $depot_row = $this->db('gudangbarang')->where('kode_brng', $kode_brng)->where('kd_bangsal', $kd_bangsal)->oneArray();
        if ($depot_row) {
          $this->db('gudangbarang')->where('kode_brng', $kode_brng)->where('kd_bangsal', $kd_bangsal)->save(['stok' => (float)$depot_row['stok'] + $keluar_total]);
        } else {
          $this->db('gudangbarang')->save([
            'kode_brng' => $kode_brng, 'kd_bangsal' => $kd_bangsal, 'stok' => $keluar_total,
            'no_batch' => $portions ? $portions[0]['no_batch'] : '0',
            'no_faktur' => $portions ? $portions[0]['no_faktur'] : '0'
          ]);
        }
      }
      if ($transaksi_set_stok && $pdo->inTransaction()) {
        $pdo->commit();
      }
      header('Content-Type: application/json');
      echo json_encode(['status' => 'success', 'message' => 'Set stok berhasil disimpan.']);
      } catch (\Throwable $e) {
        if ($transaksi_set_stok && $pdo->inTransaction()) {
          $pdo->rollBack();
        }
        error_log('Set stok (farmasi, kode_brng=' . (isset($kode_brng) ? $kode_brng : '-') . ') gagal: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Set stok gagal disimpan: ' . $e->getMessage()]);
      }

      exit();
    }

    public function postReStok()
    {
      $pdo = $this->db()->pdo();
      $transaksi_re_stok = !$pdo->inTransaction();
      if ($transaksi_re_stok) {
        $pdo->beginTransaction();
      }
      try {
      $kode_brng = $_POST['kode_brng'];
      $kd_bangsal = $_POST['kd_bangsal'];
      $stok = (float) $_POST['stok'];
      $no_batch = trim($_POST['no_batch'] ?? '');
      $no_faktur = trim($_POST['no_faktur'] ?? '');
      $kd_gudang = $this->settings->get('farmasi.gudang');
      $petugas = $this->core->getUserInfo('fullname', null, true);
      $databarang = $this->db('databarang')->where('kode_brng', $kode_brng)->oneArray();

      // Restok: kembalikan dari depot ke gudang, batch FEFO dari depot
      $depot_row = $this->db('gudangbarang')->where('kode_brng', $kode_brng)->where('kd_bangsal', $kd_bangsal)->oneArray();
      $gudang_row = $this->db('gudangbarang')->where('kode_brng', $kode_brng)->where('kd_bangsal', $kd_gudang)->oneArray();

      $portions = \Systems\PharmacyBatches::allocateWithFallback($pdo, $kode_brng, $kd_bangsal, $stok, $no_batch !== '' ? $no_batch : null);

      $masuk_total = 0;
      foreach ($portions as $portion) {
        $masuk_total += (float) $portion['jml'];
        $depot_awal = (float)($depot_row['stok'] ?? 0);
        $gudang_awal = (float)($gudang_row['stok'] ?? 0);
        $this->db('riwayat_barang_medis')->save([
          'kode_brng' => $kode_brng, 'stok_awal' => $depot_awal, 'masuk' => '0', 'keluar' => $portion['jml'],
          'stok_akhir' => $depot_awal - $masuk_total, 'posisi' => 'Mutasi', 'tanggal' => date('Y-m-d'),
          'jam' => date('H:i:s'), 'petugas' => $petugas, 'kd_bangsal' => $kd_bangsal, 'status' => 'Simpan',
          'no_batch' => $portion['no_batch'], 'no_faktur' => $portion['no_faktur'], 'keterangan' => ''
        ]);
        $this->db('riwayat_barang_medis')->save([
          'kode_brng' => $kode_brng, 'stok_awal' => $gudang_awal, 'masuk' => $portion['jml'], 'keluar' => '0',
          'stok_akhir' => $gudang_awal + $masuk_total, 'posisi' => 'Mutasi', 'tanggal' => date('Y-m-d'),
          'jam' => date('H:i:s'), 'petugas' => $petugas, 'kd_bangsal' => $kd_gudang, 'status' => 'Simpan',
          'no_batch' => $portion['no_batch'], 'no_faktur' => $portion['no_faktur'], 'keterangan' => ''
        ]);
        \Systems\PharmacyBatches::credit($pdo, $kode_brng, $kd_gudang, $portion['no_batch'], $portion['no_faktur'], $portion['jml'], $portion['h_beli'], $portion['expire'] ?? null);
        $this->db('mutasibarang')->save([
          'kode_brng' => $kode_brng, 'jml' => $portion['jml'], 'harga' => $portion['h_beli'],
          'kd_bangsaldari' => $kd_bangsal, 'kd_bangsalke' => $kd_gudang, 'tanggal' => date('Y-m-d H:i:s'),
          'keterangan' => $_POST['keterangan'] ?? 'Set restok - Mutasi', 'no_batch' => $portion['no_batch'],
          'no_faktur' => $portion['no_faktur']
        ]);
      }

      if ($depot_row) {
        $this->db('gudangbarang')->where('kode_brng', $kode_brng)->where('kd_bangsal', $kd_bangsal)->save(['stok' => (float)$depot_row['stok'] - $masuk_total]);
      }
      if ($gudang_row) {
        $this->db('gudangbarang')->where('kode_brng', $kode_brng)->where('kd_bangsal', $kd_gudang)->save(['stok' => (float)$gudang_row['stok'] + $masuk_total]);
      }
      if ($transaksi_re_stok && $pdo->inTransaction()) {
        $pdo->commit();
      }
      header('Content-Type: application/json');
      echo json_encode(['status' => 'success', 'message' => 'Restok berhasil disimpan.']);
      } catch (\Throwable $e) {
        if ($transaksi_re_stok && $pdo->inTransaction()) {
          $pdo->rollBack();
        }
        error_log('Restok (farmasi, kode_brng=' . (isset($kode_brng) ? $kode_brng : '-') . ') gagal: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Restok gagal disimpan: ' . $e->getMessage()]);
      }

      exit();
    }

    /* End Databarang Section */

    /* Info batch untuk modal Mutasi (tarik harga beli otomatis) */
    public function getBatchInfo()
    {
      header('Content-Type: application/json');
      $kode_brng = trim($_GET['kode_brng'] ?? '');
      $no_batch = trim($_GET['no_batch'] ?? '');
      if ($kode_brng === '' || $no_batch === '') {
        echo json_encode(['status' => 'error', 'message' => 'Kode barang dan no. batch wajib diisi.']);
        exit();
      }
      $ledger = $this->db('mlite_farmasi_obat_batch')
        ->where('kode_brng', $kode_brng)
        ->where('kd_bangsal', $this->settings->get('farmasi.gudang'))
        ->where('no_batch', $no_batch)
        ->oneArray();
      if (!$ledger) {
        echo json_encode(['status' => 'error', 'message' => 'Batch tidak ditemukan di gudang.']);
        exit();
      }
      echo json_encode(htmlspecialchars_array([
        'status' => 'success',
        'h_beli' => $ledger['h_beli'] ?? 0,
        'expire' => $ledger['expire'] ?? '',
        'stok' => $ledger['stok'] ?? 0
      ]));
      exit();
    }

    public function getBatch($data='')
    {
      $this->_addHeaderFiles();
      $stmt = $this->db()->pdo()->prepare("SELECT b.*, d.nama_brng, d.kode_sat, g.nm_bangsal
        FROM mlite_farmasi_obat_batch b
        LEFT JOIN databarang d ON d.kode_brng = b.kode_brng
        LEFT JOIN bangsal g ON g.kd_bangsal = b.kd_bangsal
        WHERE b.stok <> 0
        ORDER BY (b.expire IS NULL) ASC, b.expire ASC, b.kode_brng ASC");
      $stmt->execute();
      $batches = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      foreach ($batches as &$batch) {
        if (!empty($batch['expire'])) {
          $expireTs = strtotime($batch['expire']);
          if ($expireTs !== false) {
            $selisihHari = (int) floor(($expireTs - strtotime(date('Y-m-d'))) / 86400);
            if ($selisihHari < 0) {
              $batch['status_expire'] = 'Kedaluwarsa';
            } elseif ($selisihHari <= 90) {
              $batch['status_expire'] = 'Segera Kedaluwarsa';
            } else {
              $batch['status_expire'] = 'Aman';
            }
          } else {
            $batch['status_expire'] = 'Aman';
          }
        } else {
          $batch['status_expire'] = 'Aman';
        }
      }
      unset($batch);
      return $this->draw('batch.html', ['batches' => htmlspecialchars_array($batches)]);
    }

    public function getOpname($data='')
    {
      $this->_addHeaderFiles();
      if($data == 'data') {
        return $this->draw('opname.data.html');
      } else {
        return $this->draw('opname.html');
      }
    }

    public function postOpnameAll()
    {
      $gudangbarang = $this->db('gudangbarang')
        ->join('databarang', 'databarang.kode_brng=gudangbarang.kode_brng')
        ->join('bangsal', 'bangsal.kd_bangsal=gudangbarang.kd_bangsal')
        ->where('databarang.status', '1')
        ->toJson();
      echo $gudangbarang;
      exit();
    }

    public function postOpnameData()
    {
      $opname = $this->db('opname')
        ->join('databarang', 'databarang.kode_brng=opname.kode_brng')
        ->join('bangsal', 'bangsal.kd_bangsal=opname.kd_bangsal')
        ->where('databarang.status', '1')
        ->toJson();
      echo $opname;
      exit();
    }

    public function postOpnameUpdate()
    {
      $kode_brng =$_POST['kode_brng'];
      $real = $_POST['real'];
      $stok = $_POST['stok'];
      $kd_bangsal = $_POST['kd_bangsal'];
      $tanggal = $_POST['tanggal'];
      $h_beli = $_POST['h_beli'];
      $keterangan = $_POST['keterangan'];
      $no_batch = $_POST['no_batch'];
      $no_faktur = $_POST['no_faktur'];
      for($count = 0; $count < count($kode_brng); $count++){
        $selisih = $real[$count] - $stok[$count];
        $nomihilang = $selisih * $h_beli[$count];
        $lebih = 0;
        $nomilebih = 0;
        if($selisih < 0) {
          $selisih = 0;
          $nomihilang = 0;
          $lebih = $stok[$count] - $real[$count];
          $nomilebih = $lebih * $h_beli[$count];
        }

        $query2 = "INSERT INTO `opname` (`kode_brng`, `h_beli`, `tanggal`, `stok`, `real`, `selisih`, `nomihilang`, `lebih`, `nomilebih`, `keterangan`, `kd_bangsal`, `no_batch`, `no_faktur`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $opname2 = $this->db()->pdo()->prepare($query2);
        $opname2->execute([$kode_brng[$count], $h_beli[$count], $tanggal[$count], $real[$count], $stok[$count], $selisih, $nomihilang, $lebih, $nomilebih, $keterangan[$count], $kd_bangsal[$count], $no_batch[$count], $no_faktur[$count]]);

        if ($opname2->errorInfo()[2] == ''){
          $query = "UPDATE gudangbarang SET stok=?, no_batch=?, no_faktur=? WHERE kode_brng=? AND kd_bangsal=? AND no_batch=? AND no_faktur=?";
          $opname = $this->db()->pdo()->prepare($query);              
          $opname->execute([$real[$count], $no_batch[$count], $no_faktur[$count], $kode_brng[$count], $kd_bangsal[$count], $no_batch[$count], $no_faktur[$count]]);

          // Sinkronkan stok batch terkait di ledger (stok opname per batch)
          $batchRow = $this->db()->pdo()->prepare("SELECT id FROM `mlite_farmasi_obat_batch` WHERE kode_brng=? AND kd_bangsal=? AND no_batch=? AND no_faktur=? LIMIT 1");
          $batchRow->execute([$kode_brng[$count], $kd_bangsal[$count], $no_batch[$count], $no_faktur[$count]]);
          if ($batchRow->fetch(\PDO::FETCH_ASSOC)) {
            $this->db()->pdo()->prepare("UPDATE `mlite_farmasi_obat_batch` SET stok=?, updated_at=NOW() WHERE kode_brng=? AND kd_bangsal=? AND no_batch=? AND no_faktur=?")
              ->execute([$real[$count], $kode_brng[$count], $kd_bangsal[$count], $no_batch[$count], $no_faktur[$count]]);
          } else {
            $this->db()->pdo()->prepare("INSERT INTO `mlite_farmasi_obat_batch` (kode_brng, kd_bangsal, no_batch, no_faktur, h_beli, stok, created_at, updated_at) VALUES (?,?,?,?,?,?,NOW(),NOW())")
              ->execute([$kode_brng[$count], $kd_bangsal[$count], $no_batch[$count], $no_faktur[$count], $h_beli[$count], $real[$count]]);
          }
          $keluar = '0';
          $masuk = '0';
          if($real[$count]>$stok[$count]) {
          $masuk = $real[$count]-$stok[$count];
          }
          if($real[$count]<$stok[$count]) {
          $keluar = $stok[$count]-$real[$count];
          }
          $this->db('riwayat_barang_medis')
          ->save([
            'kode_brng' => $kode_brng[$count],
            'stok_awal' => $stok[$count],
            'masuk' => $masuk,
            'keluar' => $keluar,
            'stok_akhir' => $real[$count],
            'posisi' => 'Opname',
            'tanggal' => $tanggal[$count],
            'jam' => date('H:i:s'),
            'petugas' => $this->core->getUserInfo('fullname', null, true),
            'kd_bangsal' => $kd_bangsal[$count],
            'status' => 'Simpan',
            'no_batch' => $no_batch[$count],
            'no_faktur' => $no_faktur[$count],
            'keterangan' => $keterangan[$count]
          ]);   
                    
          $data = array(
            'status' => 'success', 
            'msg' => $this->db('databarang')->select('nama_brng')->where('kode_brng', $kode_brng[$count])->oneArray()['nama_brng'], 
            'info' => htmlspecialchars(json_encode($opname2->errorInfo()[2]), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
          );

        } else {
          $data = array(
            'status' => 'error', 
            'msg' => $this->db('databarang')->select('nama_brng')->where('kode_brng', $kode_brng[$count])->oneArray()['nama_brng'], 
            'info' => htmlspecialchars(json_encode($opname2->errorInfo()[2]), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
          );
        }
        echo json_encode(htmlspecialchars_array($data));   

      }
      exit();
    }

    /* Pengajuan Obat & BMHP Section */
    public function getPengajuanobatbmhp()
    {
      $this->_ensureCrudPermission('can_read');
      $this->_addHeaderFiles();

      $stmt = $this->db()->pdo()->prepare("SELECT p.*, d.nama_brng
        FROM mlite_farmasi_pengajuan_obat p
        LEFT JOIN databarang d ON d.kode_brng = p.kode_brng
        ORDER BY p.id DESC");
      $stmt->execute();
      $pengajuan = $stmt->fetchAll(\PDO::FETCH_ASSOC);

      foreach ($pengajuan as &$item) {
        $item['approveURL'] = url([ADMIN, 'farmasi', 'approvepengajuanobatbmhp', $item['id']]);
      }

      return $this->draw('pengajuan.obat.bmhp.html', [
        'mlite_crud_permissions' => htmlspecialchars_array($this->_crudPermissionsFarmasi()),
        'databarang' => htmlspecialchars_array($this->db('databarang')->where('status', '1')->toArray()),
        'pengajuan' => htmlspecialchars_array($pengajuan),
        'action' => url([ADMIN, 'farmasi', 'pengajuanobatbmhpsave']),
        'default_no_pengajuan' => htmlspecialchars($this->_generateDocumentNumber('mlite_farmasi_pengajuan_obat', 'no_pengajuan', 'PGJ'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
      ]);
    }

    public function postPengajuanobatbmhpSave()
    {
      $this->_ensureCrudPermission('can_create');

      $kode_brng = trim($_POST['kode_brng'] ?? '');
      $jumlah = (int)($_POST['jumlah'] ?? 0);

      if ($kode_brng === '' || $jumlah <= 0) {
        $this->notify('failure', 'Kode barang dan jumlah wajib diisi.');
        redirect(url([ADMIN, 'farmasi', 'pengajuanobatbmhp']));
      }

      $no_pengajuan = trim($_POST['no_pengajuan'] ?? '');
      if ($no_pengajuan === '') {
        $no_pengajuan = $this->_generateDocumentNumber('mlite_farmasi_pengajuan_obat', 'no_pengajuan', 'PGJ');
      }

      $query = $this->db('mlite_farmasi_pengajuan_obat')->save([
        'no_pengajuan' => $no_pengajuan,
        'tanggal_pengajuan' => $_POST['tanggal_pengajuan'] ?? date('Y-m-d'),
        'kode_brng' => $kode_brng,
        'jumlah' => $jumlah,
        'status' => 'Menunggu',
        'catatan' => $_POST['catatan'] ?? '',
        'dibuat_oleh' => $this->core->getUserInfo('fullname', null, true) ?? '-',
        'created_at' => date('Y-m-d H:i:s')
      ]);

      if ($query) {
        $this->notify('success', 'Pengajuan berhasil disimpan.');
      } else {
        $this->notify('failure', 'Pengajuan gagal disimpan.');
      }
      redirect(url([ADMIN, 'farmasi', 'pengajuanobatbmhp']));
    }

    public function getApprovepengajuanobatbmhp($id)
    {
      $this->_ensureCrudPermission('can_update');
      $pengajuan = $this->db('mlite_farmasi_pengajuan_obat')->where('id', $id)->oneArray();
      if (!$pengajuan) {
        $this->notify('failure', 'Data pengajuan tidak ditemukan.');
        redirect(url([ADMIN, 'farmasi', 'pengajuanobatbmhp']));
      }

      $query = $this->db('mlite_farmasi_pengajuan_obat')->where('id', $id)->save([
        'status' => 'Disetujui',
        'disetujui_oleh' => $this->core->getUserInfo('fullname', null, true) ?? '-',
        'disetujui_at' => date('Y-m-d H:i:s')
      ]);

      if ($query) {
        $this->notify('success', 'Pengajuan berhasil disetujui.');
      } else {
        $this->notify('failure', 'Pengajuan gagal disetujui.');
      }
      redirect(url([ADMIN, 'farmasi', 'pengajuanobatbmhp']));
    }
    /* End Pengajuan Obat & BMHP Section */

    /* Pemesanan Obat & BMHP Section */
    public function getPemesananobatbmhp()
    {
      $this->_ensureCrudPermission('can_read');
      $this->_addHeaderFiles();

      $stmt = $this->db()->pdo()->prepare("SELECT p.*, d.nama_brng
        FROM mlite_farmasi_pemesanan_obat p
        LEFT JOIN databarang d ON d.kode_brng = p.kode_brng
        ORDER BY p.id DESC");
      $stmt->execute();
      $pemesanan = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      foreach ($pemesanan as &$item) {
        $item['printURL'] = url([ADMIN, 'farmasi', 'cetaksuratpemesananobatbmhp', $item['id']]);
      }

      $stmtPengajuan = $this->db()->pdo()->prepare("SELECT p.id, p.no_pengajuan, p.kode_brng, p.jumlah, d.nama_brng
        FROM mlite_farmasi_pengajuan_obat p
        LEFT JOIN databarang d ON d.kode_brng = p.kode_brng
        WHERE p.status = 'Disetujui'
        ORDER BY p.id DESC");
      $stmtPengajuan->execute();
      $pengajuan = $stmtPengajuan->fetchAll(\PDO::FETCH_ASSOC);

      $stmtSupplier = $this->db()->pdo()->prepare("SELECT kode_industri, nama_industri
        FROM industrifarmasi
        WHERE kode_industri <> '-'
        ORDER BY nama_industri ASC");
      $stmtSupplier->execute();
      $supplier_master = $stmtSupplier->fetchAll(\PDO::FETCH_ASSOC);

      return $this->draw('pemesanan.obat.bmhp.html', [
        'mlite_crud_permissions' => htmlspecialchars_array($this->_crudPermissionsFarmasi()),
        'pemesanan' => htmlspecialchars_array($pemesanan),
        'pengajuan' => htmlspecialchars_array($pengajuan),
        'supplier_master' => htmlspecialchars_array($supplier_master),
        'action' => url([ADMIN, 'farmasi', 'pemesananobatbmhpsave']),
        'default_no_pemesanan' => htmlspecialchars($this->_generateDocumentNumber('mlite_farmasi_pemesanan_obat', 'no_pemesanan', 'PSN'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
      ]);
    }

    public function postPemesananobatbmhpSave()
    {
      $this->_ensureCrudPermission('can_create');

      $pengajuan_id = (int)($_POST['pengajuan_id'] ?? 0);
      $jumlah_pesan = (int)($_POST['jumlah_pesan'] ?? 0);
      $supplier_kode = trim($_POST['supplier_kode'] ?? '');

      $pengajuan = $this->db('mlite_farmasi_pengajuan_obat')->where('id', $pengajuan_id)->oneArray();
      if (!$pengajuan) {
        $this->notify('failure', 'Data pengajuan tidak ditemukan.');
        redirect(url([ADMIN, 'farmasi', 'pemesananobatbmhp']));
      }

      $supplier_master = $this->db('industrifarmasi')->where('kode_industri', $supplier_kode)->oneArray();
      $supplier = trim((string)($supplier_master['nama_industri'] ?? ''));

      if ($jumlah_pesan <= 0 || $supplier_kode === '' || $supplier === '') {
        $this->notify('failure', 'Jumlah pesan dan supplier wajib diisi dari master industri farmasi.');
        redirect(url([ADMIN, 'farmasi', 'pemesananobatbmhp']));
      }

      $no_pemesanan = trim($_POST['no_pemesanan'] ?? '');
      if ($no_pemesanan === '') {
        $no_pemesanan = $this->_generateDocumentNumber('mlite_farmasi_pemesanan_obat', 'no_pemesanan', 'PSN');
      }

      $query = $this->db('mlite_farmasi_pemesanan_obat')->save([
        'no_pemesanan' => $no_pemesanan,
        'no_pengajuan' => $pengajuan['no_pengajuan'],
        'pengajuan_id' => $pengajuan_id,
        'kode_brng' => $pengajuan['kode_brng'],
        'tanggal_pemesanan' => $_POST['tanggal_pemesanan'] ?? date('Y-m-d'),
        'supplier_kode' => $supplier_kode,
        'supplier' => $supplier,
        'jumlah_pengajuan' => (int)$pengajuan['jumlah'],
        'jumlah_pesan' => $jumlah_pesan,
        'status_pemesanan' => 'Dipesan',
        'catatan' => $_POST['catatan'] ?? '',
        'dibuat_oleh' => $this->core->getUserInfo('fullname', null, true) ?? '-',
        'created_at' => date('Y-m-d H:i:s')
      ]);

      if ($query) {
        $this->db('mlite_farmasi_pengajuan_obat')->where('id', $pengajuan_id)->save(['status' => 'Dipesan']);
        $this->notify('success', 'Pemesanan berhasil disimpan.');
      } else {
        $this->notify('failure', 'Pemesanan gagal disimpan.');
      }
      redirect(url([ADMIN, 'farmasi', 'pemesananobatbmhp']));
    }

    public function getCetaksuratpemesananobatbmhp($id)
    {
      $this->_ensureCrudPermission('can_read');

      $stmt = $this->db()->pdo()->prepare("SELECT p.*, d.nama_brng
        FROM mlite_farmasi_pemesanan_obat p
        LEFT JOIN databarang d ON d.kode_brng = p.kode_brng
        WHERE p.id = ?
        LIMIT 1");
      $stmt->execute([$id]);
      $pemesanan = $stmt->fetch(\PDO::FETCH_ASSOC);

      if (!$pemesanan) {
        $this->notify('failure', 'Data pemesanan tidak ditemukan.');
        redirect(url([ADMIN, 'farmasi', 'pemesananobatbmhp']));
      }

      echo $this->draw('cetak.surat.pemesanan.obat.bmhp.html', [
        'settings' => htmlspecialchars_array($this->settings('settings')),
        'pemesanan' => htmlspecialchars_array($pemesanan)
      ]);
      exit();
    }
    /* End Pemesanan Obat & BMHP Section */

    /* Penerimaan Obat & BMHP Section */
    public function getPenerimaanobatbmhp()
    {
      $this->_ensureCrudPermission('can_read');
      $this->_addHeaderFiles();

      $stmtPemesanan = $this->db()->pdo()->prepare("SELECT p.id, p.no_pemesanan, p.kode_brng, d.nama_brng, p.supplier, p.jumlah_pesan,
        COALESCE(SUM(t.jumlah_terima), 0) AS total_terima,
        (p.jumlah_pesan - COALESCE(SUM(t.jumlah_terima), 0)) AS sisa
        FROM mlite_farmasi_pemesanan_obat p
        LEFT JOIN databarang d ON d.kode_brng = p.kode_brng
        LEFT JOIN mlite_farmasi_penerimaan_obat t ON t.pemesanan_id = p.id
        GROUP BY p.id, p.no_pemesanan, p.kode_brng, d.nama_brng, p.supplier, p.jumlah_pesan
        HAVING sisa > 0
        ORDER BY p.id DESC");
      $stmtPemesanan->execute();
      $pemesanan = $stmtPemesanan->fetchAll(\PDO::FETCH_ASSOC);

      $stmt = $this->db()->pdo()->prepare("SELECT t.*, p.no_pemesanan, p.kode_brng, d.nama_brng, p.supplier
        FROM mlite_farmasi_penerimaan_obat t
        LEFT JOIN mlite_farmasi_pemesanan_obat p ON p.id = t.pemesanan_id
        LEFT JOIN databarang d ON d.kode_brng = p.kode_brng
        ORDER BY t.id DESC");
      $stmt->execute();
      $penerimaan = $stmt->fetchAll(\PDO::FETCH_ASSOC);

      return $this->draw('penerimaan.obat.bmhp.html', [
        'mlite_crud_permissions' => htmlspecialchars_array($this->_crudPermissionsFarmasi()),
        'pemesanan' => htmlspecialchars_array($pemesanan),
        'penerimaan' => htmlspecialchars_array($penerimaan),
        'action' => url([ADMIN, 'farmasi', 'penerimaanobatbmhpsave'])
      ]);
    }

    public function postPenerimaanobatbmhpSave()
    {
      $this->_ensureCrudPermission('can_create');

      $pemesanan_id = (int)($_POST['pemesanan_id'] ?? 0);
      $jumlah_terima = (int)($_POST['jumlah_terima'] ?? 0);
      $nomor_faktur = trim($_POST['nomor_faktur'] ?? '');
      $harga_beli = (float)str_replace(',', '.', trim((string)($_POST['harga_beli'] ?? '0')));
      $no_batch = trim($_POST['no_batch'] ?? '');
      $expire = trim($_POST['expire'] ?? '');

      if ($pemesanan_id <= 0 || $jumlah_terima <= 0) {
        $this->notify('failure', 'Pemesanan dan jumlah terima wajib diisi.');
        redirect(url([ADMIN, 'farmasi', 'penerimaanobatbmhp']));
      }

      $pemesanan = $this->db('mlite_farmasi_pemesanan_obat')->where('id', $pemesanan_id)->oneArray();
      if (!$pemesanan) {
        $this->notify('failure', 'Data pemesanan tidak ditemukan.');
        redirect(url([ADMIN, 'farmasi', 'penerimaanobatbmhp']));
      }

      $stmt = $this->db()->pdo()->prepare("SELECT COALESCE(SUM(jumlah_terima), 0) AS total_terima
        FROM mlite_farmasi_penerimaan_obat
        WHERE pemesanan_id = ?");
      $stmt->execute([$pemesanan_id]);
      $total_terima = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['total_terima'] ?? 0);
      $sisa = (int)$pemesanan['jumlah_pesan'] - $total_terima;

      if ($jumlah_terima > $sisa) {
        $this->notify('failure', 'Jumlah terima melebihi sisa pemesanan.');
        redirect(url([ADMIN, 'farmasi', 'penerimaanobatbmhp']));
      }

      $pdo = $this->db()->pdo();
      $transaksi_penerimaan = !$pdo->inTransaction();
      if ($transaksi_penerimaan) {
        $pdo->beginTransaction();
      }
      try {
        $saved = $this->db('mlite_farmasi_penerimaan_obat')->save([
          'pemesanan_id' => $pemesanan_id,
          'tanggal_penerimaan' => $_POST['tanggal_penerimaan'] ?? date('Y-m-d'),
          'jumlah_terima' => $jumlah_terima,
          'harga_beli' => $harga_beli,
          'no_batch' => $no_batch !== '' ? $no_batch : '0',
          'expire' => $expire ?: null,
          'jenis_pembayaran' => $_POST['jenis_pembayaran'] ?? 'Cash',
          'tanggal_jatuh_tempo' => $_POST['tanggal_jatuh_tempo'] ?: null,
          'nomor_faktur' => $nomor_faktur ?: null,
          'catatan' => $_POST['catatan'] ?? '',
          'dibuat_oleh' => $this->core->getUserInfo('fullname', null, true) ?? '-',
          'created_at' => date('Y-m-d H:i:s')
        ]);

        if (!$saved) {
          throw new \Exception('Gagal menyimpan penerimaan.');
        }

        $kd_gudang = $this->settings->get('farmasi.gudang');
        $gudangbarang = $this->db('gudangbarang')
          ->where('kode_brng', $pemesanan['kode_brng'])
          ->where('kd_bangsal', $kd_gudang)
          ->oneArray();

        $stok_awal = (int)($gudangbarang['stok'] ?? 0);
        $stok_akhir = $stok_awal + $jumlah_terima;

        $this->db('riwayat_barang_medis')->save([
          'kode_brng' => $pemesanan['kode_brng'],
          'stok_awal' => $stok_awal,
          'masuk' => $jumlah_terima,
          'keluar' => '0',
          'stok_akhir' => $stok_akhir,
          'posisi' => 'Penerimaan',
          'tanggal' => $_POST['tanggal_penerimaan'] ?? date('Y-m-d'),
          'jam' => date('H:i:s'),
          'petugas' => $this->core->getUserInfo('fullname', null, true),
          'kd_bangsal' => $kd_gudang,
          'status' => 'Simpan',
          'no_batch' => $no_batch !== '' ? $no_batch : '0',
          'no_faktur' => $nomor_faktur ?: '0',
          'keterangan' => 'Penerimaan dari pemesanan '.$pemesanan['no_pemesanan']
        ]);

        // Catat stok per batch (dengan harga beli & kedaluwarsa batch ini)
        \Systems\PharmacyBatches::credit(
          $this->db()->pdo(),
          $pemesanan['kode_brng'],
          $kd_gudang,
          $no_batch !== '' ? $no_batch : '0',
          $nomor_faktur ?: '0',
          $jumlah_terima,
          $harga_beli,
          $expire ?: null
        );

        if ($gudangbarang) {
          $this->db('gudangbarang')
            ->where('kode_brng', $pemesanan['kode_brng'])
            ->where('kd_bangsal', $kd_gudang)
            ->save([
              'stok' => $stok_akhir,
              'no_batch' => $no_batch !== '' ? $no_batch : '0',
              'no_faktur' => $nomor_faktur ?: '0'
            ]);
        } else {
          $this->db('gudangbarang')->save([
            'kode_brng' => $pemesanan['kode_brng'],
            'kd_bangsal' => $kd_gudang,
            'stok' => $stok_akhir,
            'no_batch' => $no_batch !== '' ? $no_batch : '0',
            'no_faktur' => $nomor_faktur ?: '0'
          ]);
        }

        $databarang = $this->db('databarang')->where('kode_brng', $pemesanan['kode_brng'])->oneArray();
        $harga_mutasi = $harga_beli > 0 ? $harga_beli : (float)($databarang['dasar'] ?? 0);
        if (!$databarang || !isset($databarang['dasar'])) {
          throw new \Exception('Harga dasar barang tidak ditemukan untuk mutasi penerimaan.');
        }
        $this->db('mutasibarang')->save([
          'kode_brng' => $pemesanan['kode_brng'],
          'jml' => $jumlah_terima,
          'harga' => $harga_mutasi,
          'kd_bangsaldari' => $kd_gudang,
          'kd_bangsalke' => $kd_gudang,
          'tanggal' => date('Y-m-d H:i:s'),
          'keterangan' => 'Penerimaan dari pemesanan '.$pemesanan['no_pemesanan'],
          'no_batch' => $no_batch !== '' ? $no_batch : '0',
          'no_faktur' => $nomor_faktur ?: '0'
        ]);

        $total_akhir = $total_terima + $jumlah_terima;
        $status_pemesanan = ($total_akhir >= (int)$pemesanan['jumlah_pesan']) ? 'Diterima' : 'Parsial';
        $this->db('mlite_farmasi_pemesanan_obat')->where('id', $pemesanan_id)->save([
          'status_pemesanan' => $status_pemesanan
        ]);

        if ($transaksi_penerimaan && $pdo->inTransaction()) {
          $pdo->commit();
        }
        $this->notify('success', 'Penerimaan berhasil disimpan dan stok diperbarui.');
      } catch (\Throwable $e) {
        if ($transaksi_penerimaan && $pdo->inTransaction()) {
          $pdo->rollBack();
        }
        error_log('Penerimaan obat (farmasi, pemesanan_id=' . (isset($_POST['pemesanan_id']) ? $_POST['pemesanan_id'] : '-') . ') gagal: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
        $this->notify('failure', 'Penerimaan gagal disimpan: '.$e->getMessage());
      }
      redirect(url([ADMIN, 'farmasi', 'penerimaanobatbmhp']));
    }
    /* End Penerimaan Obat & BMHP Section */

    /* Settings Farmasi Section */
    public function getSettings()
    {
        if ($this->core->getUserInfo('role') != 'admin') {
            $this->notify('failure', 'Anda tidak memiliki hak akses untuk halaman ini.');
            redirect(url([ADMIN, 'farmasi', 'mutasi']));
        }
        $this->assign['title'] = 'Pengaturan Modul Farmasi';
        $this->assign['bangsal'] = $this->db('bangsal')->toArray();
        $this->assign['farmasi'] = htmlspecialchars_array($this->settings('farmasi'));
        return $this->draw('settings.html', ['settings' => htmlspecialchars_array($this->assign)]);
    }

    public function postSaveSettings()
    {
        if ($this->core->getUserInfo('role') != 'admin') {
            $this->notify('failure', 'Anda tidak memiliki hak akses untuk halaman ini.');
            redirect(url([ADMIN, 'farmasi', 'mutasi']));
        }

        if (isset($_POST['farmasi']['pajak_obat_persen'])) {
            $taxPercent = str_replace(',', '.', trim((string) $_POST['farmasi']['pajak_obat_persen']));
            $taxPercent = is_numeric($taxPercent) ? (float) $taxPercent : 0;
            $taxPercent = max(0, min(100, $taxPercent));
            $_POST['farmasi']['pajak_obat_persen'] = rtrim(rtrim(number_format($taxPercent, 2, '.', ''), '0'), '.');
        }

        foreach ($_POST['farmasi'] as $key => $val) {
            $this->settings('farmasi', $key, $val);
        }
        $this->notify('success', 'Pengaturan telah disimpan');
        redirect(url([ADMIN, 'farmasi', 'settings']));
    }
    /* End Settings Farmasi Section */

    /* Detail Pemberian Obat Section */
    public function getDetailpemberianobat()
    {
        $this->_addHeaderFiles();
        $this->core->addJS(url([ADMIN, 'farmasi', 'detailpemberianobatjs']), 'footer');
        return $this->draw('detailpemberianobat.html');
    }

    public function postDetailpemberianobatData()
    {
        $draw = $_POST['draw'];
        $row1 = $_POST['start'];
        $rowperpage = $_POST['length']; // Rows display per page
        $columnIndex = $_POST['order'][0]['column']; // Column index
        $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
        $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
        $searchValue = $_POST['search']['value']; // Search value

        ## Custom Field value
        $search_field_detail_pemberian_obat= $_POST['search_field_detail_pemberian_obat'];
        $search_text_detail_pemberian_obat = $_POST['search_text_detail_pemberian_obat'];

        $allowed_fields = ['no_rawat', 'kode_brng', 'tgl_perawatan', 'jam', 'kd_bangsal', 'no_batch', 'no_faktur'];
        if (!in_array($search_field_detail_pemberian_obat, $allowed_fields)) {
            $search_field_detail_pemberian_obat = 'no_rawat';
        }

        $allowed_sort = ['asc', 'desc'];
        if (!in_array(strtolower($columnSortOrder), $allowed_sort)) {
            $columnSortOrder = 'asc';
        }
        if (!in_array($columnName, $allowed_fields)) {
            $columnName = 'no_rawat';
        }

        $tgl_awal = isset_or($_POST['tgl_awal'], date('Y-m-d'));
        $tgl_akhir = isset_or($_POST['tgl_akhir'], date('Y-m-d'));
        $no_batch_filter = isset_or($_POST['no_batch_filter'], '');
        $expire_filter = isset_or($_POST['expire_filter'], 'all');
        if (!in_array($expire_filter, ['all', 'expired', 'soon', 'safe'])) {
            $expire_filter = 'all';
        }

        $hari90 = date('Y-m-d', strtotime('+90 day'));

        $searchQuery = " ";
        $params = [];
        if($search_text_detail_pemberian_obat != ''){
            $searchQuery .= " and (".$search_field_detail_pemberian_obat." like ? ) ";
            $params[] = "%".$search_text_detail_pemberian_obat."%";
        }

        $searchQuery .= " and (tgl_perawatan between ? and ?) ";
        $params[] = $tgl_awal;
        $params[] = $tgl_akhir;

        // Filter no. batch (pemberian)
        if (trim($no_batch_filter) !== '') {
            $searchQuery .= " and (detail_pemberian_obat.no_batch like ?) ";
            $params[] = "%" . $no_batch_filter . "%";
        }

        // Filter kedaluwarsa batch (expire diambil dari ledger batch)
        $sell_join = " FROM detail_pemberian_obat
            LEFT JOIN mlite_farmasi_obat_batch bb
                ON bb.kode_brng = detail_pemberian_obat.kode_brng
               AND bb.kd_bangsal = detail_pemberian_obat.kd_bangsal
               AND bb.no_batch = detail_pemberian_obat.no_batch
               AND bb.no_faktur = detail_pemberian_obat.no_faktur ";
        if ($expire_filter === 'expired') {
            $searchQuery .= " and (bb.expire IS NOT NULL AND bb.expire < ?) ";
            $params[] = date('Y-m-d');
        } elseif ($expire_filter === 'soon') {
            $searchQuery .= " and (bb.expire IS NOT NULL AND bb.expire >= ? AND bb.expire <= ?) ";
            $params[] = date('Y-m-d');
            $params[] = $hari90;
        } elseif ($expire_filter === 'safe') {
            $searchQuery .= " and (bb.expire IS NULL OR bb.expire > ?) ";
            $params[] = $hari90;
        }

        ## Total number of records without filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from detail_pemberian_obat");
        $sel->execute();
        $records = $sel->fetch();
        $totalRecords = $records['allcount'];

        ## Total number of records with filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount " . $sell_join . " WHERE 1 " . $searchQuery);
        $sel->execute($params);
        $records = $sel->fetch();
        $totalRecordwithFilter = $records['allcount'];

        ## Fetch records
        $sel = $this->db()->pdo()->prepare("select detail_pemberian_obat.*, bb.expire as expire " . $sell_join . " WHERE 1 " . $searchQuery . " order by " . $columnName . " " . $columnSortOrder . " limit " . (int)$row1 . "," . (int)$rowperpage);
        $sel->execute($params);
        $result = $sel->fetchAll(\PDO::FETCH_ASSOC);

        $data = array();
        foreach($result as $row) {
            $databarang = $this->db('databarang')->select('nama_brng')->where('kode_brng', $row['kode_brng'])->oneArray();
            $bangsal = $this->db('bangsal')->select('nm_bangsal')->where('kd_bangsal', $row['kd_bangsal'])->oneArray();
            $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis', $row['no_rawat']);
            $data[] = array(
                'tgl_perawatan'=>$row['tgl_perawatan'],
                'jam'=>$row['jam'],
                'no_rkm_medis' => $no_rkm_medis,
                'nm_pasien' => $this->core->getPasienInfo('nm_pasien', $no_rkm_medis),
                'no_rawat'=>$row['no_rawat'],
                'kode_brng'=>$row['kode_brng'],
                'nama_brng'=>$databarang['nama_brng'],
                'h_beli'=>$row['h_beli'],
                'biaya_obat'=>$row['biaya_obat'],
                'jml'=>$row['jml'],
                'embalase'=>$row['embalase'],
                'tuslah'=>$row['tuslah'],
                'total'=>$row['total'],
                'status'=>$row['status'],
                'kd_bangsal'=>$row['kd_bangsal'],
                'nm_bangsal'=>$bangsal['nm_bangsal'],
                'no_batch'=>$row['no_batch'],
                'no_faktur'=>$row['no_faktur'],
                'expire'=>$row['expire'] ?? ''
            );
        }

        ## Response
        $response = array(
            "draw" => intval(htmlspecialchars($_POST['draw'] ?? 0, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordwithFilter,
            "aaData" => $data
        );

        echo json_encode(htmlspecialchars_array($response));
        exit();
    }

    public function getDetailpemberianobatJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/farmasi/js/admin/detailpemberianobat.js');
        exit();
    }
    /* End Detail Pemberian Obat Section */

    /* Riwayat Barang Medis Section */
    public function getRiwayatbarangmedis()
    {
        $this->_addHeaderFiles();
        $this->core->addJS(url([ADMIN, 'farmasi', 'riwayatbarangmedisjs']), 'footer');
        return $this->draw('riwayatbarangmedis.html');
    }

    public function postRiwayatbarangmedisData()
    {
        $draw = $_POST['draw'];
        $row1 = $_POST['start'];
        $rowperpage = $_POST['length']; // Rows display per page
        $columnIndex = $_POST['order'][0]['column']; // Column index
        $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
        $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
        $searchValue = $_POST['search']['value']; // Search value

        ## Custom Field value
        $search_field_riwayat_barang_medis= $_POST['search_field_riwayat_barang_medis'];
        $search_text_riwayat_barang_medis = $_POST['search_text_riwayat_barang_medis'];

        $allowed_fields = ['kode_brng', 'stok_awal', 'masuk', 'keluar', 'stok_akhir', 'posisi', 'tanggal', 'jam', 'petugas', 'kd_bangsal', 'status', 'no_batch', 'no_faktur', 'keterangan'];
        if (!in_array($search_field_riwayat_barang_medis, $allowed_fields)) {
            $search_field_riwayat_barang_medis = 'kode_brng';
        }

        $allowed_sort = ['asc', 'desc'];
        if (!in_array(strtolower($columnSortOrder), $allowed_sort)) {
            $columnSortOrder = 'asc';
        }
        if (!in_array($columnName, $allowed_fields)) {
            $columnName = 'kode_brng';
        }

        $tgl_awal = isset_or($_POST['tgl_awal'], date('Y-m-d'));
        $tgl_akhir = isset_or($_POST['tgl_akhir'], date('Y-m-d'));

        $searchQuery = " ";
        $params = [];
        if($search_text_riwayat_barang_medis != ''){
            $searchQuery .= " and (".$search_field_riwayat_barang_medis." like ? ) ";
            $params[] = "%".$search_text_riwayat_barang_medis."%";
        }

        $searchQuery .= " and (tanggal between ? and ?) ";
        $params[] = $tgl_awal;
        $params[] = $tgl_akhir;

        ## Total number of records without filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from riwayat_barang_medis");
        $sel->execute();
        $records = $sel->fetch();
        $totalRecords = $records['allcount'];

        ## Total number of records with filtering
        $sel = $this->db()->pdo()->prepare("select count(*) as allcount from riwayat_barang_medis WHERE 1 ".$searchQuery);
        $sel->execute($params);
        $records = $sel->fetch();
        $totalRecordwithFilter = $records['allcount'];

        ## Fetch records
        $sel = $this->db()->pdo()->prepare("select * from riwayat_barang_medis WHERE 1 ".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".(int)$row1.",".(int)$rowperpage);
        $sel->execute($params);
        $result = $sel->fetchAll(\PDO::FETCH_ASSOC);

        $data = array();
        foreach($result as $row) {
            $databarang = $this->db('databarang')->select('nama_brng')->where('kode_brng', $row['kode_brng'])->oneArray();
            $bangsal = $this->db('bangsal')->select('nm_bangsal')->where('kd_bangsal', $row['kd_bangsal'])->oneArray();
            $data[] = array(
                'kode_brng'=>$row['kode_brng'],
                'nama_brng'=>$databarang['nama_brng'],
                'stok_awal'=>$row['stok_awal'],
                'masuk'=>$row['masuk'],
                'keluar'=>$row['keluar'],
                'stok_akhir'=>$row['stok_akhir'],
                'posisi'=>$row['posisi'],
                'tanggal'=>$row['tanggal'],
                'jam'=>$row['jam'],
                'petugas'=>$row['petugas'],
                'kd_bangsal'=>$row['kd_bangsal'],
                'nm_bangsal'=>$bangsal['nm_bangsal'],
                'status'=>$row['status'],
                'no_batch'=>$row['no_batch'],
                'no_faktur'=>$row['no_faktur'],
                'keterangan'=>$row['keterangan']
            );
        }

        ## Response
        $response = array(
            "draw" => intval(htmlspecialchars($_POST['draw'] ?? 0, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordwithFilter,
            "aaData" => $data
        );

        echo json_encode(htmlspecialchars_array($response));
        exit();
    }

    public function getRiwayatbarangmedisJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/farmasi/js/admin/riwayatbarangmedis.js');
        exit();
    }
    /* End Riwayat Barang Medis Section */

    /* Darurat Stok Section */
    public function getDaruratStok()
    {
        $this->_addHeaderFiles();
        $this->core->addJS(url([ADMIN, 'farmasi', 'daruratstokjs']), 'footer');
        return $this->draw('daruratstok.html');
    }

    public function postDaruratStokData()
    {
        try {
            $draw           = $_POST['draw'] ?? 1;
            $row1           = $_POST['start'] ?? 0;
            $rowperpage     = $_POST['length'] ?? 10;
            $columnIndex    = $_POST['order'][0]['column'] ?? 0;
            $columnName     = $_POST['columns'][$columnIndex]['data'] ?? 'kode_brng';
            $columnSortOrder= $_POST['order'][0]['dir'] ?? 'asc';
            $search_text    = $_POST['search_text_databarang'] ?? '';
            $search_field   = $_POST['search_field_databarang'] ?? '';

            // Validasi: mencegah SQL Injection via column name
            $allowedColumns = [
                'kode_brng','nama_brng','stokminimal','kode_satbesar',
                'kode_sat','dasar','h_beli','isi','kapasitas','expire'
            ];

            if (!in_array($columnName, $allowedColumns)) {
                $columnName = 'kode_brng';
            }

            // Build search query
            $searchQuery = "";
            $params = [];

            if ($search_text !== '' && in_array($search_field, $allowedColumns)) {
                $searchQuery = " AND d.$search_field LIKE :search_text ";
                $params[':search_text'] = "%$search_text%";
            }

            // -------------------------
            // Hitung total records
            // -------------------------
            $sqlTotal = "SELECT COUNT(*) AS allcount FROM databarang";
            $stmt = $this->db()->pdo()->prepare($sqlTotal);
            $stmt->execute();
            $totalRecords = $stmt->fetch()['allcount'];

            // -------------------------
            // Hitung total filtered
            // -------------------------
            $sqlFiltered = "SELECT COUNT(*) AS allcount FROM databarang d WHERE 1 $searchQuery";
            $stmt = $this->db()->pdo()->prepare($sqlFiltered);
            if(!empty($params)){
                foreach ($params as $key => $value) {
                    $stmt->bindValue($key, $value);
                }
            }
            $stmt->execute();
            $totalRecordwithFilter = $stmt->fetch()['allcount'];

            // -------------------------
            // Ambil data JOIN stok gudang (1 kali query saja)
            // -------------------------
            // Use LIMIT length OFFSET start for better compatibility
            $sqlData = "
                SELECT d.kode_brng, d.nama_brng, d.stokminimal, d.kode_satbesar,
                      d.kode_sat, d.dasar, d.h_beli, d.isi, d.kapasitas, d.expire,
                      COALESCE(SUM(g.stok), 0) AS stok
                FROM databarang d
                LEFT JOIN gudangbarang g ON g.kode_brng = d.kode_brng
                WHERE 1 $searchQuery
                GROUP BY d.kode_brng
                ORDER BY d.$columnName $columnSortOrder
                LIMIT :length OFFSET :start
            ";

            $stmt = $this->db()->pdo()->prepare($sqlData);

            // bind parameter
            if(!empty($params)){
                foreach ($params as $key => $value) {
                    $stmt->bindValue($key, $value);
                }
            }

            $stmt->bindValue(":start", intval($row1), \PDO::PARAM_INT);
            $stmt->bindValue(":length", intval($rowperpage), \PDO::PARAM_INT);

            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $data = [];
            foreach ($result as $row) {
                $data[] = $row;
            }

            // -------------------------
            // Response JSON
            // -------------------------
            $response = [
                "draw" => intval(htmlspecialchars($draw ?? $_POST['draw'] ?? 1, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')),
                "recordsTotal" => intval($totalRecords),
                "recordsFiltered" => intval($totalRecordwithFilter),
                "data" => $data
            ];
        } catch (\Exception $e) {
            $response = [
                "draw" => intval(htmlspecialchars($_POST['draw'] ?? 1, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
                "error" => htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            ];
        }

        header('Content-Type: application/json');
        echo json_encode(htmlspecialchars_array($response));
        exit();
    }

    public function getDaruratStokJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/farmasi/js/admin/daruratstok.js');
        // echo $this->draw(MODULES.'/farmasi/js/admin/farmasi.js');
        exit();
    }
    /* End Darurat Stok Section */

    public function getCSS()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/farmasi/css/admin/farmasi.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/farmasi/js/admin/farmasi.js');
        exit();
    }

    /* API Farmasi Section */

    private function _apiAuth($method = 'GET', $action = 'can_read')
    {
        $username = $this->core->checkAuth($method);
        if (!$this->core->checkPermission($username, $action, 'farmasi')) {
            return false;
        }
        return $username;
    }

    private function _apiInput()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            $input = $_POST;
        }
        return $input;
    }

    public function apiStokList()
    {
        if (!$this->_apiAuth('GET')) {
            return ['status' => 'error', 'message' => 'You do not have permission to access this resource'];
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $page = max(1, $page);
        $per_page = min(100, max(1, $per_page));
        $offset = ($page - 1) * $per_page;
        $search = isset($_GET['s']) ? trim($_GET['s']) : '';
        $kd_bangsal = isset($_GET['kd_bangsal']) ? trim($_GET['kd_bangsal']) : '';
        $status = isset($_GET['status']) ? ($_GET['status'] === '0' ? '0' : '1') : '1';

        $where = " WHERE 1";
        $params = [];
        $where .= " AND d.status = ?";
        $params[] = $status;
        if ($kd_bangsal !== '') {
            $where .= " AND g.kd_bangsal = ?";
            $params[] = $kd_bangsal;
        }
        if ($search !== '') {
            $where .= " AND (d.kode_brng LIKE ? OR d.nama_brng LIKE ? OR g.nm_bangsal LIKE ?)";
            $params[] = '%'.$search.'%';
            $params[] = '%'.$search.'%';
            $params[] = '%'.$search.'%';
        }

        $from = " FROM gudangbarang g
            LEFT JOIN databarang d ON d.kode_brng = g.kode_brng
            LEFT JOIN bangsal b ON b.kd_bangsal = g.kd_bangsal".$where;

        $stmt = $this->db()->pdo()->prepare("SELECT COUNT(*) AS c".$from);
        $stmt->execute($params);
        $total = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['c'] ?? 0);

        $stmt = $this->db()->pdo()->prepare("SELECT g.kode_brng, d.nama_brng, d.kode_sat, d.kode_satbesar, d.dasar, d.stokminimal, d.status,
            g.kd_bangsal, b.nm_bangsal, g.stok, g.no_batch, g.no_faktur".$from.
            " ORDER BY d.nama_brng ASC LIMIT ".(int)$per_page." OFFSET ".(int)$offset);
        $stmt->execute($params);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'status' => 'success',
            'data' => htmlspecialchars_array($data),
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page
        ];
    }

    public function apiBatchList()
    {
        if (!$this->_apiAuth('GET')) {
            return ['status' => 'error', 'message' => 'You do not have permission to access this resource'];
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $page = max(1, $page);
        $per_page = min(100, max(1, $per_page));
        $offset = ($page - 1) * $per_page;
        $search = isset($_GET['s']) ? trim($_GET['s']) : '';
        $kode_brng = isset($_GET['kode_brng']) ? trim($_GET['kode_brng']) : '';
        $kd_bangsal = isset($_GET['kd_bangsal']) ? trim($_GET['kd_bangsal']) : '';
        $expire = isset($_GET['expire']) ? trim($_GET['expire']) : 'all';
        if (!in_array($expire, ['all', 'expired', 'soon', 'safe'])) {
            $expire = 'all';
        }

        $hari90 = date('Y-m-d', strtotime('+90 day'));
        $where = " WHERE b.stok <> 0";
        $params = [];
        if ($kode_brng !== '') {
            $where .= " AND b.kode_brng = ?";
            $params[] = $kode_brng;
        }
        if ($kd_bangsal !== '') {
            $where .= " AND b.kd_bangsal = ?";
            $params[] = $kd_bangsal;
        }
        if ($search !== '') {
            $where .= " AND (b.kode_brng LIKE ? OR b.no_batch LIKE ? OR d.nama_brng LIKE ?)";
            $params[] = '%'.$search.'%';
            $params[] = '%'.$search.'%';
            $params[] = '%'.$search.'%';
        }
        if ($expire === 'expired') {
            $where .= " AND b.expire IS NOT NULL AND b.expire < ?";
            $params[] = date('Y-m-d');
        } elseif ($expire === 'soon') {
            $where .= " AND b.expire IS NOT NULL AND b.expire >= ? AND b.expire <= ?";
            $params[] = date('Y-m-d');
            $params[] = $hari90;
        } elseif ($expire === 'safe') {
            $where .= " AND (b.expire IS NULL OR b.expire > ?)";
            $params[] = $hari90;
        }

        $from = " FROM mlite_farmasi_obat_batch b
            LEFT JOIN databarang d ON d.kode_brng = b.kode_brng
            LEFT JOIN bangsal g ON g.kd_bangsal = b.kd_bangsal".$where;

        $stmt = $this->db()->pdo()->prepare("SELECT COUNT(*) AS c".$from);
        $stmt->execute($params);
        $total = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['c'] ?? 0);

        $stmt = $this->db()->pdo()->prepare("SELECT b.*, d.nama_brng, d.kode_sat, g.nm_bangsal".$from.
            " ORDER BY (b.expire IS NULL) ASC, b.expire ASC, b.kode_brng ASC LIMIT ".(int)$per_page." OFFSET ".(int)$offset);
        $stmt->execute($params);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($data as &$row) {
            if (!empty($row['expire'])) {
                $selisih = (int)floor((strtotime((string)$row['expire']) - strtotime(date('Y-m-d'))) / 86400);
                $row['selisih_hari'] = $selisih;
                $row['status_expire'] = $selisih < 0 ? 'Kedaluwarsa' : ($selisih <= 90 ? 'Segera Kedaluwarsa' : 'Aman');
            } else {
                $row['selisih_hari'] = null;
                $row['status_expire'] = 'Aman';
            }
        }
        unset($row);

        return [
            'status' => 'success',
            'data' => htmlspecialchars_array($data),
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page
        ];
    }

    public function apiPenerimaanList()
    {
        if (!$this->_apiAuth('GET')) {
            return ['status' => 'error', 'message' => 'You do not have permission to access this resource'];
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $page = max(1, $page);
        $per_page = min(100, max(1, $per_page));
        $offset = ($page - 1) * $per_page;
        $search = isset($_GET['s']) ? trim($_GET['s']) : '';
        $tgl_awal = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-01');
        $tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');
        $no_batch = isset($_GET['no_batch']) ? trim($_GET['no_batch']) : '';

        $where = " WHERE t.tanggal_penerimaan BETWEEN ? AND ?";
        $params = [$tgl_awal, $tgl_akhir];
        if ($search !== '') {
            $where .= " AND (p.no_pemesanan LIKE ? OR t.nomor_faktur LIKE ? OR p.kode_brng LIKE ? OR d.nama_brng LIKE ?)";
            $params[] = '%'.$search.'%';
            $params[] = '%'.$search.'%';
            $params[] = '%'.$search.'%';
            $params[] = '%'.$search.'%';
        }
        if ($no_batch !== '') {
            $where .= " AND t.no_batch LIKE ?";
            $params[] = '%'.$no_batch.'%';
        }

        $from = " FROM mlite_farmasi_penerimaan_obat t
            LEFT JOIN mlite_farmasi_pemesanan_obat p ON p.id = t.pemesanan_id
            LEFT JOIN databarang d ON d.kode_brng = p.kode_brng".$where;

        $stmt = $this->db()->pdo()->prepare("SELECT COUNT(*) AS c".$from);
        $stmt->execute($params);
        $total = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['c'] ?? 0);

        $stmt = $this->db()->pdo()->prepare("SELECT t.*, p.no_pemesanan, p.kode_brng, d.nama_brng, d.kode_sat, p.supplier".$from.
            " ORDER BY t.tanggal_penerimaan DESC, t.id DESC LIMIT ".(int)$per_page." OFFSET ".(int)$offset);
        $stmt->execute($params);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'status' => 'success',
            'data' => htmlspecialchars_array($data),
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page
        ];
    }

    public function apiPemberianList()
    {
        if (!$this->_apiAuth('GET')) {
            return ['status' => 'error', 'message' => 'You do not have permission to access this resource'];
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $page = max(1, $page);
        $per_page = min(100, max(1, $per_page));
        $offset = ($page - 1) * $per_page;
        $search = isset($_GET['s']) ? trim($_GET['s']) : '';
        $tgl_awal = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-d');
        $tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');
        $no_batch = isset($_GET['no_batch']) ? trim($_GET['no_batch']) : '';
        $kode_brng = isset($_GET['kode_brng']) ? trim($_GET['kode_brng']) : '';
        $expire = isset($_GET['expire']) ? trim($_GET['expire']) : 'all';
        if (!in_array($expire, ['all', 'expired', 'soon', 'safe'])) {
            $expire = 'all';
        }

        $hari90 = date('Y-m-d', strtotime('+90 day'));
        $where = " WHERE p.tgl_perawatan BETWEEN ? AND ?";
        $params = [$tgl_awal, $tgl_akhir];
        if ($search !== '') {
            $where .= " AND (p.no_rawat LIKE ? OR p.kode_brng LIKE ? OR d.nama_brng LIKE ?)";
            $params[] = '%'.$search.'%';
            $params[] = '%'.$search.'%';
            $params[] = '%'.$search.'%';
        }
        if ($kode_brng !== '') {
            $where .= " AND p.kode_brng = ?";
            $params[] = $kode_brng;
        }
        if ($no_batch !== '') {
            $where .= " AND p.no_batch LIKE ?";
            $params[] = '%'.$no_batch.'%';
        }
        if ($expire === 'expired') {
            $where .= " AND bb.expire IS NOT NULL AND bb.expire < ?";
            $params[] = date('Y-m-d');
        } elseif ($expire === 'soon') {
            $where .= " AND bb.expire IS NOT NULL AND bb.expire >= ? AND bb.expire <= ?";
            $params[] = date('Y-m-d');
            $params[] = $hari90;
        } elseif ($expire === 'safe') {
            $where .= " AND (bb.expire IS NULL OR bb.expire > ?)";
            $params[] = $hari90;
        }

        $from = " FROM detail_pemberian_obat p
            LEFT JOIN databarang d ON d.kode_brng = p.kode_brng
            LEFT JOIN bangsal g ON g.kd_bangsal = p.kd_bangsal
            LEFT JOIN mlite_farmasi_obat_batch bb
                ON bb.kode_brng = p.kode_brng AND bb.kd_bangsal = p.kd_bangsal
               AND bb.no_batch = p.no_batch AND bb.no_faktur = p.no_faktur
            LEFT JOIN reg_periksa r ON r.no_rawat = p.no_rawat
            LEFT JOIN pasien ps ON ps.no_rkm_medis = r.no_rkm_medis".$where;

        $stmt = $this->db()->pdo()->prepare("SELECT COUNT(*) AS c".$from);
        $stmt->execute($params);
        $total = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['c'] ?? 0);

        $stmt = $this->db()->pdo()->prepare("SELECT p.*, d.nama_brng, d.kode_sat,
            g.nm_bangsal, bb.expire, r.no_rkm_medis, ps.nm_pasien".$from.
            " ORDER BY p.tgl_perawatan DESC, p.jam DESC LIMIT ".(int)$per_page." OFFSET ".(int)$offset);
        $stmt->execute($params);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'status' => 'success',
            'data' => htmlspecialchars_array($data),
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page
        ];
    }

    public function apiPengajuanList()
    {
        if (!$this->_apiAuth('GET')) {
            return ['status' => 'error', 'message' => 'You do not have permission to access this resource'];
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $page = max(1, $page);
        $per_page = min(100, max(1, $per_page));
        $offset = ($page - 1) * $per_page;
        $search = isset($_GET['s']) ? trim($_GET['s']) : '';
        $status = isset($_GET['status']) ? trim($_GET['status']) : 'all';

        $where = " WHERE 1";
        $params = [];
        if (in_array($status, ['Menunggu', 'Disetujui', 'Dipesan'])) {
            $where .= " AND q.status = ?";
            $params[] = $status;
        }
        if ($search !== '') {
            $where .= " AND (q.no_pengajuan LIKE ? OR q.kode_brng LIKE ? OR d.nama_brng LIKE ?)";
            $params[] = '%'.$search.'%';
            $params[] = '%'.$search.'%';
            $params[] = '%'.$search.'%';
        }

        $from = " FROM mlite_farmasi_pengajuan_obat q
            LEFT JOIN databarang d ON d.kode_brng = q.kode_brng".$where;

        $stmt = $this->db()->pdo()->prepare("SELECT COUNT(*) AS c".$from);
        $stmt->execute($params);
        $total = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['c'] ?? 0);

        $stmt = $this->db()->pdo()->prepare("SELECT q.*, d.nama_brng, d.kode_sat".$from.
            " ORDER BY q.id DESC LIMIT ".(int)$per_page." OFFSET ".(int)$offset);
        $stmt->execute($params);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'status' => 'success',
            'data' => htmlspecialchars_array($data),
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page
        ];
    }

    public function apiPengajuanSimpan()
    {
        if (!$this->_apiAuth('POST', 'can_create')) {
            return ['status' => 'error', 'message' => 'You do not have permission to access this resource'];
        }

        $input = $this->_apiInput();
        $kode_brng = trim((string)($input['kode_brng'] ?? ''));
        $jumlah = (int)($input['jumlah'] ?? 0);

        if ($kode_brng === '' || $jumlah <= 0) {
            return ['status' => 'error', 'message' => 'Kode barang dan jumlah wajib diisi.'];
        }

        $barang = $this->db('databarang')->where('kode_brng', $kode_brng)->oneArray();
        if (!$barang || $barang['status'] !== '1') {
            return ['status' => 'error', 'message' => 'Barang tidak ditemukan atau tidak aktif.'];
        }

        $no_pengajuan = $this->_generateDocumentNumber('mlite_farmasi_pengajuan_obat', 'no_pengajuan', 'PGJ');
        $saved = $this->db('mlite_farmasi_pengajuan_obat')->save([
            'no_pengajuan' => $no_pengajuan,
            'tanggal_pengajuan' => $input['tanggal_pengajuan'] ?? date('Y-m-d'),
            'kode_brng' => $kode_brng,
            'jumlah' => $jumlah,
            'status' => 'Menunggu',
            'catatan' => $input['catatan'] ?? '',
            'dibuat_oleh' => $this->core->getUserInfo('fullname', null, true) ?? '-',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if (!$saved) {
            return ['status' => 'error', 'message' => 'Pengajuan gagal disimpan.'];
        }

        return [
            'status' => 'success',
            'id' => (int)$this->db()->pdo()->lastInsertId(),
            'no_pengajuan' => $no_pengajuan,
            'kode_brng' => $kode_brng,
            'nama_brng' => $barang['nama_brng'],
            'jumlah' => $jumlah
        ];
    }

    public function apiPengajuanApprove()
    {
        if (!$this->_apiAuth('POST', 'can_update')) {
            return ['status' => 'error', 'message' => 'You do not have permission to access this resource'];
        }

        $input = $this->_apiInput();
        $id = (int)($input['id'] ?? 0);
        $pengajuan = $this->db('mlite_farmasi_pengajuan_obat')->where('id', $id)->oneArray();
        if (!$pengajuan) {
            return ['status' => 'error', 'message' => 'Data pengajuan tidak ditemukan.'];
        }
        if ($pengajuan['status'] === 'Disetujui') {
            return ['status' => 'error', 'message' => 'Pengajuan sudah disetujui sebelumnya.'];
        }

        $saved = $this->db('mlite_farmasi_pengajuan_obat')->where('id', $id)->save([
            'status' => 'Disetujui',
            'disetujui_oleh' => $this->core->getUserInfo('fullname', null, true) ?? '-',
            'disetujui_at' => date('Y-m-d H:i:s')
        ]);

        if (!$saved) {
            return ['status' => 'error', 'message' => 'Pengajuan gagal disetujui.'];
        }

        return ['status' => 'success', 'id' => $id, 'no_pengajuan' => $pengajuan['no_pengajuan']];
    }
    /* End API Farmasi Section */

    private function _addHeaderFiles()
    {
        // CSS
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));

        // JS
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'), 'footer');

        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));

        // MODULE SCRIPTS
        $this->core->addCSS(url([ADMIN, 'farmasi', 'css']));
        $this->core->addJS(url([ADMIN, 'farmasi', 'javascript']), 'footer');
    }

    private function _crudPermissionsFarmasi()
    {
      return $this->core->loadCrudPermissions('farmasi');
    }

    private function _ensureCrudPermission($action)
    {
      $mlite_crud_permissions = $this->_crudPermissionsFarmasi();
      if (!isset($mlite_crud_permissions[$action]) || $mlite_crud_permissions[$action] !== 'true') {
        $this->notify('failure', 'Anda tidak memiliki hak akses untuk aksi ini.');
        redirect(url([ADMIN, 'farmasi', 'manage']));
      }
    }

    private function _generateDocumentNumber($table, $field, $prefix)
    {
      $tanggal = date('Ymd');
      $kodeAwal = $prefix.$tanggal;

      if ($table === 'mlite_farmasi_pengajuan_obat' && $field === 'no_pengajuan') {
        $stmt = $this->db()->pdo()->prepare("SELECT `no_pengajuan` FROM `mlite_farmasi_pengajuan_obat` WHERE `no_pengajuan` LIKE ? ORDER BY `id` DESC LIMIT 1");
      } elseif ($table === 'mlite_farmasi_pemesanan_obat' && $field === 'no_pemesanan') {
        $stmt = $this->db()->pdo()->prepare("SELECT `no_pemesanan` FROM `mlite_farmasi_pemesanan_obat` WHERE `no_pemesanan` LIKE ? ORDER BY `id` DESC LIMIT 1");
      } else {
        return $kodeAwal.'0001';
      }

      $stmt->execute([$kodeAwal.'%']);
      $last = $stmt->fetch(\PDO::FETCH_ASSOC);

      $nomor = 1;
      if (!empty($last[$field]) && preg_match('/(\d{4})$/', $last[$field], $matches)) {
        $nomor = (int)$matches[1] + 1;
      }
      return $kodeAwal.sprintf('%04d', $nomor);
    }

}
