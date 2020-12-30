<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;

class Poliklinik
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('poliklinik')
        ->select('kd_poli')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('poliklinik')
        ->desc('kd_poli')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['kd_poli'])){
          $return['form'] = $this->db('poliklinik')->where('kd_poli', $_POST['kd_poli'])->oneArray();
        } else {
          $return['form'] = [
            'kd_poli' => '',
            'nm_poli' => '',
            'registrasi' => '',
            'registrasilama' => '',
            'status' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('poliklinik')
          ->select('kd_poli')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('poliklinik')
          ->desc('kd_poli')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('poliklinik')
            ->like('kd_poli', '%'.$_POST['cari'].'%')
            ->orLike('nm_poli', '%'.$_POST['cari'].'%')
            ->desc('kd_poli')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('poliklinik')
            ->desc('kd_poli')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('poliklinik')->where('kd_poli', $_POST['kd_poli'])->oneArray()) {
        $query = $this->db('poliklinik')->save($_POST);
      } else {
        $query = $this->db('poliklinik')->where('kd_poli', $_POST['kd_poli'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('poliklinik')->where('kd_poli', $_POST['kd_poli'])->delete();
    }

}
