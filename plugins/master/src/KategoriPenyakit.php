<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;

class KategoriPenyakit
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('kategori_penyakit')
        ->select('kd_ktg')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('kategori_penyakit')
        ->desc('kd_ktg')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['kd_ktg'])){
          $return['form'] = $this->db('kategori_penyakit')->where('kd_ktg', $_POST['kd_ktg'])->oneArray();
        } else {
          $return['form'] = [
            'kd_ktg' => '',
            'nm_kategori' => '',
	     'ciri_umum' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('kategori_penyakit')
          ->select('kd_ktg')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('kategori_penyakit')
          ->desc('kd_ktg')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('kategori_penyakit')
            ->like('kd_ktg', '%'.$_POST['cari'].'%')
            ->orLike('nm_kategori', '%'.$_POST['cari'].'%')
	     ->orLike('ciri_umum', '%'.$_POST['cari'].'%')
            ->desc('kd_ktg')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('kategori_penyakit')
            ->desc('kd_ktg')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('kategori_penyakit')->where('kd_ktg', $_POST['kd_ktg'])->oneArray()) {
        $query = $this->db('kategori_penyakit')->save($_POST);
      } else {
        $query = $this->db('kategori_penyakit')->where('kd_ktg', $_POST['kd_ktg'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('kategori_penyakit')->where('kd_ktg', $_POST['kd_ktg'])->delete();
    }

}
