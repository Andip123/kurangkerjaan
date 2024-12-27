<?php
class SuratMasuk {
    private $conn;
    private $table = "surat_masuk"; // Menggunakan $table yang konsisten

    public $id;
    public $penerima_id;
    public $kode_surat;
    public $tanggal_masuk;
    public $asal_surat;
    public $softfile;
    public $jenis_surat;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return null;
        }

        return $result->fetch_assoc();
    }

    public function isPenerimaNamaValid($penerima_id, $penerima_nama) {
        $query = "SELECT COUNT(*) AS count FROM pegawai WHERE id = ? AND nama = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("is", $penerima_id, $penerima_nama);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        return $count > 0;
    }
    

    public function readAll() {
        $query = "SELECT sm.*, p.nama AS penerima_nama 
                  FROM " . $this->table . " sm 
                  JOIN pegawai p ON sm.penerima_id = p.id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        return $data;
    }

    public function isPenerimaIdValid($id) {
        $query = "SELECT COUNT(*) AS count FROM pegawai WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        return $count > 0;
    }

    public function readSingle($id) {
        $query = "SELECT sm.*, p.nama AS penerima_nama 
                  FROM " . $this->table . " sm 
                  JOIN pegawai p ON sm.penerima_id = p.id
                  WHERE sm.id = ?";
    
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
    
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            return null;
        }
    
        return $result->fetch_assoc();
    }
    
    

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (penerima_id, kode_surat, tanggal_masuk, asal_surat, softfile, jenis_surat)
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "isssss",
            $this->penerima_id,
            $this->kode_surat,
            $this->tanggal_masuk,
            $this->asal_surat,
            $this->softfile,
            $this->jenis_surat
        );
    
        if ($stmt->execute()) {
            // Ambil data lengkap termasuk penerima_nama
            $newId = $this->conn->insert_id;
            $query = "SELECT sm.*, p.nama AS penerima_nama 
                      FROM " . $this->table . " sm 
                      JOIN pegawai p ON sm.penerima_id = p.id 
                      WHERE sm.id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $newId);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        }
        return false;
    }
    
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET penerima_id = ?, 
                      kode_surat = ?, 
                      tanggal_masuk = ?, 
                      asal_surat = ?, 
                      softfile = ?, 
                      jenis_surat = ?
                  WHERE id = ?";
    
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "isssssi",
            $this->penerima_id,
            $this->kode_surat,
            $this->tanggal_masuk,
            $this->asal_surat,
            $this->softfile,
            $this->jenis_surat,
            $this->id
        );
    
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    

    public function delete($id) {
        // Ambil data sebelum dihapus, termasuk penerima_nama
        $query_select = "SELECT sm.*, p.nama AS penerima_nama 
                         FROM " . $this->table . " sm
                         JOIN pegawai p ON sm.penerima_id = p.id
                         WHERE sm.id = ?";
        $stmt_select = $this->conn->prepare($query_select);
        $stmt_select->bind_param("i", $id);
        $stmt_select->execute();
    
        $result = $stmt_select->get_result();
        $dataBeforeDelete = $result->fetch_assoc();
    
        if ($dataBeforeDelete) {
            // Hapus data
            $query_delete = "DELETE FROM " . $this->table . " WHERE id = ?";
            $stmt_delete = $this->conn->prepare($query_delete);
            $stmt_delete->bind_param("i", $id);
    
            if ($stmt_delete->execute()) {
                return $dataBeforeDelete; // Kembalikan data yang dihapus
            }
        }
    
        return false; // Jika gagal atau data tidak ditemukan
    }
    
    
}
?>
