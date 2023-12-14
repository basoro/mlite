<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;


class KodeSatuan
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('kodesatuan')
        ->select('kode_sat')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('kodesatuan')
        ->desc('kode_sat')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['kode_sat'])){
          $return['form'] = $this->db('kodesatuan')->where('kode_sat', $_POST['kode_sat'])->oneArray();
        } else {
          $return['form'] = [
            'kode_sat' => '',
            'satuan' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('kodesatuan')
          ->select('kode_sat')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('kodesatuan')
          ->desc('kode_sat')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('kodesatuan')
            ->like('kode_sat', '%'.$_POST['cari'].'%')
            ->orLike('satuan', '%'.$_POST['cari'].'%')
            ->desc('kode_sat')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('kodesatuan')
            ->desc('kode_sat')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('kodesatuan')->where('kode_sat', $_POST['kode_sat'])->oneArray()) {
        $query = $this->db('kodesatuan')->save($_POST);
      } else {
        $query = $this->db('kodesatuan')->where('kode_sat', $_POST['kode_sat'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('kodesatuan')->where('kode_sat', $_POST['kode_sat'])->delete();
    }

}
