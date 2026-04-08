# Changelog

## [2026-04-07]

### Added
- VClaim: Tambah alur pembaruan tanggal-pulang SEP (endpoint baru `updatetglplg2`) + modal UI `plugins/vclaim/view/admin/sep_pulang.html`.
- VClaim: Tambah endpoint `spridata` untuk mengambil detail SPRI berdasarkan `no_surat` (dipakai untuk memuat data ke form).
- VClaim: Tambah aksi Update/Delete SPRI dari form SPRI (menggunakan endpoint update & delete VClaim).
- VClaim: Tambah aksi Update/Delete Kontrol dari form Kontrol (menggunakan endpoint update & delete VClaim).
- VClaim: Tambah menu SEP Internal di menu.

### Changed
- VClaim: Tambah tombol “Pulang” di riwayat SEP (ditampilkan untuk `jnsPelayanan == 1`) dan muat modal tanggal-pulang.
- VClaim: Perbaiki alur input SEP (isi otomatis No. MR dari data peserta, buat otomatis No. Rujukan saat kosong, dan izinkan klik No. SEP di riwayat untuk mengisi No. Rujukan).
- VClaim: Perbarui permintaan tanggal-pulang menggunakan HTTP `PUT` (bukan `DELETE`) dan terima nilai dari input.
- VClaim: Tingkatkan UX SPRI (tambah field No Surat + mode Update/Delete setelah memilih item, dan buat baris No Rawat bisa diklik untuk memuat data ke form).

### Fixed
- VClaim: Perkuat penanganan pesan kesalahan saat respons BPJS null/tidak valid (fallback ke “Unknown error”).
- VClaim: Perbaiki kesalahan kecil dan pengutipan token sesi di beberapa URL AJAX (mis. label “Faskes” dan pengutipan token).
