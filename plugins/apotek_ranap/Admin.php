<?php
namespace Plugins\Apotek_Ranap;

use Systems\AdminModule;

class Admin extends AdminModule
{
    protected $assign = [];

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

        if(isset($_POST['periode_rawat_inap'])) {
          $tgl_kunjungan = $_POST['periode_rawat_inap'];
        }
        if(isset($_POST['periode_rawat_inap_akhir'])) {
          $tgl_kunjungan_akhir = $_POST['periode_rawat_inap_akhir'];
        }
        if(isset($_POST['status_periksa'])) {
          $status_periksa = $_POST['status_periksa'];
        }
        $cek_vclaim = $this->db('mlite_modules')->where('dir', 'vclaim')->oneArray();
        $this->_Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa);
        return $this->draw('manage.html', ['rawat_inap' => $this->assign, 'cek_vclaim' => $cek_vclaim]);
    }

    public function anyDisplay()
    {
        $tgl_kunjungan = date('Y-m-d');
        $tgl_kunjungan_akhir = date('Y-m-d');
        $status_periksa = '';

        if(isset($_POST['periode_rawat_inap'])) {
          $tgl_kunjungan = $_POST['periode_rawat_inap'];
        }
        if(isset($_POST['periode_rawat_inap_akhir'])) {
          $tgl_kunjungan_akhir = $_POST['periode_rawat_inap_akhir'];
        }
        if(isset($_POST['status_periksa'])) {
          $status_periksa = $_POST['status_periksa'];
        }
        $cek_vclaim = $this->db('mlite_modules')->where('dir', 'vclaim')->oneArray();
        $this->_Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa);
        echo $this->draw('display.html', ['rawat_inap' => $this->assign, 'cek_vclaim' => $cek_vclaim]);
        exit();
    }

    public function _Display($tgl_masuk='', $tgl_masuk_akhir='', $status_pulang='')
    {
        $this->_addHeaderFiles();

        $this->assign['kamar'] = $this->db('kamar')->join('bangsal', 'bangsal.kd_bangsal=kamar.kd_bangsal')->where('statusdata', '1')->toArray();
        $this->assign['dokter']         = $this->db('dokter')->where('status', '1')->toArray();
        $this->assign['penjab']       = $this->db('penjab')->where('status', '1')->toArray();
        $this->assign['no_rawat'] = '';

        $bangsal = str_replace(",","','", $this->core->getUserInfo('cap', null, true));

        $sql = "SELECT
            kamar_inap.*,
            reg_periksa.*,
            pasien.*,
            kamar.*,
            bangsal.*,
            penjab.*
          FROM
            kamar_inap,
            reg_periksa,
            pasien,
            kamar,
            bangsal,
            penjab
          WHERE
            kamar_inap.no_rawat=reg_periksa.no_rawat
          AND
            reg_periksa.no_rkm_medis=pasien.no_rkm_medis
          AND
            kamar_inap.kd_kamar=kamar.kd_kamar
          AND
            bangsal.kd_bangsal=kamar.kd_bangsal
          AND
            reg_periksa.kd_pj=penjab.kd_pj";

        if ($this->core->getUserInfo('role') != 'admin') {
          $sql .= " AND bangsal.kd_bangsal IN ('$bangsal')";
        }
        if($status_pulang == '') {
          $sql .= " AND kamar_inap.stts_pulang = '-'";
        }
        if($status_pulang == 'all' && $tgl_masuk !== '' && $tgl_masuk_akhir !== '') {
          $sql .= " AND kamar_inap.stts_pulang = '-' AND kamar_inap.tgl_masuk BETWEEN '$tgl_masuk' AND '$tgl_masuk_akhir'";
        }
        if($status_pulang == 'masuk' && $tgl_masuk !== '' && $tgl_masuk_akhir !== '') {
          $sql .= " AND kamar_inap.tgl_masuk BETWEEN '$tgl_masuk' AND '$tgl_masuk_akhir'";
        }
        if($status_pulang == 'pulang' && $tgl_masuk !== '' && $tgl_masuk_akhir !== '') {
          $sql .= " AND kamar_inap.tgl_keluar BETWEEN '$tgl_masuk' AND '$tgl_masuk_akhir'";
        }

        $stmt = $this->db()->pdo()->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $this->assign['list'] = [];
        foreach ($rows as $row) {
          $dpjp_ranap = $this->db('dpjp_ranap')
            ->join('dokter', 'dokter.kd_dokter=dpjp_ranap.kd_dokter')
            ->where('no_rawat', $row['no_rawat'])
            ->toArray();
          $row['dokter'] = $dpjp_ranap;
          $this->assign['list'][] = $row;
        }

    }

    public function postSaveDetail()
    {

      if($_POST['kat'] == 'obat') {
        $embalase = isset($_POST['embalase']) ? $_POST['embalase'] : 0;
        $tuslah = isset($_POST['tuslah']) ? $_POST['tuslah'] : 0;
        $get_gudangbarang = $this->db('gudangbarang')->where('kode_brng', $_POST['kd_jenis_prw'])->where('kd_bangsal', $this->settings->get('farmasi.deporanap'))->oneArray();
        $get_databarang = $this->db('databarang')->where('kode_brng', $_POST['kd_jenis_prw'])->oneArray();

        $this->db('gudangbarang')
          ->where('kode_brng', $_POST['kd_jenis_prw'])
          ->where('kd_bangsal', $this->settings->get('farmasi.deporanap'))
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
            'kd_bangsal' => $this->settings->get('farmasi.deporanap'),
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
            'h_beli' => $get_databarang['h_beli'],
            'biaya_obat' => $_POST['biaya'],
            'jml' => $_POST['jml'],
            'embalase' => $embalase,
            'tuslah' => $tuslah,
            'total' => ($_POST['biaya'] * $_POST['jml']) + $embalase + $tuslah,
            'status' => 'Ranap',
            'kd_bangsal' => $this->settings->get('farmasi.deporanap'),
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
        
        $kode_brng_arr = json_decode($_POST['kode_brng'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $kode_brng_arr = json_decode(stripslashes($_POST['kode_brng']), true);
        }
        
        $kandungan_arr = json_decode($_POST['kandungan'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $kandungan_arr = json_decode(stripslashes($_POST['kandungan']), true);
        }

        if (is_array($kode_brng_arr)) {
            for ($i = 0; $i < count($kode_brng_arr); $i++) {
              $kode_brng_val = $kode_brng_arr[$i]['value'];
              $kandungan_val = isset($kandungan_arr[$i]['value']) ? floatval($kandungan_arr[$i]['value']) : 0;

              $get_gudangbarang = $this->db('gudangbarang')->where('kode_brng', $kode_brng_val)->where('kd_bangsal', $this->settings->get('farmasi.deporanap'))->oneArray();
              $kapasitas = $this->db('databarang')->where('kode_brng', $kode_brng_val)->oneArray();
              
              $kapasitas_nilai = isset($kapasitas['kapasitas']) && $kapasitas['kapasitas'] > 0 ? $kapasitas['kapasitas'] : 1;
              
              $jml = $_POST['jml'] * $kandungan_val;
              $jml = round(($jml/$kapasitas_nilai), 1);
    
              $this->db('gudangbarang')
              ->where('kode_brng', $kode_brng_val)
              ->where('kd_bangsal', $this->settings->get('farmasi.deporanap'))
              ->update([
                'stok' => $get_gudangbarang['stok'] - $jml
              ]);
    
              $this->db('riwayat_barang_medis')
                ->save([
                  'kode_brng' => $kode_brng_val,
                  'stok_awal' => $get_gudangbarang['stok'],
                  'masuk' => '0',
                  'keluar' => $jml,
                  'stok_akhir' => $get_gudangbarang['stok'] - $jml,
                  'posisi' => 'Pemberian Obat',
                  'tanggal' => $_POST['tgl_perawatan'],
                  'jam' => $_POST['jam_rawat'],
                  'petugas' => $this->core->getUserInfo('fullname', null, true),
                  'kd_bangsal' => $this->settings->get('farmasi.deporanap'),
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
                  'kode_brng' => $kode_brng_val,
                  'h_beli' => $kapasitas['h_beli'],
                  'biaya_obat' => $kapasitas['dasar'],
                  'jml' => $jml,
                  'embalase' => $this->settings->get('farmasi.embalase'),
                  'tuslah' => $this->settings->get('farmasi.tuslah'),
                  'total' => $kapasitas['dasar'] * $jml,
                  'status' => 'Ranap',
                  'kd_bangsal' => $this->settings->get('farmasi.deporanap'),
                  'no_batch' => $get_gudangbarang['no_batch'],
                  'no_faktur' => $get_gudangbarang['no_faktur']
                ]);
    
              $this->db('detail_obat_racikan')
                ->save([
                  'tgl_perawatan' => $_POST['tgl_perawatan'],
                  'jam' => $_POST['jam_rawat'],
                  'no_rawat' => $_POST['no_rawat'],
                  'no_racik' => $no_racik,
                  'kode_brng' => $kode_brng_val
                ]);          
    
            }
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

        if(!empty($get_resep_dokter_racikan)) {
            // Group by no_racik to avoid duplicate inserts
            $racikan_unique = [];
            foreach ($get_resep_dokter_racikan as $row) {
                if (!isset($racikan_unique[$row['no_racik']])) {
                    $racikan_unique[$row['no_racik']] = $row;
                }
            }
            
            foreach ($racikan_unique as $racikan) {
                $this->db('obat_racikan')->save(
                    [
                        'tgl_perawatan' => $tgl_rawat,
                        'jam' => $jam_rawat,
                        'no_rawat' => $_POST['no_rawat'],
                        'no_racik' => $racikan['no_racik'],
                        'nama_racik' => $racikan['nama_racik'],
                        'kd_racik' => $racikan['kd_racik'],
                        'jml_dr' => $racikan['jml_dr'],
                        'aturan_pakai' => $racikan['aturan_pakai'],
                        'keterangan' => $racikan['keterangan']
                    ]
                );
            }
        }

        $embalaseData = isset($_POST['embalase']) ? json_decode($_POST['embalase'], true) : [];
        $tuslahData = isset($_POST['tuslah']) ? json_decode($_POST['tuslah'], true) : [];
        $jumlahData = isset($_POST['jumlah']) ? json_decode($_POST['jumlah'], true) : [];
        $kandunganData = isset($_POST['kandungan']) ? json_decode($_POST['kandungan'], true) : [];

        foreach ($get_resep_dokter as $item) {

          $jumlah = isset($jumlahData[$item['kode_brng']]) ? $jumlahData[$item['kode_brng']] : $item['jml'];
          $kandungan = isset($kandunganData[$item['kode_brng']]) ? $kandunganData[$item['kode_brng']] : (isset($item['kandungan']) ? $item['kandungan'] : 0);

          if(isset($item['no_racik'])) {
             $jumlah_racik = isset($item['jml_dr']) ? $item['jml_dr'] : (isset($jumlahData[$item['kode_brng']]) ? $jumlahData[$item['kode_brng']] : $item['jml']);
             $jumlah = isset($jumlahData[$item['kode_brng']]) ? $jumlahData[$item['kode_brng']] : $item['jml'];
          }

          $get_gudangbarang = $this->db('gudangbarang')->where('kode_brng', $item['kode_brng'])->where('kd_bangsal', $this->settings->get('farmasi.deporanap'))->oneArray();
          $get_databarang = $this->db('databarang')->where('kode_brng', $item['kode_brng'])->oneArray();

          $this->db('gudangbarang')
            ->where('kode_brng', $item['kode_brng'])
            ->where('kd_bangsal', $this->settings->get('farmasi.deporanap'))
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
              'kd_bangsal' => $this->settings->get('farmasi.deporanap'),
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
              'status' => 'Ranap',
              'kd_bangsal' => $this->settings->get('farmasi.deporanap'),
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
      
      $get_gudangbarang = $this->db('gudangbarang')->where('kode_brng', $kode_brng)->where('kd_bangsal', $this->settings->get('farmasi.deporanap'))->oneArray();
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
            ->where('kd_bangsal', $this->settings->get('farmasi.deporanap'))
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
              'kd_bangsal' => $this->settings->get('farmasi.deporanap'),
              'status' => 'Simpan',
              'no_batch' => $get_gudangbarang['no_batch'],
              'no_faktur' => $get_gudangbarang['no_faktur'],
              'keterangan' => $no_rawat . ' ' . $this->core->getRegPeriksaInfo('no_rkm_medis', $no_rawat) . ' ' . $this->core->getPasienInfo('nm_pasien', $this->core->getRegPeriksaInfo('no_rkm_medis', $no_rawat))
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
              'status' => 'Ranap',
              'kd_bangsal' => $this->settings->get('farmasi.deporanap'),
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
        $get_gudangbarang = $this->db('gudangbarang')->where('kode_brng', $_POST['kode_brng'])->where('kd_bangsal', $this->settings->get('farmasi.deporanap'))->oneArray();
        $get_databarang = $this->db('databarang')->where('kode_brng', $_POST['kode_brng'])->oneArray();

        $this->db('gudangbarang')
          ->where('kode_brng', $_POST['kode_brng'])
          ->where('kd_bangsal', $this->settings->get('farmasi.deporanap'))
          ->update([
            'stok' => $get_gudangbarang['stok'] - $_POST['jml']
          ]);

        $this->db('riwayat_barang_medis')
          ->save([
            'kode_brng' => $_POST['kode_brng'],
            'stok_awal' => $get_gudangbarang['stok'],
            'masuk' => '0',
            'keluar' => $_POST['jml'],
            'stok_akhir' => $get_gudangbarang['stok'] - $_POST['jml'],
            'posisi' => 'Pemberian Obat',
            'tanggal' => $_POST['tgl_peresepan'],
            'jam' => $_POST['jam_peresepan'],
            'petugas' => $this->core->getUserInfo('fullname', null, true),
            'kd_bangsal' => $this->settings->get('farmasi.deporanap'),
            'status' => 'Simpan',
            'no_batch' => $get_gudangbarang['no_batch'],
            'no_faktur' => $get_gudangbarang['no_faktur'],
            'keterangan' => $no_rawat . ' ' . $this->core->getRegPeriksaInfo('no_rkm_medis', $no_rawat) . ' ' . $this->core->getPasienInfo('nm_pasien', $this->core->getRegPeriksaInfo('no_rkm_medis', $no_rawat))
          ]);

        // $this->db('resep_dokter')
        //   ->save([
        //     'no_resep' => $_POST['no_resep'],
        //     'kode_brng' => $_POST['kode_brng'],
        //     'jml' => $_POST['jml'],
        //     'aturan_pakai' => $_POST['aturan_pakai']
        //   ]);

        $this->db('detail_pemberian_obat')
          ->save([
            'tgl_perawatan' => $_POST['tgl_peresepan'],
            'jam' => $_POST['jam_peresepan'],
            'no_rawat' => $no_rawat,
            'kode_brng' => $_POST['kode_brng'],
            'h_beli' => $get_databarang['h_beli'],
            'biaya_obat' => $get_databarang['dasar'],
            'jml' => $_POST['jml'],
            'embalase' => $this->settings->get('farmasi.embalase'),
            'tuslah' => $this->settings->get('farmasi.tuslah'),
            'total' => ($get_databarang['dasar'] * $_POST['jml']) + $embalase + $tuslah,
            'status' => 'Ranap',
            'kd_bangsal' => $this->settings->get('farmasi.deporanap'),
            'no_batch' => $get_gudangbarang['no_batch'],
            'no_faktur' => $get_gudangbarang['no_faktur']
          ]);

        $this->db('aturan_pakai')
          ->save([
            'tgl_perawatan' => $_POST['tgl_peresepan'],
            'jam' => $_POST['jam_peresepan'],
            'no_rawat' => $no_rawat,
            'kode_brng' => $_POST['kode_brng'],
            'aturan' => $_POST['aturan_pakai']
          ]);

        $get_databarang['jml'] = $_POST['jml'];
        $get_databarang['aturan_pakai'] = $_POST['aturan_pakai'];
        $get_databarang['ralan'] = ($get_databarang['dasar'] * $_POST['jml']) + $embalase + $tuslah;
        $get_databarang['embalase'] = $embalase;
        $get_databarang['tuslah'] = $tuslah;
        echo json_encode($get_databarang);
      }
      exit();
    }

    public function postHapusObat()
    {
      if(isset($_POST['kd_jenis_prw'])) {
        $kode_brng = $_POST['kd_jenis_prw'];
        $no_resep = $_POST['no_resep'];
        $no_rawat = $_POST['no_rawat'];
        $tgl_peresepan = $_POST['tgl_peresepan'];
        $jam_peresepan = $_POST['jam_peresepan'];

        $jml = 0;

        $check_non_racikan = $this->db('resep_dokter')
            ->where('no_resep', $no_resep)
            ->where('kode_brng', $kode_brng)
            ->oneArray();

        if ($check_non_racikan) {
            $jml = $check_non_racikan['jml'];
            $this->db('resep_dokter')
                ->where('no_resep', $no_resep)
                ->where('kode_brng', $kode_brng)
                ->delete();
        } else {
            $check_racikan = $this->db('resep_dokter_racikan_detail')
                ->where('no_resep', $no_resep)
                ->where('kode_brng', $kode_brng)
                ->oneArray();

            if ($check_racikan) {
                $jml = $check_racikan['jml'];
                $this->db('resep_dokter_racikan_detail')
                    ->where('no_resep', $no_resep)
                    ->where('kode_brng', $kode_brng)
                    ->delete();
            }
        }

        if ($jml > 0) {
            $get_gudangbarang = $this->db('gudangbarang')
                ->where('kode_brng', $kode_brng)
                ->where('kd_bangsal', $this->settings->get('farmasi.deporanap'))
                ->oneArray();

            $this->db('gudangbarang')
                ->where('kode_brng', $kode_brng)
                ->where('kd_bangsal', $this->settings->get('farmasi.deporanap'))
                ->update([
                    'stok' => $get_gudangbarang['stok'] + $jml
                ]);

            $this->db('riwayat_barang_medis')
                ->save([
                    'kode_brng' => $kode_brng,
                    'stok_awal' => $get_gudangbarang['stok'],
                    'masuk' => $jml,
                    'keluar' => '0',
                    'stok_akhir' => $get_gudangbarang['stok'] + $jml,
                    'posisi' => 'Pemberian Obat',
                    'tanggal' => date('Y-m-d'),
                    'jam' => date('H:i:s'),
                    'petugas' => $this->core->getUserInfo('fullname', null, true),
                    'kd_bangsal' => $this->settings->get('farmasi.deporanap'),
                    'status' => 'Hapus',
                    'no_batch' => $get_gudangbarang['no_batch'],
                    'no_faktur' => $get_gudangbarang['no_faktur'],
                    'keterangan' => 'Hapus resep no rawat ' . $no_rawat
                ]);

            $this->db('detail_pemberian_obat')
                ->where('no_rawat', $no_rawat)
                ->where('kode_brng', $kode_brng)
                ->where('tgl_perawatan', $tgl_peresepan)
                ->where('jam', $jam_peresepan)
                ->where('status', 'Ranap')
                ->delete();

            $this->db('aturan_pakai')
                ->where('no_rawat', $no_rawat)
                ->where('kode_brng', $kode_brng)
                ->where('tgl_perawatan', $tgl_peresepan)
                ->where('jam', $jam_peresepan)
                ->delete();
        }
      }
      exit();
    }

    public function postHapusResep()
    {
      if(isset($_POST['no_resep'])) {
        $this->db('resep_dokter')
        ->where('no_resep', $_POST['no_resep'])
        ->delete();
        
        $this->db('resep_dokter_racikan')
        ->where('no_resep', $_POST['no_resep'])
        ->delete();
        
        $this->db('resep_dokter_racikan_detail')
        ->where('no_resep', $_POST['no_resep'])
        ->delete();
        
        $this->db('resep_obat')
        ->where('no_resep', $_POST['no_resep'])
        ->delete();
      }
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
            $get_gudangbarang = $this->db('gudangbarang')->where('kode_brng', $kode_brng)->where('kd_bangsal', $this->settings->get('farmasi.deporanap'))->oneArray();

            $this->db('gudangbarang')
            ->where('kode_brng', $kode_brng)
            ->where('kd_bangsal', $this->settings->get('farmasi.deporanap'))
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
                'kd_bangsal' => $this->settings->get('farmasi.deporanap'),
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
        ->select('resep_obat.no_resep')
        ->select('resep_obat.no_rawat')
        ->select('resep_obat.kd_dokter')
        ->select('resep_obat.tgl_perawatan')
        ->select('resep_obat.jam')
        ->select('resep_obat.tgl_peresepan')
        ->select('resep_obat.jam_peresepan')
        ->select('resep_obat.tgl_penyerahan')
        ->select('resep_obat.jam_penyerahan')
        ->select('resep_obat.status')
        ->select('dokter.nm_dokter')
        ->join('dokter', 'dokter.kd_dokter=resep_obat.kd_dokter')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('resep_obat.status', 'ranap')
        ->group('resep_obat.no_resep')
        ->toArray();

      // Filter out racikan from non-racikan list
      $rows = array_filter($rows, function($row) use ($racikan_nos) {
          return !in_array($row['no_resep'], $racikan_nos);
      });

      $resep = [];
      $jumlah_total_resep = 0;
      foreach ($rows as $row) {
        $bangsal = $this->settings->get('farmasi.deporanap');
        $row['resep_dokter'] = $this->db('resep_dokter')
          ->join('databarang', 'databarang.kode_brng=resep_dokter.kode_brng')
          ->leftJoin('gudangbarang', 'gudangbarang.kode_brng=resep_dokter.kode_brng AND gudangbarang.kd_bangsal = "'.$bangsal.'"')
          ->where('no_resep', $row['no_resep'])
          ->toArray();
        foreach ($row['resep_dokter'] as $value) {
          // Ensure 'jml' and 'dasar' keys exist with default values
          $jml_value = isset($value['jml']) ? floatval($value['jml']) : 0;
          $dasar_value = isset($value['dasar']) ? floatval($value['dasar']) : 0;
          
          $value['ranap'] = ($jml_value * $dasar_value) + $this->settings->get('farmasi.embalase') + $this->settings->get('farmasi.tuslah');
          $jumlah_total_resep += floatval($value['ranap']);
        }

        $row['validasi'] = $this->db('resep_obat')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('tgl_perawatan','!=', $row['tgl_peresepan'])
        ->where('jam', '!=', $row['jam_peresepan'])
        ->where('status', 'ranap')
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
        ->where('resep_obat.status', 'ranap')
        ->group('resep_obat.no_resep')
        ->group('resep_dokter_racikan.no_racik')
        ->toArray();
      $resep_racikan = [];
      $jumlah_total_resep_racikan = 0;
      foreach ($rows_racikan as $row) {
        $bangsal = $this->settings->get('farmasi.deporanap');
        $row['resep_dokter_racikan_detail'] = $this->db('resep_dokter_racikan_detail')
          ->join('databarang', 'databarang.kode_brng=resep_dokter_racikan_detail.kode_brng')
          ->leftJoin('gudangbarang', 'gudangbarang.kode_brng=resep_dokter_racikan_detail.kode_brng AND gudangbarang.kd_bangsal = "'.$bangsal.'"')
          ->where('no_resep', $row['no_resep'])
          ->toArray();
        foreach ($row['resep_dokter_racikan_detail'] as &$value) {
          $value['ranap'] = ($value['jml'] * $value['dasar']) + $this->settings->get('farmasi.embalase') + $this->settings->get('farmasi.tuslah');
          $jumlah_total_resep_racikan += floatval($value['ranap']);
        }

        $row['validasi'] = $this->db('resep_obat')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('tgl_perawatan','!=', $row['tgl_peresepan'])
        ->where('jam', '!=', $row['jam_peresepan'])
        ->where('status', 'ranap')
        ->oneArray();

        $resep_racikan[] = $row;
      }

      $query = $this->db()->pdo()->prepare("SELECT * FROM detail_pemberian_obat WHERE no_rawat = '{$_POST['no_rawat']}' AND status = 'Ranap' AND jam NOT IN (SELECT obat_racikan.jam FROM obat_racikan WHERE obat_racikan.no_rawat = '{$_POST['no_rawat']}' AND obat_racikan.tgl_perawatan = tgl_perawatan UNION ALL SELECT resep_obat.jam_peresepan FROM resep_obat WHERE resep_obat.no_rawat = '{$_POST['no_rawat']}' AND resep_obat.tgl_perawatan = tgl_perawatan UNION ALL SELECT detail_obat_racikan.jam FROM detail_obat_racikan WHERE detail_obat_racikan.no_rawat = '{$_POST['no_rawat']}' AND detail_obat_racikan.tgl_perawatan = tgl_perawatan)");
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
        $row['aturan_pakai'] = isset($aturan_pakai['aturan']) ? $aturan_pakai['aturan'] : '';
        $data_barang = $this->db('databarang')->where('kode_brng', $row['kode_brng'])->oneArray();
        $row['nama_brng'] = isset($data_barang['nama_brng']) ? $data_barang['nama_brng'] : '';
        $row['ranap'] = isset($data_barang['dasar']) ? floatval($data_barang['dasar']) : 0;
        $jumlah_total_obat += floatval($row['total']);
        $detail_pemberian_obat[] = $row;
      }

      $query2 = $this->db()->pdo()->prepare("SELECT obat_racikan.* FROM obat_racikan WHERE obat_racikan.no_rawat = '{$_POST['no_rawat']}' AND obat_racikan.jam NOT IN (SELECT resep_obat.jam FROM resep_obat WHERE resep_obat.no_rawat = '{$_POST['no_rawat']}' AND resep_obat.tgl_perawatan = obat_racikan.tgl_perawatan)");
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
                ->where('detail_pemberian_obat.status', 'Ranap')
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
        ->where('gudangbarang.kd_bangsal', $this->settings->get('farmasi.deporanap'))
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
            ->where('gudangbarang.kd_bangsal', $this->settings->get('farmasi.deporanap'))
            ->like('databarang.nama_brng', '%'.$_GET['nama_brng'].'%')
            ->limit(10)
            ->toArray();

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

    public function getCetakLabel($kode_brng, $no_rawat, $tgl_peresepan, $jam_peresepan, $tipe){
      if($tipe == 'nonracikan') {
        $rows_pemberian_obat = $this->db('detail_pemberian_obat')
        ->join('databarang', 'databarang.kode_brng=detail_pemberian_obat.kode_brng')
        ->join('reg_periksa', 'reg_periksa.no_rawat=detail_pemberian_obat.no_rawat')
        ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
        ->where('detail_pemberian_obat.no_rawat', revertNoRawat($no_rawat))
        ->where('detail_pemberian_obat.status', 'Ranap')
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
          $row['aturan_pakai'] = isset($aturan_pakai['aturan']) ? $aturan_pakai['aturan'] : '';
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
      // echo json_encode($detail_pemberian_obat);
      exit();
    }

    public function getCetakEresep($no_rawat, $tipe, $tgl_peresepan, $jam_peresepan){
      if($tipe == 'nonracikan') {
        $rows_pemberian_obat = $this->db('detail_pemberian_obat')
        ->join('databarang', 'databarang.kode_brng=detail_pemberian_obat.kode_brng')
        ->join('kamar_inap', 'kamar_inap.no_rawat=detail_pemberian_obat.no_rawat')
        ->join('kamar', 'kamar.kd_kamar=kamar_inap.kd_kamar')
        ->join('bangsal', 'bangsal.kd_bangsal=kamar.kd_bangsal')
        ->where('detail_pemberian_obat.no_rawat', revertNoRawat($no_rawat))
        ->where('detail_pemberian_obat.status', 'Ranap')
        ->where('detail_pemberian_obat.tgl_perawatan', $tgl_peresepan)
        ->where('detail_pemberian_obat.jam', $jam_peresepan)
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
          $row['aturan_pakai'] = isset($aturan_pakai['aturan']) ? $aturan_pakai['aturan'] : '';
          $row['keterangan'] = '';
          $detail_pemberian_obat[] = $row;
        }
      }
      if($tipe == 'racikan') {
        $rows_pemberian_obat = $this->db('obat_racikan')
          ->join('reg_periksa', 'reg_periksa.no_rawat=obat_racikan.no_rawat')
          ->join('kamar_inap', 'kamar_inap.no_rawat=obat_racikan.no_rawat')
          ->join('kamar', 'kamar.kd_kamar=kamar_inap.kd_kamar')
          ->join('bangsal', 'bangsal.kd_bangsal=kamar.kd_bangsal')  
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

      exit();
    }

    public function getCetakEresepsss($no_rawat, $tipe, $tgl_peresepan, $jam_peresepan){
      if($tipe == 'nonracikan') {
        $rows_pemberian_obat = $this->db('detail_pemberian_obat')
        ->join('databarang', 'databarang.kode_brng=detail_pemberian_obat.kode_brng')
        ->join('reg_periksa', 'reg_periksa.no_rawat=detail_pemberian_obat.no_rawat')
        ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
        ->where('detail_pemberian_obat.no_rawat', revertNoRawat($no_rawat))
        ->where('detail_pemberian_obat.status', 'Ranap')
        ->where('detail_pemberian_obat.tgl_perawatan', $tgl_peresepan)
        ->where('detail_pemberian_obat.jam', $jam_peresepan)
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
          $row['aturan_pakai'] = isset($aturan_pakai['aturan']) ? $aturan_pakai['aturan'] : '';
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
        echo $this->draw(MODULES.'/apotek_ranap/js/admin/apotek_ranap.js');
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
        $this->core->addJS(url([ADMIN, 'apotek_ranap', 'javascript']), 'footer');
    }

    public function apiResepList()
    {
        $username = $this->core->checkAuth('GET');
        if (!$this->core->checkPermission($username, 'can_read', 'apotek_ranap')) {
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
                $query->where('resep_obat.status', 'ranap')
                      ->orWhere('resep_obat.status', 'Ranap');
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

        foreach ($data as &$row) {
            $row['detail'] = $this->apiShowDetail('obat', $row['no_rawat'], $row['no_resep']);
            $row['racikan'] = $this->apiShowDetail('racikan', $row['no_rawat'], $row['no_resep']);
        }

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
        if (!$this->core->checkPermission($username, 'can_read', 'apotek_ranap')) {
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
                    ->where('resep_obat.no_rawat', $no_rawat)
                    ->where(function($query) {
                        $query->where('resep_obat.status', 'ranap')
                              ->orWhere('resep_obat.status', 'Ranap');
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
                    ->where('resep_obat.no_rawat', $no_rawat)
                    ->where(function($query) {
                        $query->where('resep_obat.status', 'ranap')
                              ->orWhere('resep_obat.status', 'Ranap');
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

}
