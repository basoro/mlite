<?php
if (!version_compare(PHP_VERSION, '5.5.0', '>=')) {
	exit("Khanza LITE requires at least <b>PHP 5.5</b>");
}

ini_set('memory_limit', '-1');
date_default_timezone_set('Asia/Jakarta');

define('DBHOST', 'localhost');
define('DBPORT', '3306');
define('DBUSER', 'root');
define('DBPASS', '');
define('DBNAME', 'sik');

// URL Webapps
define('WEBAPPS_URL', '');
define('WEBAPPS_PATH', BASE_DIR . '/webapps');

// Admin url path
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
	9999 => 'settings',
	0 => 'dashboard',
	1 => 'pasien',
	2 => 'pendaftaran',
	3 => 'ralan',
	9996 => 'master',
	9998 => 'users',
	9997 => 'modules',
]));

// Developer mode
define('DEV_MODE', false);
