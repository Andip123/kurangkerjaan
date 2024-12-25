<?php
header("Content-Type: application/json");
require_once '../config/database.php';
require_once '../models/SuratSkTugas.php';
require_once '../models/ActivityLog.php';

$database = new Database();
$conn = $database->getConnection();
$activityLog = new ActivityLog($conn);

$suratSkTugas = new SuratSkTugas($conn);

// Cek request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Handle GET request
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $data = $suratSkTugas->getById($id);

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
            $data = $suratSkTugas->getAll();
            echo json_encode([
                "status" => "success",
                "data" => $data
            ]);
        }
        break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
        
            if (!isset($input['tanggal_sk'], $input['nrp_pegawai'], $input['deskripsi'], $input['softfile'])) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Data tidak lengkap"
                ]);
                exit;
            }
        
            $suratSkTugas->tanggal_sk = $input['tanggal_sk'];
            $suratSkTugas->nrp_pegawai = $input['nrp_pegawai'];
            $suratSkTugas->deskripsi = $input['deskripsi'];
            $suratSkTugas->softfile = $input['softfile'];
        
            $result = $suratSkTugas->create();
        
            if (isset($result['error'])) {
                echo json_encode([
                    "status" => "error",
                    "message" => $result['error']
                ]);
            } elseif ($result) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Data berhasil ditambahkan",
                    "data" => $result
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
        
            if (!isset($input['id'], $input['tanggal_sk'], $input['nrp_pegawai'], $input['deskripsi'], $input['softfile'])) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Data tidak lengkap"
                ]);
                exit;
            }
        
            $id = intval($input['id']);
            $suratSkTugas->tanggal_sk = $input['tanggal_sk'];
            $suratSkTugas->nrp_pegawai = $input['nrp_pegawai'];
            $suratSkTugas->deskripsi = $input['deskripsi'];
            $suratSkTugas->softfile = $input['softfile'];
        
            $result = $suratSkTugas->update($id);
        
            if (isset($result['error'])) {
                echo json_encode([
                    "status" => "error",
                    "message" => $result['error']
                ]);
            } elseif ($result) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Data berhasil diperbarui",
                    "before_update" => $result['before_update'], // Menampilkan hanya satu data sebelum update
                    "after_update" => $result['after_update']   // Menampilkan data yang baru diupdate
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
        if (!isset($_GET['id'])) {
            echo json_encode([
                "status" => "error",
                "message" => "ID tidak diberikan"
            ]);
            exit;
        }

        $id = intval($_GET['id']);
        $deletedData = $suratSkTugas->delete($id);

        if ($deletedData) {
            $activityLog->log(1, "DELETE", "Menghapus surat SK tugas dengan ID: $id");
            echo json_encode([
                "status" => "success",
                "message" => "Data berhasil dihapus",
                "deleted_data" => $deletedData
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
