<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;

class Jabatan
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('jabatan')
        ->select('kd_jbtn')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('jabatan')
        ->desc('kd_jbtn')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['kd_jbtn'])){
          $return['form'] = $this->db('jabatan')->where('kd_jbtn', $_POST['kd_jbtn'])->oneArray();
        } else {
          $return['form'] = [
            'kd_jbtn' => '',
            'nm_jbtn' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('jabatan')
          ->select('kd_jbtn')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('jabatan')
          ->desc('kd_jbtn')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('jabatan')
            ->like('kd_jbtn', '%'.$_POST['cari'].'%')
            ->orLike('nm_jbtn', '%'.$_POST['cari'].'%')
            ->desc('kd_jbtn')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('jabatan')
            ->desc('kd_jbtn')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('jabatan')->where('kd_jbtn', $_POST['kd_jbtn'])->oneArray()) {
        $query = $this->db('jabatan')->save($_POST);
      } else {
        $query = $this->db('jabatan')->where('kd_jbtn', $_POST['kd_jbtn'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('jabatan')->where('kd_jbtn', $_POST['kd_jbtn'])->delete();
    }

}
