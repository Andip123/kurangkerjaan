<?php
class CreateAccount {
    private $conn;
    private $table = "create_account";

    public $id;
    public $nama;
    public $email;
    public $password;
    public $role;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new account
    public function create() {
        $query = "INSERT INTO " . $this->table . " (nama, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssss", $this->nama, $this->email, $this->password, $this->role);
        return $stmt->execute();
    }
    public function emailExists() {
        $query = "SELECT id FROM " . $this->table . " WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $this->email);
        $stmt->execute();
        $stmt->store_result();
    
        return $stmt->num_rows > 0;
    }    

    // Get all accounts
    public function getAll() {
        $query = "SELECT id, nama, email, role FROM " . $this->table;
        $result = $this->conn->query($query);
        return $result;
    }

    // Get account by ID
    public function getById($id) {
        $query = "SELECT id, nama, email, role FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt;
    }
}
?>
