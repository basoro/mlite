# Plugin Laboratorium

Modul pengelolaan pelayanan laboratorium klinik pada mLITE, mencakup antrian pasien rawat jalan, rawat inap, permintaan lab, input hasil pemeriksaan, dan cetak hasil laboratorium.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Laboratorium**.
- Pilih submenu sesuai kebutuhan:
  - Kelola

## Panduan Pengguna (Petugas)

1. **Kelola Antrian Rawat Jalan (Ralan)**
   - Buka **Laboratorium → Kelola**, tab default menampilkan daftar pasien rawat jalan hari ini.
   - Filter berdasarkan rentang tanggal kunjungan, status periksa (Belum / Sudah / Lunas), dan status bayar.
   - Klik baris pasien untuk memilih, lalu isi form pendaftaran laboratorium: nomor rekam medis, dokter, penjab.
   - Klik **Simpan** untuk mendaftarkan pasien ke antrian laboratorium.

2. **Kelola Antrian Rawat Inap (Ranap)**
   - Ganti tab ke **Ranap** pada halaman Kelola.
   - Filter pasien berdasarkan status pulang (masih dirawat / selesai / lunas) dan rentang tanggal masuk.
   - Pilih pasien, isi detail pemeriksaan lab sesuai permintaan dokter DPJP.

3. **Kelola Permintaan Lab**
   - Ganti tab ke **Permintaan** untuk melihat daftar permintaan lab dari poli/bangsal.
   - Pilih nomor order permintaan, lalu klik **Validasi Permintaan** untuk memroses sampel.
   - Setelah validasi, sistem otomatis membuat entri `periksa_lab` dan `detail_periksa_lab`.

4. **Input Hasil Pemeriksaan**
   - Dari halaman rincian pemeriksaan, isi kolom **Nilai** untuk setiap parameter lab.
   - Isi kolom **Keterangan** bila ada catatan tambahan per parameter.
   - Nilai dan keterangan tersimpan langsung ke tabel `detail_periksa_lab`.

5. **Cetak Hasil Laboratorium**
   - Klik ikon **Cetak Hasil** pada baris pemeriksaan yang sudah lengkap.
   - Sistem menghasilkan PDF hasil lab lengkap dengan QR code penanggung jawab lab.
   - File PDF tersimpan otomatis di folder `uploads/laboratorium/`.

6. **Cetak Permintaan Lab**
   - Gunakan tombol **Cetak Permintaan** untuk mencetak surat permintaan pemeriksaan.

## Panduan Admin

1. **Konfigurasi Poliklinik Laboratorium**
   - Pastikan kode poli laboratorium sudah diatur di **Pengaturan → settings.laboratorium**.
   - Kode ini digunakan saat mendaftarkan pasien baru ke antrian lab.

2. **Konfigurasi Penanggung Jawab Laboratorium**
   - Atur kode dokter penanggung jawab di **Pengaturan → settings.pj_laboratorium**.
   - Nama dokter ini muncul pada hasil cetak PDF beserta QR code tanda tangan digital.

3. **Master Jenis Perawatan Lab**
   - Kelola daftar jenis pemeriksaan laboratorium melalui **Master Data → Perawatan Laboratorium**.
   - Setiap jenis perawatan memiliki tarif (bagian RS, BHP, tarif perujuk, dll.) dan template parameter.

4. **Template Laboratorium**
   - Setiap jenis perawatan lab dapat memiliki beberapa parameter hasil (template).
   - Template berisi nama parameter, nilai rujukan, dan pembagian biaya per item.

5. **Hak Akses API**
   - Plugin ini menyediakan endpoint API (`apiList`, `apiShow`, `apiSave`, `apiUpdate`, `apiDelete`, `apiSaveDetail`, `apiDeleteDetail`, `apiSaveValidasi`, `apiSaveNilai`, `apiSaveKeterangan`).
   - Atur izin `can_read`, `can_create`, `can_update`, `can_delete` untuk modul `laboratorium` melalui manajemen pengguna.

## Catatan

- Tab **Ralan**, **Ranap**, dan **Permintaan** pada halaman Kelola menampilkan data berbeda; pastikan memilih tab yang sesuai dengan jenis pasien.
- Validasi permintaan lab otomatis mengisi tanggal dan jam sampel, serta membuat entri `periksa_lab` dari data `permintaan_lab`.
- File PDF hasil lab akan ditimpa jika dicetak ulang dengan kombinasi no_rawat, kd_jenis_prw, dan tanggal yang sama.
- Cek status layanan vclaim dideteksi otomatis; ikon terkait hanya muncul jika modul **vclaim** aktif.
