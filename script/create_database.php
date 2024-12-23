<?php
include '../config/database.php';

// Membuat objek database dan mendapatkan koneksi
$database = new Database();
$conn = $database->getConnection(); // Mengambil koneksi dari objek Database

if ($conn === null) {
    die("Koneksi ke database gagal!");
}

// Pilih database
$conn->select_db("arsip"); // Pastikan database "polres_demak" ada

// Query pembuatan tabel
$tables = [
    "users" => "CREATE TABLE IF NOT EXISTS users (
        id VARCHAR(36) PRIMARY KEY,
        nama VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(50) NOT NULL
    )",

    "divisi" => "CREATE TABLE IF NOT EXISTS divisi (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kode_divisi VARCHAR(50) NOT NULL UNIQUE,
        nama_divisi VARCHAR(255) NOT NULL,
        alamat_kantor TEXT NOT NULL
    )",

    "pegawai" => "CREATE TABLE IF NOT EXISTS pegawai (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id VARCHAR(36) NOT NULL,
        nomor_nrp VARCHAR(50) NOT NULL UNIQUE,
        nama VARCHAR(255) NOT NULL,
        nomor_telepon VARCHAR(15) NOT NULL,
        deskripsi TEXT,
        alamat TEXT NOT NULL,
        division_id INT NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (division_id) REFERENCES divisi(id) ON DELETE CASCADE
    )",

    "surat_keterangan_tugas" => "CREATE TABLE IF NOT EXISTS surat_keterangan_tugas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        member_id INT NOT NULL,
        nomor_sk VARCHAR(50) NOT NULL UNIQUE,
        tanggal_sk DATE NOT NULL,
        deskripsi TEXT NOT NULL,
        softfile VARCHAR(255),
        FOREIGN KEY (member_id) REFERENCES pegawai(id) ON DELETE CASCADE
    )",

    "surat_masuk" => "CREATE TABLE IF NOT EXISTS surat_masuk (
        id INT AUTO_INCREMENT PRIMARY KEY,
        penerima_id INT NOT NULL,
        kode_surat VARCHAR(50) NOT NULL UNIQUE,
        tanggal_masuk DATE NOT NULL,
        asal_surat VARCHAR(255) NOT NULL,
        jenis_surat VARCHAR(100) NOT NULL,
        softfile VARCHAR(255),
        FOREIGN KEY (penerima_id) REFERENCES pegawai(id) ON DELETE CASCADE
    )",

    "surat_keluar" => "CREATE TABLE IF NOT EXISTS surat_keluar (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pengirim_id INT NOT NULL,
        kode_surat VARCHAR(50) NOT NULL UNIQUE,
        tanggal_keluar DATE NOT NULL,
        penerima VARCHAR(255) NOT NULL,
        jenis_surat VARCHAR(100) NOT NULL,
        softfile VARCHAR(255),
        FOREIGN KEY (pengirim_id) REFERENCES pegawai(id) ON DELETE CASCADE
    )",

    // "histori_pegawai" => "CREATE TABLE IF NOT EXISTS histori_pegawai (
    //     id INT AUTO_INCREMENT PRIMARY KEY,
    //     nrp_pegawai VARCHAR(50) NOT NULL,
    //     nama_pegawai VARCHAR(255) NOT NULL,
    //     email VARCHAR(255) NOT NULL,
    //     nohp VARCHAR(15) NOT NULL,
    //     kode_bagian VARCHAR(50) NOT NULL,
    //     status ENUM('aktif', 'non-aktif') NOT NULL,
    //     sebelum TEXT NOT NULL,
    //     sesudah TEXT NOT NULL,
    //     deskripsi TEXT NOT NULL,
    //     tanggal_perubahan DATE NOT NULL,
    //     member_id INT NOT NULL,
    //     FOREIGN KEY (member_id) REFERENCES pegawai(id) ON DELETE CASCADE
    // )"
];

// Eksekusi query untuk setiap tabel
foreach ($tables as $table => $query) {
    if ($conn->query($query) === TRUE) {
        echo "Tabel $table berhasil dibuat!\n";
    } else {
        echo "Error membuat tabel $table: " . $conn->error . "\n";
    }
}

// Tutup koneksi
$conn->close();
?>
