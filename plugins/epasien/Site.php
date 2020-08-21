<?php

namespace Plugins\Epasien;

use Systems\SiteModule;

class Site extends SiteModule
{
    public function routes()
    {
        $this->route('pasien', 'getIndex');
        $this->route('pasien/logout', function () {
            $this->logout();
        });
    }

    public function getIndex()
    {
        $notify         = $this->core->getNotify();
        $logo           = $this->core->getSettings('logo');

        $page['title'] = 'e-Pasien';
        $page['desc'] = 'Dashboard SIMKES Khanza untuk Pasien';
        $page['content'] = $this->draw('login.html', ['notify' => $notify, 'logo' => $logo]);

        if ($this->_loginCheck()) {
            $page['content'] = $this->draw('index.html');
        } else {
            if (isset($_POST['login'])) {
                if ($this->_login($_POST['no_rkm_medis'], $_POST['password'])) {
                    redirect(url('pasien'));
                }
            } else {
                $page['content'] = $this->draw('login.html');
            }
        }

        $this->core->addCSS(url(MODULES.'/epasien/css/style.css'));
        $this->core->addJS(url(MODULES.'/epasien/js/app.js'));

        //echo $page['content'];
        //exit();
        $this->setTemplate('index.html');
        $this->tpl->set('page', $page);
    }

    private function _login($username, $password)
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
            $_SESSION['pasien_token']                = bin2hex(openssl_random_pseudo_bytes(6));
            $_SESSION['pasien_userAgent']            = $_SERVER['HTTP_USER_AGENT'];
            $_SESSION['pasien_IPaddress']            = $_SERVER['REMOTE_ADDR'];

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
        if (isset($_SESSION['opensimrs_pasien_user']) && isset($_SESSION['pasien_token']) && isset($_SESSION['pasien_userAgent']) && isset($_SESSION['pasien_IPaddress'])) {
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

        session_unset();
        session_destroy();
        redirect(url('pasien'));
    }

}
