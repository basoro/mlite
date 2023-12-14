<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;


class Penjab
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('penjab')
        ->select('kd_pj')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('penjab')
        ->desc('kd_pj')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['kd_pj'])){
          $return['form'] = $this->db('penjab')->where('kd_pj', $_POST['kd_pj'])->oneArray();
        } else {
          $return['form'] = [
            'kd_pj' => '',
            'png_jawab' => '',
            'nama_perusahaan' => '',
            'alamat_asuransi' => '',
            'no_telp' => '',
            'attn' => '', 
            'penjab' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('penjab')
          ->select('kd_pj')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('penjab')
          ->desc('kd_pj')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('penjab')
            ->like('kd_pj', '%'.$_POST['cari'].'%')
            ->orLike('png_jawab', '%'.$_POST['cari'].'%')
            ->desc('kd_pj')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('penjab')
            ->desc('kd_pj')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('penjab')->where('kd_pj', $_POST['kd_pj'])->oneArray()) {
        $query = $this->db('penjab')->save($_POST);
      } else {
        $query = $this->db('penjab')->where('kd_pj', $_POST['kd_pj'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('penjab')->where('kd_pj', $_POST['kd_pj'])->delete();
    }

}
