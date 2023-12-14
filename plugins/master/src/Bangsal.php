<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;


class Bangsal
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('bangsal')
        ->select('kd_bangsal')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('bangsal')
        ->desc('kd_bangsal')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['kd_bangsal'])){
          $return['form'] = $this->db('bangsal')->where('kd_bangsal', $_POST['kd_bangsal'])->oneArray();
        } else {
          $return['form'] = [
            'kd_bangsal' => '',
            'nm_bangsal' => '',
            'status' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('bangsal')
          ->select('kd_bangsal')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('bangsal')
          ->desc('kd_bangsal')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('bangsal')
            ->like('kd_bangsal', '%'.$_POST['cari'].'%')
            ->orLike('nm_bangsal', '%'.$_POST['cari'].'%')
            ->desc('kd_bangsal')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('bangsal')
            ->desc('kd_bangsal')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('bangsal')->where('kd_bangsal', $_POST['kd_bangsal'])->oneArray()) {
        $query = $this->db('bangsal')->save($_POST);
      } else {
        $query = $this->db('bangsal')->where('kd_bangsal', $_POST['kd_bangsal'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('bangsal')->where('kd_bangsal', $_POST['kd_bangsal'])->delete();
    }

}
