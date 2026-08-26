<?php
session_start();

// Proteksi halaman
if($_SESSION['status'] != "login"){
    header("location:index.php");
    exit();
}

include 'koneksi.php';

// ==========================================
// 1. PROSES TAMBAH DATA MASTER POIN
// ==========================================
if (isset($_POST['aksi_tambah'])) {

    $jenis         = mysqli_real_escape_string($koneksi, $_POST['jenis']);
    $nama_perilaku = mysqli_real_escape_string($koneksi, $_POST['nama_perilaku']);
    $poin          = mysqli_real_escape_string($koneksi, $_POST['poin']);

    $query_tambah = "INSERT INTO master_poin
                    (jenis, nama_perilaku, poin)
                    VALUES
                    ('$jenis', '$nama_perilaku', '$poin')";

    if(mysqli_query($koneksi, $query_tambah)){
        header("location:master_poin?pesan=tambah_sukses");
    } else {
        header("location:master_poin?pesan=gagal");
    }
    exit();
}

// ==========================================
// 2. PROSES EDIT DATA MASTER POIN
// ==========================================
if (isset($_POST['aksi_edit'])) {

    $id_aturan     = mysqli_real_escape_string($koneksi, $_POST['id_aturan']);
    $jenis         = mysqli_real_escape_string($koneksi, $_POST['jenis']);
    $nama_perilaku = mysqli_real_escape_string($koneksi, $_POST['nama_perilaku']);
    $poin          = mysqli_real_escape_string($koneksi, $_POST['poin']);

    $query_edit = "UPDATE master_poin SET
                    jenis = '$jenis',
                    nama_perilaku = '$nama_perilaku',
                    poin = '$poin'
                    WHERE id_aturan = '$id_aturan'";

    if(mysqli_query($koneksi, $query_edit)){
        header("location:master_poin?pesan=edit_sukses");
    } else {
        header("location:master_poin?pesan=gagal");
    }
    exit();
}

// ==========================================
// 3. PROSES HAPUS DATA MASTER POIN
// ==========================================
if (isset($_GET['hapus'])) {

    $id_aturan = mysqli_real_escape_string($koneksi, $_GET['hapus']);

    $query_hapus = "DELETE FROM master_poin
                    WHERE id_aturan = '$id_aturan'";

    if(mysqli_query($koneksi, $query_hapus)){
        header("location:master_poin?pesan=hapus_sukses");
    } else {
        header("location:master_poin?pesan=gagal");
    }
    exit();
}

// Jika akses langsung
header("location:dashboard");
exit();
?>