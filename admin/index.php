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
        if (ob_get_level() > 0)
            ob_end_clean();
        http_response_code(200);
        exit;
    }

    // Handle HTTPS forwarding with strict comparison
    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        $_SERVER['HTTPS'] = 'on';
    }
    if (!defined('BASE_DIR')) {
        define('BASE_DIR', __DIR__ . '/..');
    }
    require_once('../config.php');

    // if (!file_exists(BASE_DIR . '/.env')) {
    //     header('Location: /install.php');
    //     exit;
    // }

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
                    'fullname' => htmlspecialchars($user['fullname'] ?? $user['username'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
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
        // 1. Verifikasi kredensial aplikasi klien via API Key atau Sesi
        $apiUser = $core->checkAuth(strtoupper($_SERVER['REQUEST_METHOD']));

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
        } else {
            $urow = $core->db('mlite_users')->where('username', $apiUser)->oneArray();
            $access = $urow['access'] ?? '';
        }
        if (!($access == 'all' || in_array($module, explode(',', (string) $access)))) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }
        $args = $params ? explode('/', trim($params, '/')) : [];
        $apiMethod = 'api' . ucfirst($method);
        $anyMethod = 'any' . ucfirst($method);
        $httpMethod = strtolower($_SERVER['REQUEST_METHOD']) . ucfirst($method);
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
        if (isset($_GET['cancel_otp'])) {
            unset($_SESSION['mlite_otp_pending']);
            unset($_SESSION['mlite_otp_user_id']);
            unset($_SESSION['mlite_otp_remember_me']);
            redirect(url([ADMIN]));
        }

        if (isset($_POST['verify_otp']) && isset($_SESSION['mlite_otp_pending'])) {
            $otp_code = trim($_POST['otp_code'] ?? '');
            $uid = $_SESSION['mlite_otp_user_id'] ?? 0;
            $userRow = $core->db('mlite_users')->where('id', $uid)->oneArray();
            
            if ($userRow && $userRow['otp_code'] === $otp_code && strtotime($userRow['otp_expires']) >= time()) {
                // OTP Valid
                unset($_SESSION['mlite_otp_pending']);
                unset($_SESSION['mlite_otp_user_id']);
                
                $_SESSION['mlite_user']= $userRow['id'];
                $_SESSION['token']      = bin2hex(openssl_random_pseudo_bytes(6));
                $_SESSION['userAgent']  = $_SERVER['HTTP_USER_AGENT'];
                $_SESSION['IPaddress']  = $_SERVER['REMOTE_ADDR'];

                if (isset($_SESSION['mlite_otp_remember_me'])) {
                    $token = str_gen(64, "1234567890qwertyuiop[]asdfghjkl;zxcvbnm,./");
                    $core->db('mlite_remember_me')->save(['user_id' => $userRow['id'], 'token' => $token, 'expiry' => time()+60*60*24*30]);
                    setcookie('mlite_remember', $userRow['id'].':'.$token, time()+60*60*24*365, '/');
                    unset($_SESSION['mlite_otp_remember_me']);
                }

                $arrayURL = parseURL();
                if ($arrayURL && count($arrayURL) > 1) {
                    $url = array_merge([ADMIN], $arrayURL);
                    redirect(url($url));
                }
                if (!empty($_SESSION['mlite_force_change'])) {
                    redirect(url([ADMIN, 'profil', 'ganti_pass']));
                }
                if (MULTI_APP) {
                    if (!empty(MULTI_APP_REDIRECT)) {
                        redirect(url([ADMIN, MULTI_APP_REDIRECT, 'main']));
                    } else {
                        redirect(url([ADMIN, 'dashboard', 'main']));
                    }
                } else {
                    redirect(url([ADMIN, 'dashboard', 'main']));
                }
            } else {
                $core->setNotify('failure', 'Kode OTP tidak valid atau telah kedaluwarsa.');
            }
        }

        if (isset($_POST['login'])) {
            if ($core->login($_POST['username'], $_POST['password'], isset($_POST['remember_me']))) {
                if (isset($_SESSION['mlite_otp_pending'])) {
                    $core->setNotify('success', 'Harap masukkan 6-digit kode OTP yang telah dikirim ke WhatsApp Anda.');
                } else {
                    $arrayURL = parseURL();
                    if ($arrayURL && count($arrayURL) > 1) {
                        $url = array_merge([ADMIN], $arrayURL);
                        redirect(url($url));
                    }
                    if (!empty($_SESSION['mlite_force_change'])) {
                        redirect(url([ADMIN, 'profil', 'ganti_pass']));
                    }
                    if (MULTI_APP) {
                        if (!empty(MULTI_APP_REDIRECT)) {
                            redirect(url([ADMIN, MULTI_APP_REDIRECT, 'main']));
                        } else {
                            redirect(url([ADMIN, 'dashboard', 'main']));
                        }
                    } else {
                        redirect(url([ADMIN, 'dashboard', 'main']));
                    }
                }
            }
        }
        if (MULTI_APP) {
            if (!empty(MULTI_APP_REDIRECT)) {
                echo $core->tpl->draw(MODULES . '/' . MULTI_APP_REDIRECT . '/view/login.html', true);
            } else {
                $core->drawTheme('login.html');
            }
        } else {
            $core->drawTheme('login.html');
        }

    }

} catch (Throwable $e) {
    // Tangkap khusus error ketidakadaan tabel untuk tidak di redirect melainkan dimunculkan sebagai notifikasi
    $errMsg = $e->getMessage();
    $isMissingTable = (strpos($errMsg, 'Base table or view not found') !== false || strpos($errMsg, 'no such table') !== false || strpos($errMsg, 'Unknown database') !== false);

    // Check if this is an AJAX request expecting JSON
    $isAjaxRequest = (
        (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') ||
        (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
        (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
    );

    if ($isAjaxRequest) {
        $error = 'Database connection failed';
        $message = 'Please check database configuration and ensure MySQL server is running';
        $debug = [];

        if ($isMissingTable) {
            $error = 'Table / Database Not Found';
            $message = $errMsg;
        } elseif ($e->getCode() == 23000) {
            $message = $e->getMessage();
        }

        if (defined('DEV_MODE') && DEV_MODE) {
            $error = 'System Error';
            $message = $e->getMessage();
            $debug = [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => explode("\n", $e->getTraceAsString())
            ];
        }

        // Return HTML error response for AJAX requests (e.g. for modal content or alerts)
        header('Content-Type: text/html');
        $debug_html = '';
        if (!empty($debug)) {
            $debug_html .= '<hr>';
            $debug_html .= '<small><strong>File:</strong> ' . htmlspecialchars($debug['file']) . ' on line ' . $debug['line'] . '</small>';
            $debug_html .= '<div style="max-height: 150px; overflow: auto; margin-top: 5px; background: #f8f9fa; padding: 5px; border: 1px solid #ddd;">';
            $debug_html .= '<pre style="font-size: 10px; margin: 0;">' . htmlspecialchars(implode("\n", $debug['trace'])) . '</pre>';
            $debug_html .= '</div>';
        }

        echo '<div class="alert alert-danger alert-dismissible fade in" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4>' . htmlspecialchars($error) . '</h4>
            <p>' . htmlspecialchars($message) . '</p>
            ' . $debug_html . '
        </div>';
    } else {

        // Return HTML error for regular requests
        $title = 'System Error';
        $content = '<p>Please contact administrator.</p>';
        $debug_info = '';

        if ($isMissingTable) {
            $title = 'Database Error';
            $content = '<div class="alert alert-warning" style="margin-top: 15px;"><strong>Warning:</strong><br>' . htmlspecialchars($errMsg) . '</div>';
            $content .= '<p>Sepertinya tabel yang hendak diakses tidak tersedia di database. Anda mungkin tidak menginstal plugin secara menyeluruh, atau modul belum menyediakan tabel tersebut.</p>';
        }

        if (defined('DEV_MODE') && DEV_MODE) {
            $content .= '<div class="alert alert-danger" style="margin-top: 15px;"><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
            $debug_info = '<div class="debug-box">';
            $debug_info .= '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ' on line ' . $e->getLine() . '</p>';
            $debug_info .= '<h4>Stack Trace:</h4>';
            $debug_info .= '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
            $debug_info .= '</div>';
        }

        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>' . $title . '</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f8f9fa; color: #333; margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
                .container { background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 800px; width: 90%; }
                h1 { color: #dc3545; margin-top: 0; font-size: 24px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
                .alert { padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; }
                .alert-danger { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
                .debug-box { background: #f8f9fa; padding: 15px; border-radius: 4px; border: 1px solid #ddd; margin-top: 20px; font-size: 14px; overflow-x: auto; }
                pre { background: #2d2d2d; color: #ccc; padding: 15px; border-radius: 4px; overflow: auto; font-size: 12px; line-height: 1.5; }
                p { line-height: 1.6; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>' . $title . '</h1>
                ' . $content . '
                ' . $debug_info . '
                <div style="margin-top: 20px; text-align: center; color: #666; font-size: 12px;">
                    <p>mLITE Healthcare System &copy; ' . date('Y') . '</p>
                </div>
            </div>
        </body>
        </html>';
    }
}

// Safe buffer handling for PHP 8+
if (ob_get_level() > 0) {
    ob_end_flush();
}