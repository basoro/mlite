<?php

namespace Plugins\Settings;

use Systems\AdminModule;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FilesystemIterator;
use Plugins\Settings\Inc\RecursiveDotFilterIterator;

class Admin extends AdminModule
{
    private $assign = [];

    public function navigation()
    {
        return [
            'Instansi'          => 'general',
            'Aplikasi'          => 'aplikasi'
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
        return $this->settings('settings', $field, $value);
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
