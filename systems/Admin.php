<?php

namespace Systems;

class Admin extends Main
{
    private $assign = [];
    private $registerPage = [];
    public $module = null;

    public function __construct()
    {
        parent::__construct();

        $this->router->set('logout', function () {
            $this->logout();
        });
    }

    public function drawTheme($file)
    {
        if(isset($_SESSION['opensimrs_user']) && $_SESSION['opensimrs_user'] == 1) {
            $nama     = 'Administrator';
            $username = $this->db('lite_roles')->where('id', 1)->oneArray();
            $username = $username['username'];
            $this->assign['avatarURL']     = url('/plugins/users/img/default.png');
        } else {
            $username = $this->getUserInfo('username', null, true);
            $pegawai  = $this->db('pegawai')->where('nik', $username)->oneArray();
            $nama     = $pegawai['nama'];
            $this->assign['avatarURL']     = url('/plugins/users/img/default.png');
        }
        $access = $this->getUserInfo('access');

        $this->assign['nama']           = !empty($nama) ? $nama : $this->getUserInfo('nama');
        $this->assign['username']       = !empty($username) ? $username : $this->getUserInfo('username');

        $this->assign['notify']         = $this->getNotify();
        $this->assign['path']           = url();
        $this->assign['title']          = $this->getSettings('nama_instansi');
        $this->assign['logo']           = $this->getSettings('logo');
        $this->assign['version']        = $this->options->get('settings.version');

        $this->assign['update_access']  = ($access == 'all') || in_array('settings', explode(',', $access)) ? true : false;

        $this->assign['header']         = isset_or($this->appends['header'], ['']);
        $this->assign['footer']         = isset_or($this->appends['footer'], ['']);

        $this->tpl->set('opensimrs', $this->assign);
        echo $this->tpl->draw(THEMES.'/admin/'.$file, true);
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
                    $this->setNotify('failure', "[@{$method}] Url yang anda minta tidak tersedia");
                    $details['content'] = null;
                }

                $this->tpl->set('module', $details);
            } else {
                exit;
            }
        } else {
            exit;
        }
    }

    public function createNav($activeModule, $activeMethod)
    {
        $nav = [];
        $modules = $this->module->getArray();

        if ($this->getUserInfo('access') != 'all') {
            $modules = array_intersect_key($modules, array_fill_keys(explode(',', $this->getUserInfo('access')), null));
        }

        foreach ($modules as $dir => $module) {
            $subnav     = $this->getModuleNav($dir);
            $details    = $this->getModuleInfo($dir);

            if (isset($details['pages'])) {
                foreach ($details['pages'] as $pageName => $pageSlug) {
                    $this->registerPage($pageName, $pageSlug);
                }
            }
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
                        'url'       => url([ADMIN, $dir, $val]),
                        'active'    => $activeSubElement,
                    ];
                }

                if (count($subnavURLs) == 1) {
                    $moduleURL = $subnavURLs[0]['url'];
                    $subnavURLs = [];
                } else {
                    $moduleURL = '#';
                }

                $nav[] = [
                    'dir'       => $dir,
                    'name'      => $details['name'],
                    'icon'      => $details['icon'],
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

        $this->setNotify('failure', 'Rute yang anda minta tidak ada');
        return false;
    }

    public function login($username, $password, $remember_me = false)
    {
        // Check attempt
        $attempt = $this->db('lite_login_attempts')->where('ip', $_SERVER['REMOTE_ADDR'])->oneArray();

        // Create attempt if does not exist
        if (!$attempt) {
            $this->db('lite_login_attempts')->save(['ip' => $_SERVER['REMOTE_ADDR'], 'attempts' => 0]);
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

        $row_admin = $this->db()->pdo()->prepare("SELECT lite_roles.id as id, AES_DECRYPT(admin.usere,'nur') as username, AES_DECRYPT(admin.passworde,'windi') as password FROM lite_roles, admin WHERE lite_roles.username = AES_DECRYPT(admin.usere,'nur') AND admin.usere = AES_ENCRYPT(?,'nur')");
        $row_admin->execute([$username]);
        $row_admin = $row_admin->fetch();

        $row_user = $this->db()->pdo()->prepare("SELECT lite_roles.id as id, AES_DECRYPT(user.id_user,'nur') as username, AES_DECRYPT(user.password,'windi') as password FROM lite_roles, user WHERE lite_roles.username = AES_DECRYPT(user.id_user,'nur') AND user.id_user = AES_ENCRYPT(?,'nur')");
        $row_user->execute([$username]);
        $row_user = $row_user->fetch();

        if ($row_admin && trim($password) == $row_admin['password']) {
            // Reset fail attempts for this IP
            $this->db('lite_login_attempts')->where('ip', $_SERVER['REMOTE_ADDR'])->save(['attempts' => 0]);

            $_SESSION['opensimrs_user']       = $row_admin['id'];
            $_SESSION['opensimrs_username']   = $row_admin['username'];
            $_SESSION['token']                = bin2hex(openssl_random_pseudo_bytes(6));
            $_SESSION['userAgent']            = $_SERVER['HTTP_USER_AGENT'];
            $_SESSION['IPaddress']            = $_SERVER['REMOTE_ADDR'];

            if ($remember_me) {
                $token = str_gen(64, "1234567890qwertyuiop[]asdfghjkl;zxcvbnm,./");

                $this->db('lite_remember_me')->save(['user_id' => $row_admin['id'], 'token' => $token, 'expiry' => time()+60*60*24*30]);

                setcookie('opensimrs_remember', $row_admin['id'].':'.$token, time()+60*60*24*365, '/');
            }
            return true;
        } else if ($row_user && trim($password) == $row_user['password']) {
            // Reset fail attempts for this IP
            $this->db('lite_login_attempts')->where('ip', $_SERVER['REMOTE_ADDR'])->save(['attempts' => 0]);

            $_SESSION['opensimrs_user']       = $row_user['id'];
            $_SESSION['opensimrs_username']   = $row_user['username'];
            $_SESSION['token']                = bin2hex(openssl_random_pseudo_bytes(6));
            $_SESSION['userAgent']            = $_SERVER['HTTP_USER_AGENT'];
            $_SESSION['IPaddress']            = $_SERVER['REMOTE_ADDR'];

            if ($remember_me) {
                $token = str_gen(64, "1234567890qwertyuiop[]asdfghjkl;zxcvbnm,./");

                $this->db('lite_remember_me')->save(['user_id' => $row_user['id'], 'token' => $token, 'expiry' => time()+60*60*24*30]);

                setcookie('opensimrs_remember', $row_user['id'].':'.$token, time()+60*60*24*365, '/');
            }
            return true;
        } else {
            // Increase attempt
            $this->db('lite_login_attempts')->where('ip', $_SERVER['REMOTE_ADDR'])->save(['attempts' => $attempt['attempts']+1]);
            $attempt['attempts'] += 1;

            // ... and block if reached maximum attempts
            if ($attempt['attempts'] % 3 == 0) {
                $this->db('lite_login_attempts')->where('ip', $_SERVER['REMOTE_ADDR'])->save(['expires' => strtotime("+10 minutes")]);
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
        if (isset($_COOKIE['opensimrs_remember'])) {
            $token = explode(':', $_COOKIE['opensimrs_remember']);
            $this->db('lite_remember_me')->where('user_id', $token[0])->where('token', $token[1])->delete();
            setcookie('opensimrs_remember', null, -1, '/');
        }

        session_unset();
        session_destroy();
        redirect(url(ADMIN.'/'));
    }

    private function registerPage($name, $path)
    {
        $this->registerPage[] = ['id' => null, 'title' => $name, 'slug' => $path];
    }

    public function getRegisteredPages()
    {
        return $this->registerPage;
    }
}
