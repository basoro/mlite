# Plugin Surat-Surat

Dokumentasi singkat penggunaan modul **Surat-Surat** di mLITE untuk penerbitan surat rujukan, surat keterangan sakit, surat keterangan sehat, dan surat bebas narkoba pasien.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Surat-Surat**.
- Pilih submenu sesuai kebutuhan:
  - Kelola
  - Surat Rujukan
  - Surat Rujukan Lab
  - Surat Rujukan Radiologi
  - Surat Sakit
  - Surat Sehat
  - Surat Bebas Narkoba
  - Surat Kematian
  - Surat Kontrol

## Panduan Pengguna (Petugas)

1. **Surat Rujukan**
   - Buka submenu **Surat Rujukan**.
   - Klik **Tambah** untuk membuat surat baru, atau pilih surat dari daftar untuk diedit.
   - Isi nomor surat, no rawat, data pasien (nama, tanggal lahir, jenis kelamin, alamat), tujuan faskes rujukan, anamnesa, pemeriksaan fisik, pemeriksaan penunjang, diagnosa, terapi, dan alasan dirujuk.
   - Klik **Simpan** untuk menyimpan data.
   - Untuk mencetak, buka menu **Rawat Jalan** pada data kunjungan pasien, pilih **Surat Rujukan** dari menu surat yang muncul otomatis.

2. **Surat Keterangan Sakit**
   - Buka submenu **Surat Sakit**.
   - Klik **Tambah** atau cari surat yang sudah ada menggunakan kolom pencarian (nomor surat / nama pasien).
   - Isi nomor surat, no rawat, data pasien, keadaan umum, diagnosa, lama istirahat (angka dan huruf), tanggal mulai dan selesai.
   - Klik **Simpan**.
   - Cetak surat dari halaman kunjungan Rawat Jalan melalui menu **Surat Keterangan Sakit**.

3. **Surat Keterangan Sehat**
   - Buka submenu **Surat Sehat**.
   - Isi nomor surat, no rawat, data pasien, tanggal periksa, berat badan, tinggi badan, tensi, golongan darah, riwayat penyakit, dan keperluan surat.
   - Klik **Simpan**.
   - Cetak surat dari halaman kunjungan Rawat Jalan melalui menu **Surat Keterangan Sehat**.

4. **Surat Bebas Narkoba**
   - Buka submenu **Surat Bebas Narkoba**.
   - Isi nomor surat, no rawat, data pasien, tanggal pemeriksaan, hasil pemeriksaan, dan keperluan surat.
   - Klik **Simpan**.
   - Surat juga dapat dibuka dari dropdown **No. RM > Surat-Surat** pada modul pelayanan.

5. **Surat Kontrol**
   - Buka submenu **Surat Kontrol**.
   - Isi No. Rawat, klik **Cetak Surat Kontrol**.
   - Data pasien terisi otomatis; lengkapi tanggal kontrol berikutnya, poli tujuan, dokter tujuan, dan keterangan.
   - Klik **Simpan** lalu **Cetak Surat** untuk mencetak.
   - Surat juga dapat dibuka dari dropdown **No. RM > Surat-Surat** pada modul pelayanan.

6. **Surat Rujukan Lab / Radiologi**
   - Buka submenu **Surat Rujukan Lab** atau **Surat Rujukan Radiologi**.
   - Masukkan **No. Rawat** pasien.
   - Klik tombol cetak untuk membuka surat permintaan pemeriksaan.

7. **Pencarian Surat**
   - Setiap submenu dilengkapi kolom pencarian berdasarkan nomor surat atau nama pasien.
   - Gunakan tombol **Edit** untuk mengubah data dan **Hapus** untuk menghapus surat.

## Panduan Admin

1. **Pengaturan Kop dan Footer Surat**
   - Akses **Pengaturan** (getSettings) pada modul Surat-Surat.
   - Isi field **Kepala Surat** untuk kop surat institusi.
   - Isi field **Footer Surat** untuk tanda tangan atau informasi penutup.
   - Simpan perubahan.

2. **Template Surat**
   - Atur template tampilan untuk masing-masing jenis surat (rujukan, sakit, sehat) melalui field template di halaman Pengaturan.
   - Template menentukan tata letak cetak surat yang dihasilkan.

3. **Integrasi Rawat Jalan**
   - Plugin ini terintegrasi otomatis dengan modul Rawat Jalan melalui event `rawat_jalan.surat_menu`.
   - Pastikan modul Rawat Jalan aktif agar tautan cetak surat muncul pada halaman kunjungan pasien.

## Catatan

- Nomor surat diisi manual; pastikan menggunakan format penomoran yang konsisten sesuai kebijakan fasilitas kesehatan.
- Data dokter dan SIP dokter diambil otomatis dari data kunjungan (no rawat) yang terhubung ke tabel `reg_periksa`, `dokter`, dan `pasien`.
- Surat dicetak langsung dari browser; gunakan fungsi print browser atau tombol Print yang tersedia di halaman cetak.
- Satu no rawat hanya dapat memiliki satu surat per jenis saat disimpan dari halaman cetak; data lama akan tertimpa jika disimpan ulang dengan no rawat yang sama.
- Template cetak surat sekarang menampilkan **No. Rekam Medis** agar identitas pasien konsisten di setiap surat.
