<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;


class Pendidikan
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('pendidikan')
        ->select('tingkat')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('pendidikan')
        ->desc('tingkat')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['tingkat'])){
          $return['form'] = $this->db('pendidikan')->where('tingkat', $_POST['tingkat'])->oneArray();
        } else {
          $return['form'] = [
            'tingkat' => '',
            'indek' => '',
	          'gapok1' => '',
            'kenaikan' => '',
            'maksimal' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('pendidikan')
          ->select('tingkat')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('pendidikan')
          ->desc('tingkat')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('pendidikan')
            ->like('tingkat', '%'.$_POST['cari'].'%')
            ->orLike('indek', '%'.$_POST['cari'].'%')
	          ->orLike('gapok1', '%'.$_POST['cari'].'%')
            ->desc('tingkat')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('pendidikan')
            ->desc('tingkat')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('pendidikan')->where('tingkat', $_POST['tingkat'])->oneArray()) {
        $query = $this->db('pendidikan')->save($_POST);
      } else {
        $query = $this->db('pendidikan')->where('tingkat', $_POST['tingkat'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('pendidikan')->where('tingkat', $_POST['tingkat'])->delete();
    }

}
