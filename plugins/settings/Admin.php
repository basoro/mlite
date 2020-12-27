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
    private $feed_url = "https://api.github.com/repos/basoro/Khanza-Lite/commits/master";

    public function init()
    {
        if (file_exists(BASE_DIR.'/inc/engine')) {
            deleteDir(BASE_DIR.'/inc/engine');
        }
    }

    public function navigation()
    {
        return [
            'Umum'          => 'general',
            //'Tema' => 'theme',
            //'Pembaruan'          => 'updates',
        ];
    }

    public function getGeneral()
    {
        $settings = $this->settings('settings');
        $check_table = $this->db()->pdo()->query("SHOW TABLES LIKE 'poliklinik'");
        $check_table->execute();
        $check_table = $check_table->fetch();
        $settings['poliklinik'] = [];
        $settings['dokter'] = [];
        if($check_table) {
          $settings['poliklinik'] = $this->db('poliklinik')->toArray();
          $settings['dokter'] = $this->db('dokter')->toArray();
        }
        $settings['themes'] = $this->_getThemes();
        $settings['timezones'] = $this->_getTimezones();
        $settings['system'] = [
            'php'           => PHP_VERSION,
            'mysql'         => $this->db()->pdo()->query('SELECT VERSION() as version')->fetch()[0],
            'mysql_size'    => $this->roundSize($this->db()->pdo()->query("SELECT ROUND(SUM(data_length + index_length), 1) FROM information_schema.tables WHERE table_schema = '".DBNAME."' GROUP BY table_schema")->fetch()[0]),
            'system_size'   => $this->roundSize($this->_directorySize(BASE_DIR)),
        ];

        $settings['license'] = [];
        $settings['license']['type'] = $this->_verifyLicense();
        switch ($settings['license']['type']) {
            case License::FREE:
                $settings['license']['name'] = 'Gratis';
                break;
            case License::COMMERCIAL:
                $settings['license']['name'] = 'Berbayar';
                break;
            default:
                $settings['license']['name'] = 'Invalid';
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
        //if (checkEmptyFields(array_keys($_POST), $_POST)) {
        //    $this->notify('failure', 'Isian kosong');
        //    redirect(url([ADMIN, 'settings', 'general']), $_POST);
        //} else {
            $errors = 0;

            $_POST['logo'] = $logo;
            foreach ($_POST as $field => $value) {
                if (!$this->db('mlite_settings')->where('module', 'settings')->where('field', $field)->save(['value' => $value])) {
                    $errors++;
                }
            }

            if (!$errors) {
                $this->notify('success', 'Pengaturan berhasil disimpan.');
            } else {
                $this->notify('failure', 'Gagal menyimpan pengaturan.');
            }

            redirect(url([ADMIN, 'settings', 'general']));
        //}
    }

    public function anyLicense()
    {
        if (isset($_POST['license-key'])) {
            $licenseKey = str_replace('-', null, $_POST['license-key']);

            if (!($licenseKey = License::getLicenseData($licenseKey))) {
                $this->notify('failure', 'Kode lisensi salah.');
            }

            $verify = License::verify($licenseKey);
            if ($verify != License::COMMERCIAL) {
                $this->notify('failure', 'Kode lisensi salah.');
            } else {
                $this->notify('success', 'Kode lisensi berhasil diterima.');
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

        if (isset($_POST['check'])) {

            $url = "https://api.github.com/repos/basoro/Khanza-Lite/commits/master";
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
            $new_date_format = date('Y-m-d H:i:s', strtotime($obj['commit']['author']['date']));

            if (!is_array($obj)) {
                $this->tpl->set('error', $obj);
            } else {
                if(mb_strlen($this->settings->get('settings.version'), 'UTF-8') < 5) {
                  $this->settings('settings', 'version', '2020-01-01 00:00:00');
                }
                $this->settings('settings', 'update_version', $new_date_format);
                $this->settings('settings', 'update_changelog', $obj['commit']['message']);
            }
        } elseif (isset($_POST['update'])) {
            if (!class_exists("ZipArchive")) {
                $this->tpl->set('error', "ZipArchive is required to update Khanza LITE.");
            }

            if (!isset($_GET['manual'])) {
                $url = "https://api.github.com/repos/basoro/Khanza-Lite/commits/master";
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
                $new_date_format = date('Y-m-d H:i:s', strtotime($obj['commit']['author']['date']));
                $this->download('https://github.com/basoro/Khanza-Lite/archive/master.zip', BASE_DIR.'/tmp/latest.zip');
            } else {
                $package = glob(BASE_DIR.'/Khanza-Lite-master.zip');
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
            $this->rcopy(BASE_DIR.'/tmp/update/Khanza-Lite-master/systems', BASE_DIR.'/systems');
            $this->rcopy(BASE_DIR.'/tmp/update/Khanza-Lite-master/plugins', BASE_DIR.'/plugins');
            $this->rcopy(BASE_DIR.'/tmp/update/Khanza-Lite-master/assets', BASE_DIR.'/assets');
            $this->rcopy(BASE_DIR.'/tmp/update/Khanza-Lite-master/themes', BASE_DIR.'/themes');

            // Restore defines
            $this->rcopy(BASE_DIR.'/backup/'.$backup_date.'/config.php', BASE_DIR.'/config.php');

            // Close archive and delete all unnecessary files
            $zip->close();
            unlink(BASE_DIR.'/tmp/latest.zip');
            deleteDir(BASE_DIR.'/tmp/update');

            $this->settings('settings', 'version', $new_date_format);
            $this->settings('settings', 'update_version', $new_date_format);
            $this->settings('settings', 'update_changelog', $obj['commit']['message']);

            sleep(2);
            redirect(url([ADMIN, 'settings', 'updates']));
        } elseif (isset($_GET['reset'])) {
            $this->settings('settings', 'update_version', 0);
            $this->settings('settings', 'update_changelog', '');
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
        $settings['version'] = $this->settings->get('settings.version');
        $settings['update_changelog'] = $this->settings->get('settings.update_changelog');
        $settings['update_version'] = $this->settings->get('settings.update_version');
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

    public function postChangeOrderOfNavItem()
    {
        foreach ($_POST as $module => $order) {
            $this->db('mlite_modules')->where('dir', $module)->save(['sequence' => $order]);
        }
        exit();
    }

    public function _checkUpdate()
    {
        $settings = $this->settings('settings');
        if (time() - $settings['update_check'] > 3600*6) {
            $request = $this->updateRequest('/mlite/update', [
                'ip' => isset_or($_SERVER['SERVER_ADDR'], $_SERVER['SERVER_NAME']),
                'version' => $settings['version'],
                'domain' => url(),
            ]);

            if (is_array($request) && $request['status'] != 'error') {
                $settings['update_version'] = $request['data']['version'];
                $this->_updateSettings('update_version', $request['data']['version']);
                $this->_updateSettings('update_changelog', $request['data']['changelog']);
            }

            $this->_updateSettings('update_check', time());
        }

        if (cmpver($settings['update_version'], $settings['version']) === 1) {
            return true;
        }

        return false;
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

            $result[md5($file)] = ['name' => basename($file), 'path' => $file, 'short' => str_replace(BASE_DIR, null, $file), 'attr' => $attr];
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
        $licenseArray = (array) json_decode(base64_decode($this->settings('settings', 'license')), true);
        $license = array_replace(array_fill(0, 5, null), $licenseArray);
        list($md5hash, $pid, $lcode, $dcode, $tstamp) = $license;

        if (empty($md5hash)) {
            return License::FREE;
        }

        if ($md5hash == md5($pid.$lcode.$dcode.domain(false))) {
            return License::COMMERCIAL;
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

            $pretty_offset = "UTC${offset_prefix}${offset_formatted}";

            $timezone_list[$timezone] = "(${pretty_offset}) $timezone";
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

}
