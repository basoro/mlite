<?php
ob_start();
header('Content-Type:text/html;charset=utf-8');
if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'){
            $_SERVER['HTTPS']='on';
}
define('BASE_DIR', __DIR__.'/..');
require_once('../config.php');

if (DEV_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
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
            if (count($arrayURL = parseURL()) > 1) {
                $url = array_merge([ADMIN], $arrayURL);
                redirect(url($url));
            }
            redirect(url([ADMIN, 'dashboard', 'main']));
        }
    }
    $core->drawTheme('login.html');
}

ob_end_flush();
