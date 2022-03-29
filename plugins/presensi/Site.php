<?php

namespace Plugins\Presensi;

use Systems\SiteModule;

class Site extends SiteModule
{

    public function routes()
    {
        $this->route('presensi/token', 'getToken');
        $this->route('presensi/ambil', 'getAmbilAntrian');
    }

    public function getToken()
    {
        echo $this->_resultToken();
        exit();
    }

    private function _resultToken()
    {
        header("Content-Type: application/json");
        $konten = trim(file_get_contents("php://input"));
        $header = apache_request_headers();
        $response = array();
        if ($header[$this->settings->get('presensi.header_username')] == $this->settings->get('presensi.x_username') && $header[$this->settings->get('presensi.header_password')] == $this->settings->get('presensi.x_password')) {
            $response = array(
                'response' => array(
                    'token' => $this->_getToken()
                ),
                'metadata' => array(
                    'message' => 'Ok',
                    'code' => 200
                )
            );
        } else {
            $response = array(
                'metadata' => array(
                    'message' => 'Access denied',
                    'code' => 201
                )
            );
        }
        echo json_encode($response);
    }

    public function getAmbilAntrian()
    {
        echo $this->_resultAmbilAntrian();
        exit();
    }

    private function _resultAmbilAntrian()
    {
        // date_default_timezone_set('UTC');
        // $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
        // $key = $this->consid . $this->secretkey . $tStamp;

        date_default_timezone_set($this->settings->get('settings.timezone'));

        header("Content-Type: application/json");
        $header = apache_request_headers();
        $konten = trim(file_get_contents("php://input"));
        $decode = json_decode($konten, true);
        $response = array();
        if ($header[$this->settings->get('presensi.header_token')] == false) {
            $response = array(
                'metadata' => array(
                    'message' => 'Token expired',
                    'code' => 201
                )
            );
            http_response_code(201);
        } else if ($header[$this->settings->get('presensi.header_token')] == $this->_getToken() && $header[$this->settings->get('presensi.header_username')] == $this->settings->get('presensi.x_username')) {

            $tanggalawal = $decode['tanggalawal'];
            $tanggalakhir = $decode['tanggalakhir'];
            if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $tanggalawal)) {
                $errors[] = 'Format tanggal awal jadwal presensi tidak sesuai';
            }
            if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $tanggalakhir)) {
                $errors[] = 'Format tanggal akhir jadwal presensi tidak sesuai';
            }
            if ($tanggalawal > $tanggalakhir) {
                $errors[] = 'Format tanggal awal harus lebih kecil dari tanggal akhir';
            }
            $yearmonth = date("Ym", strtotime($tanggalawal));
            $year = date("Y", strtotime($tanggalawal));
            $month = date("m", strtotime($tanggalawal));
            // $tentukan_hari = date('D', strtotime($tanggal));
            // $day = array(
            //     'Sun' => 'AKHAD',
            //     'Mon' => 'SENIN',
            //     'Tue' => 'SELASA',
            //     'Wed' => 'RABU',
            //     'Thu' => 'KAMIS',
            //     'Fri' => 'JUMAT',
            //     'Sat' => 'SABTU'
            // );
            // $hari = $day[$tentukan_hari];
            $absen = $this->db('bridging_bkd_presensi')->where('tahun', $year)->where('bulan',$month)->limit(5)->toArray();
            // $cek_rujukan = $this->db('bridging_sep')->where('no_rujukan', $decode['nomorreferensi'])->group('tglrujukan')->oneArray();

            // $h1 = strtotime('+1 days', strtotime(date('Y-m-d')));
            // $h1 = date('Y-m-d', $h1);
            // $_h1 = date('d-m-Y', strtotime($h1));
            // if ($cek_rujukan > 0) {
            //     $h7 = strtotime('+90 days', strtotime($cek_rujukan['tglrujukan']));
            // } else {
            //     $h7 = strtotime('+7 days', strtotime(date('Y-m-d')));
            // }
            // $h7 = date('Y-m-d', $h7);
            // $_h7 = date('d-m-Y', strtotime($h7));

            // $data_pasien = $this->db('pasien')->where('no_peserta', $decode['nomorkartu'])->oneArray();
            // $poli = $this->db('maping_poli_bpjs')->where('kd_poli_bpjs', $decode['kodepoli'])->oneArray();
            // $dokter = $this->db('maping_dokter_dpjpvclaim')->where('kd_dokter_bpjs', $decode['kodedokter'])->oneArray();
            // $cek_kouta = $this->db()->pdo()->prepare("SELECT jadwal.kuota - (SELECT COUNT(booking_registrasi.tanggal_periksa) FROM booking_registrasi WHERE booking_registrasi.tanggal_periksa='$decode[tanggalperiksa]' AND booking_registrasi.kd_dokter=jadwal.kd_dokter) as sisa_kouta, jadwal.kd_dokter, jadwal.kd_poli, jadwal.jam_mulai as jam_mulai, poliklinik.nm_poli, dokter.nm_dokter, jadwal.kuota FROM jadwal INNER JOIN maping_poli_bpjs ON maping_poli_bpjs.kd_poli_rs=jadwal.kd_poli INNER JOIN poliklinik ON poliklinik.kd_poli=jadwal.kd_poli INNER JOIN dokter ON dokter.kd_dokter=jadwal.kd_dokter WHERE jadwal.hari_kerja='$hari' AND maping_poli_bpjs.kd_poli_bpjs='$decode[kodepoli]' GROUP BY jadwal.kd_dokter HAVING sisa_kouta > 0 ORDER BY sisa_kouta DESC LIMIT 1");
            // $cek_kouta->execute();
            // $cek_kouta = $cek_kouta->fetch();
            // $jadwal = $this->db('jadwal')
            //     ->join('maping_dokter_dpjpvclaim', 'maping_dokter_dpjpvclaim.kd_dokter=jadwal.kd_dokter')
            //     ->where('maping_dokter_dpjpvclaim.kd_dokter_bpjs', $decode['kodedokter'])
            //     ->where('hari_kerja', $hari)
            //     ->where('jam_mulai', strtok($decode['jampraktek'], '-') . ':00')
            //     ->where('jam_selesai', substr($decode['jampraktek'], strpos($decode['jampraktek'], "-") + 1) . ':00')
            //     ->oneArray();

            // $cek_referensi = $this->db('mlite_antrian_referensi')->where('nomor_referensi', $decode['nomorreferensi'])->oneArray();
            // $cek_referensi_noka = $this->db('mlite_antrian_referensi')->where('nomor_kartu', $decode['nomorkartu'])->where('tanggal_periksa', $decode['tanggalperiksa'])->oneArray();

            // if ($cek_referensi > 0) {
            //     $errors[] = 'Anda sudah terdaftar dalam antrian menggunakan nomor rujukan yang sama ditanggal ' . $decode['tanggalperiksa'];
            // }
            // if ($cek_referensi_noka > 0) {
            //     $errors[] = 'Anda sudah terdaftar dalam antrian ditanggal ' . $cek_referensi_noka['tanggal_periksa'] . '. Silahkan pilih tanggal lain.';
            // }
            // if (empty($decode['nomorkartu'])) {
            //     $errors[] = 'Nomor kartu tidak boleh kosong';
            // }
            // if (!empty($decode['nomorkartu']) && mb_strlen($decode['nomorkartu'], 'UTF-8') < 13) {
            //     $errors[] = 'Nomor kartu harus 13 digit';
            // }
            // if (!empty($decode['nomorkartu']) && !ctype_digit($decode['nomorkartu'])) {
            //     $errors[] = 'Nomor kartu harus mengandung angka saja!!';
            // }
            // if (empty($decode['nik'])) {
            //     $errors[] = 'Nomor kartu tidak boleh kosong';
            // }
            // if (!empty($decode['nik']) && mb_strlen($decode['nik'], 'UTF-8') < 16) {
            //     $errors[] = 'Nomor KTP harus 16 digiti atau format tidak sesuai';
            // }
            // if (!empty($decode['nik']) && !ctype_digit($decode['nik'])) {
            //     $errors[] = 'Nomor kartu harus mengandung angka saja!!';
            // }
            // if (empty($decode['tanggalperiksa'])) {
            //     $errors[] = 'Anda belum memilih tanggal periksa';
            // }
            // if (!empty($decode['tanggalperiksa']) && $decode['tanggalperiksa'] < $h1 || $decode['tanggalperiksa'] > $h7) {
            //     $errors[] = 'Tanggal periksa bisa dilakukan tanggal ' . $_h1 . ' hingga tanggal ' . $_h7;
            // }
            // if (!empty($decode['tanggalperiksa']) && !preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $decode['tanggalperiksa'])) {
            //     $errors[] = 'Format tanggal periksa tidak sesuai';
            // }
            // if (!empty($decode['tanggalperiksa']) && $decode['tanggalperiksa'] == $cek_referensi['tanggal_periksa']) {
            //     $errors[] = 'Anda sudah terdaftar dalam antrian ditanggal ' . $decode['tanggalperiksa'];
            // }
            // if (empty($decode['kodepoli'])) {
            //     $errors[] = 'Kode poli tidak boleh kosong';
            // }
            // if (!empty($decode['kodepoli']) && $poli == 0) {
            //     $errors[] = 'Kode poli tidak ditemukan';
            // }
            // if (empty($decode['kodedokter'])) {
            //     $errors[] = 'Kode dokter tidak boleh kosong';
            // }
            // if (!empty($decode['kodedokter']) && $dokter == 0) {
            //     $errors[] = 'Kode dokter tidak ditemukan';
            // }
            // if (!empty($decode['jeniskunjungan']) && $decode['jeniskunjungan'] < 1 || $decode['jeniskunjungan'] > 4) {
            //     $errors[] = 'Jenis kunjungan tidak ditemukan';
            // }

            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $response = array(
                        'metadata' => array(
                            'message' => $this->_getErrors($error),
                            'code' => 201
                        )
                    );
                };
                http_response_code(201);
            } else {
                if (!$absen) {
                    $response = array(
                        'metadata' => array(
                            'message' =>  'Data presensi tidak ditemukan',
                            'code' => 202
                        )
                    );
                    http_response_code(202);
                } else {
                    // Get antrian poli
                    // $no_reg = $this->core->setNoBooking($dokter['kd_dokter'], $decode[tanggalperiksa]);
                    // $minutes = $no_urut_reg * 10;
                    // $cek_kouta['jam_mulai'] = date('H:i:s', strtotime('+' . $minutes . ' minutes', strtotime($cek_kouta['jam_mulai'])));
                    // $keterangan = 'Peserta harap datang 30 menit lebih awal.';
                    // $query = $this->db('booking_registrasi')->save([
                    //     'tanggal_booking' => date('Y-m-d'),
                    //     'jam_booking' => date('H:i:s'),
                    //     'no_rkm_medis' => $data_pasien['no_rkm_medis'],
                    //     'tanggal_periksa' => $decode['tanggalperiksa'],
                    //     'kd_dokter' => $cek_kouta['kd_dokter'],
                    //     'kd_poli' => $cek_kouta['kd_poli'],
                    //     'no_reg' => $no_reg,
                    //     'kd_pj' => 'BPJ',
                    //     'limit_reg' => 1,
                    //     'waktu_kunjungan' => $decode['tanggalperiksa'] . ' ' . $cek_kouta['jam_mulai'],
                    //     'status' => 'Belum'
                    // ]);
                    // if ($query) {
                    $data = array();
                    foreach ($absen as $key) {
                        // $pegawai = $this->db('pegawai')->where('id', $key['id'])->oneArray();
                        $data[] = array(
                            'rekap_finalisasi_id' => "",
                            'userid' => "",
                            'nip' => $key['nip'],
                            'jumlah_kehadiran' => $key['jumlah_kehadiran'],
                            'jumlah_hari_kerja' => $key['jumlah_hari_kerja'],
                            'persentase_hari_kerja' => "",
                            'dl1' => "",
                            'dl2' => "",
                            'cuti_melahirkan' => "",
                            'cuti_besar' => "",
                            'izin' => "",
                            'sakit' => "",
                            'sakit_lbh_10_hari' => "",
                            'sakit_4_10_hari' => "",
                            'sakit_10_hari' => "",
                            'jlh_izin_akumulasi_akhir' => "",
                            'jlh_pot_hari_izin_akhir' => 0,
                            'jlh_akumulasi_cuti' => "",
                            'jlh_over_cuti' => "",
                            'sakit_seb_10_hari' => "",
                            'pot_manunggal' => "",
                            'tk1' => "",
                            'tk2' => "",
                            'jml_pot_keterlambatan' => "",
                            'jml_pot_pulang_lebih_awal' => "",
                            'jml_pot_lebih_10_hari' => "",
                            'jml_pot_sakit_4_10_hari' => "",
                            'jml_pot_sakit_10_hari' => "",
                            'jml_pot_over_akum_cuti' => "",
                            'jml_pot_over_cuti_bln_aktif' => 0,
                            'jml_pot_tanpa_keterangan' => "",
                            'jml_pot_lupa_absen_masuk' => "",
                            'jml_pot_kelebihan_cuti_akhir' => "",
                            'total_potongan' => "",
                            'persentase_final' => "",
                            'defaultdeptid' => "",
                            'periode' => $yearmonth,
                            'create_date' => $this->getTimestamp(),
                            'last_sync' => "",
                            'nama_pegawai' => $key['nama'],
                            'nm_skpd' => "RSUD H. DAMANHURI"
                        );
                    }
                    $response = array(
                        'status' => true,
                        'result' => array(
                            'data' => $data,
                        ),
                        'metadata' => array(
                            'message' => 'Ok',
                            'code' => 200
                        )
                    );
                    http_response_code(200);

                    // if (!empty($decode['nomorreferensi'])) {
                    //     $this->db('mlite_antrian_referensi')->save([
                    //         'tanggal_periksa' => $decode['tanggalperiksa'],
                    //         'nomor_kartu' => $decode['nomorkartu'],
                    //         'nomor_referensi' => $decode['nomorreferensi'],
                    //         'jenis_kunjungan' => $decode['jeniskunjungan'],
                    //         'status_kirim' => 'Sudah'
                    //     ]);
                    // }
                    // } else {
                    //     $response = array(
                    //         'metadata' => array(
                    //             'message' => "Maaf Terjadi Kesalahan, Hubungi layanan pelanggang Rumah Sakit..",
                    //             'code' => 201
                    //         )
                    //     );
                    // }
                }
            }
        } else {
            $response = array(
                'metadata' => array(
                    'message' => 'Access denied',
                    'code' => 201
                )
            );
            http_response_code(201);
        }
        echo json_encode($response);
    }

    function getTimestamp()
    {
        $microtime = floatval(substr((string)microtime(), 1, 8));
        $rounded = round($microtime, 3);
        return date("Y-m-d H:i:s") . substr((string)$rounded, 1, strlen($rounded));
    }

    private function _getToken()
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode(['username' => $this->settings->get('presensi.x_username'), 'password' => $this->settings->get('presensi.x_password'), 'date' => strtotime(date('Y-m-d')) * 1000]);
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, 'abC123!', true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
        return $jwt;
    }

    private function _getErrors($error)
    {
        $errors = $error;
        return $errors;
    }

    private function _setUmur($tanggal)
    {
        list($cY, $cm, $cd) = explode('-', date('Y-m-d'));
        list($Y, $m, $d) = explode('-', date('Y-m-d', strtotime($tanggal)));
        $umur = $cY - $Y;
        return $umur;
    }
}
