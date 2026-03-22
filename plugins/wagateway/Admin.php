<?php
namespace Plugins\Wagateway;

use Systems\AdminModule;


class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Manage' => 'manage',
            'Send Message' => 'sendmessage',
            'Send Image' => 'sendimage',
            'Send File' => 'sendfile',
            'Settings' => 'settings'
        ];
    }

    public function getManage()
    {
      $waapiphonenumber = $this->settings->get('wagateway.phonenumber');
      $waapiserver = $this->settings->get('wagateway.server');
      $sub_modules = [
          ['name' => 'Send Message', 'url' => url([ADMIN, 'wagateway', 'sendmessage']), 'icon' => 'cubes', 'desc' => 'Send Message Test'],
          ['name' => 'Send File', 'url' => url([ADMIN, 'wagateway', 'sendfile']), 'icon' => 'cubes', 'desc' => 'Send File Test'],
          ['name' => 'Send Image', 'url' => url([ADMIN, 'wagateway', 'sendimage']), 'icon' => 'cubes', 'desc' => 'Send Image Test'],
          ['name' => 'Settings', 'url' => url([ADMIN, 'wagateway', 'settings']), 'icon' => 'cubes', 'desc' => 'Settings WA Getaway'],
      ];
      return $this->draw('manage.html', ['sub_modules' => htmlspecialchars_array($sub_modules), 'waapiserver' => $waapiserver, 'waapiphonenumber' => $waapiphonenumber]);
    }

    public function getWebHook()
    {
      return $this->draw('webhook.html');
    }

    public function getSettings()
    {
      $wagateway['server'] = $this->settings->get('wagateway.server');
      $wagateway['token'] = $this->settings->get('wagateway.token');
      $wagateway['phonenumber'] = $this->settings->get('wagateway.phonenumber');
      return $this->draw('settings.html', ['wagateway' => $wagateway]);
    }

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

    public function postSaveSettings()
    {
        foreach ($_POST['wagateway'] as $key => $val) {
            $this->settings('wagateway', $key, $val);
        }

        $wagateway['token'] = $this->settings->get('wagateway.token');
        $wagateway['phonenumber'] = $this->settings->get('wagateway.phonenumber');
        $settings['email'] = $this->settings->get('settings.email');

        $url = "https://mlite.id/wagateway/activated";
        // SSRF protection: validate that the URL is strictly the intended public endpoint
        if ($url === "https://mlite.id/wagateway/activated" && $this->isSafeUrl($url)) {
            $curlHandle = curl_init();
            curl_setopt($curlHandle, CURLOPT_URL, $url);
            curl_setopt($curlHandle, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
            curl_setopt($curlHandle, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTPS);
            curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($curlHandle, CURLOPT_POSTFIELDS,"token=".$wagateway['token']."&body=".$wagateway['phonenumber']."&email=".$settings['email']);
            curl_setopt($curlHandle, CURLOPT_HEADER, 0);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curlHandle, CURLOPT_TIMEOUT,30);
            curl_setopt($curlHandle, CURLOPT_POST, 1);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, true);
            curl_exec($curlHandle);
            curl_close($curlHandle);
        }

        $this->notify('success', 'Pengaturan telah disimpan');
        redirect(url([ADMIN, 'wagateway', 'settings']));
    }

    public function anySendMessage()
    {
      if(isset($_POST['submit'])) {
        $waapitoken = $this->settings->get('wagateway.token');
        $waapiphonenumber = $this->settings->get('wagateway.phonenumber');
        $waapiserver = $this->settings->get('wagateway.server');
        $url = $waapiserver."/wagateway/kirimpesan";
        if (!$this->isSafeUrl($url)) {
            $this->notify('failure', 'Invalid or insecure WA Gateway URL');
            return $this->draw('send.message.html');
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
        curl_setopt($curlHandle, CURLOPT_TIMEOUT,30);
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, true);
        $response = curl_exec($curlHandle);
        curl_close($curlHandle);
        $response = json_decode($response, true);
        if(isset($response['status']) && $response['status'] == 'true') {
          $this->notify('success', 'Sukses mengirim pesan');
        } else {
          $this->notify('failure', 'Gagal mengirim pesan');
        }
      }
      return $this->draw('send.message.html');
    }

    public function anySendImage()
    {
      if(isset($_POST['submit'])) {
        $waapitoken = $this->settings->get('wagateway.token');
        $waapiphonenumber = $this->settings->get('wagateway.phonenumber');
        $waapiserver = $this->settings->get('wagateway.server');
        $url = $waapiserver."/wagateway/kirimgambar";
        if (!$this->isSafeUrl($url)) {
            $this->notify('failure', 'Invalid or insecure WA Gateway URL');
            return $this->draw('send.image.html');
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
            'url' => $_POST['url'] ?? '',
            'api_key' => $waapitoken
        ]));
        curl_setopt($curlHandle, CURLOPT_HEADER, 0);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT,30);
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, true);
        $response = curl_exec($curlHandle);
        curl_close($curlHandle);
        $response = json_decode($response, true);
        if(isset($response['status']) && $response['status'] == 'true') {
          $this->notify('success', 'Sukses mengirim gambar');
        } else {
          $this->notify('failure', 'Gagal mengirim gambar');
        }
      }
      return $this->draw('send.image.html');
    }

    public function anySendFile()
    {
      if(isset($_POST['submit'])) {
        $waapitoken = $this->settings->get('wagateway.token');
        $waapiphonenumber = $this->settings->get('wagateway.phonenumber');
        $waapiserver = $this->settings->get('wagateway.server');
        $url = $waapiserver."/wagateway/kirimfile";
        if (!$this->isSafeUrl($url)) {
            $this->notify('failure', 'Invalid or insecure WA Gateway URL');
            return $this->draw('send.file.html');
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
            'url' => $_POST['url'] ?? '',
            'api_key' => $waapitoken
        ]));
        curl_setopt($curlHandle, CURLOPT_HEADER, 0);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT,30);
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, true);
        $response = curl_exec($curlHandle);
        curl_close($curlHandle);
        $response = json_decode($response, true);
        if(isset($response['status']) && $response['status'] == 'true') {
          $this->notify('success', 'Sukses mengirim dokumen');
        } else {
          $this->notify('failure', 'Gagal mengirim dokumen');
        }
      }
      return $this->draw('send.file.html');
    }

}
