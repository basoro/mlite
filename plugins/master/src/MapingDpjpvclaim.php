<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;

class MapingDpjpvclaim
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('maping_dokter_dpjpvclaim')
        ->select('kd_dokter')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('maping_dokter_dpjpvclaim')
        ->desc('kd_dokter')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['kd_dokter'])){
          $return['form'] = $this->db('maping_dokter_dpjpvclaim')->where('kd_dokter', $_POST['kd_dokter'])->oneArray();
        } else {
          $return['form'] = [
            'kd_dokter' => '',
            'kd_dokter_bpjs' => '',
            'nm_dokter_bpjs' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('maping_dokter_dpjpvclaim')
          ->select('kd_dokter')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('maping_dokter_dpjpvclaim')
          ->desc('kd_dokter')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('maping_dokter_dpjpvclaim')
            ->like('kd_dokter', '%'.$_POST['cari'].'%')
            ->orLike('nm_dokter_bpjs', '%'.$_POST['cari'].'%')
            ->desc('kd_dokter')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('maping_dokter_dpjpvclaim')
            ->desc('kd_dokter')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('maping_dokter_dpjpvclaim')->where('kd_dokter', $_POST['kd_dokter'])->oneArray()) {
        $query = $this->db('maping_dokter_dpjpvclaim')->save($_POST);
      } else {
        $query = $this->db('maping_dokter_dpjpvclaim')->where('kd_dokter', $_POST['kd_dokter'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('maping_dokter_dpjpvclaim')->where('kd_dokter', $_POST['kd_dokter'])->delete();
    }

}
