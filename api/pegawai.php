<?php
header("Content-Type: application/json");
require_once '../config/database.php';
require_once '../models/Pegawai.php';
require_once '../models/ActivityLog.php';

$database = new Database();
$conn = $database->getConnection();
$activityLog = new ActivityLog($conn);

$pegawai = new Pegawai($conn);

// Cek request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Handle GET request (Read data)
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $data = $pegawai->getById($id);

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
            $data = $pegawai->getAll();
            echo json_encode([
                "status" => "success",
                "data" => $data
            ]);
        }
        break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
        
            if (!isset($input['nrp_pegawai'], $input['nama'], $input['email'], $input['nomor_hp'], $input['kode_bagian'], $input['pangkat'], $input['jabatan'], $input['deskripsi'])) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Data tidak lengkap"
                ]);
                exit;
            }
        
            $pegawai->nrp_pegawai = $input['nrp_pegawai'];
            $pegawai->nama = $input['nama'];
            $pegawai->email = $input['email'];
            $pegawai->nomor_hp = $input['nomor_hp'];
            $pegawai->kode_bagian = $input['kode_bagian'];
            $pegawai->pangkat = $input['pangkat'];
            $pegawai->jabatan = $input['jabatan'];
            $pegawai->deskripsi = $input['deskripsi'];
        
            // Validasi kode_bagian
            if (!$pegawai->isKodeBagianValid($pegawai->kode_bagian)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Kode bagian '" . $pegawai->kode_bagian . "' tidak ditemukan di tabel divisi"
                ]);
                exit;
            }
        
            if ($pegawai->create()) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Data pegawai berhasil ditambahkan"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Gagal menambahkan data pegawai"
                ]);
            }
            break;
        
        case 'PUT':
            try {
                // Handle PUT request (Update data)
                $input = json_decode(file_get_contents('php://input'), true);
        
                if (!isset($input['id'], $input['nrp_pegawai'], $input['nama'], $input['email'], $input['nomor_hp'], $input['kode_bagian'], $input['pangkat'], $input['jabatan'], $input['deskripsi'])) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Data tidak lengkap"
                    ]);
                    exit;
                }
        
                $id = intval($input['id']);
                $beforeUpdate = $pegawai->getById($id); // Data sebelum update
        
                if (!$beforeUpdate) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Data dengan ID $id tidak ditemukan"
                    ]);
                    exit;
                }
        
                $pegawai->nrp_pegawai = $input['nrp_pegawai'];
                $pegawai->nama = $input['nama'];
                $pegawai->email = $input['email'];
                $pegawai->nomor_hp = $input['nomor_hp'];
                $pegawai->kode_bagian = $input['kode_bagian'];
                $pegawai->pangkat = $input['pangkat'];
                $pegawai->jabatan = $input['jabatan'];
                $pegawai->deskripsi = $input['deskripsi'];
        
                $isUpdated = $pegawai->update($id); // Update data
        
                if ($isUpdated) {
                    $afterUpdate = $pegawai->getById($id); // Data setelah update
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
            } catch (Exception $e) {
                echo json_encode([
                    "status" => "error",
                    "message" => $e->getMessage()
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

        $dataBeforeDelete = $pegawai->getById($id);

        if ($dataBeforeDelete && $pegawai->delete($id)) {
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
