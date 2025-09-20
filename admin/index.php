<?php
/**
 * mLITE Admin Entry Point
 * Compatible with PHP 7.4 - 8.3+
 */

try {
    ob_start();
    header('Content-Type: text/html; charset=utf-8');
    
    // Handle HTTPS forwarding with strict comparison
    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        $_SERVER['HTTPS'] = 'on';
    }
define('BASE_DIR', __DIR__.'/..');
require_once('../config.php');

    if (DEV_MODE) {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
    } else {
        error_reporting(0);
        ini_set('display_errors', '0');
    }

require_once('../systems/lib/Autoloader.php');
//ob_start(base64_decode('XFN5c3RlbXNcTWFpbjo6dmVyaWZ5TGljZW5zZQ=='));

$core = new Systems\Admin;

if ($core->loginCheck()) {
    $core->loadModules();

    $core->router->set('(:str)/(:str)(:any)', function ($module, $method, $params) use ($core) {
        $core->createNav($module, $method);
        if ($params) {
            $core->loadModule($module, $method, explode('/', trim($params, '/')));
        } else {
            $core->loadModule($module, $method);
        }
    });

    $core->router->execute();
    $core->drawTheme('index.html');
    $core->module->finishLoop();
} else {
    if (isset($_POST['login'])) {
        if ($core->login($_POST['username'], $_POST['password'], isset($_POST['remember_me']))) {
            $arrayURL = parseURL();
            if ($arrayURL && count($arrayURL) > 1) {
                $url = array_merge([ADMIN], $arrayURL);
                redirect(url($url));
            }
            if(MULTI_APP) {
                if(!empty(MULTI_APP_REDIRECT)) {
                    redirect(url([ADMIN, MULTI_APP_REDIRECT, 'main']));
                } else {
                    redirect(url([ADMIN, 'dashboard', 'main']));
                }
            } else {
                redirect(url([ADMIN, 'dashboard', 'main']));
            }
        }
    }
    if(MULTI_APP) {
      if(!empty(MULTI_APP_REDIRECT)) {
        echo $core->tpl->draw(MODULES.'/'.MULTI_APP_REDIRECT.'/view/login.html', true);
      } else {
        $core->drawTheme('login.html');
      }
    } else {
      $core->drawTheme('login.html');
    }

}

} catch (Throwable $e) {
    // Check if this is an AJAX request expecting JSON
    $isAjaxRequest = (
        (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') ||
        (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
        (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
    );
    
    if ($isAjaxRequest) {
        // Return JSON error response for AJAX requests
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Database connection failed',
            'message' => 'Please check database configuration and ensure MySQL server is running',
            'details' => DEV_MODE ? $e->getMessage() : 'System error occurred'
        ]);
    } else {
        // Return HTML error for regular requests
        if (DEV_MODE) {
            echo '<h1>Error</h1><pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        } else {
            echo '<h1>System Error</h1><p>Please contact administrator.</p>';
        }
    }
}

// Safe buffer handling for PHP 8+
if (ob_get_level() > 0) {
    ob_end_flush();
}
