<?php
return [
  'name' => 'Survei Kepuasan',
  'description' => 'Modul survei kepuasan pasien',
  'author' => 'Basoro',
  'version' => '1.0',
  'compatibility' => '2020',
  'icon' => 'pie-chart',
  'pages' => ['Survei' => 'survei_kepuasan'],
  'install' => function () use ($core) {
    $this->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `lite_survei_kepuasan` (
      `id` INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `opsi` INT(10) NOT NULL,
      `tanggal` datetime NOT NULL DEFAULT '00:00:00'
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1;");
  },
  'uninstall'     =>  function () use ($core) {
    $this->core->db()->pdo()->exec("DROP TABLE IF EXISTS `lite_survei_kepuasan`");
  }
]
 ?>
