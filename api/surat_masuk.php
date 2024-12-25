<?php
header("Content-Type: application/json");
require_once '../config/database.php';
require_once '../models/SuratMasuk.php'; // Tambahkan ini untuk memuat class SuratMasuk
require_once '../models/ActivityLog.php';

// Membuat koneksi ke database
$database = new Database();
$conn = $database->getConnection();
$activityLog = new ActivityLog($conn);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $suratMasuk = new SuratMasuk($conn);

        if (isset($_GET['id'])) {
            $suratMasuk->id = intval($_GET['id']);
            $data = $suratMasuk->readSingle();

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
            $data = $suratMasuk->readAll();

            echo json_encode([
                "status" => "success",
                "data" => $data
            ]);
        }
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['penerima_id'], $input['kode_surat'], $input['tanggal_masuk'], $input['asal_surat'], $input['softfile'], $input['jenis_surat'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Data tidak lengkap"
            ]);
            exit;
        }

        $suratMasuk = new SuratMasuk($conn);
        $suratMasuk->penerima_id = $input['penerima_id'];
        $suratMasuk->kode_surat = $input['kode_surat'];
        $suratMasuk->tanggal_masuk = $input['tanggal_masuk'];
        $suratMasuk->asal_surat = $input['asal_surat'];
        $suratMasuk->softfile = $input['softfile'];
        $suratMasuk->jenis_surat = $input['jenis_surat'];

        $createdData = $suratMasuk->create();
        if ($createdData) {
            $activityLog->log(1, "POST", "Menambahkan surat masuk: " . json_encode($createdData));
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
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['id'], $input['penerima_id'], $input['kode_surat'], $input['tanggal_masuk'], $input['asal_surat'], $input['softfile'], $input['jenis_surat'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Data tidak lengkap"
            ]);
            exit;
        }

        $suratMasuk = new SuratMasuk($conn);
        $suratMasuk->id = $input['id'];
        $suratMasuk->penerima_id = $input['penerima_id'];
        $suratMasuk->kode_surat = $input['kode_surat'];
        $suratMasuk->tanggal_masuk = $input['tanggal_masuk'];
        $suratMasuk->asal_surat = $input['asal_surat'];
        $suratMasuk->softfile = $input['softfile'];
        $suratMasuk->jenis_surat = $input['jenis_surat'];

        $updatedData = $suratMasuk->update();
        if ($updatedData) {
            $activityLog->log(1, "PUT", "Memperbarui surat masuk: " . json_encode($updatedData));
            echo json_encode([
                "status" => "success",
                "message" => "Data berhasil diperbarui",
                "updated_data" => $updatedData
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Gagal memperbarui data"
            ]);
        }
        break;

    case 'DELETE':
        if (!isset($_GET['id'])) {
            echo json_encode([
                "status" => "error",
                "message" => "ID tidak diberikan"
            ]);
            exit;
        }

        $suratMasuk = new SuratMasuk($conn);
        $suratMasuk->id = intval($_GET['id']);

        $deletedData = $suratMasuk->delete();

        if ($deletedData) {
            $activityLog->log(1, "DELETE", "Menghapus surat masuk dengan ID: {$suratMasuk->id}");
            echo json_encode([
                "status" => "success",
                "message" => "Data berhasil dihapus",
                "deleted_data" => $deletedData
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Data tidak ditemukan atau gagal dihapus"
            ]);
        }
        break;

    default:
        echo json_encode([
            "status" => "error",
            "message" => "Metode HTTP tidak valid"
        ]);
        break;
}

$conn->close();
?>
