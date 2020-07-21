<?php

namespace Plugins\Settings;

use Systems\AdminModule;
use Systems\Lib\HttpRequest;

use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FilesystemIterator;
use Plugins\Settings\Inc\RecursiveDotFilterIterator;

class Admin extends AdminModule
{
    private $assign = [];
    private $feed_url = "https://basoro.id/khanza/";

    public function navigation()
    {
        return [
            'Instansi'          => 'general',
            'Aplikasi'          => 'aplikasi',
            'Update'            => 'updates'
        ];
    }

    public function getGeneral()
    {
        $settings = $this->db('setting')->toArray();
        $settings['system'] = [
            'version'       => $this->options->get('settings.version'),
            'php'           => PHP_VERSION,
            'mysql'         => $this->db()->pdo()->query('SELECT VERSION() as version')->fetch()[0],
            'mysql_size'    => $this->roundSize($this->db()->pdo()->query("SELECT ROUND(SUM(data_length + index_length), 1) FROM information_schema.tables WHERE table_schema = '".DBNAME."' GROUP BY table_schema")->fetch()[0]),
            'system_size'   => $this->roundSize($this->_directorySize(BASE_DIR)),
        ];

        if (!empty($redirectData = getRedirectData())) {
            $settings = array_merge($settings, $redirectData);
        }

        $settings['logoURL'] = url(THEMES.'/admin/img/logo.png');
        if(!empty($this->core->getSettings('logo'))) {
          $settings['logoURL'] = "data:image/jpeg;base64,".base64_encode($this->core->getSettings('logo'));
        }

        $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));

        return $this->draw('general.html');
    }

    public function postSaveGeneral()
    {
        unset($_POST['save']);

        if (checkEmptyFields(['nama_instansi', 'alamat_instansi'], $_POST)) {
            $this->notify('failure', 'Isian kosong');
            redirect(url([ADMIN, 'settings', 'general']), $_POST);
        } else {
            $errors = 0;

            if(($photo = isset_or($_FILES['logo']['tmp_name'], false))) {
              $logo = file_get_contents($photo);
            } else {
              $logo = $this->core->getSettings('logo');
            }

            $this->db('setting')
            ->where('aktifkan', 'Yes')
            ->orWhere('aktifkan', 'No')
            ->update([
              'nama_instansi' => $_POST['nama_instansi'],
              'alamat_instansi' => $_POST['alamat_instansi'],
              'kabupaten' => $_POST['kabupaten'],
              'propinsi' => $_POST['propinsi'],
              'kontak' => $_POST['kontak'],
              'email' => $_POST['email'],
              'kode_ppk' => $_POST['kode_ppk'],
              'kode_ppkinhealth' => $_POST['kode_ppkinhealth'],
              'kode_ppkkemenkes' => $_POST['kode_ppkkemenkes'],
              'wallpaper' => $this->core->getSettings('wallpaper'),
              'logo' => $logo
            ]);

            if (!$errors) {
                $this->notify('success', 'Pengaturan sukses');
            } else {
                $this->notify('failure', 'Pengaturan gagal');
            }

            redirect(url([ADMIN, 'settings', 'general']));
        }
    }

    public function getAplikasi()
    {
        $settings = $this->options('settings');

        if (!empty($redirectData = getRedirectData())) {
            $settings = array_merge($settings, $redirectData);
        }

        foreach ($this->core->getRegisteredPages() as $page) {
            $settings['pages'][] = $page;
        }

        $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));

        return $this->draw('aplikasi.html');
    }

    public function postSaveAplikasi()
    {
        unset($_POST['save']);
        if (checkEmptyFields(array_keys($_POST), $_POST)) {
            $this->notify('failure', 'Isian kosong');
            redirect(url([ADMIN, 'settings', 'aplikasi']), $_POST);
        } else {
            $errors = 0;

            foreach ($_POST as $field => $value) {
                if (!$this->db('lite_options')->where('module', 'settings')->where('field', $field)->save(['value' => $value])) {
                    $errors++;
                }
            }

            if (!$errors) {
                $this->notify('success', 'Pengaturan sukses');
            } else {
                $this->notify('failure', 'Pengaturan gagal');
            }

            redirect(url([ADMIN, 'settings', 'aplikasi']));
        }
    }


    public function postChangeOrderOfNavItem()
    {
        foreach ($_POST as $module => $order) {
            $this->db('lite_modules')->where('dir', $module)->save(['sequence' => $order]);
        }
        exit();
    }

    private function _updateSettings($field, $value)
    {
        return $this->options('lite_options', $field, $value);
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

    public function anyUpdates()
    {
        $this->tpl->set('allow_curl', intval(function_exists('curl_init')));

        if (isset($_POST['check'])) {
            $request = $this->updateRequest();

            if (!is_array($request)) {
                $this->tpl->set('error', $request);
            } elseif ($request['status'] == 'error') {
                $this->tpl->set('error', $request['message']);
            } else {
                $this->options('settings', 'update_version', $request['version']);
                $this->options('settings', 'update_changelog', $request['changelog']);
            }
        } elseif (isset($_POST['update'])) {
            if (!class_exists("ZipArchive")) {
                $this->tpl->set('error', "ZipArchive is required to update Khanza LITE.");
            }

            if (!isset($_GET['manual'])) {
                $request = $this->updateRequest();
                $this->download($request['download'], BASE_DIR.'/tmp/latest.zip');
            } else {
                $package = glob(BASE_DIR.'/khanza-lite-*.zip');
                if (!empty($package)) {
                    $package = array_shift($package);
                    $this->rcopy($package, BASE_DIR.'/tmp/latest.zip');
                }
            }

            define("UPGRADABLE", true);
            // Making backup
            $backup_date = date('YmdHis');
            $this->rcopy(BASE_DIR, BASE_DIR.'/backup/'.$backup_date.'/', 0755, [BASE_DIR.'/backup', BASE_DIR.'/tmp/latest.zip', (isset($package) ? BASE_DIR.'/'.basename($package) : '')]);

            // Unzip latest update
            $zip = new ZipArchive;
            $zip->open(BASE_DIR.'/tmp/latest.zip');
            $zip->extractTo(BASE_DIR.'/tmp/update');

            // Copy files
            $this->rcopy(BASE_DIR.'/tmp/update/systems', BASE_DIR.'/systems');
            $this->rcopy(BASE_DIR.'/tmp/update/plugins', BASE_DIR.'/plugins');
            $this->rcopy(BASE_DIR.'/tmp/update/assets', BASE_DIR.'/assets');
            $this->rcopy(BASE_DIR.'/tmp/update/themes', BASE_DIR.'/themes');

            // Restore defines
            $this->rcopy(BASE_DIR.'/backup/'.$backup_date.'/config.php', BASE_DIR.'/config.php');

            // Close archive and delete all unnecessary files
            $zip->close();
            unlink(BASE_DIR.'/tmp/latest.zip');
            deleteDir(BASE_DIR.'/tmp/update');

            $this->options('settings', 'version', $request['version']);
            $this->options('settings', 'update_version', $request['version']);
            $this->options('settings', 'update_changelog', $request['changelog']);

            sleep(2);
            redirect(url([ADMIN, 'settings', 'updates']));
        } elseif (isset($_GET['reset'])) {
            $this->options('settings', 'update_version', 0);
            $this->options('settings', 'update_changelog', '');
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

        $this->options->reload();
        $settings['version'] = $this->options->get('settings.version');
        $settings['update_changelog'] = $this->options->get('settings.update_changelog');
        $settings['update_version'] = $this->options->get('settings.update_version');
        $this->tpl->set('settings', $settings);
        $this->tpl->set('manual_mode', isset_or($manual_mode, false));
        return $this->draw('update.html');
    }

    private function updateRequest()
    {
        $output = HttpRequest::get($this->feed_url);
        if ($output === false) {
            $output = HttpRequest::getStatus();
        } else {
            $output = json_decode($output, true);
        }
        return $output;
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

}
