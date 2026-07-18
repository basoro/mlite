<?php
/**
 * Mini PACS Worklist Monitor
 * Watches for incoming C-FIND requests and updates retrieval status.
 */

// Define necessary constants usually defined in index.php
define('DS', DIRECTORY_SEPARATOR);
define('BASE_DIR', __DIR__ . '/../../');
require_once BASE_DIR . 'config.php';
require_once BASE_DIR . 'systems/lib/QueryWrapper.php';

// Initialize DB connection using constants from config
if (defined('DBDRIVER') && DBDRIVER == 'sqlite') {
    Systems\Lib\QueryWrapper::connect("sqlite:" . BASE_DIR . "/systems/data/mlite.sdb");
} else {
    Systems\Lib\QueryWrapper::connect("mysql:host=".DBHOST.";port=".DBPORT.";dbname=".DBNAME, DBUSER, DBPASS);
}
$db = new Systems\Lib\QueryWrapper();

// Ensure status table exists
$db->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_mini_pacs_worklist_status` (
  `noorder` varchar(20) NOT NULL,
  `pulled_at` datetime DEFAULT NULL,
  `notified` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`noorder`)
)");

$worklist_dir = __DIR__ . '/../../uploads/pacs/';
$request_dir = $worklist_dir . 'worklist/';

if (!is_dir($request_dir)) {
    mkdir($request_dir, 0755, true);
}

echo "Monitoring $request_dir ...\n";

while (true) {
    $files = glob($request_dir . "*");
    foreach ($files as $file) {
        if (is_file($file)) {
            echo "Processing request: " . basename($file) . "\n";
            
            // Extract AccessionNumber (0008,0050) or RequestedProcedureID (0040,1001)
            $output = shell_exec("export PATH=\$PATH:/usr/local/bin:/usr/bin:/opt/homebrew/bin && dcmdump +P \"0008,0050\" " . escapeshellarg($file) . " 2>/dev/null");
            
            $noorder = '';
            if ($output && preg_match('/\[(.*?)\]/', $output, $matches)) {
                $noorder = trim($matches[1]);
            }
            
            if ($noorder) {
                echo "Detected pull for No Order: $noorder\n";
                // Update status table using cross-platform prepared statement
                $sql = "INSERT INTO mlite_mini_pacs_worklist_status (noorder, pulled_at, notified) VALUES (?, NOW(), 0)";
                if (defined('DBDRIVER') && DBDRIVER == 'sqlite') {
                    $sql .= " ON CONFLICT(noorder) DO UPDATE SET pulled_at = excluded.pulled_at, notified = 0";
                } else {
                    $sql .= " ON DUPLICATE KEY UPDATE pulled_at = VALUES(pulled_at), notified = 0";
                }
                $stmt = $db->pdo()->prepare($sql);
                $stmt->execute([$noorder]);
            }
            
            // Delete the request file to signal it's processed
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
    sleep(2); // Poll every 2 seconds
}
