<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;


class ResikoKerja
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('resiko_kerja')
        ->select('kode_resiko')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('resiko_kerja')
        ->desc('kode_resiko')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['kode_resiko'])){
          $return['form'] = $this->db('resiko_kerja')->where('kode_resiko', $_POST['kode_resiko'])->oneArray();
        } else {
          $return['form'] = [
            'kode_resiko' => '',
            'nama_resiko' => '',
            'indek' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('resiko_kerja')
          ->select('kode_resiko')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('resiko_kerja')
          ->desc('kode_resiko')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('resiko_kerja')
            ->like('kode_resiko', '%'.$_POST['cari'].'%')
            ->orLike('nama_resiko', '%'.$_POST['cari'].'%')
            ->desc('kode_resiko')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('resiko_kerja')
            ->desc('kode_resiko')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('resiko_kerja')->where('kode_resiko', $_POST['kode_resiko'])->oneArray()) {
        $query = $this->db('resiko_kerja')->save($_POST);
      } else {
        $query = $this->db('resiko_kerja')->where('kode_resiko', $_POST['kode_resiko'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('resiko_kerja')->where('kode_resiko', $_POST['kode_resiko'])->delete();
    }

}
