# Plugin UTD

Dokumentasi singkat penggunaan modul **UTD** (Unit Transfusi Darah) di mLITE untuk pengelolaan pendonor, data donor, stok darah, dan komponen darah.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **UTD**.
- Pilih submenu sesuai kebutuhan:
  - Kelola
  - Data Pendonor
  - Data Donor
  - Stok Darah
  - Komponen Darah

## Panduan Pengguna (Petugas)

1. **Data Pendonor**
   - Buka submenu **Data Pendonor**.
   - Klik **Tambah** untuk mendaftarkan pendonor baru; nomor pendonor dibuat otomatis dengan format `UTD000001`.
   - Isi data lengkap: nama, alamat (dengan pencarian wilayah propinsi/kabupaten/kecamatan/kelurahan), golongan darah, tanggal lahir, dan data kontak.
   - Klik **Simpan** untuk menyimpan, atau **Update** untuk mengubah data pendonor yang ada.
   - Gunakan tombol **Kartu Donor** untuk mencetak kartu barcode pendonor (format PDF kartu nama 98×59 mm).
   - Gunakan fitur **Cetak** untuk mencetak daftar pendonor ke PDF (orientasi landscape).
   - Jika WA Gateway aktif, nomor pendonor dapat dikirim via WhatsApp.

2. **Data Donor**
   - Buka submenu **Data Donor**.
   - Catat setiap kegiatan donor darah: pilih pendonor dari daftar, isi tanggal donor, golongan darah, volume, dan petugas yang bertugas.
   - Klik **Simpan** untuk data baru atau **Update** untuk memperbarui.
   - Hapus data donor dengan tombol **Hapus**.

3. **Stok Darah**
   - Buka submenu **Stok Darah**.
   - Tambahkan kantong darah dengan mengisi nomor kantong, pilih komponen darah, golongan darah, tanggal ambil, dan tanggal kedaluwarsa.
   - Gunakan **Update** untuk memperbarui status kantong yang sudah dipakai atau **Hapus** untuk menghapus.

## Panduan Admin

1. **Komponen Darah**
   - Buka submenu **Komponen Darah**.
   - Tambah komponen darah baru dengan mengisi kode dan nama komponen (misal: WB, PRC, FFP, TC).
   - Klik **Simpan** untuk data baru atau **Update** untuk mengubah kode/nama komponen.
   - Komponen darah ini digunakan sebagai referensi pada input Stok Darah.

2. **Pencarian Wilayah**
   - Pada form Data Pendonor, tersedia pencarian live (AJAX) untuk propinsi, kabupaten, kecamatan, dan kelurahan.
   - Jika wilayah belum ada di database, sistem akan menambahkannya secara otomatis saat menyimpan data pendonor.

## Catatan

- Nomor pendonor dibuat otomatis oleh sistem dengan format `UTD` + 6 digit urut; tidak perlu diisi manual.
- Cetak daftar pendonor menggunakan tombol **Cetak** di halaman Data Pendonor; data yang dicetak difilter berdasarkan kata kunci pencarian.
- Stok darah perlu dipantau secara rutin untuk memastikan tidak ada kantong darah yang melewati tanggal kedaluwarsa.
- Integrasi dengan plugin **WA Gateway** memungkinkan pengiriman notifikasi WhatsApp kepada pendonor.
