<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;


class EmergencyIndex
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('emergency_index')
        ->select('kode_emergency')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('emergency_index')
        ->desc('kode_emergency')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['kode_emergency'])){
          $return['form'] = $this->db('emergency_index')->where('kode_emergency', $_POST['kode_emergency'])->oneArray();
        } else {
          $return['form'] = [
            'kode_emergency' => '',
            'nama_emergency' => '',
            'indek' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('emergency_index')
          ->select('kode_emergency')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('emergency_index')
          ->desc('kode_emergency')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('emergency_index')
            ->like('kode_emergency', '%'.$_POST['cari'].'%')
            ->orLike('nama_emergency', '%'.$_POST['cari'].'%')
            ->desc('kode_emergency')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('emergency_index')
            ->desc('kode_emergency')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('emergency_index')->where('kode_emergency', $_POST['kode_emergency'])->oneArray()) {
        $query = $this->db('emergency_index')->save($_POST);
      } else {
        $query = $this->db('emergency_index')->where('kode_emergency', $_POST['kode_emergency'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('emergency_index')->where('kode_emergency', $_POST['kode_emergency'])->delete();
    }

}
