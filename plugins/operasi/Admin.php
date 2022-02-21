<?php
    namespace Plugins\Operasi;

    use Systems\AdminModule;

    class Admin extends AdminModule
    {

        public function navigation()
        {
            return [
                'Manage' => 'manage',
                'Data Pasien Operasi' => 'pasienoperasi',
                'Paket Operasi' => 'paketoperasi',
                'Obat Operasi' => 'obatoperasi',
                'Laporan Operasi' => 'laporanoperasi',
            ];
        }

        public function getManage()
        {
          $sub_modules = [
            ['name' => 'Data Pasien Operasi', 'url' => url([ADMIN, 'operasi', 'pasienoperasi']), 'icon' => 'cubes', 'desc' => 'Data pasien operasi'],
            ['name' => 'Paket Operasi', 'url' => url([ADMIN, 'operasi', 'paketoperasi']), 'icon' => 'cubes', 'desc' => 'Data paket operasi'],
            ['name' => 'Obat Operasi', 'url' => url([ADMIN, 'operasi', 'obatoperasi']), 'icon' => 'cubes', 'desc' => 'Data obat operasi'],
            ['name' => 'Laporan Operasi', 'url' => url([ADMIN, 'operasi', 'laporanoperasi']), 'icon' => 'cubes', 'desc' => 'Data laporan operasi'],
          ];
          return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
        }

        public function getPasienOperasi()
        {
          return $this->draw('pasienoperasi.html');
        }

        public function getPaketOperasi()
        {
          return $this->draw('paketoperasi.html');
        }

        public function getObatOperasi()
        {
          return $this->draw('obatoperasi.html');
        }

        public function getLaporanOperasi()
        {
          return $this->draw('laporanoperasi.html');
        }
    }

?>
