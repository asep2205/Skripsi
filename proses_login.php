<?php 
// Mengaktifkan session PHP
session_start();

// Menghubungkan dengan koneksi database
include 'koneksi.php';

// Menangkap data yang dikirim dari form login
$username = mysqli_real_escape_string($koneksi, $_POST['username']);
$password = md5($_POST['password']);// HATI-HATI COY: Tadi md5()-nya kelupaan ditulis, ini sudah saya tambahkan

// Menyeleksi data user dengan username dan password yang sesuai
$login = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username' AND password='$password'");

// Menghitung jumlah data yang ditemukan
$cek = mysqli_num_rows($login);

if($cek > 0){
    $data = mysqli_fetch_assoc($login);
    
    // Menyimpan data ke dalam session
    $_SESSION['id_user'] = $data['id_user'];
    $_SESSION['username'] = $data['username'];
    $_SESSION['nama_lengkap'] = $data['nama_lengkap'];
    $_SESSION['role'] = $data['role'];
    $_SESSION['status'] = "login";
    
    // NOTIFIKASI BERHASIL LOGIN (Menggunakan SweetAlert2 Premium)
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login Proses...</title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Poppins', sans-serif;
                background-color: #f0f4f8;
            }
            /* Menyesuaikan ukuran alert di mobile agar tidak kebesaran */
            @media (max-width: 576px) {
                .swal2-popup { width: 85% !important; border-radius: 20px !important; }
            }
        </style>
    </head>
    <body>
        <script>
            Swal.fire({
                title: 'Login Berhasil!',
                text: 'Selamat datang kembali, <?php echo explode(' ', trim($data['nama_lengkap']))[0]; ?>.',
                icon: 'success',
                timer: 2000, // Notifikasi muncul selama 2 detik (2000ms)
                showConfirmButton: false,
                timerProgressBar: true, // Garis loading di bawah alert
                willClose: () => {
                    // Setelah alert tertutup otomatis, langsung tendang ke dashboard
                    window.location.href = 'dashboard';
                }
            });
        </script>
    </body>
    </html>
    <?php
    exit();

} else {
    // Alihkan kembali ke halaman login dengan pesan gagal
    header("location:index.php?pesan=gagal");
    exit();
}
?>