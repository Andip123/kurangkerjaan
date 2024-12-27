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
            $input = json_decode(file_get_contents('php://input'), true);
        
            if (!isset($input['tanggal_surat'], $input['nrp_pegawai'], $input['penerima'], $input['softfile'], $input['jenis_surat'], $input['nama_pegawai'])) {
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
            $suratKeluar->nama_pegawai = $input['nama_pegawai'];
        
            // Validasi NRP Pegawai
            if (!$suratKeluar->isNrpPegawaiValid($suratKeluar->nrp_pegawai)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "NRP Pegawai '" . $suratKeluar->nrp_pegawai . "' tidak ditemukan di tabel pegawai"
                ]);
                exit;
            }
        
            if ($suratKeluar->create()) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Data surat keluar berhasil ditambahkan"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Gagal menambahkan data surat keluar"
                ]);
            }
            break;
        

            case 'PUT':
                $input = json_decode(file_get_contents('php://input'), true);
            
                if (!isset($input['id'], $input['tanggal_surat'], $input['nrp_pegawai'], $input['penerima'], $input['softfile'], $input['jenis_surat'], $input['nama_pegawai'])) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Data tidak lengkap"
                    ]);
                    exit;
                }
            
                $id = intval($input['id']);
                $suratKeluar->tanggal_surat = $input['tanggal_surat'];
                $suratKeluar->nrp_pegawai = $input['nrp_pegawai'];
                $suratKeluar->penerima = $input['penerima'];
                $suratKeluar->softfile = $input['softfile'];
                $suratKeluar->jenis_surat = $input['jenis_surat'];
                $suratKeluar->nama_pegawai = $input['nama_pegawai'];
            
                // Validasi apakah ID surat keluar ada
                $beforeUpdate = $suratKeluar->getById($id);
                if (!$beforeUpdate) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Data dengan ID $id tidak ditemukan"
                    ]);
                    exit;
                }
            
                // Validasi apakah nrp_pegawai valid
                if (!$suratKeluar->isNrpPegawaiValid($suratKeluar->nrp_pegawai)) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "NRP Pegawai '" . $suratKeluar->nrp_pegawai . "' tidak ditemukan di tabel pegawai"
                    ]);
                    exit;
                }
            
                // Perbarui data
                if ($suratKeluar->update($id)) {
                    $afterUpdate = $suratKeluar->getById($id);
                    echo json_encode([
                        "status" => "success",
                        "message" => "Data surat keluar berhasil diperbarui",
                        "before_update" => $beforeUpdate,
                        "after_update" => $afterUpdate
                    ]);
                } else {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Gagal memperbarui data surat keluar"
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
