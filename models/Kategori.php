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

    public function getAll() {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->query($query);
        return $stmt->fetch_all(MYSQLI_ASSOC); // Ambil semua data
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc(); // Ambil satu baris data
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " (nama, deskripsi) VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $this->nama, $this->deskripsi);
        if ($stmt->execute()) {
            return $stmt->insert_id; // Kembalikan ID data baru
        }
        return false;
    }

    public function update($id) {
        $query = "UPDATE " . $this->table . " SET nama = ?, deskripsi = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssi", $this->nama, $this->deskripsi, $id);
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
