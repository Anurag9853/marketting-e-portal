<?php
header("Content-Type: text/plain");

include_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Database connection failed");
    }
    
    echo "Database connection successful!\n\n";
    
    // Check if database exists
    $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'project2'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo "Database 'project2' exists\n";
    } else {
        echo "Database 'project2' does not exist\n";
        exit;
    }
    
    // Check if users table exists
    $query = "SHOW TABLES LIKE 'users'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo "Table 'users' exists\n";
        
        // Show table structure
        $query = "DESCRIBE users";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        echo "\nTable structure:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "Field: " . $row['Field'] . "\n";
            echo "Type: " . $row['Type'] . "\n";
            echo "Null: " . $row['Null'] . "\n";
            echo "Key: " . $row['Key'] . "\n";
            echo "Default: " . $row['Default'] . "\n";
            echo "Extra: " . $row['Extra'] . "\n\n";
        }
    } else {
        echo "Table 'users' does not exist\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?> 