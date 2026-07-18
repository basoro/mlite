# Plugin Radiologi

Modul pengelolaan pelayanan radiologi di mLITE, mencakup penerimaan permintaan, pencatatan pemeriksaan, dan penyimpanan hasil.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Radiologi**.
- Pilih submenu sesuai kebutuhan:
  - Kelola

## Panduan Pengguna (Petugas)

1. **Daftar Kunjungan Radiologi**
   - Buka menu **Radiologi → Kelola**.
   - Tampil daftar pasien berdasarkan tanggal kunjungan (default: hari ini).
   - Filter berdasarkan tipe: **Rawat Jalan** (ralan), **Rawat Inap** (ranap), atau **Permintaan Radiologi**.
   - Filter berdasarkan status periksa: Belum, Selesai, atau Lunas.
   - Gunakan kolom pencarian untuk mencari berdasarkan no rawat, no rekam medis, nama pasien, atau nama dokter.

2. **Menerima Permintaan Pemeriksaan**
   - Permintaan radiologi yang datang dari modul Rawat Jalan/Rawat Inap tampil di tab **Permintaan**.
   - Klik detail permintaan untuk melihat daftar jenis pemeriksaan yang diminta (`permintaan_pemeriksaan_radiologi`).
   - Klik **Validasi** untuk memproses permintaan: sistem akan otomatis mencatat ke tabel `periksa_radiologi` beserta tarif dari master jenis perawatan radiologi.

3. **Mencatat Hasil Pemeriksaan**
   - Setelah pemeriksaan selesai, klik tombol **Input Hasil** pada baris pasien.
   - Isi deskripsi hasil pemeriksaan, tanggal, dan jam.
   - Data tersimpan ke tabel `hasil_radiologi`.
   - Gambar/foto pemeriksaan tersimpan ke direktori `uploads/radiologi/` dan dicatat di tabel `gambar_radiologi`.

4. **Cetak Hasil**
   - Gunakan tombol **Cetak Hasil** (`cetakhasil.html`) untuk mencetak laporan hasil radiologi.
   - Gunakan tombol **Cetak Permintaan** (`cetakpermintaan.html`) untuk mencetak formulir permintaan pemeriksaan.

5. **Hapus Data Pemeriksaan**
   - Hapus detail pemeriksaan yang salah melalui tombol hapus pada baris tindakan.
   - Hapus hasil (hasil + gambar) dari form input hasil.

## Panduan Admin

1. **Master Jenis Perawatan Radiologi**
   - Kelola daftar jenis pemeriksaan radiologi di tabel `jns_perawatan_radiologi` (melalui modul Master).
   - Pastikan setiap jenis pemeriksaan memiliki tarif yang benar: `bhp`, `tarif_perujuk`, `tarif_tindakan_dokter`, `tarif_tindakan_petugas`, `total_byr`.

2. **Pengaturan Dokter Penanggung Jawab**
   - Di menu **Settings → Umum**, atur field **Penanggung Jawab Radiologi** (`pj_radiologi`) dengan kode dokter yang bertanggung jawab.
   - Kode dokter ini digunakan sebagai default saat registrasi otomatis pasien radiologi.

3. **Pengaturan Poliklinik Radiologi**
   - Di menu **Settings → Umum**, atur field **Poliklinik Radiologi** (`radiologi`) dengan kode poliklinik radiologi.
   - Kode ini digunakan saat mendaftarkan pasien yang belum memiliki data `reg_periksa`.

4. **Hak Akses**
   - Pastikan akun petugas radiologi memiliki izin `can_read`, `can_create`, `can_update`, dan `can_delete` pada modul `radiologi`.

## Catatan

- Plugin ini mendukung tiga alur pasien: rawat jalan (ralan), rawat inap (ranap), dan permintaan langsung dari unit lain.
- Untuk pasien rawat inap, data dokter diambil dari tabel `dpjp_ranap`.
- Foto dan gambar hasil radiologi disimpan di folder `uploads/radiologi/` yang dibuat otomatis saat instalasi plugin.
