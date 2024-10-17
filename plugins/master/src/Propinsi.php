<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;


class Propinsi
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('propinsi')
        ->select('kd_prop')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('propinsi')
        ->desc('kd_prop')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['kd_prop'])){
          $return['form'] = $this->db('propinsi')->where('kd_prop', $_POST['kd_prop'])->oneArray();
        } else {
          $return['form'] = [
            'kd_prop' => '',
            'nm_prop' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('propinsi')
          ->select('kd_prop')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('propinsi')
          ->desc('kd_prop')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('propinsi')
            ->like('kd_prop', '%'.$_POST['cari'].'%')
            ->orLike('nm_prop', '%'.$_POST['cari'].'%')
            ->desc('kd_prop')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('propinsi')
            ->desc('kd_prop')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('propinsi')->where('kd_prop', $_POST['kd_prop'])->oneArray()) {
        $query = $this->db('propinsi')->save($_POST);
      } else {
        $query = $this->db('propinsi')->where('kd_prop', $_POST['kd_prop'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('propinsi')->where('kd_prop', $_POST['kd_prop'])->delete();
    }

}
