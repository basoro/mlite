<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;

class Snomed
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {
      $totalRecords = $this->db('mlite_snomed')->count();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil($totalRecords / $offset);
      $return['jumlah_data']    = $totalRecords;

      $return['list'] = $this->db('mlite_snomed')
        ->desc('id')
        ->limit(10)
        ->toArray();

      return htmlspecialchars_array($return);
    }

    public function anyForm()
    {
        if (isset($_POST['id'])){
          $return['form'] = $this->db('mlite_snomed')->where('id', $_POST['id'])->oneArray();
        } else if (isset($_POST['kode'])){
          $return['form'] = $this->db('mlite_snomed')->where('kode', $_POST['kode'])->oneArray();
        } else {
          $return['form'] = [
            'id' => '',
            'kode' => '',
            'istilah' => ''
          ];
        }

        return htmlspecialchars_array($return);
    }

    public function anyDisplay()
    {
        $perpage = '10';
        $totalRecords = $this->db('mlite_snomed')->count();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil($totalRecords / $offset);
        $return['jumlah_data']    = $totalRecords;

        $return['list'] = $this->db('mlite_snomed')
          ->desc('id')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $query = $this->db('mlite_snomed')
            ->like('kode', '%'.htmlspecialchars($_POST['cari'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'%')
            ->orLike('istilah', '%'.htmlspecialchars($_POST['cari'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'%');
          
          $jumlah_data = $query->count();
          
          $return['list'] = $query->desc('id')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
            
          $return['jumlah_data'] = $jumlah_data;
          $return['jml_halaman'] = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('mlite_snomed')
            ->desc('id')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return htmlspecialchars_array($return);
    }

    public function postSave()
    {
      if (empty($_POST['id'])) {
        $query = $this->db('mlite_snomed')->save($_POST);
      } else {
        $query = $this->db('mlite_snomed')->where('id', $_POST['id'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      if (isset($_POST['id'])) {
          return $this->db('mlite_snomed')->where('id', $_POST['id'])->delete();
      }
      if (isset($_POST['kode'])) {
          return $this->db('mlite_snomed')->where('kode', $_POST['kode'])->delete();
      }
      return false;
    }

}
