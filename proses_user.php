<?php
session_start();
// Proteksi halaman: pastikan user sudah login
if($_SESSION['status'] != "login"){
    header("location:index.php");
    exit();
}

// Hubungkan ke database menggunakan variabel $koneksi
include 'koneksi.php';

// ==========================================
// 1. PROSES TAMBAH DATA USER
// ==========================================
if (isset($_POST['aksi_tambah'])) {
    $username     = mysqli_real_escape_string($koneksi, $_POST['username']);
    // Amankan string dulu, baru dibungkus dengan md5()
    $password     = md5(mysqli_real_escape_string($koneksi, $_POST['password'])); 
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $role         = mysqli_real_escape_string($koneksi, $_POST['role']);

    // Jika ingin password terenkripsi, Anda bisa gunakan: md5($password) atau password_hash($password, PASSWORD_DEFAULT)
    // Di bawah ini menggunakan text biasa sesuai bawaan form standar:
    $query_tambah = "INSERT INTO users (username, password, nama_lengkap, role) 
                     VALUES ('$username', '$password', '$nama_lengkap', '$role')";
    
    if (mysqli_query($koneksi, $query_tambah)) {
        header("location:manajemen_user?pesan=tambah_sukses");
    } else {
        header("location:manajemen_user?pesan=gagal");
    }
    exit();
}

// ==========================================
// 2. PROSES UPDATE / EDIT DATA USER
// ==========================================
if (isset($_POST['aksi_edit'])) {
    $id_user      = mysqli_real_escape_string($koneksi, $_POST['id_user']);
    $username     = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password     = md5(mysqli_real_escape_string($koneksi, $_POST['password']));
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $role         = mysqli_real_escape_string($koneksi, $_POST['role']);

    // Cek apakah user mengisi password baru atau mengosongkannya
    if (!empty($password)) {
        // Jika password diisi, update seluruh field termasuk password baru
        $query_edit = "UPDATE users SET 
                       username = '$username', 
                       password = '$password', 
                       nama_lengkap = '$nama_lengkap', 
                       role = '$role' 
                       WHERE id_user = '$id_user'";
    } else {
        // Jika password kosong, hanya update username, nama, dan role (password lama tetap aman)
        $query_edit = "UPDATE users SET 
                       username = '$username', 
                       nama_lengkap = '$nama_lengkap', 
                       role = '$role' 
                       WHERE id_user = '$id_user'";
    }
    
    if (mysqli_query($koneksi, $query_edit)) {
        header("location:manajemen_user?pesan=edit_sukses");
    } else {
        header("location:manajemen_user?pesan=gagal");
    }
    exit();
}

// ==========================================
// 3. PROSES HAPUS DATA USER
// ==========================================
if (isset($_GET['hapus'])) {
    $id_user = mysqli_real_escape_string($koneksi, $_GET['hapus']);

    $query_hapus = "DELETE FROM users WHERE id_user = '$id_user'";
    
    if (mysqli_query($koneksi, $query_hapus)) {
        header("location:manajemen_user?pesan=hapus_sukses");
    } else {
        header("location:manajemen_user?pesan=gagal");
    }
    exit();
}

// Jika ada akses ilegal tanpa mengirimkan parameter aksi, kembalikan ke dashboard
header("location:dashboard");
exit();
?>