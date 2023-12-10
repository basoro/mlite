<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;


class StatusWP
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('stts_wp')
        ->select('stts')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('stts_wp')
        ->desc('stts')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['stts'])){
          $return['form'] = $this->db('stts_wp')->where('stts', $_POST['stts'])->oneArray();
        } else {
          $return['form'] = [
            'stts' => '',
            'ktg' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('stts_wp')
          ->select('stts')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('stts_wp')
          ->desc('stts')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('stts_wp')
            ->like('stts', '%'.$_POST['cari'].'%')
            ->orLike('ktg', '%'.$_POST['cari'].'%')
            ->desc('stts')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('stts_wp')
            ->desc('stts')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('stts_wp')->where('stts', $_POST['stts'])->oneArray()) {
        $query = $this->db('stts_wp')->save($_POST);
      } else {
        $query = $this->db('stts_wp')->where('stts', $_POST['stts'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('stts_wp')->where('stts', $_POST['stts'])->delete();
    }

}
