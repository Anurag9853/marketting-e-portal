<?php
// Set headers
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and user class files
require_once '../../config/database.php';
require_once '../../includes/user.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

try {
    // Update the role for your email
    $query = "UPDATE users SET role = 'admin' WHERE email = 'anuragsingh98352@gmail.com'";
    $stmt = $db->prepare($query);
    
    if($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Role updated to admin successfully"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Failed to update role"
        ]);
    }
} catch(Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
?> 