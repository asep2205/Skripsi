<?php
session_start();
header('Content-Type: application/json');

if($_SESSION['status'] != "login"){
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Silakan login kembali.']);
    exit();
}

include 'koneksi.php';

// Menangkap data dari form input modal
$id_user = $_SESSION['id_user']; // Pastikan session id_user tersedia saat login
$nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
$password_baru = $_POST['password_baru'];

if (empty($nama_lengkap)) {
    echo json_encode(['status' => 'error', 'message' => 'Nama lengkap tidak boleh kosong.']);
    exit();
}

// Menyesuaikan query: mengganti password atau hanya update nama saja
if (!empty($password_baru)) {
    // Menggunakan md5() sesuai dengan sistem basis data lama Anda
    $password_secure = md5($password_baru);
    $query = "UPDATE users SET nama_lengkap = '$nama_lengkap', password = '$password_secure' WHERE id_user = '$id_user'";
} else {
    $query = "UPDATE users SET nama_lengkap = '$nama_lengkap' WHERE id_user = '$id_user'";
}

if (mysqli_query($koneksi, $query)) {
    // Sinkronisasi ulang variabel session dengan nama yang baru diubah
    $_SESSION['nama_lengkap'] = $nama_lengkap;
    echo json_encode(['status' => 'success', 'message' => 'Profil Anda berhasil diperbarui.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui database: ' . mysqli_error($koneksi)]);
}
?>