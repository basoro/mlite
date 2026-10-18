<?php

namespace Systems;

/**
 * Helper stok per-batch (no_batch) untuk modul farmasi & apotek.
 *
 * Konsep:
 * - Stok total per depot tetap disimpan di tabel `gudangbarang` (1 baris per
 *   kode_brng + kd_bangsal) agar seluruh daftar obat lain tetap berjalan.
 * - Stok per batch disimpan di tabel `mlite_farmasi_obat_batch` yang juga
 *   menyimpan harga beli (h_beli) dan tanggal kedaluwarsa (expire) per batch.
 * - Pengurangan stok saat pemberian obat menggunakan prioritas FEFO:
 *   batch dengan kedaluwarsa terdekat yang masih valid keluar lebih dulu;
 *   batch tanpa tanggal kedaluwarsa keluar terakhir (FIFO per urutan masuk).
 *
 * Harga yang dibebankan ke pasien mengikuti batch yang benar-benar keluar:
 * obat dari batch lama (mis.urchased Rp 100) ditagih dengan harga batch itu,
 * setelah habis barulah digunakan batch baru (mis.assed Rp 150).
 */
class PharmacyBatches
{
    protected static function table(): string
    {
        return 'mlite_farmasi_obat_batch';
    }

    /**
     * Allocation FEFO: kembalikan daftar potongan batch yang habis dipakai
     * untuk jumlah tertentu dan kurangi stok batch di ledger.
     *
     * @param \PDO $pdo
     * @param string $kodeBrng  kode barang
     * @param string $kdBangsal depot/bangsal sumber stok
     * @param float  $jml       jumlah yang dikeluarkan
     * @param string|null $noBatch batch yang ingin didahulukan (opsional)
     * @return array daftar potongan: [no_batch, no_faktur, h_beli, expire, jml]
     */
    public static function allocateFefo(\PDO $pdo, $kodeBrng, $kdBangsal, $jml, $noBatch = null)
    {
        $jml = (float) $jml;
        $portions = [];

        if ($jml <= 0) {
            return $portions;
        }

        $order = "(expire IS NULL) ASC, expire ASC, id ASC";

        $stmt = $pdo->prepare("SELECT no_batch, no_faktur, h_beli, expire, stok
            FROM `".self::table()."`
            WHERE kode_brng = :kode AND kd_bangsal = :bangsal AND stok > 0
            ORDER BY ".($noBatch !== null && $noBatch !== '' && $noBatch !== '0'
                ? "(no_batch <> :nobatch) ASC, "
                : "").$order);
        $params = [':kode' => $kodeBrng, ':bangsal' => $kdBangsal];
        if ($noBatch !== null && $noBatch !== '' && $noBatch !== '0') {
            $params[':nobatch'] = $noBatch;
        }
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $sisa = $jml;
        foreach ($rows as $row) {
            if ($sisa <= 0) {
                break;
            }
            $ambil = min((float) $row['stok'], $sisa);
            $stmtUpd = $pdo->prepare("UPDATE `".self::table()."` SET stok = stok - :ambil
                WHERE kode_brng = :kode AND kd_bangsal = :bangsal AND no_batch = :nobatch AND no_faktur = :nofaktur");
            $stmtUpd->execute([
                ':ambil' => $ambil,
                ':kode' => $kodeBrng,
                ':bangsal' => $kdBangsal,
                ':nobatch' => $row['no_batch'],
                ':nofaktur' => $row['no_faktur'],
            ]);

            $portions[] = [
                'no_batch' => $row['no_batch'],
                'no_faktur' => $row['no_faktur'],
                'h_beli' => (float) $row['h_beli'],
                'expire' => $row['expire'],
                'jml' => $ambil,
            ];
            $sisa -= $ambil;
        }

        return $portions;
    }

    /**
     * Harga fallback ketika tidak ada batch tersedia pada depot (misal obat
     * belum pernah diterima atau ledger kosong): pakai harga master.
     */
    public static function fallbackPortions(\PDO $pdo, $kodeBrng, $jml, $noBatch = '0', $noFaktur = '0')
    {
        $stmt = $pdo->prepare("SELECT COALESCE(NULLIF(h_beli, 0), NULLIF(dasar, 0), 0) AS h_beli
            FROM databarang WHERE kode_brng = ? LIMIT 1");
        $stmt->execute([$kodeBrng]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [[
            'no_batch' => $noBatch ?: '0',
            'no_faktur' => $noFaktur ?: '0',
            'h_beli' => (float) ($row['h_beli'] ?? 0),
            'expire' => null,
            'jml' => (float) $jml,
        ]];
    }

    /**
     * Allocation + fallback: potongan FEFO, dan bila stok batch tidak cukup
     * sisanya ditandai sebagai fallback '0' (harga master) tanpa mengurangi ledger.
     */
    public static function allocateWithFallback(\PDO $pdo, $kodeBrng, $kdBangsal, $jml, $noBatch = null, $noFaktur = '0')
    {
        $portions = self::allocateFefo($pdo, $kodeBrng, $kdBangsal, $jml, $noBatch);
        $sudah = 0.0;
        foreach ($portions as $portion) {
            $sudah += (float) $portion['jml'];
        }
        $sisa = (float) $jml - $sudah;
        if ($sisa > 0.000001) {
            $fallback = self::fallbackPortions($pdo, $kodeBrng, $sisa, $noBatch ?: '0', $noFaktur ?: '0');
            foreach ($fallback as $portion) {
                $portions[] = $portion;
            }
        }
        return $portions;
    }

    /**
     * Tambah stok batch (upsert): untuk penerimaan, mutasi masuk ke depot,
     * set stok pengadaan, atau pengembalian stok saat pembatalan pemberian.
     */
    public static function credit(\PDO $pdo, $kodeBrng, $kdBangsal, $noBatch, $noFaktur, $jml, $hBeli = null, $expire = null)
    {
        $noBatch = $noBatch !== null && $noBatch !== '' ? $noBatch : '0';
        $noFaktur = $noFaktur !== null && $noFaktur !== '' ? $noFaktur : '0';
        $jml = (float) $jml;

        $stmt = $pdo->prepare("SELECT id, h_beli FROM `".self::table()."`
            WHERE kode_brng = ? AND kd_bangsal = ? AND no_batch = ? AND no_faktur = ? LIMIT 1");
        $stmt->execute([$kodeBrng, $kdBangsal, $noBatch, $noFaktur]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Sumber expire cadangan: bila pemanggil tidak mengirim expire, pakai
        // tanggal kedaluwarsa batch yang sama (kode + no. batch + no. faktur)
        // yang sudah pernah tercatat agar info lédger tidak hilang saat mutasi
        // atau pengembalian pemberian obat.
        if ($expire === null || $expire === '') {
            $stmtExp = $pdo->prepare("SELECT expire FROM `".self::table()."`
                WHERE kode_brng = ? AND no_batch = ? AND no_faktur = ?
                  AND expire IS NOT NULL AND expire <> ''
                LIMIT 1");
            $stmtExp->execute([$kodeBrng, $noBatch, $noFaktur]);
            $expRow = $stmtExp->fetch(\PDO::FETCH_ASSOC);
            if ($expRow) {
                $expire = $expRow['expire'];
            }
        }

        $now = date('Y-m-d H:i:s');

        if ($row) {
            $stmtUpd = $pdo->prepare("UPDATE `".self::table()."`
                SET stok = stok + :jml,
                    h_beli = CASE WHEN COALESCE(:hbeli, 0) > 0 THEN :hbeli ELSE h_beli END,
                    expire = COALESCE(:expire, expire),
                    updated_at = :now
                WHERE id = :id");
            $stmtUpd->execute([
                ':jml' => $jml,
                ':hbeli' => $hBeli !== null ? (float) $hBeli : null,
                ':expire' => $expire ?: null,
                ':now' => $now,
                ':id' => $row['id'],
            ]);
            return;
        }

        $stmtIns = $pdo->prepare("INSERT INTO `".self::table()."`
            (kode_brng, kd_bangsal, no_batch, no_faktur, h_beli, expire, stok, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtIns->execute([
            $kodeBrng,
            $kdBangsal,
            $noBatch,
            $noFaktur,
            (float) ($hBeli ?: 0),
            $expire ?: null,
            $jml,
            $now,
            $now,
        ]);
    }

    /**
     * Kurangi stok batch tertentu secara langsung (mis. untuk mutasi out dari
     * batch yang eksplisit dipilih petugas).
     */
    public static function debit(\PDO $pdo, $kodeBrng, $kdBangsal, $noBatch, $noFaktur, $jml)
    {
        $noBatch = $noBatch !== null && $noBatch !== '' ? $noBatch : '0';
        $noFaktur = $noFaktur !== null && $noFaktur !== '' ? $noFaktur : '0';
        // Gunakan tanggal dari PHP agar kompatibel MySQL & SQLite (NOW() tidak ada di SQLite)
        $stmt = $pdo->prepare("UPDATE `".self::table()."` SET stok = stok - ?, updated_at = ?
            WHERE kode_brng = ? AND kd_bangsal = ? AND no_batch = ? AND no_faktur = ?");
        $stmt->execute([(float) $jml, date('Y-m-d H:i:s'), $kodeBrng, $kdBangsal, $noBatch, $noFaktur]);
    }

    /**
     * Ambil satu potongan FEFO dari depot tertentu tanpa mengurangi stok,
     * untuk keperluan tampilan (mutasi UI, rekomendasi batch).
     */
    public static function getOldestPortion(\PDO $pdo, $kodeBrng, $kdBangsal)
    {
        $stmt = $pdo->prepare("SELECT no_batch, no_faktur, h_beli, expire, stok
            FROM `".self::table()."`
            WHERE kode_brng = ? AND kd_bangsal = ? AND stok > 0
            ORDER BY (expire IS NULL) ASC, expire ASC, id ASC
            LIMIT 1");
        $stmt->execute([$kodeBrng, $kdBangsal]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Sinkronkan ulang tulisan total `gudangbarang` dari jumlah batch ledger
     * (lakukan setelah opname/penyesuaian besar).
     */
    public static function syncTotalRow(\PDO $pdo, $kodeBrng, $kdBangsal)
    {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(stok), 0) AS total,
            (SELECT no_batch FROM `".self::table()."`
                WHERE kode_brng = :kode1 AND kd_bangsal = :bangsal1 AND stok > 0
                ORDER BY (expire IS NULL) ASC, expire ASC, id ASC LIMIT 1) AS no_batch,
            (SELECT no_faktur FROM `".self::table()."`
                WHERE kode_brng = :kode2 AND kd_bangsal = :bangsal2 AND stok > 0
                ORDER BY (expire IS NULL) ASC, expire ASC, id ASC LIMIT 1) AS no_faktur
            FROM `".self::table()."`
            WHERE kode_brng = :kode3 AND kd_bangsal = :bangsal3");
        $stmt->execute([
            ':kode1' => $kodeBrng, ':bangsal1' => $kdBangsal,
            ':kode2' => $kodeBrng, ':bangsal2' => $kdBangsal,
            ':kode3' => $kodeBrng, ':bangsal3' => $kdBangsal,
        ]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $total = (float) ($row['total'] ?? 0);
        $save = $pdo->prepare("UPDATE gudangbarang SET stok = ?,
            no_batch = COALESCE(NULLIF(:nb, ''), no_batch),
            no_faktur = COALESCE(NULLIF(:nf, ''), no_faktur)
            WHERE kode_brng = ? AND kd_bangsal = ?");
        $save->execute([$total, $row['no_batch'] ?? '', $row['no_faktur'] ?? '', $kodeBrng, $kdBangsal]);
    }

    public static function getBatchRows(\PDO $pdo, $kodeBrng, $kdBangsal)
    {
        $where = '';
        $params = [];
        if ($kodeBrng !== null && $kodeBrng !== '') {
            $where .= " AND kode_brng = ?";
            $params[] = $kodeBrng;
        }
        if ($kdBangsal !== null && $kdBangsal !== '') {
            $where .= " AND kd_bangsal = ?";
            $params[] = $kdBangsal;
        }
        $stmt = $pdo->prepare("SELECT * FROM `".self::table()."`
            WHERE stok <> 0".$where."
            ORDER BY (expire IS NULL) ASC, expire ASC, id ASC");
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
