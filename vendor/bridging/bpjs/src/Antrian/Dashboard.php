<?php

namespace Bridging\Bpjs\Antrian;

use Bridging\Bpjs\AntrianService;

class Dashboard extends AntrianService
{
    /**
        Parameter1 : {diisi tanggal}=> 2021-04-16
        Parameter2 : {diisi waktu}=> rs atau server
     */
    public function perTanggal($tanggal, $waktu)
    {
        return $this->get('dashboard/waktutunggu/tanggal/' . $tanggal . '/waktu/' . $waktu);
    }
    /**
        Parameter1 : {diisi bulan}=> 07
        Parameter2 : {diisi tahun}=> 2021
        Parameter3 : {diisi waktu}=> rs atau server
        a) Waktu Task 1 = Waktu tunggu admisi dalam detik
        b) Waktu Task 2 = Waktu layan admisi dalam detik
        c) Waktu Task 3 = Waktu tunggu poli dalam detik
        d) Waktu Task 4 = Waktu layan poli dalam detik
        e) Waktu Task 5 = Waktu tunggu farmasi dalam detik
        f) Waktu Task 6 = Waktu layan farmasi dalam detik
        g) Insertdate = Waktu pengambilan data, timestamp dalam milisecond
        h) Waktu server adalah data waktu (task 1-6) yang dicatat oleh server BPJS Kesehatan setelah RS mengimkan data, sedangkan waktu rs adalah data waktu (task 1-6) yang dikirimkan oleh RS
     */
    public function perBulan($bulan, $tahun, $waktu)
    {
        return $this->get('dashboard/waktutunggu/bulan/' . $bulan . '/tahun/' . $tahun . '/waktu/' . $waktu);
    }
}