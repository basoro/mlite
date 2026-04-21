# Plugin mLITE API Key

Modul pengelolaan API key untuk mLITE, memungkinkan admin membuat, mengatur, dan menguji kunci akses API yang digunakan oleh aplikasi eksternal untuk berinteraksi dengan sistem mLITE.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **mLITE API Key**.
- Pilih submenu sesuai kebutuhan:
  - Manage API
  - Alat Pengujian

## Panduan Pengguna (Petugas)

Plugin ini dikelola oleh admin. Petugas teknis dapat menggunakan **Alat Pengujian** untuk mengeksplorasi endpoint API yang tersedia.

1. **Alat Pengujian (Swagger UI)**
   - Buka **mLITE API Key → Alat Pengujian**.
   - Halaman menampilkan dokumentasi API interaktif (Swagger/OpenAPI) yang dikonversi dari file `MLITE.postman_collection.json`.
   - Daftar semua tabel database yang dapat diakses melalui endpoint **Master** ditampilkan secara otomatis.
   - Gunakan antarmuka Swagger untuk mencoba request API langsung dari browser.

## Panduan Admin

1. **Membuat API Key Baru**
   - Buka **mLITE API Key → Manage API**.
   - Klik **Tambah** untuk membuka form pembuatan API key.
   - Isi kolom berikut:
     - **API Key**: kunci akses unik (sistem dapat men-generate otomatis).
     - **Username**: akun pengguna mLITE yang terikat dengan API key ini.
     - **Method**: pilih satu atau beberapa metode HTTP yang diizinkan (`GET`, `POST`, `PUT`, `DELETE`).
     - **IP Range**: batasi akses berdasarkan alamat IP atau rentang IP (kosongkan untuk semua IP).
     - **Exp Time**: tanggal dan waktu kadaluarsa API key (kosongkan untuk tanpa batas).
   - Klik **Simpan**.

2. **Mengubah API Key**
   - Klik baris API key yang ingin diubah, lalu pilih **Edit** dari context menu.
   - Perbarui field yang diperlukan, lalu klik **Simpan**.

3. **Menghapus API Key**
   - Klik baris API key, lalu pilih **Hapus** dari context menu.
   - Konfirmasi penghapusan; API key yang dihapus tidak dapat dipulihkan.

4. **Mencari dan Memfilter Data**
   - Gunakan kolom pencarian di tabel untuk mencari berdasarkan field tertentu: `id`, `api_key`, `username`, `method`, `ip_range`, atau `exp_time`.
   - Klik header kolom untuk mengurutkan data.

5. **Melihat Detail API Key**
   - Klik ikon detail untuk melihat informasi lengkap satu API key.

6. **Visualisasi Grafik**
   - Tersedia tampilan grafik distribusi API key berdasarkan kolom tertentu (default: per method).

## Catatan

- API key dikirim melalui header HTTP `X-Api-Key` pada setiap request ke endpoint API mLITE.
- Autentikasi juga mendukung **Bearer Token** (`Authorization: Bearer <token>`).
- Satu API key dapat diberi izin untuk beberapa method HTTP sekaligus (disimpan sebagai nilai dipisah koma).
- Pembatasan IP (`ip_range`) menambah lapisan keamanan; gunakan notasi CIDR atau alamat IP tunggal.
- Log query API dapat dipantau melalui plugin **mLITE Logs** jika fitur `log_query` diaktifkan di pengaturan sistem.
