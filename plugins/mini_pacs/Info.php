<?php

return [
    'name' => 'Mini PACS',
    'description' => 'Menyimpan dan menampilkan DICOM metadata terintegrasi dengan OHIF Viewer.',
    'author' => 'Antigravity',
    'version' => '1.0',
    'category' => 'rekammedik',
    'compatibility' => '6.*.*',
    'icon' => 'camera-retro',
    'pages' => ['Mini PACS' => 'mini_pacs'],
    'install' => function () use ($core) {
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('mini_pacs', 'ae_title', 'MLITE_PACS')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('mini_pacs', 'target_aet', 'TARGET_PACS')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('mini_pacs', 'target_ip', '127.0.0.1')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('mini_pacs', 'target_port', '104')");
    },
    'uninstall' => function () use ($core) {
        $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'mini_pacs'");
    }
];
