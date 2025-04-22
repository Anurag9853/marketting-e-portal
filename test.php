<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once '../../config/database.php';

try {
    // Get database connection
    $database = new Database();
    $conn = $database->getConnection();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Check if table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'contact_messages'");
    if ($tableCheck->rowCount() == 0) {
        throw new Exception("Table 'contact_messages' does not exist");
    }

    // Check table structure
    $tableInfo = $conn->query("DESCRIBE contact_messages");
    $columns = $tableInfo->fetchAll(PDO::FETCH_COLUMN);

    // Test insert
    $testData = [
        'name' => 'Test User',
        'phone' => '1234567890',
        'message' => 'Test message'
    ];

    $query = "INSERT INTO contact_messages (name, phone, message) VALUES (:name, :phone, :message)";
    $stmt = $conn->prepare($query);
    
    $stmt->bindParam(':name', $testData['name']);
    $stmt->bindParam(':phone', $testData['phone']);
    $stmt->bindParam(':message', $testData['message']);

    if ($stmt->execute()) {
        $lastId = $conn->lastInsertId();
        // Clean up test data
        $conn->query("DELETE FROM contact_messages WHERE id = $lastId");
        
        echo json_encode([
            'success' => true,
            'message' => 'Database connection and table structure are working correctly',
            'columns' => $columns
        ]);
    } else {
        throw new Exception("Failed to insert test data");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 