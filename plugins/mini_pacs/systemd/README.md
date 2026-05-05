# Manajemen Service Systemctl Mini PACS

Berikut adalah panduan untuk mengaktifkan dan menjalankan script Mini PACS menggunakan `systemd` (`systemctl`) pada environment Linux/Docker Anda. File `.service` ini telah disesuaikan dengan _path_ direktori container Anda (`/var/www/html/`).

## Langkah-langkah Instalasi Service

1. **Salin file service ke direktori systemd sistem**
   Masuk ke terminal server / container Anda, dan jalankan perintah berikut:
   ```bash
   sudo cp /var/www/html/plugins/mini_pacs/systemd/*.service /etc/systemd/system/
   ```

2. **Reload Daemon Systemd**
   Agar sistem mengenali file service yang baru disalin, jalankan:
   ```bash
   sudo systemctl daemon-reload
   ```

3. **Pastikan Script Memiliki Izin Eksekusi**
   ```bash
   sudo chmod +x /var/www/html/plugins/mini_pacs/start_receiver.sh
   sudo chmod +x /var/www/html/plugins/mini_pacs/start_worklist.sh
   ```

## Menjalankan dan Mengaktifkan Service (Auto-Start)

Agar service otomatis berjalan ketika server/container direstart, gunakan perintah `enable --now`:

```bash
# 1. Receiver
sudo systemctl enable --now mlite-pacs-receiver.service

# 2. Worklist
sudo systemctl enable --now mlite-pacs-worklist.service

# 3. Worklist Monitor
sudo systemctl enable --now mlite-pacs-monitor.service
```

## Mengecek Status dan Log

Jika Anda ingin melihat apakah service berjalan dengan baik, atau jika ada error, gunakan perintah berikut:

**Cek Status:**
```bash
sudo systemctl status mlite-pacs-receiver.service
sudo systemctl status mlite-pacs-worklist.service
sudo systemctl status mlite-pacs-monitor.service
```

**Melihat Log (Real-time):**
```bash
# Receiver
sudo journalctl -u mlite-pacs-receiver.service -f

# Worklist
sudo journalctl -u mlite-pacs-worklist.service -f

# Monitor
sudo journalctl -u mlite-pacs-monitor.service -f
```

## Menghentikan atau Me-restart Service
```bash
# Restart
sudo systemctl restart mlite-pacs-receiver.service

# Stop
sudo systemctl stop mlite-pacs-monitor.service
```

> **Catatan:**
> Pada file `mlite-pacs-monitor.service`, *path* _binary_ PHP diset ke `/usr/local/bin/php` (standar image Docker PHP resmi). Jika environment Anda menggunakan path berbeda (misal `/usr/bin/php`), silakan ubah pada baris `ExecStart` di dalam file `mlite-pacs-monitor.service` sebelum meng-copy-nya.