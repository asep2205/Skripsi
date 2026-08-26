<?php
session_start();
if($_SESSION['status'] != "login"){
    header("location:index.php");
    exit();
}

include 'koneksi.php';
include 'periode_helper.php';

// Ambil ID User yang sedang login dari session untuk direkam ke tabel remisi
$id_user_login = isset($_SESSION['id_user']) ? (int)$_SESSION['id_user'] : 1; 

$pesan_remisi = '';
$status_remisi = '';
$id_periode_aktif = id_periode_aktif($koneksi);

if ((isset($_GET['proses_konfirmasi']) || isset($_POST['proses_pengajuan'])) && $id_periode_aktif <= 0) {
    $status_remisi = 'gagal';
    $pesan_remisi = 'Belum ada periode tahun ajaran yang aktif.';
}

// --- PROSES ALUR 1: KONFIRMASI (LANGSUNG POTONG OTOMATIS BERDASARKAN SELISIH REWARD) ---
if (isset($_GET['proses_konfirmasi']) && $id_periode_aktif > 0) {
    $id_siswa = (int)$_GET['proses_konfirmasi'];

    // 1. Ambil data poin siswa saat ini
    $query_tampil = mysqli_query($koneksi, "SELECT nama_siswa, total_poin_reward, total_poin_punishment FROM siswa WHERE id_siswa = $id_siswa");
    
    if (mysqli_num_rows($query_tampil) > 0) {
        $data = mysqli_fetch_assoc($query_tampil);
        $nama_siswa = $data['nama_siswa'];
        $reward_sekarang = (int)$data['total_poin_reward'];
        $punishment_sekarang = (int)$data['total_poin_punishment'];

        if ($punishment_sekarang >= 30) {
            
            // LOGIKA KONFIRMASI: Sistem Potong Selisih Poin otomatis (Peleburan Punishment vs Reward)
            if ($punishment_sekarang >= $reward_sekarang) {
                $poin_remisi_tercatat = $reward_sekarang;
                $punishment_baru = $punishment_sekarang - $reward_sekarang;
                $reward_baru = 0;
                $detail_pesan = "Poin sanksi berkurang menjadi $punishment_baru dan poin reward telah habis (0).";
            } else {
                $poin_remisi_tercatat = $punishment_sekarang;
                $punishment_baru = 0;
                $reward_baru = $reward_sekarang - $punishment_sekarang;
                $detail_pesan = "Poin sanksi lunas (0) dan sisa poin reward siswa menjadi $reward_baru.";
            }

            mysqli_begin_transaction($koneksi);

            try {
                // Update Poin Utama Siswa
                $query_update = "UPDATE siswa SET total_poin_reward = $reward_baru, total_poin_punishment = $punishment_baru WHERE id_siswa = $id_siswa";
                mysqli_query($koneksi, $query_update);

                // Catat transaksi Riwayat ke Tabel Remisi
                $keterangan_otomatis = "Sistem Konfirmasi Otomatis: Sinkronisasi pemotongan " . $poin_remisi_tercatat . " poin sanksi menggunakan poin reward.";
                $query_insert_remisi = "INSERT INTO remisi (id_siswa, id_user, id_periode, poin_remisi, pengajuan, keterangan, tgl_input)
                                        VALUES ($id_siswa, $id_user_login, $id_periode_aktif, $poin_remisi_tercatat, 'konfirmasi', '$keterangan_otomatis', NOW())";
                mysqli_query($koneksi, $query_insert_remisi);

                mysqli_commit($koneksi);
                $status_remisi = 'sukses';
                $pesan_remisi = "Remisi langsung (Konfirmasi) berhasil diproses untuk <strong>$nama_siswa</strong>! " . $detail_pesan;
            } catch (Exception $e) {
                mysqli_rollback($koneksi);
                $status_remisi = 'gagal';
                $pesan_remisi = "Gagal memproses transaksi database.";
            }
        } else {
            $status_remisi = 'gagal';
            $pesan_remisi = "Siswa tidak memenuhi syarat remisi (Poin sanksi kurang dari 30).";
        }
    } else {
        $status_remisi = 'gagal';
        $pesan_remisi = "Data siswa tidak ditemukan.";
    }
}

// --- PROSES ALUR 2: PENGAJUAN FORM (PERUBAHAN: MEMOTONG PUNISHMENT TANPA MENGURANGI REWARD) ---
if (isset($_POST['proses_pengajuan']) && $id_periode_aktif > 0) {
    $id_siswa = (int)$_POST['id_siswa'];
    $poin_ajukan = (int)$_POST['poin_remisi'];
    $keterangan_reward = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    // =========================================================================
    // [BARU] UPLOAD FOTO BUKTI REAL-TIME (WAJIB) UNTUK FORM PENGAJUAN REMISI
    // =========================================================================
    $bukti_final = "";
    if (isset($_FILES['bukti_foto']) && $_FILES['bukti_foto']['error'] == 0) {
        $folder_upload = 'uploads/bukti_remisi/';
        if (!is_dir($folder_upload)) {
            mkdir($folder_upload, 0755, true);
        }

        $ext_diizinkan = ['jpg', 'jpeg', 'png', 'webp'];
        $ext_file = strtolower(pathinfo($_FILES['bukti_foto']['name'], PATHINFO_EXTENSION));
        $ukuran_maks = 5 * 1024 * 1024; // 5 MB

        if (in_array($ext_file, $ext_diizinkan) && $_FILES['bukti_foto']['size'] <= $ukuran_maks) {
            $nama_file_baru = 'bukti_remisi_' . $id_siswa . '_' . time() . '_' . uniqid() . '.' . $ext_file;
            $path_tujuan = $folder_upload . $nama_file_baru;

            if (move_uploaded_file($_FILES['bukti_foto']['tmp_name'], $path_tujuan)) {
                $bukti_final = $path_tujuan;
            }
        }
    }

    if (empty($bukti_final)) {
        $status_remisi = 'gagal';
        $pesan_remisi = "Foto bukti wajib diunggah (format JPG/PNG/WEBP, maksimal 5MB).";
    } elseif ($poin_ajukan > 0) {
        // 1. Ambil data punishment siswa saat ini untuk pengecekan validasi limit data
        $query_cek = mysqli_query($koneksi, "SELECT nama_siswa, total_poin_punishment FROM siswa WHERE id_siswa = $id_siswa");
        
        if (mysqli_num_rows($query_cek) > 0) {
            $d_siswa = mysqli_fetch_assoc($query_cek);
            $nama_siswa = $d_siswa['nama_siswa'];
            $punishment_sekarang = (int)$d_siswa['total_poin_punishment'];

            // Mencegah nilai punishment menjadi minus jika poin ajuan lebih besar dari sisa sanksi
            if ($poin_ajukan > $punishment_sekarang) {
                $poin_ajukan = $punishment_sekarang; // Set maksimal pemotongan senilai sisa punishment yang ada
            }

            $punishment_baru = $punishment_sekarang - $poin_ajukan;

            // Mulai database transaksi biar data sinkron antara tabel siswa dan log remisi
            mysqli_begin_transaction($koneksi);

            try {
                // PERBAHAN LOGIKA: Hanya meng-update total_poin_punishment (poin reward diabaikan/aman)
                $query_potong_punishment = "UPDATE siswa SET total_poin_punishment = $punishment_baru WHERE id_siswa = $id_siswa";
                mysqli_query($koneksi, $query_potong_punishment);

                // Simpan rekaman histori log ke tabel remisi dengan status 'pengajuan' beserta foto buktinya
                $bukti_db = mysqli_real_escape_string($koneksi, $bukti_final);
                $query_ajukan = "INSERT INTO remisi (id_siswa, id_user, id_periode, poin_remisi, pengajuan, keterangan, bukti, tgl_input)
                                 VALUES ($id_siswa, $id_user_login, $id_periode_aktif, $poin_ajukan, 'pengajuan', '$keterangan_reward', '$bukti_db', NOW())";
                mysqli_query($koneksi, $query_ajukan);

                mysqli_commit($koneksi);
                $status_remisi = 'sukses';
                $pesan_remisi = "Pengajuan remisi untuk <strong>$nama_siswa</strong> berhasil! Poin sanksi (punishment) berhasil dipotong sebesar <strong>$poin_ajukan Poin</strong> tanpa mengubah poin reward. Sisa sanksi saat ini: $punishment_baru Poin.";
            } catch (Exception $e) {
                mysqli_rollback($koneksi);
                if (!empty($bukti_final) && file_exists($bukti_final)) {
                    @unlink($bukti_final);
                }
                $status_remisi = 'gagal';
                $pesan_remisi = "Terjadi kegagalan sistem saat memotong poin pengajuan.";
            }
        } else {
            if (!empty($bukti_final) && file_exists($bukti_final)) {
                @unlink($bukti_final);
            }
            $status_remisi = 'gagal';
            $pesan_remisi = "Data siswa tidak dikenali.";
        }
    } else {
        if (!empty($bukti_final) && file_exists($bukti_final)) {
            @unlink($bukti_final);
        }
        $status_remisi = 'gagal';
        $pesan_remisi = "Poin ajuan remisi minimal bernilai 1.";
    }
}

// Filter Kelas
$filter_kelas = isset($_GET['kelas']) ? mysqli_real_escape_string($koneksi, $_GET['kelas']) : '';

$query = "SELECT * FROM siswa WHERE 1=1";
if ($filter_kelas != '') { 
    $query .= " AND kelas = '$filter_kelas'"; 
}
$query .= " ORDER BY total_poin_punishment DESC, nama_siswa ASC";

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
        .student-mobile-card { background: white; border-radius: 18px; border: none; box-shadow: 0 4px 10px rgba(0,0,0,0.03); margin-bottom: 12px; padding: 16px; width: 100%; }
        .badge-poin { padding: 5px 10px; border-radius: 8px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
        .action-btn-group { border-top: 1px dashed #e2e8f0; margin-top: 12px; padding-top: 12px; display: flex; justify-content: flex-end; gap: 8px; }
        .modal-content { border-radius: 20px; border: none; }
        #tabelSiswa { table-layout: fixed !important; width: 100% !important; border-collapse: collapse !important; }
        #tabelSiswa td { word-wrap: break-word; white-space: normal; }
        .dataTables_wrapper .dataTables_filter { display: none !important; }
        .dataTables_wrapper .dataTables_length, .dataTables_info { display: none !important; }
        .dataTables_wrapper .dataTables_paginate .paginate_button { padding: 0px !important; margin-left: 0px !important; border: none !important; }
        .custom-search-container input { border: none !important; box-shadow: none !important; padding: 8px 12px; font-size: 14px; width: 100%; }
        .custom-search-container input:focus { outline: none; }
        @media (max-width: 767.98px) { 
            .desktop-view { display: none !important; }
            .student-container { padding-bottom: 40px; width: 100% !important; max-width: 100% !important; overflow-x: hidden; }
            .dataTables_paginate { display: flex; justify-content: center; margin-top: 15px; }
        }
        @media (min-width: 768px) { 
            .mobile-view { display: none !important; } 
            .student-container { margin-top: 30px; } 
            #tabelSiswa { table-layout: auto !important; }
        }

        .upload-foto-box { border: 2px dashed #cbd5e1; border-radius: 14px; padding: 18px; text-align: center; background-color: #f8fafc; cursor: pointer; transition: all 0.2s; }
        .upload-foto-box:hover { border-color: #4f46e5; background-color: #eef2ff; }
        .upload-foto-box i { font-size: 26px; color: #64748b; }
        .preview-foto-wrapper { display: none; margin-top: 12px; text-align: center; position: relative; }
        .preview-foto-wrapper img { max-width: 100%; max-height: 260px; border-radius: 12px; box-shadow: 0 4px 14px rgba(0,0,0,0.12); }
        .btn-hapus-foto { position: absolute; top: 6px; right: 6px; background: rgba(15,23,42,0.75); color: #fff; border: none; border-radius: 50%; width: 28px; height: 28px; }
    </style>
</head>
<body>

<div class="mobile-header">
    <div class="container d-flex align-items-center justify-content-between p-0">
        <div class="d-flex align-items-center gap-3">
            <a href="dashboard" class="btn-back"><i class="bi bi-arrow-left"></i></a>
            <h5 class="fw-bold mb-0" style="font-size: 18px;">Data Siswa & Pengajuan Remisi</h5>
        </div>
    </div>
</div>

<div class="container student-container px-3 mt-3">

    <?php if(!empty($pesan_remisi)): ?>
        <div class="alert <?php echo ($status_remisi == 'sukses') ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show rounded-3 small py-2 mb-3" role="alert">
            <i class="bi <?php echo ($status_remisi == 'sukses') ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?> me-2"></i>
            <?php echo $pesan_remisi; ?>
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" onclick="window.location.href='remisi_poin'"></button>
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
            <tr><th>Konten</th></tr>
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
                        
                        <?php if ($row['total_poin_punishment'] >= 30): ?>
                        <div class="action-btn-group">
                            <button type="button" class="btn btn-sm btn-success w-100 rounded-pill py-2 btn-pilih-opsi" data-id="<?php echo $row['id_siswa']; ?>" data-nama="<?php echo htmlspecialchars($row['nama_siswa'], ENT_QUOTES); ?>" data-poin="<?php echo $row['total_poin_punishment']; ?>" data-reward="<?php echo $row['total_poin_reward']; ?>">
                                <i class="bi bi-heart-arrow me-1"></i> Opsi Remisi Poin
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="desktop-view card border-0 shadow-sm p-3 mb-2" style="border-radius: 14px;">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <span class="text-secondary small d-block">Nomor NIS</span>
                                <span class="fw-semibold text-dark"><?php echo $row['nis']; ?></span>
                            </div>
                            <div class="col-md-3">
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
                                <span class="text-secondary small d-block mb-1">Punishment</span>
                                <span class="badge bg-danger text-white rounded px-2 py-1">- <?php echo $row['total_poin_punishment']; ?></span>
                            </div>
                            <div class="col-md-3 text-end">
                                <?php if ($row['total_poin_punishment'] >= 30): ?>
                                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1.5 btn-pilih-opsi" data-id="<?php echo $row['id_siswa']; ?>" data-nama="<?php echo htmlspecialchars($row['nama_siswa'], ENT_QUOTES); ?>" data-poin="<?php echo $row['total_poin_punishment']; ?>" data-reward="<?php echo $row['total_poin_reward']; ?>">
                                        <i class="bi bi-heart-arrow me-1"></i> Opsi Remisi
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted small italic"><i class="bi bi-shield-check text-success"></i> Sesi Aman</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="modalPengajuanForm" data-bs-backdrop="static" tabindex="-1" aria-labelledby="titleModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">
            <div class="modal-header bg-primary text-white py-3" style="border-radius: 20px 20px 0 0;">
                <h6 class="modal-title fw-bold" id="titleModal"><i class="bi bi-file-earmark-medical me-1"></i> Formulir Pengajuan Remisi</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data" id="formPengajuanRemisi">
                <div class="modal-body p-4">
                    <input type="hidden" name="id_siswa" id="modal_id_siswa">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Nama Siswa</label>
                        <input type="text" id="modal_nama_siswa" class="form-control bg-light fw-bold" readonly style="border-radius: 10px;">
                    </div>

                    <div class="mb-3">
                        <label for="poin_remisi" class="form-label small fw-semibold text-secondary">Poin Punishment yang Ingin Dihapus</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-danger-subtle text-danger fw-bold border-end-0"><i class="bi bi-dash-circle-fill"></i></span>
                            <input type="number" name="poin_remisi" id="poin_remisi" class="form-control border-start-0 py-2 fw-semibold" placeholder="Contoh: 10" required min="1" style="border-radius: 0 10px 10px 0;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="keterangan" class="form-label small fw-semibold text-secondary">Deskripsi Alasan / Keterangan Pengajuan</label>
                        <textarea name="keterangan" id="keterangan" class="form-control" rows="3" placeholder="Tulis rincian alasan pengajuan..." required style="border-radius: 10px; font-size: 13px;"></textarea>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-semibold text-secondary">Foto Bukti Pendukung (Real-time)</label>
                        <div class="upload-foto-box" id="uploadBuktiBox">
                            <i class="bi bi-camera-fill"></i>
                            <div class="mt-2 small text-muted">Ketuk untuk ambil foto atau pilih dari galeri</div>
                            <input type="file" name="bukti_foto" id="bukti_foto" accept="image/*" capture="environment" class="d-none" required>
                        </div>
                        <div class="preview-foto-wrapper" id="previewBuktiWrapper">
                            <button type="button" class="btn-hapus-foto" id="btnHapusBukti"><i class="bi bi-x"></i></button>
                            <img id="previewBuktiFoto" src="" alt="Preview foto bukti">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-2.5" style="border-radius: 0 0 20px 20px;">
                    <button type="button" class="btn type="button" class="btn btn-sm btn-secondary px-3 rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="proses_pengajuan" class="btn btn-sm btn-primary px-4 rounded-pill"><i class="bi bi-save2 me-1"></i> Simpan Pengajuan</button>
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
        var table = $('#tabelSiswa').DataTable({
            "ordering": false,
            "pageLength": 10,
            "responsive": true,
            "autoWidth": false,
            "language": {
                "sProcessing": "Sedang memproses...",
                "sZeroRecords": "Tidak ditemukan data siswa yang sesuai",
                "sInfo": "Menampilkan _START_ sampai _END_ dari _TOTAL_ siswa",
                "oPaginate": { "sPrevious": "Sebelumnya", "sNext": "Selanjutnya" }
            }
        });

        $('#pencarianSiswa').on('keyup change', function() {
            table.search($(this).val()).draw();
        });

        // Handler Pop-Up Pilihan Tindakan
        $(document).on('click', '.btn-pilih-opsi', function() {
            const idSiswa = this.getAttribute('data-id');
            const namaSiswa = this.getAttribute('data-nama');
            const totalPoin = this.getAttribute('data-poin');
            const totalReward = this.getAttribute('data-reward');

            Swal.fire({
                title: 'Pilih Metode Tindakan Remisi',
                html: `Siswa <strong>${namaSiswa}</strong> memiliki sanksi <span class="badge bg-danger">${totalPoin} Poin</span> dan reward <span class="badge bg-success">${totalReward} Poin</span>.<br><br>Pilih tipe pemrosesan data di bawah ini:`,
                icon: 'question',
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonColor: '#198754', 
                denyButtonColor: '#0d6efd',    
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-lightning-charge-fill me-1"></i> Langsung Konfirmasi',
                denyButtonText: '<i class="bi bi-file-earmark-plus-fill me-1"></i> Buat Form Pengajuan',
                cancelButtonText: 'Batal',
                reverseButtons: false
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Konfirmasi Mutasi?',
                        text: 'Sistem akan memotong poin sanksi berdasarkan jumlah poin reward secara otomatis.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#198754',
                        confirmButtonText: 'Ya, eksekusi potongan!',
                        cancelButtonText: 'Kembali'
                    }).then((confirmExecute) => {
                        if (confirmExecute.isConfirmed) {
                            window.location.href = "remisi_poin?proses_konfirmasi=" + idSiswa;
                        }
                    });

                } else if (result.isDenied) {
                    $('#modal_id_siswa').val(idSiswa);
                    $('#modal_nama_siswa').val(namaSiswa);
                    $('#poin_remisi').val('');
                    $('#keterangan').val('');

                    // Reset input & preview foto bukti setiap kali form dibuka untuk siswa baru
                    document.getElementById('bukti_foto').value = '';
                    document.getElementById('previewBuktiFoto').src = '';
                    document.getElementById('previewBuktiWrapper').style.display = 'none';
                    document.getElementById('uploadBuktiBox').style.display = 'block';

                    $('#modalPengajuanForm').modal('show');
                }
            });
        });

        // ==== Upload Foto Bukti Pengajuan + Preview Real-time ====
        const uploadBuktiBox = document.getElementById('uploadBuktiBox');
        const buktiInput = document.getElementById('bukti_foto');
        const previewBuktiWrapper = document.getElementById('previewBuktiWrapper');
        const previewBuktiFoto = document.getElementById('previewBuktiFoto');
        const btnHapusBukti = document.getElementById('btnHapusBukti');

        uploadBuktiBox.addEventListener('click', () => buktiInput.click());

        buktiInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => {
                    previewBuktiFoto.src = e.target.result;
                    previewBuktiWrapper.style.display = 'block';
                    uploadBuktiBox.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });

        btnHapusBukti.addEventListener('click', function() {
            buktiInput.value = '';
            previewBuktiFoto.src = '';
            previewBuktiWrapper.style.display = 'none';
            uploadBuktiBox.style.display = 'block';
        });

        // ==== Validasi Foto Bukti Wajib Sebelum Form Pengajuan Dikirim ====
        document.getElementById('formPengajuanRemisi').addEventListener('submit', function(e) {
            if (!buktiInput.files[0]) {
                e.preventDefault();
                Swal.fire('Foto Bukti Wajib Diunggah', 'Silakan unggah foto bukti pendukung pengajuan remisi terlebih dahulu.', 'warning');
            }
        });
    });
</script>
</body>
</html>
