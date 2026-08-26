<?php
session_start();
// Proteksi halaman: Jika belum login, kembalikan ke halaman login
if($_SESSION['status'] != "login"){
    header("location:index.php");
    exit();
}

// Ambil data session untuk digunakan di tampilan
$nama_user = $_SESSION['nama_lengkap'];
$role_user = $_SESSION['role']; // Mengambil role: Admin, Guru, BK, atau Kepsek
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SMKS DB - Dashboard </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #f0f4f8; 
            font-family: 'Poppins', sans-serif; 
            -webkit-tap-highlight-color: transparent; 
        }

        /* --- HEADER SECTION MINIMALIS & MODERN --- */
        .header-premium {
            background: linear-gradient(135deg, #1e3a8a 0%, #4f46e5 100%);
            color: white;
            border-radius: 0 0 30px 30px; 
            padding: 40px 20px 80px 20px; 
            position: relative;
            box-shadow: 0 10px 30px rgba(79, 70, 229, 0.2);
        }

        .badge-premium-role {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            border: 1px solid rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #e0e7ff;
        }

        /* Menambahkan efek kursor pointer & hover pada Avatar untuk memicu Edit Profil */
        .user-avatar-pills {
            width: 45px;
            height: 45px;
            background-color: rgba(255,255,255,0.2);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 20px;
            border: 2px solid rgba(255,255,255,0.4);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .user-avatar-pills:hover {
            transform: scale(1.08);
            background-color: rgba(255,255,255,0.3);
            border-color: rgba(255,255,255,0.7);
        }

        /* --- MAIN CONTENT SECTION --- */
        .content-container {
            margin-top: -50px; 
            position: relative;
            z-index: 10;
        }

        /* --- CARD MENU --- */
        .premium-menu-card {
            border: 1px solid rgba(0,0,0,0.03); 
            border-radius: 25px; 
            background: #ffffff;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05), 0 4px 6px -2px rgba(0,0,0,0.03);
            overflow: hidden;
            position: relative;
        }

        .premium-menu-card:active {
            transform: scale(0.96); 
            background-color: #f8fafc;
        }

        @media (min-width: 992px) {
            .premium-menu-card:hover {
                transform: translateY(-8px);
                box-shadow: 0 20px 25px -5px rgba(13, 110, 253, 0.1), 0 10px 10px -5px rgba(13, 110, 253, 0.04);
            }
        }

        .icon-premium-box {
            width: 65px;
            height: 65px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin: 0 auto 15px auto; 
        }

        .menu-title-premium {
            font-weight: 700;
            color: #1e293b;
            font-size: 15px;
            margin-bottom: 4px;
        }

        @media (max-width: 576px) {
            .premium-menu-card {
                padding: 20px !important; 
            }
            .icon-premium-box {
                width: 55px; 
                height: 55px;
                font-size: 24px;
            }
        }
        
        .btn-float-logout {
            background: rgba(255,255,255,0.1);
            color: #fca5a5;
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 12px;
            padding: 8px 12px;
            transition: all 0.2s;
        }
        .btn-float-logout:active {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }
    </style>
</head>
<body>

<div class="container-fluid p-0">
    <div class="header-premium">
        <div class="container px-4">
            <div class="d-flex justify-content-between align-items-center">
                
                <div class="d-flex align-items-center gap-3">
                    <div class="user-avatar-pills shadow-sm" data-bs-toggle="modal" data-bs-target="#modalUpdateProfil" title="Klik untuk edit profil">
                        <?php echo strtoupper(substr($nama_user, 0, 1)); ?>
                    </div>
                    <div>
                        <span class="badge-premium-role mb-2 d-inline-block" data-bs-toggle="modal" data-bs-target="#modalUpdateProfil" style="cursor:pointer;">
                            <?php echo $role_user; ?> Panel <i class="bi bi-pencil-square ms-1" style="font-size: 10px;"></i>
                        </span>
                        <h2 class="fw-bold mb-0 text-white" style="font-size: 20px; letter-spacing: -0.5px;"> Halo, <?php echo htmlspecialchars($nama_user); ?>!</h2>
                    </div>
                </div>

                <a href="logout.php" class="btn btn-float-logout" id="btn-logout">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container content-container px-4">
    
    <div class="row mb-4 px-2">
        <div class="col-12 text-center text-sm-start">
            <div class="d-inline-block px-4 py-3 w-100 text-start" style="background: #ffffff; border-radius: 16px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); border-left: 5px solid #4f46e5;">
                <h5 class="fw-bold mb-1" style="color: #0f172a; font-size: 16px; letter-spacing: 0.3px;">
                    <i class="bi bi-journal-text me-1 text-primary"></i> SISTEM KLASIFIKASI PERILAKU SISWA MENGGUNAKAN TEXT UNTUK PENENTUAN REWARD DAN PUNISHMENT </h5>
                <small style="color: #475569; font-weight: 500; font-size: 12px;">
                    Silahkan pilih menu pemrosesan di bawah ini sesuai hak akses Anda.
                </small>
            </div>
        </div>
    </div>

    <div class="row g-3 g-sm-4">
        
        <?php if(in_array($role_user, ['Admin', 'Guru', 'BK'])): ?>
        <div class="col-6 col-sm-6 col-md-4 col-lg-3">
            <div class="card premium-menu-card h-100 p-3 text-center">
                <div class="card-body p-0 d-flex flex-column align-items-center">
                    <div class="icon-premium-box bg-primary-subtle text-primary shadow-inner">
                        <i class="bi bi-layers-half"></i>
                    </div>
                    <h6 class="menu-title-premium">Input Laporan</h6>
                    <a href="input_laporan" class="stretched-link"></a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if($role_user == 'Admin'): ?>
        <div class="col-6 col-sm-6 col-md-4 col-lg-3">
            <div class="card premium-menu-card h-100 p-3 text-center">
                <div class="card-body p-0 d-flex flex-column align-items-center">
                    <div class="icon-premium-box bg-danger-subtle text-danger shadow-inner">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h6 class="menu-title-premium">Manajemen User</h6>
                    <a href="manajemen_user" class="stretched-link"></a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if(in_array($role_user, ['Admin'])): ?>
        <div class="col-6 col-sm-6 col-md-4 col-lg-3">
            <div class="card premium-menu-card h-100 p-3 text-center">
                <div class="card-body p-0 d-flex flex-column align-items-center">
                    <div class="icon-premium-box bg-success-subtle text-success shadow-inner">
                        <i class="bi bi-mortarboard"></i>
                    </div>
                    <h6 class="menu-title-premium">Data Siswa</h6>
                    <a href="data_siswa" class="stretched-link"></a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if(in_array($role_user, ['Admin'])): ?>
        <div class="col-6 col-sm-6 col-md-4 col-lg-3">
            <div class="card premium-menu-card h-100 p-3 text-center">
                <div class="card-body p-0 d-flex flex-column align-items-center">
                    <div class="icon-premium-box bg-success-subtle text-success shadow-inner">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <h6 class="menu-title-premium">Data Evaluasi Siswa</h6>
                    <a href="evaluasi_siswa" class="stretched-link"></a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if(in_array($role_user, ['Admin', 'BK'])): ?>
        <div class="col-6 col-sm-6 col-md-4 col-lg-3">
            <div class="card premium-menu-card h-100 p-3 text-center">
                <div class="card-body p-0 d-flex flex-column align-items-center">
                    <div class="icon-premium-box bg-success-subtle text-success shadow-inner">
                        <i class="bi bi-heart-arrow"></i>
                    </div>
                    <h6 class="menu-title-premium">Remisi Poin</h6>
                    <a href="remisi_poin" class="stretched-link"></a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if(in_array($role_user, ['Admin'])): ?>
        <div class="col-6 col-sm-6 col-md-4 col-lg-3">
            <div class="card premium-menu-card h-100 p-3 text-center">
                <div class="card-body p-0 d-flex flex-column align-items-center">
                    <div class="icon-premium-box bg-warning-subtle text-warning shadow-inner">
                        <i class="bi bi-hdd-network"></i>
                    </div>
                    <h6 class="menu-title-premium">Master Poin</h6>
                    <a href="master_poin" class="stretched-link"></a>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if(in_array($role_user, ['Admin','BK', 'Kepsek'])): ?>
        <div class="col-6 col-sm-6 col-md-4 col-lg-3">
            <div class="card premium-menu-card h-100 p-3 text-center">
                <div class="card-body p-0 d-flex flex-column align-items-center">
                    <div class="icon-premium-box bg-danger-subtle text-danger shadow-inner" style="background-color: #f3e8ff !important; color: #7c3aed !important;">
                        <i class="bi bi-trophy"></i>
                    </div>
                    <h6 class="menu-title-premium">Reward & Punishment</h6>
                    <a href="reward_punishment" class="stretched-link"></a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="col-6 col-sm-6 col-md-4 col-lg-3">
            <div class="card premium-menu-card h-100 p-3 text-center">
                <div class="card-body p-0 d-flex flex-column align-items-center">
                    <div class="icon-premium-box bg-info-subtle text-info shadow-inner">
                        <i class="bi bi-clipboard-pulse"></i>
                    </div>
                    <h6 class="menu-title-premium">Riwayat Laporan</h6>
                    <a href="riwayat_log.php" class="stretched-link"></a>
                </div>
            </div>
        </div>

    </div>

</div>

<div class="modal fade" id="modalUpdateProfil" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalUpdateProfilLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 20px;">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-dark" id="modalUpdateProfilLabel"><i class="bi bi-person-gear text-primary me-2"></i>Pengaturan Profil</h5>
                <button type="button" class="btn-close" data-bs-submit="modal" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formUpdateProfil" method="POST" action="proses_update_profil.php">
                <div class="modal-body px-4 pb-4 pt-2">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Nama Lengkap</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                            <input type="text" name="nama_lengkap" class="form-control bg-light border-start-0" value="<?php echo htmlspecialchars($nama_user); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Kata Sandi Baru</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password_baru" class="form-control bg-light border-start-0" placeholder="Kosongkan jika tidak diganti">
                        </div>
                        <div class="form-text" style="font-size: 11px;">Isi kolom kata sandi hanya jika Anda berniat untuk mengubahnya.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0 d-flex gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold"><i class="bi bi-check-circle me-1"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Konfirmasi Logout Modern
    document.getElementById('btn-logout').addEventListener('click', function(event) {
        event.preventDefault(); 
        const logoutUrl = this.getAttribute('href');

        Swal.fire({
            title: 'Yakin ingin keluar?',
            text: 'Sesi aman Anda akan berakhir setelah Anda menekan tombol keluar.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444', 
            cancelButtonColor: '#6c757d',  
            confirmButtonText: '<i class="bi bi-box-arrow-right"></i> Keluar',
            cancelButtonText: 'Batal',
            reverseButtons: true 
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "logout.php";
            }
        });
    });

    // Menangani respons pengolahan data form update profil via AJAX agar UX tetap smooth
    document.getElementById('formUpdateProfil').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('proses_update_profil.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    title: 'Berhasil!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonColor: '#4f46e5'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Gagal!',
                    text: data.message,
                    icon: 'error',
                    confirmButtonColor: '#ef4444'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'Error!',
                text: 'Terjadi gangguan sistem. Silakan coba kembali.',
                icon: 'error',
                confirmButtonColor: '#ef4444'
            });
        });
    });
</script>
</body>
</html>