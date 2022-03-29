<?php

namespace Plugins\Data_Sirs;

use session;
use Systems\AdminModule;

class Admin extends AdminModule
{

    public function init()
    {
        $this->id_sirs = $this->settings->get('sirs_online.id_sirs');
        $this->password = $this->settings->get('sirs_online.password');
        $this->email = $this->settings->get('sirs_online.email');
        $this->password_v3 = $this->settings->get('sirs_online.password_v3');
        $this->url = $this->settings->get('sirs_online.url');
        $this->url_v3 = $this->settings->get('sirs_online.url_v3');
    }

    public function navigation()
    {
        return [
            'Index' => 'manage',
            'Data Obat' => 'index',
            'Data Covid 19' => 'covid',
            'Pengaturan' => 'settings',
        ];
    }

    public function getManage()
    {
        $sub_modules = [
            ['name' => 'Data Obat', 'url' => url([ADMIN, 'data_sirs', 'index']), 'icon' => 'users', 'desc' => 'Data Obat'],
            ['name' => 'Data Covid 19', 'url' => url([ADMIN, 'data_sirs', 'covid']), 'icon' => 'user-plus', 'desc' => 'Tambah data covid 19'],
            ['name' => 'Data Bed Covid 19', 'url' => url([ADMIN, 'data_sirs', 'bedcovid']), 'icon' => 'bed', 'desc' => 'Data Bed covid 19'],
            ['name' => 'Pengaturan', 'url' => url([ADMIN, 'data_sirs', 'settings']), 'icon' => 'gear', 'desc' => 'Pengaturan Sirs Online'],
        ];
        return $this->draw('index.html', ['sub_modules' => $sub_modules]);
    }

    public function getIndex()
    {
        $this->assign['yesterday'] = date('Y-m-d', strtotime("-1 days"));
        $today = date('Y-m-d');
        $yesterday = $this->assign['yesterday'];
        $namaObat = array(
            'B000010146' => 'Remdesivir Inj 100 mg',
            'B000010182' => 'Favipiravir 200 mg',
            'OBT000000056' => 'Vit C (Asam askorbat) inj 1000 mg',
            'B000001456' => 'Vit C (Asam askorbat) tab 250 mg',
            'OBT000000167' => 'Vit C (Asam askorbat) tab 500 mg',
            'B000009484' => 'Zinc sirup 20 mg / 5 ml',
            'OBT0418' => 'Zinc tab dispersible 20 mg',
            'OBT000000069' => 'Oseltamivir tab 75 mg',
            'OBT0055' => 'Azitromisin tab 500mg',
            'B000010270' => 'Azitromisin 500 mg Inj',
            'OBT0250' => 'Levofloxacin infus 5 mg/mL',
            'OBT0473' => 'Levofloxacin tab 750 mg',
            'OBT0249' => 'Levofloxacin tab 500 mg',
            'OBT0133' => 'Deksametason Inj 5 mg/mL',
            'B000001325' => 'Deksametason tab 0.5mg',
            'OBT0047' => 'N- Asetil Sistein kap 200 mg',
            'OBT0547' => 'Heparin Na inj 5.000 IU/mL (i.v./s.k.)',
            'B000001324' => 'Enoksaparin sodium inj 10.000 IU/mL',

            'B000010173' => 'Fondaparinux inj 2,5 mg/0,5 mL'
        );

        $query = "SELECT SUM(gudangbarang.stok) as sum , databarang.kode_brng as kode , databarang.nama_brng as nama FROM gudangbarang JOIN databarang ON gudangbarang.kode_brng = databarang.kode_brng WHERE gudangbarang.kode_brng IN ('B000010146','B000010182','OBT000000056','B000001456','OBT000000167','B000009484','OBT0418','OBT000000069','OBT0055','B000010270','OBT0250','OBT0473','OBT0249','OBT0133','B000001325','OBT0047','OBT0547','B000001324','B000009543','B000010173','B000010147','B000010151','OBT0323','OBT0671','OBT0268','B000001172','OBT0677','B000010345') AND gudangbarang.kd_bangsal IN ('B0001','B0002','B0014','B0018') GROUP BY gudangbarang.kode_brng";
        $stmt = $this->db()->pdo()->prepare($query);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $this->assign['sedia'] = [];
        foreach ($rows as $row) {
            if ($namaObat[$row['kode']]) {
                $row['nama'] = $namaObat[$row['kode']];
            } else {
                $row['nama'] = $row['nama'];
            }
            $query1 = "SELECT SUM(detail_pemberian_obat.jml) FROM detail_pemberian_obat , kamar_inap , reg_periksa , diagnosa_pasien WHERE detail_pemberian_obat.no_rawat = kamar_inap.no_rawat AND kamar_inap.no_rawat = reg_periksa.no_rawat AND reg_periksa.no_rawat = diagnosa_pasien.no_rawat AND diagnosa_pasien.kd_penyakit IN ('B34.2','Z03.8') AND detail_pemberian_obat.kode_brng = '" . $row['kode'] . "' AND detail_pemberian_obat.tgl_perawatan = '$yesterday' GROUP BY detail_pemberian_obat.kode_brng";
            $stmt1 = $this->db()->pdo()->prepare($query1);
            $stmt1->execute();
            $rows1 = $stmt1->fetchColumn();
            $row['jml'] = $rows1;
            if ($row['jml'] == '') {
                $row['jml'] = '0';
            } else {
                $row['jml'] = $rows1;
            }
            $this->assign['sedia'][] = $row;
        }

        return $this->draw('manage.html', ['sirs' => $this->assign]);
    }

    public function anyCovid()
    {
        $tgl_masuk = '';
        $tgl_masuk_akhir = '';
        $status_pulang = '';
        $this->assign['stts_pulang'] = [];

        if (isset($_POST['periode_rawat_inap'])) {
            $tgl_masuk = $_POST['periode_rawat_inap'];
        }
        if (isset($_POST['periode_rawat_inap_akhir'])) {
            $tgl_masuk_akhir = $_POST['periode_rawat_inap_akhir'];
        }
        if (isset($_POST['status_pulang'])) {
            $status_pulang = $_POST['status_pulang'];
        }
        $this->_Display($tgl_masuk, $tgl_masuk_akhir, $status_pulang);
        return $this->draw('covidlist.html', ['rawat_inap' => $this->assign]);
    }

    public function _Display($tgl_masuk = '', $tgl_masuk_akhir = '', $status_pulang = '')
    {
        $this->_addHeaderFiles();

        $this->assign['kamar'] = $this->db('kamar')->join('bangsal', 'bangsal.kd_bangsal=kamar.kd_bangsal')->where('statusdata', '1')->toArray();
        $this->assign['dokter'] = $this->db('dokter')->where('status', '1')->toArray();
        $this->assign['penjab'] = $this->db('penjab')->toArray();
        $this->assign['no_rawat'] = '';

        $bangsal = str_replace(",", "','", $this->core->getUserInfo('cap', null, true));

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
        if ($status_pulang == '') {
            $sql .= " AND kamar_inap.stts_pulang = '-'";
        }
        if ($status_pulang == 'all' && $tgl_masuk !== '' && $tgl_masuk_akhir !== '') {
            $sql .= " AND kamar_inap.stts_pulang = '-' AND kamar_inap.tgl_masuk BETWEEN '$tgl_masuk' AND '$tgl_masuk_akhir'";
        }
        if ($status_pulang == 'masuk' && $tgl_masuk !== '' && $tgl_masuk_akhir !== '') {
            $sql .= " AND kamar_inap.tgl_masuk BETWEEN '$tgl_masuk' AND '$tgl_masuk_akhir'";
        }
        if ($status_pulang == 'pulang' && $tgl_masuk !== '' && $tgl_masuk_akhir !== '') {
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
            $isBridging = $this->db('bridging_covid')->where('no_rawat', $row['no_rawat'])->oneArray();
            if (!$isBridging) {
                $row['status_bridging'] = false;
            } else {
                $row['status_bridging'] = true;
            }
            $this->assign['list'][] = $row;
        }

        if (isset($_POST['no_rawat'])) {
            $this->assign['kamar_inap'] = $this->db('kamar_inap')
                ->join('reg_periksa', 'reg_periksa.no_rawat=kamar_inap.no_rawat')
                ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
                ->join('kamar', 'kamar.kd_kamar=kamar_inap.kd_kamar')
                ->join('dpjp_ranap', 'dpjp_ranap.no_rawat=kamar_inap.no_rawat')
                ->join('dokter', 'dokter.kd_dokter=dpjp_ranap.kd_dokter')
                ->join('penjab', 'penjab.kd_pj=reg_periksa.kd_pj')
                ->where('kamar_inap.no_rawat', $_POST['no_rawat'])
                ->oneArray();
        } else {
            $this->assign['kamar_inap'] = [
                'tgl_masuk' => date('Y-m-d'),
                'jam_masuk' => date('H:i:s'),
                'tgl_keluar' => date('Y-m-d'),
                'jam_keluar' => date('H:i:s'),
                'no_rkm_medis' => '',
                'nm_pasien' => '',
                'no_rawat' => '',
                'kd_dokter' => '',
                'kd_kamar' => '',
                'kd_pj' => '',
                'diagnosa_awal' => '',
                'diagnosa_akhir' => '',
                'stts_pulang' => '',
                'lama' => ''
            ];
        }
    }

    public function anyCovidForm()
    {
        function initials($str)
        {
            $words = explode(" ", $str);
            $acronym = "";
            $w = array();
            foreach ($words as $w) {
                $acronym .= substr($w, 0, 1);
            }

            return $acronym;
        }

        $rows = $this->db('reg_periksa')
            ->join('pasien', 'reg_periksa.no_rkm_medis = pasien.no_rkm_medis')
            ->where('no_rawat', $_POST['no_rawat'])
            ->oneArray();
        $result = [];
        $result = $rows;
        $inisial = substr($rows['nm_pasien'], strpos($rows['nm_pasien'], "BIN"));
        $inisialcut = str_replace($inisial, '', $rows['nm_pasien']);
        if ($inisialcut == '') {
            $result['inisial'] = initials($inisial);
        } else {
            $result['inisial'] = initials($inisialcut);
        }
        $result['kode'] = $this->getKecamatan($rows['kd_kec']);
        $result['tgl_inap_masuk'] = $this->db('kamar_inap')->select('tgl_masuk')->where('no_rawat', $_POST['no_rawat'])->oneArray();
        echo $this->draw('covidform.html', ['pasien' => $result]);
        exit();
    }

    public function getToken()
    {
        $url = $this->url_v3 . "api/rslogin";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Content-Type: application/json",
            "accept: */*",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        if ($this->url_v3 == "http://202.70.136.86:3020/") {
            $data = '{"kode_rs": "' . $this->id_sirs . '", "password": "' . $this->password . '"}';
        } else {
            $data = '{"userName": "' . $this->email . '", "password": "' . $this->password_v3 . '"}';
        }


        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        //for debug only!
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($curl);
        curl_close($curl);
        // var_dump($resp);
        return $resp;
    }

    public function getTokenStored()
    {
        if (isset($_COOKIE['tokenCookie'])) {
            return $_COOKIE['tokenCookie'];
        } else {
            $token = $this->getToken();
            $token = json_decode($token, true);
            setcookie("tokenCookie", $token['data']['access_token'], time() + 400);
            return $_COOKIE['tokenCookie'];
        }
    }

    public function getVarianCovid()
    {
        $token = $this->getTokenStored();
        $url = $this->url_v3 . "api/variancovid?page=1&limit=1000";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Content-Type: application/json",
            "accept: */*",
            'Authorization: Bearer ' . $token,
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        //for debug only!
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($curl);
        curl_close($curl);
        $json = json_decode($resp, true);
        $code = $json['status'];
        $message = $json['message'];
        if ($json != null) {
            echo '{
                    "metaData": {
                        "code": "' . $code . '",
                        "message": "' . $message . '"
                    },
                    "response": ' . json_encode($json['data']) . '}';
        } else {
            echo '{
                    "metaData": {
                        "code": "5000",
                        "message": "ERROR"
                    },
                    "response": "ADA KESALAHAN ATAU SAMBUNGAN KE SERVER KEMENKES TERPUTUS."}';
        }
        exit();
    }

    public function getKecamatan($kecamatan)
    {
        switch ($kecamatan) {
            case '3520':
                $kode['kecamatan'] = '630708 - BATANG ALAI UTARA';
                $kode['kabupaten'] = '6307 - KAB. HULU SUNGAI TENGAH';
                $kode['provinsi'] = '63 - KALIMANTAN SELATAN';
                $kode['warganegara'] = 'ID - INDONESIA';
                return $kode;
                break;
            case '3519':
                $kode['kecamatan'] = '630707 - BATANG ALAI SELATAN';
                $kode['kabupaten'] = '6307 - KAB. HULU SUNGAI TENGAH';
                $kode['provinsi'] = '63 - KALIMANTAN SELATAN';
                $kode['warganegara'] = 'ID - INDONESIA';
                return $kode;
                break;
            case '4337':
                $kode['kecamatan'] = '630710 - BATANG ALAI TIMUR';
                $kode['kabupaten'] = '6307 - KAB. HULU SUNGAI TENGAH';
                $kode['provinsi'] = '63 - KALIMANTAN SELATAN';
                $kode['warganegara'] = 'ID - INDONESIA';
                return $kode;
                break;
            case '3523':
                $kode['kecamatan'] = '630701 - HARUYAN';
                $kode['kabupaten'] = '6307 - KAB. HULU SUNGAI TENGAH';
                $kode['provinsi'] = '63 - KALIMANTAN SELATAN';
                $kode['warganegara'] = 'ID - INDONESIA';
                return $kode;
                break;
            case '3521':
                $kode['kecamatan'] = '630702 - BATU BENAWA';
                $kode['kabupaten'] = '6307 - KAB. HULU SUNGAI TENGAH';
                $kode['provinsi'] = '63 - KALIMANTAN SELATAN';
                $kode['warganegara'] = 'ID - INDONESIA';
                return $kode;
                break;
            case '3524':
                $kode['kecamatan'] = '630703 - LABUAN AMAS SELATAN';
                $kode['kabupaten'] = '6307 - KAB. HULU SUNGAI TENGAH';
                $kode['provinsi'] = '63 - KALIMANTAN SELATAN';
                $kode['warganegara'] = 'ID - INDONESIA';
                return $kode;
                break;
            case '3525':
                $kode['kecamatan'] = '630704 - LABUAN AMAS UTARA';
                $kode['kabupaten'] = '6307 - KAB. HULU SUNGAI TENGAH';
                $kode['provinsi'] = '63 - KALIMANTAN SELATAN';
                $kode['warganegara'] = 'ID - INDONESIA';
                return $kode;
                break;
            case '3526':
                $kode['kecamatan'] = '630705 - PANDAWAN';
                $kode['kabupaten'] = '6307 - KAB. HULU SUNGAI TENGAH';
                $kode['provinsi'] = '63 - KALIMANTAN SELATAN';
                $kode['warganegara'] = 'ID - INDONESIA';
                return $kode;
                break;
            case '3518':
                $kode['kecamatan'] = '630706 - BARABAI';
                $kode['kabupaten'] = '6307 - KAB. HULU SUNGAI TENGAH';
                $kode['provinsi'] = '63 - KALIMANTAN SELATAN';
                $kode['warganegara'] = 'ID - INDONESIA';
                return $kode;
                break;
            case '3522':
                $kode['kecamatan'] = '630709 - HANTAKAN';
                $kode['kabupaten'] = '6307 - KAB. HULU SUNGAI TENGAH';
                $kode['provinsi'] = '63 - KALIMANTAN SELATAN';
                $kode['warganegara'] = 'ID - INDONESIA';
                return $kode;
                break;
            case '4300':
                $kode['kecamatan'] = '630711 - LIMPASU';
                $kode['kabupaten'] = '6307 - KAB. HULU SUNGAI TENGAH';
                $kode['provinsi'] = '63 - KALIMANTAN SELATAN';
                $kode['warganegara'] = 'ID - INDONESIA';
                return $kode;
                break;
        }
    }

    public function postSaveCovid()
    {
        $data = [
            'kewarganegaraanId' => substr($_POST['warga'], 0, 2),
            'nik' => $_POST['nik'],
            'noPassport' => null,
            'asalPasienId' => substr($_POST['asal_pasien'], 0, 1),
            'noRM' => $_POST['no_rkm_medis'],
            'namaLengkapPasien' => $_POST['nm_pasien'],
            'namaInisialPasien' => $_POST['inisial'],
            'tanggalLahir' => $_POST['tgl_lahir'],
            'email' => null,
            'noTelp' => $_POST['no_telp'],
            'jenisKelaminId' => $_POST['jk'],
            'domisiliKecamatanId' => substr($_POST['kec'], 0, 6),
            'domisiliKabKotaId' => substr($_POST['kab'], 0, 4),
            'domisiliProvinsiId' => substr($_POST['prov'], 0, 2),
            'pekerjaanId' => substr($_POST['pekerjaan_pasien'], 0, 1),
            'tanggalMasuk' => $_POST['tgl_perawatan'],
            'jenisPasienId' => substr($_POST['jenis_rawat'], 0, 1),
            'varianCovidId' => substr($_POST['varian_covid'], 0, 1),
            'statusPasienId' => substr($_POST['status_pasien'], 0, 1),
            'statusCoInsidenId' => $_POST['coinsiden'],
            'statusRawatId' => substr($_POST['status_rawat'], 0, 2),
            'alatOksigenId' => $_POST['oksigen'] == "" ? null : substr($_POST['oksigen'], 0, 1),
            'penyintasId' => $_POST['penyintas'],
            'tanggalOnsetGejala' => $_POST['tgl_gejala'],
            'kelompokGejalaId' => substr($_POST['status_gejala'], 0, 1),
            'gejala' => [
                'demamId' => $_POST['demam'],
                'batukId' => $_POST['batuk'],
                'pilekId' => $_POST['pilek'],
                'sakitTenggorokanId' => $_POST['tenggorokan'],
                'sesakNapasId' => $_POST['sesak_nafas'],
                'lemasId' => $_POST['lemas'],
                'nyeriOtotId' => $_POST['nyeri'],
                'mualMuntahId' => $_POST['mual'],
                'diareId' => $_POST['diare'],
                'anosmiaId' => $_POST['anosmia'],
                'napasCepatId' => $_POST['nafas_cepat'],
                'frekNapas30KaliPerMenitId' => $_POST['frek'],
                'distresPernapasanBeratId' => $_POST['nafas_berat'],
                'lainnyaId' => $_POST['lainnya']
            ]
        ];

        $data = json_encode($data);

        $token = $this->getTokenStored();
        $url = $this->url_v3 . "api/laporancovid19versi3";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Content-Type: application/json",
            "accept: */*",
            'Authorization: Bearer ' . $token,
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        //for debug only!
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($curl);
        curl_close($curl);
        $ret = json_decode($resp, true);
        if ($ret == NULL) {
            $data = '{
                    "status": "false",
                    "message": "Koneksi ke server Kemenkes terputus. Silahkan ulangi beberapa saat lagi!"}';
            $data = json_decode($data, true);
            $ret = json_encode($data);
            echo $ret;
        } else if ($ret['status'] == true) {
            $code = $ret['status'];
            if ($ret != null) {

                $_POST['id'] = $ret['data']['id'];
                $simpan = $this->db('bridging_covid')->save([
                    'id' => $_POST['id'],
                    'no_rawat' => $_POST['no_rawat'],
                    'no_passport' => '',
                    'inisial' => $_POST['inisial'],
                    'tgl_onset' => $_POST['tgl_gejala'],
                    'warga' => substr($_POST['warga'], 0, 2),
                    'asal_pasien' => substr($_POST['asal_pasien'], 0, 1),
                    'jenis_pasien' => substr($_POST['jenis_rawat'], 0, 1),
                    'status_pasien' => substr($_POST['status_pasien'], 0, 1),
                    'status_rawat' => substr($_POST['status_rawat'], 0, 2),
                    'pekerjaan' => substr($_POST['pekerjaan_pasien'], 0, 1),
                    'kelompok_gejala' => substr($_POST['status_gejala'], 0, 1),
                    'varian_covid' => substr($_POST['varian_covid'], 0, 1),
                    'alat_oksigen' => $_POST['oksigen'] == "" ? '' : substr($_POST['oksigen'], 0, 1),
                    'penyintas' => $_POST['penyintas'],
                    'status_co' => $_POST['coinsiden'],
                    'demam' => $_POST['demam'],
                    'batuk' => $_POST['batuk'],
                    'pilek' => $_POST['pilek'],
                    'tenggorokan' => $_POST['tenggorokan'],
                    'sesak' => $_POST['sesak_nafas'],
                    'lemas' => $_POST['lemas'],
                    'nyeri' => $_POST['nyeri'],
                    'mual' => $_POST['mual'],
                    'diare' => $_POST['diare'],
                    'anosmia' => $_POST['anosmia'],
                    'nafas_cepat' => $_POST['nafas_cepat'],
                    'lainnya' => $_POST['lainnya'],
                    'distres' => $_POST['nafas_berat'],
                    'frekuensi' => $_POST['frek']
                ]);

                if ($simpan) {
                    $data = '{
                        "status": "' . $code . '",
                        "message": "Berhasil Menyimpan dengan Id : ' . $ret['data']['id'] . '"}';
                    $data = json_decode($data, true);
                    $ret = json_encode($data);
                    echo $ret;
                }
            } else {
                $data = '{
                    "status": "false",
                    "message": "Koneksi ke server Kemenkes terputus. Silahkan ulangi beberapa saat lagi!"}';
                $data = json_decode($data, true);
                $ret = json_encode($data);
                echo $ret;
            }
        } else {
            $ret = json_encode($ret);
            echo $ret;
        }
        exit();
    }

    public function getBedCovid()
    {
        $this->_addHeaderFiles();
        date_default_timezone_set('UTC');
        $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));


        $url = $this->url."Fasyankes";
        $headers = [
            "X-rs-id: " . $this->id_sirs,
            "X-Timestamp: " . $tStamp,
            "X-pass: " . $this->password,
            "Content-type: application/json"
        ];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $err = curl_error($curl);
        $result = curl_exec($curl);
        curl_close($curl);

        $json = json_decode($result,true);
        return $this->draw('bedcovidlist.html', ['rawat_inap' => $json['fasyankes']]);
    }

    public function getSettings()
    {
        $this->_addHeaderFiles();
        $this->assign['title'] = 'Pengaturan Modul Sirs Online';
        $this->assign['sirs_online'] = htmlspecialchars_array($this->settings('sirs_online'));
        return $this->draw('settings.html', ['settings' => $this->assign]);
    }

    public function postSaveSettings()
    {
        foreach ($_POST['sirs_online'] as $key => $val) {
            $this->settings('sirs_online', $key, $val);
        }
        $this->notify('success', 'Pengaturan telah disimpan');
        redirect(url([ADMIN, 'data_sirs', 'settings']));
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES . '/data_sirs/js/admin/data_sirs.js');
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
        $this->core->addJS(url([ADMIN, 'data_sirs', 'javascript']), 'footer');
    }
}
