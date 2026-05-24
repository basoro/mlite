<?php

namespace Plugins\Multisite;

use PDO;
use Systems\Multisite;
use Systems\SiteModule;

class Site extends SiteModule
{
    public function routes()
    {
        $this->route('daftar', 'getDaftar');
        $this->route('daftar/save', 'postDaftarSave');
    }

    public function getDaftar()
    {
        if (!Multisite::isPlatformHost()) {
            http_response_code(404);
            $this->setTemplate(false);
            return 'Not Found';
        }

        $this->setTemplate(false);
        header('Content-Type: text/html; charset=utf-8');
        echo $this->draw('register.html', [
            'multisite' => [
                'base_domain' => Multisite::baseDomain(),
                'reserved' => (string) \env('MULTISITE_RESERVED_SUBDOMAINS', 'www,admin,api,static,assets,cdn,mail'),
            ],
        ]);
        exit;
    }

    public function postDaftarSave()
    {
        if (!Multisite::isPlatformHost()) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Not Found']);
            exit;
        }

        if (defined('DBDRIVER') && DBDRIVER === 'sqlite') {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Multisite membutuhkan MySQL.']);
            exit;
        }

        $subdomain = strtolower(trim((string) ($_POST['subdomain'] ?? '')));
        $nama = trim((string) ($_POST['nama_instansi'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($subdomain === '' || !preg_match('/^[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/', $subdomain)) {
            $this->jsonError('Subdomain tidak valid.');
        }

        $reserved = array_filter(array_map('trim', explode(',', (string) \env('MULTISITE_RESERVED_SUBDOMAINS', 'www,admin,api,static,assets,cdn,mail'))));
        if (in_array($subdomain, $reserved, true)) {
            $this->jsonError('Subdomain tidak tersedia.');
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->jsonError('Email tidak valid.');
        }

        if (strlen($password) < 6) {
            $this->jsonError('Password minimal 6 karakter.');
        }

        $dbName = $subdomain . '_' . DBNAME;

        try {
            set_time_limit(0);

            $pdoServer = new PDO(
                "mysql:host=" . DBHOST . ";port=" . DBPORT . ";charset=utf8mb4",
                DBUSER,
                DBPASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );

            $exists = $pdoServer->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
            $exists->execute([$dbName]);
            if ($exists->fetchColumn()) {
                $this->jsonError('Subdomain sudah terdaftar.');
            }

            $pdoServer->exec("CREATE DATABASE `" . str_replace('`', '``', $dbName) . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            $pdoTenant = new PDO(
                "mysql:host=" . DBHOST . ";port=" . DBPORT . ";dbname=" . $dbName . ";charset=utf8mb4",
                DBUSER,
                DBPASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );

            $sqlFile = BASE_DIR . '/mlite_db.sql';
            $this->importSqlFile($pdoTenant, $sqlFile);

            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $adminUsername = 'admin';

            $pdoTenant->prepare("UPDATE mlite_users SET password = ?, email = ?, fullname = ? WHERE username = ?")
                ->execute([$passwordHash, $email, $nama !== '' ? $nama : 'Administrator', $adminUsername]);

            if ($nama !== '') {
                $pdoTenant->prepare("UPDATE mlite_settings SET value = ? WHERE module = 'settings' AND field = 'nama_instansi'")
                    ->execute([$nama]);
            }

            if ($email !== '') {
                $pdoTenant->prepare("UPDATE mlite_settings SET value = ? WHERE module = 'settings' AND field = 'email'")
                    ->execute([$email]);
            }

            $this->db()->pdo()->exec("
                CREATE TABLE IF NOT EXISTS `mlite_multisite_tenants` (
                    `id` int NOT NULL AUTO_INCREMENT,
                    `subdomain` varchar(63) NOT NULL,
                    `db_name` varchar(128) NOT NULL,
                    `admin_email` varchar(191) DEFAULT NULL,
                    `admin_username` varchar(64) DEFAULT 'admin',
                    `status` tinyint(1) NOT NULL DEFAULT 1,
                    `created_at` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `subdomain` (`subdomain`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC
            ");

            $this->db('mlite_multisite_tenants')->save([
                'subdomain' => $subdomain,
                'db_name' => $dbName,
                'admin_email' => $email,
                'admin_username' => $adminUsername,
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
                ? 'https'
                : 'http';
            $tenantUrl = $scheme . '://' . $subdomain . '.' . Multisite::baseDomain();
            $adminUrl = $tenantUrl . '/' . ADMIN . '/';

            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'message' => 'Tenant berhasil dibuat.',
                'data' => [
                    'subdomain' => $subdomain,
                    'db_name' => $dbName,
                    'tenant_url' => $tenantUrl,
                    'admin_url' => $adminUrl,
                    'admin_username' => $adminUsername,
                ],
                'next_steps' => [
                    'Buka admin_url untuk login.',
                    'Login menggunakan admin_username dan password yang Anda buat saat pendaftaran.',
                ],
            ]);
            exit;
        } catch (\Throwable $e) {
            $this->jsonError($e->getMessage());
        }
    }

    private function importSqlFile(PDO $pdo, string $path): void
    {
        if (!is_file($path)) {
            throw new \RuntimeException('File SQL tidak ditemukan.');
        }

        $handle = fopen($path, 'r');
        if (!$handle) {
            throw new \RuntimeException('Gagal membuka file SQL.');
        }

        $statement = '';
        while (($line = fgets($handle)) !== false) {
            $trim = trim($line);
            if ($trim === '' || str_starts_with($trim, '--')) {
                continue;
            }
            $statement .= $line;
            if (substr(rtrim($trim), -1) === ';') {
                $pdo->exec($statement);
                $statement = '';
            }
        }

        if (trim($statement) !== '') {
            $pdo->exec($statement);
        }

        fclose($handle);
    }

    private function jsonError(string $message): void
    {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $message]);
        exit;
    }
}
