<?php
session_start();
if($_SESSION['status'] != "login"){
    header("location:index.php");
    exit();
}

include 'koneksi.php';

// Filter Jenis Aturan (Reward / Punishment)
$filter_jenis = isset($_GET['jenis']) ? mysqli_real_escape_string($koneksi, $_GET['jenis']) : '';

$query = "SELECT * FROM master_poin WHERE 1=1";
if ($filter_jenis != '') { 
    $query .= " AND jenis = '$filter_jenis'"; 
}
$query .= " ORDER BY jenis DESC, nama_perilaku ASC";

$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Master Poin & Aturan - SMKS DB</title>
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
        .rule-mobile-card { background: white; border-radius: 18px; border: none; box-shadow: 0 4px 10px rgba(0,0,0,0.03); margin-bottom: 12px; padding: 16px; width: 100%; }
        .badge-poin { padding: 5px 10px; border-radius: 8px; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }
        .action-btn-group { border-top: 1px dashed #e2e8f0; margin-top: 12px; padding-top: 12px; display: flex; justify-content: flex-end; gap: 8px; }
        .modal-content { border-radius: 20px; border: none; }
        
        /* Kunci Lebar Tabel Pas Layar HP */
        #tabelPoin {
            table-layout: fixed !important;
            width: 100% !important;
            border-collapse: collapse !important;
        }
        #tabelPoin td {
            word-wrap: break-word;
            white-space: normal;
        }
        
        .dataTables_wrapper .dataTables_filter { display: none !important; }
        .dataTables_wrapper .dataTables_length, .dataTables_info { display: none !important; }
        .dataTables_wrapper .dataTables_paginate .paginate_button { padding: 0px !important; margin-left: 0px !important; border: none !important; }
        
        .custom-search-container input {
            border: none !important;
            box-shadow: none !important;
            padding: 8px 12px;
            font-size: 14px;
            width: 100%;
        }
        .custom-search-container input:focus { outline: none; }
        
        @media (max-width: 767.98px) { 
            .desktop-view { display: none !important; }
            .desktop-add-btn { display: none !important; }
            
            .rule-container { 
                padding-bottom: 95px; 
                width: 100% !important; 
                max-width: 100% !important;
                overflow-x: hidden;
            }
            
            .mobile-fab-container { position: fixed; bottom: 20px; left: 0; right: 0; padding: 0 20px; z-index: 1050; }
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
            .rule-container { margin-top: 30px; } 
            #tabelPoin { table-layout: auto !important; }
        }
    </style>
</head>
<body>

<div class="mobile-header">
    <div class="container d-flex align-items-center justify-content-between p-0">
        <div class="d-flex align-items-center gap-3">
            <a href="dashboard" class="btn-back"><i class="bi bi-arrow-left"></i></a>
            <h5 class="fw-bold mb-0" style="font-size: 18px;">Master Aturan & Poin</h5>
        </div>
        <button class="btn btn-light btn-sm fw-bold rounded-pill px-3 desktop-add-btn" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-plus-lg me-1"></i> Tambah Aturan
        </button>
    </div>
</div>

<div class="mobile-fab-container">
    <button class="mobile-fab-btn" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="bi bi-plus-circle-fill" style="font-size: 18px;"></i> Tambah Aturan Baru
    </button>
</div>

<div class="container rule-container px-3 mt-3">

    <?php if(isset($_GET['pesan'])): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 small py-2 mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?php 
                if($_GET['pesan'] == "tambah_sukses") echo "Aturan baru berhasil ditambahkan!";
                if($_GET['pesan'] == "edit_sukses") echo "Perubahan data aturan berhasil disimpan!";
                if($_GET['pesan'] == "hapus_sukses") echo "Data aturan telah berhasil dihapus!";
                if($_GET['pesan'] == "gagal") echo "Terjadi kesalahan sistem, operasi gagal.";
            ?>
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card p-3 border-0 shadow-sm mb-3 bg-white" style="border-radius: 16px;">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-8">
                <label class="form-label small fw-semibold text-secondary mb-1">Cari Perilaku / Aturan</label>
                <div class="input-group input-group-sm rounded overflow-hidden border custom-search-container">
                    <span class="input-group-text bg-white border-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="pencarianAturan" class="form-control" placeholder="Ketik nama perilaku atau aturan...">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <form method="GET" action="" id="filterForm">
                    <label class="form-label small fw-semibold text-secondary mb-1">Filter Jenis Aturan</label>
                    <div class="input-group input-group-sm rounded overflow-hidden border">
                        <span class="input-group-text bg-white border-0"><i class="bi bi-funnel text-muted"></i></span>
                        <select name="jenis" class="form-select form-select-sm bg-white border-0 fw-semibold text-secondary py-2" onchange="document.getElementById('filterForm').submit();">
                            <option value="">Semua Jenis</option>
                            <option value="Reward" <?php echo ($filter_jenis == 'Reward') ? 'selected' : ''; ?>>Reward (Penghargaan)</option>
                            <option value="Punishment" <?php echo ($filter_jenis == 'Punishment') ? 'selected' : ''; ?>>Punishment (Pelanggaran)</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <table id="tabelPoin" class="table table-borderless table-responsive w-100 m-0 p-0" style="display: table !important;">
        <thead class="d-none">
            <tr>
                <th>Konten Aturan</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)) { 
                $is_reward = (strtolower($row['jenis']) == 'reward');
                $badge_class = $is_reward ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
                $poin_prefix = $is_reward ? '+' : '-';
            ?>
            <tr>
                <td class="p-0 border-0">

                    <div class="mobile-view rule-mobile-card">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <div style="max-width: 75%;">
                                <span class="badge <?php echo $badge_class; ?> rounded-pill px-2 py-0.5 mb-1.5" style="font-size: 10px; font-weight: 600;">
                                    <?php echo $row['jenis']; ?>
                                </span>
                                <h6 class="fw-semibold text-dark mb-0 line-clamp" style="font-size: 14px; line-height: 1.4;">
                                    <?php echo htmlspecialchars($row['nama_perilaku']); ?>
                                </h6>
                            </div>
                            <div class="text-end" style="max-width: 25%;">
                                <span class="badge-poin <?php echo $badge_class; ?>">
                                    <?php echo $poin_prefix . ' ' . $row['poin']; ?>
                                </span>
                            </div>
                        </div>
                        <div class="action-btn-group">
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1" data-bs-toggle="modal" data-bs-target="#modalEdit" data-id="<?php echo $row['id_aturan']; ?>" data-jenis="<?php echo $row['jenis']; ?>" data-perilaku="<?php echo htmlspecialchars($row['nama_perilaku'], ENT_QUOTES); ?>" data-poin="<?php echo $row['poin']; ?>">
                                <i class="bi bi-pencil-square me-1"></i> Edit
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 btn-hapus" data-href="proses_poin.php?hapus=<?php echo $row['id_aturan']; ?>" data-nama="<?php echo htmlspecialchars($row['nama_perilaku'], ENT_QUOTES); ?>">
                                <i class="bi bi-trash3 me-1"></i> Hapus
                            </button>
                        </div>
                    </div>

                    <div class="desktop-view card border-0 shadow-sm p-3 mb-2" style="border-radius: 14px;">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <span class="text-secondary small d-block mb-1">Jenis</span>
                                <span class="badge <?php echo $badge_class; ?> px-2.5 py-1 fw-bold"><?php echo $row['jenis']; ?></span>
                            </div>
                            <div class="col-md-7">
                                <span class="text-secondary small d-block">Nama Aturan / Perilaku</span>
                                <span class="fw-semibold text-dark"><?php echo htmlspecialchars($row['nama_perilaku']); ?></span>
                            </div>
                            <div class="col-md-1 text-center">
                                <span class="text-secondary small d-block mb-1">Bobot</span>
                                <span class="badge <?php echo $is_reward ? 'bg-success' : 'bg-danger'; ?> text-white rounded px-2 py-1 fw-bold">
                                    <?php echo $poin_prefix . ' ' . $row['poin']; ?>
                                </span>
                            </div>
                            <div class="col-md-2 text-end">
                                <button class="btn btn-sm btn-outline-primary me-1 rounded-circle" style="width:32px; height:32px; padding:0;" data-bs-toggle="modal" data-bs-target="#modalEdit" data-id="<?php echo $row['id_aturan']; ?>" data-jenis="<?php echo $row['jenis']; ?>" data-perilaku="<?php echo htmlspecialchars($row['nama_perilaku'], ENT_QUOTES); ?>" data-poin="<?php echo $row['poin']; ?>"><i class="bi bi-pencil-square"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-hapus rounded-circle" style="width:32px; height:32px; padding:0;" data-href="proses_poin.php?hapus=<?php echo $row['id_aturan']; ?>" data-nama="<?php echo htmlspecialchars($row['nama_perilaku'], ENT_QUOTES); ?>">
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
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-plus-circle-fill text-primary me-2"></i>Tambah Aturan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="proses_poin.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Jenis Aturan</label>
                        <select name="jenis" class="form-select" required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="Reward">Reward (Penghargaan/Prestasi)</option>
                            <option value="Punishment">Punishment (Pelanggaran/Sanksi)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Nama Aturan / Perilaku</label>
                        <textarea name="nama_perilaku" class="form-control" rows="3" required placeholder="Masukkan detail perilaku atau pelanggaran..."></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-secondary">Nilai Poin</label>
                        <input type="number" name="poin" class="form-control" min="1" required placeholder="Contoh: 15">
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
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Aturan Poin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="proses_poin.php" method="POST">
                <input type="hidden" name="id_aturan" id="edit-id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Jenis Aturan</label>
                        <select name="jenis" id="edit-jenis" class="form-select" required>
                            <option value="Reward">Reward</option>
                            <option value="Punishment">Punishment</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Nama Aturan / Perilaku</label>
                        <textarea name="nama_perilaku" id="edit-perilaku" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-secondary">Nilai Poin</label>
                        <input type="number" name="poin" id="edit-poin" class="form-control" min="1" required>
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
        var table = $('#tabelPoin').DataTable({
            "ordering": false,
            "pageLength": 10,
            "responsive": true,
            "autoWidth": false,
            "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Semua"]],
            "language": {
                "sProcessing":   "Sedang memproses...",
                "sLengthMenu":   "Tampilkan _MENU_ data",
                "sZeroRecords":  "Tidak ditemukan aturan yang sesuai",
                "sInfo":         "Menampilkan _START_ sampai _END_ dari _TOTAL_ aturan",
                "sInfoEmpty":    "Menampilkan 0 sampai 0 dari 0 data",
                "oPaginate": {
                    "sFirst":    "Pertama",
                    "sPrevious": "Sebelumnya",
                    "sNext":     "Selanjutnya",
                    "sLast":     "Terakhir"
                }
            }
        });

        // Menghubungkan Input Pencarian Kustom
        $('#pencarianAturan').on('keyup change', function() {
            table.search($(this).val()).draw();
        });

        // Script Modal Edit Data Aturan
        const modalEdit = document.getElementById('modalEdit');
        modalEdit.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const jenis = button.getAttribute('data-jenis');
            const perilaku = button.getAttribute('data-perilaku');
            const poin = button.getAttribute('data-poin');

            modalEdit.querySelector('#edit-id').value = id;
            modalEdit.querySelector('#edit-jenis').value = jenis;
            modalEdit.querySelector('#edit-perilaku').value = perilaku;
            modalEdit.querySelector('#edit-poin').value = poin;
        });

        // SweetAlert2 Hapus Data Aturan
        $(document).on('click', '.btn-hapus', function() {
            const urlTarget = this.getAttribute('data-href');
            const namaAturan = this.getAttribute('data-nama');

            Swal.fire({
                title: 'Yakin Ingin Menghapus?',
                text: 'Aturan "' + namaAturan + '" akan dihapus secara permanen dari sistem.',
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