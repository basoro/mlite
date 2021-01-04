<?php
    namespace Plugins\Laporan;

    use Systems\AdminModule;
    use Plugins\Master\Src\BridgingSEP;
    use Systems\Lib\Fpdf\PDF_MC_Table;

    class Admin extends AdminModule
    {

        public function init()
        {
            $this->bridgingsep = new BridgingSEP();
        }

        public function navigation()
        {
            return [
                'Manage' => 'manage',
                'Bridging SEP' => 'bridgingsep',
            ];
        }

        public function getManage()
        {
          $sub_modules = [
            ['name' => 'Bridging SEP', 'url' => url([ADMIN, 'laporan', 'bridgingsep']), 'icon' => 'cubes', 'desc' => 'Laporan bridging SEP'],
          ];
          return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
        }

        /* Start Bahasa Section */
        public function getBridgingSEP()
        {
          $this->core->addJS(url([ADMIN, 'laporan', 'bridgingsepjs']), 'footer');
          $return = $this->bahasa->getIndex();
          return $this->draw('bridgingsep.html', [
            'bahasa' => $return
          ]);

        }

        public function anyBahasaForm()
        {
            $return = $this->bahasa->anyForm();
            echo $this->draw('bahasa.form.html', ['bahasa' => $return]);
            exit();
        }

        public function anyBahasaDisplay()
        {
            $return = $this->bahasa->anyDisplay();
            echo $this->draw('bahasa.display.html', ['bahasa' => $return]);
            exit();
        }

        public function postBahasaSave()
        {
          $this->bahasa->postSave();
          exit();
        }

        public function postBahasaHapus()
        {
          $this->bahasa->postHapus();
          exit();
        }

        public function getBahasaJS()
        {
            header('Content-type: text/javascript');
            echo $this->draw(MODULES.'/master/js/admin/bahasa.js');
            exit();
        }
        /* End Bahasa Section */

        public function getCSS()
        {
            header('Content-type: text/css');
            echo $this->draw(MODULES.'/master/css/admin/master.css');
            exit();
        }

        public function getJavascript()
        {
            header('Content-type: text/javascript');
            echo $this->draw(MODULES.'/master/js/admin/master.js');
            exit();
        }

        private function _addHeaderFiles()
        {
            // CSS
            $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
            $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
            $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));

            // MODULE SCRIPTS
            $this->core->addCSS(url([ADMIN, 'master', 'css']));
            $this->core->addJS(url([ADMIN, 'master', 'javascript']), 'footer');
        }

    }
