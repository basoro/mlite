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
        $username = $this->getUserInfo('fullname', null, true);
        $access = $this->getUserInfo('access');

        $this->assign['tanggal']       = getDayIndonesia(date('Y-m-d')).', '.dateIndonesia(date('Y-m-d'));
        $this->assign['username']      = !empty($username) ? $username : $this->getUserInfo('username');
        $this->assign['notify']        = $this->getNotify();
        $this->assign['powered']       = 'Powered by <a href="https://basoro.org/">KhanzaLITE</a>';
        $this->assign['path']          = url();
        $this->assign['nama_instansi'] = $this->settings->get('settings.nama_instansi');
        $this->assign['logo'] = $this->settings->get('settings.logo');
        $this->assign['theme_admin'] = $this->settings->get('settings.theme_admin');
        $this->assign['version']       = $this->settings->get('settings.version');
        $this->assign['update_access'] = ($access == 'all') || in_array('settings', explode(',', $access)) ? true : false;

        $this->assign['header'] = isset_or($this->appends['header'], ['']);
        $this->assign['footer'] = isset_or($this->appends['footer'], ['']);

        $this->assign['pasien_access'] = ($access == 'all') || in_array('pasien', explode(',', $access)) ? true : false;
        $this->assign['module_pasien'] = $this->db('mlite_modules')->where('dir', 'pasien')->oneArray();
        $this->assign['igd_access'] = ($access == 'all') || in_array('igd', explode(',', $access)) ? true : false;
        $this->assign['module_igd'] = $this->db('mlite_modules')->where('dir', 'igd')->oneArray();
        $this->assign['rawat_jalan_access'] = ($access == 'all') || in_array('rawat_jalan', explode(',', $access)) ? true : false;
        $this->assign['module_rawat_jalan'] = $this->db('mlite_modules')->where('dir', 'rawat_jalan')->oneArray();
        $this->assign['rawat_inap_access'] = ($access == 'all') || in_array('rawat_inap', explode(',', $access)) ? true : false;
        $this->assign['module_rawat_inap'] = $this->db('mlite_modules')->where('dir', 'rawat_inap')->oneArray();

        $this->assign['dokter_igd_access'] = ($access == 'all') || in_array('dokter_igd', explode(',', $access)) ? true : false;
        $this->assign['dokter_ralan_access'] = ($access == 'all') || in_array('dokter_ralan', explode(',', $access)) ? true : false;
        $this->assign['dokter_ranap_access'] = ($access == 'all') || in_array('dokter_ranap', explode(',', $access)) ? true : false;
        $this->assign['cek_anjungan'] = $this->db('mlite_modules')->where('dir', 'anjungan')->oneArray();

        $this->assign['presensi'] = $this->db('mlite_modules')->where('dir', 'presensi')->oneArray();

        $this->tpl->set('mlite', $this->assign);
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
                    $this->setNotify('failure', "[@{$method}] Alamat yang anda diminta tidak ada.");
                    $details['content'] = null;
                }

                $details['dir'] = parseUrl()[0];

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
                    $moduleURL = $subnavURLs[0]['url'];
                }

                $nav[] = [
                    'dir'       => $dir,
                    'name'      => $details['name'],
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

        $this->setNotify('failure', "[@{$method}] Alamat yang anda diminta tidak ada.");
        return false;
    }

    public function login($username, $password, $remember_me = false)
    {
        // Check attempt
        $attempt = $this->db('mlite_login_attempts')->where('ip', $_SERVER['REMOTE_ADDR'])->oneArray();

        // Create attempt if does not exist
        if (!$attempt) {
            $this->db('mlite_login_attempts')->save(['ip' => $_SERVER['REMOTE_ADDR'], 'attempts' => 0]);
            $attempt = ['ip' => $_SERVER['REMOTE_ADDR'], 'attempts' => 0, 'expires' => 0];
        } else {
            $attempt['attempts'] = intval($attempt['attempts']);
            $attempt['expires'] = intval($attempt['expires']);
        }

        // Is IP blocked?
        /*if ((time() - $attempt['expires']) < 0) {
            $this->setNotify('failure', sprintf('Batas maksimum login tercapai. Tunggu %s menit untuk coba lagi.', ceil(($attempt['expires']-time())/60)));
            return false;
        }*/

        $row = $this->db('mlite_users')->where('username', $username)->oneArray();

        if ($row && password_verify(trim($password), $row['password'])) {
            // Reset fail attempts for this IP
            $this->db('mlite_login_attempts')->where('ip', $_SERVER['REMOTE_ADDR'])->save(['attempts' => 0]);

            $_SESSION['mlite_user']= $row['id'];
            $_SESSION['token']      = bin2hex(openssl_random_pseudo_bytes(6));
            $_SESSION['userAgent']  = $_SERVER['HTTP_USER_AGENT'];
            $_SESSION['IPaddress']  = $_SERVER['REMOTE_ADDR'];

            if ($remember_me) {
                $token = str_gen(64, "1234567890qwertyuiop[]asdfghjkl;zxcvbnm,./");

                $this->db('mlite_remember_me')->save(['user_id' => $row['id'], 'token' => $token, 'expiry' => time()+60*60*24*30]);

                setcookie('mlite_remember', $row['id'].':'.$token, time()+60*60*24*365, '/');
            }
            return true;
        } else {
            // Increase attempt
            $this->db('mlite_login_attempts')->where('ip', $_SERVER['REMOTE_ADDR'])->save(['attempts' => $attempt['attempts']+1]);
            $attempt['attempts'] += 1;

            // ... and block if reached maximum attempts
            if ($attempt['attempts'] % 3 == 0) {
                $this->db('mlite_login_attempts')->where('ip', $_SERVER['REMOTE_ADDR'])->save(['expires' => strtotime("+10 minutes")]);
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
            $this->db('mlite_remember_me')->where('user_id', $token[0])->where('token', $token[1])->delete();
            setcookie('mlite_remember', null, -1, '/');
        }

        session_unset();
        session_destroy();
        redirect(url());
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
