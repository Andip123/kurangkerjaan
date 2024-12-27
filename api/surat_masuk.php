<?php
header("Content-Type: application/json");
require_once '../config/database.php';
require_once '../models/SuratMasuk.php';
require_once '../models/ActivityLog.php';

// Membuat koneksi ke database
$database = new Database();
$conn = $database->getConnection();
$suratMasuk = new SuratMasuk($conn);
$activityLog = new ActivityLog($conn);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $data = $suratMasuk->readSingle($id);
    
            if ($data) {
                echo json_encode([
                    "status" => "success",
                    "data" => $data
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Data dengan ID $id tidak ditemukan"
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
        
            // Validasi input
            if (!isset($input['penerima_id'], $input['kode_surat'], $input['tanggal_masuk'], $input['asal_surat'], $input['softfile'], $input['jenis_surat'], $input['penerima_nama'])) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Data tidak lengkap"
                ]);
                exit;
            }
        
            $suratMasuk->penerima_id = intval($input['penerima_id']);
            $suratMasuk->kode_surat = $input['kode_surat'];
            $suratMasuk->tanggal_masuk = $input['tanggal_masuk'];
            $suratMasuk->asal_surat = $input['asal_surat'];
            $suratMasuk->softfile = $input['softfile'];
            $suratMasuk->jenis_surat = $input['jenis_surat'];
            $suratMasuk->penerima_nama = $input['penerima_nama'];
        
            // Validasi apakah penerima_id valid
            if (!$suratMasuk->isPenerimaIdValid($suratMasuk->penerima_id)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "ID penerima '" . $suratMasuk->penerima_id . "' tidak ditemukan di tabel pegawai"
                ]);
                exit;
            }
        
            // Validasi apakah penerima_nama sesuai dengan penerima_id
            if (!$suratMasuk->isPenerimaNamaValid($suratMasuk->penerima_id, $suratMasuk->penerima_nama)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Nama penerima '" . $suratMasuk->penerima_nama . "' tidak sesuai dengan ID penerima '" . $suratMasuk->penerima_id . "'"
                ]);
                exit;
            }
        
            // Simpan data
            $createdData = $suratMasuk->create();
            if ($createdData) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Data surat masuk berhasil ditambahkan",
                    "data" => $createdData
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Gagal menambahkan data surat masuk"
                ]);
            }
            break;
            
            case 'PUT':
                $input = json_decode(file_get_contents('php://input'), true);
            
                // Validasi input
                if (!isset($input['id'], $input['penerima_id'], $input['kode_surat'], $input['tanggal_masuk'], $input['asal_surat'], $input['softfile'], $input['jenis_surat'], $input['penerima_nama'])) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Data tidak lengkap"
                    ]);
                    exit;
                }
            
                $suratMasuk->id = intval($input['id']);
                $suratMasuk->penerima_id = intval($input['penerima_id']);
                $suratMasuk->kode_surat = $input['kode_surat'];
                $suratMasuk->tanggal_masuk = $input['tanggal_masuk'];
                $suratMasuk->asal_surat = $input['asal_surat'];
                $suratMasuk->softfile = $input['softfile'];
                $suratMasuk->jenis_surat = $input['jenis_surat'];
                $suratMasuk->penerima_nama = $input['penerima_nama'];
            
                // Validasi ID surat
                $beforeUpdate = $suratMasuk->readSingle($suratMasuk->id);
                if (!$beforeUpdate) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Data dengan ID " . $suratMasuk->id . " tidak ditemukan"
                    ]);
                    exit;
                }
            
                // Validasi kecocokan penerima_nama dengan penerima_id
                if (!$suratMasuk->isPenerimaNamaValid($suratMasuk->penerima_id, $suratMasuk->penerima_nama)) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Nama penerima '" . $suratMasuk->penerima_nama . "' tidak sesuai dengan ID penerima '" . $suratMasuk->penerima_id . "'"
                    ]);
                    exit;
                }
            
                // Update data
                if ($suratMasuk->update()) {
                    $afterUpdate = $suratMasuk->readSingle($suratMasuk->id);
                    echo json_encode([
                        "status" => "success",
                        "message" => "Data surat masuk berhasil diperbarui",
                        "before_update" => $beforeUpdate,
                        "after_update" => $afterUpdate
                    ]);
                } else {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Gagal memperbarui data surat masuk"
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
                
                    $id = intval($_GET['id']);
                    $deletedData = $suratMasuk->delete($id);
                
                    if ($deletedData) {
                        echo json_encode([
                            "status" => "success",
                            "message" => "Data berhasil dihapus",
                            "deleted_data" => $deletedData // Menampilkan semua data, termasuk penerima_nama
                        ]);
                    } else {
                        echo json_encode([
                            "status" => "error",
                            "message" => "Data tidak ditemukan atau gagal dihapus"
                        ]);
                    }
                    break;
                
}

$conn->close();
?>
