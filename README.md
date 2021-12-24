<p align="right">
    <b>Codename: mLITE</b><br>
    Modular Khanza LITE
</p>

<p>
<img src="https://raw.githubusercontent.com/basoro/Khanza-Lite/master/mlite.png">
</p>
<pre>
Disclaimer
==========
Aplikasi SIMRS Khanza versi web (selanjutnya akan disebut KhanzaLITE),
saya kembangkan untuk kebutuhan pribadi sebagai Dokter Keluarga
dan diterapkan di Rumah Sakit tempat saya bekerja sebagai Dokter.
Silahkan gunakan sewajarnya.
Tidak ada biaya dalam penggunaan aplikasi ini.
Sebagaimana diterapkan juga pada SIMRS Khanza.
</pre>

# Khanza LITE V.2021

Khanza LITE 2021  dibuat sebagai alternatif ringan untuk SIMKES Khanza agar bisa dijalankan via Mobile / Browser. Kali ini Khanza LITE 2021  dibangun lagi dari awal dengan berfokus pada kesederhanaan - bahkan programer pemula dapat membuat Module-Modul sendiri. Bahkan mengganti tampilan pengguna (User Interface). Ini karena menerapkan sistem dan arsitektur aplikasi yang sangat mudah dalam bentuk Kerangka Kerja (Framework).

Oh iya, Khanza LITE 2021  memiliki panduan pemasangan yang sangat mudah juga. Hanya perlu 1 langkah penyesuaian (jika sudah ada database SIMEKS Khanza sebelumnya) atau 5 langkah pemasangan jika anda menginginkan sistem anda kosong (tanpa dummy data). Segera setelah anda menyalin file-file ke komputer / server dan pengaturan selesai, Khanza LITE 2021  siap digunakan! Proses pemasangan bahkan tidak membutuhkan waktu sebanyak yang diperlukan untuk menyalin file-filenya ;-)

Panel kontrol dan tampilan default sepenuhnya responsif, yang membuatnya dapat diakses dari perangkat seluler apa pun, bahkan di ponsel berkat kerangka kerja CSS yang digunakan - Bootstrap. Setiap modul dapat menyesuaikan dengan CSS nya sendiri.

Masih banyak fitur-fitur tersembunyi untuk kebutuhan pengembangan. Silahkan jelajahi!!


Kebutuhan Sistem
----------------

Persyaratan sistem untuk Khanza LITE 2021  sangat sederhana, sehingga setiap server modern sudah cukup. Berikut persyaratan minimal yang diperlukan

+ Apache 2.2+ dengan `mod_rewrite` atau Nginx
+ PHP versi 5.6+
+ MySQL atau MariaDB

Konfigurasi PHP harus memiliki ekstensi berikut:

+ dom
+ gd
+ mbstring
+ pdo
+ zip
+ cURL

Pemasangan
----------

1. Unduh versi terbaru [Khanza LITE 2021] (https://github.com/basoro/Khanza-Lite).

2. Ekstrak semua file dari paket terkompresi dan kemudian transfer ke direktori lokal atau server. Biasanya, file diunggah ke `www`,` htdocs` atau `public_html`.

3. Buat folder `uploads`, `tmp/` dan `admin/tmp`. Beberapa server mungkin memerlukan izin tambahan `chmod 777` untuk direktori dan file tersebut.

4. Sesuaikan pengaturan di config.php

5. Buka browser Anda dan navigasikan ke alamat tempat file Khanza LITE 2021 berada.

6. Silahkan login dengan Username: admin dan Password: admin


### Peringatan!

+ Aktifasi modul-modul yang belum diaktifkan jika diperlukan, misalnya...
  - IGD
  - Apotek Ralan
  - Dokter Ralan
  - Laboratorium
  - Radiologi
  - ICD 9 - 10 Request
  - Presensi
  - Profil
  - Dan seterusnya....

+ Untuk pengguna Apache, pastikan file `.htaccess` juga ada di server. Tanpanya Khanza LITE 2021 tidak akan berfungsi.
+ Untuk pengguna Nginx, tambahkan konfigurasi berikut di pengaturan nginx.conf (atau sejenisnya)

```bash
location  / {
    index  index.php;
    if (!-e $request_filename) {
        rewrite / /index.php last;
    }
}

location  /admin {
    index index.php;
    try_files $uri $uri/ /admin/index.php?$args;
}
```

Jika ada didalam folder, misalnya `Khanza-Lite`

```bash
location  /lite {
    index  index.php;
    if (!-e $request_filename) {
        rewrite / /lite/index.php last;
    }
}

location  /lite/admin {
    index index.php;
    try_files $uri $uri/ /lite/admin/index.php?$args;
}
```

Untuk masuk ke panel administrasi, tambahkan `/admin/` di akhir URL.
#### Login: `admin` Kata sandi: `admin`
Ini harus diubah segera setelah login untuk alasan keamanan. Juga dapat mengganti nama direktori dengan panel administrasi.  (Anda perlu mengubahnya pada `config.php`)

Demo dan Info lebih lanjut di https://basoro.org
