<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;

class LoincLab
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {
      $totalRecords = $this->db('mlite_loinc_lab')->count();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil($totalRecords / $offset);
      $return['jumlah_data']    = $totalRecords;

      $return['list'] = $this->db('mlite_loinc_lab')
        ->desc('Code')
        ->limit(10)
        ->toArray();

      return htmlspecialchars_array($return);
    }

    public function anyForm()
    {
        if (isset($_POST['Code'])){
          $return['form'] = $this->db('mlite_loinc_lab')->where('Code', $_POST['Code'])->oneArray();
        } else {
          $return['form'] = [
            'Code' => '',
            'Display' => '',
            'Component' => '',
            'Property' => '',
            'Timing' => '',
            'System' => '',
            'Scale' => '',
            'Method' => '',
            'CodeSystem' => 'http://loinc.org'
          ];
        }

        return htmlspecialchars_array($return);
    }

    public function anyDisplay()
    {
        $perpage = '10';
        $totalRecords = $this->db('mlite_loinc_lab')->count();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil($totalRecords / $offset);
        $return['jumlah_data']    = $totalRecords;

        $return['list'] = $this->db('mlite_loinc_lab')
          ->desc('Code')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $query = $this->db('mlite_loinc_lab')
            ->like('Code', '%'.htmlspecialchars($_POST['cari'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'%')
            ->orLike('Display', '%'.htmlspecialchars($_POST['cari'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'%')
            ->orLike('Component', '%'.htmlspecialchars($_POST['cari'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'%');
            
          $jumlah_data = $query->count();
          
          $return['list'] = $query->desc('Code')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
            
          $return['jumlah_data'] = $jumlah_data;
          $return['jml_halaman'] = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('mlite_loinc_lab')
            ->desc('Code')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return htmlspecialchars_array($return);
    }

    public function postSave()
    {
      if (!$this->db('mlite_loinc_lab')->where('Code', $_POST['Code'])->oneArray()) {
        $query = $this->db('mlite_loinc_lab')->save($_POST);
      } else {
        $query = $this->db('mlite_loinc_lab')->where('Code', $_POST['Code'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('mlite_loinc_lab')->where('Code', $_POST['Code'])->delete();
    }

}
