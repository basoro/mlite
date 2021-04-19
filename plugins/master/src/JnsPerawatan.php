<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;

class JnsPerawatan
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('jns_perawatan')
        ->where('status', '1')
        ->select('kd_jenis_prw')
        ->toArray();
      $offset         = 20;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('jns_perawatan')
        ->join('kategori_perawatan', 'kategori_perawatan.kd_kategori=jns_perawatan.kd_kategori')
        ->join('penjab', 'penjab.kd_pj=jns_perawatan.kd_pj')
        ->join('poliklinik', 'poliklinik.kd_poli=jns_perawatan.kd_poli')
        ->where('jns_perawatan.status', '1')
        ->desc('kd_jenis_prw')
        ->limit(20)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        $return['poliklinik'] = $this->db('poliklinik')->where('status', '1')->toArray();
        $return['kategori_perawatan'] = $this->db('kategori_perawatan')->toArray();
        $return['penjab'] = $this->db('penjab')->toArray();
        if (isset($_POST['kd_jenis_prw'])){
          $return['form'] = $this->db('jns_perawatan')->where('kd_jenis_prw', $_POST['kd_jenis_prw'])->oneArray();
        } else {
          $return['form'] = [
            'kd_jenis_prw' => '',
            'nm_perawatan' => '',
            'kd_kategori' => '',
            'material' => 0,
            'bhp' => 0,
            'tarif_tindakandr' => 0,
            'tarif_tindakanpr' => 0,
            'kso' => 0,
            'menejemen' => 0,
            'total_byrdr' => 0,
            'total_byrpr' => 0,
            'total_byrdrpr' => 0,
            'kd_pj' => '',
            'kd_poli' => '',
            'status' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '20';
        $totalRecords = $this->db('jns_perawatan')
          ->where('status', '1')
          ->select('kd_jenis_prw')
          ->toArray();
        $offset         = 20;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('jns_perawatan')
          ->join('kategori_perawatan', 'kategori_perawatan.kd_kategori=jns_perawatan.kd_kategori')
          ->join('penjab', 'penjab.kd_pj=jns_perawatan.kd_pj')
          ->join('poliklinik', 'poliklinik.kd_poli=jns_perawatan.kd_poli')
          ->where('jns_perawatan.status', '1')
          ->desc('kd_jenis_prw')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('jns_perawatan')
            ->join('kategori_perawatan', 'kategori_perawatan.kd_kategori=jns_perawatan.kd_kategori')
            ->join('penjab', 'penjab.kd_pj=jns_perawatan.kd_pj')
            ->join('poliklinik', 'poliklinik.kd_poli=jns_perawatan.kd_poli')
            ->where('jns_perawatan.status', '1')
            ->like('nm_perawatan', '%'.$_POST['cari'].'%')
            ->desc('kd_jenis_prw')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('jns_perawatan')
            ->join('kategori_perawatan', 'kategori_perawatan.kd_kategori=jns_perawatan.kd_kategori')
            ->join('penjab', 'penjab.kd_pj=jns_perawatan.kd_pj')
            ->join('poliklinik', 'poliklinik.kd_poli=jns_perawatan.kd_poli')
            ->where('jns_perawatan.status', '1')
            ->desc('kd_jenis_prw')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('jns_perawatan')->where('kd_jenis_prw', $_POST['kd_jenis_prw'])->oneArray()) {
        $query = $this->db('jns_perawatan')->save($_POST);
      } else {
        $query = $this->db('jns_perawatan')->where('kd_jenis_prw', $_POST['kd_jenis_prw'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('jns_perawatan')->where('kd_jenis_prw', $_POST['kd_jenis_prw'])->delete();
    }

}
