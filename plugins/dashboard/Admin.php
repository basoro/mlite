<?php

namespace Plugins\Dashboard;

use Systems\AdminModule;
use Systems\Lib\HttpRequest;

class Admin extends AdminModule
{
    public function navigation()
    {
        if ($this->core->getUserInfo('id') == 1) {
            return [
                'Main' => 'main',
                'Pengaturan' => 'settings'
            ];
        } else {
            return [
                'Main' => 'main'
            ];
        }
    }

    public function getMain()
    {

        $this->core->addCSS(url(MODULES.'/dashboard/css/style.css?v={$opensimrs.version}'));
        $this->core->addJS(url(BASE_DIR.'/assets/jscripts/Chart.bundle.min.js'));
        $this->core->addJS(url(MODULES.'/dashboard/js/app.js?v={$opensimrs.version}'));

        $settings = htmlspecialchars_array($this->options('dashboard'));
        $stats['getPasiens'] = number_format($this->countPasien(),0,'','.');
        $stats['getVisities'] = number_format($this->countVisite(),0,'','.');
        $stats['getCurrentVisities'] = number_format($this->countCurrentVisite(),0,'','.');
        $stats['poliChart'] = $this->poliChart();
        $stats['KunjunganTahunChart'] = $this->KunjunganTahunChart();
        $stats['RanapTahunChart'] = $this->RanapTahunChart();
        $stats['RujukTahunChart'] = $this->RujukTahunChart();
        $stats['tunai'] = $this->db('reg_periksa')->select(['count' => 'COUNT(DISTINCT no_rawat)'])->where('kd_pj', $settings['umum'])->like('tgl_registrasi', date('Y').'%')->oneArray();
        $stats['bpjs'] = $this->db('reg_periksa')->select(['count' => 'COUNT(DISTINCT no_rawat)'])->where('kd_pj', $settings['bpjs'])->like('tgl_registrasi', date('Y').'%')->oneArray();
        $stats['lainnya'] = $this->db('reg_periksa')->select(['count' => 'COUNT(DISTINCT no_rawat)'])->where('kd_pj', '!=', $settings['umum'])->where('kd_pj', '!=', $settings['bpjs'])->like('tgl_registrasi', date('Y').'%')->oneArray();

        $day = array(
          'Sun' => 'AKHAD',
          'Mon' => 'SENIN',
          'Tue' => 'SELASA',
          'Wed' => 'RABU',
          'Thu' => 'KAMIS',
          'Fri' => 'JUMAT',
          'Sat' => 'SABTU'
        );
        $hari=$day[date('D',strtotime(date('Y-m-d')))];

        return $this->draw('dashboard.html', [
          'settings' => $settings,
          'stats' => $stats,
          'pasien' => $this->db('pasien')->join('penjab', 'penjab.kd_pj = pasien.kd_pj')->desc('tgl_daftar')->limit('5')->toArray(),
          'dokter' => $this->db('dokter')->join('spesialis', 'spesialis.kd_sps = dokter.kd_sps')->join('jadwal', 'jadwal.kd_dokter = dokter.kd_dokter')->where('jadwal.hari_kerja', $hari)->where('status', '1')->group('dokter.kd_dokter')->rand()->limit('6')->toArray(),
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

    public function poliChart()
    {

        $query = $this->db('reg_periksa')
            ->select([
              'count'       => 'COUNT(DISTINCT no_rawat)',
              'nm_poli'     => 'nm_poli',
            ])
            ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
            ->where('tgl_registrasi', '>=', date('Y-m-d'))
            ->group(['reg_periksa.kd_poli'])
            ->desc('nm_poli');


            $data = $query->toArray();

            $return = [
                'labels'  => [],
                'visits'  => [],
            ];

            foreach ($data as $value) {
                $return['labels'][] = $value['nm_poli'];
                $return['visits'][] = $value['count'];
            }

        return $return;
    }

    public function KunjunganTahunChart()
    {

        $query = $this->db('reg_periksa')
            ->select([
              'count'       => 'COUNT(DISTINCT no_rawat)',
              'label'       => 'tgl_registrasi'
            ])
            ->like('tgl_registrasi', date('Y').'%')
            ->group('EXTRACT(MONTH FROM tgl_registrasi)');

            $data = $query->toArray();

            $return = [
                'labels'  => [],
                'visits'  => []
            ];
            foreach ($data as $value) {
                $return['labels'][] = date("M", strtotime($value['label']));
                $return['visits'][] = $value['count'];
            }

        return $return;
    }

    public function RanapTahunChart()
    {

        $query = $this->db('reg_periksa')
            ->select([
              'count'       => 'COUNT(DISTINCT no_rawat)',
              'label'       => 'tgl_registrasi'
            ])
            ->where('stts', 'Dirawat')
            ->like('tgl_registrasi', date('Y').'%')
            ->group('EXTRACT(MONTH FROM tgl_registrasi)');

            $data = $query->toArray();

            $return = [
                'labels'  => [],
                'visits'  => []
            ];
            foreach ($data as $value) {
                $return['labels'][] = date("M", strtotime($value['label']));
                $return['visits'][] = $value['count'];
            }

        return $return;
    }

    public function RujukTahunChart()
    {

        $query = $this->db('reg_periksa')
            ->select([
              'count'       => 'COUNT(DISTINCT no_rawat)',
              'label'       => 'tgl_registrasi'
            ])
            ->where('stts', 'Dirujuk')
            ->like('tgl_registrasi', date('Y').'%')
            ->group('EXTRACT(MONTH FROM tgl_registrasi)');

            $data = $query->toArray();

            $return = [
                'labels'  => [],
                'visits'  => []
            ];
            foreach ($data as $value) {
                $return['labels'][] = date("M", strtotime($value['label']));
                $return['visits'][] = $value['count'];
            }

        return $return;
    }

    public function getSettings()
    {
        $this->assign['penjab'] = $this->core->db('penjab')->toArray();
        $this->assign['dashboard'] = htmlspecialchars_array($this->options('dashboard'));
        return $this->draw('settings.html', ['settings' => $this->assign]);
    }

    public function postSaveSettings()
    {
        foreach ($_POST['dashboard'] as $key => $val) {
            $this->options('dashboard', $key, $val);
        }
        $this->notify('success', 'Pengaturan pasien telah disimpan');
        redirect(url([ADMIN, 'dashboard', 'settings']));
    }

}
