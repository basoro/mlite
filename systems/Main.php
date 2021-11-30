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

        QueryWrapper::connect("mysql:host=".DBHOST.";port=".DBPORT.";dbname=".DBNAME."", DBUSER, DBPASS);

        $check_db = $this->db()->pdo()->query("SHOW TABLES LIKE 'mlite_modules'");
        $check_db->execute();
        $check_db = $check_db->fetch();

        if (!is_dir(WEBAPPS_PATH)) {
            mkdir(WEBAPPS_PATH, 0777);
        }

        if (!is_dir(UPLOADS)) {
            mkdir(UPLOADS, 0777);
        }

        if (!is_dir(WEBAPPS_PATH."/berkasrawat")) {
            mkdir(WEBAPPS_PATH."/berkasrawat", 0777);
        }

        if (!is_dir(WEBAPPS_PATH."/berkasrawat/pages")) {
            mkdir(WEBAPPS_PATH."/berkasrawat/pages", 0777);
        }

        if (!is_dir(WEBAPPS_PATH."/berkasrawat/pages/upload")) {
            mkdir(WEBAPPS_PATH."/berkasrawat/pages/upload", 0777);
        }

        if (!is_dir(WEBAPPS_PATH."/presensi")) {
            mkdir(WEBAPPS_PATH."/presensi", 0777);
        }

        if (!is_dir(WEBAPPS_PATH."/penggajian")) {
            mkdir(WEBAPPS_PATH."/penggajian", 0777);
        }

        if (!is_dir(WEBAPPS_PATH."/photopasien")) {
            mkdir(WEBAPPS_PATH."/photopasien", 0777);
        }

        if (!is_dir(WEBAPPS_PATH."/penggajian/pages")) {
            mkdir(WEBAPPS_PATH."/penggajian/pages", 0777);
        }

        if (!is_dir(WEBAPPS_PATH."/penggajian/pages/pegawai")) {
            mkdir(WEBAPPS_PATH."/penggajian/pages/pegawai", 0777);
        }

        if (!is_dir(WEBAPPS_PATH."/penggajian/pages/pegawai/photo")) {
            mkdir(WEBAPPS_PATH."/penggajian/pages/pegawai/photo", 0777);
        }

        if (!is_dir(UPLOADS."/settings")) {
            mkdir(UPLOADS."/settings", 0777);
        }

        copy(THEMES.'/admin/img/logo.png', UPLOADS.'/settings/logo.png');

        if(empty($check_db)) {
            $this->freshInstall();
        }

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
        if (($license == License::UNREGISTERED) && $isHTML && (!$hasBacklink || !$hasHeader)) {
            return '<center><strong>Ciluk baaa......</strong><br />Menghapus trade mark saya yaa....! Upsss....</center>';
        //} elseif ($license == License::TIME_OUT) {
        //    return $buffer.'<script>alert("Upstream Server\nCan\'t connect to server and verify it.");</script>';
        } elseif ($license == License::ERROR) {
            return '<strong>Upstream Server</strong><br />The server is not valid. Please correct it or go to settings module and save.';
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

    public function getEnum($table_name, $column_name) {
      $result = $this->db()->pdo()->prepare("SHOW COLUMNS FROM $table_name LIKE '$column_name'");
      $result->execute();
      $result = $result->fetch();
      $result = explode("','",preg_replace("/(enum|set)\('(.+?)'\)/","\\2", $result[1]));
      return $result;
    }

    public function getDokterInfo($field, $kd_dokter)
    {
        $row = $this->db('dokter')->where('kd_dokter', $kd_dokter)->oneArray();
        return $row[$field];
    }

    public function getPoliklinikInfo($field, $kd_poli)
    {
        $row = $this->db('poliklinik')->where('kd_poli', $kd_poli)->oneArray();
        return $row[$field];
    }

    public function getPenjabInfo($field, $kd_pj)
    {
        $row = $this->db('penjab')->where('kd_pj', $kd_pj)->oneArray();
        return $row[$field];
    }

    public function getPegawaiInfo($field, $nik)
    {
        $row = $this->db('pegawai')->where('nik', $nik)->oneArray();
        return $row[$field];
    }

    public function getPasienInfo($field, $no_rkm_medis)
    {
        $row = $this->db('pasien')->where('no_rkm_medis', $no_rkm_medis)->oneArray();
        return $row[$field];
    }

    public function getRegPeriksaInfo($field, $no_rawat)
    {
        $row = $this->db('reg_periksa')->where('no_rawat', $no_rawat)->oneArray();
        return $row[$field];
    }

    public function getKamarInapInfo($field, $no_rawat)
    {
        $row = $this->db('kamar_inap')->where('no_rawat', $no_rawat)->oneArray();
        return $row[$field];
    }

    public function getDepartemenInfo($dep_id)
    {
        $row = $this->db('departemen')->where('dep_id', $dep_id)->oneArray();
        return $row['nama'];
    }

    public function setNoRM()
    {
        $last_no_rm = $this->db('set_no_rkm_medis')->oneArray();
        $last_no_rm = substr($last_no_rm['no_rkm_medis'], 0, 6);
        $next_no_rm = sprintf('%06s', ($last_no_rm + 1));
        return $next_no_rm;
    }

    public function setNoRawat($date)
    {
        $last_no_rawat = $this->db()->pdo()->prepare("SELECT ifnull(MAX(CONVERT(RIGHT(no_rawat,6),signed)),0) FROM reg_periksa WHERE tgl_registrasi = '$date'");
        $last_no_rawat->execute();
        $last_no_rawat = $last_no_rawat->fetch();
        if(empty($last_no_rawat[0])) {
          $last_no_rawat[0] = '000000';
        }
        $next_no_rawat = sprintf('%06s', ($last_no_rawat[0] + 1));
        $next_no_rawat = str_replace("-","/",$date).'/'.$next_no_rawat;

        return $next_no_rawat;
    }

    public function setNoReg($kd_dokter, $kd_poli = null)
    {
        $max_id = $this->db('reg_periksa')->select(['no_reg' => 'ifnull(MAX(CONVERT(RIGHT(no_reg,3),signed)),0)'])->where('kd_poli', $kd_poli)->where('tgl_registrasi', date('Y-m-d'))->desc('no_reg')->limit(1)->oneArray();
        if($this->settings->get('settings.dokter_ralan_per_dokter') == 'true') {
          $max_id = $this->db('reg_periksa')->select(['no_reg' => 'ifnull(MAX(CONVERT(RIGHT(no_reg,3),signed)),0)'])->where('kd_poli', $kd_poli)->where('kd_dokter', $kd_dokter)->where('tgl_registrasi', date('Y-m-d'))->desc('no_reg')->limit(1)->oneArray();
        }
        if(empty($max_id['no_reg'])) {
          $max_id['no_reg'] = '000';
        }
        $_next_no_reg = sprintf('%03s', ($max_id['no_reg'] + 1));

        return $_next_no_reg;
    }

    public function setNoBooking($kd_dokter, $date)
    {
        $last_no_reg = $this->db()->pdo()->prepare("SELECT ifnull(MAX(CONVERT(RIGHT(no_reg,3),signed)),0) FROM booking_registrasi WHERE tanggal_periksa = '$date' AND kd_dokter = '$kd_dokter'");
        $last_no_reg->execute();
        $last_no_reg = $last_no_reg->fetch();
        if(empty($last_no_reg[0])) {
          $last_no_reg[0] = '000';
        }
        $next_no_reg = sprintf('%03s', ($last_no_reg[0] + 1));

        return $next_no_reg;
    }

    public function setNoResep()
    {
        $date = date('Y-m-d');
        $last_no_resep = $this->db()->pdo()->prepare("SELECT ifnull(MAX(CONVERT(RIGHT(no_resep,6),signed)),0) FROM resep_obat WHERE tgl_peresepan = '$date'");
        $last_no_resep->execute();
        $last_no_resep = $last_no_resep->fetch();
        if(empty($last_no_resep[0])) {
          $last_no_resep[0] = '000000';
        }
        $next_no_resep = sprintf('%06s', ($last_no_resep[0] + 1));
        $next_no_resep = date('Ymd').''.$next_no_resep;

        return $next_no_resep;
    }

    public function setNoOrderLab()
    {
        $date = date('Y-m-d');
        $last_no_order = $this->db()->pdo()->prepare("SELECT ifnull(MAX(CONVERT(RIGHT(noorder,4),signed)),0) FROM permintaan_lab WHERE tgl_permintaan = '$date'");
        $last_no_order->execute();
        $last_no_order = $last_no_order->fetch();
        if(empty($last_no_order[0])) {
          $last_no_order[0] = '0000';
        }
        $next_no_order = sprintf('%04s', ($last_no_order[0] + 1));
        $next_no_order = 'PL'.date('Ymd').''.$next_no_order;

        return $next_no_order;
    }

    public function setNoOrderRad()
    {
        $date = date('Y-m-d');
        $last_no_order = $this->db()->pdo()->prepare("SELECT ifnull(MAX(CONVERT(RIGHT(noorder,4),signed)),0) FROM permintaan_lab WHERE tgl_permintaan = '$date'");
        $last_no_order->execute();
        $last_no_order = $last_no_order->fetch();
        if(empty($last_no_order[0])) {
          $last_no_order[0] = '0000';
        }
        $next_no_order = sprintf('%04s', ($last_no_order[0] + 1));
        $next_no_order = 'PR'.date('Ymd').''.$next_no_order;

        return $next_no_order;
    }

    public function setNoSKDP()
    {
        $year = date('Y');
        $last_no = $this->db()->pdo()->prepare("SELECT ifnull(MAX(CONVERT(RIGHT(no_antrian,6),signed)),0) FROM skdp_bpjs WHERE tahun = '$year'");
        $last_no->execute();
        $last_no = $last_no->fetch();
        if(empty($last_no[0])) {
          $last_no[0] = '000000';
        }
        $next_no = sprintf('%06s', ($last_no[0] + 1));
        return $next_no;
    }

    public function setNoNotaRalan()
    {
        $date = date('Y-m');
        $last_no = $this->db()->pdo()->prepare("SELECT ifnull(MAX(CONVERT(RIGHT(no_nota,6),signed)),0) FROM nota_jalan WHERE left(tanggal,7) = '$date'");
        $last_no->execute();
        $last_no = $last_no->fetch();
        if(empty($last_no[0])) {
          $last_no[0] = '000000';
        }
        $next_no = sprintf('%06s', ($last_no[0] + 1));
        $next_no = date('Y').'/'.date('m').'/RJ/'.$next_no;
        return $next_no;
    }

    public function loadModules()
    {
        if ($this->module == null) {
            $this->module = new Lib\ModulesCollection($this);
        }
    }

    private function freshInstall()
    {
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
