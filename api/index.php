<?php
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

$core = new Systems\Api;

if(isset($_POST['mlite_username']) && isset($_POST['mlite_password'])) {
    $core->loginApi($_POST['mlite_username'], $_POST['mlite_password']);
} else {
    if($core->loginCheckApi()) {
        $core->loadModules();

        $core->router->set('(:str)/(:str)(:any)', function ($module, $method, $params) use ($core) {
            if ($params) {
                $core->loadModule($module, $method, explode('/', trim($params, '/')));
            } else {
                $core->loadModule($module, $method);
            }
        });
    
        $core->router->execute();
        $core->module->finishLoop();    
    }
}    

ob_end_flush();