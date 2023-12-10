<?php

namespace Plugins\Settings;

use Systems\AdminModule;
use Systems\Lib\License;
use Systems\Lib\HttpRequest;

use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FilesystemIterator;
use Plugins\Settings\Inc\RecursiveDotFilterIterator;

class Admin extends AdminModule
{
    private $assign = [];

    public function init()
    {
        if (file_exists(BASE_DIR.'/inc/engine')) {
            deleteDir(BASE_DIR.'/inc/engine');
        }
    }

    public function navigation()
    {
        return [
            'Pengaturan'          => 'manage',
            'Umum'          => 'general',
            'Tema' => 'theme',
            'Pembaruan'          => 'updates',
        ];
    }

    public function getManage()
    {
      $sub_modules = [
        ['name' => 'Pengaturan Umum', 'url' => url([ADMIN, 'settings', 'general']), 'icon' => 'wrench', 'desc' => 'Pengaturan umum mLITE'],
        ['name' => 'Tema Publik', 'url' => url([ADMIN, 'settings', 'theme']), 'icon' => 'cubes', 'desc' => 'Pengaturan tema tampilan publik'],
        ['name' => 'Pembaruan Sistem', 'url' => url([ADMIN, 'settings', 'updates']), 'icon' => 'cubes', 'desc' => 'Pembaruan sistem'],
      ];
      return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
    }

    public function getGeneral()
    {
        $this->_addHeaderFiles();
        $settings = $this->settings('settings');
        $settings['module_pasien'] = $this->db('mlite_modules')->where('dir', 'pasien')->oneArray();
        $settings['module_rawat_igd'] = $this->db('mlite_modules')->where('dir', 'igd')->oneArray();
        $settings['module_laboratorium'] = $this->db('mlite_modules')->where('dir', 'laboratorium')->oneArray();
        $settings['module_radiologi'] = $this->db('mlite_modules')->where('dir', 'radiologi')->oneArray();
        $settings['module_wagateway'] = $this->db('mlite_modules')->where('dir', 'wagateway')->oneArray();
        $settings['master'] = $this->db('mlite_modules')->where('dir', 'master')->oneArray();
        $settings['poliklinik'] = [];
        $settings['dokter'] = [];
        if($settings['master']) {
          $settings['poliklinik'] = $this->db('poliklinik')->where('status', '1')->toArray();
          $settings['dokter'] = $this->db('dokter')->where('status', '1')->toArray();
        }
        $settings['bridging_sep'] = $this->db('mlite_modules')->where('dir', 'vclaim')->oneArray();
        $settings['rawat_jalan'] = $this->db('mlite_modules')->where('dir', 'rawat_jalan')->oneArray();
        $settings['presensi'] = $this->db('mlite_modules')->where('dir', 'presensi')->oneArray();
        $settings['themes'] = $this->_getThemes();
        $settings['timezones'] = $this->_getTimezones();
        $settings['system'] = [
            'php'           => PHP_VERSION,
            'mysql'         => $this->db()->pdo()->query('SELECT VERSION() as version')->fetch()[0]
        ];

        $settings['license'] = [];
        $settings['license']['type'] = $this->_verifyLicense();
        switch ($settings['license']['type']) {
            case License::UNREGISTERED:
                $settings['license']['name'] = 'Tidak Terdaftar';
                break;
            case License::REGISTERED:
                $settings['license']['name'] = 'Terdaftar';
                break;
            default:
                $settings['license']['name'] = 'Tidak Valid';
        }

        foreach ($this->core->getRegisteredPages() as $page) {
            $settings['pages'][] = $page;
        }

        if (!empty($redirectData = getRedirectData())) {
            $settings = array_merge($settings, $redirectData);
        }

        $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));
        $this->tpl->set('url', url([ADMIN, 'settings', 's']));

        return $this->draw('general.html');
    }

    public function postSaveGeneral()
    {
        unset($_POST['save']);
        if (($_logo = isset_or($_FILES['logo']['tmp_name'], false))) {
            $img = new \Systems\Lib\Image;

            if ($img->load($_logo)) {
                if ($img->getInfos('width') < $img->getInfos('height')) {
                    $img->crop(0, 0, $img->getInfos('width'), $img->getInfos('width'));
                } else {
                    $img->crop(0, 0, $img->getInfos('height'), $img->getInfos('height'));
                }

                if ($img->getInfos('width') > 512) {
                    $img->resize(512, 512);
                }

                $logo_ = uniqid('logo_');
                $img->save(UPLOADS."/settings/".$logo_.".".$img->getInfos('type'));
                $logo = "uploads/settings/".$logo_.".".$img->getInfos('type');
            }
        } else {
          $logo = $this->settings->get('settings.logo');
        }

        if (($_wallpaper = isset_or($_FILES['wallpaper']['tmp_name'], false))) {
            $img = new \Systems\Lib\Image;

            if ($img->load($_wallpaper)) {
                $wallpaper_ = uniqid('wallpaper_');
                $img->save(UPLOADS."/settings/".$wallpaper_.".".$img->getInfos('type'));
                $wallpaper = "uploads/settings/".$wallpaper_.".".$img->getInfos('type');
            }
        } else {
          $wallpaper = $this->settings->get('settings.wallpaper');
        }

        $errors = 0;

        $_POST['logo'] = $logo;
        $_POST['wallpaper'] = $wallpaper;

        foreach ($_POST as $field => $value) {
            if (!$this->db('mlite_settings')->where('module', 'settings')->where('field', $field)->save(['value' => $value])) {
                $errors++;
            }
        }

        if (!$errors) {

            $url = "https://mlite.id/datars/save";
            $curlHandle = curl_init();
            curl_setopt($curlHandle, CURLOPT_URL, $url);
            curl_setopt($curlHandle, CURLOPT_POSTFIELDS,"nama_instansi=".$_POST['nama_instansi']."&alamat_instansi=".$_POST['alamat']."&kabupaten=".$_POST['kota']."&propinsi=".$_POST['propinsi']."&kontak=".$_POST['nomor_telepon']."&email=".$_POST['email']);
            curl_setopt($curlHandle, CURLOPT_HEADER, 0);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curlHandle, CURLOPT_TIMEOUT,30);
            curl_setopt($curlHandle, CURLOPT_POST, 1);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
            curl_exec($curlHandle);
            curl_close($curlHandle);

            $this->notify('success', 'Pengaturan berhasil disimpan.');

        } else {
            $this->notify('failure', 'Gagal menyimpan pengaturan.');
        }

        redirect(url([ADMIN, 'settings', 'general']));
    }

    public function anyLicense()
    {
        if (isset($_POST['license-key'])) {
            $licenseKey = $_POST['license-key'];

            $verify = License::verify($licenseKey);
            if ($verify != License::REGISTERED) {
                $this->notify('failure', 'Kode validasi penggunaan tidak sesuai.');
            } else {
                $this->notify('success', 'Kode validasi penggunaan berhasil diterima.');
            }
        } elseif (isset($_GET['downgrade'])) {
            $this->db('mlite_settings')->where('module', 'settings')->where('field', 'license')->save(['value' => '']);
        }

        redirect(url([ADMIN,'settings','general']));
    }

    public function anyTheme($theme = null, $file = null)
    {
        $this->core->addCSS(url(MODULES.'/settings/css/admin/settings.css'));

        if (empty($theme) && empty($file)) {
            $this->tpl->set('settings', $this->settings('settings'));
            $this->tpl->set('themes', $this->_getThemes());
            return $this->draw('themes.html');
        } else {
            if ($file == 'activate') {
                $this->db('mlite_settings')->where('module', 'settings')->where('field', 'theme')->save(['value' => $theme]);
                $this->notify('success', 'Templat utama sudah diubah.');
                redirect(url([ADMIN, 'settings', 'theme']));
            }

            // Source code editor
            $this->core->addCSS(url('/assets/jscripts/editor/markitup.min.css'));
            $this->core->addCSS(url('/assets/jscripts/editor/markitup.highlight.min.css'));
            $this->core->addCSS(url('/assets/jscripts/editor/sets/html/set.min.css'));
            $this->core->addJS(url('/assets/jscripts/editor/highlight.min.js'));
            $this->core->addJS(url('/assets/jscripts/editor/markitup.min.js'));
            $this->core->addJS(url('/assets/jscripts/editor/markitup.highlight.min.js'));
            $this->core->addJS(url('/assets/jscripts/editor/sets/html/set.min.js'));

            $this->assign['files'] = $this->_getThemeFiles($file, $theme);

            if ($file) {
                $file = $this->assign['files'][$file]['path'];
            } else {
                $file = reset($this->assign['files'])['path'];
            }

            $this->assign['content'] = $this->tpl->noParse(htmlspecialchars(file_get_contents($file)));
            $this->assign['lang']    = pathinfo($file, PATHINFO_EXTENSION);

            if (isset($_POST['save']) && !FILE_LOCK) {
                if (file_put_contents($file, htmlspecialchars_decode($_POST['content']))) {
                    $this->notify('success', 'Berkas berhasil disimpan.');
                } else {
                    $this->notify('failure', 'Tidak dapat menyimpan berkas.');
                }

                redirect(url([ADMIN, 'settings', 'theme', $theme, md5($file)]));
            }

            $this->tpl->set('settings', $this->settings('settings'));
            $this->tpl->set('theme', array_merge($this->_getThemes($theme), $this->assign));
            return $this->draw('theme.html');
        }
    }

    public function anyUpdates()
    {
        $this->tpl->set('allow_curl', intval(function_exists('curl_init')));
        $settings = $this->settings('settings');

        if (isset($_POST['check'])) {
            $url = "https://api.github.com/repos/basoro/khanza-lite/releases/latest";
            $opts = [
                'http' => [
                    'method' => 'GET',
                    'header' => [
                            'User-Agent: PHP'
                    ]
                ]
            ];
            $json = file_get_contents($url, false, stream_context_create($opts));
            $obj = json_decode($json, true);
    
            $this->settings('settings', 'update_check', time());

            if (!is_array($obj)) {
                $this->tpl->set('error', $obj);
            } else {
                $this->settings('settings', 'update_version', $obj['tag_name']);
                $this->settings('settings', 'update_changelog', $obj['body']);
                $this->tpl->set('update_version', $obj['tag_name']);
            }
        } elseif (isset($_POST['update'])) {
            if (!class_exists("ZipArchive")) {
                $this->tpl->set('error', "ZipArchive is required to update mLITE.");
            }

            if (!isset($_GET['manual'])) {
                $this->download('https://github.com/basoro/khanza-lite/archive/refs/tags/'.$this->settings->get('settings.update_version').'.zip', BASE_DIR.'/tmp/latest.zip');
            } else {
                $package = glob(BASE_DIR.'/mlite-*.zip');
                if (!empty($package)) {
                    $package = array_shift($package);
                    $this->rcopy($package, BASE_DIR.'/tmp/latest.zip');
                }
            }

            define("UPGRADABLE", true);

            // Making backup
            $backup_date = date('YmdHis');
            //$this->rcopy(BASE_DIR, BASE_DIR.'/backup/'.$backup_date.'/', 0755, [BASE_DIR.'/backup', BASE_DIR.'/tmp/latest.zip', (isset($package) ? BASE_DIR.'/'.basename($package) : '')]);
            $this->rcopy(BASE_DIR.'/systems', BASE_DIR.'/backup/'.$backup_date.'/systems');
            $this->rcopy(BASE_DIR.'/plugins', BASE_DIR.'/backup/'.$backup_date.'/plugins');
            $this->rcopy(BASE_DIR.'/assets', BASE_DIR.'/backup/'.$backup_date.'/assets');
            $this->rcopy(BASE_DIR.'/themes', BASE_DIR.'/backup/'.$backup_date.'/themes');
            $this->rcopy(BASE_DIR.'/config.php', BASE_DIR.'/backup/'.$backup_date.'/config.php');
            $this->rcopy(BASE_DIR.'/manifest.json', BASE_DIR.'/backup/'.$backup_date.'/manifest.json');

            // Unzip latest update
            $zip = new \ZipArchive;
            $zip->open(BASE_DIR.'/tmp/latest.zip');
            $zip->extractTo(BASE_DIR.'/tmp/update');

            // Copy files
            $this->rcopy(BASE_DIR.'/tmp/update/khanza-lite-'.$this->settings->get('settings.update_version').'/systems', BASE_DIR.'/systems');
            $this->rcopy(BASE_DIR.'/tmp/update/khanza-lite-'.$this->settings->get('settings.update_version').'/plugins', BASE_DIR.'/plugins');
            $this->rcopy(BASE_DIR.'/tmp/update/khanza-lite-'.$this->settings->get('settings.update_version').'/assets', BASE_DIR.'/assets');
            $this->rcopy(BASE_DIR.'/tmp/update/khanza-lite-'.$this->settings->get('settings.update_version').'/themes', BASE_DIR.'/themes');

            // Restore defines
            $this->rcopy(BASE_DIR.'/backup/'.$backup_date.'/config.php', BASE_DIR.'/config.php');
            $this->rcopy(BASE_DIR.'/backup/'.$backup_date.'/manifest.json', BASE_DIR.'/manifest.json');

            // Run upgrade script
            $version = $settings['version'];
            $new_version = include(BASE_DIR.'/tmp/update/khanza-lite-'.$this->settings->get('settings.update_version').'/systems/upgrade.php');

            // Close archive and delete all unnecessary files
            $zip->close();
            unlink(BASE_DIR.'/tmp/latest.zip');
            rrmdir(BASE_DIR.'/tmp/update');

            $this->settings('settings', 'version', $new_version);
            $this->settings('settings', 'update_version', 0);
            $this->settings('settings', 'update_changelog', '');
            $this->settings('settings', 'update_check', time());

            sleep(2);
            redirect(url([ADMIN, 'settings', 'updates']));
        } elseif (isset($_GET['reset'])) {
            $this->settings('settings', 'update_version', 0);
            $this->settings('settings', 'update_changelog', '');
            $this->settings('settings', 'update_check', 0);
        } elseif (isset($_GET['manual'])) {
            $package = glob(BASE_DIR.'/khanza-lite-*.zip');
            $version = false;
            if (!empty($package)) {
                $package_path = array_shift($package);
                preg_match('/khanza-lite\-([0-9\.a-z]+)\.zip$/', $package_path, $matches);
                $version = $matches[1];
            }
            $manual_mode = ['version' => $version];
        }

        $this->settings->reload();
        $settings = $this->settings('settings');
        $this->tpl->set('settings', $settings);
        $this->tpl->set('manual_mode', isset_or($manual_mode, false));
        return $this->draw('update.html');
    }

    public function postChangeOrderOfNavItem()
    {
        foreach ($_POST as $module => $order) {
            $this->db('mlite_modules')->where('dir', $module)->save(['sequence' => $order]);
        }
        exit();
    }

    private function download($source, $dest)
    {
        set_time_limit(0);
        $fp = fopen($dest, 'w+');
        $ch = curl_init($source);
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
    }

    private function _getThemes($theme = null)
    {
        $themes = glob(THEMES.'/*', GLOB_ONLYDIR);
        $return = [];
        foreach ($themes as $e) {
            if ($e != THEMES.'/admin') {
                $manifest = array_fill_keys(['name', 'version', 'author', 'email', 'thumb'], 'Unknown');
                $manifest['name'] = basename($e);
                $manifest['thumb'] = '../admin/img/unknown_theme.png';

                if (file_exists($e.'/manifest.json')) {
                    $manifest = array_merge($manifest, json_decode(file_get_contents($e.'/manifest.json'), true));
                }

                if ($theme == basename($e)) {
                    return array_merge($manifest, ['dir' => basename($e)]);
                }

                $return[] = array_merge($manifest, ['dir' => basename($e)]);
            }
        }

        return $return;
    }

    private function _getThemeFiles($selected = null, $theme = null)
    {
        $theme = ($theme ? $theme : $this->settings('settings', 'theme'));
        $files = $this->rglob(THEMES.'/'.$theme.'/*.html');
        $files = array_merge($files, $this->rglob(THEMES.'/'.$theme.'/*.css'));
        $files = array_merge($files, $this->rglob(THEMES.'/'.$theme.'/*.js'));

        $result = [];
        foreach ($files as $file) {
            if ($selected && ($selected == md5($file))) {
                $attr = 'selected';
            } else {
                $attr = null;
            }

            $result[md5($file)] = ['name' => basename($file), 'path' => $file, 'short' => str_replace(BASE_DIR, '', $file), 'attr' => $attr];
        }

        return $result;
    }

    private function _updateSettings($field, $value)
    {
        return $this->settings('settings', $field, $value);
    }

    private function rcopy($source, $dest, $permissions = 0755, $expect = [])
    {
        foreach ($expect as $e) {
            if ($e == $source) {
                return;
            }
        }

        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }

        if (is_file($source)) {
            if (!is_dir(dirname($dest))) {
                mkdir(dirname($dest), 0777, true);
            }

            return copy($source, $dest);
        }

        if (!is_dir($dest)) {
            mkdir($dest, $permissions, true);
        }

        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            $this->rcopy("$source/$entry", "$dest/$entry", $permissions, $expect);
        }

        $dir->close();
        return true;
    }

    private function _verifyLicense()
    {

        //$licenseArray = (array) json_decode(base64_decode($this->settings('settings', 'license')), true);
        //$license = array_replace(array_fill(0, 5, null), $licenseArray);
        //list($md5hash, $pid, $lcode, $dcode, $tstamp) = $license;
        $md5hash = $this->settings('settings', 'license');

        if (empty($md5hash)) {
            return License::UNREGISTERED;
        }

        if ($md5hash == md5($this->settings('settings', 'email'))) {
            return License::REGISTERED;
        }

        return License::ERROR;
    }

    private function _getTimezones()
    {
        $regions = array(
            \DateTimeZone::AFRICA,
            \DateTimeZone::AMERICA,
            \DateTimeZone::ANTARCTICA,
            \DateTimeZone::ASIA,
            \DateTimeZone::ATLANTIC,
            \DateTimeZone::AUSTRALIA,
            \DateTimeZone::EUROPE,
            \DateTimeZone::INDIAN,
            \DateTimeZone::PACIFIC,
            \DateTimeZone::UTC,
        );

        $timezones = array();
        foreach ($regions as $region) {
            $timezones = array_merge($timezones, \DateTimeZone::listIdentifiers($region));
        }

        $timezone_offsets = array();
        foreach ($timezones as $timezone) {
            $tz = new \DateTimeZone($timezone);
            $timezone_offsets[$timezone] = $tz->getOffset(new \DateTime);
        }

        // sort timezone by offset
        asort($timezone_offsets);

        $timezone_list = array();
        foreach ($timezone_offsets as $timezone => $offset) {
            $offset_prefix = $offset < 0 ? '-' : '+';
            $offset_formatted = gmdate('H:i', abs($offset));

            $pretty_offset = "UTC{$offset_prefix}{$offset_formatted}";

            $timezone_list[$timezone] = "({$pretty_offset}) $timezone";
        }

        return $timezone_list;
    }

    private function rglob($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
            $files = array_merge($files, $this->rglob($dir.'/'.basename($pattern), $flags));
        }
        return $files;
    }

    private function _directorySize($path)
    {
        $bytestotal = 0;
        $path = realpath($path);
        if ($path!==false) {
            foreach (new RecursiveIteratorIterator(new RecursiveDotFilterIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS))) as $object) {
                try {
                    $bytestotal += $object->getSize();
                } catch (\Exception $e) {
                }
            }
        }

        return $bytestotal;
    }

    private function roundSize($bytes)
    {
        if ($bytes/1024 < 1) {
            return $bytes.' B';
        }
        if ($bytes/1024/1024 < 1) {
            return round($bytes/1024).' KB';
        }
        if ($bytes/1024/1024/1024 < 1) {
            return round($bytes/1024/1024, 2).' MB';
        } else {
            return round($bytes/1024/1024/1024, 2).' GB';
        }
    }

    public function anyCekDaftar()
    {
      if(isset($_POST['request_code'])) {
        $url = "https://mlite.id/datars/aktif";
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS,"email=".$_POST['email']);
        curl_setopt($curlHandle, CURLOPT_HEADER, 0);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT,30);
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curlHandle);
        curl_close($curlHandle);
        $response = json_decode($response, true);
        if($response['status'] == 'error') {
          $this->notify('failure', 'Request kode validasi pendaftaran aplikasi tidak bisa dilakukan. Silahkan simpan dulu pengaturan aplikasi anda. Atau pastikan email request sama dengan email di pengaturan aplikasi.');
        } else {
          $this->notify('success', 'Request kode validasi pendaftaran aplikasi sukses. Silahkan cek inbox email / spam folder yang anda daftarkan.');
        }
      }
      return $this->draw('cek.daftar.html');
    }

    private function _addHeaderFiles()
    {
      $this->core->addCSS(url('assets/css/bootstrap-colorpicker.css'));
      $this->core->addJS(url('assets/jscripts/bootstrap-colorpicker.js'), 'footer');
    }

}
