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

    public function getAll() {
        $query = "SELECT * FROM " . $this->table;
        $result = $this->conn->query($query);

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        return $data;
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        // Pastikan data ditemukan dan kembalikan sebagai array
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }

    public function isKodeBagianValid($kode_bagian) {
        $query = "SELECT COUNT(*) AS count FROM divisi WHERE kode_divisi = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $kode_bagian);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        return $count > 0;
    }
    
    

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

        if ($stmt->execute()) {
            $this->id = $this->conn->insert_id;
            return $this->getById($this->id);
        }

        return false;
    }

    public function update($id) {
        // Validasi apakah kode_bagian ada di tabel divisi
        $query_check_divisi = "SELECT COUNT(*) AS count FROM divisi WHERE kode_divisi = ?";
        $stmt_check_divisi = $this->conn->prepare($query_check_divisi);
        $stmt_check_divisi->bind_param("s", $this->kode_bagian);
        $stmt_check_divisi->execute();
        $stmt_check_divisi->bind_result($count_divisi);
        $stmt_check_divisi->fetch();
        $stmt_check_divisi->close();
    
        if ($count_divisi == 0) {
            throw new Exception("Kode bagian '{$this->kode_bagian}' tidak valid atau tidak ditemukan di tabel divisi.");
        }
    
        // Lakukan update jika validasi berhasil
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
    

    public function delete($id) {
        $dataBeforeDelete = $this->getById($id);
        if (!$dataBeforeDelete) {
            return false;
        }

        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>
