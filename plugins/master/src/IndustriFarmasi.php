<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;

class IndustriFarmasi
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('industrifarmasi')
        ->select('kode_industri')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('industrifarmasi')
        ->desc('kode_industri')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['kode_industri'])){
          $return['form'] = $this->db('industrifarmasi')->where('kode_industri', $_POST['kode_industri'])->oneArray();
        } else {
          $return['form'] = [
            'kode_industri' => '',
            'nama_industri' => '',
            'alamat' => '',
            'kota' => '',
            'no_telp' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('industrifarmasi')
          ->select('kode_industri')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('industrifarmasi')
          ->desc('kode_industri')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('industrifarmasi')
            ->like('kode_industri', '%'.$_POST['cari'].'%')
            ->orLike('nama_industri', '%'.$_POST['cari'].'%')
            ->desc('kode_industri')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('industrifarmasi')
            ->desc('kode_industri')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('industrifarmasi')->where('kode_industri', $_POST['kode_industri'])->oneArray()) {
        $query = $this->db('industrifarmasi')->save($_POST);
      } else {
        $query = $this->db('industrifarmasi')->where('kode_industri', $_POST['kode_industri'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('industrifarmasi')->where('kode_industri', $_POST['kode_industri'])->delete();
    }

}
