<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;

class KelompokJabatan
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('kelompok_jabatan')
        ->select('kode_kelompok')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('kelompok_jabatan')
        ->desc('kode_kelompok')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['kode_kelompok'])){
          $return['form'] = $this->db('kelompok_jabatan')->where('kode_kelompok', $_POST['kode_kelompok'])->oneArray();
        } else {
          $return['form'] = [
            'kode_kelompok' => '',
            'nama_kelompok' => '',
            'indek' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('kelompok_jabatan')
          ->select('kode_kelompok')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('kelompok_jabatan')
          ->desc('kode_kelompok')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('kelompok_jabatan')
            ->like('kode_kelompok', '%'.$_POST['cari'].'%')
            ->orLike('nama_kelompok', '%'.$_POST['cari'].'%')
            ->desc('kode_kelompok')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('kelompok_jabatan')
            ->desc('kode_kelompok')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('kelompok_jabatan')->where('kode_kelompok', $_POST['kode_kelompok'])->oneArray()) {
        $query = $this->db('kelompok_jabatan')->save($_POST);
      } else {
        $query = $this->db('kelompok_jabatan')->where('kode_kelompok', $_POST['kode_kelompok'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('kelompok_jabatan')->where('kode_kelompok', $_POST['kode_kelompok'])->delete();
    }

}
