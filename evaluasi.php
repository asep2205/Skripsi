<?php
// Memulai sesi PHP
session_start();

// [KOMENTAR PENGEMBANGAN] 
// Proteksi halaman dimatikan sementara
/*
if($_SESSION['status'] != "login"){
    header("location:index.php");
    exit();
}
*/

include "koneksi.php";

// Menangkap filter pencarian berdasarkan nama, nis, atau kelas siswa
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';

/**
 * QUERY UTAMA: Mengambil data siswa dan mencocokkan total_poin_reward & total_poin_punishment
 * dengan tindakan tertinggi yang memenuhi syarat minimal_poin pada tabel evaluasi_siswa.
 */
$query_str = "SELECT 
                s.*,
                -- Subquery untuk mencari Reward (Apresiasi) Tertinggi
                (SELECT e1.tindakan 
                 FROM evaluasi_siswa e1 
                 WHERE e1.jenis = 'Reward' AND e1.minimal_poin <= s.total_poin_reward 
                 ORDER BY e1.minimal_poin DESC LIMIT 1) AS apresiasi_didapat,
                 
                -- Subquery untuk mencari Punishment (Sanksi) Tertinggi
                (SELECT e2.tindakan 
                 FROM evaluasi_siswa e2 
                 WHERE e2.jenis = 'Punishment' AND e2.minimal_poin <= s.total_poin_punishment 
                 ORDER BY e2.minimal_poin DESC LIMIT 1) AS sanksi_didapat

              FROM siswa s
              WHERE 1=1";

if (!empty($search)) {
    $query_str .= " AND (s.nama_siswa LIKE '%$search%' 
                    OR s.nis LIKE '%$search%' 
                    OR s.kelas LIKE '%$search%')";
}

$query_str .= " ORDER BY s.kelas ASC, s.nama_siswa ASC";
$sql = mysqli_query($koneksi, $query_str);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Evaluasi Siswa - SMKS DB</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body { background-color: #f0f4f8; font-family: 'Poppins', sans-serif; }
        .mobile-header { 
            background: linear-gradient(135deg, #1e3a8a 0%, #4f46e5 100%); 
            color: white; padding: 16px 20px; position: sticky; top: 0; z-index: 1000; box-shadow: 0 4px 15px rgba(0,0,0,0.08); 
        }
        .btn-back { color: white; font-size: 22px; text-decoration: none; }
        .card-custom { border-radius: 20px; border: none; }
        .badge-eval { font-size: 11px; padding: 6px 10px; border-radius: 6px; font-weight: 600; display: inline-block; max-width: 100%; white-space: normal; text-align: left; }
    </style>
</head>
<body>

<div class="mobile-header">
    <div class="container d-flex align-items-center gap-3 p-0">
        <a href="dashboard" class="btn-back"><i class="bi bi-arrow-left"></i></a>
        <h5 class="fw-bold mb-0" style="font-size: 18px; letter-spacing: 0.5px;">Evaluasi & Tindakan Siswa</h5>
    </div>
</div>

<div class="container my-4">
    
    <div class="alert alert-info border-0 rounded-4 small py-3 mb-4 d-flex align-items-center shadow-sm" style="background-color: #e0f2fe; color: #0369a1;">
        <i class="bi bi-info-circle-fill me-3 fs-5"></i>
        <span style="line-height: 1.4;">
            Sistem otomatis mencocokkan total poin reward/punishment siswa saat ini dengan batas <b>Minimal Poin</b> pada tabel panduan evaluasi kesiswaan untuk menentukan apresiasi atau sanksi yang berhak didapat.
        </span>
    </div>

    <div class="card p-3 card-custom shadow-sm mb-4">
        <form method="GET" action="">
            <div class="input-group">
                <span class="input-group-text bg-light border-0 px-3"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control bg-light border-0" placeholder="Cari nama siswa, NIS, atau kelas..." value="<?= htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary px-4 fw-medium">Cari</button>
            </div>
        </form>
    </div>

    <div class="card card-custom shadow-sm p-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-person-check-fill text-primary me-2"></i>Status Tindakan Hasil Akumulasi Poin</h6>
            <span class="badge bg-secondary px-3 py-2 rounded-pill small">Total: <?= mysqli_num_rows($sql); ?> Siswa</span>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%" class="text-center py-3">No</th>
                        <th width="25%" class="py-3">Identitas Siswa</th>
                        <th width="15%" class="text-center py-3">Poin Reward</th>
                        <th width="15%" class="text-center py-3">Poin Punishment</th>
                        <th width="20%" class="py-3">Apresiasi Didapat (Reward)</th>
                        <th width="20%" class="py-3">Sanksi Didapat (Punishment)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(mysqli_num_rows($sql) > 0) {
                        $no = 1;
                        while($data = mysqli_fetch_array($sql)) {
                            // Penkondisian teks jika belum mencapai batasan minimal apa-pun di tabel evaluasi_siswa
                            $apresiasi = !empty($data['apresiasi_didapat']) ? $data['apresiasi_didapat'] : "Belum ada apresiasi khusus";
                            $sanksi = !empty($data['sanksi_didapat']) ? $data['sanksi_didapat'] : "Berkelakuan baik (Bebas Sanksi)";
                            
                            // Pewarnaan badge dinamis berdasarkan status isi data
                            $class_apresiasi = !empty($data['apresiasi_didapat']) ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-light text-muted';
                            $class_sanksi = !empty($data['sanksi_didapat']) ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-light text-muted';
                    ?>
                    <tr>
                        <td class="text-center fw-medium text-secondary"><?= $no++; ?></td>
                        <td>
                            <div class="fw-bold text-dark mb-0"><?= htmlspecialchars($data['nama_siswa']); ?></div>
                            <small class="text-muted font-monospace">NIS: <?= htmlspecialchars($data['nis']); ?> &bull; Kelas: <?= htmlspecialchars($data['kelas']); ?></small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success px-2.5 py-1.5 rounded fw-bold font-monospace shadow-sm" style="font-size: 13px;">
                                <i class="bi bi-plus-lg me-1"></i><?= $data['total_poin_reward']; ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-danger px-2.5 py-1.5 rounded fw-bold font-monospace shadow-sm" style="font-size: 13px;">
                                <i class="bi bi-dash-lg me-1"></i><?= $data['total_poin_punishment']; ?>
                            </span>
                        </td>
                        <td>
                            <div class="badge-eval <?= $class_apresiasi; ?>">
                                <i class="bi <?= !empty($data['apresiasi_didapat']) ? 'bi-trophy-fill' : 'bi-dash-circle'; ?> me-1"></i> 
                                <?= htmlspecialchars($apresiasi); ?>
                            </div>
                        </td>
                        <td>
                            <div class="badge-eval <?= $class_sanksi; ?>">
                                <i class="bi <?= !empty($data['sanksi_didapat']) ? 'bi-exclamation-octagon-fill' : 'bi-check-circle-fill'; ?> me-1"></i> 
                                <?= htmlspecialchars($sanksi); ?>
                            </div>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else { 
                        echo '<tr><td colspan="6" class="text-center text-muted py-5">Data siswa tidak ditemukan.</td></tr>';
                    } 
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>