<?php
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

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
    // Get recent users (last 5)
    $stmt = $pdo->query('SELECT id, name, email, role, company_name, created_at 
                         FROM users 
                         ORDER BY created_at DESC 
                         LIMIT 5');
    $recentUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format dates for each user
    foreach ($recentUsers as &$user) {
        $user['created_at'] = date('M d, Y H:i', strtotime($user['created_at']));
    }

    echo json_encode([
        'success' => true,
        'users' => $recentUsers
    ]);
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
} 