<?php
namespace Plugins\Apotek_Ralan;

use Systems\AdminModule;

class Admin extends AdminModule
{
    protected array $assign = [];

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
            'embalase' => $_POST['embalase'],
            'tuslah' => $_POST['tuslah'],
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
              'embalase' => $_POST['embalase'],
              'tuslah' => $_POST['tuslah'],
              'total' => $kapasitas['dasar'] * $jml,
              'status' => 'Ralan',
              'kd_bangsal' => $this->settings->get('farmasi.deporalan'),
              'no_batch' => $get_gudangbarang['no_batch'],
              'no_faktur' => $get_gudangbarang['no_faktur']
            ]);

          $this->db('detail_obat_racikan')
            ->save([
              'tgl_perawatan' => $_POST['tgl_perawatan'],
              'jam' => $_POST['jam_rawat'],
              'no_rawat' => $_POST['no_rawat'],
              'no_racik' => $no_racik,
              'kode_brng' => $_POST['kode_brng'][$i]['value']
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
              'no_racik' => 'resep_dokter_racikan.no_racik',
              'nama_racik' => 'resep_dokter_racikan.nama_racik',
              'kd_racik' => 'resep_dokter_racikan.kd_racik',
              'jml_dr' => 'resep_dokter_racikan.jml_dr',
              'keterangan' => 'resep_dokter_racikan.keterangan',
              'kode_brng' => 'kode_brng',
              'jml' => 'jml',
              'aturan_pakai' => 'aturan_pakai'
            ])
          ->join('resep_dokter_racikan_detail', 'resep_dokter_racikan_detail.no_resep=resep_dokter_racikan.no_resep AND resep_dokter_racikan.no_racik=resep_dokter_racikan_detail.no_racik')
          ->where('resep_dokter_racikan.no_resep', $_POST['no_resep'])
          ->toArray();
        $get_resep_dokter = array_merge($get_resep_dokter_nonracikan, $get_resep_dokter_racikan);

        $embalaseData = isset($_POST['embalase']) ? json_decode($_POST['embalase'], true) : [];
        $tuslahData = isset($_POST['tuslah']) ? json_decode($_POST['tuslah'], true) : [];
        $jumlahData = isset($_POST['jumlah']) ? json_decode($_POST['jumlah'], true) : [];
        $kandunganData = isset($_POST['kandungan']) ? json_decode($_POST['kandungan'], true) : [];
        $aturanPakaiData = isset($_POST['aturan_pakai']) ? json_decode($_POST['aturan_pakai'], true) : [];

        if(!empty($get_resep_dokter_racikan)) {
            // Group by no_racik to avoid duplicate inserts
            $racikan_unique = [];
            foreach ($get_resep_dokter_racikan as $row) {
                if (!isset($racikan_unique[$row['no_racik']])) {
                    $racikan_unique[$row['no_racik']] = $row;
                }
            }
            
            foreach ($racikan_unique as $racikan) {
                // Ambil aturan pakai dari input jika ada (prioritas), jika tidak gunakan dari database
                $aturan_pakai_racikan = isset($aturanPakaiData[$racikan['kd_racik']]) ? $aturanPakaiData[$racikan['kd_racik']] : $racikan['aturan_pakai'];
                
                $this->db('obat_racikan')->save(
                    [
                        'tgl_perawatan' => $tgl_rawat,
                        'jam' => $jam_rawat,
                        'no_rawat' => $_POST['no_rawat'],
                        'no_racik' => $racikan['no_racik'],
                        'nama_racik' => $racikan['nama_racik'],
                        'kd_racik' => $racikan['kd_racik'],
                        'jml_dr' => $racikan['jml_dr'],
                        'aturan_pakai' => $aturan_pakai_racikan,
                        'keterangan' => $racikan['keterangan']
                    ]
                );
            }
        }


        foreach ($get_resep_dokter as $item) {

          $jumlah = isset($jumlahData[$item['kode_brng']]) ? $jumlahData[$item['kode_brng']] : $item['jml'];
          $kandungan = isset($kandunganData[$item['kode_brng']]) ? $kandunganData[$item['kode_brng']] : (isset($item['kandungan']) ? $item['kandungan'] : 0);
          
          if (isset($aturanPakaiData[$item['kode_brng']])) {
              $aturan_pakai = $aturanPakaiData[$item['kode_brng']];
          } else {
              $aturan_pakai = isset($item['aturan_pakai']) ? $item['aturan_pakai'] : '';
          }

          if(isset($item['no_racik'])) {
             $jumlah_racik = isset($item['jml_dr']) ? $item['jml_dr'] : (isset($jumlahData[$item['kode_brng']]) ? $jumlahData[$item['kode_brng']] : $item['jml']);
             $jumlah = isset($jumlahData[$item['kode_brng']]) ? $jumlahData[$item['kode_brng']] : $item['jml'];             
          }

          $get_gudangbarang = $this->db('gudangbarang')->where('kode_brng', $item['kode_brng'])->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))->oneArray();
          $get_databarang = $this->db('databarang')->where('kode_brng', $item['kode_brng'])->oneArray();

          $this->db('gudangbarang')
            ->where('kode_brng', $item['kode_brng'])
            ->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))
            ->update([
              'stok' => $get_gudangbarang['stok'] - $jumlah
            ]);

          if(isset($item['no_racik'])) {
            $this->db('resep_dokter_racikan')
              ->where('no_resep', $_POST['no_resep'])
              ->where('no_racik', $item['no_racik'])
              ->update([
                'jml_dr' => $jumlah_racik
              ]);
            $this->db('resep_dokter_racikan_detail')
              ->where('no_resep', $_POST['no_resep'])
              ->where('no_racik', $item['no_racik'])
              ->where('kode_brng', $item['kode_brng'])
              ->update([
                'jml' => $jumlah,
                'kandungan' => $kandungan
              ]);
          } else {
            $this->db('resep_dokter')
              ->where('no_resep', $_POST['no_resep'])
              ->where('kode_brng', $item['kode_brng'])
              ->update([
                'jml' => $jumlah
              ]);
          }

          $this->db('riwayat_barang_medis')
            ->save([
              'kode_brng' => $item['kode_brng'],
              'stok_awal' => $get_gudangbarang['stok'],
              'masuk' => '0',
              'keluar' => $jumlah,
              'stok_akhir' => $get_gudangbarang['stok'] - $jumlah,
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

          $embalase = isset($embalaseData[$item['kode_brng']]) ? $embalaseData[$item['kode_brng']] : $this->settings->get('farmasi.embalase');
          $tuslah = isset($tuslahData[$item['kode_brng']]) ? $tuslahData[$item['kode_brng']] : $this->settings->get('farmasi.tuslah');

          $this->db('detail_pemberian_obat')
            ->save([
              'tgl_perawatan' => $tgl_rawat,
              'jam' => $jam_rawat,
              'no_rawat' => $_POST['no_rawat'],
              'kode_brng' => $item['kode_brng'],
              'h_beli' => $get_databarang['h_beli'],
              'biaya_obat' => $get_databarang['dasar'],
              'jml' => $jumlah,
              'embalase' => $embalase,
              'tuslah' => $tuslah,
              'total' => ($get_databarang['dasar'] * $jumlah) + $embalase + $tuslah,
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
              'aturan' => $aturan_pakai
            ]);

          if(isset($item['no_racik'])) {
            $this->db('detail_obat_racikan')
              ->save([
                'tgl_perawatan' => $tgl_rawat,
                'jam' => $jam_rawat,
                'no_rawat' => $_POST['no_rawat'],
                'no_racik' => $item['no_racik'],
                'kode_brng' => $item['kode_brng']
              ]);
          }

        }

        $this->db('resep_obat')->where('no_resep', $_POST['no_resep'])->save(['tgl_perawatan' => $tgl_rawat, 'jam' => $jam_rawat]);
      }
      exit();
    }

    public function postTambahItemResep()
    {
      $no_resep = $_POST['no_resep'];
      $no_rawat = revertNorawat($_POST['no_rawat']);
      $tgl_peresepan = $_POST['tgl_peresepan'];
      $jam_peresepan = $_POST['jam_peresepan'];
      $kode_brng = $_POST['kode_brng'];
      $tipe = isset($_POST['tipe']) ? $_POST['tipe'] : 'nonracikan';
      
      $tgl_rawat = date('Y-m-d');
      $jam_rawat = date('H:i:s');
      
      $embalase = isset($_POST['embalase']) ? $_POST['embalase'] : $this->settings->get('farmasi.embalase');
      $tuslah = isset($_POST['tuslah']) ? $_POST['tuslah'] : $this->settings->get('farmasi.tuslah');
      
      $get_gudangbarang = $this->db('gudangbarang')->where('kode_brng', $kode_brng)->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))->oneArray();
      $get_databarang = $this->db('databarang')->where('kode_brng', $kode_brng)->oneArray();

      if ($tipe == 'racikan') {
          $no_racik = $_POST['no_racik'];
          $kandungan = $_POST['kandungan'];
          $jml_dr = $_POST['jml_dr']; // Jumlah racikan (bungkus)
          $kapasitas = $get_databarang['kapasitas'] > 0 ? $get_databarang['kapasitas'] : 1;
          
          // Hitung jumlah obat
          $jml = round(($jml_dr * $kandungan) / $kapasitas, 1);
          
          // Kurangi stok
          $this->db('gudangbarang')
            ->where('kode_brng', $kode_brng)
            ->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))
            ->update([
              'stok' => $get_gudangbarang['stok'] - $jml
            ]);

          // Riwayat
          $this->db('riwayat_barang_medis')
            ->save([
              'kode_brng' => $kode_brng,
              'stok_awal' => $get_gudangbarang['stok'],
              'masuk' => '0',
              'keluar' => $jml,
              'stok_akhir' => $get_gudangbarang['stok'] - $jml,
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

          // Simpan ke detail racikan
          // $this->db('resep_dokter_racikan_detail')
          //   ->save([
          //       'no_resep' => $no_resep,
          //       'no_racik' => $no_racik,
          //       'kode_brng' => $kode_brng,
          //       'p1' => 1, // Default
          //       'p2' => 1, // Default
          //       'kandungan' => $kandungan,
          //       'jml' => $jml
          //   ]);

          // Simpan ke detail pemberian obat (billing)
          $this->db('detail_pemberian_obat')
            ->save([
              'tgl_perawatan' => $tgl_rawat,
              'jam' => $jam_rawat,
              'no_rawat' => $no_rawat,
              'kode_brng' => $kode_brng,
              'h_beli' => $get_databarang['h_beli'],
              'biaya_obat' => $get_databarang['dasar'],
              'jml' => $jml,
              'embalase' => $embalase,
              'tuslah' => $tuslah,
              'total' => ($get_databarang['dasar'] * $jml) + $embalase + $tuslah,
              'status' => 'Ralan',
              'kd_bangsal' => $this->settings->get('farmasi.deporalan'),
              'no_batch' => $get_gudangbarang['no_batch'],
              'no_faktur' => $get_gudangbarang['no_faktur']
            ]);

          $this->db('detail_obat_racikan')
            ->save([
              'tgl_perawatan' => $tgl_rawat,
              'jam' => $jam_rawat,
              'no_rawat' => $no_rawat,
              'no_racik' => $no_racik,
              'kode_brng' => $kode_brng
            ]);
            
          header('Content-Type: application/json');
          echo json_encode([
            'kode_brng' => $kode_brng,
            'nama_brng' => $get_databarang['nama_brng'] ?? 'Nama Obat Tidak Ditemukan',
            'jml' => $jml,
            'kandungan' => $kandungan,
            'kapasitas' => $kapasitas,
            'ralan' => isset($get_databarang['dasar']) ? $get_databarang['dasar'] : 0,
            'embalase' => $embalase,
            'tuslah' => $tuslah
          ]);
          exit();

      } else {
          // Logika Non-Racikan (Existing)
          $jml = $_POST['jml'];
          $aturan_pakai = $_POST['aturan_pakai'];
          
          $this->db('gudangbarang')
            ->where('kode_brng', $kode_brng)
            ->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))
            ->update([
              'stok' => $get_gudangbarang['stok'] - $jml
            ]);

          $this->db('riwayat_barang_medis')
            ->save([
              'kode_brng' => $kode_brng,
              'stok_awal' => $get_gudangbarang['stok'],
              'masuk' => '0',
              'keluar' => $jml,
              'stok_akhir' => $get_gudangbarang['stok'] - $jml,
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
              'tgl_perawatan' => $tgl_peresepan,
              'jam' => $jam_peresepan,
              'no_rawat' => $no_rawat,
              'kode_brng' => $kode_brng,
              'h_beli' => $get_databarang['h_beli'],
              'biaya_obat' => $get_databarang['dasar'],
              'jml' => $jml,
              'embalase' => $embalase,
              'tuslah' => $tuslah,
              'total' => ($get_databarang['dasar'] * $jml) + $embalase + $tuslah,
              'status' => 'Ralan',
              'kd_bangsal' => $this->settings->get('farmasi.deporalan'),
              'no_batch' => $get_gudangbarang['no_batch'],
              'no_faktur' => $get_gudangbarang['no_faktur']
            ]);

          $this->db('aturan_pakai')
            ->save([
              'tgl_perawatan' => $tgl_peresepan,
              'jam' => $jam_peresepan,
              'no_rawat' => $no_rawat,
              'kode_brng' => $kode_brng,
              'aturan' => $aturan_pakai
            ]);

          header('Content-Type: application/json');
          echo json_encode([
            'kode_brng' => $kode_brng,
            'nama_brng' => $get_databarang['nama_brng'] ?? 'Nama Obat Tidak Ditemukan',
            'jml' => $jml,
            'aturan_pakai' => $aturan_pakai,
            'ralan' => isset($get_databarang['dasar']) ? (($get_databarang['dasar'] * $jml) + $embalase + $tuslah) : 0,
            'embalase' => $embalase,
            'tuslah' => $tuslah
          ]);
          exit();
      }
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

    public function postHapusObat()
    {

      $get_gudangbarang = $this->db('gudangbarang')->where('kode_brng', $_POST['kode_brng'])->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))->oneArray();

      $this->db('gudangbarang')
        ->where('kode_brng', $_POST['kode_brng'])
        ->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))
        ->update([
          'stok' => $get_gudangbarang['stok'] + $_POST['jml']
        ]);

      $this->db('riwayat_barang_medis')
        ->save([
          'kode_brng' => $_POST['kode_brng'],
          'stok_awal' => $get_gudangbarang['stok'],
          'masuk' => $_POST['jml'],
          'keluar' => '0',
          'stok_akhir' => $get_gudangbarang['stok'] + $_POST['jml'],
          'posisi' => 'Pemberian Obat',
          'tanggal' => $_POST['tgl_perawatan'],
          'jam' => $_POST['jam'],
          'petugas' => $this->core->getUserInfo('fullname', null, true),
          'kd_bangsal' => $this->settings->get('farmasi.deporalan'),
          'status' => 'Hapus',
          'no_batch' => $get_gudangbarang['no_batch'],
          'no_faktur' => $get_gudangbarang['no_faktur'],
          'keterangan' => $_POST['no_rawat'] . ' ' . $this->core->getRegPeriksaInfo('no_rkm_medis', $_POST['no_rawat']) . ' ' . $this->core->getPasienInfo('nm_pasien', $this->core->getRegPeriksaInfo('no_rkm_medis', $_POST['no_rawat']))
        ]);

      $this->db('detail_pemberian_obat')
      ->where('kode_brng', $_POST['kode_brng'])
      ->where('no_rawat', $_POST['no_rawat'])
      ->where('tgl_perawatan', $_POST['tgl_perawatan'])
      ->where('jam', $_POST['jam'])
      ->delete();

      exit();
    }    

    public function postHapusObatRacikan()
    {
      $no_rawat = $_POST['no_rawat'];
      $tgl_perawatan = $_POST['tgl_perawatan'];
      $jam = $_POST['jam'];
      $no_racik = $_POST['no_racik'];

      // 1. Get all items belonging to this specific racikan
      $items_in_racikan = $this->db('detail_obat_racikan')
      ->where('no_rawat', $no_rawat)
      ->where('tgl_perawatan', $tgl_perawatan)
      ->where('jam', $jam)
      ->where('no_racik', $no_racik)
      ->toArray();

      foreach($items_in_racikan as $racikan_item) {
        
        $kode_brng = $racikan_item['kode_brng'];

        // 2. Restore Stock (Best Effort)
        $item_billing = $this->db('detail_pemberian_obat')
        ->where('no_rawat', $no_rawat)
        ->where('tgl_perawatan', $tgl_perawatan)
        ->where('jam', $jam)
        ->where('kode_brng', $kode_brng)
        ->oneArray();

        if ($item_billing) {
            $get_gudangbarang = $this->db('gudangbarang')->where('kode_brng', $kode_brng)->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))->oneArray();

            $this->db('gudangbarang')
            ->where('kode_brng', $kode_brng)
            ->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))
            ->update([
              'stok' => $get_gudangbarang['stok'] + $item_billing['jml']
            ]);

            $this->db('riwayat_barang_medis')
              ->save([
                'kode_brng' => $kode_brng,
                'stok_awal' => $get_gudangbarang['stok'],
                'masuk' => $item_billing['jml'],
                'keluar' => '0',
                'stok_akhir' => $get_gudangbarang['stok'] + $item_billing['jml'],
                'posisi' => 'Pemberian Obat',
                'tanggal' => $tgl_perawatan,
                'jam' => $jam,
                'petugas' => $this->core->getUserInfo('fullname', null, true),
                'kd_bangsal' => $this->settings->get('farmasi.deporalan'),
                'status' => 'Hapus',
                'no_batch' => $get_gudangbarang['no_batch'],
                'no_faktur' => $get_gudangbarang['no_faktur'],
                'keterangan' => $no_rawat . ' ' . $this->core->getRegPeriksaInfo('no_rkm_medis', $no_rawat) . ' ' . $this->core->getPasienInfo('nm_pasien', $this->core->getRegPeriksaInfo('no_rkm_medis', $no_rawat))
              ]);
        }

        // 3. Delete from detail_pemberian_obat (Unconditional delete based on keys)
        $this->db('detail_pemberian_obat')
        ->where('no_rawat', $no_rawat)
        ->where('tgl_perawatan', $tgl_perawatan)
        ->where('jam', $jam)
        ->where('kode_brng', $kode_brng)
        ->delete();

        // 4. Delete from detail_obat_racikan (per item)
        $this->db('detail_obat_racikan')
        ->where('no_rawat', $no_rawat)
        ->where('tgl_perawatan', $tgl_perawatan)
        ->where('jam', $jam)
        ->where('no_racik', $no_racik)
        ->where('kode_brng', $kode_brng)
        ->delete();
      }

      // 5. Finally delete the parent racikan entry
      $this->db('obat_racikan')
        ->where('no_rawat', $no_rawat)
        ->where('tgl_perawatan', $tgl_perawatan)
        ->where('jam', $jam)
        ->where('no_racik', $no_racik)
        ->delete();

      exit();
    }    

    public function anyRincian()
    {
      $racikan_nos = $this->db('resep_dokter_racikan')->select('no_resep')->toArray();
      $racikan_nos = array_column($racikan_nos, 'no_resep');

      $rows = $this->db('resep_obat')
        ->join('dokter', 'dokter.kd_dokter=resep_obat.kd_dokter')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('resep_obat.status', 'ralan')
        ->group('resep_obat.no_resep')
        ->toArray();

      // Filter out racikan from non-racikan list
      $rows = array_filter($rows, function($row) use ($racikan_nos) {
          return !in_array($row['no_resep'], $racikan_nos);
      });

      $resep = [];
      $jumlah_total_resep = 0;
      foreach ($rows as $row) {
        $bangsal = $this->settings->get('farmasi.deporalan');
        $row['resep_dokter'] = $this->db('resep_dokter')
          ->join('databarang', 'databarang.kode_brng=resep_dokter.kode_brng')
          ->leftJoin('gudangbarang', 'gudangbarang.kode_brng=resep_dokter.kode_brng AND gudangbarang.kd_bangsal = "'.$bangsal.'"')
          ->where('no_resep', $row['no_resep'])
          ->toArray();
        foreach ($row['resep_dokter'] as $value) {
          $value['ralan'] = ($value['jml'] * $value['dasar']) + $this->settings->get('farmasi.embalase') + $this->settings->get('farmasi.tuslah');
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
        ->select('resep_obat.*')
        ->select('dokter.nm_dokter')
        ->select('resep_dokter_racikan.no_racik')
        ->select('resep_dokter_racikan.nama_racik')
        ->select('resep_dokter_racikan.kd_racik')
        ->select('resep_dokter_racikan.jml_dr')
        ->select('resep_dokter_racikan.aturan_pakai')
        ->select('resep_dokter_racikan.keterangan')
        ->join('dokter', 'dokter.kd_dokter=resep_obat.kd_dokter')
        ->join('resep_dokter_racikan', 'resep_dokter_racikan.no_resep=resep_obat.no_resep')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('resep_obat.status', 'ralan')
        ->group('resep_obat.no_resep')
        ->group('resep_dokter_racikan.no_racik')
        ->toArray();
      $resep_racikan = [];
      $jumlah_total_resep_racikan = 0;
      foreach ($rows_racikan as $row) {
        $bangsal = $this->settings->get('farmasi.deporalan');
        $row['resep_dokter_racikan_detail'] = $this->db('resep_dokter_racikan_detail')
          ->join('databarang', 'databarang.kode_brng=resep_dokter_racikan_detail.kode_brng')
          ->leftJoin('gudangbarang', 'gudangbarang.kode_brng=resep_dokter_racikan_detail.kode_brng AND gudangbarang.kd_bangsal = "'.$bangsal.'"')
          ->where('no_resep', $row['no_resep'])
          ->where('no_racik', $row['no_racik'])
          ->toArray();
        foreach ($row['resep_dokter_racikan_detail'] as &$value) {
          $value['ralan'] = ($value['jml'] * $value['dasar']) + $this->settings->get('farmasi.embalase') + $this->settings->get('farmasi.tuslah');
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

      $query = $this->db()->pdo()->prepare("SELECT * FROM detail_pemberian_obat WHERE no_rawat = '{$_POST['no_rawat']}' AND status = 'Ralan'");
      $query->execute();
      $rows_pemberian_obat = $query->fetchAll();

      // Filter out racikan from non-racikan list (detail_pemberian_obat)
      $obat_racikan_items = $this->db('detail_obat_racikan')
          ->select('kode_brng')
          ->where('no_rawat', $_POST['no_rawat'])
          ->toArray();
      $obat_racikan_items = array_column($obat_racikan_items, 'kode_brng');

      // Filter $rows_pemberian_obat agar tidak menampilkan barang yang sudah ada di racikan
      $rows_pemberian_obat = array_filter($rows_pemberian_obat, function($row) use ($obat_racikan_items) {
          return !in_array($row['kode_brng'], $obat_racikan_items);
      });

      $detail_pemberian_obat = [];
      $jumlah_total_obat = 0;
      foreach ($rows_pemberian_obat as $row) {
        $aturan_pakai = $this->db('aturan_pakai')
        ->where('no_rawat', $row['no_rawat'])
        ->where('kode_brng', $row['kode_brng'])
        ->where('tgl_perawatan', $row['tgl_perawatan'])
        ->where('jam', $row['jam'])
        ->oneArray();
        $row['aturan_pakai'] = $aturan_pakai['aturan'] ?? '';
        $data_barang = $this->db('databarang')->where('kode_brng', $row['kode_brng'])->oneArray();
        $row['nama_brng'] = $data_barang['nama_brng'] ?? '';
        $row['ralan'] = $data_barang['ralan'] ?? 0;
        $jumlah_total_obat += floatval($row['total']);
        $detail_pemberian_obat[] = $row;
      }

      $query2 = $this->db()->pdo()->prepare("SELECT obat_racikan.* FROM obat_racikan WHERE obat_racikan.no_rawat = '{$_POST['no_rawat']}'");
      $query2->execute();
      $rows_pemberian_obat2 = $query2->fetchAll();

      $detail_pemberian_obat2 = [];
      $jumlah_total_obat2 = 0;
      foreach ($rows_pemberian_obat2 as $row) {
        $ingredients_map = $this->db('detail_obat_racikan')
            ->where('no_rawat', $row['no_rawat'])
            ->where('tgl_perawatan', $row['tgl_perawatan'])
            ->where('jam', $row['jam'])
            ->where('no_racik', $row['no_racik'])
            ->toArray();

        $row['detail_pemberian_obat'] = [];

        foreach($ingredients_map as $map) {
             $detail = $this->db('detail_pemberian_obat')
                ->join('databarang', 'databarang.kode_brng=detail_pemberian_obat.kode_brng')
                ->where('detail_pemberian_obat.no_rawat', $map['no_rawat'])
                ->where('detail_pemberian_obat.kode_brng', $map['kode_brng'])
                ->where('detail_pemberian_obat.tgl_perawatan', $map['tgl_perawatan'])
                ->where('detail_pemberian_obat.jam', $map['jam'])
                ->where('detail_pemberian_obat.status', 'Ralan')
                ->oneArray();
             
             if($detail) {
                 $detail['kandungan'] = isset($map['kandungan']) ? $map['kandungan'] : '';
                 $jumlah_total_obat2 += floatval($detail['total']);
                 $row['detail_pemberian_obat'][] = $detail;
             }
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

          $array = [];
          foreach ($rows as $row) {
            $array[] = array(
                'kode_brng' => $row['kode_brng'],
                'nama_brng'  => $row['nama_brng'],
                'stok'  => $row['stok'],
                'ralan'  => $row['ralan']
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

    public function getCetakLabel($kode_brng, $no_rawat, $tgl_peresepan, $jam_peresepan, $tipe)
    {
      $detail_pemberian_obat = [];

      if ($tipe === 'nonracikan') {

        $rows = $this->db('detail_pemberian_obat')
          ->join('databarang', 'databarang.kode_brng=detail_pemberian_obat.kode_brng')
          ->join('reg_periksa', 'reg_periksa.no_rawat=detail_pemberian_obat.no_rawat')
          ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
          ->where('detail_pemberian_obat.no_rawat', revertNoRawat($no_rawat))
          ->where('detail_pemberian_obat.status', 'Ralan')
          ->where('detail_pemberian_obat.tgl_perawatan', $tgl_peresepan)
          ->where('detail_pemberian_obat.jam', $jam_peresepan)
          ->where('detail_pemberian_obat.kode_brng', $kode_brng)
          ->toArray();

        foreach ($rows as $row) {
          $aturan = $this->db('aturan_pakai')
            ->where('no_rawat', $row['no_rawat'])
            ->where('kode_brng', $row['kode_brng'])
            ->where('tgl_perawatan', $row['tgl_perawatan'])
            ->where('jam', $row['jam'])
            ->oneArray();

          $row['aturan_pakai'] = $aturan['aturan'] ?? '';
          $row['keterangan']   = '';
          $detail_pemberian_obat[] = $row;
        }
      }

      if ($tipe === 'racikan') {

        $rows = $this->db('obat_racikan')
          ->join('reg_periksa', 'reg_periksa.no_rawat=obat_racikan.no_rawat')
          ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
          ->where('obat_racikan.no_rawat', revertNoRawat($no_rawat))
          ->where('obat_racikan.kd_racik', $kode_brng)
          ->where('obat_racikan.tgl_perawatan', $tgl_peresepan)
          ->where('obat_racikan.jam', $jam_peresepan)
          ->toArray();

        foreach ($rows as $row) {
          $detail_pemberian_obat[] = [
            'nama_brng' => $row['nama_racik'],
            'jml'       => $row['jml_dr'],
            'aturan_pakai' => $row['aturan_pakai'],
            'keterangan'   => ''
          ];
        }
      }

      // ==== DATA TAMBAHAN ====
      $tanggal = dateIndonesia(date('Y-m-d'));
      $no_rawat_real = revertNoRawat($no_rawat);
      $no_rm = $this->core->getRegPeriksaInfo('no_rkm_medis', $no_rawat_real);
      $pasien = $this->core->getPasienInfo('nm_pasien', $no_rm);

      // ==== RENDER HTML ====
      $html = $this->draw('cetak.etiket.html', [
        'pasien'   => $pasien,
        'no_rm'    => $no_rm,
        'tanggal'  => $tanggal,
        'settings' => $this->settings('settings'),
        'farmasi'  => $this->settings('farmasi'),
        'detail'   => $detail_pemberian_obat
      ]);

      // ==== PDF LABEL ====
      $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => [100, 70], // ukuran label
        'margin_left' => 2,
        'margin_right' => 2,
        'margin_top' => 2,
        'margin_bottom' => 2
      ]);

      $mpdf->WriteHTML(
        $this->core->setPrintCss(),
        \Mpdf\HTMLParserMode::HEADER_CSS
      );
      $mpdf->WriteHTML(
        $html,
        \Mpdf\HTMLParserMode::HTML_BODY
      );

      $mpdf->Output();
      exit;
    }

    public function getCetakEresep($no_rawat, $tipe, $tgl_peresepan, $jam_peresepan)
    {
      $no_rawat_real = revertNoRawat($no_rawat);
      $detail_pemberian_obat = [];

      /* ================= NON RACIKAN ================= */
      if ($tipe === 'nonracikan') {

        $resep_obat = $this->db('resep_obat')
          ->where('no_rawat', $no_rawat_real)
          ->oneArray();

        $racikan = $this->db('resep_dokter_racikan_detail')
          ->select('kode_brng')
          ->where('no_resep', $resep_obat['no_resep'] ?? '')
          ->toArray();

        $notIn = array_column($racikan, 'kode_brng');

        $query = $this->db('detail_pemberian_obat')
          ->join('databarang', 'databarang.kode_brng=detail_pemberian_obat.kode_brng')
          ->join('reg_periksa', 'reg_periksa.no_rawat=detail_pemberian_obat.no_rawat')
          ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
          ->where('detail_pemberian_obat.no_rawat', $no_rawat_real)
          ->where('detail_pemberian_obat.status', 'Ralan')
          ->where('detail_pemberian_obat.tgl_perawatan', $tgl_peresepan)
          ->where('detail_pemberian_obat.jam', $jam_peresepan);

        if (!empty($notIn)) {
          $query->notIn('detail_pemberian_obat.kode_brng', $notIn);
        }

        $rows = $query->toArray();

        foreach ($rows as $row) {
          $aturan = $this->db('aturan_pakai')
            ->where('no_rawat', $row['no_rawat'])
            ->where('kode_brng', $row['kode_brng'])
            ->where('tgl_perawatan', $row['tgl_perawatan'])
            ->where('jam', $row['jam'])
            ->oneArray();

          $row['aturan_pakai'] = $aturan['aturan'] ?? '';
          $row['keterangan']   = '';
          $detail_pemberian_obat[] = $row;
        }
      }

      /* ================= RACIKAN ================= */
      if ($tipe === 'racikan') {

        $rows = $this->db('obat_racikan')
          ->join('reg_periksa', 'reg_periksa.no_rawat=obat_racikan.no_rawat')
          ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
          ->where('obat_racikan.no_rawat', $no_rawat_real)
          ->where('obat_racikan.tgl_perawatan', $tgl_peresepan)
          ->where('obat_racikan.jam', $jam_peresepan)
          ->toArray();

        foreach ($rows as $row) {
          $detail_pemberian_obat[] = [
            'nama_brng' => $row['nama_racik'],
            'jml'       => $row['jml_dr'],
            'aturan_pakai' => $row['aturan_pakai'],
            'keterangan'   => ''
          ];
        }
      }

      /* ================= DATA PASIEN ================= */
      $no_rm   = $this->core->getRegPeriksaInfo('no_rkm_medis', $no_rawat_real);
      $pasien  = $this->core->getPasienInfo('nm_pasien', $no_rm);
      $umur    = $this->core->getRegPeriksaInfo('umurdaftar', $no_rawat_real);
      $sttsumur= $this->core->getRegPeriksaInfo('sttsumur', $no_rawat_real);
      $alamat  = $this->core->getPasienInfo('alamat', $no_rm);
      $tanggal = dateIndonesia(date('Y-m-d'));

      /* ================= RENDER HTML ================= */
      $html = $this->draw('cetak.eresep.html', [
        'pasien'   => $pasien,
        'no_rm'    => $no_rm,
        'umur'     => $umur . ' ' . $sttsumur,
        'alamat'   => $alamat,
        'tanggal'  => $tanggal,
        'settings' => $this->settings('settings'),
        'detail'   => $detail_pemberian_obat
      ]);

      /* ================= PDF ================= */
      $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => [200, 400],
        'margin_left' => 20,
        'margin_right' => 20,
        'margin_top' => 2,
        'margin_bottom' => 2
      ]);

      $mpdf->WriteHTML(
        $this->core->setPrintCss(),
        \Mpdf\HTMLParserMode::HEADER_CSS
      );
      $mpdf->WriteHTML(
        $html,
        \Mpdf\HTMLParserMode::HTML_BODY
      );

      $mpdf->Output();
      exit;
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

    public function apiResepList()
    {
        $username = $this->core->checkAuth('GET');
        if (!$this->core->checkPermission($username, 'can_read', 'apotek_ralan')) {
            return ['status' => 'error', 'message' => 'You do not have permission to access this resource'];
        }

        $this->db()->pdo()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $per_page = isset($_GET['per_page']) ? $_GET['per_page'] : 10;
        $offset = ($page - 1) * $per_page;
        $search = isset($_GET['s']) ? $_GET['s'] : '';
        $tgl_awal = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-d');
        $tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');

        $query = $this->db('resep_obat')
            ->leftJoin('reg_periksa', 'reg_periksa.no_rawat = resep_obat.no_rawat')
            ->leftJoin('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
            ->leftJoin('dokter', 'dokter.kd_dokter = resep_obat.kd_dokter')
            ->where(function($query) {
                $query->where('resep_obat.status', 'ralan');
            })
            ->where('resep_obat.tgl_peresepan', '>=', $tgl_awal)
            ->where('resep_obat.tgl_peresepan', '<=', $tgl_akhir);

        if ($search) {
             $query->like('resep_obat.no_resep', '%'.$search.'%');
        }

        $total = $query->count();
        $data = $query
            ->select('resep_obat.*')
            ->select('pasien.nm_pasien')
            ->select('pasien.no_rkm_medis')
            ->select('dokter.nm_dokter')
            ->offset($offset)
            ->limit($per_page)
            ->desc('resep_obat.tgl_peresepan')
            ->desc('resep_obat.jam_peresepan')
            ->toArray();

        // Optimization: Details are fetched asynchronously on frontend
        // foreach ($data as &$row) {
        //    $row['detail'] = $this->apiShowDetail('obat', $row['no_rawat'], $row['no_resep']);
        //    $row['racikan'] = $this->apiShowDetail('racikan', $row['no_rawat'], $row['no_resep']);
        // }

        return [
            'status' => 'success',
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page
        ];
    }

    public function apiShowDetail($category = null, $no_rawat = null, $no_resep = null)
    {
        $username = $this->core->checkAuth('GET');
        if (!$this->core->checkPermission($username, 'can_read', 'apotek_ralan')) {
            return ['status' => 'error', 'message' => 'You do not have permission to access this resource'];
        }

        $no_rawat = revertNorawat($no_rawat);
        $kategori = trim($category);
        
        if (!$no_resep) {
            $no_resep = isset($_GET['no_resep']) ? $_GET['no_resep'] : null;
        }

        $pasien = $this->db('reg_periksa')
            ->leftJoin('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
            ->where('no_rawat', $no_rawat)
            ->oneArray();

        $patient_info = [
            'nm_pasien' => $pasien['nm_pasien'] ?? '',
            'no_rkm_medis' => $pasien['no_rkm_medis'] ?? ''
        ];

        try {
            if ($kategori == 'obat') {
                $query = $this->db('resep_dokter')
                    ->join('resep_obat', 'resep_obat.no_resep = resep_dokter.no_resep')
                    ->join('databarang', 'databarang.kode_brng = resep_dokter.kode_brng')
                    ->leftJoin('reg_periksa', 'reg_periksa.no_rawat = resep_obat.no_rawat')
                    ->leftJoin('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
                    ->where('resep_obat.no_rawat', $no_rawat)
                    ->where(function($query) {
                        $query->where('resep_obat.status', 'ralan');
                    });

                if ($no_resep) {
                    $query->where('resep_obat.no_resep', $no_resep);
                }

                $resep_dokter = $query->toArray();

                return [
                    'status' => 'success',
                    'patient' => $patient_info,
                    'data' => $resep_dokter
                ];
            } elseif ($kategori == 'racikan') {
                $query = $this->db('resep_dokter_racikan')
                    ->join('resep_obat', 'resep_obat.no_resep = resep_dokter_racikan.no_resep')
                    ->join('metode_racik', 'metode_racik.kd_racik = resep_dokter_racikan.kd_racik')
                    ->leftJoin('reg_periksa', 'reg_periksa.no_rawat = resep_obat.no_rawat')
                    ->leftJoin('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
                    ->where('resep_obat.no_rawat', $no_rawat)
                    ->where(function($query) {
                        $query->where('resep_obat.status', 'ralan');
                    });

                if ($no_resep) {
                    $query->where('resep_obat.no_resep', $no_resep);
                }

                $resep_racikan = $query->toArray();

                foreach ($resep_racikan as &$racikan) {
                    $racikan['detail'] = $this->db('resep_dokter_racikan_detail')
                        ->join('databarang', 'databarang.kode_brng = resep_dokter_racikan_detail.kode_brng')
                        ->where('no_resep', $racikan['no_resep'])
                        ->where('no_racik', $racikan['no_racik'])
                        ->toArray();
                }

                return [
                    'status' => 'success',
                    'patient' => $patient_info,
                    'data' => $resep_racikan
                ];
            } else {
                return ['status' => 'error', 'message' => 'Category not supported: ' . $kategori];
            }
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function apiValidasi($no_rawat = null, $no_resep = null)
    {
        $username = $this->core->checkAuth('GET');
        if (!$this->core->checkPermission($username, 'can_read', 'apotek_ralan')) {
            return ['status' => 'error', 'message' => 'You do not have permission to access this resource'];
        }

        $no_rawat = revertNorawat($no_rawat);
        if (!$no_resep) {
            $no_resep = isset($_GET['no_resep']) ? $_GET['no_resep'] : null;
        }

        $detail_pemberian_obat = $this->db('detail_pemberian_obat')
            ->join('databarang', 'databarang.kode_brng=detail_pemberian_obat.kode_brng')
            ->where('no_rawat', $no_rawat)
            ->where('status', 'Ralan')
            ->toArray();

        // Also fetch obat_racikan
        $obat_racikan = $this->db('obat_racikan')
            ->where('no_rawat', $no_rawat)
            ->toArray();

        return [
            'status' => 'success',
            'data' => [
                'pemberian_obat' => $detail_pemberian_obat,
                'obat_racikan' => $obat_racikan
            ]
        ];
    }

    public function apiSaveValidasi()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_create', 'apotek_ralan')) {
            return ['status' => 'error', 'message' => 'You do not have permission to access this resource'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $tgl_rawat = date('Y-m-d');
        $jam_rawat = date('H:i:s');
        
        $no_resep = $input['no_resep'];
        $no_rawat = $input['no_rawat'];

        if(isset($input['penyerahan']) && $input['penyerahan'] == 'penyerahan') {
            $this->db('resep_obat')->where('no_resep', $no_resep)->save(['tgl_penyerahan' => $tgl_rawat, 'jam_penyerahan' => $jam_rawat]);
        } else {
            $get_resep_dokter_nonracikan = $this->db('resep_dokter')
              ->select([
                  'kode_brng' => 'kode_brng',
                  'jml' => 'jml',
                  'aturan_pakai' => 'aturan_pakai'
                ])
              ->where('no_resep', $no_resep)
              ->toArray();
            $get_resep_dokter_racikan = $this->db('resep_dokter_racikan')
              ->select([
                  'no_racik' => 'resep_dokter_racikan.no_racik',
                  'nama_racik' => 'resep_dokter_racikan.nama_racik',
                  'kd_racik' => 'resep_dokter_racikan.kd_racik',
                  'jml_dr' => 'resep_dokter_racikan.jml_dr',
                  'keterangan' => 'resep_dokter_racikan.keterangan',
                  'kode_brng' => 'kode_brng',
                  'jml' => 'jml',
                  'aturan_pakai' => 'aturan_pakai'
                ])
              ->join('resep_dokter_racikan_detail', 'resep_dokter_racikan_detail.no_resep=resep_dokter_racikan.no_resep AND resep_dokter_racikan.no_racik=resep_dokter_racikan_detail.no_racik')
              ->where('resep_dokter_racikan.no_resep', $no_resep)
              ->toArray();
            $get_resep_dokter = array_merge($get_resep_dokter_nonracikan, $get_resep_dokter_racikan);

            $embalaseData = isset($input['embalase']) ? (is_array($input['embalase']) ? $input['embalase'] : json_decode($input['embalase'], true)) : [];
            $tuslahData = isset($input['tuslah']) ? (is_array($input['tuslah']) ? $input['tuslah'] : json_decode($input['tuslah'], true)) : [];
            $jumlahData = isset($input['jumlah']) ? (is_array($input['jumlah']) ? $input['jumlah'] : json_decode($input['jumlah'], true)) : [];
            $kandunganData = isset($input['kandungan']) ? (is_array($input['kandungan']) ? $input['kandungan'] : json_decode($input['kandungan'], true)) : [];
            $aturanPakaiData = isset($input['aturan_pakai']) ? (is_array($input['aturan_pakai']) ? $input['aturan_pakai'] : json_decode($input['aturan_pakai'], true)) : [];

            if(!empty($get_resep_dokter_racikan)) {
                $racikan_unique = [];
                foreach ($get_resep_dokter_racikan as $row) {
                    if (!isset($racikan_unique[$row['no_racik']])) {
                        $racikan_unique[$row['no_racik']] = $row;
                    }
                }
                
                $aturan_pakai_racikan = isset($aturanPakaiData[$racikan['kd_racik']]) ? $aturanPakaiData[$racikan['kd_racik']] : $racikan['aturan_pakai'];

                foreach ($racikan_unique as $racikan) {
                    $this->db('obat_racikan')->save(
                        [
                            'tgl_perawatan' => $tgl_rawat,
                            'jam' => $jam_rawat,
                            'no_rawat' => $no_rawat,
                            'no_racik' => $racikan['no_racik'],
                            'nama_racik' => $racikan['nama_racik'],
                            'kd_racik' => $racikan['kd_racik'],
                            'jml_dr' => $racikan['jml_dr'],
                            'aturan_pakai' => $aturan_pakai_racikan,
                            'keterangan' => $racikan['keterangan']
                        ]
                    );
                }
            }

            foreach ($get_resep_dokter as $item) {

              $jumlah = isset($jumlahData[$item['kode_brng']]) ? $jumlahData[$item['kode_brng']] : $item['jml'];
              $kandungan = isset($kandunganData[$item['kode_brng']]) ? $kandunganData[$item['kode_brng']] : (isset($item['kandungan']) ? $item['kandungan'] : 0);
              $aturan_pakai = isset($aturanPakaiData[$item['kode_brng']]) ? $aturanPakaiData[$item['kode_brng']] : (isset($item['aturan_pakai']) ? $item['aturan_pakai'] : '');

              if(isset($item['no_racik'])) {
                 $jumlah_racik = isset($item['jml_dr']) ? $item['jml_dr'] : (isset($jumlahData[$item['kode_brng']]) ? $jumlahData[$item['kode_brng']] : $item['jml']);
                 $jumlah = isset($jumlahData[$item['kode_brng']]) ? $jumlahData[$item['kode_brng']] : $item['jml'];
              }

              $get_gudangbarang = $this->db('gudangbarang')->where('kode_brng', $item['kode_brng'])->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))->oneArray();
              $get_databarang = $this->db('databarang')->where('kode_brng', $item['kode_brng'])->oneArray();

              $this->db('gudangbarang')
                ->where('kode_brng', $item['kode_brng'])
                ->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))
                ->update([
                  'stok' => $get_gudangbarang['stok'] - $jumlah
                ]);

              if(isset($item['no_racik'])) {
                $this->db('resep_dokter_racikan')
                  ->where('no_resep', $no_resep)
                  ->where('no_racik', $item['no_racik'])
                  ->update([
                    'jml_dr' => $jumlah_racik
                  ]);
                $this->db('resep_dokter_racikan_detail')
                  ->where('no_resep', $no_resep)
                  ->where('no_racik', $item['no_racik'])
                  ->where('kode_brng', $item['kode_brng'])
                  ->update([
                    'jml' => $jumlah,
                    'kandungan' => $kandungan
                  ]);
              } else {
                $this->db('resep_dokter')
                  ->where('no_resep', $no_resep)
                  ->where('kode_brng', $item['kode_brng'])
                  ->update([
                    'jml' => $jumlah
                  ]);
              }

              $this->db('riwayat_barang_medis')
                ->save([
                  'kode_brng' => $item['kode_brng'],
                  'stok_awal' => $get_gudangbarang['stok'],
                  'masuk' => '0',
                  'keluar' => $jumlah,
                  'stok_akhir' => $get_gudangbarang['stok'] - $jumlah,
                  'posisi' => 'Pemberian Obat',
                  'tanggal' => $tgl_rawat,
                  'jam' => $jam_rawat,
                  'petugas' => $this->core->getUserInfo('fullname', null, true),
                  'kd_bangsal' => $this->settings->get('farmasi.deporalan'),
                  'status' => 'Simpan',
                  'no_batch' => $get_gudangbarang['no_batch'],
                  'no_faktur' => $get_gudangbarang['no_faktur'],
                  'keterangan' => $no_rawat . ' ' . $this->core->getRegPeriksaInfo('no_rkm_medis', $no_rawat) . ' ' . $this->core->getPasienInfo('nm_pasien', $this->core->getRegPeriksaInfo('no_rkm_medis', $no_rawat))
                ]);

              $embalase = isset($embalaseData[$item['kode_brng']]) ? $embalaseData[$item['kode_brng']] : $this->settings->get('farmasi.embalase');
              $tuslah = isset($tuslahData[$item['kode_brng']]) ? $tuslahData[$item['kode_brng']] : $this->settings->get('farmasi.tuslah');

              $this->db('detail_pemberian_obat')
                ->save([
                  'tgl_perawatan' => $tgl_rawat,
                  'jam' => $jam_rawat,
                  'no_rawat' => $no_rawat,
                  'kode_brng' => $item['kode_brng'],
                  'h_beli' => $get_databarang['h_beli'],
                  'biaya_obat' => $get_databarang['dasar'],
                  'jml' => $jumlah,
                  'embalase' => $embalase,
                  'tuslah' => $tuslah,
                  'total' => ($get_databarang['dasar'] * $jumlah) + $embalase + $tuslah,
                  'status' => 'Ralan',
                  'kd_bangsal' => $this->settings->get('farmasi.deporalan'),
                  'no_batch' => $get_gudangbarang['no_batch'],
                  'no_faktur' => $get_gudangbarang['no_faktur']
                ]);

              $this->db('aturan_pakai')
                ->save([
                  'tgl_perawatan' => $tgl_rawat,
                  'jam' => $jam_rawat,
                  'no_rawat' => $no_rawat,
                  'kode_brng' => $item['kode_brng'],
                  'aturan' => $aturan_pakai
                ]);

              if(isset($item['no_racik'])) {
                $this->db('detail_obat_racikan')
                  ->save([
                    'tgl_perawatan' => $tgl_rawat,
                    'jam' => $jam_rawat,
                    'no_rawat' => $no_rawat,
                    'no_racik' => $item['no_racik'],
                    'kode_brng' => $item['kode_brng']
                  ]);
              }

            }

            $this->db('resep_obat')->where('no_resep', $no_resep)->save(['tgl_perawatan' => $tgl_rawat, 'jam' => $jam_rawat]);
        }
        
        return ['status' => 'success'];
    }

    public function apiSimpanObatResep()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_create', 'apotek_ralan')) {
            return ['status' => 'error', 'message' => 'You do not have permission to access this resource'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $check = $this->db('resep_dokter')
            ->where('no_resep', $input['no_resep'])
            ->where('kode_brng', $input['kode_brng'])
            ->oneArray();

        if ($check) {
            $this->db('resep_dokter')
                ->where('no_resep', $input['no_resep'])
                ->where('kode_brng', $input['kode_brng'])
                ->update([
                    'jml' => $input['jml'],
                    'aturan_pakai' => $input['aturan_pakai']
                ]);
        } else {
            $this->db('resep_dokter')
                ->save([
                    'no_resep' => $input['no_resep'],
                    'kode_brng' => $input['kode_brng'],
                    'jml' => $input['jml'],
                    'aturan_pakai' => $input['aturan_pakai']
                ]);
        }

        return ['status' => 'success'];
    }

    public function apiSimpanRacikanResep()
    {
        $username = $this->core->checkAuth('POST');
        if (!$this->core->checkPermission($username, 'can_create', 'apotek_ralan')) {
            return ['status' => 'error', 'message' => 'You do not have permission to access this resource'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        // 1. Save Header Racikan
        $no_racik = $input['no_racik'];
        $check = $this->db('resep_dokter_racikan')
            ->where('no_resep', $input['no_resep'])
            ->where('no_racik', $no_racik)
            ->oneArray();

        if (!$check) {
            $this->db('resep_dokter_racikan')->save([
                'no_resep' => $input['no_resep'],
                'no_racik' => $no_racik,
                'nama_racik' => $input['nama_racik'],
                'kd_racik' => $input['kd_racik'],
                'jml_dr' => $input['jml_dr'],
                'aturan_pakai' => $input['aturan_pakai'],
                'keterangan' => $input['keterangan']
            ]);
        } else {
            $this->db('resep_dokter_racikan')
                ->where('no_resep', $input['no_resep'])
                ->where('no_racik', $no_racik)
                ->update([
                    'nama_racik' => $input['nama_racik'],
                    'jml_dr' => $input['jml_dr'],
                    'aturan_pakai' => $input['aturan_pakai'],
                    'keterangan' => $input['keterangan']
                ]);
        }

        // 2. Save Ingredients
        $items = isset($input['items']) ? (is_array($input['items']) ? $input['items'] : json_decode($input['items'], true)) : [];
        
        if (is_array($items)) {
            // Delete existing details for this racikan to allow full update
            $this->db('resep_dokter_racikan_detail')
                ->where('no_resep', $input['no_resep'])
                ->where('no_racik', $no_racik)
                ->delete();

            foreach ($items as $item) {
                $this->db('resep_dokter_racikan_detail')->save([
                    'no_resep' => $input['no_resep'],
                    'no_racik' => $no_racik,
                    'kode_brng' => $item['kode_brng'],
                    'p1' => 1,
                    'p2' => 1,
                    'kandungan' => $item['kandungan'],
                    'jml' => $item['jml']
                ]);
            }
        }

        return ['status' => 'success'];
    }

}
