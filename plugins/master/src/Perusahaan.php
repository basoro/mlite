<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;


class Perusahaan
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('perusahaan_pasien')
        ->select('kode_perusahaan')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('perusahaan_pasien')
        ->desc('kode_perusahaan')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['kode_perusahaan'])){
          $return['form'] = $this->db('perusahaan_pasien')->where('kode_perusahaan', $_POST['kode_perusahaan'])->oneArray();
        } else {
          $return['form'] = [
            'kode_perusahaan' => '',
            'nama_perusahaan' => '',
            'alamat' => '',
            'kota' => '',
            'no_telp' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('perusahaan_pasien')
          ->select('kode_perusahaan')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('perusahaan_pasien')
          ->desc('kode_perusahaan')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('perusahaan_pasien')
            ->like('kode_perusahaan', '%'.$_POST['cari'].'%')
            ->orLike('nama_perusahaan', '%'.$_POST['cari'].'%')
            ->desc('kode_perusahaan')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('perusahaan_pasien')
            ->desc('kode_perusahaan')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('perusahaan_pasien')->where('kode_perusahaan', $_POST['kode_perusahaan'])->oneArray()) {
        $query = $this->db('perusahaan_pasien')->save($_POST);
      } else {
        $query = $this->db('perusahaan_pasien')->where('kode_perusahaan', $_POST['kode_perusahaan'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('perusahaan_pasien')->where('kode_perusahaan', $_POST['kode_perusahaan'])->delete();
    }

}
