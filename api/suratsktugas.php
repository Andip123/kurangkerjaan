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
        // Handle GET request (Read data)
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $stmt = $suratSkTugas->getById($id);

            $stmt->bind_result($id, $tanggal_sk, $nrp_pegawai, $deskripsi, $softfile, $nama_pegawai);

            $data = [];
            while ($stmt->fetch()) {
                $data = [
                    "id" => $id,
                    "tanggal_sk" => $tanggal_sk,
                    "nrp_pegawai" => $nrp_pegawai,
                    "deskripsi" => $deskripsi,
                    "softfile" => $softfile,
                    "nama_pegawai" => $nama_pegawai
                ];
            }

            echo json_encode([
                "status" => "success",
                "data" => $data
            ]);
        } else {
            $result = $suratSkTugas->getAll();

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

        if ($suratSkTugas->create()) {
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

        if ($suratSkTugas->update($id)) {
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
                // Jika ID dikirim melalui query string
                $id = intval($_GET['id']);
            } elseif (isset($input['id'])) {
                // Jika ID dikirim melalui body JSON
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
            if ($suratSkTugas->delete($id)) {
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
