<?php

namespace Plugins\Laporan;

use Systems\AdminModule;
use Systems\Lib\QueryWrapper;

class Admin extends AdminModule
{
    /**
     * Jumlah baris per halaman laporan Audit & Review RM (paginasi server-side)
     */
    const AUDIT_REVIEW_PER_HALAMAN = 50;

    public function navigation()
    {
        return [
            'Kelola' => 'manage',
            'Laporan TB' => 'laporantb',
            'Laporan SEP BPJS' => 'laporansep',
            'Laporan Antrian Online' => 'laporanantrian',
            '10 Besar Penyakit Ralan' => 'laporanpenyakitralan',
            '10 Besar Penyakit Ranap' => 'laporanpenyakitranap',
            'Audit & Review RM' => 'auditdanreview',
            'Penilaian Efektifitas ERM' => 'penilaianefektifitas'
        ];
    }

    public function getManage()
    {
        $sub_modules = [
            ['name' => 'Laporan TB', 'url' => url([ADMIN, 'laporan', 'laporantb']), 'icon' => 'fa fa-file-text-o', 'desc' => 'Laporan data tuberkulosis'],
            ['name' => 'Laporan SEP BPJS', 'url' => url([ADMIN, 'laporan', 'laporansep']), 'icon' => 'fa fa-file-text-o', 'desc' => 'Laporan SEP BPJS'],
            ['name' => 'Laporan Antrian Online', 'url' => url([ADMIN, 'laporan', 'laporanantrian']), 'icon' => 'fa fa-file-text-o', 'desc' => 'Laporan antrian online'],
            ['name' => '10 Besar Penyakit Ralan', 'url' => url([ADMIN, 'laporan', 'laporanpenyakitralan']), 'icon' => 'fa fa-bar-chart', 'desc' => 'Laporan 10 besar penyakit rawat jalan'],
            ['name' => '10 Besar Penyakit Ranap', 'url' => url([ADMIN, 'laporan', 'laporanpenyakitranap']), 'icon' => 'fa fa-bar-chart', 'desc' => 'Laporan 10 besar penyakit rawat inap'],
            ['name' => 'Audit & Review RM', 'url' => url([ADMIN, 'laporan', 'auditdanreview']), 'icon' => 'fa fa-clipboard', 'desc' => 'Audit kelengkapan dokumen rekam medis per kunjungan'],
            ['name' => 'Penilaian Efektifitas ERM', 'url' => url([ADMIN, 'laporan', 'penilaianefektifitas']), 'icon' => 'fa fa-line-chart', 'desc' => 'Penilaian efektivitas elektronik rekam medis']
        ];

        return $this->draw('manage.html', ['sub_modules' => htmlspecialchars_array($sub_modules)]);
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

        return $this->draw('laporan_tb.html', ['laporan' => htmlspecialchars_array($query), 'tgl_awal' => htmlspecialchars_array($tgl_awal), 'tgl_akhir' => htmlspecialchars_array($tgl_akhir)]);
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

        return $this->draw('laporan_sep.html', ['laporan' => htmlspecialchars_array($query), 'tgl_awal' => htmlspecialchars_array($tgl_awal), 'tgl_akhir' => htmlspecialchars_array($tgl_akhir)]);
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

        return $this->draw('laporan_antrian.html', ['laporan' => htmlspecialchars_array($query), 'tgl_awal' => htmlspecialchars_array($tgl_awal), 'tgl_akhir' => htmlspecialchars_array($tgl_akhir)]);
    }

    public function anyLaporanPenyakitRalan()
    {
        $this->_addHeaderFiles();
        $tgl_awal = isset_or($_POST['tgl_awal'], date('Y-m-01'));
        $tgl_akhir = isset_or($_POST['tgl_akhir'], date('Y-m-d'));

        $query = $this->db('diagnosa_pasien')
            ->join('penyakit', 'penyakit.kd_penyakit = diagnosa_pasien.kd_penyakit')
            ->join('reg_periksa', 'reg_periksa.no_rawat = diagnosa_pasien.no_rawat')
            ->select([
                'diagnosa_pasien.kd_penyakit',
                'penyakit.nm_penyakit',
                'count(diagnosa_pasien.kd_penyakit) as jumlah'
            ])
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->where('reg_periksa.tgl_registrasi', '>=', $tgl_awal)
            ->where('reg_periksa.tgl_registrasi', '<=', $tgl_akhir)
            ->group('diagnosa_pasien.kd_penyakit')
            ->desc('jumlah')
            ->limit(10)
            ->toArray();

        // Handle Excel export
        if (isset($_POST['export']) && $_POST['export'] == 'excel') {
            return $this->exportPenyakitToExcel($query, 'Laporan_10_Besar_Penyakit_Ralan_' . $tgl_awal . '_' . $tgl_akhir);
        }

        return $this->draw('laporan_penyakit_ralan.html', ['laporan' => htmlspecialchars_array($query), 'tgl_awal' => htmlspecialchars_array($tgl_awal), 'tgl_akhir' => htmlspecialchars_array($tgl_akhir)]);
    }

    public function anyLaporanPenyakitRanap()
    {
        $this->_addHeaderFiles();
        $tgl_awal = isset_or($_POST['tgl_awal'], date('Y-m-01'));
        $tgl_akhir = isset_or($_POST['tgl_akhir'], date('Y-m-d'));

        $query = $this->db('diagnosa_pasien')
            ->join('penyakit', 'penyakit.kd_penyakit = diagnosa_pasien.kd_penyakit')
            ->join('reg_periksa', 'reg_periksa.no_rawat = diagnosa_pasien.no_rawat')
            ->select([
                'diagnosa_pasien.kd_penyakit',
                'penyakit.nm_penyakit',
                'count(diagnosa_pasien.kd_penyakit) as jumlah'
            ])
            ->where('reg_periksa.status_lanjut', 'Ranap')
            ->where('reg_periksa.tgl_registrasi', '>=', $tgl_awal)
            ->where('reg_periksa.tgl_registrasi', '<=', $tgl_akhir)
            ->group('diagnosa_pasien.kd_penyakit')
            ->desc('jumlah')
            ->limit(10)
            ->toArray();

        // Handle Excel export
        if (isset($_POST['export']) && $_POST['export'] == 'excel') {
            return $this->exportPenyakitToExcel($query, 'Laporan_10_Besar_Penyakit_Ranap_' . $tgl_awal . '_' . $tgl_akhir);
        }

        return $this->draw('laporan_penyakit_ranap.html', ['laporan' => htmlspecialchars_array($query), 'tgl_awal' => htmlspecialchars_array($tgl_awal), 'tgl_akhir' => htmlspecialchars_array($tgl_akhir)]);
    }

    public function anyAuditDanReview()
    {
        $this->_addHeaderFiles();
        $tgl_awal = isset_or($_POST['tgl_awal'], date('Y-m-d'));
        $tgl_akhir = isset_or($_POST['tgl_akhir'], date('Y-m-d'));
        $status = isset_or($_POST['status'], 'Semua');
        if (!in_array($status, ['Semua', 'Ralan', 'Ranap'], true)) {
            $status = 'Semua';
        }

        // Handle Excel export (tetap memuat seluruh baris)
        if (isset($_POST['export']) && $_POST['export'] == 'excel') {
            $data = $this->_auditReviewData($tgl_awal, $tgl_akhir, $status);

            return $this->exportAuditReviewToExcel($data['laporan'], 'Audit_Review_Rekam_Medis_' . $tgl_awal . '_' . $tgl_akhir);
        }

        // Tampilan memakai paginasi server-side agar tetap ringan meski datanya sangat banyak
        $halaman = (int) isset_or($_POST['halaman'], 1);
        $data = $this->_auditReviewData($tgl_awal, $tgl_akhir, $status, $halaman, self::AUDIT_REVIEW_PER_HALAMAN);

        return $this->draw('laporan_audit_review.html', [
            'laporan' => htmlspecialchars_array($data['laporan']),
            'ringkasan' => htmlspecialchars_array($data['ringkasan']),
            'halaman' => htmlspecialchars_array($data['halaman']),
            'total_halaman' => $data['total_halaman'],
            'total_data' => $data['total_data'],
            'dari' => htmlspecialchars_array($data['dari']),
            'sampai' => $data['sampai'],
            'daftar_halaman' => htmlspecialchars_array($data['daftar_halaman']),
            'tgl_awal' => htmlspecialchars_array($tgl_awal),
            'tgl_akhir' => htmlspecialchars_array($tgl_akhir),
            'status' => $status
        ]);
    }

    public function anyPenilaianEfektifitas()
    {
        $this->_addHeaderFiles();
        $tgl_awal = isset_or($_POST['tgl_awal'], date('Y-m-01'));
        $tgl_akhir = isset_or($_POST['tgl_akhir'], date('Y-m-d'));
        $status = isset_or($_POST['status'], 'Semua');
        if (!in_array($status, ['Semua', 'Ralan', 'Ranap'], true)) {
            $status = 'Semua';
        }

        $data = $this->_penilaianErmData($tgl_awal, $tgl_akhir, $status);

        // Handle Excel export
        if (isset($_POST['export']) && $_POST['export'] == 'excel') {
            return $this->exportPenilaianErmToExcel($data, 'Penilaian_Efektifitas_ERM_' . $tgl_awal . '_' . $tgl_akhir);
        }

        return $this->draw('laporan_penilaian_efektifitas.html', [
            'indikator' => htmlspecialchars_array($data['indikator']),
            'per_status' => htmlspecialchars_array($data['per_status']),
            'skor' => htmlspecialchars_array($data['skor']),
            'predikat' => htmlspecialchars_array($data['predikat']),
            'total_kunjungan' => htmlspecialchars_array($data['total_kunjungan']),
            'total_ranap' => htmlspecialchars_array($data['total_ranap']),
            'tgl_awal' => htmlspecialchars_array($tgl_awal),
            'tgl_akhir' => htmlspecialchars_array($tgl_akhir),
            'status' => $status
        ]);
    }

    public function getCetakPdfAuditReview()
    {
        $tgl_awal = isset_or($_GET['tgl_awal'], date('Y-m-01'));
        $tgl_akhir = isset_or($_GET['tgl_akhir'], date('Y-m-d'));
        $status = isset_or($_GET['status'], 'Semua');
        if (!in_array($status, ['Semua', 'Ralan', 'Ranap'], true)) {
            $status = 'Semua';
        }

        $data = $this->_auditReviewData($tgl_awal, $tgl_akhir, $status);
        $settings = $this->settings('settings');

        echo $this->draw('cetak_pdf_audit_review.html', [
            'laporan' => htmlspecialchars_array($data['laporan']),
            'ringkasan' => htmlspecialchars_array($data['ringkasan']),
            'tgl_awal' => htmlspecialchars($tgl_awal, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            'tgl_akhir' => htmlspecialchars($tgl_akhir, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            'status' => htmlspecialchars_array($status),
            'settings' => htmlspecialchars_array($settings)
        ]);
        exit();
    }

    public function getCetakPdfPenilaianEfektifitas()
    {
        $tgl_awal = isset_or($_GET['tgl_awal'], date('Y-m-01'));
        $tgl_akhir = isset_or($_GET['tgl_akhir'], date('Y-m-d'));
        $status = isset_or($_GET['status'], 'Semua');
        if (!in_array($status, ['Semua', 'Ralan', 'Ranap'], true)) {
            $status = 'Semua';
        }

        $data = $this->_penilaianErmData($tgl_awal, $tgl_akhir, $status);
        $settings = $this->settings('settings');

        echo $this->draw('cetak_pdf_penilaian_efektifitas.html', [
            'indikator' => htmlspecialchars_array($data['indikator']),
            'per_status' => htmlspecialchars_array($data['per_status']),
            'skor' => htmlspecialchars_array($data['skor']),
            'predikat' => htmlspecialchars_array($data['predikat']),
            'total_kunjungan' => htmlspecialchars_array($data['total_kunjungan']),
            'total_ranap' => htmlspecialchars_array($data['total_ranap']),
            'tgl_awal' => htmlspecialchars($tgl_awal, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            'tgl_akhir' => htmlspecialchars($tgl_akhir, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            'status' => htmlspecialchars_array($status),
            'settings' => htmlspecialchars_array($settings)
        ]);
        exit();
    }

    /**
     * Data audit kelengkapan rekam medis per kunjungan
     *
     * Ringkasan dihitung lewat satu query agregat sehingga tetap ringan meski rentang
     * tanggal memuat puluhan ribu kunjungan. $halaman/$per_halaman hanya untuk tampilan;
     * cetak & export tetap memakai seluruh baris (default $per_halaman = 0).
     */
    private function _auditReviewData($tgl_awal, $tgl_akhir, $status, $halaman = 1, $per_halaman = 0)
    {
        $ringkasan = $this->_auditReviewRingkasan($tgl_awal, $tgl_akhir, $status);

        $total_data = (int) $ringkasan['total'];
        $total_halaman = $per_halaman > 0 ? max(1, (int) ceil($total_data / $per_halaman)) : 1;
        $halaman = max(1, min((int) $halaman, $total_halaman));
        $offset = $per_halaman > 0 ? ($halaman - 1) * $per_halaman : 0;

        $query = $this->_auditReviewQuery($tgl_awal, $tgl_akhir, $status)
            ->select([
                'reg_periksa.no_rawat',
                'reg_periksa.no_rkm_medis',
                'reg_periksa.tgl_registrasi',
                'reg_periksa.status_lanjut',
                'pasien.nm_pasien',
                'poliklinik.nm_poli',
                'dokter.nm_dokter',
                '(CASE WHEN EXISTS(SELECT 1 FROM pemeriksaan_ralan WHERE pemeriksaan_ralan.no_rawat = reg_periksa.no_rawat) OR EXISTS(SELECT 1 FROM pemeriksaan_ranap WHERE pemeriksaan_ranap.no_rawat = reg_periksa.no_rawat) THEN 1 ELSE 0 END) AS ada_pemeriksaan',
                '(CASE WHEN EXISTS(SELECT 1 FROM diagnosa_pasien WHERE diagnosa_pasien.no_rawat = reg_periksa.no_rawat) THEN 1 ELSE 0 END) AS ada_diagnosa',
                '(CASE WHEN EXISTS(SELECT 1 FROM resep_obat WHERE resep_obat.no_rawat = reg_periksa.no_rawat) THEN 1 ELSE 0 END) AS ada_resep',
                '(CASE WHEN EXISTS(SELECT 1 FROM berkas_digital_perawatan WHERE berkas_digital_perawatan.no_rawat = reg_periksa.no_rawat) THEN 1 ELSE 0 END) AS ada_berkas',
                '(CASE WHEN EXISTS(SELECT 1 FROM resume_pasien WHERE resume_pasien.no_rawat = reg_periksa.no_rawat) OR EXISTS(SELECT 1 FROM resume_pasien_ranap WHERE resume_pasien_ranap.no_rawat = reg_periksa.no_rawat) THEN 1 ELSE 0 END) AS ada_resume'
            ]);

        if ($per_halaman > 0) {
            $query->limit($per_halaman)->offset($offset);
        }

        $rows = $query->desc('reg_periksa.tgl_registrasi')->desc('reg_periksa.no_rawat')->toArray();

        $laporan = [];

        foreach ($rows as $row) {
            $komponen = [
                'Anamnesis & Pemeriksaan' => (int) $row['ada_pemeriksaan'],
                'Diagnosis' => (int) $row['ada_diagnosa'],
                'Peresepan' => (int) $row['ada_resep'],
                'Berkas Digital' => (int) $row['ada_berkas']
            ];

            // Resume medis hanya berlaku untuk kunjungan rawat inap
            if ($row['status_lanjut'] == 'Ranap') {
                $komponen['Resume Medis'] = (int) $row['ada_resume'];
            }

            $terpenuhi = array_sum($komponen);
            $skor = round($terpenuhi / count($komponen) * 100, 1);

            $kurang = [];
            foreach ($komponen as $nama => $ada) {
                if (!$ada) {
                    $kurang[] = $nama;
                }
            }

            $row['komponen_total'] = count($komponen);
            $row['komponen_terpenuhi'] = $terpenuhi;
            $row['skor_kelengkapan'] = $skor;
            $row['hasil_audit'] = empty($kurang) ? 'Lengkap' : 'Kurang Lengkap';
            $row['komponen_kurang'] = empty($kurang) ? '-' : implode(', ', $kurang);

            $laporan[] = $row;
        }

        return [
            'laporan' => $laporan,
            'ringkasan' => $ringkasan,
            'total_data' => $total_data,
            'total_halaman' => $total_halaman,
            'halaman' => $halaman,
            'offset' => $offset,
            'dari' => $total_data > 0 ? $offset + 1 : 0,
            'sampai' => $total_data > 0 ? min($offset + count($laporan), $total_data) : 0,
            'daftar_halaman' => $this->_auditReviewHalamanList($halaman, $total_halaman)
        ];
    }

    /**
     * Query dasar audit review (join + filter rentang tanggal & status layanan)
     */
    private function _auditReviewQuery($tgl_awal, $tgl_akhir, $status)
    {
        $query = $this->db('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
            ->leftJoin('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
            ->leftJoin('dokter', 'dokter.kd_dokter = reg_periksa.kd_dokter')
            ->where('reg_periksa.tgl_registrasi', '>=', $tgl_awal)
            ->where('reg_periksa.tgl_registrasi', '<=', $tgl_akhir);

        if ($status !== 'Semua') {
            $query->where('reg_periksa.status_lanjut', '=', $status);
        }

        return $query;
    }

    /**
     * Ringkasan audit lewat satu query agregat — tetap ringan meski kunjungan puluhan ribu
     */
    private function _auditReviewRingkasan($tgl_awal, $tgl_akhir, $status)
    {
        $pemeriksaan = "(CASE WHEN EXISTS(SELECT 1 FROM pemeriksaan_ralan WHERE pemeriksaan_ralan.no_rawat = reg_periksa.no_rawat) OR EXISTS(SELECT 1 FROM pemeriksaan_ranap WHERE pemeriksaan_ranap.no_rawat = reg_periksa.no_rawat) THEN 1 ELSE 0 END)";
        $diagnosa = "(CASE WHEN EXISTS(SELECT 1 FROM diagnosa_pasien WHERE diagnosa_pasien.no_rawat = reg_periksa.no_rawat) THEN 1 ELSE 0 END)";
        $resep = "(CASE WHEN EXISTS(SELECT 1 FROM resep_obat WHERE resep_obat.no_rawat = reg_periksa.no_rawat) THEN 1 ELSE 0 END)";
        $berkas = "(CASE WHEN EXISTS(SELECT 1 FROM berkas_digital_perawatan WHERE berkas_digital_perawatan.no_rawat = reg_periksa.no_rawat) THEN 1 ELSE 0 END)";
        $resume = "(CASE WHEN reg_periksa.status_lanjut = 'Ranap' AND (EXISTS(SELECT 1 FROM resume_pasien WHERE resume_pasien.no_rawat = reg_periksa.no_rawat) OR EXISTS(SELECT 1 FROM resume_pasien_ranap WHERE resume_pasien_ranap.no_rawat = reg_periksa.no_rawat)) THEN 1 ELSE 0 END)";
        $terpenuhi = "($pemeriksaan + $diagnosa + $resep + $berkas + $resume)";
        $komponen = "(CASE WHEN reg_periksa.status_lanjut = 'Ranap' THEN 5 ELSE 4 END)";

        $row = $this->_auditReviewQuery($tgl_awal, $tgl_akhir, $status)
            ->select([
                'total' => 'COUNT(reg_periksa.no_rawat)',
                'lengkap' => "SUM(CASE WHEN $terpenuhi = $komponen THEN 1 ELSE 0 END)",
                // skor per kunjungan dibulatkan 1 desimal dulu baru dirata-ratakan,
                // sama persis dengan cara hitung skor per baris di tampilan
                'rata_rata_skor' => "COALESCE(ROUND(AVG(ROUND($terpenuhi * 100.0 / $komponen, 1)), 1), 0)"
            ])
            ->oneArray();

        $total = (int) $row['total'];

        return [
            'total' => $total,
            'lengkap' => (int) $row['lengkap'],
            'kurang' => $total - (int) $row['lengkap'],
            'rata_rata_skor' => round((float) $row['rata_rata_skor'], 1)
        ];
    }

    /**
     * Nomor halaman untuk kontrol paginasi (memakai elipsis bila jumlah halaman banyak)
     */
    private function _auditReviewHalamanList($halaman, $total_halaman)
    {
        if ($total_halaman <= 9) {
            return range(1, $total_halaman);
        }

        $list = [1];
        $mulai = max(2, $halaman - 2);
        $selesai = min($total_halaman - 1, $halaman + 2);
        if ($mulai > 2) {
            $list[] = '...';
        }
        for ($i = $mulai; $i <= $selesai; $i++) {
            $list[] = $i;
        }
        if ($selesai < $total_halaman - 1) {
            $list[] = '...';
        }
        $list[] = $total_halaman;

        return $list;
    }

    /**
     * Data penilaian efektivitas ERM (indikator keseluruhan + rekap per status layanan)
     */
    private function _penilaianErmData($tgl_awal, $tgl_akhir, $status)
    {
        $query = $this->db('reg_periksa')
            ->select([
                'total_kunjungan' => 'COUNT(reg_periksa.no_rawat)',
                'total_ranap' => "SUM(CASE WHEN reg_periksa.status_lanjut = 'Ranap' THEN 1 ELSE 0 END)",
                'dengan_pemeriksaan' => 'SUM(CASE WHEN EXISTS(SELECT 1 FROM pemeriksaan_ralan WHERE pemeriksaan_ralan.no_rawat = reg_periksa.no_rawat) OR EXISTS(SELECT 1 FROM pemeriksaan_ranap WHERE pemeriksaan_ranap.no_rawat = reg_periksa.no_rawat) THEN 1 ELSE 0 END)',
                'dengan_diagnosa' => 'SUM(CASE WHEN EXISTS(SELECT 1 FROM diagnosa_pasien WHERE diagnosa_pasien.no_rawat = reg_periksa.no_rawat) THEN 1 ELSE 0 END)',
                'dengan_resep' => 'SUM(CASE WHEN EXISTS(SELECT 1 FROM resep_obat WHERE resep_obat.no_rawat = reg_periksa.no_rawat) THEN 1 ELSE 0 END)',
                'dengan_berkas' => 'SUM(CASE WHEN EXISTS(SELECT 1 FROM berkas_digital_perawatan WHERE berkas_digital_perawatan.no_rawat = reg_periksa.no_rawat) THEN 1 ELSE 0 END)',
                'dengan_resume' => 'SUM(CASE WHEN EXISTS(SELECT 1 FROM resume_pasien WHERE resume_pasien.no_rawat = reg_periksa.no_rawat) OR EXISTS(SELECT 1 FROM resume_pasien_ranap WHERE resume_pasien_ranap.no_rawat = reg_periksa.no_rawat) THEN 1 ELSE 0 END)'
            ])
            ->where('reg_periksa.tgl_registrasi', '>=', $tgl_awal)
            ->where('reg_periksa.tgl_registrasi', '<=', $tgl_akhir);

        if ($status !== 'Semua') {
            $query->where('reg_periksa.status_lanjut', '=', $status);
        }

        $summary = $query->oneArray();

        $total = (int) $summary['total_kunjungan'];
        $totalRanap = (int) $summary['total_ranap'];

        $indikator = [
            ['nama' => 'Anamnesis & Pemeriksaan', 'basis' => $total, 'terpenuhi' => (int) $summary['dengan_pemeriksaan']],
            ['nama' => 'Diagnosis', 'basis' => $total, 'terpenuhi' => (int) $summary['dengan_diagnosa']],
            ['nama' => 'Peresepan', 'basis' => $total, 'terpenuhi' => (int) $summary['dengan_resep']],
            ['nama' => 'Berkas Digital', 'basis' => $total, 'terpenuhi' => (int) $summary['dengan_berkas']]
        ];

        // Resume medis hanya dinilai jika ada kunjungan rawat inap
        if ($totalRanap > 0) {
            $indikator[] = ['nama' => 'Resume Medis', 'basis' => $totalRanap, 'terpenuhi' => (int) $summary['dengan_resume']];
        }

        $persens = [];
        foreach ($indikator as $i => $item) {
            $persen = $item['basis'] > 0 ? round($item['terpenuhi'] / $item['basis'] * 100, 1) : 0;
            $indikator[$i]['persen'] = $persen;
            $indikator[$i]['penilaian'] = $this->_penilaianLabel($persen);
            $persens[] = $persen;
        }

        $skor = count($persens) > 0 ? round(array_sum($persens) / count($persens), 1) : 0;
        $predikat = $this->_predikatEfektifitas($skor);

        $perStatusQuery = $this->db('reg_periksa')
            ->select([
                'status' => 'reg_periksa.status_lanjut',
                'jumlah' => 'COUNT(reg_periksa.no_rawat)',
                'dengan_pemeriksaan' => 'SUM(CASE WHEN EXISTS(SELECT 1 FROM pemeriksaan_ralan WHERE pemeriksaan_ralan.no_rawat = reg_periksa.no_rawat) OR EXISTS(SELECT 1 FROM pemeriksaan_ranap WHERE pemeriksaan_ranap.no_rawat = reg_periksa.no_rawat) THEN 1 ELSE 0 END)',
                'dengan_diagnosa' => 'SUM(CASE WHEN EXISTS(SELECT 1 FROM diagnosa_pasien WHERE diagnosa_pasien.no_rawat = reg_periksa.no_rawat) THEN 1 ELSE 0 END)',
                'dengan_resep' => 'SUM(CASE WHEN EXISTS(SELECT 1 FROM resep_obat WHERE resep_obat.no_rawat = reg_periksa.no_rawat) THEN 1 ELSE 0 END)',
                'dengan_berkas' => 'SUM(CASE WHEN EXISTS(SELECT 1 FROM berkas_digital_perawatan WHERE berkas_digital_perawatan.no_rawat = reg_periksa.no_rawat) THEN 1 ELSE 0 END)',
                'dengan_resume' => 'SUM(CASE WHEN EXISTS(SELECT 1 FROM resume_pasien WHERE resume_pasien.no_rawat = reg_periksa.no_rawat) OR EXISTS(SELECT 1 FROM resume_pasien_ranap WHERE resume_pasien_ranap.no_rawat = reg_periksa.no_rawat) THEN 1 ELSE 0 END)'
            ])
            ->where('reg_periksa.tgl_registrasi', '>=', $tgl_awal)
            ->where('reg_periksa.tgl_registrasi', '<=', $tgl_akhir);

        if ($status !== 'Semua') {
            $perStatusQuery->where('reg_periksa.status_lanjut', '=', $status);
        }

        $perStatusRows = $perStatusQuery->group('reg_periksa.status_lanjut')->asc('reg_periksa.status_lanjut')->toArray();

        $per_status = [];
        foreach ($perStatusRows as $row) {
            $jumlah = (int) $row['jumlah'];
            $persenPemeriksaan = $jumlah > 0 ? round((int) $row['dengan_pemeriksaan'] / $jumlah * 100, 1) : 0;
            $persenDiagnosa = $jumlah > 0 ? round((int) $row['dengan_diagnosa'] / $jumlah * 100, 1) : 0;
            $persenResep = $jumlah > 0 ? round((int) $row['dengan_resep'] / $jumlah * 100, 1) : 0;
            $persenBerkas = $jumlah > 0 ? round((int) $row['dengan_berkas'] / $jumlah * 100, 1) : 0;

            $persens = [$persenPemeriksaan, $persenDiagnosa, $persenResep, $persenBerkas];
            $persenResume = null;
            if ($row['status'] == 'Ranap') {
                $persenResume = $jumlah > 0 ? round((int) $row['dengan_resume'] / $jumlah * 100, 1) : 0;
                $persens[] = $persenResume;
            }

            $skorStatus = round(array_sum($persens) / count($persens), 1);

            $per_status[] = [
                'status' => $row['status'],
                'jumlah' => $jumlah,
                'persen_pemeriksaan' => $persenPemeriksaan,
                'persen_diagnosa' => $persenDiagnosa,
                'persen_resep' => $persenResep,
                'persen_berkas' => $persenBerkas,
                'persen_resume' => $persenResume,
                'skor' => $skorStatus,
                'predikat' => $this->_predikatEfektifitas($skorStatus)
            ];
        }

        return [
            'indikator' => $indikator,
            'per_status' => $per_status,
            'skor' => $skor,
            'predikat' => $predikat,
            'total_kunjungan' => $total,
            'total_ranap' => $totalRanap
        ];
    }

    private function _penilaianLabel($persen)
    {
        if ($persen >= 90) {
            return 'Baik';
        }
        if ($persen >= 75) {
            return 'Cukup';
        }
        return 'Kurang';
    }

    private function _predikatEfektifitas($skor)
    {
        if ($skor >= 90) {
            return 'Sangat Efektif';
        }
        if ($skor >= 75) {
            return 'Efektif';
        }
        return 'Kurang Efektif';
    }

    private function exportAuditReviewToExcel($data, $filename)
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
            'Status',
            'Poli',
            'Dokter',
            'Anamnesis & Pemeriksaan',
            'Diagnosis',
            'Peresepan',
            'Berkas Digital',
            'Resume Medis',
            'Skor Kelengkapan (%)',
            'Status Audit',
            'Komponen Kurang'
        ];

        $output .= implode("\t", $headers) . "\n";

        // Data rows
        $no = 1;
        foreach ($data as $row) {
            $resume = ($row['status_lanjut'] == 'Ranap') ? ((int) $row['ada_resume'] ? 'Ya' : 'Tidak') : '-';

            $dataRow = [
                $no++,
                $row['no_rawat'],
                $row['no_rkm_medis'],
                $row['nm_pasien'],
                $row['tgl_registrasi'],
                $row['status_lanjut'],
                isset_or($row['nm_poli']),
                isset_or($row['nm_dokter']),
                (int) $row['ada_pemeriksaan'] ? 'Ya' : 'Tidak',
                (int) $row['ada_diagnosa'] ? 'Ya' : 'Tidak',
                (int) $row['ada_resep'] ? 'Ya' : 'Tidak',
                (int) $row['ada_berkas'] ? 'Ya' : 'Tidak',
                $resume,
                $row['skor_kelengkapan'],
                $row['hasil_audit'],
                $row['komponen_kurang']
            ];

            $output .= implode("\t", $dataRow) . "\n";
        }

        echo $output;
        exit;
    }

    private function exportPenilaianErmToExcel($data, $filename)
    {
        // Set headers for Excel download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        // Create Excel content using simple CSV format that Excel can read
        $output = "\xEF\xBB\xBF"; // UTF-8 BOM

        // Skor keseluruhan
        $output .= implode("\t", ['Skor Efektivitas ERM (%)', $data['skor'], 'Predikat', $data['predikat'], 'Total Kunjungan', $data['total_kunjungan']]) . "\n";
        $output .= "\n";

        // Indikator keseluruhan
        $output .= implode("\t", ['Komponen ERM', 'Basis', 'Terpenuhi', 'Persentase (%)', 'Penilaian']) . "\n";
        foreach ($data['indikator'] as $row) {
            $output .= implode("\t", [$row['nama'], $row['basis'], $row['terpenuhi'], $row['persen'], $row['penilaian']]) . "\n";
        }
        $output .= "\n";

        // Rekap per status layanan
        $output .= implode("\t", ['Status Layanan', 'Jumlah Kunjungan', 'Anamnesis (%)', 'Diagnosis (%)', 'Peresepan (%)', 'Berkas Digital (%)', 'Resume (%)', 'Skor (%)', 'Predikat']) . "\n";
        foreach ($data['per_status'] as $row) {
            $resume = $row['persen_resume'] === null ? '-' : $row['persen_resume'];
            $output .= implode("\t", [
                $row['status'],
                $row['jumlah'],
                $row['persen_pemeriksaan'],
                $row['persen_diagnosa'],
                $row['persen_resep'],
                $row['persen_berkas'],
                $resume,
                $row['skor'],
                $row['predikat']
            ]) . "\n";
        }

        echo $output;
        exit;
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
            'laporan' => htmlspecialchars_array($query),
            'tgl_awal' => htmlspecialchars_array($tgl_awal),
            'tgl_akhir' => htmlspecialchars_array($tgl_akhir),
            'settings' => htmlspecialchars_array($settings)
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
            'data' => htmlspecialchars_array($data),
            'settings' => htmlspecialchars_array($settings)
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
            'laporan' => htmlspecialchars_array($query),
            'tgl_awal' => htmlspecialchars_array($tgl_awal),
            'tgl_akhir' => htmlspecialchars_array($tgl_akhir),
            'settings' => htmlspecialchars_array($settings)
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
            'laporan' => htmlspecialchars_array($query),
            'tgl_awal' => htmlspecialchars_array($tgl_awal),
            'tgl_akhir' => htmlspecialchars_array($tgl_akhir),
            'settings' => htmlspecialchars_array($settings)
        ]);
    }

    public function getCetakPdfPenyakitRalan()
    {
        $tgl_awal = isset_or($_GET['tgl_awal'], date('Y-m-01'));
        $tgl_akhir = isset_or($_GET['tgl_akhir'], date('Y-m-d'));

        $query = $this->db('diagnosa_pasien')
            ->join('penyakit', 'penyakit.kd_penyakit = diagnosa_pasien.kd_penyakit')
            ->join('reg_periksa', 'reg_periksa.no_rawat = diagnosa_pasien.no_rawat')
            ->select([
                'diagnosa_pasien.kd_penyakit',
                'penyakit.nm_penyakit',
                'count(diagnosa_pasien.kd_penyakit) as jumlah'
            ])
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->where('reg_periksa.tgl_registrasi', '>=', $tgl_awal)
            ->where('reg_periksa.tgl_registrasi', '<=', $tgl_akhir)
            ->group('diagnosa_pasien.kd_penyakit')
            ->desc('jumlah')
            ->limit(10)
            ->toArray();

        $settings = $this->settings('settings');
        
        echo $this->draw('cetak_pdf_penyakit_ralan.html', [
            'laporan' => htmlspecialchars_array($query),
            'tgl_awal' => htmlspecialchars($tgl_awal, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            'tgl_akhir' => htmlspecialchars($tgl_akhir, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            'settings' => htmlspecialchars_array($settings)
        ]);
        exit();
    }

    public function getCetakPdfPenyakitRanap()
    {
        $tgl_awal = isset_or($_GET['tgl_awal'], date('Y-m-01'));
        $tgl_akhir = isset_or($_GET['tgl_akhir'], date('Y-m-d'));

        $query = $this->db('diagnosa_pasien')
            ->join('penyakit', 'penyakit.kd_penyakit = diagnosa_pasien.kd_penyakit')
            ->join('reg_periksa', 'reg_periksa.no_rawat = diagnosa_pasien.no_rawat')
            ->select([
                'diagnosa_pasien.kd_penyakit',
                'penyakit.nm_penyakit',
                'count(diagnosa_pasien.kd_penyakit) as jumlah'
            ])
            ->where('reg_periksa.status_lanjut', 'Ranap')
            ->where('reg_periksa.tgl_registrasi', '>=', $tgl_awal)
            ->where('reg_periksa.tgl_registrasi', '<=', $tgl_akhir)
            ->group('diagnosa_pasien.kd_penyakit')
            ->desc('jumlah')
            ->limit(10)
            ->toArray();

        $settings = $this->settings('settings');
        
        echo $this->draw('cetak_pdf_penyakit_ranap.html', [
            'laporan' => htmlspecialchars_array($query),
            'tgl_awal' => htmlspecialchars($tgl_awal, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            'tgl_akhir' => htmlspecialchars($tgl_akhir, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            'settings' => htmlspecialchars_array($settings)
        ]);
        exit();
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

    private function exportPenyakitToExcel($data, $filename)
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
            'Kode Penyakit',
            'Nama Penyakit',
            'Jumlah'
        ];
        
        $output .= implode("\t", $headers) . "\n";
        
        // Data rows
        $no = 1;
        foreach ($data as $row) {
            $dataRow = [
                $no++,
                $row['kd_penyakit'],
                $row['nm_penyakit'],
                $row['jumlah']
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
