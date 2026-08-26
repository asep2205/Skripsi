<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Klasifikasi Perilaku Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            /* Background gradient modern kombinasi biru sekolah dan ungu cerah */
            background: linear-gradient(135deg, #0d6efd 0%, #4f46e5 100%);
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
        /* Efek logo sekolah di atas form */
        .brand-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #6366f1, #0d6efd);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin: 0 auto 15px auto;
            box-shadow: 0 8px 16px rgba(13, 110, 253, 0.3);
        }
        /* Custom input group agar ikon menyatu dengan rapi */
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
            border-color: #0d6efd;
            background-color: #fff;
        }
        .btn-login {
            background: linear-gradient(135deg, #0d6efd 0%, #4f46e5 100%);
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

<div class="login-container">
    <div class="row justify-content-center m-0">
        <div class="col-12 col-sm-8 col-md-6 col-lg-4 p-0">
            <div class="card login-card p-3 p-sm-4">
                <div class="card-body">
                    
                    <div class="text-center mb-4">
                        <div class="brand-icon">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                        <h3 class="fw-bold text-dark mb-1" style="font-size: 24px;">SMKS Doa Bangsa</h3>
                        <p class="text-muted small">Sistem Klasifikasi Perilaku Siswa (NLP)</p>
                    </div>
                    
                    <?php 
                    if(isset($_GET['pesan'])){
                        if($_GET['pesan'] == "gagal"){
                            echo "<div class='alert alert-danger text-center small rounded-3 py-2 mb-3'><i class='bi bi-exclamation-triangle-fill me-2'></i>Username / Password salah!</div>";
                        } else if($_GET['pesan'] == "logout"){
                            echo "<div class='alert alert-success text-center small rounded-3 py-2 mb-3'><i class='bi bi-check-circle-fill me-2'></i>Anda berhasil keluar.</div>";
                        }
                    }
                    ?>

                    <form action="proses_login.php" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label text-secondary small fw-semibold">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" required autofocus>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label text-secondary small fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-login w-100 text-white shadow-sm">
                            Masuk ke Sistem <i class="bi bi-arrow-right-short ms-1"></i>
                        </button>
                    </form>
                    
                </div>
            </div>
            
            <!-- <div class="text-center mt-3 text-white-50 small">
                &copy; 2026 SMKS Doa Bangsa. All Rights Reserved.
            </div> -->
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>