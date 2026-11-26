<?php

/**
 * Stub minimal pengganti objek plugin Admin saat menjalankan systems/upgrade.php.
 *
 * upgrade.php hanya memakai rantai $this->core->db()->pdo(), jadi cukup
 * menyediakan rantai tersebut di atas satu koneksi PDO SQLite.
 */
class UpgradeTestDb
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function pdo()
    {
        return $this->pdo;
    }
}

class UpgradeTestCore
{
    private $db;

    public function __construct(\PDO $pdo)
    {
        $this->db = new UpgradeTestDb($pdo);
    }

    public function db()
    {
        return $this->db;
    }
}

class UpgradeTestHarness
{
    public $core;

    public function __construct(\PDO $pdo)
    {
        $this->core = new UpgradeTestCore($pdo);
    }

    /**
     * Include upgrade.php di dalam scope method agar $this dan $version
     * tersedia persis seperti saat dipanggil dari plugin Settings.
     */
    public function jalankan($file, $version)
    {
        return include $file;
    }
}
