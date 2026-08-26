    <?php
    session_start();
    // Proteksi halaman: Jika belum login, kembalikan ke halaman login
    if($_SESSION['status'] != "login"){
        header("location:index.php");
        exit();
    }

    include 'koneksi.php';

    // Menangkap filter kustom role jika ingin dikombinasikan dengan DataTables (opsional)
    $filter_role = isset($_GET['role']) ? mysqli_real_escape_string($koneksi, $_GET['role']) : '';

    // Query dasar (Pencarian teks global akan ditangani langsung secara instan oleh fitur bawaan DataTables)
    $query = "SELECT * FROM users WHERE 1=1";
    if ($filter_role != '') { 
        $query .= " AND role = '$filter_role'"; 
    }
    $query .= " ORDER BY nama_lengkap ASC";

    $result = mysqli_query($koneksi, $query);
    ?>

    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <title>Manajemen User - SMKS DB</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
        
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
        
        <style>
            body { background-color: #f0f4f8; font-family: 'Poppins', sans-serif; -webkit-tap-highlight-color: transparent; }
            .mobile-header { background: linear-gradient(135deg, #1e3a8a 0%, #4f46e5 100%); color: white; padding: 15px 20px; position: sticky; top: 0; z-index: 100; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
            .btn-back { color: white; font-size: 22px; text-decoration: none; }
            
            /* Desain Card List Khusus Mobile */
            .user-mobile-card { background: white; border-radius: 18px; border: none; box-shadow: 0 4px 10px rgba(0,0,0,0.03); margin-bottom: 12px; padding: 16px; transition: transform 0.2s ease; }
            .badge-role-premium { padding: 5px 10px; border-radius: 8px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
            .action-btn-group { border-top: 1px dashed #e2e8f0; margin-top: 12px; padding-top: 12px; display: flex; justify-content: flex-end; gap: 8px; }
            .modal-content { border-radius: 20px; border: none; }
            
            /* Custom styling agar DataTables serasi dengan tema aplikasi */
            .dataTables_wrapper .dataTables_paginate .paginate_button { padding: 0px !important; margin-left: 0px !important; border: none !important; }
            .table th { font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
            .table td { font-size: 14px; }
            
            @media (max-width: 767.98px) { 
                .desktop-view { display: none !important; }
                .desktop-add-btn { display: none !important; }
                
                .mobile-fab-container {
                    position: fixed;
                    bottom: 20px;
                    left: 0;
                    right: 0;
                    padding: 0 20px;
                    z-index: 1050;
                }
                .mobile-fab-btn {
                    width: 100%;
                    background: linear-gradient(135deg, #1e3a8a 0%, #4f46e5 100%);
                    color: white !important;
                    padding: 14px;
                    font-weight: 600;
                    border-radius: 16px;
                    box-shadow: 0 8px 24px rgba(79, 70, 229, 0.35);
                    border: none;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 8px;
                    font-size: 16px;
                    transition: all 0.2s ease;
                }
                .mobile-fab-btn:active { transform: scale(0.98); opacity: 0.95; }
                .user-container { padding-bottom: 90px; }
                
                /* Sembunyikan kontrol bawaan DataTable yang berantakan di mobile */
                .dataTables_length, .dataTables_info { display: none !important; }
                .dataTables_filter { width: 100% !important; text-align: left !important; margin-bottom: 15px; }
                .dataTables_filter input { width: 100% !important; margin-left: 0 !important; border-radius: 10px; padding: 8px 12px; border: 1px solid #ced4da !important; }
                .dataTables_paginate { display: flex; justify-content: center; margin-top: 15px; }
            }
            
            @media (min-width: 768px) { 
                .mobile-view { display: none !important; }
                .mobile-fab-container { display: none !important; }
                .user-container { margin-top: 30px; } 
            }
        </style>
    </head>
    <body>

    <div class="mobile-header">
        <div class="container d-flex align-items-center justify-content-between p-0">
            <div class="d-flex align-items-center gap-3">
                <a href="dashboard" class="btn-back"><i class="bi bi-arrow-left"></i></a>
                <h5 class="fw-bold mb-0" style="font-size: 18px;">Manajemen User</h5>
            </div>
            <button class="btn btn-light btn-sm fw-bold rounded-pill px-3 desktop-add-btn" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-lg me-1"></i> Tambah User
            </button>
        </div>
    </div>

    <div class="mobile-fab-container">
        <button class="mobile-fab-btn" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-person-plus-fill" style="font-size: 18px;"></i> Tambah User Baru
        </button>
    </div>

    <div class="container user-container px-3 mt-3">

        <?php if(isset($_GET['pesan'])): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-3 small py-2 mb-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?php 
                    if($_GET['pesan'] == "tambah_sukses") echo "User baru berhasil didaftarkan ke sistem!";
                    if($_GET['pesan'] == "edit_sukses") echo "Perubahan data akun user berhasil disimpan!";
                    if($_GET['pesan'] == "hapus_sukses") echo "Akun pengguna telah berhasil dihapus permanen!";
                    if($_GET['pesan'] == "gagal") echo "Terjadi kesalahan database, operasi gagal.";
                ?>
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card p-3 border-0 shadow-sm mb-4 bg-white" style="border-radius: 16px;">
            <form method="GET" action="" id="filterForm">
                <div class="row align-items-center g-2">
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold text-secondary mb-1">Filter Berdasarkan Jabatan/Role</label>
                        <div class="input-group input-group-sm rounded overflow-hidden border">
                            <span class="input-group-text bg-white border-0"><i class="bi bi-funnel text-muted"></i></span>
                            <select name="role" class="form-select form-select-sm bg-white border-0 fw-semibold text-secondary py-2" onchange="document.getElementById('filterForm').submit();">
                                <option value="">Semua Hak Akses (Role)</option>
                                <option value="Admin" <?php if($filter_role == 'Admin') echo 'selected'; ?>>Admin</option>
                                <option value="Guru" <?php if($filter_role == 'Guru') echo 'selected'; ?>>Guru</option>
                                <option value="BK" <?php if($filter_role == 'BK') echo 'selected'; ?>>BK</option>
                                <option value="Kepsek" <?php if($filter_role == 'Kepsek') echo 'selected'; ?>>Kepsek</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <table id="tabelUser" class="table table-borderless d-block" style="width:100%">
            <thead class="d-none">
                <tr>
                    <th>Data</th> </tr>
            </thead>
            <tbody class="w-100 d-block">
                <?php while($row = mysqli_fetch_assoc($result)) { ?>
                <tr class="d-block w-100">
                    <td class="p-0 d-block w-100">
                        
                        <div class="mobile-view user-mobile-card">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted small fw-semibold">@<?php echo htmlspecialchars($row['username']); ?></span>
                                    <h6 class="fw-bold text-dark mb-1 mt-1" style="font-size: 15px;"><?php echo htmlspecialchars($row['nama_lengkap']); ?></h6>
                                </div>
                                <div>
                                    <?php 
                                    $badge_class = "bg-primary-subtle text-primary";
                                    if(strtolower($row['role']) == 'admin') $badge_class = "bg-danger-subtle text-danger";
                                    if(strtolower($row['role']) == 'bk') $badge_class = "bg-warning-subtle text-warning";
                                    if(strtolower($row['role']) == 'kepsek') $badge_class = "bg-info-subtle text-info";
                                    ?>
                                    <span class="badge-role-premium <?php echo $badge_class; ?>">
                                        <?php echo htmlspecialchars($row['role']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="action-btn-group">
                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1" data-bs-toggle="modal" data-bs-target="#modalEdit" data-id="<?php echo $row['id_user']; ?>" data-username="<?php echo htmlspecialchars($row['username'], ENT_QUOTES); ?>" data-nama="<?php echo htmlspecialchars($row['nama_lengkap'], ENT_QUOTES); ?>" data-role="<?php echo $row['role']; ?>">
                                    <i class="bi bi-pencil-square me-1"></i> Edit
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 btn-hapus" data-href="proses_user.php?hapus=<?php echo $row['id_user']; ?>" data-nama="<?php echo htmlspecialchars($row['nama_lengkap'], ENT_QUOTES); ?>">
                                    <i class="bi bi-trash3 me-1"></i> Hapus
                                </button>
                            </div>
                        </div>

                        <div class="desktop-view card border-0 shadow-sm p-3 mb-2" style="border-radius: 14px;">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <span class="text-secondary small d-block">Username</span>
                                    <span class="fw-semibold text-dark">@<?php echo htmlspecialchars($row['username']); ?></span>
                                </div>
                                <div class="col-md-5">
                                    <span class="text-secondary small d-block">Nama Lengkap User</span>
                                    <span class="fw-bold text-dark"><?php echo htmlspecialchars($row['nama_lengkap']); ?></span>
                                </div>
                                <div class="col-md-2">
                                    <span class="text-secondary small d-block mb-1">Hak Akses</span>
                                    <?php 
                                    $badge_class = "bg-primary-subtle text-primary";
                                    if(strtolower($row['role']) == 'admin') $badge_class = "bg-danger-subtle text-danger";
                                    if(strtolower($row['role']) == 'bk') $badge_class = "bg-warning-subtle text-warning";
                                    if(strtolower($row['role']) == 'kepsek') $badge_class = "bg-info-subtle text-info";
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?> rounded px-2.5 py-1 fw-bold text-uppercase" style="font-size: 11px;">
                                        <?php echo htmlspecialchars($row['role']); ?>
                                    </span>
                                </div>
                                <div class="col-md-2 text-end">
                                    <button class="btn btn-sm btn-primary me-1" data-bs-toggle="modal" data-bs-target="#modalEdit" data-id="<?php echo $row['id_user']; ?>" data-username="<?php echo htmlspecialchars($row['username'], ENT_QUOTES); ?>" data-nama="<?php echo htmlspecialchars($row['nama_lengkap'], ENT_QUOTES); ?>" data-role="<?php echo $row['role']; ?>"><i class="bi bi-pencil-square"></i></button>
                                    <button type="button" class="btn btn-sm btn-danger btn-hapus" data-href="proses_user.php?hapus=<?php echo $row['id_user']; ?>" data-nama="<?php echo htmlspecialchars($row['nama_lengkap'], ENT_QUOTES); ?>">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-person-plus me-2 text-primary"></i>Tambah Pengguna Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="proses_user.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Username</label>
                            <input type="text" name="username" class="form-control" required placeholder="Contoh: pak_budi" autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Password Akses</label>
                            <input type="password" name="password" class="form-control" required placeholder="Masukkan password akun baru">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Nama Lengkap Pengguna</label>
                            <input type="text" name="nama_lengkap" class="form-control" required placeholder="Masukkan nama dan gelar">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold text-secondary">Role Sistem</label>
                            <select name="role" class="form-select" required>
                                <option value="Admin">Admin</option>
                                <option value="Guru">Guru</option>
                                <option value="BK">BK</option>
                                <option value="Kepsek">Kepsek</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="aksi_tambah" class="btn btn-primary rounded-pill px-4">Daftarkan Akun</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEdit" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square me-2 text-warning"></i>Ubah Informasi Akun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="proses_user.php" method="POST">
                    <input type="hidden" name="id_user" id="edit-id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Username</label>
                            <input type="text" name="username" id="edit-username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Password <small class="text-danger">(Isi jika ingin ganti password baru)</small></label>
                            <input type="password" name="password" id="edit-password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" id="edit-nama" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold text-secondary">Role Sistem</label>
                            <select name="role" id="edit-role" class="form-select" required>
                                <option value="Admin">Admin</option>
                                <option value="Guru">Guru</option>
                                <option value="BK">BK</option>
                                <option value="Kepsek">Kepsek</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="aksi_edit" class="btn btn-primary rounded-pill px-4">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inisialisasi DataTables dengan Optimasi Pencarian & Pagination untuk Card List
            $('#tabelUser').DataTable({
                "ordering": false, // Mengikuti bawaan query MySQL saja agar performa render card stabil
                "pageLength": 10,  
                "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Semua"]],
                "language": {
                    "sProcessing":   "Sedang memproses...",
                    "sLengthMenu":   "Tampilkan _MENU_ data",
                    "sZeroRecords":  "Tidak ditemukan data yang sesuai",
                    "sInfo":         "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "sInfoEmpty":    "Menampilkan 0 sampai 0 dari 0 data",
                    "sSearch":       "", // Hilangkan teks label "Search" bawaan agar bersih
                    "searchPlaceholder": "Cari nama atau username...", // Input modern placeholder
                    "oPaginate": {
                        "sFirst":    "Pertama",
                        "sPrevious": "Sebelumnya",
                        "sNext":     "Selanjutnya",
                        "sLast":     "Terakhir"
                    }
                }
            });

            // Distribusi data ke Element di dalam Modal Edit saat tombol diklik
            const modalEdit = document.getElementById('modalEdit');
            modalEdit.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                
                const id = button.getAttribute('data-id');
                const username = button.getAttribute('data-username');
                const nama = button.getAttribute('data-nama');
                const role = button.getAttribute('data-role');

                modalEdit.querySelector('#edit-id').value = id;
                modalEdit.querySelector('#edit-username').value = username;
                modalEdit.querySelector('#edit-nama').value = nama;
                modalEdit.querySelector('#edit-role').value = role;
                modalEdit.querySelector('#edit-password').value = ""; 
            });

            // SweetAlert2 Engine untuk proteksi konfirmasi Hapus data user (Delegasi Event)
            $(document).on('click', '.btn-hapus', function() {
                const urlTarget = this.getAttribute('data-href');
                const namaUser = this.getAttribute('data-nama');

                Swal.fire({
                    title: 'Hapus Akun Pengguna?',
                    text: 'Akun milik "' + namaUser + '" akan terhapus total dan tidak bisa digunakan untuk masuk ke sistem lagi.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545', 
                    cancelButtonColor: '#6c757d',  
                    confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true 
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = urlTarget;
                    }
                });
            });
        });
    </script>
    </body>
    </html>