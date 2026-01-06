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
        $dsn = "mysql:host=".DBHOST.";port=".DBPORT.";dbname=".DBNAME."";
        if (defined('DBDRIVER') && DBDRIVER == 'sqlite') {
            $dsn = "sqlite:".DBNAME;
        }
        QueryWrapper::connect($dsn, DBUSER, DBPASS);

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

        if (!is_dir(UPLOADS."/users")) {
            mkdir(UPLOADS."/users", 0777);
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
        
        copy(THEMES.'/admin/img/logo.png', UPLOADS.'/settings/logo.png');
        copy(THEMES.'/admin/img/wallpaper.jpg', UPLOADS.'/settings/wallpaper.jpg');

        $this->settings = new Settings($this);
        date_default_timezone_set($this->settings->get('settings.timezone'));

        $this->tpl = new Templates($this);
        $this->router = new Router;

        $this->append(base64_decode('PG1ldGEgbmFtZT0iZ2VuZXJhdG9yIiBjb250ZW50PSJNZWRpYyBMSVRFIEluZG9uZXNpYSIgLz4='), 'header');
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
        if (session_status() !== PHP_SESSION_ACTIVE) {
            ini_set('session.use_only_cookies', 1);
            session_name('mlite');
            session_set_cookie_params(0, (mlite_dir() === '/' ? '/' : mlite_dir().'/'));
            session_start();
        }
    }    

    /**
     * Set notification message with sprintf formatting
     * Compatible with PHP 8+ variadic parameters
     * 
     * @param string $type Notification type
     * @param string $text Message text with sprintf placeholders
     * @param mixed ...$args Arguments for sprintf formatting
     */
    public function setNotify(string $type, string $text, ...$args): void
    {
        if (!empty($args)) {
            $text = sprintf($text, ...$args);
        }
        $_SESSION[$type] = $text;
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
        $hasBacklink = strpos($checkBuffer, base64_decode('UG93ZXJlZCBieSA8YSBocmVmPSJodHRwczovL21saXRlLmlkLyI+bUxJVEU8L2E+')) !== false;
        $hasHeader = get_headers_list('X-Created-By') === 'Medic LITE Indonesia <mlite.id>';
        $license = License::verify($core->settings->get('settings.license'));
        if (($license == License::UNREGISTERED) && $isHTML && (!$hasBacklink)) {
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
            
            if($this->settings->get('settings.keamanan') == 'ya') {
                if ($_SESSION['IPaddress'] != $_SERVER['REMOTE_ADDR']) {
                    return false;
                }
                if ($_SESSION['userAgent'] != $_SERVER['HTTP_USER_AGENT']) {
                    return false;
                }
            }

            if (empty(parseURL(1))) {
                if(MULTI_APP) {
                    if(!empty(MULTI_APP_REDIRECT)) {
                        redirect(url([ADMIN, MULTI_APP_REDIRECT, 'main']));
                    } else {
                        redirect(url([ADMIN, 'dashboard', 'main']));
                    }
                } else {
                    redirect(url([ADMIN, 'dashboard', 'main']));
                }
            } elseif (!isset($_GET['t']) || ($_SESSION['token'] != @$_GET['t'])) {
                return false;
            }

            return true;
        } elseif (isset($_COOKIE['mlite_remember'])) {
            $token = explode(":", $_COOKIE['mlite_remember']);
            if (count($token) == 2) {
                $row = $this->db('mlite_users')->leftJoin('mlite_remember_me', 'mlite_remember_me.user_id = mlite_users.id')->where('mlite_users.id', $token[0])->where('mlite_remember_me.token', $token[1])->select(['mlite_users.*', 'mlite_remember_me.expiry', 'token_id' => 'mlite_remember_me.id'])->oneArray();

                if ($row) {
                    if (time() - $row['expiry'] > 0) {
                        $this->db('mlite_remember_me')->delete(['id' => $row['token_id']]);
                    } else {
                        $_SESSION['mlite_user']= $row['id'];
                        $_SESSION['token']      = bin2hex(openssl_random_pseudo_bytes(6));
                        $_SESSION['userAgent']  = $_SERVER['HTTP_USER_AGENT'];
                        $_SESSION['IPaddress']  = $_SERVER['REMOTE_ADDR'];

                        $this->db('mlite_remember_me')->where('mlite_remember_me.user_id', $token[0])->where('mlite_remember_me.token', $token[1])->save(['expiry' => time()+60*60*24*30]);

                        if (strpos($_SERVER['SCRIPT_NAME'], '/'.ADMIN.'/') !== false) {
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

                        return true;
                    }
                }
            }
            setcookie('mlite_remember', '', -1, '/');
        }

        return false;
    }

    /**
     * Get user information by field
     * Compatible with PHP 8+ with proper null handling
     * 
     * @param string $field Field name to retrieve
     * @param int|null $id User ID (defaults to current session user)
     * @param bool $refresh Whether to refresh cache
     * @return mixed|null Field value or null if not found
     */
    public function getUserInfo(string $field, ?int $id = null, bool $refresh = false)
    {
        if (!$id) {
            $id = isset_or($_SESSION['mlite_user'], 0);
        }

        if (empty(self::$userCache) || $refresh) {
            $userData = $this->db('mlite_users')->where('id', $id)->oneArray();
            self::$userCache = is_array($userData) ? $userData : [];
        }

        // Safe field access with null coalescing
        return self::$userCache[$field] ?? null;
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
        return isset_or($row[$field],'');
    }

    public function getBangsalInfo($field, $kd_bangsal)
    {
        $row = $this->db('bangsal')->where('kd_bangsal', $kd_bangsal)->oneArray();
        return isset_or($row[$field],'');
    }

    public function getKamarInfo($field, $kd_kamar)
    {
        $row = $this->db('kamar')->where('kd_kamar', $kd_kamar)->oneArray();
        return isset_or($row[$field],'');
    }

    public function getPenjabInfo($field, $kd_pj)
    {
        $row = $this->db('penjab')->where('kd_pj', $kd_pj)->oneArray();
        return $row[$field];
    }

    public function getPegawaiInfo($field, $nik)
    {
        $row = $this->db('pegawai')->where('nik', $nik)->oneArray();
        return isset_or($row[$field], '');
    }

    public function getPasienInfo($field, $no_rkm_medis)
    {
        $row = $this->db('pasien')->where('no_rkm_medis', $no_rkm_medis)->oneArray();
        return isset($row[$field]) ? $row[$field] : '';
    }

    public function getRegPeriksaInfo($field, $no_rawat)
    {
        $row = $this->db('reg_periksa')->where('no_rawat', $no_rawat)->oneArray();
        return isset($row[$field]) ? $row[$field] : '';
    }

    public function getKamarInapInfo($field, $no_rawat)
    {
        $row = $this->db('kamar_inap')->where('no_rawat', $no_rawat)->oneArray();
        return isset($row[$field]) ? $row[$field] : '';
    }

    public function getDepartemenInfo($dep_id)
    {
        $row = $this->db('departemen')->where('dep_id', $dep_id)->oneArray();
        return isset($row['nama']) ? $row['nama'] : '';
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
        $urut = $this->db('reg_periksa')
            ->where('tgl_registrasi', $date)
            ->nextRightNumber('no_rawat', 6);

        $next_no_rawat =
            str_replace('-', '/', $date) .
            '/' .
            sprintf('%06d', $urut);

        return $next_no_rawat;
    }

    public function setNoReg($kd_dokter, $kd_poli = null)
    {
        $q = $this->db('reg_periksa')
            ->where('kd_poli', $kd_poli)
            ->where('tgl_registrasi', date('Y-m-d'));

        if ($this->settings->get('settings.dokter_ralan_per_dokter') == 'true') {
            $q->where('kd_dokter', $kd_dokter);
        }

        $urut = $q->nextRightNumber('no_reg', 3);

        $_next_no_reg = sprintf('%03d', $urut);

        return $_next_no_reg;
    }

    public function setNoBooking($kd_dokter, $date, $kd_poli = null)
    {
        $q = $this->db('booking_registrasi')
            ->where('tanggal_periksa', $date);

        if ($this->settings->get('settings.dokter_ralan_per_dokter') == 'true') {
            $q->where('kd_dokter', $kd_dokter);
        } else {
            $q->where('kd_poli', $kd_poli)
            ->where('kd_dokter', $kd_dokter);
        }

        $urut = $q->nextRightNumber('no_reg', 3);

        $next_no_reg = sprintf('%03d', $urut);

        return $next_no_reg;
    }

    public function setNoResep($date)
    {
        $urut = $this->db('resep_obat')
            ->where('tgl_peresepan', $date)
            ->orWhere('tgl_perawatan', $date)
            ->nextRightNumber('no_resep', 4);

        $next_no_resep = sprintf('%04d', $urut);
        $next_no_resep = date('Ymd', strtotime($date)).''.$next_no_resep;

        return $next_no_resep;
    }

    public function setNoOrderLab()
    {
        $date = date('Y-m-d');
        $urut = $this->db('permintaan_lab')
            ->where('tgl_permintaan', $date)
            ->nextRightNumber('noorder', 4);

        $next_no_order = 'PL' . date('Ymd') . sprintf('%04d', $urut);

        return $next_no_order;
    }

    public function setNoOrderRad()
    {
        $date = date('Y-m-d');
        $urut = $this->db('permintaan_rad')
            ->where('tgl_permintaan', $date)
            ->nextRightNumber('noorder', 4);

        $next_no_order = 'PR' . date('Ymd') . sprintf('%04d', $urut);

        return $next_no_order;
    }

    public function setNoSKDP()
    {
        $year = date('Y');

        $urut = $this->db('skdp_bpjs')
            ->where('tahun', $year)
            ->nextRightNumber('no_antrian', 6);

        $next_no = sprintf('%06d', $urut);
        return $next_no;
    }

    public function setNoNotaRalan()
    {
        $dateYm = date('Y-m');

        $urut = $this->db('nota_jalan')
            ->whereRaw('LEFT(tanggal,7) = ?', [$dateYm])
            ->nextRightNumber('no_nota', 6);

        $next_no = sprintf('%06d', $urut);
        $next_no = date('Y').'/'.date('m').'/RJ/'.$next_no;

        return $next_no;
    }

    public function setNoJurnal()
    {
        $date = date('Y-m-d');

        $urut = $this->db('mlite_jurnal')
            ->where('tgl_jurnal', $date)
            ->nextRightNumber('no_jurnal', 6);

        $next_no_jurnal = sprintf('%06d', $urut);
        $next_no_jurnal = 'JR'.date('Ymd').''.$next_no_jurnal;

        return $next_no_jurnal;
    }

    public function AccesModule($module)
    {
        $access = $this->getUserInfo('access');
        $accessmodule = ($access == 'all') || in_array($module, explode(',', $access)) ? true : false;
        return $accessmodule;
    }

    public function ActiveModule($module)
    {
        $activemodule = $this->db('mlite_modules')->where('dir', $module)->oneArray();
        return isset_or($activemodule['dir']);
    }

    public function setPrintHeader()
    {
        $header = '
            <table width="100%">
                <tr>
                    <td width="120" align="center"><img src="'.url().'/'.$this->settings->get('settings.logo').'" height="75px"></td>
                    <td width="100%" align="left">
                        <span style="font-size:32px;">'.$this->settings->get('settings.nama_instansi').'</span><br>
                        '.$this->settings->get('settings.alamat').' - 
                        '.$this->settings->get('settings.kota').' - 
                        '.$this->settings->get('settings.propinsi').'<br>
                        Telepon: '.$this->settings->get('settings.nomor_telepon').' - 
                        E-Mail: '.$this->settings->get('settings.email').'
                    </td>
                </tr>
            </table>
            <hr style="height:1px;margin:3px;">
            <hr style="height:3px;margin:0;">
        ';
        return $header;
    }

    public function setPrintFooter()
    {
        $footer = '
            <table width="100%">
                <tr>
                    <td width="33%">Dicetak tanggal {DATE j-m-Y}</td>
                    <td width="33%" align="center">{PAGENO}/{nbpg}</td>
                    <td width="33%" style="text-align: right;">'.$this->settings->get('settings.nama_instansi').'</td>
                </tr>
            </table>
        ';
        return $footer;
    }

    public function setPrintCss()
    {
        $css = '
        * {
          font-family: arial, sans-serif;
        }
        div, table {
          font-family: arial, sans-serif;
          border-collapse: collapse;
          width: 100%;
        }
        
        .table td, .table th {
          border: 1px solid #dddddd;
          text-align: left;
          padding: 8px;
        }
        
        tr:nth-child(even) {
          background-color: #dddddd;
        }
        .right {
            float: right;
        ';  
        return $css;
    }
    
    public function loadCrudPermissions($module)
    {
        $permissions = $this->db('mlite_crud_permissions')->where('user', $this->getUserInfo('username', $_SESSION['mlite_user'], true))->where('module', $module)->oneArray();
        if(!$permissions) {
            $permissions = array('can_create' => 'true', 'can_read' => 'true', 'can_update' => 'true', 'can_delete' => 'true');
        }
        if($this->getUserInfo('role', $_SESSION['mlite_user'], true) == 'admin') {
            $permissions = array('can_create' => 'true', 'can_read' => 'true', 'can_update' => 'true', 'can_delete' => 'true');
        }    

        return $permissions;
    }

    public function loadModules()
    {
        if ($this->module == null) {
            $this->module = new Lib\ModulesCollection($this);
        }
    }

    public function getRegisteredPages()
    {
        return $this->router->getRegisteredPages();
    }

    public function umurDaftar($tgl_lahir) {
        $birthDate = new \DateTime($tgl_lahir);
        $today = new \DateTime("today");
    
        $umur_daftar = 0;
        $status_umur = 'Hr';
    
        if ($birthDate < $today) {
            $diff = $today->diff($birthDate);
            $y = $diff->y;
            $m = $diff->m;
            $d = $diff->d;
    
            if ($y != 0) {
                $umur_daftar = $y;
                $status_umur = "Th";
            } elseif ($m != 0) {
                $umur_daftar = $m;
                $status_umur = "Bl";
            } else {
                $umur_daftar = $d;
                $status_umur = "Hr";
            }
        }
    
        // Kembalikan sebagai array
        return [
            'umur_daftar' => $umur_daftar,
            'status_umur' => $status_umur
        ];
    }

    public function checkAuth($method)
    {
        // 1. Try API Key
        $apiKey = null;

        // Check $_SERVER for common variants
        if (!empty($_SERVER['HTTP_X_API_KEY'])) {
            $apiKey = $_SERVER['HTTP_X_API_KEY'];
        } elseif (!empty($_SERVER['X_API_KEY'])) {
            $apiKey = $_SERVER['X_API_KEY'];
        } elseif (!empty($_SERVER['HTTP_API_KEY'])) {
            $apiKey = $_SERVER['HTTP_API_KEY'];
        } elseif (!empty($_SERVER['API_KEY'])) {
            $apiKey = $_SERVER['API_KEY'];
        }

        // Get headers case-insensitively for fallback and other headers
        $headers = [];
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
        } elseif (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
        }

        // DEBUG: Log headers for investigation
        // file_put_contents(BASE_DIR.'/tmp/headers_debug.txt', date('Y-m-d H:i:s') . " Method: $method\n" . print_r($headers, true) . "\nSERVER:\n" . print_r($_SERVER, true) . "\n----------------\n", FILE_APPEND);

        // Fallback: Check all headers case-insensitively for API Key
        if (!$apiKey) {
            foreach ($headers as $key => $value) {
                if (strtolower($key) === 'x-api-key' || strtolower($key) === 'api-key') {
                    $apiKey = $value;
                    break;
                }
            }
        }
        
        $apiKey = trim((string)$apiKey);
        
        if ($apiKey) {
            $keyRecord = $this->db('mlite_api_key')->where('api_key', $apiKey)->oneArray();
            if (!$keyRecord) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Invalid API Key']);
                exit;
            }

            if (!empty($keyRecord['exp_time']) && $keyRecord['exp_time'] !== '0000-00-00' && strtotime($keyRecord['exp_time']) < time()) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'API Key expired']);
                exit;
            }

            if (!empty($keyRecord['ip_range']) && $keyRecord['ip_range'] !== '*') {
                $clientIp = $_SERVER['REMOTE_ADDR'];
                if (strpos($keyRecord['ip_range'], $clientIp) === false) {
                     http_response_code(401);
                     echo json_encode(['status' => 'error', 'message' => 'IP not allowed']);
                     exit;
                }
            }

            $allowedMethods = explode(',', strtoupper($keyRecord['method']));
            if (!in_array($method, $allowedMethods) && !in_array('ALL', $allowedMethods)) {
                 http_response_code(403);
                 echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
                 exit;
            }

            // --- Layer 2: API Client Module Access Validation ---
            // Check if the API Client (User linked to API Key) has access to the requested module
            $apiClientUser = $this->db('mlite_users')->where('username', $keyRecord['username'])->oneArray();
            if ($apiClientUser) {
                $module = parseURL(1); // Get module name from URL
                if ($module && $module !== 'api') { // Skip for 'api' module itself
                     $moduleCheck = $this->db('mlite_modules')->where('dir', $module)->oneArray();
                     if ($moduleCheck) {
                         $assignedModules = $apiClientUser['access']; // 'all' or comma-separated list
                         if ($assignedModules !== 'all' && !in_array($module, explode(',', $assignedModules))) {
                             http_response_code(403);
                             echo json_encode(['status' => 'error', 'message' => 'API Client denied access to this module']);
                             exit;
                         }
                     }
                }
            }

            // Check for User Permissions Credentials (X-Username-Permission & X-Password-Permission)
            $userPerm = null;
            $passPerm = null;
            
            foreach ($headers as $key => $value) {
                if (strtolower($key) === 'x-username-permission' || strtolower($key) === 'username-permission') {
                    $userPerm = $value;
                }
                if (strtolower($key) === 'x-password-permission' || strtolower($key) === 'password-permission') {
                    $passPerm = $value;
                }
            }
            
            // Also check $_SERVER just in case
            if(!$userPerm && !empty($_SERVER['HTTP_X_USERNAME_PERMISSION'])) $userPerm = $_SERVER['HTTP_X_USERNAME_PERMISSION'];
            if(!$passPerm && !empty($_SERVER['HTTP_X_PASSWORD_PERMISSION'])) $passPerm = $_SERVER['HTTP_X_PASSWORD_PERMISSION'];

            // Fallback: Check request parameters (GET/POST) or JSON body
            if (!$userPerm || !$passPerm) {
                // Check $_REQUEST
                if (!$userPerm && !empty($_REQUEST['username_permission'])) $userPerm = $_REQUEST['username_permission'];
                if (!$passPerm && !empty($_REQUEST['password_permission'])) $passPerm = $_REQUEST['password_permission'];
                
                // Check JSON Body (especially for DELETE/PUT where $_POST might be empty)
                if (!$userPerm || !$passPerm) {
                    $input = json_decode(file_get_contents('php://input'), true);
                    if (is_array($input)) {
                        if (!$userPerm && isset($input['username_permission'])) $userPerm = $input['username_permission'];
                        if (!$passPerm && isset($input['password_permission'])) $passPerm = $input['password_permission'];
                    }
                }
            }

            if ($userPerm && $passPerm) {
                $user = $this->db('mlite_users')->where('username', $userPerm)->oneArray();
                if ($user && password_verify(trim($passPerm), $user['password'])) {
                    
                    // --- Layer 3: End-User Module Access Validation ---
                    // Check if the End-User (logging in via Frontend) has access to the requested module
                    $module = parseURL(1); // Get module name from URL
                    if ($module && $module !== 'api') { // Skip for 'api' module itself
                         $moduleCheck = $this->db('mlite_modules')->where('dir', $module)->oneArray();
                         if ($moduleCheck) {
                             $assignedUsers = $this->getUserInfo('access', $user['id']); // Assuming 'access' field stores assigned modules
                             if ($assignedUsers !== 'all' && !in_array($module, explode(',', $assignedUsers))) {
                                 http_response_code(403);
                                 echo json_encode(['status' => 'error', 'message' => 'User access denied for this module']);
                                 exit;
                             }
                         }
                    }

                    return $user['username'];
                } else {
                    http_response_code(401);
                    echo json_encode(['status' => 'error', 'message' => 'Invalid User Permission Credentials']);
                    exit;
                }
            }
            
            return $keyRecord['username'];
        }
        
        // 2. Try Session (Internal)
        if (isset($_SESSION['mlite_user'])) {
             return $this->getUserInfo('username');
        }

        // 3. Unauthorized
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    public function checkPermission($username, $action, $module)
    {
        $user = $this->db('mlite_users')->where('username', $username)->oneArray();
        if ($user && $user['role'] == 'admin') {
            return true;
        }

        $mlite_crud_permissions = $this->db('mlite_crud_permissions')
            ->where('module', $module)
            ->where('user', $username)
            ->oneArray();
            
        if (!$mlite_crud_permissions) {
            return true; 
        }
        
        return isset($mlite_crud_permissions[$action]) && $mlite_crud_permissions[$action] == 'true';
    }

}
