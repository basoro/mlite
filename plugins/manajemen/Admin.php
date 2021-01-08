<?php
namespace Plugins\Manajemen;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Kelola'   => 'manage',
        ];
    }

    public function anyManage()
    {
      $sub_modules = [
        ['name' => 'Pendaftaran', 'url' => url([ADMIN, 'manajemen', 'pendaftaran']), 'icon' => 'wrench', 'desc' => 'Data jumlah pasien, pasien baru, pasien dilayani.'],
        ['name' => 'Poliklinik', 'url' => url([ADMIN, 'manajemen', 'poliklinik']), 'icon' => 'cubes', 'desc' => 'Jenis pemeriksaan, jenis tindakan'],
        ['name' => 'Dokter', 'url' => url([ADMIN, 'manajemen', 'dokter']), 'icon' => 'cubes', 'desc' => 'Pasien per dokter, tindakan per dokter.'],
        ['name' => 'Apotek', 'url' => url([ADMIN, 'manajemen', 'apotek']), 'icon' => 'cubes', 'desc' => 'Layanan obat dan resep.'],
        ['name' => 'Laboratorium', 'url' => url([ADMIN, 'manajemen', 'laboratorium']), 'icon' => 'cubes', 'desc' => 'Data layanan, status, stok dan ED reagen.'],
        ['name' => 'Radiologi', 'url' => url([ADMIN, 'manajemen', 'radiologi']), 'icon' => 'cubes', 'desc' => 'Data dan status layanan radiologi.'],
        ['name' => 'Rawat Inap', 'url' => url([ADMIN, 'manajemen', 'rawatinap']), 'icon' => 'cubes', 'desc' => 'Data layanan dan status rawat inap.'],
        ['name' => 'Kasir', 'url' => url([ADMIN, 'manajemen', 'kasir']), 'icon' => 'cubes', 'desc' => 'Pemasukan, tagihan dan rugi laba.'],
      ];
      return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
    }
}
