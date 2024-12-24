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

    // Get all Divisi
    public function getAll() {
        $query = "SELECT * FROM " . $this->table;
        $result = $this->conn->query($query);
        return $result;
    }

    // Get Divisi by ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt;
    }

    // Create new Divisi
    public function create() {
        $query = "INSERT INTO " . $this->table . " (kode_divisi, nama_divisi, alamat_kantor) 
                  VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sss", $this->kode_divisi, $this->nama_divisi, $this->alamat_kantor);
        return $stmt->execute();
    }

    // Update existing Divisi
    public function update($id) {
        $query = "UPDATE " . $this->table . " 
                  SET kode_divisi = ?, nama_divisi = ?, alamat_kantor = ? 
                  WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sssi", $this->kode_divisi, $this->nama_divisi, $this->alamat_kantor, $id);
        return $stmt->execute();
    }

    // Delete Divisi
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>
