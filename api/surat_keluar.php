<?php
header("Content-Type: application/json");
require_once '../config/database.php';
require_once '../models/SuratKeluar.php';
require_once '../models/ActivityLog.php';

$database = new Database();
$conn = $database->getConnection();

$suratKeluar = new SuratKeluar($conn);
$activityLog = new ActivityLog($conn);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Handle GET request
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $data = $suratKeluar->getById($id);

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
            $data = $suratKeluar->getAll();
            echo json_encode([
                "status" => "success",
                "data" => $data
            ]);
        }
        break;

    case 'POST':
        // Handle POST request
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['tanggal_surat'], $input['nrp_pegawai'], $input['penerima'], $input['softfile'], $input['jenis_surat'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Data tidak lengkap"
            ]);
            exit;
        }

        $suratKeluar->tanggal_surat = $input['tanggal_surat'];
        $suratKeluar->nrp_pegawai = $input['nrp_pegawai'];
        $suratKeluar->penerima = $input['penerima'];
        $suratKeluar->softfile = $input['softfile'];
        $suratKeluar->jenis_surat = $input['jenis_surat'];

        $createdData = $suratKeluar->create();
        if ($createdData) {
            $activityLog->log(1, "POST", "Menambahkan surat keluar: " . json_encode($createdData));
            echo json_encode([
                "status" => "success",
                "message" => "Data berhasil ditambahkan",
                "created_data" => $createdData
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Gagal menambahkan data"
            ]);
        }
        break;

    case 'PUT':
        // Handle PUT request
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['id'], $input['tanggal_surat'], $input['nrp_pegawai'], $input['penerima'], $input['softfile'], $input['jenis_surat'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Data tidak lengkap"
            ]);
            exit;
        }

        $id = intval($input['id']);
        $suratKeluar->id = $id;

        // Ambil data sebelum diperbarui
        $beforeUpdateData = $suratKeluar->getById($id);
        if (!$beforeUpdateData) {
            echo json_encode([
                "status" => "error",
                "message" => "Data tidak ditemukan"
            ]);
            exit;
        }

        $suratKeluar->tanggal_surat = $input['tanggal_surat'];
        $suratKeluar->nrp_pegawai = $input['nrp_pegawai'];
        $suratKeluar->penerima = $input['penerima'];
        $suratKeluar->softfile = $input['softfile'];
        $suratKeluar->jenis_surat = $input['jenis_surat'];

        $updatedData = $suratKeluar->update($id);
        if ($updatedData) {
            $activityLog->log(1, "PUT", "Memperbarui surat keluar dari: " . json_encode($beforeUpdateData) . " menjadi: " . json_encode($updatedData));
            echo json_encode([
                "status" => "success",
                "message" => "Data berhasil diperbarui",
                "before_update" => $beforeUpdateData,
                "after_update" => $updatedData
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
        $deletedData = $suratKeluar->delete($id);

        if ($deletedData) {
            $activityLog->log(1, "DELETE", "Menghapus surat keluar dengan ID: $id");
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
