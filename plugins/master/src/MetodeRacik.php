<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;
use Systems\MySQL;

class MetodeRacik
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->mysql('metode_racik')
        ->select('kd_racik')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->mysql('metode_racik')
        ->desc('kd_racik')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['kd_racik'])){
          $return['form'] = $this->mysql('metode_racik')->where('kd_racik', $_POST['kd_racik'])->oneArray();
        } else {
          $return['form'] = [
            'kd_racik' => '',
            'nm_racik' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->mysql('metode_racik')
          ->select('kd_racik')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->mysql('metode_racik')
          ->desc('kd_racik')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->mysql('metode_racik')
            ->like('kd_racik', '%'.$_POST['cari'].'%')
            ->orLike('nm_racik', '%'.$_POST['cari'].'%')
            ->desc('kd_racik')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->mysql('metode_racik')
            ->desc('kd_racik')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->mysql('metode_racik')->where('kd_racik', $_POST['kd_racik'])->oneArray()) {
        $query = $this->mysql('metode_racik')->save($_POST);
      } else {
        $query = $this->mysql('metode_racik')->where('kd_racik', $_POST['kd_racik'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->mysql('metode_racik')->where('kd_racik', $_POST['kd_racik'])->delete();
    }

    protected function mysql($table = NULL)
    {
        return new MySQL($table);
    }

}
