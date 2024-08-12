<?php

namespace Systems;

use Medoo\Medoo;
use Systems\Lib\Templates;
use Systems\Lib\Router;
use Systems\Lib\Settings;


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
        
        $this->db = new Medoo([
            'type' => 'mysql',
            'host' => DBHOST,
            'port' => DBPORT, 
            'database' => DBNAME,
            'username' => DBUSER,
            'password' => DBPASS,
            'logging' => true,
            'error' => \PDO::ERRMODE_SILENT
        ]);

        $this->dbmlite = new Medoo([
            'type' => 'sqlite', /* For dev only */
            'database' => BASE_DIR . '/systems/data/database.sdb' /* For dev onnly */
            // 'type' => 'mysql',
            // 'host' => DBHOST,
            // 'port' => DBPORT, 
            // 'database' => DBNAME,
            // 'username' => DBUSER,
            // 'password' => DBPASS,
            // 'logging' => true,
            // 'error' => \PDO::ERRMODE_SILENT
        ]);

        $this->vclaim = [
            'cons_id' => $this->dbmlite->get('mlite_settings', 'value', ['module' => 'settings', 'field' => 'BpjsConsID']),
            'secret_key' => $this->dbmlite->get('mlite_settings', 'value', ['module' => 'settings', 'field' => 'BpjsSecretKey']),
            'user_key' => $this->dbmlite->get('mlite_settings', 'value', ['module' => 'settings', 'field' => 'BpjsUserKey']),
            'base_url' => $this->dbmlite->get('mlite_settings', 'value', ['module' => 'settings', 'field' => 'BpjsApiUrl']),
            'service_name' => $this->dbmlite->get('mlite_settings', 'value', ['module' => 'settings', 'field' => 'BpjsServiceName'])
        ];

        if (!is_dir(UPLOADS)) {
            mkdir(UPLOADS, 0777);
        }

        if (!is_dir(UPLOADS."/users")) {
            mkdir(UPLOADS."/users", 0777);
        }

        if (!is_dir(UPLOADS."/berkasrawat")) {
            mkdir(UPLOADS."/berkasrawat", 0777);
        }

        if (!is_dir(UPLOADS."/presensi")) {
            mkdir(UPLOADS."/presensi", 0777);
        }

        if (!is_dir(UPLOADS."/pasien")) {
            mkdir(UPLOADS."/pasien", 0777);
        }

        if (!is_dir(UPLOADS."/pegawai")) {
            mkdir(UPLOADS."/pegawai", 0777);
        }

        if (!is_dir(UPLOADS."/settings")) {
            mkdir(UPLOADS."/settings", 0777);
        }

        if (!is_dir(UPLOADS."/invoices")) {
            mkdir(UPLOADS."/invoices", 0777);
        }

        if (!is_dir(UPLOADS."/laboratorium")) {
            mkdir(UPLOADS."/laboratorium", 0777);
        }

        if (!is_dir(UPLOADS."/radiologi")) {
            mkdir(UPLOADS."/radiologi", 0777);
        }
        
        copy(THEMES.'/img/logo.png', UPLOADS.'/settings/logo.png');

        $this->settings = new Settings($this);
        date_default_timezone_set($this->settings->get('settings.timezone'));

        $this->tpl = new Templates($this);
        $this->router = new Router;

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

    public function setNotify($type, $text, $args = '')
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
    
    public function loginCheck()
    {

        $whitelist = explode(',', WHITELIST_IP);

        if (!isAllowed($_SERVER['REMOTE_ADDR'], $whitelist)) {
            echo $this->drawTheme('403.html');
            exit();
        }

        if (isset($_SESSION['mlite_user']) && isset($_SESSION['token']) && isset($_SESSION['userAgent']) && isset($_SESSION['IPaddress'])) {
            
            if ($_SESSION['IPaddress'] != $_SERVER['REMOTE_ADDR']) {
                return false;
            }
            if ($_SESSION['userAgent'] != $_SERVER['HTTP_USER_AGENT']) {
                return false;
            }

            if (empty(parseURL(1))) {
                redirect(url(['dashboard', 'main']));
            } elseif (!isset($_GET['t']) || ($_SESSION['token'] != @$_GET['t'])) {
                return false;
            }

            if(!$this->getModuleInfo(parseUrl()[0]) && parseUrl()[0] !='logout') {
                $this->tpl->set('module', '');
                echo $this->tpl->draw(THEMES.'/404.html', true);
                exit;
            }

            return true;
        } elseif (isset($_COOKIE['mlite_remember'])) {
            $token = explode(":", $_COOKIE['mlite_remember']);
            if (count($token) == 2) {
                $row = $this->dbmlite->get('mlite_users', [
                  '[>]mlite_remember_me' => ['id' => 'user_id']
                ],[
                  'mlite_users.id', 'mlite_remember_me.expiry', 'mlite_remember_me.id(token_id)'
                ]);
          
                if ($row) {
                    if (time() - $row['expiry'] > 0) {
                        $this->dbmlite->delete('mlite_remember_me', [
                            'AND' => [
                                'id' => $row['token_id']
                            ]
                        ]);
                    } else {
                        $_SESSION['mlite_user']= $row['id'];
                        $_SESSION['token']      = bin2hex(openssl_random_pseudo_bytes(6));
                        $_SESSION['userAgent']  = $_SERVER['HTTP_USER_AGENT'];
                        $_SESSION['IPaddress']  = $_SERVER['REMOTE_ADDR'];

                        $this->dbmlite->update('mlite_remember_me', [
                            'expiry' => time()+60*60*24*30
                        ],[
                            'user_id' => $token[0], 
                            'token' => $token[1]
                        ]);                    
                        
                        redirect(url(['dashboard', 'main']));

                        return true;
                    }
                }
            }
            setcookie('mlite_remember', '', -1, '/');
        }

        return false;
    }

    public function getUserInfo($field, $id = '', $refresh = false)
    {
        if (!$id) {
            $id = isset_or($_SESSION['mlite_user'], 0);
        }

        if (empty(self::$userCache) || $refresh) {
            self::$userCache = $this->dbmlite->get('mlite_users', '*', ['id' => $id]);
        }

        return isset_or(self::$userCache[$field], false);
    }

    public function getEnum($table_name, $column_name) {
        $result = $this->db->pdo->prepare("SHOW COLUMNS FROM $table_name LIKE '$column_name'");
        $result->execute();
        $result = $result->fetch();
        if(!empty($result[1])) {
            $result = explode("','",preg_replace("/(enum|set)\('(.+?)'\)/","\\2", $result[1]));
        } else {
            $result = [];
        }
        return $result;
    }
  
    public function setNoRM()
    {
        $last_no_rm = $this->db->get('set_no_rkm_medis', '*');
        $last_no_rm = substr($last_no_rm['no_rkm_medis'], 0, 6);
        $next_no_rm = sprintf('%06s', ($last_no_rm + 1));
        return $next_no_rm;
    }

    public function setNoRawat($date)
    {
        $last_no_rawat = $this->db->pdo->prepare("SELECT ifnull(MAX(CONVERT(RIGHT(no_rawat,6),signed)),0) FROM reg_periksa WHERE tgl_registrasi = ?");
        $last_no_rawat->execute([$date]);
        $last_no_rawat = $last_no_rawat->fetch();
        if(empty($last_no_rawat[0])) {
          $last_no_rawat[0] = '000000';
        }
        $next_no_rawat = sprintf('%06s', ($last_no_rawat[0] + 1));
        $next_no_rawat = str_replace("-","/",$date).'/'.$next_no_rawat;

        return $next_no_rawat;
    }

    public function setNoReg($kd_poli = '', $kd_dokter = '')
    {
        $date = date('Y-m-d');
        $last_no_reg = $this->db->pdo->prepare("SELECT ifnull(MAX(CONVERT(RIGHT(no_reg,3),signed)),0) FROM reg_periksa WHERE tgl_registrasi = ? AND kd_poli = ? AND kd_dokter = ?");
        $last_no_reg->execute([$date, $kd_poli, $kd_dokter]);    
        $last_no_reg = $last_no_reg->fetch();
        if(empty($last_no_reg[0])) {
          $last_no_reg[0] = '000';
        }
        $next_no_reg = sprintf('%03s', ($last_no_reg[0] + 1));
  
        return $next_no_reg;
    }

    public function setNoResep($date)
    {
        $last_no_resep = $this->db->pdo->prepare("SELECT ifnull(MAX(CONVERT(RIGHT(no_resep,4),signed)),0) FROM resep_obat WHERE tgl_peresepan = '$date' OR tgl_perawatan =  '$date'");
        $last_no_resep->execute();
        $last_no_resep = $last_no_resep->fetch();
        if(empty($last_no_resep[0])) {
          $last_no_resep[0] = '0000';
        }
        $next_no_resep = sprintf('%04s', ($last_no_resep[0] + 1));
        $next_no_resep = date('Ymd', strtotime($date)).''.$next_no_resep;

        return $next_no_resep;
    }

    public function setNoOrderLab($date)
    {
        $last_no_order = $this->db->pdo->prepare("SELECT ifnull(MAX(CONVERT(RIGHT(noorder,4),signed)),0) FROM permintaan_lab WHERE tgl_permintaan = '$date'");
        $last_no_order->execute();
        $last_no_order = $last_no_order->fetch();
        if(empty($last_no_order[0])) {
          $last_no_order[0] = '0000';
        }
        $next_no_order = sprintf('%04s', ($last_no_order[0] + 1));
        $next_no_order = 'PL'.date('Ymd').''.$next_no_order;

        return $next_no_order;
    }

    public function setNoOrderRadiologi($date)
    {
        $last_no_order = $this->db->pdo->prepare("SELECT ifnull(MAX(CONVERT(RIGHT(noorder,4),signed)),0) FROM permintaan_radiologi WHERE tgl_permintaan = '$date'");
        $last_no_order->execute();
        $last_no_order = $last_no_order->fetch();
        if(empty($last_no_order[0])) {
          $last_no_order[0] = '0000';
        }
        $next_no_order = sprintf('%04s', ($last_no_order[0] + 1));
        $next_no_order = 'PR'.date('Ymd').''.$next_no_order;

        return $next_no_order;
    }

    public function setKodeDatabarang()
    {
        $last_kode_brng = $this->db->pdo->prepare("SELECT ifnull(MAX(CONVERT(RIGHT(kode_brng,5),signed)),0) FROM databarang");
        $last_kode_brng->execute();
        $last_kode_brng = $last_kode_brng->fetch();
        if(empty($last_kode_brng[0])) {
          $last_kode_brng[0] = '00000';
        }
        $next_kode_brng = sprintf('%05s', ($last_kode_brng[0] + 1));
        $next_kode_brng = 'B'.$next_kode_brng;

        return $next_kode_brng;
    }

    public function setKodeJnsPerawatan()
    {
        $last = $this->db->pdo->prepare("SELECT ifnull(MAX(CONVERT(RIGHT(kd_jenis_prw,3),signed)),0) FROM jns_perawatan");
        $last->execute();
        $last = $last->fetch();
        if(empty($last[0])) {
          $last[0] = '0000';
        }
        $next = sprintf('%03s', ($last[0] + 1));
        $next = 'RJ'.$next;

        return $next;
    }

    public function setKodeJnsPerawatanInap()
    {
        $last = $this->db->pdo->prepare("SELECT ifnull(MAX(CONVERT(RIGHT(kd_jenis_prw,3),signed)),0) FROM jns_perawatan_inap");
        $last->execute();
        $last = $last->fetch();
        if(empty($last[0])) {
          $last[0] = '0000';
        }
        $next = sprintf('%03s', ($last[0] + 1));
        $next = 'RI'.$next;

        return $next;
    }

    public function setKodeJnsPerawatanLab()
    {
        $last = $this->db->pdo->prepare("SELECT ifnull(MAX(CONVERT(RIGHT(kd_jenis_prw,3),signed)),0) FROM jns_perawatan_lab");
        $last->execute();
        $last = $last->fetch();
        if(empty($last[0])) {
          $last[0] = '0000';
        }
        $next = sprintf('%03s', ($last[0] + 1));
        $next = 'LAB'.$next;

        return $next;
    }    

    public function setKodeJnsPerawatanRadiologi()
    {
        $last = $this->db->pdo->prepare("SELECT ifnull(MAX(CONVERT(RIGHT(kd_jenis_prw,3),signed)),0) FROM jns_perawatan_radiologi");
        $last->execute();
        $last = $last->fetch();
        if(empty($last[0])) {
          $last[0] = '0000';
        }
        $next = sprintf('%03s', ($last[0] + 1));
        $next = 'RAD'.$next;

        return $next;
    }

    public function loadDisabledMenu($module)
    {
        $disable_menu = $this->dbmlite->get('mlite_disabled_menu', ['create', 'read', 'update', 'delete'], ['user' => $this->getUserInfo('username', $_SESSION['mlite_user'], true), 'module' => $module]);
        if(!$disable_menu) {
            $disable_menu = array('create' => 'true', 'read' => 'true', 'update' => 'true', 'delete' => 'true');
        }
        if($this->getUserInfo('role', $_SESSION['mlite_user'], true) == 'admin') {
            $disable_menu = array('create' => 'false', 'read' => 'false', 'update' => 'false', 'delete' => 'false');
        }    

        return $disable_menu;
    }

    public function LogQuery($endpoint)
    {

        $user = $this->dbmlite->get('mlite_users', 'username', ['id' => $_SESSION['mlite_user']]);
        $tanggal = date('Y-m-d');

        $this->db->insert('mlite_log_query_database', [
          'user' => $user, 
          'tanggal' => $tanggal, 
          'endpoint' => $endpoint, 
          'query' => json_encode($this->db->log(), JSON_PRETTY_PRINT)
        ]);              
        return false;

    }

    public function loadModules()
    {
        if ($this->module == '') {
            $this->module = new Lib\ModulesCollection($this);
        }
    }

}
