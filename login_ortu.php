<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Orang Tua - Monitoring Perilaku Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            /* PERUBAHAN: Mengubah gradasi background menjadi Biru Modern */
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            width: 100%;
            padding: 15px;
        }
        .login-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease;
        }
        .brand-icon {
            width: 70px;
            height: 70px;
            /* PERUBAHAN: Mengubah gradasi warna icon menjadi Biru Cerah ke Biru Tua */
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin: 0 auto 15px auto;
            /* PERUBAHAN: Shadow disesuaikan dengan warna biru */
            box-shadow: 0 8px 16px rgba(29, 78, 216, 0.3);
        }
        .input-group-text {
            background-color: transparent;
            border-right: none;
            color: #6c757d;
            border-radius: 12px 0 0 12px;
        }
        .form-control {
            border-left: none;
            border-radius: 0 12px 12px 0;
            padding: 12px;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #dee2e6;
        }
        .input-group:focus-within .input-group-text,
        .input-group:focus-within .form-control {
            /* PERUBAHAN: Border focus menjadi Biru saat input aktif */
            border-color: #2563eb;
            background-color: #fff;
        }
        .btn-login {
            /* PERUBAHAN: Tombol diubah menjadi gradasi Biru Utama */
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

<?php session_start(); // Memastikan session aktif untuk membaca flash message ?>

<div class="login-container">
    <div class="row justify-content-center m-0">
        <div class="col-12 col-sm-8 col-md-6 col-lg-4 p-0">
            <div class="card login-card p-3 p-sm-4">
                <div class="card-body">
                    
                    <div class="text-center mb-4">
                        <div class="brand-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <h3 class="fw-bold text-dark mb-1" style="font-size: 24px;">Portal Orang Tua</h3>
                        <p class="text-muted small">Monitoring Perkembangan Poin Siswa</p>
                    </div>
                    
                    <form action="proses_login_ortu.php" method="POST">
                        <div class="mb-4">
                            <label for="nis" class="form-label text-secondary small fw-semibold">Nomor Induk Siswa (NIS)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                <input type="text" class="form-control" id="nis" name="nis" placeholder="Masukkan NIS Kandung Siswa" required autofocus>
                            </div>
                            <div class="form-text text-muted small mt-1" style="font-size: 11px;">
                                *Silakan minta nomor NIS ke pihak kurikulum/wali kelas jika belum tahu.
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-login w-100 text-white shadow-sm mb-3">
                            Periksa Grafik & Poin Siswa <i class="bi bi-search ms-1"></i>
                        </button>

                        </form>
                    
                </div>
            </div>
            
            <div class="text-center mt-3 text-white-50 small">
                &copy; 2026 SMKS Doa Bangsa. All Rights Reserved.
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    <?php if(isset($_SESSION['swal_gagal'])): ?>
        Swal.fire({
            icon: 'error',
            title: 'Login Gagal!',
            text: '<?= $_SESSION['swal_gagal']; ?>',
            confirmButtonColor: '#2563eb', /* PERUBAHAN: Tombol konfirmasi alert disesuaikan jadi Biru */
            confirmButtonText: 'Coba Lagi'
        });
    <?php unset($_SESSION['swal_gagal']); endif; ?>

    <?php 
    // Pendeteksi jika ortu dialihkan ke sini melalui tautan logout bawaan sistem lama
    if(isset($_GET['pesan']) && $_GET['pesan'] == "logout"): 
    ?>
        Swal.fire({
            icon: 'success',
            title: 'Keluar Sistem',
            text: 'Anda telah berhasil keluar dari portal monitoring.',
            confirmButtonColor: '#2563eb' /* PERUBAHAN: Tombol konfirmasi alert disesuaikan jadi Biru */
        });
    <?php endif; ?>
});
</script>
</body>
</html>