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
require_once '../../includes/activity_logger.php';

// Start session
session_start();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Check if email and password are set
if (!isset($data->email) || !isset($data->password)) {
    echo json_encode([
        "success" => false,
        "message" => "Email and password are required"
    ]);
    exit();
}

try {
    // Create user object
    $user = new User($db);
    
    // Set email property
    $user->email = $data->email;
    
    // Create activity logger
    $logger = new ActivityLogger($db);
    
    // Check if user exists and verify password
    if ($user->login($data->password)) {
        // Set session variables
        $_SESSION['user_id'] = $user->id;
        
        // Log the login activity
        $logger->log($user->id, $user->email, 'login', 'User logged in successfully');
        
        // Return success response with user data
        echo json_encode([
            "success" => true,
            "message" => "Login successful",
            "user" => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "role" => $user->role,
                "company_name" => $user->company_name,
                "phone" => $user->phone
            ]
        ]);
    } else {
        // Log failed login attempt
        $logger->log(null, $data->email, 'login_failed', 'Invalid email or password');
        
        echo json_encode([
            "success" => false,
            "message" => "Invalid email or password"
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "An error occurred during login",
        "error" => $e->getMessage()
    ]);
}
?> 