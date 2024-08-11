<?php
namespace Systems;
use Systems\Lib\JwtManager;

class Api extends Main
{
    public function __construct()
    {
        parent::__construct();
        $this->loadModules();

        $return = $this->router->execute();

        if (strpos(get_headers_list('Content-Type'), 'text/html') !== false) {
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Methods: GET,POST,DELETE");
            header('Access-Control-Allow-Credentials: true');
            header("Content-type: application/json");
            header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Methods,Access-Control-Allow-Origin, Access-Control-Allow-Credentials, Authorization, X-Requested-With, X-Api-Key, X-Access-Token, apiKey, accessToken");
            if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
                header('HTTP/1.1 200 OK');
                exit();
            }            
        }

        echo $return;

        $this->module->finishLoop();
        $requestHeaders = apache_request_headers();
        $this->requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));

    }

    public function loginCheckApi()
    {
        $secretKey = trim(isset_or($this->requestHeaders['X-Api-Key'], ''));
        $mlite_api_key = $this->db->get('mlite_api_key', '*', ['api_key' => $secretKey]);

        $whitelist = explode(',', isset_or($mlite_api_key['ip_range'], ''));
        
        if (!$this->db->has('mlite_api_key', ['api_key' => $secretKey])) {
            http_response_code(403);
            $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Credensial API Key tidak sesuai. Otorisasi ditolak!'
            );
            echo json_encode($data);
            return false;
        }

        if (!isAllowed($_SERVER['REMOTE_ADDR'], $whitelist)) {
            http_response_code(403);
            $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Credensial API IP Address tidak sesuai. Otorisasi ditolak!'
            );
            echo json_encode($data);
            return false;
        }

        // Create an instance of JwtManager
        $jwtManager = new JwtManager($secretKey);
        // Validate and decode the JWT
        if (!$jwtManager->validateToken(trim($this->requestHeaders['X-Access-Token']))) {
            http_response_code(403);
            $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Validasi token untuk akses gagal. Otorisasi ditolak!'
            );
            echo json_encode($data);
            return false;
        }
        return true;
    }   
    
    public function loginCheckApiID()
    {
        $secretKey = trim(isset_or($this->requestHeaders['X-Api-Key'], ''));
        $mlite_api_key = $this->db->get('mlite_api_key', '*', ['api_key' => $secretKey]);

        $whitelist = explode(',', isset_or($mlite_api_key['ip_range'], ''));
        
        if (!$this->db->has('mlite_api_key', ['api_key' => $secretKey])) {
            http_response_code(403);
            $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Credensial API Key tidak sesuai. Otorisasi ditolak!'
            );
            echo json_encode($data);
            return false;
        }

        if (!isAllowed($_SERVER['REMOTE_ADDR'], $whitelist)) {
            http_response_code(403);
            $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Credensial API IP Address tidak sesuai. Otorisasi ditolak!'
            );
            echo json_encode($data);
            return false;
        }

        // Create an instance of JwtManager
        $jwtManager = new JwtManager($secretKey);
        // Validate and decode the JWT
        if (!$jwtManager->validateToken(trim($this->requestHeaders['X-Access-Token']))) {
            http_response_code(403);
            $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Validasi token untuk akses gagal. Otorisasi ditolak!'
            );
            echo json_encode($data);
            return false;
        }
        return true;
    }    

    public function loginApi($username, $password)
    {
        $mlite_users = $this->dbmlite->get('mlite_users', '*', ['username' => $username]);
        $mlite_api_key = $this->db->get('mlite_api_key', '*', ['username' => $username]);
        $secretKey = isset_or($mlite_api_key['api_key']);

        if ($mlite_users && password_verify(trim($password), isset_or($mlite_users['password']))) {
            // Create an instance of JwtManager
            $jwtManager = new JwtManager($secretKey);
            // Create a JWT
            $payload = [
                "user_id" => $mlite_users['id'],
                "username" => $username,
                "exp" => strtotime($mlite_api_key['exp_time']), // Token expiration
            ];
            $jwt = $jwtManager->createToken($payload);
            http_response_code(201);
            $data = array(
                'code' => '201', 
                'status' => 'success', 
                'msg' => array(
                    'key' => $secretKey, 
                    'token' => $jwt, 
                    'user_type' => $mlite_users['role']
                )
            );
            echo json_encode($data);            
            return true;
        } else {
            http_response_code(403);
            $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Username atau password salah. Otorisasi ditolak!'
            );
            echo json_encode($data);
            return false;
        }
    }

    public function loadModule($name, $method, $params = [])
    {
        $row = $this->module->{$name};

        if ($row && ($details = $this->getModuleInfo($name))) {

            $secretKey = trim(isset_or($this->requestHeaders['X-Api-Key'], ''));
            $jwtManager = new JwtManager($secretKey);
            $jwt = $jwtManager->decodeToken(trim(isset_or($this->requestHeaders['X-Access-Token'], '..')));
            $_SESSION['mlite_user'] = isset_or($jwt['user_id']);

            $mlite_api_key = $this->db->get('mlite_api_key', '*', ['api_key' => $secretKey]);

            $access = $this->getUserInfo('access');

            if (($access == 'all') || in_array($name, explode(',', isset_or($access, '')))) {
                $method = strtolower($_SERVER['REQUEST_METHOD']).ucfirst($method);

                if (in_array($_SERVER['REQUEST_METHOD'], explode(',', isset_or($mlite_api_key['method'], ''))) == false) {
                    http_response_code(405);
                    $data = array(
                        'code' => '405', 
                        'status' => 'error', 
                        'msg' => 'Not Allowed by Credential API Key.'
                    );
                    echo json_encode($data);
                } elseif (method_exists($this->module->{$name}, $method)) {
                    call_user_func_array([$this->module->{$name}, $method], array_values($params));
                } else {
                    http_response_code(405);
                    $data = array(
                        'code' => '405', 
                        'status' => 'error', 
                        'msg' => 'Method Not Allowed.'
                    );
                    echo json_encode($data);
                }
            } else {
                http_response_code(403);
                $data = array(
                    'code' => '403', 
                    'status' => 'error', 
                    'msg' => 'Forbidden.'
                );
                echo json_encode($data);
            }
        } else {
            http_response_code(404);
            $data = array(
                'code' => '404', 
                'status' => 'error', 
                'msg' => 'Module not found'
            );
            echo json_encode($data);
        }

    }

    public function getModuleInfo($dir)
    {
        $file = MODULES.'/'.$dir.'/Info.php';
        $core = $this;

        if (file_exists($file)) {
            return include($file);
        } else {
            return false;
        }
    }

}

