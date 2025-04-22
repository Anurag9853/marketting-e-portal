<?php
header('Content-Type: text/plain');

require_once 'config/database.php';

try {
    // Initialize database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        throw new Exception("Failed to connect to database");
    }
    
    echo "Database connection successful\n";

    // Check if table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'contact_messages'");
    
    if ($tableCheck->rowCount() == 0) {
        echo "Contact messages table does not exist. Creating it now...\n";
        
        // Create the table
        $sql = "CREATE TABLE contact_messages (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $conn->exec($sql);
        echo "Contact messages table created successfully!\n";
    } else {
        echo "Contact messages table already exists.\n";
    }
    
    // Test inserting a sample message
    $stmt = $conn->prepare("INSERT INTO contact_messages (name, phone, message) VALUES (?, ?, ?)");
    $result = $stmt->execute(['Test User', '1234567890', 'Test message']);
    
    if ($result) {
        echo "Test message inserted successfully!\n";
        // Clean up test data
        $conn->exec("DELETE FROM contact_messages WHERE name = 'Test User'");
        echo "Test data cleaned up.\n";
    } else {
        echo "Failed to insert test message.\n";
        print_r($stmt->errorInfo());
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 