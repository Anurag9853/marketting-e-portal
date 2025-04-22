<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../config/database.php';

try {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Get the latest message
    $query = "SELECT * FROM contact_messages ORDER BY id DESC LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Latest submission found',
            'data' => $result
        ]);
    } else {
        echo json_encode([
            'status' => 'success',
            'message' => 'No submissions found',
            'data' => null
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 