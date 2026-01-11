<?php
// mLITE - Kompatibel dengan PHP 7.4 - 8.3+
if (!version_compare(PHP_VERSION, '8.0.0', '>=')) {
    exit("mLITE requires at least <b>PHP 8.0.0</b> (Current: " . PHP_VERSION . ")");
}

function env(string $key, $default = null) {
    return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
}

// Simple .env loader (PHP 8.3 safe, no putenv)
if (file_exists(BASE_DIR . '/.env')) {
    $lines = file(BASE_DIR . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        // Skip komentar & baris kosong
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (!str_contains($line, '=')) {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);
        $name  = trim($name);
        $value = trim($value);

        // Hapus tanda kutip jika ada
        $value = trim($value, "\"'");

        if (!isset($_ENV[$name]) && !isset($_SERVER[$name])) {
            $_ENV[$name]    = $value;
            $_SERVER[$name] = $value;
        }
    }
}


// Database Driver: 'mysql' or 'sqlite'
define('DBDRIVER', env('DBDRIVER') ?: '');

if (DBDRIVER == 'sqlite') {
    $db_host = '';
    $db_user = '';
    $db_pass = '';
    $db_name = BASE_DIR . '/mlite.sdb';
    $db_port = '';
} else {
    $db_host = env('MYSQLHOST') ?: 'mysql';
    $db_user = env('MYSQLUSER') ?: 'root';
    $db_pass = env('MYSQLPASSWORD') ?: '';
    $db_name = env('MYSQLDATABASE') ?: 'mlite';
    $db_port = env('MYSQLPORT') ?: '3306';
}

define('DBHOST', $db_host);
define('DBPORT', $db_port);
define('DBUSER', $db_user);
define('DBPASS', $db_pass);
define('DBNAME', $db_name);

// URL Webapps
define('WEBAPPS_URL', 'http://mlite.loc/uploads'); // Sesuaikan http://mlite.loc dengan domain atau IP Address server
define('WEBAPPS_PATH', BASE_DIR . '/uploads');

// Multi APP
define('MULTI_APP', false);
define('MULTI_APP_REDIRECT', '');

// Admin cat name
define('ADMIN', 'admin');

// Themes path
define('THEMES', BASE_DIR . '/themes');

// Modules path
define('MODULES', BASE_DIR . '/plugins');

// Uploads path
define('UPLOADS', BASE_DIR . '/uploads');

// Lock files
define('FILE_LOCK', false);

// Basic modules
define('BASIC_MODULES', serialize([
    9 => 'settings',
    0 => 'dashboard',
    1 => 'master',
    2 => 'pasien',
    3 => 'rawat_jalan',
    4 => 'kasir_rawat_jalan',
    5 => 'kepegawaian',
    6 => 'farmasi',
    8 => 'users',
    7 => 'modules',
   10 => 'wagateway'
]));

// Developer mode
define('DEV_MODE', true);

define('JWT_SECRET', 'mlite_secret_key_change_me');

?>