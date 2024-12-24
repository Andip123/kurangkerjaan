<?php
class ActivityLog {
    private $conn;
    private $table_name = "activity_logs";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function log($user_id, $action, $description) {
        $query = "INSERT INTO " . $this->table_name . " (user_id, action, description) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iss", $user_id, $action, $description);
        return $stmt->execute();
    }
}
?>
