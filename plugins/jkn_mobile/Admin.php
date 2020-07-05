<?php

namespace Plugins\JKN_Mobile;

use Systems\AdminModule;

/**
 * Sample admin class
 */
class Admin extends AdminModule
{
    /**
     * Module navigation
     * Items of the returned array will be displayed in the administration sidebar
     *
     * @return array
     */
    public function navigation()
    {
        return [
            'Index' => 'index',
        ];
    }

    /**
     * GET: /admin/sample/index
     * Subpage method of the module
     *
     * @return string
     */
    public function getIndex()
    {
        $title = 'Khanza JKN Mobile';
        return $this->draw('index.html', ['title' => $title]);
    }
}
