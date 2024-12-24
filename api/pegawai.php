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
            $stmt = $pegawai->getById($id);

            $stmt->bind_result($id, $nrp_pegawai, $nama, $email, $nomor_hp, $kode_bagian, $pangkat, $jabatan, $deskripsi);

            $data = [];
            while ($stmt->fetch()) {
                $data = [
                    "id" => $id,
                    "nrp_pegawai" => $nrp_pegawai,
                    "nama" => $nama,
                    "email" => $email,
                    "nomor_hp" => $nomor_hp,
                    "kode_bagian" => $kode_bagian,
                    "pangkat" => $pangkat,
                    "jabatan" => $jabatan,
                    "deskripsi" => $deskripsi
                ];
            }

            echo json_encode([
                "status" => "success",
                "data" => $data
            ]);
        } else {
            $result = $pegawai->getAll();

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

        if ($pegawai->create()) {
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

        if (!isset($input['id'], $input['nrp_pegawai'], $input['nama'], $input['email'], $input['nomor_hp'], $input['kode_bagian'], $input['pangkat'], $input['jabatan'], $input['deskripsi'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Data tidak lengkap"
            ]);
            exit;
        }

        $id = intval($input['id']);
        $pegawai->nrp_pegawai = $input['nrp_pegawai'];
        $pegawai->nama = $input['nama'];
        $pegawai->email = $input['email'];
        $pegawai->nomor_hp = $input['nomor_hp'];
        $pegawai->kode_bagian = $input['kode_bagian'];
        $pegawai->pangkat = $input['pangkat'];
        $pegawai->jabatan = $input['jabatan'];
        $pegawai->deskripsi = $input['deskripsi'];

        if ($pegawai->update($id)) {
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

        if ($pegawai->delete($id)) {
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

    default:
        echo json_encode([
            "status" => "error",
            "message" => "Metode tidak didukung"
        ]);
        break;
}

$conn->close();
?>
