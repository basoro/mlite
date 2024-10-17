<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;


class Kabupaten
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('kabupaten')
        ->select('kd_kab')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('kabupaten')
        ->desc('kd_kab')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['kd_kab'])){
          $return['form'] = $this->db('kabupaten')->where('kd_kab', $_POST['kd_kab'])->oneArray();
        } else {
          $return['form'] = [
            'kd_kab' => '',
            'nm_kab' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('kabupaten')
          ->select('kd_kab')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('kabupaten')
          ->desc('kd_kab')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('kabupaten')
            ->like('kd_kab', '%'.$_POST['cari'].'%')
            ->orLike('nm_kab', '%'.$_POST['cari'].'%')
            ->desc('kd_kab')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('kabupaten')
            ->desc('kd_kab')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('kabupaten')->where('kd_kab', $_POST['kd_kab'])->oneArray()) {
        $query = $this->db('kabupaten')->save($_POST);
      } else {
        $query = $this->db('kabupaten')->where('kd_kab', $_POST['kd_kab'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('kabupaten')->where('kd_kab', $_POST['kd_kab'])->delete();
    }

}
