<?php
header("Content-Type: application/json");
require_once '../config/database.php';
require_once '../models/CreateAccount.php';
require_once '../models/ActivityLog.php';

$database = new Database();
$conn = $database->getConnection();
$activityLog = new ActivityLog($conn);

$createAccount = new CreateAccount($conn);

// Cek request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // Handle POST request (Create account)
        $input = json_decode(file_get_contents('php://input'), true);
    
        if (!isset($input['nama'], $input['email'], $input['role'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Data tidak lengkap"
            ]);
            exit;
        }
    
        // Set data untuk akun
        $createAccount->nama = $input['nama'];
        $createAccount->email = $input['email'];
        $createAccount->role = $input['role'];
    
        // Cek apakah email sudah ada
        if ($createAccount->emailExists()) {
            echo json_encode([
                "status" => "error",
                "message" => "Email sudah terdaftar"
            ]);
            exit;
        }
    
        // Generate password
        $default_password = "Password" . rand(100, 999); // Password random
        $createAccount->password = password_hash($default_password, PASSWORD_DEFAULT);
    
        // Simpan akun ke database
        if ($createAccount->create()) {
            // Kirim password ke email pegawai
            $to = $input['email'];
            $subject = "Akun Anda Telah Dibuat";
            $message = "Halo " . $input['nama'] . ",\n\n" .
                       "Akun Anda telah berhasil dibuat dengan rincian berikut:\n" .
                       "Email: " . $input['email'] . "\n" .
                       "Password: " . $default_password . "\n\n" .
                       "Silakan gunakan password ini untuk login, dan pastikan untuk mengubahnya setelah login pertama.\n\n" .
                       "Terima kasih.";
            $headers = "From: admin@yourdomain.com";
    
            if (mail($to, $subject, $message, $headers)) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Akun berhasil dibuat. Password telah dikirim ke email.",
                    "password" => $default_password // Untuk debugging (opsional, hapus di produksi)
                ]);
            } else {
                echo json_encode([
                    "status" => "success",
                    "message" => "Akun berhasil dibuat, tetapi gagal mengirim email. Hubungi admin untuk password.",
                    "password" => $default_password // Untuk debugging (opsional, hapus di produksi)
                ]);
            }
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Gagal membuat akun"
            ]);
        }
        break;
    

    default:
        echo json_encode([
            "status" => "error",
            "message" => "Metode tidak didukung"
        ]);
        break;
}

$conn->close();
?>
