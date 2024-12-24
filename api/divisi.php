<?php
header("Content-Type: application/json");
require_once '../config/database.php';
require_once '../models/Divisi.php';
require_once '../models/ActivityLog.php';

$database = new Database();
$conn = $database->getConnection();
$activityLog = new ActivityLog($conn);

$divisi = new Divisi($conn);

// Cek request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Handle GET request (Read data)
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $stmt = $divisi->getById($id);

            $stmt->bind_result($id, $kode_divisi, $nama_divisi, $alamat_kantor);

            $data = [];
            while ($stmt->fetch()) {
                $data = [
                    "id" => $id,
                    "kode_divisi" => $kode_divisi,
                    "nama_divisi" => $nama_divisi,
                    "alamat_kantor" => $alamat_kantor
                ];
            }

            echo json_encode([
                "status" => "success",
                "data" => $data
            ]);
        } else {
            $result = $divisi->getAll();

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

        if (!isset($input['kode_divisi'], $input['nama_divisi'], $input['alamat_kantor'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Data tidak lengkap"
            ]);
            exit;
        }

        $divisi->kode_divisi = $input['kode_divisi'];
        $divisi->nama_divisi = $input['nama_divisi'];
        $divisi->alamat_kantor = $input['alamat_kantor'];

        if ($divisi->create()) {
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

        if (!isset($input['id'], $input['kode_divisi'], $input['nama_divisi'], $input['alamat_kantor'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Data tidak lengkap"
            ]);
            exit;
        }

        $id = intval($input['id']);
        $divisi->kode_divisi = $input['kode_divisi'];
        $divisi->nama_divisi = $input['nama_divisi'];
        $divisi->alamat_kantor = $input['alamat_kantor'];

        if ($divisi->update($id)) {
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
            // Baca input dari body JSON atau query string
            $input = json_decode(file_get_contents('php://input'), true);
        
            if (isset($_GET['id'])) {
                // Ambil ID dari query string
                $id = intval($_GET['id']);
            } elseif (isset($input['id'])) {
                // Ambil ID dari body JSON
                $id = intval($input['id']);
            } else {
                // Jika ID tidak ditemukan
                echo json_encode([
                    "status" => "error",
                    "message" => "ID tidak diberikan"
                ]);
                exit;
            }
        
            // Proses penghapusan data
            if ($divisi->delete($id)) {
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
        
}

$conn->close();
?>
