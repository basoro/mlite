<?php

namespace Plugins\Anjungan;

use Systems\AdminModule;

class Admin extends AdminModule
{
    public function navigation()
    {
        return [
            'Kelola' => 'index',
            'Pengaturan' => 'settings',
        ];
    }

    public function getIndex()
    {
        return $this->draw('index.html');
    }

    public function getSettings()
    {
        $this->assign['title'] = 'Pengaturan Modul Anjungan';
        $this->assign['poliklinik'] = $this->_getPoliklinik($this->options->get('anjungan.display_poli'));
        $this->assign['penjab'] = $this->db('penjab')->toArray();

        $this->assign['anjungan'] = htmlspecialchars_array($this->options('anjungan'));
        return $this->draw('settings.html', ['settings' => $this->assign]);
    }

    public function postSaveSettings()
    {
        $_POST['anjungan']['display_poli'] = implode(',', $_POST['anjungan']['display_poli']);
        foreach ($_POST['anjungan'] as $key => $val) {
            $this->options('anjungan', $key, $val);
        }
        $this->notify('success', 'Pengaturan telah disimpan');
        redirect(url([ADMIN, 'anjungan', 'settings']));
    }

    private function _getPoliklinik($kd_poli = null)
    {
        $result = [];
        $rows = $this->db('poliklinik')->toArray();

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

}
