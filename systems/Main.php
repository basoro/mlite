<?php

namespace Systems;

use Systems\Lib\QueryWrapper;
use Systems\Lib\Templates;
use Systems\Lib\Router;
use Systems\Lib\Settings;
use Systems\Lib\License;


abstract class Main
{

    public $tpl;
    public $router;
    public $settings;
    public $appends = [];
    public $module = null;

    protected static $settingsCache = [];
    protected static $userCache = [];

    public function __construct()
    {
        $this->setSession();

        //$dbFile = BASE_DIR.'/systems/data/database.sdb';

        QueryWrapper::connect("mysql:host=".DBHOST.";port=".DBPORT.";dbname=".DBNAME."", DBUSER, DBPASS);

        $check_db = $this->db()->pdo()->query("SHOW TABLES LIKE 'mlite_modules'");
        $check_db->execute();
        $check_db = $check_db->fetch();

        if(empty($check_db)) {
          $this->freshInstall();
        }

        if (!is_dir(UPLOADS)) {
            mkdir(UPLOADS, 0777);
        }

        //if (file_exists($dbFile)) {
        //    QueryWrapper::connect("sqlite:{$dbFile}");
        //} else {
        //    $this->freshInstall($dbFile);
        //}

        $this->settings = new Settings($this);
        date_default_timezone_set($this->settings->get('settings.timezone'));

        $this->tpl = new Templates($this);
        $this->router = new Router;

        $this->append(base64_decode('PG1ldGEgbmFtZT0iZ2VuZXJhdG9yIiBjb250ZW50PSJCYXNvcm8uSUQiIC8+'), 'header');
    }

    public function db($table = null)
    {
        return new QueryWrapper($table);
    }

    public function getSettings($module = 'settings', $field = null, $refresh = false)
    {
        if ($refresh) {
            $this->settings->reload();
        }

        return $this->settings->get($module, $field);
    }

    public function setSettings($module, $field, $value)
    {
        return $this->settings->set($module, $field, $value);
    }

    private function setSession()
    {
        ini_set('session.use_only_cookies', 1);
        session_name('mlite');
        session_set_cookie_params(0, (mlite_dir() === '/' ? '/' : mlite_dir().'/'));
        session_start();
    }

    public function setNotify($type, $text, $args = null)
    {
        $variables = [];
        $numargs = func_num_args();
        $arguments = func_get_args();

        if ($numargs > 1) {
            for ($i = 1; $i < $numargs; $i++) {
                $variables[] = $arguments[$i];
            }
            $text = call_user_func_array('sprintf', $variables);
            $_SESSION[$arguments[0]] = $text;
        }
    }

    public function getNotify()
    {
        if (isset($_SESSION['failure'])) {
            $result = ['text' => $_SESSION['failure'], 'type' => 'danger'];
            unset($_SESSION['failure']);
            return $result;
        } elseif (isset($_SESSION['success'])) {
            $result = ['text' => $_SESSION['success'], 'type' => 'success'];
            unset($_SESSION['success']);
            return $result;
        } else {
            return false;
        }
    }

    public function addCSS($path)
    {
        $this->appends['header'][] = "<link rel=\"stylesheet\" href=\"$path\">\n";
    }

    public function addJS($path, $location = 'header')
    {
        $this->appends[$location][] = "<script src=\"$path\"></script>\n";
    }

    public function append($string, $location)
    {
        $this->appends[$location][] = $string."\n";
    }

    public static function verifyLicense($buffer)
    {
        $core = isset_or($GLOBALS['core'], false);
        if (!$core) {
            return $buffer;
        }
        $checkBuffer = preg_replace('/<!--(.|\s)*?-->/', '', $buffer);
        $isHTML = strpos(get_headers_list('Content-Type'), 'text/html') !== false;
        $hasBacklink = strpos($checkBuffer, base64_decode('UG93ZXJlZCBieSA8YSBocmVmPSJodHRwczovL2Jhc29yby5vcmcvIj5LaGFuemFMSVRFPC9hPg==')) !== false;
        $hasHeader = get_headers_list('X-Created-By') === 'Basoro.ID <basoro.org>';
        $license = License::verify($core->settings->get('settings.license'));
        if (($license == License::FREE) && $isHTML && (!$hasBacklink || !$hasHeader)) {
            return '<strong>License system</strong><br />The return link has been deleted or modified.';
        } elseif ($license == License::TIME_OUT) {
            return $buffer.'<script>alert("License system\nCan\'t connect to license server and verify it.");</script>';
        } elseif ($license == License::ERROR) {
            return '<strong>License system</strong><br />The license is not valid. Please correct it or go to free version.';
        }

        return trim($buffer);
    }

    public function loginCheck()
    {
        if (isset($_SESSION['mlite_user']) && isset($_SESSION['token']) && isset($_SESSION['userAgent']) && isset($_SESSION['IPaddress'])) {
            if ($_SESSION['IPaddress'] != $_SERVER['REMOTE_ADDR']) {
                return false;
            }
            if ($_SESSION['userAgent'] != $_SERVER['HTTP_USER_AGENT']) {
                return false;
            }

            if (empty(parseURL(1))) {
                redirect(url([ADMIN, 'dashboard', 'main']));
            } elseif (!isset($_GET['t']) || ($_SESSION['token'] != @$_GET['t'])) {
                return false;
            }

            return true;
        } elseif (isset($_COOKIE['mlite_remember'])) {
            $token = explode(":", $_COOKIE['mlite_remember']);
            if (count($token) == 2) {
                $row = $this->db('mlite_users')->leftJoin('remember_me', 'remember_me.user_id = mlite_users.id')->where('mlite_users.id', $token[0])->where('remember_me.token', $token[1])->select(['mlite_users.*', 'remember_me.expiry', 'token_id' => 'remember_me.id'])->oneArray();

                if ($row) {
                    if (time() - $row['expiry'] > 0) {
                        $this->db('mlite_remember_me')->delete(['id' => $row['token_id']]);
                    } else {
                        $_SESSION['mlite_user']= $row['id'];
                        $_SESSION['token']      = bin2hex(openssl_random_pseudo_bytes(6));
                        $_SESSION['userAgent']  = $_SERVER['HTTP_USER_AGENT'];
                        $_SESSION['IPaddress']  = $_SERVER['REMOTE_ADDR'];

                        $this->db('mlite_remember_me')->where('remember_me.user_id', $token[0])->where('remember_me.token', $token[1])->save(['expiry' => time()+60*60*24*30]);

                        if (strpos($_SERVER['SCRIPT_NAME'], '/'.ADMIN.'/') !== false) {
                            redirect(url([ADMIN, 'dashboard', 'main']));
                        }

                        return true;
                    }
                }
            }
            setcookie('mlite_remember', null, -1, '/');
        }

        return false;
    }

    public function getUserInfo($field, $id = null, $refresh = false)
    {
        if (!$id) {
            $id = isset_or($_SESSION['mlite_user'], 0);
        }

        if (empty(self::$userCache) || $refresh) {
            self::$userCache = $this->db('mlite_users')->where('id', $id)->oneArray();
        }

        return self::$userCache[$field];
    }

    public function loadModules()
    {
        if ($this->module == null) {
            $this->module = new Lib\ModulesCollection($this);
        }
    }

    private function freshInstall()
    {
        //QueryWrapper::connect("sqlite:{$dbFile}");
        QueryWrapper::connect("mysql:host=".DBHOST.";port=".DBPORT.";dbname=".DBNAME."",DBUSER, DBPASS);
        $pdo = QueryWrapper::pdo();

        $core = $this;

        $modules = unserialize(BASIC_MODULES);
        foreach ($modules as $module) {
            $file = MODULES.'/'.$module.'/Info.php';

            if (file_exists($file)) {
                $info = include($file);
                if (isset($info['install'])) {
                    $info['install']();
                }
            }
        }

        foreach ($modules as $order => $name) {
            $core->db('mlite_modules')->save(['dir' => $name, 'sequence' => $order]);
        }


        redirect(url());
    }
}
