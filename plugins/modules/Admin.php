<?php

namespace Plugins\Modules;

use Systems\AdminModule;

class Admin extends AdminModule
{
    public function navigation()
    {
        return [
            'Kelola'    => 'manage',
            'Unggah'            => 'upload'
        ];
    }

    /**
    * list of active/inactive modules
    */
    public function getManage($type = 'active')
    {
        $modules = $this->_modulesList($type);
        return $this->draw('manage.html', ['modules' => array_chunk($modules, 2), 'tab' => $type]);
    }

    /**
    * module upload
    */
    public function getUpload()
    {
        return $this->draw('upload.html');
    }

    /**
     * module extract
     */
    public function postExtract()
    {
        if (isset($_FILES['zip_module']['tmp_name']) && !FILE_LOCK) {
            $backURL = url([ADMIN, 'modules', 'upload']);
            $file = $_FILES['zip_module']['tmp_name'];

            // Verify ZIP
            $zip = zip_open($file);
            $modules = array();
            while ($entry = zip_read($zip)) {
                $entry = zip_entry_name($entry);
                if (preg_match('/^(.*?)\/Info.php$/', $entry, $matches)) {
                    $modules[] = ['path' => $matches[0], 'name' => $matches[1]];
                }

                if (strpos($entry, '/') === false) {
                    $this->notify('failure', 'Modul tidak benar atau rusak.');
                    redirect($backURL);
                }
            }

            // Extract to modules
            $zip = new \ZipArchive;
            if ($zip->open($file) === true) {
                foreach ($modules as $module) {
                    if (file_exists(MODULES.'/'.$module['name'])) {
                        $tmpName = md5(time().rand(1, 9999));
                        file_put_contents('tmp/'.$tmpName, $zip->getFromName($module['path']));
                        $info_new = include('tmp/'.$tmpName);
                        $info_old = include(MODULES.'/'.$module['name'].'/Info.php');
                        unlink('tmp/'.$tmpName);

                        if (cmpver($info_new['version'], $info_old['version']) <= 0) {
                            $this->notify('failure', 'Modul yang diunggah memiliki versi lebih lama atau sama dengan yang sudah terpasang.');
                            continue;
                        }
                    }
                    $this->unzip($file, MODULES.'/'.$module['name'], $module['name']);
                }

                $this->notify('success', 'Modul berhasil ditambahkan. Buka halaman <b>Nonaktif</b> dan aktifkan modul itu.');
            } else {
                $this->notify('failure', 'Modul tidak benar atau rusak.');
            }
        }

        redirect($backURL);
    }

    public function getInstall($dir)
    {
        $files = [
            'info'  => MODULES.'/'.$dir.'/Info.php',
            'admin' => MODULES.'/'.$dir.'/Admin.php',
            'site'  => MODULES.'/'.$dir.'/Site.php'
        ];

        if ((file_exists($files['info']) && file_exists($files['admin'])) || (file_exists($files['info']) && file_exists($files['site']))) {
            $core = $this->core;
            $info = include($files['info']);
            if (!$this->checkCompatibility(isset_or($info['compatibility']))) {
                $this->notify('failure', 'Tidak dapat memasang modul %s karena sudah lawas. Silahkan update modul dan coba lagi.', $dir);
            } elseif ($this->db('mlite_modules')->save(['dir' => $dir, 'sequence' => $this->db('mlite_modules')->count()])) {
                if (isset($info['install'])) {
                    $info['install']();
                }

                $this->notify('success', 'Modul %s berhasil diaktifkan.', $dir);
            } else {
                $this->notify('failure', 'Tidak dapat mengaktifkan modul %s.', $dir);
            }
        } else {
            $this->notify('failure', 'Tidak dapat mengaktifkan modul %s, sebab berkas kurang lengkap.', $dir);
        }

        redirect(url([ADMIN, 'modules', 'manage', 'inactive']));
    }

    public function getUninstall($dir)
    {
        if (in_array($dir, unserialize(BASIC_MODULES))) {
            $this->notify('failure', 'Tidak dapat menonaktifkan modul %s.', $dir);
            redirect(url([ADMIN, 'modules', 'manage', 'active']));
        }

        if ($this->db('mlite_modules')->delete('dir', $dir)) {
            $core = $this->core;
            $info = include(MODULES.'/'.$dir.'/Info.php');

            if (isset($info['uninstall'])) {
                $info['uninstall']();
            }

            $this->notify('success', 'Modul %s berhasil dinonaktifkan.', $dir);
        } else {
            $this->notify('failure', 'Tidak dapat menonaktifkan modul %s.', $dir);
        }

        redirect(url([ADMIN, 'modules', 'manage', 'active']));
    }

    public function getRemove($dir)
    {
        if (in_array($dir, unserialize(BASIC_MODULES))) {
            $this->notify('failure', 'Tidak dapat menghapus berkas-berkas modul %s.', $dir);
            redirect(url([ADMIN, 'modules', 'manage', 'inactive']));
        }

        $path = MODULES.'/'.$dir;
        if (is_dir($path)) {
            if (deleteDir($path)) {
                $this->notify('success', 'Berkas-berkar modul %s sudah berhasil dihapus.', $dir);
            } else {
                $this->notify('failure', 'Tidak dapat menghapus berkas-berkas modul %s.', $dir);
            }
        }
        redirect(url([ADMIN, 'modules', 'manage', 'inactive']));
    }

    public function getDetails($dir)
    {
        $files = [
            'info'      => MODULES.'/'.$dir.'/Info.php',
            'readme'    => MODULES.'/'.$dir.'/ReadMe.md'
        ];

        $module = $this->core->getModuleInfo($dir);
        $module['description'] = $this->tpl->noParse($module['description']);
        $module['last_modified'] = date("Y-m-d", filemtime($files['info']));

        // ReadMe.md
        if (file_exists($files['readme'])) {
            $parsedown = new \Systems\Lib\Parsedown();
            $module['readme'] = $parsedown->text($this->tpl->noParse(file_get_contents($files['readme'])));
        }

        $this->tpl->set('module', $module);
        echo $this->tpl->draw(MODULES.'/modules/view/admin/details.html', true);
        exit();
    }

    private function _modulesList($type)
    {
        $dbModules = array_column($this->db('mlite_modules')->toArray(), 'dir');
        $result = [];

        foreach (glob(MODULES.'/*', GLOB_ONLYDIR) as $dir) {
            $dir = basename($dir);
            $files = [
                'info'  => MODULES.'/'.$dir.'/Info.php',
                'admin' => MODULES.'/'.$dir.'/Admin.php',
                'site'  => MODULES.'/'.$dir.'/Site.php'
            ];

            if ($type == 'active') {
                $inArray = in_array($dir, $dbModules);
            } else {
                $inArray = !in_array($dir, $dbModules);
            }

            if (((file_exists($files['info']) && file_exists($files['admin'])) || (file_exists($files['info']) && file_exists($files['site']))) && $inArray) {
                $details = $this->core->getModuleInfo($dir);
                $details['description'] = $this->tpl->noParse($details['description']);
                $features = $this->core->getModuleNav($dir);
                $other = [];
                $urls = [
                    'url'            => (is_array($features) ? url([ADMIN, $dir, array_shift($features)]) : '#'),
                    'uninstallUrl'    => url([ADMIN, 'modules', 'uninstall', $dir]),
                    'removeUrl'        => url([ADMIN, 'modules', 'remove', $dir]),
                    'installUrl'    => url([ADMIN, 'modules', 'install', $dir]),
                    'detailsUrl'    => url([ADMIN, 'modules', 'details', $dir])
                ];

                $other['installed'] = $type == 'active' ? true : false;

                if (in_array($dir, unserialize(BASIC_MODULES))) {
                    $other['basic'] = true;
                } else {
                    $other['basic'] = false;
                }

                $other['compatible'] = $this->checkCompatibility(isset_or($details['compatibility'], '2022'));
                $result[] = $details + $urls + $other;
            }
        }
        return $result;
    }

    private function unzip($zipFile, $to, $path = '/')
    {
        $path = trim($path, '/');
        $zip = new \ZipArchive;
        $zip->open($zipFile);

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);

            if (empty($path) || strpos($filename, $path) == 0) {
                $file = $to.'/'.str_replace($path, null, $filename);
                if (!file_exists(dirname($file))) {
                    mkdir(dirname($file), 0777, true);
                }

                if (substr($file, -1) != '/') {
                    file_put_contents($to.'/'.str_replace($path, null, $filename), $zip->getFromIndex($i));
                }
            }
        }

        $zip->close();
    }

    private function checkCompatibility($version)
    {
        $systemVersion = $this->settings('settings', 'version');
        $version = str_replace(['.', '*'], ['\\.', '[0-9]+'], $version);
        return preg_match('/^'.$version.'[a-z]*$/', substr($systemVersion, 0, 4));
    }
}
