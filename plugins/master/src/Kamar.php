<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;

class Kamar
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('kamar')
        ->select('kd_kamar')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('kamar')
        ->join('bangsal', 'bangsal.kd_bangsal=kamar.kd_bangsal')
        ->desc('kd_kamar')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        $return['bangsal'] = $this->db('bangsal')->toArray();
        $return['status'] = ['ISI','KOSONG','DIBERSIHKAN','DIBOOKING'];
        $return['kelas'] = ['Kelas 1','Kelas 2','Kelas 3','Kelas Utama','Kelas VIP','Kelas VVIP'];
        if (isset($_POST['kd_kamar'])){
          $return['form'] = $this->db('kamar')->where('kd_kamar', $_POST['kd_kamar'])->oneArray();
        } else {
          $return['form'] = [
            'kd_kamar' => '',
            'kd_bangsal' => '',
            'trf_kamar' => '',
            'status' => '',
            'kelas' => '',
            'statusdata' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('kamar')
          ->select('kd_kamar')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('kamar')
          ->join('bangsal', 'bangsal.kd_bangsal=kamar.kd_bangsal')
          ->desc('kd_kamar')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('kamar')
            ->join('bangsal', 'bangsal.kd_bangsal=kamar.kd_bangsal')
            ->like('kd_kamar', '%'.$_POST['cari'].'%')
            ->orLike('nm_bangsal', '%'.$_POST['cari'].'%')
            ->desc('kd_kamar')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('kamar')
            ->join('bangsal', 'bangsal.kd_bangsal=kamar.kd_bangsal')
            ->desc('kd_kamar')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('kamar')->where('kd_kamar', $_POST['kd_kamar'])->oneArray()) {
        $query = $this->db('kamar')->save($_POST);
      } else {
        $query = $this->db('kamar')->where('kd_kamar', $_POST['kd_kamar'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('kamar')->where('kd_kamar', $_POST['kd_kamar'])->delete();
    }

}
