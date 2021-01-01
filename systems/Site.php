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
        $assign['powered']  = 'Powered by <a href="https://basoro.org/">KhanzaLITE</a>';
        $assign['path']     = url();
        $assign['nama_instansi']    = $this->settings->get('settings.nama_instansi');
        $assign['theme']    = url(THEMES.'/'.$this->settings->get('settings.theme'));
        $assign['nama_instansi']         = $this->settings->get('settings.nama_instansi');
        $assign['version']       = $this->settings->get('settings.version');

        $assign['header']   = isset_or($this->appends['header'], ['']);
        $assign['footer']   = isset_or($this->appends['footer'], ['']);

        $this->tpl->set('mlite', $assign);
        echo $this->tpl->draw(THEMES.'/'.$this->settings->get('settings.theme').'/'.$file, true);
    }

    public function loginCheck()
    {
        if (isset($_SESSION['mlite_user']) && isset($_SESSION['token']) && isset($_SESSION['userAgent']) && isset($_SESSION['IPaddress'])) {
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
