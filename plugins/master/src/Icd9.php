<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;


class Icd9
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('icd9')->count();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil($totalRecords / $offset);
      $return['jumlah_data']    = $totalRecords;

      $return['list'] = $this->db('icd9')
        ->desc('kode')
        ->limit(10)
        ->toArray();

      return htmlspecialchars_array($return);

    }

    public function anyForm()
    {
        if (isset($_POST['kode'])){
          $return['form'] = $this->db('icd9')->where('kode', $_POST['kode'])->oneArray();
        } else {
          $return['form'] = [
            'kode' => '',
            'deskripsi_panjang' => '',
            'deskripsi_pendek' => ''
          ];
        }

        return htmlspecialchars_array($return);
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('icd9')->count();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil($totalRecords / $offset);
        $return['jumlah_data']    = $totalRecords;

        $return['list'] = $this->db('icd9')
          ->desc('kode')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $query = $this->db('icd9')
            ->like('kode', '%'.htmlspecialchars($_POST['cari'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'%')
            ->orLike('deskripsi_panjang', '%'.htmlspecialchars($_POST['cari'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'%')
            ->orLike('deskripsi_pendek', '%'.htmlspecialchars($_POST['cari'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'%');
            
          $jumlah_data = $query->count();
          
          $return['list'] = $query->desc('kode')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
            
          $return['jumlah_data'] = $jumlah_data;
          $return['jml_halaman'] = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('icd9')
            ->desc('kode')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return htmlspecialchars_array($return);
    }

    public function postSave()
    {
      if (!$this->db('icd9')->where('kode', $_POST['kode'])->oneArray()) {
        $query = $this->db('icd9')->save($_POST);
      } else {
        $query = $this->db('icd9')->where('kode', $_POST['kode'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('icd9')->where('kode', $_POST['kode'])->delete();
    }

}