<?php
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

session_start();

require_once '../../includes/database.php';
require_once '../../includes/user.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user = new User($pdo);
$user->setId($_SESSION['user_id']);
if (!$user->getUserById() || $user->getRole() !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

try {
    // Fetch 5 most recent campaigns with user details
    $query = "SELECT c.id, c.name, c.status, c.budget, c.start_date, c.end_date, 
                     u.name as user_name, u.email as user_email
              FROM campaigns c
              JOIN users u ON c.user_id = u.id
              ORDER BY c.created_at DESC
              LIMIT 5";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format dates and budget for each campaign
    foreach ($campaigns as &$campaign) {
        $campaign['start_date'] = date('M j, Y', strtotime($campaign['start_date']));
        $campaign['end_date'] = date('M j, Y', strtotime($campaign['end_date']));
        $campaign['budget'] = '$' . number_format($campaign['budget'], 2);
    }
    
    echo json_encode([
        'success' => true,
        'campaigns' => $campaigns
    ]);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
} 