<?php

/**
 * Uji idempotensi seluruh case di systems/upgrade.php pada SQLite sementara.
 *
 * Untuk setiap versi awal (`case 'x.y.z':`), uji ini:
 *   1. menyalin database SQLite bersih hasil konversi mlite_db.sql,
 *   2. menjalankan case tersebut sekali (run-1),
 *   3. menjalankan case yang sama sekali lagi pada database yang sama (run-2),
 *   4. membandingkan hasil dan isi database antara run-1 dan run-2.
 *
 * Uji GAGAL bila menjalankan case untuk kedua kalinya mengubah hasil, memunculkan
 * galat baru, atau mengubah jumlah baris/skema database. Case yang memang sudah
 * gagal pada run-1 (SQL khusus MySQL pada driver SQLite atau karena data awal
 * uji) hanya dilaporkan sebagai PERHATIAN, selama run-2 tetap konsisten.
 *
 * Pemakaian:
 *   php systems/tests/upgrade_idempotent_test.php [--only=6.4.0,6.4.2] [--keep]
 *        [--base=file.sdb] [--file=path/upgrade.php]
 *
 * Catatan: jalur MySQL tidak diuji di sini (butuh server MySQL); uji ini
 * memakai DBDRIVER = sqlite.
 */

if (PHP_SAPI !== 'cli') {
    exit(1);
}

$root        = dirname(__DIR__, 2);
$fileUpgrade = $root . '/systems/upgrade.php';
$fileSeed    = $root . '/mlite_db.sql';
$fileRunner  = __DIR__ . '/upgrade_case_runner.php';
$fileConvert = $root . '/convert_mysql_to_sqlite.py';

$opsi = ['only' => [], 'keep' => false, 'base' => null];

foreach (array_slice($argv, 1) as $arg) {
    if (strpos($arg, '--only=') === 0) {
        $opsi['only'] = array_values(array_filter(array_map('trim', explode(',', substr($arg, 7)))));
    } elseif ($arg === '--keep') {
        $opsi['keep'] = true;
    } elseif (strpos($arg, '--base=') === 0) {
        $opsi['base'] = substr($arg, 7);
    } elseif (strpos($arg, '--file=') === 0) {
        $fileUpgrade = substr($arg, 7);
    } else {
        fwrite(STDERR, "Opsi tidak dikenal: {$arg}\n");
        exit(2);
    }
}

if (!is_file($fileUpgrade) || !is_file($fileSeed) || !is_file($fileRunner)) {
    fwrite(STDERR, "Berkas uji tidak lengkap (upgrade.php / mlite_db.sql / runner).\n");
    exit(2);
}

$dirKerja = $root . '/tmp/upgrade_test';
if (!is_dir($dirKerja) && !mkdir($dirKerja, 0775, true) && !is_dir($dirKerja)) {
    fwrite(STDERR, "Gagal membuat direktori kerja: {$dirKerja}\n");
    exit(2);
}

/* ------------------------------------------------------------------------- */
/* Database dasar                                                            */
/* ------------------------------------------------------------------------- */

function bangunBase($fileSeed, $fileConvert, $dirKerja, $baseDariOpsi)
{
    if ($baseDariOpsi !== null) {
        if (!is_file($baseDariOpsi)) {
            fwrite(STDERR, "Database dasar tidak ditemukan: {$baseDariOpsi}\n");
            exit(2);
        }
        return $baseDariOpsi;
    }

    $base = $dirKerja . '/base.sdb';

    // Pakai ulang bila lebih baru dari mlite_db.sql.
    if (is_file($base) && filemtime($base) >= filemtime($fileSeed) && filesize($base) > 0) {
        return $base;
    }

    $perintah = 'python3 ' . escapeshellarg($fileConvert)
        . ' ' . escapeshellarg($fileSeed)
        . ' ' . escapeshellarg($base)
        . ' --force 2>&1';

    $keluaran = [];
    $kode = 0;
    exec($perintah, $keluaran, $kode);

    // Konverter bisa keluar non-nol karena satu-dua INSERT data yang rusak di
    // dump, padahal skemanya lengkap. Yang menentukan: DB hasilnya bisa dipakai.
    $jumlahTabel = 0;
    if (is_file($base) && filesize($base) > 0) {
        try {
            $pdo = new \PDO('sqlite:' . $base);
            $jumlahTabel = (int) $pdo->query("SELECT COUNT(*) FROM sqlite_master WHERE type = 'table'")->fetchColumn();
            $pdo = null;
        } catch (\Throwable $e) {
            $jumlahTabel = 0;
        }
    }

    if ($jumlahTabel < 200) {
        fwrite(STDERR, "Gagal membangun database SQLite dari mlite_db.sql (tabel terbaca: {$jumlahTabel}):\n");
        fwrite(STDERR, implode("\n", array_slice($keluaran, -10)) . "\n");
        fwrite(STDERR, "Butuh python3 + convert_mysql_to_sqlite.py.\n");
        exit(2);
    }

    if ($kode !== 0) {
        echo "Peringatan: konverter keluar dengan kode {$kode}, tetapi {$jumlahTabel} tabel berhasil dibuat.\n\n";
    }

    touch($base);
    return $base;
}

$base = bangunBase($fileSeed, $fileConvert, $dirKerja, $opsi['base']);

/* ------------------------------------------------------------------------- */
/* Daftar case                                                               */
/* ------------------------------------------------------------------------- */

$isiUpgrade = file_get_contents($fileUpgrade);
if ($isiUpgrade === false || !preg_match_all("/^\s*case '([0-9]+(?:\.[0-9]+)+)':/m", $isiUpgrade, $cocok)) {
    fwrite(STDERR, "Tidak ada case versi yang ditemukan di {$fileUpgrade}\n");
    exit(2);
}

$versiSemua = array_values(array_unique($cocok[1]));
$versiUji = $opsi['only'] ? array_values(array_intersect($versiSemua, $opsi['only'])) : $versiSemua;

if (!$versiUji) {
    fwrite(STDERR, "Tidak ada versi yang cocok dengan --only.\n");
    exit(2);
}

/* ------------------------------------------------------------------------- */
/* Utilitas                                                                  */
/* ------------------------------------------------------------------------- */

function bukaDb($file)
{
    $pdo = new \PDO('sqlite:' . $file);
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

/**
 * Ringkasan isi database: jumlah baris per tabel + sidik jari skema.
 */
function potret($file)
{
    $pdo = bukaDb($file);

    $tabel = [];
    $namaTabel = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name")
        ->fetchAll(\PDO::FETCH_COLUMN);

    foreach ($namaTabel as $nama) {
        $kutip = '"' . str_replace('"', '""', $nama) . '"';
        $tabel[$nama] = (int) $pdo->query("SELECT COUNT(*) FROM {$kutip}")->fetchColumn();
    }

    $skema = $pdo->query("SELECT type, name, tbl_name, sql FROM sqlite_master WHERE name NOT LIKE 'sqlite_%' ORDER BY type, name")
        ->fetchAll(\PDO::FETCH_ASSOC);

    return ['tabel' => $tabel, 'skema' => md5((string) json_encode($skema))];
}

function bedaPotret(array $sebelum, array $sesudah)
{
    $beda = [];

    if ($sebelum['skema'] !== $sesudah['skema']) {
        $beda[] = 'skema berubah';
    }

    $semuaTabel = array_unique(array_merge(array_keys($sebelum['tabel']), array_keys($sesudah['tabel'])));
    sort($semuaTabel);
    foreach ($semuaTabel as $nama) {
        $lama = $sebelum['tabel'][$nama] ?? 0;
        $baru = $sesudah['tabel'][$nama] ?? 0;
        if ($lama !== $baru) {
            $beda[] = "{$nama}: {$lama}->{$baru} baris";
        }
    }

    return $beda;
}

function jalankanCase($fileRunner, $fileUpgrade, $fileDb, $versi)
{
    $perintah = escapeshellarg(PHP_BINARY)
        . ' ' . escapeshellarg($fileRunner)
        . ' ' . escapeshellarg($fileDb)
        . ' ' . escapeshellarg($versi)
        . ' ' . escapeshellarg($fileUpgrade)
        . ' 2>&1';

    $keluaran = [];
    $kode = 0;
    exec($perintah, $keluaran, $kode);

    $mentah = implode("\n", $keluaran);
    $json = json_decode(trim((string) end($keluaran)), true);

    if (!is_array($json)) {
        return ['kode' => $kode ?: 1, 'data' => null, 'mentah' => $mentah];
    }

    return ['kode' => $kode, 'data' => $json, 'mentah' => $mentah];
}

function ringkasHasil(array $hasil)
{
    if (!empty($hasil['data']['galat'])) {
        $pesan = $hasil['data']['galat'];
        return 'GALAT: ' . (strlen($pesan) > 70 ? substr($pesan, 0, 70) . '...' : $pesan);
    }
    if ($hasil['kode'] !== 0) {
        return 'GAGAL (exit ' . $hasil['kode'] . ')';
    }
    return 'OK -> ' . var_export($hasil['data']['return'] ?? null, true);
}

/* ------------------------------------------------------------------------- */
/* Eksekusi                                                                  */
/* ------------------------------------------------------------------------- */

$fileKerja = $dirKerja . '/kerja.sdb';

$lulus = 0;
$gagal = 0;
$perhatian = 0;
$barisGagal = [];

echo "Uji idempotensi upgrade.php\n";
echo "Basis data : {$base}\n";
echo "Berkas uji : {$fileUpgrade}\n";
echo "Jumlah case: " . count($versiUji) . " versi\n\n";

printf("%-8s %-34s %-34s %s\n", 'VERSI', 'RUN-1', 'RUN-2', 'STATUS');
echo str_repeat('-', 110) . "\n";

foreach ($versiUji as $versi) {
    if (!copy($base, $fileKerja)) {
        fwrite(STDERR, "Gagal menyalin database dasar untuk versi {$versi}.\n");
        exit(2);
    }

    $awal = potret($fileKerja);

    $run1 = jalankanCase($fileRunner, $fileUpgrade, $fileKerja, $versi);
    $setelah1 = potret($fileKerja);

    $run2 = jalankanCase($fileRunner, $fileUpgrade, $fileKerja, $versi);
    $setelah2 = potret($fileKerja);

    $galat1 = $run1['data']['galat'] ?? null;
    $galat2 = $run2['data']['galat'] ?? null;

    $masalah = [];

    if (($run1['data']['return'] ?? null) !== ($run2['data']['return'] ?? null)) {
        $masalah[] = 'nilai return berbeda';
    }
    if ((bool) $galat1 !== (bool) $galat2) {
        $masalah[] = 'run-2 memunculkan galat baru';
    } elseif ($galat1 !== null && $galat1 !== $galat2) {
        $masalah[] = 'pesan galat berbeda';
    }
    if ($run1['kode'] !== $run2['kode']) {
        $masalah[] = 'exit code berbeda';
    }

    $beda = bedaPotret($setelah1, $setelah2);
    if ($beda) {
        $masalah[] = 'isi DB berubah pada run-2 (' . implode(', ', $beda) . ')';
    }

    $adaPerubahanRun1 = bedaPotret($awal, $setelah1);

    if ($masalah) {
        $status = 'GAGAL: ' . implode('; ', $masalah);
        $gagal++;
        $barisGagal[] = [$versi, $status, $run1, $run2];
    } elseif ($galat1 !== null) {
        // Sudah gagal sejak run-1 dan tidak berubah pada run-2: bukan regresi
        // idempotensi, tetapi perlu diketahui.
        $status = 'PERHATIAN: run-1 sudah gagal (konsisten pada run-2)';
        $perhatian++;
    } else {
        if ($adaPerubahanRun1) {
            $ringkas = array_map(function ($item) {
                return $item === 'skema berubah' ? 'skema' : $item;
            }, $adaPerubahanRun1);
            $status = 'LULUS (idempoten) ; run-1 mengubah: ' . implode(', ', array_slice($ringkas, 0, 3));
        } else {
            $status = 'LULUS (idempoten) ; run-1 tanpa perubahan DB';
        }
        $lulus++;
    }

    printf("%-8s %-34s %-34s %s\n", $versi, ringkasHasil($run1), ringkasHasil($run2), $status);

    if (!$opsi['keep']) {
        @unlink($fileKerja);
    }
}

echo str_repeat('-', 110) . "\n";
echo "Lulus: {$lulus}  Perhatian: {$perhatian}  Gagal: {$gagal}\n";
echo "Jalur MySQL tidak diuji (uji ini memakai DBDRIVER = sqlite).\n";

if ($barisGagal) {
    echo "\nRincian kegagalan:\n";
    foreach ($barisGagal as $baris) {
        echo "- {$baris[0]}: {$baris[1]}\n";
        echo "  run-1: " . trim($baris[2]['mentah']) . "\n";
        echo "  run-2: " . trim($baris[3]['mentah']) . "\n";
    }
}

// Database dasar sengaja tidak dihapus: dipakai ulang pada eksekusi berikutnya
// selama mlite_db.sql belum berubah.

exit($gagal > 0 ? 1 : 0);
