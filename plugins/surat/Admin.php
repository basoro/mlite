<?php

namespace Plugins\Surat;

use Systems\AdminModule;

class Admin extends AdminModule
{
    public $assign;

    public function navigation()
    {
        return [
            'Kelola' => 'manage',
            'Surat Rujukan' => 'rujukan',
            'Surat Sakit' => 'sakit',
            'Surat Sehat' => 'sehat',
        ];
    }

    public function getManage()
    {
        $sub_modules = [
            ['name' => 'Surat Rujukan', 'url' => url([ADMIN, 'surat', 'rujukan']), 'icon' => 'share', 'desc' => 'Kelola Surat Rujukan'],
            ['name' => 'Surat Sakit', 'url' => url([ADMIN, 'surat', 'sakit']), 'icon' => 'medkit', 'desc' => 'Kelola Surat Sakit'],
            ['name' => 'Surat Sehat', 'url' => url([ADMIN, 'surat', 'sehat']), 'icon' => 'heart', 'desc' => 'Kelola Surat Sehat'],
        ];
        
        return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
    }

    // SURAT RUJUKAN METHODS
    public function anyRujukan($page = 1)
    {
        $this->_addHeaderFiles();
        $perpage = '10';
        $phrase = '';
        if (isset($_POST['s'])) {
            $phrase = $_POST['s'];
        }

        // pagination
        $totalRecords = $this->db('mlite_surat_rujukan')
            ->like('nomor_surat', '%' . $phrase . '%')
            ->orLike('nm_pasien', '%' . $phrase . '%')
            ->toArray();
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url([ADMIN, 'surat', 'rujukan', '%d']));
        $this->assign['pagination'] = $pagination->nav('pagination', '5');
        $this->assign['totalRecords'] = $totalRecords;

        $offset = $pagination->offset();
        $rows = $this->db('mlite_surat_rujukan')
            ->like('nomor_surat', '%' . $phrase . '%')
            ->orLike('nm_pasien', '%' . $phrase . '%')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();

        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                $row['editURL'] = url([ADMIN, 'surat', 'rujukanedit', $row['id']]);
                $row['deleteURL'] = url([ADMIN, 'surat', 'rujukanhapus', $row['id']]);
                $this->assign['list'][] = $row;
            }
        }

        $this->assign['searchURL'] = url([ADMIN, 'surat', 'rujukan']);
        $this->assign['addURL'] = url([ADMIN, 'surat', 'rujukanadd']);
        $this->assign['phrase'] = $phrase;
        return $this->draw('rujukan.manage.html', ['rujukan' => $this->assign]);
    }

    public function getRujukanAdd()
    {
        $this->_addHeaderFiles();
        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        } else {
            $this->assign['form'] = [
                'id' => '',
                'nomor_surat' => '',
                'no_rawat' => '',
                'no_rkm_medis' => '',
                'nm_pasien' => '',
                'tgl_lahir' => '',
                'umur' => '',
                'jk' => '',
                'alamat' => '',
                'kepada' => '',
                'di' => '',
                'anamnesa' => '',
                'pemeriksaan_fisik' => '',
                'pemeriksaan_penunjang' => '',
                'diagnosa' => '',
                'terapi' => '',
                'alasan_dirujuk' => '',
                'dokter' => '',
                'petugas' => ''
            ];
        }

        return $this->draw('rujukan.form.html', ['rujukan' => $this->assign]);
    }

    public function getRujukanEdit($id)
    {
        $this->_addHeaderFiles();
        $row = $this->db('mlite_surat_rujukan')->where('id', $id)->oneArray();
        if (!empty($row)) {
            $this->assign['form'] = $row;
            return $this->draw('rujukan.form.html', ['rujukan' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'surat', 'rujukan']));
        }
    }

    public function postRujukanSave($id = null)
    {
        $errors = 0;

        if (!$id) {
            $location = url([ADMIN, 'surat', 'rujukan']);
        } else {
            $location = url([ADMIN, 'surat', 'rujukanedit', $id]);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (!$id) {
                $query = $this->db('mlite_surat_rujukan')->save($_POST);
            } else {
                $query = $this->db('mlite_surat_rujukan')->where('id', $id)->save($_POST);
            }

            if ($query) {
                $this->notify('success', 'Simpan sukes');
            } else {
                $this->notify('failure', 'Simpan gagal');
            }

            redirect($location, $_POST);
        }

        redirect($location, $_POST);
    }

    public function getRujukanHapus($id)
    {
        if ($this->db('mlite_surat_rujukan')->where('id', $id)->delete()) {
            $this->notify('success', 'Hapus sukses');
        } else {
            $this->notify('failure', 'Hapus gagal');
        }
        redirect(url([ADMIN, 'surat', 'rujukan']));
    }

    public function getSuratRujukan($no_rawat)
    {
        $kd_dokter = $this->core->getRegPeriksaInfo('kd_dokter', revertNoRawat($no_rawat));
        $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis', revertNoRawat($no_rawat));
        $pasien = $this->db('pasien')
          ->join('kelurahan', 'kelurahan.kd_kel=pasien.kd_kel')
          ->join('kecamatan', 'kecamatan.kd_kec=pasien.kd_kec')
          ->join('kabupaten', 'kabupaten.kd_kab=pasien.kd_kab')
          ->join('propinsi', 'propinsi.kd_prop=pasien.kd_prop')
          ->where('no_rkm_medis', $no_rkm_medis)
          ->oneArray();
        $nm_dokter = $this->core->getPegawaiInfo('nama', $kd_dokter);
        $sip_dokter = $this->core->getDokterInfo('no_ijn_praktek', $kd_dokter);
        $this->tpl->set('pasien', $this->tpl->noParse_array(htmlspecialchars_array($pasien)));
        $this->tpl->set('nm_dokter', $nm_dokter);
        $this->tpl->set('sip_dokter', $sip_dokter);
        $this->tpl->set('no_rawat', revertNoRawat($no_rawat));
        $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($this->settings('settings'))));
        $this->tpl->set('surat', $this->db('mlite_surat_rujukan')->where('no_rawat', revertNoRawat($no_rawat))->oneArray());
        echo $this->tpl->draw(MODULES.'/surat/view/admin/surat.rujukan.html', true);
        exit();
    }

    // SURAT SAKIT METHODS
    public function anySakit($page = 1)
    {
        $this->_addHeaderFiles();
        $perpage = '10';
        $phrase = '';
        if (isset($_POST['s'])) {
            $phrase = $_POST['s'];
        }

        // pagination
        $totalRecords = $this->db('mlite_surat_sakit')
            ->like('nomor_surat', '%' . $phrase . '%')
            ->orLike('nm_pasien', '%' . $phrase . '%')
            ->toArray();
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url([ADMIN, 'surat', 'sakit', '%d']));
        $this->assign['pagination'] = $pagination->nav('pagination', '5');
        $this->assign['totalRecords'] = $totalRecords;

        $offset = $pagination->offset();
        $rows = $this->db('mlite_surat_sakit')
            ->like('nomor_surat', '%' . $phrase . '%')
            ->orLike('nm_pasien', '%' . $phrase . '%')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();

        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                $row['editURL'] = url([ADMIN, 'surat', 'sakitedit', $row['id']]);
                $row['deleteURL'] = url([ADMIN, 'surat', 'sakithapus', $row['id']]);
                $this->assign['list'][] = $row;
            }
        }

        $this->assign['searchURL'] = url([ADMIN, 'surat', 'sakit']);
        $this->assign['addURL'] = url([ADMIN, 'surat', 'sakitadd']);
        $this->assign['phrase'] = $phrase;
        return $this->draw('sakit.manage.html', ['sakit' => $this->assign]);
    }

    public function getSakitAdd()
    {
        $this->_addHeaderFiles();
        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        } else {
            $this->assign['form'] = [
                'id' => '',
                'nomor_surat' => '',
                'no_rawat' => '',
                'no_rkm_medis' => '',
                'nm_pasien' => '',
                'tgl_lahir' => '',
                'umur' => '',
                'jk' => '',
                'alamat' => '',
                'keadaan' => '',
                'diagnosa' => '',
                'lama_angka' => '',
                'lama_huruf' => '',
                'tanggal_mulai' => '',
                'tanggal_selesai' => '',
                'dokter' => '',
                'petugas' => ''
            ];
        }

        return $this->draw('sakit.form.html', ['sakit' => $this->assign]);
    }

    public function getSakitEdit($id)
    {
        $this->_addHeaderFiles();
        $row = $this->db('mlite_surat_sakit')->where('id', $id)->oneArray();
        if (!empty($row)) {
            $this->assign['form'] = $row;
            return $this->draw('sakit.form.html', ['sakit' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'surat', 'sakit']));
        }
    }

    public function postSakitSave($id = null)
    {
        $errors = 0;

        if (!$id) {
            $location = url([ADMIN, 'surat', 'sakit']);
        } else {
            $location = url([ADMIN, 'surat', 'sakit_edit', $id]);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (!$id) {
                $query = $this->db('mlite_surat_sakit')->save($_POST);
            } else {
                $query = $this->db('mlite_surat_sakit')->where('id', $id)->save($_POST);
            }

            if ($query) {
                $this->notify('success', 'Simpan sukes');
            } else {
                $this->notify('failure', 'Simpan gagal');
            }

            redirect($location, $_POST);
        }

        redirect($location, $_POST);
    }

    public function getSakitHapus($id)
    {
        if ($this->db('mlite_surat_sakit')->where('id', $id)->delete()) {
            $this->notify('success', 'Hapus sukses');
        } else {
            $this->notify('failure', 'Hapus gagal');
        }
        redirect(url([ADMIN, 'surat', 'sakit']));
    }

    public function getSuratSakit($no_rawat)
    {
        $kd_dokter = $this->core->getRegPeriksaInfo('kd_dokter', revertNoRawat($no_rawat));
        $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis', revertNoRawat($no_rawat));
        $pasien = $this->db('pasien')
          ->join('kelurahan', 'kelurahan.kd_kel=pasien.kd_kel')
          ->join('kecamatan', 'kecamatan.kd_kec=pasien.kd_kec')
          ->join('kabupaten', 'kabupaten.kd_kab=pasien.kd_kab')
          ->join('propinsi', 'propinsi.kd_prop=pasien.kd_prop')
          ->where('no_rkm_medis', $no_rkm_medis)
          ->oneArray();
        $nm_dokter = $this->core->getPegawaiInfo('nama', $kd_dokter);
        $sip_dokter = $this->core->getDokterInfo('no_ijn_praktek', $kd_dokter);
        $this->tpl->set('pasien', $this->tpl->noParse_array(htmlspecialchars_array($pasien)));
        $this->tpl->set('nm_dokter', $nm_dokter);
        $this->tpl->set('sip_dokter', $sip_dokter);
        $this->tpl->set('no_rawat', revertNoRawat($no_rawat));
        $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($this->settings('settings'))));
        $this->tpl->set('surat', $this->db('mlite_surat_sakit')->where('no_rawat', revertNoRawat($no_rawat))->oneArray());
        echo $this->tpl->draw(MODULES.'/surat/view/admin/surat.sakit.html', true);
        exit();
    }

    // SURAT SEHAT METHODS
    public function anySehat($page = 1)
    {
        $this->_addHeaderFiles();
        $perpage = '10';
        $phrase = '';
        if (isset($_POST['s'])) {
            $phrase = $_POST['s'];
        }

        // pagination
        $totalRecords = $this->db('mlite_surat_sehat')
            ->like('nomor_surat', '%' . $phrase . '%')
            ->orLike('nm_pasien', '%' . $phrase . '%')
            ->toArray();
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url([ADMIN, 'surat', 'sehat', '%d']));
        $this->assign['pagination'] = $pagination->nav('pagination', '5');
        $this->assign['totalRecords'] = $totalRecords;

        $offset = $pagination->offset();
        $rows = $this->db('mlite_surat_sehat')
            ->like('nomor_surat', '%' . $phrase . '%')
            ->orLike('nm_pasien', '%' . $phrase . '%')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();

        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                $row['editURL'] = url([ADMIN, 'surat', 'sehatedit', $row['id']]);
                $row['deleteURL'] = url([ADMIN, 'surat', 'sehathapus', $row['id']]);
                $this->assign['list'][] = $row;
            }
        }

        $this->assign['searchURL'] = url([ADMIN, 'surat', 'sehat']);
        $this->assign['addURL'] = url([ADMIN, 'surat', 'sehatadd']);
        $this->assign['phrase'] = $phrase;
        return $this->draw('sehat.manage.html', ['sehat' => $this->assign]);
    }

    public function getSehatAdd()
    {
        $this->_addHeaderFiles();
        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        } else {
            $this->assign['form'] = [
                'id' => '',
                'nomor_surat' => '',
                'no_rawat' => '',
                'no_rkm_medis' => '',
                'nm_pasien' => '',
                'tgl_lahir' => '',
                'umur' => '',
                'jk' => '',
                'alamat' => '',
                'tanggal' => '',
                'berat_badan' => '',
                'tinggi_badan' => '',
                'tensi' => '',
                'gol_darah' => '',
                'riwayat_penyakit' => '',
                'keperluan' => '',
                'dokter' => '',
                'petugas' => ''
            ];
        }

        return $this->draw('sehat.form.html', ['sehat' => $this->assign]);
    }

    public function getSehatEdit($id)
    {
        $this->_addHeaderFiles();
        $row = $this->db('mlite_surat_sehat')->where('id', $id)->oneArray();
        if (!empty($row)) {
            $this->assign['form'] = $row;
            return $this->draw('sehat.form.html', ['sehat' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'surat', 'sehat']));
        }
    }

    public function postSehatSave($id = null)
    {
        $errors = 0;

        if (!$id) {
            $location = url([ADMIN, 'surat', 'sehat']);
        } else {
            $location = url([ADMIN, 'surat', 'sehat_edit', $id]);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (!$id) {
                $query = $this->db('mlite_surat_sehat')->save($_POST);
            } else {
                $query = $this->db('mlite_surat_sehat')->where('id', $id)->save($_POST);
            }

            if ($query) {
                $this->notify('success', 'Simpan sukes');
            } else {
                $this->notify('failure', 'Simpan gagal');
            }

            redirect($location, $_POST);
        }

        redirect($location, $_POST);
    }

    public function getSehatHapus($id)
    {
        if ($this->db('mlite_surat_sehat')->where('id', $id)->delete()) {
            $this->notify('success', 'Hapus sukses');
        } else {
            $this->notify('failure', 'Hapus gagal');
        }
        redirect(url([ADMIN, 'surat', 'sehat']));
    }

    public function getSuratSehat($no_rawat)
    {
        $kd_dokter = $this->core->getRegPeriksaInfo('kd_dokter', revertNoRawat($no_rawat));
        $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis', revertNoRawat($no_rawat));
        $pasien = $this->db('pasien')
          ->join('kelurahan', 'kelurahan.kd_kel=pasien.kd_kel')
          ->join('kecamatan', 'kecamatan.kd_kec=pasien.kd_kec')
          ->join('kabupaten', 'kabupaten.kd_kab=pasien.kd_kab')
          ->join('propinsi', 'propinsi.kd_prop=pasien.kd_prop')
          ->where('no_rkm_medis', $no_rkm_medis)
          ->oneArray();
        $nm_dokter = $this->core->getPegawaiInfo('nama', $kd_dokter);
        $sip_dokter = $this->core->getDokterInfo('no_ijn_praktek', $kd_dokter);
        $this->tpl->set('pasien', $this->tpl->noParse_array(htmlspecialchars_array($pasien)));
        $this->tpl->set('nm_dokter', $nm_dokter);
        $this->tpl->set('sip_dokter', $sip_dokter);
        $this->tpl->set('no_rawat', revertNoRawat($no_rawat));
        $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($this->settings('settings'))));
        $this->tpl->set('surat', $this->db('mlite_surat_sehat')->where('no_rawat', revertNoRawat($no_rawat))->oneArray());
        echo $this->tpl->draw(MODULES.'/surat/view/admin/surat.sehat.html', true);
        exit();
    }

    // PENGATURAN METHODS
    public function getSettings()
    {
        $this->_addHeaderFiles();
        $this->assign['title'] = 'Pengaturan Surat';
        $this->assign['settings'] = [
            'surat_rujukan_template' => $this->settings->get('surat.rujukan_template', ''),
            'surat_sakit_template' => $this->settings->get('surat.sakit_template', ''),
            'surat_sehat_template' => $this->settings->get('surat.sehat_template', ''),
            'kepala_surat' => $this->settings->get('surat.kepala_surat', ''),
            'footer_surat' => $this->settings->get('surat.footer_surat', '')
        ];
        return $this->draw('settings.html', ['settings' => $this->assign]);
    }

    public function postSettingsSave()
    {
        foreach ($_POST as $key => $value) {
            $this->settings->set('surat.' . $key, $value);
        }
        $this->notify('success', 'Pengaturan berhasil disimpan');
        redirect(url([ADMIN, 'surat', 'settings']));
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
    }
}