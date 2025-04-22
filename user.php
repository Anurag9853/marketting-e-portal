<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $name;
    public $email;
    public $password;
    public $role;
    public $company_name;
    public $phone;
    public $created;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getUserById() {
        $query = "SELECT id, name, email, role, company_name, phone FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->name = $row['name'];
            $this->email = $row['email'];
            $this->role = $row['role'];
            $this->company_name = $row['company_name'];
            $this->phone = $row['phone'];
            return true;
        }
        return false;
    }

    public function login($password) {
        $query = "SELECT id, name, email, password, role, company_name, phone FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password'])) {
                $this->id = $row['id'];
                $this->name = $row['name'];
                $this->email = $row['email'];
                $this->role = $row['role'];
                $this->company_name = $row['company_name'];
                $this->phone = $row['phone'];
                return true;
            }
        }
        return false;
    }

    public function register() {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                SET name=:name, 
                    email=:email, 
                    password=:password, 
                    role=:role,
                    company_name=:company_name,
                    phone=:phone,
                    created=:created";
            
            $stmt = $this->conn->prepare($query);

            // Sanitize input
            $this->name = htmlspecialchars(strip_tags($this->name));
            $this->email = htmlspecialchars(strip_tags($this->email));
            $this->password = password_hash($this->password, PASSWORD_BCRYPT);
            $this->role = htmlspecialchars(strip_tags($this->role));
            $this->company_name = htmlspecialchars(strip_tags($this->company_name));
            $this->phone = htmlspecialchars(strip_tags($this->phone));
            $this->created = date('Y-m-d H:i:s');

            // Log the values being inserted
            error_log("Attempting to register user with values:");
            error_log("Name: " . $this->name);
            error_log("Email: " . $this->email);
            error_log("Role: " . $this->role);
            error_log("Company: " . $this->company_name);
            error_log("Phone: " . $this->phone);

            // Bind parameters
            $stmt->bindParam(":name", $this->name);
            $stmt->bindParam(":email", $this->email);
            $stmt->bindParam(":password", $this->password);
            $stmt->bindParam(":role", $this->role);
            $stmt->bindParam(":company_name", $this->company_name);
            $stmt->bindParam(":phone", $this->phone);
            $stmt->bindParam(":created", $this->created);

            // Execute the query
            if ($stmt->execute()) {
                error_log("User registered successfully");
                return true;
            } else {
                $error = $stmt->errorInfo();
                error_log("Registration failed: " . print_r($error, true));
                return false;
            }
        } catch (PDOException $e) {
            error_log("PDO Exception during registration: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        } catch (Exception $e) {
            error_log("General Exception during registration: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    public function emailExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function getCampaigns() {
        $query = "SELECT * FROM campaigns WHERE created_by = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        return $stmt;
    }

    public function getAnalytics($campaign_id) {
        $query = "SELECT * FROM campaign_analytics WHERE campaign_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $campaign_id);
        $stmt->execute();
        return $stmt;
    }
}
?> 