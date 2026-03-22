<?php

namespace Plugins\Api;

use Systems\AdminModule;

class Admin extends AdminModule
{
    public $assign = [];
    public function navigation()
    {
        return [
            'Kelola' => 'manage',
            'Notifikasi APAM' => 'notifikasi',
            'Pengaturan APAM' => 'settingsapam',
            'Payment Duitku' => 'paymentduitku',
            'Pengaturan API Key' => 'settingskey',
        ];
    }

    public function getManage()
    {
      $sub_modules = [
        ['name' => 'Notifikasi APAM', 'url' => url([ADMIN, 'api', 'notifikasi']), 'icon' => 'database', 'desc' => 'Notifikasi APAM API'],
        ['name' => 'Pengaturan APAM', 'url' => url([ADMIN, 'api', 'settingsapam']), 'icon' => 'database', 'desc' => 'Pengaturan APAM API'],
        ['name' => 'Payment Duitku', 'url' => url([ADMIN, 'api', 'paymentduitku']), 'icon' => 'database', 'desc' => 'Pengaturan e-Payment API'],
        ['name' => 'Pengaturan API Key', 'url' => url([ADMIN, 'api', 'settingskey']), 'icon' => 'database', 'desc' => 'Pengaturan API Key'],
      ];
      return $this->draw('manage.html', ['sub_modules' => htmlspecialchars_array($sub_modules)]);
    }

    public function getNotifikasi()
    {

      $totalRecords = $this->db('mlite_notifications')
        ->select('id')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('mlite_notifications')
        ->join('pasien', 'pasien.no_rkm_medis=mlite_notifications.no_rkm_medis')
        ->desc('id')
        ->limit(10)
        ->toArray();

      $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
      $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
      $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));

      $this->core->addJS(url([ADMIN, 'api', 'notifikasijs']), 'footer');
      return $this->draw('notifikasi.html', [
        'notifikasi' => $return
      ]);

    }

    public function anyNotifikasiForm()
    {
        if (isset($_POST['id'])){
          $return['form'] = $this->db('mlite_notifications')->where('id', $_POST['id'])->oneArray();
          if ($return['form']) {
              $return['form'] = htmlspecialchars_array($return['form']);
          }
        } else {
          $return['form'] = [
            'id' => '',
            'judul' => '',
            'pesan' => '',
            'tanggal' => '',
            'no_rkm_medis' => '',
            'status' => ''
          ];
        }

        echo $this->draw('notifikasi.form.html', ['notifikasi' => htmlspecialchars_array($return)]);
        exit();
    }

    public function anyNotifikasiDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('mlite_notifications')
          ->select('id')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $raw_list = $this->db('mlite_notifications')
          ->join('pasien', 'pasien.no_rkm_medis=mlite_notifications.no_rkm_medis')
          ->desc('id')
          ->offset(0)
          ->limit($perpage)
          ->toArray();
        $return['list'] = [];
        foreach($raw_list as $r) {
            $return['list'][] = htmlspecialchars_array($r);
        }

        if(isset($_POST['cari'])) {
          $raw_list = $this->db('mlite_notifications')
            ->join('pasien', 'pasien.no_rkm_medis=mlite_notifications.no_rkm_medis')
            ->like('id', '%'.$_POST['cari'].'%')
            ->orLike('judul', '%'.$_POST['cari'].'%')
            ->desc('id')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $return['list'] = [];
          foreach($raw_list as $r) {
              $return['list'][] = htmlspecialchars_array($r);
          }
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $raw_list = $this->db('mlite_notifications')
            ->join('pasien', 'pasien.no_rkm_medis=mlite_notifications.no_rkm_medis')
            ->desc('id')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['list'] = [];
          foreach($raw_list as $r) {
              $return['list'][] = htmlspecialchars_array($r);
          }
          $return['halaman'] = (int)$_POST['halaman'];
        }

        echo $this->draw('notifikasi.display.html', ['notifikasi' => htmlspecialchars_array($return)]);
        exit();
    }

    public function postNotifikasiSave()
    {
      if (!$this->db('mlite_notifications')->where('id', $_POST['id'])->oneArray()) {
        $_POST['status'] = 'unread';
        $query = $this->db('mlite_notifications')->save($_POST);
      } else {
        $query = $this->db('mlite_notifications')->where('id', $_POST['id'])->save($_POST);
      }
      return $query;
    }

    public function postNotifikasiHapus()
    {
      return $this->db('mlite_notifications')->where('id', $_POST['id'])->delete();
    }

    public function getNotifikasiJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/api/js/admin/notifikasi.js');
        exit();
    }

    /* Settings Section */
    public function getSettingsApam()
    {
        $this->assign['title'] = 'Pengaturan Modul API';
        $this->assign['api'] = htmlspecialchars_array($this->settings('api'));
        $this->assign['penjab'] = $this->db('penjab')->where('status', '1')->toArray();
        return $this->draw('settings.apam.html', ['settings' => htmlspecialchars_array($this->assign)]);
    }

public function postSaveSettingsApam()
{
    $api = $_POST['api'] ?? [];

    // ---------- PROPINSI ----------
    if (!empty($api['apam_kdprop'])) {
        $cek_prop = $this->db('propinsi')
            ->select('*')
            ->where('kd_prop', $api['apam_kdprop'])
            ->limit(1)
            ->oneArray();

        if (!$cek_prop) {
            $this->db('propinsi')->save([
                'kd_prop' => $api['apam_kdprop'],
                'nm_prop' => $_POST['nm_prop'] ?? ''
            ]);
        }
    }

    // ---------- KABUPATEN ----------
    if (!empty($api['apam_kdkab'])) {
        $cek_kab = $this->db('kabupaten')
            ->select('*')
            ->where('kd_kab', $api['apam_kdkab'])
            ->limit(1)
            ->oneArray();

        if (!$cek_kab) {
            $this->db('kabupaten')->save([
                'kd_kab' => $api['apam_kdkab'],
                'nm_kab' => $_POST['nm_kab'] ?? ''
            ]);
        }
    }

    // ---------- KECAMATAN ----------
    if (!empty($api['apam_kdkec'])) {
        $cek_kec = $this->db('kecamatan')
            ->select('*')
            ->where('kd_kec', $api['apam_kdkec'])
            ->limit(1)
            ->oneArray();

        if (!$cek_kec) {
            $this->db('kecamatan')->save([
                'kd_kec' => $api['apam_kdkec'],
                'nm_kec' => $_POST['nm_kec'] ?? ''
            ]);
        }
    }

    foreach ($api as $key => $val) {
        $this->settings('api', $key, $val);
    }

    $this->notify('success', 'Pengaturan telah disimpan');
    redirect(url([ADMIN, 'api', 'settingsapam']));
}

    /* End Settings Farmasi Section */

    /* Settings Section */
    public function getSettingsKey()
    {
        $this->assign['title'] = 'Pengaturan Modul API Key';
        $this->assign['api'] = htmlspecialchars_array($this->settings('api'));
        return $this->draw('settings.key.html', ['settings' => htmlspecialchars_array($this->assign)]);
    }

    public function postSaveSettingsKey()
    {
        foreach ($_POST['api'] as $key => $val) {
            $this->settings('api', $key, $val);
        }
        $this->notify('success', 'Pengaturan telah disimpan');
        redirect(url([ADMIN, 'api', 'settingskey']));
    }
    /* End Settings Farmasi Section */

    private function isSafeUrl($url) {
        $parsed = parse_url($url);
        if (!$parsed || !isset($parsed['scheme']) || strtolower($parsed['scheme']) !== 'https') {
            return false;
        }
        $host = $parsed['host'] ?? '';
        $ips = gethostbynamel($host);
        if (!$ips) {
            return false;
        }
        foreach ($ips as $ip) {
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return false;
            }
        }
        return true;
    }

    public function postKirimWA()
    {
        // Clean output buffer to remove any prior echoes/warnings
        if (ob_get_length()) ob_clean();
        
        header('Content-Type: application/json');
        
        $waapitoken = $this->settings->get('wagateway.token');
        $waapiphonenumber = $this->settings->get('wagateway.phonenumber');
        $waapiserver = $this->settings->get('wagateway.server');
        
        if (empty($waapitoken) || empty($waapiserver)) {
             echo json_encode(['status' => false, 'msg' => 'WA Gateway belum dikonfigurasi']);
             exit();
        }

        $url = $waapiserver."/wagateway/kirimpesan";
        if (!$this->isSafeUrl($url)) {
             echo json_encode(['status' => false, 'msg' => 'Invalid or insecure WA Gateway URL']);
             exit();
        }
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
        curl_setopt($curlHandle, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTPS);
        curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, http_build_query([
            'type' => 'text',
            'sender' => $waapiphonenumber,
            'number' => $_POST['number'] ?? '',
            'message' => $_POST['message'] ?? '',
            'api_key' => $waapitoken
        ]));
        curl_setopt($curlHandle, CURLOPT_HEADER, 0);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT, 30);
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($curlHandle);
        
        if (curl_errno($curlHandle)) {
            $error_msg = curl_error($curlHandle);
            curl_close($curlHandle);
            echo json_encode(['status' => false, 'msg' => 'Curl Error: ' . $error_msg]);
            exit();
        }
        
        curl_close($curlHandle);
        
        // Check if response is empty
        if (empty($response)) {
             echo json_encode(['status' => false, 'msg' => 'Empty response from WA Gateway']);
             exit();
        }

        // Validate if response is JSON, if not return error
        $json = json_decode($response, true);
        if ($json === null && json_last_error() !== JSON_ERROR_NONE) {
             // Try to return the raw response or a generic error
             echo json_encode(['status' => false, 'msg' => 'Invalid response from WA Gateway: ' . substr(strip_tags($response), 0, 100)]);
             exit();
        }

        echo $response;
        exit();
    }

    public function postKirimWAMedia()
    {
        $waapitoken = $this->settings->get('wagateway.token');
        $waapiphonenumber = $this->settings->get('wagateway.phonenumber');
        $waapiserver = $this->settings->get('wagateway.server');
        $url = $waapiserver."/wagateway/kirimgambar";
        if (!$this->isSafeUrl($url)) {
             echo json_encode(['status' => false, 'msg' => 'Invalid or insecure WA Gateway URL']);
             exit();
        }
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
        curl_setopt($curlHandle, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTPS);
        curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, http_build_query([
            'type' => 'image',
            'sender' => $waapiphonenumber,
            'number' => $_POST['number'] ?? '',
            'message' => $_POST['message'] ?? '',
            'url' => $_POST['file'] ?? '',
            'api_key' => $waapitoken
        ]));
        curl_setopt($curlHandle, CURLOPT_HEADER, 0);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT,30);
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, true);
        $response = curl_exec($curlHandle);
        curl_close($curlHandle);
        echo $response;
        exit();
    }

    public function postKirimWADocument()
    {
        $waapitoken = $this->settings->get('wagateway.token');
        $waapiphonenumber = $this->settings->get('wagateway.phonenumber');
        $waapiserver = $this->settings->get('wagateway.server');
        $url = $waapiserver."/wagateway/kirimfile";
        if (!$this->isSafeUrl($url)) {
             echo json_encode(['status' => false, 'msg' => 'Invalid or insecure WA Gateway URL']);
             exit();
        }
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
        curl_setopt($curlHandle, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTPS);
        curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, http_build_query([
            'type' => 'document',
            'sender' => $waapiphonenumber,
            'number' => $_POST['number'] ?? '',
            'message' => $_POST['message'] ?? '',
            'url' => $_POST['file'] ?? '',
            'api_key' => $waapitoken
        ]));
        curl_setopt($curlHandle, CURLOPT_HEADER, 0);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT,30);
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, true);
        $response = curl_exec($curlHandle);
        curl_close($curlHandle);
        echo $response;
        exit();
    }

    public function getPaymentDuitku()
    {

      $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
      $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'), 'footer');
      $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'), 'footer');

      $return['list'] = $this->db('mlite_duitku')
        ->join('pasien', 'pasien.no_rkm_medis=mlite_duitku.no_rkm_medis')
        ->desc('id')
        ->limit(10)
        ->toArray();

      return $this->draw('payment.duitku.html', [
        'paymentduitku' => $return
      ]);

    }

}
