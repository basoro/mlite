<?php
    namespace Plugins\Laporan;

    use Systems\AdminModule;

    class Admin extends AdminModule
    {

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

        public function getBridgingSEP()
        {
          $this->_addHeaderFiles();
          $settings = $this->settings('settings');
          $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));

          $tgl_awal = date('Y-m-d');
          $tgl_akhir = date('Y-m-d');

          if(isset($_GET['tgl_awal'])) {
            $tgl_awal = $_GET['tgl_awal'];
          }
          if(isset($_GET['tgl_akhir'])) {
            $tgl_akhir = $_GET['tgl_akhir'];
          }

          $sql = "SELECT * FROM bridging_sep WHERE (tglsep BETWEEN '$tgl_awal' AND '$tgl_akhir')";

          $stmt = $this->db()->pdo()->prepare($sql);
          $stmt->execute();
          $rows = $stmt->fetchAll();

          $return['list'] = [];
          $i = 1;
          foreach ($rows as $row) {
            $row['nomor'] = $i++;
            $return['list'][] = $row;
          }

          if(isset($_GET['action']) && $_GET['action'] == 'print') {
            echo $this->draw('bridgingsep.print.html', [
              'bridgingsep' => $return,
              'action' => url([ADMIN,'laporan','bridgingsep'])
            ]);
            exit();
          } else {
            return $this->draw('bridgingsep.html', [
              'bridgingsep' => $return,
              'action' => url([ADMIN,'laporan','bridgingsep'])
            ]);
          }
        }

        public function getCSS()
        {
            header('Content-type: text/css');
            echo $this->draw(MODULES.'/laporan/css/admin/laporan.css');
            exit();
        }

        public function getJavascript()
        {
            header('Content-type: text/javascript');
            echo $this->draw(MODULES.'/laporan/js/admin/laporan.js');
            exit();
        }

        private function _addHeaderFiles()
        {
            // CSS
            $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
            $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
            $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
            $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
            $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
            $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));

            // MODULE SCRIPTS
            $this->core->addCSS(url([ADMIN, 'laporan', 'css']));
            $this->core->addJS(url([ADMIN, 'laporan', 'javascript']), 'footer');
        }

    }
