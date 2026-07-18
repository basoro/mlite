<?php

namespace Plugins\Surat;

use Systems\AdminModule;

class Admin extends AdminModule
{
    public $assign;

    private const RUJUKAN_PERMINTAAN = [
        'rujukanlab' => [
            'jenis' => 'Laboratorium',
            'marker' => '__SURAT_PERMINTAAN_LAB__',
            'save_route' => 'simpanrujukanlab',
            'verification_type' => 'rujukanlab',
        ],
        'rujukanradiologi' => [
            'jenis' => 'Radiologi',
            'marker' => '__SURAT_PERMINTAAN_RAD__',
            'save_route' => 'simpanrujukanradiologi',
            'verification_type' => 'rujukanradiologi',
        ],
    ];

    public function init()
    {
        $this->_ensureSuratSchema();

        \Systems\Lib\Event::add('rawat_jalan.surat_menu', function ($row) {
            echo '<li><a href="'.url([ADMIN, 'surat', 'suratrujukan', convertNorawat($row['no_rawat'])]).'" target="_blank">Surat Rujukan</a></li>';
            echo '<li><a href="'.url([ADMIN, 'surat', 'rujukanlab', convertNorawat($row['no_rawat'])]).'" target="_blank">Surat Rujukan Lab</a></li>';
            echo '<li><a href="'.url([ADMIN, 'surat', 'rujukanradiologi', convertNorawat($row['no_rawat'])]).'" target="_blank">Surat Rujukan Radiologi</a></li>';
            echo '<li><a href="'.url([ADMIN, 'surat', 'suratsehat', convertNorawat($row['no_rawat'])]).'" target="_blank">Surat Keterangan Sehat</a></li>';
            echo '<li><a href="'.url([ADMIN, 'surat', 'suratsakit', convertNorawat($row['no_rawat'])]).'" target="_blank">Surat Keterangan Sakit</a></li>';
            echo '<li><a href="'.url([ADMIN, 'surat', 'suratbebasnarkoba', convertNorawat($row['no_rawat'])]).'" target="_blank">Surat Bebas Narkoba</a></li>';
            echo '<li><a href="'.url([ADMIN, 'surat', 'suratkontrol', convertNorawat($row['no_rawat'])]).'" target="_blank">Surat Kontrol</a></li>';
        });
    }

    public function navigation()
    {
        return [
            'Kelola' => 'manage',
            'Surat Rujukan' => 'rujukan',
            'Surat Rujukan Lab' => 'rujukanlab',
            'Surat Rujukan Radiologi' => 'rujukanradiologi',
            'Surat Sakit' => 'sakit',
            'Surat Sehat' => 'sehat',
            'Surat Bebas Narkoba' => 'bebasnarkoba',
            'Surat Kematian' => 'kematian',
            'Surat Kontrol' => 'kontrol',
            'Pengaturan Nomor' => 'setting',
        ];
    }

    public function getManage()
    {
        $sub_modules = [
            ['name' => 'Surat Rujukan', 'url' => url([ADMIN, 'surat', 'rujukan']), 'icon' => 'share', 'desc' => 'Cetak Surat Rujukan'],
            ['name' => 'Surat Rujukan Lab', 'url' => url([ADMIN, 'surat', 'rujukanlab']), 'icon' => 'flask', 'desc' => 'Cetak Surat Rujukan Lab'],
            ['name' => 'Surat Rujukan Radiologi', 'url' => url([ADMIN, 'surat', 'rujukanradiologi']), 'icon' => 'film', 'desc' => 'Cetak Surat Rujukan Radiologi'],
            ['name' => 'Surat Sakit', 'url' => url([ADMIN, 'surat', 'sakit']), 'icon' => 'medkit', 'desc' => 'Cetak Surat Keterangan Sakit'],
            ['name' => 'Surat Sehat', 'url' => url([ADMIN, 'surat', 'sehat']), 'icon' => 'heart', 'desc' => 'Cetak Surat Keterangan Sehat'],
            ['name' => 'Surat Bebas Narkoba', 'url' => url([ADMIN, 'surat', 'bebasnarkoba']), 'icon' => 'shield', 'desc' => 'Cetak Surat Bebas Narkoba'],
            ['name' => 'Surat Kematian', 'url' => url([ADMIN, 'surat', 'kematian']), 'icon' => 'file-text-o', 'desc' => 'Cetak Surat Keterangan Kematian'],
            ['name' => 'Surat Kontrol', 'url' => url([ADMIN, 'surat', 'kontrol']), 'icon' => 'calendar-check-o', 'desc' => 'Cetak Surat Kontrol'],
            ['name' => 'Resume Medis', 'url' => url([ADMIN, 'surat', 'resumemedis']), 'icon' => 'file-text', 'desc' => 'Lihat & Cetak Resume Medis'],
            ['name' => 'Pengaturan Nomor', 'url' => url([ADMIN, 'surat', 'setting']), 'icon' => 'cogs', 'desc' => 'Atur Prefix & Nomor Urut per Jenis Surat'],
        ];

        return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
    }

    private function _getNomorSuratTypes()
    {
        return [
            'sakit' => 'Surat Keterangan Sakit',
            'sehat' => 'Surat Keterangan Sehat',
            'rujukan' => 'Surat Rujukan',
            'rujukanlab' => 'Surat Rujukan Lab',
            'rujukanradiologi' => 'Surat Rujukan Radiologi',
            'bebasnarkoba' => 'Surat Bebas Narkoba',
            'kematian' => 'Surat Keterangan Kematian',
        ];
    }

    public function anySetting()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $prefix = trim((string) ($_POST['prefix_surat'] ?? ''));
            $this->db('mlite_settings')->where('module', 'settings')->where('field', 'prefix_surat')->save(['value' => $prefix]);

            foreach ($this->_getNomorSuratTypes() as $type => $label) {
                if (isset($_POST['nomor'][$type])) {
                    \Systems\Lib\SuratVerification::setNomorSuratCounterValue($this->core, $type, $_POST['nomor'][$type]);
                }
            }

            $this->notify('success', 'Pengaturan nomor surat berhasil disimpan.');
            redirect(url([ADMIN, 'surat', 'setting']));
        }

        $types = [];
        foreach ($this->_getNomorSuratTypes() as $type => $label) {
            $types[] = [
                'type' => $type,
                'label' => $label,
                'nomor' => \Systems\Lib\SuratVerification::getNomorSuratCounterValue($this->core, $type),
            ];
        }

        return $this->draw('setting.html', [
            'types' => $types,
            'prefix_surat' => trim((string) $this->settings->get('settings.prefix_surat')),
        ]);
    }

    // SURAT RUJUKAN METHODS
    public function anyRujukan($no_rawat = '')
    {
        return $this->_handleSuratCetak($no_rawat, 'rujukan', 'suratrujukan', 'Surat Rujukan', 'surat rujukan');
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
        if (!empty($row) && !$this->_isRujukanPermintaanRow($row)) {
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
        $row = $this->db('mlite_surat_rujukan')->where('id', $id)->oneArray();
        if (!empty($row) && !$this->_isRujukanPermintaanRow($row) && $this->db('mlite_surat_rujukan')->where('id', $id)->delete()) {
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
          ->leftJoin('kelurahan', 'kelurahan.kd_kel=pasien.kd_kel')
          ->leftJoin('kecamatan', 'kecamatan.kd_kec=pasien.kd_kec')
          ->leftJoin('kabupaten', 'kabupaten.kd_kab=pasien.kd_kab')
          ->leftJoin('propinsi', 'propinsi.kd_prop=pasien.kd_prop')
          ->where('no_rkm_medis', $no_rkm_medis)
          ->oneArray();
        $nm_dokter = $this->core->getPegawaiInfo('nama', $kd_dokter);
        $sip_dokter = $this->core->getDokterInfo('no_ijn_praktek', $kd_dokter);
        $this->tpl->set('pasien', $this->tpl->noParse_array(htmlspecialchars_array($pasien)));
        $this->tpl->set('nm_dokter', $nm_dokter);
        $this->tpl->set('sip_dokter', $sip_dokter);
        $this->tpl->set('no_rawat', revertNoRawat($no_rawat));
        $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($this->settings('settings'))));
        $surat = \Systems\Lib\SuratVerification::preparePrintRecord($this->core, 'rujukan', [
            'no_rawat' => revertNoRawat($no_rawat),
            'no_rkm_medis' => $no_rkm_medis,
            'nm_pasien' => $pasien['nm_pasien'] ?? '',
            'tgl_lahir' => $pasien['tgl_lahir'] ?? '',
            'umur' => !empty($pasien['tgl_lahir']) ? hitungUmur($pasien['tgl_lahir']) : '',
            'jk' => $pasien['jk'] ?? '',
            'alamat' => $pasien['alamat'] ?? '',
            'dokter' => $nm_dokter,
        ]);
        $nomor_surat = !empty(trim((string)($surat['nomor_surat'] ?? ''))) ? $surat['nomor_surat'] : $this->_getNomorSuratFormat('rujukan');
        $this->tpl->set('surat', $surat);
        $this->tpl->set('nomor_surat', $nomor_surat);
        $this->tpl->set('surat_verifikasi_url', \Systems\Lib\SuratVerification::getVerificationUrl($surat['token_verifikasi']));
        echo $this->tpl->draw(MODULES.'/surat/view/admin/surat.rujukan.html', true);
        exit();
    }

    public function anyRujukanLab($no_rawat = '')
    {
        return $this->_handleRujukanPermintaan($no_rawat, 'Laboratorium', 'rujukanlab', 'Surat Rujukan Lab');
    }

    public function anyRujukanRadiologi($no_rawat = '')
    {
        return $this->_handleRujukanPermintaan($no_rawat, 'Radiologi', 'rujukanradiologi', 'Surat Rujukan Radiologi');
    }

    public function postSimpanRujukanLab()
    {
        $this->_saveRujukanPermintaan('rujukanlab');
    }

    public function postSimpanRujukanRadiologi()
    {
        $this->_saveRujukanPermintaan('rujukanradiologi');
    }

    public function getRujukanLabHapus($id)
    {
        $this->_hapusRujukanPermintaan($id, 'rujukanlab');
    }

    public function getRujukanRadiologiHapus($id)
    {
        $this->_hapusRujukanPermintaan($id, 'rujukanradiologi');
    }

    private function _hapusRujukanPermintaan($id, $route)
    {
        $config = $this->_getRujukanPermintaanConfig($route);
        $row = $this->db('mlite_surat_rujukan')->where('id', $id)->oneArray();
        if (!empty($row) && isset($row['di']) && $row['di'] === $config['marker']) {
            if ($this->db('mlite_surat_rujukan')->where('id', $id)->delete()) {
                $this->notify('success', 'Hapus sukses');
            } else {
                $this->notify('failure', 'Hapus gagal');
            }
        } else {
            $this->notify('failure', 'Data tidak ditemukan atau tidak valid');
        }
        redirect(url([ADMIN, 'surat', $route]));
    }

    // SURAT SAKIT METHODS
    public function anySakit($no_rawat = '')
    {
        return $this->_handleSuratCetak($no_rawat, 'sakit', 'suratsakit', 'Surat Keterangan Sakit', 'surat keterangan sakit');
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
          ->leftJoin('kelurahan', 'kelurahan.kd_kel=pasien.kd_kel')
          ->leftJoin('kecamatan', 'kecamatan.kd_kec=pasien.kd_kec')
          ->leftJoin('kabupaten', 'kabupaten.kd_kab=pasien.kd_kab')
          ->leftJoin('propinsi', 'propinsi.kd_prop=pasien.kd_prop')
          ->where('no_rkm_medis', $no_rkm_medis)
          ->oneArray();
        $nm_dokter = $this->core->getPegawaiInfo('nama', $kd_dokter);
        $sip_dokter = $this->core->getDokterInfo('no_ijn_praktek', $kd_dokter);
        $this->tpl->set('pasien', $this->tpl->noParse_array(htmlspecialchars_array($pasien)));
        $this->tpl->set('nm_dokter', $nm_dokter);
        $this->tpl->set('sip_dokter', $sip_dokter);
        $this->tpl->set('no_rawat', revertNoRawat($no_rawat));
        $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($this->settings('settings'))));
        $surat = \Systems\Lib\SuratVerification::preparePrintRecord($this->core, 'sakit', [
            'no_rawat' => revertNoRawat($no_rawat),
            'no_rkm_medis' => $no_rkm_medis,
            'nm_pasien' => $pasien['nm_pasien'] ?? '',
            'tgl_lahir' => $pasien['tgl_lahir'] ?? '',
            'umur' => !empty($pasien['tgl_lahir']) ? hitungUmur($pasien['tgl_lahir']) : '',
            'jk' => $pasien['jk'] ?? '',
            'alamat' => $pasien['alamat'] ?? '',
            'dokter' => $nm_dokter,
        ]);
        $nomor_surat = !empty(trim((string)($surat['nomor_surat'] ?? ''))) ? $surat['nomor_surat'] : $this->_getNomorSuratFormat('sakit');
        $this->tpl->set('surat', $surat);
        $this->tpl->set('nomor_surat', $nomor_surat);
        $this->tpl->set('surat_verifikasi_url', \Systems\Lib\SuratVerification::getVerificationUrl($surat['token_verifikasi']));
        echo $this->tpl->draw(MODULES.'/surat/view/admin/surat.sakit.html', true);
        exit();
    }

    // SURAT SEHAT METHODS
    public function anySehat($no_rawat = '')
    {
        return $this->_handleSuratCetak($no_rawat, 'sehat', 'suratsehat', 'Surat Keterangan Sehat', 'surat keterangan sehat');
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
          ->leftJoin('kelurahan', 'kelurahan.kd_kel=pasien.kd_kel')
          ->leftJoin('kecamatan', 'kecamatan.kd_kec=pasien.kd_kec')
          ->leftJoin('kabupaten', 'kabupaten.kd_kab=pasien.kd_kab')
          ->leftJoin('propinsi', 'propinsi.kd_prop=pasien.kd_prop')
          ->where('no_rkm_medis', $no_rkm_medis)
          ->oneArray();
        $nm_dokter = $this->core->getPegawaiInfo('nama', $kd_dokter);
        $sip_dokter = $this->core->getDokterInfo('no_ijn_praktek', $kd_dokter);
        $this->tpl->set('pasien', $this->tpl->noParse_array(htmlspecialchars_array($pasien)));
        $this->tpl->set('nm_dokter', $nm_dokter);
        $this->tpl->set('sip_dokter', $sip_dokter);
        $this->tpl->set('no_rawat', revertNoRawat($no_rawat));
        $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($this->settings('settings'))));
        $penilaian = $this->db('pemeriksaan_ralan')->where('no_rawat', revertNoRawat($no_rawat))->desc('tgl_perawatan')->desc('jam_rawat')->oneArray();
        $surat = \Systems\Lib\SuratVerification::preparePrintRecord($this->core, 'sehat', [
            'no_rawat' => revertNoRawat($no_rawat),
            'no_rkm_medis' => $no_rkm_medis,
            'nm_pasien' => $pasien['nm_pasien'] ?? '',
            'tgl_lahir' => $pasien['tgl_lahir'] ?? '',
            'umur' => !empty($pasien['tgl_lahir']) ? hitungUmur($pasien['tgl_lahir']) : '',
            'jk' => $pasien['jk'] ?? '',
            'alamat' => $pasien['alamat'] ?? '',
            'dokter' => $nm_dokter,
        ]);
        if (empty(trim((string)($surat['berat_badan'] ?? '')))) $surat['berat_badan'] = $penilaian['berat'] ?? '';
        if (empty(trim((string)($surat['tinggi_badan'] ?? '')))) $surat['tinggi_badan'] = $penilaian['tinggi'] ?? '';
        if (empty(trim((string)($surat['tensi'] ?? '')))) $surat['tensi'] = $penilaian['tensi'] ?? '';
        if (empty(trim((string)($surat['gol_darah'] ?? '')))) { $gd = trim((string)($pasien['gol_darah'] ?? '')); $surat['gol_darah'] = ($gd !== '' && $gd !== '-') ? $gd : ''; }
        $nomor_surat = !empty(trim((string)($surat['nomor_surat'] ?? ''))) ? $surat['nomor_surat'] : $this->_getNomorSuratFormat('sehat');
        $this->tpl->set('surat', $surat);
        $this->tpl->set('nomor_surat', $nomor_surat);
        $this->tpl->set('surat_verifikasi_url', \Systems\Lib\SuratVerification::getVerificationUrl($surat['token_verifikasi']));
        echo $this->tpl->draw(MODULES.'/surat/view/admin/surat.sehat.html', true);
        exit();
    }

    // SURAT BEBAS NARKOBA METHODS
    public function anyBebasNarkoba($no_rawat = '')
    {
        return $this->_handleSuratCetak($no_rawat, 'bebasnarkoba', 'suratbebasnarkoba', 'Surat Bebas Narkoba', 'surat bebas narkoba');
    }

    public function getBebasNarkobaAdd()
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
                'tanggal' => date('Y-m-d'),
                'hasil_pemeriksaan' => 'Negatif narkoba',
                'keperluan' => '',
                'dokter' => '',
                'petugas' => ''
            ];
        }

        return $this->draw('bebasnarkoba.form.html', ['bebasnarkoba' => $this->assign]);
    }

    public function getBebasNarkobaEdit($id)
    {
        $this->_addHeaderFiles();
        $row = $this->db('mlite_surat_bebas_narkoba')->where('id', $id)->oneArray();
        if (!empty($row)) {
            $this->assign['form'] = $row;
            return $this->draw('bebasnarkoba.form.html', ['bebasnarkoba' => $this->assign]);
        }

        redirect(url([ADMIN, 'surat', 'bebasnarkoba']));
    }

    public function postBebasNarkobaSave($id = null)
    {
        $errors = 0;

        if (!$id) {
            $location = url([ADMIN, 'surat', 'bebasnarkoba']);
        } else {
            $location = url([ADMIN, 'surat', 'bebasnarkobaedit', $id]);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (!$id) {
                $query = $this->db('mlite_surat_bebas_narkoba')->save($_POST);
            } else {
                $query = $this->db('mlite_surat_bebas_narkoba')->where('id', $id)->save($_POST);
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

    public function getBebasNarkobaHapus($id)
    {
        if ($this->db('mlite_surat_bebas_narkoba')->where('id', $id)->delete()) {
            $this->notify('success', 'Hapus sukses');
        } else {
            $this->notify('failure', 'Hapus gagal');
        }
        redirect(url([ADMIN, 'surat', 'bebasnarkoba']));
    }

    public function getSuratBebasNarkoba($no_rawat)
    {
        $kd_dokter = $this->core->getRegPeriksaInfo('kd_dokter', revertNoRawat($no_rawat));
        $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis', revertNoRawat($no_rawat));
        $pasien = $this->db('pasien')
          ->leftJoin('kelurahan', 'kelurahan.kd_kel=pasien.kd_kel')
          ->leftJoin('kecamatan', 'kecamatan.kd_kec=pasien.kd_kec')
          ->leftJoin('kabupaten', 'kabupaten.kd_kab=pasien.kd_kab')
          ->leftJoin('propinsi', 'propinsi.kd_prop=pasien.kd_prop')
          ->where('no_rkm_medis', $no_rkm_medis)
          ->oneArray();
        $nm_dokter = $this->core->getPegawaiInfo('nama', $kd_dokter);
        $sip_dokter = $this->core->getDokterInfo('no_ijn_praktek', $kd_dokter);
        $surat = $this->db('mlite_surat_bebas_narkoba')->where('no_rawat', revertNoRawat($no_rawat))->oneArray();

        if (empty($surat)) {
            $surat = [
                'nomor_surat' => '',
                'tanggal' => date('Y-m-d'),
                'hasil_pemeriksaan' => 'Negatif narkoba',
                'keperluan' => '',
            ];
        }

        $this->tpl->set('pasien', $this->tpl->noParse_array(htmlspecialchars_array($pasien)));
        $this->tpl->set('nm_dokter', $nm_dokter);
        $this->tpl->set('sip_dokter', $sip_dokter);
        $this->tpl->set('no_rawat', revertNoRawat($no_rawat));
        $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($this->settings('settings'))));
        $surat = \Systems\Lib\SuratVerification::preparePrintRecord($this->core, 'bebasnarkoba', array_merge($surat, [
            'no_rawat' => revertNoRawat($no_rawat),
            'no_rkm_medis' => $no_rkm_medis,
            'nm_pasien' => $pasien['nm_pasien'] ?? '',
            'tgl_lahir' => $pasien['tgl_lahir'] ?? '',
            'umur' => !empty($pasien['tgl_lahir']) ? hitungUmur($pasien['tgl_lahir']) : '',
            'jk' => $pasien['jk'] ?? '',
            'alamat' => $pasien['alamat'] ?? '',
            'dokter' => $nm_dokter,
        ]));
        $nomor_surat = !empty(trim((string) ($surat['nomor_surat'] ?? '')))
            ? $surat['nomor_surat']
            : $this->_getNomorSuratFormat('bebasnarkoba');
        $this->tpl->set('surat', $surat);
        $this->tpl->set('nomor_surat', $nomor_surat);
        $this->tpl->set('nomor_surat_tersimpan', !empty(trim((string) ($surat['nomor_surat'] ?? ''))));
        $this->tpl->set('surat_verifikasi_url', \Systems\Lib\SuratVerification::getVerificationUrl($surat['token_verifikasi']));
        echo $this->tpl->draw(MODULES.'/surat/view/admin/surat.bebasnarkoba.html', true);
        exit();
    }

    public function postSimpanSuratBebasNarkoba()
    {
        header('Content-Type: application/json');

        $data_save = [
            'nomor_surat' => isset($_POST['nomor_surat']) ? trim($_POST['nomor_surat']) : '',
            'no_rawat' => isset($_POST['no_rawat']) ? trim($_POST['no_rawat']) : '',
            'no_rkm_medis' => isset($_POST['no_rkm_medis']) ? trim($_POST['no_rkm_medis']) : '',
            'nm_pasien' => isset($_POST['nm_pasien']) ? trim($_POST['nm_pasien']) : '',
            'tgl_lahir' => isset($_POST['tgl_lahir']) ? trim($_POST['tgl_lahir']) : '',
            'umur' => isset($_POST['umur']) ? trim($_POST['umur']) : '',
            'jk' => isset($_POST['jk']) ? trim($_POST['jk']) : '',
            'alamat' => isset($_POST['alamat']) ? trim($_POST['alamat']) : '',
            'tanggal' => isset($_POST['tanggal']) ? trim($_POST['tanggal']) : '',
            'hasil_pemeriksaan' => isset($_POST['hasil_pemeriksaan']) ? trim($_POST['hasil_pemeriksaan']) : '',
            'keperluan' => isset($_POST['keperluan']) ? trim($_POST['keperluan']) : '',
            'dokter' => isset($_POST['dokter']) ? trim($_POST['dokter']) : '',
            'petugas' => isset($_POST['petugas']) ? trim($_POST['petugas']) : ''
        ];

        if (checkEmptyFields(['nomor_surat', 'no_rawat', 'no_rkm_medis', 'nm_pasien'], $data_save)) {
            echo json_encode(['status' => 'error', 'msg' => 'Data surat belum lengkap']);
            exit();
        }

        $existing = $this->db('mlite_surat_bebas_narkoba')
            ->where('no_rawat', $data_save['no_rawat'])
            ->oneArray();
        $hadNomorBefore = !empty(trim((string) ($existing['nomor_surat'] ?? '')));
        $result = \Systems\Lib\SuratVerification::save($this->core, 'bebasnarkoba', $data_save);

        if ($result['success']) {
            if (!$hadNomorBefore && $data_save['nomor_surat'] !== '') {
                $this->_incrementNomorSuratCounter('bebasnarkoba');
            }
            echo json_encode(['status' => 'success']);
            exit();
        }

        echo json_encode(['status' => 'error', 'msg' => 'Gagal menyimpan surat']);
        exit();
    }

    // SURAT KEMATIAN METHODS
    public function anyKematian($no_rawat = '')
    {
        return $this->_handleSuratCetak($no_rawat, 'kematian', 'suratkematian', 'Surat Keterangan Kematian', 'surat keterangan kematian');
    }

    public function getSuratKematian($no_rawat)
    {
        $clean = revertNoRawat($no_rawat);
        $kd_dokter = $this->core->getRegPeriksaInfo('kd_dokter', $clean);
        $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis', $clean);
        $pasien = $this->db('pasien')
            ->leftJoin('kelurahan', 'kelurahan.kd_kel=pasien.kd_kel')
            ->leftJoin('kecamatan', 'kecamatan.kd_kec=pasien.kd_kec')
            ->leftJoin('kabupaten', 'kabupaten.kd_kab=pasien.kd_kab')
            ->leftJoin('propinsi', 'propinsi.kd_prop=pasien.kd_prop')
            ->where('no_rkm_medis', $no_rkm_medis)
            ->oneArray();
        $nm_dokter = $this->core->getPegawaiInfo('nama', $kd_dokter);
        $sip_dokter = $this->core->getDokterInfo('no_ijn_praktek', $kd_dokter);
        $pasien = array_map(function($v) {
            return is_string($v) ? preg_replace('/[\r\n\t]+/', ' ', $v) : $v;
        }, $pasien ?? []);
        $nm_dokter = preg_replace('/[\r\n\t]+/', ' ', (string)$nm_dokter);
        $surat = \Systems\Lib\SuratVerification::preparePrintRecord($this->core, 'kematian', [
            'no_rawat' => $clean,
            'no_rkm_medis' => $no_rkm_medis,
            'nm_pasien' => $pasien['nm_pasien'] ?? '',
            'no_ktp' => $pasien['no_ktp'] ?? '',
            'tgl_lahir' => $pasien['tgl_lahir'] ?? '',
            'umur' => !empty($pasien['tgl_lahir']) ? hitungUmur($pasien['tgl_lahir']) : '',
            'jk' => $pasien['jk'] ?? '',
            'alamat' => $pasien['alamat'] ?? '',
            'dokter' => $nm_dokter,
        ]);
        $nomor_surat = !empty(trim((string)($surat['nomor_surat'] ?? ''))) ? $surat['nomor_surat'] : $this->_getNomorSuratFormat('kematian');
        $this->tpl->set('pasien', $this->tpl->noParse_array(htmlspecialchars_array($pasien)));
        $this->tpl->set('nm_dokter', $nm_dokter);
        $this->tpl->set('sip_dokter', $sip_dokter);
        $this->tpl->set('no_rawat', $clean);
        $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($this->settings('settings'))));
        $this->tpl->set('surat', $this->tpl->noParse_array(htmlspecialchars_array($surat)));
        $this->tpl->set('nomor_surat', $nomor_surat);
        $this->tpl->set('nomor_surat_tersimpan', !empty(trim((string)($surat['nomor_surat'] ?? ''))));
        $this->tpl->set('surat_verifikasi_url', \Systems\Lib\SuratVerification::getVerificationUrl($surat['token_verifikasi'] ?? ''));
        echo $this->tpl->draw(MODULES.'/surat/view/admin/surat.kematian.html', true);
        exit();
    }

    public function getKematianEdit($id)
    {
        $row = $this->db('mlite_surat_kematian')->where('id', $id)->oneArray();
        if (!empty($row) && !empty($row['no_rawat'])) {
            redirect(url([ADMIN, 'surat', 'suratkematian', convertNorawat($row['no_rawat'])]));
        }
        redirect(url([ADMIN, 'surat', 'kematian']));
    }

    public function getKematianHapus($id)
    {
        if ($this->db('mlite_surat_kematian')->where('id', $id)->delete()) {
            $this->notify('success', 'Hapus sukses');
        } else {
            $this->notify('failure', 'Hapus gagal');
        }
        redirect(url([ADMIN, 'surat', 'kematian']));
    }

    public function postSimpanSuratKematian()
    {
        header('Content-Type: application/json');
        $data_save = [
            'nomor_surat'       => trim((string)($_POST['nomor_surat'] ?? '')),
            'no_rawat'          => trim((string)($_POST['no_rawat'] ?? '')),
            'no_rkm_medis'      => trim((string)($_POST['no_rkm_medis'] ?? '')),
            'nm_pasien'         => trim((string)($_POST['nm_pasien'] ?? '')),
            'no_ktp'            => trim((string)($_POST['no_ktp'] ?? '')),
            'tgl_lahir'         => trim((string)($_POST['tgl_lahir'] ?? '')),
            'umur'              => trim((string)($_POST['umur'] ?? '')),
            'jk'                => trim((string)($_POST['jk'] ?? '')),
            'alamat'            => trim((string)($_POST['alamat'] ?? '')),
            'tanggal_meninggal' => trim((string)($_POST['tanggal_meninggal'] ?? '')),
            'jam_meninggal'     => trim((string)($_POST['jam_meninggal'] ?? '')),
            'sebab_kematian'    => trim((string)($_POST['sebab_kematian'] ?? '')),
            'keterangan'        => trim((string)($_POST['keterangan'] ?? '')),
            'dokter'            => trim((string)($_POST['dokter'] ?? '')),
            'petugas'           => trim((string)($_POST['petugas'] ?? '')),
        ];
        if (checkEmptyFields(['nomor_surat', 'no_rawat', 'no_rkm_medis', 'nm_pasien'], $data_save)) {
            echo json_encode(['status' => 'error', 'msg' => 'Data surat belum lengkap']);
            exit();
        }
        $existing = $this->db('mlite_surat_kematian')->where('no_rawat', $data_save['no_rawat'])->oneArray();
        $hadNomorBefore = !empty(trim((string)($existing['nomor_surat'] ?? '')));
        $result = \Systems\Lib\SuratVerification::save($this->core, 'kematian', $data_save);
        if ($result['success']) {
            if (!$hadNomorBefore && $data_save['nomor_surat'] !== '') {
                $this->_incrementNomorSuratCounter('kematian');
            }
            echo json_encode(['status' => 'success']);
            exit();
        }
        echo json_encode(['status' => 'error', 'msg' => 'Gagal menyimpan surat']);
        exit();
    }

    // SURAT KONTROL METHODS
    public function anyKontrol($no_rawat = '')
    {
        if (!empty($no_rawat)) {
            redirect(url([ADMIN, 'surat', 'suratkontrol', $no_rawat]));
        }

        $this->_addHeaderFiles();

        $phrase  = isset($_GET['s'])    ? trim((string) $_GET['s'])    : '';
        $page    = max(1, (int) (isset($_GET['page']) ? $_GET['page'] : 1));
        $perpage = 10;

        $buildQuery = function () use ($phrase) {
            $query = $this->db('booking_registrasi')
                ->join('pasien', 'pasien.no_rkm_medis=booking_registrasi.no_rkm_medis')
                ->leftJoin('poliklinik', 'poliklinik.kd_poli=booking_registrasi.kd_poli')
                ->leftJoin('dokter', 'dokter.kd_dokter=booking_registrasi.kd_dokter')
                ->where('booking_registrasi.status', 'Belum');
            if ($phrase !== '') {
                $query->where(function ($s) use ($phrase) {
                    $s->like('pasien.nm_pasien', '%' . $phrase . '%')
                      ->orLike('pasien.no_rkm_medis', '%' . $phrase . '%');
                });
            }
            return $query;
        };

        $total = count($buildQuery()->toArray());
        $rows = $buildQuery()
            ->select('booking_registrasi.*, pasien.nm_pasien, pasien.no_rkm_medis, poliklinik.nm_poli, dokter.nm_dokter')
            ->desc('booking_registrasi.tanggal_periksa')
            ->offset(($page - 1) * $perpage)
            ->limit($perpage)
            ->toArray();

        $list = [];
        foreach ($rows as $row) {
            $row = htmlspecialchars_array($row);
            $latestNoRawat = $this->_resolveLatestNoRawatByRM($row['no_rkm_medis']);
            $row['printURL'] = !empty($latestNoRawat) ? url([ADMIN, 'surat', 'suratkontrol', convertNorawat($latestNoRawat)]) : '';
            $list[] = $row;
        }

        $paginationUrl = url([ADMIN, 'surat', 'kontrol']) . '&s=' . urlencode($phrase) . '&page=%d';
        $pagination = (new \Systems\Lib\Pagination($page, $total, $perpage, $paginationUrl))->nav('pagination', '5');

        $this->assign['title']      = 'Surat Kontrol';
        $this->assign['searchURL']  = url([ADMIN, 'surat', 'kontrol']);
        $this->assign['phrase']     = $phrase;
        $this->assign['list']       = $list;
        $this->assign['pagination'] = $pagination;
        $this->assign['total']      = $total;

        return $this->draw('kontrol.manage.html', ['kontrol' => $this->assign]);
    }

    private function _resolveLatestNoRawatByRM($no_rkm_medis)
    {
        $row = $this->db('reg_periksa')
            ->where('no_rkm_medis', $no_rkm_medis)
            ->desc('tgl_registrasi')
            ->desc('jam_reg')
            ->oneArray();

        return $row['no_rawat'] ?? '';
    }

    public function getSuratKontrol($no_rawat)
    {
        $clean = revertNoRawat($no_rawat);
        $kd_dokter = $this->core->getRegPeriksaInfo('kd_dokter', $clean);
        $kd_poli = $this->core->getRegPeriksaInfo('kd_poli', $clean);
        $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis', $clean);
        $pasien = $this->db('pasien')
            ->leftJoin('kelurahan', 'kelurahan.kd_kel=pasien.kd_kel')
            ->leftJoin('kecamatan', 'kecamatan.kd_kec=pasien.kd_kec')
            ->leftJoin('kabupaten', 'kabupaten.kd_kab=pasien.kd_kab')
            ->leftJoin('propinsi', 'propinsi.kd_prop=pasien.kd_prop')
            ->where('no_rkm_medis', $no_rkm_medis)
            ->oneArray();
        $nm_dokter = $this->core->getPegawaiInfo('nama', $kd_dokter);
        $sip_dokter = $this->core->getDokterInfo('no_ijn_praktek', $kd_dokter);
        $poliklinik = !empty($kd_poli) ? $this->db('poliklinik')->where('kd_poli', $kd_poli)->oneArray() : [];
        $pendingBooking = $this->db('booking_registrasi')
            ->leftJoin('poliklinik', 'poliklinik.kd_poli=booking_registrasi.kd_poli')
            ->leftJoin('dokter', 'dokter.kd_dokter=booking_registrasi.kd_dokter')
            ->where('booking_registrasi.no_rkm_medis', $no_rkm_medis)
            ->where('booking_registrasi.status', 'Belum')
            ->desc('booking_registrasi.tanggal_periksa')
            ->oneArray();
        $pendingSkdp = $this->db('skdp_bpjs')
            ->where('no_rkm_medis', $no_rkm_medis)
            ->where('status', 'Menunggu')
            ->desc('tanggal_rujukan')
            ->oneArray();
        $this->tpl->set('pasien', $this->tpl->noParse_array(htmlspecialchars_array($pasien)));
        $this->tpl->set('nm_dokter', $nm_dokter);
        $this->tpl->set('sip_dokter', $sip_dokter);
        $this->tpl->set('no_rawat', $clean);
        $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($this->settings('settings'))));
        $surat = \Systems\Lib\SuratVerification::prepareKontrolRecord($this->core, $no_rkm_medis, [
            'nm_pasien' => $pasien['nm_pasien'] ?? '',
            'tgl_lahir' => $pasien['tgl_lahir'] ?? '',
            'umur' => !empty($pasien['tgl_lahir']) ? hitungUmur($pasien['tgl_lahir']) : '',
            'jk' => $pasien['jk'] ?? '',
            'alamat' => $pasien['alamat'] ?? '',
            'dokter' => $nm_dokter,
        ]);
        $belumTersimpan = empty($surat['id']);
        if ($belumTersimpan && !empty($pendingBooking)) {
            if (!empty($pendingBooking['tanggal_periksa'])) $surat['tanggal_kontrol'] = $pendingBooking['tanggal_periksa'];
            if (!empty($pendingBooking['nm_poli'])) $surat['poli_tujuan'] = $pendingBooking['nm_poli'];
            if (!empty($pendingBooking['nm_dokter'])) $surat['dokter_tujuan'] = $pendingBooking['nm_dokter'];
        }
        if ($belumTersimpan && !empty($pendingSkdp['alasan1'])) $surat['keterangan'] = $pendingSkdp['alasan1'];
        if (empty(trim((string)($surat['poli_tujuan'] ?? '')))) $surat['poli_tujuan'] = $poliklinik['nm_poli'] ?? '';
        if (empty(trim((string)($surat['dokter_tujuan'] ?? '')))) $surat['dokter_tujuan'] = $nm_dokter;
        $this->tpl->set('surat', $this->tpl->noParse_array(htmlspecialchars_array($surat)));
        $this->tpl->set('surat_verifikasi_url', \Systems\Lib\SuratVerification::getVerificationUrl($surat['token_verifikasi'] ?? ''));
        echo $this->tpl->draw(MODULES.'/surat/view/admin/surat.kontrol.html', true);
        exit();
    }

    public function getKontrolEdit($id)
    {
        $row = $this->db('mlite_surat_kontrol')->where('id', $id)->oneArray();
        $noRawat = !empty($row['no_rkm_medis']) ? $this->_resolveLatestNoRawatByRM($row['no_rkm_medis']) : '';
        if (!empty($noRawat)) {
            redirect(url([ADMIN, 'surat', 'suratkontrol', convertNorawat($noRawat)]));
        }
        redirect(url([ADMIN, 'surat', 'kontrol']));
    }

    public function getKontrolHapus($id)
    {
        if ($this->db('mlite_surat_kontrol')->where('id', $id)->delete()) {
            $this->notify('success', 'Hapus sukses');
        } else {
            $this->notify('failure', 'Hapus gagal');
        }
        redirect(url([ADMIN, 'surat', 'kontrol']));
    }

    public function postSimpanSuratKontrol()
    {
        header('Content-Type: application/json');
        $data_save = [
            'no_rkm_medis'    => trim((string)($_POST['no_rkm_medis'] ?? '')),
            'nm_pasien'       => trim((string)($_POST['nm_pasien'] ?? '')),
            'tgl_lahir'       => trim((string)($_POST['tgl_lahir'] ?? '')),
            'umur'            => trim((string)($_POST['umur'] ?? '')),
            'jk'              => trim((string)($_POST['jk'] ?? '')),
            'alamat'          => trim((string)($_POST['alamat'] ?? '')),
            'tanggal_kontrol' => trim((string)($_POST['tanggal_kontrol'] ?? '')),
            'poli_tujuan'     => trim((string)($_POST['poli_tujuan'] ?? '')),
            'dokter_tujuan'   => trim((string)($_POST['dokter_tujuan'] ?? '')),
            'keterangan'      => trim((string)($_POST['keterangan'] ?? '')),
            'dokter'          => trim((string)($_POST['dokter'] ?? '')),
            'petugas'         => trim((string)($_POST['petugas'] ?? '')),
        ];

        if (checkEmptyFields(['no_rkm_medis', 'nm_pasien'], $data_save)) {
            echo json_encode(['status' => 'error', 'msg' => 'Data surat belum lengkap']);
            exit();
        }

        $result = \Systems\Lib\SuratVerification::saveKontrol($this->core, $data_save);

        if ($result['success']) {
            echo json_encode(['status' => 'success']);
            exit();
        }

        echo json_encode(['status' => 'error', 'msg' => 'Gagal menyimpan surat']);
        exit();
    }

    // RESUME MEDIS METHODS
    // resume_pasien dipakai bersama oleh dokter_ralan & dokter_igd.
    public function anyResumeMedis()
    {
        $this->_addHeaderFiles();

        $phrase  = isset($_GET['s']) ? trim((string) $_GET['s']) : '';
        $page    = max(1, (int) (isset($_GET['page']) ? $_GET['page'] : 1));
        $perpage = 10;

        $countQ = $this->db('resume_pasien')
            ->join('reg_periksa', 'reg_periksa.no_rawat=resume_pasien.no_rawat')
            ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis');
        if ($phrase !== '') {
            $countQ->where(function ($s) use ($phrase) {
                $s->like('pasien.nm_pasien', '%' . $phrase . '%')
                  ->orLike('pasien.no_rkm_medis', '%' . $phrase . '%');
            });
        }
        $total = count($countQ->toArray());

        $dataQ = $this->db('resume_pasien')
            ->join('reg_periksa', 'reg_periksa.no_rawat=resume_pasien.no_rawat')
            ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis');
        if ($phrase !== '') {
            $dataQ->where(function ($s) use ($phrase) {
                $s->like('pasien.nm_pasien', '%' . $phrase . '%')
                  ->orLike('pasien.no_rkm_medis', '%' . $phrase . '%');
            });
        }
        $rows = $dataQ->desc('resume_pasien.no_rawat')
            ->offset(($page - 1) * $perpage)
            ->limit($perpage)
            ->toArray();

        $list = [];
        foreach ($rows as $row) {
            $row['printURL'] = url([ADMIN, 'surat', 'resumemediscetak', convertNorawat($row['no_rawat'])]);
            $list[] = $row;
        }

        $paginationUrl = url([ADMIN, 'surat', 'resumemedis']) . '?s=' . urlencode($phrase) . '&page=%d';
        $paginationObj = new \Systems\Lib\Pagination($page, $total, $perpage, $paginationUrl);

        $this->assign['title']      = 'Resume Medis';
        $this->assign['searchURL']  = url([ADMIN, 'surat', 'resumemedis']);
        $this->assign['phrase']     = $phrase;
        $this->assign['list']       = $list;
        $this->assign['pagination'] = $paginationObj->nav('pagination', '5');
        $this->assign['total']      = $total;

        return $this->draw('resume.medis.form.html', ['resume_medis' => $this->assign]);
    }

    public function getResumeMedisCetak($no_rawat)
    {
        $clean = revertNoRawat($no_rawat);
        $reg = $this->db('reg_periksa')
            ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
            ->join('dokter',     'dokter.kd_dokter=reg_periksa.kd_dokter')
            ->join('penjab',     'penjab.kd_pj=reg_periksa.kd_pj')
            ->where('reg_periksa.no_rawat', $clean)
            ->oneArray();
        $pasien = $this->db('pasien')
            ->where('no_rkm_medis', $reg['no_rkm_medis'] ?? '')
            ->oneArray();
        $resume = $this->db('resume_pasien')
            ->join('dokter', 'dokter.kd_dokter=resume_pasien.kd_dokter')
            ->where('no_rawat', $clean)
            ->oneArray();
        $pemfis = $this->db('pemeriksaan_ralan')
            ->where('no_rawat', $clean)
            ->desc('tgl_perawatan')->desc('jam_rawat')
            ->oneArray();
        $diagnosa_list = $this->db('diagnosa_pasien')
            ->join('penyakit', 'penyakit.kd_penyakit=diagnosa_pasien.kd_penyakit')
            ->where('diagnosa_pasien.no_rawat', $clean)
            ->where('diagnosa_pasien.status', 'Ralan')
            ->asc('prioritas')
            ->toArray();
        $prosedur_list = $this->db('prosedur_pasien')
            ->join('icd9', 'icd9.kode=prosedur_pasien.kode')
            ->where('prosedur_pasien.no_rawat', $clean)
            ->where('prosedur_pasien.status', 'Ralan')
            ->toArray();
        $resep_list = $this->db('resep_dokter')
            ->join('resep_obat',  'resep_obat.no_resep=resep_dokter.no_resep')
            ->join('databarang',  'databarang.kode_brng=resep_dokter.kode_brng')
            ->where('resep_obat.no_rawat', $clean)
            ->where('resep_obat.status', 'ralan')
            ->toArray();

        $diagnosa_html = '';
        foreach ($diagnosa_list as $i => $dx) {
            if ($diagnosa_html) $diagnosa_html .= '<br>';
            $text = htmlspecialchars(($dx['kd_penyakit'] ?? '') . ' — ' . ($dx['nm_penyakit'] ?? ''));
            $diagnosa_html .= $i === 0 ? '<strong>' . $text . '</strong>' : $text;
        }
        if (!$diagnosa_html) $diagnosa_html = htmlspecialchars($resume['diagnosa_utama'] ?? '-');

        $prosedur_html = '';
        foreach ($prosedur_list as $pr) {
            if ($prosedur_html) $prosedur_html .= '<br>';
            $prosedur_html .= htmlspecialchars(($pr['kode'] ?? '') . ' — ' . ($pr['deskripsi_panjang'] ?? ''));
        }
        if (!$prosedur_html) $prosedur_html = htmlspecialchars($resume['prosedur_utama'] ?? '-');

        $obat_html = '';
        foreach ($resep_list as $ob) {
            if ($obat_html) $obat_html .= '<br>';
            $obat_html .= htmlspecialchars(($ob['nama_brng'] ?? '') . ' — ' . ($ob['jml'] ?? '') . ' — ' . ($ob['aturan_pakai'] ?? ''));
        }
        if (!$obat_html) $obat_html = '-';

        $this->tpl->set('settings',       $this->tpl->noParse_array(htmlspecialchars_array($this->settings('settings'))));
        $this->tpl->set('reg',            $this->tpl->noParse_array(htmlspecialchars_array($reg ?? [])));
        $this->tpl->set('pasien',         $this->tpl->noParse_array(htmlspecialchars_array($pasien ?? [])));
        $this->tpl->set('resume',         $this->tpl->noParse_array(htmlspecialchars_array($resume ?? [])));
        $this->tpl->set('pemfis',         $this->tpl->noParse_array(htmlspecialchars_array($pemfis ?? [])));
        $this->tpl->set('diagnosa_html',  $this->tpl->noParse($diagnosa_html));
        $this->tpl->set('prosedur_html',  $this->tpl->noParse($prosedur_html));
        $this->tpl->set('obat_html',      $this->tpl->noParse($obat_html));
        $this->tpl->set('lab_html',       '');
        $this->tpl->set('no_rawat',       $clean);
        echo $this->tpl->draw(MODULES.'/surat/view/admin/resume.medis.cetak.html', true);
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

    private function _ensureSuratSchema()
    {
        \Systems\Lib\SuratVerification::ensureSchema($this->core);
        \Systems\Lib\SuratVerification::cleanupDrafts($this->core);
    }

    private function _buildRujukanUtamaQuery($phrase = '')
    {
        $query = $this->_applyRujukanUtamaScope($this->db('mlite_surat_rujukan'));

        if ($phrase !== '') {
            $query->where(function ($scope) use ($phrase) {
                $scope->like('nomor_surat', '%' . $phrase . '%')
                    ->orLike('nm_pasien', '%' . $phrase . '%')
                    ->orLike('no_rkm_medis', '%' . $phrase . '%');
            });
        }

        return $query;
    }

    private function _applyRujukanUtamaScope($query)
    {
        $query->where(function ($scope) {
            $scope->notLike('di', '__SURAT_PERMINTAAN_%')->orIsNull('di');
        });

        return $query;
    }

    private function _isRujukanPermintaanRow(array $row): bool
    {
        return strpos((string) ($row['di'] ?? ''), '__SURAT_PERMINTAAN_') === 0;
    }

    private function _getRujukanPermintaanConfig($route)
    {
        if (!isset(self::RUJUKAN_PERMINTAAN[$route])) {
            throw new \InvalidArgumentException('Route surat permintaan tidak dikenali: ' . $route);
        }

        return self::RUJUKAN_PERMINTAAN[$route];
    }

    private function _getRujukanPermintaanRecord($no_rawat, $route)
    {
        $config = $this->_getRujukanPermintaanConfig($route);

        $row = $this->db('mlite_surat_rujukan')
            ->where('no_rawat', $no_rawat)
            ->where('di', $config['marker'])
            ->desc('id')
            ->oneArray();

        return $row ?: [];
    }

    private function _getRujukanPermintaanDefaults()
    {
        return [
            'nomor_surat' => '',
            'anamnesa' => '',
            'pemeriksaan_fisik' => '',
            'pemeriksaan_penunjang' => '',
            'dokter' => '',
            'petugas' => '',
        ];
    }

    private function _getNomorSuratFormat($type = 'umum')
    {
        return \Systems\Lib\SuratVerification::getNomorSuratPreview($this->core, $type);
    }

    private function _incrementNomorSuratCounter($type = 'umum')
    {
        \Systems\Lib\SuratVerification::incrementNomorSuratCounter($this->core, $type);
    }

    private function _saveRujukanPermintaan($route)
    {
        header('Content-Type: application/json');

        $config = $this->_getRujukanPermintaanConfig($route);
        $data_save = [
            'nomor_surat' => isset($_POST['nomor_surat']) ? trim($_POST['nomor_surat']) : '',
            'no_rawat' => isset($_POST['no_rawat']) ? trim($_POST['no_rawat']) : '',
            'no_rkm_medis' => isset($_POST['no_rkm_medis']) ? trim($_POST['no_rkm_medis']) : '',
            'nm_pasien' => isset($_POST['nm_pasien']) ? trim($_POST['nm_pasien']) : '',
            'tgl_lahir' => isset($_POST['tgl_lahir']) ? trim($_POST['tgl_lahir']) : '',
            'umur' => isset($_POST['umur']) ? trim($_POST['umur']) : '',
            'jk' => isset($_POST['jk']) ? trim($_POST['jk']) : '',
            'alamat' => isset($_POST['alamat']) ? trim($_POST['alamat']) : '',
            'indikasi' => isset($_POST['indikasi']) ? trim($_POST['indikasi']) : '',
            'permintaan' => isset($_POST['permintaan']) ? trim($_POST['permintaan']) : '',
            'catatan' => isset($_POST['catatan']) ? trim($_POST['catatan']) : '',
            'dokter' => isset($_POST['dokter']) ? trim($_POST['dokter']) : '',
            'petugas' => isset($_POST['petugas']) ? trim($_POST['petugas']) : '',
        ];

        if (checkEmptyFields(['nomor_surat', 'no_rawat', 'no_rkm_medis', 'nm_pasien'], $data_save)) {
            echo json_encode(['status' => 'error', 'msg' => 'Data surat belum lengkap']);
            exit();
        }

        $existing = $this->_getRujukanPermintaanRecord($data_save['no_rawat'], $route);
        $hadNomorBefore = !empty(trim((string) ($existing['nomor_surat'] ?? '')));
        $payload = [
            'nomor_surat' => $data_save['nomor_surat'],
            'no_rawat' => $data_save['no_rawat'],
            'no_rkm_medis' => $data_save['no_rkm_medis'],
            'nm_pasien' => $data_save['nm_pasien'],
            'tgl_lahir' => $data_save['tgl_lahir'],
            'umur' => $data_save['umur'],
            'jk' => $data_save['jk'],
            'alamat' => $data_save['alamat'],
            'kepada' => 'Permintaan ' . $config['jenis'],
            'di' => $config['marker'],
            'anamnesa' => $data_save['indikasi'],
            'pemeriksaan_fisik' => $data_save['permintaan'],
            'pemeriksaan_penunjang' => $data_save['catatan'],
            'diagnosa' => '',
            'terapi' => '',
            'alasan_dirujuk' => '',
            'dokter' => $data_save['dokter'],
            'petugas' => $data_save['petugas'],
        ];

        if (empty($existing)) {
            $result = $this->db('mlite_surat_rujukan')->save($payload);
        } else {
            $result = $this->db('mlite_surat_rujukan')->where('id', $existing['id'])->save($payload);
        }

        if ($result && !$hadNomorBefore && $data_save['nomor_surat'] !== '') {
            $this->_incrementNomorSuratCounter($route);
        }

        if ($result) {
            echo json_encode(['status' => 'success', 'msg' => 'Data berhasil disimpan']);
            exit();
        }

        echo json_encode(['status' => 'error', 'msg' => 'Gagal menyimpan ke database']);
        exit();
    }

    private function _handleSuratCetak($no_rawat, $route, $printRoute, $title, $jenis)
    {
        $this->_addHeaderFiles();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['no_rawat'])) {
            $no_rawat_key = $this->_normalizeNoRawatKey($_POST['no_rawat']);
            if (empty($no_rawat_key)) {
                $this->notify('failure', 'No. Rawat wajib diisi');
                redirect(url([ADMIN, 'surat', $route]), $_POST);
            }
            redirect(url([ADMIN, 'surat', $printRoute, $no_rawat_key]));
        }

        if (!empty($no_rawat)) {
            redirect(url([ADMIN, 'surat', $printRoute, $no_rawat]));
        }

        $phrase  = isset($_GET['s'])    ? trim((string) $_GET['s'])    : '';
        $page    = max(1, (int) (isset($_GET['page']) ? $_GET['page'] : 1));
        $perpage = 10;
        $config  = $this->_getSuratCetakConfig($route);
        $historyList = [];
        $pagination  = '';
        $total = 0;

        if ($config) {
            $countQ = $this->db($config['table']);
            if ($config['is_rujukan_utama']) {
                $this->_applyRujukanUtamaScope($countQ);
            }
            if ($phrase !== '') {
                $countQ->where(function ($s) use ($phrase) {
                    $s->like('nomor_surat', '%' . $phrase . '%')
                      ->orLike('nm_pasien', '%' . $phrase . '%')
                      ->orLike('no_rkm_medis', '%' . $phrase . '%');
                });
            }
            $total = count($countQ->toArray());

            $dataQ = $this->db($config['table']);
            if ($config['is_rujukan_utama']) {
                $this->_applyRujukanUtamaScope($dataQ);
            }
            if ($phrase !== '') {
                $dataQ->where(function ($s) use ($phrase) {
                    $s->like('nomor_surat', '%' . $phrase . '%')
                      ->orLike('nm_pasien', '%' . $phrase . '%')
                      ->orLike('no_rkm_medis', '%' . $phrase . '%');
                });
            }
            $rows = $dataQ->desc('id')->offset(($page - 1) * $perpage)->limit($perpage)->toArray();

            $paginationUrl = url([ADMIN, 'surat', $route]) . '&s=' . urlencode($phrase) . '&page=%d';
            $paginationObj = new \Systems\Lib\Pagination($page, $total, $perpage, $paginationUrl);
            $pagination = $paginationObj->nav('pagination', '5');

            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                $row['editURL']   = url([ADMIN, 'surat', $config['editRoute'],   $row['id']]);
                $row['deleteURL'] = url([ADMIN, 'surat', $config['deleteRoute'], $row['id']]);
                $row['printURL']  = !empty($row['no_rawat'])
                    ? url([ADMIN, 'surat', $printRoute, convertNorawat($row['no_rawat'])])
                    : '';
                $historyList[] = $row;
            }
        }

        $this->assign['title']       = $title;
        $this->assign['description'] = 'Masukkan No. Rawat untuk mencetak ' . $jenis . '.';
        $this->assign['formURL']     = url([ADMIN, 'surat', $route]);
        $this->assign['buttonText']  = 'Cetak ' . $title;
        $this->assign['example']     = 'Contoh: 2026/05/21/000001';
        $this->assign['form']        = !empty($redirectData = getRedirectData())
            ? filter_var_array($redirectData, FILTER_SANITIZE_FULL_SPECIAL_CHARS)
            : ['no_rawat' => ''];
        $this->assign['list']        = $historyList;
        $this->assign['pagination']  = $pagination;
        $this->assign['searchURL']   = url([ADMIN, 'surat', $route]);
        $this->assign['phrase']      = $phrase;
        $this->assign['total']       = $total;
        $this->assign['showHistory'] = true;

        return $this->draw('rujukan.permintaan.form.html', ['permintaan' => $this->assign]);
    }

    private function _getSuratCetakConfig($route)
    {
        $configs = [
            'rujukan' => [
                'table'           => 'mlite_surat_rujukan',
                'editRoute'       => 'rujukanedit',
                'deleteRoute'     => 'rujukanhapus',
                'is_rujukan_utama' => true,
            ],
            'sakit' => [
                'table'           => 'mlite_surat_sakit',
                'editRoute'       => 'sakitedit',
                'deleteRoute'     => 'sakithapus',
                'is_rujukan_utama' => false,
            ],
            'sehat' => [
                'table'           => 'mlite_surat_sehat',
                'editRoute'       => 'sehatedit',
                'deleteRoute'     => 'sehathapus',
                'is_rujukan_utama' => false,
            ],
            'bebasnarkoba' => [
                'table'           => 'mlite_surat_bebas_narkoba',
                'editRoute'       => 'bebasnarkobaedit',
                'deleteRoute'     => 'bebasnarkobahapus',
                'is_rujukan_utama' => false,
            ],
            'kematian' => [
                'table'           => 'mlite_surat_kematian',
                'editRoute'       => 'kematianedit',
                'deleteRoute'     => 'kematianhapus',
                'is_rujukan_utama' => false,
            ],
        ];
        return isset($configs[$route]) ? $configs[$route] : null;
    }

    private function _handleRujukanPermintaan($no_rawat, $jenis, $route, $title)
    {
        $this->_addHeaderFiles();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['no_rawat'])) {
            $no_rawat_key = $this->_normalizeNoRawatKey($_POST['no_rawat']);

            if (empty($no_rawat_key)) {
                $this->notify('failure', 'No. Rawat wajib diisi');
                redirect(url([ADMIN, 'surat', $route]), $_POST);
            }

            redirect(url([ADMIN, 'surat', $route, $no_rawat_key]));
        }

        if (!empty($no_rawat)) {
            $this->_printRujukanPermintaan($no_rawat, $jenis, $route);
        }

        $config  = $this->_getRujukanPermintaanConfig($route);
        $phrase  = isset($_GET['s'])    ? trim((string) $_GET['s'])    : '';
        $page    = max(1, (int) (isset($_GET['page']) ? $_GET['page'] : 1));
        $perpage = 10;
        $historyList = [];
        $pagination  = '';
        $total = 0;

        $countQ = $this->db('mlite_surat_rujukan')->where('di', $config['marker']);
        if ($phrase !== '') {
            $countQ->where(function ($s) use ($phrase) {
                $s->like('nomor_surat', '%' . $phrase . '%')
                  ->orLike('nm_pasien', '%' . $phrase . '%')
                  ->orLike('no_rkm_medis', '%' . $phrase . '%');
            });
        }
        $total = count($countQ->toArray());

        $dataQ = $this->db('mlite_surat_rujukan')->where('di', $config['marker']);
        if ($phrase !== '') {
            $dataQ->where(function ($s) use ($phrase) {
                $s->like('nomor_surat', '%' . $phrase . '%')
                  ->orLike('nm_pasien', '%' . $phrase . '%')
                  ->orLike('no_rkm_medis', '%' . $phrase . '%');
            });
        }
        $rows = $dataQ->desc('id')->offset(($page - 1) * $perpage)->limit($perpage)->toArray();

        $paginationUrl = url([ADMIN, 'surat', $route]) . '?s=' . urlencode($phrase) . '&page=%d';
        $paginationObj = new \Systems\Lib\Pagination($page, $total, $perpage, $paginationUrl);
        $pagination = $paginationObj->nav('pagination', '5');

        foreach ($rows as $row) {
            $row = htmlspecialchars_array($row);
            $row['printURL'] = !empty($row['no_rawat'])
                ? url([ADMIN, 'surat', $route, convertNorawat($row['no_rawat'])])
                : '';
            $row['editURL']   = $row['printURL'];
            $row['deleteURL'] = url([ADMIN, 'surat', $route . 'hapus', $row['id']]);
            $historyList[] = $row;
        }

        $this->assign['title']       = $title;
        $this->assign['description'] = 'Masukkan No. Rawat untuk mencetak surat permintaan ' . strtolower($jenis) . '.';
        $this->assign['formURL']     = url([ADMIN, 'surat', $route]);
        $this->assign['buttonText']  = 'Cetak ' . $title;
        $this->assign['example']     = 'Contoh: 2026/05/21/000001';
        $this->assign['form']        = !empty($redirectData = getRedirectData())
            ? filter_var_array($redirectData, FILTER_SANITIZE_FULL_SPECIAL_CHARS)
            : ['no_rawat' => ''];
        $this->assign['list']        = $historyList;
        $this->assign['pagination']  = $pagination;
        $this->assign['searchURL']   = url([ADMIN, 'surat', $route]);
        $this->assign['phrase']      = $phrase;
        $this->assign['total']       = $total;
        $this->assign['showHistory'] = true;

        return $this->draw('rujukan.permintaan.form.html', ['permintaan' => $this->assign]);
    }

    private function _printRujukanPermintaan($no_rawat, $jenis, $route)
    {
        $clean_no_rawat = revertNoRawat($no_rawat);
        $kd_dokter = $this->core->getRegPeriksaInfo('kd_dokter', $clean_no_rawat);
        $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis', $clean_no_rawat);

        if (empty($kd_dokter) || empty($no_rkm_medis)) {
            $this->notify('failure', 'No. Rawat tidak ditemukan');
            redirect(url([ADMIN, 'surat', $route]));
        }

        $pasien = $this->db('pasien')
          ->leftJoin('kelurahan', 'kelurahan.kd_kel=pasien.kd_kel')
          ->leftJoin('kecamatan', 'kecamatan.kd_kec=pasien.kd_kec')
          ->leftJoin('kabupaten', 'kabupaten.kd_kab=pasien.kd_kab')
          ->leftJoin('propinsi', 'propinsi.kd_prop=pasien.kd_prop')
          ->where('no_rkm_medis', $no_rkm_medis)
          ->oneArray();

        if (empty($pasien)) {
            $this->notify('failure', 'Data pasien tidak ditemukan');
            redirect(url([ADMIN, 'surat', $route]));
        }

        $requestConfig = $this->_getRujukanPermintaanConfig($route);
        $surat = array_merge($this->_getRujukanPermintaanDefaults(), $this->_getRujukanPermintaanRecord($clean_no_rawat, $route));
        $dokter_ttd = $this->resolveRujukanDokterPenandatangan($jenis, $kd_dokter);

        $this->tpl->set('pasien', $this->tpl->noParse_array(htmlspecialchars_array($pasien)));
        $this->tpl->set('nm_dokter', $dokter_ttd['nm_dokter']);
        $this->tpl->set('sip_dokter', $dokter_ttd['sip_dokter']);
        $this->tpl->set('no_rawat', $clean_no_rawat);
        $this->tpl->set('jenis_label', $jenis);
        $this->tpl->set('judul', 'SURAT PERMINTAAN ' . strtoupper($jenis));
        $nomor_surat = !empty(trim((string) ($surat['nomor_surat'] ?? '')))
            ? $surat['nomor_surat']
            : $this->_getNomorSuratFormat($route);
        $this->tpl->set('surat', $this->tpl->noParse_array(htmlspecialchars_array($surat)));
        $this->tpl->set('nomor_surat', $nomor_surat);
        $this->tpl->set('save_url', url([ADMIN, 'surat', $requestConfig['save_route']]));
        $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($this->settings('settings'))));
        $verificationType = $requestConfig['verification_type'];
        $this->tpl->set('surat_verifikasi_url', \Systems\Lib\SuratVerification::getRequestVerificationUrl($verificationType, $clean_no_rawat));

        echo $this->tpl->draw(MODULES.'/surat/view/admin/rujukan.permintaan.html', true);
        exit();
    }

    private function resolveRujukanDokterPenandatangan($jenis, $fallbackKdDokter)
    {
        $jenis = strtolower(trim((string) $jenis));
        $settingKey = $jenis === 'radiologi' ? 'settings.pj_radiologi' : 'settings.pj_laboratorium';

        $kdDokter = trim((string) $this->settings->get($settingKey));
        if ($kdDokter === '' && $jenis === 'radiologi') {
            $kdDokter = trim((string) $this->settings->get('settings.pj_laboratorium'));
        }
        if ($kdDokter === '') {
            $kdDokter = trim((string) $fallbackKdDokter);
        }

        $dokter = !empty($kdDokter) ? $this->db('dokter')->where('kd_dokter', $kdDokter)->oneArray() : [];
        if (empty($dokter) && !empty($fallbackKdDokter)) {
            $dokter = $this->db('dokter')->where('kd_dokter', $fallbackKdDokter)->oneArray();
            $kdDokter = !empty($dokter['kd_dokter']) ? $dokter['kd_dokter'] : $fallbackKdDokter;
        }

        $nmDokter = trim((string) ($dokter['nm_dokter'] ?? ''));
        if ($nmDokter === '' && $kdDokter !== '') {
            $nmDokter = trim((string) $this->core->getPegawaiInfo('nama', $kdDokter));
        }

        $sipDokter = trim((string) ($dokter['no_ijn_praktek'] ?? ''));
        if ($sipDokter === '' && $kdDokter !== '') {
            $sipDokter = trim((string) $this->core->getDokterInfo('no_ijn_praktek', $kdDokter));
        }

        return [
            'kd_dokter' => $kdDokter,
            'nm_dokter' => $nmDokter,
            'sip_dokter' => $sipDokter,
        ];
    }

    private function _normalizeNoRawatKey($no_rawat)
    {
        $no_rawat = trim((string) $no_rawat);

        if ($no_rawat === '') {
            return '';
        }

        if (strpos($no_rawat, '/') !== false) {
            $no_rawat = convertNorawat($no_rawat);
        } else {
            $no_rawat = preg_replace('/[^0-9]/', '', $no_rawat);
        }

        return strlen($no_rawat) === 14 ? $no_rawat : '';
    }
}
