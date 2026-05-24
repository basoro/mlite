<?php
// mLITE - Kompatibel dengan PHP 7.4 - 8.3+
if (!version_compare(PHP_VERSION, '8.0.0', '>=')) {
    exit("mLITE requires at least <b>PHP 8.0.0</b> (Current: " . PHP_VERSION . ")");
}

function env(string $key, $default = null)
{
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
        $name = trim($name);
        $value = trim($value);

        // Hapus tanda kutip jika ada
        $value = trim($value, "\"'");

        if (!isset($_ENV[$name]) && !isset($_SERVER[$name])) {
            $_ENV[$name] = $value;
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
    $db_name = BASE_DIR . '/systems/data/mlite.sdb';
    $db_port = '';
} else {
    $db_host = env('MYSQLHOST') ?: 'localhost';
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

function multisite_host(): string
{
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    $host = preg_replace('/:\d+$/', '', $host);
    return (string) $host;
}

function multisite_subdomain(): string
{
    if (strtolower((string) env('MULTISITE_ENABLE', '')) !== 'true') {
        return '';
    }
    $host = multisite_host();
    if ($host === '') {
        return '';
    }

    $basesRaw = strtolower(trim((string) env('MULTISITE_DOMAIN', '')));
    $bases = array_filter(array_map('trim', preg_split('/[,\s]+/', $basesRaw)));
    if (empty($bases)) {
        return '';
    }

    $base = '';
    foreach ($bases as $candidate) {
        if ($candidate === '') continue;
        if ($host === $candidate || $host === 'www.' . $candidate || str_ends_with($host, '.' . $candidate)) {
            $base = $candidate;
            break;
        }
    }
    if ($base === '' || $host === $base || $host === 'www.' . $base) {
        return '';
    }

    $suffix = '.' . $base;
    $sub = substr($host, 0, -strlen($suffix));
    if ($sub === '' || strpos($sub, '.') !== false) {
        return '';
    }
    if (!preg_match('/^[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/', $sub)) {
        return '';
    }
    $reserved = array_filter(array_map('trim', explode(',', (string) env('MULTISITE_RESERVED_SUBDOMAINS', 'www,admin,api,static,assets,cdn,mail'))));
    if (in_array($sub, $reserved, true)) {
        return '';
    }
    return $sub;
}

function multisite_base_url(): string
{
    $appUrl = trim((string) env('APPURL', ''));
    if ($appUrl !== '') {
        return rtrim($appUrl, '/');
    }
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');
    $scheme = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host;
}

$multisiteTenant = multisite_subdomain();
$webappsSuffix = $multisiteTenant !== '' ? '/' . $multisiteTenant : '';

define('WEBAPPS_URL', multisite_base_url() . '/uploads' . $webappsSuffix);
define('WEBAPPS_PATH', BASE_DIR . '/uploads' . $webappsSuffix);

define('BACKUPS_URL', multisite_base_url() . '/backups' . ($multisiteTenant !== '' ? '/' . $multisiteTenant : '/_platform'));
define('BACKUP_DIR', BASE_DIR . '/backups' . ($multisiteTenant !== '' ? '/' . $multisiteTenant : '/_platform'));

// Multi APP
define('MULTI_APP', env('MULTIAPP') ?: 'false');
define('MULTI_APP_REDIRECT', env('MULTIAPP_REDIRECT') ?: '');

// Admin cat name
define('ADMIN', 'admin');

// Themes path
define('THEMES', BASE_DIR . '/themes');

// Modules path
define('MODULES', BASE_DIR . '/plugins');

// Uploads path
define('UPLOADS', WEBAPPS_PATH);

// Lock files
define('FILE_LOCK', false);

// Basic modules
define('BASIC_MODULES', json_encode([
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
define('DEV_MODE', false);

define('JWT_SECRET', 'mlite_secret_key_change_me');

?>
