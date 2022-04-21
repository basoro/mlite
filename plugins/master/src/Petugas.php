<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;
use Systems\MySQL;

class Petugas
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->mysql('petugas')
        ->select('nip')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->mysql('petugas')
        ->join('jabatan', 'jabatan.kd_jbtn=petugas.kd_jbtn')
        ->desc('nip')
        ->limit(10)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        $return['pegawai'] = $this->mysql('pegawai')->toArray();
        $return['gol_darah'] = ['-','A','B','O','AB'];
        $return['agama'] = ['ISLAM', 'KRISTEN', 'PROTESTAN', 'HINDU', 'BUDHA', 'KONGHUCU', 'KEPERCAYAAN'];
        $return['stts_nikah'] = ['BELUM MENIKAH','MENIKAH','JANDA','DUDHA','JOMBLO'];
        $return['jabatan'] = $this->mysql('jabatan')->toArray();
        if (isset($_POST['nip'])){
          $return['form'] = $this->mysql('petugas')->where('nip', $_POST['nip'])->oneArray();
        } else {
          $return['form'] = [
            'nip' => '',
            'nama' => '',
            'jk' => '',
            'tmp_lahir' => '',
            'tgl_lahir' => '',
            'gol_darah' => '',
            'agama' => '',
            'stts_nikah' => '',
            'alamat' => '',
            'kd_jbtn' => '',
            'no_telp' => '',
            'status' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->mysql('petugas')
          ->select('nip')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->mysql('petugas')
          ->join('jabatan', 'jabatan.kd_jbtn=petugas.kd_jbtn')
          ->desc('nip')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->mysql('petugas')
            ->join('jabatan', 'jabatan.kd_jbtn=petugas.kd_jbtn')
            ->like('nip', '%'.$_POST['cari'].'%')
            ->orLike('nama', '%'.$_POST['cari'].'%')
            ->desc('nip')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->mysql('petugas')
            ->join('jabatan', 'jabatan.kd_jbtn=petugas.kd_jbtn')
            ->desc('nip')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->mysql('petugas')->where('nip', $_POST['nip'])->oneArray()) {
        $query = $this->mysql('petugas')->save($_POST);
      } else {
        $query = $this->mysql('petugas')->where('nip', $_POST['nip'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->mysql('petugas')->where('nip', $_POST['nip'])->delete();
    }

    protected function mysql($table = NULL)
    {
        return new MySQL($table);
    }

}
