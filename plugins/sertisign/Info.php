<?php

return [
    'name'          =>  'Sertisign',
    'description'   =>  'Modul integrasi TTE Sertisign',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'category'      =>  'bridging',
    'compatibility' =>  '5.*.*',
    'icon'          =>  'file-signature',
    'install'       =>  function() use($core) {
        if(DBDRIVER == 'sqlite') {
            $core->db()->pdo()->exec("CREATE TABLE mlite_sertisign_webhook (
                id INTEGER PRIMARY KEY,
                transaction_id TEXT NOT NULL,
                status TEXT NOT NULL,
                document_url TEXT NOT NULL,
                payload TEXT NOT NULL,
                received_at TEXT NOT NULL
            );");
        } else {
            $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_sertisign_webhook` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `transaction_id` varchar(100) NOT NULL,
                `status` varchar(50) NOT NULL,
                `document_url` varchar(255) NOT NULL,
                `payload` text NOT NULL,
                `received_at` datetime NOT NULL
                PRIMARY KEY (`id`),
                KEY `transaction_idx` (`transaction_id`),
                KEY `status_idx` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        }
        $core->db('mlite_settings')->save(['module' => 'sertisign', 'field' => 'api_host', 'value' => 'https://api-stag.sertisign.id/']);
        $core->db('mlite_settings')->save(['module' => 'sertisign', 'field' => 'api_key', 'value' => '']);
    },
    'uninstall'     =>  function() use($core) {
        $core->db('mlite_settings')->where('module', 'sertisign')->delete();
    }
];
