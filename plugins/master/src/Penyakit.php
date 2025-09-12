<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;


class Penyakit
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('penyakit')
        ->select('kd_penyakit')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('penyakit')
        ->join('kategori_penyakit', 'kategori_penyakit.kd_ktg=penyakit.kd_ktg')
        ->desc('kd_penyakit')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['kd_penyakit'])){
          $return['form'] = $this->db('penyakit')->where('kd_penyakit', $_POST['kd_penyakit'])->oneArray();
        } else {
          $return['form'] = [
            'kd_penyakit' => '',
            'nm_penyakit' => '',
            'ciri_ciri' => '',
            'keterangan' => '',
            'kd_ktg' => '',
            'status' => 'Tidak Menular'
          ];
        }

        $return['kategori_penyakit'] = $this->db('kategori_penyakit')->toArray();

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('penyakit')
          ->select('kd_penyakit')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('penyakit')
          ->join('kategori_penyakit', 'kategori_penyakit.kd_ktg=penyakit.kd_ktg')
          ->desc('kd_penyakit')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('penyakit')
            ->join('kategori_penyakit', 'kategori_penyakit.kd_ktg=penyakit.kd_ktg')
            ->like('kd_penyakit', '%'.$_POST['cari'].'%')
            ->orLike('nm_penyakit', '%'.$_POST['cari'].'%')
            ->orLike('ciri_ciri', '%'.$_POST['cari'].'%')
            ->orLike('keterangan', '%'.$_POST['cari'].'%')
            ->orLike('status', '%'.$_POST['cari'].'%')
            ->desc('kd_penyakit')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('penyakit')
            ->join('kategori_penyakit', 'kategori_penyakit.kd_ktg=penyakit.kd_ktg')
            ->desc('kd_penyakit')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('penyakit')->where('kd_penyakit', $_POST['kd_penyakit'])->oneArray()) {
        $query = $this->db('penyakit')->save($_POST);
      } else {
        $query = $this->db('penyakit')->where('kd_penyakit', $_POST['kd_penyakit'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('penyakit')->where('kd_penyakit', $_POST['kd_penyakit'])->delete();
    }

}