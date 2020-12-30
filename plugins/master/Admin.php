<?php
    namespace Plugins\Master;

    use Systems\AdminModule;
    use Plugins\Master\Src\Dokter;
    use Plugins\Master\Src\Bahasa;
    use Plugins\Master\Src\Cacat;
    use Plugins\Master\Src\Suku;
    use Plugins\Master\Src\Perusahaan;
    use Plugins\Master\Src\Penjab;
    use Systems\Lib\Fpdf\PDF_MC_Table;

    class Admin extends AdminModule
    {

        public function init()
        {
            $this->dokter = new Dokter();
            $this->bahasa = new Bahasa();
            $this->cacat = new Cacat();
            $this->suku = new Suku();
            $this->perusahaan = new Perusahaan();
            $this->penjab = new Penjab();
        }

        public function navigation()
        {
            return [
                'Manage' => 'manage',
                'Dokter' => 'dokter',
                'Petugas' => 'petugas',
                'Poliklinik' => 'poliklinik',
                'Bangsal' => 'bangsal',
                'Kamar' => 'kamar',
                'Data Barang' => 'databarang',
                'Perawatan Ralan' => 'jnsperawatan',
                'Perawatan Ranap' => 'jnsperawatanranap',
                'Perawatan Laboratorium' => 'jnsperawatanlab',
                'Perawatan Radiologi' => 'jnsperawatanrad',
                'Bahasa' => 'bahasa',
                'Cacat Fisik' => 'cacat',
                'Suku Bangsa' => 'suku',
                'Perusahaan Pasien' => 'perusahaan',
                'Penanggung Jawab' => 'penjab',
                'Golongan Barang' => 'golonganbarang',
                'Industri Farmasi' => 'industrifarmasi',
                'Jenis Barang' => 'jenisbarang',
                'Kategori Barang' => 'kategoribarang',
                'Kategori Penyakit' => 'kategoripenyakit',
                'Kategori Perawatan' => 'kategoriperawatan',
                'Kode Satuan' => 'kodesatuan',
                'Master Aturan Pakai' => 'masteraturanpakai',
                'Master Berkas Digital' => 'masterberkasdigital',
                'Spesialis' => 'spesialis',
                'Bank' => 'bank',
                'Bidang' => 'bidang',
                'Departemen' => 'departemen',
                'Emergency Index' => 'emergencyindex',
                'Jabatan' => 'jabatan',
                'Jenjang Jabatan' => 'jnjjabatan',
                'Kelompok Jabatan' => 'kelompokjabatan',
                'Pendidikan' => 'pendidikan',
                'Resiko Kerja' => 'resikokerja',
                'Status Kerja' => 'sttskerja',
                'Status WP' => 'sttswp',
            ];
        }

        public function getManage()
        {
          $sub_modules = [
            ['name' => 'Dokter', 'url' => url([ADMIN, 'master', 'dokter']), 'icon' => 'cubes', 'desc' => 'Master dokter'],
            ['name' => 'Petugas', 'url' => url([ADMIN, 'master', 'petugas']), 'icon' => 'cubes', 'desc' => 'Master petugas'],
            ['name' => 'Poliklinik', 'url' => url([ADMIN, 'master', 'poliklinik']), 'icon' => 'cubes', 'desc' => 'Master poliklinik'],
            ['name' => 'Bangsal', 'url' => url([ADMIN, 'master', 'bangsal']), 'icon' => 'cubes', 'desc' => 'Master bangsal'],
            ['name' => 'Kamar', 'url' => url([ADMIN, 'master', 'kamar']), 'icon' => 'cubes', 'desc' => 'Master kamar'],
            ['name' => 'Data Barang', 'url' => url([ADMIN, 'master', 'databarang']), 'icon' => 'cubes', 'desc' => 'Master data barang'],
            ['name' => 'Perawatan Rawat Jalan', 'url' => url([ADMIN, 'master', 'jnsperawatan']), 'icon' => 'cubes', 'desc' => 'Master jenis perawatan rawat jalan'],
            ['name' => 'Perawatan Rawat Inap', 'url' => url([ADMIN, 'master', 'jnsperawatanranap']), 'icon' => 'cubes', 'desc' => 'Master jenis perawatan rawat inap'],
            ['name' => 'Perawatan Laboratorium', 'url' => url([ADMIN, 'master', 'jnsperawatanlab']), 'icon' => 'cubes', 'desc' => 'Master jenis perawatan laboratorium'],
            ['name' => 'Perawatan Radiologi', 'url' => url([ADMIN, 'master', 'jnsperawatanrad']), 'icon' => 'cubes', 'desc' => 'Master jenis perawatan radiologi'],
            ['name' => 'Bahasa', 'url' => url([ADMIN, 'master', 'bahasa']), 'icon' => 'cubes', 'desc' => 'Master bahasa'],
            ['name' => 'Cacat Fisik', 'url' => url([ADMIN, 'master', 'cacat']), 'icon' => 'cubes', 'desc' => 'Master cacat fisik'],
            ['name' => 'Suku Bangsa', 'url' => url([ADMIN, 'master', 'suku']), 'icon' => 'cubes', 'desc' => 'Master suku bangsa'],
            ['name' => 'Perusahaan Pasien', 'url' => url([ADMIN, 'master', 'perusahaan']), 'icon' => 'cubes', 'desc' => 'Master perusahaan pasien'],
            ['name' => 'Penanggung Jawab', 'url' => url([ADMIN, 'master', 'penjab']), 'icon' => 'cubes', 'desc' => 'Master penanggung jawab'],
          ];
          return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
        }

        /* Start Dokter Section */
        public function getDokter()
        {
          $this->_addHeaderFiles();
          $this->core->addJS(url([ADMIN, 'master', 'dokterjs']), 'footer');
          $return = $this->dokter->getIndex();
          return $this->draw('dokter.html', [
            'dokter' => $return
          ]);

        }

        public function anyDokterForm()
        {
            $return = $this->dokter->anyForm();
            echo $this->draw('dokter.form.html', ['dokter' => $return]);
            exit();
        }

        public function anyDokterDisplay()
        {
            $return = $this->dokter->anyDisplay();
            echo $this->draw('dokter.display.html', ['dokter' => $return]);
            exit();
        }

        public function postDokterSave()
        {
          $this->dokter->postSave();
          exit();
        }

        public function postDokterHapus()
        {
          $this->dokter->postHapus();
          exit();
        }

        public function getDokterJS()
        {
            header('Content-type: text/javascript');
            echo $this->draw(MODULES.'/master/js/admin/dokter.js');
            exit();
        }
        /* End Dokter Section */

        /* Start Bahasa Section */
        public function getBahasa()
        {
          $this->core->addJS(url([ADMIN, 'master', 'bahasajs']), 'footer');
          $return = $this->bahasa->getIndex();
          return $this->draw('bahasa.html', [
            'bahasa' => $return
          ]);

        }

        public function anyBahasaForm()
        {
            $return = $this->bahasa->anyForm();
            echo $this->draw('bahasa.form.html', ['bahasa' => $return]);
            exit();
        }

        public function anyBahasaDisplay()
        {
            $return = $this->bahasa->anyDisplay();
            echo $this->draw('bahasa.display.html', ['bahasa' => $return]);
            exit();
        }

        public function postBahasaSave()
        {
          $this->bahasa->postSave();
          exit();
        }

        public function postBahasaHapus()
        {
          $this->bahasa->postHapus();
          exit();
        }

        public function getBahasaJS()
        {
            header('Content-type: text/javascript');
            echo $this->draw(MODULES.'/master/js/admin/bahasa.js');
            exit();
        }
        /* End Bahasa Section */

        /* Start Cacat Fisik Section */
        public function getCacat()
        {
          $this->core->addJS(url([ADMIN, 'master', 'cacatjs']), 'footer');
          $return = $this->cacat->getIndex();
          return $this->draw('cacat.html', [
            'cacat' => $return
          ]);

        }

        public function anyCacatForm()
        {
            $return = $this->cacat->anyForm();
            echo $this->draw('cacat.form.html', ['cacat' => $return]);
            exit();
        }

        public function anyCacatDisplay()
        {
            $return = $this->cacat->anyDisplay();
            echo $this->draw('cacat.display.html', ['cacat' => $return]);
            exit();
        }

        public function postCacatSave()
        {
          $this->cacat->postSave();
          exit();
        }

        public function postCacatHapus()
        {
          $this->cacat->postHapus();
          exit();
        }

        public function getCacatJS()
        {
            header('Content-type: text/javascript');
            echo $this->draw(MODULES.'/master/js/admin/cacat.js');
            exit();
        }
        /* End Cacat Section */

        /* Start Suku Section */
        public function getSuku()
        {
          $this->core->addJS(url([ADMIN, 'master', 'sukujs']), 'footer');
          $return = $this->suku->getIndex();
          return $this->draw('suku.html', [
            'suku' => $return
          ]);

        }

        public function anySukuForm()
        {
            $return = $this->suku->anyForm();
            echo $this->draw('suku.form.html', ['suku' => $return]);
            exit();
        }

        public function anySukuDisplay()
        {
            $return = $this->suku->anyDisplay();
            echo $this->draw('suku.display.html', ['suku' => $return]);
            exit();
        }

        public function postSukuSave()
        {
          $this->suku->postSave();
          exit();
        }

        public function postSukuHapus()
        {
          $this->suku->postHapus();
          exit();
        }

        public function getSukuJS()
        {
            header('Content-type: text/javascript');
            echo $this->draw(MODULES.'/master/js/admin/suku.js');
            exit();
        }
        /* End Suku Section */

        /* Start Perusahaan Section */
        public function getPerusahaan()
        {
          $this->core->addJS(url([ADMIN, 'master', 'perusahaanjs']), 'footer');
          $return = $this->perusahaan->getIndex();
          return $this->draw('perusahaan.html', [
            'perusahaan' => $return
          ]);

        }

        public function anyPerusahaanForm()
        {
            $return = $this->perusahaan->anyForm();
            echo $this->draw('perusahaan.form.html', ['perusahaan' => $return]);
            exit();
        }

        public function anyPerusahaanDisplay()
        {
            $return = $this->perusahaan->anyDisplay();
            echo $this->draw('perusahaan.display.html', ['perusahaan' => $return]);
            exit();
        }

        public function postPerusahaanSave()
        {
          $this->perusahaan->postSave();
          exit();
        }

        public function postPerusahaanHapus()
        {
          $this->perusahaan->postHapus();
          exit();
        }

        public function getPerusahaanJS()
        {
            header('Content-type: text/javascript');
            echo $this->draw(MODULES.'/master/js/admin/perusahaan.js');
            exit();
        }
        /* End Perusahaan Section */

        /* Start Penjab Section */
        public function getPenjab()
        {
          $this->core->addJS(url([ADMIN, 'master', 'penjabjs']), 'footer');
          $return = $this->penjab->getIndex();
          return $this->draw('penjab.html', [
            'penjab' => $return
          ]);

        }

        public function anyPenjabForm()
        {
            $return = $this->penjab->anyForm();
            echo $this->draw('penjab.form.html', ['penjab' => $return]);
            exit();
        }

        public function anyPenjabDisplay()
        {
            $return = $this->penjab->anyDisplay();
            echo $this->draw('penjab.display.html', ['penjab' => $return]);
            exit();
        }

        public function postPenjabSave()
        {
          $this->penjab->postSave();
          exit();
        }

        public function postPenjabHapus()
        {
          $this->penjab->postHapus();
          exit();
        }

        public function getPenjabJS()
        {
            header('Content-type: text/javascript');
            echo $this->draw(MODULES.'/master/js/admin/penjab.js');
            exit();
        }
        /* End Penjab Section */

        public function getCSS()
        {
            header('Content-type: text/css');
            echo $this->draw(MODULES.'/master/css/admin/master.css');
            exit();
        }

        public function getJavascript()
        {
            header('Content-type: text/javascript');
            echo $this->draw(MODULES.'/master/js/admin/master.js');
            exit();
        }

        private function _addHeaderFiles()
        {
            // CSS
            $this->core->addCSS(url('assets/css/jquery-ui.css'));

            // JS
            $this->core->addJS(url('assets/jscripts/jquery-ui.js'), 'footer');

            $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
            $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
            $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));

            // MODULE SCRIPTS
            $this->core->addCSS(url([ADMIN, 'master', 'css']));
            $this->core->addJS(url([ADMIN, 'master', 'javascript']), 'footer');
        }

    }
