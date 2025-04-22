<?php
// Set headers
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and user class files
require_once '../../config/database.php';
require_once '../../includes/user.php';

// Start session
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized access"
    ]);
    exit();
}

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Create user object
$user = new User($db);
$user->id = $_SESSION['user_id'];

// Get user details
if (!$user->getUserById() || $user->role !== 'admin') {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized access"
    ]);
    exit();
}

try {
    // Create activity_log table if it doesn't exist
    $query = "CREATE TABLE IF NOT EXISTS activity_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        user_email VARCHAR(100),
        action VARCHAR(50),
        details TEXT,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    $db->exec($query);

    // Get recent activity logs
    $query = "SELECT a.*, u.email as user_email 
              FROM activity_log a 
              LEFT JOIN users u ON a.user_id = u.id 
              ORDER BY a.timestamp DESC 
              LIMIT 50";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $activities = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $activities[] = $row;
    }
    
    echo json_encode([
        "success" => true,
        "activities" => $activities
    ]);
} catch(Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
?> 