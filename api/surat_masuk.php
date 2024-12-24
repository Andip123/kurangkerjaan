<?php
header("Content-Type: application/json");
require_once '../config/database.php';
require_once '../models/ActivityLog.php';

$database = new Database();
$conn = $database->getConnection();
$activityLog = new ActivityLog($conn);

// Cek request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Handle GET request (Read data)
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $sql = "SELECT sm.*, p.nama AS penerima_nama 
                    FROM surat_masuk sm 
                    JOIN pegawai p ON sm.penerima_id = p.id
                    WHERE sm.id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();

            echo json_encode([
                "status" => "success",
                "data" => $data
            ]);
        } else {
            $sql = "SELECT sm.*, p.nama AS penerima_nama 
                    FROM surat_masuk sm 
                    JOIN pegawai p ON sm.penerima_id = p.id";
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
        }
        break;

    case 'POST':
        // Handle POST request (Create data)
        $input = json_decode(file_get_contents('php://input'), true);

        // Cek apakah data lengkap
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

        // Cek penerima_id
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
                "message" => "Gagal menambahkan data: " . $stmt->error
            ]);
        }
        break;

    case 'PUT':
        // Handle PUT request (Update data)
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['id'], $input['penerima_id'], $input['kode_surat'], $input['tanggal_masuk'], $input['asal_surat'], $input['jenis_surat'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Data tidak lengkap"
            ]);
            exit;
        }

        $id = $input['id'];
        $penerima_id = $input['penerima_id'];
        $kode_surat = $input['kode_surat'];
        $tanggal_masuk = $input['tanggal_masuk'];
        $asal_surat = $input['asal_surat'];
        $jenis_surat = $input['jenis_surat'];

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

        $stmt = $conn->prepare("UPDATE surat_masuk SET penerima_id = ?, kode_surat = ?, tanggal_masuk = ?, asal_surat = ?, jenis_surat = ? 
                WHERE id = ?");
        $stmt->bind_param("issssi", $penerima_id, $kode_surat, $tanggal_masuk, $asal_surat, $jenis_surat, $id);

        if ($stmt->execute()) {
            echo json_encode([
                "status" => "success",
                "message" => "Data berhasil diperbarui"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Gagal memperbarui data: " . $stmt->error
            ]);
        }
        break;

        case 'DELETE':
            // Handle DELETE request
            if (!isset($_GET['id'])) {
                echo json_encode([
                    "status" => "error",
                    "message" => "ID tidak diberikan"
                ]);
                exit;
            }
        
            $id = intval($_GET['id']); // Ambil ID dari URL
            $stmt = $conn->prepare("DELETE FROM surat_masuk WHERE id = ?");
            $stmt->bind_param("i", $id);
        
            if ($stmt->execute()) {
                $activityLog->log(1, "DELETE", "Menghapus surat masuk dengan ID: $id"); // Logging
                echo json_encode([
                    "status" => "success",
                    "message" => "Data berhasil dihapus"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Gagal menghapus data: " . $stmt->error
                ]);
            }
            break;
        
}

$conn->close();
?>
