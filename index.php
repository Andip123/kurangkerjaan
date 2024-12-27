<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "polresdemak_arsip";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
} else {
    echo "Koneksi berhasil!";
}

$conn->close();
?>
