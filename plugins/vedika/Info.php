<?php
return [
    'name'          =>  'Vedika',
    'description'   =>  'Modul klaim online Vedika BPJS',
    'author'        =>  'Basoro',
    'category'      =>  'bridging', 
    'version'       =>  '1.0',
    'compatibility' =>  '6.*.*',
    'icon'          =>  'code',
    'pages'         =>  ['e-Vedika Dashboard' => 'vedika'],
    'install'       =>  function () use ($core) {

        // Idempoten: setting yang sudah ada dibiarkan (tidak menimpa konfigurasi);
        // yang belum ada disisipkan. Aman untuk MySQL dan SQLite.
        $settings = [
            'carabayar' => '',
            'sep' => '',
            'skdp' => '',
            'operasi' => '',
            'individual' => '',
            'billing' => 'mlite',
            'periode' => '2023-01',
            'verifikasi' => '2023-01',
            'inacbgs_prosedur_bedah' => '',
            'inacbgs_prosedur_non_bedah' => '',
            'inacbgs_konsultasi' => '',
            'inacbgs_tenaga_ahli' => '',
            'inacbgs_keperawatan' => '',
            'inacbgs_penunjang' => '',
            'inacbgs_pelayanan_darah' => '',
            'inacbgs_rehabilitasi' => '',
            'inacbgs_rawat_intensif' => '',
            'eklaim_url' => '',
            'eklaim_key' => '',
            'eklaim_kelasrs' => 'CP',
            'eklaim_payor_id' => '3',
            'eklaim_payor_cd' => 'JKN',
            'eklaim_cob_cd' => '#',
        ];
        foreach ($settings as $field => $value) {
            $cek = $core->db('mlite_settings')->where('module', 'vedika')->where('field', $field)->oneArray();
            if (!$cek) {
                $core->db('mlite_settings')->save(['module' => 'vedika', 'field' => $field, 'value' => $value]);
            }
        }



    },
    'uninstall'     =>  function () use ($core) {
        $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'vedika'");
    }
];
