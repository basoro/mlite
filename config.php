<?php
if (!version_compare(PHP_VERSION, '5.5.0', '>=')) {
    exit("Khanza LITE requires at least <b>PHP 5.5</b>");
}

define('DBHOST', 'localhost');
define('DBPORT', '3306');
define('DBUSER', 'root');
define('DBPASS', '');
define('DBNAME', 'sik');

// URL Webapps
define('WEBAPPS_URL', 'http://localhost/webapps');
define('WEBAPPS_PATH', BASE_DIR . '/../webapps');

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
    8 => 'settings',
    //4 => 'master',
    //1 => 'pasien',
    //2 => 'rawat_jalan',
    //3 => 'kasir_rawat_jalan',
    //11 => 'rawat_inap',
    //5 => 'anjungan',
    0 => 'dashboard',
    7 => 'users',
    6 => 'modules',
    //9 => 'icd',
    //10 => 'vclaim'
]));

// Developer mode
define('DEV_MODE', true);

?>
