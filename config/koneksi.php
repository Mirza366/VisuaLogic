
<?php

$host = "localhost";
$user = "root";
$pass = "";
$db   = "visualogic";

// Membuat koneksi database
$koneksi = mysqli_connect($host, $user, $pass, $db);

// Memeriksa koneksi
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Mengatur karakter database
mysqli_set_charset($koneksi, "utf8mb4");

?>
