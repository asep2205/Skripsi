<?php
session_start();

// Proteksi halaman: Jika belum login, kembalikan ke halaman login
if($_SESSION['status'] != "login"){
    header("location:index.php");
    exit();
}

include 'koneksi.php';

/* ==========================================
   1. TAMBAH DATA EVALUASI
========================================== */
if(isset($_POST['aksi_tambah'])){

    // Ambil data form dan sesuaikan dengan kolom database asli (poin, kategori, tindakan, jenis)
    $poin     = mysqli_real_escape_string($koneksi, $_POST['poin']);
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $tindakan = mysqli_real_escape_string($koneksi, $_POST['tindakan']);
    $jenis    = mysqli_real_escape_string($koneksi, $_POST['jenis']);

    // Query disesuaikan dengan struktur tabel database Anda
    $query = "INSERT INTO evaluasi_siswa
              (poin, kategori, tindakan, jenis)
              VALUES
              ('$poin', '$kategori', '$tindakan', '$jenis')";

    if(mysqli_query($koneksi, $query)){
        header("location:evaluasi_siswa?pesan=tambah_sukses");
    }else{
        header("location:evaluasi_siswa?pesan=gagal");
    }
    exit();
}

/* ==========================================
   2. EDIT DATA EVALUASI
========================================== */
if(isset($_POST['aksi_edit'])){

    $id_evaluasi  = mysqli_real_escape_string($koneksi, $_POST['id_evaluasi']);
    $poin         = mysqli_real_escape_string($koneksi, $_POST['poin']);
    $kategori     = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $tindakan     = mysqli_real_escape_string($koneksi, $_POST['tindakan']);
    $jenis        = mysqli_real_escape_string($koneksi, $_POST['jenis']);

    // Update query disesuaikan dengan nama kolom target database Anda
    $query = "UPDATE evaluasi_siswa SET
                poin = '$poin',
                kategori = '$kategori',
                tindakan = '$tindakan',
                jenis = '$jenis'
              WHERE id_evaluasi = '$id_evaluasi'";

    if(mysqli_query($koneksi, $query)){
        header("location:evaluasi_siswa?pesan=edit_sukses");
    }else{
        header("location:evaluasi_siswa?pesan=gagal");
    }
    exit();
}

/* ==========================================
   3. HAPUS DATA EVALUASI
========================================== */
if(isset($_GET['hapus'])){

    $id_evaluasi = mysqli_real_escape_string($koneksi, $_GET['hapus']);

    $query = "DELETE FROM evaluasi_siswa
              WHERE id_evaluasi = '$id_evaluasi'";

    if(mysqli_query($koneksi, $query)){
        header("location:evaluasi_siswa?pesan=hapus_sukses");
    }else{
        header("location:evaluasi_siswa?pesan=gagal");
    }
    exit();
}

// Redirect default jika tidak ada aksi yang terpenuhi
header("location:dashboard");
exit();
?>      