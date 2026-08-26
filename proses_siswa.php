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
// 1. PROSES TAMBAH DATA SISWA
// ==========================================
if (isset($_POST['aksi_tambah'])) {
    // Validasi wajib isi (NIS & Nama Siswa tidak boleh kosong)
    $nis_raw        = trim($_POST['nis'] ?? '');
    $nama_siswa_raw = trim($_POST['nama_siswa'] ?? '');
    if ($nis_raw === '' || $nama_siswa_raw === '') {
        header("location:data_siswa?pesan=gagal");
        exit();
    }

    // Paksa data menjadi huruf kapital agar konsisten dengan tampilan form
    $nis        = mysqli_real_escape_string($koneksi, strtoupper($nis_raw));
    $nama_siswa = mysqli_real_escape_string($koneksi, strtoupper($nama_siswa_raw));
    $kelas      = mysqli_real_escape_string($koneksi, strtoupper(trim($_POST['kelas'] ?? '')));

    // Set nilai poin bawaan awal menjadi 0 saat pertama kali mendaftar
    $query_tambah = "INSERT INTO siswa (nis, nama_siswa, kelas, total_poin_reward, total_poin_punishment) 
                     VALUES ('$nis', '$nama_siswa', '$kelas', 0, 0)";
    
    if (mysqli_query($koneksi, $query_tambah)) {
        header("location:data_siswa?pesan=tambah_sukses");
    } else {
        header("location:data_siswa?pesan=gagal");
    }
    exit();
}

// ==========================================
// 2. PROSES UPDATE / EDIT DATA SISWA
// ==========================================
if (isset($_POST['aksi_edit'])) {
    // Validasi wajib isi (NIS & Nama Siswa tidak boleh kosong)
    $nis_raw        = trim($_POST['nis'] ?? '');
    $nama_siswa_raw = trim($_POST['nama_siswa'] ?? '');
    if ($nis_raw === '' || $nama_siswa_raw === '') {
        header("location:data_siswa?pesan=gagal");
        exit();
    }

    $id_siswa       = mysqli_real_escape_string($koneksi, $_POST['id_siswa']);
    // Paksa data menjadi huruf kapital agar konsisten dengan tampilan form
    $nis            = mysqli_real_escape_string($koneksi, strtoupper($nis_raw));
    $nama_siswa     = mysqli_real_escape_string($koneksi, strtoupper($nama_siswa_raw));
    $kelas          = mysqli_real_escape_string($koneksi, strtoupper(trim($_POST['kelas'] ?? '')));
    $poin_reward    = mysqli_real_escape_string($koneksi, $_POST['poin_reward']);
    $poin_punishment= mysqli_real_escape_string($koneksi, $_POST['poin_punishment']);

    // Update data berdasarkan id_siswa menggunakan nama field database yang valid
    $query_edit = "UPDATE siswa SET 
                   nis = '$nis', 
                   nama_siswa = '$nama_siswa', 
                   kelas = '$kelas', 
                   total_poin_reward = '$poin_reward', 
                   total_poin_punishment = '$poin_punishment' 
                   WHERE id_siswa = '$id_siswa'";
    
    if (mysqli_query($koneksi, $query_edit)) {
        header("location:data_siswa?pesan=edit_sukses");
    } else {
        header("location:data_siswa?pesan=gagal");
    }
    exit();
}

// ==========================================
// 3. PROSES HAPUS DATA SISWA
// ==========================================
if (isset($_GET['hapus'])) {
    $id_siswa = mysqli_real_escape_string($koneksi, $_GET['hapus']);

    $query_hapus = "DELETE FROM siswa WHERE id_siswa = '$id_siswa'";
    
    if (mysqli_query($koneksi, $query_hapus)) {
        header("location:data_siswa?pesan=hapus_sukses");
    } else {
        header("location:data_siswa?pesan=gagal");
    }
    exit();
}

// Jika ada akses ilegal tanpa mengirimkan parameter aksi, kembalikan ke dashboard
header("location:dashboard");
exit();
?>