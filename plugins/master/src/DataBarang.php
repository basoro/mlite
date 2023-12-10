<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;


class DataBarang
{

    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function getIndex()
    {

      $totalRecords = $this->db('databarang')
        ->select('kode_brng')
        ->toArray();
      $offset         = 30;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('databarang')
        ->select([
          'kode_brng' => 'kode_brng',
          'nama_brng' => 'nama_brng',
          'kode_satbesar' => 'kodesatuan.satuan',
          'kode_sat' => 'kodesatuan.satuan',
          'letak_barang' => 'letak_barang',
          'dasar' => 'dasar',
          'h_beli' => 'h_beli',
          'ralan' => 'ralan',
          'kelas1' => 'kelas1',
          'kelas2' => 'kelas2',
          'kelas3' => 'kelas3',
          'utama' => 'utama',
          'vip' => 'vip',
          'vvip' => 'vvip',
          'beliluar' => 'beliluar',
          'jualbebas' => 'jualbebas',
          'karyawan' => 'karyawan',
          'stokminimal' => 'stokminimal',
          'kdjns' => 'jenis.nama',
          'isi' => 'isi',
          'kapasitas' => 'kapasitas',
          'expire' => 'expire',
          'status' => 'status',
          'kode_industri' => 'industrifarmasi.nama_industri',
          'kode_kategori' => 'kategori_barang.nama',
          'kode_golongan' => 'golongan_barang.nama'
        ])
        ->join('jenis', 'jenis.kdjns=databarang.kdjns')
        ->join('kodesatuan', 'kodesatuan.kode_sat=databarang.kode_sat')
        ->join('industrifarmasi', 'industrifarmasi.kode_industri=databarang.kode_industri')
        ->join('kategori_barang', 'kategori_barang.kode=databarang.kode_kategori')
        ->join('golongan_barang', 'golongan_barang.kode=databarang.kode_golongan')
        ->desc('kode_brng')
        ->limit(30)
        ->toArray();

      return $return;

    }

    public function anyForm()
    {
        $return['golongan_barang'] = $this->db('golongan_barang')->toArray();
        $return['kategori_barang'] = $this->db('kategori_barang')->toArray();
        $return['industrifarmasi'] = $this->db('industrifarmasi')->toArray();
        $return['jenis'] = $this->db('jenis')->toArray();
        $return['kodesatuan'] = $this->db('kodesatuan')->toArray();
        $return['status'] = ['1','0'];
        if (isset($_POST['kode_brng'])){
          $return['form'] = $this->db('databarang')->where('kode_brng', $_POST['kode_brng'])->oneArray();
        } else {
          $return['form'] = [
            'kode_brng' => '',
            'nama_brng' => '',
            'kode_satbesar' => '',
            'kode_sat' => '',
            'letak_barang' => '',
            'dasar' => '',
            'h_beli' => '',
            'ralan' => '',
            'kelas1' => '',
            'kelas2' => '',
            'kelas3' => '',
            'utama' => '',
            'vip' => '',
            'vvip' => '',
            'beliluar' => '',
            'jualbebas' => '',
            'karyawan' => '',
            'stokminimal' => '',
            'kdjns' => '',
            'isi' => '',
            'kapasitas' => '',
            'expire' => '',
            'status' => '',
            'kode_industri' => '',
            'kode_kategori' => '',
            'kode_golongan' => ''
          ];
        }

        return $return;
    }

    public function anyDisplay()
    {

        $perpage = '30';
        $totalRecords = $this->db('databarang')
          ->select('kode_brng')
          ->toArray();
        $offset         = 30;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('databarang')
          ->select([
            'kode_brng' => 'kode_brng',
            'nama_brng' => 'nama_brng',
            'kode_satbesar' => 'kodesatuan.satuan',
            'kode_sat' => 'kodesatuan.satuan',
            'letak_barang' => 'letak_barang',
            'dasar' => 'dasar',
            'h_beli' => 'h_beli',
            'ralan' => 'ralan',
            'kelas1' => 'kelas1',
            'kelas2' => 'kelas2',
            'kelas3' => 'kelas3',
            'utama' => 'utama',
            'vip' => 'vip',
            'vvip' => 'vvip',
            'beliluar' => 'beliluar',
            'jualbebas' => 'jualbebas',
            'karyawan' => 'karyawan',
            'stokminimal' => 'stokminimal',
            'kdjns' => 'jenis.nama',
            'isi' => 'isi',
            'kapasitas' => 'kapasitas',
            'expire' => 'expire',
            'status' => 'status',
            'kode_industri' => 'industrifarmasi.nama_industri',
            'kode_kategori' => 'kategori_barang.nama',
            'kode_golongan' => 'golongan_barang.nama'
          ])
          ->join('jenis', 'jenis.kdjns=databarang.kdjns')
          ->join('kodesatuan', 'kodesatuan.kode_sat=databarang.kode_sat')
          ->join('industrifarmasi', 'industrifarmasi.kode_industri=databarang.kode_industri')
          ->join('kategori_barang', 'kategori_barang.kode=databarang.kode_kategori')
          ->join('golongan_barang', 'golongan_barang.kode=databarang.kode_golongan')
          ->desc('kode_brng')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('databarang')
            ->select([
              'kode_brng' => 'kode_brng',
              'nama_brng' => 'nama_brng',
              'kode_satbesar' => 'kodesatuan.satuan',
              'kode_sat' => 'kodesatuan.satuan',
              'letak_barang' => 'letak_barang',
              'dasar' => 'dasar',
              'h_beli' => 'h_beli',
              'ralan' => 'ralan',
              'kelas1' => 'kelas1',
              'kelas2' => 'kelas2',
              'kelas3' => 'kelas3',
              'utama' => 'utama',
              'vip' => 'vip',
              'vvip' => 'vvip',
              'beliluar' => 'beliluar',
              'jualbebas' => 'jualbebas',
              'karyawan' => 'karyawan',
              'stokminimal' => 'stokminimal',
              'kdjns' => 'jenis.nama',
              'isi' => 'isi',
              'kapasitas' => 'kapasitas',
              'expire' => 'expire',
              'status' => 'status',
              'kode_industri' => 'industrifarmasi.nama_industri',
              'kode_kategori' => 'kategori_barang.nama',
              'kode_golongan' => 'golongan_barang.nama'
            ])
            ->join('jenis', 'jenis.kdjns=databarang.kdjns')
            ->join('kodesatuan', 'kodesatuan.kode_sat=databarang.kode_sat')
            ->join('industrifarmasi', 'industrifarmasi.kode_industri=databarang.kode_industri')
            ->join('kategori_barang', 'kategori_barang.kode=databarang.kode_kategori')
            ->join('golongan_barang', 'golongan_barang.kode=databarang.kode_golongan')
            ->like('kode_brng', '%'.$_POST['cari'].'%')
            ->orLike('nama_brng', '%'.$_POST['cari'].'%')
            ->desc('kode_brng')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('databarang')
            ->select([
              'kode_brng' => 'kode_brng',
              'nama_brng' => 'nama_brng',
              'kode_satbesar' => 'kodesatuan.satuan',
              'kode_sat' => 'kodesatuan.satuan',
              'letak_barang' => 'letak_barang',
              'dasar' => 'dasar',
              'h_beli' => 'h_beli',
              'ralan' => 'ralan',
              'kelas1' => 'kelas1',
              'kelas2' => 'kelas2',
              'kelas3' => 'kelas3',
              'utama' => 'utama',
              'vip' => 'vip',
              'vvip' => 'vvip',
              'beliluar' => 'beliluar',
              'jualbebas' => 'jualbebas',
              'karyawan' => 'karyawan',
              'stokminimal' => 'stokminimal',
              'kdjns' => 'jenis.nama',
              'isi' => 'isi',
              'kapasitas' => 'kapasitas',
              'expire' => 'expire',
              'status' => 'status',
              'kode_industri' => 'industrifarmasi.nama_industri',
              'kode_kategori' => 'kategori_barang.nama',
              'kode_golongan' => 'golongan_barang.nama'
            ])
            ->join('jenis', 'jenis.kdjns=databarang.kdjns')
            ->join('kodesatuan', 'kodesatuan.kode_sat=databarang.kode_sat')
            ->join('industrifarmasi', 'industrifarmasi.kode_industri=databarang.kode_industri')
            ->join('kategori_barang', 'kategori_barang.kode=databarang.kode_kategori')
            ->join('golongan_barang', 'golongan_barang.kode=databarang.kode_golongan')
            ->desc('kode_brng')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        return $return;
    }

    public function postSave()
    {
      if (!$this->db('databarang')->where('kode_brng', $_POST['kode_brng'])->oneArray()) {
        $query = $this->db('databarang')->save($_POST);
      } else {
        $query = $this->db('databarang')->where('kode_brng', $_POST['kode_brng'])->save($_POST);
      }
      return $query;
    }

    public function postHapus()
    {
      return $this->db('databarang')->where('kode_brng', $_POST['kode_brng'])->update(['status', '0']);
    }

    public function postMaxId()
    {
      $max_id = $this->db('databarang')->select(['kode_brng' => 'ifnull(MAX(CONVERT(RIGHT(kode_brng,5),signed)),0)'])->oneArray();
      if(empty($max_id['kode_brng'])) {
        $max_id['kode_brng'] = '00000';
      }
      $_next_max_id = sprintf('%05s', ($max_id['kode_brng'] + 1));
      $next_max_id = 'B'.$_next_max_id;
      echo $next_max_id;
      exit();
    }

}
