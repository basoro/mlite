<?php
// mLITE - Kompatibel dengan PHP 7.4 - 8.3+
if (!version_compare(PHP_VERSION, '7.4.0', '>=')) {
    exit("mLITE requires at least <b>PHP 7.4</b> (Current: " . PHP_VERSION . ")");
}

define('DBHOST', 'localhost');
define('DBPORT', '3306');
define('DBUSER', 'root');
define('DBPASS', '');
define('DBNAME', 'mlite');

// URL Webapps
define('WEBAPPS_URL', 'http://localhost/uploads'); // Sesuaikan http://mlite.loc dengan domain atau IP Address server
define('WEBAPPS_PATH', BASE_DIR . '/uploads');

// Multi APP
define('MULTI_APP', false);
define('MULTI_APP_REDIRECT', 'dokter_ulun');

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

// HTML Beauty
define('HTML_BEAUTY', false);

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
