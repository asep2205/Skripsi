<?php
$host = "localhost";
$user = "root";       // Sesuaikan dengan username database kamu (biasanya root)
$pass = "";           // Kosongkan jika menggunakan XAMPP bawaan
$db   = "db_perilaku";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>