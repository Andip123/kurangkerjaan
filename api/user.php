<?php
header("Content-Type: application/json");
require_once '../config/database.php';
require_once '../models/User.php';

$database = new Database();
$conn = $database->getConnection();

$user = new User($conn);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['email'], $input['password'])) {
            http_response_code(400); // Bad Request
            echo json_encode([
                "status" => "error",
                "message" => "Email dan password diperlukan"
            ]);
            exit;
        }

        $user->email = $input['email'];
        $user->password = $input['password'];

        $isAuthenticated = $user->login();

        if ($isAuthenticated) {
            http_response_code(200); // OK
            echo json_encode([
                "status" => "success",
                "message" => "Login berhasil",
                "data" => $isAuthenticated
            ]);
        } else {
            http_response_code(404); // Not Found
            echo json_encode([
                "status" => "error",
                "message" => "Email atau password salah"
            ]);
        }
        break;

    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode([
            "status" => "error",
            "message" => "Metode tidak didukung"
        ]);
        break;
}

$conn->close();
?>
