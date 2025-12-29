<?php
session_start();

// Redirect ke login jika belum login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}

require_once 'database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Nama file
    $filename = 'inventaris_backup_' . date('Y-m-d_H-i-s') . '.sql';
    
    // Set headers untuk download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Database name
    $dbName = DB_NAME;
    
    // Output SQL header
    echo "-- Database Backup: $dbName\n";
    echo "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    echo "-- --------------------------------------------------\n\n";
    echo "SET FOREIGN_KEY_CHECKS=0;\n\n";
    
    // Get all tables
    $tables = array();
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    
    // Loop through tables
    foreach ($tables as $table) {
        // Drop table
        echo "-- Table: $table\n";
        echo "DROP TABLE IF EXISTS `$table`;\n\n";
        
        // Create table
        $createTable = $conn->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
        echo $createTable['Create Table'] . ";\n\n";
        
        // Insert data
        $rows = $conn->query("SELECT * FROM `$table`");
        if ($rows->rowCount() > 0) {
            echo "-- Data for table: $table\n";
            
            while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
                $columns = array_keys($row);
                $values = array_values($row);
                
                // Escape values
                $escapedValues = array();
                foreach ($values as $value) {
                    if ($value === null) {
                        $escapedValues[] = 'NULL';
                    } else {
                        $escapedValues[] = "'" . addslashes($value) . "'";
                    }
                }
                
                echo "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $escapedValues) . ");\n";
            }
            echo "\n";
        }
    }
    
    echo "SET FOREIGN_KEY_CHECKS=1;\n";
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
