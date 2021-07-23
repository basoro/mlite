<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;

class JnsPerawatanLab
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('jns_perawatan_lab')
        ->where('status', '1')
        ->select('kd_jenis_prw')
        ->toArray();
      $offset         = 20;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('jns_perawatan_lab')
        ->join('penjab', 'penjab.kd_pj=jns_perawatan_lab.kd_pj')
        ->where('jns_perawatan_lab.status', '1')
        ->desc('kd_jenis_prw')
        ->limit(20)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        $return['penjab'] = $this->db('penjab')->toArray();
        $return['kelas'] = ['Kelas 1','Kelas 2','Kelas 3','Kelas Utama','Kelas VIP','Kelas VVIP'];
        $return['kategori'] = ['PA','PK'];
        if (isset($_POST['kd_jenis_prw'])){
          $return['form'] = $this->db('jns_perawatan_lab')->where('kd_jenis_prw', $_POST['kd_jenis_prw'])->oneArray();
        } else {
          $return['form'] = [
            'kd_jenis_prw' => '',
            'nm_perawatan' => '',
            'bagian_rs' => 0,
            'bhp' => 0,
            'tarif_perujuk' =>0,
            'tarif_tindakan_dokter' => 0,
            'tarif_tindakan_petugas' => 0,
            'kso' => 0,
            'menejemen' => 0,
            'total_byr' => 0,
            'kd_pj' => '',
            'status' => '',
            'kelas' => '',
            'kategori' => ''
          ];
        }

        return $return;
    }

    public function anyTemplateLaboratorium()
    {

      $return['kd_jenis_prw'] = $_POST['kd_jenis_prw'];
      $return['list'] = $this->db('template_laboratorium')
        ->where('kd_jenis_prw', $_POST['kd_jenis_prw'])
        ->toArray();

      return $return;

    }

    public function anyDisplay()
    {

        $perpage = '20';
        $totalRecords = $this->db('jns_perawatan_lab')
          ->where('status', '1')
          ->select('kd_jenis_prw')
          ->toArray();
        $offset         = 20;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('jns_perawatan_lab')
          ->join('penjab', 'penjab.kd_pj=jns_perawatan_lab.kd_pj')
          ->where('jns_perawatan_lab.status', '1')
          ->desc('kd_jenis_prw')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('jns_perawatan_lab')
            ->join('penjab', 'penjab.kd_pj=jns_perawatan_lab.kd_pj')
            ->where('jns_perawatan_lab.status', '1')
            ->like('nm_perawatan', '%'.$_POST['cari'].'%')
            ->desc('kd_jenis_prw')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('jns_perawatan_lab')
            ->join('penjab', 'penjab.kd_pj=jns_perawatan_lab.kd_pj')
            ->where('jns_perawatan_lab.status', '1')
            ->desc('kd_jenis_prw')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('jns_perawatan_lab')->where('kd_jenis_prw', $_POST['kd_jenis_prw'])->oneArray()) {
        $query = $this->db('jns_perawatan_lab')->save($_POST);
      } else {
        $query = $this->db('jns_perawatan_lab')->where('kd_jenis_prw', $_POST['kd_jenis_prw'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('jns_perawatan_lab')->where('kd_jenis_prw', $_POST['kd_jenis_prw'])->delete();
    }

}
