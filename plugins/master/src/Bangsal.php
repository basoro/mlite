<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;
use Systems\MySQL;

class Bangsal
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->mysql('bangsal')
        ->select('kd_bangsal')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->mysql('bangsal')
        ->desc('kd_bangsal')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['kd_bangsal'])){
          $return['form'] = $this->mysql('bangsal')->where('kd_bangsal', $_POST['kd_bangsal'])->oneArray();
        } else {
          $return['form'] = [
            'kd_bangsal' => '',
            'nm_bangsal' => '',
            'status' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->mysql('bangsal')
          ->select('kd_bangsal')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->mysql('bangsal')
          ->desc('kd_bangsal')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->mysql('bangsal')
            ->like('kd_bangsal', '%'.$_POST['cari'].'%')
            ->orLike('nm_bangsal', '%'.$_POST['cari'].'%')
            ->desc('kd_bangsal')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->mysql('bangsal')
            ->desc('kd_bangsal')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->mysql('bangsal')->where('kd_bangsal', $_POST['kd_bangsal'])->oneArray()) {
        $query = $this->mysql('bangsal')->save($_POST);
      } else {
        $query = $this->mysql('bangsal')->where('kd_bangsal', $_POST['kd_bangsal'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->mysql('bangsal')->where('kd_bangsal', $_POST['kd_bangsal'])->delete();
    }

    protected function mysql($table = NULL)
    {
        return new MySQL($table);
    }

}
