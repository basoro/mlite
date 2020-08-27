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
        $this->opensimrs['token']          = '';
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
        $this->route('pasien', 'getIndex');
        $this->route('pasien/booking', 'getBooking');
        $this->route('pasien/riwayat', 'getRiwayat');
        $this->route('pasien/surat/sakit', 'getSuratSakit');
        $this->route('pasien/surat/hamil', 'getSuratHamil');
        $this->route('pasien/surat/narkoba', 'getSuratNarkoba');
        $this->route('pasien/surat/kontrol', 'getSuratKontrol');
        $this->route('pasien/surat/rujukan', 'getSuratRujukan');
        $this->route('pasien/surat/covid', 'getSuratCovid');
        $this->route('pasien/tarif/kamar', 'getIndex');
        $this->route('pasien/tarif/radiologi', 'getIndex');
        $this->route('pasien/tarif/laboratorium', 'getIndex');
        $this->route('pasien/tarif/operasi', 'getIndex');
        $this->route('pasien/tarif/konsultasi', 'getIndex');
        $this->route('pasien/tarif/poliklinik', 'getIndex');
        $this->route('pasien/tarif/asuransi', 'getIndex');
        $this->route('pasien/jadwal', 'getIndex');
        $this->route('pasien/kamar', 'getIndex');
        $this->route('pasien/pengaduan', 'getIndex');
        $this->route('pasien/logout', function () {
            $this->logout();
        });
    }

    public function getIndex()
    {
        $opensimrs = $this->opensimrs;
        $page['title']               = 'e-Pasien';
        $page['desc']                = 'Dashboard SIMKES Khanza untuk Pasien';
        $page['content']             = '';

        if ($this->_loginCheck()) {
            $page['content']             = $this->draw('index.html', ['page' => $page, 'opensimrs' => $opensimrs]);
        } else {
            if (isset($_POST['login'])) {
                if ($this->_login($_POST['no_rkm_medis'], $_POST['password'], isset($_POST['remember_me']) )) {
                    if (count($arrayURL = parseURL()) > 1) {
                        $url = array_merge(['pasien'], $arrayURL);
                        redirect(url($url));
                    }
                }
                redirect(url('pasien'));
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
            redirect(url('pasien'));
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
            redirect(url('pasien'));
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
            redirect(url('pasien'));
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
            redirect(url('pasien'));
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
            redirect(url('pasien'));
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
            redirect(url('pasien'));
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
            redirect(url('pasien'));
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
            redirect(url('pasien'));
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

        session_unset();
        session_destroy();
        redirect(url('pasien'));
    }

}
