<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;
use Systems\MySQL;

class RuangOk
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->mysql('ruang_ok')
        ->select('kd_ruang_ok')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->mysql('ruang_ok')
        ->desc('kd_ruang_ok')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['kd_ruang_ok'])){
          $return['form'] = $this->mysql('ruang_ok')->where('kd_ruang_ok', $_POST['kd_ruang_ok'])->oneArray();
        } else {
          $return['form'] = [
            'kd_ruang_ok' => '',
            'nm_ruang_ok' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->mysql('ruang_ok')
          ->select('kd_ruang_ok')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->mysql('ruang_ok')
          ->desc('kd_ruang_ok')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->mysql('ruang_ok')
            ->like('kd_ruang_ok', '%'.$_POST['cari'].'%')
            ->orLike('nm_ruang_ok', '%'.$_POST['cari'].'%')
            ->desc('kd_ruang_ok')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->mysql('ruang_ok')
            ->desc('kd_ruang_ok')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->mysql('ruang_ok')->where('kd_ruang_ok', $_POST['kd_ruang_ok'])->oneArray()) {
        $query = $this->mysql('ruang_ok')->save($_POST);
      } else {
        $query = $this->mysql('ruang_ok')->where('kd_ruang_ok', $_POST['kd_ruang_ok'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->mysql('ruang_ok')->where('kd_ruang_ok', $_POST['kd_ruang_ok'])->delete();
    }

    protected function mysql($table = NULL)
    {
        return new MySQL($table);
    }

}
