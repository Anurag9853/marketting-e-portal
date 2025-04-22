<?php
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

session_start();

include_once '../../config/database.php';
include_once '../../includes/user.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

if (isset($_SESSION['user_id'])) {
    $user->id = $_SESSION['user_id'];
    
    if ($user->getUserById()) {
        http_response_code(200);
        echo json_encode(array(
            "authenticated" => true,
            "user" => array(
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "role" => $user->role,
                "company_name" => $user->company_name,
                "phone" => $user->phone
            )
        ));
    } else {
        http_response_code(401);
        echo json_encode(array(
            "authenticated" => false,
            "message" => "User not found"
        ));
    }
} else {
    http_response_code(401);
    echo json_encode(array(
        "authenticated" => false,
        "message" => "Not authenticated"
    ));
}
?> 