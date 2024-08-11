<?php
namespace Systems;

class Admin extends Main
{
    private $assign = [];
    public  $module = null;

    public function __construct()
    {
        parent::__construct();

        $this->router->set('logout', function () {
            $this->logout();
        });

    }

    public function drawTheme($file)
    {

        $username = '';
        $access = '';
        
        $id = isset_or($_SESSION['mlite_user'], '1');
  
        if($this->dbmlite->has('mlite_users', '*', ['id' => $id])) {
          $username = $this->getUserInfo('fullname', $id, true);
          $access = $this->getUserInfo('access');
          $this->assign['fullname']     = !empty($username) ? $username : $this->getUserInfo('username');
          $this->assign['role']         = $this->getUserInfo('role', $id, true);
          if($this->getUserInfo('avatar', $id, true)) {
            $this->assign['avatar']       = $this->getUserInfo('avatar', $id, true);
          } else {
            $this->assign['avatar']       = 'plugins/mlite_users/img/default.png';
          }
        }

        $this->assign['notify']         = $this->getNotify();
        $this->assign['nama_instansi']  = $this->settings->get('settings.nama_instansi');
        $this->assign['logo']           = $this->settings->get('settings.logo');

        $this->assign['header']         = isset_or($this->appends['header'], ['']);
        $this->assign['footer']         = isset_or($this->appends['footer'], ['']);

        $this->tpl->set('mlite', $this->assign);
        echo $this->tpl->draw(THEMES.'/'.$file, true);
    }

    public function loadModule($name, $method, $params = [])
    {
        $row = $this->module->{$name};

        if ($row && ($details = $this->getModuleInfo($name))) {
            if (($this->getUserInfo('access') == 'all') || in_array($name, explode(',', $this->getUserInfo('access')))) {
                $anyMethod = 'any'.ucfirst($method);
                $method = strtolower($_SERVER['REQUEST_METHOD']).ucfirst($method);

                if (method_exists($this->module->{$name}, $method)) {
                    $details['content'] = call_user_func_array([$this->module->{$name}, $method], array_values($params));
                } elseif (method_exists($this->module->{$name}, $anyMethod)) {
                    $details['content'] = call_user_func_array([$this->module->{$name}, $anyMethod], array_values($params));
                } else {
                    http_response_code(404);
                    $this->setNotify('failure', "[@{$method}] Alamat yang Anda minta tidak ada.");
                    $details['content'] = null;
                }

                $details['dir'] = parseUrl()[0];

                $this->tpl->set('module', $details);
            } else {
                $this->tpl->set('module', '');
                echo $this->tpl->draw(THEMES.'/403.html', true);
                exit();
            }
        } else {
            $this->tpl->set('module', '');
            echo $this->tpl->draw(THEMES.'/404.html', true);
            exit;
        }

    }

    public function createNav($activeModule, $activeMethod)
    {
        $nav = [];
        $modules = $this->module->getArray();

        if($_SESSION['mlite_user'] == '') {
          $id = 1;
        } else {
          $id = $_SESSION['mlite_user'];
        }

        if ($this->getUserInfo('access', $id, $refresh = false) != 'all') {
            $modules = array_intersect_key($modules, array_fill_keys(explode(',', $this->getUserInfo('access')), null));
        }

        foreach ($modules as $dir => $module) {
            $subnav     = $this->getModuleNav($dir);
            $details    = $this->getModuleInfo($dir);

            if ($subnav) {
                if ($activeModule == $dir) {
                    $activeElement = 'active';
                } else {
                    $activeElement = null;
                }

                $subnavURLs = [];
                foreach ($subnav as $key => $val) {
                    if (($activeModule == $dir) && isset($activeMethod) && ($activeMethod == $val)) {
                        $activeSubElement = 'active';
                    } else {
                        $activeSubElement = null;
                    }

                    $subnavURLs[] = [
                        'name'      => $key,
                        'url'       => url([$dir, $val]),
                        'active'    => $activeSubElement,
                    ];
                }

                if (count($subnavURLs) == 1) {
                    $moduleURL = $subnavURLs[0]['url'];
                    $subnavURLs = [];
                } else {
                    $moduleURL = $subnavURLs[0]['url'];
                }
                $hidden = $this->dbmlite->get('mlite_disabled_menu', 'hidden', ['module' => $dir, 'user' => $this->dbmlite->get('mlite_users', 'username', ['id' => $id])]);
                $nav[] = [
                    'dir'       => $dir,
                    'name'      => $details['name'],
                    'hidden'    => isset_or($hidden, 'false'),
                    'icon'      => $details['icon'],
                    'desc'      => $details['description'],
                    'url'       => $moduleURL,
                    'active'    => $activeElement,
                    'subnav'    => $subnavURLs,
                ];
            }
        }
        $this->assign['nav'] = $nav;
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

    public function getModuleNav($dir)
    {
        if ($this->module->has($dir)) {
            return $this->module->{$dir}->navigation();
        }

        return false;
    }

    public function getModuleMethod($name, $method, $params = [])
    {
        if (method_exists($this->module->{$name}, $method)) {
            return call_user_func_array([$this->module->{$name}, $method], array_values($params));
        }

        $this->setNotify('failure', "[@{$method}] Alamat yang Anda minta tidak ada.");
        return false;
    }

    public function login($username, $password, $remember_me = false)
    {
        // Check attempt
        $attempt = $this->dbmlite->get('mlite_login_attempts', ['attempts', 'expires'],[ 'ip' => $_SERVER['REMOTE_ADDR']]);

        // Create attempt if does not exist
        if (!$attempt) {
            $this->dbmlite->insert("mlite_login_attempts", [
                "ip" => $_SERVER['REMOTE_ADDR'],
                "attempts" => 0
            ]);            
            $attempt = ['ip' => $_SERVER['REMOTE_ADDR'], 'attempts' => 0, 'expires' => 0];
        } else {
            $attempt['attempts'] = intval($attempt['attempts']);
            $attempt['expires'] = intval($attempt['expires']);
        }

        // Is IP blocked?
        if ((time() - $attempt['expires']) < 0) {
            $this->setNotify('failure', sprintf('Batas maksimum login tercapai. Tunggu %s menit untuk coba lagi.', ceil(($attempt['expires']-time())/60)));
            return false;
        }

        $row = $this->dbmlite->get('mlite_users', '*', ['username' => $username]);

        if ($row && password_verify(trim($password), $row['password'])) {
            // Reset fail attempts for this IP
            $this->dbmlite->insert("mlite_login_attempts", [
                "ip" => $_SERVER['REMOTE_ADDR'],
                "attempts" => 0
            ]);            

            $_SESSION['mlite_user']= $row['id'];
            $_SESSION['token']      = bin2hex(openssl_random_pseudo_bytes(6));
            $_SESSION['userAgent']  = $_SERVER['HTTP_USER_AGENT'];
            $_SESSION['IPaddress']  = $_SERVER['REMOTE_ADDR'];

            if ($remember_me) {
                $token = str_gen(64, "1234567890qwertyuiop[]asdfghjkl;zxcvbnm,./");

                $this->dbmlite->insert("mlite_remember_me", [
                    "user_id" =>  $row['id'],
                    "token" => $token, 
                    "expiry" => time()+60*60*24*30
                ]);            
    
                setcookie('mlite_remember', $row['id'].':'.$token, time()+60*60*24*365, '/');
            }
            return true;
        } else {
            // Increase attempt
            $this->dbmlite->insert("mlite_login_attempts", [
                "ip" => $_SERVER['REMOTE_ADDR'],
                "attempts" => $attempt['attempts']+1
            ]);            

            $attempt['attempts'] += 1;

            // ... and block if reached maximum attempts
            if ($attempt['attempts'] % 3 == 0) {

                $this->dbmlite->update("mlite_login_attempts", [
                    "expires" => strtotime('+10 minutes')
                ],
                [
                    "ip" => $_SERVER['REMOTE_ADDR']        
                ]);            
    
                $attempt['expires'] = strtotime("+10 minutes");

                $this->setNotify('failure', sprintf('Batas maksimum login tercapai. Tunggu %s menit untuk coba lagi.', ceil(($attempt['expires']-time())/60)));
            } else {
                $this->setNotify('failure', 'Username atau password salah!');
            }

            return false;
        }
    }

    private function logout()
    {
        $_SESSION = [];

        // Delete remember_me token from database and cookie
        if (isset($_COOKIE['mlite_remember'])) {
            $token = explode(':', $_COOKIE['mlite_remember']);
            $this->dbmlite->delete('mlite_remember_me', ['AND' => ['user_id' => $token[0], 'token' => $token[1]]]);
            setcookie('mlite_remember', null, -1, '/');
        }

        session_unset();
        session_destroy();
        redirect(url());
    }

}
