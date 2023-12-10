<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;


class Spesialis
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('spesialis')
        ->select('kd_sps')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('spesialis')
        ->desc('kd_sps')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['kd_sps'])){
          $return['form'] = $this->db('spesialis')->where('kd_sps', $_POST['kd_sps'])->oneArray();
        } else {
          $return['form'] = [
            'kd_sps' => '',
            'nm_sps' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('spesialis')
          ->select('kd_sps')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('spesialis')
          ->desc('kd_sps')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('spesialis')
            ->like('kd_sps', '%'.$_POST['cari'].'%')
            ->orLike('nm_sps', '%'.$_POST['cari'].'%')
            ->desc('id')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('spesialis')
            ->desc('kd_sps')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('spesialis')->where('kd_sps', $_POST['kd_sps'])->oneArray()) {
        $query = $this->db('spesialis')->save($_POST);
      } else {
        $query = $this->db('spesialis')->where('kd_sps', $_POST['kd_sps'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('spesialis')->where('kd_sps', $_POST['kd_sps'])->delete();
    }

}
