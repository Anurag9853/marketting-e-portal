<?php
class ActivityLogger {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
        
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
        $this->conn->exec($query);
    }
    
    public function log($userId, $userEmail, $action, $details) {
        try {
            $query = "INSERT INTO activity_log (user_id, user_email, action, details) VALUES (?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$userId, $userEmail, $action, $details]);
            return true;
        } catch (Exception $e) {
            error_log("Error logging activity: " . $e->getMessage());
            return false;
        }
    }
}
?> 