<?php
header("Content-Type: application/json");
require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Cek request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Handle GET request
        $sql = "SELECT * FROM surat_masuk";
        $result = $conn->query($sql);

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
        break;

    case 'POST':
        // Handle POST request (menggunakan JSON)
        $input = json_decode(file_get_contents('php://input'), true);

        // Cek apakah data ada
        if (!isset($input['penerima_id'], $input['kode_surat'], $input['tanggal_masuk'], $input['asal_surat'], $input['jenis_surat'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Data tidak lengkap"
            ]);
            exit;
        }

        $penerima_id = $input['penerima_id'];
        $kode_surat = $input['kode_surat'];
        $tanggal_masuk = $input['tanggal_masuk'];
        $asal_surat = $input['asal_surat'];
        $jenis_surat = $input['jenis_surat'];

        // Cek apakah penerima_id ada di tabel pegawai
        $sql_check = "SELECT id FROM pegawai WHERE id = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("i", $penerima_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows == 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Penerima ID tidak ditemukan di tabel pegawai"
            ]);
            exit;
        }

        // Prepared statement untuk menghindari SQL Injection
        $stmt = $conn->prepare("INSERT INTO surat_masuk (penerima_id, kode_surat, tanggal_masuk, asal_surat, jenis_surat) 
                VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $penerima_id, $kode_surat, $tanggal_masuk, $asal_surat, $jenis_surat);

        if ($stmt->execute()) {
            echo json_encode([
                "status" => "success",
                "message" => "Data berhasil ditambahkan"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Gagal menambahkan data: " . $conn->error
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
