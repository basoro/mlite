<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;

class Bidang
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('bidang')
        ->select('nama')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('bidang')
        ->desc('nama')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['nama'])){
          $return['form'] = $this->db('bidang')->where('nama', $_POST['nama'])->oneArray();
        } else {
          $return['form'] = [
            'nama' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('bidang')
          ->select('nama')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('bidang')
          ->desc('nama')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('bidang')
            ->like('nama', '%'.$_POST['cari'].'%')
            ->desc('nama')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('bidang')
            ->desc('nama')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('bidang')->where('nama', $_POST['nama'])->oneArray()) {
        $query = $this->db('bidang')->save($_POST);
      } else {
        $query = $this->db('bidang')->where('nama', $_POST['nama'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('bidang')->where('nama', $_POST['nama'])->delete();
    }

}
