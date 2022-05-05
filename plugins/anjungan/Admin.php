<?php

namespace Plugins\Anjungan;

use Systems\AdminModule;

class Admin extends AdminModule
{
    public function navigation()
    {
        return [
            'Kelola' => 'manage',
            'Display' => 'index',
            'Pengaturan' => 'settings',
        ];
    }

    public function getManage()
    {
      $sub_modules = [
        ['name' => 'Display', 'url' => url([ADMIN, 'anjungan', 'index']), 'icon' => 'desktop', 'desc' => 'Display-Display Informasi Anjungan'],
        ['name' => 'Pengaturan', 'url' => url([ADMIN, 'anjungan', 'settings']), 'icon' => 'desktop', 'desc' => 'Pengaturan Anjungan'],
      ];
      return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
    }

    public function getIndex()
    {
        return $this->draw('index.html');
    }

    public function getSettings()
    {
        $this->assign['title'] = 'Pengaturan Modul Anjungan';
        $this->assign['poliklinik'] = $this->_getPoliklinik($this->settings->get('anjungan.display_poli'));
        $this->assign['penjab'] = $this->_getPenjab($this->settings->get('anjungan.carabayar'));

        $this->assign['anjungan'] = htmlspecialchars_array($this->settings('anjungan'));
        return $this->draw('settings.html', ['settings' => $this->assign]);
    }

    public function postSaveSettings()
    {
        $_POST['anjungan']['display_poli'] = implode(',', $_POST['anjungan']['display_poli']);
        $_POST['anjungan']['carabayar'] = implode(',', $_POST['anjungan']['carabayar']);
        foreach ($_POST['anjungan'] as $key => $val) {
            $this->settings('anjungan', $key, $val);
        }
        $this->notify('success', 'Pengaturan telah disimpan');
        redirect(url([ADMIN, 'anjungan', 'settings']));
    }

    private function _getPoliklinik($kd_poli = null)
    {
        $result = [];
        $rows = $this->core->mysql('poliklinik')->toArray();

        if (!$kd_poli) {
            $kd_poliArray = [];
        } else {
            $kd_poliArray = explode(',', $kd_poli);
        }

        foreach ($rows as $row) {
            if (empty($kd_poliArray)) {
                $attr = '';
            } else {
                if (in_array($row['kd_poli'], $kd_poliArray)) {
                    $attr = 'selected';
                } else {
                    $attr = '';
                }
            }
            $result[] = ['kd_poli' => $row['kd_poli'], 'nm_poli' => $row['nm_poli'], 'attr' => $attr];
        }
        return $result;
    }

    private function _getPenjab($kd_pj = null)
    {
        $result = [];
        $rows = $this->core->mysql('penjab')->toArray();

        if (!$kd_pj) {
            $kd_pjArray = [];
        } else {
            $kd_pjArray = explode(',', $kd_pj);
        }

        foreach ($rows as $row) {
            if (empty($kd_pjArray)) {
                $attr = '';
            } else {
                if (in_array($row['kd_pj'], $kd_pjArray)) {
                    $attr = 'selected';
                } else {
                    $attr = '';
                }
            }
            $result[] = ['kd_pj' => $row['kd_pj'], 'png_jawab' => $row['png_jawab'], 'attr' => $attr];
        }
        return $result;
    }

}
