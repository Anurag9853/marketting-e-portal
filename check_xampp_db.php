<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Try to connect to MySQL
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // List all databases
    $databases = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
    
    // Check if our database exists
    if (in_array('project_2', $databases)) {
        // Switch to our database
        $pdo->exec("USE project_2");
        
        // List all tables
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        // Check contact_messages table
        if (in_array('contact_messages', $tables)) {
            // Get table structure
            $structure = $pdo->query("DESCRIBE contact_messages")->fetchAll(PDO::FETCH_ASSOC);
            
            // Get all records
            $records = $pdo->query("SELECT * FROM contact_messages")->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Database and table found',
                'data' => [
                    'database' => 'project_2',
                    'tables' => $tables,
                    'table_structure' => $structure,
                    'records' => $records
                ]
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'contact_messages table not found',
                'data' => [
                    'database' => 'project_2',
                    'tables' => $tables
                ]
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database project_2 not found',
            'data' => [
                'available_databases' => $databases
            ]
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection error: ' . $e->getMessage()
    ]);
}
?> 