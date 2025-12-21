<?php
// mLITE Entry Point - Compatible with PHP 7.4 - 8.3+
header('Content-Type: text/html; charset=utf-8');

// Handle HTTPS forwarding from proxy/load balancer
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

define('BASE_DIR', __DIR__);
require_once('config.php');

// Error reporting configuration
if (DEV_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

require_once('systems/lib/Autoloader.php');

// Start output buffering with license verification callback
ob_start(base64_decode('XFN5c3RlbXNcTWFpbjo6dmVyaWZ5TGljZW5zZQ=='));

try {
    $core = new Systems\Site();
} catch (Throwable $e) {
    if (DEV_MODE) {
        throw $e;
    } else {
        error_log('mLITE Error: ' . $e->getMessage());
        http_response_code(500);
        // Clean error message for production
        $message = $e->getMessage();
        // Remove database name pattern `dbname`.
        $message = preg_replace('/`[^`]+`\./', '', $message);
        echo json_encode(['status' => 'error', 'message' => $message]);
        exit;
    }
}

ob_end_flush();
