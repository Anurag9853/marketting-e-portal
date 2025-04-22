<?php
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

session_start();
require_once '../../config/database.php';
require_once '../../includes/user.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

try {
    $user = new User($conn);
    $user->id = $_SESSION['user_id'];
    
    if (!$user->getUserById() || $user->role !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Not authorized']);
        exit;
    }

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // Get list of campaigns with user info
            $stmt = $conn->prepare("
                SELECT c.*, u.name as user_name, u.email as user_email 
                FROM campaigns c 
                LEFT JOIN users u ON c.user_id = u.id 
                ORDER BY c.created_at DESC
            ");
            $stmt->execute();
            $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get analytics for each campaign
            foreach ($campaigns as &$campaign) {
                $stmt = $conn->prepare("
                    SELECT * FROM campaign_analytics 
                    WHERE campaign_id = :campaign_id 
                    ORDER BY date DESC LIMIT 1
                ");
                $stmt->execute([':campaign_id' => $campaign['id']]);
                $analytics = $stmt->fetch(PDO::FETCH_ASSOC);
                $campaign['analytics'] = $analytics ?: null;
            }

            echo json_encode([
                'success' => true,
                'campaigns' => $campaigns
            ]);
            break;

        case 'POST':
            // Create new campaign
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['name']) || !isset($data['description']) || !isset($data['userId'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                exit;
            }

            $stmt = $conn->prepare("
                INSERT INTO campaigns (name, description, user_id, status) 
                VALUES (:name, :description, :user_id, 'active')
            ");
            $stmt->execute([
                ':name' => $data['name'],
                ':description' => $data['description'],
                ':user_id' => $data['userId']
            ]);

            echo json_encode(['success' => true, 'id' => $conn->lastInsertId()]);
            break;

        case 'PUT':
            // Update campaign status
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['campaignId']) || !isset($data['status']) || 
                !in_array($data['status'], ['active', 'paused', 'completed'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid request']);
                exit;
            }

            $stmt = $conn->prepare("UPDATE campaigns SET status = :status WHERE id = :id");
            $stmt->execute([
                ':status' => $data['status'],
                ':id' => $data['campaignId']
            ]);

            echo json_encode(['success' => true]);
            break;

        case 'DELETE':
            // Delete campaign
            $campaignId = $_GET['id'] ?? null;
            if (!$campaignId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Campaign ID required']);
                exit;
            }

            // First delete related analytics
            $stmt = $conn->prepare("DELETE FROM campaign_analytics WHERE campaign_id = :id");
            $stmt->execute([':id' => $campaignId]);

            // Then delete the campaign
            $stmt = $conn->prepare("DELETE FROM campaigns WHERE id = :id");
            $stmt->execute([':id' => $campaignId]);

            echo json_encode(['success' => true]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }

} catch (Exception $e) {
    error_log("Error in admin campaigns endpoint: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
} 