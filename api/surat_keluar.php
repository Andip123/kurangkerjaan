<?php
header("Content-Type: application/json");
require_once '../config/database.php';
require_once '../models/SuratKeluar.php';
require_once '../models/ActivityLog.php';

$database = new Database();
$conn = $database->getConnection();

$suratKeluar = new SuratKeluar($conn);

// Cek request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Handle GET request (Read data)
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $stmt = $suratKeluar->getById($id);

            // Gunakan bind_result dan fetch untuk menangani data dari statement
            $data = [];
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($id, $tanggal_surat, $nrp_pegawai, $penerima, $softfile, $jenis_surat, $nama_pegawai);
                while ($stmt->fetch()) {
                    $data = [
                        "id" => $id,
                        "tanggal_surat" => $tanggal_surat,
                        "nrp_pegawai" => $nrp_pegawai,
                        "penerima" => $penerima,
                        "softfile" => $softfile,
                        "jenis_surat" => $jenis_surat,
                        "nama_pegawai" => $nama_pegawai
                    ];
                }
            }

            echo json_encode([
                "status" => "success",
                "data" => $data
            ]);
        } else {
            // Ambil semua data surat keluar
            $result = $suratKeluar->getAll();
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

        if ($suratKeluar->create()) {
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

        if (!isset($input['id'], $input['tanggal_surat'], $input['nrp_pegawai'], $input['penerima'], $input['softfile'], $input['jenis_surat'])) {
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

        if ($suratKeluar->update($id)) {
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
    // Handle DELETE request
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($_GET['id'])) {
        // Ambil ID dari query parameter
        $id = intval($_GET['id']);
    } elseif (isset($input['id'])) {
        // Ambil ID dari body JSON
        $id = intval($input['id']);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "ID tidak diberikan"
        ]);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM surat_keluar WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "Data berhasil dihapus"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Gagal menghapus data: " . $stmt->error
        ]);
    }
    break;

}

$conn->close();
?>
