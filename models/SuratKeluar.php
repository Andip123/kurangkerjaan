<?php
class SuratKeluar {
    private $conn;
    private $table = "surat_keluar";

    public $id;
    public $tanggal_surat;
    public $nrp_pegawai;
    public $penerima;
    public $softfile;
    public $jenis_surat;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT sk.*, p.nama AS nama_pegawai 
                  FROM " . $this->table . " sk 
                  JOIN pegawai p ON sk.nrp_pegawai = p.nrp_pegawai";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    public function getById($id) {
        $query = "SELECT sk.*, p.nama AS nama_pegawai 
                  FROM " . $this->table . " sk 
                  JOIN pegawai p ON sk.nrp_pegawai = p.nrp_pegawai 
                  WHERE sk.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function isNrpPegawaiValid($nrp_pegawai) {
        $query = "SELECT COUNT(*) AS count FROM pegawai WHERE nrp_pegawai = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $nrp_pegawai);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        return $count > 0;
    }
    

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (tanggal_surat, nrp_pegawai, penerima, softfile, jenis_surat) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sssss", $this->tanggal_surat, $this->nrp_pegawai, $this->penerima, $this->softfile, $this->jenis_surat);
    
        if ($stmt->execute()) {
            return $this->getById($this->conn->insert_id); // Ambil data yang baru dibuat
        }
        return false;
    }
    
    public function update($id) {
        $query = "UPDATE " . $this->table . " 
                  SET tanggal_surat = ?, nrp_pegawai = ?, penerima = ?, softfile = ?, jenis_surat = ? 
                  WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sssssi", $this->tanggal_surat, $this->nrp_pegawai, $this->penerima, $this->softfile, $this->jenis_surat, $id);
    
        if ($stmt->execute()) {
            return $this->getById($id); // Ambil data setelah diperbarui
        }
        return false;
    }
    
    
    public function delete($id) {
        $dataBeforeDelete = $this->getById($id); // Ambil data sebelum dihapus
    
        if ($dataBeforeDelete) {
            $query = "DELETE FROM " . $this->table . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id);
    
            if ($stmt->execute()) {
                return $dataBeforeDelete; // Kembalikan data yang dihapus
            }
        }
        return false;
    }    
}
?>
