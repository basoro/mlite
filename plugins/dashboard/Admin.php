<?php

namespace Plugins\Dashboard;

use Systems\AdminModule;

class Admin extends AdminModule
{
    public function navigation()
    {
        return [
            'Main' => 'main'
        ];
    }

    public function getMain()
    {

        $this->core->addCSS(url(MODULES.'/dashboard/css/style.css?v={$opensimrs.version}'));
        $this->core->addJS(url(BASE_DIR.'/assets/jscripts/Chart.bundle.min.js'));
        $this->core->addJS(url(MODULES.'/dashboard/js/app.js?v={$opensimrs.version}'));

        $stats['getPasiens'] = number_format($this->countPasien(),0,'','.');
        $stats['getVisities'] = number_format($this->countVisite(),0,'','.');
        $stats['getCurrentVisities'] = number_format($this->countCurrentVisite(),0,'','.');
        $stats['pasienChart'] = $this->pasienChart(15);

        return $this->draw('dashboard.html', [
          'stats' => $stats,
          'modules' => $this->_modulesList()
        ]);

    }

    private function _modulesList()
    {
        $modules = array_column($this->db('lite_modules')->toArray(), 'dir');
        $result = [];

        if ($this->core->getUserInfo('access') != 'all') {
            $modules = array_intersect($modules, explode(',', $this->core->getUserInfo('access')));
        }

        foreach ($modules as $name) {
            $files = [
                'info'  => MODULES.'/'.$name.'/Info.php',
                'admin' => MODULES.'/'.$name.'/Admin.php',
            ];

            if (file_exists($files['info']) && file_exists($files['admin'])) {
                $details        = $this->core->getModuleInfo($name);
                $features       = $this->core->getModuleNav($name);

                if (empty($features)) {
                    continue;
                }

                $details['url'] = url([ADMIN, $name, array_shift($features)]);

                $result[] = $details;
            }
        }
        return $result;
    }

    public function countVisite()
    {
        $record = $this->db('reg_periksa')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->oneArray();

        return $record['count'];
    }

    public function countCurrentVisite()
    {
        $date = date('Y-m-d');
        $record = $this->db('reg_periksa')
            ->select([
                'count' => 'COUNT(DISTINCT no_rawat)',
            ])
            ->where('tgl_registrasi', $date)
            ->oneArray();

        return $record['count'];
    }

    public function countPasien()
    {
        $record = $this->db('pasien')
            ->select([
                'count' => 'COUNT(DISTINCT no_rkm_medis)',
            ])
            ->oneArray();

        return $record['count'];
    }

    public function pasienChart($days = 14, $offset = 0)
    {
        $time = strtotime(date("Y-m-d", strtotime("-".$days + $offset." days")));
        $date = date("Y-m-d", strtotime("-".$days + $offset." days"));

        $query = $this->db('reg_periksa')
            ->select([
              'count'        => 'COUNT(DISTINCT no_rawat)',
              'formatedDate' => 'tgl_registrasi',
            ])
            ->where('tgl_registrasi', '>=', $date)
            ->group(['formatedDate'])
            ->asc('formatedDate');


            $data = $query->toArray();

            $return = [
                'labels'  => [],
                'visits'  => [],
            ];

            while ($time < (time() - ($offset * 86400))) {
                $return['labels'][] = '"'.date("Y-m-d", $time).'"';
                $return['readable'][] = '"'.date("d M Y", $time).'"';
                $return['visits'][] = 0;

                $time = strtotime('+1 day', $time);
            }

            foreach ($data as $day) {
                $index = array_search('"'.$day['formatedDate'].'"', $return['labels']);
                if ($index === false) {
                    continue;
                }

                $return['visits'][$index] = $day['count'];
            }

        return $return;
    }

}
