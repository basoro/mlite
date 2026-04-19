# Plugin Presensi

Dokumentasi singkat penggunaan modul **Presensi** di mLITE.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Presensi**.
- Pilih submenu sesuai kebutuhan:
  - Presensi Masuk
  - Rekap Presensi
  - Rekap Bulanan
  - Jadwal Pegawai
  - Jadwal Tambahan
  - *(Khusus admin)* Barcode Presensi, Jam Masuk, Jam Jaga, Pengaturan

## Panduan Pengguna (Petugas)

1. **Presensi Masuk**
   - Halaman ini digunakan untuk mencatat atau memantau kehadiran pegawai.
   - Data presensi ditampilkan sesuai tanggal yang dipilih.

2. **Rekap Presensi**
   - Lihat rekap kehadiran pegawai berdasarkan periode tertentu.
   - Tersedia opsi **Cetak** untuk mencetak rekap dalam format laporan.
   - Tersedia opsi **Export Excel** untuk mengunduh data rekap ke format spreadsheet.

3. **Rekap Bulanan**
   - Lihat ringkasan presensi per bulan untuk seluruh pegawai (admin) atau pegawai di departemen/bidang yang sama (non-admin).
   - Gunakan filter bulan dan tahun untuk memilih periode yang diinginkan.
   - Tersedia opsi **Cetak** rekap bulanan.

4. **Jadwal Pegawai**
   - Lihat jadwal shift pegawai per bulan.
   - Non-admin hanya melihat jadwal pegawai di departemen dan bidang yang sama.
   - Tambah jadwal baru melalui tombol tambah, isi pegawai, tahun, bulan, dan shift per hari (h1–h31).
   - Edit jadwal yang sudah ada melalui tombol edit pada baris jadwal.

5. **Jadwal Tambahan**
   - Kelola jadwal shift tambahan di luar jadwal rutin pegawai.
   - Sama seperti Jadwal Pegawai, tersedia fitur tambah dan edit dengan filter bulan dan tahun.

## Panduan Admin

1. **Barcode Presensi**
   - Generate dan kelola barcode untuk setiap pegawai.
   - Barcode digunakan sebagai identitas pegawai saat melakukan presensi dengan scanner.

2. **Jam Masuk**
   - Kelola daftar jam masuk (shift) yang tersedia.
   - Tambah shift baru: isi nama shift, jam masuk, dan jam pulang.
   - Shift ini digunakan sebagai pilihan saat membuat jadwal pegawai (role admin).

3. **Jam Jaga**
   - Kelola daftar jam jaga per departemen.
   - Tambah jam jaga baru: pilih departemen dan shift yang berlaku untuk departemen tersebut.
   - Jam jaga ini digunakan sebagai pilihan jadwal bagi pengguna non-admin saat membuat jadwal di departemennya.

4. **Pengaturan**
   - Atur lokasi GPS kantor/faskes:
     - **Latitude** dan **Longitude**: koordinat lokasi absensi (contoh: `-2.58`, `115.37`).
     - **Distance**: radius toleransi absensi dalam kilometer (contoh: `2`).
   - Atur pesan motivasi harian (`helloworld`) yang ditampilkan kepada pegawai, pisahkan setiap pesan dengan titik koma (`;`).
   - Simpan pengaturan setelah melakukan perubahan.

## Catatan

- Pengguna dengan role `admin` dapat melihat seluruh data pegawai lintas departemen. Pengguna non-admin hanya dapat melihat dan mengelola data di departemen dan bidangnya sendiri.
- Jadwal pegawai menggunakan field `h1` hingga `h31` untuk mewakili setiap tanggal dalam sebulan.
- Koordinat GPS default saat instalasi adalah Barabai, Hulu Sungai Tengah (lat: `-2.58`, lon: `115.37`). Sesuaikan dengan lokasi faskes Anda melalui menu **Pengaturan**.
- Plugin versi 1.2 ke atas mendukung jadwal tambahan terpisah dari jadwal rutin.
