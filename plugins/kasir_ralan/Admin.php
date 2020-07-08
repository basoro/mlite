<?php

namespace Plugins\Kasir_Ralan;

use Systems\AdminModule;

class Admin extends AdminModule
{
    public function navigation()
    {
        return [
            'Manage' => 'manage',
        ];
    }

    public function getManage($page = 1)
    {
      //$this->_addHeaderFiles();
      $start_date = date('Y-m-d');
      if(isset($_GET['start_date']) && $_GET['start_date'] !='')
        $start_date = $_GET['start_date'];
      $end_date = date('Y-m-d');
      if(isset($_GET['end_date']) && $_GET['end_date'] !='')
        $end_date = $_GET['end_date'];
      $perpage = '10';
      $phrase = '';
      if(isset($_GET['s']))
        $phrase = $_GET['s'];

      // pagination
      $totalRecords = $this->db()->pdo()->prepare("SELECT reg_periksa.no_rawat FROM reg_periksa, pasien WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND (reg_periksa.no_rkm_medis LIKE ? OR reg_periksa.no_rawat LIKE ? OR pasien.nm_pasien LIKE ?) AND reg_periksa.tgl_registrasi BETWEEN '$start_date' AND '$end_date'");
      $totalRecords->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
      $totalRecords = $totalRecords->fetchAll();

      $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url([ADMIN, 'kasir_ralan', 'manage', '%d?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]));
      $this->assign['pagination'] = $pagination->nav('pagination','5');
      $this->assign['totalRecords'] = $totalRecords;

      $offset = $pagination->offset();
      $query = $this->db()->pdo()->prepare("SELECT reg_periksa.*, pasien.nm_pasien, pasien.alamat, dokter.nm_dokter, poliklinik.nm_poli, penjab.png_jawab FROM reg_periksa, pasien, dokter, poliklinik, penjab WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.kd_dokter = dokter.kd_dokter AND reg_periksa.kd_poli = poliklinik.kd_poli AND reg_periksa.kd_pj = penjab.kd_pj AND (reg_periksa.no_rkm_medis LIKE ? OR reg_periksa.no_rawat LIKE ? OR pasien.nm_pasien LIKE ?) AND reg_periksa.tgl_registrasi BETWEEN '$start_date' AND '$end_date' LIMIT $perpage OFFSET $offset");
      $query->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
      $rows = $query->fetchAll();

      $this->assign['list'] = [];
      if (count($rows)) {
          foreach ($rows as $row) {
              $row = htmlspecialchars_array($row);
              $row['viewURL'] = url([ADMIN, 'kasir_ralan', 'view', convertNorawat($row['no_rawat'])]);
              $this->assign['list'][] = $row;
          }
      }

      $this->assign['searchUrl'] =  url([ADMIN, 'kasir_ralan', 'manage', $page.'?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]);
      return $this->draw('manage.html', ['kasir_ralan' => $this->assign]);

    }

    public function getView($no_rawat)
    {
        $reg_periksa = $this->db('reg_periksa')->where('no_rawat', revertNorawat($no_rawat))->oneArray();

        if (!empty($reg_periksa)) {
            $this->assign['manageURL'] = url([ADMIN, 'kasir_ralan', 'manage']);
            return $this->draw('view.html', ['kasir_ralan' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'kasir_ralan', 'manage']));
        }
    }

}
