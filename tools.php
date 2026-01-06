<?php
/**
 * Tools and Router script for PHP Built-in Web Server
 * Emulates Apache .htaccess rewrite rules
 * 
 * Usage: php -S localhost:8000 tools.php
 */

if (isset($_GET['action']) && $_GET['action'] == 'migrate') {
    ini_set('memory_limit', '1G');

    $inputFile = 'mlite_db.sql';
    $outputFile = 'mlite.sdb';

    if (file_exists($outputFile)) {
        unlink($outputFile);
    }

    $pdo = new PDO("sqlite:$outputFile");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Optimization
    $pdo->exec("PRAGMA synchronous = OFF");
    $pdo->exec("PRAGMA journal_mode = MEMORY");

    // Nonaktifkan strict mode agar bisa insert '0000-00-00'
    $pdo->exec("PRAGMA strict = OFF");

    $sqlContent = file_get_contents($inputFile);

    // Clean up
    $sqlContent = preg_replace('/^--.*$/m', '', $sqlContent);
    $sqlContent = preg_replace('/^\/\*.*\*\/$/m', '', $sqlContent);
    $sqlContent = preg_replace('/^SET .*$/m', '', $sqlContent);

    // Split by semicolon at end of line to get statements
    $statements = preg_split('/;\s*\n/', $sqlContent);

    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;

        if (stripos($statement, 'CREATE TABLE') === 0) {
            processCreateTable($pdo, $statement);
        } elseif (stripos($statement, 'INSERT INTO') === 0) {
            processInsert($pdo, $statement);
        }
    }
    echo "Migration completed.\n";
    exit;
}

function processCreateTable($pdo, $sql) {
    // Extract table name
    if (!preg_match('/CREATE TABLE `?(\w+)`?/i', $sql, $matches)) return;
    $tableName = $matches[1];
    
    echo "Processing table: $tableName\n";

    // Extract body
    if (!preg_match('/\((.*)\)[^\)]*$/s', $sql, $matches)) return;
    $body = $matches[1];

    // Identify Primary Key
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
        if ($line === '' || $line === ')') continue;
        if (substr($line, -1) === ',') $line = substr($line, 0, -1);

        // Handle Keys/Indexes
        if (preg_match('/^(?:UNIQUE )?KEY `?(\w+)`?\s*\((.*)\)/i', $line, $m)) {
            $isUnique = (stripos($line, 'UNIQUE') === 0);
            $indexName = $m[1];
            $cols = str_replace('`', '', $m[2]);
            $cols = preg_replace('/\(\d+\)/', '', $cols); // Remove length
            
            $indexes[] = "CREATE " . ($isUnique ? "UNIQUE " : "") . "INDEX IF NOT EXISTS `idx_{$tableName}_{$indexName}` ON `$tableName` ($cols)";
            continue;
        }

        // Skip PRIMARY KEY line if handled
        if (stripos($line, 'PRIMARY KEY') === 0) {
            if (!$pkColumn) {
                // Clean up syntax if needed
                $line = preg_replace('/\s+USING\s+\w+/i', '', $line);
                $constraints[] = $line;
            }
            continue;
        }

        // Handle Constraints
        if (stripos($line, 'CONSTRAINT') === 0) {
            $line = preg_replace('/CONSTRAINT `\w+`/', '', $line); 
            $line = preg_replace('/ON UPDATE CURRENT_TIMESTAMP/i', '', $line);
            $constraints[] = $line;
            continue;
        }

        // Column Definition
        if (preg_match('/^`?(\w+)`?\s+(.*)$/', $line, $m)) {
            $colName = $m[1];
            $def = $m[2];

            // Type mapping
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

            // Handle AUTO_INCREMENT
            if (stripos($def, 'AUTO_INCREMENT') !== false) {
                $def = str_ireplace('AUTO_INCREMENT', '', $def);
                if ($colName === $pkColumn) {
                    // Check if it's explicitly "int NOT NULL" which is common in MySQL dumps
                    // Replace "int NOT NULL" or similar with INTEGER PRIMARY KEY AUTOINCREMENT
                    // SQLite requires exactly "INTEGER PRIMARY KEY AUTOINCREMENT" for auto-inc behavior
                    $def = preg_replace('/(int|integer)\s+(not\s+null)?/i', 'INTEGER PRIMARY KEY AUTOINCREMENT', $def);
                }
            }
            
            // Clean up MySQL specifics
            $def = str_ireplace('UNSIGNED', '', $def);
            $def = str_ireplace('ZEROFILL', '', $def);
            $def = preg_replace('/ON\s+UPDATE\s+CURRENT_TIMESTAMP/i', '', $def);
            $def = str_ireplace('CURRENT_TIMESTAMP', 'CURRENT_TIMESTAMP', $def); 
            
            // Remove USING BTREE/HASH in column definition? Unlikely but check
            
            $columnDefs[] = "`$colName` $def";
        }
    }

    $createSql = "CREATE TABLE `$tableName` (\n" . implode(",\n", array_merge($columnDefs, $constraints)) . "\n)";
    
    try {
        $pdo->exec($createSql);
    } catch (PDOException $e) {
        echo "Failed to create table $tableName: " . $e->getMessage() . "\n";
    }

    foreach ($indexes as $idxSql) {
        try {
            $pdo->exec($idxSql);
        } catch (PDOException $e) {
            echo "Failed to create index: " . $e->getMessage() . "\n";
        }
    }
}

function processInsert($pdo, $sql) {
    // Convert MySQL insert syntax to SQLite compatible
    // MySQL dump uses "val" for strings.
    // SQLite prefers 'val'.
    // We need to handle \" (escaped double quote) which is common in MySQL dumps.
    
    // 1. Protect \" (escaped double quotes) by replacing with a placeholder
    $sql = str_replace('\\"', '##DQ##', $sql);
    
    // 2. Escape existing single quotes ' to ''
    $sql = str_replace("'", "''", $sql);
    
    // 3. Convert outer double quotes " to single quotes '
    // Note: This assumes all string literals are wrapped in "
    // MySQL dump usually does VALUES ("1", "text"), so yes.
    // But be careful not to replace " inside the string (we protected \" but what about unescaped " inside string? MySQL shouldn't have unescaped " inside " string)
    // However, table names or column names might use `. We are in INSERT statement.
    // INSERT INTO `table` VALUES ...
    // We only want to replace " in values.
    
    // Simple replacement of " with ' might break if there are " that are not string delimiters.
    // But in VALUES (...), " is usually string delimiter.
    
    $sql = str_replace('"', "'", $sql);
    
    // 4. Restore protected double quotes as " (now inside single quoted string, so " is fine)
    $sql = str_replace('##DQ##', '"', $sql);
    
    // 5. Handle backslash escapes. MySQL uses \n, \r, \\. SQLite doesn't use backslash escapes in strings by default.
    // We might need to replace \\ with \ (literal backslash)
    $sql = str_replace('\\\\', '\\', $sql);
    
    try {
        $pdo->exec($sql);
    } catch (PDOException $e) {
        echo "Failed insert: " . $e->getMessage() . "\n";
    }
}

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

$path = __DIR__ . $uri;

// 1. Emulate security blocks from .htaccess
if (preg_match('#^/(systems|themes|tmp)/.*\.php$#', $uri)) {
    http_response_code(403);
    echo "Access Denied";
    exit;
}

if (preg_match('/\.(sdb|md|txt|env)$/i', $uri)) {
    http_response_code(403);
    echo "Access Denied";
    exit;
}

// 2. Handle /admin/ routes specific logic
if (strpos($uri, '/admin/') === 0 || $uri === '/admin') {
    // If it's a file that exists, serve it
    if (file_exists($path) && !is_dir($path)) {
        return false;
    }
    
    // If it's the admin root directory, serve admin/index.php
    if ($uri === '/admin' || $uri === '/admin/') {
        $_SERVER['SCRIPT_NAME'] = '/admin/index.php';
        chdir(__DIR__ . '/admin');
        require 'index.php';
        return;
    }

    // If file doesn't exist, rewrite to admin/index.php (Front Controller pattern for admin)
    $_SERVER['SCRIPT_NAME'] = '/admin/index.php';
    chdir(__DIR__ . '/admin');
    require 'index.php';
    return;
}

// 3. Handle root routes
// If it's a file that exists, serve it
if ($uri !== '/' && file_exists($path) && !is_dir($path)) {
    return false;
}

// 4. Fallback to root index.php for everything else
$_SERVER['SCRIPT_NAME'] = '/index.php';
require_once __DIR__ . '/index.php';
