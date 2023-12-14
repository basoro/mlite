<?php
return [
    'name'          =>  'AFM',
    'description'   =>  'Katalog API AFM mLITE',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '4.0.*',
    'icon'          =>  'laptop',
    'pages'         =>  ['API AFM mLITE' => 'afm'],
    'install'       =>  function () use ($core) {
      $core->db()->pdo()->exec("INSERT INTO mlite_settings (module, field, value) VALUES ('afm', 'afm_token', 'fc4eba4aa3ea79a7bba3070cba848696')");
      $core->db()->pdo()->exec("INSERT INTO mlite_settings (module, field, value) VALUES ('afm', 'username_finger', '')");
      $core->db()->pdo()->exec("INSERT INTO mlite_settings (module, field, value) VALUES ('afm', 'password_finger', '')");
      $core->db()->pdo()->exec("INSERT INTO mlite_settings (module, field, value) VALUES ('afm', 'x_header_token', 'X-Header-Token')");
    },
    'uninstall'     =>  function () use ($core) {
      $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'afm'");
    }
];
