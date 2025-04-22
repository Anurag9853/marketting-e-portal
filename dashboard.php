<?php
// Set headers
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Include database and user class files
require_once '../../config/database.php';
require_once '../../includes/user.php';

// Start session
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Get database connection
$database = new Database();
$conn = $database->getConnection();

// Create user object
$user = new User($conn);
$user->id = $_SESSION['user_id'];

// Get user details
if (!$user->getUserById() || $user->role !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

try {
    // Get total users
    $stmt = $conn->query("SELECT COUNT(*) as total_users FROM users");
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

    // Get active campaigns
    $stmt = $conn->query("SELECT COUNT(*) as active_campaigns FROM campaigns WHERE status = 'active'");
    $activeCampaigns = $stmt->fetch(PDO::FETCH_ASSOC)['active_campaigns'];

    // Get total messages
    $stmt = $conn->query("SELECT COUNT(*) as total_messages FROM contact_messages");
    $totalMessages = $stmt->fetch(PDO::FETCH_ASSOC)['total_messages'];

    echo json_encode([
        'success' => true,
        'stats' => [
            'total_users' => $totalUsers,
            'active_campaigns' => $activeCampaigns,
            'total_messages' => $totalMessages
        ]
    ]);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?> 