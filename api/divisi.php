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
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $data = $divisi->getById($id);

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
            $data = $divisi->getAll();
            echo json_encode([
                "status" => "success",
                "data" => $data
            ]);
        }
        break;

    case 'POST':
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

        if ($divisi->isDuplicate($divisi->kode_divisi)) {
            echo json_encode([
                "status" => "error",
                "message" => "Kode divisi sudah ada, gunakan kode yang berbeda."
            ]);
            exit;
        }

        $createdId = $divisi->create();

        if ($createdId) {
            echo json_encode([
                "status" => "success",
                "message" => "Data berhasil ditambahkan",
                "data" => $divisi->getById($createdId)
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
        
            if (!isset($input['id'], $input['kode_divisi'], $input['nama_divisi'], $input['alamat_kantor'])) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Data tidak lengkap"
                ]);
                exit;
            }
        
            $id = intval($input['id']);
            
            // Periksa apakah ID ada di database
            $beforeUpdate = $divisi->getById($id);
        
            if (!$beforeUpdate) {
                echo json_encode([
                    "status" => "error",
                    "message" => "ID $id tidak ditemukan di database"
                ]);
                exit;
            }
        
            // Periksa apakah kode_divisi digunakan oleh ID lain
            if ($divisi->isKodeDivisiUsedByOther($input['kode_divisi'], $id)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Kode divisi sudah digunakan oleh ID lain, gunakan kode divisi yang berbeda"
                ]);
                exit;
            }
        
            // Proses update data
            $divisi->kode_divisi = $input['kode_divisi'];
            $divisi->nama_divisi = $input['nama_divisi'];
            $divisi->alamat_kantor = $input['alamat_kantor'];
        
            if ($divisi->update($id)) {
                $afterUpdate = $divisi->getById($id); // Data setelah update
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
        
            // Ambil data sebelum dihapus
            $dataBeforeDelete = $divisi->getById($id);
        
            if (!$dataBeforeDelete) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Data dengan ID $id tidak ditemukan"
                ]);
                exit;
            }
        
            // Hapus data jika tidak ada constraint yang melanggar
            if ($divisi->delete($id)) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Data berhasil dihapus",
                    "deleted_data" => $dataBeforeDelete
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Gagal menghapus data, pastikan tidak ada data terkait"
                ]);
            }
            break;
        
}

$conn->close();
?>
