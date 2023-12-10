<?php

namespace Plugins\Keuangan\Src;

use Systems\Lib\QueryWrapper;

class Akunrekening
{

    public function getIndex()
    {

      $return['list'] = $this->db('mlite_rekening')
        ->asc('kd_rek')
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        if (isset($_POST['kd_rek'])){
          $return['form'] = $this->db('mlite_rekening')->where('kd_rek', $_POST['kd_rek'])->oneArray();
        } else {
          $return['form'] = [
            'kd_rek' => '',
            'nm_rek' => '',
            'tipe' => '',
            'balance' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $return['list'] = $this->db('mlite_rekening')
          ->asc('kd_rek')
          ->toArray();

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('mlite_rekening')->where('kd_rek', $_POST['kd_rek'])->oneArray()) {
        $query = $this->db('mlite_rekening')->save($_POST);
      } else {
        $query = $this->db('mlite_rekening')->where('kd_rek', $_POST['kd_rek'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('mlite_rekening')->where('kd_rek', $_POST['kd_rek'])->delete();
    }

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

}
