<?php
session_start();
include_once "../config/auth.php";

checkAuth();
checkRole(['admin']);

function backupDatabase($host, $user, $pass, $dbname, $tables = '*') {
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Get all tables
    if($tables == '*') {
        $tables = array();
        $result = $conn->query("SHOW TABLES");
        while($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }
    } else {
        $tables = is_array($tables) ? $tables : explode(',',$tables);
    }
    
    $return = '';
    
    foreach($tables as $table) {
        $result = $conn->query("SELECT * FROM $table");
        $numColumns = $result->field_count;
        
        $return .= "DROP TABLE IF EXISTS $table;\n";
        
        $result2 = $conn->query("SHOW CREATE TABLE $table");
        $row2 = $result2->fetch_row();
        $return .= "\n" . $row2[1] . ";\n\n";
        
        for($i = 0; $i < $numColumns; $i++) {
            while($row = $result->fetch_row()) {
                $return .= "INSERT INTO $table VALUES(";
                for($j=0; $j < $numColumns; $j++) {
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = preg_replace("/\n/","\\n",$row[$j]);
                    if (isset($row[$j])) {
                        $return .= '"' . $row[$j] . '"';
                    } else {
                        $return .= '""';
                    }
                    if ($j < ($numColumns-1)) {
                        $return .= ',';
                    }
                }
                $return .= ");\n";
            }
        }
        $return .= "\n\n";
    }
    
    // Create backup directory if not exists
    $backupDir = __DIR__ . '/backups/';
    if (!file_exists($backupDir)) {
        mkdir($backupDir, 0777, true);
    }
    
    // Save file
    $filename = $backupDir . 'db-backup-' . date('Y-m-d-H-i-s') . '.sql';
    $handle = fopen($filename, 'w+');
    
    if ($handle === false) {
        die("Cannot create file: $filename. Check directory permissions.");
    }
    
    fwrite($handle, $return);
    fclose($handle);
    
    return basename($filename);
}

// Jalankan backup
try {
    $backupFile = backupDatabase('localhost', 'root', '', 'rumah_sakit');
    
    // Log aktivitas backup
    $logFile = __DIR__ . '/backup_log.txt';
    $logMessage = date('Y-m-d H:i:s') . " - Backup berhasil dibuat: " . $backupFile . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    
    $_SESSION['success_message'] = "Backup database berhasil dibuat! File: " . $backupFile;
    header("Location: ../index.php");
    exit();
} catch (Exception $e) {
    $_SESSION['error_message'] = "Gagal membuat backup: " . $e->getMessage();
    header("Location: ../index.php");
    exit();
}
?>