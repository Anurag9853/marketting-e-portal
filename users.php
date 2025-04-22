<?php
// Set headers
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type");
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
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

try {
    // Handle different HTTP methods
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // Get list of users
            $stmt = $db->prepare("SELECT id, name, email, role, company_name, phone, created_at FROM users ORDER BY created_at DESC");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'users' => $users
            ]);
            break;

        case 'POST':
            // Update user role
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['userId']) || !isset($data['newRole']) || 
                !in_array($data['newRole'], ['user', 'admin'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid request']);
                exit();
            }

            $stmt = $db->prepare("UPDATE users SET role = :role WHERE id = :id");
            $stmt->execute([
                ':role' => $data['newRole'],
                ':id' => $data['userId']
            ]);

            echo json_encode(['success' => true]);
            break;

        case 'DELETE':
            // Delete user
            $userId = $_GET['id'] ?? null;
            if (!$userId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'User ID required']);
                exit();
            }

            // Prevent admin from deleting themselves
            if ($userId == $_SESSION['user_id']) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Cannot delete your own account']);
                exit();
            }

            $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute([':id' => $userId]);

            echo json_encode(['success' => true]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    error_log("Error in admin users endpoint: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?> 