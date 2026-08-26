<?php 
// mengaktifkan session php
session_start();

// menghubungkan dengan koneksi database Anda (sesuaikan nama filenya jika berbeda, misal koneksi.php)
include 'koneksi.php';

// menangkap data yang dikirim dari form
$nis = mysqli_real_escape_string($koneksi, $_POST['nis']);

// menyeleksi data siswa dengan nis yang sesuai
$login = mysqli_query($koneksi, "SELECT * FROM siswa WHERE nis='$nis'");
$cek = mysqli_num_rows($login);

if($cek > 0){
    $data = mysqli_fetch_assoc($login);

    // buat session khusus monitoring orang tua
    $_SESSION['id_siswa']   = $data['id_siswa'];
    $_SESSION['nis']        = $data['nis'];
    $_SESSION['nama_siswa'] = $data['nama_siswa'];
    $_SESSION['kelas']      = $data['kelas'];
    $_SESSION['role']       = 'Orang Tua'; // Kita tandai rolenya sebagai Orang Tua

    // Set notifikasi sukses untuk ditampilkan di dashboard
    $_SESSION['swal_sukses'] = "Selamat Datang, Bapak/Ibu Wali dari " . $data['nama_siswa'] . "!";

    // SANGAT PENTING: Pastikan menuliskan .php dengan lengkap agar session terbaca sempurna
    header("location:dashboard_ortu");
    exit();
} else {
    // Set notifikasi gagal untuk ditampilkan kembali di login_ortu.php
    $_SESSION['swal_gagal'] = "Nomor Induk Siswa (NIS) tidak ditemukan di database.";

    header("location:login_ortu");
    exit();
}
?>