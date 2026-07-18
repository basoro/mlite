<?php
/**
 * Tools and Router script for PHP Built-in Web Server
 * Emulates Apache .htaccess rewrite rules
 * 
 * Usage: PHP_CLI_SERVER_WORKERS=8 php -S localhost:8000 tools.php
 * Usage: PHP_CLI_SERVER_WORKERS=8 ~/Server/runtime/php/bin/php -c ~/Server/data/php.ini -S 0.0.0.0:8000 tools.php
 */

if (!defined('BASE_DIR')) {
    define('BASE_DIR', __DIR__);
}
require_once __DIR__ . '/config.php';



$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

$path = __DIR__ . $uri;

// 1. Emulate security blocks from .htaccess
if (preg_match('#^/(systems|themes|tmp)/.*\.php$#', $uri)) {
    http_response_code(403);
    echo "Access Denied";
    exit;
}

if (preg_match('/\.(sdb|md|txt|env)$/i', $uri)) {
    http_response_code(403);
    echo "Access Denied";
    exit;
}

// 2. Handle /admin/ routes specific logic
if (strpos($uri, '/admin/') === 0 || $uri === '/admin') {
    // If it's a file that exists, serve it
    if (file_exists($path) && !is_dir($path)) {
        return false;
    }

    // If it's the admin root directory, serve admin/index.php
    if ($uri === '/admin' || $uri === '/admin/') {
        $_SERVER['SCRIPT_NAME'] = '/admin/index.php';
        chdir(__DIR__ . '/admin');
        require 'index.php';
        return;
    }

    // If file doesn't exist, rewrite to admin/index.php (Front Controller pattern for admin)
    $_SERVER['SCRIPT_NAME'] = '/admin/index.php';
    chdir(__DIR__ . '/admin');
    require 'index.php';
    return;
}

// 3. Handle root routes
// If it's a file that exists, serve it
if ($uri !== '/' && file_exists($path) && !is_dir($path)) {
    return false;
}

// 4. Fallback to root index.php for everything else
$_SERVER['SCRIPT_NAME'] = '/index.php';
require_once __DIR__ . '/index.php';
