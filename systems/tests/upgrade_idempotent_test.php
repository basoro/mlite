<?php

/**
 * Uji idempotensi seluruh case di systems/upgrade.php.
 *
 * Untuk setiap versi awal (`case 'x.y.z':`), uji ini:
 *   1. menyiapkan database bersih hasil impor/konversi mlite_db.sql,
 *   2. menjalankan case tersebut sekali (run-1),
 *   3. menjalankan case yang sama sekali lagi pada database yang sama (run-2),
 *   4. membandingkan hasil dan isi database antara run-1 dan run-2.
 *
 * Uji GAGAL bila menjalankan case untuk kedua kalinya mengubah hasil, memunculkan
 * galat baru, atau mengubah jumlah baris/skema database. Case yang memang sudah
 * gagal pada run-1 (SQL khusus MySQL pada driver SQLite atau sebaliknya) hanya
 * dilaporkan sebagai PERHATIAN, selama run-2 tetap konsisten.
 *
 * Uji ini juga memeriksa sinkronisasi versi: fallback upgrade.php dan rantai
 * `case` harus berakhir di settings.version milik mlite_db.sql.
 *
 * Pemakaian (SQLite, default):
 *   php systems/tests/upgrade_idempotent_test.php [--only=6.4.0,6.4.2] [--keep]
 *        [--base=file.sdb] [--file=path/upgrade.php]
 *
 * Pemakaian (MySQL):
 *   php systems/tests/upgrade_idempotent_test.php --driver=mysql \
 *        --mysql-dsn="mysql:host=127.0.0.1;dbname=mlite_test;charset=utf8mb4" \
 *        --mysql-user=root --mysql-password=secret
 *
 * PERINGATAN: mode MySQL menghapus SELURUH tabel pada database di DSN tersebut
 * untuk setiap versi yang diuji (tabel dibuat ulang dari mlite_db.sql). Jangan
 * arahkan ke database berisi data nyata.
 */

if (PHP_SAPI !== 'cli') {
    exit(1);
}

$root        = dirname(__DIR__, 2);
$fileUpgrade = $root . '/systems/upgrade.php';
$fileSeed    = $root . '/mlite_db.sql';
$fileRunner  = __DIR__ . '/upgrade_case_runner.php';
$fileConvert = $root . '/convert_mysql_to_sqlite.py';

$driver = 'sqlite';
$mysqlDsn  = getenv('UPGRADE_MYSQL_DSN') ?: '';
$mysqlUser = getenv('UPGRADE_MYSQL_USER') ?: '';
$mysqlPass = getenv('UPGRADE_MYSQL_PASS') ?: '';

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
    } elseif (strpos($arg, '--driver=') === 0) {
        $driver = substr($arg, 9);
    } elseif (strpos($arg, '--mysql-dsn=') === 0) {
        $mysqlDsn = substr($arg, 12);
    } elseif (strpos($arg, '--mysql-user=') === 0) {
        $mysqlUser = substr($arg, 13);
    } elseif (strpos($arg, '--mysql-password=') === 0) {
        $mysqlPass = substr($arg, 17);
    } else {
        fwrite(STDERR, "Opsi tidak dikenal: {$arg}\n");
        exit(2);
    }
}

if (!in_array($driver, ['sqlite', 'mysql'], true)) {
    fwrite(STDERR, "Driver tidak didukung: {$driver} (pakai sqlite atau mysql)\n");
    exit(2);
}

if ($driver === 'mysql' && ($mysqlDsn === '' || strpos($mysqlDsn, 'mysql:') !== 0)) {
    fwrite(STDERR, "Mode MySQL butuh --mysql-dsn=\"mysql:host=...;dbname=...;charset=utf8mb4\".\n");
    exit(2);
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

function bangunBaseSqlite($fileSeed, $fileConvert, $dirKerja, $baseDariOpsi)
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

/**
 * Koneksi MySQL dengan multi-statement (dipakai untuk impor mlite_db.sql).
 */
function koneksiMysql($dsn, $user, $pass)
{
    return new \PDO($dsn, $user, $pass, [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
    ]);
}

/**
 * Kembalikan database MySQL ke kondisi hasil impor mlite_db.sql.
 */
function resetMysql(\PDO $pdo, $fileSeed)
{
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    $tabel = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
    foreach ($tabel as $nama) {
        $pdo->exec("DROP TABLE IF EXISTS `{$nama}`");
    }

    $sql = file_get_contents($fileSeed);
    if ($sql === false) {
        throw new \RuntimeException("Gagal membaca {$fileSeed}");
    }

    $pdo->exec($sql);
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
}

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

function bukaSqlite($file)
{
    $pdo = new \PDO('sqlite:' . $file);
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

/**
 * Ringkasan isi database SQLite: jumlah baris per tabel + sidik jari skema.
 */
function potretSqlite($file)
{
    $pdo = bukaSqlite($file);

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

/**
 * Ringkasan isi database MySQL: jumlah baris per tabel + sidik jari skema
 * (kolom dan indeks, dari information_schema).
 */
function potretMysql(\PDO $pdo)
{
    $namaTabel = $pdo->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME")
        ->fetchAll(\PDO::FETCH_COLUMN);

    // Satu query UNION ALL untuk semua jumlah baris.
    $bagian = [];
    foreach ($namaTabel as $nama) {
        $bagian[] = 'SELECT ' . $pdo->quote($nama) . ' AS t, COUNT(*) AS c FROM `' . $nama . '`';
    }
    $tabel = [];
    if ($bagian) {
        foreach ($pdo->query(implode(' UNION ALL ', $bagian))->fetchAll(\PDO::FETCH_KEY_PAIR) as $nama => $jumlah) {
            $tabel[(string) $nama] = (int) $jumlah;
        }
    }

    $kolom = $pdo->query("SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_KEY, EXTRA
        FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE()
        ORDER BY TABLE_NAME, ORDINAL_POSITION")->fetchAll(\PDO::FETCH_ASSOC);

    $indeks = $pdo->query("SELECT TABLE_NAME, INDEX_NAME, NON_UNIQUE, SEQ_IN_INDEX, COLUMN_NAME
        FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE()
        ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX")->fetchAll(\PDO::FETCH_ASSOC);

    return ['tabel' => $tabel, 'skema' => md5((string) json_encode([$kolom, $indeks]))];
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

function jalankanCase($fileRunner, $fileUpgrade, $target, $versi, $driver)
{
    $perintah = escapeshellarg(PHP_BINARY)
        . ' ' . escapeshellarg($fileRunner)
        . ' ' . escapeshellarg($target)
        . ' ' . escapeshellarg($versi)
        . ' ' . escapeshellarg($fileUpgrade)
        . ' 2>&1';

    if ($driver === 'mysql') {
        // Teruskan kredensial ke proses anak lewat environment, bukan argumen.
        $perintah = 'UPGRADE_DB_USER=' . escapeshellarg($GLOBALS['mysqlUser'])
            . ' UPGRADE_DB_PASS=' . escapeshellarg($GLOBALS['mysqlPass'])
            . ' ' . $perintah;
    }

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

if ($driver === 'sqlite') {
    $base = bangunBaseSqlite($fileSeed, $fileConvert, $dirKerja, $opsi['base']);
    $target = $fileKerja;
} else {
    $base = null;
    $target = $mysqlDsn;
    try {
        $pdoMysql = koneksiMysql($mysqlDsn, $mysqlUser, $mysqlPass);
    } catch (\Throwable $e) {
        fwrite(STDERR, "Gagal terhubung ke MySQL: " . $e->getMessage() . "\n");
        exit(2);
    }
}

$lulus = 0;
$gagal = 0;
$perhatian = 0;
$barisGagal = [];
$petaReturn = [];

// Versi sumber kebenaran: settings.version di mlite_db.sql.
$isiSeed = file_get_contents($fileSeed);
$versiSeed = null;
if (preg_match('/"settings"\s*,\s*"version"\s*,\s*"([0-9]+(?:\.[0-9]+)+)"/', (string) $isiSeed, $cocokSeed)) {
    $versiSeed = $cocokSeed[1];
}

echo "Uji idempotensi upgrade.php\n";
echo "Driver     : {$driver}\n";
if ($driver === 'sqlite') {
    echo "Basis data : {$base}\n";
} else {
    echo "MySQL DSN  : {$mysqlDsn}\n";
}
echo "Berkas uji : {$fileUpgrade}\n";
echo "settings.version: " . ($versiSeed ?? 'TIDAK DITEMUKAN') . "\n";
echo "Jumlah case: " . count($versiUji) . " versi\n\n";

printf("%-8s %-34s %-34s %s\n", 'VERSI', 'RUN-1', 'RUN-2', 'STATUS');
echo str_repeat('-', 110) . "\n";

foreach ($versiUji as $versi) {
    if ($driver === 'sqlite') {
        if (!copy($base, $fileKerja)) {
            fwrite(STDERR, "Gagal menyalin database dasar untuk versi {$versi}.\n");
            exit(2);
        }
        $awal = potretSqlite($fileKerja);
    } else {
        resetMysql($pdoMysql, $fileSeed);
        $awal = potretMysql($pdoMysql);
    }

    $run1 = jalankanCase($fileRunner, $fileUpgrade, $target, $versi, $driver);
    $setelah1 = $driver === 'sqlite' ? potretSqlite($fileKerja) : potretMysql($pdoMysql);

    $run2 = jalankanCase($fileRunner, $fileUpgrade, $target, $versi, $driver);
    $setelah2 = $driver === 'sqlite' ? potretSqlite($fileKerja) : potretMysql($pdoMysql);

    $petaReturn[$versi] = $run1['data']['return'] ?? null;

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

    if ($driver === 'sqlite' && !$opsi['keep']) {
        @unlink($fileKerja);
    }
}

/* ------------------------------------------------------------------------- */
/* Pemeriksaan sinkronisasi versi                                            */
/* ------------------------------------------------------------------------- */

echo "\nPemeriksaan sinkronisasi versi\n";
echo str_repeat('-', 110) . "\n";

if ($versiSeed === null) {
    echo "GAGAL: settings.version tidak ditemukan di mlite_db.sql.\n";
    $gagal++;
}

// Fallback upgrade.php (versi tidak dikenal) harus mengembalikan versi terbaru.
if ($versiSeed !== null) {
    if ($driver === 'sqlite') {
        if (!copy($base, $fileKerja)) {
            fwrite(STDERR, "Gagal menyalin database dasar untuk uji fallback.\n");
            exit(2);
        }
        $targetFallback = $fileKerja;
    } else {
        resetMysql($pdoMysql, $fileSeed);
        $targetFallback = $mysqlDsn;
    }

    $fallback = jalankanCase($fileRunner, $fileUpgrade, $targetFallback, '0.0.0', $driver);
    $versiFallback = $fallback['data']['return'] ?? null;

    if ($fallback['kode'] !== 0 || $versiFallback === null) {
        echo "GAGAL: fallback upgrade.php gagal dijalankan: " . trim($fallback['mentah']) . "\n";
        $gagal++;
    } elseif ($versiFallback !== $versiSeed) {
        echo "GAGAL: fallback upgrade.php mengembalikan '{$versiFallback}', settings.version '{$versiSeed}'.\n";
        $gagal++;
    } else {
        echo "OK: fallback upgrade.php mengembalikan '{$versiFallback}' (sama dengan settings.version).\n";
    }

    if ($driver === 'sqlite' && !$opsi['keep']) {
        @unlink($fileKerja);
    }
}

// Setiap case harus mengembalikan versi yang punya case sendiri atau versi terbaru.
if ($opsi['only']) {
    echo "Rantai versi tidak diperiksa penuh karena --only dipakai (dasar: " . ($versiSemua[0] ?? '-') . ").\n";
} else {
    $putus = [];

    foreach ($petaReturn as $dari => $ke) {
        // null berarti case gagal dijalankan pada fixture ini; itu sudah
        // dilaporkan sebagai PERHATIAN, bukan rantai yang salah.
        if ($ke !== null && $ke !== $versiSeed && !in_array($ke, $versiSemua, true)) {
            $putus[] = "{$dari} -> {$ke} (tidak ada case '{$ke}' dan bukan versi terbaru)";
        }
    }

    $kini = $versiSemua[0] ?? null;
    $dilewati = [];
    $terhenti = null;
    while ($kini !== null && $kini !== $versiSeed) {
        if (isset($dilewati[$kini])) {
            $putus[] = "rantai versi berputar di {$kini}";
            break;
        }
        if (($petaReturn[$kini] ?? null) === null) {
            $terhenti = $kini;
            break;
        }
        $dilewati[$kini] = true;
        $kini = $petaReturn[$kini];
    }

    if ($terhenti !== null) {
        echo "PERHATIAN: rantai versi tidak bisa ditelusuri melewati '{$terhenti}' "
            . "(case itu gagal dijalankan pada fixture ini, lihat baris PERHATIAN di atas).\n";
    } elseif ($kini !== $versiSeed) {
        $putus[] = "rantai versi berakhir di " . ($kini ?? 'null') . ", bukan {$versiSeed}";
    }

    if ($putus) {
        foreach ($putus as $pesan) {
            echo "GAGAL: {$pesan}.\n";
            $gagal++;
        }
    } elseif ($terhenti === null) {
        echo "OK: rantai versi utuh dan berakhir di '{$versiSeed}'.\n";
    }
}

echo str_repeat('-', 110) . "\n";
echo "Lulus: {$lulus}  Perhatian: {$perhatian}  Gagal: {$gagal}\n";
echo "Driver diuji: {$driver}.\n";

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
