<?php
class SuratKeluar {
    private $conn;
    private $table = "surat_keluar";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT sk.*, p.nama AS nama_pegawai 
                  FROM " . $this->table . " sk 
                  JOIN pegawai p ON sk.nrp_pegawai = p.nrp_pegawai";
        $result = $this->conn->query($query);
        return $result;
    }

    public function getById($id) {
        $query = "SELECT sk.id, sk.tanggal_surat, sk.nrp_pegawai, sk.penerima, sk.softfile, sk.jenis_surat, p.nama AS nama_pegawai 
                  FROM " . $this->table . " sk 
                  JOIN pegawai p ON sk.nrp_pegawai = p.nrp_pegawai 
                  WHERE sk.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (tanggal_surat, nrp_pegawai, penerima, softfile, jenis_surat) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sssss", $this->tanggal_surat, $this->nrp_pegawai, $this->penerima, $this->softfile, $this->jenis_surat);
        return $stmt->execute();
    }

    public function update($id) {
        $query = "UPDATE " . $this->table . " 
                  SET tanggal_surat = ?, nrp_pegawai = ?, penerima = ?, softfile = ?, jenis_surat = ? 
                  WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sssssi", $this->tanggal_surat, $this->nrp_pegawai, $this->penerima, $this->softfile, $this->jenis_surat, $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>
