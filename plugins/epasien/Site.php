<?php

namespace Plugins\Epasien;

use Systems\SiteModule;

class Site extends SiteModule
{
    protected $opensimrs;
    protected $page;

    public function init()
    {
        $this->opensimrs['notify']         = $this->core->getNotify();
        $this->opensimrs['logo']           = $this->core->getSettings('logo');
        $this->opensimrs['nama_instansi']  = $this->core->getSettings('nama_instansi');
        $this->opensimrs['path']           = url();
        $this->opensimrs['version']        = $this->core->options->get('settings.version');
        $this->opensimrs['token']          = '  ';
        if ($this->_loginCheck()) {
            $this->opensimrs['avatarURL']      = url(MODULES.'/pasien/img/'.$this->core->getPasienInfo('jk', $_SESSION['opensimrs_pasien_user']).'.png');
            $this->opensimrs['nm_pasien']      = $this->core->getPasienInfo('nm_pasien', $_SESSION['opensimrs_pasien_user']);
            $this->opensimrs['no_rkm_medis']   = $this->core->getPasienInfo('no_rkm_medis', $_SESSION['opensimrs_pasien_user']);
            $this->opensimrs['token']          = $_SESSION['opensimrs_pasien_token'];
        }
        $this->opensimrs['slug']           = parseURL();
    }

    public function routes()
    {
        $this->route('epasien', 'getIndex');
        $this->route('epasien/booking', 'getBooking');
        $this->route('epasien/riwayat', 'getRiwayat');
        $this->route('epasien/surat/sakit', 'getSuratSakit');
        $this->route('epasien/surat/hamil', 'getSuratHamil');
        $this->route('epasien/surat/narkoba', 'getSuratNarkoba');
        $this->route('epasien/surat/kontrol', 'getSuratKontrol');
        $this->route('epasien/surat/rujukan', 'getSuratRujukan');
        $this->route('epasien/surat/covid', 'getSuratCovid');
        $this->route('epasien/tarif/kamar', 'getTarifKamar');
        $this->route('epasien/tarif/radiologi', 'getTarifRadiologi');
        $this->route('epasien/tarif/laboratorium', 'getTarifLaboratorium');
        $this->route('epasien/tarif/operasi', 'getTarifOperasi');
        $this->route('epasien/tarif/konsultasi', 'getTarifKonsultasi');
        $this->route('epasien/tarif/poliklinik', 'getTarifPoliklinik');
        $this->route('epasien/tarif/asuransi', 'getTarifAsuransi');
        $this->route('epasien/jadwal', 'getJadwal');
        $this->route('epasien/kamar', 'getKamar');
        $this->route('epasien/pengaduan', 'getPengaduan');
        $this->route('epasien/logout', function () {
            $this->logout();
        });
    }

    public function getIndex()
    {
        $opensimrs = $this->opensimrs;
        $page['title']               = 'Pemeriksaan Terakhir';
        $page['desc']                = 'Dashboard SIMKES Khanza untuk Pasien';
        $page['content']             = '';

        if ($this->_loginCheck()) {

            $day = array(
              'Sun' => 'AKHAD',
              'Mon' => 'SENIN',
              'Tue' => 'SELASA',
              'Wed' => 'RABU',
              'Thu' => 'KAMIS',
              'Fri' => 'JUMAT',
              'Sat' => 'SABTU'
            );
            $hari=$day[date('D',strtotime(date('Y-m-d')))];

            $settings = htmlspecialchars_array($this->options('dashboard'));

            $stats['tunai'] = $this->db('reg_periksa')->select(['count' => 'COUNT(DISTINCT no_rawat)'])->where('reg_periksa.no_rkm_medis', $_SESSION['opensimrs_pasien_user'])->where('kd_pj', $settings['umum'])->oneArray();
            $stats['bpjs'] = $this->db('reg_periksa')->select(['count' => 'COUNT(DISTINCT no_rawat)'])->where('reg_periksa.no_rkm_medis', $_SESSION['opensimrs_pasien_user'])->where('kd_pj', $settings['bpjs'])->oneArray();
            $stats['lainnya'] = $this->db('reg_periksa')->select(['count' => 'COUNT(DISTINCT no_rawat)'])->where('reg_periksa.no_rkm_medis', $_SESSION['opensimrs_pasien_user'])->where('kd_pj', '!=', $settings['umum'])->where('kd_pj', '!=', $settings['bpjs'])->oneArray();

            $page['content'] = $this->draw('index.html', [
              'page' => $page,
              'opensimrs' => $opensimrs,
              'stats' => $stats,
              'reg_periksa' => $this->db('reg_periksa')->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')->join('dokter', 'dokter.kd_dokter = reg_periksa.kd_dokter')->where('reg_periksa.no_rkm_medis', $_SESSION['opensimrs_pasien_user'])->desc('reg_periksa.tgl_registrasi')->limit('5')->toArray(),
              'pemeriksaan_ralan' => $this->db('pemeriksaan_ralan')->select('keluhan')->select('pemeriksaan')->desc('tgl_perawatan')->limit('6')->toArray()
            ]);
        } else {
            if (isset($_POST['login'])) {
                if ($this->_login($_POST['no_rkm_medis'], $_POST['password'], isset($_POST['remember_me']) )) {
                    if (count($arrayURL = parseURL()) > 1) {
                        $url = array_merge(['epasien'], $arrayURL);
                        redirect(url($url));
                    }
                }
                redirect(url(['epasien', '']));
            }
            $page['content'] = $this->draw('login.html', ['page' => $page, 'opensimrs' => $opensimrs]);
        }

        echo $page['content'];
        exit();
    }

    public function getBooking()
    {
        $opensimrs = $this->opensimrs;

        if ($this->_loginCheck()) {
            $page['title']               = 'Booking';
            $page['desc']                = 'Dashboard SIMKES Khanza untuk Pasien';
            $page['content']             = $this->draw('booking.html', ['page' => $page, 'opensimrs' => $opensimrs]);
        } else {
            redirect(url('epasien'));
        }

        echo $page['content'];
        exit();
    }

    public function getRiwayat()
    {
        $opensimrs = $this->opensimrs;

        if ($this->_loginCheck()) {
            $page['title']               = 'Riwayat';
            $page['desc']                = 'Dashboard SIMKES Khanza untuk Pasien';
            $page['content']             = $this->draw('booking.html', ['page' => $page, 'opensimrs' => $opensimrs]);
        } else {
            redirect(url('epasien'));
        }

        echo $page['content'];
        exit();
    }

    public function getSuratSakit()
    {
        $opensimrs = $this->opensimrs;

        if ($this->_loginCheck()) {
            $page['title']               = 'Surat Sakit';
            $page['desc']                = 'Dashboard SIMKES Khanza untuk Pasien';
            $page['content']             = $this->draw('booking.html', ['page' => $page, 'opensimrs' => $opensimrs]);
        } else {
            redirect(url('epasien'));
        }

        echo $page['content'];
        exit();
    }

    public function getSuratHamil()
    {
        $opensimrs = $this->opensimrs;

        if ($this->_loginCheck()) {
            $page['title']               = 'Surat Hamil';
            $page['desc']                = 'Dashboard SIMKES Khanza untuk Pasien';
            $page['content']             = $this->draw('booking.html', ['page' => $page, 'opensimrs' => $opensimrs]);
        } else {
            redirect(url('epasien'));
        }

        echo $page['content'];
        exit();
    }

    public function getSuratNarkoba()
    {
        $opensimrs = $this->opensimrs;

        if ($this->_loginCheck()) {
            $page['title']               = 'Surat Narkoba';
            $page['desc']                = 'Dashboard SIMKES Khanza untuk Pasien';
            $page['content']             = $this->draw('booking.html', ['page' => $page, 'opensimrs' => $opensimrs]);
        } else {
            redirect(url('epasien'));
        }

        echo $page['content'];
        exit();
    }

    public function getSuratKontrol()
    {
        $opensimrs = $this->opensimrs;

        if ($this->_loginCheck()) {
            $page['title']               = 'Surat Kontrol/SKDP';
            $page['desc']                = 'Dashboard SIMKES Khanza untuk Pasien';
            $page['content']             = $this->draw('booking.html', ['page' => $page, 'opensimrs' => $opensimrs]);
        } else {
            redirect(url('epasien'));
        }

        echo $page['content'];
        exit();
    }

    public function getSuratRujukan()
    {
        $opensimrs = $this->opensimrs;

        if ($this->_loginCheck()) {
            $page['title']               = 'Surat Rujukan';
            $page['desc']                = 'Dashboard SIMKES Khanza untuk Pasien';
            $page['content']             = $this->draw('booking.html', ['page' => $page, 'opensimrs' => $opensimrs]);
        } else {
            redirect(url('epasien'));
        }

        echo $page['content'];
        exit();
    }

    public function getSuratCovid()
    {
        $opensimrs = $this->opensimrs;

        if ($this->_loginCheck()) {
            $page['title']               = 'Surat Covid-19';
            $page['desc']                = 'Dashboard SIMKES Khanza untuk Pasien';
            $page['content']             = $this->draw('booking.html', ['page' => $page, 'opensimrs' => $opensimrs]);
        } else {
            redirect(url('epasien'));
        }

        echo $page['content'];
        exit();
    }

    public function getTarifKamar()
    {
        $opensimrs = $this->opensimrs;

        if ($this->_loginCheck()) {
            $page['title']               = 'Fasilitas & Tarif Kamar';
            $page['desc']                = 'Dashboard SIMKES Khanza untuk Pasien';
            $page['content']             = $this->draw('booking.html', ['page' => $page, 'opensimrs' => $opensimrs]);
        } else {
            redirect(url('epasien'));
        }

        echo $page['content'];
        exit();
    }

    public function getTarifRadiologi()
    {
        $opensimrs = $this->opensimrs;

        if ($this->_loginCheck()) {
            $page['title']               = 'Fasilitas & Tarif Radiologi';
            $page['desc']                = 'Dashboard SIMKES Khanza untuk Pasien';
            $page['content']             = $this->draw('booking.html', ['page' => $page, 'opensimrs' => $opensimrs]);
        } else {
            redirect(url('epasien'));
        }

        echo $page['content'];
        exit();
    }

    public function getTarifLaboratorium()
    {
        $opensimrs = $this->opensimrs;

        if ($this->_loginCheck()) {
            $page['title']               = 'Fasilitas & Tarif Laboratorium';
            $page['desc']                = 'Dashboard SIMKES Khanza untuk Pasien';
            $page['content']             = $this->draw('booking.html', ['page' => $page, 'opensimrs' => $opensimrs]);
        } else {
            redirect(url('epasien'));
        }

        echo $page['content'];
        exit();
    }

    public function getTarifOperasi()
    {
        $opensimrs = $this->opensimrs;

        if ($this->_loginCheck()) {
            $page['title']               = 'Fasilitas & Tarif Operasi';
            $page['desc']                = 'Dashboard SIMKES Khanza untuk Pasien';
            $page['content']             = $this->draw('booking.html', ['page' => $page, 'opensimrs' => $opensimrs]);
        } else {
            redirect(url('epasien'));
        }

        echo $page['content'];
        exit();
    }

    public function getTarifKonsultasi()
    {
        $opensimrs = $this->opensimrs;

        if ($this->_loginCheck()) {
            $page['title']               = 'Fasilitas & Tarif Konsultasi Online';
            $page['desc']                = 'Dashboard SIMKES Khanza untuk Pasien';
            $page['content']             = $this->draw('booking.html', ['page' => $page, 'opensimrs' => $opensimrs]);
        } else {
            redirect(url('epasien'));
        }

        echo $page['content'];
        exit();
    }

    public function getTarifPoliklinik()
    {
        $opensimrs = $this->opensimrs;

        if ($this->_loginCheck()) {
            $page['title']               = 'Fasilitas & Tarif Poliklinik';
            $page['desc']                = 'Dashboard SIMKES Khanza untuk Pasien';
            $page['content']             = $this->draw('booking.html', ['page' => $page, 'opensimrs' => $opensimrs]);
        } else {
            redirect(url('epasien'));
        }

        echo $page['content'];
        exit();
    }

    public function getTarifAsuransi()
    {
        $opensimrs = $this->opensimrs;

        if ($this->_loginCheck()) {
            $page['title']               = 'Fasilitas & Tarif Asuransi';
            $page['desc']                = 'Dashboard SIMKES Khanza untuk Pasien';
            $page['content']             = $this->draw('booking.html', ['page' => $page, 'opensimrs' => $opensimrs]);
        } else {
            redirect(url('epasien'));
        }

        echo $page['content'];
        exit();
    }

    public function getJadwal()
    {
        $opensimrs = $this->opensimrs;

        if ($this->_loginCheck()) {
            $page['title']               = 'Jadwal Dokter';
            $page['desc']                = 'Dashboard SIMKES Khanza untuk Pasien';
            $page['content']             = $this->draw('booking.html', ['page' => $page, 'opensimrs' => $opensimrs]);
        } else {
            redirect(url('epasien'));
        }

        echo $page['content'];
        exit();
    }

    public function getKamar()
    {
        $opensimrs = $this->opensimrs;

        if ($this->_loginCheck()) {
            $page['title']               = 'Ketersediaan Kamar';
            $page['desc']                = 'Dashboard SIMKES Khanza untuk Pasien';
            $page['content']             = $this->draw('booking.html', ['page' => $page, 'opensimrs' => $opensimrs]);
        } else {
            redirect(url('epasien'));
        }

        echo $page['content'];
        exit();
    }

    public function getPengaduan()
    {
        $opensimrs = $this->opensimrs;

        if ($this->_loginCheck()) {
            $page['title']               = 'Pengaduan Pasien';
            $page['desc']                = 'Dashboard SIMKES Khanza untuk Pasien';
            $page['content']             = $this->draw('booking.html', ['page' => $page, 'opensimrs' => $opensimrs]);
        } else {
            redirect(url('epasien'));
        }

        echo $page['content'];
        exit();
    }

    private function _login($username, $password, $remember_me = false)
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
            $this->notify('failure', sprintf('Batas maksimum login tercapai. Tunggu %s menit untuk coba lagi.', ceil(($attempt['expires']-time())/60)));
            return false;
        }

        $row = $this->db()->pdo()->prepare("SELECT no_rkm_medis as id FROM personal_pasien WHERE no_rkm_medis = '$username' AND password = AES_ENCRYPT('$password','windi')");
        $row->execute();
        $row = $row->fetch();

        if ($row) {
            // Reset fail attempts for this IP
            $this->db('lite_login_attempts')->where('ip', $_SERVER['REMOTE_ADDR'])->save(['attempts' => 0]);

            $_SESSION['opensimrs_pasien_user']       = $row['id'];
            $_SESSION['opensimrs_pasien_token']      = bin2hex(openssl_random_pseudo_bytes(6));
            $_SESSION['pasien_userAgent']            = $_SERVER['HTTP_USER_AGENT'];
            $_SESSION['pasien_IPaddress']            = $_SERVER['REMOTE_ADDR'];

            if ($remember_me) {
                $token = str_gen(64, "1234567890qwertyuiop[]asdfghjkl;zxcvbnm,./");

                $this->db('lite_remember_me')->save(['user_id' => $row['id'], 'token' => $token, 'expiry' => time()+60*60*24*30]);

                setcookie('opensimrs_pasien_remember', $row['id'].':'.$token, time()+60*60*24*365, '/');
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

                $this->notify('failure', sprintf('Batas maksimum login tercapai. Tunggu %s menit untuk coba lagi.', ceil(($attempt['expires']-time())/60)));
            } else {
                $this->notify('failure', 'Username atau password salah!');
            }

            return false;
        }
    }

    private function _loginCheck()
    {
        if (isset($_SESSION['opensimrs_pasien_user']) && isset($_SESSION['opensimrs_pasien_token']) && isset($_SESSION['pasien_userAgent']) && isset($_SESSION['pasien_IPaddress'])) {
            if ($_SESSION['pasien_IPaddress'] != $_SERVER['REMOTE_ADDR']) {
                return false;
            }
            if ($_SESSION['pasien_userAgent'] != $_SERVER['HTTP_USER_AGENT']) {
                return false;
            }

            if (empty(parseURL(1))) {
                redirect(url('epasien'));
            } elseif (!isset($_GET['t']) || ($_SESSION['opensimrs_pasien_token'] != @$_GET['t'])) {
                return false;
            }

            return true;
        }

        return false;
    }

    private function logout()
    {
        $_SESSION = [];
        // Delete remember_me token from database and cookie
        if (isset($_COOKIE['opensimrs_pasien_remember'])) {
            $token = explode(':', $_COOKIE['opensimrs_pasien_remember']);
            $this->db('lite_remember_me')->where('user_id', $token[0])->where('token', $token[1])->delete();
            setcookie('opensimrs_pasien_remember', null, -1, '/');
        }

        unset($_SESSION['opensimrs_pasien_user']);
        unset($_SESSION['opensimrs_pasien_token']);
        unset($_SESSION['pasien_userAgent']);
        unset($_SESSION['pasien_IPaddress']);

        //session_unset();
        //session_destroy();
        redirect(url('epasien'));
    }

}
