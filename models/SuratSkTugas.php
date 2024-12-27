<?php
class SuratSkTugas {
    private $conn;
    private $table = "surat_keterangan_tugas";

    public $id;
    public $tanggal_sk;
    public $nrp_pegawai;
    public $deskripsi;
    public $softfile;
    public $nama_pegawai;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function isNamaPegawaiValid($nrp_pegawai, $nama_pegawai) {
        $query = "SELECT COUNT(*) AS count 
                  FROM pegawai 
                  WHERE nrp_pegawai = ? AND nama = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $nrp_pegawai, $nama_pegawai);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        return $count > 0;
    }

    public function getById($id) {
        $query = "SELECT skt.*, p.nama AS nama_pegawai 
                  FROM " . $this->table . " skt 
                  JOIN pegawai p ON skt.nrp_pegawai = p.nrp_pegawai 
                  WHERE skt.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        return $result->fetch_assoc(); // Mengembalikan satu baris data sebagai array asosiatif
    }    

    public function getAll() {
        $query = "SELECT skt.*, p.nama AS nama_pegawai 
                  FROM " . $this->table . " skt 
                  JOIN pegawai p ON skt.nrp_pegawai = p.nrp_pegawai";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    
        return $data; // Kembalikan semua data dalam bentuk array
    }
    

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
    
        if ($stmt->execute()) {
            $this->id = $this->conn->insert_id; // Ambil ID data baru
            return $this->getById($this->id); // Ambil data yang baru dibuat
        }
    
        return false;
    }
    

    public function update($id) {
        // Periksa apakah nrp_pegawai ada di tabel pegawai
        $query_check_pegawai = "SELECT * FROM pegawai WHERE nrp_pegawai = ?";
        $stmt_check = $this->conn->prepare($query_check_pegawai);
        $stmt_check->bind_param("s", $this->nrp_pegawai);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
    
        if ($result_check->num_rows === 0) {
            return [
                "error" => "nrp_pegawai tidak ditemukan di tabel pegawai"
            ];
        }
    
        // Ambil data sebelum diperbarui
        $query_before_update = "SELECT skt.*, p.nama AS nama_pegawai 
                                FROM " . $this->table . " skt 
                                JOIN pegawai p ON skt.nrp_pegawai = p.nrp_pegawai 
                                WHERE skt.id = ?";
        $stmt_before_update = $this->conn->prepare($query_before_update);
        $stmt_before_update->bind_param("i", $id);
        $stmt_before_update->execute();
        $dataBeforeUpdate = $stmt_before_update->get_result()->fetch_assoc();
    
        if (!$dataBeforeUpdate) {
            return [
                "error" => "Data tidak ditemukan"
            ];
        }
    
        // Lakukan pembaruan
        $query_update = "UPDATE " . $this->table . " 
                         SET tanggal_sk = ?, nrp_pegawai = ?, deskripsi = ?, softfile = ? 
                         WHERE id = ?";
        $stmt_update = $this->conn->prepare($query_update);
        $stmt_update->bind_param("ssssi", 
            $this->tanggal_sk, 
            $this->nrp_pegawai, 
            $this->deskripsi, 
            $this->softfile, 
            $id
        );
    
        if ($stmt_update->execute()) {
            // Ambil data setelah diperbarui
            $query_after_update = "SELECT skt.*, p.nama AS nama_pegawai 
                                   FROM " . $this->table . " skt 
                                   JOIN pegawai p ON skt.nrp_pegawai = p.nrp_pegawai 
                                   WHERE skt.id = ?";
            $stmt_after_update = $this->conn->prepare($query_after_update);
            $stmt_after_update->bind_param("i", $id);
            $stmt_after_update->execute();
            $dataAfterUpdate = $stmt_after_update->get_result()->fetch_assoc();
    
            return [
                "before_update" => $dataBeforeUpdate,
                "after_update" => $dataAfterUpdate
            ];
        }
    
        return false;
    }

    // Delete Surat Keterangan Tugas
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

        return false; // Jika data tidak ditemukan atau gagal dihapus
    }
}
?>
