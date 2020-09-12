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
        $this->route('epasien/booking/pilih', 'getBooking');
        $this->route('epasien/booking/riwayat', 'getBookingRiwayat');
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
        $this->route('epasien/register', 'getRegister');
        $this->route('epasien/lostpassword', 'getLostPassword');
        $this->route('epasien/saveregister', 'postSaveRegister');
        $this->route('epasien/sendmail', 'getSendmail');
        $this->route('epasien/logout', function () {
            $this->logout();
        });
    }

    public function getIndex()
    {
        $opensimrs = $this->opensimrs;
        $page['title']               = 'Tanda Vital Terakhir';
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

            $antrian = $this->db('reg_periksa')
              ->select([
                'tgl_registrasi' => 'reg_periksa.tgl_registrasi',
                'jam_reg' => 'reg_periksa.jam_reg',
                'nm_poli' => 'poliklinik.nm_poli',
                'nm_pasien' => 'pasien.nm_pasien',
                'nm_dokter' => 'dokter.nm_dokter',
                'no_reg' => 'reg_periksa.no_reg',
                'stts' => 'reg_periksa.stts'
              ])
              ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
              ->join('dokter', 'dokter.kd_dokter = reg_periksa.kd_dokter')
              ->join('penjab', 'penjab.kd_pj = reg_periksa.kd_pj')
              ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
              ->where('reg_periksa.tgl_registrasi', date('Y-m-d'))
              ->where('reg_periksa.no_rkm_medis', $_SESSION['opensimrs_pasien_user'])
              ->oneArray();

            $page['content'] = $this->draw('index.html', [
              'page' => $page,
              'opensimrs' => $opensimrs,
              'reg_periksa' => $this->db('reg_periksa')->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')->join('dokter', 'dokter.kd_dokter = reg_periksa.kd_dokter')->where('reg_periksa.no_rkm_medis', $_SESSION['opensimrs_pasien_user'])->desc('reg_periksa.tgl_registrasi')->limit('5')->toArray(),
              'tanda_vital' => $this->db('pemeriksaan_ralan')->join('reg_periksa', 'reg_periksa.no_rawat = pemeriksaan_ralan.no_rawat')->where('reg_periksa.no_rkm_medis', $_SESSION['opensimrs_pasien_user'])->desc('tgl_perawatan')->limit('1')->oneArray(),
              'tekanan_darah' => $this->db('pemeriksaan_ralan')->select('tensi')->join('reg_periksa', 'reg_periksa.no_rawat = pemeriksaan_ralan.no_rawat')->where('reg_periksa.no_rkm_medis', $_SESSION['opensimrs_pasien_user'])->asc('tgl_perawatan')->limit('6')->toArray(),
              'pemeriksaan_ralan' => $this->db('pemeriksaan_ralan')->select('tgl_perawatan')->select('keluhan')->select('pemeriksaan')->join('reg_periksa', 'reg_periksa.no_rawat = pemeriksaan_ralan.no_rawat')->where('reg_periksa.no_rkm_medis', $_SESSION['opensimrs_pasien_user'])->desc('tgl_perawatan')->limit('3')->toArray(),
              'antrian' => $antrian,
              'bookingURL' => url().'/epasien/booking/pilih?t='.$_SESSION['opensimrs_pasien_token']
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

    public function getBookingRiwayat()
    {
        $opensimrs = $this->opensimrs;

        if ($this->_loginCheck()) {
            $page['title']               = 'Riwayat Booking';
            $page['desc']                = 'Dashboard SIMKES Khanza untuk Pasien';
            $page['content']             = $this->draw('booking.riwayat.html', ['page' => $page, 'opensimrs' => $opensimrs]);
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
            $page['content']             = $this->draw('riwayat.periksa.html', ['page' => $page, 'opensimrs' => $opensimrs]);
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
            $page['content']             = $this->draw('surat.sakit.html', ['page' => $page, 'opensimrs' => $opensimrs]);
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
            $page['content']             = $this->draw('surat.hamil.html', ['page' => $page, 'opensimrs' => $opensimrs]);
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
            $page['content']             = $this->draw('surat.narkoba.html', ['page' => $page, 'opensimrs' => $opensimrs]);
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
            $page['content']             = $this->draw('surat.kontrol.html', ['page' => $page, 'opensimrs' => $opensimrs]);
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
            $page['content']             = $this->draw('surat.rujukan.html', ['page' => $page, 'opensimrs' => $opensimrs]);
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
            $page['content']             = $this->draw('surat.covid.html', ['page' => $page, 'opensimrs' => $opensimrs]);
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
            $tarif_kamar                 = $this->db('bangsal')->join('kamar', 'kamar.kd_bangsal = bangsal.kd_bangsal')->where('kamar.status', '1')->toArray();
            $page['content']             = $this->draw('tarif.kamar.html', ['page' => $page, 'opensimrs' => $opensimrs, 'tarif_kamar' => $tarif_kamar]);
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
            $tarif_radiologi             = $this->db('jns_perawatan_radiologi')->join('penjab', 'penjab.kd_pj=jns_perawatan_radiologi.kd_pj')->where('jns_perawatan_radiologi.status', '1')->toArray();
            $page['content']             = $this->draw('tarif.radiologi.html', ['page' => $page, 'opensimrs' => $opensimrs, 'tarif_radiologi' => $tarif_radiologi]);
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
            $tarif_laboratorium          = $this->db('jns_perawatan_lab')->join('penjab', 'penjab.kd_pj=jns_perawatan_lab.kd_pj')->where('jns_perawatan_lab.status', '1')->toArray();
            $page['content']             = $this->draw('tarif.laboratorium.html', ['page' => $page, 'opensimrs' => $opensimrs, 'tarif_laboratorium' => $tarif_laboratorium]);
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
            $page['content']             = $this->draw('tarif.operasi.html', ['page' => $page, 'opensimrs' => $opensimrs]);
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
            $page['content']             = $this->draw('tarif.konsultasi.html', ['page' => $page, 'opensimrs' => $opensimrs]);
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
            $page['content']             = $this->draw('tarif.poliklinik.html', ['page' => $page, 'opensimrs' => $opensimrs]);
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
            $page['content']             = $this->draw('tarif.asuransi.html', ['page' => $page, 'opensimrs' => $opensimrs]);
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
            $page['content']             = $this->draw('jadwal.dokter.html', ['page' => $page, 'opensimrs' => $opensimrs]);
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
            $page['content']             = $this->draw('ketersediaan.kamar.html', ['page' => $page, 'opensimrs' => $opensimrs]);
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
            $page['content']             = $this->draw('pengaduan.html', ['page' => $page, 'opensimrs' => $opensimrs]);
        } else {
            redirect(url('epasien'));
        }

        echo $page['content'];
        exit();
    }

    public function getLostPassword()
    {
        $opensimrs = $this->opensimrs;

        $page['title']               = 'Lost Password Pasien';
        $page['desc']                = 'Dashboard SIMKES Khanza untuk Pasien';
        $page['content']             = $this->draw('lostpassword.html', ['page' => $page, 'opensimrs' => $opensimrs]);

        echo $page['content'];
        exit();
    }

    public function getFindpass()
    {
  		if(isset($_SESSION['sendmail']) && time() - $_SESSION['sendmail'] <= 60)
        return Array("status" => false, "message" => "Your operation frequency is too high, please try again later");
  		if($data['username'] == "")
        return Array("status" => false, "message" => "Please fill in the account or email address where the password is retrieved");

  		$rs     = $this->getInfoByUser($data['username']);
  		$link   = sha1(md5($rs['username'] . $rs['password'] . time() . mt_rand(0, 9999999)) . md5(mt_rand(0, 9999999)));
  		$found = false;

  		if($rs) {
  			$this->sendFindpassEmail($rs['username'], $rs['email'], $link);
  			$found = true;
  		} else {
  			$rs = $this->getInfoByEmail($data['username']);
  			if($rs) {
  				$this->sendFindpassEmail($rs['username'], $rs['email'], $link);
  				$found = true;
  			}
  		}

  		if($found) {
  			$ms = Database::querySingleLine("findpass", Array("username" => $rs['username']));
  			if($ms) {
  				Database::delete("findpass", Array("username" => $rs['username']));
  			}
  			Database::insert("findpass", Array(
  				"username" => $rs['username'],
  				"link"     => $link,
  				"time"     => time()
  			));
  		}

  		$_SESSION['sendmail'] = time();

  		return Array("status" => true, "message" => "We have tried to send an email to the mailbox of this account, please check.");
  	}

  	public function sendFindpassEmail($username, $email, $link)
  	{

  		$type  = $this->isHttps() ? "https" : "http";
  		$token = $this->getUserToken($username);
  		$url   = "{$type}://{$_SERVER['SERVER_NAME']}/?page=findpass&link={$link}";
  		$temp  = @file_get_contents(ROOT . "/assets/email/findpass.html");

  		$temp  = str_replace("{SITENAME}", $_config['sitename'], $temp);
  		$temp  = str_replace("{SITEDESCRIPTION}", $_config['description'], $temp);
  		$temp  = str_replace("{USERNAME}", $username, $temp);
  		$temp  = str_replace("{TOKEN}", $token, $temp);
  		$temp  = str_replace("{URL}", $url, $temp);

  		$smtp  = new \Systems\Lib\Smtp(
  			$_config['smtp']['host'],
  			$_config['smtp']['port'],
  			true,
  			$_config['smtp']['user'],
  			$_config['smtp']['pass']
  		);

  		$smtp->debug = false;
  		$smtp->sendMail($email, $_config['smtp']['mail'], "Find your {$_config['sitename']} password", $temp, "HTML");
  	}

    public function getRegister()
    {
        $opensimrs = $this->opensimrs;

        $page['title']               = 'Pendaftaran Pasien';
        $page['desc']                = 'Dashboard SIMKES Khanza untuk Pasien';
        $page['content']             = $this->draw('register.html', ['page' => $page, 'opensimrs' => $opensimrs]);

        echo $page['content'];
        exit();
    }

    public function getSendmail()
    {
        if(isset($_SESSION['reg_wait'])) {
          if(time() - $_SESSION['reg_wait'] < 60) {
            exit("Your operation is too frequent. Please try again later.");
          }
        }
        if(!isset($_POST['email']) || $_POST['email'] == "") {
          exit("Please enter your email!");
        }
        if(!$this->checkEmail($_POST['email'])) {
          exit("Incorrect email format!");
        }
        $rand = mt_rand(100000, 999999);
        $_SESSION['reg_verifycode'] = $rand;
        $_SESSION['reg_wait'] = time();
        $_SESSION['reg_email'] = $_POST['email'];

        $this->sendRegisterEmail($_POST['email'], $rand);
        exit("An email has been sent to your mailbox, please check it.");

    }

    private function sendRegisterEmail($email, $number)
  	{

  		$temp  = @file_get_contents(MODULES."/epasien/email/welcome.html");

  		$temp  = str_replace("{SITENAME}", $this->core->getSettings('nama_instansi'), $temp);
      $temp  = str_replace("{ADDRESS}", $this->core->getSettings('alamat_instansi')." - ".$this->core->getSettings('kabupaten'), $temp);
      $temp  = str_replace("{TELP}", $this->core->getSettings('kontak'), $temp);
  		$temp  = str_replace("{NUMBER}", $number, $temp);

  		$smtp  = new \Systems\Lib\Smtp(
  			$this->options->get('epasien.smtp_host'),
  			$this->options->get('epasien.smtp_port'),
  			true,
  			$this->options->get('epasien.smtp_username'),
  			$this->options->get('epasien.smtp_password')
  		);

  		$smtp->debug = false;
  		$smtp->sendMail($email, $this->core->getSettings('email'), "Verifikasi pendaftaran anda di ".$this->core->getSettings('nama_instansi'), $temp, "HTML");
  	}

    private function checkEmail($email)
  	{
  		return preg_match("/^\w[-\w.+]*@([A-Za-z0-9][-A-Za-z0-9]+\.)+[A-Za-z]{2,48}$/", $email) ? true : false;
  	}

    public function postSaveRegister()
    {
        $opensimrs = $this->opensimrs;

    		if(!isset($_POST['email']) || !isset($_POST['no_ktp']) || empty($_POST['email']) || empty($_POST['no_ktp'])) {
          $this->notify('failure', 'Please complete the information');
          redirect(url(['epasien', 'register']));
    		}

  			if(!isset($_POST['verifycode']) || empty($_POST['verifycode'])) {
          $this->notify('failure', 'Please enter verification code');
          redirect(url(['epasien', 'register']));
  			} else {
  				if(!isset($_SESSION['reg_verifycode']) || $_SESSION['reg_verifycode'] == "") {
            $this->notify('failure', 'The verification code has expired, please get the email again');
            redirect(url(['epasien', 'register']));
  				}
  				if(isset($_SESSION['reg_wait'])) {
  					if(time() - $_SESSION['reg_wait'] > 900) {
              $this->notify('failure', 'The verification code has expired, please get the email again');
              redirect(url(['epasien', 'register']));
  					}
  				}
  				if($_SESSION['reg_email'] !== $_POST['email']) {
            $this->notify('failure', 'Please re-verify the email address before you can register');
            redirect(url(['epasien', 'register']));
  				}
  				if(Intval($_SESSION['reg_verifycode']) !== Intval($_POST['verifycode'])) {
            $this->notify('failure', 'The verification code is wrong, please check');
            redirect(url(['epasien', 'register']));
  				}
  			}

        $output = file_get_contents("https://bpjs.basoro.id/ktp.php?no=".$_POST['no_ktp']);
        $output = json_decode($output, true);

        if($output['metaData']['message'] !== 'OK') {
          $this->notify('failure', 'Nomor Induk Kependudukan anda tidak terdaftar dalam database '.$this->core->getSettings('nama_instansi'));
          redirect(url(['epasien', 'register']));
    		}

        unset($_POST['save']);
        unset($_POST['verifycode']);
        unset($_POST['no_ktp']);

    		// Perform registration
        $_POST['no_rkm_medis'] = $this->core->setNoRM();
        $_POST['nm_pasien'] = $output['response']['peserta']['nama'];
        $_POST['no_ktp'] = $output['response']['peserta']['nik'];
        $_POST['jk'] = $output['response']['peserta']['sex'];
        $_POST['tmp_lahir'] = '-';
        $_POST['tgl_lahir'] = $output['response']['peserta']['tglLahir'];
        $_POST['nm_ibu'] = '-';
        $_POST['alamat'] = '-';
        $_POST['gol_darah'] = '-';
        $_POST['pekerjaan'] = $output['response']['peserta']['jenisPeserta']['keterangan'];
        $_POST['stts_nikah'] = 'JOMBLO';
        $_POST['agama'] = '-';
        $_POST['tgl_daftar'] = date('Y-m-d');
        $_POST['no_tlp'] = $output['response']['peserta']['mr']['noTelepon'];
        $_POST['umur'] = $this->_setUmur($output['response']['peserta']['tglLahir']);;
        $_POST['pnd'] = '-';
        $_POST['keluarga'] = 'AYAH';
        $_POST['namakeluarga'] = '-';
        $_POST['kd_pj'] = $this->options->get('pendaftaran.bpjs');
        $_POST['no_peserta'] = $output['response']['peserta']['noKartu'];
        $_POST['kd_kel'] = $this->options->get('epasien.kdkel');
        $_POST['kd_kec'] = $this->options->get('epasien.kdkec');
        $_POST['kd_kab'] = $this->options->get('epasien.kdkab');
        $_POST['pekerjaanpj'] = '-';
        $_POST['alamatpj'] = '-';
        $_POST['kelurahanpj'] = '-';
        $_POST['kecamatanpj'] = '-';
        $_POST['kabupatenpj'] = '-';
        $_POST['perusahaan_pasien'] = $this->options->get('epasien.perusahaan_pasien');
        $_POST['suku_bangsa'] = $this->options->get('epasien.suku_bangsa');
        $_POST['bahasa_pasien'] = $this->options->get('epasien.bahasa_pasien');
        $_POST['cacat_fisik'] = $this->options->get('epasien.cacat_fisik');
        //$_POST['email'] = '';
        $_POST['nip'] = '';
        $_POST['kd_prop'] = $this->options->get('epasien.kdprop');
        $_POST['propinsipj'] = '-';

        $gambar = '';
        $query = $this->db('pasien')->save($_POST);
        $this->core->db()->pdo()->exec("INSERT INTO `personal_pasien` (`no_rkm_medis`, `gambar`, `password`) VALUES ('{$_POST['no_rkm_medis']}', '$gambar', AES_ENCRYPT('{$_POST['no_rkm_medis']}','windi'))");
        $this->core->db()->pdo()->exec("UPDATE set_no_rkm_medis SET no_rkm_medis='$_POST[no_rkm_medis]'");
        if($query) {
            $this->notify('success', 'Account registration is successful! Please login immediately..');
        }else{
            $this->notify('failure', 'Account registration is unsuccessful! Please re-register..');
        }
        redirect(url(['epasien', '']));
        exit();

    }

    private function isHttps()
  	{
  		if (!isset($_SERVER['HTTPS'])) {
  			return false;
  		}
  		if ($_SERVER['HTTPS'] === 1) {
  			return true;
  		} elseif ($_SERVER['HTTPS'] === 'on') {
  			return true;
  		} elseif ($_SERVER['SERVER_PORT'] == 443) {
  			return true;
  		} elseif ($_SERVER['REQUEST_SCHEME'] == "https") {
  			return true;
  		}
  		return false;
  	}

    private function _setUmur($tanggal)
    {
        list($cY, $cm, $cd) = explode('-', date('Y-m-d'));
        list($Y, $m, $d) = explode('-', date('Y-m-d', strtotime($tanggal)));
        $umur = $cY - $Y;
        return $umur;
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
        //$_SESSION = [];
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
