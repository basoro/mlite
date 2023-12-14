<?php

namespace Plugins\Inventaris;

use Systems\AdminModule;

class Admin extends AdminModule
{

  public function navigation()
  {
    return [
      'Manage' => 'manage',
    ];
  }

  public function getManage()
  {
    $this->_addHeaderFiles();
    $date_start = date('Y-m-d');
    $date_end = date('Y-m-d');
    if(isset_or($_GET['tgl_awal']) && isset_or($_GET['tgl_akhir'])){
      $date_start = $_GET['tgl_awal'];
      $date_end = $_GET['tgl_akhir'];
    }
    $aset = $this->db('inventaris')->toArray();

    $pemeliharaan = $this->db('pemeliharaan_inventaris')
      ->where('tanggal', '>=', $date_start)
      ->where('tanggal', '<=', $date_end)
      ->toArray();
    $rows = $this->db('permintaan_perbaikan_inventaris')
      ->join('pegawai', 'pegawai.nik=permintaan_perbaikan_inventaris.nik')
      ->where('tanggal', '>=', $date_start.' 00:00:00')
      ->where('tanggal', '<=', $date_end.' 23:59:59')
      ->toArray();
    $perbaikan = [];
    foreach ($rows as $row) {
      $perbaikan_inventaris = $this->db('perbaikan_inventaris')
        ->where('no_permintaan', $row['no_permintaan'])
        ->oneArray();
      $row['status_perbaikan'] = 'Belum';
      if($perbaikan_inventaris) {
        $row['status_perbaikan'] = 'Sudah';
      }
      $perbaikan[] = $row;
    }

    $peminjaman = $this->db('inventaris_peminjaman')
      ->where('tgl_pinjam', '>=', $date_start)
      ->where('tgl_pinjam', '<=', $date_end)
      ->toArray();

    return $this->draw('manage.html', ['tgl_awal' => $date_start, 'tgl_akhir' => $date_end, 'aset' => $aset, 'pemeliharaan' => $pemeliharaan, 'perbaikan' => $perbaikan, 'peminjaman' => $peminjaman]);
  }

  public function getDataAset()
  {
    $this->_addHeaderFiles();
    $rows = $this->db('inventaris')
      ->join('inventaris_barang', 'inventaris_barang.kode_barang=inventaris.kode_barang')
      ->join('inventaris_ruang', 'inventaris_ruang.id_ruang=inventaris.id_ruang')
      ->toArray();
    $inventaris = [];
    foreach ($rows as $row) {
      $row['tampil'] = url([ADMIN,'inventaris','asetdetail',$row['no_inventaris']]);
      $row['ubah'] = url([ADMIN,'inventaris','asetubah',$row['no_inventaris']]);
      $row['hapus'] = url([ADMIN,'inventaris','asethapus',$row['no_inventaris']]);
      $inventaris[] = $row;
    }
    $inventaris_barang = $this->db('inventaris_barang')->toArray();
    $inventaris_ruang = $this->db('inventaris_ruang')->toArray();
    return $this->draw('data.aset.html', ['inventaris' => $inventaris, 'inventaris_barang' => $inventaris_barang, 'inventaris_ruang' => $inventaris_ruang]);
  }

  public function postSaveAset()
  {
    if($_POST['simpan']) {
      unset($_POST['simpan']);
      $this->db('inventaris')->save($_POST);
      $this->notify('success', 'Data aset telah disimpan');
    } else if ($_POST['update']) {
      $no_inventaris = $_POST['no_inventaris'];
      unset($_POST['update']);
      unset($_POST['no_inventaris']);
      $this->db('inventaris')
        ->where('no_inventaris', $no_inventaris)
        ->save($_POST);
      $this->notify('failure', 'Data aset telah diubah');
    } else if ($_POST['hapus']) {
      $this->db('inventaris')
        ->where('no_inventaris', $_POST['no_inventaris'])
        ->delete();
      $this->notify('failure', 'Data aset telah dihapus');
    }
    redirect(url([ADMIN, 'inventaris', 'dataaset']));
  }

  public function getPemeliharaan()
  {
    $pemeliharaan = $this->db('pemeliharaan_inventaris')
      ->join('inventaris', 'inventaris.no_inventaris=pemeliharaan_inventaris.no_inventaris')
      ->join('inventaris_barang', 'inventaris_barang.kode_barang=inventaris.kode_barang')
      ->join('pegawai', 'pegawai.nik=pemeliharaan_inventaris.nip')
      ->toArray();
    return $this->draw('data.pemeliharaan.html', ['pemeliharaan' => $pemeliharaan]);
  }

  public function getPermintaanPerbaikan()
  {
    $this->_addHeaderFiles();
    $rows = $this->db('permintaan_perbaikan_inventaris')
      ->join('pegawai', 'pegawai.nik=permintaan_perbaikan_inventaris.nik')
      ->toArray();
    $perbaikan = [];
    foreach ($rows as $row) {
      $perbaikan_inventaris = $this->db('perbaikan_inventaris')
        ->where('no_permintaan', $row['no_permintaan'])
        ->oneArray();
      $row['status_perbaikan'] = 'Belum';
      if($perbaikan_inventaris) {
        $row['status_perbaikan'] = 'Sudah';
      }
      $row['tampil'] = url([ADMIN,'inventaris','permintaanperbaikandetail',$row['no_permintaan']]);
      $row['ubah'] = url([ADMIN,'inventaris','permintaanperbaikanubah',$row['no_permintaan']]);
      $row['hapus'] = url([ADMIN,'inventaris','permintaanperbaikanhapus',$row['no_permintaan']]);
      $perbaikan[] = $row;
    }
    return $this->draw('data.permintaan.perbaikan.html', ['perbaikan' => $perbaikan]);
  }

  public function getPermintaanPerbaikanBaru()
  {
    $this->_addHeaderFiles();

    $this->assign['form'] = [
      'no_permintaan' => $this->setNoPermintaan(),
      'no_inventaris' => '',
      'nik' => '',
      'tanggal' => '',
      'deskripsi_kerusakan' => ''
    ];

    $this->assign['aset'] = $this->db('inventaris')
      ->join('inventaris_barang', 'inventaris_barang.kode_barang=inventaris.kode_barang')
      ->toArray();
    $this->assign['pegawai'] = $this->db('pegawai')
      ->where('stts_aktif', 'AKTIF')
      ->toArray();
    return $this->draw('form.permintaan.perbaikan.html', ['permintaanperbaikan' => $this->assign]);
  }

  public function getPermintaanPerbaikanUbah($no_permintaan)
  {
    $this->assign['form'] = $this->db('permintaan_perbaikan_inventaris')
      ->where('no_permintaan', $no_permintaan)
      ->oneArray();
    $this->assign['aset'] = $this->db('inventaris')
      ->join('inventaris_barang', 'inventaris_barang.kode_barang=inventaris.kode_barang')
      ->toArray();
    $this->assign['pegawai'] = $this->db('pegawai')
      ->where('stts_aktif', 'AKTIF')
      ->toArray();
    return $this->draw('form.permintaan.perbaikan.html', ['permintaanperbaikan' => $this->assign]);
  }

  public function getPermintaanPerbaikanDetail($no_permintaan)
  {
    $permintaan_perbaikan_inventaris = $this->db('permintaan_perbaikan_inventaris')
      ->join('pegawai', 'pegawai.nik=permintaan_perbaikan_inventaris.nik')
      ->where('no_permintaan', $no_permintaan)
      ->oneArray();
    $perbaikandetail = $this->db('perbaikan_inventaris')
      ->join('pegawai', 'pegawai.nik=perbaikan_inventaris.nip')
      ->where('no_permintaan', $no_permintaan)
      ->oneArray();
    $perbaikan = url([ADMIN,'inventaris','perbaikan', $no_permintaan]);
    $perbaikanhapus = url([ADMIN,'inventaris','perbaikanhapus', $no_permintaan]);
    return $this->draw('data.permintaan.perbaikan.detail.html', ['permintaan_perbaikan_inventaris' => $permintaan_perbaikan_inventaris, 'perbaikandetail' => $perbaikandetail, 'perbaikan' => $perbaikan, 'perbaikanhapus' => $perbaikanhapus]);
  }

  public function postPermintaanPerbaikanSimpan($no_permintaan = null)
  {

    $errors = 0;

    // location to redirect
    if (!$no_permintaan) {
        $location = url([ADMIN, 'inventaris', 'permintaanperbaikanbaru']);
    } else {
        $location = url([ADMIN, 'inventaris', 'permintaanperbaikanubah', $no_permintaan]);
    }

    if (!$this->db('permintaan_perbaikan_inventaris')->where('no_permintaan', $no_permintaan)->oneArray()) {    // new
        $query = $this->db('permintaan_perbaikan_inventaris')->save(
          [
            'no_permintaan' => $_POST['no_permintaan'],
            'no_inventaris' => $_POST['no_inventaris'],
            'nik' => $_POST['nik'],
            'tanggal' => $_POST['tanggal'],
            'deskripsi_kerusakan' => $_POST['deskripsi_kerusakan']
          ]
        );
    } else {        // edit
        $query = $this->db('permintaan_perbaikan_inventaris')->where('no_permintaan', $no_permintaan)->save(
          [
            'no_inventaris' => $_POST['no_inventaris'],
            'nik' => $_POST['nik'],
            'tanggal' => $_POST['tanggal'],
            'deskripsi_kerusakan' => $_POST['deskripsi_kerusakan']
          ]
        );
    }

    if ($query) {
        $this->notify('success', 'Permintaan perbaikan inventaris berhasil disimpan.');
    } else {
        $this->notify('failure', 'Gagak menyimpan permintaan perbaikan inventaris.');
    }

    redirect($location, $_POST);
  }

  public function getPerbaikan($no_permintaan)
  {
    $this->_addHeaderFiles();

    if (!$this->db('perbaikan_inventaris')->where('no_permintaan', $no_permintaan)->oneArray()) {    // new
      $this->assign['form'] = [
        'no_permintaan' => $no_permintaan,
        'tanggal' => '',
        'uraian_kegiatan' => '',
        'nip' => '',
        'pelaksana' => '',
        'biaya' => '',
        'keterangan' => '',
        'status' => ''
      ];
    } else {
      $this->assign['form'] = $this->db('perbaikan_inventaris')->where('no_permintaan', $no_permintaan)->oneArray();
    }

    $this->assign['aset'] = $this->db('inventaris')
      ->join('inventaris_barang', 'inventaris_barang.kode_barang=inventaris.kode_barang')
      ->toArray();
    $this->assign['pegawai'] = $this->db('pegawai')
      ->where('stts_aktif', 'AKTIF')
      ->toArray();

    return $this->draw('form.perbaikan.html', ['perbaikan' => $this->assign]);
  }

  public function postPerbaikanSimpan($no_permintaan = null)
  {

    $errors = 0;

    $location = url([ADMIN, 'inventaris', 'permintaanperbaikandetail', $no_permintaan]);

    if (!$this->db('perbaikan_inventaris')->where('no_permintaan', $no_permintaan)->oneArray()) {    // new
        $query = $this->db('perbaikan_inventaris')->save(
          [
            'no_permintaan' => $no_permintaan,
            'tanggal' => $_POST['tanggal'],
            'uraian_kegiatan' => $_POST['uraian_kegiatan'],
            'nip' => $_POST['nip'],
            'pelaksana' => $_POST['pelaksana'],
            'biaya' => $_POST['biaya'],
            'keterangan' => $_POST['keterangan'],
            'status' => $_POST['status']
          ]
        );
    } else {        // edit
      $query = $this->db('perbaikan_inventaris')
        ->where('no_permintaan', $no_permintaan)
        ->save(
        [
          'tanggal' => $_POST['tanggal'],
          'uraian_kegiatan' => $_POST['uraian_kegiatan'],
          'nip' => $_POST['nip'],
          'pelaksana' => $_POST['pelaksana'],
          'biaya' => $_POST['biaya'],
          'keterangan' => $_POST['keterangan'],
          'status' => $_POST['status']
        ]
      );
    }

    if ($query) {
        $this->notify('success', 'Permintaan perbaikan inventaris berhasil disimpan.');
    } else {
        $this->notify('failure', 'Gagak menyimpan permintaan perbaikan inventaris.');
    }

    redirect($location, $_POST);
  }

  public function getPermintaanPerbaikanHapus($no_permintaan)
  {
    $this->db('permintaan_perbaikan_inventaris')->where('no_permintaan', $no_permintaan)->delete();
    redirect(url([ADMIN,'inventaris','permintaanperbaikan']));
  }

  public function getPerbaikanHapus($no_permintaan)
  {
    $this->db('perbaikan_inventaris')->where('no_permintaan', $no_permintaan)->delete();
    redirect(url([ADMIN,'inventaris','permintaanperbaikandetail', $no_permintaan]));
  }

  public function getPemeliharaanBaru()
  {
    $this->_addHeaderFiles();

    $this->assign['form'] = [
      'no_inventaris' => '',
      'tanggal' => '',
      'uraian_kegiatan' => '',
      'nip' => '',
      'pelaksana' => '',
      'biaya' => '',
      'jenis_pemeliharaan' => ''
    ];

    $this->assign['aset'] = $this->db('inventaris')
      ->join('inventaris_barang', 'inventaris_barang.kode_barang=inventaris.kode_barang')
      ->toArray();
    $this->assign['pegawai'] = $this->db('pegawai')
      ->where('stts_aktif', 'AKTIF')
      ->toArray();

    return $this->draw('form.pemeliharaan.html', ['pemeliharaan' => $this->assign]);
  }

  public function postPemeliharaanSimpan($no_inventaris = null)
  {

    $errors = 0;

    $location = url([ADMIN, 'inventaris', 'pemeliharaan']);

    if (!$this->db('pemeliharaan_inventaris')->where('no_inventaris', $no_inventaris)->oneArray()) {    // new
        $query = $this->db('pemeliharaan_inventaris')->save(
          [
            'no_inventaris' => $_POST['no_inventaris'],
            'tanggal' => $_POST['tanggal'],
            'uraian_kegiatan' => $_POST['uraian_kegiatan'],
            'nip' => $_POST['nip'],
            'pelaksana' => $_POST['pelaksana'],
            'biaya' => $_POST['biaya'],
            'jenis_pemeliharaan' => $_POST['jenis_pemeliharaan']
          ]
        );
    } else {        // edit
      $query = $this->db('pemeliharaan_inventaris')
        ->where('no_inventaris', $no_inventaris)
        ->save(
        [
          'tanggal' => $_POST['tanggal'],
          'uraian_kegiatan' => $_POST['uraian_kegiatan'],
          'nip' => $_POST['nip'],
          'pelaksana' => $_POST['pelaksana'],
          'biaya' => $_POST['biaya'],
          'jenis_pemeliharaan' => $_POST['jenis_pemeliharaan']
        ]
      );
    }

    if ($query) {
        $this->notify('success', 'Pemeliharaan inventaris berhasil disimpan.');
    } else {
        $this->notify('failure', 'Gagak menyimpan pemeliharaan inventaris.');
    }

    redirect($location, $_POST);
  }

  public function getPemeliharaanHapus($no_permintaan)
  {
    $this->db('perbaikan_inventaris')->where('no_permintaan', $no_permintaan)->delete();
    redirect(url([ADMIN,'inventaris','permintaanperbaikandetail', $no_permintaan]));
  }

  public function setNoInventaris()
  {
      $last_no_order = $this->db()->pdo()->prepare("SELECT ifnull(MAX(CONVERT(RIGHT(no_inventaris,4),signed)),0) FROM inventaris");
      $last_no_order->execute();
      $last_no_order = $last_no_order->fetch();
      if(empty($last_no_order[0])) {
        $last_no_order[0] = '0000';
      }
      $next_no_order = sprintf('%04s', ($last_no_order[0] + 1));
      $next_no_order = 'IN'.date('Ymd').''.$next_no_order;

      return $next_no_order;
  }

  public function setNoPermintaan()
  {
      $date = date('Y-m-d');
      $last_no_order = $this->db()->pdo()->prepare("SELECT ifnull(MAX(CONVERT(RIGHT(no_permintaan,4),signed)),0) FROM permintaan_perbaikan_inventaris WHERE tanggal LIKE '%$date%'");
      $last_no_order->execute();
      $last_no_order = $last_no_order->fetch();
      if(empty($last_no_order[0])) {
        $last_no_order[0] = '0000';
      }
      $next_no_order = sprintf('%04s', ($last_no_order[0] + 1));
      $next_no_order = 'PI'.date('Ymd').''.$next_no_order;

      return $next_no_order;
  }

  public function getInventarisBarang()
  {
    $this->_addHeaderFiles();
    $inventaris_barang = $this->db('inventaris_barang')
      ->join('inventaris_produsen', 'inventaris_produsen.kode_produsen=inventaris_barang.kode_produsen')
      ->join('inventaris_merk', 'inventaris_merk.id_merk=inventaris_barang.id_merk')
      ->join('inventaris_kategori', 'inventaris_kategori.id_kategori=inventaris_barang.id_kategori')
      ->join('inventaris_jenis', 'inventaris_jenis.id_jenis=inventaris_barang.id_jenis')
      ->toArray();
    $produsen = $this->db('inventaris_produsen')->toArray();
    $merk = $this->db('inventaris_merk')->toArray();
    $kategori = $this->db('inventaris_kategori')->toArray();
    $jenis = $this->db('inventaris_jenis')->toArray();
    return $this->draw('data.barang.html', ['inventaris_barang' => $inventaris_barang, 'produsen' => $produsen, 'merk' => $merk, 'kategori' => $kategori, 'jenis' => $jenis]);
  }

  public function postSaveInventarisBarang()
  {
    if($_POST['simpan']) {
      unset($_POST['simpan']);
      $this->db('inventaris_barang')->save($_POST);
      $this->notify('success', 'Data barang telah disimpan');
    } else if ($_POST['update']) {
      $kode_barang = $_POST['kode_barang'];
      unset($_POST['update']);
      unset($_POST['kode_barang']);
      $this->db('inventaris_barang')
        ->where('kode_barang', $kode_barang)
        ->save($_POST);
      $this->notify('failure', 'Data barang telah diubah');
    } else if ($_POST['hapus']) {
      $this->db('inventaris_barang')
        ->where('kode_barang', $_POST['kode_barang'])
        ->delete();
      $this->notify('failure', 'Data barang telah dihapus');
    }
    redirect(url([ADMIN, 'inventaris', 'inventarisbarang']));
  }

  public function getInventarisJenis()
  {
    $this->_addHeaderFiles();
    $inventaris_jenis = $this->db('inventaris_jenis')->toArray();
    return $this->draw('data.jenis.html', ['inventaris_jenis' => $inventaris_jenis]);
  }

  public function postSaveInventarisJenis()
  {
    if($_POST['simpan']) {
      unset($_POST['simpan']);
      $this->db('inventaris_jenis')->save($_POST);
      $this->notify('success', 'Data jenis barang telah disimpan');
    } else if ($_POST['update']) {
      $id_jenis = $_POST['id_jenis'];
      unset($_POST['update']);
      unset($_POST['id_jenis']);
      $this->db('inventaris_jenis')
        ->where('id_jenis', $id_jenis)
        ->save($_POST);
      $this->notify('failure', 'Data jenis barang telah diubah');
    } else if ($_POST['hapus']) {
      $this->db('inventaris_jenis')
        ->where('id_jenis', $_POST['id_jenis'])
        ->delete();
      $this->notify('failure', 'Data jenis barang telah dihapus');
    }
    redirect(url([ADMIN, 'inventaris', 'inventarisjenis']));
  }

  public function getInventarisKategori()
  {
    $this->_addHeaderFiles();
    $inventaris_kategori = $this->db('inventaris_kategori')->toArray();
    return $this->draw('data.kategori.html', ['inventaris_kategori' => $inventaris_kategori]);
  }

  public function postSaveInventarisKategori()
  {
    if($_POST['simpan']) {
      unset($_POST['simpan']);
      $this->db('inventaris_kategori')->save($_POST);
      $this->notify('success', 'Data kategori barang telah disimpan');
    } else if ($_POST['update']) {
      $id_kategori = $_POST['id_kategori'];
      unset($_POST['update']);
      unset($_POST['id_kategori']);
      $this->db('inventaris_kategori')
        ->where('id_kategori', $id_kategori)
        ->save($_POST);
      $this->notify('failure', 'Data kategori barang telah diubah');
    } else if ($_POST['hapus']) {
      $this->db('inventaris_kategori')
        ->where('id_kategori', $_POST['id_kategori'])
        ->delete();
      $this->notify('failure', 'Data kategori barang telah dihapus');
    }
    redirect(url([ADMIN, 'inventaris', 'inventariskategori']));
  }

  public function getInventarisMerk()
  {
    $this->_addHeaderFiles();
    $inventaris_merk = $this->db('inventaris_merk')->toArray();
    return $this->draw('data.merk.html', ['inventaris_merk' => $inventaris_merk]);
  }

  public function postSaveInventarisMerk()
  {
    if($_POST['simpan']) {
      unset($_POST['simpan']);
      $this->db('inventaris_merk')->save($_POST);
      $this->notify('success', 'Data merk barang telah disimpan');
    } else if ($_POST['update']) {
      $id_merk = $_POST['id_merk'];
      unset($_POST['update']);
      unset($_POST['id_merk']);
      $this->db('inventaris_merk')
        ->where('id_merk', $id_merk)
        ->save($_POST);
      $this->notify('failure', 'Data merk barang telah diubah');
    } else if ($_POST['hapus']) {
      $this->db('inventaris_merk')
        ->where('id_merk', $_POST['id_merk'])
        ->delete();
      $this->notify('failure', 'Data merk barang telah dihapus');
    }
    redirect(url([ADMIN, 'inventaris', 'inventarismerk']));
  }

  public function getInventarisProdusen()
  {
    $this->_addHeaderFiles();
    $inventaris_produsen = $this->db('inventaris_produsen')->toArray();
    return $this->draw('data.produsen.html', ['inventaris_produsen' => $inventaris_produsen]);
  }

  public function postSaveInventarisProdusen()
  {
    if($_POST['simpan']) {
      unset($_POST['simpan']);
      $this->db('inventaris_produsen')->save($_POST);
      $this->notify('success', 'Data produsen barang telah disimpan');
    } else if ($_POST['update']) {
      $kode_produsen = $_POST['kode_produsen'];
      unset($_POST['update']);
      unset($_POST['kode_produsen']);
      $this->db('inventaris_produsen')
        ->where('kode_produsen', $kode_produsen)
        ->save($_POST);
      $this->notify('failure', 'Data produsen barang telah diubah');
    } else if ($_POST['hapus']) {
      $this->db('inventaris_produsen')
        ->where('kode_produsen', $_POST['kode_produsen'])
        ->delete();
      $this->notify('failure', 'Data produsen barang telah dihapus');
    }
    redirect(url([ADMIN, 'inventaris', 'inventarisprodusen']));
  }

  public function getInventarisRuang()
  {
    $this->_addHeaderFiles();
    $inventaris_ruang = $this->db('inventaris_ruang')->toArray();
    return $this->draw('data.ruang.html', ['inventaris_ruang' => $inventaris_ruang]);
  }

  public function postSaveInventarisRuang()
  {
    if($_POST['simpan']) {
      unset($_POST['simpan']);
      $this->db('inventaris_ruang')->save($_POST);
      $this->notify('success', 'Data ruang barang telah disimpan');
    } else if ($_POST['update']) {
      $id_ruang = $_POST['id_ruang'];
      unset($_POST['update']);
      unset($_POST['id_ruang']);
      $this->db('inventaris_ruang')
        ->where('id_ruang', $id_ruang)
        ->save($_POST);
      $this->notify('failure', 'Data ruang barang telah diubah');
    } else if ($_POST['hapus']) {
      $this->db('inventaris_ruang')
        ->where('id_ruang', $_POST['id_ruang'])
        ->delete();
      $this->notify('failure', 'Data ruang barang telah dihapus');
    }
    redirect(url([ADMIN, 'inventaris', 'inventarisruang']));
  }

  public function getPeminjaman()
  {
    $this->_addHeaderFiles();
    $inventaris_peminjaman = $this->db('inventaris_peminjaman')
      ->join('inventaris', 'inventaris.no_inventaris=inventaris_peminjaman.no_inventaris')
      ->join('inventaris_barang', 'inventaris_barang.kode_barang=inventaris.kode_barang')
      ->join('pegawai', 'pegawai.nik=inventaris_peminjaman.nip')
      ->toArray();
    $inventaris = $this->db('inventaris')
      ->join('inventaris_barang', 'inventaris_barang.kode_barang=inventaris.kode_barang')
      ->toArray();
    $pegawai = $this->db('pegawai')->toArray();
    return $this->draw('data.peminjaman.html', ['inventaris_peminjaman' => $inventaris_peminjaman, 'inventaris' => $inventaris, 'pegawai' => $pegawai]);
  }

  public function postSavePeminjaman()
  {
    if($_POST['simpan']) {
      unset($_POST['simpan']);
      $this->db('inventaris_peminjaman')->save($_POST);
      $this->notify('success', 'Data peminjaman barang telah disimpan');
    } else if ($_POST['update']) {
      $peminjam = $_POST['peminjam'];
      $no_inventaris = $_POST['no_inventaris'];
      $tgl_pinjam = $_POST['tgl_pinjam'];
      unset($_POST['update']);
      unset($_POST['peminjam']);
      unset($_POST['no_inventaris']);
      unset($_POST['tgl_pinjam']);
      $this->db('inventaris_peminjaman')
        ->where('peminjam', $peminjam)
        ->where('no_inventaris', $no_inventaris)
        ->where('tgl_pinjam', $tgl_pinjam)
        ->save($_POST);
      $this->notify('failure', 'Data ruang barang telah diubah');
    } else if ($_POST['hapus']) {
      $this->db('inventaris_peminjaman')
        ->where('peminjam', $_POST['peminjam'])
        ->where('no_inventaris', $no_inventaris)
        ->where('tgl_pinjam', $tgl_pinjam)
        ->delete();
      $this->notify('failure', 'Data ruang barang telah dihapus');
    }
    redirect(url([ADMIN, 'inventaris', 'peminjaman']));
  }

  public function getCss()
  {
      header('Content-type: text/css');
      echo $this->draw(MODULES.'/inventaris/css/admin/inventaris.css');
      exit();
  }

  public function getJavascript()
  {
      header('Content-type: text/javascript');
      echo $this->draw(MODULES.'/inventaris/js/admin/inventaris.js');
      exit();
  }

  private function _addHeaderFiles()
  {
      $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
      $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
      $this->core->addCSS(url([ADMIN, 'inventaris', 'css']));
      $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
      $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
      $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
      $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));
      $this->core->addJS(url([ADMIN, 'inventaris', 'javascript']), 'footer');
  }

}

?>
