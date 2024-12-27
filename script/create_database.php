<?php
include '../config/database.php';

// Membuat objek database dan mendapatkan koneksi
$database = new Database();
$conn = $database->getConnection();

if ($conn === null) {
    die("Koneksi ke database gagal!");
}

// Pilih database
$conn->select_db("polresdemak_arsip");

// Query pembuatan tabel
$tables = [
    "admin" => "CREATE TABLE IF NOT EXISTS admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL
    )",

    "create_account" => "CREATE TABLE IF NOT EXISTS create_account (
        iid INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        role ENUM('Admin', 'User') NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",

    "kategori" => "CREATE TABLE IF NOT EXISTS kategori (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(100) NOT NULL,
        deskripsi TEXT NOT NULL
    )",

    "divisi" => "CREATE TABLE IF NOT EXISTS divisi (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kode_divisi VARCHAR(10) NOT NULL UNIQUE,
        nama_divisi VARCHAR(100) NOT NULL,
        alamat_kantor TEXT NOT NULL
    )",

    "pegawai" => "CREATE TABLE IF NOT EXISTS pegawai (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nrp_pegawai VARCHAR(20) NOT NULL UNIQUE,
        nama VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        nomor_hp VARCHAR(15) NOT NULL,
        kode_bagian VARCHAR(10) NOT NULL,
        pangkat VARCHAR(50) NOT NULL,
        jabatan VARCHAR(50) NOT NULL,
        deskripsi TEXT NOT NULL,
        FOREIGN KEY (kode_bagian) REFERENCES divisi(kode_divisi)
    )",

    "surat_masuk" => "CREATE TABLE IF NOT EXISTS surat_masuk (
        id INT AUTO_INCREMENT PRIMARY KEY,
        penerima_id INT NOT NULL,
        kode_surat VARCHAR(50) NOT NULL,
        tanggal_masuk DATE NOT NULL,
        asal_surat VARCHAR(255) NOT NULL,
        softfile VARCHAR(255) NOT NULL,
        jenis_surat VARCHAR(50) NOT NULL,
        FOREIGN KEY (penerima_id) REFERENCES pegawai(id)
    )",

    "surat_keterangan_tugas" => "CREATE TABLE IF NOT EXISTS surat_keterangan_tugas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tanggal_sk DATE NOT NULL,
        nrp_pegawai VARCHAR(20) NOT NULL,
        deskripsi TEXT NOT NULL,
        softfile VARCHAR(255) NOT NULL,
        FOREIGN KEY (nrp_pegawai) REFERENCES pegawai(nrp_pegawai)
    )",

    "surat_keluar" => "CREATE TABLE IF NOT EXISTS surat_keluar (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tanggal_surat DATE NOT NULL,
        nrp_pegawai VARCHAR(20) NOT NULL,
        penerima VARCHAR(255) NOT NULL,
        softfile VARCHAR(255) NOT NULL,
        jenis_surat VARCHAR(50) NOT NULL,
        FOREIGN KEY (nrp_pegawai) REFERENCES pegawai(nrp_pegawai)
    )",

    // Perbaikan foreign key untuk tabel activity_logs
    "activity_logs" => "CREATE TABLE IF NOT EXISTS activity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        action VARCHAR(50) NOT NULL,
        description TEXT NOT NULL,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES create_account(iid)
    )"
];

// Eksekusi query untuk setiap tabel
foreach ($tables as $table => $query) {
    if ($conn->query($query) === TRUE) {
        echo "Tabel $table berhasil dibuat!<br>";
    } else {
        echo "Error membuat tabel $table: " . $conn->error . "<br>";
    }
}

// Data Dummy untuk masing-masing tabel
$data = [
    "admin" => [
        "INSERT IGNORE INTO admin (nama, email, password) VALUES 
        ('Admin 1', 'admin1@example.com', 'password123'),
        ('Admin 2', 'admin2@example.com', 'password123')"
    ],

    "create_account" => [
        "INSERT IGNORE INTO create_account (nama, email, password, role) VALUES 
        ('User 1', 'user1@example.com', 'password123', 'User'),
        ('User 2', 'user2@example.com', 'password123', 'User'),
        ('Admin 3', 'admin3@example.com', 'password123', 'Admin')"
    ],

    "kategori" => [
        "INSERT IGNORE INTO kategori (nama, deskripsi) VALUES 
        ('Kategori 1', 'Deskripsi untuk Kategori 1'),
        ('Kategori 2', 'Deskripsi untuk Kategori 2')"
    ],

    "divisi" => [
        "INSERT IGNORE INTO divisi (kode_divisi, nama_divisi, alamat_kantor) VALUES 
        ('D01', 'Divisi IT', 'Jl. IT No.1'),
        ('D02', 'Divisi Keuangan', 'Jl. Keuangan No.2')"
    ],

    "pegawai" => [
        "INSERT IGNORE INTO pegawai (nrp_pegawai, nama, email, nomor_hp, kode_bagian, pangkat, jabatan, deskripsi) VALUES 
        ('12345', 'John Doe', 'john.doe@example.com', '08123456789', 'D01', 'Staff', 'IT Support', 'Pegawai IT'),
        ('67890', 'Jane Smith', 'jane.smith@example.com', '08198765432', 'D02', 'Supervisor', 'Keuangan', 'Pegawai Keuangan')"
    ],

    "surat_keterangan_tugas" => [
        "INSERT IGNORE INTO surat_keterangan_tugas (tanggal_sk, nrp_pegawai, deskripsi, softfile) VALUES 
        ('2024-02-01', '12345', 'Tugas ke PT ABC', 'softfile_sk1.pdf'),
        ('2024-02-02', '67890', 'Tugas ke PT XYZ', 'softfile_sk2.pdf')"
    ],

    "surat_masuk" => [
        "INSERT IGNORE INTO surat_masuk (penerima_id, kode_surat, tanggal_masuk, asal_surat, softfile, jenis_surat) VALUES 
        (1, 'SM-001', '2024-01-01', 'PT ABC', 'softfile.pdf', 'Resmi'),
        (2, 'SM-002', '2024-01-02', 'PT XYZ', 'softfile.pdf', 'Internal')"
    ],

    "surat_keluar" => [
        "INSERT IGNORE INTO surat_keluar (tanggal_surat, nrp_pegawai, penerima, softfile, jenis_surat) VALUES 
        ('2024-01-05', '12345', 'PT DEF', 'softfile1.pdf', 'Resmi'),
        ('2024-01-10', '67890', 'PT GHI', 'softfile2.pdf', 'Internal')"
    ]
];

// Eksekusi data dummy untuk setiap tabel
foreach ($data as $table => $queries) {
    foreach ($queries as $query) {
        if ($conn->query($query) === TRUE) {
            echo "Data dummy untuk tabel $table berhasil dimasukkan!<br>";
        } else {
            echo "Error memasukkan data dummy untuk tabel $table: " . $conn->error . "<br>";
        }
    }
}

// Tutup koneksi
$conn->close();
?>
