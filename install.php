<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

$baseDir = __DIR__;
$envFile = $baseDir . '/.env';

// Baca konfigurasi env jika diselipkan melalui PaaS / Docker untuk pre-fill UI
$envConfig = [];
if (file_exists($envFile)) {
    $envConfig = @parse_ini_file($envFile) ?: [];
}

$defHost = $envConfig['MYSQLHOST'] ?? 'localhost';
$defPort = $envConfig['MYSQLPORT'] ?? '3306';
$defDb = $envConfig['MYSQLDATABASE'] ?? 'mlite';
$defUser = $envConfig['MYSQLUSER'] ?? 'root';

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $driver = $_POST['db_driver'] ?? 'sqlite';

    if ($driver === 'sqlite') {
        try {
            ini_set('memory_limit', '1G');

            $inputFile = $baseDir . '/mlite_db.sql';
            $outputDir = $baseDir . '/systems/data';
            $outputFile = $outputDir . '/mlite.sdb';

            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0777, true);
            }
            @chmod($outputDir, 0777);

            if (!is_writable($outputDir)) {
                throw new Exception("Folder '$outputDir' tidak writable. Silakan ubah permission folder ini.");
            }

            if (file_exists($outputFile)) {
                unlink($outputFile);
            }

            $pdo = new PDO("sqlite:$outputFile");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            @chmod($outputFile, 0666);

            $pdo->exec("PRAGMA synchronous = OFF");
            $pdo->exec("PRAGMA journal_mode = MEMORY");
            $pdo->exec("PRAGMA strict = OFF");

            $sqlContent = file_get_contents($inputFile);

            $sqlContent = preg_replace('/^--.*$/m', '', $sqlContent);
            $sqlContent = preg_replace('/^\/\*.*\*\/$/m', '', $sqlContent);
            $sqlContent = preg_replace('/^SET .*$/m', '', $sqlContent);

            $statements = preg_split('/;\s*\n/', $sqlContent);

            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (empty($statement))
                    continue;

                if (stripos($statement, 'CREATE TABLE') === 0) {
                    processCreateTable($pdo, $statement);
                } elseif (stripos($statement, 'INSERT INTO') === 0) {
                    processInsert($pdo, $statement);
                }
            }

            // Create .env
            $envData = "DBDRIVER=sqlite\n";
            file_put_contents($envFile, $envData);

            $success = "SQLite Database successfully generated!";
            // header('Location: /');
            // exit;


        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } elseif ($driver === 'mysql') {
        $host = $_POST['db_host'] ?? 'localhost';
        $port = $_POST['db_port'] ?? '3306';
        $dbName = $_POST['db_name'] ?? 'mlite';
        $user = $_POST['db_user'] ?? 'root';
        $pass = $_POST['db_pass'] ?? '';

        try {
            ini_set('memory_limit', '1G');

            // Connect to MySQL server without DB first
            $dsnServer = "mysql:host=$host;port=$port;charset=utf8mb4";
            $pdoServer = new PDO($dsnServer, $user, $pass);
            $pdoServer->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Create database if not exists
            $pdoServer->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            // Connect to the specific database
            $dsn = "mysql:host=$host;port=$port;dbname=$dbName;charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Check if tables exist
            $stmt = $pdo->query("SHOW TABLES");
            if ($stmt->rowCount() > 0) {
                throw new Exception("Database sudah ada datanya. Silakan gunakan database yang kosong atau hapus tabel terlebih dahulu.");
            }

            // Execute SQL Dump
            $inputFile = $baseDir . '/mlite_db.sql';
            $sqlContent = file_get_contents($inputFile);

            // Turn off foreign key checks for import
            $pdo->exec("SET foreign_key_checks = 0;");

            // Clean up to basic statements
            $sqlContent = preg_replace('/^--.*$/m', '', $sqlContent);
            $sqlContent = preg_replace('/^\/\*.*\*\/$/m', '', $sqlContent);

            // Multi statement queries can be heavy, splitting by ;
            $statements = preg_split('/;\s*\n/', $sqlContent);
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (empty($statement))
                    continue;
                $pdo->exec($statement);
            }

            $pdo->exec("SET foreign_key_checks = 1;");

            // Create .env
            $envData = "DBDRIVER=mysql\n";
            $envData .= "MYSQLHOST=$host\n";
            $envData .= "MYSQLPORT=$port\n";
            $envData .= "MYSQLDATABASE=$dbName\n";
            $envData .= "MYSQLUSER=$user\n";
            $envData .= "MYSQLPASSWORD=$pass\n";

            file_put_contents($envFile, $envData);

            $success = "MySQL Database successfully installed!";
            // header('Location: /');
            // exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Helper Functions for SQLite parsing 
function processCreateTable($pdo, $sql)
{
    if (!preg_match('/CREATE TABLE `?(\w+)`?/i', $sql, $matches))
        return;
    $tableName = $matches[1];

    if (!preg_match('/\((.*)\)[^\)]*$/s', $sql, $matches))
        return;
    $body = $matches[1];

    $pkColumn = null;
    if (preg_match('/PRIMARY KEY\s*\(`?([^`\)]+)`?\)/', $body, $matches)) {
        $pkColumn = $matches[1];
        if (strpos($pkColumn, ',') !== false) {
            $pkColumn = null;
        }
    }

    $lines = explode("\n", $body);
    $columnDefs = [];
    $constraints = [];
    $indexes = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line === ')')
            continue;
        if (substr($line, -1) === ',')
            $line = substr($line, 0, -1);

        if (preg_match('/^(?:UNIQUE )?KEY `?(\w+)`?\s*\((.*)\)/i', $line, $m)) {
            $isUnique = (stripos($line, 'UNIQUE') === 0);
            $indexName = $m[1];
            $cols = str_replace('`', '', $m[2]);
            $cols = preg_replace('/\(\d+\)/', '', $cols);
            $indexes[] = "CREATE " . ($isUnique ? "UNIQUE " : "") . "INDEX IF NOT EXISTS `idx_{$tableName}_{$indexName}` ON `$tableName` ($cols)";
            continue;
        }

        if (stripos($line, 'PRIMARY KEY') === 0) {
            if (!$pkColumn) {
                $line = preg_replace('/\s+USING\s+\w+/i', '', $line);
                $constraints[] = $line;
            }
            continue;
        }

        if (stripos($line, 'CONSTRAINT') === 0) {
            $line = preg_replace('/CONSTRAINT `\w+`/', '', $line);
            $line = preg_replace('/ON UPDATE CURRENT_TIMESTAMP/i', '', $line);
            $constraints[] = $line;
            continue;
        }

        if (preg_match('/^`?(\w+)`?\s+(.*)$/', $line, $m)) {
            $colName = $m[1];
            $def = $m[2];

            $def = preg_replace('/int\(\d+\)/i', 'INTEGER', $def);
            $def = preg_replace('/tinyint\(\d+\)/i', 'INTEGER', $def);
            $def = preg_replace('/smallint\(\d+\)/i', 'INTEGER', $def);
            $def = preg_replace('/bigint\(\d+\)/i', 'INTEGER', $def);
            $def = preg_replace('/double(\(.*\))?/i', 'REAL', $def);
            $def = preg_replace('/float(\(.*\))?/i', 'REAL', $def);
            $def = preg_replace('/decimal(\(.*\))?/i', 'REAL', $def);
            $def = preg_replace('/varchar\(\d+\)/i', 'TEXT', $def);
            $def = preg_replace('/char\(\d+\)/i', 'TEXT', $def);
            $def = preg_replace('/text/i', 'TEXT', $def);
            $def = preg_replace('/longtext/i', 'TEXT', $def);
            $def = preg_replace('/mediumtext/i', 'TEXT', $def);
            $def = preg_replace('/enum\(.*\)/i', 'TEXT', $def);
            $def = preg_replace('/set\(.*\)/i', 'TEXT', $def);
            $def = preg_replace('/date/i', 'TEXT', $def);
            $def = preg_replace('/datetime/i', 'TEXT', $def);
            $def = preg_replace('/time/i', 'TEXT', $def);
            $def = preg_replace('/timestamp/i', 'TEXT', $def);
            $def = preg_replace('/blob/i', 'BLOB', $def);
            $def = preg_replace('/longblob/i', 'BLOB', $def);

            if (stripos($def, 'AUTO_INCREMENT') !== false) {
                $def = str_ireplace('AUTO_INCREMENT', '', $def);
                if ($colName === $pkColumn) {
                    $def = preg_replace('/(int|integer)\s+(not\s+null)?/i', 'INTEGER PRIMARY KEY AUTOINCREMENT', $def);
                }
            }

            $def = str_ireplace('UNSIGNED', '', $def);
            $def = str_ireplace('ZEROFILL', '', $def);
            $def = preg_replace('/ON\s+UPDATE\s+CURRENT_TIMESTAMP/i', '', $def);
            $def = str_ireplace('CURRENT_TIMESTAMP', 'CURRENT_TIMESTAMP', $def);

            $columnDefs[] = "`$colName` $def";
        }
    }

    $createSql = "CREATE TABLE `$tableName` (\n" . implode(",\n", array_merge($columnDefs, $constraints)) . "\n)";

    try {
        $pdo->exec($createSql);
    } catch (PDOException $e) {
    }

    foreach ($indexes as $idxSql) {
        try {
            $pdo->exec($idxSql);
        } catch (PDOException $e) {
        }
    }
}

function processInsert($pdo, $sql)
{
    $sql = str_replace('\\"', '##DQ##', $sql);
    $sql = str_replace("'", "''", $sql);
    $sql = str_replace('"', "'", $sql);
    $sql = str_replace('##DQ##', '"', $sql);
    $sql = str_replace('\\\\', '\\', $sql);

    try {
        $pdo->exec($sql);
    } catch (PDOException $e) {
    }
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="./favicon.png" type="image/x-icon">
    <title>mLITE Installer</title>
    <style>
        :root {
            --bg-color: #f7f9fc;
            --container-bg: #ffffff;
            --text-main: #333333;
            --text-muted: #6b7280;
            --border-color: #e5e7eb;
            --primary: #4b5563;
            /* Grey modern */
            --primary-hover: #374151;
            --error-bg: #fee2e2;
            --error-text: #b91c1c;
            --radius: 8px;
        }

        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .installer-container {
            background: var(--container-bg);
            width: 100%;
            max-width: 500px;
            padding: 2.5rem;
            border-radius: var(--radius);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
        }

        .logo-container {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .logo-container img {
            max-width: 120px;
            height: auto;
        }

        .header-center {
            text-align: center;
            margin-bottom: 4rem;
        }

        h1 {
            margin-top: 0;
            margin-bottom: 0.5rem;
            color: var(--primary);
            font-size: 1.8rem;
            font-weight: 600;
        }

        p.desc {
            color: var(--text-muted);
            margin-top: 0;
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }

        h1,
        .desc {
            text-align: center;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.9rem;
        }

        input[type="text"],
        input[type="password"],
        input[type="number"],
        select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            font-size: 1rem;
            transition: border-color 0.15s;
            background: #fff;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(75, 85, 99, 0.1);
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: var(--radius);
            font-size: 0.95rem;
        }

        .alert-error {
            background-color: var(--error-bg);
            color: var(--error-text);
            border: 1px solid #fecaca;
        }

        .btn-submit {
            display: block;
            width: 100%;
            padding: 0.875rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.15s;
            margin-top: 2rem;
        }

        .btn-submit:hover {
            background-color: var(--primary-hover);
        }

        .mysql-fields {
            display: none;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px dashed var(--border-color);
        }

        .mysql-fields.active {
            display: block;
        }

        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            justify-content: center;
            align-items: center;
            z-index: 1000;
            flex-direction: column;
        }

        .loading-overlay.active {
            display: flex;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid var(--border-color);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>

    <div class="loading-overlay" id="loading">
        <div class="spinner"></div>
        <p style="margin-top: 15px; font-weight: 500; color: var(--text-main);">Instalasi database sedang berjalan...
        </p>
    </div>

    <div class="installer-container">

        <div class="logo-container">
            <img src="./themes/admin/img/logo.png" alt="mLITE Logo">
        </div>

        <div class="header-center">
            <h1>mLITE Setup</h1>
            <p class="desc">Silakan atur konfigurasi database utama Anda</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <strong>Error:</strong> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert" style="background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0;">
                <strong>Berhasil!</strong> <?= htmlspecialchars($success) ?><br><br>
                <div
                    style="margin-top: 10px; padding: 10px; background: #fee2e2; border-left: 4px solid #b91c1c; color: #991b1b; border-radius: 4px;">
                    <strong style="display:block; margin-bottom: 5px;">⚠️ PERINGATAN KEAMANAN</strong>
                    Harap segera HAPUS atau UBAH NAMA (rename) file <strong>install.php</strong> ini. Membiarkan file ini
                    ada di server dapat menimbulkan celah keamanan bagi aplikasi Anda.
                </div>
            </div>
            <a href="/" class="btn-submit"
                style="text-align: center; text-decoration: none; box-sizing: border-box;">Selesai & Buka Aplikasi</a>
        <?php else: ?>
            <form method="POST" action="" id="installForm">
                <div class="form-group">
                    <label for="db_driver">Database Driver</label>
                    <select name="db_driver" id="db_driver">
                        <option value="sqlite">SQLite (Portable / Ringan)</option>
                        <option value="mysql">MySQL / MariaDB</option>
                    </select>
                </div>

                <div class="mysql-fields" id="mysql_fields">
                    <div class="form-group">
                        <label for="db_host">Host</label>
                        <input type="text" name="db_host" id="db_host" value="<?= htmlspecialchars($defHost) ?>"
                            placeholder="127.0.0.1">
                    </div>

                    <div class="form-group">
                        <label for="db_port">Port</label>
                        <input type="number" name="db_port" id="db_port" value="<?= htmlspecialchars($defPort) ?>">
                    </div>

                    <div class="form-group">
                        <label for="db_name">Database Name</label>
                        <input type="text" name="db_name" id="db_name" value="<?= htmlspecialchars($defDb) ?>"
                            placeholder="mlite">
                    </div>

                    <div class="form-group">
                        <label for="db_user">Username</label>
                        <input type="text" name="db_user" id="db_user" value="<?= htmlspecialchars($defUser) ?>"
                            placeholder="root">
                    </div>

                    <div class="form-group">
                        <label for="db_pass">Password</label>
                        <input type="password" name="db_pass" id="db_pass"
                            placeholder="(Kosongkan jika tidak ada password)">
                    </div>
                </div>

                <button type="submit" class="btn-submit">Mulai Instalasi</button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        const driverSelect = document.getElementById('db_driver');
        const mysqlFields = document.getElementById('mysql_fields');
        const form = document.getElementById('installForm');
        const loading = document.getElementById('loading');

        function toggleFields() {
            if (driverSelect.value === 'mysql') {
                mysqlFields.classList.add('active');
            } else {
                mysqlFields.classList.remove('active');
            }
        }

        driverSelect.addEventListener('change', toggleFields);
        toggleFields(); // Initial call

        form.addEventListener('submit', function () {
            loading.classList.add('active');
        });
    </script>
</body>

</html>