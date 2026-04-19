# Plugin AFM

Dokumentasi singkat penggunaan modul **AFM** di mLITE.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **API AFM mLITE**.
- Pilih submenu sesuai kebutuhan:
  - Kelola

## Panduan Pengguna (Petugas)

Plugin AFM bersifat konfigurasi teknis dan tidak memiliki alur operasional harian untuk petugas. Akses diberikan kepada admin teknis yang mengelola integrasi API.

## Panduan Admin

1. **Kelola (Pengaturan Token API)**
   - Buka menu **API AFM mLITE → Kelola**.
   - Isi field **AFM Token** dengan token autentikasi yang diberikan oleh penyedia layanan AFM.
   - Isi **Username Finger** dan **Password Finger** untuk integrasi perangkat fingerprint.
   - Isi **X-Header-Token** sesuai nama header yang disepakati dengan sistem eksternal (default: `X-Header-Token`).
   - Klik **Simpan** untuk menyimpan konfigurasi.

## Catatan

- Token AFM bersifat rahasia; jangan bagikan ke pihak yang tidak berwenang.
- Perubahan token memerlukan sinkronisasi ulang dengan sistem eksternal yang menggunakan API ini.
- Plugin ini termasuk kategori **bridging** dan berfungsi sebagai katalog titik akses API AFM mLITE untuk integrasi sistem lain.
