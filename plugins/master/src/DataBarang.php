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
      $offset         = 20;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $rows = $this->db('databarang')
        ->desc('kode_brng')
        ->limit(20)
        ->toArray();
      foreach ($rows as $row) {
        $jenis = $this->db('jenis')->where('kdjns', $row['kdjns'])->oneArray();
        $kodesatuan = $this->db('kodesatuan')->where('kode_sat', $row['kode_sat'])->oneArray();
        $kodesatuan_besar = $this->db('kodesatuan')->where('kode_sat', $row['kode_satbesar'])->oneArray();
        $industrifarmasi = $this->db('industrifarmasi')->where('kode_industri', $row['kode_industri'])->oneArray();
        $kategori_barang = $this->db('kategori_barang')->where('kode', $row['kode_kategori'])->oneArray();
        $golongan_barang = $this->db('golongan_barang')->where('kode', $row['kode_golongan'])->oneArray();
        $row['nama_jenis'] = $jenis['nama'];
        $row['nama_satuan'] = $kodesatuan['satuan'];
        $row['nama_satuan_besar'] = $kodesatuan_besar['satuan'];
        $row['nama_industri'] = $industrifarmasi['nama_industri'];
        $row['nama_kategori'] = $kategori_barang['nama'];
        $row['nama_golongan'] = $golongan_barang['nama'];
        $return['list'][] = $row;
      }
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

        $perpage = '20';
        $totalRecords = $this->db('databarang')
          ->select('kode_brng')
          ->toArray();
        $offset         = 20;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        if(isset($_POST['cari'])) {
          $rows = $this->db('databarang')
            ->like('kode_brng', '%'.$_POST['cari'].'%')
            ->orLike('nama_brng', '%'.$_POST['cari'].'%')
            ->desc('kode_brng')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          foreach ($rows as $row) {
            $jenis = $this->db('jenis')->where('kdjns', $row['kdjns'])->oneArray();
            $kodesatuan = $this->db('kodesatuan')->where('kode_sat', $row['kode_sat'])->oneArray();
            $kodesatuan_besar = $this->db('kodesatuan')->where('kode_sat', $row['kode_satbesar'])->oneArray();
            $industrifarmasi = $this->db('industrifarmasi')->where('kode_industri', $row['kode_industri'])->oneArray();
            $kategori_barang = $this->db('kategori_barang')->where('kode', $row['kode_kategori'])->oneArray();
            $golongan_barang = $this->db('golongan_barang')->where('kode', $row['kode_golongan'])->oneArray();
            $row['nama_jenis'] = $jenis['nama'];
            $row['nama_satuan'] = $kodesatuan['satuan'];
            $row['nama_satuan_besar'] = $kodesatuan_besar['satuan'];
            $row['nama_industri'] = $industrifarmasi['nama_industri'];
            $row['nama_kategori'] = $kategori_barang['nama'];
            $row['nama_golongan'] = $golongan_barang['nama'];
            $return['list'][] = $row;
          }

          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        } else if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $rows = $this->db('databarang')
            ->desc('kode_brng')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          foreach ($rows as $row) {
            $jenis = $this->db('jenis')->where('kdjns', $row['kdjns'])->oneArray();
            $kodesatuan = $this->db('kodesatuan')->where('kode_sat', $row['kode_sat'])->oneArray();
            $kodesatuan_besar = $this->db('kodesatuan')->where('kode_sat', $row['kode_satbesar'])->oneArray();
            $industrifarmasi = $this->db('industrifarmasi')->where('kode_industri', $row['kode_industri'])->oneArray();
            $kategori_barang = $this->db('kategori_barang')->where('kode', $row['kode_kategori'])->oneArray();
            $golongan_barang = $this->db('golongan_barang')->where('kode', $row['kode_golongan'])->oneArray();
            $row['nama_jenis'] = $jenis['nama'];
            $row['nama_satuan'] = $kodesatuan['satuan'];
            $row['nama_satuan_besar'] = $kodesatuan_besar['satuan'];
            $row['nama_industri'] = $industrifarmasi['nama_industri'];
            $row['nama_kategori'] = $kategori_barang['nama'];
            $row['nama_golongan'] = $golongan_barang['nama'];
            $return['list'][] = $row;
          }

          $return['halaman'] = $_POST['halaman'];
        } else {

          $rows = $this->db('databarang')
            ->desc('kode_brng')
            ->offset(0)
            ->limit($perpage)
            ->toArray();

          foreach ($rows as $row) {
            $jenis = $this->db('jenis')->where('kdjns', $row['kdjns'])->oneArray();
            $kodesatuan = $this->db('kodesatuan')->where('kode_sat', $row['kode_sat'])->oneArray();
            $kodesatuan_besar = $this->db('kodesatuan')->where('kode_sat', $row['kode_satbesar'])->oneArray();
            $industrifarmasi = $this->db('industrifarmasi')->where('kode_industri', $row['kode_industri'])->oneArray();
            $kategori_barang = $this->db('kategori_barang')->where('kode', $row['kode_kategori'])->oneArray();
            $golongan_barang = $this->db('golongan_barang')->where('kode', $row['kode_golongan'])->oneArray();
            $row['nama_jenis'] = $jenis['nama'];
            $row['nama_satuan'] = $kodesatuan['satuan'];
            $row['nama_satuan_besar'] = $kodesatuan_besar['satuan'];
            $row['nama_industri'] = $industrifarmasi['nama_industri'];
            $row['nama_kategori'] = $kategori_barang['nama'];
            $row['nama_golongan'] = $golongan_barang['nama'];
            $return['list'][] = $row;
          }

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
      return $this->db('databarang')->where('kode_brng', $_POST['kode_brng'])->delete();
    }

}
