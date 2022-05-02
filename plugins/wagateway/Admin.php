<?php
namespace Plugins\Wagateway;

use Systems\AdminModule;
use Systems\MySQL;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Manage' => 'manage',
            'Send Message' => 'sendmessage',
            'Send Image' => 'sendimage',
            'Send File' => 'sendfile',
            'Web Hook' => 'webhook',
            'Settings' => 'settings'
        ];
    }

    public function getManage()
    {
      $waapiphonenumber = $this->settings->get('settings.waapiphonenumber');
      $waapiserver = $this->settings->get('settings.waapiserver');
      $sub_modules = [
          ['name' => 'Send Message', 'url' => url([ADMIN, 'wagateway', 'sendmessage']), 'icon' => 'cubes', 'desc' => 'Send Message Test'],
          ['name' => 'Send File', 'url' => url([ADMIN, 'wagateway', 'sendfile']), 'icon' => 'cubes', 'desc' => 'Send File Test'],
          ['name' => 'Send Image', 'url' => url([ADMIN, 'wagateway', 'sendimage']), 'icon' => 'cubes', 'desc' => 'Send Image Test'],
          ['name' => 'Web Hook', 'url' => url([ADMIN, 'wagateway', 'webhook']), 'icon' => 'cubes', 'desc' => 'Webhook WA Gateway'],
          ['name' => 'Settings', 'url' => url([ADMIN, 'wagateway', 'settings']), 'icon' => 'cubes', 'desc' => 'Settings WA Getaway'],
      ];
      return $this->draw('manage.html', ['sub_modules' => $sub_modules, 'waapiserver' => $waapiserver, 'waapiphonenumber' => $waapiphonenumber]);
    }

    public function getWebHook()
    {
      return $this->draw('webhook.html');
    }

    public function getSettings()
    {
      $settings['waapiserver'] = $this->settings->get('settings.waapiserver');
      $settings['waapitoken'] = $this->settings->get('settings.waapitoken');
      $settings['waapiphonenumber'] = $this->settings->get('settings.waapiphonenumber');
      $settings['waapiwebhook'] = $this->settings->get('settings.waapiwebhook');
      return $this->draw('settings.html', ['settings' => $settings]);
    }

    public function postSaveSettings()
    {
        foreach ($_POST['settings'] as $key => $val) {
            $this->settings('settings', $key, $val);
        }

        $settings['waapitoken'] = $this->settings->get('settings.waapitoken');
        $settings['waapiphonenumber'] = $this->settings->get('settings.waapiphonenumber');
        $settings['waapiwebhook'] = $this->settings->get('settings.waapiwebhook');
        $settings['email'] = $this->settings->get('settings.email');

        $url = "https://mlite.id/wagateway/activated";
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS,"token=".$settings['waapitoken']."&body=".$settings['waapiphonenumber']."&webhook=".$settings['waapiwebhook']."&email=".$settings['email']);
        curl_setopt($curlHandle, CURLOPT_HEADER, 0);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT,30);
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($curlHandle);
        curl_close($curlHandle);

        $this->notify('success', 'Pengaturan telah disimpan');
        redirect(url([ADMIN, 'wagateway', 'settings']));
    }

    public function anySendMessage()
    {
      if(isset($_POST['submit'])) {
        $waapitoken = $this->settings->get('settings.waapitoken');
        $waapiphonenumber = $this->settings->get('settings.waapiphonenumber');
        $waapiserver = $this->settings->get('settings.waapiserver');
        $url = $waapiserver."/wagateway/kirimpesan";
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS,"type=text&sender=".$waapiphonenumber."&number=".$_POST['number']."&message=".$_POST['message']."&api_key=".$waapitoken);
        curl_setopt($curlHandle, CURLOPT_HEADER, 0);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT,30);
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curlHandle);
        curl_close($curlHandle);
        $response = json_decode($response, true);
        if($response['status'] == 'false') {
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
        $waapitoken = $this->settings->get('settings.waapitoken');
        $waapiphonenumber = $this->settings->get('settings.waapiphonenumber');
        $waapiserver = $this->settings->get('settings.waapiserver');
        $url = $waapiserver."/wagateway/kirimgambar";
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS,"type=image&sender=".$waapiphonenumber."&number=".$_POST['number']."&message=".$_POST['message']."&url=".$_POST['url']."&api_key=".$waapitoken);
        curl_setopt($curlHandle, CURLOPT_HEADER, 0);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT,30);
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curlHandle);
        curl_close($curlHandle);
        $response = json_decode($response, true);
        if($response['status'] == 'true') {
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
        $waapitoken = $this->settings->get('settings.waapitoken');
        $waapiphonenumber = $this->settings->get('settings.waapiphonenumber');
        $waapiserver = $this->settings->get('settings.waapiserver');
        $url = $waapiserver."/wagateway/kirimfile";
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS,"type=document&sender=".$waapiphonenumber."&number=".$_POST['number']."&message=".$_POST['message']."&url=".$_POST['url']."&api_key=".$waapitoken);
        curl_setopt($curlHandle, CURLOPT_HEADER, 0);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT,30);
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curlHandle);
        curl_close($curlHandle);
        $response = json_decode($response, true);
        if($response['status'] == 'true') {
          $this->notify('success', 'Sukses mengirim dokumen');
        } else {
          $this->notify('failure', 'Gagal mengirim dokumen');
        }
      }
      return $this->draw('send.file.html');
    }

    protected function mysql($table = NULL)
    {
        return new MySQL($table);
    }

}
