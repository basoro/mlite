<?php

namespace Plugins\Multisite;

use Systems\AdminModule;
use Systems\Multisite;
use PDO;

class Admin extends AdminModule
{
    public function navigation()
    {
        if (!Multisite::isPlatformHost()) {
            return [];
        }
        return [
            'Kelola' => 'manage',
        ];
    }

    public function getManage()
    {
        if (!Multisite::isPlatformHost()) {
            http_response_code(404);
            return 'Not Found';
        }
        $this->ensureTenantsTable();

        $rows = $this->db('mlite_multisite_tenants')->desc('id')->toArray();
        $rows = htmlspecialchars_array($rows);

        return $this->draw('manage.html', [
            'multisite' => [
                'tenants' => $rows,
                'base_domain' => (string) \env('MULTISITE_DOMAIN', ''),
                'enabled' => strtolower((string) \env('MULTISITE_ENABLE', '')) === 'true',
            ],
        ]);
    }

    public function postToggleStatus()
    {
        if (!Multisite::isPlatformHost()) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Not Found']);
            exit;
        }
        $this->ensureTenantsTable();

        header('Content-Type: application/json');
        $id = (int) ($_POST['id'] ?? 0);
        $status = (int) ($_POST['status'] ?? 0);

        if ($id <= 0 || !in_array($status, [0, 1], true)) {
            echo json_encode(['status' => 'error', 'message' => 'Parameter tidak valid.']);
            exit;
        }

        $ok = $this->db('mlite_multisite_tenants')->where('id', $id)->save(['status' => $status]);
        echo json_encode(['status' => $ok ? 'success' : 'error']);
        exit;
    }

    public function postDeleteTenant()
    {
        if (!Multisite::isPlatformHost()) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Not Found']);
            exit;
        }
        $this->ensureTenantsTable();

        header('Content-Type: application/json');
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID tidak valid.']);
            exit;
        }

        $row = $this->db('mlite_multisite_tenants')->where('id', $id)->oneArray();
        if (!$row) {
            echo json_encode(['status' => 'error', 'message' => 'Tenant tidak ditemukan.']);
            exit;
        }

        $ok = $this->db('mlite_multisite_tenants')->where('id', $id)->delete();
        echo json_encode(['status' => $ok ? 'success' : 'error', 'db_name' => $row['db_name'] ?? '']);
        exit;
    }

    private function ensureTenantsTable(): void
    {
        if (defined('DBDRIVER') && DBDRIVER === 'sqlite') {
            return;
        }

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
