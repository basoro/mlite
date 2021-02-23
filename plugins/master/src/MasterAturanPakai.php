<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;

class MasterAturanPakai
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('master_aturan_pakai')
        ->select('aturan')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('master_aturan_pakai')
        ->desc('aturan')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['aturan'])){
          $return['form'] = $this->db('master_aturan_pakai')->where('aturan', $_POST['aturan'])->oneArray();
        } else {
          $return['form'] = [
            'aturan' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('master_aturan_pakai')
          ->select('aturan')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('master_aturan_pakai')
          ->desc('aturan')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('master_aturan_pakai')
            ->like('aturan', '%'.$_POST['cari'].'%')
            ->desc('aturan')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('master_aturan_pakai')
            ->desc('aturan')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('master_aturan_pakai')->where('aturan', $_POST['aturan'])->oneArray()) {
        $query = $this->db('master_aturan_pakai')->save($_POST);
      } else {
        $query = $this->db('master_aturan_pakai')->where('aturan', $_POST['aturan'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('master_aturan_pakai')->where('aturan', $_POST['aturan'])->delete();
    }

}
