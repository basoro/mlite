<?php
// mLITE - Kompatibel dengan PHP 7.4 - 8.3+
if (!version_compare(PHP_VERSION, '8.0.0', '>=')) {
    exit("mLITE requires at least <b>PHP 8.0.0</b> (Current: " . PHP_VERSION . ")");
}

// Simple .env loader
if (file_exists(BASE_DIR . '/.env')) {
    $lines = file(BASE_DIR . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Database Driver: 'mysql' or 'sqlite'
define('DBDRIVER', getenv('DBDRIVER') ?: '');

if (DBDRIVER == 'sqlite') {
    $db_host = '';
    $db_user = '';
    $db_pass = '';
    $db_name = BASE_DIR . '/mlite.sdb';
    $db_port = '';
} else {
    $db_host = getenv('MYSQLHOST') ?: '';
    $db_user = getenv('MYSQLUSER') ?: '';
    $db_pass = getenv('MYSQLPASSWORD') ?: '';
    $db_name = getenv('MYSQLDATABASE') ?: '';
    $db_port = getenv('MYSQLPORT') ?: '';
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

?>
