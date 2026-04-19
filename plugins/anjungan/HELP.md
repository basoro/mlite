# Plugin Anjungan

Dokumentasi singkat penggunaan modul **Anjungan Pasien Mandiri** di mLITE.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Anjungan Pasien Mandiri**.
- Pilih submenu sesuai kebutuhan:
  - Kelola
  - Display
  - Pemanggil
  - Pengaturan

## Panduan Pengguna (Petugas)

Petugas loket, CS, dan apotek menggunakan fitur **Pemanggil** untuk memanggil nomor antrian pasien:

1. **Panggil Antrian Loket**
   - Buka **Pemanggil → Panggil Loket**.
   - Klik tombol loket yang dituju untuk memanggil nomor antrian berikutnya.
   - Sistem akan memperbarui nomor antrian dan memutar suara pemanggilan.
   - Gunakan tombol **Reset** untuk mereset nomor antrian ke awal hari.

2. **Panggil Antrian CS**
   - Buka **Pemanggil → Panggil CS**.
   - Proses sama seperti pemanggil loket, menggunakan antrian tipe CS.

3. **Panggil Antrian Apotek**
   - Buka **Pemanggil → Panggil Apotek**.
   - Proses sama seperti pemanggil loket, menggunakan antrian tipe Apotek.

4. **Display Informasi**
   - Buka submenu **Display** untuk menampilkan layar informasi antrian poliklinik, loket, laboratorium, dan apotek secara publik (dapat dipasang di monitor ruang tunggu).

## Panduan Admin

1. **Pengaturan Anjungan**
   - Buka **Anjungan → Pengaturan**.
   - Pilih **Poliklinik** yang ditampilkan di anjungan (multiselect).
   - Pilih **Cara Bayar** yang diperbolehkan mendaftar mandiri.
   - Atur nomor antrian awal untuk Loket, CS, dan Apotek.
   - Isi **Running Text** untuk setiap layar display (anjungan, loket, poli, lab, apotek, farmasi).
   - Isi **ID Video YouTube** yang akan ditayangkan di layar anjungan.
   - Klik **Simpan** untuk menyimpan pengaturan.

2. **Reset Antrian Harian**
   - Gunakan fungsi reset antrian (Loket/CS/Apotek) di awal hari atau saat antrian perlu dimulai ulang.
   - Reset dapat dipicu melalui endpoint yang tersedia di Admin.php (`resetAnjunganLoket`, `resetAnjunganCS`, `resetAnjunganApotek`).

## Catatan

- Suara pemanggilan menggunakan file audio digit (0–9) di folder `plugins/anjungan/suara/`.
- Layar Display dapat dibuka di browser terpisah atau monitor publik tanpa login admin.
- Pastikan nomor antrian awal di pengaturan sudah benar sebelum operasional dimulai setiap hari.
