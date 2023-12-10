<?php
namespace Plugins\Operasi;

use Systems\AdminModule;


class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Manage' => 'manage',
            'Pasien Operasi' => 'pasienoperasi',
            'Booking Operasi' => 'bookingoperasi',
            'Paket Operasi' => 'paketoperasi',
            'Obat Operasi' => 'obatoperasi',
            'Laporan Operasi' => 'laporanoperasi',
        ];
    }

    public function getManage()
    {
      $sub_modules = [
        ['name' => 'Pasien Operasi', 'url' => url([ADMIN, 'operasi', 'pasienoperasi']), 'icon' => 'cubes', 'desc' => 'Data pasien operasi'],
        ['name' => 'Booking Operasi', 'url' => url([ADMIN, 'operasi', 'bookingoperasi']), 'icon' => 'cubes', 'desc' => 'Data booking operasi'],
        ['name' => 'Paket Operasi', 'url' => url([ADMIN, 'operasi', 'paketoperasi']), 'icon' => 'cubes', 'desc' => 'Data paket operasi'],
        ['name' => 'Obat Operasi', 'url' => url([ADMIN, 'operasi', 'obatoperasi']), 'icon' => 'cubes', 'desc' => 'Data obat operasi'],
        ['name' => 'Laporan Operasi', 'url' => url([ADMIN, 'operasi', 'laporanoperasi']), 'icon' => 'cubes', 'desc' => 'Data laporan operasi'],
      ];
      return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
    }

    public function anyPasienOperasi()
    {
      $tgl_masuk = date('Y-m-d 00:00:00');
      $tgl_masuk_akhir = date('Y-m-d 23:59:59');

      if(isset($_POST['periode_rawat'])) {
        $tgl_masuk = $_POST['periode_rawat'];
      }
      if(isset($_POST['periode_rawat_akhir'])) {
        $tgl_masuk_akhir = $_POST['periode_rawat_akhir'];
      }
      $cek_vclaim = $this->db('mlite_modules')->where('dir', 'vclaim')->oneArray();
      $master_berkas_digital = $this->db('master_berkas_digital')->toArray();
      $this->_Display($tgl_masuk, $tgl_masuk_akhir);
      return $this->draw('pasienoperasi.html', ['operasi' => $this->assign, 'cek_vclaim' => $cek_vclaim, 'master_berkas_digital' => $master_berkas_digital]);
    }

    public function anyDisplay()
    {
        $tgl_masuk = date('Y-m-d 00:00:00');
        $tgl_masuk_akhir = date('Y-m-d 23:59:59');

        if(isset($_POST['periode_rawat'])) {
          $tgl_masuk = $_POST['periode_rawat'];
        }
        if(isset($_POST['periode_rawat_akhir'])) {
          $tgl_masuk_akhir = $_POST['periode_rawat_akhir'];
        }
        $cek_vclaim = $this->db('mlite_modules')->where('dir', 'vclaim')->oneArray();
        $master_berkas_digital = $this->db('master_berkas_digital')->toArray();
        $this->_Display($tgl_masuk, $tgl_masuk_akhir);
        echo $this->draw('display.html', ['operasi' => $this->assign, 'cek_vclaim' => $cek_vclaim, 'master_berkas_digital' => $master_berkas_digital]);
        exit();
    }

    public function _Display($tgl_masuk='', $tgl_masuk_akhir='')
    {
        $this->_addHeaderFiles();

        $this->assign['kategori'] = $this->core->getEnum('operasi','kategori');
        $this->assign['paket_operasi'] = $this->db('paket_operasi')->where('status', '1')->toArray();
        $this->assign['dokter'] = $this->db('dokter')->toArray();
        $this->assign['petugas'] = $this->db('petugas')->toArray();
        $this->assign['no_rawat'] = '';

        $bangsal = str_replace(",","','", $this->core->getUserInfo('cap', null, true));

        $sql = "SELECT
            operasi.*,
            reg_periksa.*,
            pasien.*,
            penjab.*
          FROM
            operasi,
            reg_periksa,
            pasien,
            penjab
          WHERE
            operasi.no_rawat=reg_periksa.no_rawat
          AND
            reg_periksa.no_rkm_medis=pasien.no_rkm_medis
          AND
            operasi.tgl_operasi BETWEEN '$tgl_masuk' AND '$tgl_masuk_akhir'
          AND
            reg_periksa.kd_pj=penjab.kd_pj";

        $stmt = $this->db()->pdo()->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $this->assign['list'] = [];
        foreach ($rows as $row) {
          $paket_operasi = $this->db('paket_operasi')->where('kode_paket', $row['kode_paket'])->oneArray();
          $row['nm_perawatan'] = $paket_operasi['nm_perawatan'];
          $this->assign['list'][] = $row;
        }


        if (isset($_POST['no_rawat'])){
          $this->assign['operasi'] = $this->db('operasi')
            ->join('reg_periksa', 'reg_periksa.no_rawat=operasi.no_rawat')
            ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
            ->where('operasi.no_rawat', $_POST['no_rawat'])
            ->oneArray();
        } else {
          $this->assign['operasi'] = [
            'tgl_operasi' => date('Y-m-d H:i:s'),
            'no_rkm_medis' => '',
            'nm_pasien' => '',
            'no_rawat' => '',
            'jenis_anasthesi' => '',
            'operator1' => '',
            'operator2' => '',
            'operator3' => '',
            'asisten_operator1' => '',
            'asisten_operator2' => '',
            'dokter_anak' => '',
            'perawaat_resusitas' => '',
            'dokter_anestesi' => '',
            'asisten_anestesi' => '',
            'bidan' => '',
            'perawat_luar' => '',
            'kode_paket' => '',
            'status' => ''
          ];
        }
    }

    public function anyForm()
    {

      $this->assign['kategori'] = $this->core->getEnum('operasi','kategori');
      $this->assign['paket_operasi'] = $this->db('paket_operasi')->where('status', '1')->toArray();
      $this->assign['dokter'] = $this->db('dokter')->toArray();
      $this->assign['petugas'] = $this->db('petugas')->toArray();
      $this->assign['no_rawat'] = '';
      if (isset($_POST['no_rawat'])){
        $this->assign['operasi'] = $this->db('operasi')
          ->join('reg_periksa', 'reg_periksa.no_rawat=operasi.no_rawat')
          ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
          ->where('operasi.no_rawat', $_POST['no_rawat'])
          ->oneArray();
        echo $this->draw('form.html', [
          'operasi' => $this->assign
        ]);
      } else {
        $this->assign['operasi'] = [
          'tgl_operasi' => date('Y-m-d H:i:s'),
          'no_rkm_medis' => '',
          'nm_pasien' => '',
          'no_rawat' => '',
          'jenis_anasthesi' => '',
          'operator1' => '',
          'operator2' => '',
          'operator3' => '',
          'asisten_operator1' => '',
          'asisten_operator2' => '',
          'dokter_anak' => '',
          'perawaat_resusitas' => '',
          'dokter_anestesi' => '',
          'asisten_anestesi' => '',
          'bidan' => '',
          'perawat_luar' => '',
          'kode_paket' => '',
          'status' => ''
        ];
        echo $this->draw('form.html', [
          'operasi' => $this->assign
        ]);
      }
      exit();
    }

    public function postSave()
    {
      $paket_operasi = $this->db('paket_operasi')->where('kode_paket', $_POST['kode_paket'])->oneArray();
      if(!$this->db('operasi')->where('no_rawat', $_POST['no_rawat'])->where('kode_paket', $_POST['kode_paket'])->oneArray()) {
        $operasi = $this->db('operasi')->save([
          'no_rawat' => $_POST['no_rawat'],
          'tgl_operasi' => $_POST['tgl_operasi'],
          'jenis_anasthesi' => $_POST['jenis_anasthesi'],
          'kategori' => $_POST['kategori'],
          'operator1' => $_POST['operator1'],
          'operator2' => $_POST['operator2'],
          'operator3' => $_POST['operator3'],
          'asisten_operator1' => $_POST['asisten_operator1'],
          'asisten_operator2' => $_POST['asisten_operator2'],
          'asisten_operator3' => '',
          'instrumen' => '',
          'dokter_anak' => $_POST['dokter_anak'],
          'perawaat_resusitas' => $_POST['perawaat_resusitas'],
          'dokter_anestesi' => $_POST['dokter_anestesi'],
          'asisten_anestesi' => $_POST['asisten_anestesi'],
          'asisten_anestesi2' => '',
          'bidan' => $_POST['bidan'],
          'bidan2' => '',
          'bidan3' => '',
          'perawat_luar' => $_POST['perawat_luar'],
          'omloop' => '',
          'omloop2' => '',
          'omloop3' => '',
          'omloop4' => '',
          'omloop5' => '',
          'dokter_pjanak' => '',
          'dokter_umum' => '',
          'kode_paket' => $_POST['kode_paket'],
          'biayaoperator1' => $paket_operasi['operator1'],
          'biayaoperator2' => $paket_operasi['operator2'],
          'biayaoperator3' => $paket_operasi['operator3'],
          'biayaasisten_operator1' => $paket_operasi['asisten_operator1'],
          'biayaasisten_operator2' => $paket_operasi['asisten_operator2'],
          'biayaasisten_operator3' => $paket_operasi['asisten_operator3'],
          'biayainstrumen' => $paket_operasi['instrumen'],
          'biayadokter_anak' => $paket_operasi['dokter_anak'],
          'biayaperawaat_resusitas' => $paket_operasi['perawaat_resusitas'],
          'biayadokter_anestesi' => $paket_operasi['dokter_anestesi'],
          'biayaasisten_anestesi' => $paket_operasi['asisten_anestesi'],
          'biayaasisten_anestesi2' => $paket_operasi['asisten_anestesi2'],
          'biayabidan' => $paket_operasi['bidan'],
          'biayabidan2' => $paket_operasi['bidan2'],
          'biayabidan3' => $paket_operasi['bidan3'],
          'biayaperawat_luar' => $paket_operasi['perawat_luar'],
          'biayaalat' => $paket_operasi['alat'],
          'biayasewaok' => $paket_operasi['sewa_ok'],
          'akomodasi' => $paket_operasi['akomodasi'],
          'bagian_rs' => $paket_operasi['bagian_rs'],
          'biaya_omloop' => $paket_operasi['omloop'],
          'biaya_omloop2' => $paket_operasi['omloop2'],
          'biaya_omloop3' => $paket_operasi['omloop3'],
          'biaya_omloop4' => $paket_operasi['omloop4'],
          'biaya_omloop5' => $paket_operasi['omloop5'],
          'biayasarpras' => $paket_operasi['sarpras'],
          'biaya_dokter_pjanak' => $paket_operasi['dokter_pjanak'],
          'biaya_dokter_umum' => $paket_operasi['dokter_umum'],
          'status' => $_POST['status']
        ]);
      } else {
        $operasi = $this->db('operasi')->where('no_rawat', $_POST['no_rawat'])->where('kode_paket', $_POST['kode_paket'])->update([
          'tgl_operasi' => $_POST['tgl_operasi'],
          'jenis_anasthesi' => $_POST['jenis_anasthesi'],
          'kategori' => $_POST['kategori'],
          'operator1' => $_POST['operator1'],
          'operator2' => $_POST['operator2'],
          'operator3' => $_POST['operator3'],
          'asisten_operator1' => $_POST['asisten_operator1'],
          'asisten_operator2' => $_POST['asisten_operator2'],
          'asisten_operator3' => '',
          'instrumen' => '',
          'dokter_anak' => $_POST['dokter_anak'],
          'perawaat_resusitas' => $_POST['perawaat_resusitas'],
          'dokter_anestesi' => $_POST['dokter_anestesi'],
          'asisten_anestesi' => $_POST['asisten_anestesi'],
          'asisten_anestesi2' => '',
          'bidan' => $_POST['bidan'],
          'bidan2' => '',
          'bidan3' => '',
          'perawat_luar' => $_POST['perawat_luar'],
          'omloop' => '',
          'omloop2' => '',
          'omloop3' => '',
          'omloop4' => '',
          'omloop5' => '',
          'dokter_pjanak' => '',
          'dokter_umum' => '',
          'biayaoperator1' => $paket_operasi['operator1'],
          'biayaoperator2' => $paket_operasi['operator2'],
          'biayaoperator3' => $paket_operasi['operator3'],
          'biayaasisten_operator1' => $paket_operasi['asisten_operator1'],
          'biayaasisten_operator2' => $paket_operasi['asisten_operator2'],
          'biayaasisten_operator3' => '',
          'biayainstrumen' => '',
          'biayadokter_anak' => $paket_operasi['dokter_anak'],
          'biayaperawaat_resusitas' => $paket_operasi['perawaat_resusitas'],
          'biayadokter_anestesi' => $paket_operasi['dokter_anestesi'],
          'biayaasisten_anestesi' => $paket_operasi['asisten_anestesi'],
          'biayaasisten_anestesi2' => '',
          'biayabidan' => $paket_operasi['bidan'],
          'biayabidan2' => '',
          'biayabidan3' => '',
          'biayaperawat_luar' => $paket_operasi['perawat_luar'],
          'biayaalat' => $paket_operasi['alat'],
          'biayasewaok' => $paket_operasi['sewa_ok'],
          'akomodasi' => '',
          'bagian_rs' => $paket_operasi['bagian_rs'],
          'biaya_omloop' => '',
          'biaya_omloop2' => '',
          'biaya_omloop3' => '',
          'biaya_omloop4' => '',
          'biaya_omloop5' => '',
          'biayasarpras' => '',
          'biaya_dokter_pjanak' => '',
          'biaya_dokter_umum' => '',
          'status' => $_POST['status']
        ]);
      }
      exit();
    }

    public function anyPasien()
    {
      $cari = $_POST['cari'];
      if(isset($_POST['cari'])) {
        $sql = "SELECT
            reg_periksa.no_rkm_medis,
            reg_periksa.no_rawat,
            pasien.nm_pasien
          FROM
            reg_periksa,
            pasien
          WHERE
            reg_periksa.no_rkm_medis=pasien.no_rkm_medis
          AND
            reg_periksa.no_rawat = '$cari'
          LIMIT 10";

        $stmt = $this->db()->pdo()->prepare($sql);
        $stmt->execute();
        $pasien = $stmt->fetchAll();

      }
      echo $this->draw('pasien.html', ['pasien' => $pasien]);
      exit();
    }

    public function anyRincian()
    {

      $rows_beri_obat_operasi = $this->db('beri_obat_operasi')
      ->join('obatbhp_ok', 'obatbhp_ok.kd_obat=beri_obat_operasi.kd_obat')
      ->where('no_rawat', $_POST['no_rawat'])
      ->toArray();

      $beri_obat_operasi = [];
      $no_obat = 1;
      foreach ($rows_beri_obat_operasi as $row) {
        $row['nomor'] = $no_obat++;
        $beri_obat_operasi[] = $row;
      }

      echo $this->draw('rincian.html', [
        'beri_obat_operasi' => $beri_obat_operasi,
        'no_rawat' => $_POST['no_rawat']
      ]);
      exit();
    }

    public function anyObat()
    {
      $obat = $this->db('obatbhp_ok')
        ->like('nm_obat', '%'.$_POST['obat'].'%')
        ->limit(10)
        ->toArray();
      echo $this->draw('obat.html', ['obat' => $obat]);
      exit();
    }

    public function postHapus()
    {
      $this->db('operasi')->where('no_rawat', $_POST['no_rawat'])->delete();
      exit();
    }

    public function postSaveDetail()
    {
      $this->db('beri_obat_operasi')->save([
        'no_rawat' => $_POST['no_rawat'],
        'tanggal' => date('Y-m-d H:i:s'),
        'kd_obat' => $_POST['kd_obat'],
        'hargasatuan' => $_POST['hargasatuan'],
        'jumlah' => $_POST['jumlah']
      ]);
      exit();
    }

    public function postHapusDetail()
    {
      $this->db('beri_obat_operasi')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('kd_obat', $_POST['kd_obat'])
        ->where('tanggal', $_POST['tanggal'])
        ->delete();
      exit();
    }

    public function getBookingOperasi()
    {
      $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
      $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
      $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
      $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
      $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
      $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));
      $status = $this->core->getEnum('booking_operasi','status');
      $dokter = $this->db('dokter')->where('status', '1')->toArray();
      $ruang_ok = $this->db('ruang_ok')->toArray();
      $paket_operasi = $this->db('paket_operasi')->toArray();
      $rows = $this->db('booking_operasi')->join('ruang_ok', 'ruang_ok.kd_ruang_ok=booking_operasi.kd_ruang_ok')->toArray();
      $booking_operasi = [];
      foreach ($rows as $row) {
        $row['nm_dokter'] = $this->core->getDokterInfo('nm_dokter', $row['kd_dokter']);
        $row['no_rkm_medis'] = $this->core->getRegPeriksaInfo('no_rkm_medis', $row['no_rawat']);
        $row['nm_pasien'] = $this->core->getPasienInfo('nm_pasien', $this->core->getRegPeriksaInfo('no_rkm_medis', $row['no_rawat']));
        $booking_operasi[] = $row;
      }
      return $this->draw('bookingoperasi.html', ['bookingoperasi' => $booking_operasi, 'status' => $status, 'dokter' => $dokter, 'ruang_ok' => $ruang_ok, 'paket_operasi' => $paket_operasi]);
    }

    public function postSaveBookingOperasi()
    {
      if($_POST['simpan']) {
        unset($_POST['simpan']);
        unset($_POST['no_rkm_medis']);
        unset($_POST['pasien']);
        unset($_POST['nm_pasien']);
        $this->db('booking_operasi')->save($_POST);
        $this->notify('success', 'Booking operasi telah disimpan');
      } else if ($_POST['update']) {
        $no_rawat = $_POST['no_rawat'];
        unset($_POST['update']);
        unset($_POST['no_rawat']);
        unset($_POST['no_rkm_medis']);
        unset($_POST['pasien']);
        unset($_POST['nm_pasien']);
        $this->db('booking_operasi')
          ->where('no_rawat', $no_rawat)
          ->save($_POST);
        $this->notify('failure', 'Booking operasi telah diubah');
      } else if ($_POST['hapus']) {
        $this->db('booking_operasi')
          ->where('no_rawat', $_POST['no_rawat'])
          ->delete();
        $this->notify('failure', 'Booking operasi telah dihapus');
      }
      redirect(url([ADMIN, 'operasi', 'bookingoperasi']));
    }

    public function getPaketOperasi()
    {
      $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
      $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
      $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
      $kategori = $this->core->getEnum('paket_operasi','kategori');
      $penjab = $this->db('penjab')->where('status', '1')->toArray();
      $kelas = $this->core->getEnum('paket_operasi','kelas');
      $rows_paketoperasi = $this->db('paket_operasi')->toArray();
      $paketoperasi = [];
      foreach ($rows_paketoperasi as $row) {
        $rows = $this->db('penjab')->where('kd_pj', $row['kd_pj'])->oneArray();
        $row['png_jawab'] = $rows['png_jawab'];
        $paketoperasi[] = $row;
      }
      return $this->draw('paketoperasi.html', ['paketoperasi' => $paketoperasi, 'kategori' => $kategori, 'penjab' => $penjab, 'kelas' => $kelas]);
    }

    public function postSavePaketOperasi()
    {
      if($_POST['simpan']) {
        unset($_POST['simpan']);
        $this->db('paket_operasi')->save($_POST);
        $this->notify('success', 'Paket operasi telah disimpan');
      } else if ($_POST['update']) {
        $kode_paket = $_POST['kode_paket'];
        unset($_POST['update']);
        unset($_POST['kode_paket']);
        $this->db('paket_operasi')
          ->where('kode_paket', $kode_paket)
          ->save($_POST);
        $this->notify('failure', 'Paket operasi telah diubah');
      } else if ($_POST['hapus']) {
        $this->db('paket_operasi')
          ->where('kode_paket', $_POST['kode_paket'])
          ->delete();
        $this->notify('failure', 'Paket operasi telah dihapus');
      }
      redirect(url([ADMIN, 'operasi', 'paketoperasi']));
    }

    public function getObatOperasi()
    {
      $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
      $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
      $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
      $satuan = $this->db('kodesatuan')->toArray();
      $obatoperasi = $this->db('obatbhp_ok')
        ->join('kodesatuan', 'kodesatuan.kode_sat=obatbhp_ok.kode_sat')
        ->toArray();
      return $this->draw('obatoperasi.html', ['obatoperasi' => $obatoperasi, 'satuan' => $satuan]);
    }

    public function postSaveObatOperasi()
    {
      if($_POST['simpan']) {
        unset($_POST['simpan']);
        $this->db('obatbhp_ok')->save($_POST);
        $this->notify('success', 'Obat operasi telah disimpan');
      } else if ($_POST['update']) {
        $kd_obat = $_POST['kd_obat'];
        unset($_POST['update']);
        unset($_POST['kd_obat']);
        $this->db('obatbhp_ok')
          ->where('kd_obat', $kd_obat)
          ->save($_POST);
        $this->notify('failure', 'Obat operasi telah diubah');
      } else if ($_POST['hapus']) {
        $this->db('obatbhp_ok')
          ->where('kd_obat', $_POST['kd_obat'])
          ->delete();
        $this->notify('failure', 'Obat operasi telah dihapus');
      }
      redirect(url([ADMIN, 'operasi', 'obatoperasi']));
    }

    public function getLaporanOperasi()
    {
      $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
      $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
      $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
      $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
      $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
      $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));
      $permintaan_pa = $this->core->getEnum('laporan_operasi','permintaan_pa');
      $rows = $this->db('laporan_operasi')->toArray();
      $laporanoperasi = [];
      foreach ($rows as $row) {
        $row['no_rkm_medis'] = $this->core->getRegPeriksaInfo('no_rkm_medis', $row['no_rawat']);
        $row['nm_pasien'] = $this->core->getPasienInfo('nm_pasien', $this->core->getRegPeriksaInfo('no_rkm_medis', $row['no_rawat']));
        $laporanoperasi[] = $row;
      }

      return $this->draw('laporanoperasi.html', ['laporanoperasi' => $laporanoperasi, 'permintaan_pa' => $permintaan_pa]);
    }

    public function postSaveLaporanOperasi()
    {
      if($_POST['simpan']) {
        unset($_POST['simpan']);
        unset($_POST['no_rkm_medis']);
        unset($_POST['pasien']);
        unset($_POST['nm_pasien']);
        $this->db('laporan_operasi')->save($_POST);
        $this->notify('success', 'Laporan operasi telah disimpan');
      } else if ($_POST['update']) {
        $no_rawat = $_POST['no_rawat'];
        unset($_POST['update']);
        unset($_POST['no_rawat']);
        unset($_POST['no_rkm_medis']);
        unset($_POST['pasien']);
        unset($_POST['nm_pasien']);
        $this->db('laporan_operasi')
          ->where('no_rawat', $no_rawat)
          ->save($_POST);
        $this->notify('failure', 'Laporan operasi telah diubah');
      } else if ($_POST['hapus']) {
        $this->db('laporan_operasi')
          ->where('no_rawat', $_POST['no_rawat'])
          ->delete();
        $this->notify('failure', 'Laporan operasi telah dihapus');
      }
      redirect(url([ADMIN, 'operasi', 'laporanoperasi']));
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/operasi/js/admin/operasi.js');
        exit();
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
        $this->core->addJS(url('assets/jscripts/lightbox/lightbox.min.js'));
        $this->core->addCSS(url('assets/jscripts/lightbox/lightbox.min.css'));
        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));
        $this->core->addJS(url([ADMIN, 'operasi', 'javascript']), 'footer');
    }


}

?>
