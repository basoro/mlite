<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;

class MasterBerkasDigital
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('master_berkas_digital')
        ->select('kode_berkas')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('master_berkas_digital')
        ->desc('kode_berkas')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['kode_berkas'])){
          $return['form'] = $this->db('master_berkas_digital')->where('kode_berkas', $_POST['kode_berkas'])->oneArray();
        } else {
          $return['form'] = [
            'kode_berkas' => '',
            'nama_berkas' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('master_berkas_digital')
          ->select('kode_berkas')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('master_berkas_digital')
          ->desc('kode_berkas')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('master_berkas_digital')
            ->like('kode_berkas', '%'.$_POST['cari'].'%')
            ->orLike('nama_berkas', '%'.$_POST['cari'].'%')
            ->desc('kode_berkas')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('master_berkas_digital')
            ->desc('kode_berkas')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('master_berkas_digital')->where('kode_berkas', $_POST['kode_berkas'])->oneArray()) {
        $query = $this->db('master_berkas_digital')->save($_POST);
      } else {
        $query = $this->db('master_berkas_digital')->where('kode_berkas', $_POST['kode_berkas'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('master_berkas_digital')->where('kode_berkas', $_POST['kode_berkas'])->delete();
    }

}
