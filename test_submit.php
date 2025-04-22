<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';

// Sample test data
$test_data = [
    'name' => 'Test User',
    'phone' => '+919876543210',
    'message' => 'This is a test message from the test script.'
];

try {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Prepare the insert statement
    $query = "INSERT INTO contact_messages (name, phone, message) VALUES (:name, :phone, :message)";
    $stmt = $db->prepare($query);
    
    // Bind parameters
    $stmt->bindParam(':name', $test_data['name']);
    $stmt->bindParam(':phone', $test_data['phone']);
    $stmt->bindParam(':message', $test_data['message']);
    
    // Execute the query
    if ($stmt->execute()) {
        // Get the inserted ID
        $last_id = $db->lastInsertId();
        
        // Verify the insertion by retrieving the record
        $verify_query = "SELECT * FROM contact_messages WHERE id = :id";
        $verify_stmt = $db->prepare($verify_query);
        $verify_stmt->bindParam(':id', $last_id);
        $verify_stmt->execute();
        
        $result = $verify_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Clean up test data
        $cleanup_query = "DELETE FROM contact_messages WHERE id = :id";
        $cleanup_stmt = $db->prepare($cleanup_query);
        $cleanup_stmt->bindParam(':id', $last_id);
        $cleanup_stmt->execute();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Test submission successful',
            'test_data' => $test_data,
            'inserted_record' => $result
        ]);
    } else {
        throw new Exception("Failed to insert test data");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Test failed: ' . $e->getMessage()
    ]);
}
?> 