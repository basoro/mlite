# Plugin mLITE Logs

Modul pencatatan dan pemantauan query log database pada mLITE. Merekam semua query SQL yang dieksekusi oleh pengguna (INSERT, UPDATE, DELETE) beserta binding parameter, waktu eksekusi, pesan error, dan identitas pengguna.

## Akses Modul

- Masuk ke panel admin mLITE.
- Buka menu **mLITE Logs**.
- Pilih submenu sesuai kebutuhan:
  - Kelola

## Panduan Pengguna (Petugas)

Plugin ini bersifat read-only untuk sebagian besar pengguna; hanya admin yang perlu memantau log secara rutin.

1. **Melihat Daftar Log**
   - Buka **mLITE Logs → Kelola**.
   - Tabel menampilkan semua query log: ID, teks SQL, binding parameter, waktu eksekusi (`created_at`), pesan error (jika ada), dan nama pengguna.
   - Nama pengguna ditampilkan dari data pegawai; jika tidak ditemukan ditampilkan "Tidak Diketahui".

2. **Mencari Log**
   - Gunakan dropdown **Field Pencarian** untuk memilih kolom: `id`, `sql_text`, `bindings`, `created_at`, `error_message`, atau `username`.
   - Isi teks pencarian, lalu klik **Cari** atau tekan Enter.
   - Tabel memperbarui hasil secara real-time menggunakan DataTables.

3. **Melihat Detail Log**
   - Klik baris log untuk membuka halaman detail yang menampilkan SQL lengkap, binding, waktu, dan pesan error secara utuh.

## Panduan Admin

1. **Mengaktifkan Pencatatan Log**
   - Log query hanya dicatat jika opsi `log_query` bernilai `ya` di **Pengaturan → settings.log_query**.
   - Aktifkan melalui **Admin → Pengaturan** sistem mLITE.

2. **Memantau Error Database**
   - Filter log berdasarkan kolom `error_message` untuk menemukan query yang gagal.
   - Gunakan informasi SQL dan binding untuk mendiagnosis masalah data atau bug pada modul.

3. **Analisis Aktivitas Pengguna**
   - Filter berdasarkan `username` untuk melihat seluruh aktivitas write database dari satu pengguna.
   - Grafik distribusi log per username tersedia dengan klik ikon **Chart**.

4. **Membersihkan Log Lama**
   - Plugin tidak menyediakan fitur hapus massal otomatis melalui UI.
   - Pembersihan log lama dapat dilakukan langsung melalui query SQL ke tabel `mlite_query_logs` oleh administrator database.

## Catatan

- Log hanya merekam query yang dieksekusi melalui mekanisme `QueryWrapper::logPdoQuery`; query yang dieksekusi secara langsung tanpa wrapper tidak tercatat.
- Volume log dapat tumbuh cepat pada sistem dengan banyak transaksi; lakukan pembersihan berkala untuk menjaga performa database.
- Tabel log: `mlite_query_logs` dengan kolom `id`, `sql_text`, `bindings`, `created_at`, `error_message`, `username`.
- Akses ke plugin ini sebaiknya dibatasi hanya untuk admin sistem karena log dapat mengandung data sensitif.
