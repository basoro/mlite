<?php
// mLITE Entry Point - Compatible with PHP 7.4 - 8.3+
header('Content-Type: text/html; charset=utf-8');

// Handle HTTPS forwarding from proxy/load balancer
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

if (!defined('BASE_DIR')) {
    define('BASE_DIR', __DIR__);
}
require_once('config.php');

if (!file_exists(BASE_DIR . '/.env')) {
    header('Location: /install.php');
    exit;
}

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
    error_log('mLITE Error: ' . $e->getMessage());
    http_response_code(500);
    $message = 'System Error. Please contact administrator.';
    
    // Auto-rescue untuk lingkungan PaaS (Papuyu/Docker) jika .env terbuat tapi database belum di-import
    $errMsg = $e->getMessage();
    if (strpos($errMsg, 'Base table or view not found') !== false || strpos($errMsg, 'no such table') !== false || strpos($errMsg, 'Unknown database') !== false) {
        header('Location: /install.php');
        exit;
    }
    
    $response = ['status' => 'error', 'message' => $message];
    
    if (DEV_MODE) {
        $devMessage = preg_replace('/`[^`]+`\./', '', $e->getMessage());
        $response['message'] .= ' Detail: ' . $devMessage;
        $response['debug'] = [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => explode("\n", $e->getTraceAsString())
        ];
    }
    
    echo json_encode($response);
    exit;
}

ob_end_flush();
