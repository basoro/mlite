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
            'Send Button' => 'sendbutton',
            'Send Template' => 'sendtemplate',
            'Upload Image' => 'uploadimage',
            'Upload File' => 'uploadfile',
            'Pengaturan' => 'settings'
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
          ['name' => 'Send Button', 'url' => url([ADMIN, 'wagateway', 'sendbutton']), 'icon' => 'cubes', 'desc' => 'Kirim Button Message (2 tombol quick reply)'],
          ['name' => 'Send Template', 'url' => url([ADMIN, 'wagateway', 'sendtemplate']), 'icon' => 'cubes', 'desc' => 'Kirim Template Message (tombol URL / telepon)'],
          ['name' => 'Upload Image', 'url' => url([ADMIN, 'wagateway', 'uploadimage']), 'icon' => 'cubes', 'desc' => 'Upload & kirim gambar langsung'],
          ['name' => 'Upload File', 'url' => url([ADMIN, 'wagateway', 'uploadfile']), 'icon' => 'cubes', 'desc' => 'Upload & kirim dokumen langsung'],
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
        if ($this->core->getUserInfo('role') != 'admin') {
            $this->notify('failure', 'Anda tidak memiliki hak akses untuk halaman ini.');
            redirect(url([ADMIN, 'wagateway', 'sendmessage']));
        }
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
        if ($this->core->getUserInfo('role') != 'admin') {
            $this->notify('failure', 'Anda tidak memiliki hak akses untuk halaman ini.');
            redirect(url([ADMIN, 'wagateway', 'sendmessage']));
        }
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

    private function gatewayBaseFields()
    {
        return [
            'sender' => $this->settings->get('wagateway.phonenumber'),
            'api_key' => $this->settings->get('wagateway.token'),
        ];
    }

    /**
     * POST helper ke WA Gateway sesuai https://wa.mlite.id/api-docs.json
     * Respons seragam: {status: bool, msg: string}; HTTP 200 sukses, 410 gagal.
     */
    private function callGateway($path, array $fields, $timeout = 30)
    {
        $waapiserver = rtrim((string) $this->settings->get('wagateway.server'), '/');
        $url = $waapiserver.$path;
        if (!$this->isSafeUrl($url)) {
            return ['status' => false, 'msg' => 'Invalid or insecure WA Gateway URL'];
        }
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
        curl_setopt($curlHandle, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTPS);
        curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($curlHandle, CURLOPT_HEADER, 0);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, true);
        $response = curl_exec($curlHandle);
        $curlError = curl_error($curlHandle);
        curl_close($curlHandle);
        if ($response === false || $response === '' || $response === null) {
            return ['status' => false, 'msg' => $curlError !== '' ? $curlError : 'Tidak ada respon dari WA Gateway'];
        }
        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            return ['status' => false, 'msg' => 'Respon tidak valid dari WA Gateway'];
        }
        return $decoded;
    }

    private function notifyGatewayResult(array $response, $successMessage)
    {
        $status = isset($response['status']) && filter_var($response['status'], FILTER_VALIDATE_BOOLEAN);
        if ($status) {
            $this->notify('success', $successMessage);
        } else {
            $this->notify('failure', !empty($response['msg']) ? $response['msg'] : 'Gagal mengirim pesan via WA Gateway');
        }
    }

    /**
     * Validasi upload sesuai batasan api-docs.json: maksimal 50MB dan
     * hanya PDF (untuk dokumen) / JPG, JPEG, PNG, GIF, WEBP.
     * Mengembalikan ['mime' => ...] bila valid, atau ['error' => ...] bila gagal.
     */
    private function validateUpload(array $file, array $allowedExtensions, array $allowedMimes)
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['error' => 'Upload file gagal.'];
        }
        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            return ['error' => 'Ukuran file terlalu besar. Maksimal 50MB.'];
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'Upload file gagal (kode error '.$file['error'].').'];
        }
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['error' => 'File tidak ditemukan.'];
        }
        if ((int) $file['size'] > 50 * 1024 * 1024) {
            return ['error' => 'Ukuran file terlalu besar. Maksimal 50MB.'];
        }
        $allowedMessage = 'Tipe file tidak diizinkan. Hanya PDF, JPG, JPEG, PNG, GIF, dan WEBP yang didukung.';
        $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions, true)) {
            return ['error' => $allowedMessage];
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : (string) $file['type'];
        if ($finfo) {
            finfo_close($finfo);
        }
        if (!in_array($mime, $allowedMimes, true)) {
            return ['error' => $allowedMessage];
        }
        return ['mime' => $mime];
    }

    public function anySendButton()
    {
        if (isset($_POST['submit'])) {
            $number = trim((string) ($_POST['number'] ?? ''));
            $message = trim((string) ($_POST['message'] ?? ''));
            $button1 = trim((string) ($_POST['button1'] ?? ''));
            $button2 = trim((string) ($_POST['button2'] ?? ''));
            $footer = trim((string) ($_POST['footer'] ?? ''));
            if ($number === '' || $message === '' || $button1 === '' || $button2 === '' || $footer === '') {
                $this->notify('failure', 'Nomor tujuan, pesan, label kedua tombol, dan footer wajib diisi');
            } else {
                $response = $this->callGateway('/wagateway/kirimbutton', array_merge($this->gatewayBaseFields(), [
                    'type' => 'button',
                    'number' => $number,
                    'message' => $message,
                    'button1' => $button1,
                    'button2' => $button2,
                    'footer' => $footer,
                ]));
                $this->notifyGatewayResult($response, 'Sukses mengirim button message');
            }
        }
        return $this->draw('send.button.html');
    }

    public function anySendTemplate()
    {
        if (isset($_POST['submit'])) {
            $number = trim((string) ($_POST['number'] ?? ''));
            $message = trim((string) ($_POST['message'] ?? ''));
            $template1 = trim((string) ($_POST['template1'] ?? ''));
            $template2 = trim((string) ($_POST['template2'] ?? ''));
            $footer = trim((string) ($_POST['footer'] ?? ''));
            $templateHint = 'Format template salah. Gunakan {url|call}|{Label}|{Value}, contoh: url|Website|https://example.com';
            $invalid = false;
            foreach ([$template1, $template2] as $template) {
                $parts = explode('|', $template);
                if (count($parts) < 3 || !in_array(trim($parts[0]), ['url', 'call'], true) || trim($parts[1]) === '' || trim($parts[2]) === '') {
                    $invalid = true;
                    break;
                }
            }
            if ($number === '' || $message === '' || $template1 === '' || $template2 === '' || $footer === '') {
                $this->notify('failure', 'Nomor tujuan, pesan, kedua template, dan footer wajib diisi');
            } elseif ($invalid) {
                $this->notify('failure', $templateHint);
            } else {
                $response = $this->callGateway('/wagateway/kirimtemplate', array_merge($this->gatewayBaseFields(), [
                    'type' => 'template',
                    'number' => $number,
                    'message' => $message,
                    'template1' => $template1,
                    'template2' => $template2,
                    'footer' => $footer,
                ]));
                $this->notifyGatewayResult($response, 'Sukses mengirim template message');
            }
        }
        return $this->draw('send.template.html');
    }

    public function anyUploadImage()
    {
        if (isset($_POST['submit'])) {
            $number = trim((string) ($_POST['number'] ?? ''));
            if ($number === '') {
                $this->notify('failure', 'Nomor tujuan wajib diisi');
            } elseif (empty($_FILES['image'])) {
                $this->notify('failure', 'File gambar wajib dipilih');
            } else {
                $check = $this->validateUpload(
                    $_FILES['image'],
                    ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                    ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
                );
                if (isset($check['error'])) {
                    $this->notify('failure', $check['error']);
                } else {
                    $response = $this->callGateway('/wagateway/uploadimage', array_merge($this->gatewayBaseFields(), [
                        'number' => $number,
                        'message' => trim((string) ($_POST['message'] ?? '')),
                        'image' => new \CURLFile($_FILES['image']['tmp_name'], $check['mime'], $_FILES['image']['name']),
                    ]), 120);
                    $this->notifyGatewayResult($response, 'Sukses mengirim gambar');
                }
            }
        }
        return $this->draw('upload.image.html');
    }

    public function anyUploadFile()
    {
        if (isset($_POST['submit'])) {
            $number = trim((string) ($_POST['number'] ?? ''));
            if ($number === '') {
                $this->notify('failure', 'Nomor tujuan wajib diisi');
            } elseif (empty($_FILES['file'])) {
                $this->notify('failure', 'File wajib dipilih');
            } else {
                $check = $this->validateUpload(
                    $_FILES['file'],
                    ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp'],
                    ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp']
                );
                if (isset($check['error'])) {
                    $this->notify('failure', $check['error']);
                } else {
                    $response = $this->callGateway('/wagateway/uploadfile', array_merge($this->gatewayBaseFields(), [
                        'number' => $number,
                        'file' => new \CURLFile($_FILES['file']['tmp_name'], $check['mime'], $_FILES['file']['name']),
                    ]), 120);
                    $this->notifyGatewayResult($response, 'Sukses mengirim dokumen');
                }
            }
        }
        return $this->draw('upload.file.html');
    }

}
