<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../config/database.php';
require_once '../models/CreateAccount.php';
require_once '../models/ActivityLog.php';

$database = new Database();
$conn = $database->getConnection();
$activityLog = new ActivityLog($conn);
$createAccount = new CreateAccount($conn);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['nama'], $input['email'], $input['role'])) {
            http_response_code(400); // Bad Request
            echo json_encode([
                "status" => "error",
                "message" => "Data tidak lengkap"
            ]);
            exit;
        }

        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "status" => "error",
                "message" => "Format email tidak valid"
            ]);
            exit;
        }

        $createAccount->nama = $input['nama'];
        $createAccount->email = $input['email'];
        $createAccount->role = $input['role'];

        if ($createAccount->emailExists()) {
            echo json_encode([
                "status" => "error",
                "message" => "Email sudah terdaftar"
            ]);
            exit;
        }

        $default_password = "Password" . rand(100, 999);
        $createAccount->password = password_hash($default_password, PASSWORD_DEFAULT);

        if ($createAccount->create()) {
            echo json_encode([
                "status" => "success",
                "message" => "Akun berhasil dibuat. Password telah dikirim ke email.",
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Gagal membuat akun"
            ]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode([
            "status" => "error",
            "message" => "Metode tidak didukung"
        ]);
        break;
}

$conn->close();
?>
