<?php
class Divisi {
    private $conn;
    private $table = "divisi";

    public $id;
    public $kode_divisi;
    public $nama_divisi;
    public $alamat_kantor;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table;
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Kembalikan null jika tidak ada data
        if ($result->num_rows === 0) {
            return null;
        }
        
        return $result->fetch_assoc();
    }

    public function isDuplicate($kode_divisi, $id = null) {
        $query = "SELECT COUNT(*) AS count FROM " . $this->table . " WHERE kode_divisi = ?";
        if ($id !== null) {
            $query .= " AND id != ?";
        }
        $stmt = $this->conn->prepare($query);
        if ($id !== null) {
            $stmt->bind_param("si", $kode_divisi, $id);
        } else {
            $stmt->bind_param("s", $kode_divisi);
        }
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        return $count > 0;
    }
    public function isKodeDivisiUsedByOther($kode_divisi, $id) {
        $query = "SELECT COUNT(*) AS count FROM " . $this->table . " WHERE kode_divisi = ? AND id != ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $kode_divisi, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
    
        return $data['count'] > 0; // Return true if the kode_divisi is already used by another ID
    }
    

    public function create() {
        if ($this->isDuplicate($this->kode_divisi)) {
            return [
                "status" => "error",
                "message" => "Kode divisi sudah ada, gunakan kode yang berbeda."
            ];
        }

        $query = "INSERT INTO " . $this->table . " (kode_divisi, nama_divisi, alamat_kantor) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sss", $this->kode_divisi, $this->nama_divisi, $this->alamat_kantor);
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    public function update($id) {
        if ($this->isDuplicate($this->kode_divisi, $id)) {
            return [
                "status" => "error",
                "message" => "Kode divisi sudah ada, gunakan kode yang berbeda."
            ];
        }

        $query = "UPDATE " . $this->table . " SET kode_divisi = ?, nama_divisi = ?, alamat_kantor = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sssi", $this->kode_divisi, $this->nama_divisi, $this->alamat_kantor, $id);
        return $stmt->execute();
    }

    public function delete($id) {
        // Validasi apakah kode divisi digunakan di tabel pegawai
        $checkPegawaiQuery = "SELECT COUNT(*) AS count FROM pegawai WHERE kode_bagian = (SELECT kode_divisi FROM divisi WHERE id = ?)";
        $stmtCheckPegawai = $this->conn->prepare($checkPegawaiQuery);
        $stmtCheckPegawai->bind_param("i", $id);
        $stmtCheckPegawai->execute();
        $stmtCheckPegawai->bind_result($count);
        $stmtCheckPegawai->fetch();
        $stmtCheckPegawai->close();

        if ($count > 0) {
            return [
                "status" => "error",
                "message" => "Kode divisi masih digunakan di tabel pegawai. Tidak dapat dihapus."
            ];
        }

        // Hapus data terkait di tabel pegawai (opsional jika dibutuhkan)
        $deletePegawaiQuery = "DELETE FROM pegawai WHERE kode_bagian = (SELECT kode_divisi FROM divisi WHERE id = ?)";
        $stmtDeletePegawai = $this->conn->prepare($deletePegawaiQuery);
        $stmtDeletePegawai->bind_param("i", $id);
        $stmtDeletePegawai->execute();
        $stmtDeletePegawai->close();
    
        // Hapus data di tabel divisi
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>
