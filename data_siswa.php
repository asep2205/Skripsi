<?php
session_start();
if($_SESSION['status'] != "login"){
    header("location:index.php");
    exit();
}

include 'koneksi.php';

// Filter Kelas
$filter_kelas = isset($_GET['kelas']) ? mysqli_real_escape_string($koneksi, $_GET['kelas']) : '';

$query = "SELECT * FROM siswa WHERE 1=1";
if ($filter_kelas != '') { 
    $query .= " AND kelas = '$filter_kelas'"; 
}
$query .= " ORDER BY nama_siswa ASC";

$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Data Siswa - SMKS DB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    
    <style>
        html, body { 
            background-color: #f0f4f8; 
            font-family: 'Poppins', sans-serif; 
            -webkit-tap-highlight-color: transparent;
            overflow-x: hidden;
            width: 100%;
        }
        
        .mobile-header { background: linear-gradient(135deg, #1e3a8a 0%, #4f46e5 100%); color: white; padding: 15px 20px; position: sticky; top: 0; z-index: 100; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .btn-back { color: white; font-size: 22px; text-decoration: none; }
        
        /* Desain Card List HP */
        .student-mobile-card { background: white; border-radius: 18px; border: none; box-shadow: 0 4px 10px rgba(0,0,0,0.03); margin-bottom: 12px; padding: 16px; width: 100%; }
        .badge-poin { padding: 5px 10px; border-radius: 8px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
        .action-btn-group { border-top: 1px dashed #e2e8f0; margin-top: 12px; padding-top: 12px; display: flex; justify-content: flex-end; gap: 8px; }
        .modal-content { border-radius: 20px; border: none; }
        
        /* CSS Kunci DataTables Pas Layar HP */
        #tabelSiswa {
            table-layout: fixed !important;
            width: 100% !important;
            border-collapse: collapse !important;
        }
        #tabelSiswa td {
            word-wrap: break-word;
            white-space: normal;
        }
        
        /* Sembunyikan pencarian asli DataTables agar tidak double */
        .dataTables_wrapper .dataTables_filter { display: none !important; }
        .dataTables_wrapper .dataTables_length, .dataTables_info { display: none !important; }
        .dataTables_wrapper .dataTables_paginate .paginate_button { padding: 0px !important; margin-left: 0px !important; border: none !important; }
        
        /* Styling Custom Search Bar yang dipindahkan */
        .custom-search-container input {
            border: none !important;
            box-shadow: none !important;
            padding: 8px 12px;
            font-size: 14px;
            width: 100%;
        }
        .custom-search-container input:focus {
            outline: none;
        }
        
        @media (max-width: 767.98px) { 
            .desktop-view { display: none !important; }
            .desktop-add-btn { display: none !important; }
            
            .student-container { 
                padding-bottom: 95px; 
                width: 100% !important; 
                max-width: 100% !important;
                overflow-x: hidden;
            }
            
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
            }
            .dataTables_paginate { display: flex; justify-content: center; margin-top: 15px; }
        }
        
        @media (min-width: 768px) { 
            .mobile-view { display: none !important; } 
            .mobile-fab-container { display: none !important; }
            .student-container { margin-top: 30px; } 
            #tabelSiswa { table-layout: auto !important; }
        }
    </style>
</head>
<body>

<div class="mobile-header">
    <div class="container d-flex align-items-center justify-content-between p-0">
        <div class="d-flex align-items-center gap-3">
            <a href="dashboard" class="btn-back"><i class="bi bi-arrow-left"></i></a>
            <h5 class="fw-bold mb-0" style="font-size: 18px;">Data Siswa</h5>
        </div>
        <button class="btn btn-light btn-sm fw-bold rounded-pill px-3 desktop-add-btn" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-plus-lg me-1"></i> Tambah Siswa
        </button>
    </div>
</div>

<div class="mobile-fab-container">
    <button class="mobile-fab-btn" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="bi bi-plus-circle-fill" style="font-size: 18px;"></i> Tambah Siswa Baru
    </button>
</div>

<div class="container student-container px-3 mt-3">

    <?php if(isset($_GET['pesan'])): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 small py-2 mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?php 
                if($_GET['pesan'] == "tambah_sukses") echo "Data siswa berhasil ditambahkan!";
                if($_GET['pesan'] == "edit_sukses") echo "Perubahan data siswa berhasil disimpan!";
                if($_GET['pesan'] == "hapus_sukses") echo "Data siswa telah berhasil dihapus!";
                if($_GET['pesan'] == "gagal") echo "Terjadi kesalahan sistem, operasi gagal.";
            ?>
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card p-3 border-0 shadow-sm mb-3 bg-white" style="border-radius: 16px;">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-8">
                <label class="form-label small fw-semibold text-secondary mb-1">Cari Siswa</label>
                <div class="input-group input-group-sm rounded overflow-hidden border custom-search-container">
                    <span class="input-group-text bg-white border-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="pencarianSiswa" class="form-control" placeholder="Ketik nama atau NIS siswa...">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <form method="GET" action="" id="filterForm">
                    <label class="form-label small fw-semibold text-secondary mb-1">Filter Tingkat Kelas</label>
                    <div class="input-group input-group-sm rounded overflow-hidden border">
                        <span class="input-group-text bg-white border-0"><i class="bi bi-funnel text-muted"></i></span>
                        <select name="kelas" class="form-select form-select-sm bg-white border-0 fw-semibold text-secondary py-2" onchange="document.getElementById('filterForm').submit();">
                            <option value="">Semua Kelas</option>
                            <?php
                            $query_kelas = mysqli_query($koneksi, "SELECT DISTINCT kelas FROM siswa ORDER BY kelas ASC");
                            while($k = mysqli_fetch_assoc($query_kelas)) {
                                $nama_kelas = $k['kelas'];
                                $selected = ($filter_kelas == $nama_kelas) ? 'selected' : '';
                                echo "<option value='$nama_kelas' $selected>$nama_kelas</option>";
                            }
                            ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <table id="tabelSiswa" class="table table-borderless table-responsive w-100 m-0 p-0" style="display: table !important;">
        <thead class="d-none">
            <tr>
                <th>Konten</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td class="p-0 border-0">

                    <div class="mobile-view student-mobile-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div style="max-width: 65%;">
                                <span class="text-muted small fw-semibold">NIS <?php echo $row['nis']; ?></span>
                                <h6 class="fw-bold text-dark mb-1 mt-1 text-truncate" style="font-size: 15px;"><?php echo htmlspecialchars($row['nama_siswa']); ?></h6>
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1" style="font-size: 11px;"><?php echo $row['kelas']; ?></span>
                            </div>
                            <div class="d-flex flex-column align-items-end gap-1" style="max-width: 35%;">
                                <span class="badge-poin bg-success-subtle text-success"><i class="bi bi-award-fill"></i> +<?php echo $row['total_poin_reward']; ?></span>
                                <span class="badge-poin bg-danger-subtle text-danger"><i class="bi bi-exclamation-octagon-fill"></i> -<?php echo $row['total_poin_punishment']; ?></span>
                            </div>
                        </div>
                        <div class="action-btn-group">
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1" data-bs-toggle="modal" data-bs-target="#modalEdit" data-id="<?php echo $row['id_siswa']; ?>" data-nis="<?php echo $row['nis']; ?>" data-nama="<?php echo htmlspecialchars($row['nama_siswa'], ENT_QUOTES); ?>" data-kelas="<?php echo $row['kelas']; ?>" data-reward="<?php echo $row['total_poin_reward']; ?>" data-punishment="<?php echo $row['total_poin_punishment']; ?>">
                                <i class="bi bi-pencil-square me-1"></i> Edit
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 btn-hapus" data-href="proses_siswa.php?hapus=<?php echo $row['id_siswa']; ?>" data-nama="<?php echo htmlspecialchars($row['nama_siswa'], ENT_QUOTES); ?>">
                                <i class="bi bi-trash3 me-1"></i> Hapus
                            </button>
                        </div>
                    </div>

                    <div class="desktop-view card border-0 shadow-sm p-3 mb-2" style="border-radius: 14px;">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <span class="text-secondary small d-block">Nomor NIS</span>
                                <span class="fw-semibold text-dark"><?php echo $row['nis']; ?></span>
                            </div>
                            <div class="col-md-4">
                                <span class="text-secondary small d-block">Nama Lengkap Siswa</span>
                                <span class="fw-bold text-dark"><?php echo htmlspecialchars($row['nama_siswa']); ?></span>
                            </div>
                            <div class="col-md-2">
                                <span class="text-secondary small d-block mb-1">Kelas</span>
                                <span class="badge bg-primary-subtle text-primary px-2.5 py-1 fw-bold"><?php echo $row['kelas']; ?></span>
                            </div>
                            <div class="col-md-1 text-center">
                                <span class="text-secondary small d-block mb-1">Reward</span>
                                <span class="badge bg-success text-white rounded px-2 py-1">+ <?php echo $row['total_poin_reward']; ?></span>
                            </div>
                            <div class="col-md-1 text-center">
                                <span class="text-secondary small d-block mb-1">Sanksi</span>
                                <span class="badge bg-danger text-white rounded px-2 py-1">- <?php echo $row['total_poin_punishment']; ?></span>
                            </div>
                            <div class="col-md-2 text-end">
                                <button class="btn btn-sm btn-outline-primary me-1 rounded-circle" style="width:32px; height:32px; padding:0;" data-bs-toggle="modal" data-bs-target="#modalEdit" data-id="<?php echo $row['id_siswa']; ?>" data-nis="<?php echo $row['nis']; ?>" data-nama="<?php echo htmlspecialchars($row['nama_siswa'], ENT_QUOTES); ?>" data-kelas="<?php echo $row['kelas']; ?>" data-reward="<?php echo $row['total_poin_reward']; ?>" data-punishment="<?php echo $row['total_poin_punishment']; ?>"><i class="bi bi-pencil-square"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-hapus rounded-circle" style="width:32px; height:32px; padding:0;" data-href="proses_siswa.php?hapus=<?php echo $row['id_siswa']; ?>" data-nama="<?php echo htmlspecialchars($row['nama_siswa'], ENT_QUOTES); ?>">
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
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-person-plus-fill text-primary me-2"></i>Tambah Siswa Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="proses_siswa.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Nomor Induk Siswa (NIS)</label>
                        <input type="text" name="nis" class="form-control text-uppercase" required inputmode="numeric" pattern="[0-9A-Za-z]*" maxlength="20" placeholder="Contoh: 21220101" oninput="this.value = this.value.toUpperCase()">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Nama Lengkap Siswa</label>
                        <input type="text" name="nama_siswa" class="form-control text-uppercase" required placeholder="MASUKKAN NAMA LENGKAP" oninput="this.value = this.value.toUpperCase()">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-secondary">Kelas</label>
                        <input type="text" name="kelas" class="form-control text-uppercase" required placeholder="Contoh: XII-RPL-1" oninput="this.value = this.value.toUpperCase()">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="aksi_tambah" class="btn btn-primary rounded-pill px-4">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Data & Poin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="proses_siswa.php" method="POST">
                <input type="hidden" name="id_siswa" id="edit-id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">NIS</label>
                        <input type="text" name="nis" id="edit-nis" class="form-control text-uppercase" required inputmode="numeric" pattern="[0-9A-Za-z]*" maxlength="20" oninput="this.value = this.value.toUpperCase()">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Nama Siswa</label>
                        <input type="text" name="nama_siswa" id="edit-nama" class="form-control text-uppercase" required oninput="this.value = this.value.toUpperCase()">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Kelas</label>
                        <input type="text" name="kelas" id="edit-kelas" class="form-control text-uppercase" required placeholder="Contoh: XII-RPL-1" oninput="this.value = this.value.toUpperCase()">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-2">
                            <label class="form-label small fw-semibold text-success">Poin Reward</label>
                            <input type="number" name="poin_reward" id="edit-reward" class="form-control" required>
                        </div>
                        <div class="col-6 mb-2">
                            <label class="form-label small fw-semibold text-danger">Poin Punishment</label>
                            <input type="number" name="poin_punishment" id="edit-punishment" class="form-control" required>
                        </div>
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
        // Inisialisasi DataTables
        var table = $('#tabelSiswa').DataTable({
            "ordering": false,
            "pageLength": 10,
            "responsive": true,
            "autoWidth": false,
            "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Semua"]],
            "language": {
                "sProcessing":   "Sedang memproses...",
                "sLengthMenu":   "Tampilkan _MENU_ data",
                "sZeroRecords":  "Tidak ditemukan data siswa yang sesuai",
                "sInfo":         "Menampilkan _START_ sampai _END_ dari _TOTAL_ siswa",
                "sInfoEmpty":    "Menampilkan 0 sampai 0 dari 0 data",
                "oPaginate": {
                    "sFirst":    "Pertama",
                    "sPrevious": "Sebelumnya",
                    "sNext":     "Selanjutnya",
                    "sLast":     "Terakhir"
                }
            }
        });

        // Menghubungkan Input Pencarian Kustom Baru ke dalam Mesin DataTables secara Realtime
        $('#pencarianSiswa').on('keyup change', function() {
            table.search($(this).val()).draw();
        });

        // Script Modal Edit
        const modalEdit = document.getElementById('modalEdit');
        modalEdit.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const nis = button.getAttribute('data-nis');
            const nama = button.getAttribute('data-nama');
            const kelas = button.getAttribute('data-kelas');
            const reward = button.getAttribute('data-reward');
            const punishment = button.getAttribute('data-punishment');

            modalEdit.querySelector('#edit-id').value = id;
            modalEdit.querySelector('#edit-nis').value = nis;
            modalEdit.querySelector('#edit-nama').value = nama;
            modalEdit.querySelector('#edit-kelas').value = kelas;
            modalEdit.querySelector('#edit-reward').value = reward;
            modalEdit.querySelector('#edit-punishment').value = punishment;
        });

        // SweetAlert2 Hapus
        $(document).on('click', '.btn-hapus', function() {
            const urlTarget = this.getAttribute('data-href');
            const namaSiswa = this.getAttribute('data-nama');

            Swal.fire({
                title: 'Yakin Ingin Menghapus?',
                text: 'Data siswa "' + namaSiswa + '" akan dihapus secara permanen dari sistem.',
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