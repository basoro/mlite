<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;

class Kfa
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {
      $totalRecords = $this->db('mlite_kfa')->count();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil($totalRecords / $offset);
      $return['jumlah_data']    = $totalRecords;

      $return['list'] = $this->db('mlite_kfa')
        ->desc('kode_kfa')
        ->limit(10)
        ->toArray();

      return htmlspecialchars_array($return);
    }

    public function anyForm()
    {
        if (isset($_POST['kode_kfa'])){
          $return['form'] = $this->db('mlite_kfa')->where('kode_kfa', $_POST['kode_kfa'])->oneArray();
        } else {
          $return['form'] = [
            'kode_kfa' => '',
            'nama_kfa' => '',
            'kode_bahan' => '',
            'nama_bahan' => '',
            'numerator' => '',
            'satuan_num' => '',
            'denominator' => '',
            'satuan_den' => '',
            'nama_satuan_den' => '',
            'kode_sediaan' => '',
            'nama_sediaan' => '',
            'type' => 'obat'
          ];
        }

        return htmlspecialchars_array($return);
    }

    public function anyDisplay()
    {
        $perpage = '10';
        $totalRecords = $this->db('mlite_kfa')->count();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil($totalRecords / $offset);
        $return['jumlah_data']    = $totalRecords;

        $return['list'] = $this->db('mlite_kfa')
          ->desc('kode_kfa')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $query = $this->db('mlite_kfa')
            ->like('kode_kfa', '%'.htmlspecialchars($_POST['cari'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'%')
            ->orLike('nama_kfa', '%'.htmlspecialchars($_POST['cari'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'%')
            ->orLike('nama_bahan', '%'.htmlspecialchars($_POST['cari'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'%');
            
          $jumlah_data = $query->count();
          
          $return['list'] = $query->desc('kode_kfa')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
            
          $return['jumlah_data'] = $jumlah_data;
          $return['jml_halaman'] = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('mlite_kfa')
            ->desc('kode_kfa')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return htmlspecialchars_array($return);
    }

    public function postSave()
    {
      if (!$this->db('mlite_kfa')->where('kode_kfa', $_POST['kode_kfa'])->oneArray()) {
        $query = $this->db('mlite_kfa')->save($_POST);
      } else {
        $query = $this->db('mlite_kfa')->where('kode_kfa', $_POST['kode_kfa'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('mlite_kfa')->where('kode_kfa', $_POST['kode_kfa'])->delete();
    }

}
