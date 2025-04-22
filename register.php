<?php
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../includes/user.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception("Database connection failed");
    }

    $user = new User($db);

    $data = json_decode(file_get_contents("php://input"));

    // Log the received data for debugging
    error_log("Registration attempt with data: " . print_r($data, true));

    if (
        !empty($data->name) &&
        !empty($data->email) &&
        !empty($data->password) &&
        !empty($data->role) &&
        !empty($data->company_name) &&
        !empty($data->phone)
    ) {
        $user->name = $data->name;
        $user->email = $data->email;
        $user->password = $data->password;
        $user->role = $data->role;
        $user->company_name = $data->company_name;
        $user->phone = $data->phone;

        // Validate email format
        if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(array(
                "success" => false,
                "message" => "Invalid email format"
            ));
            exit;
        }

        // Validate role
        $validRoles = ['admin', 'marketer', 'client'];
        if (!in_array($user->role, $validRoles)) {
            http_response_code(400);
            echo json_encode(array(
                "success" => false,
                "message" => "Invalid role selected"
            ));
            exit;
        }

        // Check if email exists
        if ($user->emailExists()) {
            http_response_code(400);
            echo json_encode(array(
                "success" => false,
                "message" => "Email already exists"
            ));
            exit;
        }

        // Attempt registration
        if ($user->register()) {
            http_response_code(201);
            echo json_encode(array(
                "success" => true,
                "message" => "User was registered successfully"
            ));
        } else {
            error_log("Registration failed in register.php");
            http_response_code(503);
            echo json_encode(array(
                "success" => false,
                "message" => "Unable to register the user. Please try again."
            ));
        }
    } else {
        $missing_fields = array();
        if (empty($data->name)) $missing_fields[] = "name";
        if (empty($data->email)) $missing_fields[] = "email";
        if (empty($data->password)) $missing_fields[] = "password";
        if (empty($data->role)) $missing_fields[] = "role";
        if (empty($data->company_name)) $missing_fields[] = "company_name";
        if (empty($data->phone)) $missing_fields[] = "phone";

        http_response_code(400);
        echo json_encode(array(
            "success" => false,
            "message" => "Missing required fields: " . implode(", ", $missing_fields)
        ));
    }
} catch (Exception $e) {
    error_log("Exception in register.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => "An unexpected error occurred during registration"
    ));
}
?> 