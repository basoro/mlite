# Plugin Rawat Inap

Modul pengelolaan pasien rawat inap di mLITE, mencakup pendaftaran masuk kamar, pencatatan tindakan dan obat, manajemen DPJP, hingga proses kepulangan pasien.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **Rawat Inap**.
- Pilih submenu sesuai kebutuhan:
  - Kelola

## Panduan Pengguna (Petugas)

1. **Daftar Pasien Rawat Inap**
   - Buka menu **Rawat Inap → Kelola**.
   - Tampil daftar pasien yang sedang dirawat (status pulang = `-`).
   - Filter berdasarkan periode tanggal masuk/keluar dan status pulang untuk melihat riwayat.
   - Tampilan dibatasi sesuai bangsal yang menjadi tanggung jawab akun pengguna (kecuali admin).

2. **Mendaftarkan Pasien Masuk Kamar**
   - Cari pasien dengan no rekam medis atau nama melalui form pencarian pasien.
   - Pilih pasien dengan status lanjut `Ranap` dari Rawat Jalan atau IGD.
   - Isi **Form Masuk**: kamar, dokter DPJP, tanggal masuk, jam masuk, lama rencana rawat, dan diagnosa awal.
   - Klik **Simpan** — sistem akan mengisi tarif kamar otomatis, mencatat ke `kamar_inap`, menambahkan DPJP ke `dpjp_ranap`, dan mengubah status kamar menjadi `ISI`.

3. **Manajemen DPJP (Dokter Penanggung Jawab Pasien)**
   - Tambah dokter DPJP baru melalui form Set DPJP pada detail pasien.
   - Hapus dokter DPJP yang tidak relevan menggunakan tombol hapus di daftar DPJP.
   - Satu pasien dapat memiliki lebih dari satu DPJP.

4. **Input Tindakan dan Obat**
   - Pada detail pasien, pilih kategori **Tindakan** atau **Obat**.
   - Untuk **Tindakan**: pilih jenis perawatan inap, provider (dokter/petugas/dokter+petugas), tanggal, jam, dan jumlah tindakan.
   - Untuk **Obat**: pilih obat dari master, isi jumlah dan aturan pakai — sistem otomatis membuat atau menggabungkan resep harian.
   - Hapus detail tindakan/obat yang salah melalui tombol hapus di daftar rincian.

5. **Rincian Biaya**
   - Buka tab **Rincian** pada detail pasien untuk melihat total biaya: tindakan dokter, tindakan petugas, tindakan gabungan, dan obat.
   - Informasi billing (kode billing RI.xxx) tersedia untuk proses pembayaran.

6. **Proses Kepulangan Pasien**
   - Klik **Pulangkan** pada baris pasien.
   - Isi form: status pulang, diagnosa akhir, tanggal dan jam keluar.
   - Klik **Simpan** — sistem akan memperbarui `kamar_inap`, mengubah status `reg_periksa` menjadi `Sudah`, dan status kamar menjadi `KOSONG`.
   - Pilihan status pulang: Sehat, Rujuk, APS, Meninggal, Sembuh, Membaik, Pulang Paksa, Pindah Kamar, Atas Persetujuan Dokter, dan lainnya.

7. **Ubah Penjamin**
   - Ubah penjamin (penjab) pasien yang sudah terdaftar tanpa harus mendaftar ulang.

8. **Cetak Antrian**
   - Gunakan fitur **Antrian** untuk mencetak nomor antrian atau label rawat inap pasien.

## Panduan Admin

1. **Master Kamar dan Bangsal**
   - Kelola data kamar dan bangsal di modul **Master** (tabel `kamar` dan `bangsal`).
   - Pastikan tarif kamar (`trf_kamar`) sudah diisi agar biaya rawat inap terhitung otomatis.
   - Hanya kamar dengan `statusdata = 1` yang tampil di form pendaftaran.

2. **Master Jenis Perawatan Inap**
   - Kelola daftar tindakan rawat inap di tabel `jns_perawatan_inap` melalui modul Master.
   - Pastikan tarif tindakan dokter, petugas, dan gabungan sudah benar.

3. **Hak Akses Bangsal**
   - Atur `cap` (capability) akun petugas dengan kode bangsal yang menjadi wewenangnya.
   - Petugas hanya melihat pasien di bangsal yang sesuai dengan `cap`-nya; admin melihat semua bangsal.

4. **Integrasi vClaim BPJS**
   - Jika modul `vclaim` aktif, tombol pengajuan SEP tersedia pada daftar pasien rawat inap.
   - Nomor SEP ditampilkan bila bridging sudah berhasil.

5. **Berkas Digital**
   - Master berkas digital (`master_berkas_digital`) mengatur dokumen apa saja yang dapat diunggah per pasien rawat inap.
   - File disimpan di direktori `webapps/berkasrawat/pages/upload/`.

## Catatan

- Pasien hanya dapat didaftarkan masuk kamar jika sudah memiliki data `reg_periksa` dengan `status_lanjut = Ranap`.
- Satu nomor rawat hanya dapat menempati satu kamar pada satu waktu.
- Biaya kamar dihitung otomatis: tarif kamar × lama hari rawat.
