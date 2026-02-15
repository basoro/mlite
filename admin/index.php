<?php
/**
 * mLITE Admin Entry Point
 * Compatible with PHP 7.4 - 8.3+
 */

try {
    ob_start();
    header('Content-Type: text/html; charset=utf-8');

    // CORS Headers
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Api-Key, X-Requested-With, X-Username-Permission, X-Password-Permission");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        if (ob_get_level() > 0) ob_end_clean();
        http_response_code(200);
        exit;
    }
    
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

// Register API routes available regardless of login state
$core->router->set('api/login', function () use ($core) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $apiUser = $core->checkAuth('POST');
        $input = json_decode(file_get_contents('php://input'), true);
        $username = $input['username'] ?? $_POST['username'] ?? '';
        $password = $input['password'] ?? $_POST['password'] ?? '';

        if ($apiUser !== $username && $apiUser !== 'admin') {
             header('Content-Type: application/json');
             http_response_code(401);
             echo json_encode(['error' => 'API Key does not match the provided username']);
             exit;
        }

        $user = $core->db('mlite_users')->where('username', $username)->oneArray();
        if ($user && password_verify($password, $user['password'])) {
            $payload = [
                'iss' => url(),
                'sub' => $user['id'],
                'username' => $user['username']
            ];
            $token = \Systems\Lib\Jwt::encode($payload, JWT_SECRET);
            header('Content-Type: application/json');
            echo json_encode([
                'token' => $token,
                'fullname' => $user['fullname'] ?? $user['username']
            ]);
        } else {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
        }
        exit;
    }
});

$core->router->set('api/(:str)/(:str)(:any)', function ($module, $method, $params) use ($core) {
    $token = null;
    if (isset($_SERVER['HTTP_AUTHORIZATION']) && preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $m)) {
        $token = $m[1];
    } elseif (function_exists('getallheaders')) {
        $headers = getallheaders();
        $auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        if ($auth && preg_match('/Bearer\s(\S+)/', $auth, $m)) {
            $token = $m[1];
        }
    }
    $payload = $token ? \Systems\Lib\Jwt::verify($token, JWT_SECRET) : false;
    $loggedIn = $core->loginCheck();
    if (!$payload && !$loggedIn) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    $core->loadModules();
    $moduleObj = $core->module->{$module};
    if (!$moduleObj) {
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(['error' => 'Module not found']);
        exit;
    }
    $access = 'all';
    if ($loggedIn) {
        $access = $core->getUserInfo('access');
    } elseif ($payload) {
        $uid = $payload['sub'] ?? 0;
        $urow = $core->db('mlite_users')->where('id', $uid)->oneArray();
        $access = $urow['access'] ?? '';
    }
    if (!($access == 'all' || in_array($module, explode(',', (string)$access)))) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }
    $args = $params ? explode('/', trim($params, '/')) : [];
    $apiMethod = 'api'.ucfirst($method);
    $anyMethod = 'any'.ucfirst($method);
    $httpMethod = strtolower($_SERVER['REQUEST_METHOD']).ucfirst($method);
    if (method_exists($moduleObj, $apiMethod)) {
        $result = $moduleObj->{$apiMethod}(...array_values($args));
    } elseif (method_exists($moduleObj, $httpMethod)) {
        $result = $moduleObj->{$httpMethod}(...array_values($args));
    } elseif (method_exists($moduleObj, $anyMethod)) {
        $result = $moduleObj->{$anyMethod}(...array_values($args));
    } else {
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(['error' => 'Method not found']);
        exit;
    }
    header('Content-Type: application/json');
    if (is_array($result) || is_object($result)) {
        echo json_encode($result);
    } else {
        echo json_encode(['data' => $result]);
    }
    exit;
});

// Execute API route early if requested
$__path = $core->router->execute(true);

// daftar API yang TIDAK lewat API umum
$excludedApiPrefixes = [
    'api/manage',
    'api/notifikasi',
    'api/settingsapam',
    'api/paymentduitku',
    'api/settingskey',
    'api/saveSettingsApam',
    'api/saveSettingsKey',
];

$skipApi = false;
foreach ($excludedApiPrefixes as $prefix) {
    if (strpos($__path, $prefix) === 0) {
        $skipApi = true;
        break;
    }
}

if (!$skipApi && strpos($__path, 'api/') === 0) {
    $core->router->execute();
    return;
}


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
            if (!empty($_SESSION['mlite_force_change'])) {
                redirect(url([ADMIN, 'profil', 'ganti_pass']));
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
        $message = 'Please check database configuration and ensure MySQL server is running';
        if ($e->getCode() == 23000) {
            $message = $e->getMessage();
        }

        // Return JSON error response for AJAX requests
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Database connection failed',
            'message' => $message
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