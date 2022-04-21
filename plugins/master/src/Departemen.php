<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;
use Systems\MySQL;

class Departemen
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->mysql('departemen')
        ->select('dep_id')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->mysql('departemen')
        ->desc('dep_id')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['dep_id'])){
          $return['form'] = $this->mysql('departemen')->where('dep_id', $_POST['dep_id'])->oneArray();
        } else {
          $return['form'] = [
            'dep_id' => '',
            'nama' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->mysql('departemen')
          ->select('dep_id')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->mysql('departemen')
          ->desc('dep_id')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->mysql('departemen')
            ->like('dep_id', '%'.$_POST['cari'].'%')
            ->orLike('nama', '%'.$_POST['cari'].'%')
            ->desc('dep_id')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->mysql('departemen')
            ->desc('dep_id')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->mysql('departemen')->where('dep_id', $_POST['dep_id'])->oneArray()) {
        $query = $this->mysql('departemen')->save($_POST);
      } else {
        $query = $this->mysql('departemen')->where('dep_id', $_POST['dep_id'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->mysql('departemen')->where('dep_id', $_POST['dep_id'])->delete();
    }

    protected function mysql($table = NULL)
    {
        return new MySQL($table);
    }

}
