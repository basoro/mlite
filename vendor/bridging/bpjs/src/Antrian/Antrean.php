<?php

namespace Bridging\Bpjs\Antrian;

use Bridging\Bpjs\AntrianService;

class Antrean extends AntrianService
{
    /**
     * Json Data Pengiriman Antrean
     * {
            "kodebooking": "16032021A001",
            "jenispasien": "JKN",
            "nomorkartu": "00012345678",
            "nik": "3212345678987654",
            "nohp": "085635228888",
            "kodepoli": "ANA",
            "namapoli": "Anak",
            "pasienbaru": 0,
            "norm": "123345",
            "tanggalperiksa": "2021-01-28",
            "kodedokter": 12345,
            "namadokter": "Dr. Hendra",
            "jampraktek": "08:00-16:00",
            "jeniskunjungan": 1,
            "nomorreferensi": "0001R0040116A000001",
            "nomorantrean": "A-12",
            "angkaantrean": 12,
            "estimasidilayani": 1615869169000,
            "sisakuotajkn": 5,
            "kuotajkn": 30,
            "sisakuotanonjkn": 5,
            "kuotanonjkn": 30,
            "keterangan": "Peserta harap 30 menit lebih awal guna pencatatan administrasi."
        }
     */
    public function addAntrean($data = [])
    {
        return $this->get('antrean/add', $data);
    }

    /**
     * Json Data Pengiriman Antrean
     * {
        "kodebooking": "16032021A001",
        "keterangan": "Terjadi perubahan jadwal dokter, silahkan daftar kembali"
        }
     */
    public function batalAntrean($data = [])
    {
        return $this->get('antrean/batal', $data);
    }

    /**
     * {
        "kodebooking": "16032021A001",
        "taskid": 1,
        "waktu": 1616559330000
        }
         Alur Task Id Pasien Baru: 1-2-3-4-5 (apabila ada obat tambah 6-7)
        - Alur Task Id Pasien Lama: 3-4-5 (apabila ada obat tambah 6-7)
        - Sisa antrean berkurang pada task 5
        - Pemanggilan antrean poli pasien muncul pada task 4
        - Cek in/mulai waktu tunggu untuk pasien baru mulai pada task 1
        - Cek in/mulai waktu tunggu untuk pasien lama mulai pada task 3
        - Agar terdapat validasi pada sistem RS agar alur pengiriman Task Id berurutan dari awal, dan waktu Task Id yang kecil lebih dulu daripada Task Id yang besar (misal task Id 1=08.00, task Id 2= 08.05)             
     */
    public function timeAntrean($data = [])
    {
        return $this->get('antrean/updatewaktu', $data);
    }
    /**
     * {
        "kodebooking": "Y03-20#1617068533"
        }
     */
    public function listTaskAntrean($data = [])
    {
        return $this->get('antrean/getlisttask', $data);
    }
}