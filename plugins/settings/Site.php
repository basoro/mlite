<?php

namespace Plugins\Settings;

use Systems\SiteModule;

class Site extends SiteModule
{
    public function init()
    {
        $slug = parseURL();

        if (empty($slug[0])) {
            $this->core->router->changeRoute($this->options->get('settings.homepage'));
        }

        \Systems\Lib\Event::add('router.notfound', function () {
            $this->get404();
        });

        $this->tpl->set('settings', function () {
            $settings = $this->db('setting')->toArray();
            return $settings[0];
        });
    }

    public function get404()
    {
        $page = [
            'title' => 'Khanza LITE',
            'desc' => 'Sistem Informasi Rumah Sakit',
            'content' => '<div class="container text-center" style="margin-top: 30px;margin-bottom: 30px;"><h1>404 Not Found</h1></div>'
        ];

        $this->setTemplate('index.html');
        $this->tpl->set('page', $page);
    }

}
