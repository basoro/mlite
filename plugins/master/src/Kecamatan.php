<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;


class Kecamatan
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('kecamatan')
        ->select('kd_kec')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('kecamatan')
        ->desc('kd_kec')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['kd_kec'])){
          $return['form'] = $this->db('kecamatan')->where('kd_kec', $_POST['kd_kec'])->oneArray();
        } else {
          $return['form'] = [
            'kd_kec' => '',
            'nm_kec' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('kecamatan')
          ->select('kd_kec')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('kecamatan')
          ->desc('kd_kec')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('kecamatan')
            ->like('kd_kec', '%'.$_POST['cari'].'%')
            ->orLike('nm_kec', '%'.$_POST['cari'].'%')
            ->desc('kd_kec')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('kecamatan')
            ->desc('kd_kec')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('kecamatan')->where('kd_kec', $_POST['kd_kec'])->oneArray()) {
        $query = $this->db('kecamatan')->save($_POST);
      } else {
        $query = $this->db('kecamatan')->where('kd_kec', $_POST['kd_kec'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('kecamatan')->where('kd_kec', $_POST['kd_kec'])->delete();
    }

}
