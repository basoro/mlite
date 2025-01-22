<?php
namespace Plugins\Apotek_Ralan;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Kelola'   => 'manage',
        ];
    }

    public function anyManage()
    {
        $tgl_kunjungan = date('Y-m-d');
        $tgl_kunjungan_akhir = date('Y-m-d');
        $status_periksa = '';

        if(isset($_POST['periode_rawat_jalan'])) {
          $tgl_kunjungan = $_POST['periode_rawat_jalan'];
        }
        if(isset($_POST['periode_rawat_jalan_akhir'])) {
          $tgl_kunjungan_akhir = $_POST['periode_rawat_jalan_akhir'];
        }
        if(isset($_POST['status_periksa'])) {
          $status_periksa = $_POST['status_periksa'];
        }
        $cek_vclaim = $this->db('mlite_modules')->where('dir', 'vclaim')->oneArray();
        $this->_Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa);
        return $this->draw('manage.html', ['rawat_jalan' => $this->assign, 'cek_vclaim' => $cek_vclaim]);
    }

    public function anyDisplay()
    {
        $tgl_kunjungan = date('Y-m-d');
        $tgl_kunjungan_akhir = date('Y-m-d');
        $status_periksa = '';

        if(isset($_POST['periode_rawat_jalan'])) {
          $tgl_kunjungan = $_POST['periode_rawat_jalan'];
        }
        if(isset($_POST['periode_rawat_jalan_akhir'])) {
          $tgl_kunjungan_akhir = $_POST['periode_rawat_jalan_akhir'];
        }
        if(isset($_POST['status_periksa'])) {
          $status_periksa = $_POST['status_periksa'];
        }
        $cek_vclaim = $this->db('mlite_modules')->where('dir', 'vclaim')->oneArray();
        $this->_Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa);
        echo $this->draw('display.html', ['rawat_jalan' => $this->assign, 'cek_vclaim' => $cek_vclaim]);
        exit();
    }

    public function _Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa='')
    {
        $this->_addHeaderFiles();

        $this->assign['poliklinik']     = $this->db('poliklinik')->where('status', '1')->toArray();
        $this->assign['dokter']         = $this->db('dokter')->where('status', '1')->toArray();
        $this->assign['penjab']       = $this->db('penjab')->where('status', '1')->toArray();
        $this->assign['no_rawat'] = '';
        $this->assign['no_reg']     = '';
        $this->assign['tgl_registrasi']= date('Y-m-d');
        $this->assign['jam_reg']= date('H:i:s');

        $sql = "SELECT reg_periksa.*,
            pasien.*,
            dokter.*,
            poliklinik.*,
            penjab.*
          FROM reg_periksa, pasien, dokter, poliklinik, penjab
          WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis
          AND reg_periksa.tgl_registrasi BETWEEN '$tgl_kunjungan' AND '$tgl_kunjungan_akhir'
          AND reg_periksa.kd_dokter = dokter.kd_dokter
          AND reg_periksa.kd_poli = poliklinik.kd_poli
          AND reg_periksa.kd_pj = penjab.kd_pj";

        if($status_periksa == 'belum') {
          $sql .= " AND reg_periksa.stts = 'Belum'";
        }
        if($status_periksa == 'selesai') {
          $sql .= " AND reg_periksa.stts = 'Sudah'";
        }
        if($status_periksa == 'lunas') {
          $sql .= " AND reg_periksa.status_bayar = 'Sudah Bayar'";
        }

        $stmt = $this->db()->pdo()->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $this->assign['list'] = [];
        foreach ($rows as $row) {
          $this->assign['list'][] = $row;
        }

    }

    public function postSaveDetail()
    {

      if($_POST['kat'] == 'obat') {
        $get_gudangbarang = $this->db('gudangbarang')->where('kode_brng', $_POST['kd_jenis_prw'])->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))->oneArray();
        $get_databarang = $this->db('databarang')->where('kode_brng', $_POST['kd_jenis_prw'])->oneArray();

        $this->db('gudangbarang')
          ->where('kode_brng', $_POST['kd_jenis_prw'])
          ->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))
          ->update([
            'stok' => $get_gudangbarang['stok'] - $_POST['jml']
          ]);

        $this->db('riwayat_barang_medis')
          ->save([
            'kode_brng' => $_POST['kd_jenis_prw'],
            'stok_awal' => $get_gudangbarang['stok'],
            'masuk' => '0',
            'keluar' => $_POST['jml'],
            'stok_akhir' => $get_gudangbarang['stok'] - $_POST['jml'],
            'posisi' => 'Pemberian Obat',
            'tanggal' => $_POST['tgl_perawatan'],
            'jam' => $_POST['jam_rawat'],
            'petugas' => $this->core->getUserInfo('fullname', null, true),
            'kd_bangsal' => $this->settings->get('farmasi.deporalan'),
            'status' => 'Simpan',
            'no_batch' => $get_gudangbarang['no_batch'],
            'no_faktur' => $get_gudangbarang['no_faktur'],
            'keterangan' => $_POST['no_rawat'] . ' ' . $this->core->getRegPeriksaInfo('no_rkm_medis', $_POST['no_rawat']) . ' ' . $this->core->getPasienInfo('nm_pasien', $this->core->getRegPeriksaInfo('no_rkm_medis', $_POST['no_rawat']))
          ]);

        $this->db('detail_pemberian_obat')
          ->save([
            'tgl_perawatan' => $_POST['tgl_perawatan'],
            'jam' => $_POST['jam_rawat'],
            'no_rawat' => $_POST['no_rawat'],
            'kode_brng' => $_POST['kd_jenis_prw'],
            'h_beli' => $_POST['biaya'],
            'biaya_obat' => $_POST['biaya'],
            'jml' => $_POST['jml'],
            'embalase' => '0',
            'tuslah' => '0',
            'total' => $_POST['biaya'] * $_POST['jml'],
            'status' => 'Ralan',
            'kd_bangsal' => $this->settings->get('farmasi.deporalan'),
            'no_batch' => $get_gudangbarang['no_batch'],
            'no_faktur' => $get_gudangbarang['no_faktur']
          ]);

        $this->db('aturan_pakai')
          ->save([
            'tgl_perawatan' => $_POST['tgl_perawatan'],
            'jam' => $_POST['jam_rawat'],
            'no_rawat' => $_POST['no_rawat'],
            'kode_brng' => $_POST['kd_jenis_prw'],
            'aturan' => $_POST['aturan_pakai']
          ]);
      }
      if($_POST['kat'] == 'racikan') {
        $no_racik = $this->db('obat_racikan')->where('no_rawat', $_POST['no_rawat'])->where('tgl_perawatan', $_POST['tgl_perawatan'])->count();
        $no_racik = $no_racik+1;
        $this->db('obat_racikan')
          ->save([
            'tgl_perawatan' => $_POST['tgl_perawatan'],
            'jam' => $_POST['jam_rawat'],
            'no_rawat' => $_POST['no_rawat'],
            'no_racik' => $no_racik,
            'nama_racik' => $_POST['nama_racik'],
            'kd_racik' => $_POST['kd_jenis_prw'],
            'jml_dr' => $_POST['jml'],
            'aturan_pakai' => $_POST['aturan_pakai'],
            'keterangan' => $_POST['keterangan']
          ]);
        $_POST['kode_brng'] = json_decode($_POST['kode_brng'], true);
        $_POST['kandungan'] = json_decode($_POST['kandungan'], true);
        for ($i = 0; $i < count($_POST['kode_brng']); $i++) {
          $get_gudangbarang = $this->db('gudangbarang')->where('kode_brng', $_POST['kode_brng'][$i]['value'])->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))->oneArray();
          $kapasitas = $this->db('databarang')->where('kode_brng', $_POST['kode_brng'][$i]['value'])->oneArray();
          $jml = $_POST['jml']*$_POST['kandungan'][$i]['value'];
          $jml = round(($jml/$kapasitas['kapasitas']),1);

          $this->db('gudangbarang')
          ->where('kode_brng', $_POST['kode_brng'][$i]['value'])
          ->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))
          ->update([
            'stok' => $get_gudangbarang['stok'] - $jml
          ]);

          $this->db('riwayat_barang_medis')
            ->save([
              'kode_brng' => $_POST['kode_brng'][$i]['value'],
              'stok_awal' => $get_gudangbarang['stok'],
              'masuk' => '0',
              'keluar' => $jml,
              'stok_akhir' => $get_gudangbarang['stok'] - $jml,
              'posisi' => 'Pemberian Obat',
              'tanggal' => $_POST['tgl_perawatan'],
              'jam' => $_POST['jam_rawat'],
              'petugas' => $this->core->getUserInfo('fullname', null, true),
              'kd_bangsal' => $this->settings->get('farmasi.deporalan'),
              'status' => 'Simpan',
              'no_batch' => $get_gudangbarang['no_batch'],
              'no_faktur' => $get_gudangbarang['no_faktur'],
              'keterangan' => $_POST['no_rawat'] . ' ' . $this->core->getRegPeriksaInfo('no_rkm_medis', $_POST['no_rawat']) . ' ' . $this->core->getPasienInfo('nm_pasien', $this->core->getRegPeriksaInfo('no_rkm_medis', $_POST['no_rawat']))
            ]);

          $this->db('detail_pemberian_obat')
            ->save([
              'tgl_perawatan' => $_POST['tgl_perawatan'],
              'jam' => $_POST['jam_rawat'],
              'no_rawat' => $_POST['no_rawat'],
              'kode_brng' => $_POST['kode_brng'][$i]['value'],
              'h_beli' => $kapasitas['h_beli'],
              'biaya_obat' => $kapasitas['dasar'],
              'jml' => $jml,
              'embalase' => '0',
              'tuslah' => '0',
              'total' => $kapasitas['dasar'] * $jml,
              'status' => 'Ralan',
              'kd_bangsal' => $this->settings->get('farmasi.deporalan'),
              'no_batch' => $get_gudangbarang['no_batch'],
              'no_faktur' => $get_gudangbarang['no_faktur']
            ]);          

        }        
      }
      exit();
    }

    public function postValidasiResep()
    {
      $tgl_rawat = date('Y-m-d');
      $jam_rawat = date('H:i:s');
      if($_POST['penyerahan'] == 'penyerahan') {
        $this->db('resep_obat')->where('no_resep', $_POST['no_resep'])->save(['tgl_penyerahan' => $tgl_rawat, 'jam_penyerahan' => $jam_rawat]);
      } else {
        $get_resep_dokter_nonracikan = $this->db('resep_dokter')
          ->select([
              'kode_brng' => 'kode_brng',
              'jml' => 'jml',
              'aturan_pakai' => 'aturan_pakai'
            ])
          ->where('no_resep', $_POST['no_resep'])
          ->toArray();
        $get_resep_dokter_racikan = $this->db('resep_dokter_racikan')
          ->select([
              'kode_brng' => 'kode_brng',
              'jml' => 'jml',
              'aturan_pakai' => 'aturan_pakai'
            ])
          ->join('resep_dokter_racikan_detail', 'resep_dokter_racikan_detail.no_resep=resep_dokter_racikan.no_resep')
          ->where('resep_dokter_racikan.no_resep', $_POST['no_resep'])
          // ->where('resep_dokter_racikan.no_racik=resep_dokter_racikan_detail.no_racik')
          ->toArray();
        $get_resep_dokter = array_merge($get_resep_dokter_nonracikan, $get_resep_dokter_racikan);
        foreach ($get_resep_dokter as $item) {

          $get_gudangbarang = $this->db('gudangbarang')->where('kode_brng', $item['kode_brng'])->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))->oneArray();
          $get_databarang = $this->db('databarang')->where('kode_brng', $item['kode_brng'])->oneArray();

          $this->db('gudangbarang')
            ->where('kode_brng', $item['kode_brng'])
            ->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))
            ->update([
              'stok' => $get_gudangbarang['stok'] - $item['jml']
            ]);

          $this->db('riwayat_barang_medis')
            ->save([
              'kode_brng' => $item['kode_brng'],
              'stok_awal' => $get_gudangbarang['stok'],
              'masuk' => '0',
              'keluar' => $item['jml'],
              'stok_akhir' => $get_gudangbarang['stok'] - $item['jml'],
              'posisi' => 'Pemberian Obat',
              'tanggal' => $tgl_rawat,
              'jam' => $jam_rawat,
              'petugas' => $this->core->getUserInfo('fullname', null, true),
              'kd_bangsal' => $this->settings->get('farmasi.deporalan'),
              'status' => 'Simpan',
              'no_batch' => $get_gudangbarang['no_batch'],
              'no_faktur' => $get_gudangbarang['no_faktur'],
              'keterangan' => $_POST['no_rawat'] . ' ' . $this->core->getRegPeriksaInfo('no_rkm_medis', $_POST['no_rawat']) . ' ' . $this->core->getPasienInfo('nm_pasien', $this->core->getRegPeriksaInfo('no_rkm_medis', $_POST['no_rawat']))
            ]);

          $this->db('detail_pemberian_obat')
            ->save([
              'tgl_perawatan' => $tgl_rawat,
              'jam' => $jam_rawat,
              'no_rawat' => $_POST['no_rawat'],
              'kode_brng' => $item['kode_brng'],
              'h_beli' => $get_databarang['h_beli'],
              'biaya_obat' => $get_databarang['dasar'],
              'jml' => $item['jml'],
              'embalase' => '0',
              'tuslah' => '0',
              'total' => $get_databarang['dasar'] * $item['jml'],
              'status' => 'Ralan',
              'kd_bangsal' => $this->settings->get('farmasi.deporalan'),
              'no_batch' => $get_gudangbarang['no_batch'],
              'no_faktur' => $get_gudangbarang['no_faktur']
            ]);

          $this->db('aturan_pakai')
            ->save([
              'tgl_perawatan' => $tgl_rawat,
              'jam' => $jam_rawat,
              'no_rawat' => $_POST['no_rawat'],
              'kode_brng' => $item['kode_brng'],
              'aturan' => $item['aturan_pakai']
            ]);

        }

        $resep_dokter_racikan = $this->db('resep_dokter_racikan')->where('no_resep', $_POST['no_resep'])->oneArray();

        if(!empty($resep_dokter_racikan)) {
          $this->db('obat_racikan')->save(
            [
                'tgl_perawatan' => $tgl_rawat,
                'jam' => $jam_rawat,
                'no_rawat' => $_POST['no_rawat'],
                'no_racik' => $resep_dokter_racikan['no_racik'],
                'nama_racik' => $resep_dokter_racikan['nama_racik'],
                'kd_racik' => $resep_dokter_racikan['kd_racik'],
                'jml_dr' => $resep_dokter_racikan['jml_dr'],
                'aturan_pakai' => $resep_dokter_racikan['aturan_pakai'],
                'keterangan' => $resep_dokter_racikan['keterangan']
            ]
          );
        }
        $this->db('resep_obat')->where('no_resep', $_POST['no_resep'])->save(['tgl_perawatan' => $tgl_rawat, 'jam' => $jam_rawat]);
      }
      exit();
    }

    public function postHapusResep()
    {
      if(isset($_POST['kd_jenis_prw'])) {
        $this->db('resep_dokter')
        ->where('no_resep', $_POST['no_resep'])
        ->where('kode_brng', $_POST['kd_jenis_prw'])
        ->delete();
      } else {
        $this->db('resep_obat')
        ->where('no_resep', $_POST['no_resep'])
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('tgl_peresepan', $_POST['tgl_peresepan'])
        ->where('jam_peresepan', $_POST['jam_peresepan'])
        ->delete();
      }

      exit();
    }

    public function anyRincian()
    {

      $rows = $this->db('resep_obat')
        ->join('dokter', 'dokter.kd_dokter=resep_obat.kd_dokter')
        ->join('resep_dokter', 'resep_dokter.no_resep=resep_obat.no_resep')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('resep_obat.status', 'ralan')
        ->group('resep_dokter.no_resep')
        ->toArray();
      $resep = [];
      $jumlah_total_resep = 0;
      foreach ($rows as $row) {
        $row['resep_dokter'] = $this->db('resep_dokter')->join('databarang', 'databarang.kode_brng=resep_dokter.kode_brng')->where('no_resep', $row['no_resep'])->toArray();
        foreach ($row['resep_dokter'] as $value) {
          $value['ralan'] = $value['jml'] * $value['dasar'];
          $jumlah_total_resep += floatval($value['ralan']);
        }

        $row['validasi'] = $this->db('resep_obat')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('tgl_perawatan','!=', $row['tgl_peresepan'])
        ->where('jam', '!=', $row['jam_peresepan'])
        ->where('status', 'ralan')
        ->oneArray();

        $resep[] = $row;
      }

      $rows_racikan = $this->db('resep_obat')
        ->join('dokter', 'dokter.kd_dokter=resep_obat.kd_dokter')
        ->join('resep_dokter_racikan', 'resep_dokter_racikan.no_resep=resep_obat.no_resep')
        ->where('no_rawat', $_POST['no_rawat'])
        ->group('resep_dokter_racikan.no_resep')
        ->where('resep_obat.status', 'ralan')
        ->toArray();
      $resep_racikan = [];
      $jumlah_total_resep_racikan = 0;
      foreach ($rows_racikan as $row) {
        $row['resep_dokter_racikan_detail'] = $this->db('resep_dokter_racikan_detail')->join('databarang', 'databarang.kode_brng=resep_dokter_racikan_detail.kode_brng')->where('no_resep', $row['no_resep'])->toArray();
        foreach ($row['resep_dokter_racikan_detail'] as $value) {
          $value['ralan'] = $value['jml'] * $value['dasar'];
          $jumlah_total_resep_racikan += floatval($value['ralan']);
        }

        $row['validasi'] = $this->db('resep_obat')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('tgl_perawatan','!=', $row['tgl_peresepan'])
        ->where('jam', '!=', $row['jam_peresepan'])
        ->where('status', 'ralan')
        ->oneArray();

        $resep_racikan[] = $row;
      }

      $query = $this->db()->pdo()->prepare("SELECT * FROM detail_pemberian_obat WHERE no_rawat = '{$_POST['no_rawat']}' AND status = 'Ralan' AND jam NOT IN (SELECT obat_racikan.jam FROM obat_racikan WHERE obat_racikan.no_rawat = '{$_POST['no_rawat']}' AND obat_racikan.tgl_perawatan = tgl_perawatan UNION ALL SELECT resep_obat.jam FROM resep_obat WHERE resep_obat.no_rawat = '{$_POST['no_rawat']}' AND resep_obat.tgl_perawatan = tgl_perawatan)");
      $query->execute();
      $rows_pemberian_obat = $query->fetchAll();

      $detail_pemberian_obat = [];
      $jumlah_total_obat = 0;
      foreach ($rows_pemberian_obat as $row) {
        $aturan_pakai = $this->db('aturan_pakai')
        ->where('no_rawat', $row['no_rawat'])
        ->where('kode_brng', $row['kode_brng'])
        ->where('tgl_perawatan', $row['tgl_perawatan'])
        ->where('jam', $row['jam'])
        ->oneArray();
        $row['aturan_pakai'] = $aturan_pakai['aturan'];
        $data_barang = $this->db('databarang')->where('kode_brng', $row['kode_brng'])->oneArray();
        $row['nama_brng'] = $data_barang['nama_brng'];
        $row['ralan'] = $data_barang['ralan'];
        $jumlah_total_obat += floatval($row['total']);
        $detail_pemberian_obat[] = $row;
      }

      $query2 = $this->db()->pdo()->prepare("SELECT * FROM obat_racikan WHERE no_rawat = '{$_POST['no_rawat']}' AND jam NOT IN (SELECT resep_obat.jam FROM resep_obat WHERE resep_obat.no_rawat = '{$_POST['no_rawat']}' AND resep_obat.tgl_perawatan = tgl_perawatan AND status = 'ralan')");
      $query2->execute();
      $rows_pemberian_obat2 = $query2->fetchAll();

      $detail_pemberian_obat2 = [];
      $jumlah_total_obat2 = 0;
      foreach ($rows_pemberian_obat2 as $row) {
        $row['detail_pemberian_obat'] = $this->db('detail_pemberian_obat')
          ->join('databarang', 'databarang.kode_brng=detail_pemberian_obat.kode_brng')
          ->where('no_rawat', $_POST['no_rawat'])
          ->where('tgl_perawatan', $row['tgl_perawatan'])
          ->where('jam', $row['jam'])
          ->toArray();
        foreach ($row['detail_pemberian_obat'] as $row2) {
          $jumlah_total_obat2 += floatval($row2['total']);
        }
        $detail_pemberian_obat2[] = $row;
      }

      echo $this->draw('rincian.html', ['jumlah_total_resep' => $jumlah_total_resep, 'jumlah_total_obat' => $jumlah_total_obat, 'jumlah_total_obat2' => $jumlah_total_obat2, 'resep' =>$resep, 'resep_racikan' => $resep_racikan, 'jumlah_total_resep_racikan' => $jumlah_total_resep_racikan, 'detail_pemberian_obat' => $detail_pemberian_obat, 'detail_pemberian_obat_racikan' => $detail_pemberian_obat2, 'no_rawat' => $_POST['no_rawat']]);
      exit();
    }

    public function anyObat()
    {
      $obat = $this->db('databarang')
        ->join('gudangbarang', 'gudangbarang.kode_brng=databarang.kode_brng')
        ->where('status', '1')
        ->where('gudangbarang.kd_bangsal', $this->settings->get('farmasi.deporalan'))
        ->like('databarang.nama_brng', '%'.$_POST['obat'].'%')
        ->limit(10)
        ->toArray();
      echo $this->draw('obat.html', ['obat' => $obat]);
      exit();
    }

    public function getAjax()
    {
        header('Content-type: text/html');
        $show = isset($_GET['show']) ? $_GET['show'] : "";
        switch($show){
        	default:
          break;
          case "databarang":
          $rows = $this->db('databarang')
            ->join('gudangbarang', 'gudangbarang.kode_brng=databarang.kode_brng')
            ->where('status', '1')
            ->where('stok', '>', '1')
            ->where('gudangbarang.kd_bangsal', $this->settings->get('farmasi.deporalan'))
            ->like('databarang.nama_brng', '%'.$_GET['nama_brng'].'%')
            ->limit(10)
            ->toArray();

          foreach ($rows as $row) {
            $array[] = array(
                'kode_brng' => $row['kode_brng'],
                'nama_brng'  => $row['nama_brng']
            );
          }
          echo json_encode($array, true);
          break;
        }
        exit();
    }

    public function anyRacikan()
    {
      $racikan = $this->db('metode_racik')
        ->like('nm_racik', '%'.$_POST['racikan'].'%')
        ->toArray();
      echo $this->draw('racikan.html', ['racikan' => $racikan]);
      exit();
    }

    public function postAturanPakai()
    {

      if(isset($_POST["query"])){
        $output = '';
        $key = "%".$_POST["query"]."%";
        $rows = $this->db('master_aturan_pakai')->like('aturan', $key)->limit(10)->toArray();
        $output = '';
        if(count($rows)){
          foreach ($rows as $row) {
            $output .= '<li class="list-group-item link-class">'.$row["aturan"].'</li>';
          }
        }
        echo $output;
      }

      exit();

    }

    public function postProviderList()
    {

      if(isset($_POST["query"])){
        $output = '';
        $key = "%".$_POST["query"]."%";
        $rows = $this->db('dokter')->like('nm_dokter', $key)->where('status', '1')->limit(10)->toArray();
        $output = '';
        if(count($rows)){
          foreach ($rows as $row) {
            $output .= '<li class="list-group-item link-class">'.$row["kd_dokter"].': '.$row["nm_dokter"].'</li>';
          }
        }
        echo $output;
      }

      exit();

    }

    public function postProviderList2()
    {

      if(isset($_POST["query"])){
        $output = '';
        $key = "%".$_POST["query"]."%";
        $rows = $this->db('petugas')->like('nama', $key)->limit(10)->toArray();
        $output = '';
        if(count($rows)){
          foreach ($rows as $row) {
            $output .= '<li class="list-group-item link-class">'.$row["nip"].': '.$row["nama"].'</li>';
          }
        }
        echo $output;
      }

      exit();

    }

    public function getCetakLabel($kode_brng, $no_rawat, $tgl_peresepan, $jam_peresepan, $tipe){
      if($tipe == 'nonracikan') {
        $rows_pemberian_obat = $this->db('detail_pemberian_obat')
        ->join('databarang', 'databarang.kode_brng=detail_pemberian_obat.kode_brng')
        ->join('reg_periksa', 'reg_periksa.no_rawat=detail_pemberian_obat.no_rawat')
        ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
        ->where('detail_pemberian_obat.no_rawat', revertNoRawat($no_rawat))
        ->where('detail_pemberian_obat.status', 'Ralan')
        ->where('detail_pemberian_obat.tgl_perawatan', $tgl_peresepan)
        ->where('detail_pemberian_obat.jam', $jam_peresepan)
        ->where('detail_pemberian_obat.kode_brng', $kode_brng)
        ->toArray();
        $detail_pemberian_obat = [];
        $jumlah_total_obat = 0;
        foreach ($rows_pemberian_obat as $row) {
          $aturan_pakai = $this->db('aturan_pakai')
          ->where('no_rawat', $row['no_rawat'])
          ->where('kode_brng', $row['kode_brng'])
          ->where('tgl_perawatan', $row['tgl_perawatan'])
          ->where('jam', $row['jam'])
          ->oneArray();
          $row['aturan_pakai'] = $aturan_pakai['aturan'];
          $row['keterangan'] = '';
          $detail_pemberian_obat[] = $row;
        }
      }
      if($tipe == 'racikan') {
        $rows_pemberian_obat = $this->db('obat_racikan')
          ->join('reg_periksa', 'reg_periksa.no_rawat=obat_racikan.no_rawat')
          ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
          ->where('obat_racikan.no_rawat', revertNoRawat($no_rawat))
          ->where('obat_racikan.kd_racik', $kode_brng)
          ->where('obat_racikan.tgl_perawatan', $tgl_peresepan)
          ->where('obat_racikan.jam', $jam_peresepan)
          ->toArray();
        $detail_pemberian_obat = [];
        $jumlah_total_obat = 0;
        foreach ($rows_pemberian_obat as $row) {
          $row['nama_brng'] = $row['nama_racik'];
          $row['jml'] = $row['jml_dr'];
          $detail_pemberian_obat[] = $row;
        }

      }

      $tanggal = dateIndonesia(date('Y-m-d'));
      $pasien = $this->core->getPasienInfo('nm_pasien', $this->core->getRegPeriksaInfo('no_rkm_medis', revertNoRawat($no_rawat)));
      $no_rm = $this->core->getRegPeriksaInfo('no_rkm_medis', revertNoRawat($no_rawat));

      echo $this->draw('cetak.etiket.html', [
        'pasien' => $pasien, 
        'no_rm' => $no_rm, 
        'tanggal' => $tanggal, 
        'settings' => $this->settings('settings'), 
        'farmasi' => $this->settings('farmasi'), 
        'detail' => $detail_pemberian_obat
      ]);

      $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => [100, 70], 
        'margin_left' => 2,
        'margin_right' => 2,
        'margin_top' => 2,
        'margin_bottom' => 2
      ]);

      $url = url(ADMIN.'/tmp/cetak.etiket.html');
      $html = file_get_contents($url);
      $mpdf->WriteHTML($this->core->setPrintCss(),\Mpdf\HTMLParserMode::HEADER_CSS);
      $mpdf->WriteHTML($html,\Mpdf\HTMLParserMode::HTML_BODY);

      // Output a PDF file directly to the browser
      $mpdf->Output();
      // $mpdf->Output(UPLOADS.'/test.pdf', 'F');
      exit();
    }

    public function getCetakEresep($no_rawat, $tipe, $tgl_peresepan, $jam_peresepan){
      if($tipe == 'nonracikan') {
        $resep_obat = $this->db('resep_obat')->where('no_rawat', revertNoRawat($no_rawat))->oneArray();
        $resep_dokter_racikan_detail = $this->db('resep_dokter_racikan_detail')->select('kode_brng')->where('no_resep', $resep_obat['no_resep'])->toArray();
        
        $notIn = array_map(function ($entry) {
          return ($entry[key($entry)]);
        }, $resep_dokter_racikan_detail);      
                
        $rows_pemberian_obat = $this->db('detail_pemberian_obat')
        ->join('databarang', 'databarang.kode_brng=detail_pemberian_obat.kode_brng')
        ->join('reg_periksa', 'reg_periksa.no_rawat=detail_pemberian_obat.no_rawat')
        ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
        ->where('detail_pemberian_obat.no_rawat', revertNoRawat($no_rawat))
        ->where('detail_pemberian_obat.status', 'Ralan')
        ->where('detail_pemberian_obat.tgl_perawatan', $tgl_peresepan)
        ->where('detail_pemberian_obat.jam', $jam_peresepan)
        ->toArray();
        if($notIn){
          $rows_pemberian_obat = $this->db('detail_pemberian_obat')
          ->join('databarang', 'databarang.kode_brng=detail_pemberian_obat.kode_brng')
          ->join('reg_periksa', 'reg_periksa.no_rawat=detail_pemberian_obat.no_rawat')
          ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
          ->where('detail_pemberian_obat.no_rawat', revertNoRawat($no_rawat))
          ->where('detail_pemberian_obat.status', 'Ralan')
          ->where('detail_pemberian_obat.tgl_perawatan', $tgl_peresepan)
          ->where('detail_pemberian_obat.jam', $jam_peresepan)
          ->notIn('detail_pemberian_obat.kode_brng', $notIn)
          ->toArray();  
        }
        $detail_pemberian_obat = [];
        $jumlah_total_obat = 0;
        foreach ($rows_pemberian_obat as $row) {
          $aturan_pakai = $this->db('aturan_pakai')
          ->where('no_rawat', $row['no_rawat'])
          ->where('kode_brng', $row['kode_brng'])
          ->where('tgl_perawatan', $row['tgl_perawatan'])
          ->where('jam', $row['jam'])
          ->oneArray();
          $row['aturan_pakai'] = $aturan_pakai['aturan'];
          $row['keterangan'] = '';
          $detail_pemberian_obat[] = $row;
        }
      }
      if($tipe == 'racikan') {
        $rows_pemberian_obat = $this->db('obat_racikan')
          ->join('reg_periksa', 'reg_periksa.no_rawat=obat_racikan.no_rawat')
          ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
          ->where('obat_racikan.no_rawat', revertNoRawat($no_rawat))
          ->where('obat_racikan.tgl_perawatan', $tgl_peresepan)
          ->where('obat_racikan.jam', $jam_peresepan)
          ->toArray();
        $detail_pemberian_obat = [];
        $jumlah_total_obat = 0;
        foreach ($rows_pemberian_obat as $row) {
          $row['nama_brng'] = $row['nama_racik'];
          $row['jml'] = $row['jml_dr'];
          $detail_pemberian_obat[] = $row;
        }

      }

      $tanggal = dateIndonesia(date('Y-m-d'));
      $pasien = $this->core->getPasienInfo('nm_pasien', $this->core->getRegPeriksaInfo('no_rkm_medis', revertNoRawat($no_rawat)));
      $no_rm = $this->core->getRegPeriksaInfo('no_rkm_medis', revertNoRawat($no_rawat));
      $umur = $this->core->getRegPeriksaInfo('umurdaftar', revertNoRawat($no_rawat));
      $sttsumur = $this->core->getRegPeriksaInfo('sttsumur', revertNoRawat($no_rawat));
      $alamat = $this->core->getPasienInfo('alamat', $this->core->getRegPeriksaInfo('no_rkm_medis', revertNoRawat($no_rawat)));

      echo $this->draw('cetak.eresep.html', [
        'pasien' => $pasien, 
        'no_rm' => $no_rm, 
        'umur' => $umur . ' ' . $sttsumur, 
        'alamat' => $alamat, 
        'tanggal' => $tanggal, 
        'settings' => $this->settings('settings'), 
        'detail' => $detail_pemberian_obat
      ]);

      $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => [200, 400], 
        'margin_left' => 20,
        'margin_right' => 20,
        'margin_top' => 2,
        'margin_bottom' => 2
      ]);

      $url = url(ADMIN.'/tmp/cetak.eresep.html');
      $html = file_get_contents($url);
      $mpdf->WriteHTML($this->core->setPrintCss(),\Mpdf\HTMLParserMode::HEADER_CSS);
      $mpdf->WriteHTML($html,\Mpdf\HTMLParserMode::HTML_BODY);

      // Output a PDF file directly to the browser
      $mpdf->Output();
      // $mpdf->Output(UPLOADS.'/test.pdf', 'F');
      exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $this->assign['websocket'] = $this->settings->get('settings.websocket');
        $this->assign['websocket_proxy'] = $this->settings->get('settings.websocket_proxy');
        echo $this->draw(MODULES.'/apotek_ralan/js/admin/apotek_ralan.js', ['mlite' => $this->assign]);
        exit();
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));
        $this->core->addJS(url([ADMIN, 'apotek_ralan', 'javascript']), 'footer');
    }

}
