<?php
header("Content-Type: text/plain");

try {
    // Connect to MySQL without selecting a database
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to MySQL successfully\n";
    
    // Select the existing database
    $pdo->exec("USE project_2");
    echo "Selected database 'project_2'\n";
    
    // Drop tables in correct order to handle foreign key constraints
    $pdo->exec("DROP TABLE IF EXISTS campaign_analytics");
    echo "Dropped campaign_analytics table\n";
    
    $pdo->exec("DROP TABLE IF EXISTS campaigns");
    echo "Dropped campaigns table\n";
    
    $pdo->exec("DROP TABLE IF EXISTS users");
    echo "Dropped users table\n";
    
    // Create users table with correct structure
    $pdo->exec("CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'marketer', 'client') NOT NULL,
        company_name VARCHAR(100),
        phone VARCHAR(20),
        created DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    echo "Created users table with correct structure\n";
    
    // Create campaigns table
    $pdo->exec("CREATE TABLE campaigns (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        budget DECIMAL(10,2),
        start_date DATE,
        end_date DATE,
        status ENUM('planned', 'active', 'completed', 'cancelled') DEFAULT 'planned',
        created_by INT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    echo "Created campaigns table\n";
    
    // Create campaign_analytics table
    $pdo->exec("CREATE TABLE campaign_analytics (
        id INT AUTO_INCREMENT PRIMARY KEY,
        campaign_id INT,
        impressions INT DEFAULT 0,
        clicks INT DEFAULT 0,
        conversions INT DEFAULT 0,
        date DATE,
        FOREIGN KEY (campaign_id) REFERENCES campaigns(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    echo "Created campaign_analytics table\n";
    
    // Create contact_messages table
    $pdo->exec("CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        subject VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        created_at DATETIME NOT NULL,
        status ENUM('new', 'read', 'replied') DEFAULT 'new'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    echo "Created contact_messages table\n";
    
    // Insert a test admin user
    $password = password_hash('admin123', PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, company_name, phone) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Admin User', 'admin@example.com', $password, 'admin', 'Admin Company', '1234567890']);
    echo "Created test admin user\n";
    
    echo "\nDatabase setup completed successfully!\n";
    echo "You can now try registering a new user.\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?> 