<?php
class SuratMasuk {
    // private $conn;
    private $table_name = "arsip";

    public $id;
    public $penerima_id;
    public $kode_surat;
    public $tanggal_masuk;
    public $asal_surat;
    public $jenis_surat;
    public $soffile;

    public function __construct($db) {
        $this->conn = $db;
    }

    // **Read Data**
    public function read() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // **Create Data**
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET 
            penerima_id=:penerima_id, kode_surat=:kode_surat, tanggal_masuk=:tanggal_masuk, 
            asal_surat=:asal_surat, jenis_surat=:jenis_surat, soffile=:soffile";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":penerima_id", $this->penerima_id);
        $stmt->bindParam(":kode_surat", $this->kode_surat);
        $stmt->bindParam(":tanggal_masuk", $this->tanggal_masuk);
        $stmt->bindParam(":asal_surat", $this->asal_surat);
        $stmt->bindParam(":jenis_surat", $this->jenis_surat);
        $stmt->bindParam(":soffile", $this->soffile);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // **Delete Data**
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
