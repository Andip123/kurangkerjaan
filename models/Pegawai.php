<?php
class Pegawai {
    private $conn;
    private $table = "pegawai";

    public $id;
    public $nrp_pegawai;
    public $nama;
    public $email;
    public $nomor_hp;
    public $kode_bagian;
    public $pangkat;
    public $jabatan;
    public $deskripsi;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all Pegawai
    public function getAll() {
        $query = "SELECT * FROM " . $this->table;
        $result = $this->conn->query($query);
        return $result;
    }

    // Get Pegawai by ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt;
    }

    // Create new Pegawai
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (nrp_pegawai, nama, email, nomor_hp, kode_bagian, pangkat, jabatan, deskripsi) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssssssss", 
            $this->nrp_pegawai, 
            $this->nama, 
            $this->email, 
            $this->nomor_hp, 
            $this->kode_bagian, 
            $this->pangkat, 
            $this->jabatan, 
            $this->deskripsi
        );
        return $stmt->execute();
    }

    // Update existing Pegawai
    public function update($id) {
        $query = "UPDATE " . $this->table . " 
                  SET nrp_pegawai = ?, nama = ?, email = ?, nomor_hp = ?, kode_bagian = ?, pangkat = ?, jabatan = ?, deskripsi = ? 
                  WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssssssssi", 
            $this->nrp_pegawai, 
            $this->nama, 
            $this->email, 
            $this->nomor_hp, 
            $this->kode_bagian, 
            $this->pangkat, 
            $this->jabatan, 
            $this->deskripsi, 
            $id
        );
        return $stmt->execute();
    }

    // Delete Pegawai and handle related data
    public function delete($id) {
        // Check if related data exists in surat_masuk
        $checkQuery = "SELECT COUNT(*) AS count FROM surat_masuk WHERE penerima_id = ?";
        $stmt = $this->conn->prepare($checkQuery);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            // Delete related data in surat_masuk
            $deleteQuery = "DELETE FROM surat_masuk WHERE penerima_id = ?";
            $stmt = $this->conn->prepare($deleteQuery);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }

        // Delete the pegawai
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>
