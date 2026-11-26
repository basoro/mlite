<?php

/**
 * Runner uji: menjalankan systems/upgrade.php satu kali untuk satu versi awal
 * pada database SQLite sementara, lalu mencetak hasilnya sebagai JSON.
 *
 * Dipakai oleh upgrade_idempotent_test.php. Dijalankan sebagai proses terpisah
 * karena upgrade.php mendeklarasikan fungsi global rrmdir() sehingga tidak bisa
 * di-include dua kali dalam satu proses PHP.
 *
 * Pemakaian:
 *   php systems/tests/upgrade_case_runner.php <file.sdb|dsn-mysql> <versi_awal> [file_upgrade.php]
 *
 * Target diawali `mysql:` diperlakukan sebagai DSN MySQL; kredensial dibaca
 * dari environment UPGRADE_DB_USER / UPGRADE_DB_PASS.
 */

if (PHP_SAPI !== 'cli') {
    exit(1);
}

if ($argc < 3) {
    fwrite(STDERR, "Pemakaian: php upgrade_case_runner.php <file.sdb> <versi_awal>\n");
    exit(2);
}

$dbFile  = $argv[1];
$version = $argv[2];

$root = dirname(__DIR__, 2);
$fileUpgrade = $argv[3] ?? ($root . '/systems/upgrade.php');
$driver = strpos($dbFile, 'mysql:') === 0 ? 'mysql' : 'sqlite';

define('UPGRADABLE', true);
define('DBDRIVER', $driver);
define('BASE_DIR', $root);

require_once __DIR__ . '/UpgradeTestHarness.php';

$hasil = [
    'versi'      => $version,
    'return'     => null,
    'galat'      => null,
    'galat_file' => null,
    'galat_baris' => null,
];

try {
    if ($driver === 'mysql') {
        $pdo = new \PDO(
            $dbFile,
            getenv('UPGRADE_DB_USER') ?: '',
            getenv('UPGRADE_DB_PASS') ?: '',
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );
    } else {
        $pdo = new \PDO('sqlite:' . $dbFile);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    $harness = new UpgradeTestHarness($pdo);
    $hasil['return'] = $harness->jalankan($fileUpgrade, $version);
} catch (\Throwable $e) {
    $hasil['galat']       = $e->getMessage();
    $hasil['galat_file']  = basename((string) $e->getFile());
    $hasil['galat_baris'] = $e->getLine();
    echo json_encode($hasil), PHP_EOL;
    exit(1);
}

echo json_encode($hasil), PHP_EOL;
exit(0);
