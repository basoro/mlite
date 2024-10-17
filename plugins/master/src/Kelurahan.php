<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;


class Kelurahan
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('kelurahan')
        ->select('kd_kel')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('kelurahan')
        ->desc('kd_kel')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['kd_kel'])){
          $return['form'] = $this->db('kelurahan')->where('kd_kel', $_POST['kd_kel'])->oneArray();
        } else {
          $return['form'] = [
            'kd_kel' => '',
            'nm_kel' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('kelurahan')
          ->select('kd_kel')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('kelurahan')
          ->desc('kd_kel')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('kelurahan')
            ->like('kd_kel', '%'.$_POST['cari'].'%')
            ->orLike('nm_kel', '%'.$_POST['cari'].'%')
            ->desc('kd_kel')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('kelurahan')
            ->desc('kd_kel')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('kelurahan')->where('kd_kel', $_POST['kd_kel'])->oneArray()) {
        $query = $this->db('kelurahan')->save($_POST);
      } else {
        $query = $this->db('kelurahan')->where('kd_kel', $_POST['kd_kel'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('kelurahan')->where('kd_kel', $_POST['kd_kel'])->delete();
    }

}
