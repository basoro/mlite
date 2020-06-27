<?php

namespace Systems;

class Site extends Main
{
    public $template = 'index.html';

    public function __construct()
    {
        parent::__construct();
        $this->loadModules();

        $return = $this->router->execute();

        if (is_string($this->template)) {
            $this->drawTheme($this->template);
        } elseif ($this->template === false) {
            if (strpos(get_headers_list('Content-Type'), 'text/html') !== false) {
                header("Content-type: text/plain");
            }

            echo $return;
        }

        $this->module->finishLoop();
    }

    private function drawTheme($file)
    {
        $assign = [];
        $assign['notify']   = $this->getNotify();
        $assign['powered']  = 'Powered by <a href="https://khanza.basoro.id/">Khanza LITE</a>';
        $assign['path']     = url();
        $assign['theme']    = url(THEMES.'/site');
        $assign['version']  = $this->options->get('settings.version');

        $assign['header']   = isset_or($this->appends['header'], ['']);
        $assign['footer']   = isset_or($this->appends['footer'], ['']);

        $this->tpl->set('opensimrs', $assign);
        echo $this->tpl->draw(THEMES.'/site/'.$file, true);
    }

    public function loginCheck()
    {
        if (isset($_SESSION['opensimrs_user']) && isset($_SESSION['token']) && isset($_SESSION['userAgent']) && isset($_SESSION['IPaddress'])) {
            if ($_SESSION['IPaddress'] != $_SERVER['REMOTE_ADDR']) {
                return false;
            }
            if ($_SESSION['userAgent'] != $_SERVER['HTTP_USER_AGENT']) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    }
}
