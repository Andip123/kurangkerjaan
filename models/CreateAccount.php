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

    // Method untuk membuat akun
    public function create() {
        $query = "INSERT INTO " . $this->table . " (nama, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $this->conn->error);
        }
    
        // Hash password sebelum menyimpannya ke database
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
    
        $stmt->bind_param("ssss", $this->nama, $this->email, $this->password, $this->role);
    
        if ($stmt->execute()) {
            error_log("Akun berhasil dibuat untuk: " . $this->email);
            return true;
        } else {
            error_log("Error saat membuat akun: " . $stmt->error);
            return false;
        }
    }
    

    // Method untuk mengecek apakah email sudah ada
    public function emailExists() {
        $query = "SELECT email FROM " . $this->table . " WHERE LOWER(email) = LOWER(?)";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $this->conn->error);
        }
        $stmt->bind_param("s", $this->email);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    // Method untuk mendapatkan semua akun
    public function getAll() {
        $query = "SELECT id, nama, email, role FROM " . $this->table;
        $result = $this->conn->query($query);
        if (!$result) {
            throw new Exception("Query failed: " . $this->conn->error);
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Method untuk mendapatkan akun berdasarkan ID
    public function getById($id) {
        $query = "SELECT id, nama, email, role FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $this->conn->error);
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
?>
