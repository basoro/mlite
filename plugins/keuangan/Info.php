<?php

return [
    'name'          =>  'Keuangan',
    'description'   =>  'Modul Keuangan untuk mLITE',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '4.0.*',
    'icon'          =>  'money',
    'install'       =>  function () use ($core) {
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('keuangan', 'jurnal_kasir', '0')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('keuangan', 'akun_kredit_pendaftaran', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('keuangan', 'akun_kredit_tindakan', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('keuangan', 'akun_kredit_obat_bhp', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('keuangan', 'akun_kredit_laboratorium', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('keuangan', 'akun_kredit_radiologi', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('keuangan', 'akun_kredit_tambahan_biaya', '')");
    },
    'uninstall'     =>  function() use($core)
    {
        $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'keuangan'");
    }
];
