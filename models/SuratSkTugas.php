<?php
class SuratSkTugas {
    private $conn;
    private $table = "surat_keterangan_tugas";

    public $id;
    public $tanggal_sk;
    public $nrp_pegawai;
    public $deskripsi;
    public $softfile;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all Surat Keterangan Tugas
    public function getAll() {
        $query = "SELECT skt.*, p.nama AS nama_pegawai 
                  FROM " . $this->table . " skt 
                  JOIN pegawai p ON skt.nrp_pegawai = p.nrp_pegawai";
        $result = $this->conn->query($query);
        return $result;
    }

    // Get Surat Keterangan Tugas by ID
    public function getById($id) {
        $query = "SELECT skt.*, p.nama AS nama_pegawai 
                  FROM " . $this->table . " skt 
                  JOIN pegawai p ON skt.nrp_pegawai = p.nrp_pegawai 
                  WHERE skt.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt;
    }

    // Create new Surat Keterangan Tugas
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (tanggal_sk, nrp_pegawai, deskripsi, softfile) 
                  VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssss", 
            $this->tanggal_sk, 
            $this->nrp_pegawai, 
            $this->deskripsi, 
            $this->softfile
        );
        return $stmt->execute();
    }

    // Update existing Surat Keterangan Tugas
    public function update($id) {
        $query = "UPDATE " . $this->table . " 
                  SET tanggal_sk = ?, nrp_pegawai = ?, deskripsi = ?, softfile = ? 
                  WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssssi", 
            $this->tanggal_sk, 
            $this->nrp_pegawai, 
            $this->deskripsi, 
            $this->softfile, 
            $id
        );
        return $stmt->execute();
    }

    // Delete Surat Keterangan Tugas
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>
