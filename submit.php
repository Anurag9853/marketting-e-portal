<?php
// Disable error display to prevent HTML output
ini_set('display_errors', 0);
error_reporting(0);

// Set headers for JSON response
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once '../../config/database.php';

try {
    // Get database connection
    $database = new Database();
    $conn = $database->getConnection();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Get POST data
    $rawInput = isset($GLOBALS['__PHP_INPUT_OVERRIDE']) 
        ? stream_get_contents($GLOBALS['__PHP_INPUT_OVERRIDE'])
        : file_get_contents("php://input");
        
    $data = json_decode($rawInput, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON data received: " . json_last_error_msg());
    }

    // Validate required fields
    if (empty($data['name']) || empty($data['phone']) || empty($data['message'])) {
        throw new Exception("All fields are required");
    }

    // Sanitize input
    $name = filter_var($data['name'], FILTER_SANITIZE_STRING);
    $phone = filter_var($data['phone'], FILTER_SANITIZE_STRING);
    $message = filter_var($data['message'], FILTER_SANITIZE_STRING);

    // Insert into database
    $query = "INSERT INTO contact_messages (name, phone, message) VALUES (:name, :phone, :message)";
    $stmt = $conn->prepare($query);
    
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':message', $message);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Message sent successfully'
        ]);
    } else {
        throw new Exception("Failed to save message");
    }
} catch (Exception $e) {
    error_log("Contact form error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 