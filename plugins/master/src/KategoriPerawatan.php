<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;

class KategoriPerawatan
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('kategori_perawatan')
        ->select('kd_kategori')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('kategori_perawatan')
        ->desc('kd_kategori')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['kd_kategori'])){
          $return['form'] = $this->db('kategori_perawatan')->where('kd_kategori', $_POST['kd_kategori'])->oneArray();
        } else {
          $return['form'] = [
            'kd_kategori' => '',
            'nm_kategori' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('kategori_perawatan')
          ->select('kd_kategori')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('kategori_perawatan')
          ->desc('kd_kategori')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('kategori_perawatan')
            ->like('kd_kategori', '%'.$_POST['cari'].'%')
            ->orLike('nm_kategori', '%'.$_POST['cari'].'%')
            ->desc('kd_kategori')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('kategori_perawatan')
            ->desc('kd_kategori')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('kategori_perawatan')->where('kd_kategori', $_POST['kd_kategori'])->oneArray()) {
        $query = $this->db('kategori_perawatan')->save($_POST);
      } else {
        $query = $this->db('kategori_perawatan')->where('kd_kategori', $_POST['kd_kategori'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('kategori_perawatan')->where('kd_kategori', $_POST['kd_kategori'])->delete();
    }

}
