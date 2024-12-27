<?php
class User {
    private $conn;
    private $table = "create_account";

    public $email;
    public $password;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login() {
        $query = "SELECT email, password, role FROM " . $this->table . " WHERE LOWER(TRIM(email)) = LOWER(TRIM(?))";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            error_log("Prepare statement failed: " . $this->conn->error);
            return false;
        }

        $stmt->bind_param("s", $this->email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Debugging
            error_log("Email ditemukan: " . $row['email']);
            error_log("Password hash dari database: " . $row['password']);

            if (password_verify($this->password, $row['password'])) {
                error_log("Password cocok.");
                return [
                    "email" => $row['email'],
                    "role" => $row['role']
                ];
            } else {
                error_log("Password tidak cocok.");
                return false;
            }
        } else {
            error_log("Email tidak ditemukan: " . $this->email);
            return false;
        }
    }
}
?>
