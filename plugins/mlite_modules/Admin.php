<?php

namespace Plugins\Mlite_Modules;

use Systems\AdminModule;

class Admin extends AdminModule
{
    public function navigation()
    {
        return [
            'Kelola'    => 'manage'
        ];
    }

    public function getManage($type = 'active')
    {
        $this->_addHeaderFiles();
        $modules = $this->_modulesList($type);
        return $this->draw('manage.html', ['modules' => array_chunk($modules, 2), 'tab' => $type]);
    }

    public function getInstall($dir)
    {
        $files = [
            'info'  => MODULES.'/'.$dir.'/Info.php',
            'admin' => MODULES.'/'.$dir.'/Admin.php'
        ];

        if ((file_exists($files['info']) && file_exists($files['admin'])) || (file_exists($files['info']))) {
            $core = $this->core;
            $info = include($files['info']);
            if (!$this->checkCompatibility(isset_or($info['compatibility']))) {
                $this->notify('failure', 'Tidak dapat memasang modul %s karena sudah lawas. Silahkan update modul dan coba lagi.', $dir);
            } elseif ($this->core->db->insert('mlite_modules', ['dir' => $dir, 'sequence' => $this->core->db->count('mlite_modules')])) {
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

        redirect(url([ 'mlite_modules', 'manage', 'inactive']));
    }

    public function getUninstall($dir)
    {
        if (in_array($dir, unserialize(BASIC_MODULES))) {
            $this->notify('failure', 'Tidak dapat menonaktifkan modul %s.', $dir);
            redirect(url([ 'mlite_modules', 'manage', 'active']));
        }

        if ($this->core->db->delete('mlite_modules', ['AND' => ['dir' => $dir]])) {
            $core = $this->core;
            $info = include(MODULES.'/'.$dir.'/Info.php');

            if (isset($info['uninstall'])) {
                $info['uninstall']();
            }

            $this->notify('success', 'Modul %s berhasil dinonaktifkan.', $dir);
        } else {
            $this->notify('failure', 'Tidak dapat menonaktifkan modul %s.', $dir);
        }

        redirect(url([ 'mlite_modules', 'manage', 'active']));
    }

    public function getRemove($dir)
    {
        if (in_array($dir, unserialize(BASIC_MODULES))) {
            $this->notify('failure', 'Tidak dapat menghapus berkas-berkas modul %s.', $dir);
            redirect(url([ 'mlite_modules', 'manage', 'inactive']));
        }

        $path = MODULES.'/'.$dir;
        if (is_dir($path)) {
            if (deleteDir($path)) {
                $this->notify('success', 'Berkas-berkas modul %s sudah berhasil dihapus.', $dir);
            } else {
                $this->notify('failure', 'Tidak dapat menghapus berkas-berkas modul %s.', $dir);
            }
        }
        redirect(url([ 'mlite_modules', 'manage', 'inactive']));
    }
        
    public function getDetails($dir)
    {
        $info =  MODULES.'/'.$dir.'/Info.php';

        $module = $this->core->getModuleInfo($dir);
        $module['description'] = $this->tpl->noParse($module['description']);
        $module['last_modified'] = date("Y-m-d", filemtime($info));

        $this->tpl->set('module', $module);
        echo $this->tpl->draw(MODULES.'/mlite_modules/view/details.html', true);
        exit();
    }

    private function _modulesList($type)
    {
        $dbModules = array_column($this->core->db->select('mlite_modules', '*'), 'dir');

        $result = [];

        foreach (glob(MODULES.'/*', GLOB_ONLYDIR) as $dir) {
            $dir = basename($dir);
            $files = [
                'info'  => MODULES.'/'.$dir.'/Info.php',
                'admin' => MODULES.'/'.$dir.'/Admin.php'
            ];

            if ($type == 'active') {
                $inArray = in_array($dir, $dbModules);
            } else {
                $inArray = !in_array($dir, $dbModules);
            }

            if (((file_exists($files['info']) && file_exists($files['admin'])) || (file_exists($files['info']))) && $inArray) {
                $details = $this->core->getModuleInfo($dir);
                $details['description'] = $this->tpl->noParse($details['description']);
                $features = $this->core->getModuleNav($dir);
                $other = [];
                $urls = [
                    'url'            => (is_array($features) ? url([ $dir, array_shift($features)]) : '#'),
                    'uninstallUrl'    => url([ 'mlite_modules', 'uninstall', $dir]),
                    'removeUrl'        => url([ 'mlite_modules', 'remove', $dir]),
                    'installUrl'    => url([ 'mlite_modules', 'install', $dir]),
                    'detailsUrl'    => url([ 'mlite_modules', 'details', $dir]),
                    'manageUrl'    => url([ $dir, 'manage'])
                ];

                $other['installed'] = $type == 'active' ? true : false;

                if (in_array($dir, unserialize(BASIC_MODULES))) {
                    $other['basic'] = true;
                } else {
                    $other['basic'] = false;
                }

                $other['compatible'] = $this->checkCompatibility(isset_or($details['compatibility'], '4.0.0'));
                $result[] = $details + $urls + $other;
            }
        }
        return $result;
    }

    private function checkCompatibility($version)
    {
        $systemVersion = $this->settings('settings', 'version');
        // $systemVersion = '4.0.0';
        $version = str_replace(['.', '*'], ['\\.', '[0-9]+'], $version);
        return preg_match('/^'.$version.'[a-z]*$/', $systemVersion);
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/mlite_modules/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/mlite_modules/js/scripts.js', ['settings' => $settings]);
        exit();
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/vendor/datatables/datatables.min.css'));
        $this->core->addJS(url('assets/vendor/datatables/datatables.min.js'), 'footer');

        $this->core->addCSS(url([ 'mlite_modules', 'css']));
        $this->core->addJS(url([ 'mlite_modules', 'javascript']), 'footer');
    }

}
