<?php

return [
    'name'          =>  'E-Signature',
    'description'   =>  'Modul Tanda Tangan Elektronik (TTE) Tersertifikasi',
    'author'        =>  'Basoro',
    'category'      =>  'manajemen',
    'version'       =>  '1.0',
    'compatibility' =>  '5.*.*',
    'icon'          =>  'pen-nib',
    'install'       =>  function () use ($core) {
        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `esignatures` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `ref_type` varchar(50) NOT NULL,
            `ref_id` varchar(50) NOT NULL,
            `signer_role` varchar(50) NOT NULL,
            `signer_id` varchar(50) NOT NULL,
            `signer_name` varchar(255) NOT NULL,
            `signature_path` varchar(255) NOT NULL,
            `signature_hash` varchar(255) NOT NULL,
            `chain_hash` varchar(255) DEFAULT NULL,
            `signed_at` datetime NOT NULL,
            `ip_address` varchar(45) NOT NULL,
            `user_agent` varchar(255) NOT NULL,
            `legal_basis` text,
            `audit_json` text,
            PRIMARY KEY (`id`),
            KEY `ref_idx` (`ref_type`,`ref_id`),
            KEY `hash_idx` (`signature_hash`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('esignature', 'kode_berkasdigital', '')");

    },
    'uninstall'     =>  function() use($core)
    {
        $core->db()->pdo()->exec("DROP TABLE IF EXISTS `esignatures`");
    }
];
