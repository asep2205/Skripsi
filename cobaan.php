<?php
session_start();
if($_SESSION['status'] != "login"){
    header("location:index.php");
    exit();
}

include 'koneksi.php';

// ===================================================================
// LOGIKA DYNAMIC MAPPING BERDASARKAN TOTAL POIN REWARD & PUNISHMENT
// ===================================================================

function dapatkanKategoriReward($total_poin) {
    $poin = (int)$total_poin;
    if ($poin >= 250) {
        return ['kategori' => 'Siswa Terbaik', 'hadiah' => 'Piagam penghargaan + hadiah khusus / penghargaan dari kepala sekolah', 'warna' => 'purple-badge'];
    } elseif ($poin >= 200) {
        return ['kategori' => 'Siswa Inspiratif', 'hadiah' => 'Piagam penghargaan + rekomendasi mewakili sekolah dalam kegiatan/lomba', 'warna' => 'info-badge'];
    } elseif ($poin >= 150) {
        return ['kategori' => 'Siswa Unggul', 'hadiah' => 'Piagam penghargaan + bantuan perlengkapan sekolah', 'warna' => 'primary-badge'];
    } elseif ($poin >= 100) {
        return ['kategori' => 'Siswa Berprestasi Tinggi', 'hadiah' => 'Piagam penghargaan + piala / medali', 'warna' => 'dark-badge'];
    } elseif ($poin >= 80) {
        return ['kategori' => 'Siswa Teladan', 'hadiah' => 'Piagam penghargaan + hadiah pembinaan', 'warna' => 'warning-badge'];
    } elseif ($poin >= 60) {
        return ['kategori' => 'Siswa Berprestasi', 'hadiah' => 'Piagam penghargaan + alat tulis / perlengkapan sekolah', 'warna' => 'blue-badge'];
    } elseif ($poin >= 40) {
        return ['kategori' => 'Siswa Aktif', 'hadiah' => 'Piagam penghargaan dari sekolah', 'warna' => 'success-badge'];
    } elseif ($poin >= 20) {
        return ['kategori' => 'Penghargaan Dasar', 'hadiah' => 'Sertifikat apresiasi dari wali kelas', 'warna' => 'secondary-badge'];
    } else {
        return ['kategori' => 'Belum Mencapai Target', 'hadiah' => '-', 'warna' => 'neutral-badge'];
    }
}

function dapatkanKategoriSanksi($total_punish) {
    $poin = (int)$total_punish;
    if ($poin >= 100) {
        return ['tingkat' => 'Pelanggaran Sangat Berat', 'tindakan' => 'Skorsing / Pemanggilan Orang Tua & Kepala Sekolah', 'warna' => 'bg-danger text-white'];
    } elseif ($poin >= 50) {
        return ['tingkat' => 'Pelanggaran Berat', 'tindakan' => 'Pemberian Surat Peringatan (SP) & Konseling BK', 'warna' => 'bg-warning text-dark'];
    } elseif ($poin >= 30) {
        return ['tingkat' => 'Pelanggaran Sedang', 'tindakan' => 'Tugas Pembinaan Lingkungan Sekolah / Pembersihan', 'warna' => 'bg-info text-dark'];
    } elseif ($poin >= 10) {
        return ['tingkat' => 'Pelanggaran Ringan', 'tindakan' => 'Teguran Lisan dan Pencatatan Dokumen', 'warna' => 'bg-secondary text-white'];
    } else {
        return ['tingkat' => 'Siswa Berperilaku Baik', 'tindakan' => 'Pertahankan kondisi disiplin siswa', 'warna' => 'bg-success-subtle text-success'];
    }
}

// Proses Query Mengambil & Menjumlahkan Poin Langsung dari database Anda
$filter_kelas = isset($_GET['kelas']) ? mysqli_real_escape_string($koneksi, $_GET['kelas']) : '';

$query = "SELECT 
            s.id_siswa, s.nis, s.nama_siswa, s.kelas,
            IFNULL(SUM(CASE WHEN l.label_prediksi = 'Reward' THEN l.poin_didapat ELSE 0 END), 0) AS total_poin_reward,
            IFNULL(SUM(CASE WHEN l.label_prediksi = 'Punishment' THEN l.poin_didapat ELSE 0 END), 0) AS total_poin_punishment
          FROM siswa s
          LEFT JOIN laporan_prilaku l ON s.id_siswa = l.id_siswa
          WHERE 1=1";

if ($filter_kelas != '') { 
    $query .= " AND s.kelas = '$filter_kelas'"; 
}

$query .= " GROUP BY s.id_siswa ORDER BY total_poin_reward DESC, s.nama_siswa ASC";
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Evaluasi Penghargaan & Sanksi - SMKS DB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8fafc; font-family: 'Poppins', sans-serif; }
        .card-siswa { background: white; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); margin-bottom: 15px; padding: 20px; border: none; }
        .purple-badge { background-color: #f3e8ff; color: #6b21a8; border: 1px solid #d8b4fe; }
        .info-badge { background-color: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc; }
        .primary-badge { background-color: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
        .dark-badge { background-color: #f1f5f9; color: #0f172a; border: 1px solid #cbd5e1; }
        .warning-badge { background-color: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
        .blue-badge { background-color: #e0e7ff; color: #3730a3; border: 1px solid #a5b4fc; }
        .success-badge { background-color: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .secondary-badge { background-color: #f3f4f6; color: #374151; border: 1px solid #d1d5db; }
        .neutral-badge { background-color: #fafafa; color: #a3a3a3; border: 1px solid #e5e5e5; }
    </style>
</head>
<body>

<div class="container mt-4">
    <h4 class="fw-bold mb-4">Daftar Hasil Evaluasi Perilaku Siswa</h4>
    
    <div class="row">
        <div class="col-12">
            <?php while($row = mysqli_fetch_assoc($result)) { 
                $res_reward = dapatkanKategoriReward($row['total_poin_reward']);
                $res_sanksi = dapatkanKategoriSanksi($row['total_poin_punishment']);
            ?>
            <div class="card-siswa">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <span class="text-muted small fw-semibold">NIS <?php echo $row['nis']; ?> - <?php echo $row['kelas']; ?></span>
                        <h5 class="fw-bold text-dark mt-1"><?php echo htmlspecialchars($row['nama_siswa']); ?></h5>
                        <div class="mt-2">
                            <span class="badge bg-success-subtle text-success me-1">+<?php echo $row['total_poin_reward']; ?> Reward</span>
                            <span class="badge bg-danger-subtle text-danger">-<?php echo $row['total_poin_punishment']; ?> Sanksi</span>
                        </div>
                    </div>
                    
                    <div class="col-md-5">
                        <span class="text-secondary small d-block mb-1">KATEGORI REWARD</span>
                        <span class="badge <?php echo $res_reward['warna']; ?> rounded-pill px-3 py-1.5 mb-2 fw-bold">
                            <?php echo $res_reward['kategori']; ?>
                        </span>
                        <div class="small text-muted bg-light p-2 rounded border" style="font-size: 12px;">
                            <strong>Hak:</strong> <?php echo $res_reward['hadiah']; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <span class="text-secondary small d-block mb-1">KATEGORI SANKSI</span>
                        <span class="badge <?php echo $res_sanksi['warna']; ?> rounded-pill px-3 py-1.5 mb-2 fw-bold">
                            <?php echo $res_sanksi['tingkat']; ?>
                        </span>
                        <div class="small text-muted bg-light p-2 rounded border" style="font-size: 12px;">
                            <strong>Konsekuensi:</strong> <?php echo $res_sanksi['tindakan']; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>

</body>
</html>