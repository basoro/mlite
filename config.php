<?php
if (!version_compare(PHP_VERSION, '7.0.0', '>=')) {
    exit("mLITE requires at least <b>PHP 7.0</b>");
}

define('DBHOST', 'localhost');
define('DBPORT', '3306');
define('DBUSER', 'root');
define('DBPASS', '');
define('DBNAME', 'rshd');

define('WHITELIST_IP', '*');

// Themes path
define('THEMES', BASE_DIR . '/themes');

// Modules path
define('MODULES', BASE_DIR . '/plugins');

// Uploads path
define('UPLOADS', BASE_DIR . '/uploads');

// Basic modules
define('BASIC_MODULES', serialize([
    0 => 'dashboard',
    1 => 'mlite_modules', 
    2 => 'mlite_users',
    3 => 'mlite_settings', 
    4 => 'mlite_api_tools'
]));

// Developer mode
define('DEV_MODE', true);
?>