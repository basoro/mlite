<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;

class Bank
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('bank')
        ->select('namabank')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('bank')
        ->desc('namabank')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['namabank'])){
          $return['form'] = $this->db('bank')->where('namabank', $_POST['namabank'])->oneArray();
        } else {
          $return['form'] = [
            'namabank' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('bank')
          ->select('namabank')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('bank')
          ->desc('namabank')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('bank')
            ->like('namabank', '%'.$_POST['cari'].'%')
            ->desc('namabank')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('bank')
            ->desc('namabank')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('bank')->where('namabank', $_POST['namabank'])->oneArray()) {
        $query = $this->db('bank')->save($_POST);
      } else {
        $query = $this->db('bank')->where('namabank', $_POST['namabank'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('bank')->where('namabank', $_POST['namabank'])->delete();
    }

}
