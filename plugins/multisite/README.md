# Plugin Multisite

Plugin **Multisite** menyediakan fitur **registrasi mandiri** untuk membuat instance mLITE berbasis **subdomain** (single source code) dengan **database per tenant**.

Contoh:

- Platform: `puskesmas.online`
- Tenant: `barabai.puskesmas.online`
- Nama database tenant: `barabai_{MYSQLDATABASE}`

## Konsep

- Satu source code mLITE untuk semua tenant.
- Penentuan tenant berdasarkan `HTTP_HOST`.
- Koneksi database otomatis memakai nama database tenant (`{subdomain}_{MYSQLDATABASE}`).
- Upload dipisah per tenant di folder `uploads/{subdomain}`.

## Konfigurasi .env

Tambahkan pada `.env` (platform):

- `MULTISITE_ENABLE=true`
- `MULTISITE_DOMAIN=puskesmas.online`
- `MULTISITE_RESERVED_SUBDOMAINS=www,admin,api,static,assets,cdn,mail`

Database platform (wajib MySQL/MariaDB):

- `MYSQLHOST=localhost`
- `MYSQLPORT=3306`
- `MYSQLDATABASE=sql_puskesmas_online` (contoh)
- `MYSQLUSER=sql_puskesmas_online`
- `MYSQLPASSWORD=...`

## DNS

Wajib ada wildcard subdomain:

- `A puskesmas.online -> IP server`
- `A *.puskesmas.online -> IP server`

## Nginx (wajib rewrite ke index.php)

Minimal `location /`:

```nginx
location / {
    index index.php;
    try_files $uri $uri/ /index.php?$args;
}
```

Pastikan PHP handler aktif (contoh php-fpm socket):

```nginx
location ~ \.php$ {
    include fastcgi_params;
    fastcgi_pass unix:/tmp/php-cgi-83.sock;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
}
```

Untuk admin:

```nginx
location /admin {
    index index.php;
    try_files $uri $uri/ /admin/index.php?$args;
}
```

## Instalasi Database Platform

Import schema utama ke database platform (`MYSQLDATABASE`):

- File: `mlite_db.sql`

Pastikan table `mlite_modules` berisi `multisite` (sudah di-seed di `mlite_db.sql`).

## Akses

### Halaman pendaftaran (publik)

- `https://puskesmas.online/daftar`

Submit:

- `POST https://puskesmas.online/daftar/save`

### Halaman admin (panel)

- Menu: **Multisite** → **Kelola**

Fitur:

- Lihat daftar tenant pada tabel `mlite_multisite_tenants`
- Aktif/nonaktif tenant (field `status`)
- Hapus tenant dari registry (database tenant tidak dihapus otomatis)

## Troubleshooting

## Keamanan

- Form pendaftaran menggunakan CSRF token berbasis session.
- Terdapat honeypot field untuk memblokir bot sederhana.
- Captcha sederhana (pertanyaan matematika) untuk mengurangi spam.
- Rate limit berbasis IP (blokir sementara jika terlalu banyak percobaan).

### 1) `404 Not Found nginx` saat akses `/daftar`

Penyebab:

- Rewrite Nginx tidak meneruskan request ke `index.php`
- `root` vhost bukan folder mLITE (file `index.php` tidak ada)

Solusi:

- Pastikan `try_files $uri $uri/ /index.php?$args;` ada di `location /`
- Pastikan PHP handler `location ~ \.php$` aktif
- Coba akses `https://puskesmas.online/index.php` untuk memastikan `index.php` ditemukan

### 2) `Access denied ... to database '{subdomain}_{MYSQLDATABASE}'` saat `/daftar/save`

Penyebab:

- User MySQL tidak punya privilege `CREATE` untuk membuat database tenant

Solusi (MariaDB/MySQL):

```sql
GRANT CREATE ON *.* TO 'sql_puskesmas_online'@'localhost';
GRANT ALL PRIVILEGES ON `%\\_sql_puskesmas_online`.* TO 'sql_puskesmas_online'@'localhost';
FLUSH PRIVILEGES;
```

Jika web server tidak connect sebagai `@localhost`, gunakan host yang sesuai (`'%'` atau IP).

### 3) `REFERENCES command denied ...` saat import schema tenant

Penyebab:

- User MySQL tidak punya privilege `REFERENCES` untuk membuat foreign key saat import `mlite_db.sql`.

Solusi:

```sql
GRANT REFERENCES ON *.* TO 'sql_puskesmas_online'@'localhost';
FLUSH PRIVILEGES;
```

Rekomendasi privilege minimum agar provisioning lancar:

```sql
GRANT CREATE, ALTER, INDEX, INSERT, UPDATE, DELETE, SELECT, REFERENCES, DROP
ON *.* TO 'sql_puskesmas_online'@'localhost';
FLUSH PRIVILEGES;
```

### 4) `Table '...mlite_settings' doesn't exist`

Penyebab:

- Database yang dipakai belum di-import schema mLITE (`mlite_db.sql`) atau salah memilih database.

Solusi:

- Pastikan `MYSQLDATABASE` benar dan schema sudah di-import.
