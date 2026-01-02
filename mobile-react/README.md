Penting untuk Deployment di Railway: Agar QR Code yang dihasilkan mengarah ke domain publik Railway (bukan IP internal container), Anda mungkin perlu menambahkan Environment Variable di Dashboard Railway:

- Key : REACT_NATIVE_PACKAGER_HOSTNAME
- Value : Domain publik aplikasi Anda di Railway (tanpa https:// , contoh: mobile-app-production.up.railway.app ).
Jika variabel ini tidak diset, Expo mungkin akan menampilkan QR code dengan IP internal yang tidak bisa diakses dari HP Anda.