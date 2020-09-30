<?php

namespace Plugins\Rekap_Presensi;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Rekap Presensi' => 'rekap',
        ];
    }

    public function getRekap($page = 1)
    {
        $this->_addHeaderFiles();
        $perpage = '10';
        $phrase = '';
        if(isset($_GET['s']))
          $phrase = $_GET['s'];

        $status = '1';
        if(isset($_GET['status']))
          $status = $_GET['status'];

        // pagination
        // $date = date();
        // $dt = $date->format('Y-m');
        $totalRecords = $this->db('rekap_presensi')
            ->join('pegawai','pegawai.id = rekap_presensi.id')
            ->like('jam_datang', '%'.date('Y-m').'%')
            ->asc('jam_datang')
            ->toArray();
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'rekap_presensi', 'rekap', '%d']));
        $this->assign['pagination'] = $pagination->nav('pagination','5');
        $this->assign['totalRecords'] = $totalRecords;

        // list
        $offset = $pagination->offset();
        $rows = $this->db('rekap_presensi')
            ->join('pegawai','pegawai.id = rekap_presensi.id')
            ->like('jam_datang', '%'.date('Y-m').'%')
            ->asc('jam_datang')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();

        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                // $row['editURL'] = url([ADMIN, 'master', 'petugasedit', $row['nip']]);
                // $row['delURL']  = url([ADMIN, 'master', 'petugasdelete', $row['nip']]);
                // $row['restoreURL']  = url([ADMIN, 'master', 'petugasrestore', $row['nip']]);
                // $row['viewURL'] = url([ADMIN, 'master', 'petugasview', $row['nip']]);
                $this->assign['list'][] = $row;
            }
        }

        $this->assign['getStatus'] = isset($_GET['status']);
        // $this->assign['addURL'] = url([ADMIN, 'presensi', 'jagaadd']);
        // $this->assign['printURL'] = url([ADMIN, 'master', 'petugasprint']);

        return $this->draw('index.html', ['rekap' => $this->assign]);
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/presensi/js/admin/app.js');
        exit();
    }

    private function _addHeaderFiles()
    {

        // CSS
        //$this->core->addCSS(url('assets/jscripts/lightbox/lightbox.min.css'));

        // JS
        //$this->core->addJS(url('assets/jscripts/lightbox/lightbox.min.js'), 'footer');

        // MODULE SCRIPTS
        $this->core->addJS(url([ADMIN, 'presensi', 'javascript']), 'footer');
    }
}
