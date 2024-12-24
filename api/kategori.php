<?php
header("Content-Type: application/json");
require_once '../config/database.php';
require_once '../models/Kategori.php';
require_once '../models/ActivityLog.php';

$database = new Database();
$conn = $database->getConnection();
$activityLog = new ActivityLog($conn);

$kategori = new Kategori($conn);

// Cek request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Handle GET request (Read data)
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $stmt = $kategori->getById($id);

            $stmt->bind_result($id, $nama, $deskripsi);

            $data = [];
            while ($stmt->fetch()) {
                $data = [
                    "id" => $id,
                    "nama" => $nama,
                    "deskripsi" => $deskripsi
                ];
            }

            echo json_encode([
                "status" => "success",
                "data" => $data
            ]);
        } else {
            $result = $kategori->getAll();

            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
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

        if (!isset($input['nama'], $input['deskripsi'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Data tidak lengkap"
            ]);
            exit;
        }

        $kategori->nama = $input['nama'];
        $kategori->deskripsi = $input['deskripsi'];

        if ($kategori->create()) {
            echo json_encode([
                "status" => "success",
                "message" => "Data berhasil ditambahkan"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Gagal menambahkan data"
            ]);
        }
        break;

    case 'PUT':
        // Handle PUT request (Update data)
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['id'], $input['nama'], $input['deskripsi'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Data tidak lengkap"
            ]);
            exit;
        }

        $id = intval($input['id']);
        $kategori->nama = $input['nama'];
        $kategori->deskripsi = $input['deskripsi'];

        if ($kategori->update($id)) {
            echo json_encode([
                "status" => "success",
                "message" => "Data berhasil diperbarui"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Gagal memperbarui data"
            ]);
        }
        break;

    case 'DELETE':
        // Handle DELETE request
        $input = json_decode(file_get_contents('php://input'), true);

        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
        } elseif (isset($input['id'])) {
            $id = intval($input['id']);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "ID tidak diberikan"
            ]);
            exit;
        }

        if ($kategori->delete($id)) {
            echo json_encode([
                "status" => "success",
                "message" => "Data berhasil dihapus"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Gagal menghapus data"
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
