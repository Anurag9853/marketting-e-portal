<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';

try {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if contact_messages table exists
    $check_table = $db->query("SHOW TABLES LIKE 'contact_messages'");
    if ($check_table->rowCount() == 0) {
        // Create the table if it doesn't exist
        $create_table = "CREATE TABLE contact_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $db->exec($create_table);
        echo json_encode(['status' => 'success', 'message' => 'Table created successfully']);
    } else {
        // Get table structure
        $describe = $db->query("DESCRIBE contact_messages");
        $columns = $describe->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'message' => 'Table exists', 'structure' => $columns]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 