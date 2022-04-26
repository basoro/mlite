<?php

namespace Plugins\Lisensi;

use Systems\SiteModule;
use Systems\MySQL;

class Site extends SiteModule
{
    public function routes()
    {
        $this->route('datars', 'getIndex');
        $this->route('datars/random', 'getIndexRandom');
        $this->route('datars/save', 'postSave');
        $this->route('datars/cek/(:str)', 'postCek');
        $this->route('datars/aktif', 'postAktif');
    }

    public function getIndex()
    {
        $this->_addHeaderFiles();
        $page = [
            'title' => 'Pengguna',
            'desc' => 'Data pengguna aplikasi KhanzaLITE',
            'content' => $this->draw('index.html', ['list' => $this->mysql('mlite_data_rs')->toArray()])
        ];

        $this->setTemplate('index.html');
        $this->tpl->set('page', $page);

    }

    public function getIndexRandom()
    {
        echo $this->mysql('mlite_data_rs')->select('nama_instansi')->rand()->toJson();
        exit();
    }

    public function postSave()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST');
        header("Access-Control-Allow-Headers: X-Requested-With");

        ini_set('display_errors', 0);
        error_reporting(E_ERROR | E_WARNING | E_PARSE);

        $cek = $this->mysql('mlite_data_rs')->where('email', $_POST['email'])->oneArray();

        if(!$cek) {
            $this->mysql('mlite_data_rs')->save([
                'id' => '',
                'nama_instansi' => $_POST['nama_instansi'],
                'alamat_instansi' => $_POST['alamat_instansi'],
                'kabupaten' => $_POST['kabupaten'],
                'propinsi' => $_POST['propinsi'],
                'kontak' => $_POST['kontak'],
                'email' => $_POST['email'],
                'kode_lisensi' => ''
            ]);
        } else {
            $this->mysql('mlite_data_rs')
            ->where('email', $_POST['email'])
            ->update([
                'nama_instansi' => $_POST['nama_instansi'],
                'alamat_instansi' => $_POST['alamat_instansi'],
                'kabupaten' => $_POST['kabupaten'],
                'propinsi' => $_POST['propinsi'],
                'kontak' => $_POST['kontak'],
            ]);
        }
        exit();
    }

    public function postCek($kode_lisensi)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST');
        header("Access-Control-Allow-Headers: X-Requested-With");

        ini_set('display_errors', 0);
        error_reporting(E_ERROR | E_WARNING | E_PARSE);

        $result = $this->mysql('mlite_data_rs')->where('kode_lisensi', $kode_lisensi)->oneArray();
        if($result == 0) {
          $data['status'] = 'unverified';
          echo json_encode($data);
        } else {
            $data['status'] = 'verified';
            $data['kode_lisensi'] = $result["kode_lisensi"];
            $data['nama_instansi'] = $result["nama_instansi"];
            $data['alamat_instansi'] = $result["alamat_instansi"];
            $data['kabupaten'] = $result["kabupaten"];
            $data['propinsi'] = $result["propinsi"];
            $data['kontak'] = $result["kontak"];
            $data['email'] = $result["email"];
            echo json_encode($data);
        }

        exit();
    }

    public function postAktif()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST');
        header("Access-Control-Allow-Headers: X-Requested-With");

        ini_set('display_errors', 0);
        error_reporting(E_ERROR | E_WARNING | E_PARSE);

        $result = $this->mysql('mlite_data_rs')->where('email', $_POST['email'])->oneArray();
        if($result == 0) {
          $data['status'] = 'error';
          echo json_encode($data);
        } else {
            $data['status'] = 'Ok';
            $this->mysql('mlite_data_rs')->where('email', $_POST['email'])->update(['kode_lisensi' => MD5($_POST['email'])]);
            $this->sendEmail($_POST['email'], MD5($_POST['email']));
            echo json_encode($data);
        }

        exit();
    }

    private function sendEmail($email, $number)
  	{

  		$temp  = @file_get_contents(MODULES."/lisensi/email/daftar.html");

  		$temp  = str_replace("{SITENAME}", 'KhanzaLITE', $temp);
  		$temp  = str_replace("{NUMBER}", $number, $temp);

  		$smtp  = new \Systems\Lib\Smtp(
  			'ssl://smtp.gmail.com',
  			'465',
  			true,
  			'no-reply@basoro.org',
  			'415Basoro'
  		);

  		$smtp->debug = false;
  		$smtp->sendMail($email, 'no-reply@basoro.org', "Kode validasi KhanzaLITE", $temp, "HTML");
  	}

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
    }

    protected function mysql($table = NULL)
    {
        return new MySQL($table);
    }

}
