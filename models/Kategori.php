<?php
class Kategori {
    private $conn;
    private $table = "kategori";

    public $id;
    public $nama;
    public $deskripsi;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all Kategori
    public function getAll() {
        $query = "SELECT * FROM " . $this->table;
        $result = $this->conn->query($query);
        return $result;
    }

    // Get Kategori by ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt;
    }

    // Create new Kategori
    public function create() {
        $query = "INSERT INTO " . $this->table . " (nama, deskripsi) 
                  VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $this->nama, $this->deskripsi);
        return $stmt->execute();
    }

    // Update existing Kategori
    public function update($id) {
        $query = "UPDATE " . $this->table . " 
                  SET nama = ?, deskripsi = ? 
                  WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssi", $this->nama, $this->deskripsi, $id);
        return $stmt->execute();
    }

    // Delete Kategori
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>
