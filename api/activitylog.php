<?php
header("Content-Type: application/json");
require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Cek request method
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Jika ada parameter ID user, tampilkan log berdasarkan user_id
    if (isset($_GET['user_id'])) {
        $user_id = intval($_GET['user_id']);
        $sql = "SELECT * FROM activity_logs WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        // Jika tidak ada parameter, tampilkan semua log
        $sql = "SELECT * FROM activity_logs";
        $result = $conn->query($sql);
    }

    $data = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    echo json_encode([
        "status" => "success",
        "data" => $data
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Metode tidak didukung"
    ]);
}

$conn->close();
?>
