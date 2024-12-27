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
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $data = $kategori->getById($id);

            if ($data) {
                echo json_encode([
                    "status" => "success",
                    "data" => $data
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Data tidak ditemukan"
                ]);
            }
        } else {
            $data = $kategori->getAll();
            echo json_encode([
                "status" => "success",
                "data" => $data
            ]);
        }
        break;

    case 'POST':
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

        $createdData = $kategori->create();

        if ($createdData) {
            echo json_encode([
                "status" => "success",
                "message" => "Data berhasil ditambahkan",
                "data" => $kategori->getById($conn->insert_id) // Data baru
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Gagal menambahkan data"
            ]);
        }
        break;

    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['id'], $input['nama'], $input['deskripsi'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Data tidak lengkap"
            ]);
            exit;
        }

        $id = intval($input['id']);
        $beforeUpdate = $kategori->getById($id);

        if (!$beforeUpdate) {
            echo json_encode([
                "status" => "error",
                "message" => "Data dengan ID $id tidak ditemukan"
            ]);
            exit;
        }

        $kategori->nama = $input['nama'];
        $kategori->deskripsi = $input['deskripsi'];

        if ($kategori->update($id)) {
            $afterUpdate = $kategori->getById($id);
            echo json_encode([
                "status" => "success",
                "message" => "Data berhasil diperbarui",
                "before_update" => $beforeUpdate,
                "after_update" => $afterUpdate
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Gagal memperbarui data"
            ]);
        }
        break;

    case 'DELETE':
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

        $dataBeforeDelete = $kategori->getById($id);

        if ($dataBeforeDelete && $kategori->delete($id)) {
            echo json_encode([
                "status" => "success",
                "message" => "Data berhasil dihapus",
                "deleted_data" => $dataBeforeDelete
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
