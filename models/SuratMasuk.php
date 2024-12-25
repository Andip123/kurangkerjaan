<?php
class SuratMasuk {
    private $conn;
    private $table_name = "surat_masuk";

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

    // **Read All Data**
    public function readAll() {
        $query = "SELECT sm.*, p.nama AS penerima_nama 
                  FROM " . $this->table_name . " sm 
                  JOIN pegawai p ON sm.penerima_id = p.id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $data = [];

        while ($row = $result->fetch_assoc()) {
            $data[] = $row; // Memasukkan setiap baris data ke dalam array
        }

        return $data; // Mengembalikan array data
    }

    // **Read Single Data**
    public function readSingle() {
        $query = "SELECT sm.*, p.nama AS penerima_nama 
                  FROM " . $this->table_name . " sm 
                  JOIN pegawai p ON sm.penerima_id = p.id
                  WHERE sm.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        return $result->fetch_assoc(); // Mengembalikan data sebagai array asosiatif
    }

    // **Create Data**
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
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
            $this->id = $this->conn->insert_id; // Ambil ID data baru
            return $this->readSingle(); // Ambil data yang baru dibuat
        }

        return false;
    }

    // **Update Data**
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
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
            return $this->readSingle(); // Ambil data yang baru diperbarui
        }
        return false;
    }

    // **Delete Data**
    public function delete() {
        // Ambil data sebelum dihapus
        $query_select = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt_select = $this->conn->prepare($query_select);
        $stmt_select->bind_param("i", $this->id);
        $stmt_select->execute();

        $result = $stmt_select->get_result();
        $dataBeforeDelete = $result->fetch_assoc();

        if ($dataBeforeDelete) {
            // Hapus data
            $query_delete = "DELETE FROM " . $this->table_name . " WHERE id = ?";
            $stmt_delete = $this->conn->prepare($query_delete);
            $stmt_delete->bind_param("i", $this->id);

            if ($stmt_delete->execute()) {
                return $dataBeforeDelete; // Kembalikan data yang dihapus
            }
        }

        return false; // Jika gagal atau data tidak ditemukan
    }
}
?>
