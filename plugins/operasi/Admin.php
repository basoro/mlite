<?php
namespace Plugins\Operasi;

use Systems\AdminModule;
use Systems\MySQL;

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
      $master_berkas_digital = $this->core->mysql('master_berkas_digital')->toArray();
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
        $master_berkas_digital = $this->core->mysql('master_berkas_digital')->toArray();
        $this->_Display($tgl_masuk, $tgl_masuk_akhir);
        echo $this->draw('display.html', ['operasi' => $this->assign, 'cek_vclaim' => $cek_vclaim, 'master_berkas_digital' => $master_berkas_digital]);
        exit();
    }

    public function _Display($tgl_masuk='', $tgl_masuk_akhir='')
    {
        $this->_addHeaderFiles();

        $this->assign['kategori'] = $this->core->getEnum('operasi','kategori');
        $this->assign['paket_operasi'] = $this->core->mysql('paket_operasi')->where('status', '1')->toArray();
        $this->assign['dokter'] = $this->core->mysql('dokter')->toArray();
        $this->assign['petugas'] = $this->core->mysql('petugas')->toArray();
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

        $stmt = $this->core->mysql()->pdo()->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $this->assign['list'] = [];
        foreach ($rows as $row) {
          $paket_operasi = $this->core->mysql('paket_operasi')->where('kode_paket', $row['kode_paket'])->oneArray();
          $row['nm_perawatan'] = $paket_operasi['nm_perawatan'];
          $this->assign['list'][] = $row;
        }


        if (isset($_POST['no_rawat'])){
          $this->assign['operasi'] = $this->core->mysql('operasi')
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
      $this->assign['paket_operasi'] = $this->core->mysql('paket_operasi')->where('status', '1')->toArray();
      $this->assign['dokter'] = $this->core->mysql('dokter')->toArray();
      $this->assign['petugas'] = $this->core->mysql('petugas')->toArray();
      $this->assign['no_rawat'] = '';
      if (isset($_POST['no_rawat'])){
        $this->assign['operasi'] = $this->core->mysql('operasi')
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
      $paket_operasi = $this->core->mysql('paket_operasi')->where('kode_paket', $_POST['kode_paket'])->oneArray();
      if(!$this->core->mysql('operasi')->where('no_rawat', $_POST['no_rawat'])->where('kode_paket', $_POST['kode_paket'])->oneArray()) {
        $operasi = $this->core->mysql('operasi')->save([
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
        $operasi = $this->core->mysql('operasi')->where('no_rawat', $_POST['no_rawat'])->where('kode_paket', $_POST['kode_paket'])->update([
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
            (reg_periksa.no_rkm_medis LIKE ? OR reg_periksa.no_rawat LIKE ? OR pasien.nm_pasien LIKE ? OR reg_periksa.stts LIKE ? OR reg_periksa.stts LIKE ?)
          LIMIT 10";

        $stmt = $this->core->mysql()->pdo()->prepare($sql);
        $stmt->execute(['%'.$cari.'%', '%'.$cari.'%', '%'.$cari.'%', 'Belum', 'Dirawat']);
        $pasien = $stmt->fetchAll();

      }
      echo $this->draw('pasien.html', ['pasien' => $pasien]);
      exit();
    }

    public function anyRincian()
    {

      $rows_beri_obat_operasi = $this->core->mysql('beri_obat_operasi')
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
      $obat = $this->core->mysql('obatbhp_ok')
        ->like('nm_obat', '%'.$_POST['obat'].'%')
        ->limit(10)
        ->toArray();
      echo $this->draw('obat.html', ['obat' => $obat]);
      exit();
    }

    public function postHapus()
    {
      $this->core->mysql('operasi')->where('no_rawat', $_POST['no_rawat'])->delete();
      exit();
    }

    public function postSaveDetail()
    {
      $this->core->mysql('beri_obat_operasi')->save([
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
      $this->core->mysql('beri_obat_operasi')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('kd_obat', $_POST['kd_obat'])
        ->where('tanggal', $_POST['tanggal'])
        ->delete();
      exit();
    }

    public function getBookingOperasi()
    {
      return $this->draw('bookingoperasi.html');
    }

    public function getPaketOperasi()
    {
      return $this->draw('paketoperasi.html');
    }

    public function getObatOperasi()
    {
      return $this->draw('obatoperasi.html');
    }

    public function getLaporanOperasi()
    {
      return $this->draw('laporanoperasi.html');
    }

    public function convertNorawat($text)
    {
        setlocale(LC_ALL, 'en_EN');
        $text = str_replace('/', '', trim($text));
        return $text;
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
