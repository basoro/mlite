<?php

namespace Plugins\Multisite;

use PDO;
use PHPMailer\PHPMailer\PHPMailer;
use Systems\Multisite;
use Systems\SiteModule;

class Site extends SiteModule
{
    public function routes()
    {
        $this->route('daftar', 'getDaftar');
        $this->route('daftar/save', 'postDaftarSave');
        $this->route('daftar/verify', 'getDaftarVerify');
    }

    public function getDaftar()
    {
        if (!Multisite::isPlatformHost()) {
            http_response_code(404);
            $this->setTemplate(false);
            return 'Not Found';
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION['multisite_csrf'] = bin2hex(random_bytes(16));
        [$captchaQuestion, $captchaAnswer] = $this->generateCaptcha();
        $_SESSION['multisite_captcha'] = $captchaAnswer;

        $base = Multisite::matchedBaseDomain();
        if ($base === '') {
            $base = Multisite::baseDomain();
        }

        $this->setTemplate(false);
        header('Content-Type: text/html; charset=utf-8');
        echo $this->draw('register.html', [
            'multisite' => [
                'base_domain' => $base,
                'reserved' => (string) \env('MULTISITE_RESERVED_SUBDOMAINS', 'www,admin,api,static,assets,cdn,mail'),
                'csrf' => $_SESSION['multisite_csrf'],
                'captcha_question' => $captchaQuestion,
            ],
        ]);
        exit;
    }

    public function postDaftarSave()
    {
        if (!Multisite::isPlatformHost()) {
            http_response_code(404);
            $this->respondError('Not Found', 404);
        }

        if (defined('DBDRIVER') && DBDRIVER === 'sqlite') {
            $this->respondError('Multisite membutuhkan MySQL.', 400);
        }

        $subdomain = strtolower(trim((string) ($_POST['subdomain'] ?? '')));
        $nama = trim((string) ($_POST['nama_instansi'] ?? ''));
        $email = $this->normalizeEmail((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $honeypot = trim((string) ($_POST['website'] ?? ''));
        $csrf = (string) ($_POST['csrf'] ?? '');
        $captcha = trim((string) ($_POST['captcha'] ?? ''));

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if ($honeypot !== '') {
            $this->respondError('Permintaan tidak valid.', 400);
        }
        if ($csrf === '' || empty($_SESSION['multisite_csrf']) || !hash_equals((string) $_SESSION['multisite_csrf'], $csrf)) {
            $this->respondError('Sesi pendaftaran tidak valid. Silakan ulangi dari halaman pendaftaran.', 400);
        }
        if ($captcha === '' || empty($_SESSION['multisite_captcha']) || (string) $_SESSION['multisite_captcha'] !== $captcha) {
            $this->respondError('Captcha salah. Silakan coba lagi.', 400);
        }

        if ($subdomain === '' || !preg_match('/^[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/', $subdomain)) {
            $this->respondError('Subdomain tidak valid.', 400);
        }

        $reserved = array_filter(array_map('trim', explode(',', (string) \env('MULTISITE_RESERVED_SUBDOMAINS', 'www,admin,api,static,assets,cdn,mail'))));
        if (in_array($subdomain, $reserved, true)) {
            $this->respondError('Subdomain tidak tersedia.', 400);
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->respondError('Email tidak valid.', 400);
        }

        if (strlen($password) < 6) {
            $this->respondError('Password minimal 6 karakter.', 400);
        }

        $base = Multisite::matchedBaseDomain();
        if ($base === '') {
            $base = Multisite::baseDomain();
        }
        if ($base === '') {
            $this->respondError('Konfigurasi domain multisite belum benar.', 400);
        }
        $dbName = DBNAME . '_' . $subdomain;

        try {
            set_time_limit(0);

            $this->ensureRateLimitTable();
            $this->checkRateLimit();
            $this->ensureEmailRateLimitTable();

            $this->ensureTenantsTable();

            $pdoServer = new PDO(
                "mysql:host=" . DBHOST . ";port=" . DBPORT . ";charset=utf8mb4",
                DBUSER,
                DBPASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );

            $token = bin2hex(random_bytes(32));
            $tokenExpiresAt = date('Y-m-d H:i:s', time() + 60 * 60);
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $adminUsername = 'admin';

            $existingBySubdomain = $this->db('mlite_multisite_tenants')->where('subdomain', $subdomain)->oneArray();
            if ($existingBySubdomain && (int) ($existingBySubdomain['is_installed'] ?? 0) === 1) {
                $this->respondError('Subdomain sudah terdaftar.', 400);
            }

            $existingByEmail = $this->db('mlite_multisite_tenants')->where('admin_email', $email)->oneArray();
            if ($existingByEmail && (int) ($existingByEmail['is_installed'] ?? 0) === 1) {
                $this->respondError('Email sudah terdaftar.', 400);
            }

            if ($existingBySubdomain && $this->isActivePending($existingBySubdomain)) {
                $this->respondSuccess([
                    'status' => 'success',
                    'message' => 'Subdomain sudah diajukan sebelumnya. Silakan cek email aktivasi.',
                    'ui' => [
                        'title' => 'Pendaftaran Diterima',
                        'heading' => 'Cek Email Aktivasi',
                        'subheading' => 'Kami tidak mengirim ulang email untuk mencegah penyalahgunaan.',
                    ],
                    'data' => [
                        'subdomain' => $existingBySubdomain['subdomain'],
                        'db_name' => $existingBySubdomain['db_name'],
                    ],
                    'next_steps' => [
                        'Cek inbox/spam email Anda.',
                        'Jika link sudah kedaluwarsa, silakan daftar ulang.',
                    ],
                ]);
            }

            if ($existingByEmail && $this->isActivePending($existingByEmail) && (!$existingBySubdomain || (int) $existingByEmail['id'] !== (int) $existingBySubdomain['id'])) {
                $this->respondSuccess([
                    'status' => 'success',
                    'message' => 'Email sudah digunakan untuk pendaftaran sebelumnya. Silakan cek email aktivasi.',
                    'ui' => [
                        'title' => 'Pendaftaran Diterima',
                        'heading' => 'Cek Email Aktivasi',
                        'subheading' => 'Kami tidak mengirim ulang email untuk mencegah penyalahgunaan.',
                    ],
                    'data' => [
                        'subdomain' => $existingByEmail['subdomain'],
                        'db_name' => $existingByEmail['db_name'],
                    ],
                    'next_steps' => [
                        'Cek inbox/spam email Anda.',
                        'Jika link sudah kedaluwarsa, silakan daftar ulang.',
                    ],
                ]);
            }

            $this->checkEmailRateLimit($email);

            $activateUrl = $this->buildPlatformUrl('/daftar/verify?token=' . $token);
            $this->sendActivationEmail($email, $base, $subdomain, $activateUrl);

            $now = date('Y-m-d H:i:s');
            $saveData = [
                'subdomain' => $subdomain,
                'db_name' => $dbName,
                'admin_email' => $email,
                'admin_username' => $adminUsername,
                'admin_password_hash' => $passwordHash,
                'install_token' => $token,
                'install_token_expires_at' => $tokenExpiresAt,
                'is_installed' => 0,
                'status' => 0,
                'requested_at' => $now,
                'created_at' => $now,
                'installed_at' => null,
            ];

            $updateId = 0;
            if ($existingBySubdomain) {
                $updateId = (int) $existingBySubdomain['id'];
            } elseif ($existingByEmail && (int) ($existingByEmail['is_installed'] ?? 0) === 0 && !$this->isActivePending($existingByEmail)) {
                $updateId = (int) $existingByEmail['id'];
            }

            if ($updateId > 0) {
                $this->db('mlite_multisite_tenants')->where('id', $updateId)->save($saveData);
            } else {
                $this->db('mlite_multisite_tenants')->save($saveData);
            }

            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
                ? 'https'
                : 'http';
            $tenantUrl = $scheme . '://' . $subdomain . '.' . $base;
            $adminUrl = $tenantUrl . '/' . ADMIN . '/';

            $payload = [
                'status' => 'success',
                'message' => 'Permintaan pendaftaran diterima. Silakan cek email untuk aktivasi.',
                'ui' => [
                    'title' => 'Pendaftaran Diterima',
                    'heading' => 'Cek Email Aktivasi',
                    'subheading' => 'Instalasi tenant dilakukan setelah link aktivasi diklik.',
                ],
                'data' => [
                    'subdomain' => $subdomain,
                    'db_name' => $dbName,
                    'tenant_url' => $tenantUrl,
                    'admin_url' => $adminUrl,
                    'admin_username' => $adminUsername,
                ],
                'next_steps' => [
                    'Cek email Anda dan klik link aktivasi.',
                    'Setelah aktivasi berhasil, buka admin_url untuk login.',
                ],
            ];

            $this->respondSuccess($payload);
        } catch (\Throwable $e) {
            $this->respondError($e->getMessage(), 400);
        }
    }

    public function getDaftarVerify()
    {
        if (!Multisite::isPlatformHost()) {
            http_response_code(404);
            $this->respondError('Not Found', 404);
        }

        if (defined('DBDRIVER') && DBDRIVER === 'sqlite') {
            $this->respondError('Multisite membutuhkan MySQL.', 400);
        }

        $this->ensureTenantsTable();

        $token = (string) ($_GET['token'] ?? '');
        if ($token === '' || !preg_match('/^[a-f0-9]{64}$/', $token)) {
            $this->respondError('Token aktivasi tidak valid.', 400);
        }

        $tenant = $this->db('mlite_multisite_tenants')->where('install_token', $token)->oneArray();
        if (!$tenant) {
            $this->respondError('Token aktivasi tidak ditemukan.', 404);
        }

        $exp = (string) ($tenant['install_token_expires_at'] ?? '');
        if ($exp === '' || strtotime($exp) < time()) {
            $this->respondError('Token aktivasi sudah kedaluwarsa. Silakan daftar ulang.', 400);
        }

        if ((int) ($tenant['is_installed'] ?? 0) === 1) {
            $base = Multisite::matchedBaseDomain();
            if ($base === '') {
                $base = Multisite::baseDomain();
            }
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
                ? 'https'
                : 'http';
            $tenantUrl = $scheme . '://' . $tenant['subdomain'] . '.' . $base;
            $adminUrl = $tenantUrl . '/' . ADMIN . '/';

            $this->respondSuccess([
                'status' => 'success',
                'message' => 'Tenant sudah aktif.',
                'data' => [
                    'subdomain' => $tenant['subdomain'],
                    'db_name' => $tenant['db_name'],
                    'tenant_url' => $tenantUrl,
                    'admin_url' => $adminUrl,
                    'admin_username' => $tenant['admin_username'] ?? 'admin',
                ],
                'next_steps' => [
                    'Buka admin_url untuk login.',
                ],
            ]);
        }

        $dbName = (string) ($tenant['db_name'] ?? '');
        $subdomain = (string) ($tenant['subdomain'] ?? '');
        $email = (string) ($tenant['admin_email'] ?? '');
        $adminUsername = (string) ($tenant['admin_username'] ?? 'admin');
        $passwordHash = (string) ($tenant['admin_password_hash'] ?? '');

        if ($dbName === '' || $subdomain === '' || $passwordHash === '') {
            $this->respondError('Data pendaftaran tidak lengkap. Silakan daftar ulang.', 400);
        }

        try {
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
            if (!$exists->fetchColumn()) {
                $pdoServer->exec("CREATE DATABASE `" . str_replace('`', '``', $dbName) . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            }

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

            $pdoTenant->prepare("UPDATE mlite_users SET password = ?, email = ? WHERE username = ?")
                ->execute([$passwordHash, $email, $adminUsername]);

            $this->db('mlite_multisite_tenants')->where('id', (int) $tenant['id'])->save([
                'is_installed' => 1,
                'status' => 1,
                'installed_at' => date('Y-m-d H:i:s'),
                'install_token' => null,
                'install_token_expires_at' => null,
            ]);

            $base = Multisite::matchedBaseDomain();
            if ($base === '') {
                $base = Multisite::baseDomain();
            }
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
                ? 'https'
                : 'http';
            $tenantUrl = $scheme . '://' . $subdomain . '.' . $base;
            $adminUrl = $tenantUrl . '/' . ADMIN . '/';

            $this->respondSuccess([
                'status' => 'success',
                'message' => 'Aktivasi berhasil. Tenant sudah aktif.',
                'data' => [
                    'subdomain' => $subdomain,
                    'db_name' => $dbName,
                    'tenant_url' => $tenantUrl,
                    'admin_url' => $adminUrl,
                    'admin_username' => $adminUsername,
                ],
                'next_steps' => [
                    'Buka admin_url untuk login.',
                ],
            ]);
        } catch (\Throwable $e) {
            $this->respondError($e->getMessage(), 400);
        }
    }

    private function generateCaptcha(): array
    {
        $a = random_int(2, 20);
        $b = random_int(2, 20);
        if (random_int(0, 1) === 0) {
            return ["Berapa {$a} + {$b} ?", (string) ($a + $b)];
        }
        if ($a < $b) {
            [$a, $b] = [$b, $a];
        }
        return ["Berapa {$a} - {$b} ?", (string) ($a - $b)];
    }

    private function ensureRateLimitTable(): void
    {
        $this->db()->pdo()->exec("
            CREATE TABLE IF NOT EXISTS `mlite_multisite_rate_limits` (
                `id` int NOT NULL AUTO_INCREMENT,
                `ip` varchar(64) NOT NULL,
                `attempts` int NOT NULL DEFAULT 0,
                `blocked_until` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `ip` (`ip`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC
        ");
    }

    private function ensureTenantsTable(): void
    {
        $this->db()->pdo()->exec("
            CREATE TABLE IF NOT EXISTS `mlite_multisite_tenants` (
                `id` int NOT NULL AUTO_INCREMENT,
                `subdomain` varchar(63) NOT NULL,
                `db_name` varchar(128) NOT NULL,
                `admin_email` varchar(191) DEFAULT NULL,
                `admin_username` varchar(64) DEFAULT 'admin',
                `admin_password_hash` varchar(255) DEFAULT NULL,
                `install_token` varchar(128) DEFAULT NULL,
                `install_token_expires_at` datetime DEFAULT NULL,
                `is_installed` tinyint(1) NOT NULL DEFAULT 0,
                `status` tinyint(1) NOT NULL DEFAULT 1,
                `requested_at` datetime DEFAULT NULL,
                `created_at` datetime DEFAULT NULL,
                `installed_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `subdomain` (`subdomain`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC
        ");

        $this->migrateTenantsTable();
    }

    private function checkRateLimit(): void
    {
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        if ($ip === '') {
            return;
        }
        $now = date('Y-m-d H:i:s');
        $row = $this->db('mlite_multisite_rate_limits')->where('ip', $ip)->oneArray();
        if ($row && !empty($row['blocked_until']) && strtotime((string) $row['blocked_until']) > time()) {
            $this->respondError('Terlalu banyak percobaan. Silakan coba lagi nanti.', 429);
        }
        $attempts = (int) ($row['attempts'] ?? 0);
        $attempts++;
        $blockedUntil = null;
        if ($attempts >= 10) {
            $blockedUntil = date('Y-m-d H:i:s', time() + 15 * 60);
            $attempts = 0;
        }
        if ($row) {
            $this->db('mlite_multisite_rate_limits')->where('ip', $ip)->save([
                'attempts' => $attempts,
                'blocked_until' => $blockedUntil,
                'updated_at' => $now,
            ]);
        } else {
            $this->db('mlite_multisite_rate_limits')->save([
                'ip' => $ip,
                'attempts' => $attempts,
                'blocked_until' => $blockedUntil,
                'updated_at' => $now,
            ]);
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

    private function respondSuccess(array $payload): void
    {
        if ($this->wantsJson()) {
            header('Content-Type: application/json');
            echo json_encode($payload);
            exit;
        }

        $this->setTemplate(false);
        header('Content-Type: text/html; charset=utf-8');
        $ui = is_array($payload['ui'] ?? null) ? $payload['ui'] : [];
        echo $this->draw('result.html', [
            'multisite' => [
                'success' => true,
                'title' => (string) ($ui['title'] ?? 'Pendaftaran Berhasil'),
                'heading' => (string) ($ui['heading'] ?? 'Berhasil'),
                'subheading' => (string) ($ui['subheading'] ?? 'Permintaan Anda telah diproses.'),
                'message' => $payload['message'] ?? 'Berhasil.',
                'data' => $payload['data'] ?? [],
                'next_steps' => $payload['next_steps'] ?? [],
            ],
        ]);
        exit;
    }

    private function respondError(string $message, int $statusCode): void
    {
        if ($this->wantsJson()) {
            header('Content-Type: application/json');
            http_response_code($statusCode);
            echo json_encode(['status' => 'error', 'message' => $message]);
            exit;
        }

        http_response_code($statusCode);
        $this->setTemplate(false);
        header('Content-Type: text/html; charset=utf-8');
        echo $this->draw('result.html', [
            'multisite' => [
                'success' => false,
                'title' => 'Pendaftaran Gagal',
                'heading' => 'Pendaftaran Gagal',
                'subheading' => 'Silakan periksa pesan berikut, lalu coba lagi.',
                'message' => $message,
                'data' => [],
                'next_steps' => [],
            ],
        ]);
        exit;
    }

    private function wantsJson(): bool
    {
        if (isset($_GET['format']) && $_GET['format'] === 'json') {
            return true;
        }
        $accept = (string) ($_SERVER['HTTP_ACCEPT'] ?? '');
        if (stripos($accept, 'application/json') !== false) {
            return true;
        }
        $xhr = (string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        if (strtolower($xhr) === 'xmlhttprequest') {
            return true;
        }
        return false;
    }

    private function buildPlatformUrl(string $path): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
            ? 'https'
            : 'http';
        $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
        $host = preg_replace('/:\d+$/', '', strtolower($host));
        $path = '/' . ltrim($path, '/');
        return $scheme . '://' . $host . $path;
    }

    private function sendActivationEmail(string $email, string $baseDomain, string $subdomain, string $activateUrl): void
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $this->settings->get('api.apam_smtp_host');
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $this->settings->get('api.apam_smtp_port');
        $mail->Username = $this->settings->get('api.apam_smtp_username');
        $mail->Password = $this->settings->get('api.apam_smtp_password');

        $fromEmail = $this->settings->get('settings.email') ?: ('noreply@' . $baseDomain);
        $fromName = $this->settings->get('settings.nama_instansi') ?: ('Multisite ' . $baseDomain);

        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($email, $email);
        $mail->isHTML(true);
        $mail->Subject = 'Aktivasi tenant mLITE';
        $mail->Body = '<p>Pendaftaran tenant <strong>' . htmlspecialchars($subdomain . '.' . $baseDomain, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</strong> diterima.</p>'
            . '<p>Klik link berikut untuk memulai instalasi dan aktivasi:</p>'
            . '<p><a href="' . htmlspecialchars($activateUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">' . htmlspecialchars($activateUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</a></p>'
            . '<p>Link ini berlaku selama 1 jam.</p>';

        $mail->send();
    }

    private function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    private function isActivePending(array $row): bool
    {
        if ((int) ($row['is_installed'] ?? 0) === 1) {
            return false;
        }
        $token = (string) ($row['install_token'] ?? '');
        $exp = (string) ($row['install_token_expires_at'] ?? '');
        if ($token === '' || $exp === '') {
            return false;
        }
        return strtotime($exp) > time();
    }

    private function ensureEmailRateLimitTable(): void
    {
        $this->db()->pdo()->exec("
            CREATE TABLE IF NOT EXISTS `mlite_multisite_email_rate_limits` (
                `id` int NOT NULL AUTO_INCREMENT,
                `email` varchar(191) NOT NULL,
                `attempts` int NOT NULL DEFAULT 0,
                `blocked_until` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC
        ");
    }

    private function checkEmailRateLimit(string $email): void
    {
        if ($email === '') {
            return;
        }
        $now = date('Y-m-d H:i:s');
        $row = $this->db('mlite_multisite_email_rate_limits')->where('email', $email)->oneArray();
        if ($row && !empty($row['blocked_until']) && strtotime((string) $row['blocked_until']) > time()) {
            $this->respondError('Terlalu banyak permintaan aktivasi untuk email ini. Silakan coba lagi nanti.', 429);
        }
        $attempts = (int) ($row['attempts'] ?? 0);
        $attempts++;
        $blockedUntil = null;
        if ($attempts >= 5) {
            $blockedUntil = date('Y-m-d H:i:s', time() + 15 * 60);
            $attempts = 0;
        }
        if ($row) {
            $this->db('mlite_multisite_email_rate_limits')->where('email', $email)->save([
                'attempts' => $attempts,
                'blocked_until' => $blockedUntil,
                'updated_at' => $now,
            ]);
        } else {
            $this->db('mlite_multisite_email_rate_limits')->save([
                'email' => $email,
                'attempts' => $attempts,
                'blocked_until' => $blockedUntil,
                'updated_at' => $now,
            ]);
        }
    }

    private function migrateTenantsTable(): void
    {
        $pdo = $this->db()->pdo();

        $stmt = $pdo->prepare("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'mlite_multisite_tenants'
        ");
        $stmt->execute();
        $columns = [];
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $col) {
            $columns[(string) $col] = true;
        }

        $toAdd = [];
        if (!isset($columns['admin_password_hash'])) {
            $toAdd[] = "ADD COLUMN `admin_password_hash` varchar(255) DEFAULT NULL";
        }
        if (!isset($columns['install_token'])) {
            $toAdd[] = "ADD COLUMN `install_token` varchar(128) DEFAULT NULL";
        }
        if (!isset($columns['install_token_expires_at'])) {
            $toAdd[] = "ADD COLUMN `install_token_expires_at` datetime DEFAULT NULL";
        }
        if (!isset($columns['is_installed'])) {
            $toAdd[] = "ADD COLUMN `is_installed` tinyint(1) NOT NULL DEFAULT 0";
        }
        if (!isset($columns['requested_at'])) {
            $toAdd[] = "ADD COLUMN `requested_at` datetime DEFAULT NULL";
        }
        if (!isset($columns['installed_at'])) {
            $toAdd[] = "ADD COLUMN `installed_at` datetime DEFAULT NULL";
        }

        if ($toAdd) {
            $pdo->exec("ALTER TABLE `mlite_multisite_tenants` " . implode(', ', $toAdd));
        }

        $stmt = $pdo->prepare("
            SELECT INDEX_NAME
            FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'mlite_multisite_tenants'
        ");
        $stmt->execute();
        $indexes = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $indexes[(string) $row['INDEX_NAME']] = true;
        }

        if (!isset($indexes['admin_email'])) {
            $pdo->exec("ALTER TABLE `mlite_multisite_tenants` ADD KEY `admin_email` (`admin_email`)");
        }
        if (!isset($indexes['install_token'])) {
            $pdo->exec("ALTER TABLE `mlite_multisite_tenants` ADD UNIQUE KEY `install_token` (`install_token`)");
        }
        if (!isset($indexes['is_installed'])) {
            $pdo->exec("ALTER TABLE `mlite_multisite_tenants` ADD KEY `is_installed` (`is_installed`)");
        }
    }
}
