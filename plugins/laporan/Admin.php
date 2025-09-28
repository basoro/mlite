<?php

namespace Plugins\Laporan;

use Systems\AdminModule;
use Systems\Lib\QueryWrapper;

class Admin extends AdminModule
{
    public function navigation()
    {
        return [
            'Kelola' => 'manage',
            'Laporan TB' => 'laporantb',
            'Laporan SEP BPJS' => 'laporansep',
            'Laporan Antrian Online' => 'laporanantrian'
        ];
    }

    public function getManage()
    {
        $sub_modules = [
            ['name' => 'Laporan TB', 'url' => url([ADMIN, 'laporan', 'laporantb']), 'icon' => 'fa fa-file-text-o', 'desc' => 'Laporan data tuberkulosis'],
            ['name' => 'Laporan SEP BPJS', 'url' => url([ADMIN, 'laporan', 'laporansep']), 'icon' => 'fa fa-file-text-o', 'desc' => 'Laporan SEP BPJS'],
            ['name' => 'Laporan Antrian Online', 'url' => url([ADMIN, 'laporan', 'laporanantrian']), 'icon' => 'fa fa-file-text-o', 'desc' => 'Laporan antrian online']
        ];

        return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
    }

    public function anyLaporanTb()
    {
        $this->_addHeaderFiles();
        $tgl_awal = isset_or($_POST['tgl_awal'], date('Y')."-01-01");
        $tgl_akhir = isset_or($_POST['tgl_akhir'], date('Y')."-12-31");
        
        $query = $this->db('data_tb')
            ->join('reg_periksa', 'reg_periksa.no_rawat = data_tb.no_rawat')
            ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
            ->select([
                'data_tb.no_rawat',
                'reg_periksa.no_rkm_medis',
                'pasien.nm_pasien',
                'reg_periksa.tgl_registrasi',
                'data_tb.tipe_diagnosis',
                'data_tb.klasifikasi_lokasi_anatomi',
                'data_tb.hasil_akhir_pengobatan',
                'data_tb.tanggal_mulai_pengobatan'
            ])
            ->where('reg_periksa.tgl_registrasi', '>=', $tgl_awal)
            ->where('reg_periksa.tgl_registrasi', '<=', $tgl_akhir)
            ->toArray();

        // Handle Excel export
        if (isset($_POST['export']) && $_POST['export'] == 'excel') {
            return $this->exportToExcel($query, 'Laporan_TB_' . $tgl_awal . '_' . $tgl_akhir);
        }

        return $this->draw('laporan_tb.html', ['laporan' => $query, 'tgl_awal' => $tgl_awal, 'tgl_akhir' => $tgl_akhir]);
    }

    public function anyLaporanSep()
    {
        $this->_addHeaderFiles();
        $tgl_awal = isset_or($_POST['tgl_awal'], date('Y')."-01-01");
        $tgl_akhir = isset_or($_POST['tgl_akhir'], date('Y')."-12-31");
        
        $query = $this->db('bridging_sep')
            ->join('reg_periksa', 'reg_periksa.no_rawat = bridging_sep.no_rawat')
            ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
            ->select([
                'bridging_sep.no_sep',
                'bridging_sep.no_rawat',
                'reg_periksa.no_rkm_medis',
                'pasien.nm_pasien',
                'bridging_sep.no_kartu',
                'bridging_sep.tglsep',
                'bridging_sep.nmpolitujuan as poli',
                'bridging_sep.nmdiagnosaawal as diagnosa',
                'bridging_sep.jnspelayanan'
            ])
            ->where('bridging_sep.tglsep', '>=', $tgl_awal)
            ->where('bridging_sep.tglsep', '<=', $tgl_akhir)
            ->toArray();

        // Handle Excel export
        if (isset($_POST['export']) && $_POST['export'] == 'excel') {
            return $this->exportSepToExcel($query, 'Laporan_SEP_' . $tgl_awal . '_' . $tgl_akhir);
        }

        return $this->draw('laporan_sep.html', ['laporan' => $query, 'tgl_awal' => $tgl_awal, 'tgl_akhir' => $tgl_akhir]);
    }

    public function anyLaporanAntrian()
    {
        $this->_addHeaderFiles();
        $tgl_awal = isset_or($_POST['tgl_awal'], date('Y')."-01-01");
        $tgl_akhir = isset_or($_POST['tgl_akhir'], date('Y')."-12-31");
        
        $query = $this->db('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
            ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
            ->join('dokter', 'dokter.kd_dokter = reg_periksa.kd_dokter')
            ->select([
                'reg_periksa.no_rawat',
                'reg_periksa.no_rkm_medis',
                'pasien.nm_pasien',
                'reg_periksa.tgl_registrasi',
                'reg_periksa.jam_reg',
                'poliklinik.nm_poli',
                'dokter.nm_dokter',
                'reg_periksa.status_bayar',
                'reg_periksa.stts'
            ])
            ->where('reg_periksa.tgl_registrasi', '>=', $tgl_awal)
            ->where('reg_periksa.tgl_registrasi', '<=', $tgl_akhir)
            ->where('reg_periksa.status_lanjut', '=', 'Ralan')
            ->toArray();

        return $this->draw('laporan_antrian.html', ['laporan' => $query, 'tgl_awal' => $tgl_awal, 'tgl_akhir' => $tgl_akhir]);
    }

    public function getCetakPdfSemua()
    {
        $tgl_awal = isset_or($_GET['tgl_awal'], date('Y').'-01-01');
        $tgl_akhir = isset_or($_GET['tgl_akhir'], date('Y').'-12-31');
        
        $query = $this->db('data_tb')
            ->join('reg_periksa', 'reg_periksa.no_rawat = data_tb.no_rawat')
            ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
            ->select([
                'data_tb.no_rawat',
                'reg_periksa.no_rkm_medis',
                'pasien.nm_pasien',
                'reg_periksa.tgl_registrasi',
                'data_tb.tipe_diagnosis',
                'data_tb.klasifikasi_lokasi_anatomi',
                'data_tb.hasil_akhir_pengobatan',
                'data_tb.tanggal_mulai_pengobatan'
            ])
            ->where('reg_periksa.tgl_registrasi', '>=', $tgl_awal)
            ->where('reg_periksa.tgl_registrasi', '<=', $tgl_akhir)
            ->toArray();

        $settings = $this->settings('settings');
        
        return $this->draw('cetak_pdf_semua.html', [
            'laporan' => $query,
            'tgl_awal' => $tgl_awal,
            'tgl_akhir' => $tgl_akhir,
            'settings' => $settings
        ]);
    }

    public function getCetakPdfIndividual()
    {
        $no_rawat = isset($_GET['no_rawat']) ? $_GET['no_rawat'] : null;
        
        if (!$no_rawat) {
            exit('Parameter no_rawat tidak ditemukan');
        }
                
        $data = $this->db('data_tb')
            ->join('reg_periksa', 'reg_periksa.no_rawat = data_tb.no_rawat')
            ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
            ->join('dokter', 'dokter.kd_dokter = reg_periksa.kd_dokter')
            ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
            ->select([
                'data_tb.*',
                'reg_periksa.no_rkm_medis',
                'reg_periksa.tgl_registrasi',
                'reg_periksa.jam_reg',
                'pasien.nm_pasien',
                'pasien.jk',
                'pasien.tmp_lahir',
                'pasien.tgl_lahir',
                'pasien.alamat',
                'pasien.no_tlp',
                'dokter.nm_dokter',
                'poliklinik.nm_poli'
            ])
            ->where('data_tb.no_rawat', $no_rawat)
            ->oneArray();

        if(!$data) {
            exit('Data tidak ditemukan');
        }

        $settings = $this->settings('settings');
        
        return $this->draw('cetak_pdf_individual.html', [
            'data' => $data,
            'settings' => $settings
        ]);
    }

    public function getCetakPdfSep()
    {
        $tgl_awal = isset_or($_GET['tgl_awal'], date('Y').'-01-01');
        $tgl_akhir = isset_or($_GET['tgl_akhir'], date('Y').'-12-31');
        
        $query = $this->db('bridging_sep')
            ->join('reg_periksa', 'reg_periksa.no_rawat = bridging_sep.no_rawat')
            ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
            ->select([
                'bridging_sep.no_sep',
                'bridging_sep.no_rawat',
                'reg_periksa.no_rkm_medis',
                'pasien.nm_pasien',
                'bridging_sep.no_kartu',
                'bridging_sep.tglsep',
                'bridging_sep.nmpolitujuan as poli',
                'bridging_sep.nmdiagnosaawal as diagnosa',
                'bridging_sep.jnspelayanan'
            ])
            ->where('bridging_sep.tglsep', '>=', $tgl_awal)
            ->where('bridging_sep.tglsep', '<=', $tgl_akhir)
            ->toArray();

        $settings = $this->settings('settings');
        
        return $this->draw('cetak_pdf_sep.html', [
            'laporan' => $query,
            'tgl_awal' => $tgl_awal,
            'tgl_akhir' => $tgl_akhir,
            'settings' => $settings
        ]);
    }

    public function getCetakPdfAntrian()
    {
        $tgl_awal = isset_or($_GET['tgl_awal'], date('Y').'-01-01');
        $tgl_akhir = isset_or($_GET['tgl_akhir'], date('Y').'-12-31');
        
        $query = $this->db('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
            ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
            ->join('dokter', 'dokter.kd_dokter = reg_periksa.kd_dokter')
            ->select([
                'reg_periksa.no_rawat',
                'reg_periksa.no_rkm_medis',
                'pasien.nm_pasien',
                'reg_periksa.tgl_registrasi',
                'reg_periksa.jam_reg',
                'poliklinik.nm_poli',
                'dokter.nm_dokter',
                'reg_periksa.status_bayar',
                'reg_periksa.stts'
            ])
            ->where('reg_periksa.tgl_registrasi', '>=', $tgl_awal)
            ->where('reg_periksa.tgl_registrasi', '<=', $tgl_akhir)
            ->where('reg_periksa.status_lanjut', '=', 'Ralan')
            ->toArray();

        $settings = $this->settings('settings');
        
        return $this->draw('cetak_pdf_antrian.html', [
            'laporan' => $query,
            'tgl_awal' => $tgl_awal,
            'tgl_akhir' => $tgl_akhir,
            'settings' => $settings
        ]);
    }

    private function exportToExcel($data, $filename)
    {
        // Set headers for Excel download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        // Create Excel content using simple CSV format that Excel can read
        $output = "\xEF\xBB\xBF"; // UTF-8 BOM
        
        // Header row
        $headers = [
            'No',
            'No. Rawat',
            'No. RM',
            'Nama Pasien',
            'Tanggal Registrasi',
            'Tipe Diagnosis',
            'Lokasi Anatomi',
            'Hasil Akhir Pengobatan',
            'Tanggal Mulai Pengobatan'
        ];
        
        $output .= implode("\t", $headers) . "\n";
        
        // Data rows
        $no = 1;
        foreach ($data as $row) {
            $dataRow = [
                $no++,
                $row['no_rawat'],
                $row['no_rkm_medis'],
                $row['nm_pasien'],
                $row['tgl_registrasi'],
                $row['tipe_diagnosis'],
                $row['klasifikasi_lokasi_anatomi'],
                $row['hasil_akhir_pengobatan'],
                $row['tanggal_mulai_pengobatan']
            ];
            
            $output .= implode("\t", $dataRow) . "\n";
        }
        
        echo $output;
        exit;
    }

    private function exportSepToExcel($data, $filename)
    {
        // Set headers for Excel download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        // Create Excel content using simple CSV format that Excel can read
        $output = "\xEF\xBB\xBF"; // UTF-8 BOM
        
        // Header row
        $headers = [
            'No',
            'No. SEP',
            'No. Rawat',
            'No. RM',
            'Nama Pasien',
            'No. Kartu',
            'Tanggal SEP',
            'Poli Tujuan',
            'Diagnosa',
            'Jenis Pelayanan'
        ];
        
        $output .= implode("\t", $headers) . "\n";
        
        // Data rows
        $no = 1;
        foreach ($data as $row) {
            $jnsPelayanan = ($row['jnspelayanan'] == '1') ? 'Rawat Inap' : 'Rawat Jalan';
            
            $dataRow = [
                $no++,
                $row['no_sep'],
                $row['no_rawat'],
                $row['no_rkm_medis'],
                $row['nm_pasien'],
                $row['no_kartu'],
                $row['tglsep'],
                $row['poli'],
                $row['diagnosa'],
                $jnsPelayanan
            ];
            
            $output .= implode("\t", $dataRow) . "\n";
        }
        
        echo $output;
        exit;
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
        $this->core->addJS(url('assets/jscripts/jspdf.min.js'));
        $this->core->addJS(url('assets/jscripts/jspdf.plugin.autotable.min.js'));
        $this->core->addJS(url('assets/jscripts/xlsx.js'));
    }
}