<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;


class Jenis
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('jenis')
        ->select('kdjns')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('jenis')
        ->desc('kdjns')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['kdjns'])){
          $return['form'] = $this->db('jenis')->where('kdjns', $_POST['kdjns'])->oneArray();
        } else {
          $return['form'] = [
            'kdjns' => '',
            'nama' => '',
            'keterangan' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('jenis')
          ->select('kdjns')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('jenis')
          ->desc('kdjns')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('jenis')
            ->like('kdjns', '%'.$_POST['cari'].'%')
            ->orLike('nama', '%'.$_POST['cari'].'%')
            ->desc('kdjns')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('jenis')
            ->desc('kdjns')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('jenis')->where('kdjns', $_POST['kdjns'])->oneArray()) {
        $query = $this->db('jenis')->save($_POST);
      } else {
        $query = $this->db('jenis')->where('kdjns', $_POST['kdjns'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('jenis')->where('kdjns', $_POST['kdjns'])->delete();
    }

}
