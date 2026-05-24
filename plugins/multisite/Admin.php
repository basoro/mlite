<?php

namespace Plugins\Multisite;

use Systems\AdminModule;
use Systems\Multisite;

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
                `status` tinyint(1) NOT NULL DEFAULT 1,
                `created_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `subdomain` (`subdomain`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC
        ");
    }
}
