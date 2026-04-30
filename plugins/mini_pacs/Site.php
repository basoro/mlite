<?php

namespace Plugins\Mini_Pacs;

use Systems\SiteModule;

class Site extends SiteModule
{


    public function routes()
    {
        $this->route('main/mini_pacs', 'getIndex');
    }

    public function getIndex()
    {
        echo $this->draw('login.html');
        exit();
    }


}
