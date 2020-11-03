<?php

namespace Plugins\Users;

use Systems\SiteModule;

class Site extends SiteModule
{
    public function init()
    {
        $this->tpl->set('users', function () {
            $result = [];
            $users = $this->core->db_sik('pegawai')->select(['nik', 'nama', 'photo', 'email'])->toArray();

            foreach ($users as $key => $value) {
                $result[$value['nik']] = $users[$key];
                $result[$value['nik']]['photo'] = url('uploads/users/' . $value['photo']);
            }
            return $result;
        });
    }

    public function routes()
    {
        $this->route('login', function () {
            redirect(url([ADMIN]));
        });
    }

}
