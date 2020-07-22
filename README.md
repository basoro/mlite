<p align="right">
    <b>Codename: Joyoboyo</b>
</p>

# Khanza LITE V3.0

Khanza LITE 3  dibuat sebagai alternatif ringan untuk SIMKES Khanza agar bisa dijalankan via Mobile / Browser. Kali ini Khanza LITE 3  dibangun lagi dari awal dengan berfokus pada kesederhanaan - bahkan programer pemula dapat membuat Module-Modul sendiri. Bahkan mengganti tampilan pengguna (User Interface). Ini karena menerapkan sistem dan arsitektur aplikasi yang sangat mudah dalam bentuk Kerangka Kerja (Framework).

Oh iya, Khanza LITE 3  memiliki panduan pemasangan yang sangat mudah juga. Hanya perlu 1 langkah penyesuaian (jika sudah ada database SIMEKS Khanza sebelumnya) atau 5 langkah pemasangan jika anda menginginkan sistem anda kosong (tanpa dummy data). Segera setelah anda menyalin file-file ke komputer / server dan pengaturan selesai, Khanza LITE 3  siap digunakan! Proses pemasangan bahkan tidak membutuhkan waktu sebanyak yang diperlukan untuk menyalin file-filenya ;-)

Panel kontrol dan tampilan default sepenuhnya responsif, yang membuatnya dapat diakses dari perangkat seluler apa pun, bahkan di ponsel berkat kerangka kerja CSS yang digunakan - Bootstrap. Setiap modul dapat menyesuaikan dengan CSS nya sendiri.

Masih banyak fitur-fitur tersembunyi untuk kebutuhan pengembangan. Silahkan jelajahi!!


Kebutuhan Sistem
----------------

Persyaratan sistem untuk Khanza LITE 3  sangat sederhana, sehingga setiap server modern sudah cukup. Berikut persyaratan minimal yang diperlukan

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

1. Unduh versi terbaru [Khanza LITE 3] (https://github.com/basoro/Khanza-Lite).

2. Ekstrak semua file dari paket terkompresi dan kemudian transfer ke direktori lokal atau server. Biasanya, file diunggah ke `www`,` htdocs` atau `public_html`.

3. Buat folder `tmp/` dan `admin/tmp`. Beberapa server mungkin memerlukan izin tambahan `chmod 777` untuk direktori dan file tersebut.

4. **Jika anda ingin memulai pemasangan dengan data kosong, buat database baru, misal `sik` (jika sudah ada silahkan lompat ke langkah ke 6).**

5. Navigasikan ke alamat pemasangan. Misalkan `http://localhost/Khanza-Lite/install.php`, anda akan melihat tampilan pemasangan.

6. Buka browser Anda dan navigasikan ke alamat tempat file Khanza LITE 3 berada.

### Peringatan!
+ Untuk pengguna Apache, pastikan file `.htaccess` juga ada di server. Tanpanya Khanza LITE 3 tidak akan berfungsi.
+ Untuk pengguna Nginx, tambahkan konfigurasi berikut di pengaturan nginx.conf (atau sejenisnya)

```
location / {
  try_files $uri $uri/ @handler;
    }

location  /admin {
   try_files $uri $uri/ /admin/index.php?$args;
   }

location @handler {
   if (!-e $request_filename) { rewrite / /index.php last; }
   rewrite ^(.*.php)/ $1 last;
   }
```

Untuk masuk ke panel administrasi, tambahkan `/admin/` di akhir URL.
#### Login: `spv` Kata sandi: `server`
Ini harus diubah segera setelah login untuk alasan keamanan. Juga dapat mengganti nama direktori dengan panel administrasi.  (Anda perlu mengubahnya pada `config.php`)


## TANGKAPAN LAYAR

### Pemasangan

| | |
|:-------------------------:|:-------------------------:|
|<img src="https://raw.githubusercontent.com/basoro/Khanza-Lite/ataaka/screenshoot/install_1.png">  Install 1 |  <img src="https://raw.githubusercontent.com/basoro/Khanza-Lite/ataaka/screenshoot/install_2.png"> Install 2|
|<img src="https://raw.githubusercontent.com/basoro/Khanza-Lite/ataaka/screenshoot/install_3.png">  Install 3 |  <img src="https://raw.githubusercontent.com/basoro/Khanza-Lite/ataaka/screenshoot/install_4.png"> Install 4|
|<img src="https://raw.githubusercontent.com/basoro/Khanza-Lite/ataaka/screenshoot/install_5.png">  Install 5 |  <img src="https://raw.githubusercontent.com/basoro/Khanza-Lite/ataaka/screenshoot/x2_login.png"> Login Form|


### Panel Administrasi

| | |
|:-------------------------:|:-------------------------:|
|<img src="https://raw.githubusercontent.com/basoro/Khanza-Lite/ataaka/screenshoot/x1_homepage.png">  Homepage |  <img src="https://raw.githubusercontent.com/basoro/Khanza-Lite/ataaka/screenshoot/x3_dashboard.png"> Dashboard 2|
|<img src="https://raw.githubusercontent.com/basoro/Khanza-Lite/ataaka/screenshoot/x4_settings.png">  Settings |  <img src="https://raw.githubusercontent.com/basoro/Khanza-Lite/ataaka/screenshoot/x5_modules.png"> Modules|
|<img src="https://raw.githubusercontent.com/basoro/Khanza-Lite/ataaka/screenshoot/x6_pasien.png">  Pasien Baru |  <img src="https://raw.githubusercontent.com/basoro/Khanza-Lite/ataaka/screenshoot/x7_create_sep.png"> Bridging SEP|
