<p align="right">
    <b>Codename: mLITE</b><br>
</p>

<p>
<img src="https://raw.githubusercontent.com/basoro/khanza-lite/master/mlite.png">
</p>
<pre>
Disclaimer
==========
mLITE tidak berafiliasi langsung dengan SIMRS Khanza ataupun YASKI
Aplikasi SIMRS Khanza versi web ini (selanjutnya akan disebut mLITE),
saya kembangkan untuk kebutuhan pribadi sebagai Dokter Keluarga
dan diterapkan di Rumah Sakit tempat saya bekerja sebagai Dokter.
Silahkan gunakan sewajarnya.
Tidak ada biaya dalam penggunaan aplikasi ini.
Sebagaimana diterapkan juga pada SIMRS Khanza.
</pre>

# mLITE

mLITE adalah penerus Khanza LITE, dibuat sebagai alternatif ringan untuk SIMKES Khanza agar bisa dijalankan via Mobile / Browser. Kali ini mLITE dibangun lagi dari awal dengan berfokus pada kesederhanaan - programer pemula pun dapat membuat Module-Modul sendiri. Bahkan mengganti tampilan pengguna (User Interface). Ini karena menerapkan sistem dan arsitektur aplikasi yang sangat mudah dalam bentuk Kerangka Kerja (Framework).

Oh iya, mLITE memiliki panduan pemasangan yang sangat mudah juga. Hanya perlu 1 langkah penyesuaian. Segera setelah anda menyalin file-file ke komputer / server dan pengaturan selesai, mLITE siap digunakan! Proses pemasangan bahkan tidak membutuhkan waktu sebanyak yang diperlukan untuk menyalin file-filenya ;-)

Panel kontrol dan tampilan default sepenuhnya responsif, yang membuatnya dapat diakses dari perangkat seluler apa pun, bahkan di ponsel berkat kerangka kerja CSS yang digunakan - Bootstrap. Setiap modul dapat menyesuaikan dengan CSS nya sendiri.

Masih banyak fitur-fitur tersembunyi untuk kebutuhan pengembangan. Silahkan jelajahi!!


Kebutuhan Sistem
----------------

Persyaratan sistem untuk mLITE  sangat sederhana, sehingga setiap server modern sudah cukup. Berikut persyaratan minimal yang diperlukan

+ Apache 2.2+ dengan `mod_rewrite` atau Nginx
+ PHP versi 5.5+
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

### Pemasangan menggunakan composer.
1. Jalankan perintah composer untuk pemasangan paket utama dan independensi

```
$ composer create-project basoro/khanza-lite
```

2. Buat folder `uploads`, `tmp/` dan `admin/tmp`. Beberapa server mungkin memerlukan izin tambahan `chmod 777` untuk direktori dan file tersebut.

3. Sesuaikan pengaturan di config.php

4. Buka browser Anda dan navigasikan ke alamat tempat file mLITE berada.

5. Silahkan login dengan Username: admin dan Password: admin

### Pemasangan Manual
1. Unduh versi terbaru [mLITE] (https://github.com/basoro/khanza-lite).

2. Ekstrak semua file dari paket terkompresi dan kemudian transfer ke direktori lokal atau server. Biasanya, file diunggah ke `www`,` htdocs` atau `public_html`.

3. Jalankan perintah composer untuk pemasangan independensi
```
$ composer install
```

4. Buat folder `uploads`, `tmp/` dan `admin/tmp`. Beberapa server mungkin memerlukan izin tambahan `chmod 777` untuk direktori dan file tersebut.

5. Sesuaikan pengaturan di config.php

6. Buka browser Anda dan navigasikan ke alamat tempat file mLITE berada.

7. Silahkan login dengan Username: admin dan Password: admin


### Untuk pengembangan
Anda bisa melakukan debuging dan proses pengembangan dengan menggunakan build-in server PHP dengan menjalankan perintah berikut diterminal (lingkungan Unix)

```
cd systems
php -d max_execution_time=180 -S 0.0.0.0:8080 -t .. srv/router.php
```

Atau dalam lingkungan Windows dengan klik 2x pada file

```
systems\srv\startServer.bat
```

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

+ Untuk pengguna Apache, pastikan file `.htaccess` juga ada di server. Tanpanya mLITE tidak akan berfungsi.
+ Untuk pengguna Nginx, tambahkan konfigurasi berikut di pengaturan nginx.conf (atau sejenisnya)

```bash
location  / {
    index  index.php;
    if (!-e $request_filename) {
        rewrite / /index.php last;
    }
}

location ^~ /systems/data/ {
    deny all;
    return 403;
}

location  /admin {
    index index.php;
    try_files $uri $uri/ /admin/index.php?$args;
}
```

Jika ada didalam folder, misalnya `lite`

```bash
location  /lite {
    index  index.php;
    if (!-e $request_filename) {
        rewrite / /lite/index.php last;
    }
}

location ^~ /lite/systems/data/ {
    deny all;
    return 403;
}

location  /lite/admin {
    index index.php;
    try_files $uri $uri/ /lite/admin/index.php?$args;
}
```

Untuk masuk ke panel administrasi, tambahkan `/admin/` di akhir URL.
#### Login: `admin` Kata sandi: `admin`
Ini harus diubah segera setelah login untuk alasan keamanan. Juga dapat mengganti nama direktori dengan panel administrasi.  (Anda perlu mengubahnya pada `config.php`)


# Some Screenshot
| ![frame_generic_light](https://raw.githubusercontent.com/basoro/khanza-lite/mlite/docs/1.png) | ![frame_generic_light (1)](https://raw.githubusercontent.com/basoro/khanza-lite/mlite/docs/2.png) | ![frame_generic_light (2)](https://raw.githubusercontent.com/basoro/khanza-lite/mlite/docs/3.png) |
| :---: | :---: | :---: |
| ![frame_generic_light (3)](https://raw.githubusercontent.com/basoro/khanza-lite/mlite/docs/4.png) | ![frame_generic_light (4)](https://raw.githubusercontent.com/basoro/khanza-lite/mlite/docs/5.png) | ![frame_generic_light (5)](https://raw.githubusercontent.com/basoro/khanza-lite/mlite/docs/6.png) |
| ![frame_generic_light (6)](https://raw.githubusercontent.com/basoro/khanza-lite/mlite/docs/7.png) | ![frame_generic_light (7)](https://raw.githubusercontent.com/basoro/khanza-lite/mlite/docs/8.png) | ![frame_generic_light (8)](https://raw.githubusercontent.com/basoro/khanza-lite/mlite/docs/9.png) |

## Demo
Demo dan Info lebih lanjut di https://mlite.id
